<?php
class Model {

	public static $dbh;
	public $table_name;
	public $primary_key;
	public $table_columns;
	public $table_columns_types;
	public $table_columns_sizes;

	public $has_one;
	public $has_many;
	public $belongs_to;
	public $has_many_and_belongs_to;

	public function __construct($model_name) {
		include (FIREFLY_BASE_DIR . DS . 'config' . DS . 'database.php');
		$model_settings = get_class_vars($model_name);
		$this->dbh = call_user_func(array (
			ucfirst($config['db'][ENVIRONMENT]['adapter']),
			'establish_connection'
		), $config['db'][ENVIRONMENT]['host'], $config['db'][ENVIRONMENT]['username'], $config['db'][ENVIRONMENT]['password'], $config['db'][ENVIRONMENT]['database']);
		$this->model_name = $model_name;
		if (!isset ($model_settings['table_name'])) {
			$this->table_name = Inflector :: plural($model_name);
		} else {
			$this->table_name = $model_settings['table_name'];
		}
		if (!isset ($model_settings['primary_key'])) {
			$this->primary_key = 'id';
		} else {
			$this->primary_key = $model_settings['primary_key'];
		}
			if (isset ($model_settings['table_columns']) && ($model_settings['table_columns'] != array ())) {
			$this->table_columns = $model_settings['table_columns'];
			$this->table_columns_types = $model_settings['table_columns_types'];
			$this->table_columns_sizes = $model_settings['table_columns_sizes'];
		} else {
			$tables = $this->dbh->fetch_rows("SHOW COLUMNS FROM " . $this->table_name);
			if (!isset ($this->table_columns) || !isset ($this->table_columns_types) || !isset ($this->table_columns_sizes)) {
				foreach ($tables as $column) {
					$this->table_columns[] = $column['Field'];
					$type = explode('(', $column['Type']);
					$this->table_columns_types[] = $type[0];
					$this->table_columns_sizes[] = substr($type[1], 0, -1);
				}
			}
		}
		$this->has_one = $model_settings['has_one'];
		$this->has_many = $model_settings['has_many'];
		$this->belongs_to = $model_settings['belongs_to'];
		$this->has_many_and_belongs_to = $model_settings['has_many_and_belongs_to'];
	}

	public function find() {
		$args = func_get_args();
		if (!is_array($args[0]) && (($tmp = intval($args[0])) != '')) {
			$args[0] = $tmp;
		}
		if (($args[0] == 'first') || ($args[0] == 'all')) {
			return call_user_func(array (
				$this->model_name,
				'_find_record'
			), $this, $args[0], $args[1]);
		}
		elseif (is_int($args[0]) || is_array($args[0])) {
			return call_user_func(array (
				$this->model_name,
				'_find_record_by_primary_key'
			), $this, $args[0]);
		} else {
			trigger_error('Wrong params in Model::find()', E_USER_ERROR);
		}
	}

	public function find_all($sql_params = null) {
		return $this->find('all', $sql_params);
	}

	public function count($params = null) {
		$this->table_columns[] = $this->table_name . '_count';
		if (!isset ($params)) {
			$params['fields'] = ' COUNT(*) as ' . $this->table_name . '_count';
			$params['conditions'] = array (
				'?',
				1
			);
		}
		elseif (!isset ($params['fields'])) {
			$params['fields'] = ' COUNT(*) as ' . $this->table_name . '_count';
		} else {
			$params['fields'] = ' COUNT(`' . $params['fields'] . '`) as count';
		}
		$ar = $this->find('first', $params);
		$counter = $ar-> {
			$this->table_name . '_count' };
		array_shift($this->table_columns);
		return $counter;
	}

	public function count_all() {
		$this->count();
	}

	public function create($data = null) {
		$ar = call_user_func(array (
			$this->model_name,
			'create_record'
		), $this, $data);
		$ar->_set_status(ActiveRecord :: NEW_RECORD);
		return $ar;
	}

	public function execute($sql) {
		if ($this->dbh->query($sql)) {
			return true;
		} else {
			return false;
		}
	}

}

class ActiveRecord {

	private $__status = '';
	const NEW_RECORD = 1;
	const EXISTED = 2;
	const SAVED = 3;
	const UPDATED = 4;
	const DELETED = 5;

	const ALL = 'all';
	const FIRST = 'first';
	const LAST = 'last';

	/*
		public static $model_name			= null;
		public static $table_name			= null;
		public static $primary_key			= null;
		public static $table_columns		= null;
		public static $table_columns_types	= null;
		public static $table_columns_sizes	= null;
	*/

