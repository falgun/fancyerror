<?php
declare(strict_types=1);

namespace Falgun\FancyError\Mappers;

final class ExceptionTraceToHTML implements MapperInterface
{

    protected array $traceStack;

    private function __construct(array $traceStack)
    {
        $this->traceStack = $traceStack;
    }

    public static function createFromException(\Throwable $exception): ExceptionTraceToHTML
    {
        $traces = $exception->getTrace();

        if ($exception instanceof \ErrorException) {
            /**
             * As we have converted PHP error to ErrorException
             * in ErrorHandler Class, First Trace of this array
             * will be ErrorHandler class reference, we don't need this
             */
            unset($traces[0]);
        }
        return new static($traces);
    }

    public function map(): string
    {
        $stringTrace = '';
        $currentTraceNumber = \count($this->traceStack);

        foreach ($this->traceStack as $trace) {

            $stringTrace .= $this->prepareHtmlOfSingleTrace($trace, $currentTraceNumber);
            $currentTraceNumber--;
        }

        return $stringTrace;
    }

    private function prepareHtmlOfSingleTrace(array $trace, int $traceNumber): string
    {
        $file = $trace['file'] ?? '';
        $line = $trace['line'] ?? 0;
        $class = $trace['class'] ?? '';
        $function = $trace['function'] ?? '';
        $arguments = $trace['args'] ?? [];

        $stringTrace = <<<HTML
<div>{$traceNumber} {$file}(<span class="spn-green">{$line})</span>: {$class}-><span class="trace-func">{$function}(
HTML;
        $args = [];
        foreach ($arguments as $arg) {
            if (\is_object($arg)) {
                $args[] = '(' . \gettype($arg) . ') ' . \get_class($arg);
            } else {
                $args[] = '(' . \gettype($arg) . ') ' . \json_encode($arg);
            }
        }
        $stringTrace .= '<span class="hide hide-seek">' . \preg_replace('#([\"|\'])([\s\S]+?)([\"|\'])#', '$1<b>$2</b>$3', implode(', ', $args)) . '</span>';
        $stringTrace .= ')</span>';
        $stringTrace .= '</div>';

        return $stringTrace;
    }
}
