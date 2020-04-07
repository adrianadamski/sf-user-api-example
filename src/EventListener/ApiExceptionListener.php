<?php declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiExceptionListener
{
    public const API_NAMESPACE = 'App\Controller\Api';

    private bool $debug;

    public function __construct(bool $debug)
    {
        $this->debug = $debug;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $controller = $event->getRequest()->attributes->get('_controller');

        if (substr($controller, 0, strlen(self::API_NAMESPACE)) !== self::API_NAMESPACE) {
            return;
        }

        $throwable = $event->getThrowable();

        $status = Response::HTTP_INTERNAL_SERVER_ERROR;
        $response = [
            'error' => [
                "message" => $throwable->getMessage(),
                "type" => basename(get_class($throwable)),
            ],
        ];

        if ($throwable instanceof HttpException) {
            $status = $response['error']['code'] = $throwable->getStatusCode();
        }

        if ($this->debug) {
            $response['error']['file'] = $throwable->getFile();
            $response['error']['line'] = $throwable->getLine();
        }

        $event->setResponse(new JsonResponse($response, $status));
    }
}
