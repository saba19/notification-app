<?php

namespace App\UseCase\Notification\UI\Controller;

use App\UseCase\Notification\Application\CreateNotification;
use App\UseCase\Notification\Application\SendNotification;
use App\UseCase\Notification\UI\Request\NotificationRequest;
use App\UseCase\Shared\Domain\Bus\CommandBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class NotificationController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private ValidatorInterface $validator,
    ) {
    }

    #[Route('/send-notification', 'send_notification', methods: ['POST'])]
    public function send(Request $request): JsonResponse
    {
        $request = json_decode($request->getContent(), true);
        $notificationRequest = NotificationRequest::fromArray($request);
        $errors = $this->validator->validate($notificationRequest);

        if (count($errors) > 0) {
            return new JsonResponse($this->getErrorMessage((string) $errors), Response::HTTP_BAD_REQUEST);
        }
        try {
            $notificationId = uniqid();
            $this->commandBus->dispatch(
                new CreateNotification(
                    $notificationId,
                    $notificationRequest->recipientId,
                    $notificationRequest->channel,
                    $notificationRequest->content,
                    $notificationRequest->subject,
                ));

            $this->commandBus->dispatch(new SendNotification($notificationId));
        } catch (\Exception $exception) {
            return new JsonResponse($this->getErrorMessage($exception->getMessage()), Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($this->getSuccessMessage('Notification sent'));
    }

    /**
     * @return array{
     *     error: bool,
     *     message: string
     * }
     */
    private function getErrorMessage(string $message): array
    {
        return [
            'error' => true,
            'message' => $message,
        ];
    }

    /**
     * @return array{
     *     success: bool,
     *     message: string
     * }
     */
    private function getSuccessMessage(string $message): array
    {
        return [
            'success' => true,
            'message' => $message,
        ];
    }
}
