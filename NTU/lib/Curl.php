<?php

class Curl {
	private $getDataUsleep = 15000;
	private $OnceDownLimit = 50;
	private $tmpFolder = '';
	private $instances = array();

	function __construct () {
		$this->clear();
	}

	// initial variables definition.
	public function clear () {
		if( !isset($this->meta) ){
			$this->meta = new stdClass();
		}
		$this->instances = array();
		$this->meta->url = '';
		$this->meta->referer = False;
		$this->meta->queryArray = array();
		$this->meta->headerArray = array();
		$this->meta->method = 'GET';
		$this->meta->multiData = array();
		$this->meta->save_cookie = False;
		$this->meta->cookie_path = $this->tmpFolder . 'cookie.txt';
		$this->meta->save_file = False;
		$this->meta->file_path = '';
		$this->meta->timeout = 60;
		$this->meta->multi_limit = 50;
		$this->meta->sleep = 15000;
		$this->meta->proxy = False;
		$this->meta->useragent = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.52 Safari/537.17';
		return $this;
	}
	
	
	/* Settings */
	
	// Set Url.
	// String, Ex:http://comic.ensky.tw/
	// Default empty
	public function url($url){
		$this->meta->url = $url;
		return $this;
	}
	
	// Set Method, GET or POST.
	// String GET/POST
	// Default : GET
	public function method($method){
		$this->meta->method = strtoupper($method) === 'POST' ? 'POST' : 'GET';
		return $this;
	}
	
	// Set Referer URL.
	// String, Ex:http://comic.ensky.tw/
	// Default empty
	public function referer($referer){
		$this->meta->referer = $referer;
		return $this;
	}
	
	// Set save cookie or not.
	// Boolean
	// Default : False
	public function save_cookie($s){
		$this->meta->save_cookie = $s === True;
		return $this;
	}
	
	// Set cookie path.
	// String, EX: /tmp/cookie.txt
	// Default : $this->tmpFolder . 'cookie.txt'
	public function cookie_path($c_p){
		$this->meta->cookie_path = $c_p;
		return $this;
	}
	
	// Set save file or not.
	// Boolean
	// Default : False
	public function save_file($s){
		$this->meta->save_file = $s === True;
		return $this;
	}
	
	// Set file path.
	// String, EX: /tmp/1.jpg
	// Default empty
	public function file_path($c_p){
		$this->meta->file_path = $c_p;
		return $this;
	}
	
	// Set timeout for EACH FILE.
	// Int seconds
	// Default : 60
	public function timeout($timeout){
		$this->meta->timeout = intval($timeout);
		return $this;
	}
	
	// Set Useragent.
	// String
	// Default : Mozilla/5.0 (Windows; U; Windows NT 6.1; zh-TW; rv:1.9.2.13) Gecko/20101203 Firefox/3.6.13
	public function useragent($useragent){
		$this->meta->useragent = $useragent;
		return $this;
	}
	
	// Set the sleep time before grabing a file.
	// Int microseconds
	// Default : 15000
	public function sleep($sleep){
		$this->meta->sleep = intval($sleep);
		return $this;
	}
	
	// Set the proxy server.
	// String server
	// Default : ensky.tw:3128
	public function proxy ($server=False) {
		if ( is_array($server) ) {
			$server = count($server) > 0 ? $server[ rand( 0, count($server)-1 ) ] : False;
		}
		$this->meta->proxy = $server;
		return $this;
	}
	
	// Set Query String's Key and Value Pair.
	// String key, String value
	// no default
	public function key_value($key, $value, $escape = True){
		$this->meta->queryArray[$key] = array($value, $escape);
		return $this;
	}
	
	public function tmp_folder ($tmp) {
		$this->tmpFolder = $tmp;
		return $this;
	}
	
	
	// Set Header String's Key and Value Pair.
	// String key, String value
	// no default
	public function header($key, $value){
		$this->meta->headerArray[$key] = $value;
		return $this;
	}
	/* Get Data Methods. */
	
	// Get query string, while GET would be ?aa=xx&bb=yy,
	// and POST would be aa=xx&bb=yy
	public function query_string($encode=True){
		ksort($this->meta->queryArray);
		$str = '';
		foreach($this->meta->queryArray as $key => $row){
			list($value, $escape) = $row;
			if($str != ''){
				$str .= '&';
			}
			if( $escape && $encode ){
				if($this->meta->method === 'GET'){
					$str .= rawurlencode($key).'='.rawurlencode($value);
				} else {
					$str .= urlencode($key).'='.urlencode($value);
				}
			} else {
				$str .= $key.'='.$value;
			}
		}
		return ( $this->meta->method === 'GET' && !empty($str) )  ? '?'.$str : $str;
	}
	
