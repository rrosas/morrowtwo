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

class Db {
	public static $_pdo = array();
	
	protected $scheme;
	protected $table;
	protected $id;
	protected $pdo;

	public function __construct($scheme = null, \PDO $pdo = null) {
		if(!$scheme) return;
		
		self::$_pdo[$scheme] = $pdo;
		stream_register_wrapper($scheme, __CLASS__);
	}

	function stream_open($path, $mode, $options, &$opath) {
		$path_array		= parse_url($path);
		$this->scheme	= $path_array['scheme'];
		$this->table	= $path_array['host'];
		$this->id		= ltrim($path_array['path'], '/');
		$this->pdo		= self::$_pdo[$this->scheme];
		
		var_dump($this);

		//$this->config = Factory::load('Config');
		//$this->_db = Factory::load('Db', $this->config->get($dbName));

		/*try{
			$this->_pdo = new PDO("mysql:host={$url['host']};dbname={$url['path']}", $url['user'], isset($url['pass'])? $url['pass'] : '', array());
		} catch(PDOException $e){ return false; }
		/*switch ($mode){
			case 'w' :
				$this->_ps = $this->_pdo->prepare('INSERT INTO data VALUES(null, ?, NOW())');
				break;
			case 'r' :
				$this->_ps = $this->_pdo->prepare('SELECT id, data FROM data WHERE id > ? LIMIT 1');
				break;
			default  : return false;
		}*/
		return $this;
	}

	function stream_read()
	{
		$this->_ps->execute(array($this->_rowId));
		if($this->_ps->rowCount() == 0) return false;
		$this->_ps->bindcolumn(1, $this->_rowId);
		$this->_ps->bindcolumn(2, $ret);
		$this->_ps->fetch();
		return $ret;
	}

	function stream_write($data)
	{
		$this->_ps->execute(array($data));
		return strlen($data);
	}

	function stream_tell()
	{
		return $this->_rowId;
	}

	function stream_eof()
	{
		$this->_ps->execute(array($this->_rowId));
		return (bool) $this->_ps->rowCount();
	}

	function stream_seek($offset, $step) {}
}