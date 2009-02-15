<?php
class Debugger {
	private static $include_javascript_css = false;

	private $type_array = array ( "array", "object", "resource", "boolean" );
	private $initialized = false;
	private $display_details = false;
	private $array_history = array ();

	/**
	 * Rewrite dBug.php (http://dbug.ospinto.com).
	 * If $show_method is true, debugger will output object's function names.
	 * If $display_details is true, debugger will collapse details.
	 */
	public function __construct($var, $show_method = false, $display_details = false) {
		if (!self :: $include_javascript_css) {
			self :: $include_javascript_css = true;
			$this->init_javascript_css();
		}
		$this->show_method = $show_method;
		$this->display_details = $display_details;
		$this->check_variable_type($var);
	}

	private function get_variable_name() {
		$backtrace_array = debug_backtrace();
		// possible 'included' functions.
		$include_functions = array ( "include", "include_once", "require", "require_once" );

		// check for any included/required files. if found, get array of the last included file.
		// they contain the right line numbers.
		for ($i = count($backtrace_array) - 1; $i >= 0; $i--) {
			$current_array = $backtrace_array[$i];
			if (array_key_exists("function", $current_array) && (in_array($current_array["function"], $include_functions) || strcasecmp($current_array["function"], "debugger") != 0)) {
				continue;
			}
			$include_file_array = $current_array;
			$lines = file($include_file_array["file"]);
			$code = $lines[($include_file_array["line"] - 1)];
			// find call to debugger class
			preg_match('/\bnew Debugger\s*\(\s*(.+)\s*\);/i', $code, $matches);
			return $matches[1];
		}
		return "";
	}

	private function start_table_header($type, $header, $colspan = 2) {
		if (!$this->initialized) {
			$header = $this->get_variable_name() . " (" . $header . ")";
			$this->initialized = true;
		}
		$str_i = $this->display_details ? "style='font-style:italic' " : "";

		echo "<table cellspacing=2 cellpadding=3 class='debug_" . $type . "'>" .
		"<tr><td " . $str_i . "class='debug_" . $type . "_header' colspan=" . $colspan . " onclick='debug_toggle_table(this)'>" . $header . "</td></tr>\n";
	}

	private function start_td_header($type, $header) {
		$str_d = $this->display_details ? " style='display:none'" : "";
		echo "<tr" . $str_d . ">" .
		"<td valign='top' onclick='debug_toggle_row(this)' class='debug_" . $type . "_key'>" . $header . "</td><td>";
	}

	private function close_td_row() {
		return "</td></tr>\n";
	}

	private function error($type) {
		return "variable cannot be " . $type;
	}

	private function check_variable_type($var) {
		switch (gettype($var)) {
			case "resource" :
				$this->var_is_resource($var);
				break;
			case "object" :
				$this->var_is_object($var);
				break;
			case "array" :
				$this->var_is_array($var);
				break;
			case "NULL" :
				$this->var_is_null();
				break;
			case "boolean" :
				$this->var_is_boolean($var);
				break;
			default :
				$var = ($var == "") ? "[empty string]" : $var;
				echo "<table cellspacing='0''><tr><td>" . $var . "</td></tr></table>";
				break;
		}
	}

	private function var_is_null() {
		echo "NULL";
	}

	private function var_is_boolean($var) {
		$var = ($var == 1) ? "TRUE" : "FALSE";
		echo $var;
	}

	private function var_is_array($var) {
		$var_ser = serialize($var);
		array_push($this->array_history, $var_ser);

		$this->start_table_header("array", "array");
		if (is_array($var)) {
			foreach ($var as $key => $value) {
				$this->start_td_header("array", $key);

				//check for recursion
				if (is_array($value)) {
					$var_ser = serialize($value);
					if (in_array($var_ser, $this->array_history, TRUE))
						$value = "*RECURSION*";
				}

				if (in_array(gettype($value), $this->type_array))
					$this->check_variable_type($value);
				else {
					$value = (trim($value) == "") ? "[empty string]" : $value;
					echo $value;
				}
				echo $this->close_td_row();
			}
		} else {
			echo "<tr><td>" . $this->error("array") . $this->close_td_row();
		}
		array_pop($this->array_history);
		echo "</table>";
	}

	private function var_is_object($var) {
		$var_ser = serialize($var);
		array_push($this->array_history, $var_ser);
		$this->start_table_header("object", "object");

		if (is_object($var)) {
			$arrObjVars = get_object_vars($var);
			foreach ($arrObjVars as $key => $value) {

				$value = (!is_object($value) && !is_array($value) && trim($value) == "") ? "[empty string]" : $value;
				$this->start_td_header("object", $key);

				//check for recursion
				if (is_object($value) || is_array($value)) {
					$var_ser = serialize($value);
					if (in_array($var_ser, $this->array_history, true)) {
						$value = (is_object($value)) ? "*RECURSION* -> $" . get_class($value) : "*RECURSION*";
					}
				}
				if (in_array(gettype($value), $this->type_array)) {
					$this->check_variable_type($value);
				} else {
					echo $value;
				}
				echo $this->close_td_row();
			}

			// show methods
			if ($this->show_method) {
				$methods = get_class_methods(get_class($var));
				foreach ($methods as $key => $value) {
					$this->start_td_header("object", $value);
					echo "[function]" . $this->close_td_row();
				}
			}
		} else {
			echo "<tr><td>" . $this->error("object") . $this->close_td_row();
		}
		array_pop($this->array_history);
		echo "</table>";
	}

