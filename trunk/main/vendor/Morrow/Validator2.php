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
/**
 * The Validator class provides several rules for validating data.
 * 
 * If you write a validator keep in mind that the validator should never throw an exception if `$input` is a scalar or an array.
 * Because if you validate data coming from a web client, data can only be a string or an array.
 *
 * Dot syntax for the keys of the rules.
 * 
 * Example
 * --------
 * 
 * ~~~{.php}
 * // ... Controller code
 * 
 * // optional: add your own validator with your own error message
 * $this->validator->add('captcha', function($input, $value, $session_captcha) {
 * 	return $value !== $session_captcha;
 * }, 'Captcha is wrong');
 * 
 * // optional: we want a different error message for the "equal" validator
 * $this->validator->setMessages(array(
 * 	'password'			=> 'The password should have at least %s characters.',
 * ));
 * 
 * // now let us validate the input data
 * $rules =  array(
 * 	'username'			=> array('required', 'regex' => '/[0-9a-z-_]{5,}/i'),
 * 	'email'				=> array('required', 'email'),
 * 	'password'			=> array('required', 'minlength' => 8),
 * 	'repeat_password'	=> array('required', 'same' => 'password'),
 * 	'captcha'			=> array('captcha' => $this->session->get('captcha')),
 * );
 * 
 * $input = $this->validator->filter($this->input->get(), $rules, $errors);
 *
 * // ... Controller code
 * ~~~
 * 
 * Shipped validators
 * -------------------
 *
 * Validator   | Keyname                | Default    | Description                                                              
 * ---------   | ---------              | ---------  | ------------                                                             
 * `optional`  | `debug.output.screen`  | `true`     | Defines if errors should be displayed on screen                          
 * `required`  | `debug.output.file`    | `true`     | Defines if errors should be logged to the file system
 * `equal`     | `debug.file.path`      | `APP_PATH .'logs/error_'. date('Y-m-d') .'.txt'` | Defines the path where to save the errors
 * `same`      | `debug.file.path`      | `APP_PATH .'logs/error_'. date('Y-m-d') .'.txt'` | Defines the path where to save the errors
 * `array`     | `debug.file.path`      | `APP_PATH .'logs/error_'. date('Y-m-d') .'.txt'` | Defines the path where to save the errors
 * `integer`   | `debug.file.path`      | `APP_PATH .'logs/error_'. date('Y-m-d') .'.txt'` | Defines the path where to save the errors
 * `numeric`   | `debug.file.path`      | `APP_PATH .'logs/error_'. date('Y-m-d') .'.txt'` | Defines the path where to save the errors
 * `min`       | `debug.file.path`      | `APP_PATH .'logs/error_'. date('Y-m-d') .'.txt'` | Defines the path where to save the errors
 * `max`       | `debug.file.path`      | `APP_PATH .'logs/error_'. date('Y-m-d') .'.txt'` | Defines the path where to save the errors
 * `minlength` | `debug.file.path`      | `APP_PATH .'logs/error_'. date('Y-m-d') .'.txt'` | Defines the path where to save the errors
 * `maxlength` | `debug.file.path`      | `APP_PATH .'logs/error_'. date('Y-m-d') .'.txt'` | Defines the path where to save the errors
 * `regex`     | `debug.file.path`      | `APP_PATH .'logs/error_'. date('Y-m-d') .'.txt'` | Defines the path where to save the errors
 * `in`        | `debug.file.path`      | `APP_PATH .'logs/error_'. date('Y-m-d') .'.txt'` | Defines the path where to save the errors
 * `image`     | `debug.file.path`      | `APP_PATH .'logs/error_'. date('Y-m-d') .'.txt'` | Defines the path where to save the errors
 * `width`     | `debug.file.path`      | `APP_PATH .'logs/error_'. date('Y-m-d') .'.txt'` | Defines the path where to save the errors
 * `height`    | `debug.file.path`      | `APP_PATH .'logs/error_'. date('Y-m-d') .'.txt'` | Defines the path where to save the errors
 * `email`     | `debug.file.path`      | `APP_PATH .'logs/error_'. date('Y-m-d') .'.txt'` | Defines the path where to save the errors
 * `url`       | `debug.file.path`      | `APP_PATH .'logs/error_'. date('Y-m-d') .'.txt'` | Defines the path where to save the errors
 * `ip`        | `debug.file.path`      | `APP_PATH .'logs/error_'. date('Y-m-d') .'.txt'` | Defines the path where to save the errors
 * `date`      | `debug.file.path`      | `APP_PATH .'logs/error_'. date('Y-m-d') .'.txt'` | Defines the path where to save the errors
 * `before`    | `debug.file.path`      | `APP_PATH .'logs/error_'. date('Y-m-d') .'.txt'` | Defines the path where to save the errors
 * `after`     | `debug.file.path`      | `APP_PATH .'logs/error_'. date('Y-m-d') .'.txt'` | Defines the path where to save the errors
 * `age`       | `debug.file.path`      | `APP_PATH .'logs/error_'. date('Y-m-d') .'.txt'` | Defines the path where to save the errors
 *
 * 
 */
