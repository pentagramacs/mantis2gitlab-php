<?php

namespace M2G\Mantis;

use stdClass;
use M2G\Mantis\Contracts\BaseAbstract;

class CustomField extends BaseAbstract {

	public function __construct($raw = null) {
		if (is_object($raw)) {
			$this->raw = $raw;
		} else {
			$this->raw = new stdClass();
		}
	}

	public function id($id = null) {
		if (!isset($this->raw->field)) {
			$this->raw->field = new stdClass();
		}
		if (!is_null($id)) {
			$this->raw->field->id = $id;
		}

		return isset($this->raw->field->id) ? $this->raw->field->id : null;
	}

	public function name($name = null) {
		if (!isset($this->raw->field)) {
			$this->raw->field = new stdClass();
		}
		if (!is_null($name)) {
			$this->raw->field->name = $name;
		}

		return isset($this->raw->field->name) ? $this->raw->field->name : null;
	}

	public function value($value = null) {
		if (!is_null($value)) {
			$this->raw->value = $value;
		}

		return isset($this->raw->value) ? $this->raw->value : null;
	}

	public function __call($method, $params = array()) {
		if (count($params)) {
			$this->raw->$method = $params[0];
		}

		return isset($this->raw->$method) ? $this->raw->$method : null;
	}

	public function toArray() {
		return array(
			'id' => $this->id(),
			'name' => $this->name(),
			'value' => $this->value(),
		);
	}
}