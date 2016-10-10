<?php

namespace M2G\Contracts;

use M2G\Configuration;

abstract class HandlerAbstract {

	/**
	 * Configuration object
	 * @var Configuration
	 */
	protected $config;

	/**
	 * Inject the configuration object so we can handle
	 * gitlab and mantis configurations
	 * 
	 * @param Configuration $config
	 */
	public function __construct(Configuration $config) {
		$this->config = $config;
	}

	/**
	 * Returns the configuration
	 * 
	 * @return Configuration
	 */
	public function config() {
		return $this->config;
	}

	/**
	 * This should be the entry point for the handler
	 * 
	 * @param  BaseAbstract $base  Base class (can be anything that extends BaseAbstract)
	 * @param  mixed        $value Valor que será verificado e retornado
	 * @return string
	 */
	abstract public function handle(BaseAbstract $base);

}