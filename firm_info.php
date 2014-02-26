<?php
require ('includes.php');

$firm_id = (isset($_GET['firm_id']) && is_numeric($_GET['firm_id'])) ? intval($_GET['firm_id']) : 0;
$tab = (isset($_GET['tab']) && in_array($_GET['tab'], array('info', 'struct', 'struct_map', 'workers', 'charts', 'checkup', 'telks'))) ? $_GET['tab'] : 'info';

if(isset($_POST['ajax_action']) && !strcmp($_POST['ajax_action'], 'update_progroup')) {
	$position_name = (isset($_POST['position_name'])) ? $dbInst->checkStr($_POST['position_name']) : '';
	$progroup = (isset($_POST['progroup'])) ? intval($_POST['progroup']) : '0';
	$sql = "UPDATE `firm_positions` SET `progroup` = $progroup WHERE `firm_id` = $firm_id AND `position_name` = '$position_name'";
	$dbInst->query($sql);
	$rows = $dbInst->query("SELECT position_id FROM `firm_positions` WHERE `firm_id` = $firm_id AND `position_name` = '$position_name'");
	$out = '';
	if(!empty($rows)) {
		$IDs = array();
		foreach ($rows as $row) {
			$IDs[] = $row['position_id'];
		}
		$out .= implode(',', $IDs);
	}
	die($out);
}

// Process struct map
if(isset($_POST['btnStruct'])) {
	if(!$dbInst->hasRelation($_POST) && (($_POST['subdivision_id'] != '0' || $_POST['wplace_id'] != '0') && $_POST['position_id'] != '0')) {
		$dbInst->processMap($_POST);
	}
	header('Location: '.basename($_SERVER['PHP_SELF']).'?firm_id='.$firm_id.'&tab=struct_map');
	exit();
}

// Process struct
elseif (isset($_POST['btnSubmit'])) {
	$dbInst->setSubdivisions($_POST);
	$dbInst->setWorkPlaces($_POST);
	$dbInst->setFirmPositions($_POST);
	header('Location: '.basename($_SERVER['PHP_SELF']).'?firm_id='.$firm_id.'&tab=struct');
	exit();
}

elseif (isset($_GET['del_subdivision']) && '1' == $_GET['del_subdivision']) {
	$subdivision_id = intval($_GET['subdivision_id']);
	if($_SESSION['sess_user_level'] == 1) { /* admin rights only */
		$dbInst->removeSubdivision($subdivision_id);
	}
	header('Location: '.basename($_SERVER['PHP_SELF']).'?firm_id='.$firm_id.'&tab=struct');
	exit();
}

elseif (isset($_GET['del_position']) && '1' == $_GET['del_position']) {
	$position_id = intval($_GET['position_id']);
	if($_SESSION['sess_user_level'] == 1) { /* admin rights only */
		$dbInst->removeFirmPosition($position_id, $firm_id);
	}
	header('Location: '.basename($_SERVER['PHP_SELF']).'?firm_id='.$firm_id.'&tab=struct');
	exit();
}

elseif (isset($_GET['del_wplace']) && '1' == $_GET['del_wplace']) {
	$wplace_id = intval($_GET['wplace_id']);
	if($_SESSION['sess_user_level'] == 1) { /* admin rights only */
		$dbInst->removeWorkPlace($wplace_id, $firm_id);
	}
	header('Location: '.basename($_SERVER['PHP_SELF']).'?firm_id='.$firm_id.'&tab=struct');
	exit();
}

elseif (isset($_GET['del_relation']) && '1' == $_GET['del_relation']) {
	$map_id = intval($_GET['map_id']);
	if($_SESSION['sess_user_level'] == 1) { /* admin rights only */
		$f = $dbInst->removeRelation($map_id, $firm_id);
	}
	header('Location: '.basename($_SERVER['PHP_SELF']).'?firm_id='.$firm_id.'&tab=struct_map');
	exit();
}

$firmInfo = $dbInst->getFirmInfo($firm_id);
if (!$firmInfo) {
	header('Location:firms.php');
	exit();
}

