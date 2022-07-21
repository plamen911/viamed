<?php
// http://localhost/stm2008/hipokrat_aec2/w_workers_checkups_list.php?firm_id=100&date_from=01.01.2007&date_to=31.12.2008&offline=1
require('includes.php');

$offline = (isset($_GET['offline']) && $_GET['offline'] == '1') ? 1 : 0;

$firm_id = (isset($_GET['firm_id']) && is_numeric($_GET['firm_id'])) ? intval($_GET['firm_id']) : 0;
$f = $dbInst->getFirmInfo($firm_id);
if(!$f) {
	die('Липсва индентификатор на фирмата!');
}
$subdivision_id = (isset($_GET['subdivision_id']) && !empty($_GET['subdivision_id'])) ? intval($_GET['subdivision_id']) : 0;
if(!empty($subdivision_id)) {
	$subdivision_name = $dbInst->GiveValue('subdivision_name', 'subdivisions', "WHERE `firm_id` = $firm_id AND `subdivision_id` = $subdivision_id", 0);
	if(!empty($subdivision_name)) {
		$f['firm_name'] .= ', '.$subdivision_name;
	}
}

$s = $dbInst->getStmInfo();

$stm_name = preg_replace('/\<br\s*\/?\>/', '', $s['stm_name']);

$date_from = date('Y-m-d H:i:s', mktime(0,0,0,1,1,date('Y')));
$date_to = date('Y-m-d H:i:s', mktime(23,59,59,12,31,date('Y')));
$d = new ParseBGDate();
if(isset($_GET['date_from']) && '' != trim($_GET['date_from'])) {
	if($d->Parse($_GET['date_from'])) {
		$date_from = $d->year.'-'.$d->month.'-'.$d->day.' 00:00:00';
	}
}
if(isset($_GET['date_to']) && '' != trim($_GET['date_to'])) {
	if($d->Parse($_GET['date_to'])) {
		$date_to = $d->year.'-'.$d->month.'-'.$d->day.' 23:59:59';
	}
}

if(!$offline) {
	$firm_name = str_replace(' ', '_', $f['firm_name']);
	$firm_name = str_replace('"', '', $firm_name);
	$firm_name = str_replace('\'', '', $firm_name);
	$firm_name = str_replace('”', '', $firm_name);
	$firm_name = str_replace('„', '', $firm_name);
	$firm_name = str_replace('_-_', '_', $firm_name);

	require_once("cyrlat.class.php");
	$cyrlat = new CyrLat;
	$filename = 'Spisyk_pregledi_'.$cyrlat->cyr2lat($firm_name).'.doc';

	header("Pragma: public");
	header("Content-Disposition: attachment; filename=\"$filename\";");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	//header("Cache-Control: private", false);
	header("Content-Type: application/octet-stream");
	//header("Content-type: application/msword;");
	//$imgpath = str_replace('/','\\',str_replace(basename($_SERVER['PHP_SELF']),'',$_SERVER["SCRIPT_FILENAME"])).'img\\';
}

?><html xmlns:v="urn:schemas-microsoft-com:vml"
xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:w="urn:schemas-microsoft-com:office:word"
xmlns="http://www.w3.org/TR/REC-html40">

<head>
<meta http-equiv=Content-Type content="text/html; charset=utf-8">
<meta name=ProgId content=Word.Document>
<meta name=Generator content="Microsoft Word 11">
<meta name=Originator content="Microsoft Word 11">
<!--<link rel=File-List href="workers_list_files/filelist.xml">-->
<title><?=((isset($stm_name))?HTMLFormat($stm_name):'СЛУЖБА ПО ТРУДОВА МЕДИЦИНА')?></title>
<!--[if gte mso 9]><xml>
 <o:DocumentProperties>
  <o:Author>Plamen</o:Author>
  <o:Template>Normal</o:Template>
  <o:LastAuthor>Plamen</o:LastAuthor>
  <o:Revision>9</o:Revision>
  <o:TotalTime>232</o:TotalTime>
  <o:LastPrinted>2008-04-18T09:44:00Z</o:LastPrinted>
  <o:Created>2008-07-04T10:37:00Z</o:Created>
  <o:LastSaved>2008-07-04T10:45:00Z</o:LastSaved>
  <o:Pages>1</o:Pages>
  <o:Words>64</o:Words>
  <o:Characters>367</o:Characters>
  <o:Company>СТМ</o:Company>
  <o:Lines>3</o:Lines>
  <o:Paragraphs>1</o:Paragraphs>
  <o:CharactersWithSpaces>430</o:CharactersWithSpaces>
  <o:Version>11.5606</o:Version>
 </o:DocumentProperties>
