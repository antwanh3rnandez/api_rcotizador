<?php 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, Content-Type, Authorization, Accept, Access-Control-Request-Method, X-Requested-With, Referer");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
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
require_once('controladores/itaee.controlador.php');


$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode( '/', $uri );
$request = $_SERVER['REQUEST_METHOD'];

$security = new SecurityController();
$security->methodAllowed($request);

if (true) {
    define('SERVIDOR', 'localhost');
    define('USUARIO', 'root');
    define('PASSWORD', '');
    define('BD', 'opinion');
} else {
    define('SERVIDOR', 'db5012952258.hosting-data.io');
    define('USUARIO', 'dbu2917609');
    define('PASSWORD', '6QNtt!FQEh7ca8PAa4RCnlx2!t');
    define('BD', 'dbs10876777');
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
            if ($request=='GET') {
                if (isset($uri[3])) {
                    $data = OpinionDeValor::getOpinion($uri[3]);
                    $code = 200;
                    http_response_code(200);
                }else{
                    $data = ['message'=>'Try with the {id} , or read the documentation.'];
                    $code = 400;
                    http_response_code(400);
                }
            }
            break;
        case 'itaee':
            $data = Itaee::obtener(file_get_contents('php://input'));
            $code = 200;
            break;
        case 'test':
            $data = json_decode(file_get_contents('php://input'));
            $code = 200;
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
