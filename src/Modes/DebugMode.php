<?php
declare(strict_types=1);

namespace Falgun\FancyError\Modes;

use Throwable;
use Falgun\FancyError\Mappers\CodebaseToHTML;
use Falgun\FancyError\Mappers\ExceptionTraceToHTML;

class DebugMode implements ExceptionHandlerModeInterface
{

    private const ERROR_TYPES = [
        E_ERROR => 'FATAL_ERROR',
        E_WARNING => 'ERROR_WARNING',
        E_PARSE => 'ERROR_PARSE',
        E_NOTICE => 'ERROR_NOTICE'
    ];

    protected string $rootDir;

    public function __construct($rootDir)
    {
        $this->rootDir = $rootDir;
    }

    //put your code here
    public function handle(Throwable $exception): void
    {
        \http_response_code($this->getResponseCodeFromException($exception));

        $errorPack = $this->prepareErrorPack($exception);

        $template = $this->loadTemplateFile();

        echo $this->populateTemplateWithErrorData($template, $errorPack);
        die;
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
            $errorPack['error_type'] = $this->errorType($exception->getSeverity());
        } else {
            $errorPack['reporter'] = 'Framework Internal';
            $errorPack['error_type'] = $this->errorType($exception->getCode());
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
            $template = \str_replace('#' . \strtoupper($name) . '#', $data, $template);
        }
        return $template;
    }

    private function errorType($errorCode)
    {
        if (isset(self::ERROR_TYPES[$errorCode])) {
            return \str_replace('_', ' ', self::ERROR_TYPES[$errorCode]);
        }

        return 'FATAL ERROR';
    }

    private function getResponseCodeFromException(Throwable $exception): int
    {
        return $exception->getCode() ?: 404;
    }
}