class Validator2 {
	/**
	 * Contains all user defined validator set by add().
	 * @var array $_callbacks
	 */
	protected $_callbacks = array();

	/**
	 * Contains the error messages for all validators.
	 * @var array $_messages
	 */
	protected $_messages = array();
	
	/**
	 * Initializes the class and sets default error messages.
	 */
	public function __construct() {
		$this->_messages = array(
			'optional'	=> 'VALDIDATOR_OPTIONAL',
			'required'	=> 'VALDIDATOR_REQUIRED',
			'equal'		=> 'VALDIDATOR_EQUAL:%s',
			'same'		=> 'VALDIDATOR_SAME:%s',
			'array'		=> 'VALDIDATOR_ARRAY',
			'integer'	=> 'VALDIDATOR_INTEGER',
			'numeric'	=> 'VALDIDATOR_NUMERIC',
			'min'		=> 'VALDIDATOR_MIN:%s',
			'max'		=> 'VALDIDATOR_MAX:%s',
			'minlength'	=> 'VALDIDATOR_MINLENGTH:%s',
			'maxlength'	=> 'VALDIDATOR_MAXLENGTH:%s',
			'regex'		=> 'VALDIDATOR_REGEX',
			'in'		=> 'VALDIDATOR_IN',
			'image'		=> 'VALDIDATOR_IMAGE',
			'width'		=> 'VALDIDATOR_WIDTH:%s',
			'height'	=> 'VALDIDATOR_HEIGHT:%s',
			'email'		=> 'VALDIDATOR_EMAIL',
			'url'		=> 'VALDIDATOR_URL',
			'ip'		=> 'VALDIDATOR_IP',
			'date'		=> 'VALDIDATOR_DATE',
			'before'	=> 'VALDIDATOR_BEFORE:%s',
			'after'		=> 'VALDIDATOR_AFTER:%',
			'age'		=> 'VALDIDATOR_AGE',
		);
	}

	/**
	 * Validates an array of input data against a set of rules.
	 * @param	array	$input	An associative array with the data that should be validated.
	 * @param	array	$rules	The validation rules the the input data should be validated against.
	 * @param	array	$errors	Errors are written to this parameter.
	 * @param	boolean	$strict	If set to `true`, the function will return false if at least one field is not valid.
	 * @return 	mixed	Returns an array with all valid fields or `false` if `$strict` was set and at least one field was not valid.
	 */
	public function filter(array $input, array $rules, &$errors = array(), $strict = false) {
		$returner	= array();
		$errors		= array();

		foreach ($rules as $identifier => $rules_array) {
			// get value from input array
			$value = Helpers\General::array_dotSyntaxGet($input, $identifier);

			// explode rules
			// only if all rules are true we add the value to the $returner array
			$add = true;

			foreach ($rules_array as $possible_rule1 => $possible_rule2) {
				
				$name	= $possible_rule2;
				$params	= null;

				if (!is_numeric($possible_rule1)) {
					$name	= $possible_rule1;
					$params	= array($possible_rule2);
				}

				$method	= '_validator_' . $name;

				$callback = null;
				// does the user wants to use a predefined validator
				if (method_exists($this, $method)) $callback = array($this, $method);
				// or his own closure
				if (!isset($callback) && isset($this->_callbacks[$name])) $callback = $this->_callbacks[$name];
				// we did not found any callable validator
				if (!isset($callback)) throw new \Exception(__CLASS__ . ': Validator "'.$name.'" does not exist.');

				if (isset($params)) {
					// there are params
					$result = call_user_func_array($callback, array_merge(array($input, $value), $params));
				} else {
					// there are no params
					$result = call_user_func($callback, $input, $value);
				}

				// fill errors array
				if (!$result) {
					// map all params to strings
					$params = array_map('strval', $params);
					$errors[$identifier][$name] = vsprintf($this->_messages[$name], $params);

				}

				if (!$result) $add = false;
				if (!$result && $strict) return false;
			}

			if ($add) Helpers\General::array_dotSyntaxSet($returner, $identifier, $value);
		}

		return $returner;
	}

