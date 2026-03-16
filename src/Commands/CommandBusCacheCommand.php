<?php

namespace Performing\CommandBus\Commands;

use Illuminate\Console\Command;

use Tempest\Discovery\DiscoveryCache;
use Tempest\Discovery\DiscoveryConfig;
use Tempest\Discovery\GenerateDiscoveryCache;

use function Laravel\Prompts\info;

class CommandBusCacheCommand extends Command
{
    protected $signature = 'command-bus:cache';

    protected $description = 'Cache the command bus discovery results';

    public function handle(DiscoveryConfig $config, DiscoveryCache $cache): int
    {
        $cache->clear();

        (new GenerateDiscoveryCache())(
            container: $this->laravel,
            config: $config,
            cache: $cache,
        );

        info('Command bus discovery cached successfully.');

        return Command::SUCCESS;
    }
}
