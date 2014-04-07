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

/**
* Registers a stream handler that maps schemes to paths.
*
* `THIS CLASS IS NOT YET COMPLETE`
* 
* This is useful if you want to have a shortcut to a specific path.
* It is of course also possible to achieve this by using a constant.
* But if you want to be able to switch streams this is your solution.
*
* Example
* -------
*
* ~~~{.php}
* // ... Controller code
* 
* // Initialize the stream handler
* Factory::load('Streams\File:streamfile_public', 'public', PUBLIC_PATH);
* 
* // Write a file
* file_put_contents('public://foo.txt', 'bar');
* 
* // Now delete it
* unlink('public://test.jpg');
* 
* // ... Controller code
* ~~~
*/
class File {
	/**
	 * The path to the log file.
	 * @var string $config
	 * @hidden
	 */
	public static $config = array();
	
	/**
	 * The path to the log file.
	 * @var string $scheme
	 */
	protected $scheme;

	/**
	 * The path to the log file.
	 * @var string $path
	 */
	protected $path;
	
	// dir parameters
	/**
	 * The path to the log file.
	 * @var string $dir
	 */
	protected $dir;

	/**
	 * The path to the log file.
	 * @var string $entries
	 */
	protected $entries;

	/**
	 * The path to the log file.
	 * @var string $entries_pos
	 */
	protected $entries_pos = 0;

	// file parameters
	/**
	 * The path to the log file.
	 * @var string $id
	 */
	protected $id;

	/**
	 * The path to the log file.
	 * @var string $entry
	 */
	protected $entry;

	/**
	 * The path to the log file.
	 * @var string $pos
	 */
	protected $pos = 0;

	/**
	 * The path to the log file.
	 * @var string $mode
	 */
	protected $mode;

	/**
	 * Implements function as defined in PHPs \streamWrapper.
	 * @param string $scheme The scheme that should be registered.
	 * @param string $path The path the scheme should map file access to.
	 */
	public function __construct($scheme = null, $path = null) {
		if(!$scheme) return;
		
		self::$config[$scheme] = array(
			'path'	=> trim($path, '/') . '/',
		);
		stream_register_wrapper($scheme, __CLASS__);
	}


	/**
	 * Implements function as defined in PHPs \streamWrapper.
	 * @hidden
	 */
	public function dir_closedir() {
		// Any resources which were locked, or allocated, during opening and use of the directory stream should be released.
		return true;
	}

	/**
	 * Implements function as defined in PHPs \streamWrapper.
	 * @param string $path As defined in PHPs \streamWrapper.
	 * @param integer $options As defined in PHPs \streamWrapper.
	 * @hidden
	 */
	public function dir_opendir($path, $options) {
		return true;
	}

	/**
	 * Implements function as defined in PHPs \streamWrapper.
	 * @hidden
	 */
	public function dir_readdir() {
		return false;
	}

	/**
	 * Implements function as defined in PHPs \streamWrapper.
	 * @hidden
	 */
	public function dir_rewinddir() {
	}

	/**
	 * Implements function as defined in PHPs \streamWrapper.
	 * @param string $path As defined in PHPs \streamWrapper.
	 * @param integer $mode As defined in PHPs \streamWrapper.
	 * @param integer $options As defined in PHPs \streamWrapper.
	 * @hidden
	 */
	public function mkdir($path, $mode, $options) {
		return false;
	}

	/**
	 * Implements function as defined in PHPs \streamWrapper.
	 * @param string $path_from As defined in PHPs \streamWrapper.
	 * @param string $path_to As defined in PHPs \streamWrapper.
	 * @hidden
	 */
	public function rename($path_from, $path_to) {
	}

	/**
	 * Implements function as defined in PHPs \streamWrapper.
	 * @param string $path As defined in PHPs \streamWrapper.
	 * @param integer $options As defined in PHPs \streamWrapper.
	 * @hidden
	 */
	public function rmdir($path, $options) {
	}

