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

/**
* This class helps you to debug your application.
*
* Message queues allow you to store data for processing time consuming jobs at a later time (decoupled from the current process).
* Queues can dramatically increase the user experience of a web site by reducing load times.
* All enqueued jobs are processed consecutively by an CLI call to keep the server load low.
*
* Type   | Keyname                | Default    | Description                                                              
* -----  | ---------              | ---------  | ------------                                                             
* string | `mq.cli_path`          | `php`     | The path to the php interpreter. Just use php if the cli is systemwide callable.
* string | `mq.save_path`         | `APP_PATH . 'temp/messagequeue/'` | The path where the job files are saved.
*
* Examples
* ---------
*
* Decouple time consuiming processes from the current controller `foobar.php`
* ~~~{.php}
* // ... Controller code
* 
* $this->messagequeue->set('mq/foobar', 1);
* $this->messagequeue->set('mq/foobar', 2);
* $this->messagequeue->set('mq/foobar', array('foo', 'bar'));
* 
* // ... Controller code
* ~~~
* 
* The job controller named `mq_foobar.php`
* ~~~{.php}
* // ... Controller code
* 
* // Set the handler to plain because we don't want to output anything
* $this->view->setHandler('plain');
*
* // Important line: Start the job worker if necessary
* // You have to insert this line into all your job controllers
* if ($this->messagequeue->process()) return;
*
* // get the job data you passed with $this->messagequeue->set()
* $job = $this->messagequeue->get($this->input->get('id'));
* $data = $job['data'];
* 
* 
* // This is your time consuming code
* sleep(3);
* $this->log->set(date('H:i:s'), $data);
* 
* // ... Controller code
* ~~~
*/
class MessageQueue {
	/**
	 * The path where the job files are saved.
	 * @var string $_save_path
	 */
	protected $_save_path;

	/**
	 * The path to the php interpreter. Just use php if the PHP CLI is systemwide callable.
	 * @var string $_cli_path
	 */
	protected $_cli_path;

	/**
	 * The path to the lock file which prevents multiple workers running.
	 * @var string $_lockfile
	 */
	protected $_lockfile;

	/**
	 * Initializes the MessageQueue class.
	 * @param	array	$config	All config parameters.
	 */
	public function __construct($config) {
		// set save path
		if (!is_dir($config['save_path'])) mkdir($config['save_path']);
		$this->_save_path = $config['save_path'];

		// set cli_path
		$this->_cli_path = $config['cli_path'];

		// set lockfile path
		$this->_lockfile = $this->_save_path . 'mq.lock';
	}
	
	/**
	 * Enqueues a new Job in the message queue.
	 * @param	array	$controller	The path that should be called and that contains the controller logic.
	 * @param	mixed	$data	That data that should be passed to the controller and is readable by getJob().
	 * @return	string	Return the id of the job.
	 */
	public function set($controller, $data) {
		// save request to file with id
		$id = microtime(true) . '_' . uniqid('_', true) . '.mq';
		
		$item = array(
			'id' => $id,
			'controller' => $controller,
			'data' => $data,
		);

		$id_file = $this->_save_path . $id;
		file_put_contents($id_file, json_encode($item));

		// trigger processing if worker is not running at the moment
		if (!is_file($this->_lockfile)) {
			// write lock file (we have to do this here and NOT in startworker because we could otherwise have more than one running worker)
			file_put_contents($this->_lockfile, '');

			$command = $this->_cli_path . ' ' . getcwd() . '/index.php' . ' ' . $controller . ' _morrow_startworker=true';
			$this->_execInBackground($command);
		}

		return $id;
	}

	/**
	 * Retrieves the job data for an id.
	 * @param	string	$id	The id of the job.
	 * @return	array	All data for the requested job with the two keys `id`, `controller` and `data`.
	 */
	public function get($id) {
		if (!isset($id)) throw new \Exception('ID for job is missing.');
		$id_file = $this->_save_path . $id;
		if (!is_file($id_file)) throw new \Exception('Job file for ID is missing.');

		// get the requested ID
		$item = json_decode(file_get_contents($id_file), true);

		return $item;
	}

	/**
	 * Starts the worker which processes all enqueued jobs.
	 * @return	boolean	Returns `true` if the would could have been started and `false` if not.
	 */
	public function process() {
		// just start worker if startworker was sent by $_GET
		if (!isset($_GET['_morrow_startworker'])) return false;

		// get all files to process
		$files = glob($this->_save_path . '*.mq');

		// there is no entry to process anymore
		while (count($files) > 0) {
			sort($files);

			// extract data for the job to process
			$item = json_decode(file_get_contents($files[0]), true);

			$command = $this->_cli_path . ' ' . getcwd() . '/index.php' . ' ' . $item['controller'] . ' id=' . $item['id'];
			exec($command, $output, $return_var);

			unlink($files[0]);
	
			// get all files to process
			$files = glob($this->_save_path . '*.mq');
		}

		unlink($this->_lockfile);

		return true;
	}

	/**
	 * Executes a shell command in the background (decoupled from the current process) .
	 * @param	string	$cmd	The command to execute.
	 * @return	array	All data for the requested job with the two keys `id`, `controller` and `data`.
	 */
	protected function _execInBackground($cmd) { 
		if (substr(php_uname(), 0, 7) == "Windows") {
			pclose(popen("start /B ". $cmd, "r"));
		} else {
			exec($cmd . " > /dev/null &");
		}
	} 	
}
	
