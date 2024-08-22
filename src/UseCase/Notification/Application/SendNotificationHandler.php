<?php

namespace App\UseCase\Notification\Application;

use App\UseCase\Shared\Domain\Bus\CommandHandler;

interface SendNotificationHandler extends CommandHandler
{
    public function __invoke(SendNotification $command): void;
}
