<?php
require('includes.php');

// Xajax begin
require ('xajax/xajax_core/xajax.inc.php');
function processFactors($aFormValues) {
	$objResponse = new xajaxResponse();

	$objResponse->assign("btnSubmit","disabled",false);
	$objResponse->assign("btnSubmit","value","Съхрани");

	global $dbInst;
	$dbInst->setFactors($aFormValues);
	$objResponse->assign("sub1","innerHTML",echoFactors());

	$objResponse->script('$("#listtable input:text").css("width","99%")');
	$objResponse->call("stripTable","listtable");
	$objResponse->call("DisableEnableForm",false);
	return $objResponse;
}
function processDoctorPos($aFormValues) {
	$objResponse = new xajaxResponse();

	$objResponse->assign("btnSubmit","disabled",false);
	$objResponse->assign("btnSubmit","value","Съхрани");

	global $dbInst;
	$dbInst->setDoctorPos($aFormValues);
	$objResponse->assign("sub1","innerHTML",echoDoctorPos());

	$objResponse->script('$("#listtable input:text").css("width","99%")');
	$objResponse->call("stripTable","listtable");
	$objResponse->call("DisableEnableForm",false);
	return $objResponse;
}
function deleteFactor($factor_id) {
	$objResponse = new xajaxResponse();
	global $dbInst;
	if($dbInst->removeFactor($factor_id)) {
		$objResponse->call("removeLine","line_$factor_id");
	}
	return $objResponse;
}
function deleteDoctorPos($doctor_pos_id) {
	$objResponse = new xajaxResponse();
	global $dbInst;
	if($dbInst->removeDoctorPos($doctor_pos_id)) {
		$objResponse->call("removeLine","line_$doctor_pos_id");
	}
	return $objResponse;
}
function processLabs($aFormValues) {
	$objResponse = new xajaxResponse();

	$objResponse->assign("btnSubmit","disabled",false);
	$objResponse->assign("btnSubmit","value","Съхрани");

	global $dbInst;
	$dbInst->setLabs($aFormValues);
	$objResponse->assign("sub1","innerHTML",echoLabs());

	$objResponse->script('$("#listtable input:text").css("width","99%")');
	$objResponse->call("stripTable","listtable");
	$objResponse->call("DisableEnableForm",false);
	return $objResponse;
}
function processAccounts($aFormValues) {
	$objResponse = new xajaxResponse();

	$objResponse->assign("btnSubmit","disabled",false);
	$objResponse->assign("btnSubmit","value","Съхрани");

	foreach ($aFormValues as $key=>$val) {
		if(preg_match('/user_name_(\d+)$/', $key, $matches)) {
			$user_id = $matches[1];
			if($user_id) {
				if('' == trim($val)) {
					$objResponse->call("DisableEnableForm",false);
					$objResponse->alert('Потребителското име не може да бъде празно!');
					return $objResponse;
				}
			}
		}
	}

	global $dbInst;
	$dbInst->setAccounts($aFormValues);
	$objResponse->assign("sub1","innerHTML",echoAccounts());

	$objResponse->script('$("#listtable input:text").css("width","99%")');
	$objResponse->call("stripTable","listtable");
	$objResponse->call("DisableEnableForm",false);
	return $objResponse;
}
function deleteLab($indicator_id) {
	$objResponse = new xajaxResponse();
	global $dbInst;
	if($dbInst->removeLab($indicator_id)) {
		$objResponse->call("removeLine","line_$indicator_id");
	}
	return $objResponse;
}
function deleteAccount($user_id) {
	$objResponse = new xajaxResponse();
	global $dbInst;
	if($dbInst->removeAccount($user_id)) {
		$objResponse->call("removeLine","line_$user_id");
	}
	return $objResponse;
}
function processDoctor($aFormValues) {
	$objResponse = new xajaxResponse();

	$objResponse->assign("btnDoctor","disabled",false);
	$objResponse->assign("btnDoctor","value","Съхрани");
	$objResponse->call("DisableEnableForm",false);

	if(trim($aFormValues['d_doctor_name']) == '') {
		$objResponse->alert("Моля, въведете имената на фамилния лекар.");
		return $objResponse;
	}

	global $dbInst;
	$doctor_id = $dbInst->processDoctor($aFormValues); // Insert/Update doctor
	$objResponse->assign("d_doctor_id","value",$doctor_id);
	return $objResponse;
}
function processStmInfo($aFormValues) {
	$objResponse = new xajaxResponse();

	$objResponse->assign("btnSubmit","disabled",false);
	$objResponse->assign("btnSubmit","value","Съхрани");
	$objResponse->call("DisableEnableForm",false);

	if(trim($aFormValues['stm_name']) == '') {
		$objResponse->alert("Моля, въведете наименование на СТМ.");
		return $objResponse;
	}
	if(trim($aFormValues['address']) == '') {
		$objResponse->alert("Моля, въведете адрес на СТМ.");
		return $objResponse;
	}
	if(trim($aFormValues['chief']) == '') {
		$objResponse->alert("Моля, въведете имената на ръководителя на СТМ.");
		return $objResponse;
	}
	if(trim($aFormValues['email']) != '' && !EMailIsCorrect($aFormValues['email'])) {
		$objResponse->alert($aFormValues['email'] . " е невалиден e-mail.");
		return $objResponse;
	}

	global $dbInst;
	$doctor_id = $dbInst->processStmInfo($aFormValues); // processStmInfo

	return $objResponse;
}
function deleteDoctor($doctor_id) {
	$objResponse = new xajaxResponse();

	global $dbInst;
	$dbInst->removeDoctor($doctor_id);
	$objResponse->script("self.parent.location.reload();");

	return $objResponse;
}
function changePwd($aFormValues) {
	$objResponse = new xajaxResponse();

	$objResponse->assign("btnSubmit","disabled",false);
	$objResponse->assign("btnSubmit","value","Актуализирай");
	$objResponse->call("DisableEnableForm",false);

	global $dbInst;

	$user_pass = trim($aFormValues['user_pass']);
	if($user_pass == '') {
		$objResponse->alert('Моля, въведената сегашната парола!');
		return $objResponse;
	}
	$query = sprintf("SELECT user_pass FROM users WHERE user_id = %d", $_SESSION['sess_user_id']);
	$pwd = $dbInst->fnSelectSingleRow($query);
	if($pwd['user_pass'] != $user_pass) {
		$objResponse->alert('Въведената сегашна парола е невалидна!');
		return $objResponse;
	}
	$new_user_pass = trim($aFormValues['new_user_pass']);
	if($new_user_pass == '') {
		$objResponse->alert('Моля, въведената новата парола!');
		return $objResponse;
	}
	$new_user_pass2 = trim($aFormValues['new_user_pass2']);
	if($new_user_pass2 == '') {
		$objResponse->alert('Моля, повторете паролата!');
		return $objResponse;
	}
	if($new_user_pass != $new_user_pass2) {
		$objResponse->alert('Паролите не са еднакви!');
		return $objResponse;
	}

	$db = $dbInst->getDBHandle();
	$query = "UPDATE users SET user_pass = '". $dbInst->checkStr($new_user_pass)."' WHERE user_id = '$_SESSION[sess_user_id]'";
	$count = $db->exec($query); //returns affected rows
	if($count)
	$objResponse->alert('Паролата бе успешно променена!');
	else
	$objResponse->alert('Възникна проблем при промяна на паролата!');

	return $objResponse;
}
function archiveDB() {
	$objResponse = new xajaxResponse();

	$objResponse->assign("btnArchive","disabled",false);
	$objResponse->assign("btnArchive","value","Архивирай базата данни");
	$objResponse->call("DisableEnableForm",false);

	if(!file_exists('db/stm.db')) {
		$objResponse->alert('Базата данни не може да бъде локализирана или не съществува!');
		return $objResponse;
	}
	$bkpDB = 'db/BKP'.time().'.db';
	if(!@copy('db/stm.db', $bkpDB)) {
		$objResponse->alert('Базата данни не може да бъде архивирана!');
		return $objResponse;
	}

	$objResponse->alert('Базата данни бе успешно архивирана.');
	$_SESSION['sess_bkpDB'] = $bkpDB;
	$objResponse->script("top.location.href='".basename($_SERVER['PHP_SELF'])."?tab=upd';");

	return $objResponse;
}
function recoverDB($key) {
	$objResponse = new xajaxResponse();

	$objResponse->assign("btnIntRecover$key","disabled",false);
	$objResponse->assign("btnIntRecover$key","value","Възстанови");
	$objResponse->call("DisableEnableForm",false);

	$bkpDB = 'db/BKP'.$key.'.db';
	if(!file_exists($bkpDB)) {
		$objResponse->alert('Избраната архивна база данни не може да бъде локализирана или не съществува!');
		return $objResponse;
	}

	// Backup original DB
	if(file_exists('db/stm.db')) {
		if(!@copy('db/stm.db', 'db/BKP'.time().'.db')) {
			$objResponse->alert('Възникна неочакван проблем в процеса на възстановяване!');
			return $objResponse;
		}
	}
	// Make archive DB active
	if(!@copy($bkpDB, 'db/stm.db')) {
		$objResponse->alert('Възникна неочакван проблем в процеса на възстановяване!');
		return $objResponse;
	}

	$objResponse->alert('Базата данни бе успешно възстановена от локален архив.');
	$objResponse->script("top.location.href='".basename($_SERVER['PHP_SELF'])."?tab=upd';");

	return $objResponse;
}
function deleteArchive($key) {
	$objResponse = new xajaxResponse();

	$objResponse->assign("btnIntDelete$key","disabled",false);
	$objResponse->assign("btnIntDelete$key","value","Изтрий архива");
	$objResponse->call("DisableEnableForm",false);

	if(file_exists('db/BKP'.$key.'.db')) {
		if(!@unlink('db/BKP'.$key.'.db')) {
			$objResponse->alert('Възникна неочакван проблем в процеса на изтриване на локалния архив от '.date("d.m.Y г. H:i:s ч.", $key).'!');
			return $objResponse;
		} else {
			$objResponse->alert('Локалният архив от '.date("d.m.Y г. H:i:s ч.", $key).' бе успешно изтрит.');
			$objResponse->script("top.location.href='".basename($_SERVER['PHP_SELF'])."?tab=upd';");
			return $objResponse;
		}
	} else {
		
		
		$opt1 = (file_exists('db/BKP'.$key.'.db')) ? 'Yes' : 'No';
		$opt2 = (file_exists('db/BKP'.$key)) ? 'Yes' : 'No';
		$objResponse->alert('Архивният файл не може да бъде намерен (db/BKP'.$key.'.db - '.$opt1.' | '.$opt2.')!');
		
		
		return $objResponse;
	}

	

	return $objResponse;
}
function uploadExtDB($aFormValues=null) {
	$objResponse = new xajaxResponse();

	$objResponse->assign("btnExtRecover","disabled",false);
	$objResponse->assign("btnExtRecover","value","Възстанови");
	$objResponse->call("DisableEnableForm",false);

	$fname = 'db/'.trim($aFormValues['archivedb']);
	if($fname == '') {
		$objResponse->alert('Моля, изберете архивен файл от външен носител.');
		return $objResponse;
	}
	if(!is_file($fname)) {
		$objResponse->alert($fname.' е невалиден архивен файл или не съществува!');
		return $objResponse;
	}
	if(!file_exists($fname)) {
		$objResponse->alert('Моля, изберете архивен файл от външен носител.');
		return $objResponse;
	}
	if(!preg_match('/^BKP(\d{10})\.db/', basename($fname), $matches)) {
		$objResponse->alert($fname.' е невалиден архивен файл!');
		return $objResponse;
	}

	// Backup original DB
	if(file_exists('db/stm.db')) {
		if(!@copy('db/stm.db', 'db/BKP'.time().'.db')) {
			$objResponse->alert('Възникна неочакван проблем в процеса на възстановяване!');
			return $objResponse;
		}
	}
	// Make archive DB active
	if(!@copy($fname, 'db/stm.db')) {
		$objResponse->alert('Възникна неочакван проблем в процеса на възстановяване!');
		return $objResponse;
	}

	global $dbInst;
	$db = $dbInst->getDBHandle();
	try {
		$db->beginTransaction();
		$count = $db->exec("UPDATE users SET hdd = '' WHERE 1");
		$db->commit();
	} catch (Exception $e) {
		$db->rollBack();
	}

	$objResponse->alert('Базата данни бе успешно възстановена от външен носител.');
	$objResponse->script("top.location.href='".basename($_SERVER['PHP_SELF'])."?tab=upd';");

	return $objResponse;
}
$xajax = new xajax();
$xajax->registerFunction("processFactors");
$xajax->registerFunction("processDoctorPos");
$xajax->registerFunction("deleteFactor");
$xajax->registerFunction("deleteDoctorPos");
$xajax->registerFunction("processLabs");
$xajax->registerFunction("processAccounts");
$xajax->registerFunction("deleteLab");
$xajax->registerFunction("deleteAccount");
$xajax->registerFunction("processDoctor");
$xajax->registerFunction("processStmInfo");
$xajax->registerFunction("deleteDoctor");
$xajax->registerFunction("changePwd");
$xajax->registerFunction("archiveDB");
$xajax->registerFunction("recoverDB");
$xajax->registerFunction("deleteArchive");
$xajax->registerFunction("uploadExtDB");
//$xajax->setFlag("debug",true);
$echoJS = $xajax->getJavascript('xajax/');
$xajax->processRequest();
// Xajax end

