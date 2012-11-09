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





class FilterValidate extends FilterAbstract {
	protected $filter = null;
	
	public function __construct( $filter ) {
		$this->filter = $filter;
	}
	
	public function get($content) {
		$original = $content;

		// filter
		if (!is_null($this->filter)) {
			$start = preg_quote($this->filter[0]);
			$end = preg_quote($this->filter[1]);
			
			$content = preg_replace('='.$start.'.*?'.$end.'=s', '', $content);
		}
		
		// get xml errors
		$oldSetting = libxml_use_internal_errors( true );
		libxml_clear_errors(); 
		
		$html = new DOMDocument(); 
		$html->validateOnParse = true;
		$html->loadXML( $content ); 
		$errors = libxml_get_errors();
		
		libxml_clear_errors();
		libxml_use_internal_errors( $oldSetting ); 

		// return with no errors
		if (count($errors) == 0) return $original;
		
		// output
		$lines = preg_split('=(\r\n|\r|\n)=', $content);
		
		$output = '';
		foreach ($errors as $error) {
			$line = htmlentities($lines[$error->line-1]);
			
			$output .= '<div style="background-color: #ffc; border: 1px solid #f00; padding: 10px; margin: 5px; font: normal 12px/15px \'Courier New\'; color: #000;">';
			$output .= $error->message .'<br />';
			$output .= 'Line '.$error->line.': '.$line;
			$output .= '</div>';
		}
		
		return $output . $original;
	}
}
