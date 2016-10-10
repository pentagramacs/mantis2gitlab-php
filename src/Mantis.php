<?php

namespace M2G;

use SoapClient;

class Mantis {

	protected $wsdl = null;
	protected $credentials = array();
	protected $connection;

	public function __construct(array $configuration) {
		$this->wsdl = $configuration['wsdl'];
		$this->credentials = array(
			'username' => $configuration['username'],
			'password' => $configuration['password']
		);

		$this->version = $this->connection()->version();
	}

	public function connection($connection = null) {
		if (!is_null($connection)) {
			$this->connection = $connection;
		}

		if (!is_object($this->connection)) {
			$this->connection = new Mantis\Connection($this->wsdl);
			$this->connection->credentials($this->credentials);
		}

		return $this->connection;
	}

	public function __call($method, $params) {
		$subName = __NAMESPACE__ . '\Mantis\\' . ucfirst($method);
		if (class_exists($subName, true)) {
			$instance = new $subName(array_shift($params));
			$instance->mantis($this);
			return $instance;
		}

		var_dump('method does not exists');
		var_dump($method, $params);die;
		
		throw new \Exception('Method ' . $className . ' does not exist');
	}

}
