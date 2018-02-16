<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow;

use Doctrine\DBAL\Connection;
use SlayerBirden\DataFlow\Handler\Filter;
use SlayerBirden\DataFlow\Handler\FilterCallbackInterface;
use SlayerBirden\DataFlow\Handler\Mapper;
use SlayerBirden\DataFlow\Handler\MapperCallbackInterface;
use SlayerBirden\DataFlow\Writer\ArrayWrite;
use SlayerBirden\DataFlow\Writer\Dbal\Write;
use SlayerBirden\DataFlow\Writer\Dbal\WriterUtilityInterface;
use SlayerBirden\DataFlow\Writer\Dbal\UpdateStrategy\UniqueIndexStrategy;
use SlayerBirden\DataFlow\Writer\WriteCallbackInterface;

class PipelineBuilder implements PipelineBuilderInterface
{
    /**
     * @var HandlerInterface[]
     */
    private $pipeline;

    public function addSection(HandlerInterface $handler): PipelineBuilder
    {
        // todo create pipeline object
        $this->pipeline[] = $handler;
        return $this;
    }

    public function map(string $field, MapperCallbackInterface $callback): PipelineBuilder
    {
        // todo add id
        return $this->addSection(new Mapper('', $field, $callback));
    }

    public function filter(FilterCallbackInterface $callback): PipelineBuilder
    {
        // todo add id
        return $this->addSection(new Filter('', $callback));
    }

    public function dbalWrite(
        string $table,
        Connection $connection,
        ?WriteCallbackInterface $callback = null
    ): PipelineBuilder {
        // todo add id
        return $this->addSection(new Write(
            '',
            $connection,
            $table,
            $this->getDbalUtility($connection),
            (new UniqueIndexStrategy($table, $this->getDbalUtility($connection))),
            $callback
        ));
    }

    public function arrayWrite(array &$storage, ?WriteCallbackInterface $callback = null): PipelineBuilder
    {
        return $this->addSection(new ArrayWrite('', $storage, $callback));
    }

    /**
     * @return HandlerInterface[]
     */
    public function getPipeline(): array
    {
        return $this->pipeline;
    }

    private function getDbalUtility(Connection $connection): WriterUtilityInterface
    {
        //todo
    }
}
