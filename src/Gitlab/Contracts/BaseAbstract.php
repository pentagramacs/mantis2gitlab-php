<?php 

namespace M2G\Gitlab\Contracts;

use M2G\Contracts\BaseAbstract as M2GAbstract;
use M2G\Utils\ArrayCollection;

abstract class BaseAbstract extends M2GAbstract {

	protected $userAgent = 'Mantis2Gitlab/1.0';

	protected $headers = array(
		'Accept: application/json',
		'Content-type: application/json',
		'Connection: Keep-Alive'
	);

	protected $params = array();
	protected $queryParams = array();

	protected $gitlab;

	public function gitlab(\M2G\Gitlab $gitlab = null) {
		if (!is_null($gitlab)) {
			$this->gitlab = $gitlab;
		}

		return $this->gitlab;
	}

	public function get($uid = null) {
		if (!is_null($uid)) {
			$this->params['id'] = urlencode($uid);
		}

		$this->authorize();

		$url = $this->url($this->endpoint);

		if ($this->queryParams) {
			$url .= '?' . http_build_query($this->queryParams);
		}

		$rawbody = $this->request($url, 'get');

		return json_decode($rawbody, true);
	}

	public function post($data) {
		$this->authorize();

		$url = $this->url($this->endpoint);
		$rawbody = $this->request($url, 'post', $data);

		return json_decode($rawbody, true);
	}

	public function put($data) {
		$this->authorize();

		$url = $this->url($this->endpoint);
		$rawbody = $this->request($url, 'put', $data);

		return json_decode($rawbody, true);
	}

	public function request($url, $method = 'get', $postdata = array()) {
		// we are the parent 
		$curl = curl_init(); 
		curl_setopt($curl, CURLOPT_URL, $url); 
		curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
		curl_setopt($curl, CURLOPT_USERAGENT, $this->userAgent);
		curl_setopt($curl, CURLOPT_HEADER, false); 
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_NOBODY, false); // remove body 
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		$isQueryString = strpos($url, '?') === false ? '?' : null;

		curl_setopt($curl, CURLOPT_URL, $url . $isQueryString . http_build_query($postdata)); 
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, strtoupper($method));

		if (in_array(strtolower($method), array('post'))) {
			curl_setopt($curl, CURLOPT_URL, $url . $isQueryString . http_build_query($postdata)); 
			curl_setopt($curl, CURLOPT_POST, 1);
		}

		$rawbody = curl_exec($curl); 

		if ($error = curl_error($curl)) {
			throw new \Exception($error);
		}

		$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE); 
		curl_close($curl); 

		pcntl_wait($curl); //Protect against Zombie children 

		if ($httpCode === 403) {
			throw new \Exception('The access_token used is not authorized.', 403);
		}

		if(!$rawbody) {
			return false; 
		}

		return $rawbody;
	}

	public function all() {
		$data = array();

		// get from all pages (but we don't know how many haha)
		$page = 1;
		do {
			$this->queryParams = array('state' => 'all', 'page' => $page);
			$raw = $this->get();
			foreach($raw as $item) {
				$data[] = new $this($this->project, $item);
			}

			$page++;
		} while($raw);

		return new ArrayCollection($data);
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
		$savedIssue = (($uid = $this->raw('id')) && is_int($uid) && $uid > 0) ? 
					  $this->put($this->raw()) :
					  $this->post($this->raw());

		$this->raw($savedIssue);
		return $this;
	}
}
