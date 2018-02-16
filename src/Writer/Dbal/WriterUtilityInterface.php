<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Writer\Dbal;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Index;

interface WriterUtilityInterface
{
    /**
     * Get columns for a table.
     *
     * @param string $table
     * @return Column[]
     */
    public function getColumns(string $table): array;

    /**
     * Get collection of Unique Indices for given table.
     *
     * @param string $table
     * @return Index[]
     */
    public function getUniqueKeys(string $table): array;
}
