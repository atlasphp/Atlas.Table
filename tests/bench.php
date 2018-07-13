<?php
$dir = __DIR__;
while ($dir !== '') {
    $dir = dirname($dir);
    $file = "{$dir}/vendor/autoload.php";
    if (file_exists($file)) {
        require $file;
        echo "Autoloader at $file" . PHP_EOL;
        break;
    }
}

if (! file_exists($file)) {
    "No autoloader found." . PHP_EOL;
    exit(1);
}

use Atlas\Table\TableLocator;
use Atlas\Testing\DataSourceFixture;
use Atlas\Testing\DataSource\Thread\ThreadTable;
use Atlas\Testing\DataSource\Thread\ThreadRow;

function bench($label, $callable)
{
    $k = 100000;
    $before = microtime(true);
    for ($i = 0; $i < $k; $i ++) {
        $callable();
    }
    $after = microtime(true);
    echo ($after - $before) . " : {$label}" . PHP_EOL;
}

$connection = (new DataSourceFixture())->exec();
$tableLocator = TableLocator::new($connection);
$threadTable = $tableLocator->get(ThreadTable::CLASS);

bench('new ThreadRow()', function () {
    new ThreadRow([
        'thread_id' => null,
        'author_id' => null,
        'subject' => null,
        'body' => null,
    ]);
});

bench('ThreadTable::newRow()', function () use ($threadTable) {
    $threadTable->newRow([
        'thread_id' => null,
        'author_id' => null,
        'subject' => null,
        'body' => null,
    ]);
});
