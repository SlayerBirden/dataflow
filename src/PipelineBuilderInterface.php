<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow;

interface PipelineBuilderInterface
{
    public function addSection(HandlerInterface $handler);

    public function getPipeline(): PipeLineInterface;
}
