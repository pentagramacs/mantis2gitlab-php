<?php

namespace M2G\Gitlab;

use M2G\Gitlab\Contracts\BaseAbstract;
use M2G\Utils\ArrayCollection;

class Note extends BaseAbstract {

	protected $endpoint = '/api/v3/projects/:project_id/issues/:issue_id/notes/:id';
	protected $issue;

	public function __construct($issue, $raw = null) {
		$this->issue($issue);

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

		return $this->issue;
	}

	public function issue($issue = null) {
		if (!is_null($issue)) {
			$this->issue = $issue;
			$this->params['issue_id'] = urlencode($issue->id());

			$this->project($issue->project());
		}

		return $this->issue;
	}

	public function all() {
		$rawNotes = $this->get();
		$notes = array();

		foreach($rawNotes as $note) {
			$notes[] = new self($this->issue(), $note);
		}

		return new ArrayCollection($notes);
	}
}
