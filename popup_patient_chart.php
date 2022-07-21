<?php
require('includes.php');

$firm_id = (isset($_GET['firm_id']) && is_numeric($_GET['firm_id'])) ? intval($_GET['firm_id']) : 0;
$firmInfo = $dbInst->getFirmInfo($firm_id);
if(!$firmInfo) {
	die('Липсва индентификатор на фирмата!');
}
$worker_id = (isset($_GET['worker_id']) && is_numeric($_GET['worker_id'])) ? intval($_GET['worker_id']) : 0;
$w = $dbInst->getWorkerInfo($worker_id);
/*if(!$f) {
die('Липсва индентификатор на работещия!');
}*/
$chart_id = (isset($_GET['chart_id']) && is_numeric($_GET['chart_id'])) ? intval($_GET['chart_id']) : 0;
$f = $dbInst->getChartInfo($chart_id);

// Xajax begin
require ('xajax/xajax_core/xajax.inc.php');
function loadPatientCharts($worker_id) {
	$objResponse = new xajaxResponse();

	$objResponse->assign("hospitalsList", "innerHTML", echoPatientCharts($worker_id));
	$objResponse->call("stripTable", "listtable");
	$objResponse->call("clearForm", "frmChart");

	global $dbInst;
	$w = $dbInst->getWorkerInfo($worker_id);
	$objResponse->assign("firm_id", "value", $w['firm_id']);
	$objResponse->assign("chart_id", "value", 0);
	$objResponse->assign("worker_id", "value", $worker_id);
	$objResponse->assign("wname", "value", ($w['fname'].' '.$w['sname'].' '.$w['lname']));
	$objResponse->assign("egn", "value", $w['egn']);
	$objResponse->assign("hospital_date_from", "value", "");
	$objResponse->assign("hospital_date_to", "value", "");
	$objResponse->assign("days_off", "value", "");
	$objResponse->assign("mkb_id", "value", "");
	$objResponse->assign("mkb_code", "innerHTML", "");
	$objResponse->assign("mkb_desc", "innerHTML", "");
	$objResponse->assign("chart_desc", "value", "");
	$objResponse->assign("reason_id", "value", "");
	$objResponse->assign("chart_desc", "value", "");
	// checkboxes
	$objResponse->assign("medical_type_1", "checked", false);
	$objResponse->assign("medical_type_2", "checked", false);
	$objResponse->assign("medical_type_3", "checked", false);
	$objResponse->assign("medical_type_4", "checked", false);

	return $objResponse;
}
function openChart($chart_id) {
	$objResponse = new xajaxResponse();

	global $dbInst;
	$row = $dbInst->getChartInfo($chart_id); // get patient's chart info
	if($row) {
		$objResponse->call("clearForm", "frmChart");
		$objResponse->assign("chart_id", "value", $chart_id);
		$objResponse->assign("worker_id", "value", $row['worker_id']);
		$objResponse->assign("firm_id", "value", $row['firm_id']);
		$objResponse->assign("chart_num", "value", $row['chart_num']);
		$objResponse->assign("hospital_date_from", "value", $row['hospital_date_from2']);
		$objResponse->assign("hospital_date_to", "value", $row['hospital_date_to2']);
		$objResponse->assign("days_off", "value", $row['days_off']);
		$objResponse->assign("mkb_id", "value", $row['mkb_id']);
		$objResponse->assign("mkb_code", "innerHTML", $row['mkb_code']);
		$objResponse->assign("mkb_desc", "innerHTML", $row['mkb_desc']);
		$objResponse->assign("reason_id", "value", $row['reason_id']);
		//$objResponse->assign("reason_desc", "innerHTML", $row['reason_desc']);
		$objResponse->assign("chart_desc", "value", $row['chart_desc']);

		$w = $dbInst->getWorkerInfo($row['worker_id']);
		$objResponse->assign("wname", "value", ($w['fname'].' '.$w['sname'].' '.$w['lname']));
		$objResponse->assign("egn", "value", $w['egn']);

		if(!($medical_types_arr = @unserialize($row['medical_types']))) {
			$medical_types_arr = array();
		}
		$chart_types = $dbInst->getChartTypes();
		if($chart_types && is_array($medical_types_arr)) {
			foreach ($chart_types as $chart_type) {
				$objResponse->assign("medical_type_$chart_type[type_id]", "checked", (in_array($chart_type['type_id'], $medical_types_arr)));
			}
		}
	}

	return $objResponse;
}
function removePatientChart($chart_id = 0, $worker_id = 0) {
	$objResponse = new xajaxResponse();

	global $dbInst;
	$chart_id = $dbInst->removePatientChart($chart_id); // Remove a patient's chart
	$objResponse->assign("hospitalsList", "innerHTML", echoPatientCharts($worker_id));
	$objResponse->call("stripTable", "listtable");
	$objResponse->call("clearForm", "frmChart");

	$w = $dbInst->getWorkerInfo($worker_id);
	$objResponse->assign("firm_id", "value", $w['firm_id']);
	$objResponse->assign("chart_id", "value", 0);
	$objResponse->assign("worker_id", "value", $worker_id);
	$objResponse->assign("wname", "value", ($w['fname'].' '.$w['sname'].' '.$w['lname']));
	$objResponse->assign("egn", "value", $w['egn']);

	$sql = "SELECT COUNT(*) AS `cnt` FROM `patient_charts` WHERE `worker_id` = $worker_id";
	$row = $dbInst->fnSelectSingleRow($sql);
	if(!empty($row)) {
		$objResponse->script('if(parent.$("#w_patient_charts_num_'.$worker_id.'")[0]){parent.$("#w_patient_charts_num_'.$worker_id.'").html("'.HTMLFormat($row['cnt']).'")}');
	}

	return $objResponse;
}
function processChart($aFormValues) {
	$objResponse = new xajaxResponse();

	$objResponse->assign("btnSubmit","disabled",false);
	$objResponse->assign("btnSubmit","value","Съхрани");
	$objResponse->call("DisableEnableForm",false);

	global $dbInst;
	/*if(trim($aFormValues['chart_num']) == '') {
	$objResponse->alert("Моля, въведете на номер на болничния лист.");
	return $objResponse;
	}*/

	if(!intval($aFormValues['worker_id'])) {
		$objResponse->alert("Моля, изберете работещ във фирмата.");
		return $objResponse;
	}

	$d = new ParseBGDate();
	$hospital_date_from = trim($aFormValues['hospital_date_from']);
	$hospital_date_to = trim($aFormValues['hospital_date_to']);
	if($hospital_date_from == '') {
		$objResponse->alert("Моля, въведете начална дата на болничния лист.");
		return $objResponse;
	}
	if(!$d->Parse($hospital_date_from)) {
		$objResponse->alert($hospital_date_from . " е невалидна дата!");
		return $objResponse;
	}
	if($hospital_date_to == '') {
		$objResponse->alert("Моля, въведете на коя дата работещият трябва да е на работа.");
		return $objResponse;
	}
	if(!$d->Parse($hospital_date_to)) {
		$objResponse->alert($hospital_date_to . " е невалидна дата!");
		return $objResponse;
	}

	// Check dates: Compare `date_curr_position_start` and `date_career_start` dates
	$d->Parse($hospital_date_from);
	$hospital_date_from = mktime(0, 0, 0, $d->getMonth(), $d->getDay(), $d->getYear());
	$d->Parse($hospital_date_to);
	$hospital_date_to = mktime(0, 0, 0, $d->getMonth(), $d->getDay(), $d->getYear());
	if($hospital_date_from > $hospital_date_to) {
		$objResponse->alert('Моля, проверете датите от болничния лист!');
		return $objResponse;
	}
	// Check medical chart dates
	if($dbInst->checkChartDates(date('d.m.Y', $hospital_date_from), date('d.m.Y', $hospital_date_to), $aFormValues['chart_id'], $aFormValues['worker_id'])) {
		$objResponse->alert('Има презастъпване на болнични. Моля, проверете датите от болничния лист!');
		return $objResponse;
	}
	if(trim($aFormValues['mkb_id']) == '') {
		$objResponse->alert("Моля, въведете МКБ.");
		return $objResponse;
	}
	if($aFormValues['mkb_id'] != '' && !$dbInst->isValidMkb($aFormValues['mkb_id'])) {
		$objResponse->alert($aFormValues['mkb_id'].' е невалидна стойност!');
		return $objResponse;
	}
	if(trim($aFormValues['reason_id']) == '') {
		$objResponse->alert("Моля, въведете причина.");
		return $objResponse;
	}
	if (!count(preg_grep('/^medical_type_(\d+)$/', array_keys($aFormValues)))) {
		$objResponse->alert("Моля, въведете вид на болничния лист, напр. Първичен или Продължение.");
		return $objResponse;
	}
	// Check patient's chart extension condition
	/*if(isset($aFormValues['medical_type_2']) && $aFormValues['worker_id']) {
	if(!$dbInst->chartExtensionAllowed($aFormValues['worker_id'], $aFormValues['hospital_date_from'])) {
	$objResponse->alert("Болничният лист не може да бъде продължение, тъй като има прекъсване!\nМоля, проверете датите.");
	return $objResponse;
	}
	}*/

	$isNewChart = (!empty($aFormValues['chart_id'])) ? 0 : 1;
	$chart_id = $dbInst->processPatientChart($aFormValues); // Insert/update a patient's chart
	$objResponse->call("clearForm", "frmChart");
	$objResponse->assign("mkb_code", "innerHTML", "");
	$objResponse->assign("mkb_desc", "innerHTML", "");
	$objResponse->assign("reason_desc", "innerHTML", "");
	$objResponse->assign("chart_id", "value", 0);
	$objResponse->assign("firm_id", "value", $aFormValues['firm_id']);
	$objResponse->assign("worker_id", "value", $aFormValues['worker_id']);
	$objResponse->assign("wname", "value", $aFormValues['wname']);
	$objResponse->assign("egn", "value", $aFormValues['egn']);
	$objResponse->assign("hospitalsList","innerHTML",echoPatientCharts($aFormValues['worker_id']));
	$objResponse->call("stripTable","listtable");

	$worker_id = intval($aFormValues['worker_id']);
	if($isNewChart) {
		$sql = "SELECT COUNT(*) AS `cnt` FROM `patient_charts` WHERE `worker_id` = $worker_id";
		$row = $dbInst->fnSelectSingleRow($sql);
		if(!empty($row)) {
			$objResponse->script('if(parent.$("#w_patient_charts_num_'.$worker_id.'")[0]){parent.$("#w_patient_charts_num_'.$worker_id.'").html("'.HTMLFormat($row['cnt']).'")}');
		}
	}
	$objResponse->alert("Данните от болничния лист бяха успешно въведени!");
	return $objResponse;
}
function calcDaysOff($hospital_date_from, $hospital_date_to) {
	$objResponse = new xajaxResponse();

	$hospital_date_from = trim($hospital_date_from);
	$hospital_date_to = trim($hospital_date_to);
	$d11 = $d22 = 0;
	if($hospital_date_from != '') {
		$d1 = new ParseBGDate();
		if($d1->Parse($hospital_date_from)) {
			$objResponse->assign("hospital_date_from", "value", $d1->day.'.'.$d1->month.'.'.$d1->year);
			$d11 = 1;
		}
		else
		$objResponse->assign("hospital_date_from", "value", "");
	}
	if($hospital_date_to != '') {
		$d2 = new ParseBGDate();
		if($d2->Parse($hospital_date_to)) {
			$objResponse->assign("hospital_date_to", "value", $d2->day.'.'.$d2->month.'.'.$d2->year);
			$d22 = 1;
		}
		else
		$objResponse->assign("hospital_date_to", "value", "");
	}
	if($d11 && $d22) {
		$t1 = mktime(0, 0, 0, $d1->month, $d1->day, $d1->year);
		$t2 = mktime(0, 0, 0, $d2->month, $d2->day, $d2->year);
		// Calculate how many days spans patient's chart
		$objResponse->assign("days_off", "value", round((($t2 - $t1) / ((60 * 60) * 24)) + 1));
	}
	return $objResponse;
}
$xajax = new xajax();
$xajax->registerFunction("loadPatientCharts");
$xajax->registerFunction("openChart");
$xajax->registerFunction("removePatientChart");
$xajax->registerFunction("processChart");
$xajax->registerFunction("calcDaysOff");
//$xajax->setFlag("debug",true);
$echoJS = $xajax->getJavascript('xajax/');
$xajax->processRequest();
// Xajax end


