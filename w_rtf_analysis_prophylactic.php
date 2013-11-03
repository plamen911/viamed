<?php
// http://localhost/stm2008/stm_zdrave_2/w_rtf_analysis_prophylactic.php?firm_id=174&date_from=01.01.2009&date_to=31.12.2013
require('includes.php');
require('class.stmstats.php');
require('class.prophylactic.php');
require('libchart/classes/libchart.php');

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
$Prophylactic = new Prophylactic($firm_id, $date_from, $date_to);

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
$filename = 'Prophylactic_'.$cyrlat->cyr2lat($period.'_'.$firm_name);

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

$sect->writeText('<b>Анализ на профилактичните прегледи</b>', $times20, $alignCenter);
$sect->writeText('<b>на работещите в</b>', $times14, $alignCenter);
$sect->writeText('<b>'.((isset($f['firm_name'])) ? HTMLFormat($f['firm_name']) : '').' за '.$dbInst->extractYear($date_from, $date_to).' г.</b>', $times14, $alignCenter);
$sect->writeText('<b>'.HTMLFormat($firm_address).'</b>', $times14, $alignCenter);

$sect->addEmptyParagraph();
$sect->addEmptyParagraph();

$sect->writeText('1. Данни за проведените задължителни периодични медицински прегледи през съответната календарна година:', $times12, $alignLeft);

$cnt = $Prophylactic->getNumWorkersSubject();
$cnt = (!empty($cnt)) ? $cnt : 'Няма предоставени данни';
$sect->writeText('1.1. Брой на работещите, подлежащи на задължителни периодични мед. прегледи: <b>'.$cnt.'</b>.', $times12, $alignLeft);

$cnt = (!empty($Prophylactic->num_workers_medical_checkups)) ? $Prophylactic->num_workers_medical_checkups : 'Няма предоставени данни';
$sect->writeText('1.2. Брой на работещите, обхванати със задължителни периодични мед. прегледи: <b>'.$cnt.'</b>.', $times12, $alignLeft);

$sect->writeText('На прегледите са се явили <b>'.$Prophylactic->getNumWorkersPassed().'</b> от общо <b>'.$Prophylactic->getNumWorkersSubject().'</b> подлежащи на преглед работещи, което представлява <b>'.$Prophylactic->getPercentWorkersPassed().'%</b>. Общо броят на работещите във фирмата е <b>'.$Prophylactic->getNumWorkersSubject().'</b>, а средносписъчния брой работещи по формула е <b>'.$Prophylactic->avg_workers.'</b>.', $times12, $alignLeft);

//complex table with merged cells start
$sect->writeText('- Разпределение по пол', $times12, $alignLeft);
$table = $sect->addTable();
$table->addRows(3);
$table->addColumnsList(array(2, 3, 2, 2, 2, 2, 2, 2));
//borders
$border = PHPRtfLite_Border::create($rtf, 1, '#000000');
$table->setBorderForCellRange($border, 1, 1, 3, 8);

$table->mergeCellRange(1, 1, 2, 1);
$cell = $table->getCell(1, 1);
$cell->setVerticalAlignment(PHPRtfLite_Table_Cell::VERTICAL_ALIGN_CENTER);
$cell->setTextAlignment(PHPRtfLite_Table_Cell::TEXT_ALIGN_CENTER);
$cell->writeText('Общо', $times10);

$table->mergeCellRange(1, 2, 2, 2);
$cell = $table->getCell(1, 2);
$cell->setVerticalAlignment(PHPRtfLite_Table_Cell::VERTICAL_ALIGN_CENTER);
$cell->setTextAlignment(PHPRtfLite_Table_Cell::TEXT_ALIGN_CENTER);
$cell->writeText('Подлежащи на преглед', $times10);

$table->mergeCellRange(1, 3, 1, 4);
$cell = $table->getCell(1, 3);
$cell->setVerticalAlignment(PHPRtfLite_Table_Cell::VERTICAL_ALIGN_CENTER);
$cell->setTextAlignment(PHPRtfLite_Table_Cell::TEXT_ALIGN_CENTER);
$cell->writeText('Явили се на мед. преглед'."\r\n".'(% от подлежащите)', $times10);

$table->mergeCellRange(1, 5, 1, 6);
$cell = $table->getCell(1, 5);
$cell->setVerticalAlignment(PHPRtfLite_Table_Cell::VERTICAL_ALIGN_CENTER);
$cell->setTextAlignment(PHPRtfLite_Table_Cell::TEXT_ALIGN_CENTER);
$cell->writeText('От тях - мъже'."\r\n".'(% от прегледаните)', $times10);

