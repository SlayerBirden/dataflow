<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Writer;

use SlayerBirden\DataFlow\DataBagInterface;
use SlayerBirden\DataFlow\IdentificationTrait;
use SlayerBirden\DataFlow\PipeInterface;
use SlayerBirden\DataFlow\Writer\Exception\WriteErrorException;

class Csv implements PipeInterface
{
    use IdentificationTrait;
    /**
     * @var string
     */
    private $id;
    /**
     * @var \SplFileObject
     */
    private $file;
    /**
     * @var array
     */
    private $header;

    public function __construct(string $id, \SplFileObject $file, array $header)
    {
        $this->id = $id;
        $this->file = $file;
        $this->header = $header;
    }

    public function pass(DataBagInterface $dataBag): DataBagInterface
    {
        $values = array_values(array_intersect_key($dataBag->toArray(), array_flip($this->header)));
        $return = $this->file->fputcsv($values);

        if ($return === false || $return === 0) {
            throw new WriteErrorException(
                sprintf(
                    'Failed to write into file %s, data: %s.',
                    $this->file->getFilename(),
                    json_encode($values)
                )
            );
        }

        return $dataBag;
    }
}
