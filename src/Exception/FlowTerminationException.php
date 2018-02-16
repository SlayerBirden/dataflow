<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Exception;

class FlowTerminationException extends \OutOfBoundsException implements DomainExceptionInterface
{
}
