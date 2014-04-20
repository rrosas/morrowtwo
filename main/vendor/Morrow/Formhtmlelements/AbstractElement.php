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

abstract class AbstractElement {
	protected $page;
	protected $_global_attributes = array('id', 'class', 'style', 'title', 'dir', 'lang', 'onclick', 'ondblclick', 'onmousedown', 'onmouseup', 'onmouseover', 'onmousemove', 'onmouseout', 'onkeypress', 'onkeydown', 'onkeyup', 'autofocus');
	protected $_attributes = array(
		"input" => array('accept', 'accesskey', 'align', 'alt', 'checked', 'disabled', 'ismap', 'maxlength', 'name', 'onblur', 'onchange', 'onfocus', 'onselect', 'readonly', 'size', 'src', 'tabindex', 'type', 'usemap', 'value'),
		"textarea" => array('accesskey', 'cols', 'disabled', 'name', 'onblur', 'onchange', 'onfocus', 'onselect', 'readonly', 'rows','tabindex'),
		"select" => array('disabled', 'multiple', 'name', 'onblur', 'onchange', 'onfocus', 'size', 'tabindex'),
		"option" => array('disabled', 'label', 'selected', 'value'),
		"label" => array('accesskey', 'for', 'onblur', 'onfocus'),
		"legend" => array('accesskey', 'align'),
		"fieldset" => array(),
	);

	public function __construct() {
		$this->page = \Morrow\Factory::load('Page');
	}

	public function getLabel($value, $for_id, $params) {
		return "<label for=\"" . $for_id . "\" " . $this->_getAttributeString($params, 'label') . ">$value</label>";
	}
	public function getError($value, $params, $tagname) {
		return "<$tagname " . $this->_getAttributeString($params, $tagname) . ">$value</$tagname>";
	}

	abstract public function getDisplay($name, $values, $id, $params, $options, $multiple);
	abstract public function getReadonly($name, $values, $id, $params, $options, $multiple);

	public function getListDisplay($values, $params, $options = array()) {
		return $values;
	}

	protected function _truncate($string, $length = 80, $etc = '...') {
		if ($length == 0) return '';

		if (strlen($string) > $length) {
			$length -= min($length, strlen($etc));
			$string = preg_replace('/\s+?(\S+)?$/', '', substr($string, 0, $length+1));
			return substr($string, 0, $length) . $etc;
		} else {
			return $string;
		}
	}

	protected function _filterAttributes($tagname, $attributes) {
		$tagname = strtolower($tagname);
		foreach ($attributes as $key => $value) {
			$attr = strtolower($key);
			if (!in_array($attr, $this->_global_attributes)) {
				if (!isset($this->_attributes[$tagname]) || !in_array($attr, $this->_attributes[$tagname])) {
					unset($attributes[$key]);
				}
			}
		}
		return $attributes;
	}

	protected function _getAttributeString($attributes, $tagname = null, $filter = true) {
		// special case for textarea which needs cols and rows to be valid
		if ($tagname == 'textarea') {
			$attributes = array_merge(array('cols' => 40, 'rows' => '10'), $attributes);
		}
		
		if ($filter && !is_null($tagname)) $attributes = $this->_filterAttributes($tagname, $attributes);

		$attr_sets = array();
		foreach ($attributes as $pk => $pv) {
			$attr_sets[] = "$pk=\"$pv\"";
		}
		$attributes = implode(" ", $attr_sets);
		return $attributes;
	}
}
