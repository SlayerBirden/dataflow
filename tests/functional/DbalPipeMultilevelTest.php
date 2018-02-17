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
use SlayerBirden\DataFlow\Handler\MapperCallbackInterface;
use SlayerBirden\DataFlow\PipelineBuilder;
use SlayerBirden\DataFlow\Plumber;
use SlayerBirden\DataFlow\Provider\ArrayProvider;
use SlayerBirden\DataFlow\Test\Functional\Exception\ConnectionException;
use SlayerBirden\DataFlow\Writer\Dbal\AutoIncrementCallbackInterface;

class DbalPipeMultilevelTest extends TestCase
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

        $schema = new Schema();

        // create teams Table
        $table = $schema->createTable('teams');
        $table->addColumn('id', 'integer', [
            'autoincrement' => true,
        ]);
        $table->addColumn('name', 'string');
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['name']);

        // create heroes Table
        $table = $schema->createTable('heroes');
        $table->addColumn('id', 'integer', [
            'autoincrement' => true,
        ]);
        $table->addColumn('name', 'string');
        $table->addColumn('code', 'string');
        $table->addColumn('team_id', 'integer');
        $table->addForeignKeyConstraint('teams', ['team_id'], ['id']);
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
            ->map('hero_name', new class implements MapperCallbackInterface
            {
                public function __invoke($value, ?DataBagInterface $dataBag = null)
                {
                    return $dataBag['name'];
                }
            })
            ->map('name', new class implements MapperCallbackInterface
            {
                public function __invoke($value, ?DataBagInterface $dataBag = null)
                {
                    return $dataBag['team'];
                }
            })
            ->dbalWrite('teams', $this->connection, new class implements AutoIncrementCallbackInterface
            {
                public function __invoke(int $id, DataBagInterface $dataBag)
                {
                    $dataBag['team_id'] = $id;
                }
            })
            ->map('name', new class implements MapperCallbackInterface
            {
                public function __invoke($value, ?DataBagInterface $dataBag = null)
                {
                    return $dataBag['hero_name'];
                }
            })
            ->dbalWrite('heroes', $this->connection)
            ->getPipeline();
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
            ],
        ]);
    }

    public function testDbMultiLevelPipeFlow()
    {
        $provider = new ArrayProvider('heroes', [
            [
                'name' => 'Spiderman',
                'code' => 'spider-man',
                'team' => 'Avengers',
            ],
            [
                'name' => 'Hulk',
                'code' => 'hulk',
                'team' => 'Avengers',
            ],
            [
                'name' => 'Super Man',
                'code' => 'superman',
                'team' => 'Justice League',
            ],
        ]);
        (new Plumber($provider, $this->pipeline, $this->emitter))->pour();

        $actualHeroes = $this->getConnection()->createQueryTable('heroes', 'SELECT * FROM `heroes`');
        $actualTeams = $this->getConnection()->createQueryTable('teams', 'SELECT * FROM `teams`');
        $expected = $this->createArrayDataSet([
            'heroes' => [
                [
                    'id' => 1,
                    'name' => 'Spiderman',
                    'code' => 'spider-man',
                    'team_id' => 1,
                ],
                [
                    'id' => 2,
                    'name' => 'Hulk',
                    'code' => 'hulk',
                    'team_id' => 1,
                ],
                [
                    'id' => 3,
                    'name' => 'Super Man',
                    'code' => 'superman',
                    'team_id' => 2,
                ],
            ],
            'teams' => [
                [
                    'id' => 1,
                    'name' => 'Avengers',
                ],
                [
                    'id' => 2,
                    'name' => 'Justice League',
                ],
            ],
        ]);

        $this->assertTablesEqual($expected->getTable('teams'), $actualTeams);
        $this->assertTablesEqual($expected->getTable('heroes'), $actualHeroes);
    }
}
