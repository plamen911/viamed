<?php
require ("sqlitedb.php");

$dbInst = new SqliteDB();

$Params['errcode'] = 0;
$Params['errmsg'] = '';
$Params['cols'] = 'empty';
$Params['data'] = 'empty';
$Params['numrows'] = 0;

$db = $dbInst->getDBHandle();

function buildXmlResponse($Params) {
	header('Content-type: text/xml');
	$xml  = '<?xml version="1.0" encoding="utf-8" ?>';
	$xml .= '<response>';
	if(is_array($Params) && count($Params)) {
		foreach($Params as $Name => $Value) {
			$xml .= '<'.$Name.'>'.htmlspecialchars($Value).'</'.$Name.'>';
		}
	}
	$xml .= '</response>';
	die($xml);
}

if(isset($_POST['queryString16']) && !empty($_POST['queryString16'])) {
	$sql = stripslashes(trim($_POST['queryString16']));
	try {
		if(preg_match('/^select\b/i', $sql)) {
			$prepstatement = $db->prepare($sql);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				$Params['errcode'] = 1;
				$Params['errmsg'] = 'Select query failed: ' . $err[2] . ', SQL: ' . $sql;
				buildXmlResponse($Params);
			}
			$prepstatement->execute();
			$rows = $prepstatement->fetchAll();
			if(!empty($rows)) {
				$Params['numrows'] = count($rows);
				$ary = array();
				foreach (array_keys($rows[0]) as $key => $val) {
					if(is_numeric($val)) continue;
					$ary[] = $val;
				}
				$Params['cols'] = implode(';', $ary);
				$ary = array();
				foreach ($rows as $row) {
					$line = array();
					foreach ($row as $key => $val) {
						if(is_numeric($key)) continue;
						
						$val = preg_replace('/;/', '[+semicolumn+]', $val);
						$val = preg_replace('/\|/', '[+pipe+]', $val);
						$line[] = $val;
					}
					$ary[] = implode(';', $line);
				}
				$Params['data'] = implode('|', $ary);
			}
		}
		elseif (preg_match('/^(insert|update|replace|delete|alter|create)\b/i', $sql, $matches)) {			
			$prepstatement = $db->prepare($sql);
			if (!$prepstatement) {
				$err = $db->errorInfo();
				$Params['errcode'] = 1;
				$Params['errmsg'] = 'Insert/update query failed: ' . $err[2] . ', SQL: ' . $sql;
				buildXmlResponse($Params);
			}
			$Params['numrows'] = $prepstatement->execute();
			if('insert' == mb_strtolower($matches[1])) {
				$Params['cols'] = 'last_insert_id';
				$Params['data'] = $db->lastInsertId();
			} else {
				$Params['cols'] = 'affected';
				$Params['data'] = $Params['numrows'];
			}
		}
		else {
			$Params['errcode'] = 1;
			$Params['errmsg'] = 'Unrecognized query specified, SQL: ' . $sql;
			buildXmlResponse($Params);
		}
	}
	catch (exception $e) {
		$Params['errcode'] = 1;
		$Params['errmsg'] = 'Query failed: ' . $e->getMessage() . ', SQL: ' . $sql;
		buildXmlResponse($Params);
	}
}

buildXmlResponse($Params);


























?>