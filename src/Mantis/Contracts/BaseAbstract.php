<?php 

namespace M2G\Mantis\Contracts;

use M2G\Contracts\BaseAbstract as M2GAbstract;

abstract class BaseAbstract extends M2GAbstract {

	protected $mantis;

	public function mantis(\M2G\Mantis $mantis = null) {
		if (!is_null($mantis)) {
			$this->mantis = $mantis;
		}

		return $this->mantis;
	}

	public function __call($method, $params = array()) {
		if (count($params)) {
			$this->raw->$method = $params[0];
		}

		return isset($this->raw->$method) ? $this->raw->$method : null;
	}
}