<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Writer\Dbal;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use SlayerBirden\DataFlow\DataBagInterface;
use SlayerBirden\DataFlow\HandlerInterface;
use SlayerBirden\DataFlow\Writer\WriteCallbackInterface;

class Write implements HandlerInterface
{
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
     * @var WriteCallbackInterface|null
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

    public function __construct(
        string $identifier,
        Connection $connection,
        string $table,
        WriterUtilityInterface $utility,
        UpdateStrategyInterface $updateStrategy,
        ?WriteCallbackInterface $callback = null
    ) {
        $this->identifier = $identifier;
        $this->connection = $connection;
        $this->table = $table;
        $this->callback = $callback;
        $this->utility = $utility;
        $this->updateStrategy = $updateStrategy;
    }

    /**
     * DBAL Insert statement.
     * Inserts data into a table using DBAL.
     *
     * {@inheritdoc}
     */
    public function handle(DataBagInterface $dataBag): DataBagInterface
    {
        $columns = $this->utility->getColumns($this->table);
        $dataToInsert = [];
        $inserted = 0;
        foreach ($columns as $column) {
            if (isset($dataBag[$column->getName()])) {
                $dataToInsert[$column->getName()] = $column->getType()->convertToDatabaseValue(
                    $dataBag[$column->getName()],
                    $this->connection->getDatabasePlatform()
                );
            }
        }
        try {
            if ($this->recordExists($dataBag)) {
                $inserted = $this->connection->update(
                    $this->table,
                    $dataToInsert,
                    $this->updateStrategy->getRecordIdentifier($dataBag)
                );
            } else {
                $inserted = $this->connection->insert($this->table, $dataToInsert);
            }
        } catch (DBALException $exception) {
        }

        // TODO log
        if ($this->callback) {
            $id = $this->connection->lastInsertId();
            ($this->callback)($inserted, $dataBag, $id);
        }

        return $dataBag;
    }

    /**
     * Checks if record already exists in the DB.
     *
     * @param DataBagInterface $dataBag
     * @return bool
     */
    private function recordExists(DataBagInterface $dataBag): bool
    {
        $id = $this->updateStrategy->getRecordIdentifier($dataBag);

        if (!empty($id)) {
            try {
                $queryBuilder = $this->connection->createQueryBuilder();
                $queryBuilder->select('count(*)')
                    ->from($this->table)
                    ->setParameters($id);
                foreach (array_keys($id) as $key) {
                    $queryBuilder->andWhere("$key = :$key");
                }
                $stmt = $this->connection->query($queryBuilder->getSQL());
                $stmt->execute();
                $count = (int)$stmt->fetchColumn();
                // todo throw invalid count
                return $count === 1;
            } catch (DBALException $e) {
            }
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}
