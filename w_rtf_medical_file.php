<?php
// Test: http://localhost/stm2008/aec_git/w_rtf_medical_file.php?worker_id=11904
require('includes.php');

$worker_id = (isset($_GET['worker_id']) && is_numeric($_GET['worker_id'])) ? intval($_GET['worker_id']) : 0;
$f = $dbInst->getWorkerInfo($worker_id);
if(!$f) {
	die('Липсва индентификатор на работещия!');
}

$s = $dbInst->getStmInfo();
$firm = $dbInst->getFirmInfo($f['firm_id']);

$stm_name = preg_replace('/\<br\s*\/?\>/', '', $s['stm_name']);
$firm_name = preg_replace('/[^A-Za-zА-Яа-я0-9\-_\.]/u', '', $f['firm_name']);
$worker_name = str_replace(' ', '_', (mb_substr($f['fname'], 0, 1, 'utf-8').' '.$f['lname']));

require_once("cyrlat.class.php");
$cyrlat = new CyrLat;
$filename = 'Zdravno_Dosie_'.$cyrlat->cyr2lat($worker_name.'_'.$firm_name).'-'.$f['worker_id'];

$workerNames = (isset($f)) ? HTMLFormat($f['fname'].' '.$f['sname'].' '.$f['lname']) : '';
$workerBasicInfo = (isset($f)) ? HTMLFormat($f['egn']).', ' : '';
if(isset($f) && ('' != $f['l.location_name'] || '' != $f['address'])) {
	$workerBasicInfo .= 'постоянен адрес: ';
	$workerBasicInfo .= (isset($f['r.province_name']) && '' != $f['r.province_name']) ? 'област '.HTMLFormat($f['r.province_name']).', ' : '';
	$workerBasicInfo .= (isset($f['c.community_name']) && '' != $f['c.community_name']) ? 'община  '.HTMLFormat($f['c.community_name']).', ' : '';
	$workerBasicInfo .= (isset($f['l.location_type'])) ? (('1' == $f['l.location_type']) ? 'гр.' : 'с.') : '';
	$workerBasicInfo .= (isset($f['l.location_name']) && '' != $f['l.location_name']) ? HTMLFormat($f['l.location_name']).', ' : '';
	$workerBasicInfo .= (isset($f)) ? HTMLFormat($f['address']) : '';
}
$currentProRoute  = mb_strtoupper($f['i.position_name'], 'utf-8');
$currentProRoute .= ' – от '.(('' != $f['date_curr_position_start2']) ? $f['date_curr_position_start2'].' г.' : '');

require('phprtflite/rtfbegin.php');

$sect->writeText('Приложение № 6 към чл.11, ал.10', $times10, $alignRight);
$sect->addEmptyParagraph();
$sect->writeText('<b>ЗДРАВНО ДОСИЕ</b>', $times20, $alignCenter);
$sect->addEmptyParagraph();

//
$sect->writeText('<b>І. Паспортна част</b>', $times12, $alignLeft);
$sect->writeText('<b>'.$workerNames.'</b>', $times14, $alignCenter);
$sect->writeText('ЕГН '.$workerBasicInfo, $times12, $alignLeft);
$sect->addEmptyParagraph();

$sect->writeText('<b>ІІ. Професионален маршрут</b>', $times12, $alignLeft);
$sect->writeText('1. Настоящ: '.$currentProRoute, $times12, $alignLeft);
$sect->writeText('2. Преди: ', $times12, $alignLeft);

$rows = $dbInst->getProRoute($worker_id);
if($rows) {
	$data = array();
	$data[] = array('Предприятие:', 'Длъжност/професия', 'Продължителност на стажа');
	$i = 1;
	foreach ($rows as $row) {
		$ary = array();
		array_push($ary, $i.'. '.$row['firm_name']);
		array_push($ary, $row['position']);
		$length_yy = ($row['exp_length_y']) ? HTMLFormat($row['exp_length_y']).' г.' : '';
		$length_mm = ($row['exp_length_m']) ? HTMLFormat($row['exp_length_m']).' м.' : '';
		array_push($ary, trim($length_yy.' '.$length_mm));
		$data[] = $ary;
		$i++;
	}
	$colWidts = array(6, 6, 5);
	$colAligns = array('left', 'left', 'left');
	fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'plain');
} else {
	$sect->writeText('Няма предоставени данни', $times12, $alignLeft);
}

