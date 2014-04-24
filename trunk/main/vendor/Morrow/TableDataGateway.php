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


namespace Morrow;
use Morrow\Debug;
use Morrow\Factory;

class TableDataGateway {
	protected $_db;
	protected $_table;
	protected $_allowed_insert_fields = array();
	protected $_allowed_update_fields = array();

	public function filterFields($data, $allowed_fields) {
		return array_intersect_key($data, array_flip($allowed_fields));
	}

	public function get($conditions) {
		if (is_scalar($conditions)) $conditions = array('id' => $conditions);
		$where = $this->_createWhere($conditions);

		$sql = $this->_db->get("
			SELECT *
			FROM {$this->_table}
			WHERE {$where}
		", array_values($conditions));

		return $sql['RESULT'][0];
	}

	public function insert($data) {
		$data = $this->filterFields($data, $this->_allowed_insert_fields);
		$data['created_at'] = Factory::load('\datetime')->format('Y-m-d H:i:s');

		return $this->_db->insertSafe($this->_table, $data);
	}

	public function update($data, $conditions) {
		$data = $this->filterFields($data, $this->_allowed_update_fields);
		$data['updated_at'] = Factory::load('\datetime')->format('Y-m-d H:i:s');

		if (is_scalar($conditions)) $conditions = array('id' => $conditions);
		$where = $this->_createWhere($conditions);

		return $this->_db->updateSafe($this->_table, $data, "WHERE {$where}", array_values($conditions));
	}

	public function delete($conditions) {
		if (is_scalar($conditions)) $conditions = array('id' => $conditions);
		$where = $this->_createWhere($conditions);

		return $this->_db->delete($this->_table, "WHERE {$where}", array_values($conditions));
	}

	protected function _createWhere($conditions) {
		$where = 'id = ?';
		if (!is_scalar($conditions)) {
			$where = array();
			foreach ($conditions as $field => $value) {
				$where[] = $field . '=?';
			}
			$where = implode(' AND ', $where);
		}
		return $where;
	}
}
