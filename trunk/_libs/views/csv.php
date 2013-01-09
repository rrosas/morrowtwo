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

class Csv extends AbstractView {
	public $mimetype	= 'text/csv';
	public $charset		= 'utf-8';

	public $separator	= ';';
	public $linebreaks	= "\n";
	public $delimiter 	= '"';
	public $table_header= true;
	
	public function getOutput($content, $handle) {
		$this->_outputCSV($content['content'], $handle);
		return $handle;
	}

	protected function _outputCSV($input, $handle) {
		foreach($input as $nr=>$row) {
			// use first row for headlines
			if ($nr == 0 && $this->table_header === true) {
				fwrite($handle, $this -> _createRow( array_keys($row) ));
			}

			fwrite($handle, $this -> _createRow($row));
		}
	}

	protected function _createRow($input) {
		foreach ($input as $key=>$value) {
			$temp = str_replace('"','""',$value);
			$temp = preg_replace("=(\r\n|\r|\n)=","\n",$temp);
			$input[$key] = $this->delimiter.$temp.$this->delimiter;
		}
		$output = implode($this->separator, $input).$this->linebreaks;
		return $output;
	}
}
