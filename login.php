<?php
set_time_limit(600);// set script time limit to 10 min
//ini_set("memory_limit","100M");
require ('includes.php');

$url = '';
if (isset($_GET['logout']) && $_GET['logout'] == '1') {
	my_session_destroy();
	header('Location:login.php');
	exit();
} elseif (isset($_SESSION['sess_user_id']) && $_SESSION['sess_user_id'] != '' && !$dbInst->isAjaxCall()) {
	if (isset($_GET['accessdenied']) && $_GET['accessdenied'] != '') $url .= urldecode($_GET['accessdenied']);
	else $url .= 'firms.php';
	header('Location:' . $url);
	exit();
}

// Xajax begin
require ('xajax/xajax_core/xajax.inc.php');
function login($aFormValues)
{
	$objResponse = new xajaxResponse();
	$bError = false;
	$objResponse->assign("btnLogin", "value", "Вход");
	$objResponse->assign("btnLogin", "disabled", false);
	$objResponse->call("DisableEnableForm", false);

	global $dbInst;
	$user_name = trim($aFormValues['user_name']);
	if ($user_name == '')
	$bError = true;
	$user_pass = trim($aFormValues['user_pass']);
	if ($user_pass == '')
	$bError = true;
	
	if(1 == USE_CAPCHA) {
		if(!$bError) {
			require('securimage/securimage.php');
			$securimage = new Securimage();
			if($securimage->check($aFormValues['captcha_code']) == false) {
				$objResponse->assign("errmsg", "innerHTML", "Невалиден код за сигурност. Опитайте отново.");
				$objResponse->assign("errmsg", "style.visibility", "visible");
				$objResponse->script('$("#reloadCapcha").trigger("click")');
				return $objResponse;
			}
		}
	}

	if (!$bError) {
		if ($dbInst->isLoginAllowed($user_name, $user_pass)) {
			if (isset($aFormValues['RememberMe'])) { // Remember this user for 100 days
				setcookie("stm_user", $user_name, time() + 60 * 60 * 24 * 100, "/");
				setcookie("stm_pass", $user_pass, time() + 60 * 60 * 24 * 100, "/");
			} else { // Don't remember user
				setcookie("stm_user", "", time() - 3600, "/");
				setcookie("stm_pass", "", time() - 3600, "/");
			}
			// Warn for contracts that will expire in 7 days
			//$exp_days = (isset($_COOKIE['stm_exp_days'])) ? abs(intval($_COOKIE['stm_exp_days'])) : 7;
			$rows = $dbInst->fnSelectSingleRow("SELECT contract_exp_days FROM stm_info LIMIT 1");
			$exp_days = intval($rows['contract_exp_days']);
			$query = "	SELECT f.*, f.name AS firm_name, l.*, c.*, p.*,
						strftime('%d.%m.%Y', f.contract_begin, 'localtime') AS contract_begin2,
						strftime('%d.%m.%Y', f.contract_end, 'localtime') AS contract_end2
						FROM firms f
						LEFT JOIN locations l ON (l.location_id = f.location_id)
						LEFT JOIN communities c ON (c.community_id = f.community_id)
						LEFT JOIN provinces p ON (p.province_id = f.province_id)
						WHERE f.is_active = '1'
						AND contract_end != ''
						AND strftime('%s', f.contract_end) >= strftime('%s','now')
						AND strftime('%s', f.contract_end) <= strftime('%s','now', '+$exp_days days')
						ORDER BY f.contract_end DESC,
						LOWER(f.name), l.location_name, c.community_name, p.province_name, f.firm_id";
			$rows = $dbInst->fnSelectRows($query);
			if(count($rows)) {
				$_SESSION['sess_exp_days'] = $exp_days;//Show popup
			}

			$dbInst->write2Log();//Write to log table logged-in users
			$dbInst->createDbBackup();//Create daily DB backup

			if (isset($_GET['accessdenied']) && $_GET['accessdenied'] != '') {
				$objResponse->script("top.location.href='" . urldecode($_GET['accessdenied']) . "'");
			} else {
				$objResponse->script("top.location.href='firms.php?".SESS_NAME.'='.session_id()."'");
			}
			$objResponse->assign("errmsg", "innerHTML", "Препращане към Вашия акаунт...");
			$objResponse->assign("btnLogin", "value", "Вход");
			$objResponse->assign("btnLogin", "disabled", true);
			return $objResponse;
		} else {
			$objResponse->assign("errmsg", "innerHTML", "Невалидно име или парола. Опитайте отново.");
		}
	} else {
		$objResponse->assign("errmsg", "innerHTML", "Невалидно име или парола. Опитайте отново.");
	}
	$objResponse->assign("errmsg", "style.visibility", "visible");
	if(1 == USE_CAPCHA) { $objResponse->script('$("#reloadCapcha").trigger("click")'); }
	return $objResponse;
}
function updData()
{
	$objResponse = new xajaxResponse();

	global $dbInst;
	$freeze = 1;
	if (file_exists('upd.xml')) {
		$db = $dbInst->getDBHandle();
		$xml = file_get_contents('upd.xml');
		if (preg_match_all('/\<query\>(.*?)\<\/query\>/si', $xml, $queries)) {
			try {
				$db->beginTransaction();
				foreach ($queries[1] as $query) {
					$count = $db->exec(trim($query)); //returns affected rows
				}
				$db->commit();
			}
			catch (exception $e) {
				$db->rollBack();
			}
		}
		@unlink('upd.xml');
		$freeze = 0;
	}
	if (file_exists('upd.php')) {
		$freeze = 1;
		$js = '$.post("upd.php", function(data){';
		$js .= 'xajax_deleteUpd();';
		$js .= '});';
		$objResponse->script($js);
	}
	if (!$freeze) {
		$objResponse->assign("errmsg", "style.visibility", "hidden");
		$objResponse->assign("errmsg", "innerHTML", "&nbsp;");
		$objResponse->call("DisableEnableForm", false);
	}
	return $objResponse;
}
function deleteUpd()
{
	$objResponse = new xajaxResponse();
	if (file_exists('upd.php'))
	@unlink('upd.php');

	$objResponse->assign("errmsg", "style.visibility", "hidden");
	$objResponse->assign("errmsg", "innerHTML", "&nbsp;");
	$objResponse->call("DisableEnableForm", false);
	return $objResponse;
}
$xajax = new xajax();
$xajax->registerFunction("login");
$xajax->registerFunction("updData");
$xajax->registerFunction("deleteUpd");
//$xajax->setFlag("debug",true);
$echoJS = $xajax->getJavascript('xajax/');
$xajax->processRequest();
// Xajax end

