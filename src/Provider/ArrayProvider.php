<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Provider;

use SlayerBirden\DataFlow\Data\SimpleBag;
use SlayerBirden\DataFlow\DataBagInterface;
use SlayerBirden\DataFlow\IdentificationTrait;
use SlayerBirden\DataFlow\ProviderInterface;

class ArrayProvider implements ProviderInterface
{
    use IdentificationTrait;

    private $data = [];
    /**
     * @var string
     */
    private $identifier;

    public function __construct(string $id, array $data)
    {
        $this->validate($data);
        $this->data = $data;
        $this->identifier = $id;
    }

    /**
     * @param array $data
     * @throws InvalidDataException
     */
    private function validate(array $data)
    {
        foreach ($data as $key => $row) {
            if (!is_array($row)) {
                throw new InvalidDataException(sprintf('Row #(%s) is not an array.', $key));
            }
            $localKeys = array_keys($row);
            sort($localKeys);
            if (isset($keys)) {
                if ($keys !== $localKeys) {
                    throw new InvalidDataException(
                        sprintf('Row #(%s) has different structure than the 1st element.', $key)
                    );
                }
            }
            $keys = $localKeys;
        }
    }

    /**
     * @inheritdoc
     */
    public function getCask(): \Generator
    {
        foreach ($this->data as $row) {
            yield new SimpleBag($row);
        }
    }

    /**
     * @inheritdoc
     */
    public function getEstimatedSize(): int
    {
        return count($this->data);
    }
}
