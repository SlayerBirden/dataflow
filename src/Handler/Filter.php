<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Handler;

use SlayerBirden\DataFlow\DataBagInterface;
use SlayerBirden\DataFlow\Exception\FlowTerminationException;
use SlayerBirden\DataFlow\HandlerInterface;

class Filter implements HandlerInterface
{
    /**
     * @var FilterCallbackInterface
     */
    private $callback;
    /**
     * @var string
     */
    private $identifier;

    public function __construct(string $identifier, FilterCallbackInterface $callback)
    {
        $this->identifier = $identifier;
        $this->callback = $callback;
    }

    /**
     * Filtering handler.
     * Terminate data flow based on a callback applied to DataBag.
     *
     * {@inheritdoc}
     */
    public function handle(DataBagInterface $dataBag): DataBagInterface
    {
        if (!($this->callback)($dataBag)) {
            throw new FlowTerminationException(
                sprintf(
                    'Flow was terminated by Filter (%s) for data %s.',
                    $this->getIdentifier(),
                    json_encode($dataBag)
                )
            );
        }

        return $dataBag;
    }

    /**
     * @inheritdoc
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}
