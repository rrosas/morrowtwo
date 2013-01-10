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

class Db extends PDO
	{
	protected $connected = false;	// are we already connected?
	protected $config; // the database configuration
	protected $cache = array(); // column cache for *safe() functions
	
	public function __construct($config)
		{
		$this -> config = $config;		
		}

	public function connect()
		{
		if (!$this->connected)
			{
			if($this->config['host']{0} == '/')
				{
				$connector = $this->config['driver'].':unix_socket='.$this->config['host'].';dbname='.$this->config['db'];
				}
			else
				{
				$connector = $this->config['driver'].':host='.$this->config['host'].';dbname='.$this->config['db'];
				}
			
			// sqlite
			if(isset($this->config['file'])){
				$connector = $this->config['driver'].':'.$this->config['file'].'';
			}
			
			parent::__construct($connector, $this->config['user'], $this->config['pass']);

			$this -> setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL); // leave column names as returned by the database driver
			$this -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // on errors we want to get \Exceptions

			// set encoding
			if(isset($this->config['encoding'])){
				parent::exec('SET NAMES '.$this->config['encoding']);
			}

			$this->connected = true;
			}
		}
		
	public function result($query, $params = NULL)
		{
		if (is_scalar($params)) $params = array($params);
				
		// search for access keys
		$accesskey = NULL;
		$found = preg_match('=SELECT.+>([a-z_]+).+FROM=is', $query, $match);
		if ($found === 1)
			{
			$accesskey = $match[1];
			$query = str_replace('>'.$accesskey, $accesskey, $query);
			}
		
		// do query
		$this->connect();
		$sth = $this->prepare($query);
		$returner['SUCCESS'] = $sth->execute($params);
		$returner['RESULT'] = $sth->fetchAll(PDO::FETCH_ASSOC);
		$returner['NUM_ROWS'] = count($returner['RESULT']);
		
		// if an access key was provided rearrange array
		if (!is_null($accesskey))
			{
			$newreturner = array();
			foreach ($returner['RESULT'] as $row)
				{
				$newreturner[$row[$accesskey]] = $row;
				}
			$returner['RESULT'] = $newreturner;
			}

		return $returner;
		}

	public function result_calc_found_rows($query, $params = NULL)
		{
		if (is_scalar($params)) $params = array($params);

		// because of two queries we should use transactions if available
		$this->beginTransaction();
		
		// rewrite query
		$query = preg_replace('=SELECT=is', 'SELECT SQL_CALC_FOUND_ROWS', $query, 1);
		$returner = $this->result($query, $params);

		// get found rows
		$sql = $this->query('SELECT FOUND_ROWS() AS FOUND_ROWS')->fetch( PDO::FETCH_ASSOC );
		$returner['FOUND_ROWS'] = $sql['FOUND_ROWS'];
	
		$this->commit();
		
		return $returner;
		}
		
	public function insert($table,$array,$insertid = false)
		{
		$array = $this->_createInsertAndReplaceValues($array);
		extract($array);
		
		$query = "INSERT INTO $table ($keys) VALUES ($values)";

		$this->connect();
		$sth = $this->prepare($query);

		$returner['SUCCESS'] = $sth->execute($binds);
		if ($insertid == true) $returner['INSERT_ID'] = $this->lastInsertId();
		return $returner;
		}

	public function insertSafe($table,$array,$insertid = false)
		{
		$array = $this->_safe($table, $array);
		return $this -> Insert($table,$array,$insertid);
		}
		
	protected function _createUpdateValues($array)
		{
		$values = array();
		$tokens = array();
		
		// divide normal values from function calls
		foreach ($array as $key=>$value)
			{
			if (is_array($value))
				{
				$tokens[] = '`'.$key.'`='.$value['FUNC'];
				}
			else
				{
				$tokens[] = '`'.$key.'`=?';
				$values[] = $value;
				}
			}
		
		$tokens = implode(', ', $tokens);	

		$returner = compact('tokens', 'values');
		return $returner;
		}

	public function update($table,$array,$where='',$affected_rows=false, $where_tokens=array() )
		{
		$array = $this->_createUpdateValues($array);
		extract($array);
		
		// add tokens of where clause
		if (is_scalar($where_tokens)) $where_tokens = array($where_tokens);
		foreach ($where_tokens as $value)
			{
			$values[] = $value;
			}
		
		$query = "UPDATE $table SET $tokens $where";

		$this->connect();
		$sth = $this->prepare($query);
		
		$returner['SUCCESS'] = $sth->execute( $values );
		if ($affected_rows) $returner['AFFECTED_ROWS'] = $sth->rowCount();
		return $returner;
		}

	public function updateSafe($table,$array,$where='',$affected_rows=false, $where_tokens=array() )
		{
		$array = $this->_safe($table, $array);
		return $this -> Update($table,$array,$where,$affected_rows, $where_tokens);
		}
	
	protected function _createInsertAndReplaceValues($array)
		{
		$keys = array();
		$values = array();
		$binds = array();
		
		foreach ($array as $value)
			{
			if (is_array($value))
				{
				$values[] = $value['FUNC'];
				}
			else
				{
				$values[] = '?';
				$binds[] = $value;
				}
			}

		$keys = implode(',', array_keys($array));
		$values = implode(',', $values);
		
		$returner = compact('keys', 'values', 'binds');
		return $returner;
		}

	public function replace($table,$array,$insertid = false)
		{
		$array = $this->_createInsertAndReplaceValues($array);
		extract($array);
		
		$query = "REPLACE INTO $table ($keys) VALUES ($values)";

		$this->connect();
		$sth = $this->prepare($query);

		$returner['SUCCESS'] = $sth->execute($binds);
		if ($insertid == true) $returner['INSERT_ID'] = $this->lastInsertId();
		return $returner;
		}

	public function replaceSafe($table,$array,$insertid = false)
		{
		$array = $this->_safe($table, $array);
		return $this -> Replace($table,$array,$insertid);
		}
		
	public function delete($table, $where, $affected_rows=false, $where_tokens=array())
		{
		// add tokens of where clause
		$values = array();
		if (is_scalar($where_tokens)) $where_tokens = array($where_tokens);
		foreach ($where_tokens as $value)
			{
			$values[] = $value;
			}
		
		$query = "DELETE FROM $table $where";

		$this->connect();
		$sth = $this->prepare($query);
		
		$returner['SUCCESS'] = $sth->execute( $values );
		if ($affected_rows) $returner['AFFECTED_ROWS'] = $sth->rowCount();
		return $returner;
		}
	
	public function exec($query)
		{
		$this->connect();
		$returner['AFFECTED_ROWS'] = parent::exec($query);
		return $returner;
		}

	public function query($query)
		{
		$this->connect();
		$params = func_get_args();
		return call_user_func_array( array($this, 'parent::query'), $params);
		}

	public function	beginTransaction()
		{
		$this->connect();
		$status = parent::beginTransaction();
		return $status;
		}
		
	protected function _safe($table, $array)
		{
		if (!isset($this->cache[$table]))
			{
			$this->connect();
			$query = 'SHOW COLUMNS FROM '.$table;
			$sth = $this->prepare($query);
			$sth->execute();
			$result = $sth->fetchAll(PDO::FETCH_ASSOC);
			
			foreach ($result as $row) $columns[] = $row['Field'];
			$this->cache[$table] = $columns;
			}
		
		// remove all not existent keys
		foreach ($array as $key=>$value)
			if (!in_array($key, $this->cache[$table])) unset ($array[$key]);

		return $array;
		}
	}
