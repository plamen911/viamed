<?php
require('includes.php');

ini_set('memory_limit','128M');

$firm_id = (isset($_GET['firm_id']) && is_numeric($_GET['firm_id'])) ? intval($_GET['firm_id']) : 0;
$f = $dbInst->getFirmInfo($firm_id);
if(!$f) {
	die('Липсва индентификатор на фирмата!');
}
$sbdvsn_id = (isset($_GET['subdivision_id']) && !empty($_GET['subdivision_id'])) ? intval($_GET['subdivision_id']) : 0;
if (!empty($sbdvsn_id)) {
	$record = ORM::for_table('subdivisions')
		->select('subdivision_name')
		->where('firm_id', $firm_id)
		->where('subdivision_id', $sbdvsn_id)
		->find_one();
	$f['firm_name'] .= ($record && !empty($record->subdivision_name)) ? ' - ' . $record->subdivision_name : '';
}

$wplce_ids = (isset($_GET['wplce_ids'])) ? $_GET['wplce_ids'] : '';
if (is_array($wplce_ids)) {
	$wplce_ids = implode(',', $wplce_ids);
}
// Convert to array
$IDs = array();
if (preg_match_all('/(\d+,?)/', $wplce_ids, $matches)) {
	foreach ($matches[1] as $val) {
		$val = intval($val);
		if(!empty($val)) {
			$IDs[] = $val;
		}
	}
}

$wplce_ids = $IDs;
if (!empty($wplce_ids)) {
	$flds = ORM::for_table('work_places')
		->select('wplace_name')
		->where('firm_id', $firm_id)
		->where_in('wplace_id', $wplce_ids)
		->order_by_asc('wplace_position')
		->find_many();
	if ($flds) {
		$wplace_name = array();
		foreach ($flds as $fld) {
			$wplace_name[] = $fld->wplace_name;
		}
		$f['firm_name'] .= ', '.implode('; ', $wplace_name);
	}
}

$PF_NAME = (isset($_GET['PF_NAME']) && !empty($_GET['PF_NAME'])) ? $dbInst->checkStr($_GET['PF_NAME']) : '';

/** Include path **/
ini_set('include_path', ini_get('include_path').';PHPExcel/');

/** PHPExcel */
include 'PHPExcel.php';

/** PHPExcel_Writer_Excel2007 */
include 'PHPExcel/Writer/Excel2007.php';

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set properties
$objPHPExcel->getProperties()->setCreator($added_by_txt)
							 ->setLastModifiedBy($added_by_txt)
							 ->setTitle('Maket za vyvejdane na raboteshti')
							 ->setSubject('Maket za vyvejdane na raboteshti')
							 ->setDescription('Maket za vyvejdane na raboteshti');

// Add some data
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'ЕГН')
            ->setCellValue('B1', 'Име')
            ->setCellValue('C1', 'Подразделение')
            ->setCellValue('D1', 'Длъжност')
            ->setCellValue('E1', 'Работно място')
            ->setCellValue('F1', 'Дата на назначаване')
            ->setCellValue('G1', 'Дата на напускане')
            ->setCellValue('H1', 'Адрес');

$cond  = 'w.firm_id = ' . $firm_id;
$cond .= ' AND w.is_active = 1';
$cond .= ($sbdvsn_id) ? " AND m.subdivision_id = $sbdvsn_id " : '';
$cond .= (!empty($PF_NAME)) ? " AND i.PF_NAME LIKE '$PF_NAME' " : '';
$cond .= (!empty($wplce_ids)) ? " AND m.wplace_id IN (" . implode(', ', $wplce_ids) . ") " : '';

$workers = ORM::for_table('workers')
	->table_alias('w')
	->select('w.egn', 'egn')
	->select('w.fname', 'fname')
	->select('w.sname', 'sname')
	->select('w.lname', 'lname')
	->select('s.subdivision_name', 'subdivision_name')
	->select('p.wplace_name', 'wplace_name')
	->select('i.position_name', 'position_name')
	->select('w.date_curr_position_start', 'date_curr_position_start')
	->select('w.date_retired', 'date_retired')
	->select('w.address', 'address')
	->left_outer_join('firm_struct_map', array('m.map_id', '=', 'w.map_id'), 'm')
	->left_outer_join('subdivisions', array('s.subdivision_id', '=', 'm.subdivision_id'), 's')
	->left_outer_join('work_places', array('p.wplace_id', '=', 'm.wplace_id'), 'p')
	->left_outer_join('firm_positions', array('i.position_id', '=', 'm.position_id'), 'i')
	->where_raw($cond)
	->order_by_asc('w.date_retired')
	->order_by_asc('w.fname')
	->order_by_asc('w.sname')
	->order_by_asc('w.lname')
	->order_by_asc('w.egn')
	->order_by_asc('w.worker_id')
	->find_many();
	
$i = 1;
if ($workers) {
	foreach ($workers as $worker) {
		$i++;
		
		$date_curr_position_start = (!empty($worker->date_curr_position_start) && false !== $ts = strtotime($worker->date_curr_position_start)) ? date('d.m.Y', $ts) : '';
		$date_retired = (!empty($worker->date_retired) && false !== $ts = strtotime($worker->date_retired)) ? date('d.m.Y', $ts) : '';
		
		$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A' . $i, $worker->egn)
            ->setCellValue('B' . $i, $worker->fname . ' ' . $worker->sname . ' ' . $worker->lname)
            ->setCellValue('C' . $i, $worker->subdivision_name)
            ->setCellValue('D' . $i, $worker->position_name)
            ->setCellValue('E' . $i, $worker->wplace_name)
            ->setCellValue('F' . $i, $date_curr_position_start)
            ->setCellValue('G' . $i, $date_retired)
            ->setCellValue('H' . $i, $worker->address);
	}
}

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(12);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);

$objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
$objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFill()->getStartColor()->setARGB(PHPExcel_Style_Color::COLOR_YELLOW);

// Set fonts
$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('H1')->getFont()->setBold(true);

$filename = generateFileName($f['firm_name'], 'Maket_raboteshti_', '');

// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="' . short_text($filename, 40) . '.xls"');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header ('Pragma: public'); // HTTP/1.0

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;