<?php
	require_once("libconfig.php");
	require_once("libdb.php");
	require_once("libphp.php");
	require_once("libpartida.php");
	require_once("libpdf.php");
	require_once("libuser.php");

	if (isset($_REQUEST["posted"])) {
		
		$departamento=$_REQUEST["departamento"];
		$area=$_REQUEST["area"];
		$solicitante=$_REQUEST["solicitante"];
		$requisicion=$_REQUEST["requisicion"];
		$centrocostos = 0;
		$importancia = 5;
		$res = $db->prepare("INSERT INTO requisiciones VALUES (0,NOW(),'". $requisicion ."',1,0,0,NULL,0,0,". $departamento .",". $area .",". $centrocostos .",". $importancia .",". $solicitante .",". $_COOKIE["usuario"] .");");
		$res->execute();
		$ultimoidreq = $db->lastInsertId();
		if ( isset($_REQUEST["totalpartidas"]) ) {
			foreach( $_REQUEST["totalpartidas"] as $item) {
				$cantidad = $_REQUEST["cantidad"][$item];
				$unidad = $_REQUEST["unidad"][$item];
				$descripcion = $_REQUEST["descripcion"][$item];
				$centrocostos = $_REQUEST["centrocostos"][$item];
				$importancia= 5;
				$sql="INSERT INTO partidas VALUES (0,NOW(),". floatval($cantidad) .",". $unidad .",'". $descripcion ."',1,0,0,NULL,". $centrocostos .",". $ultimoidreq .",". $importancia .",". $solicitante .",". $_COOKIE["usuario"] .");";
				$res = $db->prepare($sql);
				$res->execute();
				$ultimoidpart = $db->lastInsertId();
				if ( isset($_REQUEST["partcomentarios"]) ) {
					foreach ( $_REQUEST["partcomentarios"]["tablacomentarios". $item ] as $elemento ) {
						$res = $db->prepare("INSERT INTO comentariospartidas VALUES (0,". $ultimoidpart .",0,'". $elemento ."',". $_COOKIE["usuario"] .",NOW(),1);");
						$res->execute();
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
							$res = $db->prepare("INSERT INTO adjuntospartidas VALUES (0,". $ultimoidpart .",'". $nombrearchivo ."',". $longitudarchivo .",". $_COOKIE["usuario"] .",NOW(),1);");
							$res->execute();
						}
					}	
				}
			}
		}
		if ( isset($_REQUEST["totalreqcomentarios"]) ) {
			foreach( $_REQUEST["totalreqcomentarios"] as $item) {
				$comentario = $_REQUEST["reqcomentarios"][$item];
				$res = $db->prepare("INSERT INTO comentariosrequisiciones VALUES (0,". $ultimoidreq .",0,'". $comentario ."',". $_COOKIE["usuario"] .",NOW(),1);");
				$res->execute();
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
					$res = $db->prepare("INSERT INTO adjuntosrequisiciones VALUES (0,". $ultimoidreq .",'". $nombrearchivo ."',". $longitudarchivo .",". $_COOKIE["usuario"] .",NOW(),1);");
					$res->execute();
				}
			}
		}
		echo "OK";
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
	if ( isset($_GET["item"]) ) {
		if ( isset($_GET["action"]) && $_GET["action"]=="show" ) {
			$resultado="";
			$usuario="";
			$busqueda="";
			$vista="";
			$item = $_GET["item"];
			if ( isset($_GET["view"]) && intval($_GET["view"]) >= 0 && intval($_GET["view"]) < 6 ) {
				switch ($_GET["view"]) {
					case "0":
						$vista = " activo=1 AND impresa=1 AND surtida=0";
						break;
					case "1":
						$vista = " activo=1 AND impresa=1 AND surtida=1";
						break;
					case "2":
						$vista = " activo=1 AND impresa=0";
						break;
					case "3":
						$vista = " activo=1 AND impresa=1";
						break;	
					case "4":
						$vista = " activo=0";
						break;		
					case "5":
						$vista = " id>0";
						break;
				}
			}else	{
				$vista = " activo=1 AND impresa=1 AND surtida=0";
			}
			
			if ( isset($_GET["q"]) ) {
				$busqueda="AND id IN (SELECT id FROM requisiciones WHERE requisicion like '%". $_GET["q"] ."%' union select idrequisicion as id from partidas where descripcion like '%". $_GET["q"] ."%' union select idrequisicion as id from comentariosrequisiciones where comentario like '%". $_GET["q"] ."%')";
			}
			if ( isset($_GET["user"]) && intval($_GET["user"]) > 0 ) {
				$usuario="AND (idsolicitante=". $_GET["user"] ." OR idusuario=". $_GET["user"] .")";
			}
			
			$sql="SELECT DISTINCT(id) FROM requisiciones WHERE ". $vista ." ". $busqueda ." ". $usuario ." LIMIT ". $item .",1";
			
			$res = $db->prepare($sql);
			$res->execute();
			while ($row = $res->fetch()) {
				$resultado .="<div id=". $row[0] ." ondblclick=\"editar(". $row[0] .");\">";
				$resultado .=MostrarMarcoRequisicion($row[0]);
				$resultado .="</div>";
			}
			print $resultado;
		}
	}
	
	if ( isset($_GET["id"]) ) {
		$idrequisicion=$_GET["id"];
		switch ($_GET["action"]) {
			case "saveprinted":
				if ( isset($_GET["reqno"]) ) {
					$req=$_GET["reqno"];
					$res = $db->prepare("UPDATE requisiciones SET requisicion='". $req ."' WHERE id=". $idrequisicion .";");
					$res->execute();
				}
				if ( isset($_GET["surtir"]) ) {
					$surtir=$_GET["surtir"];
					$res = $db->prepare("UPDATE requisiciones SET fechasurtir='". $surtir ."' WHERE id=". $idrequisicion .";");
					$res->execute();
				}			
				echo "OK";
				break;
			case "show":
				$resultado = "";
				$resultado .=MostrarMarcoRequisicion($idrequisicion);
				echo $resultado;
				break;
			case "copy":
				$resultado = "";
				$resultado .= CopiarRequisicion($idrequisicion);
				echo $resultado;
				break;
			case "print":
				$pdf = new PDF();
				$pdf->SetFont('Arial','',10);
				$pdf->AddPage();
				ImprimirRequisicion($pdf, $idrequisicion);
				$pdf->Output();
				$res = $db->prepare("UPDATE requisiciones SET impresa=1 WHERE id=". $idrequisicion .";");
				$res->execute();
				break;
			case "supplied":
				$res = $db->prepare("UPDATE partidas SET surtida=1 WHERE activo=1 AND idrequisicion=". $idrequisicion .";");
				$res->execute();
				$res = $db->prepare("UPDATE requisiciones SET surtida=1 WHERE id=". $idrequisicion .";");
				$res->execute();
				echo "OK";
				break;
			case "delete":
				$res = $db->prepare("UPDATE partidas SET activo=0 WHERE surtida=0 AND idrequisicion=". $idrequisicion .";");
				$res->execute();
				$res = $db->prepare("UPDATE requisiciones SET activo=0 WHERE id=". $idrequisicion .";");
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
			$requisicion=$row[2];
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
			$centrocostos[$iter] = $row[9];
			$importancia[$iter] = $row[11];
			$solicitante[$iter] = $row[12];
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
				
				//writelog("adjuntoid ". $partadjuntoid[$iter][$iter2]);
				//writelog("ajuntonombre ". $partadjunto[$iter][$iter2]);
				
				$iter2=$iter2+1;
			}
			$iter=$iter+1;
		}				
		
		$res = $db->prepare("INSERT INTO requisiciones VALUES (0,NOW(),'". $requisicion ."',1,0,0,NULL,0,0,". $departamento .",". $area .",". $centrocostos .",". $importancia .",". $solicitante .",". $_COOKIE["usuario"] .");");
		$res->execute();
		$ultimoidreq = $db->lastInsertId();
		foreach( $totalpartidas as $item) {
			$sql="INSERT INTO partidas VALUES (0,NOW(),". floatval($cantidad[$item]) .",". $unidad[$item] .",'". $descripcion[$item] ."',1,0,0,NULL,". $centrocostos[$item] .",". $ultimoidreq .",". $importancia[$item] .",". $solicitante[$item] .",". $_COOKIE["usuario"] .");";
			$res = $db->prepare($sql);
			$res->execute();
			$ultimoidpart = $db->lastInsertId();
			if ( isset($totalpartcometarios) ) {
				foreach ( $totalpartcometarios[$item] as $item2 ) {
					$comentario=$partcomentario[$item][$item2];
					$comentarioautor=$partcomentarioautor[$item][$item2];
					$comentariofecha=$partcomentariofecha[$item][$item2];
					$res = $db->prepare("INSERT INTO comentariospartidas VALUES (0,". $ultimoidpart .",0,'". $comentario ."',". $comentarioautor .",'". $comentariofecha ."',1);");
					$res->execute();
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
					writelog("o ". $rutaorigen );
					writelog("d ". $rutadestino );
					if (copy($rutaorigen,$rutadestino)) {
						$sql="INSERT INTO adjuntospartidas VALUES (0,". $ultimoidpart .",'". $partadjunto[$item][$item2] ."',". $partadjuntolongitud[$item][$item2] .",". $partadjuntoautor[$item][$item2] .",'". $partadjuntofecha[$item][$item2] ."',1);";
						$res = $db->prepare($sql);
						$res->execute();
					}else{
						writelog("no se pudo copiar ". $rutaorigen ." a ". $rutadestino ."");
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
			//writelog("comentario ". $comentarioreq[$iter]);
			//writelog("autor ". $comentarioreqautor[$iter]);
			$iter=$iter+1;
		}	
		if ( isset($totalreqcomentarios) ) {
			foreach( $totalreqcomentarios as $item) {
				$res = $db->prepare("INSERT INTO comentariosrequisiciones VALUES (0,". $ultimoidreq .",0,'". $comentarioreq[$item] ."',". $comentarioreqautor[$item] .",'". $comentarioreqfecha[$item] ."',1);");
				$res->execute();
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
			//writelog("o ". $rutaorigen );
			//writelog("d ". $rutadestino );
			if (copy($rutaorigen,$rutadestino)) {
				$sql="INSERT INTO adjuntosrequisiciones VALUES (0,". $ultimoidreq .",'". $nombrearchivo[$item] ."',". $longitudarchivo[$item] .",". $autor[$item] .",'". $fecha[$item] ."',1);";
				$res = $db->prepare($sql);
				$res->execute();
			}else{
				//writelog("no se pudo copiar ". $rutaorigen ." a ". $rutadestino ."");
			}
		}
	
		$res = $db->prepare("INSERT INTO comentariosrequisiciones VALUES (0,". $ultimoidreq .",0,'Copia de la requisicion Id=". $idrequisicion ."',". $_COOKIE["usuario"] .",NOW(),1);");
		$res->execute();
		
		return "OK";
	}
	function ImprimirComentarios($pdf, $idrequisicion) {
		global $db;
		$comentario="";
		$solicitante="";
		$idusuario ="";
		$res = $db->prepare("SELECT idsolicitante,idusuario FROM requisiciones WHERE id=". $idrequisicion .";");
		$res->execute();
		while ($row = $res->fetch()) {
			$solicitante=$row[0];
			$idusuario=$row[1];
		}
		$res = $db->prepare("SELECT * FROM comentariosrequisiciones WHERE activo=1 AND idrequisicion=". $idrequisicion ." AND (idusuario=". $solicitante ." OR idusuario=". $idusuario .") LIMIT 1;");
		$res->execute();
		while ($row = $res->fetch()) {
			$comentario= $row[3];
		}
		if ($pdf->MeassureRows($comentario,170,5) > 10) {
			while ( strlen($comentario) && $pdf->MeassureRows($comentario ."...",170,5) > 10 ) {
				$comentario = substr($comentario,0,-1);
			}
			$comentario .= "...";
		}
		
		$pdf->PutRows(30,112,$comentario,170,5);
	}
	function ImprimirRequisicion($pdf, $idrequisicion) {	
		ImprimirPartidas($pdf, $idrequisicion);		
	}
	
	function ImprimirEncabezados($pdf, $idrequisicion) {
		global $db;

		$res = $db->prepare("SELECT * FROM requisiciones WHERE id=". $idrequisicion .";");
		$res->execute();
	
		while ($row = $res->fetch()) {
			$req = "[" . $idrequisicion . "]";
			
			$pdf->PutRows(30,30,ObtenerDescripcionDesdeID("departamentos",$row[6],"departamento"),60);
			
			$pdf->PutRows(30,35,ObtenerDescripcionDesdeID("areas",$row[7],"area"),60);
			
			$pdf->PutRows(190,25,$req);
			
			$pdf->PutRows(146,35,date("d"));
			$pdf->PutRows(155,35,date("m"));
			$pdf->PutRows(163,35,date("Y"));
			
			$pdf->PutRows(5,125,ObtenerDescripcionDesdeID("usuarios",$row[9],"nombre") ." (". ObtenerDescripcionDesdeID("usuarios",$row[9],"numero") .")",70);
		}
	}
	function ImprimirPartidas($pdf, $idrequisicion) {
		global $db;
		$y=50;
		$cr="";
		ImprimirEncabezados($pdf, $idrequisicion);
		ImprimirComentarios($pdf, $idrequisicion);
		$res = $db->prepare("SELECT * FROM partidas WHERE activo=1 AND idrequisicion=". $idrequisicion .";");
		$res->execute();
		while ($row = $res->fetch()) {
			$cr = ObtenerDescripcionDesdeID("centroscostos",$row[9],"numero");
			
			if ( $y + $pdf->MeassureRows($row[4] ,135,5) > 105 ) {
				$y=50;
				$pdf->AddPage();
				ImprimirEncabezados($pdf, $idrequisicion);
				ImprimirComentarios($pdf, $idrequisicion);
			}
			$pdf->PutRows(15,$y,(float)$row[2]);
			$pdf->PutRows(40,$y,ObtenerDescripcionDesdeID("unidades",$row[3],"unidad"));
			$pdf->PutRows(65,$y,$row[4] ,135,5);
			$y=$y+ ($pdf->MeassureRows($row[4] ,135,5));	
		}
		$pdf->PutRows(110,30,$cr);	
	}

	function AgregarComentariosRequisicion($idrequisicion) {
		$resultado="";
		if ( usuarioEsLogeado() ) {
			$resultado="<input type = \"button\" value=\"Agregar\" onclick=\"addComentarioReq('tablacomentariosreq". $idrequisicion ."');\">";
		}else{
			$resultado="<small>Acciones</small>";
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
			$resultado .= "<tr><td>". $row[3] ."</td><td>". $row[5] ."</td><td>". ObtenerDescripcionDesdeID("usuarios",$row[4],"nombre") ."</td><td>Accviones com</td></tr>";
		}
		$resultado .= "</table>";
		return $resultado;
	}
	
	function MostrarAdjuntosRequisicion($idrequisicion) {
		global $db;
		$resultado="";
		$res = $db->prepare("SELECT * FROM adjuntosrequisiciones WHERE idrequisicion=". $idrequisicion .";");
		$res->execute();
		$resultado .= "<table>";
		$resultado .= "<tr><td width=\"50%\"><small>Archivo</small></td><td width=\"10%\"><small>Tama&ntilde;o</small></td><td width=\"15%\"><small>Fecha</small></td><td width=\"15%\"><small>Autor</small></td><td width=\"10%\"><small>Accion</small></td></tr>";
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
			
		$sql="SELECT * FROM partidas WHERE idrequisicion=". $idrequisicion .";";
			
		$res = $db->prepare($sql);
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
			
			if ( !(RequisicionEsSurtida($idrequisicion)) && RequisicionEsImpresa($idrequisicion) && RequisicionEsActiva($idrequisicion) ) {
				$resultado .= '<button onClick="appSurteRequisicion('. $idrequisicion .');">Surtida</button>';
			}
		}
		if ( RequisicionEsMia($idrequisicion) || usuarioEsSuper() ) {
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
		$resultado.="			<td width=\"80%\">";
		$resultado.="			<table>";
		$resultado.="				<tr>";
		$resultado.="					<td><small>Departamento:</small></td><td><select name = \"departamento\">". ObtenerOpcionesSelect("departamentos","departamento") ."</select></td>";
		$resultado.="				</tr>";
		$resultado.="				<tr>";
		$resultado.="					<td><small>Area:</small></td><td><select name = \"area\">". ObtenerOpcionesSelect("areas","area") ."</select></td>";
		$resultado.="				</tr>";
		$resultado.="				<tr>";
		$resultado.="					<td><small>Requisicion:</small></td><td><input type='text' name='requisicion' /></td>";
		$resultado.="				</tr>";
		$resultado.="				<tr>";
		$resultado.="					<td colspan=2>";
		$resultado.="					<table id=\"tablapartidas\">";
		$resultado.="						<tr>";
		$resultado.="							<td width=\"80%\"><small>Partidas</small></td>";
		$resultado.="							<td width=\"20%\"><input type = \"button\" value=\"Agregar\" onclick=\"addPartidaNewReq('tablapartidas');\"></td>";
		$resultado.="						</tr>";
		$resultado.="					</table>";
		$resultado.="					</td>";
		$resultado.="				</tr>";
		$resultado.="				<tr>";
		$resultado.="					<td colspan=2>";
		$resultado.="					<table id=\"tablacomentariosreq\">";
		$resultado.="						<tr>";
		$resultado.="							<td width=\"80%\"><small>Comentarios</small></td>";
		$resultado.="							<td width=\"20%\"><input type = \"button\" value=\"Agregar\" onclick=\"addComentarioNewReq('tablacomentariosreq');\"></td>";
		$resultado.="						</tr>";
		$resultado.="					</table>";
		$resultado.="					";
		$resultado.="					</td>";
		$resultado.="				</tr>";
		$resultado.="				<tr>";
		$resultado.="					<td colspan=2>";
		$resultado.="					<table id=\"tablaadjuntosreq\">";
		$resultado.="						<tr>";
		$resultado.="							<td width=\"60%\"><small>Adjuntos</small></td>";
		$resultado.="							<td width=\"20%\"><small>Tama&ntilde;o</small></td>";
		$resultado.="							<td width=\"20%\"><input type = \"button\" value=\"Agregar\" onclick=\"addAdjuntoNewReq('tablaadjuntosreq');\"></td>";
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
		$resultado.="			<td width=\"20%\">";
		//$resultado.="				<input type = \"submit\" value=\"Guardar\">";
		$resultado .="						<button onClick=\"event.preventDefault();appEnviarNewReq();\">Guardar</button>";
		$resultado.="			</td>";
		$resultado.="			</tr>";
		$resultado.="			</table>";
		$resultado.="			</div>";
		$resultado.="		</form>";
		return $resultado;
	}
	
?>
