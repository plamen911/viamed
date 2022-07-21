<?php
// http://localhost/stm2008/viamed/debug_analysis.php?firm_id=258&date_from=01.01.2013&date_to=31.12.2013
require('includes.php');
require('class.stmstats.php');

$firm_id = (isset($_GET['firm_id']) && is_numeric($_GET['firm_id'])) ? intval($_GET['firm_id']) : 0;
$f = $dbInst->getFirmInfo($firm_id);
if (!$f) {
    die('Липсва индентификатор на фирмата!');
}
$s = $dbInst->getStmInfo();

//$stm_name = preg_replace('/\<br\s*\/?\>/', '', $s['stm_name']);

if (!isset($_GET['date_from']) || trim($_GET['date_from']) == '') {
    $y = date('Y') - 1;
    $date_from = date('Y-m-d H:i:s', mktime(0, 0, 0, 1, 1, $y));
    $date_to = date('Y-m-d H:i:s', mktime(23, 59, 59, 12, 31, $y));
} else {
    $d = new ParseBGDate();
    if ($d->Parse($_GET['date_from']))
        $date_from = $d->year . '-' . $d->month . '-' . $d->day . ' 00:00:00';
    else
        $date_from = '';
    if ($d->Parse($_GET['date_to']))
        $date_to = $d->year . '-' . $d->month . '-' . $d->day . ' 23:59:59';
    else
        $date_to = '';
    if ($date_from == '' || $date_to == '') {
        $y = date('Y') - 1;
        $date_from = date('Y-m-d H:i:s', mktime(0, 0, 0, 1, 1, $y));
        $date_to = date('Y-m-d H:i:s', mktime(23, 59, 59, 12, 31, $y));
    }
}
$objStats = new StmStats($firm_id, $date_from, $date_to);

$location_type = '';
switch ($f['location_type']) {
    case '0':
        $location_type = 'с.';
        break;
    case '1':
        $location_type = 'гр.';
        break;
    case '2':
        $location_type = 'жк';
        break;
    case '3':
        $location_type = 'кв.';
        break;
    default:
        $location_type = '';
        break;
}
$firm_address = trim($location_type . $f['location_name'] . ((!empty($f['address'])) ? ', ' . $f['address'] : ''));

class Debug_data
{
    public $IDs;
    public $num_diseases;
    public $diseases;
    public $num_workers_with_diseases;
    public $workers_with_diseases;

    public function __construct()
    {
        $this->IDs = array();
        $this->num_diseases = 0;
        $this->diseases = '';
        $this->num_workers_with_diseases = 0;
        $this->workers_with_diseases = '';
    }

