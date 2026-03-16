<?php

use Performing\CommandBus\CommandBusException;
use Performing\CommandBus\Facades\CommandBus;
use Performing\CommandBus\Facades\CommandBusDiscovery;
use Tempest\Discovery\BootDiscovery;
use Tempest\Discovery\DiscoveryCache;
use Tempest\Discovery\DiscoveryCacheStrategy;
use Tempest\Discovery\DiscoveryConfig;
use Tempest\Discovery\DiscoveryItems;
use Tempest\Discovery\GenerateDiscoveryCache;
use Workbench\App\TestCommand;
use Workbench\App\TestOtherCommand;

it('dispatching a command with handler is working', function () {
    $command = new TestCommand();

    CommandBus::dispatch($command);
})->throwsNoExceptions();

it('dispatching a command with handler is not working', function () {
    $command = new TestOtherCommand();

    CommandBus::dispatch($command);
})->throws(CommandBusException::class);

it('discovery facade works correctly', function () {
    $handlers = CommandBusDiscovery::handlers();
    expect($handlers)->toBeArray();
});

it('shows discovered handlers from workbench', function () {
    $handlers = CommandBusDiscovery::handlers();

    expect($handlers)->toBeArray();
    expect($handlers)->toHaveKey('Workbench\App\TestCommand');
});

it('caches and restores discovery results', function () {
    $config = app(DiscoveryConfig::class);
    $cache = new DiscoveryCache(DiscoveryCacheStrategy::FULL);

    // Generate the cache
    (new GenerateDiscoveryCache())(
        container: app(),
        config: $config,
        cache: $cache,
    );

    // Bind a fresh discovery instance so we can verify cache restores it
    $discovery = new \Performing\CommandBus\CommandBusDiscovery();
    app()->instance(\Performing\CommandBus\CommandBusDiscovery::class, $discovery);

    $boot = new BootDiscovery(
        container: app(),
        config: $config,
        cache: $cache,
    );

    $boot(
        discoveryClasses: [\Performing\CommandBus\CommandBusDiscovery::class],
        discoveryLocations: $config->locations,
    );

    expect($discovery->handlers())->toHaveKey('Workbench\App\TestCommand');

    // Clean up
    $cache->clear();
});

it('clears cache via artisan command', function () {
    $config = app(DiscoveryConfig::class);
    $cache = app(DiscoveryCache::class);

    // Generate cache first
    (new GenerateDiscoveryCache())(
        container: app(),
        config: $config,
        cache: $cache,
    );

    // Clear it
    $this->artisan('command-bus:clear')
        ->assertSuccessful();
});
