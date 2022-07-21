<?php
require('includes.php');

$worker_id = (isset($_GET['worker_id']) && is_numeric($_GET['worker_id'])) ? intval($_GET['worker_id']) : 0;
$f = $dbInst->getWorkerInfo($worker_id);
if(!$f) {
	die('Липсва индентификатор на работещия!');
}

// Xajax begin
require ('xajax/xajax_core/xajax.inc.php');
function processAnamnesis($aFormValues) {
	$objResponse = new xajaxResponse();

	$objResponse->assign("btnSubmit","disabled",false);
	$objResponse->assign("btnSubmit","value","Съхрани");
	$objResponse->call("DisableEnableForm",false);

	global $dbInst;

	$var_list = array('worker_id' => 'worker_id', 'family_hypertonia' => 'family_hypertonia', 'family_heart_disease' => 'family_heart_disease', 'family_diabetis' => 'family_diabetis', 'family_other_disease' => 'family_other_disease');
	while (list($var, $param) = @each($var_list)) {
		if (isset($aFormValues[$param]))
		$$var = $dbInst->checkStr($aFormValues[$param]);
	}

	$dbInst->query("UPDATE `workers` SET `family_hypertonia` = '$family_hypertonia', `family_heart_disease` = '$family_heart_disease', `family_diabetis` = '$family_diabetis', `family_other_disease` = '$family_other_disease' WHERE `worker_id` = $worker_id");

	return $objResponse;
}
$xajax = new xajax();
$xajax->registerFunction("processAnamnesis");
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
<script type="text/javascript" src="js/jquery-latest.pack.js"></script>
<style type="text/css">
body,html {
	background-image:none;
	background-color:#EEEEEE;
}
</style>
</head>
<body style="overflow:hidden;">
<div id="contentinner" align="center">
  <form id="frmPreCheckup" action="javascript:void(null);">
    <input type="hidden" id="worker_id" name="worker_id" value="<?=$worker_id?>" />
    <?=getPopupNavigation('Фамилна анамнеза')?>
    <table cellpadding="0" cellspacing="0" class="formBg">
      <tr>
        <td class="topSplit leftSplit rightSplit">Хипертония:
          <select id="family_hypertonia" name="family_hypertonia">
            <option value=""> &nbsp;&nbsp;</option>
            <option value="ДА"<?=((isset($f['family_hypertonia'])&&'ДА'==$f['family_hypertonia'])?' selected="selected"':'')?>>ДА &nbsp;&nbsp;</option>
            <option value="НЕ"<?=((isset($f['family_hypertonia'])&&'НЕ'==$f['family_hypertonia'])?' selected="selected"':'')?>>НЕ &nbsp;&nbsp;</option>
          </select></td>
      </tr>
      <tr>
        <td class="leftSplit rightSplit">Болести на сърцето:
          <select id="family_heart_disease" name="family_heart_disease">
            <option value=""> &nbsp;&nbsp;</option>
            <option value="ДА"<?=((isset($f['family_heart_disease'])&&'ДА'==$f['family_heart_disease'])?' selected="selected"':'')?>>ДА (исхемия, инфаркт, ревматизъм и др.) &nbsp;&nbsp;</option>
            <option value="НЕ"<?=((isset($f['family_heart_disease'])&&'НЕ'==$f['family_heart_disease'])?' selected="selected"':'')?>>НЕ &nbsp;&nbsp;</option>
          </select></td>
      </tr>
      <tr>
        <td class="leftSplit rightSplit">Захарна болест:
          <select id="family_diabetis" name="family_diabetis">
            <option value=""> &nbsp;&nbsp;</option>
            <option value="ДА"<?=((isset($f['family_diabetis'])&&'ДА'==$f['family_diabetis'])?' selected="selected"':'')?>>ДА &nbsp;&nbsp;</option>
            <option value="НЕ"<?=((isset($f['family_diabetis'])&&'НЕ'==$f['family_diabetis'])?' selected="selected"':'')?>>НЕ &nbsp;&nbsp;</option>
          </select></td>
      </tr>
      <tr>
        <td class="leftSplit rightSplit">Други заболявания:<br />
          <textarea id="family_other_disease" name="family_other_disease" cols="66" rows="3"><?=((isset($f['family_other_disease']))?HTMLFormat($f['family_other_disease']):'')?></textarea></td>
      </tr>
      <tr>
        <td class="leftSplit rightSplit"><p align="center">
            <input type="button" id="btnSubmit" name="btnSubmit" value="Съхрани" class="nicerButtons" onclick="this.value='обработка...';this.disabled=true;xajax_processAnamnesis(xajax.getFormValues('frmPreCheckup')); DisableEnableForm(true); return false;" />
          </p></td>
      </tr>
    </table>
  </form>
</div>
</body>
</html>
