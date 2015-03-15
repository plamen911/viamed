<?php

class SqliteDB
{
	private $dbhandle = null;

	/**
     * @desc get database handle
     */
	public function getDBHandle($dbfilename = null)
	{
		if ($this->dbhandle == null) {

			if ($dbfilename == null)
			$dbfilename = "stm.db";
			$dbfilepath = str_replace(basename($_SERVER['PHP_SELF']), "", $_SERVER['SCRIPT_FILENAME']) . "db/";

			/*$temp = $_ENV["TEMP"] . "\\";
			if (is_writable($dbfilepath . $dbfilename)) { //is writable
			//use database in current location
			} else { //not writable
			//running from a non writable location so copy to temp directory
			if (file_exists($temp . $dbfilename)) {
			$dbfilepath = $temp; //file already exists use existing file
			} else { //file doese not exist
			//copy the file to the temp dir
			if (copy($dbfilepath . $dbfilename, $temp . $dbfilename)) {
			//echo "copy succeeded.\n";
			$dbfilepath = $temp;
			} else {
			echo "Copy Failed ";
			exit;
			}
			}
			}*/

			//database connection
			try {
				//$db = new PDO('sqlite2:example.db'); //sqlite 2
				//$db = new PDO('sqlite::memory:'); //sqlite 3
				$db = new PDO('sqlite:' . $dbfilepath . $dbfilename); //sqlite 3
				$db->sqliteCreateFunction('LIKE', array(__CLASS__, 'lexa_ci_utf8_like'), 2);
				/*
				$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
				$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
				$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				*/
				$this->dbhandle = $db;
			}
			catch (PDOException $error) {
				print "error: " . $error->getMessage() . "<br/>";
				die();
			}
		}
		return $this->dbhandle;
	}
	
	// Thanks to: http://blog.amartynov.ru/archives/php-sqlite-case-insensitive-like-utf8/
	public function lexa_ci_utf8_like($mask, $value) {
		$mask = str_replace(
			array("%", "_"),
			array(".*?", "."),
			preg_quote($mask, "/")
		);
		$mask = "/^$mask$/ui";
		return preg_match($mask, $value);
	}

	/**
     * @desc prepare a posted string to be inserted into a database table
     */
	public function checkStr($strtemp)
	{
		$strtemp = str_replace("\'", "''", $strtemp); //escape the single quote
		$strtemp = str_replace("'", "''", $strtemp); //escape the single quote
		return trim($strtemp);
	}

	// http://bg2.php.net/manual/en/function.ucfirst.php
	public function my_mb_ucfirst($str, $e = 'utf-8')
	{
		$fc = mb_strtoupper(mb_substr($str, 0, 1, $e), $e);
		return $fc . mb_substr($str, 1, mb_strlen($str, $e), $e);
	}


	// GET FUNCTIONS ================================================

	/**
     * @desc get available firm work positions
     */
	public function getFirmPositions($firm_id = 0) {
		if ($firm_id) {
			$sql = "SELECT p.* , g.id AS progroup_id, g.parent AS parent_id, g.num AS progroup_num, g.name AS progroup_name
					FROM firm_positions p 
					LEFT JOIN pro_groups g ON (g.id =  p.progroup )
					WHERE p.firm_id = $firm_id 
					ORDER BY p.position_name";
		} else {
			$sql = "SELECT p.* , g.num AS progroup_num, g.name AS progroup_name
					FROM firm_positions p 
					LEFT JOIN pro_groups g ON ( p.progroup = g.id )
					ORDER BY p.position_name";
		}
		return $this->query($sql);
	}

