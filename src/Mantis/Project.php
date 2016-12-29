<?php

namespace M2G\Mantis;

use M2G\Mantis\Contracts\BaseAbstract;
use M2G\Mantis\Issue;
use M2G\Utils\ArrayCollection;

class Project extends BaseAbstract {

	protected $id;

	protected $name;

	public function __construct($project) {
		$this->project = $project;
	}

	public function all() {
		return new ArrayCollection($this->mantis()->connection()->projects_get_user_accessible());
	}

	public function get() {
		$allAccess = $this->all();
		return $this->__searchProject($allAccess);
	}

	public function __searchProject($projects)
	{
		foreach($projects as $project) {
			if ($project->name == $this->project || 
				$project->id == $this->project || 
				$project->id == $this->id) 
			{
				return (array)$project;
			}

			if ($project->subprojects && ($project = $this->__searchProject($project->subprojects))) {
				return $project;
			}
		}

		return array();
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
		return new ArrayCollection($categories);
	}

	public function issues() {
		$raw = $this->mantis()->connection()->project_get_issues($this->id(), 1, -1);
		$project = $this;
		$issues = array_map(function($item) use ($project) {
			$issue = new Issue($item);
			$issue->mantis($project->mantis());
			return $issue;
		}, $raw);

		return new ArrayCollection($issues);
	}

	public function versions() {
		$raw = $this->mantis()->connection()->project_get_versions($this->id());

		$versions = array_map(function($item) {
			return new Version($item);
		}, $raw);

		return new ArrayCollection($versions);
	}

}
