<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Test\Functional\Exception;

use PHPUnit\DbUnit\Exception;

class ConnectionException extends \InvalidArgumentException implements Exception
{
}
