<?php

namespace App\UseCase\Notification\Domain;

enum Status: string
{
    case CREATED = 'created';
    case FAILED = 'failed';
    case SENT = 'sent';
}
