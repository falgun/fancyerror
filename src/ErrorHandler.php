<?php
declare(strict_types=1);

namespace Falgun\FancyError;

use Throwable;
use ErrorException;
use Falgun\Fountain\Fountain;
use Falgun\FancyError\Modes\ExceptionHandlerModeInterface;
use function set_error_handler;
use function set_exception_handler;

final class ErrorHandler
{

    private ExceptionHandlerModeInterface $handler;

    public function __construct(ExceptionHandlerModeInterface $handler)
    {
        $this->handler = $handler;

        set_error_handler(function(int $level, string $message, string $file, int $line) {
            $this->errorHandler($level, $message, $file, $line);
        });

        set_exception_handler(function(Throwable $exception) {
            $this->exceptionHandler($exception);
        });
    }

    public function errorHandler(int $level, string $message, string $file, int $line): void
    {
        throw new ErrorException($message, 0, $level, $file, $line);
    }

    public function exceptionHandler(Throwable $exception): void
    {
        $this->handler->handle($exception);
    }

    public function applicationBooted(Fountain $container): void
    {
        $this->handler->enterApplicationMode($container);
    }
}
