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

class Navigation {
	protected $nodes, $tree = array();
	protected $active_id = null;
	
	public function __construct($data = null) {
		if (is_null($data)) {
			// get simple tree from language class
			$language = Factory::load('language');
			$data = $language->getTree();

			// fills $nodes and $tree
			$this->add($data);

			// set active page
			$page = Factory::load('page');
			$this->setActive($page->get('alias'));
		} else {
			// fills $nodes and $tree
			$this->add($data);
		}
	}
		
	public function add($data, $branch = null) {
		if (!is_null($branch)) $data = array($branch => $data);
		
		foreach ($data as $branch => $tree) {
			// first create the flat tree
			foreach ($tree as $id => $node) {
				// its ok just to pass a string as title
				if (is_string($node)) $node = array('title' => $node);
				
				if (!isset($node['title']) or empty($node['title'])) {
					throw new Exception(__CLASS__ . "': You have to define a title for id '{$id}'.");
				}
				
				// add other information
				$node['active'] = false;
				
				// set alias if not already set (maybe data came from db)
				if (!isset($node['alias'])) $node['alias'] = $id;
				
				$parts = explode('_', $node['alias']);
				$node['path'] = implode('/', $parts) . '/';
				$node['node'] = array_pop($parts);
				$node['parent'] = implode('_', $parts);
				
				// add to nodes collection
				$this->nodes[$node['alias']] = $node;

				// add to nested tree
				if (empty($node['parent'])) {
					$this->tree[$branch][$id] =& $this->nodes[$id];
				}
			}
		}

		$nodes =& $this->nodes;
		
		// now create the references in between
		foreach ($nodes as $id => $node) {
			// add as child to parent
			if (isset($nodes[$node['parent']])) {
				$nodes[$node['parent']]['children'][$id] =& $nodes[$id];
			}
		}
	}

	public function setActive($id) {
		if (!isset($this->nodes[$id])) { throw new Exception(__METHOD__.': id "'.$id.'" does not exist.'); return; }
		
		// set active id to retrieve the breadcrumb
		$this->active_id = $id;
		
		// set all nodes to inactive
		foreach ($this->nodes as $key => $item) {
			$this->nodes[$key]['active'] = false;
		}
		
		// set actual node to active
		$actual =& $this->nodes[$id];
		$actual['active'] = true;
		
		// loop to the top and set to active
		while (isset($this->nodes[$actual['parent']])) {
			$actual =& $this->nodes[$actual['parent']];
			$actual['active'] = true;
		}
		
		// return actual node
		return $this->nodes[$id];
	}

	public function getActive() {
		return $this->get($this->active_id);
	}

	// get full tree or specific id
	public function get($id = null) {
		// return full tree
		if (is_null($id)) return $this->tree;

		if (!isset($this->nodes[$id])) { throw new Exception(__METHOD__.': id "'.$id.'" does not exist.'); return; }
		return $this->nodes[$id];
	}

	// get full tree or the first found node by field
	public function find($field, $id) {
		// return node by user defined field
		foreach ($this->nodes as $node) {
			if (isset($node[$field]) && $node[$field] == $id) return $node;
		}
		return null;
	}

	// get breadcrumb (tree up to actual page)
	public function getBreadcrumb() {
		$breadcrumb = array();
		
		// handle not set active node
		if (!isset($this->nodes[$this->active_id])) { throw new Exception(__METHOD__.': you did not set an active node so you cannot retrieve a breadcrumb.'); return; }
		
		// get actual node
		$actual = $this->nodes[$this->active_id];
		array_unshift($breadcrumb, $actual);
		
		// loop to the top
		while (isset($this->nodes[$actual['parent']])) {
			$actual =& $this->nodes[$actual['parent']];
			array_unshift($breadcrumb, $actual);
		}
		
		return $breadcrumb;
	}
}
