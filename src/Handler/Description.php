<?php

namespace M2G\Handler;

use M2G\Contracts\HandlerAbstract;
use M2G\Contracts\BaseAbstract;

class Description extends HandlerAbstract {

	public function handle(BaseAbstract $mantisIssue) {
		$mantisIssueArr = $mantisIssue->toArray();

		$description = str_replace("\n", "\n\n", $mantisIssueArr['description']);
		if (($appends = $this->config()->get('gitlab.append_description'))) {
			foreach($appends as $append) {
				if (!is_array($append)) {
					$append = array($append, null);
				}

				list($value, $title) = $append;

				if ($value && !empty($mantisIssueArr[$value])) {
					if ($title) {
						$description .= PHP_EOL . PHP_EOL . '## ' . $title;
					}
					$description .= PHP_EOL . PHP_EOL;
					$description .= str_replace("\n", "\n\n", $mantisIssueArr[$value]);
					$description .= PHP_EOL . PHP_EOL;
				}
			}
		}

		return $description;
	}
}
