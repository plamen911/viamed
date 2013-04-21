<?php
// Test: http://localhost/stm2008/viamed/w_rtf_worker_card.php?checkup_id=9590
require('includes.php');

$checkup_id = (isset($_GET['checkup_id']) && is_numeric($_GET['checkup_id'])) ? intval($_GET['checkup_id']) : 0;
$f = $dbInst->getMedicalCheckupInfo($checkup_id);
if(!$f) {
	die('Липсва индентификатор на картата за профилактичен медицински преглед!');
}
$s = $dbInst->getStmInfo();

$stm_name = preg_replace('/\<br\s*\/?\>/', '', $s['stm_name']);

$firm_name = preg_replace('/[^A-Za-zА-Яа-я0-9\-_\.]/u', '', $f['firm_name']);
$worker_name = str_replace(' ', '_', (mb_substr($f['fname'], 0, 1, 'utf-8').' '.$f['lname']));

require_once("cyrlat.class.php");
$cyrlat = new CyrLat;
$filename = 'Karta_ot_prof_pregled_'.$cyrlat->cyr2lat($worker_name.'_'.$firm_name).'-'.$f['worker_id'];


require('phprtflite/rtfbegin.php');

$sect->writeText('<b>КАРТА ОТ ПРОФИЛАКТИЧЕН ПРЕГЛЕД</b>', $times20, $alignCenter);
$sect->writeText('<b>на</b>', $times14, $alignCenter);
$sect->writeText('<b>'.((isset($f)) ? mb_strtoupper($f['fname'].' '.$f['sname'].' '.$f['lname'], 'utf-8') : '').((isset($f['checkup_date_h']) && isset($f['birth_date2'])) ? ', '.worker_age($f['birth_date2'], $f['checkup_date_h']).' г.' : '').'</b>', $times14, $alignCenter);
$sect->writeText('<b>'.((isset($f)) ? mb_strtoupper($f['firm_name'],'utf-8') : '').((isset($f)) ? ' – '.mb_strtoupper($f['location_name'], 'utf-8') : '').'</b>', $times14, $alignCenter);

$sect->addEmptyParagraph();
$sect->addEmptyParagraph();

$sect->writeText('ЕГН: '.((isset($f)) ? HTMLFormat($f['egn']) : '')."\t\t\t          ".'Дата на прегледа: '.((isset($f)) ? HTMLFormat($f['checkup_date_h']).' г.' : ''), $times12, $alignLeft);
if(isset($f) && ($f['worker_location'] != '' || $f['address'] != '')) {
	$sect->writeText(((isset($f) && $f['worker_location'] != '') ? 'Гр./с. '.HTMLFormat($f['worker_location'].', '.$f['address']) : ''), $times12, $alignLeft);
}
if(isset($f) && $f['subdivision_name'] != '') {
	$sect->writeText(((isset($f)) ? HTMLFormat($f['subdivision_name']) : ''), $times12, $alignLeft);
}
if(isset($f) && $f['wplace_name'] != '') {
	$sect->writeText('Раб. място: '.((isset($f)) ? HTMLFormat($f['wplace_name']) : ''), $times12, $alignLeft);
}
$sect->addEmptyParagraph();

$table = $sect->addTable();
$table->addColumnsList(array(8, 8));

$i = 1; $table->addRow();
$table->writeToCell($i, 1, 'Ръст: ', $times12, $alignLeft);
$table->writeToCell($i, 2, (($f['worker_height'] != '') ? HTMLFormat($f['worker_height']).' см' : ''), $times12, $alignLeft);
$i++; $table->addRow();
$table->writeToCell($i, 1, 'Тегло: ', $times12, $alignLeft);
$table->writeToCell($i, 2, (($f['worker_weight'] != '') ? HTMLFormat($f['worker_weight']).' кг' : ''), $times12, $alignLeft);
$i++; $table->addRow();
$table->writeToCell($i, 1, 'RR сист: ', $times12, $alignLeft);
$table->writeToCell($i, 2, ((isset($f)) ? HTMLFormat($f['rr_syst']) : ''), $times12, $alignLeft);
$i++; $table->addRow();
$table->writeToCell($i, 1, 'RR диаст: ', $times12, $alignLeft);
$table->writeToCell($i, 2, ((isset($f)) ? HTMLFormat($f['rr_diast']) : ''), $times12, $alignLeft);

