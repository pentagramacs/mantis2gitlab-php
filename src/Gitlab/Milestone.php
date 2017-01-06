<?php

namespace M2G\Gitlab;

use M2G\Gitlab\Contracts\BaseAbstract;
use M2G\Utils\ArrayCollection;

class Milestone extends BaseAbstract {

	protected $endpoint = '/api/v3/projects/:project_id/milestones/:id';
	protected $project;

	public function __construct($project, $raw = null) {
		$this->project($project);

		if (!is_null($raw)) {
			$this->raw = $raw;
		}
	}

	public function project($project = null) {
		if (!is_null($project)) {
			$this->project = $project;
			$this->gitlab($project->gitlab());
			$this->params['project_id'] = urlencode($project->id());
		}

		return $this->project;
	}

	public function all() {
		$milestones = parent::all();
		$arr = [];
		foreach($milestones as $milestone) {
			$arr[$milestone->raw('title')] = $milestone;
		}
		return new ArrayCollection($arr);
	}
}
