<?php
require('includes.php');

$fname = (isset($_GET['id']) && strlen(trim($_GET['id'])) == 10) ? trim($_GET['id']) : 0;
if(!$fname) {
	die('Липсва наименование на архива!');
}
$fname = 'BKP'.$fname.'.db';
if(!is_file('db/'.$fname) || !file_exists('db/'.$fname)) {
	die($fname.' е невалиден архивен файл или не съществува!');
}


$perPage = (isset($_GET['perPage'])) ? abs(intval($_GET['perPage'])) : 25;

// PAGER BEGIN
require_once 'Pager/Pager_Wrapper.php';
$pagerOptions = array(
'mode'    => 'Jumping',			// Sliding
'delta'   => 10,				// 2
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
$query = "	SELECT f.*, strftime('%d.%m.%Y г. %H:%M:%S ч.', f.date_modified) AS date_modified2, l.location_name,
			c.community_name, p.province_name
			FROM firms f
			LEFT JOIN locations l ON (l.location_id = f.location_id)
			LEFT JOIN communities c ON (c.community_id = f.community_id)
			LEFT JOIN provinces p ON (p.province_id = f.province_id)";

$txtCondition = "WHERE is_active='1'";

if(isset($_GET['btnFind'])) {	// Filter properties
	if(isset($_GET['keyword']) && trim($_GET['keyword']) != '') {
		$keyword = $dbInst->checkStr($_GET['keyword']);
		$txtCondition .= (preg_match('/\bWHERE\b/', $txtCondition)) ? ' AND ' : ' WHERE ';
		$txtCondition .= "(f.name LIKE '%$keyword%' OR f.name LIKE '%".$dbInst->my_mb_ucfirst($keyword)."%' OR f.address LIKE '%$keyword%' OR f.address LIKE '%".$dbInst->my_mb_ucfirst($keyword)."%')";
	}
}	// Search end
$sortArr = array('name','location_name','address','date_modified');
if (isset($_GET["sort_by"]) && in_array($_GET["sort_by"],$sortArr)) {
	$order = (isset($_GET['order']) && $_GET['order']=='ASC') ? 'ASC' : 'DESC';
	$txtCondition .= " ORDER BY `$_GET[sort_by]` $order, LOWER(f.name), l.location_name, c.community_name, p.province_name, f.firm_id";
}
else $txtCondition .= " ORDER BY date_modified DESC, LOWER(f.name), l.location_name, c.community_name, p.province_name, f.firm_id";

$query .= $txtCondition;
//die($query);
$db = $dbInst->getDBHandle($fname);
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
<script type="text/javascript">
//<![CDATA[
function stripTable(tableid) {
	// Strip table
	$("#"+tableid+" tr:even").addClass("alternate");
	// Hightlight table rows
	$("#"+tableid+" tr").not(".notover").hover(function() {
		$(this).addClass("over");
	},function() {
		$(this).removeClass("over");
	});
}
function removeLine(childID) {
	theChild = document.getElementById(childID);
	theChild.parentNode.removeChild(theChild);
	return false;
}
$(document).ready(function() {
	var parent = (window.opener) ? window.opener : self.parent;
	parent.document.getElementById('TB_ajaxWindowTitle').style.fontWeight = 'bold';
	stripTable('listtable');
});
//]]>
</script>
<style type="text/css">
body, html {
	background-image:none;
}
</style>
</head>
<body>
<div id="contentWrapper" style="width:780px">
  <div id="contentinner" align="center">
    <div class="panel" style="display:block;overflow:hidden;">
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
            <th>Бр. работещи</th>
            <th><?php if (isset($_GET["sort_by"])&&$_GET["sort_by"]=="date_modified"){?><img src="img/<?php if (isset($_GET["order"])&&$_GET["order"]=="DESC"){ ?>sort_arrow_down.gif<?php } else { ?>sort_arrow_up.gif<?php } ?>" alt="Sort" width="16" height="16" border="0" /><?php } ?>
              <a href="<?=basename($_SERVER['PHP_SELF']).cleanQueryString('sort_by=date_modified&order='.((isset($_GET["sort_by"])&&$_GET["sort_by"]=="date_modified")?(($_GET["order"]=="DESC")?"ASC":"DESC"):"ASC"))?>" title="Сортиране по дата на актуализация">Последна<br />
              актуализация</a></th>
          </tr>
          <?php
          if(is_array($firms) && count($firms)>0) {
          	$i=0;
          	foreach ($firms as $row) {
          		$field = $dbInst->fnSelectSingleRow("SELECT COUNT(*) AS cnt FROM workers w WHERE w.firm_id=$row[firm_id] AND w.is_active='1' AND w.date_retired=''");
          		$num_workers = $field['cnt'];
          ?>
          <tr>
            <td align="left"><?=HTMLFormat($row['name'])?></td>
            <td align="left"><?=$row['location_name']?></td>
            <td align="left"><?=$row['address']?></td>
            <td align="center"><strong><?=$num_workers?></strong></td>
            <td align="center"><?=$row['date_modified2']?></td>
          </tr>
          <?php
          	}
          } else {
          ?>
          <tr>
            <td align="left" colspan="8">Няма намерени резултати.</td>
          </tr>
          <?php } ?>
          <tr class="notover">
            <td align="left" colspan="8">&nbsp;</td>
          </tr>
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
