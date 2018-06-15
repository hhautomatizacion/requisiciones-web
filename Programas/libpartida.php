<?php
	require_once("libconfig.php");
	require_once("libdb.php");
	require_once("libphp.php");
	require_once("librequisicion.php");

	if ( isset($_POST["accion"]) && $_POST["accion"] == "agregaradjuntopart" ) {
		$idpartida=$_POST["partida"];
		$cntarchivoduplicado=0;
		$uploaddir="uploads/";
		$rutaupload=$uploaddir ."p". $idpartida;
		if (!is_writeable($rutaupload)) {
			mkdir($rutaupload);
		}
		$nombrearchivo = $_FILES["archivo"]["name"];
		$rutatemp = $_FILES["archivo"]["tmp_name"];
		$longitudarchivo=$_FILES["archivo"]["size"];
		$rutadestino=$rutaupload ."/". $nombrearchivo;
		$nombrearchivooriginal = $nombrearchivo;
		while(file_exists($rutadestino)) {
			$cntarchivoduplicado = $cntarchivoduplicado + 1;
			list($name, $ext) = explode(".", $nombrearchivooriginal);
			$nombrearchivo = $name ." (". $cntarchivoduplicado .").". $ext;
			$rutadestino=$rutaupload ."/". $nombrearchivo;
		}
		if (move_uploaded_file($rutatemp,$rutadestino)) {
			$res = $db->prepare("INSERT INTO adjuntospartidas VALUES (0,". $idpartida .",'". $nombrearchivo ."',". $longitudarchivo .",". $_COOKIE["usuario"] .",NOW(),1);");
			$res->execute();
			echo "OK";
		}	
	}
	if ( isset($_GET["id"]) ) {
		$idpartida=$_GET["id"];
		switch ($_GET["action"]) {
			case "parttobesupplied":
				$res = $db->prepare("UPDATE partidas SET surtida=0 WHERE id=". $idpartida .";");
				$res->execute();
				$res = $db->prepare("UPDATE requisiciones SET surtida=0 WHERE id IN (SELECT idrequisicion FROM partidas WHERE id=". $idpartida .");");
				$res->execute();
				echo "OK";
				break;
			case "partsupplied":
				$res = $db->prepare("UPDATE partidas SET surtida=1 WHERE id=". $idpartida .";");
				$res->execute();
				$res = $db->prepare("INSERT INTO notificacionespartidas VALUES (0, NOW(), 5,". $idpartida .", ". $_COOKIE["usuario"] .",1)");
				$res->execute();
				echo "OK";
				break;
			case "partdelete":
				$res = $db->prepare("UPDATE partidas SET activo=0 WHERE id=". $idpartida .";");
				$res->execute();
				$res = $db->prepare("INSERT INTO notificacionespartidas VALUES (0, NOW(), 6,". $idpartida .", ". $_COOKIE["usuario"] .",1)");
				$res->execute();
				echo "OK";
				break;
			case "partundelete":
				$res = $db->prepare("UPDATE partidas SET activo=1 WHERE id=". $idpartida .";");
				$res->execute();
				echo "OK";
				break;
		}
	}
	
	function PartidaEsActiva($idpartida) {
		global $db;
		$resultado=false;
		$res = $db->prepare("SELECT activo FROM partidas WHERE id=". $idpartida .";");
		$res->execute();
		while ($row = $res->fetch()) {
			if ( $row[0] == 1 ) {
				$resultado=true;
			}
		}
		return $resultado;
	}
	function PartidaEsSurtida($idpartida) {
		global $db;
		$resultado=0;
		
		$res = $db->prepare("SELECT surtida FROM partidas WHERE activo=1 AND id=". $idpartida .";");
		$res->execute();
		while ($row = $res->fetch()) {
			if ( $row[0] == 1 ) {
				$resultado=1;
			}
		}
		return $resultado;
	}
	function AccionesPartida($idpartida) {
		global $db;
		$idrequisicion=0;
		$resultado="";
		$res = $db->prepare("SELECT idrequisicion FROM partidas WHERE id=". $idpartida .";");
		$res->execute();
		while ($row = $res->fetch()) {
			$idrequisicion=$row[0];
		}
		if ( RequisicionEsMia($idrequisicion) || usuarioEsSuper() ) {
			if ( !PartidaEsSurtida($idpartida) && PartidaEsActiva($idpartida) && RequisicionEsImpresa($idrequisicion) && RequisicionEsActiva($idrequisicion) ) {
				$resultado .= "<button onClick=\"appSurtePartida(". $idpartida .",". $idrequisicion .");\">Surtida</button>";
			}
			if ( RequisicionEsActiva($idrequisicion) && PartidaEsActiva($idpartida) && !PartidaEsSurtida($idpartida) ) {
				$resultado .= "<button onClick=\"appBorraPartida(". $idpartida .",". $idrequisicion .");\">Eliminar</button>";
			}
		}
		if ( usuarioEsSuper() ) {
			if ( RequisicionEsActiva($idrequisicion) && PartidaEsActiva($idpartida) && PartidaEsSurtida($idpartida) ) {
				$resultado .= "<button onClick=\"appPorsurtirPartida(". $idpartida .",". $idrequisicion .");\">Por surtir</button>";
			}
			if ( !PartidaEsActiva($idpartida) && RequisicionEsActiva($idrequisicion) ) {	
				$resultado .= "<button onClick=\"appRestauraPartida(". $idpartida .",". $idrequisicion .");\">Restaurar</button>";
			}
		}
		return $resultado;
	}
	function AgregarAdjuntosPartida($idpartida) {
		$resultado="";
		if ( usuarioEsLogeado() ) {
			$resultado="<input type = \"button\" value=\"Agregar\" onclick=\"addAdjuntoPart(". $idpartida .");\">";
		}else{
			$resultado="<small>Acciones</small>";
		}
		return $resultado;
	}
	function MostrarAdjuntosPartida($idpartida) {
		global $db;
		$resultado="";
		$res = $db->prepare("SELECT * FROM adjuntospartidas WHERE idpartida=". $idpartida .";");
		$res->execute();
		$resultado .= "<table id=\"tablaadjuntospart". $idpartida ."\">";
		$resultado .= "<tr><td width=\"50%\"><small>Archivo</small></td><td width=\"10%\"><small>Tama&ntilde;o</small></td><td width=\"15%\"><small>Fecha</small></td><td width=\"15%\"><small>Autor</small></td><td width=\"10%\"><small>". AgregarAdjuntosPartida($idpartida) ."</small></td></tr>";
		while ($row = $res->fetch()) {
			$rutaarchivo = "uploads/p". $idpartida ."/". $row[2];
			$resultado .= "<tr><td>". $row[2] ."</td><td>". formatBytes($row[3]) ."</td><td>". $row[5] ."</td><td>". ObtenerDescripcionDesdeID("usuarios",$row[4],"nombre") ."</td><td><button onClick=\"window.open('". $rutaarchivo ."');\">Abrir</button></td></tr>";
		}
		$resultado .= "</table>";
		return $resultado;
	}
	function AgregarComentariosPartida($idpartida) {
		$resultado="";
		if ( usuarioEsLogeado() ) {
			$resultado="<input type = \"button\" value=\"Agregar\" onclick=\"addComentarioPart(". $idpartida .");\">";
		}else{
			$resultado="<small>Acciones</small>";
		}
		return $resultado;
	}
	function ComentarioPartEsActivo($idcomentario) {
		global $db;
		$resultado=false;
		$res = $db->prepare("SELECT activo FROM comentariospartidas WHERE id=". $idcomentario .";");
		$res->execute();
		while ($row = $res->fetch()) {
			if ( $row[0] == 1 ) {
				$resultado=true;
			}
		}
		return $resultado;
	}
	function ComentarioPartEsMio($idcomentario) {
		global $db;
		$resultado=false;
		if ( usuarioEsLogeado() ) {
			$res = $db->prepare("SELECT id FROM comentariospartidas WHERE id=". $idcomentario ." AND idusuario=". $_COOKIE["usuario"] .";");
			$res->execute();
			while ($row = $res->fetch()) {
				if ( $row[0] == $idcomentario ) {
					$resultado=true;
				}
			}
		}
		return $resultado;
	}
	function AccionesComentarioPartida($idcomentario) {
		$resultado="";
		if ( usuarioEsLogeado() ) {
			if ( ComentarioPartEsMio($idcomentario) || usuarioEsSuper() ) {
				if ( comentarioPartEsActivo($idcomentario) ) {
					$resultado .= "<input type=\"button\" value=\"Eliminar\" onclick=\"deleteComentarioPart(this, ". $idcomentario .");\">";
				}
			}
			if ( ComentarioPartEsActivo($idcomentario) ) {
				$resultado .= "<input type=\"button\" value=\"Responder\" onclick=\"replyComentarioPart(". $idcomentario .");\">";
			}
			if ( !ComentarioPartEsActivo($idcomentario) && usuarioEsSuper() ) {
				$resultado .= "<input type=\"button\" value=\"Restaurar\" onclick=\"undeleteComentarioPart(this, ". $idcomentario .");\">";
			}
		}
		return $resultado;
	}
	function MostrarComentariosPartida($idpartida) {
		global $db;	
		$resultado="";
		$res = $db->prepare("SELECT * FROM comentariospartidas WHERE idpartida=". $idpartida .";");
		$res->execute();
		$resultado .= "<table id=\"tablacomentariospart". $idpartida ."\">";
		$resultado .= "<tr><td width=\"60%\"><small>Comentario</small></td><td width=\"15%\"><small>Fecha</small></td><td width=\"15%\"><small>Autor</small></td><td width=\"10%\">". AgregarComentariosPartida($idpartida) ."</td></tr>";
		while ($row = $res->fetch()) {
			$clase = "com";
			if ( ComentarioPartEsMio($row[0]) ) {
				$clase .= " comowner";
			}
			if ( !ComentarioPartEsActivo($row[0]) ) {
				$clase .= " comdeleted";
			}
			$resultado .= "<tr class=\"". $clase ."\"><td>". $row[3] ."</td><td>". $row[5] ."</td><td>". ObtenerDescripcionDesdeID("usuarios",$row[4],"nombre") ."</td><td>". AccionesComentarioPartida($row[0]) ."</td></tr>";
		}
		$resultado .= "</table>";
		return $resultado;
	}	
	
	
?>
