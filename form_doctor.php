<?php
require('includes.php');

$d_doctor_id = (isset($_GET['doctor_id']) && is_numeric($_GET['doctor_id'])) ? intval($_GET['doctor_id']) : 0;
if($d_doctor_id) { $d = $dbInst->getDoctorInfo($d_doctor_id); }

if(isset($_GET['reload']) && $_GET['reload'] == '1') {
?>
<script type="text/javascript">
//<![CDATA[
document.getElementById('TB_ajaxWindowTitle').style.fontWeight = 'bold';
// Reload the parent window when the close button of Thickbox popup is clicked!
document.getElementById('TB_closeWindowButton').onclick = function() {
	window.location.reload();
};
//]]>
</script>
<?php } ?>
<!-- doctor's form -->
<div id="newdoctor" align="center">
  <form id="d_frmDoctor" name="d_frmDoctor" action="javascript:void(null);">
  	<input type="hidden" id="d_doctor_id" name="d_doctor_id" value="<?=$d_doctor_id?>" />
    <table cellpadding="0" cellspacing="0" class="formBg">
      <tr>
        <td class="leftSplit rightSplit topSplit"><strong>Имена:</strong>
          <input type="text" id="d_doctor_name" name="d_doctor_name" value="<?=((isset($d['doctor_name']))?HTMLFormat($d['doctor_name']):'')?>" size="60" maxlength="60" />
        </td>
      </tr>
      <tr>
        <td class="leftSplit rightSplit">Адрес на практика:
          <input type="text" id="d_address" name="d_address" value="<?=((isset($d['address']))?HTMLFormat($d['address']):'')?>" size="57" maxlength="60" />
        </td>
      </tr>
      <tr>
        <td class="leftSplit rightSplit">Тел. 1:
          <input type="text" id="d_phone1" name="d_phone1" value="<?=((isset($d['phone1']))?HTMLFormat($d['phone1']):'')?>" size="28" maxlength="40" />
          Тел. 2:
          <input type="text" id="d_phone2" name="d_phone2" value="<?=((isset($d['phone2']))?HTMLFormat($d['phone2']):'')?>" size="28" maxlength="40" />
        </td>
      </tr>
      <tr>
        <td class="leftSplit rightSplit"><p style="text-align:center">
            <input type="button" id="btnDoctor" name="btnDoctor" value="Съхрани" onclick="xajax_processDoctor(xajax.getFormValues('d_frmDoctor'));DisableEnableForm(true);return false;" class="nicerButtons" />
          </p></td>
      </tr>
    </table>
  </form>
</div>