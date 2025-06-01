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
     * @throws CommandException
     */
    #[Override]
    public function dispatch(Command $command): void
    {
        $commandClass = get_class($command);

        if (!array_key_exists($commandClass, $this->handlers)) {
            throw new CommandException('Command does not have handler');
        }

        $handler = $this->handlers[$commandClass];

        if (!class_exists($handler)) {
            throw new CommandException($handler . ' class does exists');
        }

        $handler = App::make($handler);

        Pipeline::send($command)
            ->through($this->middlewares)
            ->then(function ($command) use ($handler): void {
                try {
                    $handler->handle($command);
                } catch (Throwable $throwable) {
                    Log::error($throwable->getMessage());
                    throw new CommandException($throwable->getMessage());
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
}
