<?php

class Validator {
	public static function checkUsername($value, &$error) {
		// check the format of the username
		if(!preg_match("=^[a-z0-9_]{5,20}$=i", $value)) {
			$error = Factory::load('language')->_('Invalid Username. Allowed are only a-z, 0-9, _. Additionally the length must be between 5 and 20 characters.');
			return false;
		}
		return true;
	}

	public static function checkJpegFile($value, &$error) {
		$img_attr = getimagesize($value['tmp_name']);
		if($img_attr[2] === IMAGETYPE_JPEG) {
			return true;
		}
		$error = Factory::load('language')->_('Image is not a valid JPEG.');
		return false;
	}

	public static function checkEmail($var, &$error) {
		if (!is_string($var)) return null;
		
		// syntax check
		$localpart = "[a-z0-9!#$%&'*+-/\=?^_`{|}~]"; // RFC 2822
		$domainpart = "[\.a-z0-9-]";
		if(!preg_match("=^$localpart+(\.$localpart+)*@($domainpart+)$=i", $var, $match)) {
			$error = Factory::load('language')->_('Email address is not valid.');
			return false;
		}
		
		// dnscheck
		$host = $match[2];
		if(!(checkdnsrr($host, 'MX') || checkdnsrr($host, 'A'))) {
			$error = Factory::load('language')->_('Email address is not valid.');
			return false;
		}
		
		return $var;
	}
		
	public static function checkPassword($value, &$error, $compare_value=null) {
		if(strlen($value) > 0 && strlen($value) < 5) {
			$error = Factory::load('language')->_('Passwords must be at least 5 characters long.');
			return false;
		}
		if($compare_value !== null && $value != $compare_value) {
			$error = Factory::load('language')->_('Passwords are not identical.');
			return false;
		}
		return true;
	}

	public static function checkCurrency($value, &$error, $compare_value=null, $locale) {
		$sep = ".";
		if(isset($locale['currency']['separator'])){
			$sep = $locale['currency']['separator'];
		}
		if(!preg_match("=^[0-9]{1,}({$sep}[0-9]{0,4})?$=",$value)) { 
			$error = Factory::load('language')->_('Price not valid.');
			return false;
		}
		return true;
	}

	public static function checkValidDate($value, &$error) {
		if(!preg_match('/([0-9]{0,4})-([0-9]{0,2})-([0-9]{0,2})/',$value, $matches)) {
			$error = Factory::load('language')->_('The date is not valid.');
			return false;
		}
		$y = $matches[1];
		$m = $matches[2];
		$d = $matches[3];

		if(empty($y) || empty($d) || empty($m)) {
			$error = Factory::load('language')->_('The date is not valid.');
			return false;
		}

		if(!checkdate ( $m, $d, $y )) {
			$error = Factory::load('language')->_('The date is not valid.');
			return false;
		}
		return true;
	}

	public static function checkDate($value, &$error) {
		$return = false;
		
		if(is_scalar($value)) {
			$value = strtotime($value, time());
				
			if($value !== false && $value !== -1) {
				$return = checkdate(date('m', $value), date('d', $value), date('Y', $value));
			}
		}
		
		if($return != true) {
			$error = Factory::load('language')->_('The date is not valid.');
		}
		
		return $return;
	}
	
	public static function check18($birthday, &$error) {
		$return   = false;
		
		if(self::checkDate($birthday, $error) === true) {
			$age = floor((date("Ymd") - Time::create($c.birthday)->date('Ymd')) / 10000);
			
			if($age >= 18) return true;
		}
		
		return Factory::load('language')->_('You must be at least 18 years old.');
	}
	
	public static function checkGermanZip($value, &$error) {
		 if(!preg_match("=^[0-9]{5,5}*$=",$value)) {
			$error = Factory::load('language')->_('Zip is not valid.');
			return false;
		 }
		return true;
	}
	
	public static function checkCaptcha($captcha, &$error) {
		$session = Factory::load('session');
		$session_captcha = $session->get('captcha');
		$session->delete('captcha');
		if (strtolower($captcha) != strtolower($session_captcha)) {
			$error = Factory::load('language')->_('Captcha does not match.');
			return false;
		}
		return true;
	}
	
	public static function checkInteger($value, &$error) {
		if(!preg_match('=^[0-9]*$=',$value)) { 
			$error = Factory::load('language')->_('Not a number.');
			return false;
		}
		return true;
	}

	public static function checkNumeric($value, &$error) {
		$return = (is_numeric($value)) ? true : false;
		
		if($return != true) {
			$error = Factory::load('language')->_('Not a number.');
		}
		
		return $return;
	}
	
	public static function checkEAN($value, &$error) {
		$return = false;
		
		if(is_string($value)) {
			$return = ($value[12] == (((ceil(((($value[1] + $value[3] + $value[5] + $value[7] + $value[9] + $value[11]) * 3) + ($value[0] + $value[2] + $value[4] + $value[6] + $value[8] + $value[10]))/10))*10) - ((($value[1] + $value[3] + $value[5] + $value[7] + $value[9] + $value[11]) * 3) + ($value[0] + $value[2] + $value[4] + $value[6] + $value[8] + $value[10]))));
		}
		
		if($return != true) {
			$error = Factory::load('language')->_('EAN Code not valid.');
		}
		
		return $return;
	}
	
	public static function checkUPC($value, &$error) {
		$return = false;
		
		if(is_string($value)) {
			$return = ($value[11] == (((ceil(((($value[0] + $value[2] + $value[4] + $value[6] + $value[8] + $value[10]) * 3) + ($value[1] + $value[3] + $value[5] + $value[7] + $value[9]))/10))*10) - ((($value[0] + $value[2] + $value[4] + $value[6] + $value[8] + $value[10]) * 3) + ($value[1] + $value[3] + $value[5] + $value[7] + $value[9]))));
		}
    	
		if($return != true) {
			$error = Factory::load('language')->_('UPC Code not valid.');
		}
		
    	return $return;
    }
}
