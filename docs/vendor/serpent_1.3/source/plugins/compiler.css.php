<?php

/*
 * Project: Serpent - the PHP compiling template engine
 * Copyright (C) 2009 Christoph Erdmann
 * 
 * This library is free software; you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License as published by the Free Software Foundation; either version 2.1 of the License, or (at your option) any later version.
 * This library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more details.
 * You should have received a copy of the GNU Lesser General Public License along with this library; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110, USA
 */

class SerpentCompilerCss extends SerpentCompilerAbstract
    {
	protected $_placeholders = array();
	
	// creates the compiled template
    public function compile($source)
        {
		$time_start = microtime(true);
		
		$category_stack = array();
		$returner = '';
		
		// strip comments
		$source = preg_replace('=/\*.*?\*/=s', '', "\n".$source);
		
		preg_match_all('=
			@(constants|mixins) \s* \{ \s*
					(\{.*\})+
			\s* \}
		=isx', $source, $constants, PREG_SET_ORDER);
		//dump($constants);
		
		
		// get all selectors
		preg_match_all('=\s*\n(\s*)(.+?)\{(.*?)\}=s', $source, $expressions, PREG_SET_ORDER);
		//dump($expressions);
		
		foreach ($expressions as $expression)
			{
			$selectors = $this->_parse_selector($expression[2]);
			$attributes = $this->_parse_attributes( $expression[3] );
			
			// create new category
			$indent = strlen($expression[1]);
			
			// throw away indexes above indent
			$category_stack = array_slice($category_stack, 0, $indent+1);
			
			// now we have to combine all possible selectors
			$selector_new = array();
			$for_stack = array();
			foreach ($selectors as $i=>$selector)
				{
				// put actual selectors to stack
				if ($selector{0} == '&') $for_stack[] = substr($selector, 1);
				else $for_stack[] = ' '.$selector;
				}
			$category_stack[ $indent ] = $for_stack;
			$selector_new = $this->_getCombinations( $category_stack );
			$selector = implode(",\n", $selector_new);
			
			
			// implode attributes
			$values = array();
			foreach ($attributes as $a)
				{
				$values[] = $a['key'].': '.$a['value'];
				}
			
			$returner .= "$selector { ".implode('; ', $values)." }\n";
			}
		
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		$returner = "/* Processed by Serpent Template Engine (CSS Compiler) on ".date('r')." in ".$time." seconds */\n\n" . $returner;

		dump($returner);
		return $returner;
		}

	protected function _parse_selector($selector)
		{
		$selector = trim($selector);
		
		// filter unnecessary whitespace
		$selector = preg_replace("=\s{1,}=", ' ', $selector);
		
		// split comma separated selectors
		$selector = preg_split("=,\s*=", $selector);
		
		return $selector;
		}

	protected function _parse_attributes($line)
		{
		// split attributes
		$attributes_count = preg_match_all("=([a-z-\s]+)\:(.+?)(;|$)=s", $line, $attribute, PREG_SET_ORDER);
		
		foreach ($attribute as $parts)
			{
			$key = trim( $parts[1] );
			$value = trim( $parts[2] );
			
			// replace placeholders
			if (isset( $this->_placeholders[$key] ))
				{
				$vars =& $this->_placeholders[$key];
				foreach ($vars as $vkey=>$vvalue)
					{
					$value = str_replace('<'.$vkey.'>', $vvalue, $value);
					}
				}
			
			$attributes[] = array('key' => $key, 'value' => $value);
			}
		
		return $attributes;
		}
	
	protected function _getCombinations($params = array(), $reset = true)
		{
		$combinations = array();
		$break  = true;
	
		foreach($params as $k => $v)
			{
			if(count($v) > 1)
				{
				$break = false;
	
				foreach ($v as $v2)
					{
					$params[$k] = array($v2);
					$combinations = array_merge($combinations, $this->_getCombinations($params, false));
					}
				break;
				}
			}
	
		if ($break)
			{
			foreach($params as $k => $v)
				{
				$params[$k] = $v[0];
				}
			$combinations[] = join('', $params);
			}
		
		return $combinations;
		}
	}
