<?php

declare(strict_types=1);

namespace Performing\CommandBus;

use Attribute;
use Tempest\Reflection\MethodReflector;

#[Attribute]
final class CommandBusHandler
{
    public string $commandName;

    public MethodReflector $handler;

    public function setCommandName(string $commandName): self
    {
        $this->commandName = $commandName;

        return $this;
    }

    public function setHandler(MethodReflector $handler): self
    {
        $this->handler = $handler;

        return $this;
    }
}
