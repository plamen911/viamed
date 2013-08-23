<?php
require ('includes.php');

$firm_id = (isset($_GET['firm_id']) && is_numeric($_GET['firm_id'])) ? intval($_GET['firm_id']) : 0;
$tab = (isset($_GET['tab']) && in_array($_GET['tab'], array('info', 'struct', 'struct_map', 'workers', 'charts', 'checkup'))) ? $_GET['tab'] : 'info';

if(isset($_POST['btnImport']) && $_FILES['datafile']['tmp_name']) {
	$message = array();
	$filename = $_FILES['datafile']['name'];
	$ftmp_name = $_FILES['datafile']['tmp_name'];
	$mime_type = $_FILES['datafile']['type'];
	$filesize = $_FILES['datafile']['size'];
	//Allowable file Mime Types. Add more mime types if you want
	$FILE_MIMES = array('application/vnd.ms-excel');
	//Allowable file ext. names. you may add more extension names.
	$FILE_EXTS  = array('xls');
	$file_ext = (preg_match('/\.([A-Za-z]+)$/i', $filename, $matches)) ? strtolower($matches[1]) : '';
	if (!in_array($mime_type, $FILE_MIMES) && !in_array($file_ext, $FILE_EXTS)) {
		setFlash('Съжалявам, '.$filename.' ('.$mime_type.') не е Excel файл. Моля, изберете Excel файл, който е по образеца, показан по-долу.');
		header('Location: firm_info.php'.((isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) ? '?'.$_SERVER['QUERY_STRING'].'' : '').'#xlsanc');
		exit();
	}

	if (is_uploaded_file($_FILES['datafile']['tmp_name'])) {
		require_once 'Excel/reader.php';
		$data = new Spreadsheet_Excel_Reader();
		$data->setOutputEncoding('CP1251');// Set output Encoding.
		$data->read($_FILES['datafile']['tmp_name']);
		if(1 < $data->sheets[0]['numRows']) {
			// Remove old data
			/*if(isset($_POST['chkRemoveExisting'])) {
				if($tab == 'charts') {
					$sql = "DELETE FROM `patient_charts` WHERE `firm_id` = $firm_id AND julianday(`date_added`) >= julianday('".date('Y-m-d H:i:s', strtotime('-1 hours'))."')";
					$dbInst->query($sql);
				} else {
					$sql = "SELECT * FROM `workers` WHERE `firm_id` = $firm_id AND julianday(`date_added`) >= julianday('".date('Y-m-d H:i:s', strtotime('-1 hours'))."')";
					$rows = $dbInst->query($sql);
					if(!empty($rows)) {
						foreach ($rows as $row) {
							$dbInst->removeWorker($row['worker_id']);
						}
					}
				}
			}*/
			
			$oldEGNs = array();
			$oldPChDates = array();
			if($tab == 'workers') {
				// Insert workers that don't exist, based on EGN
				$flds = $dbInst->query("SELECT `worker_id`, `egn` FROM `workers` WHERE `firm_id` = $firm_id");
				if(!empty($flds)) {
					foreach ($flds as $fld) {
						$oldEGNs[$fld['egn']] = $fld['worker_id'];
					}
				}
			} else {
				$flds = $dbInst->query("SELECT `chart_id`, `worker_id`, `hospital_date_from`, `hospital_date_to` FROM `patient_charts` WHERE `firm_id` = $firm_id");
				if(!empty($flds)) {
					foreach ($flds as $fld) {
						$oldPChDates[$fld['worker_id'].'_'.substr($fld['hospital_date_from'], 0, 10).'_'.substr($fld['hospital_date_to'], 0, 10)] =$fld['chart_id'];
					}
				}
			}
			$date_added = $date_modified = date('Y-m-d H:i:s');
			$cnt = 0;
			for ($i = 2; $i <= $data->sheets[0]['numRows']; $i++) {
				$row = array();
				for ($j = 1; $j <= $data->sheets[0]['numCols']; $j++) {
					// "date" | "number" | "unknown"
					if(isset($data->sheets[0]['cellsInfo'][$i][$j]['type']) && $data->sheets[0]['cellsInfo'][$i][$j]['type'] == 'date') {
						$cellValue = date('Y-m-d H:i:s', $data->sheets[0]['cellsInfo'][$i][$j]['raw'] - 60 * 60 * 24 * 1);
					} else {
						$cellValue = (isset($data->sheets[0]['cells'][$i][$j])) ? $data->sheets[0]['cells'][$i][$j] : '';
					}
					$row[] = $dbInst->checkStr(cp1251_to_utf8($cellValue));
				}

				if($tab == 'charts') {
					// Import patient charts
					if(isset($row[0])) { $r['egn'] = $row[0]; } else continue;
					if(isset($row[2])) { $r['chart_num'] = $row[2]; } else continue;
					if(isset($row[3])) { $r['medical_types'] = (!empty($row[3])) ? serialize(array(strval($row[3]))) : ''; } else continue;
					if('a:1:{i:0;i:1;}' == $r['medical_types']) $r['medical_types'] = 'a:1:{i:0;s:1:"1";}';
					elseif('a:1:{i:0;i:2;}' == $r['medical_types']) $r['medical_types'] = 'a:1:{i:0;s:1:"2";}';
					elseif('a:1:{i:0;i:3;}' == $r['medical_types']) $r['medical_types'] = 'a:1:{i:0;s:1:"3";}';
					elseif('a:1:{i:0;i:4;}' == $r['medical_types']) $r['medical_types'] = 'a:1:{i:0;s:1:"4";}';
					$d = new ParseBGDate();
					if(isset($row[4])) {
						$r['published_date'] = (!empty($row[4]) && false !== $ts = strtotime($row[4])) ? date('Y-m-d', $ts).' 00:00:00' : '';
						if(empty($r['published_date']) && $d->Parse($row[4])) {
							$r['published_date'] = date('Y-m-d', strtotime($d->year.'-'.$d->month.'-'.$d->day)).' 00:00:00';
						}
						// hack for dates
						elseif(empty($r['published_date'])) {
							$ary = $data->createDate($row[4]);
							$r['published_date'] = (10 == strlen($ary[1])) ? date('Y-m-d', intval($ary[1]) - 60 * 60 * 24 * 1).' 00:00:00' : '';
						}
						if(empty($r['published_date'])) continue;
					} else continue;
					
					$ts1 = 0;
					if(isset($row[5])) {
						$r['hospital_date_from'] = (!empty($row[5]) && false !== $ts1 = strtotime($row[5])) ? date('Y-m-d', $ts1).' 00:00:00' : '';
						if(empty($r['hospital_date_from']) && $d->Parse($row[5])) {
							$ts1 = strtotime($d->year.'-'.$d->month.'-'.$d->day);
							$r['hospital_date_from'] = date('Y-m-d', $ts1).' 00:00:00';
						}
						// hack for dates
						elseif(empty($r['hospital_date_from'])) {
							$ary = $data->createDate($row[5]);
							$ts1 = intval($ary[1]);
							$r['hospital_date_from'] = (10 == strlen($ary[1])) ? date('Y-m-d', $ts1 - 60 * 60 * 24 * 1).' 00:00:00' : '';
						}
						if(empty($r['hospital_date_from'])) continue;
					} else continue;
					
					$ts2 = 0;
					if(isset($row[6])) {
						$r['hospital_date_to'] = (!empty($row[6]) && false !== $ts2 = strtotime($row[6])) ? date('Y-m-d', $ts2).' 00:00:00' : '';
						if(empty($r['hospital_date_to']) && $d->Parse($row[6])) {
							$ts2 = strtotime($d->year.'-'.$d->month.'-'.$d->day);
							$r['hospital_date_to'] = date('Y-m-d', $ts2).' 00:00:00';
						}
						// hack for dates
						elseif(empty($r['hospital_date_to'])) {
							$ary = $data->createDate($row[6]);
							$ts2 = intval($ary[1]);
							$r['hospital_date_to'] = (10 == strlen($ary[1])) ? date('Y-m-d', $ts2 - 60 * 60 * 24 * 1).' 00:00:00' : '';
						}
						if(empty($r['hospital_date_to'])) continue;
					} else continue;
					
					if(!$ts1 || !$ts2) {
						$message[] = 'Трябва да има посочени начална и крайна дата (кол. F и G) на болничния на работещия с ЕГН '.$r['egn'].'. | '.$ts1.' | '.$ts2.' | '.$row[6];
						continue;
					}
					if($ts1 > $ts2) {
						$message[] = 'Крайната дата не може да бъде преди началната дата на болничния (кол. F и G) на работещия с ЕГН '.$r['egn'].'.';
						continue;
					}				
					
					if(isset($row[7])) { $r['days_off'] = intval($row[7]); }
					if(empty($r['days_off'])) {
						// Calculate how many days spans patient's chart
						$r['days_off'] = round((($ts2 - $ts1) / ((60 * 60) * 24)) + 1);
						
					}
					
					if(isset($row[8])) { $r['mkb_id'] = fixMkbCode($row[8]); } else continue;
					if(isset($row[9])) { $r['reason_id'] = sprintf("%02s", $row[9]); } else continue;

					if(empty($r['egn'])) continue;
					if(empty($r['hospital_date_from'])) continue;
					if(empty($r['hospital_date_to'])) continue;
					if(empty($r['days_off'])) continue;
					if(empty($r['mkb_id'])) {
						$message[] = 'Кодът по МКБ (кол. I) на диагнозата на работещия с ЕГН '.$r['egn'].' ('.$row[1].') е празен.';
						continue;
					}
					if(empty($r['reason_id'])) continue;

					$error = 0;
					$worker_id = $dbInst->GiveValue('worker_id', 'workers', "WHERE `firm_id` = $firm_id AND `egn` = '$r[egn]'", 0);
					if(empty($worker_id)) {
						if(!empty($r['egn'])) {
							$message[] = 'Работещият с ЕГН '.$r['egn'].' ('.$row[1].') не може да бъде намерен в текущата фирма.';
							$error = 1;
						}
					}
					if(!empty($row[3]) && !in_array(strval($row[3]), array('1', '2', '3', '4'))) {
						$message[] = 'Типът "'.$row[3].'" на болничния (кол. D) на работещия с ЕГН '.$r['egn'].' ('.$row[1].') трябва да бъде число от 1 до 4 вкл.';
						$error = 1;
					}
					if(!empty($r['mkb_id'])) {
						$num = $dbInst->fnCountRow('mkb', "mkb_id = '".$dbInst->checkStr($r['mkb_id'])."'");
						if(!$num) {
							$message[] = 'Кодът по МКБ "'.$r['mkb_id'].'" (кол. I) на диагнозата на работещия с ЕГН '.$r['egn'].' ('.$row[1].') е невалиден.';
							$error = 1;
						}
					}
					if(!empty($row[9]) && !in_array(intval($row[9]), range(1, 27))) {
						$message[] = 'Причината "'.$row[9].'" (кол. J) за болничния на работещия с ЕГН '.$r['egn'].' ('.$row[1].') трябва да бъде число от 1 до 27 вкл.';
						$error = 1;
					}
					if($error) continue;

					if(!isset($oldPChDates[$worker_id.'_'.substr($r['hospital_date_from'], 0, 10).'_'.substr($r['hospital_date_to'], 0, 10)])) {
						$sql = "INSERT INTO `patient_charts` (`firm_id`, `worker_id`, `chart_num`, `hospital_date_from`, `hospital_date_to`, `days_off`, `mkb_id`, `medical_types`, `reason_id`, `date_added`, `date_modified`, `published_date`) VALUES ($firm_id, $worker_id, '$r[chart_num]', '$r[hospital_date_from]', '$r[hospital_date_to]', $r[days_off], '$r[mkb_id]', '$r[medical_types]', '$r[reason_id]', '$date_added', '$date_modified', '$r[published_date]')";
						$chart_id = $dbInst->query($sql);
					} else {
						$chart_id = $oldPChDates[$worker_id.'_'.substr($r['hospital_date_from'], 0, 10).'_'.substr($r['hospital_date_to'], 0, 10)];
						$sql = "UPDATE `patient_charts` SET `chart_num` = '$r[chart_num]', `days_off` = $r[days_off], `mkb_id` = '$r[mkb_id]', `medical_types` = '$r[medical_types]', `reason_id` = '$r[reason_id]', `date_modified` = '$date_modified', `published_date` = '$r[published_date]' WHERE `chart_id` = $chart_id";
						$dbInst->query($sql);
					}
					if(!empty($chart_id)) $cnt++;
				} else {
					// Import workers
					$r['firm_id'] = $firm_id;
					$r['is_active'] = '1';
					$r['date_added'] = $r['date_modified'] = date('Y-m-d H:i:s');
					$r['modified_by'] = (isset($_SESSION['sess_user_id'])) ? $_SESSION['sess_user_id'] : 0;
					$r['fname'] = '';
					$r['sname'] = '';
					$r['lname'] = '';

					if(isset($row[0])) {
						$egn = $row[0];
						if(preg_match('/^[0-9]{10}$/', $egn)) {
							$y = substr($egn, 0, 2);
							$m = substr($egn, 2, 2);
							$d = substr($egn, 4, 2);
							$sex = substr($egn, 8, 1);
							$r['egn'] = $egn;
							$r['sex'] = (($sex%2) ? 'Ж' : 'М');
							$r['birth_date'] = date('Y-m-d', mktime(0, 0, 0, $m, $d, (1900 + $y))).' 00:00:00';
						} else {
							$r['egn'] = $egn;
							$r['sex'] = '';
							$r['birth_date'] = '';
						}
					} else continue;
					if(isset($row[1]) && !empty($row[1])) {
						$tokens = explode(' ', $row[1]);
						$r['fname'] = (isset($tokens[0])) ? $tokens[0] : '';
						$r['sname'] = (isset($tokens[1])) ? $tokens[1] : '';
						$r['lname'] = (isset($tokens[2])) ? $tokens[2] : '';
						if(empty($r['lname'])) {
							$r['lname'] = $r['sname'];
							$r['sname'] = '';
						}
					} else continue;

					//Подразделение - subdivision_name
					//*Длъжност - position_name
					//*Работно място - wplace_name
					$subdivision_name = $position_name = $wplace_name = '';
					if(isset($row[2])) { $subdivision_name = $row[2]; }
					if(isset($row[3])) { $position_name = $row[3]; }
					if(isset($row[4])) { $wplace_name = $row[4]; } else continue;
					if(empty($position_name)) continue;
					if(empty($wplace_name)) $wplace_name = $position_name;

					$d = new ParseBGDate();
					if(isset($row[5]) && !empty($row[5])) {
						$r['date_curr_position_start'] = (!empty($row[5]) && false !== $ts = strtotime($row[5])) ? date('Y-m-d H:i:s', $ts) : '';
						if(empty($r['date_curr_position_start']) && $d->Parse($r['date_curr_position_start'])) {
							$r['date_curr_position_start'] = $d->year.'-'.$d->month.'-'.$d->day.' 00:00:00';
						}
						// hack for dates
						elseif(empty($r['date_curr_position_start'])) {
							$ary = $data->createDate($row[5]);
							$r['date_curr_position_start'] = (10 == strlen($ary[1])) ? date('Y-m-d', intval($ary[1]) - 60 * 60 * 24 * 1).' 00:00:00' : '';
						}
					} else continue;


					$r['date_retired'] = (isset($row[6]) && !empty($row[6]) && false !== $ts = strtotime($row[6])) ? date('Y-m-d H:i:s', $ts) : '';
					if(empty($r['date_retired']) && $d->Parse($r['date_retired'])) {
						$r['date_retired'] = $d->year.'-'.$d->month.'-'.$d->day.' 00:00:00';
					}
					// hack for dates
					elseif(empty($r['date_retired'])) {
						$ary = $data->createDate($row[6]);
						$r['date_retired'] = (10 == strlen($ary[1])) ? date('Y-m-d', intval($ary[1]) - 60 * 60 * 24 * 1).' 00:00:00' : '';
					}
					
					if(empty($r['egn'])) continue;
					if(empty($r['fname'])) continue;
					//if(empty($r['lname'])) continue;
					$r['address'] = (isset($row[7])) ? $row[7] : '';

					$r['map_id'] = regenerateMapId($firm_id, $subdivision_name, $wplace_name, $position_name);
					if(!isset($oldEGNs[$r['egn']])) {
						$sql = "INSERT INTO `workers` (`".implode("`,`", array_keys($r))."`) VALUES ('".implode("','", array_values($r))."')";
						$worker_id = $dbInst->query($sql);
					} else {
						$worker_id = $oldEGNs[$r['egn']];
						$sql = "UPDATE `workers` SET ";
						foreach ($r as $key => $val) { $sql .= "`$key` = '$val',"; }
						$sql = substr($sql, 0, -1);
						$sql .= " WHERE `worker_id` = $worker_id";
						$dbInst->query($sql);
					}
					if(!empty($worker_id)) $cnt++;
				}
			}//end for loop
			$message[] = '==== БРОЙ УСПЕШНО ВЪВЕДЕНИ ЗАПИСИ: '.$cnt.' ====';
			setFlash(implode('<br />', $message));
		}
		header('Location: firm_info.php'.((isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) ? '?'.$_SERVER['QUERY_STRING'].'' : '').'#xlsanc');
		exit();
	} else {
		setFlash('Possible file upload attack: '.$filename.' ('.$mime_type.').');
		header('Location: firm_info.php'.((isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) ? '?'.$_SERVER['QUERY_STRING'].'' : '').'#xlsanc');
		exit();
	}
}

