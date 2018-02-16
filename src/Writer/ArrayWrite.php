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

    public function __construct(string $id, array &$localStorage)
    {
        $this->identifier = $id;
        $this->localStorage = &$localStorage;
    }

    /**
     * @inheritdoc
     */
    public function handle(DataBagInterface $dataBag): DataBagInterface
    {
        $this->localStorage[] = $dataBag->toArray();

        return $dataBag;
    }
}
