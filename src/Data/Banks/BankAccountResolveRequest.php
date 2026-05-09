<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\Banks;

use InvalidArgumentException;

/**
 * Request DTO for resolving bank account details via the Flutterwave API.
 *
 * @see https://developer.flutterwave.com/reference/banks_account_resolve
 */
final readonly class BankAccountResolveRequest
{
    /**
     * @param  array<string, mixed>  $account
     */
    private function __construct(
        public string $variant,
        public array $account,
    ) {
        $this->validate();
    }

    /**
     * Create an NGN bank account lookup request.
     */
    public static function forNgn(string $bankCode, string $accountNumber): self
    {
        return new self(
            variant: 'ngn',
            account: [
                'code' => $bankCode,
                'number' => $accountNumber,
            ],
        );
    }

    /**
     * Create a USD bank account lookup request for Nigerian bank accounts.
     */
    public static function forUsdNg(string $bankCode, string $accountNumber): self
    {
        return new self(
            variant: 'usd_ng',
            account: [
                'code' => $bankCode,
                'number' => $accountNumber,
            ],
        );
    }

    /**
     * Create a GBP corporate bank account lookup request.
     */
    public static function forGbpCorporate(string $bankCode, string $accountNumber, string $businessName): self
    {
        return new self(
            variant: 'gbp_corporate',
            account: [
                'code' => $bankCode,
                'number' => $accountNumber,
                'business_name' => $businessName,
            ],
        );
    }

    /**
     * Create a GBP individual bank account lookup request.
     */
    public static function forGbpIndividual(
        string $bankCode,
        string $accountNumber,
        string $firstName,
        string $lastName,
        ?string $middleName = null,
    ): self
    {
        $name = [
            'first' => $firstName,
            'last' => $lastName,
        ];

        if ($middleName !== null) {
            $name['middle'] = $middleName;
        }

        return new self(
            variant: 'gbp_individual',
            account: [
                'code' => $bankCode,
                'number' => $accountNumber,
                'name' => $name,
            ],
        );
    }

    /**
     * Convert to API payload.
     *
     * @return array{account: array<string, mixed>}
     */
    public function toApiPayload(): array
    {
        return [
            'account' => $this->account,
        ];
    }

    private function validate(): void
    {
        $code = $this->requireString('code');
        $number = $this->requireString('number');

        match ($this->variant) {
            'ngn' => $this->validateNgn($code, $number),
            'usd_ng' => $this->validateUsdNg($code, $number),
            'gbp_corporate' => $this->validateGbpCorporate($code, $number),
            'gbp_individual' => $this->validateGbpIndividual($code, $number),
            default => throw new InvalidArgumentException("Unsupported bank account resolve variant [{$this->variant}]"),
        };
    }

    private function validateNgn(string $code, string $number): void
    {
        $this->assertLength($code, 3, 10, 'account.code');
        $this->assertExactLength($number, 10, 'account.number');
    }

    private function validateUsdNg(string $code, string $number): void
    {
        $this->assertLength($code, 2, 10, 'account.code');
        $this->assertLength($number, 4, 50, 'account.number');
    }

    private function validateGbpCorporate(string $code, string $number): void
    {
        $this->assertLength($code, 2, 10, 'account.code');
        $this->assertLength($number, 4, 50, 'account.number');
        $this->assertLength($this->requireString('business_name'), 2, 50, 'account.business_name');
    }

    private function validateGbpIndividual(string $code, string $number): void
    {
        $this->assertLength($code, 2, 10, 'account.code');
        $this->assertLength($number, 4, 50, 'account.number');

        $name = $this->account['name'] ?? null;
        if (! is_array($name) || $name === []) {
            throw new InvalidArgumentException('account.name must be a non-empty object for GBP individual lookups');
        }

        $this->requireNestedString($name, 'first', 'account.name.first');
        $middle = $name['middle'] ?? null;
        if ($middle !== null && (! is_string($middle) || trim($middle) === '')) {
            throw new InvalidArgumentException('account.name.middle must be a non-empty string when provided');
        }
        $this->requireNestedString($name, 'last', 'account.name.last');
    }

    private function requireString(string $key): string
    {
        $value = $this->account[$key] ?? null;

        if (! is_string($value) || trim($value) === '') {
            throw new InvalidArgumentException("account.{$key} must be a non-empty string");
        }

        return $value;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function requireNestedString(array $data, string $key, string $field): string
    {
        $value = $data[$key] ?? null;

        if (! is_string($value) || trim($value) === '') {
            throw new InvalidArgumentException("{$field} must be a non-empty string");
        }

        return $value;
    }

    private function assertLength(string $value, int $min, int $max, string $field): void
    {
        $length = mb_strlen($value);

        if ($length < $min || $length > $max) {
            throw new InvalidArgumentException("{$field} length must be between {$min} and {$max} characters");
        }
    }

    private function assertExactLength(string $value, int $expected, string $field): void
    {
        if (mb_strlen($value) !== $expected) {
            throw new InvalidArgumentException("{$field} length must be exactly {$expected} characters");
        }
    }
}
