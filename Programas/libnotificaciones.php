<?php
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
		writelog('enviar notif '. $idnotificacion .' de la req '. $idrequisicion);
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
		// $filename = 'req'. $idrequisicion .'.pdf';
		// //$path = 'your path goes here';
		// //$file = $path . "/" . $filename;

		// //$direccion = 'mail@mail.com';
		// //$asunto = 'Subject';
		// //$mensaje = 'My message';
	
		// $pdf = new PDF("L");
		// $pdf->SetFont('Arial','',6);
		// $pdf->AddPage();
	
		// ExportarEncabezados($pdf);
	
		// ExportarRequisicion($pdf, $idrequisicion);	
	
		// $content = $pdf->Output("","S");
	
		// $content = chunk_split(base64_encode($content));
	

		// // carriage return type (RFC)
		// $eol = "\r\n";

		// // main header (multipart mandatory)
		// // $headers = "From: MantenimientoCL <mttocl@cualquierlavado.com.mx>" . $eol;
		// // $headers .= "MIME-Version: 1.0" . $eol;
		// // $headers .= "Content-Type: multipart/mixed; boundary=\"" . $separator . "\"" . $eol;
		// // $headers .= "Content-Transfer-Encoding: 7bit" . $eol;
		// // $headers .= "This is a MIME encoded message." . $eol;

		// $uid = md5(uniqid(time()));
		// //$name = basename($file);

		// // header
		// $header = "From: MantenimientoCL <mttocl@cualquierlavado.com.mx>\r\n";
		// //$header .= "Reply-To: ".$replyto."\r\n";
		// $header .= "MIME-Version: 1.0\r\n";
		// $header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";

		// // // message
		// // $body = "--" . $separator . $eol;
		// // $body .= "Content-Type: text/plain; charset=\"iso-8859-1\"" . $eol;
		// // $body .= "Content-Transfer-Encoding: 8bit" . $eol;
		// // $body .= $mensaje . $eol;

		// // // attachment
		// // $body .= "--" . $separator . $eol;
		// // $body .= "Content-Type: application/pdf; name=\"" . $filename . "\"" . $eol;
		// // $body .= "Content-Transfer-Encoding: base64" . $eol;
		// // $body .= "Content-Disposition: attachment" . $eol;
		// // $body .= $content . $eol;
		// // $body .= "--" . $separator . "--";

		// $message = "--".$uid."\r\n";
		// $message .= "Content-type:text/plain; charset=iso-8859-1\r\n";
		// $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
		// $message .= $mensaje."\r\n\r\n";
		// $message .= "--".$uid."\r\n";
		// $message .= "Content-Type: application/octet-stream; name=\"".$filename."\"\r\n";
		// $message .= "Content-Transfer-Encoding: base64\r\n";
		// $message .= "Content-Disposition: attachment; filename=\"".$filename."\"\r\n\r\n";
		// $message .= $content."\r\n\r\n";
		// $message .= "--".$uid."--";
		
		// //SEND Mail
		// if ( mail($direccion, $asunto, $message, $header) ) {
			// writelog('notificaicon enviada '. $asunto);
		// }
		$to = $direccion;

		$subject = $asunto;

		$from = 'mttocl@cualquierlavado.com.mx';

		 

		// To send HTML mail, the Content-type header must be set

		$headers  = 'MIME-Version: 1.0' . "\r\n";

		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

		 

		// Create email headers

		$headers .= 'From: '.$from."\r\n".

			'Reply-To: '.$from."\r\n" .

			'X-Mailer: PHP/' . phpversion();

		 

		// Compose a simple HTML email message

		$message = '<html>';
		$message .= '<head>';
		$message .= '<style>';
		$message .= 'table {';
		$message .= 'border-radius: 5px;';
		$message .= 'border: 2px solid gray;';
		$message .= 'width:100%;';
		$message .= 'margin-bottom: 1px;';
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

		

		$message .= MostrarMarcoRequisicion($idrequisicion);

		$message .= '</body>';
		$message .= '</html>';

		 

		// Sending email

		if(mail($to, $subject, $message, $headers)){

			writelog( 'Your mail has been sent successfully.');

		} else{

			writelog( 'Unable to send email. Please try again.');

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
					writelog('agrega '. $req);
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