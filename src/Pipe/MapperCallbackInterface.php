<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Pipe;

use SlayerBirden\DataFlow\DataBagInterface;

interface MapperCallbackInterface
{
    /**
     * Transform data coming in "value" and return it.
     *
     * @param mixed $value
     * @param DataBagInterface $dataBag
     * @return string
     */
    public function __invoke($value, DataBagInterface $dataBag);
}