	/*
		public $has_one						= null;
		public $has_many					= null;
		public $belongs_to					= null;
		public $has_many_and_belongs_to		= null;
	*/

	private $model = null;

	/*
		private $logger 					= null;
	*/
	public static $instances = array ();

	public static function model($caller) {
		if (!array_key_exists($caller, self :: $instances)) {
			self :: $instances[$caller] = new Model($caller);
		}
		return self :: $instances[$caller];
	}

	public function __construct() {
		if (!isset ($this->model))
			$this->model = self :: $instances[get_class($this)];
		return $this;
	}

	public function save($data = null, $conditions = null) {
		if ($this->model->dbh->execute($this->_insert_sql($data, $conditions))) {
			$this-> {
				$this->model->primary_key }
			= $this->model->dbh->last_insert_id();
			return $this->_set_status(ActiveRecord :: SAVED);
		} else {
			return false;
		}
	}

	public function update($changes = null) {
		$sql = 'UPDATE `' . $this->model->table_name . '` SET ';
		foreach ($changes as $key => $val) {
			if (in_array($key, $this->model->table_columns)) {
				$sql .= $key . ' = \'' . $val . '\', ';
			}
		}
		$sql = $this->_add_conditions(substr($sql, 0, -2), array (
			$this->model->table_name . '.' . $this->model->primary_key . ' = ?',
			$this-> {
				$this->model->primary_key }
		));
		if ($this->model->dbh->execute($sql)) {
			$this->_set_status(ActiveRecord :: UPDATED);
		} else {
			return false;
		}
	}

	public function delete() {
		$sql = 'DELETE FROM `' . $this->model->table_name . '` WHERE `' . $this->model->primary_key . '` = ' . $this-> {
			$this->model->primary_key };
		$this->_set_status(ActiveRecord :: DELETED);
		return $this->model->dbh->execute($sql);
	}

	public function is_new_record() {
		return ($this->__status == ActiveRecord :: NEW_RECORD);
	}

	public function _find_record($model, $first_or_all, $sql_params = null) {
		$sql = isset ($sql_params['fields']) ? self :: _select_sql($model, $sql_params['fields']) : self :: _select_sql($model);
		$sql = isset ($sql_params['join']) ? self :: _add_join($sql, $sql_params['join']) : $sql;
		$sql = isset ($sql_params['conditions']) ? self :: _add_conditions($sql, $sql_params['conditions']) : $sql;
		$sql = isset ($sql_params['group']) ? self :: _add_group($sql, $sql_params['group']) : $sql;
		$sql = isset ($sql_params['order']) ? self :: _add_order($sql, $sql_params['order']) : $sql;
		$sql = isset ($sql_params['limit']) ? self :: _add_limit($sql, $sql_params['limit']) : $sql;

		if ($first_or_all == 'all') {
			$ars = array ();
			$rs = $model->dbh->execute($sql);
			while (false !== ($row = $model->dbh->fetch($rs))) {
				$ar = self :: create_record($model, $row);
				$ars[] = $ar->_set_status(ActiveRecord :: EXISTED);
			}
			return $ars;
		}
		elseif ($first_or_all == 'first') {
			if ($ar_data = $model->dbh->fetch($model->dbh->execute(self :: _add_limit($sql, 1)))) {
				$ar = self :: create_record($model, $ar_data);
				return $ar->_set_status(ActiveRecord :: EXISTED);
			} else {
				return false;
			}
		} else {
			$ar = self :: create_record($model->dbh->fetch($model->dbh->execute($sql)));
			return $ar->_set_status(ActiveRecord :: EXISTED);
		}
	}

	public function _find_record_by_primary_key($model, $ids) {
		$sql = self :: _select_sql($model);
		if (is_array($ids)) {
			$sql = self :: _add_conditions($sql, array (
				'`' . $model->primary_key . '` IN (?)',
				$ids
			));
			$ars = array ();
			$rs = $model->dbh->execute($sql);
			while (false !== ($row = $model->dbh->fetch($rs))) {
				$ar = self :: create_record($model, $row);
				$ars[] = $ar->_set_status(ActiveRecord :: EXISTED);
			}
				if ($ars == array ())
				return false;
			return $ars;
		} else {
			$sql = self :: _add_conditions($sql, array (
				'`' . $model->primary_key . '` = ?',
				$ids
			));
			if ($ar_data = $model->dbh->fetch($model->dbh->execute($sql))) {
				$ar = self :: create_record($model, $ar_data);
			} else {
				return false;
			}
			return $ar->_set_status(ActiveRecord :: EXISTED);
		}
	}

