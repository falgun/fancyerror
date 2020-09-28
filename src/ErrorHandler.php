<?php
declare(strict_types=1);

namespace Falgun\FancyError;

use Throwable;
use ErrorException;
use Falgun\Fountain\Fountain;
use Falgun\Application\Config;
use Falgun\FancyError\Modes\DebugMode;
use Falgun\FancyError\Modes\ProductionMode;
use Falgun\FancyError\Modes\ExceptionHandlerModeInterface;

final class ErrorHandler
{

    protected bool $isDebug;
    protected string $rootDir;
    protected ExceptionHandlerModeInterface $handler;

    private function __construct(bool $isDebug, string $rootDir)
    {
        $this->isDebug = $isDebug;
        $this->rootDir = $rootDir;
        $this->handler = $this->buildHandler();

        \set_error_handler([$this, 'errorHandler']);
        \set_exception_handler([$this, 'exceptionHandler']);
    }

    public static function createFromConfig(Config $config, string $rootDir): ErrorHandler
    {
        return (new ErrorHandler(
                $config->get('DEBUG'),
                $rootDir,
        ));
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

    private function buildHandler(): ExceptionHandlerModeInterface
    {
        if ($this->isDebug === true) {
            return new DebugMode($this->rootDir);
        }

        return new ProductionMode($this->rootDir);
    }
}
