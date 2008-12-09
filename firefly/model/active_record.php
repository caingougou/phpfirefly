<?php

class ActiveRecord {

	private $model = '';

	/**
	 * @todo active record status
	 */
	private $__status = '';
	const UPDATED = 1;
	const DELETED = 2;
	const SAVED   = 3;

	public function ActiveRecord($model) {
		$this->class_name = strtolower(get_class($this));
		$this->model = $model;
	}

	private function __set_status($status) {
		$this->__status = $status;
		return $this;
	}

	public function save($datas = null, $conditions = null) {
		if($this->model->dbh->execute(self::_insert_sql($datas, $conditions))) {
			$this->{$this->model->prime_key} = $this->model->dbh->last_insert_id();
			return $this->__set_status(ActiveRecord::SAVED);
		} else {
			return false;
		}
	}

	public function update($changes = null) {
		$sql = 'UPDATE `'.$this->model->table_name.'` SET ';
		foreach($changes as $key=>$val) {
			if(in_array($key, $this->model->table_columns)) {
				$sql .= $key.' = \''.$val.'\', ';
			}
		}
		$sql = self::_add_conditions(substr($sql, 0, -2), array($this->model->table_name.'.'.$this->model->prime_key.' = ?',$this->{$this->model->prime_key}));
		return $this->model->dbh->execute($sql);
	}

	public function delete() {
		$sql = 'DELETE FROM `'.$this->model->table_name.'` WHERE `'.$this->model->prime_key.'` = '.$this->{$this->model->prime_key};
		return $this->model->dbh->execute($sql);
	}

	public static function find_record($model, $first_or_all, $sql_params = null) {
		$sql = isset($sql_params['fields']) ? self::_select_sql($model, $sql_params['fields']) : self::_select_sql($model);
		$sql = isset($sql_params['join']) ? self::_add_join($sql, $model, $sql_params['join']) : $sql;
		$sql = isset($sql_params['conditions']) ? self::_add_conditions($sql, $sql_params['conditions']) : $sql;
		$sql = isset($sql_params['group']) ? self::_add_group($sql, $sql_params['group']) : $sql;
		$sql = isset($sql_params['order']) ? self::_add_order($sql, $sql_params['order']) : $sql;
		$sql = isset($sql_params['limit']) ? self::_add_limit($sql, $sql_params['limit']) : $sql;

		if($first_or_all == 'all') {
//			if(DEBUG){echo 'Executing the select sql : '.$sql.'<br/>';}
			$ars = array();
			$rs = $model->dbh->execute($sql);
			while(false !== ($row = $model->dbh->fetch($rs))) {
				$ars[] = self::create_record($model, $row);
			}
			return $ars;
		} elseif($first_or_all == 'first') {
//			if(DEBUG){echo 'Executing the select sql : '.$sql.'<br/>';}
			if($ar = $model->dbh->fetch($model->dbh->execute(self::_add_limit($sql, 1)))) {
				return self::create_record($model, $ar);
			} else {
				return false;
			}
		} else {
//			if(DEBUG){echo'Executing the select sql : '.$sql.'<br/>';}
			return self::create_record($model, $model->dbh->fetch($model->dbh->execute($sql)));
		}
	}

	public static function find_record_by_prime_key($model, $ids) {
		$sql = self::_select_sql($model);
		if(is_array($ids)) {
			$sql = self::_add_conditions($sql, array('`'.$model->prime_key.'` IN (?)', $ids));
//			if(DEBUG){echo'Executing the select sql : '.$sql.'<br/>';}
			$ars = array();
			$rs = $model->dbh->execute($sql);
			while(false !== ($row = $model->dbh->fetch($rs))) {
				$ars[] = self::create_record($model, $row);
			}
			return $ars;
		} else {
			$sql = self::_add_conditions($sql, array('`'.$model->prime_key.'` = ?', $ids));
//			if(DEBUG){echo'Executing the select sql : '.$sql.'<br/>';}
			return self::create_record($model, $model->dbh->fetch($model->dbh->execute($sql)));
		}
	}

	public static function create_record($model, $datas = null) {
		$ar = new ActiveRecord($model);
		if($datas) {
			foreach($datas as $key => $value) {
				$ar->$key = $value;
			}
		}
		return $ar;
	}

	public static function execute($sql) {
		if($this->model->dbh->query($sql)) {
			return true;
		} else {
			return false;
		}
	}

	private function _select_sql($model, $columns = null) {
		$fields = (!isset($columns) || ($columns == null)) ? '*': str_replace('|', ' , ', $columns);
		return 'SELECT '.$fields.' FROM `'.$model->table_name.'` ';
	}

	private function _insert_sql($datas, $conditions = null) {
		$sql = 'INSERT INTO `'.$this->model->table_name.'` ';
		if($datas) {
			$sql .= ' ('.implode(',', array_keys($datas)).') VALUES ('.implode('\',\'', array_values($datas)).');';
		} else {
			$keys = '';
			$values = '';
			foreach($this as $key => $value) {
				if((is_string($value) || is_numeric($value)) && in_array($key, $this->model->table_columns) ) {
					$keys .= '`'.$key.'`,';
					$values .= '\''.$value.'\',';
				}
			}
			$sql .= ' ('.substr($keys, 0, -1).') VALUES ('.substr($values, 0, -1).') ';
		}
		if($conditions) {
			$sql = self::_add_conditions($sql, $conditions);
		}
		return $sql;
	}

