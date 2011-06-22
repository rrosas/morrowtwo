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




class ViewXml extends ViewAbstract
	{
	public $mimetype	= 'application/xml';
	public $charset		= 'UTF-8';
	
	public $numeric_prefix	= 'entry';
	public $strip_tag		= ' ';
	public $attribute_tag	= ':';

	public function getOutput($content, $handle)
		{
		fwrite($handle, '<?xml version="1.0" encoding="'.$this->charset.'"?>');
		
		// count subkeys. if there is more than one we have to generate an auto container
		// we have to take care of attributes
		$count = 0;
		foreach ($content['content'] as $key=>$item)
			{
			if ($key{0} != ':') $count++;
			}
		
		if ($count != 1) $content['content'] = array('auto-container' => $content['content']);
		fwrite($handle, $this -> _outputXML($content['content']));
		return $handle;
		}

	private function _outputXML($input)
		{
		$attributes = '';
		$output = '';
		
		// get attributes
		foreach($input as $key=>$value)
			{
			$attribute = array();

			if ($key[0] == $this->attribute_tag)
				{
				$newkey = substr($key, 1);
				foreach ($value as $pkey=>$pvalue)
					{
					$attribute[] = $pkey.'="'.htmlspecialchars($pvalue).'"';
					}
				$attributes[$newkey] = implode(' ', $attribute);
				unset($input[$key]);
				}
			}

		// run through all keys
		foreach($input as $key=>$value)
			{
			// set attribs
			if (isset($attributes[$key])) $attr = ' '.$attributes[$key]; else $attr = '';

			// process numeric indices
			if (is_numeric($key)) $key = $this->numeric_prefix.$key;

			if (is_array($value) && count($value) > 0)
				{
				$body = $this -> _outputXML($value);
				}
			else
				{
				// process value
				if (is_array($value)) $value = '';
				elseif ($value === true) $value = 'true';
				elseif ($value === false) $value = 'false';
				elseif ($value === null) $value = 'null';
				else
					{
					$value = trim(stripslashes($value));

					if (strpos($value, '<') !== false OR strpos($value, '>') !== false OR strpos($value, '&') !== false)
						{
						$value = '<![CDATA['.$value.']]>';
						}
					}

				$body = $value;
				}

			// process equal named tags
			$key = explode($this->strip_tag, $key);
			$key = $key[0];

			// Start-Tag setzen
			if (!isset($output)) $output = '';
			$output .= '<'.$key.$attr.'>';
			$output .= $body;
			// End-Tag setzen
			$output .= '</'.$key.'>';
			}
		return $output;
		}
	}
