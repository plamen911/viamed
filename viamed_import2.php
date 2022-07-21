<?php
set_time_limit(1200);
require ('sqlitedb.php');
require ('functions.php');
$dbInst = new SqliteDB();

include 'class_mdb.php';

//$accessDB = 'test/viamed/Data_ASYA/STMData.stm';
$accessDB = 'test/viamed/2012-05-23/Data_ASYA/STMData.stm';
//$accessDB = 'test/viamed/2012-05-23/Data_MILENA/STMData.stm';
//$accessDB = 'test/viamed/2012-05-23/Data_KRISI/STMData.stm';
//$accessDB = 'test/viamed/2012-05-23/Data_SISI/STMData.stm';
//$accessDB = 'test/viamed/2012-05-23/Data_DEA/STMData.stm';

/*
БАЗА АСЯ

$fIDs = array(17, 25, 27, 28, 30, 29, 6);
Електрон консорциум		FirmID: 17 / firm_id: --
Мират Груп				FirmID: 25 / firm_id: --
Ветеринарен център Свети Наум
Авто Плами				FirmID: 27 / firm_id: --
Декострой				FirmID: 28 / firm_id: --
Енергомонтаж инженеринг	FirmID: 30 / firm_id: --
Биг Стар				FirmID: 29 / firm_id: --
Вайбау					FirmID: 6  / firm_id: 67, 78

БАЗА МИЛЕНА

$fIDs = array(5, 22);
Пазари Изток			FirmID: 5  / firm_id: 5
Дорма България			FirmID: 22 / firm_id: 22

БАЗА ДЕЯ

$fIDs = array(24, 25, 26, 38);
Крафт Фуудс България	FirmID: 24, 25, 26 / firm_id: 115, 114, 113
Валентин				FirmID: 38 / firm_id: 127
*/

/*foreach (array(67, 78, 5, 22, 115, 114, 113, 127) as $firm_id) {
	$dbInst->removeFirm($firm_id);
}*/

$doctors_arr = array();
$mkb_ids_arr = array();
$users_arr = array();