	private function var_is_resource($var) {
		$this->start_table_header("resource", "resource", 1);
		echo "<tr><td>";
		switch (get_resource_type($var)) {
			case "fbsql result" :
			case "mssql result" :
			case "msql query" :
			case "pgsql result" :
			case "sybase-db result" :
			case "sybase-ct result" :
			case "mysql result" :
				$db = current(explode(" ", get_resource_type($var)));
				$this->var_is_db_resource($var, $db);
				break;
			default :
				echo get_resource_type($var) . $this->close_td_row();
		}
		echo $this->close_td_row() . "</table>";
	}

	private function var_is_db_resource($var, $db = "mysql") {
		if ($db == "pgsql") {
			$db = "pg";
		}
		if ($db == "sybase-db" || $db == "sybase-ct") {
			$db = "sybase";
		}
		$arrFields = array ( "name", "type", "flags" );
		$numrows = call_user_func($db . "_num_rows", $var);
		$numfields = call_user_func($db . "_num_fields", $var);
		$this->start_table_header("resource", $db . " result", $numfields +1);
		echo "<tr><td class='debug_resource_key'>&nbsp;</td>";
		for ($i = 0; $i < $numfields; $i++) {
			$field_header = "";
			for ($j = 0; $j < count($arrFields); $j++) {
				$db_func = $db . "_field_" . $arrFields[$j];
				if (function_exists($db_func)) {
					$fheader = call_user_func($db_func, $var, $i) . " ";
					if ($j == 0) {
						$field_name = $fheader;
					} else {
						$field_header .= $fheader;
					}
				}
			}
			$field[$i] = call_user_func($db . "_fetch_field", $var, $i);
			echo "<td class='debug_resource_key' title='" . $field_header . "'>" . $field_name . "</td>";
		}
		echo "</tr>";
		for ($i = 0; $i < $numrows; $i++) {
			$row = call_user_func($db . "_fetch_array", $var, constant(strtoupper($db) . "_ASSOC"));
			echo "<tr>";
			echo "<td class='debug_resource_key'>" . ($i +1) . "</td>";
			for ($k = 0; $k < $numfields; $k++) {
				$tempField = $field[$k]->name;
				$fieldrow = $row[($field[$k]->name)];
				$fieldrow = ($fieldrow == "") ? "[empty string]" : $fieldrow;
				echo "<td>" . $fieldrow . "</td>";
			}
			echo "</tr>";
		}
		echo "</table>";
		if ($numrows > 0) {
			call_user_func($db . "_data_seek", $var, 0);
		}
	}

	/**
	 * code modified from ColdFusion's cfdump code.
	 */
	private function init_javascript_css() {
		echo "
		<script type='application/javascript'>
			function debug_toggle_row(source) {
				var target = (document.all) ? source.parentElement.cells[1] : source.parentNode.lastChild;
				debug_toggle_target(target, debug_toggle_source(source));
			}

			function debug_toggle_source(source) {
				if (source.style.fontStyle == 'italic') {
					source.style.fontStyle = 'normal';
					source.title = 'click to collapse';
					return 'open';
				} else {
					source.style.fontStyle = 'italic';
					source.title = 'click to expand';
					return 'closed';
				}
			}

			function debug_toggle_target(target, switch_state) {
				target.style.display = (switch_state == 'open') ? '' : 'none';
			}

			function debug_toggle_table(source) {
				var switch_state = debug_toggle_source(source);
				if (document.all) {
					var table = source.parentElement.parentElement;
					for (var i = 1; i < table.rows.length; i++) {
						target = table.rows[i];
						debug_toggle_target(target, switch_state);
					}
				} else {
					var table = source.parentNode.parentNode;
					for (var i = 1;i < table.childNodes.length; i++) {
						target = table.childNodes[i];
						if (target.style) {
							debug_toggle_target(target, switch_state);
						}
					}
				}
			}
		</script>

		<style type='text/css'>
			table.debug_array,table.debug_object,table.debug_resource {
				font-family:Verdana, Arial, Helvetica, sans-serif; color:#000000; font-size:12px;
			}

			.debug_array_header,
			.debug_object_header,
			.debug_resource_header { font-weight:bold; color:#FFFFFF; cursor:pointer; }

			.debug_array_key,
			.debug_object_key { cursor:pointer; }

			/* array */
			table.debug_array { background-color:#006600; }
			table.debug_array td { background-color:#FFFFFF; }
			table.debug_array td.debug_array_header { background-color:#009900; }
			table.debug_array td.debug_array_key { background-color:#CCFFCC; }

			/* object */
			table.debug_object { background-color:#0000CC; }
			table.debug_object td { background-color:#FFFFFF; }
			table.debug_object td.debug_object_header { background-color:#4444CC; }
			table.debug_object td.debug_object_key { background-color:#CCDDFF; }

			/* resource */
			table.debug_resource { background-color:#884488; }
			table.debug_resource td { background-color:#FFFFFF; }
			table.debug_resource td.debug_resource_header { background-color:#AA66AA; }
			table.debug_resource td.debug_resource_key { background-color:#FFDDFF; }
		</style>
		";
	}

}
?>
