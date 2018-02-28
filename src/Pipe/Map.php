<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Pipe;

use SlayerBirden\DataFlow\DataBagInterface;
use SlayerBirden\DataFlow\PipeInterface;
use SlayerBirden\DataFlow\IdentificationTrait;

class Map implements PipeInterface
{
    use IdentificationTrait;
    /**
     * @var string
     */
    private $field;
    /**
     * @var MapperCallbackInterface
     */
    private $callback;
    /**
     * @var string
     */
    private $identifier;

    public function __construct(string $identifier, string $field, MapperCallbackInterface $callback)
    {
        $this->identifier = $identifier;
        $this->field = $field;
        $this->callback = $callback;
    }

    /**
     * Mapping handler.
     * Proceeds to transform an entry to a new value using callback function.
     *
     * {@inheritdoc}
     */
    public function pass(DataBagInterface $dataBag): DataBagInterface
    {
        $dataBag[$this->field] = ($this->callback)($dataBag[$this->field] ?? null, $dataBag);

        return $dataBag;
    }
}
