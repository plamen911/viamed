<?php
require('includes.php');

if(isset($_POST['ajax_req'])) {
	$ret = array();
	$ret['code'] = 0;
	$ret['message'] = '';
	$ret['data'] = array();
	
	switch ($_POST['ajax_req']) {
		case 'transfer_medical_info':
			$transf = array();
			$transf['medical_precheckups'] = 0;
			$transf['patient_charts'] = 0;
			$transf['telks'] = 0;
			$transf['medical_checkups'] = 0;
			
			//gather all medical info for this worker based on his/her EGN from all other firms in DB
			$worker_id = (isset($_POST['worker_id'])) ? intval($_POST['worker_id']) : 0;
			$row = $dbInst->fnSelectSingleRow("SELECT `firm_id`, `fname`, `sname`, `lname`, `egn` FROM `workers` WHERE `worker_id` = $worker_id");
			$firm_id = $row['firm_id'];
			$fname = $row['fname'];
			$sname = $row['sname'];
			$lname = $row['lname'];
			$egn = $row['egn'];

			//get woorker's patient charts from current firm
			$aCurrentPatientCharts = array();
			$sql = "SELECT `hospital_date_from`, `chart_id` FROM `patient_charts` WHERE `worker_id` = $worker_id AND `hospital_date_from` != ''";
			$rows = $dbInst->query($sql);
			if(!empty($rows)) {
				foreach ($rows as $key => $row) {
					$aCurrentPatientCharts[$row['hospital_date_from']] = $row['chart_id'];
				}
			}
			//transfer patient charts from other firms
			$sql = "SELECT c.*
					FROM `patient_charts` c
					LEFT JOIN `workers` w ON (w.`worker_id` = c.`worker_id`)
					WHERE w.`egn` LIKE '$egn'
					AND c.`firm_id` != $firm_id";
			if(!empty($aCurrentPatientCharts)) {
				$sql .= " AND c.`hospital_date_from` NOT IN ('".implode("','", array_keys($aCurrentPatientCharts))."')";
			}
			$rows = $dbInst->query($sql);
			if(!empty($rows)) {
				$i = 0;
				foreach ($rows as $key => $row) {
					$flds = array();
					foreach ($row as $key => $val) {
						if(is_numeric($key) || in_array($key, array('chart_id'))) continue;
						$flds[$key] = (is_null($val)) ? '' : $dbInst->checkStr($val);
					}
					$flds['firm_id'] = $firm_id;
					$flds['worker_id'] = $worker_id;
					$sql = "INSERT INTO `patient_charts` (`".implode('`,`', array_keys($flds))."`) VALUES ('".implode("','", array_values($flds))."')";
					$chart_id = $dbInst->query($sql);
					$i++;
				}
				$transf['patient_charts'] = $i;
			}

			//*************

			//get woorker's medical precheckups from current firm
			$aCurrentPatientPreCheckups = array();
			$sql = "SELECT `prchk_date`, `precheckup_id` FROM `medical_precheckups` WHERE `worker_id` = $worker_id AND `prchk_date` != ''";
			$rows = $dbInst->query($sql);
			if(!empty($rows)) {
				foreach ($rows as $key => $row) {
					$aCurrentPatientPreCheckups[$row['prchk_date']] = $row['precheckup_id'];
				}
			}
			//transfer medical precheckups from other firms
			$sql = "SELECT c.*
					FROM `medical_precheckups` c
					LEFT JOIN `workers` w ON (w.`worker_id` = c.`worker_id`)
					WHERE w.`egn` LIKE '$egn'
					AND c.`firm_id` != $firm_id";
			if(!empty($aCurrentPatientPreCheckups)) {
				$sql .= " AND c.`prchk_date` NOT IN ('".implode("','", array_keys($aCurrentPatientPreCheckups))."')";
			}
			$rows = $dbInst->query($sql);
			if(!empty($rows)) {
				$i = 0;
				foreach ($rows as $key => $row) {
					$flds = array();
					foreach ($row as $key => $val) {
						if(is_numeric($key) || in_array($key, array('precheckup_id'))) continue;
						$flds[$key] = (is_null($val)) ? '' : $dbInst->checkStr($val);
					}
					$flds['firm_id'] = $firm_id;
					$flds['worker_id'] = $worker_id;
					$sql = "INSERT INTO `medical_precheckups` (`".implode('`,`', array_keys($flds))."`) VALUES ('".implode("','", array_values($flds))."')";
					$precheckup_id = $dbInst->query($sql);

					$sql = "SELECT * FROM `medical_precheckups_doctors2` WHERE `precheckup_id` = $row[precheckup_id]";
					$lines = $dbInst->query($sql);
					if(!empty($lines)) {
						foreach ($lines as $line) {
							$flds = array();
							foreach ($line as $key => $val) {
								if(is_numeric($key)) continue;
								$flds[$key] = (is_null($val)) ? '' : $dbInst->checkStr($val);
							}
							$flds['precheckup_id'] = $precheckup_id;
							$sql = "INSERT INTO `medical_precheckups_doctors2` (`".implode('`,`', array_keys($flds))."`) VALUES ('".implode("','", array_values($flds))."')";
							$dbInst->query($sql);
						}
					}

					$sql = "SELECT * FROM `prchk_diagnosis` WHERE `precheckup_id` = $row[precheckup_id]";
					$lines = $dbInst->query($sql);
					if(!empty($lines)) {
						foreach ($lines as $line) {
							$flds = array();
							foreach ($line as $key => $val) {
								if(is_numeric($key) || in_array($key, array('prchk_id'))) continue;
								$flds[$key] = (is_null($val)) ? '' : $dbInst->checkStr($val);
							}
							$flds['precheckup_id'] = $precheckup_id;
							$flds['worker_id'] = $worker_id;
							$sql = "INSERT INTO `prchk_diagnosis` (`".implode('`,`', array_keys($flds))."`) VALUES ('".implode("','", array_values($flds))."')";
							$dbInst->query($sql);
						}
					}
					$i++;
				}
				$transf['medical_precheckups'] = $i;
			}

			//*************

			//get woorker's patient charts from current firm
			$aCurrentTelks = array();
			$sql = "SELECT `telk_date_from`, `telk_id` FROM `telks` WHERE `worker_id` = $worker_id AND `telk_date_from` != ''";
			$rows = $dbInst->query($sql);
			if(!empty($rows)) {
				foreach ($rows as $key => $row) {
					$aCurrentTelks[$row['telk_date_from']] = $row['telk_id'];
				}
			}
			//transfer telks from other firms
			$sql = "SELECT c.*
					FROM `telks` c
					LEFT JOIN `workers` w ON (w.`worker_id` = c.`worker_id`)
					WHERE w.`egn` LIKE '$egn'
					AND c.`firm_id` != $firm_id";
			if(!empty($aCurrentTelks)) {
				$sql .= " AND c.`telk_date_from` NOT IN ('".implode("','", array_keys($aCurrentTelks))."')";
			}
			$rows = $dbInst->query($sql);
			if(!empty($rows)) {
				$i = 0;
				foreach ($rows as $key => $row) {
					$flds = array();
					foreach ($row as $key => $val) {
						if(is_numeric($key) || in_array($key, array('telk_id'))) continue;
						$flds[$key] = (is_null($val)) ? '' : $dbInst->checkStr($val);
					}
					$flds['firm_id'] = $firm_id;
					$flds['worker_id'] = $worker_id;
					$sql = "INSERT INTO `telks` (`".implode('`,`', array_keys($flds))."`) VALUES ('".implode("','", array_values($flds))."')";
					$chart_id = $dbInst->query($sql);
					$i++;
				}
				$transf['telks'] = $i;
			}

			//*************

			//get woorker's medical checkups from current firm
			$aCurrentPatientCheckups = array();
			$sql = "SELECT `checkup_date`, `checkup_id` FROM `medical_checkups` WHERE `worker_id` = $worker_id AND `checkup_date` != ''";
			$rows = $dbInst->query($sql);
			if(!empty($rows)) {
				foreach ($rows as $key => $row) {
					$aCurrentPatientCheckups[$row['checkup_date']] = $row['checkup_id'];
				}
			}
			//transfer medical checkups from other firms
			$sql = "SELECT c.*
					FROM `medical_checkups` c
					LEFT JOIN `workers` w ON (w.`worker_id` = c.`worker_id`)
					WHERE w.`egn` LIKE '$egn'
					AND c.`firm_id` != $firm_id";
			if(!empty($aCurrentPatientCheckups)) {
				$sql .= " AND c.`checkup_date` NOT IN ('".implode("','", array_keys($aCurrentPatientCheckups))."')";
			}
			$rows = $dbInst->query($sql);
			if(!empty($rows)) {
				$i = 0;
				foreach ($rows as $key => $row) {
					$flds = array();
					foreach ($row as $key => $val) {
						if(is_numeric($key) || in_array($key, array('checkup_id'))) continue;
						$flds[$key] = (is_null($val)) ? '' : $dbInst->checkStr($val);
					}
					$flds['firm_id'] = $firm_id;
					$flds['worker_id'] = $worker_id;
					$sql = "INSERT INTO `medical_checkups` (`".implode('`,`', array_keys($flds))."`) VALUES ('".implode("','", array_values($flds))."')";
					$checkup_id = $dbInst->query($sql);

					$sql = "SELECT * FROM `family_diseases` WHERE `checkup_id` = $row[checkup_id]";
					$lines = $dbInst->query($sql);
					if(!empty($lines)) {
						foreach ($lines as $line) {
							$flds = array();
							foreach ($line as $key => $val) {
								if(is_numeric($key) || in_array($key, array('disease_id'))) continue;
								$flds[$key] = (is_null($val)) ? '' : $dbInst->checkStr($val);
							}
							$flds['checkup_id'] = $checkup_id;
							$flds['firm_id'] = $firm_id;
							$flds['worker_id'] = $worker_id;
							$sql = "INSERT INTO `family_diseases` (`".implode('`,`', array_keys($flds))."`) VALUES ('".implode("','", array_values($flds))."')";
							$dbInst->query($sql);
						}
					}

					$sql = "SELECT * FROM `family_weights` WHERE `checkup_id` = $row[checkup_id]";
					$lines = $dbInst->query($sql);
					if(!empty($lines)) {
						foreach ($lines as $line) {
							$flds = array();
							foreach ($line as $key => $val) {
								if(is_numeric($key) || in_array($key, array('family_weight_id'))) continue;
								$flds[$key] = (is_null($val)) ? '' : $dbInst->checkStr($val);
							}
							$flds['checkup_id'] = $checkup_id;
							$flds['firm_id'] = $firm_id;
							$flds['worker_id'] = $worker_id;
							$sql = "INSERT INTO `family_weights` (`".implode('`,`', array_keys($flds))."`) VALUES ('".implode("','", array_values($flds))."')";
							$dbInst->query($sql);
						}
					}

					$sql = "SELECT * FROM `anamnesis` WHERE `checkup_id` = $row[checkup_id]";
					$lines = $dbInst->query($sql);
					if(!empty($lines)) {
						foreach ($lines as $line) {
							$flds = array();
							foreach ($line as $key => $val) {
								if(is_numeric($key) || in_array($key, array('anamnesis_id'))) continue;
								$flds[$key] = (is_null($val)) ? '' : $dbInst->checkStr($val);
							}
							$flds['checkup_id'] = $checkup_id;
							$flds['firm_id'] = $firm_id;
							$flds['worker_id'] = $worker_id;
							$sql = "INSERT INTO `anamnesis` (`".implode('`,`', array_keys($flds))."`) VALUES ('".implode("','", array_values($flds))."')";
							$dbInst->query($sql);
						}
					}

					$sql = "SELECT * FROM `lab_checkups` WHERE `checkup_id` = $row[checkup_id]";
					$lines = $dbInst->query($sql);
					if(!empty($lines)) {
						foreach ($lines as $line) {
							$flds = array();
							foreach ($line as $key => $val) {
								if(is_numeric($key) || in_array($key, array('lab_checkup_id'))) continue;
								$flds[$key] = (is_null($val)) ? '' : $dbInst->checkStr($val);
							}
							$flds['checkup_id'] = $checkup_id;
							$flds['firm_id'] = $firm_id;
							$flds['worker_id'] = $worker_id;
							$sql = "INSERT INTO `lab_checkups` (`".implode('`,`', array_keys($flds))."`) VALUES ('".implode("','", array_values($flds))."')";
							$dbInst->query($sql);
						}
					}

					$sql = "SELECT * FROM `medical_checkups_doctors2` WHERE `checkup_id` = $row[checkup_id]";
					$lines = $dbInst->query($sql);
					if(!empty($lines)) {
						foreach ($lines as $line) {
							$flds = array();
							foreach ($line as $key => $val) {
								if(is_numeric($key)) continue;
								$flds[$key] = (is_null($val)) ? '' : $dbInst->checkStr($val);
							}
							$flds['checkup_id'] = $checkup_id;
							$sql = "INSERT INTO `medical_checkups_doctors2` (`".implode('`,`', array_keys($flds))."`) VALUES ('".implode("','", array_values($flds))."')";
							$dbInst->query($sql);
						}
					}
					$i++;
				}
				$transf['medical_checkups'] = $i;
			}
			
			$message = '<strong>'.$fname.' '.$sname.' '.$lname.'</strong> ('.$egn.'): ';
			$message .= 'Предварит. прегледи: '.$transf['medical_precheckups'].', ';
			$message .= 'Болнични: '.$transf['patient_charts'].', ';
			$message .= 'ТЕЛК: '.$transf['telks'].', ';
			$message .= 'Профилакт. прегледи: '.$transf['medical_checkups'];
			
			$ret['message'] = $message;
			die(json_encode($ret));
	}
	die(json_encode($ret));
}

