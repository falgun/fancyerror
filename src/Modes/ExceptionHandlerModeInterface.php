<?php

namespace Falgun\FancyError\Modes;

interface ExceptionHandlerModeInterface
{

    public function handle(\Throwable $exception): void;
}
