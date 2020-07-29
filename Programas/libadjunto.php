<?php
	require_once "libconfig.php";
	require_once "libdb.php";
	require_once "libphp.php";
	require_once "libpartida.php";
	require_once "libuser.php";

	$accion = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_SPECIAL_CHARS);
	if ( $accion == "adjundelete" ) {
		$idadjunto = filter_input(INPUT_GET, 'idadj', FILTER_SANITIZE_NUMBER_INT);
		$tipo = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_SPECIAL_CHARS);
		if ( $tipo == "adjpart" ) {
			$res = $db->prepare("UPDATE adjuntospartidas SET activo=1 WHERE id= ?;");
			$res->execute([$idadjunto]);
			echo "OK";
		}
		if ( $tipo == "adjreq" ) {
			$res = $db->prepare("UPDATE adjuntosrequisiciones SET activo=1 WHERE id= ?;");
			$res->execute([$idadjunto]);
			echo "OK";
		}
	}
	if ( $accion == "adjdelete" ) {
		$idadjunto = filter_input(INPUT_GET, 'idadj', FILTER_SANITIZE_NUMBER_INT);
		$tipo = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_SPECIAL_CHARS);
		if ( $tipo == "adjpart" ) {
			$res = $db->prepare("UPDATE adjuntospartidas SET activo=0 WHERE id= ?;");
			$res->execute([$idadjunto]);
			echo "OK";
		}
		if ( $tipo == "adjreq" ) {
			$res = $db->prepare("UPDATE adjuntosrequisiciones SET activo=0 WHERE id= ?;");
			$res->execute([$idadjunto]);
			echo "OK";
		}
	}
?>