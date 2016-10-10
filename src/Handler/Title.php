<?php

namespace M2G\Handler;

use M2G\Contracts\HandlerAbstract;
use M2G\Contracts\BaseAbstract;

class Title extends HandlerAbstract {

	public function handle(BaseAbstract $mantisIssue) {
		$title = $mantisIssue->summary();

		$mantisIssueArr = $mantisIssue->toArray();
		if (($prefix = $this->config()->get('gitlab.prefix'))) {
			preg_match_all('/:([\w]+)/', $prefix, $matches);

			foreach($matches[1] as $i => $key) {
				if (!empty($mantisIssueArr[$key])) {
					$prefix = str_replace($matches[0][$i], $mantisIssueArr[$key], $prefix);
				}
			}

			$title = trim($prefix) . ' ' . $title;
		}

		return $title;
	}
}