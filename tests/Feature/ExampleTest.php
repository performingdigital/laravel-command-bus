<?php

use Performing\CommandBus\CommandBusException;
use Performing\CommandBus\Facades\CommandBus;
use Performing\CommandBus\Facades\CommandBusDiscovery;
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
