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

class Formfilter {
	public static function outDate($values) {
		$y = $values['_Year'];
		$m = $values['_Month'];
		$d = $values['_Day'];
		$h = $values['_Hour'];
		$min = $values['_Min'];
		$sec = $values['_Sec'];
		if (empty($y) && empty($m) && empty($d)) return '';
		$out = sprintf("%s-%s-%s %s:%s:%s", $y, $m, $d, $h, $min, $sec);
		return $out;
	}
	
	public static function inDate($value) {
		if (is_array($value)) return $value;
		$values = array();
		if (preg_match("/([0-9]{4})-([0-9]{2})-([0-9]{2}) ?([0-9]{2})?:?([0-9]{2})?:?([0-9]{2})?/", $value, $matches)) {
			$values['_Year'] = $matches[1];
			$values['_Month'] = $matches[2];
			$values['_Day'] = $matches[3];
			$values['_Hour'] = (isset($matches[4]))?$matches[4]:'00';
			$values['_Min'] = (isset($matches[5]))?$matches[5]:'00';
			$values['_Sec'] = (isset($matches[6]))?$matches[6]:'00';
			return $values;
		}
		return $value;
	}
}
