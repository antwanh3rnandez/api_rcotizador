<?php 

class Turistas{

    public static function obtener($request)
    {
        $request = json_decode($request, true);

        $slugEstado = $request['slug'];

        $pdo = Conexion::conectar();
        $stmt = $pdo->prepare("SELECT * FROM turistas WHERE entidad = :slugEstado AND tipo = 'nacionales'");
        $stmt->bindParam(":slugEstado", $slugEstado, PDO::PARAM_STR);
        $stmt->execute();
        $nacionales = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pdo = Conexion::conectar();
        $stmt = $pdo->prepare("SELECT * FROM turistas WHERE entidad = :slugEstado AND tipo = 'extranjeros'");
        $stmt->bindParam(":slugEstado", $slugEstado, PDO::PARAM_STR);
        $stmt->execute();
        $extranjeros = $stmt->fetchAll(PDO::FETCH_ASSOC);


        $chartArray = [];
        $chartArray['labels'] = [];
        $chartArray['nacionales'] = [];
        $chartArray['extranjeros'] = [];
        $chartArray['recharts'] = [];
        $chartArray['totales'] = [];
        foreach ($nacionales as $key => $periodo) {
            $periodoLabel = $periodo['anio'].' | '. $periodo['mes'];
            
            array_push($chartArray['nacionales'], $periodo['total']);
            array_push($chartArray['extranjeros'], $extranjeros[$key]['total']);
            array_push($chartArray['totales'], $periodo['total'] + $extranjeros[$key]['total']);
            array_push($chartArray['labels'], $periodoLabel);

            $chartArray['recharts'][$key]['name'] = $periodoLabel;
            $chartArray['recharts'][$key]['nacionales'] = $periodo['total'];
            $chartArray['recharts'][$key]['extranjeros'] = $extranjeros[$key]['total'];
            $chartArray['recharts'][$key]['totales'] = $periodo['total'] + $extranjeros[$key]['total'];
        }

        return $chartArray;

    }

}

?>