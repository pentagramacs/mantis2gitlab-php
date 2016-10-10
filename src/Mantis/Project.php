<?php

namespace M2G\Mantis;

use M2G\Mantis\Contracts\BaseAbstract;
use M2G\Mantis\Issue;

class Project extends BaseAbstract {

	protected $id;

	protected $name;

	public function __construct($project) {
		$this->project = $project;
	}

	public function get() {
		$id = $this->mantis()->connection()->projects_get_user_accessible(array('id' => $this->id()));
var_dump($id);
die;
		return $this->id;
	}

	public function id() {
		if (empty($this->id)) {
			$id = $this->mantis()->connection()->getProjectIdByName($this->project);

			if ($id !== 0) {
				$this->id = $id;
				$this->name = $this->project;
			} else {
				$this->id = $id;
			}
		}

		return $this->id;
	}

	public function categories() {
		$categories = $this->mantis()->connection()->project_get_categories($this->id());
		return $categories;
	}

	public function issues() {
		$raw = $this->mantis()->connection()->project_get_issues($this->id());
		$issues = array();

		foreach($raw as $rawIssue) {
			$issues[] = new Issue($rawIssue);
		}

		return $issues;
	}
}
