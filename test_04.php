<?php
header('Content-Type: text/html; charset=utf-8');
require('includes.php');
error_reporting(E_ALL);

$firm_id = 258;
$IDs = array(27384, 27541);// Албена Стаменова, ЕГН 6806077252
//$IDs = array(27541);// Албена Стаменова, ЕГН 6806077252

function get_worker_data($dbInst = null, $firm_id = 0, $IDs = array()) {
	$data = array();
	$s = $dbInst->getStmInfo();
	$firm = $dbInst->getFirmInfo($firm_id);
	
	$data['stm_name'] = preg_replace('/\<br\s*\/?\>/', '', $s['stm_name']);
	$data['stm_name_short'] = $dbInst->shortStmName($data['stm_name']);
	$data['stm_address'] = $s['address'];
	$data['firm_name'] = preg_replace('/[^A-Za-zА-Яа-я0-9\-_\.]/u', ' ', $firm['firm_name']);
	$data['firm_location'] = $firm['location_name'];
	$data['firm_address'] = $firm['address'];
	$data['workers'] = array();
	
	if (empty($IDs)) return $data;
	
	$chart_types = $dbInst->getChartTypes();
	$labs = $dbInst->getLabs();
	
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
			
			$aWorkEnvProtocols[$row['subdivision_id']][$row['wplace_id']][] = $ary;
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
			$aWorkers[$worker_id]['patient_charts'] = array();
			$aWorkers[$worker_id]['work_env_protocols'] = (isset($aWorkEnvProtocols[$row['subdivision_id']][$row['wplace_id']])) ? $aWorkEnvProtocols[$row['subdivision_id']][$row['wplace_id']] : array();
			$aWorkers[$worker_id]['wplace_factors_info'] = (isset($aWPlaceFactorsInfo[$row['subdivision_id']][$row['wplace_id']])) ? $aWPlaceFactorsInfo[$row['subdivision_id']][$row['wplace_id']] : array();
			
			$aWorkers[$worker_id]['prchk_date2'] = $row['prchk_date2'];
			$aWorkers[$worker_id]['prchk_stm_date2'] = $row['prchk_stm_date2'];
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
			
			$aWorkers[$worker_id]['prophylactic_cards'] = array();
			
		}
	}
	
	$sql = "SELECT c.*,
			strftime('%d.%m.%Y', c.checkup_date, 'localtime') AS checkup_date_h,
			strftime('%d.%m.%Y', c.stm_date, 'localtime') AS stm_date2,
			t.position_name AS position_name
			FROM medical_checkups c
			LEFT JOIN workers w ON (w.worker_id = c.worker_id)
			LEFT JOIN firm_struct_map m ON (m.map_id = w.map_id)
			LEFT JOIN firm_positions t ON (t.position_id = m.position_id)
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
			
			$ary = array();
			$ary['checkup_id'] = $row['checkup_id'];
			$ary['checkup_date_h'] = (empty($row['checkup_date_h'])) ? '--' : $row['checkup_date_h'];
			$ary['hospitals'] = $hospitals;
			$ary['doctors'] = $doctors;
			$ary['EKG'] = $row['EKG'];
			$ary['x_ray'] = $row['x_ray'];
			$ary['echo_ray'] = $row['echo_ray'];
			$ary['left_eye'] = $row['left_eye'];
			$ary['left_eye2'] = $row['left_eye2'];
			$ary['right_eye'] = $row['right_eye'];
			$ary['right_eye2'] = $row['right_eye2'];
			$ary['VK'] = $row['VK'];
			$ary['FEO1'] = $row['FEO1'];
			$ary['tifno'] = $row['tifno'];
			$ary['hearing_loss'] = $row['hearing_loss'];
			$ary['left_ear'] = $row['left_ear'];
			$ary['right_ear'] = $row['right_ear'];
			$ary['hearing_diagnose'] = $row['hearing_diagnose'];
			
			//Фамилна обремененост
			$ary['fweights_descr'] = (!empty($row['fweights_descr'])) ? HTMLFormat($row['fweights_descr']) : '--';
			$ary['fweights_list'] = array();
			$flds = $dbInst->getFamilyWeights($checkup_id);
			if ($flds) {
				foreach ($flds as $fld) {
					$mkb_desc = HTMLFormat($fld['mkb_desc']);
					if($fld['diagnosis'] != '') {
						$mkb_desc .= "\n".HTMLFormat($fld['diagnosis']);
					}
					$ary['fweights_list'][] = array(
						'mkb_id' => $fld['mkb_id'],
						'mkb_desc' => $mkb_desc
					);
				}	
			}
			
			$ary['lab_tests'] = array();
			$flds = $dbInst->getLabCheckups($checkup_id);
			if ($flds) {
				foreach ($flds as $fld) {
					$ary['lab_tests'][] = array(
						'indicator_name' => $fld['indicator_type'] . ((!empty($fld['indicator_name'])) ? ' (' . $fld['indicator_name'] . ')' : ''),
						'pdk_min' => $fld['pdk_min'],
						'pdk_max' => $fld['pdk_max'],
						'checkup_level' => $fld['checkup_level'],
						'indicator_dimension' => $fld['indicator_dimension']
					);
				}
			}
			
			//Анамнеза
			$ary['anamnesis_descr'] = (!empty($row['anamnesis_descr'])) ? HTMLFormat($row['anamnesis_descr']) : '--';
			$ary['anamnesis_list'] = array();
			$flds = $dbInst->getAnamnesis($checkup_id);
			if ($flds) {
				foreach ($flds as $fld) {
					$mkb_desc = HTMLFormat($fld['mkb_desc']);
					if($fld['diagnosis'] != '') {
						$mkb_desc .= "\n" . HTMLFormat($fld['diagnosis']);
					}
					$ary['anamnesis_list'][] = array(
						'mkb_id' => $fld['mkb_id'],
						'mkb_desc' => $mkb_desc
					);
				}
			}
			
			$ary['diagnosis_list'] = array();
			$flds = $dbInst->getDiseases($checkup_id);
			if ($flds) {
				foreach ($flds as $fld) {
					$mkb_desc = HTMLFormat($fld['mkb_desc']);
					if(!empty($row['diagnosis'])) {
						$mkb_desc .= "\n" . HTMLFormat($fld['diagnosis']);
					}
					$ary['diagnosis_list'][] = array(
						'mkb_id' => $fld['mkb_id'],
						'mkb_desc' => $mkb_desc,
						'is_new' => ('1' == $fld['is_new']) ? 'да' : 'не'
					);
				}
			}
			
			$ary['conclusion_list'] = array();
			$flds = $dbInst->getDoctorsDesc($checkup_id);
			if ($flds) {
				foreach ($flds as $fld) {
					if($fld['conclusion'] == '') continue;
					$ary['conclusion_list'][] = $dbInst->my_mb_ucfirst(HTMLFormat($fld['SpecialistName'])) . ': ' . HTMLFormat($fld['conclusion']);
				}
			}
			
			$stm_conclusion = '';
			switch ($row['stm_conclusion']) {
				case '1':
					$stm_conclusion .= '<b>Може</b> да изпълнява посочената длъжност/професия ' . HTMLFormat($row['position_name']) . ' в ' . HTMLFormat($firm['name']);
					break;
				case '2':
					$stm_conclusion .= '<b>Може</b> да изпълнява посочената длъжност/професия ' . HTMLFormat($row['position_name']) . ' в ' . HTMLFormat($firm['name']) . ' при следните условия: ';
					break;
				case '0':
					$stm_conclusion .= '<b>Не може</b> да изпълнява посочената длъжност/професия ' . HTMLFormat($row['position_name']) . ' в ' . HTMLFormat($firm['name']);
					break;
				case '3':
					$stm_conclusion .= '<b>Не може да се прецени</b> пригодността на работещия да изпълнява посочената длъжност/професия ' . HTMLFormat($row['position_name']) . ' в ' . HTMLFormat($firm['name']);
					break;
			}
			if (empty($stm_conclusion)) {
				$stm_conclusion .= "\n";
			}
			$stm_conclusion .= $row['stm_conditions'] . ((!empty($row['stm_conditions2'])) ? "\n" . $row['stm_conditions2'] : '');
			
			$ary['stm_conclusion'] = $stm_conclusion;
			$ary['stm_date2'] = $row['stm_date2'];
			
			$aWorkers[$worker_id]['prophylactic_cards'][] = $ary;
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
			
			$aWorkers[$row['worker_id']]['patient_charts'][] = $ary;
		}
	}

	$data['workers'] = $aWorkers;
	
	return $data;
}

