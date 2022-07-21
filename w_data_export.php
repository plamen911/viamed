<?php
// http://localhost/stm2008/hipokrat/w_data_export.php?worker_id=51992
require('includes.php');

set_time_limit(600);
ini_set('memory_limit', '256M');

// Based on: http://www.phpclasses.org/package/3776-PHP-Template-engine-based-on-real-HTML-tag-replacement.html
require("templatehtml.class.php");
// Create the template object
$templateHeader = new TemplateHTML("tpls/htmlbegin.html");
$templateFooter = new TemplateHTML("tpls/htmlend.html");

$firm_id = (isset($_GET['firm_id']) && is_numeric($_GET['firm_id'])) ? intval($_GET['firm_id']) : 0;
$worker_id = (isset($_GET['worker_id']) && is_numeric($_GET['worker_id'])) ? intval($_GET['worker_id']) : 0;

if(!empty($firm_id)) {
	$name = $dbInst->GiveValue("`name`", 'firms', "WHERE `firm_id` = $firm_id", 0);
} else {
	$name = $dbInst->GiveValue("(`fname` || ' ' || `sname` || ' ' || `lname`) AS `names`", 'workers', "WHERE `worker_id` = $worker_id", 0);
}

$filename = makeFileName($name).'.doc';

header("Pragma: public");
header("Content-Disposition: attachment; filename=\"$filename\";");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
//header("Cache-Control: private", false);
header("Content-Type: application/octet-stream");

$templateHeader->replaceTag('ptitle', $name);
$templateHeader->showPage();

if(!empty($firm_id)) {
	$row = $dbInst->getFirmInfo($firm_id);
	if(empty($row)) { die('Невалиден идентификатор на фирма!'); }
	
	$templateFirm = new TemplateHTML("tpls/firm.html");
	foreach ($row as $key => $val) {
		if('' == $val) { $val = '--'; }
		$templateFirm->replaceTag($key, $val);
	}
	$num_workers = $num_retired = 0;
	
	$page = (isset($_GET['page']) && !empty($_GET['page'])) ? (intval($_GET['page']) - 1) : 0;
	$limit = (isset($_GET['limit']) && !empty($_GET['limit'])) ? intval($_GET['limit']) : 200;
	
	$cnt = $dbInst->fnCountRow('workers', "firm_id = $firm_id");
	if($cnt > $limit) {
		$templateFirm->replaceTag('part_num', ' - Том '.($page + 1));
	}
	
	$sql = "SELECT w.*,
			strftime('%d.%m.%Y', w.birth_date, 'localtime') AS birth_date2,
			strftime('%d.%m.%Y', w.date_curr_position_start, 'localtime') AS date_curr_position_start2,
			strftime('%d.%m.%Y', w.date_career_start, 'localtime') AS date_career_start2,
			strftime('%d.%m.%Y', w.date_retired, 'localtime') AS date_retired2,
			strftime('%d.%m.%Y', w.prchk_date, 'localtime') AS prchk_date2,
			strftime('%d.%m.%Y', w.prchk_stm_date, 'localtime') AS prchk_stm_date2,
			f.name AS firm_name,
			r.province_name AS province_name,
			c.community_name AS community_name,
			l.location_name AS location_name,
			l.location_type AS location_type,
			s.subdivision_id AS subdivision_id, s.subdivision_name AS subdivision_name,
			p.wplace_id AS wplace_id, p.wplace_name AS wplace_name, p.wplace_workcond AS wplace_workcond,
			i.position_id AS position_id, i.position_name AS position_name,
			i.position_workcond AS position_workcond,
			d.doctor_name AS doctor_name,
			d.address AS doctor_address,
			d.phone1 AS doctor_phone,
			d.phone2 AS doctor_phone2
			FROM workers w
			LEFT JOIN firms f ON (f.firm_id = w.firm_id)
			LEFT JOIN locations l ON (l.location_id = w.location_id)
			LEFT JOIN communities c ON (c.community_id = l.community_id)
			LEFT JOIN provinces r ON (r.province_id = c.province_id)
			LEFT JOIN firm_struct_map m ON (m.map_id = w.map_id)
			LEFT JOIN subdivisions s ON (s.subdivision_id = m.subdivision_id)
			LEFT JOIN work_places p ON (p.wplace_id = m.wplace_id)
			LEFT JOIN firm_positions i ON (i.position_id = m.position_id)
			LEFT JOIN doctors d ON (w.doctor_id = d.doctor_id)
			WHERE w.firm_id = $firm_id
			GROUP BY w.worker_id
			ORDER BY w.`date_retired`, w.`fname`, w.`sname`, w.`lname`, w.`egn`, w.`worker_id`
			LIMIT ".($page * $limit).", $limit";
	$flds = $dbInst->query($sql);
	$workerData = '';
	if(!empty($flds)) {
		foreach ($flds as $fld) {
			$f = array();
			foreach ($fld as $key => $val) {
				if(is_numeric($key)) continue;
				$f[$key] = $val;
			}
			if(!empty($fld['date_retired2'])) {
				$num_retired++;
			} else {
				$num_workers++;
			}
			$workerData .= exportWorker($dbInst, $f);
		}
	}

	$templateFirm->replaceTag('date', date('d.m.Y'));
	$templateFirm->replaceTag('num_workers', $num_workers);
	$templateFirm->replaceTag('num_retired', $num_retired);
	$templateFirm->showPage();
	echo $workerData;
	unset($workerData);

} else {
	echo exportWorker($dbInst, $worker_id);
}



