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
//$sect->addEmptyParagraph();
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

/**
 * @param array $dataMap
 * @param int $total
 * @param string $suffix
 * @param PHPRtfLite_Container_Section $sect
 * @param PHPRtfLite_Font $font
 * @param PHPRtfLite_ParFormat $parFormat
 * @return void
 */
function fnChartDataPercentsList($dataMap, $total, $suffix, $sect, $font, $parFormat)
{
    arsort($dataMap);

    $position = 1;
    foreach ($dataMap as $key => $val) {
        $percent = sprintf('%.2f', ((int)$val / (int)$total) * 100);
        $sect->writeText('    ' . $position . ' място ' . $key . ' или ' . $percent . ' % от всички ' . $suffix . ';', $font, $parFormat);
        $position++;
    }
}

/**
 * @param string $firmName
 * @param string $timePeriod
 * @param PHPRtfLite_Container_Section $sect
 * @param PHPRtfLite_Font $font
 * @param PHPRtfLite_ParFormat $parFormat
 * @return void
 */
function fnGeneralConclusion($firmName, $timePeriod, $sect, $font, $parFormat)
{
    $sect->writeText('    Във връзка с гореизложеното, въпреки, че работодателят е предприел всички необходими мерки за осигуряване на здравословни и безопасни условия на труд, съществува частична връзка между данните за заболеваемостта и условията на труд. От анализа на база болнични листове, регистрираните заболявания с най-висок относителен дял по честота на случаите и дните имат пряка връзка с условията на труд в период на вирусни епидемии.', $font, $parFormat);
    $sect->writeText('    За много от заболяванията има и други причини, като генетични фактори, здравна култура, начин на хранене, вредни навици и други, които могат да инициират или обострят развили се вече заболявания. Такива са:', $font, $parFormat);
    $sect->writeText('    - Наднорменото тегло – една от основните причини за заболяването артериална хипертония;', $font, $parFormat);
    $sect->writeText('    - Фамилната обремененост – наличието на генетична предиспозиция многократно увеличава вероятността от развитие на редица заболявания на сърдечно-съдовата система и жлези с вътрешна секреция;', $font, $parFormat);
    $sect->writeText('    - Тютюнопушене и употреба на алкохол – наличието на тези социално-значими фактори е предпоставка за развитие на хронични заболявания.', $font, $parFormat);
    $sect->addEmptyParagraph();
    $sect->writeText('    Рискът от заразяване със SARS-COV-2 не може да се приеме за професионален риск, доколкото вероятността от заразяване е в резултат от общата епидемична обстановка, а не от източници на работната среда и е съизмерима с тази на общото население. Работещите са уведомени за задължителните предпазни мерки във връзка с пандемичната обстановка, налагащи носенето на маски в затворени помещения и при по-малка дистанция от 1,5 м на открити пространства, необходимостта от провеждане на дезинфекция и ограничаване на физическия контакт.', $font, $parFormat);
    $sect->addEmptyParagraph();
    $sect->writeText('    След предоставяне на обобщения анализ на заболяваемостта на работещите в ' . $firmName . ' през ' . $timePeriod . ' г. препоръчваме да бъде продължена добрата практика относно:', $font, $parFormat);
    $sect->writeText('    1. Отделяне на специално внимание на конкретните мерки за оптимизиране на условията на труд и за редуциране на установените рискови фактори;', $font, $parFormat);
    $sect->writeText('    2. Трудоустрояването на подходящи места на лицата с установени заболявания;', $font, $parFormat);
    $sect->writeText('    3. При постъпване на работа работодателят да изисква медицинско свидетелство от предварителните медицински прегледи със заключение за професионална пригодност, съгл. Нар. 3 от 1987 г. /ДВ бр. 16 от 1987 г./;', $font, $parFormat);
    $sect->writeText('    4. Динамичното наблюдение: редовни периодични медицински прегледи, като се вземе предвид необходимите медицински специалисти и изследвания и честотата на провеждане по препоръчания от СТМ ВИАМЕД ООД списък, насочване към личния лекар за проследяване и лечение;', $font, $parFormat);
    $sect->writeText('    5. Повишаване на информираността на работещите за прилагане на утвърдените правила за диспансерно наблюдение и медикаментозно контролиране, с цел недопускане на ранни усложнения и инвалидизации в работоспособна възраст;', $font, $parFormat);
    $sect->writeText('    6. Спазване на утвърдения Режим на труд и почивка в дружеството;', $font, $parFormat);
    $sect->writeText('    7. Мониториране на факторите на работната среда и на трудовия процес, и на здравното състояние на работещите с цел ранното откриване на „ранимите групи“ и адекватния подход към тях;', $font, $parFormat);
    $sect->writeText('    8. Реализиране съвместно със СТМ на програми за промоция на здравето на работещите на работното място, отстраняване на рисковите фактори на начина на живот, опазване и укрепване на работоспособността и преодоляване на стреса при работа;', $font, $parFormat);
    $sect->writeText('    9. Спазване на разпоредбите на Министерство на здравеопазването и регионалния оперативен щаб и периодичен инструктаж във връзка с извънредната епидемиологична обстановка и разясняване на необходимостта от прилагане на противоепидемичните мерки.', $font, $parFormat);
    $sect->addEmptyParagraph();
}