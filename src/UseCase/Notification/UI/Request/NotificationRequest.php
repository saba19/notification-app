<?php

namespace App\UseCase\Notification\UI\Request;

use Symfony\Component\Validator\Constraints as Assert;

class NotificationRequest
{
    private function __construct(
        #[Assert\NotBlank]
        #[Assert\Type(
            type: 'string',
            message: 'The value {{ value }} is not a valid {{ type }} for recipient field.',
        )]
        public ?string $recipientId,

        #[Assert\NotBlank]
        #[Assert\Type(
            type: 'string',
            message: 'The value {{ value }} is not a valid {{ type }}.',
        )]
        public ?string $channel,

        #[Assert\NotBlank]
        #[Assert\Type(
            type: 'string',
            message: 'The value {{ value }} is not a valid {{ type }}.',
        )]
        public ?string $content,

        public ?string $subject,
    ) {
    }

    /**
     * @param array{
     *     recipient?: string|null,
     *     channel?: string|null,
     *     content?: string|null,
     *     subject?: string|null
     * } $request
     */
    public static function fromArray(array $request): self
    {
        return new self(
            $request['recipient'] ?? null,
            $request['channel'] ?? null,
            $request['content'] ?? null,
            $request['subject'] ?? null,
        );
    }
}
