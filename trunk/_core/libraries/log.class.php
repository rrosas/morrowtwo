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

class Log
	{
	private $logfile;

	public function __construct($args = null)
		{
		// set defaults
		$date = strftime('%Y-%m-%d');
		if (!isset($args['logfile'])) $args['logfile'] = FW_PATH.'_logs/log_'.$date.'.txt';

		$this->logfile = $args['logfile'];
		return true;
		}

	public function set()
		{
		$data['args'] = func_get_args();
		$caller = debug_backtrace();
		$data['caller']= $caller[0];
		$data['formated_time'] = strftime('%Y-%m-%d %H:%M:%S', time());
		$data['formated_microtime'] = sprintf("%'03d", microtime()*1000);

		return $this->_getOutputPlain($data);
		}

	private function _getOutputPlain($data)
		{
		// create output string
		$output = 'date: '.$data['formated_time'].' .'.$data['formated_microtime']."\n";
		$output .= 'call: '.$data['caller']['file'].' (line '.$data['caller']['line'].')'."\n";

		foreach ($data['args'] as $arg)
			{
			$output .= '---'."\n";

			// get dump
			ob_start();
			var_dump($arg);
			$dump = ob_get_contents();
			ob_end_clean();
			
			$output .= $dump;
			}

		$output .= '=============================='."\n";

		$result =  file_put_contents($this->logfile, $output, FILE_APPEND);
		if (is_integer($result)) return true;
		else return false;
		}
	}
