<?php

header("Content-type: text/html;charset=UTF-8");

if(isset($_SERVER['SERVER_ADMIN']) && 'plamen@lynxlake.org' == $_SERVER['SERVER_ADMIN']) {
	error_reporting(E_ALL);
} else {
	error_reporting(0);
}

if(strpos($_SERVER["SERVER_SOFTWARE"], 'Abyss') !== false) {
	$_SERVER['PHP_SELF'] = $_ENV["URL"];	// Hack to fix the script URL
}

$STM = 'Служба по трудова медицина';
if(file_exists('defines.xml')) {
	$text = file_get_contents('defines.xml');
	if(preg_match('/\<stm_name\>(.*?)\<\/stm_name\>/si', $text, $matches)) {
		$STM = trim($matches[1]);
	}
}

define('SECRET_PASS', 'babamarta');
define('SITE_NAME', $STM);
if(preg_match('/^acc_/i', basename($_SERVER['PHP_SELF']))) {
	define('SESS_NAME', 'ACC_BGSTM');
	$STM = 'Счетоводна програма';
} else {
	define('SESS_NAME', 'BGSTM');
}
if (!defined('PHP_EOL')) define ('PHP_EOL', strtoupper(substr(PHP_OS,0,3) == 'WIN') ? "\r\n" : "\n");
define('CREATE_FIRM_FOLDERS', 0);
define('SHOW_ACCOUNTING_APP', 0);


$aServices = array('annex' => 'Анекс към договора', 'serve' => 'Обслужване', 'train' => 'Обучение', 'estimate' => 'Оценки', 'plans' => 'План аварии', 'ventilation' => 'Вентилация', 'vibrations' => 'Вибрации', 'electricity' => 'Ел. измервания', 'ground' => 'Заземление и мълниезащита', 'microclimate' => 'Микроклимат', 'light' => 'Осветление', 'dust' => 'Прах', 'chemicals' => 'Химически вещества', 'noise' => 'Шум');

function getServices() {
	$aServices = array();

	$item = array();
	$item['annex'] = 'Анекс към договора';
	$catg[''] = $item;

	$item = array();
	$item['serve'] = 'Обслужване';
	$item['train'] = 'Обучение';
	$item['estimate'] = 'Оценки';
	$item['plans'] = 'План аварии';
	//$item['other'] = 'Други';
	$catg['Услуги'] = $item;

	$item = array();
	$item['ventilation'] = 'Вентилация';
	$item['vibrations'] = 'Вибрации';
	$item['electricity'] = 'Ел. измервания';
	$item['ground'] = 'Заземление и мълниезащита';
	$item['microclimate'] = 'Микроклимат';
	$item['light'] = 'Осветление';
	$item['dust'] = 'Прах';
	$item['chemicals'] = 'Химически вещества';
	$item['noise'] = 'Шум';
	$catg['Измервания'] = $item;

	$aServices[] = $catg;
	
	return $aServices;
}