function echoPatientCharts($worker_id) {
	global $dbInst;
	ob_start();
	?>
	<table id="listtable">
	  <tr>
	  	<th>&nbsp;</th>
	    <th>От</th>
	    <th>На раб. на</th>
	    <th>МКБ</th>
	    <th>Вид</th>
	    <th>Причина</th>
	    <th>&nbsp;</th>
	  </tr>
	  <?php
	  $charts = $dbInst->getPatientCharts($worker_id);
	  if($charts) {
	  	foreach ($charts as $row) {
	  		if(!($medical_types_arr = @unserialize($row['medical_types']))) {
	  			$medical_types_arr = array();
	  		}
	  		$chart_types = $dbInst->getChartTypes();
	  		$medical_types = null;
	  		if($chart_types) {
	  			foreach ($chart_types as $chart_type) {
	  				if(!is_array($medical_types_arr))
	  				continue;
	  				if(in_array($chart_type['type_id'], $medical_types_arr)) {
	  					switch ($chart_type['type_id']) {
	  						case '1':
	  							$c = 'blue';
	  							break;
	  						case '2':
	  							$c = 'red';
	  							break;
	  						case '3':
	  							$c = 'orange';
	  							break;
	  						default:
	  							$c = 'black';
	  							break;
	  					}
	  					$medical_types[] = '<span style="color:'.$c.';">'.$chart_type['type_desc_short'].'</span>';
	  				}
	  			}
	  		}
	  ?>
	  <tr>
	  	<td><a href="javascript:void(null);" onclick="xajax_openChart(<?=$row['chart_id']?>);return false;" title="Отвори болничния лист"><img src="img/moreinfo.gif" width="17" height="17" border="0" alt="Отвори болничния лист" /></a></td>
	  	<td><?=$row['hospital_date_from']?></td>
	    <td><?=$row['hospital_date_to']?></td>
	    <td><strong><?=$row['mkb_id']?></strong></td>
	    <td><?=(($medical_types != null) ? implode('<br />', $medical_types) : '')?></td>
	    <td><?=$row['reason_id']?></td>
	    <td><a href="javascript:void(null);" onclick="var answ=confirm('Наистина ли искате да изтриете болничния лист на работещия?');if(answ){xajax_removePatientChart(<?=$row['chart_id']?>, <?=$worker_id?>);}return false;" title="Изтриване на болничен лист"><img src="img/delete.gif" alt="delete" width="15" height="15" border="0" /></a></td>
	  </tr>
	  <?php
	  	}
	  }
	  else {
	  ?>
	  <tr>
	    <td colspan="7">Няма регистрирани болнични листове.</td>
	  </tr>
	  <?php
	  }
	  ?>
	</table>
	<?php
	$buff = ob_get_contents();
	ob_end_clean();
	return $buff;
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?=SITE_NAME?></title>
<link href="styles.css" rel="stylesheet" type="text/css" media="screen" />
<?=$echoJS?>
<script type="text/javascript" src="scw.js"></script>
<script type="text/javascript" src="js/RegExpValidate.js"></script>
<!-- http://jquery.com/demo/thickbox/ -->
<script type="text/javascript" src="js/jquery-latest.pack.js"></script>
<script type="text/javascript" src="js/thickbox/thickbox.js"></script>
<link rel="stylesheet" href="js/thickbox/thickbox.css" type="text/css" media="screen" />
<!-- http://colorpowered.com/colorbox/core/example1/index.html -->
<link type="text/css" media="screen" rel="stylesheet" href="js/colorbox/colorbox.css" />
<script type="text/javascript" src="js/colorbox/jquery.colorbox.js"></script>
<script type="text/javascript">
//<![CDATA[
var obj_mkb_id = null;
var obj_mkb_desc = null;
var obj_mkb_code = null;

function openMkbNomenclature(el) {
	obj_mkb_id = $('#mkb_id');
	obj_mkb_desc = $('#mkb_desc');
	obj_mkb_code = $('#mkb_code');
	$(el).colorbox({width:"90%", height:"100%", iframe:true, overlayClose:false, title:'Номенклатура МКБ 10', transition:"none", fastIframe:false, href:'popup_mkb_nomenclature.php'});
	return false;
}
function populateFields(mkb_id, mkb_desc) {
	obj_mkb_id.val(mkb_id);
	obj_mkb_desc.html(mkb_desc);
	obj_mkb_code.html('МКБ 10');
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
		$("#mkb_desc").html('');
	}
	catch (err) {
		alert(err.description);
	}
}

