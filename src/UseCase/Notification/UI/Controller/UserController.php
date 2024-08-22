<?php

namespace App\UseCase\Notification\UI\Controller;

use App\UseCase\Notification\Domain\User;
use App\UseCase\Notification\UI\Request\UserRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
{
    public function __construct(
        private ValidatorInterface $validator,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/user', 'user_create', methods: ['POST'])]
    public function createUser(Request $request): JsonResponse
    {
        // Implementation for DEMO purposes only.
        // The solution should be implemented using the CQRS pattern, with appropriate validations applied.
        $request = json_decode($request->getContent(), true);
        $userRequest = UserRequest::fromArray($request);
        $errors = $this->validator->validate($userRequest);

        if (count($errors) > 0) {
            return new JsonResponse($this->getErrorMessage((string) $errors), Response::HTTP_BAD_REQUEST);
        }
        try {
            $user = new User($userRequest->email, $userRequest->phoneNumber);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        } catch (\Exception $exception) {
            return new JsonResponse($this->getErrorMessage($exception->getMessage()), Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($this->getSuccessMessage($user->getId()));
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
     *     userId: string
     * }
     */
    private function getSuccessMessage(string $userId): array
    {
        return [
            'success' => true,
            'userId' => $userId,
        ];
    }
}
