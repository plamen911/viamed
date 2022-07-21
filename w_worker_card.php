<?php
// Test: http://localhost/stm2008/hipokrat/w_worker_card.php?checkup_id=502&offline=1

require('includes.php');

$offline = (isset($_GET['offline']) && $_GET['offline'] == '1') ? 1 : 0;

$checkup_id = (isset($_GET['checkup_id']) && is_numeric($_GET['checkup_id'])) ? intval($_GET['checkup_id']) : 0;
$f = $dbInst->getMedicalCheckupInfo($checkup_id);
if(!$f) {
	die('Липсва индентификатор на картата за профилактичен медицински преглед!');
}
$s = $dbInst->getStmInfo();

$stm_name = preg_replace('/\<br\s*\/?\>/', '', $s['stm_name']);

//$unchecked = 'image001.jpg';
//$checked = 'image002.jpg';
$unchecked = 'unchecked.gif';
$checked = 'checked.gif';
//$imgpath = 'img/';
$imgpath = "http://" . ((isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:$_SERVER['SERVER_ADDR'].':'.$_SERVER['SERVER_PORT'])) . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/img/";

if(!$offline) {
	$firm_name = str_replace(' ', '_', $f['firm_name']);
	$firm_name = str_replace('"', '', $firm_name);
	$firm_name = str_replace('\'', '', $firm_name);
	$firm_name = str_replace('”', '', $firm_name);
	$firm_name = str_replace('„', '', $firm_name);
	$firm_name = str_replace('_-_', '_', $firm_name);
	
	$worker_name = str_replace(' ', '_', (mb_substr($f['fname'], 0, 1, 'utf-8').' '.$f['lname']));

	require_once("cyrlat.class.php");
	$cyrlat = new CyrLat;
	$filename = 'Karta_ot_prof_pregled_'.$cyrlat->cyr2lat($worker_name.'_'.$firm_name).'-'.$f['worker_id'].'.doc';	

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
<!--<link rel=File-List href="worker_card3_files/filelist.xml">
<link rel=Edit-Time-Data href="worker_card3_files/editdata.mso">-->
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
  <o:Revision>9</o:Revision>
  <o:TotalTime>282</o:TotalTime>
  <o:LastPrinted>2008-04-18T09:44:00Z</o:LastPrinted>
  <o:Created>2008-07-27T12:00:00Z</o:Created>
  <o:LastSaved>2008-07-27T12:09:00Z</o:LastSaved>
  <o:Pages>1</o:Pages>
  <o:Words>355</o:Words>
  <o:Characters>2028</o:Characters>
  <o:Company>СТМ</o:Company>
  <o:Lines>16</o:Lines>
  <o:Paragraphs>4</o:Paragraphs>
  <o:CharactersWithSpaces>2379</o:CharactersWithSpaces>
  <o:Version>11.5606</o:Version>
 </o:DocumentProperties>
</xml><![endif]--><!--[if gte mso 9]><xml>
 <w:WordDocument>
  <w:View>Print</w:View>
  <w:Zoom>105</w:Zoom>
  <w:DontDisplayPageBoundaries/>
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
 <o:shapedefaults v:ext="edit" spidmax="9218"/>
</xml><![endif]--><!--[if gte mso 9]><xml>
 <o:shapelayout v:ext="edit">
  <o:idmap v:ext="edit" data="1"/>
 </o:shapelayout></xml><![endif]-->
</head>

<body lang=BG style='tab-interval:35.4pt'>

<div class=Section1>

<?php w_heading($s); ?>

</div>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>

<p class=MsoNormal align=center style='text-align:center'><b><span
style='font-size:16.0pt'>КАРТА ОТ ПРОФИЛАКТИЧЕН ПРЕГЛЕД<o:p></o:p></span></b></p>

<p class=MsoNormal align=center style='text-align:center'><b style='mso-bidi-font-weight:
normal'><span style='font-size:14.0pt'>на <o:p></o:p></span></b></p>

<p class=MsoNormal align=center style='text-align:center'><b style='mso-bidi-font-weight:
normal'><span style='font-size:14.0pt'><?=((isset($f))?HTMLFormat($f['fname'].' '.$f['sname'].' '.$f['lname']):'')?><?=((isset($f['checkup_date_h'])&&isset($f['birth_date2']))?', '.worker_age($f['birth_date2'], $f['checkup_date_h']).' г.':'')?>,<o:p></o:p></span></b></p>

<p class=MsoNormal align=center style='text-align:center'><b style='mso-bidi-font-weight:
normal'><span style='font-size:14.0pt'><?=((isset($f))?mb_strtoupper(HTMLFormat($f['firm_name']),'utf-8'):'')?><?=((isset($f))?' – '.mb_strtoupper(HTMLFormat($f['location_name']),'utf-8'):'')?></span><o:p></o:p></b></p>

<p class=MsoNormal align=center style='text-align:center'><o:p>&nbsp;</o:p></p>

<p class=MsoNormal><b style='mso-bidi-font-weight:normal'><span
style='font-size:14.0pt'><o:p>&nbsp;</o:p></span></b></p>

<div align=center>

<?php $i = 0; ?>
<table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 width="100%"
 style='width:100.0%;border-collapse:collapse;mso-padding-alt:0cm 0cm 0cm 0cm'>
 <tr style='mso-yfti-irow:<?=$i++?>;mso-yfti-firstrow:yes'>
  <td width=308 valign=top style='width:231.1pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal>ЕГН: <?=((isset($f))?HTMLFormat($f['egn']):'')?></p>
  </td>
  <td width=311 valign=top style='width:233.3pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=right style='text-align:right'>Дата на прегледа:
  <?=((isset($f))?HTMLFormat($f['checkup_date_h']).' г.':'')?></p>
  </td>
 </tr>
 <?php if(isset($f) && ($f['worker_location'] != '' || $f['address'] != '')) { ?>
 <tr style='mso-yfti-irow:<?=$i++?>'>
  <td width=619 colspan=2 valign=top style='width:464.4pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><?=((isset($f) && $f['worker_location'] != '')?'Гр./с. '.HTMLFormat($f['worker_location'].', '.$f['address']):'')?></p>
  </td>
 </tr>
 <?php } ?>
 <?php if(isset($f) && $f['subdivision_name'] != '') { ?>
 <tr style='mso-yfti-irow:<?=$i++?>'>
  <td width=619 colspan=2 valign=top style='width:464.4pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><?=((isset($f))?HTMLFormat($f['subdivision_name']):'')?></p>
  </td>
 </tr>
 <?php } ?>
 <?php if(isset($f) && $f['wplace_name'] != '') { ?>
 <tr style='mso-yfti-irow:<?=$i++?>;mso-yfti-lastrow:yes'>
  <td width=619 colspan=2 valign=top style='width:464.4pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal>Раб. място: <?=((isset($f))?HTMLFormat($f['wplace_name']):'')?></p>
  </td>
 </tr>
 <?php } ?>
</table>

</div>

<p class=MsoNormal>&nbsp;</p>

<div align=center>

<?php $i = 0; ?>
<table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 width="100%"
 style='width:100.0%;border-collapse:collapse;mso-padding-alt:0cm 0cm 0cm 0cm'>
 <tr style='mso-yfti-irow:<?=$i++?>;mso-yfti-firstrow:yes'>
  <td width=308 valign=top style='width:231.1pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal>Ръст:<o:p></o:p></p>
  </td>
  <td width=311 valign=top style='width:233.3pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><?=(($f['worker_height']!='')?HTMLFormat($f['worker_height']).' см':'')?><o:p></o:p></p>
  </td>
 </tr>
 <tr style='mso-yfti-irow:<?=$i++?>'>
  <td width=308 valign=top style='width:231.1pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal>Тегло:<o:p></o:p></p>
  </td>
  <td width=311 valign=top style='width:233.3pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><?=(($f['worker_weight']!='')?HTMLFormat($f['worker_weight']).' кг':'')?><o:p></o:p></p>
  </td>
 </tr>
 <tr style='mso-yfti-irow:<?=$i++?>'>
  <td width=308 valign=top style='width:231.1pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal>RR <span class=SpellE>сист</span>.<o:p></o:p></p>
  </td>
  <td width=311 valign=top style='width:233.3pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><?=((isset($f))?HTMLFormat($f['rr_syst']):'')?><o:p></o:p></p>
  </td>
 </tr>
 <tr style='mso-yfti-irow:<?=$i++?>'>
  <td width=308 valign=top style='width:231.1pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal>RR <span class=SpellE>диаст</span>.:<o:p></o:p></p>
  </td>
  <td width=311 valign=top style='width:233.3pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><?=((isset($f))?HTMLFormat($f['rr_diast']):'')?><o:p></o:p></p>
  </td>
 </tr>
 <?php if($f['smoking']) { ?>
 <tr style='mso-yfti-irow:<?=$i++?>'>
  <td width=308 valign=top style='width:231.1pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal>Тютюнопушене:<o:p></o:p></p>
  </td>
  <td width=311 valign=top style='width:233.3pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><img width=13 height=13 id="_x0000_i1026"
  src="<?=$imgpath.(($f['smoking'])?$checked:$unchecked)?>"><o:p></o:p></p>
  </td>
 </tr>
 <?php } ?>
 <?php if($f['drinking']) { ?>
 <tr style='mso-yfti-irow:<?=$i++?>'>
  <td width=308 valign=top style='width:231.1pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal>Алкохол:<o:p></o:p></p>
  </td>
  <td width=311 valign=top style='width:233.3pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><img width=13 height=13 id="_x0000_i1027"
  src="<?=$imgpath.(($f['drinking'])?$checked:$unchecked)?>"><o:p></o:p></p>
  </td>
 </tr>
 <?php } ?>
 <?php if($f['fats']) { ?>
 <tr style='mso-yfti-irow:<?=$i++?>'>
  <td width=308 valign=top style='width:231.1pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal>Нерационално хранене:<o:p></o:p></p>
  </td>
  <td width=311 valign=top style='width:233.3pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><img width=13 height=13 id="_x0000_i1028"
  src="<?=$imgpath.(($f['fats'])?$checked:$unchecked)?>"><o:p></o:p></p>
  </td>
 </tr>
 <?php } ?>
 <?php if($f['diet']) { ?>
 <tr style='mso-yfti-irow:<?=$i++?>'>
  <td width=308 valign=top style='width:231.1pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal>Диета:<o:p></o:p></p>
  </td>
  <td width=311 valign=top style='width:233.3pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><img width=13 height=13 id="_x0000_i1029"
  src="<?=$imgpath.(($f['diet'])?$checked:$unchecked)?>"><o:p></o:p></p>
  </td>
 </tr>
 <?php } ?>
 <?php if($f['home_stress']) { ?>
 <tr style='mso-yfti-irow:<?=$i++?>'>
  <td width=308 valign=top style='width:231.1pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal>Стрес в дома:<o:p></o:p></p>
  </td>
  <td width=311 valign=top style='width:233.3pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><img width=13 height=13 id="_x0000_i1030"
  src="<?=$imgpath.(($f['home_stress'])?$checked:$unchecked)?>"><o:p></o:p></p>
  </td>
 </tr>
 <?php } ?>
 <?php if($f['work_stress']) { ?>
 <tr style='mso-yfti-irow:<?=$i++?>'>
  <td width=308 valign=top style='width:231.1pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal>Стрес в работата:<o:p></o:p></p>
  </td>
  <td width=311 valign=top style='width:233.3pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><img width=13 height=13 id="_x0000_i1031"
  src="<?=$imgpath.(($f['work_stress'])?$checked:$unchecked)?>"><o:p></o:p></p>
  </td>
 </tr>
 <?php } ?>
 <?php if($f['social_stress']) { ?>
 <tr style='mso-yfti-irow:<?=$i++?>'>
  <td width=308 valign=top style='width:231.1pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal>Социален стрес:<o:p></o:p></p>
  </td>
  <td width=311 valign=top style='width:233.3pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><img width=13 height=13 id="_x0000_i1032"
  src="<?=$imgpath.(($f['social_stress'])?$checked:$unchecked)?>"><o:p></o:p></p>
  </td>
 </tr>
 <?php } ?>
 <?php if($f['video_display']) { ?>
 <tr style='mso-yfti-irow:<?=$i++?>'>
  <td width=308 valign=top style='width:231.1pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal>ВИДЕОДИСПЛЕЙ<span style='mso-ansi-language:EN-US'> </span>повече
  от 1/2 от<span style='mso-ansi-language:EN-US'> </span>раб.<span
  style='mso-ansi-language:EN-US'> </span><span
  style='mso-spacerun:yes'> </span>време:<o:p></o:p></p>
  </td>
  <td width=311 valign=top style='width:233.3pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><img width=13 height=13 id="_x0000_i1033"
  src="<?=$imgpath.(($f['video_display'])?$checked:$unchecked)?>"><o:p></o:p></p>
  </td>
 </tr>
 <?php } ?>
 <?php if($f['hours_activity']) { ?>
 <tr style='mso-yfti-irow:<?=$i++?>;mso-yfti-lastrow:yes'>
  <td width=308 valign=top style='width:231.1pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal>Физическа активност
  часа /<o:p></o:p></p>
  </td>
  <td width=311 valign=top style='width:233.3pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><?=((isset($f))?HTMLFormat($f['hours_activity']):'')?><o:p></o:p></p>
  </td>
 </tr>
 <?php } ?>
 <?php if($f['low_activity']) { ?>
 <tr style='mso-yfti-irow:<?=$i++?>'>
  <td width=308 valign=top style='width:231.1pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal>Намалена двигателна активност:<o:p></o:p></p>
  </td>
  <td width=311 valign=top style='width:233.3pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><img width=13 height=13 id="_x0000_i1034"
  src="<?=$imgpath.(($f['low_activity'])?$checked:$unchecked)?>"><o:p></o:p></p>
  </td>
 </tr>
 <?php } ?>
 
</table>

</div>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>

<?php if(isset($f['EKG']) && $f['EKG'] != '') { ?>
<p class=MsoNormal>ЕКГ: <?=HTMLFormat($f['EKG'])?></p>
<?php } if(isset($f['x_ray']) && $f['x_ray'] != '') { ?>
<p class=MsoNormal>Рентгенография: <?=HTMLFormat($f['x_ray'])?></p>
<?php } if(isset($f['echo_ray']) && $f['echo_ray'] != '') { ?>
<p class=MsoNormal>Ехография: <?=HTMLFormat($f['echo_ray'])?></p>
<?php } ?>

<?php if(isset($f) && ($f['left_eye'] != '' || $f['right_eye'] != '')) { ?>
<p class=MsoNormal>Зрителна острота</p>

<div align=center>

<table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 width="100%"
 style='width:100.0%;border-collapse:collapse;mso-padding-alt:0cm 5.4pt 0cm 5.4pt'>
 <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes;mso-yfti-lastrow:yes'>
  <td width=84 valign=top style='width:63.1pt;border:none;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal>Ляво око:</p>
  </td>
  <td width=41 valign=top style='width:31.0pt;border:solid windowtext 1.0pt;
  border-left:none;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><?=((isset($f))?HTMLFormat($f['left_eye']):'')?><o:p></o:p></p>
  </td>
  <td width=18 valign=top style='width:13.8pt;border:none;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal>&nbsp;</p>
  </td>
  <td width=50 valign=top style='width:37.2pt;border:solid windowtext 1.0pt;
  border-left:none;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><?=((isset($f))?HTMLFormat($f['left_eye2']):'')?></p>
  </td>
  <td width=60 valign=top style='width:45.2pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><span class=SpellE>dp</span> </p>
  </td>
  <td width=92 valign=top style='width:69.15pt;border:none;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal>Дясно око:</p>
  </td>
  <td width=42 valign=top style='width:31.45pt;border:solid windowtext 1.0pt;
  border-left:none;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><?=((isset($f))?HTMLFormat($f['right_eye']):'')?><o:p></o:p></p>
  </td>
  <td width=31 valign=top style='width:23.4pt;border:none;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal>&nbsp;</p>
  </td>
  <td width=40 valign=top style='width:30.15pt;border:solid windowtext 1.0pt;
  border-left:none;padding:0cm 0cm 0cm 0cm'>
  <p class=MsoNormal><?=((isset($f))?HTMLFormat($f['right_eye2']):'')?></p>
  </td>
  <td width=153 valign=top style='width:114.55pt;padding:0cm 0cm 0cm 0cm'>
  <p class=MsoNormal><span class=SpellE>dp</span></p>
  </td>
 </tr>
</table>

</div>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>
<?php } ?>

<?php if(isset($f) && ($f['VK'] != '' || $f['FEO1'] != '')) { ?>
<p class=MsoNormal>Функционално изследване на дишането</p>

<div align=center>

<table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 width="100%"
 style='width:100.0%;border-collapse:collapse;mso-padding-alt:0cm 0cm 0cm 0cm'>
 <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes;mso-yfti-lastrow:yes'>
  <td width=88 valign=top style='width:66.15pt;border:none;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal>ВК:</p>
  </td>
  <td width=47 valign=top style='width:35.0pt;border:solid windowtext 1.0pt;
  border-left:none;padding:0cm 0cm 0cm 0cm'>
  <p class=MsoNormal><?=((isset($f['VK']))?HTMLFormat($f['VK']):'')?></p>
  </td>
  <td width=112 valign=top style='width:84.15pt;padding:0cm 0cm 0cm 0cm'>
  <p class=MsoNormal>&nbsp;ml</p>
  </td>
  <td width=91 valign=top style='width:68.1pt;border:none;border-right:solid windowtext 1.0pt;
  mso-border-right-alt:solid windowtext .5pt;padding:0cm 0cm 0cm 0cm'>
  <p class=MsoNormal>ФЕО 1:</p>
  </td>
  <td width=47 valign=top style='width:35.55pt;border:solid windowtext 1.0pt;
  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;padding:0cm 0cm 0cm 0cm'>
  <p class=MsoNormal><?=((isset($f['FEO1']))?HTMLFormat($f['FEO1']):'')?></p>
  </td>
  <td width=234 valign=top style='width:175.45pt;border:none;mso-border-left-alt:
  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal>ml</p>
  </td>
 </tr>
</table>

</div>
<?php } ?>

<?php if(isset($f['tifno']) && $f['tifno'] != '') { ?>
<p class=MsoNormal>Показател на <span class=SpellE>Тифно</span>: <?=HTMLFormat($f['tifno'])?></p>
<?php } ?>

<?php if(isset($f['hearing_loss']) && $f['hearing_loss'] != '') { ?>
<p class=MsoNormal>Тонална аудиометрия</p>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>

<p class=MsoNormal>Загуба на слуха: <?=HTMLFormat($f['hearing_loss'])?></p>

<div align=center>

<table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 width="100%"
 style='width:100.0%;border-collapse:collapse;mso-padding-alt:0cm 0cm 0cm 0cm'>
 <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes;mso-yfti-lastrow:yes'>
  <td width=88 valign=top style='width:66.15pt;border:none;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal>Ляво ухо:</p>
  </td>
  <td width=47 valign=top style='width:35.0pt;border:solid windowtext 1.0pt;
  border-left:none;padding:0cm 0cm 0cm 0cm'>
  <p class=MsoNormal><?=HTMLFormat($f['left_ear'])?></p>
  </td>
  <td width=112 valign=top style='width:84.15pt;padding:0cm 0cm 0cm 0cm'>
  <p class=MsoNormal>&nbsp;</p>
  </td>
  <td width=91 valign=top style='width:68.1pt;border:none;border-right:solid windowtext 1.0pt;
  mso-border-right-alt:solid windowtext .5pt;padding:0cm 0cm 0cm 0cm'>
  <p class=MsoNormal>Дясно ухо:</p>
  </td>
  <td width=47 valign=top style='width:35.55pt;border:solid windowtext 1.0pt;
  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;padding:0cm 0cm 0cm 0cm'>
  <p class=MsoNormal><?=HTMLFormat($f['right_ear'])?></p>
  </td>
  <td width=234 valign=top style='width:175.45pt;border:none;mso-border-left-alt:
  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><o:p>&nbsp;</o:p></p>
  </td>
 </tr>
</table>

</div>
<?php
if(isset($f['hearing_diagnose']) && $f['hearing_diagnose'] != '') { ?>
<p class=MsoNormal>Диагноза: <?=HTMLFormat($f['hearing_diagnose'])?></p>
<?php } ?>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>
<?php } ?>

<p class=MsoNormal>Фамилна обремененост: <?=((!empty($f['fweights_descr'])) ? HTMLFormat($f['fweights_descr']) : '--')?></p>
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
  <p class=MsoNormal align=center style='text-align:center'>МКБ</p>
  </td>
  <td width=450 valign=top style='width:337.65pt;border:solid windowtext 1.0pt;
  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'>Диагноза</p>
  </td>
 </tr>
 <?php foreach ($rows as $row) { ?>
 <tr style='mso-yfti-irow:<?=$i++?>'>
  <td width=169 valign=top style='width:126.75pt;border:solid windowtext 1.0pt;
  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><?=HTMLFormat($row['mkb_id'])?></p>
  </td>
  <td width=450 valign=top style='width:337.65pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <?='<p class=MsoNormal>'.HTMLFormat($row['mkb_desc']).'</p>'.(($row['diagnosis']!='')?'<p class=MsoNormal>'.HTMLFormat($row['diagnosis']).'<o:p></o:p></p>':'')?>
  </td>
 </tr>
 <?php } ?>
</table>

</div>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>
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
  <p class=MsoNormal align=center style='text-align:center'>Показател</p>
  </td>
  <td width=223 colspan=2 valign=top style='width:166.95pt;border:solid windowtext 1.0pt;
  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;padding:0cm 0cm 0cm 0cm'>
  <p class=MsoNormal align=center style='text-align:center'>Min &lt; Норма &gt;
  Max</p>
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
  <p class=MsoNormal><?=((isset($row))?HTMLFormat($row['indicator_name']):'')?></p>
  </td>
  <td width=111 valign=top style='width:83.45pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 0cm 0cm 0cm'>
  <p class=MsoNormal align=center style='text-align:center'><?=((isset($row))?HTMLFormat($row['pdk_min']):'')?></p>
  </td>
  <td width=111 valign=top style='width:83.5pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 0cm 0cm 0cm'>
  <p class=MsoNormal align=center style='text-align:center'><?=((isset($row))?HTMLFormat($row['pdk_max']):'')?></p>
  </td>
  <td width=112 valign=top style='width:83.65pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 0cm 0cm 0cm'>
  <p class=MsoNormal align=center style='text-align:center'><?=((isset($row))?HTMLFormat($row['checkup_level']):'')?> <?php /*calcDeviation($row['pdk_min'], $row['pdk_max'], $row['checkup_level'], $imgpath);*/ ?></p>
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

<p class=MsoNormal><o:p>&nbsp;</o:p></p>
<?php } ?>

