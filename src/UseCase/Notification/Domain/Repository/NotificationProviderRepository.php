<?php

namespace App\UseCase\Notification\Domain\Repository;

use App\UseCase\Notification\Domain\NotificationProvider;
use App\UseCase\Notification\Domain\Provider;

interface NotificationProviderRepository
{
    public function save(NotificationProvider $notificationProvider): void;

    public function isEnabled(Provider $name): bool;

    public function getByName(Provider $name): NotificationProvider;
}
