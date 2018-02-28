# DataFlow
[![Build Status](https://travis-ci.org/SlayerBirden/dataflow.svg?branch=master)](https://travis-ci.org/SlayerBirden/dataflow)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/SlayerBirden/dataflow/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/SlayerBirden/dataflow/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/SlayerBirden/dataflow/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/SlayerBirden/dataflow/?branch=master)

Build a pipeline to flow your data through!

## About

This is a low level lib that helps you with building a data migration process.
It requires a little bootstrapping to float, but it can be easily done if you need just a minimal process
 or just looking to test things out.

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
We need a little bootstrapping to get DBAL connection and empty emitter.

```php
use Doctrine\DBAL\DriverManager;
use SlayerBirden\DataFlow\Emitter\BlackHole;
use SlayerBirden\DataFlow\PipelineBuilder;

# bootstrap
$connection = DriverManager::getConnection([
    'url' => 'mysql://test-user:testpwd@localhost:4486/foo?charset=UTF8',
]);
$emitter = new BlackHole();

# pipeline
$pipeline = (new PipelineBuilder($emitter))
    ->dbalWrite('users', $connection)
    ->getPipeline();
```
4. Now initiate the Plumber and pour.
```php
use SlayerBirden\DataFlow\Plumber;
use SlayerBirden\DataFlow\Provider\Csv;
...
(new Plumber(new Csv('users_file', __DIR__ . '/users.csv'), $pipeline, $emitter))->pour();
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
```
7. Full file can be found under `examples/example1-import-cli/import.php`.
8. Logged results:
![results](examples/example1-import-cli/2-cli-runs.png)
