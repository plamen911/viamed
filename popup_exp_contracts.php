<?php
require('includes.php');

//$exp_days = (isset($_GET['exp_days']) && is_numeric($_GET['exp_days'])) ? abs(intval($_GET['exp_days'])) : 7;
if(isset($_GET['btnSubmit'])) {
	$dbInst->fnExecSql(sprintf("UPDATE stm_info SET contract_exp_days = %d", $_GET['exp_days']));
	//setcookie("stm_exp_days", $exp_days, time() + 60 * 60 * 24 * 100, "/");
}
$rows = $dbInst->fnSelectSingleRow("SELECT contract_exp_days FROM stm_info LIMIT 1");
$exp_days = intval($rows['contract_exp_days']);

$perPage = (isset($_GET['perPage'])) ? abs(intval($_GET['perPage'])) : 25;
// PAGER BEGIN
require_once 'Pager/Pager_Wrapper.php';
$pagerOptions = array(
'mode'    => 'Jumping',			// Sliding
'delta'   => 28,				// 2
'perPage' => $perPage,
//'separator'=>'|',
'spacesBeforeSeparator'=>1,	// number of spaces before the separator
'spacesAfterSeparator'=>1,		// number of spaces after the separator
//'linkClass'=>'', 				// name of CSS class used for link styling
//'curPageLinkClassName'=>'',	// name of CSS class used for current page link
'urlVar' =>'page',				// name of pageNumber URL var, for example "pageID"
//'path'=>SECURE_URL,				// complete path to the page (without the page name)
'firstPagePre'=>'',				// string used before first page number
'firstPageText'=>'FIRST',		// string used in place of first page number
'firstPagePost'=>'',			// string used after first page number
'lastPagePre'=>'',				// string used before last page number
'lastPageText'=>'LAST',			// string used in place of last page number
'lastPagePost'=>'',				// string used after last page number
'curPageLinkClassName'=>'current',
'prevImg'=>'<img src="img/pg-prev.gif" alt="prev" width="16" height="16" border="0" align="texttop" />',
'nextImg'=>'<img src="img/pg-next.gif" alt="next" width="16" height="16" border="0" align="texttop" />',
'clearIfVoid'=>true				// if there's only one page, don't display pager
);

$query = "	SELECT f.*, f.name AS firm_name, l.*, c.*, p.*,
			strftime('%d.%m.%Y', f.contract_begin, 'localtime') AS contract_begin2,
			strftime('%d.%m.%Y', f.contract_end, 'localtime') AS contract_end2
			FROM firms f
			LEFT JOIN locations l ON (l.location_id = f.location_id)
			LEFT JOIN communities c ON (c.community_id = f.community_id)
			LEFT JOIN provinces p ON (p.province_id = f.province_id)";
$txtCondition = " 	WHERE f.is_active = '1'
					AND f.contract_end != ''
					AND strftime('%s', f.contract_end) >= strftime('%s','now')
					AND strftime('%s', f.contract_end) <= strftime('%s','now', '+$exp_days days')";

$sortArr = array('name','location_name','address','contract_begin','contract_end');
if (isset($_GET["sort_by"]) && in_array($_GET["sort_by"],$sortArr)) {
	$order = (isset($_GET['order']) && $_GET['order']=='ASC') ? 'ASC' : 'DESC';
	$txtCondition .= " ORDER BY `$_GET[sort_by]` $order, LOWER(f.name), l.location_name, c.community_name, p.province_name, f.firm_id";
}
else $txtCondition .= " ORDER BY f.contract_end DESC, LOWER(f.name), l.location_name, c.community_name, p.province_name, f.firm_id";

$query .= $txtCondition;
//die($query);
$db = $dbInst->getDBHandle();
$paged_data = Pager_Wrapper_PDO($db, $query, $pagerOptions);
$firms	 = $paged_data['data'];  //paged data
$links = $paged_data['links']; //xhtml links for page navigation
$current = (isset($paged_data['page_numbers']['current'])) ? $paged_data['page_numbers']['current'] : 0;
$totalItems = $paged_data['totalItems'];
$from = ($current) ? $paged_data['from'] : 0;
$to = $paged_data['to'];
// PAGER END


