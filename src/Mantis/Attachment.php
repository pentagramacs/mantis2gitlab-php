<?php

namespace M2G\Mantis;

use stdClass;
use M2G\Mantis\Contracts\BaseAbstract;

class Attachment extends BaseAbstract {

	public function __construct($raw = null) {
		if (is_object($raw)) {
			$this->raw = $raw;
		} else {
			$this->raw = new stdClass();
		}
	}

	public function download() {
		return $this->mantis()->connection()->issue_attachment_get($this->id());
	}

	public function dateSubmitted() {
		return property_exists($this->raw, 'date_submitted') && $this->raw->date_submitted
			   ? new \DateTime($this->raw->date_submitted) 
			   : null;
	}

	public function toArray() {
		return array(
			'id' => $this->id(),
			'filename' => $this->filename(),
			'size' => $this->size(),
			'content_type' => $this->content_type(),
			'date_submitted' => $this->dateSubmitted(),
			'download_url' => $this->download_url(),
			'user_id' => $this->user_id()
		);
	}
}