<?php

namespace App\UseCase\Notification\Exception;

class UserNotFoundException extends NotificationException
{
    public static function withId(string $userId): self
    {
        return new self(
            sprintf(
                'User with id %s not found.',
                $userId,
            )
        );
    }
}
