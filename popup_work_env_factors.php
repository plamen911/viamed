<?php
require('includes.php');

$firm_id = (isset($_GET['firm_id']) && is_numeric($_GET['firm_id'])) ? intval($_GET['firm_id']) : 0;
if(!$firm_id) {
	die('Липсва индентификатор на фирмата!');
}
$wplace_id = (isset($_GET['wplace_id']) && is_numeric($_GET['wplace_id'])) ? intval($_GET['wplace_id']) : 0;
if(!$wplace_id) {
	die('Липсва индентификатор на работното място!');
}

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
	var parent = (window.opener) ? window.opener : self.parent;
	parent.document.getElementById('TB_ajaxWindowTitle').style.fontWeight = 'bold';
});
function calcDeviation(el) {
	var arr = el.id.split("_");
	var level = parseFloat(el.value);
	var pdk_min = parseFloat($("#pdk_min_"+arr[1]).val());
	var pdk_max = parseFloat($("#pdk_max_"+arr[1]).val());
	if(level >= pdk_min && level <= pdk_max) {
		$("#deviation_"+arr[1]).val(0);
	}
	else if(level < pdk_min) {
		$("#deviation_"+arr[1]).val(pdk_min - level);
	}
	else if(level > pdk_max) {
		$("#deviation_"+arr[1]).val(level - pdk_max);
	}
	else {
		$("#deviation_"+arr[1]).val(0);
	}
	return false;
}
//]]>
</script>
</head>
<body>
<div align="center">
  <div id="content">
    <div id="contentinner">
      <form id="frmFirm" action="javascript:void(null);">
        <input type="hidden" id="worker_id" name="worker_id" value="0" />
        <table border="0" cellpadding="0" cellspacing="0" id="grayTable" width="770">
          <tr>
            <td><p><span class="labeltext">Фирма:</span> &quot;Автосвят&quot; ООД</p></td>
          </tr>
          <tr>
            <td><p><span class="labeltext">Подразделение:</span> &quot;Автосвят&quot; ООД </p></td>
          </tr>
          <tr>
            <td class="primary"><p><span class="labeltext"><strong>Работно място:</strong></span> <strong>Офис</strong> </p></td>
          </tr>
          <tr>
            <th colspan="4"><p align="center">
                <input type="button" id="btnSubmit" name="btnSubmit" value="Съхрани" class="nicerButtons" />
              </p></th>
          </tr>
        </table>
        <div id="factorsWrapper" style="width:765px;height:300px;overflow:scroll;">
          <table id="listtable">
            <tbody>
              <tr>
                <th>Фактор</th>
                <th>Ниво</th>
                <th>min ПДК</th>
                <th>max ПДК</th>
                <th>МЕ</th>
                <th>Отклонение</th>
                <th>&nbsp;</th>
              </tr>
              <!-- line begin -->
              <tr>
                <td><select id="factor_id_1" name="factor_id_1" style="width:98%;">
                    <option value="1">Мед &nbsp;&nbsp;</option>
                    <option value="2">Микроклимат &nbsp;&nbsp;</option>
                    <option value="3">Осветление &nbsp;&nbsp;</option>
                  </select></td>
                <td><input type="text" id="level_1" name="level_1" value="" style="width:98%;" onchange="calcDeviation(this);" /></td>
                <td><input type="text" id="pdk_min_1" name="pdk_min_1" value="100" style="width:98%;" readonly="readonly" /></td>
                <td><input type="text" id="pdk_max_1" name="pdk_max_1" value="" style="width:98%;" readonly="readonly" /></td>
                <td><input type="text" id="factor_dimension_1" name="factor_dimension_1" value="lux" style="width:98%;" readonly="readonly" /></td>
                <td><input type="text" id="deviation_1" name="deviation_1" value="" style="width:98%;" /></td>
                <td><a href="javascript:void(null);" onclick="var answ=confirm('Наистина ли искате да изтриете протокола?');if(answ){xajax_removeProtokol();}return false;" title="Изтриване на протокола"><img src="img/delete.gif" border="0" width="15" height="15" alt="Изтриване на протокола" /></a></td>
              </tr>
              <tr>
                <td>&nbsp;</td>
                <td colspan="6"><strong>Протокол №:</strong>
                  <input type="text" id="prot_1" name="prot_1" value="" />
                  <strong>от дата:</strong>
                  <input type="text" id="prot_date_1" name="prot_date_1" value="" /></td>
              </tr>
              <!-- line end -->
              <tr class="alternate">
                <td><select name="select" id="factor_id_1" style="width:98%;">
                    <option value="1">Мед &nbsp;&nbsp;</option>
                    <option value="2">Микроклимат &nbsp;&nbsp;</option>
                    <option value="3">Осветление Осветление Осветление &nbsp;&nbsp;</option>
                  </select></td>
                <td><input type="text" id="level_1" name="level_1" value="" style="width:98%;" /></td>
                <td><input type="text" id="level_2" name="level_2" value="100" style="width:98%;" /></td>
                <td><input type="text" id="level_3" name="level_3" value="" style="width:98%;" /></td>
                <td><input type="text" id="level_4" name="level_4" value="lux" style="width:98%;" /></td>
                <td><input type="text" id="level_5" name="level_5" value="" style="width:98%;" /></td>
                <td><a href="#" title="Изтриване на протокола"><img src="img/delete.gif" border="0" width="15" height="15" alt="Изтриване на протокола" /></a></td>
              </tr>
              <tr class="alternate">
                <td>&nbsp;</td>
                <td colspan="6"><strong>Протокол №:</strong>
                  <input type="text" id="prot_1" name="prot_1" value="" />
                  <strong>от дата:</strong>
                  <input type="text" id="prot_date_1" name="prot_date_1" value="" /></td>
              </tr>
              <!-- new line -->
              <tr>
                <td><select name="select" id="factor_id_1" style="width:98%;" class="newItem">
                    <option value="0">&nbsp;</option>
                    <option value="1">Мед &nbsp;&nbsp;</option>
                    <option value="2">Микроклимат &nbsp;&nbsp;</option>
                    <option value="3">Осветление &nbsp;&nbsp;</option>
                  </select></td>
                <td><input type="text" id="level_1" name="level_1" value="" style="width:98%;" class="newItem" /></td>
                <td><input type="text" id="level_2" name="level_2" value="" style="width:98%;" class="newItem" /></td>
                <td><input type="text" id="level_3" name="level_3" value="" style="width:98%;" class="newItem" /></td>
                <td><input type="text" id="level_4" name="level_4" value="" style="width:98%;" class="newItem" /></td>
                <td><input type="text" id="level_5" name="level_5" value="" style="width:98%;" class="newItem" /></td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td>&nbsp;</td>
                <td colspan="6"><strong>Протокол №:</strong>
                  <input type="text" id="prot_1" name="prot_1" value="" class="newItem" />
                  <strong>от дата:</strong>
                  <input type="text" id="prot_date_1" name="prot_date_1" value="" class="newItem" /></td>
              </tr>
            </tbody>
          </table>
        </div>
      </form>
    </div>
  </div>
</div>
</body>
</html>
