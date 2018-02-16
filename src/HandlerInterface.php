<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow;

interface HandlerInterface
{
    public function handle(DataBagInterface $dataBag): DataBagInterface;

    public function getIdentifier(): string;
}
