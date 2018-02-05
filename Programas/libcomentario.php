<?php
	require_once("libconfig.php");
	require_once("libdb.php");
	require_once("libphp.php");
	require_once("libpartida.php");
	require_once("libuser.php");

	if ( isset($_GET["action"]) && $_GET["action"] = "add" ) {
		if ( isset($_GET["idreq"]) ) {
			$idrequisicion=$_GET["idreq"];
			$comentario=$_GET["comentario"];
			$res = $db->prepare("INSERT INTO comentariosrequisiciones VALUES (0,". $idrequisicion .",0,'". $comentario ."',". $_COOKIE["usuario"] .",NOW(),1);");
			$res->execute();
			echo "OK";
		}
		if ( isset($_GET["idpart"]) ) {
			$idpartida=$_GET["idpart"];
			$comentario=$_GET["comentario"];
			$sql = "INSERT INTO comentariospartidas VALUES (0,". $idpartida .",0,'". $comentario ."',". $_COOKIE["usuario"] .",NOW(),1);";
			
			$res = $db->prepare($sql);
			$res->execute();
			echo "OK";
		}
	}	
?>