$rows = $dbInst->query("SELECT * FROM readjustments WHERE worker_id = $worker_id ORDER BY id");
if(!empty($rows)) {
	$sect->writeText('3. Трудоустрояване:', $times12, $alignLeft);

	$data = array();
	$data[] = array('Дата', 'МКБ', 'Диагноза', 'Комисия', 'Срок', 'Място на трудоустрояване');
	$i = 1;
	foreach ($rows as $row) {
		$ary = array();
		$published_on = (!empty($row['published_on']) && false !== $ts = strtotime($row['published_on'])) ? date('d.m.Y', $ts).' г.' : '';
		$period  = (!empty($row['start_date']) && false !== $ts = strtotime($row['start_date'])) ? date('d.m.y', $ts) : '';
		$period .= (!empty($row['end_date']) && false !== $ts = strtotime($row['end_date'])) ? ' ÷ '.date('d.m.y', $ts) : '';

		array_push($ary, $published_on);
		array_push($ary, $row['mkb_id']);
		array_push($ary, $row['diagnosis']);
		array_push($ary, $row['commission']);
		array_push($ary, $period);
		array_push($ary, $row['place']);
		$data[] = $ary;
		$i++;
	}
	$colWidts = array(2.5, 1.5, 5, 1.8, 3.2, 3);
	$colAligns = array('center', 'center', 'left', 'left', 'left', 'left');
	fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'small');
}

$sect->addEmptyParagraph();

$sect->writeText('<b>ІІІ. Данни за регистрирани професионални болести, трудови злополуки, трудоустрояване и за трайно намалена работоспособност</b>', $times12, $alignLeft);

$sect->addEmptyParagraph();

$sect->writeText('1. Регистрирани професионални болести по данни на работещия и/или работодателя:', $times12, $alignLeft);
$flag = 0;
$rows = $dbInst->getWorkerTelkTypes($worker_id, '4');
if($rows) {
	$flag = 1;
	foreach ($rows as $row) {
		$sect->writeText('- Експертно решение на ТЕЛК № '.$row['telk_num'].'/'.$row['telk_date_from2'].' г., диагноза: '.$row['mkb_id'].' – '.$row['mkb_desc'], $times12, $alignLeft);
	}
}
// Get professional sickness patient's charts
$rows = $dbInst->getPatientCharts($worker_id, array('02', '03'));
if($rows) {
	$flag = 1;
	foreach ($rows as $row) {
		$sect->writeText('- Болничен лист от '.$row['hospital_date_from'].' г., диагноза: '.$row['mkb_id'].' - '.$row['mkb_desc'].', причина: '.$row['reason_desc'], $times12, $alignLeft);
	}
}
if(!$flag) {
	$sect->writeText('Няма предоставени данни', $times12, $alignLeft);
}

$sect->addEmptyParagraph();

$sect->writeText('2. Трудови злополуки по данни на работещия и/или работодателя:', $times12, $alignLeft);
$flag = 0;
$rows = $dbInst->getWorkerTelkTypes($worker_id, '3');
if($rows) {
	$flag = 1;
	foreach ($rows as $row) {
		$sect->writeText('- Експертно решение на ТЕЛК № '.$row['telk_num'].'/'.$row['telk_date_from2'].' г., диагноза: '.$row['mkb_id'].' – '.$row['mkb_desc'], $times12, $alignLeft);
	}
}
// Get professional sickness patient's charts
$rows = $dbInst->getPatientCharts($worker_id, array('04', '05'));
if($rows) {
	$flag = 1;
	foreach ($rows as $row) {
		$sect->writeText('- Болничен лист от '.$row['hospital_date_from'].' г., диагноза: '.$row['mkb_id'].' - '.$row['mkb_desc'].', причина: '.$row['reason_desc'], $times12, $alignLeft);
	}
}
if(!$flag) {
	$sect->writeText('Няма предоставени данни', $times12, $alignLeft);
}

$sect->addEmptyParagraph();

