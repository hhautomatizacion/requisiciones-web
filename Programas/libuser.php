<?php
	use PHPMailer\PHPMailer\PHPMailer;

	require_once "PHPMailer.php";
	require_once "SMTP.php";
	require_once "libconfig.php";
	require_once "libdb.php";
	require_once "libphp.php";

	if ( isset($_GET["action"]) && $_GET["action"] == "showpreferencesform" ) {
		$resultado= formPreferencesForm();
		echo $resultado;
	}

	if ( isset($_GET["action"]) && $_GET["action"] == "showsigninform" ) {
		$resultado= formSigninForm();
		echo $resultado;
	}

	if ( isset($_GET["action"]) && $_GET["action"] == "showlostpasswordform" ) {
		$resultado= formLostpasswordForm();
		echo $resultado;
	}

	if ( isset($_GET["action"]) && $_GET["action"] == "showloginform" ) {
		$resultado= formLoginForm();
		echo $resultado;
	}

	if ( isset($_GET["action"]) && $_GET["action"] == "logout" ) {
		setcookie("usuario","");
		//usuarioDesactivarToken();
		echo "OK";
	}

	if ( isset($_POST["action"]) && $_POST["action"] == "login" ) {
		$errores=array();
		$validos=array();
		$nombre=$_POST["name"];
		$password=$_POST["password"];
		if ( strlen($nombre) == 0) {
			$errores[] = "name";
		} else {
			$validos[] = "name";
		}
		if ( strlen($password) == 0) {
			$errores[] = "password";
		} else {
			$validos[] = "password";
		}
		if ( count($errores) == 0 ) {
			if (usuarioDarEntrada(usuarioVerificarCredenciales($nombre, $password)) > 0) {
				echo json_encode(array('succes' => 1));
				/* if ( isset($_POST["autologin"]) && $_POST["autologin"] == "on" ) {
					usuarioActivarToken();
				} */
			}else{
				$errores[]="loginform";
				echo json_encode(array('succes' => 0, 'errors' => $errores, 'validos' => $validos));
			}
		}else{
			$validos[]="loginform";
			echo json_encode(array('succes' => 0, 'errors' => $errores, 'validos' => $validos));
		}
	}

	if ( isset($_POST["action"]) && $_POST["action"] == "signin" ) {
		$encontrado = false;
		$errores = array();
		$validos = array();
		if ( intval($_REQUEST["numero"]) <= 0 ){
			$errores[] = "numero";
		} else {
			$encontrado = false;
			$res = $db->prepare("SELECT * FROM usuarios WHERE numero=" . intval($_REQUEST["numero"]) .";");
			$res->execute();
			while ($row = $res->fetch()) {
				$encontrado = true;
			}
			if ( $encontrado ) {
				$errores[] = "numero";
			} else {
				$validos[] = "numero";
			}
		}
		if ( strlen($_REQUEST["nombre"]) == 0) {
			$errores[] = "nombre";
		} else {
			$validos[] = "nombre";
		}
		if ( strlen($_REQUEST["email"]) == 0) {
			$errores[] = "email";
		} else {
			$validos[] = "email";
		}
		$encontrado = false;
		$res = $db->prepare("SELECT * FROM usuarios WHERE LCASE(nombre)=LCASE('". $_REQUEST["nombre"]."') OR LCASE(usuario)=LCASE('". $_REQUEST["nombre"] ."') OR LCASE(email)=LCASE('". $_REQUEST["nombre"] ."');");
		$res->execute();
		while ($row = $res->fetch()) {
			$encontrado = true;
		}
		if ( $encontrado ) {
			$errores[] = "nombre";
		} else {
			$validos[] = "nombre";
		}
		$encontrado = false;
		$res = $db->prepare("SELECT * FROM usuarios WHERE LCASE(nombre)=LCASE('". $_REQUEST["usuario"]."') OR LCASE(usuario)=LCASE('". $_REQUEST["usuario"] ."') OR LCASE(email)=LCASE('". $_REQUEST["usuario"] ."');");
		$res->execute();
		while ($row = $res->fetch()) {
			$encontrado = true;
		}
		if ( $encontrado ) {
			$errores[] = "usuario";
		} else {
			$validos[] = "usuario";
		}
		$encontrado = false;
		$res = $db->prepare("SELECT * FROM usuarios WHERE LCASE(nombre)=LCASE('". $_REQUEST["email"]."') OR LCASE(usuario)=LCASE('". $_REQUEST["email"] ."') OR LCASE(email)=LCASE('". $_REQUEST["email"] ."');");
		$res->execute();
		while ($row = $res->fetch()) {
			$encontrado = true;
		}
		if ( $encontrado ) {
			$errores[] = "email";
		} else {
			$validos[] = "email";
		}
		if ( strlen($_REQUEST["password1"]) == 0 || $_REQUEST["password1"] <> $_REQUEST["password2"] ) {
			$errores[] = "password1";
		} else {
			$validos[] = "password1";
		}
		if ( strlen($_REQUEST["password2"]) == 0 || $_REQUEST["password1"] <> $_REQUEST["password2"] ) {
			$errores[] = "password2";
		} else {
			$validos[] = "password2";
		}
		if ( count($errores) == 0 ){
			$res = $db->prepare("INSERT INTO usuarios VALUES (0,". $_REQUEST["numero"] .",'". $_REQUEST["nombre"] ."','". $_REQUEST["usuario"] ."','". $_REQUEST["email"] ."',SHA1('". $_REQUEST["password1"] ."'), NULL, NULL, '', 0, 0, 1);");
			$res->execute();
			echo json_encode(array('succes' => 1));
		} else {
			echo json_encode(array('succes' => 0, 'errors' => $errores, 'validos' => $validos));
		}
	}

	if ( isset($_POST["action"]) && $_POST["action"] == "editpassword" ) {
		$errores = array();
		$validos = array();
		if ( strlen($_REQUEST["password1"]) == 0 || $_REQUEST["password1"] <> $_REQUEST["password2"] ) {
			$errores[]="password1";
		} else {
			$validos[]="password1";
		}
		if ( strlen($_REQUEST["password2"]) == 0 || $_REQUEST["password1"] <> $_REQUEST["password2"] ) {
			$errores[]="password2";
		} else {
			$validos[]="password2";
		}
		if ( count($errores) == 0 ){
			$res = $db->prepare("UPDATE usuarios SET password=SHA1('". $_REQUEST["password1"] ."') WHERE id=". usuarioId() .";");
			$res->execute();
			echo json_encode(array('succes' => 1));
		} else {
			echo json_encode(array('succes' => 0, 'errors' => $errores, 'validos' => $validos));
		}
	}

	if ( isset($_POST["action"]) && $_POST["action"] == "edituser" ) {
		$errores = array();
		$validos = array();
		$numero = intval($_REQUEST["numero"]);
		$nombre = $_REQUEST["nombre"];
		$usuario = $_REQUEST["usuario"];
		$email = $_REQUEST["email"];
		if ( $numero < 0 ){
			$errores[] = "numero";
		} else {
			$id = usuarioId();
			$res = $db->prepare("SELECT id FROM usuarios WHERE numero=". $numero .";");
			$res->execute();
			while ($row = $res->fetch()) {
				$id = $row[0];
			}
			if ( $id == usuarioId() ) {
				$validos[] = "numero";
			} else {
				$errores[] = "numero";
			}
		}
		if ( strlen($nombre) == 0 ){
			$errores[] = "nombre";
		} else {
			$id = usuarioId();
			$res = $db->prepare("SELECT id FROM usuarios WHERE LCASE(nombre)=LCASE('". $nombre ."') OR LCASE(usuario)=LCASE('". $nombre ."') OR LCASE(email)=LCASE('". $nombre ."');");
			$res->execute();
			while ($row = $res->fetch()) {
				$id=$row[0];
			}
			if ( $id == usuarioId() ) {
				$validos[] = "nombre";
			} else {
				$errores[] = "nombre";
			}
		}
		if ( strlen($usuario) == 0 ){
			$errores[] = "usuario";
		} else {
			$id = usuarioId();
			$res = $db->prepare("SELECT id FROM usuarios WHERE LCASE(nombre)=LCASE('". $usuario ."') OR LCASE(usuario)=LCASE('". $usuario ."') OR LCASE(email)=LCASE('". $usuario ."');");
			$res->execute();
			while ($row = $res->fetch()) {
				$id=$row[0];
			}
			if ( $id == usuarioId() ) {
				$validos[] = "usuario";
			} else {
				$errores[] = "usuario";
			}
		}
		if ( strlen($email) == 0 ){
			$errores[] = "email";
		} else {
			$id = usuarioId();
			$res = $db->prepare("SELECT id FROM usuarios WHERE LCASE(nombre)=LCASE('". $email ."') OR LCASE(usuario)=LCASE('". $email ."') OR LCASE(email)=LCASE('". $email ."');");
			$res->execute();
			while ($row = $res->fetch()) {
				$id=$row[0];
			}
			if ( $id == usuarioId() ) {
				$validos[] = "email";
			} else {
				$errores[] = "email";
			}
		}
		if ( count($errores) == 0 ){
			$res = $db->prepare("UPDATE usuarios SET numero=". $numero .", nombre='". $nombre ."', usuario='". $usuario ."', email='". $email ."' WHERE id=". usuarioId() .";");
			$res->execute();
			echo json_encode(array('succes' => 1));
		}else{
			echo json_encode(array('succes' => 0, 'errors' => $errores, 'validos' => $validos));
		}
	}

	if ( isset($_POST["action"]) && $_POST["action"] == "lostpassword" ) {
		if ( $_POST["name"] ) {
			$resultado="";
			$name=$_POST["name"];
			$res = $db->prepare("SELECT id,email,activo FROM usuarios WHERE (numero=? OR LCASE(nombre)=LCASE(?) OR LCASE(usuario)=LCASE(?) OR LCASE(email)=LCASE(?))");
			$res->execute([$name, $name, $name, $name]);
			while ($row = $res->fetch()) {
				if ($row[1]){
					$recoverypw=randomString(6);
					if ( intval($row[2]) ) {
						$res = $db->prepare("UPDATE usuarios SET recovery=1, recoverypw=SHA1('". $recoverypw ."') WHERE id=". $row[0]);
						$res->execute();
						enviarPorCorreo($row[1],"Recuperacion de password","Su nuevo password temporal es ". $recoverypw ."\nEste password es valido solo por una ocasion.\nSi usted no ha solicitado esta informacion puede seguir usando su password actual.");
					}else{
						enviarPorCorreo($row[1],"Su cuenta esta desactivada","Su cuenta de usuario esta desactivada. Pongase en contacto con el administrador del sitio.");
					}
					$resultado="OK";
				}
			}
			echo $resultado;
		}
	}

	function enviarPorCorreo($direccion, $asunto, $mensaje) {
		$mail_server = obtenerPreferenciaGlobal("mail","server","128.128.5.243");
		$mail_port = obtenerPreferenciaGlobal("mail","port","25");
		$mail_user = obtenerPreferenciaGlobal("mail","user","mttocl");
		$mail_pass = obtenerPreferenciaGlobal("mail","pass","lcottm");
		$mail_fromaddress = obtenerPreferenciaGlobal("mail","fromaddres","mttocl@cualquierlavado.com.mx");
		$mail_fromname = obtenerPreferenciaGlobal("mail","fromname","MantenimientoCL");

		$mail = new PHPMailer(true);
		try
		{
			$mail->IsSMTP();
			$mail->Host = $mail_server;
			$mail->SMTPAuth = true;
			$mail->Username = $mail_user;
			$mail->Password = $mail_pass;
			$mail->SMTPSecure = 'tls';
			$mail->port = $mail_port;
			$mail->setFrom($mail_fromaddress,$mail_fromname);
			$mail->addAddress($direccion);
			$mail->IsHTML(true);
			$mail->CharSet = 'utf-8';
			$mail->Subject=$asunto;
			$mail->Body=$mensaje;
			$mail->send();
		}
		catch (Exception $e)
		{
			writelog('Error al enviar correo: '. $asunto . ' a '. $direccion);
		}
	}

	function formPreferencesForm() {
		$numero = ObtenerDescripcionDesdeID("usuarios", usuarioId(), "numero");
		$nombre = ObtenerDescripcionDesdeID("usuarios", usuarioId(), "nombre");
		$usuario = ObtenerDescripcionDesdeID("usuarios", usuarioId(), "usuario");
		$email = ObtenerDescripcionDesdeID("usuarios", usuarioId(), "email");

		$resultado="";
		$resultado .="<form id=\"edituserform\" method = \"POST\">";
		$resultado .="	<input type=\"hidden\" name=\"action\" value=\"edituser\"/>";
		$resultado .="	<table>";
		$resultado .="		<tr>";
		$resultado .="			<td width=\"80%\">";
		$resultado .="				<table>	";
		$resultado .="					<tr>";
		$resultado .="						<td width=\"20%\"><small>Numero de empleado:</small></td>";
		$resultado .="						<td width=\"80%\"><input id = \"numero\" type = \"number\" min=\"0\" name = \"numero\" value = \"". $numero ."\" /></td>";
		$resultado .="					</tr>";
		$resultado .="					<tr>";
		$resultado .="						<td width=\"20%\"><small>Nombre:</small></td>";
		$resultado .="						<td width=\"80%\"><input id = \"nombre\" type = \"text\" name = \"nombre\" value = \"". $nombre ."\" /></td>";
		$resultado .="					</tr>";
		$resultado .="					<tr>";
		$resultado .="						<td width=\"20%\"><small>Usuario:</small></td>";
		$resultado .="						<td width=\"80%\"><input id = \"usuario\" type = \"text\" name = \"usuario\" value = \"". $usuario ."\" /></td>";
		$resultado .="					</tr>";
		$resultado .="					<tr>";
		$resultado .="						<td width=\"20%\"><small>Email:</small></td>";
		$resultado .="						<td width=\"80%\"><input id = \"email\" type = \"email\" name = \"email\" value = \"". $email ."\" /></td>";
		$resultado .="					</tr>";
		$resultado .="				</table>";
		$resultado .="			</td>";
		$resultado .="			<td width=\"20%\">";
		$resultado .="				<button onClick=\"event.preventDefault();appEnviarEditUser();\">Guardar</button>";
		$resultado .="			</td>";
		$resultado .="		</tr>";
		$resultado .="	</table>";
		$resultado .="</form>";
		$resultado .="<form id=\"editpasswordform\" method = \"POST\">";
		$resultado .="	<input type=\"hidden\" name=\"action\" value=\"editpassword\"/>";
		$resultado .="	<table>";
		$resultado .="		<tr>";
		$resultado .="			<td width=\"80%\">";
		$resultado .="				<table>	";
		$resultado .="					<tr>";
		$resultado .="						<td width=\"20%\"><small>Password:</small></td>";
		$resultado .="						<td width=\"80%\"><input id=\"password1\" type = \"password\" name = \"password1\" /></td>";
		$resultado .="					</tr>";
		$resultado .="					<tr>";
		$resultado .="						<td width=\"20%\"><small>Repetir password:</small></td>";
		$resultado .="						<td width=\"80%\"><input id=\"password2\" type = \"password\" name = \"password2\" /></td>";
		$resultado .="					</tr>";
		$resultado .="				</table>";
		$resultado .="			</td>";
		$resultado .="			<td width=\"20%\">";
		$resultado .="				<button onClick=\"event.preventDefault();appEnviarEditPassword();\">Cambiar</button>";
		$resultado .="			</td>";
		$resultado .="		</tr>";
		$resultado .="	</table>";
		$resultado .="</form>";
		return $resultado;
	}

	function formLostpasswordForm() {
		$resultado="";
		$resultado .="			<form id=\"lostpasswordform\" action=\"libuser.php\" method=\"POST\">";
		$resultado .="				<input type=\"hidden\" name=\"action\" value=\"lostpassword\">";
		$resultado .="				<table>";
		$resultado .="					<tr>";
		$resultado .="						<td width=\"80%\">";
		$resultado .="						<table>";
		$resultado .="							<tr>";
		$resultado .="								<td width=\"20%\"><small>Usuario:</small></td>";
		$resultado .="								<td><input type = \"text\" name = \"name\" /></td>";
		$resultado .="							</tr>";
		$resultado .="						</table>";
		$resultado .="						</td>";
		$resultado .="						<td width=\"20%\">";
		$resultado .="						<button onClick=\"event.preventDefault();appEnviarPassword();\">Enviar</button>";
		$resultado .="						</td>";
		$resultado .="					</tr>";
		$resultado .="				</table>";
		$resultado .="			</form>";
		return $resultado;
	}

	function formLoginForm() {
		$resultado="";
		$resultado .="<form id=\"loginform\" action=\"libuser.php\" method=\"POST\">";
		$resultado .="	<input type=\"hidden\" name=\"action\" value=\"login\">";
		$resultado .="	<table>";
		$resultado .="		<tr>";
		$resultado .="			<td width=\"80%\">";
		$resultado .="			<table>";
		$resultado .="				<tr>";
		$resultado .="					<td width=\"20%\"><small>Usuario:</small></td>";
		$resultado .="					<td><input id=\"name\" type = \"text\" name = \"name\" /></td>";
		$resultado .="				</tr>";
		$resultado .="				<tr>";
		$resultado .="					<td width=\"20%\"><small>Password:</small></td>";
		$resultado .="					<td><input id=\"password\" type = \"password\" name = \"password\" /></td>";
		$resultado .="				</tr>";
		$resultado .="				<tr>";
		$resultado .="					<td width=\"20%\"><small>Login automatico:</small></td>";
		$resultado .="					<td><input type = \"checkbox\" name = \"autologin\" /></td>";
		$resultado .="				</tr>";
		$resultado .="			</table>";
		$resultado .="			</td>";
		$resultado .="			<td width=\"20%\">";
		$resultado .="			<button onClick=\"event.preventDefault();appEnviarLogin();\">Entrar</button>";
		$resultado .="			</td>";
		$resultado .="		</tr>";
		$resultado .="	</table>";
		$resultado .="	<button onClick=\"event.preventDefault();appLostpassword();\">He olvidado mi password!</button>";
		$resultado .="</form>";
		return $resultado;
	}

	function formSigninForm() {
		$resultado="";
		$resultado .="<form id=\"signinform\" method = \"POST\">";
		$resultado .="	<input type=\"hidden\" name=\"action\" value=\"signin\"/>";
		$resultado .="	<table>";
		$resultado .="		<tr>";
		$resultado .="			<td width=\"80%\">";
		$resultado .="				<table>	";
		$resultado .="					<tr>";
		$resultado .="						<td width=\"20%\"><small>Numero de empleado:</small></td>";
		$resultado .="						<td width=\"80%\"><input id=\"numero\" type = \"number\" min=\"0\" name = \"numero\" /></td>";
		$resultado .="					</tr>";
		$resultado .="					<tr>";
		$resultado .="						<td width=\"20%\"><small>Nombre:</small></td>";
		$resultado .="						<td width=\"80%\"><input id=\"nombre\" type = \"text\" name = \"nombre\" /></td>";
		$resultado .="					</tr>";
		$resultado .="					<tr>";
		$resultado .="						<td width=\"20%\"><small>Usuario:</small></td>";
		$resultado .="						<td width=\"80%\"><input id=\"usuario\" type = \"text\" name = \"usuario\" /></td>";
		$resultado .="					</tr>";
		$resultado .="					<tr>";
		$resultado .="						<td width=\"20%\"><small>Email:</small></td>";
		$resultado .="						<td width=\"80%\"><input id=\"email\" type = \"email\" name = \"email\" /></td>";
		$resultado .="					</tr>";
		$resultado .="					<tr>";
		$resultado .="						<td width=\"20%\"><small>Password:</small></td>";
		$resultado .="						<td width=\"80%\"><input id=\"password1\" type = \"password\" name = \"password1\" /></td>";
		$resultado .="					</tr>";
		$resultado .="					<tr>";
		$resultado .="						<td width=\"20%\"><small>Repetir password:</small></td>";
		$resultado .="						<td width=\"80%\"><input id=\"password2\" type = \"password\" name = \"password2\" /></td>";
		$resultado .="					</tr>";
		$resultado .="				</table>";
		$resultado .="			</td>";
		$resultado .="			<td width=\"20%\">";
		$resultado .="				<button onClick=\"event.preventDefault();appEnviarSignin();\">Registrar</button>";
		$resultado .="			</td>";
		$resultado .="		</tr>";
		$resultado .="	</table>";
		$resultado .="</form>";
		return $resultado;
	}

	function usuarioVerificarCredenciales($name, $password) {
		global $db;
		$resultado="";
		$res = $db->prepare("SELECT id FROM usuarios WHERE (numero=? OR LCASE(nombre)=LCASE(?) OR LCASE(usuario)=LCASE(?) OR LCASE(email)=LCASE(?)) AND activo=1 AND password=SHA1(?)");
		$res->execute([$name, $name, $name, $name, $password]);
		while ($row = $res->fetch()) {
			$resultado=$row[0];
		}
		if ( strlen($resultado) == 0 ) {
			$res = $db->prepare("SELECT id FROM usuarios WHERE (numero=? OR LCASE(nombre)=LCASE(?) OR LCASE(usuario)=LCASE(?) OR LCASE(email)=LCASE(?)) AND activo=1 AND recovery=1 AND recoverypw=SHA1(?)");
			$res->execute([$name, $name, $name, $name, $password]);
			while ($row = $res->fetch()) {
				$resultado=$row[0];
			}
		}
		return $resultado;
	}
	
	function usuarioId() {
		global $db;
		$resultado = 0;
		$token = $_COOKIE["usuario"];
		$res = $db->prepare("SELECT id FROM usuarios WHERE token = ? AND activo = 1;");
		$res->execute([$token]);
		while ($row = $res->fetch()) {
			$resultado = $row[0];
		}
		return $resultado;
	}

	function usuarioNombre() {
		return ObtenerDescripcionDesdeID("usuarios", usuarioId() ,"nombre");
	}

	function usuarioEsLogeado() {
		return ( usuarioId() != 0 );
	}

	function usuarioEsSuper() {
		global $db;
		$resultado=false;
		/* if ( usuarioEsLogeado() ) {
			if ( $idusuario == "" ) {
				$idusuario =usuarioId();
			} */
		$res = $db->prepare("SELECT id FROM usuarios WHERE id= ? AND su=1 AND activo=1");
		$res->execute([usuarioId()]);
		while ($row = $res->fetch()) {
			$resultado=true;
		}
		//}
		return $resultado;
	}
	
	function crearToken() {
		global $db;
		$resultado = "";
		$usuarios = 1;
		while ($usuarios > 0) {
			$usuarios = 0;
			$resultado = randomString(8);
			$res = $db->prepare("SELECT COUNT(id) FROM usuarios WHERE token = ?;");
			$res->execute([$resultado]);
			while ($row = $res->fetch()) {
				$usuarios = $row[0];
			}
		}
		return $resultado;
	}

	function usuarioDarEntrada($idusuario) {
		global $db;
		$resultado = 0;
		$usuarios = 1;
		/* while ($usuarios > 0) {
			$usuarios = 0;
			$token = randomString(8);
			$res = $db->prepare("SELECT COUNT(id) FROM usuarios WHERE token = ?;");
			$res->execute([$token]);
			while ($row = $res->fetch()) {
				$usuarios = $row[0];
			}
		} */
		$token = crearToken();
		$res = $db->prepare("SELECT id FROM usuarios WHERE id= ? AND activo=1");
		$res->execute([$idusuario]);
		while ($row = $res->fetch()) {
			setcookie("usuario",$token);
			$resultado = $row[0];
			$res = $db->prepare("UPDATE usuarios SET token= ?, ultimologin=NOW(), recovery=0, recoverypw='' WHERE id=?" );
			$res->execute([$token, $row[0]]);
			
		}
		return $resultado;
	}

	/* function usuarioDesactivarToken() {
		global $db;
		$db->prepare("DELETE FROM tokensusuarios WHERE cliente=?")->execute([$_SERVER["REMOTE_ADDR"]]);
		setcookie("token", "");
	}

	function usuarioActivarToken() {
		global $db;
		$tokenNuevo = "";
		$tokenNuevo=randomString(40);
		$db->prepare("INSERT INTO tokensusuarios VALUES (0,?,?,?,DATE_ADD(NOW(), INTERVAL 7 DAY)")->execute([$_COOKIE["usuario"], $_SERVER["REMOTE_ADDR"], $tokenNuevo]);
		setcookie("token", $tokenNuevo, time()+604800);
	} */

