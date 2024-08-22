<?php

namespace App\UseCase\Notification\Infrastructure\Doctrine\Repository;

use App\UseCase\Notification\Domain\Repository\UserRepository;
use App\UseCase\Notification\Domain\User;
use App\UseCase\Notification\Exception\UserNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DoctrineUserRepository extends ServiceEntityRepository implements UserRepository // @phpstan-ignore missingType.generics
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /** @throws UserNotFoundException */
    public function getOneById(string $id): User
    {
        return $this->findOneBy(['id' => $id])
            ?? throw UserNotFoundException::withId($id);
    }
}
