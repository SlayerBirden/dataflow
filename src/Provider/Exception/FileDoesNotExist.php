<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Provider\Exception;

use SlayerBirden\DataFlow\Exception\DomainExceptionInterface;

class FileDoesNotExist extends \InvalidArgumentException implements DomainExceptionInterface
{
}
