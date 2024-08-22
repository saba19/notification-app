<?php

namespace App\UseCase\Notification\Infrastructure\ChannelProvider\sms;

use App\UseCase\Notification\Domain\Notification;
use App\UseCase\Notification\Domain\NotificationResponse;
use App\UseCase\Notification\Domain\Service\SmsDomainNotificationChannelProvider;
use App\UseCase\Notification\Infrastructure\ChannelProvider\sms\Response\SmsFailureNotificationResponse;

class FakeSmsProvider implements SmsDomainNotificationChannelProvider
{
    public function send(Notification $notification): NotificationResponse
    {
        return new SmsFailureNotificationResponse();
    }
}
