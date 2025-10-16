<?php

declare(strict_types=1);

namespace Performing\CommandBus;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Pipeline;
use Override;
use Performing\CommandBus\CommandBus;
use Throwable;

final class DefaultCommandBus implements CommandBus
{
    /** @var array<string> */
    protected array $middlewares = [];

    /** @var array<string> */
    protected array $handlers = [];

    /**
     * Pass the command through the middlewares and then run it.
     * @throws CommandBusException
     */
    #[Override]
    public function dispatch(object $command): mixed
    {
        $commandClass = get_class($command);

        if (!array_key_exists($commandClass, $this->handlers)) {
            throw new CommandBusException('Command does not have handler');
        }

        $handler = $this->handlers[$commandClass];

        if (!class_exists($handler)) {
            throw new CommandBusException($handler . ' class does exists');
        }

        $handler = App::make($handler);

        return Pipeline::send($command)
            ->through($this->middlewares)
            ->then(function ($command) use ($handler): mixed {
                try {
                    return $handler->handle($command);
                } catch (Throwable $throwable) {
                    Log::error($throwable->getMessage());
                    throw new CommandBusException($throwable->getMessage());
                }
            });
    }

    #[Override]
    public function register(string $command, string $handler): void
    {
        $this->handlers[$command] = $handler;
    }

    #[Override]
    public function registerMany(array $commands): void
    {
        foreach ($commands as $command => $handler) {
            $this->register($command, $handler);
        }
    }

    /**
     * Get all registered handlers.
     *
     * @return array<string, string>
     */
    #[Override]
    public function handlers(): array
    {
        return $this->handlers;
    }
}
