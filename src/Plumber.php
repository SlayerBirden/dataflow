<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow;

use SlayerBirden\DataFlow\Exception\FlowTerminationException;
use SlayerBirden\DataFlow\Provider\EmptyException;

class Plumber
{
    /**
     * @var ProviderInterface
     */
    private $source;
    /**
     * @var PipeLineInterface
     */
    private $pipeLine;

    public function __construct(ProviderInterface $source, PipeLineInterface $pipeLine)
    {
        $this->source = $source;
        $this->pipeLine = $pipeLine;
    }

    /**
     * Pour source into pipeline.
     */
    public function pour(): void
    {
        try {
            while ($dataBag = $this->source->provide()) {
                try {
                    $this->pipeLine->rewind();
                    while ($this->pipeLine->valid()) {
                        $handler = $this->pipeLine->current();
                        $handler->handle($dataBag);
                        $this->pipeLine->next();
                    }
                } catch (FlowTerminationException $exception) {
                    // todo handle
                }
            }
        } catch (EmptyException $exception) {
            // todo handle
        }
    }
}