// Xajax begin
require ('xajax/xajax_core/xajax.inc.php');
function processFirm($aFormValues)
{
	$objResponse = new xajaxResponse();

	$objResponse->assign("btnSubmit", "disabled", false);
	$objResponse->assign("btnSubmit", "value", "Съхрани");
	$objResponse->call("DisableEnableForm", false);

	if (trim($aFormValues['name']) == '') {
		$objResponse->alert("Моля, въведете наименование на фирмата.");
		return $objResponse;
	}
	if ($aFormValues['email'] != '' && !EMailIsCorrect($aFormValues['email'])) {
		$objResponse->alert("$aFormValues[email] е невалиден e-mail адрес!");
		return $objResponse;
	}
	if (!intval($aFormValues['location_id']) && trim($aFormValues['location_name']) ==
	'') {
		$objResponse->assign("location_name", "value", "");
		$objResponse->assign("location_id", "value", 0);
	}
	if (!intval($aFormValues['community_id']) && trim($aFormValues['community_name']) ==
	'') {
		$objResponse->assign("community_name", "value", "");
		$objResponse->assign("community_id", "value", 0);
	}
	if (!intval($aFormValues['province_id']) && trim($aFormValues['province_name']) ==
	'') {
		$objResponse->assign("province_name", "value", "");
		$objResponse->assign("province_id", "value", 0);
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

	global $dbInst;
	global $firm_id;
	$dbInst->processFirm($aFormValues);
	$firmInfo = $dbInst->getFirmInfo($firm_id);
	$ftitle = 'Обект: ' . $firmInfo['name'] . ' - ' . $firmInfo['location_name'] .
	', ' . $firmInfo['address'];
	$objResponse->assign("ftitle", "innerHTML", $ftitle);
	$objResponse->assign('lastModified', 'innerHTML', $dbInst->getModifiedBy('firms',
	'firm_id', $firm_id));
	//$objResponse->alert("Данните за фирмата бяха успешно актуализирани!");
	$objResponse->script("posAutocomplete();wplaceAutocomplete();");
	return $objResponse;
}
function deleteWorker($worker_id)
{
	$objResponse = new xajaxResponse();

	if($_SESSION['sess_user_level'] == 1) { /* admin rights only */
		global $dbInst;
		$dbInst->removeWorker($worker_id);
		$objResponse->script("self.parent.location.reload();");
	}

	return $objResponse;
}
function loadMap($firm_id)
{
	global $dbInst;
	$f = $dbInst->getFirmInfo($firm_id);

	$out = '<ul>';
	$out .= '<li class="lev1">' . HTMLFormat($f['name'] . ' - ' . $f['location_name']) . '</li>';
	$rows = $dbInst->getMap($firm_id);
	if ($rows) {
		$subdivision_id = -1;
		$wplace_id = -1;
		$position_id = -1;
		foreach ($rows as $row) {
			// subdivisions
			if ($row['subdivision_id'] != $subdivision_id && $row['subdivision_id']) {
				$out .= '<li class="lev2">' . HTMLFormat($row['subdivision_name']) .
				' &nbsp;&nbsp;<a href="'.basename($_SERVER['PHP_SELF']).'?del_relation=1&amp;map_id='.$row['map_id'].'&amp;firm_id='.$firm_id.'&amp;tab=struct_map" title="изтрий">[ X ]</a></li>';
				$subdivision_id = $row['subdivision_id'];
				$wplace_id = -1;
			}

			// work places
			if ($row['wplace_id'] != $wplace_id && $row['wplace_id']) {
				$out .= '<li class="lev3">' . HTMLFormat($row['wplace_name']) .
				' &nbsp;&nbsp;<a href="'.basename($_SERVER['PHP_SELF']).'?del_relation=1&amp;map_id='.$row['map_id'].'&amp;firm_id='.$firm_id.'&amp;tab=struct_map" title="изтрий">[ X ]</a></li>';
				$wplace_id = $row['wplace_id'];
			}

			// firm positions
			//if($row['position_id'] != $position_id && $row['position_id']) {
			$out .= '<li class="lev4">' . HTMLFormat($row['position_name']) .
			' &nbsp;&nbsp;<a href="'.basename($_SERVER['PHP_SELF']).'?del_relation=1&amp;map_id='.$row['map_id'].'&amp;firm_id='.$firm_id.'&amp;tab=struct_map" title="изтрий">[ X ]</a></li>';
			//$position_id = $row['position_id'];
			//}
		}
	} else {
		$out .= '<li class="lev4">Няма създадена структура.</li>';
	}
	$out .= '</ul>';

	return $out;
}
function loadWorkPlacesInSubdivision($subdivision_id, $firm_id)
{
	$objResponse = new xajaxResponse();

	global $dbInst;
	//$rows = $dbInst->getWorkPlacesInSubdivision($subdivision_id, $firm_id);
	$rows = $dbInst->getMap($firm_id);
	$arr = null;
	$out = '<select id="wplace_id" name="wplace_id" size="20" style="width:98%;height:286px;">';
	foreach ($rows as $row) {
		if ($row['subdivision_id'] == $subdivision_id)
		$arr[$row['wplace_id']] = $row['wplace_name'];
	}
	if ($arr != null) {
		$i = 0;
		foreach ($arr as $key => $value) {
			$out .= '<option value="' . $key . '"' . ((!$i++) ? ' selected="selected"' : '') .
			'>' . HTMLFormat($value) . '</option>';
		}
	}
	$out .= '</select>';
	$objResponse->assign("wplacesWrapper", "innerHTML", $out);
	$objResponse->script("posAutocomplete();wplaceAutocomplete();");

	return $objResponse;
}
$xajax = new xajax();
$xajax->registerFunction("processFirm");
$xajax->registerFunction("deleteWorker");
$xajax->registerFunction("calcContractEnd");
$xajax->registerFunction("formatBGDate");
$xajax->registerFunction("guessLocation");
$xajax->registerFunction("guessCommunity");
$xajax->registerFunction("guessProvince");
$xajax->registerFunction("loadWorkPlacesInSubdivision");
//$xajax->setFlag("debug",true);
$echoJS = $xajax->getJavascript('xajax/');
$xajax->processRequest();
// Xajax end

function echoInfo($row, $firm_id)
{
	ob_start();
	global $dbInst;
?>
      <form id="frmFirm" action="javascript:void(null);">
		<input type="hidden" id="firm_id" name="firm_id" value="<?=$firm_id?>" />
        <table cellpadding="0" cellspacing="0" class="formBg">
          <tr>
            <td colspan="4" class="leftSplit rightSplit topSplit"><div id="lastModified" class="lastModified"><?=$dbInst->getModifiedBy('firms', 'firm_id', $firm_id)?></div></td>
          </tr>
          <tr>
            <th colspan="4" class="leftSplit rightSplit">Основна информация за фирмата</th>
          </tr>
          <tr>
            <td colspan="4" class="leftSplit rightSplit"><div align="center">( Фирмата е <select id="is_active" name="is_active">
              <option value="1"<?=(('1'==$row['is_active'])?' selected="selected"':'')?>>активна &nbsp;&nbsp;</option>
              <option value="0"<?=(('0'==$row['is_active'])?' selected="selected"':'')?>>неактивна &nbsp;&nbsp;</option>
            </select> )</div></td>
          </tr>
          <tr>
            <td class="leftSplit"><strong>Наименование: </strong></td>
            <td class="rightSplit"><input type="text" id="name" name="name" value="<?=HTMLFormat($row['name'])?>" size="70" maxlength="100" tabindex="1" /></td>
            <td>Тел. 1:</td>
            <td class="rightSplit"><input type="text" id="phone1" name="phone1" value="<?=HTMLFormat($row['phone1'])?>" size="40" maxlength="50" tabindex="7" /></td>
          </tr>
          <tr>
            <td class="leftSplit">Населено място:</td>
            <td class="rightSplit"><input type="text" id="location_name" name="location_name" value="<?=HTMLFormat($row['location_name'])?>" size="70" maxlength="100" tabindex="2" onchange="xajax_guessLocation(this.value);return false;" />
              <input type="hidden" id="location_id" name="location_id" value="<?=(($row['location_id']=='')?'0':$row['location_id'])?>" /></td>
            <td>Тел. 2:</td>
            <td class="rightSplit"><input type="text" id="phone2" name="phone2" value="<?=HTMLFormat($row['phone2'])?>" size="40" maxlength="50" tabindex="8" /></td>
          </tr>
          <tr>
            <td class="leftSplit">Община:</td>
            <td class="rightSplit"><input type="text" id="community_name" name="community_name" value="<?=HTMLFormat($row['community_name'])?>" size="70" maxlength="100" tabindex="3" onchange="xajax_guessCommunity(this.value);return false;" />
              <input type="hidden" id="community_id" name="community_id" value="<?=(($row['community_id']=='')?'0':$row['community_id'])?>" /></td>
            <td>Факс:</td>
            <td class="rightSplit"><input type="text" id="fax" name="fax" value="<?=HTMLFormat($row['fax'])?>" size="40" maxlength="50" tabindex="9" /></td>
          </tr>
          <tr>
            <td class="leftSplit">Област:</td>
            <td class="rightSplit"><input type="text" id="province_name" name="province_name" value="<?=HTMLFormat($row['province_name'])?>" size="70" maxlength="100" tabindex="4" onchange="xajax_guessProvince(this.value);return false;" />
              <input type="hidden" id="province_id" name="province_id" value="<?=(($row['province_id']=='')?'0':$row['province_id'])?>" /></td>
            <td>E-mail:</td>
            <td class="rightSplit"><input type="text" id="email" name="email" value="<?=HTMLFormat($row['email'])?>" size="40" maxlength="50" tabindex="10" /></td>
          </tr>
          <tr>
            <td class="leftSplit">Адрес:</td>
            <td class="rightSplit"><input type="text" id="address" name="address" value="<?=HTMLFormat($row['address'])?>" size="70" maxlength="100" tabindex="5" /></td>
            <td>Договор рег. №: </td>
            <td class="rightSplit"><input type="text" id="contract_num" name="contract_num" value="<?=HTMLFormat($row['contract_num'])?>" size="40" maxlength="50" /></td>
          </tr>
          
          <tr>
            <td class="leftSplit">Управител:</td>
            <td class="rightSplit"><input type="text" id="FirmUpravitel" name="FirmUpravitel" value="<?=((isset($row['FirmUpravitel'])) ? HTMLFormat($row['FirmUpravitel']) : '')?>" size="70" maxlength="100" /></td>
            <td>Дата на сключване:</td>
            <td class="rightSplit"><input type="text" id="contract_begin" name="contract_begin" value="<?=HTMLFormat($row['contract_begin2'])?>" size="20" maxlength="10" onchange="xajax_calcContractEnd(this.value);return false;" onclick="scwShow(this,event);" class="date_input" />
              г. </td>
          </tr>
          <tr>
            <td class="leftSplit">МОЛ:</td>
            <td class="rightSplit"><input type="text" id="FirmMOL" name="FirmMOL" value="<?=((isset($row['FirmMOL'])) ? HTMLFormat($row['FirmMOL']) : '')?>" size="70" maxlength="100" tabindex="5" /></td>
            <td>Дата на изтичане: </td>
            <td class="rightSplit"><input type="text" id="contract_end" name="contract_end" value="<?=HTMLFormat($row['contract_end2'])?>" size="20" maxlength="10" onchange="xajax_formatBGDate(this.name,this.value);return false;" onclick="scwShow(this,event);" class="date_input" />
              г. </td>
          </tr>
          <tr>
            <td class="leftSplit">Лице за контакти:</td>
            <td class="rightSplit"><input type="text" id="FirmLice" name="FirmLice" value="<?=((isset($row['FirmLice'])) ? HTMLFormat($row['FirmLice']) : '')?>" size="70" maxlength="100" tabindex="5" /></td>
            <td rowspan="3">Бележки:</td>
            <td rowspan="3" class="rightSplit"><textarea id="notes" name="notes" rows="4" cols="36" tabindex="6"><?=HTMLFormat($row['notes'])?></textarea></td>
          </tr>
          <tr>
            <td class="leftSplit">Телефон:</td>
            <td class="rightSplit"><input type="text" id="FirmLiceTel" name="FirmLiceTel" value="<?=((isset($row['FirmLiceTel'])) ? HTMLFormat($row['FirmLiceTel']) : '')?>" size="40" maxlength="50" /></td>
          </tr>
          <tr>
            <td class="leftSplit">E-mail:</td>
            <td class="rightSplit"><input type="text" id="FirmLiceEmail" name="FirmLiceEmail" value="<?=((isset($row['FirmLiceEmail'])) ? HTMLFormat($row['FirmLiceEmail']) : '')?>" size="40" maxlength="50" /></td>
          </tr>
          <tr>
            <td colspan="4" class="leftSplit rightSplit"><p align="center">
                <input type="button" id="btnSubmit" name="btnSubmit" value="Съхрани" class="nicerButtons" onclick="xajax_processFirm(xajax.getFormValues('frmFirm'));DisableEnableForm(true);return false;" tabindex="11" />
              </p></td>
          </tr>
        </table>
      </form>
	<?php
	$buff = ob_get_contents();
	ob_end_clean();
	return $buff;
}
function echoStructForm($firm_id)
{
	global $dbInst;
	ob_start();
?>
      	<!--<form id="frmFirm" action="javascript:void(null);">-->
      	<form id="frmFirm" action="<?=basename($_SERVER['PHP_SELF'])?>?firm_id=<?=$firm_id?>&amp;tab=struct" method="post">
		  <input type="hidden" id="firm_id" name="firm_id" value="<?=$firm_id?>" />
          <table cellpadding="0" cellspacing="0" width="99%" class="formBg">
            <tr>
              <th colspan="3" class="leftSplit rightSplit topSplit"><div id="lastModified" class="lastModified"><?=$dbInst->getModifiedBy('firms', 'firm_id', $firm_id)?></div></th>
            </tr>
            <tr>
              <th class="leftSplit rightSplit">Подразделение</th>
              <th class="rightSplit">Длъжност</th>
              <th class="rightSplit">Работно място</th>
            </tr>
            <tr>
              <td valign="top" class="leftSplit rightSplit">
                  <?php
                  $subdivisions = $dbInst->getSubdivisions($firm_id);
                  foreach ($subdivisions as $subdivision) {
                  ?>
                  <input type="text" id="subdivision_name_<?= $subdivision['subdivision_id'] ?>" name="subdivision_name_<?= $subdivision['subdivision_id'] ?>" value="<?=HTMLFormat($subdivision['subdivision_name'])?>" size="36" /> <a href="<?=basename($_SERVER['PHP_SELF'])?>?del_subdivision=1&amp;firm_id=<?=$firm_id?>&amp;subdivision_id=<?=$subdivision['subdivision_id']?>" title="Изтриване"><img src="img/delete.gif" alt="delete" width="15" height="15" border="0" align="top" /></a>
                  <div class="hr"></div>
                  <?php
                  }
				  ?><input type="text" id="subdivision_name_0" name="subdivision_name_0" value="" size="40" class="newItem" /></td>
              <td valign="top" class="rightSplit">
                  <?php
                  $positions = $dbInst->getFirmPositions($firm_id);
                  foreach ($positions as $position) {
                  ?>
                  <input type="text" id="position_name_<?= $position['position_id'] ?>" name="position_name_<?= $position['position_id'] ?>" value="<?=HTMLFormat($position['position_name'])?>" size="36" /> <a href="<?=basename($_SERVER['PHP_SELF'])?>?del_position=1&amp;firm_id=<?=$firm_id?>&amp;position_id=<?=$position['position_id']?>" title="Изтриване"><img src="img/delete.gif" alt="delete" width="15" height="15" border="0" align="top" /></a>
                  <div class="br"></div>
                  Кратко описание на дейността:<div class="br"></div>
                  <textarea id="position_workcond_<?= $position['position_id'] ?>" name="position_workcond_<?= $position['position_id'] ?>" cols="42" rows="2"><?= HTMLFormat($position['position_workcond']) ?></textarea>
                  <div class="br"></div>
                  <?php
                  $progroup = '--';
                  $parent_id = 0;
                  $progroup_id = 0;
                  if(!empty($position['progroup_name'])) {
                  	$converter = new ConvertRoman($position['progroup_num']);
                  	$position['progroup_num'] = $converter->result();
                  	$progroup = '<strong>'.$position['progroup_num'].'. '.HTMLFormat($position['progroup_name']).'</strong> <a href="javascript:void(0)" onclick="updateAllProGroups('.$position['position_id'].', 0, \'--\', 0);" title="Изтриване на проф. група">(-)</a>';
                  	$parent_id = $position['parent_id'];
                  	$progroup_id = $position['progroup_id'];
                  }
                  ?>
                  <a id="lnkprogroup_<?=$position['position_id']?>" href="popup_progroups.php?firm_id=<?=$firm_id?>&parent_id=<?=$parent_id?>&progroup_id=<?=$progroup_id?>&position_id=<?=$position['position_id']?>&<?=session_name().'='.session_id()?>" title="Определяне на проф. група на '<?=HTMLFormat($position['position_name'])?>'">Професионална група:</a>
                  <span id="progroup_<?=$position['position_id']?>"><?=$progroup?></span>
                  <div class="hr"></div>
                  <?php
                  }
                  ?>
              	  <p><input type="text" id="position_name_0" name="position_name_0" value="" size="40" class="newItem" />
              	  <div class="br"></div>
                  Кратко описание на дейността:<div class="br"></div>
                  <textarea id="position_workcond_0" name="position_workcond_0" cols="42" rows="2" class="newItem"></textarea>
                </td>
              <td valign="top" class="rightSplit">
                  <?php
                  $wplaces = $dbInst->getWorkPlaces($firm_id);
                  foreach ($wplaces as $wplace) {
                  ?>
                  <input type="text" id="wplace_name_<?= $wplace['wplace_id'] ?>" name="wplace_name_<?= $wplace['wplace_id'] ?>" value="<?= HTMLFormat($wplace['wplace_name']) ?>" size="36" /> <a href="<?=basename($_SERVER['PHP_SELF'])?>?del_wplace=1&amp;firm_id=<?=$firm_id?>&amp;wplace_id=<?=$wplace['wplace_id']?>" title="Изтриване"> <img src="img/delete.gif" alt="delete" width="15" height="15" border="0" style="vertical-align: middle;" /></a>
                  <div class="br"></div>
                  Условия на труд:<div class="br"></div>
                  <textarea id="wplace_workcond_<?= $wplace['wplace_id'] ?>" name="wplace_workcond_<?= $wplace['wplace_id'] ?>" cols="42" rows="2"><?= HTMLFormat($wplace['wplace_workcond']) ?></textarea>
                  <div class="hr"></div>
                  <?php
                  }
                  ?>
                  <input type="text" id="wplace_name_0" name="wplace_name_0" value="" size="40" maxlength="50" class="newItem" />
                  <div class="br"></div>
                  Условия на труд:<div class="br"></div>
                  <textarea id="wplace_workcond_0" name="wplace_workcond_0" cols="42" rows="2" class="newItem"></textarea></td>
            </tr>
            <tr>
              <th colspan="3" class="leftSplit rightSplit"> <input type="submit" id="btnSubmit" name="btnSubmit" value="Съхрани" class="nicerButtons" />
              </th>
            </tr>
          </table>

          <h2><a href="<?=basename($_SERVER['PHP_SELF'])?>?firm_id=<?=$firm_id?>&amp;tab=struct_map">Структура на фирмата</a> </h2>
      	</form>
	<?php
	$buff = ob_get_contents();
	ob_end_clean();
	return $buff;
}
function echoStructMap($firm_id)
{
	global $dbInst;
	ob_start();
?>
          <table width="99%">
            <tr>
              <th>&nbsp;&nbsp;<a name="structmap"></a>Подразделение</th>
              <th>&nbsp;&nbsp;Работно място *</th>
              <th>&nbsp;&nbsp;Длъжност *</th>
              <th>&nbsp;</th>
            </tr>
            <tr>
              <th><select id="subdivision_id" name="subdivision_id" class="subdivision" style="width:220px;">
                <option value="0">&nbsp;&nbsp;</option>
                <?php
                $subdivisions = $dbInst->getSubdivisions($firm_id);
                foreach ($subdivisions as $subdivision) {
                ?>
                <option value="<?=$subdivision['subdivision_id']?>"><?=$subdivision['subdivision_name']?> &nbsp;&nbsp;</option>
                <?php } ?>
                </select>
              </th>
              <th><select id="wplace_id" name="wplace_id" class="wplace" style="width:220px;">
                <option value="0">&nbsp;&nbsp;</option>
                <?php
                $wplaces = $dbInst->getWorkPlaces($firm_id);
                foreach ($wplaces as $wplace) {
                ?>
                <option value="<?= $wplace['wplace_id'] ?>"><?= $wplace['wplace_name'] ?> &nbsp;&nbsp;</option>
                <?php } ?>
                </select></th>
              <th><select id="position_id" name="position_id" class="position" style="width:220px;">
                <option value="0">&nbsp;&nbsp;</option>
                <?php
                $positions = $dbInst->getFirmPositions($firm_id);
                foreach ($positions as $position) {
?>
                <option value="<?= $position['position_id'] ?>"><?= $position['position_name'] ?> &nbsp;&nbsp;</option>
                <?php } ?>
                </select>
              </th>
              <th><input type="submit" id="btnStruct" name="btnStruct" value="Съхрани" class="nicerButtons" onclick="if($('select#wplace_id').val()=='0'||$('select#position_id').val()=='0'){alert('Моля, въведете работно място и длъжност!');return false;}" />
              </th>
            </tr>
          </table>
	<?php
	$buff = ob_get_contents();
	ob_end_clean();
	return $buff;
}
function echoStruct($firm_id)
{
	ob_start();
?>
      <h2>Въвеждане на подразделения, работни места и длъжности </h2>
       <div id="sub1">
		<?php echo echoStructForm($firm_id); ?>
       </div>	<!-- sub1 end -->

	<?php
	$buff = ob_get_contents();
	ob_end_clean();
	return $buff;
}
function echoWorkers($firm_id)
{
	global $dbInst;
	global $firmInfo;

	$perPage = (isset($_GET['perPage'])) ? abs(intval($_GET['perPage'])) : 25;

	// PAGER BEGIN
	require_once 'Pager/Pager_Wrapper.php';
	$pagerOptions = array('mode' => 'Jumping', // Sliding
		'delta'   => 1000,                // 2
		'perPage' => $perPage,
		'separator'=> ' | ',
		'spacesBeforeSeparator' => 0, // number of spaces before the separator
		'spacesAfterSeparator' => 0, // number of spaces after the separator
		//'linkClass'=>'', 				// name of CSS class used for link styling
		//'curPageLinkClassName'=>'',	// name of CSS class used for current page link
		'urlVar' => 'page', // name of pageNumber URL var, for example "pageID"
		//'path'=>SECURE_URL,				// complete path to the page (without the page name)
		'firstPagePre' => '', // string used before first page number
		'firstPageText' => 'FIRST', // string used in place of first page number
		'firstPagePost' => '', // string used after first page number
		'lastPagePre' => '', // string used before last page number
		'lastPageText' => 'LAST', // string used in place of last page number
		'lastPagePost' => '', // string used after last page number
		'curPageLinkClassName' => 'current', 'prevImg' =>
		'<img src="img/pg-prev.gif" alt="prev" width="16" height="16" border="0" align="texttop" />',
		'nextImg' => '<img src="img/pg-next.gif" alt="next" width="16" height="16" border="0" align="texttop" />',
		'clearIfVoid' => true // if there's only one page, don't display pager
	);
	$firm_id = intval($firm_id);
	$query = "	SELECT w.*, strftime('%d.%m.%Y г.', w.date_retired, 'localtime') AS date_retired_h,
				f.name AS firm_name,
				l.location_name,
				s.subdivision_name,
				p.wplace_name,
				i.position_name
				FROM workers w
				LEFT JOIN firms f ON (f.firm_id = w.firm_id)
				LEFT JOIN locations l ON (l.location_id = w.location_id)
				LEFT JOIN firm_struct_map m ON (m.map_id = w.map_id )
				LEFT JOIN subdivisions s ON (s.subdivision_id = m.subdivision_id)
				LEFT JOIN work_places p ON (p.wplace_id = m.wplace_id)
				LEFT JOIN firm_positions i ON (i.position_id = m.position_id)";
	$txtCondition = " WHERE w.firm_id = $firm_id AND w.is_active = '1'";

	if (isset($_GET['btnFind'])) { // Filter workers
		if (isset($_GET['keyword']) && trim($_GET['keyword']) != '') {
			$keyword = $dbInst->checkStr(mb_strtoupper($_GET['keyword'], 'utf-8'));
			$uc_keyword = $dbInst->my_mb_ucfirst(mb_strtolower($keyword, 'utf-8'));
			$txtCondition .= (preg_match('/\bWHERE\b/', $txtCondition)) ? ' AND ' : ' WHERE ';
			$txtCondition .= "(
				w.egn LIKE '$keyword%' 
				OR w.fname LIKE '%$keyword%' 
				OR w.lname LIKE '%$keyword%' 
				OR w.fname LIKE '%$uc_keyword%' 
				OR w.lname LIKE '%$uc_keyword%'";
			// the keyword is a worker's name(s)
			if(false !== strpos($keyword, ' ')) {
				$chunks = explode(' ', $keyword);
				$tmp = array();
				foreach ($chunks as $chunk) {
					if(empty($chunk)) continue;
					$tmp[] = $chunk;
				}
				$chunks = $tmp;
				if(2 == count($chunks)) {
					$fname = trim($chunks[0]);
					$sname = $lname = trim($chunks[1]);
					$txtCondition .= " OR (w.fname LIKE '%$fname%' AND w.lname LIKE '%$lname%')";
					$txtCondition .= " OR (w.fname LIKE '%$fname%' AND w.sname LIKE '%$sname%')";
					$txtCondition .= " OR (w.fname LIKE '%".$dbInst->my_mb_ucfirst(mb_strtolower($fname, 'utf-8'))."%' AND w.lname LIKE '%".$dbInst->my_mb_ucfirst(mb_strtolower($sname, 'utf-8'))."%')";
					$txtCondition .= " OR (w.fname LIKE '%".$dbInst->my_mb_ucfirst(mb_strtolower($fname, 'utf-8'))."%' AND w.sname LIKE '%".$dbInst->my_mb_ucfirst(mb_strtolower($sname, 'utf-8'))."%')";
				}
				elseif(3 == count($chunks)) {
					$fname = trim($chunks[0]);
					$sname = trim($chunks[1]);
					$lname = trim($chunks[2]);
					$txtCondition .= " OR (w.fname LIKE '%$fname%' AND w.sname LIKE '%$sname%' AND w.lname LIKE '%$lname%')";
					$txtCondition .= " OR (w.fname LIKE '%".$dbInst->my_mb_ucfirst(mb_strtolower($fname, 'utf-8'))."%' AND w.sname LIKE '%".$dbInst->my_mb_ucfirst(mb_strtolower($sname, 'utf-8'))."%' AND w.lname LIKE '%".$dbInst->my_mb_ucfirst(mb_strtolower($lname, 'utf-8'))."%')";
				}
			}
			$txtCondition .= ")";
		}
	}
	if (isset($_GET['chkRetired'])) {
		//TODO
	} else {
		$txtCondition .= (preg_match('/\bWHERE\b/', $txtCondition)) ? ' AND ' : ' WHERE ';
		$txtCondition .= "(w.date_retired = '')";
	}
	// Search end
	$sortArr = array('fname', 'lname', 'egn', 'subdivision_name', 'position_name', 'subdiv_pos_wplace', 'subdiv_wplace_pos');
	if (isset($_GET["sort_by"]) && in_array($_GET["sort_by"], $sortArr)) {
		$order = (isset($_GET['order']) && $_GET['order'] == 'ASC') ? 'ASC' : 'DESC';
		$sort_by = $_GET['sort_by'];
		if('subdiv_pos_wplace' == $sort_by) {
			$sort_by = "s.subdivision_name $order, i.position_name $order, p.wplace_name";
		} elseif('subdiv_wplace_pos' == $sort_by) {
			$sort_by = "s.subdivision_name $order, p.wplace_name $order, i.position_name";
		}
		$txtCondition .= " ORDER BY date_retired, $sort_by $order, w.fname, w.sname, w.lname, w.egn, w.worker_id";
	} else {
		$txtCondition .= " ORDER BY date_retired, w.fname, w.sname, w.lname, w.egn, w.worker_id";
	}

	$query .= $txtCondition;
	//die($query);
	$db = $dbInst->getDBHandle();
	$paged_data = Pager_Wrapper_PDO($db, $query, $pagerOptions);
	$workers = $paged_data['data']; //paged data
	$links = $paged_data['links']; //xhtml links for page navigation
	$current = (isset($paged_data['page_numbers']['current'])) ? $paged_data['page_numbers']['current'] : 0;
	$totalItems = $paged_data['totalItems'];
	$from = ($current) ? $paged_data['from'] : 0;
	$to = $paged_data['to'];
	// PAGER END
	
	$IDs = array();
	$flds = $dbInst->query("SELECT w.worker_id ".strstr($query, 'FROM '));
	if(!empty($flds)) {
		foreach ($flds as $fld) {
			$IDs[] = $fld['worker_id'];
		}
	}
	$_SESSION['search_res_worker_ids'] = $IDs;	

	ob_start();
?>
      <form id="frmFirm" name="frmFirm" action="<?=basename($_SERVER['PHP_SELF'])?>" method="get">
		<input type="hidden" id="firm_id" name="firm_id" value="<?=$firm_id?>" />
		<input type="hidden" id="tab" name="tab" value="workers" />
        <div id="sub1" class="submenu">
          <div align="left" style="margin-left:4px;">
            <input type="hidden" id="page" name="page" value="1" />
            <table cellpadding="0" cellspacing="0" class="formBg">
              <tr>
                <td>Търсене по ЕГН или име: </td>
                  <td><input type="text" id="keyword" name="keyword" value="<?=((isset($_GET['keyword']))?HTMLFormat($_GET['keyword']):'')?>" size="35" /></td>
                  <td>&nbsp;&nbsp;&nbsp;<input type="submit" id="btnFind" name="btnFind" value="Намери" class="nicerButtons" /></td>
                  <td>| Сортиране по: <select id="sort_by" name="sort_by">
                    <option value="">- изберете - &nbsp;</option>
                    <option value="fname"<?=((isset($_GET['sort_by']) && 'fname' == $_GET['sort_by']) ? ' selected="selected"' : '')?>>Име </option>
                    <option value="sname"<?=((isset($_GET['sort_by']) && 'sname' == $_GET['sort_by']) ? ' selected="selected"' : '')?>>Презиме </option>
                    <option value="lname"<?=((isset($_GET['sort_by']) && 'lname' == $_GET['sort_by']) ? ' selected="selected"' : '')?>>Фамилия </option>
                    <option value="egn"<?=((isset($_GET['sort_by']) && 'egn' == $_GET['sort_by']) ? ' selected="selected"' : '')?>>ЕГН </option>
                    <option value="position_name"<?=((isset($_GET['sort_by']) && 'position_name' == $_GET['sort_by']) ? ' selected="selected"' : '')?>>Длъжност </option>
                    <option value="subdiv_pos_wplace"<?=((isset($_GET['sort_by']) && 'subdiv_pos_wplace' == $_GET['sort_by']) ? ' selected="selected"' : '')?>>Подразделение, длъжност, работно място </option>
                    <option value="subdiv_wplace_pos"<?=((isset($_GET['sort_by']) && 'subdiv_wplace_pos' == $_GET['sort_by']) ? ' selected="selected"' : '')?>>Подразделение, работно място, длъжност </option>
                  </select>
                  <?php if(isset($_GET["order"]) && isset($_GET['sort_by']) && !empty($_GET['sort_by'])) { ?><img src="img/<?php if ($_GET["order"] == "DESC") { ?>s_desc.png<?php } else { ?>s_asc.png<?php } ?>" alt="Sort" width="11" height="9" border="0" /><?php } ?>
                  <input type="button" id="btnSort" name="btnSort" value="Подреди" onclick="advSort();" class="nicerButtons" /></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td><input type="checkbox" id="chkRetired" name="chkRetired" value="1"<?=((isset($_GET['chkRetired']))?' checked="checked"':'')?> /> покажи и напусналите </td>
                <td colspan="2">&nbsp;</td>
              </tr>
            </table>
          </div>
          <div id="actionsdiv">
            <table width="99%" border="0">
              <tr>
                <td align="right">Резултати <?= $from ?> - <?= $to ?> от <?= $totalItems ?><?php if ($paged_data['links']) { ?> / Иди на страница <?= $paged_data['links'] ?><?php } ?></td>
              </tr>
            </table>
          </div>
          <table id="listtable">
            <tbody>
              <tr>
                <th><?php if (isset($_GET["sort_by"]) && $_GET["sort_by"] == "fname") { ?><img src="img/<?php if (isset($_GET["order"]) && $_GET["order"] == "DESC") { ?>sort_arrow_down.gif<?php } else { ?>sort_arrow_up.gif<?php } ?>" alt="Sort" width="16" height="16" border="0" /><?php } ?><a href="<?= basename($_SERVER['PHP_SELF']) . cleanQueryString('sort_by=fname&order=' . ((isset($_GET["sort_by"]) && $_GET["sort_by"] == "fname") ? (($_GET["order"] == "DESC") ? "ASC" : "DESC") : "ASC")) ?>" title="Сортиране по име">Име</a></th>
                <th><?php if (isset($_GET["sort_by"]) && $_GET["sort_by"] == "sname") { ?><img src="img/<?php if (isset($_GET["order"]) && $_GET["order"] == "DESC") { ?>sort_arrow_down.gif<?php } else { ?>sort_arrow_up.gif<?php } ?>" alt="Sort" width="16" height="16" border="0" /><?php } ?><a href="<?= basename($_SERVER['PHP_SELF']) . cleanQueryString('sort_by=sname&order=' . ((isset($_GET["sort_by"]) && $_GET["sort_by"] == "sname") ? (($_GET["order"] == "DESC") ? "ASC" : "DESC") : "ASC")) ?>" title="Сортиране по презиме">Презиме</a></th>
                <th><?php if (isset($_GET["sort_by"]) && $_GET["sort_by"] == "lname") { ?><img src="img/<?php if (isset($_GET["order"]) && $_GET["order"] == "DESC") { ?>sort_arrow_down.gif<?php } else { ?>sort_arrow_up.gif<?php } ?>" alt="Sort" width="16" height="16" border="0" /><?php } ?><a href="<?= basename($_SERVER['PHP_SELF']) . cleanQueryString('sort_by=lname&order=' . ((isset($_GET["sort_by"]) && $_GET["sort_by"] == "lname") ? (($_GET["order"] == "DESC") ? "ASC" : "DESC") : "ASC")) ?>" title="Сортиране по фамилия">Фамилия</a></th>
                <th><?php if (isset($_GET["sort_by"]) && $_GET["sort_by"] == "egn") { ?><img src="img/<?php if (isset($_GET["order"]) && $_GET["order"] == "DESC") { ?>sort_arrow_down.gif<?php } else { ?>sort_arrow_up.gif<?php } ?>" alt="Sort" width="16" height="16" border="0" /><?php } ?><a href="<?= basename($_SERVER['PHP_SELF']) . cleanQueryString('sort_by=egn&order=' . ((isset($_GET["sort_by"]) && $_GET["sort_by"] == "egn") ? (($_GET["order"] == "DESC") ? "ASC" : "DESC") : "ASC")) ?>" title="Сортиране по ЕГН">ЕГН</a></th>
                <th><?php if (isset($_GET["sort_by"]) && $_GET["sort_by"] == "position_name") { ?><img src="img/<?php if (isset($_GET["order"]) && $_GET["order"] == "DESC") { ?>sort_arrow_down.gif<?php } else { ?>sort_arrow_up.gif<?php } ?>" alt="Sort" width="16" height="16" border="0" /><?php } ?><a href="<?= basename($_SERVER['PHP_SELF']) . cleanQueryString('sort_by=position_name&order=' . ((isset($_GET["sort_by"]) && $_GET["sort_by"] == "position_name") ? (($_GET["order"] == "DESC") ? "ASC" : "DESC") : "ASC")) ?>" title="Сортиране по длъжност">Длъжност</a></th>
                <th>Предварит. <br />прегледи</th>
                <th>Болнични <br />листове</th>
                <th>Фамилна <br />анамнеза</th>
                <th>ТЕЛК</th>
                <th>Профилакт. <br />прегледи</th>
                <th>Здравно <br />досие</th>
                <th>Отвори /<br />Редактирай</th>
                <th>Пренос<br />на данни</th>
                <?php if($_SESSION['sess_user_level'] == 1) { /* admin rights only */ ?>
                <th>Изтрий</th>
                <?php } ?>
              </tr>
              <?php
              if (is_array($workers) && count($workers) > 0) {
              	$IDs = array();
              	foreach ($workers as $row) {
              		$IDs[] = $row['worker_id'];
              	}
              	$aPatientPreCheckups = array();
              	$aPatientCharts = array();
              	$aPatientTelks = array();
              	$aPatientCheckups = array();
              	if(!empty($IDs)) {
              		$sql = "SELECT worker_id, COUNT(*) AS `cnt` FROM `medical_precheckups` WHERE `worker_id` IN (".implode(',', $IDs).") GROUP BY `worker_id`";
              		$flds = $dbInst->query($sql);
              		if(!empty($flds)) {
              			foreach ($flds as $fld) {
              				$aPatientPreCheckups[$fld['worker_id']] = $fld['cnt'];
              			}
              		}
              		$sql = "SELECT worker_id, COUNT(*) AS `cnt` FROM `patient_charts` WHERE `worker_id` IN (".implode(',', $IDs).") GROUP BY `worker_id`";
              		$flds = $dbInst->query($sql);
              		if(!empty($flds)) {
              			foreach ($flds as $fld) {
              				$aPatientCharts[$fld['worker_id']] = $fld['cnt'];
              			}
              		}
              		$sql = "SELECT worker_id, COUNT(*) AS `cnt` FROM `telks` WHERE `worker_id` IN (".implode(',', $IDs).") GROUP BY `worker_id`";
              		$flds = $dbInst->query($sql);
              		if(!empty($flds)) {
              			foreach ($flds as $fld) {
              				$aPatientTelks[$fld['worker_id']] = $fld['cnt'];
              			}
              		}
              		$sql = "SELECT worker_id, COUNT(*) AS `cnt` FROM `medical_checkups` WHERE `worker_id` IN (".implode(',', $IDs).") AND checkup_date != '' GROUP BY `worker_id`";
              		$flds = $dbInst->query($sql);
              		if(!empty($flds)) {
              			foreach ($flds as $fld) {
              				$aPatientCheckups[$fld['worker_id']] = $fld['cnt'];
              			}
              		}
              	}

              	$i = 0;
              	foreach ($workers as $row) {
              		$row['precheckups_num'] = (isset($aPatientPreCheckups[$row['worker_id']])) ? $aPatientPreCheckups[$row['worker_id']] : 0;
              		$row['patient_charts_num'] = (isset($aPatientCharts[$row['worker_id']])) ? $aPatientCharts[$row['worker_id']] : 0;
              		$row['patient_telks_num'] = (isset($aPatientTelks[$row['worker_id']])) ? $aPatientTelks[$row['worker_id']] : 0;
              		$row['checkups_num'] = (isset($aPatientCheckups[$row['worker_id']])) ? $aPatientCheckups[$row['worker_id']] : 0;
					?>
              <tr>
                <td align="left"><a id="w_fname_<?=$row['worker_id']?>" href="popup_worker.php?worker_id=<?=$row['worker_id']?>&amp;firm_id=<?=$firm_id?>&amp;<?=SESS_NAME.'='.session_id()?>" title="Редактиране на данните на <?=HTMLFormat($row['fname'].' '.$row['lname'].', ЕГН '.$row['egn'])?>" class="workerinfo"><?= (($row['date_retired'] != '') ? '<img src="img/caution.gif" alt="retired" width="11" height="11" border="0" title="Напуснал на ' . $row['date_retired_h'] . '" /> ' : '') ?><?=$row['fname']?></a></td>
                <td align="left"><a id="w_sname_<?=$row['worker_id']?>" href="popup_worker.php?worker_id=<?=$row['worker_id']?>&amp;firm_id=<?=$firm_id?>&amp;<?=SESS_NAME.'='.session_id()?>" title="Редактиране на данните на <?=HTMLFormat($row['fname'].' '.$row['lname'].', ЕГН '.$row['egn'])?>" class="workerinfo"><?= $row['sname'] ?></a></td>
                <td align="left"><a id="w_lname_<?=$row['worker_id']?>" href="popup_worker.php?worker_id=<?=$row['worker_id']?>&amp;firm_id=<?=$firm_id?>&amp;<?=SESS_NAME.'='.session_id()?>" title="Редактиране на данните на <?=HTMLFormat($row['fname'].' '.$row['lname'].', ЕГН '.$row['egn'])?>" class="workerinfo"><?= $row['lname'] ?></a></td>
                <td align="left"><span id="w_egn_<?=$row['worker_id']?>"><?=$row['egn']?></span></td>
                <td align="left"><span id="w_position_name_<?=$row['worker_id']?>"><?=HTMLFormat($row['position_name'])?></span></td>
                <td align="center" nowrap="nowrap"><a href="popup_precheckup.php?worker_id=<?=$row['worker_id']?>&amp;<?=SESS_NAME.'='.session_id()?>" title="Нанасяне на резултатите от предварителен медицински преглед на <?=HTMLFormat($row['fname'].' '.$row['lname'].', ЕГН '.$row['egn'])?>" class="workerinfo"><img src="img/prchk_checkup.gif" alt="Нанасяне на резултатите от предварителен медицински преглед на <?=HTMLFormat($row['fname'].' '.$row['lname'].', ЕГН '.$row['egn'])?>" width="16" height="16" border="0" /> (<span id="w_precheckups_num_<?=$row['worker_id']?>"><?=$row['precheckups_num']?></span>)</a></td>
                <td align="center" nowrap="nowrap"><a href="popup_patient_chart.php?worker_id=<?=$row['worker_id']?>&amp;firm_id=<?=$firm_id?>&amp;<?=SESS_NAME.'='.session_id()?>" title="Болнични листове на <?=HTMLFormat($row['fname'].' '.$row['lname'].', ЕГН '.$row['egn'])?>" class="workerinfo"><img src="img/icon-cross1.gif" alt="Болнични листове на <?=HTMLFormat($row['fname'].' '.$row['lname'].', ЕГН '.$row['egn'])?>" width="16" height="16" border="0" /> (<span id="w_patient_charts_num_<?=$row['worker_id']?>"><?=$row['patient_charts_num']?></span>)</a></td>
				<td align="center"><a href="popup_anamnesis.php?worker_id=<?=$row['worker_id']?>&amp;<?=SESS_NAME.'='.session_id()?>" title="Фамилна анамнеза на <?=HTMLFormat($row['fname'].' '.$row['lname'].', ЕГН '.$row['egn'])?>" class="workerinfo"><img src="img/anamnesis.gif" alt="Фамилна анамнеза на <?=HTMLFormat($row['fname'].' '.$row['lname'].', ЕГН '.$row['egn'])?>" width="16" height="16" border="0" /></a></td>
                <td align="center" nowrap="nowrap"><a href="popup_telk.php?worker_id=<?=$row['worker_id']?>&amp;firm_id=<?=$firm_id?>&amp;<?=SESS_NAME.'='.session_id()?>" title="Експ. решения от ТЕЛК на <?=HTMLFormat($row['fname'].' '.$row['lname'].', ЕГН '.$row['egn'])?>" class="workerinfo"><img src="img/telk.gif" alt="Експ. решения от ТЕЛК на <?=HTMLFormat($row['fname'].' '.$row['lname'].', ЕГН '.$row['egn'])?>" width="16" height="16" border="0" /> (<span id="w_patient_telks_num_<?=$row['worker_id']?>"><?=$row['patient_telks_num']?></span>)</a></td>
                <td align="center" nowrap="nowrap"><a href="popup_medical_checkup.php?worker_id=<?=$row['worker_id']?>&amp;firm_id=<?=$firm_id?>&amp;<?=SESS_NAME.'='.session_id()?>" title="Профилактични прегледи на <?=HTMLFormat($row['fname'].' '.$row['lname'].', ЕГН '.$row['egn'])?>" class="workerinfo"><img src="img/checkup.gif" alt="Профилактични прегледи на <?= HTMLFormat($row['fname'].' '.$row['lname'].', ЕГН '.$row['egn'])?>" width="16" height="16" border="0" /> (<span id="w_checkups_num_<?=$row['worker_id']?>"><?=$row['checkups_num']?></span>)</a></td>
                <td align="center"><a href="w_rtf_medical_file.php?worker_id=<?=$row['worker_id']?>&amp;offline=1" title="Здравно досиена на <?=HTMLFormat($row['fname'].' '.$row['lname'].', ЕГН '.$row['egn'])?>"><img src="img/books_016.gif" width="16" height="16" alt="Здравно досиена на <?=HTMLFormat($row['fname'].' '.$row['lname'].', ЕГН '.$row['egn'])?>" border="0" /></a></td>
                <td align="center"><a href="popup_worker.php?worker_id=<?=$row['worker_id']?>&amp;firm_id=<?=$firm_id?>&amp;<?=SESS_NAME.'='.session_id()?>" title="Редактиране на данните на <?=HTMLFormat($row['fname'].' '.$row['lname'].', ЕГН '.$row['egn'])?>" class="workerinfo"><img src="img/edititem.gif" alt="Редактиране данните на <?= HTMLFormat($row['fname'].' '.$row['lname'].', ЕГН '.$row['egn'])?>" width="16" height="16" border="0" /></a></td>
                <td align="center"><a href="w_data_export.php?worker_id=<?=$row['worker_id']?>" title="Пренос на данни на работещия"><img width="16" border="0" height="16" alt="download" src="img/download2.gif" /></a></td>
				<?php if($_SESSION['sess_user_level'] == 1) { /* admin rights only */ ?>
                <td align="center"><a href="javascript:void(null);" onclick="var answ=confirm('Наистина ли искате да изтриете всички данни за <?=HTMLFormat($row['fname'].' '.$row['lname'])?> от системата?');if(answ){xajax_deleteWorker(<?=$row['worker_id']?>);}return false;" title="Изтриване на фирма"><img src="img/delete.gif" alt="Изтриване данните на <?=HTMLFormat($row['fname'].' '.$row['lname'].', ЕГН '.$row['egn'])?>" width="15" height="15" border="0" /></a></td>
				<?php } ?>
              </tr>
              <?php
              	}
              } else {
				?>
              <tr>
                <td colspan="14">Няма намерени резултати.</td>
              </tr>
              <?php } ?>
              <tr class="notover">
                <td colspan="14">&nbsp;</td>
              </tr>
              <!--<tr class="notover">
                <td colspan="7"><strong>Покажи </strong><input type="text" id="perPage" name="perPage" value="<?= $perPage ?>" size="5" maxlength="10" onKeyPress="return numbersonly(this, event);" /> <strong>работещи на страница</strong></td>
              </tr>-->
              <tr>
                <th colspan="14" align="center"><input type="button" id="btnSubmit" name="btnSubmit" value="Нов работещ" class="nicerButtons" /></th>
              </tr>
            </tbody>
          </table>
          <div id="actionsdiv">
            <table width="100%" border="0">
              <tr>
                <td align="right">Резултати <?= $from ?> - <?= $to ?> от <?= $totalItems ?><?php if ($paged_data['links']) { ?> / Иди на страница <?= $paged_data['links'] ?><?php } ?></td>
              </tr>
            </table>
          </div>
          <?php if (is_array($workers) && count($workers) > 0) { ?>
            <table cellpadding="0" cellspacing="0" width="99%" class="formBg" align="left">
              <tr>
                <td><a id="lnkWList" href="#" title="Списък на работещите"><img src="img/medical3.gif" width="16" height="16" border="0" alt="Списък" /> Списък работещи по подразделения</a> <select id="subdivision_id" name="subdivision_id">
                  <option value="0">- избери - &nbsp;&nbsp;</option>
                  <?php 
                  $rows = $dbInst->fnSelectRows(sprintf("SELECT * FROM subdivisions WHERE firm_id = %d ORDER BY subdivision_position", $firm_id));
                  if(count($rows)) {
                  	foreach ($rows as $row) {
                  		echo '<option value="'.$row['subdivision_id'].'">'.HTMLFormat($row['subdivision_name']).' &nbsp;&nbsp;</option>';
                  	}
                  }
                  ?>
                </select></td>
              </tr>
              <tr>
                <td><a id="lnkMCheckupList" href="#" title="Списък на работещите, преминали профилактичен преглед"><img src="img/medical3.gif" width="16" height="16" border="0" alt="Списък" /> Списък на преминалите профилакт. преглед </a> от <input type="text" id="date_from" name="date_from" value="" size="18" maxlength="10" onchange="xajax_formatBGDate('date_from',this.value);return false;" onclick="scwShow(this,event);" class="date_input" /> г. до <input type="text" id="date_to" name="date_to" value="<?=date("d.m.Y", time())?>" size="18" maxlength="10" onchange="xajax_formatBGDate('date_to',this.value);return false;" onclick="scwShow(this,event);" class="date_input" /> г. </td>
              </tr>
              <tr>
                <td><a id="lnkAnalysisProphylactic" href="#" title="Анализ на профилактичните прегледи"><img src="img/medical3.gif" width="16" height="16" border="0" alt="Списък" /> Анализ на профилактичните прегледи </a> от <input type="text" id="date_from4" name="date_from4" value="" size="18" maxlength="10" onchange="xajax_formatBGDate('date_from4',this.value);return false;" onclick="scwShow(this,event);" class="date_input" /> г. до <input type="text" id="date_to4" name="date_to4" value="<?=date("d.m.Y", time())?>" size="18" maxlength="10" onchange="xajax_formatBGDate('date_to4',this.value);return false;" onclick="scwShow(this,event);" class="date_input" /> г. </td>
              </tr>
              <tr>
                <td><a id="lnkZVN" href="#" title="Справка ЗВН"><img src="img/medical3.gif" width="16" height="16" border="0" alt="Списък" /> Справка ЗВН за периода</a> от <input type="text" id="date_from2" name="date_from2" value="" size="18" maxlength="10" onchange="xajax_formatBGDate('date_from2',this.value);return false;" onclick="scwShow(this,event);" class="date_input" /> г. до <input type="text" id="date_to2" name="date_to2" value="<?=date("d.m.Y", time())?>" size="18" maxlength="10" onchange="xajax_formatBGDate('date_to2',this.value);return false;" onclick="scwShow(this,event);" class="date_input" /> г.</td>
              </tr>
              <tr>
                <td><a id="lnkMChkup" href="#" title="Справка болнични листове"><img src="img/medical3.gif" width="16" height="16" border="0" alt="Списък" /> Справка болнични листове за периода</a> от <input type="text" id="date_from3" name="date_from3" value="" size="18" maxlength="10" onchange="xajax_formatBGDate('date_from3',this.value);return false;" onclick="scwShow(this,event);" class="date_input" /> г. до <input type="text" id="date_to3" name="date_to3" value="<?=date("d.m.Y", time())?>" size="18" maxlength="10" onchange="xajax_formatBGDate('date_to3',this.value);return false;" onclick="scwShow(this,event);" class="date_input" /> г. &nbsp;&nbsp;&nbsp;<input type="checkbox" id="sickonly" name="sickonly" value="1" checked="checked" /> покажи само работещите с болнични</td>
              </tr>
              <tr>
                <td><a id="lnkHealthStatus" href="#" title="Здравен статус на работещите"><img src="img/medical3.gif" width="16" height="16" border="0" alt="Списък" /> Справка здравен статус на работещите</a></td>
              </tr>
              <tr>
                <td><?php
                $ownersPerExport = 200;
                $cnt = $dbInst->fnCountRow('workers', "firm_id = $firm_id");
                if($cnt > $ownersPerExport) {
                ?><a href="javascript:void(0);" onclick="window.location.href='w_data_export.php?firm_id=<?=$firm_id?>&page='+$('#archiveParts').val()+'&limit=<?=$ownersPerExport?>';" title="Пренос на данни на фирмата"><img width="16" border="0" height="16" alt="download" src="img/download2.gif" /> Пренос на данни на фирмата</a> <select id="archiveParts" name="archiveParts">
                  <?php
                  for ($i = 1, $j = 1; $i <= $cnt; $i += $ownersPerExport, $j++) { echo '<option value="'.$j.'">Том '.$j.' &nbsp;</option>'; }
               	  ?>
                </select> <em>(по не повече от <?=$ownersPerExport?> работещи на том)</em>
                <?php } else { ?>
                <a href="w_data_export.php?firm_id=<?=$firm_id?>" title="Пренос на данни на фирмата"><img width="16" border="0" height="16" alt="download" src="img/download2.gif" /> Пренос на данни на фирмата</a>
                <?php } ?></td>
              </tr>
              <tr>
                <td><a class="workerinfo" href="transfer_medical_info.php?firm_id=<?=$firm_id?>&<?=SESS_NAME.'='.session_id()?>"><img width="16" border="0" height="16" alt="download" src="img/download2.gif" title="Прехвърляне на медицинска информация от други фирми" /> Прехвърляне на медицинска информация от други фирми</a></td>
              </tr>
            </table>
            <script type="text/javascript">
            if ( jQuery.browser.msie && jQuery.browser.version < 7 ) {
            	document.write('<hr \/>');
            }
            </script>
          <?php } ?>          
        </div>
      </form>
      <br clear="all" />
      <script type="text/javascript">
      //<![CDATA[
      function validate(form) {
      	$('#waitMsgHolder').show();
      	$('#btnImport').hide();
      	if(form.chkRemoveExisting.checked) {
      		if(!confirm('Сигурни ли сте, че преди въвеждане на новите работещи, искате да изтриете всички работещи на фирмата, въведени през последния 1 час?')) {
      			$('#waitMsgHolder').hide();
      			$('#btnImport').show();
      			return false;
      		}
      	}
      	$('#preLoader').show();
      	return true;
      }
      //]]>
      </script>
      <h2>Въвеждане на работещи от Excel файл</h2>
      <?php if('' != ($msg = getFlash())) { ?><div class="err"><?=$msg?></div><?php } ?>
      <a name="xlsanc" id="xlsanc"></a>
      <form id="frmImport" method="post" action="xl_import.php<?=((isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) ? '?'.$_SERVER['QUERY_STRING'] : '')?>#xlsanc" enctype="multipart/form-data" onsubmit="return validate(this)">
        <p>Избери файл:
          <input type="file" id="datafile" name="datafile" />
          <button type="submit" id="btnImport" name="btnImport"><img width="12" height="12" border="0" align="top" alt="MSExcel" src="img/excel_icon.gif" /> Въведи</button>
          <span id="waitMsgHolder" style="display:none;">Моля, изчакайте...</span> </p>
        <p>
          <!--<input type="checkbox" id="chkRemoveExisting" name="chkRemoveExisting" value="1" />
          Изтрий всички работещи, въведени през последния 1 час.-->&nbsp;</p>
        <p><img src="img/obrazec_personal.png" alt="Образец на Excel файла" border="0" width="800" height="150" /></p>
        <p><a href="obrazec_personal.xls" title="Свали образец на Excel файла">Свали образец на Excel файла</a></p>
      </form>
	<?php
	$buff = ob_get_contents();
	ob_end_clean();
	return $buff;
}
function echoCharts($firm_id = 0) {
	ob_start();
?>
<h2>Въвеждане на болнични на работещи</h2>
<iframe src="popup_patient_chart.php?firm_id=<?=$firm_id?>" width="810" height="450" scrolling="no" frameborder="0"><!-- Alternate content for non-supporting browsers --></iframe>
<script type="text/javascript">
//<![CDATA[
function validate(form) {
	$('#waitMsgHolder').show();
	$('#btnImport').hide();
	if(form.chkRemoveExisting.checked) {
		if(!confirm('Сигурни ли сте, че преди въвеждане на новите болнични листове, искате да изтриете всички болнични на фирмата, въведени през последния 1 час?')) {
			$('#waitMsgHolder').hide();
			$('#btnImport').show();
			return false;
		}
	}
	return true;
}
//]]>
</script>
<h2>Въвеждане на болнични от Excel файл</h2>
<?php if('' != ($msg = getFlash())) { ?><div class="err"><?=$msg?></div><?php } ?>
<a name="xlsanc"></a>
<form id="frmImport" method="post" action="xl_import.php<?=((isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) ? '?'.$_SERVER['QUERY_STRING'] : '')?>#xlsanc" enctype="multipart/form-data" onsubmit="return validate(this)">
  <p>Избери файл:
    <input type="file" id="datafile" name="datafile" />
    <button type="submit" id="btnImport" name="btnImport"><img width="12" height="12" border="0" align="top" alt="MSExcel" src="img/excel_icon.gif" /> Въведи</button><span id="waitMsgHolder" style="display:none;">Моля, изчакайте...</span>
  </p>
  <p><!--<input type="checkbox" id="chkRemoveExisting" name="chkRemoveExisting" value="1" /> Изтрий всички болнични, въведени през последния 1 час.-->&nbsp;</p>
  <p><img src="img/obrazec_bolnichni.png" alt="Образец на Excel файла" border="0" width="800" height="150" /></p>
  <p><a href="obrazec_bolnichni.xls" title="Свали образец на Excel файла">Свали образец на Excel файла</a></p>
</form>
<?php
$buff = ob_get_contents();
ob_end_clean();
return $buff;
}
function echoTelks($firm_id)
{
	ob_start();
?>
<h2>Въвеждане на ТЕЛК експертни решения</h2>
<iframe src="popup_telk.php?firm_id=<?=$firm_id?>" width="770" height="580" scrolling="no" frameborder="0"><!-- Alternate content for non-supporting browsers --></iframe>
<?php
$buff = ob_get_contents();
ob_end_clean();
return $buff;
}
function echoCheckup($firm_id)
{
	global $dbInst;
	ob_start();
?>
      <form id="frmFirm" action="javascript:void(null);">
        <input type="hidden" id="firm_id" name="firm_id" value="<?=$firm_id?>" />
        <div id="sub1" class="submenu">
          <table cellpadding="0" cellspacing="0" class="formBg">
            <tr>
              <th width="33%" class="leftSplit rightSplit topSplit">Подразделение</th>
              <th width="33%" class="rightSplit topSplit">Работно място</th>
              <th width="34%" valign="top" rowspan="2" class="rightSplit topSplit"> <h2>Работни места</h2>
                <a href="javascript:void(null);" onclick="openWorkEnvFactors();return false;" title="Въвеждане на фактори на работната среда за работно място">Фактори на работната среда за работно място</a>
                <div class="hr"></div>
                <a href="popup_work_env_group.php?firm_id=<?=$firm_id?>&amp;<?=SESS_NAME.'='.session_id()?>" title="Въвеждане на фактори на работната среда за група работни места" class="workerinfo">Фактори на работната среда за група работни места</a>
                <div class="hr"></div>
                <h2>Работещи</h2>
                <a href="javascript:void(null);" onclick="openGiveMCards();return false;" title="Карти за профилактични прегледи на работещи във фирмата">Карти за профилактични прегледи</a>
                <div class="hr"></div></th>
            </tr>
            <tr>
              <td valign="top" class="leftSplit rightSplit">
                  <select id="subdivision_id" name="subdivision_id" size="20" style="width:98%;height:286px;" onchange="xajax_loadWorkPlacesInSubdivision(this.value, <?=$firm_id?>);return false;">
                  	<?php
                  	$f = $dbInst->getFirmInfo($firm_id);
                  	echo '<option value="0" selected="selected">' . HTMLFormat($f['name']) .
                  	'</option>';
                  	$rows = $dbInst->getMap($firm_id);
                  	$arr = null;
                  	foreach ($rows as $row) {
                  		if ($row['subdivision_id'])
                  		$arr[$row['subdivision_id']] = $row['subdivision_name'];
                  	}
                  	if ($arr != null) {
                  		foreach ($arr as $key => $value)
                  		echo '<option value="' . $key . '">' . HTMLFormat($value) . '</option>';
                  	}
                  	?>
                  </select></td>
              <td valign="top" class="rightSplit"><div id="wplacesWrapper"><script type="text/javascript">
              xajax_loadWorkPlacesInSubdivision(0, <?=$firm_id?>);
              </script></div></td>
            </tr>
          </table>
        </div>
      </form>
	<?php
	$buff = ob_get_contents();
	ob_end_clean();
	return $buff;
}

function showStructMap($firm_id) {
	global $dbInst;
	ob_start();
	?>
      <form id="frmFirm" action="<?=basename($_SERVER['PHP_SELF'])?>?firm_id=<?=$firm_id?>&amp;tab=struct_map" method="post">
        <input type="hidden" id="firm_id" name="firm_id" value="<?=$firm_id?>" />
        <h2><a href="<?=basename($_SERVER['PHP_SELF'])?>?firm_id=<?=$firm_id?>&amp;tab=struct">Въвеждане на подразделения, работни места и длъжности </a></h2>
        <h2>Структура на фирмата</h2>
        <div id="struct_map">
          <table width="99%">
            <tr>
              <th>&nbsp;&nbsp;<a name="structmap" id="structmap"></a>Подразделение</th>
              <th>&nbsp;&nbsp;Работно място *</th>
              <th>&nbsp;&nbsp;Длъжност *</th>
              <th>&nbsp;</th>
            </tr>
            <tr>
              <th><select id="subdivision_id" name="subdivision_id" class="subdivision" style="width:220px;">
                  <option value="0">&nbsp;&nbsp;</option>
                  <?php
                  $subdivisions = $dbInst->getSubdivisions($firm_id);
                  foreach ($subdivisions as $subdivision) {
                  ?>
                  <option value="<?=$subdivision['subdivision_id']?>"><?=HTMLFormat($subdivision['subdivision_name'])?> &nbsp;&nbsp;</option>
                  <?php } ?>
                </select>
              </th>
              <th><select id="wplace_id" name="wplace_id" class="wplace" style="width:220px;">
                  <option value="0">&nbsp;&nbsp;</option>
                  <?php
                  $wplaces = $dbInst->getWorkPlaces($firm_id);
                  foreach ($wplaces as $wplace) {
                  ?>
                  <option value="<?= $wplace['wplace_id'] ?>"><?=HTMLFormat($wplace['wplace_name'])?> &nbsp;&nbsp;</option>
                  <?php } ?>
                </select></th>
              <th><select id="position_id" name="position_id" class="position" style="width:220px;">
                  <option value="0">&nbsp;&nbsp;</option>
                  <?php
                  $positions = $dbInst->getFirmPositions($firm_id);
                  foreach ($positions as $position) {
                  ?>
                  <option value="<?= $position['position_id'] ?>"><?=HTMLFormat($position['position_name'])?> &nbsp;&nbsp;</option>
                  <?php } ?>
                </select>
              </th>
              <th><input type="submit" id="btnStruct" name="btnStruct" value="Съхрани" class="nicerButtons" onclick="if($('select#wplace_id').val()=='0'||$('select#position_id').val()=='0'){alert('Моля, въведете работно място и длъжност!');return false;}" />
              </th>
            </tr>
          </table>
        </div>
        <div class="clear"></div>
        <div id="mapWrapper">
          <?php echo loadMap($firm_id); ?>
        </div>
      </form>
	<?php
	$buff = ob_get_contents();
	ob_end_clean();
	return $buff;
}

if ($tab == 'info') {
	$echoJS .= <<< EOT
<!-- Auto-completer begin -->
<!-- http://dev.jquery.com/view/trunk/plugins/autocomplete/ -->
<!-- <script type="text/javascript" src="js/autocompleter/jquery.js"></script> -->
<script type='text/javascript' src='js/autocompleter/jquery.bgiframe.min.js'></script>
<script type='text/javascript' src='js/autocompleter/jquery.dimensions.js'></script>
<script type='text/javascript' src='js/autocompleter/jquery.ajaxQueue.js'></script>
<script type='text/javascript' src='js/autocompleter/jquery.autocomplete.js'></script>
<script type='text/javascript' src='js/autocompleter/localdata.js'></script>
<!-- <link rel="stylesheet" type="text/css" href="js/autocompleter/main.css" /> -->
<link rel="stylesheet" type="text/css" href="js/autocompleter/jquery.autocomplete.css" />
<script type="text/javascript">
//<![CDATA[
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
//]]>
</script>
<!-- Auto-completer end -->
EOT;

} elseif ($tab == 'workers') {
	ob_start();
	?>
<script type="text/javascript">
//<![CDATA[
$(document).ready(function(){
	$("a.workerinfo").each(function(i){
		var win_title = $(this).attr('title');
		$(this).colorbox({width:"98%", height:"100%", iframe:true, overlayClose:false, title:win_title, transition:"none", fastIframe:false, fixed:true });
	});
	$("#btnSubmit").click(function(e){
		e.preventDefault();
		$(this).colorbox({width:"98%", height:"100%", iframe:true, overlayClose:false, title:"Добавяне на нов работещ", transition:"none", fastIframe:false, href:"popup_worker.php?worker_id=0&firm_id=<?=$firm_id?>&<?=SESS_NAME.'='.session_id()?>", fixed:true });
	});
});
//]]>
</script>
<script type="text/javascript">
//<![CDATA[
$(document).ready(function() {
	stripTable('listtable');
	$("#lnkWList").click(function(){
		window.location = 'w_workers_list.php?firm_id=<?=$firm_id?>&subdivision_id=' + $("#subdivision_id").val() + '&<?=SESS_NAME.'='.session_id()?>';
		return false;
	});
	$("#lnkMCheckupList").click(function(){
		window.location = 'w_workers_checkups_list.php?firm_id=<?=$firm_id?>&date_from=' + $("#date_from").val() + '&date_to=' + $("#date_to").val() + '&<?=SESS_NAME.'='.session_id()?>';
		return false;
	});
	$("#lnkAnalysisProphylactic").click(function(){
		window.location = 'w_rtf_analysis_prophylactic.php?firm_id=<?=$firm_id?>&date_from=' + $("#date_from4").val() + '&date_to=' + $("#date_to4").val() + '&<?=SESS_NAME.'='.session_id()?>';
		return false;
	});
	$("#lnkZVN").click(function(){
		window.location = 'w_zvn.php?firm_id=<?=$firm_id?>&date_from=' + $("#date_from2").val() + '&date_to=' + $("#date_to2").val() + '&<?=SESS_NAME.'='.session_id()?>';
		return false;
	});
	$("#lnkMChkup").click(function(){
		var sickonly = ($('input#sickonly').get(0).checked) ? 1 : 0;
		window.location = 'w_mchkup.php?firm_id=<?=$firm_id?>&date_from=' + $("#date_from3").val() + '&date_to=' + $("#date_to3").val() + '&sickonly=' + sickonly + '&<?=SESS_NAME.'='.session_id()?>';
		return false;
	});
	$("#lnkHealthStatus").click(function(){
		window.location = 'xl_health_status.php?firm_id=<?=$firm_id?>&<?=SESS_NAME.'='.session_id()?>';
		return false;
	});
});
function advSort() {
	var sort_by = $('#sort_by').val();
	var url = new String(window.location);

	var newQS = '';
	var baseURL = url;
	if(url.indexOf("?") !== -1){ //if there is a query string involved
		baseURL = url.substr(0, url.indexOf("?"));

		var QS = url.substr(url.indexOf("?") + 1, url.length);

		var hit = 0;
		var pairs = QS.split('&');
		for(var i = 0; i < pairs.length; i++) {
			var params = pairs[i].split('=');
			if(params[0] == 'sort_by') {
				params[1] = sort_by;
				hit = 1;
			} else if(params[0] == 'order') {
				params[1] = (params[1] == 'ASC') ? 'DESC' : 'ASC';
			}
			newQS += params[0] + '=' + params[1] + '&'
		}
		newQS = '?' + newQS.substr(0, newQS.length - 1);
		if(!hit) newQS += '&sort_by=' + sort_by + '&order=ASC';
	}
	window.location = baseURL + newQS;
}
//]]>
</script>
	<?php
	$echoJS .= ob_get_clean();
} elseif ($tab == 'checkup') {
	ob_start();
	?>
<script type="text/javascript">
//<![CDATA[
$(document).ready(function(){
	$("a.workerinfo").each(function(i){
		var win_title = $(this).attr('title');
		$(this).colorbox({width:"98%", height:"100%", iframe:true, overlayClose:false, title:win_title, transition:"none", fastIframe:false, fixed:true });
	});
});
function openWorkEnvFactors() {
	if($("#wplace_id").val() == 0 || !$("#wplace_id").val()) return false;
	$.colorbox({width:"98%", height:"100%", iframe:true, overlayClose:false, title:"Фактори на работното място", transition:"none", fastIframe:false, href:"popup_work_env_protocols.php?firm_id=" + $("#firm_id").val() + "&subdivision_id=" + $("#subdivision_id").val() + "&wplace_id=" + $("#wplace_id").val() + "&<?=SESS_NAME.'='.session_id()?>", fixed:true });
	return false;
}
function openGiveMCards() {
	if($("#wplace_id").val() == 0 || !$("#wplace_id").val()) return false;
	$.colorbox({width:"98%", height:"100%", iframe:true, overlayClose:false, title:"Карти за профилактични прегледи на работещи във фирмата", transition:"none", fastIframe:false, href:"popup_give_mcards.php?firm_id=" + $("#firm_id").val() + "&subdivision_id=" + $("#subdivision_id").val() + "&wplace_id=" + $("#wplace_id").val() + "&<?=SESS_NAME.'='.session_id()?>", fixed:true });
	return false;
}
//]]>
</script>
	<?php
	$echoJS .= ob_get_clean();

} elseif ($tab == 'struct') {
	ob_start();
	?>
<script type="text/javascript">
//<![CDATA[
$(document).ready(function(){
	$("a[id^='lnkprogroup_']").each(function(i){
		var win_title = $(this).attr('title');
		$(this).colorbox({width:"80%", height:"80%", iframe:true, overlayClose:false, title:win_title, transition:"none", fastIframe:false, fixed:true });
	});
});
//]]>
</script>
<script type="text/javascript">
//<![CDATA[
/*  Based on: http://www.esqsoft.com/javascript-help/how-to-select-html-input-and-copy-to-clipboard.htm
Programming Notes:
1. attaching the function to a known object is a practice that allows
us to test for the availability of an interface before calling it
as a method and inadvertantly triggering a JavaScript error.
2. by including the code as a function (instead of within the onfocus
event of each caller for example) we can reuse the same code for many inputs.
This is further facilitated by relying on rSource being passed as
a reference to the "this" context of the calling object.
*/

window.fCopyToClipboard = function(rSource){
	rSource.select()
	if(window.clipboardData){ var r=clipboardData.setData('Text',rSource.value); return 1; }
	else return 0
}
// Copy text to clipboard
// Based on: http://www.jeffothy.com/weblog/clipboard-copy/
function fnCopy(inElement) {
	if (inElement.createTextRange) {
		var range = inElement.createTextRange();
		//if (range && BodyLoaded==1)
		if (range)
		range.execCommand('Copy');
	} else {
		var flashcopier = 'flashcopier';
		if(!document.getElementById(flashcopier)) {
			var divholder = document.createElement('div');
			divholder.id = flashcopier;
			document.body.appendChild(divholder);
		}
		document.getElementById(flashcopier).innerHTML = '';
		var divinfo = '<embed src="_clipboard.swf" FlashVars="clipboard='+encodeURIComponent(inElement.value)+'" width="0" height="0" type="application/x-shockwave-flash"><\/embed>';
		document.getElementById(flashcopier).innerHTML = divinfo;
	}
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
	/*$(":text, textarea").result(findValueCallback).next().click(function() {
	$(this).prev().search();
	});*/

	posAutocomplete();
	wplaceAutocomplete();
});
function updateAllProGroups(position_id, progroup, progroup_lbl, parent_id) {
	var position_name = $('#position_name_' + position_id).val();
	$.post('<?=basename($_SERVER['PHP_SELF'])?>?firm_id=<?=$firm_id?>&tab=struct',
	{ 'ajax_action': 'update_progroup', 'position_name': position_name, 'progroup': progroup },
	function(data, textStatus){
		if(textStatus == 'success') {
			if(data != '') {
				var IDs = data.split(',');
				for(var i = 0; i < IDs.length; i++) {
					$("#progroup_" + IDs[i]).html(progroup_lbl);
					$("#lnkprogroup_" + IDs[i]).attr("href", "popup_progroups.php?firm_id=<?=$firm_id?>&parent_id=" + parent_id + "&progroup_id=" + progroup + "&position_id=" + position_id + "&<?=session_name().'='.session_id()?>");
				}
			}
		}
	}
	);
}
function posAutocomplete() {
	$("input[name^='position_name_']").autocomplete("autocompleter.php", {
		minChars: 1,
		extraParams: { search: "position_name" },
		width: 230,
		/*max: 4,*/
		/*highlight: false,*/
		scroll: true,
		scrollHeight: 250,
		selectFirst: false,
		formatItem: function(data, i, n, value) {
			var position_name = data[0];
			var position_workcond = data[1];
			return position_name;
		}
	});
	$("input[name^='position_name_']").result(function(event, data, formatted) {
		if (data) {
			var id = this.name.slice(14);
			$("#position_name_"+id).val(data[0]);
			$("#position_workcond_"+id).val(data[1]);

		}
	});
}
function wplaceAutocomplete() {
	$("input[name^='wplace_name_']").autocomplete("autocompleter.php", {
		minChars: 1,
		extraParams: { search: "wplace_name" },
		width: 230,
		/*max: 4,*/
		/*highlight: false,*/
		scroll: true,
		scrollHeight: 250,
		selectFirst: false,
		formatItem: function(data, i, n, value) {
			var wplace_name = data[0];
			var wplace_workcond = data[1];
			return wplace_name;
		}
	});
	$("input[name^='wplace_name_']").result(function(event, data, formatted) {
		if (data) {
			var id = this.name.slice(12);
			$("#wplace_name_"+id).val(data[0]);
			$("#wplace_workcond_"+id).val(data[1]);

		}
	});
}
// Auto-completer end
//]]>
</script>
	<?php
	$echoJS .= ob_get_clean();
}

