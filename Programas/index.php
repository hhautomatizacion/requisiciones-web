<html>
	<head>
		<meta charset="utf-8" />
		<link rel="shortcut icon" href="favicon.ico">
		<link rel="icon" sizes="16x16 32x32 64x64" href="favicon.ico">
		<link rel="icon" type="image/png" sizes="196x196" href="favicon-192.png">
		<link rel="icon" type="image/png" sizes="160x160" href="favicon-160.png">
		<link rel="icon" type="image/png" sizes="96x96" href="favicon-96.png">
		<link rel="icon" type="image/png" sizes="64x64" href="favicon-64.png">
		<link rel="icon" type="image/png" sizes="32x32" href="favicon-32.png">
		<link rel="icon" type="image/png" sizes="16x16" href="favicon-16.png">
		<link rel="apple-touch-icon" href="favicon-57.png">
		<link rel="apple-touch-icon" sizes="114x114" href="favicon-114.png">
		<link rel="apple-touch-icon" sizes="72x72" href="favicon-72.png">
		<link rel="apple-touch-icon" sizes="144x144" href="favicon-144.png">
		<link rel="apple-touch-icon" sizes="60x60" href="favicon-60.png">
		<link rel="apple-touch-icon" sizes="120x120" href="favicon-120.png">
		<link rel="apple-touch-icon" sizes="76x76" href="favicon-76.png">
		<link rel="apple-touch-icon" sizes="152x152" href="favicon-152.png">
		<link rel="apple-touch-icon" sizes="180x180" href="favicon-180.png">
		<meta name="msapplication-TileColor" content="#FFFFFF">
		<meta name="msapplication-TileImage" content="favicon-144.png">
		<meta name="msapplication-config" content="/browserconfig.xml">
		<?php
			require_once "libconfig.php";
			require_once "libdb.php";
			require_once "libphp.php";
			require_once "libuser.php";
			require_once "libsettings.php";
		?>
		<style type="text/css">
			* {
				font-family: 'Arial';
				font-size: 15px;
			}
			b {
				border-radius: 3px;
				border: 0px solid white;
				padding: 0px;
				width: 100%;
				color: white;
				background: red;
			}
			table {
				border-radius: 5px;
				border: 2px solid gray;
				width: 100%;
				margin-bottom: 1px;
			}
			td {
				border-bottom: 1px solid gray;
				vertical-align: top;
			}
			tr:last-child>td {
				border-bottom: 0px;
			}
			small {
				font-size: 10px;
			}
			select {
				font-size: 20px;
				width: 100%;
				box-sizing: border-box;
				-moz-box-sizing: border-box;
				height: 26px;
				vertical-align: -moz-middle-with-baseline;
				vertical-align: middle;
			}
			button {
				font-size: 20px;
				height: 28px;
				vertical-align: middle;
			}
			input[type="button"] {
				font-size: 20px;
				height: 28px;
				vertical-align: middle;
				margin-bottom: 0px;
			}
			input[type="submit"] {
				font-size: 20px;
			}
			input[type="number"] {
				font-size: 20px;
				width: 100%;
				box-sizing: border-box;
				-moz-box-sizing: border-box;
			}
			input[type="date"] {
				font-size: 20px;
				width: 100%;
				box-sizing: border-box;
				-moz-box-sizing: border-box;
			}
			input[type="text"] {
				font-size: 20px;
				width: 100%;
				box-sizing: border-box;
				-moz-box-sizing: border-box;
			}
			input[type="email"] {
				font-size: 20px;
				width: 100%;
				box-sizing: border-box;
				-moz-box-sizing: border-box;
			}
			input[type="password"] {
				font-size: 20px;
				width: 100%;
				box-sizing: border-box;
				-moz-box-sizing: border-box;
			}
			div {
				margin-bottom: 10px;
			}
			#header {
				position: fixed;
				left: 0;
				top: 0;
				right:0;
				width: 100%;
				height: 32px;
				background: rgba(128,128,128,0.5);
				z-index: +1;
			}
			#menu {
				position: fixed;
				left: 0;
				right: 0;
				top: 32px;
				width: 100%;
				height: 32px;
				background: rgba(128,128,128,0.5);
				z-index: +1;
			}
			#contenido {
				margin-top: 70px;
				left: 0px;
				margin-bottom: 10px;
			}
			#formulario {
				width: 98%;
				margin: 0;
				margin-left: 1%;
				margin-right: 1%;
				margin-top: 0px;
				position: absolute;
				top: 70px;
				left: 0px;
				background: rgba(128,128,128,0.5);
				border: none;
				visibility: hidden;
			}
			#mostrarrequisiciones {
				width: 15%;
			}
			#usuariosrequisiciones {
				width: 15%;
			}
			#ordenrequisiciones {
				width: 15%;
			}
			#busquedarequisiciones {
				width: 15%;
				height: 26px;
				vertical-align: -moz-middle-with-baseline;
				vertical-align: middle;
			}
			#estado {
				width: 15%;
				float: right;
				height: 26px;
				vertical-align: -moz-middle-with-baseline;
				vertical-align: middle;
			}
			.campo {
				border: 0;
				width:100%;
				margin-bottom: 1px;
			}
			.req {background: lightgray;}
			.printed {background: #FFC040;}
			.supplied {background: #C0C080;}
			.req {opacity: 0.9;}
			.owner {opacity: 1;}
			.deleted {opacity: 0.5;}
			.partsupplied {background: #C0C080;}
			.partdeleted {opacity: 0.5;}
			.com {opacity: 0.9;}
			.comowner {opacity: 1;}
			.comdeleted {opacity: 0.5;}
		</style>
		<script language="JavaScript" type="text/javascript">
			var ocupado = false;
			var requisiciones = [];
			var requisicion = 0;
			var busquedarequisiciones = "";
			var file_upload_max_size = 0;
			function formatBytes(bytes) {
				if (typeof bytes !== 'number') {
					return '';
				}
				if (bytes >= 1024*1024*1024) {
					return (bytes / (1024*1024*1024)).toFixed(2) + ' GB';
				}
				if (bytes >= 1024*1024) {
					return (bytes / (1024*1024)).toFixed(2) + ' MB';
				}
				if (bytes >= 1024) {
					return (bytes / 1024).toFixed(2) + ' KB';
				}
				return bytes + ' B';
			}

			function GetFileSizeAdjunto(el, tableID, fileID) {
				var thesize = formatBytes(el.files.item(0).size);
				var table = document.getElementById(tableID);
				table.rows[fileID].cells[1].innerHTML = thesize;
				if ( el.files.item(0).size >  file_upload_max_size ) {
					table.rows[fileID].cells[1].style.outline = '#f00 solid 2px';
				} else {
					if ( el.files.item(0).size == 0 ) {
						table.rows[fileID].cells[1].style.outline = '#f00 solid 2px';
					} else {
						table.rows[fileID].cells[1].style.outline = '0px';
					}
				}
			}

			function randomString(length = 8) {
				var pass = '';
				var alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ";
				var alphaLength = alphabet.length;
				for (i = 0; i < length; i++) {
					pass += alphabet.charAt(Math.floor(Math.random() * alphaLength));
				}
				return pass;
			}

			function elementoMostrar(idelemento) {
				document.getElementById(idelemento).style.visibility="visible";
			}

			function elementoOcultar(idelemento) {
				document.getElementById(idelemento).style.visibility="hidden";
			}

			function elementoDeshabilitar(idelemento) {
				document.getElementById(idelemento).disabled = true;
			}

			function elementoHabilitar(idelemento) {
				document.getElementById(idelemento).disabled = false;
			}

			function appTextBusqueda(e) {
				if ( e.key == 'Enter' ) {
					appBusqueda();
				}
			}

			function appBusqueda() {
				txtBusqueda= document.getElementById("busquedarequisiciones");
				busquedarequisiciones=txtBusqueda.value;
				elementoOcultar("formulario");
				elementoMostrar("contenido");
				appActualizaVista();
			}

			function appEnviarNewReq() {
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						var respuesta = JSON.parse(this.responseText);
						if ( respuesta.succes == 1 ) {
							t = setInterval(tik, 10);
							requisiciones[requisiciones.length] = respuesta.id;
							elementoOcultar("formulario");
							elementoMostrar("contenido");
							document.title = "Requisiciones - "+ requisiciones.length +" mostradas";
							window.scrollTo(0, window.scrollHeight);
						} else {
							elementoHabilitar("botonenviarnewreq");
							for (var iter=0; iter < respuesta.validos.length; iter++) {
								var el=document.getElementById(respuesta.validos[iter]);
								if ( el ) {
									el.style.outline = '0px';
								}
							}
							for (var iter=0; iter < respuesta.errors.length; iter++) {
								var el=document.getElementById(respuesta.errors[iter]);
								if ( el ) {
									el.style.outline = '#f00 solid 2px';
								}
							}
						}
					}
				};
				elementoDeshabilitar("botonenviarnewreq");
				xmlhttp.open("POST", "librequisicion.php", true);
				xmlhttp.send(new FormData(document.getElementById("newreqform")));
			}

			function appEnviarSignin() {
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						var respuesta = JSON.parse(this.responseText);
						if ( respuesta.succes == 1 ) {
							elementoOcultar("formulario");
							elementoMostrar("contenido");
							appHeader();
							appActualizaVista();
						} else {
							for (var iter=0; iter < respuesta.validos.length; iter++) {
								var el=document.getElementById(respuesta.validos[iter]);
								if ( el ) {
									el.style.outline = '0px';
								}
							}
							for (var iter=0; iter < respuesta.errors.length; iter++) {
								var el=document.getElementById(respuesta.errors[iter]);
								if ( el ) {
									el.style.outline = '#f00 solid 2px';
								}
							}
						}
					}
				};
				xmlhttp.open("POST", "libuser.php", true);
				xmlhttp.send(new FormData(document.getElementById("signinform")));
			}

			function appEnviarLogin() {
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						var respuesta = JSON.parse(this.responseText);
						if ( respuesta.succes == 1 ) {
							elementoOcultar("formulario");
							elementoMostrar("contenido");
							appHeader();
							requisicion=0;
							t = setInterval(tik, 10);
						} else {
							for (var iter=0; iter < respuesta.validos.length; iter++) {
								var el=document.getElementById(respuesta.validos[iter]);
								if ( el ) {
									el.style.outline = '0px';
								}
							}
							for (var iter=0; iter < respuesta.errors.length; iter++) {
								var el=document.getElementById(respuesta.errors[iter]);
								if ( el ) {
									el.style.outline = '#f00 solid 2px';
								}
							}
						}
					}
				};
				xmlhttp.open("POST", "libuser.php", true);
				xmlhttp.send(new FormData(document.getElementById("loginform")));
			}

			function appEnviarPassword() {
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if (this.responseText == "OK") {
							elementoOcultar("formulario");
							elementoMostrar("contenido");
						}
					}
				};
				xmlhttp.open("POST", "libuser.php", true);
				xmlhttp.send(new FormData(document.getElementById("lostpasswordform")));
			}

			function appEnviarEditUser() {
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						var respuesta = JSON.parse(this.responseText);
						if ( respuesta.succes == 1 ) {
							elementoOcultar("formulario");
							elementoMostrar("contenido");
							appHeader();
							appActualizaVista();
						} else {
							for (var iter=0; iter < respuesta.validos.length; iter++) {
								var el=document.getElementById(respuesta.validos[iter]);
								if ( el ) {
									el.style.outline = '0px';
								}
							}
							for (var iter=0; iter < respuesta.errors.length; iter++) {
								var el=document.getElementById(respuesta.errors[iter]);
								if ( el ) {
									el.style.outline = '#f00 solid 2px';
								}
							}
						}
					}
				};
				xmlhttp.open("POST", "libuser.php", true);
				xmlhttp.send(new FormData(document.getElementById("edituserform")));
			}

			function appEnviarEditPassword() {
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						var respuesta = JSON.parse(this.responseText);
						if ( respuesta.succes == 1 ) {
							elementoOcultar("formulario");
							elementoMostrar("contenido");
							appHeader();
							appActualizaVista();
						} else {
							for (var iter=0; iter < respuesta.validos.length; iter++) {
								var el=document.getElementById(respuesta.validos[iter]);
								if ( el ) {
									el.style.outline = '0px';
								}
							}
							for (var iter=0; iter < respuesta.errors.length; iter++) {
								var el=document.getElementById(respuesta.errors[iter]);
								if ( el ) {
									el.style.outline = '#f00 solid 2px';
								}
							}
						}
					}
				};
				xmlhttp.open("POST", "libuser.php", true);
				xmlhttp.send(new FormData(document.getElementById("editpasswordform")));
			}

			window.onload = function () {
				appHeader();
				appMenu();
				appActualizaVista();
				getServerInfo();
			}

			function getServerInfo() {
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						file_upload_max_size = this.responseText;
					}
				};
				xmlhttp.open("GET", "libphp.php?action=getserverinfo", true);
				xmlhttp.send();
			}

			function tik() {
				var divContenido = document.getElementById("contenido");
				var estado = document.getElementById("estado");
				var div = document.createElement('div');
				if ( !ocupado ) {
					estado.max = requisiciones.length;
					div.id = requisiciones[requisicion];
					divContenido.appendChild(div);
					estado.value = requisicion + 1;
					appActualizaRequisicion(requisiciones[requisicion]);
					requisicion++;
				}
				if ( requisicion >= requisiciones.length ) {
					clearInterval(t);
					estado.value = 0;
				}
			}

			function appActualizaVista() {
				var mostrarusuarios = 0;
				var mostrarvista = 0;
				var mostrarorden = 0;
				var usuarios = '';
				var busqueda = '';
				var orden = '';
				requisicion = 0;
				requisiciones = [];
				if ( document.getElementById("mostrarrequisiciones") ) {
					mostrarvista = document.getElementById("mostrarrequisiciones").value;
				}
				if ( document.getElementById("usuariosrequisiciones") ) {
					mostrarusuarios = document.getElementById("usuariosrequisiciones").value;
				}
				if ( document.getElementById("busquedarequisiciones") ) {
					document.getElementById("busquedarequisiciones").value = busquedarequisiciones;
				}
				if ( document.getElementById("ordenrequisiciones") ) {
					mostrarorden = document.getElementById("ordenrequisiciones").value;
				}
				var divContenido = document.getElementById("contenido");
				if ( !ocupado) {
					ocupado = true;
					document.title = "Requisiciones - Buscando...";
					divContenido.innerHTML = "Espere...";
					xmlhttp = new XMLHttpRequest();
					xmlhttp.onreadystatechange = function() {
						if (this.readyState == 4 && this.status == 200) {
							divContenido.innerHTML="";
							if ( this.responseText.length > 0 ) {
								requisiciones = this.responseText.split(" ");
								document.title = "Requisiciones - "+ requisiciones.length +" mostradas";
								t = setInterval(tik, 10);
							}else{
								document.title = "Requisiciones";
								divContenido.innerHTML = "No hay resultados. Intente cambiando el alcance de la busqueda.";
							}
							ocupado = false;
						}
					};
					if ( mostrarusuarios ) {
						usuarios = '&user='+ mostrarusuarios;
					}
					if ( busquedarequisiciones.length > 0 ) {
						busqueda = '&q='+ busquedarequisiciones;
					}
					if ( mostrarorden ) {
						orden = '&s='+ mostrarorden;
					}
					xmlhttp.open("GET","librequisicion.php?action=list"+ usuarios +"&view="+ mostrarvista + busqueda + orden,true);
					xmlhttp.send();
				}
			}

			function appLostpassword() {
				appLostpasswordForm();
				elementoMostrar("formulario");
				elementoOcultar("contenido");
				window.scrollTo(0,0);
			}

			function appSignin() {
				appSigninForm();
				elementoMostrar("formulario");
				elementoOcultar("contenido");
				window.scrollTo(0,0);
			}

			function appSettings() {
				appSettingsForm();
				elementoMostrar("formulario");
				elementoOcultar("contenido");
				window.scrollTo(0,0);
			}

			function appLogin() {
				appLoginForm();
				elementoMostrar("formulario");
				elementoOcultar("contenido");
				window.scrollTo(0,0);
			}

			function appNewReq() {
				appNewReqForm();
				elementoMostrar("formulario");
				elementoOcultar("contenido");
				document.title = "Requisiciones - Nueva";
				window.scrollTo(0,0);
			}

			function appNewReqForm() {
				var divFormulario = document.getElementById("formulario");
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText.length > 0 ) {
							divFormulario.innerHTML = this.responseText;
						}
					}
				};
				xmlhttp.open("GET","librequisicion.php?action=showreqform",true);
				xmlhttp.send();
			}

			function appLostpasswordForm() {
				var divFormulario = document.getElementById("formulario");
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText.length > 0 ) {
							divFormulario.innerHTML = this.responseText;
						}
					}
				};
				xmlhttp.open("GET","libuser.php?action=showlostpasswordform",true);
				xmlhttp.send();
			}

			function appSigninForm() {
				var divFormulario = document.getElementById("formulario");
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText.length > 0 ) {
							divFormulario.innerHTML = this.responseText;
						}
					}
				};
				xmlhttp.open("GET","libuser.php?action=showsigninform",true);
				xmlhttp.send();
			}

			function appLoginForm() {
				var divFormulario = document.getElementById("formulario");
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText.length > 0 ) {
							divFormulario.innerHTML = this.responseText;
						}
					}
				};
				xmlhttp.open("GET","libuser.php?action=showloginform",true);
				xmlhttp.send();
			}

			function appSettingsForm() {
				var divFormulario = document.getElementById("formulario");
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText.length > 0 ) {
							divFormulario.innerHTML = this.responseText;
						}
					}
				};
				xmlhttp.open("GET","libsettings.php?action=showsettingsform",true);
				xmlhttp.send();
			}

			function appPreferencesForm() {
				var divFormulario = document.getElementById("formulario");
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText.length > 0 ) {
							divFormulario.innerHTML = this.responseText;
						}
					}
				};
				xmlhttp.open("GET","libuser.php?action=showpreferencesform",true);
				xmlhttp.send();
			}

			function appHelp() {
				appHelpForm();
				elementoMostrar("formulario");
				elementoOcultar("contenido");
				window.scrollTo(0,0);
			}

			function appHelpForm() {
				var divFormulario = document.getElementById("formulario");
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText.length > 0 ) {
							divFormulario.innerHTML = this.responseText;
						}
					}
				};
				xmlhttp.open("GET","libhelp.php?action=showhelp",true);
				xmlhttp.send();
			}

			function appLogout() {
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText == "OK" ) {
							appHome();
						}
					}
				};
				xmlhttp.open("GET","libuser.php?action=logout",true);
				xmlhttp.send();
			}

			function appHeader() {
				var divHeader = document.getElementById("header");
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText.length > 0 ) {
							divHeader.innerHTML = this.responseText;
						}
					}
				};
				xmlhttp.open("GET","libheader.php?action=showheader",true);
				xmlhttp.send();
			}

			function appMenu() {
				var divMenu = document.getElementById("menu");
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText.length > 0 ) {
							divMenu.innerHTML = this.responseText;
						}
					}
				};
				xmlhttp.open("GET","libheader.php?action=showmenu",true);
				xmlhttp.send();
			}

			function appHome() {
				document.getElementById("busquedarequisiciones").value="";
				busquedarequisiciones="";
				elementoOcultar("formulario");
				elementoMostrar("contenido");
				appHeader();
				requisicion=0;
				t = setInterval(tik, 10);
			}

			function appPrefereces() {
				elementoOcultar("contenido");
				appPreferencesForm();
				elementoMostrar("formulario");
			}

			function deleteComentarioReq(el, idcomentario){
				var cell = el.parentElement;
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText == "OK" ) {
							cell.innerHTML = "";
						}
					}
				};
				xmlhttp.open("GET","libcomentario.php?action=comdelete&type=comreq&idcom="+ idcomentario,true);
				xmlhttp.send();
			}

			function deleteComentarioPart(el, idcomentario){
				var cell = el.parentElement;
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText == "OK" ) {
							cell.innerHTML = "";
						}
					}
				};
				xmlhttp.open("GET","libcomentario.php?action=comdelete&type=compart&idcom="+ idcomentario,true);
				xmlhttp.send();
			}

			function undeleteComentarioReq(el, idcomentario){
				var cell = el.parentElement;
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText == "OK" ) {
							cell.innerHTML = "";
						}
					}
				};
				xmlhttp.open("GET","libcomentario.php?action=comundelete&type=comreq&idcom="+ idcomentario,true);
				xmlhttp.send();
			}

			function undeleteComentarioPart(el, idcomentario){
				var cell = el.parentElement;
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText == "OK" ) {
							cell.innerHTML = "";
						}
					}
				};
				xmlhttp.open("GET","libcomentario.php?action=comundelete&type=compart&idcom="+ idcomentario,true);
				xmlhttp.send();
			}

			function removeRow(tableID, rowID){
				var table = document.getElementById(tableID);
				var cols = table.rows[rowID].cells.length;
				for ( var iter = 0; iter < cols ; iter++ ) {
					table.rows[rowID].cells[iter].innerHTML='';
				}
				table.rows[rowID].style.display='none';
			}

			function populateSelectUsers(el,tabla,campo){
				if ( el.options.length <= 1 ) {
					xmlhttp = new XMLHttpRequest();
					xmlhttp.onreadystatechange = function() {
						if (this.readyState == 4 && this.status == 200) {
							if ( this.responseText.length > 0 ) {
								el.innerHTML = "<option value=0>Todos</option>"+ this.responseText;
							}
						}
					};
					xmlhttp.open("GET","libdb.php?action=getoptions&table="+ tabla +"&description="+ campo,true);
					xmlhttp.send();
				}
			}

			function populateSelect(el, tabla, campo, seleccionado=0){
				if ( el.options.length == 0 ) {
					xmlhttp = new XMLHttpRequest();
					xmlhttp.onreadystatechange = function() {
						if (this.readyState == 4 && this.status == 200) {
							if ( this.responseText.length > 0 ) {
								el.innerHTML = this.responseText;
							}
						}
					};
					var sel = "";
					if ( seleccionado > 0 ) {
						sel = "&sel="+seleccionado;
					}
					xmlhttp.open("GET","libdb.php?action=getoptions&table="+ tabla +"&description="+ campo + sel,true);
					xmlhttp.send();
				}
			}

			function populateSelectGroup(el, tabla, campo, tablagrupos, campogrupo, seleccionado = 0){
				if ( el.options.length == 0 ) {
					var sel = "";
					xmlhttp = new XMLHttpRequest();
					xmlhttp.onreadystatechange = function() {
						if (this.readyState == 4 && this.status == 200) {
							if ( this.responseText.length > 0 ) {
								el.innerHTML = this.responseText;
							}
						}
					};
					if ( seleccionado != 0 ) {
						sel = "&sel="+seleccionado;
					}
					xmlhttp.open("GET","libdb.php?action=getgroupoptions&table="+ tabla +"&description="+ campo +"&grouptable="+ tablagrupos +"&groupfield="+ campogrupo + sel,true);
					xmlhttp.send();
				}
			}

			function addPartidaNewReq(tableID) {
				var table = document.getElementById(tableID);
				var newRow = table.rows.length;
				var row = table.insertRow(newRow);
				row.insertCell(0).innerHTML = "<input type='hidden' name='totalpartidas[]' value='"+ newRow +"'><table><tr><td width=\"10%\"><small>Cantidad</small></td><td width=\"10%\"><small>Unidad</small></td><td width=\"65%\"><small>Descripcion</small></td><td width=\"15%\"><small>CentroCostos</small></td></tr><tr><td><input id = 'cantidad"+newRow+"' type = 'number' min='0' step='0.001' name = 'cantidad["+newRow+"]' /></td><td><select id = 'unidad"+newRow+"' name = 'unidad["+newRow+"]' onfocus=\"populateSelect(this,'unidades','unidad');\"></select></td><td><input id = 'descripcion"+newRow+"' type = 'text' name = 'descripcion["+newRow+"]' /></td><td><select id = 'centrocostos"+newRow+"' name = 'centrocostos["+newRow+"]' onfocus=\"populateSelectGroup(this, 'centroscostos','descripcion','empresas','idempresa')\" ></select></td></tr></table><table id='tablacomentarios"+newRow+"'><tr><td width=\"80%\"><small>Comentarios</small></td><td width=\"20%\"><input type = 'button' value='Agregar' onclick='addComentarioPartidaNewReq(\"tablacomentarios"+newRow+"\");'></td></tr></table><table id='tablaadjuntos"+newRow+"'><tr><td width=\"60%\"><small>Adjuntos</small></td><td width=\"20%\"><small>Tama&ntilde;o</small></td><td width=\"20%\"><input type = 'button' value='Agregar' onclick='addAdjuntoPartidaNewReq(\"tablaadjuntos"+newRow+"\");'></td></tr></table>";
				row.insertCell(1).innerHTML = "<input type = 'button' value='Quitar' onclick='removeRow(\"tablapartidas\","+ newRow +");'>";
				var el = document.getElementById("centrocostos"+newRow);
				var sel = document.getElementById("centrocostosreq").value;
				populateSelectGroup(el, 'centroscostos','descripcion','empresas','idempresa', sel);
			}
			
			function addPartidaReq(idrequisicion) {
				var table = document.getElementById('tablapartidasreq'+ idrequisicion);
				var newRow = table.rows.length;
				var row = table.insertRow(newRow);
				var token = randomString();
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText.length > 0 ) {
							row.insertCell(0).innerHTML = this.responseText;
							row.insertCell(1).innerHTML = "<input type = 'button' value='Guardar' onclick='appSavePartidaReq("+ idrequisicion +",\""+ token +"\");'><input type = 'button' value='Quitar' onclick='removeRow(\"tablapartidasreq"+  idrequisicion +"\","+ newRow +");'>";
						}
					}
				};
				xmlhttp.open("GET","libpartida.php?action=editpart&id="+ token,true);
				xmlhttp.send();
			}
			
			function appSavePartidaReq(idrequisicion, token) {
				console.log("here");
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						var respuesta = JSON.parse(this.responseText);
						if ( respuesta.succes == 1 ) {
							appActualizaRequisicion(idrequisicion);
						} else {
							for (var iter=0; iter < respuesta.validos.length; iter++) {
								var el=document.getElementById(respuesta.validos[iter]);
								if ( el ) {
									el.style.outline = '0px';
								}
							}
							for (var iter=0; iter < respuesta.errors.length; iter++) {
								var el=document.getElementById(respuesta.errors[iter]);
								if ( el ) {
									el.style.outline = '#f00 solid 2px';
								}
							}
						}
					}
				};
				
				var formdata = new FormData();
				formdata.append("accion", "savepartreq");
				formdata.append("idrequisicion", idrequisicion);
				formdata.append("cantidad", document.getElementById("cantidad"+ token).value);
				formdata.append("unidad", document.getElementById("unidad"+ token).value);
				formdata.append("descripcion", document.getElementById("descripcion"+ token).value);
				formdata.append("centrocostos", document.getElementById("centrocostos"+ token).value);
				xmlhttp.open("POST", "libpartida.php");
				xmlhttp.send(formdata);
			}
			
			function addAdjuntoReq(idrequisicion) {
				var table = document.getElementById('tablaadjuntosreq'+ idrequisicion);
				var newRow = table.rows.length;
				var row = table.insertRow(newRow);
				var fecha = new Date().toISOString().replace("T"," ").slice(0,19);
				row.insertCell(0).innerHTML = "<input type='file' id='adjuntosrequisicion"+ idrequisicion +"["+ newRow +"]' onchange='GetFileSizeAdjunto(this, \"tablaadjuntosreq"+ idrequisicion +"\", "+ newRow +");' />";
				row.insertCell(1).innerHTML = "";
				row.insertCell(2).innerHTML = fecha;
				row.insertCell(3).innerHTML = "";
				row.insertCell(4).innerHTML = "<input type = 'button' value='Guardar' onclick='saveAdjuntoReq("+  idrequisicion +","+ newRow +");'><input type = 'button' value='Quitar' onclick='removeRow(\"tablaadjuntosreq"+  idrequisicion +"\","+ newRow +");'>";
			}

			function saveAdjuntoReq(idrequisicion, adjunto) {
				var input = document.getElementById("adjuntosrequisicion"+ idrequisicion +"["+ adjunto +"]");
				var file = input.files.item(0);
				var celdas = document.getElementById('tablaadjuntosreq'+ idrequisicion).rows[adjunto].cells;
				celdas[4].innerHTML="";
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText == "OK" ) {
							celdas[0].innerHTML = file.name;
							celdas[4].innerHTML = "<button onClick=\"window.open('uploads/r"+ idrequisicion +"/"+ file.name +"');\">Abrir</button>";
						}
					}
				};
				var formdata = new FormData();
				formdata.append("accion", "agregaradjuntoreq");
				formdata.append("requisicion", idrequisicion);
				formdata.append("archivo", file);
				xmlhttp.open("POST","librequisicion.php");
				xmlhttp.send(formdata);
			}

			function addAdjuntoPart(idpartida) {
				var table = document.getElementById('tablaadjuntospart'+ idpartida);
				var newRow = table.rows.length;
				var row = table.insertRow(newRow);
				var fecha = new Date().toISOString().replace("T"," ").slice(0,19);
				row.insertCell(0).innerHTML = "<input type='file' id='adjuntospartida"+ idpartida +"["+ newRow +"]' onchange='GetFileSizeAdjunto(this, \"tablaadjuntospart"+ idpartida +"\", "+ newRow +");' />";
				row.insertCell(1).innerHTML = "";
				row.insertCell(2).innerHTML = fecha;
				row.insertCell(3).innerHTML = "";
				row.insertCell(4).innerHTML = "<input type = 'button' value='Guardar' onclick='saveAdjuntoPart("+  idpartida +","+ newRow +");'><input type = 'button' value='Quitar' onclick='removeRow(\"tablaadjuntospart"+  idpartida +"\","+ newRow +");'>";
			}

			function saveAdjuntoPart(idpartida, adjunto) {
				var input = document.getElementById("adjuntospartida"+ idpartida +"["+ adjunto +"]");
				var file = input.files.item(0);
				var celdas = document.getElementById('tablaadjuntospart'+ idpartida).rows[adjunto].cells;
				celdas[4].innerHTML="";
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText == "OK" ) {
							celdas[0].innerHTML = file.name;
							celdas[4].innerHTML = "<button onClick=\"window.open('uploads/p"+ idpartida +"/"+ file.name +"');\">Abrir</button>";
						}
					}
				};
				var formdata = new FormData();
				formdata.append("accion", "agregaradjuntopart");
				formdata.append("partida", idpartida);
				formdata.append("archivo", file);
				xmlhttp.open("POST","libpartida.php");
				xmlhttp.send(formdata);
			}

			function addComentarioReq(tableID) {
				var table = document.getElementById(tableID);
				var newRow = table.rows.length;
				var row = table.insertRow(newRow);
				var fecha = new Date().toISOString().replace("T"," ").slice(0,19);
				row.insertCell(0).innerHTML = "<input type='hidden' name='totalreqcomentarios[]' value='"+ newRow +"'><input type='text' name='reqcomentarios["+ newRow +"]' />";
				row.insertCell(1).innerHTML = fecha;
				row.insertCell(2).innerHTML = "";
				row.insertCell(3).innerHTML = "<input type = 'button' value='Guardar' onclick='saveComentarioReq(\""+ tableID +"\","+ newRow +");'><input type = 'button' value='Quitar' onclick='removeRow(\""+ tableID +"\","+ newRow +");'>";
			}

			function addComentarioPart(idpartida) {
				var table = document.getElementById('tablacomentariospart'+ idpartida);
				var newRow = table.rows.length;
				var row = table.insertRow(newRow);
				var fecha = new Date().toISOString().replace("T"," ").slice(0,19);
				row.insertCell(0).innerHTML = "<input type='text' id='comentariospartida"+ idpartida +"["+ newRow +"]' />";
				row.insertCell(1).innerHTML = fecha;
				row.insertCell(2).innerHTML = "";
				row.insertCell(3).innerHTML = "<input type = 'button' value='Guardar' onclick='saveComentarioPart(\""+  idpartida +"\","+ newRow +");'><input type = 'button' value='Quitar' onclick='removeRow(\"tablacomentariospart"+ idpartida +"\","+ newRow +");'>";
			}

			function saveComentarioReq(tableID, rowID){
				var table = document.getElementById(tableID);
				var comentario = table.rows[rowID].cells[0].lastChild;
				var idrequisicion = tableID.toString().replace("tablacomentariosreq","");
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText == "OK" ) {
							table.rows[rowID].cells[0].innerHTML = comentario.value;
							table.rows[rowID].cells[3].innerHTML = '';
						}
					}
				};
				xmlhttp.open("GET","libcomentario.php?action=comadd&type=comreq&idreq="+ idrequisicion +"&comentario="+ encodeURIComponent(comentario.value),true);
				xmlhttp.send();
			}

			function saveComentarioPart(idpartida, renglon){
				var comentario = document.getElementById("comentariospartida"+ idpartida +"["+ renglon +"]");
				var celdas = document.getElementById('tablacomentariospart'+ idpartida).rows[renglon].cells;
				celdas[3].innerHTML="";
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText == "OK" ) {
							celdas[0].innerHTML= comentario.value;
							celdas[3].innerHTML= "";
						}
					}
				};
				xmlhttp.open("GET","libcomentario.php?action=comadd&type=compart&idpart="+ idpartida +"&comentario="+ encodeURIComponent(comentario.value),true);
				xmlhttp.send();
			}

			function addComentarioPartidaNewReq(tableID) {
				var table = document.getElementById(tableID);
				var newRow = table.rows.length;
				var row = table.insertRow(newRow);
				row.insertCell(0).innerHTML = "<input type='hidden' name='totalpartcomentarios["+ tableID +"][]' value='"+ newRow +"'><input type='text' name='partcomentarios["+ tableID +"]["+ newRow +"]' />";
				row.insertCell(1).innerHTML = "<input type = 'button' value='Quitar' onclick='removeRow(\""+  tableID +"\","+ newRow +");'>";
			}

			function addComentarioNewReq(tableID) {
				var table = document.getElementById(tableID);
				var newRow = table.rows.length;
				var row = table.insertRow(newRow);
				row.insertCell(0).innerHTML = "<input type='hidden' name='totalreqcomentarios[]' value='"+ newRow +"'><input type='text' name='reqcomentarios["+ newRow +"]' />";
				row.insertCell(1).innerHTML = "<input type = 'button' value='Quitar' onclick='removeRow(\""+  tableID +"\","+ newRow +");'>";
			}

			function addAdjuntoPartidaNewReq(tableID) {
				var table = document.getElementById(tableID);
				var newRow = table.rows.length;
				var row = table.insertRow(newRow);
				row.insertCell(0).innerHTML = "<input type='hidden' name='totalpartadjuntos["+ tableID +"][]' value='"+ newRow +"'><input type='file' onchange='GetFileSizeAdjunto(this, \""+ tableID +"\","+ newRow +");' name='partadjuntos"+ tableID +"["+newRow+"]' />";
				row.insertCell(1).innerHTML = "";
				row.insertCell(2).innerHTML = "<input type = 'button' value='Quitar' onclick='removeRow(\""+  tableID +"\","+ newRow +");'>";
			}

			function addAdjuntoNewReq(tableID) {
				var table = document.getElementById(tableID);
				var newRow = table.rows.length;
				var row = table.insertRow(newRow);
				row.insertCell(0).innerHTML = "<input type='hidden' name='totalreqadjuntos[]' value='"+ newRow +"'><input type='file' onchange='GetFileSizeAdjunto(this, \""+ tableID +"\","+ newRow +");' name='reqadjuntos["+ newRow +"]' />";
				row.insertCell(1).innerHTML = "";
				row.insertCell(2).innerHTML = "<input type = 'button' value='Quitar' onclick='removeRow(\""+  tableID +"\","+ newRow +");'>";
			}

			function addSetting(tableID,descripcion) {
				var table = document.getElementById(tableID);
				var newRow = table.rows.length;
				var row = table.insertRow(newRow);
				row.insertCell(0).innerHTML = "<input type='hidden' name='total"+ tableID +"[]' value='"+ newRow +"'>";
				row.insertCell(1).innerHTML = '<input type = "text" id = "'+ tableID +'descripcion'+ newRow +'" />';
				row.insertCell(2).innerHTML = "<button onClick=\"event.preventDefault();appSaveSetting('"+ tableID +"',"+ newRow +");\">Guardar</button><input type = 'button' value='Quitar' onclick='removeRow(\""+  tableID +"\","+ newRow +");'>";
			}

			function addSetting2(tableID,numero,descripcion) {
				var table = document.getElementById(tableID);
				var newRow = table.rows.length;
				var row = table.insertRow(newRow);
				row.insertCell(0).innerHTML = "<input type='hidden' name='total"+ tableID +"[]' value='"+ newRow +"'>";
				row.insertCell(1).innerHTML = '<input type = "number" id = "'+ tableID +'numero'+ newRow +'" />';
				row.insertCell(2).innerHTML = '<input type = "text" id = "'+ tableID +'descripcion'+ newRow +'" />';
				row.insertCell(3).innerHTML = "<button onClick=\"event.preventDefault();appSaveSetting('"+ tableID +"',"+ newRow +");\">Guardar</button><input type = 'button' value='Quitar' onclick='removeRow(\""+  tableID +"\","+ newRow +");'>";
			}

			function addSettingEmpresa(tableID,descripcion) {
				var table = document.getElementById(tableID);
				var newRow = table.rows.length;
				var row = table.insertRow(newRow);
				row.insertCell(0).innerHTML = "<input type='hidden' name='total"+ tableID +"[]' value='"+ newRow +"'>";
				row.insertCell(1).innerHTML = '<select id = "'+ tableID +'empresa'+ newRow +'"></select>';
				row.insertCell(2).innerHTML = '<input type = "text" id = "'+ tableID +'descripcion'+ newRow +'" />';
				row.insertCell(3).innerHTML = "<button onClick=\"event.preventDefault();appSaveSetting('"+ tableID +"',"+ newRow +");\">Guardar</button><input type = 'button' value='Quitar' onclick='removeRow(\""+  tableID +"\","+ newRow +");'>";
				var sel = document.getElementById(tableID +'empresa'+ newRow );
				populateSelect(sel, "empresas", "nombre")
			}
			
			function addSettingCodigo(tableID,descripcion) {
				var table = document.getElementById(tableID);
				var newRow = table.rows.length;
				var row = table.insertRow(newRow);
				row.insertCell(0).innerHTML = "<input type='hidden' name='total"+ tableID +"[]' value='"+ newRow +"'>";
				row.insertCell(1).innerHTML = '<input type = "text" id = "'+ tableID +'codigo'+ newRow +'" />';
				row.insertCell(2).innerHTML = '<input type = "text" id = "'+ tableID +'descripcion'+ newRow +'" />';
				row.insertCell(3).innerHTML = "<button onClick=\"event.preventDefault();appSaveSetting('"+ tableID +"',"+ newRow +");\">Guardar</button><input type = 'button' value='Quitar' onclick='removeRow(\""+  tableID +"\","+ newRow +");'>";
			}

			function addSettingEmpresaNumero(tableID,numero,descripcion) {
				var table = document.getElementById(tableID);
				var newRow = table.rows.length;
				var row = table.insertRow(newRow);
				row.insertCell(0).innerHTML = "<input type='hidden' name='total"+ tableID +"[]' value='"+ newRow +"'>";
				row.insertCell(1).innerHTML = '<select id = "'+ tableID +'empresa'+ newRow +'"></select>';
				row.insertCell(2).innerHTML = '<input type = "number" id = "'+ tableID +'numero'+ newRow +'" />';
				row.insertCell(3).innerHTML = '<input type = "text" id = "'+ tableID +'descripcion'+ newRow +'" />';
				row.insertCell(4).innerHTML = "<button onClick=\"event.preventDefault();appSaveSetting('"+ tableID +"',"+ newRow +");\">Guardar</button><input type = 'button' value='Quitar' onclick='removeRow(\""+  tableID +"\","+ newRow +");'>";
				var sel = document.getElementById(tableID +'empresa'+ newRow );
				populateSelect(sel, "empresas", "nombre")
			}

			function appActivarSetting(setting, id) {
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText.length > 0 ) {
							appSettingsForm();
							alert(this.responseText);
						}
					}
				};
				xmlhttp.open("GET","libsettings.php?action=activate&setting="+ setting +"&id="+ id,true);
				xmlhttp.send();
			}

			function appDesactivarSetting(setting, id) {
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText.length > 0 ) {
							appSettingsForm();
							alert(this.responseText);
						}
					}
				};
				xmlhttp.open("GET","libsettings.php?action=deactivate&setting="+ setting +"&id="+ id,true);
				xmlhttp.send();
			}

			function appSaveSetting(setting, row) {
				var empresa = "";
				var codigo = "";
				var numero = "";
				var descripcion = "";
				if ( document.getElementById(setting +"empresa"+ row) ) {
					empresa = "&empresa="+ document.getElementById(setting +"empresa"+ row).value;
				}else{
					empresa = "";
				}
				if ( document.getElementById(setting +"numero"+ row) ) {
					numero = "&numero="+ document.getElementById(setting +"numero"+ row).value;
				}else{
					numero = "";
				}
				if ( document.getElementById(setting +"codigo"+ row) ) {
					codigo = "&codigo="+ document.getElementById(setting +"codigo"+ row).value;
				}else{
					codigo = "";
				}
				if ( document.getElementById(setting +"descripcion"+ row) ) {
					descripcion = "&description="+ document.getElementById(setting +"descripcion"+ row).value;
				}else{
					descripcion = "";
				}
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText.length > 0 ) {
							alert(this.responseText);
							if ( document.getElementById(setting +"empresa"+ row) ) {
								document.getElementById(setting +"empresa"+ row).parentElement.innerHTML=document.getElementById(setting +"empresa"+ row).value;
							}
							if ( document.getElementById(setting +"numero"+ row) ) {
								document.getElementById(setting +"numero"+ row).parentElement.innerHTML=document.getElementById(setting +"numero"+ row).value;
							}
							if ( document.getElementById(setting +"codigo"+ row) ) {
								document.getElementById(setting +"codigo"+ row).parentElement.innerHTML=document.getElementById(setting +"codigo"+ row).value;
							}
							if ( document.getElementById(setting +"descripcion"+ row) ) {
								document.getElementById(setting +"descripcion"+ row).parentElement.innerHTML=document.getElementById(setting +"descripcion"+ row).value;
							}
						}
					}
				};
				xmlhttp.open("GET","libsettings.php?action=addsetting&setting="+ setting + empresa + descripcion + codigo + numero,true);
				xmlhttp.send();
			}
			
			function appSeguirRequisicion(idrequisicion) {
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText = "OK" ) {
							appActualizaRequisicion(idrequisicion);
						}
					}
				};
				xmlhttp.open("GET","librequisicion.php?action=follow&id="+idrequisicion,true);
				xmlhttp.send();
			}

			function appAbandonarRequisicion(idrequisicion) {
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText = "OK" ) {
							appActualizaRequisicion(idrequisicion);
						}
					}
				};
				xmlhttp.open("GET","librequisicion.php?action=unfollow&id="+idrequisicion,true);
				xmlhttp.send();
			}
			
			function appIncluirEnRequisicion(idrequisicion) {
				appIncludeUserForm(idrequisicion);
				elementoMostrar("formulario");
				elementoOcultar("contenido");
				window.scrollTo(0,0);
			}
			
			function appIncludeUserForm(idrequisicion) {
				var divFormulario = document.getElementById("formulario");
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText.length > 0 ) {
							divFormulario.innerHTML = this.responseText;
						}
					}
				};
				xmlhttp.open("GET","librequisicion.php?action=showincludeuserform&idreq="+idrequisicion,true);
				xmlhttp.send();
			}
			
			function appSaveIncludeUser(idrequisicion) {
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if (this.responseText == "OK") {
							elementoOcultar("formulario");
							elementoMostrar("contenido");
							document.getElementById("mostrarrequisicion"+idrequisicion).scrollIntoView();
						}
					}
				};
				elementoDeshabilitar("botonsaveincludeuser");
				xmlhttp.open("POST", "librequisicion.php", true);
				xmlhttp.send(new FormData(document.getElementById("includeuserform")));
			}
			
			function appCancelIncludeUser(idrequisicion) {
				elementoOcultar("formulario");
				elementoMostrar("contenido");
				document.getElementById("mostrarrequisicion"+idrequisicion).scrollIntoView();
			}
			
			function appSeguirPartida(idpartida, idrequisicion) {
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText = "OK" ) {
							appActualizaRequisicion(idrequisicion);
						}
					}
				};
				xmlhttp.open("GET","libpartida.php?action=follow&id="+idpartida,true);
				xmlhttp.send();
			}

			function appAbandonarPartida(idpartida, idrequisicion) {
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText = "OK" ) {
							appActualizaRequisicion(idrequisicion);
						}
					}
				};
				xmlhttp.open("GET","libpartida.php?action=unfollow&id="+idpartida,true);
				xmlhttp.send();
			}

			function appRestauraPartida(idpartida, idrequisicion) {
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText = "OK" ) {
							appActualizaRequisicion(idrequisicion);
						}
					}
				};
				xmlhttp.open("GET","libpartida.php?action=partundelete&id="+idpartida,true);
				xmlhttp.send();
			}

			function appBorraPartida(idpartida, idrequisicion) {
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText = "OK" ) {
							appActualizaRequisicion(idrequisicion);
						}
					}
				};
				xmlhttp.open("GET","libpartida.php?action=partdelete&id="+idpartida,true);
				xmlhttp.send();
			}

			function appEditPart(el, idpartida, idrequisicion) {
				var cell = el.parentElement;
				var tablapart = document.getElementById("corepart"+idpartida);
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText.length > 0 ) {
							cell.innerHTML = "<input type = \"button\" value=\"Guardar\" onclick=\"appSaveEditPart("+ idpartida +","+ idrequisicion +");\"><input type = \"button\" value=\"Cancelar\" onclick=\"appActualizaRequisicion("+ idrequisicion +");\">";
							tablapart.innerHTML = this.responseText;
						}
					}
				};
				xmlhttp.open("GET","libpartida.php?action=editpart&id="+idpartida,true);
				xmlhttp.send();
			}

			function appSaveEditPart(idpartida, idrequisicion) {
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						var respuesta = JSON.parse(this.responseText);
						if ( respuesta.succes == 1 ) {
							appActualizaRequisicion(idrequisicion);
						} else {
							for (var iter=0; iter < respuesta.validos.length; iter++) {
								var el=document.getElementById(respuesta.validos[iter]);
								if ( el ) {
									el.style.outline = '0px';
								}
							}
							for (var iter=0; iter < respuesta.errors.length; iter++) {
								var el=document.getElementById(respuesta.errors[iter]);
								if ( el ) {
									el.style.outline = '#f00 solid 2px';
								}
							}
						}
					}
				};
				var formdata = new FormData();
				formdata.append("accion", "saveeditpart");
				formdata.append("partida", idpartida);
				formdata.append("cantidad", document.getElementById("cantidad"+ idpartida).value);
				formdata.append("unidad", document.getElementById("unidad"+ idpartida).value);
				formdata.append("descripcion", document.getElementById("descripcion"+ idpartida).value);
				formdata.append("centrocostos", document.getElementById("centrocostos"+ idpartida).value);
				xmlhttp.open("POST", "libpartida.php");
				xmlhttp.send(formdata);
			}

			function appSurtePartida(idpartida, idrequisicion) {
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText = "OK" ) {
							appActualizaRequisicion(idrequisicion);
						}
					}
				};
				xmlhttp.open("GET","libpartida.php?action=partsupplied&id="+idpartida,true);
				xmlhttp.send();
			}

			function appPorSurtirPartida(idpartida, idrequisicion) {
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText = "OK" ) {
							appActualizaRequisicion(idrequisicion);
						}
					}
				};
				xmlhttp.open("GET","libpartida.php?action=parttobesupplied&id="+idpartida,true);
				xmlhttp.send();
			}

			function appExportar() {
				var mostrarusuarios = 0;
				var mostrarvista = 0;
				var mostrarorden = 0;
				var usuarios = '';
				var busqueda = '';
				var orden = '';
				if ( document.getElementById("mostrarrequisiciones") ) {
					mostrarvista = document.getElementById("mostrarrequisiciones").value;
				}
				if ( document.getElementById("usuariosrequisiciones") ) {
					mostrarusuarios = document.getElementById("usuariosrequisiciones").value;
				}
				if ( document.getElementById("busquedarequisiciones") ) {
					document.getElementById("busquedarequisiciones").value = busquedarequisiciones;
				}
				if ( document.getElementById("ordenrequisiciones") ) {
					mostrarorden = document.getElementById("ordenrequisiciones").value;
				}
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					var a;
					if (this.readyState == 4 && this.status == 200) {
						var fecha = new Date().toISOString().replace("T"," ").slice(0,19);
						a = document.createElement("a");
						a.href=window.URL.createObjectURL(this.response);
						a.download="req "+ fecha +".pdf";
						document.body.appendChild(a);
						a.click();
					}
				};
				if ( mostrarusuarios ) {
					usuarios='&user='+ mostrarusuarios;
				}
				if ( busquedarequisiciones.length > 0 ) {
					busqueda='&q='+ busquedarequisiciones;
				}
				if ( mostrarorden ) {
					orden='&s='+ mostrarorden;
				}
				xmlhttp.responseType="blob";
				xmlhttp.open("GET","librequisicion.php?action=export"+ usuarios +"&view="+ mostrarvista + busqueda + orden ,true);
				xmlhttp.send();
			}

			function appImprimeRequisicion(idrequisicion) {
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					var a;
					if (this.readyState == 4 && this.status == 200) {
						a = document.createElement("a");
						a.href=window.URL.createObjectURL(this.response);
						a.download="id"+ idrequisicion +".pdf";
						document.body.appendChild(a);
						a.click();
						appActualizaRequisicion(idrequisicion);
					}
				};
				xmlhttp.responseType="blob";
				xmlhttp.open("GET","librequisicion.php?action=print&id="+idrequisicion,true);
				xmlhttp.send();
			}

			function appPorSurtirRequisicion(idrequisicion) {
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText = "OK" ) {
							appActualizaRequisicion(idrequisicion);
						}
					}
				};
				xmlhttp.open("GET","librequisicion.php?action=tobesupplied&id="+idrequisicion,true);
				xmlhttp.send();
			}

			function appSurteRequisicion(idrequisicion) {
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText = "OK" ) {
							appActualizaRequisicion(idrequisicion);
						}
					}
				};
				xmlhttp.open("GET","librequisicion.php?action=supplied&id="+idrequisicion,true);
				xmlhttp.send();
			}

			function appBorraRequisicion(idrequisicion) {
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText = "OK" ) {
							appActualizaRequisicion(idrequisicion);
						}
					}
				};
				xmlhttp.open("GET","librequisicion.php?action=delete&id="+idrequisicion,true);
				xmlhttp.send();
			}

			function appRestauraRequisicion(idrequisicion) {
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText = "OK" ) {
							appActualizaRequisicion(idrequisicion);
						}
					}
				};
				xmlhttp.open("GET","librequisicion.php?action=undelete&id="+idrequisicion,true);
				xmlhttp.send();
			}

			function appCopiaRequisicion(idrequisicion) {
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						var respuesta = JSON.parse(this.responseText);
						if ( respuesta.succes == 1 ) {
							t = setInterval(tik, 10);
							requisiciones[requisiciones.length] = respuesta.id;
							elementoOcultar("formulario");
							elementoMostrar("contenido");
							document.title = "Requisiciones - "+ requisiciones.length +" mostradas";
							document.getElementById("mostrarrequisicion"+idrequisicion).scrollIntoView();
						}else{
							for (var iter=0; iter < respuesta.errors.length; iter++) {
								var el=document.getElementById(respuesta.errors[iter]);
								if ( el ) {
									el.style.outline = '#f00 solid 2px';
								}
							}
						}
					}
				};
				xmlhttp.open("GET","librequisicion.php?action=copy&id="+idrequisicion,true);
				xmlhttp.send();
			}

			function appActualizaRequisicion(idrequisicion) {
				var divRequisicion = document.getElementById(idrequisicion);
				var busqueda='';
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText.length > 0 ) {
							divRequisicion.innerHTML = this.responseText;
						}
					}
				};
				if ( busquedarequisiciones.length > 0 ) {
					busqueda='&q='+ busquedarequisiciones;
				}
				xmlhttp.open("GET","librequisicion.php?action=show&id="+idrequisicion+busqueda,true);
				xmlhttp.send();
			}

			function appSaveEditarImpresa(idrequisicion) {
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText = "OK" ) {
							appActualizaRequisicion(idrequisicion);
						}
					}
				};
				var reqno = '';
				var fecha = '';
				if ( document.getElementById('editreqno'+ idrequisicion) ) {
					if ( document.getElementById('editreqno'+ idrequisicion).value.length > 0 ) {
						reqno='&reqno='+ document.getElementById('editreqno'+ idrequisicion).value;
					}
				}
				if ( document.getElementById('editfecha'+ idrequisicion) ) {
					if ( document.getElementById('editfecha'+ idrequisicion).value.length > 0 ) {
						fecha='&surtir='+ document.getElementById('editfecha'+ idrequisicion).value;
					}
				}
				xmlhttp.open("GET","librequisicion.php?action=saveprinted&id="+idrequisicion + reqno + fecha,true);
				xmlhttp.send();
			}

			function appTextSaveEditarImpresa(e, idrequisicion) {
				if ( e.key == 'Enter' ) {
					appSaveEditarImpresa(idrequisicion);
				}
			}

			function appEditarImpresa(el,idrequisicion) {
				var divRequisicion=document.getElementById('mostrarrequisicion'+ idrequisicion);
				if ( !document.getElementById('editreqno'+ idrequisicion) ) {
					var cellRequisicion = divRequisicion.children[0].children[0].children[5];
					var requisicion= cellRequisicion.innerHTML;
					cellRequisicion.innerHTML='<input id="editreqno'+ idrequisicion +'" type="text" value="'+ requisicion +'" onkeyup="appTextSaveEditarImpresa(event, '+ idrequisicion +');">';
				}
				el.parentElement.innerHTML='<button onClick="appSaveEditarImpresa('+ idrequisicion +');">Guardar</button><button onClick="appActualizaRequisicion('+ idrequisicion +');">Cancelar</button>';
			}
		</script>
		<title>Requisiciones</title>
	</head>
	<body>
		<div id="header"></div>
		<div id="menu"></div>
		<div id="contenido"></div>
		<div id="formulario"></div>
	</body>
</html>
