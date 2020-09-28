<?php

namespace Falgun\FancyError\Modes;

use Falgun\Fountain\Fountain;

interface ExceptionHandlerModeInterface
{

    public function handle(\Throwable $exception): void;

    public function enterApplicationMode(Fountain $container): void;
}
