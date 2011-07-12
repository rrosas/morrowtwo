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




class ViewJson extends ViewAbstract
	{
	public $mimetype	= 'application/json';
	public $charset		= 'utf-8';

	public function getOutput($content, $handle)
		{
		if (function_exists('json_encode'))
			fwrite($handle, json_encode($content['content']));
		else
			fwrite($handle, $this -> _outputJSON($content['content']));
		return $handle;
		}

	private function _outputJSON($a)
		{
		if (is_null($a)) return 'null';
		if ($a === false) return 'false';
		if ($a === true) return 'true';
		if (is_scalar($a))
			{
			$a = addslashes($a);
			$a = str_replace("\n", '\n', $a);
			$a = str_replace("\r", '\r', $a);
			$a = preg_replace('{(</)(script)}i', "$1'+'$2", $a);
			
			// escaping a forward slash is not required. But json_encode does it so we too
			$a = str_replace('/', '\/', $a);
			
			return "\"$a\"";
			}
        $isList = true;
        for ($i=0, reset($a); $i<count($a); $i++, next($a))
            if (key($a) !== $i) { $isList = false; break; }
        $result = array();

        if ($isList)
			{
            foreach ($a as $v) $result[] = $this->_outputJSON($v);
			return '[' . join(',', $result) . ']';
			}
		else
			{
            foreach ($a as $k=>$v) $result[] = $this->_outputJSON($k) . ':' . $this->_outputJSON($v);
            return '{' . join(',', $result) . '}';
			}
		}
	}
