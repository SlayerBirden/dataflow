<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Writer\Dbal;

use SlayerBirden\DataFlow\DataBagInterface;

interface AutoIncrementCallbackInterface
{
    /**
     * Use Auto-Increment field for connection
     *
     * @param int $id AutoIncrement value
     * @param DataBagInterface $dataBag
     * @return void
     */
    public function __invoke(int $id, DataBagInterface $dataBag);
}
