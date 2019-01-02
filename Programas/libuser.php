<?php
	use PHPMailer\PHPMailer\PHPMailer;

	require_once("PHPMailer.php");
	require_once("SMTP.php");
	require_once("libconfig.php");
	require_once("libdb.php");
	require_once("libphp.php");

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
		usuarioDesactivarToken();
		echo "OK";
	}

	if ( isset($_POST["action"]) && $_POST["action"] == "login" ) {
		if ( $_POST["name"] || $_POST["password"] ) {
			usuarioDarEntrada(usuarioVerificarCredenciales($_POST["name"], $_POST["password"]));
			if ( usuarioEsLogeado() ) {
				if ( isset($_POST["autologin"]) && $_POST["autologin"] == "on" ) {
					usuarioActivarToken();
				}
			}
		}
	}

	if ( isset($_POST["action"]) && $_POST["action"] == "signin" ) {
		$errores=array();
		if ( intval($_REQUEST["numero"]) <= 0 ){
			$errores["numero"]="Numero de usuario no valido";
		}
		$res = $db->prepare("SELECT * FROM usuarios WHERE numero='" . intval($_REQUEST["numero"]) ."' OR LCASE(nombre)=LCASE('". $_REQUEST["nombre"]."') OR LCASE(usuario)=LCASE('". $_REQUEST["usuario"] ."') OR LCASE(email)=LCASE('". $_REQUEST["usuario"] ."');");
		$res->execute();
		while ($row = $res->fetch()) {
			$errores["user1"]="El usuario ya existe";
		}
		if ( strlen($_REQUEST["password1"]) == 0) {
			$errores["password1"]="Password vacio";
		}
		if ( $_REQUEST["password1"] <> $_REQUEST["password2"] ) {
			$errores["password2"]="Password no igual";
		}
		if (count($errores) == 0){
			$res = $db->prepare("INSERT INTO usuarios VALUES (0,". $_REQUEST["numero"] .",'". $_REQUEST["nombre"] ."','". $_REQUEST["usuario"] ."','". $_REQUEST["email"] ."',SHA1('". $_REQUEST["password1"] ."'),'',0,0,1);");
			$res->execute();
			echo "OK";
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
		global $mail_server;
		global $mail_port;
		global $mail_user;
		global $mail_pass;
		global $mail_fromaddress;
		global $mail_fromname;

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
		$resultado="";
		$resultado .="			<form>";
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
		$resultado .="						<button onClick=\"event.preventDefault();appPruebaImprimeRequisicion(1895);\">Prueba</button>";
		$resultado .="						</td>";
		$resultado .="					</tr>";
		$resultado .="				</table>";
		$resultado .="			</form>";
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
		$resultado .="			<form id=\"loginform\" action=\"libuser.php\" method=\"POST\">";
		$resultado .="				<input type=\"hidden\" name=\"action\" value=\"login\">";
		$resultado .="				<table>";
		$resultado .="					<tr>";
		$resultado .="						<td width=\"80%\">";
		$resultado .="						<table>";
		$resultado .="							<tr>";
		$resultado .="								<td width=\"20%\"><small>Usuario:</small></td>";
		$resultado .="								<td><input type = \"text\" name = \"name\" /></td>";
		$resultado .="							</tr>";
		$resultado .="							<tr>";
		$resultado .="								<td width=\"20%\"><small>Password:</small></td>";
		$resultado .="								<td><input type = \"password\" name = \"password\" /></td>";
		$resultado .="							</tr>";
		$resultado .="							<tr>";
		$resultado .="								<td width=\"20%\"><small>Login automatico:</small></td>";
		$resultado .="								<td><input type = \"checkbox\" name = \"autologin\" /></td>";
		$resultado .="							</tr>";
		$resultado .="						</table>";
		$resultado .="						</td>";
		$resultado .="						<td width=\"20%\">";
		$resultado .="						<button onClick=\"event.preventDefault();appEnviarLogin();\">Entrar</button>";
		$resultado .="						</td>";
		$resultado .="					</tr>";
		$resultado .="				</table>";
		$resultado .="				<button onClick=\"event.preventDefault();appLostpassword();\">He olvidado mi password!</button>";
		$resultado .="			</form>";
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
		$resultado .="						<td width=\"80%\"><input type = \"number\" min=\"0\" name = \"numero\" /></td>";
		$resultado .="					</tr>";
		$resultado .="					<tr>";
		$resultado .="						<td width=\"20%\"><small>Nombre:</small></td>";
		$resultado .="						<td width=\"80%\"><input type = \"text\" name = \"nombre\" /></td>";
		$resultado .="					</tr>";
		$resultado .="					<tr>";
		$resultado .="						<td width=\"20%\"><small>Usuario:</small></td>";
		$resultado .="						<td width=\"80%\"><input type = \"text\" name = \"usuario\" /></td>";
		$resultado .="					</tr>";
		$resultado .="					<tr>";
		$resultado .="						<td width=\"20%\"><small>Email:</small></td>";
		$resultado .="						<td width=\"80%\"><input type = \"email\" name = \"email\" /></td>";
		$resultado .="					</tr>";
		$resultado .="					<tr>";
		$resultado .="						<td width=\"20%\"><small>Password:</small></td>";
		$resultado .="						<td width=\"80%\"><input type = \"password\" name = \"password1\" /></td>";
		$resultado .="					</tr>";
		$resultado .="					<tr>";
		$resultado .="						<td width=\"20%\"><small>Repetir password:</small></td>";
		$resultado .="						<td width=\"80%\"><input type = \"password\" name = \"password2\" /></td>";
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
			echo "OK";
			$resultado=$row[0];
		}
		if ( strlen($resultado) == 0 ) {
			$res = $db->prepare("SELECT id FROM usuarios WHERE (numero=? OR LCASE(nombre)=LCASE(?) OR LCASE(usuario)=LCASE(?) OR LCASE(email)=LCASE(?)) AND activo=1 AND recovery=1 AND recoverypw=SHA1(?)");
			$res->execute([$name, $name, $name, $name, $password]);
			while ($row = $res->fetch()) {
				echo "OK";
				$resultado=$row[0];
			}
		}
		return $resultado;
	}

	function usuarioNombre() {
		return ObtenerDescripcionDesdeID("usuarios", $_COOKIE["usuario"] ,"nombre");
	}

	function usuarioEsLogeado() {
		return ( isset($_COOKIE["usuario"])  && $_COOKIE["usuario"] != "" );
	}

	function usuarioEsSuper($idusuario = "") {
		global $db;
		$resultado=false;
		if ( usuarioEsLogeado() ) {
			if ( $idusuario == "" ) {
				$idusuario = $_COOKIE["usuario"];
			}
			$res = $db->prepare("SELECT id FROM usuarios WHERE id= ? AND su=1 AND activo=1");
			$res->execute([$idusuario]);
			while ($row = $res->fetch()) {
				$resultado=true;
			}
		}
		return $resultado;
	}

	function usuarioDarEntrada($idusuario) {
		global $db;
		$res = $db->prepare("SELECT id FROM usuarios WHERE id= ? AND activo=1");
		$res->execute([$idusuario]);
		while ($row = $res->fetch()) {
			setcookie("usuario",$row[0]);
			$res = $db->prepare("UPDATE usuarios SET recovery=0, recoverypw='' WHERE id=?" );
			$res->execute([$row[0]]);
		}
	}

	function usuarioDesactivarToken() {
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
	}

	function userAutoLogin() {
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
	}

	function guardarPreferencia($seccion, $clave, $valor) {
		global $db;
		$resultado = 0;
		$res = $db->prepare("SELECT id FROM opcionesusuarios WHERE idusuario= ? AND seccion= ? AND clave= ?;");
		$res->execute([$_COOKIE["usuario"], $seccion, $clave]);
		while ($row = $res->fetch()) {
			$resultado = $row[0];
		}
		if ( $resultado > 0 ) {
			$res = $db->prepare("UPDATE opcionesusuarios SET valor= ? WHERE idusuario= ? AND seccion= ? AND clave= ?;");
			$res->execute([$valor, $_COOKIE["usuario"], $seccion, $clave]);
		}
		else
		{
			$res = $db->prepare("INSERT INTO opcionesusuarios VALUES (0,  ?,  ?,  ?,  ?);");
			$res->execute([$_COOKIE["usuario"], $seccion, $clave, $valor]);
		}
		return $resultado;	
	}

	function obtenerPreferencia($seccion, $clave, $default='') {
		global $db;
		$resultado = $default;
		$res = $db->prepare("SELECT valor FROM opcionesusuarios WHERE idusuario= ? AND seccion= ? AND clave= ?;");
		$res->execute([$_COOKIE["usuario"], $seccion, $clave]);
		while ($row = $res->fetch()) {
			$resultado = $row[0];
		}
		return $resultado;	
	}
?>