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
* The class for accessing the user session with methods for getting, setting and deleting content and is always initialized by Morrow.
*
* The class is not interested in the way sessioning is handled by PHP and uses whatever is the default setting (usually PHP saves to the file system).
* It can be extended to take care of different session handling methods. Morrow provides a class for saving sessions in the database: Dbsession.
* This can also be used as an example for defining one's own session handling.
* 
* Dot Syntax
* ----------
* 
* This class works with the extended dot syntax. So if you use keys like `foo.bar` and `foo.bar2` as identifiers in your config, you can call `$this->session->get("foo")` to receive an array with the keys `bar` and `bar2`. 
* 
* Examples
* ---------
*
* ~~~{.php}
* // ... Controller code
*  
* // counting per user page visits for the whole site
* 
* $visits = $this->session->get('visits');
* if ($visits === null) $visits = 0;
*  
* $this->session->set('visits', ++$visits);
* 
* // ... Controller code
* ~~~
*/
class Session {
	/**
	 * The array with parsed data that holds the session data.
	 * Has to be static because it represents the superglobal `$_SESSION`.
	 * @var array $_data
	 */
	protected static $_data = array();

	/**
	 * Defines the key we want to use to store the data in `$_SESSION`.
	 * @var string $section
	 */
	protected $section = "main";

	/**
	 * Initializes the class. Usually you don't have to initialize this class yourself.
	 * 
	 * @param array $config	Config parameters as an associative array that are passed to session_set_cookie_params(). Use the keys `lifetime`, `path`, `domain`, `secure` and `httponly` that are described in the documentation to session_set_cookie_params().
	 * @param array $input_get	Pass the input data eg. which could contain the session id, eg. $_GET. That is used to allow a session id to be passed via GET.
	 */
	public function __construct($config, $input_get) {
		if (ini_get('session.auto_start') != true) {
			
			// set cookie params
			session_set_cookie_params(
				$config['lifetime'],
				$config['path'],
				$config['domain'],
				$config['secure'],
				$config['httponly']
			);
			
			// _GET has been removed, if session id comes from input, get it now
			$session_id = isset($input_get[session_name()]) ? $input_get[session_name()] : null;
			if (!is_null($session_id) && !empty($session_id)) {
				session_id($session_id);
			}
			
			// start session
			session_start();
		}
		
		// it should not be possible to inject a session
		$this->_preventSessionFixation();
		
		// get session data
		self::$_data = $_SESSION;
	}

	/**
	 * Writes the internal data back to `$_SESSION` to let PHP save the data.
	 */
	public function __destruct() {
		$_SESSION = self::$_data;
		session_write_close();
	}

	/**
	 * Retrieves session data saved with a given identifier.
	 * 
	 * @param string $identifier	The identifier you have used on setting the data.
	 * @return mixed 	The requested data.
	 */
	public function get($identifier = null) {
		return Helpers\General::array_dotSyntaxGet(self::$_data, $this->section . ($identifier !== null ? '.' . $identifier : ''));
	}

	/**
	 * Sets session data with a given identifier.
	 * 
	 * @param string $identifier	The identifier you want to store the data with.
	 * @param string $value	The data you want to store.
	 * @return null
	 */
	public function set($identifier, $value) {
		Helpers\General::array_dotSyntaxSet(self::$_data, $this->section . "." . $identifier, $value);
	}

	/**
	 * Deletes session data.
	 * 
	 * @param string $identifier	The identifier you want to delete.
	 * @return null
	 */
	public function delete($identifier = null) {
		if ($this->get($identifier) === null) return;
		Helpers\General::array_dotSyntaxDelete(self::$_data, $this->section . ($identifier !== null ? '.' . $identifier : ''));
	}
	
	/**
	 * Prevents session fixation. It is automatically called if there isn't a session id known for this user and uses the USER AGENT as identifier.
	 * So the session id is regenerated if the USER AGENT changes.
	 * For more information take a look at: http://phpsec.org/projects/guide/4.html
	 * 
	 * @return null
	 */
	protected function _preventSessionFixation() {
		if (!isset($_SERVER['HTTP_USER_AGENT'])) return;
		if (!isset($_SESSION['SERVER_GENERATED_SID']) or $_SESSION['SERVER_GENERATED_SID'] != md5($_SERVER['HTTP_USER_AGENT'])) {
			session_regenerate_id();
			session_unset();
			$_SESSION['SERVER_GENERATED_SID'] = md5($_SERVER['HTTP_USER_AGENT']);
		}
	}
}
