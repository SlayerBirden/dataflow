<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow;

/**
 * This plumber is laze. It delegates pouring to others using emitter;
 * Can be used for concurrency.
 */
class LazyPlumber
{
    /**
     * @var ProviderInterface
     */
    private $provider;
    /**
     * @var PipeLineInterface
     */
    private $pipeLine;
    /**
     * @var EmitterInterface
     */
    private $emitter;

    public function __construct(
        ProviderInterface $provider,
        PipeLineInterface $pipeLine,
        EmitterInterface $emitter
    ) {
        $this->provider = $provider;
        $this->pipeLine = $pipeLine;
        $this->emitter = $emitter;
    }

    public function pour(): void
    {
        $provider = $this->provider->getCask();
        foreach ($provider as $dataBag) {
            $this->emitter->emit('pour', $dataBag, $this->pipeLine);
        }
        $this->emitter->emit('empty_cask');
    }
}
