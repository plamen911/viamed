<?php
require('includes.php');

$worker_id = (isset($_GET['worker_id']) && is_numeric($_GET['worker_id'])) ? intval($_GET['worker_id']) : 0;
$firm_id = $dbInst->GiveValue('firm_id', 'workers', "WHERE `worker_id` = $worker_id", 0);
if(!$firm_id) {
	die('Incorrect worker ID!');
}
$precheckup_id = (isset($_GET['precheckup_id']) && is_numeric($_GET['precheckup_id'])) ? intval($_GET['precheckup_id']) : 0;
if(!isset($_GET['precheckup_id'])) {
	$precheckup_id = $dbInst->GiveValue('precheckup_id', 'medical_precheckups', "WHERE `worker_id` = $worker_id ORDER BY `prchk_date` DESC, `precheckup_id` LIMIT 1", 0);
}

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
} else {
	$precheckup_id = 0;
	$f = $dbInst->getWorkerInfo($worker_id);
}

$tab = (isset($_GET['tab']) && $_GET['tab'] != '') ? trim($_GET['tab']) : 'checkups';

// Xajax begin
require ('xajax/xajax_core/xajax.inc.php');
function loadWorkerInfo($worker_id) {
	$objResponse = new xajaxResponse();

	$objResponse->call("clearForm", "frmPreCheckup");

	global $dbInst;
	$w = $dbInst->getWorkerInfo($worker_id);
	$objResponse->assign("worker_id", "value", $worker_id);
	$objResponse->assign("wname", "value", HTMLFormat($w['fname'].' '.$w['sname'].' '.$w['lname']));
	$objResponse->assign("egn", "value", $w['egn']);
	$objResponse->assign("prchk_author", "value", HTMLFormat($w['prchk_author']));
	$objResponse->assign("prchk_date", "value", $w['prchk_date2']);
	$objResponse->assign("prchk_anamnesis", "value", HTMLFormat($w['prchk_anamnesis']));
	$objResponse->assign("prchk_data", "value", HTMLFormat($w['prchk_data']));
	$objResponse->assign("prchk_conclusion", "value", $w['prchk_conclusion']);
	$objResponse->assign("prchk_conditions", "value", HTMLFormat($w['prchk_conditions']));
	if($w['prchk_conclusion'] == '2') {
		$objResponse->assign("prchk_conditions", "style.display", "block");
	}
	else {
		$objResponse->assign("prchk_conditions", "style.display", "none");
	}
	$objResponse->assign("prchk_stm_date", "value", $w['prchk_stm_date2']);

	return $objResponse;
}
function processPrchkCheckup($aFormValues, $tab='checkups') {
	$objResponse = new xajaxResponse();

	$objResponse->assign("btnSubmit","disabled",false);
	$objResponse->assign("btnSubmit","value","Съхрани");
	$objResponse->call("DisableEnableForm",false);

	global $dbInst;
	$worker_id = intval($aFormValues['worker_id']);
	if(!$worker_id) {
		$objResponse->alert('Моля, изберете работещ по ЕГН или име.');
		if($tab != 'checkups') {
			$objResponse->script("window.location.href='".basename($_SERVER['PHP_SELF'])."?worker_id=".intval($aFormValues['worker_id'])."&tab=checkups';");
		}
		return $objResponse;
	}
	if(trim($aFormValues['prchk_author']) == '') {
		$objResponse->alert('Моля, въведете от кого е издадена картата за предварителен медицински преглед.');
		return $objResponse;
	}

	$d = new ParseBGDate();
	$prchk_date = trim($aFormValues['prchk_date']);
	if($prchk_date == '') {
		$objResponse->alert('Моля, въведете дата на предварителния медицинския преглед.');
		if($tab != 'checkups') {
			$objResponse->script("window.location.href='".basename($_SERVER['PHP_SELF'])."?worker_id=".intval($aFormValues['worker_id'])."&tab=checkups';");
		}
		return $objResponse;
	}
	if(!$d->Parse($prchk_date)) {
		$objResponse->alert($prchk_date . ' е невалидна дата!');
		$objResponse->script("window.location.href='".basename($_SERVER['PHP_SELF'])."?worker_id=".intval($aFormValues['worker_id'])."';");
		return $objResponse;
	}

	if($tab == 'checkups') {
		if($aFormValues['prchk_conclusion'] == '2' && trim($aFormValues['prchk_conditions']) == '') {
			$objResponse->alert("Моля, въведете условията, при които работещият може да изпълнява тази длъжност/професия.");
			return $objResponse;
		}
		$d = new ParseBGDate();
		$prchk_stm_date = trim($aFormValues['prchk_stm_date']);
		/*if($prchk_stm_date == '') {
		$objResponse->alert('Моля, въведете датата на изготвяне на заключението от СТМ.');
		return $objResponse;
		}*/
		if($prchk_stm_date != '' && !$d->Parse($prchk_stm_date)) {
			$objResponse->alert($prchk_stm_date . ' е невалидна дата!');
			return $objResponse;
		}
		if($prchk_stm_date != '') {
			$d->Parse($prchk_stm_date);
			$prchk_stm_date = mktime(0, 0, 0, $d->getMonth(), $d->getDay(), $d->getYear());
			$d->Parse($prchk_date);
			$prchk_date = mktime(0, 0, 0, $d->getMonth(), $d->getDay(), $d->getYear());
			if($prchk_date > $prchk_stm_date) {
				$objResponse->alert('Датата на изготвяне на заключението не може да е преди датата на прегледа!');
				return $objResponse;
			}
		}
		if($aFormValues['prchk_conclusion'] == '') {
			$objResponse->assign('noPrintConclusion', 'style.display', '');
			$objResponse->assign('printConclusion', 'style.display', 'none');
		} else {
			$objResponse->assign('noPrintConclusion', 'style.display', 'none');
			$objResponse->assign('printConclusion', 'style.display', '');
		}
	}

	if($tab == 'diagnosis') {
		foreach ($aFormValues as $key=>$val) {
			if(preg_match('/^mkb_id_(\d+)$/', $key, $matches)) {
				if(!$dbInst->isValidMkb($val)) {
					$objResponse->alert($val.' е невалидна стойност!');
					return $objResponse;
				}
			}
		}
	}
	elseif ($tab == 'specialists') {
		$SpecialistID = (isset($aFormValues['SpecialistID'])) ? intval($aFormValues['SpecialistID']) : 0;
		if(!empty($SpecialistID)) {
			$conclusion = (isset($aFormValues['conclusion'])) ? trim($aFormValues['conclusion']) : '';
			if(empty($conclusion)) {
				$objResponse->alert('Моля, въведете име и заключение на специалиста.');
				return $objResponse;
			}
		}
	}

	$precheckup_id = intval($aFormValues['precheckup_id']);
	$isNewPreCheckup = (!empty($precheckup_id)) ? 0 : 1;
	$thisID = $dbInst->processPrchkCheckup($aFormValues, $tab); // Insert/update a medical checkup
	if(!$precheckup_id) { $precheckup_id = $thisID; }

	//$objResponse->alert($precheckup_id);

	//$objResponse->call("clearForm", "frmPreCheckup");
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
	$w = $dbInst->query($sql);
	if(!empty($w)) {
		$w = $w[0];
	}
	$objResponse->assign("precheckup_id", "value", $precheckup_id);
	$objResponse->assign("wname", "value", HTMLFormat($w['fname'].' '.$w['sname'].' '.$w['lname']));
	$objResponse->assign("egn", "value", $w['egn']);
	$objResponse->assign("prchk_author", "value", $w['prchk_author']);
	$objResponse->assign("prchk_date", "value", $w['prchk_date2']);
	if($tab == 'diagnosis') {
		$objResponse->assign("panel", "innerHTML", echoDiagnosis($precheckup_id));
		$objResponse->script("mkbAutocomplete()");
	}
	elseif ($tab == 'specialists') {
		$objResponse->assign("panel", "innerHTML", echoSpecialists($precheckup_id));
	}
	if(isset($w['prchk_author']) && $w['prchk_author'] != '') {
		$objResponse->assign("lnks", "style.visibility", "visible");
	} else {
		$objResponse->assign("lnks", "style.visibility", "hidden");
	}

	$objResponse->assign('sp_cards', 'innerHTML', precheckupPulldown($worker_id, $precheckup_id));
	
	$dbInst->processLastPrchkCheckup($worker_id);

	if($isNewPreCheckup) {
		$sql = "SELECT COUNT(*) AS `cnt` FROM `medical_precheckups` WHERE `worker_id` = $worker_id";
		$row = $dbInst->fnSelectSingleRow($sql);
		if(!empty($row)) {
			$objResponse->script('if(parent.$("#w_precheckups_num_'.$worker_id.'")[0]){parent.$("#w_precheckups_num_'.$worker_id.'").html("'.HTMLFormat($row['cnt']).'")}');
		}
	}

	return $objResponse;
}
function removePrchkDiagnosis($prchk_id, $precheckup_id) {
	$objResponse = new xajaxResponse();

	if(isset($_SESSION['sess_user_level']) && $_SESSION['sess_user_level'] == 1) { /* admin rights only */
		global $dbInst;
		$count = $dbInst->removePrchkDiagnosis($prchk_id);
		$objResponse->assign("panel", "innerHTML", echoDiagnosis($precheckup_id));
		$objResponse->script("mkbAutocomplete();");
	}

	return $objResponse;
}
function deletePrchkCheckup($precheckup_id = 0, $worker_id = 0) {
	$objResponse = new xajaxResponse();
	if(isset($_SESSION['sess_user_level']) && $_SESSION['sess_user_level'] == 1) { /* admin rights only */
		global $dbInst;
		$dbInst->query("DELETE FROM `medical_precheckups` WHERE `precheckup_id` = $precheckup_id");
		$dbInst->query("DELETE FROM `prchk_diagnosis` WHERE `precheckup_id` = $precheckup_id");
		$dbInst->processLastPrchkCheckup($worker_id);
		$sql = "SELECT COUNT(*) AS `cnt` FROM `medical_precheckups` WHERE `worker_id` = $worker_id";
		$row = $dbInst->fnSelectSingleRow($sql);
		if(!empty($row)) {
			$objResponse->script('if(parent.$("#w_precheckups_num_'.$worker_id.'")[0]){parent.$("#w_precheckups_num_'.$worker_id.'").html("'.HTMLFormat($row['cnt']).'")}');
		}
		$objResponse->script('window.location="'.basename($_SERVER['PHP_SELF']).'?worker_id='.$worker_id.'&tab=checkups&'.SESS_NAME.'='.session_id().'"');
	}
	return $objResponse;
}
function removeConclusion($precheckup_id = 0, $SpecialistID = 0) {
	$objResponse = new xajaxResponse();
	global $dbInst;
	$dbInst->query("DELETE FROM medical_precheckups_doctors2 WHERE precheckup_id = $precheckup_id AND SpecialistID = $SpecialistID");
	$objResponse->assign("panel", "innerHTML", echoSpecialists($precheckup_id));
	return $objResponse;
}
$xajax = new xajax();
$xajax->registerFunction("loadWorkerInfo");
$xajax->registerFunction("formatBGDate");
$xajax->registerFunction("processPrchkCheckup");
$xajax->registerFunction("removePrchkDiagnosis");
$xajax->registerFunction("deletePrchkCheckup");
$xajax->registerFunction("removeConclusion");
//$xajax->setFlag("debug",true);
$echoJS = $xajax->getJavascript('xajax/');
$xajax->processRequest();
// Xajax end

