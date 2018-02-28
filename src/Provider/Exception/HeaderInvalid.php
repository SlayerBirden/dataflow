<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Provider\Exception;

use SlayerBirden\DataFlow\Exception\DomainExceptionInterface;

class HeaderInvalid extends \LogicException implements DomainExceptionInterface
{
}
