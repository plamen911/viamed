<?php
############################################################################
#CyrLat class v. 1.0.1 by Yaroslav Shapoval
#en: Class for converting Cyrillic to Latin characters in both directions.
#ru: Класс для конвертирования Кирилицы в Латиницу и обратно.
#    "Privet, Mir!" <-> "Привет, Мир!"
#en: See test.php for example of usage
#ru: Файл test.php показывает принцип использования
#en: see "examples" dir for additional examples.
#ru: в папке "examples" дополнительные примеры
# Modified on Aug, 11 - 2008 by Plamen Markov to suit his needs
# Based on: http://www.phpclasses.org/browse/package/2641/download/zip.html
#############################################################################
class CyrLat {
	var $cyr=array(
	"Щ","Ш","Ч","Ц","Ю","Я","Ж","А","Б","В","Г","Д","Е","Ё","З","И","Й","К","Л","М","Н","О","П","Р","С","Т","У","Ф","Х","Ь","Ы","Ъ","Э");
	var $lat=array(
	"Sht","Sh","Ch","Ts","Yu","Ya","Zh","A","B","V","G","D","E","E","Z","I","J","K","L","M","N","O","P","R","S","T","U","F","H","'","Y","Y","E");
	var $lat_additional=array(
	"W","X","Q","Yo","Ja","Ju","'","`","y");
	var $cyr_additional=array(
	"В","Кс","К","Ё","Я","Ю","ь","ъ","ы");
	function cyr2lat($input){
		for($i=0;$i<count($this->cyr);$i++){
			$current_cyr=$this->cyr[$i];
			$current_lat=$this->lat[$i];
			$input=str_replace($current_cyr,$current_lat,$input);
			$input=str_replace(mb_strtolower($current_cyr, 'utf-8'),mb_strtolower($current_lat, 'utf-8'),$input);
		}
		return($input);
	}
	function lat2cyr($input){
		for($i=0;$i<count($this->lat_additional);$i++){
			$current_cyr=$this->cyr_additional[$i];
			$current_lat=$this->lat_additional[$i];
			$input=str_replace($current_lat,$current_cyr,$input);
			$input=str_replace(mb_strtolower($current_lat, 'utf-8'),mb_strtolower($current_cyr, 'utf-8'),$input);
		}
		for($i=0;$i<count($this->lat);$i++){
			$current_cyr=$this->cyr[$i];
			$current_lat=$this->lat[$i];
			$input=str_replace($current_lat,$current_cyr,$input);
			$input=str_replace(mb_strtolower($current_lat, 'utf-8'),mb_strtolower($current_cyr, 'utf-8'),$input);
		}
		return($input);
	}
}

#Uncomment for example
#$cyrlat = new CyrLat;
#$inp="Здравствуй, мой далёкий незнакомый друг!";
#$out=$cyrlat->cyr2lat($inp);
#echo "!: $out <br>";
#$out2=$cyrlat->lat2cyr($out);
#echo "!: $out2 <br>";

/*$cyrlat = new CyrLat;
$inp="Здравствуй, мой далёкий незнакомый друг! Test щастие";
$out=$cyrlat->cyr2lat($inp);
echo "!: $out <br>";*/


?>