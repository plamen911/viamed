<?php
require('includes.php');

// Xajax begin
require ('xajax/xajax_core/xajax.inc.php');
function processFirm($aFormValues) {
	$objResponse = new xajaxResponse();

	$objResponse->assign("btnSubmit","disabled",false);
	$objResponse->assign("btnSubmit","value","Съхрани");
	$objResponse->call("DisableEnableForm",false);

	global $dbInst;

	if($_SESSION['sess_user_level'] == 4 && $dbInst->getFirmsNum() >= 5) {	// Demo user
		$objResponse->alert("В Н И М А Н И Е ! \n\nТова е пробна версия на програмния продукт, която не позволява \nвъвеждане на повече от 5 фирми.");
		return $objResponse;
	}

	if(trim($aFormValues['name']) == '') {
		$objResponse->alert("Моля, въведете наименование на фирмата.");
		return $objResponse;
	}
	if($aFormValues['email'] != '' && !EMailIsCorrect($aFormValues['email'])) {
		$objResponse->alert("$aFormValues[email] е невалиден e-mail адрес!");
		return $objResponse;
	}
	if(!intval($aFormValues['location_id']) && trim($aFormValues['location_name']) == '') {
		$objResponse->assign("location_name","value","");
		$objResponse->assign("location_id","value",0);
	}
	if(!intval($aFormValues['community_id']) && trim($aFormValues['community_name']) == '') {
		$objResponse->assign("community_name","value","");
		$objResponse->assign("community_id","value",0);
	}
	if(!intval($aFormValues['province_id']) && trim($aFormValues['province_name']) == '') {
		$objResponse->assign("province_name","value","");
		$objResponse->assign("province_id","value",0);
	}

	$contract_begin = trim($aFormValues['contract_begin']);
	$contract_end = trim($aFormValues['contract_end']);
	$d = new ParseBGDate();
	if( $contract_begin != '' && !$d->Parse($contract_begin) ) {
		$objResponse->alert($contract_begin.' е невалидна дата!');
		return $objResponse;
	}
	if( $contract_end != '' && !$d->Parse($contract_end) ) {
		$objResponse->alert($contract_end.' е невалидна дата!');
		return $objResponse;
	}
	if($contract_begin != '' && $contract_end != '') {
		$d->Parse($contract_begin);
		$contract_begin = mktime(0, 0, 0, $d->getMonth(), $d->getDay(), $d->getYear());
		$d->Parse($contract_end);
		$contract_end = mktime(0, 0, 0, $d->getMonth(), $d->getDay(), $d->getYear());
		if($contract_begin > $contract_end) {
			$objResponse->alert('Датата на изтичане на договора не може да е преди датата на сключване!');
			return $objResponse;
		}
	}

	$dbInst->processFirm($aFormValues); // Insert firm
	$objResponse->assign("firmsList", "innerHTML", echoFirms());
	$objResponse->alert("Данните за фирмата бяха успешно въведени!");
	$objResponse->script('document.getElementById("frmFirm").reset()');
	$objResponse->assign('form_is_dirty', 'value', '1');
	$objResponse->call("stripTable", "listtable");
	return $objResponse;
}
$xajax = new xajax();
$xajax->registerFunction("processFirm");
$xajax->registerFunction("calcContractEnd");
$xajax->registerFunction("formatBGDate");
$xajax->registerFunction("guessLocation");
$xajax->registerFunction("guessCommunity");
$xajax->registerFunction("guessProvince");
//$xajax->setFlag("debug",true);
$echoJS = $xajax->getJavascript('xajax/');
$xajax->processRequest();
// Xajax end

