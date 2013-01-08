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


namespace Morrow\Core\Libraries;

class formhtmlelementGroup extends formhtmlelement{

	public function getLabel($value, $for_id, $params){
		return "<legend " .  HelperHtmlFormAttributes::getAttributeString($params, 'legend') . ">$value</legend>";
	}

	public function getDisplay($name, $values, $id, $params, $options, $multiple){	
		$el_name = $name;
		$output = array_values($options);
		$options = array_keys($options);
		$separator_start = '';
		$separator_end = '';
		if(isset($params['separator'])){
			$sep = explode("|",$params['separator']);
			$separator_end = $sep[0];
			if(isset($sep[1])) {
				$separator_start = $sep[0];
				$separator_end = $sep[1];
			}
		}
		$cnt=0;
		$content = '';
		foreach($options as $ok=>$ov){
			$checked = "";
			$type = "radio";
			$idname = str_replace('[', '_', $name);
			$idname = str_replace(']', '', $idname);
			#checkboxes
			if($multiple){
				$opt_id = "mw_" . $idname . "_" . $ok;
				$el_name = $name . "[" . $ok . "]";
				if(is_array($values) && in_array($ov, $values)) $checked = ' checked="checked"';
				$type = "checkbox";
			}
			#radios
			else{
				$opt_id = "mw_" . $idname . "_" . $cnt++;
				if(!is_null($values) && $values == $ov) $checked = ' checked="checked"';
			}
			$label = "<label for=\"" . $opt_id . "\">" . htmlspecialchars($output[$ok]) . "</label>";
			$content .= $separator_start;
			if(isset($params['prepend'])) $content .= $label; 
			$content .= "<input id=\"" . $opt_id . "\" type=\"" . $type . "\" name=\"" . $el_name . "\" value=\"" . str_replace('"','&quot;',$ov) . "\" $checked " .  HelperHtmlFormAttributes::getAttributeString($params, 'input') . " />";
			if(!isset($params['prepend'])) $content .= $label; 
			$content .= $separator_end;
		}
			if(isset($params['height'])){
				$content = '<div style="height:' . $params['height'] . ';overflow:auto;">' . $content . '</div>';
			}
			return $content;
		
	}

	public function getReadonly($name, $values, $id, $params, $options, $multiple){
		if($multiple){ 
			$name .= "[]";
		}
		$content = '';
		foreach($values as $value){
			$content .= '<input type="hidden" name="'.$name.'" value="'.$value.'">';
			$content .= '<div '. HelperHtmlFormAttributes::getAttributeString($params, 'div') .'>'.htmlspecialchars($value, ENT_QUOTES, $this->page->get('charset')).'</div>';
		}
                return $content;
	}	

	public function getListDisplay($values, $params, $options = array()){
		$content = '';
		if(is_array($values)){
			$tmp_content = array();
			foreach($values as $value){
				$tmp_content[] = isset($options[$value]) ? htmlspecialchars($options[$value], ENT_QUOTES, $this->page->get('charset')) : htmlspecialchars($value, ENT_QUOTES, $this->page->get('charset'));
			}
			#$content = implode(', ', $tmp_content);
			$content = '<ul><li>' . implode('</li><li>', $tmp_content) . '</li></ul>';
		}
		else{
			$content = isset($options[$values]) ? $options[$values] : $values;
			$content = htmlspecialchars($content, ENT_QUOTES, $this->page->get('charset'));
		}
		return $content;
	}	


}
