<?php

namespace App\UseCase\Notification\Infrastructure\Doctrine\Repository;

use App\UseCase\Notification\Domain\Notification;
use App\UseCase\Notification\Domain\Repository\NotificationRepository;
use App\UseCase\Notification\Domain\Status;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class DoctrineNotificationRepository extends ServiceEntityRepository implements NotificationRepository // @phpstan-ignore missingType.generics
{
    private const int DELAYED_TIME_IN_SEC = 5 * 60;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    public function save(Notification $notification): void
    {
        $this->getEntityManager()->persist($notification);
        $this->getEntityManager()->flush();
    }

    public function getNotificationToResend(): array
    {
        // use cqrs in the future instead
        $delayedTime = new \DateTime();
        $delayedTime->modify('-'.self::DELAYED_TIME_IN_SEC.' second');

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('n')
            ->from(Notification::class, 'n')
            ->andWhere('n.status = :status')
            ->setParameter('status', Status::FAILED)
            ->andWhere('n.sendingAttempts <= :sendingAttempts')
            ->setParameter('sendingAttempts', Notification::MAX_ATTEMPTS)
            ->andWhere(
                $qb->expr()->lt('n.lastFailureAttemptAt', ':delayedTime')
            )
            ->setParameter('delayedTime', $delayedTime)
            ->orderBy('n.createdAt', 'ASC')
            ->setMaxResults(10);

        return $qb->getQuery()->getResult();
    }

    public function getOneById(string $id): Notification
    {
        // todo add exception
        return $this->findOneBy(['id' => $id]);
    }
}
