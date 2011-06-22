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


/** PLEASE! **********************************************************************************
this file is intended as an example for several validation cases, you should override this 
file with your own validator.class.php in _libs or set a different (your own) validator.class 
using the appropriate form.class method.

Refer to the wiki page "Form Handling" <http://code.google.com/p/morrowtwo/wiki/FtFormHandling>.
**********************************************************************************************/


class Validator{


	public static function checkUsername($value, &$error){
		// check the format of the username
		if (!preg_match("=^[a-z0-9_]{5,20}$=i", $value)){
			$error = 'BADUSERNAME';
			return false;
		}
		return true;
	}

	public static function checkJpegFile($value, &$error){
		$img_attr = getimagesize($value['tmp_name']);
		if($img_attr[2] === IMAGETYPE_JPEG){
			return true;
		}
		$error = "NOTJPEG";
		return false;
	}

	public static function checkNumber($value, &$error){
		if(!preg('=^[0-9]*$=',$value)){ 
			$error = 'NAN';
			return false;
		}
		return true;
	}

	public static function checkEmail( $var, &$error )
		{
		if (!is_string($var)) return false;
		
		// syntax check
		$localpart = "[a-z0-9!#$%&'*+-/\=?^_`{|}~]"; // RFC 2822
		$domainpart = "[\.a-z0-9-]";
		if (!preg_match("=^$localpart+(\.$localpart+)*@($domainpart+)$=i", $var, $match))
			{
			$error = 'BADEMAIL';
			return false;
			}
		
		// dnscheck
		$host = $match[2];
		if(!(checkdnsrr($host, 'MX') || checkdnsrr($host, 'A')))
			{
			$error = 'BADEMAIL';
			return false;
			}
		
		return $var;
		}
		
	public static function checkPassword($value, &$error, $compare_value=null){
		 if(strlen($value) > 0 && strlen($value) < 5) {
			$error = 'PWTOOSHORT';
			return false;
		 }
		if($compare_value !== null && $value != $compare_value){
			$error = 'MISMATCH';
			return false;
		}
		return true;
	}

	public static function checkCurrency($value, &$error, $compare_value=null, $locale){
		$sep = ".";
		if(isset($locale['currency']['separator'])){
			$sep = $locale['currency']['separator'];
		}
		if(!preg("=^[0-9]{1,}({$sep}[0-9]{0,4})?$=",$value)){ 
			$error = 'BADPRICE';
			return false;
		}
		return true;
	}

	public static function checkValidDate($value, &$error){
		if(!preg_match('/([0-9]{0,4})-([0-9]{0,2})-([0-9]{0,2})/',$value, $matches)) {
			$error = 'BADDATE';
			return false;
		}
		$y = $matches[1];
		$m = $matches[2];
		$d = $matches[3];

		if(empty($y) || empty($d) || empty($m)) {
			$error = 'BADDATE';
			return false;
		}

		if(!checkdate ( $m, $d, $y )){
			$error = 'BADDATE';
			return false;
		}
		return true;
	}

	public static function checkOver18($value, &$error){
		list($y,$m,$d) = explode("-",$value);
		if(!checkdate ( $m, $d, $y )){
			$error = 'BADDATE';
			return false;
		}
		$checkdate = strtotime ($value);
		$min_date = mktime(0,0,0,date('m'),date('d'),(date('Y')-18));
		if($checkdate > $min_date){
			$error = 'TOOYOUNG';
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
			$error = 'BADDATE';
		}
		
		return $return;
	}
	
	public static function check18($birthday, &$error) {
		$return   = false;
		
		if(self::checkDate($birthday, $error) === true) {
			$time     = time();
			$birthday = strtotime($birthday, $time);
			$age      = (date('m-d', $birthday) <= date('m-d', $time)) ? date('Y', $time) - date('Y', $birthday) : date('Y', $time) - date('Y', $birthday) - 1;
			
			if($age >= 18) {
				$return = true;
			}
			
			unset($time);
		}
		
		if($return != true) {
			$error = 'TOOYOUNG';
		}
		
		return $return;
	}
	
	public static function checkMinAge($birthday, &$error, $compare, $arguments) {
		$return   = false;
		
		if(empty($arguments) || !is_scalar($arguments) || self::checkInteger($arguments, $error) != true) {
			trigger_error('Missing or invalid argument 4 for Validator::checkMinAge()', E_USER_ERROR);
		}
		
		if(self::checkDate($birthday, $error) === true) {
			$time     = time();
			$birthday = strtotime($birthday, $time);
			$age = (date('m-d', $birthday) <= date('m-d', $time)) ? date('Y', $time) - date('Y', $birthday) : date('Y', $time) - date('Y', $birthday) - 1;
			
			if($age >= $arguments) {
				$return = true;
			}
			
			unset($time);
		}
		
		if($return != true) {
			$error = 'TOOYOUNG';
		}
		
		return $return;
	}

	public static function checkZip($value, &$error) {
		// nur deutsche PLZ!
		 if(!preg("=^[0-9]*$=",$value)){
			$error = 'ZIPBADCHARS';
			return false;
		 }
		 if(strlen($value) > 0 && strlen($value) < 5) {
			$error = 'ZIPTOOSHORT';
			return false;
		 }
		 if(strlen($value) > 5) {
			$error = 'ZIPTOOLONG';
			return false;
		 }
		return true;
	}
	public static function checkCaptcha($captcha, &$error)
		{
		$session = Factory::load('session');
		$session_captcha = $session->get('captcha');
		$session->delete('captcha');
		if (strtolower($captcha) != strtolower($session_captcha))
			{
			$error = 'BADCAPTCHA';
			return false;
		}
		return true;
	}
	
	public static function checkFloat($value, &$error) {
		$return = (is_float($value)) ? true : false;
		
		if($return != true) {
			$error = 'NOTFLOAT';
		}
		
		return $return;
	}
	
	public static function checkInteger($value, &$error) {
		$return = ((string) $value) === ((string) (int) $value);
		
		if($return != true) {
			$error = 'NOTINTEGER';
		}
		
		return $return;
	}
	
	public static function checkNumeric($value, &$error) {
		$return = (is_numeric($value)) ? true : false;
		
		if($return != true) {
			$error = 'NOTNUMERIC';
		}
		
		return $return;
	}
	
	public static function checkEAN($value, &$error) {
		$return = false;
		
		if(is_string($value)) {
			$return = ($value[12] == (((ceil(((($value[1] + $value[3] + $value[5] + $value[7] + $value[9] + $value[11]) * 3) + ($value[0] + $value[2] + $value[4] + $value[6] + $value[8] + $value[10]))/10))*10) - ((($value[1] + $value[3] + $value[5] + $value[7] + $value[9] + $value[11]) * 3) + ($value[0] + $value[2] + $value[4] + $value[6] + $value[8] + $value[10]))));
		}
		
		if($return != true) {
			$error = 'BADEAN';
		}
		
		return $return;
	}
	
	public static function checkUPC($value, &$error) {
		$return = false;
		
		if(is_string($value)) {
			$return = ($value[11] == (((ceil(((($value[0] + $value[2] + $value[4] + $value[6] + $value[8] + $value[10]) * 3) + ($value[1] + $value[3] + $value[5] + $value[7] + $value[9]))/10))*10) - ((($value[0] + $value[2] + $value[4] + $value[6] + $value[8] + $value[10]) * 3) + ($value[1] + $value[3] + $value[5] + $value[7] + $value[9]))));
		}
    	
		if($return != true) {
			$error = 'BADUPC';
		}
		
    	return $return;
    }
}