/* 	function userAutoLogin() {
		global $db;
		$token = "";
		$userId = "";
		if ( !usuarioEsLogeado() ) {
			$res = $db->prepare("SELECT idusuario, token FROM tokensusuarios WHERE cliente = ? AND expira > NOW()");
			$res->execute([$_SERVER["REMOTE_ADDR"]]);
			while ($row = $res->fetch()) {
				$userId = $row[0];
				$token = $row[1];
			}
			if ( isset($_COOKIE["token"]) && $_COOKIE["token"] == $token ) {
				usuarioActivarToken();
				usuarioDarEntrada($userId);
			}
		}
	} */

	function guardarPreferencia($seccion, $clave, $valor) {
		global $db;
		$resultado = 0;
		$res = $db->prepare("SELECT id FROM opcionesusuarios WHERE idusuario= ? AND seccion= ? AND clave= ?;");
		$res->execute([usuarioId(), $seccion, $clave]);
		while ($row = $res->fetch()) {
			$resultado = $row[0];
		}
		if ( $resultado > 0 ) {
			$res = $db->prepare("UPDATE opcionesusuarios SET valor= ? WHERE idusuario= ? AND seccion= ? AND clave= ?;");
			$res->execute([$valor, usuarioId(), $seccion, $clave]);
		}
		else
		{
			$res = $db->prepare("INSERT INTO opcionesusuarios VALUES (0,  ?,  ?,  ?,  ?);");
			$res->execute([usuarioId(), $seccion, $clave, $valor]);
		}
	}

	function obtenerPreferencia($seccion, $clave, $default='') {
		global $db;
		$resultado = $default;
		$encontrado = false;
		//if ( isset($_COOKIE["usuario"]) ) {
		$res = $db->prepare("SELECT valor FROM opcionesusuarios WHERE idusuario= ? AND seccion= ? AND clave= ?;");
		$res->execute([usuarioId(), $seccion, $clave]);
		while ($row = $res->fetch()) {
			$encontrado = true;
			$resultado = $row[0];
		}
		if (!$encontrado) {
			guardarPreferencia($seccion, $clave, $resultado);
		}
		//}
		return $resultado;
	}
?>