// set users
$users = get_records("SELECT * FROM Users WHERE UserName NOT LIKE 'Admin'");
if(!empty($users)) {
	foreach ($users as $user) {
		$user_name = $user['UserName'];
		if(!$dbInst->fnCountRow('users', "user_name = '$user_name'")) {
			$fld['user_name'] = $user_name;
			$fld['user_pass'] = $user['UserPassword'];
			$fld['fname'] = $user['EmployeeNames'];
			$fld['lname'] = '';
			if(preg_match('/(\w+)\s+(\w+)/', $fld['fname'], $matches)) {
				$fld['fname'] = $matches[1];
				$fld['lname'] = $matches[2];
			}
			$fld['user_level'] = 1;
			$fld['email'] = '';
			$fld['date_created'] = $fld['date_modified'] = date('Y-m-d H:i:s');
			$fld['date_last_login'] = '';
			$fld['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];
			$fld['hdd'] = '';

			$sql = build_sql('users', $fld);
			$user_id = $dbInst->query($sql);
		}
	}
}

$users = $dbInst->query("SELECT * FROM `users`");
if(!empty($users)) {
	foreach ($users as $user) {
		$users_arr[$user['user_name']] = $user['user_id'];
	}
}

// Reset data
/*$dbInst->query("DELETE FROM `firms` WHERE `firm_id` > 1167");
$dbInst->query("DELETE FROM `workers` WHERE `worker_id` > 8340");
$dbInst->query("DELETE FROM `firm_struct_map` WHERE `map_id` > 3146");
$dbInst->query("DELETE FROM `wplace_factors_map` WHERE `map_id` > 182");
$dbInst->query("DELETE FROM `work_places` WHERE `wplace_id` > 2818");
$dbInst->query("DELETE FROM `firm_positions` WHERE `position_id` > 2823");
$dbInst->query("DELETE FROM `subdivisions` WHERE `subdivision_id` > 1413");
$dbInst->query("DELETE FROM `patient_charts` WHERE `chart_id` > 6059");
$dbInst->query("DELETE FROM `telks` WHERE `telk_id` > 20");
$dbInst->query("DELETE FROM `medical_checkups` WHERE `checkup_id` > 8567");
$dbInst->query("DELETE FROM `medical_checkups_doctors2` WHERE `checkup_id` > 8567");
$dbInst->query("DELETE FROM `lab_checkups` WHERE `lab_checkup_id` > 12805");
$dbInst->query("DELETE FROM `family_diseases` WHERE `family_diseases` > 6914");
*/
/*$dbInst->query("DELETE FROM `firms`");
$dbInst->query("DELETE FROM `workers`");
$dbInst->query("DELETE FROM `firm_struct_map`");
$dbInst->query("DELETE FROM `wplace_factors_map`");
$dbInst->query("DELETE FROM `work_places`");
$dbInst->query("DELETE FROM `firm_positions`");
$dbInst->query("DELETE FROM `subdivisions`");
$dbInst->query("DELETE FROM `patient_charts`");
$dbInst->query("DELETE FROM `telks`");
$dbInst->query("DELETE FROM `medical_checkups`");
$dbInst->query("DELETE FROM `medical_checkups_doctors2`");
$dbInst->query("DELETE FROM `lab_checkups`");
$dbInst->query("DELETE FROM `family_diseases`");
$dbInst->query("DELETE FROM `medical_precheckups`");
$dbInst->query("DELETE FROM `medical_precheckups_doctors2`");
$dbInst->query("DELETE FROM `prchk_diagnosis`");
*/
$i = 0;
$d = new ParseBGDate();
$firms = get_records("SELECT * FROM Firms WHERE FirmID = 19");
//$firms = get_records("SELECT * FROM Firms WHERE FirmID > 20 AND FirmID <= 40");
//$firms = get_records("SELECT * FROM Firms WHERE FirmID > 40 AND FirmID <= 60");
//$firms = get_records("SELECT * FROM Firms WHERE FirmID > 60 AND FirmID <= 80");
//$firms = get_records("SELECT * FROM Firms WHERE FirmID > 80 AND FirmID <= 1000");

if(!empty($firms)) {
	foreach ($firms as $firm) {
		$fld = array();
		$FirmID = $firm['FirmID'];
		
		$Firmname = mb_strtoupper($firm['FirmName'], 'windows-1251');
		$Firmname = str_replace('"', '', $Firmname);
		$fld['name'] = $Firmname;
		
		foreach ($firm as $key => $val) {
			if('-' == $val) { $firm[$key] = ''; }
		}
		
		$fld['location_id'] = 0;
		$fld['community_id'] = 0;
		$fld['province_id'] = 0;
		$fld['address'] = $firm['FirmAdres'];
		if(preg_match('/София/', $fld['address'])) {
			$fld['location_id'] = 442;
			$fld['community_id'] = 26;
			$fld['province_id'] = 23;
		}
		$fld['email'] = ('-' == $firm['FirmEmail']) ? '' : $firm['FirmEmail'];
		$fld['notes'] = '';
		$fld['phone1'] = $firm['FirmTel'];
		$fld['phone2'] = '';
		$fld['fax'] = $firm['FirmFax'];
		$fld['date_added'] = $firm['CurrData'];
		if ($d->Parse($fld['date_added'])) { $fld['date_added'] = $d->year . '-' . $d->month . '-' . $d->day . ' 00:00:00'; }
		else { $fld['date_added'] = date('Y-m-d H:i:s'); }
		$fld['date_modified'] = date('Y-m-d H:i:s');
		$fld['modified_by'] = (isset($users_arr[$firm['CurrUser']])) ? $users_arr[$firm['CurrUser']] : 1;
		$fld['contract_num'] = $firm['FirmDogovorNo'];
		$fld['contract_begin'] = $firm['FirmDogovorData'];
		$yy = $mm = $dd = 0;
		if ($d->Parse($fld['contract_begin'])) {
			$yy = $d->year;
			$mm = $d->month;
			$dd = $d->day;
			$fld['contract_begin'] = $d->year . '-' . $d->month . '-' . $d->day . ' 00:00:00';
		} else {
			$fld['contract_begin'] = '';
		}
		$FirmDogovorSrok = $firm['FirmDogovorSrok'];
		if(!empty($yy) && !empty($FirmDogovorSrok)) {
			$fld['contract_end'] = date('Y-m-d H:i:s', mktime(0, 0, 0, $mm + intval($FirmDogovorSrok), $dd, $yy));
		} else {
			$fld['contract_end'] = '';
		}
		$fld['firm_folder'] = '';
		$fld['bulstat'] = '';

		$fld['FirmMOL'] = $firm['FirmMOL'];
		$fld['FirmUpravitel'] = $firm['FirmUpravitel'];
		$fld['FirmLice'] = $firm['FirmLice'];
		$fld['FirmLiceTel'] = $firm['FirmLiceTel'];
		$fld['FirmLiceEmail'] = $firm['FirmLiceEmail'];

		$sql = build_sql('firms', $fld);
		$firm_id = $dbInst->query($sql);

		// set workers
		$workers = get_records("SELECT * FROM Employees WHERE EmployeeFirmID = $FirmID");
		if(!empty($workers)) {
			foreach ($workers as $worker) {
				$fld = array();
				$EmployeeID = $worker['EmployeeID'];
				$fld['firm_id'] = $firm_id;
				$fld['is_active'] = 1;
				$fld['fname'] = $worker['EmployeeName'];
				$fld['sname'] = '';
				$fld['lname'] = '';
				if(preg_match('/^(\w+)\s+(\w+)$/', $fld['fname'], $matches)) {
					$fld['fname'] = $matches[1];
					$fld['sname'] = '';
					$fld['lname'] = $matches[2];
				} elseif(preg_match('/^(\w+)\s+(\w+)\s+(\w+)$/', $fld['fname'], $matches)) {
					$fld['fname'] = $matches[1];
					$fld['sname'] = $matches[2];
					$fld['lname'] = $matches[3];
				}
				$egn = $worker['EmployeeEGN'];
				if(preg_match('/^[0-9]{10}$/', $egn)) {
					$y = substr($egn, 0, 2);
					$m = substr($egn, 2, 2);
					$d = substr($egn, 4, 2);
					$sex = substr($egn, 8, 1);
					$fld['egn'] = $egn;
					$fld['sex'] = (($sex%2) ? 'Ж' : 'М');
					$fld['birth_date'] = date('Y-m-d', mktime(0, 0, 0, $m, $d, (1900 + $y))).' 00:00:00';
				} else {
					$fld['egn'] = $egn;
					$fld['sex'] = '';
					$fld['birth_date'] = '';
				}
				$worker_sex = $fld['sex'];
				$fld['location_id'] = 0;
				$fld['address'] = $worker['EmployeeAddress'];
				if(preg_match('/София/', $fld['address'])) {
					$fld['location_id'] = 442;
				}
				$fld['phone1'] = $worker['EmployeeTel'];
				$fld['phone2'] = '';
				
				$worker['EmployeeOtdelID'] = intval($worker['EmployeeOtdelID']);
				$worker['EmployeeNKDIID'] = intval($worker['EmployeeNKDIID']);
				$subdivision_name = get_one("SELECT OtdelName FROM FirmsOtdels WHERE FirmID = $FirmID AND OtdelID = $worker[EmployeeOtdelID]");
				$wplace_name = $worker['EmployeeRabMiasto'];
				$position_name = $dbInst->GiveValue('NKDI', 'NKDI', "WHERE NKDIID = $worker[EmployeeNKDIID]", 0);
				if(empty($position_name)) {
					$position_name = $worker['EmployeeNKDIID'];
				}
				$subdivision_name = iconv("CP1251", "UTF-8", $subdivision_name['OtdelName']);
				$wplace_name = iconv("CP1251", "UTF-8", $wplace_name);
				
				$ary = array();
				if(!empty($worker['EmployeeRabPoza']) && '-' != $worker['EmployeeRabPoza']) {
					$ary[] = 'Работна поза: '.$worker['EmployeeRabPoza'];
				}
				if(!empty($worker['EmployeeTevesti']) && '-' != $worker['EmployeeTevesti']) {
					$ary[] = 'Ръчна работа с тежести: '.$worker['EmployeeTevesti'];
				}
				if(!empty($worker['EmployeeMonotonna']) && '-' != $worker['EmployeeMonotonna']) {
					$ary[] = 'Двигателна монотонна работа: '.$worker['EmployeeMonotonna'];
				}
				if(!empty($worker['EmployeePsihic']) && '-' != $worker['EmployeePsihic']) {
					$ary[] = 'Нервно-психично напрежение: '.$worker['EmployeePsihic'];
				}
				if(!empty($worker['EmployeeRevim']) && '-' != $worker['EmployeeRevim']) {
					$ary[] = 'Режим на работа: '.$worker['EmployeeRevim'];
				}
				if(!empty($worker['EmployeeProdalvitelnost']) && '-' != $worker['EmployeeProdalvitelnost']) {
					$ary[] = 'Продължителност на работното време: '.$worker['EmployeeProdalvitelnost'];
				}
				if(!empty($worker['EmployeeOther']) && '-' != $worker['EmployeeOther']) {
					$ary[] = 'Други: '.$worker['EmployeeOther'];
				}
				$wplace_workcond = (!empty($ary)) ? iconv("CP1251", "UTF-8", implode('; ', $ary)) : '';
				$position_workcond = (!empty($worker['EmployeeOpisanie']) && '-' != $worker['EmployeeOpisanie']) ? iconv("CP1251", "UTF-8", $worker['EmployeeOpisanie']) : '';
				
				$factors = array();
				$factors['fact_work_pose'] = iconv("CP1251", "UTF-8", $worker['EmployeeRabPoza']);
				$factors['fact_manual_weights'] = iconv("CP1251", "UTF-8", $worker['EmployeeTevesti']);
				$factors['fact_monotony'] = iconv("CP1251", "UTF-8", $worker['EmployeeMonotonna']);
				$factors['fact_nervous'] = iconv("CP1251", "UTF-8", $worker['EmployeePsihic']);
				$factors['fact_work_regime'] = iconv("CP1251", "UTF-8", $worker['EmployeeRevim']);
				$factors['fact_work_hours'] = iconv("CP1251", "UTF-8", $worker['EmployeeProdalvitelnost']);
				$factors['fact_other'] = iconv("CP1251", "UTF-8", $worker['EmployeeOther']);
				
				$fld['map_id'] = regenerateMapId($firm_id, $subdivision_name, $wplace_name, $position_name, $wplace_workcond, $position_workcond, $factors);

				$fld['date_curr_position_start'] = $worker['EmployeeDataIn'];
				
				if(!is_object($d)) {
					$d = new ParseBGDate();
				}
				if ($d->Parse($fld['date_curr_position_start'])) { $fld['date_curr_position_start'] = $d->year . '-' . $d->month . '-' . $d->day . ' 00:00:00'; }
				else { $fld['date_curr_position_start'] = date('Y-m-d H:i:s'); }
				$fld['date_career_start'] = '';
				$fld['date_retired'] = $worker['EmployeeDataOut'];
				if ($d->Parse($fld['date_retired'])) { $fld['date_retired'] = $d->year . '-' . $d->month . '-' . $d->day . ' 00:00:00'; }
				else { $fld['date_retired'] = ''; }

				// Set GP
				$EmployeeGP = $dbInst->checkStr(iconv("CP1251", "UTF-8", $worker['EmployeeGP']));
				$doctor_id = (isset($doctors_arr[$EmployeeGP])) ? $doctors_arr[$EmployeeGP] : $dbInst->GiveValue('doctor_id', 'doctors', "WHERE doctor_name = '$EmployeeGP'", 0);
				if(empty($doctor_id)) {
					if(!empty($EmployeeGP) && '-' != $EmployeeGP) {
						$doctor_name = $EmployeeGP;
						$phone1 = $dbInst->checkStr(iconv("CP1251", "UTF-8", $worker['EmployeeGPTel']));
						$sql = "INSERT INTO doctors (doctor_name, address, phone1, phone2) VALUES ('$doctor_name', '', '$phone1', '')";
						$doctor_id = $dbInst->query($sql);
						$doctors_arr[$EmployeeGP] = $doctor_id;
					}
				}
				$fld['doctor_id'] = $doctor_id;
				$fld['date_added'] = $worker['CurrData'];
				if ($d->Parse($fld['date_added'])) { $fld['date_added'] = $d->year . '-' . $d->month . '-' . $d->day . ' 00:00:00'; }
				else { $fld['date_added'] = date('Y-m-d H:i:s'); }
				$fld['date_modified'] = date('Y-m-d H:i:s');
				$fld['modified_by'] = (isset($users_arr[$worker['CurrUser']])) ? $users_arr[$worker['CurrUser']] : 1;
				$fld['notes'] = $worker['EmployeeNotes'];

				$sql = build_sql('workers', $fld);
				$worker_id = $dbInst->query($sql);
				
				// Set patient charts
				$patient_charts = get_records("SELECT * FROM EmployeesBolnichni WHERE EmployeeID = $EmployeeID");
				if(!empty($patient_charts)) {
					if(!is_object($d)) {
						$d = new ParseBGDate();
					}
					foreach ($patient_charts as $patient_chart) {
						$fld = array();
						$fld['firm_id'] = $firm_id;
						$fld['worker_id'] = $worker_id;
						$fld['chart_num'] = '';
						$fld['hospital_date_from'] = $patient_chart['BolnichenOt'];
						if ($d->Parse($fld['hospital_date_from'])) { $fld['hospital_date_from'] = $d->year . '-' . $d->month . '-' . $d->day . ' 00:00:00'; }
						else { $fld['hospital_date_from'] = ''; }
						$fld['hospital_date_to'] = $patient_chart['BolnichenDo'];
						if ($d->Parse($fld['hospital_date_to'])) { $fld['hospital_date_to'] = $d->year . '-' . $d->month . '-' . $d->day . ' 00:00:00'; }
						else { $fld['hospital_date_to'] = ''; }
						$fld['days_off'] = intval($patient_chart['BolnichenDni']);
						if(!isset($mkb_ids_arr[$patient_chart['BolnichenDiagnoseID']])) {
							$mkb_ids_arr[$patient_chart['BolnichenDiagnoseID']] = $dbInst->GiveValue('DiagnoseKod', 'Diagnoses', "WHERE DiagnoseID = $patient_chart[BolnichenDiagnoseID]", 0);
						}
						$fld['mkb_id'] = $mkb_ids_arr[$patient_chart['BolnichenDiagnoseID']];
						$fld['medical_types'] = (!empty($patient_chart['BolnichenKind'])) ? serialize(array($patient_chart['BolnichenKind'])) : '';
						$fld['reason_id'] = sprintf("%02s", $patient_chart['BolnichenKind']);
						$fld['chart_desc'] = '';
						$fld['date_added'] = $fld['date_modified'] = date('Y-m-d H:i:s');
						$fld['published_date'] = '';
						
						$sql = build_sql('patient_charts', $fld);
						$chart_id = $dbInst->query($sql);
					}
				}
				
				// Set telks
				$telks = get_records("SELECT * FROM EmployeesTelk WHERE EmployeeID = $EmployeeID");
				if(!empty($telks)) {
					if(!is_object($d)) {
						$d = new ParseBGDate();
					}
					foreach ($telks as $telk) {
						$fld = array();
						$fld['firm_id'] = $firm_id;
						$fld['worker_id'] = $worker_id;
						$fld['telk_num'] = $telk['TelkNo'];
						$fld['telk_date_from'] = $telk['TelkData'];
						if ($d->Parse($fld['telk_date_from'])) { $fld['telk_date_from'] = $d->year . '-' . $d->month . '-' . $d->day . ' 00:00:00'; }
						else { $fld['telk_date_from'] = ''; }
						$fld['telk_date_to'] = $telk['TelkSrok'];
						if ($d->Parse($fld['telk_date_to'])) { $fld['telk_date_to'] = $d->year . '-' . $d->month . '-' . $d->day . ' 00:00:00'; }
						else { $fld['telk_date_to'] = ''; }
						
						$fld['telk_duration'] = '';
						if('пожизнено' == $telk['TelkSrok']) {
							$fld['telk_duration'] = 'пожизнен';
						} elseif (!empty($fld['telk_date_from']) && !empty($fld['telk_date_to'])) {
							$year_from = intval(substr($fld['telk_date_from'], 0, 4));
							$year_to = intval(substr($fld['telk_date_to'], 0, 4));
							switch ($year_to - $year_from) {
								case 1: $fld['telk_duration'] = '1 г.'; break;
								case 2: $fld['telk_duration'] = '2 г.'; break;
								case 3: $fld['telk_duration'] = '3 г.'; break;
							}
						}
						
						if(!isset($mkb_ids_arr[$telk['TelkDiagnoseID']])) {
							$mkb_ids_arr[$telk['TelkDiagnoseID']] = $dbInst->GiveValue('DiagnoseKod', 'Diagnoses', "WHERE DiagnoseID = $telk[TelkDiagnoseID]", 0);
						}
						$fld['mkb_id_1'] = $mkb_ids_arr[$telk['TelkDiagnoseID']];
						$fld['mkb_id_2'] = '';
						$fld['mkb_id_3'] = '';
						$fld['mkb_id_4'] = '';
						
						$fld['percent_inv'] = floatval($telk['TelkProc']);
						$fld['bad_work_env'] = $telk['TelkProtivopokaz'];
						$fld['date_added'] = $fld['date_modified'] = date('Y-m-d H:i:s');
						$fld['first_inv_date'] = $fld['telk_date_from'];
						
						$sql = build_sql('telks', $fld);
						$chart_id = $dbInst->query($sql);
					}
				}
				
				// Set preliminary medical checkups
				$medical_checkups = get_records("SELECT * FROM Pregleds WHERE EmployeeID = $EmployeeID AND PregledKindID = 0");
				if(!empty($medical_checkups)) {
					if(!is_object($d)) {
						$d = new ParseBGDate();
					}
					foreach ($medical_checkups as $medical_checkup) {
						$fld = array();
						$PregledID = $medical_checkup['PregledID'];
						$fld['firm_id'] = $firm_id;
						$fld['worker_id'] = $worker_id;
						$fld['PregledNo'] = $medical_checkup['PregledNo'];
						$fld['prchk_date'] = $medical_checkup['PregledData'];
						if ($d->Parse($fld['prchk_date'])) { $fld['prchk_date'] = $d->year . '-' . $d->month . '-' . $d->day . ' 00:00:00'; }
						else { $fld['prchk_date'] = ''; }						
						$fld['prchk_author'] = $medical_checkup['MedZavedenie'];
						
						/*
						stm_conclusion
						1 - може
						2 - може при сл. условия
						0 - не може
						3 - не може да се прецени пригодността на работещия
						*/
						
						$fld['prchk_conclusion'] = 1;
						$fld['prchk_conditions'] = '';
						if(preg_match('/^при следните условия/i', $medical_checkup['LastNote'])) {
							$fld['prchk_conclusion'] = 2;
							$fld['prchk_conditions'] = str_replace('при следните условия', '', trim($medical_checkup['LastNote']));
						} elseif (preg_match('/^Не може да се прецени/i', $medical_checkup['LastNote'])) {
							$fld['prchk_conclusion'] = 3;
						}
						$fld['prchk_stm_date'] = '';
						$fld['prchk_anamnesis'] = $medical_checkup['Anamnese'];
						$fld['prchk_data'] = '';
						$fld['date_modified'] = $medical_checkup['CurrData'];
						if ($d->Parse($fld['date_modified'])) { $fld['date_modified'] = $d->year . '-' . $d->month . '-' . $d->day . ' 00:00:00'; }
						else { $fld['date_modified'] = date('Y-m-d H:i:s'); }
						$fld['date_added'] = $fld['date_modified'];
												
						$sql = build_sql('medical_precheckups', $fld);
						$precheckup_id = $dbInst->query($sql);
						
						// Set specialist conclusions
						$medical_checkups_doctors2 = get_records("SELECT * FROM PregledsSpecialists WHERE PregledID = $PregledID");
						if(!empty($medical_checkups_doctors2)) {
							foreach ($medical_checkups_doctors2 as $line) {
								$fld = array();
								$fld['precheckup_id'] = $precheckup_id;
								$fld['SpecialistID'] = $line['SpecialistID'];
								$fld['conclusion'] = $line['SpecIzsledvania'];
								$sql = build_sql('medical_precheckups_doctors2', $fld, 'REPLACE');
								$dbInst->query($sql);
							}
						}
						
						// Set diagnosis
						$family_diseases = get_records("SELECT * FROM PregledsDiagnoses WHERE PregledID = $PregledID AND DiagnoseID > 0");
						if(!empty($family_diseases)) {
							foreach ($family_diseases as $line) {
								$fld = array();
								$fld['worker_id'] = $worker_id;
								$fld['precheckup_id'] = $precheckup_id;
								$fld['mkb_id'] = '';
								$fld['diagnosis'] = '';
								$fld['published_by'] = 0;
								
								$DiagnoseID = $line['DiagnoseID'];
								if(!isset($mkb_ids_arr[$DiagnoseID])) {
									$mkb_ids_arr[$DiagnoseID] = $dbInst->GiveValue('DiagnoseKod', 'Diagnoses', "WHERE DiagnoseID = $DiagnoseID", 0);
								}
								$fld['mkb_id'] = $mkb_ids_arr[$DiagnoseID];
								$sql = build_sql('prchk_diagnosis', $fld);
								$dbInst->query($sql);
							}
						}
					}
				}
				
				
				// Set medical checkups
				$medical_checkups = get_records("SELECT * FROM Pregleds WHERE EmployeeID = $EmployeeID AND PregledKindID = 1");
				if(!empty($medical_checkups)) {
					if(!is_object($d)) {
						$d = new ParseBGDate();
					}
					foreach ($medical_checkups as $medical_checkup) {
						$fld = array();
						$PregledID = $medical_checkup['PregledID'];
						$fld['firm_id'] = $firm_id;
						$fld['worker_id'] = $worker_id;
						$fld['PregledNo'] = $medical_checkup['PregledNo'];
						$fld['year_to_be_done'] = '';
						$fld['checkup_date'] = $medical_checkup['PregledData'];
						if ($d->Parse($fld['checkup_date'])) { $fld['checkup_date'] = $d->year . '-' . $d->month . '-' . $d->day . ' 00:00:00'; }
						else { $fld['checkup_date'] = ''; }						
						$fld['hospital'] = $medical_checkup['MedZavedenie'];
						
						//
						$checkups_arr = array();
						$checkups = get_records("SELECT * FROM PregledsIzsledvania WHERE PregledID = $PregledID");
						if(!empty($checkups)) {
							foreach ($checkups as $checkup) {
								$IzsledvaneID = $checkup['IzsledvaneID'];
								$Resultat = $checkup['Resultat'];
								$checkups_arr[$IzsledvaneID] = $Resultat;
							}
						}
						
						$fld['worker_height'] = (isset($checkups_arr[1])) ? $checkups_arr[1] : '';
						$fld['worker_weight'] = (isset($checkups_arr[2])) ? $checkups_arr[2] : '';
						
						$rr_syst = '';
						$rr_diast = '';
						if(isset($checkups_arr[3])) {
							if(preg_match('/^(\d+)\/(\d+)$/', trim($checkups_arr[3]), $matches)) {
								$rr_syst = $matches[1];
								$rr_diast = $matches[2];
							}
						}
						$fld['rr_syst'] = $rr_syst;
						$fld['rr_diast'] = $rr_diast;
						
						// Set bad properties
						$bad_properties_arr = array();
						$bad_properties = get_records("SELECT * FROM PregledsBadProperty WHERE PregledID = $PregledID");
						if(!empty($bad_properties)) {
							foreach ($bad_properties as $bad_property) {
								$bad_properties_arr[] = $bad_property['BadPropertyName'];
							}
						}
						
						$fld['hours_activity'] = '';
						$fld['low_activity'] = (in_array('Намалена двигателна активност', $bad_properties_arr)) ? '1' : '0';
						$fld['home_stress'] = '0';
						$fld['work_stress'] = (in_array('Психо-емоционално напрежение', $bad_properties_arr)) ? '1' : '0';
						$fld['social_stress'] = '0';
						$fld['video_display'] = '0';
						$fld['smoking'] = (in_array('Тютюнопушене', $bad_properties_arr)) ? '1' : '0';
						$fld['drinking'] = '0';
						$fld['fats'] = 
						$fld['diet'] = '0';
						$fld['left_eye'] = '';
						$fld['left_eye2'] = '';
						$fld['right_eye'] = '';
						$fld['right_eye2'] = '';
						$fld['VK'] = '';
						$fld['FEO1'] = '';
						$fld['tifno'] = '';
						$fld['hearing_loss'] = '';
						$fld['hearing_diagnose'] = '';
						$fld['left_ear'] = '';
						$fld['right_ear'] = '';
						$fld['EKG'] = (isset($checkups_arr[4])) ? $checkups_arr[4] : '';
						$fld['x_ray'] = '';
						$fld['echo_ray'] = (isset($checkups_arr[27])) ? $checkups_arr[27] : '';
						$fld['desc_GP'] = '';
						$fld['desc_pathologist'] = '';
						$fld['desc_neurologist'] = '';
						$fld['desc_UNG'] = '';
						$fld['desc_ophthalmologist'] = '';
						$fld['desc_dermatologist'] = '';
						$fld['desc_surgeon'] = '';
						$fld['conclusion'] = '';
						$fld['notes'] = '';
						
						/*
						stm_conclusion
						1 - може
						2 - може при сл. условия
						0 - не може
						3 - не може да се прецени пригодността на работещия
						*/
						
						$fld['stm_conclusion'] = 1;
						$fld['stm_conditions'] = '';
						if(preg_match('/^при следните условия/i', $medical_checkup['LastNote'])) {
							$fld['stm_conclusion'] = 2;
							$fld['stm_conditions'] = str_replace('при следните условия', '', trim($medical_checkup['LastNote']));
						} elseif (preg_match('/^Не може да се прецени/i', $medical_checkup['LastNote'])) {
							$fld['stm_conclusion'] = 3;
						}
						$fld['stm_date'] = '';
						$fld['anamnesis_descr'] = $medical_checkup['Anamnese'];
						$fld['date_modified'] = $medical_checkup['CurrData'];
						if ($d->Parse($fld['date_modified'])) { $fld['date_modified'] = $d->year . '-' . $d->month . '-' . $d->day . ' 00:00:00'; }
						else { $fld['date_modified'] = date('Y-m-d H:i:s'); }
						$fld['date_added'] = $fld['date_modified'];						
						// Set family weight description
						$fweights_descr = '';
						$fweights = get_records("SELECT * FROM PregledsFamily WHERE PregledID = $PregledID");
						if(!empty($fweights)) {
							$ary = array();
							foreach ($fweights as $line) {
								$ary[] = $line['FamilyName'];
							}
							$fweights_descr = implode('; ', $ary);
						}
						$fld['fweights_descr'] = $fweights_descr;
						
						$sql = build_sql('medical_checkups', $fld);
						$checkup_id = $dbInst->query($sql);
						
						// Set specialist conclusions
						$medical_checkups_doctors2 = get_records("SELECT * FROM PregledsSpecialists WHERE PregledID = $PregledID");
						if(!empty($medical_checkups_doctors2)) {
							foreach ($medical_checkups_doctors2 as $line) {
								$fld = array();
								$fld['checkup_id'] = $checkup_id;
								$fld['SpecialistID'] = $line['SpecialistID'];
								$fld['conclusion'] = $line['SpecIzsledvania'];
								$sql = build_sql('medical_checkups_doctors2', $fld, 'REPLACE');
								$dbInst->query($sql);
							}
						}
						
						// Set laboratory checkups
						/*
						22 - Урина - ph					! 100
						7 - Урина-албумин				! 101
						19 - Урина - кръв				! 102
						21 - Урина - специфично тегло	! 103
						16 - Кръв - алфа холестерол		! 104
						24 - ПКК /Пълна кръвна картина/
						25 - Урина
						26 - СУЕ /Скорост на утаяване на еритроцитите/	! 105 /Кръв - мъж/ 106 /Кръв - жена/
						11 - Кръв - хематокрит			! 4 /Кръв - мъж/ 3 /Кръв - жена/
						6 - Урина- кет.тела				| 19
						5 - Урина-захар 				| 16???
						8 - Урина-уробилиноген 			| 14
						9 - Кръв-хемоглобин				| 2 /Кръв - мъж/ 1 /Кръв - жена/
						10 - Кръв - еритроцити			| 10 /Кръв - мъж/ 9 /Кръв - жена/
						12 - Кръв - левкоцити			| 12
						14 - Кръв - тромбоцити			| 18
						15 - Кръв - холестерол			| 21
						17 - Кръв - триглицериди		| 22
						18 - Урина - билирубин			| 15
						20 - Урина - седимент			| 31
						23 - Кръв - глюкоза 			| 16
						*/
						$ary = array(22, 7, 19, 21, 16, 26, 11, 6, 5, 8, 9, 10, 12, 14, 15, 17, 18, 20, 23);
						$fld = array();
						$fld['firm_id'] = $firm_id;
						$fld['worker_id'] = $worker_id;
						$fld['checkup_id'] = $checkup_id;
						$fld['checkup_type'] = '';
						foreach ($ary as $IzsledvaneID) {
							$hit = false;
							if(isset($checkups_arr[$IzsledvaneID])) {
								switch ($IzsledvaneID) {
									case 22: $fld['indicator_id'] = 100; $hit = true; break;
									case 7: $fld['indicator_id'] = 101; $hit = true; break;
									case 19: $fld['indicator_id'] = 102; $hit = true; break;
									case 21: $fld['indicator_id'] = 103; $hit = true; break;
									case 16: $fld['indicator_id'] = 104; $hit = true; break;
									case 26: $fld['indicator_id'] = ('М' == $worker_sex) ? 105 : 106; $hit = true; break;
									case 11: $fld['indicator_id'] = ('М' == $worker_sex) ? 4 : 3; $hit = true; break;
									case 6: $fld['indicator_id'] = 19; $hit = true; break;
									case 5: $fld['indicator_id'] = 16; $hit = true; break;
									case 8: $fld['indicator_id'] = 14; $hit = true; break;
									case 9: $fld['indicator_id'] = ('М' == $worker_sex) ? 2 : 1; $hit = true; break;
									case 10: $fld['indicator_id'] = ('М' == $worker_sex) ? 10 : 9; $hit = true; break;
									case 12: $fld['indicator_id'] = 12; $hit = true; break;
									case 14: $fld['indicator_id'] = 18; $hit = true; break;
									case 15: $fld['indicator_id'] = 21; $hit = true; break;
									case 17: $fld['indicator_id'] = 22; $hit = true; break;
									case 18: $fld['indicator_id'] = 15; $hit = true; break;
									case 20: $fld['indicator_id'] = 31; $hit = true; break;
									case 23: $fld['indicator_id'] = 16; $hit = true; break;
								}
							}
							if($hit) {
								$fld['checkup_level'] = floatval($checkups_arr[$IzsledvaneID]);
								$sql = build_sql('lab_checkups', $fld);
								$dbInst->query($sql);
							}							
						}
						
						// Set diagnosis
						$family_diseases = get_records("SELECT * FROM PregledsDiagnoses WHERE PregledID = $PregledID AND DiagnoseID > 0");
						if(!empty($family_diseases)) {
							foreach ($family_diseases as $line) {
								$fld = array();
								$fld['firm_id'] = $firm_id;
								$fld['worker_id'] = $worker_id;
								$fld['checkup_id'] = $checkup_id;
								$fld['mkb_id'] = '';
								$fld['diagnosis'] = '';
								$fld['is_new'] = '0';
								
								$DiagnoseID = $line['DiagnoseID'];
								if(!isset($mkb_ids_arr[$DiagnoseID])) {
									$mkb_ids_arr[$DiagnoseID] = $dbInst->GiveValue('DiagnoseKod', 'Diagnoses', "WHERE DiagnoseID = $DiagnoseID", 0);
								}
								$fld['mkb_id'] = $mkb_ids_arr[$DiagnoseID];
								$sql = build_sql('family_diseases', $fld);
								$dbInst->query($sql);
							}
						}
					}
				}
			}
		}
		$i++;
		echo 'Inserted  firm #'.$i.'...<br />';
	}
}

echo 'Nice work!. '.$i.' firms total inserted.';


function build_sql($tablename = '', $fld = array(), $action = 'INSERT') {
	global $dbInst;
	foreach ($fld as $key => $val) {
		$fld[$key] = $dbInst->checkStr(iconv("CP1251", "UTF-8", $val));
	}
	return "$action INTO `$tablename` (`".implode("`, `", array_keys($fld))."`) VALUES ('".implode("', '", array_values($fld))."')";
}

function get_records($sql) {
	global $accessDB;
	$rows = array();

	try {
		$mdb = new mdb($accessDB); // your own mdb filename required
		$mdb->execute($sql); // your own table in the mdb file

		while( !$mdb->eof() ) {
			$row = array();
			for ($i = 0; $i < $mdb->fieldcount(); $i++) {
				$key = $mdb->fieldname($i);
				$val = $mdb->fieldvalue($key);
				$row[$key] = $val;
			}
			$rows[] = $row;
			$mdb->movenext();
		}

		$mdb->close();
	} catch (Exception $ex) {
		echo $ex->getMessage().' / '.$sql.'<br />';
	}
	return $rows;
}

function get_one($sql) {
	$rows = get_records($sql);
	return (isset($rows[0])) ? $rows[0] : null;
}

function regenerateMapId($firm_id = 0, $subdivision_name = '', $wplace_name = '', $position_name = '', $wplace_workcond = '', $position_workcond = '', $factors = array()) {
	global $dbInst;
	$map_id = 0;

	// fix names
	$subdivision_name = str_replace('^', '"', $subdivision_name);
	$wplace_name = str_replace('^', '"', $wplace_name);
	$position_name = str_replace('^', '"', $position_name);
	$position_workcond = str_replace('^', '"', $position_workcond);
	$wplace_workcond = str_replace('^', '"', $wplace_workcond);

	$subdivision_id = $dbInst->GiveValue('subdivision_id', 'subdivisions', "WHERE `firm_id` = $firm_id AND `subdivision_name` = '".$dbInst->checkStr($subdivision_name)."'", 0);
	if(empty($subdivision_id) && !empty($subdivision_name)) {
		$subdivision_position = $dbInst->GiveValue("COUNT(*) AS `cnt`", 'subdivisions', "WHERE `firm_id` = $firm_id");
		$subdivision_position = (empty($subdivision_position)) ? 1 : $subdivision_position + 1;
		$sql = "INSERT INTO `subdivisions` (`firm_id`, `subdivision_name`, `subdivision_position`) VALUES ($firm_id, '".$dbInst->checkStr($subdivision_name)."', $subdivision_position)";
		$subdivision_id = $dbInst->query($sql);
	}
	$wplace_id = $dbInst->GiveValue('wplace_id', 'work_places', "WHERE `firm_id` = $firm_id AND `wplace_name` = '".$dbInst->checkStr($wplace_name)."'", 0);
	if(empty($wplace_id) && !empty($wplace_name)) {
		$wplace_position = $dbInst->GiveValue("COUNT(*) AS `cnt`", 'work_places', "WHERE `firm_id` = $firm_id");
		$wplace_position = (empty($wplace_position)) ? 1 : $wplace_position + 1;
		$sql = "INSERT INTO `work_places` (`firm_id`, `wplace_name`, `wplace_position`, `wplace_workcond`) VALUES ($firm_id, '".$dbInst->checkStr($wplace_name)."', $wplace_position, '".$dbInst->checkStr($wplace_workcond)."')";
		$wplace_id = $dbInst->query($sql);
	}
	$position_id = $dbInst->GiveValue('position_id', 'firm_positions', "WHERE `firm_id` = $firm_id AND `position_name` = '".$dbInst->checkStr($position_name)."'", 0);
	if(empty($position_id) && !empty($position_name)) {
		$position_position = $dbInst->GiveValue("COUNT(*) AS `cnt`", 'firm_positions', "WHERE `firm_id` = $firm_id");
		$position_position = (empty($position_position)) ? 1 : $position_position + 1;
		$sql = "INSERT INTO `firm_positions` (`firm_id`, `position_name`, `position_position`, `position_workcond`) VALUES ($firm_id, '".$dbInst->checkStr($position_name)."', $position_position, '".$dbInst->checkStr($position_workcond)."')";
		$position_id = $dbInst->query($sql);
	}
	$map_id = $dbInst->GiveValue('map_id', 'firm_struct_map', "WHERE `firm_id` = $firm_id AND `subdivision_id` = $subdivision_id AND `wplace_id` = $wplace_id AND `position_id` = $position_id", 0);
	if(empty($map_id)) {
		$sql = "INSERT INTO `firm_struct_map` (`firm_id`, `subdivision_id`, `wplace_id`, `position_id`) VALUES ($firm_id, $subdivision_id, $wplace_id, $position_id)";
		$map_id = $dbInst->query($sql);
	}

	if(!empty($factors)) {
		$hit = false;
		foreach ($factors as $key => $val) {
			if(empty($val) || '-' == $val) continue;
			$factors[$key] = $dbInst->checkStr($val);
			$hit = true;
		}
		
		$factors['map_id'] = $map_id;
		$factors['firm_id'] = $firm_id;
		$factors['subdivision_id'] = $subdivision_id;
		$factors['wplace_id'] = $wplace_id;
		
		if($hit) {
			$sql = "REPLACE INTO `wplace_factors_map` (`".implode("`, `", array_keys($factors))."`) VALUES ('".implode("', '", array_values($factors))."')";
			$dbInst->query($sql);
		}
	}

	return $map_id;
}














