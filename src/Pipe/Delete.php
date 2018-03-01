<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Pipe;

use SlayerBirden\DataFlow\DataBagInterface;
use SlayerBirden\DataFlow\IdentificationTrait;
use SlayerBirden\DataFlow\PipeInterface;

class Delete implements PipeInterface
{
    use IdentificationTrait;
    /**
     * @var string
     */
    private $id;
    /**
     * @var string[]
     */
    private $fieldNames;

    public function __construct(string $id, string ...$fieldNames)
    {
        $this->id = $id;
        $this->fieldNames = $fieldNames;
    }

    /**
     * Delete data from the bag by key[s]
     *
     * @param DataBagInterface $dataBag
     * @return DataBagInterface
     */
    public function pass(DataBagInterface $dataBag): DataBagInterface
    {
        foreach ($this->fieldNames as $name) {
            if (isset($dataBag[$name])) {
                unset($dataBag[$name]);
            }
        }

        return $dataBag;
    }
}
