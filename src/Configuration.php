<?php

namespace M2G;

class Configuration {

	protected $configs = array();

	public function __construct($path, $override = array()) {
		$this->path = $path;

		if ($this->path{strlen($this->path)-1} !== '/') {
			$this->path .= '/';
		}

		$files = glob($this->path . '*.php');
		foreach($files as $file) {
			$pathParts = explode(DIRECTORY_SEPARATOR, $file);
			$fileName = array_pop($pathParts);
			$configName = substr($fileName, 0, strpos($fileName, '.'));
	
			$this->configs[$configName] = include $file;
		}

		foreach($override as $configName => $options) {
			if (empty($this->configs[$configName])) {
				$this->configs[$configName] = array();
			}
			$this->configs[$configName] = array_merge($this->configs[$configName], $options);
		}
	}

	public function __call($method, $params = array()) {
		$configuration = null;

		if (isset($this->configs[$method])) {
			$configuration = $this->configs[$method];
		}

		foreach($params as $param) {
			if (!empty($configuration[$param])) {
				$configuration = $configuration[$param];
			}
		}

		return $configuration;
	}

	/**
	 * Handle configuration ease access
	 *
	 * Other params must obey the params depth
	 * like: gitlab.endpoint
	 * 
	 * @param  [type] $variable [description]
	 * @return [type]           [description]
	 */
	public function get($variable) {
		$variable = explode('.', $variable);

		$config = $this;
		foreach($variable as $i => $value) {
			if ($i === 0) {
				$config = $config->$value();
			} elseif (isset($config[$value])) {
				$config = $config[$value];
			} elseif ($value === '') {
				$config = null;
			} else {
				$config = null;
			}
		}

		return $config;
	}

}
