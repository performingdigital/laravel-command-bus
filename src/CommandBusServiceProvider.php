<?php

namespace Performing\CommandBus;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Performing\CommandBus\Commands\CommandBusClearCommand;
use Performing\CommandBus\Commands\CommandBusListCommand;
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
        $discovery = CommandBusDiscovery::add(location: app_path());

        // Add workbench directory for testing
        if (App::environment('testing')) {
            $workbenchPath = realpath(__DIR__ . '/../workbench/app');
            if ($workbenchPath && is_dir($workbenchPath)) {
                $discovery->add($workbenchPath);
            }
        }

        $handlers = $discovery->handlers();

        CommandBus::registerMany($handlers);

        if ($this->app->runningInConsole()) {
            $this->commands([
                CommandBusListCommand::class,
                CommandBusClearCommand::class,
            ]);
        }
    }
}
