<?php
require('includes.php');

$firm_id = (isset($_GET['firm_id']) && is_numeric($_GET['firm_id'])) ? intval($_GET['firm_id']) : 0;
$firmInfo = $dbInst->getFirmInfo($firm_id);
if(!$firmInfo) {
	die('Липсва индентификатор на фирмата!');
}
$parent_id = (isset($_GET['parent_id']) && is_numeric($_GET['parent_id'])) ? intval($_GET['parent_id']) : 0;
if($parent_id && !$parent_id = $dbInst->GiveValue('id', 'pro_groups', "WHERE id = $parent_id")) {
	$parent_id = 0;
}
$progroup_id = (isset($_GET['progroup_id']) && is_numeric($_GET['progroup_id'])) ? intval($_GET['progroup_id']) : 0;
if($progroup_id && !$progroup_id = $dbInst->GiveValue('id', 'pro_groups', "WHERE id = $progroup_id")) {
	$progroup_id = 0;
}
$position_id = (isset($_GET['position_id']) && is_numeric($_GET['position_id'])) ? intval($_GET['position_id']) : 0;
if($position_id && !$position_id = $dbInst->GiveValue('position_id', 'firm_positions', "WHERE position_id = $position_id AND firm_id = $firm_id")) {
	$position_id = 0;
}
if(!$position_id) {
	die('Липсва индентификатор на длъжността!');
}

