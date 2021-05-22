<?php
declare(strict_types=1);

namespace Falgun\FancyError\Modes;

use Throwable;
use ErrorException;
use Falgun\Fountain\Fountain;
use Falgun\FancyError\Mappers\CodebaseToHTML;
use Falgun\FancyError\Mappers\ExceptionTraceToHTML;

final class DebugMode implements ExceptionHandlerModeInterface
{
    private string $rootDir;

    public function __construct(string $rootDir)
    {
        $this->rootDir = $rootDir;
    }

    public function handle(Throwable $exception): void
    {
        \http_response_code($this->getResponseCodeFromException($exception));

        $errorPack = $this->prepareErrorPack($exception);

        $template = $this->loadTemplateFile();

        echo $this->populateTemplateWithErrorData($template, $errorPack);

        if (\defined('PHPUNIT_RUNNING') === false) {
            die;
        }
    }

    private function prepareErrorPack(Throwable $exception): array
    {
        $traceMapper = ExceptionTraceToHTML::createFromException($exception);
        $codebaseMapper = CodebaseToHTML::createFromException($exception);

        $errorPack = [
            'error' => $exception->getMessage(),
            'file' => $this->prepareErrorFilePath($exception->getFile()),
            'line' => $exception->getLine(),
            'error_code' => $exception->getCode(),
            'stacktrace' => $traceMapper->map(),
            'previous' => $exception->getPrevious(),
            'codebase' => $codebaseMapper->map(),
        ];

        if ($exception instanceof ErrorException) {
            $errorPack['reporter'] = 'PHP Parser';
        } else {
            $errorPack['reporter'] = 'Framework Internal';
        }

        return $errorPack;
    }

    private function prepareErrorFilePath(string $path): string
    {
        return \str_replace($this->rootDir, '', $path);
    }

    private function loadTemplateFile(): string
    {
        return \file_get_contents(\dirname(__DIR__) . '/stub/displayException.tpl');
    }

    private function populateTemplateWithErrorData(string $template, array $errorData): string
    {
        foreach ($errorData as $name => $data) {
            $template = \str_replace('#' . \strtoupper($name) . '#', (string) $data, $template);
        }
        return $template;
    }

    private function getResponseCodeFromException(Throwable $exception): int
    {
        $code = $exception->getCode();

        return is_int($code) && $code >= 100 ? $code : 500;
    }

    public function enterApplicationMode(Fountain $container): void
    {
        return;
    }
}
