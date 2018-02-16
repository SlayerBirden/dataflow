<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Writer;

use SlayerBirden\DataFlow\DataBagInterface;

interface WriteCallbackInterface
{
    /**
     * Write callback.
     * Can be used to track if row was inserted or updated.
     * $processed === 1 => inserted
     * $processed === 2 => updated
     * $processed === 0 => skipped
     * If $id is provided it cab be set into $dataBag to make "connection" possible down the pipeline.
     *
     * @param int $processed
     * @param DataBagInterface $dataBag
     * @param string|null $id
     * @return void
     */
    public function __invoke(int $processed, DataBagInterface $dataBag, ?string $id);
}
