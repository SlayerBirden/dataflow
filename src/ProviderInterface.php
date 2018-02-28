<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow;

interface ProviderInterface
{
    /**
     * @return \Generator|DataBagInterface[]
     */
    public function getCask(): \Generator;

    public function getIdentifier(): string;

    /**
     * Estimate number of entries in the provider.
     *
     * @return int
     */
    public function getEstimatedSize(): int;
}
