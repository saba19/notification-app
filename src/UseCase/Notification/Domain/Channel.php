<?php

namespace App\UseCase\Notification\Domain;

enum Channel: string
{
    case SMS = 'sms';
    case PUSH = 'push';
    case EMAIL = 'email';
}
