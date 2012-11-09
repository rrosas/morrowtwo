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








class Benchmark
	{
	private $section;	// the name of the actual measured section
	private $active;	// is measuring active at the moment
	private $realtime;	// start value of measuring in real time
	private $proctime;	// start value of measuring in proc time
	private $time_all;	// overall time of measuring
	private $data;		// the data of measuring

	public function start($section = 'Unknown section')
		{
		if ($this->active) $this->stop();

		// set start value for system + user time
		if (function_exists('getrusage'))
			{
			$use = getrusage();
			$user   = sprintf('%6d.%06d', $use['ru_utime.tv_sec'], $use['ru_utime.tv_usec']);
			$system = sprintf('%6d.%06d', $use['ru_stime.tv_sec'], $use['ru_stime.tv_usec']);
			$this->proctime = $user+$system;
			}

		// set start value for real time
		$this->realtime		= microtime(true);

		$this->section		= $section;
		$this->active		= true;
		}

	public function stop()
		{
		$temp['section'] = $this->section;

		// set start value for system + user time
		if (function_exists('getrusage'))
			{
			$use = getrusage();
			$user   = sprintf('%6d.%06d', $use['ru_utime.tv_sec'], $use['ru_utime.tv_usec']);
			$system = sprintf('%6d.%06d', $use['ru_stime.tv_sec'], $use['ru_stime.tv_usec']);
			$proctime_end = $user+$system;
			$temp['proctime'] = $proctime_end - $this->proctime;
			}
		else $temp['proctime'] = 'n/a';

		$realtime_end = microtime(true);
		$temp['realtime'] = $realtime_end - $this->realtime;

		if (function_exists('memory_get_usage')) $temp['mem'] = memory_get_usage();
		else $temp['mem'] = 'n/a';

		$this->data[] = $temp;
		$this->active	= false;
		}

	public function get()
		{
		if ($this->active) $this->stop();

		foreach ($this->data as $key=>$value)
			{
			$row =& $this->data[$key];
			$row['realtime_ms'] = $row['realtime']*1000;
			if (is_numeric($row['proctime']))
				{
				$row['proctime_ms'] = $row['proctime']*1000;
				}
			else $row['proctime_ms'] = 'n/a';
			}

		$returner = $this->data;
		unset($this->data);
		
		return $returner;
		}
	}
