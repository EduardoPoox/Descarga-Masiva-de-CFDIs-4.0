<?php

/*
* Explicación breve sobre el funcionamiento
* del inicio de sesión con CIEC/Captcha.
*/

$maxDescargasSimultaneas = 10;
$rutaDescarga = dirname(__FILE__).DIRECTORY_SEPARATOR.'descargas'.DIRECTORY_SEPARATOR;

// Instanciar librería
require 'DescargaMasivaCfdi.php';
$descargaCfdi = new DescargaMasivaCfdi();

$rfcStr = '';
$contrasenaStr = '';

// escribir captcha mostrado en el explorador
$captchaStr = null;

// Iniciar sesión en PHP para poder guardar datos
session_start();

if($captchaStr === null) {
    // PASO 1

    // a) Obtener datos del captcha que se mostrará al usuario
    $imagenBase64 = $descargaCfdi->obtenerCaptcha();

    // b) Obtener sesión actual.
    $sesionStr = $descargaCfdi->obtenerSesion();

    // c) Guardar sesión del SAT en una sesión PHP
    $_SESSION['sesion_sat'] = $sesionStr;

    // c) Mostrar imagen del captcha
    $imgStr = '<img src="data:image/jpeg;base64,'.$imagenBase64.'" />';
    echo $imgStr;
    die;
}else{
    // PASO 2

    // a) Restaurar sesión guardada previamente
    $descargaCfdi->restaurarSesion($_SESSION['sesion_sat']);

    // b) Iniciar sesion
    $inicioSesionOk = $descargaCfdi->iniciarSesionCiecCaptcha(
        $rfcStr, $contrasenaStr, $captchaStr
    );

    if(!$inicioSesionOk) {
        $_SESSION['sesion_sat'] = null;
        die("Error al iniciar sesión en el SAT.\n");
    }
}


// borrar sesion
$_SESSION['sesion_sat'] = null;



// PASO 3

// Preparar datos para busqueda de recibidos
$busqueda = new BusquedaRecibidos();
$busqueda->establecerFecha(2017, 11); // $anio, $mes, $dia=null
// $busqueda->establecerHoraInicial($hora=0, $minuto=0, $segundo=0);
// $busqueda->establecerHoraFinal($hora='23', $minuto='59', $segundo='59');
// $busqueda->establecerRfcEmisor($rfc);
// $busqueda->establecerEstado($estado); // Ejemplo: BusquedaRecibidos::ESTADO_VIGENTE
// $busqueda->establecerFolioFiscal($uuid);

// Preparar datos para busqueda de emitidos
// $busqueda = new BusquedaEmitidos();
// $busqueda->establecerFechaInicial(2017, 10, 1); // $anio, $mes, $dia
// $busqueda->establecerFechaFinal(2017, 10, 15); // $anio, $mes, $dia
// $busqueda->establecerHoraInicial($hora='0', $minuto='0', $segundo='0');
// $busqueda->establecerHoraFinal($hora='0', $minuto='0', $segundo='0');
// $busqueda->establecerRfcReceptor($rfc);
// $busqueda->establecerEstado($estado); // Ejemplo: BusquedaEmitidos::ESTADO_VIGENTE
// $busqueda->establecerFolioFiscal($uuid);

// Obtener los datos de los CFDIs encontrados
$xmlInfoArr = $descargaCfdi->buscar($busqueda);
if($xmlInfoArr){

    // Preparar herramienta para descarga asincrona
    $descarga = new DescargaAsincrona($maxDescargasSimultaneas);

    // Recorrer array de resultados
    foreach ($xmlInfoArr as $xmlInfo) {

        // Mostrar datos del comprobante
        print_r($xmlInfo);

        // Agregar XML a la cola de descarga
        $descarga->agregarXml(
            $xmlInfo->urlDescargaXml,
            $rutaDescarga,
            $xmlInfo->folioFiscal
        );

        // Agregar Acuse a la cola de descarga (si aplica)
        if($xmlInfo->urlDescargaAcuse) {
            $descarga->agregarAcuse(
                $xmlInfo->urlDescargaAcuse,
                $rutaDescarga,
                $xmlInfo->folioFiscal
            );
        }
    }

    // Iniciar proceso de descarga
    $descarga->procesar();

    // Mostrar detalle de la descarga
    print_r($descarga->resultado());
    echo "\n";
}else{
    echo "No se han encontrado CFDIS.\n";
}
