<?php
	function randomString($length = 6) {
		$alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
		$pass = array();
		$alphaLength = strlen($alphabet) - 1;
		for ($i = 0; $i < $length; $i++) {
			$n = rand(0, $alphaLength);
			$pass[] = $alphabet[$n];
		}
		return implode($pass);
	}

	function formatBytes($bytes, $precision = 2) {
		$units = array('Bytes', 'KB', 'MB', 'GB', 'TB');
		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);
		$bytes /= pow(1024, $pow);
		return round($bytes, $precision) . ' ' . $units[$pow];
	}

	function writelog( $data ) {
		$output = $data;
		$date = date("Y-m-d");
		$time = date("Y-m-d H:i:s");
		if ( is_array( $output ) )
			$output = implode( ',', $output);

		$myfile = fopen("uploads/". $date .".log", "a") ;
		fwrite($myfile, $time ." ". $output ."\n");
		fclose($myfile);
	}
?>
