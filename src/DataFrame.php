<?php namespace Archon;

use Iterator;
use ArrayAccess;
use Closure;
use InvalidArgumentException;

use Assert\Assertion;

class DataFrame implements ArrayAccess, Iterator
{

    private $data = [];
    private $pointer = 0;

    private function __construct(array $data)
    {
        $this->data = $data;
    }

    public static function fromArray(array $data)
    {
        return new DataFrame($data);
    }

    public function toArray()
    {
        return $this->data;
    }

    public function offsetGet($key)
    {
        $col = array_column($this->data, $key);
        foreach($col as $i => $row) $col[$i] = [$key => $row];
        return new DataFrame($columns=[$key], $data=$col);
    }

    public function offsetSet($key, $value)
    {
        $this->columns[] = $key;
        foreach($this as $row) $row[$key] = $value;
    }

    public function current()
    {
        return $this->data[$this->key()];
    }

    public function next()
    {
        $this->pointer++;
    }

    public function key()
    {
        return $this->pointer;
    }

    public function valid()
    {
        return isset($this->data[$this->key()]);
    }

    public function rewind()
    {
        $this->pointer = 0;
    }

    public function offsetExists($offset)
    {
        foreach($this as $row) {
            if (!array_key_exists($offset, $row)) {
                return FALSE;
            }
        }

        return TRUE;
    }

    public function offsetUnset($offset)
    {
        if (!isset($this[$offset])) {
            throw new \RuntimeException("Key {$offset} not found in DataFrame.");
        }

        foreach($this as $i => $row) {
            unset($this->data[$i][$offset]);
        }

        if(($key = array_search($offset, $this->columns)) !== FALSE) {
            unset($this->columns[$key]);
        }
    }
}