<?php
require('includes.php');

$firm_id = (isset($_GET['firm_id']) && is_numeric($_GET['firm_id'])) ? intval($_GET['firm_id']) : 0;
$subdivision_id = (isset($_GET['subdivision_id']) && is_numeric($_GET['subdivision_id'])) ? intval($_GET['subdivision_id']) : 0;
//if(!$subdivision_id) $subdivision_id = $firm_id;
$wplace_id = (isset($_GET['wplace_id']) && is_numeric($_GET['wplace_id'])) ? intval($_GET['wplace_id']) : 0;
$row = $dbInst->getWPlaceInfo($firm_id, $subdivision_id, $wplace_id);
if(!$row) {
	die('Липсва индентификатор на работното място!');
}

$options = $dbInst->getDoctorsPulldown('doctor_pos_id');

// Xajax begin
require ('xajax/xajax_core/xajax.inc.php');
function processGiveCards($aFormValues) {
	$objResponse = new xajaxResponse();

	$objResponse->assign("btnSubmit","disabled",false);
	$objResponse->assign("btnSubmit","value","Съхрани");
	$objResponse->call("DisableEnableForm",false);

	$hasWorker = false;
	$hasDoctor = false;
	foreach ($aFormValues as $key=>$val) {
		if(preg_match('/^worker_id_(\d+)$/', $key))	$hasWorker = true;
		if('doctor_pos_id' == $key) $hasDoctor = true;
	}
	if(!$hasWorker) {
		$objResponse->script("SwitchMenu('panel1');$('#act').val('personnel');");
		$objResponse->alert('Моля, изберете работещ.');
		return $objResponse;
	}
	if(!$hasDoctor) {
		$objResponse->script("SwitchMenu('panel2');$('#act').val('checkups');");
		$objResponse->alert('Моля, изберете лекар.');
		return $objResponse;
	}

	global $dbInst;
	$IDs = $dbInst->processGiveCards($aFormValues);
	if(count($IDs)) {
		foreach ($IDs as $worker_id=>$checkup_id) {
			$objResponse->assign("checkup_id_$worker_id","value",$checkup_id);
		}
	}
	return $objResponse;
}
function downloadGiveCards($aFormValues) {
	$objResponse = new xajaxResponse();

	$objResponse->call("DisableEnableForm",false);

	$hasWorker = false;
	$hasDoctor = false;
	$IDs = array();
	foreach ($aFormValues as $key=>$val) {
		if(preg_match('/^worker_id_(\d+)$/', $key))	$hasWorker = true;
		if('doctor_pos_id' == $key) $hasDoctor = true;
		if(preg_match('/^checkup_id_(\d+)$/', $key))	{
			$IDs[$val] = $val;
		}
	}
	if(!$hasWorker) {
		$objResponse->script("SwitchMenu('panel1');$('#act').val('personnel');");
		$objResponse->alert('Моля, изберете работещ.');
		return $objResponse;
	}
	if(!$hasDoctor) {
		$objResponse->script("SwitchMenu('panel2');$('#act').val('checkups');");
		$objResponse->alert('Моля, изберете лекар.');
		return $objResponse;
	}
	global $dbInst;
	$IDs = $dbInst->processGiveCards($aFormValues);
	if(count($IDs)) {
		foreach ($IDs as $worker_id=>$checkup_id) {
			$objResponse->assign("checkup_id_$worker_id","value",$checkup_id);
		}
	}

	$objResponse->script("window.location = 'w_worker_cards_blank.php?IDs=".implode(',', $IDs)."'");

	return $objResponse;
}

