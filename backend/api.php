<?


error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit','512M');
set_time_limit(0);
//ignore_user_abort(true);

mUTFencoding();

require_once('config.php');

$objCore = new clsCore($config);

if ($objCore->mGetVar($_REQUEST, 'test') == 1) {
	$GLOBALS['test'] = 1;
}


if ($GLOBALS['test']) {
	fnDrawUploadForm();
	
	//$_REQUEST['action']					= 'sendNotifications';
	//$_REQUEST['user_id']				= '1';
	//$_REQUEST['calendar_id']			= '2';
}

$objCore->doAction();

class clsCore {
	private $db;
	private $smarty;
	private $lang;
	
	public function __construct($config) {
		try {
			$this->db = new PDO('mysql:host=' . $config['db']['host'] . ';dbname=' . $config['db']['name'], $config['db']['login'], $config['db']['password']);

			$sqls[] = "SET NAMES 'utf8'";
			$sqls[] = "SET CHARACTER SET utf8";
			$sqls[] = "SET CHARACTER_SET_CONNECTION=utf8";
			$sqls[] = "SET SQL_MODE = ''";
			
			foreach ($sqls as $sql) {
				$this->db->query($sql);
			}
						
		} catch (PDOException $e) {
			return $this->error($e->getMessage());
		}
	}
	public function doAction() {
		$action = $this->mGetVar($_REQUEST, 'action');
		
		$res['error'] = 'Method was not implemented';
		
		if (method_exists($this, $action)) {
			$res = $this->$action();
		}
		
		return $this->json($res);
	}
	// ------------------------------------------------------------- UP methods ----------------------------------------------
	// ----------------- User -------------------
	private function getUsers() {
		return $this->get(array(), 'user');
	}
	private function getUser() {
		$f = array('user_id');
		
		return $this->get($f);
	}
	private function getUserExt() {
		$uid = $this->mGetVar($_REQUEST, 'user_id');
		
		$sql = "SELECT u.*, m.url as picture FROM user u
					LEFT JOIN media m ON u.media_id = m.media_id
						WHERE u.user_id = {$uid}";
		$query = $this->query($sql);
				
		return $this->json($query->rows);
	}
	private function editUser() {
		return $this->edit();
	}
	// ----------------- Device -------------------
	private function getDevice() {
		$f = array('user_id', 'device_id', 'uuid');
		
		return $this->get($f);
	}
	private function getDevices() {
		$f = array('user_id');
		
		return $this->get($f);
	}
	// ----------------- Config -------------------
	private function getConfig() {
		$f = array('config_id');
		
		return $this->get($f);
	}
	private function editConfig() {
		return $this->edit();
	}
	// ----------------- Log -------------------
	private function getLog() {
		$f = array('action_type', 'limit', 'order');
		
		return $this->get($f);
	}
	private function editLog() {
		return $this->edit();
	}
	// ----------------- Media -------------------
	private function getMedia() {
		$f = array('media_id');
		
		return $this->get($f);
	}
	private function editMedia() {
		return $this->edit();
	}
	// ----------------- Events -------------------
	private function getEvents() {
		$_REQUEST['start_date'] = 'NOW()';
		
		$f = array('e.event_id', 'e.calendar_id', 'c.user_id');
		$mysql_condition = $this->getMySQLCondition($f);
		
		$sql = "SELECT * FROM event e
					LEFT JOIN calendar c ON e.calendar_id = c.calendar_id
						{$mysql_condition}";
		$query = $this->query($sql);
		
		$data = array();
		foreach ($query->rows as $v) {
			$date = date('Y-m-d', strtotime($v['start_date']));
			$v['date_formatted'] = date('l, F d', strtotime($v['start_date']));
			$v['time_formatted'] = date('g:ia', strtotime($v['start_date']));
			$data[$date][] = $v;
		}
		
		ksort($data);
		foreach ($data as $k => $v) {
			mMultyArraySortByColomn($data[$k], 'start_date', 'ASC');		
		}
		
		return $this->json($data);
	}
	private function getEvent() {
		$f = array('event_id', 'calendar_id');
		
		return $this->get($f);
	}
	private function editEvent() {
		return $this->edit();
	}
	// ----------------- Sensor -------------------
	private function getSensor() {
		$f = array('sensor_id');
		
		return $this->get($f);
	}
	private function getSensors() {
		$f = array('p.place_id', 's.sensor_type');
		
		$mysql_condition = $this->getMySQLCondition($f);
		
		$sql = "SELECT p.*, s.* FROM place p
					LEFT JOIN sensor s ON p.place_id = s.place_id
						{$mysql_condition}";
		$query = $this->query($sql);
				
		return $this->json($query->rows);
	}
	// ----------------- Shows -------------------	
	private function getMShows() {
		$field_name1	= 'user_id';
		$field_name2	= 'mshow_id';
		
		$table1			= 'mshow';
		$table2			= 'media';

		$data = $this->getExt($table1, $table2, $field_name1, $field_name2);

		return $this->json($data);
	}
	private function editMShow() {
		return $this->edit();
	}
	// ----------------- Calendar -------------------	
	private function getCalendars() {
		$calendar_id	=	$this->mGetVar($_REQUEST, 'calendar_id');
		$mysql_add		=	($calendar_id)?	" AND calendar_id	= '{$calendar_id}'"	: '';
		
		
		$field_name1	= 'user_id';
		$field_name2	= 'calendar_id';
		
		$table1			= 'calendar';
		$table2			= 'event';
		
		$data = $this->getExt($table1, $table2, $field_name1, $field_name2, $mysql_add);
		
		return $this->json($data);
	}
	// ----------------- Places -------------------	
	private function getPlaces() {
		$field_name1	= 'user_id';
		$field_name2	= 'place_id';
		
		$table1			= 'place';
		$table2			= 'sensor';
		
		$data = $this->getExt($table1, $table2, $field_name1, $field_name2);
				
		return $this->json($data);
	}
	// ----------------- Contacts -------------------	
	private function getContacts() {
		$contact_category_type	=	$this->mGetVar($_REQUEST, 'contact_category_type');
		$contact_user_id		=	$this->mGetVar($_REQUEST, 'contact_user_id');
		$mysql_add				=	($contact_category_type)?	" AND contact_category_type	= '{$contact_category_type}'"	: '';
		$mysql_add				.=	($contact_user_id)?			" AND contact_user_id		= '{$contact_user_id}'"			: '';
		
		$field_name1	= 'user_id';
		$field_name2	= 'contact_user_id';
		
		$table1			= 'contact';
		$table2			= 'user';
		
		$id			= $this->mGetVar($_REQUEST, $field_name1,	'', true);
		$send_all	= $this->mGetVar($_REQUEST, 'send_all');
		$sql		= "SELECT * FROM {$table1} WHERE {$field_name1} = {$id} {$mysql_add}";
		
		$query = $this->query($sql);
		
		$data = array();
		foreach ($query->rows as $v) {
			$data[$v[$field_name2]]	= $v;
		}
		
		$ids = array_keys($data);
		if ($send_all == 1 && $ids) {
			$mysql_add = $this->mysqlArrToStr($ids, "t1.{$field_name1}");
			$sql = "SELECT t1.*, t2.{$field_name2}, m.url as picture FROM {$table2} t1
						INNER JOIN {$table1} t2 ON t1.{$field_name1} = t2.{$field_name2}
						LEFT JOIN media m ON t1.media_id = m.media_id 
							WHERE {$mysql_add}";
			$query = $this->query($sql);
			
			foreach ($query->rows as $v) {
				$data[$v[$field_name2]]['user_data'] = $v;
			}
		}
		
		return $this->json($data);
	}
	// ----------------- CareLang -------------------
	private function getCareLang() {
		$f = array('category');
		return $this->get($f);
	}
	private function editCareLang() {
		return $this->edit();
	}
	// ----------------- Rules -------------------
	private function getRules() {
		$table = 'rule';
		$f = $this->getFields($table);
		
		return $this->get($f, $table, 'ASIS');
	}
	private function editRule() {
		return $this->edit();
	}
	// ----------------- Notification -------------------
	private function getNotifications($message_type = array('tts'), $json_return = true) {
		$data	= array();
		$ids	= array();
		
		$user_id = $this->mGetVar($_REQUEST, 'user_id');
		if ($user_id) {
			$s = '"' . implode('", "', $message_type) . '"';
			$sql = "SELECT * FROM notification WHERE user_id = '{$user_id}' AND message_type IN ({$s}) AND is_sent = 0";
			
			$query = $this->query($sql);
			foreach ($query->rows as $v) {
				$data[$v['notification_id']] = $v;
				$ids[] = $v['notification_id'];
			}
			
			if (!empty($data)) {
				$sql = "UPDATE notification
							SET is_sent = 1
								WHERE notification_id IN (" . implode(', ', $ids). ")";
				$this->query($sql);
			}
		}
		
		if ($json_return) {
			return $this->json($data);
		} else {
			return $data;	
		}
	}
	private function getTTSNotifications() {
		$message_type = array('tts');
		
		return $this->getNotifications($message_type);
	}
	private function editNotification($data = array()) {
		return $this->edit(false, $data);
	}
	// ----------------- Rule Notifications -------------------
	private function sendNotifications() {
		$sql = "SELECT * FROM rule";
		$query = $this->query($sql);
		
		$rules = $query->rows;

		foreach ($rules as $v) {
			$t = time() - 300; // time_interval_to_check_logs
			$sql = "SELECT * FROM log
						WHERE	device		= '{$v['device']}' AND
								device_value {$v['device_value']}"; //AND date_stamp > {$t}
			$query = $this->query($sql);
			$logs = $query->rows;
			foreach ($logs as $vv) {
				$sql = "SELECT * FROM rule_notification WHERE rule_id = {$v['rule_id']}";
				
				$query = $this->query($sql);
				$rule_notifications = $query->rows;
				
				foreach ($rule_notifications as $vvv) {
					if ($vvv['user_id']) {
						$this->addNotification($vvv);
					}
					if ($vvv['contact_category_type']) {
						$sql = "SELECT c.* FROM contact c
									LEFT JOIN user u ON c.user_id = u.user_id
										WHERE contact_category_type = '{$vvv['contact_category_type']}'";
						$query = $this->query($sql);
						$contacts = $query->rows;
						
						foreach ($contacts as $c) {
							$vvv['user_id'] = $c['contact_user_id'];
							$this->addNotification($vvv);
						}
					}
				}
			}
		}
		
		// Sending notifications
		$data = array();
		$sql = "SELECT n.*, u.email, u.mobile_phone FROM notification n
					LEFT JOIN user u ON n.user_id = u.user_id
						WHERE n.message_type = 'sms' OR n.message_type = 'email' AND n.is_sent = 0";
	
		$query = $this->query($sql);
		foreach ($query->rows as $v) {
			$data[$v['notification_id']] = $v;
			$ids[] = $v['notification_id'];
		}
		
		if (!empty($data)) {
			$sql = "UPDATE notification
						SET is_sent = 1
							WHERE notification_id IN (" . implode(', ', $ids). ")";
			$this->query($sql);
		}
		
		foreach ($data as $v) {
			if ($v['message_type'] == 'sms') {
				$v['to']	= $v['mobile_phone'];
				$this->sendSMS($v);
			} elseif ($v['message_type'] == 'email') {
				$v['to']	= $v['email'];
				$this->sendEmail($v);		
			}
		}
	}
	private function addNotification($data) {
		$v1 = time() - $data['time_interval_to_check_logs'];
		$v2 = time() + $data['time_interval_to_check_logs'];
		
		$sql = "SELECT * FROM notification
					WHERE	user_id			= '{$data['user_id']}'							AND
							message			= " . $this->db->quote($data['message']) . "	AND
							message_type	= '{$data['message_type']}'						AND
							date_stamp		> {$v1}											AND 
							date_stamp		< {$v2}";
		$query = $this->query($sql);
		
		if ($query->total < 1) {
			$this->editNotification($data);
		}
	}
	// ------------------------------------------------------------------------- NOTIFICATIONS --------------------------------------------------------------
	private function sendSMS($data = array()) {
		$data = (empty($data))? $_REQUEST : $data;
		
		require_once($this->getPath() . "external/twilio-php-master/Services/Twilio.php");
		
		$AccountSid	= "AC42d2ee9201afe333953d92771fdf7ecf";
		$AuthToken	= "7c427e8d50c9120d45858046edf9e160";
		
		$client = new Services_Twilio($AccountSid, $AuthToken);
		
		$message = $client->account->messages->create(array(
		    "From"	=> $this->mGetVar($data, 'from',	'289-799-3487'),
		    "To"	=> $this->mGetVar($data, 'to',		'416-569-1524'),
		    "Body"	=> $this->mGetVar($data, 'message'),
		));
		
		return $message->sid;
	}
	private function sendEmail($data = array()) {
		$data = (empty($data))? $_REQUEST : $data;
		
		require_once($this->getPath() . "external/phpmailer-master/PHPMailerAutoload.php");
		
		$mail = new PHPMailer;
		
		$from		= $this->mGetVar($data, 'from',	'system@mable.care');
		$to			= $this->mGetVar($data, 'to');
		$subject	= $this->mGetVar($data, 'subject', 'Notification from MABLE!');
		$message	= $this->mGetVar($data, 'message');
		
		
		$mail->setFrom($from);
		$mail->addAddress($to);
		
		$mail->isHTML(true);		
		$mail->Subject = $subject;
		$mail->Body    = $message;
		
		if (!$mail->send()) {
		    $res['error']	= 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo;
		} else {
		    $res['success']	= 'Message has been sent';
		}
		
		return $res;
	}
	// ------------------------------------------------------------- Internal -------------------------------------------------
	private function get($field_names = array(), $table = false, $op = '=') {
		if ($table === false) {
			$prefix	= 'get';
			$t		= debug_backtrace();
			$table	= strtolower(substr($t[1]['function'], strlen($prefix)));
		}
		
		$mysql_condition = $this->getMySQLCondition($field_names, $op);
		
		$sql = "SELECT * FROM `{$table}`
					{$mysql_condition}";
		$query = $this->query($sql);
		
		return $this->json($query->rows);
	}
	private function edit($send_in_json = true, $data = array()) {
		$prefix	= 'edit';
		$t		= debug_backtrace();
		$table	= strtolower(substr($t[1]['function'], strlen($prefix)));
		
		$fields	= $this->getFields($table);
		$data	= (empty($data))? $_REQUEST : $data;
		
		$key_field	= $table . '_id';
		$id			= $this->mGetVar($data, $key_field, 0);
		$tmp = array();
		$date_arr = array('date_original', 'date', 'start_date', 'end_date');
		foreach ($fields as $k => $field) {
			if ($field == 'url') {
				$data[$field] = $this->uploadMedia($field);
			}
			if ($field == 'date_stamp') {
				$data[$field] = time();
			}
			if (array_search($field, $date_arr)) {
				if ($this->mGetVar($data, $field, false) === false) {
					$data[$field] = 'NOW()';
				}
			}
			if ($this->mGetVar($data, $field, false) !== false) {
				if ($field == $key_field) {
					continue;
				}
				
				if ($this->mGetVar($data, $field) == 'NOW()') {
					$value = $this->mGetVar($data, $field);
				} else {
					$value = $this->mGetVar($data, $field, '', true);
				}
				$tmp[] = "`{$field}` = " .  $value;
			}
		}
		
		if (!empty($tmp)) {
			$mysql_add = implode(', ', $tmp);
			
			$mysql_operation	= (!$id)? 'INSERT INTO'	: 'UPDATE';
			$mysql_condition	= (!$id)? ''			: ('WHERE ' . $table . '_id = ' . $this->db->quote($id));
			
			$sql = "{$mysql_operation} `{$table}`
						SET {$mysql_add}
							{$mysql_condition}";
			$query = $this->query($sql);
			
			if ($id == 0) {
				$id = $query->lastID;
			}
			
			$res['id'] = $id;
			if ($send_in_json) {
				return $this->json($res);
			} else {
				return $res;
			}
		}
	}
	private function getExt($table1, $table2, $field_name1, $field_name2, $mysql_add_ext = '') {
		$id			= $this->mGetVar($_REQUEST, $field_name1,	'', true);
		$send_all	= $this->mGetVar($_REQUEST, 'send_all');
		$sql		= "SELECT * FROM {$table1} WHERE {$field_name1} = {$id}";
		
		$query = $this->query($sql);
		
		$data = array();
		foreach ($query->rows as $v) {
			$v['source']			= array();
			$data[$v[$field_name2]]	= $v;
		}
		
		$ids = array_keys($data);
		if ($send_all == 1 && $ids) {
			$mysql_add = $this->mysqlArrToStr($ids, "t2.{$field_name2}");
			$sql = "SELECT * FROM {$table2} t1
						INNER JOIN {$table1} t2 ON t1.{$field_name2} = t2.{$field_name2} 
							WHERE {$mysql_add} {$mysql_add_ext}";
			$query = $this->query($sql);
			
			foreach ($query->rows as $v) {
				$data[$v[$field_name2]]['source'][] = $v;
			}
		}
		
		return $data;
	}
	private function uploadMedia($field_name) {
		$d				= 'uploads';
		$uploads_dir	= $this->getPath($d);
		$web_path		= '/' . $d . '/';
		$media_file		= '';
		
		if (isset($_FILES[$field_name])) {
			$tmp_name	= $_FILES[$field_name]['tmp_name'];
			$name		= $_FILES[$field_name]['name'];
			
			$path_parts = pathinfo($name);
			$file_ext	= $path_parts['extension'];
			
			$file_name	= time() . '.' . $file_ext;
			@move_uploaded_file($tmp_name, $uploads_dir . $file_name);
			$media_file = $web_path . $file_name;
		}
		
		return $media_file;
	}
	private function getFields($table) {
		$sql = "SHOW COLUMNS FROM `$table`";
		
		$query = $this->query($sql);
		
		$data = array();
		foreach ($query->rows as $v) {
			$data[] = $v['Field'];
		}
		
		return $data;
	}
	private function getKeyField($table) {
		$key_field = $table . '_id';
		
		return $key_field;
	}
	private function query($sql) {
		$obj		= new stdClass();
		$obj->rows	= array();
		
		if ($GLOBALS['test']) {
			echo 'SQL =' . $sql;
		}
		
		$query = $this->db->query($sql);
		if ($query === false) {
			$res['error'] = "Error: {$sql} - " . print_r($this->db->errorInfo(), true);
			return $this->json($res);
		}
		while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
			$obj->rows[] = $row;
		}
		$obj->lastID	= $this->db->lastInsertId();
		$obj->row		= isset($obj->rows[0])?	$obj->rows[0]		: array();
		$obj->total		= !empty($obj->rows)?	count($obj->rows)	: 0;
			
