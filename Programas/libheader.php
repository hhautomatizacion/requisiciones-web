<?php
	require_once("libconfig.php");
	require_once("libdb.php");
	require_once("libphp.php");
	require_once("libuser.php");

	
	if ( isset($_GET["action"]) && $_GET["action"] == "showheader" ) {
		
		echo "<button onClick=\"event.preventDefault();appHome();\">Requisiciones</button>";
		
		if ( usuarioEsSuper() ) {	
			echo "<button onClick=\"event.preventDefault();appSettings();\">Configuracion</button>";
		}
		if ( usuarioEsLogeado() ) {
			echo "<button onClick=\"event.preventDefault();appPrefereces();\">Preferencias ". usuarioNombre() ."</button>";
			echo "<button onClick=\"event.preventDefault();appLogout();\">Salir</button>";
		}
		if ( !usuarioEsLogeado() ) {
			echo "<button onClick=\"event.preventDefault();appSignin();\">Registrarse</button>";
			echo "<button onClick=\"event.preventDefault();appLogin();\">Acceder</button>";
		}
	}
	
	if ( isset($_GET["action"]) && $_GET["action"] == "showmenu" ) {
		echo "<button onClick=\"event.preventDefault();appNewReq();\">Nueva</button>";
		echo "<select width='15%' id='mostrarrequisiciones' onchange=\"appActualizaVista();\"><option value=0>Por surtir</option><option value=1>Surtidas</option><option value=2>Por imprimir</option><option value=3>Impresas</option><option value=4>Eliminadas</option><option value=5>Todas</option></select>";
		echo "<select width='15%' id='usuariosrequisiciones' onchange=\"appActualizaVista();\" onfocus=\"populateUsersCombo(this,'usuarios','nombre');\"><option value=0>Todos</option></select>";
		echo "<input width='15%' type='text' id='busquedarequisiciones' placeholder='Buscar' onkeyup='appTextBusqueda(event);'></input>";
		echo "<button onClick=\"event.preventDefault();appBusqueda();\">Buscar</button>";
		echo "<progress id=\"estado\" value=\"0\"></progress>";
	}
?>