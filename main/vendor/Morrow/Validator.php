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

class Validator {
	public static function checkUsername($value, &$error) {
		// check the format of the username
		if (!preg_match("=^[a-z0-9_]{5,20}$=i", $value)) {
			$error = Factory::load('Language')->_('Invalid Username. Allowed are only a-z, 0-9, _. Additionally the length must be between 5 and 20 characters.');
			return false;
		}
		return true;
	}

	public static function checkJpegFile($value, &$error) {
		$img_attr = getimagesize($value['tmp_name']);
		if ($img_attr[2] === IMAGETYPE_JPEG) {
			return true;
		}
		$error = Factory::load('Language')->_('Image is not a valid JPEG.');
		return false;
	}

	public static function checkEmail($var, &$error) {
		if (!is_string($var)) return null;
		
		// syntax check
		$localpart = "[a-z0-9!#$%&'*+-/\=?^_`{|}~]"; // RFC 2822
		$domainpart = "[\.a-z0-9-]";
		if (!preg_match("=^$localpart+(\.$localpart+)*@($domainpart+\.$domainpart+)$=i", $var, $match)) {
			$error = Factory::load('Language')->_('Email address is not valid.');
			return false;
		}
		
		// dnscheck
		$host = $match[2];
		if (!(checkdnsrr($host, 'MX') || checkdnsrr($host, 'A'))) {
			$error = Factory::load('Language')->_('Email address is not valid.');
			return false;
		}
		
		return $var;
	}
		
	public static function checkPassword($value, &$error, $compare_value = null) {
		if (strlen($value) > 0 && strlen($value) < 5) {
			$error = Factory::load('Language')->_('Passwords must be at least 5 characters long.');
			return false;
		}
		if ($compare_value !== null && $value != $compare_value) {
			$error = Factory::load('Language')->_('Passwords are not identical.');
			return false;
		}
		return true;
	}

	public static function checkCurrency($value, &$error, $compare_value = null, $locale = array()) {
		$sep = ".";
		if (isset($locale['currency']['separator'])) {
			$sep = $locale['currency']['separator'];
		}
		if (!preg_match("=^[0-9]{1,}({$sep}[0-9]{0,4})?$=", $value)) { 
			$error = Factory::load('Language')->_('Price not valid.');
			return false;
		}
		return true;
	}

	public static function checkValidDate($value, &$error) {
		if (!preg_match('/([0-9]{0,4})-([0-9]{0,2})-([0-9]{0,2})/', $value, $matches)) {
			$error = Factory::load('Language')->_('The date is not valid.');
			return false;
		}
		$y = $matches[1];
		$m = $matches[2];
		$d = $matches[3];

		if (empty($y) || empty($d) || empty($m)) {
			$error = Factory::load('Language')->_('The date is not valid.');
			return false;
		}

		if (!checkdate($m, $d, $y)) {
			$error = Factory::load('Language')->_('The date is not valid.');
			return false;
		}
		return true;
	}

	public static function checkDate($value, &$error) {
		$return = false;
		
		if (is_scalar($value)) {
			$value = strtotime($value, time());
				
			if ($value !== false && $value !== -1) {
				$return = checkdate(date('m', $value), date('d', $value), date('Y', $value));
			}
		}
		
		if ($return != true) {
			$error = Factory::load('Language')->_('The date is not valid.');
		}
		
		return $return;
	}
	
	public static function check18($birthday, &$error) {
		$return   = false;
		
		if (self::checkDate($birthday, $error) === true) {
			$age = floor((date("Ymd") - date('Ymd', strtotime($birthday))) / 10000);
			
			if ($age >= 18) return true;
		}
		
		$error = Factory::load('Language')->_('You must be at least 18 years old.');
		return $return;
	}
	
	public static function checkGermanZip($value, &$error) {
		if (!preg_match("=^[0-9]{5,5}*$=", $value)) {
			$error = Factory::load('Language')->_('Zip is not valid.');
			return false;
		}
		return true;
	}
	
	public static function checkCaptcha($captcha, &$error) {
		$session = Factory::load('session');
		$session_captcha = $session->get('captcha');
		$session->delete('captcha');
		if (strtolower($captcha) != strtolower($session_captcha)) {
			$error = Factory::load('Language')->_('Captcha does not match.');
			return false;
		}
		return true;
	}
	
	public static function checkInteger($value, &$error) {
		if (!preg_match('=^[0-9]*$=', $value)) { 
			$error = Factory::load('Language')->_('Not a number.');
			return false;
		}
		return true;
	}

	public static function checkNumeric($value, &$error) {
		$return = (is_numeric($value)) ? true : false;
		
		if ($return != true) {
			$error = Factory::load('Language')->_('Not a number.');
		}
		
		return $return;
	}
	
	public static function checkEAN($value, &$error) {
		$return = false;
		
		if (is_string($value)) {
			$return = ($value[12] == (((ceil(((($value[1] + $value[3] + $value[5] + $value[7] + $value[9] + $value[11]) * 3) + ($value[0] + $value[2] + $value[4] + $value[6] + $value[8] + $value[10]))/10))*10) - ((($value[1] + $value[3] + $value[5] + $value[7] + $value[9] + $value[11]) * 3) + ($value[0] + $value[2] + $value[4] + $value[6] + $value[8] + $value[10]))));
		}
		
		if ($return != true) {
			$error = Factory::load('Language')->_('EAN Code not valid.');
		}
		
		return $return;
	}
	
	public static function checkUPC($value, &$error) {
		$return = false;
		
		if (is_string($value)) {
			$return = ($value[11] == (((ceil(((($value[0] + $value[2] + $value[4] + $value[6] + $value[8] + $value[10]) * 3) + ($value[1] + $value[3] + $value[5] + $value[7] + $value[9]))/10))*10) - ((($value[0] + $value[2] + $value[4] + $value[6] + $value[8] + $value[10]) * 3) + ($value[1] + $value[3] + $value[5] + $value[7] + $value[9]))));
		}
		
		if ($return != true) {
			$error = Factory::load('Language')->_('UPC Code not valid.');
		}
		
		return $return;
	}
}
