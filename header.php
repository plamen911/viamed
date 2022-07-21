<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?=SITE_NAME?></title>
<link href="styles.css" rel="stylesheet" type="text/css" media="screen" />
<style type="text/css">
<!--
#navbar {
	margin-top:-5px;
}
-->
</style>
<!--[if IE]>
<style type="text/css">
#navbar {
	margin-top:-4px;
	padding-top:4px;
}
</style>
<![endif]-->
<script type="text/javascript" src="js/RegExpValidate.js"></script>
<script type="text/javascript" src="scw.js"></script>
<!-- http://jquery.com/demo/thickbox/ -->
<script type="text/javascript" src="js/jquery-latest.pack.js"></script>

<!-- http://colorpowered.com/colorbox/core/example1/index.html -->
<link type="text/css" media="screen" rel="stylesheet" href="js/colorbox/colorbox.css" />
<script type="text/javascript" src="js/colorbox/jquery.colorbox.js"></script>

<script type="text/javascript">
//<![CDATA[
var user_id = <?=((isset($_SESSION['sess_user_id'])) ? $_SESSION['sess_user_id'] : 0)?>;
var ip_addr = '<?=((!empty($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : '')?>';
//]]>
</script>
<script type="text/javascript" src="js/thickbox/thickbox.js"></script>
<link rel="stylesheet" href="js/thickbox/thickbox.css" type="text/css" media="screen" />
<?php if(isset($echoJS)) echo $echoJS; ?>
<script type="text/javascript">
//<![CDATA[
function openMkb10(el) {
	$(el).colorbox({width:"90%", height:"100%", iframe:true, overlayClose:false, title:'Номенклатура МКБ 10', transition:"none", fastIframe:false, href:'popup_mkb_nomenclature.php', fixed:true});
	return false;
}
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
	$('#preLoader').css('display', 'none');
	if($.browser.msie) {
		$("input[type='text']:disabled,textarea:disabled,select:disabled").css("background-color", "#EEEEEE");
		$(":checkbox").css("border","none");
	}
	<?php
	if('login.php' == basename($_SERVER['PHP_SELF']) && (file_exists('upd.xml') || file_exists('upd.php'))) {
		echo 'document.getElementById("errmsg").innerHTML="Актуализация на системата. Моля, изчакайте...";';
		echo 'document.getElementById("errmsg").style.visibility="visible";';
		echo 'xajax_updData();';
		echo 'DisableEnableForm(true);';
	}
	if(isset($_SESSION['sess_exp_days'])) {
		// Show popup
		echo "tb_show('Списък на договорите, изтичащи след $_SESSION[sess_exp_days] дни','popup_exp_contracts.php?exp_days=$_SESSION[sess_exp_days]&amp;".SESS_NAME.'='.session_id()."&amp;KeepThis=true&amp;TB_iframe=true&amp;height=480&amp;width=790&amp;modal=true',0);";
		unset($_SESSION['sess_exp_days']);
	}
	?>
});
//]]>
</script>
<style type="text/css" media="screen">
<!--
#preLoader {
    height: 100%;
    position: absolute;
    text-align: center;
    vertical-align: middle;
    width: 100%;
    z-index: 50000;
}
.preLoaderText {
	background-color: #191919;
	filter:alpha(opacity=75);
	-moz-opacity: 0.75;
	opacity: 0.75;
	width: 300px;
	height: 150px;
	padding: 90px 70px 10px 70px;
	border: 1px solid #444444;
	font-size:1.1em;
	color:#FFFFFF;
}
-->
</style>
<script type="text/javascript" src="js/autocompleter/jquery.bgiframe.min.js"></script>
<script type="text/javascript">
//<![CDATA[
var imgLoader = new Image();// preload image
imgLoader.src = 'img/loader.gif';
$(function() {
	$('#preLoader').bgiframe();
});
//]]>
</script>
</head>
<body>
<div id="preLoader">
  <table width="100%" border="0" cellpadding="0">
    <tr>
      <td align="center"><div class="preLoaderText">
          <p>Моля, изчакайте да се зареди страницата...</p>
          <p>&nbsp;</p>
          <p><img src="img/loader.gif" alt="Моля, изчакайте" /></p>
        </div></td>
    </tr>
  </table>
</div>
<?php if(isset($_SESSION['sess_user_level'])) { /* User is logged-in */ ?>
<div align="right" style="padding-right:8px;" id="loggedinfo">Потребител: <?=(('demo' == $_SESSION['sess_user_name'])?$_SESSION['sess_fname']:HTMLFormat($_SESSION['sess_fname'].' '.$_SESSION['sess_lname']))?>&nbsp;</div>
<div id="contentWrapper">
  <div id="navbar">
    <ul>
      <li class="<?=((in_array(basename($_SERVER['PHP_SELF']), array('firms.php', 'firm_info.php')))?'active':'adminmenu')?>"><a href="firms.php" title="Списък на отделните фирми">Фирми</a></li>
      <li class="<?=((basename($_SERVER['PHP_SELF'])=='official_data.php')?'active':'adminmenu')?>"><a href="official_data.php">Служебни данни</a></li>
      <li class="adminmenu"><a href="javascript:void(0);" onclick="openMkb10(this);">МКБ 10</a></li>
      <?php if('1' == SHOW_ACCOUNTING_APP) { ?><li class="adminmenu"><a href="acc_payments.php">Счетоводна програма</a></li><?php } ?>
      <li class="adminmenu"><a href="login.php?logout=1">Изход</a></li>
    </ul>
  </div>
  <?php } else { /* User is NOT logged-in */ ?>
<script type="text/javascript">
//<![CDATA[
function triggerLogin(e) { //e is event object passed from function invocation
	var code;
	if (!e) var e = window.event;
	if (e.keyCode) code = e.keyCode;
	else if (e.which) code = e.which;
	if(code == 13){ //if generated character code is equal to ascii 13 (if enter key)
		xajax_login(xajax.getFormValues('frmLogin'));
		//DisableEnableForm(true); //submit the form
		return false;
	}
	else{
		return true;
	}
}
$(function() {
	$("#user_name").focus();
});
//]]>
</script>
<div align="right" style="padding-right:8px;visibility:hidden;height:40px;" id="loggedinfo">&nbsp;</div>
<div id="contentWrapper">
  <?php } ?>
  <div id="contentinner" align="center"> <br clear="all" />