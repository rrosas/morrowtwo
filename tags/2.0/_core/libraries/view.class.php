<?php
/*////////////////////////////////////////////////////////////////////////////////
    MorrowTwo - a PHP-Framework for efficient Web-Development
    Copyright (C) 2009  Christoph Erdmann, R.David Cummins

    This file is part of MorrowTwo <http://code.google.com/p/morrowtwo/>

    MorrowTwo is free software:  you can redistribute it and/or modify
    it under the terms of the GNU Lesser General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Lesser General Public License for more details.

    You should have received a copy of the GNU Lesser General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
////////////////////////////////////////////////////////////////////////////////*/






class View {
	protected $cachetime = 0;
	protected $mode = NULL;
	protected $filename;
	protected $handler;
	protected $header = array();
	protected $cacheetag = true;

	protected $properties	= array();
	protected $filters	= array();

	public $content;

	public function __construct() {
		// set standards
		$this-> path_to_views = FW_PATH.'_core/view/';
		$this-> path_to_filters = FW_PATH.'_core/filters/';

		// get page
		$this->page = Factory::load('page');
		$this->url  = Factory::load('url');
	}

	public function setContent($value, $key = 'content', $overwrite = false) {
		// validation
		if (!is_string($key) || empty($key)) {
			trigger_error(__CLASS__.': the key has to be of type "string" and not empty.', E_USER_ERROR);
			return;
		}

		// set
		if (isset($this->content[$key]) && !$overwrite) {
			trigger_error(__CLASS__.': the key "'.$key.' is already set.', E_USER_ERROR);
			return;
		}
		else $this->content[$key] = $value;
	}

	public function getContent($key = null) {
		if (is_null($key)) return $this->content;

		if (!is_string($key) OR !isset($this->content[$key])) {
			trigger_error(__CLASS__.': key "'.$key.'" not found.', E_USER_ERROR);
			return;
		}
		return $this->content[$key];
	}

	// main method to get output
	public function get($compression_level = 0) {
		// get the underlying display handler
		$displayHandler = $this -> getDisplayHandler();

		// overwrite default properties
		$mimetype_changed = false;
		if (isset($this->properties[$this->mode]))
			foreach ($this->properties[$this->mode] as $key=>$value) {
				if (!isset($displayHandler->$key))
					trigger_error(__CLASS__.': the property "'.$key.'" does not exist for handler "'.$this->mode.'".', E_USER_ERROR);
				$displayHandler->$key = $value;
				if ($key === 'mimetype') $mimetype_changed = true;
			}

		// add charset and mimetype to the "page" array
		$this->content['page']['charset'] = $displayHandler->charset;
		$this->content['page']['mimetype'] = $displayHandler->mimetype;
		
		### set standard header lines (those headers will be cached)
		// set download header
		if (!empty($displayHandler->downloadable)) {
			if (!$mimetype_changed) {
				$displayHandler->mimetype = helperFile::getMimeType($displayHandler->downloadable);
			}
			$this->header[] = 'Content-Disposition: attachment; filename='.basename($displayHandler->downloadable);
			
			// this is a workaround for ie
			// see http://support.microsoft.com/kb/316431
			$this->header[] = 'Pragma: protected';
			$this->header[] = 'Cache-control: protected, must-revalidate';
		}

		// set content type
		$this->header[] = 'Content-Type: '.$displayHandler->mimetype.'; charset='.$displayHandler->charset;
		
		### output
		// create stream handle for the output
		$handle = fopen('php://temp/maxmemory:'.(1*1024*1024), 'r+'); // 1MB

		// get body stream
		$handle = $displayHandler->getOutput($this->getContent(), $handle);
		
		// process Filters
		$handle = $this->_processFilters($handle);

		// add compression
		if($compression_level > 0)
			$handle = $this->_compressStream($handle, $compression_level);

		// get filesize of stream
		$stats = fstat($handle);
		$this->header[] = 'Content-Length: '.$stats['size'];

		// use etag for content validation (only HTTP1.1)
		rewind($handle);
		$hash = hash_init('md5');
		hash_update_stream($hash, $handle);
		$hash = hash_final($hash);
		
		// add charset and mimetype to hash
		// if we change one of those we also want to see the actual view
		$hash = md5( $hash . $displayHandler->charset . $displayHandler->mimetype );
		
		if ($this->cacheetag) $this->header[] = 'ETag: '.$hash; // HTTP 1.1
		$this->header[] = 'Vary:';
		
		// no caching
		if ($this->cachetime == 0) {
			if ($this->cacheetag) $this->header[] = 'Cache-Control: no-cache, must-revalidate';
			else $this->header[] = 'Cache-Control: no-store, no-cache, must-revalidate'; // to overwrite default php setting with "no-store"
		}
		// caching
		else {
			$fileexpired = strtotime( $this->cachetime );
			$filemaxage = $fileexpired-time();

			// HTTP 1.0
			$this->header[] = 'Pragma: ';
			$this->header[] = 'Expires: '.gmdate("D, d M Y H:i:s", $fileexpired) ." GMT";

			// HTTP 1.1
			$this->header[] = 'Cache-Control: public, max-age='.$filemaxage;
		}

		// check for etag
		if (!isset($_SERVER['HTTP_CACHE_CONTROL']) || !preg_match('/max-age=0|no-cache/i', $_SERVER['HTTP_CACHE_CONTROL'])) // by-pass "not modified" on explicit reload
			if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == $hash) {
				$this->header[] = 'HTTP/1.1 304 Not Modified';
				// create empty stream
				$handle = fopen('php://temp/maxmemory:'.(1*1024), 'r+'); // 1kb
			}

