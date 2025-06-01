<?php

use Performing\CommandBus\Command;
use Performing\CommandBus\CommandException;
use Performing\CommandBus\CommandHandler;
use Performing\CommandBus\Facades\CommandBus;

class TestCommand implements Command
{
    public function __construct()
    {
        // Initialize command properties
    }
}

class TestCommandHandler implements CommandHandler
{
    public function handle(TestCommand $command)
    {
        // Handle command logic
    }
}

it('dispatching a command with handler is working', function () {
    $command = new TestCommand();

    CommandBus::register(TestCommand::class, TestCommandHandler::class);

    CommandBus::dispatch($command);
})->throwsNoExceptions();

it('dispatching a command without handler throws exception', function () {
    $command = new TestCommand();

    CommandBus::dispatch($command);
})->throws(CommandException::class);
