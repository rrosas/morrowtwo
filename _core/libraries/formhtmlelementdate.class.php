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






class formhtmlelementDate extends formhtmlelement{

	public function getDisplay($name, $values, $id, $params, $options, $multiple){
		$date_str = '';
                if(is_array($values)) {
			$date_str = sprintf("%s-%s-%s %s:%s:%s", $values['_Year'],  $values['_Month'], $values['_Day'], $values['_Hour'], $values['_Min'], $values['_Sec']);
		}
                else $date_str = $values;

                $format = "%d%m%Y";
                $start_year = null;
                $end_year = null;
                if(isset($params['format'])) $format = $params['format'];
                if(isset($params['start_year'])) $start_year = $params['start_year'];
                if(isset($params['end_year'])) $end_year = $params['end_year'];

                $content = "<span class=\"date\">";
                $content .= HelperHtmlDate::getOutput($name, $date_str, $format, $start_year, $end_year, $params);
                $content .= "</span>";
                return $content;
	}	

	public function getReadonly($name, $values, $id, $params, $options, $multiple){
		$date_str = '';
                if(is_array($values)) $date_str = sprintf("%s-%s-%s %s:%s:%s", $values['_Year'],  $values['_Month'], $values['_Day'], $values['_Hour'], $values['_Min'], $values['_Sec']);
                else $date_str = $values;
		$content = '<input type="hidden" name="' . $name . '" value="' . $date_str . '" />';

		$format = "%d/%m/%Y";
                if(isset($params['format'])) $format = $params['format'];

		if(preg_match('=^0000=',$date_str)){
			$content .= '-';
		}
                else $content .= strftime($format, strtotime($date_str));
                return $content;

	}	

	public function getListDisplay($values, $params,$options=array()){

		$format = "%d%m%Y";
                if(isset($params['format'])) $format = $params['format'];

		$date_str = $values;
		if(preg_match('=^0000=',$date_str)){
			$content = '-';
		}
                else $content = strftime($format, strtotime($date_str));
                return $content;

	}




}