	// Get header array
	public function header_array(){
		$arr = array();
		foreach($this->meta->headerArray as $key => $value){
			$arr[] = $key . ': ' . $value;
		}
		return $arr;
	}
	
	// Get url, query string included.
	public function get_url($query_string = True){
		return $this->meta->method === 'GET' && $query_string === True ? 
			   $this->meta->url . $this->query_string() : $this->meta->url;
	}
	
	/* Main Methods */
	
	// Add a job.
	public function add(){
		$this->meta->url_full = $this->get_url();
		
		if(strlen($this->meta->url_full) < 14){
			throw new Exception($this->meta->url_full .'Empty url.');
		}
		
		$ch = curl_init( $this->meta->url_full );
		if( count($this->meta->headerArray) > 0 ){
			curl_setopt($ch, CURLOPT_HTTPHEADER, $this->header_array());
		}
		if( $this->meta->save_cookie === TRUE ){
			if( !touch($this->meta->cookie_path) ){
				throw new Exception('Cannot open cookie file.');
			}
			curl_setopt($ch, CURLOPT_COOKIEJAR, $this->meta->cookie_path);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $this->meta->cookie_path);
		}
		if( $this->meta->method === 'POST' ){
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $this->query_string());
		}
		if( $this->meta->referer !== False ){
            // curl_setopt($ch,  CURLOPT_REFERER, $this->meta->referer . ';auto');
            curl_setopt($ch,  CURLOPT_REFERER, $this->meta->referer);
			// curl_setopt($ch, CURLOPT_AUTOREFERER, True);
		}
		if( $this->meta->save_file === True ){
			$file = @fopen($this->meta->file_path, "w");
			if(!$file){
				throw new Exception('Fopen Failed.');
			}
			curl_setopt($ch, CURLOPT_FILE, $file);
		}
		if ( $this->meta->proxy !== False ) {
			curl_setopt($ch, CURLOPT_PROXY, $this->meta->proxy);
		}
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->meta->timeout);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->meta->useragent);
		curl_setopt($ch, CURLOPT_HEADER, False);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, False);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, False);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, True);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		$instance = array('ch' => $ch, 'meta' => clone $this->meta);
		if( isset($file) ){
			$instance['fp'] = $file;
		}
		$this->instances[] = $instance;
		return $this;
	}

	// Get a job.
	// return HTML while not saving, and return True While saves file.
	public function get(){
		$instances = array_shift($this->instances);
		extract($instances);
		
		usleep($meta->sleep);
		
		$save_file = $meta->save_file;
		if($save_file !== True){
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			// curl_setopt($ch, CURLOPT_HEADER, 1);
		}
		
		$response = curl_exec($ch);
		
		curl_close($ch);
		
		if( $save_file === True ){
			fclose($fp);
			return $this;
		} else {
			return $response;
		}
	}
	
	
	/* Multi Methods. */
	
	// Set multi-grab limitation.
	// Int
	// Default : 50
	public function limit($limit){
		$this->meta->multi_limit = intval($limit);
		return $this;
	}
	
	// Get all jobs.
	// return a array includes all not saving jobs' HTML,
	// or True for all jobs are saving jobs.
	public function get_all($chs = False){
		$limit = $this->meta->multi_limit;
		$timeout = $this->meta->multi_limit * $this->meta->timeout;
		if($chs === False){
			$chs = $this->instances;
		}
		
		if( count($chs) > $limit ){
			$chs1 = array_slice($chs, 0, $limit);
			$chs2 = array_slice($chs, $limit);
			$data1 = $this->get_all($chs1);
			$data2 = $this->get_all($chs2);
			return $data1 === True ? $data2 : ( $data2 === True ? $data1 : array_merge($data1, $data2) );
		} else {
			$mh = curl_multi_init();
			foreach($chs as &$row){
				if( $row['meta']->save_file === False ){
					// We need to create a tmp file to save.
					$row['tmpFile'] = $this->tmpFolder . sha1( $row['meta']->url_full );
					$row['fp'] = fopen($row['tmpFile'], 'w');
					if(!$row['fp']){
						throw new Exception('Fopen '.$row['tmpFile'].' Failed.');
					}
					curl_setopt($row['ch'], CURLOPT_FILE, $row['fp']);
				}
				curl_multi_add_handle($mh,$row['ch']);
			}
			
			do{
				usleep(50);
				$n = curl_multi_exec($mh,$threads);
			} while ($threads > 0);

			$return = array();
			foreach ($chs as $row) {
				@curl_multi_remove_handle($mh,$row['ch']);
				@curl_close($row['ch']);
				@fclose ($row['fp']);
				if( $row['meta']->save_file === False ){
					if( is_file( $row['tmpFile'] ) ){
						$tmp = @file_get_contents( $row['tmpFile'] );
						@unlink( $row['tmpFile'] );
					} else {
						$tmp = @file_get_contents( $row['meta']->url_full );
					}
					if(strlen($tmp) < 50){
						throw new Exception($row['meta']->url_full.'File Get Error.');
					}
					$return[]=$tmp;
				}
			}
			curl_multi_close($mh);
			
			return count($return) != 0 ? $return : True;
		}
	}
	
	/* Old functions, for compatiblity only */
	public function getData($url,$referer=false,$login=false,$post=false){
		usleep($this->getDataUsleep);
		
		$ch = curl_init($url);
		if( $login === TRUE )
		{
			$fp = fopen("cookie.txt", "a");
			fclose($fp);
			curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
			curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
		}
		if( $post !== FALSE )
		{
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		}
		if( $referer !== FALSE )
            curl_setopt($ch,  CURLOPT_REFERER, $referer);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.1; zh-TW; rv:1.9.2.13) Gecko/20101203 Firefox/3.6.13");
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		
		$response = curl_exec($ch);
		curl_close($ch);
		return $response;
	}
	
	public function getDataWithInfo($url,$referer=false,$post=false,&$info){
		usleep($this->getDataUsleep);
		$ch = curl_init($url);
		if( $post !== FALSE )
		{
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		}
		if( $referer !== FALSE )
            curl_setopt($ch,  CURLOPT_REFERER, $referer);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.1; zh-TW; rv:1.9.2.13) Gecko/20101203 Firefox/3.6.13");
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		
		$response = curl_exec($ch);
		
		$info = curl_getinfo($ch);
		
		curl_close($ch);
		return $response;
	}
	
	public function getData_multi_tmp($urls,$ref=False){
		if( !is_dir($this->tmpFolder) ){
			mkdir($this->tmpFolder,0755);
			if( !is_dir($this->tmpFolder) ){
				throw new Exception("CURL : getData_multi_tmp: can't open new tmp folder: {$this->tmpFolder}.");
			}
		}
		$fileNames=array();
		foreach( $urls as $url ){
			$fileNames[] = $this->tmpFolder .md5( $url );
		}
		$this->getData_multi($urls,$ref,$fileNames);
		
		$data=array();
		foreach( $fileNames as $i=> $fileName ){
			if( is_file($fileName) ){
				$data[] = @file_get_contents($fileName);
				unlink($fileName);
			} else {
				$data[] = @file_get_contents($urls[$i]);
			}
		}
		return $data;
	}
	
	public function getData_multi($urls,$ref=False,$files=False){
		if($files === False){
			return getData_multi_tmp($urls,$ref);
		}
		if( count($urls) > $this->OnceDownLimit ){
			$url1 = array_slice($urls, 0, $this->OnceDownLimit);
			$url2 = array_slice($urls, $this->OnceDownLimit);
			$file1 = array_slice($files, 0, $this->OnceDownLimit);
			$file2 = array_slice($files, $this->OnceDownLimit);
			$this->getData_multi($url1,$ref,$file1);
			$this->getData_multi($url2,$ref,$file2);
		} else {
			$nums=sizeof($urls);
			$c=array();$f=array();
			$mh = curl_multi_init();
			foreach($urls as $col => $url){
				$c[$col] = curl_init($url);
				$f[$col] = fopen($files[$col], "w");
				if( $f[$col] === False )
					throw new Exception('CURL : fopen "'.$files[$col].'" failed.');
				else if( $c[$col] == False )
					throw new Exception('CURL : curl_init "'.$url.'" failed.');
				if($ref){
					curl_setopt($c[$col], CURLOPT_REFERER, $ref);
				}
				curl_setopt($c[$col], CURLOPT_FILE, $f[$col]);
				curl_setopt($c[$col], CURLOPT_HEADER, 0);
				curl_setopt($c[$col], CURLOPT_TIMEOUT,600);
				curl_multi_add_handle($mh,$c[$col]);
			}
			
			do{
				usleep(50);
				$n=curl_multi_exec($mh,$threads);
			}
			while ($threads > 0);

			foreach ($urls as $col => $url) {
				curl_multi_remove_handle($mh,$c[$col]);
				curl_close($c[$col]);
				fclose ($f[$col]);
			}
			curl_multi_close($mh);
		}
	}
}
