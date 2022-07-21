<?php
// http://localhost/stm2008/hipokrat_aec2/w_mchkup.php?firm_id=100&date_from=01.01.2007&date_to=31.12.2008&offline=1
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

$sickonly = (isset($_GET['sickonly'])) ? intval($_GET['sickonly']) : 1;

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

	$period = str_replace(', ', '_', $dbInst->extractYear($date_from, $date_to));
	$period = str_replace(' и ', '_', $period);

	require_once("cyrlat.class.php");
	$cyrlat = new CyrLat;
	$filename = 'Spravka_bolnichni_'.$firm_id.'_'.$cyrlat->cyr2lat($period.'_'.$firm_name).'.doc';

	header("Pragma: public");
	header("Content-Disposition: attachment; filename=\"$filename\";");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	//header("Cache-Control: private", false);
	header("Content-Type: application/octet-stream");
	//header("Content-type: application/msword;");
	//$imgpath = str_replace('/','\\',str_replace(basename($_SERVER['PHP_SELF']),'',$_SERVER["SCRIPT_FILENAME"])).'img\\';
}

?><html>

<head>
<meta http-equiv=Content-Type content="text/html; charset=utf-8">
<meta name=Generator content="Microsoft Word 11 (filtered)">
<title><?=((isset($stm_name))?HTMLFormat($stm_name):'СЛУЖБА ПО ТРУДОВА МЕДИЦИНА')?></title>
<style>
<!--
 /* Font Definitions */
 @font-face
	{font-family:Tahoma;
	panose-1:2 11 6 4 3 5 4 4 2 4;}
 /* Style Definitions */
 p.MsoNormal, li.MsoNormal, div.MsoNormal
	{margin:0cm;
	margin-bottom:.0001pt;
	font-size:12.0pt;
	font-family:"Times New Roman";}
p.msoacetate0, li.msoacetate0, div.msoacetate0
	{margin:0cm;
	margin-bottom:.0001pt;
	font-size:8.0pt;
	font-family:Tahoma;}
@page Section1
	{size:595.3pt 841.9pt;
	margin:35.95pt 70.85pt 53.95pt 70.85pt;}
div.Section1
	{page:Section1;}
-->
</style>

</head>

<body lang=BG>

<div class=Section1>

<?php w_heading($s); ?>

</div>

<p class=MsoNormal><b><i><span style='font-size:20.0pt'>&nbsp;</span></i></b></p>

<p class=MsoNormal align=center style='text-align:center'><b><span
style='font-size:20.0pt'>Справка</span></b></p>

<p class=MsoNormal align=center style='text-align:center'><b><span
style='font-size:14.0pt'>на работещите в <?=((isset($f['firm_name']))?HTMLFormat($f['firm_name']):'')?></span></b></p>

<p class=MsoNormal align=center style='text-align:center'><b><span
style='font-size:14.0pt'>с болнични листове от <?=date('d.m.Y', strtotime($date_from))?> г. до <?=date('d.m.Y', strtotime($date_to))?> г.</span></b></p>

<p class=MsoNormal>&nbsp;</p>

<p class=MsoNormal>&nbsp;</p>

<p class=MsoNormal>&nbsp;</span></p>

<?php
$sql = "SELECT `chart_id`, `worker_id`, `medical_types`
		FROM `patient_charts`
		WHERE `firm_id` = $firm_id
		AND (julianday(`hospital_date_from`) BETWEEN julianday('$date_from') AND julianday('$date_to'))";
$rows = $dbInst->query($sql);
if(!empty($rows)) {
	$workers = array();
	foreach ($rows as $row) {
		if(!empty($row['medical_types']) && $medical_types = unserialize($row['medical_types'])) {
			$workers[$row['worker_id']][] = implode(', ', $medical_types);
		}
	}
	$sql = "SELECT fname, sname, lname, egn, worker_id, date_retired, position_name
			FROM workers w
			LEFT JOIN firm_struct_map m ON (m.map_id = w.map_id )
			LEFT JOIN firm_positions i ON (i.position_id = m.position_id) 
			WHERE w.firm_id = $firm_id
			AND w.worker_id IN (".implode(',', array_keys($workers)).")
			AND w.is_active = '1'
			".(($subdivision_id)?" AND m.subdivision_id = '$subdivision_id' ":'')."
			GROUP BY w.worker_id
			ORDER BY w.date_retired, w.fname, w.sname, w.lname, w.egn, w.worker_id";
	$rows = $dbInst->query($sql);
	if(!empty($rows)) {
		?>
<table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 width="100%"
 style='width:100.0%;margin-left:1.9pt;border-collapse:collapse'>
 <tr>
  <td width=53 style='width:39.5pt;border:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b>№ по ред</b></p>
  </td>
  <td width=204 style='width:153.0pt;border:solid windowtext 1.0pt;border-left:
  none;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b>Име</b></p>
  </td>
  <td width=115 style='width:86.1pt;border:solid windowtext 1.0pt;border-left:
  none;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b>ЕГН</b></p>
  </td>
  <td width=124 style='width:92.9pt;border:solid windowtext 1.0pt;border-left:
  none;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b>Длъжност</b></p>
  </td>
  <td width=124 style='width:92.9pt;border:solid windowtext 1.0pt;border-left:
  none;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b>Болнични листове</b></p>
  </td>
 </tr>
		<?php
		$i = 0;
		foreach ($rows as $row) {
			$row['patient_charts_num'] = (isset($workers[$row['worker_id']])) ? count($workers[$row['worker_id']]) : 0;
			?>
 <tr>
  <td width=53 valign=top style='width:39.5pt;border:solid windowtext 1.0pt;
  border-top:none;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><?=++$i?>.</p>
  </td>
  <td width=204 valign=top style='width:153.0pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><?=((!empty($row['date_retired'])) ? '*' : '').HTMLFormat(trim($row['fname'].' '.$row['sname'].' '.$row['lname']))?></p>
  </td>
  <td width=115 valign=top style='width:86.1pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><?=$row['egn']?></p>
  </td>
  <td width=124 valign=top style='width:92.9pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><?=HTMLFormat($row['position_name'])?></p>
  </td>
  <td width=124 valign=top style='width:92.9pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><?=HTMLFormat($row['patient_charts_num'])?></p>
  </td>
 </tr>
			<?php
		}
	}
	?>
</table>
	<?php
} else {
	?>
<p class=MsoNormal>Няма работещи с регистрирани болнични за периода.</p>
	<?php
}
?>

<p class=MsoNormal><span style='font-size:14.0pt'>&nbsp;</span></p>

<p class=MsoNormal><span style='font-size:14.0pt'>&nbsp;</span></p>

<p class=MsoNormal><span style='font-size:14.0pt'><?=date("d.m.Y")?> г.                                                                                                                                          Ръководител
СТМ:</span></p>

<p class=MsoNormal align=right style='text-align:right'><span style='font-size:
14.0pt'>(<?=HTMLFormat($s['chief'])?>)</span></p>

</div>

</body>

</html>
