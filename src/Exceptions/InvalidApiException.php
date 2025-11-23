<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Exceptions;

use Exception;

final class InvalidApiException extends Exception
{
    public function __construct(string $message = 'Invalid API')
    {
        parent::__construct($message);
    }
}
