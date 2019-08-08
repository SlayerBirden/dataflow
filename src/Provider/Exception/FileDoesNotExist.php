<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Provider\Exception;

class FileDoesNotExist extends \InvalidArgumentException implements ProviderExceptionInterface
{
}
