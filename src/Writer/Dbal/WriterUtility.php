<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Writer\Dbal;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Index;

class WriterUtility implements WriterUtilityInterface
{
    /**
     * @var Connection
     */
    private $connection;
    private $columns = [];
    private $keys = [];

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getColumns(string $table): array
    {
        if (!isset($this->columns[$table])) {
            $this->columns[$table] = $this->connection->getSchemaManager()->listTableColumns($table);
        }
        return $this->columns[$table];
    }

    public function getUniqueKeys(string $table): array
    {
        if (!isset($this->keys[$table])) {
            $allKeys = $this->connection->getSchemaManager()->listTableIndexes($table);
            $this->keys[$table] = array_filter($allKeys, function (Index $index) {
                return $index->isUnique() || $index->isPrimary();
            });
        }
        return $this->keys[$table];
    }
}
