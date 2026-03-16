<?php

namespace Performing\CommandBus;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Override;
use Performing\CommandBus\Commands\CommandBusCacheCommand;
use Performing\CommandBus\Commands\CommandBusClearCommand;
use Performing\CommandBus\Commands\CommandBusListCommand;
use Performing\CommandBus\Facades\CommandBus;
use Tempest\Discovery\BootDiscovery;
use Tempest\Discovery\DiscoveryCache;
use Tempest\Discovery\DiscoveryCacheStrategy;
use Tempest\Discovery\DiscoveryConfig;
use Tempest\Discovery\DiscoveryLocation;

final class CommandBusServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->singleton('performing-command-bus', static function () {
            return new DefaultCommandBus();
        });

        $this->app->singleton(CommandBusDiscovery::class, static function () {
            return new CommandBusDiscovery();
        });

        $this->app->singleton(DiscoveryCache::class, static function () {
            $strategy = config('app.debug')
                ? DiscoveryCacheStrategy::NONE
                : DiscoveryCacheStrategy::FULL;

            return new DiscoveryCache($strategy);
        });

        $this->app->singleton(DiscoveryConfig::class, static function () {
            $locations = [
                new DiscoveryLocation('App\\', app_path()),
            ];

            if (App::environment('testing')) {
                $workbenchPath = realpath(__DIR__ . '/../workbench/app');
                if ($workbenchPath && is_dir($workbenchPath)) {
                    $locations[] = new DiscoveryLocation('Workbench\\App\\', $workbenchPath);
                }
            }

            return new DiscoveryConfig(locations: $locations);
        });

        try {
            $this->app->alias(CommandBusDiscovery::class, 'performing-command-bus-discovery');
        } catch (\Exception) {
            Log::error('Failed to alias CommandBusDiscovery to performing-command-bus-discovery');
        }
    }

    public function boot(): void
    {
        $config = $this->app->make(DiscoveryConfig::class);

        try {
            $discovery = $this->app->make(CommandBusDiscovery::class);
        } catch (\Exception) {
            Log::error('Failed to make CommandBusDiscovery');
            return;
        }

        $cache = $this->app->make(DiscoveryCache::class);

        $boot = new BootDiscovery(
            container: $this->app,
            config: $config,
            cache: $cache,
        );

        $boot(
            discoveryClasses: [CommandBusDiscovery::class],
            discoveryLocations: $config->locations,
        );

        CommandBus::registerMany($discovery->handlers());

        if ($this->app->runningInConsole()) {
            $this->commands([
                CommandBusListCommand::class,
                CommandBusCacheCommand::class,
                CommandBusClearCommand::class,
            ]);
        }
    }
}
