<?php

namespace M2G\Handler;

use M2G\Contracts\HandlerAbstract;
use M2G\Contracts\BaseAbstract;

class Label extends HandlerAbstract {

	public function handle(BaseAbstract $mantisIssue) {
		$labels = array();

		if ($this->config()->get('gitlab.map_status') && ($label = $this->handleStatus($mantisIssue))) {
			$labels[] = $label;
		}

		if ($this->config()->get('gitlab.map_resolution') && ($label = $this->handleResolution($mantisIssue))) {
			$labels[] = $label;
		}

		if ($this->config()->get('gitlab.map_category') && ($label = $this->handleCategory($mantisIssue))) {
			$labels[] = $label;
		}

		if ($this->config()->get('gitlab.map_severity') && ($label = $this->handleSeverity($mantisIssue))) {
			$labels[] = $label;
		}

		if ($this->config()->get('gitlab.map_severity') && ($label = $this->handleSeverity($mantisIssue))) {
			$labels[] = $label;
		}

		if ($this->config()->get('gitlab.map_priority') && ($label = $this->handlePriority($mantisIssue))) {
			$labels[] = $label;
		}

		if ($this->config()->get('gitlab.map_frequency') && ($label = $this->handleFrequency($mantisIssue))) {
			$labels[] = $label;
		}

		if ($this->config()->get('gitlab.map_custom_fields') && ($label = $this->handleCustomFields($mantisIssue))) {
			$labels[] = $label;
		}

		return implode(',', $labels);
	}

	public function handleStatus(BaseAbstract $mantisIssue) {
		$labels = array();

		$status = $mantisIssue->status();
		if ($status) {
			$labels[] = $this->getLabelFromConfiguration($status->name);
		}

		return implode(',', $labels);
	}

	public function handleResolution(BaseAbstract $mantisIssue) {
		$labels = array();

		$resolution = $mantisIssue->resolution();
		if ($resolution) {
			$labels[] = $this->getLabelFromConfiguration($resolution->name);
		}

		return implode(',', $labels);
	}

	public function handleCategory(BaseAbstract $mantisIssue) {
		$labels = array();

		$category = $mantisIssue->category();
		if ($category) {
			$labels[] = $this->getLabelFromConfiguration($category);
		}

		return implode(',', $labels);
	}

	public function handleSeverity(BaseAbstract $mantisIssue) {
		$labels = array();

		$severity = $mantisIssue->severity();
		if ($severity) {
			$labels[] = $this->getLabelFromConfiguration($severity->name);
		}
		return implode(',', $labels);
	}

	public function handlePriority(BaseAbstract $mantisIssue) {
		$labels = array();

		$priority = $mantisIssue->priority();
		if ($priority) {
			$labels[] = $this->getLabelFromConfiguration($priority->name);
		}
		return implode(',', $labels);
	}

	public function handleFrequency(BaseAbstract $mantisIssue) {
		$labels = array();

		$frequency = $mantisIssue->frequency();
		if ($frequency) {
			$labels[] = $this->getLabelFromConfiguration($frequency->name);
		}
		return implode(',', $labels);
	}

	public function handleCustomFields(BaseAbstract $mantisIssue) {
		$labels = array();

		$customFields = $mantisIssue->customFields();
		foreach($customFields as $field) {
			$title = $this->config()->get('labels.' . $field->name());
			$values = explode('|', $field->value());
			foreach($values as $value) {
				if (($tmp = $this->config()->get('labels.' . $value))) {
					$value = $tmp;
				}

				if ($title) {
					$labels[] = "$title: $value";
				} else {
					$labels[] = $value;
				}
			}
		}

		return implode(',', $labels);
	}

	public function getLabelFromConfiguration($label) {
		if (!is_string($label)) {
			throw new \Exception('We only accept `string` type to search for label.');
		}

		return $this->config()->get('labels.' . $label);
	}
}