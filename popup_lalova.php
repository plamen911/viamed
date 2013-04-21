<?php
require('includes.php');

/*$srcStm['stm_info'] = 'Служба по трудова медицина "ТМ-Д-Р МАРГАРИТА ЛАЛОВА" ЕООД';
//$srcStm['stm_db'] = '/www/lynxlake.org/stm/root/lalova/db/stm.db';
$srcStm['stm_db'] = 'E:\plamen\htdocs\stm2008\hipokrat\db\stm.db';*/
$destStm['stm_info'] = 'Служба по трудова медицина "ЛАЛОВИ" ООД';
//$destStm['stm_db'] = '/www/lynxlake.org/stm/root/lalovi/db/stm.db';
$destStm['stm_db'] = 'E:\plamen\htdocs\stm2008\hipokrat\db\lalovi\stm.db';


class SqliteDBLalova extends SqliteDB {

	function __construct($dbfilepath = null) {
		//database connection
		try {
			$db = new PDO('sqlite:' . $dbfilepath); //sqlite 3
			$this->dbhandle = $db;
			echo 'Inside extended class.<br>';
		}
		catch (PDOException $error) {
			print "error: " . $error->getMessage() . "<br/>";
			die();
		}
	}
}
/*
$dbInst = new SqliteDBLalova($srcStm['stm_db']);

$rows = $dbInst->query("SELECT * FROM `stm_info`");
echo '<pre>';
print_r($rows);
echo '</pre>';

echo '<hr />';

//unset($dbInst);
$dbInst = new SqliteDBLalova($destStm['stm_db']);

$rows = $dbInst->query("SELECT * FROM `stm_info`");
echo '<pre>';
print_r($rows);
echo '</pre>';

*/

// Xajax begin
require ('xajax/xajax_core/xajax.inc.php');
function processTransfer($aFormValues = null) {
	$objResponse = new xajaxResponse();
	
	global $destStm;
	global $dbInst;

	$objResponse->assign("btnTransfer","disabled",false);
	$objResponse->script('$("#btnTransfer").html(\'Прехвърли избраните фирми в '.$destStm['stm_info'].'\')');
	$objResponse->call("DisableEnableForm",false);
	
	if(!empty($aFormValues)) {
		$IDs = array();
		foreach ($aFormValues as $key => $value) {
			if(preg_match('/^chk_(\d+)$/', $key, $matches)) {
				$IDs[] = $matches[1];
			}
		}
		if(!empty($IDs)) {
			foreach ($IDs as $firm_id) {
				$rows = $dbInst->query("SELECT * FROM `firms` WHERE `firm_id` = $firm_id");
				if(!empty($rows[0])) {
					$keys = array();
					$values = array();
					foreach ($rows[0] as $key => $value) {
						if('firm_id' == $key || is_numeric($key)) continue;
						$keys[] = "`".$key."`";
						if(null === $value) $value = '';
						$values[] = (is_numeric($value)) ? $value : "'".$dbInst->checkStr($value)."'";
					}
					$sql = "INSERT INTO `firms` (".implode(',', $keys).") VALUES (".implode(',', $values).")";
					$new_firm_id = srcQuery($sql);
					
					
					
					//$objResponse->alert($sql);
				}
			}
		}
		//$objResponse->script('window.location.reload()');
	}
	
	return $objResponse;
}
$xajax = new xajax();
$xajax->registerFunction("processTransfer");
//$xajax->setFlag("debug",true);
$echoJS = $xajax->getJavascript('xajax/');
$xajax->processRequest();
// Xajax end

function srcQuery($sql = '') {
	global $destStm;
	$dbInst = new SqliteDBLalova($destStm['stm_db']);
	$result = $dbInst->query($sql);
	unset($dbInst);
	return $result;
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

$txtCondition = " WHERE is_active='1'";

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
$db = $dbInst->getDBHandle();
$paged_data = Pager_Wrapper_PDO($db, $query, $pagerOptions);
$firms	 = $paged_data['data'];  //paged data
$links = $paged_data['links']; //xhtml links for page navigation
$current = (isset($paged_data['page_numbers']['current'])) ? $paged_data['page_numbers']['current'] : 0;
$totalItems = $paged_data['totalItems'];
$from = ($current) ? $paged_data['from'] : 0;
$to = $paged_data['to'];
// PAGER END

$s = $dbInst->getStmInfo();
$stm_name = preg_replace('/\<br\s*\/?\>/', '', $s['stm_name']);

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
$(function() {
	stripTable('listtable');
	if($.browser.msie) {
		$("input[type='text']:disabled,textarea:disabled,select:disabled").css("background-color", "#EEEEEE");
		$(":checkbox").css("border","none");
	}
	$("#chk_all").click(function(e){
		var checked = $(this)[0].checked;
		$("input[id^='chk_']").each(function(i){
			$(this)[0].checked = checked;
		}); 
	});
	$("#btnTransfer").click(function(e){
		e.preventDefault();
		var num_firms = $("input[id^='chk_']:checked").length;
		if(!num_firms) {
			alert('Моля, изберете фирми, които искате да прехвърлите в <?=$destStm['stm_info']?>');
			return false;
		}
		var label = (1 == num_firms) ? 'избраната фирма' : 'избраните ' + num_firms + ' фирми';
		if(confirm('Нистина ли искате да прехвърлите ' + label + ' в <?=$destStm['stm_info']?>?')) {
			$("#btnTransfer")[0].disabled = true;
			$("#btnTransfer").html('моля, изчакайте...');
			xajax_processTransfer(xajax.getFormValues('frmFirm'));
			DisableEnableForm(true);
			return false;
		}
	});
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
<div id="contentWrapper" style="width:800px">
  <div id="contentinner" align="center">
    <div class="panel" style="display:block;overflow:hidden;">
      <h2><?=HTMLFormat($stm_name)?></h2>
      <div align="center"><button id="btnTransfer" name="btnTransfer">Прехвърли избраните фирми в <?=HTMLFormat($destStm['stm_info'])?></button></div>
      <div id="actionsdiv">
        <table width="100%" border="0">
          <tr>
            <td align="right">Резултати <?=$from?> - <?=$to?> от <?=$totalItems?><?php if($paged_data['links']) { ?> / Иди на страница <?=$paged_data['links']?><?php } ?></td>
          </tr>
        </table>
      </div>
      <form id="frmFirm" name="frmFirm" action="javascript:void(0)">
      <table id="listtable">
        <tbody>
          <tr>
            <th><input type="checkbox" id="chk_all" name="chk_all" value="1" /></th>
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
            <td align="left"><input type="checkbox" id="chk_<?=$row['firm_id']?>" name="chk_<?=$row['firm_id']?>" value="1" /></td>
            <td align="left"><?=HTMLFormat($row['name'])?></td>
            <td align="left"><?=$row['location_name']?></td>
            <td align="left"><?=$row['address']?></td>
            <td align="center"><strong><?=$num_workers?></strong></td>
            <td align="center" nowrap="nowrap"><?=$row['date_modified2']?></td>
          </tr>
          <?php
          	}
          } else {
          ?>
          <tr>
            <td align="left" colspan="9">Няма намерени резултати.</td>
          </tr>
          <?php } ?>
          <tr class="notover">
            <td align="left" colspan="9">&nbsp;</td>
          </tr>
        </tbody>
      </table>
      </form>
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
