<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Writer\Dbal\UpdateStrategy;

use SlayerBirden\DataFlow\DataBagInterface;
use SlayerBirden\DataFlow\Writer\Dbal\UpdateStrategyInterface;

class ConfigStrategy implements UpdateStrategyInterface
{
    /**
     * @var array
     */
    private $fields;

    public function __construct(array $fields)
    {
        //todo make more real life config object
        $this->fields = $fields;
    }

    /**
     * Use predefined config.
     *
     * @throws InvalidConfigException
     * {@inheritdoc}
     */
    public function getRecordIdentifier(DataBagInterface $dataBag): array
    {
        $ids = [];
        foreach ($this->fields as $field) {
            if (!isset($dataBag[$field])) {
                throw new InvalidConfigException(
                    sprintf('Identifier field %s is missing in the bag: %s.', $field, json_encode($dataBag))
                );
            }
            $ids[$field] = $dataBag[$field];
        }

        return $ids;
    }
}
