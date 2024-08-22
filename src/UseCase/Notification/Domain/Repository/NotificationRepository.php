<?php

namespace App\UseCase\Notification\Domain\Repository;

use App\UseCase\Notification\Domain\Notification;

interface NotificationRepository
{
    public function save(Notification $notification): void;

    /** @return Notification[] */
    public function getNotificationToResend(): array;

    public function getOneById(string $id): Notification;
}
