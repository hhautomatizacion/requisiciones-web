<?php
	//require_once("libconfig.php");
	//require_once("libdb.php");
	//require_once("libphp.php");
	//require_once("libuser.php");

	if ( isset($_GET["action"]) && $_GET["action"] == "showcredits" ) {
		echo "<table>";
		echo "<tr><td width=\"20%\"><small>Campo</small></td><td width=\"80%\"><small>Valor</small></td></tr>";
		echo "<tr><td>Descripcion</td><td>Sistema para control de requisiciones</td></tr>";
		echo "<tr><td>Version</td><td>0.02</td></tr>";
		echo "<tr><td>Autor</td><td>Emmanuel Castillo</td></tr>";
		echo "<tr><td>Email</td><td>emmanuel156@gmail.com</td></tr>";
		echo "<tr><td>Autor</td><td>Jorge Ramirez</td></tr>";
		echo "<tr><td>Email</td><td>deliriun_jrh@hotmail.com</td></tr>";
		echo "</table>";
	}

	if ( isset($_GET["action"]) && $_GET["action"] == "showhelp" ) {
		echo "<table>";
		echo "<tr><td width=\"20%\"><small>Boton</small></td><td width=\"80%\"><small>Accion</small></td></tr>";
		echo "<tr><td>Requisiciones</td><td>Regresa a la vista principal y pone la busqueda como vacia.</td></tr>";
		echo "<tr><td>Preferencias</td><td>Permite cambiar configuraciones del usuario actual, tales como Nombre de usuario, Correo electronico, Password, etc.</td></tr>";
		echo "<tr><td>Creditos</td><td>Muestra la version del sistema asi como la informacion del autor.</td></tr>";
		echo "<tr><td>Configuracion</td><td>Permite cambiar la configuracion del sistema en general, por ejemplo, usuarios activos, unidades de medida, etc.</td></tr>";
		echo "<tr><td>Ayuda</td><td>Esta pagina de ayuda.</td></tr>";
		echo "<tr><td>Registrarse</td><td>Darse de alta en el sistema.</td></tr>";
		echo "<tr><td>Acceder</td><td>Iniciar sesion. El usuario que no ha iniciado sesion no puede hacer modificaciones.</td></tr>";
		echo "<tr><td>Salir</td><td>Cierra sesion del usuario actual.</td></tr>";
		echo "</table>";
		echo "<table>";
		echo "<tr><td width=\"20%\"><small>Boton</small></td><td width=\"80%\"><small>Accion</small></td></tr>";
		echo "<tr><td>Nueva</td><td>Muestra el formulario para crear una nueva requisicion.</td></tr>";
		echo "<tr><td>Tipo</td><td>Filtra los resultados para mostrar solo las requisiciones del tipo seleccionado.</td></tr>";
		echo "<tr><td>Usuario</td><td>Filtra los resultados para mostrar solo las requisiciones del usuario seleccionado.</td></tr>";
		echo "<tr><td>Busqueda</td><td>El texto a buscar en las requisiciones.</td></tr>";
		echo "<tr><td>Buscar</td><td>Realiza la busqueda del texto en las requisiciones filtrando los resultados por el tipo seleccionado y usario seleccionado. Si la busqueda no regresa resultado muestra un mensaje.</td></tr>";
		echo "<tr><td>Exportar</td><td>Exporta la vista actual en formato de lista a un archivo PDF.</td></tr>";
		echo "</table>";
		echo "<table>";
		echo "<tr><td width=\"20%\"><small>Estado</small></td><td width=\"80%\"><small>Significado</small></td></tr>";
		echo "<tr><td>M</td><td>El usuario es autor o solicitante de la requisicion.</td></tr>";
		echo "<tr><td><div class=\"printed\" style=\"margin-bottom:0px;\">I</div></td><td>La requisicion ha sido impresa.</td></tr>";
		echo "<tr><td><div class=\"supplied\" style=\"margin-bottom:0px;\">S</div></td><td>La requisicion ha sido surtida.</td></tr>";
		echo "<tr><td>E</td><td>La requisicion ha sido eliminada.</td></tr>";
		echo "</table>";
	}
?>
