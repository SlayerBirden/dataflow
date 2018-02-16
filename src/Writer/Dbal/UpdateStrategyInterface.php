<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Writer\Dbal;

use SlayerBirden\DataFlow\DataBagInterface;

interface UpdateStrategyInterface
{
    /**
     * Get a unique identifier as key => value pair for given record.
     *
     * @param DataBagInterface $dataBag
     * @return array
     */
    public function getRecordIdentifier(DataBagInterface $dataBag): array;
}
