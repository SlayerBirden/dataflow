<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Writer\Dbal\UpdateStrategy;

use SlayerBirden\DataFlow\Exception\DomainExceptionInterface;

class InvalidConfigException extends \InvalidArgumentException implements DomainExceptionInterface
{
}
