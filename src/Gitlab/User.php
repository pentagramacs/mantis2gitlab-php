<?php 

namespace M2G\Gitlab;

use M2G\Gitlab\Contracts\BaseAbstract;

class User extends BaseAbstract {

	protected $endpoint = '/users/:id';

	public function __construct($raw = null) {
		if (!is_null($raw)) {
			$this->raw = $raw;
		}
	}

	public function all() {
		$rawUsers = $this->get();
		$users = array();

		foreach($rawUsers as $user) {
			$users[$user['username']] = new self($user);
		}

		return $users;
	}
}