<?php
$sect->addEmptyParagraph();
$sect->addEmptyParagraph();

if(!isset($timesFooter)) $timesFooter = $times12;

$sect->writeText(((!empty($date)) ? $date : date("d.m.Y"))." г.\t\t\tЛекар СТМ:", $timesFooter, $alignLeft);
//$sect->writeText('('.HTMLFormat($s['chief']).')', $timesFooter, $alignRight);
$sect->writeText('(.........................................)', $timesFooter, $alignRight);

try {
	// save rtf document
	//$rtf->save($dir . '/' . basename(__FILE__, '.php') . '.rtf');
	//$rtf->save($new_file);
	$rtf->sendRtf($filename.'.rtf');
} catch (Exception $e) {
	echo $e->getMessage();
}