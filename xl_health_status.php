<?php
// http://localhost/stm2008/viamed/xl_health_status.php?firm_id=93
require('includes.php');

$firm_id = (isset($_GET['firm_id']) && is_numeric($_GET['firm_id'])) ? intval($_GET['firm_id']) : 0;
$f = $dbInst->getFirmInfo($firm_id);
if(!$f) {
	die('Липсва индентификатор на фирмата!');
}
$firm_name = str_replace(' ', '_', $f['firm_name']);
$firm_name = str_replace('"', '', $firm_name);
$firm_name = str_replace('\'', '', $firm_name);
$firm_name = str_replace('”', '', $firm_name);
$firm_name = str_replace('„', '', $firm_name);
$firm_name = str_replace('_-_', '_', $firm_name);

require_once("cyrlat.class.php");
$cyrlat = new CyrLat;
$filename = 'Zdraven_Status_'.$cyrlat->cyr2lat($firm_name).'.xls';

$chkY = date('Y');
$chkY_0 = $chkY - 1;
$chkY_1 = $chkY_0 - 1;
$chkY_2 = $chkY_1 - 1;
$chkY_3 = $chkY_2 - 1;
$eol = '[newline]';

$data = array();
$sql = "SELECT w.worker_id AS worker_id, w.fname AS fname, w.sname AS sname, w.lname AS lname, w.egn AS egn,
		strftime('%d.%m.%Y', w.birth_date, 'localtime') AS birth_date2,
		strftime('%d.%m.%Y', w.date_curr_position_start, 'localtime') AS date_curr_position_start2,
		s.subdivision_name AS subdivision_name, p.wplace_name AS wplace_name, i.position_name AS position_name,
		
		case
	        when date(w.birth_date, '+' ||
	            (strftime('%Y', 'now') - strftime('%Y', w.birth_date)) ||
	            ' years') <= date('now')
	        then strftime('%Y', 'now') - strftime('%Y', w.birth_date)
	        else strftime('%Y', 'now') - strftime('%Y', w.birth_date) - 1
	    end
	    as age
	    
		FROM workers w
		LEFT JOIN firm_struct_map m ON (m.map_id = w.map_id)
		LEFT JOIN subdivisions s ON (s.subdivision_id = m.subdivision_id)
		LEFT JOIN firm_positions i ON (i.position_id = m.position_id)
		LEFT JOIN work_places p ON (p.wplace_id = m.wplace_id)
		WHERE w.firm_id = $firm_id 
		AND w.is_active = '1' 
		AND w.date_retired = ''
		GROUP BY w.worker_id
		ORDER BY w.fname, w.sname, w.lname, w.egn, w.worker_id";
$rows = $dbInst->query($sql);
$wIDs = array();
if(!empty($rows)) {
	foreach ($rows as $row) {
		foreach ($row as $key => $val) {
			if(is_numeric($key)) {
				unset($row[$key]);
			}
		}
		$row['names'] = trim($row['fname'].' '.$row['sname'].' '.$row['lname']);
		$row['age'] = (!empty($row['age'])) ? $row['age'].' г.' : '';
		$row['prchkps'] = '';
		$row['fanamnesis'] = '';
		$row['pcharts'] = '';
		$row['prophylactic_'.$chkY_3] = '';
		$row['prophylactic_'.$chkY_2] = '';
		$row['prophylactic_'.$chkY_1] = '';
		$row['prophylactic_'.$chkY_0] = '';
		$row['prophylactic_'.$chkY] = '';
		$row['telks'] = '';
		$wIDs[] = $row['worker_id'];
		$data[$row['worker_id']] = $row;
	}
}

