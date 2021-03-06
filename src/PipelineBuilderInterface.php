<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow;

interface PipelineBuilderInterface
{
    public function addSection(PipeInterface $handler, int $priority = 0);

    public function build(): PipeLineInterface;
}
