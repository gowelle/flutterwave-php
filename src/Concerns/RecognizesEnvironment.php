<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Concerns;

use Gowelle\Flutterwave\Enums\FlutterwaveEnvironment;

trait RecognizesEnvironment
{
    public function recognizeEnvironment(): FlutterwaveEnvironment
    {
        return FlutterwaveEnvironment::fromConfig();
    }
}
