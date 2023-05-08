<?php 

class OpinionDeValor{

    public static function generateOpinon($inputArr)
    {
        $casaInput = json_decode($inputArr, true);

        if (!isset($casaInput['direccion']) || !isset($casaInput['latitud']) || !isset($casaInput['longitud'])) {
            return null;
        }

        $casa = array(
            "calle" => $casaInput['calle'],
            "colonia" => $casaInput['colonia'],
            "municipio" => $casaInput['municipio'],
            "estado" => $casaInput['estado'],
            "direccion" => $casaInput['calle'].", ".$casaInput['colonia'].", ".$casaInput['municipio'].", ".$casaInput['estado'],
            "caracteristicas" => array(
                "tipo" => $casaInput['tipo'],
                "cuartos" => $casaInput['cuartos'],
                "banios" => $casaInput['banios'],
                "mediosBanios" => $casaInput['mediosBanios'],
                "estacionamientos" => $casaInput['estacionamientos'],
                "metrosConstruccion" => $casaInput['metrosConstruccion'],
                "metrosTerreno" => $casaInput['metrosTerreno'],
                "antiguedad" => $casaInput['antiguedad'],
                "estado" => $casaInput['estadoCasa'],
            ),
            "amenidades" => array(
                "seguridadPrivada" => (isset($casaInput['seguridadPrivada']) && $casaInput['seguridadPrivada'] == "on") ? "si" : "no",
                "amueblado" => (isset($casaInput['amueblado']) && $casaInput['amueblado'] == "on") ? "si" : "no",
                "cocinaIntegral" => (isset($casaInput['cocinaIntegral']) && $casaInput['cocinaIntegral'] == "on") ? "si" : "no",
                "alberca" => (isset($casaInput['alberca']) && $casaInput['alberca'] == "on") ? "si" : "no",
                "aireAcondicionado" => (isset($casaInput['aireAcondicionado']) && $casaInput['aireAcondicionado'] == "on") ? "si" : "no",
                "jardin" => (isset($casaInput['jardin']) && $casaInput['jardin'] == "on") ? "si" : "no",
                "gimnasio" => (isset($casaInput['gimnasio']) && $casaInput['gimnasio'] == "on") ? "si" : "no",
                "cuartoDeServicio" => (isset($casaInput['cuartoDeServicio']) && $casaInput['cuartoDeServicio'] == "on") ? "si" : "no",
            ),
            "latitud" => $casaInput['latitud'],
            "longitud" =>  $casaInput['longitud'],
        );

        try {
            $opinion = new OpinionDeValorTranscription();
            $opinion = $opinion->main($casa);
            return $opinion;
        } catch (\Throwable $th) {
            return 'Error: '. $th;
        }

    }

}

class OpinionDeValorTranscription
{

