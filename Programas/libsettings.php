<?php
	require_once "libconfig.php";
	require_once "libuser.php";
	require_once "libphp.php";
	
	if ( isset($_GET["action"]) && $_GET["action"] == "showsettingsform" ) {
		$resultado="";
		$resultado .= TablaElementos2('usuarios','numero','nombre');
		$resultado .= TablaElementos('unidades','unidad');
		$resultado .= TablaEmpresas('empresas','codigo','nombre');
		//$resultado .= TablaElementos('departamentos','departamento');
		$resultado .= TablaDepartamentos('departamentos','departamento');
		$resultado .= TablaAreas('areas','area');
		//$resultado .= TablaElementos2('centroscostos','numero','descripcion');
		$resultado .= TablaCentrosCostos('centroscostos','numero','descripcion');
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
			switch ($setting) {
				case "centroscostos":
					$numero = $_GET["numero"];
					$descripcion = $_GET["description"];
					$empresa = $_GET["empresa"];
					$res = $db->prepare("INSERT INTO ". $setting ." VALUES (0,". $empresa .",". $numero .",'". $descripcion ."',1)");
					$res->execute();
					break;
				case "areas":
					$descripcion = $_GET["description"];
					$empresa = $_GET["empresa"];
					$res = $db->prepare("INSERT INTO ". $setting ." VALUES (0,". $empresa .",'". $descripcion ."',1)");
					$res->execute();
					break;
				case "departamentos":
					$descripcion = $_GET["description"];
					$empresa = $_GET["empresa"];
					$res = $db->prepare("INSERT INTO ". $setting ." VALUES (0,". $empresa .",'". $descripcion ."',1)");
					$res->execute();
					break;
				case "empresas":
					$descripcion = $_GET["description"];
					$codigo = $_GET["codigo"];
					$res = $db->prepare("INSERT INTO ". $setting ." VALUES (0,". $codigo .",'". $descripcion ."',1)");
					$res->execute();
					break;
				case "unidades":
					$descripcion = $_GET["description"];
					$res = $db->prepare("INSERT INTO ". $setting ." VALUES (0,'". $descripcion ."',1)");
					$res->execute();
					break;
			}
			// if ( isset($_GET["description"]) &&  isset($_GET["codigo"]) ) {
				// $codigo=$_GET["codigo"];
				// $descripcion = $_GET["description"];
				// $res = $db->prepare("INSERT INTO ". $setting ." VALUES (0,'". $codigo ."','". $descripcion ."',1)");
				// $res->execute();
			// }elseif ( isset($_GET["description"]) &&  isset($_GET["number"]) ) {
				// $numero=$_GET["number"];
				// $descripcion = $_GET["description"];
				// $res = $db->prepare("INSERT INTO ". $setting ." VALUES (0,". $numero .",'". $descripcion ."',1)");
				// $res->execute();
			// }
			echo "Ok";
		}
	}
	
	function TablaElementos($nombretabla,$descripcion) {
		global $db;
		$resultado="";
		$res = $db->prepare("SELECT id," . $descripcion . ",activo FROM " . $nombretabla ." ORDER BY " . $descripcion);
		$res->execute();
		$resultado .= "<div><table id='". $nombretabla ."'>";
		$resultado .= '<tr><td colspan=3><big>'. $nombretabla .'</big></td></tr>';
		$resultado .= '<tr><td width="10%"><small>id</small></td><td width="70%"><small>'.$descripcion.'</small></td><td width="20%"><small><input type = "button" value = "Agregar" onclick = "addSetting(\''  . $nombretabla  .'\',\'' . $descripcion . '\');"></small></td></tr>';
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
		$resultado .= '<tr><td width="10%"><small>id</small></td><td width="20%"><small>'.$numero.'</small></td><td width="50%"><small>'.$descripcion.'</small></td><td width="20%"><small><input type = "button" value = "Agregar" onclick = "addSetting2(\''  . $nombretabla  .'\',\'' . $numero . '\',\'' . $descripcion . '\');"></small></td></tr>';
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

	function TablaCentrosCostos($nombretabla,$numero,$descripcion) {
		global $db;
		$resultado="";
		$res = $db->prepare("SELECT id, idempresa, ". $numero ."," . $descripcion . ",activo FROM " . $nombretabla ." ORDER BY idempresa, " . $descripcion);
		$res->execute();
		$resultado .= "<div><table id='". $nombretabla ."'>";
		$resultado .= '<tr><td colspan=5><big>'. $nombretabla .'</big></td></tr>';
		$resultado .= '<tr><td width="10%"><small>id</small></td><td width="15%"><small>empresa</small></td><td width="15%"><small>'.$numero.'</small></td><td width="40%"><small>'.$descripcion.'</small></td><td width="20%"><small><input type = "button" value = "Agregar" onclick = "addSettingEmpresaNumero(\''  . $nombretabla  .'\',\'' . $numero . '\',\'' . $descripcion . '\');"></small></td></tr>';
		while ($row = $res->fetch()) {
			$resultado .= "<tr>";
			if ( $row[3] == "1" ) {
				$resultado .= "<td>". $row[0] ."</td><td>" . ObtenerDescripcionDesdeId("empresas",$row[1],"nombre") . "</td><td>" . $row[2] . "</td><td>" . $row[3] . "</td><td><button onClick=\"event.preventDefault();appDesactivarSetting('". $nombretabla ."',". $row[0] .");\">Desactivar</button></td>";
			}else{
				$resultado .= "<td>". $row[0] ."</td><td>" . ObtenerDescripcionDesdeId("empresas",$row[1],"nombre") . "</td><td>" . $row[2] . "</td><td>" . $row[3] . "</td><td><button onClick=\"event.preventDefault();appActivarSetting('". $nombretabla ."',". $row[0] .");\">Desactivar</button></td>";
			}
			$resultado .= "</tr>";
		}
		$resultado .= "</table></div>\n";
		return $resultado;
	}
	
	function TablaEmpresas($nombretabla,$numero,$descripcion) {
		global $db;
		$resultado="";
		$res = $db->prepare("SELECT id,". $numero ."," . $descripcion . ",activo FROM " . $nombretabla ." ORDER BY " . $descripcion);
		$res->execute();
		$resultado .= "<div><table id='". $nombretabla ."'>";
		$resultado .= '<tr><td colspan=4><big>'. $nombretabla .'</big></td></tr>';
		$resultado .= '<tr><td width="10%"><small>id</small></td><td width="20%"><small>'.$numero.'</small></td><td width="50%"><small>'.$descripcion.'</small></td><td width="20%"><small><input type = "button" value = "Agregar" onclick = "addSettingCodigo(\''  . $nombretabla  .'\',\'' . $numero . '\',\'' . $descripcion . '\');"></small></td></tr>';
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
	
	function TablaDepartamentos($nombretabla,$descripcion) {
		global $db;
		$resultado="";
		$res = $db->prepare("SELECT id, idempresa," . $descripcion . ",activo FROM " . $nombretabla ." ORDER BY " . $descripcion);
		$res->execute();
		$resultado .= "<div><table id='". $nombretabla ."'>";
		$resultado .= '<tr><td colspan=4><big>'. $nombretabla .'</big></td></tr>';
		$resultado .= '<tr><td width="10%"><small>id</small></td><td width="20%"><small>empresa</small></td><td width="50%"><small>'.$descripcion.'</small></td><td width="20%"><small><input type = "button" value = "Agregar" onclick = "addSettingEmpresa(\''  . $nombretabla  .'\',\'' . $descripcion . '\');"></small></td></tr>';
		while ($row = $res->fetch()) {
			$resultado .= "<tr>";
			if ( $row[3] == "1" ) {
				$resultado .= "<td>". $row[0] ."</td><td>" . ObtenerDescripcionDesdeId("empresas",$row[1],"nombre") . "</td><td>" . $row[2] . "</td><td><button onClick=\"event.preventDefault();appDesactivarSetting('". $nombretabla ."',". $row[0] .");\">Desactivar</button></td>";
			}else{
				$resultado .= "<td>". $row[0] ."</td><td>" . ObtenerDescripcionDesdeId("empresas",$row[1],"nombre") . "</td><td>" . $row[2] . "</td><td><button onClick=\"event.preventDefault();appActivarSetting('". $nombretabla ."',". $row[0] .");\">Activar</button></td>";
			}
			$resultado .= "</tr>";
		}
		$resultado .= "</table></div>\n";
		return $resultado;
	}
	
	function TablaAreas($nombretabla,$descripcion) {
		global $db;
		$resultado="";
		$res = $db->prepare("SELECT id, idempresa," . $descripcion . ",activo FROM " . $nombretabla ." ORDER BY " . $descripcion);
		$res->execute();
		$resultado .= "<div><table id='". $nombretabla ."'>";
		$resultado .= '<tr><td colspan=4><big>'. $nombretabla .'</big></td></tr>';
		$resultado .= '<tr><td width="10%"><small>id</small></td><td width="20%"><small>empresa</small></td><td width="50%"><small>'.$descripcion.'</small></td><td width="20%"><small><input type = "button" value = "Agregar" onclick = "addSettingEmpresa(\''  . $nombretabla  .'\',\'' . $descripcion . '\');"></small></td></tr>';
		while ($row = $res->fetch()) {
			$resultado .= "<tr>";
			if ( $row[3] == "1" ) {
				$resultado .= "<td>". $row[0] ."</td><td>" . ObtenerDescripcionDesdeId("empresas",$row[1],"nombre") . "</td><td>" . $row[2] . "</td><td><button onClick=\"event.preventDefault();appDesactivarSetting('". $nombretabla ."',". $row[0] .");\">Desactivar</button></td>";
			}else{
				$resultado .= "<td>". $row[0] ."</td><td>" . ObtenerDescripcionDesdeId("empresas",$row[1],"nombre") . "</td><td>" . $row[2] . "</td><td><button onClick=\"event.preventDefault();appActivarSetting('". $nombretabla ."',". $row[0] .");\">Activar</button></td>";
			}
			$resultado .= "</tr>";
		}
		$resultado .= "</table></div>\n";
		return $resultado;
	}
	
	function guardarPreferenciaGlobal($seccion, $clave, $valor) {
		global $db;
		$resultado = 0;
		$res = $db->prepare("SELECT id FROM opciones WHERE seccion= ? AND clave= ?;");
		$res->execute([$seccion, $clave]);
		while ($row = $res->fetch()) {
			$resultado = $row[0];
		}
		if ( $resultado > 0 ) {
			$res = $db->prepare("UPDATE opciones SET valor= ? WHERE seccion= ? AND clave= ?;");
			$res->execute([$valor, $seccion, $clave]);
		}
		else
		{
			$res = $db->prepare("INSERT INTO opciones VALUES (0, ?, ?, ?);");
			$res->execute([$seccion, $clave, $valor]);
		}
		return $resultado;
	}

	function obtenerPreferenciaGlobal($seccion, $clave, $default='') {
		global $db;
		$resultado = $default;
		$encontrado = false;
		$res = $db->prepare("SELECT valor FROM opciones WHERE seccion= ? AND clave= ?;");
		$res->execute([$seccion, $clave]);
		while ($row = $res->fetch()) {
			$resultado = $row[0];
			$encontrado = true;
		}
		if (!$encontrado) {
			guardarPreferenciaGlobal($seccion, $clave, $resultado);
		}
		return $resultado;
	}
?>