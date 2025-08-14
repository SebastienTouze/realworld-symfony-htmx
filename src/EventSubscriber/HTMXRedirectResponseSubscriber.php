<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * This Subscriber checks if the response is a 302 with a HTMX request.
 * If yes changes the response to a working redirection for HTMX.
 */
class HTMXRedirectResponseSubscriber implements EventSubscriberInterface
{
    public function onKernelResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        if (302 !== $response->getStatusCode()) {
            return;
        }

        $isHTMXRequest = $event->getRequest()->headers->get('HX-Request', false);
        if ($isHTMXRequest) {
            $redirectLocation = $response->headers->get('location');
            $event->setResponse(new Response(null, Response::HTTP_NO_CONTENT, ['HX-Redirect' => $redirectLocation]));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }
}
