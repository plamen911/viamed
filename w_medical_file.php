<?php
// Test: http://localhost/stm2008/viamed/w_medical_file.php?worker_id=18864&offline=1

require('includes.php');

$offline = (isset($_GET['offline']) && $_GET['offline'] == '1') ? 1 : 0;

$worker_id = (isset($_GET['worker_id']) && is_numeric($_GET['worker_id'])) ? intval($_GET['worker_id']) : 0;
$f = $dbInst->getWorkerInfo($worker_id);
if(!$f) {
	die('Липсва индентификатор на работещия!');
}

$s = $dbInst->getStmInfo();
$firm = $dbInst->getFirmInfo($f['firm_id']);
$dbInst->makeAllMkbUpperCase();

$stm_name = preg_replace('/\<br\s*\/?\>/', '', $s['stm_name']);

$unchecked = 'unchecked.gif';
$checked = 'checked.gif';
$imgpath = "http://" . ((isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:$_SERVER['SERVER_ADDR'].':'.$_SERVER['SERVER_PORT'])) . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/img/";

if($offline) {
	$firm_name = str_replace(' ', '_', $firm['name']);
	$firm_name = str_replace('"', '', $firm_name);
	$firm_name = str_replace('\'', '', $firm_name);
	$firm_name = str_replace('”', '', $firm_name);
	$firm_name = str_replace('„', '', $firm_name);
	$firm_name = str_replace('_-_', '_', $firm_name);

	$worker_name = str_replace(' ', '_', (mb_substr($f['fname'], 0, 1, 'utf-8').' '.$f['lname']));

	require_once("cyrlat.class.php");
	$cyrlat = new CyrLat;
	$filename = 'Zdravno_dosie_'.$cyrlat->cyr2lat($worker_name.'_'.$firm_name).'-'.$f['worker_id'].'.doc';

	header("Pragma: public");
	header("Content-Disposition: attachment; filename=\"$filename\";");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	//header("Cache-Control: private", false);
	header("Content-Type: application/octet-stream");
	//header("Content-type: application/msword;");
	//$imgpath = str_replace('/','\\',str_replace(basename($_SERVER['PHP_SELF']),'',$_SERVER["SCRIPT_FILENAME"])).'img\\';
}

$i = 1;

?><html xmlns:v="urn:schemas-microsoft-com:vml"
xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:w="urn:schemas-microsoft-com:office:word"
xmlns="http://www.w3.org/TR/REC-html40">

