<?php

//$file_path = 'classes/class_pdf_forms/Medical_and_Pick.pdf';
$file_path = $_GET['i'];
if(!file_exists($file_path)) die('File '.$file_path.' doesn\'t exist');
$fsize = filesize($file_path);
if (function_exists('mime_content_type')) {
	$mtype = mime_content_type($file_path);
}
else if (function_exists('finfo_file')) {
	$finfo = finfo_open(FILEINFO_MIME); // return mime type
	$mtype = finfo_file($finfo, $file_path);
	finfo_close($finfo);
}
else {
	$mtype = "application/force-download";
}
// set headers
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: public");
header("Content-Description: File Transfer");
header("Content-Type: $mtype");
header("Content-Disposition: attachment; filename=\"".basename($file_path)."\"");
header("Content-Transfer-Encoding: binary");
header("Content-Length: $fsize");

// download
// @readfile($file_path);
$file = @fopen($file_path,"rb");
if ($file) {
	while(!feof($file)) {
		print(fread($file, 1024*8));
		flush();
		if (connection_status()!=0) {
			@fclose($file);
			die();
		}
	}
	@fclose($file);
}

?>