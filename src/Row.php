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

use Atlas\Table\Exception;
use JsonSerializable;

abstract class Row implements JsonSerializable
{
    private $init = [];

    protected $cols = [];

    final public function __construct(array $cols = [])
    {
        $this->init();
        foreach ($cols as $col => $val) {
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
        $this->assertHas($col);
        $this->modify($col, null);
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

    public function jsonSerialize() : array
    {
        return $this->getArrayCopy();
    }

    public function init() : void
    {
        $this->init = $this->cols;
    }

    protected function modify(string $col, $new) : void
    {
        $this->assertValid($new);
        $this->cols[$col] = $new;
    }

    protected function assertValid($value) : void
    {
        if (! is_null($value) && ! is_scalar($value)) {
            throw Exception::invalidType('scalar or null', $value);
        }
    }

    protected function assertHas($col) : void
    {
        if (! $this->has($col)) {
            throw Exception::propertyDoesNotExist($this, $col);
        }
    }

    protected function isModified($col) : bool
    {
        $old = $this->init[$col];
        $new = $this->cols[$col];

        return (is_numeric($old) && is_numeric($new))
            ? $old != $new // numeric, compare loosely
            : $old !== $new; // not numeric, compare strictly
    }
}
