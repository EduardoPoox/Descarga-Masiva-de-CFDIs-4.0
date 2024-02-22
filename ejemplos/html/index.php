<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$tipo = null;
switch (empty($_GET['tipo']) ? null : $_GET['tipo']) {
	case 'ciec':
		$tipo = 'ciec';
		break;
	case 'fiel':
		$tipo = 'fiel';
		break;
	case 'ciecc':
	default:
		$tipo = 'ciecc';
		require dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'DescargaMasivaCfdi.php';
		$descargaCfdi = new DescargaMasivaCfdi;
		break;
	
}
?>
<!DOCTYPE html>
<html lang="es">
  <head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Descarga Masiva de CFDIs 4.0</title>
		<link href="css/bootstrap.min.css" rel="stylesheet">
		<link href="css/styles.css" rel="stylesheet">
		<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
		<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->

	<script type="text/javascript">
		function ocultarLinkDescargaZIP()
		{
			document.getElementById('iddownzip'),style.display = 'none'
		}
	</script>

	</head>
	<body>
		<nav class="navbar navbar-inverse navbar-fixed-top">
			<div class="container-fluid">
				<div class="navbar-header">
					<span class="navbar-brand">Descarga Masiva de CFDIs En su version 4.0</span>
				</div>
				<div class="collapse navbar-collapse">
					<ul class="nav navbar-nav navbar-right">
						<li class="<?php echo ($tipo == 'ciecc') ? 'active' : '' ?>"><a href="?tipo=ciecc">CIEC con Captcha</a></li>
						<li class="<?php echo ($tipo == 'ciec') ? 'active' : '' ?>"><a href="?tipo=ciec">CIEC</a></li>
						<li class="<?php echo ($tipo == 'fiel') ? 'active' : '' ?>"><a href="?tipo=fiel">FIEL</a></li>
					</ul>
				</div>
			</div>
		</nav>

	    <div id="main">
			<div class="container-fluid">
<?php
if($tipo == 'ciec') {
	echo '<h2>Inicio de sesión con CIEC</h2>';
	require 'form-login-ciec.inc.php';
}elseif($tipo == 'fiel'){
	echo '<h2>Inicio de sesión con FIEL</h2>';
	require 'form-login-fiel.inc.php';
}else{
	echo '<h2>Inicio de sesión con CIEC/Captcha</h2>';
	require 'form-login-ciec-captcha.inc.php';	
}
?>
				<hr/>

				<h2>Descarga</h2>
				<div class="tablas-resultados">
					<div class="overlay"></div>
					<ul class="nav nav-tabs" role="tablist">
						<li role="presentation" class="active"><a href="#recibidos" aria-controls="recibidos" role="tab" data-toggle="tab">Recibidos</a></li>
						<li role="presentation"><a href="#emitidos" aria-controls="emitidos" role="tab" data-toggle="tab">Emitidos</a></li>
					</ul>
					<div class="tab-content">
					    <div role="tabpanel" class="tab-pane active" id="recibidos">
					    	<?php require 'form-recibidos.inc.php' ?>
							<form method="POST" class="descarga-form">
								<input type="hidden" name="accion" value="descargar-recibidos" />
								<input type="hidden" name="sesion" class="sesion-ipt" />
								<div style="overflow:auto">
									<table class="table table-hover table-condensed" id="tabla-recibidos">
										<thead>
											<tr>
												<th class="text-center">XML</th>
												<th class="text-center">Acuse</th>
												<th>Efecto</th>
												<th>Razón Social</th>
												<th>RFC</th>
												<th>Estado</th>
												<th>Folio Fiscal</th>
												<th>Emisión</th>
												<th>Total</th>
												<th>Certificación</th>
												<th>Cancelación</th>
												<th>PAC</th>
											</tr>
										</thead>
										<tbody></tbody>
									</table>
								</div>
								<div class="text-right">
									<button type="submit" class="btn btn-success">Descargar seleccionados</button>
								</div>
							</form>
					    </div>
					    <div role="tabpanel" class="tab-pane" id="emitidos">
							<?php require 'form-emitidos.inc.php' ?>
							<form method="POST" class="descarga-form">
								<input type="hidden" name="accion" value="descargar-emitidos" />
								<input type="hidden" name="sesion" class="sesion-ipt" />
								<div style="overflow:auto">
									<table class="table table-hover table-condensed" id="tabla-emitidos">
										<thead>
											<tr>
												<th class="text-center">XML</th>
												<th class="text-center">Acuse</th>
												<th>Efecto</th>
												<th>Razón Social</th>
												<th>RFC</th>
												<th>Estado</th>
												<th>Folio Fiscal</th>
												<th>Emisión</th>
												<th>Total</th>
												<th>Certificación</th>
												<th>PAC</th>
											</tr>
										</thead>
										<tbody></tbody>
									</table>
								</div>
								<div class="text-right">
									<!-- para exportacion y descarga ecxel -->
									<a href="#" class="btn btn-primary excel-export" download='cfdi_emitidos.xls'>Exportar a excel</a>
									<button type="submit" class="btn btn-success">Descargar seleccionados</button>
								
									<!-- Coloca aquí la URL donde se creará el ZIP -->
									<a href="http://localhost/Descarga%20Masiva%204.0/Descarga-Masiva-de-CFDIs-4.0/ejemplos/html/descargasat.zip" id="iddownzip" class="classdownzip" style="display:none;" onclick="ocultarLinkDescargaZIP();">Descargar ZIP</a>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>

		<script src="js/jquery-3.1.1.min.js"></script>
		<script src="js/bootstrap.min.js"></script>
		<script src="js/code.js"></script>
	</body>
</html>