	public function create_record($model, $datas = null) {
		$ar = new $model->model_name;
		if ($datas) {
			foreach ($datas as $key => $value) {
				$ar-> $key = $value;
			}
		}
		return $ar;
	}

	private function _select_sql($model, $columns = null) {
		$fields = (!isset ($columns) || ($columns == null)) ? '*' : str_replace('|', ' , ', $columns);
		return 'SELECT ' . $fields . ' FROM `' . $model->table_name . '` ';
	}

	private function _insert_sql($data, $conditions = null) {
		$sql = 'INSERT INTO `' . $this->model->table_name . '` ';
		if ($data) {
			$keys = '';
			$values = '';
			foreach ($data as $key => $value) {
				if ((is_string($value) || is_numeric($value)) && in_array($key, $this->model->table_columns)) {
					$keys .= '`' . $key . '`,';
					$values .= '\'' . $value . '\',';
				}
			}
			$sql .= ' (' . substr($keys, 0, -1) . ') VALUES (' . substr($values, 0, -1) . ') ';
		} else {
			$keys = '';
			$values = '';
			foreach ($this as $key => $value) {
				if ((is_string($value) || is_numeric($value)) && in_array($key, $this->model->table_columns)) {
					$keys .= '`' . $key . '`,';
					$values .= '\'' . $value . '\',';
				}
			}
			$sql .= ' (' . substr($keys, 0, -1) . ') VALUES (' . substr($values, 0, -1) . ') ';
		}
		if ($conditions) {
			$sql = self :: _add_conditions($sql, $conditions);
		}
		return $sql;
	}

	private function _add_conditions($sql, $conditions) {
		foreach ($conditions as $key => $value) {
			if ($key != 0) {
				if (is_string($value) || is_numeric($value)) {
					$conditions[$key] = '\'' . $conditions[$key] . '\'';
				}
				elseif (is_array($value)) {
					$conditions[$key] = join(',', $value);
				} else {
					trigger_error('Wrong params in Model::_add_conditions()', E_USER_ERROR);
				}
			}
		}
		$conditions[0] = str_replace(array (
			'%',
			'?'
		), array (
			'%%',
			'%s'
		), $conditions[0]);
		return $sql . ' WHERE ' . call_user_func_array('sprintf', $conditions);
	}

	private function _add_join($sql, $join_model) {
		if (is_string($join_model)) {
			$sql .= ' JOIN ' . $join_model;
		} else {
			$sql .= ' JOIN ' . $join_model->table_name . ' ON ' . $this->model->table_name . '.' . $join_model->primary_key . ' = ' . $join_model->table_name . '.' . $join_model->primary_key . ' ';
		}
		return $sql;
	}

	private function _add_group($sql, $group = null) {
		if (isset ($group)) {
			$sql .= ' GROUP BY ' . $group;
		}
		return $sql;
	}

	private function _add_order($sql, $order = null) {
		if (isset ($order)) {
			$sql .= ' ORDER BY ' . $order;
		}
		return $sql;
	}

	private function _add_limit($sql, $limit_from_to = null) {
		if (is_array($limit_from_to)) {
			return $sql . ' LIMIT ' . $limit_from_to[0] . ', ' . $limit_from_to[1] . ' ';
		}
		elseif ($limit_from_to == null) {
			return $sql;
		} else {
			return $sql . ' LIMIT ' . $limit_from_to . ' ';
		}
	}

	public function _set_status($status) {
		$this->__status = $status;
		return $this;
	}

	public function __set($key, $value) {
		$this-> $key = $value;
	}

