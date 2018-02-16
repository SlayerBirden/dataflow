<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow;

interface ProviderInterface
{
    public function provide(): DataBagInterface;

    public function getIdentifier(): string;
}
