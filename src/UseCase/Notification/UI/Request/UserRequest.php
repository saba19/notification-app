<?php

namespace App\UseCase\Notification\UI\Request;

use Symfony\Component\Validator\Constraints as Assert;

class UserRequest
{
    private function __construct(
        #[Assert\NotBlank]
        #[Assert\Type(
            type: 'string',
            message: 'The value {{ value }} is not a valid {{ type }} for recipient field.',
        )]
        public ?string $email,

        #[Assert\NotBlank]
        #[Assert\Type(
            type: 'string',
            message: 'The value {{ value }} is not a valid {{ type }}.',
        )]
        public ?string $phoneNumber,
    ) {
    }

    /**
     * @param array{
     *     email?: string|null,
     *     phoneNumber?: string|null
     * } $request
     */
    public static function fromArray(array $request): self
    {
        return new self(
            $request['email'] ?? null,
            $request['phoneNumber'] ?? null,
        );
    }
}
