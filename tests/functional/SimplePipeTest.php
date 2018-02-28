<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Test\Functional;

use PHPUnit\Framework\TestCase;
use SlayerBirden\DataFlow\DataBagInterface;
use SlayerBirden\DataFlow\Emitter\BlackHole;
use SlayerBirden\DataFlow\Pipe\FilterCallbackInterface;
use SlayerBirden\DataFlow\Pipe\MapperCallbackInterface;
use SlayerBirden\DataFlow\PipelineBuilder;
use SlayerBirden\DataFlow\Plumber;
use SlayerBirden\DataFlow\Provider\ArrayProvider;

class SimplePipeTest extends TestCase
{
    private $pipeline;
    private $storage = [];
    private $emitter;

    protected function setUp()
    {
        $this->emitter = new BlackHole();
        $this->pipeline = (new PipelineBuilder($this->emitter))
            ->filter(new class implements FilterCallbackInterface
            {
                public function __invoke(DataBagInterface $dataBag): bool
                {
                    return stripos($dataBag['firstname'], 'cl') !== false;
                }
            })
            ->map('name', new class implements MapperCallbackInterface
            {
                public function __invoke($value, ?DataBagInterface $dataBag = null)
                {
                    return $dataBag['firstname'] . ' ' . $dataBag['lastname'];
                }
            })
            ->arrayWrite($this->storage, null)
            ->getPipeline();
    }

    public function testSimplePipeFlow()
    {
        $provider = new ArrayProvider('heroes', [
            [
                'id' => 1,
                'firstname' => 'Clark',
                'lastname' => 'Kent',
            ],
            [
                'id' => 2,
                'firstname' => 'Peter',
                'lastname' => 'Parker',
            ],
            [
                'id' => 3,
                'firstname' => 'Clock',
                'lastname' => 'Werk',
            ],
        ]);

        (new Plumber($provider, $this->pipeline, $this->emitter))->pour();

        $this->assertEquals([
            [
                'id' => 1,
                'firstname' => 'Clark',
                'lastname' => 'Kent',
                'name' => 'Clark Kent',
            ],
            [
                'id' => 3,
                'firstname' => 'Clock',
                'lastname' => 'Werk',
                'name' => 'Clock Werk',
            ],
        ], $this->storage);
    }
}
