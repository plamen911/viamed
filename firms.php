<?php
require('includes.php');

$abs_path = '';
$net_path = '';
$paths = $dbInst->getDocsPath();
if(isset($paths['abs_path']) && !empty($paths['abs_path'])) {
	$abs_path = $paths['abs_path'];
}
if(isset($paths['net_path']) && !empty($paths['net_path'])) {
	$net_path = $paths['net_path'];
}

// Xajax begin
require ('xajax/xajax_core/xajax.inc.php');
function deleteFirm($firm_id) {
	$objResponse = new xajaxResponse();

	if($_SESSION['sess_user_level'] == 1) { /* admin rights only */
		global $dbInst;
		$dbInst->removeFirm($firm_id);
		$objResponse->script("window.location.reload();");
	}

	return $objResponse;
}

$xajax = new xajax();
$xajax->registerFunction("deleteFirm");
//$xajax->setFlag("debug",true);
$echoJS = $xajax->getJavascript('xajax/');
$xajax->processRequest();
// Xajax end

$perPage = (isset($_GET['perPage'])) ? abs(intval($_GET['perPage'])) : 25;
$_SESSION['sess_QUERY_STRING'] = (isset($_SERVER['QUERY_STRING'])) ? $_SERVER['QUERY_STRING'] : '';

// PAGER BEGIN
require_once 'Pager/Pager_Wrapper.php';
$pagerOptions = array(
	'mode'    => 'Jumping',			// Sliding
	'delta'   => 1000,				// 2
	'perPage' => $perPage,
	'separator'=> ' | ',
	'spacesBeforeSeparator' => 0, // number of spaces before the separator
	'spacesAfterSeparator' => 0, // number of spaces after the separator
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
/*$query = "	SELECT f.*, l.location_name, c.community_name, p.province_name,
(SELECT COUNT(*) FROM workers w WHERE w.firm_id=f.firm_id AND w.date_retired='' AND w.is_active='1') AS num_workers
FROM firms f
LEFT JOIN locations l ON (l.location_id = f.location_id)
LEFT JOIN communities c ON (c.community_id = f.community_id)
LEFT JOIN provinces p ON (p.province_id = f.province_id)"; */

$txtCondition = '';
$is_active = (isset($_GET['is_active']) && in_array($_GET['is_active'], array('1', '0', ''))) ? $_GET['is_active'] : '1';

$query = "  SELECT f.*, l.location_name, c.community_name, p.province_name
            FROM firms f
            LEFT JOIN locations l ON (l.location_id = f.location_id)
            LEFT JOIN communities c ON (c.community_id = f.community_id)
            LEFT JOIN provinces p ON (p.province_id = f.province_id)";
if('' == $is_active) { /*$txtCondition = "WHERE 1";*/ }
else { $txtCondition .= "WHERE is_active = '$is_active'"; }

if(isset($_GET['btnFind'])) {	// Filter properties
	if(isset($_GET['keyword']) && trim($_GET['keyword']) != '') {
		$keyword = $dbInst->checkStr($_GET['keyword']);
		$txtCondition .= (preg_match('/\bWHERE\b/', $txtCondition)) ? ' AND ' : ' WHERE ';
		$txtCondition .= "(f.name LIKE '%$keyword%' 
							OR f.name LIKE '%".$dbInst->my_mb_ucfirst($keyword)."%' 
							OR f.name LIKE '%".mb_strtoupper($keyword,'utf-8')."%' 
							OR f.address LIKE '%$keyword%' 
							OR f.address LIKE '%".$dbInst->my_mb_ucfirst($keyword)."%' 
							OR f.address LIKE '%".mb_strtoupper($keyword,'utf-8')."%')";
	}
}	// Search end
$sortArr = array('name','location_name','address','num_workers');
if (isset($_GET["sort_by"]) && in_array($_GET["sort_by"],$sortArr)) {
	$order = (isset($_GET['order']) && $_GET['order']=='ASC') ? 'ASC' : 'DESC';
	$txtCondition .= " ORDER BY f.`is_active` DESC, `$_GET[sort_by]` $order, LOWER(name), l.location_name, c.community_name, p.province_name, f.firm_id";
}
else $txtCondition .= " ORDER BY f.`is_active` DESC, LOWER(name), l.location_name, c.community_name, p.province_name, f.firm_id";

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

