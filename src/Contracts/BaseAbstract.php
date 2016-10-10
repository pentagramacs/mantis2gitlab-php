<?php

namespace M2G\Contracts;

use M2G\Configuration;

abstract class BaseAbstract {
	protected $raw;

	public function raw($attrib = null, $setValue = null) {
		if (!is_array($this->raw)) {
			$this->raw = $this->get();
		}

		$value = $this->raw;

		if (is_string($attrib) && isset($value[$attrib])) {
			if (is_null($setValue)) {
				$value = $value[$attrib];
			} else {
				$value = $value[$attrib] = $setValue;
			}
		} elseif (is_array($attrib)) {
			$value = $this->raw = $attrib;
		}

		return $value;
	}
}