$flds = array('smoking' => 'Тютюнопушене', 'drinking' => 'Алкохол', 'fats' => 'Нерационално хранене', 'diet' => 'Диета', 'home_stress' => 'Стрес в дома', 'work_stress' => 'Стрес в работата', 'social_stress' => 'Социален стрес', 'video_display' => 'Видеодисплей повече от 1/2 от раб. време', 'hours_activity' => 'Физическа активност часа /', 'low_activity' => 'Намалена двигателна активност');
foreach ($flds as $key => $val) {
	if($f[$key]) {
		$i++; $table->addRow();
		$table->writeToCell($i, 1, $val.': ', $times12, $alignLeft);
		$cell = $table->getCell($i, 2);
		$checkbox = $cell->addCheckbox();
		if($f[$key]) { $checkbox->setChecked(); }
	}
}

if(isset($f['EKG']) && $f['EKG'] != '') {
	$sect->writeText('ЕКГ: '.HTMLFormat($f['EKG']), $times12, $alignLeft);
}
if(isset($f['x_ray']) && $f['x_ray'] != '') {
	$sect->writeText('Рентгенография: '.HTMLFormat($f['x_ray']), $times12, $alignLeft);
}
if(isset($f['echo_ray']) && $f['echo_ray'] != '') {
	$sect->writeText('Ехография: '.HTMLFormat($f['echo_ray']), $times12, $alignLeft);
}

if(isset($f) && ($f['left_eye'] != '' || $f['right_eye'] != '')) {
	$sect->writeText('Зрителна острота', $times12, $alignLeft);
	$sect->writeText('Ляво око: '.((isset($f['left_eye'])) ? HTMLFormat($f['left_eye']) : '').' / '.((isset($f['left_eye2'])) ? HTMLFormat($f['left_eye2']) : '').' dp'."\t\t".'Дясно око: '.((isset($f['right_eye'])) ? HTMLFormat($f['right_eye']) : '').' / '.((isset($f['right_eye2'])) ? HTMLFormat($f['right_eye2']) : '').' dp', $times12, $alignLeft);
}

if(isset($f) && ($f['VK'] != '' || $f['FEO1'] != '')) {
	$sect->writeText('Функционално изследване на дишането', $times12, $alignLeft);
	$sect->writeText('ВК: '.((isset($f['VK'])) ? HTMLFormat($f['VK']) : '').' ml'."\t\t\t".'ФЕО 1: '.((isset($f['FEO1'])) ? HTMLFormat($f['FEO1']) : '').' ml', $times12, $alignLeft);
}

if(isset($f['tifno']) && $f['tifno'] != '') {
	$sect->writeText('Показател на Тифно: '.HTMLFormat($f['tifno']), $times12, $alignLeft);
}

if(isset($f['hearing_loss']) && $f['hearing_loss'] != '') {
	$sect->writeText('Тонална аудиометрия', $times12, $alignLeft);
	$sect->writeText('Загуба на слуха: '.HTMLFormat($f['hearing_loss']), $times12, $alignLeft);
	$sect->writeText('Ляво ухо: '.((isset($f['left_eye'])) ? HTMLFormat($f['left_eye']) : '')."\t\t".'Дясно ухо: '.((isset($f['right_ear'])) ? HTMLFormat($f['right_ear']) : ''), $times12, $alignLeft);
	if(isset($f['hearing_diagnose']) && $f['hearing_diagnose'] != '') {
		$sect->writeText('Диагноза: '.HTMLFormat($f['hearing_diagnose']), $times12, $alignLeft);
	}
}

$sect->writeText('Фамилна обремененост: '.((!empty($f['fweights_descr'])) ? HTMLFormat($f['fweights_descr']) : '--'), $times12, $alignLeft);

$rows = $dbInst->getFamilyWeights($checkup_id);
if($rows) {
	$data = array();
	$data[] = array('МКБ', 'Диагноза');
	foreach ($rows as $row) {
		$mkb_id = HTMLFormat($row['mkb_id']);
		$mkb_desc = HTMLFormat($row['mkb_desc']);
		if($row['diagnosis'] != '') {
			$mkb_desc .= '<br>'.HTMLFormat($row['diagnosis']);
		}
		$data[] = array($mkb_id, $mkb_desc);
	}
	$colWidts = array(2, 14);
	$colAligns = array('center', 'left');
	fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'plain');		
}