header('Location: firm_info.php'.((isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) ? '?'.$_SERVER['QUERY_STRING'].'' : '').'#xlsanc');
exit();

function regenerateMapId($firm_id = 0, $subdivision_name = '', $wplace_name = '', $position_name = '') {
	global $dbInst;
	$map_id = 0;

	// fix names
	$subdivision_name = str_replace('^', '"', $subdivision_name);
	$wplace_name = str_replace('^', '"', $wplace_name);
	$position_name = str_replace('^', '"', $position_name);

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
		$sql = "INSERT INTO `work_places` (`firm_id`, `wplace_name`, `wplace_position`) VALUES ($firm_id, '".$dbInst->checkStr($wplace_name)."', $wplace_position)";
		$wplace_id = $dbInst->query($sql);
	}
	$position_id = $dbInst->GiveValue('position_id', 'firm_positions', "WHERE `firm_id` = $firm_id AND `position_name` = '".$dbInst->checkStr($position_name)."'", 0);
	if(empty($position_id) && !empty($position_name)) {
		$position_position = $dbInst->GiveValue("COUNT(*) AS `cnt`", 'firm_positions', "WHERE `firm_id` = $firm_id");
		$position_position = (empty($position_position)) ? 1 : $position_position + 1;
		$sql = "INSERT INTO `firm_positions` (`firm_id`, `position_name`, `position_position`) VALUES ($firm_id, '".$dbInst->checkStr($position_name)."', $position_position)";
		$position_id = $dbInst->query($sql);
	}
	$map_id = $dbInst->GiveValue('map_id', 'firm_struct_map', "WHERE `firm_id` = $firm_id AND `subdivision_id` = $subdivision_id AND `wplace_id` = $wplace_id AND `position_id` = $position_id", 0);
	if(empty($map_id)) {
		$sql = "INSERT INTO `firm_struct_map` (`firm_id`, `subdivision_id`, `wplace_id`, `position_id`) VALUES ($firm_id, $subdivision_id, $wplace_id, $position_id)";
		$map_id = $dbInst->query($sql);
	}
	return $map_id;
}
