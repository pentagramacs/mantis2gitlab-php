<?php

namespace M2G\Mantis;

use stdClass;

class Version {

	public function __construct($raw = null) {
		if (is_object($raw)) {
			$this->raw = $raw;
		} else {
			$this->raw = new stdClass();
		}
	}

	public function __call($method, $params = array()) {
		if (count($params)) {
			$this->raw->$method = $params[0];
		}

		return isset($this->raw->$method) ? $this->raw->$method : null;
	}

	public function dateOrder() {
		return new \DateTime($this->raw->date_order);
	}

	public function toArray() {
		return array(
			'id' => $this->id(),
			'name' => $this->name(),
			'date_order' => $this->dateOrder(),
			'released' => $this->released(),
			'obsolete' => $this->obsolete(),
		);
	}
}