<p class=MsoNormal>Анамнеза: <?=((!empty($f['anamnesis_descr'])) ? HTMLFormat($f['anamnesis_descr']) : '--')?></p>
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
	$i = 1;
?>
<p class=MsoNormal>Заболявания (диагнози)</p>

<div align=center>

<table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 width="100%"
 style='width:100.0%;border-collapse:collapse;mso-padding-alt:0cm 0cm 0cm 0cm'>
 <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes'>
  <td width=166 valign=top style='width:124.85pt;border:solid windowtext 1.0pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'>МКБ</p>
  </td>
  <td width=314 valign=top style='width:235.45pt;border:solid windowtext 1.0pt;
  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;padding:0cm 0cm 0cm 0cm'>
  <p class=MsoNormal align=center style='text-align:center'>Диагноза</p>
  </td>
  <td width=132 valign=top style='width:98.95pt;border:solid windowtext 1.0pt;
  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;padding:0cm 0cm 0cm 0cm'>
  <p class=MsoNormal align=center style='text-align:center'>Новооткрито</p>
  </td>
 </tr>
 <?php foreach ($rows2 as $row) { ?>
 <tr style='mso-yfti-irow:<?=$i++?>;mso-yfti-lastrow:yes'>
  <td width=166 valign=top style='width:124.85pt;border:solid windowtext 1.0pt;
  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><?=HTMLFormat($row['mkb_id'])?></p>
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
  <p class=MsoNormal align=center style='text-align:center'><img width=13
  height=13 id="_x0000_i1025" src="<?=$imgpath.(($row['is_new']=='1')?$checked:$unchecked)?>"></p>
  </td>
 </tr>
 <?php } ?>
