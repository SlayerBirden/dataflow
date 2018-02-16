<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Handler;

use SlayerBirden\DataFlow\DataBagInterface;
use SlayerBirden\DataFlow\HandlerInterface;

class Mapper implements HandlerInterface
{
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
    public function handle(DataBagInterface $dataBag): DataBagInterface
    {
        $dataBag[$this->field] = ($this->callback)($dataBag[$this->field] ?? null, $dataBag);

        return $dataBag;
    }

    /**
     * @inheritdoc
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}