		return $obj;
	}
	private function getMySQLCondition($field_names, $op = '=', $cmp = 'AND') {
		$fields = array();
		foreach ($field_names as $v) {
			list($table_name, $field_name) = $this->getTableField($v);
			if ($this->mGetVar($_REQUEST, $field_name, false) !== false) {
				if (!$table_name) {
					$k = "`{$field_name}`";
				} else {
					$k = "{$table_name}.`{$field_name}`";
				}
				$fields[$k]	= $this->mGetVar($_REQUEST, $field_name);
			}
		}
		
		$tmp = array();
		foreach ($fields as $field => $value) {
			if ($op == 'ASIS') {
				$tmp[] = "{$field} {$value}";
			} elseif ($field == 'NOW()') {
				$tmp[] = "{$field} >= {$value}";
			} else {
				$tmp[] = "{$field} {$op} " . $this->db->quote($value);
			}
		}
		
		$mysql_condition = '';
		if (!empty($tmp)) {
			$order_by	= $this->mGetVar($_REQUEST, 'order_by',			false);
			$order_dir	= $this->mGetVar($_REQUEST, 'order_direction',	false);
			$order = ($order_by === false || $order_dir === false)? '' : "ORDER BY {$order_by} {$order_dir}";
			
			$v = $this->mGetVar($_REQUEST, 'limit', false);
			$limit = ($v === false)? '' : "LIMIT {$v}";
			
			$mysql_condition = 'WHERE ' . implode(" {$cmp} ", $tmp) . " {$order} {$limit}";
		}
		
		return $mysql_condition;
	}
	private function getTableField($s) {
		$table = '';
		$field = '';
		
		$tmp = explode('.', $s);
		if (isset($tmp[0]) && isset($tmp[1])) {
			$table	= $tmp[0];
			$field	= $tmp[1];
		} else {
			$field = $s;
		}
		
		return array($table, $field);
	}
	private function mysqlArrToStr($arr, $field_name, $op = '=') {
		$res = '';
		if (!$arr) {
			return $res;
		}
		
		$res = $field_name . $op . implode(" OR {$field_name} {$op} ", $arr);
		
		return $res;	
	}
	// ------------------------------------------------------------------------------------
	private function getSmarty() {
		$path = $this->getPath() . 'smarty/';
		
		require_once $path . 'libs/Smarty.class.php';
		require_once $path . 'libs/SmartyBC.class.php';
		
		$this->smarty				= new SmartyBC();
		$this->smarty->template_dir	= $path . 'templates/';
		$this->smarty->compile_dir	= $path . 'templates_c/';
		//$this->smarty->debugging	= true;
		
		$this->smarty->assign('lang', $this->lang);
	}
	private function getPath($dir = 'backend') {
		$p = $_SERVER['DOCUMENT_ROOT'] . '/' . $dir . '/';
		$p = str_replace('//', '/', $p);
		
		return $p;
	}
	private function json($arr) {
		if ($GLOBALS['test']) {
			print_r($arr);
			exit;	
		}
		
		$res = json_encode($arr);
		
		echo $res;
		exit;
	}
	private function error($msg) {
		$res['error'] = $msg;
		
		return $this->json($res);
	}
	public function mGetVar($arr, $key, $default_value = '', $use_escape = false) {
		$val = (isset($arr[$key]))? $arr[$key] : $default_value;
		if ($use_escape) {
			$val = $this->db->quote($val);
		}
		return $val;
	}
}
function mMultyArraySortByColomn(&$array, $col, $direction = 'ASC', $use_lower_case = false) {
	if (!empty($array)) {
		$sorter	= array();
		$ret	= array();
		reset($array);
		foreach ($array as $ii => $va) {
			$sorter[$ii] = $va[$col];
		}
	
		uasort($sorter, create_function('$a, $b', 'return mCmp($a, $b, "' . $direction . '", ' . (($use_lower_case)? 1 : 0) . ');'));
	
		foreach ($sorter as $ii => $va) {
			$ret[$ii] = $array[$ii];
		}
		$array = $ret;
	}
}
function mCmp($a, $b, $direction = 'ASC', $use_lower_case = false) {
    if ($use_lower_case) {
    	$a = strtolower($a);
    	$b = strtolower($b);
    }
    
	if ($a == $b) {
        return 0;
    }
    
    if ($direction == 'ASC') {
    	$res = ($a < $b) ? -1 : 1;
    } else {
    	$res = ($a > $b) ? -1 : 1;
    }
    return $res;
}
function mUTFencoding() {
	mb_internal_encoding('UTF-8');
	mb_http_output('UTF-8');
	mb_http_input('UTF-8');
	mb_language('uni');
	mb_regex_encoding('UTF-8');
	ob_start('mb_output_handler');
	header('Content-Type: text/html; charset=utf-8');
	header('Access-Control-Allow-Origin: *');
}
function fnDrawUploadForm() {
	$s = '	<form method=post enctype="multipart/form-data">
				File to upload: <input type=file name="url"><br>
				<input type=hidden name="test" value="1">
				<input type=submit value="Submit">
			</form>
		';
	
	echo $s;
}