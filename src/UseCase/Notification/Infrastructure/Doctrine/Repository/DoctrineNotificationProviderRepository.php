<?php

namespace App\UseCase\Notification\Infrastructure\Doctrine\Repository;

use App\UseCase\Notification\Domain\NotificationProvider;
use App\UseCase\Notification\Domain\Provider;
use App\UseCase\Notification\Domain\Repository\NotificationProviderRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DoctrineNotificationProviderRepository extends ServiceEntityRepository implements NotificationProviderRepository // @phpstan-ignore missingType.generics
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NotificationProvider::class);
    }

    public function save(NotificationProvider $notificationProvider): void
    {
        $this->getEntityManager()->persist($notificationProvider);
        $this->getEntityManager()->flush();
    }

    public function isEnabled(Provider $name): bool
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('n')
            ->from(NotificationProvider::class, 'n')
            ->andWhere('n.name = :name')
            ->setParameter('name', $name)
            ->andWhere('n.enabled = :enabled')
            ->setParameter('enabled', 1);

        return (bool) $qb->getQuery()->getOneOrNullResult();
    }

    public function getByName(Provider $name): NotificationProvider
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('n')
            ->from(NotificationProvider::class, 'n')
            ->andWhere('n.name = :name')
            ->setParameter('name', $name);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