// МЗР - Медицинско за започване на работа
$aCheckups = array();
if(!empty($wIDs)) {
	$sql = "SELECT p.worker_id AS worker_id, p.precheckup_id AS precheckup_id, strftime('%d.%m.%Y', p.prchk_date, 'localtime') AS prchk_date2,
			s.SpecialistName AS SpecialistName , c.conclusion AS conclusion
			FROM medical_precheckups_doctors2 c
			LEFT JOIN medical_precheckups p ON ( p.precheckup_id = c.precheckup_id )
			LEFT JOIN Specialists s ON ( s.SpecialistID = c.SpecialistID )
			WHERE p.worker_id IN (".implode(',', $wIDs).")
			ORDER BY p.prchk_date, p.precheckup_id, s.SpecialistName, s.SpecialistID";
	$rows = $dbInst->query($sql);
	if(!empty($rows)) {
		foreach ($rows as $row) {
			foreach ($row as $key => $val) {
				if(is_numeric($key)) {
					unset($row[$key]);
				}
			}
			$aCheckups[$row['worker_id']][$row['precheckup_id']][] = $row;
		}

		foreach ($aCheckups as $worker_id => $aCheckup) {
			$j = 0;
			$aOlder = array();
			foreach ($aCheckup as $precheckup_id => $lines) {
				$ary = array();
				foreach ($lines as $i => $line) {
					if(!$i && !empty($line['prchk_date2'])) {
						$ary[] = $line['prchk_date2'];
					}
					if($j && !empty($line['prchk_date2'])) {
						$aOlder[] = $line['prchk_date2'];
					}
					$ary[] = $line['SpecialistName'].': '.$line['conclusion'];
				}
				$j++;
			}
			if(!empty($aOlder)) {
				$ary[] = '('.implode('; ', $aOlder).')';
			}
			$data[$worker_id]['prchkps'] = implode($eol, $ary);
		}
	}
}

// фам. заб. (Фамилна анамнеза)
$aCheckups = array();
if(!empty($wIDs)) {
	$sql = "SELECT c.worker_id AS worker_id, c.checkup_id AS checkup_id,
			strftime('%d.%m.%Y', c.checkup_date, 'localtime') AS checkup_date2, w.mkb_id AS mkb_id
			FROM family_weights w
			LEFT JOIN medical_checkups c ON (c.checkup_id = w.checkup_id)
			WHERE c.worker_id IN (".implode(',', $wIDs).")
			ORDER BY c.checkup_date DESC, c.checkup_id DESC, w.family_weight_id";
	$rows = $dbInst->query($sql);
	if(!empty($rows)) {
		foreach ($rows as $row) {
			foreach ($row as $key => $val) {
				if(is_numeric($key)) {
					unset($row[$key]);
				}
			}
			$aCheckups[$row['worker_id']][$row['checkup_id']][] = $row;
		}

		foreach ($aCheckups as $worker_id => $aCheckup) {
			$ary = array();
			foreach ($aCheckup as $checkup_id => $lines) {
				$aMkb = array();
				$checkup_date = '';
				foreach ($lines as $i => $line) {
					if(!$i) {
						$checkup_date = $line['checkup_date2'];
					}
					$aMkb[$line['mkb_id']] = $line['mkb_id'];
				}
				$ary[] = $checkup_date.' - '.implode('; ', $aMkb);
			}
			$data[$worker_id]['fanamnesis'] = implode($eol, $ary);
		}
	}
}

// ВНР
$aPCharts = array();
if(!empty($wIDs)) {
	$sql = "SELECT worker_id, hospital_date_from, mkb_id, days_off, reason_id
			FROM patient_charts 
			WHERE worker_id IN (".implode(',', $wIDs).")
			ORDER BY hospital_date_from, mkb_id, days_off";
	$rows = $dbInst->query($sql);
	if(!empty($rows)) {
		foreach ($rows as $row) {
			foreach ($row as $key => $val) {
				if(is_numeric($key)) {
					unset($row[$key]);
				}
			}
			$aPCharts[$row['worker_id']][] = $row;
		}

		foreach ($aPCharts as $worker_id => $charts) {
			$ary = array();
			foreach ($charts as $i => $chart) {
				$ary[] = ($i + 1).'. '.substr($chart['hospital_date_from'], 2, 2).' /'.$chart['mkb_id'].' /'.$chart['days_off'].' /'.$chart['reason_id'];
			}
			$data[$worker_id]['pcharts'] = implode($eol, $ary);
		}
		unset($aPCharts);
	}
}

