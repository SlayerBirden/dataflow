# DataFlow
[![Build Status](https://travis-ci.org/SlayerBirden/dataflow.svg?branch=master)](https://travis-ci.org/SlayerBirden/dataflow)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/SlayerBirden/dataflow/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/SlayerBirden/dataflow/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/SlayerBirden/dataflow/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/SlayerBirden/dataflow/?branch=master)

Build a pipeline to pour your data through!

## About

This is a low level lib that helps you with building a data migration process.
It requires a little bootstrapping to float, but it can be easily done if you just need a minimal process
 or looking to test things out.

The main idea is that you're building a pipeline where each section can mutate the data that's flowing through.
It allows process to be extremely flexible, since each new step can potentially get (**completely**) transformed data.
It also can be very dangerous, since mistake can be costly, hence we crafted awesome error handling and reporting mechanisms.

## Examples

### Build a simple cli import script.

You have a csv file you need to import into database? Let's do that!

1. For the sake of example we'll be importing users into table "users" with columns:

   | id (int) | name (string) | email (string, unique) | age (int) |
   | ---- | ---- | ---- | ---- |
2. Our csv file looks like this

    | first | last | email | age |
    | ---- | ---- | ---- | ---- |
    | Arthur| Dayne | morningsword@sunspear.com | 23 |
    | Gerold | Hightower | whitebull@kingsguard.net | 43 |
    | Eddard | Stark | ned@winterfell.net | 36 |
    | Jaime | Lannister | kingslayer@kingsguard.net | 34 |
    | Aegon I |Targaryen | theconqueror@westeros.com | 64 |
3. Let's use PipeLine builder to set up some known steps.
We need a little bootstrapping to get DBAL writer and empty emitter.

```php
use Doctrine\DBAL\DriverManager;
use SlayerBirden\DataFlow\Emitter\BlackHole;
use SlayerBirden\DataFlow\PipelineBuilder;
use SlayerBirden\DataFlow\Writer\Dbal\UpdateStrategy\UniqueIndexStrategy;
use SlayerBirden\DataFlow\Writer\Dbal\Write;
use SlayerBirden\DataFlow\Writer\Dbal\WriterUtility;

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
$emitter = new BlackHole();

# pipeline
$pipeline = (new PipelineBuilder($emitter))
    ->addSection($dbWrite)
    ->build();
```
4. Now initiate the Plumber and pour.
```php
use SlayerBirden\DataFlow\Plumber;
use SlayerBirden\DataFlow\Provider\Csv;
...
$file = new \SplFileObject(__DIR__ . '/users.csv');
$file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::READ_AHEAD | \SplFileObject::SKIP_EMPTY);
(new Plumber(new Csv('users_file', $file), $pipeline, $emitter))->pour();
```
5. We want some reporting to know what's going on. Let's implement basic stdOut emitter.
```php
$emitter = new class implements \SlayerBirden\DataFlow\EmitterInterface
{
    public function emit(string $event, ...$args): void
    {
        echo $event, ' ==> ', implode(', ', $args), PHP_EOL;
    }
};
```
6. There are something else we need to do: concat firstName and lastName and assign to "name"
```php
use SlayerBirden\DataFlow\DataBagInterface;

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
```
7. Full file can be found under `examples/example1-import-cli/import.php`.
8. Logged results:
![results](examples/example1-import-cli/2-cli-runs.png)

## Goals
* **Easy to maintain**. Strong reporting shows what exactly went wrong.
* **Flexible**. Extremely flexible workflow allows to work with different import/export/migration scenarios.
    * one file -> multiple tables
    * multiple files -> one table
    * supports dynamic DB connection
    * supports different source types and destination types
* **Fast**. Low level operations produce maximum flow speed.
* **Stable**. Strong test coverage guarantees bug-free groundwork.

## Influences
* [Ddeboer Data Import library](https://github.com/ddeboer/data-import)
