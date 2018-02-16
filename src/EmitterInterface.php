<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow;

interface EmitterInterface
{
    public function emit(string $event, ...$args): void;
}
