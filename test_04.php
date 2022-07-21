<?php
header('Content-Type: text/html; charset=utf-8');
require('includes.php');
error_reporting(E_ALL);

$firm_id = 93;
$worker_id = 18864;


// get the last checkup

class EyeSharpness {
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
echo '<pre>' . print_r($rows, 1) . '</pre>';