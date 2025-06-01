<?php

declare(strict_types=1);

namespace Performing\CommandBus;

interface CommandBus
{
    public function dispatch(Command $command): void;

    public function register(string $command, string $handler): void;

    public function registerMany(array $array): void;
}
