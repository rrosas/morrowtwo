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


namespace Morrow\Helpers;

class HtmlFormAttributes {
	static protected $global_attributes = array('id', 'class', 'style', 'title', 'dir', 'lang', 'onclick', 'ondblclick', 'onmousedown', 'onmouseup', 'onmouseover', 'onmousemove', 'onmouseout', 'onkeypress', 'onkeydown', 'onkeyup', 'autofocus');
	static protected $attributes = array(
		"input" => array('accept', 'accesskey', 'align', 'alt', 'checked', 'disabled', 'ismap', 'maxlength', 'name', 'onblur', 'onchange', 'onfocus', 'onselect', 'readonly', 'size', 'src', 'tabindex', 'type', 'usemap', 'value'),
		"textarea" => array('accesskey', 'cols', 'disabled', 'name', 'onblur', 'onchange', 'onfocus', 'onselect', 'readonly', 'rows','tabindex'),
		"select" => array('disabled', 'multiple', 'name', 'onblur', 'onchange', 'onfocus', 'size', 'tabindex'),
		"option" => array('disabled', 'label', 'selected', 'value'),
		"label" => array('accesskey', 'for', 'onblur', 'onfocus'),
		"legend" => array('accesskey', 'align'),
		"fieldset" => array(),
	);

	static public function filterAttributes($tagname, $attributes) {
		$tagname = strtolower($tagname);
		foreach ($attributes as $key => $value) {
			$attr = strtolower($key);
			if (!in_array($attr, self::$global_attributes)) {
				if (!isset(self::$attributes[$tagname]) || !in_array($attr, self::$attributes[$tagname])) {
					unset($attributes[$key]);
				}
			}
		}
		return $attributes;
	}

	static public function getAttributeString($attributes, $tagname = null, $filter = true) {
		// special case for textarea which needs cols and rows to be valid
		if ($tagname == 'textarea') {
			$attributes = array_merge(array('cols' => 40, 'rows' => '10'), $attributes);
		}
		
		if ($filter && !is_null($tagname)) $attributes = self::filterAttributes($tagname, $attributes);

		$attr_sets = array();
		foreach ($attributes as $pk => $pv) {
			$attr_sets[] = "$pk=\"$pv\"";
		}
		$attributes = implode(" ", $attr_sets);
		return $attributes;
	}
}
