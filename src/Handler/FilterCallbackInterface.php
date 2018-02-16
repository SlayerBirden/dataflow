<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Handler;

use SlayerBirden\DataFlow\DataBagInterface;
use SlayerBirden\DataFlow\Exception\FlowTerminationException;

interface FilterCallbackInterface
{
    /**
     * Return "false" if data should be filtered out.
     *
     * @param DataBagInterface $dataBag
     * @return bool
     * @throws FlowTerminationException
     */
    public function __invoke(DataBagInterface $dataBag): bool;
}
