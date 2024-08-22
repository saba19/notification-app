<?php

namespace App\UseCase\Notification\Application;

use App\UseCase\Shared\Domain\Bus\Command;

final readonly class SendNotification implements Command
{
    public function __construct(
        private string $notificationId,
    ) {
    }

    public function getNotificationId(): string
    {
        return $this->notificationId;
    }
}
