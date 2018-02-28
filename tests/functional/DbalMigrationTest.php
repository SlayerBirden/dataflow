<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Test\Functional;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDOConnection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use PHPUnit\DbUnit\Operation\Factory;
use PHPUnit\DbUnit\TestCase;
use SlayerBirden\DataFlow\DataBagInterface;
use SlayerBirden\DataFlow\Emitter\BlackHole;
use SlayerBirden\DataFlow\Pipe\FilterCallbackInterface;
use SlayerBirden\DataFlow\Pipe\MapperCallbackInterface;
use SlayerBirden\DataFlow\PipelineBuilder;
use SlayerBirden\DataFlow\Plumber;
use SlayerBirden\DataFlow\Provider\Dbal;
use SlayerBirden\DataFlow\Test\Functional\Exception\ConnectionException;

class DbalMigrationTest extends TestCase
{
    /**
     * @var Connection
     */
    private $connection;
    private $emitter;
    private $pipeline;

    protected function setUp(): void
    {
        $params = require __DIR__ . '/config/db-config.php';
        $this->connection = DriverManager::getConnection($params);

        $schema = new Schema();
        // create Heroes Table
        $table = $schema->createTable('heroes');
        $table->addColumn('id', 'integer', [
            'autoincrement' => true,
        ]);
        $table->addColumn('name', 'string');
        $table->addColumn('code', 'string');
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['code']);

        // create Users Table
        $table = $schema->createTable('users');
        $table->addColumn('id', 'integer', [
            'autoincrement' => true,
        ]);
        $table->addColumn('first', 'string');
        $table->addColumn('last', 'string');
        $table->addColumn('code', 'string');
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['code']);

        $currentSchema = $this->connection->getSchemaManager()->createSchema();
        $sql = $currentSchema->getMigrateToSql($schema, $this->connection->getDatabasePlatform());
        array_walk($sql, function ($script) {
            $this->connection->executeUpdate($script);
        });

        parent::setUp();
        $this->emitter = new BlackHole();
        $this->pipeline = (new PipelineBuilder($this->emitter))
            ->delete(['id'])
            ->filter(new class implements FilterCallbackInterface
            {
                public function __invoke(DataBagInterface $dataBag): bool
                {
                    return !empty($dataBag['code']);
                }
            })
            ->map('name', new class implements MapperCallbackInterface
            {
                public function __invoke($value, ?DataBagInterface $dataBag = null)
                {
                    return $dataBag['first'] . ' ' . $dataBag['last'];
                }
            })
            ->dbalWrite('heroes', $this->connection)
            ->getPipeline();
    }

    /**
     * @inheritdoc
     */
    protected function getConnection()
    {
        $con = $this->connection->getWrappedConnection();
        if ($con instanceof PDOConnection) {
            return $this->createDefaultDBConnection($con);
        }

        throw new ConnectionException('Could not get PDO object.');
    }

    protected function getTearDownOperation()
    {
        return Factory::TRUNCATE();
    }

    /**
     * @inheritdoc
     */
    protected function getDataSet()
    {
        return $this->createArrayDataSet([
            'users' => [
                [
                    'id' => 1,
                    'first' => 'John',
                    'last' => 'Doe',
                    'code' => ''
                ],
                [
                    'id' => 2,
                    'first' => 'Peter',
                    'last' => 'Parker',
                    'code' => 'spiderman'
                ],
            ],
            'heroes' => [],
        ]);
    }

    public function testDbSimplePipeFlow()
    {
        $provider = new Dbal('db_users', $this->connection, 'users');

        (new Plumber($provider, $this->pipeline, $this->emitter))->pour();

        $actual = $this->getConnection()->createQueryTable('heroes', 'SELECT * FROM `heroes`');
        $expected = $this->createArrayDataSet([
            'heroes' => [
                [
                    'id' => 1,
                    'name' => 'Peter Parker',
                    'code' => 'spiderman',
                ],
            ]
        ]);

        $this->assertTablesEqual($expected->getTable('heroes'), $actual);
    }

    public function testGetSize()
    {
        $provider = new Dbal('db_users', $this->connection, 'users');

        $this->assertSame(2, $provider->getEstimatedSize());
    }
}
