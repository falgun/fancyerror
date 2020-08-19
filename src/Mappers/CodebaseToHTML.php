<?php
declare(strict_types=1);

namespace Falgun\FancyError\Mappers;

final class CodebaseToHTML implements MapperInterface
{

    private string $file;
    private int $line;

    private function __construct(string $file, int $line)
    {
        $this->file = $file;
        $this->line = $line;
    }

    public static function createFromException(\Throwable $exception): CodebaseToHTML
    {
        return new static($exception->getFile(), $exception->getLine());
    }

    public function map(): string
    {
        if (\is_readable($this->file) === false) {
            return '';
        }

        $code = \file_get_contents($this->file);
        $codeLines = \explode(PHP_EOL, $code);
        $codeBase = '';
        $blockStart = $this->line - 7;
        $blockEnd = $this->line + 4;

        for ($i = $blockStart; $i < $blockEnd; $i++) {

            if (isset($codeLines[$i]) === false) {
                break;
            }

            $codeLine = $codeLines[$i];
            $ln = $i + 1;

            if ($this->line !== ($i + 1)) {
                // String
                $codeLine = \preg_replace('#([\'|\"]+)(.*?)([\'|\"]+)#', '$1<font class="text">$2</font>$3', $codeLine);

                // Integer
                $codeLine = \preg_replace('#(\s)([0-9]+)([\s|\;])#', '$1<font color="orangered">$2</font>$3', $codeLine);

                // Local Variables
                $codeLine = \preg_replace('#\$([a-zA-Z0-9]+)#', '<font color="brown">\$$1</font>', $codeLine);

                // Lets blue all definations
                $codeLine = \preg_replace('#(\s)([a-zA-Z0-9]+)(\s)#', '$1<font color="blue">$2</font>$3', $codeLine);
                $codeLine = \preg_replace('#(\s)([a-zA-Z0-9]+)(\s)#', '$1<font color="blue">$2</font>$3', $codeLine);
                $codeLine = \preg_replace('#(\s)([a-zA-Z0-9]+)(\s)#', '$1<font color="blue">$2</font>$3', $codeLine);

                // Object properties
                $codeLine = \preg_replace('#->([a-zA-Z0-9]+)([\s|\)|\;]+)#', '-><font color="green">$1</font>$2', $codeLine);

                // method names
                $codeLine = \preg_replace('#([a-zA-Z0-9]+)\(#', '<b>$1</b>(', $codeLine);
            }

            $codeLine = \preg_replace('#//(.*)$#', '<span class="commentLine">//$1</span>', $codeLine);

            if (\strpos($codeLine, '/*') !== false) {
                $codeLine = \preg_replace('#/\*(.*)$#', '<span class="commentBlock">/*$1', $codeLine);
                $commentBlock = true;
            }

            if (\strpos($codeLine, '*/') !== false && !empty($commentBlock)) {
                $codeLine = \preg_replace('#^(.*)\*/#', '$1*/</span>', $codeLine);
                $commentBlock = false;
            }

            if ($this->line === ($i + 1)) {
                $prefix = '<span class="activeLine">';
                $suffix = '</span>';
                $seperator = '';
            } else {
                $prefix = '';
                $suffix = '';
                $seperator = PHP_EOL;
            }
            $codeBase .= $prefix . $ln . ".\t" . $codeLine . $suffix . $seperator;
        }


        return $codeBase;
    }
}
