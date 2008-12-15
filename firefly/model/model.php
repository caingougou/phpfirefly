<?php

class Model {

	static $instance				= null;
	public $dbh						= null;

	public $model_name				= null;
	public $prime_key				= null;
	public $table_name				= null;
	public $table_columns			= array();
	public $table_columns_types		= array();
	public $table_columns_sizes		= array();

	public $has_one					= array();
	public $has_many				= array();
	public $belongs_to				= array();
	public $has_many_and_belongs_to = array();

	public function __construct() {
		global $DBH;
		$this->__init();
		$this->model_name = get_class($this);
		$this->dbh = $DBH;
	}

	private function __init() {
		if(!isset($this->prime_key)) {
			$this->prime_key = 'id';
		}
	}

	public function find() {
		$args = func_get_args();
		if(!is_array($args[0])) {
			if(intval($args[0]) != '') {
				$args[0] = intval($args[0]);
			}
		}
		if(($args[0] == 'first') || ($args[0] == 'all')) {
			return ActiveRecord::find_record($this, $args[0], $args[1]);
		} elseif(is_int($args[0]) || is_array($args[0])) {
			return ActiveRecord::find_record_by_prime_key($this, $args[0]);
		} else {
			echo '<pre>';
			debug_print_backtrace();
			echo '</pre>';
			trigger_error('You may input the wrong params in Model::find()');
		}
	}

	public function find_all($sql_params = null) {
		return $this->find('all', $sql_params);
	}

	public function count($params = null) {
		$this->table_colunmns[] = $this->table_name.'_count';
		if(!isset($params)) {
			$params['fields'] = ' COUNT(*) as '.$this->table_name.'_count';
			$params['conditions'] = array('?', 1);
		} elseif(!isset($params['fields'])) {
			$params['fields'] = ' COUNT(*) as '.$this->table_name.'_count';
		} else {
			$params['fields'] = ' COUNT(`'.$params['fields'].'`) as count';
		}
		$ar = $this->find('first', $params);
		return $ar->{$this->table_name.'_count'};
	}

	public function count_all() {
		$this->count();
	}

	public function create($datas = null) {
		return ActiveRecord::create_record($this, $datas);
	}

	public function execute($sql) {
		return ActiveRecord::execute($this, $sql);
	}

	/* Follow methods are NOT implemented */

	public function delete($params = null) {

	}

	public function update($params = null) {

	}

	/*

	public function write($key,$contents) {
		return CacheManager::set($key, $contents);
	}

	public function read($key) {
		return CacheManager::get($key);
	}

	*/

	function __call($method, $args) {
		$method = explode('_', $method);
		if($method[0].'_'.$method[1] == 'find_by') {
			if(in_array($method[2].'_'.$method[3], $this->table_columns)) {
				return ActiveRecord::find_record($this, 'all', array('conditions' => array($method[2].'_'.$method[3].' = ?' , $args[0])));
			} elseif(in_array($method[2], $this->table_columns)) {
				return ActiveRecord::find_record($this, 'all', array('conditions' => array($method[2].' = ?' , $args[0])));
			} else {
				throw ActiveRecordError('no fun find : errors2 ');
			}
		} elseif($method[0].'_'.$method[1] == 'find_like') {
			if(in_array($method[2].'_'.$method[3], $this->table_columns)) {
				return ActiveRecord::find_record($this, 'all', array('conditions' => array($method[2].'_'.$method[3].' like ?' , $args[0])));
			} elseif(in_array($method[2], $this->table_columns)) {
				return ActiveRecord::find_record($this, 'all', array('conditions' => array($method[2].' like ?' , $args[0])));
			} else {
			echo '<pre>';
			debug_print_backtrace();
			echo '</pre>';
			trigger_error('There is no column named '.$method[2].'_'.$method[3].' in this table.');
			}
		} else {
			foreach($method as $key=>$val) {
				$error .= ' '.$val;
			}
			echo '<pre>';
			debug_print_backtrace();
			echo '</pre>';
			trigger_error('Not found');
		}
	}

}

?>