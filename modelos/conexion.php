<?php 

class Conexion{

    public static function conectar()
    {
        $host = SERVIDOR;
        $user = USUARIO;
        $pass = PASSWORD;
        $db = BD;

        $cnn = new PDO("mysql:host=$host;dbname=$db",$user,$pass);
        $cnn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $cnn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $cnn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $cnn->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, "SET NAMES utf8");
        
        $cnn->exec("set names utf8");
        return $cnn;
    }

}

?>