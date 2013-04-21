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
$telk_id = (isset($_GET['telk_id']) && is_numeric($_GET['telk_id'])) ? intval($_GET['telk_id']) : 0;
$f = $dbInst->getChartInfo($telk_id);

// Xajax begin
require ('xajax/xajax_core/xajax.inc.php');
function loadPatientTelks($worker_id) {
	$objResponse = new xajaxResponse();

	$objResponse->assign("hospitalsList", "innerHTML", echoPatientTelks($worker_id));
	$objResponse->call("stripTable", "listtable");
	$objResponse->call("clearForm", "frmTelk");

	global $dbInst;
	$w = $dbInst->getWorkerInfo($worker_id);
	$objResponse->assign("firm_id", "value", $w['firm_id']);
	$objResponse->assign("telk_id", "value", 0);
	$objResponse->assign("worker_id", "value", $worker_id);
	$objResponse->assign("wname", "value", ($w['fname'].' '.$w['sname'].' '.$w['lname']));
	$objResponse->assign("egn", "value", $w['egn']);
	$objResponse->assign("mkb_code_1", "innerHTML", "");
	$objResponse->assign("mkb_desc_1", "innerHTML", "");
	$objResponse->assign("mkb_code_2", "innerHTML", "");
	$objResponse->assign("mkb_desc_2", "innerHTML", "");
	$objResponse->assign("mkb_code_3", "innerHTML", "");
	$objResponse->assign("mkb_desc_3", "innerHTML", "");
	$objResponse->assign("mkb_code_4", "innerHTML", "");
	$objResponse->assign("mkb_desc_4", "innerHTML", "");
	return $objResponse;
}
function openTelk($telk_id) {
	$objResponse = new xajaxResponse();

	global $dbInst;
	$row = $dbInst->getTelkInfo($telk_id); // get patient's chart info
	if($row) {
		$objResponse->call("clearForm", "frmTelk");
		$objResponse->assign("telk_id", "value", $row['telk_id']);
		$objResponse->assign("worker_id", "value", $row['worker_id']);
		$objResponse->assign("firm_id", "value", $row['firm_id']);
		$objResponse->assign("telk_num", "value", $row['telk_num']);
		$objResponse->assign("telk_date_from", "value", $row['telk_date_from2']);
		if($row['telk_duration'] == 'life')
		$objResponse->assign("telk_date_to", "value", 'пожизнен');
		else
		$objResponse->assign("telk_date_to", "value", $row['telk_date_to2']);

		$objResponse->assign("first_inv_date", "value", $row['first_inv_date2']);
		$objResponse->assign("telk_duration", "value", $row['telk_duration']);
		$objResponse->assign("mkb_id_1", "value", $row['mkb_id_1']);
		$objResponse->assign("mkb_code_1", "innerHTML", $row['mkb_code_1']);
		$objResponse->assign("mkb_desc_1", "innerHTML", $row['mkb_desc_1']);
		$objResponse->assign("mkb_id_2", "value", $row['mkb_id_2']);
		$objResponse->assign("mkb_code_2", "innerHTML", $row['mkb_code_2']);
		$objResponse->assign("mkb_desc_2", "innerHTML", $row['mkb_desc_2']);
		$objResponse->assign("mkb_id_3", "value", $row['mkb_id_3']);
		$objResponse->assign("mkb_code_3", "innerHTML", $row['mkb_code_3']);
		$objResponse->assign("mkb_desc_3", "innerHTML", $row['mkb_desc_3']);
		$objResponse->assign("mkb_id_4", "value", $row['mkb_id_4']);
		$objResponse->assign("mkb_code_4", "innerHTML", $row['mkb_code_4']);
		$objResponse->assign("mkb_desc_4", "innerHTML", $row['mkb_desc_4']);
		$objResponse->assign("percent_inv", "value", $row['percent_inv']);
		$objResponse->assign("bad_work_env", "value", $row['bad_work_env']);

		$w = $dbInst->getWorkerInfo($row['worker_id']);
		$objResponse->assign("wname", "value", ($w['fname'].' '.$w['sname'].' '.$w['lname']));
		$objResponse->assign("egn", "value", $w['egn']);
	}

	return $objResponse;
}
function removePatientTelk($telk_id = 0, $worker_id = 0) {
	$objResponse = new xajaxResponse();

	if($_SESSION['sess_user_level'] == 1) { /* admin rights only */
		global $dbInst;
		$telk_id = $dbInst->removePatientTelk($telk_id); // Remove a patient's telk
		$objResponse->assign("hospitalsList", "innerHTML", echoPatientTelks($worker_id));
		$objResponse->call("stripTable", "listtable");
		$objResponse->call("clearForm", "frmTelk");

		$w = $dbInst->getWorkerInfo($worker_id);
		$objResponse->assign("firm_id", "value", $w['firm_id']);
		$objResponse->assign("telk_id", "value", 0);
		$objResponse->assign("worker_id", "value", $worker_id);
		$objResponse->assign("wname", "value", ($w['fname'].' '.$w['sname'].' '.$w['lname']));
		$objResponse->assign("egn", "value", $w['egn']);
		
		$sql = "SELECT COUNT(*) AS `cnt` FROM `telks` WHERE `worker_id` = $worker_id";
		$row = $dbInst->fnSelectSingleRow($sql);
		if(!empty($row)) {
			$objResponse->script('if(parent.$("#w_patient_telks_num_'.$worker_id.'")[0]){parent.$("#w_patient_telks_num_'.$worker_id.'").html("'.HTMLFormat($row['cnt']).'")}');
		}
	}

	return $objResponse;
}
function processTelk($aFormValues) {
	$objResponse = new xajaxResponse();

	$objResponse->assign("btnSubmit","disabled",false);
	$objResponse->assign("btnSubmit","value","Съхрани");
	$objResponse->call("DisableEnableForm",false);

	if(!intval($aFormValues['worker_id'])) {
		$objResponse->alert("Моля, изберете работещ във фирмата.");
		return $objResponse;
	}

	global $dbInst;
	$d = new ParseBGDate();
	$telk_date_from = trim($aFormValues['telk_date_from']);
	$telk_date_to = trim($aFormValues['telk_date_to']);
	if($telk_date_from == '') {
		$objResponse->alert('Моля, въведете дата на експертното решение.');
		return $objResponse;
	}
	if(!$d->Parse($telk_date_from)) {
		$objResponse->alert($telk_date_from . ' е невалидна дата!');
		return $objResponse;
	}
	if($telk_date_to == '' && $aFormValues['telk_duration'] != 'пожизнен') {
		$objResponse->alert('Моля, въведете срок на инвалидността.');
		return $objResponse;
	}
	if(!$d->Parse($telk_date_to) && $aFormValues['telk_duration'] != 'пожизнен') {
		$objResponse->alert($telk_date_to . ' е невалидна дата!');
		return $objResponse;
	}
	
	//Check for duplicate telks (Експ. решение N) - telk_num, telk_date_from
	$firm_id = (isset($aFormValues['firm_id']) && !empty($aFormValues['firm_id'])) ? intval($aFormValues['firm_id']) : 0;
	$worker_id = (isset($aFormValues['worker_id']) && !empty($aFormValues['worker_id'])) ? intval($aFormValues['worker_id']) : 0;
	$telk_id = (isset($aFormValues['telk_id']) && !empty($aFormValues['telk_id'])) ? intval($aFormValues['telk_id']) : 0;
	$telk_num = $dbInst->checkStr($aFormValues['telk_num']);
	$telk_date_from = ($d->Parse($aFormValues['telk_date_from'])) ? $d->year . '-' . $d->month . '-' . $d->day . ' 00:00:00' : '';
	$sql = "SELECT t.* , w.fname, w.sname, w.lname, w.egn
			FROM telks t 
			LEFT JOIN workers w ON ( w.worker_id = t.worker_id )
			WHERE t.firm_id = $firm_id 
			AND t.worker_id = $worker_id 
			AND t.telk_num = '$telk_num' 
			AND t.telk_date_from = '$telk_date_from'";
	if(!empty($telk_id)) $sql .= " AND t.telk_id != $telk_id";
	$rows = $dbInst->query($sql);
	if(!empty($rows)) {
		$msg = (1 == count($rows)) ? 'Има вече въведено '.count($rows).' експертно решение' : 'Има вече въведени '.count($rows).' експертни решения';
		$msg .= ' от ТЕЛК № '.$telk_num.'/'.$d->day.'.'.$d->month.'.'.$d->year.' г. за: '."\n";
		foreach ($rows as $row) {
			$msg .= '- '.stripslashes($row['fname'].' '.$row['sname'].' '.$row['lname'].' (ЕГН: '.$row['egn'].')')."\n";
		}
		$objResponse->alert($msg);
		return $objResponse;
	}
	
	/*if($dbInst->checkTelkDates($telk_date_from, $telk_date_to, $aFormValues['telk_id'], $aFormValues['worker_id'])) {
	$objResponse->alert('Моля, проверете датите от експертното решение!');
	return $objResponse;
	}*/
	if(trim($aFormValues['percent_inv']) == '' || floatval($aFormValues['percent_inv']) <= 0) {
		$objResponse->alert('Моля, въведете % трудова неработоспособност!');
		return $objResponse;
	}
	if(trim($aFormValues['mkb_id_1']) == '' && trim($aFormValues['mkb_id_2']) == '' && trim($aFormValues['mkb_id_3']) == '' && trim($aFormValues['mkb_id_4']) ==''){
		$objResponse->alert('Моля, въведете МКБ!');
		return $objResponse;
	}
	if(empty($aFormValues['mkb_id_1'])) {
		$objResponse->alert('Моля, въведете МКБ водеща диагноза!');
		return $objResponse;
	}
	if($aFormValues['mkb_id_1'] != '' && !$dbInst->isValidMkb($aFormValues['mkb_id_1'])) {
		$objResponse->alert($aFormValues['mkb_id_1'].' е невалидна стойност!');
		return $objResponse;
	}
	if($aFormValues['mkb_id_2'] != '' && !$dbInst->isValidMkb($aFormValues['mkb_id_2'])) {
		$objResponse->alert($aFormValues['mkb_id_2'].' е невалидна стойност!');
		return $objResponse;
	}
	if($aFormValues['mkb_id_3'] != '' && !$dbInst->isValidMkb($aFormValues['mkb_id_3'])) {
		$objResponse->alert($aFormValues['mkb_id_3'].' е невалидна стойност!');
		return $objResponse;
	}
	if($aFormValues['mkb_id_4'] != '' && !$dbInst->isValidMkb($aFormValues['mkb_id_4'])) {
		$objResponse->alert($aFormValues['mkb_id_4'].' е невалидна стойност!');
		return $objResponse;
	}

	$isNewTelk = (!empty($aFormValues['telk_id'])) ? 0 : 1;
	$telk_id = $dbInst->processPatientTelk($aFormValues); // Insert/update a patient's telk
	$objResponse->call("clearForm", "frmTelk");
	$objResponse->assign("mkb_code_1", "innerHTML", "");
	$objResponse->assign("mkb_desc_1", "innerHTML", "");
	$objResponse->assign("mkb_code_2", "innerHTML", "");
	$objResponse->assign("mkb_desc_2", "innerHTML", "");
	$objResponse->assign("mkb_code_3", "innerHTML", "");
	$objResponse->assign("mkb_desc_3", "innerHTML", "");
	$objResponse->assign("mkb_code_4", "innerHTML", "");
	$objResponse->assign("mkb_desc_4", "innerHTML", "");
	$objResponse->assign("telk_id", "value", 0);
	$objResponse->assign("firm_id", "value", $aFormValues['firm_id']);
	$objResponse->assign("worker_id", "value", $aFormValues['worker_id']);
	$objResponse->assign("wname", "value", $aFormValues['wname']);
	$objResponse->assign("egn", "value", $aFormValues['egn']);
	$objResponse->assign("hospitalsList","innerHTML",echoPatientTelks($aFormValues['worker_id']));
	$objResponse->call("stripTable","listtable");
	if($isNewTelk) {
		$sql = "SELECT COUNT(*) AS `cnt` FROM `telks` WHERE `worker_id` = $worker_id";
		$row = $dbInst->fnSelectSingleRow($sql);
		if(!empty($row)) {
			$objResponse->script('if(parent.$("#w_patient_telks_num_'.$worker_id.'")[0]){parent.$("#w_patient_telks_num_'.$worker_id.'").html("'.HTMLFormat($row['cnt']).'")}');
		}
	}
	//$objResponse->alert("Данните от експертното решение бяха успешно въведени!");
	return $objResponse;
}
$xajax = new xajax();
$xajax->registerFunction("loadPatientTelks");
$xajax->registerFunction("openTelk");
$xajax->registerFunction("removePatientTelk");
$xajax->registerFunction("processTelk");
$xajax->registerFunction("formatBGDate");
//$xajax->setFlag("debug",true);
$echoJS = $xajax->getJavascript('xajax/');
$xajax->processRequest();
// Xajax end