// TABS BEGIN =====================
function echoSpecialists($precheckup_id = 0) {
	global $dbInst;

	$sql = "SELECT s.SpecialistName AS SpecialistName , c.conclusion AS conclusion , c.SpecialistID AS SpecialistID
			FROM medical_precheckups_doctors2 c
			LEFT JOIN Specialists s ON ( s.SpecialistID = c.SpecialistID )
			WHERE c.precheckup_id = $precheckup_id
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
		<td><a title="Изтрий заболяването" onclick="var answ=confirm('Наистина ли искате да изтриете заключението?'); if(answ) { xajax_removeConclusion(<?=$precheckup_id?>, <?=$row['SpecialistID']?>); } return false;" href="javascript:void(null);"><img width="15" height="15" border="0" alt="Изтрий" src="img/delete.gif" /></a></td>
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
function echoDiagnosis($precheckup_id=0) {
	global $dbInst;
	$rows = $dbInst->getPrchkDiagnosis($precheckup_id);
	$options = $dbInst->query("SELECT * FROM Specialists ORDER BY SpecialistName, SpecialistID");

	ob_start();
	?>
	<table class="xlstable" cellpadding="0" cellspacing="0">
	  <tr>
	    <th>Заболявания (диагнози) </th>
	  </tr>
	  <tr>
	    <td><div id="diagnosisWrapper">
	        <table cellpadding="0" cellspacing="0">
	          <?php foreach ($rows as $row) { ?>
	          <tr>
	            <td class="primary"><a href="#" id="mkb_code_<?=$row['prchk_id']?>" onclick="openMkbNomenclature(this);">МКБ <img src="img/moreinfo.gif" alt="info" border="0" width="17" height="17" /></a></td>
	            <td><input type="text" id="mkb_id_<?=$row['prchk_id']?>" name="mkb_id_<?=$row['prchk_id']?>" size="10" maxlength="50" value="<?=HTMLFormat($row['mkb_id'])?>" />
	              <span id="mkb_desc_<?=$row['prchk_id']?>"><?=HTMLFormat($row['mkb_desc'])?></span></td>
	            <td>&nbsp;&nbsp;&nbsp;
	              <?php if($_SESSION['sess_user_level'] == 1) { /* admin rights only */ ?>
	              <a href="javascript:void(null);" onclick="var answ=confirm('Наистина ли искате да изтриете заболяването?'); if(answ) { xajax_removePrchkDiagnosis(<?=$row['prchk_id']?>, <?=$precheckup_id?>); } return false;" title="Изтрий заболяването"><img src="img/delete.gif" width="15" height="15" border="0" alt="Изтрий" /></a>
	              <?php } ?></td>
	          </tr>
	          <tr class="underline">
	            <td><strong>Диагноза</strong></td>
	            <td><input type="text" id="diagnosis_<?=$row['prchk_id']?>" name="diagnosis_<?=$row['prchk_id']?>" size="75" maxlength="100" value="<?=HTMLFormat($row['diagnosis'])?>" />
	              &nbsp;&nbsp;
	              <select id="published_by_<?=$row['prchk_id']?>" name="published_by_<?=$row['prchk_id']?>">
	                <option value=""> - издадена от -&nbsp;&nbsp;</option>
	                <?php
	                foreach ($options as $field) {
	                	echo '<option value="'.$field['SpecialistID'].'"'.((isset($row['published_by']) && $row['published_by'] == $field['SpecialistID'])?' selected="selected"':'').'>'.HTMLFormat($field['SpecialistName']).' &nbsp;&nbsp;</option>';
	                }
	                ?>
	              </select></td>
	            <td>&nbsp;</td>
	          </tr>
	          <?php } ?>
	          <tr>
	            <td class="primary"><a href="#" id="mkb_code_0" onclick="openMkbNomenclature(this);">МКБ <img src="img/moreinfo.gif" alt="info" border="0" width="17" height="17" /></a></td>
	            <td colspan="2"><input type="text" id="mkb_id_0" name="mkb_id_0" size="10" maxlength="50" value="" class="newItem" />
	              <span id="mkb_desc_0"></span></td>
	          </tr>
	          <tr>
	            <td><strong>Диагноза</strong></td>
	            <td><input type="text" id="diagnosis_0" name="diagnosis_0" size="75" maxlength="100" value="" class="newItem" />
	              &nbsp;&nbsp;
	              <select id="published_by_0" name="published_by_0" class="newItem">
	                <option value=""> - издадена от -&nbsp;&nbsp;</option>
	                <?php
	                foreach ($options as $field) {
	                	echo '<option value="'.$field['SpecialistID'].'">'.HTMLFormat($field['SpecialistName']).' &nbsp;&nbsp;</option>';
	                }
	                ?>
	              </select></td>
	            <td>&nbsp;</td>
	          </tr>
	        </table>
	      </div></td>
	  </tr>
	</table>
	<?php
	$buff = ob_get_contents();
	ob_end_clean();
	return $buff;
}
function echoCheckups($worker_id=0) {
	global $f;
	ob_start();
	?>
                <table class="xlstable" cellpadding="0" cellspacing="0">
                  <tr>
                    <td><table>
                        <tr>
                          <td valign="top"><p>Анамнеза:</p></td>
                          <td><textarea id="prchk_anamnesis" name="prchk_anamnesis" cols="60" rows="4"><?=((isset($f['prchk_anamnesis']))?HTMLFormat($f['prchk_anamnesis']):'')?></textarea></td>
                        </tr>
                        <tr>
                          <td valign="top"><p>Данни от прегледа:</p></td>
                          <td><textarea id="prchk_data" name="prchk_data" cols="60" rows="4"><?=((isset($f['prchk_data']))?HTMLFormat($f['prchk_data']):'')?></textarea></td>
                        </tr>
                        <tr>
                          <td valign="top"><p id="printConclusion" style="display:<?=((isset($f['prchk_conclusion']) && $f['prchk_conclusion'] != '')?'block':'none')?>;"><a href="w_rtf_stm_conclusion_prchk.php?precheckup_id=<?=$f['precheckup_id']?>&amp;<?=SESS_NAME.'='.session_id()?>" title="Отвори с MSWord"><img src="img/medical3.gif" width="16" height="16" border="0" alt="Заключение" /> Заключение на СТМ:</a></p><p id="noPrintConclusion" style="display:<?=((isset($f['prchk_conclusion']) && $f['prchk_conclusion'] != '')?'none':'block')?>;">Заключение на СТМ:</p></td>
                          <td>Лицето
                              <select id="prchk_conclusion" name="prchk_conclusion">
                                <option value=""> &nbsp;&nbsp;</option>
                                <option value="1"<?=((isset($f['prchk_conclusion'])&&$f['prchk_conclusion']=='1')?' selected="selected"':'')?>>може &nbsp;&nbsp;</option>
                                <option value="2"<?=((isset($f['prchk_conclusion'])&&$f['prchk_conclusion']=='2')?' selected="selected"':'')?>>може при сл. условия &nbsp;&nbsp;</option>
                                <option value="0"<?=((isset($f['prchk_conclusion'])&&$f['prchk_conclusion']=='0')?' selected="selected"':'')?>>не може &nbsp;&nbsp;</option>
                              </select>
                              да изпълнява тази длъжност/професия<br />
                              <textarea id="prchk_conditions" name="prchk_conditions" rows="3" cols="60"<?=((isset($f['prchk_conclusion'])&&$f['prchk_conclusion']=='2')?'':' style="display:none"')?>><?=((isset($f['prchk_conditions']))?HTMLFormat($f['prchk_conditions']):'')?></textarea></td>
                        </tr>
                        <tr>
                          <td><p>Дата на изготвяне:</p></td>
                          <td><input type="text" id="prchk_stm_date" name="prchk_stm_date" value="<?=((isset($f['prchk_stm_date2']))?HTMLFormat($f['prchk_stm_date2']):date("d.m.Y"))?>" onchange="xajax_formatBGDate('prchk_stm_date', this.value);return false;" size="20" maxlength="10" onclick="scwShow(this,event);" class="date_input" /> г.</td>
                        </tr>
                      </table></td>
                  </tr>
                </table>
	<?php
	$buff = ob_get_contents();
	ob_end_clean();
	return $buff;
}
function precheckupPulldown($worker_id=0, $precheckup_id=0) {
	global $dbInst;
	$out  = '';
	$out .= "<select id=\"precheckup_id\" name=\"precheckup_id\" onchange=\"window.location='".basename($_SERVER['PHP_SELF'])."?worker_id=$worker_id&precheckup_id='+this.value+'&".SESS_NAME."=".session_id()."'\">";
	$out .= '<option value="0"> - НОВА КАРТА - &nbsp;&nbsp;</option>';
	$flds = $dbInst->query("SELECT * FROM `medical_precheckups` WHERE `worker_id` = $worker_id ORDER BY `prchk_date` DESC, `precheckup_id` ASC");
	if(!empty($flds)) {
		$numCards = count($flds);
		$i = 1;
		foreach ($flds as $line) {
			$out .= '<option value="'.$line['precheckup_id'].'"'.(($precheckup_id==$line['precheckup_id'])?' selected="selected"':'').'>Карта '.($i++).' &nbsp;&nbsp;</option>';
		}
	}
	$out .= '</select>';
	return $out;
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
<script type="text/javascript" charset="utf-8">
//<![CDATA[
$(document).ready(function() {
	stripTable();
	if($.browser.msie) {
		$("input[type='text']:disabled,textarea:disabled,select:disabled").css("background-color", "#EEEEEE");
		$(":checkbox").css("border","none");
	}
	$("#prchk_conclusion").change(function(){
		if(this.value == '2') {
			$("#prchk_conditions").show("slow");
		} else {
			$("#prchk_conditions").val('');
			$("#prchk_conditions").hide("slow");
		}
	});
	$("a.tab").click(function(e){
		e.preventDefault();
		var tab = $(this).attr("rel");
		window.location = '<?=basename($_SERVER['PHP_SELF'])?>?worker_id=' + $("#worker_id").val() + '&precheckup_id=' + $("#precheckup_id").val() + '&tab=' + tab + '&<?=SESS_NAME.'='.session_id()?>';
	});
	$("#lnkDel").click(function(e){
		e.preventDefault();
		if(confirm('Наистина ли искате да изтриете картата от предварителния медицински преглед?')) {
			xajax_deletePrchkCheckup(<?=$precheckup_id?>, <?=$worker_id?>);
			return false;
		}
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

	<?php if($tab == 'checkups') { ?>
	$("#egn").autocomplete("autocompleter.php", {
		minChars: 1,
		extraParams: { search: "wname", firm_id: "<?=$firm_id?>" },
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
			parent.document.getElementById('TB_ajaxWindowTitle').innerHTML = 'Нанасяне на резултатите от предварителен медицински преглед на '+data[1]+', ЕГН '+data[3];
			xajax_loadWorkerInfo(data[0]);
		}
	});

	$("#wname").autocomplete("autocompleter.php", {
		minChars: 1,
		extraParams: { search: "wname", firm_id: "<?=$firm_id?>" },
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
			parent.document.getElementById('TB_ajaxWindowTitle').innerHTML = 'Нанасяне на резултатите от предварителен медицински преглед на '+data[1]+', ЕГН '+data[3];
			xajax_loadWorkerInfo(data[0]);
			$("#lnkCard").hide();
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
//]]>
</script>
<style type="text/css">
body,html {
	background-image:none;
	background-color:#EEEEEE;
}
html {
	overflow-y: scroll;
}
</style>
</head>
<body>
<div id="contentinner" align="center">
  <form id="frmPreCheckup" name="frmPreCheckup" action="javascript:void(null);">
    <input type="hidden" id="worker_id" name="worker_id" value="<?=$worker_id?>" />
    <input type="hidden" id="firm_id" name="firm_id" value="<?=$firm_id?>" />
    <?=getPopupNavigation('Предварителен мед. преглед')?>
    <table cellpadding="0" cellspacing="0" class="formBg" width="770">
      <tr>
        <th class="leftSplit rightSplit topSplit">Карта от предварителен медицински преглед &nbsp;&nbsp;<?php if($_SESSION['sess_user_level'] == 1 && !empty($precheckup_id)) { /* admin rights only */ ?><a id="lnkDel" href="javascript:void(0)" title="Изтрий прегледа"><img src="img/delete.gif" width="15" height="15" border="0" alt="Изтрий прегледа" /></a><?php } ?></th>
      </tr>
      <tr>
        <td class="leftSplit rightSplit">ЕГН:
          <?php $readonly = ($tab != 'checkups') ? ' readonly="readonly"' : ''; ?>
          <input type="text" id="egn" name="egn" value="<?=((isset($f['egn']))?HTMLFormat($f['egn']):'')?>" size="20" maxlength="10"<?=$readonly?> />
          &nbsp;&nbsp;
          Име:
          <input type="text" id="wname" name="wname" value="<?=((isset($f['lname']))?HTMLFormat($f['fname'].' '.$f['sname'].' '.$f['lname']):'')?>" size="45" maxlength="50"<?=$readonly?> />
          &nbsp;&nbsp;
          Карта: 
          <span id="sp_cards">
          <?php
          echo precheckupPulldown($worker_id, $precheckup_id);
          if(!$precheckup_id) unset($f);
          ?>
          </span>
          <div class="br"></div>
          <strong>Издадена в/от:</strong>
          <input type="text" id="prchk_author" name="prchk_author" value="<?=((isset($f['prchk_author'])&&$precheckup_id)?HTMLFormat($f['prchk_author']):'')?>" size="65" style="width:352px;"<?=$readonly?> />
          &nbsp;&nbsp; <strong>Дата:</strong>
          <input type="text" id="prchk_date" name="prchk_date" value="<?=((isset($f['prchk_date2'])&&$precheckup_id)?HTMLFormat($f['prchk_date2']):'')?>" onchange="xajax_formatBGDate('prchk_date', this.value);return false;" size="20" maxlength="10"<?=$readonly?> onclick="scwShow(this,event);" class="date_input" />
          г.
          <div class="hr"></div>
          <div id="tabs"> <a href="#" class="tab<?=(($tab=='checkups')?' active':'')?>" rel="checkups">Прегледи</a> <span id="lnks" style="visibility:<?=((isset($f['prchk_author']) && $f['prchk_author'] != '')?'visible':'hidden')?>"><a href="#" class="tab<?=(($tab=='specialists')?' active':'')?>" rel="specialists">Специалисти </a> <a href="#" class="tab<?=(($tab=='diagnosis')?' active':'')?>" rel="diagnosis">Диагнози </a></span></div>
          <script type="text/javascript">if ( (jQuery.browser.msie && jQuery.browser.version < 7)) { document.write('<br clear="all" \/>'); }</script>
          <div id="panel" class="panel" style="display:block;">
          <?php
          switch ($tab) {
          	case 'specialists':
          		echo echoSpecialists($precheckup_id);
          		break;

          	case 'diagnosis':
          		echo echoDiagnosis($precheckup_id);
          		break;

          	case 'checkups':
          	default:
          		echo echoCheckups($worker_id);
          		break;
          }
          ?></div></td>
      </tr>
      <tr>
        <td class="leftSplit rightSplit"><p align="center">
            <input type="button" id="btnSubmit" name="btnSubmit" value="Съхрани" class="nicerButtons" onclick="this.value='обработка...';this.disabled=true;xajax_processPrchkCheckup(xajax.getFormValues('frmPreCheckup'),'<?=$tab?>'); DisableEnableForm(true); return false;" />
          </p></td>
      </tr>
    </table>
  </form>
</div>
</body>
</html>