$results = get_worker_data($dbInst, $firm_id, $IDs);
if (!$results) {
	die('No data.');
}

/*echo '<pre>' . print_r($results, 1) . '</pre>';
die();*/

define('NO_DATA_AVAILABLE', 'Няма предоставени данни.');

require_once("cyrlat.class.php");
$cyrlat = new CyrLat;
$filename = 'Zdravni_Dosieta_'.$cyrlat->cyr2lat($results['firm_name']);

require('phprtflite/rtfbegin.php');

$sect->writeText('Приложение № 6 към чл.11, ал.10', $times10, $alignRight);
$sect->addEmptyParagraph();
$sect->writeText('<b>ЗДРАВНИ ДОСИЕТА</b>', $times20, $alignCenter);
$sect->writeText('<b>на ' . $results['firm_name'].'</b>', $times14, $alignCenter);
$sect->addEmptyParagraph();

if (!empty($results['workers'])) {
	foreach ($results['workers'] as $res) {
		$sect->writeText('<b>І. Паспортна част</b>', $times12, $alignLeft);
		$sect->writeText('<b>' . $res['worker_names'] . '</b>', $times14, $alignCenter);
		$sect->writeText('ЕГН: ' . $res['basic_info'], $times12, $alignLeft);
		$sect->writeText('Дата на раждане: ' . $res['birth_date2'] . ' г.', $times12, $alignLeft);
		$sect->writeText('Подразделение: ' . $res['subdivision_name'], $times12, $alignLeft);
		$sect->writeText('Работно място: ' . $res['wplace_name'], $times12, $alignLeft);
		$sect->writeText('Длъжност: ' . $res['position_name'], $times12, $alignLeft);
		if (!empty($res['date_retired2'])) {
			$sect->writeText('Напуснал на: ' . $res['date_retired2'] . ' г.', $times12, $alignLeft);
		}
		$sect->addEmptyParagraph();
		
		$sect->writeText('<b>ІІ. Професионален маршрут</b>', $times12, $alignLeft);
		$sect->writeText('1. Настоящ: ' . $res['current_pro_route'], $times12, $alignLeft);
		$rows = $res['prev_pro_routes'];
		$sect->writeText('2. Преди: ' . ((empty($rows)) ? NO_DATA_AVAILABLE : ''), $times12, $alignLeft);
		if (!empty($rows)) {
			$data = array();
			$data[] = array('Предприятие:', 'Длъжност/професия', 'Продължителност на стажа');
			foreach ($rows as $row) {
				$ary = array();
				array_push($ary, $row['num'] . '. ' . $row['firm_name']);
				array_push($ary, $row['position']);
				array_push($ary, $row['experience']);
				$data[] = $ary;
			}
			$colWidts = array(6, 6, 5);
			$colAligns = array('left', 'left', 'left');
			fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'plain');
		} else {
			$sect->addEmptyParagraph();
		}
		
		$rows = $res['readjustments'];
		if (!empty($rows)) {
			$sect->writeText('3. Трудоустрояване:', $times12, $alignLeft);
			$data = array();
			$data[] = array('Дата', 'МКБ', 'Диагноза', 'Комисия', 'Срок', 'Място на трудоустрояване');
			foreach ($rows as $row) {
				$ary = array();
				array_push($ary, $row['published_on']);
				array_push($ary, $row['mkb_id']);
				array_push($ary, $row['diagnosis']);
				array_push($ary, $row['commission']);
				array_push($ary, $row['period']);
				array_push($ary, $row['place']);
				$data[] = $ary;
			}
			$colWidts = array(2.5, 1.5, 5, 1.8, 3.2, 3);
			$colAligns = array('center', 'center', 'left', 'left', 'left', 'left');
			fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'small');
		}
		
		//
		
		$sect->writeText('<b>ІІІ. Данни за регистрирани професионални болести, трудови злополуки, трудоустрояване и за трайно намалена работоспособност</b>', $times12, $alignLeft);

		$sect->addEmptyParagraph();
		
		$rows = $res['pro_diseases'];
		$sect->writeText('1. Регистрирани професионални болести по данни на работещия и/или работодателя: ' . ((empty($rows)) ? NO_DATA_AVAILABLE : ''), $times12, $alignLeft);
		if (!empty($rows)) {
			foreach ($rows as $row) {
				$sect->writeText('- ' . $row, $times12, $alignLeft);
			}
		}
		
		$sect->addEmptyParagraph();
		
		$rows = $res['work_accidents'];
		$sect->writeText('2. Трудови злополуки по данни на работещия и/или работодателя: ' . ((empty($rows)) ? NO_DATA_AVAILABLE : ''), $times12, $alignLeft);
		if (!empty($rows)) {
			foreach ($rows as $row) {
				$sect->writeText('- ' . $row, $times12, $alignLeft);
			}
		}
		
		$sect->addEmptyParagraph();

		$rows = $res['readjustment_50down'];
		$sect->writeText('3. Трудоустрояване по данни на работещия и/или работодателя: ' . ((empty($rows)) ? NO_DATA_AVAILABLE : ''), $times12, $alignLeft);
		if (!empty($rows)) {
			foreach ($rows as $row) {
				$sect->writeText('- ' . $row, $times12, $alignLeft);
			}
		}
		
		$sect->addEmptyParagraph();
		
		$rows = $res['readjustment_50up'];
		$sect->writeText('4. Трайно намалена работоспособност по данни на работещия и/или работодателя: ' . ((empty($rows)) ? NO_DATA_AVAILABLE : ''), $times12, $alignLeft);
		if (!empty($rows)) {
			$i = 1;
			foreach ($rows as $row) {
				$sect->writeText('4.'. $i++ . '. ' . $row[0], $times12, $alignLeft);
				$sect->writeText($row[1] . "\n", $times12, $alignLeft);
				$percent_inv = intval($row[2]);
				$checkbox = $sect->addCheckbox();
				if(90 < $percent_inv) { $checkbox->setChecked(); }
				$sect->writeText('над 90 %', $times12, $alignLeft);
		
				$checkbox = $sect->addCheckbox();
				if(70 < $percent_inv && 90 >= $percent_inv) { $checkbox->setChecked(); }
				$sect->writeText('от 71 – 90 %', $times12, $alignLeft);
		
				$checkbox = $sect->addCheckbox();
				if(50 <= $percent_inv && 70 >= $percent_inv) { $checkbox->setChecked(); }
				$sect->writeText('от 50 – 70 %', $times12, $alignLeft);
			}
		}
		
		$sect->addEmptyParagraph();
		
		$rows = $res['patient_charts'];
		if (!empty($rows)) {
			$sect->writeText('5. ВНР', $times12, $alignLeft);
			$data = array();
			$data[] = array('МКБ', 'Причина', 'Вид', 'Брой дни', 'От дата');
			foreach ($rows as $row) {
				$data[] = array_values($row);
			}
			$colWidts = array(3, 3, 4, 3, 3);
			$colAligns = array('center', 'center', 'center', 'center', 'center');
			fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'small');
		}
		
		$str = '<b>ІV. Условия на труд и данни от проведени предварителни и периодични медицински прегледи и изследвания по време на работата на работещия</b> в ' . $results['firm_name'];
		if (!empty($results['firm_location'])) {
			$str .= ' - ' . $results['firm_location'];
		}
		if (!empty($results['firm_address'])) {
			$str .= ', ' . $results['firm_address'];
		}
		$sect->writeText($str, $times12, $alignLeft);
		
		$sect->addEmptyParagraph();
		
		$sect->writeText('1. Данни за изпълняваната в предприятието длъжност/професия, работното място и условията на труд', $times12, $alignLeft);
		$sect->writeText('1.1. Длъжност: ' . $dbInst->my_mb_ucfirst($res['position_name']), $times12, $alignLeft);
		$sect->writeText('1.2. Работно място: ' . $dbInst->my_mb_ucfirst($res['wplace_name']), $times12, $alignLeft);
		$sect->writeText('1.3. Условия на труд при длъжност/професия по т. 1.1 и работно място по т. 1.2', $times12, $alignLeft);
		$sect->writeText('1.3.1. Кратко описание на извършваната дейност:', $times12, $alignLeft);
		$i = 1;
		if (!empty($res['position_workcond'])) {
			$sect->writeText('1.3.1.' . $i++ . '. ' . $res['position_workcond'], $times12, $alignLeft);
		}
		if (!empty($res['wplace_workcond'])) {
			$sect->writeText('1.3.1.' . $i++ . '. ' . $res['wplace_workcond'], $times12, $alignLeft);
		}
		
		$rows = $res['work_env_protocols'];
		if (!empty($rows)) {
			$sect->writeText('1.3.2. Фактори на работната среда и трудовия процес', $times12, $alignLeft);
			$data = array();
			$data[] = array('Показател', '№ и дата на протокола', 'Установени норми', 'Гранични');
			foreach ($rows as $row) {
				unset($row['prot_date']);
				$data[] = array_values($row);
			}
			$colWidts = array(5, 4, 4, 4);
			$colAligns = array('center', 'center', 'center', 'center', 'center');
			fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'small');
		} else {
			$sect->addEmptyParagraph();
		}
		
		$rows = $res['wplace_factors_info'];
		if (!empty($rows)) {
			foreach ($rows as $row) {
				$sect->writeText($row, $times12, $alignLeft);
			}
		}
		
		$sect->writeText('2. Данни от предварителен медицински преглед:' . "\n", $times12, $alignLeft);
		
		if(!empty($res['prchk_date2'])) {
			$checkbox = $sect->addCheckbox();
			$checkbox->setChecked();
			$sect->writeText('2.1. Има налични данни за проведен предварителен преглед.', $times12, $alignLeft);
			$sect->writeText('2.1.1. Kарта за предварителен медицински преглед, издадена от ' . $res['prchk_author'], $times12, $alignLeft);
			
			$rows = $res['prchk_conclusions'];
			if (!empty($rows)) {
				$sect->addEmptyParagraph();
				$sect->writeText('- Заключение на лекаря/лекарите, провели прегледите:', $times12, $alignLeft);
				foreach ($rows as $row) {
					$sect->writeText($row, $times12, $alignLeft);
				}
			}
			
			$rows = $res['prchk_diagnosis'];
			if (!empty($rows)) {
				$sect->addEmptyParagraph();
				$sect->writeText('- Заболявания (диагнози)', $times12, $alignLeft);
				$data = array();
				$data[] = array('МКБ', 'Диагноза', 'Издадена от');
				foreach ($rows as $row) {
					$data[] = array_values($row);
				}
				$colWidts = array(2, 11, 4);
				$colAligns = array('center', 'left', 'left');
				fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'small');
			}
			
			$prchk_date2 = (isset($res['prchk_date2']) && !empty($res['prchk_date2'])) ? $res['prchk_date2'] : '';
			
			$sect->writeText('2.1.2. Заключение на СТМ за пригодността на работещия да изпълнява даден вид дейност въз основа на карта от задължителен предварителен медицински преглед, издадена от ' . $res['prchk_author'] . ' ' . ((!empty($prchk_date2)) ? ' на ' . $prchk_date2 . ' г.' : ''), $times12, $alignLeft);
			
			$data = array();
			$data[] = array('Наименование и адрес на СТМ, изготвила заключението, и дата на изготвянето му', 'Заключение');
			$ary = array();
			array_push($ary, $results['stm_name_short'] . "\n" . $results['stm_address'] . ((!empty($prchk_date2)) ? ' / ' . $prchk_date2 . ' г.' : ''));
			array_push($ary, $res['prchk_stm_conclusion']);
			$data[] = $ary;
			$colWidts = array(8, 9);
			$colAligns = array('left', 'left');
			fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'small');
		} else {
			$checkbox = $sect->addCheckbox();
			$checkbox->setChecked();
			$sect->writeText('2.1. Няма налични данни за проведен предварителен медицински преглед.', $times12, $alignLeft);
			$sect->addEmptyParagraph();
		}
		
		$sect->writeText('3. Данни от извършените периодични медицински прегледи и изследвания:', $times12, $alignLeft);
		$sect->writeText('3.1. Работещият се е явил на периодичен медицински преглед и са проведени определените изследвания.', $times12, $alignLeft);
		
		$rows = $res['prophylactic_cards'];
		if (!empty($rows)) {
			$k = 1;
			foreach ($rows as $row) {
				$sect->writeText('3.' . $k . '.1. Дата на провеждане на прегледа: ' . ((empty($row['checkup_date_h'])) ? '--' : $row['checkup_date_h'] . ' г.'), $times12, $alignLeft);
				$sect->writeText('3.' . $k . '.2. Наименование на лечебното заведение, провело прегледа: '.((empty($row['hospitals'])) ? NO_DATA_AVAILABLE : $row['hospitals']) . '.', $times12, $alignLeft);
				$sect->writeText('3.' . $k . '.3. Вид на медицинските специалисти, извършили прегледите' . $row['doctors'] . '.', $times12, $alignLeft);
				$sect->writeText('3.' . $k . '.4. Вид на извършените функционални и лабораторни изследвания.', $times12, $alignLeft);
				if (isset($row['EKG']) && !empty($row['EKG'])) {
					$sect->writeText('ЕКГ: ' . $row['EKG'], $times12, $alignLeft);
				}
				if (isset($row['x_ray']) && !empty($row['x_ray'])) {
					$sect->writeText('Рентгенография: ' . $row['x_ray'], $times12, $alignLeft);
				}
				if (isset($row['echo_ray']) && !empty($row['echo_ray'])) {
					$sect->writeText('Ехография: ' . $row['echo_ray'], $times12, $alignLeft);
				}
				if (($row['left_eye'] != '' || $row['right_eye'] != '')) {
					$sect->writeText('Зрителна острота', $times12, $alignLeft);
					$sect->writeText('Ляво око: ' . HTMLFormat($row['left_eye']) . ' / ' . HTMLFormat($row['left_eye2']) . ' dp'."\t\t".'Дясно око: ' . HTMLFormat($row['right_eye']) . ' / ' . HTMLFormat($row['right_eye2']) .' dp', $times12, $alignLeft);
				}
				if ((!empty($row['VK']) || !empty($row['FEO1']))) {
					$sect->writeText('Функционално изследване на дишането', $times12, $alignLeft);
					$sect->writeText('ВК: ' . HTMLFormat($row['VK']) . ' ml'."\t\t\t".'ФЕО 1: ' . HTMLFormat($row['FEO1']) . ' ml', $times12, $alignLeft);
				}
				if (!empty($row['tifno'])) {
					$sect->writeText('Показател на Тифно: ' . $row['tifno'], $times12, $alignLeft);
				}
				if (!empty($row['hearing_loss'])) {
					$sect->writeText('Тонална аудиометрия', $times12, $alignLeft);
					$sect->writeText('Загуба на слуха: ' . $row['hearing_loss'], $times12, $alignLeft);
					$sect->writeText('Ляво ухо: ' . $row['left_ear'] . "\t\t".'Дясно ухо: ' . $row['right_ear'], $times12, $alignLeft);
					if(!empty($row['hearing_diagnose'])) {
						$sect->writeText('Диагноза: ' . $row['hearing_diagnose'], $times12, $alignLeft);
					}
				}
				
				//Фамилна обремененост
				$sect->writeText('Фамилна обремененост: ' . ((!empty($row['fweights_descr'])) ? HTMLFormat($row['fweights_descr']) : '--'), $times12, $alignLeft);
				$flds = $row['fweights_list'];
				if (!empty($flds)) {
					$data = array();
					$data[] = array('МКБ', 'Диагноза');
					foreach ($flds as $fld) {
						$data[] = array_values($fld);
					}
					$colWidts = array(2, 15);
					$colAligns = array('center', 'left');
					fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'plain');
				}
				
				$flds = $row['lab_tests'];
				if (!empty($flds)) {
					$sect->writeText('Лабораторни изследвания', $times12, $alignLeft);
					$data = array();
					$data[] = array('Показател', 'Min', 'Max', 'Ниво', '');
					foreach ($flds as $fld) {
						$data[] = array_values($fld);
					}
					$colWidts = array(5, 3, 3, 3, 3);
					$colAligns = array('left', 'center', 'center', 'center', 'center');
					fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'small');
				}
				
				//Анамнеза
				$sect->writeText('Анамнеза: ' . ((!empty($row['anamnesis_descr'])) ? HTMLFormat($row['anamnesis_descr']) : '--'), $times12, $alignLeft);	
				$flds = $row['anamnesis_list'];
				if (!empty($flds)) {
					$data = array();
					$data[] = array('МКБ', 'Диагноза');
					foreach ($flds as $fld) {
						$data[] = array_values($fld);
					}
					$colWidts = array(2, 15);
					$colAligns = array('center', 'left');
					fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'plain');
				}
				
				$flds = $row['diagnosis_list'];
				if (!empty($flds)) {
					$sect->writeText('Заболявания (диагнози)', $times12, $alignLeft);
					$data = array();
					$data[] = array('МКБ', 'Диагноза', 'Новооткрито');
					foreach ($flds as $fld) {
						$data[] = array_values($fld);
					}
					$colWidts = array(2, 12, 3);
					$colAligns = array('center', 'left', 'center');
					fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'small');
				}
				
				$sect->writeText('3.' . $k . '.5. Заключение на лекаря/лекарите, провели прегледите:', $times12, $alignLeft);
				
				$flds = $row['conclusion_list'];
				if (!empty($flds)) {
					foreach ($flds as $fld) {
						$sect->writeText($fld, $times12, $alignLeft);
					}
				}
				
				$sect->writeText('3.' . $k . '.6. Заключение на службата по трудова медицина за пригодността на работещия да изпълнява даден вид дейност въз основа на задължителния периодичен медицински преглед, проведен' . ((!empty($row['hospitals'])) ? ' от/в ' . $row['hospitals'] : '') . ' на ' . ((empty($row['checkup_date_h'])) ? '--' : $row['checkup_date_h'] . ' г.'), $times12, $alignLeft);
				
				$data = array();
				$data[] = array('Наименование и адрес на СТМ, изготвила заключението, и дата на изготвянето му', 'Заключение');
				$ary = array();
				array_push($ary, $results['stm_name_short'] . "\n" . $results['stm_address'] . ((!empty($row['stm_date2'])) ? ' / ' . $row['stm_date2'] . ' г.' : ''));
				array_push($ary, $row['stm_conclusion']);
				$data[] = $ary;
				$colWidts = array(8, 9);
				$colAligns = array('left', 'left');
				fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'small');
				
				$k++;
			}
		}
		
		$sect->writeText('<b>V. Данни за посещенията на работещия в службата по трудова медицина по негова инициатива</b>', $times12, $alignLeft);

		$sect->addEmptyParagraph();
		
		$sect->writeText('1. Извършено посещение на работещия в '. $results['stm_name_short']  .'.', $times12, $alignLeft);
		$sect->writeText('2. Дата на извършеното посещение:', $times12, $alignLeft);
		$sect->writeText('3. Кратко описание на целта на посещението.', $times12, $alignLeft);
		$sect->writeText('4. Описание на предприетите мерки от службата по трудова медицина във връзка с поставените въпроси, проблеми и други, когато е необходимо.', $times12, $alignLeft);
		$sect->writeText('5. Други.', $times12, $alignLeft);
		
		$sect->addEmptyParagraph();
		$sect->addEmptyParagraph();
		$sect->addEmptyParagraph();
	}//workers foreach loop end
}

require('phprtflite/rtfend.php');

