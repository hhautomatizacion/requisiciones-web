<?php
	require_once "libconfig.php";
	require_once "libdb.php";
	require_once "libphp.php";
	require_once "libpartida.php";
	require_once "libuser.php";

	$accion = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_SPECIAL_CHARS);
	if ( $accion == "comadd" ) {
		$tipo = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_SPECIAL_CHARS);
		if ( $tipo == "comreq" ) {
			$idrequisicion = filter_input(INPUT_GET, 'idreq', FILTER_SANITIZE_NUMBER_INT);
			$comentario = urldecode(filter_input(INPUT_GET, 'comentario', FILTER_DEFAULT));
			$res = $db->prepare("INSERT INTO comentariosrequisiciones VALUES (0, ?,0, ?, ?,NOW(),1);");
			$res->execute([$idrequisicion, $comentario, usuarioId()]);
			echo "OK";
		}
		if ( $tipo == "compart" ) {
			$idpartida = filter_input(INPUT_GET, 'idpart', FILTER_SANITIZE_NUMBER_INT);
			$comentario = urldecode(filter_input(INPUT_GET, 'comentario', FILTER_DEFAULT));
			$res = $db->prepare("INSERT INTO comentariospartidas VALUES (0, ?,0, ?, ?,NOW(),1);");
			$res->execute([$idpartida, $comentario, usuarioId()]);
			echo "OK";
		}
	}
	if ( $accion == "comundelete" ) {
		$idcomentario = filter_input(INPUT_GET, 'idcom', FILTER_SANITIZE_NUMBER_INT);
		$tipo = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_SPECIAL_CHARS);
		if ( $tipo == "compart" ) {
			$res = $db->prepare("UPDATE comentariospartidas SET activo=1 WHERE id= ?;");
			$res->execute([$idcomentario]);
			echo "OK";
		}
		if ( $tipo == "comreq" ) {
			$res = $db->prepare("UPDATE comentariosrequisiciones SET activo=1 WHERE id= ?;");
			$res->execute([$idcomentario]);
			echo "OK";
		}
	}
	if ( $accion == "comdelete" ) {
		$idcomentario = filter_input(INPUT_GET, 'idcom', FILTER_SANITIZE_NUMBER_INT);
		$tipo = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_SPECIAL_CHARS);
		if ( $tipo == "compart" ) {
			$res = $db->prepare("UPDATE comentariospartidas SET activo=0 WHERE id= ?;");
			$res->execute([$idcomentario]);
			echo "OK";
		}
		if ( $tipo == "comreq" ) {
			$res = $db->prepare("UPDATE comentariosrequisiciones SET activo=0 WHERE id= ?;");
			$res->execute([$idcomentario]);
			echo "OK";
		}
	}
?>