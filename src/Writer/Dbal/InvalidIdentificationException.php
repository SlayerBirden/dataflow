<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Writer\Dbal;

use SlayerBirden\DataFlow\Exception\DomainExceptionInterface;

class InvalidIdentificationException extends \LengthException implements DomainExceptionInterface
{
}
