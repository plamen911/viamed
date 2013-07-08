<?php
// http://localhost/stm2008/hipokrat/w_rtf_analiz_31-100.php?firm_id=187&date_from=01.01.2011&date_to=31.12.2012
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

$data = array();
$data[] = array('Средно-списъчен състав на работещите', 'М', 'Ж');
$data[] = array('<b>'.$avg_workers.'</b>', '<b>'.$avg_men.'</b>', '<b>'.$avg_women.'</b>');
$colWidts = array(8, 2, 2);
$colAligns = array('center', 'center', 'center');
fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'plain');

$sect->writeText('2. Данни за боледувалите работещи за съответната календарна година:', $times12, $alignLeft);

$sick_anual_workers = (!empty($sick_anual_workers)) ? $sick_anual_workers : 'Няма предоставени данни';
$sect->writeText('2.1. Брой работещи с регистрирани заболявания (по данни от болничните листове): <b>'.$sick_anual_workers.'</b>.', $times12, $alignLeft);

$primary_charts = (!empty($objStats->primary_charts)) ? $objStats->primary_charts : 'Няма предоставени данни';
$sect->writeText('2.2. Абсолютен брой случаи (първични болнични листове) – общо и по нозологична структура, съгласно МКБ-10: <b>'.$primary_charts.'</b>.', $times12, $alignLeft);
$data = array();
$tbl = $objStats->getPatientChartsByNumCasesTable();
if(!empty($tbl['table'])) {
	$data = fnExtractTableData($tbl['table']);
}
if(!empty($data)) {
	$colWidts = array(12, 2, 3);
	$colAligns = array('left', 'center', 'center');
	fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'nosology');
	fnGenerateChart($tbl['chart_data'], $imgname = 'primarylists_'.$firm_id, $title = 'Разпределение по абсолютен брой случаи (първични болнични листове)');
}

$days_off = (!empty($objStats->days_off)) ? $objStats->days_off : 'Няма предоставени данни';
$sect->writeText('2.3. Брой на дните с временна неработоспособност (общо от всички болнични листове – първични и продължения) – общо и по нозологична структура, съгласно МКБ-10: <b>'.$days_off.'</b>.', $times12, $alignLeft);
$data = array();
$tbl = $objStats->getPatientChartsByDaysOffTable();
if(!empty($tbl['table'])) {
	$data = fnExtractTableData($tbl['table']);
}
if(!empty($data)) {
	$colWidts = array(12, 2, 3);
	$colAligns = array('left', 'center', 'center');
	fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'nosology');
	fnGenerateChart($tbl['chart_data'], $imgname = 'numdaysoff_'.$firm_id, $title = 'Разпределение по брой на дните с временна неработоспособност');
}

$primary_charts_days_off_3down = (!empty($objStats->primary_charts_days_off_3down)) ? $objStats->primary_charts_days_off_3down : 'Няма предоставени данни';
$sect->writeText('2.4. Брой случаи с временна неработоспособност с продължителност до 3 дни (първични болнични листове): <b>'.$primary_charts_days_off_3down.'</b>.', $times12, $alignLeft);

$num_workers_primary_charts_4up = (!empty($objStats->num_workers_primary_charts_4up)) ? $objStats->num_workers_primary_charts_4up : 'Няма предоставени данни';
$sect->writeText('2.5. Брой на работещите с 4 и повече случаи с временна неработоспособност (първични болнични листове): <b>'.$num_workers_primary_charts_4up.'</b>.', $times12, $alignLeft);

$num_workers_days_off_30up = (!empty($objStats->num_workers_days_off_30up)) ? $objStats->num_workers_days_off_30up : 'Няма предоставени данни';
$sect->writeText('2.6. Брой на работещите с 30 и повече дни временна неработоспособност от заболявания: <b>'.$num_workers_days_off_30up.'</b>.', $times12, $alignLeft);

$num_pro_diseases = (!empty($objStats->num_pro_diseases)) ? $objStats->num_pro_diseases : 'Няма предоставени данни';
$sect->writeText('2.7. Брой регистрирани професионални болести: <b>'.$num_pro_diseases.'</b>.', $times12, $alignLeft);

$num_workers_pro_diseases = (!empty($objStats->num_workers_pro_diseases)) ? $objStats->num_workers_pro_diseases : 'Няма предоставени данни';
$sect->writeText('2.8. Брой работещи с регистрирани професионални болести: <b>'.$num_workers_pro_diseases.'</b>.', $times12, $alignLeft);

