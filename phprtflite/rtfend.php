<?php
/**
 * @global $sect
 * @global $times12
 * @global $alignLeft
 * @global $alignRight
 * @global $rtf
 * @global $filename
 */

$stmChief = ! empty($s['chief']) ? stmChiefShort($s['chief']) : 'А. Терзиева';

$sect->addEmptyParagraph();
$sect->addEmptyParagraph();

if (! isset($timesFooter)) {
    $timesFooter = $times12;
}

$sect->writeText(((!empty($date)) ? $date : date("d.m.Y"))." г.\t\t\tЛице, управляващо СТМ:", $timesFooter, $alignLeft);
//$sect->writeText('('.HTMLFormat($s['chief']).')', $timesFooter, $alignRight);
$sect->writeText('('.$stmChief.')', $timesFooter, $alignRight);

try {
	// save rtf document
	//$rtf->save($dir . '/' . basename(__FILE__, '.php') . '.rtf');
	//$rtf->save($new_file);
	$rtf->sendRtf($filename.'.rtf');
} catch (Exception $e) {
	echo $e->getMessage();
}
