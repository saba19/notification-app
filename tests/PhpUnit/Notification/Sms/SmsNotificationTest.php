<?php

namespace App\Tests\PhpUnit\Notification\Sms;

use App\UseCase\Notification\Domain\Channel;
use App\UseCase\Notification\Domain\NotificationFactory;
use App\UseCase\Notification\Domain\Service\DomainNotificationChannelProvider;
use App\UseCase\Notification\Domain\Status;
use App\UseCase\Notification\Domain\User;
use App\UseCase\Notification\Infrastructure\ChannelProvider\sms\Response\SmsFailureNotificationResponse;
use App\UseCase\Notification\Infrastructure\ChannelProvider\sms\Response\SmsSuccessNotificationResponse;
use PHPUnit\Framework\TestCase;

class SmsNotificationTest extends TestCase
{
    public function testSendSmsNotificationSuccessfully(): void
    {
        $notificationId = uniqid();
        $content = 'Some sms message';
        $user = new User('electra$AS@gmail.com', '+48000000000');

        $notificationFactory = new NotificationFactory();

        $smsNotification = $notificationFactory->createNotification(
            $notificationId,
            'sms',
            $user,
            $content,
            null
        );

        $notificationProvider = $this->createMock(DomainNotificationChannelProvider::class);
        $notificationProvider->expects($this->once())->method('send')->willReturn(new SmsSuccessNotificationResponse());
        $smsNotification->sendViaProvider($notificationProvider);

        $this->assertEquals(Channel::SMS, $smsNotification->getChannel());
        $this->assertEquals(Status::SENT, $smsNotification->getStatus());
        $this->assertEquals($content, $smsNotification->getContent());
    }

    public function testSmsNotificationFails(): void
    {
        $notificationId = uniqid();
        $content = 'Some sms message';
        $user = new User('electra$AS@gmail.com', '+48000000000');

        $notificationFactory = new NotificationFactory();

        $smsNotification = $notificationFactory->createNotification(
            $notificationId,
            'sms',
            $user,
            $content,
            null
        );

        $notificationProvider = $this->createMock(DomainNotificationChannelProvider::class);
        $notificationProvider->expects($this->once())->method('send')->willReturn(new SmsFailureNotificationResponse());
        $smsNotification->sendViaProvider($notificationProvider);

        $this->assertEquals(Channel::SMS, $smsNotification->getChannel());
        $this->assertEquals(Status::FAILED, $smsNotification->getStatus());
        $this->assertEquals($content, $smsNotification->getContent());
    }
}
