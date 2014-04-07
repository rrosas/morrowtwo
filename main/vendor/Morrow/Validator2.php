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
Hinzufügen von Validatorn per Closure
*/

class Validator2 {
	protected $_closures = array();
	
	// Returns an array with all valid fields
	// $strict returns false if one of the fields is not valid
	public function filter($input, $fields, $strict = false) {
		$returner = array();

		foreach ($fields as $identifier => $rules_string) {
			// get value from input array
			$value = Helpers\General::array_dotSyntaxGet($input, $identifier);

			// explode rules
			$rules = explode('|', $rules_string);
			
			// only if all rules are true we add the value to the $returner array
			$add = true;

			foreach ($rules as $rule) {
				$parts	= explode(':', $rule, 2);
				$name	= $parts[0];
				$method	= '_validator_' . $parts[0];

				$callback = null;
				// does the user wants to use a predefined validator
				if (method_exists($this, $method)) $callback = array($this, $method);
				// or his own closure
				if (!isset($callback) && isset($this->_closures[$name])) $callback = $this->_closures[$name];
				// we did not found any callable validator
				if (!isset($callback)) throw new \Exception(__CLASS__ . ': Validator "'.$name.'" does not exist.');

				if (isset($parts[1])) {
					// there are params
					$params	= array_merge(array($input, $value), array_map('trim', explode(',', $parts[1])));
					$result = call_user_func_array($callback, $params);
				} else {
					// there are no params
					$result = call_user_func($callback, $input, $value);
				}
				
				if (!$result) $add = false;
				if (!$result && $strict) return false;
			}

			if ($add) Helpers\General::array_dotSyntaxSet($returner, $identifier, $value);
		}

		return $returner;
	}

	public function add($name, $closure) {
		$this->_closures[$name] = $closure;
	}

	protected function _validator_optional($input, $value) {
		return true;
	}

	protected function _validator_required($input, $value, $field = null, $field_value = null) {
		if ($field === null) return !is_null($value) && $value !== '';
		if ($input[$field] == $field_value) return !is_null($value) && $value !== '';
	}

	protected function _validator_same($input, $value, $compare_field) {
		return $value == $input[$compare_field];
	}

	protected function _validator_array($input, $value) {
		return is_array($value);
	}

	protected function _validator_integer($input, $value) {
		if (!is_scalar($value)) return false;
		if (!preg_match('=^[0-9]+$=', $value)) return false;
		return true;
	}

	protected function _validator_numeric($input, $value) {
		return is_numeric($value);
	}

	protected function _validator_min($input, $value, $min) {
		if (!is_scalar($value)) return false;
		return $value >= $min;
	}

	protected function _validator_max($input, $value, $max) {
		if (!is_scalar($value)) return false;
		return $value <= $max;
	}

	protected function _validator_length($input, $value, $min_length, $max_length) {
		if (!is_scalar($value)) return false;
		$strlen = strlen((string)$value);
		
		if ($strlen < $min_length) return false;
		if ($strlen > $max_length) return false;
		return true;
	}

	protected function _validator_regex($input, $value, $regex = null) {
		if (!is_scalar($value)) return false;
		$args = func_get_args();
		if (count($args) > 2) $regex = implode(',', array_slice($args, 2));

		if (!preg_match($regex, $value)) return false;
		return true;
	}

	protected function _validator_in($input, $value, $in = null) {
		if (!is_scalar($value)) return false;
		$in = array_map('strtolower', array_slice(func_get_args(), 2));

		return in_array((string)$value, $in, true);
	}

	protected function _validator_image($input, $path, $types_user = null) {
		if (!is_string($path)) return false;
		if (count(func_get_args()) < 3) throw new \Exception(__METHOD__ . ': You have to pass image types ("png", "gif" or "jpg").');

		$types = array(
			'jpg'	=> IMAGETYPE_JPEG,
			'png'	=> IMAGETYPE_PNG,
			'gif'	=> IMAGETYPE_GIF,
		);
		$types_user = array_map('strtolower', array_slice(func_get_args(), 2));
		$types		= array_intersect_key($types, array_flip($types_user));

		try {
			$imagesize = getimagesize($path);
			if (in_array($imagesize[2], $types)) {
				return true;
			}
		} catch (\Exception $e) {
			return false;
		}
		return false;
	}

