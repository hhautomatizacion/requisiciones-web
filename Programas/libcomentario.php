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
			$res = $db->prepare("INSERT INTO comentariosrequisiciones VALUES (0,". $idrequisicion .",'". $comentario ."',". $_COOKIE["usuario"] .",NOW(),1);");
			$res->execute();
			echo "OK";
		}
	}	
?>