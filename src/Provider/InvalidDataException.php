<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Provider;

use SlayerBirden\DataFlow\Exception\DomainExceptionInterface;

class InvalidDataException extends \InvalidArgumentException implements DomainExceptionInterface
{
}