    public function getProfessionalDiseases($dbInst = null, $objStats = null, $date_from = '', $date_to = '')
    {
        $this->num_diseases = (!empty($objStats->num_pro_diseases)) ? $objStats->num_pro_diseases : 'Няма предоставени данни';
        $this->num_workers_with_diseases = (!empty($objStats->num_workers_pro_diseases)) ? $objStats->num_workers_pro_diseases : 'Няма предоставени данни';
        $pro_diseases_by_worker = $objStats->pro_diseases_by_worker;
        $this->IDs = $IDs = array_keys($pro_diseases_by_worker);

        $workers = array();
        $workers_pro_diseases = '';
        if (!empty($IDs)) {
            $sql = "SELECT w.`worker_id` AS worker_id, w.`fname` AS fname, w.`lname` AS lname, w.`egn` AS egn, p.`position_name` AS position_name
                    FROM `firm_positions` p
                    LEFT JOIN `firm_struct_map` m ON ( m.`position_id` = p.`position_id` )
                    LEFT JOIN `workers` w ON ( w.`map_id` = m.`map_id` )
                    WHERE w.`worker_id` IN ( " . implode(',', $IDs) . " )
                    ORDER BY w.`fname`, w.`lname`";
            $rows = $dbInst->query($sql);
            if (!empty($rows)) {
                $workers_pro_diseases .= '<ol>';
                foreach ($rows as $row) {
                    $worker_id = $row['worker_id'];
                    $workers[$worker_id] = $row;

                    $caution_ico = (1 < count($pro_diseases_by_worker[$worker_id])) ? '<img src="img/caution.gif" alt="caution" width="11" height="11" /> ' : '';

                    $workers_pro_diseases .= '<li>' . $caution_ico . $row['fname'] . ' ' . $row['lname'] . ' (' . $row['egn'] . ') - ' . $row['position_name'] . ((isset($pro_diseases_by_worker[$worker_id])) ? ' - <span class="grey">' . implode('; ', $pro_diseases_by_worker[$worker_id]) . '</span>' : '') . '</li>';
                }
                $workers_pro_diseases .= '</ol>';
            }
        }
        $this->workers_with_diseases = $workers_pro_diseases;

        $pro_diseases = '';
        if (!empty($IDs)) {
            $ary = array();

            // Брой работещи с регистрирани професионални болести & Брой регистрирани професионални болести
            $sql = "SELECT *
                    FROM `patient_charts`
                    WHERE `worker_id` IN (" . implode(',', $IDs) . ")
                    AND ((julianday(`hospital_date_from`) >= julianday('$date_from'))
                    AND (julianday(`hospital_date_from`) <= julianday('$date_to')))
                    AND `reason_id` IN ( '02', '03' )";
            $rows = $dbInst->query($sql);
            if (!empty($rows)) {
                foreach ($rows as $row) {
                    if (!empty($row['medical_types']) && $medical_types = unserialize($row['medical_types'])) {
                        if (in_array('1', $medical_types)) {
                            $worker_id = $row['worker_id'];
                            if (isset($workers[$worker_id])) {
                                $row['fname'] = $workers[$worker_id]['fname'];
                                $row['lname'] = $workers[$worker_id]['lname'];
                                $row['egn'] = $workers[$worker_id]['egn'];
                                $row['position_name'] = $workers[$worker_id]['position_name'];

                                $chart_num = $row['chart_num'];
                                $hospital_date_from = $row['hospital_date_from'];
                                $mkb_id = $row['mkb_id'];
                                $descr = '';
                                if (!empty($chart_num)) {
                                    $descr .= '№ ' . $chart_num . ' ';
                                }
                                if (!empty($hospital_date_from)) {
                                    $descr .= ' от ' . date('d.m.Y', strtotime($hospital_date_from)) . ' г.';
                                }
                                if (!empty($mkb_id)) {
                                    $descr .= ' (<span class="grey">МКБ ' . $mkb_id . '</span>) ';
                                }

                                $row['popup_title'] = '<span class="patient_chart">Болничен лист</span> ' . $descr;
                                $row['popup'] = 'popup_patient_chart.php?worker_id=' . $worker_id . '&firm_id=' . $row['firm_id'];
                            }
                            $ary[] = $row;
                        }
                    }
                }
            }

            // *** TELKs
            $sql = "SELECT t.*
                    FROM `telks` t
                    WHERE t.`worker_id` IN (" . implode(',', $IDs) . ")
                    AND (
                        ( julianday(t.`telk_date_from`) >= julianday('$date_from') ) AND ( julianday(t.`telk_date_from`) <= julianday('$date_to') )
                        OR
                        ( julianday(t.`telk_date_from`) <= julianday('$date_to') ) AND ( (t.`telk_date_to` = '' OR `telk_date_to` IS NULL) OR julianday(t.`telk_date_to`) >= julianday('$date_from') )
                    )
                    AND t.`mkb_id_4` IS NOT NULL
                    AND t.`mkb_id_4` != ''";

            $rows = $dbInst->query($sql);
            if (!empty($rows)) {
                foreach ($rows as $row) {
                    $worker_id = $row['worker_id'];
                    if (isset($workers[$worker_id])) {
                        $row['fname'] = $workers[$worker_id]['fname'];
                        $row['lname'] = $workers[$worker_id]['lname'];
                        $row['egn'] = $workers[$worker_id]['egn'];
                        $row['position_name'] = $workers[$worker_id]['position_name'];

                        $mkb_id = $row['mkb_id_4'];
                        $telk_num = $row['telk_num'];
                        $telk_date_from = $row['telk_date_from'];
                        $descr = '';
                        if (!empty($telk_num)) {
                            $descr .= '№ ' . $telk_num . ' ';
                        }
                        if (!empty($telk_date_from)) {
                            $descr .= ' от ' . date('d.m.Y', strtotime($telk_date_from)) . ' г.';
                        }
                        if (!empty($mkb_id)) {
                            $descr .= ' (<span class="grey">МКБ ' . $mkb_id . '</span>) ';
                        }

                        $row['popup_title'] = '<span class="telk">ТЕЛК</span> ' . $descr;
                        $row['popup'] = 'popup_telk.php?worker_id=' . $worker_id . '&firm_id=' . $row['firm_id'];
                    }
                    $ary[] = $row;
                }
            }

            if (!empty($ary)) {
                usort($ary, 'sortByFName');

                $pro_diseases .= '<ol>';
                foreach ($ary as $row) {
                    $pro_diseases .= '<li>' . $row['popup_title'] . ': ' . $row['fname'] . ' ' . $row['lname'] . ' (' . $row['egn'] . ') - ' . $row['position_name'] . '</li>';
                }
                $pro_diseases .= '</ol>';
            }
        }
        $this->diseases = $pro_diseases;
    }