function newChart() {
	var worker_id = $("#worker_id").val();
	var firm_id = $("#firm_id").val();
	var egn = $("#egn").val();
	var wname = $("#wname").val();

	clearForm('frmChart');

	$("#chart_id").val(0);
	$("#worker_id").val(worker_id);
	$("#firm_id").val(firm_id);
	$("#egn").val(egn);
	$("#wname").val(wname);
	$("#mkb_code").empty();
	$("#mkb_desc").empty();
	$("#reason_desc").empty();

	return false;
}

function fnCalcDaysOff() {
	xajax_calcDaysOff($('#hospital_date_from').val(), $('#hospital_date_to').val());
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

			if(parent) {
				var parent = (window.opener) ? window.opener : self.parent;
				parent.document.getElementById('TB_ajaxWindowTitle').innerHTML = data[1]+', ЕГН '+data[3];
			}
			xajax_loadPatientCharts(data[0]);
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
			var worker_id = data[0];
			var wname = data[1];
			return wname;
		}
	});
	$("#wname").result(function(event, data, formatted) {
		if (data) {
			$("#wname").val(data[1]);
			$("#firm_id").val(data[2]);
			$("#worker_id").val(data[0]);

			if(parent) {
				var parent = (window.opener) ? window.opener : self.parent;
				parent.document.getElementById('TB_ajaxWindowTitle').innerHTML = data[1]+', ЕГН '+data[3];
			}
			xajax_loadPatientCharts(data[0]);
		}
	});

	$("#mkb_id").autocomplete("autocompleter.php", {
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
	$("#mkb_id").result(function(event, data, formatted) {
		if (data) {
			$("#mkb_id").val(data[0]);
			$("#mkb_desc").html(data[1]);
			$("#mkb_code").html(data[2]);
		}
	});

	/*$("#reason_id").autocomplete("autocompleter.php", {
	minChars: 1,
	extraParams: { search: "medical_reasons" },
	width: 550,
	selectFirst: false,
	formatItem: function(data, i, n, value) {
	var reason_id = data[0];
	var reason_desc = data[1];
	return "<table border='0' cellpadding='0' cellspacing='0'><tr><td width='30'>"+reason_id+"<\/td><td width='520'>"+reason_desc+"<\/td><\/tr><\/table>";
	}
	});
	$("#reason_id").result(function(event, data, formatted) {
	if (data) {
	$("#reason_id").val(data[0]);
	$("#reason_desc").html(data[1]);
	}
	});*/
});
// Auto-completer end
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
  <form id="frmChart" name="frmChart" action="javascript:void(null);">
    <input type="hidden" id="form_is_dirty" name="form_is_dirty" value="0" />
    <input type="hidden" id="chart_id" name="chart_id" value="<?=$chart_id?>" />
    <input type="hidden" id="worker_id" name="worker_id" value="<?=$worker_id?>" />
    <input type="hidden" id="firm_id" name="firm_id" value="<?=$firm_id?>" />
    <?=getPopupNavigation('Болнични листове')?>
    <table cellpadding="0" cellspacing="0" class="formBg" width="805">
      <tr>
        <th class="leftSplit rightSplit topSplit">Болнични листове - въвеждане</th>
        <th class="rightSplit topSplit">Болнични листове - списък</th>
      </tr>
      <tr>
        <td valign="top" align="center" width="428" class="leftSplit rightSplit"><!-- Patient's chart form -->
          <table cellpadding="0" cellspacing="0" border="0" width="99%">
            <tr>
              <td colspan="2">ЕГН:
                  <input type="text" id="egn" name="egn" value="<?=((isset($w['egn']))?HTMLFormat($w['egn']):'')?>" size="20" maxlength="10" />
                  Болничен лист N:
                  <input type="text" id="chart_num" name="chart_num" value="<?=((isset($f['chart_num']))?HTMLFormat($f['chart_num']):'')?>" size="14" maxlength="50" />
                <div class="br"></div>
                Име:
                <input type="text" id="wname" name="wname" value="<?=((isset($w['lname']))?HTMLFormat($w['fname'].' '.$w['sname'].' '.$w['lname']):'')?>" size="65" maxlength="50" style="width:352px;" />
                <div class="hr"></div>
                <span class="labeltext"><strong>От дата:</strong> </span>
                <input type="text" id="hospital_date_from" name="hospital_date_from" value="<?=((isset($f['hospital_date_from']))?HTMLFormat($f['hospital_date_from']):'')?>" size="20" maxlength="10" onchange="xajax_calcDaysOff($('#hospital_date_from').val(), $('#hospital_date_to').val());" onclick="scwNextAction=fnCalcDaysOff.runsAfterSCW(this);scwShow(this,event);" class="date_input" /> г.
                <div class="br"></div>
                <span class="labeltext"><strong>До дата: </strong></span>
                <input type="text" id="hospital_date_to" name="hospital_date_to" value="<?=((isset($f['hospital_date_to']))?HTMLFormat($f['hospital_date_to']):'')?>" size="20" maxlength="10" onchange="xajax_calcDaysOff($('#hospital_date_from').val(), $('#hospital_date_to').val());" onclick="scwNextAction=fnCalcDaysOff.runsAfterSCW(this);scwShow(this,event);" class="date_input" /> г. вкл.
                <div class="br"></div>
                <span class="labeltext">ВН: </span>
                <input type="text" id="days_off" name="days_off" value="<?=((isset($f['days_off']))?HTMLFormat($f['days_off']):'')?>" size="10" maxlength="50" onkeypress="return numbersonly(this, event);" />
                &nbsp;&nbsp;
                дни
                <div class="hr"></div>
                <span class="labeltext"><a href="#" onclick="openMkbNomenclature(this);">МКБ <img src="img/moreinfo.gif" alt="info" border="0" width="17" height="17" /></a></span>
                <input type="text" id="mkb_id" name="mkb_id" value="<?=((isset($f['mkb_id']))?HTMLFormat($f['mkb_id']):'')?>" size="10" maxlength="50" />
                &nbsp;&nbsp;<span class="primary"><strong id="mkb_code"></strong></span>
                <div class="br"></div>
                <span id="mkb_desc" class="hospitalDetail"><?=((isset($f['mkb_desc']))?HTMLFormat($f['mkb_desc']):'')?></span>
                <strong>Причина: </strong>
                <select id="reason_id" name="reason_id">
                  <option value=""> &nbsp;&nbsp;</option>
                  <?php
                  $rows = $dbInst->searchByMedicalReasons();
                  foreach ($rows as $row) {
                  	echo '<option value="'.$row['reason_id'].'"'.((isset($f['reason_id'])&&$f['reason_id']==$row['reason_id'])?' selected="selected"':'').'>'.$row['reason_id'].' - '.HTMLFormat($row['reason_desc']).'</option>';
                  }
                  ?>
                </select>
                <div class="hr"></div>
                <?php
                $medical_types_arr = (isset($f['medical_types']) && is_array($f['medical_types'])) ? unserialize($f['medical_types']) : array();
                $chart_types = $dbInst->getChartTypes();
                if($chart_types) {
                	foreach ($chart_types as $chart_type) {
                		echo '<input type="checkbox" id="medical_type_'.$chart_type['type_id'].'" name="medical_type_'.$chart_type['type_id'].'" value="'.$chart_type['type_id'].'"'.((in_array($chart_type['type_id'], $medical_types_arr))?' checked="checked"':'').' /> '.HTMLFormat($chart_type['type_desc']).'&nbsp;&nbsp;';
                	}
                }
                ?></td>
            </tr>
            <tr>
              <td colspan="2"></td>
            </tr>
            <tr>
              <td>Разширена <br />
                диагноза: </td>
              <td><textarea id="chart_desc" name="chart_desc" rows="3" cols="54"><?=((isset($f['chart_desc']))?HTMLFormat($f['chart_desc']):'')?></textarea>
              </td>
            </tr>
          </table></td>
        <td valign="top" class="rightSplit"><div id="hospitalsList">
            <?=echoPatientCharts($worker_id);?>
          </div></td>
      </tr>
      <tr>
        <td colspan="2" class="leftSplit rightSplit"><p align="center">
            <input type="button" id="btnSubmit" name="btnSubmit" value="Съхрани" class="nicerButtons" onclick="$('input#form_is_dirty').val(1);var days_off = parseInt($('#days_off').val(),10); if(days_off <= 1 || days_off >= 30) { var answ = confirm('Сигурни ли сте, че временната нетрудоспособност е '+days_off+' дни?'); if(!answ) return false; }	xajax_processChart(xajax.getFormValues('frmChart')); DisableEnableForm(true); return false;" />
            <input type="button" id="btnNewChart" name="btnNewChart" value="Нов болничен" class="nicerButtons" onclick="$('input#form_is_dirty').val(1);newChart();" />
          </p></td>
      </tr>
    </table>
  </form>
</div>
</body>
</html>