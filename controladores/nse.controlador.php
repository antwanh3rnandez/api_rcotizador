<?php 

class NSE{

    public static function getNse($request)
    {

    }

    public static function getLocalidades($request)
    {
        $request = json_decode($request, true);
        $municipalityCode = $request['municipality'];

        return $municipalityCode;
    }

}

?>