// Периодични прегледи
$aCheckups = array();
if(!empty($wIDs)) {
	$sql = "SELECT m.worker_id AS worker_id, strftime('%d.%m.%Y', m.checkup_date, 'localtime') AS checkup_date2,
			s.SpecialistName AS SpecialistName, c.conclusion AS conclusion, c.checkup_id AS checkup_id, c.SpecialistID AS SpecialistID
			FROM medical_checkups_doctors2 c
			LEFT JOIN medical_checkups m ON ( m.checkup_id = c.checkup_id )
			LEFT JOIN Specialists s ON ( s.SpecialistID = c.SpecialistID )
			WHERE m.checkup_date >= '$chkY_3-01-01 00:00:00' 
			AND m.checkup_date <= '$chkY-12-31 23:59:59' 
			AND m.worker_id IN (".implode(',', $wIDs).")
			ORDER BY s.SpecialistName, s.SpecialistID";
	$rows = $dbInst->query($sql);
	if(!empty($rows)) {
		foreach ($rows as $row) {
			foreach ($row as $key => $val) {
				if(is_numeric($key)) {
					unset($row[$key]);
				}
			}
			$yy = substr($row['checkup_date2'], 6, 4);
			$aCheckups[$row['worker_id']][$yy][][$row['checkup_id'].'_'.$row['SpecialistID']][] = $row;
		}

		foreach ($aCheckups as $worker_id => $years) {
			foreach ($years as $yy => $aCheckup) {
				$ary[$yy] = array();
				$j = 0;
				foreach ($aCheckup as $lines) {
					foreach ($lines as $flds) {
						foreach ($flds as $i => $line) {
							if(!$j && !empty($line['checkup_date2'])) {
								$ary[$yy][] = $line['checkup_date2'];
							}

							if(!empty($line['conclusion'])) {
								$SpecialistName = $dbInst->my_mb_ucfirst(HTMLFormat($line['SpecialistName']));
								$SpecialistName = trim(mb_substr($SpecialistName, 0, 8));
								$SpecialistName = str_replace('(', '', $SpecialistName);
								if('Инте' == $SpecialistName) { $SpecialistName = 'Инт.'; }
								elseif('Офта' == $SpecialistName) { $SpecialistName = 'Офт.'; }
								elseif('Хиру' == $SpecialistName) { $SpecialistName = 'Хир.'; }
								$ary[$yy][] = ($j + 1).'. '.$SpecialistName.': '.$line['conclusion'];
								$j++;
							}
						}
					}
				}
				$data[$worker_id]['prophylactic_'.$yy] = implode($eol, $ary[$yy]);
			}
		}
		unset($ary);

	}
}

// ТЕЛК
$aTelks = array();
if(!empty($wIDs)) {
	$sql = "SELECT worker_id, percent_inv, mkb_id_1, mkb_id_2, mkb_id_3, mkb_id_4, telk_date_from, telk_date_to
			FROM telks 
			WHERE worker_id IN (".implode(',', $wIDs).")
			ORDER BY telk_date_from, telk_duration";
	$rows = $dbInst->query($sql);
	if(!empty($rows)) {
		foreach ($rows as $row) {
			foreach ($row as $key => $val) {
				if(is_numeric($key)) {
					unset($row[$key]);
				}
			}
			$aTelks[$row['worker_id']][] = $row;
		}

		foreach ($aTelks as $worker_id => $telks) {
			$ary = array();
			foreach ($telks as $i => $telk) {
				$line  = ($i + 1).'.';
				if(!empty($telk['telk_date_from'])) {
					list($yy, $mm, $dd) = explode('-', substr($telk['telk_date_from'], 0, 10));
					$line .= ' '.$dd.'.'.$mm.'.'.substr($yy, 2, 2);
				}
				if(!empty($telk['telk_date_to'])) {
					list($yy, $mm, $dd) = explode('-', substr($telk['telk_date_to'], 0, 10));
					$line .= ' - '.$dd.'.'.$mm.'.'.substr($yy, 2, 2);
				}
				if(!empty($telk['percent_inv'])) { $line .= ' / '.$telk['percent_inv'].'%'; }
				$mkbs = array();
				if(!empty($telk['mkb_id_1'])) { $mkbs[] = $telk['mkb_id_1']; }
				if(!empty($telk['mkb_id_2'])) { $mkbs[] = $telk['mkb_id_2'].' (01)'; }
				if(!empty($telk['mkb_id_3'])) { $mkbs[] = $telk['mkb_id_3'].' (04)'; }
				if(!empty($telk['mkb_id_4'])) { $mkbs[] = $telk['mkb_id_4'].' (02)'; }
				if(!empty($mkbs)) { $line .= ' / '.implode(';', $mkbs); }
				$ary[] = $line;
			}
			$data[$worker_id]['telks'] = implode($eol, $ary);
		}
	}
}

