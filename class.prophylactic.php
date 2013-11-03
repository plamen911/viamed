<?

class Prophylactic extends StmStats {
	public $aWorkersSubject = array();
	public $aWorkersPassed = array();
	public $aMenPassed = array();
	public $aWomenPassed = array();
	public $aDiseases = array();

	function __construct($firm_id = 0, $date_from = '2010-01-01 00:00:00', $date_to = '2011-12-31 23:59:59', $subdivision_id = 0, $wplace_id = 0) {
		parent::__construct($firm_id, $date_from, $date_to, $subdivision_id, $wplace_id);
		
		if(!empty($this->worker_ids)) {
			$sql = "SELECT `worker_id`, `sex`, `birth_date`, `date_curr_position_start` FROM `workers` WHERE `worker_id` IN (".$this->worker_ids.")";
			$rows = $this->query($sql);
			if(!empty($rows)) {
				foreach ($rows as $row) {
					foreach ($row as $key => $val) {
						if(is_numeric($key)) {
							unset($row[$key]);
						}
					}
					
					$row['date_from'] = $date_from;
					$row['date_to'] = $date_to;
					$row['date_to'] = $date_to;
					$row['age'] = self::calcYears($date_to, $row['birth_date']);
					$row['curr_position_length'] = self::calcYears($date_to, $row['date_curr_position_start']);
					$this->aWorkersSubject[$row['worker_id']] = $row;
				}
			}
			unset($rows);
			
			$sql = "SELECT w.`worker_id` AS `worker_id`, w.`sex` AS `sex`, w.`birth_date` AS `birth_date`, m.`mkb_id` AS `mkb_id`, m.`mkb_desc` AS `mkb_desc`,
					i.`position_name` AS `position_name`
					FROM `family_diseases` d
					LEFT JOIN `mkb` m ON (m.`mkb_id` = d.`mkb_id`)
					LEFT JOIN `workers` w ON ( w.`worker_id` = d.`worker_id` )
					LEFT JOIN `firm_struct_map` m ON ( m.`map_id` = w.`map_id` )
					LEFT JOIN `firm_positions` i ON ( i.`position_id` = m.`position_id` )
					WHERE d.`worker_id` IN ( ".$this->worker_ids." )
					ORDER BY m.`mkb_id`, m.`mkb_desc`";
			$rows = $this->query($sql);
			if(!empty($rows)) {
				foreach ($rows as $row) {
					$row['age'] = self::calcYears($date_to, $row['birth_date']);
					if(empty($row['position_name'])) { $row['position_name'] = '--'; }
					$this->aDiseases[] = array('worker_id' => $row['worker_id'], 'sex' => $row['sex'], 'age' => $row['age'], 'mkb_id' => $row['mkb_id'], 'mkb_desc' => $row['mkb_desc'], 'position_name' => $row['position_name']);
				}
			}
			unset($rows);
		}
		
		$rows = $this->medical_checkups;
		if(!empty($rows)) {
			foreach ($rows as $row) {
				$worker_id = $row['worker_id'];
				if(isset($this->aWorkersSubject[$worker_id])) {
					$this->aWorkersPassed[$worker_id] = $this->aWorkersSubject[$worker_id];
					if('М' == $this->aWorkersPassed[$worker_id]['sex']) {
						$this->aMenPassed[$worker_id] = $this->aWorkersPassed[$worker_id];
					} else {
						$this->aWomenPassed[$worker_id] = $this->aWorkersPassed[$worker_id];
					}
				}
			}
		}
		
		$this->avg_workers = sprintf("%.1f", $this->avg_workers);
	}

	public function getProphylacticCheckups() {
		$rows = $this->medical_checkups;
		if(!empty($rows)) {
			foreach ($rows as $i => $row) {
				foreach ($row as $key => $val) {
					if(is_numeric($key)) {
						unset($row[$key]);
					}
				}
				$rows[$i] = $row;
			}
		}
		return $rows;
	}
	
	public function getNumWorkersSubject() {
		return count($this->aWorkersSubject);
	}
	
	public function getNumWorkersPassed() {
		return count($this->aWorkersPassed);
	}
	
	public function getNumMenPassed() {
		return count($this->aMenPassed);
	}
	
	public function getNumWomenPassed() {
		return count($this->aWomenPassed);
	}
	
	public function getPercentWorkersPassed() {
		$cnt = ($this->getNumWorkersSubject()) ? $this->getNumWorkersPassed() * 100 / $this->getNumWorkersSubject() : 0;
		return sprintf("%.2f", $cnt);
	}
	
	public function getPercentMenPassed() {
		$cnt = ($this->getNumWorkersPassed()) ? $this->getNumMenPassed() * 100 / $this->getNumWorkersPassed() : 0;
		return sprintf("%.2f", $cnt);
	}
	