function echoPatientTelks($worker_id) {
	global $dbInst;
	ob_start();
	?>
	<table id="listtable">
	  <tr>
	  	<th>&nbsp;</th>
	    <th>Срок до</th>
	    <th>за</th>
	    <th>МКБ</th>
	    <th>% тр.н.раб.</th>
	    <?php if($_SESSION['sess_user_level'] == 1) { /* admin rights only */ ?>
	    <th>&nbsp;</th>
	    <?php } ?>
	  </tr>
	  <?php
	  $telks = $dbInst->getPatientTelks($worker_id);
	  if($telks) {
	  	foreach ($telks as $row) {
	  ?>
	  <tr>
	  	<td><a href="javascript:void(null);" onclick="xajax_openTelk(<?=$row['telk_id']?>);return false;" title="Отвори експ. решение"><img src="img/moreinfo.gif" width="17" height="17" border="0" alt="Отвори експ. решение" /></a></td>
	  	<td><?=(($row['telk_duration']=='пожизнен')?'пожизнен':$row['telk_date_to_h'])?></td>
	    <td><?=$row['telk_duration']?></td>
	    <td><strong><?=$row['mkb_id_1']?></strong></td>
	    <td><?=$row['percent_inv']?> %</td>
	    <?php if($_SESSION['sess_user_level'] == 1) { /* admin rights only */ ?>
	    <td><a href="javascript:void(null);" onclick="var answ=confirm('Наистина ли искате да изтриете експертното решение от ТЕЛК на работещия?');if(answ){xajax_removePatientTelk(<?=$row['telk_id']?>, <?=$worker_id?>);}return false;" title="Изтриване на експертно решение от ТЕЛК"><img src="img/delete.gif" alt="delete" width="15" height="15" border="0" /></a></td>
	    <?php } ?>
	  </tr>
	  <?php
	  	}
	  }
	  else {
	  ?>
	  <tr>
	    <td colspan="6">Няма регистрирани експ. решения.</td>
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
<script type="text/javascript" src="js/RegExpValidate.js"></script>
<script type="text/javascript" src="scw.js"></script>
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
	var prchk_id = $(el).attr('id').split('_')[2];
	obj_mkb_id = $('#mkb_id_' + prchk_id);
	obj_mkb_desc = $('#mkb_desc_' + prchk_id);
	obj_mkb_code = $('#mkb_code_' + prchk_id);
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
			//if(element.name == 'telk_id' || element.name == 'firm_id' || element.name == 'wname' || element.name == 'egn') continue;
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

function newTelk() {
	var worker_id = $("#worker_id").val();
	var firm_id = $("#firm_id").val();
	var egn = $("#egn").val();
	var wname = $("#wname").val();

	clearForm('frmTelk');

	$("#telk_id").val(0);
	$("#worker_id").val(worker_id);
	$("#firm_id").val(firm_id);
	$("#egn").val(egn);
	$("#wname").val(wname);
	$("#mkb_code_1").empty();
	$("#mkb_desc_1").empty();
	$("#mkb_code_2").empty();
	$("#mkb_desc_2").empty();
	$("#mkb_code_3").empty();
	$("#mkb_desc_3").empty();
	$("#mkb_code_4").empty();
	$("#mkb_desc_4").empty();

	return false;
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
			xajax_loadPatientTelks(data[0]);
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
			var telk_id = data[0];
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
			xajax_loadPatientTelks(data[0]);
		}
	});

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
</style>
</head>
<body style="overflow:hidden;">
<div id="contentinner" align="center">
  <form id="frmTelk" name="frmTelk" action="javascript:void(null);">
    <input type="hidden" id="form_is_dirty" name="form_is_dirty" value="0" />
    <input type="hidden" id="telk_id" name="telk_id" value="<?=$telk_id?>" />
    <input type="hidden" id="worker_id" name="worker_id" value="<?=$worker_id?>" />
    <input type="hidden" id="firm_id" name="firm_id" value="<?=$firm_id?>" />
    <?=getPopupNavigation('Експ. решения от ТЕЛК')?>
    <table cellpadding="0" cellspacing="0" class="formBg" width="770">
      <tr>
        <td colspan="2" class="leftSplit rightSplit topSplit"><strong>ЕГН: </strong>
          <input type="text" id="egn" name="egn" value="<?=((isset($w['egn']))?HTMLFormat($w['egn']):'')?>" size="20" maxlength="10" />
          &nbsp;&nbsp;<strong> Име:</strong>
          <input type="text" id="wname" name="wname" value="<?=((isset($w['lname']))?HTMLFormat($w['fname'].' '.$w['sname'].' '.$w['lname']):'')?>" size="65" maxlength="50" style="width:352px;" />
        </td>
      </tr>
      <tr>
        <th class="leftSplit rightSplit">ТЕЛК експертно решение - въвеждане</th>
        <th class="rightSplit">Списък на ТЕЛК експертни решения</th>
      </tr>
      <tr>
        <td valign="top" align="center" width="428" class="leftSplit rightSplit"><!-- Patient's telk form -->
          <table cellpadding="0" cellspacing="0" border="0" width="99%">
            <tr>
              <td colspan="2">Експ. решение №
                <input type="text" id="telk_num" name="telk_num" value="<?=((isset($f['telk_num']))?HTMLFormat($f['telk_num']):'')?>" size="8" maxlength="4"  />
                <input type="text" id="telk_date_from" name="telk_date_from" value="<?=((isset($f['telk_date_from']))?HTMLFormat($f['telk_date_from']):'')?>" size="18" maxlength="10" onchange="xajax_formatBGDate('telk_date_from',this.value);return false;" onclick="scwShow(this,event);" class="date_input" />
                г. <br />
                Срок на инвалидността до:
                <input type="text" id="telk_date_to" name="telk_date_to" value="<?=((isset($f['telk_date_to']))?HTMLFormat($f['telk_date_to']):'')?>" size="18" maxlength="10" onchange="xajax_formatBGDate('telk_date_to',this.value);return false;" onclick="scwShow(this,event);" class="date_input" />
                г. за
                <select id="telk_duration" name="telk_duration">
                  <option value="">&nbsp;&nbsp;</option>
                  <?php $telk_duration = (isset($f['telk_duration'])) ? $f['telk_duration'] : ''; ?>
                  <option value="1 г."<?=(($telk_duration=='1')?' selected="selected"':'')?>>1 г.&nbsp;&nbsp;</option>
                  <option value="2 г."<?=(($telk_duration=='2')?' selected="selected"':'')?>>2 г.&nbsp;&nbsp;</option>
                  <option value="3 г."<?=(($telk_duration=='3')?' selected="selected"':'')?>>3 г.&nbsp;&nbsp;</option>
                  <option value="пожизнен"<?=(($telk_duration=='life')?' selected="selected"':'')?>>пожизнен&nbsp;&nbsp;</option>
                </select>
                <div class="br"></div>
                Дата на първа инвалидизация: <input type="text" id="first_inv_date" name="first_inv_date" value="<?=((isset($f['first_inv_date']))?HTMLFormat($f['first_inv_date']):'')?>" size="18" maxlength="10" onchange="xajax_formatBGDate('first_inv_date',this.value);return false;" onclick="scwShow(this,event);" class="date_input" /> г.
                <div class="br"></div>
                % трудова неработоспособност:
                <input type="text" id="percent_inv" name="percent_inv" value="<?=((isset($f['percent_inv']))?HTMLFormat($f['percent_inv']):'')?>" size="10" maxlength="50" onKeyPress="return numbersonly(this, event);" />
                <div class="hr"></div>
                <input type="text" id="mkb_id_1" name="mkb_id_1" value="<?=((isset($f['mkb_id_1']))?HTMLFormat($f['mkb_id_1']):'')?>" onkeyup="if(this.value==''){$('#mkb_desc_1').html('');$('#mkb_code_1').html('');}" size="10" maxlength="50" />
                <a href="#" id="lnk_poppup_1" onclick="openMkbNomenclature(this);">МКБ <img src="img/moreinfo.gif" alt="info" border="0" width="17" height="17" /></a> водеща диагноза
                &nbsp;&nbsp;<span class="primary"><strong id="mkb_code_1"><?=((isset($f['mkb_code_1']))?HTMLFormat($f['mkb_code_1']):'')?></strong></span>
                <label id="mkb_desc_1"><?=((isset($f['mkb_desc_1']))?HTMLFormat($f['mkb_desc_1']):'')?></label>
                <input type="text" id="mkb_id_2" name="mkb_id_2" value="<?=((isset($f['mkb_id_2']))?HTMLFormat($f['mkb_id_2']):'')?>" onkeyup="if(this.value==''){$('#mkb_desc_2').html('');$('#mkb_code_2').html('');}" size="10" maxlength="50" />
                <a href="#" id="lnk_poppup_2" onclick="openMkbNomenclature(this);">МКБ <img src="img/moreinfo.gif" alt="info" border="0" width="17" height="17" /></a> общо заболяване
                &nbsp;&nbsp;<span class="primary"><strong id="mkb_code_2"><?=((isset($f['mkb_code_2']))?HTMLFormat($f['mkb_code_2']):'')?></strong></span>
                <label id="mkb_desc_2"><?=((isset($f['mkb_desc_2']))?HTMLFormat($f['mkb_desc_2']):'')?></label>
                <input type="text" id="mkb_id_3" name="mkb_id_3" value="<?=((isset($f['mkb_id_3']))?HTMLFormat($f['mkb_id_3']):'')?>" onkeyup="if(this.value==''){$('#mkb_desc_3').html('');$('#mkb_code_3').html('');}" size="10" maxlength="50" />
                <a href="#" id="lnk_poppup_3" onclick="openMkbNomenclature(this);">МКБ <img src="img/moreinfo.gif" alt="info" border="0" width="17" height="17" /></a> трудова злополука
                &nbsp;&nbsp;<span class="primary"><strong id="mkb_code_3"><?=((isset($f['mkb_code_3']))?HTMLFormat($f['mkb_code_3']):'')?></strong></span>
                <label id="mkb_desc_3"><?=((isset($f['mkb_desc_3']))?HTMLFormat($f['mkb_desc_3']):'')?></label>
                <input type="text" id="mkb_id_4" name="mkb_id_4" value="<?=((isset($f['mkb_id_4']))?HTMLFormat($f['mkb_id_4']):'')?>" onkeyup="if(this.value==''){$('#mkb_desc_4').html('');$('#mkb_code_4').html('');}" size="10" maxlength="50" />
                <a href="#" id="lnk_poppup_3" onclick="openMkbNomenclature(this);">МКБ <img src="img/moreinfo.gif" alt="info" border="0" width="17" height="17" /></a> професионално заболяване
                &nbsp;&nbsp;<span class="primary"><strong id="mkb_code_4"><?=((isset($f['mkb_code_4']))?HTMLFormat($f['mkb_code_4']):'')?></strong></span>
                <label id="mkb_desc_4"><?=((isset($f['mkb_desc_4']))?HTMLFormat($f['mkb_desc_4']):'')?></label>
				<div class="hr"></div>
                Противопоказни усл. на труд:
                <input type="text" id="bad_work_env" name="bad_work_env" value="<?=((isset($f['bad_work_env']))?HTMLFormat($f['bad_work_env']):'')?>" size="38" />
              </td>
            </tr>
          </table></td>
        <td valign="top" class="rightSplit"><div id="hospitalsList" style="height:300px;">
            <?=echoPatientTelks($worker_id);?>
          </div></td>
      </tr>
      <tr>
        <td colspan="2" class="leftSplit rightSplit"><p align="center">
            <input type="button" id="btnSubmit" name="btnSubmit" value="Съхрани" class="nicerButtons" onclick="$('input#form_is_dirty').val(1);xajax_processTelk(xajax.getFormValues('frmTelk'));DisableEnableForm(true);return false;" />
            <input type="button" id="btnNewTelk" name="btnNewTelk" value="Ново решение" class="nicerButtons" onclick="$('input#form_is_dirty').val(1);newTelk();" />
          </p></td>
      </tr>
    </table>
  </form>
</div>
</body>
</html>