    public function main($casa)
    {
        // Datos de la casa
        // $casa = array(
        //     "calle" => "Av. Dr. José Narro Robles 845",
        //     "colonia" => "Los González",
        //     "municipio" => "Saltillo",
        //     "estado" => "Coahuila",
        //     "direccion" => "Av. Dr. José Narro Robles 845, Los González, Saltillo, Coahuila",
        //     "caracteristicas" => array(
        //         "tipo" => "Casa",
        //         "cuartos" => 3,
        //         "banios" => 3,
        //         "mediosBanios" => 1,
        //         "estacionamientos" => 2,
        //         "metrosConstruccion" => 390,
        //         "metrosTerreno" => 504,
        //         "antiguedad" => 0,
        //         "estado" => "muy bueno",
        //     ),
        //     "amenidades" => array(
        //         "seguridadPrivada" => "si",
        //         "amueblado" => "no",
        //         "cocinaIntegral" => "si",
        //         "alberca" => "si",
        //         "aireAcondicionado" => "no",
        //         "jardin" => "si",
        //         "gimnasio" => "no",
        //         "cuartoDeServicio" => "si",
        //     ),
        //     "latitud" => 25.475167,
        //     "longitud" => -100.951960,
        // );

        // $casa = array(
        //     "calle" => "Av. Dr. José Narro Robles 845",
        //     "colonia" => "Los González",
        //     "municipio" => "Saltillo",
        //     "estado" => "Coahuila",
        //     "direccion" => "Av. Dr. José Narro Robles 845, Los González, Saltillo, Coahuila",
        //     "caracteristicas" => array(
        //         "tipo" => "Casa",
        //         "cuartos" => 3,
        //         "banios" => 1,
        //         "mediosBanios" => 1,
        //         "estacionamientos" => 2,
        //         "metrosConstruccion" => 180,
        //         "metrosTerreno" => 150,
        //         "antiguedad" => 17,
        //         "estado" => "muy bueno",
        //     ),
        //     "amenidades" => array(
        //         "seguridadPrivada" => "si",
        //         "amueblado" => "si",
        //         "cocinaIntegral" => "si",
        //         "alberca" => "no",
        //         "aireAcondicionado" => "si",
        //         "jardin" => "no",
        //         "gimnasio" => "no",
        //         "cuartoDeServicio" => "si",
        //     ),
        //     "latitud" => 25.693649,
        //     "longitud" =>  -100.502019,
        // );

        // Primera consulta
        // ../manychat-api/bigdatachido/inmuebles?page=1&per_page=10&sort=distance&order=asc&q=null&query=${colonia2}&latitud=${latitud}&longitud=${longitud}&estado=${estado}&municipio=${municipio}&colonia=${colonia[1]}&tipo=${tipo}&cuartos=${cuartos}&banios=${banios}&estacionamiento=${estacionamientos}
        $table = "inmuebles";
        $latitud = $casa["latitud"];
        $longitud = $casa["longitud"];
        $estado = $casa["estado"];
        $colonia = $casa["colonia"];
        $tipo = $casa["caracteristicas"]["tipo"];
        $cuartos = $casa["caracteristicas"]["cuartos"];
        $banios = $casa["caracteristicas"]["banios"];
        $estacionamiento = $casa["caracteristicas"]["estacionamientos"];
        $sort = "distance";
        $order = "asc";
        $offset = 0;
        $perPage = 10;

        $queryDistancia = ", (((acos(sin((" . $latitud . "*pi()/180)) * sin((latitud*pi()/180)) + cos((" . $latitud . "*pi()/180)) * cos((latitud*pi()/180)) * cos(((" . $longitud . " - longitud)*pi()/180)))) * 180/pi()) * 60 * 1.1515 * 1.609344) as distance";

        $queryLatitud = "latitud <= (" . $latitud . " + 0.018) AND latitud >= (" . $latitud . " - 0.018) AND";
        $queryLongitud = "longitud <= (" . $longitud . " + 0.018) AND longitud >= (" . $longitud . " - 0.018) AND";
        $queryTipo = "tipo = '" . $tipo . "' AND";

        // Query inmuebles
        $stmt = Conexion::conectar();
        $stmt = $stmt->prepare("SELECT * $queryDistancia FROM $table WHERE $queryLatitud $queryLongitud $queryTipo metros_construidos IS NOT NULL AND metros_totales IS NOT NULL ORDER BY $sort $order LIMIT $offset,$perPage");
        $stmt->execute();
        $inmuebles = $stmt->fetchAll();
        $stmt = null;

        // Query Valor Terreno
        // $queryDistancia = " (((acos(sin((" . $latitud . "*pi()/180)) * sin((latitud*pi()/180)) + cos((" . $latitud . "*pi()/180)) * cos((latitud*pi()/180)) * cos(((" . $longitud . " - longitud)*pi()/180)))) * 180/pi()) * 60 * 1.1515 * 1.609344) as distance";
        $queryLatitud = "(latitud <= (" . $latitud . " + 0.008) AND latitud >= (" . $latitud . " - 0.008)) OR";
        $queryLongitud = "(longitud <= (" . $longitud . " + 0.008) AND longitud >= (" . $longitud . " - 0.008))";
        $pdo = Conexion::conectar();
        $stmt = $pdo->prepare("SELECT colonia, monto, latitud, longitud $queryDistancia FROM nuevo_leon WHERE $queryLatitud $queryLongitud ORDER BY $sort $order LIMIT $offset,$perPage");
        $stmt->execute();
        $terreno = $stmt->fetch(PDO::FETCH_ASSOC);



        // Iniciar loop por inmuebles
        $operacionesInmuebles = $this->resultadosCasas($inmuebles, $terreno, $casa);

        // Calcular precio inmueble
        $calcularPrecio = $this->calcularPrecio($operacionesInmuebles, $casa);

        // Exportacion
        $export = array(
            "propiedad" => $casa,
            "terreno" => $terreno,
            "inmuebles" => $inmuebles,
            "resultados" => $operacionesInmuebles,
            "precio" => $calcularPrecio,
        );

        return $export;
    }

