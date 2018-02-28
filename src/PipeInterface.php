<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow;

interface PipeInterface
{
    public function pass(DataBagInterface $dataBag): DataBagInterface;

    public function getIdentifier(): string;
}
