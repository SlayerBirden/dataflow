<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Writer;

use SlayerBirden\DataFlow\DataBagInterface;
use SlayerBirden\DataFlow\HandlerInterface;
use SlayerBirden\DataFlow\IdentificationTrait;

class ArrayWrite implements HandlerInterface
{
    use IdentificationTrait;
    /**
     * @var array
     */
    private $localStorage;
    /**
     * @var string
     */
    private $identifier;
    /**
     * @var null|WriteCallbackInterface
     */
    private $callback;

    public function __construct(string $id, array &$localStorage, ?WriteCallbackInterface $callback)
    {
        $this->identifier = $id;
        $this->localStorage = &$localStorage;
        $this->callback = $callback;
    }

    /**
     * @inheritdoc
     */
    public function handle(DataBagInterface $dataBag): DataBagInterface
    {
        $this->localStorage[] = $dataBag->toArray();

        if ($this->callback) {
            ($this->callback)(1, $dataBag);
        }

        return $dataBag;
    }
}