	private function _add_conditions($sql, $conditions) {
		foreach($conditions as $key => $value) {
			if($key != 0) {
				if(is_string($value) || is_numeric($value)) {
					$conditions[$key] = '\''.$conditions[$key].'\'';
				} elseif(is_array($value)) {
					$conditions[$key] = join(',', $value);
				} else {
					debug_print_backtrace();
				}
			}
		}
		$conditions[0] = str_replace(array('%','?'),array('%%', '%s'), $conditions[0]);
		return $sql.' WHERE '.call_user_func_array('sprintf', $conditions);
	}

	private function _add_join($sql, $model, $join_model) {
		if(is_string($join_model)) {
			$sql .= ' JOIN '.$join_model;
		} else {
			$sql .= ' JOIN '.$join_model->table_name.' ON '.$model->table_name.'.'.$join_model->prime_key.' = '.$join_model->table_name.'.'.$join_model->prime_key.' ';
		}
		return $sql;
	}

	private function _add_group($sql, $group = null) {
		if(isset($group)) {
			$sql .= ' GROUP BY '.$group;
		}
		return $sql;
	}

	private function _add_order($sql, $order = null) {
		if(isset($order)) {
			$sql .= ' ORDER BY '.$order;
		}
		return $sql;
	}

	private function _add_limit($sql, $limit_from_to = null) {
		if(is_array($limit_from_to)) {
			return $sql.' LIMIT '.$limit_from_to[0].', '.$limit_from_to[1].' ';
		} elseif($limit_from_to == null) {
			return $sql;
		} else {
			return $sql.' LIMIT '.$limit_from_to.' ';
		}
	}

	public function __set($key, $value) {
		$this->$key = $value;
	}

	public function __get($key) {
		if(in_array($key, $this->model->table_columns)) {
			return $this->$key;
		} elseif (in_array($key, $this->model->has_one) || in_array($key, $this->model->has_many) || in_array($key, $this->model->belongs_to) || in_array($key, $this->model->has_many_and_belongs_to)) {
			if(in_array($key, $this->model->has_many)) {
				$model_name = ucfirst(substr($key, 0, -1)).'_Model';
			} else {
				$model_name = ucfirst($key).'_Model';
			}
			$reference_model = new $model_name;
			if(in_array($key, $this->model->has_one)) {
				$fk = strtolower($reference_model->model_name).'_id';
				return self::find_record($reference_model, 'first', array('conditions' => array($reference_model->prime_key.' = ?', $this->{$fk})));
			} elseif(in_array($key, $this->model->has_many)) {
				return self::find_record($reference_model, 'all',
					array('conditions' => array('`'.substr(strtolower($this->model->model_name), 0, -5).'id` = ?', $this->{$this->model->prime_key})));
			} elseif(in_array($key, $this->model->belongs_to)) {
				return self::find_record($this->model, 'first', array(
					'conditions' => array('`'.$this->model->table_name.'`.`'.$this->model->prime_key.'` = ?', $this->{$this->model->prime_key}), 'join' => $reference_model));
			} elseif(in_array($key, $this->model->has_many_and_belongs_to)) {
				if($this->model->model_name < $reference_model->model_name) {
					$hmbt_name = $this->model->model_name.'_'.$reference_model->model_name;
				} else {
					$hmbt_name = $reference_model->model_name.'_'.$this->model->model_name;
				}
				$hmbt = new $hmbt_name;
				return self::find_record($reference_model, 'all', array('conditions' => array($this->model->table_name.'.'.$this->model->prime_key.' = ?', $this->{$this->model->prime_key}), 'join' => $hmbt->table_name.' on '.$hmbt->table_name.'.'.$reference_model->prime_key.' = '.$reference_model->table_name.'.'.$reference_model->prime_key.' join '.$this->model->table_name.' on '.$hmbt->table_name.'.'.$this->model->prime_key.'='.$this->model->table_name.'.'.$this->model->prime_key,'group' => 'group by '.$reference_model->table_name.'.'.$reference_model->prime_key));
			}
			return '';
		} elseif(method_exists($this->model, '__get_'.$key)) {
			return call_user_func(array($this->model, '__get_'.$key), $this);
		} else {
			trigger_error('The ActiveRecord association [' . $key . '] is not defined', E_USER_ERROR);
			return '';
		}
	}

	public function __toString() {
		return $this->{$this->model->prime_key};
	}

	public function to_array() {
		$return = array();
		foreach($this->model->table_columns as $column_name) {
			if(isset($this->$column_name)) {
				$return[$column_name] = $this->$column_name;
			} else {
				$return[$column_name] = null;
			}
		}
		return $return;
	}

	public static function toArray($active_records) {
		$array = array();
		foreach($active_records as $key=>$acvtive_record) {
			foreach($acvtive_record as $v) {
				if(is_string($v) || is_numeric($v)) {
					$array[$key][] = $v;
				}
			}
		}
		return $array;
	}

}

?>