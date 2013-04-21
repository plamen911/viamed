<?php
require('includes.php');

$firm_id = (isset($_GET['firm_id']) && is_numeric($_GET['firm_id'])) ? intval($_GET['firm_id']) : 0;
$f = $dbInst->getFirmInfo($firm_id);
if(!$f) {
	die('Липсва индентификатор на фирмата!');
}

// Xajax begin
require ('xajax/xajax_core/xajax.inc.php');
//TODO - Ajax functions comes below this line
function processGroupEnvProtocols($aFormValues) {
	$objResponse = new xajaxResponse();

	$objResponse->assign("btnSubmit","disabled",false);
	$objResponse->assign("btnSubmit","value","Съхрани");
	$objResponse->call("DisableEnableForm",false);

	if(!count(preg_grep('/^wplace_id_(\d+)$/', array_keys($aFormValues)))) {
		$objResponse->alert("Моля, изберете работни места.");
		return $objResponse;
	}
	if(trim($aFormValues['factor_id_0']) == '0') {
		$objResponse->alert("Моля, изберете фактор на работната среда.");
		return $objResponse;
	}

	global $dbInst;
	$dbInst->processGroupEnvProtocols($aFormValues);
	$objResponse->alert("Факторите за избраните работни места бяха успешно въведени.");
	$objResponse->call("clearForm", "frmFirm");

	return $objResponse;
}
$xajax = new xajax();
$xajax->registerFunction("processGroupEnvProtocols");
$xajax->registerFunction("populateValues");	// Shared
$xajax->registerFunction("formatProtDate");	// Shared
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
<script type="text/javascript" src="scw.js"></script>
<!-- http://jquery.com/demo/thickbox/ -->
<script type="text/javascript" src="js/jquery-latest.pack.js"></script>
<script type="text/javascript" charset="utf-8">
//<![CDATA[
$(document).ready(function() {
	if($.browser.msie) {
		$("input[type='text']:disabled,textarea:disabled,select:disabled").css("background-color", "#EEEEEE");
		$(":checkbox").css("border","none");
	}
	// Strip table
	$(".xlstable tr:even").addClass("alternate");
	// Hightlight table rows
	$(".xlstable tr").not(".notover").hover(function() {
		$(this).addClass("over");
	},function() {
		$(this).removeClass("over");
	});
});
function clearForm(itsForm) {
	try {
		var form = document.forms[itsForm];
		for(var i = 0; i < form.elements.length; i++) {
			var element = form.elements[i];
			// Don't clean up the fields below
			//if(element.name == 'worker_id' || element.name == 'firm_id' || element.name == 'wname' || element.name == 'egn') continue;
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
<style type="text/css">
body, html {
	background-image:none;
	background-color:#EEEEEE;
}
</style>
</head>
<body>
<div id="contentinner" align="center">
  <div style="width:780px">
    <form id="frmFirm" action="javascript:void(null);">
      <input type="hidden" id="firm_id" name="firm_id" value="<?=$firm_id?>" />
      <table cellpadding="0" cellspacing="0" class="formBg" width="770">
        <tr>
          <th colspan="4" class="leftSplit rightSplit topSplit">Работни места в <?=HTMLFormat($f['name'].' - '.$f['location_name'].', '.$f['address'])?></th>
          <th class="rightSplit topSplit">Избор</th>
        </tr>
        <?php
        $rows = $dbInst->getWorkPlaces($firm_id);
        foreach ($rows as $row) {
        ?>
        <tr>
          <td colspan="4" align="left" class="leftSplit rightSplit"><?=HTMLFormat($row['wplace_name'])?></td>
          <td class="rightSplit"><div align="center"><input type="checkbox" id="wplace_id_<?=$row['wplace_id']?>" name="wplace_id_<?=$row['wplace_id']?>" value="<?=$row['wplace_id']?>" /></div></td>
        </tr>
            <?php } ?>
        <tr class="notover">
          <td colspan="4" class="leftSplit rightSplit"><p align="center">
              <input type="button" id="btnSubmit" name="btnSubmit" value="Съхрани" class="nicerButtons" onclick="this.disabled=true;this.value='обработка...';xajax_processGroupEnvProtocols(xajax.getFormValues('frmFirm'));DisableEnableForm(true);return false;" />
            </p></td>
          <td class="rightSplit">&nbsp;</td>
        </tr>
      </table>
      <!--<div class="hr"></div>-->
      <table class="xlstable" cellpadding="0" cellspacing="0" width="770">
        <tbody>
          <tr>
            <th>Фактор</th>
            <th>Ниво</th>
            <th>min ПДК</th>
            <th>max ПДК</th>
            <th>МЕ</th>
          </tr>
          <!-- new protocol -->
          <tr>
            <td align="left"><select name="factor_id_0" id="factor_id_0" style="width: 170px;" class="newItem" onchange="xajax_populateValues(this.value,'0');return false;">
                <option value="0">&nbsp;</option>
                <?php
                  $factors = $dbInst->getFactors();
                  foreach ($factors as $factor) {
                  	echo '<option value="'.$factor['factor_id'].'"'.(($factor['factor_id']==$row['factor_id'])?' selected="selected"':'').'>'.HTMLFormat($factor['factor_name']).'</option>';
                  }
                ?>
              </select></td>
            <td align="left"><input id="level_0" name="level_0" value="" class="newItem" style="width: 80px;" type="text" /></td>
            <td align="left"><input id="pdk_min_0" name="pdk_min_0" value="" class="newItem" style="width: 80px;" readonly="readonly" type="text" /></td>
            <td align="left"><input id="pdk_max_0" name="pdk_max_0" value="" class="newItem" style="width: 80px;" readonly="readonly" type="text" /></td>
            <td><input id="factor_dimension_0" name="factor_dimension_0" value="" class="newItem" readonly="readonly" style="width: 80px;" type="text" /></td>
          </tr>
          <tr>
            <td align="left">&nbsp;</td>
            <td align="left" colspan="5"><strong>Протокол №:</strong>
              <input id="prot_num_0" name="prot_num_0" value="" class="newItem" type="text" />
              <strong>от дата:</strong>
              <input id="prot_date_0" name="prot_date_0" value="" class="newItem date_input" onchange="xajax_formatProtDate(this.value, '0');return false;" onclick="scwShow(this,event);" type="text" /> г.</td>
          </tr>
        </tbody>
      </table>
      <p>&nbsp;</p>
      <p>&nbsp;</p>
    </form>
  </div>
</div>
</body>
</html>