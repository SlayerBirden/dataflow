<?php
declare(strict_types=1);

use Doctrine\DBAL\DriverManager;
use SlayerBirden\DataFlow\Pipe\MapperCallbackInterface;
use SlayerBirden\DataFlow\PipelineBuilder;
use SlayerBirden\DataFlow\Plumber;
use SlayerBirden\DataFlow\Provider\Csv;

require '../../vendor/autoload.php';

# bootstrap
$connection = DriverManager::getConnection([
    'url' => 'mysql://test-user:testpwd@localhost:4486/foo?charset=UTF8',
]);
$emitter = new class implements \SlayerBirden\DataFlow\EmitterInterface
{
    public function emit(string $event, ...$args): void
    {
        echo $event, ' ==> ', implode(', ', $args), PHP_EOL;
    }
};

# pipeline
$pipeline = (new PipelineBuilder($emitter))
    ->map('name', new class implements MapperCallbackInterface
    {
        public function __invoke($value, ?\SlayerBirden\DataFlow\DataBagInterface $dataBag = null)
        {
            return $dataBag['first'] . ' ' . $dataBag['last'];
        }
    })
    ->dbalWrite('users', $connection)
    ->getPipeline();

(new Plumber(new Csv('users_file', __DIR__ . '/users.csv'), $pipeline, $emitter))->pour();
