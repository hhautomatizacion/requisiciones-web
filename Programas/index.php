<html>
	<head>
		<style type="text/css">
			
			* {
				font-family: "arial";
				font-size: 15px;
			}
			b {
				border-radius: 5px;
				border: 2px solid white;
				padding: 3px;
				width:100%;
				color:white; 
				background:red
			}
			table {
				border-radius: 5px;
				border: 2px solid gray;
				width:100%;
				margin-bottom: 1px;
				
			}
			td {
				border-bottom: 1px solid gray;
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
				width: 90%; 
				margin: 0; 
				margin-left: 5%; 
				margin-right: 5%;
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
			.req {background: lightgray;}
			.printed {background: #FFC040;}
			.supplied {background: #C0C080;}
      		.req {	opacity: 0.9;}
			.owner {opacity: 1;}
			.deleted {opacity: 0.5;}
			.partsupplied {background: #C0C080;}
			.partdeleted {opacity: 0.5;}
			.com {	opacity: 0.9;}
			.comowner {opacity: 1;}
			.comdeleted {opacity: 0.5;}
		
		</style>
		<script language="JavaScript" type="text/javascript">
			var ocupado=false;
			var item=0;  // borrar borrar tambien de librequisicion.php
			var requisiciones = [];
			var requisicion = 0;
			var busquedarequisiciones="";
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
			function GetFileSizeAdjuntoNewReq(fileID) {
				var zTextFields = document.getElementsByTagName("input");
				for (var i=0; i<zTextFields.length; i++) {
					thefield=zTextFields[i].name;
					if (!thefield) thefield=zTextFields[i].id;
					if (thefield == 'reqadjuntos['+ fileID +']' ) {
						thesize=formatBytes(zTextFields[i].files.item(0).size);
						if (zTextFields[i].files.item(0).size >  100 * 1024 * 1024 ) {
							alert("El archivo es demasiado grande.\n\nFile: " + zTextFields[i].files.item(0).name + "\nSize: " + thesize);
							var table = document.getElementById('tablaadjuntosreq');
				
							table.rows[fileID].cells[1].innerHTML='<b>'+ thesize +'</b>';
						}else{
							var table = document.getElementById('tablaadjuntosreq');
				
							table.rows[fileID].cells[1].innerHTML=thesize ;
						}
					}
				}
			}
			function GetFileSizeAdjuntoPartidaNewReq(tableID, fileID) {
				var zTextFields = document.getElementsByTagName("input");
				for (var i=0; i<zTextFields.length; i++) {
					thefield=zTextFields[i].name;
					if (!thefield) thefield=zTextFields[i].id;
					if (thefield == 'partadjuntos'+ tableID +'['+ fileID +']' ) {
						thesize=formatBytes(zTextFields[i].files.item(0).size);
						if (zTextFields[i].files.item(0).size >  100 * 1024 * 1024 ) {
							alert("El archivo es demasiado grande.\n\nFile: " + zTextFields[i].files.item(0).name + "\nSize: " + thesize);
							var table = document.getElementById(tableID);
							table.rows[fileID].cells[1].innerHTML='<b>'+ thesize +'</b>';
						}else{
							var table = document.getElementById(tableID);
							table.rows[fileID].cells[1].innerHTML=thesize ;
						}
					}
				}
			}
			function GetFileSizeAdjunto(el, tableID, fileID) {
				thesize=formatBytes(el.files.item(0).size);
				var table = document.getElementById(tableID);
				if (el.files.item(0).size >  100 * 1024 * 1024 ) {
					alert("El archivo es demasiado grande.\n\nFile: " + el.files.item(0).name + "\nSize: " + thesize);
					table.rows[fileID].cells[1].innerHTML='<b>'+ thesize +'</b>';
				}else{
					if (el.files.item(0).size == 0 ) {
						alert("El archivo esta vacio.\n\nFile: " + el.files.item(0).name + "\nSize: " + thesize);
						table.rows[fileID].cells[1].innerHTML='<b>'+ thesize +'</b>';
					}else{
						table.rows[fileID].cells[1].innerHTML=thesize;
					}
				}
			}
			function elementoMostrar(idelemento) {
				document.getElementById(idelemento).style.visibility="visible";
			}
			function elementoOcultar(idelemento) {
				document.getElementById(idelemento).style.visibility="hidden"; 	
			}
			function appTextBusqueda(e) {
				if ( e.key == 'Enter' ) {
					appBusqueda();
				}
			}
			function appBusqueda() {
				txtBusqueda= document.getElementById("busquedarequisiciones");
				busquedarequisiciones=txtBusqueda.value;
				appActualizaVista();
			}
			
			function appEnviarNewReq() {
				if (window.XMLHttpRequest) {
					xmlhttp = new XMLHttpRequest();
				} else {
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if (this.responseText.length > 0) {
							alert(this.responseText);
							elementoOcultar("formulario");
							elementoMostrar("contenido");
						}
					}
				};
				xmlhttp.open("POST", "librequisicion.php", true);
				xmlhttp.send(new FormData(document.getElementById("newreqform")));
			}
			function appEnviarSignin() {
				if (window.XMLHttpRequest) {
					xmlhttp = new XMLHttpRequest();
				} else {
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if (this.responseText == "OK") {
							elementoOcultar("formulario");
							elementoMostrar("contenido");
							appHeader();
							appActualizaVista();
						}
					}
				};
				xmlhttp.open("POST", "libuser.php", true);
				xmlhttp.send(new FormData(document.getElementById("signinform")));
			}
			function appEnviarLogin() {
				if (window.XMLHttpRequest) {
					xmlhttp = new XMLHttpRequest();
				} else {
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if (this.responseText == "OK") {
							elementoOcultar("formulario");
							elementoMostrar("contenido");
							appHeader();
							appActualizaVista();
						}
					}
				};
				xmlhttp.open("POST", "libuser.php", true);
				xmlhttp.send(new FormData(document.getElementById("loginform")));
			}
			function appEnviarPassword() {
				if (window.XMLHttpRequest) {
					xmlhttp = new XMLHttpRequest();
				} else {
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if (this.responseText == "OK") {
							elementoOcultar("formulario");
							elementoMostrar("contenido");
							appHeader();
							appActualizaVista();
						}
					}
				};
				xmlhttp.open("POST", "libuser.php", true);
				xmlhttp.send(new FormData(document.getElementById("lostpasswordform")));
			}
			
			
			window.onload = function () {
				
				appHeader();
				
				appMenu();
				
				appActualizaVista();
			}
			
			function tik() {
				var divContenido = document.getElementById("contenido");
				var estado = document.getElementById("estado");
				estado.max=requisiciones.length;
				var div = document.createElement('div');
				div.id=requisiciones[requisicion];
				divContenido.appendChild(div);
				appActualizaRequisicion(requisiciones[requisicion]);
				estado.value = requisicion + 1;
				requisicion++;
				
				
				if ( requisicion >= requisiciones.length ) {
					clearInterval(t);
					estado.value = 0;
				}
									
									
			}
			
			function appActualizaVista() {
				var mostrarusuarios = 0;
				var mostrarvista =0;
				var usuarios='';
				var busqueda='';
				
				requisicion = 0;
				requisiciones = [];
				//if ( t ) {
				//	console.log("cancelar timer");
				//}
				
				if ( document.getElementById("mostrarrequisiciones") ) {
					mostrarvista = document.getElementById("mostrarrequisiciones").value;
				}
				if ( document.getElementById("usuariosrequisiciones") ) {
					mostrarusuarios = document.getElementById("usuariosrequisiciones").value;
				}
				if ( document.getElementById("busquedarequisiciones") ) {
					document.getElementById("busquedarequisiciones").value = busquedarequisiciones;
				}
				var divContenido = document.getElementById("contenido");	
				if ( !ocupado) {
					ocupado=true;	
					divContenido.innerHTML = "Espere...";
					if (window.XMLHttpRequest) {
						xmlhttp = new XMLHttpRequest();
					} else {
						xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
					}
					xmlhttp.onreadystatechange = function() {
						if (this.readyState == 4 && this.status == 200) {
							divContenido.innerHTML="";
							if ( this.responseText.length > 0 ) {
								requisiciones = this.responseText.split(" ");
								console.log(requisiciones);
								t = setInterval(tik, 100);
							}else{
								divContenido.innerHTML = "No hay resultados";
							}
							ocupado=false;
						}
					};
					if ( mostrarusuarios ) {
						usuarios='&user='+ mostrarusuarios;
					}
					if ( busquedarequisiciones.length > 0 ) {
						busqueda='&q='+ busquedarequisiciones;
					}
					xmlhttp.open("GET","librequisicion.php?action=show"+ usuarios +"&view="+ mostrarvista +"&item="+ item + busqueda ,true);
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
				window.scrollTo(0,0);
			}	
			
			function appNewReqForm() {
				var divFormulario = document.getElementById("formulario");
				if (window.XMLHttpRequest) {
					xmlhttp = new XMLHttpRequest();
				} else {
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
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
				if (window.XMLHttpRequest) {
					xmlhttp = new XMLHttpRequest();
				} else {
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
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
				if (window.XMLHttpRequest) {
					xmlhttp = new XMLHttpRequest();
				} else {
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
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
				if (window.XMLHttpRequest) {
					xmlhttp = new XMLHttpRequest();
				} else {
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
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
				if (window.XMLHttpRequest) {
					xmlhttp = new XMLHttpRequest();
				} else {
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText.length > 0 ) {
							divFormulario.innerHTML = this.responseText;
						}
					}
				};
				xmlhttp.open("GET","libdb.php?action=showsettingsform",true);
				xmlhttp.send();
			}
			function appLogout() {
				if (window.XMLHttpRequest) {
					xmlhttp = new XMLHttpRequest();
				} else {
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
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
				if (window.XMLHttpRequest) {
					xmlhttp = new XMLHttpRequest();
				} else {
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
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
				if (window.XMLHttpRequest) {
					xmlhttp = new XMLHttpRequest();
				} else {
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
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
				appActualizaVista();
			}
			function saveComentarioReq(tableID, rowID){
				var table = document.getElementById(tableID);
				var comentario= table.rows[rowID].cells[0].lastChild;
				var idrequisicion = tableID.toString().replace("tablacomentariosreq","");
				if (window.XMLHttpRequest) {
					xmlhttp = new XMLHttpRequest();
				} else {
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText == "OK" ) {
							table.rows[rowID].cells[0].innerHTML=comentario.value;
							table.rows[rowID].cells[3].innerHTML='';
						}
					}
				};
				xmlhttp.open("GET","libcomentario.php?action=add&idreq="+ idrequisicion +"&comentario="+ comentario.value,true);
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
			function populateUsersCombo(el,tabla,campo){
				if ( el.options.length <= 1 ) {
					if (window.XMLHttpRequest) {
						xmlhttp = new XMLHttpRequest();
					} else {
						xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
					}
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

			function populateCombo(el,tabla,campo){
				if ( el.options.length == 0 ) { 
					if (window.XMLHttpRequest) {
						xmlhttp = new XMLHttpRequest();
					} else {
						xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
					}
					xmlhttp.onreadystatechange = function() {
						if (this.readyState == 4 && this.status == 200) {
							if ( this.responseText.length > 0 ) {
								el.innerHTML = this.responseText;
							}
						}
					};
					xmlhttp.open("GET","libdb.php?action=getoptions&table="+ tabla +"&description="+ campo,true);
					xmlhttp.send();
				}			
			}
			function addPartidaNewReq(tableID) {
				var table = document.getElementById(tableID);
				var newRow = table.rows.length;
				var row = table.insertRow(newRow);
				
				row.insertCell(0).innerHTML = "<input type='hidden' name='totalpartidas[]' value='"+ newRow +"'><table><tr><td width=\"15%\"><small>Cantidad</small></td><td width=\"15%\"><small>Unidad</small></td><td width=\"50%\"><small>Descripcion</small></td><td width=\"20%\"><small>C.R.</small></td></tr><tr><td><input type = 'number' min='0' step='0.001' name = 'cantidad["+newRow+"]' /></td><td><select name = 'unidad["+newRow+"]' onfocus=\"populateCombo(this,'unidades','unidad');\"></select></td><td><input type = 'text' name = 'descripcion["+newRow+"]' /></td><td><select name = 'centrocostos["+newRow+"]' onfocus=\"populateCombo(this, 'centroscostos','descripcion')\" ></select></td></tr></table><table id='tablacomentarios"+newRow+"'><tr><td width=\"80%\"><small>Comentarios</small></td><td width=\"20%\"><input type = 'button' value='Agregar' onclick='addComentarioPartidaNewReq(\"tablacomentarios"+newRow+"\");'></td></tr></table><table id='tablaadjuntos"+newRow+"'><tr><td width=\"60%\"><small>Adjuntos</small></td><td width=\"20%\"><small>Tama&ntilde;o</small></td><td width=\"20%\"><input type = 'button' value='Agregar' onclick='addAdjuntoPartidaNewReq(\"tablaadjuntos"+newRow+"\");'></td></tr></table>";
				row.insertCell(1).innerHTML = "<input type = 'button' value='Quitar' onclick='removeRow(\"tablapartidas\","+ newRow +");'>";
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
							celdas[0].innerHTML= file.name;
							celdas[4].innerHTML= "<button onClick=\"window.open('uploads/r"+ idrequisicion +"/"+ file.name +"');\">Abrir</button>";
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
							celdas[0].innerHTML= file.name;
							celdas[4].innerHTML= "<button onClick=\"window.open('uploads/p"+ idpartida +"/"+ file.name +"');\">Abrir</button>";
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
				row.insertCell(3).innerHTML = "<input type = 'button' value='Guardar' onclick='saveComentarioReq(\""+  tableID +"\","+ newRow +");'><input type = 'button' value='Quitar' onclick='removeRow(\""+  tableID +"\","+ newRow +");'>";
			}
			
			function addComentarioPart(idpartida) {
				var table = document.getElementById('tablacomentariospart'+ idpartida);
				var newRow = table.rows.length;
				var row = table.insertRow(newRow);
				var fecha = new Date().toISOString().replace("T"," ").slice(0,19);
				row.insertCell(0).innerHTML = "<input type='text' id='comentariospartida"+ idpartida +"["+ newRow +"]' />";
				row.insertCell(1).innerHTML = fecha;
				row.insertCell(2).innerHTML = "";
				row.insertCell(3).innerHTML = "<input type = 'button' value='Guardar' onclick='saveComentarioPart(\""+  idpartida +"\","+ newRow +");'><input type = 'button' value='Quitar' onclick='removeRow(\"tablacomentriospart"+ idpartida +"\","+ newRow +");'>";
				
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
				xmlhttp.open("GET","libcomentario.php?action=add&idpart="+ idpartida +"&comentario="+ comentario.value,true);
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
				row.insertCell(0).innerHTML = "<input type='hidden' name='totalpartadjuntos["+ tableID +"][]' value='"+ newRow +"'><input type='file' onchange='GetFileSizeAdjuntoPartidaNewReq(\""+ tableID +"\","+ newRow +");' name='partadjuntos"+ tableID +"["+newRow+"]' />";
				row.insertCell(1).innerHTML = "";
				row.insertCell(2).innerHTML = "<input type = 'button' value='Quitar' onclick='removeRow(\""+  tableID +"\","+ newRow +");'>";
			}
			function addAdjuntoNewReq(tableID) {
				var table = document.getElementById(tableID);
				var newRow = table.rows.length;
				var row = table.insertRow(newRow);
				
				row.insertCell(0).innerHTML = "<input type='hidden' name='totalreqadjuntos[]' value='"+ newRow +"'><input type='file' onchange='GetFileSizeAdjuntoNewReq("+ newRow +");' name='reqadjuntos["+ newRow +"]' />";
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
			function appActivarSetting(setting, id) {
				if (window.XMLHttpRequest) {
					xmlhttp = new XMLHttpRequest();
				} else {
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText.length > 0 ) {
							appSettingsForm();
							alert(this.responseText);
						}
					}
				};
				xmlhttp.open("GET","libdb.php?action=activate&setting="+ setting +"&id="+ id,true);
				xmlhttp.send();
			}

			function appDesactivarSetting(setting, id) {
				if (window.XMLHttpRequest) {
					xmlhttp = new XMLHttpRequest();
				} else {
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText.length > 0 ) {
							appSettingsForm();
							alert(this.responseText);
						}
					}
				};
				xmlhttp.open("GET","libdb.php?action=deactivate&setting="+ setting +"&id="+ id,true);
				xmlhttp.send();
			}
			function appSaveSetting(setting, row) {
				if ( document.getElementById(setting +"numero"+ row) ) {
					numero="&number="+ document.getElementById(setting +"numero"+ row).value;
				}else{
					numero="";
				}
				if ( document.getElementById(setting +"descripcion"+ row) ) {
					descripcion="&description="+ document.getElementById(setting +"descripcion"+ row).value;
				}else{
					descripcion="";
				}
				if (window.XMLHttpRequest) {
					xmlhttp = new XMLHttpRequest();
				} else {
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText.length > 0 ) {
							alert(this.responseText);
							if ( document.getElementById(setting +"numero"+ row) ) {
								document.getElementById(setting +"numero"+ row).parentElement.innerHTML=document.getElementById(setting +"numero"+ row).value;
							}
							if ( document.getElementById(setting +"descripcion"+ row) ) {
								document.getElementById(setting +"descripcion"+ row).parentElement.innerHTML=document.getElementById(setting +"descripcion"+ row).value;
							}
						}
					}
				};
				xmlhttp.open("GET","libdb.php?action=addsetting&setting="+ setting + descripcion + numero,true);
				xmlhttp.send();
				
				
			}
			
			function appRestauraPartida(idpartida, idrequisicion) {
				if (window.XMLHttpRequest) {
					xmlhttp = new XMLHttpRequest();
				} else {
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
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
				if (window.XMLHttpRequest) {
					xmlhttp = new XMLHttpRequest();
				} else {
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
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
			function appSurtePartida(idpartida, idrequisicion) {
				
				if (window.XMLHttpRequest) {
					xmlhttp = new XMLHttpRequest();
				} else {
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
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
			function appPorsurtirPartida(idpartida, idrequisicion) {
				if (window.XMLHttpRequest) {
					xmlhttp = new XMLHttpRequest();
				} else {
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
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
			function appImprimeRequisicion(idrequisicion) {
				if (window.XMLHttpRequest) {
					xmlhttp = new XMLHttpRequest();
				} else {
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
				xmlhttp.onreadystatechange = function() {
					var a;
					if (this.readyState == 4 && this.status == 200) {

						
							a = document.createElement("a");
							a.href=window.URL.createObjectURL(this.response);
							a.download="id"+ idrequisicion +".pdf";
							//a.style.display="none";
							document.body.appendChild(a);
							a.click();
							appActualizaRequisicion(idrequisicion);

					}
				};
				xmlhttp.responseType="blob";
				xmlhttp.open("GET","librequisicion.php?action=print&id="+idrequisicion,true);
				xmlhttp.send();
			}
			function appPorsurtirRequisicion(idrequisicion) {
				if (window.XMLHttpRequest) {
					xmlhttp = new XMLHttpRequest();
				} else {
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
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
				if (window.XMLHttpRequest) {
					xmlhttp = new XMLHttpRequest();
				} else {
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
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
				if (window.XMLHttpRequest) {
					xmlhttp = new XMLHttpRequest();
				} else {
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
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
				if (window.XMLHttpRequest) {
					xmlhttp = new XMLHttpRequest();
				} else {
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
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
				if (window.XMLHttpRequest) {
					xmlhttp = new XMLHttpRequest();
				} else {
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText = "OK" ) {
							//appActualizaRequisicion(idrequisicion);
							window.scrollTo(0,document.body.scrollHeight);
						}
					}
				};
				xmlhttp.open("GET","librequisicion.php?action=copy&id="+idrequisicion,true);
				xmlhttp.send();
			}			
			function appActualizaRequisicion(idrequisicion) {
				var divRequisicion = document.getElementById(idrequisicion);
				if (window.XMLHttpRequest) {
					xmlhttp = new XMLHttpRequest();
				} else {
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText.length > 0 ) {
							divRequisicion.innerHTML = this.responseText;
						}
					}
				};
				xmlhttp.open("GET","librequisicion.php?action=show&id="+idrequisicion,true);
				xmlhttp.send();
			}
			function appSaveEditarImpresa(idrequisicion) {
				if (window.XMLHttpRequest) {
					xmlhttp = new XMLHttpRequest();
				} else {
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if ( this.responseText = "OK" ) {
							appActualizaRequisicion(idrequisicion);
						}
					}
				};
				var reqno='';
				var fecha='';
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
					
					var cellRequisicion = divRequisicion.children[0].children[0].children[3];
					var requisicion= cellRequisicion.innerHTML;
					cellRequisicion.innerHTML='<input id="editreqno'+ idrequisicion +'" type="text" value="'+ requisicion +'" onkeyup="appTextSaveEditarImpresa(event, '+ idrequisicion +');">';
					//var cellSurtir = divRequisicion.children[0].children[1].children[5];
					//cellSurtir.innerHTML='<input id="editfecha'+ idrequisicion +'" type="date">'
					
					// elChildren= divRequisicion.children[0].children[1].children;
					// for (var i=0; i < elChildren.length; i++) {
						// console.log(i);
						// console.log(elChildren[i].innerHTML);
					// }
					
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
