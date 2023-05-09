<?php 

class Itaee{

    public static function obtener($request)
    {
        $request = json_decode($request, true);
        if (!isset($request['minYear']) && !isset($request['maxYear']) && !isset($request['state']) && !isset($request['municipality'])) {
            return ['message'=>'Try with the {minYear} , {maxYear} , {state} and {municipality} , or read the documentation.'];
        }

        // $jsonEstados = file_get_contents('assets/json/clave_estados_entidad.json');
        // $estados = json_decode($jsonEstados, true);
        // $selectedState = $estados[array_search($request['state'], array_column($estados, 'entidad'))];
        $slugEstado = $request['slug'];

        // Range Query
        $minYear = $request['minYear'];
        $maxYear = $request['maxYear'];
        $rangeQuery = 'AND (anio BETWEEN '.$minYear.' AND '.$maxYear.')';
        $noAnual = 'AND periodo != "anual"';

        $pdo = Conexion::conectar();
        $stmt = $pdo->prepare("SELECT * FROM actividades_primarias WHERE slug = :slugEstado $rangeQuery $noAnual");
        $stmt->bindParam(":slugEstado", $slugEstado, PDO::PARAM_STR);
        $stmt->execute();
        $actividadesPrimarias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = $pdo->prepare("SELECT * FROM actividades_secundarias WHERE slug = :slugEstado $rangeQuery $noAnual");
        $stmt->bindParam(":slugEstado", $slugEstado, PDO::PARAM_STR);
        $stmt->execute();
        $actividadesSecundarias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = $pdo->prepare("SELECT * FROM actividades_terciarias WHERE slug = :slugEstado $rangeQuery $noAnual");
        $stmt->bindParam(":slugEstado", $slugEstado, PDO::PARAM_STR);
        $stmt->execute();
        $actividadesTerciarias = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $chartArray = [];
        $chartArray['labels'] = [];
        $chartArray['actividadesPrimarias'] = [];
        $chartArray['actividadesSecundarias'] = [];
        $chartArray['actividadesTerciarias'] = [];
        foreach ($actividadesPrimarias as $key => $periodo) {
            $periodoLabel = 'T'.$periodo['periodo'].' | '. $periodo['anio'];

            array_push($chartArray['actividadesPrimarias'], $periodo['variacion']);
            array_push($chartArray['actividadesSecundarias'], $actividadesSecundarias[$key]['variacion']);
            array_push($chartArray['actividadesTerciarias'], $actividadesTerciarias[$key]['variacion']);

            array_push($chartArray['labels'], $periodoLabel);
        }

        return $chartArray;

    }

}

?>