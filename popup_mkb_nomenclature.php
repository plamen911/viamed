<?php
require ("sqlitedb.php");
require ("convertroman.php");

$dbInst = new SqliteDB();

if(isset($_POST['ajax_action'])) {
	$ret['out'] = '';
	switch ($_POST['ajax_action']) {
		case 'get_groups':
			$class_id = (isset($_POST['item_id'])) ? intval($_POST['item_id']) : 0;
			$flds = $dbInst->query("SELECT * FROM `mkb_groups` WHERE `class_id` = $class_id");
			if(!empty($flds)) {
				$out = '<ul>';
				$i = 0;
				foreach ($flds as $fld) {
					$fld['group_name'] = preg_replace('/(.*?)\s+\(([A-Z0-9\-]+)\)$/', '$2 - $1', trim($fld['group_name']));
					
					$out .= '<li class="expandable'.((count($flds) == $i + 1) ? ' lastExpandable' : '').'">';
					$out .= '<div id="group_id_'.$fld['group_id'].'" class="hitarea hasChildren-hitarea expandable-hitarea'.((count($flds) == $i + 1) ? ' lastExpandable-hitarea' : '').'"></div>';
					$out .= '<span>'.$fld['group_name'].'</span>';
					$out .= '<div id="subGroupWrapper_'.$fld['group_id'].'"></div>';
					$out .= '</li>';
					$i++;
				}
				$out .= '</ul>';
				$ret['out'] = $out;
			}
			break;

		case 'get_subgroups':
			$group_id = (isset($_POST['item_id'])) ? intval($_POST['item_id']) : 0;
			$flds = $dbInst->query("SELECT * FROM `mkb` WHERE `group_id` = $group_id");
			if(!empty($flds)) {
				foreach ($flds as $i => $fld) {
					foreach ($flds as $i => $fld) {
						if(!isSubGroup($fld['mkb_id'])) {
							unset($flds[$i]);
						}
					}
				}
			}
			if(!empty($flds)) {
				$out = '<ul>';
				$i = 0;
				foreach ($flds as $fld) {
					if(hasChildren($dbInst, $fld['mkb_id'])) {
						$out .= '<li class="expandable'.((count($flds) == $i + 1) ? ' lastExpandable' : '').'">';
						$out .= '<div id="mkb_id_'.$fld['mkb_id'].'" class="hitarea hasChildren-hitarea expandable-hitarea'.((count($flds) == $i + 1) ? ' lastExpandable-hitarea' : '').'"></div>';					
						$out .= '<span><a href="#" title="'.$fld['mkb_id'].'" rel="'.$fld['mkb_desc'].'" class="mkb_code">'.$fld['mkb_id'].' - '.$fld['mkb_desc'].'</a></span>';
						$out .= '<div id="mkbWrapper_'.str_replace('*', '', $fld['mkb_id']).'"></div>';
						$out .= '</li>';
					} else {
						$out .= '<li'.((count($flds) == $i + 1) ? ' class="last"' : '').'><span><a href="#" title="'.$fld['mkb_id'].'" rel="'.$fld['mkb_desc'].'" class="mkb_code">'.$fld['mkb_id'].' - '.$fld['mkb_desc'].'</a></span></li>';
					}
					$i++;
				}
				$out .= '</ul>';
				$ret['out'] = $out;
			}
			break;
			
		case 'get_mkb':
			$mkb_id = (isset($_POST['item_id'])) ? $dbInst->checkStr($_POST['item_id']) : '';
			$flds = $dbInst->query("SELECT * FROM `mkb` WHERE `mkb_id` LIKE '".str_replace('*', '', $mkb_id)."%' AND `mkb_id` NOT LIKE '$mkb_id'");
			if(!empty($flds)) {
				foreach ($flds as $i => $fld) {
					foreach ($flds as $i => $fld) {
						if(isSubGroup($fld['mkb_id']) || $fld['mkb_id'] == $mkb_id) {
							unset($flds[$i]);
						}
					}
				}
			}
			if(!empty($flds)) {
				$out = '<ul>';
				$i = 0;
				foreach ($flds as $fld) {
					$out .= '<li'.((count($flds) == $i + 1) ? ' class="last"' : '').'><span><a href="#" title="'.$fld['mkb_id'].'" rel="'.$fld['mkb_desc'].'" class="mkb_code">'.$fld['mkb_id'].' - '.$fld['mkb_desc'].'</a></span></li>';
					$i++;
				}
				$out .= '</ul>';
				$ret['out'] = $out;
			}
			break;

		default:
			break;
	}
	die(json_encode($ret));
}

