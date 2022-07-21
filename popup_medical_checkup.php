<?php
require('includes.php');

$firm_id = (isset($_GET['firm_id']) && is_numeric($_GET['firm_id'])) ? intval($_GET['firm_id']) : 0;
$firmInfo = $dbInst->getFirmInfo($firm_id);
/*if(!$firmInfo) {
die('Липсва индентификатор на фирмата!');
}*/
$worker_id = (isset($_GET['worker_id']) && is_numeric($_GET['worker_id'])) ? intval($_GET['worker_id']) : 0;
$w = $dbInst->getWorkerInfo($worker_id);
/*if(!$f) {
die('Липсва индентификатор на работещия!');
}*/
$checkup_id = 0;
if((isset($_GET['checkup_id']) && $_GET['checkup_id'] != '' && is_numeric($_GET['checkup_id']))) {
	$checkup_id = intval($_GET['checkup_id']);
} else {
	$db = $dbInst->getDBHandle();
	$query = sprintf("SELECT checkup_id FROM medical_checkups WHERE worker_id = %d ORDER BY checkup_date DESC LIMIT 1", $worker_id);
	$prepstatement = $db->prepare($query);
	if (!$prepstatement) {
		$err = $db->errorInfo();
		die('Грешка при изпълнение на заявка към базата данни: '.$err[2]);
	}
	$prepstatement->execute();
	$row = $prepstatement->fetch(PDO::FETCH_ASSOC);
	$checkup_id = ($row['checkup_id']) ? $checkup_id = $row['checkup_id'] : 0;
}

$tab = (isset($_GET['tab']) && $_GET['tab'] != '') ? trim($_GET['tab']) : 'exam1';

