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


namespace Morrow\Formhtmlelements;

class Select extends AbstractElement {
	public function getDisplay($name, $values, $id, $params, $options, $multiple) {
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

	public function getReadonly($name, $values, $id, $params, $options, $multiple) {
		if ($multiple) {
			$name .= "[]";
		}
		$content = '';
		if (is_array($values)) {
			foreach ($values as $value) {
				$content .= '<input type="hidden" name="'.$name.'" value="'.$value.'" />';
				$content .= '<div '. $this->_getAttributeString($params, 'div') .'>'.$options[$value].'</div>';
			}
		} else {
			$value = $values;
			if (isset($options[$values])) $value = $options[$values];
			$content .= '<input type="hidden" name="'.$name.'" value="'.$values.'" />';
			$content .= '<div '. $this->_getAttributeString($params, 'div') .'>'.$value.'</div>';
		}
		return $content;
	}

	public function getListDisplay($values, $params, $options = array()) {
		$content = '';
		if (is_array($values)) {
			$tmp_content = array();
			foreach ($values as $value) {
				$tmp_content[] = isset($options[$value]) ? htmlspecialchars($options[$value], ENT_QUOTES, $this->page->get('charset')) : htmlspecialchars($value, ENT_QUOTES, $this->page->get('charset'));
			}
			$content = '<ul><li>' . implode('</li><li>', $tmp_content) . '</li></ul>';
		} else {
			$content = isset($options[$values]) ? $options[$values] : $values;
			$content = htmlspecialchars($content, ENT_QUOTES, $this->page->get('charset'));
		}
		return $content;
	}

	protected function _getOutput($name, $keys, $values, $selected = array(), $class = '', $style = array(), $classes = array(), $extras = array()) {
		if (!is_array($selected)) $selected = array_map('strval', array_values((array)$selected));
		
		$_html_result = '';
		foreach ($keys as $i => $key) {
			// add css styles
			$ostyle = (is_array($style) && isset($style[$i])) ? $style[$i] : '';

			// add generated option to output
			$_html_result .= Htmloptions::getOption($key, $values[$i], $selected, $ostyle, $class);
		}

		// add extras
		$extra_str = '';
		foreach ($extras as $_key => $_value) {
			$extra_str .= ' '.$_key.'="'.$this->_htmlSpecialChars($_value).'"';
		}

		if (!empty($name)) {
			$_html_result = "<select name=\"{$name}\" {$extra_str}>{$_html_result}</select>\n";
		}
		
		return $_html_result;
	}

	protected function _getOption($key, $value, $selected, $stylevalue, $classall, $classvalue = '') {
		if (!is_array($value)) {
			$_html_result = '<option class="' .$this->_htmlSpecialChars($classall).' '.$this->_htmlSpecialChars($classvalue)
				.'" style="' .$this->_htmlSpecialChars($stylevalue)
				.'" label="' .$this->_htmlSpecialChars($value)
				.'" value="' .$this->_htmlSpecialChars($key) . '"';
			
			if (!is_array($value) && in_array((string)$key, $selected)) {
				$_html_result .= ' selected="selected"';
			}

			$_html_result .= '>' . $this->_htmlSpecialChars($value) . '</option>' . chr(10);
		} else {
			$_html_result = Htmloptions::getOptGroup($key, $value, $selected, $stylevalue, $classall, $classvalue);
		}
		return $_html_result;
	}

	protected function _getOptGroup($key, $values, $selected, $stylevalue, $classall, $classvalue) {
		$style = '';
		if (!isset($stylevalue)) $style = '';
		elseif (!is_array($stylevalue)) $style = $stylevalue;
		elseif (isset($stylevalue[$key])) $style = $stylevalue[$key];
		$class = $classvalue;

		$optgroup_str = '<optgroup class="' .$this->_htmlSpecialChars($classall)
			.'" style="' .$this->_htmlSpecialChars($style)
			.'" label="' . $this->_htmlSpecialChars($key) . '">' . chr(10);
		foreach ($values as $key => $value) {
			if (isset($stylevalue[$key])) $style = $stylevalue[$key];
			if (isset($classvalue[$key])) $class = $classvalue[$key];
			$optgroup_str .= Htmloptions::getOption($key, $value, $selected, $style, $classall, $class); 
		}
		$optgroup_str .= "</optgroup>" . chr(10);
		return $optgroup_str;
	}

	protected function _htmlSpecialChars($string) {
		$returner = htmlspecialchars($string);
		$returner = preg_replace('|&amp;(#?\w+);|', '&$1;', $returner);
		return $returner;
	}
}
