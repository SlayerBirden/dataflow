<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Provider;

use SlayerBirden\DataFlow\Exception\DomainExceptionInterface;

class EmptyException extends \UnderflowException implements DomainExceptionInterface
{
}
