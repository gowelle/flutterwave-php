<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Exceptions;

final class WebhookVerificationException extends FlutterwaveException
{
    public static function missingSignature(): self
    {
        return new self('Webhook signature header is missing');
    }

    public static function invalidSignature(): self
    {
        return new self('Invalid webhook signature');
    }

    public static function missingEvent(): self
    {
        return new self('Webhook event type is missing');
    }

    public static function missingData(): self
    {
        return new self('Webhook data is missing');
    }
}
