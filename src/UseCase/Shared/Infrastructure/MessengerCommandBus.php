<?php

namespace App\UseCase\Shared\Infrastructure;

use App\UseCase\Shared\Domain\Bus\Command;
use App\UseCase\Shared\Domain\Bus\CommandBus;
use Symfony\Component\Messenger\MessageBusInterface;

final class MessengerCommandBus implements CommandBus
{
    private MessageBusInterface $commandBus;

    public function __construct(MessageBusInterface $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function dispatch(Command $command): void
    {
        $this->commandBus->dispatch($command);
    }
}
