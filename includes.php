<?php

define('USE_CAPCHA', 1);

require ("config.php");
require ("functions.php");
require ("sqlitedb.php");
require ("convertroman.php");

if(in_array(basename($_SERVER['PHP_SELF']), array('login.php', 'acc_login.php'))) {
	ini_set('memory_limit', '64M');
}

my_session_start();

$preff = (preg_match('/^acc_/i', basename($_SERVER['PHP_SELF']))) ? 'acc_' : '';

if(!isset($_POST['xjxr'])) {
	if (!isset($_SESSION['sess_user_id']) && (!in_array(basename($_SERVER['PHP_SELF']), array($preff.'login.php', 'autocompleter.php')))) {
	
		if(preg_match('/^popup_/', basename($_SERVER['PHP_SELF']))) {
			echo '<script type="text/javascript">'."\n";
			echo 'parent.location = \'index.php\';'."\n";
			echo '</script>';
		} else {
			$params = (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING']!='') ? '?'.$_SERVER['QUERY_STRING'] : '';
			header('Location:'.$preff.'login.php?accessdenied='.urlencode(basename($_SERVER['PHP_SELF']).$params));
		}
		exit();
	}
}

$dbInst = new SqliteDB();

@set_magic_quotes_runtime(0);
// the magic_quotes_gpc workaround
if(get_magic_quotes_gpc()) {
	kill_magic_quotes($_GET);
	kill_magic_quotes($_POST);
	kill_magic_quotes($_COOKIE);
	kill_magic_quotes($_REQUEST);
}

// this simulates magic_quotes_gpc = 0...
// Hope it works!
function & kill_magic_quotes(&$str) {
	if(is_array($str)) {
		while(list($key, $val) = each($str)) {
			$str[$key] = kill_magic_quotes($val); // this basically loops into arrays...
		}
	} else {
		$str = stripslashes(trim($str)); // get rid of those slashes!
	}
	return $str;
}