$num_workers_with_telk = (!empty($objStats->num_workers_with_telk)) ? $objStats->num_workers_with_telk : 'Няма предоставени данни';
$sect->writeText('2.9. Брой на работещите с експертно решение на ТЕЛК за заболяване с трайна неработоспособност: <b>'.$num_workers_with_telk.'</b>.', $times12, $alignLeft);

$sect->writeText('3. Данни за проведените задължителни периодични медицински прегледи през съответната календарна година:', $times12, $alignLeft);

$avg_workers = (!empty($objStats->avg_workers)) ? $objStats->avg_workers : 'Няма предоставени данни';
$sect->writeText('3.1. Брой на работещите, подлежащи на задължителни периодични медицински прегледи: <b>'.$avg_workers.'</b>.', $times12, $alignLeft);

$num_workers_medical_checkups = (!empty($objStats->num_workers_medical_checkups)) ? $objStats->num_workers_medical_checkups : 'Няма предоставени данни';
$sect->writeText('3.2. Брой на работещите, обхванати със задължителни периодични медицински прегледи: <b>'.$num_workers_medical_checkups.'</b>.', $times12, $alignLeft);

$sect->addEmptyParagraph();

$sect->writeText('II. Анализ и оценка на показателите, характеризиращи здравното състояние на работещите', $times12, $alignLeft);

$sect->writeText('1. Честота на боледувалите работещи със заболяемост с временна неработоспособност: '.str_replace('<b style=\'mso-bidi-font-weight:normal\'>', '<b>', $objStats->freqSickWorkersTempDisability()).'.', $times12, $alignLeft);

$sect->writeText('2. Честота на случаите с временна неработоспособност: '.str_replace('<b style=\'mso-bidi-font-weight:normal\'>', '<b>', $objStats->freqCasesTempDisability()).'.', $times12, $alignLeft);

$sect->writeText('3. Честота на трудозагубите с временна неработоспособност: '.str_replace('<b style=\'mso-bidi-font-weight:normal\'>', '<b>', $objStats->freqDaysOffTempDisability()).'.', $times12, $alignLeft);

$avg_length_of_chart = (!empty($objStats->avg_length_of_chart)) ? $objStats->avg_length_of_chart : 'Няма предоставени данни';
$sect->writeText('4. Средна продължителност на един случай с временна неработоспособност: <b>'.$avg_length_of_chart.'</b>.', $times12, $alignLeft);

$sect->writeText('5. Честота на работещите с професионални болести: '.str_replace('<b style=\'mso-bidi-font-weight:normal\'>', '<b>', $objStats->freqWorkersProDiseases()).'.', $times12, $alignLeft);

$sect->writeText('6. Честота на работещите с трудови злополуки: '.str_replace('<b style=\'mso-bidi-font-weight:normal\'>', '<b>', $objStats->freqWorkersLabourAccidents()).'.', $times12, $alignLeft);

$sect->writeText('7. Честота на работещите със заболяемост с трайна неработоспособност: '.str_replace('<b style=\'mso-bidi-font-weight:normal\'>', '<b>', $objStats->freqWorkersWithTelk()).'.', $times12, $alignLeft);

$data = array();
$table = $objStats->getWorkersDaysOff30upTable($freq = 1);
if(!empty($table)) {
	$data = fnExtractTableData($table);
}
$no_data = (!empty($data)) ? ':' : ': <b>Няма предоставени данни</b>.';
$sect->writeText('8. Описание на често и дълго боледували работещи – брой, диагнози (код по МКБ-10)'.$no_data, $times12, $alignLeft);
if(!empty($data)) {
	$colWidts = array(1, 1, 2, 7, 6);
	$colAligns = array('center', 'center', 'center', 'center', 'center');
	fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'plain');
}

$sect->writeText('По основни признаци, показателите на заболеваемостта с временна неработоспособност са представени в следната таблица:', $times12, $alignLeft);
$table = $objStats->getAnaliticsTable();
if(!empty($table)) {
	$data = fnExtractTableData($table);
}
if(!empty($data)) {
	$data1 = array_slice($data, 0, 4);
	$data2 = array_slice($data, 5);
	array_unshift($data2, $data1[0]);

	$colWidts = array(2.8, 2.2, 2, 2, 2, 2, 2, 2);
	$colAligns = array('center', 'center', 'center', 'center', 'center', 'center', 'center', 'center');
	fnGenerateTable($data1, $colWidts, $colAligns, $tableType = 'pro_groups');

	$sect->writeText('Възрастови групи', $times12, $alignLeft);
	fnGenerateTable($data2, $colWidts, $colAligns, $tableType = 'pro_groups');
}

