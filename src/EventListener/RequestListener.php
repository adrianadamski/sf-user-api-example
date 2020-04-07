<?php declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\ControllerEvent;

class RequestListener
{
    public function onKernelController(ControllerEvent $event): void
    {
        $request = $event->getRequest();

        if (!$event->isMasterRequest() || $request->getContentType() != 'json' || !$request->getContent()) {
            return;
        }

        $data = json_decode($request->getContent(), true);

        if (json_last_error() === JSON_ERROR_NONE) {
            $request->request->replace((array)$data);
        }
    }
}