		// rewind handle
		rewind($handle);
		
		return array(
			'headers' => $this->header,
			'content' => $handle,
		);
	}

	protected function _processFilters($handle) {
		if (!isset($this->filters[$this->mode])) return $handle;
		
		rewind($handle);
		$content = stream_get_contents($handle);
		
		// target handle
		$fhandle = fopen('php://memory', 'r+');
		fclose($handle);
			
		foreach ($this->filters[$this->mode] as $value)
			{
			$filtername = $value[0];
			$filter = new $filtername( $value[1] );
			$content = $filter->get($content);
			}
		fwrite($fhandle, $content);
		
		return $fhandle;
	}

	// enable compression
	protected function _compressStream($handle, $compression) {
		// add compression header
		$this->header[] = 'Content-Encoding: gzip';

		// use a different way to compress for small and big streams to preserve memory
		$stats = fstat($handle);
		$size = $stats['size'];

		if ($size < (1*1024*1024)) { // smaller than 1 MB
			// create gzhandler
			$gzhandle = fopen('php://memory', 'r+');

			// get and compress
			rewind($handle);
			$content = gzencode( stream_get_contents($handle), $compression);
			fclose($handle);

			// write to gzhandler
			$size = fwrite($gzhandle, $content);
			$handle = $gzhandle;
		}
		else {
			// create gzhandler
			$tmp_path = PROJECT_PATH . 'temp/_view_streams';
			if (!is_dir($tmp_path)) mkdir($tmp_path);
			$tempname = tempnam(PROJECT_PATH . 'temp/_view_streams', 'gzhandle');
			$gzhandle = gzopen($tempname, 'w');

			// get and compress
			rewind($handle);
			while (!feof($handle)) {
				$buffer = fread($handle, 4096);
				gzputs($gzhandle, $buffer);
			}
			fclose($handle);
			fclose($gzhandle);
			
			// write to gzhandler
			$size = filesize($tempname);
			$handle = fopen($tempname, 'r');
		}

		return $handle;
	}
		
	// return instance of a display handler
	protected function getDisplayHandler() {
		if($this->handler == null) {
			// get viewhandler
			$displayClassName = 'view' . $this->mode;
			
			// assign class (DO NOT USE the factory)
			$this->handler = new $displayClassName($this);
		}
		return $this->handler;
	}

	public function setCache($cachetime, $etag = true) {
		$this->cachetime = $cachetime;
		$this->cacheetag = $etag;
	}

	public function setFilter($name, $config = array(), $handler = null) {
		if ($handler == null) $handler = $this->mode;
		$this->filters[$handler][$name] = array('filter'.$name, $config);
	}

	public function unsetFilter($name, $handler = null) {
		if ($handler == null) $handler = $this->mode;
		if (isset($this->filters[$handler][$name])) unset($this->filters[$handler][$name]);
	}

	public function setHandler($mode) {
		$this -> mode = strtolower($mode);
	}

	public function setHeader($key, $value = '') {
		if (stripos($key, 'content-type') !== false) {
			trigger_error(__CLASS__.': the content-type header should not be directly set. Use setProperty("mimetype", ...) and setProperty("charset", ...) instead.', E_USER_ERROR);
		}

		$header = $key . (!empty($value) ? ': '.$value : '');
		$this->header[] = $header;
	}

	public function setProperty($key, $value = array(), $handler = null) {
		if ($handler == null) $handler = $this->mode;
		$this->properties[$handler][$key] = $value;
	}
}
