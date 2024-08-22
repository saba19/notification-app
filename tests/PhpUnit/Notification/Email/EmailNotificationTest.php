<?php

namespace App\Tests\PhpUnit\Notification\Email;

use App\UseCase\Notification\Domain\Channel;
use App\UseCase\Notification\Domain\Notification;
use App\UseCase\Notification\Domain\NotificationFactory;
use App\UseCase\Notification\Domain\Service\DomainNotificationChannelProvider;
use App\UseCase\Notification\Domain\Status;
use App\UseCase\Notification\Domain\User;
use App\UseCase\Notification\Infrastructure\ChannelProvider\email\Response\EmailFailureNotificationResponse;
use App\UseCase\Notification\Infrastructure\ChannelProvider\email\Response\EmailSuccessNotificationResponse;
use PHPUnit\Framework\TestCase;

class EmailNotificationTest extends TestCase
{
    public function testSendEmailNotificationSuccessfully(): void
    {
        $notificationId = uniqid();
        $content = 'Some email message';
        $subject = 'Some email subject';
        $user = new User('electra$AS@gmail.com', '+48000000000');

        $notificationFactory = new NotificationFactory();
        $emailNotification = $notificationFactory->createNotification(
            $notificationId,
            'email',
            $user,
            $content,
            $subject
        );

        $notificationProvider = $this->createMock(DomainNotificationChannelProvider::class);
        $notificationProvider->expects($this->once())->method('send')->willReturn(new EmailSuccessNotificationResponse());
        $emailNotification->sendViaProvider($notificationProvider);

        $this->assertEquals(Channel::EMAIL, $emailNotification->getChannel());
        $this->assertEquals(Status::SENT, $emailNotification->getStatus());
        $this->assertEquals($content, $emailNotification->getContent());
        $this->assertEquals($subject, $emailNotification->getSubject());
    }

    public function testEmailNotificationFails(): void
    {
        $notificationId = uniqid();
        $content = 'Some email message';
        $subject = 'Some email subject';
        $user = new User('electra$AS@gmail.com', '+48000000000');

        $notificationFactory = new NotificationFactory();

        $emailNotification = $notificationFactory->createNotification(
            $notificationId,
            'email',
            $user,
            $content,
            $subject
        );

        $notificationProvider = $this->createMock(DomainNotificationChannelProvider::class);
        $notificationProvider->expects($this->once())->method('send')->willReturn(new EmailFailureNotificationResponse());
        $emailNotification->sendViaProvider($notificationProvider);

        $this->assertEquals(Channel::EMAIL, $emailNotification->getChannel());
        $this->assertEquals(Status::FAILED, $emailNotification->getStatus());
        $this->assertEquals($content, $emailNotification->getContent());
        $this->assertEquals($subject, $emailNotification->getSubject());
    }

    public function testSentNotificationCannotBeResend(): void
    {
        $notificationId = uniqid();
        $content = 'Some email message';
        $subject = 'Some email subject';
        $user = new User('electra$AS@gmail.com', '+48000000000');

        $notificationFactory = new NotificationFactory();

        $emailNotification = $notificationFactory->createNotification(
            $notificationId,
            'email',
            $user,
            $content,
            $subject
        );

        $notificationProvider = $this->createMock(DomainNotificationChannelProvider::class);
        $notificationProvider->expects($this->once())->method('send')->willReturn(new EmailSuccessNotificationResponse());
        $emailNotification->sendViaProvider($notificationProvider);

        try {
            $emailNotification->sendViaProvider($notificationProvider);
        } catch (\Exception $exception) {
            $this->assertStringContainsString('Cannot send notification with', $exception->getMessage());
        }
    }

    public function testNotificationCannotBeResendIfMaxAttemptsReached(): void
    {
        $notificationId = uniqid();
        $content = 'Some email message';
        $subject = 'Some email subject';
        $user = new User('electra$AS@gmail.com', '+48000000000');

        $notificationFactory = new NotificationFactory();

        $emailNotification = $notificationFactory->createNotification(
            $notificationId,
            'email',
            $user,
            $content,
            $subject
        );

        $notificationProvider = $this->createMock(DomainNotificationChannelProvider::class);
        $notificationProvider->method('send')->willReturn(new EmailFailureNotificationResponse());
        $maxAttemptsReached = Notification::MAX_ATTEMPTS + 1;

        try {
            for ($i = 0; $i < $maxAttemptsReached; ++$i) {
                $emailNotification->sendViaProvider($notificationProvider);
            }
        } catch (\Exception $exception) {
            $this->assertStringContainsString('Maximum number of sending attempts reached for notification id', $exception->getMessage());
        }
    }
}
