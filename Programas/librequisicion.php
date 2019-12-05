<?php
	require_once "libconfig.php";
	require_once "libdb.php";
	require_once "libphp.php";
	require_once "libpartida.php";
	require_once "libpdf.php";
	require_once "libuser.php";
	require_once "libsettings.php";
	
	if ( isset($_REQUEST["posted"]) ) {
		$errores=array();
		$validos=array();
		$empresa=$_REQUEST["empresa"];;
		$departamento=$_REQUEST["departamento"];
		$area=$_REQUEST["area"];
		$solicitante=$_REQUEST["solicitante"];
		$centrocostosreq = $_REQUEST["centrocostosreq"];
		$importancia = 5;
		$uploaddir="uploads/";
		if ( !usuarioEsLogeado() ) {
			$errores[]="newreqform";
		}
		if ( isset($_REQUEST["totalpartidas"]) ) {
			foreach( $_REQUEST["totalpartidas"] as $item) {
				if ( isset($_REQUEST["cantidad"][$item])) {
					$cantidad = $_REQUEST["cantidad"][$item];
				}else{
					$cantidad = 0;
				}
				if ( isset($_REQUEST["unidad"][$item]) ) {
					$unidad = $_REQUEST["unidad"][$item];
				}else{
					$unidad=0;
				}
				if ( isset($_REQUEST["descripcion"][$item]) ) {
					$descripcion = $_REQUEST["descripcion"][$item];
				}else{
					$descripcion="";
				}
				if ( isset($_REQUEST["centrocostos"][$item]) ) {
					$centrocostos = $_REQUEST["centrocostos"][$item];
				}else{
					$centrocostos=0;
				}
				if ( (float)$cantidad <= 0 ) {
					$errores[] = "cantidad". $item;
				} else {
					$validos[] = "cantidad". $item;
				}
				if ( $unidad == 0) {
					$errores[] = "unidad". $item;
				} else {
					$validos[] = "unidad". $item;
				}
				if ( strlen($descripcion) == 0) {
					$errores[] = "descripcion". $item;
				} else {
					$validos[] = "descripcion". $item;
				}
				if ( $centrocostos == 0) {
					$errores[] = "centrocostos". $item;
				} else {
					$validos[] = "centrocostos". $item;
				}
			}
		}else{
			$errores[]="newreqform";
		}
		if ( count($errores) == 0 ) {
			$res = $db->prepare("INSERT INTO requisiciones VALUES (0,NOW(),'',1,0,0,NULL,NULL,NULL,NULL, NOW(),NULL,NULL,NULL,NULL, ?, ?, ?, ?, ?, ?, ?);");
			$res->execute([$empresa, $departamento, $area, $centrocostosreq, $importancia, $solicitante, usuarioId()]);
			$ultimoidreq = $db->lastInsertId();
			if ( isset($_REQUEST["totalpartidas"]) ) {
				foreach( $_REQUEST["totalpartidas"] as $item) {
					$cantidad = $_REQUEST["cantidad"][$item];
					$unidad = $_REQUEST["unidad"][$item];
					$descripcion = $_REQUEST["descripcion"][$item];
					$centrocostos = $_REQUEST["centrocostos"][$item];
					$importancia= 5;
					$res = $db->prepare("INSERT INTO partidas VALUES (0,NOW(), ?, ?, ?,1,0,0,NULL, NULL,NULL,NULL, NULL,NULL,NULL,NULL,?, ?, ?, ?, ?);");
					$res->execute([$cantidad, $unidad, $descripcion, $centrocostos, $ultimoidreq, $importancia, $solicitante, usuarioId()]);
					$ultimoidpart = $db->lastInsertId();
					if ( isset($_REQUEST["partcomentarios"]) ) {
						foreach ( $_REQUEST["partcomentarios"]["tablacomentarios". $item ] as $elemento ) {
							if ( strlen($elemento) > 0 ) {
								$res = $db->prepare("INSERT INTO comentariospartidas VALUES (0, ?,0, ?, ?,NOW(),1);");
								$res->execute([$ultimoidpart, $elemento, usuarioId()]);
							}
						}
					}
					if ( isset($_REQUEST["totalpartadjuntos"]) ) {
						foreach ( $_REQUEST["totalpartadjuntos"]["tablaadjuntos". $item ] as $elemento ) {
							$rutaupload=$uploaddir ."p". $ultimoidpart;
							if (!is_writeable($rutaupload)) {
								mkdir($rutaupload);
							}
							$nombrearchivo=$_FILES["partadjuntostablaadjuntos". $item]["name"][$elemento];
							$rutatemp=$_FILES["partadjuntostablaadjuntos". $item]["tmp_name"][$elemento];
							$longitudarchivo=$_FILES["partadjuntostablaadjuntos". $item]["size"][$elemento];
							$rutadestino=$rutaupload ."/". $nombrearchivo;
							if (move_uploaded_file($rutatemp,$rutadestino)) {
								$res = $db->prepare("INSERT INTO adjuntospartidas VALUES (0, ?, ?, ?, ?,NOW(),1);");
								$res->execute([$ultimoidpart, $nombrearchivo, $longitudarchivo, usuarioId()]);
							}
						}
					}
				}
			}
			if ( isset($_REQUEST["totalreqcomentarios"]) ) {
				foreach( $_REQUEST["totalreqcomentarios"] as $item) {
					$comentario = $_REQUEST["reqcomentarios"][$item];
					if ( strlen($comentario) > 0 ) {
						$res = $db->prepare("INSERT INTO comentariosrequisiciones VALUES (0, ?,0, ?, ?,NOW(),1);");
						$res->execute([$ultimoidreq, $comentario, usuarioId()]);
					}
				}
			}
			if ( isset($_REQUEST["totalreqadjuntos"]) ) {
				foreach( $_REQUEST["totalreqadjuntos"] as $item) {
					$rutaupload=$uploaddir ."r". $ultimoidreq;
					if (!is_writeable($rutaupload)) {
						mkdir($rutaupload);
					}
					$nombrearchivo = $_FILES["reqadjuntos"]["name"][$item];
					$rutatemp = $_FILES["reqadjuntos"]["tmp_name"][$item];
					$longitudarchivo = $_FILES["reqadjuntos"]["size"][$item];
					$rutadestino = $rutaupload ."/". $nombrearchivo;
					if (move_uploaded_file($rutatemp,$rutadestino)) {
						$res = $db->prepare("INSERT INTO adjuntosrequisiciones VALUES (0, ?, ?, ?, ?,NOW(),1);");
						$res->execute([$ultimoidreq, $nombrearchivo, $longitudarchivo, usuarioId()]);
					}
				}
			}
			echo json_encode(array('succes' => 1, 'id' => $ultimoidreq));
		}else{
			echo json_encode(array('succes' => 0, 'errors' => $errores, 'validos' => $validos));
		}
	}
	
	if ( isset($_REQUEST["includeusers"]) ) {
		$idrequisicion = intval($_REQUEST["idreq"]);
		foreach( $_REQUEST["user"] as $idusuario) {
			SeguirRequisicion($idrequisicion, $idusuario);
		}
		echo "OK";
	}

	if ( isset($_POST["accion"]) && $_POST["accion"] == "agregaradjuntoreq" ) {
		$idrequisicion=$_POST["requisicion"];
		$cntarchivoduplicado=0;
		$uploaddir="uploads/";
		$rutaupload=$uploaddir ."r". $idrequisicion;
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
			$res = $db->prepare("INSERT INTO adjuntosrequisiciones VALUES (0, ?, ?, ?, ?,NOW(),1);");
			$res->execute([$idrequisicion, $nombrearchivo, $longitudarchivo, usuarioId()]);
			echo "OK";
		}
	}

	if ( isset($_GET["action"]) && $_GET["action"] == "showreqform" ) {
		$resultado="";
		if ( usuarioEsLogeado() ) {
			$resultado = formNewReqForm();
		}else{
			$resultado = formLoginForm();
		}
		echo $resultado;
	}

	if ( isset($_GET["action"]) && $_GET["action"] == "showincludeuserform" ) {
		$idrequisicion = intval($_GET["idreq"]);
		echo formIncludeUserInReqForm($idrequisicion);
	}

	function ListaRequisiciones($view=0, $user=-1, $q="") {
		global $db;
		$usuario="";
		$vista="";
		$resultado="";
		$tablatemp="temp". randomString(4);
			switch ($view) {
				case "0":
					$vista = " (activo=1 AND impresa=1 AND surtida=0)";
					break;
				case "1":
					$vista = " (activo=1 AND impresa=1 AND surtida=1)";
					break;
				case "2":
					$vista = " (activo=1 AND impresa=0)";
					break;
				case "3":
					$vista = " (activo=1 AND impresa=1)";
					break;
				case "4":
					$vista = " (activo=0)";
					break;
				case "5":
					$vista = " (id>0)";
					break;
			}
			if ( $user > 0 ) {
				$usuario=" AND (idsolicitante=". $user ." OR idusuario=". $user .")";
			}
			if ( strlen($q) == 0 ) {
				$sql="SELECT DISTINCT(id) FROM requisiciones WHERE". $vista . $usuario ." ORDER BY id;"; 
			}else{
				$res = $db->prepare("DROP TABLE IF EXISTS ". $tablatemp .";");
				$res->execute();
				$res = $db->prepare("CREATE TABLE ". $tablatemp ." (id INT UNSIGNED) ENGINE MEMORY DEFAULT CHARSET utf8;");
				$res->execute();
				if ( is_numeric($q) ){
					$res = $db->prepare("INSERT INTO ". $tablatemp ." (SELECT id FROM requisiciones WHERE id=". $q .");");
					$res->execute();
				}
				$res = $db->prepare("INSERT INTO ". $tablatemp ." (SELECT id FROM requisiciones WHERE requisicion LIKE '%". $q ."%');");
				$res->execute();
				$res = $db->prepare("INSERT INTO ". $tablatemp ." (SELECT idrequisicion AS id FROM partidas WHERE descripcion LIKE '%". $q ."%');");
				$res->execute();
				$res = $db->prepare("INSERT INTO ". $tablatemp ." (SELECT idrequisicion AS id FROM comentariosrequisiciones WHERE comentario LIKE '%". $q ."%');");
				$res->execute();
				$res = $db->prepare("INSERT INTO ". $tablatemp ." (SELECT idrequisicion AS id FROM adjuntosrequisiciones WHERE nombre LIKE '%". $q ."%');");
				$res->execute();
				$res = $db->prepare("INSERT INTO ". $tablatemp ." (SELECT partidas.idrequisicion AS id FROM partidas, comentariospartidas WHERE partidas.id=comentariospartidas.idpartida AND comentariospartidas.comentario LIKE '%". $q ."%');");
				$res->execute();
				$res = $db->prepare("INSERT INTO ". $tablatemp ." (SELECT partidas.idrequisicion AS id FROM partidas, adjuntospartidas WHERE partidas.id=adjuntospartidas.idpartida AND adjuntospartidas.nombre LIKE '%". $q ."%');");
				$res->execute();
				$sql="SELECT DISTINCT(id) FROM requisiciones INNER JOIN ". $tablatemp ." USING(id) WHERE". $vista . $usuario ." ORDER BY id;";
			}
			$res = $db->prepare($sql);
			$res->execute();
			while ($row = $res->fetch()) {
				$resultado = $resultado ." ". $row[0];
			}
			$res = $db->prepare("DROP TABLE IF EXISTS ". $tablatemp .";");
			$res->execute();
			$resultado = trim($resultado, " ");
		return $resultado;
	}

	if ( isset($_GET["action"]) && $_GET["action"] == "list" ) {
		if ( isset($_GET["view"]) ){
			$view=$_GET["view"];
		}else{
			$view=0;
		}
		if ( isset($_GET["user"]) ){
			$user=$_GET["user"];
		}else{
			$user=0;
		}
		if ( isset($_GET["q"]) ){
			$q=$_GET["q"];
		}else{
			$q="";
		}
		$resultado=ListaRequisiciones($view, $user, $q);
		echo $resultado;
	}

	if ( isset($_GET["action"]) && $_GET["action"] == "export" ) {
		$resultado="";
		if ( isset($_GET["view"]) ){
			$view=$_GET["view"];
		}else{
			$view=0;
		}
		if ( isset($_GET["user"]) ){
			$user=$_GET["user"];
		}else{
			$user=0;
		}
		if ( isset($_GET["q"]) ){
			$q=$_GET["q"];
		}else{
			$q="";
		}
		$resultado=ListaRequisiciones($view, $user, $q);
		$pdf = new PDF("L");
		ExportarEncabezados($pdf);
		$listarequisiciones= explode(" ",$resultado);
		foreach ($listarequisiciones as $idrequisicion) {
			ExportarRequisicion($pdf, $idrequisicion);
		}
		$pdf->Output();
	}

	if ( isset($_GET["id"]) ) {
		$idrequisicion=$_GET["id"];
		if ( isset($_GET["q"]) ){
			$q=$_GET["q"];
		}else{
			$q="";
		}
		switch ($_GET["action"]) {
			case "saveprinted":
				if ( isset($_GET["reqno"]) ) {
					$req = $_GET["reqno"];
					$res = $db->prepare("UPDATE requisiciones SET requisicion='". $req ."' WHERE id=". $idrequisicion .";");
					$res->execute();
				}
				echo "OK";
				break;
			case "show":
				$resultado = "";
				$resultado .= MostrarMarcoRequisicion($idrequisicion, $q);
				echo $resultado;
				break;
			case "copy":
				$resultado = "";
				$resultado .= CopiarRequisicion($idrequisicion);
				echo $resultado;
				break;
			case "print":
				$pdf = new PDF('P', 'mm', 'Letter');
				$pdf->AddPage();
				ImprimirRequisicion($pdf, $idrequisicion);
				$pdf->Output();
				$res = $db->prepare("UPDATE requisiciones SET impresa=1, fecha=NOW(), fechaimpresa=NOW(), idimpresa=". usuarioId() ." WHERE id=". $idrequisicion .";");
				$res->execute();
				$res = $db->prepare("INSERT INTO notificacionesrequisiciones VALUES (0, NOW(), 4, ". $idrequisicion .", ". usuarioId() .", 1);");
				$res->execute();
				break;
			case "testprint":
				$pdf = new PDF('P', 'mm', 'Letter');
				$pdf->AddPage();
				ImprimirRequisicion($pdf, $idrequisicion, 1);
				$pdf->Output();
				break;
			case "tobesupplied":
				$res = $db->prepare("UPDATE partidas SET surtida=0 WHERE activo=1 AND idrequisicion=". $idrequisicion .";");
				$res->execute();
				$res = $db->prepare("UPDATE requisiciones SET surtida=0 WHERE id=". $idrequisicion .";");
				$res->execute();
				echo "OK";
				break;
			case "supplied":
				$res = $db->prepare("UPDATE partidas SET surtida=1, fechasurtida=NOW(), idsurtida=". usuarioId() ." WHERE activo=1 AND surtida=0 AND idrequisicion=". $idrequisicion .";");
				$res->execute();
				$res = $db->prepare("UPDATE requisiciones SET surtida=1, fechasurtida=NOW(), idsurtida=". usuarioId() ." WHERE id=". $idrequisicion .";");
				$res->execute();
				$res = $db->prepare("INSERT INTO notificacionesrequisiciones VALUES (0, NOW(), 5, ". $idrequisicion .", ". usuarioId() .", 1);");
				$res->execute();
				echo "OK";
				break;
			case "delete":
				$res = $db->prepare("UPDATE partidas SET activo=0, fechaactiva=NOW(), idactiva=". usuarioId() ." WHERE surtida=0 AND activo=1 AND idrequisicion=". $idrequisicion .";");
				$res->execute();
				$res = $db->prepare("UPDATE requisiciones SET activo=0, fechaactiva=NOW(), idactiva=". usuarioId() ." WHERE id=". $idrequisicion .";");
				$res->execute();
				$res = $db->prepare("INSERT INTO notificacionesrequisiciones VALUES (0, NOW(), 6, ". $idrequisicion .", ". usuarioId() .", 1);");
				$res->execute();
				echo "OK";
				break;
			case "undelete":
				$res = $db->prepare("UPDATE requisiciones SET activo=1 WHERE id=". $idrequisicion .";");
				$res->execute();
				echo "OK";
				break;
			case "follow":
				SeguirRequisicion($idrequisicion);
				echo "OK";
				break;
			case "unfollow":
				AbandonarRequisicion($idrequisicion);
				echo "OK";
				break;
		}
	}

	function SeguirRequisicion($idrequisicion, $idusuario=0) {
		global $db;
		$idsiguiendo = 0;
		if ( $idusuario == 0) {
			$idusuario = usuarioId();
		}
		$res= $db->prepare("SELECT id FROM seguidoresrequisiciones WHERE idrequisicion=? AND idusuario=?;");
		$res->execute([$idrequisicion, $idusuario]);
		while ($row = $res->fetch()) {
			$idsiguiendo = $row[0];
		}
		if ( $idsiguiendo == 0 ) {
			$res= $db->prepare("INSERT INTO seguidoresrequisiciones VALUES (0,?,?,1);");
			$res->execute([$idrequisicion, $idusuario]);
		} else {
			$res= $db->prepare("UPDATE seguidoresrequisiciones SET activo=1 WHERE id=?;");
			$res->execute([$idsiguiendo]);
		}
	}
	
	function AbandonarRequisicion($idrequisicion) {
		global $db;
		$res= $db->prepare("UPDATE seguidoresrequisiciones SET activo=0 WHERE idrequisicion=? AND idusuario=?;");
		$res->execute([$idrequisicion, usuarioId() ]);
	}

	function CopiarRequisicion($idrequisicion) {
		global $db;
		$uploaddir="uploads/";
		$res= $db->prepare("SELECT * FROM requisiciones WHERE id=". $idrequisicion);
		$res->execute();
		while ($row = $res->fetch()) {
			$requisicion="";
			$empresa=$row[15];
			$departamento=$row[16];
			$area=$row[17];
			$solicitante=$row[20];
			$centrocostos = $row[18];
			$importancia = $row[19];
		}
		$iter=0;
		$res= $db->prepare("SELECT * FROM partidas WHERE activo=1 AND idrequisicion=". $idrequisicion);
		$res->execute();
		while ($row = $res->fetch()) {
			$totalpartidas[]=$iter;
			$cantidad[$iter] = $row[2];
			$unidad[$iter] = $row[3];
			$descripcion[$iter] = $row[4];
			$centrocostospart[$iter] = $row[16];
			$importanciapart[$iter] = $row[18];
			$solicitantepart[$iter] = $row[19];
			$iter2=0;
			$res2= $db->prepare("SELECT * FROM comentariospartidas WHERE activo=1 AND idpartida=". $row[0]);
			$res2->execute();
			while ($row2 = $res2->fetch()) {
				$totalpartcometarios[$iter][$iter2]=$iter2;
				$partcomentario[$iter][$iter2]=$row2[3];
				$partcomentarioautor[$iter][$iter2]=$row2[4];
				$partcomentariofecha[$iter][$iter2]=$row2[5];
				$iter2=$iter2+1;
			}
			$iter2=0;
			$res2= $db->prepare("SELECT * FROM adjuntospartidas WHERE activo=1 AND idpartida=". $row[0]);
			$res2->execute();
			while ($row2 = $res2->fetch()) {
				$totalpartadjuntos[$iter][$iter2]=$iter2;
				$partadjuntoid[$iter][$iter2]=$row[0];
				$partadjunto[$iter][$iter2]=$row2[2];
				$partadjuntolongitud[$iter][$iter2]=$row2[3];
				$partadjuntoautor[$iter][$iter2]=$row2[4];
				$partadjuntofecha[$iter][$iter2]=$row2[5];
				$iter2=$iter2+1;
			}
			$iter=$iter+1;
		}
		$res = $db->prepare("INSERT INTO requisiciones VALUES (0,NOW(), ?,1,0,0,NULL, NULL,NULL,NULL,NOW(),NULL,NULL,NULL,NULL, ?, ?, ?, ?, ?, ?, ?);");
		$res->execute([$requisicion, $empresa, $departamento, $area, $centrocostos, $importancia, $solicitante, usuarioId()]);
		$ultimoidreq = $db->lastInsertId();
		foreach( $totalpartidas as $item) {
			$res = $db->prepare("INSERT INTO partidas VALUES (0,NOW(), ?, ?, ?,1,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL, ?, ?, ?, ?, ?);");
			$res->execute([$cantidad[$item], $unidad[$item], $descripcion[$item], $centrocostospart[$item], $ultimoidreq, $importanciapart[$item], $solicitantepart[$item], usuarioId()]);
			$ultimoidpart = $db->lastInsertId();
			if ( isset($totalpartcometarios) ) {
				foreach ( $totalpartcometarios[$item] as $item2 ) {
					$comentario=$partcomentario[$item][$item2];
					$comentarioautor=$partcomentarioautor[$item][$item2];
					$comentariofecha=$partcomentariofecha[$item][$item2];
					$res = $db->prepare("INSERT INTO comentariospartidas VALUES (0, ?,0, ?, ?, ?,1);");
					$res->execute([$ultimoidpart, $comentario, $comentarioautor, $comentariofecha]);
				}
			}
			if ( isset($totalpartadjuntos) ) {
				foreach ( $totalpartadjuntos[$item] as $item2 ) {
					$rutaupload=$uploaddir ."p". $ultimoidpart;
					if (!is_writeable($rutaupload)) {
						mkdir($rutaupload);
					}
					$rutaorigen=$uploaddir ."p". $partadjuntoid[$item][$item2] ."/". $partadjunto[$item][$item2];
					$rutadestino=$uploaddir ."p". $ultimoidpart ."/". $partadjunto[$item][$item2];
					if ( copy($rutaorigen,$rutadestino) ) {
						$res = $db->prepare("INSERT INTO adjuntospartidas VALUES (0, ?, ?, ?, ?, ?,1);");
						$res->execute([$ultimoidpart, $partadjunto[$item][$item2], $partadjuntolongitud[$item][$item2], $partadjuntoautor[$item][$item2], $partadjuntofecha[$item][$item2]]);
					}
				}
			}
		}
		$iter=0;
		$res= $db->prepare("SELECT * FROM comentariosrequisiciones WHERE activo=1 AND idrequisicion=". $idrequisicion);
		$res->execute();
		while ($row = $res->fetch()) {
			$totalreqcomentarios[]=$iter;
			$comentarioreq[$iter]=$row[3];
			$comentarioreqautor[$iter]=$row[4];
			$comentarioreqfecha[$iter]=$row[5];
			$iter=$iter+1;
		}
		if ( isset($totalreqcomentarios) ) {
			foreach( $totalreqcomentarios as $item) {
				$res = $db->prepare("INSERT INTO comentariosrequisiciones VALUES (0, ?,0, ?, ?, ?,1);");
				$res->execute([$ultimoidreq, $comentarioreq[$item], $comentarioreqautor[$item], $comentarioreqfecha[$item]]);
			}
		}
		$iter=0;
		$res= $db->prepare("SELECT * FROM adjuntosrequisiciones WHERE activo=1 AND idrequisicion=". $idrequisicion);
		$res->execute();
		while ($row = $res->fetch()) {
			$totalreqadjuntos[]=$iter;
			$nombrearchivo[$iter]=$row[2];
			$longitudarchivo[$iter]=$row[3];
			$autor[$iter]=$row[4];
			$fecha[$iter]=$row[5];
			$iter=$iter+1;
		}
		foreach( $totalreqadjuntos as $item) {
			$rutaupload=$uploaddir ."r". $ultimoidreq;
			if (!is_writeable($rutaupload)) {
				mkdir($rutaupload);
			}
			$rutaorigen=$uploaddir ."r". $idrequisicion ."/". $nombrearchivo[$item];
			$rutadestino=$uploaddir ."r". $ultimoidreq ."/". $nombrearchivo[$item];
			if ( copy($rutaorigen,$rutadestino) ) {
				$res = $db->prepare("INSERT INTO adjuntosrequisiciones VALUES (0, ?, ?, ?, ?, ?,1);");
				$res->execute([$ultimoidreq, $nombrearchivo[$item], $longitudarchivo[$item], $autor[$item], $fecha[$item]]);
			}
		}
		$comentario = 'Copia de la requisicion Id='. $idrequisicion ;
		$res = $db->prepare("INSERT INTO comentariosrequisiciones VALUES (0, ?,0, ?, ?,NOW(), 0);");
		$res->execute([$ultimoidreq, $comentario, usuarioId()]);
		return json_encode(array('succes' => 1, 'id' => $ultimoidreq));
	}

	function ExportarRequisicion($pdf, $idrequisicion) {
		global $db;
		$req="";
		$fecha="";
		$solicitante="";
		$estado="";
		$res = $db->prepare("SELECT * FROM requisiciones WHERE id = ". $idrequisicion .";");
		$res->execute();
		while ($row = $res->fetch()) {
			$fecha=$row[1];
			$req=$row[2];
			$empresa = ObtenerDescripcionDesdeID("empresas", $row[15], "nombre");
			$solicitante=ObtenerDescripcionDesdeID("usuarios", $row[20], "nombre");
		}
		$y=$pdf->GetY();
		$pdf->SetLineWidth(0.40);
		$pdf->Line(2,$y-4,292,$y-4);
		$res = $db->prepare("SELECT * FROM partidas WHERE idrequisicion = ". $idrequisicion .";");
		$res->execute();
		while ($row = $res->fetch()) {
			$estado="";
			$y=$pdf->GetY();
			$fontsize = obtenerPreferenciaGlobal('reporte', 'FormatoDescripcionFontSize', '8');
			$fontname = obtenerPreferenciaGlobal('reporte', 'FormatoDescripcionFontName', 'Arial');
			$ancho = obtenerPreferenciaGlobal('reporte', 'FormatoDescripcionAncho', '69');
			$pdf->SetFont($fontname,'', $fontsize);
			$alto = $pdf->MeassureRows($row[4] , $ancho,5);
			if ( $y + $alto > 210 ) {
				ExportarEncabezados($pdf);
				$y=$pdf->GetY();
			}
			$pdf->SetLineWidth(0.20);
			$pdf->Line(4,$y-4,290,$y-4);
			if ( strval($row[6]) == 1 ) {
				$estado .= "Surtida";
			}
			if ( strval($row[5]) == 0 ) {
				$estado .= "Eliminada";
			}
			$visible = obtenerPreferenciaGlobal('reporte', 'FormatoIdVisible', '1');
			if ( $visible == "1" ) {
				$fontsize = obtenerPreferenciaGlobal('reporte', 'FormatoIdFontSize', '8');
				$fontname = obtenerPreferenciaGlobal('reporte', 'FormatoIdFontName', 'Arial');
				$x = obtenerPreferenciaGlobal('reporte', 'FormatoIdX', '5');
				$ancho = obtenerPreferenciaGlobal('reporte', 'FormatoIdAncho', '14');
				$pdf->SetFont($fontname,'', $fontsize);
				$pdf->PutRows($x, $y, $idrequisicion , $ancho);
			}
			$visible = obtenerPreferenciaGlobal('reporte', 'FormatoFechaVisible', '1');
			if ( $visible == "1" ) {
				$fontsize = obtenerPreferenciaGlobal('reporte', 'FormatoFechaFontSize', '8');
				$fontname = obtenerPreferenciaGlobal('reporte', 'FormatoFechaFontName', 'Arial');
				$x = obtenerPreferenciaGlobal('reporte', 'FormatoFechaX', '20');
				$ancho = obtenerPreferenciaGlobal('reporte', 'FormatoFechaAncho', '29');
				$pdf->SetFont($fontname,'', $fontsize);
				$pdf->PutRows($x, $y, $fecha, $ancho);
			}

			$visible = obtenerPreferenciaGlobal('reporte', 'FormatoEmpresaVisible', '1');
			if ( $visible == "1" ) {
				$fontsize = obtenerPreferenciaGlobal('reporte', 'FormatoEmpresaFontSize', '8');
				$fontname = obtenerPreferenciaGlobal('reporte', 'FormatoEmpresaFontName', 'Arial');
				$x = obtenerPreferenciaGlobal('reporte', 'FormatoEmpresaX', '20');
				$ancho = obtenerPreferenciaGlobal('reporte', 'FormatoEmpresaAncho', '29');
				$pdf->SetFont($fontname,'', $fontsize);
				$pdf->PutRows($x, $y, $empresa, $ancho);
			}
			$visible = obtenerPreferenciaGlobal('reporte', 'FormatoRequisicionVisible', '1');
			if ( $visible == "1" ) {
				$fontsize = obtenerPreferenciaGlobal('reporte', 'FormatoRequisicionFontSize', '8');
				$fontname = obtenerPreferenciaGlobal('reporte', 'FormatoRequisicionFontName', 'Arial');
				$x = obtenerPreferenciaGlobal('reporte', 'FormatoRequisicionX', '50');
				$ancho = obtenerPreferenciaGlobal('reporte', 'FormatoRequisicionAncho', '14');
				$pdf->SetFont($fontname,'', $fontsize);
				$pdf->PutRows($x, $y, $req, $ancho);
			}
			$visible = obtenerPreferenciaGlobal('reporte', 'FormatoCantidadVisible', '1');
			if ( $visible == "1" ) {
				$fontsize = obtenerPreferenciaGlobal('reporte', 'FormatoCantidadFontSize', '8');
				$fontname = obtenerPreferenciaGlobal('reporte', 'FormatoCantidadFontName', 'Arial');
				$x = obtenerPreferenciaGlobal('reporte', 'FormatoCantidadX', '95');
				$ancho = obtenerPreferenciaGlobal('reporte', 'FormatoCantidadAncho', '14');
				$pdf->SetFont($fontname,'', $fontsize);
				$pdf->PutRows($x, $y, (float)$row[2], $ancho);
			}

			$visible = obtenerPreferenciaGlobal('reporte', 'FormatoUnidadVisible', '1');
			if ( $visible == "1" ) {
				$fontsize = obtenerPreferenciaGlobal('reporte', 'FormatoUnidadFontSize', '8');
				$fontname = obtenerPreferenciaGlobal('reporte', 'FormatoUnidadFontName', 'Arial');
				$x = obtenerPreferenciaGlobal('reporte', 'FormatoUnidadX', '100');
				$ancho = obtenerPreferenciaGlobal('reporte', 'FormatoUnidadAncho', '14');
				$pdf->SetFont($fontname,'', $fontsize);
				$pdf->PutRows($x, $y, ObtenerDescripcionDesdeID("unidades",$row[3],"unidad"), $ancho);
			}
			$visible = obtenerPreferenciaGlobal('reporte', 'FormatoDescripcionVisible', '1');
			if ( $visible == "1" ) {
				$fontsize = obtenerPreferenciaGlobal('reporte', 'FormatoDescripcionFontSize', '8');
				$fontname = obtenerPreferenciaGlobal('reporte', 'FormatoDescripcionFontName', 'Arial');
				$x = obtenerPreferenciaGlobal('reporte', 'FormatoDescripcionX', '115');
				$ancho = obtenerPreferenciaGlobal('reporte', 'FormatoDescripcionAncho', '69');
				$pdf->SetFont($fontname,'', $fontsize);
				$pdf->PutRows($x, $y, $row[4], $ancho,5);
			}
			$visible = obtenerPreferenciaGlobal('reporte', 'FormatoEstadoVisible', '1');
			if ( $visible == "1" ) {
				$fontsize = obtenerPreferenciaGlobal('reporte', 'FormatoEstadoFontSize', '8');
				$fontname = obtenerPreferenciaGlobal('reporte', 'FormatoEstadoFontName', 'Arial');
				$x = obtenerPreferenciaGlobal('reporte', 'FormatoEstadoX', '240');
				$ancho = obtenerPreferenciaGlobal('reporte', 'FormatoEstadoAncho', '14');
				$pdf->SetFont($fontname,'', $fontsize);
				$pdf->PutRows($x, $y, $estado, $ancho);
			}
			$visible = obtenerPreferenciaGlobal('reporte', 'FormatoSolicitanteVisible', '1');
			if ( $visible == "1" ) {
				$fontsize = obtenerPreferenciaGlobal('reporte', 'FormatoSolicitanteFontSize', '8');
				$fontname = obtenerPreferenciaGlobal('reporte', 'FormatoSolicitanteFontName', 'Arial');
				$x = obtenerPreferenciaGlobal('reporte', 'FormatoSolicitanteX', '255');
				$ancho = obtenerPreferenciaGlobal('reporte', 'FormatoSolicitanteAncho', '34');
				$pdf->SetFont($fontname,'', $fontsize);
				$pdf->PutRows($x, $y, $solicitante, $ancho);
			}
			$pdf->SetY($y + $alto);
		}
	}

	function ExportarEncabezados($pdf) {
		$pdf->AddPage();
		$visible = obtenerPreferenciaGlobal('reporte', 'FormatoIdVisible', '1');
		if ( $visible == "1" ) {
			$fontsize = obtenerPreferenciaGlobal('reporte', 'FormatoIdTituloFontSize', '6');
			$fontname = obtenerPreferenciaGlobal('reporte', 'FormatoIdFontName', 'Arial');
			$x = obtenerPreferenciaGlobal('reporte', 'FormatoIdX', '5');
			$y = obtenerPreferenciaGlobal('reporte', 'FormatoIdY', '5');
			$ancho = obtenerPreferenciaGlobal('reporte', 'FormatoIdAncho', '14');
			$etiqueta = obtenerPreferenciaGlobal('reporte', 'FormatoIdEtiqueta', 'Id');
			$pdf->SetFont($fontname,'', $fontsize);
			$pdf->PutRows($x, $y,  $etiqueta , $ancho);
		}
		$visible = obtenerPreferenciaGlobal('reporte', 'FormatoRequisicionVisible', '1');
		if ( $visible == "1" ) {
			$fontsize = obtenerPreferenciaGlobal('reporte', 'FormatoRequisicionTituloFontSize', '6');
			$fontname = obtenerPreferenciaGlobal('reporte', 'FormatoRequisicionFontName', 'Arial');
			$x = obtenerPreferenciaGlobal('reporte', 'FormatoRequisicionX', '20');
			$y = obtenerPreferenciaGlobal('reporte', 'FormatoRequisicionY', '5');
			$ancho = obtenerPreferenciaGlobal('reporte', 'FormatoRequisicionAncho', '14');
			$etiqueta = obtenerPreferenciaGlobal('reporte', 'FormatoRequisicionEtiqueta', 'Requisicion');
			$pdf->SetFont($fontname,'', $fontsize);
			$pdf->PutRows($x, $y,  $etiqueta , $ancho);
		}
		$visible = obtenerPreferenciaGlobal('reporte', 'FormatoFechaVisible', '1');
		if ( $visible == "1" ) {
			$fontsize = obtenerPreferenciaGlobal('reporte', 'FormatoFechaTituloFontSize', '6');
			$fontname = obtenerPreferenciaGlobal('reporte', 'FormatoFechaFontName', 'Arial');
			$x = obtenerPreferenciaGlobal('reporte', 'FormatoFechaX', '35');
			$y = obtenerPreferenciaGlobal('reporte', 'FormatoFechaY', '5');
			$ancho = obtenerPreferenciaGlobal('reporte', 'FormatoFechaAncho', '29');
			$etiqueta = obtenerPreferenciaGlobal('reporte', 'FormatoFechaEtiqueta', 'Fecha');
			$pdf->SetFont($fontname,'', $fontsize);
			$pdf->PutRows($x, $y,  $etiqueta , $ancho);
		}
		$visible = obtenerPreferenciaGlobal('reporte', 'FormatoEmpresaVisible', '1');
		if ( $visible == "1" ) {
			$fontsize = obtenerPreferenciaGlobal('reporte', 'FormatoEmpresaTituloFontSize', '6');
			$fontname = obtenerPreferenciaGlobal('reporte', 'FormatoEmpresaFontName', 'Arial');
			$x = obtenerPreferenciaGlobal('reporte', 'FormatoEmpresaX', '65');
			$y = obtenerPreferenciaGlobal('reporte', 'FormatoEmpresaY', '5');
			$ancho = obtenerPreferenciaGlobal('reporte', 'FormatoEmpresaAncho', '29');
			$etiqueta = obtenerPreferenciaGlobal('reporte', 'FormatoEmpresaEtiqueta', 'Empresa');
			$pdf->SetFont($fontname,'', $fontsize);
			$pdf->PutRows($x, $y,  $etiqueta , $ancho);
		}
		$visible = obtenerPreferenciaGlobal('reporte', 'FormatoCantidadVisible', '1');
		if ( $visible == "1" ) {
			$fontsize = obtenerPreferenciaGlobal('reporte', 'FormatoCantidadTituloFontSize', '6');
			$fontname = obtenerPreferenciaGlobal('reporte', 'FormatoCantidadFontName', 'Arial');
			$x = obtenerPreferenciaGlobal('reporte', 'FormatoCantidadX', '95');
			$y = obtenerPreferenciaGlobal('reporte', 'FormatoCantidadY', '5');
			$ancho = obtenerPreferenciaGlobal('reporte', 'FormatoCantidadAncho', '14');
			$etiqueta = obtenerPreferenciaGlobal('reporte', 'FormatoCantidadEtiqueta', 'Cantidad');
			$pdf->SetFont($fontname,'', $fontsize);
			$pdf->PutRows($x, $y,  $etiqueta , $ancho);
		}
		$visible = obtenerPreferenciaGlobal('reporte', 'FormatoUnidadVisible', '1');
		if ( $visible == "1" ) {
			$fontsize = obtenerPreferenciaGlobal('reporte', 'FormatoUnidadTituloFontSize', '6');
			$fontname = obtenerPreferenciaGlobal('reporte', 'FormatoUnidadFontName', 'Arial');
			$x = obtenerPreferenciaGlobal('reporte', 'FormatoUnidadX', '110');
			$y = obtenerPreferenciaGlobal('reporte', 'FormatoUnidadY', '5');
			$ancho = obtenerPreferenciaGlobal('reporte', 'FormatoUnidadAncho', '14');
			$etiqueta = obtenerPreferenciaGlobal('reporte', 'FormatoUnidadEtiqueta', 'Unidad');
			$pdf->SetFont($fontname,'', $fontsize);
			$pdf->PutRows($x, $y,  $etiqueta , $ancho);
		}
		$visible = obtenerPreferenciaGlobal('reporte', 'FormatoDescripcionVisible', '1');
		if ( $visible == "1" ) {
			$fontsize = obtenerPreferenciaGlobal('reporte', 'FormatoDescripcionTituloFontSize', '6');
			$fontname = obtenerPreferenciaGlobal('reporte', 'FormatoDescripcionFontName', 'Arial');
			$x = obtenerPreferenciaGlobal('reporte', 'FormatoDescripcionX', '125');
			$y = obtenerPreferenciaGlobal('reporte', 'FormatoDescripcionY', '5');
			$ancho = obtenerPreferenciaGlobal('reporte', 'FormatoDescripcionAncho', '69');
			$etiqueta = obtenerPreferenciaGlobal('reporte', 'FormatoDescripcionEtiqueta', 'Descripcion');
			$pdf->SetFont($fontname,'', $fontsize);
			$pdf->PutRows($x, $y,  $etiqueta , $ancho);
		}
		$visible = obtenerPreferenciaGlobal('reporte', 'FormatoComentarioVisible', '1');
		if ( $visible == "1" ) {
			$fontsize = obtenerPreferenciaGlobal('reporte', 'FormatoComentarioTituloFontSize', '6');
			$fontname = obtenerPreferenciaGlobal('reporte', 'FormatoComentarioFontName', 'Arial');
			$x = obtenerPreferenciaGlobal('reporte', 'FormatoComentarioX', '195');
			$y = obtenerPreferenciaGlobal('reporte', 'FormatoComentarioY', '5');
			$ancho = obtenerPreferenciaGlobal('reporte', 'FormatoComentarioAncho', '54');
			$etiqueta = obtenerPreferenciaGlobal('reporte', 'FormatoComentarioEtiqueta', 'Comentario');
			$pdf->SetFont($fontname,'', $fontsize);
			$pdf->PutRows($x, $y,  $etiqueta , $ancho);
		}
		$visible = obtenerPreferenciaGlobal('reporte', 'FormatoEstadoVisible', '1');
		if ( $visible == "1" ) {
			$fontsize = obtenerPreferenciaGlobal('reporte', 'FormatoEstadoTituloFontSize', '6');
			$fontname = obtenerPreferenciaGlobal('reporte', 'FormatoEstadoFontName', 'Arial');
			$x = obtenerPreferenciaGlobal('reporte', 'FormatoEstadoX', '250');
			$y = obtenerPreferenciaGlobal('reporte', 'FormatoEstadoY', '5');
			$ancho = obtenerPreferenciaGlobal('reporte', 'FormatoEstadoAncho', '14');
			$etiqueta = obtenerPreferenciaGlobal('reporte', 'FormatoEstadoEtiqueta', 'Estado');
			$pdf->SetFont($fontname,'', $fontsize);
			$pdf->PutRows($x, $y,  $etiqueta , $ancho);
		}
		$visible = obtenerPreferenciaGlobal('reporte', 'FormatoSolicitanteVisible', '1');
		if ( $visible == "1" ) {
			$fontsize = obtenerPreferenciaGlobal('reporte', 'FormatoSolicitanteTituloFontSize', '6');
			$fontname = obtenerPreferenciaGlobal('reporte', 'FormatoSolicitanteFontName', 'Arial');
			$x = obtenerPreferenciaGlobal('reporte', 'FormatoSolicitanteX', '265');
			$y = obtenerPreferenciaGlobal('reporte', 'FormatoSolicitanteY', '5');
			$ancho = obtenerPreferenciaGlobal('reporte', 'FormatoSolicitanteAncho', '34');
			$etiqueta = obtenerPreferenciaGlobal('reporte', 'FormatoSolicitanteEtiqueta', 'Solicitante');
			$pdf->SetFont($fontname,'', $fontsize);
			$pdf->PutRows($x, $y,  $etiqueta , $ancho);
		}
		$pdf->SetY($y +5);
	}

	function ImprimirRequisicion($pdf, $idrequisicion, $modoprueba=0) {
		ImprimirEncabezados($pdf, $idrequisicion, $modoprueba);
		ImprimirComentarios($pdf, $idrequisicion, $modoprueba);
		ImprimirPartidas($pdf, $idrequisicion, $modoprueba);
	}

	function ObtenerEmpresaRequisicion($idrequisicion) {
		global $db;
		$resultado='';
		$res = $db->prepare("SELECT idempresa FROM requisiciones WHERE id=". $idrequisicion .";");
		$res->execute();
		while ($row = $res->fetch()) {
			$resultado=$row[0];
		}
		return $resultado;
	}

	function ImprimirComentarios($pdf, $idrequisicion, $modoprueba) {
		global $db;
		$comentario="";
		$solicitante="";
		$idusuario ="";
		$empresa= ObtenerEmpresaRequisicion($idrequisicion);
		$fontsize = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoComentarioFontSize', '12');
		$fontname = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoComentarioFontName', 'Arial');
		$x = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoComentarioX', '40');
		$y = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoComentarioY', '108');
		$ancho = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoComentarioAncho', '170');
		$interlineado = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoComentarioInterlineado', '5');
		$lineas = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoComentarioLineas', '2');
		$pdf->SetFont($fontname,'', $fontsize);
		$maximoy = $lineas * $interlineado;
		$res = $db->prepare("SELECT idsolicitante,idusuario FROM requisiciones WHERE id=". $idrequisicion .";");
		$res->execute();
		while ($row = $res->fetch()) {
			$solicitante=$row[0];
			$idusuario=$row[1];
		}
		$res = $db->prepare("SELECT * FROM comentariosrequisiciones WHERE activo=1 AND idrequisicion=". $idrequisicion ." AND (idusuario=". $solicitante ." OR idusuario=". $idusuario .");");
		$res->execute();
		while ($row = $res->fetch()) {
			$comentario= $row[3];
		}
		if ( $modoprueba == 1 ) {
			$comentario = str_replace(' ',' prueba ', $comentario);
		}
		if ($pdf->MeassureRows($comentario, $ancho, $interlineado) > $maximoy) {
			while ( strlen($comentario) && $pdf->MeassureRows($comentario ."...", $ancho, $interlineado) > $maximoy ) {
				$comentario = substr($comentario,0,-1);
			}
			$comentario .= "...";
		}
		$pdf->PutRows($x, $y, $comentario, $ancho, $interlineado);
	}

	function ImprimirEncabezados($pdf, $idrequisicion, $modoprueba) {
		global $db;
		$empresa= ObtenerEmpresaRequisicion($idrequisicion);
		$res = $db->prepare("SELECT * FROM requisiciones WHERE id=". $idrequisicion .";");
		$res->execute();
		while ($row = $res->fetch()) {
			$fontsize = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoIdFontSize', '14');
			$fontname = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoIdFontName', 'Arial');
			$x = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoIdX', '190');
			$y = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoIdY', '5');
			$pdf->SetFont($fontname,'', $fontsize);
			$pdf->PutRows($x, $y, "[". $idrequisicion ."]");
			$fontsize = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoDeptoFontSize', '10');
			$fontname = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoDeptoFontName', 'Arial');
			$x = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoDeptoX', '35');
			$y = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoDeptoY', '28');
			$ancho = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoDeptoAncho', '60');
			$pdf->SetFont($fontname,'', $fontsize);
			$pdf->PutRows($x, $y, ObtenerDescripcionDesdeID("departamentos",$row[16],"departamento"), $ancho);
			$fontsize = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoAreaFontSize', '10');
			$fontname = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoAreaFontName', 'Arial');
			$x = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoAreaX', '35');
			$y = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoAreaY', '34');
			$ancho = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoAreaAncho', '60');
			$pdf->SetFont($fontname,'', $fontsize);
			$pdf->PutRows($x, $y, ObtenerDescripcionDesdeID("areas",$row[17],"area"),60);
			$visible = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoCrNumeroVisible', '1');
			if ( $visible == "1" ) {
				$fontsize = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoCrNumeroFontSize', '14');
				$fontname = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoCrNumeroFontName', 'Arial');
				$x = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoCrNumeroX', '110');
				$y = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoCrNumeroY', '28');
				$pdf->SetFont($fontname,'', $fontsize);
				$pdf->PutRows($x, $y, ObtenerDescripcionDesdeID("centroscostos", $row[18], "numero"));
			}
			$visible = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoCrDescripcionVisible', '0');
			if ( $visible == "1" ) {
				$fontsize = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoCrDescripcionFontSize', '14');
				$fontname = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoCrDescripcionFontName', 'Arial');
				$x = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoCrDescripcionX', '110');
				$y = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoCrDescripcionY', '28');
				$pdf->SetFont($fontname,'', $fontsize);
				$pdf->PutRows($x, $y, ObtenerDescripcionDesdeID("centroscostos", $row[18], "descripcion"));
			}
			$fontsize = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoFechaFontSize', '10');
			$fontname = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoFechaFontName', 'Arial');
			$dx = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoFechaDayX', '148');
			$dy = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoFechaDayY', '32');
			$mx = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoFechaMonthX', '156');
			$my = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoFechaMonthY', '32');
			$yx = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoFechaYearX', '163');
			$yy = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoFechaYearY', '32');
			$pdf->SetFont($fontname,'', $fontsize);
			$pdf->PutRows($dx, $dy, date("d"));
			$pdf->PutRows($mx, $my, date("m"));
			$pdf->PutRows($yx, $yy, date("Y"));
			$visible = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoFechaCompletaVisible', '0');
			if ( $visible == "1" ) {
				$fontsize = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoFechaCompletaFontSize', '10');
				$fontname = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoFechaCompletaFontName', 'Arial');
				$x = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoFechaCompletaX', '60');
				$y = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoFechaCompletaY', '55');
				$pdf->SetFont($fontname,'', $fontsize);
				$pdf->PutRows($x, $y, date("Y-m-d"));
			}
			$visible = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoSolicitanteNombreVisible', '1');
			if ( $visible == "1" ){
				$fontsize = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoSolicitanteNombreFontSize', '10');
				$fontname = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoSolicitanteNombreFontName', 'Arial');
				$x = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoSolicitanteNombreX', '15');
				$y = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoSolicitanteNombreY', '120');
				$ancho = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoSolicitanteNombreAncho', '60');
				$pdf->SetFont($fontname,'', $fontsize);
				$pdf->PutRows($x, $y, ObtenerDescripcionDesdeID("usuarios",$row[20],"nombre"), $ancho);
			}
			$visible = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoSolicitanteNumeroVisible', '1');
			if ( $visible == "1" ){
				$fontsize = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoSolicitanteNumeroFontSize', '10');
				$fontname = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoSolicitanteNumeroFontName', 'Arial');
				$x = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoSolicitanteNumeroX', '15');
				$y = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoSolicitanteNumeroY', '125');
				$ancho = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoSolicitanteNumeroAncho', '60');
				$pdf->SetFont($fontname,'', $fontsize);
				$pdf->PutRows($x, $y, "(". ObtenerDescripcionDesdeID("usuarios",$row[20],"numero") .")", $ancho);
			}
		}
	}

	function ImprimirPartidas($pdf, $idrequisicion, $modoprueba) {
		global $db;
		$empresa= ObtenerEmpresaRequisicion($idrequisicion);
		$fontsize = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoPartidasFontSize', '12');
		$fontname = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoPartidasFontName', 'Arial');
		$partvisible = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoPartidasPartVisible', '0');
		$partx = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoPartidasPartX', '18');
		$cantx = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoPartidasCantX', '18');
		$unidx = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoPartidasUnidX', '40');
		$descx = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoPartidasDescX', '65');
		$inicioy = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoPartidasY', '47');
		$ancho = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoPartidasAncho', '145');
		$interlineado = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoPartidasInterlineado', '4.91');
		$lineas = obtenerPreferenciaGlobal('empresa'. $empresa, 'FormatoPartidasLineas', '12');
		$linea=0;
		$pdf->SetFont($fontname,'', $fontsize);
		$maximoy = $inicioy + ($lineas * $interlineado);
		$partidasy = $inicioy;
		if ( $modoprueba == 1 ) {
			$pdf->SetLineWidth(0.40);
			$pdf->Line($cantx, $inicioy, $descx + $ancho, $inicioy);
			$pdf->Line($cantx, $maximoy - $interlineado, $descx + $ancho, $maximoy - $interlineado);
		}
		$res = $db->prepare("SELECT * FROM partidas WHERE activo=1 AND idrequisicion=". $idrequisicion .";");
		$res->execute();
		while ($row = $res->fetch()) {
			$linea = $linea +1;
			if ( $partidasy + $pdf->MeassureRows($row[4], $ancho, $interlineado) > $maximoy ) {
				$partidasy = $inicioy;
				$pdf->AddPage();
				ImprimirEncabezados($pdf, $idrequisicion, $modoprueba);
				ImprimirComentarios($pdf, $idrequisicion, $modoprueba);
			}
			if ( $partvisible == "1") {
				$pdf->PutRows($partx, $partidasy, $linea);
			}
			$pdf->PutRows($cantx, $partidasy, (float)$row[2]);
			$pdf->PutRows($unidx, $partidasy, ObtenerDescripcionDesdeID("unidades", $row[3], "unidad"));
			$descripcion = $row[4];
			if ( $modoprueba == 1 ) {
				$descripcion = str_replace(' ',' prueba ', $descripcion);
			}
			$pdf->PutRows($descx, $partidasy, $descripcion, $ancho, $interlineado);
			$partidasy = $partidasy + ($pdf->MeassureRows($descripcion, $ancho, $interlineado));
		}
	}

	function AgregarComentariosRequisicion($idrequisicion) {
		$resultado="";
		if ( usuarioEsLogeado() ) {
			$resultado="<input type=\"button\" value=\"Agregar\" onclick=\"addComentarioReq('tablacomentariosreq". $idrequisicion ."');\">";
		}else{
			$resultado="<small>Acciones</small>";
		}
		return $resultado;
	}

	function ComentarioReqEsActivo($idcomentario) {
		global $db;
		$resultado=false;
		$res = $db->prepare("SELECT activo FROM comentariosrequisiciones WHERE id=". $idcomentario .";");
		$res->execute();
		while ($row = $res->fetch()) {
			if ( $row[0] == 1 ) {
				$resultado=true;
			}
		}
		return $resultado;
	}

	function ComentarioReqEsMio($idcomentario) {
		global $db;
		$resultado=false;
		if ( usuarioEsLogeado() ) {
			$res = $db->prepare("SELECT id FROM comentariosrequisiciones WHERE id=". $idcomentario ." AND idusuario=". usuarioId() .";");
			$res->execute();
			while ($row = $res->fetch()) {
				if ( $row[0] == $idcomentario ) {
					$resultado=true;
				}
			}
		}
		return $resultado;
	}

	function AccionesComentarioRequisicion($idcomentario) {
		$resultado="";
		if ( usuarioEsLogeado() ) {
			if ( ComentarioReqEsMio($idcomentario) || usuarioEsSuper() ) {
				if ( ComentarioReqEsActivo($idcomentario) ) {
					$resultado .= "<input type=\"button\" value=\"Eliminar\" onclick=\"deleteComentarioReq(this, ". $idcomentario .");\">";
				}
			}
			if ( !ComentarioReqEsActivo($idcomentario) && usuarioEsSuper() ) {
				$resultado .= "<input type=\"button\" value=\"Restaurar\" onclick=\"undeleteComentarioReq(this, ". $idcomentario .");\">";
			}
		}
		return $resultado;
	}

	function MostrarComentariosRequisicion($idrequisicion, $q) {
		global $db;
		$resultado="";
		$res = $db->prepare("SELECT * FROM comentariosrequisiciones WHERE idrequisicion=". $idrequisicion .";");
		$res->execute();
		$resultado .= "<table id=\"tablacomentariosreq". $idrequisicion ."\">";
		$resultado .= "<tr><td width=\"60%\"><small>Comentario</small></td><td width=\"15%\"><small>Fecha</small></td><td width=\"15%\"><small>Autor</small></td><td width=\"10%\">". AgregarComentariosRequisicion($idrequisicion) ."</td></tr>";
		while ($row = $res->fetch()) {
			$clase="com";
			if ( ComentarioReqEsMio($row[0]) ) {
				$clase .= " comowner";
			}
			if ( !ComentarioReqEsActivo($row[0]) ) {
				$clase .= " comdeleted";
			}
			$resultado .= "<tr class=\"". $clase ."\"><td>". resaltarBusqueda($row[3], $q) ."</td><td>". $row[5] ."</td><td>". ObtenerDescripcionDesdeID("usuarios",$row[4],"nombre") ."</td><td>". AccionesComentarioRequisicion($row[0]) ."</td></tr>";
		}
		$resultado .= "</table>";
		return $resultado;
	}

	function AgregarAdjuntosRequisicion($idrequisicion) {
		$resultado="";
		if ( usuarioEsLogeado() ) {
			$resultado="<input type = \"button\" value=\"Agregar\" onclick=\"addAdjuntoReq(". $idrequisicion .");\">";
		}else{
			$resultado="<small>Acciones</small>";
		}
		return $resultado;
	}

	function MostrarAdjuntosRequisicion($idrequisicion,$q) {
		global $db;
		$resultado="";
		$res = $db->prepare("SELECT * FROM adjuntosrequisiciones WHERE idrequisicion=". $idrequisicion .";");
		$res->execute();
		$resultado .= "<table id=\"tablaadjuntosreq". $idrequisicion ."\">";
		$resultado .= "<tr><td width=\"50%\"><small>Archivo</small></td><td width=\"10%\"><small>Tama&ntilde;o</small></td><td width=\"15%\"><small>Fecha</small></td><td width=\"15%\"><small>Autor</small></td><td width=\"10%\">". AgregarAdjuntosRequisicion($idrequisicion) ."</td></tr>";
		while ($row = $res->fetch()) {
			$rutaarchivo = "uploads/r". $idrequisicion ."/". $row[2];
			$resultado .= "<tr><td>". resaltarBusqueda($row[2], $q) ."</td><td>". formatBytes($row[3]) ."</td><td>". $row[5] ."</td><td>". ObtenerDescripcionDesdeID("usuarios",$row[4],"nombre") ."</td><td><button onClick=\"window.open('". $rutaarchivo ."');\">Abrir</button></td></tr>";
		}
		$resultado .= "</table>";
		return $resultado;
	}

	function MostrarCampo($etiqueta, $texto) {
		if ( strlen($texto) == 0 ) {
			$texto="&nbsp;";
		}
		$resultado="";
		$resultado .= "<table class=\"campo\">";
		$resultado .= "<tr><td><small>". $etiqueta ."<small></td></tr>";
		$resultado .= "<tr><td>". $texto ."</td></tr>";
		$resultado .= "</table>";
		return $resultado;
	}

	function MostrarPartidas($idrequisicion, $q) {
		global $db;
		$resultado="";
		$partida = 0;
		$res = $db->prepare("SELECT * FROM partidas WHERE idrequisicion=". $idrequisicion .";");
		$res->execute();
		$resultado .= "<table>";
		$resultado .= "<tr><td width=\"90%\"><small>Partida</small></td><td width=\"10%\"><small>Acciones</small></td></tr>";
		while ($row = $res->fetch()) {
			$partida = $partida +1;
			$clase = "part";
			$estado = "";
			if ( strval($row[7]) == 1 ) {
				$clase .= " partprinted";
				$estado .= "I";
			}
			if ( strval($row[6]) == 1 ) {
				$clase .= " partsupplied";
				$estado .= "S";
			}
			if ( strval($row[5]) == 0 ) {
				$clase .= " partdeleted";
				$estado .= "E";
			}
			if ( soySeguidorPartida($row[0]) ) {
				$estado .= "F";
			}
			$resultado .= "<tr id=\"part". $row[0] ."\" class=\"". $clase ."\"><td>";
			$resultado .= "<table>";
			$resultado .= "<tr>";
			$resultado .= "<td>";
			$resultado .= "<table id=\"corepart". $row[0] ."\">";
			$resultado .= "<tr>";
			$resultado .= "<td width=\"12.5%\">". MostrarCampo("Cantidad",(float)$row[2]) ."</td>";
			$resultado .= "<td width=\"12.5%\">". MostrarCampo("Unidad",ObtenerDescripcionDesdeID("unidades",$row[3],"unidad")) ."</td>";
			$resultado .= "<td width=\"75%\" colspan=5>". MostrarCampo("Descripcion",resaltarBusqueda($row[4], $q)) ."</td>";
			$resultado .= "</tr>";
			$resultado .= "<tr>";
			$resultado .= "<td width=\"16.6%\">". MostrarCampo("CentroCostos",ObtenerDescripcionDesdeID("centroscostos",$row[16],"descripcion")) ."</td>";
			$resultado .= "<td width=\"16.6%\">". MostrarCampo("Solicitante",ObtenerDescripcionDesdeID("usuarios",$row[19],"nombre")) ."</td>";
			$resultado .= "<td width=\"16.6%\">". MostrarCampo("Eliminada",$row[8]) ."</td>";
			$resultado .= "<td width=\"16.6%\">". MostrarCampo("Surtida",$row[9]) ."</td>";
			$resultado .= "<td width=\"16.6%\">". MostrarCampo("Modificada",$row[11]) ."</td>";
			$resultado .= "<td width=\"8.3%\">". MostrarCampo("Importancia",$row[18]) ."</td>";
			$resultado .= "<td width=\"8.3%\">". MostrarCampo("Estado",$estado) ."</td>";
			$resultado .= "</tr>";
			$resultado .= "</table>";
			$resultado .= "</tr>";
			$resultado .= "<tr>";
			$resultado .= "<td>";
			$resultado .= MostrarComentariosPartida($row[0], $q);
			$resultado .= "</td>";
			$resultado .= "</tr>";
			$resultado .= "<tr>";
			$resultado .= "<td>";
			$resultado .= MostrarAdjuntosPartida($row[0], $q);
			$resultado .= "</td>";
			$resultado .= "</tr>";
			$resultado .= "</table>";
			$resultado .= "</td>";
			$resultado .= "<td>";
			$resultado .= AccionesPartida($row[0]);
			$resultado .= "</td>";
			$resultado .= "</tr>";
		}
		$resultado .= "</table>";
		return $resultado;
	}

	function RequisicionEsActiva($idrequisicion) {
		global $db;
		$resultado=false;
		$res = $db->prepare("SELECT activo FROM requisiciones WHERE id=". $idrequisicion .";");
		$res->execute();
		while ($row = $res->fetch()) {
			if ( $row[0] == 1 ) {
				$resultado=true;
			}
		}
		return $resultado;
	}

	function RequisicionEsSurtida($idrequisicion) {
		global $db;
		$resultado=0;
		$res = $db->prepare("SELECT surtida FROM requisiciones WHERE activo=1 AND id=". $idrequisicion .";");
		$res->execute();
		while ($row = $res->fetch()) {
			if ( $row[0] == 1 ) {
				$resultado=1;
			}
		}
		if ( $resultado==0 ) {
			if ( RequisicionEsImpresa($idrequisicion) && RequisicionEsActiva($idrequisicion) ) {
				$resultado = 1;
				$res = $db->prepare("SELECT surtida FROM partidas WHERE activo=1 AND idrequisicion=". $idrequisicion .";");
				$res->execute();
				while ($row = $res->fetch()) {
					if ( $row[0] == 0 ) {
						$resultado=0;
					}
				}
				if ( $resultado == 1 ) {
					$res = $db->prepare("UPDATE requisiciones SET surtida=1 WHERE id=". $idrequisicion .";");
					$res->execute();
				}
			}
		}
		return $resultado;
	}

	function RequisicionEsMia($idrequisicion) {
		global $db;
		$resultado=false;
		if ( usuarioEsLogeado() ) {
			$res = $db->prepare("SELECT id FROM requisiciones WHERE id=". $idrequisicion ." AND (idsolicitante=". usuarioId() ." OR idusuario=". usuarioId() .");");
			$res->execute();
			while ($row = $res->fetch()) {
				if ( $row[0] == $idrequisicion ) {
					$resultado=true;
				}
			}
		}
		return $resultado;
	}

	function RequisicionEsImpresa($idrequisicion) {
		global $db;
		$resultado=false;
		$res = $db->prepare("SELECT impresa FROM requisiciones WHERE id=". $idrequisicion .";");
		$res->execute();
		while ($row = $res->fetch()) {
			$resultado= strval($row[0]);
		}
		return $resultado;
	}
	
	function soySeguidorRequisicion($idrequisicion) {
		global $db;
		$resultado=false;
		if ( usuarioEsLogeado() ) {
			$res = $db->prepare("SELECT id FROM seguidoresrequisiciones WHERE idrequisicion=? AND idusuario=? AND activo=1;");
			$res->execute([$idrequisicion, usuarioId()]);
			while ($row = $res->fetch()) {
				$resultado=true;
			}
		}
		return $resultado;
	}

	function AccionesRequisicion($idrequisicion) {
		$resultado="";
		if ( usuarioEsLogeado() ) {
			$resultado .= '<button onClick="appCopiaRequisicion('. $idrequisicion .');">Clonar</button>';
			if ( soySeguidorRequisicion($idrequisicion) ) {
				$resultado .= '<button onClick="appAbandonarRequisicion('. $idrequisicion .');">Abandonar</button>';
			}
		}
		if ( usuarioEsLogeado() && RequisicionEsActiva($idrequisicion) ) {
			if ( !soySeguidorRequisicion($idrequisicion) && !RequisicionEsMia($idrequisicion) ) {
				$resultado .= '<button onClick="appSeguirRequisicion('. $idrequisicion .');">Seguir</button>';
			}
		}
		if ( RequisicionEsMia($idrequisicion) || usuarioEsSuper() ) {
			if ( !(RequisicionEsSurtida($idrequisicion)) && RequisicionEsImpresa($idrequisicion) && RequisicionEsActiva($idrequisicion) ) {
				$resultado .= '<button onClick="appSurteRequisicion('. $idrequisicion .');">Surtida</button>';
			}
			if ( !RequisicionEsImpresa($idrequisicion) && RequisicionEsActiva($idrequisicion) ) {
				$resultado .= '<button onClick="appImprimeRequisicion('. $idrequisicion .');">Imprimir</button>';
			}
			if ( RequisicionEsImpresa($idrequisicion) && !RequisicionEsSurtida($idrequisicion) && RequisicionEsActiva($idrequisicion) ) {
				$resultado .= '<button onClick="appEditarImpresa(this, '. $idrequisicion .');">Editar</button>';
			}
			if ( RequisicionEsActiva($idrequisicion) && !RequisicionEsSurtida($idrequisicion) ) {
				$resultado .= '<button onClick="appIncluirEnRequisicion('. $idrequisicion .');">Incluir</button>';
				$resultado .= '<button onClick="appBorraRequisicion('. $idrequisicion .');">Eliminar</button>';
			}
			if ( !(RequisicionEsActiva($idrequisicion)) ) {
				$resultado .= '<button onClick="appRestauraRequisicion('. $idrequisicion .');">Restaurar</button>';
			}
		}
		if ( usuarioEsSuper() ) {
			if ( RequisicionEsSurtida($idrequisicion) && RequisicionEsActiva($idrequisicion)  ) {
				$resultado .= '<button onClick="appPorSurtirRequisicion('. $idrequisicion .');">Por surtir</button>';
			}
			if ( RequisicionEsImpresa($idrequisicion) && RequisicionEsActiva($idrequisicion)  ) {
				$resultado .= '<button onClick="appImprimeRequisicion('. $idrequisicion .');">Reimprimir</button>';
			}
		}
		return $resultado;
	}

	function MostrarMarcoRequisicion($idrequisicion, $q) {
		$resultado = "";
		$clase="req";
		if ( RequisicionEsMia($idrequisicion) ) {
			$clase .= " owner";
		}
		if ( RequisicionEsImpresa($idrequisicion) ) {
			$clase .= " printed";
		}
		if ( RequisicionEsSurtida($idrequisicion) ) {
			$clase .= " supplied";
		}
		if ( !RequisicionEsActiva($idrequisicion) ) {
			$clase .= " deleted";
		}
		$resultado .="<table id=\"marcorequisicion". $idrequisicion ."\" class=\"". $clase ."\">";
		$resultado .="<tr>";
		$resultado .="<td width=\"90%\">";
		$resultado .="<small>Requisicion</small>";
		$resultado .="</td>";
		$resultado .="<td width=\"10%\">";
		$resultado .="<small>Acciones</small>";
		$resultado .="</td>";
		$resultado .="<tr>";
		$resultado .="<td>";
		$resultado .=MostrarRequisicion($idrequisicion, $q);
		$resultado .="</td>";
		$resultado .="<td>";
		$resultado .=AccionesRequisicion($idrequisicion);
		$resultado .="</td>";
		$resultado .="</tr>";
		$resultado .="</table>";
		return $resultado;
	}

	function MostrarRequisicion($idrequisicion, $q="") {
		global $db;
		$resultado="";
		$res = $db->prepare("SELECT * FROM requisiciones WHERE id=". $idrequisicion .";");
		$res->execute();
		while ($row = $res->fetch()) {
			$status="";
			$clase="req";
			if ( RequisicionEsImpresa($idrequisicion) ) {
				$clase .= " printed";
			}
			if ( RequisicionEsSurtida($idrequisicion) ) {
				$clase .= " supplied";
			}
			if ( !RequisicionEsActiva($idrequisicion) ) {
				$clase .= " deleted";
			}
			if ( RequisicionEsMia($idrequisicion) ) {
				$status .= "M";
			}
			if ( RequisicionEsImpresa($idrequisicion) ) {
				$status .= "I";
			}
			if ( RequisicionEsSurtida($idrequisicion) ) {
				$status .= "S";
			}
			if ( !RequisicionEsActiva($idrequisicion) ) {
				$status .= "E";
			}
			if ( soySeguidorRequisicion($idrequisicion) ) {
				$status .= "F";
			}
			$resultado .= "<table id=\"mostrarrequisicion". $idrequisicion ."\"  class=\"". $clase ."\">";
			$resultado .= "<tr>";
			$resultado .= "<td width=\"6%\"><small>Id:</small></td><td width=\"14%\">". resaltarBusqueda($row[0], $q) ."</td>";
			$resultado .= "<td width=\"6%\"><small>Empresa:</small></td><td width=\"14%\">". ObtenerDescripcionDesdeID("empresas",$row[15],"nombre") ."</td>";
			$resultado .= "<td width=\"6%\"><small>Requisicion:</small></td><td width=\"14%\">". resaltarBusqueda($row[2], $q) ."</td>";
			$resultado .= "<td width=\"6%\"><small>Fecha:</small></td><td width=\"14%\">". $row[1] ."</td>";
			$resultado .= "<td width=\"6%\"><small>Estado:</small></td><td width=\"14%\">". $status ."</td>";
			$resultado .= "</tr>";
			$resultado .= "<tr>";
			$resultado .= "<td width=\"6%\"><small>Departamento:</small></td><td width=\"14%\">". ObtenerDescripcionDesdeID("departamentos",$row[16],"departamento") ."</td>";
			$resultado .= "<td width=\"6%\"><small>Area:</small></td><td width=\"14%\">". ObtenerDescripcionDesdeID("areas",$row[17],"area") ."</td>";
			$resultado .= "<td width=\"6%\"><small>CentroCostos:</small></td><td width=\"14%\">". ObtenerDescripcionDesdeID("centroscostos",$row[18],"descripcion") ."</td>";
			$resultado .= "<td width=\"6%\"><small>Solicitante:</small></td><td width=\"14%\">". ObtenerDescripcionDesdeID("usuarios",$row[20],"nombre") ."</td>";
			$resultado .= "<td width=\"6%\"><small>Importancia:</small></td><td width=\"14%\">". $row[19] ."</td>";
			$resultado .= "</tr>";
			$resultado .= "<tr><td colspan=10>";
			$resultado .= MostrarPartidas($idrequisicion, $q);
			$resultado .= "</td></tr>";
			$resultado .= "<tr><td colspan=10>";
			$resultado .= MostrarComentariosRequisicion($idrequisicion, $q);
			$resultado .= "</td></tr>";
			$resultado .= "<tr><td colspan=10>";
			$resultado .= MostrarAdjuntosRequisicion($idrequisicion, $q);
			$resultado .= "</td></tr>";
			$resultado .= "<tr>";
			$resultado .= "<td width=\"6%\"><small>Creada:</small></td><td width=\"14%\">". $row[10] ."</td>";
			$resultado .= "<td width=\"6%\"><small>Eliminada:</small></td><td width=\"14%\">". $row[6] ."</td>";
			$resultado .= "<td width=\"6%\"><small>Impresa:</small></td><td width=\"14%\">". $row[8] ."</td>";
			$resultado .= "<td width=\"6%\"><small>Surtida:</small></td><td width=\"14%\">". $row[7] ."</td>";
			$resultado .= "<td width=\"6%\"><small>Modificada:</small></td><td width=\"14%\">". $row[9] ."</td>";
			$resultado .= "</tr>";
			$resultado .= "<tr>";
			$resultado .= "<td width=\"6%\"><small>Creada:</small></td><td width=\"14%\">". ObtenerDescripcionDesdeID("usuarios",$row[21],"nombre") ."</td>";
			$resultado .= "<td width=\"6%\"><small>Eliminada:</small></td><td width=\"14%\">". ObtenerDescripcionDesdeID("usuarios",$row[11],"nombre") ."</td>";
			$resultado .= "<td width=\"6%\"><small>Impresa:</small></td><td width=\"14%\">". ObtenerDescripcionDesdeID("usuarios",$row[13],"nombre") ."</td>";
			$resultado .= "<td width=\"6%\"><small>Surtida:</small></td><td width=\"14%\">". ObtenerDescripcionDesdeID("usuarios",$row[12],"nombre") ."</td>";
			$resultado .= "<td width=\"6%\"><small>Modificada:</small></td><td width=\"14%\">". ObtenerDescripcionDesdeID("usuarios",$row[14],"nombre") ."</td>";
			$resultado .= "</tr>";
			$resultado .= "</table>";
		}
		return $resultado;
	}

	function formNewReqForm() {
		$resultado="";
		$resultado .="<form id=\"newreqform\" method = \"POST\" enctype=\"multipart/form-data\">";
		$resultado .="	<input type=\"hidden\" name=\"posted\" value=\"1\">";
		$resultado .="	<div>";
		$resultado .="	<table>";
		$resultado .="	<tr>";
		$resultado .="	<td width=\"90%\">";
		$resultado .="	<table>";
		$resultado .="		<tr>";
		$resultado .="			<td width=\"20%\"><small>Empresa:</small></td><td width=\"80%\"><select name = \"empresa\">". ObtenerOpcionesSelect("empresas","nombre") ."</select></td>";
		$resultado .="		</tr>";
		$resultado .="		<tr>";
		$resultado .="			<td width=\"20%\"><small>Departamento:</small></td><td width=\"80%\"><select name = \"departamento\">". ObtenerOpcionesSelectGroup("departamentos","departamento","empresas","idempresa") ."</select></td>";
		$resultado .="		</tr>";
		$resultado .="		<tr>";
		$resultado .="			<td width=\"20%\"><small>Area:</small></td><td width=\"80%\"><select name = \"area\">". ObtenerOpcionesSelectGroup("areas","area","empresas","idempresa") ."</select></td>";
		$resultado .="		</tr>";
		$resultado .="		<tr>";
		$resultado .="			<td width=\"20%\"><small>CentroCostos:</small></td><td width=\"80%\"><select name = \"centrocostosreq\" id=\"centrocostosreq\">". ObtenerOpcionesSelectGroup("centroscostos","descripcion","empresas","idempresa") ."</select></td>";
		$resultado .="		</tr>";
		$resultado .="		<tr>";
		$resultado .="			<td colspan=2>";
		$resultado .="			<table id=\"tablapartidas\">";
		$resultado .="				<tr>";
		$resultado .="					<td width=\"90%\"><small>Partidas</small></td>";
		$resultado .="					<td width=\"10%\"><input type = \"button\" value=\"Agregar\" onclick=\"addPartidaNewReq('tablapartidas');\"></td>";
		$resultado .="				</tr>";
		$resultado .="			</table>";
		$resultado .="			</td>";
		$resultado .="		</tr>";
		$resultado .="		<tr>";
		$resultado .="			<td colspan=2>";
		$resultado .="			<table id=\"tablacomentariosreq\">";
		$resultado .="				<tr>";
		$resultado .="					<td width=\"90%\"><small>Comentarios</small></td>";
		$resultado .="					<td width=\"10%\"><input type = \"button\" value=\"Agregar\" onclick=\"addComentarioNewReq('tablacomentariosreq');\"></td>";
		$resultado .="				</tr>";
		$resultado .="			</table>";
		$resultado .="			</td>";
		$resultado .="		</tr>";
		$resultado .="		<tr>";
		$resultado .="			<td colspan=2>";
		$resultado .="			<table id=\"tablaadjuntosreq\">";
		$resultado .="				<tr>";
		$resultado .="					<td width=\"80%\"><small>Adjuntos</small></td>";
		$resultado .="					<td width=\"10%\"><small>Tama&ntilde;o</small></td>";
		$resultado .="					<td width=\"10%\"><input type = \"button\" value=\"Agregar\" onclick=\"addAdjuntoNewReq('tablaadjuntosreq');\"></td>";
		$resultado .="				</tr>";
		$resultado .="			</table>";
		$resultado .="			</td>";
		$resultado .="		</tr>";
		$resultado .="		<tr>";
		$resultado .="			<td width=\"20%\"><small>Solicitante:</small></td><td width=\"80%\"><select name = \"solicitante\">". ObtenerUsuariosSelect() ."</select></td>";
		$resultado .="		</tr>";
		$resultado .="	</table>";
		$resultado .="	</td>";
		$resultado .="	<td width=\"10%\">";
		$resultado .="		<button id=\"botonenviarnewreq\" onClick=\"event.preventDefault();appEnviarNewReq();\">Guardar</button>";
		$resultado .="	</td>";
		$resultado .="	</tr>";
		$resultado .="	</table>";
		$resultado .="	</div>";
		$resultado .="</form>";
		return $resultado;
	}
	
	function formIncludeUserInReqForm($idrequisicion) {
		global $db;
		$resultado="";
		$resultado .="<form id=\"includeuserform\" method = \"POST\" enctype=\"multipart/form-data\">";
		$resultado .="	<input type=\"hidden\" name=\"includeusers\" value=\"1\">";
		$resultado .="	<input type=\"hidden\" name=\"idreq\" value=\"". $idrequisicion ."\">";
		$resultado .="	<div>";
		$resultado .="		<table>";
		$resultado .="			<tr>";
		$resultado .="				<td width=\"90%\">";
		$resultado .="					<small>Usuario</small>";
		$resultado .="				</td>";
		$resultado .="				<td width=\"10%\">";
		$resultado .="					<small>Acciones</small>";
		$resultado .="				</td>";
		$resultado .="			</tr>";
		$resultado .="			<tr>";
		$resultado .="				<td>";
		$resultado .="					<table>";
		$res = $db->prepare("SELECT id, nombre FROM usuarios WHERE activo=1 ORDER BY nombre;");
		$res->execute();
		while ($row = $res->fetch()) {
			$resultado .="						<tr>";
			$resultado .="						<td>";
			$resultado .="							<input type=\"checkbox\" name=\"user[]\" value=\"". $row[0] ."\"> ". $row[1];
			$resultado .="						</td>";
			$resultado .="						</tr>";
		}
		$resultado .="					</table>";
		$resultado .="				</td>";
		$resultado .="				<td>";
		$resultado .="					<button id=\"botonsaveincludeuser\" onClick=\"event.preventDefault();appSaveIncludeUser();\">Incluir</button>";
		$resultado .="				</td>";
		$resultado .="			</tr>";
		$resultado .="		<table>";
		$resultado .="	<div>";
		$resultado .="</form>";
		return $resultado;
	}
?>