// Xajax begin
require ('xajax/xajax_core/xajax.inc.php');
function processCategory($aFormValues) {
	$objResponse = new xajaxResponse();
	$objResponse->call("DisableEnableForm", false);

	global $dbInst;
	global $firm_id;

	$position_id = intval($aFormValues['position_id']);
	$parent_id = intval($aFormValues['parent_id']);
	$name = $dbInst->checkStr($aFormValues['parent_name']);
	if(empty($name)) {
		$objResponse->alert("Моля, въведете наименование на категорията.");
		return $objResponse;
	}

	switch ($aFormValues['catgAction']) {
		case 'add':
			$parent_id = $dbInst->query("INSERT INTO `pro_groups` (`parent`, `name`, `num`) VALUES (0, '$name', 0)");
			break;
		case 'edit':
			$dbInst->query("UPDATE `pro_groups` SET `name` = '$name' WHERE `id` = $parent_id");
			break;
		case 'delete':
			$dbInst->query("DELETE FROM `pro_groups` WHERE `id` = $parent_id");
			$dbInst->query("DELETE FROM `pro_groups` WHERE `parent` = $parent_id");
			$parent_id = 0;
			break;
	}
	$objResponse->script("window.location='".$_SERVER['PHP_SELF']."?firm_id=$firm_id&parent_id=$parent_id&progroup_id=0&position_id=$position_id&".session_name()."=".session_id()."'");
	return $objResponse;
}
function processProGroup($aFormValues) {
	$objResponse = new xajaxResponse();
	$objResponse->call("DisableEnableForm", false);

	global $dbInst;
	global $firm_id;

	$position_id = intval($aFormValues['position_id']);
	$parent_id = intval($aFormValues['parent_id']);
	if(empty($parent_id)) {
		$objResponse->alert("Моля, изберете категория.");
		return $objResponse;
	}
	$progroup_id = (isset($aFormValues['progroup_id'])) ? intval($aFormValues['progroup_id']) : 0;
	$name = $dbInst->checkStr($aFormValues['progroup_name']);
	if(empty($name)) {
		$objResponse->alert("Моля, въведете наименование на проф. група.");
		return $objResponse;
	}

	switch ($aFormValues['progroupAction']) {
		case 'add':
			$progroup_id = $dbInst->query("INSERT INTO `pro_groups` (`parent`, `name`, `num`) VALUES ($parent_id, '$name', 0)");
			break;
		case 'edit':
			$dbInst->query("UPDATE `pro_groups` SET `name` = '$name' WHERE `id` = $progroup_id");
			break;
		case 'delete':
			$dbInst->query("DELETE FROM `pro_groups` WHERE `id` = $progroup_id");
			$progroup_id = 0;
			break;
	}
	// Fix pro group order
	$rows = $dbInst->query("SELECT * FROM `pro_groups` WHERE `parent` = $parent_id ORDER BY `id`");
	if(!empty($rows)) {
		$i = 1;
		foreach ($rows as $row) {
			$dbInst->query("UPDATE `pro_groups` SET `num` = $i WHERE `id` = $row[id]");
			$i++;
		}
	}
	$objResponse->script("window.location='".$_SERVER['PHP_SELF']."?firm_id=$firm_id&parent_id=$parent_id&progroup_id=$progroup_id&position_id=$position_id&".session_name()."=".session_id()."'");
	return $objResponse;
}
function assignProGroup($aFormValues) {
	$objResponse = new xajaxResponse();
	$objResponse->call("DisableEnableForm", false);

	global $dbInst;
	global $firm_id;

	$position_id = (isset($aFormValues['position_id'])) ? intval($aFormValues['position_id']) : 0;
	$progroup_id = (isset($aFormValues['progroup_id'])) ? intval($aFormValues['progroup_id']) : 0;
	if(empty($progroup_id)) {
		$objResponse->alert('Моля, изберете професионална група.');
		return $objResponse;
	}
	$row = $dbInst->fnSelectSingleRow("SELECT `parent`, `name`, `num` FROM `pro_groups` WHERE `id` = $progroup_id AND `parent` > 0");
	if(empty($row)) {
		$objResponse->alert('Невалиден номер на професионалната група!');
		return $objResponse;
	}
	$converter = new ConvertRoman($row['num']);
	$row['num'] = $converter->result();
	$progroup_lbl = '<strong>'.$row['num'].'. '.addslashes($row['name']).'</strong> <a href="javascript:void(0)" onclick="updateAllProGroups('.$position_id.', 0, &quot;--&quot;, 0);" title="Изтриване на проф. група">(-)</a>';
	$parent = $row['parent'];
	$objResponse->script('if(parent.updateAllProGroups) parent.updateAllProGroups('.$position_id.', '.$progroup_id.', \''.$progroup_lbl.'\', '.$parent.')');
	$objResponse->script('if(parent.$.colorbox) parent.$.colorbox.close()');
	return $objResponse;
}
$xajax = new xajax();
$xajax->registerFunction("processCategory");
$xajax->registerFunction("processProGroup");
$xajax->registerFunction("assignProGroup");
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
$(document).ready(function() {
	var parent = (window.opener) ? window.opener : self.parent;
	if(parent.document.getElementById('TB_closeWindowButton')) {
		parent.document.getElementById('TB_ajaxWindowTitle').style.fontWeight = 'bold';
		// Reload the parent window when the close button of Thickbox popup is clicked!
		parent.document.getElementById('TB_closeWindowButton').onclick = function() {
			if($('input#form_is_dirty').val() != 0) {
				parent.location.reload();
			}
		};
	}
	//
	$("#lnkAddCatg").click(function(e){
		e.preventDefault();
		$("#catgEditBar").show();
		$("#catgShowBar").hide();
		$("#catgAction").val('add');
		$("#parent_name").val('');
		<?php if(!empty($parent_id)) { ?>$("#progroupWrapper").hide();<?php } ?>
	});
	$("#lnkEditCatg").click(function(e){
		e.preventDefault();
		if($("#parent_id").val() == null) {
			alert("Моля, изберете категория.");
			return false;
		}
		$("#catgEditBar").show();
		$("#catgShowBar").hide();
		$("#catgAction").val('edit');
		var parent_name = $.trim($("#parent_id")[0].options[$("#parent_id")[0].selectedIndex].text);
		$("#parent_name").val(parent_name);
		<?php if(!empty($parent_id)) { ?>$("#progroupWrapper").hide();<?php } ?>
	});
	$("#lnkDelCatg").click(function(e){
		e.preventDefault();
		if($("#parent_id").val() == null) {
			alert("Моля, изберете категория.");
			return false;
		}
		var parent_name = $.trim($("#parent_id")[0].options[$("#parent_id")[0].selectedIndex].text);
		$("#parent_name").val(parent_name);
		if(confirm("Наистина ли искате да изтриете категория '" + parent_name + "' и всички проф. групи в нея?")) {
			$("#catgAction").val('delete');
			xajax_processCategory(xajax.getFormValues('frmProGroups'));
			DisableEnableForm(true);
		}
		return false;
	});
	$("#lnkCancelCatg").click(function(e){
		e.preventDefault();
		$("#catgEditBar").hide();
		$("#catgShowBar").show();
		$("#catgAction").val('');
		<?php if(!empty($parent_id)) { ?>$("#progroupWrapper").show();<?php } ?>
	});
	$("#parent_id").change(function(e){
		var parent_id = $(this).val();
		window.location = '<?=$_SERVER['PHP_SELF']?>?firm_id=<?=$firm_id?>&parent_id=' + parent_id + '&progroup_id=0&position_id=<?=$position_id?>&<?=session_name().'='.session_id()?>';
	});
	$("#lnkSaveCatg").click(function(e){
		e.preventDefault();
		xajax_processCategory(xajax.getFormValues('frmProGroups'));
		DisableEnableForm(true);
		return false;
	});
	// Proofessional groups
	$("#lnkAddProGroup").click(function(e){
		e.preventDefault();
		$("#progroupEditBar").show();
		$("#progroupShowBar").hide();
		$("#progroupAction").val('add');
		$("#progroup_name").val('');
	});
	$("#lnkEditProGroup").click(function(e){
		e.preventDefault();
		if($("#progroup_id").val() == null) {
			alert("Моля, изберете професионална група.");
			return false;
		}
		$("#progroupEditBar").show();
		$("#progroupShowBar").hide();
		$("#progroupAction").val('edit');
		var progroup_name = $.trim($("#progroup_id")[0].options[$("#progroup_id")[0].selectedIndex].text);
		// Get rid the number in front
		progroup_name = progroup_name.substr(progroup_name.indexOf(' ') + 1);
		$("#progroup_name").val(progroup_name);
	});
	$("#lnkDelProGroup").click(function(e){
		e.preventDefault();
		if($("#progroup_id").val() == null) {
			alert("Моля, изберете професионална група.");
			return false;
		}
		var progroup_name = $.trim($("#progroup_id")[0].options[$("#progroup_id")[0].selectedIndex].text);
		// Get rid the number in front
		progroup_name = progroup_name.substr(progroup_name.indexOf(' ') + 1);
		$("#progroup_name").val(progroup_name);
		if(confirm("Наистина ли искате да изтриете професионална група '" + progroup_name + "'?")) {
			$("#progroupAction").val('delete');
			xajax_processProGroup(xajax.getFormValues('frmProGroups'));
			DisableEnableForm(true);
		}
		return false;
	});
	$("#lnkCancelProGroup").click(function(e){
		e.preventDefault();
		$("#progroupEditBar").hide();
		$("#progroupShowBar").show();
		$("#progroupAction").val('');
	});
	$("#lnkSaveProGroup").click(function(e){
		e.preventDefault();
		xajax_processProGroup(xajax.getFormValues('frmProGroups'));
		DisableEnableForm(true);
		return false;
	});
	//
	$("#btnAssignProGroup").click(function(e){
		e.preventDefault();
		xajax_assignProGroup(xajax.getFormValues('frmProGroups'));
		DisableEnableForm(true);
		return false;
	});
});
//]]>
</script>
<style type="text/css">
body, html {
	background-image:none;
	background-color:#EEEEEE;
}
#contentinner p {
	padding:4px;
}
</style>
</head>
<body>
<div id="contentinner" align="center">
  <form id="frmProGroups" name="frmProGroups" action="javascript:void(null);">
    <input type="hidden" id="catgAction" name="catgAction" value="" />
    <input type="hidden" id="firm_id" name="firm_id" value="<?=$firm_id?>" />
    <input type="hidden" id="position_id" name="position_id" value="<?=$position_id?>" />
    <table cellpadding="0" cellspacing="0" class="formBg">
      <tr>
        <td class="leftSplit rightSplit topSplit"><p><strong>Категория:</strong> <span id="catgBar">( <a id="lnkAddCatg" href="#" title="Добави нова категория">Добави</a> <?php if(!empty($parent_id)) { ?>| <a id="lnkEditCatg" href="#" title="Редактирай текущата категория">Редактирай</a> | <a id="lnkDelCatg" href="#" title="Изтрий текущата категория и всички групи в нея">Изтрий</a> <?php } ?>)</span></p>
          <p id="catgShowBar">
            <select id="parent_id" name="parent_id">
              <option value="0">-- изберете -- </option>
              <?php
              $rows = $dbInst->query("SELECT * FROM `pro_groups` WHERE `parent` = 0 ORDER BY `name`");
              if(!empty($rows)) {
              	foreach ($rows as $row) {
              		echo '<option value="'.$row['id'].'"'.((!empty($parent_id) && !strcmp($parent_id, $row['id'])) ? ' selected="selected"' : '').'>'.HTMLFormat($row['name']).'</option>';
              	}
              }
              ?>
            </select>
          </p></td>
      </tr>
      <tr>
        <td id="catgEditBar" class="leftSplit rightSplit" style="display:none"><p>
          <input type="text" id="parent_name" name="parent_name" value="" size="50" />
          ( <a id="lnkSaveCatg" href="#">Съхрани</a> | <a id="lnkCancelCatg" href="#">Отмени</a> )</p></td>
      </tr>
    <?php 
    if(!empty($parent_id)) {
    	$rows = $dbInst->query("SELECT * FROM `pro_groups` WHERE `parent` = $parent_id ORDER BY `num`");
    	?>
      <tr>
        <td id="progroupWrapper" class="leftSplit rightSplit"><input type="hidden" id="progroupAction" name="progroupAction" value="" />
          <p><strong>Професионални групи:</strong> <span id="progroupBar">( <a id="lnkAddProGroup" href="#" title="Добави нова проф. група">Добави</a> <?php if(!empty($rows)) { ?>| <a id="lnkEditProGroup" href="#" title="Редактирай текущата проф. група">Редактирай</a> | <a id="lnkDelProGroup" href="#" title="Изтрий текущата проф. група">Изтрий</a> <?php } ?>)</span></p>
          <p id="progroupEditBar" style="display:none">
            <input type="text" id="progroup_name" name="progroup_name" value="" size="50" />
            ( <a id="lnkSaveProGroup" href="#">Съхрани</a> | <a id="lnkCancelProGroup" href="#">Отмени</a> )
          <p>
            <select id="progroup_id" name="progroup_id" size="10">
              <?php
              if(!empty($rows)) {
              	foreach ($rows as $row) {
              		$converter = new ConvertRoman($row['num']);
              		$row['num'] = $converter->result();
              		echo '<option value="'.$row['id'].'"'.((!empty($progroup_id) && !strcmp($progroup_id, $row['id'])) ? ' selected="selected"' : '').'>'.$row['num'].'. '.HTMLFormat($row['name']).'</option>';
              	}
              }
              ?>
            </select>
          </p>
          <p>
            <input type="button" id="btnAssignProGroup" name="btnAssignProGroup" value="Определи проф. група" class="nicerButtons" style="width:140px;" />
          </p></td>
      </tr>
    <?php } ?>
    </table>
  </form>
</div>
</body>
</html>