	/**
	 * Adds a user defined validator.
	 * @param	string	$name	The name of the validator.
	 * @param	callback	$callback	A valid PHP callback like `array('OBJECT', 'METHOD')`, `array($obj, 'METHOD')` or a n anonymous function like `function($input, $value){}`. The method gets at least two parameters passed. An array of all `$input` parameters and the `$value` to validate. Other parameters are the passed parameters.
	 * @param	callback	$error_message	The error message that get all parameters passed via `sprintf`, so you can use `%s` and other `sprintf` replacements.
	 */
	public function add($name, $callback, $error_message) {
		$this->_callbacks[$name] = $callback;
		$this->_messages[$name] = $error_message;
	}

	/**
	 * Adds user defined error messages.
	 * @param	array	$messages	An associative array with the error messages. Parameters are passed via `sprintf`, so you can use `%s` and other `sprintf` replacements.
	 */
	public function setMessages($messages) {
		foreach ($messages as $name=>$message) {
			$this->_messages[$name] = $message;
		}
	}

	/**
	 * Look at the validator list.
	 * @param	array	$input	All input parameters that were passed to `filter()`.
	 * @param	mixed	$value	The input data to validate.
	 * @return 	booolean	The result of the validation.
	 */
	protected function _validator_optional($input, $value) {
		return true;
	}

	/**
	 * Look at the validator list.
	 * @param	array	$input	All input parameters that were passed to `filter()`.
	 * @param	mixed	$value	The input data to validate.
	 * @param	array	$fields	If the fields in this array have the same values as in the input array this validator checks for required.
	 * @return 	booolean	The result of the validation.
	 */
	protected function _validator_required($input, $value, $fields = array()) {
		// check for values in fields to make this field required 
		$required = true;
		foreach ($fields as $key => $value) {
			if (!isset($input[$key]) || $input[$key] != $value) {
				$required = false;
				break;
			}
		}

		if ($required) return !is_null($value) && !empty($value);
		else return true;
	}

	/**
	 * Look at the validator list.
	 * @param	array	$input	All input parameters that were passed to `filter()`.
	 * @param	mixed	$value	The input data to validate.
	 * @param	mixed	$compare_value	The value to compare `$value` with.
	 * @return 	booolean	The result of the validation.
	 */
	protected function _validator_equal($input, $value, $compare_value) {
		return $value == $compare_value;
	}

	/**
	 * Look at the validator list.
	 * @param	array	$input	All input parameters that were passed to `filter()`.
	 * @param	mixed	$value	The input data to validate.
	 * @param	string	$compare_field	The field to compare `$value` with.
	 * @return 	booolean	The result of the validation.
	 */
	protected function _validator_same($input, $value, $compare_field) {
		if (!isset($input[$compare_field])) return false;
		return $value == $input[$compare_field];
	}

	/**
	 * Look at the validator list.
	 * @param	array	$input	All input parameters that were passed to `filter()`.
	 * @param	mixed	$value	The input data to validate.
	 * @return 	booolean	The result of the validation.
	 */
	protected function _validator_array($input, $value) {
		return is_array($value);
	}

	/**
	 * Look at the validator list.
	 * @param	array	$input	All input parameters that were passed to `filter()`.
	 * @param	mixed	$value	The input data to validate.
	 * @return 	booolean	The result of the validation.
	 */
	protected function _validator_integer($input, $value) {
		if (!is_string($value) && !is_integer($value) && !is_float($value)) return false;
		if (is_string($value)) $value = (int)$value;
		return is_integer($value);
	}

	/**
	 * Look at the validator list.
	 * @param	array	$input	All input parameters that were passed to `filter()`.
	 * @param	mixed	$value	The input data to validate.
	 * @return 	booolean	The result of the validation.
	 */
	protected function _validator_numeric($input, $value) {
		return is_numeric($value);
	}

	/**
	 * Look at the validator list.
	 * @param	array	$input	All input parameters that were passed to `filter()`.
	 * @param	mixed	$value	The input data to validate.
	 * @param	integer	$min	The min value of `$value`.
	 * @return 	booolean	The result of the validation.
	 */
	protected function _validator_min($input, $value, $min) {
		if (!is_string($value) && !is_integer($value) && !is_float($value)) return false;
		if (is_string($value)) $value = (int)$value;
		return $value >= $min;
	}

