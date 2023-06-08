<?php 

class NSE{

    public static function getNse($request)
    {
        $request = json_decode($request, true);
        $municipalityCode = $request['municipality'];
        $entity = intval(substr($municipalityCode, 0, 2));
        $municipalityCode = intval(substr($municipalityCode, 2, 3));

        $pdo = Conexion::conectar();
        $stmt = $pdo->prepare("SELECT * FROM nse_localidades WHERE entidad = :entityCode AND municipio = :municipalityCode");
        $stmt->bindParam(":entityCode", $entity, PDO::PARAM_INT);
        $stmt->bindParam(":municipalityCode", $municipalityCode, PDO::PARAM_INT);
        $stmt->execute();
        $nse = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $ab = 0;
        $cmas = 0;
        $c = 0;
        $dmas = 0;
        $de = 0;
        // $h = 0;
        foreach ($nse as $key => $value) {
            $ab += intval($value['a_b']);
            $cmas += intval($value['c_mas']);
            $c += intval($value['c']);
            $dmas += intval($value['d_mas']);
            $de += intval($value['d']) + intval($value['e']);
        }
        $total = $ab + $cmas + $c + $dmas + $de;
        // Calcular porcentaje por sección
        $porcentaje_ab = round(($ab / $total) * 100, 2);
        $porcentaje_cmas = round(($cmas / $total) * 100, 2);
        $porcentaje_c = round(($c / $total) * 100, 2);
        $porcentaje_dmas = round(($dmas / $total) * 100, 2);
        $porcentaje_de = round(($de / $total) * 100, 2);
        // $porcentaje_h = round(($h / $total) * 100, 2);

        try {
            $poblacion = self::getPoblacion($entity, $municipalityCode);
        } catch (\Throwable $th) {
            $poblacion = [];
        }

        $export = [
            "hogares" => [
                'a_b' => $ab,
                'c_mas' => $cmas,
                'c' => $c,
                'd_mas' => $dmas,
                'd_e' => $de,
                // 'h' => $h
            ],
            "porcentajes" => [
                'a_b' => $porcentaje_ab,
                'c_mas' => $porcentaje_cmas,
                'c' => $porcentaje_c,
                'd_mas' => $porcentaje_dmas,
                'd_e' => $porcentaje_de,
                // 'h' => $porcentaje_h
            ],
            "poblacion" => $poblacion
            
        ];
        return $export;
    }

    public static function getPoblacion($entity, $municipalityCode)
    {
        $pdo = Conexion::conectar();

        $stmt = $pdo->prepare("SELECT * FROM poblacion WHERE entidad = :entityCode AND municipio = :municipalityCode");
        $stmt->bindParam(":entityCode", $entity, PDO::PARAM_INT);
        $stmt->bindParam(":municipalityCode", $municipalityCode, PDO::PARAM_INT);
        $stmt->execute();

        $poblacion = $stmt->fetch(PDO::FETCH_ASSOC);

        $toExportArr = [];

        // Comprobacion para eliminar indice si está en 0
        $yearsArray = [1995, 2000, 2005, 2010, 2015, 2020];
        foreach ($poblacion as $key => $totalCenso) {
            if (in_array($key, $yearsArray)) {
                if ($totalCenso<=0) {
                    unset($poblacion[$key]);
                }else{
                    array_push($toExportArr, array(
                        'label' => $key,
                        'total' => $totalCenso
                    ));
                }
            }
        }

        return $toExportArr;
    }

    public static function getLocalidades($request)
    {
        $request = json_decode($request, true);
        $municipalityCode = $request['municipality'];


        return $municipalityCode;
    }

}