function echoFactors() {
	global $dbInst;
	ob_start();
	?>
	  <form id="frmSubmit" name="frmSubmit" action="javascript:void(null);">
		<div id="sub1" class="submenu">
          <table id="listtable">
            <tbody>
              <tr>
                <th>Фактор</th>
                <th>ПДК max</th>
                <th>ПДК min</th>
                <th>Мерни единици</th>
                <th>&nbsp;</th>
              </tr>
              <?php
              $factors = $dbInst->getFactors();
              foreach ($factors as $factor) {
              ?>
              <tr id="line_<?=$factor['factor_id']?>">
                <td><input type="text" id="factor_name_<?=$factor['factor_id']?>" name="factor_name_<?=$factor['factor_id']?>" value="<?=HTMLFormat($factor["factor_name"])?>" size="40" maxlength="60" /></td>
                <td><input type="text" id="pdk_max_<?=$factor['factor_id']?>" name="pdk_max_<?=$factor['factor_id']?>" value="<?=HTMLFormat($factor["pdk_max"])?>" size="15" maxlength="50" onKeyPress="return floatsonly(this, event);" /></td>
                <td><input type="text" id="pdk_min_<?=$factor['factor_id']?>" name="pdk_min_<?=$factor['factor_id']?>" value="<?=HTMLFormat($factor["pdk_min"])?>" size="15" maxlength="50" onKeyPress="return floatsonly(this, event);" /></td>
                <td><input type="text" id="factor_dimension_<?=$factor['factor_id']?>" name="factor_dimension_<?=$factor['factor_id']?>" value="<?=HTMLFormat($factor["factor_dimension"])?>" size="20" maxlength="20" /></td>
                <td align="center"><a href="javascript:void(null);" onclick="xajax_deleteFactor(<?=$factor['factor_id']?>);return false;" title="Изтриване"><img src="img/delete.gif" alt="delete" width="15" height="15" border="0" align="top" /></a></td>
              </tr>
              <?php
              }
              ?>
              <tr>
                <td><input type="text" id="factor_name_0" name="factor_name_0" value="" size="40" maxlength="50" class="newItem" /></td>
                <td><input type="text" id="pdk_max_0" name="pdk_max_0" value="" size="15" maxlength="50" class="newItem" onKeyPress="return floatsonly(this, event);" /></td>
                <td><input type="text" id="pdk_min_0" name="pdk_min_0" value="" size="15" maxlength="50" class="newItem" onKeyPress="return floatsonly(this, event);" /></td>
                <td><input type="text" id="factor_dimension_0" name="factor_dimension_0" value="" size="20" maxlength="50" class="newItem" /></td>
                <td align="center">&nbsp;</td>
              </tr>
              <tr>
                <th colspan="5" align="center"><input type="button" id="btnSubmit" name="btnSubmit" value="Съхрани" class="nicerButtons" onclick="this.disabled=true;this.value='обработка...';xajax_processFactors(xajax.getFormValues('frmSubmit'));DisableEnableForm(true);return false;" /></th>
              </tr>
            </tbody>
          </table>
        </div>
      </form>
	<?php
	$buff = ob_get_contents();
	ob_end_clean();
	return $buff;
}

