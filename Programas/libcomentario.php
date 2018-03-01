<?php
	require_once("libconfig.php");
	require_once("libdb.php");
	require_once("libphp.php");
	require_once("libpartida.php");
	require_once("libuser.php");

	if ( isset($_GET["action"]) && $_GET["action"] == "comadd" ) {
		if ( isset($_GET["type"]) && $_GET["type"] == "comreq" ) {
			if ( isset($_GET["idreq"]) ) {
				$idrequisicion=$_GET["idreq"];
				$comentario=$_GET["comentario"];
				$res = $db->prepare("INSERT INTO comentariosrequisiciones VALUES (0,". $idrequisicion .",0,'". $comentario ."',". $_COOKIE["usuario"] .",NOW(),1);");
				$res->execute();
				echo "OK";
			}
		}
		if ( isset($_GET["type"]) && $_GET["type"] == "compart" ) {
			if ( isset($_GET["idpart"]) ) {
				$idpartida=$_GET["idpart"];
				$comentario=$_GET["comentario"];
				$sql = "INSERT INTO comentariospartidas VALUES (0,". $idpartida .",0,'". $comentario ."',". $_COOKIE["usuario"] .",NOW(),1);";	
				$res = $db->prepare($sql);
				$res->execute();
				echo "OK";
			}
		}
	}
	if ( isset($_GET["action"]) && $_GET["action"] == "comundelete" ) {
		if ( isset($_GET["idcom"]) ) {
			$idcomentario=$_GET["idcom"];
			if ( isset($_GET["type"]) && $_GET["type"] == "compart" ) {
				$sql = "UPDATE comentariospartidas SET activo=1 WHERE id=". $idcomentario .";";
				$res = $db->prepare($sql);
				$res->execute();
				echo "OK";
			}
			if ( isset($_GET["type"]) && $_GET["type"] == "comreq" ) {
				$sql = "UPDATE comentariosrequisiciones SET activo=1 WHERE id=". $idcomentario .";";
				
				$res = $db->prepare($sql);
				$res->execute();
				echo "OK";
			}
		}
	}		
	if ( isset($_GET["action"]) && $_GET["action"] == "comdelete" ) {
		if ( isset($_GET["idcom"]) ) {
			$idcomentario=$_GET["idcom"];
			if ( isset($_GET["type"]) && $_GET["type"] == "compart" ) {
				$sql = "UPDATE comentariospartidas SET activo=0 WHERE id=". $idcomentario .";";
				$res = $db->prepare($sql);
				$res->execute();
				echo "OK";
			}
			if ( isset($_GET["type"]) && $_GET["type"] == "comreq" ) {
				$sql = "UPDATE comentariosrequisiciones SET activo=0 WHERE id=". $idcomentario .";";
				writelog($sql);
				$res = $db->prepare($sql);
				$res->execute();
				echo "OK";
			}
		}
	}
?>