    public function getWorkAccidents($dbInst = null, $objStats = null, $date_from = '', $date_to = '')
    {
        $pro_diseases_by_worker = array();
        $IDs = (!empty($objStats->worker_ids)) ? explode(',', $objStats->worker_ids) : array(); //all workers IDs
        $workers = $objStats->workers;

        $pro_diseases = '';
        $workers_pro_diseases = '';
        if (!empty($IDs)) {
            $ary = array();

            // Брой работещи с регистрирани професионални болести & Брой регистрирани професионални болести
            $sql = "SELECT *
                    FROM `patient_charts`
                    WHERE `worker_id` IN (" . implode(',', $IDs) . ")
                    AND ((julianday(`hospital_date_from`) >= julianday('$date_from'))
                    AND (julianday(`hospital_date_from`) <= julianday('$date_to')))
                    AND `reason_id` IN ( '04', '05' )";
            $rows = $dbInst->query($sql);
            if (!empty($rows)) {
                foreach ($rows as $row) {
                    if (!empty($row['medical_types']) && $medical_types = unserialize($row['medical_types'])) {
                        if (in_array('1', $medical_types)) {
                            $worker_id = $row['worker_id'];
                            if (isset($workers[$worker_id])) {
                                $row['fname'] = (isset($workers[$worker_id]['fname'])) ? $workers[$worker_id]['fname'] : '';
                                $row['lname'] = (isset($workers[$worker_id]['lname'])) ? $workers[$worker_id]['lname'] : '';
                                $row['egn'] = $workers[$worker_id]['egn'];
                                $row['position_name'] = $workers[$worker_id]['position_name'];

                                $chart_num = $row['chart_num'];
                                $hospital_date_from = $row['hospital_date_from'];
                                $mkb_id = $row['mkb_id'];
                                $descr = '';
                                if (!empty($chart_num)) {
                                    $descr .= '№ ' . $chart_num . ' ';
                                }
                                if (!empty($hospital_date_from)) {
                                    $descr .= ' от ' . date('d.m.Y', strtotime($hospital_date_from)) . ' г.';
                                }
                                if (!empty($mkb_id)) {
                                    $descr .= ' (<span class="grey">МКБ ' . $mkb_id . '</span>) ';
                                }

                                $row['popup_title'] = '<span class="patient_chart">Болничен лист</span> ' . $descr;
                                $row['popup'] = 'popup_patient_chart.php?worker_id=' . $worker_id . '&firm_id=' . $row['firm_id'];

                                $pro_diseases_by_worker[$worker_id][] = $mkb_id;
                            }
                            $ary[] = $row;
                        }
                    }
                }
            }

            // *** TELKs
            $sql = "SELECT t.*
                    FROM `telks` t
                    WHERE t.`worker_id` IN (" . implode(',', $IDs) . ")
                    AND (
                        ( julianday(t.`telk_date_from`) >= julianday('$date_from') ) AND ( julianday(t.`telk_date_from`) <= julianday('$date_to') )
                        OR
                        ( julianday(t.`telk_date_from`) <= julianday('$date_to') ) AND ( (t.`telk_date_to` = '' OR `telk_date_to` IS NULL) OR julianday(t.`telk_date_to`) >= julianday('$date_from') )
                    )
                    AND t.`mkb_id_3` IS NOT NULL
                    AND t.`mkb_id_3` != ''";
            $rows = $dbInst->query($sql);
            if (!empty($rows)) {
                foreach ($rows as $row) {
                    $worker_id = $row['worker_id'];
                    if (isset($workers[$worker_id])) {
                        $row['fname'] = (isset($workers[$worker_id]['fname'])) ? $workers[$worker_id]['fname'] : '';
                        $row['lname'] = (isset($workers[$worker_id]['lname'])) ? $workers[$worker_id]['lname'] : '';
                        $row['egn'] = $workers[$worker_id]['egn'];
                        $row['position_name'] = $workers[$worker_id]['position_name'];

                        $mkb_id = $row['mkb_id_3'];
                        $telk_num = $row['telk_num'];
                        $telk_date_from = $row['telk_date_from'];
                        $descr = '';
                        if (!empty($telk_num)) {
                            $descr .= '№ ' . $telk_num . ' ';
                        }
                        if (!empty($telk_date_from)) {
                            $descr .= ' от ' . date('d.m.Y', strtotime($telk_date_from)) . ' г.';
                        }
                        if (!empty($mkb_id)) {
                            $descr .= ' (<span class="grey">МКБ ' . $mkb_id . '</span>) ';
                        }

                        $row['popup_title'] = '<span class="telk">ТЕЛК</span> ' . $descr;
                        $row['popup'] = 'popup_telk.php?worker_id=' . $worker_id . '&firm_id=' . $row['firm_id'];
                        $pro_diseases_by_worker[$worker_id][] = $mkb_id;
                    }
                    $ary[] = $row;
                }
            }

            // Workers
            $workers = array();
            $sql = "SELECT w.`worker_id` AS worker_id, w.`fname` AS fname, w.`lname` AS lname, w.`egn` AS egn, p.`position_name` AS position_name
                    FROM `firm_positions` p
                    LEFT JOIN `firm_struct_map` m ON ( m.`position_id` = p.`position_id` )
                    LEFT JOIN `workers` w ON ( w.`map_id` = m.`map_id` )
                    WHERE w.`worker_id` IN ( " . implode(',', array_keys($pro_diseases_by_worker)) . " )
                    ORDER BY w.`fname`, w.`lname`";
            $rows = $dbInst->query($sql);
            if (!empty($rows)) {
                $workers_pro_diseases .= '<ol>';
                foreach ($rows as $row) {
                    $worker_id = $row['worker_id'];
                    $workers[$worker_id] = $row;
                    $this->IDs[$worker_id] = $worker_id;

                    $caution_ico = (1 < count($pro_diseases_by_worker[$worker_id])) ? '<img src="img/caution.gif" alt="caution" width="11" height="11" /> ' : '';

                    $workers_pro_diseases .= '<li>' . $caution_ico . $row['fname'] . ' ' . $row['lname'] . ' (' . $row['egn'] . ') - ' . $row['position_name'] . ((isset($pro_diseases_by_worker[$worker_id])) ? ' - <span class="grey">' . implode('; ', $pro_diseases_by_worker[$worker_id]) . '</span>' : '') . '</li>';
                }
                $workers_pro_diseases .= '</ol>';
            }
            $this->num_workers_with_diseases = count($pro_diseases_by_worker);
            if (empty($this->num_workers_with_diseases)) {
                $this->num_workers_with_diseases = 'Няма предоставени данни';
            }
            $this->workers_with_diseases = $workers_pro_diseases;

            // Diseases
            if (!empty($ary)) {
                foreach ($ary as $i => $row) {
                    $worker_id = $row['worker_id'];
                    $ary[$i]['fname'] = $workers[$worker_id]['fname'];
                    $ary[$i]['lname'] = $workers[$worker_id]['lname'];
                }
                usort($ary, 'sortByFName');

                $pro_diseases .= '<ol>';
                foreach ($ary as $row) {
                    $pro_diseases .= '<li>' . $row['popup_title'] . ': ' . $row['fname'] . ' ' . $row['lname'] . ' (' . $row['egn'] . ') - ' . $row['position_name'] . '</li>';
                }
                $pro_diseases .= '</ol>';
            }
            $this->num_diseases = count($ary);
            if (empty($this->num_diseases)) {
                $this->num_diseases = 'Няма предоставени данни';
            }
            $this->diseases = $pro_diseases;
        }
    }

}

