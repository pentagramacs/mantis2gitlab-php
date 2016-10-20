<?php

namespace M2G\Gitlab;

use M2G\Gitlab\Contracts\BaseAbstract;
use CURLFile;

class Upload extends BaseAbstract {

	protected $endpoint = '/api/v3/projects/:project_id/uploads';
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

	public function save() {
		$this->authorize();

		unset($this->headers[1]);
		$this->headers[] = 'Content-type: multipart/form-data';

		$url = $this->url($this->endpoint);
		if (!isset($this->raw['data'])) {
			throw new \Exception('You canno\'t upload a file without setting it\'s content here.');
		}

		$fileName = '/tmp/gitlab/' . $this->raw['file'];
		if (!is_dir(dirname($fileName))) {
			mkdir(dirname($fileName), 0755, true);
		}

		if (!file_put_contents($fileName, $this->raw['data'])) {
			throw new \Exception('We failed writing to the temporary file.');
		} else {
			unset($this->raw['data']);
		}

		$postdata = new CURLFile($fileName, $this->raw['content_type'], basename($fileName));

		$rawbody = $this->request($url, 'post', array('file' => $postdata));

		$this->raw(json_decode($rawbody, true));

		return $this;
	}

	public function request($url, $method = 'get', $postdata = array()) {
		// we are the parent 
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, $url); 
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
		curl_setopt($ch, CURLOPT_HEADER, false); 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_NOBODY, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		curl_setopt($ch, CURLOPT_SAFE_UPLOAD, true);

		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);

		$rawbody = curl_exec($ch); 

		if ($error = curl_error($ch)) {
			throw new \Exception($error);
		}

		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch); 

		pcntl_wait($ch); //Protect against Zombie children 

		if ($httpCode < 200 && $httpCode >= 400) {
			throw new \Exception($rawbody);
		}

		if(!$rawbody) {
			return false; 
		}

		return $rawbody;
	}
}
