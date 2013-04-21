<?php
require('includes.php');

header('Content-type:text/plain; charset=utf-8');

$q = $_GET["q"];
if (!$q) return;

if(isset($_GET['search'])) {

	switch ($_GET['search']) {
		case 'locations':
			$items = $dbInst->searchByLocation($q);
			foreach ($items as $key=>$value) {
				echo "$value|$key\n";
			}
			break;
			
		case 'communities':
			$items = $dbInst->searchByCommunity($q);
			foreach ($items as $key=>$value) {
				echo "$value|$key\n";
			}
			break;

		case 'provinces':
			$items = $dbInst->searchByProvince($q);
			foreach ($items as $key=>$value) {
				echo "$value|$key\n";
			}
			break;
	
		case 'wname':
			$firm_id = (isset($_GET['firm_id'])) ? intval($_GET['firm_id']) : 0;
			$items = $dbInst->searchByWName($q, $firm_id);
			foreach ($items as $item) {
				echo "$item[worker_id]|$item[wname]|$item[firm_id]|$item[egn]\n";
			}
			break;
			
		case 'mkb':
			$items = $dbInst->searchByMkb($q);
			foreach ($items as $item) {
				echo "$item[mkb_id]|$item[mkb_desc]|$item[mkb_code]\n";
			}
			break;
			
		case 'medical_reasons':
			$items = $dbInst->searchByMedicalReasons($q);
			foreach ($items as $item) {
				echo "$item[reason_id]|$item[reason_desc]\n";
			}
			break;
			
		case 'doctors':
			$items = $dbInst->searchByDoctor($q);
			foreach ($items as $key=>$value) {
				echo "$value|$key\n";
			}
			break;
			
		case 'position_name':
			$items = $dbInst->searchByWPosition($q);
			foreach ($items as $item) {
				echo "$item[position_name]|".mb_ereg_replace("\n", " ", $item['position_workcond'])."\n";
			}
			break;
			
		case 'wplace_name':
			$items = $dbInst->searchByWPlace($q);
			foreach ($items as $item) {
				echo "$item[wplace_name]|".mb_ereg_replace("\n", " ", $item['wplace_workcond'])."\n";
			}
			break;
			
		case 'fact_dust':
		case 'fact_chemicals':
		case 'fact_biological':
		case 'fact_work_pose':
		case 'fact_manual_weights':
		case 'fact_monotony':
		case 'fact_work_regime':
		case 'fact_work_hours':
		case 'fact_work_and_break':
		case 'fact_nervous':
		case 'fact_other':
			$items = $dbInst->searchByFactor($q, $_GET['search']);
			foreach ($items as $item) {
				echo mb_ereg_replace("\n", " ", $item[$_GET['search']])."\n";
			}
			break;
		
		default:
			break;
	}


}

?>