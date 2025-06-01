<?php

namespace Performing\CommandBus;

use Illuminate\Support\ServiceProvider;

class CommandBusServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(CommandBus::class, DefaultCommandBus::class);
    }
}