    // Calculos por casa
    public function resultadosCasas($inmuebles, $terreno, $inmuebleBase)
    {

        $arrayResultados = array();

        // vars individual
        $distancia = 0;
        $metrosTerreno = 0;
        $metrosConstruccion = 0;
        $precio = 0;
        $antiguedad = 0;
        $precioPorMetroCuadradoTerreno = 0;
        $precioPorMetroCuadradoConstruccion = 0;

        $depreciacion = 0;

        foreach ($inmuebles as $key => $inmueble) {

            // Calcular distancia
            $distancia = $inmueble["distance"];

            // Calcular metros de terreno
            $metrosTerreno = $inmueble["metros_totales"];

            // Calcular metros de construccion
            $metrosConstruccion = $inmueble["metros_construidos"];

            // Calcular precio
            $precio = $inmueble["precios"];

            // Calcular antiguedad
            $antiguedad = $inmueble["antiguedad"];

            // Calcular precio por metro cuadrado de terreno
            // TODO: En caso de que no haya catastral, buscar una casa similar con un precio aproximado y verificar si esa tiene catastral
            $precioPorMetroCuadradoTerreno = doubleval($terreno["monto"]);

            // Calcular precio del terreno
            $precioTerreno = $precioPorMetroCuadradoTerreno * $metrosTerreno;
            if ($precioTerreno > $precio) {
                $precioTerreno = ($precioPorMetroCuadradoTerreno * .52) * $metrosTerreno;
            } else {
                $precioTerreno = $precioPorMetroCuadradoTerreno * $metrosTerreno;
            }

            // Calcular precio por metro cuadrado de construccion
            $precioPorMetroCuadradoConstruccion = ($precio - $precioTerreno) / $metrosConstruccion;

            // Calcular precio de la construccion
            $precioConstruccion = $metrosConstruccion * $precioPorMetroCuadradoConstruccion;

            // Calcular precio total
            $precioTotal = $precioTerreno + $precioConstruccion;

            // Depreciacion
            $depreciacion = $this->depreciacion('muy bueno');

            // Calcular precio total depreciado
            $precioTotalDepreciado = $precioTotal * $depreciacion;

            // Calcular valor total construccion depreciado
            // $valorTotalConstruccionDepreciado = $precioConstruccion * $depreciacion; WARN: Sugerida
            $precioTotalConstruccionDepreciado = $precioConstruccion - $precioTotalDepreciado;

            // Calcular valor final casa Depreciada
            if ($antiguedad >= 1) {
                $precioFinalCasaDepreciada = $precioTotal - ($precioTerreno + $precioTotalConstruccionDepreciado);
            } else {
                $precioFinalCasaDepreciada = $precioTotal;
            }

            // Llamada a funcion similitud
            $similitud = $this->calcularSimilitud($inmuebleBase, $inmueble);

            array_push(
                $arrayResultados,
                array(
                    "distancia" => $distancia,
                    "metrosTerreno" => $metrosTerreno,
                    "metrosConstruccion" => $metrosConstruccion,
                    "precio" => $precio,
                    "antiguedad" => $antiguedad,
                    "precioPorMetroCuadradoTerreno" => $precioPorMetroCuadradoTerreno,
                    "precioPorMetroCuadradoConstruccion" => $precioPorMetroCuadradoConstruccion,
                    "precioTerreno" => $precioTerreno,
                    "precioConstruccion" => $precioConstruccion,
                    "precioTotal" => $precioTotal,
                    "depreciacion" => $depreciacion,
                    "precioTotalDepreciado" => $precioTotalDepreciado,
                    "precioTotalConstruccionDepreciado" => $precioTotalConstruccionDepreciado,
                    "precioFinalCasaDepreciada" => $precioFinalCasaDepreciada,
                    "similitud" => $similitud
                )
            );
        }

        return $arrayResultados;
    }

