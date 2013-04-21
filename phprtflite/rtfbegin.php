<?php
// http://sourceforge.net/projects/phprtf/
// http://sigma-scripts.de/phprtflite/docs/sections.html
require('phprtflite/lib/PHPRtfLite.php');

// register PHPRtfLite class loader
PHPRtfLite::registerAutoloader();

$rtf = new PHPRtfLite();
$rtf->setMargins(3, 1, 1, 2);

//Fonts
$times10 = new PHPRtfLite_Font(10, 'Times new Roman');
$times11 = new PHPRtfLite_Font(11, 'Times new Roman');
$times12 = new PHPRtfLite_Font(12, 'Times new Roman');
$times14 = new PHPRtfLite_Font(14, 'Times new Roman');
$times20 = new PHPRtfLite_Font(20, 'Times new Roman');

$parBlack = new PHPRtfLite_ParFormat();
$parBlack->setBackgroundColor('#CCCCCC');

$alignCenter = new PHPRtfLite_ParFormat(PHPRtfLite_ParFormat::TEXT_ALIGN_CENTER);
$alignLeft = new PHPRtfLite_ParFormat(PHPRtfLite_ParFormat::TEXT_ALIGN_LEFT);
$alignRight = new PHPRtfLite_ParFormat(PHPRtfLite_ParFormat::TEXT_ALIGN_RIGHT);

$sect = $rtf->addSection();

$image = $sect->addImage('img/logo_viamed.jpg');
/*
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

$stm_name = (isset($s['stm_name'])) ? HTMLFormat($s['stm_name']) : 'СЛУЖБА ПО ТРУДОВА МЕДИЦИНА';
$stm_address  = (isset($s['address'])) ? HTMLFormat($s['address']).', ' : '';
$stm_address .= ($s['phone1']) ? 'тел. '.$s['phone1'] : '';
$stm_address .= ($s['phone2']) ? ', '.$s['phone2'] : '';
$stm_address .= ($s['fax']) ? ', факс: '.$s['fax'] : '';
$stm_address .= ($s['email']) ? ', e-mail: '.$s['email'] : '';

$sect->writeText('<b>'.$stm_name.'</b>', new PHPRtfLite_Font($fsize, 'Times new Roman', '#000080'), $alignCenter);
$sect->writeText($stm_address, $times11, $alignCenter);
if($s['license_num'] != '') {
	$sect->writeText('Удостоверение № '.HTMLFormat($s['license_num']).' от Министерство на Здравеопазването', $times11, $alignCenter);
}

$sect->addEmptyParagraph(new PHPRtfLite_Font(1, 'Times new Roman'), $parBlack);
*/
$sect->addEmptyParagraph();
$sect->addEmptyParagraph();

function fnGenerateTable($data = array(), $colWidts = array(), $colAligns = array(), $tableType = 'nosology', &$table = null) {
	global $rtf, $sect, $times10, $times11, $times12, $times14;

	$colorGreen = '#CCFFCC';
	$colorGrey = '#E3E3E3';

	if(null === $table) {
		$rowCount = count($data);
		$colCount = count($colWidts);
		$table = $sect->addTable();
		$table->addRows($rowCount);
		$table->addColumnsList($colWidts);
		
		//borders
		$border = PHPRtfLite_Border::create($rtf, 1, '#000000');
		$table->setBorderForCellRange($border, 1, 1, $rowCount, $colCount);
	}
	
	foreach ($data as $i => $rows) {
		foreach ($rows as $j => $row) {
			$rowIndex = $i + 1;
			$columnIndex = $j + 1;
			$cell = $table->getCell($rowIndex, $columnIndex);
			if('nosology' == $tableType) {
				if(1 == $rowIndex) {
					$cell->writeText($row, $times14);
				} else {
					$cell->writeText($row, $times11);
				}
			} elseif (in_array($tableType, array('pro_groups'))) {
				$cell->writeText($row, $times10);
			} elseif (in_array($tableType, array('small'))) {
				$cell->writeText($row, $times11);
			} else {
				$cell->writeText($row, $times12);
			}
			$cell->setVerticalAlignment(PHPRtfLite_Table_Cell::VERTICAL_ALIGN_CENTER);
			$align = (isset($colAligns[$j])) ? $colAligns[$j] : 'center';
			if(1 == $rowIndex) {
				if(in_array($tableType, array('nosology', 'pro_groups'))) {
					$cell->setBackgroundColor($colorGreen);
				}
				$align = 'center';
			}
			elseif('nosology' == $tableType) {
				if(preg_match('/^\<b\>[IXV]{1,4}\.\s+/', $rows[0])) {
					$cell->setBackgroundColor($colorGreen);
				}
				elseif(preg_match('/^\<b\>[IXV]{1,4}\.[0-9]+\.\s+/', $rows[0])) {
					$cell->setBackgroundColor($colorGrey);
				}
				elseif($rowCount == $rowIndex) {
					$cell->setBackgroundColor($colorGreen);
					$align = 'center';
				}
			}
			switch ($align) {
				case 'left':
					$cell->setTextAlignment(PHPRtfLite_Table_Cell::TEXT_ALIGN_LEFT);
					break;
				case 'right':
					$cell->setTextAlignment(PHPRtfLite_Table_Cell::TEXT_ALIGN_RIGHT);
					break;
				case 'center':
				default:
					$cell->setTextAlignment(PHPRtfLite_Table_Cell::TEXT_ALIGN_CENTER);
			}
		}
	}
}

function fnExtractTableData($table = '') {
	$data = array();
	if(preg_match_all('/\<tr\s*.*?\>(.*?)\<\/tr\>/is', $table, $matches)) {
		foreach ($matches[1] as $cols) {
			if(preg_match_all('/\<td\s+.*?\>(.*?)\<\/td\>/is', $cols, $found)) {
				$line = array();
				foreach ($found[1] as $text) {
					$text = preg_replace('/\<b\s+.*?\>/is', '<b>', $text);
					$text = trim(strip_tags($text, '<b></b>'));
					$text = str_replace("\n", '', $text);
					$text = str_replace("\r", '', $text);
					$text = str_replace("\t", '', $text);
					$line[] = $text;
				}
				$data[] = $line;
			}
		}
	}
	return $data;
}

function fnGenerateChart($data = array(), $imgname = '', $title = '') {
	global $sect, $times11, $times12, $alignLeft;
	
	include "libchart/classes/libchart.php";
	
	if(empty($imgname)) {
		$imgname = strtolower(basename($_SERVER['PHP_SELF'], '.php'));
		$imgname = str_replace(' ', '_', $imgname);
	}
	
	$libchart_path = 'libchart/';

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
	$chart->render($libchart_path.'generated/'.$imgname.'.png');
	
	$ret = '';
	if(!empty($title)) {
		$sect->writeText($title.' ', $times12, $alignLeft);
	}
	$sect->addImage($libchart_path.'generated/'.$imgname.'.png', null);
	
	$i = 0;
	$images = array('blue', 'orange', 'brown', 'red', 'beige', 'smoke', 'dark', 'dark_blue', 'light_blue', 'light_green');
	$sect->writeText('Легенда:', $times12, $alignLeft);
	
	foreach ($data as $key => $val) {
		if(!isset($images[$i])) { $i = 0; }
		$sect->addImage($libchart_path.'images/'.$images[$i].'.png', new PHPRtfLite_ParFormat());
		$sect->writeText(' '.$key.' ('.$val.')', $times11);
		$i++;
	}
	$sect->addEmptyParagraph();
}