$sect->writeText('3. Трудоустрояване по данни на работещия и/или работодателя:', $times12, $alignLeft);
$flag = 0;
$rows = $dbInst->getPatientTelks($worker_id, '50down');
if($rows) {
	$flag = 1;
	foreach ($rows as $row) {
		$sect->writeText('- Експертно решение на ТЕЛК № '.$row['telk_num'].'/'.$row['telk_date_from_h'].' г., диагноза: '.$row['mkb_id'].' – '.$row['mkb_desc'], $times12, $alignLeft);
	}
}
// transfer to a more appropriate job (for reasons of health).
$rows = $dbInst->getPatientCharts($worker_id, array('16'));
if($rows) {
	$flag = 1;
	foreach ($rows as $row) {
		$sect->writeText('- Болничен лист от '.$row['hospital_date_from'].' г., диагноза: '.$row['mkb_id'].' - '.$row['mkb_desc'].', причина: '.$row['reason_desc'], $times12, $alignLeft);
	}
}
if(!$flag) {
	$sect->writeText('Няма предоставени данни', $times12, $alignLeft);
}

$sect->addEmptyParagraph();

$sect->writeText('4. Трайно намалена работоспособност по данни на работещия и/или работодателя:', $times12, $alignLeft);
$rows = $dbInst->getPatientTelks($worker_id, '50up');
if($rows) {
	$i = 1;
	foreach ($rows as $row) {
		$sect->writeText('4.'.$i++.'. Експертно решение на ТЕЛК № '.$row['telk_num'].'/'.$row['telk_date_from_h'].'/ г., диагноза: '.$row['mkb_id'].' – '.$row['mkb_desc'], $times12, $alignLeft);
		$sect->writeText('Срок: до '.$row['telk_date_to_h'].' г. за '.$row['telk_duration'].', % загубена работоспособност: '.$row['percent_inv'].' %'."\n", $times12, $alignLeft);
		$checkbox = $sect->addCheckbox();
		if(90 < $row['percent_inv']) { $checkbox->setChecked(); }
		$sect->writeText('над 90 %', $times12, $alignLeft);

		$checkbox = $sect->addCheckbox();
		if(70 < $row['percent_inv'] && 90 >= $row['percent_inv']) { $checkbox->setChecked(); }
		$sect->writeText('от 71 – 90 %', $times12, $alignLeft);

		$checkbox = $sect->addCheckbox();
		if(50 <= $row['percent_inv'] && 70 >= $row['percent_inv']) { $checkbox->setChecked(); }
		$sect->writeText('от 50 – 70 %', $times12, $alignLeft);
	}
} else {
	$sect->writeText('Няма предоставени данни', $times12, $alignLeft);
}

$sect->addEmptyParagraph();

$charts = $dbInst->getPatientCharts($worker_id);
if($charts) {
	$sect->writeText('5. ВНР', $times12, $alignLeft);

	$data = array();
	$data[] = array('МКБ', 'Причина', 'Вид', 'Брой дни', 'От дата');
	$i = 1;
	$chart_types = $dbInst->getChartTypes();
	foreach ($charts as $row) {
		if(!($medical_types_arr = @unserialize($row['medical_types']))) {
			$medical_types_arr = array();
		}
		$medical_types = null;
		if($chart_types) {
			foreach ($chart_types as $chart_type) {
				if(!is_array($medical_types_arr))
				continue;
				if(in_array($chart_type['type_id'], $medical_types_arr)) {
					$medical_types[] = $chart_type['type_desc_short'];
					//$medical_types[] = $chart_type['type_desc'];
				}
			}
		}
		$medical_types = ($medical_types != null) ? implode('<br />', $medical_types) : '';

		$ary = array();
		array_push($ary, $row['mkb_id']);
		array_push($ary, $row['reason_id']);
		array_push($ary, $medical_types);
		array_push($ary, $row['days_off']);
		array_push($ary, $row['hospital_date_from']);

		$data[] = $ary;
		$i++;
	}
	$colWidts = array(3, 3, 4, 3, 3);
	$colAligns = array('center', 'center', 'center', 'center', 'center');
	fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'small');
}

$sect->writeText('<b>ІV. Условия на труд и данни от проведени предварителни и периодични медицински прегледи и изследвания по време на работата на работещия</b> в '.$firm['name'].' - '.$firm['location_name'].', '.$firm['address'], $times12, $alignLeft);

$sect->addEmptyParagraph();

