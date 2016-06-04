<?
// Common utility functions

function mGetVar($arr, $key, $default_value = '', $use_escape = false) {
	$val = (isset($arr[$key]))? $arr[$key] : $default_value;
	if ($use_escape) {
		if (isset($GLOBALS['db'])) {
			$val = $GLOBALS['db']->escape($val);
		} else {
			$val = mEscape($val);
		}
	}
	return $val;
}

function mCut($text, $limit, $add_dots = true) {
	$post_fix = '';
	if (strlen($text) > $limit && $add_dots) {
		$post_fix = '...';
		$str = mb_strcut($text, 0, $limit, 'UTF-8') . $post_fix;
		$lastpos = strrpos($str, ' ');
		if($lastpos && $lastpos > $limit - 20) {
			$str = substr($str,0,$lastpos) . '...';
		}
	}
	else {
		$str = $text;
	}

	return $str;
}

?>