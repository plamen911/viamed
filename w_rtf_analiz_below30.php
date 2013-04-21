<?php
// Test: http://localhost/stm2008/hipokrat/w_rtf_analiz_below30.php?firm_id=104&date_from=01.01.2011&date_to=31.12.2011
require('includes.php');
require('class.stmstats.php');

$firm_id = (isset($_GET['firm_id']) && is_numeric($_GET['firm_id'])) ? intval($_GET['firm_id']) : 0;
$f = $dbInst->getFirmInfo($firm_id);
if(!$f) {
	die('Липсва индентификатор на фирмата!');
}
$s = $dbInst->getStmInfo();

$stm_name = preg_replace('/\<br\s*\/?\>/', '', $s['stm_name']);

if(!isset($_GET['date_from']) || trim($_GET['date_from']) == '') {
	$y = date('Y') - 1;
	$date_from = date('Y-m-d H:i:s', mktime(0,0,0,1,1,$y));
	$date_to = date('Y-m-d H:i:s', mktime(23,59,59,12,31,$y));
} else {
	$d = new ParseBGDate();
	if($d->Parse($_GET['date_from']))
	$date_from = $d->year.'-'.$d->month.'-'.$d->day.' 00:00:00';
	else
	$date_from = '';
	if($d->Parse($_GET['date_to']))
	$date_to = $d->year.'-'.$d->month.'-'.$d->day.' 23:59:59';
	else
	$date_to = '';
	if($date_from == '' || $date_to == '') {
		$y = date('Y') - 1;
		$date_from = date('Y-m-d H:i:s', mktime(0,0,0,1,1,$y));
		$date_to = date('Y-m-d H:i:s', mktime(23,59,59,12,31,$y));
	}
}
$objStats = new StmStats($firm_id, $date_from, $date_to);

$firm_name = str_replace(' ', '_', $f['firm_name']);
$firm_name = str_replace('"', '', $firm_name);
$firm_name = str_replace('\'', '', $firm_name);
$firm_name = str_replace('”', '', $firm_name);
$firm_name = str_replace('„', '', $firm_name);
$firm_name = str_replace('_-_', '_', $firm_name);

$period = str_replace(', ', '_', $dbInst->extractYear($date_from, $date_to));
$period = str_replace(' и ', '_', $period);

require_once("cyrlat.class.php");
$cyrlat = new CyrLat;
$filename = 'Analiz_'.$cyrlat->cyr2lat($period.'_'.$firm_name);

$location_type = '';
switch ($f['location_type']) {
	case '0': $location_type = 'с.'; break;
	case '1': $location_type = 'гр.'; break;
	case '2': $location_type = 'жк'; break;
	case '3': $location_type = 'кв.'; break;
	default: $location_type = ''; break;
}
$firm_address = trim($location_type.$f['location_name'].((!empty($f['address'])) ? ', '.$f['address'] : ''));


require('phprtflite/rtfbegin.php');

$sect->writeText('<b>Обобщен анализ на здравното състояние</b>', $times20, $alignCenter);
$sect->writeText('<b>на работещите в</b>', $times14, $alignCenter);
$sect->writeText('<b>'.((isset($f['firm_name'])) ? HTMLFormat($f['firm_name']) : '').' за '.$dbInst->extractYear($date_from, $date_to).' г.</b>', $times14, $alignCenter);
$sect->writeText('<b>'.HTMLFormat($firm_address).'</b>', $times14, $alignCenter);

$sect->addEmptyParagraph();
$sect->addEmptyParagraph();

$sect->writeText('1. Данни за работещите в предприятието:', $times12, $alignLeft);

$avg_workers = $objStats->avg_workers;
$avg_men = $objStats->avg_men;
$avg_women = $objStats->avg_women;
$sick_anual_workers = $objStats->sick_anual_workers;
if(isset($f['firm_name'])) {
	// hack asked by Asya from Viamed, Sofia
	if(false !== strpos($stm_name, 'ВИАМЕД')) {
		$avg_men = round($avg_men, 0);
		$avg_women = round($avg_women, 0);
		$avg_workers = $avg_men + $avg_women;
		$sick_anual_workers = round($objStats->sick_anual_workers);
	}
}

$data = array();
$data[] = array('Средно-списъчен състав на работещите', 'М', 'Ж');
$data[] = array('<b>'.$avg_workers.'</b>', '<b>'.$avg_men.'</b>', '<b>'.$avg_women.'</b>');
$colWidts = array(8, 2, 2);
$colAligns = array('center', 'center', 'center');
fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'plain');

