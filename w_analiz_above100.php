<?php
// To test: http://localhost/stm2008/hipokrat/w_analiz_above100.php?firm_id=227&date_from=01.01.2010&date_to=31.12.2010&offline=1
require('includes.php');
require('class.stmstats.php');

$offline = (isset($_GET['offline']) && $_GET['offline'] == '1') ? 1 : 0;

$firm_id = (isset($_GET['firm_id']) && is_numeric($_GET['firm_id'])) ? intval($_GET['firm_id']) : 0;
$f = $dbInst->getFirmInfo($firm_id);
if(!$f) {
	die('Липсва индентификатор на фирмата!');
}
$sbdvsn_id = (isset($_GET['subdivision_id']) && !empty($_GET['subdivision_id'])) ? intval($_GET['subdivision_id']) : 0;
if(!empty($sbdvsn_id)) {
	$subdivision_name = $dbInst->GiveValue('subdivision_name', 'subdivisions', "WHERE `firm_id` = $firm_id AND `subdivision_id` = $sbdvsn_id", 0);
	if(!empty($subdivision_name)) {
		$f['firm_name'] .= ', '.$subdivision_name;
	}
}
$wplce_id = (isset($_GET['wplace_id']) && !empty($_GET['wplace_id'])) ? intval($_GET['wplace_id']) : 0;
if(!empty($wplce_id)) {
	$wplace_name = $dbInst->GiveValue('wplace_name', 'work_places', "WHERE `firm_id` = $firm_id AND `wplace_id` = $wplce_id", 0);
	if(!empty($wplace_name)) {
		$f['firm_name'] .= ', '.$wplace_name;
	}
}

$s = $dbInst->getStmInfo();

$stm_name = preg_replace('/\<br\s*\/?\>/', '', $s['stm_name']);

//$dbInst->makeAllMkbUpperCase();

if(!isset($_GET['date_from']) || trim($_GET['date_from']) == '') {
	$y = date('Y') - 1;
	$date_from = date('Y-m-d H:i:s', mktime(0,0,0,1,1,$y));
	$date_to = date('Y-m-d H:i:s', mktime(23,59,59,12,31,$y));
} else {
	$d = new ParseBGDate();
	if($d->Parse($_GET['date_from']))
	$date_from = $d->year.'-'.$d->month.'-'.$d->day.' 00:00:00';
	else
	$date_from = '';
	if($d->Parse($_GET['date_to']))
	$date_to = $d->year.'-'.$d->month.'-'.$d->day.' 23:59:59';
	else
	$date_to = '';
	if($date_from == '' || $date_to == '') {
		$y = date('Y') - 1;
		$date_from = date('Y-m-d H:i:s', mktime(0,0,0,1,1,$y));
		$date_to = date('Y-m-d H:i:s', mktime(23,59,59,12,31,$y));
	}
}
$objStats = new StmStats($firm_id, $date_from, $date_to, $sbdvsn_id, $wplce_id);

$unchecked = 'unchecked.gif';
$checked = 'checked.gif';
$imgpath = "http://" . ((isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:$_SERVER['SERVER_ADDR'].':'.$_SERVER['SERVER_PORT'])) . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/img/";

$filename = $f['firm_name'];

if(!$offline) {
	$firm_name = str_replace(' ', '_', $filename);
	$firm_name = str_replace('"', '', $firm_name);
	$firm_name = str_replace('\'', '', $firm_name);
	$firm_name = str_replace('”', '', $firm_name);
	$firm_name = str_replace('„', '', $firm_name);
	$firm_name = str_replace('_-_', '_', $firm_name);

	$period = str_replace(', ', '_', $dbInst->extractYear($date_from, $date_to));
	$period = str_replace(' и ', '_', $period);

	require_once("cyrlat.class.php");
	$cyrlat = new CyrLat;
	$filename = 'Analiz_'.$cyrlat->cyr2lat($period.'_'.$firm_name).'.doc';

	header("Pragma: public");
	header("Content-Disposition: attachment; filename=\"$filename\";");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	//header("Cache-Control: private", false);
	header("Content-Type: application/octet-stream");
	//header("Content-type: application/msword;");
	//$imgpath = str_replace('/','\\',str_replace(basename($_SERVER['PHP_SELF']),'',$_SERVER["SCRIPT_FILENAME"])).'img\\';
}

