<?php

namespace Performing\CommandBus\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Performing\CommandBus\CommandBusDiscovery
 *
 * @method static array handlers()
 */
class CommandBusDiscovery extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'performing-command-bus-discovery';
    }
}
