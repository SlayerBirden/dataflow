<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Pipe;

use SlayerBirden\DataFlow\DataBagInterface;
use SlayerBirden\DataFlow\IdentificationTrait;
use SlayerBirden\DataFlow\PipeInterface;

class Swap implements PipeInterface
{
    use IdentificationTrait;
    /**
     * @var string
     */
    private $id;
    /**
     * @var string
     */
    private $first;
    /**
     * @var string
     */
    private $second;

    public function __construct(string $id, string $first, string $second)
    {
        $this->id = $id;
        $this->first = $first;
        $this->second = $second;
    }

    public function pass(DataBagInterface $dataBag): DataBagInterface
    {
        $first = $dataBag[$this->first] ?? null;
        $second = $dataBag[$this->second] ?? null;
        $dataBag[$this->first] = $second;
        $dataBag[$this->second] = $first;

        return $dataBag;
    }
}
