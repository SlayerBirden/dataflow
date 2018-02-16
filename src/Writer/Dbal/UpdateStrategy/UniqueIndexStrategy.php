<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Writer\Dbal\UpdateStrategy;

use SlayerBirden\DataFlow\DataBagInterface;
use SlayerBirden\DataFlow\Writer\Dbal\WriterUtilityInterface;
use SlayerBirden\DataFlow\Writer\Dbal\UpdateStrategyInterface;

class UniqueIndexStrategy implements UpdateStrategyInterface
{
    /**
     * @var string
     */
    private $table;
    /**
     * @var WriterUtilityInterface
     */
    private $utility;

    public function __construct(string $table, WriterUtilityInterface $utility)
    {
        $this->table = $table;
        $this->utility = $utility;
    }

    /**
     * Use table unique keys.
     *
     * {@inheritdoc}
     */
    public function getRecordIdentifier(DataBagInterface $dataBag): array
    {
        $identifiers = [];
        $indices = $this->utility->getUniqueKeys($this->table);
        foreach ($indices as $index) {
            foreach ($index->getColumns() as $column) {
                if (isset($dataBag[$column])) {
                    $identifiers[$column] = $dataBag[$column];
                } else {
                    $identifiers = [];
                    break;
                }
            }
        }
        return $identifiers;
    }
}
