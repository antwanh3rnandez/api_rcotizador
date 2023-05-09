<?php 

class Itae{

    public function FunctionName($request)
    {
        if (!isset($request['minYear']) && !isset($request['maxYear']) && !isset($request['state']) && !isset($request['municipality'])) {
            return ['message'=>'Try with the {minYear} , {maxYear} , {state} and {municipality} , or read the documentation.'];
        }

        $pdo = Conexion::conectar();




    }

}

?>