<?php

namespace M2G\Mantis;

class Enum extends Contracts\BaseAbstract {

	protected $_priorities = array();
	protected $_severities = array();
	protected $_reproducibilities = array();
	protected $_projections = array();
	protected $_etas = array();

	public function priorities() {
		if (!$this->_priorities) {
			$priorities = $this->mantis()->connection()->enum_priorities();
			foreach($priorities as $priority) {
				$this->_priorities[$priority->id] = $priority->name;
			}
		}

		return $this->_priorities;
	}

	public function severities() {
		if (!$this->_severities) {
			$severities = $this->mantis()->connection()->enum_severities();
			foreach($severities as $severity) {
				$this->_severities[$severity->id] = $severity->name;
			}
		}

		return $this->_severities;
	}

	public function reproducibilities() {
		if (!$this->_reproducibilities) {
			$reproducibilities = $this->mantis()->connection()->enum_reproducibilities();
			foreach($reproducibilities as $reproducibility) {
				$this->_reproducibilities[$reproducibility->id] = $reproducibility->name;
			}
		}

		return $this->_reproducibilities;
	}

	public function projections() {
		if (!$this->_projections) {
			$projections = $this->mantis()->connection()->enum_projections();
			foreach($projections as $projection) {
				$this->_projections[$projection->id] = $projection->name;
			}
		}

		return $this->_projections;
	}

	public function etas() {
		if (!$this->_etas) {
			$etas = $this->mantis()->connection()->enum_etas();
			foreach($etas as $eta) {
				$this->_etas[$eta->id] = $eta->name;
			}
		}

		return $this->_etas;
	}

	public function resolutions() {
		if (!$this->_resolutions) {
			$resolutions = $this->mantis()->connection()->enum_resolutions();
			foreach($resolutions as $resolution) {
				$this->_resolutions[$resolution->id] = $resolution->name;
			}
		}

		return $this->_resolutions;
	}

	public function levels() {
		if (!$this->_levels) {
			$levels = $this->mantis()->connection()->enum_levels();
			foreach($levels as $level) {
				$this->_levels[$level->id] = $level->name;
			}
		}

		return $this->_levels;
	}

	public function statuses() {
		if (!$this->_statuses) {
			$statuses = $this->mantis()->connection()->enum_status();
			foreach($statuses as $status) {
				$this->_statuses[$status->id] = $status->name;
			}
		}

		return $this->_statuses;
	}

	public function projectViewStates() {
		if (!$this->_project_view_states) {
			$project_view_states = $this->mantis()->connection()->enum_project_view_states();
			foreach($project_view_states as $state) {
				$this->_project_view_states[$state->id] = $state->name;
			}
		}

		return $this->_project_view_states;
	}

	public function states() {
		if (!$this->_view_states) {
			$view_states = $this->mantis()->connection()->enum_view_states();
			foreach($view_states as $state) {
				$this->_view_states[$state->id] = $state->name;
			}
		}

		return $this->_view_states;
	}

	public function get($enum) {
		if (empty($this->_enums[$enum])) {
			$this->_enums[$enum] = array();
			$enums = $this->mantis()->connection()->enum_get($enum);
			foreach($enums as $enum) {
				$this->_enums[$enum][$enum->id] = $enum->name;
			}
		}

		return $this->_enums[$enum];
	}

}