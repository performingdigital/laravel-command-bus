<?php

namespace Performing\CommandBus\Commands;

use Illuminate\Console\Command;
use Performing\CommandBus\Facades\CommandBusDiscovery;

use function Laravel\Prompts\info;
use function Laravel\Prompts\note;

class CommandBusClearCommand extends Command
{
    protected $signature = 'command-bus:clear {--force : Force cache clearing without confirmation}';

    protected $description = 'Clear the command bus discovery cache';

    public function handle(): int
    {
        CommandBusDiscovery::clearCache();

        info('Command bus discovery cache cleared successfully.');
        note('Handlers will be re-discovered automatically on next request.');

        return Command::SUCCESS;
    }
}