function sortByFName($a, $b)
{
    //return $a['fname'] - $b['fname'];
    return strcmp($a['fname'], $b['fname']); //
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title><?= SITE_NAME ?></title>
    <style type="text/css">
        .grey {
            background-color: #EEEEEE;
            padding: 2px;
        }

        .patient_chart {
            background-color: #d8e6fc;
            padding: 2px;
        }

        .telk {
            background-color: #86CCED;
            padding: 2px;
        }
    </style>
</head>
<body>
<div id="contentinner">
    <h1>Извлечение от обобщения анализ за <?= $dbInst->extractYear($date_from, $date_to) ?> г. на</h1>

    <h2><?= ((isset($f['firm_name'])) ? HTMLFormat($f['firm_name']) : '') ?></h2>
    <?php if (!empty($firm_address)) { ?><h3><?= HTMLFormat($firm_address) ?></h3><?php } ?>
    <p><em>от <?= date('d.m.Y', strtotime($date_from)) ?> г. до <?= date('d.m.Y', strtotime($date_to)) ?> г.</em></p>
    <hr/>

    <p>
        Брой работили целогодишно: <strong><?= $objStats->anual_workers ?></strong> (<?= $objStats->anual_men ?> мъже и <?= $objStats->anual_women ?> жени)<br/>
        Брой постъпили: <strong><?= $objStats->joined_workers ?></strong> (<?= $objStats->joined_men ?> мъже и <?= $objStats->joined_women ?> жени)<br/>
        Брой напуснали: <strong><?= $objStats->retired_workers ?></strong> (<?= $objStats->retired_men ?> мъже и <?= $objStats->retired_women ?> жени)<br/>
        <table border="0" cellpadding="2" cellspacing="0" style="text-wrap: none;">
            <tbody>
                <tr>
                    <td rowspan="2">Средно-списъчен брой работещи = Брой работили целогодишно + </td>
                    <td style="border-bottom:1px solid #000000;"> (Брой постъпили + Брой напуснали) </td>
                    <td rowspan="2"> = <?= $objStats->anual_workers ?> + </td>
                    <td style="border-bottom:1px solid #000000;"> (<?= $objStats->joined_workers ?> + <?= $objStats->retired_workers ?>) </td>
                    <td rowspan="2"> = <?= $objStats->avg_workers ?></td>
                </tr>
                <tr>
                    <td align="center">2</td>
                    <td align="center">2</td>
                </tr>
            </tbody>
        </table>
        <p><em><img src="img/caution.gif" alt="caution" width="11" height="11"/> Закръгляваме получените числа за мъже и жени, за да не се показва дробен остатък 0.5</em></p>
        <?php $avg_men = round($objStats->avg_men, 0); ?>
        <table border="0" cellpadding="2" cellspacing="0" style="text-wrap: none;">
            <tbody>
                <tr>
                    <td rowspan="2">Средно-списъчен брой мъже = Брой мъже работили целогодишно + </td>
                    <td style="border-bottom:1px solid #000000;"> (Брой постъпили мъже + Брой напуснали мъже) </td>
                </tr>
                <tr>
                    <td align="center">2</td>
                </tr>
            </tbody>
        </table>
        <table>
            <tbody>
                <tr>
                    <td rowspan="2">Средно-списъчен брой мъже = </td>
                    <td rowspan="2"><?= $objStats->anual_men ?> + </td>
                    <td style="border-bottom:1px solid #000000;"> (<?= $objStats->joined_men ?> + <?= $objStats->retired_men ?>) </td>
                    <td rowspan="2"> = <?= $objStats->avg_men ?> &asymp; <strong><?= $avg_men ?></strong></td>
                </tr>
                <tr>
                    <td align="center">2</td>
                </tr>
            </tbody>
        </table>
        <?php $avg_women = round($objStats->avg_women, 0); ?>
        <table border="0" cellpadding="2" cellspacing="0" style="text-wrap: none;">
            <tbody>
            <tr>
                <td rowspan="2">Средно-списъчен брой жени = Брой жени работили целогодишно + </td>
                <td style="border-bottom:1px solid #000000;"> (Брой постъпили жени + Брой напуснали жени) </td>
            </tr>
            <tr>
                <td align="center">2</td>
            </tr>
            </tbody>
        </table>
        <table>
            <tbody>
            <tr>
                <td rowspan="2">Средно-списъчен брой жени = </td>
                <td rowspan="2"><?= $objStats->anual_women ?> + </td>
                <td style="border-bottom:1px solid #000000;"> (<?= $objStats->joined_women ?> + <?= $objStats->retired_women ?>) </td>
                <td rowspan="2"> = <?= $objStats->avg_women ?> &asymp; <strong><?= $avg_women ?></strong></td>
            </tr>
            <tr>
                <td align="center">2</td>
            </tr>
            </tbody>
        </table>
        <p>
            <strong>Корекция на средно-списъчния брой работещи</strong><br />
            Средно-списъчен брой работещи = Средно-списъчен брой мъже + Средно-списъчен брой жени = <?= $avg_men ?> + <?= $avg_women ?> = <strong><?= ($avg_men + $avg_women) ?></strong>
        </p>
    </p>
    <hr />
    <?php
    $dbg = new Debug_data();
    $dbg->getProfessionalDiseases($dbInst, $objStats, $date_from, $date_to);
    ?>
    <h3>Професионални болести</h3>

    <p><em>(от първични болнични листове - причина 02 или 03 и от ТЕЛК решения - при попълнена графа &ldquo;Професионално заболяване&rdquo;)</em>
    </p>

    <p>
        <?php if (!empty($dbg->IDs)) { ?>
            <a href="firm_info.php?firm_id=<?= $firm_id ?>&tab=workers&IDs=<?= implode(',', $dbg->IDs) ?>&chkRetired=1"
               target="_blank">
                Брой регистрирани професионални болести: <strong><?= $dbg->num_diseases ?></strong>.
            </a>
        <?php } else { ?>
            Брой регистрирани професионални болести: <strong><?= $dbg->num_diseases ?></strong>.
        <?php } ?>
    </p>
    <?= $dbg->diseases ?>
    <p>
        <?php if (!empty($dbg->IDs)) { ?>
            <a href="firm_info.php?firm_id=<?= $firm_id ?>&tab=workers&IDs=<?= implode(',', $dbg->IDs) ?>&chkRetired=1"
               target="_blank">
                Брой работещи с регистрирани професионални болести:
                <strong><?= $dbg->num_workers_with_diseases ?></strong>.
            </a>
        <?php } else { ?>
            Брой работещи с регистрирани професионални болести: <strong><?= $dbg->num_workers_with_diseases ?></strong>.
        <?php } ?>
    </p>
    <?= $dbg->workers_with_diseases ?>
    <hr/>

    <?php
    $dbg = new Debug_data();
    $dbg->getWorkAccidents($dbInst, $objStats, $date_from, $date_to);
    ?>
    <h3>Трудови злополуки</h3>
    <p><em>(от първични болнични листове - причина 04 или 05 и от ТЕЛК решения - при попълнена графа &ldquo;Трудова
            злополука&rdquo;)</em></p>
    <p>
        <?php if (!empty($dbg->IDs)) { ?>
            <a href="firm_info.php?firm_id=<?= $firm_id ?>&tab=workers&IDs=<?= implode(',', $dbg->IDs) ?>&chkRetired=1"
               target="_blank">
                Брой регистрирани трудови злополуки: <strong><?= $dbg->num_diseases ?></strong>.
            </a>
        <?php } else { ?>
            Брой регистрирани трудови злополуки: <strong><?= $dbg->num_diseases ?></strong>.
        <?php } ?>
    </p>
    <?= $dbg->diseases ?>
    <p>
        <?php if (!empty($dbg->IDs)) { ?>
            <a href="firm_info.php?firm_id=<?= $firm_id ?>&tab=workers&IDs=<?= implode(',', $dbg->IDs) ?>&chkRetired=1"
               target="_blank">
                Брой работещи с регистрирани трудови злополуки: <strong><?= $dbg->num_workers_with_diseases ?></strong>.
            </a>
        <?php } else { ?>
            Брой работещи с регистрирани трудови злополуки: <strong><?= $dbg->num_workers_with_diseases ?></strong>.
        <?php } ?>
    </p>
    <?= $dbg->workers_with_diseases ?>
</div>
</body>
</html>
