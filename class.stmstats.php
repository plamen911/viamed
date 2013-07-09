<?

class StmStats extends SqliteDB {
	var $firm_id = 0;
	var $date_from = '';
	var $date_to = '';
	var $subdivision_id = 0;
	var $wplace_id = 0;

	var $anual_workers = 0;		// anual_workers
	var $joined_workers = 0;	// joined_workers
	var $retired_workers = 0;	// retired_workers
	var $anual_men = 0;			// anual_men
	var $joined_men = 0;		// joined_men
	var $retired_men = 0;		// retired_men
	var $anual_women = 0;		// anual_women
	var $joined_women = 0;		// joined_women
	var $retired_women = 0;		// retired_women

	var $avg_workers = 0;
	var $avg_men = 0;
	var $avg_women = 0;

	var $age_25down = 0;
	var $age_25_35 = 0;
	var $age_36_45 = 0;
	var $age_46_55 = 0;
	var $age_55up = 0;

	var $service_5down = 0;
	var $service_5_10 = 0;
	var $service_10up = 0;

	var $worker_ids = '';
	var $telks = array();
	var $workers = array();

	var $sick_anual_workers = 0;
	var $sick_anual_men = 0;
	var $sick_anual_women = 0;
	var $sick_age_25down = 0;
	var $sick_age_25_35 = 0;
	var $sick_age_36_45 = 0;
	var $sick_age_46_55 = 0;
	var $sick_age_55up = 0;
	var $sick_age_25down_men = 0;
	var $sick_age_25_35_men = 0;
	var $sick_age_36_45_men = 0;
	var $sick_age_46_55_men = 0;
	var $sick_age_55up_men = 0;
	var $sick_age_25down_women = 0;
	var $sick_age_25_35_women = 0;
	var $sick_age_36_45_women = 0;
	var $sick_age_46_55_women = 0;
	var $sick_age_55up_women = 0;

	var $no_sick_anual_workers = 0;
	var $no_sick_anual_men = 0;
	var $no_sick_anual_women = 0;
	var $no_sick_age_25down = 0;
	var $no_sick_age_25_35 = 0;
	var $no_sick_age_36_45 = 0;
	var $no_sick_age_46_55 = 0;
	var $no_sick_age_55up = 0;
	var $no_sick_age_25down_men = 0;
	var $no_sick_age_25_35_men = 0;
	var $no_sick_age_36_45_men = 0;
	var $no_sick_age_46_55_men = 0;
	var $no_sick_age_55up_men = 0;
	var $no_sick_age_25down_women = 0;
	var $no_sick_age_25_35_women = 0;
	var $no_sick_age_36_45_women = 0;
	var $no_sick_age_46_55_women = 0;
	var $no_sick_age_55up_women = 0;

	var $primary_charts = 0;
	var $primary_charts_men = 0;
	var $primary_charts_women = 0;
	var $primary_charts_age_25down = 0;
	var $primary_charts_age_25_35 = 0;
	var $primary_charts_age_36_45 = 0;
	var $primary_charts_age_46_55 = 0;
	var $primary_charts_age_55up = 0;

	var $days_off = 0;
	var $days_off_men = 0;
	var $days_off_women = 0;
	var $days_off_age_25down = 0;
	var $days_off_age_25_35 = 0;
	var $days_off_age_36_45 = 0;
	var $days_off_age_46_55 = 0;
	var $days_off_age_55up = 0;
	
	var $primary_charts_days_off_3down = 0;	// Брой случаи с временна неработоспособност с продължителност до 3 дни (първични болнични листове)
	var $num_workers_primary_charts_4up = 0;// Брой на работещите с 4 и повече случаи с временна неработоспособност (първични болнични листове)
	var $num_workers_days_off_30up = 0;		// Брой на работещите с 30 и повече дни временна неработоспособност от заболявания
	var $num_pro_diseases = 0;				// Брой регистрирани професионални болести
	var $num_workers_pro_diseases = 0;		// Брой работещи с регистрирани професионални болести
	var $num_workers_with_telk = 0;			// Брой на работещите с експертно решение на ТЕЛК за заболяване с трайна неработоспособност
	var $num_workers_medical_checkups = 0;	// Брой на работещите, обхванати със задължителни периодични медицински прегледи
	var $num_diseases_medical_checkups = 0;	// Брой заболявания, открити при проведените задължителни периодични медицински прегледи
	var $num_ill_workers_medical_checkups = 0;// Брой работещи със заболявания, открити при проведените задължителни периодични медицински прегледи
	var $num_workers_labour_accidents = 0;	// Брой на работещите с трудови злополуки
	var $num_workers_labour_accidents_ary = array();	// Работещите с трудови злополуки
	
	var $pro_diseases_by_worker = array();
	var $workers_days_off_30up = array();		// Работещи с 30 и повече дни временна неработоспособност от заболявания
	var $patient_charts_by_mkb = array();
	var $patient_charts_by_worker = array();
	var $pro_diseases = array();				// Регистрирани професионални болести с първични болнични листове или експертно решение на ТЕЛК
	var $medical_checkups = array();			// Задължителни периодични медицински прегледи
	var $family_diseases = array();				// Фам. заб.
	
	var $tbl_diseases_medical_checkups = '';	// Таблица: заболявания, открити при проведените задължителни периодични медицински прегледи
	var $tbl_ill_workers_medical_checkups = '';	// Таблица: работещи със заболявания, открити при проведените задължителни периодични медицински прегледи
	var $workers_labour_accidents = array();	// 

	var $progroup_0 = 0;
	var $progroup_1 = 0;
	var $progroup_2 = 0;
	var $progroup_3 = 0;
	var $progroup_4 = 0;
	var $progroup_5 = 0;
	var $sick_progroup_0 = 0;
	var $sick_progroup_1 = 0;
	var $sick_progroup_2 = 0;
	var $sick_progroup_3 = 0;
	var $sick_progroup_4 = 0;
	var $sick_progroup_5 = 0;
	var $no_sick_progroup_0 = 0;
	var $no_sick_progroup_1 = 0;
	var $no_sick_progroup_2 = 0;
	var $no_sick_progroup_3 = 0;
	var $no_sick_progroup_4 = 0;
	var $no_sick_progroup_5 = 0;
	var $primary_charts_progroup_0 = 0;
	var $primary_charts_progroup_1 = 0;
	var $primary_charts_progroup_2 = 0;
	var $primary_charts_progroup_3 = 0;
	var $primary_charts_progroup_4 = 0;
	var $primary_charts_progroup_5 = 0;
	var $days_off_progroup_0 = 0;
	var $days_off_progroup_1 = 0;
	var $days_off_progroup_2 = 0;
	var $days_off_progroup_3 = 0;
	var $days_off_progroup_4 = 0;
	var $days_off_progroup_5 = 0;
	var $cdb_off_progroup_0 = 0;
	var $cdb_off_progroup_1 = 0;
	var $cdb_off_progroup_2 = 0;
	var $cdb_off_progroup_3 = 0;
	var $cdb_off_progroup_4 = 0;
	var $cdb_off_progroup_5 = 0;
	var $positions_progroup_0 = array();
	var $positions_progroup_1 = array();
	var $positions_progroup_2 = array();
	var $positions_progroup_3 = array();
	var $positions_progroup_4 = array();
	var $positions_progroup_5 = array();

	// dyn. progroups
	var $progroups = array();
	var $sick_progroups = array();
	var $no_sick_progroups = array();
	var $primary_charts_progroups = array();
	var $days_off_progroups = array();
	var $cdb_off_progroups = array();

	// ЧДБ - с 4 и повече случаи с временна неработоспособност (първични болнични листове)
	// и/или с 30 и повече дни с трудозагуби от заболявания за съответния период
	var $cdb_off = 0;
	var $cdb_off_men = 0;
	var $cdb_off_women = 0;
	var $cdb_off_age_25down = 0;
	var $cdb_off_age_25_35 = 0;
	var $cdb_off_age_36_45 = 0;
	var $cdb_off_age_46_55 = 0;
	var $cdb_off_age_55up = 0;

	// Calculated fields
	var $rel_sick_anual_workers = 0;
	var $rel_sick_anual_men = 0;
	var $rel_sick_anual_women = 0;
	var $freq_primary_charts = 0;
	var $freq_primary_charts_men = 0;
	var $freq_primary_charts_women = 0;
	var $freq_days_off = 0;
	var $freq_days_off_men = 0;
	var $freq_days_off_women = 0;
	var $avg_length_of_chart = 0;
	var $avg_length_of_chart_men = 0;
	var $avg_length_of_chart_women = 0;
	var $rel_charts_per_worker = 0;
	var $rel_charts_per_worker_men = 0;
	var $rel_charts_per_worker_women = 0;
	var $rel_charts_per_worker_age_25down = 0;
	var $rel_charts_per_worker_age_25_35 = 0;
	var $rel_charts_per_worker_age_36_45 = 0;
	var $rel_charts_per_worker_age_46_55 = 0;
	var $rel_charts_per_worker_age_55up = 0;
	var $rel_days_off_per_worker = 0;
	var $rel_days_off_per_worker_men = 0;
	var $rel_days_off_per_worker_women = 0;
	var $rel_days_off_per_worker_25down = 0;
	var $rel_days_off_per_worker_25_35 = 0;
	var $rel_days_off_per_worker_36_45 = 0;
	var $rel_days_off_per_worker_46_55 = 0;
	var $rel_days_off_per_worker_55up = 0;
	var $rel_cdb_off = 0;
	var $rel_cdb_off_men = 0;
	var $rel_cdb_off_women = 0;
	var $rel_cdb_off_age_25down = 0;
	var $rel_cdb_off_age_25_35 = 0;
	var $rel_cdb_off_age_36_45 = 0;
	var $rel_cdb_off_age_46_55 = 0;
	var $rel_cdb_off_age_55up = 0;
	var $rel_sick_age_25down = 0;
	var $rel_sick_age_25_35 = 0;
	var $rel_sick_age_36_45 = 0;
	var $rel_sick_age_46_55 = 0;
	var $rel_sick_age_55up = 0;
	var $freq_primary_charts_age_25down = 0;
	var $freq_primary_charts_age_25_35 = 0;
	var $freq_primary_charts_age_36_45 = 0;
	var $freq_primary_charts_age_46_55 = 0;
	var $freq_primary_charts_age_55up = 0;
	var $freq_days_off_age_25down = 0;
	var $freq_days_off_age_25_35 = 0;
	var $freq_days_off_age_36_45 = 0;
	var $freq_days_off_age_46_55 = 0;
	var $freq_days_off_age_55up = 0;
	var $avg_length_of_chart_age_25down = 0;
	var $avg_length_of_chart_age_25_35 = 0;
	var $avg_length_of_chart_age_36_45 = 0;
	var $avg_length_of_chart_age_46_55 = 0;
	var $avg_length_of_chart_age_55up = 0;
	var $rel_sick_progroup_0 = 0;
	var $rel_sick_progroup_1 = 0;
	var $rel_sick_progroup_2 = 0;
	var $rel_sick_progroup_3 = 0;
	var $rel_sick_progroup_4 = 0;
	var $rel_sick_progroup_5 = 0;
	var $freq_primary_charts_progroup_0 = 0;
	var $freq_primary_charts_progroup_1 = 0;
	var $freq_primary_charts_progroup_2 = 0;
	var $freq_primary_charts_progroup_3 = 0;
	var $freq_primary_charts_progroup_4 = 0;
	var $freq_primary_charts_progroup_5 = 0;
	var $freq_days_off_progroup_0 = 0;
	var $freq_days_off_progroup_1 = 0;
	var $freq_days_off_progroup_2 = 0;
	var $freq_days_off_progroup_3 = 0;
	var $freq_days_off_progroup_4 = 0;
	var $freq_days_off_progroup_5 = 0;
	var $avg_length_of_chart_progroup_0 = 0;
	var $avg_length_of_chart_progroup_1 = 0;
	var $avg_length_of_chart_progroup_2 = 0;
	var $avg_length_of_chart_progroup_3 = 0;
	var $avg_length_of_chart_progroup_4 = 0;
	var $avg_length_of_chart_progroup_5 = 0;
	var $rel_cdb_off_progroup_0 = 0;
	var $rel_cdb_off_progroup_1 = 0;
	var $rel_cdb_off_progroup_2 = 0;
	var $rel_cdb_off_progroup_3 = 0;
	var $rel_cdb_off_progroup_4 = 0;
	var $rel_cdb_off_progroup_5 = 0;
	var $rel_charts_per_worker_progroup_0 = 0;
	var $rel_charts_per_worker_progroup_1 = 0;
	var $rel_charts_per_worker_progroup_2 = 0;
	var $rel_charts_per_worker_progroup_3 = 0;
	var $rel_charts_per_worker_progroup_4 = 0;
	var $rel_charts_per_worker_progroup_5 = 0;
	var $rel_days_off_per_worker_progroup_0 = 0;
	var $rel_days_off_per_worker_progroup_1 = 0;
	var $rel_days_off_per_worker_progroup_2 = 0;
	var $rel_days_off_per_worker_progroup_3 = 0;
	var $rel_days_off_per_worker_progroup_4 = 0;
	var $rel_days_off_per_worker_progroup_5 = 0;

	// dyn. progroups
	var $rel_sick_progroups = array();
	var $freq_primary_charts_progroups = array();
	var $freq_days_off_progroups = array();
	var $avg_length_of_chart_progroups = array();
	var $rel_cdb_off_progroups = array();
	var $rel_charts_per_worker_progroups = array();
	var $rel_days_off_per_worker_progroups = array();
	
	var $inc = 0;
	
	var $tmp_unable_to_work_struct_chart_data = array();
	var $chart_data = array();

