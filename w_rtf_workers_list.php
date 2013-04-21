<?php
// http://localhost/stm2008/viamed/w_rtf_workers_list.php?firm_id=93&BGSTM=dea527a8a85afc8bffa15cc8ec4446c3&offline=1
require('includes.php');

$offline = (isset($_GET['offline']) && $_GET['offline'] == '1') ? 1 : 0;

$firm_id = (isset($_GET['firm_id']) && is_numeric($_GET['firm_id'])) ? intval($_GET['firm_id']) : 0;
$f = $dbInst->getFirmInfo($firm_id);
if(!$f) {
	die('Липсва индентификатор на фирмата!');
}
$subdivision_id = (isset($_GET['subdivision_id']) && !empty($_GET['subdivision_id'])) ? intval($_GET['subdivision_id']) : 0;
if(!empty($subdivision_id)) {
	$subdivision_name = $dbInst->GiveValue('subdivision_name', 'subdivisions', "WHERE `firm_id` = $firm_id AND `subdivision_id` = $subdivision_id", 0);
	if(!empty($subdivision_name)) {
		$f['firm_name'] .= ', '.$subdivision_name;
	}
}

$s = $dbInst->getStmInfo();

$stm_name = preg_replace('/\<br\s*\/?\>/', '', $s['stm_name']);

$firm_name = str_replace(' ', '_', $f['firm_name']);
$firm_name = str_replace('"', '', $firm_name);
$firm_name = str_replace('\'', '', $firm_name);
$firm_name = str_replace('”', '', $firm_name);
$firm_name = str_replace('„', '', $firm_name);
$firm_name = str_replace('_-_', '_', $firm_name);

require_once("cyrlat.class.php");
$cyrlat = new CyrLat;
$filename = 'Spisak_'.$cyrlat->cyr2lat($firm_name);


require('phprtflite/rtfbegin.php');

$sect->writeText('<b>Списък</b>', $times20, $alignCenter);
$sect->writeText('<b>на работещите в '.((isset($f['firm_name'])) ? HTMLFormat($f['firm_name']) : '').'</b>'.((isset($f['location_name'])) ? ' – '.HTMLFormat($f['location_name']) : ''), $times14, $alignCenter);
if($subdivision_id) {
	$sect->writeText(((isset($rows[0]['subdivision_name'])) ? HTMLFormat($rows[0]['subdivision_name']) : ''), $times14, $alignCenter);
}

$sect->addEmptyParagraph();
$sect->addEmptyParagraph();

$sql = "SELECT w.fname, w.sname, w.lname, w.egn,
		strftime('%d.%m.%Y г.', w.date_retired, 'localtime') AS date_retired_h,
		strftime('%d.%m.%Y', w.date_curr_position_start, 'localtime') AS date_curr_position_start2, i.position_name
		FROM workers w
		LEFT JOIN firm_struct_map m ON (m.map_id = w.map_id )
		LEFT JOIN firm_positions i ON (i.position_id = m.position_id)
		WHERE w.firm_id = '$firm_id'  
		AND w.is_active = '1'
		".(($subdivision_id)?" AND m.subdivision_id = '$subdivision_id' ":'')."
		ORDER BY w.date_retired, w.fname, w.sname, w.lname, w.egn, w.worker_id";

$rows = $dbInst->fnSelectRows($sql);
$data = array();
if(count($rows)) {
	$data[] = array('<b>№ по ред</b>', '<b>Име</b>', '<b>ЕГН</b>', '<b>Длъжност</b>', '<b>Дата на постъпване</b>', '<b>Дата на напускане</b>');
	$i = 1;
	foreach ($rows as $row) {
		$flds = array();
		$flds[] = $i++.'.';
		$flds[] = HTMLFormat($row['fname'].' '.$row['sname'].' '.$row['lname']);
		$flds[] = $row['egn'];
		$flds[] = HTMLFormat($row['position_name']);
		$flds[] = $row['date_curr_position_start2'].' г.';
		$flds[] = ((!empty($row['date_retired_h'])) ? $row['date_retired_h'] : '');
		$data[] = $flds;
	}
	$colWidts = array(1.5, 3.5, 2.5, 4, 2.5, 2.5);
	$colAligns = array('center', 'left', 'center', 'left', 'center', 'center');
	fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'plain');
} else {
	$sect->writeText('Няма предоставени данни.', $times14, $alignLeft);
	$sect->addEmptyParagraph();
}

$timesFooter = $times14;
require('phprtflite/rtfend.php');
