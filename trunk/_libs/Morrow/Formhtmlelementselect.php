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

class FormhtmlelementSelect extends Formhtmlelement {
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
		$content = "<select id=\"$id\" name=\"$name\"  " . HelperHtmlFormAttributes::getAttributeString($params, 'select') . " $multiplestr>" . chr(10);
		$content .= HelperHtmlOptions::getOutput('', $keys, $output, $values, $class, $styles, $classes);

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
				$content .= '<div '. HelperHtmlFormAttributes::getAttributeString($params, 'div') .'>'.$options[$value].'</div>';
			}
		} else {
			$value = $values;
			if (isset($options[$values])) $value = $options[$values];
			$content .= '<input type="hidden" name="'.$name.'" value="'.$values.'" />';
			$content .= '<div '. HelperHtmlFormAttributes::getAttributeString($params, 'div') .'>'.$value.'</div>';
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
}
