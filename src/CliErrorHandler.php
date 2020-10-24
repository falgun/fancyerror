<?php

namespace Falgun\FancyError;

use Throwable;
use ErrorException;
use function set_error_handler;
use function set_exception_handler;

class CliErrorHandler
{

    protected string $rootDir;

    public function __construct(string $rootDir)
    {
        $this->rootDir = $rootDir;

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
        /**
         * @todo Replace with template like <blue>text</blue>
         */
        echo "\e[0;42m\033[31m" .
        $exception->getMessage() .
        "\033[0m at \033[34m" .
        str_replace($this->rootDir, '', $exception->getFile()) .
        "\033[0m on line \033[32m" .
        $exception->getLine() .
        "\033[0m" .
        PHP_EOL;

        $this->terminate();
    }

    private function terminate(): void
    {
        if (\defined('PHPUNIT_RUNNING') === false) {
            die;
        }
    }
}
