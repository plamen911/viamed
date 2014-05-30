<?php
require('includes.php');

$firm_id = (isset($_GET['firm_id']) && is_numeric($_GET['firm_id'])) ? intval($_GET['firm_id']) :
    0;
$firmInfo = $dbInst->getFirmInfo($firm_id);
if (!$firmInfo) {
    die('Липсва индентификатор на фирмата!');
}

// Xajax begin
require('xajax/xajax_core/xajax.inc.php');
function openAnalysis($aFormValues)
{
    $objResponse = new xajaxResponse();

    $objResponse->assign("btnSubmit", "disabled", false);
    $objResponse->assign("btnSubmit", "value", "Покажи");
    $objResponse->call("DisableEnableForm", false);

    global $firm_id;
    global $dbInst;
    $d = new ParseBGDate();
    $date_from = trim($aFormValues['date_from']);
    $date_to = trim($aFormValues['date_to']);
    if ($date_from == '') {
        $objResponse->alert("Моля, въведете начална дата на анализа.");
        return $objResponse;
    }
    if (!$d->Parse($date_from)) {
        $objResponse->alert($date_from . " е невалидна дата!");
        return $objResponse;
    } else {
        $date_from = $d->year . '-' . $d->month . '-' . $d->day . ' 00:00:00';
    }

    if ($date_to == '') {
        $date_to = date("Y-m-d H:i:s", mktime(23, 59, 59, date('n'), date('j'), date('Y')));
        $aFormValues['date_to'] = date("d.m.Y", mktime(23, 59, 59, date('n'), date('j'),
            date('Y')));
    } else {
        if (!$d->Parse($date_to)) {
            $objResponse->alert($date_to . " е невалидна дата!");
            return $objResponse;
        } else {
            $date_to = $d->year . '-' . $d->month . '-' . $d->day . ' 23:59:59';
        }
    }

    $r = $dbInst->getAnnualReport($firm_id, $date_from, $date_to);
    $avgWorkers = (isset($r)) ? ($r['anual_workers'] + (($r['joined_workers'] + $r['retired_workers']) /
            2)) : 0;
    if (!$avgWorkers) {
        $objResponse->alert("Няма въведени работещи във фирмата през посочения от Вас период!");
        return $objResponse;
    }

    $offline = (isset($aFormValues['show_as_html'])) ? 1 : 0;

    if ($avgWorkers <= 30) {
        //$dest = "w_analiz_below30.php";
        $dest = 'w_rtf_analiz_below30.php';
        $offline = 0;
    } elseif ($avgWorkers > 31 && $avgWorkers <= 100) {
        $dest = "w_rtf_analiz_31-100.php";
    } else {
        $dest = "w_rtf_analiz_above100.php";
    }

    if ($offline) {
        $js = 'testwindow = window.open ("' . $dest . '?firm_id=' . $firm_id . '&date_from=' . trim($aFormValues['date_from']) . '&date_to=' . trim($aFormValues['date_to']) . '&offline=1", "mywindow", "status=1, toolbar=1, location=1, menubar=1, directories=1, resizable=1, scrollbars=1, width=900, height=700");
		testwindow.moveTo(0,0);';
        $objResponse->script($js);
    } else {
        $objResponse->script("window.location='$dest?firm_id=$firm_id&date_from=" . trim($aFormValues['date_from']) . "&date_to=" . trim($aFormValues['date_to']) . "&rand=" . time() . "';");
    }

    return $objResponse;
}
$xajax = new xajax();
$xajax->registerFunction("openAnalysis");
$xajax->registerFunction("formatBGDate");
//$xajax->setFlag("debug",true);
$echoJS = $xajax->getJavascript('xajax/');
$xajax->processRequest();
// Xajax end


?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title><?= SITE_NAME ?></title>
    <link href="styles.css" rel="stylesheet" type="text/css" media="screen"/>
    <?= $echoJS ?>
    <script type="text/javascript" src="js/RegExpValidate.js"></script>
    <script type="text/javascript" src="scw.js"></script>
    <script type="text/javascript" src="js/jquery-latest.pack.js"></script>
    <style type="text/css">
        body, html {
            background-image: none;
            background-color: #EEEEEE;
        }
    </style>
    <script type="text/javascript">
        //<![CDATA[
        $(function(){
            $('#lnkDebug').bind('click', function(e){
                e.preventDefault();
                var date_from = $.trim($('#date_from').val()),
                    date_to = $.trim($('#date_to').val());
                if(date_from == '' || date_to == '') {
                    alert('Моля, въведете начална и крайна дата на анализа.');
                    $('#date_from').focus();
                    return false;
                }

                var target = 'debug_analysis.php?firm_id=<?= $firm_id ?>&date_from=' + date_from + '&date_to=' + date_to,
                    $myLink = $('#lnkMCheckupPassed_newpage');

                $myLink.attr('href', target);
                $myLink[0].click();
                return false;
            });
        });
        //]]>
    </script>
</head>
<body style="overflow:hidden;">
<div id="content">
    <div id="contentinner" align="center">
        <form id="frmAnalysis" action="javascript:void(null);">
            <input type="hidden" id="firm_id" name="firm_id" value="<?= $firm_id ?>"/>
            <table cellpadding="0" cellspacing="0" class="formBg">
                <tr>
                    <td class="leftSplit topSplit"><strong>Начална дата: </strong></td>
                    <td class="rightSplit topSplit"><input type="text" id="date_from" name="date_from"
                                                           value="<?= date('d.m.Y', mktime(0, 0, 0, 1, 1, date('Y'))) ?>"
                                                           size="18" maxlength="10"
                                                           onchange="xajax_formatBGDate('date_from',this.value);return false;"
                                                           onclick="scwShow(this,event);" class="date_input"/>
                        г.
                    </td>
                </tr>
                <tr>
                    <td class="leftSplit"><strong>Крайна дата: </strong></td>
                    <td class="rightSplit"><input type="text" id="date_to" name="date_to"
                                                  value="<?= date('d.m.Y', mktime(23, 59, 59, 12, 31, date('Y'))) ?>"
                                                  size="18" maxlength="10"
                                                  onchange="xajax_formatBGDate('date_to',this.value);return false;"
                                                  onclick="scwShow(this,event);" class="date_input"/>
                        г.
                    </td>
                </tr>
                <tr>
                    <td class="leftSplit">&nbsp;</td>
                    <td class="rightSplit"><input type="button" id="btnSubmit" name="btnSubmit" value="Покажи"
                                                  class="nicerButtons"
                                                  onclick="this.value='обработка...'; this.disabled=true; xajax_openAnalysis(xajax.getFormValues('frmAnalysis')); DisableEnableForm(true); return false;"/>
                    </td>
                </tr>
                <tr>
                    <td class="leftSplit">&nbsp;</td>
                    <td class="rightSplit"><input type="checkbox" id="show_as_html" name="show_as_html" value="1"/> ...
                        като HTML документ
                    </td>
                </tr>
                <tr>
                    <td class="leftSplit">&nbsp;</td>
                    <td class="rightSplit">
                        <a id="lnkDebug" href="#">Извлечение от анализа</a>
                        <a id="lnkMCheckupPassed_newpage" href="#" target="_blank" style="display:none;">Hidden link</a>
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>
</body>
</html>