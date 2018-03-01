<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Test\Functional;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDOConnection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use PHPUnit\DbUnit\DataSet\IDataSet;
use PHPUnit\DbUnit\Operation\Factory;
use PHPUnit\DbUnit\TestCase;
use SlayerBirden\DataFlow\DataBagInterface;
use SlayerBirden\DataFlow\Emitter\BlackHole;
use SlayerBirden\DataFlow\Pipe\MapperCallbackInterface;
use SlayerBirden\DataFlow\PipelineBuilder;
use SlayerBirden\DataFlow\Plumber;
use SlayerBirden\DataFlow\Provider\ArrayProvider;
use SlayerBirden\DataFlow\Test\Functional\Exception\ConnectionException;
use SlayerBirden\DataFlow\Writer\Dbal\UpdateStrategy\UniqueIndexStrategy;
use SlayerBirden\DataFlow\Writer\Dbal\Write;
use SlayerBirden\DataFlow\Writer\Dbal\WriterUtility;

class DbalPipeTest extends TestCase
{
    /**
     * @var Connection
     */
    private $connection;
    private $pipeline;
    private $emitter;

    protected function setUp(): void
    {
        $params = require __DIR__ . '/config/db-config.php';
        $this->connection = DriverManager::getConnection($params);

        // create Table
        $schema = new Schema();
        $table = $schema->createTable('heroes');
        $table->addColumn('id', 'integer', [
            'autoincrement' => true,
        ]);
        $table->addColumn('name', 'string');
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
        $utility = new WriterUtility($this->connection);
        $this->pipeline = (new PipelineBuilder($this->emitter))
            ->map('name', new class implements MapperCallbackInterface
            {
                public function __invoke($value, ?DataBagInterface $dataBag = null)
                {
                    return $dataBag['firstname'] . ' ' . $dataBag['lastname'];
                }
            })
            ->addSection(new Write(
                'heroes_write',
                $this->connection,
                'heroes',
                $utility,
                new UniqueIndexStrategy('heroes', $utility),
                $this->emitter
            ))
            ->build();
    }

    protected function getTearDownOperation()
    {
        return Factory::TRUNCATE();
    }

    /**
     * Returns the test database connection.
     *
     * @return \PHPUnit\DbUnit\Database\Connection
     */
    protected function getConnection()
    {
        $con = $this->connection->getWrappedConnection();
        if ($con instanceof PDOConnection) {
            return $this->createDefaultDBConnection($con);
        }

        throw new ConnectionException('Could not get PDO object.');
    }

    /**
     * Returns the test dataset.
     *
     * @return IDataSet
     */
    protected function getDataSet()
    {
        return $this->createArrayDataSet([
            'heroes' => [
                [
                    'id' => 1,
                    'name' => 'Spiderman',
                    'code' => 'spider-man',
                ],
            ],
        ]);
    }

    public function testDbSimplePipeFlow()
    {
        $provider = new ArrayProvider('heroes', [
            [
                'code' => 'super-man',
                'firstname' => 'Clark',
                'lastname' => 'Kent',
            ],
            [
                'code' => 'spider-man',
                'firstname' => 'Peter',
                'lastname' => 'Parker',
            ],
            [
                'code' => 'clockwerk',
                'firstname' => 'Clock',
                'lastname' => 'Werk',
            ],
        ]);
        (new Plumber($provider, $this->pipeline, $this->emitter))->pour();

        $actual = $this->getConnection()->createQueryTable('heroes', 'SELECT * FROM `heroes`');
        $expected = $this->createArrayDataSet([
            'heroes' => [
                [
                    'id' => 1,
                    'name' => 'Peter Parker',
                    'code' => 'spider-man',
                ],
                [
                    'id' => 2,
                    'name' => 'Clark Kent',
                    'code' => 'super-man',
                ],
                [
                    'id' => 3,
                    'name' => 'Clock Werk',
                    'code' => 'clockwerk',
                ],
            ]
        ]);

        $this->assertTablesEqual($expected->getTable('heroes'), $actual);
    }
}