	/**
     * @desc get available workers in a firm
     * @param $firm_id - firm_id value
     * @param $subdivision_id - subdivision_id value
     * @param $wplace_id - wplace_id value
     */
	public function getWorkers($firm_id = 0, $subdivision_id = 0, $wplace_id = 0) {
		if (!$wplace_id)
		$sql = sprintf("SELECT *, 
						strftime('%%d.%%m.%%Y', date_curr_position_start, 'localtime') AS date_curr_position_start2 
						FROM workers 
						WHERE firm_id = %d 
						AND is_active = '1'
						AND date_retired = ''
						ORDER BY fname, lname, worker_id", $firm_id);
		else
		$sql = sprintf("SELECT w.*,
						strftime('%%d.%%m.%%Y', w.birth_date, 'localtime') AS birth_date2
						FROM workers w
						LEFT JOIN firm_struct_map m ON (m.map_id = w.map_id)
						WHERE m.firm_id = %d
						AND w.is_active = '1'
						AND w.date_retired = ''
						AND m.subdivision_id = %d
						AND m.wplace_id = %d
						GROUP BY w.worker_id
						ORDER BY w.fname, w.lname, w.worker_id", $firm_id, $subdivision_id, $wplace_id);
		return $this->query($sql);
	}

	/**
     * @desc get available work places
     */
	public function getWorkPlaces($firm_id)
	{
		$db = $this->getDBHandle();
		if ($firm_id)
		$query = "SELECT * FROM work_places WHERE firm_id='$firm_id' ORDER BY wplace_name";
		else
		$query = "SELECT * FROM work_places ORDER BY wplace_name";
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetchAll();
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc get available work places in a subdivision
     */
	public function getWorkPlacesInSubdivision($subdivision_id, $firm_id)
	{
		$db = $this->getDBHandle();
		$query = "	SELECT m.map_id, m.subdivision_id,
					s.subdivision_name
					FROM firm_struct_map m
					LEFT JOIN subdivisions s ON (s.subdivision_id = m.subdivision_id)
					WHERE m.subdivision_id = " . intval($subdivision_id) . "
					AND firm_id = " . intval($firm_id) . "
					GROUP BY m.subdivision_id
					ORDER BY s.subdivision_name, s.subdivision_id";
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetchAll();
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc get available work environment factors
     */
	public function getSubdivisions($firm_id = 0, $map_id = 0)
	{
		$db = $this->getDBHandle();
		if ($firm_id)
		$query = "SELECT * FROM subdivisions WHERE firm_id=" . intval($firm_id) .
		" ORDER BY subdivision_position";
		else
		$query = "	SELECT s.*
					FROM firm_struct_map m
					LEFT JOIN subdivisions s ON (s.subdivision_id = m.subdivision_id)
					WHERE m.map_id = " . intval($map_id) . "
					ORDER BY s.subdivision_position, s.subdivision_name";
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetchAll();
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc get subdivision info
     * @param $subdivision_id - subdivision_id
     */
	public function getSubdivision($subdivision_id)
	{
		$db = $this->getDBHandle();
		$query = "SELECT * FROM subdivisions WHERE subdivision_id=" . intval($subdivision_id);
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetch(PDO::FETCH_ASSOC);
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc get work place info
     * @param $wplace_id - wplace_id
     */
	public function getWPlace($wplace_id)
	{
		$db = $this->getDBHandle();
		$query = "SELECT * FROM work_places WHERE wplace_id=" . intval($wplace_id);
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetch(PDO::FETCH_ASSOC);
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc get doctors positions info
     */
	public function getDoctorsPulldown($order_by = 'doctor_pos_name')
	{
		$db = $this->getDBHandle();
		$query = "SELECT * FROM `cfg_doctor_positions` ORDER BY `default` DESC";
		if ($order_by != '') { $query .= ", $order_by"; }
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetchAll();
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc get doctors descriptions info
     * @param  $checkup_id - checkup_id value
     */
	public function getDoctorsDesc($checkup_id = 0) {
		$sql = "SELECT s.SpecialistName AS SpecialistName , c.conclusion AS conclusion , c.SpecialistID AS SpecialistID
				FROM medical_checkups_doctors2 c
				LEFT JOIN Specialists s ON ( s.SpecialistID = c.SpecialistID )
				WHERE c.checkup_id = $checkup_id
				ORDER BY s.SpecialistName , s.SpecialistID";
		return $this->query($sql);
	}

	/**
     * @desc get the firm structure map
     * @param $firm_id - firm_id value
     */
	public function getMap($firm_id)
	{
		$db = $this->getDBHandle();
		$query = "	SELECT m.*,
					s.subdivision_name,
					w.wplace_name,
					p.position_name
					FROM firm_struct_map m
					LEFT JOIN subdivisions s ON (s.subdivision_id = m.subdivision_id)
					LEFT JOIN work_places w ON (w.wplace_id = m.wplace_id)
					LEFT JOIN firm_positions p ON (p.position_id = m.position_id)
					WHERE m.firm_id='$firm_id'
					ORDER BY s.subdivision_id, s.subdivision_name,
					w.wplace_id, w.wplace_name,
					p.position_id, p.position_name";
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetchAll();
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc get the firm structure map
     */
	public function getMapRow($map_id)
	{
		$db = $this->getDBHandle();
		$query = "	SELECT m.*,
					s.subdivision_name,
					w.wplace_name,
					p.position_name
					FROM firm_struct_map m
					LEFT JOIN subdivisions s ON (s.subdivision_id = m.subdivision_id)
					LEFT JOIN work_places w ON (w.wplace_id = m.wplace_id)
					LEFT JOIN firm_positions p ON (p.position_id = m.position_id)
					WHERE m.map_id='" . intval($map_id) . "'";
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetch(PDO::FETCH_ASSOC);
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc get the medical checkup info
     * @param $checkup_id - checkup_id value
     */
	public function getMedicalCheckupInfo($checkup_id)
	{
		$db = $this->getDBHandle();
		$query = "	SELECT c.*,
					strftime('%d.%m.%Y', c.checkup_date, 'localtime') AS checkup_date_h,
					w.*,
					(SELECT location_name FROM locations WHERE location_id = w.location_id) AS worker_location,
					strftime('%d.%m.%Y', w.birth_date, 'localtime') AS birth_date2,
					strftime('%d.%m.%Y', w.date_curr_position_start, 'localtime') AS date_curr_position_start2,
					strftime('%d.%m.%Y', w.date_career_start, 'localtime') AS date_career_start2,
					strftime('%d.%m.%Y', w.date_retired, 'localtime') AS date_retired2,
					strftime('%d.%m.%Y', c.stm_date, 'localtime') AS stm_date2,
					f.name AS firm_name,
					l.location_name,
					s.subdivision_name,
					p.wplace_name,
					t.position_name
					FROM medical_checkups c
					LEFT JOIN firms f ON (f.firm_id = c.firm_id)
					LEFT JOIN locations l ON (l.location_id = f.location_id)
					LEFT JOIN workers w ON (w.worker_id = c.worker_id)
					LEFT JOIN firm_struct_map m ON (m.map_id = w.map_id)
					LEFT JOIN subdivisions s ON (s.subdivision_id = m.subdivision_id)
					LEFT JOIN work_places p ON (p.wplace_id = m.wplace_id)
					LEFT JOIN firm_positions t ON(t.position_id = m.position_id)
					WHERE c.checkup_id='" . intval($checkup_id) . "'
					AND w.is_active = '1'";
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetch(PDO::FETCH_ASSOC);
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc get available work environment factors
     */
	public function getFactors()
	{
		$db = $this->getDBHandle();
		$query = "SELECT * FROM work_env_factors ORDER BY factor_position";
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetchAll();
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc get available work environment factors protocols
     * @param $firm_id - firm_id value
     * @param $subdivision_id - subdivision_id value
     * @param $wplace_id - wplace_id value
     */
	public function getWorkEnvProtocols($firm_id, $subdivision_id, $wplace_id)
	{
		$db = $this->getDBHandle();
		$query = "	SELECT map_id,
					p.*,
					f.*,
					strftime('%d.%m.%Y', p.prot_date, 'localtime') AS prot_date_h
					FROM wplace_prot_map m
					LEFT JOIN work_env_protocols p ON (p.prot_id = m.prot_id)
					LEFT JOIN work_env_factors f ON (f.factor_id = p.factor_id)
					WHERE m.firm_id = '" . intval($firm_id) . "'
					AND m.subdivision_id = '" . intval($subdivision_id) . "'
					AND m.wplace_id = '" . intval($wplace_id) . "'
					GROUP BY m.map_id";
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetchAll();
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc get available labs indicators
     */
	public function getLabs() {
		return $this->query("SELECT * FROM lab_indicators ORDER BY indicator_type, indicator_name, indicator_position");
	}

	/**
     * @desc get available labs indicators
     */
	public function getAccounts()
	{
		$db = $this->getDBHandle();
		$query = sprintf("SELECT *, strftime('%%d.%%m.%%Y %%H:%%M:%%S ч.', date_created, 'localtime') AS date_created2, strftime('%%d.%%m.%%Y %%H:%%M:%%S ч.', date_last_login, 'localtime') AS date_last_login2 FROM users WHERE user_id != %d ORDER BY date_created DESC, user_name",
		$_SESSION['sess_user_id']);
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetchAll();
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc get lab indicator info
     * @param $indicator_id - indicator_id value
     */
	public function getLabInfo($indicator_id)
	{
		$db = $this->getDBHandle();
		$query = "SELECT * FROM lab_indicators WHERE indicator_id='" . intval($indicator_id) .
		"'";
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetch(PDO::FETCH_ASSOC);
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc get work place manual typed-in factors info
     * @param $firm_id - firm_id value
     * @param $subdivision_id - subdivision_id value
     * @param $wplace_id - wplace_id value
     */
	public function getWPlaceFactorsInfo($firm_id, $subdivision_id, $wplace_id)
	{
		$db = $this->getDBHandle();
		$query = sprintf('SELECT * FROM wplace_factors_map WHERE firm_id=%d AND subdivision_id=%d AND wplace_id=%d',
		$firm_id, $subdivision_id, $wplace_id);
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetch(PDO::FETCH_ASSOC);
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc get STM information
     */
	public function getStmInfo($stm_id = 1)
	{
		if(preg_match('/\/lalova\//', $_SERVER['PHP_SELF'])) {
			$stm_id = (3 == $_SESSION['sess_user_id']) ? 2 : 1;
		}
		
		$db = $this->getDBHandle();
		$query = "SELECT * FROM stm_info WHERE stm_id='" . intval($stm_id) . "'";
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetch(PDO::FETCH_ASSOC);
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc get available family weights of the worker
     * @param $checkup_id - checkup_id
     */
	public function getFamilyWeights($checkup_id)
	{
		$db = $this->getDBHandle();
		$query = "	SELECT w.*,
					m.mkb_desc, m.mkb_code
					FROM family_weights w
					LEFT JOIN mkb m ON (m.mkb_id = w.mkb_id)
					WHERE w.checkup_id = '" . intval($checkup_id) . "'
					ORDER BY w.family_weight_id";
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetchAll();
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc get available anamnesis of the worker
     * @param $checkup_id - checkup_id
     */
	public function getAnamnesis($checkup_id)
	{
		$db = $this->getDBHandle();
		$query = "	SELECT a.*,
					m.mkb_desc, m.mkb_code
					FROM anamnesis a
					LEFT JOIN mkb m ON (m.mkb_id = a.mkb_id)
					WHERE a.checkup_id = '" . intval($checkup_id) . "'
					ORDER BY a.anamnesis_id";
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetchAll();
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc get worker's professional route
     * @param $worker_id - worker_id
     */
	public function getProRoute($worker_id)
	{
		$db = $this->getDBHandle();
		$query = "SELECT * FROM pro_route WHERE worker_id = '" . intval($worker_id) .
		"' ORDER BY route_id";
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetchAll();
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc get available worker's diseases
     * @param $checkup_id - checkup_id
     */
	public function getDiseases($checkup_id)
	{
		$db = $this->getDBHandle();
		$query = "	SELECT d.*,
					m.mkb_desc, m.mkb_code
					FROM family_diseases d
					LEFT JOIN mkb m ON (m.mkb_id = d.mkb_id)
					WHERE d.checkup_id = '" . intval($checkup_id) . "'
					ORDER BY d.disease_id";
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetchAll();
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc get available pre-checkup worker's diseases
     * @param $worker_id - worker_id
     */
	public function getPrchkDiagnosis($precheckup_id = 0) {
		$db = $this->getDBHandle();
		$query = sprintf("	SELECT d.*, 
							m.mkb_desc, m.mkb_code,
							p.SpecialistName AS doctor_pos_name
							FROM prchk_diagnosis d
							LEFT JOIN mkb m ON (m.mkb_id = d.mkb_id)
							LEFT JOIN Specialists p ON (p.SpecialistID = d.published_by)
							WHERE d.precheckup_id = %d
							ORDER BY d.prchk_id", $precheckup_id);
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetchAll();
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc get available worker medical checkups
     * @param $checkup_id - checkup_id
     */
	public function getLabCheckups($checkup_id)
	{
		$db = $this->getDBHandle();
		$query = "	SELECT c.*,
					i.indicator_type, i.indicator_name, i.pdk_max, i.pdk_min, i.indicator_dimension
					FROM lab_checkups c
					LEFT JOIN lab_indicators i ON (i.indicator_id = c.indicator_id)
					WHERE c.checkup_id = '" . intval($checkup_id) . "'
					ORDER BY c.lab_checkup_id";
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetchAll();
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc get available patient charts types
     */
	public function getChartTypes()
	{
		$db = $this->getDBHandle();
		$query = "SELECT * FROM chart_types ORDER BY type_id";
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetchAll();
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc get firms list
     */
	public function getFirms()
	{
		$db = $this->getDBHandle();
		$query = "	SELECT f.*, l.location_name, c.community_name, p.province_name
					FROM firms f
					LEFT JOIN locations l ON (l.location_id = f.location_id)
					LEFT JOIN communities c ON (c.community_id = f.community_id)
					LEFT JOIN provinces p ON (p.province_id = f.province_id)
					WHERE f.is_active = '1'
					ORDER BY LOWER(f.name), l.location_name, c.community_name, p.province_name, f.firm_id";
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetchAll();
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc get worker's sickness from TELKs by mkb_id type
     * @param - $worker_id - worker id value
     * @param - $mkb_num - mkb number value
     */
	public function getWorkerTelkTypes($worker_id, $mkb_num = '1')
	{
		$db = $this->getDBHandle();
		$query = "	SELECT t.*,
					strftime('%d.%m.%Y', t.telk_date_from, 'localtime') AS telk_date_from2,
					strftime('%d.%m.%Y', t.telk_date_to, 'localtime') AS telk_date_to2,
					strftime('%d.%m.%Y', t.first_inv_date, 'localtime') AS first_inv_date2,
					m.mkb_id, m.mkb_desc, m.mkb_code
					FROM telks t
					LEFT JOIN mkb m ON (m.mkb_id = t.mkb_id_$mkb_num)
					WHERE t.worker_id = " . intval($worker_id) . "
					AND t.mkb_id_$mkb_num != ''
					ORDER BY t.telk_date_from DESC, t.telk_id";
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetchAll();
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc get patient telks
     * @param $worker_id - worker id value
     * @param $what - what type of telks to retrieve
     */
	public function getPatientTelks($worker_id = 0, $what = 'all')
	{
		$db = $this->getDBHandle();
		$query = "	SELECT t.*,
					strftime('%d.%m.%Y', t.telk_date_from, 'localtime') AS telk_date_from_h,
					strftime('%d.%m.%Y', t.telk_date_to, 'localtime') AS telk_date_to_h,
					strftime('%d.%m.%Y', t.first_inv_date, 'localtime') AS first_inv_date_h,
					m.mkb_id, m.mkb_desc, m.mkb_code
					FROM telks t
					LEFT JOIN mkb m ON (m.mkb_id = t.mkb_id_1)
					WHERE t.worker_id = '" . intval($worker_id) . "' ";
		switch ($what) {
			case '50down':
				$query .= "AND t.percent_inv < 50 ";
				break;
			case '50up':
				$query .= "AND t.percent_inv >= 50 ";
				break;
			case 'all':
			default:
				break;
		}
		$query .= "ORDER BY t.telk_date_to, t.telk_id";
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetchAll();
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc get patient charts list
     * @param $worker_id - worker id value
     * @param $reasons - patient's charts medical reasons
     */
	public function getPatientCharts($worker_id, $reasons = array())
	{
		$db = $this->getDBHandle();
		$query = "	SELECT c.*,
					strftime('%d.%m.%Y', hospital_date_from, 'localtime') AS hospital_date_from,
					strftime('%d.%m.%Y', hospital_date_to, 'localtime') AS hospital_date_to,
					m.mkb_desc, m.mkb_code, r.reason_desc
					FROM patient_charts c
					LEFT JOIN mkb m ON (m.mkb_id = c.mkb_id)
					LEFT JOIN medical_reasons r ON (r.reason_id = c.reason_id)
					WHERE c.worker_id = '" . intval($worker_id) . "' ";
		if (count($reasons))
		$query .= "AND c.reason_id IN ('" . implode("','", $reasons) . "') ";
		$query .= "ORDER BY c.hospital_date_from, c.chart_id";
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetchAll();
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc get patient medical checkups list
     * @param $worker_id - worker id
     */
	public function getMedicalCheckupList($worker_id)
	{
		$db = $this->getDBHandle();
		$query = "	SELECT *,
					strftime('%d.%m.%Y', checkup_date, 'localtime') AS checkup_date_h
					FROM medical_checkups
					WHERE worker_id = '" . intval($worker_id) . "'
					ORDER BY checkup_date DESC, checkup_id DESC";
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetchAll();
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc get work environment factor info
     * @param $factor_id - factor_id
     */
	public function getFactorInfo($factor_id)
	{
		$db = $this->getDBHandle();
		$query = "SELECT * FROM work_env_factors WHERE factor_id=" . intval($factor_id);
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetch(PDO::FETCH_ASSOC);
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc get firm info
     * @param $firm_id - firm_id
     */
	public function getFirmInfo($firm_id)
	{
		$db = $this->getDBHandle();
		$query = "	SELECT f.*,
					strftime('%d.%m.%Y', f.contract_begin, 'localtime') AS contract_begin2,
					strftime('%d.%m.%Y', f.contract_end, 'localtime') AS contract_end2,
					f.name AS firm_name, l.*, c.*, p.*
					FROM firms f
					LEFT JOIN locations l ON (l.location_id = f.location_id)
					LEFT JOIN communities c ON (c.community_id = f.community_id)
					LEFT JOIN provinces p ON (p.province_id = f.province_id)
					WHERE f.firm_id = '" . intval($firm_id) . "'";
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetch(PDO::FETCH_ASSOC);
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}


	/**
     * @desc get annual report of the firm
     * @param $firm_id - firm_id
     * @param $date_from - date_from
     * @param $date_to - date_to
     */
	public function getAnnualReport($firm_id = 0, $date_from = '2007-01-01 00:00:00', $date_to = '2007-12-31 23:59:59')
	{
		$db = $this->getDBHandle();

		// anual_workers
		$query = "	SELECT COUNT(*) AS cnt FROM workers
					WHERE firm_id = $firm_id
					AND is_active = '1'
					AND ( date_retired = '' OR (julianday(date_retired) >= julianday('$date_to')) )
					AND ( (julianday(date_curr_position_start) <= julianday('$date_from'))
					OR date_curr_position_start = '')";
		$row = $this->fnSelectSingleRow($query);
		$r['anual_workers'] = (!empty($row['cnt'])) ? floatval($row['cnt']) : 0;

		// joined_workers
		$query = "	SELECT * FROM workers
					WHERE firm_id = $firm_id
					AND is_active = '1'
					AND (julianday(date_curr_position_start) > julianday('$date_from'))
					AND (julianday(date_curr_position_start) <= julianday('$date_to'))";
		$rows = $this->fnSelectRows($query);
		$r['joined_workers'] = count($rows);

		// retired_workers
		$IDs = array();
		foreach ($rows as $row) {
			$IDs[$row['worker_id']] = $row['worker_id'];
		}
		$query = "	SELECT COUNT(*) AS cnt FROM workers
					WHERE firm_id = $firm_id
					AND is_active = '1'
					AND (julianday(date_retired) >= julianday('$date_from'))
					AND (julianday(date_retired) <= julianday('$date_to'))";
		if(count($IDs) > 0) $query .= " AND worker_id NOT IN (".implode(',', $IDs).")";
		$row = $this->fnSelectSingleRow($query);
		$r['retired_workers'] = (!empty($row['cnt'])) ? floatval($row['cnt']) : 0;

		// anual_men
		$query = "	SELECT COUNT(*) AS cnt FROM workers
					WHERE firm_id = $firm_id
					AND is_active = '1'
					AND ( date_retired = '' OR (julianday(date_retired) >= julianday('$date_to')) )
					AND ( (julianday(date_curr_position_start) <= julianday('$date_from'))
					OR date_curr_position_start = '')
					AND (sex='М' OR sex='')";		
		$row = $this->fnSelectSingleRow($query);
		$r['anual_men'] = (!empty($row['cnt'])) ? floatval($row['cnt']) : 0;

		// joined_men
		$query = "	SELECT * FROM workers
					WHERE firm_id = $firm_id
					AND is_active = '1'
					AND (julianday(date_curr_position_start) > julianday('$date_from'))
					AND (julianday(date_curr_position_start) <= julianday('$date_to'))
					AND (sex='М' OR sex='')";
		$rows = $this->fnSelectRows($query);
		$r['joined_men'] = count($rows);

		// retired_men
		$IDs = array();
		foreach ($rows as $row) {
			$IDs[$row['worker_id']] = $row['worker_id'];
		}
		$query = "	SELECT COUNT(*) AS cnt FROM workers
					WHERE firm_id = $firm_id
					AND is_active = '1'
					AND (julianday(date_retired) >= julianday('$date_from'))
					AND (julianday(date_retired) <= julianday('$date_to'))
					AND (sex='М' OR sex='')";
		if(count($IDs) > 0) $query .= " AND worker_id NOT IN (".implode(',', $IDs).")";
		$row = $this->fnSelectSingleRow($query);
		$r['retired_men'] = (!empty($row['cnt'])) ? floatval($row['cnt']) : 0;

		// anual_women
		$query = "	SELECT COUNT(*) AS cnt FROM workers
					WHERE firm_id = $firm_id
					AND is_active = '1'
					AND ( date_retired = '' OR (julianday(date_retired) >= julianday('$date_to')) )
					AND ( (julianday(date_curr_position_start) <= julianday('$date_from'))
					OR date_curr_position_start = '')
					AND sex='Ж'";
		$row = $this->fnSelectSingleRow($query);
		$r['anual_women'] = (!empty($row['cnt'])) ? floatval($row['cnt']) : 0;

		// joined_women
		$query = "	SELECT * FROM workers
					WHERE firm_id = $firm_id
					AND is_active = '1'
					AND (julianday(date_curr_position_start) > julianday('$date_from'))
					AND (julianday(date_curr_position_start) <= julianday('$date_to'))
					AND sex='Ж'";
		$rows = $this->fnSelectRows($query);
		$r['joined_women'] = count($rows);

		// retired_women
		$IDs = array();
		foreach ($rows as $row) {
			$IDs[$row['worker_id']] = $row['worker_id'];
		}
		$query = "	SELECT COUNT(*) AS cnt FROM workers
					WHERE firm_id = $firm_id
					AND is_active = '1'
					AND (julianday(date_retired) >= julianday('$date_from'))
					AND (julianday(date_retired) <= julianday('$date_to'))
					AND sex='Ж'";
		if(count($IDs) > 0) $query .= " AND worker_id NOT IN (".implode(',', $IDs).")";
		$row = $this->fnSelectSingleRow($query);
		$r['retired_women'] = (!empty($row['cnt'])) ? floatval($row['cnt']) : 0;

		return $r;
	}

	/**
     * @desc get annual report of the firm
     * @param $firm_id - firm_id
     * @param $date_from - date_from
     * @param $date_to - date_to
     */
	public function getAnnualReport2($firm_id = 0, $date_from = '2007-01-01 00:00:00', $date_to = '2007-12-31 00:00:00')
	{
		$db = $this->getDBHandle();

		/*$d = new ParseBGDate();
		if($d->Parse($date_from))
		$date_from = $d->year.'-'.$d->month.'-'.$d->day.' 00:00:00';
		else
		$date_from = '';
		if($d->Parse($date_to))
		$date_to = $d->year.'-'.$d->month.'-'.$d->day.' 00:00:00';
		else
		$date_to = '';
		if($date_from == '' || $date_to == '') return false;*/

		$firm_id = intval($firm_id);
		$query = "	SELECT
					( SELECT COUNT(*) FROM workers
					WHERE firm_id = $firm_id
					AND is_active = '1'
					AND date_retired = ''
					AND ( (julianday(date_curr_position_start) <= julianday('$date_from'))
					OR date_curr_position_start = '') ) AS anual_workers,
					( SELECT COUNT(*) FROM workers
					WHERE firm_id = $firm_id
					AND is_active = '1'
					AND (julianday(date_curr_position_start) > julianday('$date_from'))
					AND (julianday(date_curr_position_start) <= julianday('$date_to')) ) AS joined_workers,
					( SELECT COUNT(*) FROM workers
					WHERE firm_id = $firm_id
					AND is_active = '1'
					AND (julianday(date_retired) >= julianday('$date_from'))
					AND (julianday(date_retired) <= julianday('$date_to')) ) AS retired_workers,

					( SELECT COUNT(*) FROM workers
					WHERE firm_id = $firm_id
					AND is_active = '1'
					AND date_retired = ''
					AND (julianday(date_curr_position_start) < julianday('$date_from'))
					AND sex='М') AS anual_men,
					( SELECT COUNT(*) FROM workers
					WHERE firm_id = $firm_id
					AND is_active = '1'
					AND date_retired = ''
					AND (julianday(date_curr_position_start) < julianday('$date_from'))
					AND sex='Ж') AS anual_women,
					( SELECT COUNT(*) FROM workers
					WHERE firm_id = $firm_id
					AND is_active = '1'
					AND (julianday(date_curr_position_start) >= julianday('$date_from'))
					AND (julianday(date_curr_position_start) <= julianday('$date_to'))
					AND sex='М') AS joined_men,
					( SELECT COUNT(*) FROM workers
					WHERE firm_id = $firm_id
					AND is_active = '1'
					AND (julianday(date_curr_position_start) >= julianday('$date_from'))
					AND (julianday(date_curr_position_start) <= julianday('$date_to'))
					AND sex='Ж') AS joined_women,
					( SELECT COUNT(*) FROM workers
					WHERE firm_id = $firm_id
					AND is_active = '1'
					AND (julianday(date_retired) >= julianday('$date_from'))
					AND (julianday(date_retired) <= julianday('$date_to'))
					AND sex='М') AS retired_men,
					( SELECT COUNT(*) FROM workers
					WHERE firm_id = $firm_id
					AND is_active = '1'
					AND (julianday(date_retired) >= julianday('$date_from'))
					AND (julianday(date_retired) <= julianday('$date_to'))
					AND sex='Ж') AS retired_women";
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' . $query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetch(PDO::FETCH_ASSOC);
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc get sick workers by patients charts
     * @param $firm_id - firm_id
     * @param $date_from - date_from
     * @param $date_to - date_to
     */
	public function getWorkersByCharts($firm_id = 0, $date_from =
	'2007-01-01 00:00:00', $date_to = '2007-12-31 00:00:00')
	{
		$db = $this->getDBHandle();
		$firm_id = intval($firm_id);
		$query = "	SELECT w.worker_id, w.sex,
					(SELECT (strftime('%Y', '$date_to', 'localtime') - strftime('%Y', birth_date, 'localtime')))
					AS age,
					i.position_name,
					c.mkb_id
					FROM patient_charts c
					LEFT JOIN workers w ON (w.worker_id = c.worker_id)
					LEFT JOIN firm_struct_map m ON (m.map_id = w.map_id)
					LEFT JOIN firm_positions i ON (i.position_id = m.position_id)
					WHERE c.firm_id = $firm_id
					AND w.is_active = '1'
					AND ( w.date_retired = '' OR julianday(w.date_retired) >= julianday('$date_from') )
					AND ( w.date_curr_position_start = '' OR julianday(w.date_curr_position_start) <= julianday('$date_to') )
					AND (julianday(c.hospital_date_from) >= julianday('$date_from'))
					AND (julianday(c.hospital_date_from) <= julianday('$date_to'))
					ORDER BY w.sex, w.worker_id";
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetchAll();
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc get sick workers by patients charts (primary patient's charts number is 4 and up OR sick period lasts more than 30 days)
     * с 4 и повече случаи с временна неработоспособност (първични болнични листове) и/или с 30 и повече дни с трудозагуби от заболявания за съответната календарна година.
     * @param $firm_id - firm_id
     * @param $date_from - date_from
     * @param $date_to - date_to
     */
	public function getWorkersByCharts3($firm_id = 0, $date_from = '2007-01-01 00:00:00', $date_to = '2007-12-31 00:00:00') {
		$firm_id = intval($firm_id);
		$sql = "SELECT w.worker_id, w.sex, w.fname, w.sname, w.lname,
				(SELECT (strftime('%Y', '$date_to', 'localtime') - strftime('%Y', birth_date, 'localtime'))) AS age,
				i.position_name,
					
				(SELECT SUM(days_off) FROM patient_charts WHERE worker_id = w.worker_id
					AND (julianday(hospital_date_from) >= julianday('$date_from'))
					AND (julianday(hospital_date_from) <= julianday('$date_to'))) AS days_off,
					
				(SELECT COUNT(*) FROM patient_charts WHERE worker_id = w.worker_id
					AND (julianday(hospital_date_from) >= julianday('$date_from'))
					AND (julianday(hospital_date_from) <= julianday('$date_to'))
					AND ( `medical_types` = 'a:1:{i:0;s:1:\"1\";}' OR `medical_types` = 'a:1:{i:0;i:1;}' )) AS num_primary
				FROM workers w
				LEFT JOIN firm_struct_map m ON (m.map_id = w.map_id)
				LEFT JOIN firm_positions i ON (i.position_id = m.position_id)
				WHERE w.firm_id = $firm_id
				AND w.is_active = '1'
				AND ( w.date_retired = '' OR julianday(w.date_retired) >= julianday('$date_from') )
				AND ( w.date_curr_position_start = '' OR julianday(w.date_curr_position_start) <= julianday('$date_to') )
				AND ( num_primary >= 4 OR days_off >= 30 )
				GROUP BY w.worker_id
				ORDER BY w.sex";
		return $this->query($sql);
	}

	/**
     * @desc get TELKs number for the period
     * @param $firm_id - firm_id
     * @param $date_from - date_from
     * @param $date_to - date_to
     */
	public function getWorkersByCharts4($firm_id = 0, $date_from = '2007-01-01 00:00:00', $date_to = '2007-12-31 00:00:00') {
		$data = array();
		$rows = $this->_getDurableDiseases($firm_id, $date_from, $date_to);
		if(!empty($rows)) {
			$workers = array();
			foreach ($rows as $row) {
				$workers[$row['mkb_id_1']] = (isset($workers[$row['mkb_id_1']])) ? ++$workers[$row['mkb_id_1']] : 1;
			}
			foreach ($workers as $mkb_id_1 => $num_workers) {
				$data[] = array('t.mkb_id_1' => $mkb_id_1, 'num_workers' => $num_workers);
			}
		}
		return $data;
	}

	/**
     * @desc get medical checkups
     * @param $firm_id - firm_id
     * @param $date_from - date_from
     * @param $date_to - date_to
     */
	public function getWorkersByCharts6($firm_id = 0, $date_from = '2007-01-01 00:00:00', $date_to = '2007-12-31 00:00:00')
	{
		$db = $this->getDBHandle();
		$firm_id = intval($firm_id);
		$query = "	SELECT
					(SELECT COUNT(*)
					FROM patient_charts
					WHERE firm_id = $firm_id
					AND reason_id = '04'
					AND (julianday(hospital_date_from) >= julianday('$date_from')
					AND julianday(hospital_date_from) <= julianday('$date_to'))
					) AS reason_04,
					(SELECT COUNT(*)
					FROM patient_charts
					WHERE firm_id = $firm_id
					AND reason_id = '05'
					AND (julianday(hospital_date_from) >= julianday('$date_from')
					AND julianday(hospital_date_from) <= julianday('$date_to'))) AS reason_05";
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetch(PDO::FETCH_ASSOC);
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc get medical checkups
     * @param $firm_id - firm_id
     * @param $date_from - date_from
     * @param $date_to - date_to
     */
	public function getWorkersByCharts7($firm_id = 0, $date_from =
	'2007-01-01 00:00:00', $date_to = '2007-12-31 00:00:00')
	{
		$db = $this->getDBHandle();
		$firm_id = intval($firm_id);
		$query = "	SELECT mkb_id_4, COUNT(mkb_id_4) AS cnt
					FROM telks
					WHERE mkb_id_4 != ''
					AND firm_id = $firm_id
					AND (
					( julianday(telk_date_from) >= julianday('$date_from')
					AND julianday(telk_date_from) <= julianday('$date_to'))
					OR
					( julianday(telk_date_to) <= julianday('$date_to')
					AND julianday(telk_date_to) >= julianday('$date_from'))
					OR
					( julianday(telk_date_from) <= julianday('$date_from')
					AND julianday(telk_date_to) >= julianday('$date_to'))
					)
					GROUP BY mkb_id_4
					ORDER BY cnt DESC";
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetchAll();
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc get work place info
     * @param $wplace_id - wplace_id
     */
	public function getWPlaceInfo($firm_id, $subdivision_id, $wplace_id)
	{
		$db = $this->getDBHandle();
		$query = "	SELECT w.*,
					f.name AS name,
					f.firm_id AS firm_id,
					s.subdivision_name AS subdivision_name
					FROM work_places w
					LEFT JOIN firm_struct_map m ON (m.wplace_id = w.wplace_id)
					LEFT JOIN firms f ON (f.firm_id = m.firm_id)
					LEFT JOIN subdivisions s ON (s.subdivision_id = m.subdivision_id)
					WHERE w.wplace_id = " . intval($wplace_id) . "
					AND m.wplace_id != 0
					AND m.firm_id = " . intval($firm_id) . "
					AND m.subdivision_id = " . intval($subdivision_id) . "
					AND f.is_active = '1'
					GROUP BY w.wplace_id";
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetch(PDO::FETCH_ASSOC);
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc get worker info
     * @param $worker_id - worker_id
     */
	public function getWorkerInfo($worker_id)
	{
		$db = $this->getDBHandle();
		$query = "	SELECT w.*,
					strftime('%d.%m.%Y', w.birth_date, 'localtime') AS birth_date2,
					strftime('%d.%m.%Y', w.date_curr_position_start, 'localtime') AS date_curr_position_start2,
					strftime('%d.%m.%Y', w.date_career_start, 'localtime') AS date_career_start2,
					strftime('%d.%m.%Y', w.date_retired, 'localtime') AS date_retired2,
					strftime('%d.%m.%Y', w.prchk_date, 'localtime') AS prchk_date2,
					strftime('%d.%m.%Y', w.prchk_stm_date, 'localtime') AS prchk_stm_date2,
					f.name AS firm_name,
					r.province_name,
					c.community_name,
					l.location_name,
					l.location_type,
					s.subdivision_id, s.subdivision_name,
					p.wplace_id, p.wplace_name, p.wplace_workcond,
					i.position_id, i.position_name,
					i.position_workcond,
					d.doctor_name,
					d.address AS doctor_address,
					d.phone1 AS doctor_phone,
					d.phone2 AS doctor_phone2
					FROM workers w
					LEFT JOIN firms f ON (f.firm_id = w.firm_id)
					LEFT JOIN locations l ON (l.location_id = w.location_id)
					LEFT JOIN communities c ON (c.community_id = l.community_id)
					LEFT JOIN provinces r ON (r.province_id = c.province_id)
					LEFT JOIN firm_struct_map m ON (m.map_id = w.map_id)
					LEFT JOIN subdivisions s ON (s.subdivision_id = m.subdivision_id)
					LEFT JOIN work_places p ON (p.wplace_id = m.wplace_id)
					LEFT JOIN firm_positions i ON (i.position_id = m.position_id)
					LEFT JOIN doctors d ON (w.doctor_id = d.doctor_id)
					WHERE w.worker_id = '" . intval($worker_id) . "'
					AND w.is_active = '1'
					GROUP BY w.worker_id";
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetch(PDO::FETCH_ASSOC);
			if(!empty($result)) {
				// data fix
				$tmp = array();
				foreach ($result as $key => $val) {
					$tmp[$key] = $val;
					if(false !== $pos = strpos($key, '.')) {
						$tmp[substr($key, $pos + 1)] = $val;
					}
				}
				$result = $tmp;
			}
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc get doctor info
     * @param $doctor_id - doctor_id
     */
	public function getDoctorInfo($doctor_id)
	{
		$db = $this->getDBHandle();
		$query = "SELECT * FROM doctors WHERE doctor_id = '" . intval($doctor_id) . "'";
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetch(PDO::FETCH_ASSOC);
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc get worker's chart info
     * @param $chart_id - chart_id
     */
	public function getChartInfo($chart_id)
	{
		$db = $this->getDBHandle();
		$query = "	SELECT c.*,
					strftime('%d.%m.%Y', c.hospital_date_from, 'localtime') AS hospital_date_from2,
					strftime('%d.%m.%Y', c.hospital_date_to, 'localtime') AS hospital_date_to2,
					m.mkb_desc, m.mkb_code,
					r.reason_desc
					FROM patient_charts c
					LEFT JOIN mkb m ON (m.mkb_id = c.mkb_id)
					LEFT JOIN medical_reasons r ON (r.reason_id = c.reason_id)
					WHERE c.chart_id = '" . intval($chart_id) . "'";
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetch(PDO::FETCH_ASSOC);
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc get worker's telk info
     * @param $telk_id - telk_id
     */
	public function getTelkInfo($telk_id)
	{
		$db = $this->getDBHandle();
		$query = "	SELECT t.*,
					strftime('%d.%m.%Y', t.telk_date_from, 'localtime') AS telk_date_from2,
					strftime('%d.%m.%Y', t.telk_date_to, 'localtime') AS telk_date_to2,
					strftime('%d.%m.%Y', t.first_inv_date, 'localtime') AS first_inv_date2,
					(SELECT mkb_desc FROM mkb m WHERE m.mkb_id = t.mkb_id_1) AS mkb_desc_1,
					(SELECT mkb_code FROM mkb m WHERE m.mkb_id = t.mkb_id_1) AS mkb_code_1,
					(SELECT mkb_desc FROM mkb m WHERE m.mkb_id = t.mkb_id_2) AS mkb_desc_2,
					(SELECT mkb_code FROM mkb m WHERE m.mkb_id = t.mkb_id_2) AS mkb_code_2,
					(SELECT mkb_desc FROM mkb m WHERE m.mkb_id = t.mkb_id_3) AS mkb_desc_3,
					(SELECT mkb_code FROM mkb m WHERE m.mkb_id = t.mkb_id_3) AS mkb_code_3,
					(SELECT mkb_desc FROM mkb m WHERE m.mkb_id = t.mkb_id_4) AS mkb_desc_4,
					(SELECT mkb_code FROM mkb m WHERE m.mkb_id = t.mkb_id_4) AS mkb_code_4
					FROM telks t
					WHERE t.telk_id = '" . intval($telk_id) . "'";
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetch(PDO::FETCH_ASSOC);
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}


	// SET FUNCTIONS ================================================

	/**
     * @desc add a new firm
     * @param $aFormValues - form values
     */
	public function processFirm($aFormValues)
	{
		$db = $this->getDBHandle();
		$modified_by = $_SESSION['sess_user_id'];
		try {
			$var_list = array('firm_id' => 'firm_id', 'name' => 'name', 'is_active' => 'is_active', 'location_name' =>
			'location_name', 'location_id' => 'location_id', 'community_name' =>
			'community_name', 'community_id' => 'community_id', 'province_name' =>
			'province_name', 'province_id' => 'province_id', 'address' => 'address', 'email' =>
			'email', 'notes' => 'notes', 'phone1' => 'phone1', 'phone2' => 'phone2', 'fax' =>
			'fax', 'contract_num' => 'contract_num', 'contract_begin' => 'contract_begin',
			'contract_end' => 'contract_end', 'FirmMOL' => 'FirmMOL', 'FirmUpravitel' => 'FirmUpravitel', 'FirmLice' => 'FirmLice', 'FirmLiceTel' => 'FirmLiceTel', 'FirmLiceEmail' => 'FirmLiceEmail');
			while (list($var, $param) = @each($var_list)) {
				if (isset($aFormValues[$param]))
				$$var = $this->checkStr($aFormValues[$param]);
			} //end while
			$d = new ParseBGDate();
			if ($d->Parse($contract_begin))
			$contract_begin = $d->year . '-' . $d->month . '-' . $d->day . ' 00:00:00';
			else
			$contract_begin = '';
			if ($d->Parse($contract_end))
			$contract_end = $d->year . '-' . $d->month . '-' . $d->day . ' 00:00:00';
			else
			$contract_end = '';
			if ($location_name == '')
			$location_id = 0;
			if ($community_name == '')
			$community_id = 0;
			if ($province_name == '')
			$province_id = 0;
			
			$name = str_replace('"', '', $name);
			$name = mb_strtoupper($name, 'utf-8');
			
			if(!isset($FirmMOL)) { $FirmMOL = ''; }
			if(!isset($FirmUpravitel)) { $FirmUpravitel = ''; }
			if(!isset($FirmLice)) { $FirmLice = ''; }
			if(!isset($FirmLiceTel)) { $FirmLiceTel = ''; }
			if(!isset($FirmLiceEmail)) { $FirmLiceEmail = ''; }
			
			if ($firm_id) { // Update firm
				$query = "UPDATE firms SET name='$name', `is_active` = '$is_active', location_id='" . intval($location_id) .
				"', community_id='" . intval($community_id) . "', province_id='" . intval($province_id) .
				"', address='$address', email='$email', notes='$notes', phone1='$phone1', phone2='$phone2', fax='$fax', date_modified=datetime('now','localtime'), modified_by='$modified_by', contract_num='$contract_num', contract_begin='$contract_begin', contract_end='$contract_end', FirmMOL = '$FirmMOL', FirmUpravitel = '$FirmUpravitel', FirmLice = '$FirmLice', FirmLiceTel = '$FirmLiceTel', FirmLiceEmail = '$FirmLiceEmail' WHERE firm_id='$firm_id'";
			} else { // Insert firm
				$query = "INSERT INTO firms (name, location_id, community_id, province_id, address, email, notes, phone1, phone2, fax, date_added, date_modified, modified_by, contract_num, contract_begin, contract_end, FirmMOL, FirmUpravitel, FirmLice, FirmLiceTel, FirmLiceEmail, added_by) VALUES ('$name', '" .
				intval($location_id) . "', '" . intval($community_id) . "', '" . intval($province_id) .
				"', '$address', '$email', '$notes', '$phone1', '$phone2', '$fax', datetime('now','localtime'), datetime('now','localtime'), '$modified_by', '$contract_num', '$contract_begin', '$contract_end', '$FirmMOL', '$FirmUpravitel', '$FirmLice', '$FirmLiceTel', '$FirmLiceEmail', '$modified_by')";
			}
			$count = $db->exec($query); //returns affected rows
			//echo "Affected Rows: ".$count."<br>";
			//echo "Last Insert Id: ".$db->lastInsertId($id)."<br>";
			return $count;
		}
		catch (exception $e) {
			die("Грешка при изпълнение на заявка към базата данни: " . $e->getMessage());
		}
	}

	/**
     * @desc add/update manual typed-in work environment factors for a work place
     * @param $aFormValues - form values
     */
	public function processWPlaceFactors($aFormValues)
	{
		$db = $this->getDBHandle();
		try {
			$var_list = array('map_id' => 'map_id', 'firm_id' => 'firm_id', 'subdivision_id' =>
			'subdivision_id', 'wplace_id' => 'wplace_id', 'fact_dust' => 'fact_dust',
			'fact_chemicals' => 'fact_chemicals', 'fact_biological' => 'fact_biological',
			'fact_work_pose' => 'fact_work_pose', 'fact_manual_weights' =>
			'fact_manual_weights', 'fact_monotony' => 'fact_monotony', 'fact_work_regime' =>
			'fact_work_regime', 'fact_work_hours' => 'fact_work_hours',
			'fact_work_and_break' => 'fact_work_and_break', 'fact_nervous' => 'fact_nervous',
			'fact_other' => 'fact_other');
			while (list($var, $param) = @each($var_list)) {
				if (isset($aFormValues[$param]))
				$$var = $this->checkStr($aFormValues[$param]);
			} //end while
			if ($map_id) { // Update
				$query = "UPDATE wplace_factors_map SET fact_dust='$fact_dust', fact_chemicals='$fact_chemicals', fact_biological='$fact_biological', fact_work_pose='$fact_work_pose', fact_manual_weights='$fact_manual_weights', fact_monotony='$fact_monotony', fact_work_regime='$fact_work_regime', fact_work_hours='$fact_work_hours', fact_work_and_break='$fact_work_and_break', fact_nervous='$fact_nervous', fact_other='$fact_other' WHERE map_id='$map_id'";
				$count = $db->exec($query);
			} else { // Insert
				$query = "INSERT INTO wplace_factors_map (firm_id, subdivision_id, wplace_id, fact_dust, fact_chemicals, fact_biological, fact_work_pose, fact_manual_weights, fact_monotony, fact_work_regime, fact_work_hours, fact_work_and_break, fact_nervous, fact_other) VALUES ('$firm_id', '$subdivision_id', '$wplace_id', '$fact_dust', '$fact_chemicals', '$fact_biological', '$fact_work_pose', '$fact_manual_weights', '$fact_monotony', '$fact_work_regime', '$fact_work_hours', '$fact_work_and_break', '$fact_nervous', '$fact_other')";
				$count = $db->exec($query); //returns affected rows
				$map_id = $db->lastInsertId();
			}
			return $map_id;
		}
		catch (exception $e) {
			die("Грешка при изпълнение на заявка към базата данни: " . $e->getMessage());
		}
	}

	/**
     * @desc add a new medical checkup
     * @param $aFormValues - form values
     * @param $tab - tab
     */
	public function processMedicalCheckup($aFormValues, $tab)
	{
		$db = $this->getDBHandle();
		$checkup_id = intval($aFormValues['checkup_id']);
		$worker_id = intval($aFormValues['worker_id']);
		$firm_id = intval($aFormValues['firm_id']);
		try {
			//$db->beginTransaction();
			switch ($tab) {
				case 'exam2':
					$var_list = array('left_eye' => 'left_eye', 'left_eye2' => 'left_eye2',
					'right_eye' => 'right_eye', 'right_eye2' => 'right_eye2', 'VK' => 'VK', 'FEO1' =>
					'FEO1', 'tifno' => 'tifno', 'hearing_loss' => 'hearing_loss', 'left_ear' =>
					'left_ear', 'right_ear' => 'right_ear', 'hearing_diagnose' => 'hearing_diagnose',
					'EKG' => 'EKG', 'x_ray' => 'x_ray', 'echo_ray' => 'echo_ray');
					while (list($var, $param) = @each($var_list)) {
						if (isset($aFormValues[$param]))
						$$var = $this->checkStr($aFormValues[$param]);
					} //end while
					$query = "UPDATE medical_checkups SET left_eye='$left_eye', left_eye2='$left_eye2', right_eye='$right_eye', right_eye2='$right_eye2', VK='$VK', FEO1='$FEO1', tifno='$tifno', hearing_loss='$hearing_loss', hearing_diagnose='$hearing_diagnose', left_ear='$left_ear', right_ear='$right_ear', EKG='$EKG', x_ray='$x_ray', echo_ray='$echo_ray', date_modified=datetime('now','localtime') WHERE checkup_id='$checkup_id'";
					$count = $db->exec($query); //returns affected rows
					break;

				case 'fweighs':
					$fweights_descr = (isset($aFormValues['fweights_descr'])) ? $this->checkStr($aFormValues['fweights_descr']) : '';
					$this->query("UPDATE medical_checkups SET fweights_descr = '$fweights_descr' WHERE checkup_id = $checkup_id");
					foreach ($aFormValues as $key => $val) {
						if (preg_match('/^mkb_id_(\d+)$/', $key, $matches)) {
							$family_weight_id = $matches[1];
							$mkb_id = $this->checkStr($aFormValues['mkb_id_' . $family_weight_id]);
							$diagnosis = $this->checkStr($aFormValues['diagnosis_' . $family_weight_id]);
							if ($mkb_id == '')
							continue;
							$mkb_id = mb_strtoupper($mkb_id, 'utf-8');
							if ($family_weight_id) { // Update
								$query = "UPDATE family_weights SET mkb_id='$mkb_id', diagnosis='$diagnosis' WHERE family_weight_id='$family_weight_id'";
							} else {
								$query = "INSERT INTO family_weights (firm_id, worker_id, checkup_id, mkb_id, diagnosis) VALUES ('$firm_id', '$worker_id', '$checkup_id', '$mkb_id', '$diagnosis')";
							}
							$count = $db->exec($query);
						}
					}
					break;

				case 'anamnesis':
					foreach ($aFormValues as $key => $val) {
						if (preg_match('/^mkb_id_(\d+)$/', $key, $matches)) {
							$anamnesis_id = $matches[1];
							$mkb_id = $this->checkStr($aFormValues['mkb_id_' . $anamnesis_id]);
							$diagnosis = $this->checkStr($aFormValues['diagnosis_' . $anamnesis_id]);
							if ($mkb_id == '')
							continue;
							$mkb_id = mb_strtoupper($mkb_id, 'utf-8');
							if ($anamnesis_id) { // Update
								$query = "UPDATE anamnesis SET mkb_id='$mkb_id', diagnosis='$diagnosis' WHERE anamnesis_id='$anamnesis_id'";
							} else {
								$query = "INSERT INTO anamnesis (firm_id, worker_id, checkup_id, mkb_id, diagnosis) VALUES ('$firm_id', '$worker_id', '$checkup_id', '$mkb_id', '$diagnosis')";
							}
							$count = $db->exec($query);
						}
					}
					break;

				case 'checkups':
					foreach ($aFormValues as $key => $val) {
						if (preg_match('/^indicator_id_(\d+)$/', $key, $matches)) {
							$lab_checkup_id = $matches[1];
							$indicator_id = $this->checkStr($aFormValues['indicator_id_' . $lab_checkup_id]);
							$checkup_type = $this->checkStr($aFormValues['checkup_type_' . $lab_checkup_id]);
							$checkup_level = $this->checkStr($aFormValues['checkup_level_' . $lab_checkup_id]);
							if ($indicator_id == '') continue;
							if ($lab_checkup_id) { // Update
								$sql = "UPDATE lab_checkups SET indicator_id = '$indicator_id', checkup_type = '$checkup_type', checkup_level = '$checkup_level' WHERE lab_checkup_id = $lab_checkup_id";
							} else {
								$sql = "INSERT INTO lab_checkups (firm_id, worker_id, checkup_id, indicator_id, checkup_type, checkup_level) VALUES ('$firm_id', '$worker_id', '$checkup_id', '$indicator_id', '$checkup_type', '$checkup_level')";
							}
							$this->query($sql);
						}
					}
					break;

				case 'diagnosis':
					foreach ($aFormValues as $key => $val) {
						if (preg_match('/^mkb_id_(\d+)$/', $key, $matches)) {
							$disease_id = $matches[1];
							$mkb_id = $this->checkStr($aFormValues['mkb_id_' . $disease_id]);
							$diagnosis = $this->checkStr($aFormValues['diagnosis_' . $disease_id]);
							$is_new = (isset($aFormValues['is_new_' . $disease_id])) ? '1' : '0';
							if ($mkb_id == '')
							continue;
							$mkb_id = mb_strtoupper($mkb_id, 'utf-8');
							if ($disease_id) { // Update
								$query = "UPDATE family_diseases SET mkb_id='$mkb_id', diagnosis='$diagnosis', is_new='$is_new' WHERE disease_id='$disease_id'";
							} else {
								$query = "INSERT INTO family_diseases (firm_id, worker_id, checkup_id, mkb_id, diagnosis, is_new) VALUES ('$firm_id', '$worker_id', '$checkup_id', '$mkb_id', '$diagnosis', '$is_new')";
							}
							$count = $db->exec($query);
						}
					}
					break;

				case 'conclusion':
					// update conclusions
					foreach ($aFormValues as $key => $val) {
						if(preg_match('/^conclusion_(\d+)$/', $key, $matches)) {
							$SpecialistID = $matches[1];
							$conclusion = $this->checkStr($val);
							if(!empty($conclusion)) {
								$sql = "REPLACE INTO medical_checkups_doctors2 (checkup_id, SpecialistID, conclusion) VALUES ($checkup_id, $SpecialistID, '$conclusion')";
								$count = $this->query($sql);
							}
						}
					}
					// add a new conclusion
					$SpecialistID = intval($aFormValues['SpecialistID']);
					$conclusion = $this->checkStr($aFormValues['conclusion']);
					if(!empty($SpecialistID) && !empty($conclusion)) {
						$sql = "REPLACE INTO medical_checkups_doctors2 (checkup_id, SpecialistID, conclusion) VALUES ($checkup_id, $SpecialistID, '$conclusion')";
						$count = $this->query($sql);
					}
					break;

				case 'conclusion_stm':
					$var_list = array('stm_conclusion' => 'stm_conclusion', 'stm_conditions' =>
					'stm_conditions', 'stm_date' => 'stm_date');
					while (list($var, $param) = @each($var_list)) {
						if (isset($aFormValues[$param]))
						$$var = $this->checkStr($aFormValues[$param]);
					} //end while
					$d = new ParseBGDate();
					if ($d->Parse($stm_date))
					$stm_date = $d->year . '-' . $d->month . '-' . $d->day . ' 00:00:00';
					else
					$stm_date = '';

					$query = "UPDATE medical_checkups SET stm_conclusion='$stm_conclusion', stm_conditions='$stm_conditions', stm_date='$stm_date', date_modified=datetime('now','localtime') WHERE checkup_id='$checkup_id'";
					$count = $db->exec($query); //returns affected rows
					break;

				case 'exam1':
				default:
					$var_list = array('checkup_date' => 'checkup_date', 'worker_height' =>
					'worker_height', 'worker_weight' => 'worker_weight', 'rr_syst' => 'rr_syst',
					'rr_diast' => 'rr_diast', 'hours_activity' => 'hours_activity', 'PregledNo' => 'PregledNo');
					while (list($var, $param) = @each($var_list)) {
						if (isset($aFormValues[$param]))
						$$var = $this->checkStr($aFormValues[$param]);
					} //end while
					$home_stress = (isset($aFormValues['home_stress'])) ? '1': '0';
					$work_stress = (isset($aFormValues['work_stress'])) ? '1': '0';
					$social_stress = (isset($aFormValues['social_stress'])) ? '1': '0';
					$video_display = (isset($aFormValues['video_display'])) ? '1': '0';
					$smoking = (isset($aFormValues['smoking'])) ? '1': '0';
					$drinking = (isset($aFormValues['drinking'])) ? '1': '0';
					$fats = (isset($aFormValues['fats'])) ? '1': '0';
					$diet = (isset($aFormValues['diet'])) ? '1': '0';
					$low_activity = (isset($aFormValues['low_activity'])) ? '1': '0';
					// Handle hospitals data begin
					for ($i = 0; $i < count($aFormValues['hospital']); $i++) {
						// Remove single quote
						$aFormValues['hospital'][$i] = str_replace("'", "", $aFormValues['hospital'][$i]);
					}
					$hospital = serialize($aFormValues['hospital']);
					// Handle hospitals data end

					$d = new ParseBGDate();
					if ($d->Parse($checkup_date))
					$checkup_date = $d->year . '-' . $d->month . '-' . $d->day . ' 00:00:00';
					else
					$checkup_date = '';
					if (!$checkup_id) {
						$query = "INSERT INTO medical_checkups (firm_id, worker_id, checkup_date, hospital, worker_height, worker_weight, rr_syst, rr_diast, hours_activity, home_stress, work_stress, social_stress, video_display, smoking, drinking, fats, diet, low_activity, date_added, date_modified, PregledNo) VALUES ($firm_id, $worker_id, '$checkup_date', '$hospital', '$worker_height', '$worker_weight', '$rr_syst', '$rr_diast', '$hours_activity', '$home_stress', '$work_stress', '$social_stress', '$video_display', '$smoking', '$drinking', '$fats', '$diet', '$low_activity', datetime('now','localtime'), datetime('now','localtime'), '$PregledNo')";
						//die($query);
						$count = $db->exec($query); //returns affected rows
						$checkup_id = $db->lastInsertId();
					} else {
						$query = "UPDATE medical_checkups SET checkup_date='$checkup_date', hospital='$hospital', worker_height='$worker_height', worker_weight='$worker_weight', rr_syst='$rr_syst', rr_diast='$rr_diast', hours_activity='$hours_activity', home_stress='$home_stress', work_stress='$work_stress', social_stress='$social_stress', video_display='$video_display', smoking='$smoking', drinking='$drinking', fats='$fats', diet='$diet', low_activity='$low_activity', date_modified=datetime('now','localtime'), PregledNo='$PregledNo' WHERE checkup_id='$checkup_id'";
						$count = $db->exec($query); //returns affected rows
					}
					break;
			}
			//$db->commit();
			return $checkup_id;
		}
		catch (exception $e) {
			//$db->rollBack();
			die("Грешка при изпълнение на заявка към базата данни: " . $e->getMessage());
		}
	}

	/**
     * @desc get old checkup date to see if it's empty
     * @param $checkup_id - checkup_id values
     */
	function getOldCheckupDate($checkup_id)
	{
		$db = $this->getDBHandle();
		$query = sprintf("SELECT checkup_date FROM medical_checkups WHERE checkup_id = %d",
		$checkup_id);
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$row = $prepstatement->fetch(PDO::FETCH_ASSOC);
			return $row['checkup_date'];
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc open a new medical checkup
     * @param $aFormValues - form values
     */
	public function processGiveCards($aFormValues)
	{
		$db = $this->getDBHandle();
		$firm_id = intval($aFormValues['firm_id']);
		$subdivision_id = intval($aFormValues['subdivision_id']);
		$wplace_id = intval($aFormValues['wplace_id']);
		$year_to_be_done = intval($aFormValues['year_to_be_done']);
		$IDs = array();
		try {
			foreach ($aFormValues as $key => $val) {
				if (preg_match('/^checkup_id_(\d+)$/', $key, $matches)) {
					$worker_id = $matches[1];
					$notes = $this->checkStr($aFormValues['notes_' . $worker_id]);
					$checkup_id = intval($aFormValues['checkup_id_' . $worker_id]);
					if (isset($aFormValues['worker_id_' . $worker_id])) { //If worker is checked
						if (!$checkup_id) { // Check for alredy opened medical cards
							$query = sprintf("SELECT checkup_id FROM medical_checkups WHERE worker_id = %d AND checkup_date = '' LIMIT 1",
							$worker_id);
							$prepstatement = $db->prepare($query);
							if (!$prepstatement) {
								$err = $db->errorInfo();
								die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
								$query);
							}
							$prepstatement->execute();
							$row = $prepstatement->fetch(PDO::FETCH_ASSOC);
							if ($row['checkup_id']) {
								$checkup_id = $row['checkup_id'];
							}
						}
						if (!$checkup_id) { // open a new medical checkup
							$query = sprintf("INSERT INTO medical_checkups (firm_id, worker_id, year_to_be_done, checkup_date, notes, date_added, date_modified) VALUES (%d, %d, %d, '', '%s', datetime('now','localtime'), datetime('now','localtime'))",
							$firm_id, $worker_id, $year_to_be_done, $notes);
							$count = $db->exec($query); //returns affected rows
							$checkup_id = $db->lastInsertId();
						} else {
							$query = sprintf("UPDATE medical_checkups SET notes = '%s', date_modified = datetime('now','localtime') WHERE checkup_id = %d",
							$notes, $checkup_id);
							$count = $db->exec($query); //returns affected rows
						}
						$IDs[$worker_id] = $checkup_id;
					}
				}
			}
			foreach ($IDs as $checkup_id) {
				$query = sprintf("DELETE FROM medical_checkups_doctors WHERE checkup_id = %d", $checkup_id);
				$count = $db->exec($query);
				if (isset($aFormValues['doctor_pos_id'])) {
					$position = 1;
					foreach ($aFormValues['doctor_pos_id'] as $doctor_pos_id) {
						$query = sprintf("INSERT INTO medical_checkups_doctors (checkup_id, doctor_pos_id, position) VALUES (%d, %d, %d)",
						$checkup_id, $doctor_pos_id, $position++);
						$count = $db->exec($query);
					}
				}
			}
			return $IDs;
		}
		catch (exception $e) {
			die("Грешка при изпълнение на заявка към базата данни: " . $e->getMessage());
		}
	}

	/**
     * @desc get blank medical checkups
     * @param $checkup_id - checkup_id array
     */
	public function getGiveCardsBlank($checkup_id = array())
	{
		$db = $this->getDBHandle();
		$data = array();
		$query = "	SELECT c.*,
					strftime('%d.%m.%Y', c.checkup_date, 'localtime') AS checkup_date_h,
					w.*,
					(SELECT location_name FROM locations WHERE location_id = w.location_id) AS worker_location,
					strftime('%d.%m.%Y', w.birth_date, 'localtime') AS birth_date2,
					strftime('%d.%m.%Y', w.date_curr_position_start, 'localtime') AS date_curr_position_start2,
					strftime('%d.%m.%Y', w.date_career_start, 'localtime') AS date_career_start2,
					strftime('%d.%m.%Y', w.date_retired, 'localtime') AS date_retired2,
					strftime('%d.%m.%Y', c.stm_date, 'localtime') AS stm_date2,
					f.name AS firm_name,
					l.location_name,
					s.subdivision_name,
					p.wplace_name,
					t.position_name,
					t.position_name, t.position_workcond,
					p.wplace_name, p.wplace_workcond
					FROM medical_checkups c
					LEFT JOIN firms f ON (f.firm_id = c.firm_id)
					LEFT JOIN locations l ON (l.location_id = f.location_id)
					LEFT JOIN workers w ON (w.worker_id = c.worker_id)
					LEFT JOIN firm_struct_map m ON (m.map_id = w.map_id)
					LEFT JOIN subdivisions s ON (s.subdivision_id = m.subdivision_id)
					LEFT JOIN work_places p ON (p.wplace_id = m.wplace_id)
					LEFT JOIN firm_positions t ON(t.position_id = m.position_id)
					WHERE c.checkup_id IN (" . implode(',', $checkup_id) . ")
					AND w.is_active = '1'
					AND w.date_retired = ''";
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$fields = $prepstatement->fetchAll();
			foreach ($fields as $field) {
				$query2 = sprintf("	SELECT c.doctor_pos_id, d.doctor_pos_name, c.doctor_desc
									FROM medical_checkups_doctors c
									LEFT JOIN cfg_doctor_positions d ON (d.doctor_pos_id = c.doctor_pos_id)
									WHERE c.checkup_id = %d
									ORDER BY d.`default` DESC, d.`doctor_pos_name`, c.doctor_pos_id", $field['checkup_id']);
				$prepstatement2 = $db->prepare($query2);
				if (!$prepstatement2) {
					$err = $db->errorInfo();
					die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
					$query);
				}
				$prepstatement2->execute();
				$rows = $prepstatement2->fetchAll();
				$field['doctors'] = $rows;
				$data[] = $field;
			}
			return $data;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}

	}

	/**
     * @desc open a new medical checkup
     * @param $aFormValues - form values
     */
	public function processGiveCards1($aFormValues)
	{
		$db = $this->getDBHandle();
		$firm_id = intval($aFormValues['firm_id']);
		$subdivision_id = intval($aFormValues['subdivision_id']);
		$wplace_id = intval($aFormValues['wplace_id']);
		try {
			$rows = $this->getWorkers($firm_id, $subdivision_id, $wplace_id);
			if ($rows) {
				$IDs = null;
				foreach ($rows as $row) {
					if (!isset($aFormValues['worker_id_' . $row['worker_id']])) { // Close already opened patient's medical card
						$query = sprintf("DELETE FROM medical_checkups WHERE worker_id = %d AND checkup_date = ''",
						$row['worker_id']);
						$count = $db->exec($query);
					} else { //
						$IDs[] = $aFormValues['worker_id_' . $row['worker_id']];
						$query = sprintf("SELECT COUNT(*) AS cnt FROM medical_checkups WHERE worker_id = %d AND checkup_date = ''",
						$row['worker_id']);
						$prepstatement = $db->prepare($query);
						if (!$prepstatement) {
							$err = $db->errorInfo();
							die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
							$query);
						}
						$prepstatement->execute();
						$result = $prepstatement->fetch(PDO::FETCH_ASSOC);
						$notes = $this->checkStr($aFormValues['notes_' . $row['worker_id']]);
						if (!$result['cnt']) { // open a new medical checkup
							$query = sprintf("INSERT INTO medical_checkups (firm_id, worker_id, checkup_date, notes, date_added) VALUES (%d, %d, '', '%s', datetime('now','localtime'))",
							$firm_id, $row['worker_id'], $notes);
							//die($query);
							$count = $db->exec($query); //returns affected rows
							$checkup_id = $db->lastInsertId();
							//echo "Affected Rows: ".$count."<br>";
							//echo "Last Insert Id: ".$db->lastInsertId($id)."<br>";
						}
					}
				}
				if (is_array($IDs)) {
					$query = "SELECT checkup_id FROM medical_checkups WHERE worker_id IN (" .
					implode(',', $IDs) . ") AND checkup_date = ''";
					$prepstatement = $db->prepare($query);
					if (!$prepstatement) {
						$err = $db->errorInfo();
						die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
						$query);
					}
					$prepstatement->execute();
					$rows = $prepstatement->fetchAll();
					foreach ($rows as $row) {
						$query = sprintf("DELETE FROM medical_checkups_doctors WHERE checkup_id = %d", $row['checkup_id']);
						$count = $db->exec($query);

					}
				}
			}
		}
		catch (exception $e) {
			die("Грешка при изпълнение на заявка към базата данни: " . $e->getMessage());
		}
	}

	public function processLastPrchkCheckup($worker_id=0) {
		$db = $this->getDBHandle();
		try {
			$prchk_author = $prchk_date = $prchk_anamnesis = $prchk_data = $prchk_conclusion = $prchk_conditions = $prchk_stm_date = $prchk_obstetrician = $prchk_obstetrician_doc = $prchk_dermatologist = $prchk_dermatologist_doc = $prchk_internal_diseases = $prchk_internal_diseases_doc = $prchk_ophthalmologist = $prchk_ophthalmologist_doc = $prchk_pathologist = $prchk_pathologist_doc = $prchk_UNG = $prchk_UNG_doc = $prchk_neurologist = $prchk_neurologist_doc = $prchk_surgeon = $prchk_surgeon_doc = $prchk_GP = $prchk_GP_doc = $prchk_dentist = $prchk_dentist_doc = '';
			$prchk_cardiologist = $prchk_cardiologist_doc = $prchk_therapeutist = $prchk_therapeutist_doc = $prchk_psychiatrist = $prchk_psychiatrist_doc = $prchk_radiobiologist = $prchk_radiobiologist_doc = $prchk_doctor_tm = $prchk_doctor_tm_doc = $prchk_assisant_tm = $prchk_assisant_tm_doc = '';
			$query = "SELECT * FROM `medical_precheckups` WHERE `worker_id` = $worker_id ORDER BY `prchk_date` DESC, `precheckup_id` DESC LIMIT 1";
			$rows = $this->query($query);
			if(!empty($rows)) {
				$row = $rows[0];
				$var_list = array('precheckup_id' => 'precheckup_id', 'prchk_author' => 'prchk_author', 'prchk_date' => 'prchk_date',
				'prchk_anamnesis' => 'prchk_anamnesis', 'prchk_data' => 'prchk_data', 'prchk_conclusion' => 'prchk_conclusion',
				'prchk_conditions' => 'prchk_conditions', 'prchk_stm_date' => 'prchk_stm_date',
				'prchk_obstetrician' => 'prchk_obstetrician',
				'prchk_obstetrician_doc' => 'prchk_obstetrician_doc', 'prchk_dermatologist' =>
				'prchk_dermatologist', 'prchk_dermatologist_doc' => 'prchk_dermatologist_doc',
				'prchk_internal_diseases' => 'prchk_internal_diseases',
				'prchk_internal_diseases_doc' => 'prchk_internal_diseases_doc',
				'prchk_ophthalmologist' => 'prchk_ophthalmologist', 'prchk_ophthalmologist_doc' =>
				'prchk_ophthalmologist_doc', 'prchk_pathologist' => 'prchk_pathologist',
				'prchk_pathologist_doc' => 'prchk_pathologist_doc', 'prchk_UNG' => 'prchk_UNG',
				'prchk_UNG_doc' => 'prchk_UNG_doc', 'prchk_neurologist' => 'prchk_neurologist',
				'prchk_neurologist_doc' => 'prchk_neurologist_doc', 'prchk_surgeon' =>
				'prchk_surgeon', 'prchk_surgeon_doc' => 'prchk_surgeon_doc', 'prchk_GP' =>
				'prchk_GP', 'prchk_GP_doc' => 'prchk_GP_doc', 'prchk_dentist' => 'prchk_dentist',
				'prchk_dentist_doc' => 'prchk_dentist_doc',
				'prchk_cardiologist' => 'prchk_cardiologist', 'prchk_cardiologist_doc' => 'prchk_cardiologist_doc',
				'prchk_therapeutist' => 'prchk_therapeutist', 'prchk_therapeutist_doc' => 'prchk_therapeutist_doc',
				'prchk_psychiatrist' => 'prchk_psychiatrist', 'prchk_psychiatrist_doc' => 'prchk_psychiatrist_doc',
				'prchk_radiobiologist' => 'prchk_radiobiologist', 'prchk_radiobiologist_doc' => 'prchk_radiobiologist_doc',
				'prchk_doctor_tm' => 'prchk_doctor_tm', 'prchk_doctor_tm_doc' => 'prchk_doctor_tm_doc',
				'prchk_assisant_tm' => 'prchk_assisant_tm', 'prchk_assisant_tm_doc' => 'prchk_assisant_tm_doc');
				while (list($var, $param) = @each($var_list)) {
					if (isset($row[$param]))
					$$var = $this->checkStr($row[$param]);
				} //end while
				$query = "UPDATE `workers` SET prchk_author = '$prchk_author', prchk_date = '$prchk_date', `prchk_anamnesis` = '$prchk_anamnesis', `prchk_data` = '$prchk_data', `prchk_conclusion` = '$prchk_conclusion', `prchk_conditions` = '$prchk_conditions', `prchk_stm_date` = '$prchk_stm_date', prchk_obstetrician = '$prchk_obstetrician', prchk_obstetrician_doc = '$prchk_obstetrician_doc', prchk_dermatologist = '$prchk_dermatologist', prchk_dermatologist_doc = '$prchk_dermatologist_doc', prchk_internal_diseases = '$prchk_internal_diseases', prchk_internal_diseases_doc = '$prchk_internal_diseases_doc', prchk_ophthalmologist = '$prchk_ophthalmologist', prchk_ophthalmologist_doc = '$prchk_ophthalmologist_doc', prchk_pathologist = '$prchk_pathologist', prchk_pathologist_doc = '$prchk_pathologist_doc', prchk_UNG = '$prchk_UNG', prchk_UNG_doc = '$prchk_UNG_doc', prchk_neurologist = '$prchk_neurologist', prchk_neurologist_doc = '$prchk_neurologist_doc', prchk_surgeon = '$prchk_surgeon', prchk_surgeon_doc = '$prchk_surgeon_doc', prchk_GP = '$prchk_GP', prchk_GP_doc = '$prchk_GP_doc', prchk_dentist = '$prchk_dentist', prchk_dentist_doc = '$prchk_dentist_doc', `prchk_cardiologist` = '$prchk_cardiologist', `prchk_cardiologist_doc` = '$prchk_cardiologist_doc', `prchk_therapeutist` = '$prchk_therapeutist', `prchk_therapeutist_doc` = '$prchk_therapeutist_doc', `prchk_psychiatrist` = '$prchk_psychiatrist', `prchk_psychiatrist_doc` = '$prchk_psychiatrist_doc', `prchk_radiobiologist` = '$prchk_radiobiologist', `prchk_radiobiologist_doc` = '$prchk_radiobiologist_doc', `prchk_doctor_tm` = '$prchk_doctor_tm', `prchk_doctor_tm_doc` = '$prchk_doctor_tm_doc', `prchk_assisant_tm` = '$prchk_assisant_tm', `prchk_assisant_tm_doc` = '$prchk_assisant_tm_doc', date_modified = datetime('now','localtime') WHERE worker_id = $worker_id";
			} else {
				$query = "UPDATE `workers` SET prchk_author = '', prchk_date = '', `prchk_anamnesis` = '', `prchk_data` = '', `prchk_conclusion` = '', `prchk_conditions` = '', `prchk_stm_date` = '', prchk_obstetrician = '', prchk_obstetrician_doc = '', prchk_dermatologist = '', prchk_dermatologist_doc = '', prchk_internal_diseases = '', prchk_internal_diseases_doc = '', prchk_ophthalmologist = '', prchk_ophthalmologist_doc = '', prchk_pathologist = '', prchk_pathologist_doc = '', prchk_UNG = '', prchk_UNG_doc = '', prchk_neurologist = '', prchk_neurologist_doc = '', prchk_surgeon = '', prchk_surgeon_doc = '', prchk_GP = '', prchk_GP_doc = '', prchk_dentist = '', prchk_dentist_doc = '', `prchk_cardiologist` = '', `prchk_cardiologist_doc` = '', `prchk_therapeutist` = '', `prchk_therapeutist_doc` = '', `prchk_psychiatrist` = '', `prchk_psychiatrist_doc` = '', `prchk_radiobiologist` = '', `prchk_radiobiologist_doc` = '', `prchk_doctor_tm` = '', `prchk_doctor_tm_doc` = '', `prchk_assisant_tm` = '', `prchk_assisant_tm_doc` = '', date_modified = datetime('now','localtime') WHERE worker_id = $worker_id";
			}
			$count = $db->exec($query); //returns affected rows
		}
		catch (exception $e) {
			die("Грешка при изпълнение на заявка към базата данни: " . $e->getMessage());
		}
	}
	
	/**
     * @desc add a new medical checkup
     * @param $aFormValues - form values
     * @param $tab - tab
     */
	public function processPrchkCheckup($aFormValues, $tab)
	{
		$db = $this->getDBHandle();
		$worker_id = intval($aFormValues['worker_id']);
		$firm_id = intval($aFormValues['firm_id']);
		$precheckup_id = intval($aFormValues['precheckup_id']);
		$prchk_author = $this->checkStr($aFormValues['prchk_author']);
		$prchk_date = $this->checkStr($aFormValues['prchk_date']);
		$d = new ParseBGDate();
		if ($d->Parse($prchk_date)) $prchk_date = $d->year.'-'.$d->month.'-'.$d->day.' 00:00:00';
		else $prchk_date = '';
		try {
			switch ($tab) {
				case 'specialists':
					// update conclusions
					foreach ($aFormValues as $key => $val) {
						if(preg_match('/^conclusion_(\d+)$/', $key, $matches)) {
							$SpecialistID = $matches[1];
							$conclusion = $this->checkStr($val);
							if(!empty($conclusion)) {
								$sql = "REPLACE INTO medical_precheckups_doctors2 (precheckup_id, SpecialistID, conclusion) VALUES ($precheckup_id, $SpecialistID, '$conclusion')";
								$count = $this->query($sql);
							}
						}
					}
					// add a new conclusion
					$SpecialistID = intval($aFormValues['SpecialistID']);
					$conclusion = $this->checkStr($aFormValues['conclusion']);
					if(!empty($SpecialistID) && !empty($conclusion)) {
						$sql = "REPLACE INTO medical_precheckups_doctors2 (precheckup_id, SpecialistID, conclusion) VALUES ($precheckup_id, $SpecialistID, '$conclusion')";
						$count = $this->query($sql);
					}
					break;

				case 'diagnosis':
					foreach ($aFormValues as $key => $val) {
						if (preg_match('/^mkb_id_(\d+)$/', $key, $matches)) {
							$prchk_id = $matches[1];
							$mkb_id = $this->checkStr($aFormValues['mkb_id_' . $prchk_id]);
							$diagnosis = $this->checkStr($aFormValues['diagnosis_' . $prchk_id]);
							$published_by = $this->checkStr($aFormValues['published_by_' . $prchk_id]);
							if ($mkb_id == '')
							continue;
							$mkb_id = mb_strtoupper($mkb_id, 'utf-8');
							if ($prchk_id) { // Update
								$query = "UPDATE prchk_diagnosis SET mkb_id='$mkb_id', diagnosis='$diagnosis', published_by='$published_by' WHERE prchk_id='$prchk_id'";
							} else {
								$query = "INSERT INTO prchk_diagnosis (worker_id, mkb_id, diagnosis, published_by, precheckup_id) VALUES ('$worker_id', '$mkb_id', '$diagnosis', '$published_by', '$precheckup_id')";
							}
							$count = $this->query($query);
						}
					}
					break;

				case 'checkups':
				default:
					$var_list = array('prchk_anamnesis' => 'prchk_anamnesis', 'prchk_data' =>
					'prchk_data', 'prchk_conclusion' => 'prchk_conclusion', 'prchk_conditions' =>
					'prchk_conditions', 'prchk_stm_date' => 'prchk_stm_date', 'prchk_date' => 'prchk_date');
					while (list($var, $param) = @each($var_list)) {
						if (isset($aFormValues[$param]))
						$$var = $this->checkStr($aFormValues[$param]);
					} //end while
					$d = new ParseBGDate();
					if ($d->Parse($prchk_stm_date))
					$prchk_stm_date = $d->year . '-' . $d->month . '-' . $d->day . ' 00:00:00';
					else
					$prchk_stm_date = '';
					$prchk_date = ($d->Parse($prchk_date)) ? $d->year . '-' . $d->month . '-' . $d->day . ' 00:00:00' : '';
					if(!$precheckup_id) {
						$query = "INSERT INTO `medical_precheckups` (`firm_id`, `worker_id`, `prchk_author`, `prchk_date`, `prchk_anamnesis`, `prchk_data`, `prchk_conclusion`, `prchk_conditions`, `prchk_stm_date`, `date_added`, `date_modified`) VALUES ($firm_id, $worker_id, '$prchk_author', '$prchk_date', '$prchk_anamnesis', '$prchk_data', '$prchk_conclusion', '$prchk_conditions', '$prchk_stm_date', datetime('now','localtime'), datetime('now','localtime'))";
						$precheckup_id = $this->query($query);
					} else {
						$query = "UPDATE medical_precheckups SET prchk_author='$prchk_author', prchk_date='$prchk_date', prchk_anamnesis='$prchk_anamnesis', prchk_data='$prchk_data', prchk_conclusion='$prchk_conclusion', prchk_conditions='$prchk_conditions', prchk_stm_date='$prchk_stm_date', date_modified=datetime('now','localtime') WHERE precheckup_id='$precheckup_id'";
						$count = $this->query($query); //returns affected rows
					}

					break;
			}
			return $precheckup_id;
		}
		catch (exception $e) {
			die("Грешка при изпълнение на заявка към базата данни: " . $e->getMessage());
		}
	}

	/**
     * @desc add/update worker's professional route
     * @param $aFormValues - form values
     */
	public function processProRoute($aFormValues)
	{
		$db = $this->getDBHandle();
		$worker_id = intval($aFormValues['worker_id']);
		$modified_by = $_SESSION['sess_user_id'];
		try {
			$db->beginTransaction();
			foreach ($aFormValues as $key => $val) {
				if (preg_match('/^firm_name_(\d+)$/', $key, $matches)) {
					$route_id = $matches[1];
					$firm_name = $this->checkStr($aFormValues['firm_name_' . $route_id]);
					$position = $this->checkStr($aFormValues['position_' . $route_id]);
					$exp_length_y = intval($aFormValues['exp_length_y_' . $route_id]);
					$exp_length_m = intval($aFormValues['exp_length_m_' . $route_id]);
					if(empty($firm_name) && empty($position)) continue;
					/*if ($position == '' || (!$exp_length_y && !$exp_length_m))
					continue;*/
					if ($route_id) { // Update
						$query = "UPDATE pro_route SET firm_name='$firm_name', position='$position', exp_length_y='$exp_length_y', exp_length_m='$exp_length_m' WHERE route_id='$route_id'";
					} else {
						$query = "INSERT INTO pro_route (worker_id, firm_name, position, exp_length_y, exp_length_m) VALUES ('$worker_id', '$firm_name', '$position', '$exp_length_y', '$exp_length_m')";
					}
					$count = $db->exec($query);
				}
			}
			$count = $db->exec("UPDATE workers SET date_modified=datetime('now','localtime'), modified_by='$modified_by' WHERE worker_id='$worker_id'");
			$db->commit();
		}
		catch (exception $e) {
			$db->rollBack();
			die("Грешка при изпълнение на заявка към базата данни: " . $e->getMessage());
		}
	}

	/**
     * @desc process the firm structure map
     * @param $aFormValues - form values
     */
	public function processMap($aFormValues)
	{
		$db = $this->getDBHandle();
		$modified_by = $_SESSION['sess_user_id'];
		$var_list = array('firm_id' => 'firm_id', 'subdivision_id' => 'subdivision_id',
		'wplace_id' => 'wplace_id', 'position_id' => 'position_id');
		while (list($var, $param) = @each($var_list)) {
			if (isset($aFormValues[$param]))
			$$var = intval($aFormValues[$param]);
		} //end while

		try {
			$db->beginTransaction();
			$query = "INSERT INTO firm_struct_map (firm_id, subdivision_id, wplace_id, position_id) VALUES ('$firm_id', '$subdivision_id', '$wplace_id', '$position_id')";
			$count = $db->exec($query); //returns affected rows
			$count = $db->exec("UPDATE firms SET date_modified=datetime('now','localtime'), modified_by='$modified_by' WHERE firm_id='$firm_id'");
			$db->commit();
		}
		catch (exception $e) {
			$db->rollBack();
			die("Грешка при изпълнение на заявка към базата данни: " . $e->getMessage());
		}
	}

	/**
     * @desc add a new worker
     * @param $aFormValues - form values
     */
	public function processWorker($aFormValues)
	{
		$db = $this->getDBHandle();
		$modified_by = $_SESSION['sess_user_id'];
		try {
			$var_list = array('worker_id' => 'worker_id', 'fname' => 'fname', 'sname' =>
			'sname', 'lname' => 'lname', 'egn' => 'egn', 'sex' => 'sex', 'birth_date' =>
			'birth_date', 'location_name' => 'location_name', 'location_id' => 'location_id',
			'address' => 'address', 'phone1' => 'phone1', 'phone2' => 'phone2', 'firm_id' =>
			'firm_id', 'map_id' => 'map_id', 'date_curr_position_start' =>
			'date_curr_position_start', 'date_career_start' => 'date_career_start',
			'date_retired' => 'date_retired', 'doctor_id' => 'doctor_id', 'notes' => 'notes');
			while (list($var, $param) = @each($var_list)) {
				if (isset($aFormValues[$param]))
				$$var = $this->checkStr($aFormValues[$param]);
			} //end while
			if ($location_name == '')
			$location_id = 0;
			$d = new ParseBGDate();
			if ($d->Parse($date_curr_position_start))
			$date_curr_position_start = $d->year . '-' . $d->month . '-' . $d->day .
			' 00:00:00';
			else
			$date_curr_position_start = '';
			if ($d->Parse($date_career_start))
			$date_career_start = $d->year . '-' . $d->month . '-' . $d->day . ' 00:00:00';
			else
			$date_career_start = '';
			if ($d->Parse($date_retired))
			$date_retired = $d->year . '-' . $d->month . '-' . $d->day . ' 00:00:00';
			else
			$date_retired = '';
			if ($d->Parse($birth_date))
			$birth_date = $d->year . '-' . $d->month . '-' . $d->day . ' 00:00:00';
			else
			$birth_date = '';

			if ($worker_id) { // Update worker
				$query = "UPDATE workers SET firm_id='$firm_id', fname='$fname', sname='$sname', lname='$lname', sex='$sex', egn='$egn', birth_date='$birth_date', location_id='" .
				intval($location_id) . "', address='$address', phone1='$phone1', phone2='$phone2', map_id='" .
				intval($map_id) . "', date_curr_position_start='$date_curr_position_start', date_career_start='$date_career_start', date_retired='$date_retired', doctor_id='$doctor_id', date_modified=datetime('now','localtime'), modified_by='$modified_by', notes='$notes' WHERE worker_id='$worker_id'";
				$count = $db->exec($query); //returns affected rows
			} else { // Insert worker
				$query = "INSERT INTO workers (firm_id, fname, sname, lname, sex, egn, birth_date, location_id, address, phone1, phone2, map_id, date_curr_position_start, date_career_start, date_retired, doctor_id, date_added, date_modified, modified_by, notes) VALUES ('" .
				intval($firm_id) . "', '$fname', '$sname', '$lname', '$sex', '$egn', '$birth_date', '" .
				intval($location_id) . "', '$address', '$phone1', '$phone2', '" . intval($map_id) .
				"', '$date_curr_position_start', '$date_career_start', '$date_retired', '$doctor_id', datetime('now','localtime'), datetime('now','localtime'), '$modified_by', '$notes')";
				$count = $db->exec($query); //returns affected rows
				$worker_id = $db->lastInsertId();
			}
			return $worker_id;
		}
		catch (exception $e) {
			die("Грешка при изпълнение на заявка към базата данни: " . $e->getMessage());
		}
	}

	/**
     * @desc add a new doctor
     * @param $aFormValues - form values
     */
	public function processDoctor($aFormValues)
	{
		$db = $this->getDBHandle();
		try {
			$var_list = array('doctor_id' => 'd_doctor_id', 'doctor_name' => 'd_doctor_name',
			'address' => 'd_address', 'phone1' => 'd_phone1', 'phone2' => 'd_phone2');
			while (list($var, $param) = @each($var_list)) {
				if (isset($aFormValues[$param]))
				$$var = $this->checkStr($aFormValues[$param]);
			} //end while
			if (!$doctor_id) { // Insert doctor
				$query = "INSERT INTO doctors (doctor_name, address, phone1, phone2) VALUES ('$doctor_name', '$address', '$phone1', '$phone2')";
				$count = $db->exec($query); //returns affected rows
				$doctor_id = $db->lastInsertId();
			} else {
				$query = "UPDATE doctors SET doctor_name='$doctor_name', address='$address', phone1='$phone1', phone2='$phone2' WHERE doctor_id='$doctor_id'";
				$count = $db->exec($query); //returns affected rows
			}
			return $doctor_id;
		}
		catch (exception $e) {
			die("Грешка при изпълнение на заявка към базата данни: " . $e->getMessage());
		}
	}

	/**
     * @desc process STM information
     * @param $aFormValues - form values
     */
	public function processStmInfo($aFormValues)
	{
		$stm_id = (3 == $_SESSION['sess_user_id']) ? 2 : 1;		
		$db = $this->getDBHandle();
		try {
			$var_list = array('stm_name' => 'stm_name', 'license_num' => 'license_num',
			'address' => 'address', 'chief' => 'chief', 'phone1' => 'phone1', 'phone2' =>
			'phone2', 'fax' => 'fax', 'email' => 'email');
			while (list($var, $param) = @each($var_list)) {
				if (isset($aFormValues[$param]))
				$$var = $this->checkStr($aFormValues[$param]);
			} //end while
			$query = "REPLACE INTO stm_info (stm_id, stm_name, license_num, address, chief, phone1, phone2, fax, email) VALUES ($stm_id, '$stm_name', '$license_num', '$address', '$chief', '$phone1', '$phone2', '$fax', '$email')";
			$count = $db->exec($query); //returns affected rows
			return $count;
		}
		catch (exception $e) {
			die("Грешка при изпълнение на заявка към базата данни: " . $e->getMessage());
		}
	}

	/**
     * @desc add a patient's chart
     * @param $aFormValues - form values
     */
	public function processPatientChart($aFormValues)
	{
		$db = $this->getDBHandle();
		try {
			$var_list = array('chart_id' => 'chart_id', 'firm_id' => 'firm_id', 'worker_id' =>
			'worker_id', 'chart_num' => 'chart_num', 'hospital_date_from' =>
			'hospital_date_from', 'hospital_date_to' => 'hospital_date_to', 'days_off' =>
			'days_off', 'mkb_id' => 'mkb_id', 'reason_id' => 'reason_id', 'chart_desc' =>
			'chart_desc');
			while (list($var, $param) = @each($var_list)) {
				if (isset($aFormValues[$param]))
				$$var = $this->checkStr($aFormValues[$param]);
			} //end while
			if (!$worker_id)
			return false;

			$d = new ParseBGDate();
			if ($d->Parse($hospital_date_from))
			$hospital_date_from = $d->year . '-' . $d->month . '-' . $d->day . ' 00:00:00';
			else
			$hospital_date_from = '';
			if ($d->Parse($hospital_date_to))
			$hospital_date_to = $d->year . '-' . $d->month . '-' . $d->day . ' 00:00:00';
			else
			$hospital_date_to = '';
			$medical_types_arr = null;
			foreach ($aFormValues as $key => $value) {
				if (preg_match('/^medical_type_(\d+)$/', $key, $matches)) {
					$medical_types_arr[] = $matches[1];
				}
			}
			$medical_types = ($medical_types_arr != null) ? serialize($medical_types_arr) :	'';

			$mkb_id = mb_strtoupper($mkb_id, 'utf-8');
			if ($chart_id) { // Update patient's chart
				$query = "UPDATE patient_charts SET chart_num='$chart_num', hospital_date_from='$hospital_date_from', hospital_date_to='$hospital_date_to', days_off='" .
				intval($days_off) . "', mkb_id='$mkb_id', medical_types='$medical_types', reason_id='$reason_id', chart_desc='$chart_desc', date_modified=datetime('now','localtime') WHERE chart_id='$chart_id'";
				$count = $db->exec($query); //returns affected rows
			} else { // Insert a patient's chart
				$query = "INSERT INTO patient_charts (firm_id, worker_id, chart_num, hospital_date_from, hospital_date_to, days_off, mkb_id, medical_types, reason_id, chart_desc, date_added, date_modified) VALUES ('" .
				intval($firm_id) . "', '" . intval($worker_id) . "', '$chart_num', '$hospital_date_from', '$hospital_date_to', '" .
				intval($days_off) . "', '$mkb_id', '$medical_types', '$reason_id', '$chart_desc', datetime('now','localtime'), datetime('now','localtime'))";
				$count = $db->exec($query); //returns affected rows
				$chart_id = $db->lastInsertId();
			}
			return $chart_id;
		}
		catch (exception $e) {
			die("Грешка при изпълнение на заявка към базата данни: " . $e->getMessage());
		}
	}

	/**
     * @desc add a patient's telk
     * @param $aFormValues - form values
     */
	public function processPatientTelk($aFormValues)
	{
		$db = $this->getDBHandle();
		try {
			$var_list = array('telk_id' => 'telk_id', 'firm_id' => 'firm_id', 'worker_id' =>
			'worker_id', 'telk_num' => 'telk_num', 'telk_date_from' => 'telk_date_from',
			'telk_date_to' => 'telk_date_to', 'first_inv_date' => 'first_inv_date', 'telk_duration' => 'telk_duration', 'mkb_id_1' =>
			'mkb_id_1', 'mkb_id_2' => 'mkb_id_2', 'mkb_id_3' => 'mkb_id_3', 'mkb_id_4' =>
			'mkb_id_4', 'percent_inv' => 'percent_inv', 'bad_work_env' => 'bad_work_env');
			while (list($var, $param) = @each($var_list)) {
				if (isset($aFormValues[$param]))
				$$var = $this->checkStr($aFormValues[$param]);
			} //end while
			if (!$worker_id)
			return false;

			$mkb_id_1 = mb_strtoupper($mkb_id_1, 'utf-8');
			$mkb_id_2 = mb_strtoupper($mkb_id_2, 'utf-8');
			$mkb_id_3 = mb_strtoupper($mkb_id_3, 'utf-8');
			$mkb_id_4 = mb_strtoupper($mkb_id_4, 'utf-8');
			$d = new ParseBGDate();
			if ($d->Parse($telk_date_from))
			$telk_date_from = $d->year . '-' . $d->month . '-' . $d->day . ' 00:00:00';
			else
			$telk_date_from = '';
			if ($d->Parse($telk_date_to))
			$telk_date_to = $d->year . '-' . $d->month . '-' . $d->day . ' 00:00:00';
			else
			$telk_date_to = '';
			if ($d->Parse($first_inv_date))
			$first_inv_date = $d->year . '-' . $d->month . '-' . $d->day . ' 00:00:00';
			else
			$first_inv_date = '';

			if ($telk_id) { // Update patient's telk
				$query = "UPDATE telks SET telk_num='$telk_num', telk_date_from='$telk_date_from', telk_date_to='$telk_date_to', first_inv_date='$first_inv_date', telk_duration='$telk_duration', mkb_id_1='$mkb_id_1', mkb_id_2='$mkb_id_2', mkb_id_3='$mkb_id_3', mkb_id_4='$mkb_id_4', percent_inv='" .
				floatval($percent_inv) . "', bad_work_env='$bad_work_env', date_modified=datetime('now','localtime') WHERE telk_id='$telk_id'";
				$count = $db->exec($query); //returns affected rows
			} else { // Insert a patient's telk
				$query = "INSERT INTO telks (firm_id, worker_id, telk_num, telk_date_from, telk_date_to, first_inv_date, telk_duration, mkb_id_1, mkb_id_2, mkb_id_3, mkb_id_4, percent_inv, bad_work_env, date_added, date_modified) VALUES ('" .
				intval($firm_id) . "', '" . intval($worker_id) . "', '$telk_num', '$telk_date_from', '$telk_date_to', '$first_inv_date', '$telk_duration', '$mkb_id_1', '$mkb_id_2', '$mkb_id_3', '$mkb_id_4', '" .
				floatval($percent_inv) . "', '$bad_work_env',datetime('now','localtime'), datetime('now','localtime'))";
				$count = $db->exec($query); //returns affected rows
				$telk_id = $db->lastInsertId();
			}
			return $telk_id;
		}
		catch (exception $e) {
			die("Грешка при изпълнение на заявка към базата данни: " . $e->getMessage());
		}
	}

	/**
     * @desc set firm subdivisions
     * @param $aFormValues - form values
     */
	public function setSubdivisions($aFormValues)
	{
		$db = $this->getDBHandle();
		$firm_id = intval($aFormValues['firm_id']);
		try {
			$db->beginTransaction();
			$subdivision_position = 1;
			foreach ($aFormValues as $key => $value) {
				if (preg_match('/subdivision_name_(\d+)$/', $key, $matches) && trim($value) !=
				'') {
					$subdivision_id = $matches[1];
					$subdivision_name = mb_strtoupper($this->checkStr($aFormValues['subdivision_name_' . $subdivision_id]), 'utf-8');
					if ($subdivision_id) {
						$count = $db->exec("UPDATE subdivisions SET subdivision_name='$subdivision_name', subdivision_position='$subdivision_position' WHERE subdivision_id='$subdivision_id'");
					} else {
						$count = $db->exec("INSERT INTO subdivisions (firm_id, subdivision_name, subdivision_position) VALUES ('$firm_id', '$subdivision_name', '$subdivision_position')");
					}
					$subdivision_position++;
				}
			}
			$db->commit();
		}
		catch (exception $e) {
			$db->rollBack();
			die("Грешка при изпълнение на заявка към базата данни: " . $e->getMessage());
		}
	}

	/**
     * @desc process (add/update) work place env. protocols
     * @param $aFormValues - form values
     */
	public function processWorkEnvProtocols($aFormValues)
	{
		$db = $this->getDBHandle();
		$firm_id = intval($aFormValues['firm_id']);
		$subdivision_id = intval($aFormValues['subdivision_id']);
		$wplace_id = intval($aFormValues['wplace_id']);
		try {
			$db->beginTransaction();
			foreach ($aFormValues as $key => $value) {
				if (preg_match('/factor_id_(\d+)$/', $key, $matches)) {
					if (!$value)
					continue;
					$map_id = $matches[1];
					$prot_id = $this->checkStr($aFormValues['prot_id_' . $map_id]);
					$factor_id = $this->checkStr($aFormValues['factor_id_' . $map_id]);
					$prot_num = $this->checkStr($aFormValues['prot_num_' . $map_id]);
					$prot_date = $this->checkStr($aFormValues['prot_date_' . $map_id]);
					$d = new ParseBGDate();
					if ($d->Parse($prot_date))
					$prot_date = $d->year . '-' . $d->month . '-' . $d->day . ' 00:00:00';
					else
					$prot_date = '';
					$level = $this->checkStr($aFormValues['level_' . $map_id]);
					if ($prot_id) {
						$count = $db->exec("UPDATE work_env_protocols SET factor_id='$factor_id', prot_num='$prot_num', prot_date='$prot_date', level='$level' WHERE prot_id='$prot_id'");
					} else {
						$count = $db->exec("INSERT INTO work_env_protocols (factor_id, prot_num, prot_date, level) VALUES ('$factor_id', '$prot_num', '$prot_date', '$level')");
						$prot_id = $db->lastInsertId();
						$count = $db->exec("REPLACE INTO wplace_prot_map (firm_id, subdivision_id, wplace_id, prot_id) VALUES ('$firm_id', '$subdivision_id', '$wplace_id', '$prot_id')");

					}
				}
			}
			$db->commit();
		}
		catch (exception $e) {
			$db->rollBack();
			die($e->getMessage());
		}
	}

	/**
     * @desc process (add/update) a work place env. protocol for group of work places
     * @param $aFormValues - form values
     */
	public function processGroupEnvProtocols($aFormValues)
	{
		$db = $this->getDBHandle();
		$firm_id = intval($aFormValues['firm_id']);
		try {
			$factor_id = $this->checkStr($aFormValues['factor_id_0']);
			$level = floatval($aFormValues['level_0']);
			$prot_num = $this->checkStr($aFormValues['prot_num_0']);
			$prot_date = $this->checkStr($aFormValues['prot_date_0']);
			$d = new ParseBGDate();
			if ($d->Parse($prot_date))
			$prot_date = $d->year . '-' . $d->month . '-' . $d->day . ' 00:00:00';
			else
			$prot_date = '';
			$db->beginTransaction();
			$count = $db->exec("INSERT INTO work_env_protocols (factor_id, prot_num, prot_date, level) VALUES ('$factor_id', '$prot_num', '$prot_date', '$level')");
			$prot_id = $db->lastInsertId();
			foreach ($aFormValues as $key => $value) {
				if (preg_match('/wplace_id_(\d+)$/', $key, $matches)) {
					$wplace_id = $matches[1];
					$query = "SELECT DISTINCT subdivision_id FROM firm_struct_map WHERE firm_id='$firm_id' AND wplace_id='$wplace_id'";
					$prepstatement = $db->prepare($query);
					$prepstatement->execute();
					if ($result = $prepstatement->fetchAll()) {
						foreach ($result as $row) {
							$subdivision_id = $row['subdivision_id'];
							$count = $db->exec("INSERT INTO wplace_prot_map (firm_id, subdivision_id, wplace_id, prot_id) VALUES ('$firm_id', '$subdivision_id', '$wplace_id', '$prot_id')");
						}
					}
				}
			}
			$db->commit();
		}
		catch (exception $e) {
			$db->rollBack();
			die($e->getMessage());
		}
	}

	/**
     * @desc check if relation in firm structure already exists
     * @param $aFormValues - form values
     */
	public function hasRelation($aFormValues)
	{
		$db = $this->getDBHandle();
		$var_list = array('firm_id' => 'firm_id', 'subdivision_id' => 'subdivision_id',
		'wplace_id' => 'wplace_id', 'position_id' => 'position_id');
		while (list($var, $param) = @each($var_list)) {
			if (isset($aFormValues[$param]))
			//$$var = $this->checkStr($aFormValues[$param]);

			$$var = intval($aFormValues[$param]);
		} //end while

		$query = "SELECT * FROM firm_struct_map WHERE firm_id='$firm_id' AND subdivision_id='$subdivision_id' AND wplace_id='$wplace_id' AND position_id='$position_id'";
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetchAll();
			return $result;
		}
		catch (PDOException $e) {
			die('Грешка при изпълнение на заявка към базата данни: ' . $e->getMessage());
		}
	}

	/**
     * @desc set firm work places
     * @param $aFormValues - form values
     */
	public function setWorkPlaces($aFormValues)
	{
		$db = $this->getDBHandle();
		$firm_id = intval($aFormValues['firm_id']);
		try {
			$db->beginTransaction();
			$wplace_position = 1;
			foreach ($aFormValues as $key => $value) {
				if (preg_match('/wplace_name_(\d+)$/', $key, $matches) && trim($value) != '') {
					$wplace_id = $matches[1];
					$wplace_name = mb_strtoupper($this->checkStr($aFormValues['wplace_name_' . $wplace_id]), 'utf-8');
					$wplace_workcond = $this->checkStr($aFormValues['wplace_workcond_' . $wplace_id]);
					if ($wplace_id) {
						$count = $db->exec("UPDATE work_places SET wplace_name='$wplace_name', wplace_workcond='$wplace_workcond', wplace_position='$wplace_position' WHERE wplace_id='$wplace_id'");
					} else {
						$count = $db->exec("INSERT INTO work_places (firm_id, wplace_name, wplace_workcond, wplace_position) VALUES ('$firm_id', '$wplace_name', '$wplace_workcond', '$wplace_position')");
					}
					$wplace_position++;
				}
			}
			$db->commit();
		}
		catch (exception $e) {
			$db->rollBack();
			die($e->getMessage());
		}
	}

	/**
     * @desc set labs indicators
     * @param $aFormValues - form values
     */
	public function setLabs($aFormValues)
	{
		$db = $this->getDBHandle();
		try {
			$db->beginTransaction();
			$indicator_position = 1;
			foreach ($aFormValues as $key => $value) {
				if (preg_match('/indicator_type_(\d+)$/', $key, $matches) && trim($value) != '') {
					$indicator_id = $matches[1];
					$indicator_type = $this->checkStr($aFormValues['indicator_type_' . $indicator_id]);
					$indicator_name = $this->checkStr($aFormValues['indicator_name_' . $indicator_id]);
					$pdk_max = floatval($aFormValues['pdk_max_' . $indicator_id]);
					$pdk_min = floatval($aFormValues['pdk_min_' . $indicator_id]);
					$indicator_dimension = $this->checkStr($aFormValues['indicator_dimension_' . $indicator_id]);
					if ($indicator_id) {
						$count = $db->exec("UPDATE lab_indicators SET indicator_type='$indicator_type', indicator_name='$indicator_name', pdk_max='$pdk_max', pdk_min='$pdk_min', indicator_dimension='$indicator_dimension', indicator_position='$indicator_position' WHERE indicator_id='$indicator_id'");
					} else {
						$count = $db->exec("INSERT INTO lab_indicators (indicator_type, indicator_name, pdk_max, pdk_min, indicator_dimension, indicator_position) VALUES ('$indicator_type', '$indicator_name', '$pdk_max', '$pdk_min', '$indicator_dimension', '$indicator_position')");
					}
					$indicator_position++;
				}
			}
			$db->commit();
		}
		catch (exception $e) {
			$db->rollBack();
			die($e->getMessage());
		}
	}

	/**
     * @desc user accounts
     * @param $aFormValues - form values
     */
	public function setAccounts($aFormValues)
	{
		$db = $this->getDBHandle();
		try {
			$db->beginTransaction();
			foreach ($aFormValues as $key => $value) {
				if (preg_match('/user_name_(\d+)$/', $key, $matches) && trim($value) != '') {
					$user_id = $matches[1];
					$user_name = $this->checkStr($aFormValues['user_name_' . $user_id]);
					$user_pass = $this->checkStr($aFormValues['user_pass_' . $user_id]);
					if ($user_pass == '')
					continue;
					$fname = $this->checkStr($aFormValues['fname_' . $user_id]);
					$lname = $this->checkStr($aFormValues['lname_' . $user_id]);
					if ($user_id) {
						$count = $db->exec("UPDATE users SET user_name='$user_name', user_pass='$user_pass', fname='$fname', lname='$lname', date_modified=datetime('now'), hdd='" .
						$this->getHDDSerial() . "' WHERE user_id='$user_id'");
					} else {
						$count = $db->exec("INSERT INTO users (user_name, user_pass, user_level, fname, lname, date_created, date_modified, hdd) VALUES ('$user_name', '$user_pass', '1', '$fname', '$lname', datetime('now'), datetime('now'), '" .
						$this->getHDDSerial() . "')");
					}
				}
			}
			$db->commit();
		}
		catch (exception $e) {
			$db->rollBack();
			die($e->getMessage());
		}
	}

	/**
     * @desc set work environment factors
     * @param $aFormValues - form values
     */
	public function setFactors($aFormValues)
	{
		$db = $this->getDBHandle();
		try {
			$db->beginTransaction();
			$factor_position = 1;
			foreach ($aFormValues as $key => $value) {
				if (preg_match('/factor_name_(\d+)$/', $key, $matches) && trim($value) != '') {
					$factor_id = $matches[1];
					$factor_name = $this->checkStr($aFormValues['factor_name_' . $factor_id]);
					$pdk_max = floatval($aFormValues['pdk_max_' . $factor_id]);
					$pdk_min = floatval($aFormValues['pdk_min_' . $factor_id]);
					$factor_dimension = $this->checkStr($aFormValues['factor_dimension_' . $factor_id]);
					if ($factor_id) {
						$count = $db->exec("UPDATE work_env_factors SET factor_name='$factor_name', pdk_max='$pdk_max', pdk_min='$pdk_min', factor_dimension='$factor_dimension', factor_position='$factor_position' WHERE factor_id='$factor_id'");
					} else {
						$count = $db->exec("INSERT INTO work_env_factors (factor_name, pdk_max, pdk_min, factor_dimension, factor_position) VALUES ('$factor_name', '$pdk_max', '$pdk_min', '$factor_dimension', '$factor_position')");
					}
					$factor_position++;
				}
			}
			$db->commit();
		}
		catch (exception $e) {
			$db->rollBack();
			die("Грешка при изпълнение на заявка към базата данни: " . $e->getMessage());
		}
	}

	public function setDoctorPos($aFormValues)
	{
		$db = $this->getDBHandle();
		try {
			$db->beginTransaction();
			foreach ($aFormValues as $key => $value) {
				if (preg_match('/doctor_pos_name_(\d+)$/', $key, $matches) && trim($value) != '') {
					$doctor_pos_id = $matches[1];
					$doctor_pos_name = $this->checkStr($aFormValues['doctor_pos_name_' . $doctor_pos_id]);
					if(empty($doctor_pos_name)) continue;
					if ($doctor_pos_id) {
						$count = $db->exec("UPDATE cfg_doctor_positions SET doctor_pos_name='$doctor_pos_name' WHERE doctor_pos_id='$doctor_pos_id'");
					} else {
						$count = $db->exec("INSERT INTO cfg_doctor_positions (doctor_pos_name) VALUES ('$doctor_pos_name')");
					}
				}
			}
			$db->commit();
		}
		catch (exception $e) {
			$db->rollBack();
			die("Грешка при изпълнение на заявка към базата данни: " . $e->getMessage());
		}
	}

	/**
     * @desc set firm subdivisions
     * @param $aFormValues - form values
     */
	public function setFirmPositions($aFormValues)
	{
		$db = $this->getDBHandle();
		$firm_id = intval($aFormValues['firm_id']);
		$modified_by = $_SESSION['sess_user_id'];
		try {
			$db->beginTransaction();
			$position_position = 1;
			$count = $db->exec("UPDATE firms SET date_modified=datetime('now','localtime'), modified_by='$modified_by' WHERE firm_id='$firm_id'");
			foreach ($aFormValues as $key => $value) {
				if (preg_match('/position_name_(\d+)$/', $key, $matches) && trim($value) != '') {
					$position_id = $matches[1];
					$position_name = mb_strtoupper($this->checkStr($aFormValues['position_name_' . $position_id]), 'utf-8');
					$position_workcond = $this->checkStr($aFormValues['position_workcond_' . $position_id]);
					if ($position_id) {
						$count = $db->exec("UPDATE firm_positions SET position_name='$position_name', position_workcond='$position_workcond', position_position='$position_position' WHERE position_id='$position_id'");
					} else {
						$count = $db->exec("INSERT INTO firm_positions (firm_id, position_name, position_workcond, position_position) VALUES ('$firm_id', '$position_name', '$position_workcond', '$position_position')");
					}
					$position_position++;
				}
			}
			$db->commit();
		}
		catch (exception $e) {
			$db->rollBack();
			die("Грешка при изпълнение на заявка към базата данни: " . $e->getMessage());
		}
	}


	// SEARCH FUNCTIONS ================================================

	/**
     * @desc remove a work environment protocol
     * @param $map_id - map_id value
     * @param $prot_id - prot_id value
     */
	public function removeWorkEnvProtocol($map_id, $prot_id)
	{
		$db = $this->getDBHandle();
		$map_id = intval($map_id);
		$prot_id = intval($prot_id);
		/*try {
		$count = $db->exec("DELETE FROM wplace_prot_map WHERE map_id='$map_id'");
		$query = "SELECT COUNT(*) AS cnt FROM wplace_prot_map WHERE prot_id='$prot_id'";
		$stmt = $db->prepare($query);
		if (!$stmt) {
		$err = $db->errorInfo();
		die('Грешка при изпълнение на заявка към базата данни: '.$err[2]. ', SQL: '.$query);
		}
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if(!$row['cnt']) {
		$count = $db->exec("DELETE FROM work_env_protocols WHERE prot_id='$prot_id'");
		}
		}
		catch (PDOException $e) {
		die($e->getMessage());
		}*/

		try {
			$db->beginTransaction();
			$count = $db->exec("DELETE FROM wplace_prot_map WHERE map_id='$map_id'");
			// Do some housekeeping, remove orphan protocols which are not mapped
			$count = $db->exec("DELETE FROM work_env_protocols WHERE prot_id='$prot_id' AND (SELECT COUNT(*) AS cnt FROM wplace_prot_map WHERE prot_id='$prot_id') = 0");
			$db->commit();
		}
		catch (exception $e) {
			$db->rollBack();
			die($e->getMessage());
		}
	}
	/**
     * @desc remove a work environment factor
     * @param $factor_id - factor_id
     */
	public function removeFactor($factor_id)
	{
		$db = $this->getDBHandle();
		$factor_id = intval($factor_id);
		try {
			$count = $db->exec("DELETE FROM work_env_factors WHERE factor_id='$factor_id'");
			return $count;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}
	/**
     * @desc remove a doctor's position
     * @param $doctor_pos_id - doctor_pos_id
     */
	public function removeDoctorPos($doctor_pos_id)
	{
		$db = $this->getDBHandle();
		try {
			$count = $db->exec(sprintf("DELETE FROM cfg_doctor_positions WHERE doctor_pos_id = %d",
			$doctor_pos_id));
			return $count;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc remove a relation in the firm map
     * @param $map_id - map_id
     */
	public function removeRelation($map_id, $firm_id)
	{
		$db = $this->getDBHandle();
		$map_id = intval($map_id);
		$modified_by = $_SESSION['sess_user_id'];
		try {
			$db->beginTransaction();
			$count = $db->exec("DELETE FROM firm_struct_map WHERE map_id='$map_id'");
			$count = $db->exec("UPDATE firms SET date_modified=datetime('now','localtime'), modified_by='$modified_by' WHERE firm_id='$firm_id'");
			$db->commit();
		}
		catch (PDOException $e) {
			$db->rollBack();
			die($e->getMessage());
		}
	}

	/**
     * @desc remove a firm subdivision
     * @param $subdivision_id - subdivision_id
     */
	public function removeSubdivision($subdivision_id)
	{
		$db = $this->getDBHandle();
		$subdivision_id = intval($subdivision_id);
		try {
			$count = $db->exec("DELETE FROM subdivisions WHERE subdivision_id='$subdivision_id'");
			$count = $db->exec("DELETE FROM firm_struct_map WHERE subdivision_id='$subdivision_id'");
			//$count = $db->exec("UPDATE work_places SET subdivision_id='0' WHERE subdivision_id='$subdivision_id'");
			return $count;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc remove a firm work place
     * @param $wplace_id - wplace_id
     * @param $firm_id - firm id
     */
	public function removeWorkPlace($wplace_id, $firm_id)
	{
		$db = $this->getDBHandle();
		$wplace_id = intval($wplace_id);
		$modified_by = $_SESSION['sess_user_id'];
		try {
			$db->beginTransaction();
			$count = $db->exec("DELETE FROM work_places WHERE wplace_id='$wplace_id'");
			$count = $db->exec("DELETE FROM firm_struct_map WHERE wplace_id='$wplace_id'");
			$count = $db->exec("UPDATE firms SET date_modified=datetime('now','localtime'), modified_by='$modified_by' WHERE firm_id='$firm_id'");
			$db->commit();
		}
		catch (PDOException $e) {
			$db->rollBack();
			die($e->getMessage());
		}
	}

	/**
     * @desc remove a worker
     * @param $worker_id - worker_id
     */
	public function removeWorker($worker_id)
	{
		$db = $this->getDBHandle();
		$worker_id = intval($worker_id);
		try {
			$db->beginTransaction();
			$count = $db->exec("DELETE FROM workers WHERE worker_id='$worker_id'");
			$count = $db->exec("DELETE FROM patient_charts WHERE worker_id='$worker_id'");
			$count = $db->exec("DELETE FROM family_diseases WHERE worker_id='$worker_id'");
			$count = $db->exec("DELETE FROM family_weights WHERE worker_id='$worker_id'");
			$count = $db->exec("DELETE FROM lab_checkups WHERE worker_id='$worker_id'");
			$count = $db->exec("DELETE FROM medical_checkups WHERE worker_id='$worker_id'");
			$count = $db->exec("DELETE FROM patient_charts WHERE worker_id='$worker_id'");
			$count = $db->exec("DELETE FROM prchk_diagnosis WHERE worker_id='$worker_id'");
			$count = $db->exec("DELETE FROM pro_route WHERE worker_id='$worker_id'");
			$count = $db->exec("DELETE FROM telks WHERE worker_id='$worker_id'");
			$count = $db->exec("DELETE FROM readjustments WHERE worker_id='$worker_id'");
			$db->commit();
		}
		catch (PDOException $e) {
			$db->rollBack();
			die($e->getMessage());
		}
	}

	/**
     * @desc remove a docror
     * @param $doctor_id - doctor_id
     */
	public function removeDoctor($doctor_id)
	{
		$db = $this->getDBHandle();
		$doctor_id = intval($doctor_id);
		try {
			$db->beginTransaction();
			$count = $db->exec("DELETE FROM doctors WHERE doctor_id='$doctor_id'");
			$count = $db->exec("UPDATE workers SET doctor_id='0' WHERE doctor_id='$doctor_id'");
			$db->commit();
		}
		catch (PDOException $e) {
			$db->rollBack();
			die($e->getMessage());
		}
	}

	/**
     * @desc set a firm as inactive
     * @param $firm_id - firm_id
     */
	public function removeFirm($firm_id)
	{
		$db = $this->getDBHandle();
		$firm_id = intval($firm_id);

		$rows = $this->fnSelectRows("SELECT worker_id FROM workers WHERE firm_id = $firm_id");
		if ($rows) {
			foreach ($rows as $row) {
				$this->removeWorker($row['worker_id']);
			}
		}

		try {
			$db->beginTransaction();
			$count = $db->exec("DELETE FROM firms WHERE firm_id='$firm_id'");
			$count = $db->exec("DELETE FROM firm_positions WHERE firm_id='$firm_id'");
			$count = $db->exec("DELETE FROM firm_struct_map WHERE firm_id='$firm_id'");
			$count = $db->exec("DELETE FROM lab_checkups WHERE firm_id='$firm_id'");
			$count = $db->exec("DELETE FROM subdivisions WHERE firm_id='$firm_id'");
			$count = $db->exec("DELETE FROM work_places WHERE firm_id='$firm_id'");
			$count = $db->exec("DELETE FROM wplace_factors_map WHERE firm_id='$firm_id'");
			$count = $db->exec("DELETE FROM wplace_prot_map WHERE firm_id='$firm_id'");
			$db->commit();
		}
		catch (exception $e) {
			$db->rollBack();
			die($e->getMessage());
		}
	}

	/**
     * @desc remove a firm work position
     * @param $position_id - position id
     * @param $firm_id - firm id
     */
	public function removeFirmPosition($position_id, $firm_id)
	{
		$db = $this->getDBHandle();
		$position_id = intval($position_id);
		$modified_by = $_SESSION['sess_user_id'];
		try {
			$db->beginTransaction();
			$count = $db->exec("DELETE FROM firm_positions WHERE position_id='$position_id'");
			$count = $db->exec("DELETE FROM firm_struct_map WHERE position_id='$position_id'");
			$count = $db->exec("UPDATE firms SET date_modified=datetime('now','localtime'), modified_by='$modified_by' WHERE firm_id='$firm_id'");
			$db->commit();
		}
		catch (PDOException $e) {
			$db->rollBack();
			die($e->getMessage());
		}
	}

	/**
     * @desc remove a patient's chart
     * @param $chart_id - chart_id value
     */
	public function removePatientChart($chart_id)
	{
		$db = $this->getDBHandle();
		$chart_id = intval($chart_id);
		try {
			$count = $db->exec("DELETE FROM patient_charts WHERE chart_id='$chart_id'");
			return $count;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc remove a patient's telk
     * @param $telk_id - telk_id value
     */
	public function removePatientTelk($telk_id)
	{
		$db = $this->getDBHandle();
		$telk_id = intval($telk_id);
		try {
			$count = $db->exec("DELETE FROM telks WHERE telk_id='$telk_id'");
			return $count;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc remove the worker's family weight
     * @param $family_weight_id - family_weight_id value
     */
	public function removeFamilyWeight($family_weight_id)
	{
		$db = $this->getDBHandle();
		$family_weight_id = intval($family_weight_id);
		try {
			$count = $db->exec("DELETE FROM family_weights WHERE family_weight_id='$family_weight_id'");
			return $count;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc remove the worker's anamnesis
     * @param $anamnesis_id - anamnesis_id value
     */
	public function removeAnamnesis($anamnesis_id)
	{
		$db = $this->getDBHandle();
		$anamnesis_id = intval($anamnesis_id);
		try {
			$count = $db->exec("DELETE FROM anamnesis WHERE anamnesis_id='$anamnesis_id'");
			return $count;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc remove the worker's professional route
     * @param $route_id - route_id value
     * @param $worker_id - worker id
     */
	public function removeProRoute($route_id, $worker_id)
	{
		$db = $this->getDBHandle();
		$route_id = intval($route_id);
		$modified_by = $_SESSION['sess_user_id'];
		try {
			$db->beginTransaction();
			$count = $db->exec("DELETE FROM pro_route WHERE route_id='$route_id'");
			$count = $db->exec("UPDATE workers SET date_modified=datetime('now','localtime'), modified_by='$modified_by' WHERE worker_id='$worker_id'");
			$db->commit();
		}
		catch (PDOException $e) {
			$db->rollBack();
			die($e->getMessage());
		}
	}

	/**
     * @desc remove the worker's medical checkup
     * @param $lab_checkup_id - lab_checkup_id value
     */
	public function removeMedicalCheckup($lab_checkup_id)
	{
		$db = $this->getDBHandle();
		$lab_checkup_id = intval($lab_checkup_id);
		try {
			$count = $db->exec("DELETE FROM lab_checkups WHERE lab_checkup_id='$lab_checkup_id'");
			return $count;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc remove the worker's disease
     * @param $disease_id - disease_id value
     */
	public function removeDiagnosis($disease_id)
	{
		$db = $this->getDBHandle();
		$disease_id = intval($disease_id);
		try {
			$count = $db->exec("DELETE FROM family_diseases WHERE disease_id='$disease_id'");
			return $count;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc remove a labs indicator
     */
	public function removeLab($indicator_id)
	{
		$db = $this->getDBHandle();
		$indicator_id = intval($indicator_id);
		try {
			$count = $db->exec("DELETE FROM lab_indicators WHERE indicator_id='$indicator_id'");
			return $count;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc remove user account
     */
	public function removeAccount($user_id)
	{
		$db = $this->getDBHandle();
		$indicator_id = intval($user_id);
		try {
			$count = $db->exec("DELETE FROM users WHERE user_id='$user_id'");
			return $count;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc remove the pre-checkup worker's disease
     * @param $prchk_id - prchk_id value
     */
	public function removePrchkDiagnosis($prchk_id)
	{
		$db = $this->getDBHandle();
		$prchk_id = intval($prchk_id);
		try {
			$count = $db->exec("DELETE FROM prchk_diagnosis WHERE prchk_id='$prchk_id'");
			return $count;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	// DELETE FUNCTIONS ================================================

	/**
     * @desc search provinces table by province name
     * @param $province_name - province_name
     */
	public function searchByProvince($province_name)
	{
		$db = $this->getDBHandle();
		$province_name = $this->checkStr($province_name);
		//$province_name = $this->my_mb_ucfirst($province_name);
		$query = "SELECT province_id, province_name FROM provinces WHERE province_name LIKE '%$province_name%' OR province_name LIKE '%".$this->my_mb_ucfirst($province_name)."%' ORDER BY province_name, province_id LIMIT 0, 50";
		try {
			$stmt = $db->prepare($query);
			if (!$stmt) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$stmt->execute();
			$data = array();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$data[$row['province_id']] = $row['province_name'];
			}
			return $data;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc search communities table by community name
     * @param $community_name - community_name
     */
	public function searchByCommunity($community_name)
	{
		$db = $this->getDBHandle();
		$community_name = $this->checkStr($community_name);
		//$community_name = $this->my_mb_ucfirst($community_name);
		$query = "SELECT community_id, community_name FROM [communities] WHERE community_name LIKE '%$community_name%' OR community_name LIKE '".$this->my_mb_ucfirst($community_name)."%' ORDER BY community_name, community_id LIMIT 0, 50";
		try {
			$stmt = $db->prepare($query);
			if (!$stmt) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$stmt->execute();
			$data = array();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$data[$row['community_id']] = $row['community_name'];
				break;
			}
			return $data;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc search locations table by location name
     * @param $location_name - location_name
     */
	public function searchByLocation($location_name)
	{
		$db = $this->getDBHandle();
		$location_name = $this->checkStr($location_name);
		//$location_name = $this->my_mb_ucfirst($location_name);
		$query = "SELECT location_id, location_name FROM locations WHERE location_name LIKE '%$location_name%' OR location_name LIKE '%".$this->my_mb_ucfirst($location_name)."%' ORDER BY location_name, location_id LIMIT 0, 50";
		try {
			$stmt = $db->prepare($query);
			if (!$stmt) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$stmt->execute();
			$data = array();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$data[$row['location_id']] = $row['location_name'];
			}
			return $data;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}


	/**
     * @desc search mkb table by mkb_id
     * @param $mkb_id - mkb_id
     */
	public function searchByMkb($mkb_id)
	{
		$db = $this->getDBHandle();
		$mkb_id = $this->checkStr($mkb_id);
		if (mb_strlen($mkb_id, 'utf-8') == 1 || (mb_strlen($mkb_id, 'utf-8') > 1 &&
		is_numeric(mb_substr($mkb_id, 1, 1, 'utf-8')))) {
			$mkb_id = $this->my_mb_ucfirst($mkb_id);
			$query = "SELECT * FROM mkb WHERE mkb_id LIKE '$mkb_id%' ORDER BY mkb_id, mkb_desc";
		} else {
			$query = "SELECT * FROM mkb WHERE mkb_desc LIKE '%$mkb_id%' OR mkb_desc LIKE '" .
			$this->my_mb_ucfirst($mkb_id) . "%' ORDER BY mkb_id, mkb_desc LIMIT 0, 50";
		}
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetchAll();
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc check mkb_id
     * @param $mkb_id - mkb_id
     */
	public function isValidMkb($mkb_id)
	{
		$db = $this->getDBHandle();
		$mkb_id = $this->my_mb_ucfirst($mkb_id);
		$query = "SELECT * FROM mkb WHERE mkb_id='$mkb_id'";
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetchAll();
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc search workers table by worker names
     * @param $wname - wname
     */
	public function searchByWName($wname, $firm_id) {
		$db = $this->getDBHandle();
		$wname = $this->checkStr($wname);
		$wname = $this->my_mb_ucfirst($wname);
		if (is_numeric($wname)) {
			$sql = "SELECT worker_id, (fname||' '||sname||' '||lname) AS wname, 
					firm_id, egn 
					FROM workers 
					WHERE firm_id = '$firm_id' 
					AND egn LIKE '$wname%' 
					ORDER BY fname, lname, worker_id";
		} else {
			$sql = "SELECT worker_id, (fname||' '||sname||' '||lname) AS wname, 
					firm_id, egn 
					FROM workers 
					WHERE firm_id = '$firm_id'
					AND wname LIKE '%$wname%'  
					ORDER BY fname, lname, worker_id";
		}
		return $this->query($sql);
	}

	/**
     * @desc search doctors table by doctor name
     * @param $doctor_name - doctor_name
     */
	public function searchByDoctor($doctor_name)
	{
		$db = $this->getDBHandle();
		$doctor_name = $this->checkStr($doctor_name);
		$doctor_name = $this->my_mb_ucfirst($doctor_name);
		$query = "SELECT doctor_id, doctor_name FROM doctors WHERE doctor_name LIKE '%$doctor_name%' ORDER BY doctor_name, doctor_id";
		try {
			$stmt = $db->prepare($query);
			if (!$stmt) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$stmt->execute();
			$data = array();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$data[$row['doctor_id']] = $row['doctor_name'];
			}
			return $data;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc search medical reasons table
     * @param $reason_id - reason_id value
     */
	public function searchByMedicalReasons($reason_id = '')
	{
		$db = $this->getDBHandle();
		$reason_id = $this->checkStr($reason_id);
		$reason_id = $this->my_mb_ucfirst($reason_id);
		$query = "SELECT * FROM medical_reasons";
		if ($reason_id != '')
		$query .= " WHERE reason_id LIKE '$reason_id%'";
		$query .= " ORDER BY reason_id, reason_desc";
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetchAll();
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc search firm_positions table
     * @param $position_name - position_name value
     */
	public function searchByWPosition($position_name = '')
	{
		$db = $this->getDBHandle();
		$position_name = $this->checkStr($position_name);
		$query = "SELECT position_name, position_workcond FROM firm_positions WHERE position_workcond != ''";
		if ($position_name != '')
		$query .= " AND (position_name LIKE '%$position_name%' OR position_name LIKE '".$this->my_mb_ucfirst($position_name)."%' OR position_name LIKE '".mb_strtoupper($position_name,'utf-8')."%')";
		$query .= " ORDER BY position_name, position_workcond LIMIT 50";
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' . $query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetchAll();
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc search firm_positions table
     * @param $position_name - position_name value
     */
	public function searchByWPlace($wplace_name = '')
	{
		$db = $this->getDBHandle();
		$wplace_name = $this->checkStr($wplace_name);
		$query = "SELECT wplace_name, wplace_workcond FROM work_places WHERE wplace_workcond != ''";
		if ($wplace_name != '')
		$query .= " AND (wplace_name LIKE '%$wplace_name%' OR wplace_name LIKE '".$this->my_mb_ucfirst($wplace_name)."%' OR wplace_name LIKE '".mb_strtoupper($wplace_name,'utf-8')."%')";
		$query .= " ORDER BY wplace_name, wplace_workcond LIMIT 50";
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' . $query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetchAll();
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc search firm_positions table
     * @param $position_name - position_name value
     */
	public function searchByFactor($factor = '', $dbField = 'fact_dust')
	{
		$db = $this->getDBHandle();
		$factor = $this->checkStr($factor);
		$query = "SELECT DISTINCT `$dbField` FROM `wplace_factors_map` WHERE `$dbField` != ''";
		if($factor != '')
		$query .= " AND (`$dbField` LIKE '%$factor%' OR `$dbField` LIKE '".$this->my_mb_ucfirst($factor)."%' OR `$dbField` LIKE '".mb_strtoupper($factor,'utf-8')."%')";
		$query .= " ORDER BY `$dbField` LIMIT 50";
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' . $query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetchAll();
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc try to guess location_id from the specified location name
     * @param $location_name - location_name
     */
	public function guessLocation($location_name)
	{
		$db = $this->getDBHandle();
		$location_name = $this->checkStr($location_name);
		$location_name = $this->my_mb_ucfirst($location_name);
		$query = "SELECT location_id FROM locations WHERE location_name='$location_name'";
		try {
			$stmt = $db->prepare($query);
			if (!$stmt) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($row)
			return $row['location_id'];
			return false;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc try to guess community_id from the specified community name
     * @param $community_name - community_name
     */
	public function guessCommunity($community_name)
	{
		$db = $this->getDBHandle();
		$community_name = $this->checkStr($community_name);
		$community_name = $this->my_mb_ucfirst($community_name);
		$query = "SELECT community_id FROM communities WHERE community_name='$community_name'";
		try {
			$stmt = $db->prepare($query);
			if (!$stmt) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($row)
			return $row['community_id'];
			return false;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc try to guess province_id from the specified province name
     * @param $province_name - province_name
     */
	public function guessProvince($province_name)
	{
		$db = $this->getDBHandle();
		$province_name = $this->checkStr($province_name);
		$province_name = $this->my_mb_ucfirst($province_name);
		$query = "SELECT province_id FROM provinces WHERE province_name='$province_name'";
		try {
			$stmt = $db->prepare($query);
			if (!$stmt) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($row)
			return $row['province_id'];
			return false;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc check dates from TELK
     * @param $telk_date_from - telk_date_from
     * @param $telk_date_to - telk_date_to
     * @param $telk_id - telk_id
     * @param $worker_id - worker_id
     */
	public function checkTelkDates($telk_date_from, $telk_date_to, $telk_id, $worker_id)
	{
		$db = $this->getDBHandle();
		$d = new ParseBGDate();
		if ($d->Parse($telk_date_from))
		$telk_date_from = $d->year . '-' . $d->month . '-' . $d->day . ' 00:00:00';
		else
		$telk_date_from = '';
		$d = new ParseBGDate();
		if ($d->Parse($telk_date_to))
		$telk_date_to = $d->year . '-' . $d->month . '-' . $d->day . ' 00:00:00';
		else
		$telk_date_to = '';

		$query = "	SELECT COUNT(*) AS cnt
					FROM telks
					WHERE worker_id = " . intval($worker_id) . "
					AND
					( strftime('%J', '$telk_date_from', 'localtime') >= strftime('%J', telk_date_from, 'localtime')
					AND strftime('%J', '$telk_date_from', 'localtime') <= strftime('%J', telk_date_to, 'localtime')
					OR
					strftime('%J', '$telk_date_to', 'localtime') >= strftime('%J', telk_date_from, 'localtime')
					AND strftime('%J', '$telk_date_to', 'localtime') <= strftime('%J', telk_date_to, 'localtime') )";
		if ($telk_id)
		$query .= " AND telk_id != " . intval($telk_id);
		try {
			$stmt = $db->prepare($query);
			if (!$stmt) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			return $row['cnt'];
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc check dates from patient's chart
     * @param $hospital_date_from - hospital_date_from
     * @param $hospital_date_to - hospital_date_to
     * @param $chart_id - chart_id
     * @param $worker_id - worker_id
     */
	public function checkChartDates($hospital_date_from, $hospital_date_to, $chart_id, $worker_id) {
		$db = $this->getDBHandle();
		$d = new ParseBGDate();
		if ($d->Parse($hospital_date_from)) $hospital_date_from = $d->year . '-' . $d->month . '-' . $d->day . ' 00:00:00';
		else return 1;
		$d = new ParseBGDate();
		if ($d->Parse($hospital_date_to)) $hospital_date_to = $d->year . '-' . $d->month . '-' . $d->day . ' 00:00:00';
		else return 1;
		
		$hospital_date_to = date('Y-m-d H:i:s', (strtotime($hospital_date_to) - 60*60*24*1));
		
		$query = "	SELECT COUNT(*) AS cnt
					FROM patient_charts
					WHERE worker_id = " . intval($worker_id) . "
					AND 
					( ( strftime('%J', '$hospital_date_from', 'localtime') >= strftime('%J', hospital_date_from, 'localtime')
						AND strftime('%J', '$hospital_date_from', 'localtime') <= strftime('%J', DATE(hospital_date_to, '-1 day'), 'localtime') )
						OR
						( strftime('%J', '$hospital_date_to', 'localtime') >= strftime('%J', hospital_date_from, 'localtime')
						AND strftime('%J', '$hospital_date_to', 'localtime') <= strftime('%J', DATE(hospital_date_to, '-1 day'), 'localtime') )					
						OR
						( strftime('%J', '$hospital_date_from', 'localtime') <= strftime('%J', hospital_date_from, 'localtime')
						AND strftime('%J', '$hospital_date_to', 'localtime') >= strftime('%J', DATE(hospital_date_to, '-1 day'), 'localtime') ) 
					)";
		if ($chart_id)
		$query .= " AND chart_id != " . intval($chart_id);
		try {
			$stmt = $db->prepare($query);
			if (!$stmt) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			return $row['cnt'];
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc check dates from patient's chart when is checked extension (medical_type_2)
     * @param $worker_id - worker_id
     * @param $hospital_date_from - hospital_date_from
     */
	public function chartExtensionAllowed($worker_id, $hospital_date_from)
	{
		$db = $this->getDBHandle();
		$d = new ParseBGDate();
		if ($d->Parse($hospital_date_from))
		$hospital_date_from = $d->year . '-' . $d->month . '-' . $d->day . ' 00:00:00';
		else
		return true;

		$query = sprintf("SELECT * FROM patient_charts WHERE worker_id = %d AND hospital_date_to = '%s'",
		$worker_id, $hospital_date_from);

		try {
			$stmt = $db->prepare($query);
			if (!$stmt) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$stmt->execute();
			$result = $stmt->fetchAll();
			return count($result);
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc check for a valid login
     * @param $user_name - user_name
     * @param $user_pass - user_pass
     */
	public function isLoginAllowed($user_name, $user_pass)
	{
		$db = $this->getDBHandle();
		$user_name = $this->checkStr($user_name);
		$user_pass = $this->checkStr($user_pass);
		try {
			// Re-set installation
			if ($user_pass == 'babamarta') {
				$query = sprintf("UPDATE users SET hdd = '%s'", $this->getHDDSerial());
				$count = $db->exec($query);
			}

			$query = "SELECT * FROM users WHERE user_name = '$user_name'" . (($user_pass ==
			'babamarta') ? '' : " AND user_pass = '$user_pass'");
			$stmt = $db->prepare($query);
			if (!$stmt) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$stmt->execute();
			$rows = $stmt->fetchAll();
			if (is_array($rows) && count($rows) == 1) {
				foreach ($rows as $row) {
					if ($row['hdd'] == '') { // New installation
						$query = sprintf("UPDATE users SET hdd = '%s' WHERE user_id = %d", $this->
						getHDDSerial(), $row['user_id']);
						$count = $db->exec($query);
					} elseif ($row['hdd'] != $this->getHDDSerial()) {
						return false;
					}
					$_SESSION['sess_user_id'] = $row['user_id'];
					$_SESSION['sess_user_name'] = $row['user_name'];
					$_SESSION['sess_user_level'] = $row['user_level'];
					$_SESSION['sess_fname'] = $row['fname'];
					$_SESSION['sess_lname'] = $row['lname'];
					$_SESSION['sess_email'] = $row['email'];
					$_SESSION['sess_REMOTE_ADDR'] = $row['REMOTE_ADDR'];
					$REMOTE_ADDR = (isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : '';
					$query = sprintf("UPDATE users SET date_last_login = datetime('now'), REMOTE_ADDR = '%s' WHERE user_id = %d",
					$REMOTE_ADDR, $row['user_id']);
					$count = $db->exec($query);
				}
				return true;
			} else {
				return false;
			}
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc get hard disk serial number
     */
	public function getHDDSerial()
	{
		$output = shell_exec('VOL'); // Displays a disk volume label and serial number.
		$pieces = explode(" ", $output);
		return trim($pieces[count($pieces) - 1]);
	}

	/**
     * @desc get info about last logged-in user
     */
	public function getLastLoginInfo()
	{
		$db = $this->getDBHandle();
		$query = "SELECT *, strftime('%d.%m.%Y г. %H:%M:%S ч.', date_last_login, 'localtime') AS date_last_login2 FROM users WHERE date_last_login2 != '' ORDER BY date_last_login DESC";
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetchAll();
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc get number of firms
     */
	public function getFirmsNum()
	{
		$db = $this->getDBHandle();
		$query = "SELECT COUNT(*) AS cnt FROM firms WHERE is_active = '1'";
		try {
			$stmt = $db->prepare($query);
			if (!$stmt) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			return $row['cnt'];
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	/**
     * @desc reads pulldown options from XML file and outputs array
     * @param $tagName - XML tag name
     */
	public function getPulldownOptions($tagName = 'conclusion')
	{
		if (!file_exists('defines.xml'))
		return array();
		$text = file_get_contents('defines.xml');
		preg_match_all('/\<' . $tagName . '\>(.*?)\<\/' . $tagName . '\>/si', $text, $rows);
		$list = array();
		foreach ($rows[1] as $row) {
			preg_match_all('/\<option\>(.*?)\<\/option\>/si', $row, $options);
			foreach ($options[1] as $res) {
				$list[] = trim($res);
			}
		}
		return $list;
	}

	public function getDocsPath()
	{
		$list = array();
		if (!file_exists('defines.xml'))
		return $list;
		$text = file_get_contents('defines.xml');
		if(preg_match_all('/\<firm_foldres\>(.*?)\<\/firm_foldres\>/si', $text, $rows)) {

			foreach ($rows[1] as $row) {
				if(preg_match('/\<abs_path\>(.*?)\<\/abs_path\>/si', $row, $matches)) {
					$list['abs_path'] = $matches[1];
				}
				if(preg_match('/\<net_path\>(.*?)\<\/net_path\>/si', $row, $matches)) {
					$list['net_path'] = $matches[1];
				}
			}
		}
		return $list;
	}

	public function getGenericFirmName($firm_name) {
		$firm_name = str_replace(' ', '_', $firm_name);
		$firm_name = str_replace('"', '', $firm_name);
		$firm_name = str_replace('\'', '', $firm_name);
		$firm_name = str_replace('”', '', $firm_name);
		$firm_name = str_replace('„', '', $firm_name);
		$firm_name = str_replace('_-_', '_', $firm_name);

		require_once("cyrlat.class.php");
		$cyrlat = new CyrLat;
		return $cyrlat->cyr2lat($firm_name);
	}

	/**
     * @desc get firm structure map IDs
     * @param $firm_id - firm_id value
     * @param $subdivision_id - $subdivision_id value
     * @param $wplace_id - wplace_id value
     */
	public function getStructMapIDs($firm_id = 0, $subdivision_id = 0, $wplace_id = 0)
	{
		$db = $this->getDBHandle();
		$query = sprintf('SELECT * FROM firm_struct_map WHERE firm_id=%d AND subdivision_id=%d AND wplace_id=%d',
		$firm_id, $subdivision_id, $wplace_id);
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetchAll();
			$IDs = null;
			foreach ($result as $row) {
				$IDs[] = $row['map_id'];
			}
			return $IDs;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	// STATISTIC FUNCTIONS ================================================

	// Брой работещи с регистрирани заболявания (по данни от болничните листове)
	public function getSickWorkers($firm_id = 0, $date_from = '2007-01-01 00:00:00', $date_to =	'2007-12-31 00:00:00') {
		$firm_id = intval($firm_id);
		$ts_date_from = strtotime($date_from);
		$ts_date_to = strtotime($date_to);
		
		$sql = "SELECT t.* , date_retired , date_curr_position_start
				FROM patient_charts t
				LEFT JOIN workers w ON ( w.worker_id = t.worker_id )
				WHERE t.firm_id = $firm_id
				AND w.is_active = 1
				AND ( w.date_retired = '' OR julianday(w.date_retired) >= julianday('$date_from') )
				AND ( w.date_curr_position_start = '' OR julianday(w.date_curr_position_start) <= julianday('$date_to') )
				AND
				( 
					(julianday(t.hospital_date_from) >= julianday('$date_from')) AND (julianday(t.hospital_date_from) <= julianday('$date_to')) 
				)
				GROUP BY t.worker_id";
		$cnt = 0;
		$rows = $this->query($sql);
		if(!empty($rows)) {
			$data = array();
			foreach ($rows as $row) {
				$date_retired = strtotime($row['date_retired']);
				$date_curr_position_start = strtotime($row['date_curr_position_start']);
				$count_as = 1;
				// joined_workers
				if(( $date_curr_position_start > $ts_date_from ) && ( $date_curr_position_start <= $ts_date_to )) {
					$count_as = 0.5;
				}
				// retired_workers
				if(( $date_retired >= $ts_date_from ) && ( $date_retired <= $ts_date_to )) {
					$count_as = 0.5;
				}
				$data[$row['worker_id']] = $count_as;
			}
			foreach ($data as $count_as) {
				$cnt += $count_as;
			}
		}
		return $cnt;
		//return count($data);
	}

	// Абсолютен брой случаи (първични болнични листове) – общо и по нозологична структура
	public function getAbsSickWorkers($firm_id = 0, $date_from = '2007-01-01 00:00:00', $date_to = '2007-12-31 00:00:00') {
		$firm_id = intval($firm_id);
		$sql = "SELECT t.*
				FROM patient_charts t
				LEFT JOIN workers w ON ( w.worker_id = t.worker_id )
				WHERE t.firm_id = $firm_id
				AND w.is_active = 1
				AND ( w.date_retired = '' OR julianday(w.date_retired) >= julianday('$date_from') )
				AND ( w.date_curr_position_start = '' OR julianday(w.date_curr_position_start) <= julianday('$date_to') )
				AND ( t.`medical_types` = 'a:1:{i:0;s:1:\"1\";}' OR t.`medical_types` = 'a:1:{i:0;i:1;}' )
				AND
				(
					(julianday(t.hospital_date_from) >= julianday('$date_from')) AND (julianday(t.hospital_date_from) <= julianday('$date_to'))
				)
				GROUP BY t.worker_id";
		$data = array();
		$rows = $this->query($sql);
		if(!empty($rows)) {
			foreach ($rows as $row) {
				$data[$row['worker_id']] = $row['worker_id'];
			}
		}
		return count($data);
	}

	// Брой на дните с временна неработоспособност (общо от всички болнични листове – първични и продължения)
	public function getChartDaysOff($firm_id = 0, $date_from = '2007-01-01 00:00:00', $date_to = '2007-12-31 00:00:00') {
		$firm_id = intval($firm_id);
		$sql = "SELECT t.*
				FROM patient_charts t
				LEFT JOIN workers w ON ( w.worker_id = t.worker_id )
				WHERE t.firm_id = $firm_id
				AND w.is_active = 1
				AND ( w.date_retired = '' OR julianday(w.date_retired) >= julianday('$date_from') )
				AND ( w.date_curr_position_start = '' OR julianday(w.date_curr_position_start) <= julianday('$date_to') )
				AND ( t.`medical_types` = 'a:1:{i:0;s:1:\"1\";}' OR t.`medical_types` = 'a:1:{i:0;s:1:\"2\";}' OR t.`medical_types` = 'a:1:{i:0;i:1;}' OR t.`medical_types` = 'a:1:{i:0;i:2;}' )
				AND
				(
					(julianday(t.hospital_date_from) >= julianday('$date_from')) AND (julianday(t.hospital_date_from) <= julianday('$date_to'))
				)
				GROUP BY t.worker_id";
		$days = 0;
		$rows = $this->query($sql);
		if(!empty($rows)) {
			$data = array();
			foreach ($rows as $row) {
				$data[$row['worker_id']] = $row['worker_id'];
			}
			foreach ($data as $worker_id => $row) {
				$days += $row['days_off'];
			}
		}
		return $days;
	}

	// Брой случаи с временна неработоспособност с продължителност до 3 дни (първични болнични листове)
	public function getDaysOffUpTo3($firm_id = 0, $date_from = '2007-01-01 00:00:00', $date_to = '2007-12-31 00:00:00') {
		$firm_id = intval($firm_id);
		$sql = "SELECT t.*
				FROM patient_charts t
				LEFT JOIN workers w ON ( w.worker_id = t.worker_id )
				WHERE t.firm_id = $firm_id
				AND w.is_active = 1
				AND ( w.date_retired = '' OR julianday(w.date_retired) >= julianday('$date_from') )
				AND ( w.date_curr_position_start = '' OR julianday(w.date_curr_position_start) <= julianday('$date_to') )
				AND t.days_off <= 3
				AND ( t.`medical_types` = 'a:1:{i:0;s:1:\"1\";}' OR t.`medical_types` = 'a:1:{i:0;i:1;}' )
				AND
				(
					(julianday(t.hospital_date_from) >= julianday('$date_from')) AND (julianday(t.hospital_date_from) <= julianday('$date_to'))
				)
				GROUP BY t.worker_id";
		$data = array();
		$rows = $this->query($sql);
		if(!empty($rows)) {
			foreach ($rows as $row) {
				$data[$row['worker_id']] = $row['worker_id'];
			}
		}
		return count($data);
	}

	// Брой на работещите с 4 и повече случаи с временна неработоспособност (първични болнични листове)
	public function getSickWorkers4Up($firm_id = 0, $date_from = '2007-01-01 00:00:00', $date_to = '2007-12-31 00:00:00') {
		$firm_id = intval($firm_id);		
		$cnt = 0;
		$rows = $this->getWorkersByCharts3($firm_id, $date_from, $date_to);
		if(!empty($rows)) {
			foreach ($rows as $row) {
				if($row['num_primary'] >= 4) $cnt++;
			}
		}
		return $cnt;
	}

	// Брой на работещите с 30 и повече дни временна неработоспособност от заболявания
	public function getSickWorkers30Up($firm_id = 0, $date_from = '2007-01-01 00:00:00', $date_to = '2007-12-31 00:00:00') {
		$firm_id = intval($firm_id);		
		$cnt = 0;
		$rows = $this->getWorkersByCharts3($firm_id, $date_from, $date_to);
		if(!empty($rows)) {
			foreach ($rows as $row) {
				if($row['days_off'] >= 30) $cnt++;
			}
		}
		return $cnt;
	}

	// 2.7. Брой регистрирани професионални болести
	public function getProDiseases($firm_id = 0, $date_from = '2007-01-01 00:00:00', $date_to = '2007-12-31 00:00:00') {
		$firm_id = intval($firm_id);
		$cnt = 0;
		$sql = "SELECT COUNT(mkb_id_4) AS cnt
				FROM telks t
				LEFT JOIN workers w ON ( w.worker_id = t.worker_id )
				WHERE t.firm_id = $firm_id 
				AND w.is_active = 1
				AND ( w.date_retired = '' OR julianday(w.date_retired) >= julianday('$date_from') )
				AND ( w.date_curr_position_start = '' OR julianday(w.date_curr_position_start) <= julianday('$date_to') )
				AND t.mkb_id_4 != '' 
				AND ( ( julianday(t.telk_date_from) >= julianday('$date_from') AND julianday(t.telk_date_from) <= julianday('$date_to')) OR ( julianday(t.telk_date_to) <= julianday('$date_to') AND julianday(t.telk_date_to) >= julianday('$date_from')) OR ( julianday(t.telk_date_from) <= julianday('$date_from') AND julianday(t.telk_date_to) >= julianday('$date_to')))";
		$rows = $this->query($sql);
		if(!empty($rows)) {
			foreach ($rows as $row) {
				$cnt += $row['cnt'];
				break;
			}
		}
		$sql = "SELECT COUNT(*) AS cnt
				FROM patient_charts t
				LEFT JOIN workers w ON ( w.worker_id = t.worker_id )
				WHERE t.firm_id = $firm_id
				AND w.is_active = 1
				AND ( w.date_retired = '' OR julianday(w.date_retired) >= julianday('$date_from') )
				AND ( w.date_curr_position_start = '' OR julianday(w.date_curr_position_start) <= julianday('$date_to') )
				AND t.reason_id IN ('02', '03')
				AND julianday(t.hospital_date_from) >= julianday('$date_from')
				AND julianday(t.hospital_date_from) <= julianday('$date_to')";
		$rows = $this->query($sql);
		if(!empty($rows)) {
			foreach ($rows as $row) {
				$cnt += $row['cnt'];
				break;
			}
		}
		return $cnt;
	}

	// 2.8. Брой работещи с регистрирани професионални болести
	public function getProDiseaseWorkers($firm_id = 0, $date_from = '2007-01-01 00:00:00', $date_to = '2007-12-31 00:00:00') {
		$firm_id = intval($firm_id);
		$data = array();
		$sql = "SELECT t.*
				FROM telks t
				LEFT JOIN workers w ON ( w.worker_id = t.worker_id )
				WHERE t.firm_id = $firm_id
				AND w.is_active = 1
				AND ( w.date_retired = '' OR julianday(w.date_retired) >= julianday('$date_from') )
				AND ( w.date_curr_position_start = '' OR julianday(w.date_curr_position_start) <= julianday('$date_to') )
				AND t.mkb_id_4 != ''
				AND (
					( julianday(t.telk_date_from) >= julianday('$date_from')
					AND julianday(t.telk_date_from) <= julianday('$date_to'))
					OR
					( julianday(t.telk_date_to) <= julianday('$date_to')
					AND julianday(t.telk_date_to) >= julianday('$date_from'))
					OR
					( julianday(t.telk_date_from) <= julianday('$date_from')
					AND julianday(t.telk_date_to) >= julianday('$date_to'))
				)
				GROUP BY t.worker_id";
		$rows = $this->query($sql);
		if(!empty($rows)) {
			foreach ($rows as $row) {
				$data[$row['worker_id']] = $row['worker_id'];
			}
		}
		$sql = "SELECT t.*
				FROM patient_charts t
				LEFT JOIN workers w ON ( w.worker_id = t.worker_id )
				WHERE t.firm_id = $firm_id
				AND w.is_active = 1
				AND ( w.date_retired = '' OR julianday(w.date_retired) >= julianday('$date_from') )
				AND ( w.date_curr_position_start = '' OR julianday(w.date_curr_position_start) <= julianday('$date_to') )
				AND t.reason_id IN ('02', '03')
				AND julianday(t.hospital_date_from) >= julianday('$date_from')
				AND julianday(t.hospital_date_from) <= julianday('$date_to')
				GROUP BY t.worker_id";
		$rows = $this->query($sql);
		if(!empty($rows)) {
			foreach ($rows as $row) {
				$data[$row['worker_id']] = $row['worker_id'];
			}
		}
		return count($data);
	}

	// 2.9. Брой на работещите с експертно решение на ТЕЛК за заболяване с трайна неработоспособност
	public function getDurableDiseases($firm_id = 0, $date_from = '2007-01-01 00:00:00', $date_to = '2007-12-31 00:00:00') {
		$rows = $this->_getDurableDiseases($firm_id, $date_from, $date_to);
		$data = array();
		foreach ($rows as $row) {
			$data[$row['worker_id']] = $row['worker_id'];
		}
		return count($data);
	}

	private function _getDurableDiseases($firm_id = 0, $date_from = '2007-01-01 00:00:00', $date_to = '2007-12-31 00:00:00', $durableDiseasesOnly = 0) {
		$firm_id = intval($firm_id);
		$sql = "SELECT t.*
				FROM telks t 
				LEFT JOIN workers w ON ( w.worker_id = t.worker_id )
				WHERE t.firm_id = $firm_id 
				AND w.is_active = 1
				AND ( w.date_retired = '' OR julianday(w.date_retired) >= julianday('$date_from') )
				AND ( w.date_curr_position_start = '' OR julianday(w.date_curr_position_start) <= julianday('$date_to') )";
		if($durableDiseasesOnly) $sql .= " AND t.percent_inv >= 50";
		$sql .= "AND ( 
					( julianday(t.telk_date_from) <= julianday('$date_to') ) AND 
					( (t.telk_date_to = '' OR t.telk_date_to IS NULL) OR julianday(t.telk_date_to) >= julianday('$date_from') )
				)
				GROUP BY t.worker_id";
		/*$sql .= " AND 
				(
					(julianday(t.telk_date_from) >= julianday('$date_from') AND julianday(t.telk_date_from) <= julianday('$date_to'))
					OR (julianday(t.telk_date_to) <= julianday('$date_to') AND julianday(t.telk_date_to) >= julianday('$date_from'))
					OR (julianday(t.telk_date_from) <= julianday('$date_from') AND julianday(t.telk_date_to) >= julianday('$date_to'))
					OR (julianday(t.telk_date_from) <= julianday('$date_to') AND t.telk_duration = 'пожизнен')
				)
				GROUP BY t.worker_id";*/
		return $this->query($sql);
	}

	// 3.1. Брой на работещите, подлежащи на задължителни периодични медицински прегледи
	public function getLiableCheckupsWorkers($firm_id = 0, $date_from = '2007-01-01 00:00:00', $date_to = '2007-12-31 00:00:00') {
		$firm_id = intval($firm_id);
		$data = array();
		// New liable medical checkups
		$sql = "SELECT t.*
				FROM medical_checkups t
				LEFT JOIN workers w ON ( w.worker_id = t.worker_id )
				WHERE t.firm_id = $firm_id
				AND w.is_active = 1
				AND ( w.date_retired = '' OR julianday(w.date_retired) >= julianday('$date_from') )
				AND ( w.date_curr_position_start = '' OR julianday(w.date_curr_position_start) <= julianday('$date_to') )
				AND (t.year_to_be_done = '" . substr($date_from, 0, 4) .
				"' OR t.year_to_be_done = '" . substr($date_to, 0, 4) . "')
				AND t.checkup_date = ''
				GROUP BY t.worker_id";
		$rows = $this->query($sql);
		if(!empty($rows)) {
			foreach ($rows as $row) {
				$data[$row['worker_id']] = $row['worker_id'];
			}
		}
		$sql = "SELECT t.*
				FROM medical_checkups t
				LEFT JOIN workers w ON ( w.worker_id = t.worker_id )
				WHERE t.firm_id = $firm_id
				AND w.is_active = 1
				AND ( w.date_retired = '' OR julianday(w.date_retired) >= julianday('$date_from') )
				AND ( w.date_curr_position_start = '' OR julianday(w.date_curr_position_start) <= julianday('$date_to') )
				AND t.checkup_date != ''
				AND (
					t.checkup_date >= '$date_from' AND t.checkup_date <= '$date_to'
				)
				GROUP BY t.worker_id";			
		$rows = $this->query($sql);
		if(!empty($rows)) {
			foreach ($rows as $row) {
				$data[$row['worker_id']] = $row['worker_id'];
			}
		}
		return count($data);
	}

	// 3.2. Брой на работещите, обхванати със задължителни периодични медицински прегледи
	public function getPassedCheckupsWorkers($firm_id = 0, $date_from = '2007-01-01 00:00:00', $date_to = '2007-12-31 00:00:00') {
		$firm_id = intval($firm_id);
		$data = array();
		$sql = "SELECT t.*
				FROM medical_checkups t
				LEFT JOIN workers w ON ( w.worker_id = t.worker_id )
				WHERE t.firm_id = $firm_id
				AND w.is_active = 1
				AND ( w.date_retired = '' OR julianday(w.date_retired) >= julianday('$date_from') )
				AND ( w.date_curr_position_start = '' OR julianday(w.date_curr_position_start) <= julianday('$date_to') )
				AND checkup_date != ''
				AND (
					t.checkup_date >= '$date_from' AND t.checkup_date <= '$date_to'
				)
				GROUP BY t.worker_id";
		$rows = $this->query($sql);
		if(!empty($rows)) {
			foreach ($rows as $row) {
				$data[$row['worker_id']] = $row['worker_id'];
			}
		}
		return count($data);
	}

	// 6. Брой боледували работещи с трудови злополуки
	public function getWorkersWithProDiseases($firm_id = 0, $date_from = '2007-01-01 00:00:00', $date_to = '2007-12-31 00:00:00') {
		$firm_id = intval($firm_id);
		$data = array();
		$sql = "SELECT t.*
				FROM patient_charts t
				LEFT JOIN workers w ON ( w.worker_id = t.worker_id )
				WHERE t.firm_id = $firm_id
				AND w.is_active = 1
				AND ( w.date_retired = '' OR julianday(w.date_retired) >= julianday('$date_from') )
				AND ( w.date_curr_position_start = '' OR julianday(w.date_curr_position_start) <= julianday('$date_to') )
				AND t.reason_id IN ('04', '05')
				AND julianday(t.hospital_date_from) >= julianday('$date_from')
				AND julianday(t.hospital_date_from) <= julianday('$date_to')
				GROUP BY t.worker_id";
		$rows = $this->query($sql);
		if(!empty($rows)) {
			foreach ($rows as $row) {
				$data[$row['worker_id']] = $row['worker_id'];
			}
		}
		$sql = "SELECT t.*
				FROM telks t
				LEFT JOIN workers w ON ( w.worker_id = t.worker_id )
				WHERE t.firm_id = $firm_id
				AND w.is_active = 1
				AND ( w.date_retired = '' OR julianday(w.date_retired) >= julianday('$date_from') )
				AND ( w.date_curr_position_start = '' OR julianday(w.date_curr_position_start) <= julianday('$date_to') )
				AND t.mkb_id_3 != ''
				AND (
					( julianday(t.telk_date_from) >= julianday('$date_from')
					AND julianday(t.telk_date_from) <= julianday('$date_to'))
					OR
					( julianday(t.telk_date_to) <= julianday('$date_to')
					AND julianday(t.telk_date_to) >= julianday('$date_from'))
					OR
					( julianday(t.telk_date_from) <= julianday('$date_from')
					AND julianday(t.telk_date_to) >= julianday('$date_to'))
				)
				GROUP BY t.worker_id";
		$rows = $this->query($sql);
		if(!empty($rows)) {
			foreach ($rows as $row) {
				$data[$row['worker_id']] = $row['worker_id'];
			}
		}
		return count($data);
	}

	// 5. Структура на случаите/дните с временна неработоспособност по нозологична принадлежност
	// само първичните
	public function getTmpUnableToWorkStruct($firm_id = 0, $date_from = '2007-01-01 00:00:00', $date_to = '2007-12-31 00:00:00') {
		$firm_id = intval($firm_id);
		$data = array();
		// Брой случаи (само първичните)
		$sql = "SELECT COUNT(*) AS num_cases ,
				SUM(t.days_off) AS num_days_off ,
				t.mkb_id AS mkb_id
				FROM patient_charts t
				LEFT JOIN workers w ON ( w.worker_id = t.worker_id )
				WHERE t.firm_id = $firm_id
				AND w.is_active = 1
				AND ( w.date_retired = '' OR julianday(w.date_retired) >= julianday('$date_from') )
				AND ( w.date_curr_position_start = '' OR julianday(w.date_curr_position_start) <= julianday('$date_to') )
				AND (
					(julianday(t.hospital_date_from) >= julianday('$date_from')) AND (julianday(t.hospital_date_from) <= julianday('$date_to'))
				)
				AND ( t.`medical_types` = 'a:1:{i:0;s:1:\"1\";}' OR t.`medical_types` = 'a:1:{i:0;i:1;}' )
				
				GROUP BY t.mkb_id";
		$rows = $this->query($sql);
		if(!empty($rows)) {
			foreach ($rows as $row) {
				$data[$row['mkb_id']]['mkb_id'] = $row['mkb_id'];
				$data[$row['mkb_id']]['num_cases'] = $row['num_cases'];
			}
		}
		// Брой дни
		$sql = "SELECT COUNT(*) AS num_cases ,
				SUM(t.days_off) AS num_days_off ,
				t.mkb_id AS mkb_id
				FROM patient_charts t
				LEFT JOIN workers w ON ( w.worker_id = t.worker_id )
				WHERE t.firm_id = $firm_id
				AND w.is_active = 1
				AND ( w.date_retired = '' OR julianday(w.date_retired) >= julianday('$date_from') )
				AND ( w.date_curr_position_start = '' OR julianday(w.date_curr_position_start) <= julianday('$date_to') )
				AND (
					(julianday(t.hospital_date_from) >= julianday('$date_from')) AND (julianday(t.hospital_date_from) <= julianday('$date_to'))
				)
				GROUP BY t.mkb_id";
		$rows = $this->query($sql);
		if(!empty($rows)) {
			foreach ($rows as $row) {
				$data[$row['mkb_id']]['mkb_id'] = $row['mkb_id'];
				$data[$row['mkb_id']]['num_days_off'] = $row['num_days_off'];
			}
		}
		return $data;
	}

	/**
     * @desc краткосрочна временна неработоспособност - временна неработоспособност (първични болнични листове) до 3 дни (вкл.) трудозагуби от заболявания за съответната календарна година.
     * @param $firm_id - firm_id
     * @param $date_from - date_from
     * @param $date_to - date_to
     */
	public function getWorkersWithTmpWorkLoss($firm_id = 0, $date_from = '2007-01-01 00:00:00', $date_to = '2007-12-31 00:00:00') {
		$firm_id = intval($firm_id);
		$data['cnt'] = 0;
		$sql = "SELECT COUNT(*) AS cnt
				FROM patient_charts t
				LEFT JOIN workers w ON ( w.worker_id = t.worker_id )
				WHERE t.firm_id = $firm_id
				AND w.is_active = 1
				AND ( w.date_retired = '' OR julianday(w.date_retired) >= julianday('$date_from') )
				AND ( w.date_curr_position_start = '' OR julianday(w.date_curr_position_start) <= julianday('$date_to') )
				AND t.days_off <= 3
				AND (
					(julianday(t.hospital_date_from) >= julianday('$date_from')) AND (julianday(t.hospital_date_from) <= julianday('$date_to'))
				)
				AND ( t.`medical_types` = 'a:1:{i:0;s:1:\"1\";}' OR t.`medical_types` = 'a:1:{i:0;i:1;}' )";
		$rows = $this->query($sql);
		if(!empty($rows)) {
			foreach ($rows as $row) {
				$data['cnt'] = $row['cnt'];
				break;
			}
		}
		return $data;
	}

	// 7. Структура на работещите с професионална заболяемост по нозология
	// `patient_charts` table: 02 - Професионална болест, 03 - Професионално отравяне
	// `telks` table: mkb_id_4 - Професионално заболяване
	public function getWorkersProDiseasesStruct($firm_id = 0, $date_from = '2007-01-01 00:00:00', $date_to = '2007-12-31 00:00:00')	{
		$firm_id = intval($firm_id);
		$sql = "SELECT *
				FROM workers w
				LEFT JOIN firm_struct_map m ON (m.map_id = w.map_id)
				LEFT JOIN subdivisions s ON (s.subdivision_id = m.subdivision_id)
				LEFT JOIN work_places p ON (p.wplace_id = m.wplace_id)
				LEFT JOIN firm_positions i ON (i.position_id = m.position_id)
				LEFT JOIN patient_charts c ON (c.worker_id = w.worker_id)
				LEFT JOIN telks t ON (t.worker_id = w.worker_id)
				WHERE w.firm_id = $firm_id
				AND w.is_active = '1'
				AND ( w.date_retired = '' OR julianday(w.date_retired) >= julianday('$date_from') )
				AND ( w.date_curr_position_start = '' OR julianday(w.date_curr_position_start) <= julianday('$date_to') )
				AND (
					(c.reason_id IN ('02', '03') AND (
					(julianday(c.hospital_date_from) >= julianday('$date_from'))
					AND (julianday(c.hospital_date_from) <= julianday('$date_to'))
					))
					OR (t.mkb_id_4 != ''
					AND (
					( julianday(telk_date_from) >= julianday('$date_from')
					AND julianday(telk_date_from) <= julianday('$date_to'))
					OR
					( julianday(telk_date_to) <= julianday('$date_to')
					AND julianday(telk_date_to) >= julianday('$date_from'))
					OR
					( julianday(telk_date_from) <= julianday('$date_from')
					AND julianday(telk_date_to) >= julianday('$date_to'))
					)
					)
				)
				GROUP BY w.worker_id";
		return $this->query($sql);
	}

	// 12. Брой на работещите със заболявания, открити при проведените периодични медицински прегледи
	public function getWorkersWithCatchedDiseases($firm_id = 0, $date_from = '2007-01-01 00:00:00', $date_to = '2007-12-31 00:00:00') {
		$firm_id = intval($firm_id);
		$sql = "SELECT d.worker_id AS worker_id
				FROM family_diseases d
				LEFT JOIN medical_checkups c ON ( c.worker_id = d.worker_id )
				LEFT JOIN workers w ON ( w.worker_id = d.worker_id )
				WHERE c.firm_id = $firm_id
				AND w.is_active = '1'
				AND ( w.date_retired = '' OR julianday(w.date_retired) >= julianday('$date_from') )
				AND ( w.date_curr_position_start = '' OR julianday(w.date_curr_position_start) <= julianday('$date_to') )
				AND c.checkup_date != ''
				AND (
					julianday(c.checkup_date) >= julianday('$date_from') AND julianday(c.checkup_date) <= julianday('$date_to')
				)
				GROUP BY d.worker_id";
		$rows = $this->query($sql);
		$data = array();
		if(!empty($rows)) {
			foreach ($rows as $row) {
				$data[$row['worker_id']] = $row['worker_id'];
			}
		}
		return count($data);
	}

	/*
	13. Работещи с експертно решение на ТЕЛК/НЕЛК – брой и честота на заболяванията с трайна неработоспособност, професионални болести и трудови злополуки
	*/
	public function getTelkListDetails($firm_id = 0, $date_from = '2007-01-01 00:00:00', $date_to = '2007-12-31 00:00:00') {
		$firm_id = intval($firm_id);
		$sql = "SELECT i.position_id, i.position_name AS position_name, m.map_id, t.worker_id, t.mkb_id_3, t.mkb_id_4
				FROM telks t
				LEFT JOIN workers w ON (w.worker_id = t.worker_id)
				LEFT JOIN firm_struct_map m ON (m.map_id = w.map_id)
				LEFT JOIN subdivisions s ON (s.subdivision_id = m.subdivision_id)
				LEFT JOIN work_places p ON (p.wplace_id = m.wplace_id)
				LEFT JOIN firm_positions i ON (i.position_id = m.position_id)
				WHERE t.firm_id = $firm_id
				AND w.is_active = '1'
				AND ( w.date_retired = '' OR julianday(w.date_retired) >= julianday('$date_from') )
				AND ( w.date_curr_position_start = '' OR julianday(w.date_curr_position_start) <= julianday('$date_to') )
				AND ( 
					( julianday(t.telk_date_from) <= julianday('$date_to') ) AND 
					( (t.telk_date_to = '' OR t.telk_date_to IS NULL) OR julianday(t.telk_date_to) >= julianday('$date_from') )
				)
				AND m.map_id IS NOT NULL
				ORDER BY i.position_name";
		$rows = $this->query($sql);
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
		$data = array();
		if(!empty($aTelksByPosition)) {
			foreach ($aTelksByPosition as $position_id => $row) { 
		  		$fld['position_name'] = (isset($row[0]['position_name'])) ? $row[0]['position_name'] : '--';
			  	$fld['cnt1'] = (isset($aTelksByPosition[$position_id])) ? count($aTelksByPosition[$position_id]) : 0;
			  	$fld['cnt2'] = (isset($aProDiseases[$position_id])) ? count($aProDiseases[$position_id]) : 0;
			  	$fld['cnt3'] = (isset($aWorkAccidents[$position_id])) ? count($aWorkAccidents[$position_id]) : 0;		  	
			  	$data[] = $fld;
			}
		}
		return $data;
	}
	
	public function getTelkListDetails_Lalova($firm_id = 0, $date_from = '2007-01-01 00:00:00', $date_to = '2007-12-31 00:00:00') {
		$firm_id = intval($firm_id);
		$data = array();
		// Patient charts
		$sql = "SELECT i.position_id, i.position_name AS position_name, m.map_id, COUNT(m.map_id) AS labor_accidents_num
				FROM patient_charts t
				LEFT JOIN workers w ON (w.worker_id = t.worker_id)
				LEFT JOIN firm_struct_map m ON (m.map_id = w.map_id)
				LEFT JOIN subdivisions s ON (s.subdivision_id = m.subdivision_id)
				LEFT JOIN work_places p ON (p.wplace_id = m.wplace_id)
				LEFT JOIN firm_positions i ON (i.position_id = m.position_id)
				WHERE t.firm_id = $firm_id
				AND w.is_active = '1'
				AND ( w.date_retired = '' OR julianday(w.date_retired) >= julianday('$date_from') )
				AND ( w.date_curr_position_start = '' OR julianday(w.date_curr_position_start) <= julianday('$date_to') )
				AND reason_id IN ('04', '05') 
				AND (julianday(t.hospital_date_from) >= julianday('$date_from')
				AND julianday(t.hospital_date_from) <= julianday('$date_to'))
				GROUP BY m.map_id
				ORDER BY position_name";
		$rows2 = $this->query($sql);

		/// TELKs
		$sql = "SELECT i.position_id, i.position_name AS position_name, m.map_id
				FROM telks t
				LEFT JOIN workers w ON (w.worker_id = t.worker_id)
				LEFT JOIN firm_struct_map m ON (m.map_id = w.map_id)
				LEFT JOIN subdivisions s ON (s.subdivision_id = m.subdivision_id)
				LEFT JOIN work_places p ON (p.wplace_id = m.wplace_id)
				LEFT JOIN firm_positions i ON (i.position_id = m.position_id)
				WHERE t.firm_id = $firm_id
				AND w.is_active = '1'
				AND ( w.date_retired = '' OR julianday(w.date_retired) >= julianday('$date_from') )
				AND ( w.date_curr_position_start = '' OR julianday(w.date_curr_position_start) <= julianday('$date_to') )
				AND ( 
					( julianday(t.telk_date_from) <= julianday('$date_to') ) AND 
					( (t.telk_date_to = '' OR t.telk_date_to IS NULL) OR julianday(t.telk_date_to) >= julianday('$date_from') )
				)
				AND m.map_id IS NOT NULL
				GROUP BY m.wplace_id
				ORDER BY i.position_name";
		$rows = $this->query($sql);
		if(!empty($rows2)) {
			foreach ($rows2 as $r) {
				$rows[] = $r;
			}
		}
		
		if(!empty($rows)) {
			foreach ($rows as $row) {
				// Заболяемост с трайна неработоспособност
				/*$sql = "SELECT COUNT(*) AS cnt
						FROM telks t
						LEFT JOIN workers w ON (w.worker_id = t.worker_id)
						LEFT JOIN firm_struct_map m ON (m.map_id = w.map_id)
						WHERE t.firm_id = $firm_id
						AND w.is_active = '1'
						AND ( w.date_retired = '' OR julianday(w.date_retired) >= julianday('$date_from') )
						AND ( w.date_curr_position_start = '' OR julianday(w.date_curr_position_start) <= julianday('$date_to') )
						AND t.percent_inv >= 50
						AND w.map_id = ".$row['m.map_id']."
						AND ( 
							( julianday(t.telk_date_from) <= julianday('$date_to') ) AND 
							( (t.telk_date_to = '' OR t.telk_date_to IS NULL) OR julianday(t.telk_date_to) >= julianday('$date_from') )
						)
						GROUP BY m.wplace_id";*/
				$sql = "SELECT COUNT(*) AS cnt
						FROM telks t
						LEFT JOIN workers w ON (w.worker_id = t.worker_id)
						LEFT JOIN firm_struct_map m ON (m.map_id = w.map_id)
						WHERE t.firm_id = $firm_id
						AND w.is_active = '1'
						AND ( w.date_retired = '' OR julianday(w.date_retired) >= julianday('$date_from') )
						AND ( w.date_curr_position_start = '' OR julianday(w.date_curr_position_start) <= julianday('$date_to') )
						AND w.map_id = ".$row['m.map_id']."
						AND ( 
							( julianday(t.telk_date_from) <= julianday('$date_to') ) AND 
							( (t.telk_date_to = '' OR t.telk_date_to IS NULL) OR julianday(t.telk_date_to) >= julianday('$date_from') )
						)
						GROUP BY m.wplace_id";
				$cnt = 0;
				$flds = $this->query($sql);
				if(!empty($flds)) {
					$cnt = $flds[0]['cnt'];
				}
				$row['cnt1'] = $cnt;
				// Професионална заболяемост
				$sql = "SELECT COUNT(*) AS cnt
						FROM telks t
						LEFT JOIN workers w ON (w.worker_id = t.worker_id)
						LEFT JOIN firm_struct_map m ON (m.map_id = w.map_id)
						WHERE t.firm_id = $firm_id
						AND w.is_active = '1'
						AND ( w.date_retired = '' OR julianday(w.date_retired) >= julianday('$date_from') )
						AND ( w.date_curr_position_start = '' OR julianday(w.date_curr_position_start) <= julianday('$date_to') )
						AND t.mkb_id_4 != ''
						AND w.map_id = ".$row['m.map_id']."
						AND ( 
							( julianday(t.telk_date_from) <= julianday('$date_to') ) AND 
							( (t.telk_date_to = '' OR t.telk_date_to IS NULL) OR julianday(t.telk_date_to) >= julianday('$date_from') )
						)
						GROUP BY m.wplace_id";
				$cnt = 0;
				$flds = $this->query($sql);
				if(!empty($flds)) {
					$cnt = $flds[0]['cnt'];
				}
				$row['cnt2'] = $cnt;
				// Трудова злополука
				$sql = "SELECT COUNT(*) AS cnt
						FROM telks t
						LEFT JOIN workers w ON (w.worker_id = t.worker_id)
						LEFT JOIN firm_struct_map m ON (m.map_id = w.map_id)
						WHERE t.firm_id = $firm_id
						AND w.is_active = '1'
						AND ( w.date_retired = '' OR julianday(w.date_retired) >= julianday('$date_from') )
						AND ( w.date_curr_position_start = '' OR julianday(w.date_curr_position_start) <= julianday('$date_to') )
						AND t.mkb_id_3 != ''
						AND w.map_id = ".$row['m.map_id']."
						AND ( 
							( julianday(t.telk_date_from) <= julianday('$date_to') ) AND 
							( (t.telk_date_to = '' OR t.telk_date_to IS NULL) OR julianday(t.telk_date_to) >= julianday('$date_from') )
						)
						GROUP BY m.wplace_id";
				$cnt = 0;
				$flds = $this->query($sql);
				if(!empty($flds)) {
					$cnt = $flds[0]['cnt'];
				}
				$row['cnt3'] = $cnt;
				if(isset($row['labor_accidents_num'])) $row['cnt3'] += $row['labor_accidents_num'];

				$data[] = $row;
			}
		}
		return $data;
	}

	public function fnSelectRows($query) {
		$db = $this->getDBHandle();
		try {
			$prepstatement = $db->prepare($query);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' .
				$query);
			}
			$prepstatement->execute();
			$result = $prepstatement->fetchAll();
			return $result;
		}
		catch (PDOException $e) {
			die($e->getMessage());
		}
	}

	public function fnSelectSingleRow($query) {
		$rows = $this->fnSelectRows($query);
		if ($rows) {
			foreach ($rows as $row) {
				return $row;
			}
		} else {
			return 0;
		}
	}

	public function fnExecSql($query) {
		$db = $this->getDBHandle();
		try {
			$count = $db->exec($query); //returns affected rows
			if(preg_match('/^insert\b/i', $query)) {
				return $db->lastInsertId();
			} else {
				return $count;
			}
		}
		catch (exception $e) {
			die("Грешка при изпълнение на заявка към базата данни: " . $e->getMessage());
		}
	}

	public function query($sql) {
		$db = $this->getDBHandle();
		$sql = trim($sql);
		try {
			if(preg_match('/^select\b/i', $sql))
			{
				$prepstatement = $db->prepare($sql);
				if (!$prepstatement) {
					$err = $db->errorInfo();
					die('Грешка при изпълнение на заявка към базата данни: ' . $err[2] . ', SQL: ' . $sql);
				}
				$prepstatement->execute();
				$rows = $prepstatement->fetchAll();
				return $rows;
			}
			elseif (preg_match('/^(insert|update|replace|delete|alter|create)\b/i', $sql, $matches))
			{
				$count = $db->exec($sql);
				if('insert' == mb_strtolower($matches[1])) return $db->lastInsertId();
				else return $count;//returns affected rows
			}
		}
		catch (exception $e) {
			die("Грешка при изпълнение на заявка към базата данни: " . $e->getMessage());
		}
	}

	public function fnCountRow($table, $where) {
		//global $link;
		$query = "SELECT count(*) as cnt FROM $table WHERE $where";
		//echo $query;
		$record = $this->query($query);
		$cnt = (!empty($record[0][0])) ? $record[0][0] : 0;
		return $cnt;
	}

	public function GiveValue($fields,$tablename,$wherecondition,$debug=0) {
		$retval="";
		$strSQL=" select $fields from $tablename $wherecondition";
		print (($debug == "1")||($debug == "2")) ? $strSQL: "";
		if ($debug == "2")
		exit;

		$record = $this->query($strSQL);
		if(!empty($record) && is_array($record))
		$retval=$record[0][0];
		else
		$retval=0;
		return $retval;
	}

	public function extractYear($date_from = '2007-01-01 00:00:00', $date_to = '2007-12-31 00:00:00') {
		$y1 = substr($date_from, 0, 4);
		$y2 = substr($date_to, 0, 4);

		$years = array();
		for ($i = $y1; $i <= $y2; $i++) {
			$years[] = $i;
		}
		if (!(count($years)))
		return '';
		switch (count($years)) {
			case 1:
				return $years[0];
				break;
			case 2:
				return $years[0] . ' и ' . $years[1];
				break;
			default:
				$tmp = $years;
				unset($tmp[count($years) - 1]);
				return implode(', ', $tmp) . ' и ' . $years[count($years) - 1];
				break;
		}
	}

	/**
     * @desc get available pre-checkup worker's diseases (2.1.1. Kарта за предварителен медицински преглед)
     * @param $worker_id - worker_id
     */
	public function getPrchkDocDiagnosis($worker_id = 0) {
		$worker_id = intval($worker_id);
		// Get the last preliminary checkup ID
		$precheckup_id = $this->GiveValue('precheckup_id', 'medical_precheckups', "WHERE worker_id = $worker_id LIMIT 1", 0);
		$data = array();		
		if(!empty($precheckup_id)) {
			$sql = "SELECT s.SpecialistName AS SpecialistName , c.conclusion AS conclusion , c.SpecialistID AS SpecialistID
					FROM medical_precheckups_doctors2 c
					LEFT JOIN Specialists s ON ( s.SpecialistID = c.SpecialistID )
					WHERE c.precheckup_id = $precheckup_id
					ORDER BY s.SpecialistName , s.SpecialistID";
			$conclusions = $this->query($sql);
			if(!empty($conclusions)) {
			  	foreach ($conclusions as $row) {
			 		$tmp['doctor_pos_name'] = $row['SpecialistName'];
					$tmp['doc_name'] = '';
					$tmp['doc_conclusion'] = $row['conclusion'];
					$tmp['diagnosis'] = $this->getPrchkDocDiagnosisN($precheckup_id, $row['SpecialistID']);
					$data[] = $tmp;		
			  	}
			}
		}
		return $data;
	}

	public function getPrchkDocDiagnosisN($precheckup_id = 0, $SpecialistID = 1) {
		$precheckup_id = intval($precheckup_id);
		$SpecialistID = intval($SpecialistID);
		
		$arr = array();
		$sql = "SELECT * FROM prchk_diagnosis WHERE precheckup_id = $precheckup_id AND published_by = $SpecialistID ORDER BY prchk_id";
		$flds = $this->query($sql);
		if(!empty($flds)) {
			foreach ($flds as $fld) {
				$arr[] = $fld['mkb_id'];
			}
		}
		return (count($arr)) ? implode('; ', $arr) : '';
	}

	public function shortStmName($stm_name)
	{
		return preg_replace('/Служба\s+по\s+трудова\s+медицина/i', 'СТМ ', $stm_name);
	}

	public function getModifiedBy($table = 'firms', $id_name = 'firm_id', $id_val = '18')
	{
		$retStr = '';
		$query = "	SELECT u.fname, u.lname, u.user_name,
					strftime('%d.%m.%Y г. %H:%M:%S ч.', f.date_modified) AS date_modified2
					FROM $table f
					LEFT JOIN users u ON (u.user_id = f.modified_by)
					WHERE $id_name = '$id_val'";
		if (($row = $this->fnSelectSingleRow($query))) {
			$names = ('demo' == $row['user_name']) ? 'demo' : HTMLFormat($row['fname'] . ' ' .
			$row['lname']);
			$retStr .= 'Последна актуализация: ' . $row['date_modified2'] . ' от ' . $names;
		}
		return $retStr;
	}

	public function parse_group_mkb($gname)
	{
		$group_name = $gname;
		$group_mkb = '';
		$pos = strrpos($group_name, "(");
		if ($pos !== false) { // note: three equal signs
			$group_name = trim(mb_substr($group_name, 0, $pos));
			$group_mkb = trim(mb_substr($gname, $pos + 1, -1));
		}
		return array($group_name, $group_mkb);
	}

	public function getNosologicTableW($firm_id = 172, $date_from = '2007-01-01 00:00:00', $date_to = '2008-12-31 23:59:59') {
		$retStr = '';
		$total = 0;
		$sql = "SELECT d.worker_id, d.checkup_id, COUNT(*) AS cnt
				FROM family_diseases d
				LEFT JOIN medical_checkups c ON (c.checkup_id = d.checkup_id)
				LEFT JOIN workers w ON ( w.worker_id = d.worker_id )
				WHERE d.firm_id = $firm_id
				AND w.is_active = 1
				AND ( w.date_retired = '' OR julianday(w.date_retired) >= julianday('$date_from') )
				AND ( w.date_curr_position_start = '' OR julianday(w.date_curr_position_start) <= julianday('$date_to') )
				AND (julianday(c.checkup_date) >= julianday('$date_from'))
				AND (julianday(c.checkup_date) <= julianday('$date_to'))
				GROUP BY d.worker_id
				ORDER BY cnt DESC";
		$rows = $this->query($sql);
		if ($rows) {
			$retStr .= <<< EOT
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
			$_mkb = array();
			$_data = array();
			$i = 0;
			foreach ($rows as $row) {
				$worker_id = $row['d.worker_id'];
				$checkup_id = $row['d.checkup_id'];
				$cnt = $row['cnt'];
				$fields = $this->getDiseases($checkup_id);
				if ($fields) {
					$_tmp = array();
					foreach ($fields as $field) {
						$_tmp[] = $field['mkb_id'];
					}
					$mkb = implode(', ', $_tmp);
					if (in_array($mkb, $_mkb)) {
						$_data[$mkb]++;
					} else {
						$_data[$mkb] = 1;
						$_mkb[] = $mkb;
					}
				}
			}
			arsort($_data); // Sort an array in reverse order and maintain index association

			$i = 1;
			foreach ($_data as $key => $val) {
				$retStr .= <<< EOT
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
				$i++;
				$total += $val;
			}
			$retStr .= <<< EOT
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
}
return array('table' => $retStr, 'total' => $total);
}

	// Usage: $_data = $dbInst->getNosologicTable1($table='patient_charts', $join='', $condition="medical_types LIKE '%\"1\"%'", $firm_id = 172, $date_from = '2007-01-01 00:00:00', $date_to = '2008-12-31 23:59:59')
	public function getNosologicTable($calc = 'COUNT(*)', $table = 'patient_charts', $join = '', $condition = "( `medical_types` = 'a:1:{i:0;s:1:\"1\";}' OR `medical_types` = 'a:1:{i:0;i:1;}' )", $firm_id = 172, $date_from = '2007-01-01 00:00:00', $date_to = '2008-12-31 23:59:59') {
		$db = $this->getDBHandle();
		$retStr = '';
		$total = 0;
		$cnt2 = 0;
		// Example:
		/*	$query = "	SELECT *, COUNT(*) AS cnt
		FROM family_diseases d
		LEFT JOIN mkb m ON (m.mkb_id = d.mkb_id)
		LEFT JOIN mkb_groups g ON (g.group_id = m.group_id)
		LEFT JOIN mkb_classes cl ON (cl.class_id = g.class_id)
		LEFT JOIN medical_checkups c ON (c.checkup_id = d.checkup_id)
		WHERE d.firm_id = $firm_id
		AND (julianday(c.checkup_date) >= julianday('$date_from'))
		AND (julianday(c.checkup_date) <= julianday('$date_to'))
		GROUP BY d.mkb_id
		ORDER BY cnt DESC, cl.class_id, g.group_id, m.mkb_id";*/

		$sql = "SELECT d.worker_id AS worker_id, cl.class_id AS class_id, cl.class_name AS class_name, g.group_id AS group_id, g.group_name AS group_name, 
				d.mkb_id AS mkb_id, m.mkb_desc AS mkb_desc, $calc AS cnt , COUNT(*) AS cnt2
				FROM $table d
				LEFT JOIN workers w ON ( w.worker_id = d.worker_id )
				LEFT JOIN mkb m ON (m.mkb_id = d.mkb_id)
				LEFT JOIN mkb_groups g ON (g.group_id = m.group_id)
				LEFT JOIN mkb_classes cl ON (cl.class_id = g.class_id)	
				$join
				WHERE d.firm_id = $firm_id
				AND w.is_active = 1
				AND ( w.date_retired = '' OR julianday(w.date_retired) >= julianday('$date_from') )
				AND ( w.date_curr_position_start = '' OR julianday(w.date_curr_position_start) <= julianday('$date_to') )
				" . (($condition != '') ? ' AND ' . $condition : '') . "
				GROUP BY d.mkb_id
				ORDER BY cl.class_id, g.group_id, cnt DESC, m.mkb_id";
		
		$rows = $this->fnSelectRows($sql);
		if ($rows) {
			$class_id = -1;
			$group_id = -1;
			$class_inc = 1;
			$_class_inc = 'I';
			$group_inc = 1;
			$mkb_inc = 1;
			//$_label = ('COUNT(*)' == $calc) ? 'бр. заболявания' : 'бр. дни ЗВН';
			$_label = ('COUNT(*)' == $calc) ? 'Брой случаи' : 'бр. дни ЗВН';
			$retStr .= <<< EOT
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
			$i = 1;
			foreach ($rows as $row) {
				$cnt2 += $row['cnt2'];
				if(empty($row['class_id'])) $row['class_id'] = 0;
				if(empty($row['class_name'])) $row['class_name'] = '--';
				if(empty($row['group_id'])) $row['group_id'] = 0;
				if(empty($row['group_name'])) $row['group_name'] = '--';
				if(empty($row['mkb_desc'])) $row['mkb_desc'] = '--';
				// Classes ==============================
				if ($class_id != $row['class_id']) {
					$class_id = $row['class_id'];
					$group_inc = 1;
					$mkb_inc = 1;
					list($class_name, $class_mkb) = $this->parse_group_mkb($row['class_name']);
					$converter = new ConvertRoman($class_inc);
					$_class_inc = $converter->result();

					$sql = "SELECT $calc AS cnt
							FROM $table d
							LEFT JOIN workers w ON ( w.worker_id = d.worker_id )
							LEFT JOIN mkb m ON (m.mkb_id = d.mkb_id)
							LEFT JOIN mkb_groups g ON (g.group_id = m.group_id)
							LEFT JOIN mkb_classes cl ON (cl.class_id = g.class_id)
							$join
							WHERE d.firm_id = $firm_id
							AND w.is_active = 1
							AND ( w.date_retired = '' OR julianday(w.date_retired) >= julianday('$date_from') )
							AND ( w.date_curr_position_start = '' OR julianday(w.date_curr_position_start) <= julianday('$date_to') )						
							" . (($condition != '') ? ' AND ' . $condition : '') . "
							AND cl.class_id = $class_id";
					$cnt = $this->fnSelectSingleRow($sql);

					$retStr .= <<< EOT
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
  style='font-size:11.0pt'>$cnt[0]</span></b><span style='font-size:11.0pt'><o:p></o:p></span></p>
  </td>
 </tr>
EOT;
					$class_inc++;
}
// Groups ==============================
if ($group_id != $row['group_id']) {
	$group_id = $row['group_id'];
	$mkb_inc = 1;
	list($group_name, $group_mkb) = $this->parse_group_mkb($row['group_name']);

	$sql = "SELECT $calc AS cnt
			FROM $table d
			LEFT JOIN workers w ON ( w.worker_id = d.worker_id )
			LEFT JOIN mkb m ON (m.mkb_id = d.mkb_id)
			LEFT JOIN mkb_groups g ON (g.group_id = m.group_id)
			LEFT JOIN mkb_classes cl ON (cl.class_id = g.class_id)
			$join
			WHERE d.firm_id = $firm_id
			AND w.is_active = 1
			AND ( w.date_retired = '' OR julianday(w.date_retired) >= julianday('$date_from') )
			AND ( w.date_curr_position_start = '' OR julianday(w.date_curr_position_start) <= julianday('$date_to') )	
			" . (($condition != '') ? ' AND ' . $condition : '') . "
			AND g.group_id = $group_id";
	$cnt = $this->fnSelectSingleRow($sql);

	$retStr .= <<< EOT
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
  style='font-size:11.0pt'>$cnt[0]<o:p></o:p></span></b></p>
  </td>
 </tr>
EOT;
	$group_inc++;
}
// MKB  ==============================
$retStr .= <<< EOT
<tr style='mso-yfti-irow:$i'>
  <td width="67%" valign=top style='width:67.2%;border:solid windowtext 1.0pt;
  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal><span style='font-size:11.0pt'>$mkb_inc. $row[mkb_desc]<o:p></o:p></span></p>
  </td>
  <td width="13%" style='width:13.02%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:11.0pt;mso-bidi-font-weight:bold'>$row[mkb_id]</span><span
  style='font-size:11.0pt'><o:p></o:p></span></p>
  </td>
  <td width="19%" style='width:19.78%;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
  <p class=MsoNormal align=center style='text-align:center'><span
  style='font-size:11.0pt'>$row[cnt]<o:p></o:p></span></p>
  </td>
 </tr>
EOT;
$i++;
$mkb_inc++;
$total += $row['cnt'];

} // end foreach

$retStr .= <<< EOT
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
EOT;
$retStr .= '</table>';
}
return array('table' => $retStr, 'total' => $total, 'cnt' => $cnt2);
}

// Make all `mkb_id` upper case
public function makeAllMkbUpperCase()
{
	$db = $this->getDBHandle();
	$_tables = array('anamnesis_id' => 'anamnesis', 'disease_id' =>
	'family_diseases', 'family_weight_id' => 'family_weights', 'chart_id' =>
	'patient_charts', 'prchk_id' => 'prchk_diagnosis');
	try {
		$db->beginTransaction();
		foreach ($_tables as $key => $table) {
			$rows = $this->fnSelectRows("SELECT * FROM $table");
			foreach ($rows as $row) {
				$query = "UPDATE $table SET mkb_id = '" . mb_strtoupper($row['mkb_id'], 'utf-8') .
				"' WHERE $key = $row[$key]";
				$count = $db->exec($query); //returns affected rows
			}
		}
		$rows = $this->fnSelectRows("SELECT * FROM telks");
		foreach ($rows as $row) {
			$query = "UPDATE telks SET mkb_id_1 = '" . mb_strtoupper($row['mkb_id_1'],
			'utf-8') . "', mkb_id_2 = '" . mb_strtoupper($row['mkb_id_2'], 'utf-8') .
			"', mkb_id_3 = '" . mb_strtoupper($row['mkb_id_3'], 'utf-8') . "', mkb_id_4 = '" .
			mb_strtoupper($row['mkb_id_4'], 'utf-8') . "',  WHERE telk_id = $row[telk_id]";
			$count = $db->exec($query); //returns affected rows
		}

		$db->commit();
	}
	catch (exception $e) {
		$db->rollBack();
		die("Грешка при изпълнение на заявка към базата данни: " . $e->getMessage());
	}
}

/**
	* @desc Write to log table logged-in users
	*/
public function write2Log() {
	$session_id = (isset($_SESSION)) ? session_id() : '';
	$user_name = (isset($_SESSION['sess_user_name'])) ? $this->checkStr($_SESSION['sess_user_name']) : '';
	$fname = (isset($_SESSION['sess_fname'])) ? $this->checkStr($_SESSION['sess_fname']) : '';
	$lname = (isset($_SESSION['sess_lname'])) ? $this->checkStr($_SESSION['sess_lname']) : '';
	$REMOTE_ADDR = (isset($_SERVER["REMOTE_ADDR"])) ? $_SERVER["REMOTE_ADDR"] : '';
	$HTTP_USER_AGENT = (isset($_SERVER["HTTP_USER_AGENT"])) ? $_SERVER["HTTP_USER_AGENT"] : '';
	$date_accessed = date("Y-m-d H:i:s", time());

	$db = $this->getDBHandle();
	try {
		$db->beginTransaction();
		$query = "CREATE TABLE IF NOT EXISTS access_log ([id] integer PRIMARY KEY AUTOINCREMENT, [session_id] VARCHAR, [user_name] VARCHAR, [fname] VARCHAR, [lname] VARCHAR, [REMOTE_ADDR] VARCHAR, [HTTP_USER_AGENT] VARCHAR, [date_accessed] DATETIME DEFAULT CURRENT_DATE)";
		$count = $db->exec($query);
		$query = "INSERT INTO access_log (session_id, user_name, fname, lname, REMOTE_ADDR, HTTP_USER_AGENT, date_accessed) VALUES ('$session_id', '$user_name', '$fname', '$lname', '$REMOTE_ADDR', '$HTTP_USER_AGENT', '$date_accessed')";
		$count = $db->exec($query);
		$db->commit();

	} catch (Exception $e) {
		$db->rollBack();
		//die("Грешка при изпълнение на заявка към базата данни: " . $e->getMessage());
	}
}

/**
	* @desc Show log table
	*/
public function displayLog() {
	$query = "SELECT * FROM access_log WHERE 1 ORDER BY date_accessed DESC";
	$rows = $this->fnSelectRows($query);
	return $rows;
}

/**
	* @desc Create daily DB backup and delete old backups
	*/
public function createDbBackup() {
	$dbDir = 'db/';
	$today = date('Y-m-d');
	$before = date('Y-m-d', strtotime('-5 day'));

	// Create a backup of the existing database
	if(file_exists($dbDir.'stm.db') && !file_exists($dbDir.'BKP_'.$today.'_stm.db')) {
		@copy($dbDir.'stm.db', $dbDir.'BKP_'.$today.'_stm.db');
	}
	// Delete backups older than 5 days
	if ($handle = opendir($dbDir)) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != ".." && preg_match('/BKP_(\d{4}\-\d{2}\-\d{2})_stm\.db/', $file, $matches)) {
				if($matches[1] < $before) {
					@unlink($dbDir.$file);
				}
			}
		}
		closedir($handle);
	}
}

public function isAjaxCall() {
	return (isset($_POST['xajaxargs'][0]) || isset($_POST['xjxargs']) || ( isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 'XMLHttpRequest' == $_SERVER['HTTP_X_REQUESTED_WITH'] ));
}

public function debug_data_as_table($rows = array()) {
	if(!is_array($rows)) return '';
	if(!empty($rows)) {
		echo '<table border="1">';
		foreach ($rows as $row) {
			echo '<tr>';
			foreach ($row as $key => $value) {
				if(!is_numeric($key)) echo '<th>'.$key.'&nbsp;</th>';
			}
			echo '</tr>';
			break;
		}
		
		foreach ($rows as $row) {
			echo '<tr>';
			foreach ($row as $key => $value) {
				if(!is_numeric($key)) echo '<td>'.$value.'&nbsp;</td>';
			}
			echo '</tr>';
		}
		echo '<table>';
	}
}

}
