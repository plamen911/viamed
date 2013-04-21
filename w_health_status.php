<?php
// http://localhost/stm2008/hipokrat/w_health_status.php?firm_id=174&offline=1
require('includes.php');

$offline = (isset($_GET['offline']) && $_GET['offline'] == '1') ? 1 : 0;

$firm_id = (isset($_GET['firm_id']) && is_numeric($_GET['firm_id'])) ? intval($_GET['firm_id']) : 0;
$f = $dbInst->getFirmInfo($firm_id);
if(!$f) {
	die('Липсва индентификатор на фирмата!');
}
$s = $dbInst->getStmInfo();

$stm_name = preg_replace('/\<br\s*\/?\>/', '', $s['stm_name']);

if(!$offline) {
	$firm_name = str_replace(' ', '_', $f['firm_name']);
	$firm_name = str_replace('"', '', $firm_name);
	$firm_name = str_replace('\'', '', $firm_name);
	$firm_name = str_replace('”', '', $firm_name);
	$firm_name = str_replace('„', '', $firm_name);
	$firm_name = str_replace('_-_', '_', $firm_name);

	require_once("cyrlat.class.php");
	$cyrlat = new CyrLat;
	$filename = 'Zdraven_Status_'.$cyrlat->cyr2lat($firm_name).'.doc';

	header("Pragma: public");
	header("Content-Disposition: attachment; filename=\"$filename\";");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	//header("Cache-Control: private", false);
	header("Content-Type: application/octet-stream");
	//header("Content-type: application/msword;");
	//$imgpath = str_replace('/','\\',str_replace(basename($_SERVER['PHP_SELF']),'',$_SERVER["SCRIPT_FILENAME"])).'img\\';
}

$sql = "SELECT w.*, strftime('%d.%m.%Y', w.birth_date, 'localtime') AS birth_date2,
		strftime('%d.%m.%Y', w.date_curr_position_start, 'localtime') AS date_curr_position_start2,
		p.wplace_name
		FROM `workers` w
		LEFT JOIN firm_struct_map m ON (m.map_id = w.map_id)
		LEFT JOIN work_places p ON (p.wplace_id = m.wplace_id)
		WHERE w.`firm_id` = $firm_id 
		AND w.`is_active` = '1' 
		AND w.date_retired = ''
		GROUP BY w.worker_id
		ORDER BY w.date_retired, w.fname, w.sname, w.lname, w.egn, w.worker_id";
$rows = $dbInst->query($sql);

$chkY = date('Y');
$chkY_0 = $chkY - 1;
$chkY_1 = $chkY_0 - 1;
$chkY_2 = $chkY_1 - 1;
$chkY_3 = $chkY_2 - 1;

