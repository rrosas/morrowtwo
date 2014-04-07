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
minage
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
image
in (foo, bar) // for selects
ip

*/
class Validator2 {
	public function isValid($input, $rules) {

	}

	/*
	Returns an array with all valid fields
	Returns false if only one field is not valid
	*/
	public function filter($input, $rules) {
		return $data;
	}

	protected function _validator_image($value) {
		$img_attr = getimagesize($value['tmp_name']);
		if ($img_attr[2] === IMAGETYPE_JPEG) {
			return true;
		}
		return false;
	}

	protected function _validator_email($var) {
		if (!is_string($var)) return false;
		
		// syntax check
		$localpart = "[a-z0-9!#$%&'*+-/\=?^_`{|}~]"; // RFC 2822
		$domainpart = "[\.a-z0-9-]";
		if (!preg_match("=^$localpart+(\.$localpart+)*@($domainpart+\.$domainpart+)$=i", $var, $match)) {
			return false;
		}
		
		// dnscheck
		$host = $match[2];
		if (!(checkdnsrr($host, 'MX') || checkdnsrr($host, 'A'))) {
			return false;
		}
		
		return true;
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
	
	protected function _validator_age($birthday, $age) {
		$return   = false;
		
		if (self::checkDate($birthday, $error) === true) {
			$age = floor((date("Ymd") - date('Ymd', strtotime($birthday))) / 10000);
			
			if ($age >= $age) return true;
		}
		
		return $return;
	}
	
	protected function _validator_captcha($captcha) {
		$session = Factory::load('session');
		$session_captcha = $session->get('captcha');
		$session->delete('captcha');
		if (strtolower($captcha) != strtolower($session_captcha)) {
			return false;
		}
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