	function __construct($firm_id = 0, $date_from = '2010-01-01 00:00:00', $date_to = '2011-12-31 23:59:59', $subdivision_id = 0, $wplace_id = 0) {
		$this->firm_id = intval($firm_id);
		
		$date_from = (!empty($date_from) && false !== $ts = strtotime($date_from)) ? date('Y-m-d H:i:s', $ts) : date('Y-m-d H:i:s', mktime(0, 0, 0, 1, 1, (date('Y') - 1)));
		$date_to = (!empty($date_to) && false !== $ts = strtotime($date_to)) ? date('Y-m-d H:i:s', $ts) : date('Y-m-d H:i:s', mktime(23, 59, 59, 12, 31, date('Y')));
		$this->date_from = $date_from;
		$this->date_to = $date_to;
				
		$this->subdivision_id = intval($subdivision_id);
		$this->wplace_id = intval($wplace_id);
		
		$dt = substr($date_to, 0, 10);
		list($last_year, $last_month, $last_day) = explode('-', $dt);
		$ts_date_from = strtotime($date_from);
		$ts_date_to = strtotime($date_to);

		$IDs = array();
		$sql = "SELECT w.`worker_id` , w.`sex` , w.`egn` , w.`birth_date`, w.`map_id` ,
				w.`date_curr_position_start` , w.`date_career_start` , w.`date_retired` , i.`position_name` , 
				g.id AS progroup_id, g.parent AS parent_id, g.num AS `progroup`, g.name AS progroup_name
				FROM `workers` w 
				LEFT JOIN `firm_struct_map` m ON ( m.`map_id` = w.`map_id` )
				LEFT JOIN `firm_positions` i ON ( i.`position_id` = m.`position_id` )
				LEFT JOIN `pro_groups` g ON ( g.`id` = i.`progroup` )
				WHERE w.`firm_id` = $this->firm_id 
				AND w.`is_active` = '1'
				AND ( date_retired = '' OR julianday(date_retired) >= julianday('$date_from') )
				AND ( date_curr_position_start = '' OR julianday(date_curr_position_start) <= julianday('$date_to') )";
		if(!empty($this->subdivision_id)) $sql .= " AND m.`subdivision_id` = $this->subdivision_id";
		if(!empty($this->wplace_id)) $sql .= " AND m.`wplace_id` = $this->wplace_id";
		
		$rows = $this->query($sql);
		$is_joined = 0;
		if(!empty($rows)) {
			$wIDs = array();
			$sIDs = array();
			$_row = array();
			foreach ($rows as $row) {
				$IDs[] = $row['worker_id'];
				$date_retired = strtotime($row['date_retired']);
				$date_curr_position_start = strtotime($row['date_curr_position_start']);
				$sex = $row['sex'];
				$worker_age = 0;
				if(!empty($row['birth_date'])) {
					list($birth_year, $birth_month, $birth_day) = explode('-', substr($row['birth_date'], 0, 10)) ;
					$worker_age = calculate_age($birth_day, $birth_month, $birth_year, $last_day, $last_month, $last_year);
				}
				// anual_workers
				if(( empty($row['date_retired']) || $date_retired >= $ts_date_to ) && ( $date_curr_position_start <= $ts_date_from || empty($row['date_curr_position_start']) )) {
					$this->anual_workers++;
				}
				$is_joined = 0;
				$count_as = 1;
				// joined_workers
				if(( $date_curr_position_start > $ts_date_from ) && ( $date_curr_position_start <= $ts_date_to )) {
					$this->joined_workers++;
					$is_joined = 1;
					$count_as = 0.5;
				}
				// retired_workers
				if(!$is_joined && ( $date_retired >= $ts_date_from ) && ( $date_retired <= $ts_date_to )) {
					$this->retired_workers++;
					$count_as = 0.5;
				}
				// anual_men
				if(( empty($row['date_retired']) || $date_retired >= $ts_date_to ) && ( $date_curr_position_start <= $ts_date_from || empty($row['date_curr_position_start']) ) && ( $sex == 'М' || $sex == '' )) {
					$this->anual_men++;
				}
				$is_joined = 0;
				// joined_men
				if(( $date_curr_position_start > $ts_date_from ) && ( $date_curr_position_start <= $ts_date_to ) && ( $sex == 'М' || $sex == '' )) {
					$this->joined_men++;
					$is_joined = 1;
				}
				// retired_men
				if(!$is_joined && ( $date_retired >= $ts_date_from ) && ( $date_retired <= $ts_date_to ) && ( $sex == 'М' || $sex == '' )) {
					$this->retired_men++;
				}

				// anual_women
				if(( empty($row['date_retired']) || $date_retired >= $ts_date_to ) && ( $date_curr_position_start <= $ts_date_from || empty($row['date_curr_position_start']) ) && ( $sex == 'Ж' )) {
					$this->anual_women++;
				}
				$is_joined = 0;
				// joined_women
				if(( $date_curr_position_start > $ts_date_from ) && ( $date_curr_position_start <= $ts_date_to ) && ( $sex == 'Ж' )) {
					$this->joined_women++;
					$is_joined = 1;
				}
				// retired_women
				if(!$is_joined && ( $date_retired >= $ts_date_from ) && ( $date_retired <= $ts_date_to ) && ( $sex == 'Ж' )) {
					$this->retired_women++;
				}
				// ages
				if($worker_age < 25) { $this->age_25down += $count_as; }
				elseif ($worker_age >= 25 && $worker_age <= 35 ) { $this->age_25_35 += $count_as; }
				elseif ($worker_age > 35 && $worker_age <= 45 ) { $this->age_36_45 += $count_as; }
				elseif ($worker_age > 45 && $worker_age <= 55 ) { $this->age_46_55 += $count_as; }
				else { $this->age_55up += $count_as; }
				// service lengths
				$date_curr_position_start = $row['date_curr_position_start'];
				if(empty($date_curr_position_start)) $this->service_5down += $count_as;
				else {
					$dt = substr($date_curr_position_start, 0, 10);
					list($position_year, $position_month, $position_day) = explode('-', $dt);
					$t = calculate_age($position_day, $position_month, $position_year, $last_day, $last_month, $last_year);
					if($t < 5) { $this->service_5down += $count_as; }
					elseif ($t >= 5 && $t < 10) { $this->service_5_10 += $count_as; }
					elseif ($t >= 10) { $this->service_10up += $count_as; }
				}
				$wIDs[$row['worker_id']] = $count_as;
				$sIDs[$row['worker_id']] = $sex;
				if(empty($row['progroup'])) $row['progroup'] = 0;
				$row['age'] = $worker_age;
				$row['count_as'] = $count_as;
				$_row[$row['worker_id']] = $row;

				if(!isset($this->progroups[$row['progroup']])) $this->progroups[$row['progroup']] = 0;
				$this->progroups[$row['progroup']] += $count_as;

				$this->{'progroup_'.$row['progroup']} += $count_as;
				$this->{'positions_progroup_'.$row['progroup']}[$row['position_name']] = $row['position_name'];
			}
			$this->workers = $_row;
			if(isset($this->progroups[0])) unset($this->progroups[0]);
			ksort($this->progroups);
			for($i = 0; $i <= 5; $i++) {
				$this->{'positions_progroup_'.$i} = array_map(array('SqliteDB', 'my_mb_ucfirst'), $this->{'positions_progroup_'.$i});
			}
			$this->avg_workers = $this->anual_workers + (($this->joined_workers + $this->retired_workers) / 2);
			$this->avg_men = $this->anual_men + (($this->joined_men + $this->retired_men) / 2);
			$this->avg_women = $this->anual_women + (($this->joined_women + $this->retired_women) / 2);
			$this->worker_ids = implode(',', $IDs);
			
			$patient_charts_by_worker = array();
			$sql = "SELECT * FROM `patient_charts` WHERE `worker_id` IN (".implode(',', $IDs).") AND ((julianday(`hospital_date_from`) >= julianday('$date_from'))
AND (julianday(`hospital_date_from`) <= julianday('$date_to')))";
			$rows = $this->query($sql);
			// Sick workers
			$sick_wIDs = array();
			$primaries = array();
			if(!empty($rows)) {
				$sick_anual_workers = array();
				$sick_anual_men = array();
				$sick_anual_women = array();
				$cdb = array();
				$days_off_4up = array();
				foreach ($rows as $row) {
					$patient_charts_by_worker[$row['worker_id']][] = $row;
					$sick_anual_workers[$row['worker_id']] = $row['worker_id'];
					if($sIDs[$row['worker_id']] == 'Ж') { $sick_anual_women[$row['worker_id']] = $row['worker_id']; }
					else { $sick_anual_men[$row['worker_id']] = $row['worker_id']; }
					$sick_wIDs[$row['worker_id']] = $row['worker_id'];
					if(!empty($row['medical_types']) && $medical_types = unserialize($row['medical_types'])) {
						if(in_array('1', $medical_types)) {
							$primaries[] = $row;
							(isset($cdb[$row['worker_id']]['primary'])) ? $cdb[$row['worker_id']]['primary'] += 1 : $cdb[$row['worker_id']]['primary'] = 1;
						}
					}
					$this->days_off += $row['days_off'];
					$this->{'days_off_progroup_'.$_row[$row['worker_id']]['progroup']} += $row['days_off'];

					if(!isset($this->days_off_progroups[$_row[$row['worker_id']]['progroup']])) $this->days_off_progroups[$_row[$row['worker_id']]['progroup']] =0;
					$this->days_off_progroups[$_row[$row['worker_id']]['progroup']] += $row['days_off'];

					if($sIDs[$row['worker_id']] == 'Ж') { $this->days_off_women += $row['days_off']; }
					else { $this->days_off_men += $row['days_off']; }
					$this->_assignWorkerAgeToGroup($_row[$row['worker_id']]['age'], $row['days_off'], 'days_off_age', '');
					(isset($cdb[$row['worker_id']]['days_off'])) ? $cdb[$row['worker_id']]['days_off'] += $row['days_off'] : $cdb[$row['worker_id']]['days_off'] = $row['days_off'];
					(isset($days_off_4up[$row['worker_id']])) ? $days_off_4up[$row['worker_id']]++ : $days_off_4up[$row['worker_id']] = 1;
				}
				foreach ($sick_anual_workers as $worker_id) {
					$this->sick_anual_workers += $wIDs[$worker_id];
					$this->_assignWorkerAgeToGroup($_row[$worker_id]['age'], $wIDs[$worker_id], 'sick_age', '');
					$this->{'sick_progroup_'.$_row[$worker_id]['progroup']} += $wIDs[$worker_id];

					if(!isset($this->sick_progroups[$_row[$worker_id]['progroup']])) $this->sick_progroups[$_row[$worker_id]['progroup']] = 0;
					$this->sick_progroups[$_row[$worker_id]['progroup']] += $wIDs[$worker_id];
				}
				foreach ($sick_anual_women as $worker_id) {
					$this->sick_anual_women += $wIDs[$worker_id];
					$this->_assignWorkerAgeToGroup($_row[$worker_id]['age'], $wIDs[$worker_id], 'sick_age', '_women');
				}
				foreach ($sick_anual_men as $worker_id) {
					$this->sick_anual_men += $wIDs[$worker_id];
					$this->_assignWorkerAgeToGroup($_row[$worker_id]['age'], $wIDs[$worker_id], 'sick_age', '_men');
				}
				foreach ($days_off_4up as $worker_id => $primary_charts) {
					if($days_off_4up[$worker_id] < 4) unset($days_off_4up[$worker_id]);
				}
				$this->num_workers_primary_charts_4up = count($days_off_4up);
				unset($days_off_4up);
			}
			
			// hack asked by Asya from Viamed, Sofia
			global $stm_name;
			if(!empty($stm_name) && false !== strpos($stm_name, 'ВИАМЕД')) {
				$this->avg_men = round($this->avg_men, 0);
				$this->avg_women = round($this->avg_women, 0);
				$this->avg_workers = $this->avg_men + $this->avg_women;
				$this->sick_anual_workers = round($this->sick_anual_workers);
			}
			
			// No sick workers
			$no_sick_wIDs = array_diff($IDs, $sick_wIDs);
			if(!empty($no_sick_wIDs)) {
				$no_sick_anual_workers = array();
				$no_sick_anual_men = array();
				$no_sick_anual_women = array();
				foreach ($no_sick_wIDs as $worker_id) {
					$row = $_row[$worker_id];
					$no_sick_anual_workers[$row['worker_id']] = $row['worker_id'];
					if($sIDs[$row['worker_id']] == 'Ж') { $no_sick_anual_women[$row['worker_id']] = $row['worker_id']; }
					else { $no_sick_anual_men[$row['worker_id']] = $row['worker_id']; }
				}
				foreach ($no_sick_anual_workers as $worker_id) {
					$this->no_sick_anual_workers += $wIDs[$worker_id];
					$this->_assignWorkerAgeToGroup($_row[$worker_id]['age'], $wIDs[$worker_id], 'no_sick_age', '');
					$this->{'no_sick_progroup_'.$_row[$worker_id]['progroup']} += $wIDs[$worker_id];

					if(!isset($this->no_sick_progroups[$_row[$worker_id]['progroup']])) $this->no_sick_progroups[$_row[$worker_id]['progroup']] = 0;
					$this->no_sick_progroups[$_row[$worker_id]['progroup']] += $wIDs[$worker_id];
				}
				foreach ($no_sick_anual_women as $worker_id) {
					$this->no_sick_anual_women += $wIDs[$worker_id];
					$this->_assignWorkerAgeToGroup($_row[$worker_id]['age'], $wIDs[$worker_id], 'no_sick_age', '_women');
				}
				foreach ($no_sick_anual_men as $worker_id) {
					$this->no_sick_anual_men += $wIDs[$worker_id];
					$this->_assignWorkerAgeToGroup($_row[$worker_id]['age'], $wIDs[$worker_id], 'no_sick_age', '_men');
				}
			}
			// Calc. primary cases
			$this->primary_charts = count($primaries);
			$num_workers_labour_accidents = array();
			$num_workers_pro_diseases = array();
			$num_pro_diseases = array();
			if(!empty($primaries)) {
				foreach ($primaries as $row) {
					if($sIDs[$row['worker_id']] == 'Ж') { $this->primary_charts_women += 1; }
					else { $this->primary_charts_men += 1; }
					$this->_assignWorkerAgeToGroup($_row[$row['worker_id']]['age'], 1, 'primary_charts_age', '');
					$this->{'primary_charts_progroup_'.$_row[$row['worker_id']]['progroup']} += 1;

					if(!isset($this->primary_charts_progroups[$_row[$row['worker_id']]['progroup']])) $this->primary_charts_progroups[$_row[$row['worker_id']]['progroup']] = 0;
					$this->primary_charts_progroups[$_row[$row['worker_id']]['progroup']] += 1;
					if(3 >= intval($row['days_off'])) {
						$this->primary_charts_days_off_3down++; 	
					}
					$this->patient_charts_by_mkb[$row['mkb_id']][] = $row;
					
					// Брой работещи с регистрирани професионални болести & Брой регистрирани професионални болести
					if(in_array($row['reason_id'], array('02', '03'))) {
						(isset($num_workers_pro_diseases[$row['worker_id']])) ? $num_workers_pro_diseases[$row['worker_id']]++ : $num_workers_pro_diseases[$row['worker_id']] = 1;
						$num_pro_diseases[$row['mkb_id']] = 1;
						$this->pro_diseases_by_worker[$row['worker_id']] = $row['mkb_id'];
						$this->pro_diseases[] = array('worker_id' => $row['worker_id'], 'mkb_id' => $row['mkb_id']);
					}
					// Брой на работещите с трудови злополуки
					if(in_array($row['reason_id'], array('04', '05'))) {
						$num_workers_labour_accidents[$row['worker_id']] = $row['mkb_id'].'&patient_charts&'.$row['chart_id'];
						$this->workers_labour_accidents[$row['reason_id']] = (isset($this->workers_labour_accidents[$row['reason_id']])) ? ++$this->workers_labour_accidents[$row['reason_id']] : 1;
					}
					$this->patient_charts_by_worker[$row['worker_id']][] = $row;
				}
			}
			//
			if(!empty($cdb)) {
				$num_workers_days_off_30up = array();
				// 1st pass - get rid redundant records
				foreach ($cdb as $worker_id => $row) {
					if(!isset($row['primary'])) {
						$cdb[$worker_id]['primary'] = $row['primary'] = 0;
					}
					
					$hit = 0;
					$days_off = 0;
					$mkbs = '';
					$num_primary = 0;
					$chart_mkbs = '';
					if($row['primary'] > 0 && $row['days_off'] >= 30) {
						$num_workers_days_off_30up[$worker_id] = 1;
						$hit = 1;
						if(isset($patient_charts_by_worker[$worker_id])) {
							$aMkb = array();
						  	foreach ($patient_charts_by_worker[$worker_id] as $fld) {
						  		$days_off += intval($fld['days_off']);
						  		$aMkb[$fld['mkb_id']] = $fld['mkb_id']; 
						  	}
						  	$mkbs = implode('; ', $aMkb);
						}
					}
					if($row['primary'] >= 4) {
						$hit = 1;
						$num_primary = $row['primary'];
						if(isset($patient_charts_by_worker[$worker_id])) {
							$aMkb = array();
						  	foreach ($patient_charts_by_worker[$worker_id] as $fld) {
						  		$aMkb[$fld['mkb_id']] = $fld['mkb_id']; 
						  	}
						  	$mkbs = implode('; ', $aMkb);
						}
						$chart_mkbs = $mkbs;
					}
					if($hit) {
						$this->workers_days_off_30up[$worker_id] = $_row[$worker_id];
						$this->workers_days_off_30up[$worker_id]['days_off'] = $days_off;
						$this->workers_days_off_30up[$worker_id]['mkbs'] = $mkbs;
						$this->workers_days_off_30up[$worker_id]['num_primary'] = $num_primary;
						$this->workers_days_off_30up[$worker_id]['chart_mkbs'] = $chart_mkbs;
					}
					
					if($row['primary'] >= 4 || $row['days_off'] >= 30) { /*It's OK!*/ }
					else { unset($cdb[$worker_id]); }
				}
				$this->cdb_off = count($cdb);
				$this->num_workers_days_off_30up = count($num_workers_days_off_30up);
				unset($num_workers_days_off_30up);
				// 2nd pass
				if(!empty($cdb)) {
					foreach ($cdb as $worker_id => $row) {
						if($sIDs[$worker_id] == 'Ж') { $this->cdb_off_women += 1; }
						else { $this->cdb_off_men += 1; }
						$this->_assignWorkerAgeToGroup($_row[$worker_id]['age'], 1, 'cdb_off_age', '');
						$this->{'cdb_off_progroup_'.$_row[$worker_id]['progroup']} += 1;

						if(!isset($this->cdb_off_progroups[$_row[$worker_id]['progroup']])) $this->cdb_off_progroups[$_row[$worker_id]['progroup']] = 0;
						$this->cdb_off_progroups[$_row[$worker_id]['progroup']] += 1;
					}
				}
			}
			unset($rows);
			
			// *** TELKs
			$sql = "SELECT t.* , i.`position_id` AS `position_id`, i.`position_name` AS `position_name` 
					FROM `telks` t
					LEFT JOIN `workers` w ON (w.`worker_id` = t.`worker_id`)
					LEFT JOIN `firm_struct_map` m ON (m.`map_id` = w.`map_id`)
					LEFT JOIN `firm_positions` i ON (i.`position_id` = m.`position_id`)
					WHERE t.`worker_id` IN (".implode(',', $IDs).") 
					AND (
						( julianday(t.`telk_date_from`) >= julianday('$date_from') ) AND ( julianday(t.`telk_date_from`) <= julianday('$date_to') )
						OR
						( julianday(t.`telk_date_from`) <= julianday('$date_to') ) AND ( (t.`telk_date_to` = '' OR `telk_date_to` IS NULL) OR julianday(t.`telk_date_to`) >= julianday('$date_from') )
					)";
			$rows = $this->query($sql);
			$num_workers_with_telk = array();
			if(!empty($rows)) {
				foreach ($rows as $row) {
					// Брой работещи с регистрирани професионални болести & Брой регистрирани професионални болести
					if(!empty($row['mkb_id_4'])) {
						(isset($num_workers_pro_diseases[$row['worker_id']])) ? $num_workers_pro_diseases[$row['worker_id']]++ : $num_workers_pro_diseases[$row['worker_id']] = 1;	
						$num_pro_diseases[$row['mkb_id_4']] = 1;
						$this->pro_diseases_by_worker[$row['worker_id']] = $row['mkb_id_4'];
						$this->pro_diseases[] = array('worker_id' => $row['worker_id'], 'mkb_id' => $row['mkb_id_4']);
					}
					// Брой на работещите с трудови злополуки
					if(!empty($row['mkb_id_3'])) {
						$num_workers_labour_accidents[$row['worker_id']] = $row['mkb_id_3'].'&telks&'.$row['telk_id'];
					}
					$num_workers_with_telk[$row['worker_id']] = 1;
				}
			}
			$this->telks = $rows;
			unset($rows);
			$this->num_workers_pro_diseases = count($num_workers_pro_diseases);
			unset($num_workers_pro_diseases);
			$this->num_pro_diseases = count($num_pro_diseases);
			unset($num_pro_diseases);
			$this->num_workers_with_telk = count($num_workers_with_telk);
			unset($num_workers_with_telk);
			$this->num_workers_labour_accidents = count($num_workers_labour_accidents);
			$this->num_workers_labour_accidents_ary = $num_workers_labour_accidents;
			unset($num_workers_labour_accidents);
			// *** Medical checkups
			$sql = "SELECT * 
					FROM `medical_checkups` 
					WHERE `worker_id` IN (".implode(',', $IDs).") 
					AND `checkup_date` != '' 
					AND `checkup_date` IS NOT NULL
					AND julianday(`checkup_date`) >= julianday('$date_from') 
					AND julianday(`checkup_date`) <= julianday('$date_to')";
			$rows = $this->query($sql);
			$chkIDs = array();
			$num_workers_medical_checkups = array();
			if(!empty($rows)) {
				foreach ($rows as $row) {
					$chkIDs[] = $row['checkup_id'];
					$num_workers_medical_checkups[$row['worker_id']] = 1;
					$this->medical_checkups[$row['worker_id']] = $row;
				}
			}
			unset($rows);
			$this->num_workers_medical_checkups = count($num_workers_medical_checkups);
			unset($num_workers_medical_checkups);
			
			if(!empty($chkIDs)) {
				// *** Family diseases
				$sql = "SELECT d.worker_id AS worker_id, cl.class_id AS class_id, cl.class_name AS class_name, 
						g.group_id AS group_id, g.group_name AS group_name, 
						d.mkb_id AS mkb_id, m.mkb_desc AS mkb_desc, COUNT(*) AS cnt
						FROM `family_diseases` d
						LEFT JOIN `mkb` m ON (m.mkb_id = d.mkb_id)
						LEFT JOIN `mkb_groups` g ON (g.group_id = m.group_id)
						LEFT JOIN `mkb_classes` cl ON (cl.class_id = g.class_id)
						WHERE d.`checkup_id` IN (".implode(',', $chkIDs).")
						GROUP BY d.mkb_id
						ORDER BY cl.class_id, g.group_id, cnt DESC, m.mkb_id";
				$data = $this->_getNosologicTable($sql);
				$this->num_diseases_medical_checkups = $data['total'];
				$this->tbl_diseases_medical_checkups = $data['table'];
				$this->chart_data = $data['chart_data'];
				
				// *** Family diseases by worker
				$sql = "SELECT * FROM `family_diseases` WHERE `checkup_id` IN (".implode(',', $chkIDs).") ORDER BY `worker_id`, `mkb_id`";
				$rows = $this->query($sql);
				if(!empty($rows)) {
					$data = array();
					foreach ($rows as $row) {
						$data[$row['worker_id']][] = $row['mkb_id'];
						$this->family_diseases[$row['checkup_id']][] = $row;
					}
					$ary = array();
					// group by MKBs
					foreach ($data as $worker_id => $mkbs) {
						$key = implode(', ', $mkbs);
						(isset($ary[$key])) ? $ary[$key]++ : $ary[$key] = 1;
					}
					$tbl_ill_workers_medical_checkups = <<< EOT
<table class=MsoTableGrid border=1 cellspacing=0 cellpadding=0 width="50%"
 style='width:50.0%;margin-left:1.9pt;border-collapse:collapse;border:none;
 mso-border-alt:solid windowtext .5pt;mso-yfti-tbllook:480;mso-padding-alt:
 0cm 5.4pt 0cm 5.4pt;mso-border-insideh:.5pt solid windowtext;mso-border-insidev:
 .5pt solid windowtext'>
 <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes'>
  <td width="50%" style='width:50.0%;border:solid windowtext 1.0pt;mso-border-alt:
  solid windowtext .5pt;background:#CCFFCC;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:14.0pt'>МКБ</span></b><o:p></o:p></p>
  </td>
  <td width="50%" style='width:50.0%;border:solid windowtext 1.0pt;border-left:
  none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  background:#CCFFCC;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:14.0pt'>бр. заболели</span></b><span lang=EN-US
  style='mso-ansi-language:EN-US'><o:p></o:p></span></p>
  </td>
 </tr>
EOT;
					$i = 0;
					$total = 0;
					foreach ($ary as $key => $val) {
						$tbl_ill_workers_medical_checkups .= <<< EOT
<tr style='mso-yfti-irow:$i'>
  <td width="50%" style='width:50.0%;border:solid windowtext 1.0pt;border-top:
  none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:11.0pt;mso-bidi-font-weight:bold'>$key</span><span lang=EN-US style='font-size:11.0pt;mso-ansi-language:
  EN-US'><o:p></o:p></span></p>
  </td>
  <td width="50%" style='width:50.0%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
    <p class=MsoNormal align=center style='text-align:center'><span lang=EN-US
  style='font-size:11.0pt;mso-ansi-language:EN-US'>$val<o:p></o:p></span></p></td>
 </tr>
EOT;
						$total += $val;
						$i++;
					}
					$tbl_ill_workers_medical_checkups .= <<< EOT
 <tr style='mso-yfti-irow:6;mso-yfti-lastrow:yes'>
  <td width="50%" style='width:50.0%;border:solid windowtext 1.0pt;border-top:
  none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  background:#CCFFCC;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:11.0pt'>ВСИЧКО</span></b><b style='mso-bidi-font-weight:
  normal'><span style='font-size:11.0pt'><o:p></o:p></span></b></p>
  </td>
  <td width="50%" style='width:50.0%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;background:#CCFFCC;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b
  style='mso-bidi-font-weight:normal'><span style='font-size:11.0pt'>$total<o:p></o:p></span></b></p>
  </td>
 </tr>
</table>
EOT;
					$this->tbl_ill_workers_medical_checkups = $tbl_ill_workers_medical_checkups;
					$this->num_ill_workers_medical_checkups = $total;
				}
				unset($rows);
				unset($data);
				
			}
			
		}
		// Calculations
		$this->rel_sick_anual_workers = (!empty($this->avg_workers)) ? number_format($this->sick_anual_workers / $this->avg_workers * 100, 1, '.', '') : '0.0';
		$this->rel_sick_anual_men = (!empty($this->avg_men)) ? number_format($this->sick_anual_men / $this->avg_men * 100, 1, '.', '') : '0.0';
		$this->rel_sick_anual_women = (!empty($this->avg_women)) ? number_format($this->sick_anual_women / $this->avg_women * 100, 1, '.', '') : '0.0';
		$this->freq_primary_charts = (!empty($this->avg_workers)) ? number_format($this->primary_charts / $this->avg_workers * 100, 1, '.', '') : '0.0';
		$this->freq_primary_charts_men = (!empty($this->avg_men)) ? number_format($this->primary_charts_men / $this->avg_men * 100, 1, '.', '') : '0.0';
		$this->freq_primary_charts_women = (!empty($this->avg_women)) ? number_format($this->primary_charts_women / $this->avg_women * 100, 1, '.', '') : '0.0';