function getMedicalCheckups($worker_id, $chkY) {
	global $dbInst;
	$out = '';
	$sql = "SELECT *, strftime('%d.%m.%Y', `checkup_date`, 'localtime') AS `checkup_date2` FROM `medical_checkups` WHERE `checkup_date` >= '$chkY-01-01 00:00:00' AND `checkup_date` <= '$chkY-12-31 23:59:59' AND `worker_id` = $worker_id";
	$fields = $dbInst->query($sql);
	if(!empty($fields)) {
		foreach ($fields as $field) {
			if(!empty($field['checkup_date2'])) { $out .= '<p class=MsoNormal><span style=\'font-size:10.0pt\'>'.$field['checkup_date2'].'</span></p>'; }
			$flds = $dbInst->getDoctorsDesc($field['checkup_id']);
			$i = 1;
			if(!empty($flds)) {
				foreach ($flds as $f) {
					if(!empty($f['conclusion'])) {
						$doctor_pos_name = $dbInst->my_mb_ucfirst(HTMLFormat($f['SpecialistName']));
						$doctor_pos_name = trim(mb_substr($doctor_pos_name, 0, 8));
						$doctor_pos_name = str_replace('(', '', $doctor_pos_name);
						if('Инте' == $doctor_pos_name) { $doctor_pos_name = 'Инт.'; }
						elseif('Офта' == $doctor_pos_name) { $doctor_pos_name = 'Офт.'; }
						elseif('Хиру' == $doctor_pos_name) { $doctor_pos_name = 'Хир.'; }
						$out .= '<p class=MsoNormal><span style=\'font-size:10.0pt\'>'.$i++.'. '.$doctor_pos_name.'- '.HTMLFormat($f['conclusion']).'<o:p></o:p></span></p>';
					}
				}
			} else { $out .= '<p class=MsoNormal><span style=\'font-size:10.0pt\'>- <o:p></o:p></span></p>'; }
		}
	}
	return $out;
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
<link rel=File-List href="Health_Status_04_files/filelist.xml">
<title><?=((isset($stm_name))?HTMLFormat($stm_name):'СЛУЖБА ПО ТРУДОВА МЕДИЦИНА')?></title>
<!--[if gte mso 9]><xml>
 <o:DocumentProperties>
  <o:Author><?=((isset($stm_name))?HTMLFormat($stm_name):'СЛУЖБА ПО ТРУДОВА МЕДИЦИНА')?></o:Author>
  <o:Template>Normal</o:Template>
  <o:LastAuthor><?=((isset($stm_name))?HTMLFormat($stm_name):'СЛУЖБА ПО ТРУДОВА МЕДИЦИНА')?> Markov</o:LastAuthor>
  <o:Revision>2</o:Revision>
  <o:TotalTime>16</o:TotalTime>
  <o:LastPrinted>2008-04-18T09:44:00Z</o:LastPrinted>
  <o:Created>2009-06-16T15:40:00Z</o:Created>
  <o:LastSaved>2009-06-16T15:40:00Z</o:LastSaved>
  <o:Pages>1</o:Pages>
  <o:Words>155</o:Words>
  <o:Characters>884</o:Characters>
  <o:Company>СТМ</o:Company>
  <o:Lines>7</o:Lines>
  <o:Paragraphs>2</o:Paragraphs>
  <o:CharactersWithSpaces>1037</o:CharactersWithSpaces>
  <o:Version>11.9999</o:Version>
 </o:DocumentProperties>
</xml><![endif]--><!--[if gte mso 9]><xml>
 <w:WordDocument>
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
	mso-font-signature:-520078593 -1073717157 41 0 66047 0;}
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
@page Section1
	{size:841.9pt 595.3pt;
	mso-page-orientation:landscape;
	margin:44.95pt 31.9pt 35.95pt 36.0pt;
	mso-header-margin:35.45pt;
	mso-footer-margin:35.45pt;
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
<![endif]--><!--[if gte mso 9]><xml>
 <o:shapedefaults v:ext="edit" spidmax="7170"/>
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
normal'><span style='font-size:20.0pt'>Здравен статус<o:p></o:p></span></b></p>

<p class=MsoNormal align=center style='text-align:center'><b style='mso-bidi-font-weight:
normal'><span style='font-size:14.0pt'>на работещите в <?=((isset($f['firm_name']))?HTMLFormat($f['firm_name'].' – '.$f['location_name']):'')?></span></b></p>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>

<?php if(!empty($rows)) { ?>
<table class=MsoTableGrid border=1 cellspacing=0 cellpadding=0 width="100%"
 style='width:100.0%;border-collapse:collapse;border:none;mso-border-alt:solid windowtext .5pt;
 mso-yfti-tbllook:480;mso-padding-alt:0cm 5.4pt 0cm 5.4pt;mso-border-insideh:
 .5pt solid windowtext;mso-border-insidev:.5pt solid windowtext'>
 <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes'>
  <td width="2%" rowspan=2 style='width:2.66%;border:solid windowtext 1.0pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b
  style='mso-bidi-font-weight:normal'><span style='font-size:10.0pt'>№<o:p></o:p></span></b></p>
  </td>
  <td width="9%" rowspan=2 style='width:9.02%;border:solid windowtext 1.0pt;
  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b
  style='mso-bidi-font-weight:normal'><span style='font-size:10.0pt'>Име<o:p></o:p></span></b></p>
  </td>
  <td width="3%" rowspan=2 style='width:3.8%;border:solid windowtext 1.0pt;
  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b
  style='mso-bidi-font-weight:normal'><span style='font-size:10.0pt'>Год.<o:p></o:p></span></b></p>
  </td>
  <td width="9%" rowspan=2 style='width:9.34%;border:solid windowtext 1.0pt;
  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b
  style='mso-bidi-font-weight:normal'><span style='font-size:10.0pt'>Дата на
  назнач.; <o:p></o:p></span></b></p>
  <p class=MsoNormal align=center style='text-align:center'><b
  style='mso-bidi-font-weight:normal'><span style='font-size:10.0pt'>РМ<o:p></o:p></span></b></p>
  </td>
  <td width="6%" rowspan=2 style='width:6.28%;border:solid windowtext 1.0pt;
  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b
  style='mso-bidi-font-weight:normal'><span style='font-size:10.0pt'>МЗР<o:p></o:p></span></b></p>
  </td>
  <td width="8%" rowspan=2 style='width:8.36%;border:solid windowtext 1.0pt;
  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b
  style='mso-bidi-font-weight:normal'><span style='font-size:10.0pt'>Фамилна
  анамнеза<o:p></o:p></span></b></p>
  </td>
  <td width="7%" rowspan=2 style='width:7.42%;border:solid windowtext 1.0pt;
  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b
  style='mso-bidi-font-weight:normal'><span style='font-size:10.0pt'>ВНР<o:p></o:p></span></b></p>
  </td>
  <td width="29%" colspan=4 style='width:29.68%;border:solid windowtext 1.0pt;
  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b
  style='mso-bidi-font-weight:normal'><span style='font-size:10.0pt'>Периодични
  прегледи<o:p></o:p></span></b></p>
  </td>
  <td width="7%" rowspan=2 style='width:7.08%;border:solid windowtext 1.0pt;
  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b
  style='mso-bidi-font-weight:normal'><span style='font-size:10.0pt'>ТЕЛК<o:p></o:p></span></b></p>
  </td>
  <td width="16%" colspan=2 style='width:16.36%;border:solid windowtext 1.0pt;
  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b
  style='mso-bidi-font-weight:normal'><span style='font-size:10.0pt'>Периодични
  пр. <?=$chkY?><o:p></o:p></span></b></p>
  </td>
 </tr>
 <tr style='mso-yfti-irow:1'>
  <td width="6%" style='width:6.38%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b
  style='mso-bidi-font-weight:normal'><span style='font-size:10.0pt'><?=$chkY_3?><o:p></o:p></span></b></p>
  </td>
  <td width="6%" style='width:6.4%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;mso-border-top-alt:
  solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b
  style='mso-bidi-font-weight:normal'><span style='font-size:10.0pt'><?=$chkY_2?><o:p></o:p></span></b></p>
  </td>
  <td width="9%" style='width:9.8%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;mso-border-top-alt:
  solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b
  style='mso-bidi-font-weight:normal'><span style='font-size:10.0pt'><?=$chkY_1?><o:p></o:p></span></b></p>
  </td>
  <td width="7%" style='width:7.12%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b
  style='mso-bidi-font-weight:normal'><span style='font-size:10.0pt'><?=$chkY_0?><o:p></o:p></span></b></p>
  </td>
  <td width="8%" style='width:8.06%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b
  style='mso-bidi-font-weight:normal'><span style='font-size:10.0pt'>Преминали<o:p></o:p></span></b></p>
  </td>
  <td width="8%" style='width:8.3%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;mso-border-top-alt:
  solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b
  style='mso-bidi-font-weight:normal'><span style='font-size:10.0pt'>Подлежащи<o:p></o:p></span></b></p>
  </td>
 </tr>
  <?php $i = 1; foreach ($rows as $row) { ?>
 <tr style='mso-yfti-irow:<?=($i+1)?>;mso-yfti-lastrow:yes'>
  <td width="2%" valign=top style='width:2.66%;border:solid windowtext 1.0pt;
  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><span style='font-size:10.0pt'><?=$i++?>.<o:p></o:p></span></p>
  </td>
  <td width="9%" valign=top style='width:9.02%;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><span style='font-size:10.0pt'><?=HTMLFormat(trim($row['fname'].' '.$row['sname'].' '.$row['lname']))?><o:p></o:p></span></p>
  </td>
  <td width="3%" valign=top style='width:3.8%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><span style='font-size:10.0pt'><?=((!empty($row['birth_date2']))?worker_age($row['birth_date2'], date("d.m.Y")).' г.':'')?><o:p></o:p></span></p>
  </td>
  <td width="9%" valign=top style='width:9.34%;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <?php if(!empty($row['date_curr_position_start2'])) { ?>
  <p class=MsoNormal><span style='font-size:10.0pt'><?=$row['date_curr_position_start2']?>;<o:p></o:p></span></p>
  <?php } if(!empty($row['p.wplace_name'])) { ?>
  <p class=MsoNormal><span style='font-size:10.0pt'><?=$row['p.wplace_name']?><o:p></o:p></span></p>
  <?php } else { echo '<p class=MsoNormal><span style=\'font-size:10.0pt\'><o:p>&nbsp;</o:p></span></p>'; } ?></td>
  <td width="6%" valign=top style='width:6.28%;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'><?php

  // Get the last preliminary checkup ID
  //prchk_date
  $sql = "SELECT precheckup_id, prchk_date FROM medical_precheckups WHERE worker_id = $row[worker_id] LIMIT 1";
  $fld = $dbInst->fnSelectSingleRow($sql);
  if(!empty($fld)) {
  	$precheckup_id = $fld['precheckup_id'];
  	$prchk_date = (!empty($fld['prchk_date']) && false !== $ts = strtotime($fld['prchk_date'])) ? date('d.m.Y', $ts) : '';
  	echo (!empty($prchk_date)) ? '<p class=MsoNormal><span style=\'font-size:10.0pt\'>'.$prchk_date.'<o:p></o:p></span></p>' : '';
  	$precheckup_id = $dbInst->GiveValue('precheckup_id', 'medical_precheckups', "WHERE worker_id = $row[worker_id] LIMIT 1", 0);
  	$sql = "SELECT s.SpecialistName AS SpecialistName , c.conclusion AS conclusion , c.SpecialistID AS SpecialistID
			FROM medical_precheckups_doctors2 c
			LEFT JOIN Specialists s ON ( s.SpecialistID = c.SpecialistID )
			WHERE c.precheckup_id = $precheckup_id
			ORDER BY s.SpecialistName , s.SpecialistID";
  	$conclusions = $dbInst->query($sql);
  	if($conclusions) {
  		foreach ($conclusions as $fld) {
  			echo '<p class=MsoNormal><span style=\'font-size:10.0pt\'>'.$fld['SpecialistName'].': '.HTMLFormat($fld['conclusion']).'<o:p></o:p></span></p>';
  		}
  	}
  }
  // Check for older precheckups
  $sql = "SELECT strftime('%d.%m.%Y', `prchk_date`, 'localtime') AS `prchk_date2` FROM `medical_precheckups` WHERE `worker_id` = $row[worker_id] ORDER BY `prchk_date` DESC, `precheckup_id` DESC LIMIT 1, -1";
  $lines = $dbInst->query($sql);
  if(!empty($lines)) {
  	$ary = array();
  	foreach ($lines as $line) {
  		$ary[] = $line['prchk_date2'];
  	}
  	echo '<p class=MsoNormal><span style=\'font-size:10.0pt\'>('.implode('; ', $ary).')<o:p></o:p></span></p>';
  }
  ?></td>
  <td width="8%" valign=top style='width:8.36%;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'><?php

  $flds = $dbInst->query("	SELECT *,
							strftime('%d.%m.%Y', checkup_date, 'localtime') AS checkup_date_h
							FROM medical_checkups
							WHERE worker_id = '$row[worker_id]'
							ORDER BY checkup_date DESC, checkup_id DESC");
  if(!empty($flds)) {
  	foreach ($flds as $f) {
  		$ary = array();
  		$rs = $dbInst->getFamilyWeights($f['checkup_id']);
  		foreach ($rs as $r) {
  			$ary[] = $r['mkb_id'];
  		}
  		if(count($ary)) {
  			echo '<p class=MsoNormal><span style=\'font-size:10.0pt\'>'.$f['checkup_date_h'].' - '.implode(', ', $ary).'<o:p></o:p></span></p>';
  		}
  	}
  }

  /*
  $j = 1;
  if(!empty($row['family_hypertonia'])) { echo '<p class=MsoNormal><span style=\'font-size:10.0pt\'>'.$j++.'. Хипертония: '.HTMLFormat($row['family_hypertonia']).'<o:p></o:p></span></p>'; }
  if(!empty($row['family_heart_disease'])) { echo '<p class=MsoNormal><span style=\'font-size:10.0pt\'>'.$j++.'. Бол. на сърцето: '.HTMLFormat($row['family_heart_disease']).'<o:p></o:p></span></p>'; }
  if(!empty($row['family_diabetis'])) { echo '<p class=MsoNormal><span style=\'font-size:10.0pt\'>'.$j++.'. Зах. болест: '.HTMLFormat($row['family_diabetis']).'<o:p></o:p></span></p>'; }
  if(!empty($row['family_other_disease'])) { echo '<p class=MsoNormal><span style=\'font-size:10.0pt\''.$j++.'. >Други заб.: '.HTMLFormat($row['family_other_disease']).'<o:p></o:p></span></p>'; }*/
  ?></td>
  <td width="7%" valign=top style='width:7.42%;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'><?php
  $fields = $dbInst->query("SELECT * FROM `patient_charts` WHERE `worker_id` = $row[worker_id] ORDER BY `hospital_date_from`, `mkb_id`, `days_off`");
  if(!empty($fields)) {
  	$j = 1;
  	foreach ($fields as $field) {
  		echo '<p class=MsoNormal><span style=\'font-size:10.0pt\'>'.$j++.'. '.date('y', strtotime($field['hospital_date_from'])).' /'.$field['mkb_id'].' /'.$field['days_off'].'/'.$field['reason_id'].'<o:p></o:p></span></p>';
  	}
  } else { echo '<p class=MsoNormal><span style=\'font-size:10.0pt\'>-<o:p></o:p></span></p>'; }
  ?></td>
  <td width="6%" valign=top style='width:6.38%;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'><?=getMedicalCheckups($row['worker_id'], $chkY_3)?></td>
  <td width="6%" valign=top style='width:6.4%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'><?=getMedicalCheckups($row['worker_id'], $chkY_2)?></td>
  <td width="9%" valign=top style='width:9.8%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'><?=getMedicalCheckups($row['worker_id'], $chkY_1)?></td>
  <td width="7%" valign=top style='width:7.12%;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'><?=getMedicalCheckups($row['worker_id'], $chkY_0)?></td>
  <td width="7%" valign=top style='width:7.08%;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'><?php
  $sql = "SELECT * FROM `telks` WHERE `worker_id` = $row[worker_id] ORDER BY `telk_date_from`, `telk_duration`";
  $fields = $dbInst->query($sql);
  if(!empty($fields)) {
  	$j = 1;
  	foreach ($fields as $field) {
  		echo '<p class=MsoNormal><span style=\'font-size:10.0pt\'>'.$j++.'. ';
  		echo (!empty($field['telk_date_from'])) ? date('d.m.y', strtotime($field['telk_date_from'])) : '';
  		//echo (!empty($field['first_inv_date'])) ? date('d.m.y', strtotime($field['first_inv_date'])) : date('d.m.y', strtotime($field['telk_date_from']));
  		echo (!empty($field['telk_date_to'])) ? ' - '.date('d.m.y', strtotime($field['telk_date_to'])) : '';
  		echo (!empty($field['percent_inv'])) ? ' / '.$field['percent_inv'].'%' : '';
  		$mkb_array = array();
  		if(!empty($field['mkb_id_1'])) { $mkb_array[] = $field['mkb_id_1']; }
  		if(!empty($field['mkb_id_2'])) { $mkb_array[] = $field['mkb_id_2'].' (01)'; }
  		if(!empty($field['mkb_id_3'])) { $mkb_array[] = $field['mkb_id_3'].' (04)'; }
  		if(!empty($field['mkb_id_4'])) { $mkb_array[] = $field['mkb_id_4'].' (02)'; }
  		echo (count($mkb_array)) ? ' / '.implode(';', $mkb_array) : '';
  		echo '<o:p></o:p></span></p>';
  	}
  } else { echo '<p class=MsoNormal><span style=\'font-size:10.0pt\'>-<o:p></o:p></span></p>'; }
  ?></td>
  <td width="8%" valign=top style='width:8.06%;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'><?=getMedicalCheckups($row['worker_id'], $chkY)?></td>
  <td width="8%" valign=top style='width:8.3%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal><span style='font-size:10.0pt'><o:p>&nbsp;</o:p></span></p></td>
 </tr>
 <?php } ?>
</table>
<?php } ?>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>

<p class=MsoNormal><span style='font-size:14.0pt'><o:p>&nbsp;</o:p></span></p>

<p class=MsoNormal><span style='font-size:14.0pt'><?=date('d.m.Y')?> г.<span
style='mso-tab-count:6'>                                                      </span><span
style='mso-tab-count:7'>                                                                  </span><span
style='mso-tab-count:1'>         </span><span style='mso-tab-count:1'>         </span>Ръководител
СТМ:<o:p></o:p></span></p>

<p class=MsoNormal align=right style='text-align:right'><span style='font-size:
14.0pt'>(<?=HTMLFormat($s['chief'])?>)<o:p></o:p></span></p>

</div>

</body>

</html>