$location_type = '';
switch ($f['location_type']) {
	case '0':
		$location_type = 'с.';
		break;
	case '1':
		$location_type = 'гр.';
		break;
	case '2':
		$location_type = 'жк';
		break;
	case '3':
		$location_type = 'кв.';
		break;
	default:
		$location_type = '';
		break;
}
$firm_address = trim($location_type.$f['location_name'].((!empty($f['address'])) ? ', '.$f['address'] : ''));

?><html xmlns:v="urn:schemas-microsoft-com:vml"
xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:w="urn:schemas-microsoft-com:office:word"
xmlns="http://www.w3.org/TR/REC-html40">

<head>
<meta http-equiv=Content-Type content="text/html; charset=utf-8">
<meta name=ProgId content=Word.Document>
<meta name=Generator content="Microsoft Word 11">
<meta name=Originator content="Microsoft Word 11">
<title><?=((isset($stm_name))?HTMLFormat($stm_name):'СЛУЖБА ПО ТРУДОВА МЕДИЦИНА')?></title>
<!--[if gte mso 9]><xml>
 <o:DocumentProperties>
  <o:Author>STM</o:Author>
  <o:LastAuthor>STM</o:LastAuthor>
  <o:Revision>3</o:Revision>
  <o:TotalTime>27</o:TotalTime>
  <o:LastPrinted>2008-04-18T09:44:00Z</o:LastPrinted>
  <o:Created>2008-06-17T06:44:00Z</o:Created>
  <o:LastSaved>2008-06-17T06:45:00Z</o:LastSaved>
  <o:Pages>1</o:Pages>
  <o:Words>498</o:Words>
  <o:Characters>2840</o:Characters>
  <o:Company>STM</o:Company>
  <o:Lines>23</o:Lines>
  <o:Paragraphs>6</o:Paragraphs>
  <o:CharactersWithSpaces>3332</o:CharactersWithSpaces>
  <o:Version>11.5606</o:Version>
 </o:DocumentProperties>
</xml><![endif]--><!--[if gte mso 9]><xml>
 <w:WordDocument>
  <w:View>Print</w:View>
  <w:SpellingState>Clean</w:SpellingState>
  <w:GrammarState>Clean</w:GrammarState>
  <w:HyphenationZone>21</w:HyphenationZone>
  <w:PunctuationKerning/>
  <w:ValidateAgainstSchemas/>
  <w:SaveIfXMLInvalid>false</w:SaveIfXMLInvalid>
  <w:IgnoreMixedContent>false</w:IgnoreMixedContent>
  <w:AlwaysShowPlaceholderText>false</w:AlwaysShowPlaceholderText>
  <w:Compatibility>
   <w:BreakWrappedTables/>
   <w:SnapToGridInCell/>
   <w:WrapTextWithPunct/>
   <w:UseAsianBreakRules/>
   <w:DontGrowAutofit/>
  </w:Compatibility>
  <w:BrowserLevel>MicrosoftInternetExplorer4</w:BrowserLevel>
 </w:WordDocument>
</xml><![endif]--><!--[if gte mso 9]><xml>
 <w:LatentStyles DefLockedState="false" LatentStyleCount="156">
 </w:LatentStyles>
</xml><![endif]-->
<style>
<!--
 /* Font Definitions */
 @font-face
	{font-family:Tahoma;
	panose-1:2 11 6 4 3 5 4 4 2 4;
	mso-font-charset:204;
	mso-generic-font-family:swiss;
	mso-font-pitch:variable;
	mso-font-signature:1627421319 -2147483648 8 0 66047 0;}
 /* Style Definitions */
 p.MsoNormal, li.MsoNormal, div.MsoNormal
	{mso-style-parent:"";
	margin:0cm;
	margin-bottom:.0001pt;
	mso-pagination:widow-orphan;
	font-size:12.0pt;
	font-family:"Times New Roman";
	mso-fareast-font-family:"Times New Roman";}
