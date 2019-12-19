<?php
	if ( isset($_GET["action"]) && $_GET["action"] == "getserverinfo" ) {
		echo file_upload_max_size();
	}

	function randomString($length = 6) {
		$alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ";
		$pass = array();
		$alphaLength = strlen($alphabet) - 1;
		for ($i = 0; $i < $length; $i++) {
			$pass[] = $alphabet[rand(0, $alphaLength)];
		}
		return implode($pass);
	}

	function resaltarBusqueda($dato, $q) {
		if (strlen($dato) ==0 ) {
			return "";
		}
		if (strlen($q) ==0 ) {
			return $dato;
		}
		if ( !is_int(strpos(strtolower($dato), strtolower($q))) ) {
			return $dato;
		}
		$resultado="";
		$inicio=strpos(strtolower($dato),strtolower($q));
		$resultado=substr($dato,0,$inicio)."<b>". substr($dato,$inicio,strlen($q)) ."</b>". resaltarBusqueda(substr($dato,$inicio+strlen($q)), $q);
		return $resultado;
	}

	function formatBytes($bytes, $precision = 2) {
		$units = array('Bytes', 'KB', 'MB', 'GB', 'TB');
		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);
		$bytes /= pow(1024, $pow);
		return round($bytes, $precision) . ' ' . $units[$pow];
	}

	function file_upload_max_size() {
		$post_max_size = parse_size(ini_get('post_max_size'));
		if ($post_max_size > 0) {
			$max_size = $post_max_size;
		}
		$upload_max_size = parse_size(ini_get('upload_max_filesize'));
		if ($upload_max_size > 0 && $upload_max_size < $max_size) {
			$max_size = $upload_max_size;
		}
		return $max_size;
	}

	function parse_size($size) {
		$unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
		$size = preg_replace('/[^0-9\.]/', '', $size);
		if ($unit) {
			return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
		} else {
			return round($size);
		}
	}

	function writelog( $data ) {
		$output = $data;
		$date = date("Y-m-d");
		$time = date("Y-m-d H:i:s");
		if ( is_array( $output ) ) {
			$output = print_r($output, true);
		}
		$myfile = fopen("uploads/". $date .".log", "a") ;
		fwrite($myfile, $time ." ". $output ."\n");
		fclose($myfile);
	}
?>
