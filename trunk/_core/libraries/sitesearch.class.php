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

class Sitesearch
	{
	private $contextradius = 50;
	private $limit = 10;
	
	public function __construct($config = array())
		{
		$this->db_config = array(
			'driver' => 'sqlite',
			'file' => PROJECT_PATH.'temp/_sitesearch.sqlite',
			'host' => 'localhost',
			'db' => 'sitesearch_searchengine',
			'user' => 'root',
			'pass' => ''
		);

		// apply config
		foreach ($config as $key=>$value)
			{
			$this->$key = $value;
			}

		$this->db = Factory::load('db:dbsitesearch', $this->db_config);
		$this->bm = Factory::load('benchmark:benchmarksitesearch');
		}

	public function getAll($where = '')
		{
		$results = $this->db->Result("
			SELECT url,title,searchdata,bytes,strftime('%s', changed) as changed, *
			FROM searchdata
			".$where."
		");
		
		$returner = $results['RESULT'];
		return $returner;
		}
		
	public function get($q)
		{
		// start timer
		$start = microtime(true);
		
		// clean query
		$q = $this->_cleanQuery($q);
		$q2 = explode(' ', $q);
		$q_count = count($q2);
		
		foreach ($q2 as $key=>$value)
			{
			$q2[$key] = '%'.$value.'%';
			}

		$replacements = array();
		for ($i=0; $i<3; $i++)
			{
			$replacements = array_merge($replacements, $q2);
			}
		array_push($replacements, $this->limit);
		
		$results = $this->db->Result("
			SELECT url,title,searchdata,bytes,strftime('%s', changed) as changed
			FROM searchdata
			WHERE
				".str_repeat('title LIKE ? OR ', $q_count)."
				".str_repeat('url LIKE ? OR ', $q_count)."
				".str_repeat('searchdata LIKE ? OR ', $q_count)."
			1=0
			LIMIT ?
		", $replacements);
		
		$returner['time'] = microtime(true)-$start;
		$returner['data'] = $this->_prepare($q, $results);
		return $returner;
		}
		
	private function _prepare($q, $results)
		{
		if (!isset($results['RESULT'][0])) return;

		$phrases = explode(' ', $q);
		foreach ($results['RESULT'] as $key=>$result)
			{
			$new =& $results['RESULT'][$key];
			
			$raw = helperString::excerpt($result['searchdata'], $q, $this->contextradius);
			$new['searchdata']	= htmlspecialchars($raw['excerpt']);
			$new['relevance']	= $raw['weight'];

			$new['url']			= htmlspecialchars($result['url']);
			$new['title']		= htmlspecialchars($result['title']);
			
			// hits in url and url should lead to higher relevance
			$extract = strtolower($result['title'].' '.$result['url']);
			$weight = array();
			foreach ($phrases as $phrase)
				{
				$phrase = strtolower($phrase);
				$weight[$phrase] = substr_count($extract, $phrase)+1;
				}
			$weight = array_product($weight)*1.5;

			$new['relevance']	+= $weight;
			}

		// sort after weight
		$weight = array();
		foreach ($results['RESULT'] as $key => $row)
			{
			$weight[$key]  = $row['relevance'];
			}

		array_multisort($weight, SORT_DESC, $results['RESULT']);
		return $results;
		}

	// stopword lists: http://www.ranks.nl/tools/stopwords.html
	private function _cleanQuery($q)
		{
		$q = trim($q);
		$q = preg_replace('|\s+|', ' ', $q); // strip whitespace
		$words = explode(' ', $q);

		// exclude all words shorter than 2 chars
		foreach ($words as $key=>$word) {
			if (strlen($word) < 3) unset ($words[$key]);
		}

		// stopwords longer than 2 chars
		$stopwords = array('about', 'and', 'are', 'com', 'for', 'from', 'how', 'she', 'that', 'the', 'this', 'was', 'what', 'when', 'where', 'who', 'will', 'with', 'the', 'www');
		$words = array_diff($words, $stopwords);
		
		$q = implode(' ', $words);
		return $q;
		}

	}