p.MsoAcetate, li.MsoAcetate, div.MsoAcetate
	{mso-style-noshow:yes;
	margin:0cm;
	margin-bottom:.0001pt;
	mso-pagination:widow-orphan;
	font-size:8.0pt;
	font-family:Tahoma;
	mso-fareast-font-family:"Times New Roman";}
span.SpellE
	{mso-style-name:"";
	mso-spl-e:yes;}
@page Section1
	{size:595.3pt 841.9pt;
	margin:70.85pt 70.85pt 70.85pt 70.85pt;
	mso-header-margin:35.4pt;
	mso-footer-margin:35.4pt;
	mso-paper-source:0;}
div.Section1
	{page:Section1;}
-->
</style>
<!--[if gte mso 10]>
<style>
 /* Style Definitions */
 table.MsoNormalTable
	{mso-style-name:"Table Normal";
	mso-tstyle-rowband-size:0;
	mso-tstyle-colband-size:0;
	mso-style-noshow:yes;
	mso-style-parent:"";
	mso-padding-alt:0cm 5.4pt 0cm 5.4pt;
	mso-para-margin:0cm;
	mso-para-margin-bottom:.0001pt;
	mso-pagination:widow-orphan;
	font-size:10.0pt;
	font-family:"Times New Roman";
	mso-ansi-language:#0400;
	mso-fareast-language:#0400;
	mso-bidi-language:#0400;}
table.MsoTableGrid
	{mso-style-name:"Table Grid";
	mso-tstyle-rowband-size:0;
	mso-tstyle-colband-size:0;
	border:solid windowtext 1.0pt;
	mso-border-alt:solid windowtext .5pt;
	mso-padding-alt:0cm 5.4pt 0cm 5.4pt;
	mso-border-insideh:.5pt solid windowtext;
	mso-border-insidev:.5pt solid windowtext;
	mso-para-margin:0cm;
	mso-para-margin-bottom:.0001pt;
	mso-pagination:widow-orphan;
	font-size:10.0pt;
	font-family:"Times New Roman";
	mso-ansi-language:#0400;
	mso-fareast-language:#0400;
	mso-bidi-language:#0400;}
</style>
<![endif]--><!--[if gte mso 9]><xml>
 <o:shapedefaults v:ext="edit" spidmax="2050"/>
</xml><![endif]--><!--[if gte mso 9]><xml>
 <o:shapelayout v:ext="edit">
  <o:idmap v:ext="edit" data="1"/>
 </o:shapelayout></xml><![endif]-->
</head>

<body lang=BG style='tab-interval:35.4pt'>

<div class=Section1>

<?php w_heading($s); ?>

</div>

<p class=MsoNormal><b style='mso-bidi-font-weight:normal'><i style='mso-bidi-font-style:
normal'><span style='font-size:20.0pt'><o:p>&nbsp;</o:p></span></i></b></p>

<p class=MsoNormal align=center style='text-align:center'><b style='mso-bidi-font-weight:
normal'><span style='font-size:20.0pt'>Обобщен анализ на здравното състояние<o:p></o:p></span></b></p>

<p class=MsoNormal align=center style='text-align:center'><b style='mso-bidi-font-weight:
normal'><span style='font-size:14.0pt'>на работещите в <o:p></o:p></span></b></p>

<p class=MsoNormal align=center style='text-align:center'><b style='mso-bidi-font-weight:
normal'><span style='font-size:14.0pt'><?=((isset($f['firm_name']))?HTMLFormat($f['firm_name']):'')?> за <?=$dbInst->extractYear($date_from, $date_to)?> г.<o:p></o:p></span></b></p>

<p class=MsoNormal align=center style='text-align:center'><b style='mso-bidi-font-weight:
normal'><span style='font-size:14.0pt'><?=HTMLFormat($firm_address)?><o:p></o:p></span></b></p>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>

<p class=MsoNormal>1. Данни за работещите в предприятието</p>

<table class=MsoTableGrid border=1 cellspacing=0 cellpadding=0 width="100%"
 style='width:100.0%;border-collapse:collapse;border:none;mso-border-alt:solid windowtext .5pt;
 mso-yfti-tbllook:480;mso-padding-alt:0cm 5.4pt 0cm 5.4pt;mso-border-insideh:
 .5pt solid windowtext;mso-border-insidev:.5pt solid windowtext'>
 <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes;height:11.95pt'>
  <td width=295 rowspan=2 style='width:221.4pt;border:solid windowtext 1.0pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt;height:11.95pt'>
  <p class=MsoNormal align=center style='text-align:center'>Средно-списъчен
  състав на работещите:</p>
  <p class=MsoNormal align=center style='text-align:center'><o:p>&nbsp;</o:p></p>
  </td>
  <td width=324 colspan=2 style='width:243.0pt;border:solid windowtext 1.0pt;
  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt;height:11.95pt'>
  <p class=MsoNormal align=center style='text-align:center'>Пол</p>
  </td>
 </tr>
 <tr style='mso-yfti-irow:1;height:9.0pt'>
  <td width=169 style='width:126.9pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt;height:9.0pt'>
  <p class=MsoNormal align=center style='text-align:center'>М </p>
  </td>
  <td width=155 style='width:116.1pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt;height:9.0pt'>
  <p class=MsoNormal align=center style='text-align:center'>Ж </p>
  </td>
 </tr>
 <tr style='mso-yfti-irow:2;mso-yfti-lastrow:yes'>
  <td width=295 valign=top style='width:221.4pt;border:solid windowtext 1.0pt;
  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b
  style='mso-bidi-font-weight:normal'><?=$objStats->avg_workers?></b></p>
  </td>
  <td width=169 valign=top style='width:126.9pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b
  style='mso-bidi-font-weight:normal'><?=$objStats->avg_men?></b></p>
  </td>
  <td width=155 valign=top style='width:116.1pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b
  style='mso-bidi-font-weight:normal'><?=$objStats->avg_women?></b></p>
  </td>
 </tr>
</table>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>

<p class=MsoNormal style='text-align:justify;'>2. Данни за
боледувалите работещи за съответната календарна година:</p>

<p class=MsoNormal style='text-align:justify;'>2.1. Брой
работещи с регистрирани заболявания (по данни от болничните листове): <b
style='mso-bidi-font-weight:normal'><?=((!empty($objStats->sick_anual_workers)) ? $objStats->sick_anual_workers : 'Няма предоставени данни')?></b>.</p>

<p class=MsoNormal style='text-align:justify;'>2.2. Абсолютен
брой случаи (първични болнични листове) – общо и по <span class=SpellE>нозологична</span>
структура, съгласно <span class=SpellE>МКБ-</span>10: <?=((!empty($objStats->primary_charts)) ? 'общо <b style=\'mso-bidi-font-weight:normal\'>'.$objStats->primary_charts.'</b>' : '<b style=\'mso-bidi-font-weight:normal\'>Няма предоставени данни</b>')?>.</p>

<?php
if($tbl = $objStats->getPatientChartsByNumCasesTable()) {
	echo $tbl['table'];
	echo "<p class=MsoNormal style='text-align:justify;'><o:p>&nbsp;</o:p></p>";
	echo getChart($tbl['chart_data'], $imgname = 'primarylists_'.$firm_id, $title = 'Разпределение по абсолютен брой случаи (първични болнични листове)');
	echo "<p class=MsoNormal style='text-align:justify;'><o:p>&nbsp;</o:p></p>";
}
?>

<p class=MsoNormal style='text-align:justify;'>2.3. Брой на
дните с временна неработоспособност (общо от всички болнични листове – първични
и продължения) – общо и по <span class=SpellE>нозологична</span> структура,
съгласно <span class=SpellE>МКБ-</span>10: <?=((!empty($objStats->days_off)) ? 'общо <b style=\'mso-bidi-font-weight:normal\'>'.$objStats->days_off.'</b>' : '<b style=\'mso-bidi-font-weight:normal\'>Няма предоставени данни</b>')?>.</p>

<?php
if($tbl = $objStats->getPatientChartsByDaysOffTable()) {
	echo $tbl['table'];
	echo "<p class=MsoNormal style='text-align:justify;'><o:p>&nbsp;</o:p></p>";
	echo getChart($tbl['chart_data'], $imgname = 'numdaysoff_'.$firm_id, $title = 'Разпределение по брой на дните с временна неработоспособност');
	echo "<p class=MsoNormal style='text-align:justify;'><o:p>&nbsp;</o:p></p>";
}
?>

<p class=MsoNormal style='text-align:justify;'>2.4. Брой случаи
с временна неработоспособност с продължителност до 3 дни (първични болнични
листове): <b style='mso-bidi-font-weight:normal'><?=((!empty($objStats->primary_charts_days_off_3down)) ? $objStats->primary_charts_days_off_3down : 'Няма предоставени данни')?></b>.</p>

<p class=MsoNormal style='text-align:justify;'>2.5. Брой на
работещите с 4 и повече случаи с временна неработоспособност (първични болнични
листове): <b style='mso-bidi-font-weight:normal'><?=((!empty($objStats->num_workers_primary_charts_4up)) ? $objStats->num_workers_primary_charts_4up : 'Няма предоставени данни')?></b>.</p>

<p class=MsoNormal style='text-align:justify;'>2.6. Брой на
работещите с 30 и повече дни временна неработоспособност от заболявания: <b
style='mso-bidi-font-weight:normal'><?=((!empty($objStats->num_workers_days_off_30up)) ? $objStats->num_workers_days_off_30up : 'Няма предоставени данни')?></b>.</p>

<?php
if($tbl = $objStats->getWorkersDaysOff30upTable()) {
	echo $tbl;
	echo "<p class=MsoNormal style='text-align:justify;'><o:p>&nbsp;</o:p></p>";
}
?>

<p class=MsoNormal style='text-align:justify;'>2.7. Брой регистрирани
професионални болести: <b style='mso-bidi-font-weight:normal'><?=((!empty($objStats->num_pro_diseases)) ? $objStats->num_pro_diseases : 'Няма предоставени данни')?></b>.</p>

<p class=MsoNormal style='text-align:justify;'>2.8. Брой
работещи с регистрирани професионални болести: <b style='mso-bidi-font-weight:
normal'><?=((!empty($objStats->num_workers_pro_diseases)) ? $objStats->num_workers_pro_diseases : 'Няма предоставени данни')?></b>.</p>

<p class=MsoNormal style='text-align:justify;'>2.9. Брой на
работещите с експертно решение на ТЕЛК за заболяване с трайна
неработоспособност: <b style='mso-bidi-font-weight:normal'><?=((!empty($objStats->num_workers_with_telk)) ? $objStats->num_workers_with_telk : 'Няма предоставени данни')?></b>.</p>

<p class=MsoNormal style='text-align:justify;'>3. Данни за проведените
задължителни периодични медицински прегледи през съответната календарна година:</p>

<p class=MsoNormal style='text-align:justify;'>3.1. Брой на
работещите, подлежащи на задължителни периодични медицински прегледи: <b
style='mso-bidi-font-weight:normal'><?=((!empty($objStats->avg_workers)) ? $objStats->avg_workers : 'Няма предоставени данни')?></b>.</p>

<p class=MsoNormal style='text-align:justify;'>3.2. Брой на
работещите, обхванати със задължителни периодични медицински прегледи: <b
style='mso-bidi-font-weight:normal'><?=((!empty($objStats->num_workers_medical_checkups)) ? $objStats->num_workers_medical_checkups : 'Няма предоставени данни')?></b>.</p>

<p class=MsoNormal style='text-align:justify;'>3.3. Брой
заболявания, открити при проведените задължителни периодични медицински
прегледи: <?=((!empty($objStats->num_diseases_medical_checkups)) ? 'общо <b style=\'mso-bidi-font-weight:normal\'>'.$objStats->num_diseases_medical_checkups.'</b>' : '<b style=\'mso-bidi-font-weight:normal\'>Няма предоставени данни</b>')?>.</p>

<?php
if($tbl = $objStats->tbl_diseases_medical_checkups) {
	echo $tbl;
	echo "<p class=MsoNormal style='text-align:justify;'><o:p>&nbsp;</o:p></p>";
	echo getChart($objStats->chart_data, $imgname = 'prophylactic_'.$firm_id, $title = 'Разпределение по брой заболявания, открити при периодичните мед. прегледи');
	echo "<p class=MsoNormal style='text-align:justify;'><o:p>&nbsp;</o:p></p>";
}
?>

<p class=MsoNormal style='text-align:justify;'>3.4. Брой
работещи със заболявания, открити при проведените задължителни периодични
медицински прегледи: <?=((!empty($objStats->num_ill_workers_medical_checkups)) ? 'общо <b style=\'mso-bidi-font-weight:normal\'>'.$objStats->num_ill_workers_medical_checkups.'</b>' : '<b style=\'mso-bidi-font-weight:normal\'>Няма предоставени данни</b>')?>.</p>

<?php
if($tbl = $objStats->tbl_ill_workers_medical_checkups) {
	echo $tbl;
	echo "<p class=MsoNormal style='text-align:justify;'><o:p>&nbsp;</o:p></p>";
}
?>

<p class=MsoNormal style='text-align:justify;'>II. Анализ и
оценка на показателите, характеризиращи здравното състояние на работещите</p>

<p class=MsoNormal style='text-align:justify;'>1. Честота на
боледувалите работещи със заболяемост с временна
неработоспособност: <?=$objStats->freqSickWorkersTempDisability()?>.</p>

<p class=MsoNormal style='text-align:justify;'>2. Честота на
случаите с временна неработоспособност: <?=$objStats->freqCasesTempDisability()?>.</p>

<p class=MsoNormal style='text-align:justify;'>3. Честота на 
трудозагубите с временна неработоспособност: <?=$objStats->freqDaysOffTempDisability()?>.</p>

<p class=MsoNormal style='text-align:justify;'>4. Средна
продължителност на един случай с временна неработоспособност: <b
style='mso-bidi-font-weight:normal'><?=((!empty($objStats->avg_length_of_chart)) ? $objStats->avg_length_of_chart : 'Няма предоставени данни')?></b>.</p>

<p class=MsoNormal style='text-align:justify;'>5. Структура на
случаите/дните с временна неработоспособност по <span class=SpellE>нозологична</span>
принадлежност:</p>

<?php
if(false === strpos($s['stm_name'], 'МАРС-2001')) {
	if($tbl = $objStats->getTmpUnableToWorkStructTable()) {
		echo $tbl;
		echo "<p class=MsoNormal style='text-align:justify;'><o:p>&nbsp;</o:p></p>";
		
		$flds = $objStats->getTmpUnableToWorkStructChartData();
		if(!empty($flds)) {
			$mkbs = array();
			$result = $dbInst->query("SELECT mkb_id, mkb_desc FROM mkb WHERE mkb_id IN ( '".implode("', '", array_keys($flds))."' )");
			foreach ($result as $key => $val) {
				$mkbs[$val['mkb_id']] = $val['mkb_id'].' - '.$val['mkb_desc'];
			}
			$data1 = array();
			$data2 = array();
			foreach ($flds as $mkb_id => $fld) {
				$data1[$mkbs[$mkb_id]] = $fld['num_days_off'];
				$data2[$mkbs[$mkb_id]] = $fld['num_cases'];
			}
			echo getChart($data1, $imgname = 'num_days_off_'.$firm_id, $title = 'Разпределение по брой дни с временна неработоспособност');
			echo "<p class=MsoNormal style='text-align:justify;'><o:p>&nbsp;</o:p></p>";
			echo getChart($data2, $imgname = 'num_cases_'.$firm_id, $title = 'Разпределение по брой случаи с временна неработоспособност');
		}
		
		echo "<p class=MsoNormal style='text-align:justify;'><o:p>&nbsp;</o:p></p>";
	}
}
?>

<p class=MsoNormal style='text-indent:35.4pt'>По основни признаци, показателите на заболеваемостта с временна неработоспособност 
са представени в следната таблица:</p>
<?php
echo $objStats->getAnaliticsTable();
?>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>

<p class=MsoNormal style='text-align:justify;'>6. Относителен
дял на често и дълго боледувалите работещи: <?=(($cnt = $objStats->relativeShareLongDaysOff()) ? $cnt : 'Няма предоставени данни')?></p>

<p class=MsoNormal style='text-align:justify;'>7. Относителен
дял на краткосрочната временна неработоспособност: <?=(($cnt = $objStats->relativeShareShortDaysOff()) ? $cnt : 'Няма предоставени данни')?></p>

<p class=MsoNormal style='text-align:justify;'>8. Честота на
работещите с професионални болести: <b style='mso-bidi-font-weight:normal'><?=(($cnt = $objStats->freqWorkersProDiseases()) ? $cnt : 'Няма предоставени данни')?></b>.</p>

<?php
$no_data = '';
if($tbl = $objStats->getWorkersProDiseasesStruct()) {
	$tbl .= "<p class=MsoNormal style='text-align:justify;'><o:p>&nbsp;</o:p></p>";
} else {
	$no_data .= ': <b>Няма предоставени данни</b>.';
}
?>
<p class=MsoNormal style='text-align:justify;'>9. Структура на
работещите с професионална <span class=SpellE>заболяемост</span> по <span
class=SpellE>нозология<?=$no_data?></span></p>
<?=$tbl?>

<p class=MsoNormal style='text-align:justify;'>10. Честота на
работещите с трудови злополуки: <b style='mso-bidi-font-weight:normal'><?=(($cnt = $objStats->freqWorkersLabourAccidents()) ? $cnt : 'Няма предоставени данни')?></b>.</p>

<p class=MsoNormal style='text-align:justify;'>11. Честота на
работещите със <span class=SpellE>заболяемост</span> с трайна
неработоспособност: <b style='mso-bidi-font-weight:normal'><?=(($cnt = $objStats->freqWorkersWithTelk()) ? $cnt : 'Няма предоставени данни')?></b>.</p>

<p class=MsoNormal style='text-align:justify;'>12. Честота на
лицата със заболявания, открити при проведените периодични медицински прегледи:
<b style='mso-bidi-font-weight:normal'><?=(($cnt = $objStats->freqILLWorkersMedicalCheckups()) ? $cnt : 'Няма предоставени данни')?></b>.</p>

<?php
$no_data = '';
if($tbl = $objStats->getTelkListDetailsTable()) {
	$tbl .= "<p class=MsoNormal style='text-align:justify;'><o:p>&nbsp;</o:p></p>";
} else {
	$no_data .= ': <b>Няма предоставени данни</b>.';
}
?>
<p class=MsoNormal style='text-align:justify;'>13. Работещи с
експертно решение на ТЕЛК/НЕЛК – брой и честота на заболяванията с трайна
неработоспособност, професионални болести и трудови злополуки<?=$no_data?></p>
<?=$tbl?>

<p class=MsoNormal>14. Анализ на връзката между данните за <span class=SpellE>заболяемостта</span>
и трудовата дейност, изводи и препоръки:</p>

<p class=MsoNormal style='text-indent:35.4pt'>Няма пряка връзка между
регистрираните заболявания и условията на труд. Работодателят е предприел всички
необходими мерки за ЗБУТ.</p>

<?php w_footer($s); ?>

</div>

</body>

</html>
