<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Exception;

use Throwable;

class FlowTerminationException extends \OutOfBoundsException implements DomainExceptionInterface
{
    /**
     * @var string
     */
    private $identifier;

    public function __construct(
        string $message = "",
        string $identifier = '',
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->identifier = $identifier;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}
