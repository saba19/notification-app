<?php

namespace App\UseCase\Notification\Domain;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: '`notification_providers`')]
class NotificationProvider
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column(length: 13)]
    private readonly string $id;

    #[ORM\Column(length: 255, unique: true)]
    private Provider $name;
    #[ORM\Column(length: 255)]
    private readonly Channel $channel;

    #[ORM\Column]
    private bool $enabled;

    #[ORM\Column]
    private int $position;

    public function __construct(Provider $name, Channel $channel, bool $enabled, int $position)
    {
        $this->id = uniqid();
        $this->name = $name;
        $this->channel = $channel;
        $this->enabled = $enabled;
        $this->position = $position;
    }

    public function getName(): Provider
    {
        return $this->name;
    }

    public function getChannel(): Channel
    {
        return $this->channel;
    }

    public function enabled(): bool
    {
        return $this->enabled;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getId(): string
    {
        return $this->id;
    }
}