	/**
	 * Implements function as defined in PHPs \streamWrapper.
	 * @hidden
	 */
	public function stream_cast() {
		// Should return the underlying stream resource used by the wrapper, or FALSE.
		return false;
	}

	/**
	 * Implements function as defined in PHPs \streamWrapper.
	 * @hidden
	 */
	public function stream_close() {
		return fclose($this->entry);
	}

	/**
	 * Implements function as defined in PHPs \streamWrapper.
	 * @hidden
	 */
	public function stream_eof() {
		return ($this->pos === strlen($this->entry['data']) - 1);
	}

	/**
	 * Implements function as defined in PHPs \streamWrapper.
	 * @hidden
	 */
	public function stream_flush() {
		// Should return TRUE if the cached data was successfully stored (or if there was no data to store), or FALSE if the data could not be stored.
		// because er have stored the data in stream_write() there is no possibility to return false.
		return true;
	}

	/**
	 * Implements function as defined in PHPs \streamWrapper.
	 * @param integer $operation As defined in PHPs \streamWrapper.
	 * @hidden
	 */
	public function stream_lock($operation) {
		return false;
	}

	/**
	 * Implements function as defined in PHPs \streamWrapper.
	 * @param string $path As defined in PHPs \streamWrapper.
	 * @param integer $option As defined in PHPs \streamWrapper.
	 * @param mixed $value As defined in PHPs \streamWrapper.
	 * @hidden
	 */
	public function stream_metadata($path, $option, $value) {
		return false;
	}

	/**
	 * Implements function as defined in PHPs \streamWrapper.
	 * @param string $path As defined in PHPs \streamWrapper.
	 * @param string $mode As defined in PHPs \streamWrapper.
	 * @param integer $options As defined in PHPs \streamWrapper.
	 * @param string $opath As defined in PHPs \streamWrapper.
	 * @hidden
	 */
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

	/**
	 * Implements function as defined in PHPs \streamWrapper.
	 * @param integer $count As defined in PHPs \streamWrapper.
	 * @hidden
	 */
	public function stream_read($count) {
		return fread($this->entry, $count);
	}

	/**
	 * Implements function as defined in PHPs \streamWrapper.
	 * @param integer $offset As defined in PHPs \streamWrapper.
	 * @param integer $whence As defined in PHPs \streamWrapper.
	 * @hidden
	 */
	public function stream_seek($offset, $whence = SEEK_SET) {
		return fseek($this->entry, $offset, $whence);
	}

	/**
	 * Implements function as defined in PHPs \streamWrapper.
	 * @hidden
	 */
	public function stream_set_option() {
		return false;
	}

	/**
	 * Implements function as defined in PHPs \streamWrapper.
	 * @hidden
	 */
	public function stream_stat() {
		return fstat($this->entry);
	}

	/**
	 * Implements function as defined in PHPs \streamWrapper.
	 * @hidden
	 */
	public function stream_tell() {
		return ftell($this->entry);
	}

	/**
	 * Implements function as defined in PHPs \streamWrapper.
	 * @param integer $new_size As defined in PHPs \streamWrapper.
	 * @hidden
	 */
	public function stream_truncate($new_size) {
		return ftruncate($this->entry, $new_size);
	}

	/**
	 * Implements function as defined in PHPs \streamWrapper.
	 * @param string $data As defined in PHPs \streamWrapper.
	 * @hidden
	 */
	public function stream_write($data) {
		return fwrite($this->entry, $data);
	}

	/**
	 * Implements function as defined in PHPs \streamWrapper.
	 * @param string $path As defined in PHPs \streamWrapper.
	 * @hidden
	 */
	public function unlink($path) {
		$parts	= explode('://', $path, 2);
		$id		= $parts[1];
		return unlink($this->path . $id);
	}

	/**
	 * Implements function as defined in PHPs \streamWrapper.
	 * @param string $filename As defined in PHPs \streamWrapper.
	 * @hidden
	 */
	public function url_stat($filename) {
		$this->stream_open($filename, 'r', array(), $opath);
		$stats = $this->stream_stat();
		$this->stream_close();
		return $stats;
	}
}