$table->mergeCellRange(1, 7, 1, 8);
$cell = $table->getCell(1, 7);
$cell->setVerticalAlignment(PHPRtfLite_Table_Cell::VERTICAL_ALIGN_CENTER);
$cell->setTextAlignment(PHPRtfLite_Table_Cell::TEXT_ALIGN_CENTER);
$cell->writeText('От тях - жени'."\r\n".'(% от прегледаните)', $times10);

$cell = $table->getCell(2, 3);
$cell->setTextAlignment(PHPRtfLite_Table_Cell::TEXT_ALIGN_CENTER);
$cell->writeText('Брой', $times10);

$cell = $table->getCell(2, 4);
$cell->setTextAlignment(PHPRtfLite_Table_Cell::TEXT_ALIGN_CENTER);
$cell->writeText('%', $times10);

$cell = $table->getCell(2, 5);
$cell->setTextAlignment(PHPRtfLite_Table_Cell::TEXT_ALIGN_CENTER);
$cell->writeText('Брой', $times10);

$cell = $table->getCell(2, 6);
$cell->setTextAlignment(PHPRtfLite_Table_Cell::TEXT_ALIGN_CENTER);
$cell->writeText('%', $times10);

$cell = $table->getCell(2, 7);
$cell->setTextAlignment(PHPRtfLite_Table_Cell::TEXT_ALIGN_CENTER);
$cell->writeText('Брой', $times10);

$cell = $table->getCell(2, 8);
$cell->setTextAlignment(PHPRtfLite_Table_Cell::TEXT_ALIGN_CENTER);
$cell->writeText('%', $times10);

//the data
$data = array();
$data[0] = $Prophylactic->getNumWorkersSubject();
$data[1] = $Prophylactic->getNumWorkersSubject();
$data[2] = $Prophylactic->getNumWorkersPassed();
$data[3] = $Prophylactic->getPercentWorkersPassed();
$data[4] = $Prophylactic->getNumMenPassed();
$data[5] = $Prophylactic->getPercentMenPassed();
$data[6] = $Prophylactic->getNumWomenPassed();
$data[7] = $Prophylactic->getPercentWomenPassed();

for($i = 0; $i < count($data); $i++) {
	$cell = $table->getCell(3, ($i + 1));
	$cell->setTextAlignment(PHPRtfLite_Table_Cell::TEXT_ALIGN_CENTER);
	$cell->writeText($data[$i], $times10);
}
//complex table with merged cells end

//pie chart for complex table with merged cells start
$chart = new PieChart(500, 250);
/*$chart->getPlot()->getPalette()->setPieColor(array(
	//new Color(0, 255, 0),
	//new Color(255, 0, 0)
	new Color(250, 128, 114),
	new Color(152, 251, 152)
));*/
$dataSet = new XYDataSet();
$dataSet->addPoint(new Point('Мъже ('.$data[5].')', $data[5]));
$dataSet->addPoint(new Point('Жени ('.$data[7].')', $data[7]));
$chart->setDataSet($dataSet);
$chart->setTitle('Разпределение по ПОЛ');
$chart->render('libchart/generated/prophylactic_by_sex_'.$firm_id.'.png');
$sect->addImage('libchart/generated/prophylactic_by_sex_'.$firm_id.'.png', null);
//pie chart for complex table with merged cells end

$sect->writeText('- Разпределение по възраст', $times12, $alignLeft);
$colWidts = array(4, 2, 2, 2, 2, 2);
$colAligns = array('left', 'center', 'center', 'center', 'center', 'center');

$data = array();
$data[] = array('<b>Възраст</b>', '<b>До 25 г.</b>', '<b>25 - 35 г.</b>', '<b>35 - 45 г.</b>', '<b>45 - 55 г.</b>', '<b>Над 55 г.</b>');
$data[] = array('Мъже', $Prophylactic->getNumMenPassedByAge(0, 25), $Prophylactic->getNumMenPassedByAge(25, 35), $Prophylactic->getNumMenPassedByAge(35, 45), $Prophylactic->getNumMenPassedByAge(45, 55), $Prophylactic->getNumMenPassedByAge(55, 555));
$data[] = array('Жени', $Prophylactic->getNumWomenPassedByAge(0, 25), $Prophylactic->getNumWomenPassedByAge(25, 35), $Prophylactic->getNumWomenPassedByAge(35, 45), $Prophylactic->getNumWomenPassedByAge(45, 55), $Prophylactic->getNumWomenPassedByAge(55, 555));
$data[] = array('Процент за мъже', $Prophylactic->getPercentMenPassedByAge(0, 25), $Prophylactic->getPercentMenPassedByAge(25, 35), $Prophylactic->getPercentMenPassedByAge(35, 45), $Prophylactic->getPercentMenPassedByAge(45, 55), $Prophylactic->getPercentMenPassedByAge(55, 555));
$data[] = array('Процент за жени', $Prophylactic->getPercentWomenPassedByAge(0, 25), $Prophylactic->getPercentWomenPassedByAge(25, 35), $Prophylactic->getPercentWomenPassedByAge(35, 45), $Prophylactic->getPercentWomenPassedByAge(45, 55), $Prophylactic->getPercentWomenPassedByAge(55, 555));

