<?php
	require_once("libconfig.php");
	require_once("libdb.php");
	require_once("libphp.php");
	require_once("libpartida.php");
	require_once("libpdf.php");
	require_once("libuser.php");

	if ( isset($_REQUEST["posted"]) ) {
		$errores=array();
		$departamento=$_REQUEST["departamento"];
		$area=$_REQUEST["area"];
		$solicitante=$_REQUEST["solicitante"];
		$centrocostos = 0;
		$importancia = 5;
		if ( isset($_REQUEST["totalpartidas"]) ) {
			foreach( $_REQUEST["totalpartidas"] as $item) {
				$cantidad = $_REQUEST["cantidad"][$item];
				$unidad = $_REQUEST["unidad"][$item];
				$descripcion = $_REQUEST["descripcion"][$item];
				$centrocostos = $_REQUEST["centrocostos"][$item];
				if ( (float)$cantidad <= 0 ) {
					$errores["partidas"] = "Partida ". $item ." cantidad no valida";
				}
				if ( strlen($unidad) == 0) {
					$errores["partidas"] = "Unidades ". $item ." no valida";
				}
				if ( strlen($descripcion) == 0) {
					$errores["partidas"] = "Partida ". $item ." vacia";
				}
				if ( strlen($centrocostos) == 0) {
					$errores["partidas"] = "Centro costos ". $item ." no valida";
				}
			}
		}else{
			$errores["vacia"]="Requisicion vacia";
		}
		if ( count($errores)==0 ) {
		$res = $db->prepare("INSERT INTO requisiciones VALUES (0,NOW(),'',1,0,0,NULL,0,0, ?, ?, ?, ?, ?, ?);");
		$res->execute([$departamento, $area, $centrocostos, $importancia, $solicitante, $_COOKIE["usuario"]]);
		$ultimoidreq = $db->lastInsertId();
		if ( isset($_REQUEST["totalpartidas"]) ) {
			foreach( $_REQUEST["totalpartidas"] as $item) {
				$cantidad = $_REQUEST["cantidad"][$item];
				$unidad = $_REQUEST["unidad"][$item];
				$descripcion = $_REQUEST["descripcion"][$item];
				$centrocostos = $_REQUEST["centrocostos"][$item];
				$importancia= 5;
				$res = $db->prepare("INSERT INTO partidas VALUES (0,NOW(), ?, ?, ?,1,0,0,NULL, ?, ?, ?, ?, ?);");
				$res->execute([$cantidad, $unidad, $descripcion, $centrocostos, $ultimoidreq, $importancia, $solicitante, $_COOKIE["usuario"]]);
				$ultimoidpart = $db->lastInsertId();
				if ( isset($_REQUEST["partcomentarios"]) ) {
					foreach ( $_REQUEST["partcomentarios"]["tablacomentarios". $item ] as $elemento ) {
						$res = $db->prepare("INSERT INTO comentariospartidas VALUES (0, ?,0, ?, ?,NOW(),1);");
						$res->execute([$ultimoidpart, $elemento, $_COOKIE["usuario"]]);
					}
				}
				$uploaddir="uploads/";
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
							$res->execute([$ultimoidpart, $nombrearchivo, $longitudarchivo, $_COOKIE["usuario"]]);
						}
					}
				}
			}
		}
		if ( isset($_REQUEST["totalreqcomentarios"]) ) {
			foreach( $_REQUEST["totalreqcomentarios"] as $item) {
				$comentario = $_REQUEST["reqcomentarios"][$item];
				$res = $db->prepare("INSERT INTO comentariosrequisiciones VALUES (0, ?,0, ?, ?,NOW(),1);");
				$res->execute([$ultimoidreq, $comentario, $_COOKIE["usuario"]]);
			}
		}
		$uploaddir="uploads/";
		if ( isset($_REQUEST["totalreqadjuntos"]) ) {
			foreach( $_REQUEST["totalreqadjuntos"] as $item) {
				$rutaupload=$uploaddir ."r". $ultimoidreq;
				if (!is_writeable($rutaupload)) {
					mkdir($rutaupload);
				}
				$nombrearchivo=$_FILES["reqadjuntos"]["name"][$item];
				$rutatemp=$_FILES["reqadjuntos"]["tmp_name"][$item];
				$longitudarchivo=$_FILES["reqadjuntos"]["size"][$item];
				$rutadestino=$rutaupload ."/". $nombrearchivo;
				if (move_uploaded_file($rutatemp,$rutadestino)) {
					$res = $db->prepare("INSERT INTO adjuntosrequisiciones VALUES (0, ?, ?, ?, ?,NOW(),1);");
					$res->execute([$ultimoidreq, $nombrearchivo, $longitudarchivo, $_COOKIE["usuario"]]);
				}
			}
		}
		echo $ultimoidreq;
		}
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
			$res->execute([$idrequisicion, $nombrearchivo, $longitudarchivo, $_COOKIE["usuario"]]);
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
		$pdf->SetFont('Arial','',6);
		$pdf->AddPage();
		ExportarEncabezados($pdf);

		$listarequisiciones= explode(" ",$resultado);
		foreach ($listarequisiciones as $idrequisicion) {
			ExportarRequisicion($pdf, $idrequisicion);
		}
		$pdf->Output();
	}

	if ( isset($_GET["id"]) ) {
		$idrequisicion=$_GET["id"];
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
				$resultado .= MostrarMarcoRequisicion($idrequisicion);
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
				$res = $db->prepare("UPDATE requisiciones SET impresa=1 WHERE id=". $idrequisicion .";");
				$res->execute();
				$res = $db->prepare("INSERT INTO notificacionesrequisiciones VALUES (0, NOW(), 4, ". $idrequisicion .", ". $_COOKIE["usuario"] .", 1);");
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
				$res = $db->prepare("UPDATE partidas SET surtida=1 WHERE activo=1 AND idrequisicion=". $idrequisicion .";");
				$res->execute();
				$res = $db->prepare("UPDATE requisiciones SET surtida=1 WHERE id=". $idrequisicion .";");
				$res->execute();
				$res = $db->prepare("INSERT INTO notificacionesrequisiciones VALUES (0, NOW(), 5, ". $idrequisicion .", ". $_COOKIE["usuario"] .", 1);");
				$res->execute();
				echo "OK";
				break;
			case "delete":
				$res = $db->prepare("UPDATE partidas SET activo=0 WHERE surtida=0 AND idrequisicion=". $idrequisicion .";");
				$res->execute();
				$res = $db->prepare("UPDATE requisiciones SET activo=0 WHERE id=". $idrequisicion .";");
				$res->execute();
				$res = $db->prepare("INSERT INTO notificacionesrequisiciones VALUES (0, NOW(), 6, ". $idrequisicion .", ". $_COOKIE["usuario"] .", 1);");
				$res->execute();
				echo "OK";
				break;
			case "undelete":
				$res = $db->prepare("UPDATE requisiciones SET activo=1 WHERE id=". $idrequisicion .";");
				$res->execute();
				echo "OK";
				break;
		}
	}
	function CopiarRequisicion($idrequisicion) {
		global $db;
		$uploaddir="uploads/";
		$res= $db->prepare("SELECT * FROM requisiciones WHERE id=". $idrequisicion);
		$res->execute();
		while ($row = $res->fetch()) {
			$requisicion="";
			$departamento=$row[9];
			$area=$row[10];
			$solicitante=$row[13];
			$centrocostos = $row[11];
			$importancia = $row[12];
		}
		$iter=0;
		$res= $db->prepare("SELECT * FROM partidas WHERE activo=1 AND idrequisicion=". $idrequisicion);
		$res->execute();
		while ($row = $res->fetch()) {
			$totalpartidas[]=$iter;
			$cantidad[$iter] = $row[2];
			$unidad[$iter] = $row[3];
			$descripcion[$iter] = $row[4];
			$centrocostospart[$iter] = $row[9];
			$importanciapart[$iter] = $row[11];
			$solicitantepart[$iter] = $row[12];
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
		$res = $db->prepare("INSERT INTO requisiciones VALUES (0,NOW(), ?,1,0,0,NULL,0,0, ?, ?, ?, ?, ?, ?);");
		$res->execute([$requisicion, $departamento, $area, $centrocostos, $importancia, $solicitante, $_COOKIE["usuario"]]);
		$ultimoidreq = $db->lastInsertId();
		foreach( $totalpartidas as $item) {
			$res = $db->prepare("INSERT INTO partidas VALUES (0,NOW(), ?, ?, ?,1,0,0,NULL, ?, ?, ?, ?, ?);");
			$res->execute([$cantidad[$item], $unidad[$item], $descripcion[$item], $centrocostospart[$item], $ultimoidreq, $importanciapart[$item], $solicitantepart[$item], $_COOKIE["usuario"]]);
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
		$res->execute([$ultimoidreq, $comentario, $_COOKIE["usuario"]]);
		return "OK";
	}

	function ExportarRequisicion($pdf, $idrequisicion) {
		global $db;
		$y=$pdf->LastY();
		$req="";
		$fecha="";
		$solicitante="";
		$estado="";
		$res = $db->prepare("SELECT * FROM requisiciones WHERE id=". $idrequisicion .";");
		$res->execute();
		while ($row = $res->fetch()) {
			$fecha=$row[1];
			$req=$row[2];
			$solicitante=ObtenerDescripcionDesdeID("usuarios",$row[13],"nombre");
		}
		$res = $db->prepare("SELECT * FROM partidas WHERE idrequisicion=". $idrequisicion .";");
		$res->execute();
		while ($row = $res->fetch()) {
			$estado="";
			if ( $y + $pdf->MeassureRows($row[4] ,115,5) > 210 ) {
				$pdf->AddPage();
				ExportarEncabezados($pdf);
				$y=10;
			}
			if ( strval($row[6]) == 1 ) {
				$estado .= "Surtida";
			}
			if ( strval($row[5]) == 0 ) {
				$estado .= "Eliminada";
			}
			$pdf->PutRows(5,$y,$idrequisicion);
			$pdf->PutRows(19,$y,$req);
			$pdf->PutRows(33,$y,$fecha);
			$pdf->PutRows(61,$y,(float)$row[2]);
			$pdf->PutRows(75,$y,ObtenerDescripcionDesdeID("unidades",$row[3],"unidad"));
			$pdf->PutRows(229,$y,$estado);
			$pdf->PutRows(257,$y,$solicitante);
			$pdf->PutRows(89,$y,$row[4] ,115,5);
			$y=$y+ ($pdf->MeassureRows($row[4] ,115,5));
			$pdf->SetLineWidth(0.20);
			$pdf->Line(4,$y-4,280,$y-4);
		}
		$pdf->SetLineWidth(0.40);
		$pdf->Line(2,$y-4,285,$y-4);
	}

	function ExportarEncabezados($pdf) {
		$y=5;
		$pdf->PutRows(5, $y, "Id");
		$pdf->PutRows(19, $y, "Req");
		$pdf->PutRows(33, $y, "Fecha");
		$pdf->PutRows(61, $y, "Cant");
		$pdf->PutRows(75, $y, "Unidad");
		$pdf->PutRows(89, $y, "Descripcion");
		$pdf->PutRows(229, $y, "Estado");
		$pdf->PutRows(257, $y, "Solicitante");
		$pdf->PutRows(5, 10, "");
	}

	function ImprimirRequisicion($pdf, $idrequisicion, $modoprueba=0) {
		ImprimirEncabezados($pdf, $idrequisicion, $modoprueba);
		ImprimirComentarios($pdf, $idrequisicion, $modoprueba);
		ImprimirPartidas($pdf, $idrequisicion, $modoprueba);
	}

	function ImprimirComentarios($pdf, $idrequisicion, $modoprueba) {
		global $db;
		$comentario="";
		$solicitante="";
		$idusuario ="";

		$fontsize = obtenerPreferencia('impresion', 'ComentarioFontSize', '12');
		$fontname = obtenerPreferencia('impresion', 'ComentarioFontName', 'Arial');
		$x = obtenerPreferencia('impresion', 'ComentarioX', '40');
		$y = obtenerPreferencia('impresion', 'ComentarioY', '108');
		$ancho = obtenerPreferencia('impresion', 'ComentarioAncho', '170');
		$interlineado = obtenerPreferencia('impresion', 'ComentarioInterlineado', '5');
		$lineas = obtenerPreferencia('impresion', 'ComentarioLineas', '2');
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
		if ( strlen($comentario) == 0 ) {
			$res = $db->prepare("SELECT comentariospartidas.comentario, comentariospartidas.activo FROM comentariospartidas INNER JOIN partidas ON (comentariospartidas.idpartida=partidas.id) WHERE (comentariospartidas.idusuario=". $solicitante ." OR comentariospartidas.idusuario=". $idusuario .") AND partidas.activo=1 AND partidas.idrequisicion=". $idrequisicion .";");
			$res->execute();
			while ($row = $res->fetch()) {
				if ( $row[1] == '1' ) {
					$comentario= $row[0];
				}
			}
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
		$res = $db->prepare("SELECT * FROM requisiciones WHERE id=". $idrequisicion .";");
		$res->execute();
		while ($row = $res->fetch()) {
			$fontsize = obtenerPreferencia('impresion', 'IdFontSize', '14');
			$fontname = obtenerPreferencia('impresion', 'IdFontName', 'Arial');
			$x = obtenerPreferencia('impresion', 'IdX', '190');
			$y = obtenerPreferencia('impresion', 'IdY', '5');
			$pdf->SetFont($fontname,'', $fontsize);
			$pdf->PutRows($x, $y, "[". $idrequisicion ."]");

			$fontsize = obtenerPreferencia('impresion', 'DeptoFontSize', '10');
			$fontname = obtenerPreferencia('impresion', 'DeptoFontName', 'Arial');
			$x = obtenerPreferencia('impresion', 'DeptoX', '35');
			$y = obtenerPreferencia('impresion', 'DeptoY', '28');
			$ancho = obtenerPreferencia('impresion', 'DeptoAncho', '60');
			$pdf->SetFont($fontname,'', $fontsize);
			$pdf->PutRows($x, $y, ObtenerDescripcionDesdeID("departamentos",$row[9],"departamento"), $ancho);

			$fontsize = obtenerPreferencia('impresion', 'AreaFontSize', '10');
			$fontname = obtenerPreferencia('impresion', 'AreaFontName', 'Arial');
			$x = obtenerPreferencia('impresion', 'AreaX', '35');
			$y = obtenerPreferencia('impresion', 'AreaY', '34');
			$ancho = obtenerPreferencia('impresion', 'AreaAncho', '60');
			$pdf->SetFont($fontname,'', $fontsize);
			$pdf->PutRows($x, $y, ObtenerDescripcionDesdeID("areas",$row[10],"area"),60);

			$fontsize = obtenerPreferencia('impresion', 'FechaFontSize', '10');
			$fontname = obtenerPreferencia('impresion', 'FechaFontName', 'Arial');
			$dx = obtenerPreferencia('impresion', 'FechaDayX', '148');
			$dy = obtenerPreferencia('impresion', 'FechaDayY', '32');
			$mx = obtenerPreferencia('impresion', 'FechaMonthX', '156');
			$my = obtenerPreferencia('impresion', 'FechaMonthY', '32');
			$yx = obtenerPreferencia('impresion', 'FechaYearX', '163');
			$yy = obtenerPreferencia('impresion', 'FechaYearY', '32');
			$pdf->SetFont($fontname,'', $fontsize);
			$pdf->PutRows($dx, $dy, date("d"));
			$pdf->PutRows($mx, $my, date("m"));
			$pdf->PutRows($yx, $yy, date("Y"));

			$fontsize = obtenerPreferencia('impresion', 'SolicitanteFontSize', '10');
			$fontname = obtenerPreferencia('impresion', 'SolicitanteFontName', 'Arial');
			$x = obtenerPreferencia('impresion', 'SolicitanteX', '15');
			$y = obtenerPreferencia('impresion', 'SolicitanteY', '120');
			$ancho = obtenerPreferencia('impresion', 'SolicitanteAncho', '60');
			$pdf->SetFont($fontname,'', $fontsize);
			$pdf->PutRows($x, $y, ObtenerDescripcionDesdeID("usuarios",$row[13],"nombre") ." (". ObtenerDescripcionDesdeID("usuarios",$row[13],"numero") .")", $ancho);
		}
	}

	function ImprimirPartidas($pdf, $idrequisicion, $modoprueba) {
		global $db;
		$cr = "";

		$fontsize = obtenerPreferencia('impresion', 'PartidasFontSize', '12');
		$fontname = obtenerPreferencia('impresion', 'PartidasFontName', 'Arial');
		$cantx = obtenerPreferencia('impresion', 'PartidasCantX', '18');
		$unidx = obtenerPreferencia('impresion', 'PartidasUnidX', '40');
		$descx = obtenerPreferencia('impresion', 'PartidasDescX', '65');
		$inicioy = obtenerPreferencia('impresion', 'PartidasY', '47');
		$ancho = obtenerPreferencia('impresion', 'PartidasAncho', '140');
		$interlineado = obtenerPreferencia('impresion', 'PartidasInterlineado', '4.91');
		$lineas = obtenerPreferencia('impresion', 'PartidasLineas', '12');
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
			$cr = ObtenerDescripcionDesdeID("centroscostos", $row[9], "numero");
			if ( $partidasy + $pdf->MeassureRows($row[4], $ancho, $interlineado) > $maximoy ) {
				$partidasy = $inicioy;
				$pdf->AddPage();
				ImprimirEncabezados($pdf, $idrequisicion, $modoprueba);
				ImprimirComentarios($pdf, $idrequisicion, $modoprueba);
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
		$fontsize = obtenerPreferencia('impresion', 'CrFontSize', '14');
		$fontname = obtenerPreferencia('impresion', 'CrFontName', 'Arial');
		$x = obtenerPreferencia('impresion', 'CrX', '110');
		$y = obtenerPreferencia('impresion', 'CrY', '28');
		$pdf->SetFont($fontname,'', $fontsize);
		$pdf->PutRows($x, $y, $cr);
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
			$res = $db->prepare("SELECT id FROM comentariosrequisiciones WHERE id=". $idcomentario ." AND idusuario=". $_COOKIE["usuario"] .";");
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
			if ( ComentarioReqEsActivo($idcomentario) ) {
				$resultado .= "<input type=\"button\" value=\"Responder\" onclick=\"replyComentarioReq(". $idcomentario .");\">";
			}
			if ( !ComentarioReqEsActivo($idcomentario) && usuarioEsSuper() ) {
				$resultado .= "<input type=\"button\" value=\"Restaurar\" onclick=\"undeleteComentarioReq(this, ". $idcomentario .");\">";
			}
		}
		return $resultado;
	}

	function MostrarComentariosRequisicion($idrequisicion) {
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
			$resultado .= "<tr class=\"". $clase ."\"><td>". $row[3] ."</td><td>". $row[5] ."</td><td>". ObtenerDescripcionDesdeID("usuarios",$row[4],"nombre") ."</td><td>". AccionesComentarioRequisicion($row[0]) ."</td></tr>";
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
	function MostrarAdjuntosRequisicion($idrequisicion) {
		global $db;
		$resultado="";
		$res = $db->prepare("SELECT * FROM adjuntosrequisiciones WHERE idrequisicion=". $idrequisicion .";");
		$res->execute();
		$resultado .= "<table id=\"tablaadjuntosreq". $idrequisicion ."\">";
		$resultado .= "<tr><td width=\"50%\"><small>Archivo</small></td><td width=\"10%\"><small>Tama&ntilde;o</small></td><td width=\"15%\"><small>Fecha</small></td><td width=\"15%\"><small>Autor</small></td><td width=\"10%\">". AgregarAdjuntosRequisicion($idrequisicion) ."</td></tr>";
		while ($row = $res->fetch()) {
			$rutaarchivo = "uploads/r". $idrequisicion ."/". $row[2];
			$resultado .= "<tr><td>". $row[2] ."</td><td>". formatBytes($row[3]) ."</td><td>". $row[5] ."</td><td>". ObtenerDescripcionDesdeID("usuarios",$row[4],"nombre") ."</td><td><button onClick=\"window.open('". $rutaarchivo ."');\">Abrir</button></td></tr>";
		}
		$resultado .= "</table>";
		return $resultado;
	}

	function MostrarPartidas($idrequisicion) {
		global $db;
		$resultado="";
		$res = $db->prepare("SELECT * FROM partidas WHERE idrequisicion=". $idrequisicion .";");
		$res->execute();
		$resultado .= "<table>";
		$resultado .= "<tr><td width=\"90%\"><small>Partida</small></td><td width=\"10%\"><small>Acciones</small></td></tr>";
		while ($row = $res->fetch()) {
			$clase="part";
			if ( strval($row[7]) == 1 ) {
				$clase .= " partprinted";
			}
			if ( strval($row[6]) == 1 ) {
				$clase .= " partsupplied";
			}
			if ( strval($row[5]) == 0 ) {
				$clase .= " partdeleted";
			}
			$resultado .= "<tr class=\"". $clase ."\"><td>";
			$resultado .= "<table >";
			$resultado .= "<tr><td width=\"10%\"><small>Cantidad</small></td><td width=\"10%\"><small>Unidad</small></td><td><small>Descripcion</small></td><td width=\"15%\"><small>C.R.</small></td></tr>";
			$resultado .= "<tr><td>". (float)$row[2] ."</td><td>". ObtenerDescripcionDesdeID("unidades",$row[3],"unidad") ."</td><td>". $row[4] ."</td><td>". ObtenerDescripcionDesdeID("centroscostos",$row[9],"descripcion") ."</td></tr>";
			$resultado .= "</table>";
			$resultado .= MostrarComentariosPartida($row[0]);
			$resultado .= MostrarAdjuntosPartida($row[0]);
			$resultado .= "</td><td>";
			$resultado .= AccionesPartida($row[0]);
			$resultado .= "</td></tr>";
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
			$res = $db->prepare("SELECT id FROM requisiciones WHERE id=". $idrequisicion ." AND (idsolicitante=". $_COOKIE["usuario"] ." OR idusuario=". $_COOKIE["usuario"] .");");
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

	function AccionesRequisicion($idrequisicion) {
		$resultado="";
		if ( usuarioEsLogeado() ) {
			$resultado .= '<button onClick="appCopiaRequisicion('. $idrequisicion .');">Copiar</button>';
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
				$resultado .= '<button onClick="appBorraRequisicion('. $idrequisicion .');">Eliminar</button>';
			}
		}
		if ( usuarioEsSuper() ) {
			if ( RequisicionEsSurtida($idrequisicion) && RequisicionEsActiva($idrequisicion)  ) {
				$resultado .= '<button onClick="appPorsurtirRequisicion('. $idrequisicion .');">Por surtir</button>';
			}
			if ( RequisicionEsImpresa($idrequisicion) && RequisicionEsActiva($idrequisicion)  ) {
				$resultado .= '<button onClick="appImprimeRequisicion('. $idrequisicion .');">Reimprimir</button>';
			}
			if ( !(RequisicionEsActiva($idrequisicion)) ) {
				$resultado .= '<button onClick="appRestauraRequisicion('. $idrequisicion .');">Restaurar</button>';
			}
		}
		return $resultado;
	}

	function MostrarMarcoRequisicion($idrequisicion) {
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
		$resultado .=MostrarRequisicion($idrequisicion);
		$resultado .="</td>";
		$resultado .="<td width=\"10%\">";
		$resultado .=AccionesRequisicion($idrequisicion);
		$resultado .="</td>";
		$resultado .="</tr>";
		$resultado .="</table>";
		return $resultado;
	}

	function ResumenRequisicion($idrequisicion) {
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
			if ( RequisicionEsImpresa($idrequisicion) ) {
				$status .= "I";
			}
			if ( RequisicionEsSurtida($idrequisicion) ) {
				$status .= "S";
			}
			if ( !RequisicionEsActiva($idrequisicion) ) {
				$status .= "E";
			}
			$resultado .="<table id=\"mostrarrequisicion". $idrequisicion ."\"  class=\"". $clase ."\">";
			$resultado .= "<tr><td width=\"10%\"><small>Id:</small></td><td width=\"15%\">". $row[0] ."</td><td width=\"10%\"><small>Requisicion:</small></td><td width=\"15%\">". $row[2] ."</td><td width=\"10%\"><small>Fecha:</small></td><td width=\"15%\">". $row[1] ."</td><td width=\"10%\"><small>Importancia:</small></td><td width=\"15%\">TODO</td></tr>";
			$resultado .= "<tr><td width=\"10%\"><small>Departamento:</small></td><td width=\"15%\">". ObtenerDescripcionDesdeID("departamentos",$row[9],"departamento") ."</td><td width=\"10%\"><small>Area:</small></td><td width=\"15%\">". ObtenerDescripcionDesdeID("areas",$row[10],"area") ."</td><td width=\"10%\"><small>Surtir:</small></td><td width=\"15%\">TODO</td><td width=\"10%\"><small>Estado:</small></td><td width=\"15%\">". $status ."</td></tr>";
			$resultado .="<tr><td colspan=8>";
			$resultado .= ResumenPartidas($idrequisicion);
			$resultado .="</td></tr>";
			$resultado .="<tr><td colspan=8>";
			$resultado .=MostrarComentariosRequisicion($idrequisicion);
			$resultado .="</td></tr>";
			$resultado .="<tr><td colspan=8>";
			$resultado .=MostrarAdjuntosRequisicion($idrequisicion);
			$resultado .="</td></tr>";
			$resultado .="<tr><td width=\"10%\"><small>Surtida:</small></td><td width=\"15%\">". ObtenerDescripcionDesdeID("usuarios",$row[7],"nombre") ."</td><td width=\"10%\"><small>Imprime:</small></td><td width=\"15%\">". ObtenerDescripcionDesdeID("usuarios",$row[8],"nombre") ."</td><td width=\"10%\"><small>Solicitante:</small></td><td width=\"15%\">". ObtenerDescripcionDesdeID("usuarios",$row[13],"nombre") ."</td><td width=\"10%\"><small>Autor:</small></td><td width=\"15%\">". ObtenerDescripcionDesdeID("usuarios",$row[14],"nombre") ."</td></tr>";
			$resultado .="</table>";
		}
		return $resultado;
	}

	function ResumenPartidas($idrequisicion) {
		global $db;
		$resultado="";
		$res = $db->prepare("SELECT * FROM partidas WHERE idrequisicion=". $idrequisicion .";");
		$res->execute();
		$resultado .= "<table>";
		$resultado .= "<tr><td width=\"90%\"><small>Partida</small></td><td width=\"10%\"><small>Acciones</small></td></tr>";
		while ($row = $res->fetch()) {
			$clase="part";
			if ( strval($row[7]) == 1 ) {
				$clase .= " partprinted";
			}
			if ( strval($row[6]) == 1 ) {
				$clase .= " partsupplied";
			}
			if ( strval($row[5]) == 0 ) {
				$clase .= " partdeleted";
			}
			$resultado .= "<tr class=\"". $clase ."\"><td>";
			$resultado .= "<table >";
			$resultado .= "<tr><td width=\"10%\"><small>Cantidad</small></td><td width=\"10%\"><small>Unidad</small></td><td><small>Descripcion</small></td><td width=\"15%\"><small>C.R.</small></td></tr>";
			$resultado .= "<tr><td>". (float)$row[2] ."</td><td>". ObtenerDescripcionDesdeID("unidades",$row[3],"unidad") ."</td><td>". $row[4] ."</td><td>". ObtenerDescripcionDesdeID("centroscostos",$row[9],"descripcion") ."</td></tr>";
			$resultado .= "</table>";
			$resultado .= MostrarComentariosPartida($row[0]);
			$resultado .= MostrarAdjuntosPartida($row[0]);
			$resultado .= "</td><td>";
			$resultado .= "</td></tr>";
		}
		$resultado .= "</table>";
		return $resultado;
	}

	function MostrarRequisicion($idrequisicion) {
		global $db;
		$resultado="";
		$res = $db->prepare("SELECT * FROM requisiciones WHERE id=". $idrequisicion .";");
		$res->execute();
		while ($row = $res->fetch()) {
			$status="";
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
			$resultado .="<table id=\"mostrarrequisicion". $idrequisicion ."\">";
			$resultado .= "<tr><td width=\"10%\"><small>Id:</small></td><td width=\"15%\">". $row[0] ."</td><td width=\"10%\"><small>Requisicion:</small></td><td width=\"15%\">". $row[2] ."</td><td width=\"10%\"><small>Fecha:</small></td><td width=\"15%\">". $row[1] ."</td><td width=\"10%\"><small>Importancia:</small></td><td width=\"15%\">TODO</td></tr>";
			$resultado .= "<tr><td width=\"10%\"><small>Departamento:</small></td><td width=\"15%\">". ObtenerDescripcionDesdeID("departamentos",$row[9],"departamento") ."</td><td width=\"10%\"><small>Area:</small></td><td width=\"15%\">". ObtenerDescripcionDesdeID("areas",$row[10],"area") ."</td><td width=\"10%\"><small>Surtir:</small></td><td width=\"15%\">TODO</td><td width=\"10%\"><small>Estado:</small></td><td width=\"15%\">". $status ."</td></tr>";
			$resultado .="<tr><td colspan=8>";
			$resultado .= MostrarPartidas($idrequisicion);
			$resultado .="</td></tr>";
			$resultado .="<tr><td colspan=8>";
			$resultado .=MostrarComentariosRequisicion($idrequisicion);
			$resultado .="</td></tr>";
			$resultado .="<tr><td colspan=8>";
			$resultado .=MostrarAdjuntosRequisicion($idrequisicion);
			$resultado .="</td></tr>";
			$resultado .="<tr><td width=\"10%\"><small>Surtida:</small></td><td width=\"15%\">". ObtenerDescripcionDesdeID("usuarios",$row[7],"nombre") ."</td><td width=\"10%\"><small>Imprime:</small></td><td width=\"15%\">". ObtenerDescripcionDesdeID("usuarios",$row[8],"nombre") ."</td><td width=\"10%\"><small>Solicitante:</small></td><td width=\"15%\">". ObtenerDescripcionDesdeID("usuarios",$row[13],"nombre") ."</td><td width=\"10%\"><small>Autor:</small></td><td width=\"15%\">". ObtenerDescripcionDesdeID("usuarios",$row[14],"nombre") ."</td></tr>";
			$resultado .="</table>";
		}
		return $resultado;
	}
	function formNewReqForm() {
		$resultado="";
		$resultado.="		<form id=\"newreqform\" method = \"POST\" enctype=\"multipart/form-data\">";
		$resultado.="			<input type=\"hidden\" name=\"posted\" value=\"1\">";
		$resultado.="			<div>";
		$resultado.="			<table>";
		$resultado.="			<tr>";
		$resultado.="			<td width=\"100%\">";
		$resultado.="			<table>";
		$resultado.="				<tr>";
		$resultado.="					<td><small>Departamento:</small></td><td><select name = \"departamento\">". ObtenerOpcionesSelect("departamentos","departamento") ."</select></td>";
		$resultado.="				</tr>";
		$resultado.="				<tr>";
		$resultado.="					<td><small>Area:</small></td><td><select name = \"area\">". ObtenerOpcionesSelect("areas","area") ."</select></td>";
		$resultado.="				</tr>";
		$resultado.="				<tr>";
		$resultado.="					<td colspan=2>";
		$resultado.="					<table id=\"tablapartidas\">";
		$resultado.="						<tr>";
		$resultado.="							<td width=\"90%\"><small>Partidas</small></td>";
		$resultado.="							<td width=\"10%\"><input type = \"button\" value=\"Agregar\" onclick=\"addPartidaNewReq('tablapartidas');\"></td>";
		$resultado.="						</tr>";
		$resultado.="					</table>";
		$resultado.="					</td>";
		$resultado.="				</tr>";
		$resultado.="				<tr>";
		$resultado.="					<td colspan=2>";
		$resultado.="					<table id=\"tablacomentariosreq\">";
		$resultado.="						<tr>";
		$resultado.="							<td width=\"90%\"><small>Comentarios</small></td>";
		$resultado.="							<td width=\"10%\"><input type = \"button\" value=\"Agregar\" onclick=\"addComentarioNewReq('tablacomentariosreq');\"></td>";
		$resultado.="						</tr>";
		$resultado.="					</table>";
		$resultado.="					";
		$resultado.="					</td>";
		$resultado.="				</tr>";
		$resultado.="				<tr>";
		$resultado.="					<td colspan=2>";
		$resultado.="					<table id=\"tablaadjuntosreq\">";
		$resultado.="						<tr>";
		$resultado.="							<td width=\"80%\"><small>Adjuntos</small></td>";
		$resultado.="							<td width=\"10%\"><small>Tama&ntilde;o</small></td>";
		$resultado.="							<td width=\"10%\"><input type = \"button\" value=\"Agregar\" onclick=\"addAdjuntoNewReq('tablaadjuntosreq');\"></td>";
		$resultado.="						</tr>";
		$resultado.="					</table>";
		$resultado.="					";
		$resultado.="					</td>";
		$resultado.="				</tr>";
		$resultado.="				<tr>";
		$resultado.="					<td><small>Solicitante:</small></td><td><select name = \"solicitante\">". ObtenerUsuariosSelect() ."</select></td>";
		$resultado.="				</tr>";
		$resultado.="			</table>";
		$resultado.="			</td>";
		$resultado.="			<td width=\"10%\">";
		$resultado .="						<button onClick=\"event.preventDefault();appEnviarNewReq();\">Guardar</button>";
		$resultado.="			</td>";
		$resultado.="			</tr>";
		$resultado.="			</table>";
		$resultado.="			</div>";
		$resultado.="		</form>";
		return $resultado;
	}
?>
