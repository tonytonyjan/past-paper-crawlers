<?php

require_once __DIR__ . '/Curl.php';
/*
{
	"school": "國立交通大學",
	"department": "電機工程學系",
	"program": null,
	"subject": "線性代數與機率",
	"year": 2012,
	"exam_type": ”入學考”, // 5/25 新增
	"file_paths": [
	 "files/eed1211.pdf"
	]
}
*/
class Crawler {
	private $curl = null;
	private $save_dir = '';
	private $json_path = '';
	private $json = [];
	private $data = [
		"school"=> "",
		"department" => "",
		"program" => null,
		"subject" => "",
		"year" => 0,
		"exam_type" => "",
		"file_paths" => []
	];

	function __construct ($school, $save_dir, $json_path) {
		$this->data = (object) $this->data;
		$this->data->school = $school;
		$this->save_dir = $save_dir;
		if (!is_dir($this->save_dir)) {
			mkdir($this->save_dir, 0755, true);
		}

		$this->curl = new Curl();
		$this->curl->save_file(True);

		$this->json_path = $json_path;
		$this->json = file_exists($this->json_path) ? json_decode($this->json_path) : [];
	}

	public function save () {
		file_put_contents($this->json_path . 'past_papers.json', json_encode($this->json));
		return $this;
	}

	public function set ($key, $val) {
		$this->data->{$key} = $val;
		return $this;
	}

	// add a crawler job
	public function insert_urls ($params, $urls, $filenames = array()) {
		foreach ($urls as $i => $url) {
			$filename = isset($filenames[$i]) ? $filenames[$i] 
				: substr(strrchr($url, "/"), 1); // get filename by url
			$this->curl->url($url)
				->file_path($this->save_dir . $filename)
				->add()->get();
			$files[] = $filename;
		}
		$this->insert_files($params, $files);
		return $this;
	}

	// add a saved file into index.
	public function insert_files ($params, $files) {
		$data = $this->data;
		foreach ($params as $key => $value) {
			$data->{$key} = $value;
		}
		foreach ($files as &$file) {
			$file = $this->save_dir . $file;
		}
		$data->file_paths = $files;
		$this->json[] = $data;
		return $this;
	}
}