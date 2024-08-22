<?php

namespace App\UseCase\Notification\Domain;

use App\UseCase\Notification\Domain\Service\DomainNotificationChannelProvider;
use App\UseCase\Notification\Exception\MaxAttemptsExceededException;
use App\UseCase\Notification\Exception\NotificationCannotBeSentException;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[ORM\Entity]
#[ORM\Table(name: '`notifications`')]
class Notification
{
    public const int MAX_ATTEMPTS = 10;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column(length: 13)]
    private readonly string $id;

    #[ManyToOne(targetEntity: User::class, inversedBy: 'notifications')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', )]
    private User $user;

    #[ORM\Column(length: 255)]
    private readonly Channel $channel;

    #[ORM\Column(length: 255)]
    private Status $status;

    #[ORM\Column(length: 100, nullable: true)]
    private readonly ?string $subject;

    #[ORM\Column]
    private int $sendingAttempts;

    #[ORM\Column(type: Types::TEXT, length: 255, nullable: true)]
    private readonly string $content;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt; // @phpstan-ignore property.onlyWritten

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $sentAt; // @phpstan-ignore property.onlyWritten

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $lastFailureAttemptAt; // @phpstan-ignore property.onlyWritten

    private function __construct(
        string $id,
        Channel $channel,
        User $recipient,
        string $content,
        ?string $subject,
    ) {
        $this->id = $id;
        $this->channel = $channel;
        $this->subject = $subject;
        $this->user = $recipient;
        $this->content = $content;
        $this->createdAt = new \DateTimeImmutable();
        $this->sentAt = null;
        $this->sendingAttempts = 0;
        $this->status = Status::CREATED;
        $this->lastFailureAttemptAt = null;
    }

    /**
     * @throws NotificationCannotBeSentException
     * @throws MaxAttemptsExceededException
     */
    public function sendViaProvider(DomainNotificationChannelProvider $channelProvider): void
    {
        $this->assertCanSend();

        $response = $channelProvider->send($this);
        $this->increaseSendingAttempts();
        if ($response instanceof SuccessNotificationResponse) {
            $this->markAsSent();

            return;
        }

        $this->markAsFailed();
    }

    private function assertCanSend(): void
    {
        if (Status::SENT == $this->status) {
            throw NotificationCannotBeSentException::withId($this->id);
        }

        if ($this->sendingAttempts >= self::MAX_ATTEMPTS) {
            throw MaxAttemptsExceededException::withId($this->id);
        }
    }

    public static function fromSms(string $notificationId, User $recipient, string $content): self
    {
        return new self($notificationId, Channel::SMS, $recipient, $content, null);
    }

    public static function fromPush(string $notificationId, User $recipient, string $content): self
    {
        return new self($notificationId, Channel::PUSH, $recipient, $content, null);
    }

    public static function fromEmail(string $notificationId, User $recipient, string $content, string $subject): self
    {
        return new self($notificationId, Channel::EMAIL, $recipient, $content, $subject);
    }

    public function markAsSent(): void
    {
        $this->status = Status::SENT;
        $this->sentAt = new \DateTimeImmutable();
    }

    public function markAsFailed(): void
    {
        $this->status = Status::FAILED;
        $this->lastFailureAttemptAt = new \DateTimeImmutable();
    }

    private function increaseSendingAttempts(): void
    {
        ++$this->sendingAttempts;
    }

    public function getChannel(): Channel
    {
        return $this->channel;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function getRecipientPhoneNumber(): ?string
    {
        return $this->user->getPhoneNumber();
    }

    public function getRecipientEmail(): ?string
    {
        return $this->user->getEmail();
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function canBeSend(): bool
    {
        return Status::SENT !== $this->status && !$this->maxSendingAttemptsReached();
    }

    // temporary setter only for testing purpose
    // To replace the date time provider
    public function setLastFailureAttemptAt(?\DateTimeImmutable $lastFailureAttemptAt): void
    {
        $this->lastFailureAttemptAt = $lastFailureAttemptAt;
    }

    public function maxSendingAttemptsReached(): bool
    {
        return $this->sendingAttempts >= self::MAX_ATTEMPTS;
    }
}