<head>
<meta http-equiv=Content-Type content="text/html; charset=utf-8">
<meta name=ProgId content=Word.Document>
<meta name=Generator content="Microsoft Word 11">
<meta name=Originator content="Microsoft Word 11">
<!--<link rel=File-List href="medical_file_02_files/filelist.xml">
<link rel=Edit-Time-Data href="medical_file_02_files/editdata.mso">-->
<!--[if !mso]>
<style>
v\:* {behavior:url(#default#VML);}
o\:* {behavior:url(#default#VML);}
w\:* {behavior:url(#default#VML);}
.shape {behavior:url(#default#VML);}
</style>
<![endif]-->
<title><?=((isset($stm_name))?HTMLFormat($stm_name):'СЛУЖБА ПО ТРУДОВА МЕДИЦИНА')?></title>
<!--[if gte mso 9]><xml>
 <o:DocumentProperties>
  <o:Author>Plamen</o:Author>
  <o:Template>Normal</o:Template>
  <o:LastAuthor>Plamen</o:LastAuthor>
  <o:Revision>34</o:Revision>
  <o:TotalTime>251</o:TotalTime>
  <o:LastPrinted>2008-04-18T09:44:00Z</o:LastPrinted>
  <o:Created>2008-06-19T20:08:00Z</o:Created>
  <o:LastSaved>2008-06-19T20:37:00Z</o:LastSaved>
  <o:Pages>1</o:Pages>
  <o:Words>762</o:Words>
  <o:Characters>4350</o:Characters>
  <o:Company>СТМ</o:Company>
  <o:Lines>36</o:Lines>
  <o:Paragraphs>10</o:Paragraphs>
  <o:CharactersWithSpaces>5102</o:CharactersWithSpaces>
  <o:Version>11.5606</o:Version>
 </o:DocumentProperties>
</xml><![endif]--><!--[if gte mso 9]><xml>
 <w:WordDocument>
  <w:View>Print</w:View>
  <w:Zoom>105</w:Zoom>
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
 /* List Definitions */
 @list l0
	{mso-list-id:1178735115;
	mso-list-type:hybrid;
	mso-list-template-ids:-2004087272 2094595962 67239961 67239963 67239951 67239961 67239963 67239951 67239961 67239963;}
@list l0:level1
	{mso-level-tab-stop:36.0pt;
	mso-level-number-position:left;
	text-indent:-18.0pt;
	mso-ansi-font-weight:bold;}
@list l0:level2
	{mso-level-tab-stop:72.0pt;
	mso-level-number-position:left;
	text-indent:-18.0pt;}
@list l0:level3
	{mso-level-tab-stop:108.0pt;
	mso-level-number-position:left;
	text-indent:-18.0pt;}
@list l0:level4
	{mso-level-tab-stop:144.0pt;
	mso-level-number-position:left;
	text-indent:-18.0pt;}
@list l0:level5
	{mso-level-tab-stop:180.0pt;
	mso-level-number-position:left;
	text-indent:-18.0pt;}
@list l0:level6
	{mso-level-tab-stop:216.0pt;
	mso-level-number-position:left;
	text-indent:-18.0pt;}
@list l0:level7
	{mso-level-tab-stop:252.0pt;
	mso-level-number-position:left;
	text-indent:-18.0pt;}
@list l0:level8
	{mso-level-tab-stop:288.0pt;
	mso-level-number-position:left;
	text-indent:-18.0pt;}
@list l0:level9
	{mso-level-tab-stop:324.0pt;
	mso-level-number-position:left;
	text-indent:-18.0pt;}
@list l1
	{mso-list-id:1249771650;
	mso-list-type:hybrid;
	mso-list-template-ids:917685260 -1923855076 -1247252138 -1848992194 990307432 -93304986 -1528002426 87060344 -1324040920 389848682;}
@list l1:level1
	{mso-level-number-format:image;
	list-style-image:url("medical_file_02_files/image002.gif");
	mso-level-text:;
	mso-level-tab-stop:36.0pt;
	mso-level-number-position:left;
	text-indent:-18.0pt;
	font-family:Symbol;}
@list l2
	{mso-list-id:1341666871;
	mso-list-type:hybrid;
	mso-list-template-ids:760355828 -373292292 -119223386 579644974 -889176536 -645637596 1892703864 -1381754582 -1099244570 -1142100316;}
@list l2:level1
	{mso-level-number-format:image;
	list-style-image:url("medical_file_02_files/image002.gif");
	mso-level-text:;
	mso-level-tab-stop:36.0pt;
	mso-level-number-position:left;
	text-indent:-18.0pt;
	font-family:Symbol;}
@list l3
	{mso-list-id:1720519433;
	mso-list-type:hybrid;
	mso-list-template-ids:-132379704 959471686 -1230440080 -960330550 -1416229214 -1523530840 -1253810400 -1320243918 -998869084 1946345508;}
@list l3:level1
	{mso-level-number-format:image;
	list-style-image:url("medical_file_02_files/image001.gif");
	mso-level-text:;
	mso-level-tab-stop:36.0pt;
	mso-level-number-position:left;
	text-indent:-18.0pt;
	font-family:Symbol;}
ol
	{margin-bottom:0cm;}
ul
	{margin-bottom:0cm;}
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
table.a0
	{mso-style-name:"Мрежа в таблица";
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

</div>

<p class=MsoNormal align=right style='text-align:right'>Приложение № 6 към чл.
11, ал.10</p>

<p class=MsoNormal align=center style='text-align:center'><b><span
style='font-size:14.0pt'><o:p>&nbsp;</o:p></span></b></p>

<p class=MsoNormal align=center style='text-align:center'><b><span
style='font-size:14.0pt'>ЗДРАВНО ДОСИЕ</span>&nbsp;<o:p></o:p></b></p>

<p class=MsoNormal align=center style='text-align:center'><span
style='font-size:14.0pt'><o:p>&nbsp;</o:p></span></p>

<p class=MsoNormal><b>І. Паспортна част</b></p>

<p class=MsoNormal align=center style='text-align:center'><b style='mso-bidi-font-weight:
normal'><span style='font-size:14.0pt'><?=((isset($f))?HTMLFormat($f['fname'].' '.$f['sname'].' '.$f['lname']):'')?><span
style='mso-spacerun:yes'>  </span><o:p></o:p></span></b></p>

<p class=MsoNormal>ЕГН <?=((isset($f))?HTMLFormat($f['egn']).', ':'')?><?php if(isset($f) && (''!=$f['location_name']||''!=$f['address'])) { ?>постоянен адрес: <?=((isset($f['province_name'])&&$f['province_name']!='')?'област '.HTMLFormat($f['province_name']).', ':'')?><?=((isset($f['c.community_name'])&&$f['c.community_name']!='')?'община  '.HTMLFormat($f['c.community_name']).', ':'')?><?=((isset($f['location_type']))?(($f['location_type']=='1')?'гр.':'с.'):'')?> <?=((isset($f['location_name'])&&$f['location_name']!='')?HTMLFormat($f['location_name']).', ':'')?><?=((isset($f))?HTMLFormat($f['address']):'')?><?php } ?></p>

<?php if(isset($f) && ''!=$f['doctor_name']) { ?><p class=MsoNormal style='text-align:justify'>Личен лекар: <?=HTMLFormat($f['doctor_name'])?>,
адрес на регистрираната практика: <?=HTMLFormat($f['doctor_address'])?><?=(($f['doctor_phone']!='')?', тел. '.HTMLFormat($f['doctor_phone']):'')?><?=((isset($f['doctor_phone2']))?', '.HTMLFormat($f['doctor_phone2']):'')?></p><?php } ?>

<p class=MsoNormal>&nbsp;</p>

<p class=MsoNormal><b>ІІ. Професионален маршрут<o:p></o:p></b></p>

<ol style='margin-top:0cm' start=1 type=1>
 <li class=MsoNormal style='mso-list:l0 level1 lfo1;tab-stops:list 36.0pt'><b>Настоящ: </b><span style='mso-bidi-font-weight:bold'><?=mb_strtoupper(HTMLFormat($f['i.position_name']), 'utf-8')?> – от <?=(($f['date_curr_position_start2']!='')?$f['date_curr_position_start2'].' г.':'')?><o:p></o:p></span></li>
 <li class=MsoNormal style='mso-list:l0 level1 lfo1;tab-stops:list 36.0pt'><b>Преди:</b></li>
</ol>

<?php
$rows = $dbInst->getProRoute($worker_id);
if($rows) {
?>
<p class=MsoNormal align=center style='text-align:center'>&nbsp;</p>
<table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 width="100%"
 style='width:100.0%;border-collapse:collapse;mso-padding-alt:0cm 5.4pt 0cm 5.4pt'>
 <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes'>
  <td width=205 style='width:153.5pt;border:solid windowtext 1.0pt;padding:
  0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'>Предприятие:</p>
  </td>
  <td width=205 style='width:153.55pt;border:solid windowtext 1.0pt;border-left:
  none;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'>Длъжност/професия</p>
  </td>
  <td width=205 style='width:153.55pt;border:solid windowtext 1.0pt;border-left:
  none;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'>Продължителност на стажа</p>
  </td>
 </tr>
 <?php
 $i = 1;
 foreach ($rows as $row) {
 ?>
 <tr style='mso-yfti-irow:<?=$i?>'>
  <td width=205 valign=top style='width:153.5pt;border:solid windowtext 1.0pt;
  border-top:none;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><?=$i++?>. <?=HTMLFormat($row['firm_name'])?></p>
  </td>
  <td width=205 valign=top style='width:153.55pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><?=HTMLFormat($row['position'])?></p>
  </td>
  <td width=205 valign=top style='width:153.55pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><?=(($row['exp_length_y'])?HTMLFormat($row['exp_length_y']).' г.':'')?> <?=(($row['exp_length_m'])?HTMLFormat($row['exp_length_m']).' м.':'')?></p>
  </td>
 </tr>
 <?php } ?>
</table>
<?php } else { ?>
<p class=MsoNormal>Не са предоставени данни</p>
<?php } ?>

<?php
$flds = $dbInst->query("SELECT * FROM readjustments WHERE worker_id = $worker_id ORDER BY id");
if(!empty($flds)) {
	?>
  <ol style='margin-top:0cm' start=3 type=1>
	<li class=MsoNormal style='mso-list:l0 level1 lfo1;tab-stops:list 36.0pt'><b>Трудоустрояване:</b></li>
  </ol>
  <div align=center>
    <table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 width="100%"
 style='width:100.0%;border-collapse:collapse;mso-padding-alt:0cm 0cm 0cm 0cm'>
      <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes'>
        <td width=102 style='width:76.75pt;border:solid windowtext 1.0pt;padding:
  0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:11.0pt'>Дата</span></p></td>
        <td width=102 style='width:76.75pt;border:solid windowtext 1.0pt;border-left:
  none;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:11.0pt'>МКБ</span></p></td>
        <td width=102 style='width:76.75pt;border:solid windowtext 1.0pt;border-left:
  none;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:11.0pt'>Диагноза</span></p></td>
        <td width=102 style='width:76.75pt;border:solid windowtext 1.0pt;border-left:
  none;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:11.0pt'>Комисия</span></p></td>
        <td width=102 style='width:76.8pt;border:solid windowtext 1.0pt;border-left:
  none;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:11.0pt'>Срок</span></p></td>
        <td width=102 style='width:76.8pt;border:solid windowtext 1.0pt;border-left:
  none;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:11.0pt'>Място на трудоустрояване</span></p></td>
      </tr>
      <?php
      foreach ($flds as $i => $fld) { ?>
      <tr style='mso-yfti-irow:<?=($i+1)?>;<?=(((count($fld) - 1) == $i) ? 'mso-yfti-lastrow:yes' : '')?>'>
        <td width=102 style='width:76.75pt;border:solid windowtext 1.0pt;border-top:
  none;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:11.0pt'><?=((!empty($fld['published_on']) && false !== $ts = strtotime($fld['published_on'])) ? date('d.m.Y', $ts).' г.' : '')?></span></p></td>
        <td width=102 style='width:76.75pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:11.0pt'><?=HTMLFormat($fld['mkb_id'])?></span></p></td>
        <td width=102 style='width:76.75pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:11.0pt'><?=HTMLFormat($fld['diagnosis'])?></span></p></td>
        <td width=102 style='width:76.75pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:11.0pt'><?=HTMLFormat($fld['commission'])?></span></p></td>
        <td width=102 style='width:76.8pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:11.0pt'><?=((!empty($fld['start_date']) && false !== $ts = strtotime($fld['start_date'])) ? 'от '.date('d.m.Y', $ts) : '')?><?=((!empty($fld['end_date']) && false !== $ts = strtotime($fld['end_date'])) ? ' до '.date('d.m.Y', $ts).' г.' : '')?></span></p></td>
        <td width=102 style='width:76.8pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:11.0pt'><?=HTMLFormat($fld['place'])?></span></p></td>
      </tr>
      <?php } ?>
    </table>
  </div>
	<?php
}
?>


<p class=MsoNormal align=center style='text-align:center'><b>&nbsp;</b></p>

<p class=MsoNormal><b>ІІІ. Данни за
регистрирани професионални болести, трудови злополуки, трудоустрояване и за трайно
намалена работоспособност<o:p></o:p></b></p>

<p class=MsoNormal>&nbsp;</p>

<p class=MsoNormal>1. Регистрирани професионални болести по данни на работещия
и/или работодателя:</p>
<?php
$flag = 0;
$rows = $dbInst->getWorkerTelkTypes($worker_id, '4');
if($rows) {
	$flag = 1;
	foreach ($rows as $row) {
?>
<p class=MsoNormal>- Експертно решение на ТЕЛК № <?=$row['telk_num'].'/'.$row['telk_date_from2']?> г., диагноза: <?=$row['mkb_id']?> – <?=HTMLFormat($row['mkb_desc'])?></p>
<?php
	}
}
// Get professional sickness patient's charts
$rows = $dbInst->getPatientCharts($worker_id, array('02', '03'));
if($rows) {
	$flag = 1;
	foreach ($rows as $row) {
?>
<p class=MsoNormal>- Болничен лист от <?=$row['hospital_date_from']?> г., диагноза: <?=$row['mkb_id']?> – <?=HTMLFormat($row['mkb_desc'])?>, причина: <?=HTMLFormat($row['reason_desc'])?></p>
<?php
	}
}
if(!$flag) {
?>
<p class=MsoNormal>Не са предоставени данни</p>
<?php } ?>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>

<p class=MsoNormal>2. Трудови злополуки по данни на работещия и/или
работодателя:</p>
<?php
$flag = 0;
$rows = $dbInst->getWorkerTelkTypes($worker_id, '3');
if($rows) {
	$flag = 1;
	foreach ($rows as $row) {
?>
<p class=MsoNormal>- Експертно решение на ТЕЛК № <?=$row['telk_num'].'/'.$row['telk_date_from2']?> г., диагноза: <?=$row['mkb_id']?> – <?=HTMLFormat($row['mkb_desc'])?></p>
<?php
	}
}
// Get work accidents patient's charts
$rows = $dbInst->getPatientCharts($worker_id, array('04', '05'));
if($rows) {
	$flag = 1;
	foreach ($rows as $row) {
?>
<p class=MsoNormal>- Болничен лист от <?=$row['hospital_date_from']?> г., диагноза: <?=$row['mkb_id']?> – <?=HTMLFormat($row['mkb_desc'])?>, причина: <?=HTMLFormat($row['reason_desc'])?></p>
<?php
	}
}
if(!$flag) {
?>
<p class=MsoNormal>Не са предоставени данни</p>
<?php } ?>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>

<p class=MsoNormal>3. Трудоустрояване по данни на работещия и/или работодателя:</p>
<?php
$flag = 0;
$rows = $dbInst->getPatientTelks($worker_id, '50down');
if($rows) {
	$flag = 1;
	foreach ($rows as $row) {
?>
<p class=MsoNormal>- Експертно решение на ТЕЛК № <?=$row['telk_num'].'/'.$row['telk_date_from_h']?> г., диагноза: <?=$row['mkb_id']?> – <?=HTMLFormat($row['mkb_desc'])?></p>
<?php
	}
}
// transfer to a more appropriate job (for reasons of health).
$rows = $dbInst->getPatientCharts($worker_id, array('16'));
if($rows) {
	$flag = 1;
	foreach ($rows as $row) {
?>
<p class=MsoNormal>- Болничен лист от <?=$row['hospital_date_from']?> г., диагноза: <?=$row['mkb_id']?> – <?=HTMLFormat($row['mkb_desc'])?>, причина: <?=HTMLFormat($row['reason_desc'])?></p>
<?php
	}
}
if(!$flag) {
?>
<p class=MsoNormal>Не са предоставени данни</p>
<?php } ?>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>

<p class=MsoNormal>4. Трайно намалена работоспособност по данни на работещия
и/или работодателя:</p>
<?php
$rows = $dbInst->getPatientTelks($worker_id, '50up');
if($rows) {
	$i = 1;
	foreach ($rows as $row) {
?>
<p class=MsoNormal>4.<?=$i++?>. Експертно решение на ТЕЛК № <?=$row['telk_num'].'/'.$row['telk_date_from_h']?>/ г., диагноза: <?=$row['mkb_id']?> – <?=HTMLFormat($row['mkb_desc'])?></p>

<p class=MsoNormal>Срок: до <b><?=$row['telk_date_to_h']?></b> г. за <?=$row['telk_duration']?>, % загубена работоспособност: <b><?=$row['percent_inv']?> %</b></p>

<p class=MsoNormal><img width=13 height=13 src="<?=$imgpath.(($row['percent_inv']>90)?$checked:$unchecked)?>" alt="*">&nbsp;над 90 % &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img width=13 height=13 src="<?=$imgpath.(($row['percent_inv']>70&&$row['percent_inv']<=90)?$checked:$unchecked)?>" alt="*">&nbsp;от 71 – 90 % &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img width=13 height=13 src="<?=$imgpath.(($row['percent_inv']>=50&&$row['percent_inv']<=70)?$checked:$unchecked)?>" alt="*">&nbsp;от 50 – 70 %</p>
<?php }} else { ?>
<p class=MsoNormal>Не са предоставени данни</p>
<?php } ?>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>

<?php
$charts = $dbInst->getPatientCharts($worker_id);
if($charts) {
	?>
<p class=MsoNormal>5. ВНР</p>

<div align=center>

<table class=MsoTableGrid border=1 cellspacing=0 cellpadding=0 width="100%"
 style='width:100.0%;border-collapse:collapse;border:none;mso-border-alt:solid windowtext .5pt;
 mso-yfti-tbllook:480;mso-padding-alt:0cm 5.4pt 0cm 5.4pt;mso-border-insideh:
 .5pt solid windowtext;mso-border-insidev:.5pt solid windowtext'>
 <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes'>
  <td width="20%" valign=top style='width:20.0%;border:solid windowtext 1.0pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b
  style='mso-bidi-font-weight:normal'>МКБ<o:p></o:p></b></p>
  </td>
  <td width="20%" valign=top style='width:20.0%;border:solid windowtext 1.0pt;
  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b
  style='mso-bidi-font-weight:normal'>Причина<o:p></o:p></b></p>
  </td>
  <td width="20%" valign=top style='width:20.0%;border:solid windowtext 1.0pt;
  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b
  style='mso-bidi-font-weight:normal'>Вид<o:p></o:p></b></p>
  </td>
  <td width="20%" valign=top style='width:20.0%;border:solid windowtext 1.0pt;
  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b
  style='mso-bidi-font-weight:normal'>Брой дни<o:p></o:p></b></p>
  </td>
  <td width="20%" valign=top style='width:20.0%;border:solid windowtext 1.0pt;
  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b
  style='mso-bidi-font-weight:normal'>От дата<o:p></o:p></b></p>
  </td>
 </tr>
	<?php
	$i = 1;
	$num_rows = count($charts);
	foreach ($charts as $row) {
		if(!($medical_types_arr = @unserialize($row['medical_types']))) {
			$medical_types_arr = array();
		}
		$chart_types = $dbInst->getChartTypes();
		$medical_types = null;
		if($chart_types) {
			foreach ($chart_types as $chart_type) {
				if(!is_array($medical_types_arr))
				continue;
				if(in_array($chart_type['type_id'], $medical_types_arr)) {
					$medical_types[] = $chart_type['type_desc_short'];
					//$medical_types[] = $chart_type['type_desc'];
				}
			}
		}
		?>
 <tr style='mso-yfti-irow:<?=$i?><?=(($num_rows==$i)?';mso-yfti-lastrow:yes':'')?>'>
  <td width="20%" valign=top style='width:20.0%;border:solid windowtext 1.0pt;
  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><?=$row['mkb_id']?></p>
  </td>
  <td width="20%" valign=top style='width:20.0%;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><?=$row['reason_id']?></p>
  </td>
  <td width="20%" valign=top style='width:20.0%;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><?=(($medical_types != null) ? implode('<br />', $medical_types) : '')?></p>
  </td>
  <td width="20%" valign=top style='width:20.0%;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><?=$row['days_off']?></p>
  </td>
  <td width="20%" valign=top style='width:20.0%;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><?=$row['hospital_date_from']?></p>
  </td>
 </tr>
		<?php
		$i++;
	}
	?>
</table>

</div>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>
	<?php
}

?>

<p class=MsoNormal align=center style='text-align:center'><b>ІV. Условия на
труд и данни от проведени предварителни и периодични медицински прегледи и
изследвания по време на работата на работещия</b></p>

<p class=MsoNormal align=center style='text-align:center'>в <?=HTMLFormat($firm['name'])?> –
<?=HTMLFormat($firm['location_name'])?>, <?=HTMLFormat($firm['address'])?></p>

<p class=MsoNormal>&nbsp;</p>

<p class=MsoNormal>1. Данни за изпълняваната в предприятието длъжност/професия,
работното място и условията на труд</p>

<p class=MsoNormal>1.1. Длъжност: <b style='mso-bidi-font-weight:normal'><?=$dbInst->my_mb_ucfirst(HTMLFormat($f['position_name']))?><o:p></o:p></b></p>

<p class=MsoNormal>1.2. Работно място: <b style='mso-bidi-font-weight:normal'><?=$dbInst->my_mb_ucfirst(HTMLFormat($f['wplace_name']))?></b></p>

<p class=MsoNormal>1.3. Условия на труд при длъжност/професия по т. 1.1 и
работно място по т. 1.2</p>

<p class=MsoNormal>1.3.1. Кратко описание на извършваната дейност:</p>

<?php $i = 1; if($f['position_workcond'] != '') { ?>
<p class=MsoNormal>1.3.1.<?=$i++?>. <?=HTMLFormat($f['position_workcond'])?></p>
<?php } if($f['position_workcond'] != '') { ?>
<p class=MsoNormal>1.3.1.<?=$i++?>. <?=HTMLFormat($f['wplace_workcond'])?></p>
<?php } ?>

<?php
$rows = $dbInst->getWorkEnvProtocols($f['firm_id'], $f['subdivision_id'], $f['wplace_id']);
if($rows) {
?>
<p class=MsoNormal>1.3.2. Фактори на работната среда и трудовия процес</p>
<table class=MsoTableGrid border=1 cellspacing=0 cellpadding=0 width="100%"
 style='width:100.0%;margin-left:1.9pt;border-collapse:collapse;border:none;
 mso-border-alt:solid windowtext .5pt;mso-yfti-tbllook:480;mso-padding-alt:
 0cm 5.4pt 0cm 5.4pt;mso-border-insideh:.5pt solid windowtext;mso-border-insidev:
 .5pt solid windowtext'>
 <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes'>
  <td width=154 style='width:115.15pt;border:solid windowtext 1.0pt;mso-border-alt:
  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b>Показател</b><o:p></o:p></p>
  </td>
  <td width=154 style='width:115.15pt;border:solid windowtext 1.0pt;border-left:
  none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b>№ и дата на протокола</b><o:p></o:p></p>
  </td>
  <td width=154 style='width:115.15pt;border:solid windowtext 1.0pt;border-left:
  none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b>Установени норми</b><o:p></o:p></p>
  </td>
  <td width=154 style='width:115.15pt;border:solid windowtext 1.0pt;border-left:
  none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b>Гранични</b><o:p></o:p></p>
  </td>
 </tr>
 <?php
 function compare_prot_date($a, $b) { return strnatcmp($b['prot_date'], $a['prot_date']); }
 # http://www.the-art-of-web.com/php/sortarray/
 # sort alphabetically by protocol date
 usort($rows, 'compare_prot_date');

 $prots = array();
 foreach ($rows as $num => $row):
 $rows[$num]['factor_name'] = HTMLFormat($row['factor_name']);
 $rows[$num]['prot_num'] = $row['prot_num'].(($row['prot_date_h'] != '') ? '/'.$row['prot_date_h'].' г.' : '');
 $rows[$num]['prot_norms'] = HTMLFormat($row['level']).' '.HTMLFormat($row['factor_dimension']);
 $rows[$num]['prot_data'] = (($row['pdk_min'] != '') ? HTMLFormat($row['pdk_min']) : '').(($row['pdk_max'] != '') ? ' - '.HTMLFormat($row['pdk_max']) : '').' '.HTMLFormat($row['factor_dimension']);
 if(empty($row['prot_date'])) $rows[$num]['prot_date'] = '0000-00-00';
 //$prots[$rows[$num]['prot_num'].'_'.$row['factor_id']][] = $rows[$num];
 //$prots[$rows[$num]['prot_num']][] = $rows[$num];
 $suffix = $row['factor_name'];
 if(preg_match('/^(.*?)\s+/i', $row['factor_name'], $matches)) $suffix = $matches[1];
 $prots[$rows[$num]['prot_num'].'|'.$suffix][] = $rows[$num];
 endforeach;

 $rows = $prots;

 $i = 1;
 foreach ($rows as $prot_num => $prot):
 $num = count($prot);
 $j = 0;
 foreach ($prot as $row):
	?>
 <tr style='mso-yfti-irow:<?=$i++?>'>
  <td width=154 style='width:115.15pt;border:solid windowtext 1.0pt;border-top:
  none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><?=$row['factor_name']?><o:p></o:p></p>
  </td>
  <?php
  if(!$j):
  $j++;
  if(false !== $pos = strpos($prot_num, '|')) $prot_num = substr($prot_num, 0, $pos);
  ?>
  <td width=154 style='width:115.15pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt' rowspan=<?=$num?>>
  <p class=MsoNormal align=center style='text-align:center'><?=$prot_num?><o:p></o:p></p>
  </td>
  <?php endif; ?>
  <td width=154 style='width:115.15pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><?=$row['prot_norms']?><o:p></o:p></p>
  </td>
  <td width=154 style='width:115.15pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><?=$row['prot_data']?><o:p></o:p></p>
  </td>
 </tr>
<?php endforeach; ?>
<?php endforeach; ?>
</table>
<?php
}
$i = 1;
$row = $dbInst->getWPlaceFactorsInfo($f['firm_id'], $f['subdivision_id'], $f['wplace_id']);
if(isset($row['fact_dust']) && $row['fact_dust'] != '') {
	echo '<p class=MsoNormal>1.3.2.'.$i++.'. Прах – вид: '.HTMLFormat($row['fact_dust']).'</p>';
}
if(isset($row['fact_chemicals']) && $row['fact_chemicals'] != '') {
	echo '<p class=MsoNormal>1.3.2.'.$i++.'. Химични агенти – вид: '.HTMLFormat($row['fact_chemicals']).'</p>';
}
if(isset($row['fact_biological']) && $row['fact_biological'] != '') {
	echo '<p class=MsoNormal>1.3.2.'.$i++.'. Биологични агенти: '.HTMLFormat($row['fact_biological']).'</p>';
}
if(isset($row['fact_work_pose']) && $row['fact_work_pose'] != '') {
	echo '<p class=MsoNormal>1.3.2.'.$i++.'. Работна поза: '.HTMLFormat($row['fact_work_pose']).'</p>';
}
if(isset($row['fact_manual_weights']) && $row['fact_manual_weights'] != '') {
	echo '<p class=MsoNormal>1.3.2.'.$i++.'. Ръчна работа с тежести: '.HTMLFormat($row['fact_manual_weights']).'</p>';
}
if(isset($row['fact_monotony']) && $row['fact_monotony'] != '') {
	echo '<p class=MsoNormal>1.3.2.'.$i++.'. Двигателна монотонна работа: '.HTMLFormat($row['fact_monotony']).'</p>';
}
if(isset($row['fact_nervous']) && $row['fact_nervous'] != '') {
	echo '<p class=MsoNormal>1.3.2.'.$i++.'. Нервно-психично напрежение: '.HTMLFormat($row['fact_nervous']).'</p>';
}
if(isset($row['fact_nervous']) && ($row['fact_work_regime'] != '' || $row['fact_work_hours'] != '' || $row['fact_work_and_break'] != '')) {
	echo '<p class=MsoNormal>1.3.2.'.$i.'. Организация на труда:</p>';
	$j = 1;
	if($row['fact_work_regime'] != '') {
		echo '<p class=MsoNormal>1.3.2.'.$i.'.'.$j++.'. режим на работа: '.HTMLFormat($row['fact_work_regime']).'</p>';
	}
	if($row['fact_work_hours'] != '') {
		echo '<p class=MsoNormal>1.3.2.'.$i.'.'.$j++.'. продължителност на работното време: '.HTMLFormat($row['fact_work_hours']).'</p>';
	}
	if($row['fact_work_and_break'] != '') {
		echo '<p class=MsoNormal>1.3.2.'.$i.'.'.$j++.'. физиологични режими на труд и почивка: '.HTMLFormat($row['fact_work_and_break']).'</p>';
	}
	$i++;
}
if(isset($row['fact_other']) && $row['fact_other'] != '') {
	echo '<p class=MsoNormal>1.3.2.'.$i++.'. Други: '.HTMLFormat($row['fact_other']).'</p>';
}
?>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>

<p class=MsoNormal>2. Данни от предварителен медицински преглед: </p>

<?php if($f['prchk_date2'] != '') { ?>
<p class=MsoNormal>2.1. <img width=12 height=12 id="_x0000_i1025"
src="<?=$imgpath.$checked?>" alt="*">&nbsp;Има налични данни за
проведен предварителен преглед.</p>

<p class=MsoNormal>2.1.1. <span class=SpellE>Kарта</span> за предварителен
медицински преглед издадена от <?=HTMLFormat($f['prchk_author'])?></p>

<?php
$rows = $dbInst->getPrchkDocDiagnosis($worker_id);
if($rows) {
?>
<p class=MsoNormal>- Заключение на лекаря/лекарите, провели прегледите:</p>
<?php foreach ($rows as $row) { ?>
<p class=MsoNormal><b><?=HTMLFormat($row['doctor_pos_name'])?><?=((''!=$row['doc_name'])?' ('.HTMLFormat($row['doc_name']).')':'')?></b>: <?=HTMLFormat($row['doc_conclusion'])?></p>
<?php
}
}
?>
<?php
// Get the last preliminary checkup ID
$precheckup_id = $dbInst->GiveValue('precheckup_id', 'medical_precheckups', "WHERE worker_id = $worker_id LIMIT 1", 0);
$rows = $dbInst->getPrchkDiagnosis($precheckup_id);
if($rows) {
?>
<p class=MsoNormal>- Заболявания (диагнози)</p>

<div align=center>

<table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 width="100%"
 style='width:100.0%;border-collapse:collapse;mso-padding-alt:0cm 0cm 0cm 0cm'>
 <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes'>
  <td width=166 valign=top style='width:124.85pt;border:solid windowtext 1.0pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'>МКБ<o:p></o:p></p>
  </td>
  <td width=314 valign=top style='width:235.45pt;border:solid windowtext 1.0pt;
  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;padding:0cm 0cm 0cm 0cm'>
  <p class=MsoNormal align=center style='text-align:center'>Диагноза<o:p></o:p></p>
  </td>
  <td width=132 valign=top style='width:98.95pt;border:solid windowtext 1.0pt;
  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;padding:0cm 0cm 0cm 0cm'>
  <p class=MsoNormal align=center style='text-align:center'>Издадена от<o:p></o:p></p>
  </td>
 </tr>
 <?php foreach ($rows as $row) { ?>
 <tr style='mso-yfti-irow:1;mso-yfti-lastrow:yes'>
  <td width=166 valign=top style='width:124.85pt;border:solid windowtext 1.0pt;
  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><?=HTMLFormat($row['mkb_id'])?><o:p></o:p></p>
  </td>
  <td width=314 valign=top style='width:235.45pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 0cm 0cm 0cm'>
  <?='<p class=MsoNormal>'.HTMLFormat($row['mkb_desc']).'</p>'.(($row['diagnosis']!='')?'<p class=MsoNormal><o:p>'.HTMLFormat($row['diagnosis']).'</o:p></p>':'')?>
  </td>
  <td width=132 valign=top style='width:98.95pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 0cm 0cm 0cm'>
  <p class=MsoNormal align=center style='text-align:center'><?=HTMLFormat($row['doctor_pos_name'])?><o:p></o:p></p>
  </td>
 </tr>
 <?php } ?>
</table>

</div>
<?php } ?>

<!--<p class=MsoNormal><o:p>&nbsp;</o:p></p>-->

<p class=MsoNormal>2.1.2. Заключение на СТМ за пригодността на работещия да изпълнява
даден вид дейност въз основа на карта от задължителен предварителен медицински
преглед, издадена от <?=$dbInst->shortStmName(HTMLFormat($stm_name))?> <?=((isset($f['prchk_stm_date2'])&&$f['prchk_stm_date2']!='')?' на '.$f['prchk_stm_date2'].' г.':'')?></p>

<table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 width="100%"
 style='width:100.0%;border-collapse:collapse;mso-padding-alt:0cm 5.4pt 0cm 5.4pt'>
 <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes'>
  <td width=307 style='width:230.3pt;border:solid windowtext 1.0pt;padding:
  0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'>Наименование и
  адрес на СТМ, изготвила заключението, и дата на изготвянето му</p>
  </td>
  <td width=307 style='width:230.3pt;border:solid windowtext 1.0pt;border-left:
  none;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'>Заключение</p>
  </td>
 </tr>
 <tr style='mso-yfti-irow:1;mso-yfti-lastrow:yes'>
  <td width=307 valign=top style='width:230.3pt;border:solid windowtext 1.0pt;
  border-top:none;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><?=$dbInst->shortStmName(HTMLFormat($stm_name))?></p>
  <p class=MsoNormal align=center style='text-align:center'><?=HTMLFormat($s['address'])?></p>
  </td>
  <td width=307 valign=top style='width:230.3pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <?php
  if($f['prchk_conclusion'] == '1') {
  	echo '<p class=MsoNormal><b style=\'mso-bidi-font-weight:normal\'>Може</b> да изпълнява посочената длъжност/професия</p>';
  }
  elseif ($f['prchk_conclusion'] == '2') {
  	echo '<p class=MsoNormal><b style=\'mso-bidi-font-weight:normal\'>Може</b> да изпълнява посочената длъжност/професия при следните условия:</p>';
  	echo '<p class=MsoNormal>'.HTMLFormat($f['prchk_conditions']).'</p>';
  }
  elseif ($f['prchk_conclusion'] == '0') {
  	echo '<p class=MsoNormal><b style=\'mso-bidi-font-weight:normal\'>Не може</b> да изпълнява посочената длъжност/професия</p>';
  }
  ?>
  </td>
 </tr>
</table>

<?php } else { ?>
<p class=MsoNormal>2.2. <img width=13 height=13 src="<?=$imgpath.$checked?>" alt="*">&nbsp;Няма
налични данни за проведен предварителен медицински преглед.</p>
<?php } ?>

<p class=MsoNormal>&nbsp;</p>

<p class=MsoNormal>3. Данни от извършените периодични медицински прегледи и
изследвания:</p>

<p class=MsoNormal>3.1. Работещият се е явил на периодичен медицински преглед и
са проведени определените изследвания.</p>
<!--<p class=MsoNormal>&nbsp;</p>-->

<?php
$rows = $dbInst->getMedicalCheckupList($worker_id);
$k = 1;
foreach ($rows as $row) {
	$checkup_id = $row['checkup_id'];
	$line = $dbInst->getMedicalCheckupInfo($checkup_id);
	?>
<p class=MsoNormal>3.<?=$k?>.1. Дата на провеждане на прегледа: <b style='mso-bidi-font-weight:normal'><?=((empty($line['checkup_date_h'])) ? '--' : $line['checkup_date_h'].' г.')?><o:p></o:p></b></p>

<!--<p class=MsoNormal>3.1.2. Наименование на лечебното заведение, провело прегледа – <?=HTMLFormat($line['hospital'])?>.</p>-->
<p class=MsoNormal>3.<?=$k?>.2. Наименование на лечебното заведение, провело прегледа – <?php
$_arr = array();
$hospitals = '';
if($_data = @unserialize($line['hospital'])) {
	for ($j = 0; $j < count($_data); $j++) {
		if(mb_strlen($_data[$j], 'utf-8') == 0) continue;
		else $_arr[] = stripslashes($_data[$j]);
	}
	//$hospitals = (mb_strlen($hospitals, 'utf-8') > 2) ? mb_substr($hospitals, 0, (mb_strlen($hospitals) - 2), 'utf-8') : '';
	$hospitals = (count($_arr)) ? implode(', ', $_arr) : '';
}
if($hospitals == '') {
	echo 'Не са предоставени данни';
} else {
	echo $hospitals;
}
?>.</p>

<p class=MsoNormal>3.<?=$k?>.3. Вид на медицинските специалисти, извършили прегледите
<?php
$rows = $dbInst->getDoctorsDesc($checkup_id);
if($rows) {
	$_arr = array();
	foreach ($rows as $row) {
		if($row['conclusion'] == '') continue;
		$_arr[] = $dbInst->my_mb_ucfirst(HTMLFormat($row['SpecialistName']));
	}
	if(count($_arr)) {
		echo ' – '.implode(', ', $_arr);
	}
}
?>.</p>

<p class=MsoNormal>3.<?=$k?>.4. Вид на извършените функционални и лабораторни
изследвания.</p>

<?php if(isset($line['EKG']) && $line['EKG'] != '') { ?>
<p class=MsoNormal>ЕКГ: <?=HTMLFormat($line['EKG'])?></p>
<?php } if(isset($line['x_ray']) && $line['x_ray'] != '') { ?>
<p class=MsoNormal>Рентгенография: <?=HTMLFormat($line['x_ray'])?></p>
<?php } if(isset($line['echo_ray']) && $line['echo_ray'] != '') { ?>
<p class=MsoNormal>Ехография: <?=HTMLFormat($line['echo_ray'])?></p>
<?php } ?>

<?php if(isset($line) && $line['left_eye'] != '') { ?>
<p class=MsoNormal>Зрителна острота</p>

<div align=center>

<table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 width="100%"
 style='width:100.0%;border-collapse:collapse;mso-padding-alt:0cm 5.4pt 0cm 5.4pt'>
 <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes;mso-yfti-lastrow:yes'>
  <td width=84 valign=top style='width:63.1pt;border:none;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal>Ляво око:<o:p></o:p></p>
  </td>
  <td width=41 valign=top style='width:31.0pt;border:solid windowtext 1.0pt;
  border-left:none;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><o:p><?=HTMLFormat($line['left_eye'])?></o:p></p>
  </td>
  <td width=18 valign=top style='width:13.8pt;border:none;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal>&nbsp;<o:p></o:p></p>
  </td>
  <td width=50 valign=top style='width:37.2pt;border:solid windowtext 1.0pt;
  border-left:none;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><?=HTMLFormat($line['left_eye2'])?><o:p></o:p></p>
  </td>
  <td width=60 valign=top style='width:45.2pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><span class=SpellE>dp</span> <o:p></o:p></p>
  </td>
  <td width=92 valign=top style='width:69.15pt;border:none;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal>Дясно око:<o:p></o:p></p>
  </td>
  <td width=42 valign=top style='width:31.45pt;border:solid windowtext 1.0pt;
  border-left:none;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><o:p><?=HTMLFormat($line['right_eye'])?></o:p></p>
  </td>
  <td width=31 valign=top style='width:23.4pt;border:none;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal>&nbsp;<o:p></o:p></p>
  </td>
  <td width=40 valign=top style='width:30.15pt;border:solid windowtext 1.0pt;
  border-left:none;padding:0cm 0cm 0cm 0cm'>
  <p class=MsoNormal><?=HTMLFormat($line['right_eye2'])?><o:p></o:p></p>
  </td>
  <td width=153 valign=top style='width:114.55pt;padding:0cm 0cm 0cm 0cm'>
  <p class=MsoNormal><span class=SpellE>dp</span><o:p></o:p></p>
  </td>
 </tr>
<!-- Функционално изследване на дишането -->
</table>

</div>
<?php } ?>

<?php if(isset($line) && ($line['VK'] != '' || $line['FEO1'] != '')) { ?>
<p class=MsoNormal>Функционално изследване на дишането</p>

<div align=center>

<table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 width="100%"
 style='width:100.0%;border-collapse:collapse;mso-padding-alt:0cm 0cm 0cm 0cm'>
 <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes;mso-yfti-lastrow:yes'>
  <td width=88 valign=top style='width:66.15pt;border:none;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal>ВК:<o:p></o:p></p>
  </td>
  <td width=47 valign=top style='width:35.0pt;border:solid windowtext 1.0pt;
  border-left:none;padding:0cm 0cm 0cm 0cm'>
  <p class=MsoNormal><?=HTMLFormat($line['VK'])?><o:p></o:p></p>
  </td>
  <td width=112 valign=top style='width:84.15pt;padding:0cm 0cm 0cm 0cm'>
  <p class=MsoNormal>&nbsp;ml<o:p></o:p></p>
  </td>
  <td width=91 valign=top style='width:68.1pt;border:none;border-right:solid windowtext 1.0pt;
  mso-border-right-alt:solid windowtext .5pt;padding:0cm 0cm 0cm 0cm'>
  <p class=MsoNormal>ФЕО 1:<o:p></o:p></p>
  </td>
  <td width=47 valign=top style='width:35.55pt;border:solid windowtext 1.0pt;
  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;padding:0cm 0cm 0cm 0cm'>
  <p class=MsoNormal><?=HTMLFormat($line['FEO1'])?><o:p></o:p></p>
  </td>
  <td width=234 valign=top style='width:175.45pt;border:none;mso-border-left-alt:
  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal>ml<o:p></o:p></p>
  </td>
 </tr>
</table>

</div>
<?php } ?>

<?php if(isset($line['tifno']) && $line['tifno'] != '') { ?>
<p class=MsoNormal>Показател на <span class=SpellE>Тифно</span>: <?=HTMLFormat($line['tifno'])?></p>
<?php } ?>

<?php if(isset($line['hearing_loss']) && $line['hearing_loss'] != '') { ?>
<p class=MsoNormal>Тонална аудиометрия</p>

<p class=MsoNormal>Загуба на слуха: <?=HTMLFormat($line['hearing_loss'])?></p>

<div align=center>

<table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 width="100%"
 style='width:100.0%;border-collapse:collapse;mso-padding-alt:0cm 0cm 0cm 0cm'>
 <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes;mso-yfti-lastrow:yes'>
  <td width=88 valign=top style='width:66.15pt;border:none;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal>Ляво ухо:<o:p></o:p></p>
  </td>
  <td width=47 valign=top style='width:35.0pt;border:solid windowtext 1.0pt;
  border-left:none;padding:0cm 0cm 0cm 0cm'>
  <p class=MsoNormal><?=HTMLFormat($line['left_ear'])?><o:p></o:p></p>
  </td>
  <td width=112 valign=top style='width:84.15pt;padding:0cm 0cm 0cm 0cm'>
  <p class=MsoNormal>&nbsp;<o:p></o:p></p>
  </td>
  <td width=91 valign=top style='width:68.1pt;border:none;border-right:solid windowtext 1.0pt;
  mso-border-right-alt:solid windowtext .5pt;padding:0cm 0cm 0cm 0cm'>
  <p class=MsoNormal>Дясно ухо:<o:p></o:p></p>
  </td>
  <td width=47 valign=top style='width:35.55pt;border:solid windowtext 1.0pt;
  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;padding:0cm 0cm 0cm 0cm'>
  <p class=MsoNormal><?=HTMLFormat($line['right_ear'])?><o:p></o:p></p>
  </td>
  <td width=234 valign=top style='width:175.45pt;border:none;mso-border-left-alt:
  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><o:p>&nbsp;</o:p></p>
  </td>
 </tr>
</table>

</div>

<?php if(isset($line['hearing_diagnose']) && $line['hearing_diagnose'] != '') { ?>
<p class=MsoNormal>Диагноза: <?=HTMLFormat($line['hearing_diagnose'])?></p>
<?php
}
}
?>

<!-- Фамилна обремененост -->
<p class=MsoNormal>Фамилна обремененост: <?=((!empty($line['fweights_descr'])) ? HTMLFormat($line['fweights_descr']) : '--')?></p>
<?php
$rows = $dbInst->getFamilyWeights($checkup_id);
if($rows) {
	$i = 1;
	?>
<div align=center>

<table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 width="100%"
 style='width:100.0%;border-collapse:collapse;mso-padding-alt:0cm 0cm 0cm 0cm'>
 <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes'>
  <td width=169 valign=top style='width:126.75pt;border:solid windowtext 1.0pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'>МКБ<o:p></o:p></p>
  </td>
  <td width=450 valign=top style='width:337.65pt;border:solid windowtext 1.0pt;
  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'>Диагноза<o:p></o:p></p>
  </td>
 </tr>
 <?php foreach ($rows as $row) { ?>
 <tr style='mso-yfti-irow:<?=$i++?>'>
  <td width=169 valign=top style='width:126.75pt;border:solid windowtext 1.0pt;
  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><?=HTMLFormat($row['mkb_id'])?><o:p></o:p></p>
  </td>
  <td width=450 valign=top style='width:337.65pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <?='<p class=MsoNormal>'.HTMLFormat($row['mkb_desc']).'</p>'.(($row['diagnosis']!='')?'<p class=MsoNormal>'.HTMLFormat($row['diagnosis']).'<o:p></o:p></p>':'')?></td>
 </tr>
 <?php } ?>
</table>

</div>
<?php } ?>

<?php
$checkups = $dbInst->getLabCheckups($checkup_id);
$labs = $dbInst->getLabs();
if($checkups) {
	$i = 1;
?>
<p class=MsoNormal>Лабораторни изследвания</p>

<div align=center>

<table class=MsoNormalTable border=1 cellspacing=0 cellpadding=0 width="100%"
 style='width:100.0%;border-collapse:collapse;border:none;mso-border-alt:solid windowtext .5pt;
 mso-padding-alt:0cm 0cm 0cm 0cm;mso-border-insideh:.5pt solid windowtext;
 mso-border-insidev:.5pt solid windowtext'>
 <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes'>
  <td width=167 valign=top style='width:125.25pt;border:solid windowtext 1.0pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'>Показател<o:p></o:p></p>
  </td>
  <td width=223 colspan=2 valign=top style='width:166.95pt;border:solid windowtext 1.0pt;
  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;padding:0cm 0cm 0cm 0cm'>
  <p class=MsoNormal align=center style='text-align:center'>Min &lt; Норма &gt;
  Max<o:p></o:p></p>
  </td>
  <td width=223 colspan=2 valign=top style='width:167.05pt;border:solid windowtext 1.0pt;
  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;padding:0cm 0cm 0cm 0cm'>
  <p class=MsoNormal align=center style='text-align:center'>Ниво<span
  style='font-size:10.0pt'><o:p></o:p></span></p>
  </td>
 </tr>
 <?php foreach ($checkups as $row) { ?>
 <tr style='mso-yfti-irow:<?=$i++?>'>
  <td width=167 valign=top style='width:125.25pt;border:solid windowtext 1.0pt;
  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><?=((isset($row))?HTMLFormat($row['indicator_name']):'')?><o:p></o:p></p>
  </td>
  <td width=111 valign=top style='width:83.45pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 0cm 0cm 0cm'>
  <p class=MsoNormal align=center style='text-align:center'><?=((isset($row))?HTMLFormat($row['pdk_min']):'')?><o:p></o:p></p>
  </td>
  <td width=111 valign=top style='width:83.5pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 0cm 0cm 0cm'>
  <p class=MsoNormal align=center style='text-align:center'><?=((isset($row))?HTMLFormat($row['pdk_max']):'')?><o:p></o:p></p>
  </td>
  <td width=112 valign=top style='width:83.65pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 0cm 0cm 0cm'>
  <p class=MsoNormal align=center style='text-align:center'><?=((isset($row))?HTMLFormat($row['checkup_level']):'')?> <?php /*calcDeviation($row['pdk_min'], $row['pdk_max'], $row['checkup_level'], $imgpath);*/ ?><o:p></o:p></p>
  </td>
  <td width=111 valign=top style='width:83.4pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 0cm 0cm 0cm'>
  <p class=MsoNormal align=center style='text-align:center'><?php echo ((isset($row))?HTMLFormat($row['indicator_dimension']):''); ?><span
  style='font-size:10.0pt'><o:p></o:p></span></p>
  </td>
 </tr>
 <?php } ?>
</table>

</div>
<?php } ?>

<p class=MsoNormal>Анамнеза: <?=((!empty($line['anamnesis_descr'])) ? HTMLFormat($line['anamnesis_descr']) : '--')?></p>
<?php
$rows = $dbInst->getAnamnesis($checkup_id);
if($rows) {
	$i = 1;
	?>
<div align=center>

<table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 width="100%"
 style='width:100.0%;border-collapse:collapse;mso-padding-alt:0cm 0cm 0cm 0cm'>
 <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes'>
  <td width=169 valign=top style='width:126.75pt;border:solid windowtext 1.0pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'>МКБ<o:p></o:p></p>
  </td>
  <td width=450 valign=top style='width:337.65pt;border:solid windowtext 1.0pt;
  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'>Диагноза<o:p></o:p></p>
  </td>
 </tr>
 <?php foreach ($rows as $row) { ?>
 <tr style='mso-yfti-irow:<?=$i++?>'>
  <td width=169 valign=top style='width:126.75pt;border:solid windowtext 1.0pt;
  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><?=HTMLFormat($row['mkb_id'])?><o:p></o:p></p>
  </td>
  <td width=450 valign=top style='width:337.65pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <?='<p class=MsoNormal>'.HTMLFormat($row['mkb_desc']).'</p>'.(($row['diagnosis']!='')?'<p class=MsoNormal>'.HTMLFormat($row['diagnosis']).'<o:p></o:p></p>':'')?></td>
 </tr>
 <?php } ?>
</table>

</div>
<?php } ?>

<?php
$rows2 = $dbInst->getDiseases($checkup_id);
if($rows2) {
?>
<p class=MsoNormal>Заболявания (диагнози)</p>

<div align=center>

<table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 width="100%"
 style='width:100.0%;border-collapse:collapse;mso-padding-alt:0cm 0cm 0cm 0cm'>
 <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes'>
  <td width=166 valign=top style='width:124.85pt;border:solid windowtext 1.0pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'>МКБ<o:p></o:p></p>
  </td>
  <td width=314 valign=top style='width:235.45pt;border:solid windowtext 1.0pt;
  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;padding:0cm 0cm 0cm 0cm'>
  <p class=MsoNormal align=center style='text-align:center'>Диагноза<o:p></o:p></p>
  </td>
  <td width=132 valign=top style='width:98.95pt;border:solid windowtext 1.0pt;
  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;padding:0cm 0cm 0cm 0cm'>
  <p class=MsoNormal align=center style='text-align:center'>Новооткрито<o:p></o:p></p>
  </td>
 </tr>
 <?php foreach ($rows2 as $row) { ?>
 <tr style='mso-yfti-irow:1;mso-yfti-lastrow:yes'>
  <td width=166 valign=top style='width:124.85pt;border:solid windowtext 1.0pt;
  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><?=HTMLFormat($row['mkb_id'])?><o:p></o:p></p>
  </td>
  <td width=314 valign=top style='width:235.45pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 0cm 0cm 0cm'>
  <?='<p class=MsoNormal>'.HTMLFormat($row['mkb_desc']).'</p>'.(($row['diagnosis']!='')?'<p class=MsoNormal><o:p>'.HTMLFormat($row['diagnosis']).'</o:p></p>':'')?>
  </td>
  <td width=132 valign=top style='width:98.95pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 0cm 0cm 0cm'>
  <p class=MsoNormal align=center style='text-align:center'><img width=13 height=13 src="<?=$imgpath.(($row['is_new']=='1')?$checked:$unchecked)?>"><o:p></o:p></p>
  </td>
 </tr>
 <?php } ?>
</table>

</div>
<?php } ?>

<?php
/*
$rows3 = $dbInst->getDiseases($checkup_id);
if($rows3) {
?>
<p class=MsoNormal>Диагнози:</p>

<div align=center>

<table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 width="100%"
style='width:100.0%;border-collapse:collapse;mso-padding-alt:0cm 0cm 0cm 0cm'>
<?php foreach ($rows3 as $row) { ?>
<tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes;mso-yfti-lastrow:yes'>
<td width=169 valign=top style='width:126.75pt;padding:0cm 5.4pt 0cm 5.4pt'>
<p class=MsoNormal><?=HTMLFormat($row['mkb_id'])?><o:p></o:p></p>
</td>
<td width=450 valign=top style='width:337.65pt;padding:0cm 5.4pt 0cm 5.4pt'>
<p class=MsoNormal><?=HTMLFormat($row['mkb_desc'])?><span style='font-size:10.0pt'><o:p></o:p></span></p>
</td>
</tr>
<?php } ?>
</table>

</div>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>
<?php } */ ?>

<p class=MsoNormal>3.<?=$k?>.5. Заключение на лекаря/лекарите, провели прегледите:</p>

<?php
$rows = $dbInst->getDoctorsDesc($checkup_id);
if($rows) {
	foreach ($rows as $row) {
		if($row['conclusion'] == '') continue;
?>
<p class=MsoNormal><b><?=$dbInst->my_mb_ucfirst(HTMLFormat($row['SpecialistName']))?></b>: <?=HTMLFormat($row['conclusion'])?></p>
<?php
	}
}
?>

<?php /*if(isset($line['conclusion']) && $line['conclusion'] != '') { ?>
<p class=MsoNormal style='margin-left:36.0pt;text-indent:-18.0pt;mso-list:l3 level1 lfo2;
tab-stops:list 36.0pt'><![if !supportLists]><span style='font-family:Symbol;
mso-fareast-font-family:Symbol;mso-bidi-font-family:Symbol'><span
style='mso-list:Ignore'><img width=13 height=13 src="<?=$imgpath.$checked?>" alt="*"><span style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
</span></span></span><![endif]><?=HTMLFormat($line['conclusion']).(($line['notes']!='')?' - '.HTMLFormat($line['notes']):'')?></p>

<p class=MsoNormal>&nbsp;</p>
<?php }*/ ?>

<p class=MsoNormal>3.<?=$k?>.6. Заключение на службата по трудова медицина за
пригодността на работещия да изпълнява даден вид дейност въз основа на
задължителния периодичен медицински преглед, проведен<?=(($hospitals!='')?' от/в '.$hospitals:'')?> на <?=((empty($line['checkup_date_h'])) ? '--' : $line['checkup_date_h'].' г.')?></p>

<table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 width="100%"
 style='width:100.0%;border-collapse:collapse;mso-padding-alt:0cm 5.4pt 0cm 5.4pt'>
 <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes'>
  <td width=307 style='width:230.3pt;border:solid windowtext 1.0pt;padding:
  0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'>Наименование и
  адрес на СТМ, изготвила заключението, и дата на изготвянето му</p>
  </td>
  <td width=307 style='width:230.3pt;border:solid windowtext 1.0pt;border-left:
  none;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'>Заключение</p>
  </td>
 </tr>
 <tr style='mso-yfti-irow:1;mso-yfti-lastrow:yes'>
  <td width=307 valign=top style='width:230.3pt;border:solid windowtext 1.0pt;
  border-top:none;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><?=$dbInst->shortStmName(HTMLFormat($stm_name))?></p>
  <p class=MsoNormal align=center style='text-align:center'><?=HTMLFormat($s['address'])?></p>
  </td>
  <td width=307 valign=top style='width:230.3pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <?php
  switch ($line['stm_conclusion']) {
  	case '1':
  		echo '<p class=MsoNormal>Може да изпълнява посочената длъжност/професия '.HTMLFormat($f['position_name']).' в '.HTMLFormat($firm['name']).'</p>';
  		break;
  	case '2':
  		echo '<p class=MsoNormal>Може да изпълнява посочената длъжност/професия '.HTMLFormat($f['position_name']).' в '.HTMLFormat($firm['name']).' при следните условия:</p>';
  		echo '<p class=MsoNormal>'.HTMLFormat($line['stm_conditions']).'</p>';
  		break;
  	case '0':
  		echo '<p class=MsoNormal>Не може да изпълнява посочената длъжност/професия '.HTMLFormat($f['position_name']).' в '.HTMLFormat($firm['name']).'</p>';
  		break;
  	case '3':
  		echo '<p class=MsoNormal>Не може да се прецени пригодността на работещия да изпълнява посочената длъжност/професия '.HTMLFormat($f['position_name']).' в '.HTMLFormat($firm['name']).'</p>';
  		break;

  	default:
  		break;
  }
  ?>
  </td>
 </tr>
</table>
<p class=MsoNormal>&nbsp;</p>
	<?php
	$k++;
}
?>

<p class=MsoNormal><b>V. Данни за
посещенията на работещия в службата по трудова медицина по негова инициатива</b></p>

<p class=MsoNormal>&nbsp;</p>

<p class=MsoNormal>1. Извършено посещение на работещия в <?=$dbInst->shortStmName(HTMLFormat($stm_name))?></p>

<p class=MsoNormal>2. Дата на извършеното посещение: &nbsp;</p>

<p class=MsoNormal>3. Кратко описание на целта на посещението.</p>

<p class=MsoNormal>4. Описание на предприетите мерки от службата по трудова
медицина във връзка с поставените въпроси, проблеми и други, когато е
необходимо.</p>

<p class=MsoNormal>5. Други.</p>

<?php w_footer($s); ?>

</div>

</body>

</html>