$firm_id = (isset($_GET['firm_id'])) ? intval($_GET['firm_id']) : 0;
$rows = $dbInst->query("SELECT `worker_id` FROM `workers` WHERE `firm_id` = $firm_id ORDER BY `fname`, `sname`, `lname`, `worker_id`");
$IDs = array();
if(!empty($rows)) {
	foreach ($rows as $row) {
		$IDs[] = $row['worker_id'];
	}
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Прехвърляне на медицинска информация от други фирми</title>
<link href="styles.css" rel="stylesheet" type="text/css" media="screen" />
<style type="text/css">
<!--
body, html {
	background-image:none;
	background-color:#EEEEEE;
}
#transferInner {
	text-align:left;
	width:780px;
}
#transferInner li {
	padding:2px;
	border-bottom:1px dotted #666;
}
-->
</style>
<script type="text/javascript" src="js/jquery-latest.pack.js"></script>
<script type="text/javascript">
//<![CDATA[
var IDs = [<?=(implode(',', $IDs))?>];

var ajax_endpoint = '<?=$_SERVER['PHP_SELF']?>?<?=$_SERVER['QUERY_STRING']?>';
var i = 0;

$(function(){
	transferMedicalInfo();
	
	if(parent.$("#cboxClose")[0]) {
		// Reload the parent window when the close button of Colorbox popup is clicked!
		parent.$("#cboxClose")[0].onclick = function() {
			parent.location.reload();
		}
	}
});

