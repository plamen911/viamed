<?php

function my_session_start() {
	ini_set('session.gc_maxlifetime', 8*60*60);
	ini_set('session.gc_probability',1);
	ini_set('session.gc_divisor',1);
	session_set_cookie_params(8*60*60);
	
	$session_name = SESS_NAME;
	@session_name($session_name);
	
	if (!isset($_COOKIE[$session_name])) {
		$r = session_start();
		if ($r !== true) {
        	setcookie($session_name, '', 1);
		}	
	} else {
	    session_start();
	}
	
	/*@session_name(SESS_NAME);
	//Set the current session id
	if(isset($_GET[SESS_NAME]) && !empty($_GET[SESS_NAME])) {
		session_id(strip_tags($_GET[SESS_NAME]));
	}
	//Initialize session data
	@session_start();*/
}

function my_session_destroy() {
	// Unset all of the session variables.
	$_SESSION = array();

	// If it's desired to kill the session, also delete the session cookie.
	// Note: This will destroy the session, and not just the session data!
	if (isset($_COOKIE[session_name()])) {
		setcookie(session_name(), '', time()-42000, '/');
	}

	// Finally, destroy the session.
	session_destroy();
}

function EMailIsCorrect($email)
{
	$i=0;

	$email = mb_strtolower($email);
	$allowedchars = " abcdefghijklmnopqrstuvwxyz_-0123456789";

	$atpos = 0;
	for(;$i<strlen($email);$i++)
	if($email[$i]=="@")
	{
		if($i==0) return false;
		$atpos = $i;
		$i++;
		break;
	}
	else if($email[$i]!="." && strpos($allowedchars,$email[$i])==0) return false; // not an allowed char in e-mail

	$dotpos = 0;
	for(;$i<strlen($email);$i++)
	{
		if($email[$i]==".")
		{
			if($atpos+1==$i) return false; // "@." combination
			if($dotpos+1==$i) return false; // ".." combination
			if($i+1==strlen($email)) return false; // if e-mail end with "."
			$dotpos = $i;
		}
		else if(strpos($allowedchars,$email[$i])==0) return false; // not an allowed char in e-mail
	}

	if($atpos==0) return false;
	if($dotpos==0) return false;

	return true;
}

function cleanQueryString($appendParams="") {
	$queryString = '';
	if(isset($_SERVER["QUERY_STRING"]) && $_SERVER["QUERY_STRING"]!='') {
		$params = $_SERVER["QUERY_STRING"].'&'.$appendParams;
		if(strpos($params,"&")!==false) {
			$pairs = explode('&',$params);
			$q = null;
			foreach ($pairs as $pair) {
				if(strpos($pair,'=')===false) continue;
				list($k,$v) = explode('=',$pair);
				$q[$k] = $v;
			}
			$pairs = null;
			if($q != null && is_array($q)) {
				foreach ($q as $k=>$v) {
					$pairs[] = $k.'='.$v;
				}
				$queryString = '?'.implode('&',$pairs);
			}
			return $queryString;
		}
	}
	return '?'.$appendParams;
}

function HTMLFormat($str) {
	$str = str_replace("\"", "&quot;", $str);
	$str = str_replace("''", "'", $str);
	return stripslashes($str);
}

/*******************************************************************************
* Software: ParseBGDate                                                        *
* Version:  1.0                                                                *
* Date:     2008-03-03                                                         *
* Author:   Plamen MARKOV                                                      *
* License:  Freeware                                                           *
*                                                                              *
* You may use, modify and redistribute this software as you wish.              *
*******************************************************************************/
// Allowed date formats
// ------------
//$date = '2,3,2008';
//$date = '2.3.2008';
//$date = ' 2 -03   -08 г.';
//$date = '2/3/08';
//
// SAMPLE USAGE
// ------------
//
//$d = new ParseBGDate();
//if($d->Parse($date)) {
//	echo $d->year.'-'.$d->month.'-'.$d->day;
//} else {
//	echo 'INVALID DATE!';
//}

class ParseBGDate {
	var $day;
	var $month;
	var $year;
	function ParseBGDate() {
		$this->day = null;
		$this->month = null;
		$this->year = null;
	}
	function Parse($bgdate) {
		if(preg_match('/^(\d{1,2})\s*[\/|\,|\.|\-]\s*(\d{1,2})\s*[\/|\,|\.|\-]\s*(\d{2,4}).*?$/', trim($bgdate), $matches)) {
			$d = $matches[1];
			$m = $matches[2];
			$y = $matches[3];
			//if(intval($y) < 100) $y += (intval($y) <= (date('Y') - 2000)) ? 2000 : 1900;
			if(intval($y) < 100) $y += (intval($y) <= 50) ? 2000 : 1900;
			$this->day = sprintf('%02d', $d);
			$this->month = sprintf('%02d', $m);
			$this->year = $y;
			return true;
		} else {
			return false;
		}
	}
	function getDay() {
		return $this->day;
	}
	function getMonth() {
		return $this->month;
	}
	function getYear() {
		return $this->year;
	}
}

# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #
#                                                                             #
# B00zy's timespan script v1.2                                               #
#                                                                             #
# timespan -- get the exact time span between any two moments in time.        #
#                                                                             #
# Description:                                                                #
#                                                                             #
#        class timespan, function calc ( int timestamp1, int timestamp2)      #
#                                                                             #
#        The purpose of this script is to be able to return the time span     #
#        between any two specific moments in time AFTER the Unix Epoch        #
#        (January 1 1970) in a human-readable format. You could, for example, #
#        determine your age, how long you have been married, or the last time #
#        you... you know. ;)                                                  #
#                                                                             #
#        The class, "timespan", will produce variables within the class       #
#        respectively titled years, months, weeks, days, hours, minutes,      #
#        seconds.                                                             #
#                                                                             #
# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #
#                                                                             #
# Example 1. B00zy's age.                                                     #
#                                                                             #
#        $t = new timespan( time(), mktime(0,13,0,8,28,1982));                #
#        print "B00zy is $t->years years, $t->months months, ".               #
#                "$t->days days, $t->hours hours, $t->minutes minutes, ".     #
#                "and $t->seconds seconds old.\n";                            #
#                                                                             #
# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #

define('day', 60*60*24 );
define('hour', 60*60 );
define('minute', 60 );

class timespan
{
	var $years;
	var $months;
	var $weeks;
	var $days;
	var $hours;
	var $minutes;
	var $seconds;

	function leap($time)
	{
		if (date('L',$time) and (date('z',$time) > 58))
		return (double)(60*60*24*366);
		else
		{
			$de = getdate($time);
			$mkt = mktime(0,0,0,$de['mon'],$de['mday'],($de['year'] - 1));
			if ((date('z',$time) <= 58) and date('L',$mkt))
			return (double)(60*60*24*366);
			else
			return (double)(60*60*24*365);
		}
	}
	function readable()
	{
		$values = array('years','months','weeks','days','hours','minutes','seconds');
		foreach ($values as $k => $v)
		if ($this->{$v}) $fmt .= ( $fmt? ', ': '') . $this->{$v} . " $v";
		return $fmt . ( $fmt? '.': '') ;
	}