	public function getPercentWomenPassed() {
		$cnt = ($this->getNumWorkersPassed()) ? $this->getNumWomenPassed() * 100 / $this->getNumWorkersPassed() : 0;
		return sprintf("%.2f", $cnt);
	}
	
	public function getNumMenPassedByAge($age_from = 25, $age_to = 35) {
		$num = 0;
		if(!empty($this->aMenPassed)) {
			foreach ($this->aMenPassed as $row) {
				if($row['age'] >= $age_from && $row['age'] < $age_to) {
					$num++;
				}
			}
		}
		return $num;
	}
	
	public function getNumWomenPassedByAge($age_from = 25, $age_to = 35) {
		$num = 0;
		if(!empty($this->aWomenPassed)) {
			foreach ($this->aWomenPassed as $row) {
				if($row['age'] >= $age_from && $row['age'] < $age_to) {
					$num++;
				}
			}
		}
		return $num;
	}
	
	public function getPercentMenPassedByAge($age_from = 25, $age_to = 35) {
		$cnt = ($this->getNumWorkersPassed()) ? $this->getNumMenPassedByAge($age_from, $age_to) * 100 / $this->getNumWorkersPassed() : 0;
		return sprintf("%.2f", $cnt);
	}
	
	public function getPercentWomenPassedByAge($age_from = 25, $age_to = 35) {
		$cnt = ($this->getNumWorkersPassed()) ? $this->getNumWomenPassedByAge($age_from, $age_to) * 100 / $this->getNumWorkersPassed() : 0;
		return sprintf("%.2f", $cnt);
	}
	
	public function getPercentWorkersPassedByAge($age_from = 25, $age_to = 35) {
		return sprintf("%.2f", $this->getPercentMenPassedByAge($age_from, $age_to) + $this->getPercentWomenPassedByAge($age_from, $age_to));
	}
	
	public function getNumMenPassedByWPos($date_curr_position_from = 25, $date_curr_position_to = 35) {
		$num = 0;
		if(!empty($this->aMenPassed)) {
			foreach ($this->aMenPassed as $row) {
				if($row['curr_position_length'] >= $date_curr_position_from && $row['curr_position_length'] < $date_curr_position_to) {
					$num++;
				}
			}
		}
		return $num;
	}
	
	public function getNumWomenPassedByWPos($date_curr_position_from = 25, $date_curr_position_to = 35) {
		$num = 0;
		if(!empty($this->aWomenPassed)) {
			foreach ($this->aWomenPassed as $row) {
				if($row['curr_position_length'] >= $date_curr_position_from && $row['curr_position_length'] < $date_curr_position_to) {
					$num++;
				}
			}
		}
		return $num;
	}
	
	public function getPercentMenPassedByWPos($date_curr_position_from = 25, $date_curr_position_to = 35) {
		$cnt = ($this->getNumWorkersPassed()) ? $this->getNumMenPassedByWPos($date_curr_position_from, $date_curr_position_to) * 100 / $this->getNumWorkersPassed() : 0;
		return sprintf("%.2f", $cnt);
	}
	
	public function getPercentWomenPassedByWPos($date_curr_position_from = 25, $date_curr_position_to = 35) {
		$cnt = ($this->getNumWorkersPassed()) ? $this->getNumWomenPassedByWPos($date_curr_position_from, $date_curr_position_to) * 100 / $this->getNumWorkersPassed() : 0;
		return sprintf("%.2f", $cnt);
	}
	
	public function getPercentWorkersPassedByWPos($date_curr_position_from = 25, $date_curr_position_to = 35) {
		return sprintf("%.2f", $this->getPercentMenPassedByWPos($date_curr_position_from, $date_curr_position_to) + $this->getPercentWomenPassedByWPos($date_curr_position_from, $date_curr_position_to));
	}
	
	public function getDiseasesByMkb() {
		$rows = $this->aDiseases;
		if(!empty($rows)) {
			$ary = array();
			foreach ($rows as $row) {
				$mkb_id = $row['mkb_id'];
				$men = ('М' == $row['sex']) ? 1 : 0;
				$women = ('М' == $row['sex']) ? 0 : 1;
				
				if(isset($ary[$mkb_id])) {
					$men += $ary[$mkb_id]['men'];
					$women += $ary[$mkb_id]['women'];
				}
				
				$ary[$mkb_id] = array('mkb' => $row['mkb_id'].' '.$row['mkb_desc'], 'men' => $men, 'women' => $women);
			}
			$rows = array();
			foreach ($ary as $row) {
				$percent = ($this->getNumWorkersPassed()) ? ($row['men'] + $row['women']) * 100 / $this->getNumWorkersPassed() : 0;
				$row['percent'] = sprintf("%.2f", $percent);
				$rows[] = $row;	
			}
		}
		return $rows;
	}
	
