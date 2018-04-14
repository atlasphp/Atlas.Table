<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
declare(strict_types=1);

namespace Atlas\Table;

class Exception extends \Exception
{
    public static function propertyDoesNotExist(string $class, string $property) : Exception
    {
        return new Exception("{$class}::\${$property} does not exist.");
    }

    public static function tableNotFound(string $class) : Exception
    {
        return new Exception("{$class} not found in table locator.");
    }

    public static function invalidType(string $expect, $actual) : Exception
    {
        if (is_object($actual)) {
            $actual = get_class($actual);
        } else {
            $actual = gettype($actual);
        }

        return new Exception("Expected type $expect; got $actual instead.");
    }

    public static function unexpectedRowCountAffected($count)
    {
        return new Exception("Expected 1 row affected, actual {$count}.");
    }

    public static function immutableOnceDeleted(string $class, string $property)
    {
        return new Exception("{$class}::\${$property} is immutable after Row is deleted.");
    }

    public static function primaryValueNotScalar(string $col, $val)
    {
        $message = "Expected scalar value for primary key '{$col}', "
            . "got " . gettype($val) . " instead.";
        return new Exception($message);
    }

    public static function primaryValueMissing(string $col)
    {
        $message = "Expected scalar value for primary key '$col', "
            . "value is missing instead.";
        return new Exception($message);
    }

    public static function tableAlreadySet() : Exception
    {
        return new Exception("Table already set.");
    }

    public static function unexpectedOption($value, array $options)
    {
        $message = "Expected one of '" . implode("','", $options)
            . "'; got '{$value}' instead.";
        return new Exception($message);
    }
}