	function timespan($after,$before)
	{
		# Set variables to zero, instead of null.

		$this->years = 0;
		$this->months = 0;
		$this->weeks = 0;
		$this->days = 0;
		$this->hours = 0;
		$this->minutes = 0;
		$this->seconds = 0;

		$duration = $after - $before;

		# 1. Number of years
		$dec = $after;

		//$year = $year;
		$year = $this->leap($dec);

		while (floor($duration / $year) >= 1)
		{
			//print date("F j, Y\n",$dec);

			$this->years += 1;
			$duration -= (int)$year;
			$dec -= (int)$year;

			$year = $this->leap($dec);
		}

		# 2. Number of months
		$dec = $after;
		$m = date('n',$after);
		$d = date('j',$after);

		while (($duration - day) >= 0)
		{
			$duration -= day;
			$dec -= day;
			$this->days += 1;

			if ( (date('n',$dec) != $m) and (date('j',$dec) <= $d) )
			{
				$m = date('n',$dec);
				$d = date('j',$dec);

				$this->months += 1;
				$this->days = 0;
			}
		}
		# 3. Number of weeks.
		$this->weeks = floor($this->days / 7);
		$this->days %= 7;

		# 4. Number of hours, minutes, and seconds.
		$this->hours = floor($duration / (60*60));
		$duration %= (60*60);

		$this->minutes = floor($duration / 60);
		$duration %= 60;

		$this->seconds = $duration;
	}
}

// Xajax shared functions
function guessLocation($location_name) {
	$objResponse = new xajaxResponse();
	if(trim($location_name) == '')
	return $objResponse;

	global $dbInst;
	$location_id = $dbInst->guessLocation($location_name);
	if($location_id) {
		$objResponse->assign("location_id","value",$location_id);
	}
	return $objResponse;
}

function guessCommunity($community_name) {
	$objResponse = new xajaxResponse();
	if(trim($community_name) == '')
	return $objResponse;

	global $dbInst;
	$community_id = $dbInst->guessCommunity($community_name);
	if($community_id) {
		$objResponse->assign("community_id","value",$community_id);
	}
	return $objResponse;
}

function guessProvince($province_name) {
	$objResponse = new xajaxResponse();
	if(trim($province_name) == '')
	return $objResponse;

	global $dbInst;
	$province_id = $dbInst->guessProvince($province_name);
	if($province_id) {
		$objResponse->assign("province_id","value",$province_id);
	}
	return $objResponse;
}

function formatBGDate($key, $val) {
	$objResponse = new xajaxResponse();
	if(trim($val) != '') {
		$d = new ParseBGDate();
		if($d->Parse($val)) {
			$objResponse->assign($key, "value", $d->day.'.'.$d->month.'.'.$d->year);
		}
		else
		$objResponse->assign($key, "value", "");
	}
	return $objResponse;
}

function calcContractEnd($contract_begin) {
	$objResponse = new xajaxResponse();

	$contract_begin = trim($contract_begin);
	$contract_end = "";
	if($contract_begin != '') {
		$d = new ParseBGDate();
		if($d->Parse($contract_begin)) {
			$contract_begin = $d->day.'.'.$d->month.'.'.$d->year;
			$contract_end = date('d.m.Y', mktime(0, 0, 0, $d->month, $d->day, $d->year+1));
		}
		$objResponse->assign("contract_begin", "value", $contract_begin);
		$objResponse->assign("contract_end", "value", $contract_end);
	}

	return $objResponse;
}

function calcDeviation($min, $max, $level, $imgpath='img/') {
	if(!is_numeric($level)) return '&nbsp;';
	if($level < $min) return '<div align="center" class="primary"><img src="'.$imgpath.'minus.gif" width="12" height="12" border="0" alt="minus" /></div>';
	elseif ($level > $max) return '<div align="center" class="primary"><img src="'.$imgpath.'plus.gif" width="12" height="12" border="0" alt="plus" /></div>';
	else return '&nbsp;';
}

// Auto-populate work env. factors
function populateValues($factor_id, $suff) {
	$objResponse = new xajaxResponse();

	global $dbInst;
	$row = $dbInst->getFactorInfo($factor_id);
	if(!$row) {
		$row['pdk_min'] = $row['pdk_max'] = $row['factor_dimension'] = '';
	}
	$objResponse->assign("pdk_min_$suff","value",$row['pdk_min']);
	$objResponse->assign("pdk_max_$suff","value",$row['pdk_max']);
	$objResponse->assign("factor_dimension_$suff","value",$row['factor_dimension']);

	return $objResponse;
}

function formatProtDate($prot_date, $suff) {
	$objResponse = new xajaxResponse();

	$prot_date = trim($prot_date);
	if($prot_date != '') {
		$d = new ParseBGDate();
		if($d->Parse($prot_date))
		$objResponse->assign("prot_date_$suff", "value", $d->day.'.'.$d->month.'.'.$d->year);
		else
		$objResponse->assign("prot_date_$suff", "value", "");
	}

	return $objResponse;
}

// Usage: worker_age('19.10.1972', '27.03.2008');
function worker_age($date_start, $date_end) {
	$d1 = new ParseBGDate();
	$d2 = new ParseBGDate();
	if($d1->Parse($date_start) && $d2->Parse($date_end)) {
		$t = new timespan(mktime(0, 0, 0, $d2->month, $d2->day, $d2->year), mktime(0, 0, 0, $d1->month, $d1->day, $d1->year));
		return $t->years;
	}
	return "";
}

function calculate_age($birth_day, $birth_month, $birth_year, $current_day, $current_month, $current_year) {

	$year_dif = $current_year - $birth_year;

	if(($birth_month > $current_month) || ($birth_month == $current_month && $current_day < $birth_day))
	$age = $year_dif - 1;
	else
	$age = $year_dif;

	return $age;

}

// PRINT FUNCTIONS

function w_heading($s=array()) {
	$fsize = 20;
	if(isset($s['stm_name'])) {
		$strLength = strlen($s['stm_name']);
		for ($i = 19, $j = 74; $i > 12; $i--, $j +=3) {
			if($strLength > $j && $strLength <= ($j + 3)) {
				$fsize = $i;
				break;
			}
		}
	}
?>
<p class=MsoNormal align=center style='text-align:center'><b style='mso-bidi-font-weight:
normal'><span style='font-size:<?=sprintf("%01.1f", $fsize);?>pt;color:navy'><?=((isset($s['stm_name']))?HTMLFormat($s['stm_name']):'СЛУЖБА ПО ТРУДОВА МЕДИЦИНА')?><o:p></o:p></span></b></p>

<p class=MsoNormal align=center style='text-align:center;text-indent:18.0pt'><span
style='font-size:11.0pt'><?=((isset($s['address']))?HTMLFormat($s['address']).', ':'')?><?=(($s['phone1'])?'тел. '.$s['phone1']:'')?><?=(($s['phone2'])?', '.$s['phone2']:'')?><?=(($s['fax'])?', факс: '.$s['fax']:'')?><?=(($s['email'])?', e-mail: '.$s['email']:'')?><o:p></o:p></span></p>

<div style='mso-element:para-border-div;border:none;border-bottom:double windowtext 1.5pt;
padding:0cm 0cm 1.0pt 0cm'>

<?php if($s['license_num'] != '') { ?>
<p class=MsoNormal align=center style='text-align:center;text-indent:18.0pt;
border:none;mso-border-bottom-alt:double windowtext 1.5pt;padding:0cm;
mso-padding-alt:0cm 0cm 1.0pt 0cm'><span style='font-size:11.0pt'>Удостоверение
№ <?=HTMLFormat($s['license_num'])?> от Министерство на Здравеопазването<o:p></o:p></span></p>
<?php } ?>

<?php
}

function w_footer($s=array(), $date='') {
	global $dbInst;
?>
<p class=MsoNormal><span style='font-size:14.0pt'><o:p>&nbsp;</o:p></span></p>

<p class=MsoNormal><span style='font-size:14.0pt'><o:p>&nbsp;</o:p></span></p>

<p class=MsoNormal><span style='font-size:14.0pt'><o:p>&nbsp;</o:p></span></p>

<p class=MsoNormal><span style='font-size:14.0pt'><o:p>&nbsp;</o:p></span></p>

<p class=MsoNormal><span style='font-size:14.0pt'><?=(($date!='')?$date:date("d.m.Y"))?> г.<span
style='mso-tab-count:4'>                                    </span><span
style='mso-tab-count:1'>                   </span>Лекар СТМ:<o:p></o:p></span></p>

<p class=MsoNormal align=right style='text-align:right'><span style='font-size:
14.0pt'>(............................................)<o:p></o:p></span></p>

<?php
}