function echoDoctorPos() {
	global $dbInst;
	ob_start();
	?>
	  <form id="frmSubmit" name="frmSubmit" action="javascript:void(null);">
		<div id="sub1" class="submenu">
          <table id="listtable">
            <tbody>
              <tr>
                <th>Специалисти</th>
                <th>&nbsp;</th>
              </tr>
              <?php
              $rows = $dbInst->getDoctorsPulldown('doctor_pos_id');
              foreach ($rows as $row) {
              ?>
              <tr id="line_<?=$row['doctor_pos_id']?>">
                <td><input type="text" id="doctor_pos_name_<?=$row['doctor_pos_id']?>" name="doctor_pos_name_<?=$row['doctor_pos_id']?>" value="<?=HTMLFormat($row["doctor_pos_name"])?>" size="40" maxlength="60" /></td>
                <td align="center"><?php if('0' == $row['default']) { ?><a href="javascript:void(null);" onclick="xajax_deleteDoctorPos(<?=$row['doctor_pos_id']?>);return false;" title="Изтриване"><img src="img/delete.gif" alt="delete" width="15" height="15" border="0" align="top" /></a><?php } else { echo '--'; } ?></td>
              </tr>
              <?php
              }
              ?>
              <tr>
                <td><input type="text" id="doctor_pos_name_0" name="doctor_pos_name_0" value="" size="40" maxlength="50" class="newItem" /></td>
                <td align="center">&nbsp;</td>
              </tr>
              <tr>
                <th colspan="5" align="center"><input type="button" id="btnSubmit" name="btnSubmit" value="Съхрани" class="nicerButtons" onclick="this.disabled=true;this.value='обработка...';xajax_processDoctorPos(xajax.getFormValues('frmSubmit'));DisableEnableForm(true);return false;" /></th>
              </tr>
            </tbody>
          </table>
        </div>
      </form>
	<?php
	$buff = ob_get_contents();
	ob_end_clean();
	return $buff;
}

function echoSTM() {
	global $dbInst;
	ob_start();
	$f = $dbInst->getStmInfo();
	?>
      <form id="frmSubmit" name="frmSubmit" action="javascript:void(null);">
        <table cellpadding="0" cellspacing="0" class="formBg">
          <tr>
            <th colspan="4" class="leftSplit rightSplit topSplit">Основна информация за службата по трудова медицина </th>
          </tr>
		  <!--
          <tr>
            <td class="leftSplit rightSplit"><strong>Наименование: </strong></td>
            <td class="rightSplit"><input type="text" id="stm_name" name="stm_name" value="<?=((isset($f['stm_name']))?HTMLFormat($f['stm_name']):'')?>" size="80" maxlength="100" />
            </td>
          </tr>
          <tr>
            <td class="leftSplit rightSplit">Удостоверение  №: </td>
            <td class="rightSplit"><input type="text" id="license_num" name="license_num" value="<?=((isset($f['license_num']))?HTMLFormat($f['license_num']):'')?>" size="25" maxlength="100" />
              от Министерство на Здравеопазването </td>
          </tr>
		  -->
          <tr>
            <td class="leftSplit"><strong>Наименование: </strong></td>
            <td class="rightSplit"><strong><?=((isset($f['stm_name']))?HTMLFormat($f['stm_name']):'')?></strong>
              <input type="hidden" id="stm_name" name="stm_name" value="<?=((isset($f['stm_name']))?HTMLFormat($f['stm_name']):'')?>" />
            </td>
          </tr>
          <tr>
            <td class="leftSplit"><p>Удостоверение  №: </p></td>
            <td class="rightSplit"><strong><?=((isset($f['license_num']))?HTMLFormat($f['license_num']):'')?></strong>
              <input type="hidden" id="license_num" name="license_num" value="<?=((isset($f['license_num']))?HTMLFormat($f['license_num']):'')?>" />
              от Министерство на Здравеопазването </td>
          </tr>
          <tr>
            <td class="leftSplit"><strong>Адрес: </strong></td>
            <td class="rightSplit"><input type="text" id="address" name="address" value="<?=((isset($f['address']))?HTMLFormat($f['address']):'')?>" size="80" maxlength="100" />
            </td>
          </tr>
          <tr>
            <td class="leftSplit"><strong>Ръководител: </strong></td>
            <td class="rightSplit"><input type="text" id="chief" name="chief" value="<?=((isset($f['chief']))?HTMLFormat($f['chief']):'')?>" size="80" maxlength="100" />
            </td>
          </tr>
          <tr>
            <td class="leftSplit">Тел. 1: </td>
            <td class="rightSplit"><input type="text" id="phone1" name="phone1" value="<?=((isset($f['phone1']))?HTMLFormat($f['phone1']):'')?>" size="40" maxlength="50" />
            </td>
          </tr>
          <tr>
            <td class="leftSplit">Тел. 2: </td>
            <td class="rightSplit"><input type="text" id="phone2" name="phone2" value="<?=((isset($f['phone2']))?HTMLFormat($f['phone2']):'')?>" size="40" maxlength="50" />
            </td>
          </tr>
          <tr>
            <td class="leftSplit">Факс: </td>
            <td class="rightSplit"><input type="text" id="fax" name="fax" value="<?=((isset($f['fax']))?HTMLFormat($f['fax']):'')?>" size="40" maxlength="50" />
            </td>
          </tr>
          <tr>
            <td class="leftSplit">E-mail: </td>
            <td class="rightSplit"><input type="text" id="email" name="email" value="<?=((isset($f['email']))?HTMLFormat($f['email']):'')?>" size="40" maxlength="50" />
            </td>
          </tr>
          <tr>
            <td class="leftSplit">&nbsp;</td>
            <td class="rightSplit">
                <input type="button" id="btnSubmit" name="btnSubmit" value="Съхрани" class="nicerButtons" onclick="this.disabled=true;this.value='обработка...';xajax_processStmInfo(xajax.getFormValues('frmSubmit'));DisableEnableForm(true);return false;" />
            </td>
          </tr>
        </table>
      </form>
	<?php
	$buff = ob_get_contents();
	ob_end_clean();
	return $buff;
}

