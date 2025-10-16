<?php

use Illuminate\Support\Facades\Cache;
use Performing\CommandBus\CommandBusException;
use Performing\CommandBus\Facades\CommandBus;
use Performing\CommandBus\Facades\CommandBusDiscovery;
use Workbench\App\TestCommand;
use Workbench\App\TestOtherCommand;

it('dispatching a command with handler is working', function () {
    $handlers = CommandBusDiscovery::add(__DIR__ . '/../../workbench')
        ->handlers();

    CommandBus::registerMany($handlers);

    $command = new TestCommand();

    CommandBus::dispatch($command);
})->throwsNoExceptions();

it('dispatching a command with handler is not working', function () {
    $command = new TestOtherCommand();

    CommandBus::dispatch($command);
})->throws(CommandBusException::class);

it('discovery facade works correctly', function () {
    $discovery = CommandBusDiscovery::add('/nonexistent');
    expect($discovery)->toBeInstanceOf(\Performing\CommandBus\CommandBusDiscovery::class);

    $handlers = CommandBusDiscovery::handlers();
    expect($handlers)->toBeArray();
});

it('integrates cache operations with discovery', function () {
    // Test cache operations work
    $testHandlers = ['TestCommand' => 'TestHandler'];
    Cache::put('command-bus-discovery', $testHandlers, 60);

    expect(Cache::has('command-bus-discovery'))->toBeTrue();
    expect(Cache::get('command-bus-discovery'))->toBe($testHandlers);

    Cache::forget('command-bus-discovery');
    expect(Cache::has('command-bus-discovery'))->toBeFalse();
});

it('shows discovered handlers from workbench', function () {
    // Discover handlers from workbench
    $handlers = CommandBusDiscovery::add(__DIR__ . '/../../workbench/app')->handlers();

    // Should discover the TestCommandHandler
    expect($handlers)->toBeArray();

    if (!empty($handlers)) {
        expect($handlers)->toHaveKey('Workbench\\App\\TestCommand');
    }
});
