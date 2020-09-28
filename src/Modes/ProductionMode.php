<?php
declare(strict_types=1);

namespace Falgun\FancyError\Modes;

use Falgun\Fountain\Fountain;
use Falgun\FancyError\ErrorLogger;
use App\Templates\Site\SiteTemplate;

class ProductionMode implements ExceptionHandlerModeInterface
{

    protected string $rootDir;
    private Fountain $container;

    public function __construct(string $rootDir)
    {
        $this->rootDir = $rootDir;
    }

    public function handle(\Throwable $exception): void
    {
        try {
            $this->writeToLog($exception);
            $this->printErrorToScreen($exception);
        } catch (\Throwable $exception) {
            \http_response_code(500);
        } finally {
            die;
        }
    }

    private function writeToLog(\Throwable $exception): void
    {
        $errorLogger = new ErrorLogger($this->rootDir . DS . 'var' . DS . 'errors');
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
     * FIX IT
     */
    private function showErrorTemplate(\Throwable $exception): void
    {
        $acceptable = \explode(',', $_SERVER['HTTP_ACCEPT'] ?? 'text/html');

        if (\in_array('text/html', $acceptable, true) && class_exists(SiteTemplate::class)) {
            // We can show error page as HTML

            // Template may require session
            $_SESSION = [];
            $request = $this->container->get(\Falgun\Http\Request::class);

            /* @var $template SiteTemplate */
            $template = $this->container->get(SiteTemplate::class);

            $template->view(strval($exception->getCode() ?: 500));
            $template->setStatusCode($exception->getCode() ?: 500);
            $template->setViewDirFromControllerPath('\\Controllers\\ErrorsController', $this->rootDir . '/src/Views');

            $responseEmitter = new \Falgun\Application\ResponseEmitter();
            $responseEmitter->emit($request, $template);
            exit;
        }
        // We are gonna force Json here
        \header('Content-Type: application/json');
        echo \json_encode([
            'error_no' => ($exception->getCode() ?: 500),
            'error_msg' => 'oops! something went wrong!'
        ]);
        exit;
    }

    public function enterApplicationMode(Fountain $container): void
    {
        $this->container = $container;
    }
}
