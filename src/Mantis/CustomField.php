<?php

namespace M2G\Mantis;

use stdClass;
use M2G\Mantis\Contracts\BaseAbstract;

class CustomField extends BaseAbstract {

	public function __construct($raw = null) {
		$this->raw = new stdClass();
		
		if (is_object($raw)) {
			$this->raw = $raw;
		}
	}

	protected function __getField($field, $value = null) {
		if (!isset($this->raw->field)) {
			$this->raw->field = new stdClass();
		}
		if (!is_null($value)) {
			$this->raw->field->$field = $value;
		}

		return isset($this->raw->field->$field) ? $this->raw->field->$field : null;
	}

	public function id($id = null) {
		return $this->__getField('id', $id);
	}

	public function name($name = null) {
		return $this->__getField('name', $name);
	}

	public function value($value = null) {
		if (!is_null($value)) {
			$this->raw->value = $value;
		}

		return isset($this->raw->value) ? $this->raw->value : null;
	}

	public function toArray() {
		return array(
			'id' => $this->id(),
			'name' => $this->name(),
			'value' => $this->value(),
		);
	}
}