<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Emitter;

use SlayerBirden\DataFlow\EmitterInterface;

class BlackHole implements EmitterInterface
{
    public function emit(string $event, ...$args): void
    {
    }
}
