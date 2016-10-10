<?php

namespace M2G\Mantis;

use stdClass;

class Note {

	public function __construct($raw = null) {
		if (is_object($raw)) {
			$this->raw = $raw;
		} else {
			$this->raw = new stdClass();
		}
	}

	public function __call($method, $params = array()) {
		if (count($params)) {
			$this->raw->$method = $params[0];
		}

		return isset($this->raw->$method) ? $this->raw->$method : null;
	}

	public function dateSubmitted() {
		return new \DateTime($this->raw->date_submitted);
	}

	public function lastModified() {
		return new \DateTime($this->raw->last_modified);
	}

	public function toArray() {
		return array(
			'id' => $this->id(),
			'reporter' => $this->reporter()->name,
			'text' => $this->text(),
			'view_state' => $this->view_state()->name,
			'date_submitted' => $this->dateSubmitted(),
			'last_modified' => $this->lastModified(),
			'time_tracking' => $this->time_tracking(),
			'note_type' => $this->note_type(),
			'note_attr' => $this->note_attr(),
		);
	}
}