$sect->writeText('1. Данни за изпълняваната в предприятието длъжност/професия, работното място и условията на труд', $times12, $alignLeft);
$sect->writeText('1.1. Длъжност: '.$dbInst->my_mb_ucfirst($f['i.position_name']), $times12, $alignLeft);
$sect->writeText('1.2. Работно място: '.$dbInst->my_mb_ucfirst($f['p.wplace_name']), $times12, $alignLeft);
$sect->writeText('1.3. Условия на труд при длъжност/професия по т. 1.1 и работно място по т. 1.2', $times12, $alignLeft);
$sect->writeText('1.3.1. Кратко описание на извършваната дейност:', $times12, $alignLeft);
$i = 1;
if(!empty($f['i.position_workcond'])) {
	$sect->writeText('1.3.1.'.$i++.'. '.$f['i.position_workcond'], $times12, $alignLeft);
}
if(!empty($f['p.wplace_workcond'])) {
	$sect->writeText('1.3.1.'.$i++.'. '.$f['p.wplace_workcond'], $times12, $alignLeft);
}

$rows = $dbInst->getWorkEnvProtocols($f['firm_id'], $f['s.subdivision_id'], $f['p.wplace_id']);
if($rows) {
	$sect->writeText('1.3.2. Фактори на работната среда и трудовия процес', $times12, $alignLeft);

	function compare_prot_date($a, $b) { return strnatcmp($b['prot_date'], $a['prot_date']); }
	# http://www.the-art-of-web.com/php/sortarray/
	# sort alphabetically by protocol date
	usort($rows, 'compare_prot_date');

	$prots = array();
	foreach ($rows as $num => $row) {
		$rows[$num]['factor_name'] = $row['factor_name'];
		$rows[$num]['prot_num'] = $row['prot_num'].(($row['prot_date_h'] != '') ? '/'.$row['prot_date_h'].' г.' : '');
		$rows[$num]['prot_norms'] = $row['level'].' '.$row['factor_dimension'];
		$rows[$num]['prot_data'] = (($row['pdk_min'] != '') ? $row['pdk_min'] : '').(($row['pdk_max'] != '') ? ' - '.$row['pdk_max'] : '').' '.$row['factor_dimension'];
		if(empty($row['prot_date'])) $rows[$num]['prot_date'] = '0000-00-00';
		//$prots[$rows[$num]['prot_num'].'_'.$row['factor_id']][] = $rows[$num];
		//$prots[$rows[$num]['prot_num']][] = $rows[$num];
		$suffix = $row['factor_name'];
		if(preg_match('/^(.*?)\s+/i', $row['factor_name'], $matches)) $suffix = $matches[1];
		$prots[$rows[$num]['prot_num'].'|'.$suffix][] = $rows[$num];
	}

	$rows = $prots;

	$data = array();
	$data[] = array('Показател', '№ и дата на протокола', 'Установени норми', 'Гранични');
	foreach ($rows as $prot_num => $prot) {
		$num = count($prot);
		$j = 0;
		foreach ($prot as $row) {
			if(!$j) {
				$j++;
				if(false !== $pos = strpos($prot_num, '|')) { $prot_num = substr($prot_num, 0, $pos); }
			}

			$ary = array();
			array_push($ary, $row['factor_name']);
			array_push($ary, $prot_num);
			array_push($ary, $row['prot_norms']);
			array_push($ary, $row['prot_data']);
			$data[] = $ary;
		}
	}
	$colWidts = array(5, 4, 4, 4);
	$colAligns = array('center', 'center', 'center', 'center', 'center');
	fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'small');
}

