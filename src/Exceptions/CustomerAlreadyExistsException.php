<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Exceptions;

final class CustomerAlreadyExistsException extends FlutterwaveException
{
    public static function withEmail(string $email): self
    {
        return new self("A Flutterwave customer with email {$email} already exists.");
    }
}