include ("header.php");
?>
    <div class="wtitle">Обект: <?=HTMLFormat($firmInfo['name'].' - '.$firmInfo['location_name'].', '.$firmInfo['address'])?></div>
    <div id="tabs"> <a href="<?=basename($_SERVER['PHP_SELF'])?>?firm_id=<?=$firm_id?>&amp;tab=info" title="Информация за фирмата" class="tab<?=(($tab=='info')?' active':'')?>">Информация за фирмата</a> <a href="<?=basename($_SERVER['PHP_SELF'])?>?firm_id=<?=$firm_id?>&amp;tab=struct" title="Структура и длъжности" class="tab<?=((in_array($tab, array('struct','struct_map')))?' active':'')?>">Структура и длъжности</a> <a href="<?=basename($_SERVER['PHP_SELF'])?>?firm_id=<?=$firm_id?>&amp;tab=workers" class="tab<?=(($tab=='workers')?' active':'')?>">Работещи</a> <a href="<?=basename($_SERVER['PHP_SELF'])?>?firm_id=<?=$firm_id?>&amp;tab=charts" class="tab<?=(($tab=='charts')?' active':'')?>">Болнични</a> <a href="<?=basename($_SERVER['PHP_SELF'])?>?firm_id=<?=$firm_id?>&amp;tab=telks" class="tab<?=(($tab=='telks')?' active':'')?>">ТЕЛК</a> <a href="<?=basename($_SERVER['PHP_SELF'])?>?firm_id=<?=$firm_id?>&amp;tab=checkup" class="tab<?=(($tab=='checkup')?' active':'')?>">Измервания и прегледи</a> <a href="firms.php<?=((isset($_SESSION['sess_QUERY_STRING'])&&''!=$_SESSION['sess_QUERY_STRING'])?'?'.$_SESSION['sess_QUERY_STRING']:'')?>" title="Обратно към списък на фирмите" class="tab">&laquo; Обратно</a></div>
    <script type="text/javascript">if ( (jQuery.browser.msie && jQuery.browser.version < 7)) { document.write('<br clear="all" \/>'); }</script>
    <div class="panel" style="display:block;<?=(('workers'==$tab)?'overflow:hidden;':'')?>">
      <?php
      switch ($tab) {
      	case 'struct':
      		echo echoStruct($firm_id);
      		break;
      	case 'struct_map':
      		echo showStructMap($firm_id);
      		break;
      	case 'workers':
      		echo echoWorkers($firm_id);
      		break;
      	case 'charts':
      		echo echoCharts($firm_id);
      		break;
      	case 'telks':
      		echo echoTelks($firm_id);
      		break;
      	case 'checkup':
      		echo echoCheckup($firm_id);
      		break;
      	case 'info':
      	default:
      		echo echoInfo($firmInfo, $firm_id);
      		break;
      }
      ?>
    </div>

<?php include ("footer.php"); ?>