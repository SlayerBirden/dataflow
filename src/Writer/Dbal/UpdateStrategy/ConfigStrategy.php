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
        foreach ($this->fields as $key => $value) {
            if ($value === null) {
                if (!isset($dataBag[$key])) {
                    throw new InvalidConfigException(
                        sprintf('Identifier field %s is missing in the bag: %s.', $key, json_encode($dataBag))
                    );
                }
                $ids[$key] = $dataBag[$key];
            } else {
                $ids[$key] = $value;
            }
        }

        return $ids;
    }
}