// Xajax begin
require ('xajax/xajax_core/xajax.inc.php');
function loadPatientInfo($worker_id) {
	$objResponse = new xajaxResponse();
	$objResponse->call("clearForm", "frmCheckup");
	global $dbInst;
	$w = $dbInst->getWorkerInfo($worker_id);
	$objResponse->assign("firm_id", "value", $w['firm_id']);
	$objResponse->assign("checkup_id", "value", 0);
	$objResponse->assign("worker_id", "value", $worker_id);
	$objResponse->assign("wname", "value", HTMLFormat($w['fname'].' '.$w['sname'].' '.$w['lname']));
	$objResponse->assign("egn", "value", $w['egn']);
	$objResponse->assign("age2", "value", worker_age($w['birth_date2'], date("d.m.Y")).' г.');
	$objResponse->assign("sex", "value", $w['sex']);
	$objResponse->assign("subdivision_name", "innerHTML", HTMLFormat($w['s.subdivision_name']).' &nbsp;');
	$objResponse->assign("wplace_name", "innerHTML", HTMLFormat($w['wplace_name']).' &nbsp;');
	$objResponse->assign("position_name", "innerHTML", HTMLFormat($w['position_name']).' &nbsp;');
	$objResponse->assign("date_curr_position_start2", "value", $w['date_curr_position_start2']);
	$objResponse->assign("date_curr_position_start2", "value", $w['date_curr_position_start2']);
	$objResponse->assign("checkupWrapper", "innerHTML", checkupListOptions($worker_id, $w['firm_id'], 0));

	return $objResponse;
}
function removeMedicalCheckup($lab_checkup_id, $checkup_id) {
	$objResponse = new xajaxResponse();
	if($_SESSION['sess_user_level'] == 1) { /* admin rights only */
		global $dbInst;
		$count = $dbInst->removeMedicalCheckup($lab_checkup_id);
		$objResponse->assign("labCheckupsWrapper", "innerHTML", loadLabCheckups($checkup_id));
	}
	return $objResponse;
}
function removeDiagnosis($disease_id, $checkup_id) {
	$objResponse = new xajaxResponse();

	if($_SESSION['sess_user_level'] == 1) { /* admin rights only */
		global $dbInst;
		$count = $dbInst->removeDiagnosis($disease_id);
		$objResponse->assign("diagnosisWrapper", "innerHTML", loadDiagnosis($checkup_id));
		$objResponse->script("mkbAutocomplete();");
	}

	return $objResponse;
}
function removeFamilyWeight($family_weight_id, $checkup_id) {
	$objResponse = new xajaxResponse();

	if($_SESSION['sess_user_level'] == 1) { /* admin rights only */
		global $dbInst;
		$count = $dbInst->removeFamilyWeight($family_weight_id);
		$objResponse->assign("weightsWrapper", "innerHTML", loadFamilyWeights($checkup_id));
		$objResponse->script("mkbAutocomplete();");
	}

	return $objResponse;
}
function removeAnamnesis($anamnesis_id, $checkup_id) {
	$objResponse = new xajaxResponse();

	if($_SESSION['sess_user_level'] == 1) { /* admin rights only */
		global $dbInst;
		$count = $dbInst->removeAnamnesis($anamnesis_id);
		$objResponse->assign("anamnesisWrapper", "innerHTML", loadAnamnesis($checkup_id));
		$objResponse->script("mkbAutocomplete();");
	}

	return $objResponse;
}
function removeConclusion($checkup_id = 0, $SpecialistID = 0) {
	$objResponse = new xajaxResponse();
	global $dbInst;
	$dbInst->query("DELETE FROM medical_checkups_doctors2 WHERE checkup_id = $checkup_id AND SpecialistID = $SpecialistID");
	$objResponse->assign("panel", "innerHTML", echoConclusion($checkup_id));
	return $objResponse;
}
function processMedicalCheckup($aFormValues, $tab='exam1') {
	$objResponse = new xajaxResponse();

	$objResponse->assign("btnSubmit","disabled",false);
	$objResponse->assign("btnSubmit","value","Съхрани");
	$objResponse->call("DisableEnableForm",false);

	global $dbInst;
	$worker_id = intval($aFormValues['worker_id']);
	if(!$worker_id) {
		$objResponse->alert('Моля, изберете работещ по ЕГН или име.');
		if($tab != 'exam1')
		$objResponse->script("window.location.href='".basename($_SERVER['PHP_SELF'])."?worker_id=".intval($aFormValues['worker_id'])."&firm_id=".intval($aFormValues['firm_id'])."';");
		return $objResponse;
	}
	$checkup_id = intval($aFormValues['checkup_id']);
	$isNewCheckup = (!empty($aFormValues['checkup_id'])) ? 0 : 1;

	$oldCheckupDate = $dbInst->getOldCheckupDate($checkup_id);

	$d = new ParseBGDate();
	$checkup_date = trim($aFormValues['checkup_date']);
	/*if($checkup_date == '') {
	$objResponse->alert('Моля, въведете дата на медицинския преглед.');
	if($tab != 'exam1')
	$objResponse->script("window.location.href='".basename($_SERVER['PHP_SELF'])."?worker_id=".intval($aFormValues['worker_id'])."&firm_id=".intval($aFormValues['firm_id'])."';");
	return $objResponse;
	}*/
	if(!empty($checkup_date) && !$d->Parse($checkup_date)) {
		$objResponse->alert($checkup_date . ' е невалидна дата!');
		//$objResponse->script("window.location.href='".basename($_SERVER['PHP_SELF'])."?worker_id=".intval($aFormValues['worker_id'])."&firm_id=".intval($aFormValues['firm_id'])."';");
		return $objResponse;
	}

	if($tab == 'fweighs' || $tab == 'diagnosis') {
		foreach ($aFormValues as $key=>$val) {
			if(preg_match('/^mkb_id_(\d+)$/', $key, $matches)) {
				if('mkb_id_0' != $key && !$dbInst->isValidMkb($val)) {
					$objResponse->alert($val.' е невалидна стойност!');
					return $objResponse;
				} elseif ('mkb_id_0' == $key && '' != $val && !$dbInst->isValidMkb($val)) {
					$objResponse->alert($val.' е невалидна стойност!');
					return $objResponse;
				}
			}
		}
	}
	elseif ($tab == 'conclusion_stm') {
		$stm_date = trim($aFormValues['stm_date']);
		if($stm_date == '') {
			$objResponse->alert('Моля, въведете дата на изготвяне на заключението на СТМ.');
			return $objResponse;
		}
		if(!$d->Parse($stm_date)) {
			$objResponse->alert($stm_date . ' е невалидна дата!');
			return $objResponse;
		}
		// Check dates: Compare `$checkup_date` and `$stm_date` dates
		$d->Parse($checkup_date);
		$checkup_date = mktime(0, 0, 0, $d->getMonth(), $d->getDay(), $d->getYear());
		$d->Parse($stm_date);
		$stm_date = mktime(0, 0, 0, $d->getMonth(), $d->getDay(), $d->getYear());
		if($checkup_date > $stm_date) {
			$objResponse->alert('Датата на изготвяне на заключението на СТМ не може да бъде преди датата на профилактичния преглед!');
			return $objResponse;
		}
		if($aFormValues['stm_conclusion'] == '') {
			$objResponse->assign('noPrintConclusion', 'style.display', '');
			$objResponse->assign('printConclusion', 'style.display', 'none');
		} else {
			$objResponse->assign('noPrintConclusion', 'style.display', 'none');
			$objResponse->assign('printConclusion', 'style.display', '');
		}
	}
	elseif ($tab == 'conclusion') {
		$SpecialistID = (isset($aFormValues['SpecialistID'])) ? intval($aFormValues['SpecialistID']) : 0;
		if(!empty($SpecialistID)) {
			$conclusion = (isset($aFormValues['conclusion'])) ? trim($aFormValues['conclusion']) : '';
			if(empty($conclusion)) {
				$objResponse->alert('Моля, въведете име и заключение на специалиста.');
				return $objResponse;
			}
		}
	}
	$checkup_id = $dbInst->processMedicalCheckup($aFormValues, $tab); // Update a medical checkup
	if(!empty($checkup_id) && isset($aFormValues['anamnesis_descr'])) {
		$anamnesis_descr = $dbInst->checkStr($aFormValues['anamnesis_descr']);
		$sql = "UPDATE `medical_checkups` SET `anamnesis_descr` = '$anamnesis_descr' WHERE `checkup_id` = $checkup_id";
		$dbInst->query($sql);
	}
	//$objResponse->call("clearForm", "frmCheckup");
	$w = $dbInst->getWorkerInfo($worker_id);
	$objResponse->assign("firm_id", "value", $w['firm_id']);
	$objResponse->assign("checkup_id", "value", $checkup_id);
	$objResponse->assign("worker_id", "value", $worker_id);
	$objResponse->assign("wname", "value", HTMLFormat($w['fname'].' '.$w['sname'].' '.$w['lname']));
	$objResponse->assign("egn", "value", $w['egn']);
	//$objResponse->assign("checkup_date", "value", "");

	if('exam1' == $tab && $isNewCheckup) {
		$sql = "SELECT COUNT(*) AS `cnt` FROM `medical_checkups` WHERE `worker_id` = $worker_id AND checkup_date != ''";
		$row = $dbInst->fnSelectSingleRow($sql);
		if(!empty($row)) {
			$objResponse->script('if(parent.$("#w_checkups_num_'.$worker_id.'")[0]){parent.$("#w_checkups_num_'.$worker_id.'").html("'.HTMLFormat($row['cnt']).'")}');
		}
	}

	if($tab == 'exam1' && (!$aFormValues['checkup_id'] || $oldCheckupDate == '')) {
		$objResponse->script("window.location.href='".basename($_SERVER['PHP_SELF'])."?checkup_id=$checkup_id&worker_id=".intval($aFormValues['worker_id'])."&firm_id=".intval($aFormValues['firm_id'])."'");
	}
	elseif($tab == 'exam2') {
		$objResponse->assign("checkupWrapper", "innerHTML", checkupListOptions($worker_id, $w['firm_id'], $checkup_id));
	}
	elseif($tab == 'fweighs') {
		$objResponse->assign("weightsWrapper", "innerHTML", loadFamilyWeights($checkup_id));
		$objResponse->script("mkbAutocomplete()");
	}
	elseif($tab == 'anamnesis') {
		$objResponse->assign("anamnesisWrapper", "innerHTML", loadAnamnesis($checkup_id));
		$objResponse->script("mkbAutocomplete()");
	}
	elseif($tab == 'checkups') {
		$objResponse->assign("labCheckupsWrapper", "innerHTML", loadLabCheckups($checkup_id));
	}
	elseif($tab == 'diagnosis') {
		$objResponse->assign("diagnosisWrapper", "innerHTML", loadDiagnosis($checkup_id));
		$objResponse->script("mkbAutocomplete()");
	}
	elseif ($tab == 'conclusion') {
		$objResponse->assign("panel", "innerHTML", echoConclusion($checkup_id));
	}

	return $objResponse;
}
function populateMedicalCheckup($indicator_id, $suff='0') {
	$objResponse = new xajaxResponse();

	global $dbInst;
	$f = $dbInst->getLabInfo($indicator_id);
	if(!$f) {
		$objResponse->assign("indicator_dimension_$suff", "value", "");
		$objResponse->assign("pdk_min_$suff", "value", "");
		$objResponse->assign("pdk_max_$suff", "value", "");
	}
	else {
		$objResponse->assign("indicator_dimension_$suff", "value", HTMLFormat($f['indicator_dimension']));
		$objResponse->assign("pdk_min_$suff", "value", HTMLFormat($f['pdk_min']));
		$objResponse->assign("pdk_max_$suff", "value", HTMLFormat($f['pdk_max']));
	}
	return $objResponse;
}
// Delete the checkup
function deleteCheckup($checkup_id = 0, $worker_id = 0, $firm_id = 0) {
	$objResponse = new xajaxResponse();

	if($_SESSION['sess_user_level'] == 1) { /* admin rights only */
		global $dbInst;
		$dbInst->query(sprintf("DELETE FROM medical_checkups WHERE checkup_id = %d", $checkup_id));
		$dbInst->query(sprintf("DELETE FROM medical_checkups_doctors2 WHERE checkup_id = %d", $checkup_id));

		$sql = "SELECT COUNT(*) AS `cnt` FROM `medical_checkups` WHERE `worker_id` = $worker_id AND checkup_date != ''";
		$row = $dbInst->fnSelectSingleRow($sql);
		if(!empty($row)) {
			$objResponse->script('if(parent.$("#w_checkups_num_'.$worker_id.'")[0]){parent.$("#w_checkups_num_'.$worker_id.'").html("'.HTMLFormat($row['cnt']).'")}');
		}
		$objResponse->script('window.location="'.basename($_SERVER['PHP_SELF']).'?worker_id='.$worker_id.'&firm_id='.$firm_id.'&'.SESS_NAME.'='.session_id().'"');
	}
	return $objResponse;
}
$xajax = new xajax();
$xajax->registerFunction("loadPatientInfo");
$xajax->registerFunction("removeMedicalCheckup");
$xajax->registerFunction("removeDiagnosis");
$xajax->registerFunction("removeFamilyWeight");
$xajax->registerFunction("removeAnamnesis");
$xajax->registerFunction("removeConclusion");
$xajax->registerFunction("processMedicalCheckup");
$xajax->registerFunction("formatBGDate");
$xajax->registerFunction("populateMedicalCheckup");
$xajax->registerFunction("deleteCheckup");
//$xajax->setFlag("debug",true);
$echoJS = $xajax->getJavascript('xajax/');
$xajax->processRequest();
// Xajax end