$percentByAge25down = $Prophylactic->getPercentWorkersPassedByAge(0, 25);
$percentByAge25_25 = $Prophylactic->getPercentWorkersPassedByAge(25, 35);
$percentByAge35_45 = $Prophylactic->getPercentWorkersPassedByAge(35, 45);
$percentByAge45_55 = $Prophylactic->getPercentWorkersPassedByAge(45, 55);
$percentByAge55up = $Prophylactic->getPercentWorkersPassedByAge(55, 555);

$data[] = array('Процент общо', $percentByAge25down, $percentByAge25_25, $percentByAge35_45, $percentByAge45_55, $percentByAge55up);

fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'small');

//pie chart start
$chart = new PieChart(500, 250);
$dataSet = new XYDataSet();
$dataSet->addPoint(new Point('До 25 г. ('.$percentByAge25down.')', $percentByAge25down));
$dataSet->addPoint(new Point('25 - 35 г. ('.$percentByAge25_25.')', $percentByAge25_25));
$dataSet->addPoint(new Point('35 - 45 г. ('.$percentByAge35_45.')', $percentByAge35_45));
$dataSet->addPoint(new Point('45 - 55 г. ('.$percentByAge45_55.')', $percentByAge45_55));
$dataSet->addPoint(new Point('Над 55 г. ('.$percentByAge55up.')', $percentByAge55up));
$chart->setDataSet($dataSet);
$chart->setTitle('Разпределение по ВЪЗРАСТ');
$chart->render('libchart/generated/prophylactic_by_age_'.$firm_id.'.png');
$sect->addImage('libchart/generated/prophylactic_by_age_'.$firm_id.'.png', null);
//pie chart end

$sect->writeText('- Разпределение по професионален трудов стаж в предпрятието', $times12, $alignLeft);
$colWidts = array(4, 2, 2, 2, 2);
$colAligns = array('left', 'center', 'center', 'center', 'center');

$data = array();
$data[] = array('<b>Години</b>', '<b>До 3 г.</b>', '<b>3 - 5 г.</b>', '<b>5 - 10 г.</b>', '<b>Над 10 г.</b>');
$data[] = array('Мъже', $Prophylactic->getNumMenPassedByWPos(0, 3), $Prophylactic->getNumMenPassedByWPos(3, 5), $Prophylactic->getNumMenPassedByWPos(5, 10), $Prophylactic->getNumMenPassedByWPos(10, 555));
$data[] = array('Жени', $Prophylactic->getNumWomenPassedByWPos(0, 3), $Prophylactic->getNumWomenPassedByWPos(3, 5), $Prophylactic->getNumWomenPassedByWPos(5, 10), $Prophylactic->getNumWomenPassedByWPos(10, 555));
$data[] = array('Процент за мъже', $Prophylactic->getPercentMenPassedByWPos(0, 3), $Prophylactic->getPercentMenPassedByWPos(3, 5), $Prophylactic->getPercentMenPassedByWPos(5, 10), $Prophylactic->getPercentMenPassedByWPos(10, 555));
$data[] = array('Процент за жени', $Prophylactic->getPercentWomenPassedByWPos(0, 3), $Prophylactic->getPercentWomenPassedByWPos(3, 5), $Prophylactic->getPercentWomenPassedByWPos(5, 10), $Prophylactic->getPercentWomenPassedByWPos(10, 555));

$percentByWPos3down = $Prophylactic->getPercentWorkersPassedByWPos(0, 3);
$percentByWPos3_5 = $Prophylactic->getPercentWorkersPassedByWPos(3, 5);
$percentByWPos5_10 = $Prophylactic->getPercentWorkersPassedByWPos(5, 10);
$percentByWPos10up = $Prophylactic->getPercentWorkersPassedByWPos(10, 555);

$data[] = array('Процент общо', $percentByWPos3down, $percentByWPos3_5, $percentByWPos5_10, $percentByWPos10up);

fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'small');

