<?php
namespace Falgun\FancyError;

use Exception;
use ErrorException;
use function set_error_handler;
use function set_exception_handler;

class CliErrorHandler
{

    public function __construct()
    {
        set_error_handler(array($this, 'errorHandler'));
        set_exception_handler(array($this, 'exceptionHandler'));
    }

    /**
     * 
     * @param type $level
     * @param type $message
     * @param type $file
     * @param type $line
     * @throws ErrorException
     */
    public function errorHandler($level, $message, $file, $line)
    {
        throw new ErrorException($message, 0, $level, $file, $line);
        die();
    }

    /**
     * Handle any errors
     * @param Exception $exception
     */
    public function exceptionHandler($exception)
    {
        /**
         * @todo Replace with template like <blue>text</blue>
         */
        echo "\e[0;42m\033[31m" . $exception->getMessage() . "\033[0m at \033[34m" . str_replace(ROOT_DIR, '', $exception->getFile()) . "\033[0m on line \033[32m" . $exception->getLine() . "\033[0m" . PHP_EOL;
        die;
    }
}
