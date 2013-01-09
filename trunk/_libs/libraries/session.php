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


namespace Morrow\Core\Libraries;

class Session {
	static protected $data = array(); // The array with parsed data
	protected $section = "main";
 
 	public function __construct($data){
		if (ini_get('session.auto_start') != true){
			
			// set cookie params
			$config = \Morrow\Core\Factory::load('Libraries\config')->get('session');
			session_set_cookie_params($config['lifetime'], $config['path'], $config['domain'], $config['secure'], $config['httponly']);
			
			// _GET has been removed, if session id comes from input, get it now
			$session_id = isset($data[session_name()]) ? $data[session_name()] : null;
			if(!is_null($session_id) && !empty($session_id)){
				session_id($session_id);
			}
			session_start();
		}
 		$this->prevent_session_fixation();
		self::$data = $_SESSION;
	}

 	public function __destruct(){
		$_SESSION = array();
		$_SESSION = self::$data;
		session_write_close();
	}

	public final function get($identifier = null){
		if($identifier != null) $identifier  = $this->section  . "." . $identifier;
		else $identifier = $this->section;
		return helperArray::dotSyntaxGet(self::$data, $identifier);
	}

	public final function set($identifier, $value){
		$identifier  = $this->section . "." . $identifier;
		helperArray::dotSyntaxSet(self::$data, $identifier, $value);
	}

	public final function delete($identifier = null){
		if($this->get($identifier) !== null){
			$pidentifier = $this->section . "." . $identifier;
			if($identifier == null){
				helperArray::dotSyntaxDelete(self::$data, $this->section);
			}
			else{
				helperArray::dotSyntaxDelete(self::$data, $pidentifier);
			}
		}
	}
	
	// handles session fixation
	public final function prevent_session_fixation() {
		if (!isset($_SERVER['HTTP_USER_AGENT'])) return;
		if (!isset($_SESSION['SERVER_GENERATED_SID']) or $_SESSION['SERVER_GENERATED_SID'] != md5($_SERVER['HTTP_USER_AGENT'])) {
			session_regenerate_id();
			session_unset();
			$_SESSION['SERVER_GENERATED_SID'] =  md5($_SERVER['HTTP_USER_AGENT']);
		}
	}
}
