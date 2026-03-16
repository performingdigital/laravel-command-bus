<?php

declare(strict_types=1);

namespace Performing\CommandBus;

use Override;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;

final class CommandBusDiscovery implements Discovery
{
    use IsDiscovery;

    /** @var array<class-string<Command>, class-string> command class => handler class */
    private array $handlers = [];

    /** @throws CommandBusException */
    #[Override]
    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        foreach ($class->getPublicMethods() as $method) {
            if (! $method->hasAttribute(CommandBusHandler::class)) {
                continue;
            }

            $parameter = $method->getParameter(0);

            if ($parameter === null) {
                throw new CommandBusException(
                    "Handler method '{$method->getName()}' must have a parameter with the command as its type",
                );
            }

            $commandType = $parameter->getType()->getName();

            $this->discoveryItems->add($location, [
                'command' => $commandType,
                'handler' => $class->getName(),
            ]);
        }
    }

    /** @throws CommandBusException */
    #[Override]
    public function apply(): void
    {
        foreach ($this->discoveryItems as $item) {
            $commandType = $item['command'];
            $handlerClass = $item['handler'];

            if (array_key_exists($commandType, $this->handlers)) {
                throw new CommandBusException(
                    "Handler for command type '{$commandType}' already exists",
                );
            }

            $this->handlers[$commandType] = $handlerClass;
        }
    }

    /** @return array<string, string> */
    public function handlers(): array
    {
        return $this->handlers;
    }
}
