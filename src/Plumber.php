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
    /**
     * @var EmitterInterface
     */
    private $emitter;

    public function __construct(ProviderInterface $source, PipeLineInterface $pipeLine, EmitterInterface $emitter)
    {
        $this->source = $source;
        $this->pipeLine = $pipeLine;
        $this->emitter = $emitter;
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
                    $this->emitter->emit('valve_closed', $exception->getIdentifier(), $dataBag);
                }
            }
        } catch (EmptyException $exception) {
            $this->emitter->emit('empty_cask');
        }
    }
}