function checkupListOptions($worker_id, $firm_id, $checkup_id) {
	global $dbInst;
	$retStr  = '<select id="checkup_id" name="checkup_id" onchange="window.location=\''.basename($_SERVER['PHP_SELF']).'?worker_id='.$worker_id.'&firm_id='.$firm_id.'&checkup_id=\'+this.value+\'&'.SESS_NAME.'='.session_id().'\'">';
	$rows = $dbInst->getMedicalCheckupList($worker_id); // patient medical checkups list
	$numCards = count($rows);
	$retStr .= '<option value="0"> - НОВА КАРТА - &nbsp;&nbsp;</option>';
	foreach ($rows as $row) {
		$retStr .= '<option value="'.$row['checkup_id'].'"'.(($checkup_id==$row['checkup_id'])?' selected="selected"':'').'>Карта '.($numCards--).(($row['checkup_date']=='')?'* (няма предоставени данни)':'').' &nbsp;&nbsp;</option>';
	}
	$retStr .= '</select>';
	return $retStr;
}
function loadFamilyWeights($checkup_id) {
	global $dbInst;
	ob_start();
	$rows = $dbInst->getFamilyWeights($checkup_id);
	$fweights_descr = $dbInst->GiveValue('fweights_descr', 'medical_checkups', "WHERE `checkup_id` = $checkup_id", 0);
	if(empty($fweights_descr)) $fweights_descr = '';
	?>
	<table cellpadding="0" cellspacing="0">
	  <tr class="underline">
	    <td align="left"><strong>Описание</strong></td>
	    <td align="left"><textarea id="fweights_descr" name="fweights_descr" rows="2" cols="104"><?=HTMLFormat($fweights_descr)?></textarea></td>
	    <td align="left">&nbsp;</td>
	  </tr>	
	  <?php foreach ($rows as $row) { ?>
	  <tr>
	    <td class="primary"><a href="#" id="mkb_code_<?=$row['family_weight_id']?>" onclick="openMkbNomenclature(this);">МКБ <img src="img/moreinfo.gif" alt="info" border="0" width="17" height="17" /></a></td>
	    <td><input type="text" id="mkb_id_<?=$row['family_weight_id']?>" name="mkb_id_<?=$row['family_weight_id']?>" size="10" maxlength="50" value="<?=HTMLFormat($row['mkb_id'])?>" />
	      <span id="mkb_desc_<?=$row['family_weight_id']?>">
	      <?=HTMLFormat($row['mkb_desc'])?>
	      </span></td>
	    <td><?php if($_SESSION['sess_user_level'] == 1) { /* admin rights only */ ?>
	      <a href="javascript:void(null);" onclick="var answ=confirm('Наистина ли искате да изтриете фамилната обремененост?'); if(answ) { xajax_removeFamilyWeight(<?=$row['family_weight_id']?>, <?=$checkup_id?>); } return false;" title="Изтрий фамилната обремененост"><img src="img/delete.gif" width="15" height="15" border="0" alt="Изтрий" /></a>
	      <?php } ?>
	      &nbsp;</td>
	  </tr>
	  <tr class="underline">
	    <td><strong>Диагноза</strong></td>
	    <td><textarea id="diagnosis_<?=$row['family_weight_id']?>" name="diagnosis_<?=$row['family_weight_id']?>" rows="2" cols="104"><?=HTMLFormat($row['diagnosis'])?></textarea></td>
	    <td>&nbsp;</td>
	  </tr>
	  <?php } ?>
	  <tr>
	    <td class="primary"><a href="#" id="mkb_code_0" onclick="openMkbNomenclature(this);">МКБ <img src="img/moreinfo.gif" alt="info" border="0" width="17" height="17" /></a></td>
	    <td><input type="text" id="mkb_id_0" name="mkb_id_0" size="10" maxlength="50" value="" class="newItem" />
	      <span id="mkb_desc_0"></span></td>
	    <td>&nbsp;</td>
	  </tr>
	  <tr>
	    <td><strong>Диагноза</strong></td>
	    <td><textarea id="diagnosis_0" name="diagnosis_0" rows="2" cols="104" class="newItem"></textarea></td>
	    <td>&nbsp;</td>
	  </tr>
	</table>
	<?php
	$buff = ob_get_contents();
	ob_end_clean();
	return $buff;
}
function loadAnamnesis($checkup_id = 0) {
	global $dbInst;
	ob_start();
	//$rows = $dbInst->getFamilyWeights($checkup_id);
	$rows = $dbInst->getAnamnesis($checkup_id);
	$anamnesis_descr = $dbInst->GiveValue('anamnesis_descr', 'medical_checkups', "WHERE `checkup_id` = $checkup_id", 0);
	if(empty($anamnesis_descr)) $anamnesis_descr = '';
	?>
	<table cellpadding="0" cellspacing="0">
	  <tr class="underline">
	    <td align="left"><strong>Описание</strong></td>
	    <td align="left"><textarea id="anamnesis_descr" name="anamnesis_descr" rows="2" cols="104"><?=HTMLFormat($anamnesis_descr)?></textarea></td>
	    <td align="left">&nbsp;</td>
	  </tr>
	  <?php foreach ($rows as $row) { ?>
	  <tr>
	    <td align="left" class="primary"><a href="#" id="mkb_code_<?=$row['anamnesis_id']?>" onclick="openMkbNomenclature(this);">МКБ <img src="img/moreinfo.gif" alt="info" border="0" width="17" height="17" /></a></td>
	    <td align="left"><input type="text" id="mkb_id_<?=$row['anamnesis_id']?>" name="mkb_id_<?=$row['anamnesis_id']?>" size="10" maxlength="50" value="<?=HTMLFormat($row['mkb_id'])?>" />
	      <span id="mkb_desc_<?=$row['anamnesis_id']?>"><?=HTMLFormat($row['mkb_desc'])?></span></td>
	    <td align="left">
	      <a href="javascript:void(null);" onclick="var answ=confirm('Наистина ли искате да изтриете анамнезата?'); if(answ) { xajax_removeAnamnesis(<?=$row['anamnesis_id']?>, <?=$checkup_id?>); } return false;" title="Изтрий анамнезата"><img src="img/delete.gif" width="15" height="15" border="0" alt="Изтрий" /></a>
	      &nbsp;</td>
	  </tr>
	  <tr class="underline">
	    <td align="left"><strong>Анамнеза</strong></td>
	    <td align="left"><textarea id="diagnosis_<?=$row['anamnesis_id']?>" name="diagnosis_<?=$row['anamnesis_id']?>" rows="2" cols="104"><?=HTMLFormat($row['diagnosis'])?></textarea></td>
	    <td align="left">&nbsp;</td>
	  </tr>
	  <?php } ?>
	  <tr>
	    <td align="left" class="primary"><a href="#" id="mkb_code_0" onclick="openMkbNomenclature(this);">МКБ <img src="img/moreinfo.gif" alt="info" border="0" width="17" height="17" /></a></td>
	    <td align="left"><input type="text" id="mkb_id_0" name="mkb_id_0" size="10" maxlength="50" value="" class="newItem" />
	      <span id="mkb_desc_0"></span></td>
	    <td align="left">&nbsp;</td>
	  </tr>
	  <tr>
	    <td align="left"><strong>Анамнеза</strong></td>
	    <td align="left"><textarea id="diagnosis_0" name="diagnosis_0" rows="2" cols="104" class="newItem"></textarea></td>
	    <td align="left">&nbsp;</td>
	  </tr>
	</table>
	<?php
	$buff = ob_get_contents();
	ob_end_clean();
	return $buff;
}
function loadLabCheckups($checkup_id) {
	global $dbInst;
	ob_start();
	$rows = $dbInst->getLabCheckups($checkup_id);
	$labs = $dbInst->getLabs();
	?>
	<table cellpadding="0" cellspacing="0">
	  <tr>
	    <td><strong>Вид</strong></td>
	    <td><strong>Показател</strong></td>
	    <td><strong>Ниво</strong></td>
	    <td><strong>МЕ</strong></td>
	    <td><strong>Min</strong></td>
	    <td><strong>Max</strong></td>
	    <td><strong>Откл.</strong></td>
	    <td>&nbsp;</td>
	  </tr>
	  <?php foreach ($rows as $row) { ?>
	  <tr>
	    <td><input type="text" id="checkup_type_<?=$row['lab_checkup_id']?>" name="checkup_type_<?=$row['lab_checkup_id']?>" size="10" maxlength="50" value="<?=HTMLFormat($row['checkup_type'])?>" /></td>
	    <td><select id="indicator_id_<?=$row['lab_checkup_id']?>" name="indicator_id_<?=$row['lab_checkup_id']?>" onchange="xajax_populateMedicalCheckup(this.value,'<?=$row['lab_checkup_id']?>');return false;">
	        <?php
	        foreach ($labs as $lab) {
	        	echo '<option value="'.$lab['indicator_id'].'"'.(($row['indicator_id']==$lab['indicator_id'])?' selected="selected"':'').'>'.HTMLFormat($lab['indicator_type']).((!empty($lab['indicator_name'])) ? ' ('.HTMLFormat($lab['indicator_name']).')' : '').' &nbsp;&nbsp;</option>';
	        }
	        ?>
	      </select></td>
	    <td><input type="text" id="checkup_level_<?=$row['lab_checkup_id']?>" name="checkup_level_<?=$row['lab_checkup_id']?>" size="10" maxlength="50" value="<?=HTMLFormat($row['checkup_level'])?>" /></td>
	    <td><input type="text" id="indicator_dimension_<?=$row['lab_checkup_id']?>" name="indicator_dimension_<?=$row['lab_checkup_id']?>" size="10" maxlength="50" value="<?=HTMLFormat($row['indicator_dimension'])?>" readonly="readonly" /></td>
	    <td><input type="text" id="pdk_min_<?=$row['lab_checkup_id']?>" name="pdk_min_<?=$row['lab_checkup_id']?>" size="10" maxlength="50" value="<?=HTMLFormat($row['pdk_min'])?>" readonly="readonly" /></td>
	    <td><input type="text" id="pdk_max_<?=$row['lab_checkup_id']?>" name="pdk_max_<?=$row['lab_checkup_id']?>" size="10" maxlength="50" value="<?=HTMLFormat($row['pdk_max'])?>" readonly="readonly" /></td>
	    <td><?=calcDeviation($row['pdk_min'], $row['pdk_max'], $row['checkup_level'])?></td>
	    <td><a href="javascript:void(null);" onclick="var answ=confirm('Наистина ли искате да изтриете изследването?');if(answ){xajax_removeMedicalCheckup(<?=$row['lab_checkup_id']?>,<?=$checkup_id?>);}return false;" title="Изтриване на изследването"><img src="img/delete.gif" border="0" width="15" height="15" alt="Изтриване на изследването" /></a></td>
	  </tr>
	  <?php } ?>
	  <tr>
	    <td><input type="text" id="checkup_type_0" name="checkup_type_0" size="10" maxlength="50" value="" class="newItem" /></td>
	    <td><select id="indicator_id_0" name="indicator_id_0" class="newItem" onchange="xajax_populateMedicalCheckup(this.value,'0');return false;">
	        <option value=""> &nbsp;&nbsp;</option>
	        <?php
	        foreach ($labs as $lab) {
	        	echo '<option value="'.$lab['indicator_id'].'">'.HTMLFormat($lab['indicator_type']).' ('.HTMLFormat($lab['indicator_name']).') &nbsp;&nbsp;</option>';
	        }
	        ?>
	      </select></td>
	    <td><input type="text" id="checkup_level_0" name="checkup_level_0" size="10" maxlength="50" value="" class="newItem" /></td>
	    <td><input type="text" id="indicator_dimension_0" name="indicator_dimension_0" size="10" maxlength="50" value="" class="newItem" readonly="readonly" /></td>
	    <td><input type="text" id="pdk_min_0" name="pdk_min_0" size="10" maxlength="50" value="" class="newItem" readonly="readonly" /></td>
	    <td><input type="text" id="pdk_max_0" name="pdk_max_0" size="10" maxlength="50" value="" class="newItem" readonly="readonly" /></td>
	    <td>&nbsp;</td>
	    <td>&nbsp;</td>
	  </tr>
	</table>
	<?php
	$buff = ob_get_contents();
	ob_end_clean();
	return $buff;
}
function loadDiagnosis($checkup_id) {
	global $dbInst;
	ob_start();
	$rows = $dbInst->getDiseases($checkup_id);
	?>
	<table cellpadding="0" cellspacing="0">
	  <?php foreach ($rows as $row) { ?>
	  <tr>
	    <td align="left" class="primary"><a href="#" id="mkb_code_<?=$row['disease_id']?>" onclick="openMkbNomenclature(this);">МКБ <img src="img/moreinfo.gif" alt="info" border="0" width="17" height="17" /></a></td>
	    <td align="left"  colspan="2"><input type="text" id="mkb_id_<?=$row['disease_id']?>" name="mkb_id_<?=$row['disease_id']?>" size="10" maxlength="50" value="<?=HTMLFormat($row['mkb_id'])?>" />
	      <span id="mkb_desc_<?=$row['disease_id']?>"><?=HTMLFormat($row['mkb_desc'])?></span></td>
	    <td align="left"><?php if($_SESSION['sess_user_level'] == 1) { /* admin rights only */ ?>
	      <a href="javascript:void(null);" onclick="var answ=confirm('Наистина ли искате да изтриете заболяването?'); if(answ) { xajax_removeDiagnosis(<?=$row['disease_id']?>, <?=$checkup_id?>); } return false;" title="Изтрий заболяването"><img src="img/delete.gif" width="15" height="15" border="0" alt="Изтрий" /></a>
	      <?php } ?>
	      &nbsp;</td>
	  </tr>
	  <tr class="underline">
	    <td align="left"><strong>Диагноза</strong></td>
	    <td align="left"><input type="text" id="diagnosis_<?=$row['disease_id']?>" name="diagnosis_<?=$row['disease_id']?>" size="80" maxlength="100" value="<?=HTMLFormat($row['diagnosis'])?>" /></td>
	    <td align="left"><div align="right">
	        <input type="checkbox" id="is_new_<?=$row['disease_id']?>" name="is_new_<?=$row['disease_id']?>" value="1"<?=(($row['is_new']=='1')?' checked="checked"':'')?> />
	        Новооткрито </div></td>
	    <td align="left">&nbsp;</td>
	  </tr>
	  <?php } ?>
	  <tr>
	    <td align="left" class="primary"><a href="#" id="mkb_code_0" onclick="openMkbNomenclature(this);">МКБ <img src="img/moreinfo.gif" alt="info" border="0" width="17" height="17" /></a></td>
	    <td align="left" colspan="2"><input type="text" id="mkb_id_0" name="mkb_id_0" size="10" maxlength="50" value="" class="newItem" />
	      <span id="mkb_desc_0"></span></td>
	    <td align="left">&nbsp;</td>
	  </tr>
	  <tr>
	    <td align="left"><strong>Диагноза</strong></td>
	    <td align="left"><input type="text" id="diagnosis_0" name="diagnosis_0" size="80" maxlength="100" value="" class="newItem" /></td>
	    <td align="left"><div align="right">
	        <input type="checkbox" id="is_new_0" name="is_new_0" value="1" />
	        Новооткрито </div></td>
	    <td align="left">&nbsp;</td>
	  </tr>
	</table>
	<?php
	$buff = ob_get_contents();
	ob_end_clean();
	return $buff;
}

