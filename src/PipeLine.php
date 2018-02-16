<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow;

class PipeLine implements PipeLineInterface, \Countable
{
    private $queue = [];
    private $pointer = 0;
    private $queueOrder = PHP_INT_MAX;

    public function current(): HandlerInterface
    {
        return $this->queue[$this->pointer]['item'];
    }

    public function insert(HandlerInterface $handler, int $priority = 0): void
    {
        $this->queue[] = [
            'item' => $handler,
            'p' => $priority,
            '_p' => $this->queueOrder--,
        ];
        usort($this->queue, function ($itemA, $itemB) {
            if ($itemA['p'] == $itemB['p']) {
                return ($itemA['_p'] > $itemB['_p']) ? -1 : 1;
            }

            return ($itemA['p'] > $itemB['p']) ? -1 : 1;
        });
    }

    public function next(): void
    {
        $this->pointer++;
    }

    public function valid(): bool
    {
        return isset($this->queue[$this->pointer]);
    }

    public function count(): int
    {
        return count($this->queue);
    }

    public function rewind(): void
    {
        $this->pointer = 0;
    }
}
