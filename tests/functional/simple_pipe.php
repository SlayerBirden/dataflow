<?php
declare(strict_types=1);

use SlayerBirden\DataFlow\DataBagInterface;
use SlayerBirden\DataFlow\Exception\FlowTerminationException;
use SlayerBirden\DataFlow\Handler\FilterCallbackInterface;
use SlayerBirden\DataFlow\Handler\MapperCallbackInterface;
use SlayerBirden\DataFlow\PipelineBuilder;
use SlayerBirden\DataFlow\Provider\EmptyException;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$storage = [];

$pipeline = (new PipelineBuilder())
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
    ->arrayWrite($storage, null)
    ->getPipeline();

$provider = new \SlayerBirden\DataFlow\Provider\ArrayProvider('heroes', [
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

//todo
try {
    while ($dataBag = $provider->provide()) {
        // pour
        try {
            foreach ($pipeline as $handler) {
                $handler->handle($dataBag);
            }
        } catch (FlowTerminationException $exception) {
        }
    }
} catch (EmptyException $exception) {
}

var_dump($storage);
