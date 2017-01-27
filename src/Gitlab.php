<?php

namespace M2G;

use SoapClient;

class Gitlab {

	protected $configuration = array();

	public function __construct($configuration) {
                if (
                        empty($configuration['endpoint']) ||
                        empty($configuration['access_token']) ||
                        empty($configuration['project']) 
                ) {
                        throw new \Exception('You are missing configuration settings. You MUST set endpoint, access_token and project.' . PHP_EOL . 'Check the options below.');
                }

		$this->configuration = $configuration;
	}

	public function config($config = null) {
		$data = $this->configuration;

		if (!is_null($config) && isset($data[$config])) {
			$data = $data[$config];
		}

		return $data;
	}

	public function __call($method, $params) {
		$subName = __NAMESPACE__ . '\Gitlab\\' . ucfirst($method);
		if (class_exists($subName, true)) {
			$instance = new $subName(array_shift($params));
			$instance->gitlab($this);
			return $instance;
		}
		
		throw new \Exception('Method ' . $subName . ' does not exist');
	}

}
