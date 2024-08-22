<?php

namespace App\UseCase\Shared\Domain\Bus;

interface CommandBus
{
    public function dispatch(Command $command): void;
}