/* new functions */

function setFlash($msg = '') {
	$_SESSION['sess_flashMsg'] = $msg;
}

function getFlash() {
	$retStr = '';
	if(isset($_SESSION['sess_flashMsg'])) {
		$retStr .= '<div align="center"><span class="error">'.HTMLFormat($_SESSION['sess_flashMsg']).'</span></div>';
		unset($_SESSION['sess_flashMsg']);
	}
	return $retStr;
}

function Resize($Dir, $Image, $NewDir, $NewImage, $MaxWidth, $MaxHeight, $Quality) {
	list($ImageWidth, $ImageHeight, $TypeCode) = getimagesize($Dir.$Image);
	$ImageType = ($TypeCode==1 ? "gif" : ($TypeCode==2 ? "jpeg" : ($TypeCode==3 ? "png" : FALSE)));
	$CreateFunction = "imagecreatefrom".$ImageType;
	$OutputFunction = "image".$ImageType;
	if ($ImageType) {
		$Ratio = ($ImageHeight/$ImageWidth);
		$ImageSource = $CreateFunction($Dir.$Image);
		if ($ImageWidth > $MaxWidth || $ImageHeight > $MaxHeight) {
			if ($ImageWidth > $MaxWidth) {
				$ResizedWidth = $MaxWidth;
				$ResizedHeight = $ResizedWidth*$Ratio;
			} else {
				$ResizedWidth = $ImageWidth;
				$ResizedHeight = $ImageHeight;
			}
			if ($ResizedHeight > $MaxHeight) {
				$ResizedHeight = $MaxHeight;
				$ResizedWidth = $ResizedHeight/$Ratio;
			}
			$ResizedImage = imagecreatetruecolor($ResizedWidth, $ResizedHeight);
			imagecopyresampled($ResizedImage, $ImageSource, 0, 0, 0, 0, $ResizedWidth,$ResizedHeight, $ImageWidth, $ImageHeight);
		} else {
			$ResizedWidth = $ImageWidth;
			$ResizedHeight = $ImageHeight;
			$ResizedImage = $ImageSource;
		}
		switch ($ImageType) {
			case 'jpeg':
				$OutputFunction($ResizedImage, $NewDir.$NewImage, $Quality);
				break;
			default:
				$OutputFunction($ResizedImage, $NewDir.$NewImage);
				break;
		}
		return true;
	} else {
		return false;
	}
}


// UTF8 to Cyrillic Win-1251 encoding convertor
function utf2win1251 ($s)
{
	$out = "";

	for ($i=0; $i<strlen($s); $i++)
	{
		$c1 = substr ($s, $i, 1);
		$byte1 = ord ($c1);
		if ($byte1>>5 == 6) // 110x xxxx, 110 prefix for 2 bytes unicode
		{
			$i++;
			$c2 = substr ($s, $i, 1);
			$byte2 = ord ($c2);
			$byte1 &= 31; // remove the 3 bit two bytes prefix
			$byte2 &= 63; // remove the 2 bit trailing byte prefix
			$byte2 |= (($byte1 & 3) << 6); // last 2 bits of c1 become first 2 of c2
			$byte1 >>= 2; // c1 shifts 2 to the right

			$word = ($byte1<<8) + $byte2;
			if ($word==1025) $out .= chr(168);
			elseif ($word==1105) $out .= chr(184);
			elseif ($word>=0x0410 && $word<=0x044F) $out .= chr($word-848);
			else
			{
				$a = dechex($byte1);
				$a = str_pad($a, 2, "0", STR_PAD_LEFT);
				$b = dechex($byte2);
				$b = str_pad($b, 2, "0", STR_PAD_LEFT);
				$out .= "&#x".$a.$b.";";
			}
		}
		else
		{
			$out .= $c1;
		}
	}

	return $out;
}

// http://www.ustrem.org/en/articles/how-to-convert-between-utf8-cp1251-without-iconv-en/
function cp1251_to_utf8($s){
	$t = '';
	$c209 = chr(209); $c208 = chr(208); $c129 = chr(129);
	for($i = 0; $i < strlen($s); $i++)    {
		$c = ord($s[$i]);
		if ($c >= 192 and $c <= 239) $t .= $c208.chr($c-48);
		elseif ($c > 239) $t .= $c209.chr($c-112);
		elseif ($c == 184) $t .= $c209.$c209;
		elseif ($c == 168) $t .= $c208.$c129;
		else $t .= $s[$i];
	}
	return $t;
}

function make_uploaddir($uploaddir) {
	if (!is_dir($uploaddir)) {
		if (!mkdir($uploaddir))
		die ("$uploaddir directory doesn't exist and creation failed");
		if (!chmod($uploaddir,0755))
		die ("change permission to 755 failed.");
	}
}

function getServicesPulldownOptions() {
	$str  = '';
	$aServices = getServices();
	$aServices[0]['Услуги']['other'] = 'Други';

	foreach ($aServices[0] as $catg => $items) {
		if(!empty($catg)) {	$str .= '<optgroup label="'.$catg.'">';	}
		foreach ($items as $key => $value) {
			$str .= '<option value="'.$key.'">'.$value.' </option>';
		}
		if(!empty($catg)) {	$str .= '</optgroup>';	}
	}

	return $str;
}

