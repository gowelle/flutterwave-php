<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Infrastructure;

use Gowelle\Flutterwave\Data\ApiResponse;

interface FlutterwaveApiContract
{
    public function list(): ApiResponse;

    public function create(array $data): ApiResponse;

    public function retrieve(string $id): ApiResponse;

    public function update(string $id, array $data): ApiResponse;

    public function search(array $data): ApiResponse;

    public function buildApiSpecificBaseUrl(): string;
}