//pie chart start
$chart = new PieChart(500, 250);
$dataSet = new XYDataSet();
$dataSet->addPoint(new Point('До 3 г. ('.$percentByWPos3down.')', $percentByWPos3down));
$dataSet->addPoint(new Point('3 - 5 г. ('.$percentByWPos3_5.')', $percentByWPos3_5));
$dataSet->addPoint(new Point('5 - 10 г. ('.$percentByWPos5_10.')', $percentByWPos5_10));
$dataSet->addPoint(new Point('Над 10 г. ('.$percentByWPos10up.')', $percentByWPos10up));
$chart->setDataSet($dataSet);
$chart->setTitle('Разпределение по ПРОФ. ТРУДОВ СТАЖ');
$chart->render('libchart/generated/prophylactic_by_wpos_'.$firm_id.'.png');
$sect->addImage('libchart/generated/prophylactic_by_wpos_'.$firm_id.'.png', null);
//pie chart end

$cnt = (!empty($Prophylactic->num_diseases_medical_checkups)) ? $Prophylactic->num_diseases_medical_checkups : 'Няма предоставени данни';
$sect->writeText('1.3. Брой заболявания, открити при проведените задължителни периодични медицински прегледи: <b>'.$cnt.'</b>.', $times12, $alignLeft);
$data = array();
$table = $Prophylactic->tbl_diseases_medical_checkups;
if(!empty($table)) {
	$data = fnExtractTableData($table);
}
if(!empty($data)) {
	$sect->writeText('- По нозологична структура, съгласно МКБ-10', $times12, $alignLeft);
	$colWidts = array(12, 2, 3);
	$colAligns = array('left', 'center', 'center');
	fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'nosology');
	fnGenerateChart($Prophylactic->chart_data, $imgname = 'prophylactic_'.$firm_id, $title = 'Разпределение по брой заболявания, открити при периодичните мед. прегледи');
}

$rows = $Prophylactic->getDiseasesByWorkPosition();
if(!empty($rows)) {
	$sect->writeText('- Структура на болестите по длъжност и пол - заболявания, установени при профилактични прегледи:', $times12, $alignLeft);
	$data = array();
	$data[] = array('<b>Длъжност</b>', '<b>Заболявания (МКБ)</b>', '<b>Брой при мъже</b>', '<b>Брой при жени</b>', '<b>Относителен дял в %'."\r\n".'(общо за двата пола)</b>');
	foreach ($rows as $row) {
		$data[] = array($row['position_name'], $row['mkb'], $row['men'], $row['women'], $row['percent']);
	}
	
	$colWidts = array(3.5, 7, 1.5, 1.5, 3.5);
	$colAligns = array('left', 'left', 'center', 'center', 'center');
	fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'pro_groups');
}

$rows = $Prophylactic->getDiseasesByMkb();
if(!empty($rows)) {
	$sect->writeText('- Структура на болестите - заболявания, установени при профилактични прегледи:', $times12, $alignLeft);
	$data = array();
	$data[] = array('<b>Заболявания (МКБ)</b>', '<b>Брой при мъже</b>', '<b>Брой при жени</b>', '<b>Относителен дял в %'."\r\n".'(общо за двата пола)</b>');
	foreach ($rows as $row) {
		$data[] = array($row['mkb'], $row['men'], $row['women'], $row['percent']);
	}
	
	$colWidts = array(9.5, 2, 2, 3.5);
	$colAligns = array('left', 'center', 'center', 'center', 'center');
	fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'pro_groups');
}

$rows = $Prophylactic->getDiseasesByAge();
if(!empty($rows)) {
	$sect->writeText('- Структура на болестите по възраст и пол - заболявания, установени при профилактични прегледи:', $times12, $alignLeft);
	$data = array();
	$data[] = array('<b>Възрастова група</b>', '<b>Заболявания (МКБ)</b>', '<b>Брой при мъже</b>', '<b>Брой при жени</b>', '<b>Относителен дял в %'."\r\n".'(общо за двата пола)</b>');
	foreach ($rows as $row) {
		$data[] = array($row['age_group'], $row['mkb'], $row['men'], $row['women'], $row['percent']);
	}
	
	$colWidts = array(2, 7.5, 2, 2, 3.5);
	$colAligns = array('left', 'left', 'center', 'center', 'center', 'center');
	fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'pro_groups');
}

$cnt = (!empty($Prophylactic->num_ill_workers_medical_checkups)) ? 'общо <b>'.$Prophylactic->num_ill_workers_medical_checkups.'</b>.' : '<b>Няма предоставени данни</b>.';
$sect->writeText('1.4. Брой работещи със заболявания, открити при проведените задължителни периодични медицински прегледи: '.$cnt, $times12, $alignLeft);

$data = array();
$table = $Prophylactic->tbl_ill_workers_medical_checkups;
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

$sect->writeText('2. Анализ на връзката между данните за заболяемостта и трудовата дейност, изводи и препоръки:', $times12, $alignLeft);
$sect->writeText('Няма пряка връзка между регистрираните заболявания и условията на труд. Работодателят е предприел всички необходими мерки за ЗБУТ.', $times12, $alignLeft);

require('phprtflite/rtfend.php');
die();