// Accounting functions
function getDuePayments($due_from=0, $due_to=0, $totalAmountDue) {
	global $dbInst;

	$sql = "SELECT c.*, f.`name` AS `firm_name`, f.`address` AS `firm_address`, l.location_name, cc.community_name, p.province_name
			FROM `acc_contracts` c
			LEFT JOIN firms f ON (f.`firm_id` = c.`firm_id`)
			LEFT JOIN locations l ON (l.location_id = f.location_id)
	        LEFT JOIN communities cc ON (cc.community_id = f.community_id)
	        LEFT JOIN provinces p ON (p.province_id = f.province_id)
			WHERE f.is_active = '1'
			AND c.`contract_halt` = '0'
			AND `amount_due_total` > `amount_paid_total`";
	$txtCondition = '';
	if(!$due_to) {
		$due_from_timestamp = date('Y-m-d H:i:s', strtotime($due_from));
		$txtCondition .= " AND ( (c.due_date != '' AND julianday(c.due_date) < julianday('$due_from_timestamp'))";
		$txtCondition .= " OR (c.due_date2 != '' AND julianday(c.due_date2) < julianday('$due_from_timestamp'))";
		$txtCondition .= " OR (c.due_date3 != '' AND julianday(c.due_date3) < julianday('$due_from_timestamp'))";
		$txtCondition .= " OR (c.due_date4 != '' AND julianday(c.due_date4) < julianday('$due_from_timestamp'))";
		$txtCondition .= " OR (c.due_date5 != '' AND julianday(c.due_date5) < julianday('$due_from_timestamp'))";
		$txtCondition .= " OR (c.due_date6 != '' AND julianday(c.due_date6) < julianday('$due_from_timestamp'))";
		$txtCondition .= " OR (c.due_date7 != '' AND julianday(c.due_date7) < julianday('$due_from_timestamp'))";
		$txtCondition .= " OR (c.due_date8 != '' AND julianday(c.due_date8) < julianday('$due_from_timestamp')) )";
	} else {
		$due_from_timestamp = date('Y-m-d H:i:s', strtotime($due_from));
		$due_to_timestamp = date('Y-m-d H:i:s', strtotime($due_to));
		$txtCondition .= " AND ( (c.due_date != '' AND (julianday(c.due_date) >= julianday('$due_from_timestamp') AND julianday(c.due_date) < julianday('$due_to_timestamp')))";
		$txtCondition .= " OR (c.due_date2 != '' AND (julianday(c.due_date2) >= julianday('$due_from_timestamp') AND julianday(c.due_date2) < julianday('$due_to_timestamp')))";
		$txtCondition .= " OR (c.due_date3 != '' AND (julianday(c.due_date3) >= julianday('$due_from_timestamp') AND julianday(c.due_date3) < julianday('$due_to_timestamp')))";
		$txtCondition .= " OR (c.due_date4 != '' AND (julianday(c.due_date4) >= julianday('$due_from_timestamp') AND julianday(c.due_date4) < julianday('$due_to_timestamp')))";
		$txtCondition .= " OR (c.due_date5 != '' AND (julianday(c.due_date5) >= julianday('$due_from_timestamp') AND julianday(c.due_date5) < julianday('$due_to_timestamp')))";
		$txtCondition .= " OR (c.due_date6 != '' AND (julianday(c.due_date6) >= julianday('$due_from_timestamp') AND julianday(c.due_date6) < julianday('$due_to_timestamp')))";
		$txtCondition .= " OR (c.due_date7 != '' AND (julianday(c.due_date7) >= julianday('$due_from_timestamp') AND julianday(c.due_date7) < julianday('$due_to_timestamp')))";
		$txtCondition .= " OR (c.due_date8 != '' AND (julianday(c.due_date8) >= julianday('$due_from_timestamp') AND julianday(c.due_date8) < julianday('$due_to_timestamp'))) )";
	}
	$txtCondition .= " GROUP BY c.`contract_id` ORDER BY c.due_date DESC, c.due_date2 DESC, c.due_date3 DESC, c.due_date4 DESC, `firm_name`, c.amount_due_total, c.`contract_id`";
	$sql .= $txtCondition;

	$data = array();

	$IDs = array();

	$rows = $dbInst->query($sql);
	if(is_array($rows) && count($rows)>0) {
		foreach ($rows as $row) {
			$amount_due_total = floatval($row['amount_due_total']);
			$amount_paid_total = floatval($row['amount_paid_total']);

			$amount_due = floatval($row['amount_due']);
			$amount_paid = floatval($row['amount_paid']);
			$due_date = (!empty($row['due_date'])) ? strtotime($row['due_date']) : 0;
			$amount_due2 = floatval($row['amount_due2']);
			$amount_paid2 = floatval($row['amount_paid2']);
			$due_date2 = (!empty($row['due_date2'])) ? strtotime($row['due_date2']) : 0;
			$amount_due3 = floatval($row['amount_due3']);
			$amount_paid3 = floatval($row['amount_paid3']);
			$due_date3 = (!empty($row['due_date3'])) ? strtotime($row['due_date3']) : 0;
			$amount_due4 = floatval($row['amount_due4']);
			$amount_paid4 = floatval($row['amount_paid4']);
			$due_date4 = (!empty($row['due_date4'])) ? strtotime($row['due_date4']) : 0;
			$amount_due5 = floatval($row['amount_due5']);
			$amount_paid5 = floatval($row['amount_paid5']);
			$due_date5 = (!empty($row['due_date5'])) ? strtotime($row['due_date5']) : 0;
			$amount_due6 = floatval($row['amount_due6']);
			$amount_paid6 = floatval($row['amount_paid6']);
			$due_date6 = (!empty($row['due_date6'])) ? strtotime($row['due_date6']) : 0;
			$amount_due7 = floatval($row['amount_due7']);
			$amount_paid7 = floatval($row['amount_paid7']);
			$due_date7 = (!empty($row['due_date7'])) ? strtotime($row['due_date7']) : 0;
			$amount_due8 = floatval($row['amount_due8']);
			$amount_paid8 = floatval($row['amount_paid8']);
			$due_date8 = (!empty($row['due_date8'])) ? strtotime($row['due_date8']) : 0;

			if($amount_due_total <= $amount_paid_total) continue;
			if(in_array($row['firm_id'], $IDs)) continue;

			$IDs[$row['firm_id']] = $row['firm_id'];

			$row['AMOUNTDUE'] = $amount_paid_total;
			$row['REMINDER'] = $amount_due_total - $amount_paid_total;
			$row['TOTAL'] = $amount_due_total;
			$totalAmountDue += $row['REMINDER'];

			$row['DUEON'] = '';
			$row['DUEON_TIMESTAMP'] = 0;
			if(!$due_to) {
				if($amount_due > $amount_paid && $due_date && date('Y-m-d H:i:s', $due_date) < $due_from_timestamp) {
					$row['DUEON'] = date('d.m.Y', $due_date);
					$row['DUEON_TIMESTAMP'] = $due_date;
					$data[] = $row;
				}
				if($amount_due2 > $amount_paid2 && $due_date2 && date('Y-m-d H:i:s', $due_date2) < $due_from_timestamp) {
					$row['DUEON'] = date('d.m.Y', $due_date2);
					$row['DUEON_TIMESTAMP'] = $due_date2;
					$data[] = $row;
				}
				if($amount_due3 > $amount_paid3 && $due_date3 && date('Y-m-d H:i:s', $due_date3) < $due_from_timestamp) {
					$row['DUEON'] = date('d.m.Y', $due_date3);
					$row['DUEON_TIMESTAMP'] = $due_date3;
					$data[] = $row;
				}
				if($amount_due4 > $amount_paid4 && $due_date4 && date('Y-m-d H:i:s', $due_date4) < $due_from_timestamp) {
					$row['DUEON'] = date('d.m.Y', $due_date4);
					$row['DUEON_TIMESTAMP'] = $due_date4;
					$data[] = $row;
				}
				if($amount_due5 > $amount_paid5 && $due_date5 && date('Y-m-d H:i:s', $due_date5) < $due_from_timestamp) {
					$row['DUEON'] = date('d.m.Y', $due_date5);
					$row['DUEON_TIMESTAMP'] = $due_date5;
					$data[] = $row;
				}
				if($amount_due6 > $amount_paid6 && $due_date6 && date('Y-m-d H:i:s', $due_date6) < $due_from_timestamp) {
					$row['DUEON'] = date('d.m.Y', $due_date6);
					$row['DUEON_TIMESTAMP'] = $due_date6;
					$data[] = $row;
				}
				if($amount_due7 > $amount_paid7 && $due_date7 && date('Y-m-d H:i:s', $due_date7) < $due_from_timestamp) {
					$row['DUEON'] = date('d.m.Y', $due_date7);
					$row['DUEON_TIMESTAMP'] = $due_date7;
					$data[] = $row;
				}

			} else {
				if($amount_due > $amount_paid && $due_date && date('Y-m-d H:i:s', $due_date) >= $due_from_timestamp && date('Y-m-d H:i:s', $due_date) < $due_to_timestamp) {
					$row['DUEON'] = date('d.m.Y', $due_date);
					$row['DUEON_TIMESTAMP'] = $due_date;
					$data[] = $row;
				}
				if($amount_due2 > $amount_paid2 && $due_date2 && date('Y-m-d H:i:s', $due_date2) >= $due_from_timestamp && date('Y-m-d H:i:s', $due_date2) < $due_to_timestamp) {
					$row['DUEON'] = date('d.m.Y', $due_date2);
					$row['DUEON_TIMESTAMP'] = $due_date2;
					$data[] = $row;
				}
				if($amount_due3 > $amount_paid3 && $due_date3 && date('Y-m-d H:i:s', $due_date3) >= $due_from_timestamp && date('Y-m-d H:i:s', $due_date3) < $due_to_timestamp) {
					$row['DUEON'] = date('d.m.Y', $due_date3);
					$row['DUEON_TIMESTAMP'] = $due_date3;
					$data[] = $row;
				}
				if($amount_due4 > $amount_paid4 && $due_date4 && date('Y-m-d H:i:s', $due_date4) >= $due_from_timestamp && date('Y-m-d H:i:s', $due_date4) < $due_to_timestamp) {
					$row['DUEON'] = date('d.m.Y', $due_date4);
					$row['DUEON_TIMESTAMP'] = $due_date4;
					$data[] = $row;
				}
				if($amount_due5 > $amount_paid5 && $due_date5 && date('Y-m-d H:i:s', $due_date5) >= $due_from_timestamp && date('Y-m-d H:i:s', $due_date5) < $due_to_timestamp) {
					$row['DUEON'] = date('d.m.Y', $due_date5);
					$row['DUEON_TIMESTAMP'] = $due_date5;
					$data[] = $row;
				}
				if($amount_due6 > $amount_paid6 && $due_date6 && date('Y-m-d H:i:s', $due_date6) >= $due_from_timestamp && date('Y-m-d H:i:s', $due_date6) < $due_to_timestamp) {
					$row['DUEON'] = date('d.m.Y', $due_date6);
					$row['DUEON_TIMESTAMP'] = $due_date6;
					$data[] = $row;
				}
				if($amount_due7 > $amount_paid7 && $due_date7 && date('Y-m-d H:i:s', $due_date7) >= $due_from_timestamp && date('Y-m-d H:i:s', $due_date7) < $due_to_timestamp) {
					$row['DUEON'] = date('d.m.Y', $due_date7);
					$row['DUEON_TIMESTAMP'] = $due_date7;
					$data[] = $row;
				}
				if($amount_due8 > $amount_paid8 && $due_date8 && date('Y-m-d H:i:s', $due_date8) >= $due_from_timestamp && date('Y-m-d H:i:s', $due_date8) < $due_to_timestamp) {
					$row['DUEON'] = date('d.m.Y', $due_date8);
					$row['DUEON_TIMESTAMP'] = $due_date8;
					$data[] = $row;
				}

			}
		}
	}

	# http://www.the-art-of-web.com/php/sortarray/
	# sort alphabetically by due on date
	usort($data, 'compare_dueon');

	return $data;
}

