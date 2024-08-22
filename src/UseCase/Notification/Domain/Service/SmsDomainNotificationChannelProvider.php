<?php

namespace App\UseCase\Notification\Domain\Service;

use App\UseCase\Notification\Domain\Notification;
use App\UseCase\Notification\Domain\NotificationResponse;

interface SmsDomainNotificationChannelProvider extends DomainNotificationChannelProvider
{
    public function send(Notification $notification): NotificationResponse;
}
