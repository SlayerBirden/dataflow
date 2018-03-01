<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow;

use SlayerBirden\DataFlow\Pipe\Copy;
use SlayerBirden\DataFlow\Pipe\Delete;
use SlayerBirden\DataFlow\Pipe\Filter;
use SlayerBirden\DataFlow\Pipe\FilterCallbackInterface;
use SlayerBirden\DataFlow\Pipe\Map;
use SlayerBirden\DataFlow\Pipe\MapperCallbackInterface;
use SlayerBirden\DataFlow\Pipe\Swap;

class PipelineBuilder implements PipelineBuilderInterface
{
    /**
     * @var PipeLineInterface
     */
    private $pipeline;

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
     * @param PipeInterface $handler
     * @param int $priority
     * @return PipelineBuilder
     */
    public function addSection(PipeInterface $handler, int $priority = 0): PipelineBuilder
    {
        $this->pipeline->insert($handler, $priority);
        $this->pipesCount++;
        return $this;
    }

    public function map(string $field, MapperCallbackInterface $callback, ?string $id = null): PipelineBuilder
    {
        if (!$id) {
            $id = 'mapper' . $this->pipesCount++ . '-' . $field;
        }
        return $this->addSection(new Map($id, $field, $callback));
    }

    public function filter(FilterCallbackInterface $callback, ?string $id = null): PipelineBuilder
    {
        if (!$id) {
            $id = 'filter' . $this->pipesCount++ . '-' . get_class($callback);
        }
        return $this->addSection(new Filter($id, $callback));
    }

    public function delete(array $names, ?string $id = null): PipelineBuilder
    {
        if (!$id) {
            $id = 'delete' . $this->pipesCount++ . '-' . json_encode($names);
        }
        return $this->addSection(new Delete($id, ...$names));
    }

    public function swap(string $first, string $second, ?string $id = null): PipelineBuilder
    {
        if (!$id) {
            $id = 'swap' . $this->pipesCount++ . '-' . $first . '-' . $second;
        }
        return $this->addSection(new Swap($id, $first, $second));
    }

    public function cp(string $from, string $to, ?string $id = null): PipelineBuilder
    {
        if (!$id) {
            $id = 'copy' . $this->pipesCount++ . '-' . $from . '-' . $to;
        }
        return $this->addSection(new Copy($id, $from, $to));
    }

    public function build(): PipeLineInterface
    {
        return $this->pipeline;
    }
}