$i = 1;
$row = $dbInst->getWPlaceFactorsInfo($f['firm_id'], $f['s.subdivision_id'], $f['p.wplace_id']);
if(isset($row['fact_dust']) && $row['fact_dust'] != '') {
	$sect->writeText('1.3.2.'.$i++.'. Прах – вид: '.$row['fact_dust'], $times12, $alignLeft);
}
if(isset($row['fact_chemicals']) && $row['fact_chemicals'] != '') {
	$sect->writeText('1.3.2.'.$i++.'. Химични агенти – вид: '.$row['fact_chemicals'], $times12, $alignLeft);
}
if(isset($row['fact_biological']) && $row['fact_biological'] != '') {
	$sect->writeText('1.3.2.'.$i++.'. Биологични агенти: '.$row['fact_biological'], $times12, $alignLeft);
}
if(isset($row['fact_work_pose']) && $row['fact_work_pose'] != '') {
	$sect->writeText('1.3.2.'.$i++.'. Работна поза: '.$row['fact_work_pose'], $times12, $alignLeft);
}
if(isset($row['fact_manual_weights']) && $row['fact_manual_weights'] != '') {
	$sect->writeText('1.3.2.'.$i++.'. Ръчна работа с тежести: '.$row['fact_manual_weights'], $times12, $alignLeft);
}
if(isset($row['fact_monotony']) && $row['fact_monotony'] != '') {
	$sect->writeText('1.3.2.'.$i++.'. Двигателна монотонна работа: '.$row['fact_monotony'], $times12, $alignLeft);
}
if(isset($row['fact_nervous']) && $row['fact_nervous'] != '') {
	$sect->writeText('1.3.2.'.$i++.'. Нервно-психично напрежение: '.$row['fact_nervous'], $times12, $alignLeft);
}
if(isset($row['fact_nervous']) && ($row['fact_work_regime'] != '' || $row['fact_work_hours'] != '' || $row['fact_work_and_break'] != '')) {
	$sect->writeText('1.3.2.'.$i++.'. Организация на труда:', $times12, $alignLeft);
	$j = 1;
	if($row['fact_work_regime'] != '') {
		$sect->writeText('1.3.2.'.$i++.'. режим на работа: '.$row['fact_work_regime'], $times12, $alignLeft);
	}
	if($row['fact_work_hours'] != '') {
		$sect->writeText('1.3.2.'.$i++.'. продължителност на работното време: '.$row['fact_work_hours'], $times12, $alignLeft);
	}
	if($row['fact_work_and_break'] != '') {
		$sect->writeText('1.3.2.'.$i++.'. физиологични режими на труд и почивка: '.$row['fact_work_and_break'], $times12, $alignLeft);
	}
	$i++;
}
if(isset($row['fact_other']) && $row['fact_other'] != '') {
	$sect->writeText('1.3.2.'.$i++.'. Други: '.$row['fact_other'], $times12, $alignLeft);
}

$sect->addEmptyParagraph();

$sect->writeText('2. Данни от предварителен медицински преглед:'."\n", $times12, $alignLeft);

if(!empty($f['prchk_date2'])) {
	$checkbox = $sect->addCheckbox();
	$checkbox->setChecked();
	$sect->writeText('2.1. Има налични данни за проведен предварителен преглед.', $times12, $alignLeft);
	$sect->writeText('2.1.1. Kарта за предварителен медицински преглед, издадена от '.$f['prchk_author'], $times12, $alignLeft);

	$rows = $dbInst->getPrchkDocDiagnosis($worker_id);
	if($rows) {
		$sect->addEmptyParagraph();
		$sect->writeText('- Заключение на лекаря/лекарите, провели прегледите:', $times12, $alignLeft);
		foreach ($rows as $row) {
			$sect->writeText($row['doctor_pos_name'].((!empty($row['doc_name'])) ? ' ('.$row['doc_name'].')' : '').': '.$row['doc_conclusion'], $times12, $alignLeft);
		}
	}

	$sql = "SELECT d.*, m.mkb_desc, m.mkb_code, p.doctor_pos_name
			FROM prchk_diagnosis d
			LEFT JOIN mkb m ON (m.mkb_id = d.mkb_id)
			LEFT JOIN cfg_doctor_positions p ON (p.doctor_pos_id = d.published_by)
			WHERE d.worker_id = $worker_id
			ORDER BY d.prchk_id";
	$rows = $dbInst->query($sql);
	if($rows) {
		$sect->addEmptyParagraph();
		$sect->writeText('- Заболявания (диагнози)', $times12, $alignLeft);
		$data = array();
		$data[] = array('МКБ', 'Диагноза', 'Издадена от');
		foreach ($rows as $row) {
			$mkb_desc = $row['mkb_desc'];
			if(!empty($row['diagnosis'])) {
				$mkb_desc .= "\n".$row['diagnosis'];
			}

			$ary = array();
			array_push($ary, $row['mkb_id']);
			array_push($ary, $mkb_desc);
			array_push($ary, $row['doctor_pos_name']);
			$data[] = $ary;
		}
		$colWidts = array(2, 11, 4);
		$colAligns = array('center', 'left', 'left');
		fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'small');
	}

	$sect->writeText('2.1.2. Заключение на СТМ за пригодността на работещия да изпълнява даден вид дейност въз основа на карта от задължителен предварителен медицински преглед, издадена от '.$dbInst->shortStmName($stm_name).' '.((isset($f['prchk_stm_date2']) && !empty($f['prchk_stm_date2'])) ? ' на '.$f['prchk_stm_date2'].' г.' : ''), $times12, $alignLeft);

	$stm_conclusion = '';
	if('1' == $f['prchk_conclusion']) {
		$stm_conclusion .= '<b>Може</b> да изпълнява посочената длъжност/професия';
	} elseif ('2' == $f['prchk_conclusion']) {
		$stm_conclusion .= '<b>Може</b> да изпълнява посочената длъжност/професия при следните условия:'."\n";
		$stm_conclusion .= $f['prchk_conditions'];
	}
	elseif ($f['prchk_conclusion'] == '0') {
		$stm_conclusion .= '<b>Не може</b> да изпълнява посочената длъжност/професия';
	}

	$data = array();
	$data[] = array('Наименование и адрес на СТМ, изготвила заключението, и дата на изготвянето му', 'Заключение');
	$ary = array();
	array_push($ary, $dbInst->shortStmName($stm_name)."\n".$s['address']);
	array_push($ary, $stm_conclusion);
	$data[] = $ary;
	$colWidts = array(8, 9);
	$colAligns = array('left', 'left');
	fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'small');

} else {
	$checkbox = $sect->addCheckbox();
	$checkbox->setChecked();
	$sect->writeText('2.2. Няма налични данни за проведен предварителен медицински преглед.', $times12, $alignLeft);
	$sect->addEmptyParagraph();
}

