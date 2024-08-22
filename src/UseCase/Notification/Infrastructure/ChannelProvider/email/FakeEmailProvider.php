<?php

namespace App\UseCase\Notification\Infrastructure\ChannelProvider\email;

use App\UseCase\Notification\Domain\Notification;
use App\UseCase\Notification\Domain\NotificationResponse;
use App\UseCase\Notification\Domain\Service\SmsDomainNotificationChannelProvider;
use App\UseCase\Notification\Infrastructure\ChannelProvider\sms\Response\SmsSuccessNotificationResponse;

class FakeEmailProvider implements SmsDomainNotificationChannelProvider
{
    public function send(Notification $notification): NotificationResponse
    {
        return new SmsSuccessNotificationResponse();
    }
}
