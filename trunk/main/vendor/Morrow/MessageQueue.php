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


namespace Morrow;


// the next file to work with always has the extension "mq_processing"

class MessageQueue {
	protected $_save_path;
	protected $_program;

	public function __construct($config) {
		// set save path
		if (!is_dir($config['save_path'])) mkdir($config['save_path']);
		$this->_save_path = $config['save_path'];

		// set cli_path
		$this->_cli_path = $config['cli_path'];
	}
	
	public function enqueue($controller, $data) {
		// save request to file with id
		$id = microtime(true) . '_' . uniqid('_', true) . '.mq';
		
		$json = json_encode(array(
			'controller' => $controller,
			'data' => $data,
		));
		
		$id_file = $this->_save_path . $id;
		file_put_contents($id_file, $json);

		// trigger processing if not running at the moment
		$lockfile = $this->_save_path . 'mq.lock';
		if (!is_file($lockfile)) {
			// write lock file
			file_put_contents($lockfile, '');

			// we rename the file to mq_processing because if there is an error this file will be kept
			rename($id_file, $id_file . '_processing');
			
			// trigger current entry
			$command = $this->_cli_path . ' ' . getcwd() . '/index.php' . ' ' . $controller;
			$this->_execInBackground($command);
		}

		return $id;
	}

	public function getItem() {
		// get a list of all mq files and choose the oldest
		$files = glob($this->_save_path . '*.mq_processing');
		sort($files);
		$item = json_decode(file_get_contents($files[0]), true);

		return $item;
	}

	public function next($success) {
		// write lock file
		$lockfile = $this->_save_path . 'mq.lock';
		file_put_contents($lockfile, '');

		// get a list of all mq files and choose the oldest
		$files = glob($this->_save_path . '*.mq_processing');
		if ($success) {
			if (isset($files[0])) {
				unlink($files[0]);
			}
		} else {
			rename($files[0], $files[0] . '_failed');
		}

		$files = glob($this->_save_path . '*.mq');
		sort($files);

		if (count($files) === 0) {
			// there is no entry to process anymore
			unlink($lockfile);
			return false;
		}

		$item = json_decode(file_get_contents($files[0]), true);

		// we rename the file to mq_processing because if there is an error this file will be kept
		rename($files[0], $files[0] . '_processing');

		// trigger next entry
		$command = $this->_cli_path . ' ' . getcwd() . '/index.php' . ' ' . $item['controller'];
		$this->_execInBackground($command);

		return true;
	}

	protected function _execInBackground($cmd) { 
		if (substr(php_uname(), 0, 7) == "Windows") {
			pclose(popen("start /B ". $cmd, "r"));
		} else {
			exec($cmd . " > /dev/null &");
		}
	} 	
}
	