// TABS BEGIN =====================
function echoExam1($checkup_id=0, $worker_id=0) {
	global $dbInst;
	ob_start();
	$f = $dbInst->getMedicalCheckupInfo($checkup_id);
	$w = $dbInst->getWorkerInfo($worker_id);
	?>
                <table class="xlstable" cellpadding="0" cellspacing="0">
                  <tr>
                    <td style="border-right:1px solid #CCCCCC;" width="60%"><table>
                        <tr>
                          <td align="left">Ръст</td>
                          <td align="left" nowrap="nowrap"><input type="text" id="worker_height" name="worker_height" size="4" maxlength="10" value="<?=((isset($f['worker_height']))?HTMLFormat($f['worker_height']):'')?>" onkeypress="return floatsonly(this, event);" />
                            см</td>
                          <td align="left">Стрес в дома</td>
                          <td align="left"><input type="checkbox" id="home_stress" name="home_stress" value="1"<?=((isset($f['home_stress'])&&$f['home_stress']=='1')?' checked="checked"':'')?> /></td>
                        </tr>
                        <tr>
                          <td align="left">Тегло</td>
                          <td align="left"><input type="text" id="worker_weight" name="worker_weight" size="4" maxlength="10" value="<?=((isset($f['worker_weight']))?HTMLFormat($f['worker_weight']):'')?>" onkeypress="return floatsonly(this, event);" />
                            кг</td>
                          <td align="left">Стрес в работата</td>
                          <td align="left"><input type="checkbox" id="work_stress" name="work_stress" value="1"<?=((isset($f['work_stress'])&&$f['work_stress']=='1')?' checked="checked"':'')?> /></td>
                        </tr>
                        <tr>
                          <td align="left">RR сист.</td>
                          <td align="left"><input type="text" id="rr_syst" name="rr_syst" size="4" maxlength="10" value="<?=((isset($f['rr_syst']))?HTMLFormat($f['rr_syst']):'')?>" onkeypress="return numbersonly(this, event);" /></td>
                          <td align="left">Социален стрес</td>
                          <td align="left"><input type="checkbox" id="social_stress" name="social_stress" value="1"<?=((isset($f['social_stress'])&&$f['social_stress']=='1')?' checked="checked"':'')?> /></td>
                        </tr>
                        <tr>
                          <td align="left">RR диаст.</td>
                          <td align="left"><input type="text" id="rr_diast" name="rr_diast" size="4" maxlength="10" value="<?=((isset($f['rr_diast']))?HTMLFormat($f['rr_diast']):'')?>" onkeypress="return numbersonly(this, event);" /></td>
                          <td align="left" rowspan="2">ВИДЕОДИСПЛЕЙ <br />
                            повече от 1/2 от <br />
                            раб. време</td>
                          <td align="left" rowspan="2"><input type="checkbox" id="video_display" name="video_display" value="1"<?=((isset($f['video_display'])&&$f['video_display']=='1')?' checked="checked"':'')?> /></td>
                        </tr>
                        <tr>
                          <td align="left">Тютюнопушене</td>
                          <td align="left"><input type="checkbox" id="smoking" name="smoking" value="1"<?=((isset($f['smoking'])&&$f['smoking']=='1')?' checked="checked"':'')?> /></td>
                        </tr>
                        <tr>
                          <td align="left">Алкохол</td>
                          <td align="left"><input type="checkbox" id="drinking" name="drinking" value=""<?=((isset($f['drinking'])&&$f['drinking']=='1')?' checked="checked"':'')?> /></td>
                          <td align="left" rowspan="2">Физическа<br />
                            активност часа /</td>
                          <td align="left" rowspan="2"><input type="text" id="hours_activity" name="hours_activity" size="4" maxlength="10" value="<?=((isset($f['hours_activity']))?HTMLFormat($f['hours_activity']):'')?>" onkeypress="return floatsonly(this, event);" /></td>
                        </tr>
                        <tr>
                          <td align="left">Нерационално хранене</td>
                          <td align="left"><input type="checkbox" id="fats" name="fats" value="1"<?=((isset($f['fats'])&&$f['fats']=='1')?' checked="checked"':'')?> /></td>
                        </tr>
                        <tr>
                          <td align="left">Диета</td>
                          <td align="left"><input type="checkbox" id="diet" name="diet" value="1"<?=((isset($f['diet'])&&$f['diet']=='1')?' checked="checked"':'')?> /></td>
                          <td align="left">Намалена двигателна активност</td>
                          <td align="left"><input type="checkbox" id="low_activity" name="low_activity" value="1"<?=((isset($f['low_activity'])&&$f['low_activity']=='1')?' checked="checked"':'')?> /></td>
                        </tr>
                      </table></td>
                    <td width="40%"><table>
                        <tr class="primary">
                          <td align="left">Възраст</td>
                          <td align="left"><input type="text" id="age1" name="age1" size="4" maxlength="10" value="<?=((isset($f['checkup_date_h'])&&isset($w['birth_date2']))?worker_age($w['birth_date2'], $f['checkup_date_h']).' г.':'')?>" readonly="readonly" />
                            г. (към датата на прегледа)</td>
                        </tr>
                        <tr>
                          <td align="left">Възраст</td>
                          <td align="left"><input type="text" id="age2" name="age2" size="4" maxlength="10" value="<?=((isset($w['birth_date2']))?worker_age($w['birth_date2'], date("d.m.Y")).' г.':'')?>" readonly="readonly" />
                            г. (в момента)</td>
                        </tr>
                        <tr>
                          <td align="left">Пол</td>
                          <td align="left"><input type="text" id="sex" name="sex" size="4" maxlength="10" value="<?=((isset($w['sex']))?HTMLFormat($w['sex']):'')?>" readonly="readonly" /></td>
                        </tr>
                        <tr>
                          <th align="left" colspan="2" id="subdivision_name"><?=((isset($w['subdivision_name']))?HTMLFormat($w['subdivision_name']):'')?>&nbsp;</th>
                        </tr>
                        <tr>
                          <th align="left" colspan="2" id="wplace_name"><?=((isset($w['wplace_name']))?HTMLFormat($w['wplace_name']):'')?>&nbsp;</th>
                        </tr>
                        <tr class="primary">
                          <th align="left" colspan="2" id="position_name"><?=((isset($w['position_name']))?HTMLFormat($w['position_name']):'')?>&nbsp;</th>
                        </tr>
                        <tr>
                          <td align="left" colspan="2">На тази длъжност от
                            <input type="text" id="date_curr_position_start2" name="duration" size="10" maxlength="20" value="<?=((isset($w['date_curr_position_start2']))?HTMLFormat($w['date_curr_position_start2']):'')?>" readonly="readonly" />
                            г.</td>
                        </tr>
                      </table></td>
                  </tr>
                </table>
	<?php
	$buff = ob_get_contents();
	ob_end_clean();
	return $buff;
}
function echoExam2($checkup_id=0) {
	global $dbInst;
	ob_start();
	$f = $dbInst->getMedicalCheckupInfo($checkup_id);
	?>
                <table class="xlstable" cellpadding="0" cellspacing="0">
                  <tr>
                    <td style="border-right:1px solid #CCCCCC;" width="50%"><table width="99%">
                        <tr>
                          <th align="left" colspan="2">Зрителна острота</th>
                        </tr>
                        <tr>
                          <td align="left">Ляво око</td>
                          <td align="left"><input type="text" id="left_eye" name="left_eye" size="4" maxlength="20" value="<?=((isset($f['left_eye']))?HTMLFormat($f['left_eye']):'')?>" />
                            <input type="text" id="left_eye2" name="left_eye2" size="4" maxlength="20" value="<?=((isset($f['left_eye2']))?HTMLFormat($f['left_eye2']):'')?>" onkeypress="return floatsonly(this, event);" />
                            dp</td>
                        </tr>
                        <tr>
                          <td align="left">Дясно око</td>
                          <td align="left"><input type="text" id="right_eye" name="right_eye" size="4" maxlength="20" value="<?=((isset($f['right_eye']))?HTMLFormat($f['right_eye']):'')?>" />
                            <input type="text" id="right_eye2" name="right_eye2" size="4" maxlength="20" value="<?=((isset($f['right_eye2']))?HTMLFormat($f['right_eye2']):'')?>" onkeypress="return floatsonly(this, event);" />
                            dp</td>
                        </tr>
                        <tr>
                          <th align="left" colspan="2">Функционално изследване на дишането</th>
                        </tr>
                        <tr>
                          <td align="left" colspan="2">ВК
                            <input type="text" id="VK" name="VK" size="4" maxlength="20" value="<?=((isset($f['VK']))?HTMLFormat($f['VK']):'')?>" onkeypress="return floatsonly(this, event);" />
                            ml &nbsp;&nbsp;ФЕО 1
                            <input type="text" id="FEO1" name="FEO1" size="4" maxlength="20" value="<?=((isset($f['FEO1']))?HTMLFormat($f['FEO1']):'')?>" onkeypress="return floatsonly(this, event);" />
                            ml</td>
                        </tr>
                        <tr>
                          <td align="left" colspan="2">Показател на Тифно
                            <input type="text" id="tifno" name="tifno" size="10" maxlength="20" value="<?=((isset($f['tifno']))?HTMLFormat($f['tifno']):'')?>" /></td>
                        </tr>
                        <tr>
                          <th align="left" colspan="2">Тонална аудиометрия</th>
                        </tr>
                        <tr>
                          <td align="left" colspan="2">Загуба на слуха
                            <select id="hearing_loss" name="hearing_loss">
                              <option value=""> &nbsp;&nbsp;</option>
                              <?php
                              $options = $dbInst->getPulldownOptions('hearing_loss');
                              foreach ($options as $option) {
                              	echo '<option value="'.HTMLFormat($option).'"'.((isset($f['hearing_loss']) && $f['hearing_loss']==$option)?' selected="selected"':'').'>'.HTMLFormat($option).' &nbsp;&nbsp;</option>';
                              }
                              ?>
                            </select></td>
                        </tr>
                        <tr>
                          <td align="left" colspan="2">Ляво ухо:
                            <input type="text" id="left_ear" name="left_ear" size="4" maxlength="20" value="<?=((isset($f['left_ear']))?HTMLFormat($f['left_ear']):'')?>" onkeypress="return numbersonly(this, event, 1);" /> &nbsp;&nbsp;&nbsp;&nbsp;
                            Дясно ухо:
                            <input type="text" id="right_ear" name="right_ear" size="4" maxlength="20" value="<?=((isset($f['right_ear']))?HTMLFormat($f['right_ear']):'')?>" onkeypress="return numbersonly(this, event, 1);" />
                            </td>
                        </tr>
                        <tr>
                          <td align="left" colspan="2">Диагноза
                            <input type="text" id="hearing_diagnose" name="hearing_diagnose" size="52" maxlength="100" value="<?=((isset($f['hearing_diagnose']))?HTMLFormat($f['hearing_diagnose']):'')?>" /></td>
                        </tr>
                      </table></td>
                    <td width="50%"><table width="99%">
                        <tr>
                          <td align="left">ЕКГ<br />
                            <textarea id="EKG" name="EKG" rows="3" cols="60"><?=((isset($f['EKG']))?HTMLFormat($f['EKG']):'')?></textarea>
                          </td>
                        </tr>
                        <tr>
                          <td align="left">Рентгенография<br />
                            <textarea id="x_ray" name="x_ray" rows="3" cols="60"><?=((isset($f['x_ray']))?HTMLFormat($f['x_ray']):'')?></textarea>
                          </td>
                        </tr>
                        <tr>
                          <td align="left">Ехография<br />
                            <textarea id="echo_ray" name="echo_ray" rows="3" cols="60"><?=((isset($f['echo_ray']))?HTMLFormat($f['echo_ray']):'')?></textarea>
                          </td>
                        </tr>
                      </table></td>
                  </tr>
                </table>
	<?php
	$buff = ob_get_contents();
	ob_end_clean();
	return $buff;
}
function echoFWeighs($checkup_id=0) {
	global $dbInst;
	ob_start();
	?>
	<table class="xlstable" cellpadding="0" cellspacing="0">
	  <tr>
	    <th align="left">Фамилна обремененост</th>
	  </tr>
	  <tr>
	    <td align="left"><div id="weightsWrapper"><?=loadFamilyWeights($checkup_id)?></div></td>
	  </tr>
	</table>
	<?php
	$buff = ob_get_contents();
	ob_end_clean();
	return $buff;
}
function echoAnamnesis($checkup_id=0) {
	global $dbInst;
	ob_start();
	?>
	<table class="xlstable" cellpadding="0" cellspacing="0">
	  <tr>
	    <th align="left">Анамнеза</th>
	  </tr>
	  <tr>
	    <td align="left"><div id="anamnesisWrapper"><?=loadAnamnesis($checkup_id)?></div></td>
	  </tr>
	</table>
	<?php
	$buff = ob_get_contents();
	ob_end_clean();
	return $buff;
}
function echoLabCheckups($checkup_id=0) {
	global $dbInst;
	ob_start();
	?>
	<table class="xlstable" cellpadding="0" cellspacing="0">
	  <tr>
	    <th align="left">Лабораторни изследвания </th>
	  </tr>
	  <tr>
	    <td align="left"><div id="labCheckupsWrapper"><?=loadLabCheckups($checkup_id)?></div></td>
	  </tr>
	</table>
	<?php
	$buff = ob_get_contents();
	ob_end_clean();
	return $buff;
}
function echoDiagnosis($checkup_id=0) {
	global $dbInst;
	ob_start();
	?>
	<table class="xlstable" cellpadding="0" cellspacing="0">
	  <tr>
	    <th align="left">Заболявания (диагнози) </th>
	  </tr>
	  <tr>
	    <td align="left"><div id="diagnosisWrapper"><?=loadDiagnosis($checkup_id)?></div></td>
	  </tr>
	</table>
	<?php
	$buff = ob_get_contents();
	ob_end_clean();
	return $buff;
}
function echoConclusion($checkup_id = 0) {
	global $dbInst;

	$sql = "SELECT s.SpecialistName AS SpecialistName , c.conclusion AS conclusion , c.SpecialistID AS SpecialistID
			FROM medical_checkups_doctors2 c
			LEFT JOIN Specialists s ON ( s.SpecialistID = c.SpecialistID )
			WHERE c.checkup_id = $checkup_id
			ORDER BY s.SpecialistName , s.SpecialistID";
	$conclusions = $dbInst->query($sql);

	$skipThese = array();
	if(!empty($conclusions)) {
		foreach ($conclusions as $row) {
			$skipThese[$row['SpecialistID']] = $row['SpecialistID'];
		}
	}

	$sql = "SELECT * FROM Specialists";
	if(!empty($skipThese)) { $sql .= " WHERE SpecialistID NOT IN ( ".implode(', ', $skipThese)." )"; }
	$sql .= " ORDER BY SpecialistName , SpecialistID";
	$specialists = $dbInst->query($sql);

	ob_start();
	?>
	<table class="xlstable" cellpadding="0" cellspacing="0">
	  <tr>
		<td align="left" width="35%">&nbsp;</td>
		<th align="left" width="62%">Име и заключение</th>
		<td width="3%">&nbsp;</td>
	  </tr>
	  <?php if(!empty($conclusions)) {
	  	foreach ($conclusions as $row) { ?>
	  <tr>
		<td align="left"><?=HTMLFormat($row['SpecialistName'])?></td>
		<td align="left"><textarea id="conclusion_<?=$row['SpecialistID']?>" name="conclusion_<?=$row['SpecialistID']?>" rows="2" style="width:99%"><?=HTMLFormat($row['conclusion'])?></textarea></td>
		<td><a title="Изтрий заболяването" onclick="var answ=confirm('Наистина ли искате да изтриете заключението?'); if(answ) { xajax_removeConclusion(<?=$checkup_id?>, <?=$row['SpecialistID']?>); } return false;" href="javascript:void(null);"><img width="15" height="15" border="0" alt="Изтрий" src="img/delete.gif" /></a></td>
	  </tr>
	  <?php }
	  } ?>
	  <?php if(!empty($specialists)) { ?>
	  <tr>
		<td align="left"><select id="SpecialistID" name="SpecialistID">
		    <option value="0">- ИЗБЕРЕТЕ СПЕЦИАЛИСТ - </option>
		    <?php foreach ($specialists as $row) { ?>
		    <option value="<?=$row['SpecialistID']?>"><?=HTMLFormat($row['SpecialistName'])?> </option>
		    <?php } ?>
		  </select></td>
		<td align="left"><textarea id="conclusion" name="conclusion" rows="2" style="width:99%"></textarea></td>
		<td>&nbsp;</td>
	  </tr>
	  <?php } ?>
	</table>
	<?php
	return ob_get_clean();
}
function echoConclusionSTM($checkup_id=0) {
	global $dbInst;
	ob_start();
	$f = $dbInst->getMedicalCheckupInfo($checkup_id);
	?>
                <table class="xlstable" cellpadding="0" cellspacing="0">
                  <tr>
                    <td align="left" valign="top"><p id="printConclusion" style="display:<?=((isset($f['stm_conclusion']) && $f['stm_conclusion'] != '')?'block':'none')?>;"><a href="w_rtf_stm_conclusion_medchk.php?checkup_id=<?=$checkup_id?>&amp;<?=SESS_NAME.'='.session_id()?>" title="Отвори с MSWord"><img src="img/medical3.gif" width="16" height="16" border="0" alt="Заключение" /> Заключение на СТМ:</a></p><p id="noPrintConclusion" style="display:<?=((isset($f['stm_conclusion']) && $f['stm_conclusion'] != '')?'none':'block')?>;">Заключение на СТМ:</p></td>
                    <td align="left">Лицето
                    <select id="stm_conclusion" name="stm_conclusion">
                      <option value=""> &nbsp;&nbsp;</option>
                      <option value="1"<?=((isset($f['stm_conclusion'])&&$f['stm_conclusion']=='1')?' selected="selected"':'')?>>може &nbsp;&nbsp;</option>
                      <option value="2"<?=((isset($f['stm_conclusion'])&&$f['stm_conclusion']=='2')?' selected="selected"':'')?>>може при сл. условия &nbsp;&nbsp;</option>
                      <option value="0"<?=((isset($f['stm_conclusion'])&&$f['stm_conclusion']=='0')?' selected="selected"':'')?>>не може &nbsp;&nbsp;</option>
                      <option value="3"<?=((isset($f['stm_conclusion'])&&$f['stm_conclusion']=='3')?' selected="selected"':'')?>>не може да се прецени пригодността на работещия</option>
                    </select>
                    да изпълнява тази длъжност/професия<br />
                    <textarea id="stm_conditions" name="stm_conditions" rows="4" cols="60"<?=((isset($f['stm_conclusion']) && in_array($f['stm_conclusion'], array('2', '3')))?'':' style="display:none"')?>><?=((isset($f['stm_conditions']))?HTMLFormat($f['stm_conditions']):'')?></textarea></td>
                  </tr>
                  <tr>
                    <td align="left"><p>Дата на изготвяне:</p></td>
                    <td align="left"><input type="text" id="stm_date" name="stm_date" value="<?=((isset($f['stm_date2']))?HTMLFormat($f['stm_date2']):date('d.m.Y'))?>" onchange="xajax_formatBGDate('stm_date', this.value);return false;" onclick="scwShow(this,event);" class="date_input" size="20" maxlength="10" /> г.</td>
                  </tr>
                </table>
	<?php
	$buff = ob_get_contents();
	ob_end_clean();
	return $buff;
}
// TABS END =====================

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
<script type="text/javascript" charset="utf-8">
//<![CDATA[
$(document).ready(function() {
	stripTable();
	if($.browser.msie) {
		$("input[type='text']:disabled,textarea:disabled,select:disabled").css("background-color", "#EEEEEE");
		$(":checkbox").css("border","none");
	}
	$("#stm_conclusion").change(function(){
		if(this.value == '2' || this.value == '3') {
			$("#stm_conditions").show("slow");
		} else {
			$("#stm_conditions").val('');
			$("#stm_conditions").hide("slow");
		}
	});
	$("a.tab").click(function(e){
		e.preventDefault();
		var tab = $(this).attr("rel");
		window.location = '<?=$_SERVER['PHP_SELF']?>?firm_id='+$("#firm_id").val()+'&worker_id='+$("#worker_id").val()+'&checkup_id='+$("#checkup_id").val()+'&tab='+tab;
	});
});
function stripTable() {
	// Strip table
	$("#hospitalsList tr:even").addClass("alternate");
	// Hightlight table rows
	$("#hospitalsList tr").hover(function() {
		$(this).addClass("over");
	},function() {
		$(this).removeClass("over");
	});
}
function clearForm(itsForm) {
	try {
		var form = document.forms[itsForm];
		for(var i = 0; i < form.elements.length; i++) {
			var element = form.elements[i];
			// Don't clean up the fields below
			//if(element.name == 'checkup_id' || element.name == 'firm_id' || element.name == 'wname' || element.name == 'egn') continue;
			var type = element.type;
			var tag = element.tagName.toLowerCase();
			if (type == 'text' || type == 'password' || tag == 'textarea')
			element.value = '';
			else if (type == 'checkbox' || type == 'radio')
			element.checked = false;
			else if (tag == 'select')
			element.selectedIndex = -1;
		}
	}
	catch (err) {
		alert(err.description);
	}
}
//]]>
</script>
<!-- Auto-completer includes begin -->
<!-- http://dev.jquery.com/view/trunk/plugins/autocomplete/ -->
<!-- <script type="text/javascript" src="js/autocompleter/jquery.js"></script> -->
<script type='text/javascript' src='js/autocompleter/jquery.bgiframe.min.js'></script>
<script type='text/javascript' src='js/autocompleter/jquery.dimensions.js'></script>
<script type='text/javascript' src='js/autocompleter/jquery.ajaxQueue.js'></script>
<script type='text/javascript' src='js/autocompleter/jquery.autocomplete.js'></script>
<script type='text/javascript' src='js/autocompleter/localdata.js'></script>
<!-- <link rel="stylesheet" type="text/css" href="js/autocompleter/main.css" /> -->
<link rel="stylesheet" type="text/css" href="js/autocompleter/jquery.autocomplete.css" />
<!-- Auto-completer includes end -->
<?php if(in_array($tab, array('fweighs', 'anamnesis', 'diagnosis'))) { ?>
<!-- http://colorpowered.com/colorbox/core/example1/index.html -->
<link type="text/css" media="screen" rel="stylesheet" href="js/colorbox/colorbox.css" />
<script type="text/javascript" src="js/colorbox/jquery.colorbox.js"></script>
<script type="text/javascript">
//<![CDATA[
var obj_mkb_id = null;
var obj_mkb_desc = null;

function openMkbNomenclature(el) {
	var prchk_id = $(el).attr('id').split('_')[2];
	obj_mkb_id = $('#mkb_id_' + prchk_id);
	obj_mkb_desc = $('#mkb_desc_' + prchk_id);
	$(el).colorbox({width:"90%", height:"100%", iframe:true, overlayClose:false, title:'Номенклатура МКБ 10', transition:"none", fastIframe:false, href:'popup_mkb_nomenclature.php'});
	return false;
}
function populateFields(mkb_id, mkb_desc) {
	obj_mkb_id.val(mkb_id);
	obj_mkb_desc.html(mkb_desc);
}
//]]>
</script>
<?php } ?>
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
	$(":text, textarea").result(findValueCallback).next().click(function() {
		$(this).prev().search();
	});

	<?php if($tab == 'exam1') { ?>
	$("#egn").autocomplete("autocompleter.php", {
		minChars: 1,
		extraParams: { search: "wname", firm_id: $("#firm_id").val() },
		width: 129,
		/*max: 4,
		highlight: false,*/
		scroll: true,
		scrollHeight: 300,
		selectFirst: false,
		formatItem: function(data, i, n, value) {
			var worker_id = data[0];
			var egn = data[3];
			return egn;
		}
	});
	$("#egn").result(function(event, data, formatted) {
		if (data) {
			$("#wname").val(data[1]);
			$("#firm_id").val(data[2]);
			$("#worker_id").val(data[0]);

			var parent = (window.opener) ? window.opener : self.parent;
			parent.document.getElementById('TB_ajaxWindowTitle').innerHTML = 'Нанасяне на резултатите от профилактични прегледи на '+data[1]+', ЕГН '+data[3];
			xajax_loadPatientInfo(data[0]);
			$("#lnkCard").hide();
			$("#lnkDel").hide();
		}
	});

	$("#wname").autocomplete("autocompleter.php", {
		minChars: 1,
		extraParams: { search: "wname", firm_id: $("#firm_id").val() },
		width: 356,
		/*max: 4,
		highlight: false,
		scroll: true,
		scrollHeight: 300,*/
		selectFirst: false,
		formatItem: function(data, i, n, value) {
			var checkup_id = data[0];
			var wname = data[1];
			return wname;
		}
	});
	$("#wname").result(function(event, data, formatted) {
		if (data) {
			$("#wname").val(data[1]);
			$("#firm_id").val(data[2]);
			$("#worker_id").val(data[0]);

			var parent = (window.opener) ? window.opener : self.parent;
			parent.document.getElementById('TB_ajaxWindowTitle').innerHTML = 'Нанасяне на резултатите от профилактични прегледи на '+data[1]+', ЕГН '+data[3];
			xajax_loadPatientInfo(data[0]);
			$("#lnkCard").hide();
			$("#lnkDel").hide();
		}
	});
	<?php } ?>

	mkbAutocomplete();
});
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
			$("#mkb_desc_"+id).html(data[1]);
			$("#mkb_code_"+id).html(data[2]);
			/*$("#mkb_id").val(data[0]);
			$("#mkb_desc").html(data[1]);
			$("#mkb_code").html(data[2]);*/
		}
	});
}
// Auto-completer end
function downloadCard() {
	if($("#checkup_id").val() == 0) {
		alert('Моля, изберете медицински преглед.')
		return false;
	}
	window.location = 'w_rtf_worker_card.php?checkup_id='+$("#checkup_id").val();
}
//]]>
</script>
<style type="text/css">
body, html {
	background-image:none;
	background-color:#EEEEEE;
}
</style>
</head>
<body>
<div id="contentinner" align="center">
  <form id="frmCheckup" name="frmCheckup" action="javascript:void(null);">
    <input type="hidden" id="form_is_dirty" name="form_is_dirty" value="0" />
    <input type="hidden" id="worker_id" name="worker_id" value="<?=$worker_id?>" />
    <input type="hidden" id="firm_id" name="firm_id" value="<?=$firm_id?>" />
    <?=getPopupNavigation('Профилактични прегледи')?>
    <table cellpadding="0" cellspacing="0" class="formBg" width="790">
      <tr>
        <th class="leftSplit rightSplit topSplit">Карта от профилактичен медицински преглед &nbsp;&nbsp;<?php if($_SESSION['sess_user_level'] == 1) { /* admin rights only */ ?><a id="lnkDel" href="javascript:void(0)" onclick="if(confirm('Наистина ли искате да изтриете картата от профилактичния медицински преглед?')){xajax_deleteCheckup(<?=$checkup_id?>,<?=$worker_id?>,<?=$firm_id?>);}return false;"<?php if($checkup_id <= 0) { echo ' style="display:none;"'; } ?> title="Изтрий прегледа"><img src="img/delete.gif" width="15" height="15" border="0" alt="Изтрий прегледа" /></a><?php } ?></th>
      </tr>
      <tr>
        <td class="leftSplit rightSplit">ЕГН:
          <?php $readonly = ($tab != 'exam1') ? ' readonly="readonly"' : ''; ?>
          <input type="text" id="egn" name="egn" value="<?=((isset($w['egn']))?HTMLFormat($w['egn']):'')?>" size="20" maxlength="10"<?=$readonly?> />
          &nbsp;&nbsp;
          Име:
          <input type="text" id="wname" name="wname" value="<?=((isset($w['lname']))?HTMLFormat($w['fname'].' '.$w['sname'].' '.$w['lname']):'')?>" size="65" maxlength="50" style="width:352px;"<?=$readonly?> />
          &nbsp;&nbsp;
          <?php $f = $dbInst->getMedicalCheckupInfo($checkup_id); ?>
          <span id="lnkCard"<?php if($f['checkup_date_h'] == '') { echo ' style="display:none;"'; } ?>><a href="#" onclick="downloadCard();" title="Печат на картата за профилактичен преглед"><img src="img/medical3.gif" alt="Печат на картата за профилактичен преглед" width="16" height="16" border="0" /> Карта от проф. преглед</a></span>
          <div class="br"></div>
          Преглед №
          <input type="text" id="PregledNo" name="PregledNo" value="<?=((isset($f['PregledNo']))?HTMLFormat($f['PregledNo']):'')?>"<?=$readonly?> />
          <strong>Дата:</strong>
          <input type="text" id="checkup_date" name="checkup_date" value="<?=((isset($f['checkup_date_h']))?HTMLFormat($f['checkup_date_h']):'')?>" onchange="xajax_formatBGDate('checkup_date', this.value);return false;" onclick="scwShow(this,event);" class="date_input" size="20" maxlength="10"<?=$readonly?> />
          г. &nbsp;&nbsp;
          Изберете карта: <span id="checkupWrapper">
          <?=checkupListOptions($worker_id, $firm_id, $checkup_id)?>
          </span>
          <div class="br"></div>
          Място на прегледа:
          <?php
          if(!($_data = @unserialize($f['hospital']))) {
          	$_data = array();
          }
          for ($i = 0; $i < 3; $i++) {
          	echo '<input type="text" name="hospital[]" value="'.((is_array($_data)&&isset($_data[$i]))?HTMLFormat($_data[$i]):'').'" size="35" maxlength="50"'.$readonly.' />';
          }
          ?>
        </td>
      </tr>
    </table>
    <div align="center" style="width:790px;margin-top:2px;">
      <div id="tabs"> <a href="#" class="tab<?=(($tab=='exam1')?' active':'')?>" rel="exam1">1 - преглед</a> <?php if($checkup_id) { ?><a href="#" class="tab<?=(($tab=='exam2')?' active':'')?>" rel="exam2">2 - преглед </a> <a href="#" class="tab<?=(($tab=='fweighs')?' active':'')?>" rel="fweighs">фам. заб. </a> <a href="#" class="tab<?=(($tab=='anamnesis')?' active':'')?>" rel="anamnesis">анамнеза </a> <a href="#" class="tab<?=(($tab=='checkups')?' active':'')?>" rel="checkups">изследвания </a> <a href="#" class="tab<?=(($tab=='diagnosis')?' active':'')?>" rel="diagnosis">диагнози </a> <a href="#" class="tab<?=(($tab=='conclusion')?' active':'')?>" rel="conclusion">заключение </a> <a href="#" class="tab<?=(($tab=='conclusion_stm')?' active':'')?>" rel="conclusion_stm">заключение на СТМ </a><?php } ?></div>
      <script type="text/javascript">if ( (jQuery.browser.msie && jQuery.browser.version < 7)) { document.write('<br clear="all" \/>'); }</script>
      <div id="panel" class="panel" style="display:block">
      <?php
      switch ($tab) {
      	case 'exam2':
      		echo echoExam2($checkup_id);
      		break;

      	case 'fweighs':
      		echo echoFWeighs($checkup_id);
      		break;

      	case 'anamnesis':
      		echo echoAnamnesis($checkup_id);
      		break;

      	case 'checkups':
      		echo echoLabCheckups($checkup_id);
      		break;

      	case 'diagnosis':
      		echo echoDiagnosis($checkup_id);
      		break;

      	case 'conclusion':
      		echo echoConclusion($checkup_id);
      		break;

      	case 'conclusion_stm':
      		echo echoConclusionSTM($checkup_id);
      		break;

      	case 'exam1':
      	default:
      		echo echoExam1($checkup_id, $worker_id);
      		break;
      }
      ?>
      </div>
    </div>
    <table cellpadding="0" cellspacing="0" class="formBg" width="790">
      <tr>
        <td class="leftSplit rightSplit"><p align="center">
            <input type="button" id="btnSubmit" name="btnSubmit" value="Съхрани" class="nicerButtons" onclick="$('input#form_is_dirty').val(1);this.value='обработка...';this.disabled=true;xajax_processMedicalCheckup(xajax.getFormValues('frmCheckup'),'<?=$tab?>'); DisableEnableForm(true); return false;" />
          </p></td>
      </tr>
    </table>
  </form>
</div>
</body>
</html>