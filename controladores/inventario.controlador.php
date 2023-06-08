<?php

class Inventario
{

    public static function oferta($request)
    {
        $request = json_decode($request, true);
        $municipalityCode = $request['municipality'];
        $entity = intval(substr($municipalityCode, 0, 2));
        $municipalityCode = intval(substr($municipalityCode, 2, 3));

        // $filterQuery = 'AND tipo = 2 AND (avance_obra = 6 OR avance_obra = 5)';
        $filterQuery = 'AND tipo = 2';
        $pdo = Conexion::conectar();
        $stmt = $pdo->prepare("SELECT * FROM oferta_vivienda WHERE cve_ent = :entityCode $filterQuery");
        $stmt->bindParam(":entityCode", $entity, PDO::PARAM_INT);
        $stmt->execute();
        $ofertas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $toExport = [];
        foreach ($ofertas as $key => $oferta) {
            if (array_search($oferta['cve_mun'], array_column($toExport, 'cve_municipio'), true) !== false) {
                $toExport[array_search($oferta['cve_mun'], array_column($toExport, 'cve_municipio'))]['viviendas'] += $oferta['viviendas'];
            } else {
                array_push(
                    $toExport,
                    array(
                        "cve_municipio" => $oferta['cve_mun'],
                        "municipio" => $oferta['municipio'],
                        "viviendas" => $oferta['viviendas'],
                        "economicas" => 0,
                        "popular" => 0,
                        "tradicional" => 0,
                        "media" => 0,
                        "residencial" => 0,
                        "residencial_plus" => 0,
                        "indice" => array_search($oferta['cve_mun'], array_column($toExport, 'cve_municipio'), true)
                    )
                );
            }

            switch ($oferta['vivienda_valor']) {
                case 1:
                    $toExport[array_search($oferta['cve_mun'], array_column($toExport, 'cve_municipio'))]['economicas'] += $oferta['viviendas'];
                    break;
                case 2:
                    $toExport[array_search($oferta['cve_mun'], array_column($toExport, 'cve_municipio'))]['popular'] += $oferta['viviendas'];
                    break;
                case 3:
                    $toExport[array_search($oferta['cve_mun'], array_column($toExport, 'cve_municipio'))]['tradicional'] += $oferta['viviendas'];
                    break;
                case 4:
                    $toExport[array_search($oferta['cve_mun'], array_column($toExport, 'cve_municipio'))]['media'] += $oferta['viviendas'];
                    break;
                case 5:
                    $toExport[array_search($oferta['cve_mun'], array_column($toExport, 'cve_municipio'))]['residencial'] += $oferta['viviendas'];
                    break;
                case 6:
                    $toExport[array_search($oferta['cve_mun'], array_column($toExport, 'cve_municipio'))]['residencial_plus'] += $oferta['viviendas'];
                    break;
            }


        }

        return $toExport;
    }
}
