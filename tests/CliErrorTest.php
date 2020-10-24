<?php
declare(strict_types=1);

namespace Falgun\FancyError\Tests;

use PHPUnit\Framework\TestCase;
use Falgun\FancyError\CliErrorHandler;

final class CliErrorTest extends TestCase
{

    public function setUp(): void
    {
        if (\defined('PHPUNIT_RUNNING') === false) {
            \define('PHPUNIT_RUNNING', true);
        }
    }

    public function testCliErrorHandler()
    {
        $handler = new CliErrorHandler('/root');

        ob_start();
        $handler->exceptionHandler(new \RuntimeException('Terminal boomed!'));
        $output = ob_get_clean();

        $this->assertStringContainsString('Terminal boomed!', $output);
    }
}
