<?php

namespace Performing\CommandBus\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Performing\CommandBus\CommandBusDiscovery
 *
 * @method static array handlers()
 * @method static static add(string $location)
 */
class CommandBusDiscovery extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'performing-command-bus-discovery';
    }
}