ob_start();
?>
<script type="text/javascript">
//<![CDATA[
$(document).ready(function(){
	$("#lnkAddFirm").colorbox({width:"100%", height:"100%", iframe:true, overlayClose:false, title:$("#lnkAddFirm").attr('title'), transition:"none", fastIframe:false, fixed:true});
	$("a[id^='lnkanalysis_']").each(function(i){
		var win_title = $(this).attr('title');
		$(this).colorbox({width:"80%", height:"80%", iframe:true, overlayClose:false, title:win_title, transition:"none", fastIframe:false, fixed:true });
	});
});
//]]>
</script>
<?php
$echoJS .= ob_get_clean();

include("header.php");
?>
	<script type="text/javascript">
	//<![CDATA[
	$(document).ready(function() {
		stripTable('listtable');
		// Toggle search form
		$("#lnkSearch").toggle(function() {
			$("#searchHolder").show("slow");
		},function(){
			$("#searchHolder").hide("slow");
		});
	});
	//]]>
	</script>
    <div id="wtitle" class="wtitle"><a id="lnkSearch" href="#">Търси</a> | <a id="lnkAddFirm" href="popup_firm.php?<?=SESS_NAME.'='.session_id()?>" title="Добавяне на нова фирма">Добави нова фирма</a></div>
      <!-- frmFind -->
    <div id="searchHolder" align="left" style="display:none;">
      <form id="frmFind" action="<?=basename($_SERVER['PHP_SELF'])?>" method="get">
        <input type="hidden" id="page" name="page" value="1" />
        <table cellpadding="0" cellspacing="0" class="formBg">
          <tr>
            <td align="left" class="leftSplit rightSplit topSplit">Наименование или адрес на фирмата:
              <input type="text" id="keyword" name="keyword" value="<?=((isset($_GET['keyword']))?HTMLFormat($_GET['keyword']):'')?>" size="35" />
              Покажи
              <select id="is_active" name="is_active">
                <option value=""<?=((''==$is_active)?' selected="selected"':'')?>>всички &nbsp;&nbsp;</option>
                <option value="1"<?=(('1'==$is_active)?' selected="selected"':'')?>>само активните &nbsp;&nbsp;</option>
                <option value="0"<?=(('0'==$is_active)?' selected="selected"':'')?>>само неактивните &nbsp;&nbsp;</option>
              </select> фирми
              <input type="submit" id="btnFind" name="btnFind" value="Намери" class="nicerButtons" /></td>
          </tr>
        </table>
      </form>
    </div>
    <!-- /frmFind -->
    <div class="panel" style="display:block;overflow:hidden;">
      <div class="pageline1">Резултати <?=$from?> - <?=$to?> от <?=$totalItems?><?php if($paged_data['links']) { ?> / Иди на страница <?=$paged_data['links']?><?php } ?></div>
      <!-- frmAgents -->
      <form id="frmFirms" action="<?=basename($_SERVER['PHP_SELF'])?>" method="get">
        <table id="listtable">
          <tbody>
            <tr>
              <th><?php if (isset($_GET["sort_by"])&&$_GET["sort_by"]=="name"){?><img src="img/<?php if (isset($_GET["order"])&&$_GET["order"]=="DESC"){ ?>sort_arrow_down.gif<?php } else { ?>sort_arrow_up.gif<?php } ?>" alt="Sort" width="16" height="16" border="0" /><?php } ?>
              <a href="<?=basename($_SERVER['PHP_SELF']).cleanQueryString('sort_by=name&order='.((isset($_GET["sort_by"])&&$_GET["sort_by"]=="name")?(($_GET["order"]=="DESC")?"ASC":"DESC"):"ASC"))?>" title="Сортиране по наименование">Наименование</a></th>
              <th><?php if (isset($_GET["sort_by"])&&$_GET["sort_by"]=="location_name"){?><img src="img/<?php if (isset($_GET["order"])&&$_GET["order"]=="DESC"){ ?>sort_arrow_down.gif<?php } else { ?>sort_arrow_up.gif<?php } ?>" alt="Sort" width="16" height="16" border="0" /><?php } ?>
              <a href="<?=basename($_SERVER['PHP_SELF']).cleanQueryString('sort_by=location_name&order='.((isset($_GET["sort_by"])&&$_GET["sort_by"]=="location_name")?(($_GET["order"]=="DESC")?"ASC":"DESC"):"ASC"))?>" title="Сортиране по населено място">Населено място</a></th>
              <th><?php if (isset($_GET["sort_by"])&&$_GET["sort_by"]=="address"){?><img src="img/<?php if (isset($_GET["order"])&&$_GET["order"]=="DESC"){ ?>sort_arrow_down.gif<?php } else { ?>sort_arrow_up.gif<?php } ?>" alt="Sort" width="16" height="16" border="0" /><?php } ?>
              <a href="<?=basename($_SERVER['PHP_SELF']).cleanQueryString('sort_by=address&order='.((isset($_GET["sort_by"])&&$_GET["sort_by"]=="address")?(($_GET["order"]=="DESC")?"ASC":"DESC"):"ASC"))?>" title="Сортиране по адрес">Адрес</a></th>
              <!--<th><?php if (isset($_GET["sort_by"])&&$_GET["sort_by"]=="num_workers"){?><img src="img/<?php if (isset($_GET["order"])&&$_GET["order"]=="DESC"){ ?>sort_arrow_down.gif<?php } else { ?>sort_arrow_up.gif<?php } ?>" alt="Sort" width="16" height="16" border="0" /><?php } ?>
              <a href="<?=basename($_SERVER['PHP_SELF']).cleanQueryString('sort_by=num_workers&order='.((isset($_GET["sort_by"])&&$_GET["sort_by"]=="num_workers")?(($_GET["order"]=="DESC")?"ASC":"DESC"):"ASC"))?>" title="Сортиране по брой работещи">Бр. работещи</a></th> -->
              <th>Бр. работещи</th>
              <th>Списък <br />работещи</th>
              <th>Анализ здр. <br />състояние</th>
              <th>Отвори /  <br />Редактирай</th>
              <?php if($_SESSION['sess_user_level'] == 1) { /* admin rights only */ ?>
              <th>Изтрий</th>
              <?php } ?>
              <th>Папка</th>
            </tr>
            <?php
            if(is_array($firms) && count($firms) > 0) {
            	$i = 0;
            	$IDs = array();
            	foreach ($firms as $row) {
            		$IDs[] = $row['firm_id'];
            	}
            	$sql = "SELECT COUNT(*) AS cnt, firm_id
            			FROM workers 
            			WHERE firm_id IN (".implode(',', $IDs).") 
            			AND date_retired = '' 
            			AND is_active = '1'
            			GROUP BY firm_id";
            	$rows = $dbInst->query($sql);
            	$cntWorkers = array();
            	foreach ($rows as $row) {
            		$cntWorkers[$row['firm_id']] = $row['cnt'];
            	}
            	foreach ($firms as $row) {
            		//$field = $dbInst->fnSelectSingleRow("SELECT COUNT(*) AS cnt FROM workers w WHERE w.firm_id=$row[firm_id] AND w.date_retired='' AND w.is_active='1'");
            		//$num_workers = $field['cnt'];
            		$num_workers = (isset($cntWorkers[$row['firm_id']])) ? $cntWorkers[$row['firm_id']] : 0;
            		$firm_folder = $row['firm_folder'];
            		if(empty($firm_folder) && 1 == CREATE_FIRM_FOLDERS) {
            			$firm_folder = $dbInst->getGenericFirmName($row['name']);
            			// Make sure that firm folder is unique
            			$j = 1;
            			while (1) {
            				if(file_exists($abs_path.$firm_folder)) {
            					$firm_folder .= $j;
            				} else break;
            				$j++;
            			}
            			if(@mkdir($abs_path.$firm_folder)) {
            				$dbInst->query("UPDATE firms SET firm_folder = '".$dbInst->checkStr($firm_folder)."' WHERE firm_id = $row[firm_id]");
            			}
            		}
            ?>
            <tr>
              <td align="left"><a href="firm_info.php?firm_id=<?=$row['firm_id']?>" title="Отвори/Редактирай <?=HTMLFormat($row['name'])?>">
              <?php if('0'==$row['is_active']){ echo '<img src="img/caution.gif" alt="inactive" border="0" width="11" height="11" /> '; }; ?>
              <?=HTMLFormat($row['name'])?></a></td>
              <td align="left"><?=$row['location_name']?></td>
              <td align="left"><?=$row['address']?></td>
              <td align="center"><strong><?=$num_workers?></strong></td>
              <td align="center"><?=(($num_workers)?'<a href="w_rtf_workers_list.php?firm_id='.$row['firm_id'].'&amp;'.SESS_NAME.'='.session_id().'" title="Списък на работещите в '.HTMLFormat($row['name']).'"><img src="img/medical3.gif" width="16" height="16" border="0" alt="Списък" /></a>':'--')?></td>
              <td align="center"><?=(($num_workers)?'<a id="lnkanalysis_'.$row['firm_id'].'" href="popup_analysis_dates.php?firm_id='.$row['firm_id'].'&amp;'.SESS_NAME.'='.session_id().'" title="Анализ здр. състояние на \''.HTMLFormat($row['name']).'\'"><img src="img/books_016.gif" width="16" height="16" border="0" alt="Анализ" /></a>':'--')?></td>
              <td align="center"><a href="firm_info.php?firm_id=<?=$row['firm_id']?>" title="Отвори/Редактирай <?=HTMLFormat($row['name'])?>"><img src="img/edititem.gif" alt="Отвори/Редактирай <?=HTMLFormat($row['name'])?>" width="16" height="16" border="0" /></a></td>
              <?php if($_SESSION['sess_user_level'] == 1) { /* admin rights only */ ?>
              <td align="center"><a href="javascript:void(null);" onclick="var answ=confirm('Наистина ли искате да изтриете всички данни за фирмата?');if(answ){xajax_deleteFirm(<?=$row['firm_id']?>);}return false;" title="Изтрий <?=HTMLFormat($row['name'])?>"><img src="img/delete.gif" alt="Изтрий <?=HTMLFormat($row['name'])?>" width="15" height="15" border="0" /></a></td>
              <?php } ?>
              <td align="center"><?php
              if(isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/(MSIE)\b(.*?);/i', $_SERVER['HTTP_USER_AGENT'], $matches) && 1 == CREATE_FIRM_FOLDERS) {
              	$version = floatval(trim($matches[2]));
              	echo '<a href="'.$net_path.'\\'.$firm_folder.'"'.(((7 > $version))?' target="_blank"':'').'><img src="img/folder.gif" width="16" height="16" border="0" alt="'.HTMLFormat($firm_folder).'" /></a>';
              } else {
              	echo '--';
              }
              ?></td>
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
            <tr class="notover">
              <td align="left" colspan="9"><strong>Покажи </strong><input type="text" id="perPage" name="perPage" value="<?=$perPage?>" size="5" maxlength="10" onKeyPress="return numbersonly(this, event);" /> <strong>фирми на страница</strong></td>
            </tr>
          </tbody>
        </table>
      </form>
      <!-- /frmAgents -->
      <div class="pageline1">Резултати <?=$from?> - <?=$to?> от <?=$totalItems?><?php if($paged_data['links']) { ?> / Иди на страница <?=$paged_data['links']?><?php } ?></div>
      <div class="hr"></div>
      <?php /*$exp_days = (isset($_COOKIE['stm_exp_days'])) ? abs(intval($_COOKIE['stm_exp_days'])) : 7;*/
      $rows = $dbInst->fnSelectSingleRow("SELECT contract_exp_days FROM stm_info LIMIT 1");
      $exp_days = intval($rows['contract_exp_days']);
      ?>
      <div align="left">&nbsp;<a href="popup_exp_contracts.php?exp_days=<?=$exp_days?>&amp;<?=SESS_NAME.'='.session_id()?>&amp;KeepThis=true&amp;TB_iframe=true&amp;height=480&amp;width=790&amp;modal=true" title="Списък на договорите, изтичащи след <?=$exp_days?> дни" class="thickbox">Списък на изтичащите след <span id="exp_days"><?=$exp_days?></span> дни договори</a></div>
    </div>



<?php include("footer.php"); ?>