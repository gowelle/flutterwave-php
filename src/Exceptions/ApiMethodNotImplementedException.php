<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Exceptions;

use Exception;

final class ApiMethodNotImplementedException extends Exception
{
    public function __construct(string $message = 'API method not implemented')
    {
        parent::__construct($message);
    }
}