function echoUpd() {
	global $dbInst;
	global $msg;
	global $tab;
	ob_start();
	$f = $dbInst->getStmInfo();

	if(isset($_SESSION['sess_msg'])) {
		$msg = $_SESSION['sess_msg'];
		unset($_SESSION['sess_msg']);
	}
	if(count($msg)) {
		echo '<p class="notes">&nbsp;&nbsp;&nbsp;<img src="img/moreinfo.gif" alt="info" border="0" width="17" height="17" /> &nbsp;';
		echo implode('<br />', $msg);
		echo '</p>';
	}
	?>

      <h2>Архивиране на текущата база данни</h2>
      <form id="frmArchive" name="frmArchive" action="javascript:void(null);">
        <table cellpadding="0" cellspacing="0" class="formBg">
          <tr>
            <th><img src="img/download2.gif" alt="download" border="0" width="16" height="16" /> &nbsp;
              <input type="button" id="btnArchive" name="btnArchive" value="Архивирай базата данни" onclick="this.disabled=true;this.value='обработка...';xajax_archiveDB();DisableEnableForm(true);return false;" class="nicerButtons_lg" /></th>
          </tr>
        </table>
      </form>
      <h2>Възстановяване на базата данни от архивна база данни</h2>
      <?php
      $doupload = 0;
      if(isset($_SERVER['SERVER_SOFTWARE']) && preg_match('/Apache/i', $_SERVER['SERVER_SOFTWARE'])) {
      	//if(preg_match('/Хипократ/i', $f['stm_name'])) {
      	$doupload = 1;
      }
      if($doupload) {
      ?>
      <form id="frmExtRecover" name="frmExtRecover" action="<?=basename($_SERVER['PHP_SELF'])?>?tab=upd" method="post" enctype="multipart/form-data">
      <?php } else { ?>
      <form id="frmExtRecover" name="frmExtRecover" action="javascript:void(null);">
      <?php } ?>
        <table border="0" cellpadding="0" cellspacing="0" class="xlstable" style="width:99%">
          <tr>
            <td align="center" class="notes"><img src="img/caution.gif" alt="warning" border="0" width="11" height="11" /> &nbsp;В Н И М А Н И Е ! След възстановяването архивните данни ще заменят текущите данни в системата. </td>
          </tr>
          <tr>
            <th>Избери архивен файл (напр. BKP1216835014.db) от външен носител :
              <input type="file" id="archivedb" name="archivedb" value="" />
            </th>
          </tr>
          <tr>
            <th><img src="img/backup_(start)_16x16.gif" border="0" alt="backupstart" width="16" height="16" /> &nbsp;
              <?php if($doupload) { ?>
              <input type="submit" id="btnExtRecover" name="btnExtRecover" value="Възстанови" onclick="var answ=confirm('В Н И М А Н И Е ! \n\nСлед възстановяването архивните данни ще заменят текущите данни в системата.\nСигурни ли сте, че искате да продължите?');if(!answ){return false;} this.value='обработка...';" class="nicerButtons_lg" />
              <?php } else { ?>
              <input type="button" id="btnExtRecover" name="btnExtRecover" value="Възстанови" onclick="var answ=confirm('В Н И М А Н И Е ! \n\nСлед възстановяването архивните данни ще заменят текущите данни в системата.\nСигурни ли сте, че искате да продължите?');if(!answ){return false;} this.value='обработка...';this.disabled=true;xajax_uploadExtDB(xajax.getFormValues('frmExtRecover'));DisableEnableForm(true);return false;" class="nicerButtons_lg" />
              <?php } ?></th>
          </tr>
        </table>
      </form>
      <?php
      $_BKPs = array();
      if ($handle = opendir('db/')) {
      	while (false !== ($file = readdir($handle))) {
      		if ($file != "." && $file != "..") {
      			//BKP1216844309.db
      			if(preg_match('/^BKP(\d{10})\.db/', $file, $matches)) {
      				$fsize = filesize('db/'.$file) / 1024;
      				$_BKPs[$matches[1]] =  'Локален архив от '.date("d.m.Y г. H:i:s ч.", $matches[1]).' ( '.number_format($fsize, 0, ',', ' ').' KB )';
      			}
      		}
      	}
      	closedir($handle);
      }
      krsort($_BKPs);	// Sort an array by key in reverse order
      if(count($_BKPs)) {
      ?>
      <form id="frmIntRecover" name="frmIntRecover" action="javascript:void(null);">
        <div id="sub4" class="submenu">
          <table border="0" cellpadding="0" cellspacing="0" class="xlstable" id="listtable">
            <tr class="notover">
              <th colspan="5">ИЛИ<br />
                избери съществуваща архивна база данни</th>
            </tr>
            <?php
            $i = 0;
            foreach ($_BKPs as $key=>$file) {
            ?>
            <tr>
              <td align="left" width="3%"><?=++$i?>. </td>
              <td align="left" width="61%"><a href="preview_archive.php?id=<?=$key?>&amp;<?=SESS_NAME.'='.session_id()?>&amp;KeepThis=true&amp;TB_iframe=true&amp;height=465&amp;width=790&amp;modal=true" title="Преглед на <?=$file?> <?=(($i==1))?'* - последно създаден':''?>" class="thickbox"><img src="img/support2_16x16.gif" border="0" alt="dbbackup" width="16" height="16" /> &nbsp;<?=$file?> <?=(($i==1))?'* - последно създаден':''?></a> </td>
              <td width="12%">
              <?php if(is_file('db/BKP'.$key.'.db')) { ?><input type="button" id="btnDownload<?=$key?>" name="btnDownload<?=$key?>" value="Свали" onclick="window.location.href='db/BKP<?=$key?>.db';" class="nicerButtons" /><?php } ?>
              </td>
              <td align="left" width="12%"><input type="button" id="btnIntRecover<?=$key?>" name="btnIntRecover<?=$key?>" value="Възстанови" onclick="var answ=confirm('В Н И М А Н И Е ! \n\nСлед възстановяването архивните данни ще заменят текущите данни в системата.\nСигурни ли сте, че искате да продължите?');if(!answ){return false;} this.disabled=true;this.value='обработка...';xajax_recoverDB('<?=$key?>');DisableEnableForm(true);return false;" class="nicerButtons" />
              </td>
              <td align="left" width="12%"><input type="button" id="btnIntDelete<?=$key?>" name="btnIntDelete<?=$key?>" value="Изтрий архива" onclick="var answ=confirm('Сигурни ли сте, че искате да изтриете\n<?=$file?>?');if(!answ){return false;} this.disabled=true;this.value='обработка...';xajax_deleteArchive('<?=$key?>');DisableEnableForm(true);return false;" class="nicerButtons" />
              </td>
            </tr>
            <?php } ?>
          </table>
        </div>
      </form>
      <?php } ?>
      <!--
      <h2>Съхраняване на данните във файл за пренос в друга база данни </h2>
      <form id="frmExport" name="frmExport" action="javascript:void(null);">
        <div id="sub5" class="submenu">
          <table border="0" cellpadding="0" cellspacing="0" class="xlstable">
            <tr>
              <th><img src="img/download2.gif" alt="download" border="0" width="16" height="16" /> &nbsp;
                <input type="button" id="btnExport" name="btnExport" value="Съхрани" onclick="this.value='обработка...';" class="nicerButtons" style="width:200px" /></th>
            </tr>
          </table>
        </div>
      </form>
      <h2>Въвеждане на информация от друга база данни</h2>
      <form id="frmSubmit" name="frmSubmit" action="<?=basename($_SERVER['PHP_SELF'])?>?tab=<?=$tab?>" method="post" enctype="multipart/form-data">
        <div id="sub6" class="submenu">
          <table border="0" cellpadding="0" cellspacing="0" class="xlstable">
            <tr>
              <td class="notes"><img src="img/caution.gif" alt="warning" border="0" width="11" height="11" /> &nbsp;В Н И М А Н И Е ! Новите данни ще бъдат добавени към вече съществуващите данни в системата.</td>
            </tr>
            <tr>
              <th><img src="img/backup_(start)_16x16.gif" border="0" alt="backupstart" width="16" height="16" /> &nbsp;Избери  файл (напр. UPD1216835014.xml) от външен носител:
                <input type="file" id="updatedb" name="updatedb" value="" />
              </th>
            </tr>
            <tr>
              <th><input type="submit" id="btnSubmit" name="btnSubmit" value="Въведи" onclick="var answ=confirm('В Н И М А Н И Е ! \n\nНовите данни ще бъдат добавени към вече съществуващите данни в системата.');if(!answ){return false;} this.value='обработка...';" class="nicerButtons" style="width:200px" /></th>
            </tr>
          </table>
        </div>
      </form>

      <h2>Актуализация на системата</h2>
      <form id="frmUpd" name="frmUpd" action="<?=basename($_SERVER['PHP_SELF'])?>?tab=<?=$tab?>" method="post" enctype="multipart/form-data">
        <div id="sub7" class="submenu">
          <table border="0" cellpadding="0" cellspacing="0" class="xlstable">
            <tr>
              <th>Избери файл с данни:
                <input type="file" id="updfile" name="updfile" value="" />
              </th>
            </tr>
            <tr>
              <th align="center"><input type="submit" id="btnUpd" name="btnUpd" value="Актуализирай" class="nicerButtons" onclick="this.value='обработка...';" style="width:200px" /></th>
            </tr>
          </table>
        </div>
      </form>
      -->
	<?php
	$buff = ob_get_contents();
	ob_end_clean();
	return $buff;
}

