<?php

require ("config.php");
require ("functions.php");
require ("sqlitedb.php");

ini_set('memory_limit', '64M');

$dbInst = new SqliteDB();
$rows = $dbInst->displayLog();

$known['85.11.130.1'] = '"ТМ-Д-Р МАРГАРИТА ЛАЛОВА" ЕООД';
$known['212.233.204.99'] = '"ПЛЕВЕН ПРОЕКТ КОНСУЛТ" ЕООД';
$known['77.85.162.230'] = 'Me!';
$known['87.126.105.204'] = 'Me!';

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Access List Log</title>
</head>
<body>
<h2>Access List Log</h2>
<table border="1">
  <tr bgcolor="#FFCC66">
    <th>N</th>
    <th>IP</th>
    <th>Who</th>
    <th>Browser</th>
    <th>Access Date</th>
  </tr>
<?php
$i = 0;
$light = '#FFFFCC';
$dark = '#FFFF99';
$date_accessed = '';
$color = '#FFFFCC';
foreach ($rows as $row) {
	if($date_accessed != substr($row['date_accessed'], 0, 10)) {
		$color = ($color == $light) ? $dark : $light;
		$date_accessed = substr($row['date_accessed'], 0, 10);
	}
	$who = $row['user_name'];
	if(isset($known[$row['REMOTE_ADDR']])) { $who = $known[$row['REMOTE_ADDR']]; }
	elseif(!empty($row['fname'])) {
		$who = $row['fname'];
		if(!empty($row['lname'])) { $who .= ' '.$row['lname']; }
	}
	elseif(!empty($row['lname'])) { $who = $row['lname']; }
	
	echo '<tr bgcolor="'.$color.'">';
	echo '<td>'.(++$i).'</td>';
	echo '<td>'.$row['REMOTE_ADDR'].'</td>';
	echo '<td>'.$who.'</td>';
	echo '<td>'.$row['HTTP_USER_AGENT'].'</td>';
	echo '<td nowrap="nowrap">'.$row['date_accessed'].'</td>';
	echo '</tr>';
}
?>
</table>
</body>
</html>