$data = array();
$table = $objStats->getWorkersByPatientChartTable();
if(!empty($table)) {
	$data = fnExtractTableData($table);
}
$no_data = (!empty($data)) ? ':' : ': <b>Няма предоставени данни</b>.';
$sect->writeText('2. Описание на боледувалите работещи по данни от болничните листове'.$no_data, $times12, $alignLeft);
if(!empty($data)) {
	$colWidts = array(1, 1, 2, 7, 6);
	$colAligns = array('center', 'center', 'center', 'center', 'center');
	fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'plain');
	if($tbl = $objStats->getPatientChartsByNumCasesTable()) {
		fnGenerateChart($tbl['chart_data'], $imgname = 'primarylists_'.$firm_id, $title = 'Разпределение по абсолютен брой случаи (първични болнични листове)');
	}
}

$data = array();
$table = $objStats->getWorkersDaysOff30upTable($freq = 1);
if(!empty($table)) {
	$data = fnExtractTableData($table);
}
$no_data = (!empty($data)) ? ':' : ': <b>Няма предоставени данни</b>.';
$sect->writeText('3. Често и дълго боледували работещи'.$no_data, $times12, $alignLeft);
if(!empty($data)) {
	$colWidts = array(1, 1, 2, 7, 6);
	$colAligns = array('center', 'center', 'center', 'center', 'center');
	fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'plain');
}

$data = array();
$table = $objStats->getWorkersWithTelkTable($freq = 1);
if(!empty($table)) {
	$data = fnExtractTableData($table);
}
$no_data = (!empty($data)) ? ':' : ': <b>Няма предоставени данни</b>.';
$sect->writeText('4. Описание на работещите с експертно решение на ТЕЛК/НЕЛК за '.$dbInst->extractYear($date_from, $date_to).' г.'.$no_data, $times12, $alignLeft);
if(!empty($data)) {
	$colWidts = array(2, 3, 12);
	$colAligns = array('center', 'center', 'center');
	fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'plain');
}

$data = array();
$table = $objStats->getMedicalCheckupResultsTable();
if(!empty($table)) {
	$data = fnExtractTableData($table);
}
$no_data = (!empty($data)) ? ':' : ': <b>Няма предоставени данни</b>.';
$sect->writeText('5. Описание на резултатите от проведените периодични медицински прегледи'.$no_data, $times12, $alignLeft);
if(!empty($data)) {
	$colWidts = array(1, 1, 1.5, 4, 2.5, 2, 2.5, 2.5);
	$colAligns = array('center', 'center', 'center', 'center', 'center', 'center', 'center', 'center');
	fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'small');
}

$no_data = (!empty($objStats->num_diseases_medical_checkups)) ? ': <b>'.$objStats->num_diseases_medical_checkups.'</b>.' : ': <b>Няма предоставени данни</b>.';
$sect->writeText('Брой заболявания, открити при проведените задължителни периодични медицински прегледи'.$no_data, $times12, $alignLeft);
$data = array();
$table = $objStats->tbl_diseases_medical_checkups;
if(!empty($table)) {
	$data = fnExtractTableData($table);
}
if(!empty($data)) {
	$colWidts = array(12, 2, 3);
	$colAligns = array('left', 'center', 'center');
	fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'nosology');
	fnGenerateChart($objStats->chart_data, $imgname = 'prophylactic_'.$firm_id, $title = 'Разпределение по брой заболявания, открити при периодичните мед. прегледи');
}

$data = array();
$table = $objStats->getWorkersLabourAccidentsTable();
if(!empty($table)) {
	$data = fnExtractTableData($table);
}
$no_data = (!empty($data)) ? ':' : ': <b>Няма предоставени данни</b>.';
$sect->writeText('6. Описание на трудовите злополуки за '.$dbInst->extractYear($date_from, $date_to).' година – брой и причини'.$no_data, $times12, $alignLeft);
if(!empty($data)) {
	$colWidts = array(1, 3, 13);
	$colAligns = array('center', 'center', 'center');
	fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'plain');
}

$data = array();
$table = $objStats->getWorkersProDiseasesTable();
if(!empty($table)) {
	$data = fnExtractTableData($table);
}
$no_data = (!empty($data)) ? ':' : ': <b>Няма предоставени данни</b>.';
$sect->writeText('7. Описание на регистрираните професионални болести за '.$dbInst->extractYear($date_from, $date_to).' година – брой и диагнози'.$no_data, $times12, $alignLeft);
if(!empty($data)) {
	$colWidts = array(1, 3, 13);
	$colAligns = array('center', 'center', 'center');
	fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'plain');
}

//TODO
if(false !== strpos($s['stm_name'], 'МАРС-2001')) {
	
}

$sect->writeText('8. Анализ на връзката между данните за заболяемостта и трудовата дейност, изводи и препоръки:', $times12, $alignLeft);
$sect->writeText('Няма пряка връзка между регистрираните заболявания и условията на труд. Работодателят е предприел всички необходими мерки за ЗБУТ.', $times12, $alignLeft);

require('phprtflite/rtfend.php');