<?php 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, Content-Type, Authorization, Accept, Access-Control-Request-Method, X-Requested-With"); // X-API-KEY, 
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 2400');
header("Content-Type: application/json; charset=utf-8");

date_default_timezone_set('America/Mexico_City');

require_once __DIR__ . '/vendor/autoload.php';

// Import data
require_once('modelos/conexion.php');
require_once('vistas/JsonExporter.php');
require_once('controladores/Security.controller.php');
require_once('controladores/opinion.controlador.php');
require_once('controladores/rcotizador.controlador.php');


$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode( '/', $uri );
$request = $_SERVER['REQUEST_METHOD'];

$security = new SecurityController();
$security->methodAllowed($request);

if (true) {
    define('SERVIDOR', 'localhost');
    define('USUARIO', 'root');
    define('PASSWORD', '');
    define('BD', 'toicommx_inmobiliaria');
} else {
    define('SERVIDOR', 'localhost');
    define('USUARIO', 'toicommx_innovacion');
    define('PASSWORD', '141621.Dj#');
    define('BD', 'reclutamiento');
}

if (isset($uri)) {

    switch ($uri[2]) {
        case 'rCotizador':
            if ($request=='POST') {
                $entityBody = file_get_contents('php://input');
                $data = ControladorCotizador::ctrRecibirDatos($entityBody);
                $code = 200;
            }
            if ($request=='GET') {
                if (isset($uri[3])) {
                    // $data = ControladorCotizador::ctrEnviarDatos($uri);
                    $data = ['message'=>'Try with the {id} , or read the documentation.'];
                    $code = 200;
                    http_response_code(200);
                }else{
                    $data = ['message'=>'Try with the {id} , or read the documentation.'];
                    $code = 400;
                    http_response_code(400);
                }
            }
            break;
        case 'opinion':
            if ($request=='POST') {
                $entityBody = file_get_contents('php://input');
                $data = OpinionDeValor::generateOpinon($entityBody);
                $code = 200;
            }
            break;
        default:
        $data = 'admin_api';
        $code = 200;
        break;
    }
}

$export['data'] = $data;
$export['code'] = $code;


echo json_encode($export);