		$this->freq_days_off = (!empty($this->avg_workers)) ? number_format($this->days_off / $this->avg_workers * 100, 1, '.', '') : '0.0';
		$this->freq_days_off_men = (!empty($this->avg_men)) ? number_format($this->days_off_men / $this->avg_men * 100, 1, '.', '') : '0.0';
		$this->freq_days_off_women = (!empty($this->avg_women)) ? number_format($this->days_off_women / $this->avg_women * 100, 1, '.', '') : '0.0';

		$this->avg_length_of_chart = (!empty($this->primary_charts)) ? number_format($this->days_off / $this->primary_charts, 1, '.', '') : '0.0';
		$this->avg_length_of_chart_men = (!empty($this->primary_charts_men)) ? number_format($this->days_off_men / $this->primary_charts_men, 1, '.', '') : '0.0';
		$this->avg_length_of_chart_women = (!empty($this->primary_charts_women)) ? number_format($this->days_off_women / $this->primary_charts_women, 1, '.', '') : '0.0';

		$this->rel_charts_per_worker = (!empty($this->sick_anual_workers)) ? number_format($this->primary_charts / $this->sick_anual_workers, 1, '.', '') : '0.0';
		$this->rel_charts_per_worker_men = (!empty($this->sick_anual_men)) ? number_format($this->primary_charts_men / $this->sick_anual_men, 1, '.', '') : '0.0';
		$this->rel_charts_per_worker_women = (!empty($this->sick_anual_women)) ? number_format($this->primary_charts_women / $this->sick_anual_women, 1, '.', '') :'0.0';
		$this->rel_charts_per_worker_age_25down = (!empty($this->sick_age_25down)) ? number_format($this->primary_charts_age_25down / $this->sick_age_25down, 1, '.', '') : '0.0';
		$this->rel_charts_per_worker_age_25_35 = (!empty($this->sick_age_25_35)) ? number_format($this->primary_charts_age_25_35 / $this->sick_age_25_35, 1, '.', '') : '0.0';
		$this->rel_charts_per_worker_age_36_45 = (!empty($this->sick_age_36_45)) ? number_format($this->primary_charts_age_36_45 / $this->sick_age_36_45, 1, '.', '') : '0.0';
		$this->rel_charts_per_worker_age_46_55 = (!empty($this->sick_age_46_55)) ? number_format($this->primary_charts_age_46_55 / $this->sick_age_46_55, 1, '.', '') : '0.0';
		$this->rel_charts_per_worker_age_55up = (!empty($this->sick_age_55up)) ? number_format($this->primary_charts_age_55up / $this->sick_age_55up, 1, '.', '') : '0.0';

		$this->rel_days_off_per_worker = (!empty($this->sick_anual_workers)) ? number_format($this->days_off / $this->sick_anual_workers, 1, '.', '') : '0.0';
		$this->rel_days_off_per_worker_men = (!empty($this->sick_anual_men)) ? number_format($this->days_off_men / $this->sick_anual_men, 1, '.', '') : '0.0';
		$this->rel_days_off_per_worker_women = (!empty($this->sick_anual_women)) ? number_format($this->days_off_women / $this->sick_anual_women, 1, '.', ''):'0.0';
		$this->rel_days_off_per_worker_25down = (!empty($this->sick_age_25down)) ? number_format($this->days_off_age_25down / $this->sick_age_25down, 1, '.', '') : '0.0';
		$this->rel_days_off_per_worker_25_35 = (!empty($this->sick_age_25_35)) ? number_format($this->days_off_age_25_35 / $this->sick_age_25_35, 1, '.', ''):'0.0';
		$this->rel_days_off_per_worker_36_45 = (!empty($this->sick_age_36_45)) ? number_format($this->days_off_age_36_45 / $this->sick_age_36_45, 1, '.', ''):'0.0';
		$this->rel_days_off_per_worker_46_55 = (!empty($this->sick_age_46_55)) ? number_format($this->days_off_age_46_55 / $this->sick_age_46_55, 1, '.', ''):'0.0';
		$this->rel_days_off_per_worker_55up = (!empty($this->sick_age_55up)) ? number_format($this->days_off_age_55up / $this->sick_age_55up, 1, '.', '') : '0.0';

		$this->rel_cdb_off = (!empty($this->avg_workers)) ? number_format($this->cdb_off / $this->avg_workers * 100, 1, '.', '') : '0.0';
		$this->rel_cdb_off_men = (!empty($this->avg_men)) ? number_format($this->cdb_off_men / $this->avg_men * 100, 1, '.', '') : '0.0';
		$this->rel_cdb_off_women = (!empty($this->avg_women)) ? number_format($this->cdb_off_women / $this->avg_women * 100, 1, '.', '') : '0.0';
		$this->rel_cdb_off_age_25down = (!empty($this->age_25down)) ? number_format($this->cdb_off_age_25down / $this->age_25down * 100, 1, '.', '') : '0.0';
		$this->rel_cdb_off_age_25_35 = (!empty($this->age_25_35)) ? number_format($this->cdb_off_age_25_35 / $this->age_25_35 * 100, 1, '.', '') : '0.0';
		$this->rel_cdb_off_age_36_45 = (!empty($this->age_36_45)) ? number_format($this->cdb_off_age_36_45 / $this->age_36_45 * 100, 1, '.', '') : '0.0';
		$this->rel_cdb_off_age_46_55 = (!empty($this->age_46_55)) ? number_format($this->cdb_off_age_46_55 / $this->age_46_55 * 100, 1, '.', '') : '0.0';
		$this->rel_cdb_off_age_55up = (!empty($this->age_55up)) ? number_format($this->cdb_off_age_55up / $this->age_55up * 100, 1, '.', '') : '0.0';

		$this->rel_sick_age_25down = (!empty($this->age_25down)) ? number_format($this->sick_age_25down / $this->age_25down * 100, 1, '.', '') : '0.0';
		$this->rel_sick_age_25_35 = (!empty($this->age_25_35)) ? number_format($this->sick_age_25_35 / $this->age_25_35 * 100, 1, '.', '') : '0.0';
		$this->rel_sick_age_36_45 = (!empty($this->age_36_45)) ? number_format($this->sick_age_36_45 / $this->age_36_45 * 100, 1, '.', '') : '0.0';
		$this->rel_sick_age_46_55 = (!empty($this->age_46_55)) ? number_format($this->sick_age_46_55 / $this->age_46_55 * 100, 1, '.', '') : '0.0';
		$this->rel_sick_age_55up = (!empty($this->age_55up)) ? number_format($this->sick_age_55up / $this->age_55up * 100, 1, '.', '') : '0.0';

		$this->freq_primary_charts_age_25down = (!empty($this->age_25down)) ? number_format($this->primary_charts_age_25down / $this->age_25down * 100, 1, '.', '') : '0.0';
		$this->freq_primary_charts_age_25_35 =(!empty($this->age_25_35))?number_format($this->primary_charts_age_25_35 / $this->age_25_35 * 100, 1, '.', '') :'0.0';
		$this->freq_primary_charts_age_36_45 =(!empty($this->age_36_45))?number_format($this->primary_charts_age_36_45 / $this->age_36_45 * 100, 1, '.', '') :'0.0';
		$this->freq_primary_charts_age_46_55 =(!empty($this->age_46_55))?number_format($this->primary_charts_age_46_55 / $this->age_46_55 * 100, 1, '.', '') :'0.0';
		$this->freq_primary_charts_age_55up = (!empty($this->age_55up)) ? number_format($this->primary_charts_age_55up / $this->age_55up * 100, 1, '.', '') : '0.0';

		$this->freq_days_off_age_25down = (!empty($this->age_25down)) ? number_format($this->days_off_age_25down / $this->age_25down * 100, 1, '.', '') : '0.0';
		$this->freq_days_off_age_25_35 = (!empty($this->age_25_35)) ? number_format($this->days_off_age_25_35 / $this->age_25_35 * 100, 1, '.', '') : '0.0';
		$this->freq_days_off_age_36_45 = (!empty($this->age_36_45)) ? number_format($this->days_off_age_36_45 / $this->age_36_45 * 100, 1, '.', '') : '0.0';
		$this->freq_days_off_age_46_55 = (!empty($this->age_46_55)) ? number_format($this->days_off_age_46_55 / $this->age_46_55 * 100, 1, '.', '') : '0.0';
		$this->freq_days_off_age_55up = (!empty($this->age_55up)) ? number_format($this->days_off_age_55up / $this->age_55up * 100, 1, '.', '') : '0.0';

		$this->avg_length_of_chart_age_25down = (!empty($this->primary_charts_age_25down)) ? number_format($this->days_off_age_25down / $this->primary_charts_age_25down, 1, '.', '') : '0.0';
		$this->avg_length_of_chart_age_25_35 = (!empty($this->primary_charts_age_25_35)) ? number_format($this->days_off_age_25_35 / $this->primary_charts_age_25_35, 1, '.', '') : '0.0';
		$this->avg_length_of_chart_age_36_45 = (!empty($this->primary_charts_age_36_45)) ? number_format($this->days_off_age_36_45 / $this->primary_charts_age_36_45, 1, '.', '') : '0.0';
		$this->avg_length_of_chart_age_46_55 = (!empty($this->primary_charts_age_46_55)) ? number_format($this->days_off_age_46_55 / $this->primary_charts_age_46_55, 1, '.', '') : '0.0';
		$this->avg_length_of_chart_age_55up = (!empty($this->primary_charts_age_55up)) ? number_format($this->days_off_age_55up / $this->primary_charts_age_55up, 1, '.', '') : '0.0';
		// Professional groups
		$this->rel_sick_progroup_0 = (!empty($this->progroup_0)) ? number_format($this->sick_progroup_0 / $this->progroup_0 * 100, 1, '.', '') : '0.0';
		$this->rel_sick_progroup_1 = (!empty($this->progroup_1)) ? number_format($this->sick_progroup_1 / $this->progroup_1 * 100, 1, '.', '') : '0.0';
		$this->rel_sick_progroup_2 = (!empty($this->progroup_2)) ? number_format($this->sick_progroup_2 / $this->progroup_2 * 100, 1, '.', '') : '0.0';
		$this->rel_sick_progroup_3 = (!empty($this->progroup_3)) ? number_format($this->sick_progroup_3 / $this->progroup_3 * 100, 1, '.', '') : '0.0';
		$this->rel_sick_progroup_4 = (!empty($this->progroup_4)) ? number_format($this->sick_progroup_4 / $this->progroup_4 * 100, 1, '.', '') : '0.0';
		$this->rel_sick_progroup_5 = (!empty($this->progroup_5)) ? number_format($this->sick_progroup_5 / $this->progroup_5 * 100, 1, '.', '') : '0.0';
		$this->freq_primary_charts_progroup_0 = (!empty($this->progroup_0)) ? number_format($this->primary_charts_progroup_0 / $this->progroup_0 * 100, 1, '.', '') : '0.0';
		$this->freq_primary_charts_progroup_1 = (!empty($this->progroup_1)) ? number_format($this->primary_charts_progroup_1 / $this->progroup_1 * 100, 1, '.', '') : '0.0';
		$this->freq_primary_charts_progroup_2 = (!empty($this->progroup_2)) ? number_format($this->primary_charts_progroup_2 / $this->progroup_2 * 100, 1, '.', '') : '0.0';
		$this->freq_primary_charts_progroup_3 = (!empty($this->progroup_3)) ? number_format($this->primary_charts_progroup_3 / $this->progroup_3 * 100, 1, '.', '') : '0.0';
		$this->freq_primary_charts_progroup_4 = (!empty($this->progroup_4)) ? number_format($this->primary_charts_progroup_4 / $this->progroup_4 * 100, 1, '.', '') : '0.0';
		$this->freq_primary_charts_progroup_5 = (!empty($this->progroup_5)) ? number_format($this->primary_charts_progroup_5 / $this->progroup_5 * 100, 1, '.', '') : '0.0';
		$this->freq_days_off_progroup_0 = (!empty($this->progroup_0)) ? number_format($this->days_off_progroup_0 / $this->progroup_0 * 100, 1, '.', '') : '0.0';
		$this->freq_days_off_progroup_1 = (!empty($this->progroup_1)) ? number_format($this->days_off_progroup_1 / $this->progroup_1 * 100, 1, '.', '') : '0.0';
		$this->freq_days_off_progroup_2 = (!empty($this->progroup_2)) ? number_format($this->days_off_progroup_2 / $this->progroup_2 * 100, 1, '.', '') : '0.0';
		$this->freq_days_off_progroup_3 = (!empty($this->progroup_3)) ? number_format($this->days_off_progroup_3 / $this->progroup_3 * 100, 1, '.', '') : '0.0';
		$this->freq_days_off_progroup_4 = (!empty($this->progroup_4)) ? number_format($this->days_off_progroup_4 / $this->progroup_4 * 100, 1, '.', '') : '0.0';
		$this->freq_days_off_progroup_5 = (!empty($this->progroup_5)) ? number_format($this->days_off_progroup_5 / $this->progroup_5 * 100, 1, '.', '') : '0.0';
		$this->avg_length_of_chart_progroup_0 = (!empty($this->primary_charts_progroup_0)) ? number_format($this->days_off_progroup_0 / $this->primary_charts_progroup_0, 1, '.', '') : '0.0';
		$this->avg_length_of_chart_progroup_1 = (!empty($this->primary_charts_progroup_1)) ? number_format($this->days_off_progroup_1 / $this->primary_charts_progroup_1, 1, '.', '') : '0.0';
		$this->avg_length_of_chart_progroup_2 = (!empty($this->primary_charts_progroup_2)) ? number_format($this->days_off_progroup_2 / $this->primary_charts_progroup_2, 1, '.', '') : '0.0';
		$this->avg_length_of_chart_progroup_3 = (!empty($this->primary_charts_progroup_3)) ? number_format($this->days_off_progroup_3 / $this->primary_charts_progroup_3, 1, '.', '') : '0.0';
		$this->avg_length_of_chart_progroup_4 = (!empty($this->primary_charts_progroup_4)) ? number_format($this->days_off_progroup_4 / $this->primary_charts_progroup_4, 1, '.', '') : '0.0';
		$this->avg_length_of_chart_progroup_5 = (!empty($this->primary_charts_progroup_5)) ? number_format($this->days_off_progroup_5 / $this->primary_charts_progroup_5, 1, '.', '') : '0.0';
		$this->rel_cdb_off_progroup_0 = (!empty($this->progroup_0)) ? number_format($this->cdb_off_progroup_0 / $this->progroup_0 * 100, 1, '.', '') : '0.0';
		$this->rel_cdb_off_progroup_1 = (!empty($this->progroup_1)) ? number_format($this->cdb_off_progroup_1 / $this->progroup_1 * 100, 1, '.', '') : '0.0';
		$this->rel_cdb_off_progroup_2 = (!empty($this->progroup_2)) ? number_format($this->cdb_off_progroup_2 / $this->progroup_2 * 100, 1, '.', '') : '0.0';
		$this->rel_cdb_off_progroup_3 = (!empty($this->progroup_3)) ? number_format($this->cdb_off_progroup_3 / $this->progroup_3 * 100, 1, '.', '') : '0.0';
		$this->rel_cdb_off_progroup_4 = (!empty($this->progroup_4)) ? number_format($this->cdb_off_progroup_4 / $this->progroup_4 * 100, 1, '.', '') : '0.0';
		$this->rel_cdb_off_progroup_5 = (!empty($this->progroup_5)) ? number_format($this->cdb_off_progroup_5 / $this->progroup_5 * 100, 1, '.', '') : '0.0';
		$this->rel_charts_per_worker_progroup_0 = (!empty($this->sick_progroup_0)) ? number_format($this->primary_charts_progroup_0 / $this->sick_progroup_0, 1, '.', '') : '0.0';
		$this->rel_charts_per_worker_progroup_1 = (!empty($this->sick_progroup_1)) ? number_format($this->primary_charts_progroup_1 / $this->sick_progroup_1, 1, '.', '') : '0.0';
		$this->rel_charts_per_worker_progroup_2 = (!empty($this->sick_progroup_2)) ? number_format($this->primary_charts_progroup_2 / $this->sick_progroup_2, 1, '.', '') : '0.0';
		$this->rel_charts_per_worker_progroup_3 = (!empty($this->sick_progroup_3)) ? number_format($this->primary_charts_progroup_3 / $this->sick_progroup_3, 1, '.', '') : '0.0';
		$this->rel_charts_per_worker_progroup_4 = (!empty($this->sick_progroup_4)) ? number_format($this->primary_charts_progroup_4 / $this->sick_progroup_4, 1, '.', '') : '0.0';
		$this->rel_charts_per_worker_progroup_5 = (!empty($this->sick_progroup_5)) ? number_format($this->primary_charts_progroup_5 / $this->sick_progroup_5, 1, '.', '') : '0.0';
		$this->rel_days_off_per_worker_progroup_0 = (!empty($this->sick_progroup_0)) ? number_format($this->days_off_progroup_0 / $this->sick_progroup_0, 1, '.', '') : '0.0';
		$this->rel_days_off_per_worker_progroup_1 = (!empty($this->sick_progroup_1)) ? number_format($this->days_off_progroup_1 / $this->sick_progroup_1, 1, '.', '') : '0.0';
		$this->rel_days_off_per_worker_progroup_2 = (!empty($this->sick_progroup_2)) ? number_format($this->days_off_progroup_2 / $this->sick_progroup_2, 1, '.', '') : '0.0';
		$this->rel_days_off_per_worker_progroup_3 = (!empty($this->sick_progroup_3)) ? number_format($this->days_off_progroup_3 / $this->sick_progroup_3, 1, '.', '') : '0.0';
		$this->rel_days_off_per_worker_progroup_4 = (!empty($this->sick_progroup_4)) ? number_format($this->days_off_progroup_4 / $this->sick_progroup_4, 1, '.', '') : '0.0';
		$this->rel_days_off_per_worker_progroup_5 = (!empty($this->sick_progroup_5)) ? number_format($this->days_off_progroup_5 / $this->sick_progroup_5, 1, '.', '') : '0.0';

