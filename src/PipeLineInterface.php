<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow;

interface PipeLineInterface
{
    public function current(): PipeInterface;

    public function insert(PipeInterface $handler, int $priority = 0): void;

    public function next(): void;

    public function valid(): bool;

    public function count(): int;

    public function rewind(): void;
}
