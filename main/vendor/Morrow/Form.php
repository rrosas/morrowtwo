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
class Form {
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
		list($id, $attributes) = $this->_prepare($name, $attributes);
		return "<label for=\"{$id}\"{$attributes}>{$value}</label>";
	}

	public function error($name, $attributes = array()) {
		if (!isset($this->_errors[$name])) return '';

		list($id, $attributes) = $this->_prepare($name, $attributes);
		
		// show only the first error. The others could be consequential error
		$value = $this->_escape(current($this->_errors[$name]));

		return "<span data-error-for=\"{$id}\"{$attributes}>{$value}</span>";
	}

	public function hidden($name, $value, $attributes = array()) {
		return $this->_getDefaultInputHtml('hidden', $name, $attributes, $value);
	}

	public function text($name, $attributes = array()) {
		return $this->_getDefaultInputHtml('text', $name, $attributes);
	}

	public function password($name, $attributes = array()) {
		return $this->_getDefaultInputHtml('password', $name, $attributes);
	}

	public function file($name, $attributes = array()) {
		return $this->_getDefaultInputHtml('file', $name, $attributes);
	}

	public function textarea($name, $attributes = array()) {
		list($id, $attributes, $value) = $this->_prepare($name, $attributes);
		return "<textarea id=\"{$id}\" name=\"{$name}\"{$attributes}>{$value}</textarea>";
	}

	public function checkbox($name, $value, $attributes = array()) {
		if (isset($this->_input[$name])) {
			if ($this->_input[$name] === $value) $attributes['checked'] = 'checked';
			else unset($attributes['checked']);
		}

		return $this->_getDefaultInputHtml('checkbox', $name, $attributes, $value);
	}

	public function radio($name, $value, $attributes = array()) {
		if (isset($this->_input[$name])) {
			if ($this->_input[$name] === $value) $attributes['checked'] = 'checked';
			else unset($attributes['checked']);
		}

		return $this->_getDefaultInputHtml('radio', $name, $attributes, $value);
	}
	
	public function select($name, $values, $attributes = array()) {
		list($id, $attributes, $selected_value) = $this->_prepare($name, $attributes);

		$content = "<select id=\"$id\" name=\"$name\"{$attributes}>";
		$content .= $this->select_option($values, $selected_value);
		$content .= "</select>";
		return $content;
	}

	public function select_option($values, $selected_value) {
		$content = '';

		foreach ($values as $value => $title) {
			if (is_array($title)) {
				$label		= $this->_escape($value);
				$content	.= "<optgroup label=\"{$label}\">";
				$content	.= $this->select_option($title, $selected_value);
				$content	.= "</optgroup>";
			} else {
				$value		= $this->_escape($value);
				$title		= $this->_escape($title);
				$selected	= $value == $selected_value ? ' selected="selected"' : '';
				$content	.= "<option value=\"{$value}\"{$selected}>{$title}</option>";
			}
		}

		return $content;
	}

	public function setName($name) {
		$this->_form_prefix = $name . '_';
	}

	protected function _getDefaultInputHtml($type, $name, $attributes, $value_fixed = null) {
		list($id, $attributes, $value) = $this->_prepare($name, $attributes, $value_fixed);
		if ($value_fixed !== null) $value = $this->_escape($value_fixed);

		return "<input type=\"{$type}\" id=\"{$id}\" name=\"{$name}\" value=\"{$value}\"{$attributes} />";
	}

	protected function _prepare($name, $attributes) {
		// add error class ...
		if (isset($this->_errors[$name])) {
			if (isset($attributes['class'])) {
				$attributes['class'] .= ' ' . self::$error_class;
			} else {
				$attributes['class'] = self::$error_class;
			}
		}

		// ... and create string from attributes
		$attributes_string = '';
		foreach ($attributes as $key => $value) {
			$attributes_string .= ' ' . $key . '="' . $this->_escape($value) . '"';
		}

		// add value
		if (!isset($this->_input[$name]) || is_array($this->_input[$name])) $value = '';
		else $value = $this->_escape($this->_input[$name]);
		
		// add id
		$id = $this->_form_prefix . str_replace(array('[', ']'), '', $name);

		return array($id, $attributes_string, $value);
	}

	protected function _escape($string) {
		return htmlspecialchars($string, ENT_QUOTES, 'utf-8');
	}
}
