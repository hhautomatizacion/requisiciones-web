<?php
	require_once "libconfig.php";
	require_once "libuser.php";
	require_once "libphp.php";

	dbConectar();

	$accion = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_SPECIAL_CHARS);
	if ( $accion == "getoptions" ) {
		$tabla = filter_input(INPUT_GET, 'table', FILTER_SANITIZE_SPECIAL_CHARS);
		$descripcion = filter_input(INPUT_GET, 'description', FILTER_SANITIZE_SPECIAL_CHARS);
		$options = array('options' => array('default' => 0));
		$seleccionado = filter_input(INPUT_GET, 'sel', FILTER_SANITIZE_SPECIAL_CHARS, $options);
		$resultado = "";
		if ( $seleccionado > 0 ) {
			$resultado= ObtenerOpcionesSelect($tabla, $descripcion, $seleccionado);
		} else {
			$resultado= ObtenerOpcionesSelect($tabla, $descripcion);
		}
		echo $resultado;
	}

	if ( $accion == "getgroupoptions" ) {
		$tabla = filter_input(INPUT_GET, 'table', FILTER_SANITIZE_SPECIAL_CHARS);
		$descripcion = filter_input(INPUT_GET, 'description', FILTER_SANITIZE_SPECIAL_CHARS);
		$grouptable = filter_input(INPUT_GET, 'grouptable', FILTER_SANITIZE_SPECIAL_CHARS);
		$groupfield = filter_input(INPUT_GET, 'groupfield', FILTER_SANITIZE_SPECIAL_CHARS);
		$options = array('options' => array('default' => 0));
		$seleccionado = filter_input(INPUT_GET, 'sel', FILTER_SANITIZE_SPECIAL_CHARS, $options);
		$resultado = "";
		if ( $seleccionado > 0 ) {
			$resultado = ObtenerOpcionesSelectGroup($tabla, $descripcion, $grouptable, $groupfield, $seleccionado);
		} else {
			$resultado = ObtenerOpcionesSelectGroup($tabla, $descripcion, $grouptable, $groupfield);
		}
		echo $resultado;
	}

	function dbConectar() {
		global $db;
		global $db_server;
		global $db_database;
		global $db_user;
		global $db_pass;
		try {
			$db = new PDO("mysql:host=". $db_server .";dbname=". $db_database, $db_user , $db_pass);
		}
		catch (Exception $error) {
			writelog("error conexion a db ". $error->getMessage());
		}
		try {
			$res = $db->prepare("SELECT usuario FROM usuarios WHERE id=1;");
			$res->execute();
			$encontrado = false;
			while ($row = $res->fetch()) {
				$encontrado = true;
			}
			if ( !$encontrado ) {
				$res = $db->prepare("CREATE TABLE adjuntospartidas (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, idpartida INT UNSIGNED, nombre VARCHAR(300), longitud INT UNSIGNED, idusuario INT UNSIGNED, fecha DATETIME, activo TINYINT(1) UNSIGNED);");
				$res->execute();
				$res = $db->prepare("CREATE TABLE adjuntosrequisiciones (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, idrequisicion INT UNSIGNED, nombre VARCHAR(300), longitud INT UNSIGNED, idusuario INT UNSIGNED, fecha DATETIME, activo TINYINT(1) UNSIGNED);");
				$res->execute();
				$res = $db->prepare("CREATE TABLE areas (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, idempresa INT UNSIGNED, area VARCHAR(20), activo TINYINT(1) UNSIGNED);");
				$res->execute();
				$res = $db->prepare("CREATE TABLE centroscostos (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, idempresa INT UNSIGNED, numero INT UNSIGNED, descripcion VARCHAR(20), activo TINYINT(1) UNSIGNED);");
				$res->execute();
				$res = $db->prepare("CREATE TABLE comentariospartidas (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, idpartida INT UNSIGNED, idpadre INT UNSIGNED, comentario TEXT, idusuario INT UNSIGNED, fecha DATETIME, activo TINYINT(1) UNSIGNED);");
				$res->execute();
				$res = $db->prepare("CREATE TABLE comentariosrequisiciones (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, idrequisicion INT UNSIGNED, idpadre INT UNSIGNED, comentario TEXT, idusuario INT UNSIGNED, fecha DATETIME, activo TINYINT(1) UNSIGNED);");
				$res->execute();
				$res = $db->prepare("CREATE TABLE departamentos (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, idempresa INT UNSIGNED, departamento VARCHAR(20), activo TINYINT(1) UNSIGNED);");
				$res->execute();
				$res = $db->prepare("CREATE TABLE empresas (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, codigo VARCHAR(10), nombre VARCHAR(50), activo TINYINT(1) UNSIGNED);");
				$res->execute();
				$res = $db->prepare("CREATE TABLE notificacionespartidas (id INT AUTO_INCREMENT PRIMARY KEY, fecha DATETIME, clave INT, idpartida INT, idusuario INT, activo TINYINT(1));");
				$res->execute();
				$res = $db->prepare("CREATE TABLE notificacionesrequisiciones (id INT AUTO_INCREMENT PRIMARY KEY, fecha DATETIME, clave INT, idrequisicion INT, idusuario INT, activo TINYINT(1));");
				$res->execute();
				$res = $db->prepare("CREATE TABLE opciones (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, seccion VARCHAR(100), clave VARCHAR(100), valor VARCHAR(300));");
				$res->execute();
				$res = $db->prepare("CREATE TABLE opcionesusuarios (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, idusuario INT UNSIGNED, seccion VARCHAR(100), clave VARCHAR(100), valor VARCHAR(300));");
				$res->execute();
				$res = $db->prepare("CREATE TABLE partidas (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, fecha DATETIME, cantidad DECIMAL(12,3) UNSIGNED, idunidad INT UNSIGNED, descripcion VARCHAR(500), activo TINYINT(1) UNSIGNED, surtida TINYINT(1) UNSIGNED, impresa TINYINT(1) UNSIGNED, fechaactiva DATETIME, fechasurtida DATETIME, fechaimpresa DATETIME, fechamodificacion DATETIME, idactiva INT UNSIGNED, idsurtida INT UNSIGNED, idimpresa INT UNSIGNED, idmodificacion INT UNSIGNED, idcentrocostos INT UNSIGNED, idrequisicion INT UNSIGNED, importancia TINYINT(1) UNSIGNED, idsolicitante INT UNSIGNED, idusuario INT UNSIGNED);");
				$res->execute();
				$res = $db->prepare("CREATE TABLE requisiciones (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, fecha DATETIME, requisicion VARCHAR(10), activo TINYINT(1) UNSIGNED, surtida TINYINT(1) UNSIGNED, impresa TINYINT(1) UNSIGNED, fechaactiva DATETIME, fechasurtida DATETIME, fechaimpresa DATETIME, fechamodificacion DATETIME, fechacreacion DATETIME, idactiva INT UNSIGNED, idsurtida INT UNSIGNED, idimpresa INT UNSIGNED, idmodificacion INT UNSIGNED, idempresa INT UNSIGNED, iddepartmento INT UNSIGNED, idarea INT UNSIGNED, idcentrocostos INT UNSIGNED, importancia TINYINT(1) UNSIGNED, idsolicitante INT UNSIGNED, idusuario INT UNSIGNED);");
				$res->execute();
				$res = $db->prepare("CREATE TABLE seguidorespartidas (id INT AUTO_INCREMENT PRIMARY KEY, idpartida INT, idusuario INT, activo TINYINT(1));");
				$res->execute();
				$res = $db->prepare("CREATE TABLE seguidoresrequisiciones (id INT AUTO_INCREMENT PRIMARY KEY, idrequisicion INT, idusuario INT, activo TINYINT(1));");
				$res->execute();
				$res = $db->prepare("CREATE TABLE unidades (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, unidad VARCHAR(10), activo TINYINT(1) UNSIGNED);");
				$res->execute();
				$res = $db->prepare("CREATE TABLE usuarios (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, numero INT UNSIGNED, nombre VARCHAR(80), usuario VARCHAR(20), email VARCHAR(50), password VARCHAR(40), token VARCHAR(8), ultimologin DATETIME, recoverypw VARCHAR(40), recovery TINYINT(1) UNSIGNED, su TINYINT(1) UNSIGNED, activo TINYINT(1) UNSIGNED);");
				$res->execute();
				$res = $db->prepare("INSERT INTO usuarios VALUES (0,NULL,'root','root','',SHA1('manttocl'),'',NULL,'',0,1,1);");
				$res->execute();
				while ( !$encontrado ) {
					$res = $db->prepare("SELECT usuario FROM usuarios WHERE id=1;");
					$res->execute();
					while ($row = $res->fetch()) {
						$encontrado = true;
					}
				}
			}
		}
		catch (Exception $error) {
			writelog("error ". $error->getMessage());
		}
	}
	
	function ObtenerDescripcionDesdeID($tabla, $id, $campo) {
		global $db;
		$temp = "";
		$res = $db->prepare("SELECT ". $campo ." FROM ". $tabla ." WHERE id=". $id .";");
		$res->execute();
		while ($row = $res->fetch()) {
			$temp=$row[0];
		}
		return $temp;
	}
	
	function ObtenerUsuariosSelect() {
		global $db;
		$resultado = "";
		$res = $db->prepare("SELECT id, nombre FROM usuarios WHERE activo=1 ORDER BY nombre");
		$res->execute();
		while ($row = $res->fetch()) {
			if ( usuarioEsLogeado() && $row[0] == usuarioId() ) {
				$resultado .= "<option value='". $row[0] ."' selected>" . $row[1] . "</option>";
			}else{
				$resultado .= "<option value='". $row[0] ."'>" . $row[1] . "</option>";
			}
		}
		return $resultado;
	}
	
	function ObtenerOpcionesSelect($nombretabla, $nombrecampo, $seleccionado = -1) {
		global $db;
		$resultado="";
		$res = $db->prepare("SELECT id,". $nombrecampo ." FROM ". $nombretabla ." WHERE activo=1 ORDER BY ". $nombrecampo);
		$res->execute();
		while ($row = $res->fetch()) {
			if ( $row[0] == $seleccionado ) {
				$resultado .= "<option value='". $row[0] ."' selected>" . $row[1] . "</option>";
			} else {
				$resultado .= "<option value='". $row[0] ."'>" . $row[1] . "</option>";
			}
		}
		return $resultado;
	}
	
	function ObtenerOpcionesSelect2($nombretabla,$numero,$nombrecampo) {
		global $db;
		$resultado="";
		$res = $db->prepare("SELECT id,". $numero .",". $nombrecampo ." FROM ". $nombretabla ." WHERE activo=1 ORDER BY ". $nombrecampo);
		$res->execute();
		while ($row = $res->fetch()) {
			$resultado .= "<option value='". $row[0] ."'>" . $row[1] . " ". $row[2] ." </option>";
		}
		return $resultado;
	}
	
	function ObtenerOpcionesSelectGroup($nombretabla, $nombrecampo, $tablagrupos, $campogrupo, $seleccionado = -1) {
		global $db;
		$resultado = "";
		$grupos = array();
		$res = $db->prepare("SELECT DISTINCT(id) FROM ". $tablagrupos .";");
		$res->execute();
		while ($row = $res->fetch()) {
			$grupos[] = $row[0];
		}
		foreach ( $grupos as $grupo ) {
			$resultado .= "<optgroup label=\"". ObtenerDescripcionDesdeID("empresas",$grupo,"nombre") ."\">";
			$res = $db->prepare("SELECT id,". $nombrecampo ." FROM ". $nombretabla ." WHERE ". $campogrupo ."=". $grupo ." ORDER BY ". $nombrecampo .";");
			$res->execute();
			while ($row = $res->fetch()) {
				if ( $row[0] == $seleccionado ) {
					$resultado .= "<option value='". $row[0] ."' selected>" . $row[1] . "</option>";
				}else{
					$resultado .= "<option value='". $row[0] ."'>" . $row[1] . "</option>";
				}
			}
			$resultado .= "</optgroup>";
		}
		return $resultado;
	}
?>
