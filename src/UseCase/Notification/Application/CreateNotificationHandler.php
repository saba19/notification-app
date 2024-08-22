<?php

namespace App\UseCase\Notification\Application;

use App\UseCase\Notification\Domain\NotificationFactory;
use App\UseCase\Notification\Domain\Repository\NotificationRepository;
use App\UseCase\Notification\Domain\Repository\UserRepository;
use App\UseCase\Notification\Exception\NotSupportedChannelException;
use App\UseCase\Notification\Exception\UserNotFoundException;
use App\UseCase\Shared\Domain\Bus\CommandHandler;

final readonly class CreateNotificationHandler implements CommandHandler
{
    public function __construct(
        private NotificationRepository $notificationRepository,
        private UserRepository $userRepository,
        private NotificationFactory $notificationFactory,
    ) {
    }

    /**
     * @throws UserNotFoundException
     * @throws NotSupportedChannelException
     */
    public function __invoke(CreateNotification $command): void
    {
        $user = $this->userRepository->getOneById($command->getRecipientId());
        $notification = $this->notificationFactory->createNotification(
            $command->getNotificationId(),
            $command->getChannel(),
            $user,
            $command->getContent(),
            $command->getSubject(),
        );

        $this->notificationRepository->save($notification);
    }
}
