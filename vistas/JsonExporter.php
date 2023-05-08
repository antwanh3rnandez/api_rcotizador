<?php 

class JsonExporter{

    public function imprimir($body){

        echo json_encode($body);
        exit;

    }

}

?>