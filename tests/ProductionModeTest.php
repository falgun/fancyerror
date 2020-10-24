<?php
declare(strict_types=1);

namespace Falgun\FancyError\Tests;

use Falgun\Fountain\Fountain;
use PHPUnit\Framework\TestCase;
use Falgun\FancyError\ErrorHandler;
use Falgun\FancyError\Modes\ProductionMode;

final class ProductionModeTest extends TestCase
{

    public function setUp(): void
    {
        if (\defined('PHPUNIT_RUNNING') === false) {
            \define('PHPUNIT_RUNNING', true);
        }
        if (file_exists(__DIR__ . '/tmp/errors/error_log')) {
            unlink(__DIR__ . '/tmp/errors/error_log');
        }
    }

    public function testWithoutContainer()
    {
        $handler = new ProductionMode('/src', __DIR__ . '/tmp/errors');

        new ErrorHandler($handler);

        ob_start();
        $handler->handle(new \RuntimeException('Boom!! Error Happened'));
        $output = ob_get_clean();

        $this->assertSame(<<<HTML
<html>
    <head>
        <title>Oops! An Error Occured.</title>
    </head>
    <body>
        <h1>Oops! An Error Occured.</h1>
        <h4>
            Something is broken. Please let us know what you were doing when this error occured.
            We will fix it as soon as possible. Sorry For any inconvenience caused.
        </h4>
    </body>
</html>
HTML, $output);

        $log = file_get_contents(__DIR__ . '/tmp/errors/error_log');

        $this->assertStringContainsString('# Boom!! Error Happened Thrown in /home/ataur/server/falgun/fancyerror/tests/ProductionModeTest.php on line', $log);
    }

    public function testWithContainer()
    {
        $_SERVER['HTTP_ACCEPT'] = 'text/html';

        $handler = new ProductionMode('/src', __DIR__ . '/tmp/errors');

        $errorHandler = new ErrorHandler($handler);
        $errorHandler->applicationBooted(new Fountain());

        ob_start();
        $handler->handle(new \RuntimeException('Boom!! testWithContainer Error Happened'));
        $output = ob_get_clean();

        $this->assertSame('', $output);

        $log = file_get_contents(__DIR__ . '/tmp/errors/error_log');

        $this->assertStringContainsString('# Boom!! testWithContainer Error Happened Thrown in /home/ataur/server/falgun/fancyerror/tests/ProductionModeTest.php on line', $log);
    }

    public function testInvalidRootPath()
    {
        $handler = new ProductionMode('/src', '/root');
        new ErrorHandler($handler);

        ob_start();
        $handler->handle(new \RuntimeException('Boom!! Error Happened'));
        $output = ob_get_clean();

        $this->assertSame('', $output);
    }
}
