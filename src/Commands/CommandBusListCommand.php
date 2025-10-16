<?php

namespace Performing\CommandBus\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Performing\CommandBus\Facades\CommandBus;
use Performing\CommandBus\Facades\CommandBusDiscovery;
use Symfony\Component\Console\Terminal;

use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\warning;

class CommandBusListCommand extends Command
{
    protected $signature = 'command-bus:list
                            {--json : Output the handler list as JSON}
                            {--compact : Display compact format}';

    protected $description = 'List all command bus handlers';

    protected $headers = ['Status', 'Command', 'Handler', 'Source'];

    protected $statusColors = [
        'registered' => 'green',
        'discovered' => 'yellow',
    ];

    public function handle()
    {
        $handlers = $this->getHandlers();

        if ($handlers->isEmpty()) {
            warning('No command handlers found.');
            note('To get started, create handlers in your app directory and annotate them with #[CommandBusHandler]');
            return Command::SUCCESS;
        }

        $this->displayHandlers($handlers);

        return Command::SUCCESS;
    }

    protected function getHandlers(): Collection
    {
        $discovered = CommandBusDiscovery::handlers();
        $registered = CommandBus::handlers();

        $handlers = collect();

        // Add discovered handlers
        foreach ($discovered as $command => $handler) {
            $isRegistered = array_key_exists($command, $registered);
            $handlers->push([
                'command' => $command,
                'handler' => $handler,
                'source' => 'Discovered',
                'status' => $isRegistered ? 'registered' : 'discovered',
                'is_registered' => $isRegistered,
            ]);
        }

        // Add manually registered handlers that weren't discovered
        $manuallyRegistered = array_diff_key($registered, $discovered);
        foreach ($manuallyRegistered as $command => $handler) {
            $handlers->push([
                'command' => $command,
                'handler' => $handler,
                'source' => 'Manual',
                'status' => 'registered',
                'is_registered' => true,
            ]);
        }

        return $handlers->sortBy('command');
    }

    protected function displayHandlers(Collection $handlers): void
    {
        if ($this->option('json')) {
            $this->line($handlers->toJson(JSON_PRETTY_PRINT));
            return;
        }

        $this->displayForCli($handlers);
    }

    protected function displayForCli(Collection $handlers): void
    {
        $terminalWidth = (new Terminal)->getWidth();

        $output = $handlers->map(function ($handler) use ($terminalWidth) {
            $status = $handler['status'] === 'registered' ? '✓' : '•';
            $statusColor = $this->statusColors[$handler['status']];
            $command = $this->formatClassName($handler['command']);
            $handlerClass = $this->formatClassName($handler['handler']);
            $source = $handler['source'];

            if ($this->option('compact')) {
                return sprintf(
                    '  <fg=%s>%s</> <fg=white>%s</> › <fg=cyan>%s</>',
                    $statusColor,
                    $status,
                    $command,
                    $handlerClass
                );
            }

            // Calculate dots for spacing
            $statusPart = "  $status ";
            $commandPart = "$command › ";
            $sourcePart = " [$source]";
            $dotsLength = max($terminalWidth - mb_strlen($statusPart . $commandPart . $handlerClass . $sourcePart) - 2, 0);
            $dots = str_repeat('.', $dotsLength);

            return sprintf(
                '  <fg=%s>%s</> <fg=white>%s</>›<fg=#6C7280> %s</> <fg=cyan>%s</><fg=#6C7280> %s</>',
                $statusColor,
                $status,
                $command,
                $dots,
                $handlerClass,
                $sourcePart
            );
        });

        // Add header and footer
        $output->prepend('');
        $output->push('');
        $output->push($this->getHandlerCount($handlers, $terminalWidth));
        $output->push('');

        // Add legend
        $output->push('  <fg=green>✓</> Registered and available for dispatch');
        if ($handlers->where('status', 'discovered')->isNotEmpty()) {
            $output->push('  <fg=yellow>•</> Discovered but not registered');
        }
        $output->push('');

        foreach ($output as $line) {
            $this->line($line);
        }

        // Show warnings if needed
        $unregistered = $handlers->where('status', 'discovered')->count();
        if ($unregistered > 0) {
            warning("$unregistered handler(s) discovered but not registered. They cannot be dispatched.");
        }
    }

    protected function formatClassName(string $className): string
    {
        // Remove common namespace prefixes for cleaner display
        $className = str_replace(['App\\', 'Domain\\'], '', $className);

        // If still too long, shorten intelligently
        if (strlen($className) > 50) {
            $parts = explode('\\', $className);
            if (count($parts) > 2) {
                return $parts[0] . '\\...\\' . end($parts);
            }
        }

        return $className;
    }

    protected function getHandlerCount(Collection $handlers, int $terminalWidth): string
    {
        $registered = $handlers->where('status', 'registered')->count();
        $total = $handlers->count();

        $text = "Showing [$total] handlers ($registered registered)";
        $offset = max($terminalWidth - mb_strlen($text) - 2, 0);
        $spaces = str_repeat(' ', $offset);

        return $spaces . "<fg=blue;options=bold>$text</>";
    }
}
