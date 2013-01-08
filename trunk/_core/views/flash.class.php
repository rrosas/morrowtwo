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


namespace Morrow\Core\Views;

class Flash extends AbstractView {
	public $mimetype	= 'text/plain';
	public $charset		= 'utf-8';
	
	public $numeric_prefix = 'entry';

	public function getOutput($content, $handle) {
		if (isset($content['content'])) $this -> _outputVars('', $content['content'], $handle);
		fwrite($handle, '&eof=1');
		return $handle;
	}
	
	protected function _outputVars($var, $input, $handle) {
		if (is_array($input)) {
			if (count($input) === 0) return '';
			foreach($input as $key=>$value) {
				if (is_numeric($key)) $key = $this->numeric_prefix.$key;
				if (!empty($var)) $var_to = $var.'_'.$key;
				else $var_to = $key;

				if (!isset($output)) $output = '';
				$this -> _outputVars($var_to, $value, $handle);
			}
		} else {
			// Ausgabe des Inhalts
			if ($input === true) $input = 'true';
			elseif ($input === false) $input = 'false';
			elseif ($input === null) $input = 'null';
			else {
				$input = trim(stripslashes($input));
			}

			$body = $input;
			fwrite($handle, '&'.$var.'='.rawurlencode($body));
		}
	}
}