    // Calcular similitud
    public function calcularSimilitud($propiedadBase, $propiedadComparando)
    {

        $similitud = 0;

        // WARN: ¿Por qué estos valores?
        $aniosSimilitud = 15;
        $construccionSimilitud = 37.5;
        $metrosSimilitud = 27.5;
        $cuartosSimilitud = 7.5;
        $baniosSimilitud = 7.5;

        $amenidadesSimilitud = 5;

        // Por antiguedad
        if ($propiedadComparando['antiguedad'] <= $propiedadBase['caracteristicas']['antiguedad']) {

            $similitud = $similitud + $aniosSimilitud;
        } else {

            $similitud = $similitud + ($propiedadComparando['antiguedad'] / $aniosSimilitud);
        }

        // Por metros de construccion
        if ($propiedadComparando['metros_construidos'] <= $propiedadBase['caracteristicas']['metrosConstruccion']) {

            $similitud = $similitud + $construccionSimilitud;
        } else {

            $similitud = $similitud + ($propiedadComparando['metros_construidos'] / $construccionSimilitud);
        }

        // Por metros totales
        if ($propiedadComparando['metros_totales'] <= $propiedadBase['metrosTerreno']) {

            $similitud = $similitud + $metrosSimilitud;
        } else {

            $similitud = $similitud + ($propiedadComparando['metros_totales'] / $metrosSimilitud);
        }

        // Por cuartos
        if ($propiedadComparando['recamaras'] <= $propiedadBase['caracteristicas']['cuartos']) {

            $similitud = $similitud + $cuartosSimilitud;
        } else {

            $similitud = $similitud + ($propiedadComparando['recamaras'] / $cuartosSimilitud);
        }

        // Por banios
        if ($propiedadComparando['banios'] <= $propiedadBase['caracteristicas']['banios']) {

            $similitud = $similitud + $baniosSimilitud;
        } else {

            $similitud = $similitud + ($propiedadComparando['banios'] / $baniosSimilitud);
        }

        return $similitud;
    }

