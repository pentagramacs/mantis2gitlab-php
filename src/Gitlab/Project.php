<?php 

namespace M2G\Gitlab;

use M2G\Gitlab\Contracts\BaseAbstract;

class Project extends BaseAbstract {

	protected $endpoint = '/api/v3/projects/:id';
	protected $raw;

	public function __construct($project = null) {
		if (!is_null($project)) {
			$this->id($project);
		}
	}

	public function id($id = null) {
		if (!is_null($id)) {
			$this->id = $id;
			$this->params['id'] = urlencode($this->id);
			return $this->id;
		}

		if ($this->raw) {
			return $this->raw('id');
		}

		return $this->id;
	}

	public function isIssueEnabled() {
		return $this->raw('issues_enabled');
	}

	public function issues() {
		if (!$this->isIssueEnabled()) {
			throw new \Exception('This project have no Issues Feature enabled.');
		}

		return (new Issue($this, null))->all();
	}

	public function labels() {
		return (new Label($this, null))->all();
	}

	public function milestones() {
		return (new Milestone($this, null))->all();
	}

	public function newIssue(array $raw) {
		if (!$this->isIssueEnabled()) {
			throw new \Exception('This project have no Issues Feature enabled.');
		}

		$issue = new Issue($this, $raw);
		return $issue->save();
	}

	public function newMilestone(array $raw) {
		if (!$this->isIssueEnabled()) {
			throw new \Exception('This project have no Issues Feature enabled.');
		}

		$milestone = new Milestone($this, $raw);
		return $milestone->save();
	}
}
