<?php

namespace App\UseCase\Notification\Application;

use App\UseCase\Shared\Domain\Bus\Command;

final readonly class CreateNotification implements Command
{
    public function __construct(
        private string $notificationId,
        private string $recipientId,
        private string $channel,
        private string $content,
        private ?string $subject,
    ) {
    }

    public function getRecipientId(): string
    {
        return $this->recipientId;
    }

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function getNotificationId(): string
    {
        return $this->notificationId;
    }
}
