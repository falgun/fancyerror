<?php
declare(strict_types=1);

namespace Falgun\FancyError;

use Throwable;
use ErrorException;
use Falgun\Application\Config;
use Falgun\FancyError\Modes\DebugMode;
use Falgun\FancyError\Modes\ProductionMode;

final class ErrorHandler
{

    protected bool $isDebug;
    protected string $rootDir;

    private function __construct(bool $isDebug, string $rootDir)
    {
        $this->isDebug = $isDebug;
        $this->rootDir = $rootDir;

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
        if ($this->isDebug === true) {
            $handler = new DebugMode($this->rootDir);
        } else {
            $handler = new ProductionMode($this->rootDir);
        }

        $handler->handle($exception);
    }
}
