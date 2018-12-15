<?php
	use PHPMailer\PHPMailer\PHPMailer;
	
	require_once("PHPMailer.php");
	require_once("SMTP.php");
	require_once("libconfig.php");
	require_once("libdb.php");
	require_once("libphp.php");
	require_once("libpartida.php");
	require_once("librequisicion.php");
	require_once("libpdf.php");
	require_once("libuser.php");
	
	function NotificacionesPartidas() {
		global $db;
		$resultado = array();
		$res= $db->prepare("SELECT id FROM notificacionespartidas WHERE fecha < DATE_SUB(NOW(), INTERVAL 10 MINUTE) AND activo=1;");
		$res->execute();
		while ($row = $res->fetch()) {
			$resultado[] = $row[0];
		}
		foreach ( $resultado as $item ) {
			$res= $db->prepare("UPDATE notificacionespartidas SET activo=0 WHERE id=". $item .";");
			$res->execute();
		}
		return $resultado;
	}

	function NotificacionesRequisiciones() {
		global $db;
		$resultado = array();
		$res= $db->prepare("SELECT id FROM notificacionesrequisiciones WHERE fecha < DATE_SUB(NOW(), INTERVAL 10 MINUTE) AND activo=1;");
		$res->execute();
		while ($row = $res->fetch()) {
			$resultado[] = $row[0];
		}
		foreach ( $resultado as $item ) {
			$res= $db->prepare("UPDATE notificacionesrequisiciones SET activo=0 WHERE id=". $item .";");
			$res->execute();
		}
		return $resultado;
	}
	
	function EnviarNotificacionRequisicion($idnotificacion, $idrequisicion) {
		global $db;
		$idusuario=0;
		$idsolicitante=0;
		$destinatarios=array();
		//writelog('enviar notif '. $idnotificacion .' de la req '. $idrequisicion);
		$res= $db->prepare("SELECT idusuario, clave FROM notificacionesrequisiciones WHERE id=". $idnotificacion .";");
		$res->execute();
		while ($row = $res->fetch()) {
			$idusuario = $row[0];
			switch ($row[1]) {
				case 4:
					$tiponotificacion='Impresa';			
					break;
				case 5:
					$tiponotificacion='Surtida';			
					break;
				case 6:
					$tiponotificacion='Eliminada';			
					break;
			}
		}
		if ( $idnotificacion == 0 ) {
			$tiponotificacion='Con cambios';			
		}
		$res= $db->prepare("SELECT idsolicitante, idusuario FROM requisiciones WHERE id=". $idrequisicion .";");
		$res->execute();
		while ($row = $res->fetch()) {
			if ( $idusuario != $row[0] ) {
				$destinatarios[] = $row[0];
			}
			if ( $idusuario != $row[1] ) {
				$destinatarios[] = $row[1];
			}
		}		
		foreach ($destinatarios as $dest) {
			$direccion = ObtenerDescripcionDesdeID("usuarios", $dest, "email");
			$mensaje=ObtenerDescripcionDesdeID("usuarios", $idusuario , "nombre") ." reporta la requisicion Id=". $idrequisicion ." como ". $tiponotificacion;
			if ( $direccion != '') {
			writelog('enviar notificacion a '. $direccion .' requisicion '. $idrequisicion .'');
			enviarRequisicionPorCorreo($idrequisicion, $direccion, "Requisicion Id=". $idrequisicion ." ". $tiponotificacion, $mensaje);
			}
		}
	}
	
	function enviarRequisicionPorCorreo($idrequisicion, $direccion, $asunto, $mensaje) {
		global $mail_server;
		global $mail_port;
		global $mail_user;
		global $mail_pass;
		global $mail_fromaddress;
		global $mail_fromname;

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
		$message .= '.req {background: lightgray;}';
		$message .= '.printed {background: #FFC040;}';
		$message .= '.supplied {background: #C0C080;}';
		$message .= '.req {	opacity: 0.9;}';
		$message .= '.owner {opacity: 1;}';
		$message .= '.deleted {opacity: 0.5;}';
		$message .= '.partsupplied {background: #C0C080;}';
		$message .= '.partdeleted {opacity: 0.5;}';
		$message .= '.com {	opacity: 0.9;}';
		$message .= '.comowner {opacity: 1;}';
		$message .= '.comdeleted {opacity: 0.5;}';
		$message .= '</style>';
		$message .= '</head>';
		$message .= '<body>';
		$message .= ResumenRequisicion($idrequisicion);
		$message .= '</body>';
		$message .= '</html>';
	 
		$mail = new PHPMailer(true);

		try 
		{
			$mail->IsSMTP();
			$mail->Host = $mail_server;
			$mail->SMTPAuth = true;
			$mail->Username = $mail_user;
			$mail->Password = $mail_pass;
			$mail->SMTPSecure = 'tls';
			$mail->port = $mail_port;

			$mail->setFrom($mail_fromaddress,$mail_fromname);
			$mail->addAddress($direccion);

			$mail->IsHTML(true);
			$mail->CharSet = 'utf-8';
			$mail->Subject=$asunto;
			$mail->Body=$message;

			$mail->send();
		}
		catch (Exception $e)
		{
			writelog('Error al enviar correo: '. $asunto . ' a '. $direccion);
		}
		
	}
	function notificarPartidas() {
		global $db;
		$partidas = array();
		$requisiciones = array();
		
		foreach ( NotificacionesPartidas() as $item ) {
			$res= $db->prepare("SELECT idpartida FROM notificacionespartidas WHERE id=". $item .";");
			$res->execute();
			while ($row = $res->fetch()) {
				$partidas[] = $row[0];
			}
		}
		foreach ($partidas as $item) {
			$res= $db->prepare("SELECT idrequisicion FROM partidas WHERE id=". $item .";");
			$res->execute();
			while ($row = $res->fetch()) {
				$req = $row[0];
				if ( !in_array($req, $requisiciones) ) {
					$requisiciones[] = $req;
				}
			}	
		}
		foreach ( $requisiciones as $item ) {
			EnviarNotificacionRequisicion(0, $item);
		}
	}
	notificarPartidas();
	foreach ( NotificacionesRequisiciones() as $item ) {
		$res= $db->prepare("SELECT idrequisicion FROM notificacionesrequisiciones WHERE id=". $item .";");
		$res->execute();
		while ($row = $res->fetch()) {
			EnviarNotificacionRequisicion($item, $row[0]);
		}	
	}
?>
