<?php
require('includes.php');

$firm_id = (isset($_GET['firm_id']) && is_numeric($_GET['firm_id'])) ? intval($_GET['firm_id']) : 0;
$firmInfo = $dbInst->getFirmInfo($firm_id);
if(!$firmInfo) {
	die('Липсва индентификатор на фирмата!');
}
$worker_id = (isset($_GET['worker_id']) && is_numeric($_GET['worker_id'])) ? intval($_GET['worker_id']) : 0;

// Xajax begin
require ('xajax/xajax_core/xajax.inc.php');
function processWorker($aFormValues) {
	$objResponse = new xajaxResponse();
	
	if(!isset($_SESSION['sess_user_id'])) {
		$objResponse->script('top.location.reload();');
		return $objResponse;
	}

	$objResponse->assign("btnSubmit","disabled",false);
	$objResponse->assign("btnSubmit","value","Съхрани");
	$objResponse->call("DisableEnableForm",false);

	global $dbInst;
	global $firm_id;
	$date_retired = null;

	if(trim($aFormValues['fname']) == '' && trim($aFormValues['lname']) == '') {
		$objResponse->alert("Моля, въведете име или фамилия на работещия.");
		return $objResponse;
	}
	if('' == trim($aFormValues['egn'])/* || 10 != strlen(trim($aFormValues['egn']))*/) {
		$objResponse->alert('Моля, въведете ЕГН на работещия.');
		return $objResponse;
	}
	// Check Personal number processWorker
	$worker_id = intval($aFormValues['worker_id']);
	$isNewWorker = (!empty($worker_id)) ? 0 : 1;
	if(!$worker_id) {// New worker
		$query = sprintf("	SELECT w.fname, w.sname, w.lname, f.name AS firm_name, w.date_retired
							FROM workers w
							LEFT JOIN firms f ON (f.firm_id = w.firm_id)
							WHERE f.firm_id = %d
							AND w.egn = '%s'
							AND w.date_retired = ''
							ORDER BY w.fname, w.sname, w.lname", $firm_id, $dbInst->checkStr($aFormValues['egn']));
	} else {
		$date_retired = $dbInst->GiveValue('date_retired', 'workers', "WHERE `worker_id` = $worker_id");
		$query = sprintf("	SELECT w.fname, w.sname, w.lname, f.name AS firm_name, w.date_retired
							FROM workers w
							LEFT JOIN firms f ON (f.firm_id = w.firm_id)
							WHERE f.firm_id = %d
							AND w.egn = '%s' 
							AND w.worker_id != %d
							AND w.date_retired = ''
							ORDER BY w.fname, w.sname, w.lname", $firm_id, $dbInst->checkStr($aFormValues['egn']), $worker_id);
	}
	$rows = $dbInst->fnSelectRows($query);
	if(count($rows)) {
		$lines = array();
		$i = 1;
		foreach ($rows as $row) {
			$lines[] = $i.'). '.$row['fname'].' '.$row['sname'].' '.$row['lname'].' ('.$row['firm_name'].')'.((!empty($row['date_retired']) && false !== $ts = strtotime($row['date_retired'])) ? ', напуснал на '.date('d.m.Y', $ts).' г.' : '');
			$i++;
		}
		$msg = "Въведеният ЕГН ($aFormValues[egn]) съвпада с ЕГН на:\n";
		$msg .= implode("\n", $lines);
		$objResponse->alert($msg);
		return $objResponse;
	}

	if(!intval($aFormValues['location_id']) && trim($aFormValues['location_name']) == '') {
		$objResponse->assign("location_name","value","");
		$objResponse->assign("location_id","value",0);
	}

	$d = new ParseBGDate();
	if( trim($aFormValues['date_curr_position_start']) != '' && !$d->Parse(trim($aFormValues['date_curr_position_start'])) ) {
		$objResponse->alert(trim($aFormValues['date_curr_position_start']).' е невалидна дата!');
		return $objResponse;
	}
	if( trim($aFormValues['date_career_start']) != '' && !$d->Parse(trim($aFormValues['date_career_start'])) ) {
		$objResponse->alert(trim($aFormValues['date_career_start']).' е невалидна дата!');
		return $objResponse;
	}
	// Check dates: Compare `date_curr_position_start` and `date_career_start` dates
	if( trim($aFormValues['date_curr_position_start']) != '' && trim($aFormValues['date_career_start']) != '' ) {
		$d->Parse(trim($aFormValues['date_curr_position_start']));
		$date_curr_position_start = mktime(0, 0, 0, $d->getMonth(), $d->getDay(), $d->getYear());
		$d->Parse(trim($aFormValues['date_career_start']));
		$date_career_start = mktime(0, 0, 0, $d->getMonth(), $d->getDay(), $d->getYear());
		if($date_career_start > $date_curr_position_start) {
			$objResponse->alert('Датата на постъпване на настоящата длъжност не може да бъде преди датата на трудовия стаж.');
			return $objResponse;
		}
	}

	$map_id = 0;
	$subdivision_name = (isset($aFormValues['subdivision_id']) && '-' != $aFormValues['subdivision_id']) ? $dbInst->checkStr($aFormValues['subdivision_id']) : '';
	$wplace_name = (isset($aFormValues['wplace_id']) && '-' != $aFormValues['wplace_id']) ? $dbInst->checkStr($aFormValues['wplace_id']) : '';
	$position_name = (isset($aFormValues['position_id']) && '-' != $aFormValues['position_id']) ? $dbInst->checkStr($aFormValues['position_id']) : '';
	if(!empty($position_name)) {
		if(empty($wplace_name)) $wplace_name = $position_name;
		$position_id = $dbInst->GiveValue('position_id', 'firm_positions', "WHERE `firm_id` = $firm_id AND `position_name` = '$position_name'", 0);
		if(empty($position_id)) {
			$position_position = $dbInst->fnCountRow('firm_positions', "firm_id = $firm_id");
			$position_id = $dbInst->query("INSERT INTO `firm_positions` (`firm_id`, `position_name`, `position_workcond`, `position_position`, `progroup`) VALUES ($firm_id, '".mb_strtoupper($position_name, 'utf-8')."', '', ".($position_position + 1).", 0)");
		}
		$wplace_id = $dbInst->GiveValue('wplace_id', 'work_places', "WHERE `firm_id` = $firm_id AND `wplace_name` = '$wplace_name'", 0);
		if(empty($wplace_id)) {
			$wplace_position = $dbInst->fnCountRow('work_places', "firm_id = $firm_id");
			$wplace_id = $dbInst->query("INSERT INTO `work_places` (`firm_id`, `wplace_name`, `wplace_workcond`, `wplace_position`) VALUES ($firm_id, '".mb_strtoupper($wplace_name, 'utf-8')."', '', ".($wplace_position + 1).")");
		}
		$subdivision_id = 0;
		if(empty($subdivision_name)) {
			$subdivision_name = $dbInst->GiveValue('name', 'firms', "WHERE `firm_id` = $firm_id", 0);
			$subdivision_name = mb_strtoupper($dbInst->checkStr($subdivision_name), 'utf-8');
		}
		$subdivision_id = $dbInst->GiveValue('subdivision_id', 'subdivisions', "WHERE `firm_id` = $firm_id AND `subdivision_name` = '$subdivision_name'", 0);
		if(empty($subdivision_id)) {
			$subdivision_position = $dbInst->fnCountRow('subdivisions', "firm_id = $firm_id");
			$subdivision_id = $dbInst->query("INSERT INTO `subdivisions` (`firm_id`, `subdivision_name`, `subdivision_position`) VALUES ($firm_id, '".mb_strtoupper($subdivision_name, 'utf-8')."', ".($subdivision_position + 1).")");
		}
		$map_id = $dbInst->GiveValue('map_id', 'firm_struct_map', "WHERE `firm_id` = $firm_id AND `subdivision_id` = $subdivision_id AND `wplace_id` = $wplace_id AND `position_id` = $position_id", 0);
		if(empty($map_id)) {
			$map_id = $dbInst->query("INSERT INTO `firm_struct_map` (`firm_id`, `subdivision_id`, `wplace_id`, `position_id`) VALUES ($firm_id, $subdivision_id, $wplace_id, $position_id)");
		}
	}
	$aFormValues['map_id'] = (!empty($map_id)) ? intval($map_id) : 0;

	$worker_id = $dbInst->processWorker($aFormValues); // Insert worker
	$objResponse->assign("worker_id","value",$worker_id);
	$objResponse->assign('lastModified','innerHTML',$dbInst->getModifiedBy('workers', 'worker_id', $worker_id));
	//$objResponse->alert("Данните за работещия бяха успешно въведени!");
	if($isNewWorker) {
		$objResponse->assign('form_is_dirty', 'value', '1');
	} else {
		$objResponse->assign('form_is_dirty', 'value', '0');
		$sql = "SELECT w.fname, w.sname, w.lname, w.egn, w.date_retired, strftime('%d.%m.%Y г.', w.date_retired, 'localtime') AS date_retired_h, p.position_name
				FROM workers w
				LEFT JOIN firm_struct_map m ON ( m.map_id = w.map_id )
				LEFT JOIN firm_positions p ON ( p.position_id = m.position_id )
				WHERE `worker_id` = $worker_id";
		$row = $dbInst->fnSelectSingleRow($sql);
		if(!empty($row)) {
			if($date_retired != $row['date_retired']) {
				$objResponse->assign('form_is_dirty', 'value', '1');
			} else {
				$fname = (($row['date_retired'] != '') ? '<img src="img/caution.gif" alt="retired" width="11" height="11" border="0" title="Напуснал на ' . $row['date_retired_h'] . '" /> ' : '').HTMLFormat($row['fname']);
				$objResponse->script('if(parent.$("#w_fname_'.$worker_id.'")[0]){parent.$("#w_fname_'.$worker_id.'").html("'.addslashes($fname).'")}');
				$objResponse->script('if(parent.$("#w_sname_'.$worker_id.'")[0]){parent.$("#w_sname_'.$worker_id.'").html("'.HTMLFormat($row['sname']).'")}');
				$objResponse->script('if(parent.$("#w_lname_'.$worker_id.'")[0]){parent.$("#w_lname_'.$worker_id.'").html("'.HTMLFormat($row['lname']).'")}');
				$objResponse->script('if(parent.$("#w_egn_'.$worker_id.'")[0]){parent.$("#w_egn_'.$worker_id.'").html("'.HTMLFormat($row['egn']).'")}');
				$objResponse->script('if(parent.$("#w_position_name_'.$worker_id.'")[0]){parent.$("#w_position_name_'.$worker_id.'").html("'.HTMLFormat($row['position_name']).'")}');
			}
		}
	}
	return $objResponse;
}
function processProRoute($aFormValues) {
	$objResponse = new xajaxResponse();
	
	if(!isset($_SESSION['sess_user_id'])) {
		$objResponse->script('top.location.reload();');
		return $objResponse;
	}

	$objResponse->assign("btnSubmit","disabled",false);
	$objResponse->assign("btnSubmit","value","Съхрани");
	$objResponse->call("DisableEnableForm",false);

	$worker_id = intval($aFormValues['worker_id']);
	if(!$worker_id) {
		$objResponse->alert("Моля, въведете данни за работещия.");
		$objResponse->script("window.location.href='".basename($_SERVER['PHP_SELF'])."?worker_id=".intval($aFormValues['worker_id'])."&firm_id=".intval($aFormValues['firm_id'])."&tab=worker_data';");
		return $objResponse;
	}

	global $dbInst;
	// Global check
	foreach ($aFormValues as $key=>$val) {
		if(preg_match('/^firm_name_(\d+)$/', $key, $matches)) {
			$route_id = $matches[1];
			$firm_name = $dbInst->checkStr($aFormValues['firm_name_'.$route_id]);
			$position = $dbInst->checkStr($aFormValues['position_'.$route_id]);
			$exp_length_y = $dbInst->checkStr($aFormValues['exp_length_y_'.$route_id]);
			$exp_length_m = $dbInst->checkStr($aFormValues['exp_length_m_'.$route_id]);
			/*if($route_id && $position == '') {
			$objResponse->alert("Моля, въведете всички данни за професионалния стаж на работещия.");
			return $objResponse;
			}
			else*/if (intval($exp_length_m) < 0 || intval($exp_length_m) > 12) {
			$objResponse->alert($exp_length_m." е невалиден месец!");
			return $objResponse;
			}
			/*elseif ($route_id && (!$exp_length_y && !$exp_length_m)) {
			$objResponse->alert("Моля, въведете продължителност на стажа.");
			return $objResponse;
			}*/
		}
	}
	$dbInst->processProRoute($aFormValues); // add/update worker's professional route
	$objResponse->assign("panel","innerHTML",echoProRoute($worker_id));
	$objResponse->assign('lastModified','innerHTML',$dbInst->getModifiedBy('workers', 'worker_id', $worker_id));
	return $objResponse;
}
function processReadjustment($aFormValues = null) {
	$objResponse = new xajaxResponse();
	
	if(!isset($_SESSION['sess_user_id'])) {
		$objResponse->script('top.location.reload();');
		return $objResponse;
	}

	$objResponse->assign("btnSubmit","disabled",false);
	$objResponse->assign("btnSubmit","value","Съхрани");
	$objResponse->call("DisableEnableForm",false);

	$worker_id = intval($aFormValues['worker_id']);
	if(!$worker_id) {
		$objResponse->alert("Моля, въведете данни за работещия.");
		$objResponse->script("window.location.href='".basename($_SERVER['PHP_SELF'])."?worker_id=".intval($aFormValues['worker_id'])."&firm_id=".intval($aFormValues['firm_id'])."&tab=worker_data';");
		return $objResponse;
	}

	global $dbInst;
	$queries = array();
	$errmsg = array();
	foreach ($aFormValues as $key=>$val) {
		if(preg_match('/^published_on_(\d+)$/', $key, $matches)) {
			$readjustment_id = $matches[1];
			$published_on = $dbInst->checkStr($aFormValues['published_on_'.$readjustment_id]);
			$mkb_id = $dbInst->checkStr($aFormValues['mkb_id_'.$readjustment_id]);
			$diagnosis = $dbInst->checkStr($aFormValues['diagnosis_'.$readjustment_id]);
			$commission = $dbInst->checkStr($aFormValues['commission_'.$readjustment_id]);
			$start_date = $dbInst->checkStr($aFormValues['start_date_'.$readjustment_id]);
			$end_date = $dbInst->checkStr($aFormValues['end_date_'.$readjustment_id]);
			$place = $dbInst->checkStr($aFormValues['place_'.$readjustment_id]);
			if(!$readjustment_id && empty($published_on)) continue;
			
			$d = new ParseBGDate();
			$published_on = (!empty($published_on) && $d->Parse($published_on)) ? $d->year.'-'.$d->month.'-'.$d->day.' 00:00:00' : '';
			$start_date = (!empty($start_date) && $d->Parse($start_date)) ? $d->year.'-'.$d->month.'-'.$d->day.' 00:00:00' : '';
			$end_date = (!empty($end_date) && $d->Parse($end_date)) ? $d->year.'-'.$d->month.'-'.$d->day.' 00:00:00' : '';
			if(!$dbInst->isValidMkb($mkb_id)) $mkb_id = '';
			
			if(!empty($readjustment_id)) {
				$queries[] = "UPDATE `readjustments` SET `published_on` = '$published_on' , `mkb_id` = '$mkb_id' , `diagnosis` = '$diagnosis' , `commission` = '$commission' , `start_date` = '$start_date' , `end_date` = '$end_date' , `place` = '$place' WHERE `id` = $readjustment_id";
			} else {
				$queries[] = "INSERT INTO `readjustments` (`worker_id` , `published_on` , `mkb_id` , `diagnosis` , `commission` , `start_date` , `end_date` , `place`) VALUES ($worker_id , '$published_on' , '$mkb_id' , '$diagnosis' , '$commission' , '$start_date' , '$end_date' , '$place')";
			}
		}
	}
	if(!empty($queries)) {
		foreach ($queries as $sql) {
			$dbInst->query($sql);
		}
		$dbInst->query("UPDATE workers SET date_modified=datetime('now','localtime'), modified_by='".$_SESSION['sess_user_id']."' WHERE worker_id='$worker_id'");
	}
	$objResponse->assign('panel', 'innerHTML', echoReadjustment($worker_id));
	$objResponse->assign('lastModified', 'innerHTML', $dbInst->getModifiedBy('workers', 'worker_id', $worker_id));
	$objResponse->script('mkbAutocomplete();');
	return $objResponse;
}
function processDoctor($aFormValues) {
	$objResponse = new xajaxResponse();

	if(!isset($_SESSION['sess_user_id'])) {
		$objResponse->script('top.location.reload();');
		return $objResponse;
	}
	
	$objResponse->assign("btnDoctor","disabled",false);
	$objResponse->assign("btnDoctor","value","Добави");
	$objResponse->call("DisableEnableForm",false);

	if(trim($aFormValues['d_doctor_name']) == '') {
		$objResponse->alert("Моля, въведете имената на фамилния лекар.");
		return $objResponse;
	}

	global $dbInst;
	$doctor_id = $dbInst->processDoctor($aFormValues); // Insert doctor
	$objResponse->loadcommands(loadPulldown($doctor_id));
	$objResponse->script("tb_remove();");

	return $objResponse;
}
function currentServiceLength($date_curr_position_start) {
	$objResponse = new xajaxResponse();

	$date_curr_position_start = trim($date_curr_position_start);
	$curr_position_length = "";
	if($date_curr_position_start != '') {
		$d = new ParseBGDate();
		if($d->Parse($date_curr_position_start)) {
			$date_curr_position_start = $d->day.'.'.$d->month.'.'.$d->year;
			$curr_position_length = calcTimespan($d->day, $d->month, $d->year);
		}
		$objResponse->assign("date_curr_position_start", "value", $date_curr_position_start);
		$objResponse->assign("curr_position_length", "value", $curr_position_length);
	}
	return $objResponse;
}
function totalServiceLength($date_career_start) {
	$objResponse = new xajaxResponse();

	$date_career_start = trim($date_career_start);
	$career_length = "";
	if($date_career_start != '') {
		$d = new ParseBGDate();
		if($d->Parse($date_career_start)) {
			$date_career_start = $d->day.'.'.$d->month.'.'.$d->year;
			$career_length = calcTimespan($d->day, $d->month, $d->year);
		}
		$objResponse->assign("date_career_start", "value", $date_career_start);
		$objResponse->assign("career_length", "value", $career_length);
	}

	return $objResponse;
}
function calcBirthDate($egn) {
	$objResponse = new xajaxResponse();
	if(preg_match('/^[0-9]{10}$/',$egn)) {
		$y = substr($egn, 0, 2);
		$y = 1900 + intval($y);
		$m = substr($egn, 2, 2);
		$d = substr($egn, 4, 2);
		$sex = substr($egn, 8, 1);
		$birth_date = (false !== $ts = strtotime($y.'-'.$m.'-'.$d)) ? sprintf("%02d.%02d.%04d", $d, $m, $y) : '';
		$objResponse->assign("birth_date", "value", $birth_date);
		$objResponse->assign("sex", "value", (($sex%2) ? 'Ж' : 'М'));
	}
	return $objResponse;
}
function formatDateRetired($date_retired) {
	$objResponse = new xajaxResponse();

	$date_retired = trim($date_retired);
	if($date_retired != '') {
		$d = new ParseBGDate();
		if($d->Parse($date_retired))
		$objResponse->assign("date_retired", "value", $d->day.'.'.$d->month.'.'.$d->year);
		else
		$objResponse->assign("date_retired", "value", "");
	}

	return $objResponse;
}
function populateAbove($map_id) {
	$objResponse = new xajaxResponse();

	if($map_id) {
		global $dbInst;
		$row = $dbInst->getMapRow($map_id);
		$objResponse->assign("subdivision_name","value",stripslashes($row['subdivision_name']));
		$objResponse->assign("wplace_name","value",stripslashes($row['wplace_name']));
	}
	else {
		$objResponse->assign("subdivision_name","value","");
		$objResponse->assign("wplace_name","value","");
	}

	return $objResponse;
}
function removeProRoute($route_id, $worker_id) {
	$objResponse = new xajaxResponse();

	if($_SESSION['sess_user_level'] == 1) { /* admin rights only */
		global $dbInst;
		$count = $dbInst->removeProRoute($route_id, $worker_id);
		$objResponse->assign("panel","innerHTML",echoProRoute($worker_id));
		$objResponse->assign('lastModified','innerHTML',$dbInst->getModifiedBy('workers', 'worker_id', $worker_id));
	}
	return $objResponse;
}
function removeReadjustment($readjustment_id = 0, $worker_id = 0) {
	$objResponse = new xajaxResponse();

	if($_SESSION['sess_user_level'] == 1) { /* admin rights only */
		global $dbInst;
		$dbInst->query("DELETE FROM `readjustments` WHERE `id` = $readjustment_id AND `worker_id` = $worker_id");
		$objResponse->assign("panel", "innerHTML", echoReadjustment($worker_id));
		$objResponse->assign('lastModified', 'innerHTML', $dbInst->getModifiedBy('workers', 'worker_id', $worker_id));
	}
	return $objResponse;
}
function loadPulldown($doctor_id=0) {
	$objResponse = new xajaxResponse();
	$html  = '<select id="doctor_id" name="doctor_id" style="width:59%;">';
	$html .= '<option value="0"> &nbsp;&nbsp;</option>';
	global $dbInst;
	$rows = $dbInst->fnSelectRows("SELECT * FROM doctors ORDER BY doctor_name");
	foreach ($rows as $row) {
		$html .= '<option value="'.$row['doctor_id'].'"'.(($doctor_id==$row['doctor_id'])?' selected="selected"':'').'>'.HTMLFormat($row['doctor_name']).'</option>';
	}
	$html .= '</select>';
	$objResponse->assign("pulldownWrapper","innerHTML",$html);
	return $objResponse;
}
$xajax = new xajax();
$xajax->registerFunction("processWorker");
$xajax->registerFunction("processProRoute");
$xajax->registerFunction("currentServiceLength");
$xajax->registerFunction("totalServiceLength");
$xajax->registerFunction("calcBirthDate");
$xajax->registerFunction("swapFirm");
$xajax->registerFunction("formatDateRetired");
$xajax->registerFunction("guessLocation");
$xajax->registerFunction("processReadjustment");
$xajax->registerFunction("processDoctor");
$xajax->registerFunction("populateAbove");
$xajax->registerFunction("removeProRoute");
$xajax->registerFunction("removeReadjustment");
$xajax->registerFunction("loadPulldown");
$xajax->registerFunction("formatBGDate");
//$xajax->setFlag("debug",true);
$echoJS = $xajax->getJavascript('xajax/');
$xajax->processRequest();
// Xajax end

function echoProRoute($worker_id) {
	global $dbInst;
	$f = $dbInst->getWorkerInfo($worker_id);
	$rows = $dbInst->getProRoute($worker_id);
	ob_start();
	?>
          <table border="0" cellpadding="0" cellspacing="0" class="xlstable" width="770">
            <tr>
              <th>Предприятие</th>
              <th>Длъжност/професия</th>
              <th>Продължителност <br />
                на стажа</th>
              <?php if($_SESSION['sess_user_level'] == 1) { /* admin rights only */ ?>
              <th>&nbsp;</th>
              <?php } ?>
            </tr>
            <?php
            if(isset($rows)) {
            foreach ($rows as $row) { ?>
            <tr>
              <td><input type="text" id="firm_name_<?=$row['route_id']?>" name="firm_name_<?=$row['route_id']?>" value="<?=HTMLFormat($row['firm_name'])?>" size="52" maxlength="100" /></td>
              <td><input type="text" id="position_<?=$row['route_id']?>" name="position_<?=$row['route_id']?>" value="<?=HTMLFormat($row['position'])?>" size="50" maxlength="80" /></td>
              <td align="center"><input type="text" id="exp_length_y_<?=$row['route_id']?>" name="exp_length_y_<?=$row['route_id']?>" size="2" maxlength="2" value="<?=HTMLFormat($row['exp_length_y'])?>" onkeypress="return numbersonly(this, event);" />
                г.
                <input type="text" id="exp_length_m_<?=$row['route_id']?>" name="exp_length_m_<?=$row['route_id']?>" value="<?=HTMLFormat($row['exp_length_m'])?>" size="2" maxlength="2" onkeypress="return numbersonly(this, event);" />
                м. </td>
              <?php if($_SESSION['sess_user_level'] == 1) { /* admin rights only */ ?>
              <td align="center" width="20"><a href="javascript:void(null);" onclick="var answ=confirm('Наистина ли искате да изтриете трудовия стаж?'); if(answ) { xajax_removeProRoute(<?=$row['route_id']?>, <?=$worker_id?>);} return false;" title="Изтрий трудовия стаж"><img src="img/delete.gif" width="15" height="15" border="0" alt="Изтрий" /></a></td>
              <?php } ?>
            </tr>
            <?php }} ?>
            <tr>
              <td><input type="text" id="firm_name_0" name="firm_name_0" value="" size="52" maxlength="100" class="newItem" /></td>
              <td><input type="text" id="position_0" name="position_0" value="" size="50" maxlength="80" class="newItem" /></td>
              <td align="center"><input type="text" id="exp_length_y_0" name="exp_length_y_0" size="2" maxlength="2" value="" onkeypress="return numbersonly(this, event);" class="newItem" />
                г.
                <input type="text" id="exp_length_m_0" name="exp_length_m_0" value="" size="2" maxlength="2" onkeypress="return numbersonly(this, event);" class="newItem" />
                м. </td>
              <?php if($_SESSION['sess_user_level'] == 1) { /* admin rights only */ ?>
              <td align="center">&nbsp;</td>
              <?php } ?>
            </tr>
            <tr>
              <td colspan="4"><p align="center">
                  <input type="button" id="btnSubmit" name="btnSubmit" value="Съхрани" class="nicerButtons" onclick="this.value='обработка...';this.disabled=true;xajax_processProRoute(xajax.getFormValues('frmFirm'));DisableEnableForm(true);return false;" />
                </p></td>
            </tr>
          </table>
	<?php
	$buff = ob_get_contents();
	ob_end_clean();
	return $buff;
}
function echoWorkerData($worker_id, $firmInfo) {
	global $dbInst;
	global $firm_id;
	$f = $dbInst->getWorkerInfo($worker_id);
	ob_start();
	
	$retired = (isset($f['date_retired']) && !empty($f['date_retired'])) ? true : false;
	?>
        <table cellpadding="0" cellspacing="0" class="formBg">
          <tr>
            <td colspan="4" class="leftSplit rightSplit topSplit"><strong>Име: </strong>
                <input type="text" id="fname" name="fname" value="<?=((isset($f['fname']))?HTMLFormat($f['fname']):'')?>" size="30" maxlength="50" onblur="ucfirst(this)" />
                &nbsp;Презиме: <input type="text" id="sname" name="sname" value="<?=((isset($f['sname']))?HTMLFormat($f['sname']):'')?>" size="30" maxlength="50" onblur="ucfirst(this)" />
                &nbsp;<strong>Фамилия: </strong>
                <input type="text" id="lname" name="lname" value="<?=((isset($f['lname']))?HTMLFormat($f['lname']):'')?>" size="30" maxlength="50" onblur="ucfirst(this)" />
              </td>
          </tr>
          <tr>
            <td class="leftSplit"><strong>ЕГН:</strong></td>
            <td>
                <input type="text" id="egn" name="egn" value="<?=((isset($f['egn']))?HTMLFormat($f['egn']):'')?>" size="15" maxlength="15" onKeyPress="return numbersonly(this, event);" onchange="xajax_calcBirthDate(this.value);" />
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Пол:
                <select id="sex" name="sex">
                  <option value="М"<?=((isset($f['sex'])&&$f['sex']=='М')?' selected="selected"':'')?>>Мъж &nbsp;&nbsp;&nbsp;</option>
                  <option value="Ж"<?=((isset($f['sex'])&&$f['sex']=='Ж')?' selected="selected"':'')?>>Жена &nbsp;&nbsp;&nbsp;</option>
                </select>
              </td>
            <td nowrap="nowrap">Дата на раждане:</td>
            <td class="rightSplit">
                <input type="text" id="birth_date" name="birth_date" value="<?=((isset($f['birth_date']))?$f['birth_date2']:'')?>" size="38" maxlength="10" />
                г. </td>
          </tr>
          <tr>
            <td nowrap="nowrap" class="leftSplit">Населено място: </td>
            <td>
            	<input type="text" id="location_name" name="location_name" value="<?=((isset($f['location_name']))?$f['location_name']:'')?>" size="40" maxlength="50" onchange="xajax_guessLocation(this.value);return false;" />
                <input type="hidden" id="location_id" name="location_id" value="<?=((isset($f['location_id']))?HTMLFormat($f['location_id']):'0')?>" />
              </td>
            <td>Адрес: </td>
            <td class="rightSplit">
                <input type="text" id="address" name="address" value="<?=((isset($f['address']))?HTMLFormat($f['address']):'')?>" size="40" maxlength="50" />
              </td>
          </tr>
          <tr>
            <td class="leftSplit">Тел. 1: </td>
            <td>
                <input type="text" id="phone1" name="phone1" value="<?=((isset($f['phone1']))?HTMLFormat($f['phone1']):'')?>" size="40" maxlength="50" />
              </td>
            <td>Тел. 2: </td>
            <td class="rightSplit">
                <input type="text" id="phone2" name="phone2" value="<?=((isset($f['phone2']))?HTMLFormat($f['phone2']):'')?>" size="40" maxlength="50" />
              </td>
          </tr>
          <tr>
            <td colspan="4" class="leftSplit rightSplit"><span class="labeltext">Личен лекар:</span>
              <span id="pulldownWrapper">зареждане...<script type="text/javascript">xajax_loadPulldown(<?=((isset($f['doctor_id']))?$f['doctor_id']:'0')?>);</script></span>&nbsp;&nbsp;<a href="form_doctor.php?doctor_id=0&amp;<?=SESS_NAME.'='.session_id()?>&amp;height=160&amp;width=472&amp;modal=true" title="Добави нов фамилен лекар" class="thickbox"><img src="img/newitem.gif" alt="" width="16" height="16" border="0" /> Нов фамилен лекар</a>
                </td>
          </tr>
          
          <tr>
            <td colspan="4" class="leftSplit rightSplit"><span class="labeltext">Подразделение: </span>
              <select id="subdivision_id" name="subdivision_id" style="width:59%;" class="editable-select">
                <option value="-">-- изберете -- </option>
              	<?php
              	$subdivision_id = 0;
              	$wplace_id = 0;
              	$position_id = 0;
              	$map_id = 0;
              	if(isset($f['map_id']) && !empty($f['map_id'])) {
              		$sql = "SELECT * FROM `firm_struct_map` WHERE `firm_id` = $firm_id AND `map_id` = $f[map_id]";
              		$rows = $dbInst->query($sql);
              		if(!empty($rows)) {
              			foreach ($rows as $row) {
              				$subdivision_id = intval($row['subdivision_id']);
              				$wplace_id = intval($row['wplace_id']);
              				$position_id = intval($row['position_id']);
              				$map_id = intval($row['map_id']);
              				break;
              			}
              		}
              	}
              	$rows = $dbInst->query("SELECT * FROM `subdivisions` WHERE `firm_id` = $firm_id ORDER BY `subdivision_name`, `subdivision_id`");
              	if(!empty($rows)) {
              		foreach ($rows as $row) {
              			$subdivision_name = HTMLFormat($row['subdivision_name']);
              			echo '<option value="'.$subdivision_name.'"'.(($subdivision_id == $row['subdivision_id']) ? ' selected="selected"' : '').'>'.$subdivision_name.'</option>';
              		}
              	}
              	?>
              </select>
              <div class="br"></div>
              <span class="labeltext">Работно място: </span>
              <select id="wplace_id" name="wplace_id" style="width:59%;" class="editable-select">
                <option value="-">-- изберете -- </option>
                <?php
                $rows = $dbInst->query("SELECT * FROM `work_places` WHERE `firm_id` = $firm_id ORDER BY `wplace_name`, `wplace_position`");
                if(!empty($rows)) {
                	foreach ($rows as $row) {
                		$wplace_name = HTMLFormat($row['wplace_name']);
                		echo '<option value="'.$wplace_name.'"'.(($wplace_id == $row['wplace_id']) ? ' selected="selected"' : '').'>'.$wplace_name.' </option>';
                	}
                }
              	?>
              </select>
              <div class="br"></div>
              <span class="labeltext">Длъжност: </span>
              <select id="position_id" name="position_id" style="width:59%;" class="editable-select">
                <option value="-">-- изберете -- </option>
                <?php
                $rows = $dbInst->query("SELECT * FROM `firm_positions` WHERE `firm_id` = $firm_id ORDER BY `position_name`, `position_position`");
                if(!empty($rows)) {
                	foreach ($rows as $row) {
                		$position_name = HTMLFormat($row['position_name']);
                		echo '<option value="'.$position_name.'"'.(($position_id == $row['position_id']) ? ' selected="selected"' : '').'>'.$position_name.' </option>';
                	}
                }
                ?>
              </select>
            </td>
          </tr>
          <tr>
            <td nowrap="nowrap" class="leftSplit">Бележки: </td>
            <td colspan="3" class="rightSplit"><textarea id="notes" name="notes" rows="2" cols="100"><?=((isset($f['notes']))?HTMLFormat($f['notes']):'')?></textarea></td>
          </tr>
          <tr>
            <td colspan="4" class="leftSplit rightSplit"><span class="labeltextL">Тр. стаж по настоящата длъжност от:</span>
                <input type="text" id="date_curr_position_start" name="date_curr_position_start" value="<?=((isset($f['date_curr_position_start']))?$f['date_curr_position_start2']:'')?>" onchange="xajax_currentServiceLength(this.value);return false" size="20" maxlength="10" /> г.
                &nbsp;&nbsp;&nbsp;<img src="img/caret-r.gif" alt="" width="11" height="7" border="0" />&nbsp;&nbsp;
                <?php
                $curr_position_length = '';
                if(isset($f['date_curr_position_start']) && $f['date_curr_position_start'] != '') {
                	$date = substr($f['date_curr_position_start'], 0, 10);
                	list($y, $m, $d) = explode('-',$date);
                	$curr_position_length = calcTimespan($d, $m, $y);
                }
                ?>
                <input type="text" id="curr_position_length" name="curr_position_length" value="<?=$curr_position_length?>" size="20" maxlength="30" readonly="readonly" />
                <div class="br"></div>
                <span class="labeltextL">Общ трудов стаж от:</span>
                <input type="text" id="date_career_start" name="date_career_start" value="<?=((isset($f['date_career_start']))?$f['date_career_start2']:'')?>" onchange="xajax_totalServiceLength(this.value);return false" size="20" maxlength="10" /> г.
                общо
                <?php
                $career_length = '';
                if(isset($f['date_career_start']) && $f['date_career_start'] != '') {
                	$date = substr($f['date_career_start'], 0, 10);
                	list($y, $m, $d) = explode('-',$date);
                	$career_length = calcTimespan($d, $m, $y);
                }
                ?>
                <input type="text" id="career_length" name="career_length" value="<?=$career_length?>" size="20" maxlength="30" readonly="readonly" />
                <div class="br"></div>
                <span class="continue labeltextL"><strong>Напуснал на:</strong></span>
                <?php if(!$retired) { ?>
                <input type="text" id="date_retired" name="date_retired" value="<?=((isset($f['date_retired']))?$f['date_retired2']:'')?>" size="20" maxlength="10" onchange="xajax_formatDateRetired(this.value);return false;" />
                <?php } else { ?>
                <?=$f['date_retired2']?>
                <?php } ?>
                г. </td>
          </tr>
          <?php if(!$retired) { ?>
          <tr>
            <th colspan="4" class="leftSplit rightSplit"><p align="center">
                <input type="button" id="btnSubmit" name="btnSubmit" value="Съхрани" class="nicerButtons" onclick="postData();" />
              </p></th>
          </tr>
          <?php } ?>
        </table>
	<?php
	$buff = ob_get_contents();
	ob_end_clean();
	return $buff;
}
function echoReadjustment($worker_id = 0) {
	global $dbInst;
	$worker_id = intval($worker_id);
	$aCommissions = array('ТЕЛК', 'НЕЛК', 'ЛКК', 'болничен лист');
		
	ob_start();
	?>
  <table border="0" cellpadding="0" cellspacing="0" class="xlstable" width="770">
    <tr>
      <th>Дата</th>
      <th>МКБ</th>
      <th>Диагноза</th>
      <th>Комисия</th>
      <th>Срок</th>
      <th>Място на трудоустрояване</th>
      <th>&nbsp;</th>
    </tr>
    <?php
    $flds = $dbInst->query("SELECT * FROM readjustments WHERE worker_id = $worker_id ORDER BY id");
    if(!empty($flds)) {
    	foreach ($flds as $fld) {
    		?>
    <tr>
      <td><input type="text" id="published_on_<?=$fld['id']?>" name="published_on_<?=$fld['id']?>" value="<?=((!empty($fld['published_on']) && false !== $ts = strtotime($fld['published_on'])) ? date('d.m.Y', $ts) : '')?>" maxlength="10" onchange="xajax_formatBGDate(this.name,this.value);return false;" onclick="scwShow(this,event);"  class="date_input" style="width:58px;" /></td>
      <td><input type="text" id="mkb_id_<?=$fld['id']?>" name="mkb_id_<?=$fld['id']?>" value="<?=HTMLFormat($fld['mkb_id'])?>" maxlength="10" size="6" /></td>
      <td><textarea id="diagnosis_<?=$fld['id']?>" name="diagnosis_<?=$fld['id']?>" rows="2" style="width:178px;"><?=HTMLFormat($fld['diagnosis'])?></textarea></td>
      <td><select id="commission_<?=$fld['id']?>" name="commission_<?=$fld['id']?>">
          <?php foreach ($aCommissions as $commission) { ?>
          <option value="<?=HTMLFormat($commission)?>"<?=(($fld['commission'] == $commission) ? ' selected="selected"' : '')?>><?=HTMLFormat($commission)?></option>
          <?php } ?>
        </select></td>
      <td nowrap="nowrap" align="right">от
        <input type="text" id="start_date_<?=$fld['id']?>" name="start_date_<?=$fld['id']?>" value="<?=((!empty($fld['start_date']) && false !== $ts = strtotime($fld['start_date'])) ? date('d.m.Y', $ts) : '')?>" maxlength="10" onchange="xajax_formatBGDate(this.name,this.value);return false;" onclick="scwShow(this,event);"  class="date_input" style="width:57px;" />
        <br />
        до <input type="text" id="end_date_<?=$fld['id']?>" name="end_date_<?=$fld['id']?>" value="<?=((!empty($fld['end_date']) && false !== $ts = strtotime($fld['end_date'])) ? date('d.m.Y', $ts) : '')?>" maxlength="10" onchange="xajax_formatBGDate(this.name,this.value);return false;" onclick="scwShow(this,event);"  class="date_input" style="width:57px;" />
        <br /></td>
      <td><textarea id="place_<?=$fld['id']?>" name="place_<?=$fld['id']?>" rows="2" style="width:178px;"><?=HTMLFormat($fld['place'])?></textarea></td>
      <td><a title="Изтрий трудовия стаж" onclick="var answ=confirm('Наистина ли искате да изтриете трудоустрояването?'); if(answ) { xajax_removeReadjustment(<?=$fld['id']?>, <?=$worker_id?>);} return false;" href="javascript:void(null);"><img border="0" width="15" height="15" alt="Изтрий" src="img/delete.gif" /></a></td>
    </tr>
    		<?php
    	}
    }
    ?>
    <tr>
      <td class="newItem"><input type="text" id="published_on_0" name="published_on_0" value="" maxlength="10" onchange="xajax_formatBGDate(this.name,this.value);return false;" onclick="scwShow(this,event);" class="newItem date_input" style="width:58px;" /></td>
      <td><input type="text" id="mkb_id_0" name="mkb_id_0" value="" maxlength="10" size="6" class="newItem" /></td>
      <td><textarea id="diagnosis_0" name="diagnosis_0" class="newItem" rows="2" style="width:178px;"></textarea></td>
      <td><select id="commission_0" name="commission_0" class="newItem">
          <?php foreach ($aCommissions as $commission) { ?>
          <option value="<?=HTMLFormat($commission)?>"><?=HTMLFormat($commission)?></option>
          <?php } ?>
        </select></td>
      <td nowrap="nowrap" align="right">от
        <input type="text" id="start_date_0" name="start_date_0" value="" maxlength="10" onchange="xajax_formatBGDate(this.name,this.value);return false;" onclick="scwShow(this,event);" class="newItem date_input" style="width:57px;" />
        <br />
        до
        <input type="text" id="end_date_0" name="end_date_0" value="" maxlength="10" onchange="xajax_formatBGDate(this.name,this.value);return false;" onclick="scwShow(this,event);" class="newItem date_input" style="width:57px;" />
        <br /></td>
      <td><textarea id="place_0" name="place_0" rows="2" style="width:178px;" class="newItem"></textarea></td>
      <td>&nbsp;</td>
    </tr>
  </table>
  <div class="hr"></div>
  <div align="center">
    <input type="button" id="btnSubmit" name="btnSubmit" value="Съхрани" class="nicerButtons" onclick="this.value='обработка...';this.disabled=true;xajax_processReadjustment(xajax.getFormValues('frmFirm'));DisableEnableForm(true);return false;" />
  </div>
	<?php
	return ob_get_clean();
}


$tab = (isset($_GET['tab'])) ? $_GET['tab'] : 'worker_data';

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?=SITE_NAME?></title>
<link href="styles.css" rel="stylesheet" type="text/css" media="screen" />
<?=$echoJS?>
<script type="text/javascript" src="js/RegExpValidate.js"></script>
<script type="text/javascript" src="scw.js"></script>
<!-- http://jquery.com/demo/thickbox/ -->
<script type="text/javascript" src="js/jquery-latest.pack.js"></script>
<script type="text/javascript" src="js/thickbox/thickbox.js"></script>
<link rel="stylesheet" href="js/thickbox/thickbox.css" type="text/css" media="screen" />

<!-- http://coffeescripter.com/code/editable-select/ -->
<link rel="stylesheet" type="text/css" href="js/jquery.editable-select.1.3.2/jquery.editable-select.css" />
<script type="text/javascript" src="js/jquery.editable-select.1.3.2/jquery.editable-select.pack.js"></script>
<script type="text/javascript">
//<![CDATA[
if(window.jQuery) {
	(function($) {
		$(function() {
			$('.editable-select:not(:disabled)').editableSelect(
				{
					//bg_iframe: true,
					onSelect: function(list_item) {
						// alert('List item text: '+ list_item.text());
						// 'this' is a reference to the instance of EditableSelect
						// object, so you have full access to everything there
						// alert('Input value: '+ this.text.val());
					},
					case_sensitive: false, // If set to true, the user has to type in an exact
					// match for the item to get highlighted
					items_then_scroll: 10 // If there are more than 10 items, display a scrollbar
				}
			);
		});
	})(jQuery);
}
//]]>
</script>
<style type="text/css">
.editable-select {
	width:400px;	
}
.editable-select-options li {
	font-size: 12px;
}
</style>

<script type="text/javascript" charset="utf-8">
//<![CDATA[
$(document).ready(function() {
	stripTable('listtable');
	if(parent.$("#cboxClose")[0]) {
		// Reload the parent window when the close button of Colorbox popup is clicked!
		parent.$("#cboxClose")[0].onclick = function() {
			if($('input#form_is_dirty').val() != '0') {
				parent.location.reload();
			}
		}
	}
	$("a.tab").click(function(e){
		e.preventDefault();
		var tab = $(this).attr("rel");
		window.location = '<?=$_SERVER['PHP_SELF']?>?firm_id=<?=$firm_id?>&worker_id=' + $("input[name='worker_id']").val() + '&tab=' + tab + '&<?=SESS_NAME.'='.session_id()?>';
	});
});
function stripTable(tableid) {
	// Strip table
	$("#"+tableid+" tr:even").addClass("alternate");
	// Hightlight table rows
	$("#"+tableid+" tr").hover(function() {
		$(this).addClass("over");
	},function() {
		$(this).removeClass("over");
	});
}
function postData() {
	var fname = $.trim($('#fname').val());
	var lname = $.trim($('#lname').val());
	if(fname == '' && lname == '') {
		alert('Моля, въведете име или фамилия на работещия.');
		$('#fname').focus();
		return false;
	}
	var egn = $.trim($('#egn').val());
	if(egn == '') {
		alert('Моля, въведете ЕГН на работещия.');
		$('#egn').focus();
		return false;
	}
	if(egn.length != 10) {
		if(!confirm('Сигурни ли сте, че въведеният ЕГН е правилен?')) {
			return false;
		}
	}
	xajax_processWorker(xajax.getFormValues('frmFirm'));
	DisableEnableForm(true);
	return false;
}
//]]>
</script>
<!-- Auto-completer includes begin -->
<!-- http://dev.jquery.com/view/trunk/plugins/autocomplete/ -->
<!-- <script type="text/javascript" src="js/autocompleter/jquery.js"></script> -->
<script type='text/javascript' src='js/autocompleter/jquery.bgiframe.min.js'></script>
<!--<script type="text/javascript" src="http://dev.jquery.com/view/trunk/plugins/autocomplete/lib/jquery.bgiframe.min.js"></script>-->
<script type='text/javascript' src='js/autocompleter/jquery.dimensions.js'></script>
<script type='text/javascript' src='js/autocompleter/jquery.ajaxQueue.js'></script>
<script type='text/javascript' src='js/autocompleter/jquery.autocomplete.js'></script>
<script type='text/javascript' src='js/autocompleter/localdata.js'></script>
<!-- <link rel="stylesheet" type="text/css" href="js/autocompleter/main.css" /> -->
<link rel="stylesheet" type="text/css" href="js/autocompleter/jquery.autocomplete.css" />
<!-- Auto-completer includes end -->
<script type="text/javascript">
//<![CDATA[
// Auto-completer begin
$(document).ready(function() {

	function findValueCallback(event, data, formatted) {
		$("<li>").html( !data ? "No match!" : "Selected: " + formatted).appendTo("#result");
	}
	function formatItem(row) {
		return row[0] + " (<strong>id: " + row[1] + "<\/strong>)";
	}
	function formatResult(row) {
		return row[0].replace(/(<.+?>)/gi, '');
	}
	$("input[type='text'], textarea").result(findValueCallback).next().click(function() {
		$(this).prev().search();
	});

	$("#location_name").autocomplete("autocompleter.php", {
		minChars: 1,
		extraParams: { search: "locations" },
		width: 260,
		scroll: true,
		scrollHeight: 300,
		selectFirst: false
	});
	$("#location_name").result(function(event, data, formatted) {
		if (data) $("#location_id").val(data[1]);
	});
	
	mkbAutocomplete();
});
// Auto-completer end
function ucfirst(el) {
	if(el.value != "") {
		var str = el.value;
		el.value = str.charAt(0).toUpperCase() + str.slice(1);
	}
}
function mkbAutocomplete() {
	$("input[name^='mkb_id_']").autocomplete("autocompleter.php", {
		minChars: 1,
		extraParams: { search: "mkb" },
		width: 600,
		/*max: 4,*/
		/*highlight: false,*/
		scroll: true,
		scrollHeight: 250,
		selectFirst: false,
		formatItem: function(data, i, n, value) {
			var mkb_id = data[0];
			var mkb_desc = data[1];
			var mkb_code = data[2];
			return "<table border='0' cellpadding='0' cellspacing='0'><tr><td width='50'>"+mkb_id+"<\/td><td width='500'>"+mkb_desc+"<\/td><td width='50'>"+mkb_code+"<\/td><\/tr><\/table>";
		}
	});
	$("input[name^='mkb_id_']").result(function(event, data, formatted) {
		if (data) {
			var id = this.name.slice(7);
			$("#diagnosis_"+id).html(data[1]);
		}
	});
}
//]]>
</script>
<style type="text/css">
body,html {
	background-image:none;
	background-color:#EEEEEE;
}
</style>
</head>
<body style="overflow:hidden;">
<div id="contentinner" align="center">
  <form id="frmFirm" action="javascript:void(null);">
    <input type="hidden" id="form_is_dirty" name="form_is_dirty" value="0" />
    <input type="hidden" id="worker_id" name="worker_id" value="<?=$worker_id?>" />
    <input type="hidden" id="firm_id" name="firm_id" value="<?=$firm_id?>" />
    <?php if(!empty($worker_id)) { echo getPopupNavigation('Редактиране на данните'); } ?>
    <div align="center" style="width:790px;">
      <div id="lastModified" class="lastModified"><?php if($worker_id) { echo $dbInst->getModifiedBy('workers', 'worker_id', $worker_id); } else { echo '<br />'; } ?></div>
      <div id="tabs"> <a href="#" class="tab<?=(($tab=='worker_data')?' active':'')?>" rel="worker_data">Данни за работещия </a> <a href="#" class="tab<?=(($tab=='pro_route')?' active':'')?>" rel="pro_route">Професионален маршрут</a> <a href="#" class="tab<?=(($tab=='readjustment')?' active':'')?>" rel="readjustment">Трудоустрояване</a></div>
      <script type="text/javascript">if ( (jQuery.browser.msie && jQuery.browser.version < 7)) { document.write('<br clear="all" \/>'); }</script>
      <div id="panel" class="panel" style="display:block;<?=(('worker_data'==$tab)?'overflow:hidden;':'')?>">

      <?php
      switch ($tab) {
      	case 'pro_route':
      		echo echoProRoute($worker_id);
      		break;
      	case 'readjustment':
      		echo echoReadjustment($worker_id);
      		break;
      	case 'worker_data':
      	default:
      		echo echoWorkerData($worker_id, $firmInfo);
      		break;
      }
      ?>

      </div>
    </div>
  </form>
</div>
</body>
</html>