$xajax = new xajax();
$xajax->registerFunction("processGiveCards");
$xajax->registerFunction("downloadGiveCards");
//$xajax->setFlag("debug",true);
$echoJS = $xajax->getJavascript('xajax/');
$xajax->processRequest();
// Xajax end

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?=SITE_NAME?></title>
<link href="styles.css" rel="stylesheet" type="text/css" media="screen" />
<?=$echoJS?>
<script type="text/javascript" src="js/RegExpValidate.js"></script>
<!-- http://jquery.com/demo/thickbox/ -->
<script type="text/javascript" src="js/jquery-latest.pack.js"></script>
<script type="text/javascript" src="js/thickbox/thickbox.js"></script>
<link rel="stylesheet" href="js/thickbox/thickbox.css" type="text/css" media="screen" />
<script type="text/javascript" charset="utf-8">
//<![CDATA[
$(document).ready(function(){
	if($.browser.msie) {
		$("input[type='text']:disabled,textarea:disabled,select:disabled").css("background-color", "#EEEEEE");
		$(":checkbox").css("border","none");
	}
	$("select.newItem").change(function(){
		if(this.value != '') {
			$("#checkupsWrapper").append('<table class="xlstable" cellpadding="0" cellspacing="0"><tr class="underline"><td align="left"><select name="doctor_pos_id[]"><?php foreach ($options as $field) { ?><option value="<?=$field['doctor_pos_id']?>"'+((this.value==<?=$field['doctor_pos_id']?>)?' selected="selected"':'')+'><?=HTMLFormat($field['doctor_pos_name'])?> &nbsp;&nbsp;<\/option><?php } ?><\/select><\/td><td width="30" align="center"><a href="javascript:void(null);" onclick="var answ=confirm(\'Наистина ли искате да изтриете прегледа?\');if(answ) { $(this).parent().parent().parent().parent().remove(); } return false;"><img src="img/delete.gif" alt="Изтрий" width="15" height="15" border="0" \/><\/a><\/td><\/tr><\/table>');
			this.value = '';
		}
	});
});
function SwitchMenu(obj){
	if(document.getElementById){
		var el = document.getElementById(obj);
		var ar = document.getElementById("frmFirm").getElementsByTagName("div");
		if(el.style.display == "block") return;
		if(el.style.display != "block") {
			for (var i=0; i<ar.length; i++) {
				if (ar[i].className == "panel") {
					ar[i].style.display = "none";
					var suff = ar[i].id.slice(5);
					document.getElementById('tab'+suff).className = 'tab';
				}
			}
			el.style.display = "block";
			var suff = el.id.slice(5);
			document.getElementById('tab'+suff).className = 'tab active';
		} else {
			el.style.display = "none";
			var suff = el.id.slice(5);
			document.getElementById('tab'+suff).className = 'tab';
		}
	}
}
function hasWorkers(){
	var k = 0;
	$("input[@type='checkbox']").each(function(i){
		if(this.checked) k = 1;
	});
	return k;
}
function hasCheckups() {
	if($("#checkupsWrapper").html() == '') {
		return false;
	}
	return true;
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
  <div style="width:800px">
    <form id="frmFirm" name="frmFirm" action="javascript:void(null);">
      <input type="hidden" id="firm_id" name="firm_id" value="<?=$firm_id?>" />
      <input type="hidden" id="subdivision_id" name="subdivision_id" value="<?=$subdivision_id?>" />
      <input type="hidden" id="wplace_id" name="wplace_id" value="<?=$wplace_id?>" />
      <input type="hidden" id="act" name="act" value="personnel" />
      <table cellpadding="0" cellspacing="0" class="formBg" width="770">
        <tr>
          <td colspan="2" class="leftSplit rightSplit topSplit"><div align="right"><a href="javascript:void(null);" onclick="xajax_downloadGiveCards(xajax.getFormValues('frmFirm'));DisableEnableForm(true);return false;" title="Отпечатване на картите за профилактичен медицински преглед"><img src="img/medical3.gif" width="16" height="16" border="0" alt="" /> Карти за профилактичен преглед</a> | <!--<a href="#" title="Отпечатване на списък на подлежащите на профилактичен медицински преглед"><img src="img/medical3.gif" width="16" height="16" border="0" alt="" /> Списък на подлежащите на профилактичен преглед</a>-->
            </div></td>
        </tr>
        <tr>
          <td class="leftSplit rightSplit">Фирма:</td>
          <td class="rightSplit"><?=HTMLFormat($row['name'])?></td>
        </tr>
        <tr>
          <td class="leftSplit rightSplit">Подразделение:</td>
          <td class="rightSplit"><?=HTMLFormat($row['subdivision_name'])?></td>
        </tr>
        <tr>
          <td class="leftSplit rightSplit"><p class="primary"><strong>Работно място:</strong></p></td>
          <td class="rightSplit"><strong><?=HTMLFormat($row['wplace_name'])?></strong></td>
        </tr>
        <tr>
          <td colspan="2" class="leftSplit rightSplit">Подлежащи на задължителен периодичен медицински преглед през
            <?php
            $i = 0;
            if (!$wplace_id)
            $sql = sprintf("SELECT *, strftime('%%d.%%m.%%Y', date_curr_position_start, 'localtime') AS date_curr_position_start2 FROM workers WHERE firm_id = %d AND is_active = '1' AND w.date_retired = '' ORDER BY fname, lname, worker_id", $firm_id);
            else
            $sql = sprintf("SELECT w.*,
							strftime('%%d.%%m.%%Y', w.birth_date, 'localtime') AS birth_date2
							FROM workers w
							LEFT JOIN firm_struct_map m ON (m.map_id = w.map_id)
							WHERE m.firm_id = %d
							AND w.is_active = '1'
							AND w.date_retired = ''
							AND m.subdivision_id = %d
							AND m.wplace_id = %d
							GROUP BY w.worker_id
							ORDER BY w.fname, w.lname, w.worker_id", $firm_id, $subdivision_id, $wplace_id);
			$rows = $dbInst->query($sql);
            ?>
            <select id="year_to_be_done" name="year_to_be_done">
              <option value="<?=(date('Y')+1)?>"><?=(date('Y')+1)?> &nbsp;&nbsp;</option>
              <option value="<?=date('Y')?>" selected="selected"><?=date('Y')?> &nbsp;&nbsp;</option>
              <option value="<?=(date('Y')-1)?>"><?=(date('Y')-1)?> &nbsp;&nbsp;</option>
            </select>
            г. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <input id="btnSubmit" name="btnSubmit" value="Съхрани" class="nicerButtons" onclick="this.disabled=true;this.value='обработка...';xajax_processGiveCards(xajax.getFormValues('frmFirm'));DisableEnableForm(true);return false;" type="button"<?=((!($rows))?' disabled="disabled"':'')?> /></td>
        </tr>
      </table>
      <div class="br"></div>
      <div style="width: 765px;">
        <div id="tabs"> <a id="tab1" href="#" onclick="SwitchMenu('panel1');$('#act').val('personnel');" class="tab active">Персонал </a> <a id="tab2" href="#" onclick="SwitchMenu('panel2');$('#act').val('checkups');" class="tab">Прегледи </a></div>
        <div class="clear"></div>
        <div id="panel1" class="panel" style="overflow: hidden; display: block;">
          <div id="factorsWrapper" style="overflow: auto; width: 750px; height: 220px;">
            <table class="xlstable" cellpadding="0" cellspacing="0">
              <tbody>
                <tr>
                  <th colspan="2">Име</th>
                  <th>год.</th>
                  <th colspan="2">Бележки</th>
                </tr>
                <?php
                if($rows) {
                	foreach ($rows as $row) {
                ?>
                <tr class="underline">
                  <th><?=++$i?></th>
                  <td align="left"> <?=HTMLFormat($row['fname'].' '.$row['sname'].' '.$row['lname'])?> </td>
                  <th><?=(($row['birth_date'] != '')?worker_age($row['birth_date2'], date("d.m.Y")):'')?></th>
                  <th width="6%"><input type="checkbox" id="worker_id_<?=$row['worker_id']?>" name="worker_id_<?=$row['worker_id']?>" value="<?=$row['worker_id']?>" /></th>
                  <td width="36%"><input id="notes_<?=$row['worker_id']?>" name="notes_<?=$row['worker_id']?>" value="" style="width: 260px;" type="text" />
                    <input type="hidden" id="checkup_id_<?=$row['worker_id']?>" name="checkup_id_<?=$row['worker_id']?>" value="0" /></td>
                </tr>
                <?php }} else { ?>
                <tr class="underline">
                  <td colspan="5">Няма въведени работещи на това работно място.</td>
                </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
          <table cellpadding="0" cellspacing="0" class="formBg" width="99%">
            <tr>
              <td class="leftSplit rightSplit topSplit"><span class="labeltext">Брой работещи:</span> <?=$i?> &nbsp;</td>
            </tr>
          </table>
        </div>
        <div id="panel2" class="panel" style="display: none;">
          <table class="xlstable" cellpadding="0" cellspacing="0">
            <tbody>
              <tr>
                <th colspan="2">Лекар</th>
              </tr>
              <tr>
                <td colspan="2"><div id="checkupsWrapper"></div></td>
              </tr>
              <tr>
                <td align="left"><select id="doctor_pos_id_0" name="doctor_pos_id_0" class="newItem">
                    <option value=""> &nbsp;&nbsp;</option>
                    <?php
                    foreach ($options as $field) {
                    	echo '<option value="'.$field['doctor_pos_id'].'">'.HTMLFormat($field['doctor_pos_name']).' &nbsp;&nbsp;</option>';
                    }
                    ?>
                  </select>
                </td>
                <td align="center">&nbsp;</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </form>
  </div>
</div>
</body>
</html>
