<?php

namespace App\UseCase\Notification\Exception;

class MaxAttemptsExceededException extends NotificationException
{
    public static function withId(string $id): self
    {
        return new self(
            sprintf(
                'Maximum number of sending attempts reached for notification id "%s."',
                $id
            )
        );
    }
}
