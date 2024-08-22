<?php

namespace App\UseCase\Notification\Infrastructure;

use App\UseCase\Notification\Application\SendNotification;
use App\UseCase\Notification\Application\SendNotificationHandler;
use App\UseCase\Notification\Domain\Repository\NotificationRepository;
use App\UseCase\Notification\Exception\MaxAttemptsExceededException;
use App\UseCase\Notification\Exception\NotificationCannotBeSentException;
use App\UseCase\Notification\Infrastructure\ChannelProvider\NotificationChannelProviderFactory;

readonly class ApiSendNotificationHandler implements SendNotificationHandler
{
    public function __construct(
        private NotificationRepository $notificationRepository,
        private NotificationChannelProviderFactory $notificationChannelProviderFactory,
    ) {
    }

    /**
     * @throws NotificationCannotBeSentException
     * @throws MaxAttemptsExceededException
     */
    public function __invoke(SendNotification $command): void
    {
        $notification = $this->notificationRepository->getOneById($command->getNotificationId());
        $notificationChannelProviders = $this->notificationChannelProviderFactory->getAllEnabledProvidersByChannel(
            $notification->getChannel()
        );

        foreach ($notificationChannelProviders as $notificationChannelProvider) {
            if (!$notification->canBeSend()) {
                return;
            }

            $notification->sendViaProvider($notificationChannelProvider);
        }
    }
}
