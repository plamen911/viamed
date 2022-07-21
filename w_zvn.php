<?php
// http://localhost/stm2008/hipokrat/w_zvn.php?firm_id=228&date_from=01.01.2007&date_to=31.12.2008&offline=1
require('includes.php');

$offline = (isset($_GET['offline']) && $_GET['offline'] == '1') ? 1 : 0;

$firm_id = (isset($_GET['firm_id']) && is_numeric($_GET['firm_id'])) ? intval($_GET['firm_id']) : 0;
$f = $dbInst->getFirmInfo($firm_id);
if(!$f) {
	die('Липсва индентификатор на фирмата!');
}
$s = $dbInst->getStmInfo();

$stm_name = preg_replace('/\<br\s*\/?\>/', '', $s['stm_name']);

$dbInst->makeAllMkbUpperCase();

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

$r = $dbInst->getAnnualReport($firm_id, $date_from, $date_to);

$dt = substr($date_to, 0, 10);
list($last_year, $last_month, $last_day) = explode('-', $dt);

$unchecked = 'unchecked.gif';
$checked = 'checked.gif';
$http = !empty($_SERVER['HTTPS']) ? 'https://' : 'http://';
$imgpath = $http . ((isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:$_SERVER['SERVER_ADDR'].':'.$_SERVER['SERVER_PORT'])) . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/img/";

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
	$filename = 'Spravka_ZVN_'.$firm_id.'_'.$cyrlat->cyr2lat($period.'_'.$firm_name).'.doc';

	header("Pragma: public");
	header("Content-Disposition: attachment; filename=\"$filename\";");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	//header("Cache-Control: private", false);
	header("Content-Type: application/octet-stream");
	//header("Content-type: application/msword;");
	//$imgpath = str_replace('/','\\',str_replace(basename($_SERVER['PHP_SELF']),'',$_SERVER["SCRIPT_FILENAME"])).'img\\';
}

$avgWorkers = (isset($r)) ? ($r['anual_workers'] + (($r['joined_workers'] + $r['retired_workers']) / 2)) : 0;

/*$query = "	SELECT w.worker_id,
			m.group_id,
			g.group_name, 
			d.reason_id,
			i.position_name,
			i.position_id,
			COUNT(i.position_id) AS num_employees,
			SUM(d.days_off) AS num_days_off
			FROM patient_charts d
			LEFT JOIN mkb m ON (m.mkb_id = d.mkb_id)
			LEFT JOIN mkb_groups g ON (g.group_id = m.group_id)
			LEFT JOIN workers w ON (w.worker_id = d.worker_id)
			LEFT JOIN firm_struct_map m1 ON (m1.map_id = w.map_id )
			LEFT JOIN work_places p ON (p.wplace_id = m1.wplace_id)
			LEFT JOIN firm_positions i ON (i.position_id = m1.position_id)
			WHERE d.firm_id = $firm_id
			AND w.is_active = '1'
			AND ( w.date_retired = '' OR julianday(w.date_retired) >= julianday('$date_from') )
			AND ( w.date_curr_position_start = '' OR julianday(w.date_curr_position_start) <= julianday('$date_to') )
			AND ( d.`medical_types` = 'a:1:{i:0;s:1:\"1\";}' OR d.`medical_types` = 'a:1:{i:0;s:1:\"2\";}' OR d.`medical_types` = 'a:1:{i:0;i:1;}' OR d.`medical_types` = 'a:1:{i:0;i:2;}' )
			AND ((julianday(d.hospital_date_from) >= julianday('$date_from'))
			AND (julianday(d.hospital_date_from) <= julianday('$date_to')))
			GROUP BY g.group_id, d.reason_id, i.position_id
			ORDER BY g.group_id, m.mkb_id, i.position_name";
*/
$query = "	SELECT w.worker_id,
			m.group_id,
			g.group_name, 
			d.reason_id,
			i.position_name,
			i.position_id,
			COUNT(i.position_id) AS num_employees,
			SUM(d.days_off) AS num_days_off
			FROM patient_charts d
			LEFT JOIN mkb m ON (m.mkb_id = d.mkb_id)
			LEFT JOIN mkb_groups g ON (g.group_id = m.group_id)
			LEFT JOIN workers w ON (w.worker_id = d.worker_id)
			LEFT JOIN firm_struct_map m1 ON (m1.map_id = w.map_id )
			LEFT JOIN work_places p ON (p.wplace_id = m1.wplace_id)
			LEFT JOIN firm_positions i ON (i.position_id = m1.position_id)
			WHERE d.firm_id = $firm_id
			AND w.is_active = '1'
			AND ( w.date_retired = '' OR julianday(w.date_retired) >= julianday('$date_from') )
			AND ( w.date_curr_position_start = '' OR julianday(w.date_curr_position_start) <= julianday('$date_to') )
			AND ( d.`medical_types` = 'a:1:{i:0;s:1:\"1\";}' OR d.`medical_types` = 'a:1:{i:0;s:1:\"2\";}' OR d.`medical_types` = 'a:1:{i:0;i:1;}' OR d.`medical_types` = 'a:1:{i:0;i:2;}' )
			AND ((julianday(d.hospital_date_from) >= julianday('$date_from'))
			AND (julianday(d.hospital_date_from) <= julianday('$date_to')))
			GROUP BY g.group_id, d.reason_id, i.position_id
			ORDER BY g.group_id, m.mkb_id, i.position_name";

$rows = $dbInst->query($query);

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
	{size:841.9pt 595.3pt;
	margin:35.95pt 31.9pt 26.95pt 36.0pt;}
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
style='font-size:14.0pt'>за ЗВН по професионални групи на работещите в </span></b></p>

<p class=MsoNormal align=center style='text-align:center'><b><span
style='font-size:14.0pt'><?=((isset($f['firm_name']))?HTMLFormat($f['firm_name']):'')?> за периода <?=date('d.m.Y', strtotime($date_from))?> 
г. – <?=date('d.m.Y', strtotime($date_to))?> г.</span></b></p>

<p class=MsoNormal>&nbsp;</p>

<p class=MsoNormal>&nbsp;</p>

<p class=MsoNormal>I. Списък на ЗВН по нозологична структура</p>