	public function getDiseasesByAge() {
		$rows = $this->aDiseases;
		if(!empty($rows)) {
			$ary = array();
			foreach ($rows as $row) {
				$age_group = '';
				if(25 > $row['age']) { $age_group = 'До 25 г.'; }
				elseif(25 <= $row['age'] && 35 > $row['age']) { $age_group = '25 - 35 г.'; }
				elseif(35 <= $row['age'] && 45 > $row['age']) { $age_group = '35 - 45 г.'; }
				elseif(45 <= $row['age'] && 55 >= $row['age']) { $age_group = '45 - 55 г.'; }
				else { $age_group = 'Над 55 г.'; }
				
				$mkb_id = $row['mkb_id'];
				$men = ('М' == $row['sex']) ? 1 : 0;
				$women = ('М' == $row['sex']) ? 0 : 1;
				
				if(isset($ary[$age_group][$mkb_id])) {
					$men += $ary[$age_group][$mkb_id]['men'];
					$women += $ary[$age_group][$mkb_id]['women'];
				}
				
				$ary[$age_group][$mkb_id] = array('age_group' => $age_group, 'mkb' => $row['mkb_id'].' '.$row['mkb_desc'], 'men' => $men, 'women' => $women);
			}
			$rows = array();
			$rows['До 25 г.'] = array();
			$rows['25 - 35 г.'] = array();
			$rows['35 - 45 г.'] = array();
			$rows['45 - 55 г.'] = array();
			$rows['Над 55 г.'] = array();
			foreach ($ary as $age_group => $flds) {
				foreach ($flds as $mkb_id => $row) {
					$percent = ($this->getNumWorkersPassed()) ? ($row['men'] + $row['women']) * 100 / $this->getNumWorkersPassed() : 0;
					$row['percent'] = sprintf("%.2f", $percent);
					$rows[$age_group][] = $row;
				}
			}
			$ary = array();
			foreach ($rows as $age_group => $flds) {
				if(!empty($flds)) {
					foreach ($flds as $i => $fld) {
						if(0 < $i) { $fld['age_group'] = ''; }
						$ary[] = $fld;
					}
				}
			}
			$rows = $ary;
		}
		return $rows;
	}
	
	public function getDiseasesByWorkPosition() {
		$rows = $this->aDiseases;
		if(!empty($rows)) {
			$ary = array();
			foreach ($rows as $row) {
				$position_name = $row['position_name'];
				
				$mkb_id = $row['mkb_id'];
				$men = ('М' == $row['sex']) ? 1 : 0;
				$women = ('М' == $row['sex']) ? 0 : 1;
				
				if(isset($ary[$position_name][$mkb_id])) {
					$men += $ary[$position_name][$mkb_id]['men'];
					$women += $ary[$position_name][$mkb_id]['women'];
				}
				
				$ary[$position_name][$mkb_id] = array('position_name' => $position_name, 'mkb' => $row['mkb_id'].' '.$row['mkb_desc'], 'men' => $men, 'women' => $women);
			}
			$rows = array();
			foreach ($ary as $position_name => $flds) {
				foreach ($flds as $mkb_id => $row) {
					$percent = ($this->getNumWorkersPassed()) ? ($row['men'] + $row['women']) * 100 / $this->getNumWorkersPassed() : 0;
					$row['percent'] = sprintf("%.2f", $percent);
					$rows[$position_name][] = $row;
				}
			}
			ksort($rows);
			$ary = array();
			foreach ($rows as $position_name => $flds) {
				if(!empty($flds)) {
					foreach ($flds as $i => $fld) {
						if(0 < $i) { $fld['position_name'] = ''; }
						$ary[] = $fld;
					}
				}
			}
			$rows = $ary;
		}
		return $rows;
	}
	
	public static function calcYears($date_to = '2011-12-31 23:59:59', $date_from = '2010-01-01 00:00:00') {
		if(empty($date_to) || empty($date_from)) return 0;
		
		list($yy1, $mm1, $dd1) = split('-', substr($date_to, 0, 10));
		list($yy2, $mm2, $dd2) = split('-', substr($date_from, 0, 10));
		
		$mm1 = intval($mm1);
		$mm2 = intval($mm2);
		$dd1 = intval($dd1);
		$dd2 = intval($dd2);
		
		$yy = $yy1 - $yy2;
		if($mm1 < $mm2) {
			$yy--;
		} elseif($mm1 == $mm2 && $dd1 < $dd2) {
			$yy--;
		}
		
		return $yy;
	}

}















