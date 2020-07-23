<?php
	require_once "libconfig.php";
	require_once "libdb.php";
	require_once "libphp.php";
	require_once "librequisicion.php";
	
	$uploaddir = obtenerPreferenciaGlobal("uploads","uploaddir","uploads/");
	$action = filter_input(INPUT_POST, 'accion', FILTER_SANITIZE_SPECIAL_CHARS);
	$accion = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_SPECIAL_CHARS);

	if ( $action == "agregaradjuntopart" ) {
		$idpartida = filter_input(INPUT_POST, 'partida', FILTER_SANITIZE_NUMBER_INT);
		$cntarchivoduplicado = 0;
		$rutaupload = $uploaddir ."p". $idpartida;
		if (!is_writeable($rutaupload)) {
			mkdir($rutaupload);
		}
		$nombrearchivo = $_FILES["archivo"]["name"];
		$rutatemp = $_FILES["archivo"]["tmp_name"];
		$longitudarchivo = $_FILES["archivo"]["size"];
		$rutadestino = $rutaupload ."/". $nombrearchivo;
		$nombrearchivooriginal = $nombrearchivo;
		if ( $longitudarchivo <= file_upload_max_size() ) {
			while(file_exists($rutadestino)) {
				$cntarchivoduplicado = $cntarchivoduplicado + 1;
				list($name, $ext) = explode(".", $nombrearchivooriginal);
				$nombrearchivo = $name ." (". $cntarchivoduplicado .").". $ext;
				$rutadestino = $rutaupload ."/". $nombrearchivo;
			}
			if (move_uploaded_file($rutatemp,$rutadestino)) {
				$res = $db->prepare("INSERT INTO adjuntospartidas VALUES (0, ?, ?, ?, ?,NOW(),1);");
				$res->execute([$idpartida, $nombrearchivo, $longitudarchivo, usuarioId()]);
				echo json_encode(array('succes' => 1, 'nombrearchivo' => $nombrearchivo , 'usuario' => ObtenerDescripcionDesdeID("usuarios", usuarioId() ,"nombre")));
			}
		} else {
			echo json_encode(array('succes' => 0));
		}
	}
	
	if ( $action == "saveeditpart" ) {
		$errores = array();
		$validos = array();
		$idpartida = filter_input(INPUT_POST, 'partida', FILTER_SANITIZE_NUMBER_INT);
		$cantidad = filter_input(INPUT_POST, 'cantidad', FILTER_SANITIZE_NUMBER_FLOAT);
		$unidad = filter_input(INPUT_POST, 'unidad', FILTER_SANITIZE_NUMBER_INT);
		$descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING);
		$centrocostos = filter_input(INPUT_POST, 'centrocostos', FILTER_SANITIZE_NUMBER_INT);
		if ( $cantidad <= 0 ) {
			$errores[] = "cantidad". $idpartida;
		} else {
			$validos[] = "cantidad". $idpartida;
		}
		if ( strlen($descripcion) == 0) {
			$errores[] = "descripcion". $idpartida;
		} else {
			$validos[] = "descripcion". $idpartida;
		}
		if ( count($errores) == 0 ) {
			$res = $db->prepare("UPDATE partidas SET cantidad=?, idunidad=?, descripcion=?, idcentrocostos=?, fechamodificacion=NOW(), idmodificacion=? WHERE id= ?;");
			$res->execute([$cantidad, $unidad, $descripcion, $centrocostos, usuarioId() ,$idpartida]);
			echo json_encode(array('succes' => 1));
		} else {
			echo json_encode(array('succes' => 0, 'errors' => $errores, 'validos' => $validos));
		}
	}

	if ( $action == "savepartreq" ) {
		$errores = array();
		$validos = array();
		$idrequisicion = filter_input(INPUT_POST, 'idrequisicion', FILTER_SANITIZE_NUMBER_INT);
		$cantidad = filter_input(INPUT_POST, 'cantidad', FILTER_SANITIZE_NUMBER_FLOAT);
		$unidad = filter_input(INPUT_POST, 'unidad', FILTER_SANITIZE_NUMBER_INT);
		$descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING);
		$centrocostos = filter_input(INPUT_POST, 'centrocostos', FILTER_SANITIZE_NUMBER_INT);
		$importancia = 5;
		$solicitante = usuarioId();
		if ( (float)$cantidad <= 0 ) {
			$errores[] = "cantidad". $idpartida;
		} else {
			$validos[] = "cantidad". $idpartida;
		}
		if ( strlen($descripcion) == 0) {
			$errores[] = "descripcion". $idpartida;
		} else {
			$validos[] = "descripcion". $idpartida;
		}
		if ( count($errores) == 0 ) {
			$res = $db->prepare("INSERT INTO partidas VALUES (0,NOW(), ?, ?, ?,1,0,0,NULL, NULL,NULL,NULL, NULL,NULL,NULL,NULL,?, ?, ?, ?, ?);");
			$res->execute([$cantidad, $unidad, $descripcion, $centrocostos, $idrequisicion, $importancia, $solicitante, usuarioId()]);
			echo json_encode(array('succes' => 1));
		} else {
			echo json_encode(array('succes' => 0, 'errors' => $errores, 'validos' => $validos));
		}
	}
	
	$idpartida = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
	if ( $idpartida ) {
		switch ($accion) {
			case "parttobesupplied":
				$res = $db->prepare("UPDATE partidas SET surtida=0 WHERE id= ?;");
				$res->execute([$idpartida]);
				$res = $db->prepare("UPDATE requisiciones SET surtida=0 WHERE id IN (SELECT idrequisicion FROM partidas WHERE id= ?);");
				$res->execute([$idpartida]);
				echo "OK";
				break;
			case "partsupplied":
				$res = $db->prepare("UPDATE partidas SET surtida=1, fechasurtida=NOW() WHERE id= ?;");
				$res->execute([$idpartida]);
				$res = $db->prepare("INSERT INTO notificacionespartidas VALUES (0, NOW(), 5, ?, ?,1)");
				$res->execute([$idpartida, usuarioId()]);
				echo "OK";
				break;
			case "partdelete":
				$res = $db->prepare("UPDATE partidas SET activo=0, fechaactiva=NOW() WHERE id= ?;");
				$res->execute([$idpartida]);
				$res = $db->prepare("INSERT INTO notificacionespartidas VALUES (0, NOW(), 6, ?, ?,1)");
				$res->execute([$idpartida, usuarioId()]);
				echo "OK";
				break;
			case "partundelete":
				$res = $db->prepare("UPDATE partidas SET activo=1 WHERE id= ?;");
				$res->execute([$idpartida]);
				echo "OK";
				break;
			case "editpart":
				echo formEditPartForm($idpartida);
				break;
			case "follow":
				SeguirPartida($idpartida);
				echo "OK";
				break;
			case "unfollow":
				AbandonarPartida($idpartida);
				echo "OK";
				break;
		}
	}

	function SeguirPartida($idpartida, $idusuario=0) {
		global $db;
		$idsiguiendo = 0;
		if ( $idusuario == 0) {
			$idusuario = usuarioId();
		}
		$res = $db->prepare("SELECT id FROM seguidorespartidas WHERE idpartida=? AND idusuario=?;");
		$res->execute([$idpartida, $idusuario]);
		while ($row = $res->fetch()) {
			$idsiguiendo = $row[0];
		}
		if ( $idsiguiendo == 0 ) {
			$res = $db->prepare("INSERT INTO seguidorespartidas VALUES (0,?,?,1);");
			$res->execute([$idpartida, $idusuario]);
		} else {
			$res = $db->prepare("UPDATE seguidorespartidas SET activo=1 WHERE id=?;");
			$res->execute([$idsiguiendo]);
		}
	}
	
	function AbandonarPartida($idpartida) {
		global $db;
		$res = $db->prepare("UPDATE seguidorespartidas SET activo=0 WHERE idpartida=? AND idusuario=?;");
		$res->execute([$idpartida, usuarioId() ]);
	}

	function PartidaEsActiva($idpartida) {
		global $db;
		$resultado = false;
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
		$resultado = 0;
		
		$res = $db->prepare("SELECT surtida FROM partidas WHERE activo=1 AND id=". $idpartida .";");
		$res->execute();
		while ($row = $res->fetch()) {
			if ( $row[0] == 1 ) {
				$resultado=1;
			}
		}
		return $resultado;
	}

	function soySeguidorPartida($idpartida) {
		global $db;
		$resultado = false;
		if ( usuarioEsLogeado() ) {
			$res = $db->prepare("SELECT id FROM seguidorespartidas WHERE idpartida=? AND idusuario=? AND activo=1;");
			$res->execute([$idpartida, usuarioId()]);
			if ( $res->rowCount() ) {
				$resultado = true;
			}
		}
		return $resultado;
	}

	function AccionesPartida($idpartida) {
		global $db;
		$idrequisicion = 0;
		$resultado = "";
		$res = $db->prepare("SELECT idrequisicion FROM partidas WHERE id=". $idpartida .";");
		$res->execute();
		while ($row = $res->fetch()) {
			$idrequisicion = $row[0];
		}
		if ( RequisicionEsMia($idrequisicion) || usuarioEsSuper() ) {
			if ( !PartidaEsSurtida($idpartida) && PartidaEsActiva($idpartida) && RequisicionEsImpresa($idrequisicion) && RequisicionEsActiva($idrequisicion) ) {
				$resultado .= "<button onClick=\"appSurtePartida(". $idpartida .",". $idrequisicion .");\">Surtida</button>";
			}
			if ( RequisicionEsActiva($idrequisicion) && PartidaEsActiva($idpartida) && !PartidaEsSurtida($idpartida) ) {
				$resultado .= "<button onClick=\"appBorraPartida(". $idpartida .",". $idrequisicion .");\">Eliminar</button>";
			}
			if ( (!RequisicionEsImpresa($idrequisicion) || usuarioEsSuper()) && PartidaEsActiva($idpartida) ) {
				$resultado .= "<button onClick=\"appEditPart(this,". $idpartida .",". $idrequisicion .");\">Editar</button>";
			}
			if ( (!RequisicionEsImpresa($idrequisicion) || usuarioEsSuper()) && !PartidaEsActiva($idpartida) && RequisicionEsActiva($idrequisicion) ) {
				$resultado .= "<button onClick=\"appRestauraPartida(". $idpartida .",". $idrequisicion .");\">Restaurar</button>";
			}
		}
		if ( usuarioEsLogeado() ) {
			if ( soySeguidorPartida($idpartida) ) {
				$resultado .= '<button onClick="appAbandonarPartida('. $idpartida .','. $idrequisicion .');">Abandonar</button>';
			}
		}
		if ( usuarioEsLogeado() && RequisicionEsActiva($idrequisicion) && PartidaEsActiva($idpartida) ) {
			if ( !soySeguidorPartida($idpartida) && !RequisicionEsMia($idrequisicion) ) {
				$resultado .= '<button onClick="appSeguirPartida('. $idpartida .','. $idrequisicion .');">Seguir</button>';
			}
		}
		if ( usuarioEsSuper() ) {
			if ( RequisicionEsActiva($idrequisicion) && PartidaEsActiva($idpartida) && PartidaEsSurtida($idpartida) ) {
				$resultado .= "<button onClick=\"appPorSurtirPartida(". $idpartida .",". $idrequisicion .");\">Por surtir</button>";
			}
		}
		return $resultado;
	}

	function AgregarAdjuntosPartida($idpartida) {
		$resultado = "";
		if ( usuarioEsLogeado() ) {
			$resultado="<input type = \"button\" value=\"Agregar\" onclick=\"addAdjuntoPart(". $idpartida .");\">";
		}else{
			$resultado="<small>Acciones</small>";
		}
		return $resultado;
	}

	function MostrarAdjuntosPartida($idpartida,$q) {
		global $db;
		$resultado = "";
		$res = $db->prepare("SELECT * FROM adjuntospartidas WHERE idpartida=". $idpartida .";");
		$res->execute();
		$resultado .= "<table id=\"tablaadjuntospart". $idpartida ."\">";
		$resultado .= "<tr><td width=\"50%\"><small>Archivo</small></td><td width=\"10%\"><small>Tama&ntilde;o</small></td><td width=\"15%\"><small>Fecha</small></td><td width=\"15%\"><small>Autor</small></td><td width=\"10%\"><small>". AgregarAdjuntosPartida($idpartida) ."</small></td></tr>";
		while ($row = $res->fetch()) {
			$rutaarchivo = "uploads/p". $idpartida ."/". $row[2];
			$resultado .= "<tr><td>". resaltarBusqueda($row[2], $q) ."</td><td>". formatBytes($row[3]) ."</td><td>". $row[5] ."</td><td>". ObtenerDescripcionDesdeID("usuarios",$row[4],"nombre") ."</td><td><button onClick=\"window.open('". $rutaarchivo ."');\">Abrir</button></td></tr>";
		}
		$resultado .= "</table>";
		return $resultado;
	}

	function AgregarComentariosPartida($idpartida) {
		$resultado = "";
		if ( usuarioEsLogeado() ) {
			$resultado = "<input type = \"button\" value=\"Agregar\" onclick=\"addComentarioPart(". $idpartida .");\">";
		}else{
			$resultado = "<small>Acciones</small>";
		}
		return $resultado;
	}

	function ComentarioPartEsActivo($idcomentario) {
		global $db;
		$resultado = false;
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
		$resultado = false;
		if ( usuarioEsLogeado() ) {
			$res = $db->prepare("SELECT id FROM comentariospartidas WHERE id=". $idcomentario ." AND idusuario=". usuarioId() .";");
			$res->execute();
			while ($row = $res->fetch()) {
				if ( $row[0] == $idcomentario ) {
					$resultado = true;
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
			if ( !ComentarioPartEsActivo($idcomentario) && usuarioEsSuper() ) {
				$resultado .= "<input type=\"button\" value=\"Restaurar\" onclick=\"undeleteComentarioPart(this, ". $idcomentario .");\">";
			}
		}
		return $resultado;
	}

	function UltimoComentarioPartida($idpartida) {
		global $db;	
		$resultado = "";
		$res = $db->prepare("SELECT comentario FROM comentariospartidas WHERE idpartida=". $idpartida .";");
		$res->execute();
		while ($row = $res->fetch()) {
			$resultado = $row[0];
		}
		return $resultado;
	}

	function MostrarComentariosPartida($idpartida,$q) {
		global $db;	
		$resultado = "";
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
			$resultado .= "<tr class=\"". $clase ."\"><td>". resaltarBusqueda($row[3], $q) ."</td><td>". $row[5] ."</td><td>". ObtenerDescripcionDesdeID("usuarios",$row[4],"nombre") ."</td><td>". AccionesComentarioPartida($row[0]) ."</td></tr>";
		}
		$resultado .= "</table>";
		return $resultado;
	}
	
	function formEditPartForm($idpartida) {
		global $db;
		$resultado = "";
		$res = $db->prepare("SELECT cantidad, idunidad, descripcion, idcentrocostos FROM partidas WHERE id=?;");
		$res->execute([$idpartida]);
		while ($row = $res->fetch()) {
			$cantidad = $row[0];
			$unidad = $row[1];
			$descripcion = $row[2];
			$centrocostos = $row[3];
		}
		$resultado .= "";
		$resultado .= "<table>";
		$resultado .= "<tr>";
		$resultado .= "<td width=\"10%\"><small>Cantidad</small></td>";
		$resultado .= "<td width=\"10%\"><small>Unidad</small></td>";
		$resultado .= "<td width=\"65%\"><small>Descripcion</small></td>";
		$resultado .= "<td width=\"15%\"><small>CentroCostos</small></td>";
		$resultado .= "</tr>";
		$resultado .= "<tr>";
		$resultado .= "<td><input id = 'cantidad". $idpartida ."' type = 'number' min='0' step='0.001' name = 'cantidad[". $idpartida ."]' value=\"". $cantidad ."\"/></td>";
		$resultado .= "<td><select id = 'unidad". $idpartida ."' name = 'unidad[". $idpartida ."]'>". ObtenerOpcionesSelect("unidades", "unidad", $unidad) ."</select></td>";
		$resultado .= "<td><input id = 'descripcion". $idpartida ."' type = 'text' name = 'descripcion[". $idpartida ."]' value = '". $descripcion ."' /></td>";
		$resultado .= "<td><select id = 'centrocostos". $idpartida ."' name = 'centrocostos[". $idpartida ."]'>". ObtenerOpcionesSelectGroup("centroscostos","descripcion","empresas","idempresa", $centrocostos) ."</select></td>";
		$resultado .= "</tr>";
		$resultado .= "</table>";
		return $resultado;
	}
?>
