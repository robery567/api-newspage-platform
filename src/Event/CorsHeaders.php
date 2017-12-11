<?php
/**
 * @package api
 * @author Petru Szemereczki <petru.office92@gmail.com>
 * @since 1.0
 */

namespace Newspage\Api\Event\After;


use Pimple\Container;
use Silex\Api\EventListenerProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CorsHeaders implements EventListenerProviderInterface
{
    public function subscribe(Container $app, EventDispatcherInterface $dispatcher)
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest' => 10000],
            KernelEvents::RESPONSE => ['onKernelResponse' => 10000],
        ];
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        $request = $event->getRequest();
        $method = $request->getRealMethod();
        if ('OPTIONS' == $method) {
            $response = new JsonResponse();
            $event->setResponse($response);
        }
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $response = $event->getResponse();
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST');
        $response->headers->set('Access-Control-Allow-Headers', '*');
    }
}