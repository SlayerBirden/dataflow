<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow;

interface DataBagInterface extends \Countable, \IteratorAggregate, \ArrayAccess, \Serializable, \JsonSerializable
{
    public function toArray(): array;
}
