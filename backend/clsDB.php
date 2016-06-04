<?
/*
 * MySQL class based on OpenCart framework & DB Class by Alex Korsukov, edited by Sam Singer
 * ArtCo 2016
 *
*/

final class MySQL {
	private $link;
	
	public function __construct($hostname, $username, $password, $database) {
		$this->link = @mysqli_connect($hostname, $username, $password, $database);
		
		if (!$this->link) {
			trigger_error('Error: Could not make a database link using ' . $username . '@' . $hostname);
		}
		
		mysqli_query($this->link, "SET NAMES 'utf8'");
		mysqli_query($this->link, "SET CHARACTER SET utf8");
		mysqli_query($this->link, "SET CHARACTER_SET_CONNECTION=utf8");
		mysqli_query($this->link, "SET SQL_MODE = ''");
		mysqli_query($this->link, "SET time_zone = 'right/Canada/Eastern'");
		// right/Canada/Eastern !!!!!
	}
	public function query($sql, $track_system_query = false, $use_first_db_provider = false) {
		if ($res = mysqli_query($this->link, $sql)) {
			$data = array();
			
			if (is_object($res)) {
				while ($row = mysqli_fetch_assoc($res)) {
					$data[] = $row;
				}
				
				mysqli_free_result($res);
			}
						
			$query = new stdClass();
			$query->row			= isset($data[0])? $data[0] : array();
			$query->rows		= $data;
			$query->num_rows	= count($data);
			$query->total		= isset($query->row['total'])? $query->row['total'] : false;
			
			unset($data);
			
			return $query;	
		} else {
			trigger_error('Error: ' . mysqli_error($this->link) . '<br />Error No: ' . mysqli_errno($this->link) . '<br />' . $sql);
			exit();
		}
	}
	public function escape($value) {
		return mysqli_real_escape_string($this->link, $value);
	}
	
	public function countAffected() {
		return mysqli_affected_rows($this->link);
	}

	public function getLastId() {
		return mysqli_insert_id($this->link);
	}	
	
	public function __destruct() {
		@mysqli_close($this->link);
	}
	public function getLink() {
		return $this->link;	
	}
	private function redirect($error_number) {
		if (!isset($GLOBALS['skip_mysql_redirect'])) {
			header("Location: /offline.html?error={$error_number}");
			exit;
		}
	}
}

class DB {
	private $driver;
	private $driver_name;
	private $hostname;
	private $username;
	private $password;
	private $database;
	
	public function __construct($driver_name, $hostname, $username, $password, $database) {
		$this->driver_name	= $driver_name;
		$this->hostname		= $hostname;
		$this->username		= $username;
		$this->password		= $password;
		$this->database		= $database;
	}
  	public function query($sql) {
		$this->getInstance();

		if (defined('SQL_DEBUG')) {
			$caller = debug_backtrace();
			Debug::trigger('sql', $sql, $caller);
		}
		return $this->driver->query($sql);
  	}
	public function escape($value) {
		$this->getInstance();

		return $this->driver->escape($value);
	}
  	public function countAffected() {
		$this->getInstance();

		return $this->driver->countAffected();
  	}
  	public function getLastId() {
		$this->getInstance();

		return $this->driver->getLastId();
  	}
  	public function getLink() {
		$this->getInstance();
		
		return $this->driver->geLink();
  	}
  	public function toStringForSET($data) {
  		// $data = array('field_name1' => 'field_value1', 'field_name2' => 'field_value2', 'field_name3' => 'field_value3' ...);
  		
  		$res	= false;
  		$parts	= array();
  		foreach ($data as $field_name => $field_value) {
  			$parts[] = "`{$field_name}` = '" . $this->escape($field_value) . "'";
  		}
  		
  		if (!empty($parts)) {
  			$res = implode(', ', $parts);
  		}
  		
  		return $res;
  	}
  	private function getInstance() {
		if (!is_object($this->driver)) {					
			$this->driver = new $this->driver_name($this->hostname, $this->username, $this->password, $this->database);
		}
  	}
}

