<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data;

final readonly class ApiResponse
{
    public function __construct(
        public string $status,
        public ?string $message,
        public mixed $data,
        public ?array $meta = null,
    ) {}

    /**
     * Create from API response array
     */
    public static function fromArray(array $response): self
    {
        $message = $response['message'] ?? null;

        // Handle nested error structure
        if (\is_array($message)) {
            $message = $message['message'] ?? null;
        }

        // Ensure message is string or null
        if (! \is_string($message) && $message !== null) {
            $message = null;
        }

        return new self(
            status: $response['status'] ?? 'unknown',
            message: $message,
            data: $response['data'] ?? null,
            meta: $response['meta'] ?? null,
        );
    }

    /**
     * Check if the response was successful
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    /**
     * Check if the response was an error
     */
    public function isError(): bool
    {
        return ! $this->isSuccessful();
    }

    /**
     * Get the error message if response failed
     */
    public function getErrorMessage(): ?string
    {
        return $this->isError() ? $this->message : null;
    }

    /**
     * Get data as array
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'message' => $this->message,
            'data' => $this->data,
            'meta' => $this->meta,
        ];
    }
}
