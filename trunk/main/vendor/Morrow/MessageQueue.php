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

class MessageQueue {
	protected $_save_path;
	protected $_program;
	protected $_controller_path;

	public function __construct($config) {
		// set save path
		if (!is_dir($config['save_path'])) mkdir($config['save_path']);
		$this->_save_path = $config['save_path'];

		// set program
		$this->_program = $config['program'];

		// set mq controller
		$this->_controller_path = $config['controller_path'];
	}
	
	public function enqueue($controller, array $data) {
		// save request to file with id
		$id = microtime(true) . '_' . uniqid('mq_', true);
		
		$json = json_encode(array(
			'controller' => $controller,
			'data' => $data,
		));
		
		file_put_contents($this->_save_path . $id, $json);

		// trigger processing
		$command = $this->_program . ' ' . getcwd() . '/index.php' . ' ' . $this->_controller_path;
		$this->_execInBackground($command);

		return $id;
	}

	public function process($controller, $data) {
		// write lock file

		die('fdfd');

		// call controller one after another
		//exec('php mq &');

		// delete lock file
	}

	protected function _execInBackground($cmd) { 
		if (substr(php_uname(), 0, 7) == "Windows") {
			pclose(popen("start /B ". $cmd, "r"));
		} else {
			exec($cmd . " > /dev/null &");   
		}
	} 	
}
	
