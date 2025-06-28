<?php

use Performing\CommandBus\CommandBusException;
use Performing\CommandBus\Facades\CommandBus;
use Performing\CommandBus\Facades\CommandBusDiscovery;
use Workbench\App\TestCommand;

it('dispatching a command with handler is working', function () {
    $handlers = CommandBusDiscovery::add(__DIR__ . '/../../workbench')
        ->handlers();

    CommandBus::registerMany($handlers);

    $command = new TestCommand();

    CommandBus::dispatch($command);
})->throwsNoExceptions();

it('dispatching a command with handler is not working', function () {
    $command = new TestCommand();

    CommandBus::dispatch($command);
})->throws(CommandBusException::class);
