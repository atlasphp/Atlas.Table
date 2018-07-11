<?php
require dirname(__DIR__) . '/vendor/autoload.php';

use Atlas\Table\TableLocator;
use Atlas\Testing\DataSourceFixture;
use Atlas\Testing\DataSource\Employee\EmployeeTable;
use Atlas\Testing\DataSource\Employee\EmployeeRow;

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
$employeeTable = $tableLocator->get(EmployeeTable::CLASS);

bench('new EmployeeRow()', function () {
    new EmployeeRow();
});

bench('EmployeeTable::newRow()', function () use ($employeeTable) {
    $employeeTable->newRow();
});