</table>

</div>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>
<?php } ?>

<p class=MsoNormal><b style='mso-bidi-font-weight:normal'>- Заключение на
лекаря/лекарите, провели прегледите:<o:p></o:p></b></p>

<?php
$rows = $dbInst->getDoctorsDesc($checkup_id);
if($rows) {
	foreach ($rows as $row) {
		if($row['conclusion'] == '') continue;
?>
<p class=MsoNormal><?=$dbInst->my_mb_ucfirst(HTMLFormat($row['SpecialistName']))?>: <?=HTMLFormat($row['conclusion'])?></p>
<?php } ?>
<p class=MsoNormal>&nbsp;</p>
<?php } ?>


<p class=MsoNormal><b style='mso-bidi-font-weight:normal'>- Заключение на
службата по трудова медицина:<o:p></o:p></b></p>

<?php
switch ($f['stm_conclusion']) {
	case '1':
		echo '<p class=MsoNormal>Може да изпълнява посочената длъжност/професия '.HTMLFormat($f['position_name']).' в '.HTMLFormat($f['firm_name']).'</p>';
		break;
	case '2':
		echo '<p class=MsoNormal>Може да изпълнява посочената длъжност/професия '.HTMLFormat($f['position_name']).' в '.HTMLFormat($f['firm_name']).' при следните условия:</p>';
		if(!empty($f['stm_conditions'])) {
  			echo '<p class=MsoNormal>'.HTMLFormat($f['stm_conditions']).'</p>';
  		}
		break;
	case '0':
		echo '<p class=MsoNormal>Не може да изпълнява посочената длъжност/професия '.HTMLFormat($f['position_name']).' в '.HTMLFormat($f['firm_name']).'</p>';
		break;
  	case '3':
  		echo '<p class=MsoNormal>Не може да се прецени пригодността на работещия да изпълнява посочената длъжност/професия '.HTMLFormat($f['position_name']).' в '.HTMLFormat($f['firm_name']).'</p>';
  		if(!empty($f['stm_conditions'])) {
  			echo '<p class=MsoNormal>'.HTMLFormat($f['stm_conditions']).'</p>';
  		}
	default:
		break;
}
?>

<?php w_footer($s); ?>

</div>

</body>

</html>
