<?php

namespace App\UseCase\Notification\Domain;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OneToMany;

#[ORM\Entity]
#[ORM\Table(name: '`users`')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column(length: 13)]
    private readonly string $id;

    #[ORM\Column(length: 255, unique: true)]
    private readonly string $email;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phoneNumber;

    #[OneToMany(targetEntity: Notification::class, mappedBy: 'user')]
    /**
     * @phpstan-ignore-next-line
     */
    private Collection $notifications;

    public function __construct(string $email, ?string $phoneNumber = null)
    {
        $this->id = uniqid();
        $this->email = $email;
        $this->phoneNumber = $phoneNumber;
        $this->notifications = new ArrayCollection();
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    public function getId(): string
    {
        return $this->id;
    }
}
