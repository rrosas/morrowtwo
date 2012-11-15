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


namespace Morrow\Libraries;

class Cache {
	protected $cachedir;
	protected $user_droppable;

	public function __construct($cachedir = null, $user_droppable = false) {
		// set defaults
		if (is_null($cachedir)) $cachedir = PROJECT_PATH.'temp/_codecache/';

		// clean params
		$cachedir = Helpers\General::cleanPath($cachedir);
		if ($cachedir === false) return false;

		// validation
		if (!is_dir($cachedir)) {
			throw new \Exception(__CLASS__.': Directory "'.$dir.'" not exists.');
			return false;
		}
		if (!is_writeable($cachedir)) {
			throw new \Exception(__CLASS__.': Directory "'.$dir.'" is not writeable.');
			return false;
		}

		// set new paths
		$this->cachedir = $cachedir;
		
		// set user droppable
		$this->user_droppable = $user_droppable;
		
		return true;
	}

	public function get($id, $comparator = null) {
		// clean id
		$id = $this->_cleanId($id);

		// create cache file paths
		$cachefile			= $this->cachedir.$id;
		$cachefile_res		= $cachefile.'%res';

		// does cachefile exist?
		if (!is_file($cachefile)) return false;
		else $item = unserialize(file_get_contents($cachefile));

		$item['cachefile']		= $cachefile;
		$item['cachefile_res']	= $cachefile_res;
		
		// get cached data
		$item = $this->_getObject($item);
		
		// check valid state
		$item['valid'] = $this->_isValid($item, $comparator);
		
		return $item;		
	}
	
	public function load($id, $comparator = null) {
		// get item
		$item = $this->get($id, $comparator);
		if(!$item) return false;
		
		// check validity
		if (!$item['valid']) return false;
		
		return $item['object'];
	}

	protected function _getObject($item) {
		// load handle if necessary
		if (!isset($item['object'])) {
			if (!is_file($item['cachefile_res'])) return false;
			$item['object'] = fopen($item['cachefile_res'], 'r');
		}
		
		return $item;
	}
	
	protected function _isValid($item, $comparator = null) {
		// were no-cache headers sent?
		if ($item['user_droppable'] && isset($_SERVER['HTTP_CACHE_CONTROL']) && preg_match('/max-age=0|no-cache/i', $_SERVER['HTTP_CACHE_CONTROL'])) return false;

		// is cache file expired?
		if (time() > $item['expires']) return false;
		
		// are the comparators different?
		$comparator = $this->_createComparator($comparator);
		if ($comparator != $item['comparator']) return false;
		
		return true;
	}
	
	public function save($id, $object, $cachetime, $comparator = null, $user_droppable = null) {
		// if user_droppable is given it overwrites default
		$user_droppable = !is_null($user_droppable) ? $user_droppable : $this->user_droppable ;

		// clean id
		$id = $this->_cleanId($id);

		// define filename
		$cachefile			= $this->cachedir.$id;
		$cachefile_tmp		= $cachefile.getmypid();
		$cachefile_res		= $cachefile.'%res';
		$cachefile_res_tmp	= $cachefile_res.getmypid();

		// create cache file
		$STATUS = array();
		$STATUS['id']			= $id;
		$STATUS['created']		= time();
		$STATUS['expires']		= strtotime(date('r').' '.$cachetime); // date(r) fixes a bug with older php5 versions
		$STATUS['cachetime']	= $cachetime;
		$STATUS['comparator']	= $this->_createComparator($comparator);
		$STATUS['user_droppable']= $user_droppable;

		// add standard objects like arrays, objects and so on
		if (!is_resource($object)) {
			$STATUS['object']		= $object;

			// create hash
			$STATUS['object_hash']	= md5(serialize($object));
		} else {
			// write resource file
			$res_handle = fopen($cachefile_res_tmp, 'w');
			rewind($object);
			stream_copy_to_stream($object, $res_handle);
			fclose($res_handle);
			
			// create real cache file
			$io_result = rename($cachefile_res_tmp, $cachefile_res);

			// create hash
			$STATUS['object_hash']	= md5_file($cachefile_res);

			// could it be renamed? otherwise delete temporary file
			if ($io_result === false) {
				unlink($cachefile_res_tmp);
				return false;
			}
		}

		// write cache file
		$io_result = file_put_contents($cachefile_tmp, serialize($STATUS));

		// could cachefile be written?
		if ($io_result === false) return false;

		// create real cache file
		$io_result = rename($cachefile_tmp, $cachefile);

		// could it be renamed? otherwise delete temporary file
		if ($io_result === false) {
			unlink($cachefile_tmp);
			return false;
		}

		return $STATUS;
	}

	// Delete cache files with shell patterns
	public function delete($pattern) {
		$returner = $this -> _delete($pattern, $this->cachedir);
		return $returner;
	}

	protected function _delete($pattern, $dir) {
		$files = scandir($dir);

		// files with leading dot must not be deleted
		foreach ($files as $key=>$file) {
			if ($file[0] == '.') unset($files[$key]);
		}

		// build regex pattern
		$pattern = basename($pattern);
		$pattern = preg_replace("=[^\w\*\?]=i", '', $pattern);
		$pattern = strtolower($pattern);
		$pattern = preg_replace('=([\*|\?])=', '.$1',$pattern);
		$pattern = "=^".$pattern."$=";

		// delete the matching files
		$result = 0;
		foreach ($files as $key=>$file) {
			if (preg_match($pattern, $file)) {
				$result++;
				unlink($dir.$file);
			}
		}
		return $result;
	}

	// create valid filename from id
	protected function _cleanId($id) {
		if (empty($id)) $id = 'morrow_empty_replacement';
		$id = preg_replace("=[^\w\.]=i", '_', $id);
		$id = strtolower($id);
		return $id;
	}

	// create a comparator string from any vars
	protected function _createComparator($input) {
		if (is_array($input) OR is_object($input)) {
			// So that _sleep is not called of origin object
			if (is_object($input)) $input = clone $input;
			$output = md5(serialize($input));
		} else {
			$output = $input;
		}
		return $output;
	}
}
