<?php

namespace Workbench\App;

class TestCommand
{
    public function __construct(
        public string $foo = 'bar',
    ) {}
}
