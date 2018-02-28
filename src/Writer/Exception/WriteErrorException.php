<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Writer\Exception;

use SlayerBirden\DataFlow\Exception\DomainExceptionInterface;

class WriteErrorException extends \RuntimeException implements DomainExceptionInterface
{
}
