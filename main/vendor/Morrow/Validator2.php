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


/*
age
required
required_if 
numeric
max
min
regex
email (inkl checkdnsrr)
url (inkl checkdnsrr)
array
date
before
after
date_format
different (field)
in (foo, bar) // for selects
ip

*/
class Validator2 {
	// Returns false if only one field is not valid
	public function validate($input, $rules) {
		// first 
	}

	// Returns an array with all valid fields
	public function filter($input, $rules) {
		return $data;
	}

	protected function _validator_image($value, $types_user) {
		if (!is_string($value)) return false;
		if (!is_string($type)) throw new \Exception(__METHOD__ . ': $type has to be of type string.');


		$types = array(
			'jpg'	=> IMAGETYPE_JPEG,
			'png'	=> IMAGETYPE_PNG,
			'gif'	=> IMAGETYPE_GIF,
		);
		$types_user = array_map(function($data){ return strtolower(trim($data)); }, array_slice(func_get_args(), 1));
		$types		= array_intersect_key($types, array_flip($types_user));

		try {
			$imagesize = getimagesize($value['tmp_name']);
			if (in_array($imagesize[2], $types)) {
				return true;
			}
		} catch (\Exception $e) {
			return false;
		}
		return false;
	}

	protected function _validator_width($value, $width) {
		if (!is_string($value)) return false;
		if (!is_string($width)) throw new \Exception(__METHOD__ . ': $width has to be of type string.');

		try {
			$imagesize = getimagesize($value['tmp_name']);
			if ($imagesize[0] == $width) return true;
			return false;
		} catch (\Exception $e) {
			return false;
		}
		return false;
	}

	protected function _validator_height($value, $height) {
		if (!is_string($value)) return false;
		if (!is_string($height)) throw new \Exception(__METHOD__ . ': $height has to be of type string.');

		try {
			$imagesize = getimagesize($value['tmp_name']);
			if ($imagesize[1] == $height) return true;
			return false;
		} catch (\Exception $e) {
			return false;
		}
		return false;
	}

	protected function _validator_email($value) {
		if (!is_string($value)) return false;
		
		// syntax check
		$localpart = "[a-z0-9!#$%&'*+-/\=?^_`{|}~]"; // RFC 2822
		$domainpart = "[\.a-z0-9-]";
		if (!preg_match("=^$localpart+(\.$localpart+)*@($domainpart+\.$domainpart+)$=i", $value, $match)) {
			return false;
		}
		
		// dnscheck
		$host = $match[2];
		if (!(checkdnsrr($host, 'MX') || checkdnsrr($host, 'A'))) {
			return false;
		}
		
		return true;
	}

	protected function _validator_url($value, $schemes) {
		if (!is_string($value)) return false;
		
		$schemes_user = array_map(function($data){ return strtolower(trim($data)); }, array_slice(func_get_args(), 1));
		if (preg_match('~^('.implode('|', $schemes_user).')~', $value)) return true;

		return false;
	}






		
	protected function _validator_date($value) {
		$return = false;
		
		if (is_scalar($value)) {
			$value = strtotime($value, time());
				
			if ($value !== false && $value !== -1) {
				$return = checkdate(date('m', $value), date('d', $value), date('Y', $value));
			}
		}
		
		return $return;
	}
	
	protected function _validator_age($birthday, $min, $max) {
		$birthday = strtotime($birthday);
		if (!$birthday) return false;

		$age = floor((date("Ymd") - date('Ymd', $birthday)) / 10000);
		if ($age < $min) return false;
		if ($age > $max) return false;
		
		return true;
	}
	



	
	protected function _validator_integer($value) {
		if (!preg_match('=^[0-9]*$=', $value)) { 
			return false;
		}
		return true;
	}

	protected function _validator_numeric($value) {
		$return = (is_numeric($value)) ? true : false;
		
		return $return;
	}
}
