<?php

require_once __DIR__ . '/vendor/autoload.php';

$container = (new \DI\ContainerBuilder())
    ->addDefinitions(__DIR__ . '/definitions.php')
    ->build();

if ($argc != 3) {
    echo 'Missing Arguments: ./faker.php [logCount] [entryCount]' . PHP_EOL;
    exit(1);
}

if (!is_numeric($argv[1]) || !is_numeric($argv[2])) {
    echo 'Invalid Argument types, both arguments must be a numeric value.' . PHP_EOL;
    exit(1);
}

$logCount = (int)$argv[1];
$entryCount = (int)$argv[2];

$faker = Faker\Factory::create();

/** @var \App\Database\Repositories\LogRepository $logRepository */
/** @var \App\Database\Repositories\LogEntryRepository $entryRepository */
$logRepository = $container->get(\App\Database\Repositories\LogRepository::class);
$entryRepository = $container->get(\App\Database\Repositories\LogEntryRepository::class);

for ($i = 0; $i < $logCount; $i++) {
    $log = new \App\Database\Entities\Log();
    do {
        $name = $faker->text(20);
    } while(!$logRepository->isNameUnique($name));
    $log->setName($name);
    $log->setDescription($faker->text(500));
    $logRepository->save($log);
    echo 'Saved Log: ' . $log->getId() . PHP_EOL;
    for ($j = 0; $j < $entryCount; $j++) {
        $entry = new \App\Database\Entities\LogEntry();
        $entry->setTech($faker->email);
        $entry->setDevice($faker->text(30));
        $entry->setInitiatedBy($faker->text(30));
        $entry->setChangeDescription($faker->text(500));
        $entry->setRollbackDescription($faker->text(500));
        $entry->setLog($log);
        $entry->setCreatedAt(DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-10 years', 'now', 'UTC')));
        $entryRepository->save($entry);
        echo 'Saved Entry: ' . $entry->getId() . PHP_EOL;
    }
}