		if($this->_hasProGroups()) {
			foreach ($this->progroups as $key => $progroup) {
				$sick_progroup = (isset($this->sick_progroups[$key])) ? $this->sick_progroups[$key] : 0;
				$primary_charts_progroup = (isset($this->primary_charts_progroups[$key])) ? $this->primary_charts_progroups[$key] : 0;
				$days_off_progroup = (isset($this->days_off_progroups[$key])) ? $this->days_off_progroups[$key] : 0;
				$cdb_off_progroup = (isset($this->cdb_off_progroups[$key])) ? $this->cdb_off_progroups[$key] : 0;

				$this->rel_sick_progroups[$key] = (!empty($progroup)) ? number_format($sick_progroup / $progroup * 100, 1, '.', '') : '0.0';
				$this->freq_primary_charts_progroups[$key] = (!empty($progroup)) ? number_format($primary_charts_progroup / $progroup * 100, 1, '.', '') : '0.0';
				$this->freq_days_off_progroups[$key] = (!empty($progroup)) ? number_format($days_off_progroup / $progroup * 100, 1, '.', '') : '0.0';
				$this->avg_length_of_chart_progroups[$key] = (!empty($primary_charts_progroup)) ? number_format($days_off_progroup / $primary_charts_progroup, 1, '.', '') : '0.0';
				$this->rel_cdb_off_progroups[$key] = (!empty($progroup)) ? number_format($cdb_off_progroup / $progroup * 100, 1, '.', '') : '0.0';
				$this->rel_charts_per_worker_progroups[$key] = (!empty($sick_progroup)) ? number_format($primary_charts_progroup / $sick_progroup, 1, '.', '') : '0.0';
				$this->rel_days_off_per_worker_progroups[$key] = (!empty($sick_progroup)) ? number_format($days_off_progroup / $sick_progroup, 1, '.', '') : '0.0';
			}
		}
	}

	private function _assignWorkerAgeToGroup($worker_age = 0, $count_as = 1, $pref = 'no_sick_age', $suf = '_men') {
		// ages
		if($worker_age < 25) { $this->{$pref.'_25down'.$suf} += $count_as; }
		elseif ($worker_age >= 25 && $worker_age <= 35 ) { $this->{$pref.'_25_35'.$suf} += $count_as; }
		elseif ($worker_age > 35 && $worker_age <= 45 ) { $this->{$pref.'_36_45'.$suf} += $count_as; }
		elseif ($worker_age > 45 && $worker_age <= 55 ) { $this->{$pref.'_46_55'.$suf} += $count_as; }
		else { $this->{$pref.'_55up'.$suf} += $count_as; }
	}

	private function _hasProGroups() {
		return !empty($this->progroups);
	}

	public function getHtmlTables() {
		ob_start();
		?>
		<table width="100%" border="1" align="center" cellpadding="0" cellspacing="0">
		  <tr>
		    <td rowspan="2">&nbsp;</td>
		    <td colspan="3"><div align="center"><strong>средно списъчен състав</strong></div></td>
		    <td colspan="5"><div align="center"><strong>възрастови групи</strong></div></td>
		    <td colspan="3"><div align="center"><strong>групи по общ трудов стаж</strong></div></td>
		    <td colspan="3"><div align="center"><strong>групи по специален трудов стаж</strong></div></td>
		    <td colspan="5"><div align="center"><strong>основни професионални групи</strong></div></td>
		    <?php if($cnt = count($this->progroups)) { ?>
		    <td colspan="<?=$cnt?>"><div align="center"><strong>основни професионални групи</strong></div></td>		
		    <?php } ?>
		  </tr>
		  <tr>
		    <td><div align="center"><strong>общ брой</strong></div></td>
		    <td><div align="center"><strong>мъже</strong></div></td>
		    <td><div align="center"><strong>жени</strong></div></td>
		    <td><div align="center"><strong>до 25</strong></div></td>
		    <td><div align="center"><strong>25-35</strong></div></td>
		    <td><div align="center"><strong>36-45</strong></div></td>
		    <td><div align="center"><strong>46-55</strong></div></td>
		    <td><div align="center"><strong>над 55</strong></div></td>
		    <td><div align="center"><strong>до 5</strong></div></td>
		    <td><div align="center"><strong>5-10</strong></div></td>
		    <td><div align="center"><strong>над 10</strong></div></td>
		    <td><div align="center"><strong>до 3</strong></div></td>
		    <td><div align="center"><strong>3-10</strong></div></td>
		    <td><div align="center"><strong>над 10</strong></div></td>
		    <td><div align="center"><strong>1ва</strong></div></td>
		    <td><div align="center"><strong>2ра</strong></div></td>
		    <td><div align="center"><strong>3та</strong></div></td>
		    <td><div align="center"><strong>4та</strong></div></td>
		    <td><div align="center"><strong>5та</strong></div></td>
		    <?php
		    if($this->_hasProGroups()) {
		    	foreach ($this->progroups as $key => $val) {
		    		$converter = new ConvertRoman($key);
		    		echo '<td><div align="center"><strong>'.$converter->result().'</strong></div></td>';
		    	}
		    }
		    ?>
		  </tr>
		  <tr>
		    <td>общ брой</td>
		    <td align="right"><?=$this->avg_workers?></td>
		    <td align="right"><?=$this->avg_men?></td>
		    <td align="right"><?=$this->avg_women?></td>
		    <td align="right"><?=$this->age_25down?></td>
		    <td align="right"><?=$this->age_25_35?></td>
		    <td align="right"><?=$this->age_36_45?></td>
		    <td align="right"><?=$this->age_46_55?></td>
		    <td align="right"><?=$this->age_55up?></td>
		    <td align="right"><?=$this->service_5down?></td>
		    <td align="right"><?=$this->service_5_10?></td>
		    <td align="right"><?=$this->service_10up?></td>
		    <td align="right">-</td>
		    <td align="right">-</td>
		    <td align="right">-</td>
		    <td align="right"><?=$this->progroup_1?></td>
		    <td align="right"><?=$this->progroup_2?></td>
		    <td align="right"><?=$this->progroup_3?></td>
		    <td align="right"><?=$this->progroup_4?></td>
		    <td align="right"><?=$this->progroup_5?></td>
		    <?php
		    if($this->_hasProGroups()) {
		    	foreach ($this->progroups as $key => $val) {
		    		echo '<td align="right">'.$val.'</td>';
		    	}
		    }
		    ?>
		  </tr>
		  <tr>
		    <td>боледували</td>
		    <td align="right"><?=$this->sick_anual_workers?></td>
		    <td align="right"><?=$this->sick_anual_men?></td>
		    <td align="right"><?=$this->sick_anual_women?></td>
		    <td align="right"><?=$this->sick_age_25down?></td>
		    <td align="right"><?=$this->sick_age_25_35?></td>
		    <td align="right"><?=$this->sick_age_36_45?></td>
		    <td align="right"><?=$this->sick_age_46_55?></td>
		    <td align="right"><?=$this->sick_age_55up?></td>
		    <td align="right">-</td>
		    <td align="right">-</td>
		    <td align="right">-</td>
		    <td align="right">-</td>
		    <td align="right">-</td>
		    <td align="right">-</td>
		    <td align="right"><?=$this->sick_progroup_1?></td>
		    <td align="right"><?=$this->sick_progroup_2?></td>
		    <td align="right"><?=$this->sick_progroup_3?></td>
		    <td align="right"><?=$this->sick_progroup_4?></td>
		    <td align="right"><?=$this->sick_progroup_5?></td>
		    <?php
		    if($this->_hasProGroups()) {
		    	foreach ($this->progroups as $key => $val) {
		    		$val = (isset($this->sick_progroups[$key])) ? $this->sick_progroups[$key] : 0;
		    		echo '<td align="right">'.$val.'</td>';
		    	}
		    }
		    ?>
		  </tr>
		  <tr>
		    <td>неболедували</td>
		    <td align="right"><?=$this->no_sick_anual_workers?></td>
		    <td align="right"><?=$this->no_sick_anual_men?></td>
		    <td align="right"><?=$this->no_sick_anual_women?></td>
		    <td align="right"><?=$this->no_sick_age_25down?></td>
		    <td align="right"><?=$this->no_sick_age_25_35?></td>
		    <td align="right"><?=$this->no_sick_age_36_45?></td>
		    <td align="right"><?=$this->no_sick_age_46_55?></td>
		    <td align="right"><?=$this->no_sick_age_55up?></td>
		    <td align="right">-</td>
		    <td align="right">-</td>
		    <td align="right">-</td>
		    <td align="right">-</td>
		    <td align="right">-</td>
		    <td align="right">-</td>
		    <td align="right"><?=$this->no_sick_progroup_1?></td>
		    <td align="right"><?=$this->no_sick_progroup_2?></td>
		    <td align="right"><?=$this->no_sick_progroup_3?></td>
		    <td align="right"><?=$this->no_sick_progroup_4?></td>
		    <td align="right"><?=$this->no_sick_progroup_5?></td>
		    <?php
		    if($this->_hasProGroups()) {
		    	foreach ($this->progroups as $key => $val) {
		    		$val = (isset($this->no_sick_progroups[$key])) ? $this->no_sick_progroups[$key] : 0;
		    		echo '<td align="right">'.$val.'</td>';
		    	}
		    }
		    ?>
		  </tr>
		  <tr>
		    <td>брой първични случаи</td>
		    <td align="right"><?=$this->primary_charts?></td>
		    <td align="right"><?=$this->primary_charts_men?></td>
		    <td align="right"><?=$this->primary_charts_women?></td>
		    <td align="right"><?=$this->primary_charts_age_25down?></td>
		    <td align="right"><?=$this->primary_charts_age_25_35?></td>
		    <td align="right"><?=$this->primary_charts_age_36_45?></td>
		    <td align="right"><?=$this->primary_charts_age_46_55?></td>
		    <td align="right"><?=$this->primary_charts_age_55up?></td>
		    <td align="right">-</td>
		    <td align="right">-</td>
		    <td align="right">-</td>
		    <td align="right">-</td>
		    <td align="right">-</td>
		    <td align="right">-</td>
		    <td align="right"><?=$this->primary_charts_progroup_1?></td>
		    <td align="right"><?=$this->primary_charts_progroup_2?></td>
		    <td align="right"><?=$this->primary_charts_progroup_3?></td>
		    <td align="right"><?=$this->primary_charts_progroup_4?></td>
		    <td align="right"><?=$this->primary_charts_progroup_5?></td>
		    <?php
		    if($this->_hasProGroups()) {
		    	foreach ($this->progroups as $key => $val) {
		    		$val = (isset($this->primary_charts_progroups[$key])) ? $this->primary_charts_progroups[$key] : 0;
		    		echo '<td align="right">'.$val.'</td>';
		    	}
		    }
		    ?>
		  </tr>
		  <tr>
		    <td>дни трудозагуба</td>
		    <td align="right"><?=$this->days_off?></td>
		    <td align="right"><?=$this->days_off_men?></td>
		    <td align="right"><?=$this->days_off_women?></td>
		    <td align="right"><?=$this->days_off_age_25down?></td>
		    <td align="right"><?=$this->days_off_age_25_35?></td>
		    <td align="right"><?=$this->days_off_age_36_45?></td>
		    <td align="right"><?=$this->days_off_age_46_55?></td>
		    <td align="right"><?=$this->days_off_age_55up?></td>
		    <td align="right">-</td>
		    <td align="right">-</td>
		    <td align="right">-</td>
		    <td align="right">-</td>
		    <td align="right">-</td>
		    <td align="right">-</td>
		    <td align="right"><?=$this->days_off_progroup_1?></td>
		    <td align="right"><?=$this->days_off_progroup_2?></td>
		    <td align="right"><?=$this->days_off_progroup_3?></td>
		    <td align="right"><?=$this->days_off_progroup_4?></td>
		    <td align="right"><?=$this->days_off_progroup_5?></td>
		    <?php
		    if($this->_hasProGroups()) {
		    	foreach ($this->progroups as $key => $val) {
		    		$val = (isset($this->days_off_progroups[$key])) ? $this->days_off_progroups[$key] : 0;
		    		echo '<td align="right">'.$val.'</td>';
		    	}
		    }
		    ?>
		  </tr>
		  <tr>
		    <td>брой ЧДБ</td>
		    <td align="right"><?=$this->cdb_off?></td>
		    <td align="right"><?=$this->cdb_off_men?></td>
		    <td align="right"><?=$this->cdb_off_women?></td>
		    <td align="right"><?=$this->cdb_off_age_25down?></td>
		    <td align="right"><?=$this->cdb_off_age_25_35?></td>
		    <td align="right"><?=$this->cdb_off_age_36_45?></td>
		    <td align="right"><?=$this->cdb_off_age_46_55?></td>
		    <td align="right"><?=$this->cdb_off_age_55up?></td>
		    <td align="right">-</td>
		    <td align="right">-</td>
		    <td align="right">-</td>
		    <td align="right">-</td>
		    <td align="right">-</td>
		    <td align="right">-</td>
		    <td align="right"><?=$this->cdb_off_progroup_1?></td>
		    <td align="right"><?=$this->cdb_off_progroup_2?></td>
		    <td align="right"><?=$this->cdb_off_progroup_3?></td>
		    <td align="right"><?=$this->cdb_off_progroup_4?></td>
		    <td align="right"><?=$this->cdb_off_progroup_5?></td>
		    <?php
		    if($this->_hasProGroups()) {
		    	foreach ($this->progroups as $key => $val) {
		    		$val = (isset($this->cdb_off_progroups[$key])) ? $this->cdb_off_progroups[$key] : 0;
		    		echo '<td align="right">'.$val.'</td>';
		    	}
		    }
		    ?>
		  </tr>
		</table>
		<hr />
		<table border="1" cellpadding="0" cellspacing="0">
		  <tr align="center" valign="middle">
		    <td><strong>Показатели
		      / признаци</strong></td>
		    <td><strong>Относителен дял на боледувалите лица </strong></td>
		    <td><strong>Честота на случаите </strong></td>
		    <td><strong>Честота на дните </strong></td>
		    <td><strong>Средна продължителност на един случай </strong></td>
		    <td><strong>Относителен дял на ЧДБЛ в % </strong></td>
		    <td><strong>Случаи на едно боледувало лице </strong></td>
		    <td><strong>Дни на едно боледувало лице </strong></td>
		  </tr>
		  <tr>
		    <td valign="top"><p> общ брой</p></td>
		    <td valign="top"><p align="right"><?=$this->rel_sick_anual_workers?></p></td>
		    <td valign="top"><p align="right"><?=$this->freq_primary_charts?></p></td>
		    <td valign="top"><p align="right"><?=$this->freq_days_off?></p></td>
		    <td valign="top"><p align="right"><?=$this->avg_length_of_chart?></p></td>
		    <td valign="top"><p align="right"><?=$this->rel_cdb_off?></p></td>
		    <td valign="top"><p align="right"><?=$this->rel_charts_per_worker?></p></td>
		    <td valign="top"><p align="right"><?=$this->rel_days_off_per_worker?></p></td>
		  </tr>		  
		  <tr>
		    <td valign="top"><p> мъже</p></td>
		    <td valign="top"><p align="right"><?=$this->rel_sick_anual_men?></p></td>
		    <td valign="top"><p align="right"><?=$this->freq_primary_charts_men?></p></td>
		    <td valign="top"><p align="right"><?=$this->freq_days_off_men?></p></td>
		    <td valign="top"><p align="right"><?=$this->avg_length_of_chart_men?></p></td>
		    <td valign="top"><p align="right"><?=$this->rel_cdb_off_men?></p></td>
		    <td valign="top"><p align="right"><?=$this->rel_charts_per_worker_men?></p></td>
		    <td valign="top"><p align="right"><?=$this->rel_days_off_per_worker_men?></p></td>
		  </tr>
		  <tr>
		    <td valign="top"><p> жени</p></td>
		    <td valign="top"><p align="right"><?=$this->rel_sick_anual_women?></p></td>
		    <td valign="top"><p align="right"><?=$this->freq_primary_charts_women?></p></td>
		    <td valign="top"><p align="right"><?=$this->freq_days_off_women?></p></td>
		    <td valign="top"><p align="right"><?=$this->avg_length_of_chart_women?></p></td>
		    <td valign="top"><p align="right"><?=$this->rel_cdb_off_women?></p></td>
		    <td valign="top"><p align="right"><?=$this->rel_charts_per_worker_women?></p></td>
		    <td valign="top"><p align="right"><?=$this->rel_days_off_per_worker_women?></p></td>
		  </tr>
		  <tr>
		    <td valign="top"><p><strong>Възрастови групи:</strong></p></td>
		    <td valign="top" colspan="8"><p align="right">&nbsp;</p></td>
		  </tr>
		  <tr>
		    <td valign="top"><p>до 25 години</p></td>
		    <td valign="top"><p align="right"><?=$this->rel_sick_age_25down?></p></td>
		    <td valign="top"><p align="right"><?=$this->freq_primary_charts_age_25down?></p></td>
		    <td valign="top"><p align="right"><?=$this->freq_days_off_age_25down?></p></td>
		    <td valign="top"><p align="right"><?=$this->avg_length_of_chart_age_25down?></p></td>
		    <td valign="top"><p align="right"><?=$this->rel_cdb_off_age_25down?></p></td>
		    <td valign="top"><p align="right"><?=$this->rel_charts_per_worker_age_25down?></p></td>
		    <td valign="top"><p align="right"><?=$this->rel_days_off_per_worker_25down?></p></td>
		  </tr>
		  <tr>
		    <td valign="top"><p>25 – 35 години</p></td>
		    <td valign="top"><p align="right"><?=$this->rel_sick_age_25_35?></p></td>
		    <td valign="top"><p align="right"><?=$this->freq_primary_charts_age_25_35?></p></td>
		    <td valign="top"><p align="right"><?=$this->freq_days_off_age_25_35?></p></td>
		    <td valign="top"><p align="right"><?=$this->avg_length_of_chart_age_25_35?></p></td>
		    <td valign="top"><p align="right"><?=$this->rel_cdb_off_age_25_35?></p></td>
		    <td valign="top"><p align="right"><?=$this->rel_charts_per_worker_age_25_35?></p></td>
		    <td valign="top"><p align="right"><?=$this->rel_days_off_per_worker_25_35?></p></td>
		  </tr>
		  <tr>
		    <td valign="top"><p>36 – 45 години</p></td>
		    <td valign="top"><p align="right"><?=$this->rel_sick_age_36_45?></p></td>
		    <td valign="top"><p align="right"><?=$this->freq_primary_charts_age_36_45?></p></td>
		    <td valign="top"><p align="right"><?=$this->freq_days_off_age_36_45?></p></td>
		    <td valign="top"><p align="right"><?=$this->avg_length_of_chart_age_36_45?></p></td>
		    <td valign="top"><p align="right"><?=$this->rel_cdb_off_age_36_45?></p></td>
		    <td valign="top"><p align="right"><?=$this->rel_charts_per_worker_age_36_45?></p></td>
		    <td valign="top"><p align="right"><?=$this->rel_days_off_per_worker_36_45?></p></td>
		  </tr>
		  <tr>
		    <td valign="top"><p>46 – 55 години</p></td>
		    <td valign="top"><p align="right"><?=$this->rel_sick_age_46_55?></p></td>
		    <td valign="top"><p align="right"><?=$this->freq_primary_charts_age_46_55?></p></td>
		    <td valign="top"><p align="right"><?=$this->freq_days_off_age_46_55?></p></td>
		    <td valign="top"><p align="right"><?=$this->avg_length_of_chart_age_46_55?></p></td>
		    <td valign="top"><p align="right"><?=$this->rel_cdb_off_age_46_55?></p></td>
		    <td valign="top"><p align="right"><?=$this->rel_charts_per_worker_age_46_55?></p></td>
		    <td valign="top"><p align="right"><?=$this->rel_days_off_per_worker_46_55?></p></td>
		  </tr>
		  <tr>
		    <td valign="top"><p>над 55 години</p></td>
		    <td valign="top"><p align="right"><?=$this->rel_sick_age_55up?></p></td>
		    <td valign="top"><p align="right"><?=$this->freq_primary_charts_age_55up?></p></td>
		    <td valign="top"><p align="right"><?=$this->freq_days_off_age_55up?></p></td>
		    <td valign="top"><p align="right"><?=$this->avg_length_of_chart_age_55up?></p></td>
		    <td valign="top"><p align="right"><?=$this->rel_cdb_off_age_55up?></p></td>
		    <td valign="top"><p align="right"><?=$this->rel_charts_per_worker_age_55up?></p></td>
		    <td valign="top"><p align="right"><?=$this->rel_days_off_per_worker_55up?></p></td>
		  </tr>
		  <?php if($this->_hasProGroups()) { ?>
		  <tr>
		    <td valign="top"><p><strong>Професионални групи:</strong></p></td>
		    <td valign="top" colspan="8"><p align="right">&nbsp;</p></td>
		  </tr>
		  <tr>
		    <td valign="top"><p>І – ва група</p></td>
		    <td valign="top"><p align="right"><?=$this->rel_sick_progroup_1?></p></td>
		    <td valign="top"><p align="right"><?=$this->freq_primary_charts_progroup_1?></p></td>
		    <td valign="top"><p align="right"><?=$this->freq_days_off_progroup_1?></p></td>
		    <td valign="top"><p align="right"><?=$this->avg_length_of_chart_progroup_1?></p></td>
		    <td valign="top"><p align="right"><?=$this->rel_cdb_off_progroup_1?></p></td>
		    <td valign="top"><p align="right"><?=$this->rel_charts_per_worker_progroup_1?></p></td>
		    <td valign="top"><p align="right"><?=$this->rel_days_off_per_worker_progroup_1?></p></td>
		  </tr>
		  <tr>
		    <td valign="top"><p>ІІ – ра група</p></td>
		    <td valign="top"><p align="right"><?=$this->rel_sick_progroup_2?></p></td>
		    <td valign="top"><p align="right"><?=$this->freq_primary_charts_progroup_2?></p></td>
		    <td valign="top"><p align="right"><?=$this->freq_days_off_progroup_2?></p></td>
		    <td valign="top"><p align="right"><?=$this->avg_length_of_chart_progroup_2?></p></td>
		    <td valign="top"><p align="right"><?=$this->rel_cdb_off_progroup_2?></p></td>
		    <td valign="top"><p align="right"><?=$this->rel_charts_per_worker_progroup_2?></p></td>
		    <td valign="top"><p align="right"><?=$this->rel_days_off_per_worker_progroup_2?></p></td>
		  </tr>
		  <tr>
		    <td valign="top"><p>ІІІ – та група</p></td>
		    <td valign="top"><p align="right"><?=$this->rel_sick_progroup_3?></p></td>
		    <td valign="top"><p align="right"><?=$this->freq_primary_charts_progroup_3?></p></td>
		    <td valign="top"><p align="right"><?=$this->freq_days_off_progroup_3?></p></td>
		    <td valign="top"><p align="right"><?=$this->avg_length_of_chart_progroup_3?></p></td>
		    <td valign="top"><p align="right"><?=$this->rel_cdb_off_progroup_3?></p></td>
		    <td valign="top"><p align="right"><?=$this->rel_charts_per_worker_progroup_3?></p></td>
		    <td valign="top"><p align="right"><?=$this->rel_days_off_per_worker_progroup_3?></p></td>
		  </tr>
		  <tr>
		    <td valign="top"><p>ІV – та група</p></td>
		    <td valign="top"><p align="right"><?=$this->rel_sick_progroup_4?></p></td>
		    <td valign="top"><p align="right"><?=$this->freq_primary_charts_progroup_4?></p></td>
		    <td valign="top"><p align="right"><?=$this->freq_days_off_progroup_4?></p></td>
		    <td valign="top"><p align="right"><?=$this->avg_length_of_chart_progroup_4?></p></td>
		    <td valign="top"><p align="right"><?=$this->rel_cdb_off_progroup_4?></p></td>
		    <td valign="top"><p align="right"><?=$this->rel_charts_per_worker_progroup_4?></p></td>
		    <td valign="top"><p align="right"><?=$this->rel_days_off_per_worker_progroup_4?></p></td>
		  </tr>
		  <tr>
		    <td valign="top"><p>V – та група</p></td>
		    <td valign="top"><p align="right"><?=$this->rel_sick_progroup_5?></p></td>
		    <td valign="top"><p align="right"><?=$this->freq_primary_charts_progroup_5?></p></td>
		    <td valign="top"><p align="right"><?=$this->freq_days_off_progroup_5?></p></td>
		    <td valign="top"><p align="right"><?=$this->avg_length_of_chart_progroup_5?></p></td>
		    <td valign="top"><p align="right"><?=$this->rel_cdb_off_progroup_5?></p></td>
		    <td valign="top"><p align="right"><?=$this->rel_charts_per_worker_progroup_5?></p></td>
		    <td valign="top"><p align="right"><?=$this->rel_days_off_per_worker_progroup_5?></p></td>
		  </tr>
		  <tr>
		    <td valign="top" colspan="8">&nbsp;</td>
		  </tr>
		  <?php foreach ($this->progroups as $key => $val) { ?>
		  <tr>
		    <td valign="top"><p><?php $converter = new ConvertRoman($key); echo $converter->result(); ?> група</p></td>
		    <td valign="top"><p align="right"><?=$this->rel_sick_progroups[$key]?></p></td>
		    <td valign="top"><p align="right"><?=$this->freq_primary_charts_progroups[$key]?></p></td>
		    <td valign="top"><p align="right"><?=$this->freq_days_off_progroups[$key]?></p></td>
		    <td valign="top"><p align="right"><?=$this->avg_length_of_chart_progroups[$key]?></p></td>
		    <td valign="top"><p align="right"><?=$this->rel_cdb_off_progroups[$key]?></p></td>
		    <td valign="top"><p align="right"><?=$this->rel_charts_per_worker_progroups[$key]?></p></td>
		    <td valign="top"><p align="right"><?=$this->rel_days_off_per_worker_progroups[$key]?></p></td>
		  </tr>
		  <?php } ?>
		  <?php } ?>
		</table>
		<?php
		return ob_get_clean();
	}

	public function getBasicTable() {
		ob_start();
		?>
  <table class=MsoTableGrid border=1 cellspacing=0 cellpadding=0 width="111%"
 style='width:111.62%;border-collapse:collapse;border:none'>
    <tr>
      <td width="18%" rowspan=2 style='width:18.52%;border:solid windowtext 1.0pt;
  background:#CCFFCC;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'>&nbsp;</span></p></td>
      <td width="20%" colspan=3 style='width:20.86%;border:solid windowtext 1.0pt;
  border-left:none;background:#CCFFCC;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'>средно списъчен състав</span></p></td>
      <td width="34%" colspan=5 style='width:34.68%;border:solid windowtext 1.0pt;
  border-left:none;background:#CCFFCC;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'>възрастови групи</span></p></td>
      <td width="25%" colspan=5 style='width:25.92%;border:solid windowtext 1.0pt;
  border-left:none;background:#CCFFCC;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'>основни професионални групи</span></p></td>
    </tr>
    <tr>
      <td width="6%" style='width:6.94%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  background:#CCFFCC;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'>общ брой</span></p></td>
      <td width="6%" style='width:6.94%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  background:#CCFFCC;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'>мъже</span></p></td>
      <td width="6%" style='width:6.98%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  background:#CCFFCC;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'>жени</span></p></td>
      <td width="6%" style='width:6.6%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;background:#CCFFCC;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'>до 25</span></p></td>
      <td width="6%" style='width:6.6%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;background:#CCFFCC;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'>25-35</span></p></td>
      <td width="6%" style='width:6.6%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;background:#CCFFCC;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'>36-45</span></p></td>
      <td width="6%" style='width:6.6%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;background:#CCFFCC;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'>46-55</span></p></td>
      <td width="8%" style='width:8.3%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;background:#CCFFCC;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'>над 55</span></p></td>
      <td width="5%" style='width:5.2%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;background:#CCFFCC;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'>1ва</span></p></td>
      <td width="5%" style='width:5.2%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;background:#CCFFCC;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'>2ра</span></p></td>
      <td width="5%" style='width:5.2%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;background:#CCFFCC;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'>3та</span></p></td>
      <td width="5%" style='width:5.2%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;background:#CCFFCC;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'>4та</span></p></td>
      <td width="5%" style='width:5.12%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  background:#CCFFCC;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'>5та</span></p></td>
    </tr>
    <tr>
      <td width="18%" style='width:18.52%;border:solid windowtext 1.0pt;border-top:
  none;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal><span style='font-size:10.0pt'>общ брой</span></p></td>
      <td width="6%" style='width:6.94%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->avg_workers?></span></p></td>
      <td width="6%" style='width:6.94%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->avg_men?></span></p></td>
      <td width="6%" style='width:6.98%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->avg_women?></span></p></td>
      <td width="6%" style='width:6.6%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->age_25down?></span></p></td>
      <td width="6%" style='width:6.6%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->age_25_35?></span></p></td>
      <td width="6%" style='width:6.6%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->age_36_45?></span></p></td>
      <td width="6%" style='width:6.6%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->age_46_55?></span></p></td>
      <td width="8%" style='width:8.3%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->age_55up?></span></p></td>
      <td width="5%" style='width:5.2%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->progroup_1?></span></p></td>
      <td width="5%" style='width:5.2%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->progroup_2?></span></p></td>
      <td width="5%" style='width:5.2%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->progroup_3?></span></p></td>
      <td width="5%" style='width:5.2%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->progroup_4?></span></p></td>
      <td width="5%" style='width:5.12%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->progroup_5?></span></p></td>
    </tr>
    <tr>
      <td width="18%" style='width:18.52%;border:solid windowtext 1.0pt;border-top:
  none;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal><span style='font-size:10.0pt'>боледували</span></p></td>
      <td width="6%" style='width:6.94%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->sick_anual_workers?></span></p></td>
      <td width="6%" style='width:6.94%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->sick_anual_men?></span></p></td>
      <td width="6%" style='width:6.98%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->sick_anual_women?></span></p></td>
      <td width="6%" style='width:6.6%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->sick_age_25down?></span></p></td>
      <td width="6%" style='width:6.6%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->sick_age_25_35?></span></p></td>
      <td width="6%" style='width:6.6%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->sick_age_36_45?></span></p></td>
      <td width="6%" style='width:6.6%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->sick_age_46_55?></span></p></td>
      <td width="8%" style='width:8.3%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->sick_age_55up?></span></p></td>
      <td width="5%" style='width:5.2%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->sick_progroup_1?></span></p></td>
      <td width="5%" style='width:5.2%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->sick_progroup_2?></span></p></td>
      <td width="5%" style='width:5.2%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->sick_progroup_3?></span></p></td>
      <td width="5%" style='width:5.2%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->sick_progroup_4?></span></p></td>
      <td width="5%" style='width:5.12%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->sick_progroup_5?></span></p></td>
    </tr>
    <tr>
      <td width="18%" style='width:18.52%;border:solid windowtext 1.0pt;border-top:
  none;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal><span style='font-size:10.0pt'>неболедували</span></p></td>
      <td width="6%" style='width:6.94%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->no_sick_anual_workers?></span></p></td>
      <td width="6%" style='width:6.94%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->no_sick_anual_men?></span></p></td>
      <td width="6%" style='width:6.98%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->no_sick_anual_women?></span></p></td>
      <td width="6%" style='width:6.6%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->no_sick_age_25down?></span></p></td>
      <td width="6%" style='width:6.6%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->no_sick_age_25_35?></span></p></td>
      <td width="6%" style='width:6.6%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->no_sick_age_36_45?></span></p></td>
      <td width="6%" style='width:6.6%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->no_sick_age_46_55?></span></p></td>
      <td width="8%" style='width:8.3%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->no_sick_age_55up?></span></p></td>
      <td width="5%" style='width:5.2%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->no_sick_progroup_1?></span></p></td>
      <td width="5%" style='width:5.2%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->no_sick_progroup_2?></span></p></td>
      <td width="5%" style='width:5.2%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->no_sick_progroup_3?></span></p></td>
      <td width="5%" style='width:5.2%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->no_sick_progroup_4?></span></p></td>
      <td width="5%" style='width:5.12%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->no_sick_progroup_5?></span></p></td>
    </tr>
    <tr>
      <td width="18%" style='width:18.52%;border:solid windowtext 1.0pt;border-top:
  none;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal><span style='font-size:10.0pt'>брой първични случаи</span></p></td>
      <td width="6%" style='width:6.94%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->primary_charts?></span></p></td>
      <td width="6%" style='width:6.94%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->primary_charts_men?></span></p></td>
      <td width="6%" style='width:6.98%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->primary_charts_women?></span></p></td>
      <td width="6%" style='width:6.6%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->primary_charts_age_25down?></span></p></td>
      <td width="6%" style='width:6.6%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->primary_charts_age_25_35?></span></p></td>
      <td width="6%" style='width:6.6%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->primary_charts_age_36_45?></span></p></td>
      <td width="6%" style='width:6.6%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->primary_charts_age_46_55?></span></p></td>
      <td width="8%" style='width:8.3%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->primary_charts_age_55up?></span></p></td>
      <td width="5%" style='width:5.2%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->primary_charts_progroup_1?></span></p></td>
      <td width="5%" style='width:5.2%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->primary_charts_progroup_2?></span></p></td>
      <td width="5%" style='width:5.2%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->primary_charts_progroup_3?></span></p></td>
      <td width="5%" style='width:5.2%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->primary_charts_progroup_4?></span></p></td>
      <td width="5%" style='width:5.12%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->primary_charts_progroup_5?></span></p></td>
    </tr>
    <tr>
      <td width="18%" style='width:18.52%;border:solid windowtext 1.0pt;border-top:
  none;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal><span style='font-size:10.0pt'>дни трудозагуба</span></p></td>
      <td width="6%" style='width:6.94%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->days_off?></span></p></td>
      <td width="6%" style='width:6.94%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->days_off_men?></span></p></td>
      <td width="6%" style='width:6.98%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->days_off_women?></span></p></td>
      <td width="6%" style='width:6.6%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->days_off_age_25down?></span></p></td>
      <td width="6%" style='width:6.6%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->days_off_age_25_35?></span></p></td>
      <td width="6%" style='width:6.6%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->days_off_age_36_45?></span></p></td>
      <td width="6%" style='width:6.6%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->days_off_age_46_55?></span></p></td>
      <td width="8%" style='width:8.3%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->days_off_age_55up?></span></p></td>
      <td width="5%" style='width:5.2%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->days_off_progroup_1?></span></p></td>
      <td width="5%" style='width:5.2%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->days_off_progroup_2?></span></p></td>
      <td width="5%" style='width:5.2%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->days_off_progroup_3?></span></p></td>
      <td width="5%" style='width:5.2%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->days_off_progroup_4?></span></p></td>
      <td width="5%" style='width:5.12%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->days_off_progroup_5?></span></p></td>
    </tr>
    <tr>
      <td width="18%" style='width:18.52%;border:solid windowtext 1.0pt;border-top:
  none;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal><span style='font-size:10.0pt'>брой ЧДБ</span></p></td>
      <td width="6%" style='width:6.94%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->cdb_off?></span></p></td>
      <td width="6%" style='width:6.94%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->cdb_off_men?></span></p></td>
      <td width="6%" style='width:6.98%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->cdb_off_women?></span></p></td>
      <td width="6%" style='width:6.6%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->cdb_off_age_25down?></span></p></td>
      <td width="6%" style='width:6.6%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->cdb_off_age_25_35?></span></p></td>
      <td width="6%" style='width:6.6%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->cdb_off_age_36_45?></span></p></td>
      <td width="6%" style='width:6.6%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->cdb_off_age_46_55?></span></p></td>
      <td width="8%" style='width:8.3%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->cdb_off_age_55up?></span></p></td>
      <td width="5%" style='width:5.2%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->cdb_off_progroup_1?></span></p></td>
      <td width="5%" style='width:5.2%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->cdb_off_progroup_2?></span></p></td>
      <td width="5%" style='width:5.2%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->cdb_off_progroup_3?></span></p></td>
      <td width="5%" style='width:5.2%;border-top:none;border-left:none;border-bottom:
  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->cdb_off_progroup_4?></span></p></td>
      <td width="5%" style='width:5.12%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->cdb_off_progroup_5?></span></p></td>
    </tr>
  </table>
		<?php
		return ob_get_clean();
	}

	public function getAnaliticsTable() {
		ob_start();
		?>
  <table class=MsoTableGrid border=1 cellspacing=0 cellpadding=0 width="111%"
 style='width:111.8%;border-collapse:collapse;border:none'>
    <tr>
      <td width="16%" style='width:16.4%;border:solid windowtext 1.0pt;background:
  #CCFFCC;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'>Показатели / признаци</span></p></td>
      <td width="13%" style='width:13.24%;border:solid windowtext 1.0pt;border-left:
  none;background:#CCFFCC;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'>Относителен дял на боледувалите лица</span></p></td>
      <td width="9%" style='width:9.42%;border:solid windowtext 1.0pt;border-left:
  none;background:#CCFFCC;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=center style='text-align:center'><strong><span
  style='font-size:10.0pt;font-weight:normal'>Честота на случаите</span></strong></p></td>
      <td width="8%" style='width:8.56%;border:solid windowtext 1.0pt;border-left:
  none;background:#CCFFCC;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'>Честота на дните</span></p></td>
      <td width="16%" style='width:16.68%;border:solid windowtext 1.0pt;border-left:
  none;background:#CCFFCC;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'>Средна продължителност на един случай</span></p></td>
      <td width="12%" style='width:12.74%;border:solid windowtext 1.0pt;border-left:
  none;background:#CCFFCC;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'>Относителен дял на ЧДБЛ в %</span></p></td>
      <td width="11%" style='width:11.48%;border:solid windowtext 1.0pt;border-left:
  none;background:#CCFFCC;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'>Случаи на едно боледувало лице</span></p></td>
      <td width="11%" style='width:11.48%;border:solid windowtext 1.0pt;border-left:
  none;background:#CCFFCC;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:10.0pt'>Дни на едно боледувало лице</span></p></td>
    </tr>
    <tr>
      <td width="16%" style='width:16.4%;border:solid windowtext 1.0pt;border-top:
  none;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal><span style='font-size:10.0pt'>общ брой</span></p></td>
      <td width="13%" style='width:13.24%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->rel_sick_anual_workers?></span></p></td>
      <td width="9%" style='width:9.42%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->freq_primary_charts?></span></p></td>
      <td width="8%" style='width:8.56%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->freq_days_off?></span></p></td>
      <td width="16%" style='width:16.68%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->avg_length_of_chart?></span></p></td>
      <td width="12%" style='width:12.74%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->rel_cdb_off?></span></p></td>
      <td width="11%" style='width:11.48%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->rel_charts_per_worker?></span></p></td>
      <td width="11%" style='width:11.48%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->rel_days_off_per_worker?></span></p></td>
    </tr>
    <tr>
      <td width="16%" style='width:16.4%;border:solid windowtext 1.0pt;border-top:
  none;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal><span style='font-size:10.0pt'>мъже</span></p></td>
      <td width="13%" style='width:13.24%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->rel_sick_anual_men?></span></p></td>
      <td width="9%" style='width:9.42%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->freq_primary_charts_men?></span></p></td>
      <td width="8%" style='width:8.56%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->freq_days_off_men?></span></p></td>
      <td width="16%" style='width:16.68%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->avg_length_of_chart_men?></span></p></td>
      <td width="12%" style='width:12.74%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->rel_cdb_off_men?></span></p></td>
      <td width="11%" style='width:11.48%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->rel_charts_per_worker_men?></span></p></td>
      <td width="11%" style='width:11.48%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->rel_days_off_per_worker_men?></span></p></td>
    </tr>
    <tr>
      <td width="16%" style='width:16.4%;border:solid windowtext 1.0pt;border-top:
  none;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal><span style='font-size:10.0pt'>жени</span></p></td>
      <td width="13%" style='width:13.24%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->rel_sick_anual_women?></span></p></td>
      <td width="9%" style='width:9.42%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->freq_primary_charts_women?></span></p></td>
      <td width="8%" style='width:8.56%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->freq_days_off_women?></span></p></td>
      <td width="16%" style='width:16.68%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->avg_length_of_chart_women?></span></p></td>
      <td width="12%" style='width:12.74%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->rel_cdb_off_women?></span></p></td>
      <td width="11%" style='width:11.48%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->rel_charts_per_worker_women?></span></p></td>
      <td width="11%" style='width:11.48%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->rel_days_off_per_worker_women?></span></p></td>
    </tr>
    <tr>
      <td width="100%" colspan=8 style='width:100.0%;border:solid windowtext 1.0pt;
  border-top:none;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>Възрастови групи</span></b></p></td>
    </tr>
    <tr>
      <td width="16%" style='width:16.4%;border:solid windowtext 1.0pt;border-top:
  none;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal><span style='font-size:10.0pt'>до 25 години</span></p></td>
      <td width="13%" style='width:13.24%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->rel_sick_age_25down?></span></p></td>
      <td width="9%" style='width:9.42%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->freq_primary_charts_age_25down?></span></p></td>
      <td width="8%" style='width:8.56%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->freq_days_off_age_25down?></span></p></td>
      <td width="16%" style='width:16.68%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->avg_length_of_chart_age_25down?></span></p></td>
      <td width="12%" style='width:12.74%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->rel_cdb_off_age_25down?></span></p></td>
      <td width="11%" style='width:11.48%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->rel_charts_per_worker_age_25down?></span></p></td>
      <td width="11%" style='width:11.48%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->rel_days_off_per_worker_25down?></span></p></td>
    </tr>
    <tr>
      <td width="16%" style='width:16.4%;border:solid windowtext 1.0pt;border-top:
  none;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal><span style='font-size:10.0pt'>25 – 35 години</span></p></td>
      <td width="13%" style='width:13.24%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->rel_sick_age_25_35?></span></p></td>
      <td width="9%" style='width:9.42%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->freq_primary_charts_age_25_35?></span></p></td>
      <td width="8%" style='width:8.56%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->freq_days_off_age_25_35?></span></p></td>
      <td width="16%" style='width:16.68%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->avg_length_of_chart_age_25_35?></span></p></td>
      <td width="12%" style='width:12.74%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->rel_cdb_off_age_25_35?></span></p></td>
      <td width="11%" style='width:11.48%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->rel_charts_per_worker_age_25_35?></span></p></td>
      <td width="11%" style='width:11.48%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->rel_days_off_per_worker_25_35?></span></p></td>
    </tr>
    <tr>
      <td width="16%" style='width:16.4%;border:solid windowtext 1.0pt;border-top:
  none;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal><span style='font-size:10.0pt'>36 – 45 години</span></p></td>
      <td width="13%" style='width:13.24%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->rel_sick_age_36_45?></span></p></td>
      <td width="9%" style='width:9.42%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->freq_primary_charts_age_36_45?></span></p></td>
      <td width="8%" style='width:8.56%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->freq_days_off_age_36_45?></span></p></td>
      <td width="16%" style='width:16.68%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->avg_length_of_chart_age_36_45?></span></p></td>
      <td width="12%" style='width:12.74%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->rel_cdb_off_age_36_45?></span></p></td>
      <td width="11%" style='width:11.48%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->rel_charts_per_worker_age_36_45?></span></p></td>
      <td width="11%" style='width:11.48%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->rel_days_off_per_worker_36_45?></span></p></td>
    </tr>
    <tr>
      <td width="16%" style='width:16.4%;border:solid windowtext 1.0pt;border-top:
  none;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal><span style='font-size:10.0pt'>46 – 55 години</span></p></td>
      <td width="13%" style='width:13.24%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->rel_sick_age_46_55?></span></p></td>
      <td width="9%" style='width:9.42%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->freq_primary_charts_age_46_55?></span></p></td>
      <td width="8%" style='width:8.56%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->freq_days_off_age_46_55?></span></p></td>
      <td width="16%" style='width:16.68%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->avg_length_of_chart_age_46_55?></span></p></td>
      <td width="12%" style='width:12.74%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->rel_cdb_off_age_46_55?></span></p></td>
      <td width="11%" style='width:11.48%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->rel_charts_per_worker_age_46_55?></span></p></td>
      <td width="11%" style='width:11.48%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->rel_days_off_per_worker_46_55?></span></p></td>
    </tr>
    <tr>
      <td width="16%" style='width:16.4%;border:solid windowtext 1.0pt;border-top:
  none;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal><span style='font-size:10.0pt'>над 55 години</span></p></td>
      <td width="13%" style='width:13.24%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->rel_sick_age_55up?></span></p></td>
      <td width="9%" style='width:9.42%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->freq_primary_charts_age_55up?></span></p></td>
      <td width="8%" style='width:8.56%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->freq_days_off_age_55up?></span></p></td>
      <td width="16%" style='width:16.68%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->avg_length_of_chart_age_55up?></span></p></td>
      <td width="12%" style='width:12.74%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->rel_cdb_off_age_55up?></span></p></td>
      <td width="11%" style='width:11.48%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->rel_charts_per_worker_age_55up?></span></p></td>
      <td width="11%" style='width:11.48%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->rel_days_off_per_worker_55up?></span></p></td>
    </tr>
    <?php if($this->_hasProGroups()) { ?>
    <tr>
      <td width="100%" colspan=8 style='width:100.0%;border:solid windowtext 1.0pt;
  border-top:none;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:10.0pt'>Професионални групи</span></b></p></td>
    </tr>
    <?php foreach ($this->progroups as $key => $val) { ?>
    <tr>
      <td width="16%" style='width:16.4%;border:solid windowtext 1.0pt;border-top:
  none;padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal><span style='font-size:10.0pt'><?php $converter = new ConvertRoman($key); echo $converter->result(); ?> група</span></p></td>
      <td width="13%" style='width:13.24%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->rel_sick_progroups[$key]?></span></p></td>
      <td width="9%" style='width:9.42%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->freq_primary_charts_progroups[$key]?></span></p></td>
      <td width="8%" style='width:8.56%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->freq_days_off_progroups[$key]?></span></p></td>
      <td width="16%" style='width:16.68%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->avg_length_of_chart_progroups[$key]?></span></p></td>
      <td width="12%" style='width:12.74%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->rel_cdb_off_progroups[$key]?></span></p></td>
      <td width="11%" style='width:11.48%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->rel_charts_per_worker_progroups[$key]?></span></p></td>
      <td width="11%" style='width:11.48%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  padding:0cm 5.4pt 0cm 5.4pt'><p class=MsoNormal align=right style='text-align:right'><span
  style='font-size:10.0pt'><?=$this->rel_days_off_per_worker_progroups[$key]?></span></p></td>
    </tr>
    <?php } ?>
    <?php } ?>
  </table>
  	  <?php
  	  if($this->_hasProGroups()) {
  	  	echo '<p class=MsoNormal><u>Легенда:</u></p>';
  	  	$sql = "SELECT g.num AS num , g.name AS progroup_name
				FROM firm_positions p
				LEFT JOIN pro_groups g ON ( g.id = p.progroup )
				WHERE p.firm_id = $this->firm_id 
				AND p.progroup != 0
				GROUP BY g.num
				ORDER BY g.num";
  	  	$rows = $this->query($sql);
  	  	if(!empty($rows)) {
  	  		foreach ($rows as $row) {
  	  			if(empty($row['num'])) continue;
	  	  		$converter = new ConvertRoman($row['num']);
	  	  		$num = $converter->result();
  	  			echo '<p class=MsoNormal><span style=\'font-size:10.0pt\'>'.$num.' група: '.HTMLFormat($row['progroup_name']).'</span></p>';
  	  		}
  	  	}
  	  }
  	  
  	  return ob_get_clean();
	}

	// Честота на боледувалите работещи със заболяемост с временна неработоспособност
	public function freqSickWorkersTempDisability() {
		if(empty($this->rel_sick_anual_workers)) {
			return "<b style='mso-bidi-font-weight:normal'>Няма предоставени данни</b>";
		}
		$str = '';
		$freq = $this->rel_sick_anual_workers;
		if($freq < 45) {
			$str .= 'ниска';
		} elseif ($freq >= 45 && $freq <= 55) {
			$str .= 'средна';
		} else {
			$str .= 'висока';
		}
		return "<b style='mso-bidi-font-weight:normal'>$freq</b> ($str)";
	}
	
	// Честота на случаите с временна неработоспособност
	public function freqCasesTempDisability() {
		if(empty($this->freq_primary_charts)) {
			return "<b style='mso-bidi-font-weight:normal'>Няма предоставени данни</b>";
		}
		$str = '';
		$freq = $this->freq_primary_charts;
		if($freq < 60) {
			$str .= 'много ниска';
		} elseif ($freq >= 60 && $freq < 80) {
			$str .= 'ниска';
		} elseif ($freq >= 80 && $freq < 100) {
			$str .= 'средна';
		} elseif ($freq >= 100 && $freq < 120) {
			$str .= 'висока';
		} else {
			$str .= 'много висока';
		}
		return "<b style='mso-bidi-font-weight:normal'>$freq</b> ($str)";
	}
	
	// Честота на трудозагубите с временна неработоспособност
	public function freqDaysOffTempDisability() {
		if(empty($this->freq_days_off)) {
			return "<b style='mso-bidi-font-weight:normal'>Няма предоставени данни</b>";
		}
		$str = '';
		$freq = $this->freq_days_off;
		if($freq < 600) {
			$str .= 'много ниска';
		} elseif ($freq >= 600 && $freq < 800) {
			$str .= 'ниска';
		} elseif ($freq >= 800 && $freq < 1000) {
			$str .= 'средна';
		} elseif ($freq >= 1000 && $freq < 1200) {
			$str .= 'висока';
		} else {
			$str .= 'много висока';
		}
		return "<b style='mso-bidi-font-weight:normal'>$freq</b> ($str)";
	}
	
	// Относителен дял на често и дълго боледувалите работещи
	public function relativeShareLongDaysOff() {
		if(empty($this->rel_cdb_off)) {
			return "<b style='mso-bidi-font-weight:normal'>Няма предоставени данни</b>";
		}
		$str = '';
		$freq = $this->rel_cdb_off;
		if($freq < 30) {
			$str .= 'нисък';
		} elseif ($freq >= 30 && $freq < 60) {
			$str .= 'среден';
		} else {
			$str .= 'висок';
		}
		return "<b style='mso-bidi-font-weight:normal'>$freq%</b> ($str)";
	}
	
	// Относителен дял на краткосрочната временна неработоспособност
	public function relativeShareShortDaysOff() {
		if(empty($this->primary_charts_days_off_3down)) {
			return "<b style='mso-bidi-font-weight:normal'>Няма предоставени данни</b>";
		}
		$str = '';
		$freq = (!empty($this->avg_workers)) ? round(($this->primary_charts_days_off_3down / $this->avg_workers) * 100, 1) : 0;
		if(empty($freq)) {
			return "<b style='mso-bidi-font-weight:normal'>Няма предоставени данни</b>";
		}
		if($freq < 40) {
			$str .= 'нисък';
		} elseif ($freq >= 40 && $freq < 60) {
			$str .= 'среден';
		} else {
			$str .= 'висок';
		}
		return "<b style='mso-bidi-font-weight:normal'>$freq%</b> ($str)";
	}
	
	// Честота на работещите с професионални болести
	public function freqWorkersProDiseases() {
		if(empty($this->num_workers_pro_diseases)) {
			return "<b style='mso-bidi-font-weight:normal'>Няма предоставени данни</b>";
		}
		$str = '';
		$freq = (!empty($this->avg_workers)) ? round(($this->num_workers_pro_diseases / $this->avg_workers) * 100, 2) : 0;
		if(empty($freq)) {
			return "<b style='mso-bidi-font-weight:normal'>Няма предоставени данни</b>";
		}
		if($freq < 40) {
			$str .= 'нисък';
		} elseif ($freq >= 40 && $freq < 60) {
			$str .= 'среден';
		} else {
			$str .= 'висок';
		}
		return "<b style='mso-bidi-font-weight:normal'>$freq</b> ($str)";
	}
	
	// Честота на работещите с трудови злополуки
	public function freqWorkersLabourAccidents() {
		if(empty($this->num_workers_labour_accidents)) {
			return "<b style='mso-bidi-font-weight:normal'>Няма предоставени данни</b>";
		}
		$freq = (!empty($this->avg_workers)) ? round(($this->num_workers_labour_accidents / $this->avg_workers) * 100, 2) : 0;
		if(empty($freq)) {
			return "<b style='mso-bidi-font-weight:normal'>Няма предоставени данни</b>";
		}
		return "<b style='mso-bidi-font-weight:normal'>$freq</b>";
	}
	
	// Работещи с трудови злополуки по пол, длъжност, МКБ и т.н.
	public function getWorkersLabourAccidents() {
		$aWorkers = array();
		if(!empty($this->num_workers_labour_accidents_ary)) {
			foreach ($this->num_workers_labour_accidents_ary as $worker_id => $pair) {
				if(isset($this->workers[$worker_id])) {
					list($mkb_id, $source, $source_id) = explode('&', $pair);
					$this->workers[$worker_id]['mkb_id'] = $mkb_id;
					$this->workers[$worker_id]['source'] = $source;
					$this->workers[$worker_id]['source_id'] = $source_id;
					$tmp = array();
					foreach ($this->workers[$worker_id] as $key => $val) {
						if(is_numeric($key)) continue;
						$tmp[$key] = $val;	
					}
					$aWorkers[$worker_id] = $tmp;
				}
			}
		}
		return $aWorkers;
	}
	
	// Честота на работещите със заболяемост с трайна неработоспособност
	public function freqWorkersWithTelk() {
		if(empty($this->num_workers_with_telk)) {
			return "<b style='mso-bidi-font-weight:normal'>Няма предоставени данни</b>";
		}
		$freq = (!empty($this->avg_workers)) ? round(($this->num_workers_with_telk / $this->avg_workers) * 100, 2) : 0;
		if(empty($freq)) {
			return "<b style='mso-bidi-font-weight:normal'>Няма предоставени данни</b>";
		}
		return "<b style='mso-bidi-font-weight:normal'>$freq</b>";		
	}
	
	// Честота на лицата със заболявания, открити при проведените периодични медицински прегледи
	public function freqILLWorkersMedicalCheckups() {
		if(empty($this->num_workers_medical_checkups)) {
			return "<b style='mso-bidi-font-weight:normal'>Няма предоставени данни</b>";
		}
		$freq = (!empty($this->num_workers_medical_checkups))? round(($this->num_ill_workers_medical_checkups / $this->num_workers_medical_checkups) * 100, 2) : 0;
		if(empty($freq)) {
			return "<b style='mso-bidi-font-weight:normal'>Няма предоставени данни</b>";
		}
		return "<b style='mso-bidi-font-weight:normal'>$freq</b>";		
	}
	
	private function _getNosologicTblHeader($_label = 'Брой случаи') {
		$this->inc = 0;
		return <<< EOT
<table class=MsoTableGrid border=1 cellspacing=0 cellpadding=0 width="99%"
 style='width:99.18%;margin-left:1.9pt;border-collapse:collapse;border:none;
 mso-border-alt:solid windowtext .5pt;mso-yfti-tbllook:480;mso-padding-alt:
 0cm 5.4pt 0cm 5.4pt;mso-border-insideh:.5pt solid windowtext;mso-border-insidev:
 .5pt solid windowtext'>
 <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes'>
  <td width="67%" style='width:67.2%;border:solid windowtext 1.0pt;mso-border-alt:
  solid windowtext .5pt;background:#CCFFCC;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:14.0pt'>Наименование</span></b></p>
  </td>
  <td width="13%" style='width:13.02%;border:solid windowtext 1.0pt;border-left:
  none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  background:#CCFFCC;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:14.0pt'>МКБ</span></b></p>
  </td>
  <td width="19%" style='width:19.78%;border:solid windowtext 1.0pt;border-left:
  none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  background:#CCFFCC;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:14.0pt'>$_label</span></b></p>
  </td>
 </tr>
EOT;
	}
	
	private function _getNosologicTblClsRow($_class_inc, $class_name, $class_mkb, $cnt) {
		$i = $this->inc++;
		return <<< EOT
 <tr style='mso-yfti-irow:$i'>
  <td width="67%" valign=top style='width:67.2%;border:solid windowtext 1.0pt;
  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  background:#CCFFCC;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><b><span style='font-size:11.0pt'>$_class_inc. $class_name</span></b>
  <span style='font-size:11.0pt'><o:p></o:p></span></p>
  </td>
  <td width="13%" style='width:13.02%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;background:#CCFFCC;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:11.0pt'>$class_mkb</span></b><span style='font-size:11.0pt'><o:p></o:p></span></p>
  </td>
  <td width="19%" style='width:19.78%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;background:#CCFFCC;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:11.0pt'>$cnt</span></b><span style='font-size:11.0pt'><o:p></o:p></span></p>
  </td>
 </tr>
EOT;
	}
	
	private function _getNosologicTblGrpRow($_class_inc, $group_inc, $group_name, $group_mkb, $cnt) {
		$i = $this->inc++;
		return <<< EOT
 <tr style='mso-yfti-irow:$i'>
  <td width="67%" valign=top style='width:67.2%;border:solid windowtext 1.0pt;
  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  background:#E6E6E6;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><b><span style='font-size:11.0pt'>$_class_inc.$group_inc. $group_name<o:p></o:p></span></b></p>
  </td>
  <td width="13%" style='width:13.02%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;background:#E6E6E6;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:11.0pt;mso-bidi-font-weight:bold'>$group_mkb</span><span
  style='font-size:11.0pt'><o:p></o:p></span></b></p>
  </td>
  <td width="19%" style='width:19.78%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;background:#E6E6E6;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:11.0pt'>$cnt<o:p></o:p></span></b></p>
  </td>
 </tr>
EOT;
	}
	
	private function _getNosologicTblMkbRow($mkb_inc, $mkb_desc, $mkb_id, $cnt) {
		$i = $this->inc++;
		return <<< EOT
<tr style='mso-yfti-irow:$i'>
  <td width="67%" valign=top style='width:67.2%;border:solid windowtext 1.0pt;
  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><span style='font-size:11.0pt'>$mkb_inc. $mkb_desc<o:p></o:p></span></p>
  </td>
  <td width="13%" style='width:13.02%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:11.0pt;mso-bidi-font-weight:bold'>$mkb_id</span><span
  style='font-size:11.0pt'><o:p></o:p></span></p>
  </td>
  <td width="19%" style='width:19.78%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:11.0pt'>$cnt<o:p></o:p></span></p>
  </td>
 </tr>
EOT;
	}
	
	private function _getNosologicTblFooter($total) {
		$i = $this->inc++;
		return <<< EOT
<tr style='mso-yfti-irow:$i;mso-yfti-lastrow:yes'>
  <td width="67%" style='width:67.2%;border:solid windowtext 1.0pt;border-top:
  none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  background:#CCFFCC;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b><span
  style='font-size:11.0pt'>ВСИЧКО</span></b><b style='mso-bidi-font-weight:
  normal'><span style='font-size:11.0pt'><o:p></o:p></span></b></p>
  </td>
  <td width="13%" style='width:13.02%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;background:#CCFFCC;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b
  style='mso-bidi-font-weight:normal'><span style='font-size:11.0pt'><o:p>&nbsp;</o:p></span></b></p>
  </td>
  <td width="19%" style='width:19.78%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;background:#CCFFCC;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><b
  style='mso-bidi-font-weight:normal'><span style='font-size:11.0pt'>$total<o:p></o:p></span></b></p>
  </td>
 </tr>
</table>
EOT;
	}

	private function _getNosologicTable($sql, $lbl = 'Брой случаи') {
		$rows = $this->query($sql);
		if(!empty($rows)) {
			// Group by classes, groups, mkbs
			$data = array();
			$chart_data = array();
			$mkbClasses = array();
			foreach ($rows as $row) {
				if(empty($row['class_name'])) $row['class_name'] = '--';
				$data[$row['class_id']][$row['group_id']][] = $row;
				$mkbClasses[$row['class_id']] = $row['class_name'];
			}					
			$total = 0;
			$tbl = '';
			if(!empty($data)) {
				$class_inc = 1;
				$tbl .= $this->_getNosologicTblHeader($lbl);
				foreach ($data as $class_id => $groups) {
					$cls_cnt = 0;
					$tbGrp = '';
					foreach ($groups as $group_id => $lines) {
						$group_inc = 1;
						if(empty($lines[0]['group_name'])) $lines[0]['group_name'] = '--';
						$grp_cnt = 0;
						$tblMkb = '';
						for ($i = 0; $i < count($lines); $i++) {
							$grp_cnt += intval($lines[$i]['cnt']);
							$mkb_inc = $i + 1;
							$mkb_id = $lines[$i]['mkb_id'];
							$mkb_desc = $lines[$i]['mkb_desc'];
							$cnt = $lines[$i]['cnt'];
							$tblMkb .= $this->_getNosologicTblMkbRow($mkb_inc, $mkb_desc, $mkb_id, $cnt);
							//$chart_data[$mkb_id.' - '.$mkb_desc] = $cnt;
						}
						$cls_cnt += $grp_cnt;
						$converter = new ConvertRoman($class_inc);
						$_class_inc = $converter->result();
						list($group_name, $group_mkb) = $this->parse_group_mkb($lines[0]['group_name']);
						$tbGrp .= $this->_getNosologicTblGrpRow($_class_inc, $group_inc, $group_name, $group_mkb, $grp_cnt).$tblMkb;
						$group_inc++;
					}
					$total += $cls_cnt;
					list($class_name, $class_mkb) = $this->parse_group_mkb($mkbClasses[$class_id]);
					$converter = new ConvertRoman($class_inc);
					$_class_inc = $converter->result();
					$tblCls = $this->_getNosologicTblClsRow($_class_inc, $class_name, $class_mkb, $cls_cnt);
					$chart_data[$class_mkb.' - '.$class_name] = $cls_cnt;
					
					$tbl .= $tblCls.$tbGrp;
					$class_inc++;
				}
				$tbl .= $this->_getNosologicTblFooter($total);
			}
			return array('table' => $tbl, 'total' => $total, 'chart_data' => $chart_data);
		}
		return null;
	}
	
	// Структура на работещите с професионална заболяемост по нозология
	public function getWorkersProDiseasesStruct() {
		if(!empty($this->pro_diseases_by_worker)) {
			$sql = "SELECT w.`worker_id`, p.`position_name`
					FROM `firm_positions` p
					LEFT JOIN `firm_struct_map` m ON ( m.`position_id` = p.`position_id` )
					LEFT JOIN `workers` w ON ( w.`map_id` = m.`map_id` )
					WHERE w.`worker_id` IN ( ".implode(',', array_keys($this->pro_diseases_by_worker))." )
					ORDER BY p.`position_name`";
			$rows = $this->query($sql);
			ob_start();
			if(!empty($rows)) {
				?>
				<table class=MsoTableGrid border=1 cellspacing=0 cellpadding=0 width="100%"
				 style='width:100.0%;margin-left:1.9pt;border-collapse:collapse;border:none;
				 mso-border-alt:solid windowtext .5pt;mso-yfti-tbllook:480;mso-padding-alt:
				 0cm 5.4pt 0cm 5.4pt;mso-border-insideh:.5pt solid windowtext;mso-border-insidev:
				 .5pt solid windowtext'>
				 <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes'>
				  <td width=154 style='width:115.4pt;border:solid windowtext 1.0pt;mso-border-alt:
				  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
				  <p class=MsoNormal align=center style='text-align:center'>№ по ред</p>
				  </td>
				  <td width=154 style='width:115.4pt;border:solid windowtext 1.0pt;border-left:
				  none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
				  padding:0cm 5.4pt 0cm 5.4pt'>
				  <p class=MsoNormal align=center style='text-align:center'>Длъжност на работещите с професионална заболяемост</p>
				  </td>
				  <td width=154 style='width:115.15pt;border:solid windowtext 1.0pt;border-left:
				  none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
				  padding:0cm 5.4pt 0cm 5.4pt'>
				  <p class=MsoNormal align=center style='text-align:center'><span class=SpellE>Нозологична</span>
				  принадлежност <span lang=EN-US style='mso-ansi-language:EN-US'>(</span>код по</p>
				  <p class=MsoNormal align=center style='text-align:center'><span class=SpellE>МКБ-</span>10<span
				  lang=EN-US style='mso-ansi-language:EN-US'>)<o:p></o:p></span></p>
				  </td>
				 </tr>
				<?php
				$i = 1;
				foreach ($rows as $row) {
					$mkb = (isset($this->pro_diseases_by_worker[$row['worker_id']])) ? $this->pro_diseases_by_worker[$row['worker_id']] : '--';
					?>
				 <tr style='mso-yfti-irow:<?php echo $i; ?>'>
				  <td width=154 style='width:115.4pt;border:solid windowtext 1.0pt;border-top:
				  none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
				  padding:0cm 5.4pt 0cm 5.4pt'>
				  <p class=MsoNormal align=center style='text-align:center'><?php echo $i++; ?></p>
				  </td>
				  <td width=154 style='width:115.4pt;border-top:none;border-left:none;
				  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
				  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
				  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
				  <p class=MsoNormal align=center style='text-align:center'><?php echo $row['position_name']; ?></p>
				  </td>
				  <td width=154 style='width:115.15pt;border-top:none;border-left:none;
				  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
				  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
				  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
				  <p class=MsoNormal align=center style='text-align:center'><span lang=EN-US
				  style='mso-ansi-language:EN-US'><?php echo $mkb; ?></span></p>
				  </td>
				 </tr>
					<?php
					//echo $this->pro_diseases_by_worker[$row['worker_id']].' / '. $row['position_name'].'<br />';
				}
				?>
				</table>
				<?php
			}
			return ob_get_clean();
		}
		return '';
	}

	// Работещи с експертно решение на ТЕЛК/НЕЛК – брой и честота на заболяванията с трайна неработоспособност, професионални болести и трудови злополуки
	public function getTelkListDetailsTable() {
		$rows = $this->telks;
		
		$aWorkers = array();
		if(!empty($rows)) {
			foreach ($rows as $row) {
				$aWorkers[$row['worker_id']] = $row;
			}
		}
		$aTelksByPosition = array();
		$aProDiseases = array();
		$aWorkAccidents = array();
		if(!empty($aWorkers)) {
			foreach ($aWorkers as $row) {
				// Заболяемост с трайна неработоспособност
				$aTelksByPosition[$row['position_id']][] = $row;
				// Професионална заболяемост
				if(!empty($row['mkb_id_4'])) { $aProDiseases[$row['position_id']][] = $row; }
				// Трудова злополука
				if(!empty($row['mkb_id_3'])) { $aWorkAccidents[$row['position_id']][] = $row; }
			}
		}
		$rows = array();
		if(!empty($aTelksByPosition)) {
			foreach ($aTelksByPosition as $position_id => $row) { 
		  		$fld['position_name'] = (isset($row[0]['position_name'])) ? $row[0]['position_name'] : '--';
			  	$fld['cnt1'] = (isset($aTelksByPosition[$position_id])) ? count($aTelksByPosition[$position_id]) : 0;
			  	$fld['cnt2'] = (isset($aProDiseases[$position_id])) ? count($aProDiseases[$position_id]) : 0;
			  	$fld['cnt3'] = (isset($aWorkAccidents[$position_id])) ? count($aWorkAccidents[$position_id]) : 0;		  	
			  	$rows[] = $fld;
			}
		}
		if(!empty($rows)) {
			ob_start();
			?>
			<table class=MsoTableGrid border=1 cellspacing=0 cellpadding=0 width="100%"
			 style='width:100.08%;border-collapse:collapse;border:none;mso-border-alt:solid windowtext .5pt;
			 mso-yfti-tbllook:480;mso-padding-alt:0cm 5.4pt 0cm 5.4pt;mso-border-insideh:
			 .5pt solid windowtext;mso-border-insidev:.5pt solid windowtext'>
			 <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes'>
			  <td width=143 rowspan=2 style='width:107.15pt;border:solid windowtext 1.0pt;
			  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'>Професия</p>
			  </td>
			  <td width=159 colspan=2 style='width:119.2pt;border:solid windowtext 1.0pt;
			  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
			  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center;mso-pagination:none;
			  mso-layout-grid-align:none;text-autospace:none'><span class=SpellE>Заболяемост</span>
			  с трайна</p>
			  <p class=MsoNormal align=center style='text-align:center'>неработоспособност</p>
			  </td>
			  <td width=159 colspan=2 style='width:119.2pt;border:solid windowtext 1.0pt;
			  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
			  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center;mso-pagination:none;
			  mso-layout-grid-align:none;text-autospace:none'>Професионална</p>
			  <p class=MsoNormal align=center style='text-align:center'><span class=SpellE>заболяемост</span></p>
			  </td>
			  <td width=159 colspan=2 style='width:119.2pt;border:solid windowtext 1.0pt;
			  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
			  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'>Трудова злополука</p>
			  </td>
			 </tr>
			 <tr style='mso-yfti-irow:1'>
			  <td width=79 style='width:59.6pt;border-top:none;border-left:none;border-bottom:
			  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;mso-border-top-alt:
			  solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
			  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'>брой</p>
			  </td>
			  <td width=79 style='width:59.6pt;border-top:none;border-left:none;border-bottom:
			  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;mso-border-top-alt:
			  solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
			  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'>честота</p>
			  </td>
			  <td width=79 style='width:59.6pt;border-top:none;border-left:none;border-bottom:
			  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;mso-border-top-alt:
			  solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
			  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'>брой</p>
			  </td>
			  <td width=79 style='width:59.6pt;border-top:none;border-left:none;border-bottom:
			  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;mso-border-top-alt:
			  solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
			  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'>честота</p>
			  </td>
			  <td width=79 style='width:59.6pt;border-top:none;border-left:none;border-bottom:
			  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;mso-border-top-alt:
			  solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
			  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'>брой</p>
			  </td>
			  <td width=79 style='width:59.6pt;border-top:none;border-left:none;border-bottom:
			  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;mso-border-top-alt:
			  solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
			  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'>честота</p>
			  </td>
			 </tr>
			<?php
			$i = 2;
			$j = 1;
			$totalCnt1 = 0;
			$totalCnt2 = 0;
			$totalCnt3 = 0;
			$totalPercentCnt1 = 0;
			$totalPercentCnt2 = 0;
			$totalPercentCnt3 = 0;
			$avgWorkers = $this->avg_workers;
			
			foreach ($rows as $row) {
				$totalCnt1 += $row['cnt1'];
 				$totalCnt2 += $row['cnt2'];
		 		$totalCnt3 += $row['cnt3'];
		
		 		$percentCnt1 = (!empty($avgWorkers)) ? ($row['cnt1'] / $avgWorkers) * 100 : 0;
		 		$percentCnt2 = (!empty($avgWorkers)) ? ($row['cnt2'] / $avgWorkers) * 100 : 0;
		 		$percentCnt3 = (!empty($avgWorkers)) ? ($row['cnt3'] / $avgWorkers) * 100 : 0;
		
		 		$totalPercentCnt1 += $percentCnt1;
		 		$totalPercentCnt2 += $percentCnt2;
		 		$totalPercentCnt3 += $percentCnt3;
		 		?>
			  <tr style='mso-yfti-irow:<?php echo $i++; ?>'>
			  <td width=143 style='width:107.15pt;border:solid windowtext 1.0pt;border-top:
			  none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
			  padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal><?=$j++?>. <?=HTMLFormat($row['position_name'])?></p>
			  </td>
			  <td width=79 style='width:59.6pt;border-top:none;border-left:none;border-bottom:
			  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;mso-border-top-alt:
			  solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
			  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'><?=$row['cnt1']?></p>
			  </td>
			  <td width=79 style='width:59.6pt;border-top:none;border-left:none;border-bottom:
			  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;mso-border-top-alt:
			  solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
			  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'><?=round($percentCnt1, 2)?></p>
			  </td>
			  <td width=79 style='width:59.6pt;border-top:none;border-left:none;border-bottom:
			  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;mso-border-top-alt:
			  solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
			  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'><?=$row['cnt2']?></p>
			  </td>
			  <td width=79 style='width:59.6pt;border-top:none;border-left:none;border-bottom:
			  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;mso-border-top-alt:
			  solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
			  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'><?=round($percentCnt2, 2)?></p>
			  </td>
			  <td width=79 style='width:59.6pt;border-top:none;border-left:none;border-bottom:
			  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;mso-border-top-alt:
			  solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
			  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'><?=$row['cnt3']?></p>
			  </td>
			  <td width=79 style='width:59.6pt;border-top:none;border-left:none;border-bottom:
			  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;mso-border-top-alt:
			  solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
			  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'><?=round($percentCnt3, 2)?></p>
			  </td>
			 </tr>
		 		<?php
			 }
			 ?>
			 <tr style='mso-yfti-irow:<?php echo $i++; ?>;mso-yfti-lastrow:yes'>
			  <td width=143 style='width:107.15pt;border:solid windowtext 1.0pt;border-top:
			  none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
			  padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal>Общо</p>
			  </td>
			  <td width=79 style='width:59.6pt;border-top:none;border-left:none;border-bottom:
			  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;mso-border-top-alt:
			  solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
			  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'><?=$totalCnt1?></p>
			  </td>
			  <td width=79 style='width:59.6pt;border-top:none;border-left:none;border-bottom:
			  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;mso-border-top-alt:
			  solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
			  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'><?=round($totalPercentCnt1, 2)?></p>
			  </td>
			  <td width=79 style='width:59.6pt;border-top:none;border-left:none;border-bottom:
			  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;mso-border-top-alt:
			  solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
			  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'><?=$totalCnt2?></p>
			  </td>
			  <td width=79 style='width:59.6pt;border-top:none;border-left:none;border-bottom:
			  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;mso-border-top-alt:
			  solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
			  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'><?=round($totalPercentCnt2, 2)?></p>
			  </td>
			  <td width=79 style='width:59.6pt;border-top:none;border-left:none;border-bottom:
			  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;mso-border-top-alt:
			  solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
			  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'><?=$totalCnt3?></p>
			  </td>
			  <td width=79 style='width:59.6pt;border-top:none;border-left:none;border-bottom:
			  solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;mso-border-top-alt:
			  solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
			  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'><?=round($totalPercentCnt3, 2)?></p>
			  </td>
			 </tr>
			</table>
			 <?php
			return ob_get_clean();
		}
		return '';
	}

	// Абсолютен брой случаи (първични болнични листове) – общо и по нозологична структура, съгласно МКБ-10
	public function getPatientChartsByNumCasesTable() {
		$IDs = (empty($this->worker_ids)) ? '-1' : $this->worker_ids;
		// *** Patient charts
		$sql = "SELECT d.worker_id AS worker_id, cl.class_id AS class_id, cl.class_name AS class_name, 
				g.group_id AS group_id, g.group_name AS group_name, 
				d.mkb_id AS mkb_id, m.mkb_desc AS mkb_desc, COUNT(*) AS cnt
				FROM `patient_charts` d
				LEFT JOIN `mkb` m ON (m.mkb_id = d.mkb_id)
				LEFT JOIN `mkb_groups` g ON (g.group_id = m.group_id)
				LEFT JOIN `mkb_classes` cl ON (cl.class_id = g.class_id)
				WHERE d.`worker_id` IN ($IDs)
				AND ( d.`medical_types` = 'a:1:{i:0;s:1:\"1\";}' OR d.`medical_types` = 'a:1:{i:0;i:1;}' )
				AND (
					(julianday(d.`hospital_date_from`) >= julianday('$this->date_from')) AND (julianday(d.`hospital_date_from`) <= julianday('$this->date_to'))
				)
				GROUP BY d.mkb_id
				ORDER BY cl.class_id, g.group_id, cnt DESC, m.mkb_id";
		$data = $this->_getNosologicTable($sql, 'Брой случаи');
		return $data;
	}
	
	// Брой на дните с временна неработоспособност (общо от всички болнични листове – първични и продължения) – общо и по нозологична структура, съгласно МКБ-10
	public function getPatientChartsByDaysOffTable() {
		$IDs = (empty($this->worker_ids)) ? '-1' : $this->worker_ids;
		// *** Patient charts
		$sql = "SELECT d.worker_id AS worker_id, cl.class_id AS class_id, cl.class_name AS class_name, 
				g.group_id AS group_id, g.group_name AS group_name, 
				d.mkb_id AS mkb_id, m.mkb_desc AS mkb_desc, SUM(d.`days_off`) AS cnt
				FROM `patient_charts` d
				LEFT JOIN `mkb` m ON (m.mkb_id = d.mkb_id)
				LEFT JOIN `mkb_groups` g ON (g.group_id = m.group_id)
				LEFT JOIN `mkb_classes` cl ON (cl.class_id = g.class_id)
				WHERE d.`worker_id` IN ( $IDs )
				AND ( d.`medical_types` = 'a:1:{i:0;s:1:\"1\";}' OR d.`medical_types` = 'a:1:{i:0;s:1:\"2\";}' OR d.`medical_types` = 'a:1:{i:0;i:1;}' OR d.`medical_types` = 'a:1:{i:0;i:2;}' )
				AND (
					(julianday(d.`hospital_date_from`) >= julianday('$this->date_from')) AND (julianday(d.`hospital_date_from`) <= julianday('$this->date_to'))
				)
				GROUP BY d.mkb_id
				ORDER BY cl.class_id, g.group_id, cnt DESC, m.mkb_id";
		$data = $this->_getNosologicTable($sql, 'бр. дни ЗВН');
		return $data;
	}
	
	// $freq = 0 --> Работещи с 30 и повече дни временна неработоспособност от заболявания - пол, възраст, длъжност, диагнози
	// $freq = 1 --> Описание на често и дълго боледували работещи – брой, диагнози (код по МКБ-10)
	public function getWorkersDaysOff30upTable($freq = 0) {
		if(!empty($this->workers_days_off_30up)) {
			$rows = $this->workers_days_off_30up;
			
			// Sort array - http://php.net/manual/en/function.array-multisort.php
			$sex = array();
			$age = array();
			$position_name = array();
			$days_off = array();
			foreach ($rows as $key => $row) {
				if(empty($row['sex'])) $row['sex'] = 'М';
				if(empty($row['age'])) $row['age'] = 0;
				$sex[$key] = $row['sex'];
				$age[$key] = $row['age'];
				$position_name[$key] = $row['position_name'];				
				$days_off[$key] = $row['days_off'];				
			}
			array_multisort($sex, SORT_ASC, $age, SORT_ASC, $position_name, SORT_ASC, $days_off, SORT_ASC, $rows);
			
			ob_start();
			?>
			<table class=MsoTableGrid border=1 cellspacing=0 cellpadding=0 width="100%"
			 style='width:100.0%;border-collapse:collapse;border:none;mso-border-alt:solid windowtext .5pt;
			 mso-yfti-tbllook:480;mso-padding-alt:0cm 5.4pt 0cm 5.4pt;mso-border-insideh:
			 .5pt solid windowtext;mso-border-insidev:.5pt solid windowtext'>
			 <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes'>
			  <td width=91 style='width:68.4pt;border:solid windowtext 1.0pt;mso-border-alt:
			  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'>№ по ред</p>
			  </td>
			  <td width=84 style='width:63.0pt;border:solid windowtext 1.0pt;border-left:
			  none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
			  padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'>пол</p>
			  </td>
			  <td width=84 style='width:63.0pt;border:solid windowtext 1.0pt;border-left:
			  none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
			  padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'>възраст</p>
			  </td>
			  <td width=180 style='width:135.0pt;border:solid windowtext 1.0pt;border-left:
			  none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
			  padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'>Длъжност</p>
			  </td>
			  <td width=180 style='width:135.0pt;border:solid windowtext 1.0pt;border-left:
			  none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
			  padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'>Диагнози</p>
			  <p class=MsoNormal align=center style='text-align:center'>(код по <span
			  class=SpellE>МКБ-</span>10)</p>
			  </td>
			 </tr>
			<?php
			$i = 1;
			foreach ($rows as $row) {
				if(!$freq && empty($row['days_off'])) continue;
				?>
			 <tr style='mso-yfti-irow:<?=$i?>;mso-yfti-lastrow:yes'>
			  <td width=91 valign=top style='width:68.4pt;border:solid windowtext 1.0pt;
			  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
			  padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal><?=$i++?>.</p>
			  </td>
			  <td width=84 valign=top style='width:63.0pt;border-top:none;border-left:none;
			  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
			  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
			  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'><?=$row['sex']?></p>
			  </td>
			  <td width=84 valign=top style='width:63.0pt;border-top:none;border-left:none;
			  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
			  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
			  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'><?=((!empty($row['age'])) ? $row['age'].' г.' : '')?></p>
			  </td>
			  <td width=180 valign=top style='width:135.0pt;border-top:none;border-left:
			  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
			  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
			  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal><?=HTMLFormat($row['position_name'])?></p>
			  </td>
			  <td width=180 valign=top style='width:135.0pt;border-top:none;border-left:
			  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
			  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
			  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
			  <?php
			  if(!$freq) {
			  		echo '<p class=MsoNormal>'.$row['days_off'].' дни с трудозагуби - '.$row['mkbs'].'</p>';
			  } else {
			  		if(!empty($row['num_primary'])) echo '<p class=MsoNormal>Често ('.$row['num_primary'].' бр. първ. болнични) - '.$row['chart_mkbs'].'</p>';
			  		if(!empty($row['days_off'])) echo '<p class=MsoNormal>Дълго ('.$row['days_off'].' дни с трудозагуби) - '.$row['mkbs'].'</p>';
			  }
			  ?>
			  </td>
			 </tr>
				<?php	
			}
			?>
			</table>
			<?php
			return ob_get_clean();
		}
		return null;
	}
	
	// Структура на случаите/дните с временна неработоспособност по нозологична принадлежност
	public function getTmpUnableToWorkStructTable() {
		$rows = $this->patient_charts_by_mkb;
		if(!empty($rows)) {
			
			$data = array();
			foreach ($rows as $mkb_id => $row) {
				$num_cases = count($row);
				$num_days_off = 0;
				foreach ($row as $key => $fld) {
					$num_days_off += intval($fld['days_off']);
				}
				$data[$mkb_id] = array('num_cases' => $num_cases, 'num_days_off' => $num_days_off, 'mkb_id' => $mkb_id);
			}
			//ksort($data);
			
			// Sort array - http://php.net/manual/en/function.array-multisort.php
			$num_cases = array();
			$num_days_off = array();
			foreach ($data as $key => $row) {
				$num_cases[$key] = $row['num_cases'];
				$num_days_off[$key] = $row['num_days_off'];			
			}
			array_multisort($num_cases, SORT_DESC, $num_days_off, SORT_DESC, $data);
			
			ob_start();
			?>
			<table class=MsoTableGrid border=1 cellspacing=0 cellpadding=0 width="100%"
			 style='width:100.0%;margin-left:1.9pt;border-collapse:collapse;border:none;
			 mso-border-alt:solid windowtext .5pt;mso-yfti-tbllook:480;mso-padding-alt:
			 0cm 5.4pt 0cm 5.4pt;mso-border-insideh:.5pt solid windowtext;mso-border-insidev:
			 .5pt solid windowtext'>
			 <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes'>
			  <td width=154 style='width:115.4pt;border:solid windowtext 1.0pt;mso-border-alt:
			  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'>Брой случаи с
			  временна неработоспособност</p>
			  </td>
			  <td width=154 style='width:115.4pt;border:solid windowtext 1.0pt;border-left:
			  none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
			  padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'>Брой дни с временна
			  неработоспособност<span lang=EN-US style='mso-ansi-language:EN-US'><o:p></o:p></span></p>
			  </td>
			  <td width=154 style='width:115.15pt;border:solid windowtext 1.0pt;border-left:
			  none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
			  padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'><span class=SpellE>Нозологична</span>
			  принадлежност <span lang=EN-US style='mso-ansi-language:EN-US'>(</span>код по</p>
			  <p class=MsoNormal align=center style='text-align:center'><span class=SpellE>МКБ-</span>10<span
			  lang=EN-US style='mso-ansi-language:EN-US'>)<o:p></o:p></span></p>
			  </td>
			 </tr>
			<?php
			$i = 1;
			foreach ($data as $mkb_id => $row) {
				?>
			 <tr style='mso-yfti-irow:<?php echo $i++; ?>'>
			  <td width=154 style='width:115.4pt;border:solid windowtext 1.0pt;border-top:
			  none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
			  padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'><?=$row['num_cases']?></p>
			  </td>
			  <td width=154 style='width:115.4pt;border-top:none;border-left:none;
			  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
			  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
			  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'><?=$row['num_days_off']?></p>
			  </td>
			  <td width=154 style='width:115.15pt;border-top:none;border-left:none;
			  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
			  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
			  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'><span lang=EN-US
			  style='mso-ansi-language:EN-US'><?=$mkb_id?></span></p>
			  </td>
			 </tr>
				<?php	
			}
			$this->tmp_unable_to_work_struct_chart_data = $data;
			?>
			</table>
			<?php
			return ob_get_clean();
		}
		return '';
	}
	
	public function getTmpUnableToWorkStructChartData() {
		if(empty($this->tmp_unable_to_work_struct_chart_data)) {
			$this->getTmpUnableToWorkStructTable();
		}
		return $this->tmp_unable_to_work_struct_chart_data;
	}
	
	// Описание на работещите с експертно решение на ТЕЛК/НЕЛК
	public function getWorkersWithTelkTable() {
		$rows = $this->telks;
		
		if(!empty($rows)) {
			$workers = array();			
			foreach ($rows as $row) {
				$workers[$row['worker_id']][$row['mkb_id_1']] = 1;
			}
			$rows = array();
			foreach ($workers as $worker_id => $mkbs) {
				$rows[$worker_id] = implode('; ', array_keys($mkbs));
			}
			$telks = array();
			foreach ($rows as $worker_id => $mkbs) {
				$telks[$mkbs] = (isset($telks[$mkbs])) ? ++$telks[$mkbs] : 1;	
			}
			$rows = array();
			foreach ($telks as $mkbs => $num_workers) {
				$rows[] = array('mkb_id_1' => $mkbs, 'num_workers' => $num_workers);	
			}
			// Sort array - http://php.net/manual/en/function.array-multisort.php
			$mkb_id_1 = array();
			$num_workers = array();
			foreach ($rows as $key => $row) {
				$mkb_id_1[$key] = $row['mkb_id_1'];
				$num_workers[$key] = $row['num_workers'];			
			}
			array_multisort($num_workers, SORT_ASC, $mkb_id_1, SORT_ASC, $rows);
			
			ob_start();
			?>
			<table class=MsoTableGrid border=1 cellspacing=0 cellpadding=0 width="100%"
			 style='width:100.0%;border-collapse:collapse;border:none;mso-border-alt:solid windowtext .5pt;
			 mso-yfti-tbllook:480;mso-padding-alt:0cm 5.4pt 0cm 5.4pt;mso-border-insideh:
			 .5pt solid windowtext;mso-border-insidev:.5pt solid windowtext'>
			 <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes'>
			  <td width=91 style='width:68.4pt;border:solid windowtext 1.0pt;mso-border-alt:
			  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'>№ по ред</p>
			  </td>
			  <td width=168 style='width:126.0pt;border:solid windowtext 1.0pt;border-left:
			  none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
			  padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'>брой работещи</p>
			  </td>
			  <td width=360 style='width:270.0pt;border:solid windowtext 1.0pt;border-left:
			  none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
			  padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'>Диагнози (код по <span
			  class=SpellE>МКБ-</span>10)</p>
			  </td>
			 </tr>
			<?php
			$i = 1;
			foreach ($rows as $row) {
				?>
			 <tr style='mso-yfti-irow:<?=$i?>;mso-yfti-lastrow:yes'>
			  <td width=91 valign=top style='width:68.4pt;border:solid windowtext 1.0pt;
			  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
			  padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal><?=$i++?>.</p>
			  </td>
			  <td width=168 valign=top style='width:126.0pt;border-top:none;border-left:
			  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
			  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
			  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'><?=$row['num_workers']?></p>
			  </td>
			  <td width=360 valign=top style='width:270.0pt;border-top:none;border-left:
			  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
			  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
			  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'><?=$row['mkb_id_1']?></p>
			  </td>
			 </tr>
				<?php
			}
			?>
			</table>
			<?php
			return ob_get_clean();
		}
		return '';
	}

	// Описание на трудовите злополуки: брой и причини
	public function getWorkersLabourAccidentsTable() {
		$rows = $this->workers_labour_accidents;
		if(!empty($rows)) {
			ob_start();
			?>
			<table class=MsoTableGrid border=1 cellspacing=0 cellpadding=0 width="100%"
			 style='width:100.0%;border-collapse:collapse;border:none;mso-border-alt:solid windowtext .5pt;
			 mso-yfti-tbllook:480;mso-padding-alt:0cm 5.4pt 0cm 5.4pt;mso-border-insideh:
			 .5pt solid windowtext;mso-border-insidev:.5pt solid windowtext'>
			 <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes'>
			  <td width=91 style='width:68.4pt;border:solid windowtext 1.0pt;mso-border-alt:
			  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'>№ по ред</p>
			  </td>
			  <td width=180 style='width:135.0pt;border:solid windowtext 1.0pt;border-left:
			  none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
			  padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'>Брой трудови
			  злополуки</p>
			  </td>
			  <td width=348 style='width:261.0pt;border:solid windowtext 1.0pt;border-left:
			  none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
			  padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'>Причини</p>
			  </td>
			 </tr>
			<?php
			$i = 1;
			foreach ($rows as $reason_id => $cnt) {
				$accident = ('04' == $reason_id) ? '04 - Злополука - трудова по чл. 55 ал. 1 от КЗОО' : '05 - Злополука - трудова по чл. 55 ал. 2 от КЗООО';
				?>
			<tr style='mso-yfti-irow:<?=$i?>;mso-yfti-lastrow:yes'>
			  <td width=91 valign=top style='width:68.4pt;border:solid windowtext 1.0pt;
			  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
			  padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal><?=$i++?></p>
			  </td>
			  <td width=180 valign=top style='width:135.0pt;border-top:none;border-left:
			  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
			  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
			  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'><?=$cnt?></p>
			  </td>
			  <td width=348 valign=top style='width:261.0pt;border-top:none;border-left:
			  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
			  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
			  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'><?=$accident?></p>
			  </td>
			 </tr>
				<?php	
			}
			?>
			</table>
			<?php
			return ob_get_clean();
		}
		return '';
	}

	// Описание на регистрираните професионални болести – брой и диагнози.
	public function getWorkersProDiseasesTable() {
		if(!empty($this->pro_diseases)) {
			$rows = array();
			foreach ($this->pro_diseases as $key => $row) {
				$rows[$row['mkb_id']] = (isset($rows[$row['mkb_id']])) ? ++$rows[$row['mkb_id']] : 1;
			}
			ob_start();
			?>
			<table class=MsoTableGrid border=1 cellspacing=0 cellpadding=0 width="100%"
			 style='width:100.0%;border-collapse:collapse;border:none;mso-border-alt:solid windowtext .5pt;
			 mso-yfti-tbllook:480;mso-padding-alt:0cm 5.4pt 0cm 5.4pt;mso-border-insideh:
			 .5pt solid windowtext;mso-border-insidev:.5pt solid windowtext'>
			 <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes'>
			  <td width=91 style='width:68.4pt;border:solid windowtext 1.0pt;mso-border-alt:
			  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'>№ по ред</p>
			  </td>
			  <td width=180 style='width:135.0pt;border:solid windowtext 1.0pt;border-left:
			  none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
			  padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'>Брой регистрирани
			  професионални болести</p>
			  </td>
			  <td width=348 style='width:261.0pt;border:solid windowtext 1.0pt;border-left:
			  none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
			  padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'>Диагнози</p>
			  </td>
			 </tr>
			<?php
			$i = 1;
			foreach ($rows as $mkb_id => $cnt) {
				?>
			 <tr style='mso-yfti-irow:<?=$i?>;mso-yfti-lastrow:yes'>
			  <td width=91 valign=top style='width:68.4pt;border:solid windowtext 1.0pt;
			  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
			  padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal><?=$i++?>.</p>
			  </td>
			  <td width=180 valign=top style='width:135.0pt;border-top:none;border-left:
			  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
			  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
			  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'><?=$cnt?></p>
			  </td>
			  <td width=348 valign=top style='width:261.0pt;border-top:none;border-left:
			  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
			  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
			  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'><?=$mkb_id?></p>
			  </td>
			 </tr>
				<?php	
			}
			?>
			</table>
			<?php
			return ob_get_clean();
		}
		return '';
	}
	
	// Описание на резултатите от задължителни периодични медицински прегледи по нозологична структура, съгласно МКБ-10
	public function getPatientChartResultsTable() {
		$IDs = (empty($this->worker_ids)) ? '-1' : $this->worker_ids;
		// *** Patient charts
		$sql = "SELECT d.worker_id AS worker_id, cl.class_id AS class_id, cl.class_name AS class_name, 
				g.group_id AS group_id, g.group_name AS group_name, 
				d.mkb_id AS mkb_id, m.mkb_desc AS mkb_desc, COUNT(*) AS cnt
				FROM `family_diseases` d
				LEFT JOIN `mkb` m ON (m.mkb_id = d.mkb_id)
				LEFT JOIN `mkb_groups` g ON (g.group_id = m.group_id)
				LEFT JOIN `mkb_classes` cl ON (cl.class_id = g.class_id)
				LEFT JOIN `medical_checkups` c ON (c.checkup_id = d.checkup_id) 
				WHERE d.`worker_id` IN ($IDs)
				AND (
					(julianday(c.`checkup_date`) >= julianday('$this->date_from')) AND (julianday(c.`checkup_date`) <= julianday('$this->date_to'))
				)
				GROUP BY d.mkb_id
				ORDER BY cl.class_id, g.group_id, cnt DESC, m.mkb_id";
		$data = $this->_getNosologicTable($sql, 'Брой случаи');
		return $data['table'];
	}

	// Описание на резултатите от проведените периодични медицински прегледи
	public function getMedicalCheckupResultsTable() {
		if(!empty($this->medical_checkups)) {
			$chkIDs = array();
			$medical_checkups = $this->medical_checkups;
			foreach ($medical_checkups as $key => $fld) {
				$chkIDs[] = $fld['checkup_id'];	
			}
			// family_weights
			$sql = "SELECT `checkup_id`, `mkb_id` FROM `family_weights` WHERE `checkup_id` IN (".implode(',', $chkIDs).") ORDER BY `family_weight_id`";
			$rows = $this->query($sql);
			$family_weights = array();
			if(!empty($rows)) {
				foreach ($rows as $row) {
					$family_weights[$row['checkup_id']][] = $row;
				}
			}
			// anamnesis
			$sql = "SELECT `checkup_id`, `mkb_id` FROM `anamnesis` WHERE `checkup_id` IN (".implode(',', $chkIDs).") ORDER BY `anamnesis_id`";
			$rows = $this->query($sql);
			$anamnesis = array();
			if(!empty($rows)) {
				foreach ($rows as $row) {
					$anamnesis[$row['checkup_id']][] = $row;
				}
			}
			// lab_checkups
			$sql = "SELECT c.checkup_id, c.checkup_level, i.indicator_type, i.indicator_name
					FROM `lab_checkups` c 
					LEFT JOIN `lab_indicators` i ON (i.`indicator_id` = c.`indicator_id`)
					WHERE c.`checkup_id` IN (".implode(',', $chkIDs).") 
					ORDER BY c.`lab_checkup_id`";
			$rows = $this->query($sql);
			$lab_checkups = array();
			if(!empty($rows)) {
				foreach ($rows as $row) {
					$lab_checkups[$row['checkup_id']][] = $row;
				}
			}
			// family_diseases
			$family_diseases = $this->family_diseases;
			ob_start();
			?>
			<table class=MsoTableGrid border=1 cellspacing=0 cellpadding=0 width="100%"
			 style='width:100.0%;border-collapse:collapse;border:none'>
			 <tr>
			  <td width="11%" style='width:11.3%;border:solid windowtext 1.0pt;padding:
			  0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'>№ по ред</p>
			  </td>
			  <td width="11%" style='width:11.3%;border:solid windowtext 1.0pt;border-left:
			  none;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'>Пол</p>
			  </td>
			  <td width="11%" style='width:11.36%;border:solid windowtext 1.0pt;border-left:
			  none;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'>Възраст</p>
			  </td>
			  <td width="13%" style='width:13.46%;border:solid windowtext 1.0pt;border-left:
			  none;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'>Длъжност</p>
			  </td>
			  <td width="10%" style='width:10.3%;border:solid windowtext 1.0pt;border-left:
			  none;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'>Фам. заб.</p>
			  </td>
			  <td width="13%" style='width:13.06%;border:solid windowtext 1.0pt;border-left:
			  none;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'>Анамнеза</p>
			  </td>
			  <td width="16%" style='width:16.44%;border:solid windowtext 1.0pt;border-left:
			  none;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'>Лаб. изследвания</p>
			  </td>
			  <td width="12%" style='width:12.76%;border:solid windowtext 1.0pt;border-left:
			  none;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'>Заболявания (диагнози)</p>
			  </td>
			 </tr>
			<?php
			$i = 1;
			foreach ($medical_checkups as $row) {
				$checkup_id = $row['checkup_id'];
				$worker_id = $row['worker_id'];
				$sex = (isset($this->workers[$worker_id]['sex'])) ? $this->workers[$worker_id]['sex'] : '--';
				$age = (isset($this->workers[$worker_id]['age'])) ? $this->workers[$worker_id]['age'] : '--';
				$position_name = (isset($this->workers[$worker_id]['position_name'])) ? $this->workers[$worker_id]['position_name'] : '--';
				?>
			 <tr>
			  <td width="11%" valign=top style='width:11.3%;border:solid windowtext 1.0pt;
			  border-top:none;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'><?=$i++?>.</p>
			  </td>
			  <td width="11%" valign=top style='width:11.3%;border-top:none;border-left:
			  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
			  padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'><?=$sex?></p>
			  </td>
			  <td width="11%" valign=top style='width:11.36%;border-top:none;border-left:
			  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
			  padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'><?=$age?> г.</p>
			  </td>
			  <td width="13%" valign=top style='width:13.46%;border-top:none;border-left:
			  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
			  padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal><?=HTMLFormat($position_name)?></p>
			  </td>
			  <td width="10%" valign=top style='width:10.3%;border-top:none;border-left:
			  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
			  padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal><?php
			  if(isset($family_weights[$checkup_id])) {
			  	$ary = array();
			  	foreach ($family_weights[$checkup_id] as $fld) { $ary[] = $fld['mkb_id']; }
			  	echo implode('; ', $ary);
			  }
			  ?>&nbsp;</p>
			  </td>
			  <td width="13%" valign=top style='width:13.06%;border-top:none;border-left:
			  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
			  padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal><?php
			  if(isset($anamnesis[$checkup_id])) {
			  	$ary = array();
			  	foreach ($anamnesis[$checkup_id] as $fld) { $ary[] = $fld['mkb_id']; }
			  	echo implode('; ', $ary);
			  }
			  ?>&nbsp;</p>
			  </td>
			  <td width="16%" valign=top style='width:16.44%;border-top:none;border-left:
			  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
			  padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal><?php
			  if(isset($lab_checkups[$checkup_id])) {
			  	$ary = array();
			  	foreach ($lab_checkups[$checkup_id] as $fld) { $ary[] = $fld['indicator_type'].' ('.$fld['indicator_name'].') '.$fld['checkup_level']; }
			  	echo implode('; ', $ary);
			  }
			  ?>&nbsp;</p>
			  </td>
			  <td width="12%" valign=top style='width:12.76%;border-top:none;border-left:
			  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
			  padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal><?php
			  if(isset($family_diseases[$checkup_id])) {
			  	$ary = array();
			  	foreach ($family_diseases[$checkup_id] as $fld) { $ary[] = $fld['mkb_id']; }
			  	echo implode('; ', $ary);
			  }
			  ?>&nbsp;</p>
			  </td>
			 </tr>
				<?php
			}
			?>
			</table>
			<?php
			return ob_get_clean();
		}
		return '';
	}
	
	// Описание на боледувалите работещи по данни от болничните листове
	public function getWorkersByPatientChartTable() {
		if(!empty($this->patient_charts_by_worker)) {
			$rows = array();
			foreach ($this->patient_charts_by_worker as $worker_id => $flds) {
				$ary = array();
				foreach ($flds as $fld) { $ary[] = $fld['mkb_id']; }
				$rows[$worker_id]['mkbs'] = implode('; ', $ary);
				$rows[$worker_id]['sex'] = (isset($this->workers[$worker_id]['sex'])) ? $this->workers[$worker_id]['sex'] : 'М';
				$rows[$worker_id]['age'] = (isset($this->workers[$worker_id]['age'])) ? $this->workers[$worker_id]['age'] : 0;
				$rows[$worker_id]['position_name'] = (isset($this->workers[$worker_id]['position_name'])) ? $this->workers[$worker_id]['position_name'] : '--';
			}
			
			// Sort array - http://php.net/manual/en/function.array-multisort.php
			$sex = array();
			$age = array();
			$position_name = array();
			foreach ($rows as $key => $row) {
				if(empty($row['sex'])) $row['sex'] = 'М';
				if(empty($row['age'])) $row['age'] = 0;
				$sex[$key] = $row['sex'];
				$age[$key] = $row['age'];
				$position_name[$key] = $row['position_name'];							
			}
			array_multisort($sex, SORT_ASC, $age, SORT_ASC, $position_name, SORT_ASC, $rows);
			
			ob_start();
			?>
			<table class=MsoTableGrid border=1 cellspacing=0 cellpadding=0 width="100%"
			 style='width:100.0%;border-collapse:collapse;border:none;mso-border-alt:solid windowtext .5pt;
			 mso-yfti-tbllook:480;mso-padding-alt:0cm 5.4pt 0cm 5.4pt;mso-border-insideh:
			 .5pt solid windowtext;mso-border-insidev:.5pt solid windowtext'>
			 <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes'>
			  <td width=91 style='width:68.4pt;border:solid windowtext 1.0pt;mso-border-alt:
			  solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'>№ по ред</p>
			  </td>
			  <td width=84 style='width:63.0pt;border:solid windowtext 1.0pt;border-left:
			  none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
			  padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'>Пол</p>
			  </td>
			  <td width=84 style='width:63.0pt;border:solid windowtext 1.0pt;border-left:
			  none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
			  padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal>Възраст</p>
			  </td>
			  <td width=180 style='width:135.0pt;border:solid windowtext 1.0pt;border-left:
			  none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
			  padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'>Длъжност</p>
			  </td>
			  <td width=180 style='width:135.0pt;border:solid windowtext 1.0pt;border-left:
			  none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
			  padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'>Диагнози</p>
			  <p class=MsoNormal align=center style='text-align:center'>(код <span
			  class=SpellE>по МКБ-</span>10)</p>
			  </td>
			 </tr>
			<?php
			$i = 1;
			foreach ($rows as $worker_id => $flds) {
				?>
			 <tr style='mso-yfti-irow:<?=$i?>;mso-yfti-lastrow:yes'>
			  <td width=91 valign=top style='width:68.4pt;border:solid windowtext 1.0pt;
			  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
			  padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal><?=$i++?>.</p>
			  </td>
			  <td width=84 valign=top style='width:63.0pt;border-top:none;border-left:none;
			  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
			  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
			  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'><?=$flds['sex']?></p>
			  </td>
			  <td width=84 valign=top style='width:63.0pt;border-top:none;border-left:none;
			  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
			  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
			  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal align=center style='text-align:center'><?=$flds['age']?></p>
			  </td>
			  <td width=180 valign=top style='width:135.0pt;border-top:none;border-left:
			  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
			  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
			  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal><?=HTMLFormat($flds['position_name'])?></p>
			  </td>
			  <td width=180 valign=top style='width:135.0pt;border-top:none;border-left:
			  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
			  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
			  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
			  <p class=MsoNormal><?=$flds['mkbs']?></p>
			  </td>
			 </tr>	
				<?php
			}
			?>
			</table>
			<?php
			return ob_get_clean();
		}
		return '<p class=MsoNormal>Няма предоставени данни</p>';
	}
}















