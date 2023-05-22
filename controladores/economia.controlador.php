<?php 

class Economia{

    public static function getEconomy($slug, $year)
    {

        $year = $year ?? 2021;

        $jsonEstados = file_get_contents('assets/json/clave_estados_entidad.json');
        $estados = json_decode($jsonEstados, true);
        $selectedState = $estados[array_search($slug, array_column($estados, 'slug'))];
        $codeState = $selectedState['clave'];
        /**
         * Ventas
         */
        $urlVentas = 'https://datamexico.org/api/data?Date+Year='.$year.'&Flow=2&State='.$codeState.'&Product+Level=4&cube=economy_foreign_trade_ent&drilldowns=HS4+4+Digit&measures=Trade+Value&parents=true&locale=es';

        $curlVentas = curl_init();
        curl_setopt_array($curlVentas, [
            CURLOPT_URL => $urlVentas,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
        ]);

        $responseVentas = curl_exec($curlVentas);
        $err = curl_error($curlVentas);
        curl_close($curlVentas);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            $responseVentasDecode = json_decode($responseVentas, true);
        }

        /**
         * Destinos
         */
        $urlDestinos = 'https://datamexico.org/api/data?Date+Year='.$year.'&State='.$codeState.'&Flow=2&Product+Level=4&cube=economy_foreign_trade_ent&drilldowns=Country&measures=Trade+Value&parents=true&locale=es';

        $curlDestinos = curl_init();
        curl_setopt_array($curlDestinos, [
            CURLOPT_URL => $urlDestinos,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
        ]);

        $responseDestinos = curl_exec($curlDestinos);
        $err = curl_error($curlDestinos);
        curl_close($curlDestinos);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            $responseDestinosDecode = json_decode($responseDestinos, true);
        }

        $ventasDataSet = $responseVentasDecode['data'];
        $destinosDataSet = $responseDestinosDecode['data'];

        $ventas = [];
        $ventas['parentCategories'] = [];
        $ventas['categories'] = [];
        $ventas['ventas'] = [];
        $ventas['totalVentas'] = 0;
        foreach ($ventasDataSet as $value) {
            // Genera valores aleatorios para los componentes de color rojo, verde y azul
            $rojo = rand(50, 205);
            $verde = rand(50, 205);
            $azul = rand(50, 205);
            // Concatena los valores de los componentes en formato hexadecimal
            $color = sprintf("#%02x%02x%02x", $rojo, $verde, $azul);
            $ventas['totalVentas'] += $value['Trade Value'];
            // Verificar si no esta en el array

            if (!in_array($value['Chapter 4 Digit ID'], $ventas['parentCategories'])) {
                $ventas['parentCategories'][$value['Chapter 4 Digit ID']] = array(
                    'name' => $value['Chapter 4 Digit'],
                    'color' => $color
                );
            }
            // if (!in_array($value['HS2 4 Digit'], $ventas['categories'])) {
            //     $ventas['categories'][] = $value['HS2 4 Digit'];
            // }
        }

        foreach ($ventasDataSet as $key => $venta) {
            
            $percent = ($venta['Trade Value'] * 100) / $ventas['totalVentas'];
            $ventas['ventas'][] = [
                'parentCategory' => $venta['Chapter 4 Digit'],
                'category' => $venta['HS2 4 Digit'],
                'title' => $venta['HS4 4 Digit'],
                'value' => $venta['Trade Value'],
                'percent' => $percent,
                'color' => $ventas['parentCategories'][$venta['Chapter 4 Digit ID']]['color']
            ];

        }

        $destinos = [];
        $destinos['totalDestinos'] = 0;
        foreach ($destinosDataSet as $key => $destino) {
            $destinos['totalDestinos'] += $destino['Trade Value'];
        }

        foreach ($destinosDataSet as $key => $destino) {
            $rojo = rand(50, 205);
            $verde = rand(50, 205);
            $azul = rand(50, 205);
            // Concatena los valores de los componentes en formato hexadecimal
            $color = sprintf("#%02x%02x%02x", $rojo, $verde, $azul);

            $percent = ($destino['Trade Value'] * 100) / $destinos['totalDestinos'];
            $destinos['destinos'][] = [
                'title' => $destino['Country'],
                'value' => $destino['Trade Value'],
                'percent' => $percent,
                'color' => $color
            ];
        }
        $export = array_merge($ventas, $destinos);
        return $export;
    }

}
