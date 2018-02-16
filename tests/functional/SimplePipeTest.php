<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Test\Functional;

use PHPUnit\Framework\TestCase;
use SlayerBirden\DataFlow\DataBagInterface;
use SlayerBirden\DataFlow\Exception\FlowTerminationException;
use SlayerBirden\DataFlow\Handler\FilterCallbackInterface;
use SlayerBirden\DataFlow\Handler\MapperCallbackInterface;
use SlayerBirden\DataFlow\HandlerInterface;
use SlayerBirden\DataFlow\PipelineBuilder;
use SlayerBirden\DataFlow\Provider\ArrayProvider;
use SlayerBirden\DataFlow\Provider\EmptyException;

class SimplePipeTest extends TestCase
{
    /**
     * @var HandlerInterface[]
     */
    private $pipeline;
    private $storage = [];

    protected function setUp()
    {
        $this->pipeline = (new PipelineBuilder())
            ->filter(new class implements FilterCallbackInterface
            {
                public function __invoke(DataBagInterface $dataBag): bool
                {
                    return stripos($dataBag['firstname'], 'cl') !== false;
                }
            })
            ->map('name', new class implements MapperCallbackInterface
            {
                public function __invoke($value, ?DataBagInterface $dataBag)
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

        try {
            while ($dataBag = $provider->provide()) {
                // pour
                try {
                    foreach ($this->pipeline as $handler) {
                        $handler->handle($dataBag);
                    }
                } catch (FlowTerminationException $exception) {
                }
            }
        } catch (EmptyException $exception) {
        }

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
