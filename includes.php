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

// Require the idiorm file
require('idiorm.php');
// Connect to the demo database file
ORM::configure('sqlite:./db/stm.db');

$added_by = $modified_by = $updated_by = (isset($_SESSION['sess_user_id'])) ? $_SESSION['sess_user_id'] : 0;
$added_by_txt = $modified_by_txt = $updated_by_txt = (isset($_SESSION['sess_fname'])) ? $dbInst->checkStr($_SESSION['sess_fname'] . ' ' . $_SESSION['sess_lname']) : '';
$added_on = $modified_on = $updated_on = date('Y-m-d H:i:s');
$added_from_ip = $updated_from_ip = (isset($_SERVER['REMOTE_ADDR'])) ? $dbInst->checkStr($_SERVER['REMOTE_ADDR']) : $_SERVER['REMOTE_ADDR'];

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

