<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Provider;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use SlayerBirden\DataFlow\Data\SimpleBag;
use SlayerBirden\DataFlow\DataBagInterface;
use SlayerBirden\DataFlow\IdentificationTrait;
use SlayerBirden\DataFlow\Provider\Exception\ProviderException;
use SlayerBirden\DataFlow\ProviderInterface;

class Dbal implements ProviderInterface
{
    use IdentificationTrait;

    const LIMIT = 100;
    /**
     * @var string
     */
    private $id;
    /**
     * @var Connection
     */
    private $connection;
    /**
     * @var string
     */
    private $table;

    public function __construct(string $id, Connection $connection, string $table)
    {
        $this->id = $id;
        $this->connection = $connection;
        $this->table = $table;
    }

    /**
     * @return \Generator|DataBagInterface[]
     */
    public function getCask(): \Generator
    {
        $offset = 0;
        $qb = $this->connection->createQueryBuilder();
        do {
            $qb->select('*')
                ->from($this->table)
                ->setFirstResult($offset)
                ->setMaxResults(self::LIMIT);
            try {
                $stmt = $this->connection->query($qb->getSQL());
            } catch (DBALException $e) {
                yield new ProviderException($e->getMessage(), $e->getCode(), $e);
            }
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $count = count($result);
            if ($count) {
                foreach ($result as $row) {
                    yield new SimpleBag($row);
                }
                $offset += self::LIMIT;
            }
        } while ($count > 0);
    }

    /**
     * @inheritdoc
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getEstimatedSize(): int
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('count(*)')->from($this->table);

        $stmt = $this->connection->query($qb->getSQL());
        return (int)$stmt->fetchColumn();
    }
}
