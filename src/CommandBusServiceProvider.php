<?php

namespace Performing\CommandBus;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Performing\CommandBus\Facades\CommandBus;
use Performing\CommandBus\Facades\CommandBusDiscovery;

class CommandBusServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('performing-command-bus', function () {
            return new DefaultCommandBus();
        });

        $this->app->singleton('performing-command-bus-discovery', function () {
            return new \Performing\CommandBus\CommandBusDiscovery([
                app_path(),
            ]);
        });
    }

    public function boot()
    {
        $handlers = CommandBusDiscovery::add(location: app_path())
            ->handlers();

        CommandBus::registerMany($handlers);
    }
}