	/**
	 * Look at the validator list.
	 * @param	array	$input	All input parameters that were passed to `filter()`.
	 * @param	mixed	$value	The input data to validate.
	 * @param	integer	$max	The max value of `$value`.
	 * @return 	booolean	The result of the validation.
	 */
	protected function _validator_max($input, $value, $max) {
		if (!is_string($value) && !is_integer($value) && !is_float($value)) return false;
		if (is_string($value)) $value = (int)$value;
		return $value <= $max;
	}

	/**
	 * Look at the validator list.
	 * @param	array	$input	All input parameters that were passed to `filter()`.
	 * @param	mixed	$value	The input data to validate.
	 * @param	integer	$min	The min length of `$value`.
	 * @return 	booolean	The result of the validation.
	 */
	protected function _validator_minlength($input, $value, $min) {
		if (!is_string($value)) return false;
		if (strlen($value) < $min) return false;
		return true;
	}

	/**
	 * Look at the validator list.
	 * @param	array	$input	All input parameters that were passed to `filter()`.
	 * @param	mixed	$value	The input data to validate.
	 * @param	integer	$max	The max length of `$value`.
	 * @return 	booolean	The result of the validation.
	 */
	protected function _validator_maxlength($input, $value, $max) {
		if (!is_string($value)) return false;
		if (strlen($value) > $max) return false;
		return true;
	}

	/**
	 * Look at the validator list.
	 * @param	array	$input	All input parameters that were passed to `filter()`.
	 * @param	mixed	$value	The input data to validate.
	 * @param	string	$regex	The regex to validate against.
	 * @return 	booolean	The result of the validation.
	 */
	protected function _validator_regex($input, $value, $regex) {
		if (!is_string($value) && !is_integer($value) && !is_float($value)) return false;
		if (!preg_match($regex, $value)) return false;
		return true;
	}

	/**
	 * Look at the validator list.
	 * @param	array	$input	All input parameters that were passed to `filter()`.
	 * @param	mixed	$value	The input data to validate.
	 * @param	array	$in	An array of values `$value` has to exist in.
	 * @return 	booolean	The result of the validation.
	 */
	protected function _validator_in($input, $value, $in) {
		return in_array($value, $in);
	}

	/**
	 * Look at the validator list.
	 * @param	array	$input	All input parameters that were passed to `filter()`.
	 * @param	mixed	$value	The input data to validate.
	 * @param	array	$types_user	An array of filetypes the filetype of `$value` has to exist in.
	 * @return 	booolean	The result of the validation.
	 */
	protected function _validator_image($input, $value, $types_user) {
		if (!is_string($value)) return false;

		$types = array(
			'jpg'	=> IMAGETYPE_JPEG,
			'png'	=> IMAGETYPE_PNG,
			'gif'	=> IMAGETYPE_GIF,
		);
		$types_user = array_map('strtolower', $types_user);
		$types		= array_intersect_key($types, array_flip($types_user));

		try {
			$imagesize = getimagesize($value);
			if (in_array($imagesize[2], $types)) {
				return true;
			}
		} catch (\Exception $e) {
			return false;
		}
		return false;
	}

	/**
	 * Look at the validator list.
	 * @param	array	$input	All input parameters that were passed to `filter()`.
	 * @param	mixed	$value	The input data to validate.
	 * @param	integer	$width	The width the image must have.
	 * @return 	booolean	The result of the validation.
	 */
	protected function _validator_width($input, $value, $width) {
		if (!is_string($value)) return false;

		try {
			$imagesize = getimagesize($value);
			if ($imagesize[0] == $width) return true;
			return false;
		} catch (\Exception $e) {
			return false;
		}
		return false;
	}

	/**
	 * Look at the validator list.
	 * @param	array	$input	All input parameters that were passed to `filter()`.
	 * @param	mixed	$value	The input data to validate.
	 * @param	integer	$height	The height the image must have.
	 * @return 	booolean	The result of the validation.
	 */
	protected function _validator_height($input, $value, $height) {
		if (!is_string($value)) return false;

		try {
			$imagesize = getimagesize($value);
			if ($imagesize[1] == $height) return true;
			return false;
		} catch (\Exception $e) {
			return false;
		}
		return false;
	}

