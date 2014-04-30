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

/**
 * The Validator class provides several rules for validating data.
 * 
 * If you write a validator keep in mind that the validator should never throw an exception if `$input` is a scalar or an array.
 * Because if you validate data coming from a web client, data can only be a string or an array.
 */
class Form2 {
	protected $_input;
	protected $_errors;
	public static $error_class = 'error';
	
	public static $form_counter = 0;
	protected $_form_prefix;

	public function __construct($input, $errors) {
		$this->_input	= $input;
		$this->_errors	= $errors;

		$this->_form_prefix = 'form' . ++self::$form_counter . '_';
	}

	public function label($name, $value, $attributes = array()) {
		list($attributes) = $this->_prepare($name, $attributes);
		return '<label for="' . $this->_form_prefix . $name . '"' . $this->_getAttributesString($attributes) . '>' . $this->_escape($value) . '</label>';
	}

	public function hidden($name, $value, $attributes = array()) {
		return $this->_getDefaultHtml('hidden', $name, $attributes, $value);
	}

	public function text($name, $attributes = array()) {
		return $this->_getDefaultHtml('text', $name, $attributes);
	}

	public function password($name, $attributes = array()) {
		return $this->_getDefaultHtml('password', $name, $attributes);
	}

	public function file($name, $attributes = array()) {
		return $this->_getDefaultHtml('file', $name, $attributes);
	}

	public function textarea($name, $attributes = array()) {
		list($attributes, $value) = $this->_prepare($name, $attributes);
		return '<textarea id="' . $this->_form_prefix . $name . '" name="' . $name . '"' . $this->_getAttributesString($attributes) . '>' . $this->_escape($value) . '</textarea>';
	}

	public function checkbox($name, $value, $attributes = array()) {
		if (isset($this->_input[$name])) {
			if ($this->_input[$name] === $value) $attributes['checked'] = 'checked';
			else unset($attributes['checked']);
		}

		return $this->_getDefaultHtml('checkbox', $name, $attributes, $value);
	}

	public function radio($name, $value, $attributes = array()) {
		if (isset($this->_input[$name])) {
			if ($this->_input[$name] === $value) $attributes['checked'] = 'checked';
			else unset($attributes['checked']);
		}

		return $this->_getDefaultHtml('radio', $name, $attributes, $value);
	}
	
	public function select($name, $values, $params, $options, $multiple) {
		$classes = array();
		$class = '';
		$styles = '';
		if (isset($params['opt_classes'])) {
			$classes = $params['opt_classes'];
		}
		if (isset($params['class'])) {
			$class = $params['class'];
		}
		if (isset($params['styles'])) {
			$styles = $params['styles'];
		}
		$multiplestr = "";
		if ($multiple) { 
			$multiplestr = "multiple=\"multiple\"";
			$name .= "[]";
		}
		$output = array_values($options);
		$keys = array_keys($options);
		$content = "<select id=\"$id\" name=\"$name\"  " . $this->_getAttributeString($params, 'select') . " $multiplestr>" . chr(10);
		$content .= \Morrow\Helpers\Htmloptions::getOutput('', $keys, $output, $values, $class, $styles, $classes);

		$content .= "</select>" . chr(10);
		return $content;
	}







	public function setName($name) {
		$this->_form_prefix = $name . '_';
	}



	protected function _getDefaultHtml($type, $name, $attributes, $value_fixed = null) {
		list($attributes, $value) = $this->_prepare($name, $attributes);
		if ($value_fixed !== null) $value = $this->_escape($value_fixed);

		$id			= $this->_form_prefix . $name;
		$value		= $this->_escape($value);
		$attributes	= $this->_getAttributesString($attributes);

		return "<input type=\"{$type}\" id=\"{$id}\" name=\"{$name}\" value=\"{$value}\"{$attributes} />";
	}

	protected function _prepare($name, $attributes) {
		// add error class
		if (isset($this->_errors[$name])) {
			if (isset($attributes['class'])) {
				$attributes['class'] .= ' ' . self::$error_class;
			} else {
				$attributes['class'] = self::$error_class;
			}
		}

		// add value
		$value = isset($this->_input[$name]) ? $this->_input[$name] : '';
		
		return array($attributes, $value);
	}

	protected function _escape($string) {
		return htmlspecialchars($string, ENT_QUOTES, 'utf-8');
	}

	protected function _getAttributesString($attributes) {
		$returner = '';
		foreach ($attributes as $key => $value) {
			$returner .= ' ' . $key . '="' . $this->_escape($value) . '"';
		}
		return $returner;
	}
}
