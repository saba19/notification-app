<?php

namespace App\UseCase\Notification\Domain\Repository;

use App\UseCase\Notification\Domain\User;
use App\UseCase\Notification\Exception\UserNotFoundException;

interface UserRepository
{
    /** @throws UserNotFoundException */
    public function getOneById(string $id): User;
}