function echoPwd() {
	global $dbInst;
	ob_start();
	?>
      <form id="frmSubmit" name="frmSubmit" action="javascript:void(null);">
        <table cellpadding="0" cellspacing="0" class="formBg">
          <tr>
            <td class="leftSplit topSplit">Сегашна парола: </td>
            <td class="rightSplit topSplit"><input type="password" id="user_pass" name="user_pass" value="" /></td>
          </tr>
          <tr>
            <td class="leftSplit">Нова парола: </td>
            <td class="rightSplit"><input type="password" id="new_user_pass" name="new_user_pass" value="" /></td>
          </tr>
          <tr>
            <td class="leftSplit">Повторете паролата: </td>
            <td class="rightSplit"><input type="password" id="new_user_pass2" name="new_user_pass2" value="" /></td>
          </tr>
          <tr>
            <td class="leftSplit">&nbsp;</td>
            <td class="rightSplit">
                <input type="button" id="btnSubmit" name="btnSubmit" value="Актуализирай" class="nicerButtons" onclick="this.disabled=true;this.value='обработка...';xajax_changePwd(xajax.getFormValues('frmSubmit'));DisableEnableForm(true);return false;" />
            </td>
          </tr>
        </table>
      </form>
	<?php
	$buff = ob_get_contents();
	ob_end_clean();
	return $buff;
}

function echoAccounts() {
	global $dbInst;
	ob_start();
	?>
	  <form id="frmSubmit" name="frmSubmit" action="javascript:void(null);">
		<div id="sub1" class="submenu">
          <table id="listtable">
            <tbody>
              <tr>
                <th>Потребителско име</th>
                <th>Парола</th>
                <th>Име</th>
                <th>Фамилия</th>
                <th>Дата на<br />създаване</th>
                <th>Последен вход<br />в системата</th>
                <th>&nbsp;</th>
              </tr>
              <?php
              $rows = $dbInst->getAccounts();
              foreach ($rows as $row) {
              ?>
              <tr id="line_<?=$row['user_id']?>">
                <td align="left"><?php if('demo' == $row["user_name"]) { ?><strong>demo</strong><input type="hidden" id="user_name_<?=$row['user_id']?>" name="user_name_<?=$row['user_id']?>" value="<?=$row["user_name"]?>" /><?php } else { ?><input type="text" id="user_name_<?=$row['user_id']?>" name="user_name_<?=$row['user_id']?>" value="<?=HTMLFormat($row["user_name"])?>" readonly="readonly" size="30" maxlength="60" /><?php } ?></td>
                <td align="left"><span>
                <input type="password" id="user_pass_<?=$row['user_id']?>" name="user_pass_<?=$row['user_id']?>" value="<?=HTMLFormat($row["user_pass"])?>" size="24" style="width:100px;border:none;background:none;" />
                </span><a id="lnkReveal_<?=$row['user_id']?>" href="javascript:void(null);" onclick="unmaskPwd(this);return false;">(покажи)</a></td>
                <td align="left"><input type="text" id="fname_<?=$row['user_id']?>" name="fname_<?=$row['user_id']?>" value="<?=HTMLFormat($row["fname"])?>" size="30" maxlength="60" /></td>
                <td align="left"><input type="text" id="lname_<?=$row['user_id']?>" name="lname_<?=$row['user_id']?>" value="<?=HTMLFormat($row["lname"])?>" size="30" maxlength="60" /></td>
                <td align="left"><?=$row["date_created2"]?></td>
                <td align="left"><?=$row["date_last_login2"]?></td>
                <td align="center"><?php if('demo' != $row["user_name"]) { ?><a href="javascript:void(null);" onclick="var answ=confirm('Наистина ли сикате да изтриете акаунта?');if(!answ){return false;}xajax_deleteAccount(<?=$row['user_id']?>);return false;" title="Изтриване"><img src="img/delete.gif" alt="delete" width="15" height="15" border="0" align="top" /></a><?php } ?>&nbsp;</td>
              </tr>
              <?php
              }
              ?>
              <tr>
                <td align="left"><input type="text" id="user_name_0" name="user_name_0" value="" size="30" maxlength="60" class="newItem" /></td>
                <td align="left"><input type="text" id="user_pass_0" name="user_pass_0" value="" size="30" maxlength="60" class="newItem" /></td>
                <td align="left"><input type="text" id="fname_0" name="fname_0" value="" size="30" maxlength="60" class="newItem" /></td>
                <td align="left"><input type="text" id="lname_0" name="lname_0" value="" size="30" maxlength="60" class="newItem" /></td>
                <td align="left">&nbsp;</td>
                <td align="left">&nbsp;</td>
                <td align="left">&nbsp;</td>
              </tr>
              <tr>
                <th colspan="7" align="center"><input type="button" id="btnSubmit" name="btnSubmit" value="Съхрани" class="nicerButtons" onclick="this.disabled=true;this.value='обработка...';xajax_processAccounts(xajax.getFormValues('frmSubmit'));DisableEnableForm(true);return false;" /></th>
              </tr>
            </tbody>
          </table>
        </div>
      </form>
	<?php
	$buff = ob_get_contents();
	ob_end_clean();
	return $buff;
}

