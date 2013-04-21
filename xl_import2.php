<?php
require ('includes.php');

$firm_id = (isset($_GET['firm_id']) && is_numeric($_GET['firm_id'])) ? intval($_GET['firm_id']) : 0;
$tab = (isset($_GET['tab']) && in_array($_GET['tab'], array('info', 'struct', 'struct_map', 'workers', 'charts', 'checkup'))) ? $_GET['tab'] : 'info';

if(isset($_POST['btnImport']) && $_FILES['datafile']['tmp_name']) {
	$errmsg = array();
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
			if(isset($_POST['chkRemoveExisting'])) {
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
			}

			$date_added = $date_modified = date('Y-m-d H:i:s');
			$cnt = 0;
			for ($i = 2; $i <= $data->sheets[0]['numRows']; $i++) {
				$row = array();
				for ($j = 1; $j <= $data->sheets[0]['numCols']; $j++) {
					if(isset($data->sheets[0]['cellsInfo'][$i][$j]['type']) && $data->sheets[0]['cellsInfo'][$i][$j]['type'] == 'date') {
						$cellValue = date('Y-m-d H:i:s', $data->sheets[0]['cellsInfo'][$i][$j]['raw'] - 60 * 60 * 24 * 1);
					} else {
						$cellValue = (isset($data->sheets[0]['cells'][$i][$j])) ? $data->sheets[0]['cells'][$i][$j] : '';
					}
					$row[] = $dbInst->checkStr(cp1251_to_utf8($cellValue));
				}
				
				$cols = array();
				$cols['A'] = (isset($row[0])) ? $row[0] : '';	// No
				$cols['B'] = (isset($row[1])) ? $row[1] : '';	// ЕГН
				$cols['C'] = (isset($row[2])) ? $row[2] : '';	// Имена
				$cols['D'] = (isset($row[3])) ? $row[3] : '';	// Дата на раждане *			
				$cols['E'] = (isset($row[4])) ? $row[4] : '';	// Пол *
				$cols['F'] = (isset($row[5])) ? $row[5] : '';	// Град
				$cols['G'] = (isset($row[6])) ? $row[6] : '';	// Адрес по лична карта 
				$cols['H'] = (isset($row[7])) ? $row[7] : '';	// Телефон *
				$cols['I'] = (isset($row[8])) ? $row[8] : '';	// Адрес по лична карта 
				$cols['J'] = (isset($row[9])) ? $row[9] : '';	// Телефон *
				$cols['K'] = (isset($row[10])) ? $row[10] : '';
				$cols['L'] = (isset($row[11])) ? $row[11] : '';
				$cols['M'] = (isset($row[12])) ? $row[12] : '';
				$cols['N'] = (isset($row[13])) ? $row[13] : '';
				$cols['O'] = (isset($row[14])) ? $row[14] : '';
				$cols['P'] = (isset($row[15])) ? $row[15] : '';
				$cols['Q'] = (isset($row[16])) ? $row[16] : '';
				$cols['R'] = (isset($row[17])) ? $row[17] : '';
				$cols['S'] = (isset($row[18])) ? $row[18] : '';
				$cols['T'] = (isset($row[19])) ? $row[19] : '';
				$cols['U'] = (isset($row[20])) ? $row[20] : '';
				$cols['V'] = (isset($row[21])) ? $row[21] : '';
				$cols['W'] = (isset($row[22])) ? $row[22] : '';
				$cols['X'] = (isset($row[23])) ? $row[23] : '';
				$cols['Y'] = (isset($row[24])) ? $row[24] : '';
				$cols['Z'] = (isset($row[25])) ? $row[25] : '';
				$cols['AA'] = (isset($row[26])) ? $row[26] : '';
				

				// Import workers
				$r['firm_id'] = $firm_id;
				$r['is_active'] = '1';
				$r['date_added'] = $r['date_modified'] = date('Y-m-d H:i:s');
				$r['modified_by'] = 1;
				$r['fname'] = '';
				$r['sname'] = '';
				$r['lname'] = '';
				
				if(isset($cols['B'])) {
					$egn = $cols['B'];
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
				if(isset($cols['C']) && !empty($cols['C'])) {
					$tokens = explode(' ', $cols['C']);
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

				if(isset($row[5])) {
					$r['date_curr_position_start'] = (!empty($row[5]) && false !== $ts = strtotime($row[5])) ? date('Y-m-d H:i:s', $ts) : '';
				} else continue;
				$r['date_retired'] = (isset($row[6]) && !empty($row[6]) && false !== $ts = strtotime($row[6])) ? date('Y-m-d H:i:s', $ts) : '';

				if(empty($r['egn'])) continue;
				if(empty($r['fname'])) continue;
				//if(empty($r['lname'])) continue;

				$r['map_id'] = regenerateMapId($firm_id, $subdivision_name, $wplace_name, $position_name);

				$sql = "INSERT INTO `workers` (`".implode("`,`", array_keys($r))."`) VALUES ('".implode("','", array_values($r))."')";
				$worker_id = $dbInst->query($sql);
				if(!empty($worker_id)) $cnt++;

			}
			setFlash('БРОЙ УСПЕШНО ВЪВЕДЕНИ ЗАПИСИ: '.$cnt);
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
