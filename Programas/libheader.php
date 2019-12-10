<?php
	require_once "libconfig.php";
	require_once "libdb.php";
	require_once "libphp.php";
	require_once "libuser.php";

	if ( isset($_GET["action"]) && $_GET["action"] == "showheader" ) {
		if ( usuarioEsLogeado() ) {
			echo "<button onClick=\"event.preventDefault();appHome();\">Requisiciones</button>";
			echo "<button onClick=\"event.preventDefault();appPrefereces();\">Preferencias ". ObtenerDescripcionDesdeID("usuarios", usuarioId() ,"usuario") ."</button>";
			if ( usuarioEsSuper() ) {
				echo "<button onClick=\"event.preventDefault();appSettings();\">Configuracion</button>";
			}
			echo "<button onClick=\"event.preventDefault();appHelp();\">Ayuda</button>";
			echo "<button onClick=\"event.preventDefault();appLogout();\">Salir</button>";
		}
		if ( !usuarioEsLogeado() ) {
			echo "<button onClick=\"event.preventDefault();appHome();\">Requisiciones</button>";
			echo "<button onClick=\"event.preventDefault();appHelp();\">Ayuda</button>";
			echo "<button onClick=\"event.preventDefault();appSignin();\">Registrarse</button>";
			echo "<button onClick=\"event.preventDefault();appLogin();\">Acceder</button>";
		}
	}
	
	if ( isset($_GET["action"]) && $_GET["action"] == "showmenu" ) {
		echo "<button onClick=\"event.preventDefault();appNewReq();\">Nueva</button>";
		echo "<select width='15%' id='mostrarrequisiciones' onchange=\"appActualizaVista();\">";
		echo "	<option value=0>Por surtir</option>";
		echo "	<option value=1>Surtidas</option>";
		echo "	<option value=2>Por imprimir</option>";
		echo "	<option value=3>Impresas</option>";
		echo "	<option value=4>Eliminadas</option>";
		echo "	<option value=5>Todas</option>";
		echo "</select>";
		echo "<select width='15%' id='usuariosrequisiciones' onchange=\"appActualizaVista();\" onfocus=\"populateSelectUsers(this,'usuarios','nombre');\"><option value=0>Todos</option></select>";
		echo "<input width='15%' type='text' id='busquedarequisiciones' placeholder='Buscar' onkeyup='appTextBusqueda(event);'></input>";
		echo "<button onClick=\"event.preventDefault();appBusqueda();\">Buscar</button>";
		echo "<select width='15%' id='ordenrequisiciones' onchange=\"appActualizaVista();\">";
		echo "	<option value=0>Por Id</option>";
		echo "	<option value=1>Por empresa</option>";
		echo "	<option value=2>Por requisicion</option>";
		echo "	<option value=3>Por fecha</option>";
		echo "	<option value=4>Por estado</option>";
		echo "	<option value=5>Por departamento</option>";
		echo "	<option value=6>Por area</option>";
		echo "	<option value=7>Por centrocostos</option>";
		echo "	<option value=8>Por solicitante</option>";
		echo "	<option value=9>Por importancia</option>";
		echo "	<option value=10>Por autor</option>";
		echo "</select>";
		echo "<button onClick=\"event.preventDefault();appExportar();\">Exportar</button>";
		echo "<progress id=\"estado\" value=\"0\"></progress>";
	}
?>