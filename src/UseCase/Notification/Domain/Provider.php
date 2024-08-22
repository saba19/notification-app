<?php

namespace App\UseCase\Notification\Domain;

enum Provider: string
{
    case TWILIO = 'twilio';
    case FAKE_SMS = 'fake_sms';
    case SENDGRID = 'sendgrid';
    case FAKE_EMAIL = 'fake_email';
}
