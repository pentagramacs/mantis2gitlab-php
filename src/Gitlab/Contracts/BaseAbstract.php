<?php 

namespace M2G\Gitlab\Contracts;

use M2G\Contracts\BaseAbstract as M2GAbstract;

abstract class BaseAbstract extends M2GAbstract {

	protected $user_agent = 'Mantis2Gitlab/1.0';

	protected $headers = array(
		'Accept: application/json',
		'Content-type: application/json',
		'Connection: Keep-Alive'
	);

	protected $params = array();

	protected $gitlab;

	public function gitlab(\M2G\Gitlab $gitlab = null) {
		if (!is_null($gitlab)) {
			$this->gitlab = $gitlab;
		}

		return $this->gitlab;
	}

	public function get($id = null) {
		if (!is_null($id)) {
			$this->params['id'] = urlencode($id);
		}

		$this->authorize();

		$url = $this->url($this->endpoint);
		$rawbody = $this->request($url, 'get');

		return json_decode($rawbody, true);
	}

	public function post($data) {
		$this->authorize();

		$url = $this->url($this->endpoint);
		$rawbody = $this->request($url, 'post', $data);

		return json_decode($rawbody, true);
	}

	public function request($url, $method = 'get', $postdata = array()) {
		// we are the parent 
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, $url); 
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
		curl_setopt($ch, CURLOPT_HEADER, false); 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_NOBODY, false); // remove body 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		if (strtolower($method) == 'post') {
			curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($postdata)); 
			curl_setopt($ch, CURLOPT_POST, 1);
		}
		$rawbody = curl_exec($ch); 

		if ($error = curl_error($ch)) {
			throw new \Exception($error);
		}

		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
		curl_close($ch); 

		pcntl_wait($ch); //Protect against Zombie children 

		if ($httpCode === 403) {
			throw new \Exception('The access_token used is not authorized.', 403);
		}

		if(!$rawbody) {
			return false; 
		}

		return $rawbody;
	}

	public function url($endpoint) {
		$url = $this->gitlab()->config('endpoint') . $endpoint;
		$params = $this->params;
		$matches = array();

		preg_match_all('/:([^\/]+)/', $url, $matches);

		foreach($matches[0] as $i => $match) {
			$url = str_replace(
				'/' . $match, 
				isset($params[$matches[1][$i]]) ? 
				'/' . $params[$matches[1][$i]] : 
				'', 
				$url
			);
		}
		return $url;
	}

	public function authorize() {
		$authorization = 'PRIVATE-TOKEN: ' . $this->gitlab()->config('access_token');
		if (!in_array($authorization, $this->headers)) {
			$this->headers[] = $authorization;
		}
		return $authorization;
	}

	public function save() {
		$savedIssue = $this->post($this->raw());
		$this->raw($savedIssue);
		return $this;
	}
}
