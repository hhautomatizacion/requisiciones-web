<?php
	require_once("libconfig.php");
	require_once("libuser.php");
	require_once("libphp.php");

	dbConectar();

	if ( isset($_GET["action"]) && $_GET["action"] == "showsettingsform" ) {
		$resultado="";
		$resultado .= TablaElementos2('usuarios','numero','nombre');
		$resultado .= TablaElementos('unidades','unidad');
		$resultado .= TablaElementos('departamentos','departamento');
		$resultado .= TablaElementos('areas','area');
		$resultado .= TablaElementos2('centroscostos','numero','descripcion');
		echo $resultado;
	}
	if ( isset($_GET["action"]) && $_GET["action"] == "activate" ) {
		if ( isset($_GET["id"]) && isset($_GET["setting"]) ) {
			$idelemento=$_GET["id"];
			$setting = $_GET["setting"];
			$res = $db->prepare("UPDATE ". $setting ." SET activo=1 WHERE id=". $idelemento .";");
			$res->execute();
			echo "Activado";
		}
	}
	if ( isset($_GET["action"]) && $_GET["action"] == "deactivate" ) {
		if ( isset($_GET["id"]) && isset($_GET["setting"]) ) {
			$idelemento=$_GET["id"];
			$setting = $_GET["setting"];
			$res = $db->prepare("UPDATE ". $setting ." SET activo=0 WHERE id=". $idelemento .";");
			$res->execute();
			echo "Desactivado";
		}
	}
	if ( isset($_GET["action"]) && $_GET["action"] == "addsetting" ) {
		if ( isset($_GET["setting"]) ) {
			$setting =$_GET["setting"];
			if ( isset($_GET["description"]) &&  isset($_GET["number"]) ) {
				$numero=$_GET["number"];
				$descripcion = $_GET["description"];
				$res = $db->prepare("INSERT INTO ". $setting ." VALUES (0,". $numero .",'". $descripcion ."',1)");
				$res->execute();
			}elseif ( isset($_GET["description"]) ) {
				$descripcion = $_GET["description"];
				$res = $db->prepare("INSERT INTO ". $setting ." VALUES (0,'". $descripcion ."',1)");
				$res->execute();
			}
			echo "Ok";
		}
	}
	if ( isset($_GET["action"]) && $_GET["action"] == "getoptions" ) {
		$tabla=$_GET["table"];
		$descripcion=$_GET["description"];
		$resultado="";
		$resultado= ObtenerOpcionesSelect($tabla,$descripcion);
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
				$res = $db->prepare("CREATE TABLE usuarios (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, numero INT UNSIGNED, nombre VARCHAR(80), usuario VARCHAR(20), email VARCHAR(50), password VARCHAR(40), recoverypw VARCHAR(40), recovery TINYINT(1) UNSIGNED, su TINYINT(1) UNSIGNED, activo TINYINT(1) UNSIGNED) DEFAULT CHARACTER SET utf8;");
				$res->execute();
				$res = $db->prepare("CREATE TABLE opcionesusuarios (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, idusuario INT UNSIGNED, seccion VARCHAR(100), clave VARCHAR(100), valor VARCHAR(300)) DEFAULT CHARACTER SET utf8;");
				$res->execute();
				$res = $db->prepare("CREATE TABLE unidades (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, unidad VARCHAR(10), activo TINYINT(1) UNSIGNED) DEFAULT CHARACTER SET utf8;");
				$res->execute();
				$res = $db->prepare("CREATE TABLE partidas (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, fecha DATETIME, cantidad DECIMAL(12,3) UNSIGNED, idunidad INT UNSIGNED, descripcion VARCHAR(500), activo TINYINT(1) UNSIGNED, surtida TINYINT(1) UNSIGNED, impresa TINYINT(1) UNSIGNED, fechasurtir DATETIME, idcentrocostos INT UNSIGNED, idrequisicion INT UNSIGNED, importancia TINYINT(1) UNSIGNED, idsolicitante INT UNSIGNED, idusuario INT UNSIGNED) DEFAULT CHARACTER SET utf8;");
				$res->execute();
				$res = $db->prepare("CREATE TABLE requisiciones (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, fecha DATETIME, requisicion VARCHAR(10), activo TINYINT(1) UNSIGNED, surtida TINYINT(1) UNSIGNED, impresa TINYINT(1) UNSIGNED, fechasurtir DATETIME, idsurtida INT UNSIGNED, idimpresa INT UNSIGNED, iddepartmento INT UNSIGNED, idarea INT UNSIGNED, idcentrocostos INT UNSIGNED, importancia TINYINT(1) UNSIGNED, idsolicitante INT UNSIGNED, idusuario INT UNSIGNED) DEFAULT CHARACTER SET utf8;");
				$res->execute();
				$res = $db->prepare("CREATE TABLE comentariospartidas (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, idpartida INT UNSIGNED, idpadre INT UNSIGNED, comentario TEXT, idusuario INT UNSIGNED, fecha DATETIME, activo TINYINT(1) UNSIGNED) DEFAULT CHARACTER SET utf8;");
				$res->execute();
				$res = $db->prepare("CREATE TABLE comentariosrequisiciones (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, idrequisicion INT UNSIGNED, idpadre INT UNSIGNED, comentario TEXT, idusuario INT UNSIGNED, fecha DATETIME, activo TINYINT(1) UNSIGNED) DEFAULT CHARACTER SET utf8;");
				$res->execute();
				$res = $db->prepare("CREATE TABLE adjuntospartidas (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, idpartida INT UNSIGNED, nombre VARCHAR(300), longitud INT UNSIGNED, idusuario INT UNSIGNED, fecha DATETIME, activo TINYINT(1) UNSIGNED) DEFAULT CHARACTER SET utf8;");
				$res->execute();
				$res = $db->prepare("CREATE TABLE adjuntosrequisiciones (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, idrequisicion INT UNSIGNED, nombre VARCHAR(300), longitud INT UNSIGNED, idusuario INT UNSIGNED, fecha DATETIME, activo TINYINT(1) UNSIGNED) DEFAULT CHARACTER SET utf8;");
				$res->execute();
				$res = $db->prepare("CREATE TABLE departamentos (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, departamento VARCHAR(20), activo TINYINT(1) UNSIGNED) DEFAULT CHARACTER SET utf8;;");
				$res->execute();
				$res = $db->prepare("CREATE TABLE areas (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, area VARCHAR(20), activo TINYINT(1) UNSIGNED) DEFAULT CHARACTER SET utf8;");
				$res->execute();
				$res = $db->prepare("CREATE TABLE centroscostos (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, numero INT UNSIGNED, descripcion VARCHAR(20), activo TINYINT(1) UNSIGNED) DEFAULT CHARACTER SET utf8;");
				$res->execute();
				$res = $db->prepare("CREATE TABLE notificacionesrequisiciones (id INT AUTO_INCREMENT PRIMARY KEY, fecha DATETIME, clave INT, idrequisicion INT, idusuario INT, activo TINYINT(1)) DEFAULT CHARACTER SET utf8;");
				$res->execute();
				$res = $db->prepare("CREATE TABLE notificacionespartidas (id INT AUTO_INCREMENT PRIMARY KEY, fecha DATETIME, clave INT, idpartida INT, idusuario INT, activo TINYINT(1)) DEFAULT CHARACTER SET utf8;");
				$res->execute();
				$res = $db->prepare("INSERT INTO usuarios VALUES (0,NULL,'root','root','',SHA1('manttocl'),'',0,1,1);");
				$res->execute();
			}
		}
		catch (Exception $error) {
			writelog("error ". $error->getMessage());
		}

	}
	function ObtenerDescripcionDesdeID($tabla, $id, $campo) {
		global $db;
		$temp="";
		$res = $db->prepare("SELECT ". $campo ." FROM ". $tabla ." WHERE id=". $id .";");
		$res->execute();
		while ($row = $res->fetch()) {
			$temp=$row[0];
		}
		return $temp;
	}
	function ObtenerUsuariosSelect() {
		global $db;
		$resultado="";
		$res = $db->prepare("SELECT id, nombre FROM usuarios WHERE activo=1 ORDER BY nombre");
		$res->execute();
		while ($row = $res->fetch()) {
			if ( usuarioEsLogeado() && $row[0]==$_COOKIE["usuario"] ) {
				$resultado .= "<option value='". $row[0] ."' selected>" . $row[1] . "</option>";
			}else{
				$resultado .= "<option value='". $row[0] ."'>" . $row[1] . "</option>";
			}
		}
		return $resultado;
	}
	function ObtenerOpcionesSelect($nombretabla,$nombrecampo) {
		global $db;
		$resultado="";
		$res = $db->prepare("SELECT id,". $nombrecampo ." FROM ". $nombretabla ." WHERE activo=1 ORDER BY ". $nombrecampo);
		$res->execute();
		while ($row = $res->fetch()) {
			$resultado .= "<option value='". $row[0] ."'>" . $row[1] . "</option>";
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

	function TablaElementos($nombretabla,$descripcion) {
		global $db;
		$resultado="";
		$res = $db->prepare("SELECT id," . $descripcion . ",activo FROM " . $nombretabla ." ORDER BY " . $descripcion);
		$res->execute();
		$resultado .= "<div><table id='". $nombretabla ."'>";
		$resultado .= '<tr><td colspan=3><big>'. $nombretabla .'</big></td></tr>';
		$resultado .= '<tr><td width="15%"><small>id</small></td><td width="70%"><small>'.$descripcion.'</small></td><td width="15%"><small><input type = "button" value = "Agregar" onclick = "addSetting(\''  . $nombretabla  .'\',\'' . $descripcion . '\');"></small></td></tr>';
		while ($row = $res->fetch()) {
			$resultado .= "<tr>";
			if ( $row[2] == "1" ) {
				$resultado .= "<td>". $row[0] ."</td><td>" . $row[1] . "</td><td><button onClick=\"event.preventDefault();appDesactivarSetting('". $nombretabla ."',". $row[0] .");\">Desactivar</button></td>";
			}else{
				$resultado .= "<td>". $row[0] ."</td><td>" . $row[1] . "</td><td><button onClick=\"event.preventDefault();appActivarSetting('". $nombretabla ."',". $row[0] .");\">Activar</button></td>";
			}
			$resultado .= "</tr>";
		}
		$resultado .= "</table></div>\n";
		return $resultado;
	}
	function TablaElementos2($nombretabla,$numero,$descripcion) {
		global $db;
		$resultado="";

		$res = $db->prepare("SELECT id,". $numero ."," . $descripcion . ",activo FROM " . $nombretabla ." ORDER BY " . $descripcion);
		$res->execute();
		$resultado .= "<div><table id='". $nombretabla ."'>";
		$resultado .= '<tr><td colspan=4><big>'. $nombretabla .'</big></td></tr>';
		$resultado .= '<tr><td width="15%"><small>id</small></td><td width="20%"><small>'.$numero.'</small></td><td width="50%"><small>'.$descripcion.'</small></td><td width="15%"><small><input type = "button" value = "Agregar" onclick = "addSetting2(\''  . $nombretabla  .'\',\'' . $numero . '\',\'' . $descripcion . '\');"></small></td></tr>';
		while ($row = $res->fetch()) {
			$resultado .= "<tr>";
			if ( $row[3] == "1" ) {
				$resultado .= "<td>". $row[0] ."</td><td>" . $row[1] . "</td><td>" . $row[2] . "</td><td><button onClick=\"event.preventDefault();appDesactivarSetting('". $nombretabla ."',". $row[0] .");\">Desactivar</button></td>";
			}else{
				$resultado .= "<td>". $row[0] ."</td><td>" . $row[1] . "</td><td>" . $row[2] . "</td><td><button onClick=\"event.preventDefault();appActivarSetting('". $nombretabla ."',". $row[0] .");\">Activar</button></td>";
			}
			$resultado .= "</tr>";
		}
		$resultado .= "</table></div>\n";
		return $resultado;
	}
?>
