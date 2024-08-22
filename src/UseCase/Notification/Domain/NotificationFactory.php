<?php

namespace App\UseCase\Notification\Domain;

use App\UseCase\Notification\Exception\NotSupportedChannelException;

class NotificationFactory
{
    /** @throws NotSupportedChannelException */
    public function createNotification(
        string $notificationId,
        string $type,
        User $recipient,
        string $content,
        ?string $subject
    ): Notification {
        switch ($type) {
            case Channel::EMAIL->value:
                return Notification::fromEmail($notificationId, $recipient, $content, $subject);
            case Channel::PUSH->value:
                return Notification::fromPush($notificationId, $recipient, $content);
            case Channel::SMS->value:
                return Notification::fromSms($notificationId, $recipient, $content);
            default: throw NotSupportedChannelException::withChannel($type);
        }
    }
}
