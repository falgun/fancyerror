<?php
declare(strict_types=1);

namespace Falgun\FancyError\Modes;

use Falgun\Fountain\Fountain;
use Falgun\FancyError\ErrorLogger;

final class ProductionMode implements ExceptionHandlerModeInterface
{

    private string $appDir;
    private string $logDir;
    private ?Fountain $container;

    public function __construct(string $appDir, string $logDir)
    {
        $this->appDir = $appDir;
        $this->logDir = $logDir;
        $this->container = null;
    }

    public function enterApplicationMode(Fountain $container): void
    {
        $this->container = $container;
    }

    public function handle(\Throwable $exception): void
    {
        try {
            $this->writeToLog($exception);
            $this->printErrorToScreen($exception);
        } catch (\Throwable $exception) {
            \http_response_code(500);
        } finally {
            $this->terminate();
        }
    }

    private function writeToLog(\Throwable $exception): void
    {
        $errorLogger = new ErrorLogger($this->logDir);
        $errorLogger->logException($exception);
    }

    private function printErrorToScreen(\Throwable $exception): void
    {
        if (isset($this->container)) {
            $this->showErrorTemplate($exception);
            return;
        }
        echo $this->loadDefaultMessage();
    }

    private function loadDefaultMessage(): string
    {
        return \file_get_contents(\dirname(__DIR__) . '/stub/defaultMessage.tpl');
    }

    /**
     * Huge Mess
     * Sorry, I've failed to make this code any better
     * please FIX IT if you can
     */
    private function showErrorTemplate(\Throwable $exception): void
    {
        if (isset($this->container) === false) {
            return;
        }

        $request = $this->container->get(\Falgun\Http\Request::class);

        $acceptHeader = strtolower($request->headers()->get('Accept', 'text/html'));

        if ((\strpos($acceptHeader, 'text/html') !== false) && class_exists(\App\Templates\Site\SiteTemplate::class)) {
            // We can show error page as HTML

            /* @var $response SiteTemplate */
            $response = $this->container->get(\App\Templates\Site\SiteTemplate::class);

            $response->view(strval($exception->getCode() ?: 500));
            $response->setStatusCode($exception->getCode() ?: 500);
            $response->setViewDirFromControllerPath('\\Controllers\\ErrorsController', $this->appDir . '/Views');

            $response->with(['exception', $exception]);
        } else {
            $output = [
                'success' => false,
                'error' => [
                    'code' => ($exception->getCode() ?: 500),
                    'message' => 'Sorry, something went wrong!',
                    'reason' => 'Internal Server Error',
                ],
            ];
            // We are gonna force Json here
            $response = new \Falgun\Http\Response(
                \json_encode($output),
                ($exception->getCode() ?: 500),
                'Internal Server Error'
            );
            $response->headers()->set('Content-Type', 'application/json');
        }

        $responseEmitter = new \Falgun\Application\ResponseEmitter();
        $responseEmitter->emit($request, $response);
    }

    private function terminate(): void
    {
        if (\defined('PHPUNIT_RUNNING') === false) {
            die;
        }
    }
}
