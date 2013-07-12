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


namespace Morrow\Filters;

use Morrow\Factory;

class Sitesearch extends AbstractFilter {
	// some new properties
	public $buildindex			= true;

	public $exclude_patterns	= array();
	public $tag_include_start	= '<!-- search_include_start -->';
	public $tag_include_end		= '<!-- search_include_end -->';
	public $tag_exclude_start	= '<!-- search_exclude_start -->';
	public $tag_exclude_end		= '<!-- search_exclude_end -->';
	
	public $check_divisor		= 10;
	public $gc_divisor			= 100; // on default the garbagecollection will be used every 1000 times because of the check_divisor
	public $entry_lifetime		= '+1 month'; // when a page is not recrawled once a month it will get kicked out of the index
	
	public $db_config			= array();

	protected $db_tablename		= 'searchdata';

	public function __construct($config = array()) {
		$this->db_config = array(
			'driver' => 'sqlite',
			'file' => APP_PATH .'temp/sitesearch.sqlite',
			'host' => 'localhost',
			'db' => 'sitesearch_searchengine',
			'user' => 'root',
			'pass' => ''
		);
		
		// apply config
		foreach ($config as $key => $value) {
			$this->$key = $value;
		}
	}
	
	public function get($content) {
		// get output from standard handler
		$original_output = $content;

		// deactivate index building
		if (!$this->buildindex) return $original_output;

		// check only sometimes
		if (rand(1, $this->check_divisor) !== 1) return $original_output;
		
		$output = $original_output;

		// load needed classes
		$this->url = Factory::load('Url');
		$this->searchdb		= Factory::load('Db:searchdb', $this->db_config);

		// connect to DB and do maintenance
		$this->searchdb->connect();
		$this->_createTableIfNotExists($this->db_tablename);
		if (rand(1, $this->gc_divisor) === 1) $this->_deleteOldEntries($this->db_tablename);
				
		// create url for current page
		$url = $this->url->create('');

		//$this->_check($url);
		
		
		// ##### check checksum #####
		// now check if we have to refresh the database entry
		$md5 = md5($output);
		$result = $this->searchdb->result("
			SELECT checksum
			FROM ".$this->db_tablename."
			WHERE url = ?
		", $url);
		if (isset($result['RESULT'][0]['checksum'])) {
			$checksum = $result['RESULT'][0]['checksum'];
		}
		else $checksum = 1;
		if ($checksum === $md5) {
			// touch the current entry
			$this->_touchEntry($this->db_tablename, $url);
			return $original_output;
		}

		// ##### create Index #####
		
		// strip the excludes
		$regex = preg_quote($this->tag_exclude_start).'(.+?)'.preg_quote($this->tag_exclude_end);
		$output = preg_replace('='.$regex.'=s', '', $output);
		
		// now we take all content between the include tags
		$regex = preg_quote($this->tag_include_start).'(.+?)'.preg_quote($this->tag_include_end);
		$found = preg_match_all('='.$regex.'=s', $output, $matches, PREG_PATTERN_ORDER);
		if ($found === 0) return $original_output;

		// put all results in a string
		$output = implode(' ', $matches[1]);
		
		// create text version of content
		$output = strip_tags($output);

		// Replace useless space
		$output = preg_replace('=([ \t\r\n]|&nbsp;)+=i', ' ', $output);
		
		// replace entities for better search results
		$output = html_entity_decode($output);

		// first we take the title tag
		$found = preg_match('=<title>(.+?)</title>=is', $original_output, $match);
		if ($found === 0) $title = '';
		else $title = html_entity_decode($match[1]);

		// save to database
		$replace['url']			= $url;
		$replace['checksum']	= $md5;
		$replace['title']		= $title;
		$replace['searchdata']	= $output;
		$replace['bytes']		= strlen($original_output);
		$replace['changed']		= array('FUNC' => "datetime('now')");
		
		// execute exclude patterns
		$save_to_db = true;
		foreach ($this->exclude_patterns as $patterns) {
			foreach ($patterns as $field => $pattern) {
				if (preg_match($pattern, $replace[$field])) {
					$save_to_db = false;
					break;
				}
			}
		}
		
		// now save
		if ($save_to_db) {
			$this->searchdb->replace($this->db_tablename, $replace);
			$this->searchdb->query("VACUUM");
		}
		
		// output the original content
		return $original_output;
	}

	protected function _createTableIfNotExists($table) {
		$this->searchdb->query("
			CREATE TABLE IF NOT EXISTS `".$table."` (
			`url` char(255) NOT NULL,
			`checksum` char(32) NOT NULL,
			`title` char(255) NOT NULL,
			`searchdata` text NOT NULL default CURRENT_TIMESTAMP,
			`touched` timestamp NOT NULL default CURRENT_TIMESTAMP,
			`changed` timestamp NOT NULL,
			`bytes` int(11) NOT NULL,
			PRIMARY KEY  (`url`)
			);
		");
	}
	
	protected function _deleteOldEntries($table) {
		$sql = $this->searchdb->exec("
			DELETE FROM {$table}
			WHERE datetime(touched, '{$this->entry_lifetime}') < datetime('now');
		");
		$this->searchdb->query("VACUUM");
	}
		
	protected function _touchEntry($table, $url) {
		$updates['touched'] = array('FUNC' => "datetime('now')");
		$this->searchdb->update($table, $updates, "WHERE url = '{$url}'");
	}

	protected function _check($url) {
		$sql = $this->searchdb->result("
			SELECT *
			FROM {$this->db_tablename}
			WHERE url = ?
		", $url);
		dump($sql);
	}
}
