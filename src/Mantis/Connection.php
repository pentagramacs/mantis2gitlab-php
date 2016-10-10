<?php

namespace M2G\Mantis;

use SoapClient;

class Connection extends SoapClient {

	protected $credentials = array();

	public function __call($methodName, $params) {
		if (is_array($params)) {
			$params = array_merge($this->credentials(), $params);
		} else {
			$params = array();
		}

		return parent::__call('mc_' . $methodName, $params);
	}

	public function credentials($credentials = null) {
		if (!is_null($credentials) && is_array($credentials)) {
			$this->credentials = $credentials;
		}
		return $this->credentials;
	}

	public function getProjectIdByName($name) {
		return $this->project_get_id_from_name($name);
	}
}