var transferMedicalInfo = function(){
	if(IDs.length) {
		var worker_id = IDs[i];
		$.ajax({
			type: 'post',
			url: ajax_endpoint,
			dataType: 'json',
			data: { ajax_req: 'transfer_medical_info', worker_id: worker_id }, // End data
			success: function(result) {
				$('#transferInner').append('<li>' + result.message + '<\/li>');
				i++;
				$('#progressInner').html(i);
				if(i > IDs.length - 1) {
					$('#transferDone').html('<h1>Готово! Медицинската информация от други фирми на ' + i + ' от <?=count($IDs)?> работещи бе успешно прехвърлена.<\/h1>');
				}
				$(window).scrollTop($(document).height());//scroll to bottom
			}, // End success
			error: function(jqXHR, textStatus, errorThrown) {
				$('#transferInner').append("Error... " + textStatus + " / " + errorThrown);
			}, // End error
			complete: function(){
				if(i <= IDs.length - 1) {
					window.setTimeout(function(){
						transferMedicalInfo();
					}, 100);
				}
			}
		}); // End ajax method
	}
};
//]]>
</script>
</head>
<body>
<div id="contentinner" align="center">
  <h1>Прехвърляне на медицинска информация от други фирми - <span id="progressInner">0</span> от <?=count($IDs)?> работещи</h1>
  <ol id="transferInner"></ol>
  <div id="transferDone"></div>
</div>
</body>
</html>