function echoLabs() {
	global $dbInst;
	ob_start();
	?>
	  <form id="frmSubmit" name="frmSubmit" action="javascript:void(null);">
		<div id="sub1" class="submenu">
          <table id="listtable">
            <tbody>
              <tr>
                <th>Вид</th>
                <th>Показател</th>
                <th>Min</th>
                <th>Max</th>
                <th>Мерни единици</th>
                <th>&nbsp;</th>
              </tr>
              <?php
              $labs = $dbInst->getLabs();
              foreach ($labs as $lab) {
              ?>
              <tr id="line_<?=$lab['indicator_id']?>">
                <td><input type="text" id="indicator_type_<?=$lab['indicator_id']?>" name="indicator_type_<?=$lab['indicator_id']?>" value="<?=HTMLFormat($lab["indicator_type"])?>" size="40" maxlength="60" /></td>
                <td><input type="text" id="indicator_name_<?=$lab['indicator_id']?>" name="indicator_name_<?=$lab['indicator_id']?>" value="<?=HTMLFormat($lab["indicator_name"])?>" size="40" maxlength="60" /></td>
                <td><input type="text" id="pdk_min_<?=$lab['indicator_id']?>" name="pdk_min_<?=$lab['indicator_id']?>" value="<?=HTMLFormat($lab["pdk_min"])?>" size="15" maxlength="50" onKeyPress="return floatsonly(this, event);" /></td>
                <td><input type="text" id="pdk_max_<?=$lab['indicator_id']?>" name="pdk_max_<?=$lab['indicator_id']?>" value="<?=HTMLFormat($lab["pdk_max"])?>" size="15" maxlength="50" onKeyPress="return floatsonly(this, event);" /></td>
                <td><input type="text" id="indicator_dimension_<?=$lab['indicator_id']?>" name="indicator_dimension_<?=$lab['indicator_id']?>" value="<?=HTMLFormat($lab["indicator_dimension"])?>" size="20" maxlength="20" /></td>
                <td align="center"><a href="javascript:void(null);" onclick="xajax_deleteLab(<?=$lab['indicator_id']?>);return false;" title="Изтриване"><img src="img/delete.gif" alt="delete" width="15" height="15" border="0" align="top" /></a></td>
              </tr>
              <?php
              }
              ?>
              <tr>
                <td><input type="text" id="indicator_type_0" name="indicator_type_0" value="" size="40" maxlength="50" class="newItem" /></td>
                <td><input type="text" id="indicator_name_0" name="indicator_name_0" value="" size="40" maxlength="50" class="newItem" /></td>
                <td><input type="text" id="pdk_min_0" name="pdk_min_0" value="" size="15" maxlength="50" class="newItem" onKeyPress="return floatsonly(this, event);" /></td>
                <td><input type="text" id="pdk_max_0" name="pdk_max_0" value="" size="15" maxlength="50" class="newItem" onKeyPress="return floatsonly(this, event);" /></td>
                <td><input type="text" id="indicator_dimension_0" name="indicator_dimension_0" value="" size="20" maxlength="50" class="newItem" /></td>
                <td align="center">&nbsp;</td>
              </tr>
              <tr>
                <th colspan="6" align="center"><input type="button" id="btnSubmit" name="btnSubmit" value="Съхрани" class="nicerButtons" onclick="this.disabled=true;this.value='обработка...';xajax_processLabs(xajax.getFormValues('frmSubmit'));DisableEnableForm(true);return false;" /></th>
              </tr>
            </tbody>
          </table>
        </div>
      </form>
	<?php
	$buff = ob_get_contents();
	ob_end_clean();
	return $buff;
}