    // Calcular precio propiedad base
    public function calcularPrecio($inmuebles, $propiedad)
    {

        // Promediamos el precio por metro cuadrado de construccion de todos los inmuebles
        $acumuladoPrecioPorMetroCuadradoConstruccion = array_sum(array_column($inmuebles, 'precioPorMetroCuadradoConstruccion'));
        $promedioPrecioPorMetroCuadradoConstruccion = $acumuladoPrecioPorMetroCuadradoConstruccion / count($inmuebles);

        // Promediamos el precio por metro cuadrado de terreno de todos los inmuebles
        $acumuladoPrecioPorMetroCuadradoTerreno = array_sum(array_column($inmuebles, 'precioPorMetroCuadradoTerreno'));
        $promedioPrecioPorMetroCuadradoTerreno = $acumuladoPrecioPorMetroCuadradoTerreno / count($inmuebles);

        // Calcular valor del terreno
        $precioTerreno = $propiedad['caracteristicas']['metrosTerreno'] * $promedioPrecioPorMetroCuadradoTerreno;

        // Calcular valor de la construccion
        $precioConstruccion = $propiedad['caracteristicas']['metrosConstruccion'] * $promedioPrecioPorMetroCuadradoConstruccion;

        // Calcular depreciacion
        $depreciacion = $this->depreciacion($propiedad['caracteristicas']['estado']);

        // Calcular valor de la construccion depreciado
        $precioConstruccionDepreciado = $precioConstruccion * $depreciacion;

        // Calcular valor total depreciado
        $precioTotalDepreciado = $precioTerreno + $precioConstruccionDepreciado;

        // Suma inmuebles depreciados
        $acumuladoPrecioInmueblesDepreciado = array_sum(array_column($inmuebles, 'precioTotalDepreciado'));
        $promedioPrecioInmueblesDepreciados = $acumuladoPrecioInmueblesDepreciado / count($inmuebles);

        // Calcular limite inferior
        $limiteInferiorTotal = $promedioPrecioInmueblesDepreciados * 0.8;

        // Calcular limite superior
        $limiteSuperiorTotal = $promedioPrecioInmueblesDepreciados * 1.2;

        // Calcular limite inferior metros cuadrados
        $limiteInferiorMetrosCuadrados = $promedioPrecioPorMetroCuadradoTerreno * 0.8;

        // Calcular limite superior metros cuadrados
        $limiteSuperiorMetrosCuadrados = $promedioPrecioPorMetroCuadradoTerreno * 1.2;

        // Calcular precio de renta
        $precioRenta = ($precioTotalDepreciado * 0.08) / 12;

        $export = array(
            "acumuladoPrecioPorMetroCuadradoConstruccion" => $acumuladoPrecioPorMetroCuadradoConstruccion,
            "promedioPrecioPorMetroCuadradoConstruccion" => $promedioPrecioPorMetroCuadradoConstruccion,
            "acumuladoPrecioPorMetroCuadradoTerreno" => $acumuladoPrecioPorMetroCuadradoTerreno,
            "promedioPrecioPorMetroCuadradoTerreno" => $promedioPrecioPorMetroCuadradoTerreno,
            "precioTerreno" => $precioTerreno,
            "precioConstruccion" => $precioConstruccion,
            "depreciacion" => $depreciacion,
            "precioConstruccionDepreciado" => $precioConstruccionDepreciado,
            "precioTotalDepreciado" => $precioTotalDepreciado,
            "acumuladoPrecioInmueblesDepreciado" => $acumuladoPrecioInmueblesDepreciado,
            "promedioPrecioInmueblesDepreciados" => $promedioPrecioInmueblesDepreciados,
            "limiteInferiorTotal" => $limiteInferiorTotal,
            "limiteSuperiorTotal" => $limiteSuperiorTotal,
            "limiteInferiorMetrosCuadrados" => $limiteInferiorMetrosCuadrados,
            "limiteSuperiorMetrosCuadrados" => $limiteSuperiorMetrosCuadrados,
            "precioRenta" => $precioRenta,
        );

        return $export;
    }

    // Calcular depreciacion
    public function depreciacion($estadoPropiedad, $anios = null)
    {
        $depreciacion = 0;

        // Calcular depreciacion
        // if (anios >= 10) {
        //         depreciacion = 0.50
        //     }else if(anios == 9){
        //         depreciacion = 0.45
        //     }else if(anios == 8){
        //         depreciacion = 0.40
        //     }else if(anios == 7){
        //         depreciacion = 0.35
        //     }else if(anios == 6){
        //         depreciacion = 0.30
        //     }else if(anios == 5){
        //         depreciacion = 0.25
        //     }else if(anios == 4){
        //         depreciacion = 0.20
        //     }else if(anios == 3){
        //         depreciacion = 0.15
        //     }else if(anios == 2){
        //         depreciacion = 0.10
        //     }else if(anios == 1){
        //         depreciacion = 0.05
        // }

        if ($estadoPropiedad === "muy bueno") {
            $depreciacion = 0.9968;
        } else if ($estadoPropiedad === "bueno") {
            $depreciacion = 0.9774;
        } else if ($estadoPropiedad === "regular") {
            $depreciacion = 0.9404;
        } else if ($estadoPropiedad === "malo") {
            $depreciacion = 0.7852;
        }

        return $depreciacion;
    }

}

?>