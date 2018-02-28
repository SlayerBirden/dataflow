<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow;

trait IdentificationTrait
{
    public function getIdentifier(): string
    {
        if (property_exists($this, 'identifier')) {
            return $this->identifier;
        }
        if (property_exists($this, 'id')) {
            return $this->id;
        }

        return uniqid();
    }
}