// Accounting functions
function BKP_2009_10_19_getDuePayments($due_from=0, $due_to=0) {
	global $dbInst, $totalAmountDue;

	$totalAmountDue = 0;

	$query = "	SELECT c.*, f.`name` AS `firm_name`, f.`address` AS `firm_address`, l.location_name, cc.community_name, p.province_name
				FROM `acc_contracts` c
				LEFT JOIN firms f ON (f.`firm_id` = c.`firm_id`)
				LEFT JOIN locations l ON (l.location_id = f.location_id)
	            LEFT JOIN communities cc ON (cc.community_id = f.community_id)
	            LEFT JOIN provinces p ON (p.province_id = f.province_id)
				WHERE f.is_active = '1'
				AND c.`contract_halt` = '0'";
	$txtCondition = '';
	if(!$due_to) {
		$due_from_timestamp = date('Y-m-d H:i:s', strtotime($due_from));
		$txtCondition .= " AND ( (c.due_date != '' AND c.amount_due != 0 AND julianday(c.due_date) < julianday('$due_from_timestamp') AND c.amount_due > c.amount_paid)";
		$txtCondition .= " OR (c.due_date2 != '' AND c.amount_due2 != 0 AND julianday(c.due_date2) < julianday('$due_from_timestamp') AND c.amount_due2 > c.amount_paid2)";
		$txtCondition .= " OR (c.due_date3 != '' AND c.amount_due3 != 0 AND julianday(c.due_date3) < julianday('$due_from_timestamp') AND c.amount_due3 > c.amount_paid3)";
		$txtCondition .= " OR (c.due_date4 != '' AND c.amount_due4 != 0 AND julianday(c.due_date4) < julianday('$due_from_timestamp') AND c.amount_due4 > c.amount_paid4) )";
	} else {
		$due_from_timestamp = date('Y-m-d H:i:s', strtotime($due_from));
		$due_to_timestamp = date('Y-m-d H:i:s', strtotime($due_to));
		$txtCondition .= " AND ( (c.due_date != '' AND c.amount_due != 0 AND (julianday(c.due_date) >= julianday('$due_from_timestamp') AND julianday(c.due_date) < julianday('$due_to_timestamp')) AND c.amount_due > c.amount_paid)";
		$txtCondition .= " OR (c.due_date2 != '' AND c.amount_due2 != 0 AND (julianday(c.due_date2) >= julianday('$due_from_timestamp') AND julianday(c.due_date2) < julianday('$due_to_timestamp')) AND c.amount_due2 > c.amount_paid2)";
		$txtCondition .= " OR (c.due_date3 != '' AND c.amount_due3 != 0 AND (julianday(c.due_date3) >= julianday('$due_from_timestamp') AND julianday(c.due_date3) < julianday('$due_to_timestamp')) AND c.amount_due3 > c.amount_paid3)";
		$txtCondition .= " OR (c.due_date4 != '' AND c.amount_due4 != 0 AND (julianday(c.due_date4) >= julianday('$due_from_timestamp') AND julianday(c.due_date4) < julianday('$due_to_timestamp')) AND c.amount_due4 > c.amount_paid4) )";
	}
	$txtCondition .= " GROUP BY c.`contract_id` ORDER BY c.due_date DESC, c.due_date2 DESC, c.due_date3 DESC, c.due_date4 DESC, `firm_name`, c.amount_due_total, c.`contract_id`";
	$query .= $txtCondition;

	$data = array();
	$rows = $dbInst->query($query);
	if(is_array($rows) && count($rows)>0) {
		foreach ($rows as $row) {
			$amount_due_total = floatval($row['amount_due_total']);
			$amount_paid_total = floatval($row['amount_paid_total']);

			$amount_due = floatval($row['amount_due']);
			$amount_paid = floatval($row['amount_paid']);
			$due_date = (!empty($row['due_date'])) ? strtotime($row['due_date']) : 0;
			$amount_due2 = floatval($row['amount_due2']);
			$amount_paid2 = floatval($row['amount_paid2']);
			$due_date2 = (!empty($row['due_date2'])) ? strtotime($row['due_date2']) : 0;
			$amount_due3 = floatval($row['amount_due3']);
			$amount_paid3 = floatval($row['amount_paid3']);
			$due_date3 = (!empty($row['due_date3'])) ? strtotime($row['due_date3']) : 0;
			$amount_due4 = floatval($row['amount_due4']);
			$amount_paid4 = floatval($row['amount_paid4']);
			$due_date4 = (!empty($row['due_date4'])) ? strtotime($row['due_date4']) : 0;

			if($amount_due_total <= $amount_paid_total) continue;

			$row['DUEON'] = '';
			$row['DUEON_TIMESTAMP'] = 0;
			$row['AMOUNTDUE'] = 0;
			$row['AMOUNTDUENUM'] = '';
			if(!$due_to) {
				if($amount_due > $amount_paid && $due_date && date('Y-m-d H:i:s', $due_date) < $due_from_timestamp) {
					$row['DUEON'] = date('d.m.Y', $due_date);
					$row['DUEON_TIMESTAMP'] = $due_date;
					$row['AMOUNTDUE'] = $amount_due - $amount_paid;
					$row['AMOUNTDUENUM'] = 'I.';
					$data[] = $row;
					$totalAmountDue += $row['AMOUNTDUE'];
				}
				if($amount_due2 > $amount_paid2 && $due_date2 && date('Y-m-d H:i:s', $due_date2) < $due_from_timestamp) {
					$row['DUEON'] = date('d.m.Y', $due_date2);
					$row['DUEON_TIMESTAMP'] = $due_date2;
					$row['AMOUNTDUE'] = $amount_due2 - $amount_paid2;
					$row['AMOUNTDUENUM'] = 'II.';
					$data[] = $row;
					$totalAmountDue += $row['AMOUNTDUE'];
				}
				if($amount_due3 > $amount_paid3 && $due_date3 && date('Y-m-d H:i:s', $due_date3) < $due_from_timestamp) {
					$row['DUEON'] = date('d.m.Y', $due_date3);
					$row['DUEON_TIMESTAMP'] = $due_date3;
					$row['AMOUNTDUE'] = $amount_due3 - $amount_paid3;
					$row['AMOUNTDUENUM'] = 'III.';
					$data[] = $row;
					$totalAmountDue += $row['AMOUNTDUE'];
				}
				if($amount_due4 > $amount_paid4 && $due_date4 && date('Y-m-d H:i:s', $due_date4) < $due_from_timestamp) {
					$row['DUEON'] = date('d.m.Y', $due_date4);
					$row['DUEON_TIMESTAMP'] = $due_date4;
					$row['AMOUNTDUE'] = $amount_due4 - $amount_paid4;
					$row['AMOUNTDUENUM'] = 'IV.';
					$data[] = $row;
					$totalAmountDue += $row['AMOUNTDUE'];
				}
			} else {
				if($amount_due > $amount_paid && $due_date && date('Y-m-d H:i:s', $due_date) >= $due_from_timestamp && date('Y-m-d H:i:s', $due_date) < $due_to_timestamp) {
					$row['DUEON'] = date('d.m.Y', $due_date);
					$row['DUEON_TIMESTAMP'] = $due_date;
					$row['AMOUNTDUE'] = $amount_due - $amount_paid;
					$row['AMOUNTDUENUM'] = 'I.';
					$data[] = $row;
					$totalAmountDue += $row['AMOUNTDUE'];
				}
				if($amount_due2 > $amount_paid2 && $due_date2 && date('Y-m-d H:i:s', $due_date2) >= $due_from_timestamp && date('Y-m-d H:i:s', $due_date2) < $due_to_timestamp) {
					$row['DUEON'] = date('d.m.Y', $due_date2);
					$row['DUEON_TIMESTAMP'] = $due_date2;
					$row['AMOUNTDUE'] = $amount_due2 - $amount_paid2;
					$row['AMOUNTDUENUM'] = 'II.';
					$data[] = $row;
					$totalAmountDue += $row['AMOUNTDUE'];
				}
				if($amount_due3 > $amount_paid3 && $due_date3 && date('Y-m-d H:i:s', $due_date3) >= $due_from_timestamp && date('Y-m-d H:i:s', $due_date3) < $due_to_timestamp) {
					$row['DUEON'] = date('d.m.Y', $due_date3);
					$row['DUEON_TIMESTAMP'] = $due_date3;
					$row['AMOUNTDUE'] = $amount_due3 - $amount_paid3;
					$row['AMOUNTDUENUM'] = 'III.';
					$data[] = $row;
					$totalAmountDue += $row['AMOUNTDUE'];
				}
				if($amount_due4 > $amount_paid4 && $due_date4 && date('Y-m-d H:i:s', $due_date4) >= $due_from_timestamp && date('Y-m-d H:i:s', $due_date4) < $due_to_timestamp) {
					$row['DUEON'] = date('d.m.Y', $due_date4);
					$row['DUEON_TIMESTAMP'] = $due_date4;
					$row['AMOUNTDUE'] = $amount_due4 - $amount_paid4;
					$row['AMOUNTDUENUM'] = 'IV.';
					$data[] = $row;
					$totalAmountDue += $row['AMOUNTDUE'];
				}
			}
		}
	}

	# http://www.the-art-of-web.com/php/sortarray/
	# sort alphabetically by due on date
	usort($data, 'compare_dueon');

	return $data;
}