$data = array();
$table = $objStats->getWorkersWithTelkTable($freq = 1);
if(!empty($table)) {
	$data = fnExtractTableData($table);
}
$no_data = (!empty($data)) ? ':' : ': <b>Няма предоставени данни</b>.';
$sect->writeText('9. Описание на работещите с експертно решение на ТЕЛК/НЕЛК за '.$dbInst->extractYear($date_from, $date_to).' г.'.$no_data, $times12, $alignLeft);
if(!empty($data)) {
	$colWidts = array(1, 3, 10);
	$colAligns = array('center', 'center', 'center');
	fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'plain');
}

$data = array();
$table = $objStats->getWorkersLabourAccidentsTable();
if(!empty($table)) {
	$data = fnExtractTableData($table);
}
$no_data = (!empty($data)) ? ':' : ': <b>Няма предоставени данни</b>.';
$sect->writeText('10. Описание на трудовите злополуки за '.$dbInst->extractYear($date_from, $date_to).' година – брой и причини'.$no_data, $times12, $alignLeft);
if(!empty($data)) {
	$colWidts = array(1, 3, 10);
	$colAligns = array('center', 'center', 'center');
	fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'plain');
}

$data = array();
$table = $objStats->getWorkersProDiseasesTable();
if(!empty($table)) {
	$data = fnExtractTableData($table);
}
$no_data = (!empty($data)) ? ':' : ': <b>Няма предоставени данни</b>.';
$sect->writeText('11. Описание на регистрираните професионални болести за '.$dbInst->extractYear($date_from, $date_to).' г. – брой и диагнози'.$no_data, $times12, $alignLeft);
if(!empty($data)) {
	$colWidts = array(1, 5, 8);
	$colAligns = array('center', 'center', 'center');
	fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'plain');
}

$sect->writeText('12. Описание на резултатите от задължителни периодични медицински прегледи през съответната календарна година:', $times12, $alignLeft);

$num_diseases_medical_checkups = (!empty($objStats->num_diseases_medical_checkups)) ? $objStats->num_diseases_medical_checkups : 'Няма предоставени данни';
$sect->writeText('- Брой заболявания, открити при проведените задължителни периодични медицински прегледи: <b>'.$num_diseases_medical_checkups.'</b>.', $times12, $alignLeft);
$data = array();
$table = $objStats->tbl_diseases_medical_checkups;
if(!empty($table)) {
	$data = fnExtractTableData($table);
}
if(!empty($data)) {
	$sect->writeText('- По нозологична структура, съгласно МКБ-10', $times12, $alignLeft);
	$colWidts = array(12, 2, 3);
	$colAligns = array('left', 'center', 'center');
	fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'nosology');
	fnGenerateChart($objStats->chart_data, $imgname = 'prophylactic_'.$firm_id, $title = 'Разпределение по брой заболявания, открити при периодичните мед. прегледи');
}

$num_ill_workers_medical_checkups = (!empty($objStats->num_ill_workers_medical_checkups)) ? 'общо <b>'.$objStats->num_ill_workers_medical_checkups.'</b>.' : '<b>Няма предоставени данни</b>.';
$sect->writeText('- Брой работещи със заболявания, открити при проведените задължителни периодични медицински прегледи: '.$num_ill_workers_medical_checkups, $times12, $alignLeft);

$data = array();
$table = $objStats->tbl_ill_workers_medical_checkups;
if(!empty($table)) {
	$data = fnExtractTableData($table);
}
$no_data = (!empty($data)) ? ':' : ': <b>Няма предоставени данни</b>.';
$sect->writeText('- По нозологична структура, съгласно МКБ-10'.$no_data, $times12, $alignLeft);
if(!empty($data)) {
	$colWidts = array(9, 3);
	$colAligns = array('center', 'center');
	fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'nosology');
}

$sect->writeText('13. Анализ на връзката между данните за заболяемостта и трудовата дейност, изводи и препоръки:', $times12, $alignLeft);
$sect->writeText('Няма пряка връзка между регистрираните заболявания и условията на труд. Работодателят е предприел всички необходими мерки за ЗБУТ.', $times12, $alignLeft);

require('phprtflite/rtfend.php');
die();
