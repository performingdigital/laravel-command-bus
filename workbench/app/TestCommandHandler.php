<?php

namespace Workbench\App;

use Performing\CommandBus\CommandBusHandler;

class TestCommandHandler
{
    #[CommandBusHandler]
    public function handle(TestCommand $command)
    {
        // Handle command logic
    }
}
