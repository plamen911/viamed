<?php
// http://localhost/stm2008/viamed/w_stm_conclusion_prchk.php?worker_id=23795&BGSTM=821110dac18ffe8635c11c1b5b0b0361&offline=1
// http://localhost/stm2008/viamed/w_rtf_stm_conclusion_prchk.php?precheckup_id=831&BGSTM=eb930dd2b256bbc9963e21472d207705

require('includes.php');

$offline = (isset($_GET['offline']) && $_GET['offline'] == '1') ? 1 : 0;

$worker_id = (isset($_GET['worker_id']) && is_numeric($_GET['worker_id'])) ? intval($_GET['worker_id']) : 0;
$f = $dbInst->getWorkerInfo($worker_id);
if(!$f) {
	die('Липсва индентификатор на работещия!');
}
$s = $dbInst->getStmInfo();

$stm_name = preg_replace('/\<br\s*\/?\>/', '', $s['stm_name']);

$line = $dbInst->getFirmInfo($f['firm_id']);

//$unchecked = 'image001.jpg';
//$checked = 'image002.jpg';
$unchecked = 'unchecked.gif';
$checked = 'checked.gif';
//$imgpath = 'img/';
$imgpath = "http://" . ((isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:$_SERVER['SERVER_ADDR'].':'.$_SERVER['SERVER_PORT'])) . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/img/";

