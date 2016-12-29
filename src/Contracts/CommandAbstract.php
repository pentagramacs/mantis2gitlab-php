<?php

namespace M2G\Contracts;

use Symfony\Component\Console\Command\Command;
use M2G\Traits\DefaultOptions;

abstract class CommandAbstract extends Command
{
	use DefaultOptions;

	protected function sanitizeOptions(array $options) {
		return array_filter($options, function($item) {
			if ($item) {
				return $item;
			}
		});
	}

	protected function splitIndexes(array $options) {
		$splitted = array();

		foreach($options as $index => $value) {
			$parts = explode('.', $index);
			$configName = array_shift($parts);
			foreach($parts as $part) {
				if (!isset($splitted[$configName])) {
					$splitted[$configName] = array();
				}

				if (!isset($splitted[$configName][$part])) {
					$splitted[$configName][$part] = $value;
				}
			}
		}
		return $splitted;
	}
	
}