<?php
// http://localhost/stm2008/viamed/w_rtf_eye_sharpness.php?firm_id=93
require('includes.php');

$firm_id = (isset($_GET['firm_id']) && is_numeric($_GET['firm_id'])) ? intval($_GET['firm_id']) : 0;
$f = $dbInst->getFirmInfo($firm_id);
if (!$f) {
    die('Липсва индентификатор на фирмата!');
}

$s = $dbInst->getStmInfo();

$stm_name = preg_replace('/\<br\s*\/?\>/', '', $s['stm_name']);

$firm_name = str_replace(' ', '_', $f['firm_name']);
$firm_name = str_replace('"', '', $firm_name);
$firm_name = str_replace('\'', '', $firm_name);
$firm_name = str_replace('”', '', $firm_name);
$firm_name = str_replace('„', '', $firm_name);
$firm_name = str_replace('_-_', '_', $firm_name);

require_once("cyrlat.class.php");
$cyrlat = new CyrLat;
$filename = 'SpravkaZritelnaOstrota_' . $cyrlat->cyr2lat($firm_name);

require('phprtflite/rtfbegin.php');

$sect->writeText('<b>Справка</b>', $times20, $alignCenter);
$sect->writeText('<b>относно проф. прегледи на зрителната острота на работещите в ' . ((isset($f['firm_name'])) ? HTMLFormat($f['firm_name']) : '') . '</b>' . ((isset($f['location_name'])) ? ' – ' . HTMLFormat($f['location_name']) : ''), $times14, $alignCenter);

$sect->addEmptyParagraph();
$sect->addEmptyParagraph();

class EyeSharpness
{
    public static function getWorkers($firm_id = 0)
    {
        $data = array();

        // select all active workers
        $records = ORM::for_table('workers')
            ->where('firm_id', $firm_id)
            ->where('is_active', 1)
            ->where_raw('(`date_retired` IS NULL OR `date_retired` = \'\' OR `date_retired` = \'0000-00-00 00:00:00\')')
            ->order_by_asc('fname')
            ->order_by_asc('sname')
            ->order_by_asc('lname')
            ->order_by_asc('egn')
            ->find_many();
        $aWorkers = array();
        if ($records) {
            foreach ($records as $record) {
                $aWorkers[$record->worker_id] = $record->as_array();
            }
        }

        // select the last prophylactic card results
        $records = ORM::for_table('medical_checkups')
            ->where_in('worker_id', array_keys($aWorkers))
            ->order_by_desc('checkup_date')
            ->find_many();
        if ($records) {
            foreach ($records as $record) {
                if (!isset($data[$record->worker_id])) {
                    $data[$record->worker_id] = array_merge($aWorkers[$record->worker_id], $record->as_array());
                }
            }

            // sort data by worker names
            $tmp = array();
            foreach ($aWorkers as $worker_id => $worker) {
                if (isset($data[$worker_id])) {
                    $tmp[$worker_id] = $data[$worker_id];
                }
            }
            $data = $tmp;
        }

        return $data;
    }
}

$rows = EyeSharpness::getWorkers($firm_id);
$data = array();
if (count($rows)) {
    $data[] = array('<b>№ по ред</b>', '<b>Име</b>', '<b>ЕГН</b>', '<b>Дата на прегледа</b>', '<b>Ляво око</b>', '<b>Дясно око</b>');
    $i = 1;
    foreach ($rows as $row) {

        $aLEye = array();
        if (!empty($row['left_eye'])) {
            $aLEye[] = $row['left_eye'];
        }
        if (!empty($row['left_eye2'])) {
            $aLEye[] = $row['left_eye2'];
        }

        $aREye = array();
        if (!empty($row['right_eye'])) {
            $aREye[] = $row['right_eye'];
        }
        if (!empty($row['right_eye2'])) {
            $aREye[] = $row['right_eye2'];
        }

        $flds = array();
        $flds[] = $i++ . '.';
        $flds[] = HTMLFormat($row['fname'] . ' ' . $row['sname'] . ' ' . $row['lname']);
        $flds[] = $row['egn'];
        $flds[] = (!empty($row['checkup_date']) && false !== $ts = strtotime($row['checkup_date'])) ? date('d.m.Y', $ts) : '';
        $flds[] = (!empty($aLEye)) ? implode(' / ', $aLEye) : '';
        $flds[] = (!empty($aREye)) ? implode(' / ', $aREye) : '';
        $data[] = $flds;
    }
    $colWidts = array(1.5, 3.5, 2.5, 3, 3, 3);
    $colAligns = array('center', 'left', 'center', 'center', 'center', 'center');
    fnGenerateTable($data, $colWidts, $colAligns, $tableType = 'plain');
} else {
    $sect->writeText('Няма предоставени данни.', $times14, $alignLeft);
    $sect->addEmptyParagraph();
}

$timesFooter = $times14;
require('phprtflite/rtfend.php');
