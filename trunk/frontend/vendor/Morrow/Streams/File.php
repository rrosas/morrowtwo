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

namespace Morrow\Streams;

class File {
	public static $config = array();
	
	protected $scheme;
	protected $path;
	
	// dir parameters
	protected $dir;
	protected $entries;
	protected $entries_pos = 0;

	// file parameters
	protected $id;
	protected $entry;
	protected $pos = 0;
	protected $mode;

	public function __construct($scheme = null, $path = null) {
		if(!$scheme) return;
		
		self::$config[$scheme] = array(
			'path'	=> trim($path ,'/') . '/',
		);
		stream_register_wrapper($scheme, __CLASS__);
	}

	public function dir_closedir() {
		// Any resources which were locked, or allocated, during opening and use of the directory stream should be released.
		return true;
	}

	public function dir_opendir($path, $options) {
		return true;
	}

	public function dir_readdir() {
		return false;
	}

	public function dir_rewinddir() {
	}

	public function mkdir($path, $mode, $options) {
		return false;
	}

	public function rename($path, $options) {
	}

	public function rmdir($path, $options) {
	}

	public function stream_cast() {
		// Should return the underlying stream resource used by the wrapper, or FALSE.
		return false;
	}

	public function stream_close() {
		return fclose($this->entry);
	}

	public function stream_eof() {
		return ($this->pos === strlen($this->entry['data']) - 1);
	}

	public function stream_flush() {
		// Should return TRUE if the cached data was successfully stored (or if there was no data to store), or FALSE if the data could not be stored.
		// because er have stored the data in stream_write() there is no possibility to return false.
		return true;
	}

	public function stream_lock($operation) {
		return false;
	}

	public function stream_metadata($path, $option, $value) {
		return false;
	}

	public function stream_open($path, $mode, $options, &$opath) {
		$parts = explode('://', $path, 2);

		$this->scheme	= $parts[0];
		$this->id		= $parts[1];
		$this->path		= self::$config[$this->scheme]['path'];
		$this->mode		= $mode;

		$this->entry = fopen($this->path . $this->id, $mode);
		if ($this->entry === false) return false;
		return true;
	}

	public function stream_read($count) {
		return fread($this->entry, $count);
	}

	public function stream_seek($offset, $whence = SEEK_SET) {
		return fseek($this->entry, $offset, $whence);
	}

	public function stream_set_option() {
		return false;
	}

	public function stream_stat() {
		return fstat($this->entry);
	}

	public function stream_tell() {
		return ftell($this->entry);
	}

	public function stream_truncate($new_size) {
		return ftruncate($this->entry, $new_size);
	}

	public function stream_write($data) {
		return fwrite($this->entry, $data);
	}

	public function unlink($path) {
		$parts	= explode('://', $path, 2);
		$id		= $parts[1];
		return unlink($this->path . $id);
	}

	public function url_stat($filename) {
		$this->stream_open($filename, 'r', array(), $opath);
		$stats = $this->stream_stat();
		$this->stream_close();
		return $stats;
	}
}
