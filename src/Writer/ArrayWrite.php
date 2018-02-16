<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Writer;

use SlayerBirden\DataFlow\DataBagInterface;
use SlayerBirden\DataFlow\HandlerInterface;

class ArrayWrite implements HandlerInterface
{
    /**
     * @var array
     */
    private $localStorage;
    /**
     * @var string
     */
    private $id;
    /**
     * @var null|WriteCallbackInterface
     */
    private $callback;

    public function __construct(string $id, array &$localStorage, ?WriteCallbackInterface $callback)
    {
        $this->id = $id;
        $this->localStorage = &$localStorage;
        $this->callback = $callback;
    }

    /**
     * @return array
     */
    public function getLocalStorage(): array
    {
        return $this->localStorage;
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

    /**
     * @inheritdoc
     */
    public function getIdentifier(): string
    {
        return $this->id;
    }
}
