<?php
// To test: http://localhost/stm2008/hipokrat/w_analysis_prophylactic.php?firm_id=16&date_from=01.01.2011&date_to=31.12.2012&offline=1
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
	$filename = 'Prophylactic_'.$cyrlat->cyr2lat($period.'_'.$firm_name).'.doc';

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
normal'><span style='font-size:20.0pt'>Анализ на профилактичните прегледи<o:p></o:p></span></b></p>

<p class=MsoNormal align=center style='text-align:center'><b style='mso-bidi-font-weight:
normal'><span style='font-size:14.0pt'>на работещите в <o:p></o:p></span></b></p>

<p class=MsoNormal align=center style='text-align:center'><b style='mso-bidi-font-weight:
normal'><span style='font-size:14.0pt'><?=((isset($f['firm_name']))?HTMLFormat($f['firm_name']):'')?> за <?=$dbInst->extractYear($date_from, $date_to)?> г.<o:p></o:p></span></b></p>

<p class=MsoNormal align=center style='text-align:center'><b style='mso-bidi-font-weight:
normal'><span style='font-size:14.0pt'><?=HTMLFormat($firm_address)?><o:p></o:p></span></b></p>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>

<p class=MsoNormal style='text-align:justify;'>1. Данни за проведените
задължителни периодични медицински прегледи през съответната календарна година:</p>

<p class=MsoNormal style='text-align:justify;'>1.1. Брой на
работещите, подлежащи на задължителни периодични медицински прегледи: <b
style='mso-bidi-font-weight:normal'><?=((!empty($objStats->avg_workers)) ? $objStats->avg_workers : 'Няма предоставени данни')?></b>.</p>

<p class=MsoNormal style='text-align:justify;'>1.2. Брой на
работещите, обхванати със задължителни периодични медицински прегледи: <b
style='mso-bidi-font-weight:normal'><?=((!empty($objStats->num_workers_medical_checkups)) ? $objStats->num_workers_medical_checkups : 'Няма предоставени данни')?></b>.</p>

<p class=MsoNormal style='text-align:justify;'>1.3. Брой
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

<p class=MsoNormal style='text-align:justify;'>1.4. Брой
работещи със заболявания, открити при проведените задължителни периодични
медицински прегледи: <?=((!empty($objStats->num_ill_workers_medical_checkups)) ? 'общо <b style=\'mso-bidi-font-weight:normal\'>'.$objStats->num_ill_workers_medical_checkups.'</b>' : '<b style=\'mso-bidi-font-weight:normal\'>Няма предоставени данни</b>')?>.</p>

<?php
if($tbl = $objStats->tbl_ill_workers_medical_checkups) {
	echo $tbl;
	echo "<p class=MsoNormal style='text-align:justify;'><o:p>&nbsp;</o:p></p>";
}
?>

<p class=MsoNormal>2. Анализ на връзката между данните за <span class=SpellE>заболяемостта</span>
и трудовата дейност, изводи и препоръки:</p>

<p class=MsoNormal style='text-indent:35.4pt'>Няма пряка връзка между
регистрираните заболявания и условията на труд. Работодателят е предприел всички
необходими мерки за ЗБУТ.</p>

<?php w_footer($s); ?>

</div>

</body>

</html>