if(!$offline) {
	$firm_name = str_replace(' ', '_', $line['firm_name']);
	$firm_name = str_replace('"', '', $firm_name);
	$firm_name = str_replace('\'', '', $firm_name);
	$firm_name = str_replace('”', '', $firm_name);
	$firm_name = str_replace('„', '', $firm_name);
	$firm_name = str_replace('_-_', '_', $firm_name);
	
	$worker_name = str_replace(' ', '_', (mb_substr($f['fname'], 0, 1, 'utf-8').' '.$f['lname']));

	require_once("cyrlat.class.php");
	$cyrlat = new CyrLat;
	$filename = 'Zaklyuchenie_predv_pregled_'.$cyrlat->cyr2lat($worker_name.'_'.$firm_name).'-'.$f['worker_id'].'.doc';

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
<!--<link rel=File-List href="stm_conclusion_prchk_files/filelist.xml">-->
<!--<link rel=Edit-Time-Data href="stm_conclusion_prchk_files/editdata.mso">-->
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
  <o:Revision>20</o:Revision>
  <o:TotalTime>263</o:TotalTime>
  <o:LastPrinted>2008-04-18T09:44:00Z</o:LastPrinted>
  <o:Created>2008-07-04T11:32:00Z</o:Created>
  <o:LastSaved>2008-07-04T12:06:00Z</o:LastSaved>
  <o:Pages>1</o:Pages>
  <o:Words>135</o:Words>
  <o:Characters>776</o:Characters>
  <o:Company>СТМ</o:Company>
  <o:Lines>6</o:Lines>
  <o:Paragraphs>1</o:Paragraphs>
  <o:CharactersWithSpaces>910</o:CharactersWithSpaces>
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

<p class=MsoNormal align=center style='text-align:center'><b style='mso-bidi-font-weight:
normal'><o:p>&nbsp;</o:p></b></p>

<p class=MsoNormal align=center style='text-align:center'><b style='mso-bidi-font-weight:
normal'><o:p>&nbsp;</o:p></b></p>

<p class=MsoNormal align=center style='text-align:center'><b style='mso-bidi-font-weight:
normal'><o:p>&nbsp;</o:p></b></p>

<p class=MsoNormal align=center style='text-align:center'><b style='mso-bidi-font-weight:
normal'><o:p>&nbsp;</o:p></b></p>

<p class=MsoNormal align=center style='text-align:center'><b style='mso-bidi-font-weight:
normal'><span style='font-size:22.0pt'>З А К Л Ю Ч Е Н И Е<o:p></o:p></span></b></p>

<p class=MsoNormal align=center style='text-align:center'><b style='mso-bidi-font-weight:
normal'><o:p>&nbsp;</o:p></b></p>

<p class=MsoNormal align=center style='text-align:center'><b style='mso-bidi-font-weight:
normal'><o:p>&nbsp;</o:p></b></p>

<p class=MsoNormal align=center style='text-align:center'><b style='mso-bidi-font-weight:
normal'><span style='font-size:14.0pt'>от <?=mb_strtoupper($dbInst->shortStmName(HTMLFormat($stm_name)), 'utf-8')?><o:p></o:p></span></b></p>

<p class=MsoNormal align=center style='text-align:center'><b style='mso-bidi-font-weight:
normal'><span style='font-size:14.0pt'><o:p>&nbsp;</o:p></span></b></p>

<p class=MsoNormal align=center style='text-align:center'><b style='mso-bidi-font-weight:
normal'><span style='font-size:14.0pt'>за пригодността на лицето <?=((isset($f))?mb_strtoupper(HTMLFormat($f['fname'].' '.$f['sname'].' '.$f['lname']), 'utf-8'):'')?><o:p></o:p></span></b></p>

<p class=MsoNormal align=center style='text-align:center'><b style='mso-bidi-font-weight:
normal'><span style='font-size:14.0pt'><o:p>&nbsp;</o:p></span></b></p>

<p class=MsoNormal align=center style='text-align:center'><b style='mso-bidi-font-weight:
normal'><span style='font-size:14.0pt'>да изпълнява длъжността <?=((isset($f))?mb_strtoupper(HTMLFormat($f['position_name']), 'utf-8'):'')?><o:p></o:p></span></b></p>

<p class=MsoNormal align=center style='text-align:center'><b style='mso-bidi-font-weight:
normal'><span style='font-size:14.0pt'><o:p>&nbsp;</o:p></span></b></p>

<p class=MsoNormal align=center style='text-align:center'><b style='mso-bidi-font-weight:
normal'><span style='font-size:14.0pt'>в <?=((isset($line))?mb_strtoupper(HTMLFormat($line['firm_name']), 'utf-8'):'')?><?=((isset($line))?' - '.HTMLFormat($line['location_name']):'')?><o:p></o:p></span></b></p>

<p class=MsoNormal><span style='font-size:14.0pt'><o:p>&nbsp;</o:p></span></p>

<p class=MsoNormal><span style='font-size:14.0pt'><o:p>&nbsp;</o:p></span></p>

<p class=MsoNormal style='text-align:justify'><span style='font-size:14.0pt'><span
style='mso-tab-count:1'>         </span>Въз основа на условията на труд и
данните от <b style='mso-bidi-font-weight:normal'>предварителните</b> <b
style='mso-bidi-font-weight:normal'>медицински прегледи</b>, проведени в <?=((isset($f))?HTMLFormat($f['prchk_author']):'')?> на <?=((isset($f))?HTMLFormat($f['prchk_date2']):'')?> г.,<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:14.0pt'><o:p>&nbsp;</o:p></span></p>

<p class=MsoNormal align=center style='text-align:center'><span
style='font-size:14.0pt'>Лицето <b style='mso-bidi-font-weight:normal'><span
style='text-transform:uppercase'><?=((isset($f))?mb_strtoupper(HTMLFormat($f['fname'].' '.$f['sname'].' '.$f['lname']), 'utf-8'):'')?><o:p></o:p></span></b></span></p>

<p class=MsoNormal><b style='mso-bidi-font-weight:normal'><span
style='font-size:14.0pt'><o:p>&nbsp;</o:p></span></b></p>

<p class=MsoNormal style='text-indent:35.4pt'><span style='font-size:14.0pt'><img width=13 height=13 src="<?=$imgpath.((isset($f['prchk_conclusion'])&&$f['prchk_conclusion']=='1')?$checked:$unchecked)?>" alt="*"><span
style='mso-spacerun:yes'> </span><span style='mso-spacerun:yes'> </span><?php if(isset($f['prchk_conclusion'])&&$f['prchk_conclusion']=='1') { ?><b style='mso-bidi-font-weight:normal'>може да изпълнява посочената длъжност.</b><?php } else { ?>може да изпълнява посочената длъжност.<?php } ?><o:p></o:p></span></p>

<p class=MsoNormal><b style='mso-bidi-font-weight:normal'><span
style='font-size:14.0pt'><o:p>&nbsp;</o:p></span></b></p>

<p class=MsoNormal style='text-indent:35.4pt'><span style='font-size:14.0pt'><img width=13 height=13
src="<?=$imgpath.((isset($f['prchk_conclusion'])&&$f['prchk_conclusion']=='2')?$checked:$unchecked)?>" alt="*"><span
style='mso-spacerun:yes'> </span><span style='mso-spacerun:yes'> </span>може да
изпълнява посочената длъжност/професия при следните условия<o:p></o:p></span></p>

<?php if(isset($f) && $f['prchk_conditions'] != '') { ?>
<p class=MsoNormal style='text-indent:35.4pt'><b style='mso-bidi-font-weight:
normal'><span style='font-size:14.0pt'><b style='mso-bidi-font-weight:normal'><?=HTMLFormat($f['prchk_conditions'])?></b><o:p></o:p></span></b></p>
<?php } ?>

<p class=MsoNormal><span style='font-size:14.0pt'><o:p>&nbsp;</o:p></span></p>

<p class=MsoNormal style='text-indent:35.4pt'><span style='font-size:14.0pt'><img width=13 height=13
src="<?=$imgpath.((isset($f['prchk_conclusion'])&&$f['prchk_conclusion']=='0')?$checked:$unchecked)?>" alt="*"><span
style='mso-spacerun:yes'> </span><span style='mso-spacerun:yes'> </span><?php if(isset($f['prchk_conclusion'])&&$f['prchk_conclusion']=='0') { ?><b style='mso-bidi-font-weight:normal'>не може да изпълнява посочената длъжност/професия в съответното предприятие.</b><?php } else { ?>не може да изпълнява посочената длъжност/професия в съответното предприятие.<?php } ?><o:p></o:p></span></p>

<?php w_footer($s, $f['prchk_stm_date2']); ?>

</div>

</body>

</html>
