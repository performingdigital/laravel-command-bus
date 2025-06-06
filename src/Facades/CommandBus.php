<?php

namespace Performing\CommandBus\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Performing\CommandBus\CommandBus
 *
 * @method static mixed dispatch($command)
 * @method static void register($command)
 * @method static void registerMany(array $commands)
 */
class CommandBus extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'performing-command-bus';
    }
}
