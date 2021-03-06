<?php


namespace App\EventListener;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener extends AbstractController
{
    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        $message = [
            'status' => $exception->getCode(),
            'message' => $exception->getMessage()
        ];


        // Customize your response object to display the exception details
        $response = $this->json($message) ;

        // HttpExceptionInterface is a special type of exception that
        // holds status code and header details
        if ($exception instanceof HttpExceptionInterface) {
            if ($exception->getCode() === 0){
                $message = [
                    'status' => $exception->getStatusCode(),
                    'message' => $exception->getMessage()
                ];
            }
            $response = $this->json($message, $exception->getStatusCode(), [$exception->getHeaders()]);

        } else {
            $response->setStatusCode(JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        // sends the modified response object to the event
        $event->setResponse($response);

    }
}