</xml><![endif]--><!--[if gte mso 9]><xml>
 <w:WordDocument>
  <w:View>Print</w:View>
  <w:Zoom>115</w:Zoom>
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
p.msoacetate0, li.msoacetate0, div.msoacetate0
	{mso-style-name:msoacetate;
	mso-style-noshow:yes;
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
	margin:35.95pt 70.85pt 53.95pt 70.85pt;
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
table.a
	{mso-style-name:"Нормална таблица";
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
</style>
<![endif]--><!--<link rel=File-List href="analiz_below30_02_files/filelist.xml">--><!--[if gte mso 9]><xml>
 <o:shapedefaults v:ext="edit" spidmax="6146"/>
</xml><![endif]--><!--[if gte mso 9]><xml>
 <o:shapelayout v:ext="edit">
  <o:idmap v:ext="edit" data="1"/>
 </o:shapelayout></xml><![endif]-->
</head>

<body lang=BG style='tab-interval:35.4pt'>

<div class=Section1>

<?php w_heading($s); ?>

<?php

$query = "	SELECT w.fname, w.sname, w.lname, w.egn, w.worker_id,
			strftime('%d.%m.%Y г.', c.checkup_date, 'localtime') AS checkup_date2,
			i.position_name
			FROM medical_checkups c
			LEFT JOIN workers w ON (w.worker_id = c.worker_id)
			LEFT JOIN firm_struct_map m ON (m.map_id = w.map_id )
			LEFT JOIN firm_positions i ON (i.position_id = m.position_id)
			WHERE w.firm_id = $firm_id
			AND w.is_active = '1'
			AND w.date_retired = ''
			AND julianday(c.checkup_date) >= julianday('$date_from')
			AND julianday(c.checkup_date) <= julianday('$date_to')
			".(($subdivision_id)?" AND m.subdivision_id = '$subdivision_id' ":'')."
			ORDER BY w.date_retired, w.fname, w.sname, w.lname, w.egn, w.worker_id";

$rows = $dbInst->fnSelectRows($query);
?>

</div>

<p class=MsoNormal><b style='mso-bidi-font-weight:normal'><i style='mso-bidi-font-style:
normal'><span style='font-size:20.0pt'><o:p>&nbsp;</o:p></span></i></b></p>

<p class=MsoNormal align=center style='text-align:center'><b style='mso-bidi-font-weight:
normal'><span style='font-size:20.0pt'>Списък<o:p></o:p></span></b></p>

<p class=MsoNormal align=center style='text-align:center'><b style='mso-bidi-font-weight:
normal'><span lang=BG style='font-size:14.0pt;mso-ansi-language:BG'>на
работещите в <?=((isset($f['firm_name']))?HTMLFormat($f['firm_name']):'')?><?=((isset($f['location_name']))?' – '.HTMLFormat($f['location_name']):'')?><o:p></o:p></span></b></p>

<p class=MsoNormal align=center style='text-align:center'><b style='mso-bidi-font-weight:
normal'><span lang=BG style='font-size:14.0pt;mso-ansi-language:BG'>преминали
профилактичен медицински преглед <o:p></o:p></span></b></p>

<p class=MsoNormal align=center style='text-align:center'><b style='mso-bidi-font-weight:
normal'><span lang=BG style='font-size:14.0pt;mso-ansi-language:BG'>от
<?=date("d.m.Y", strtotime($date_from))?> г. до <?=date("d.m.Y", strtotime($date_to))?> г.<o:p></o:p></span></b></p>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>

<?php if(count($rows)) { ?>
<table class=MsoTableGrid border=1 cellspacing=0 cellpadding=0 width="100%"
 style='width:100.0%;margin-left:1.9pt;border-collapse:collapse;border:none;
 mso-border-alt:solid windowtext .5pt;mso-yfti-tbllook:480;mso-padding-alt:
 0cm 5.4pt 0cm 5.4pt;mso-border-insideh:.5pt solid windowtext;mso-border-insidev:
 .5pt solid windowtext'>
 <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes'>
  <td width=53 style='width:39.5pt;border:solid windowtext 1.0pt;mso-border-alt:
  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b
  style='mso-bidi-font-weight:normal'><span lang=BG style='mso-ansi-language:
  BG'>№ по ред<o:p></o:p></span></b></p>
  </td>
  <td width=204 style='width:153.0pt;border:solid windowtext 1.0pt;border-left:
  none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b
  style='mso-bidi-font-weight:normal'><span lang=BG style='mso-ansi-language:
  BG'>Име<o:p></o:p></span></b></p>
  </td>
  <td width=124 style='width:92.9pt;border:solid windowtext 1.0pt;border-left:
  none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b
  style='mso-bidi-font-weight:normal'><span lang=BG style='mso-ansi-language:
  BG'>Длъжност<o:p></o:p></span></b></p>
  </td>
  <td width=124 style='width:92.9pt;border:solid windowtext 1.0pt;border-left:
  none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b
  style='mso-bidi-font-weight:normal'><span lang=BG style='mso-ansi-language:
  BG'>Дата на прегледа<o:p></o:p></span></b></p>
  </td>
 </tr>
 <?php
 $lines = array();
 foreach ($rows as $row) {
 	$lines[$row['worker_id']]['name'] = HTMLFormat($row['fname'].' '.$row['sname'].' '.$row['lname']);
 	$lines[$row['worker_id']]['position_name'] = HTMLFormat($row['position_name']);
 	$lines[$row['worker_id']]['date'] = (isset($lines[$row['worker_id']]['date'])) ? $lines[$row['worker_id']]['date'].'; '.$row['checkup_date2'] : $row['checkup_date2'];
 }

 $i = 1;
 foreach ($lines as $line) {
 ?>
 <tr style='mso-yfti-irow:<?=$i?>'>
  <td width=53 valign=top style='width:39.5pt;border:solid windowtext 1.0pt;
  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><span lang=BG
  style='mso-ansi-language:BG'><?=$i++?>.<o:p></o:p></span></p>
  </td>
  <td width=204 valign=top style='width:153.0pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><span lang=BG style='mso-ansi-language:BG'><?=$line['name']?><o:p></o:p></span></p>
  </td>
  <td width=124 valign=top style='width:92.9pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><span lang=BG style='mso-ansi-language:BG'><?=$line['position_name']?><o:p></o:p></span></p>
  </td>
  <td width=124 valign=top style='width:92.9pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><span lang=BG
  style='mso-ansi-language:BG'><?=$line['date']?><o:p></o:p></span></p>
  </td>
 </tr>
 <?php } ?>
</table>
<?php } else { ?>
 <p class=MsoNormal>Няма предоставени данни</p>
<?php } ?>

<?php w_footer($s); ?>

</div>

</body>

</html>
