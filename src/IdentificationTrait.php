<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow;

trait IdentificationTrait
{
    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}
