<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Writer\Dbal;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use SlayerBirden\DataFlow\DataBagInterface;
use SlayerBirden\DataFlow\HandlerInterface;
use SlayerBirden\DataFlow\IdentificationTrait;
use SlayerBirden\DataFlow\Writer\WriteCallbackInterface;

class Write implements HandlerInterface
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
        foreach ($columns as $column) {
            if (isset($dataBag[$column->getName()])) {
                $dataToInsert[$column->getName()] = $column->getType()->convertToDatabaseValue(
                    $dataBag[$column->getName()],
                    $this->connection->getDatabasePlatform()
                );
            }
        }
        if ($this->recordExists($dataBag)) {
            $inserted = $this->connection->update(
                $this->table,
                $dataToInsert,
                $this->updateStrategy->getRecordIdentifier($dataBag)
            );
        } else {
            $inserted = $this->connection->insert($this->table, $dataToInsert);
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
                    sprintf('Could not resolve 1 entry using given predicate: %s', json_encode($id))
                );
            }
            return $count === 1;
        }
        return false;
    }
}