	public function __get($key) {
		if ($key == 'dbh' || $key == 'model_name' || $key == 'table_name' || $key == 'primary_key' || $key == 'table_columns' || $key == 'table_columns_types' || $key == 'table_columns_sizes' || $key == 'has_one' || $key == 'has_many' || $key == 'belongs_to' || $key == 'has_many_and_belongs_to')
			return $this-> $key;
		if (in_array($key, $this->model->table_columns)) {
			return $this-> $key;
		}
		elseif ($this->model->has_one != null && in_array($key, $this->model->has_one)) {
			$ref_model_name = ucfirst($key);
			$foreign_key = $ref_model . '_id';
			return $ref_model->find('first', array (
				'conditions' => array (
					$this->model->primary_key . ' = ?',
					$this->model-> {
						$foreign_key }
				)
			));
		}
		elseif ($this->model->has_many != null && in_array($key, $this->model->has_many)) {
			$ref_model_name = ucfirst(Inflector :: singular($key));
			$ref_model = call_user_func(array (
				$ref_model_name,
				'model'
			));
			return $ref_model->find('all', array (
				'conditions' => array (
					'`' . strtolower($this->model->model_name) . '_id` = ?',
					$this-> {
						$this->model->primary_key }
				)
			));
			//call_user_func(array($ref_model_name, 'find'), 'all', array('conditions' => array('`'.strtolower($this->model->model_name).'_id` = ?', $this->{$this->primary_key})));
			//$ref_model = new $ref_model_name;
			//return $ref_model->find('all', array('conditions' => array('`'.strtolower($this->model_name).'_id` = ?', $this->{$this->primary_key})));
		}
		elseif ($this->belongs_to != null && in_array($key, $this->belongs_to)) {
			$ref_model_name = ucfirst($key);
			//			$ref_model = new $ref_model_name;
			return self :: find_record('first', array (
				'conditions' => array (
					'`' . $this->table_name . '`.`' . $this->primary_key . '` = ?',
					$this-> {
						$this->primary_key }
				),
				'join' => $reference_model
			));
		}
		elseif ($this->has_many_and_belongs_to != null && in_array($key, $this->has_many_and_belongs_to)) {
			$ref_model_name = ucfirst($key);
			$ref_model = new $ref_model_name;
			if ($this->model_name < $reference_model->model_name) {
				$hmbt_name = $this->model_name . '_' . $reference_model->model_name;
			} else {
				$hmbt_name = $reference_model->model_name . '_' . $this->model_name;
			}
			$hmbt = new $hmbt_name;
			return $ref_model->find('all', array (
				'conditions' => array (
					$this->table_name . '.' . $this->primary_key . ' = ?',
					$this-> {
						$this->primary_key }
				),
				'join' => $hmbt->table_name . ' on ' . $hmbt->table_name . '.' . $reference_model->primary_key . ' = ' . $reference_model->table_name . '.' . $reference_model->primary_key . ' join ' . $this->table_name . ' on ' . $hmbt->table_name . '.' . $this->primary_key . '=' . $this->table_name . '.' . $this->primary_key,
				'group' => 'group by ' . $reference_model->table_name . '.' . $reference_model->primary_key
			));
		} else {
			trigger_error('The ActiveRecord association [' . $key . '] is not defined', E_USER_ERROR);
			return '';
		}
	}

	public function __call($method, $args) {
		$method = explode('_', $method);
		if ($method[0] == 'find' && (sizeof($method) == 1)) {
			echo 'find called';
		}
		elseif ($method[0] . '_' . $method[1] == 'find_by') {
			if (in_array($method[2] . '_' . $method[3], $this->table_columns)) {
				return ActiveRecord :: find_record($this, 'all', array (
					'conditions' => array (
						$method[2] . '_' . $method[3] . ' = ?',
						$args[0]
					)
				));
			}
			elseif (in_array($method[2], $this->table_columns)) {
				return ActiveRecord :: find_record($this, 'all', array (
					'conditions' => array (
						$method[2] . ' = ?',
						$args[0]
					)
				));
			} else {
				throw ActiveRecordError('no fun find : errors2 ');
			}
		}
		elseif ($method[0] . '_' . $method[1] == 'find_like') {
			if (in_array($method[2] . '_' . $method[3], $this->table_columns)) {
				return ActiveRecord :: find_record($this, 'all', array (
					'conditions' => array (
						$method[2] . '_' . $method[3] . ' like ?',
						$args[0]
					)
				));
			}
			elseif (in_array($method[2], $this->table_columns)) {
				return ActiveRecord :: find_record($this, 'all', array (
					'conditions' => array (
						$method[2] . ' like ?',
						$args[0]
					)
				));
			} else {
				trigger_error('There is no column named [' . $method[2] . '_' . $method[3] . '] in this table.', E_USER_ERROR);
			}
		} else {
			foreach ($method as $key => $val) {
				$error .= ' ' . $val;
			}
			trigger_error('Method [' . $method . '] Not found', E_USER_ERROR);
		}
	}

	public function __toString() {
		return $this->{ $this->model->primary_key };
	}

	public function to_array() {
		$return = array ();
		foreach ($this->model->table_columns as $column_name) {
			if (isset ($this-> $column_name)) {
				$return[$column_name] = $this-> $column_name;
			} else {
				$return[$column_name] = null;
			}
		}
		return $return;
	}

	public static function toArray($active_records) {
		$array = array ();
		foreach ($active_records as $key => $acvtive_record) {
			foreach ($acvtive_record as $v) {
				if (is_string($v) || is_numeric($v)) {
					$array[$key][] = $v;
				}
			}
		}
		return $array;
	}

}
?>