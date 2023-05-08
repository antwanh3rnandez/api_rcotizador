<?php 

class SecurityController{

    public function methodAllowed($method){

        $printer = new JsonExporter();

        $methodsAllowed = ['POST','GET','PUT','DELETE'];

        if ( !in_array($method, $methodsAllowed) ) {

            $data = [
                "method" => $method,
                "mensaje" => "Método no permitido"
            ];

            http_response_code(405);
            return $printer->imprimir($data);
        }
    }

}

?>