function echoDoctors() {
	global $dbInst;

	$perPage = (isset($_GET['perPage'])) ? abs(intval($_GET['perPage'])) : 25;
	$_SESSION['sess_QUERY_STRING'] = (isset($_SERVER['QUERY_STRING'])) ? '?'.$_SERVER['QUERY_STRING'] : '';

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
	$query = "SELECT d.*, (SELECT COUNT(*) FROM workers w WHERE w.doctor_id = d.doctor_id AND is_active = '1' AND date_retired = '' ) AS patients_num FROM doctors d";
	$txtCondition = "";

	if(isset($_GET['btnFind']) || (isset($_GET['keyword']) && trim($_GET['keyword']) != '') ) {	// Filter workers
		if(isset($_GET['keyword']) && trim($_GET['keyword']) != '') {
			$keyword = $dbInst->checkStr($_GET['keyword']);
			$uc_keyword = $dbInst->my_mb_ucfirst($keyword);
			$txtCondition .= (preg_match('/\bWHERE\b/', $txtCondition)) ? ' AND ' : ' WHERE ';
			$txtCondition .= "(doctor_name LIKE '%$keyword%' OR address LIKE '%$keyword%' OR doctor_name LIKE '%$uc_keyword%' OR address LIKE '%$uc_keyword%')";
		}
	}	// Search end
	$sortArr = array('doctor_name','address','phone1','phone2','patients_num');
	if (isset($_GET["sort_by"]) && in_array($_GET["sort_by"],$sortArr)) {
		$order = (isset($_GET['order']) && $_GET['order']=='ASC') ? 'ASC' : 'DESC';
		$txtCondition .= " ORDER BY `$_GET[sort_by]` $order, d.doctor_id";
	}
	else $txtCondition .= " ORDER BY d.doctor_name, d.doctor_id";

	$query .= $txtCondition;
	//die($query);
	$db = $dbInst->getDBHandle();
	$paged_data = Pager_Wrapper_PDO($db, $query, $pagerOptions);
	$doctors = $paged_data['data'];  //paged data
	$links = $paged_data['links']; //xhtml links for page navigation
	$current = (isset($paged_data['page_numbers']['current'])) ? $paged_data['page_numbers']['current'] : 0;
	$totalItems = $paged_data['totalItems'];
	$from = ($current) ? $paged_data['from'] : 0;
	$to = $paged_data['to'];
	// PAGER END


	ob_start();
	?>
	  <form id="frmSubmit" name="frmSubmit" action="<?=basename($_SERVER['PHP_SELF'])?>" method="get">
		<div id="sub1" class="submenu">
          <div id="searchHolder">
            <input type="hidden" id="page" name="page" value="1" />
            <input type="hidden" id="tab" name="tab" value="doctors" />
            <table width="100%" border="0" cellpadding="3" cellspacing="0" id="admin_search" class="inset">
              <tbody>
                <tr>
                  <td align="left">Търсене по име или адрес на практика:
                    <input type="text" id="keyword" name="keyword" value="<?=((isset($_GET['keyword']))?HTMLFormat($_GET['keyword']):'')?>" size="35" />
                    <input type="button" id="btnFind" name="btnFind" value="Намери" class="nicerButtons" onclick="window.location='<?=basename($_SERVER['PHP_SELF'])?>?tab=doctors&btnFind=go&keyword='+document.getElementById('keyword').value" /></td>
                </tr>
              </tbody>
            </table>
          </div>
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
                <th><?php if (isset($_GET["sort_by"])&&$_GET["sort_by"]=="doctor_name"){?><img src="img/<?php if (isset($_GET["order"])&&$_GET["order"]=="DESC"){ ?>sort_arrow_down.gif<?php } else { ?>sort_arrow_up.gif<?php } ?>" alt="Sort" width="16" height="16" border="0" /><?php } ?>
                <a href="<?=basename($_SERVER['PHP_SELF']).cleanQueryString('sort_by=doctor_name&order='.((isset($_GET["sort_by"])&&$_GET["sort_by"]=="doctor_name")?(($_GET["order"]=="DESC")?"ASC":"DESC"):"ASC"))?>" title="Сортиране по име">Име</a></th>
                <th><?php if (isset($_GET["sort_by"])&&$_GET["sort_by"]=="address"){?><img src="img/<?php if (isset($_GET["order"])&&$_GET["order"]=="DESC"){ ?>sort_arrow_down.gif<?php } else { ?>sort_arrow_up.gif<?php } ?>" alt="Sort" width="16" height="16" border="0" /><?php } ?>
                <a href="<?=basename($_SERVER['PHP_SELF']).cleanQueryString('sort_by=address&order='.((isset($_GET["sort_by"])&&$_GET["sort_by"]=="address")?(($_GET["order"]=="DESC")?"ASC":"DESC"):"ASC"))?>" title="Сортиране по адрес">Адрес на практика</a></th>
                <th><?php if (isset($_GET["sort_by"])&&$_GET["sort_by"]=="phone1"){?><img src="img/<?php if (isset($_GET["order"])&&$_GET["order"]=="DESC"){ ?>sort_arrow_down.gif<?php } else { ?>sort_arrow_up.gif<?php } ?>" alt="Sort" width="16" height="16" border="0" /><?php } ?>
                <a href="<?=basename($_SERVER['PHP_SELF']).cleanQueryString('sort_by=phone1&order='.((isset($_GET["sort_by"])&&$_GET["sort_by"]=="phone1")?(($_GET["order"]=="DESC")?"ASC":"DESC"):"ASC"))?>" title="Сортиране по тел. 1">Тел. 1</a></th>
                <th><?php if (isset($_GET["sort_by"])&&$_GET["sort_by"]=="phone2"){?><img src="img/<?php if (isset($_GET["order"])&&$_GET["order"]=="DESC"){ ?>sort_arrow_down.gif<?php } else { ?>sort_arrow_up.gif<?php } ?>" alt="Sort" width="16" height="16" border="0" /><?php } ?>
                <a href="<?=basename($_SERVER['PHP_SELF']).cleanQueryString('sort_by=phone2&order='.((isset($_GET["sort_by"])&&$_GET["sort_by"]=="phone2")?(($_GET["order"]=="DESC")?"ASC":"DESC"):"ASC"))?>" title="Сортиране по тел. 2">Тел. 2</a></th>
                <th><?php if (isset($_GET["sort_by"])&&$_GET["sort_by"]=="patients_num"){?><img src="img/<?php if (isset($_GET["order"])&&$_GET["order"]=="DESC"){ ?>sort_arrow_down.gif<?php } else { ?>sort_arrow_up.gif<?php } ?>" alt="Sort" width="16" height="16" border="0" /><?php } ?>
                <a href="<?=basename($_SERVER['PHP_SELF']).cleanQueryString('sort_by=patients_num&order='.((isset($_GET["sort_by"])&&$_GET["sort_by"]=="patients_num")?(($_GET["order"]=="DESC")?"ASC":"DESC"):"ASC"))?>" title="Сортиране по тел. 2">Бр. пациенти</a></th>
                <th>Редактирай</th>
                <th>Изтрий</th>
              </tr>
              <?php
              if(is_array($doctors) && count($doctors)>0) {
              	$i=0;
              	foreach ($doctors as $row) {
              ?>
              <tr>
                <td align="left"><?=$row['doctor_name']?></td>
                <td align="left"><?=$row['address']?></td>
                <td align="left"><?=$row['phone1']?></td>
                <td align="left"><?=$row['phone2']?></td>
                <td align="center"><strong><?=$row['patients_num']?></strong></td>
                <td align="center"><a href="form_doctor.php?doctor_id=<?=$row['doctor_id']?>&amp;reload=1&amp;<?=SESS_NAME.'='.session_id()?>&amp;height=160&amp;width=472&amp;modal=true" title="Редактиране на данните на <?=HTMLFormat($row['doctor_name'])?>" class="thickbox"><img src="img/edititem.gif" alt="Редактиране данните на <?=HTMLFormat($row['doctor_name'])?>" width="16" height="16" border="0" /></a></td>
                <td align="center"><a href="javascript:void(null);" onclick="var answ=confirm('Наистина ли искате да изтриете данните за фамилния лекар?');if(answ){xajax_deleteDoctor(<?=$row['doctor_id']?>);}return false;" title="Изтриване данните на <?=HTMLFormat($row['doctor_name'])?>"><img src="img/delete.gif" alt="Изтриване данните на <?=HTMLFormat($row['doctor_name'])?>" width="15" height="15" border="0" /></a></td>
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
              <!--<tr class="notover">
                <td colspan="7"><strong>Покажи </strong><input type="text" id="perPage" name="perPage" value="<?=$perPage?>" size="5" maxlength="10" onKeyPress="return numbersonly(this, event);" /> <strong>работещи на страница</strong></td>
              </tr>-->
              <tr>
                <th colspan="7" align="center"><input type="button" id="btnSubmit" name="btnSubmit" value="Нов лекар" onclick="tb_show('Добавяне на нов фамилен лекар','form_doctor.php?doctor_id=0&amp;reload=1&amp;<?=SESS_NAME.'='.session_id()?>&amp;height=160&amp;width=472&amp;modal=true',0);return false;" class="nicerButtons" /></th>
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
	  </form>
	<?php
	$buff = ob_get_contents();
	ob_end_clean();
	return $buff;
}

function echoSysUpdate() {
	global $dbInst;
	?>
	<?php if('' != ($msg = getFlash())) { ?>
	<div class="err"><?=$msg?></div>
	<?php } ?>
	<script type="text/javascript">
	//<![CDATA[
	function validate(form) {
		$('#waitMsgHolder').show();
		$('#btnImport').hide();
		$('#preLoader').show();
		return true;
	}
	//]]>
	</script>
	<table width="100%">
	  <tr>
	    <td align="center"><h2>Актуализация на системата от ZIP файл</h2></td>
	  </tr>
	</table>
	<form id="frmImport" method="post" action="sys_update.php<?=((isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) ? '?'.$_SERVER['QUERY_STRING'] : '')?>" enctype="multipart/form-data" onsubmit="return validate(this)">
	  <p>&nbsp;</p>
	  <p>Избери файл:
	    <input type="hidden" name="MAX_FILE_SIZE" value="<?=return_bytes(ini_get('upload_max_filesize'))?>" />
	    <input type="file" id="datafile" name="datafile" />
	    <button type="submit" id="btnImport" name="btnImport"> Въведи</button>
	    <span id="waitMsgHolder" style="display:none;">Моля, изчакайте...</span> </p>
	  <p>&nbsp;</p>
	</form>
	<?php
	return ob_get_clean();
}

$tab = (isset($_GET['tab'])) ? $_GET['tab'] : 'env';

$msg = array();
// Update the application
if(isset($_POST['btnUpd']))
{
	if ( $_FILES["updfile"]['size'] != 0 && $_FILES["updfile"]['size'] < 1048576 )
	{
		//Allowable file Mime Types. Add more mime types if you want
		$FILE_MIMES = array('text/xml');
		//Allowable file ext. names. you may add more extension names.
		$FILE_EXTS = array('xml');

		$fname = $_FILES["updfile"]['name'];
		$ftmp_name = $_FILES["updfile"]['tmp_name'];
		$ftype = $_FILES["updfile"]['type'];
		$fsize = $_FILES["updfile"]['size'];
		$fext = strtolower(substr($fname,-3));

		$file_name  = time() . "_";
		$file_name .= str_replace( " ", "_", $fname );
		$file_name  = strtolower( $file_name );

		if (in_array($ftype, $FILE_MIMES) && in_array($fext, $FILE_EXTS))
		{
			// FILE TYPE IS ALLOWED
			if (move_uploaded_file($ftmp_name, $file_name))
			{
				$db = $dbInst->getDBHandle();
				$xml = file_get_contents($file_name);
				$i = 0;
				if(preg_match_all('/\<query\>(.*?)\<\/query\>/si', $xml, $queries))
				{
					foreach ($queries[1] as $query) {
						$count = $db->exec(trim($query)); //returns affected rows
						$i++;
					}
				}

				if($i) $msg[] = 'Данните в системата бяха успешно актуализирани.';
				else $msg[] = 'Не бяха извършени актуализации в системата.';

				if(file_exists($file_name)) @unlink($file_name);
			}
			else
			{
				$msg[] = "Possible fishy upload! Here's some debugging info:<br />";
				$msg[] = $_FILES["updfile"]['error'] . " | " . $file_name;
			}
		}
		else
		{
			$msg[] = "Файлът $fname не може да извършва актуализация на системата!";
		}

		$_SESSION['sess_msg'] = $msg;
		header('Location:'.basename($_SERVER['PHP_SELF']).'?tab=upd');
		exit();
	}
}

// Recover current database from external archived database
if(isset($_POST['btnExtRecover']))
{
	if ( $_FILES["archivedb"]['size'] != 0 )
	{
		$err = 0;
		//Allowable file Mime Types. Add more mime types if you want
		$FILE_MIMES = array('application/octet-stream');

		$fname = $_FILES["archivedb"]['name'];
		$ftmp_name = $_FILES["archivedb"]['tmp_name'];
		$ftype = $_FILES["archivedb"]['type'];
		$fsize = $_FILES["archivedb"]['size'];

		if (in_array($ftype, $FILE_MIMES) && preg_match('/^BKP(\d{10})\.db/', $fname, $matches))
		{
			// FILE TYPE IS ALLOWED
			if (move_uploaded_file($ftmp_name, $fname))
			{
				// Backup original DB
				if(file_exists('db/stm.db')) {
					if(!@copy('db/stm.db', 'db/BKP'.time().'.db')) {
						$msg[] = 'Възникна неочакван проблем в процеса на възстановяване!';
						$err = 1;
					}
				}
				// Make archive DB active
				if(!@copy($fname, 'db/stm.db')) {
					$msg[] = 'Възникна неочакван проблем в процеса на възстановяване!';
					$err = 1;
				}
				if(!$err) {
					$msg[] = 'Базата данни бе успешно възстановена от външен носител.';
				}
			}
			else
			{
				$msg[] = "Possible fishy upload! Here's some debugging info:<br />";
				$msg[] = $_FILES["updfile"]['error'] . " | " . $file_name;
			}
		}
		else
		{
			$msg[] = $fname.' е невалиден архивен файл!';
		}

		$_SESSION['sess_msg'] = $msg;
		header('Location:'.basename($_SERVER['PHP_SELF']).'?tab=upd');
		exit();
	}
}

include("header.php");
?>
<script type="text/javascript">
//<![CDATA[
$(document).ready(function() {
	stripTable('listtable');
	$("#listtable input:text").css("width","99%");
});
function unmaskPwd(el) {
	var passwordInput = $(el).parent().find("input");
	var user_id = $(el).attr('id').split('_')[1];
	var value = passwordInput.val();
	var type = "text";
	if("password" == passwordInput.attr("type").toLowerCase()) {
		type = "text";
		$(el).html("(скрий)");
	} else {
		type = "password";
		$(el).html("(покажи)");
	}
	$(el).parent().find("span").html('<input type="' + type + '" id="user_pass_' + user_id + '" name="user_pass_' + user_id + '" value="' + value + '" size="24" maxlength="70" readonly="readonly" style="width:100px;border:none;background:none;" \/>');
}
//]]>
</script>

    <div id="tabs"> <a href="official_data.php?tab=env" title="Фактори на работната среда" class="tab<?=(('env'==$tab)?' active':'')?>">Фактори на работната среда </a> <a href="official_data.php?tab=lab" title="Лабораторни показатели" class="tab<?=(('lab'==$tab)?' active':'')?>">Лабораторни показатели</a> <a href="official_data.php?tab=doctor_pos" title="Лекари" class="tab<?=(('doctor_pos'==$tab)?' active':'')?>">Лекари</a> <a href="official_data.php?tab=doctors" title="Фамилни лекари" class="tab<?=(('doctors'==$tab)?' active':'')?>">Фамилни лекари</a> <a href="official_data.php?tab=stm" title="За СТМ" class="tab<?=(('stm'==$tab)?' active':'')?>">За СТМ</a> <a href="official_data.php?tab=pwd" title="Смяна на парола" class="tab<?=(('pwd'==$tab)?' active':'')?>">Смяна на парола</a>
    <?php if($_SESSION['sess_user_level'] == 1) { ?>
    <a href="official_data.php?tab=accounts" title="Акаунти" class="tab<?=(('accounts'==$tab)?' active':'')?>">Акаунти</a>
    <a href="official_data.php?tab=upd" title="Поддръжка на системата" class="tab<?=(('upd'==$tab)?' active':'')?>">Поддръжка на системата</a>
    <a href="<?=$_SERVER['PHP_SELF']?>?tab=sysupdate" title="Актуализация на системата" class="tab<?=(('sysupdate'==$tab)?' active':'')?>">Актуализация на системата</a>
    <?php } ?></div>
    <script type="text/javascript">if ( (jQuery.browser.msie && jQuery.browser.version < 7)) { document.write('<br clear="all" \/>'); }</script>
    <div class="panel" style="display:block;overflow:hidden;">
      <?php
      switch ($tab) {
      	case 'lab':
      		echo echoLabs();
      		break;

      	case 'doctor_pos':
      		echo echoDoctorPos();
      		break;

      	case 'doctors':
      		echo echoDoctors();
      		break;

      	case 'stm':
      		echo echoSTM();
      		break;
      		
      	case 'sysupdate':
      		if($_SESSION['sess_user_level'] == 1) { echo echoSysUpdate(); }
      		else { echo echoPwd(); }
      		break;

      	case 'upd':
      		if($_SESSION['sess_user_level'] == 1) {
      			echo echoUpd();
      		} else {
      			echo echoFactors();
      		}
      		break;

      	case 'pwd':
      		echo echoPwd();
      		break;

      	case 'accounts':
      		if($_SESSION['sess_user_level'] == 1) {
      			echo echoAccounts();
      		} else {
      			echo echoFactors();
      		}
      		break;

      	case 'env':
      	default:
      		echo echoFactors();
      		break;
      }
      ?>
    </div>

    <?php if(isset($_SESSION['sess_bkpDB'])) { ?>
	<script type="text/javascript">
	//<![CDATA[
	window.onload = function() {
		window.location.href='<?=$_SESSION['sess_bkpDB']?>';
	}
	//]]>
	</script>
	<?php unset($_SESSION['sess_bkpDB']); } ?>

<?php include("footer.php"); ?>