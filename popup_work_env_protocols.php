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

// Xajax begin
require ('xajax/xajax_core/xajax.inc.php');
function loadWorkEnvProtocols($wplace_id) {
	$objResponse = new xajaxResponse();

	global $dbInst;
	global $firm_id;
	global $subdivision_id;
	ob_start();
	$factors = $dbInst->getFactors();
	?>
          <table class="xlstable" cellpadding="0" cellspacing="0">
            <tbody>
              <tr>
                <th>Фактор</th>
                <th>Ниво</th>
                <th>min гранична ст.</th>
                <th>max гранична ст.</th>
                <th>МЕ</th>
                <th>Отклонение</th>
                <th width="30">&nbsp;</th>
              </tr>
              <?php
              $i = 0;
              $rows = $dbInst->getWorkEnvProtocols($firm_id, $subdivision_id, $wplace_id);
              if($rows) {
              	foreach ($rows as $row) {
              		$map_id = $row['map_id'];
              		$prot_id = $row['prot_id'];
              ?>
              <tr>
                <td><input type="hidden" id="prot_id_<?=$map_id?>" name="prot_id_<?=$map_id?>" value="<?=$prot_id?>" />
                  <select name="factor_id_<?=$map_id?>" id="factor_id_<?=$map_id?>" style="width:170px;" onchange="xajax_populateValues(this.value,'<?=$prot_id?>');return false;">
                	<?php
                	foreach ($factors as $factor) {
                		echo '<option value="'.$factor['factor_id'].'"'.(($factor['factor_id']==$row['factor_id'])?' selected="selected"':'').'>'.HTMLFormat($factor['factor_name']).'</option>';
                	}
                    ?>
                  </select></td>
                <td><input type="text" id="level_<?=$map_id?>" name="level_<?=$map_id?>" value="<?=HTMLFormat($row['level'])?>" style="width:80px;" /></td>
                <td><input type="text" id="pdk_min_<?=$map_id?>" name="pdk_min_<?=$map_id?>" value="<?=HTMLFormat($row['pdk_min'])?>" style="width:80px;" /></td>
                <td><input type="text" id="pdk_max_<?=$map_id?>" name="pdk_max_<?=$map_id?>" value="<?=HTMLFormat($row['pdk_max'])?>" style="width:80px;" /></td>
                <td><input type="text" id="factor_dimension_<?=$map_id?>" name="factor_dimension_<?=$map_id?>" value="<?=HTMLFormat($row['factor_dimension'])?>" style="width:80px;" /></td>
                <td><?=calcDeviation($row['pdk_min'], $row['pdk_max'], $row['level'])?></td>
                <td width="30" align="center"><a href="javascript:void(null);" onclick="var answ=confirm('Наистина ли искате да изтриете протола от измерването?');if(answ){xajax_removeWorkEnvProtocol(<?=$map_id?>,<?=$prot_id?>,<?=$wplace_id?>);}return false;" title="Изтриване на протокола"><img src="img/delete.gif" border="0" width="15" height="15" alt="Изтриване на протокола" /></a></td>
              </tr>
              <tr class="underline">
                <td>&nbsp;</td>
                <td colspan="6"><strong>Протокол №:</strong>
                  <input type="text" id="prot_num_<?=$map_id?>" name="prot_num_<?=$map_id?>" value="<?=HTMLFormat($row['prot_num'])?>" />
                  <strong>от дата:</strong>
                  <input type="text" id="prot_date_<?=$map_id?>" name="prot_date_<?=$map_id?>" value="<?=HTMLFormat($row['prot_date_h'])?>" onchange="xajax_formatProtDate(this.value, '<?=$map_id?>');return false;" onclick="scwShow(this,event);" class="date_input" /></td>
              </tr>
              <?php
              $i++;
              	}
              }
              ?>
              <!-- new protocol -->
              <tr<?=((!($i%2))?'':' class="alternate"')?>>
                <td><input type="hidden" id="prot_id_0" name="prot_id_0" value="0" />
                  <select name="factor_id_0" id="factor_id_0" style="width:170px;" class="newItem" onchange="xajax_populateValues(this.value,'0');return false;">
                    <option value="0">&nbsp;</option>
                    <?php
                    foreach ($factors as $factor) {
                    	echo '<option value="'.$factor['factor_id'].'">'.HTMLFormat($factor['factor_name']).'</option>';
                    }
                    ?>
                  </select></td>
                <td><input type="text" id="level_0" name="level_0" value="" class="newItem" style="width:80px;" /></td>
                <td><input type="text" id="pdk_min_0" name="pdk_min_0" value="" class="newItem" style="width:80px;" readonly="readonly" /></td>
                <td><input type="text" id="pdk_max_0" name="pdk_max_0" value="" class="newItem" style="width:80px;" readonly="readonly" /></td>
                <td><input type="text" id="factor_dimension_0" name="factor_dimension_0" value="" class="newItem" readonly="readonly" style="width:80px;" /></td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
              </tr>
              <tr<?=((!($i%2))?'':' class="alternate"')?>>
                <td>&nbsp;</td>
                <td colspan="6"><strong>Протокол №:</strong>
                  <input type="text" id="prot_num_0" name="prot_num_0" value="" class="newItem" />
                  <strong>от дата:</strong>
                  <input type="text" id="prot_date_0" name="prot_date_0" value="" class="newItem date_input" onchange="xajax_formatProtDate(this.value, '0');return false;" onclick="scwShow(this,event);" /></td>
              </tr>
            </tbody>
          </table>
	<?php
	$buff = ob_get_contents();
	ob_end_clean();
	$objResponse->assign("factorsWrapper","innerHTML",$buff);

	return $objResponse;
}
function processWorkEnvProtocols($aFormValues) {
	$objResponse = new xajaxResponse();

	$objResponse->assign("btnSubmit","disabled",false);
	$objResponse->assign("btnSubmit","value","Съхрани");
	$objResponse->assign("btnSubmit2","disabled",false);
	$objResponse->assign("btnSubmit2","value","Съхрани");
	$objResponse->call("DisableEnableForm",false);

	foreach ($aFormValues as $key=>$val) {
		if(preg_match('/^prot_num_(\d+)$/', $key, $matches)) {
			if($matches[1] != '0' && trim($val) == '') {
				$objResponse->alert("Моля, въведете номер на протокола.");
				return $objResponse;
			}
		}
	}
	global $dbInst;
	$dbInst->processWorkEnvProtocols($aFormValues);
	$map_id = $dbInst->processWPlaceFactors($aFormValues);
	$objResponse->assign('map_id','value',$map_id);
	$objResponse->loadcommands(loadWorkEnvProtocols($aFormValues['wplace_id']));

	return $objResponse;
}
function removeWorkEnvProtocol($map_id, $prot_id, $wplace_id) {
	$objResponse = new xajaxResponse();

	global $dbInst;
	$dbInst->removeWorkEnvProtocol($map_id, $prot_id);
	$objResponse->loadcommands(loadWorkEnvProtocols($wplace_id));

	return $objResponse;
}
$xajax = new xajax();
$xajax->registerFunction("populateValues");	// Shared
$xajax->registerFunction("loadWorkEnvProtocols");
$xajax->registerFunction("processWorkEnvProtocols");
$xajax->registerFunction("removeWorkEnvProtocol");
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
<script type="text/javascript" src="js/jquery-latest.pack.js"></script>
<script type="text/javascript" charset="utf-8">
//<![CDATA[
function calcDeviation(el) {
	var arr = el.id.split("_");
	if(isNaN(el.value)) return false;
	
	var level = parseFloat(el.value);
	var pdk_min = parseFloat($("#pdk_min_"+arr[1]).val());
	var pdk_max = parseFloat($("#pdk_max_"+arr[1]).val());
	if(level >= pdk_min && level <= pdk_max) {
		$("#deviation_"+arr[1]).val(0);
	}
	else if(level < pdk_min) {
		$("#deviation_"+arr[1]).val(pdk_min - level);
	}
	else if(level > pdk_max) {
		$("#deviation_"+arr[1]).val(level - pdk_max);
	}
	else {
		$("#deviation_"+arr[1]).val(0);
	}
	return false;
}
function SwitchMenu(obj){
	if(document.getElementById){
		var el = document.getElementById(obj);
		var ar = document.getElementById("frmFirm").getElementsByTagName("div");
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
//]]>
</script>

<!-- Auto-completer begin -->
<!-- http://dev.jquery.com/view/trunk/plugins/autocomplete/ -->
<!-- <script type="text/javascript" src="js/autocompleter/jquery.js"></script> -->
<script type='text/javascript' src='js/autocompleter/jquery.bgiframe.min.js'></script>
<script type='text/javascript' src='js/autocompleter/jquery.dimensions.js'></script>
<script type='text/javascript' src='js/autocompleter/jquery.ajaxQueue.js'></script>
<script type='text/javascript' src='js/autocompleter/jquery.autocomplete.js'></script>
<script type='text/javascript' src='js/autocompleter/localdata.js'></script>
<!-- <link rel="stylesheet" type="text/css" href="js/autocompleter/main.css" /> -->
<link rel="stylesheet" type="text/css" href="js/autocompleter/jquery.autocomplete.css" />
<script type="text/javascript">
//<![CDATA[
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

	$("#fact_dust").autocomplete("autocompleter.php", {
		minChars: 0,
		extraParams: { search: "fact_dust" },
		width: 260,
		scroll: true,
		scrollHeight: 300,
		selectFirst: false
	});
	$("#fact_dust").result(function(event, data, formatted) {
		if (data) $("#fact_dust").val(data[0]);
	});
	$("#fact_chemicals").autocomplete("autocompleter.php", {
		minChars: 0,
		extraParams: { search: "fact_chemicals" },
		width: 260,
		scroll: true,
		scrollHeight: 300,
		selectFirst: false
	});
	$("#fact_chemicals").result(function(event, data, formatted) {
		if (data) $("#fact_chemicals").val(data[0]);
	});
	$("#fact_biological").autocomplete("autocompleter.php", {
		minChars: 0,
		extraParams: { search: "fact_biological" },
		width: 260,
		scroll: true,
		scrollHeight: 300,
		selectFirst: false
	});
	$("#fact_biological").result(function(event, data, formatted) {
		if (data) $("#fact_biological").val(data[0]);
	});
	$("#fact_work_pose").autocomplete("autocompleter.php", {
		minChars: 0,
		extraParams: { search: "fact_work_pose" },
		width: 260,
		scroll: true,
		scrollHeight: 300,
		selectFirst: false
	});
	$("#fact_work_pose").result(function(event, data, formatted) {
		if (data) $("#fact_work_pose").val(data[0]);
	});
	$("#fact_manual_weights").autocomplete("autocompleter.php", {
		minChars: 0,
		extraParams: { search: "fact_manual_weights" },
		width: 260,
		scroll: true,
		scrollHeight: 300,
		selectFirst: false
	});
	$("#fact_manual_weights").result(function(event, data, formatted) {
		if (data) $("#fact_manual_weights").val(data[0]);
	});
	$("#fact_monotony").autocomplete("autocompleter.php", {
		minChars: 0,
		extraParams: { search: "fact_monotony" },
		width: 260,
		scroll: true,
		scrollHeight: 300,
		selectFirst: false
	});
	$("#fact_monotony").result(function(event, data, formatted) {
		if (data) $("#fact_monotony").val(data[0]);
	});
	$("#fact_work_regime").autocomplete("autocompleter.php", {
		minChars: 0,
		extraParams: { search: "fact_work_regime" },
		width: 260,
		scroll: true,
		scrollHeight: 300,
		selectFirst: false
	});
	$("#fact_work_regime").result(function(event, data, formatted) {
		if (data) $("#fact_work_regime").val(data[0]);
	});
	$("#fact_work_hours").autocomplete("autocompleter.php", {
		minChars: 0,
		extraParams: { search: "fact_work_hours" },
		width: 260,
		scroll: true,
		scrollHeight: 300,
		selectFirst: false
	});
	$("#fact_work_hours").result(function(event, data, formatted) {
		if (data) $("#fact_work_hours").val(data[0]);
	});
	$("#fact_work_and_break").autocomplete("autocompleter.php", {
		minChars: 0,
		extraParams: { search: "fact_work_and_break" },
		width: 260,
		scroll: true,
		scrollHeight: 300,
		selectFirst: false
	});
	$("#fact_work_and_break").result(function(event, data, formatted) {
		if (data) $("#fact_work_and_break").val(data[0]);
	});
	$("#fact_nervous").autocomplete("autocompleter.php", {
		minChars: 0,
		extraParams: { search: "fact_nervous" },
		width: 260,
		scroll: true,
		scrollHeight: 300,
		selectFirst: false
	});
	$("#fact_nervous").result(function(event, data, formatted) {
		if (data) $("#fact_nervous").val(data[0]);
	});
	$("#fact_other").autocomplete("autocompleter.php", {
		minChars: 0,
		extraParams: { search: "fact_other" },
		width: 260,
		scroll: true,
		scrollHeight: 300,
		selectFirst: false
	});
	$("#fact_other").result(function(event, data, formatted) {
		if (data) $("#fact_other").val(data[0]);
	});
});
//]]>
</script>
<!-- Auto-completer end -->

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
  <div id="tabs"> <a id="tab1" href="#" onclick="SwitchMenu('panel1')" class="tab active">Протоколи от изпитвания </a> <a id="tab2" href="#"  onclick="SwitchMenu('panel2')" class="tab">Ръчно въвеждане на фактори </a></div>
  <div class="clear"></div>
  <form id="frmFirm" name="frmFirm" action="javascript:void(null);">
    <input type="hidden" id="firm_id" name="firm_id" value="<?=$firm_id?>" />
    <input type="hidden" id="subdivision_id" name="subdivision_id" value="<?=$subdivision_id?>" />
    <input type="hidden" id="wplace_id" name="wplace_id" value="<?=$wplace_id?>" />
    <div id="panel1" class="panel" style="display:block">
      <table cellpadding="0" cellspacing="0" class="formBg" width="770">
        <tr>
          <td class="leftSplit rightSplit topSplit">Фирма:</td>
          <td class="rightSplit topSplit"><?=HTMLFormat($row['name'])?>&nbsp;</td>
        </tr>
        <tr>
          <td class="leftSplit rightSplit">Подразделение:</td>
          <td class="rightSplit"><?=HTMLFormat($row['subdivision_name'])?>&nbsp;</td>
        </tr>
        <tr>
          <td class="leftSplit rightSplit"><p class="primary"><strong>Работно място:</strong></p></td>
          <td class="rightSplit"><strong><?=HTMLFormat($row['wplace_name'])?>&nbsp;</strong></td>
        </tr>
        <tr>
          <td class="leftSplit">&nbsp;</td>
          <td class="rightSplit"><input type="button" id="btnSubmit" name="btnSubmit" value="Съхрани" class="nicerButtons" onclick="this.disabled=true;this.value='обработка...';xajax_processWorkEnvProtocols(xajax.getFormValues('frmFirm'));DisableEnableForm(true);return false;" />
          </td>
        </tr>
      </table>
      <div class="hr"></div>
      <div id="factorsWrapper" style="width:765px;height:300px;overflow:scroll;">Зареждане...
          <script type="text/javascript">
          xajax_loadWorkEnvProtocols(<?=$wplace_id?>);
          </script>
      </div>
    </div>
    <?php $field = $dbInst->getWPlaceFactorsInfo($firm_id, $subdivision_id, $wplace_id); ?>
    <input type="hidden" id="map_id" name="map_id" value="<?=((isset($field['map_id']))?$field['map_id']:'0')?>" />
      <div id="panel2" class="panel" style="display:none">
        <table border="0" cellpadding="0" cellspacing="0" class="xlstable" width="100%">
          <tr>
            <td>Прах - вид:<br />
              <textarea id="fact_dust" name="fact_dust" cols="40" rows="3"><?=((isset($field['fact_dust']))?HTMLFormat($field['fact_dust']):'')?></textarea></td>
            <td>Химични агенти - вид:<br />
              <textarea id="fact_chemicals" name="fact_chemicals" cols="40" rows="3"><?=((isset($field['fact_chemicals']))?HTMLFormat($field['fact_chemicals']):'')?></textarea></td>
            <td>Биологични агенти:<br />
              <textarea id="fact_biological" name="fact_biological" cols="40" rows="3"><?=((isset($field['fact_biological']))?HTMLFormat($field['fact_biological']):'')?></textarea>
            </td>
          </tr>
          <tr>
            <td>Работна поза:<br />
              <textarea id="fact_work_pose" name="fact_work_pose" cols="40" rows="3"><?=((isset($field['fact_work_pose']))?HTMLFormat($field['fact_work_pose']):'')?></textarea>
            </td>
            <td>Ръчна работа с тежести:<br />
              <textarea id="fact_manual_weights" name="fact_manual_weights" cols="40" rows="3"><?=((isset($field['fact_manual_weights']))?HTMLFormat($field['fact_manual_weights']):'')?></textarea>
            </td>
            <td>Двигателна монотонна работа:<br />
              <textarea id="fact_monotony" name="fact_monotony" cols="40" rows="3"><?=((isset($field['fact_monotony']))?HTMLFormat($field['fact_monotony']):'')?></textarea>
            </td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td align="center">Организация на труда</td>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td>Режим на работа:<br />
              <textarea id="fact_work_regime" name="fact_work_regime" cols="40" rows="3"><?=((isset($field['fact_work_regime']))?HTMLFormat($field['fact_work_regime']):'')?></textarea>
            </td>
            <td>Продължителност на работното време:<br />
              <textarea id="fact_work_hours" name="fact_work_hours" cols="40" rows="3"><?=((isset($field['fact_work_hours']))?HTMLFormat($field['fact_work_hours']):'')?></textarea>
            </td>
            <td>Физиолог. режими на труд и почивка:<br />
              <textarea id="fact_work_and_break" name="fact_work_and_break" cols="40" rows="3"><?=((isset($field['fact_work_and_break']))?HTMLFormat($field['fact_work_and_break']):'')?></textarea>
            </td>
          </tr>
          <tr>
            <td>Нервно-психично напрежение:<br />
              <textarea id="fact_nervous" name="fact_nervous" cols="40" rows="3"><?=((isset($field['fact_nervous']))?HTMLFormat($field['fact_nervous']):'')?></textarea>
            </td>
            <td>Други:<br />
              <textarea id="fact_other" name="fact_other" cols="40" rows="3"><?=((isset($field['fact_other']))?HTMLFormat($field['fact_other']):'')?></textarea>
            </td>
            <td align="center"><input type="button" id="btnSubmit2" name="btnSubmit2" value="Съхрани" class="nicerButtons" onclick="this.disabled=true;this.value='обработка...';xajax_processWorkEnvProtocols(xajax.getFormValues('frmFirm'));DisableEnableForm(true);return false;" /></td>
          </tr>
        </table>
      </div>
  </form>
</div>
</div>
</body>
</html>
