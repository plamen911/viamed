<?php
require('config.php');
require ("functions.php");
require ("sqlitedb.php");

error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

$ret['errcode'] = 0;
$ret['errmsg'] = '212';
//die('----------');
die('OUT: '.json_encode($ret));

if(isset($_POST['ajax_action']) && 'download_db' == $_POST['ajax_action']) {
	$ret['errcode'] = 0;
	$ret['errmsg'] = '';

	if(!class_exists('mycurl')) {
		// php class to get remote files (as strings) when `allow_url_fopen` is disabled
		// Based on: http://wiki.dreamhost.com/index.php/Talk:Allow_url_fopen
		class mycurl {
			var $timeout;
			var $url;
			var $file_contents;
			var $error = '';
			function getFile($url, $timeout = 5) {
				# use CURL library to fetch remote file
				$ch = curl_init();
				$this->url = $url;
				$this->timeout = $timeout;
				curl_setopt ($ch, CURLOPT_URL, $this->url);
				curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
				curl_setopt ($ch, CURLOPT_VERBOSE, true);
				$this->file_contents = curl_exec($ch);
				if ( curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 200 ) {
					$this->error = 'Базата данни не може да бъде синхронизирана.';
					unset($ch);
					return false;
				} else {
					unset($ch);
					return $this->file_contents;
				}
			}
		}
	}

	$url = 'http://localhost/stm2008/hipokrat/db/stm.db';

	$curl = new mycurl;

	if(false === $rawdata = $curl->getFile($url)) {
		$ret['errcode'] = 1;
		$ret['errmsg'] = $curl->error;
		die(json_encode($ret));
	} else {
		//save db
		$dbdir = 'db/';
		$dbname = basename($url);
		if(is_file($dbdir.$dbname)) {
			// make db. backup
			copy($dbdir.$dbname, $dbdir.time().'_'.$dbname);
		}
		$fp = fopen($dbdir.$dbname, 'w');
		fwrite($fp, $rawdata);
		fclose($fp);

		$dbInst = new SqliteDB();
		$dbInst->query("UPDATE users SET hdd = '".$dbInst->getHDDSerial()."'");
	}
	die(json_encode($ret));
}

ob_start();
?>
<script type="text/javascript">
//<![CDATA[
$(function(){
	$('#lnkSynch').bind('click', function(e){
		e.preventDefault();
		$('#preLoader').css('display', 'block');
		$.post('<?=$_SERVER['PHP_SELF']?>', { ajax_action: 'download_db' }, function(data){
			$('#preLoader').css('display', 'none');
			if(!data){
				alert('Грешка! Няма отговор от сървъра.');
			} else if(data.errcode != '0') {
				alert(data.errmsg);
			} else {
				alert('Готово!');
				window.location.href = 'index.php';
			}
		}, 'json');
	});
});
//]]>
</script>
<?php
$echoJS = ob_get_clean();

include("header.php");
echo '<p>&nbsp;</p>';
echo '<p><a id="lnkSynch" href="#">Синхронизация на данните</a></p>';
echo '<p>&nbsp;</p>';
echo '<p><a href="#">&larr; Обратно</a></p>';
echo '<p>&nbsp;</p>';
include("footer.php");
