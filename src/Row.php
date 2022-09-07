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
use IteratorAggregate;
use JsonSerializable;
use Traversable;

abstract class Row implements IteratorAggregate, JsonSerializable
{
    public const SELECT = 'SELECT';
    public const INSERT = 'INSERT';
    public const UPDATE = 'UPDATE';
    public const DELETE = 'DELETE';

    protected const META = '\0META';

    public function __construct(array $cols = [])
    {
        $this->set($cols);

        // clever, terrible hack to disguise the meta property so it does not
        // conflict with legitimate column properties. if have a table column
        // named "\0META" you get what you deserve.
        $this->{static::META} = (object) [
            'init' => $this->getArrayCopy(),
            'lastAction' => null,
            'clean' => true,
            'delete' => false,
        ];
    }

    public function __get(string $col) : mixed
    {
        $this->assertHas($col);
        return $this->$col;
    }

    public function __set(string $col, mixed $val) : void
    {
        if ($col === static::META) {
            $this->{static::META} = $val;
            return;
        }

        if ($this->{static::META}->lastAction == self::DELETE) {
            throw new Exception\ImmutableAfterDeleted(static::CLASS, $col);
        }

        $this->assertHas($col);
        $this->$col = $val;
        $this->{static::META}->clean = false;
    }

    public function __isset(string $col) : bool
    {
        $this->assertHas($col);
        return isset($this->$col);
    }

    public function __unset(string $col) : void
    {
        if ($this->{static::META}->lastAction == self::DELETE) {
            throw new Exception\ImmutableAfterDeleted(static::CLASS, $col);
        }

        $this->assertHas($col);
        $this->$col = null;
    }

    public function getIterator() : Traversable
    {
        return new ArrayIterator($this->getArrayCopy());
    }

    public function set(array $cols) : void
    {
        foreach ($cols as $col => $val) {
            if (property_exists($this, $col)) {
                $this->$col = $val;
            }
        }
    }

    public function has(string $col) : bool
    {
        return property_exists($this, $col);
    }

    public function getArrayCopy() : array
    {
        $copy = get_object_vars($this);
        unset($copy[static::META]);
        return $copy;
    }

    public function getArrayDiff() : array
    {
        $diff = [];
        $init = $this->{static::META}->init;

        foreach ($init as $col => $old) {
            if ($this->isModified($col, $old)) {
                $diff[$col] = $this->$col;
            }
        }

        return $diff;
    }

    public function getArrayInit() : array
    {
        return $this->{static::META}->init;
    }

    public function jsonSerialize() : array
    {
        return $this->getArrayCopy();
    }

    public function setLastAction(string $lastAction) : void
    {
        $options = [
            static::SELECT,
            static::INSERT,
            static::UPDATE,
            static::DELETE
        ];

        if (! in_array($lastAction, $options)) {
            throw new Exception\UnexpectedOption($lastAction, $options);
        }

        $this->{static::META}->lastAction = $lastAction;
        $this->{static::META}->init = $this->getArrayCopy();
        $this->{static::META}->clean = true;
    }

    public function setDelete(bool $delete) : void
    {
        $this->{static::META}->delete = $delete;
    }

    public function getNextAction() : ?string
    {
        if ($this->{static::META}->lastAction === null) {
            return $this->{static::META}->delete ? null : static::INSERT;
        }

        if ($this->{static::META}->delete) {
            return static::DELETE;
        }

        if ($this->{static::META}->clean) {
            return null;
        }

        foreach ($this->{static::META}->init as $col => $old) {
            if ($this->isModified($col, $old)) {
                return static::UPDATE;
            }

            $this->{static::META}->clean = true;
        }

        return null;
    }

    public function getLastAction() : ?string
    {
        return $this->{static::META}->lastAction;
    }

    protected function assertHas(string $col) : void
    {
        if (! $this->has($col)) {
            throw new Exception\PropertyDoesNotExist(static::CLASS, $col);
        }
    }

    protected function isModified(string $col, mixed $old) : bool
    {
        $old = is_bool($old) ? (int) $old : $old;
        $new = $this->$col;
        $new = is_bool($new) ? (int) $new : $new;

        return (is_numeric($old) && is_numeric($new))
            ? $old != $new // numeric, compare loosely
            : $old !== $new; // not numeric, compare strictly
    }
}
