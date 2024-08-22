<?php

namespace App\UseCase\Notification\Exception;

class NotificationCannotBeSentException extends NotificationException
{
    public static function withId(string $id): self
    {
        return new self(
            sprintf(
                'Cannot send notification with id "%s" and status sent',
                $id
            )
        );
    }
}
