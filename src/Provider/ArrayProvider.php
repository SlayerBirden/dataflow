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
    private $pointer = 0;
    /**
     * @var string
     */
    private $identifier;

    public function __construct(string $id, array $data)
    {
        $this->data = $data;
        $this->identifier = $id;
    }

    /**
     * @inheritdoc
     */
    public function provide(): DataBagInterface
    {
        if (isset($this->data[$this->pointer])) {
            return new SimpleBag($this->data[$this->pointer++]);
        }
        throw new EmptyException(sprintf('Provider %s is empty.', $this->getIdentifier()));
    }
}
