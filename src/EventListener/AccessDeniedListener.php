<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
class AccessDeniedListener
{
    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        // Handle AccessDeniedException
        if ($exception instanceof AccessDeniedException) {
            $response = new JsonResponse([
                'status' => 403, // Change to 403 for Access Denied
                'message' => 'Access Denied: You do not have permission to access this resource.',
            ], 403);

            // Set the response
            $event->setResponse($response);
            return;
        }

        // Optionally handle other exceptions
        if ($exception instanceof HttpExceptionInterface) {
            $response = new JsonResponse([
                'status' => $exception->getStatusCode(),
                'message' => $exception->getMessage(),
            ], $exception->getStatusCode());

            $event->setResponse($response);
        }
    }
}
