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
* The DBSession class extends Session and is used in exactly the same way. The difference is that the session data is stored in a database.
* Therefore a database table must be created and the database configuration must be communicated to the DBSession class as in the example below.
* In Order to use the DBSession class, Morrow must be informed of the handler. This is done by setting `session.handler` to `dbsession`; 
* 
* Examples
* ---------
*
* ~~~{.php}
* // ... Config code
*
* $config['session.handler']      = 'dbsession';
*
* $config['session.db.driver']    = 'mysql';
* $config['session.db.host']      = 'localhost';
* $config['session.db.db']        = 'morrow';
* $config['session.db.user']      = 'morrow_user';
* $config['session.db.pass']      = '';
* $config['session.db.table']     = 'sessions';
*  
* // ... Config code
* ~~~
*
* Table Structure
* ---------------
*
* The name of the table is variable and must be set in the configuration as `session.db.table`. The necessary columns are fixed and are:
* 
*    * **session_id:** Primary key, characters (length: 32)
*    * **session_data:** text/characters
*    * **session_expiration:** timestamp/date-time 
* 
* Example for MySql
* -----------------
*
* ~~~{.sql}
* CREATE TABLE `sessions` (
*     `session_id` varchar(32) NOT NULL default '',
*     `session_data` text NOT NULL,
*     `session_expiration` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
*     primary key  (`session_id`)
* ) ENGINE=MEMORY DEFAULT CHARSET=utf8;
* ~~~
*/
class Dbsession extends Session {
	/**
	 * Contains the config object
	 * @var object $config
	 */
	protected $config = null;

	/**
	 * Contains the db object
	 * @var object $db
	 */
	protected $db = null;
	
	/**
	 * The format we use for the database timestamp
	 * @var string $format
	 */
	protected $format = '%Y%m%d%H%M%S';

	/**
	 * Initializes the class.
	 *
	 * You do not have to call this yourself.
	 * 
	 * @param array $input An array which contain all user input (e.g. $_GET or $this->input->get())
	 * @return null
	 */
	public function __construct($input) {
		if (ini_get('session.auto_start') == true) {
			throw new \Exception('You must turn off session.auto_start in your php.ini or use different session handler');
		}

		// setting the session handlers
		$junk = session_set_save_handler(array($this, "on_session_start"), array($this,"on_session_end"), array($this,"on_session_read"), array($this,"on_session_write"), array($this,"on_session_destroy"), array($this,"on_session_gc"));

		$this->config = Factory::load('Config');
		$this->db = Factory::load('Db', $this->config->get('session.db'));

		// important!: call the parent constructor
		parent::__construct($input);
	}

	/**
	 * passed to session_set_save_handler()
	 */
	protected function on_session_start($save_path, $session_name) {
	}

	/**
	 * passed to session_set_save_handler()
	 */
	protected function on_session_end() {
		// Nothing needs to be done in this function
		// since we used persistent connection.
	}

	/**
	 * passed to session_set_save_handler()
	 */
	protected function on_session_read($key) {
		$stmt = "select session_data from " . $this->config->get('session.db.table');
		$stmt .= " where session_id = ? ";
		$stmt .= "and session_expiration > ?";

		$sth = $this->db->Result($stmt, array($key, strftime($this->format))); 
		
		if ($sth['NUM_ROWS'] > 0) {
			return($sth['RESULT'][0]['session_data']);
		} else {
			return '';
		}
	}

	/**
	 * passed to session_set_save_handler()
	 */
	protected function on_session_write($key, $val) {
		$expires = '+' . ini_get('session.cache_expire') . ' Minutes';

		$data = array(
				'session_id' => $key,
				'session_data' => $val,
				'session_expiration' => strftime($this->format, strtotime($expires)),
				);


		$this->db->replace($this->config->get('session.db.table'), $data);
	}

	/**
	 * passed to session_set_save_handler()
	 */
	protected function on_session_destroy($key) {
		$this->db->delete($this->config->get('session.db.table'), "where session_id = ?", false, $key);
	}

	/**
	 * passed to session_set_save_handler()
	 */
	protected function on_session_gc($max_lifetime) {
		$this->db->delete($this->config->get('session.db.table'), "where session_expiration < ?", false, strftime($this->format));
	}
}
