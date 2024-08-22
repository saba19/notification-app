<?php

namespace App\UseCase\Notification\Infrastructure\ChannelProvider\email;

use App\UseCase\Notification\Domain\Notification;
use App\UseCase\Notification\Domain\NotificationResponse;
use App\UseCase\Notification\Domain\Service\EmailDomainNotificationChannelProvider;
use App\UseCase\Notification\Infrastructure\ChannelProvider\email\Response\EmailFailureNotificationResponse;
use App\UseCase\Notification\Infrastructure\ChannelProvider\email\Response\EmailSuccessNotificationResponse;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class SendgridProvider implements EmailDomainNotificationChannelProvider
{
    public function __construct(
        private string $sendgridSender, private MailerInterface $mailer)
    {
    }

    public function send(Notification $notification): NotificationResponse
    {
        try {
            $email = (new Email())
                ->to($notification->getRecipientEmail())
                ->sender($this->sendgridSender)
                ->subject($notification->getSubject())
                ->text($notification->getRecipientEmail());

            $this->mailer->send($email);
        } catch (\Exception|TransportExceptionInterface $exception) {
            return new EmailFailureNotificationResponse();
        }

        return new EmailSuccessNotificationResponse();
    }
}