function echoFirms() {
	global $dbInst;
	ob_start();
	?>
	<table id="listtable">
	  <?php
	  $firms = $dbInst->getFirms();
	  foreach ($firms as $firm) {
	  ?>
	  <tr>
	    <td><?=$firm['name']?></td>
	    <td><?=$firm['location_name']?></td>
	  </tr>
	  <?php
	  }
	  ?>
	  <tr>
	    <td>&nbsp;</td>
	    <td>&nbsp;</td>
	  </tr>
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
<script type="text/javascript" src="js/jquery-latest.pack.js"></script>
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

	$("#province_name").autocomplete("autocompleter.php", {
		minChars: 0,
		extraParams: { search: "provinces" },
		width: 260,
		scroll: true,
		scrollHeight: 300,
		selectFirst: false
	});
	$("#province_name").result(function(event, data, formatted) {
		if (data) $("#province_id").val(data[1]);
	});

	$("#community_name").autocomplete("autocompleter.php", {
		minChars: 0,
		extraParams: { search: "communities" },
		width: 260,
		scroll: true,
		scrollHeight: 300,
		selectFirst: false
	});
	$("#community_name").result(function(event, data, formatted) {
		if (data) $("#community_id").val(data[1]);
	});

	$("#location_name").autocomplete("autocompleter.php", {
		minChars: 0,
		extraParams: { search: "locations" },
		width: 260,
		scroll: true,
		scrollHeight: 300,
		selectFirst: false
	});
	$("#location_name").result(function(event, data, formatted) {
		if (data) $("#location_id").val(data[1]);
	});

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
  <form id="frmFirm" action="javascript:void(null);">
    <input type="hidden" id="form_is_dirty" name="form_is_dirty" value="0" />
    <table cellpadding="0" cellspacing="0" class="formBg">
      <tr>
        <th colspan="2" class="leftSplit rightSplit topSplit">Фирма</th>
        <th class="rightSplit topSplit">Списък на фирмите</th>
      </tr>
      <tr>
        <td class="leftSplit"><strong>Наименование: </strong></td>
        <td class="rightSplit"><input type="text" id="name" name="name" value="" size="40" maxlength="50" />
        </td>
        <td rowspan="13" valign="top" class="rightSplit"><div id="firmsList">
          <?=echoFirms()?>
          </div></td>
      </tr>
      <tr>
        <td nowrap="nowrap" class="leftSplit">Населено място: </td>
        <td class="rightSplit"><input type="text" id="location_name" name="location_name" value="" size="40" maxlength="50" onchange="xajax_guessLocation(this.value);return false;" />
          <input type="hidden" id="location_id" name="location_id" value="0" />
        </td>
      </tr>
      <tr>
        <td nowrap="nowrap" class="leftSplit">Община: </td>
        <td class="rightSplit"><input type="text" id="community_name" name="community_name" value="" size="40" maxlength="50" onchange="xajax_guessCommunity(this.value);return false;" />
          <input type="hidden" id="community_id" name="community_id" value="0" />
        </td>
      </tr>
      <tr>
        <td nowrap="nowrap" class="leftSplit">Област: </td>
        <td class="rightSplit"><input type="text" id="province_name" name="province_name" value="" size="40" maxlength="50" onchange="xajax_guessProvince(this.value);return false;" />
          <input type="hidden" id="province_id" name="province_id" value="0" />
        </td>
      </tr>
      <tr>
        <td class="leftSplit">Адрес: </td>
        <td class="rightSplit"><input type="text" id="address" name="address" value="" size="40" maxlength="50" />
        </td>
      </tr>
      <tr>
        <td class="leftSplit">Тел. 1: </td>
        <td class="rightSplit"><input type="text" id="phone1" name="phone1" value="" size="40" maxlength="50" />
        </td>
      </tr>
      <tr>
        <td class="leftSplit">Тел. 2: </td>
        <td class="rightSplit"><input type="text" id="phone2" name="phone2" value="" size="40" maxlength="50" />
        </td>
      </tr>
      <tr>
        <td class="leftSplit">Факс: </td>
        <td class="rightSplit"><input type="text" id="fax" name="fax" value="" size="40" maxlength="50" />
        </td>
      </tr>
      <tr>
        <td class="leftSplit">E-mail: </td>
        <td class="rightSplit"><input type="text" id="email" name="email" value="" size="40" maxlength="50" />
          <input type="hidden" id="notes" name="notes" value="" />
        </td>
      </tr>
      <tr>
        <td class="leftSplit">Договор рег. №: </td>
        <td class="rightSplit"><input type="text" id="contract_num" name="contract_num" value="" size="40" maxlength="50" />
        </td>
      </tr>
      <tr>
        <td class="leftSplit">Дата на сключване: </td>
        <td class="rightSplit"><input type="text" id="contract_begin" name="contract_begin" value="" size="20" maxlength="10" onchange="xajax_calcContractEnd(this.value);return false;" onclick="scwShow(this,event);" class="date_input" />
          г. </td>
      </tr>
      <tr>
        <td class="leftSplit">Дата на изтичане: </td>
        <td class="rightSplit"><input type="text" id="contract_end" name="contract_end" value="" size="20" maxlength="10" onchange="xajax_formatBGDate(this.name,this.value);return false;" onclick="scwShow(this,event);" class="date_input" />
          г. </td>
      </tr>
      <tr>
        <td colspan="2" class="leftSplit rightSplit"><p align="center">
            <input type="hidden" id="firm_id" name="firm_id" value="0" />
            <input type="button" id="btnSubmit" name="btnSubmit" value="Съхрани" class="nicerButtons" onclick="xajax_processFirm(xajax.getFormValues('frmFirm'));DisableEnableForm(true);return false;" />
          </p></td>
      </tr>
    </table>
  </form>
</div>
</body>
</html>