function getPaidAmounts($due_from=0, $due_to=0) {
	global $dbInst, $totalAmountPaid;

	$totalAmountPaid = 0;

	$query = "	SELECT c.*, f.`name` AS `firm_name`, f.`address` AS `firm_address`, l.location_name, cc.community_name, p.province_name
				FROM `acc_contracts` c
				LEFT JOIN firms f ON (f.`firm_id` = c.`firm_id`)
				LEFT JOIN locations l ON (l.location_id = f.location_id)
	            LEFT JOIN communities cc ON (cc.community_id = f.community_id)
	            LEFT JOIN provinces p ON (p.province_id = f.province_id)
				WHERE f.is_active = '1'
				AND c.`contract_halt` = '0'";
	$txtCondition = '';
	if(!$due_to) {
		$due_from_timestamp = date('Y-m-d H:i:s', strtotime($due_from));
		$txtCondition .= " AND ( (c.paid_date != '' AND c.amount_paid != 0 AND julianday(c.paid_date) < julianday('$due_from_timestamp') AND c.amount_paid > 0)";
		$txtCondition .= " OR (c.paid_date2 != '' AND c.amount_paid2 != 0 AND julianday(c.paid_date2) < julianday('$due_from_timestamp') AND c.amount_paid2 > 0)";
		$txtCondition .= " OR (c.paid_date3 != '' AND c.amount_paid3 != 0 AND julianday(c.paid_date3) < julianday('$due_from_timestamp') AND c.amount_paid3 > 0)";
		$txtCondition .= " OR (c.paid_date4 != '' AND c.amount_paid4 != 0 AND julianday(c.paid_date4) < julianday('$due_from_timestamp') AND c.amount_paid4 > 0) )";
	} else {
		$due_from_timestamp = date('Y-m-d H:i:s', strtotime($due_from));
		$due_to_timestamp = date('Y-m-d H:i:s', strtotime($due_to));
		$txtCondition .= " AND ( (c.paid_date != '' AND c.amount_paid != 0 AND (julianday(c.paid_date) >= julianday('$due_from_timestamp') AND julianday(c.paid_date) < julianday('$due_to_timestamp')) AND c.amount_paid > 0)";
		$txtCondition .= " OR (c.paid_date2 != '' AND c.amount_paid2 != 0 AND (julianday(c.paid_date2) >= julianday('$due_from_timestamp') AND julianday(c.paid_date2) < julianday('$due_to_timestamp')) AND c.amount_paid2 > 0)";
		$txtCondition .= " OR (c.paid_date3 != '' AND c.amount_paid3 != 0 AND (julianday(c.paid_date3) >= julianday('$due_from_timestamp') AND julianday(c.paid_date3) < julianday('$due_to_timestamp')) AND c.amount_paid3 > 0)";
		$txtCondition .= " OR (c.paid_date4 != '' AND c.amount_paid4 != 0 AND (julianday(c.paid_date4) >= julianday('$due_from_timestamp') AND julianday(c.paid_date4) < julianday('$due_to_timestamp')) AND c.amount_paid4 > 0) )";
	}
	$txtCondition .= " GROUP BY c.`contract_id` ORDER BY c.paid_date DESC, c.paid_date2 DESC, c.paid_date3 DESC, c.paid_date4 DESC, `firm_name`, c.amount_paid_total, c.`contract_id`";
	$query .= $txtCondition;
	//die($query);

	$data = array();
	$rows = $dbInst->query($query);

	if(is_array($rows) && count($rows)>0) {
		foreach ($rows as $row) {
			$amount_paid_total = floatval($row['amount_paid_total']);
			$amount_paid_total = floatval($row['amount_paid_total']);

			$amount_paid = floatval($row['amount_paid']);
			$amount_paid = floatval($row['amount_paid']);
			$paid_date = (!empty($row['paid_date'])) ? strtotime($row['paid_date']) : 0;
			$amount_paid2 = floatval($row['amount_paid2']);
			$amount_paid2 = floatval($row['amount_paid2']);
			$paid_date2 = (!empty($row['paid_date2'])) ? strtotime($row['paid_date2']) : 0;
			$amount_paid3 = floatval($row['amount_paid3']);
			$amount_paid3 = floatval($row['amount_paid3']);
			$paid_date3 = (!empty($row['paid_date3'])) ? strtotime($row['paid_date3']) : 0;
			$amount_paid4 = floatval($row['amount_paid4']);
			$amount_paid4 = floatval($row['amount_paid4']);
			$paid_date4 = (!empty($row['paid_date4'])) ? strtotime($row['paid_date4']) : 0;

			if($amount_paid_total > 0) {} else { continue; }

			$row['PAIDON'] = '';
			$row['PAIDON_TIMESTAMP'] = 0;
			$row['AMOUNTPAID'] = 0;
			$row['AMOUNTPAIDNUM'] = '';
			if(!$due_to) {
				if($amount_paid >= 0 && $paid_date && date('Y-m-d H:i:s', $paid_date) < $due_from_timestamp) {
					$row['PAIDON'] = date('d.m.Y', $paid_date);
					$row['PAIDON_TIMESTAMP'] = $paid_date;
					$row['AMOUNTPAID'] = $amount_paid;
					$row['AMOUNTPAIDNUM'] = 'I.';
					$data[] = $row;
					$totalAmountPaid += $row['AMOUNTPAID'];
				}
				if($amount_paid2 > 0 && $paid_date2 && date('Y-m-d H:i:s', $paid_date2) < $due_from_timestamp) {
					$row['PAIDON'] = date('d.m.Y', $paid_date2);
					$row['PAIDON_TIMESTAMP'] = $paid_date2;
					$row['AMOUNTPAID'] = $amount_paid2;
					$row['AMOUNTPAIDNUM'] = 'II.';
					$data[] = $row;
					$totalAmountPaid += $row['AMOUNTPAID'];
				}
				if($amount_paid3 > 0 && $paid_date3 && date('Y-m-d H:i:s', $paid_date3) < $due_from_timestamp) {
					$row['PAIDON'] = date('d.m.Y', $paid_date3);
					$row['PAIDON_TIMESTAMP'] = $paid_date3;
					$row['AMOUNTPAID'] = $amount_paid3;
					$row['AMOUNTPAIDNUM'] = 'III.';
					$data[] = $row;
					$totalAmountPaid += $row['AMOUNTPAID'];
				}
				if($amount_paid4 > 0 && $paid_date4 && date('Y-m-d H:i:s', $paid_date4) < $due_from_timestamp) {
					$row['PAIDON'] = date('d.m.Y', $paid_date4);
					$row['PAIDON_TIMESTAMP'] = $paid_date4;
					$row['AMOUNTPAID'] = $amount_paid4;
					$row['AMOUNTPAIDNUM'] = 'IV.';
					$data[] = $row;
					$totalAmountPaid += $row['AMOUNTPAID'];
				}
			} else {
				if($amount_paid > 0 && $paid_date && date('Y-m-d H:i:s', $paid_date) >=$due_from_timestamp && date('Y-m-d H:i:s', $paid_date) < $due_to_timestamp) {
					$row['PAIDON'] = date('d.m.Y', $paid_date);
					$row['PAIDON_TIMESTAMP'] = $paid_date;
					$row['AMOUNTPAID'] = $amount_paid;
					$row['AMOUNTPAIDNUM'] = 'I.';
					$data[] = $row;
					$totalAmountPaid += $row['AMOUNTPAID'];
				}
				if($amount_paid2 > 0 && $paid_date2 && date('Y-m-d H:i:s', $paid_date2) >= $due_from_timestamp && date('Y-m-d H:i:s', $paid_date2) < $due_to_timestamp) {
					$row['PAIDON'] = date('d.m.Y', $paid_date2);
					$row['PAIDON_TIMESTAMP'] = $paid_date2;
					$row['AMOUNTPAID'] = $amount_paid2;
					$row['AMOUNTPAIDNUM'] = 'II.';
					$data[] = $row;
					$totalAmountPaid += $row['AMOUNTPAID'];
				}
				if($amount_paid3 > 0 && $paid_date3 && date('Y-m-d H:i:s', $paid_date3) >= $due_from_timestamp && date('Y-m-d H:i:s', $paid_date3) < $due_to_timestamp) {
					$row['PAIDON'] = date('d.m.Y', $paid_date3);
					$row['PAIDON_TIMESTAMP'] = $paid_date3;
					$row['AMOUNTPAID'] = $amount_paid3;
					$row['AMOUNTPAIDNUM'] = 'III.';
					$data[] = $row;
					$totalAmountPaid += $row['AMOUNTPAID'];
				}
				if($amount_paid4 > 0 && $paid_date4 && date('Y-m-d H:i:s', $paid_date4) >= $due_from_timestamp && date('Y-m-d H:i:s', $paid_date4) < $due_to_timestamp) {
					$row['PAIDON'] = date('d.m.Y', $paid_date4);
					$row['PAIDON_TIMESTAMP'] = $paid_date4;
					$row['AMOUNTPAID'] = $amount_paid4;
					$row['AMOUNTPAIDNUM'] = 'IV.';
					$data[] = $row;
					$totalAmountPaid += $row['AMOUNTPAID'];
				}
			}
		}


	}

	# http://www.the-art-of-web.com/php/sortarray/
	# sort alphabetically by due on date
	usort($data, 'compare_paidon');

	return $data;
}

