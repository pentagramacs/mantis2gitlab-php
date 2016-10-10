<?php

namespace M2G\Gitlab;

use M2G\Gitlab\Contracts\BaseAbstract;

class Issue extends BaseAbstract {

	protected $endpoint = '/projects/:project_id/issues/:id';
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

	public function newNote(array $raw) {
		$note = new Note($this, $raw);
		return $note->save();
	}

	public function id() {
		return $this->raw('id');
	}

	public function notes() {
		return (new Note($this, null))->all();
	}

	public function all() {
		$rawIssues = $this->get();
		$issues = array();

		foreach($rawIssues as $issue) {
			$issues[] = new self($this->project(), $issue);
		}

		return $issues;
	}
}
