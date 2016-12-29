<?php

namespace M2G\Utils;

use Iterator;
use Closure;
use ArrayAccess;

class ArrayCollection implements Iterator, ArrayAccess {
	private $position = 0;
	private $data = array();

	public function __construct($data = array()) {
		$this->data = $data;
		$this->position = 0;
	}

	public function count() {
		return count($this->data);
	}

	public function filter(Closure $callback) {
		return array_filter($this->data, $callback);
	}

	public function rewind() {
		$this->position = 0;
	}

	public function current() {
		return $this->data[$this->position];
	}

	public function key() {
		return $this->position;
	}

	public function next() {
		++$this->position;
	}

	public function valid() {
		return isset($this->data[$this->position]);
	}

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->data[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->data[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }
}
