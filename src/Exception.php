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
    public static function propertyDoesNotExist(
        string $class,
        string $property
    ) : Exception
    {
        return new Exception("{$class}::\${$property} does not exist.");
    }

    public static function tableNotFound(string $class) : Exception
    {
        return new Exception("{$class} not found in table locator.");
    }

    public static function unexpectedRowCountAffected(int $count) : Exception
    {
        return new Exception("Expected 1 row affected, actual {$count}.");
    }

    public static function immutableOnceDeleted(
        string $class,
        string $property
    ) : Exception
    {
        $classProp = "{$class}::\${$property}";
        return new Exception("{$classProp} is immutable after Row is deleted.");
    }

    public static function primaryValueNotScalar(
        string $col,
        mixed $val
    ) : Exception
    {
        $message = "Expected scalar value for primary key '{$col}', "
            . "got " . gettype($val) . " instead.";
        return new Exception($message);
    }

    public static function primaryValueMissing(string $col) : Exception
    {
        $message = "Expected scalar value for primary key '$col', "
            . "value is missing instead.";
        return new Exception($message);
    }

    public static function primaryValueChanged(string $col) : Exception
    {
        $message = "Primary key value for '$col' changed.";
        return new Exception($message);
    }

    public static function cannotPerformWithoutPrimaryKey(
        string $operation,
        string $table
    ) : Exception
    {
        $message = "Cannot {$operation} on table '$table' without primary key.";
        return new Exception($message);
    }

    public static function unexpectedOption(
        string $option,
        array $options
    ) : Exception
    {
        $message = "Expected one of '" . implode("','", $options)
            . "'; got '{$option}' instead.";
        return new Exception($message);
    }

    public static function rowAlreadyIdentityMapped(Row $row, string $serial) : self
    {
        $class = get_class($row);
        return new Exception("{$class} with serial {$serial} already exists in IdentityMap.");
    }
}
