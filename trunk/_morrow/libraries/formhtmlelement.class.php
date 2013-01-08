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


namespace Morrow\Libraries;

abstract class formhtmlelement{
	protected $page;	

	public function __construct(){
		$this->page = \Morrow\Factory::load('Morrow\Libraries\page');
	}

	public function getLabel($value, $for_id, $params){
		return "<label for=\"" . $for_id . "\" " . HelperHtmlFormAttributes::getAttributeString($params, 'label') . ">$value</label>";
	}
	public function getError($value, $params, $tagname){
		 return "<$tagname " . HelperHtmlFormAttributes::getAttributeString($params, $tagname) . ">$value</$tagname>";
	}

	abstract public function getDisplay($name, $values, $id, $params, $options, $multiple);	
	abstract public function getReadonly($name, $values, $id, $params, $options, $multiple);	

	public function getListDisplay($values, $params, $options=array()){
		return $values;
	}	


}