function isSubGroup($mkb_id = '') {
	return preg_match('/^.\d+\**$/', $mkb_id);
}
function hasChildren($dbInst = null, $mkb_id = '') {
	return $dbInst->fnCountRow('mkb', "`mkb_id` LIKE '".str_replace('*', '', $mkb_id)."%' AND `mkb_id` NOT LIKE '$mkb_id'");
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Номенклатура МКБ 10</title>
<link type="text/css" rel="stylesheet" href="js/jquery.treeview/jquery.treeview.css" />
<style type="text/css">
html, body {
	height:100%;
	margin: 0;
	padding: 0;
}
html>body {
	font-size: 16px;
	font-size: 68.75%;
} /* Reset Base Font Size */
body {
	font-family: Verdana, helvetica, arial, sans-serif;
	font-size: 68.75%;
	background: #fff;
	color: #333;
}
h1, h2 {
	font-family: 'trebuchet ms', verdana, arial;
	padding: 10px;
	margin: 0
}
h1 {
	font-size: large
}
#main {
	padding: 1em;
}
a {
	text-decoration: none;
}
a img {
	border: none;
}
#quickNote {
	/*margin-top:300px;*/
	float:right;
	border:1px solid blask;
	background-color:#FFFFCC;
	padding:4px 10px;
	color:#CC6600;
	display:none;
}
</style>
<script type="text/javascript" src="js/jquery-latest.pack.js"></script>
<script type="text/javascript">
//<![CDATA[
var pathToImage = "img/ajax-loader.gif";
var imgLoader = new Image();// preload image
imgLoader.src = pathToImage;
var loaderImg = '<img src="' + imgLoader.src + '" alt="loading..." \/> зареждане...';
$(function(){
	// Classe item click
	$('div[id^="class_id_"]').live('click', function(e){
		e.preventDefault();
		var itemId = $(this).attr('id');
		var class_id = itemId.split('_')[2];
		var wrapperElem = $('#groupWrapper_' + class_id);

		processSelection(this, class_id, wrapperElem, 'get_groups');
	});
	// Group item click
	$('div[id^="group_id_"]').live('click', function(e){
		e.preventDefault();
		var itemId = $(this).attr('id');
		var group_id = itemId.split('_')[2];
		var wrapperElem = $('#subGroupWrapper_' + group_id);

		processSelection(this, group_id, wrapperElem, 'get_subgroups');
	});
	// Subgroup item click
	$('div[id^="mkb_id_"]').live('click', function(e){
		e.preventDefault();
		var itemId = $(this).attr('id');
		var mkb_id = itemId.split('_')[2];
		var wrapperElem = $('#mkbWrapper_' + mkb_id.replace('*', ''));
		
		processSelection(this, mkb_id, wrapperElem, 'get_mkb');
	});
	
	$('a.mkb_code').live('click', function(e){
		e.preventDefault();
		var mkb_id = $(this).attr('title');
		var mkb_desc = $(this).attr('rel');
		var offset = $(this).offset();
		
		if(typeof parent.populateFields != 'undefined') {
			parent.populateFields(mkb_id, mkb_desc);
			
			$('#quickNote').html('Кодът на заболяването ' + mkb_id + ' бе успешно въведен.').css({'display': 'block', 'margin-top': offset.top + 'px'});
			window.setTimeout(function(){
				$('#quickNote').css('display', 'none');
			}, 3000);
		}
	});
});
function processSelection(el, item_id, wrapperElem, ajax_action) {
	if($(el).hasClass('expandable-hitarea')) {
		$(el).removeClass('expandable-hitarea').addClass('collapsable-hitarea');
		wrapperElem.css('display', 'block');
		if(wrapperElem.parent('li').hasClass('lastExpandable')) {
			wrapperElem.parent('li').removeClass('lastExpandable').addClass('lastCollapsable');
		}
		if($.trim(wrapperElem.html()) == '') {
			wrapperElem.html(loaderImg);
			$.post('<?=$_SERVER['PHP_SELF']?>', { 'ajax_action': ajax_action, 'item_id': item_id }, function(data){
				wrapperElem.html(data.out);
			}, 'json');
		}
	} else {
		$(el).removeClass('collapsable-hitarea').addClass('expandable-hitarea');
		wrapperElem.css('display', 'none');
		if(wrapperElem.parent('li').hasClass('lastCollapsable')) {
			wrapperElem.parent('li').removeClass('lastCollapsable').addClass('lastExpandable');
		}
	}
}
//]]>
</script>
</head>
<body>
<div id="main">
  <div id="quickNote">зареждане...</div>
  <!--<h4>Номенклатура МКБ 10</h4>-->
  <?php $classes = $dbInst->query("SELECT * FROM `mkb_classes` WHERE `class_id` < 1000 ORDER BY `class_id`"); ?>
  <?php if(!empty($classes)) { ?>
  <ul id="mixed" class="treeview">
    <?php
    foreach ($classes as $i => $class) {
    	$converter = new ConvertRoman($i + 1);
    	$num = $converter->result();
    	$class['class_name'] = preg_replace('/(.*?)\s+\((.*?)\)$/', '$2 - Клас '.$num.', $1', trim($class['class_name']));
    	?>
    <li class="expandable<?=((count($classes) == $i + 1) ? ' lastExpandable' : '')?>">
      <div id="class_id_<?=$class['class_id']?>" class="hitarea hasChildren-hitarea expandable-hitarea<?=((count($classes) == $i + 1) ? ' lastExpandable-hitarea' : '')?>"></div>      
      <span><?=$class['class_name']?></span>
      <div id="groupWrapper_<?=$class['class_id']?>"></div>
    </li>
    <?php } ?>
  </ul>
  <?php } ?>
  <div style="height:100px;"></div>
</div>
</body>
</html>