$templateFooter->showPage();

function exportWorker($dbInst = null, $param = null) {
	if(is_numeric($param)) {
		$worker_id = $param;
		$f = $dbInst->getWorkerInfo($worker_id);
		if(empty($f)) { die('Невалиден идентификатор на работещия!'); }
	} else {
		$f = $param;
		$worker_id = $f['worker_id'];
	}

	//echo '<pre>'.print_r($f, 1).'</pre>';

	$templateMain = new TemplateHTML("tpls/worker.html");

	$trHeader = '';
	$tdHeader = 'style="font-weight: bold;"';

	// Данни за работещия
	foreach ($f as $key => $val) {
		if(in_array($key, array('birth_date')) && !empty($val) && false !== $ts = strtotime($val)) {
			$val = date('d.m.Y', $ts);
		}
		if('' == $val) { $val = '--'; }
		// Replace the tags with real data
		$templateMain->replaceTag($key, $val);
	}
	$doctor_name = '--';
	if(!empty($f['doctor_id'])) {
		$doctor_name = $dbInst->GiveValue('doctor_name', 'doctors', "WHERE `doctor_id` = $f[doctor_id]", 0);
		if(empty($doctor_name)) { $doctor_name = '--'; }
	}
	$templateMain->replaceTag('doctor_name', $doctor_name);

	$curr_position_length = '--';
	if(isset($f['date_curr_position_start']) && $f['date_curr_position_start'] != '') {
		$date = substr($f['date_curr_position_start'], 0, 10);
		list($y, $m, $d) = explode('-',$date);
		$curr_position_length = calcTimespan($d, $m, $y);
	}
	$templateMain->replaceTag('curr_position_length', $curr_position_length);

	$career_length = '--';
	if(isset($f['date_career_start']) && $f['date_career_start'] != '') {
		$date = substr($f['date_career_start'], 0, 10);
		list($y, $m, $d) = explode('-',$date);
		$career_length = calcTimespan($d, $m, $y);
	}
	$templateMain->replaceTag('career_length', $career_length);

	// Професионален маршрут
	// Easy Template can create dropdowns, lists, and tables from arrays
	$rows = $dbInst->getProRoute($worker_id);
	$array = array();
	if(!empty($rows)) {
		foreach ($rows as $row) {
			$exp_length  = ((!empty($row['exp_length_y'])) ? $row['exp_length_y'] : '--').' г. ';
			$exp_length .= ((!empty($row['exp_length_m'])) ? $row['exp_length_m'] : '--').' м.';
			$array[] = array('Предприятие' => $row['firm_name'], 'Длъжност/професия' => $row['position'], 'Продължителност на стажа' => $exp_length);
		}
	} else {
		$templateMain->replaceTag("pro_route_nodata", ' - няма предоставени данни.');
	}
	// Replace the actual table data in the page!
	$templateMain->replaceHtmlTableData("pro_route", $array, true, 1, $trHeader, $tdHeader);

	// Предварителни медицински прегледи
	$sql = "SELECT p.*, p.`firm_id` AS `firm_id`,
			strftime('%d.%m.%Y', p.prchk_date, 'localtime') AS prchk_date2,
			strftime('%d.%m.%Y', p.prchk_stm_date, 'localtime') AS prchk_stm_date2,
			w.`fname`, w.`sname`, w.`lname`, w.`sex`, w.`egn`,
			f.`name` AS `firm_name`
			FROM `medical_precheckups` p
			LEFT JOIN `workers` w ON (w.`worker_id` = p.`worker_id`)
			LEFT JOIN `firms` f ON (f.`firm_id` = p.`firm_id`)
			WHERE p.`worker_id` = $worker_id
			ORDER BY p.`prchk_date` DESC, p.`precheckup_id` ASC";
	$flds = $dbInst->query($sql);
	if(!empty($flds)) {
		$ret = '';
		$cnt = 1;
		foreach ($flds as $ary) {
			$templatePrecheckup = new TemplateHTML("tpls/medical_precheckup.html");
			$templatePrecheckup->replaceTag('cnt', $cnt);
			foreach ($ary as $key => $val) {
				if(is_numeric($key)) continue;// Important!
				if('prchk_conclusion' == $key) {
					switch ($val) {
						case '1': $val = 'Лицето може да изпълнява тази длъжност/професия'; break;
						case '2': $val = 'Лицето може да изпълнява тази длъжност/професия при сл. условия'; break;
						case '0': $val = 'Лицето не може да изпълнява тази длъжност/професия'; break;
						case '3': $val = 'Не може да се прецени пригодността на лицето да изпълнява тази длъжност/професия'; break;
					}
				}
				if('prchk_conditions' == $key && !empty($val)) { $val = ': '.$val; }
				if(in_array($key, array('prchk_obstetrician_doc', 'prchk_dermatologist_doc', 'prchk_internal_diseases_doc')) && !empty($val)) { $val = $val.': '; }
				if('' == $val && !in_array($key, array('prchk_conditions'))) { $val = '--'; }
				$templatePrecheckup->replaceTag($key, $val);
			}
			
			//Специалисти
			$array = array();
			$sql = "SELECT s.SpecialistName AS SpecialistName , c.conclusion AS conclusion , c.SpecialistID AS SpecialistID
					FROM medical_precheckups_doctors2 c
					LEFT JOIN Specialists s ON ( s.SpecialistID = c.SpecialistID )
					WHERE c.precheckup_id = $ary[precheckup_id]
					ORDER BY s.SpecialistName , s.SpecialistID";
			$rows = $dbInst->query($sql);
			if(!empty($rows)) {
				$i = 1;
				foreach ($rows as $row) {
					$array[] = array('№' => $i++, 'Лекар' => $row['SpecialistName'], 'Име и заключение' => $row['conclusion']);
				}
			}
			$templatePrecheckup->replaceHtmlTableData("precheckup_conclusions", $array, true, 1, $trHeader, $tdHeader);
			
			//Заболявания (диагнози)
			$array = array();
			$rows = $dbInst->getPrchkDiagnosis($ary['precheckup_id']);
			if(!empty($rows)) {
				$i = 1;
				foreach ($rows as $row) {
					$array[] = array('№' => $i++, 'МКБ' => $row['mkb_id'].' - '.$row['mkb_desc'], 'Диагноза' => $row['diagnosis'], 'Издадена от' => $row['doctor_pos_name']);
				}
			}
			$templatePrecheckup->replaceHtmlTableData("diagnosis", $array, true, 1, $trHeader, $tdHeader);

			$cnt++;
			$ret .= $templatePrecheckup->getPage();
			unset($templatePrecheckup);
		}
		$templateMain->replaceTag("medical_precheckups", $ret);
	} else {
		$templateMain->replaceTag("medical_precheckups_nodata", ' - няма предоставени данни.');
	}

	// Профилактични медицински прегледи
	$flds = $dbInst->getMedicalCheckupList($worker_id); // patient medical checkups list
	if(!empty($flds)) {
		$ret = '';
		$cnt = 1;
		foreach ($flds as $ary) {
			$templateCheckup = new TemplateHTML("tpls/medical_checkup.html");
			$templateCheckup->replaceTag('cnt', $cnt);
			$checkup_id = $ary['checkup_id'];
			foreach ($ary as $key => $val) {
				if(is_numeric($key)) continue;// Important!
				if('hospital' == $key) {
					if(!($_data = @unserialize($val))) {
						$_data = array();
					}
					$tmp = array();
					if(!empty($_data)) {
						foreach ($_data as $r) {
							if(empty($r)) continue;
							$tmp[] = $r;
						}
					}
					$val = implode(', ', $tmp);
				}
				if(in_array($key, array('home_stress', 'work_stress', 'social_stress', 'video_display', 'smoking', 'drinking', 'fats', 'diet', 'low_activity'))) {
					$val = (empty($val)) ? 'Не' : 'Да';
				}
				if('stm_conclusion' == $key) {
					switch ($val) {
						case '1': $val = 'Лицето може да изпълнява тази длъжност/професия'; break;
						case '2': $val = 'Лицето може да изпълнява тази длъжност/професия при сл. условия'; break;
						case '0': $val = 'Лицето не може да изпълнява тази длъжност/професия'; break;
						case '3': $val = 'Не може да се прецени пригодността на лицето да изпълнява тази длъжност/професия'; break;
					}
				}
				if('stm_conditions' == $key && !empty($val)) { $val = ': '.$val; }
				if('stm_date' == $key && !empty($val) && false !== $ts = strtotime($val)) { $val = date('d.m.Y', $ts).' г.'; }
				if('' == $val && !in_array($key, array('stm_conditions'))) { $val = '--'; }
				$templateCheckup->replaceTag($key, $val);
			}
			$templateCheckup->replaceTag('worker_age_till_checkup', worker_age($f['birth_date2'], $ary['checkup_date_h']).' г.');
			$templateCheckup->replaceTag('worker_age_at_present', worker_age($f['birth_date2'], date("d.m.Y")).' г.');

			//Фамилна обремененост
			$array = array();
			$rows = $dbInst->getFamilyWeights($checkup_id);
			if(!empty($rows)) {
				$i = 1;
				foreach ($rows as $row) {
					$array[] = array('№' => $i++, 'МКБ' => $row['mkb_code'].' - '.$row['mkb_desc'], 'Диагноза' => $row['diagnosis']);
				}
			}
			if(empty($array)) { $array[] = array('№' => '--', 'МКБ' => '--', 'Диагноза' => '--'); }
			$templateCheckup->replaceHtmlTableData("familyWeights", $array, true, 1, $trHeader, $tdHeader);

			//Анамнеза
			$array = array();
			$rows = $dbInst->getAnamnesis($checkup_id);
			if(!empty($rows)) {
				$i = 1;
				foreach ($rows as $row) {
					$array[] = array('№' => $i++, 'МКБ' => $row['mkb_code'].' - '.$row['mkb_desc'], 'Диагноза' => $row['diagnosis']);
				}
			}
			if(empty($array)) { $array[] = array('№' => '--', 'МКБ' => '--', 'Диагноза' => '--'); }
			$templateCheckup->replaceHtmlTableData("anamnesis", $array, true, 1, $trHeader, $tdHeader);

			//Лабораторни изследвания
			$array = array();
			$rows = $dbInst->getLabCheckups($checkup_id);
			if(!empty($rows)) {
				$i = 1;
				foreach ($rows as $row) {
					$deviation = calcDeviation($row['pdk_min'], $row['pdk_max'], $row['checkup_level']);
					if(preg_match('/\<img.*?alt="(.*?)"\s+\/\>/', $deviation, $matches)) {
						$deviation = ('minus' == $matches[1]) ? '-' : '+';
					} else {
						$deviation = '';
					}
					$array[] = array('№' => $i++, 'Вид' => $row['checkup_type'], 'Показател' => $row['indicator_id'], 'Ниво' => $row['checkup_level'], 'МЕ' => $row['indicator_dimension'], 'Min' => $row['pdk_min'], 'Max' => $row['pdk_max'], 'Откл.' => $deviation);
				}
			}
			if(empty($array)) { $array[] = array('№' => '--', 'Вид' => '--', 'Показател' => '--', 'Ниво' => '--', 'МЕ' => '--', 'Min' => '--', 'Max' =>'--', 'Откл.' => '--'); }
			$templateCheckup->replaceHtmlTableData("labCheckups", $array, true, 1, $trHeader, $tdHeader);

			//Заболявания (диагнози)
			$array = array();
			$rows = $dbInst->getDiseases($checkup_id);
			if(!empty($rows)) {
				$i = 1;
				foreach ($rows as $row) {
					$array[] = array('№' => $i++, 'МКБ' => $row['mkb_code'].' - '.$row['mkb_desc'], 'Диагноза' => $row['diagnosis'], 'Новооткрито?' => (($row['is_new'] == '1') ? 'Да' : 'Не'));
				}
			}
			if(empty($array)) { $array[] = array('№' => '--', 'МКБ' => '--', 'Диагноза' => '--', 'Новооткрито?' => '--'); }
			$templateCheckup->replaceHtmlTableData("diagnosis", $array, true, 1, $trHeader, $tdHeader);
			
			//Заключение
			$array = array();
			$rows = $dbInst->getDoctorsDesc($checkup_id);
			if(!empty($rows)) {
				$i = 1;
				foreach ($rows as $row) {
					$array[] = array('№' => $i++, 'Лекар' => $dbInst->my_mb_ucfirst($row['SpecialistName']), 'Име и заключение' => ((!empty($row['conclusion'])) ? $row['conclusion'] : '--'));
				}
			}
			if(empty($array)) { $array[] = array('№' => '--', 'Лекар' => '--', 'Име и заключение' => '--'); }
			$templateCheckup->replaceHtmlTableData("conclusions", $array, true, 1, $trHeader, $tdHeader);

			$cnt++;
			$ret .= $templateCheckup->getPage();
			unset($templateCheckup);
		}
		$templateMain->replaceTag("medical_checkups", $ret);
	} else {
		$templateMain->replaceTag("medical_checkups_nodata", ' - няма предоставени данни.');
	}

	//Болнични листове
	$rows = $dbInst->getPatientCharts($worker_id);
	if(!empty($rows)) {
		$i = 1;
		$array = array();
		$chart_types = $dbInst->getChartTypes();
		foreach ($rows as $row) {
			if(!($medical_types_arr = @unserialize($row['medical_types']))) {
				$medical_types_arr = array();
			}
			$medical_types = null;
			if($chart_types) {
				foreach ($chart_types as $chart_type) {
					if(!is_array($medical_types_arr)) continue;
					if(in_array($chart_type['type_id'], $medical_types_arr)) {
						switch ($chart_type['type_id']) {
							case '1':
								$c = 'blue';
								break;
							case '2':
								$c = 'red';
								break;
							case '3':
								$c = 'orange';
								break;
							default:
								$c = 'black';
								break;
						}
						$medical_types[] = $chart_type['type_desc_short'];
					}
				}
			}
			$array[] = array('№' => $i++, 'От' => $row['hospital_date_from'], 'На раб. на' => $row['hospital_date_to'], 'ВН (дни)' => $row['days_off'], 'МКБ' => $row['mkb_id'].' - '.$row['mkb_desc'], 'Причина' => $row['reason_id'].' - '.$row['reason_desc'], 'Вид' => (($medical_types != null) ? implode(', ', $medical_types) : ''), 'Разш. диагноза' => $row['chart_desc']);
		}
		$templatePatientChart = new TemplateHTML("tpls/patient_chart.html");
		$templatePatientChart->replaceHtmlTableData("patient_charts", $array, true, 1, $trHeader, $tdHeader);
		$templateMain->replaceTag("patient_charts", $templatePatientChart->getPage());
		unset($templatePatientChart);
	} else {
		$templateMain->replaceTag("patient_charts_nodata", ' - няма предоставени данни.');
	}

	//Експертни решения от ТЕЛК
	$rows = $dbInst->getPatientTelks($worker_id);
	if(!empty($rows)) {
		$i = 1;
		$array = array();
		foreach ($rows as $row) {
			switch ($row['telk_duration']) {
				case '1': $row['telk_duration'] = '1 г.'; break;
				case '2': $row['telk_duration'] = '2 г.'; break;
				case '3': $row['telk_duration'] = '3 г.'; break;
				case 'life': $row['telk_duration'] = 'пожизнен'; break;
			}
			if(!empty($row['telk_duration'])) { $row['telk_duration'] = ' ('.$row['telk_duration'].')'; }
			$array[] = array('№' => $i++, 'Експ. решение №' => $row['telk_num'].'/'.$row['telk_date_from_h'], 'Срок на инвалид-ността до' => $row['telk_date_to_h'].$row['telk_duration'], 'Дата на първа инвалиди-зация' => $row['first_inv_date_h'], '% тр. неработо-способ-ност' => $row['percent_inv'], 'МКБ водеща диагноза' => $row['mkb_id_1'], 'МКБ общо заболяване' => $row['mkb_id_2'], 'МКБ тр. злопо-лука' => $row['mkb_id_3'], 'МКБ проф. заболя-ване' => $row['mkb_id_4'], 'Противо-показни усл. на труд' => $row['bad_work_env']);
		}
		$templatePatientChart = new TemplateHTML("tpls/telk.html");
		$templatePatientChart->replaceHtmlTableData("telks", $array, true, 1, $trHeader, $tdHeader);
		$templateMain->replaceTag("telks", $templatePatientChart->getPage());
		unset($templatePatientChart);
	} else {
		$templateMain->replaceTag("telks_nodata", ' - няма предоставени данни.');
	}
	
	$tpl = $templateMain->getPage();
	$tpl = preg_replace('/\<table.*?name="pro_route"\>\s*\<\/table\>/is', '', $tpl);
	return $tpl;
}






