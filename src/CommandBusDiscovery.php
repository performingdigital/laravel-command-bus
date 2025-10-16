<?php

namespace Performing\CommandBus;

use Illuminate\Support\Facades\Cache;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionMethod;
use RegexIterator;

class CommandBusDiscovery
{
    public function __construct(
        protected array $locations = [],
    ) {}

    public function add(string $location): self
    {
        $this->locations[] = $location;

        $this->clearCache();

        return $this;
    }

    public function clearCache(): self
    {
        Cache::forget('command-bus-discovery');

        return $this;
    }

    public function handlers()
    {
        if (config('app.debug')) {
            return $this->discoverHandlers();
        }

        return Cache::rememberForever('command-bus-discovery', function () {
            return $this->discoverHandlers();
        });
    }

    public function discoverHandlers()
    {
        $handlers = [];

        foreach ($this->locations as $location) {
            $handlers = array_merge($handlers, $this->scanLocationForHandlers($location));
        }

        return $handlers;
    }

    private function scanLocationForHandlers(string $location): array
    {
        $handlers = [];

        if (!is_dir($location)) {
            return $handlers;
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($location));
        $phpFiles = new RegexIterator($iterator, '/^.+\.php$/i', RegexIterator::GET_MATCH);

        foreach ($phpFiles as $file) {
            $filePath = $file[0];
            $classes = $this->getClassesFromFile($filePath);

            foreach ($classes as $className) {
                if (class_exists($className)) {
                    $handlers = array_merge($handlers, $this->findHandlerMethodsInClass($className));
                }
            }
        }

        return $handlers;
    }

    private function getClassesFromFile(string $filePath): array
    {
        $content = file_get_contents($filePath);
        $classes = [];
        $namespace = '';

        // Extract namespace
        if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
            $namespace = $matches[1];
        }

        // Extract class names
        if (preg_match_all('/class\s+(\w+)/', $content, $matches)) {
            foreach ($matches[1] as $className) {
                $classes[] = $namespace ? ($namespace . '\\' . $className) : $className;
            }
        }

        return $classes;
    }

    private function findHandlerMethodsInClass(string $className): array
    {
        $handlers = [];

        try {
            $reflection = new ReflectionClass($className);

            foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                $attributes = $method->getAttributes();

                foreach ($attributes as $attribute) {
                    if (
                        $attribute->getName() === 'CommandBusHandler' ||
                            $attribute->getName() === 'Performing\\CommandBus\\CommandBusHandler'
                    ) {
                        $parameters = $method->getParameters();
                        if (count($parameters) > 0) {
                            $firstParameter = $parameters[0];
                            $parameterType = $firstParameter->getType();

                            if ($parameterType && $parameterType instanceof \ReflectionNamedType) {
                                $commandType = $parameterType->getName();

                                if (array_key_exists($commandType, $handlers)) {
                                    throw new CommandBusException(
                                        "Handler for command type '{$commandType}' already exists",
                                    );
                                }

                                $handlers[$commandType] = $className;
                            }
                        } else {
                            throw new CommandBusException(
                                "Handler method '{$method->getName()}' must have a parameter with the command as its type",
                            );
                        }
                    }
                }
            }
        } catch (\ReflectionException $e) {
            // Skip classes that can't be reflected
        }

        return $handlers;
    }
}