$checkups = $dbInst->getLabCheckups($checkup_id);
$labs = $dbInst->getLabs();
if($checkups) {
	$sect->writeText('Лабораторни изследвания', $times12, $alignLeft);		
	$data = array();
	$data[] = array('Показател', 'Min', 'Max', 'Ниво', '');
	foreach ($checkups as $row) {
		$indicator_name = HTMLFormat($row['indicator_name']);
		$pdk_min = HTMLFormat($row['pdk_min']);
		$pdk_max = HTMLFormat($row['pdk_max']);
		$checkup_level = HTMLFormat($row['checkup_level']);
		$indicator_dimension = HTMLFormat($row['indicator_dimension']);
		$data[] = array($indicator_name, $pdk_min, $pdk_max, $checkup_level, $indicator_dimension);
	}
	$colWidts = array(6.5, 2.5, 2.5, 2.5, 2);
	$colAligns = array('center', 'center', 'center', 'center', 'center');
	fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'plain');	
}

$sect->writeText('Анамнеза: '.((!empty($f['anamnesis_descr'])) ? HTMLFormat($f['anamnesis_descr']) : '--'), $times12, $alignLeft);	

$rows = $dbInst->getAnamnesis($checkup_id);
if($rows) {
	$data = array();
	$data[] = array('МКБ', 'Диагноза');
	foreach ($rows as $row) {
		$mkb_id = HTMLFormat($row['mkb_id']);
		$mkb_desc = HTMLFormat($row['mkb_desc']);
		if($row['diagnosis'] != '') {
			$mkb_desc .= '<br>'.HTMLFormat($row['diagnosis']);
		}
		$data[] = array($mkb_id, $mkb_desc);
	}
	$colWidts = array(2, 14);
	$colAligns = array('center', 'left');
	fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'plain');
}

$rows = $dbInst->getDiseases($checkup_id);
if($rows) {
	$sect->writeText('Заболявания (диагнози)', $times12, $alignLeft);
	$data = array();
	$data[] = array('МКБ', 'Диагноза', 'Новооткрито');
	foreach ($rows as $row) {
		$mkb_id = HTMLFormat($row['mkb_id']);
		$mkb_desc = HTMLFormat($row['mkb_desc']);
		if($row['diagnosis'] != '') {
			$mkb_desc .= '<br>'.HTMLFormat($row['diagnosis']);
		}
		$is_new = ($row['is_new'] == '1') ? 'Да' : 'Не';
		$data[] = array($mkb_id, $mkb_desc, $is_new);
	}
	$colWidts = array(2, 11, 3);
	$colAligns = array('center', 'left', 'center');
	fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'plain');
}

$sect->writeText('<b>- Заключение на лекаря/лекарите, провели прегледите:</b>', $times12, $alignLeft);
$rows = $dbInst->getDoctorsDesc($checkup_id);
if($rows) {
	foreach ($rows as $row) {
		if($row['conclusion'] == '') continue;
		$sect->writeText($dbInst->my_mb_ucfirst(HTMLFormat($row['SpecialistName'])).': '.HTMLFormat($row['conclusion']), $times12, $alignLeft);
	}
}

$sect->writeText('<b>- Заключение на службата по трудова медицина:</b>', $times12, $alignLeft);
switch ($f['stm_conclusion']) {
	case '1':
		$sect->writeText('Може да изпълнява посочената длъжност/професия '.HTMLFormat($f['position_name']).' в '.HTMLFormat($f['firm_name']), $times12,$alignLeft);
		break;
	case '2':
		$sect->writeText('Може да изпълнява посочената длъжност/професия '.HTMLFormat($f['position_name']).' в '.HTMLFormat($f['firm_name']).' при следните условия:', $times12, $alignLeft);
		$sect->writeText(HTMLFormat($f['stm_conditions']), $times12, $alignLeft);
		break;
	case '0':
		$sect->writeText('Не може да изпълнява посочената длъжност/професия '.HTMLFormat($f['position_name']).' в '.HTMLFormat($f['firm_name']), $times12, $alignLeft);
		break;
  	case '3':
  		$sect->writeText('Не може да се прецени пригодността на работещия да изпълнява посочената длъжност/професия '.HTMLFormat($f['position_name']).' в '.HTMLFormat($f['firm_name']), $times12, $alignLeft);
	default:
		break;
}

$sect->addEmptyParagraph();
$sect->addEmptyParagraph();

$timesFooter = $times14;
require('phprtflite/rtfend.php');
