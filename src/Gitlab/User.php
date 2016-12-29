<?php 

namespace M2G\Gitlab;

use M2G\Gitlab\Contracts\BaseAbstract;
use M2G\Utils\ArrayCollection;

class User extends BaseAbstract {

	protected $endpoint = '/api/v3/users/:id';

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

		return new ArrayCollection($users);
	}
}