if(!empty($data)) {
	$i = 0;
	foreach ($data as $worker_id => $row) {
		$row['num'] = ++$i;
		foreach ($row as $key => $val) {
			// http://social.msdn.microsoft.com/Forums/en-US/78942116-bc1d-44e7-84b2-551e2b97f49f/encoding-or-escaping-newline-characters-using-xmldocument-and-xmlwriter?forum=xmlandnetfx
			$row[$key] = (empty($val)) ? '--' : str_replace('[newline]', '&#10;', htmlspecialchars($val));//preserve the new-line character in Excel
		}
		$data[$worker_id] = $row;
	}
}

$ExpandedRowCount = count($data) + 5;
$author = htmlspecialchars($_SESSION['sess_fname'].' '.$_SESSION['sess_lname']);
$firm_name = htmlspecialchars($f['firm_name']);
$location_name = htmlspecialchars($f['location_name']);

header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Pragma: no-cache");
header('Content-Description: File Transfer');
header("Content-type: application/vnd.ms-excel;");
header("Content-Disposition: attachment; filename=$filename");

echo '<?xml version="1.0"?>';
echo '<?mso-application progid="Excel.Sheet"?>';
?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
  <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
    <Author><?=$author?></Author>
    <LastAuthor><?=$author?></LastAuthor>
    <LastPrinted><?=date('Y-m-d')?>T12:49:17Z</LastPrinted>
    <Created><?=date('Y-m-d')?>T08:50:36Z</Created>
    <LastSaved><?=date('Y-m-d')?>T08:49:56Z</LastSaved>
    <Version>14.00</Version>
  </DocumentProperties>
  <OfficeDocumentSettings xmlns="urn:schemas-microsoft-com:office:office">
    <AllowPNG/>
  </OfficeDocumentSettings>
  <ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">
    <WindowHeight>5490</WindowHeight>
    <WindowWidth>9660</WindowWidth>
    <WindowTopX>0</WindowTopX>
    <WindowTopY>0</WindowTopY>
    <ProtectStructure>False</ProtectStructure>
    <ProtectWindows>False</ProtectWindows>
  </ExcelWorkbook>
  <Styles>
    <Style ss:ID="Default" ss:Name="Normal">
      <Alignment ss:Vertical="Bottom"/>
      <Borders/>
      <Font ss:FontName="Arial"/>
      <Interior/>
      <NumberFormat/>
      <Protection/>
    </Style>
    <Style ss:ID="m73121056">
      <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>
      <Borders>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
      </Borders>
      <Font ss:FontName="Arial" x:CharSet="204" x:Family="Swiss" ss:Bold="1"/>
      <Protection ss:Protected="0"/>
    </Style>
    <Style ss:ID="m73121076">
      <Alignment ss:Horizontal="Center" ss:Vertical="Bottom"/>
      <Borders>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
      </Borders>
      <Font ss:FontName="Arial" ss:Bold="1"/>
      <Protection ss:Protected="0"/>
    </Style>
    <Style ss:ID="m73121096">
      <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
      <Borders>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
      </Borders>
      <Font ss:FontName="Arial" ss:Bold="1"/>
      <Protection ss:Protected="0"/>
    </Style>
    <Style ss:ID="m73121116">
      <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
      <Borders>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
      </Borders>
      <Font ss:FontName="Arial" x:CharSet="204" x:Family="Swiss" ss:Bold="1"/>
      <Protection ss:Protected="0"/>
    </Style>
    <Style ss:ID="m73121136">
      <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>
      <Borders>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
      </Borders>
      <Font ss:FontName="Arial" x:CharSet="204" x:Family="Swiss" ss:Bold="1"/>
      <Protection ss:Protected="0"/>
    </Style>
    <Style ss:ID="m73121156">
      <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>
      <Borders>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
      </Borders>
      <Font ss:FontName="Arial" x:CharSet="204" x:Family="Swiss" ss:Bold="1"/>
      <Protection ss:Protected="0"/>
    </Style>
    <Style ss:ID="m73121176">
      <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>
      <Borders>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
      </Borders>
      <Font ss:FontName="Arial" x:CharSet="204" x:Family="Swiss" ss:Bold="1"/>
      <Protection ss:Protected="0"/>
    </Style>
    <Style ss:ID="m73120384">
      <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
      <Borders>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
      </Borders>
      <Font ss:FontName="Arial" ss:Bold="1"/>
      <Protection ss:Protected="0"/>
    </Style>
    <Style ss:ID="m73120404">
      <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
      <Borders>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
      </Borders>
      <Font ss:FontName="Arial" ss:Bold="1"/>
      <Protection ss:Protected="0"/>
    </Style>
    <Style ss:ID="m73120424">
      <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
      <Borders>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
      </Borders>
      <Font ss:FontName="Arial" ss:Bold="1"/>
      <Protection ss:Protected="0"/>
    </Style>
    <Style ss:ID="m73120444">
      <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
      <Borders>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
      </Borders>
      <Font ss:FontName="Arial" ss:Bold="1"/>
      <Protection ss:Protected="0"/>
    </Style>
    <Style ss:ID="m73120464">
      <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
      <Borders>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
      </Borders>
      <Font ss:FontName="Arial" ss:Bold="1"/>
      <Protection ss:Protected="0"/>
    </Style>
    <Style ss:ID="m73120484">
      <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
      <Borders>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
      </Borders>
      <Font ss:FontName="Arial" ss:Bold="1"/>
      <Protection ss:Protected="0"/>
    </Style>
    <Style ss:ID="s62">
      <Protection ss:Protected="0"/>
    </Style>
    <Style ss:ID="s63">
      <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
      <Font ss:FontName="Arial" x:CharSet="204" x:Family="Swiss" ss:Size="18"/>
      <Protection ss:Protected="0"/>
    </Style>
    <Style ss:ID="s64">
      <Font ss:FontName="Arial" x:CharSet="204" x:Family="Swiss" ss:Size="12"/>
      <Protection ss:Protected="0"/>
    </Style>
    <Style ss:ID="s65">
      <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
      <Font ss:FontName="Arial" x:CharSet="204" x:Family="Swiss"/>
      <Protection ss:Protected="0"/>
    </Style>
    <Style ss:ID="s66">
      <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
      <Borders>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
      </Borders>
      <Font ss:FontName="Arial" ss:Bold="1"/>
      <Protection ss:Protected="0"/>
    </Style>
    <Style ss:ID="s93">
      <Alignment ss:Horizontal="Left" ss:Vertical="Center" ss:WrapText="1"/>
      <Borders>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
      </Borders>
      <Protection ss:Protected="0"/>
    </Style>
  </Styles>
  <Worksheet ss:Name="Zdraven Status">
    <Table ss:ExpandedColumnCount="17" ss:ExpandedRowCount="<?=$ExpandedRowCount?>" x:FullColumns="1" x:FullRows="1" ss:StyleID="s62">
      <Column ss:StyleID="s62" ss:Width="15.75"/>
      <Column ss:StyleID="s62" ss:Width="89.25"/>
      <Column ss:StyleID="s62" ss:Width="25.5"/>
      <Column ss:StyleID="s62" ss:Width="84"/>
      <Column ss:StyleID="s62" ss:Width="78.75"/>
      <Column ss:StyleID="s62" ss:Width="97.5"/>
      <Column ss:StyleID="s62" ss:Width="53.25" ss:Span="3"/>
      <Column ss:Index="11" ss:StyleID="s62" ss:Width="87"/>
      <Column ss:StyleID="s62" ss:Width="104.25" ss:Span="2"/>
      <Column ss:Index="15" ss:StyleID="s62" ss:Width="108"/>
      <Column ss:StyleID="s62" ss:Width="60.75"/>
      <Column ss:StyleID="s62" ss:Width="63"/>
      <Row ss:AutoFitHeight="0" ss:Height="23.25">
        <Cell ss:Index="7" ss:StyleID="s63">
          <Data ss:Type="String">Здравен статус</Data>
        </Cell>
        <Cell ss:StyleID="s63"/>
        <Cell ss:StyleID="s63"/>
        <Cell ss:StyleID="s63"/>
      </Row>
      <Row ss:AutoFitHeight="0" ss:Height="15">
        <Cell ss:Index="6" ss:StyleID="s64"/>
        <Cell ss:StyleID="s65">
          <Data ss:Type="String">на работещите в <?=$firm_name?> – <?=$location_name?></Data>
        </Cell>
        <Cell ss:StyleID="s65"/>
        <Cell ss:StyleID="s65"/>
        <Cell ss:StyleID="s65"/>
      </Row>
      <Row ss:Index="4">
        <Cell ss:MergeDown="1" ss:StyleID="m73120384">
          <Data ss:Type="String">№</Data>
        </Cell>
        <Cell ss:MergeDown="1" ss:StyleID="m73120404">
          <Data ss:Type="String">Име</Data>
        </Cell>
        <Cell ss:MergeDown="1" ss:StyleID="m73120424">
          <Data ss:Type="String">Год.</Data>
        </Cell>
        <Cell ss:MergeDown="1" ss:StyleID="m73120444">
          <Data ss:Type="String">Подразделение</Data>
        </Cell>
        <Cell ss:MergeDown="1" ss:StyleID="m73120464">
          <Data ss:Type="String">Работно място</Data>
        </Cell>
        <Cell ss:MergeDown="1" ss:StyleID="m73120484">
          <Data ss:Type="String">Длъжност</Data>
        </Cell>
        <Cell ss:MergeDown="1" ss:StyleID="m73121136">
          <Data ss:Type="String">Дата на &#10;назнач.</Data>
        </Cell>
        <Cell ss:MergeDown="1" ss:StyleID="m73121156">
          <Data ss:Type="String">МЗР</Data>
        </Cell>
        <Cell ss:MergeDown="1" ss:StyleID="m73121176">
          <Data ss:Type="String">Фамилна анамнеза</Data>
        </Cell>
        <Cell ss:MergeDown="1" ss:StyleID="m73121056">
          <Data ss:Type="String">ВНР</Data>
        </Cell>
        <Cell ss:MergeAcross="3" ss:StyleID="m73121076">
          <Data ss:Type="String">Периодични прегледи</Data>
        </Cell>
        <Cell ss:MergeDown="1" ss:StyleID="m73121096">
          <Data ss:Type="String">ТЕЛК</Data>
        </Cell>
        <Cell ss:MergeAcross="1" ss:StyleID="m73121116">
          <Data ss:Type="String">Периодични пр. <?=$chkY?></Data>
        </Cell>
      </Row>
      <Row>
        <Cell ss:Index="11" ss:StyleID="s66">
          <Data ss:Type="Number"><?=$chkY_3?></Data>
        </Cell>
        <Cell ss:StyleID="s66">
          <Data ss:Type="Number"><?=$chkY_2?></Data>
        </Cell>
        <Cell ss:StyleID="s66">
          <Data ss:Type="Number"><?=$chkY_1?></Data>
        </Cell>
        <Cell ss:StyleID="s66">
          <Data ss:Type="Number"><?=$chkY_0?></Data>
        </Cell>
        <Cell ss:Index="16" ss:StyleID="s66">
          <Data ss:Type="String">Преминали</Data>
        </Cell>
        <Cell ss:StyleID="s66">
          <Data ss:Type="String">Подлежащи</Data>
        </Cell>
      </Row>
      <?php if(!empty($data)) { ?>
      	<?php foreach ($data as $row) { ?>
      <Row ss:AutoFitHeight="0" ss:Height="127.5">
        <Cell ss:StyleID="s93">
          <Data ss:Type="Number"><?=$row['num']?></Data>
        </Cell>
        <Cell ss:StyleID="s93">
          <Data ss:Type="String"><?=$row['names']?></Data>
        </Cell>
        <Cell ss:StyleID="s93">
          <Data ss:Type="String"><?=$row['age']?></Data>
        </Cell>
        <Cell ss:StyleID="s93">
          <Data ss:Type="String"><?=$row['subdivision_name']?></Data>
        </Cell>
        <Cell ss:StyleID="s93">
          <Data ss:Type="String"><?=$row['wplace_name']?></Data>
        </Cell>
        <Cell ss:StyleID="s93">
          <Data ss:Type="String"><?=$row['position_name']?></Data>
        </Cell>
        <Cell ss:StyleID="s93">
          <Data ss:Type="String"><?=$row['date_curr_position_start2']?></Data>
        </Cell>
        <Cell ss:StyleID="s93">
          <Data ss:Type="String"><?=$row['prchkps']?></Data>
        </Cell>
        <Cell ss:StyleID="s93">
          <Data ss:Type="String"><?=$row['fanamnesis']?></Data>
        </Cell>
        <Cell ss:StyleID="s93">
          <Data ss:Type="String"><?=$row['pcharts']?></Data>
        </Cell>
        <Cell ss:StyleID="s93">
          <Data ss:Type="String"><?=$row['prophylactic_'.$chkY_3]?></Data>
        </Cell>
        <Cell ss:StyleID="s93">
          <Data ss:Type="String"><?=$row['prophylactic_'.$chkY_2]?></Data>
        </Cell>
        <Cell ss:StyleID="s93">
          <Data ss:Type="String"><?=$row['prophylactic_'.$chkY_1]?></Data>
        </Cell>
        <Cell ss:StyleID="s93">
          <Data ss:Type="String"><?=$row['prophylactic_'.$chkY_0]?></Data>
        </Cell>
        <Cell ss:StyleID="s93">
          <Data ss:Type="String"><?=$row['telks']?></Data>
        </Cell>
        <Cell ss:StyleID="s93">
          <Data ss:Type="String"><?=$row['prophylactic_'.$chkY]?></Data>
        </Cell>
        <Cell ss:StyleID="s93"/>
      </Row>
      	<?php } ?>
      <?php } ?>
    </Table>
    <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
      <PageSetup>
        <Layout x:Orientation="Landscape"/>
      </PageSetup>
      <Print>
        <FitHeight>0</FitHeight>
        <ValidPrinterInfo/>
        <PaperSizeIndex>9</PaperSizeIndex>
        <HorizontalResolution>600</HorizontalResolution>
        <VerticalResolution>600</VerticalResolution>
        <Gridlines/>
      </Print>
      <Selected/>
      <Panes>
        <Pane>
          <Number>3</Number>
          <ActiveRow>3</ActiveRow>
          <RangeSelection>R4C1:R5C1</RangeSelection>
        </Pane>
      </Panes>
      <ProtectObjects>False</ProtectObjects>
      <ProtectScenarios>False</ProtectScenarios>
    </WorksheetOptions>
  </Worksheet>
</Workbook>
