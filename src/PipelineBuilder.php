<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Index;
use SlayerBirden\DataFlow\Handler\Filter;
use SlayerBirden\DataFlow\Handler\FilterCallbackInterface;
use SlayerBirden\DataFlow\Handler\Mapper;
use SlayerBirden\DataFlow\Handler\MapperCallbackInterface;
use SlayerBirden\DataFlow\Writer\ArrayWrite;
use SlayerBirden\DataFlow\Writer\Dbal\AutoIncrementCallbackInterface;
use SlayerBirden\DataFlow\Writer\Dbal\Write;
use SlayerBirden\DataFlow\Writer\Dbal\WriterUtilityInterface;
use SlayerBirden\DataFlow\Writer\Dbal\UpdateStrategy\UniqueIndexStrategy;

class PipelineBuilder implements PipelineBuilderInterface
{
    /**
     * @var PipeLineInterface
     */
    private $pipeline;
    /**
     * @var WriterUtilityInterface
     */
    private static $utility;

    private $pipesCount = 0;
    /**
     * @var EmitterInterface
     */
    private $emitter;

    public function __construct(EmitterInterface $emitter)
    {
        $this->pipeline = new PipeLine();
        $this->emitter = $emitter;
    }

    /**
     * Add arbitrary (not pre-defined) section to pipeline.
     *
     * @param HandlerInterface $handler
     * @param int $priority
     * @return PipelineBuilder
     */
    public function addSection(HandlerInterface $handler, int $priority = 0): PipelineBuilder
    {
        $this->pipeline->insert($handler, $priority);
        return $this;
    }

    public function map(string $field, MapperCallbackInterface $callback, ?string $id = null): PipelineBuilder
    {
        if (!$id) {
            $id = 'mapper' . $this->pipesCount++ . '-' . $field;
        }
        return $this->addSection(new Mapper($id, $field, $callback));
    }

    public function filter(FilterCallbackInterface $callback, ?string $id = null): PipelineBuilder
    {
        if (!$id) {
            $id = 'filter' . $this->pipesCount++ . '-' . md5(get_class($callback));
        }
        return $this->addSection(new Filter($id, $callback));
    }

    public function dbalWrite(
        string $table,
        Connection $connection,
        ?AutoIncrementCallbackInterface $callback = null,
        ?string $id = null
    ): PipelineBuilder {
        if (!$id) {
            $id = 'dbal-write' . $this->pipesCount++ . '-' . $table;
        }
        return $this->addSection(new Write(
            $id,
            $connection,
            $table,
            $this->getDbalUtility($connection),
            (new UniqueIndexStrategy($table, $this->getDbalUtility($connection))),
            $this->emitter,
            $callback
        ));
    }

    public function arrayWrite(
        array &$storage,
        ?string $id = null
    ): PipelineBuilder {
        if (!$id) {
            $id = 'array-write' . $this->pipesCount++;
        }
        return $this->addSection(new ArrayWrite($id, $storage));
    }

    public function getPipeline(): PipeLineInterface
    {
        return $this->pipeline;
    }

    public static function getDbalUtility(Connection $connection): WriterUtilityInterface
    {
        if (self::$utility === null) {
            self::$utility = new class($connection) implements WriterUtilityInterface
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
            };
        }
        return self::$utility;
    }
}
