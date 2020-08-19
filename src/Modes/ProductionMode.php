<?php
declare(strict_types=1);

namespace Falgun\FancyError\Modes;

use Falgun\FancyError\ErrorLogger;
use App\Templates\Site\SiteTemplate;

class ProductionMode implements ExceptionHandlerModeInterface
{
    protected string $rootDir;

    public function __construct($rootDir)
    {
        $this->rootDir = $rootDir;
    }

    public function handle(\Throwable $exception): void
    {
        $errorLogger = new ErrorLogger($this->rootDir . DS . 'var' . DS . 'errors');
        $errorLogger->logException($exception);

        try {
            $this->showErrorTemplate($exception);
        } catch (\Exception $ex) {
            \http_response_code(500);
        }
        die;
    }

    /**
     * Huge Mess
     * FIX IT
     */
    private function showErrorTemplate($exception)
    {
        $acceptable = \explode(',', $_SERVER['HTTP_ACCEPT'] ?? 'text/html');

        if (\in_array('text/html', $acceptable, true) && class_exists(SiteTemplate::class)) {
            // We can show error page as HTML
            $fountain = new \Falgun\Fountain\Fountain(new \Falgun\Fountain\SharedServices());
            
            /* @var $template SiteTemplate */
            $template = $fountain->resolve(SiteTemplate::class);

            $template->view(strval($exception->getCode() ?: 500));
            $template->setStatusCode($exception->getCode() ?: 500);
            $template->setViewDirFromControllerPath('ErrorsController', $this->rootDir . '/src/Views');

            return $template->send();
        }
        // We are gonna force Json here
        \header('Content-Type: application/json');
        echo \json_encode(['error_no' => ($exception->getCode() ?: 500),
            'error_msg' => 'oops! something went wrong!']);
    }
}
