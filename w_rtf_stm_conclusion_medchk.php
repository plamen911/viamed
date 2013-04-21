<?php
// Test: http://localhost/stm2008/viamed/w_stm_conclusion_medchk.php?checkup_id=9066
require('includes.php');

$precheckup_id = (isset($_GET['precheckup_id']) && is_numeric($_GET['precheckup_id'])) ? intval($_GET['precheckup_id']) : 0;

$worker_id = $firm_id = 0;
$checkup_id = (isset($_GET['checkup_id']) && is_numeric($_GET['checkup_id'])) ? intval($_GET['checkup_id']) : 0;
$f = $dbInst->getMedicalCheckupInfo($checkup_id);
if(!$f) {
	die('Липсва индентификатор на картата за профилактичен медицински преглед!');
}
$worker_id = $f['worker_id'];
$firm_id = $f['firm_id'];

$s = $dbInst->getStmInfo();

$stm_name = preg_replace('/\<br\s*\/?\>/', '', $s['stm_name']);

$line = $dbInst->getFirmInfo($f['firm_id']);

$firm_name = preg_replace('/[^A-Za-zА-Яа-я0-9\-_\.]/u', '', $line['firm_name']);
$worker_name = str_replace(' ', '_', (mb_substr($f['fname'], 0, 1, 'utf-8').' '.$f['lname']));

require_once("cyrlat.class.php");
$cyrlat = new CyrLat;
$filename = 'Zaklyuchenie_prof_pregled_'.$cyrlat->cyr2lat($worker_name.'_'.$firm_name).'-'.$f['worker_id'];


require('phprtflite/rtfbegin.php');

$sect->writeText('<b>З А К Л Ю Ч Е Н И Е</b>', $times20, $alignCenter);
$sect->addEmptyParagraph();
$sect->writeText('<b>от '.mb_strtoupper(html_entity_decode($dbInst->shortStmName($stm_name)), 'utf-8').'</b>', $times14, $alignCenter);
$sect->writeText('<b>за пригодността на лицето '.((isset($f)) ? mb_strtoupper($f['fname'].' '.$f['sname'].' '.$f['lname'], 'utf-8') : '').'</b>', $times14, $alignCenter);
$sect->writeText('<b>да изпълнява длъжността '.((isset($f)) ? mb_strtoupper($f['position_name'], 'utf-8') : '').'</b>', $times14, $alignCenter);
$sect->writeText('<b>в '.((isset($line)) ? mb_strtoupper($line['firm_name'], 'utf-8'):'').((isset($line)) ? ' - '.$line['location_name'] : '').'</b>', $times14, $alignCenter);

$sect->addEmptyParagraph();
$sect->addEmptyParagraph();

$hospitals = '';
if($_data = @unserialize($f['hospital'])) {
	for ($j = 0; $j < count($_data); $j++) {
		if(mb_strlen($_data[$j], 'utf-8') == 0) continue;
		else $hospitals .= HTMLFormat($_data[$j]).', ';
	}
	$hospitals = (mb_strlen($hospitals, 'utf-8') > 2) ? mb_substr($hospitals, 0, (mb_strlen($hospitals) - 2), 'utf-8') : '';
}

$sect->writeText("\t".'Въз основа на условията на труд и данните от задължителните периодични медицински прегледи, проведени в/от '.$hospitals.' на '.((isset($f)) ? $f['checkup_date_h'] : '').' г.', $times14, $alignLeft);

$sect->addEmptyParagraph();

$sect->writeText('Лицето <b>'.((isset($f)) ? mb_strtoupper($f['fname'].' '.$f['sname'].' '.$f['lname'], 'utf-8') : '--').'</b>', $times14, $alignCenter);

$sect->addEmptyParagraph();

$ary = array();
if(isset($f)) {
	if(!empty($f['stm_conditions'])) { $ary[] = '<b>'.str_replace("<br />", "\n", $f['stm_conditions']).'</b>'; }
	if(!empty($f['stm_conditions2'])) { $ary[] = '<b>'.str_replace("<br />", "\n", $f['stm_conditions2']).'</b>'; }
}
$stm_conditions = "    ".implode("\n", $ary);

$checkbox = $sect->addCheckbox();
if(isset($f['stm_conclusion']) && '1' == $f['stm_conclusion']) {
	$checkbox->setChecked();
	$sect->writeText('<b>може да изпълнява посочената длъжност.</b>', $times14, $alignLeft);
	//$sect->writeText($stm_conditions, $times14, $alignLeft);
} else {
	$sect->writeText('може да изпълнява посочената длъжност.', $times14, $alignLeft);
}
$sect->addEmptyParagraph();

$checkbox = $sect->addCheckbox();
if(isset($f['stm_conclusion']) && '2' == $f['stm_conclusion']) {
	$checkbox->setChecked();
	$sect->writeText('<b>може да изпълнява посочената длъжност/професия при следните условия.</b>', $times14, $alignLeft);
	$sect->writeText($stm_conditions, $times14, $alignLeft);
} else {
	$sect->writeText('може да изпълнява посочената длъжност/професия при следните условия', $times14, $alignLeft);
}
$sect->addEmptyParagraph();

$checkbox = $sect->addCheckbox();
if(isset($f['stm_conclusion']) && '0' == $f['stm_conclusion']) {
	$checkbox->setChecked();
	$sect->writeText('<b>не може да изпълнява посочената длъжност/професия в съответното предприятие.</b>', $times14, $alignLeft);
	//$sect->writeText($stm_conditions, $times14, $alignLeft);
} else {
	$sect->writeText('не може да изпълнява посочената длъжност/професия в съответното предприятие.', $times14, $alignLeft);
}
$sect->addEmptyParagraph();

$checkbox = $sect->addCheckbox();
if(isset($f['stm_conclusion']) && '3' == $f['stm_conclusion']) {
	$checkbox->setChecked();
	$sect->writeText('<b>не може да се прецени пригодността на работещия да изпълнява посочената длъжност/професия в съответното предприятие.</b>', $times14, $alignLeft);
	$sect->writeText($stm_conditions, $times14, $alignLeft);
} else {
	$sect->writeText('не може да се прецени пригодността на работещия да изпълнява посочената длъжност/професия в съответното предприятие.', $times14, $alignLeft);
}

$sect->addEmptyParagraph();
$sect->addEmptyParagraph();

$timesFooter = $times14;
require('phprtflite/rtfend.php');