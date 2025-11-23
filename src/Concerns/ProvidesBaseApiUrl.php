<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Concerns;

trait ProvidesBaseApiUrl
{
    use RecognizesEnvironment;

    public function getBaseApiUrl(): string
    {
        return $this->recognizeEnvironment()->getBaseUrl();
    }

    public function buildApiSpecificBaseUrl(): string
    {
        return $this->getBaseApiUrl().$this->endpoint;
    }
}
