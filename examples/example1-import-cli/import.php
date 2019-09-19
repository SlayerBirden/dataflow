<?php
declare(strict_types=1);

use Doctrine\DBAL\DriverManager;
use SlayerBirden\DataFlow\DataBagInterface;
use SlayerBirden\DataFlow\Pipe\MapperCallbackInterface;
use SlayerBirden\DataFlow\PipelineBuilder;
use SlayerBirden\DataFlow\Plumber;
use SlayerBirden\DataFlow\Provider\Csv;
use SlayerBirden\DataFlow\Writer\Dbal\UpdateStrategy\UniqueIndexStrategy;
use SlayerBirden\DataFlow\Writer\Dbal\Write;
use SlayerBirden\DataFlow\Writer\Dbal\WriterUtility;

require '../../vendor/autoload.php';

# bootstrap
$connection = DriverManager::getConnection([
    'url' => 'mysql://test-user:testpwd@localhost:4486/foo?charset=UTF8',
]);
// this is just a utility class to "cache" schema info
$utility = new WriterUtility($connection);
$dbWrite = new Write(
    'users_write', // pipe ID for reporting
    $connection, // DBAL connection
    'users', // db table name
    $utility, // utility class
    new UniqueIndexStrategy('users', $utility), // update or insert will depend on unique fields in the table
    $this->emitter
);

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
        public function __invoke($value, DataBagInterface $dataBag)
        {
            return $dataBag['first'] . ' ' . $dataBag['last'];
        }
    })
    ->addSection($dbWrite)
    ->build();

$file = new \SplFileObject(__DIR__ . '/users.csv');
$file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::READ_AHEAD | \SplFileObject::SKIP_EMPTY);

(new Plumber(new Csv('users_file', $file), $pipeline, $emitter))->pour();
