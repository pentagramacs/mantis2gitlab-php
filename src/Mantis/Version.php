<?php

namespace M2G\Mantis;

use stdClass;
use M2G\Mantis\Contracts\BaseAbstract;

class Version extends BaseAbstract {

	public function __construct($raw = null) {
		if (is_object($raw)) {
			$this->raw = $raw;
		} else {
			$this->raw = new stdClass();
		}
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