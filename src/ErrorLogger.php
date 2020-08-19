<?php
declare(strict_types=1);

namespace Falgun\FancyError;

class ErrorLogger
{

    protected string $logDIR;

    public function __construct(string $logDIR)
    {
        //ROOT_DIR . DS . 'var' . DS . 'errors'

        $this->logDIR = \rtrim($logDIR, '/');

        if (\is_dir($this->logDIR) === false) {
            \mkdir($this->logDIR, 0755, true);
        }
    }

    public function logException(\Throwable $exception)
    {
        $errorText = \date('Y-m-d H:i:s') . ' # ' . $exception->getMessage() . ' Thrown in ' . $exception->getFile() . ' on line ' . $exception->getLine() . \PHP_EOL;
        return $this->log($errorText);
    }

    public function log(string $errorMsg, string $fileName = 'error_log')
    {
        $errorFile = $this->logDIR . DS . $fileName;

        return \file_put_contents($errorFile, $errorMsg, \FILE_APPEND);
    }
}