function compare_dueon($a, $b) { return strnatcmp($a['DUEON_TIMESTAMP'], $b['DUEON_TIMESTAMP']); }
function compare_paidon($a, $b) { return strnatcmp($a['PAIDON_TIMESTAMP'], $b['PAIDON_TIMESTAMP']); }

if(!function_exists('calcTimespan')) {
	function calcTimespan($d, $m, $y) {
		$t = new timespan(time(), mktime(0, 0, 0, $m, $d, $y));
		return $t->years.' г. и '.$t->months.' м.';
	}
}
function makeFileName($firm_name = '') {
	$firm_name = str_replace(' ', '_', $firm_name);
	$firm_name = str_replace('"', '', $firm_name);
	$firm_name = str_replace('\'', '', $firm_name);
	$firm_name = str_replace('”', '', $firm_name);
	$firm_name = str_replace('„', '', $firm_name);
	$firm_name = str_replace('_-_', '_', $firm_name);
	
	require_once("cyrlat.class.php");
	$cyrlat = new CyrLat;
	return $cyrlat->cyr2lat($firm_name);
}

function getPopupNavigation($popupStartTitle = '') {
	if(empty($popupStartTitle)) return '';
	if(!isset($_SESSION['search_res_worker_ids'])) return '';
	if(!isset($_GET['worker_id'])) return '';

	global $dbInst;

	$IDs = $_SESSION['search_res_worker_ids'];
	$worker_id = intval($_GET['worker_id']);
	$row = $dbInst->fnSelectSingleRow("SELECT firm_id, fname, sname, lname, egn FROM `workers` WHERE `worker_id` = $worker_id");
	if(empty($row)) return '';

	$key = array_search($worker_id, $IDs);// returns the corresponding key
	if(false === $key) return '';

	$prev_worker_id = (isset($IDs[$key - 1])) ? $IDs[$key - 1] : -1;
	$next_worker_id = (isset($IDs[$key + 1])) ? $IDs[$key + 1] : -1;

	//$navUrl = preg_replace('/worker_id=(\d*)/', 'worker_id=[+worker_id+]', $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);
	$navUrl = $_SERVER['PHP_SELF'].'?firm_id='.$row['firm_id'].'&amp;worker_id=[+worker_id+]&amp;'.SESS_NAME.'='.session_id();

	$ret = '';
	ob_start();
	?>
	<script type="text/javascript">
	//<![CDATA[
	$(function() {
		var parent = window.opener || self.parent || parent;
		if(parent.document.getElementById('cboxTitle')) {
			parent.document.getElementById('cboxTitle').innerHTML = '<?=$popupStartTitle?> на <?=((isset($row['fname'])) ? HTMLFormat($row['fname'].' '.$row['lname']) : '')?><?=((isset($row['egn'])) ? ', ЕГН '.HTMLFormat($row['egn']) : '')?>';
		}

	});
	//]]>
	</script>
	<?php
	$ret .= ob_get_clean();

	$ret .= '<div id="navBtns">';
	if(-1 < $prev_worker_id) {
		$ret .= '<div id="navLeft"><a href="'.str_replace('[+worker_id+]', $prev_worker_id, $navUrl).'" title="Предишен">&nbsp;</a></div>';
	}
	if(-1 < $next_worker_id) {
		$ret .= '<div id="navRight"><a href="'.str_replace('[+worker_id+]', $next_worker_id, $navUrl).'" title="Следващ">&nbsp;</a></div>';
	}
	$ret .= '</div>';
	return $ret;
}

