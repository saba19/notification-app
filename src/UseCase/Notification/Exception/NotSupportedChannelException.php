<?php

namespace App\UseCase\Notification\Exception;

final class NotSupportedChannelException extends NotificationException
{
    public static function withChannel(string $channel): self
    {
        return new self(
            sprintf(
                'Not supported notification channel "%s"',
                $channel
            )
        );
    }
}
