<?php

namespace Performing\CommandBus;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Override;
use Performing\CommandBus\Commands\CommandBusClearCommand;
use Performing\CommandBus\Commands\CommandBusListCommand;
use Performing\CommandBus\Facades\CommandBus;
use Tempest\Discovery\BootDiscovery;
use Tempest\Discovery\DiscoveryConfig;
use Tempest\Discovery\DiscoveryLocation;

final class CommandBusServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('performing-command-bus', static function () {
            return new DefaultCommandBus();
        });

        $this->app->singleton(CommandBusDiscovery::class, static function () {
            return new CommandBusDiscovery();
        });

        try {
            $this->app->alias(CommandBusDiscovery::class, 'performing-command-bus-discovery');
        } catch (\Exception) {
            Log::error('Failed to alias CommandBusDiscovery to performing-command-bus-discovery');
        }
    }

    public function boot(): void
    {
        $locations = [
            new DiscoveryLocation('App\\', app_path()),
        ];

        if (App::environment('testing')) {
            $workbenchPath = realpath(__DIR__ . '/../workbench/app');
            if ($workbenchPath && is_dir($workbenchPath)) {
                $locations[] = new DiscoveryLocation('Workbench\\App\\', $workbenchPath);
            }
        }

        $config = new DiscoveryConfig(locations: $locations);

        try {
            $discovery = $this->app->make(CommandBusDiscovery::class);
        } catch (\Exception) {
            Log::error('Failed to make CommandBusDiscovery');
            return;
        }

        $boot = new BootDiscovery(
            container: $this->app,
            config: $config,
        );

        $boot(
            discoveryClasses: [CommandBusDiscovery::class],
            discoveryLocations: $config->locations,
        );

        CommandBus::registerMany($discovery->handlers());

        if ($this->app->runningInConsole()) {
            $this->commands([
                CommandBusListCommand::class,
                CommandBusClearCommand::class,
            ]);
        }
    }
}
