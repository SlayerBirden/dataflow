<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Pipe;

use SlayerBirden\DataFlow\DataBagInterface;
use SlayerBirden\DataFlow\IdentificationTrait;
use SlayerBirden\DataFlow\PipeInterface;

class Copy implements PipeInterface
{
    use IdentificationTrait;
    /**
     * @var string
     */
    private $id;
    /**
     * @var string
     */
    private $from;
    /**
     * @var string
     */
    private $to;

    public function __construct(string $id, string $from, string $to)
    {
        $this->id = $id;
        $this->from = $from;
        $this->to = $to;
    }

    public function pass(DataBagInterface $dataBag): DataBagInterface
    {
        $from = $dataBag[$this->from] ?? null;
        $dataBag[$this->to] = $from;

        return $dataBag;
    }
}