$user_name = (isset($_COOKIE['stm_user'])) ? HTMLFormat($_COOKIE['stm_user']) : '';
$user_pass = (isset($_COOKIE['stm_pass'])) ? HTMLFormat($_COOKIE['stm_pass']) : '';

include ("header.php");
?>
    <form id="frmLogin" name="frmLogin" action="javascript:void(null)">
      <table cellpadding="0" cellspacing="0" class="formBg">
        <tr>
          <td colspan="2" class="leftSplit rightSplit topSplit"><div id="lastModified" class="lastModified"><?php $fields = $dbInst->getLastLoginInfo(); ?>Последен вход в системата: <strong><?=((isset($fields[0]['date_last_login2']))?$fields[0]['date_last_login2']:'N/A')?></strong><?=((isset($fields[0]['fname'])&&$fields[0]['fname']!='')?' от <strong>'.$fields[0]['fname'].' '.$fields[0]['lname'].'</strong>':'')?></div></td>
        </tr>
        <tr>
          <td colspan="2" class="leftSplit rightSplit"><div id="errmsg" class="notes" style="visibility:hidden">&nbsp;</div></td>
        </tr>
        <tr>
          <td align="left" class="leftSplit">Потребителско име: </td>
          <td align="left" class="rightSplit"><input id="user_name" name="user_name" size="30" value="<?=$user_name?>" maxlength="70" type="text" onkeypress="triggerLogin(event);" /></td>
        </tr>
        <tr>
          <td align="left" class="leftSplit">Парола: </td>
          <td align="left" class="rightSplit"><input id="user_pass" name="user_pass" size="30" value="<?=$user_pass?>" maxlength="20" type="password" onkeypress="triggerLogin(event);" /></td>
        </tr>
        <?php if(1 == USE_CAPCHA) { ?>
        <tr>
          <td align="left" class="leftSplit">Код за сигурност (CAPTCHA): <div style="font-style: italic;font-size: 11px;"> (латински букви и/или цифри без интервали)</div></td>
          <td align="left" class="rightSplit"><img id="captcha" src="securimage/securimage_show.php?<?=session_name().'='.session_id()?>" alt="CAPTCHA Image" /> <a tabindex="-1" id="reloadCapcha" style="border-style: none" href="#" onclick="document.getElementById('captcha').src = 'securimage/securimage_show.php?' + Math.random(); return false"><img src="securimage/images/refresh.gif" alt="Reload Image" border="0" onclick="this.blur()" align="bottom" /></a></td>
        </tr>
        <tr>
          <td align="left" class="leftSplit">Въведете кода от изображението:</td>
          <td align="left" class="rightSplit"><input type="text" id="captcha_code" name="captcha_code" size="10" maxlength="4" /></td>
        </tr>
        <?php } ?>
        <tr>
          <td class="leftSplit">&nbsp;</td>
          <td align="left" class="rightSplit"><input id="btnLogin" name="btnLogin" value="Вход" onclick="this.disabled=true;this.value='изчакайте...';xajax_login(xajax.getFormValues('frmLogin'));DisableEnableForm(true);return false;" type="button" class="nicerButtons" />
          <div class="hr"></div>
          <input type="checkbox" name="RememberMe" value="1"<?php if(isset($_COOKIE['stm_user']) && isset($_COOKIE['stm_pass'])) echo ' checked="checked"'; ?> />
            Запомни ме
            <div style="font-style: italic;font-size: 11px;">(Махнете отметката, ако компютърът е споделен)</div>
          </td>
        </tr>
        <!--<tr>
          <td colspan="2" class="leftSplit rightSplit">&nbsp;</td>
        </tr>
        <tr>
          <th colspan="2" class="leftSplit rightSplit"><u>Демонстрационна версия</u></th>
        </tr>
        <tr>
          <td align="left" class="leftSplit rightSplit">Потребителско име: </td>
          <td align="left" class="rightSplit"><strong>demo</strong></td>
        </tr>
        <tr>
          <td align="left" class="leftSplit rightSplit">Парола: </td>
          <td align="left" class="rightSplit"><strong>demo</strong></td>
        </tr>-->
      </table>
    </form>

<?php include("footer.php"); ?>
