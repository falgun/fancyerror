<?php
declare(strict_types=1);

namespace Falgun\FancyError\Tests;

use Falgun\Fountain\Fountain;
use PHPUnit\Framework\TestCase;
use Falgun\FancyError\ErrorHandler;
use Falgun\FancyError\Modes\DebugMode;
use Falgun\FancyError\Modes\ProductionMode;

final class ErrorHandlerTest extends TestCase
{

    public function setUp(): void
    {
        if (\defined('PHPUNIT_RUNNING') === false) {
            \define('PHPUNIT_RUNNING', true);
        }
    }

    public function testDebugMode()
    {
        $handler = new DebugMode('/root');
        $errorHandler = new ErrorHandler($handler);
        $errorHandler->applicationBooted(new Fountain());

        ob_start();
        $handler->handle(new \RuntimeException('Boom!! Error Happened'));
        $output = ob_get_clean();

        $this->assertStringContainsString('<h2><font color="green">Boom!! Error Happened</font></h2>', $output);
        $this->assertStringContainsString('<p> Thrown in <b>/home/ataur/server/falgun/fancyerror/tests/ErrorHandlerTest.php</b>', $output);
        $this->assertStringContainsString('on line <font color="red"><b>29</b></font></p>', $output);
        $this->assertStringContainsString('<p>Type : FATAL ERROR</p>', $output);
        $this->assertStringContainsString('<p>Thrown by: Framework Internal</p>', $output);
    }

}
