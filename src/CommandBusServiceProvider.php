<?php

namespace Performing\CommandBus;

use Illuminate\Support\ServiceProvider;

class CommandBusServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('performing-command-bus', function () {
            return new DefaultCommandBus();
        });
    }
}
