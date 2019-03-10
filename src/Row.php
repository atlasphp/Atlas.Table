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

use ArrayIterator;
use Atlas\Table\Exception;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

abstract class Row implements IteratorAggregate, JsonSerializable
{
    const INSERT = 'INSERT';
    const UPDATE = 'UPDATE';
    const DELETE = 'DELETE';

    const SELECTED = 'SELECTED';
    const INSERTED = 'INSERTED';
    const UPDATED = 'UPDATED';
    const DELETED = 'DELETED';

    private $action = self::INSERT;

    private $delete = false;

    private $init = [];

    private $status = '';

    private $validAction = ['', self::INSERT, self::UPDATE, self::DELETE];

    private $validStatus = ['', self::SELECTED, self::INSERTED, self::UPDATED, self::DELETED];

    protected $cols = [];

    final public function __construct(array $cols = [])
    {
        $this->init = $this->cols;
        foreach ($cols as $col => $val) {
            if (! array_key_exists($col, $this->cols)) {
                continue;
            }
            $this->assertValidValue($val);
            $this->init[$col] = $val;
            $this->cols[$col] = $val;
        }
    }

    public function __get(string $col)
    {
        $this->assertHas($col);
        return $this->cols[$col];
    }

    public function __set(string $col, $val) : void
    {
        if ($this->status == self::DELETED) {
            throw Exception::immutableOnceDeleted(static::CLASS, $col);
        }

        $this->assertHas($col);
        $this->modify($col, $val);
    }

    public function __isset(string $col) : bool
    {
        $this->assertHas($col);
        return isset($this->cols[$col]);
    }

    public function __unset(string $col) : void
    {
        if ($this->status == self::DELETED) {
            throw Exception::immutableOnceDeleted(static::CLASS, $col);
        }

        $this->assertHas($col);
        $this->modify($col, null);
    }

    public function getIterator() : Traversable
    {
        return new ArrayIterator($this->cols);
    }

    public function set(array $cols = []) : void
    {
        foreach ($cols as $col => $val) {
            if ($this->has($col)) {
                $this->modify($col, $val);
            }
        }
    }

    public function has(string $col) : bool
    {
        return array_key_exists($col, $this->cols);
    }

    public function getArrayCopy() : array
    {
        return $this->cols;
    }

    public function getArrayDiff() : array
    {
        $diff = [];
        foreach ($this->cols as $col => $val) {
            if ($this->isModified($col)) {
                $diff[$col] = $val;
            }
        }
        return $diff;
    }

    public function getArrayInit() : array
    {
        return $this->init;
    }

    public function jsonSerialize() : array
    {
        return $this->getArrayCopy();
    }

    public function init(string $status) : void
    {
        $this->setStatus($status);
        $this->init = $this->cols;
        $this->action = '';
    }

    public function setDelete(bool $delete) : void
    {
        $this->delete = $delete;
    }

    public function getAction() : string
    {
        $delete = $this->delete && $this->status !== '';
        return $delete ? self::DELETE : $this->action;
    }

    public function getStatus() : string
    {
        return $this->status;
    }

    protected function setStatus(string $status) : void
    {
        $this->assertValidOption($status, $this->validStatus);
        $this->status = $status;
    }

    protected function assertValidOption(string $option, array $options) : void
    {
        if (! in_array($option, $options)) {
            throw Exception::unexpectedOption($option, $options);
        }
    }

    protected function modify(string $col, $new) : void
    {
        $this->assertValidValue($new);
        $this->cols[$col] = $new;
        if ($this->action !== self::INSERT && $this->isModified($col)) {
            $this->action = self::UPDATE;
        }
    }

    protected function assertValidValue($value) : void
    {
        if (! is_null($value) && ! is_scalar($value)) {
            throw Exception::invalidType('scalar or null', $value);
        }
    }

    protected function assertHas(string $col) : void
    {
        if (! $this->has($col)) {
            throw Exception::propertyDoesNotExist(static::CLASS, $col);
        }
    }

    protected function isModified(string $col) : bool
    {
        $old = is_bool($this->init[$col])
            ? (int) $this->init[$col]
            : $this->init[$col];

        $new = is_bool($this->cols[$col])
            ? (int) $this->cols[$col]
            : $this->cols[$col];

        return (is_numeric($old) && is_numeric($new))
            ? $old != $new // numeric, compare loosely
            : $old !== $new; // not numeric, compare strictly
    }
}