?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?=SITE_NAME?></title>
<link href="styles.css" rel="stylesheet" type="text/css" media="screen" />
<script type="text/javascript" src="js/RegExpValidate.js"></script>
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
	var parent = (window.opener) ? window.opener : self.parent;
	parent.document.getElementById('TB_ajaxWindowTitle').style.fontWeight = 'bold';
	parent.document.getElementById('TB_ajaxWindowTitle').innerHTML = 'Списък на договорите, изтичащи след <?=$exp_days?> дни';
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
//]]>
</script>
<style type="text/css">
body,html {
	background-image:none;
}
</style>
</head>
<body style="overflow:hidden;">
<div id="contentWrapper" style="width:780px">
  <div id="contentinner" align="center">
    <div class="panel" style="display:block;overflow:hidden;">
      <form id="frmDeadline" action="<?=basename($_SERVER['PHP_SELF'])?>" method="get">
        Договори, които изтичат след
        <input type="text" id="exp_days" name="exp_days" value="<?=$exp_days?>" size="3" maxlength="3" onkeypress="return numbersonly(this, event);" />
        дни &nbsp;&nbsp;&nbsp;
        <input type="submit" id="btnSubmit" name="btnSubmit" value="Покажи" class="nicerButtons" onclick="parent.document.getElementById('exp_days').innerHTML = parseInt(document.getElementById('exp_days').value, 10);" />
      </form>
      <div class="br"></div>
      <div id="actionsdiv">
        <table width="100%" border="0">
          <tr>
            <td align="right">Резултати <?=$from?> - <?=$to?> от <?=$totalItems?><?php if($paged_data['links']) { ?> / Иди на страница <?=$paged_data['links']?><?php } ?></td>
          </tr>
        </table>
      </div>
      <table id="listtable">
        <tbody>
          <tr>
            <th><?php if (isset($_GET["sort_by"])&&$_GET["sort_by"]=="name"){?><img src="img/<?php if (isset($_GET["order"])&&$_GET["order"]=="DESC"){ ?>sort_arrow_down.gif<?php } else { ?>sort_arrow_up.gif<?php } ?>" alt="Sort" width="16" height="16" border="0" /><?php } ?>
              <a href="<?=basename($_SERVER['PHP_SELF']).cleanQueryString('sort_by=name&order='.((isset($_GET["sort_by"])&&$_GET["sort_by"]=="name")?(($_GET["order"]=="DESC")?"ASC":"DESC"):"ASC"))?>" title="Сортиране по наименование">Наименование</a></th>
            <th><?php if (isset($_GET["sort_by"])&&$_GET["sort_by"]=="location_name"){?><img src="img/<?php if (isset($_GET["order"])&&$_GET["order"]=="DESC"){ ?>sort_arrow_down.gif<?php } else { ?>sort_arrow_up.gif<?php } ?>" alt="Sort" width="16" height="16" border="0" /><?php } ?>
              <a href="<?=basename($_SERVER['PHP_SELF']).cleanQueryString('sort_by=location_name&order='.((isset($_GET["sort_by"])&&$_GET["sort_by"]=="location_name")?(($_GET["order"]=="DESC")?"ASC":"DESC"):"ASC"))?>" title="Сортиране по населено място">Населено място</a></th>
            <th><?php if (isset($_GET["sort_by"])&&$_GET["sort_by"]=="address"){?><img src="img/<?php if (isset($_GET["order"])&&$_GET["order"]=="DESC"){ ?>sort_arrow_down.gif<?php } else { ?>sort_arrow_up.gif<?php } ?>" alt="Sort" width="16" height="16" border="0" /><?php } ?>
              <a href="<?=basename($_SERVER['PHP_SELF']).cleanQueryString('sort_by=address&order='.((isset($_GET["sort_by"])&&$_GET["sort_by"]=="address")?(($_GET["order"]=="DESC")?"ASC":"DESC"):"ASC"))?>" title="Сортиране по адрес">Адрес</a></th>
            <th>Договор рег. №</th>
            <th><?php if (isset($_GET["sort_by"])&&$_GET["sort_by"]=="contract_begin"){?><img src="img/<?php if (isset($_GET["order"])&&$_GET["order"]=="DESC"){ ?>sort_arrow_down.gif<?php } else { ?>sort_arrow_up.gif<?php } ?>" alt="Sort" width="16" height="16" border="0" /><?php } ?>
              <a href="<?=basename($_SERVER['PHP_SELF']).cleanQueryString('sort_by=contract_begin&order='.((isset($_GET["sort_by"])&&$_GET["sort_by"]=="contract_begin")?(($_GET["order"]=="DESC")?"ASC":"DESC"):"ASC"))?>" title="Сортиране по дата на сключване">Дата на сключване</a></th>
            <th><?php if (isset($_GET["sort_by"])&&$_GET["sort_by"]=="contract_end"){?><img src="img/<?php if (isset($_GET["order"])&&$_GET["order"]=="DESC"){ ?>sort_arrow_down.gif<?php } else { ?>sort_arrow_up.gif<?php } ?>" alt="Sort" width="16" height="16" border="0" /><?php } ?>
              <a href="<?=basename($_SERVER['PHP_SELF']).cleanQueryString('sort_by=contract_end&order='.((isset($_GET["sort_by"])&&$_GET["sort_by"]=="contract_end")?(($_GET["order"]=="DESC")?"ASC":"DESC"):"ASC"))?>" title="Сортиране по дата на изтичане">Дата на изтичане</a></th>
          </tr>
          <?php
          if(is_array($firms) && count($firms)>0) {
          	$i=0;
          	foreach ($firms as $row) {
          ?>
          <tr>
            <td align="left"><a href="#" onclick="parent.location='firm_info.php?firm_id=<?=$row['firm_id']?>&<?=SESS_NAME.'='.session_id()?>'" title="Отвори/Редактирай <?=HTMLFormat($row['name'])?>"><?=HTMLFormat($row['name'])?></a></td>
            <td align="left"><?=$row['location_name']?></td>
            <td align="left"><?=$row['address']?></td>
            <td align="center"><strong><?=$row['contract_num']?></strong></td>
            <td align="center"><?=((''!=$row['contract_begin2'])?$row['contract_begin2'].' г.':'')?></td>
            <td align="center"><span class="err"><?=((''!=$row['contract_end2'])?$row['contract_end2'].' г.':'')?></span></td>
          </tr>
          <?php
          	}
          } else {
          ?>
          <tr>
            <td align="left" colspan="8">Няма намерени резултати.</td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
      <div id="actionsdiv">
        <table width="100%" border="0">
          <tr>
            <td align="right">Резултати <?=$from?> - <?=$to?> от <?=$totalItems?><?php if($paged_data['links']) { ?> / Иди на страница <?=$paged_data['links']?><?php } ?></td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</div>
</body>
</html>