	/**
	 * Look at the validator list.
	 * @param	array	$input	All input parameters that were passed to `filter()`.
	 * @param	mixed	$value	The input data to validate.
	 * @return 	booolean	The result of the validation.
	 */
	protected function _validator_email($input, $value) {
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

	/**
	 * Look at the validator list.
	 * @param	array	$input	All input parameters that were passed to `filter()`.
	 * @param	mixed	$value	The input data to validate.
	 * @param	array	$schemes_user	An array of schemes the scheme of `$value` has to exist in.
	 * @return 	booolean	The result of the validation.
	 */
	protected function _validator_url($input, $value, $schemes_user) {
		if (!is_string($value)) return false;

		$schemes = array_map('strtolower', $schemes_user);

		$domainpart = "[\.a-z0-9-]";
		$regex = '~^(' . implode(':|', $schemes) . ':)?//(' . $domainpart . '+)~i';
		if (!preg_match($regex, $value, $match)) return false;

		// dnscheck
		$host = $match[2];
		if (!checkdnsrr($host, 'A')) {
			return false;
		}

		return true;
	}

	/**
	 * Look at the validator list.
	 * @param	array	$input	All input parameters that were passed to `filter()`.
	 * @param	mixed	$value	The input data to validate.
	 * @return 	booolean	The result of the validation.
	 * @param	array	$flags_user	An array of falgs to control the behaviour of the validator.
	 */
	protected function _validator_ip($input, $value, $flags_user) {
		if (!is_string($value)) return false;

		$flags = array_flip(array_map('strtolower', $flags_user));

		// by default we don't want to have private or reserved ips
		$options = 0;
		if (!isset($flags['private'])) $options		|= FILTER_FLAG_NO_PRIV_RANGE;
		if (!isset($flags['reserved'])) $options	|= FILTER_FLAG_NO_RES_RANGE;

		// check for ipv4 and ipv6 compability
		if (isset($flags['ipv4']) && isset($flags['ipv6'])) $result = filter_var($value, FILTER_VALIDATE_IP, $options);
		else if (isset($flags['ipv4'])) $result = filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | $options);
		else if (isset($flags['ipv6'])) $result = filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 | $options);
		else throw new \Exception(__METHOD__ . ': You have define the IP version ("ipv4" or "ipv6").');

		if ($result === false) return false;
		return true;
	}
		
	/**
	 * Look at the validator list.
	 * @param	array	$input	All input parameters that were passed to `filter()`.
	 * @param	mixed	$value	The input data to validate.
	 * @param	string	$date_format	The format to validate against.
	 * @return 	booolean	The result of the validation.
	 */
	protected function _validator_date($input, $value, $date_format = null) {
		if (!is_string($value) && !is_integer($value)) return false;
		$timestamp = is_string($value) ? strtotime($value) : $value;
		if ($timestamp === false) return false;

		if ($date_format !== null) {
			if ($value !== strftime($date_format, $timestamp)) return false;
		}

		return true;
	}
	
	/**
	 * Look at the validator list.
	 * @param	array	$input	All input parameters that were passed to `filter()`.
	 * @param	mixed	$value	The input data to validate.
	 * @param	string	$before	The date (readable by `strtotime`) that has to be before `$value`.
	 * @return 	booolean	The result of the validation.
	 */
	protected function _validator_before($input, $value, $before) {
		if (!is_string($value) && !is_integer($value)) return false;
		$timestamp = is_string($value) ? strtotime($value) : $value;

		if (!is_string($before) && !is_integer($before)) return false;
		$timestamp2 = is_string($before) ? strtotime($before) : $before;

		if ($timestamp >= $timestamp2) return false;
		return true;
	}
	
	/**
	 * Look at the validator list.
	 * @param	array	$input	All input parameters that were passed to `filter()`.
	 * @param	mixed	$value	The input data to validate.
	 * @param	string	$after	The date (readable by `strtotime`) that has to be after `$value`.
	 * @return 	booolean	The result of the validation.
	 */
	protected function _validator_after($input, $value, $after) {
		if (!is_string($value) && !is_integer($value)) return false;
		$timestamp = is_string($value) ? strtotime($value) : $value;

		if (!is_string($after) && !is_integer($after)) return false;
		$timestamp2 = is_string($after) ? strtotime($after) : $after;

		if ($timestamp <= $timestamp2) return false;
		return true;
	}

	/**
	 * Look at the validator list.
	 * @param	array	$input	All input parameters that were passed to `filter()`.
	 * @param	mixed	$value	The input data to validate.
	 * @param	array	$boundaries	Contains the minimum and maximum boundaries for the age.
	 * @return 	booolean	The result of the validation.
	 */
	protected function _validator_age($input, $value, $boundaries) {
		$min = $boundaries[0];
		$max = $boundaries[1];

		if (!is_string($value) && !is_integer($value)) return false;
		$timestamp = is_string($value) ? strtotime($value) : $value;

		if ($timestamp === false) return false;

		$age = floor((date("Ymd") - date('Ymd', $timestamp)) / 10000);
		if ($age < $min) return false;
		if ($age > $max) return false;
		
		return true;
	}
}
