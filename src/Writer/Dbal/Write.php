<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Writer\Dbal;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use SlayerBirden\DataFlow\DataBagInterface;
use SlayerBirden\DataFlow\EmitterInterface;
use SlayerBirden\DataFlow\PipeInterface;
use SlayerBirden\DataFlow\IdentificationTrait;

class Write implements PipeInterface
{
    use IdentificationTrait;
    /**
     * @var string
     */
    private $identifier;
    /**
     * @var Connection
     */
    private $connection;
    /**
     * @var string
     */
    private $table;
    /**
     * @var AutoIncrementCallbackInterface|null
     */
    private $callback;
    /**
     * @var WriterUtilityInterface
     */
    private $utility;
    /**
     * @var UpdateStrategyInterface
     */
    private $updateStrategy;
    /**
     * @var EmitterInterface
     */
    private $emitter;
    /**
     * @var string|null
     */
    private $autoIncrementColumn;

    public function __construct(
        string $identifier,
        Connection $connection,
        string $table,
        WriterUtilityInterface $utility,
        UpdateStrategyInterface $updateStrategy,
        EmitterInterface $emitter,
        ?AutoIncrementCallbackInterface $callback = null
    ) {
        $this->identifier = $identifier;
        $this->connection = $connection;
        $this->table = $table;
        $this->callback = $callback;
        $this->utility = $utility;
        $this->updateStrategy = $updateStrategy;
        $this->emitter = $emitter;
    }

    /**
     * DBAL Insert statement.
     * Inserts data into a table using DBAL.
     *
     * {@inheritdoc}
     */
    public function pass(DataBagInterface $dataBag): DataBagInterface
    {
        $dataToInsert = $this->getDataToInsert($dataBag);
        if ($this->recordExists($dataBag)) {
            $this->updateRecord($dataToInsert, $dataBag);
        } else {
            $this->insertRecord($dataToInsert, $dataBag);
        }

        return $dataBag;
    }

    private function insertRecord(array $dataToInsert, DataBagInterface $dataBag)
    {
        $autoIncrementColumn = $this->getAutoIncrementColumn();
        $this->connection->insert($this->table, $dataToInsert);
        $this->emitter->emit('record_insert', $this->table, $dataBag);
        if ($autoIncrementColumn && $this->callback) {
            $id = (int)$this->connection->lastInsertId();
            ($this->callback)($id, $dataBag);
        }
    }

    /**
     * @param array $dataToInsert
     * @param DataBagInterface $dataBag
     * @throws DBALException
     */
    private function updateRecord(array $dataToInsert, DataBagInterface $dataBag)
    {
        $identifier = $this->updateStrategy->getRecordIdentifier($dataBag);
        $autoIncrementColumn = $this->getAutoIncrementColumn();
        $this->connection->update(
            $this->table,
            $dataToInsert,
            $identifier
        );
        $this->emitter->emit('record_update', $this->table, $dataBag);
        if ($autoIncrementColumn && $this->callback) {
            $id = $this->getRecordId($identifier, $autoIncrementColumn);

            ($this->callback)($id, $dataBag);
        }
    }

    private function getDataToInsert(DataBagInterface $dataBag): array
    {
        $columns = $this->utility->getColumns($this->table);
        $dataToInsert = [];
        foreach ($columns as $column) {
            if (isset($dataBag[$column->getName()])) {
                $dataToInsert[$column->getName()] = $column->getType()->convertToDatabaseValue(
                    $dataBag[$column->getName()],
                    $this->connection->getDatabasePlatform()
                );
            }
        }

        return $dataToInsert;
    }

    /**
     * @return null|string
     */
    private function getAutoIncrementColumn(): ?string
    {
        if ($this->autoIncrementColumn === null) {
            $columns = $this->utility->getColumns($this->table);
            foreach ($columns as $column) {
                if ($column->getAutoincrement()) {
                    $this->autoIncrementColumn = $column->getName();
                }
            }
        }

        return $this->autoIncrementColumn;
    }

    /**
     * Get auto-increment field value of the record using given id.
     *
     * @param array $identifier
     * @param string $autoIncrementColumn
     * @return int
     * @throws DBALException
     */
    public function getRecordId(array $identifier, string $autoIncrementColumn): int
    {
        if (isset($identifier[$autoIncrementColumn])) {
            return (int)$identifier[$autoIncrementColumn];
        }
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->select($autoIncrementColumn)
            ->from($this->table)
            ->setParameters($identifier);
        foreach (array_keys($identifier) as $key) {
            $queryBuilder->andWhere("$key = :$key");
        }
        $stmt = $this->connection->prepare($queryBuilder->getSQL());
        $stmt->execute($identifier);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Checks if record already exists in the DB.
     *
     * @param DataBagInterface $dataBag
     * @return bool
     * @throws DBALException
     */
    private function recordExists(DataBagInterface $dataBag): bool
    {
        $id = $this->updateStrategy->getRecordIdentifier($dataBag);

        if (!empty($id)) {
            $queryBuilder = $this->connection->createQueryBuilder();
            $queryBuilder->select('count(*)')
                ->from($this->table)
                ->setParameters($id);
            foreach (array_keys($id) as $key) {
                $queryBuilder->andWhere("$key = :$key");
            }
            $stmt = $this->connection->prepare($queryBuilder->getSQL());
            $stmt->execute($id);
            $count = (int)$stmt->fetchColumn();
            if ($count > 1) {
                throw new InvalidIdentificationException(
                    sprintf('Could not narrow results to 1 entry using given predicate: %s', json_encode($id))
                );
            }
            return $count === 1;
        }
        return false;
    }
}