$sect->writeText('3. Данни от извършените периодични медицински прегледи и изследвания:', $times12, $alignLeft);
$sect->writeText('3.1. Работещият се е явил на периодичен медицински преглед и са проведени определените изследвания.', $times12, $alignLeft);

$rows = $dbInst->getMedicalCheckupList($worker_id);
$k = 1;
foreach ($rows as $row) {
	$checkup_id = $row['checkup_id'];
	$line = $dbInst->getMedicalCheckupInfo($checkup_id);

	$_arr = array();
	$hospitals = '';
	if($_data = @unserialize($line['hospital'])) {
		for ($j = 0; $j < count($_data); $j++) {
			if(mb_strlen($_data[$j], 'utf-8') == 0) continue;
			else $_arr[] = stripslashes($_data[$j]);
		}
		//$hospitals = (mb_strlen($hospitals, 'utf-8') > 2) ? mb_substr($hospitals, 0, (mb_strlen($hospitals) - 2), 'utf-8') : '';
		$hospitals = (count($_arr)) ? implode(', ', $_arr) : '';
	}

	$doctors = '';
	$rows = $dbInst->getDoctorsDesc($checkup_id);
	if($rows) {
		$_arr = array();
		foreach ($rows as $row) {
			if($row['conclusion'] == '') continue;
			$_arr[] = $dbInst->my_mb_ucfirst(HTMLFormat($row['SpecialistName']));
		}
		if(count($_arr)) {
			$doctors = ': '.implode(', ', $_arr);
		}
	}

	$sect->writeText('3.'.$k.'.1. Дата на провеждане на прегледа: '.((empty($line['checkup_date_h'])) ?'--' : $line['checkup_date_h'].' г.'), $times12, $alignLeft);
	$sect->writeText('3.'.$k.'.2. Наименование на лечебното заведение, провело прегледа: '.((empty($hospitals)) ? 'Няма предоставени данни' : $hospitals).'.', $times12, $alignLeft);
	$sect->writeText('3.'.$k.'.3. Вид на медицинските специалисти, извършили прегледите'.$doctors.'.', $times12, $alignLeft);
	$sect->writeText('3.'.$k.'.4. Вид на извършените функционални и лабораторни изследвания.', $times12, $alignLeft);
	if(isset($line['EKG']) && !empty($line['EKG'])) {
		$sect->writeText('ЕКГ: '.$line['EKG'], $times12, $alignLeft);
	}
	if(isset($line['x_ray']) && !empty($line['x_ray'])) {
		$sect->writeText('Рентгенография: '.$line['x_ray'], $times12, $alignLeft);
	}
	if(isset($line['echo_ray']) && !empty($line['echo_ray'])) {
		$sect->writeText('Ехография: '.$line['echo_ray'], $times12, $alignLeft);
	}
	
	if(isset($line) && ($line['left_eye'] != '' || $line['right_eye'] != '')) {
		$sect->writeText('Зрителна острота', $times12, $alignLeft);
		$sect->writeText('Ляво око: '.((isset($line['left_eye'])) ? HTMLFormat($line['left_eye']) : '').' / '.((isset($line['left_eye2'])) ? HTMLFormat($line['left_eye2']) : '').' dp'."\t\t".'Дясно око: '.((isset($line['right_eye'])) ? HTMLFormat($line['right_eye']) : '').' / '.((isset($line['right_eye2'])) ? HTMLFormat($line['right_eye2']) : '').' dp', $times12, $alignLeft);
	}
	
	if(isset($line) && (!empty($line['VK']) || !empty($line['FEO1']))) {
		$sect->writeText('Функционално изследване на дишането', $times12, $alignLeft);
		$sect->writeText('ВК: '.((isset($line['VK'])) ? HTMLFormat($line['VK']) : '').' ml'."\t\t\t".'ФЕО 1: '.((isset($line['FEO1'])) ? HTMLFormat($line['FEO1']) : '').' ml', $times12, $alignLeft);
	}
	
	if(isset($line['tifno']) && !empty($line['tifno'])) {
		$sect->writeText('Показател на Тифно: '.$line['tifno'], $times12, $alignLeft);
	}

	if(isset($line['hearing_loss']) && !empty($line['hearing_loss'])) {
		$sect->writeText('Тонална аудиометрия', $times12, $alignLeft);
		$sect->writeText('Загуба на слуха: '.$line['hearing_loss'], $times12, $alignLeft);
		
		$sect->writeText('Ляво ухо: '.$line['left_ear']."\t\t".'Дясно ухо: '.$line['left_ear'], $times12, $alignLeft);
		if(isset($line['hearing_diagnose']) && !empty($line['hearing_diagnose'])) {
			$sect->writeText('Диагноза: '.$line['hearing_diagnose'], $times12, $alignLeft);
		}
	}

	//Фамилна обремененост
	$sect->writeText('Фамилна обремененост: '.((!empty($line['fweights_descr'])) ? HTMLFormat($line['fweights_descr']) : '--'), $times12, $alignLeft);
	$flds = $dbInst->getFamilyWeights($checkup_id);
	if($flds) {
		$data = array();
		$data[] = array('МКБ', 'Диагноза');
		foreach ($flds as $fld) {
			$mkb_id = HTMLFormat($fld['mkb_id']);
			$mkb_desc = HTMLFormat($fld['mkb_desc']);
			if($fld['diagnosis'] != '') {
				$mkb_desc .= '<br>'.HTMLFormat($fld['diagnosis']);
			}
			$data[] = array($mkb_id, $mkb_desc);
		}
		$colWidts = array(2, 15);
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
			$ary = array();
			array_push($ary, $row['indicator_name']);
			array_push($ary, $row['pdk_min']);
			array_push($ary, $row['pdk_max']);
			array_push($ary, $row['checkup_level']);
			array_push($ary, $row['indicator_dimension']);
			$data[] = $ary;
		}
		$colWidts = array(5, 3, 3, 3, 3);
		$colAligns = array('left', 'center', 'center', 'center', 'center');
		fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'small');
	}

	//Фамилна обремененост
	$sect->writeText('Анамнеза: '.((!empty($line['anamnesis_descr'])) ? HTMLFormat($line['anamnesis_descr']) : '--'), $times12, $alignLeft);	
	$flds = $dbInst->getAnamnesis($checkup_id);
	if($flds) {
		$data = array();
		$data[] = array('МКБ', 'Диагноза');
		foreach ($flds as $fld) {
			$mkb_id = HTMLFormat($fld['mkb_id']);
			$mkb_desc = HTMLFormat($fld['mkb_desc']);
			if($fld['diagnosis'] != '') {
				$mkb_desc .= '<br>'.HTMLFormat($fld['diagnosis']);
			}
			$data[] = array($mkb_id, $mkb_desc);
		}
		$colWidts = array(2, 15);
		$colAligns = array('center', 'left');
		fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'plain');
	}
	
	$rows2 = $dbInst->getDiseases($checkup_id);
	if($rows2) {
		$sect->writeText('Заболявания (диагнози)', $times12, $alignLeft);
		$data = array();
		$data[] = array('МКБ', 'Диагноза', 'Новооткрито');
		foreach ($rows2 as $row) {
			$mkb_desc = $row['mkb_desc'];
			if(!empty($row['diagnosis'])) {
				$mkb_desc .= "\n".$row['diagnosis'];
			}

			$ary = array();
			array_push($ary, $row['mkb_id']);
			array_push($ary, $mkb_desc);
			array_push($ary, (('1' == $row['is_new']) ? 'да' : 'не'));
			$data[] = $ary;
		}
		$colWidts = array(2, 12, 3);
		$colAligns = array('center', 'left', 'center');
		fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'small');
	}

	$sect->writeText('3.'.$k.'.5. Заключение на лекаря/лекарите, провели прегледите:', $times12, $alignLeft);

	$flds = $dbInst->getDoctorsDesc($checkup_id);
	if($flds) {
		foreach ($flds as $fld) {
			if($fld['conclusion'] == '') continue;
			$sect->writeText($dbInst->my_mb_ucfirst(HTMLFormat($fld['SpecialistName'])).': '.HTMLFormat($fld['conclusion']), $times12, $alignLeft);
		}
	}

	$sect->writeText('3.'.$k.'.6. Заключение на службата по трудова медицина за пригодността на работещия да изпълнява даден вид дейност въз основа на задължителния периодичен медицински преглед, проведен'.((!empty($hospitals)) ? ' от/в '.$hospitals : '').' на '.((empty($line['checkup_date_h'])) ? '--' : $line['checkup_date_h'].' г.'), $times12, $alignLeft);

	$stm_conclusion = '';
	switch ($line['stm_conclusion']) {
		case '1':
			$stm_conclusion .= '<b>Може</b> да изпълнява посочената длъжност/професия '.HTMLFormat($f['i.position_name']).' в '.HTMLFormat($firm['name']);
			break;
		case '2':
			$stm_conclusion .= '<b>Може</b> да изпълнява посочената длъжност/професия '.HTMLFormat($f['i.position_name']).' в '.HTMLFormat($firm['name']).' при следните условия: ';
			break;
		case '0':
			$stm_conclusion .= '<b>Не може</b> да изпълнява посочената длъжност/професия '.HTMLFormat($f['i.position_name']).' в '.HTMLFormat($firm['name']);
			break;
		case '3':
			$stm_conclusion .= '<b>Не може да се прецени</b> пригодността на работещия да изпълнява посочената длъжност/професия '.HTMLFormat($f['position_name']).' в '.HTMLFormat($firm['name']);
			break;
	}
	if(empty($stm_conclusion)) { $stm_conclusion .= "\n"; }
	$stm_conclusion .= $line['stm_conditions'].((!empty($line['stm_conditions2'])) ? "\n".$line['stm_conditions2'] : '');
	
	$data = array();
	$data[] = array('Наименование и адрес на СТМ, изготвила заключението, и дата на изготвянето му', 'Заключение');
	$ary = array();
	array_push($ary, $dbInst->shortStmName($stm_name)."\n".$s['address']);
	array_push($ary, $stm_conclusion);
	$data[] = $ary;
	$colWidts = array(8, 9);
	$colAligns = array('center', 'left');
	fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'small');

	$k++;
}

$sect->writeText('<b>V. Данни за посещенията на работещия в службата по трудова медицина по негова инициатива</b>', $times12, $alignLeft);

$sect->addEmptyParagraph();

$sect->writeText('1. Извършено посещение на работещия в '.$dbInst->shortStmName($stm_name).'.', $times12, $alignLeft);
$sect->writeText('2. Дата на извършеното посещение:', $times12, $alignLeft);
$sect->writeText('3. Кратко описание на целта на посещението.', $times12, $alignLeft);
$sect->writeText('4. Описание на предприетите мерки от службата по трудова медицина във връзка с поставените въпроси, проблеми и други, когато е необходимо.', $times12, $alignLeft);
$sect->writeText('5. Други.', $times12, $alignLeft);

require('phprtflite/rtfend.php');