	protected function _validator_width($input, $path, $width) {
		if (!is_string($path)) return false;
		if (!is_string($width)) throw new \Exception(__METHOD__ . ': $width has to be of type string.');

		try {
			$imagesize = getimagesize($path);
			if ($imagesize[0] == $width) return true;
			return false;
		} catch (\Exception $e) {
			return false;
		}
		return false;
	}

	protected function _validator_height($input, $path, $height) {
		if (!is_string($path)) return false;
		if (!is_string($height)) throw new \Exception(__METHOD__ . ': $height has to be of type string.');

		try {
			$imagesize = getimagesize($path);
			if ($imagesize[1] == $height) return true;
			return false;
		} catch (\Exception $e) {
			return false;
		}
		return false;
	}

	protected function _validator_email($input, $address) {
		if (!is_string($address)) return false;
		
		// syntax check
		$localpart = "[a-z0-9!#$%&'*+-/\=?^_`{|}~]"; // RFC 2822
		$domainpart = "[\.a-z0-9-]";
		if (!preg_match("=^$localpart+(\.$localpart+)*@($domainpart+\.$domainpart+)$=i", $address, $match)) {
			return false;
		}
		
		// dnscheck
		$host = $match[2];
		if (!(checkdnsrr($host, 'MX') || checkdnsrr($host, 'A'))) {
			return false;
		}
		
		return true;
	}

	protected function _validator_url($input, $url, $schemes = null) {
		if (!is_string($url)) return false;
		if (count(func_get_args()) < 3) throw new \Exception(__METHOD__ . ': You have to pass schemes like "http", "https" or "ftp".');

		$schemes_user = array_map('strtolower', array_slice(func_get_args(), 2));

		$domainpart = "[\.a-z0-9-]";
		$regex = '~^(' . implode(':|', $schemes_user) . ':)?//(' . $domainpart . '+)~';
		if (!preg_match($regex, $url, $match)) return false;

		// dnscheck
		$host = $match[2];
		if (!checkdnsrr($host, 'A')) {
			return false;
		}

		return true;
	}

	protected function _validator_ip($input, $ip, $flags_user = null) {
		if (!is_string($ip)) return false;
		if (count(func_get_args()) < 3) throw new \Exception(__METHOD__ . ': You have to pass flags ("ipv4", "ipv6", "private" or "reserved").');

		$flags = array_flip(array_map('strtolower', array_slice(func_get_args(), 2)));

		// by default we don't want to have private or reserved ips
		$options = 0;
		if (!isset($flags['private'])) $options		|= FILTER_FLAG_NO_PRIV_RANGE;
		if (!isset($flags['reserved'])) $options	|= FILTER_FLAG_NO_RES_RANGE;

		// check for ipv4 and ipv6 compability
		if (isset($flags['ipv4']) && isset($flags['ipv6'])) $result = filter_var($ip, FILTER_VALIDATE_IP, $options);
		else if (isset($flags['ipv4'])) $result = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | $options);
		else if (isset($flags['ipv6'])) $result = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 | $options);
		else throw new \Exception(__METHOD__ . ': You have define the IP version ("ipv4" or "ipv6").');

		if ($result === false) return false;
		return true;
	}
		
	protected function _validator_date($input, $date, $date_format = null) {
		if (!is_string($date)) return false;
		$timestamp = strtotime($date);
		if ($timestamp === false) return false;


		if ($date_format !== null) {
			if ($date !== strftime($date_format, $timestamp)) return false;
		}

		return true;
	}
	
	protected function _validator_before($input, $date, $before) {
		if (!is_string($date)) return false;
		if (strtotime($date) >= strtotime($before)) return false;
		return true;
	}
	
	protected function _validator_after($input, $date, $after) {
		if (!is_string($date)) return false;
		if (strtotime($date) <= strtotime($after)) return false;
		return true;
	}

	protected function _validator_age($input, $birthday, $min, $max) {
		if (!is_string($date)) return false;
		$birthday = strtotime($birthday);
		if ($birthday === false) return false;

		$age = floor((date("Ymd") - date('Ymd', $birthday)) / 10000);
		if ($age < $min) return false;
		if ($age > $max) return false;
		
		return true;
	}
}
