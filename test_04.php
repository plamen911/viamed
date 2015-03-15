<?php
header('Content-Type: text/html; charset=utf-8');
require('includes.php');
error_reporting(E_ALL);

$firm_id = 258;
$IDs = array(27384, 27541);// Албена Стаменова, ЕГН 6806077252

function get_worker_data($dbInst = null, $firm_id = 0, $IDs = array()) {
	$data = array();
	$s = $dbInst->getStmInfo();
	$firm = $dbInst->getFirmInfo($firm_id);
	
	$data['stm_name'] = preg_replace('/\<br\s*\/?\>/', '', $s['stm_name']);
	$data['stm_name_short'] = $dbInst->shortStmName($data['stm_name']);
	$data['stm_address'] = $s['address'];
	$data['firm_name'] = preg_replace('/[^A-Za-zА-Яа-я0-9\-_\.]/u', ' ', $firm['firm_name']);
	$data['location_name'] = $firm['location_name'];
	$data['address'] = $firm['address'];
	$data['workers'] = array();
	
	if (empty($IDs)) return $data;
	
	$chart_types = $dbInst->getChartTypes();
	
	$aWorkEnvProtocols = array();
	$sql = "SELECT m.map_id AS map_id, m.subdivision_id AS subdivision_id, m.wplace_id AS wplace_id,
			p.*,
			f.*,
			strftime('%d.%m.%Y', p.prot_date, 'localtime') AS prot_date_h
			FROM wplace_prot_map m
			LEFT JOIN work_env_protocols p ON (p.prot_id = m.prot_id)
			LEFT JOIN work_env_factors f ON (f.factor_id = p.factor_id)
			WHERE m.firm_id = $firm_id
			GROUP BY m.map_id
			ORDER BY p.prot_date";
	$rows = $dbInst->query($sql);
	if (!empty($rows)) {
		foreach ($rows as $row) {
			$ary = array();
			$ary['factor_name'] = $row['factor_name'];
			$ary['prot_num'] = $row['prot_num'] . (($row['prot_date_h'] != '') ? '/' . $row['prot_date_h'] . ' г.' : '');
			$ary['prot_norms'] = $row['level'] . ' ' . $row['factor_dimension'];
			$ary['prot_data'] = (($row['pdk_min'] != '') ? $row['pdk_min'] : '') . (($row['pdk_max'] != '') ? ' - '.$row['pdk_max'] : '') . ' ' . $row['factor_dimension'];
			$ary['prot_date'] = (empty($row['prot_date'])) ? '0000-00-00' : $row['prot_date'];
			
			$aWorkEnvProtocols[$row['subdivision_id']][$row['wplace_id']] = $ary;
		}
	}
	
	$aWPlaceFactorsInfo = array();//SELECT * FROM wplace_factors_map WHERE firm_id = 258
	$sql = "SELECT * FROM wplace_factors_map WHERE firm_id = $firm_id";
	$rows = $dbInst->query($sql);
	if (!empty($rows)) {
		foreach ($rows as $row) {
			$i = 1;
			$ary = array();
			if(isset($row['fact_dust']) && $row['fact_dust'] != '') {
				$ary[] = '1.3.2.' . $i++ . '. Прах – вид: ' . $row['fact_dust'];
			}
			if(isset($row['fact_chemicals']) && $row['fact_chemicals'] != '') {
				$ary[] = '1.3.2.' . $i++ . '. Химични агенти – вид: ' . $row['fact_chemicals'];
			}
			if(isset($row['fact_biological']) && $row['fact_biological'] != '') {
				$ary[] = '1.3.2.' . $i++ . '. Биологични агенти: ' . $row['fact_biological'];
			}
			if(isset($row['fact_work_pose']) && $row['fact_work_pose'] != '') {
				$ary[] = '1.3.2.' . $i++ . '. Работна поза: ' . $row['fact_work_pose'];
			}
			if(isset($row['fact_manual_weights']) && $row['fact_manual_weights'] != '') {
				$ary[] = '1.3.2.' . $i++ . '. Ръчна работа с тежести: ' . $row['fact_manual_weights'];
			}
			if(isset($row['fact_monotony']) && $row['fact_monotony'] != '') {
				$ary[] = '1.3.2.' . $i++ . '. Двигателна монотонна работа: ' . $row['fact_monotony'];
			}
			if(isset($row['fact_nervous']) && $row['fact_nervous'] != '') {
				$ary[] = '1.3.2.' . $i++ . '. Нервно-психично напрежение: ' . $row['fact_nervous'];
			}
			if(isset($row['fact_nervous']) && ($row['fact_work_regime'] != '' || $row['fact_work_hours'] != '' || $row['fact_work_and_break'] != '')) {
				$ary[] = '1.3.2.' . $i . '. Организация на труда:';
				$j = 1;
				if($row['fact_work_regime'] != '') {
					$ary[] = '1.3.2.' .$i . '.' . $j++ . '. режим на работа: ' . $row['fact_work_regime'];
				}
				if($row['fact_work_hours'] != '') {
					$ary[] = '1.3.2.' .$i . '.' . $j++ . '. продължителност на работното време: ' . $row['fact_work_hours'];
				}
				if($row['fact_work_and_break'] != '') {
					$ary[] = '1.3.2.' .$i . '.' . $j++ . '. физиологични режими на труд и почивка: ' . $row['fact_work_and_break'];
				}
				$i++;
			}
			if(isset($row['fact_other']) && $row['fact_other'] != '') {
				$ary[] = '1.3.2.' . $i++ . '. Други: ' . $row['fact_other'];
			}
			$aWPlaceFactorsInfo[$row['subdivision_id']][$row['wplace_id']] = $ary;
		}
	}
	
	$aWorkers = array();
	$sql = "SELECT w.worker_id AS worker_id, w.fname AS fname, w.sname AS sname, w.lname AS lname, w.egn AS egn,
			w. address AS  address, w.sex AS sex, w.phone1 AS phone1, w.phone2 AS phone2, w.prchk_author AS prchk_author,
			strftime('%d.%m.%Y', w.birth_date, 'localtime') AS birth_date2,
			strftime('%d.%m.%Y', w.date_curr_position_start, 'localtime') AS date_curr_position_start2,
			strftime('%d.%m.%Y', w.date_career_start, 'localtime') AS date_career_start2,
			strftime('%d.%m.%Y', w.date_retired, 'localtime') AS date_retired2,
			strftime('%d.%m.%Y', w.prchk_date, 'localtime') AS prchk_date2,
			strftime('%d.%m.%Y', w.prchk_stm_date, 'localtime') AS prchk_stm_date2,
			w.prchk_conclusion AS prchk_conclusion, w.prchk_conditions AS prchk_conditions,
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
			LEFT JOIN locations l ON (l.location_id = w.location_id)
			LEFT JOIN communities c ON (c.community_id = l.community_id)
			LEFT JOIN provinces r ON (r.province_id = c.province_id)
			LEFT JOIN firm_struct_map m ON (m.map_id = w.map_id)
			LEFT JOIN subdivisions s ON (s.subdivision_id = m.subdivision_id)
			LEFT JOIN work_places p ON (p.wplace_id = m.wplace_id)
			LEFT JOIN firm_positions i ON (i.position_id = m.position_id)
			LEFT JOIN doctors d ON (w.doctor_id = d.doctor_id)
			WHERE w.worker_id IN (" . implode(',', $IDs) . ")
			AND w.is_active = '1'
			GROUP BY w.worker_id";
	$rows = $dbInst->query($sql);
	if (!empty($rows)) {
		foreach ($rows as $row) {
			$worker_id = $row['worker_id'];
			$aWorkers[$worker_id]['worker_id'] = $worker_id;
			$aWorkers[$worker_id]['fname'] = $row['fname'];
			$aWorkers[$worker_id]['sname'] = $row['sname'];
			$aWorkers[$worker_id]['lname'] = $row['lname'];
			$aWorkers[$worker_id]['egn'] = $row['egn'];
			$aWorkers[$worker_id]['birth_date2'] = $row['birth_date2'];
			$aWorkers[$worker_id]['sex'] = $row['sex'];
			$aWorkers[$worker_id]['phone1'] = $row['phone1'];
			$aWorkers[$worker_id]['phone2'] = $row['phone2'];
			$aWorkers[$worker_id]['worker_name'] = str_replace(' ', '_', (mb_substr($row['fname'], 0, 1, 'utf-8') . ' ' . $row['lname']));
			$aWorkers[$worker_id]['worker_names'] = $row['fname'] . ' ' . $row['sname'] . ' ' . $row['lname'];
			$aWorkers[$worker_id]['subdivision_name'] = $row['subdivision_name'];
			$aWorkers[$worker_id]['wplace_name'] = $row['wplace_name'];
			$aWorkers[$worker_id]['position_name'] = $row['position_name'];
			$aWorkers[$worker_id]['position_workcond'] = $row['position_workcond'];
			$aWorkers[$worker_id]['wplace_workcond'] = $row['wplace_workcond'];
			$aWorkers[$worker_id]['date_curr_position_start2'] = $row['date_curr_position_start2'];
			$aWorkers[$worker_id]['date_career_start2'] = $row['date_career_start2'];
			$aWorkers[$worker_id]['date_retired2'] = $row['date_retired2'];
			
			$basicInfo = $row['egn'] . ', ';
			if( ('' != $row['location_name'] || '' != $row['address'])) {
				$basicInfo .= 'постоянен адрес: ';
				$basicInfo .= ('' != $row['province_name']) ? 'област ' . HTMLFormat($row['province_name']) . ', ' : '';
				$basicInfo .= ('' != $row['community_name']) ? 'община  ' . HTMLFormat($row['community_name']) . ', ' : '';
				$basicInfo .= ('1' == $row['location_type']) ? 'гр.' : 'с.';
				$basicInfo .= ('' != $row['location_name']) ? HTMLFormat($row['location_name']) . ', ' : '';
				$basicInfo .= $row['address'];
			}
			$aWorkers[$worker_id]['basic_info'] = preg_replace('/\s+/u', ' ', $basicInfo);
			
			$currentProRoute  = mb_strtoupper($row['position_name'], 'utf-8');
			$currentProRoute .= ' – от '.(('' != $row['date_curr_position_start2']) ? $row['date_curr_position_start2'].' г.' : '');
			$aWorkers[$worker_id]['current_pro_route'] = $currentProRoute;
			$aWorkers[$worker_id]['prev_pro_routes'] = array();
			$aWorkers[$worker_id]['readjustments'] = array();
			$aWorkers[$worker_id]['pro_diseases'] = array();
			$aWorkers[$worker_id]['work_accidents'] = array();
			$aWorkers[$worker_id]['readjustment_50down'] = array();
			$aWorkers[$worker_id]['readjustment_50up'] = array();
			$aWorkers[$worker_id]['vnr'] = array();
			$aWorkers[$worker_id]['work_env_protocols'] = (isset($aWorkEnvProtocols[$row['subdivision_id']][$row['wplace_id']])) ? $aWorkEnvProtocols[$row['subdivision_id']][$row['wplace_id']] : array();
			$aWorkers[$worker_id]['wplace_factors_info'] = (isset($aWPlaceFactorsInfo[$row['subdivision_id']][$row['wplace_id']])) ? $aWPlaceFactorsInfo[$row['subdivision_id']][$row['wplace_id']] : array();
			
			$aWorkers[$worker_id]['prchk_date2'] = $row['prchk_date2'];
			$aWorkers[$worker_id]['prchk_author'] = $row['prchk_author'];
			$aPrchkConclusions = array();
			if(!empty($row['prchk_date2'])) {
				$flds = $dbInst->getPrchkDocDiagnosis($worker_id);
				if($flds) {
					foreach ($flds as $fld) {
						$aPrchkConclusions[] = $fld['doctor_pos_name'] . ((!empty($fld['doc_name'])) ? ' ('.$fld['doc_name'].')' : '') . ': ' . $fld['doc_conclusion'];
					}
				}
			}
			$aWorkers[$worker_id]['prchk_conclusions'] = $aPrchkConclusions;
			$aWorkers[$worker_id]['prchk_diagnosis'] = array();
			
			$stm_conclusion = '';
			if('1' == $row['prchk_conclusion']) {
				$stm_conclusion .= '<b>Може</b> да изпълнява посочената длъжност/професия';
			} elseif ('2' == $row['prchk_conclusion']) {
				$stm_conclusion .= '<b>Може</b> да изпълнява посочената длъжност/професия при следните условия:' . "\n";
				$stm_conclusion .= $row['prchk_conditions'];
			}
			elseif ($row['prchk_conclusion'] == '0') {
				$stm_conclusion .= '<b>Не може</b> да изпълнява посочената длъжност/професия';
			}
			$aWorkers[$worker_id]['prchk_stm_conclusion'] = $stm_conclusion;
			
			$aWorkers[$worker_id]['checkup_list'] = array();
			
		}
	}
	
	$sql = "SELECT *, strftime('%d.%m.%Y', checkup_date, 'localtime') AS checkup_date_h
			FROM medical_checkups
			WHERE worker_id IN (" . implode(',', $IDs) . ")
			ORDER BY checkup_date DESC, checkup_id DESC";
	
	
	$sql = "SELECT c.*,
			strftime('%d.%m.%Y', c.checkup_date, 'localtime') AS checkup_date_h,
			w.*,
			(SELECT location_name FROM locations WHERE location_id = w.location_id) AS worker_location,
			strftime('%d.%m.%Y', w.birth_date, 'localtime') AS birth_date2,
			strftime('%d.%m.%Y', w.date_curr_position_start, 'localtime') AS date_curr_position_start2,
			strftime('%d.%m.%Y', w.date_career_start, 'localtime') AS date_career_start2,
			strftime('%d.%m.%Y', w.date_retired, 'localtime') AS date_retired2,
			strftime('%d.%m.%Y', c.stm_date, 'localtime') AS stm_date2,
			f.name AS firm_name,
			l.location_name,
			s.subdivision_name,
			p.wplace_name,
			t.position_name
			FROM medical_checkups c
			LEFT JOIN firms f ON (f.firm_id = c.firm_id)
			LEFT JOIN locations l ON (l.location_id = f.location_id)
			LEFT JOIN workers w ON (w.worker_id = c.worker_id)
			LEFT JOIN firm_struct_map m ON (m.map_id = w.map_id)
			LEFT JOIN subdivisions s ON (s.subdivision_id = m.subdivision_id)
			LEFT JOIN work_places p ON (p.wplace_id = m.wplace_id)
			LEFT JOIN firm_positions t ON(t.position_id = m.position_id)
			WHERE c.worker_id IN (" . implode(',', $IDs) . ")
			AND w.is_active = '1'
			ORDER BY c.checkup_date DESC, c.checkup_id DESC";
	$rows = $dbInst->query($sql);
	if (!empty($rows)) {
		$i = 1;
		foreach ($rows as $row) {
			$worker_id = $row['worker_id'];
			$checkup_id = $row['checkup_id'];
			
			$_arr = array();
			$hospitals = '';
			if($_data = @unserialize($row['hospital'])) {
				for ($j = 0; $j < count($_data); $j++) {
					if(mb_strlen($_data[$j], 'utf-8') == 0) continue;
					else $_arr[] = stripslashes($_data[$j]);
				}
				//$hospitals = (mb_strlen($hospitals, 'utf-8') > 2) ? mb_substr($hospitals, 0, (mb_strlen($hospitals) - 2), 'utf-8') : '';
				$hospitals = (count($_arr)) ? implode(', ', $_arr) : '';
			}
			
			$doctors = '';
			$flds = $dbInst->getDoctorsDesc($checkup_id);
			if($flds) {
				$_arr = array();
				foreach ($flds as $fld) {
					if($fld['conclusion'] == '') continue;
					$_arr[] = $dbInst->my_mb_ucfirst(HTMLFormat($fld['SpecialistName']));
				}
				if(count($_arr)) {
					$doctors = ': '.implode(', ', $_arr);
				}
			}
			
			
			
			
			
			
		}
	}
	
	
	$sql = "SELECT d.*, m.mkb_desc, m.mkb_code, p.doctor_pos_name
			FROM prchk_diagnosis d
			LEFT JOIN mkb m ON (m.mkb_id = d.mkb_id)
			LEFT JOIN cfg_doctor_positions p ON (p.doctor_pos_id = d.published_by)
			WHERE d.worker_id IN (" . implode(',', $IDs) . ")
			ORDER BY d.prchk_id";
	$rows = $dbInst->query($sql);
	if (!empty($rows)) {
		$i = 1;
		foreach ($rows as $row) {
			$worker_id = $row['worker_id'];
			
			$mkb_desc = $row['mkb_desc'];
			if(!empty($row['diagnosis'])) {
				$mkb_desc .= "\n" . $row['diagnosis'];
			}
			
			$ary = array();
			$ary['mkb_id'] = $row['mkb_id'];
			$ary['mkb_desc'] = $mkb_desc;
			$ary['doctor_pos_name'] = $row['doctor_pos_name'];
			
			$aWorkers[$worker_id]['prchk_diagnosis'][] = $ary;
		}
	}
	
	$sql = "SELECT * FROM pro_route WHERE worker_id IN (" . implode(',', $IDs) . ") ORDER BY route_id";
	$rows = $dbInst->query($sql);
	if (!empty($rows)) {
		$i = 1;
		foreach ($rows as $row) {
			$worker_id = $row['worker_id'];
			
			$ary = array();
			$ary['num'] = $i;
			$ary['firm_name'] = $row['firm_name'];
			$ary['position'] = $row['position'];
			$length_yy = ($row['exp_length_y']) ? HTMLFormat($row['exp_length_y']).' г.' : '';
			$length_mm = ($row['exp_length_m']) ? HTMLFormat($row['exp_length_m']).' м.' : '';
			$ary['experience'] = trim($length_yy.' '.$length_mm);
			
			$aWorkers[$worker_id]['prev_pro_routes'][] = $ary;
			
			$i++;
		}
	}
	
	$sql = "SELECT * FROM readjustments WHERE worker_id IN (" . implode(',', $IDs) . ") ORDER BY id";
	$rows = $dbInst->query($sql);
	if (!empty($rows)) {
		foreach ($rows as $row) {
			$worker_id = $row['worker_id'];
			
			$ary = array();
			$published_on = (!empty($row['published_on']) && false !== $ts = strtotime($row['published_on'])) ? date('d.m.Y', $ts) . ' г.' : '';
			$period  = (!empty($row['start_date']) && false !== $ts = strtotime($row['start_date'])) ? date('d.m.y', $ts) : '';
			$period .= (!empty($row['end_date']) && false !== $ts = strtotime($row['end_date'])) ? ' ÷ ' . date('d.m.y', $ts) : '';
			$ary['published_on'] = $published_on;
			$ary['mkb_id'] = $row['mkb_id'];
			$ary['diagnosis'] = $row['diagnosis'];
			$ary['commission'] = $row['commission'];
			$ary['period'] = $period;
			$ary['place'] = $row['place'];
			
			$aWorkers[$worker_id]['readjustments'][] = $ary;
		}
	}
	
	// Professional diseases and work accidents
	$mycfg = array(
		'pro_diseases' => array(
			'mkb_num' => '4',
			'reasons' => array('02', '03')
		),
		'work_accidents' => array(
			'mkb_num' => '3',
			'reasons' => array('04', '05')
		)
	);
	
	foreach ($mycfg as $type => $ary) {
		$mkb_num = $ary['mkb_num'];
		$reasons = $ary['reasons'];
		
		// 1). from telks
		$sql = "SELECT t.worker_id AS worker_id, t.telk_num AS telk_num,
				strftime('%d.%m.%Y', t.telk_date_from, 'localtime') AS telk_date_from2,
				strftime('%d.%m.%Y', t.telk_date_to, 'localtime') AS telk_date_to2,
				strftime('%d.%m.%Y', t.first_inv_date, 'localtime') AS first_inv_date2,
				m.mkb_id AS mkb_id, m.mkb_desc AS mkb_desc, m.mkb_code AS mkb_code
				FROM telks t
				LEFT JOIN mkb m ON (m.mkb_id = t.mkb_id_$mkb_num)
				WHERE t.worker_id IN (" . implode(',', $IDs) . ")
				AND t.mkb_id_$mkb_num != ''
				ORDER BY t.telk_date_from DESC, t.telk_id";
		$rows = $dbInst->query($sql);
		if (!empty($rows)) {
			foreach ($rows as $row) {
				$aWorkers[$row['worker_id']][$type][] = 'Експертно решение на ТЕЛК № ' . $row['telk_num'] . '/' . $row['telk_date_from2'] . ' г., диагноза: ' . $row['mkb_id'] . ' – ' . $row['mkb_desc'];
			}
		}
		// 2). from patient chart
		$sql = "SELECT c.worker_id AS worker_id,
				strftime('%d.%m.%Y', hospital_date_from, 'localtime') AS hospital_date_from,
				strftime('%d.%m.%Y', hospital_date_to, 'localtime') AS hospital_date_to,
				r.reason_desc AS reason_desc,
				m.mkb_id AS mkb_id, m.mkb_desc AS mkb_desc, m.mkb_code AS mkb_code
				FROM patient_charts c
				LEFT JOIN mkb m ON (m.mkb_id = c.mkb_id)
				LEFT JOIN medical_reasons r ON (r.reason_id = c.reason_id)
				WHERE c.worker_id IN (" . implode(',', $IDs) . ") 
				AND c.reason_id IN ('" . implode("','", $reasons) . "')
				ORDER BY c.hospital_date_from, c.chart_id";
		$rows = $dbInst->query($sql);
		if (!empty($rows)) {
			foreach ($rows as $row) {
				$aWorkers[$row['worker_id']][$type][] = 'Болничен лист от ' . $row['hospital_date_from'] . ' г., диагноза: ' . $row['mkb_id'] . ' - ' . $row['mkb_desc'] . ', причина: ' . $row['reason_desc'];
			}
		}
	}
	
	// readjustment
	$sql = "SELECT t.worker_id AS worker_id, t.telk_num AS telk_num, t.percent_inv AS percent_inv, t.telk_duration AS telk_duration,
			strftime('%d.%m.%Y', t.telk_date_from, 'localtime') AS telk_date_from2,
			strftime('%d.%m.%Y', t.telk_date_to, 'localtime') AS telk_date_to2,
			strftime('%d.%m.%Y', t.first_inv_date, 'localtime') AS first_inv_date2,
			m.mkb_id AS mkb_id, m.mkb_desc AS mkb_desc, m.mkb_code AS mkb_code
			FROM telks t
			LEFT JOIN mkb m ON (m.mkb_id = t.mkb_id_1)
			WHERE t.worker_id IN (" . implode(',', $IDs) . ")
			ORDER BY t.telk_date_to, t.telk_id";
	$rows = $dbInst->query($sql);
	if (!empty($rows)) {
		foreach ($rows as $row) {
			$line = 'Експертно решение на ТЕЛК № ' . $row['telk_num'] . '/' . $row['telk_date_from2'] . ' г., диагноза: ' . $row['mkb_id'] . ' – ' . $row['mkb_desc'];
			if (intval(50 > $row['percent_inv'])) {
				$aWorkers[$row['worker_id']]['readjustment_50down'][] = $line;
			} elseif (intval(50 <= $row['percent_inv'])) {
				$ary = array();
				$ary[] = $line;
				$ary[] = 'Срок: ' . (('пожизнен' == $row['telk_duration']) ? $row['telk_duration'] : 'до ' . $row['telk_date_to2'] . ' г. за ' . $row['telk_duration']) . ', загубена работоспособност: ' . $row['percent_inv'] . ' %';
				$ary[] = $row['percent_inv'];
				$aWorkers[$row['worker_id']]['readjustment_50up'][] = $ary;
			}
		}
	}
	
	// transfer to a more appropriate job (for reasons of health).
	$sql = "SELECT c.worker_id AS worker_id, c.reason_id AS reason_id, c.medical_types AS medical_types, c.days_off AS days_off,
			strftime('%d.%m.%Y', hospital_date_from, 'localtime') AS hospital_date_from,
			strftime('%d.%m.%Y', hospital_date_to, 'localtime') AS hospital_date_to,
			c.mkb_id AS mkb_id, m.mkb_desc AS mkb_desc, m.mkb_code AS mkb_code, r.reason_desc AS reason_desc
			FROM patient_charts c
			LEFT JOIN mkb m ON (m.mkb_id = c.mkb_id)
			LEFT JOIN medical_reasons r ON (r.reason_id = c.reason_id)
			WHERE c.worker_id IN (" . implode(',', $IDs) . ")
			ORDER BY c.hospital_date_from, c.chart_id";
	$rows = $dbInst->query($sql);
	if (!empty($rows)) {
		foreach ($rows as $row) {
			if (in_array(strval($row['reason_id']), array('16'))) {
				$aWorkers[$row['worker_id']]['readjustment_50down'][] = 'Болничен лист от ' . $row['hospital_date_from'] . ' г., диагноза: ' . $row['mkb_id'] . ' - ' . $row['mkb_desc'] . ', причина: ' . $row['reason_desc'];
			}
			
			if(!($medical_types_arr = @unserialize($row['medical_types']))) {
				$medical_types_arr = array();
			}
			$medical_types = null;
			if($chart_types) {
				foreach ($chart_types as $chart_type) {
					if(!is_array($medical_types_arr)) continue;
					
					if(in_array($chart_type['type_id'], $medical_types_arr)) {
						$medical_types[] = $chart_type['type_desc_short'];
					}
				}
			}
			$medical_types = ($medical_types != null) ? implode('<br />', $medical_types) : '';
			
			$ary = array();
			$ary['mkb_id'] = $row['mkb_id'];
			$ary['reason_id'] = $row['reason_id'];
			$ary['medical_types'] = $medical_types;
			$ary['days_off'] = $row['days_off'];
			$ary['hospital_date_from'] = $row['hospital_date_from'];
			
			$aWorkers[$row['worker_id']]['vnr'][] = $ary;
		}
	}
	
	


	
	//
	
	
	$data['workers'] = $aWorkers;
	
	return $data;
}

$data = get_worker_data($dbInst, $firm_id, $IDs);

echo '<pre>' . print_r($data, 1) . '</pre>';



















