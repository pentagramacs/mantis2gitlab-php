<?php

namespace M2G\Gitlab;

use M2G\Gitlab\Contracts\BaseAbstract;

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
		$milestones = array();

		// get from all pages (but we don't know how many haha)
		$page = 1;
		do {
			$this->query_params = array('state' => 'all', 'page' => $page);
			$rawMilestones = $this->get();
			foreach($rawMilestones as $milestone) {
				$milestones[] = new self($this->project, $milestone);
			}

			$page++;
		} while($rawMilestones);

		return $milestones;
	}
}
