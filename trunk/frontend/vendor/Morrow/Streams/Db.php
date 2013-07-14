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

/*
CREATE TABLE IF NOT EXISTS `files` (
  `id` char(255) NOT NULL,
  `data` longblob NOT NULL,
  `ctime` datetime NOT NULL,
  `mtime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Used for Db stream wrapper';
*/

namespace Morrow\Streams;

class Db {
	public static $config = array();
	
	protected $scheme;
	protected $table;
	protected $id;
	protected $db;

	public function __construct($scheme = null, $table = null, \Morrow\Db $db = null) {
		if(!$scheme) return;
		
		self::$config[$scheme] = array(
			'db'	=> $db,
			'table'	=> $table,
		);
		stream_register_wrapper($scheme, __CLASS__);
	}

	function stream_open($path, $mode, $options, &$opath) {
		$path_array		= parse_url($path);
		$this->scheme	= $path_array['scheme'];
		$this->id		= ltrim($path_array['path'], '/');
		$this->db		= self::$config[$this->scheme]['db'];
		$this->table	= self::$config[$this->scheme]['table'];
		
		//var_dump($this);
		//die();

		// handle the different modes
		$sql = $this->db->result("SELECT * FROM ". $this->table ." WHERE id = ? LIMIT 1", $this->id);
		if ($sql['NUM_ROWS'] === 0) return false;
		return true;
	}

	function stream_stat() {
		// implement atime (dont do that, not performant)
		// implement ctime
		// implement mtime
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