<table class=MsoTableGrid border=1 cellspacing=0 cellpadding=0 width="100%"
 style='width:100.0%;border-collapse:collapse;border:none'>
 <tr style='height:13.85pt'>
  <td width="6%" rowspan=2 style='width:6.24%;border:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt;height:13.85pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>МКБ 10</span></b></p>
  </td>
  <td width="6%" rowspan=2 style='width:6.7%;border:solid windowtext 1.0pt;
  border-left:none;padding:0cm 5.4pt 0cm 5.4pt;height:13.85pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>Причина</span></b></p>
  </td>
  <td width="17%" rowspan=2 style='width:17.12%;border:solid windowtext 1.0pt;
  border-left:none;padding:0cm 5.4pt 0cm 5.4pt;height:13.85pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>Професия</span></b></p>
  </td>
  <td width="7%" rowspan=2 style='width:7.6%;border:solid windowtext 1.0pt;
  border-left:none;padding:0cm 5.4pt 0cm 5.4pt;height:13.85pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>Бр. </span></b></p>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>служители</span></b></p>
  </td>
  <td width="5%" rowspan=2 style='width:5.16%;border:solid windowtext 1.0pt;
  border-left:none;padding:0cm 5.4pt 0cm 5.4pt;height:13.85pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>% от </span></b></p>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>общия </span></b></p>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>брой</span></b></p>
  </td>
  <td width="5%" rowspan=2 style='width:5.58%;border:solid windowtext 1.0pt;
  border-left:none;padding:0cm 5.4pt 0cm 5.4pt;height:13.85pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>Бр. </span></b></p>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>дни</span></b></p>
  </td>
  <td width="6%" colspan=2 style='width:6.9%;border:solid windowtext 1.0pt;
  border-left:none;padding:0cm 5.4pt 0cm 5.4pt;height:13.85pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>Пол</span></b></p>
  </td>
  <td width="25%" colspan=5 style='width:25.48%;border:solid windowtext 1.0pt;
  border-left:none;padding:0cm 5.4pt 0cm 5.4pt;height:13.85pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>Възрастови групи</span></b></p>
  </td>
  <td width="19%" colspan=3 style='width:19.2%;border:solid windowtext 1.0pt;
  border-left:none;padding:0cm 5.4pt 0cm 5.4pt;height:13.85pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>Групи по трудов стаж</span></b></p>
  </td>
 </tr>
 <tr style='height:13.85pt'>
  <td width="3%" style='width:3.3%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt;
  height:13.85pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>М</span></b></p>
  </td>
  <td width="3%" style='width:3.6%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt;
  height:13.85pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>Ж</span></b></p>
  </td>
  <td width="5%" style='width:5.1%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt;
  height:13.85pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>до 25</span></b></p>
  </td>
  <td width="5%" style='width:5.1%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt;
  height:13.85pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>25-35</span></b></p>
  </td>
  <td width="5%" style='width:5.1%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt;
  height:13.85pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>35-45</span></b></p>
  </td>
  <td width="5%" style='width:5.1%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt;
  height:13.85pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>45-55</span></b></p>
  </td>
  <td width="5%" style='width:5.1%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt;
  height:13.85pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>над 55</span></b></p>
  </td>
  <td width="6%" style='width:6.4%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt;
  height:13.85pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>до 5 г.</span></b></p>
  </td>
  <td width="6%" style='width:6.4%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt;
  height:13.85pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>5-10 г.</span></b></p>
  </td>
  <td width="6%" style='width:6.4%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt;
  height:13.85pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>над 10 г.</span></b></p>
  </td>
 </tr>
 
   <?php
   if(!empty($rows)) {
   	$group_id = -1;
   	$reason_id = '';

   	$zvn_employees = array();
   	$total_num_days_off = 0;

   	foreach ($rows as $row) {

   		if(empty($row['i.position_name'])) continue;

   		$zvn_employees[$row['w.worker_id']] = $row['w.worker_id'];

   		if($reason_id != $row['d.reason_id']) {
   			$reason_name = $row['d.reason_id'];
   			$reason_id = $row['d.reason_id'];
   		} else {
   			$reason_name = '';
   		}

   		if($group_id != $row['m.group_id']) {
   			$group_name = substr(strrchr($row['g.group_name'], '('), 1, -1);
   			$group_id = $row['m.group_id'];
   			$reason_name = $row['d.reason_id'];
   			$reason_id = $row['d.reason_id'];
   		} else {
   			$group_name = '';
   		}

   		$position_id = (!empty($row['i.position_id'])) ? $row['i.position_id'] : 0;
   		$position_name = (!empty($row['i.position_name'])) ? $row['i.position_name'] : '';
   		$num_employees = (!empty($row['num_employees'])) ? $row['num_employees'] : 0;
   		$percent_employees = ($avgWorkers) ? ($num_employees * 100) / $avgWorkers : 0;
   		$num_days_off = $row['num_days_off'];

   		$men = $dbInst->query("		SELECT COUNT(*) AS cnt
									FROM patient_charts d
									LEFT JOIN mkb m ON (m.mkb_id = d.mkb_id)
									LEFT JOIN mkb_groups g ON (g.group_id = m.group_id)
									LEFT JOIN workers w ON (w.worker_id = d.worker_id)
									LEFT JOIN firm_struct_map m1 ON (m1.map_id = w.map_id )
									LEFT JOIN work_places p ON (p.wplace_id = m1.wplace_id)
									LEFT JOIN firm_positions i ON (i.position_id = m1.position_id)	
									WHERE d.firm_id = $firm_id
   									AND w.is_active = '1'
   									AND ( w.date_retired = '' OR julianday(w.date_retired) >= julianday('$date_from') )
  									AND ( d.`medical_types` = 'a:1:{i:0;s:1:\"1\";}' OR d.`medical_types` = 'a:1:{i:0;s:1:\"2\";}' OR d.`medical_types` = 'a:1:{i:0;i:1;}' OR d.`medical_types` = 'a:1:{i:0;i:2;}' )
  									AND ((julianday(d.hospital_date_from) >= julianday('$date_from'))
									AND (julianday(d.hospital_date_from) <= julianday('$date_to')))
									AND g.group_id = $group_id
									AND d.reason_id = '$reason_id'
									AND i.position_id = $position_id
									AND (w.sex = 'М' OR w.sex = '')
									GROUP BY g.group_id, d.reason_id, i.position_id");
   		$num_men = (!empty($men[0]['cnt'])) ? $men[0]['cnt'] : 0;

   		$women = $dbInst->query("	SELECT COUNT(*) AS cnt
									FROM patient_charts d
									LEFT JOIN mkb m ON (m.mkb_id = d.mkb_id)
									LEFT JOIN mkb_groups g ON (g.group_id = m.group_id)
									LEFT JOIN workers w ON (w.worker_id = d.worker_id)
									LEFT JOIN firm_struct_map m1 ON (m1.map_id = w.map_id )
									LEFT JOIN work_places p ON (p.wplace_id = m1.wplace_id)
									LEFT JOIN firm_positions i ON (i.position_id = m1.position_id)	
									WHERE d.firm_id = $firm_id
   									AND w.is_active = '1'
   									AND ( w.date_retired = '' OR julianday(w.date_retired) >= julianday('$date_from') )
  									AND ( d.`medical_types` = 'a:1:{i:0;s:1:\"1\";}' OR d.`medical_types` = 'a:1:{i:0;s:1:\"2\";}' OR d.`medical_types` = 'a:1:{i:0;i:1;}' OR d.`medical_types` = 'a:1:{i:0;i:2;}' )
  									AND ((julianday(d.hospital_date_from) >= julianday('$date_from'))
									AND (julianday(d.hospital_date_from) <= julianday('$date_to')))
									AND g.group_id = $group_id
									AND d.reason_id = '$reason_id'
									AND i.position_id = $position_id
									AND w.sex = 'Ж'
									GROUP BY g.group_id, d.reason_id, i.position_id");
   		$num_women = (!empty($women[0]['cnt'])) ? $women[0]['cnt'] : 0;

   		$ageUpTo25 = 0;
   		$age25UpTo35 = 0;
   		$age35UpTo45 = 0;
   		$age45UpTo55 = 0;
   		$ageAbove55 = 0;
   		
   		$workExpUpTo5 = 0;
   		$workExp5UpTo10 = 0;
   		$workExpAbove10 = 0;

   		$ages = $dbInst->query("	SELECT w.egn, w.date_curr_position_start
									FROM patient_charts d
									LEFT JOIN mkb m ON (m.mkb_id = d.mkb_id)
									LEFT JOIN mkb_groups g ON (g.group_id = m.group_id)
									LEFT JOIN workers w ON (w.worker_id = d.worker_id)
									LEFT JOIN firm_struct_map m1 ON (m1.map_id = w.map_id )
									LEFT JOIN work_places p ON (p.wplace_id = m1.wplace_id)
									LEFT JOIN firm_positions i ON (i.position_id = m1.position_id)	
									WHERE d.firm_id = $firm_id
   									AND w.is_active = '1'
   									AND ( w.date_retired = '' OR julianday(w.date_retired) >= julianday('$date_from') )
  									AND ( d.`medical_types` = 'a:1:{i:0;s:1:\"1\";}' OR d.`medical_types` = 'a:1:{i:0;s:1:\"2\";}' OR d.`medical_types` = 'a:1:{i:0;i:1;}' OR d.`medical_types` = 'a:1:{i:0;i:2;}' )
  									AND ((julianday(d.hospital_date_from) >= julianday('$date_from'))
									AND (julianday(d.hospital_date_from) <= julianday('$date_to')))
									AND g.group_id = $group_id
									AND d.reason_id = '$reason_id'
									AND i.position_id = $position_id");
   		if(!empty($ages)) {
   			foreach ($ages as $line) {
   				$birth_year = intval(substr($line['egn'], 0, 2)) + 1900;
   				$birth_month = intval(substr($line['egn'], 2, 2));
   				$birth_day = intval(substr($line['egn'], 4, 2));
   				$t = calculate_age($birth_day, $birth_month, $birth_year, $last_day, $last_month, $last_year);
   				if($t < 25) $ageUpTo25++;
   				elseif ($t >= 25 && $t < 35) $age25UpTo35++;
   				elseif ($t >= 35 && $t < 45) $age35UpTo45++;
   				elseif ($t >= 45 && $t < 55) $age45UpTo55++;
   				elseif ($t >= 55) $ageAbove55++;
   				
   				$date_curr_position_start = $line['date_curr_position_start'];
   				if(empty($date_curr_position_start)) $workExpUpTo5++;
   				else {
   					$dt = substr($date_curr_position_start, 0, 10);
   					list($position_year, $position_month, $position_day) = explode('-', $dt);
   					$t = calculate_age($position_day, $position_month, $position_year, $last_day, $last_month, $last_year);
   					if($t < 5) $workExpUpTo5++;
   					elseif ($t >= 5 && $t < 10) $workExp5UpTo10++;
   					elseif ($t >= 10) $workExpAbove10++;
   				}
   			}
   		}

   		$total_num_days_off += $num_days_off;
  		?>
  		
 <tr>
  <td width="6%" style='width:6.24%;border:solid windowtext 1.0pt;border-top:
  none;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'><?=$group_name?></span></p>
  </td>
  <td width="6%" style='width:6.7%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'><?=$reason_name?></span></p>
  </td>
  <td width="17%" style='width:17.12%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><span
  style='font-size:10.0pt'><?=$position_name?></span></p>
  </td>
  <td width="7%" style='width:7.6%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'><?=$num_employees?></span></p>
  </td>
  <td width="5%" style='width:5.16%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'><?=number_format($percent_employees, 2, ',', ' ')?></span></p>
  </td>
  <td width="5%" style='width:5.58%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'><?=$num_days_off?></span></p>
  </td>
  <td width="3%" style='width:3.3%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'><?=$num_men?></span></p>
  </td>
  <td width="3%" style='width:3.6%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'><?=$num_women?></span></p>
  </td>
  <td width="5%" style='width:5.1%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'><?=((!empty($ageUpTo25))?$ageUpTo25:'-')?></span></p>
  </td>
  <td width="5%" style='width:5.1%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'><?=((!empty($age25UpTo35))?$age25UpTo35:'-')?></span></p>
  </td>
  <td width="5%" style='width:5.1%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'><?=((!empty($age35UpTo45))?$age35UpTo45:'-')?></span></p>
  </td>
  <td width="5%" style='width:5.1%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'><?=((!empty($age45UpTo55))?$age45UpTo55:'-')?></span></p>
  </td>
  <td width="5%" style='width:5.1%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'><?=((!empty($ageAbove55))?$ageAbove55:'-')?></span></p>
  </td>
  <td width="6%" style='width:6.4%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'><?=((!empty($workExpUpTo5))?$workExpUpTo5:'-')?></span></p>
  </td>
  <td width="6%" style='width:6.4%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'><?=((!empty($workExp5UpTo10))?$workExp5UpTo10:'-')?></span></p>
  </td>
  <td width="6%" style='width:6.4%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'><?=((!empty($workExpAbove10))?$workExpAbove10:'-')?></span></p>
  </td>
 </tr>  		
  		
<?php 
   	}

   	$total_num_employees = count($zvn_employees);

   	$query = "	SELECT COUNT(*) AS cnt
				FROM workers
				WHERE firm_id = $firm_id
   				AND is_active = '1'
   				AND ( date_retired = '' OR julianday(date_retired) >= julianday('$date_from') )
				AND (sex = 'М' OR sex = '')";
   	if($total_num_employees) $query .= " AND worker_id IN (".implode(', ', $zvn_employees).")";
   	$men = $dbInst->query($query);
   	$total_num_men = (!empty($men[0]['cnt'])) ? $men[0]['cnt'] : 0;

   	$query = "	SELECT COUNT(*) AS cnt
				FROM workers
				WHERE firm_id = $firm_id
   				AND is_active = '1'
   				AND ( date_retired = '' OR julianday(date_retired) >= julianday('$date_from') )
				AND sex = 'Ж'";
   	if($total_num_employees) $query .= " AND worker_id IN (".implode(', ', $zvn_employees).")";
   	$women = $dbInst->query($query);
   	$total_num_women = (!empty($women[0]['cnt'])) ? $women[0]['cnt'] : 0;

   	$total_ageUpTo25 = 0;
   	$total_age25UpTo35 = 0;
   	$total_age35UpTo45 = 0;
   	$total_age45UpTo55 = 0;
   	$total_ageAbove55 = 0;

   	$query = "	SELECT egn
				FROM workers
				WHERE firm_id = $firm_id
   				AND is_active = '1'
   				AND ( date_retired = '' OR julianday(date_retired) >= julianday('$date_from') )";
   	if($total_num_employees) $query .= " AND worker_id IN (".implode(', ', $zvn_employees).")";
   	$ages = $dbInst->query($query);
   	if(!empty($ages)) {
   		foreach ($ages as $age) {
   			$birth_year = intval(substr($age['egn'], 0, 2)) + 1900;
   			$birth_month = intval(substr($age['egn'], 2, 2));
   			$birth_day = intval(substr($age['egn'], 4, 2));
   			$t = calculate_age($birth_day, $birth_month, $birth_year, $last_day, $last_month, $last_year);
   			if($t < 25) $total_ageUpTo25++;
   			elseif ($t >= 25 && $t < 35) $total_age25UpTo35++;
   			elseif ($t >= 35 && $t < 45) $total_age35UpTo45++;
   			elseif ($t >= 45 && $t < 55) $total_age45UpTo55++;
   			elseif ($t >= 55) $total_ageAbove55++;
   		}
   	}

   	$total_workExpUpTo5 = 0;
   	$total_workExp5UpTo10 = 0;
   	$total_workExpAbove10 = 0;

   	$query = "	SELECT date_curr_position_start
				FROM workers
				WHERE firm_id = $firm_id
   				AND is_active = '1'
   				AND ( date_retired = '' OR julianday(date_retired) >= julianday('$date_from') )";
   	if($total_num_employees) $query .= " AND worker_id IN (".implode(', ', $zvn_employees).")";
   	$workExp = $dbInst->query($query);
   	if(!empty($workExp)) {
   		foreach ($workExp as $work) {
   			$date_curr_position_start = $work['date_curr_position_start'];
   			if(empty($date_curr_position_start)) $workExpUpTo5++;
   			else {
   				$dt = substr($date_curr_position_start, 0, 10);
   				list($position_year, $position_month, $position_day) = explode('-', $dt);
   				$t = calculate_age($position_day, $position_month, $position_year, $last_day, $last_month, $last_year);
   				if($t < 5) $total_workExpUpTo5++;
   				elseif ($t >= 5 && $t < 10) $total_workExp5UpTo10++;
   				elseif ($t >= 10) $total_workExpAbove10++;
   			}
   		}
   	}

?>

 <tr>
  <td width="6%" style='width:6.24%;border:solid windowtext 1.0pt;border-top:
  none;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>ОБЩО</span></b></p>
  </td>
  <td width="6%" style='width:6.7%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><b><span style='font-size:10.0pt'>&nbsp;</span></b></p>
  </td>
  <td width="17%" style='width:17.12%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>&nbsp;</span></b></p>
  </td>
  <td width="7%" style='width:7.6%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'><?=$total_num_employees?></span></b></p>
  </td>
  <td width="5%" style='width:5.16%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'><?=number_format((($avgWorkers) ? ($total_num_employees * 100) / $avgWorkers : 0), 2, ',', ' ')?></span></b></p>
  </td>
  <td width="5%" style='width:5.58%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'><?=$total_num_days_off?></span></b></p>
  </td>
  <td width="3%" style='width:3.3%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'><?=$total_num_men?></span></b></p>
  </td>
  <td width="3%" style='width:3.6%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'><?=$total_num_women?></span></b></p>
  </td>
  <td width="5%" style='width:5.1%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'><?=((!empty($total_ageUpTo25))?$total_ageUpTo25:'-')?></span></b></p>
  </td>
  <td width="5%" style='width:5.1%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'><?=((!empty($total_age25UpTo35))?$total_age25UpTo35:'-')?></span></b></p>
  </td>
  <td width="5%" style='width:5.1%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'><?=((!empty($total_age35UpTo45))?$total_age35UpTo45:'')?></span></b></p>
  </td>
  <td width="5%" style='width:5.1%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'><?=((!empty($total_age45UpTo55))?$total_age45UpTo55:'-')?></span></b></p>
  </td>
  <td width="5%" style='width:5.1%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'><?=((!empty($total_ageAbove55))?$total_ageAbove55:'')?></span></b></p>
  </td>
  <td width="6%" style='width:6.4%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'><?=((!empty($total_workExpUpTo5))?$total_workExpUpTo5:'-')?></span></b></p>
  </td>
  <td width="6%" style='width:6.4%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'><?=((!empty($total_workExp5UpTo10))?$total_workExp5UpTo10:'-')?></span></b></p>
  </td>
  <td width="6%" style='width:6.4%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'><?=((!empty($total_workExpAbove10))?$total_workExpAbove10:'-')?></span></b></p>
  </td>
 </tr>

<?php } ?>

</table>

<p class=MsoNormal>&nbsp;</p>

<p class=MsoNormal>II. Списък на ЗВН по причини</p>

<?php
$query = "	SELECT w.worker_id,
			d.reason_id,
			i.position_name,
			i.position_id,
			SUM(d.days_off) AS num_days_off
			FROM patient_charts d
			LEFT JOIN mkb m ON (m.mkb_id = d.mkb_id)
			LEFT JOIN mkb_groups g ON (g.group_id = m.group_id)
			LEFT JOIN workers w ON (w.worker_id = d.worker_id)
			LEFT JOIN firm_struct_map m1 ON (m1.map_id = w.map_id )
			LEFT JOIN work_places p ON (p.wplace_id = m1.wplace_id)
			LEFT JOIN firm_positions i ON (i.position_id = m1.position_id)
			WHERE d.firm_id = $firm_id
			AND ( d.`medical_types` = 'a:1:{i:0;s:1:\"1\";}' OR d.`medical_types` = 'a:1:{i:0;s:1:\"2\";}' OR d.`medical_types` = 'a:1:{i:0;i:1;}' OR d.`medical_types` = 'a:1:{i:0;i:2;}' )
			AND ((julianday(d.hospital_date_from) >= julianday('$date_from'))
			AND (julianday(d.hospital_date_from) <= julianday('$date_to')))
			AND w.is_active = '1'
			AND ( w.date_retired = '' OR julianday(w.date_retired) >= julianday('$date_from') )
			GROUP BY d.reason_id, i.position_id
			ORDER BY d.reason_id, i.position_name";

$rows = $dbInst->query($query);

?>

<table class=MsoTableGrid border=1 cellspacing=0 cellpadding=0 width="100%"
 style='width:100.0%;border-collapse:collapse;border:none'>
 <tr style='height:13.85pt'>
  <td width="7%" rowspan=2 style='width:7.14%;border:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt;height:13.85pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>Причина</span></b></p>
  </td>
  <td width="18%" rowspan=2 style='width:18.26%;border:solid windowtext 1.0pt;
  border-left:none;padding:0cm 5.4pt 0cm 5.4pt;height:13.85pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>Професия</span></b></p>
  </td>
  <td width="8%" rowspan=2 style='width:8.1%;border:solid windowtext 1.0pt;
  border-left:none;padding:0cm 5.4pt 0cm 5.4pt;height:13.85pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>Бр. </span></b></p>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>служители</span></b></p>
  </td>
  <td width="5%" rowspan=2 style='width:5.5%;border:solid windowtext 1.0pt;
  border-left:none;padding:0cm 5.4pt 0cm 5.4pt;height:13.85pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>% от </span></b></p>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>общия </span></b></p>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>брой</span></b></p>
  </td>
  <td width="5%" rowspan=2 style='width:5.96%;border:solid windowtext 1.0pt;
  border-left:none;padding:0cm 5.4pt 0cm 5.4pt;height:13.85pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>Бр. </span></b></p>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>дни</span></b></p>
  </td>
  <td width="7%" colspan=2 style='width:7.36%;border:solid windowtext 1.0pt;
  border-left:none;padding:0cm 5.4pt 0cm 5.4pt;height:13.85pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>Пол</span></b></p>
  </td>
  <td width="27%" colspan=5 style='width:27.18%;border:solid windowtext 1.0pt;
  border-left:none;padding:0cm 5.4pt 0cm 5.4pt;height:13.85pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>Възрастови групи</span></b></p>
  </td>
  <td width="20%" colspan=3 style='width:20.48%;border:solid windowtext 1.0pt;
  border-left:none;padding:0cm 5.4pt 0cm 5.4pt;height:13.85pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>Групи по трудов стаж</span></b></p>
  </td>
 </tr>
 <tr style='height:13.85pt'>
  <td width="3%" style='width:3.52%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt;height:13.85pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>М</span></b></p>
  </td>
  <td width="3%" style='width:3.84%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt;height:13.85pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>Ж</span></b></p>
  </td>
  <td width="5%" style='width:5.44%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt;height:13.85pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>до 25</span></b></p>
  </td>
  <td width="5%" style='width:5.44%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt;height:13.85pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>25-35</span></b></p>
  </td>
  <td width="5%" style='width:5.44%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt;height:13.85pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>35-45</span></b></p>
  </td>
  <td width="5%" style='width:5.44%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt;height:13.85pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>45-55</span></b></p>
  </td>
  <td width="5%" style='width:5.44%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt;height:13.85pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>над 55</span></b></p>
  </td>
  <td width="6%" style='width:6.82%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt;height:13.85pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>до 5 г.</span></b></p>
  </td>
  <td width="6%" style='width:6.82%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt;height:13.85pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>5-10 г.</span></b></p>
  </td>
  <td width="6%" style='width:6.82%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt;height:13.85pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>над 10 г.</span></b></p>
  </td>
 </tr>
 
 <?php
 if(!empty($rows)) {
 	$reason_id = -1;

 	$total_num_days_off = 0;

 	foreach ($rows as $row) {

 		if(empty($row['i.position_name'])) continue;

 		if($reason_id != $row['d.reason_id']) {
 			$reason_name = $row['d.reason_id'];
 			$reason_id = $row['d.reason_id'];
 		} else {
 			$reason_name = '';
 		}

 		$position_id = (!empty($row['i.position_id'])) ? $row['i.position_id'] : 0;
 		$position_name = (!empty($row['i.position_name'])) ? $row['i.position_name'] : '';


 		$employees = $dbInst->query("	SELECT i.position_id, i.position_name
										FROM patient_charts d
										LEFT JOIN mkb m ON (m.mkb_id = d.mkb_id)
										LEFT JOIN workers w ON (w.worker_id = d.worker_id)
										LEFT JOIN firm_struct_map m1 ON (m1.map_id = w.map_id )
										LEFT JOIN work_places p ON (p.wplace_id = m1.wplace_id)
										LEFT JOIN firm_positions i ON (i.position_id = m1.position_id)
										WHERE d.firm_id = $firm_id
 										AND w.is_active = '1'
										AND ( w.date_retired = '' OR julianday(w.date_retired) >= julianday('$date_from') )
										AND ( d.`medical_types` = 'a:1:{i:0;s:1:\"1\";}' OR d.`medical_types` = 'a:1:{i:0;s:1:\"2\";}' OR d.`medical_types` = 'a:1:{i:0;i:1;}' OR d.`medical_types` = 'a:1:{i:0;i:2;}' )
										AND ( 
											(julianday(d.hospital_date_from) >= julianday('$date_from'))
										  	AND (julianday(d.hospital_date_from) <= julianday('$date_to')) 
										)
										AND d.reason_id = '$reason_id'
										AND i.position_id = $position_id
										GROUP BY w.worker_id, i.position_id");
 		$num_employees = count($employees);
 		$percent_employees = ($avgWorkers) ? ($num_employees * 100) / $avgWorkers : 0;
 		$num_days_off = $row['num_days_off'];



 		$men = $dbInst->query("		SELECT i.position_id, i.position_name
									FROM patient_charts d
									LEFT JOIN mkb m ON (m.mkb_id = d.mkb_id)
									LEFT JOIN workers w ON (w.worker_id = d.worker_id)
									LEFT JOIN firm_struct_map m1 ON (m1.map_id = w.map_id )
									LEFT JOIN work_places p ON (p.wplace_id = m1.wplace_id)
									LEFT JOIN firm_positions i ON (i.position_id = m1.position_id)
									WHERE d.firm_id = $firm_id
 									AND w.is_active = '1'
 									AND ( w.date_retired = '' OR julianday(w.date_retired) >= julianday('$date_from') )
									AND ( d.`medical_types` = 'a:1:{i:0;s:1:\"1\";}' OR d.`medical_types` = 'a:1:{i:0;s:1:\"2\";}' OR d.`medical_types` = 'a:1:{i:0;i:1;}' OR d.`medical_types` = 'a:1:{i:0;i:2;}' )
									AND ( 
										(julianday(d.hospital_date_from) >= julianday('$date_from'))
										AND (julianday(d.hospital_date_from) <= julianday('$date_to')) 
									)
									AND d.reason_id = '$reason_id'
									AND i.position_id = $position_id
									AND (w.sex = 'М' OR w.sex = '')
									GROUP BY w.worker_id, i.position_id");
 		$num_men = count($men);

 		$women = $dbInst->query("	SELECT i.position_id, i.position_name
									FROM patient_charts d
									LEFT JOIN mkb m ON (m.mkb_id = d.mkb_id)
									LEFT JOIN workers w ON (w.worker_id = d.worker_id)
									LEFT JOIN firm_struct_map m1 ON (m1.map_id = w.map_id )
									LEFT JOIN work_places p ON (p.wplace_id = m1.wplace_id)
									LEFT JOIN firm_positions i ON (i.position_id = m1.position_id)
									WHERE d.firm_id = $firm_id
									AND ( d.`medical_types` = 'a:1:{i:0;s:1:\"1\";}' OR d.`medical_types` = 'a:1:{i:0;s:1:\"2\";}' OR d.`medical_types` = 'a:1:{i:0;i:1;}' OR d.`medical_types` = 'a:1:{i:0;i:2;}' )
									AND ( 
										(julianday(d.hospital_date_from) >= julianday('$date_from'))
										AND (julianday(d.hospital_date_from) <= julianday('$date_to')) 
									)
									AND w.is_active = '1'
									AND d.reason_id = '$reason_id'
									AND i.position_id = $position_id
									AND w.sex = 'Ж'
									GROUP BY w.worker_id, i.position_id");
 		$num_women = count($women);

 		$ageUpTo25 = 0;
 		$age25UpTo35 = 0;
 		$age35UpTo45 = 0;
 		$age45UpTo55 = 0;
 		$ageAbove55 = 0;

 		$workExpUpTo5 = 0;
 		$workExp5UpTo10 = 0;
 		$workExpAbove10 = 0;

 		$ages = $dbInst->query("	SELECT egn, date_curr_position_start
									FROM patient_charts d
									LEFT JOIN mkb m ON (m.mkb_id = d.mkb_id)
									LEFT JOIN workers w ON (w.worker_id = d.worker_id)
									LEFT JOIN firm_struct_map m1 ON (m1.map_id = w.map_id )
									LEFT JOIN work_places p ON (p.wplace_id = m1.wplace_id)
									LEFT JOIN firm_positions i ON (i.position_id = m1.position_id)
									WHERE d.firm_id = $firm_id
 									AND w.is_active = '1'
 									AND ( w.date_retired = '' OR julianday(w.date_retired) >= julianday('$date_from') )
									AND ( d.`medical_types` = 'a:1:{i:0;s:1:\"1\";}' OR d.`medical_types` = 'a:1:{i:0;s:1:\"2\";}' OR d.`medical_types` = 'a:1:{i:0;i:1;}' OR d.`medical_types` = 'a:1:{i:0;i:2;}' )
									AND ( 
										(julianday(d.hospital_date_from) >= julianday('$date_from'))
										AND (julianday(d.hospital_date_from) <= julianday('$date_to')) 
									)
									
									AND d.reason_id = '$reason_id'
									AND i.position_id = $position_id
									GROUP BY w.worker_id, i.position_id"); 		
 		if(!empty($ages)) {
 			foreach ($ages as $line) {
 				$birth_year = intval(substr($line['egn'], 0, 2)) + 1900;
 				$birth_month = intval(substr($line['egn'], 2, 2));
 				$birth_day = intval(substr($line['egn'], 4, 2));
 				$t = calculate_age($birth_day, $birth_month, $birth_year, $last_day, $last_month, $last_year);
 				if($t < 25) $ageUpTo25++;
 				elseif ($t >= 25 && $t < 35) $age25UpTo35++;
 				elseif ($t >= 35 && $t < 45) $age35UpTo45++;
 				elseif ($t >= 45 && $t < 55) $age45UpTo55++;
 				elseif ($t >= 55) $ageAbove55++;

 				$date_curr_position_start = $line['date_curr_position_start'];
 				if(empty($date_curr_position_start)) $workExpUpTo5++;
 				else {
 					$dt = substr($date_curr_position_start, 0, 10);
 					list($position_year, $position_month, $position_day) = explode('-', $dt);
 					$t = calculate_age($position_day, $position_month, $position_year, $last_day, $last_month, $last_year);
 					if($t < 5) $workExpUpTo5++;
 					elseif ($t >= 5 && $t < 10) $workExp5UpTo10++;
 					elseif ($t >= 10) $workExpAbove10++;
 				}
 			}
 		}

 		$total_num_days_off += $num_days_off;
  		?> 
 
 <tr>
  <td width="7%" style='width:7.14%;border:solid windowtext 1.0pt;border-top:
  none;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'><?=$reason_name?></span></p>
  </td>
  <td width="18%" style='width:18.26%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><span style='font-size:10.0pt'><?=$position_name?></span></p>
  </td>
  <td width="8%" style='width:8.1%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'><?=$num_employees?></span></p>
  </td>
  <td width="5%" style='width:5.5%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'><?=number_format($percent_employees, 2, ',', ' ')?></span></p>
  </td>
  <td width="5%" style='width:5.96%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'><?=$num_days_off?></span></p>
  </td>
  <td width="3%" style='width:3.52%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'><?=$num_men?></span></p>
  </td>
  <td width="3%" style='width:3.84%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'><?=$num_women?></span></p>
  </td>
  <td width="5%" style='width:5.44%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'><?=((!empty($ageUpTo25))?$ageUpTo25:'-')?></span></p>
  </td>
  <td width="5%" style='width:5.44%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'><?=((!empty($age25UpTo35))?$age25UpTo35:'-')?></span></p>
  </td>
  <td width="5%" style='width:5.44%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'><?=((!empty($age35UpTo45))?$age35UpTo45:'-')?></span></p>
  </td>
  <td width="5%" style='width:5.44%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'><?=((!empty($age45UpTo55))?$age45UpTo55:'-')?></span></p>
  </td>
  <td width="5%" style='width:5.44%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'><?=((!empty($ageAbove55))?$ageAbove55:'-')?></span></p>
  </td>
  <td width="6%" style='width:6.82%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'><?=((!empty($workExpUpTo5))?$workExpUpTo5:'-')?></span></p>
  </td>
  <td width="6%" style='width:6.82%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'><?=((!empty($workExp5UpTo10))?$workExp5UpTo10:'-')?></span></p>
  </td>
  <td width="6%" style='width:6.82%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'><?=((!empty($workExpAbove10))?$workExpAbove10:'-')?></span></p>
  </td>
 </tr>

 <?php 
 	}

 	$total_num_employees = count($zvn_employees);

 	$query = "	SELECT COUNT(*) AS cnt
				FROM workers
				WHERE firm_id = $firm_id
   				AND is_active = '1'
   				AND ( date_retired = '' OR julianday(date_retired) >= julianday('$date_from') )
				AND (sex = 'М' OR sex = '')";
 	if($total_num_employees) $query .= " AND worker_id IN (".implode(', ', $zvn_employees).")";
 	$men = $dbInst->query($query);
 	$total_num_men = (!empty($men[0]['cnt'])) ? $men[0]['cnt'] : 0;

 	$query = "	SELECT COUNT(*) AS cnt
				FROM workers
				WHERE firm_id = $firm_id
   				AND is_active = '1'
   				AND ( date_retired = '' OR julianday(date_retired) >= julianday('$date_from') )
				AND sex = 'Ж'";
 	if($total_num_employees) $query .= " AND worker_id IN (".implode(', ', $zvn_employees).")";
 	$women = $dbInst->query($query);
 	$total_num_women = (!empty($women[0]['cnt'])) ? $women[0]['cnt'] : 0;

 	$total_ageUpTo25 = 0;
 	$total_age25UpTo35 = 0;
 	$total_age35UpTo45 = 0;
 	$total_age45UpTo55 = 0;
 	$total_ageAbove55 = 0;

 	$query = "	SELECT egn
				FROM workers
				WHERE firm_id = $firm_id
   				AND is_active = '1'
   				AND ( date_retired = '' OR julianday(date_retired) >= julianday('$date_from') )";
 	if($total_num_employees) $query .= " AND worker_id IN (".implode(', ', $zvn_employees).")";
 	$ages = $dbInst->query($query);
 	if(!empty($ages)) {
 		foreach ($ages as $age) {
 			$birth_year = intval(substr($age['egn'], 0, 2)) + 1900;
 			$birth_month = intval(substr($age['egn'], 2, 2));
 			$birth_day = intval(substr($age['egn'], 4, 2));
 			$t = calculate_age($birth_day, $birth_month, $birth_year, $last_day, $last_month, $last_year);
 			if($t < 25) $total_ageUpTo25++;
 			elseif ($t >= 25 && $t < 35) $total_age25UpTo35++;
 			elseif ($t >= 35 && $t < 45) $total_age35UpTo45++;
 			elseif ($t >= 45 && $t < 55) $total_age45UpTo55++;
 			elseif ($t >= 55) $total_ageAbove55++;
 		}
 	}

 	$total_workExpUpTo5 = 0;
 	$total_workExp5UpTo10 = 0;
 	$total_workExpAbove10 = 0;

 	$query = "	SELECT date_curr_position_start
				FROM workers
				WHERE firm_id = $firm_id
   				AND is_active = '1'";
 	if($total_num_employees) $query .= " AND worker_id IN (".implode(', ', $zvn_employees).")";
 	$workExp = $dbInst->query($query);
 	if(!empty($workExp)) {
 		foreach ($workExp as $work) {
 			$date_curr_position_start = $work['date_curr_position_start'];
 			if(empty($date_curr_position_start)) $workExpUpTo5++;
 			else {
 				$dt = substr($date_curr_position_start, 0, 10);
 				list($position_year, $position_month, $position_day) = explode('-', $dt);
 				$t = calculate_age($position_day, $position_month, $position_year, $last_day, $last_month, $last_year);
 				if($t < 5) $total_workExpUpTo5++;
 				elseif ($t >= 5 && $t < 10) $total_workExp5UpTo10++;
 				elseif ($t >= 10) $total_workExpAbove10++;
 			}
 		}
 	}

 ?>

 <tr>
  <td width="7%" style='width:7.14%;border:solid windowtext 1.0pt;border-top:
  none;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>ОБЩО</span></b></p>
  </td>
  <td width="18%" style='width:18.26%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><b><span style='font-size:10.0pt'>&nbsp;</span></b></p>
  </td>
  <td width="8%" style='width:8.1%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'><?=$total_num_employees?></span></b></p>
  </td>
  <td width="5%" style='width:5.5%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'><?=number_format((($avgWorkers) ? ($total_num_employees * 100) / $avgWorkers : 0), 2, ',', ' ')?></span></b></p>
  </td>
  <td width="5%" style='width:5.96%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'><?=$total_num_days_off?></span></b></p>
  </td>
  <td width="3%" style='width:3.52%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'><?=$total_num_men?></span></b></p>
  </td>
  <td width="3%" style='width:3.84%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'><?=$total_num_women?></span></b></p>
  </td>
  <td width="5%" style='width:5.44%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'><?=((!empty($total_ageUpTo25))?$total_ageUpTo25:'-')?></span></b></p>
  </td>
  <td width="5%" style='width:5.44%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'><?=((!empty($total_age25UpTo35))?$total_age25UpTo35:'-')?></span></b></p>
  </td>
  <td width="5%" style='width:5.44%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'><?=((!empty($total_age35UpTo45))?$total_age35UpTo45:'')?></span></b></p>
  </td>
  <td width="5%" style='width:5.44%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'><?=((!empty($total_age45UpTo55))?$total_age45UpTo55:'-')?></span></b></p>
  </td>
  <td width="5%" style='width:5.44%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'><?=((!empty($total_ageAbove55))?$total_ageAbove55:'')?></span></b></p>
  </td>
  <td width="6%" style='width:6.82%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'><?=((!empty($total_workExpUpTo5))?$total_workExpUpTo5:'-')?></span></b></p>
  </td>
  <td width="6%" style='width:6.82%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'><?=((!empty($total_workExp5UpTo10))?$total_workExp5UpTo10:'-')?></span></b></p>
  </td>
  <td width="6%" style='width:6.82%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'><?=((!empty($total_workExpAbove10))?$total_workExpAbove10:'-')?></span></b></p>
  </td>
 </tr>

<?php } ?>
 
</table>

<p class=MsoNormal>&nbsp;</p>

<p class=MsoNormal>III. Данни за работещите в предприятието</p>

<table class=MsoTableGrid border=1 cellspacing=0 cellpadding=0 width="100%"
 style='width:100.0%;border-collapse:collapse;border:none'>
 <tr>
  <td width="17%" rowspan=2 style='width:17.74%;border:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>Средно-списъчен състав на работещите</span></b></p>
  </td>
  <td width="20%" colspan=2 style='width:20.56%;border:solid windowtext 1.0pt;
  border-left:none;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>Пол</span></b></p>
  </td>
  <td width="20%" colspan=2 style='width:20.56%;border:solid windowtext 1.0pt;
  border-left:none;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>Целогодишно работещи</span></b></p>
  </td>
  <td width="20%" colspan=2 style='width:20.56%;border:solid windowtext 1.0pt;
  border-left:none;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>Постъпили</span></b></p>
  </td>
  <td width="20%" colspan=2 style='width:20.56%;border:solid windowtext 1.0pt;
  border-left:none;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>Напуснали</span></b></p>
  </td>
 </tr>
 <tr>
  <td width="10%" style='width:10.28%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>М</span></b></p>
  </td>
  <td width="10%" style='width:10.28%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>Ж</span></b></p>
  </td>
  <td width="10%" style='width:10.28%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>М</span></b></p>
  </td>
  <td width="10%" style='width:10.28%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>Ж</span></b></p>
  </td>
  <td width="10%" style='width:10.28%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>М</span></b></p>
  </td>
  <td width="10%" style='width:10.28%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>Ж</span></b></p>
  </td>
  <td width="10%" style='width:10.28%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>М</span></b></p>
  </td>
  <td width="10%" style='width:10.28%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>Ж</span></b></p>
  </td>
 </tr>
 <tr>
  <td width="17%" style='width:17.74%;border:solid windowtext 1.0pt;border-top:
  none;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'><?=$avgWorkers?></span></p>
  </td>
  <td width="10%" style='width:10.28%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'><?=((isset($r))?($r['anual_men']+(($r['joined_men']+$r['retired_men'])/2)):'')?></span></p>
  </td>
  <td width="10%" style='width:10.28%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'><?=((isset($r))?($r['anual_women']+(($r['joined_women']+$r['retired_women'])/2)):'')?></span></p>
  </td>
  <td width="10%" style='width:10.28%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'><?=((isset($r))?$r['anual_men']:'')?></span></p>
  </td>
  <td width="10%" style='width:10.28%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'><?=((isset($r))?$r['anual_women']:'')?></span></p>
  </td>
  <td width="10%" style='width:10.28%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'><?=((isset($r))?$r['joined_men']:'')?></span></p>
  </td>
  <td width="10%" style='width:10.28%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'><?=((isset($r))?$r['joined_women']:'')?></span></p>
  </td>
  <td width="10%" style='width:10.28%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'><?=((isset($r))?$r['retired_men']:'')?></span></p>
  </td>
  <td width="10%" style='width:10.28%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'><?=((isset($r))?$r['retired_women']:'')?></span></p>
  </td>
 </tr>
</table>

<p class=MsoNormal><span style='font-size:14.0pt'>&nbsp;</span></p>

<p class=MsoNormal><span style='font-size:14.0pt'>&nbsp;</span></p>

<p class=MsoNormal><span style='font-size:14.0pt'><?=date("d.m.Y")?> г.                                                                                                                                          Ръководител
СТМ:</span></p>

<p class=MsoNormal align=right style='text-align:right'><span style='font-size:
14.0pt'>(<?=HTMLFormat($s['chief'])?>)</span></p>

</div>

</body>

</html>
