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


namespace Morrow\Libraries;

class Time
	{
	public $time = 0;
	public $types = 0;
	
	public function __construct($var_timestamp = null)
		{
		if (!is_null($var_timestamp))
			$this->time = $this->_convertToTimestamp($var_timestamp);
		else
			$this->time = time();
		
		$this->types = array(
			'mysql_date' => 'Y-m-d',
			'mysql_datetime' => 'Y-m-d H:i:s',
			'date' => 'Y-m-d',
			'datetime' => 'Y-m-d H:i:s',
			'iso8601' => 'c',
			'rfc2822' => 'r',
			'iso' => 'c',
			'rfc' => 'r',
		);
		}
	
	public static function create($var_timestamp = null)
		{
		return new self($var_timestamp);
		}
	
	public function set($var_timestamp)
		{
		$timestamp = $this->_convertToTimestamp($var_timestamp);
		if (!$timestamp) return false;
		
		$this->time = $timestamp;
		return true;
		}

	public function get($type = 'timestamp', $alter = null)
		{
		$type = strtolower($type);
		
		// return timestamp
		if ($type == 'timestamp') return $this->time;

		// check type
		$types = array_keys($this->types);
		if (!in_array( $type, $types )) throw new Exception('type "'.$type.'" no valid. Valid types are "'.implode('", "', $types).'".');
		
		$timestamp = $this->time;

		// alter date
		if (!is_null($alter)) $timestamp = strtotime($alter, $timestamp);

		return date($this->types[$type], $timestamp);
		}
	
	public function strftime($format)
		{
		return strftime($format, $this->time);
		}
	
	public function date($format)
		{
		return date($format, $this->time);
		}

	protected function _convertToTimestamp($var)
		{
		if (is_numeric($var)) return (int)$var;
		
		$var = strtotime($var);
		if (!$var) return false;
		return $var;
		}
	
	public function time_since($var_since, $skip_empty_values = true)
		{
		$since = $this->_convertToTimestamp($var_since);
		if (!$since) return false;
		$since = $this->time - $since;
		
		$chunks = array(
			array(60 * 60 * 24 * 365 , 'year'),
			array(60 * 60 * 24 * 30 , 'month'),
			array(60 * 60 * 24 * 7, 'week'),
			array(60 * 60 * 24 , 'day'),
			array(60 * 60 , 'hour'),
			array(60 , 'minute'),
			array(1 , 'second')
		);

		$fragment = array();
		foreach ($chunks as $i=>$value)
			{
			$seconds = $value[0];
			$key = $value[1];
			$count = floor($since / $seconds);
			
			// skip empty values
			if ($skip_empty_values && count($fragment) == 0 && $count == 0) continue;
			
			$fragment[$key] = $count;
			
			// substract for further calculations
			$since -= $count*$seconds;
			}

		return $fragment;
		}
	}
