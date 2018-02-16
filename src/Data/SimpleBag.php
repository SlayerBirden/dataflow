<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Data;

use SlayerBirden\DataFlow\DataBagInterface;

class SimpleBag extends \ArrayObject implements DataBagInterface
{
    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return $this->getArrayCopy();
    }

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return $this->getArrayCopy();
    }
}
