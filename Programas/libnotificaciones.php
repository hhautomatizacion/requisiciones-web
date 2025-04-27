<?php
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\SMTP;

	require_once "PHPMailer.php";
	require_once "SMTP.php";
	require_once "Exception.php";
	require_once "libconfig.php";
	require_once "libdb.php";
	require_once "libphp.php";
	require_once "libpartida.php";
	require_once "librequisicion.php";
	require_once "libpdf.php";
	require_once "libuser.php";

	$accion = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_SPECIAL_CHARS);
	if ( $accion == "checkfornotifications" ) {
		notificarPartidas();
		foreach ( NotificacionesRequisiciones() as $item ) {
			$res = $db->prepare("SELECT idrequisicion FROM notificacionesrequisiciones WHERE id=" . $item . ";");
			$res->execute();
			while ($row = $res->fetch()) {
				EnviarNotificacionRequisicion($item, $row[0]);
			}
		}
		echo "OK";
	}

	function NotificacionesPartidas() {
		global $db;
		$resultado = array();
		$res = $db->prepare("SELECT id FROM notificacionespartidas WHERE fecha < DATE_SUB(NOW(), INTERVAL 10 MINUTE) AND activo=1;");
		$res->execute();
		while ($row = $res->fetch()) {
			$resultado[] = $row[0];
		}
		foreach ( $resultado as $item ) {
			$res = $db->prepare("UPDATE notificacionespartidas SET activo=0 WHERE id=" . $item . ";");
			$res->execute();
		}
		return $resultado;
	}

	function NotificacionesRequisiciones() {
		global $db;
		$resultado = array();
		$res = $db->prepare("SELECT id FROM notificacionesrequisiciones WHERE fecha < DATE_SUB(NOW(), INTERVAL 10 MINUTE) AND activo=1;");
		$res->execute();
		while ($row = $res->fetch()) {
			$resultado[] = $row[0];
		}
		foreach ( $resultado as $item ) {
			$res = $db->prepare("UPDATE notificacionesrequisiciones SET activo=0 WHERE id=" . $item . ";");
			$res->execute();
		}
		return $resultado;
	}
	
	function EnviarNotificacionRequisicion($idnotificacion, $idrequisicion, $destinatarios = array()) {
		global $db;
		$idusuario = 0;
		$idsolicitante = 0;
		$direcciones = array();
		$res= $db->prepare("SELECT idusuario, clave FROM notificacionesrequisiciones WHERE id=" . $idnotificacion . ";");
		$res->execute();
		while ($row = $res->fetch()) {
			$idusuario = $row[0];
			switch ($row[1]) {
				case 4:
					$tiponotificacion = 'Impresa';
					break;
				case 5:
					$tiponotificacion = 'Surtida';
					break;
				case 6:
					$tiponotificacion = 'Eliminada';
					break;
			}
		}
		if ( $idnotificacion == 0 ) {
			$tiponotificacion = 'Con cambios';
		}
		$res= $db->prepare("SELECT idsolicitante, idusuario FROM requisiciones WHERE id=" . $idrequisicion . ";");
		$res->execute();
		while ($row = $res->fetch()) {
			if ( $idusuario != $row[0] && !in_array($row[0], $destinatarios) ) {
				$destinatarios[] = $row[0];
			}
			if ( $idusuario != $row[1] && !in_array($row[1], $destinatarios) ) {
				$destinatarios[] = $row[1];
			}
		}
		$res= $db->prepare("SELECT idusuario FROM seguidoresrequisiciones WHERE idrequisicion=? AND activo=1;");
		$res->execute([$idrequisicion]);
		while ($row = $res->fetch()) {
			if ( !in_array($row[0], $destinatarios) ) {
				$destinatarios[] = $row[0];
			}
		}
		foreach ($destinatarios as $dest) {
			$direccion = ObtenerDescripcionDesdeID("usuarios", $dest, "email");
			if ( $direccion != '') {
				$direcciones[] = $direccion;
			}
		}
		
		if ( count($direcciones) ) {
			$asunto = ObtenerDescripcionDesdeID("usuarios", $idusuario , "nombre") . " reporta la requisicion Id=" . $idrequisicion . " como " . $tiponotificacion;
			enviarRequisicionPorCorreo($idrequisicion, $direcciones, $asunto);
		}
	}

	function obtenerAdjuntosRequisicion($idrequisicion) {
		global $db;
		$mail_attachmentsize = obtenerPreferenciaGlobal("mail", "attachmentsize", "10485760");
		$nombre = "";
		$longitud = 0;
		$partidas = array();
		$resultado = array();
		$longitudmax = parse_size($mail_attachmentsize);
		$uploaddir = obtenerPreferenciaGlobal("uploads", "uploaddir", "uploads/");
		$res = $db->prepare("SELECT nombre, longitud FROM adjuntosrequisiciones WHERE idrequisicion=" . $idrequisicion . " AND activo=1;");
		$res->execute();
		while ($row = $res->fetch()) {
			$nombre = $row[0];
			$longitud = $row[1];
			if ( $longitud <= $longitudmax ) {
				$resultado[] = $uploaddir . "r" . $idrequisicion . "/" . $nombre;
			}
		}
		$res = $db->prepare("SELECT id FROM partidas WHERE idrequisicion=" . $idrequisicion . " AND activo=1;");
		$res->execute();
		while ($row = $res->fetch()) {
			$partidas[] = $row[0];
		}
		foreach ( $partidas as $idpartida ) {
			$res= $db->prepare("SELECT nombre, longitud FROM adjuntospartidas WHERE idpartida=" . $idpartida . " AND activo=1;");
			$res->execute();
			while ($row = $res->fetch()) {
				$nombre = $row[0];
				$longitud = $row[1];
				if ( $longitud <= $longitudmax ) {
					$resultado[] = $uploaddir . "p" . $idpartida . "/" . $nombre;
				}
			}
		}
		return $resultado;
	}

	function enviarRequisicionPorCorreo($idrequisicion, $direcciones, $asunto) {
		$direccion = '';
		$mail_server = obtenerPreferenciaGlobal("mail", "server", "128.128.5.243");
		$mail_port = obtenerPreferenciaGlobal("mail", "port", "25");
		$mail_user = obtenerPreferenciaGlobal("mail", "user", "mttocl");
		$mail_pass = obtenerPreferenciaGlobal("mail", "pass", "lcottm");
		$mail_tls = obtenerPreferenciaGlobal("mail", "usetls", "true");
		$mail_fromaddress = obtenerPreferenciaGlobal("mail", "fromaddres", "mttocl@cualquierlavado.com.mx");
		$mail_fromname = obtenerPreferenciaGlobal("mail", "fromname", "MantenimientoCL");
		$message = '<html>';
		$message .= '<head>';
		$message .= '<style>';
		$message .= 'table {';
		$message .= 'border-radius: 5px;';
		$message .= 'border: 2px solid gray;';
		$message .= 'width:100%;';
		$message .= 'margin-bottom: 1px;';
		$message .= '}';
		$message .= 'td {';
		$message .= 'border-bottom: 1px solid gray;';
		$message .= '}';
		$message .= 'tr:last-child>td {';
		$message .= 'border-bottom: 0px;';
		$message .= '}';
		$message .= '.campo {';
		$message .= 'border: 0;';
		$message .= 'width:100%;';
		$message .= 'margin-bottom: 1px;';
		$message .= '}';
		$message .= '.req {background: lightgray;}';
		$message .= '.printed {background: #FFC040;}';
		$message .= '.supplied {background: #C0C080;}';
		$message .= '.req {opacity: 0.9;}';
		$message .= '.owner {opacity: 1;}';
		$message .= '.deleted {opacity: 0.5;}';
		$message .= '.partsupplied {background: #C0C080;}';
		$message .= '.partdeleted {opacity: 0.5;}';
		$message .= '.com {opacity: 0.9;}';
		$message .= '.comowner {opacity: 1;}';
		$message .= '.comdeleted {opacity: 0.5;}';
		$message .= '</style>';
		$message .= '</head>';
		$message .= '<body>';
		$message .= MostrarRequisicion($idrequisicion);
		$message .= '</body>';
		$message .= '</html>';
		try  {
			$mail = new PHPMailer(true);
			$mail->IsSMTP();
			$mail->Host = $mail_server;
			$mail->Port = $mail_port;
			$mail->SMTPAuth = true;
			$mail->Username = $mail_user;
			$mail->Password = $mail_pass;
			if ( booleanFromString($mail_tls) ) {
				$mail->SMTPSecure = 'tls';
			}
			$mail->setFrom($mail_fromaddress, $mail_fromname);
			foreach ($direcciones as $direccion) {
				$mail->addAddress($direccion);
				writelog('Enviar correo: ' . $asunto . ' a ' . $direccion);
			}
			$mail->IsHTML(true);
			$mail->CharSet = 'utf-8';
			$mail->Subject = $asunto;
			$mail->Body = $message;
			foreach ( obtenerAdjuntosRequisicion($idrequisicion) as $adjunto ) {
				$mail->addAttachment($adjunto);
			}
			$mail->send();
		}
		catch (Exception $e) {
			foreach ($direcciones as $direccion) {
				writelog('Error al enviar correo: ' . $asunto . ' a ' . $direccion);
				writelog($mail->ErrorInfo);
			}
		}
	}
	
	function notificarPartidas() {
		global $db;
		$partidas = array();
		$requisiciones = array();
		$destinatarios = array();
		foreach ( NotificacionesPartidas() as $item ) {
			$res = $db->prepare("SELECT idpartida FROM notificacionespartidas WHERE id=" . $item . ";");
			$res->execute();
			while ($row = $res->fetch()) {
				$partidas[] = $row[0];
			}
		}
		foreach ($partidas as $item) {
			$res = $db->prepare("SELECT idrequisicion FROM partidas WHERE id=" . $item . ";");
			$res->execute();
			while ($row = $res->fetch()) {
				$req = $row[0];
				if ( !in_array($req, $requisiciones) ) {
					$requisiciones[] = $req;
				}
			}
		}
		foreach ( $requisiciones as $item ) {
			$destinatarios = array();
			foreach ($partidas as $idpartida ) {
				$partida = 0;
				$res = $db->prepare("SELECT id FROM partidas WHERE id=" . $idpartida . " AND idrequisicion=" . $item . ";");
				$res->execute();
				while ($row = $res->fetch()) {
					$partida=$row[0];
				}
				if ( $partida != 0 ) {
					$res = $db->prepare("SELECT idusuario FROM seguidorespartidas WHERE idpartida=? AND activo=1;");
					$res->execute([$partida]);
					while ($row = $res->fetch()) {
						if ( !in_array($row[0], $destinatarios) ) {
							$destinatarios[] = $row[0];
						}
					}
				}
			}
			EnviarNotificacionRequisicion(0, $item, $destinatarios);
		}
	}
?>