if (!function_exists('json_encode')) {
	function json_encode($a = false) {
		if (is_null($a)) return 'null';
		if ($a === false) return 'false';
		if ($a === true) return 'true';
		if (is_scalar($a)) {
			if (is_float($a)) {
				// Always use "." for floats.
				return floatval(str_replace(",", ".", strval($a)));
			}

			if (is_string($a)) {
				static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
				return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
			}
			else
			return $a;
		}
		$isList = true;
		for ($i = 0, reset($a); $i < count($a); $i++, next($a)) {
			if (key($a) !== $i) {
				$isList = false;
				break;
			}
		}
		$result = array();
		if ($isList) {
			foreach ($a as $v) $result[] = json_encode($v);
			return '[' . join(',', $result) . ']';
		} else {
			foreach ($a as $k => $v) $result[] = json_encode($k).':'.json_encode($v);
			return '{' . join(',', $result) . '}';
		}
	}
}

function getChart($data = array(), $imgname = '', $title = '') {
	include "libchart/classes/libchart.php";
	
	if(empty($imgname)) {
		$imgname = strtolower(basename($_SERVER['PHP_SELF']));
		$imgname = str_replace('.php', '', $imgname);
		$imgname = str_replace(' ', '_', $imgname);
	}
	
	$http = !empty($_SERVER['HTTPS']) ? 'https://' : 'http://';
	$libchart_path = $http . ((isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_ADDR'].':'.$_SERVER['SERVER_PORT'])) . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/libchart/";

	$chart = new VerticalBarChart(660,300);
	//$chart = new VerticalBarChart();
	// Set bar color
	$chart->getPlot()->getPalette()->setBarColor(array(
		new Color(42, 71, 181),		// #2A47B5
		new Color(243, 198, 118),	// #F3C676
		new Color(128, 63, 35),		// #803F23
		new Color(195, 45, 28),		// #C32D1C
		new Color(224, 198, 165),	// #E0C6A5
		new Color(239, 238, 218),	// #EFEEDA
		new Color(40, 72, 59),		// #28483B
		new Color(71, 112, 132),	// #477084
		new Color(167, 192, 199),	// #A7C0C7
		new Color(218, 233, 202)	// #DAE9CA
	));
	
	$dataSet = new XYSeriesDataSet();
	foreach ($data as $key => $val) {
		$serie = new XYDataSet();
		$serie->addPoint(new Point('', $val));
		$dataSet->addSerie($key, $serie);
	}

	$chart->setDataSet($dataSet);
	$chart->getPlot()->setGraphCaptionRatio(0.88);

	//$chart->setTitle('Разпределение по брой случаи');
	$chart->setTitle('');
	$chart->render('libchart/generated/'.$imgname.'.png');
	
	$ret = '';
	if(!empty($title)) {
		$ret .= '<p class=MsoNormal>'.$title.'</p>';
	}
	$ret .= '<p class=MsoNormal><img alt="Line chart" src="'.$libchart_path.'generated/'.$imgname.'.png" style="border: 1px solid gray;" /></p>';
	$i = 0;
	$images = array('blue', 'orange', 'brown', 'red', 'beige', 'smoke', 'dark', 'dark_blue', 'light_blue', 'light_green');
	$ret .= '<p>Легенда:</p>';
	foreach ($data as $key => $val) {
		if(!isset($images[$i])) { $i = 0; }
		$ret .= '<p class=MsoNormal><img width=10 height=10 src="'.$libchart_path.'images/'.$images[$i].'.png">&nbsp;'.$key.' ('.$val.')</p>';
		$i++;
	}
	return $ret;
}

function NEW_getChart($data = array(), $imgname = '', $title = '') {
	include "libchart/classes/libchart.php";
	
	if(empty($imgname)) {
		$imgname = strtolower(basename($_SERVER['PHP_SELF']));
		$imgname = str_replace('.php', '', $imgname);
		$imgname = str_replace(' ', '_', $imgname);
	}
	
	$libchart_path = "http://" . ((isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_ADDR'].':'.$_SERVER['SERVER_PORT'])) . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/libchart/";

	$chart = new VerticalBarChart(660,300);
	//$chart = new VerticalBarChart();
	// Set bar color
	$chart->getPlot()->getPalette()->setBarColor(array(
		new Color(42, 71, 181),		// #2A47B5
		new Color(243, 198, 118),	// #F3C676
		new Color(128, 63, 35),		// #803F23
		new Color(195, 45, 28),		// #C32D1C
		new Color(224, 198, 165),	// #E0C6A5
		new Color(239, 238, 218),	// #EFEEDA
		new Color(40, 72, 59),		// #28483B
		new Color(71, 112, 132),	// #477084
		new Color(167, 192, 199),	// #A7C0C7
		new Color(218, 233, 202)	// #DAE9CA
	));
	
	$dataSet = new XYSeriesDataSet();
	foreach ($data as $key => $val) {
		$serie = new XYDataSet();
		$serie->addPoint(new Point('', $val));
		$dataSet->addSerie($key, $serie);
	}

	$chart->setDataSet($dataSet);
	$chart->getPlot()->setGraphCaptionRatio(0.88);

	//$chart->setTitle('Разпределение по брой случаи');
	$chart->setTitle('');
	$generated_file = 'libchart/generated/'.$imgname.'.png';
	$chart->render($generated_file);
	$base64 = chunk_split(base64_encode(file_get_contents($generated_file)));
	
	$ret = '';
	if(!empty($title)) {
		$ret .= '<p class=MsoNormal>'.$title.'</p>';
	}
	$ret .= '<p class=MsoNormal><img alt="Line chart" src="'.$generated_file.'" style="border: 1px solid gray;" /></p>';
	//$ret .= '<p class=MsoNormal><img alt="Line chart" src="data:image/png;base64,'.$base64.'" style="border: 1px solid gray;" /></p>';
	$i = 0;
	$images = array('blue', 'orange', 'brown', 'red', 'beige', 'smoke', 'dark', 'dark_blue', 'light_blue', 'light_green');
	$ret .= '<p>Легенда:</p>';
	foreach ($data as $key => $val) {
		if(!isset($images[$i])) { $i = 0; }
		$ret .= '<p class=MsoNormal><img width=10 height=10 src="'.$libchart_path.'images/'.$images[$i].'.png">&nbsp;'.$key.' ('.$val.')</p>';
		$i++;
	}
	return $ret;
}

function return_bytes($val) {
	$val = trim($val);
	$last = strtolower($val[strlen($val)-1]);
	switch($last) {
		// The 'G' modifier is available since PHP 5.1.0
		case 'g':
			$val *= 1024;
		case 'm':
			$val *= 1024;
		case 'k':
			$val *= 1024;
	}
	return $val;
}

function fixMkbCode($mkb_id = '') {
	$mkb_id = trim(mb_strtoupper($mkb_id, 'utf-8'));
	$bad = array('А', 'В', 'С', 'Е', 'К', 'М', 'О', 'Р', 'Т', 'Х', 'З', 'О', ' ', ',', '\'', '"');
	$good = array('A', 'B', 'C', 'E', 'K', 'M', 'O', 'P', 'T', 'X', '3', '0', '', '.', '', '');
	return str_replace($bad, $good, $mkb_id);
}