<?php
require('includes.php');

$precheckup_id = (isset($_GET['precheckup_id']) && is_numeric($_GET['precheckup_id'])) ? intval($_GET['precheckup_id']) : 0;

$worker_id = $firm_id = 0;
$sql = "SELECT p.*, p.`firm_id` AS `firm_id`,
		strftime('%d.%m.%Y', p.prchk_date, 'localtime') AS prchk_date2,
		strftime('%d.%m.%Y', p.prchk_stm_date, 'localtime') AS prchk_stm_date2,
		w.`fname`, w.`sname`, w.`lname`, w.`sex`, w.`egn`,
		f.`name` AS `firm_name`
		FROM `medical_precheckups` p
		LEFT JOIN `workers` w ON (w.`worker_id` = p.`worker_id`)
		LEFT JOIN `firms` f ON (f.`firm_id` = p.`firm_id`)
		WHERE p.`precheckup_id` = $precheckup_id";
$f = $dbInst->query($sql);
if(!empty($f)) {
	$f = $f[0];
	$worker_id = $f['worker_id'];
	$firm_id = $f['firm_id'];
	$w = $dbInst->getWorkerInfo($worker_id);
} else {
	die('Invalid ID!');
}

$s = $dbInst->getStmInfo();

$stm_name = preg_replace('/\<br\s*\/?\>/', '', $s['stm_name']);

$line = $dbInst->getFirmInfo($f['firm_id']);

$firm_name = str_replace(' ', '_', $line['firm_name']);
$firm_name = str_replace('"', '', $firm_name);
$firm_name = str_replace('\'', '', $firm_name);
$firm_name = str_replace('”', '', $firm_name);
$firm_name = str_replace('„', '', $firm_name);
$firm_name = str_replace('_-_', '_', $firm_name);

$worker_name = str_replace(' ', '_', (mb_substr($f['fname'], 0, 1, 'utf-8').' '.$f['lname']));

require_once("cyrlat.class.php");
$cyrlat = new CyrLat;
$filename = 'Zaklyuchenie_predv_pregled_'.$cyrlat->cyr2lat($worker_name.'_'.$firm_name).'-'.$f['worker_id'];


require('phprtflite/rtfbegin.php');

$sect->writeText('<b>З А К Л Ю Ч Е Н И Е</b>', $times20, $alignCenter);
$sect->addEmptyParagraph();
$sect->writeText('<b>от '.mb_strtoupper(html_entity_decode($dbInst->shortStmName($stm_name)), 'utf-8').'</b>', $times14, $alignCenter);
$sect->writeText('<b>за пригодността на лицето '.((isset($f)) ? mb_strtoupper($f['fname'].' '.$f['sname'].' '.$f['lname'], 'utf-8') : '').'</b>', $times14, $alignCenter);
$sect->writeText('<b>да изпълнява длъжността '.((isset($w)) ? mb_strtoupper($w['position_name'], 'utf-8') : '').'</b>', $times14, $alignCenter);
$sect->writeText('<b>в '.((isset($line)) ? mb_strtoupper($line['firm_name'], 'utf-8'):'').((isset($line)) ? ' - '.$line['location_name'] : '').'</b>', $times14, $alignCenter);

$sect->addEmptyParagraph();
$sect->addEmptyParagraph();

$sql = "SELECT p.*, p.`firm_id` AS `firm_id`,
		strftime('%d.%m.%Y', p.prchk_date, 'localtime') AS prchk_date2,
		strftime('%d.%m.%Y', p.prchk_stm_date, 'localtime') AS prchk_stm_date2,
		w.`fname`, w.`sname`, w.`lname`, w.`sex`, w.`egn`,
		f.`name` AS `firm_name`
		FROM `medical_precheckups` p
		LEFT JOIN `workers` w ON (w.`worker_id` = p.`worker_id`)
		LEFT JOIN `firms` f ON (f.`firm_id` = p.`firm_id`)
		WHERE p.`precheckup_id` = $precheckup_id
		AND p.`worker_id` = $worker_id";
$f = $dbInst->query($sql);
if(!empty($f)) {
	$f = $f[0];
}

$sect->writeText("\t".'Въз основа на условията на труд и данните от <b>предварителните медицински прегледи</b>, проведени в/от '.((isset($f)) ? $f['prchk_author'] : '').' на '.((isset($f)) ? $f['prchk_date2'] : '').' г.', $times14, $alignLeft);

$sect->addEmptyParagraph();

$sect->writeText('Лицето '.((isset($f)) ? mb_strtoupper($f['fname'].' '.$f['sname'].' '.$f['lname'], 'utf-8') : '--'), $times14, $alignCenter);

$sect->addEmptyParagraph();

$checkbox = $sect->addCheckbox();
if(isset($f['prchk_conclusion']) && '1' == $f['prchk_conclusion']) {
	$checkbox->setChecked();
	$sect->writeText('<b>може да изпълнява посочената длъжност.</b>', $times14, $alignLeft);
} else {
	$sect->writeText('може да изпълнява посочената длъжност.', $times14, $alignLeft);
}
$sect->addEmptyParagraph();

$checkbox = $sect->addCheckbox();
if(isset($f['prchk_conclusion']) && '2' == $f['prchk_conclusion']) {
	$checkbox->setChecked();
}
$sect->writeText('може да изпълнява посочената длъжност/професия при следните условия', $times14, $alignLeft);
if(isset($f) && !empty($f['prchk_conditions'])) {
	$sect->writeText('    <b>'.$f['prchk_conditions'].'</b>', $times14, $alignLeft);
}
$sect->addEmptyParagraph();

$checkbox = $sect->addCheckbox();
if(isset($f['prchk_conclusion']) && '0' == $f['prchk_conclusion']) {
	$checkbox->setChecked();
	$sect->writeText('<b>не може да изпълнява посочената длъжност/професия в съответното предприятие.</b>', $times14, $alignLeft);
} else {
	$sect->writeText('не може да изпълнява посочената длъжност/професия в съответното предприятие.', $times14, $alignLeft);
}
$sect->addEmptyParagraph();
$sect->addEmptyParagraph();

$timesFooter = $times14;
require('phprtflite/rtfend.php');