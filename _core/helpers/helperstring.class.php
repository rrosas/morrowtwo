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





class helperString
	{
	public static function truncate($string, $length = 80, $etc = '...', $break_words = false, $middle = false)
		{
		if ($length == 0) return '';

		if (strlen($string) > $length)
			{
			$length -= min($length, strlen($etc));
			if (!$break_words && !$middle)
				{
				$string = preg_replace('/\s+?(\S+)?$/', '', substr($string, 0, $length+1));
				}
			if(!$middle)
				{
				return substr($string, 0, $length) . $etc;
				}
			else
				{
				return substr($string, 0, $length/2) . $etc . substr($string, -$length/2);
				}
			}
		else
			{
			return $string;
			}
		}

	public static function excerpt($text, $phrase, $radius = 100, $etc = "...")
		{
		$textlength = strlen($text);

		// find the positions of all phrase words
		$phrases = explode(' ', $phrase);
		$phrases_regex = implode('|', array_map('preg_quote', $phrases));

		// get all the positions of the search words in the text
		$found = preg_match_all('='.$phrases_regex.'=i', $text, $matches, PREG_OFFSET_CAPTURE );
		$matches = $matches[0];
		
		// if phrase words were not found return the start of the page
		// useful on search results if you have a match in the url but no in the page text you want to show
		if (!$found)
			{
			$output = array();
			$output['excerpt'] = substr($text, 0, $radius*2);
			$output['weight'] = 0;
			return $output;
			}
				
		// get all positions and counts of search words within the text
		$tmp_positions = array();
		foreach ($matches as $match)
			{
			$tmp_positions[] = $match[1];
			$word = strtolower($match[0]);
			}
		
		// include all positions within the radius
		// take care that smaller radius has to be added to the other site
		$positions = array();
		foreach ($tmp_positions as $pos)
			{
			$start = ($pos-$radius < 0) ? 0 : $pos-$radius;
			$end = ($pos+$radius >= $textlength-1) ? $textlength-1 : $pos+$radius;
			$positions[] = array( 'start' => $start, 'end' => $end );
			}
		
		//dump($positions);

		// combine overlapping ranges
		$newpositions = array();
		$count = count($positions);
		
		for ($i=0; $i<$count; $i++)
			{
			$curr =& $positions[$i];
			$next =& $positions[$i+1];
			
			if (!is_null($next) && $curr['end'] > $next['start'])
				{
				$next['start'] = $curr['start'];
				unset($curr);
				}
			else
				{
				$newpositions[] = $curr;
				}
			}
		$positions = $newpositions;
		
		//dump($newpositions);


		$output = array();

		// calc weight of full text to return the relevance
		$text_lower = strtolower($text);
		foreach ($phrases as $phrase)
			{
			$phrase = strtolower($phrase);
			$weight[$phrase] = substr_count($text_lower, $phrase);
			}
		$output['weight'] = array_product($weight);
		
		
		// iterate all excerpts
		$highest_weight = 0;
		foreach ($positions as $pos)
			{
			$string = substr($text, $pos['start'], ($pos['end']-$pos['start']));
			if ($pos['start'] !== 0) $string = $etc.$string;
			if ($pos['end'] !== $textlength-1) $string .= $etc;
			
			// calc weight of this excerpt
			$weight = array();
			$extract = strtolower($string);
			foreach ($phrases as $phrase)
				{
				$phrase = strtolower($phrase);
				$weight[$phrase] = substr_count($extract, $phrase)+1;
				}
			$weight = array_product($weight);
			if ($weight > $highest_weight) $output['excerpt'] = $string;
			}
		return $output;
		}

	public static function htmlSpecialChars($string)
		{
		$returner = htmlspecialchars($string);
		$returner = preg_replace('|&amp;(#?\w+);|', '&$1;', $returner);
		return $returner;
		}

	// "encoded-word" encoding
	// http://tools.ietf.org/html/rfc2047
	// MIME (Multipurpose Internet Mail Extensions) Part Three: Message Header Extensions for Non-ASCII Text
	public static function encodeEncodedWord($string, $encoding = 'utf-8', $length = 75)
		{
		if (empty($string)) return '';
		
		$intro = '=?' . $encoding . '?q?';
		$ending = '?=';
		$divider = "\r\n ";
		
		$text = self::encodeQuotedPrintable($string, $length - strlen($intro . $ending), true);
		if ($text == $string) return $text;
		$text = str_replace(' ', '_', $text);
		$strings = preg_split("-(=\r\n|\r\n)-", $text);
		
		$returner = $intro . implode($ending.$divider.$intro, $strings) . $ending;
		return $returner;
		}

	public static function encodeQuotedPrintable($string, $line_width = 75, $encoded_word = false)
		{
		// generate array with ascii codes to encode
		for ($i = 1; $i < 255; $i++)
			{
			if ($i == 9) continue; // tab
			if ($i == 10) continue; // lf
			if ($i == 13) continue; // cr
			if ($i == 32) continue; // space
			if ($i >= 33 and $i <= 60) continue; // literal representation
			if ($i >= 62 and $i <= 126) continue; // literal representation
			
			$convert[] = $i;
			}
		
		// in case of "encoded word" we have to encode some more characters
		if ($encoded_word)
			{
			$convert[] = ord('_');
			$convert[] = ord('=');
			$convert[] = ord('?');
			}
		
		// iterate each line of input array 
		$returner = array();
		$strings = preg_split("=(\r\n|\r|\n)=", $string);
		foreach ($strings as $string)
			{
			$line = '';
			$count_line = 0;
			$count_string = strlen($string);

			for ($i = 0; $i < $count_string; $i++)
				{
				$char = $string{$i};
				$ord = ord($char);
			
				// we have a character which needs encoding
				if (in_array($ord, $convert))
					{
					if ($count_line + 3 > $line_width)
						{
						$line .= '=';
						$returner[] = $line;
						$line = '';
						$count_line = 0;
						}
				
					$line .= sprintf("=%02X", $ord);
					$count_line += 3;
					}
				// we have a character which doesn't encoding
				else
					{
					if ($count_line == $line_width)
						{
						$line .= '=';
						$returner[] = $line;
						$line = '';
						$count_line = 0;
						}

					$line .= $char;
					$count_line++;
					}
				}

			$returner[] = $line;
			}
		
		$returner = implode("\r\n", $returner);
		return $returner;
		}
	}
