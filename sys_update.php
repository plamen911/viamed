<?php
set_time_limit(600);// set script time limit to 10 min
ini_set("memory_limit","100M");

require ('includes.php');

if(isset($_POST['btnImport']) && $_FILES['datafile']['tmp_name'] && isset($_SESSION['sess_user_level']) && 1 == $_SESSION['sess_user_level']) {
	$errmsg = array();
	$filename = $_FILES['datafile']['name'];
	$ftmp_name = $_FILES['datafile']['tmp_name'];
	$mime_type = $_FILES['datafile']['type'];
	$filesize = $_FILES['datafile']['size'];
	//Allowable file Mime Types. Add more mime types if you want
	$FILE_MIMES = array('application/zip', 'application/x-zip-compressed', 'application/octet-stream');
	//Allowable file ext. names. you may add more extension names.
	$FILE_EXTS = array('zip');
	$file_ext = (preg_match('/\.([A-Za-z]+)$/i', $filename, $matches)) ? strtolower($matches[1]) : '';

	if (!in_array($mime_type, $FILE_MIMES)) {
		setFlash( print_r($_FILES, 1) );
		setFlash('Съжалявам, '.$filename.' ('.$mime_type.') не е ZIP файл.');
		header('Location: official_data.php'.((isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) ? '?'.$_SERVER['QUERY_STRING'].'' : ''));
		exit();
	}
	if(!preg_match('/^update_\d+\-\d+\-\d+\.zip/', $filename)) {
		setFlash('Невалидно име на ZIP файла.');
		header('Location: official_data.php'.((isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) ? '?'.$_SERVER['QUERY_STRING'].'' : ''));
		exit();
	}

	if (is_uploaded_file($_FILES['datafile']['tmp_name'])) {
		// extract files
		$zip = new ZipArchive;
		$res = $zip->open($_FILES['datafile']['tmp_name']);
		if ($res === TRUE) {
			$zip->extractTo('./');
			$zip->close();
			// update db
			if(file_exists('upd.xml')) {
				$db = $dbInst->getDBHandle();
				$xml = file_get_contents('upd.xml');
				if(preg_match_all('/\<query\>(.*?)\<\/query\>/si', $xml, $queries)) {
					try {
						$db->beginTransaction();
						foreach ($queries[1] as $query) {
							$count = $db->exec(trim($query)); //returns affected rows
						}
						$db->commit();
					}
					catch (exception $e) {
						$db->rollBack();
					}
				}
				@unlink('upd.xml');
			}
			setFlash('СИСТЕМАТА БЕ УСПЕШНО АКТУАЛИЗИРАНА!');
		} else {
			setFlash('Грешка при отваряне на архива!');
		}
	} else {
		setFlash('Possible file upload attack: '.$filename.' ('.$mime_type.').');
		header('Location: official_data.php'.((isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) ? '?'.$_SERVER['QUERY_STRING'].'' : ''));
		exit();
	}
}

header('Location: official_data.php'.((isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) ? '?'.$_SERVER['QUERY_STRING'].'' : ''));
exit();









































