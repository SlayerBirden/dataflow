<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow;

use SlayerBirden\DataFlow\Exception\FlowTerminationException;
use SlayerBirden\DataFlow\Provider\Exception\ProviderExceptionInterface;

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
        $provider = $this->source->getCask();
        try {
            foreach ($provider as $dataBag) {
                try {
                    $this->pipeLine->rewind();
                    while ($this->pipeLine->valid()) {
                        $handler = $this->pipeLine->current();
                        $dataBag = $handler->pass($dataBag);
                        $this->pipeLine->next();
                    }
                } catch (FlowTerminationException $exception) {
                    $this->emitter->emit('valve_closed', $exception->getIdentifier(), $dataBag);
                }
            }
        } catch (ProviderExceptionInterface $exception) {
            $this->emitter->emit('provider_error', $exception->getMessage());
        }
        $this->emitter->emit('empty_cask');
    }
}
