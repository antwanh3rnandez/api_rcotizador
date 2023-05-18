<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Calculation\Financial\CashFlow\Constant\Periodic\Payments;

class ControladorCotizador
{
    public static function ctrRecibirDatos($json)
    {
        $apiEndpoint = 'https://pruebacliente.toi.com.mx/comparativo/cliente';
        $data = json_decode($json, true);

        /**
         * ? Recibimos los datos que provienen del rCotizador.
         * * Los tranformamos para la realizacion de calculos.
         */

        /**
         * ! MONTOS INICIALES
         */
        $montos = array(
            "sueldoBruto" => $data['montos']['sueldoBruto'],
            "sueldoNeto" => $data['montos']['sueldoNeto'],
            "deudas" => $data['montos']['deudas'],
            "sueldoRestante" => $data['montos']['sueldoRestante'],
            "plazo" => $data['montos']['plazo']
        );

        /**
         * ! TASAS BANCARIAS Y COMISIONES
         */
        $tasaBanorte = 10.68;
        $comisionBanorte = 299;

        $tasaScotiabank = 11.8;

        $tasaHsbc = 10.75; //Se calcula en el codigo
        $comisionHsbc = 250;

        $tasaSantander = 11.55;
        $comisionSantander = 406;

        $tasaCitibanamex = 10.50;

        $tasaHeybanco = 11;
        $comisionHeyBanco = 250;

        $comisionAfirme = 462.84;

        /**
         * ! BANORTE  ==============================================================================
         */
        $pagoXMilBanorte = Payments::annuity(( ($tasaBanorte / 100) / 360 ) * 30.4, $data['montos']['plazo'], -1000 );
        $sueldoReqBanorte = ($data['montos']['sueldoBruto']-$data['montos']['deudas']);                                      
        $factorSVBanorte = 0.0543742999086155;
        $factorSDBanorte = 0.0211455610755727;
        $sueldoConComisionBanorte = ($sueldoReqBanorte/2) - $comisionBanorte;
        $seguroVidaBanorte = $sueldoConComisionBanorte * $factorSVBanorte;
        $seguroDaniosBanorte = $sueldoConComisionBanorte * $factorSDBanorte;
        $creditoBanorte = $sueldoConComisionBanorte - $seguroVidaBanorte - $seguroDaniosBanorte;
        $montoCreditoBanorte = ($creditoBanorte / $pagoXMilBanorte) * 1000;
        $valorViviendaBanorte = $montoCreditoBanorte / 0.90;
        $valorDestructibleBanorte = $valorViviendaBanorte * 0.7;

        /**
         * ! SCOTIABANK  ==============================================================================
         */
        $pagoXMilScotiabank = 11.00;
        $sueldoReqScotiabank = ($data['montos']['sueldoBruto']-$data['montos']['deudas']); 
        $factorSueldoScotiabank = 55 / 100;
        $factorConversionScotiabank = 1.09630360355133;
        $factorSVScotiabank = 0.0559160729597832;
        $factorSDScotiabank = 0.0319278447646168;
        $montoCreditoScotiabank = (($data['montos']['sueldoBruto'] * 1000) / $pagoXMilScotiabank) * $factorSueldoScotiabank;
        $valorViviendaScotiabank = $montoCreditoScotiabank / 0.95;
        $mensualidadScotiabank = ($montoCreditoScotiabank / 1000) * $pagoXMilScotiabank;
        $pagoMensual = $mensualidadScotiabank * $factorConversionScotiabank;
        $seguroVidaScotiabank = $pagoMensual * $factorSVScotiabank;
        $seguroDaniosScotiabank = $pagoMensual * $factorSDScotiabank;

        /**
         * ! HSBC ==============================================================================
         */
        //Q44
        $tipoHsbc = "Fijo";
        //Q36
        $concatenarTipoPlazoHsbc = $tipoHsbc.$data['montos']['plazo'];
        // Definir el array con matrices
        $tablaHsbc = array(
            array("Creciente240", "Creciente", 240, 10.75),
            array("Creciente180", "Creciente", 180, 10.75),
            array("Creciente120", "Creciente", 120, 10.50),
            array("Creciente60", "Creciente", 60, 10.25),
            array("Fijo240", "Fijo", 240, 10.75, 9.1),
            array("Fijo180", "Fijo", 180, 10.75, 10.16),
            array("Fijo120", "Fijo", 120, 10.50, 12.58),
            array("Fijo60", "Fijo", 60, 10.25, 20.66)
        );

        // Buscar el valor en la primera columna del array y devolver el $concatenarTipoPlazoHsbc en la cuarta columna
        //Q38
        foreach ($tablaHsbc as $filaHsbc) {
            if ($filaHsbc[0] == $concatenarTipoPlazoHsbc) {
                $tasaHsbc = $filaHsbc[3];
                break;
            }
        }
        //Q39
        if ($tipoHsbc == "Creciente") {
            $pagoXMilHsbc = Payments::annuity(($tasaHsbc / 100) / 12, $data['montos']['plazo'], -1000);
        } else {
            $indexHsbc = array_search($concatenarTipoPlazoHsbc, array_column($tablaHsbc, 0));
            $pagoXMilHsbc = $tablaHsbc[$indexHsbc][4];
        }

        $factorSueldoHsbc = (((($data['montos']['sueldoNeto']-$data['montos']['deudas']) * (100/100)) * (65/100))); 

        //B4
        $b4 = 0.42;


        $factorSVHsbc = 0.0384386121071183;
        $factorSDHsbc = 0.0290486082923794;
        $sueldoConComisionHsbc = $factorSueldoHsbc-$comisionHsbc;
        $seguroVidaHsbc = $sueldoConComisionHsbc*$factorSVHsbc;
        $seguroDaniosHsbc = $sueldoConComisionHsbc*$factorSDHsbc;

        $sueldoBaseHsbc = $factorSueldoHsbc-$comisionHsbc-$seguroDaniosHsbc-$seguroVidaHsbc;

        $sueldoReqHsbc = $sueldoConComisionHsbc-$seguroVidaHsbc-$seguroDaniosHsbc;
        $b8 = Payments::annuity(($tasaHsbc / 100) / 12, $data['montos']['plazo'], -1000); 
        $montoCreditoHsbc = ($sueldoBaseHsbc*1000) / $b8;
        $valorViviendaHsbc = $montoCreditoHsbc / 0.85;

        /**
         * ! SANTANDER ==============================================================================
         */
        $pagoXMilSantander = Payments::annuity(($tasaSantander / 100) / 12, $data['montos']['plazo'], -1000);
        $sueldoSantander = $data['montos']['sueldoNeto'];
        $factorSVSantander = 0.0004685;
        $factorSDSantander = 0.0003;

        //D33
        if ($sueldoSantander <= 187293.489830615) {
            $resultadoSantander = 1;
        } else if ($sueldoSantander > 187293.489830615 && $sueldoSantander < 204098.132728158) {
            $resultadoSantander = 2;
        } else {
            $resultadoSantander = 3;
        }

        $resultadoConcatenado = $resultadoSantander.$data['montos']['plazo'];

        //D36
        if ($resultadoSantander == 1) {
            $calculo = $sueldoSantander * 0.55;
        } else {
            $calculo = $sueldoSantander * 0.45;
        }

        //D37
        // Definir la matriz con los valores de la tabla
        $tabla = array(
            array(1240, 1, 240, 1.065599448),
            array(2240, 2, 240, 1.068325619),
            array(3240, 3, 240, 1.068325619),
            array(1180, 1, 180, 1.059915574),
            array(2180, 2, 180, 1.062405535),
            array(3180, 3, 180, 1.062405535),
            array(1120, 1, 120, 1.049817229),
            array(2120, 2, 120, 1.051887525),
            array(3120, 3, 120, 1.051887525)
        );
        // Buscar el valor de $resultadoConcatenado dentro de la matriz
        $indice = array_search($resultadoConcatenado, array_column($tabla, 0));
        // Tomar el valor correspondiente en la cuarta columna de la tabla
        $valorTabla = $tabla[$indice][3];
        // Realizar la operación
        $resultadoD37 = ($calculo - $comisionSantander) / $valorTabla;

        //D38
        if ($resultadoSantander == 2) {
            $montoCreditoSantander = 8000001;
        } else {
            $montoCreditoSantander = ($resultadoD37 / $pagoXMilSantander) * 1000;
        }
        
        //D39
        if ($resultadoSantander == 1) {
            $valorViviendaSantander = $montoCreditoSantander / 0.9;
        } else {
            $valorViviendaSantander = $montoCreditoSantander / 0.8;
        }

        //D40
        $valorDestructibleSantander = $valorViviendaSantander*0.7;
        //D41
        $seguroVidaSantander = $montoCreditoSantander * $factorSVSantander;
        //D42
        $seguroDaniosSantander = $montoCreditoSantander * $factorSDSantander;
        //D47
        $mensualidadSantander = $resultadoD37-$seguroVidaSantander-$seguroDaniosSantander-$comisionSantander;
        //D48
        if ($resultadoSantander == 2) {
            $sueldoReqSantander = $sueldoSantander;
        } elseif ($resultadoSantander == 1) {
            $sueldoReqSantander = $mensualidadSantander / 0.55;
        } else {
            $sueldoReqSantander = $mensualidadSantander / 0.45;
        }

        /**
         * ! CITIBANAMEX ==============================================================================
         */
        // $pagoXMilCitibanamex = round(ceil(Payments::annuity(($tasaCitibanamex / 100) / 12, $data['montos']['plazo'], -1000) * 100) / 100, 2);
        $sueldoReqCitibanamex = ($data['montos']['sueldoBruto']-$data['montos']['deudas']); 
        $factorSVCitibanamex = 0.00047400;
        $factorSDCitibanamex = 0.0003;
        $aforoCitibanamex = 0.9;
        
        if ($data['montos']['plazo'] == 240) {
            $plazo = array(
                //     S. Inf.      S. Sup.      Factor   Tasa   Plazo  PXM
                array(    1.0000, 21527.0000, 2.394394394, 10.50, 240, 9.99, 900000.00, 1499999.99),
                array(21528.0000, 34645.6667, 2.394394394, 10.50, 240, 9.99, 900000.00, 1499999.99),
                array(34646.6667, 166304.0000, 2.401016401, 9.95, 240, 9.62, 1500000.00, 7200000.00),
                array(166304.0100, 179999.9900, 2.401016401, 9.95, 240, 9.62, 7200001.00, 7333333.33),
                array(180000.00, 600000.00, 2.401016401, 9.50, 240, 9.33)
            );
        }else if($data['montos']['plazo'] == 180){
            $plazo = array(
                //     S. Inf.      S. Sup.      Factor   Tasa   Plazo  PXM
                array(    1.0000, 23667.0000, 2.377737593, 10.50, 180, 11.06, 900000.00, 1499999.99),
                array(23668.0000, 39446.6664, 2.377737593, 10.50, 180, 11.06, 900000.00, 1499999.99),
                array(39447.6664, 183904.0000, 2.382669983, 9.95, 180, 10.72, 1500000.00, 7200000.00),
                array(183904.0100, 204337.7778, 2.382669983, 9.95, 180, 10.72, 7200001.00, 9000000.00),
                array(204338.78, 600000.00, 2.382669983, 9.50, 180, 10.45)
            );
        }else if ($data['montos']['plazo'] == 120) {
            $plazo = array(
                //     S. Inf.      S. Sup.      Factor   Tasa   Plazo  PXM
                array(    1.0000, 23667.0000, 2.34962963, 10.50, 120, 13.50, 900000.00, 1499999.99),
                array(23668.0000, 47579.9997, 2.34962963, 10.50, 120, 13.50, 900000.00, 1499999.99),
                array(47580.9997, 223424.0000, 2.352624042, 9.95, 120, 13.19, 1500000.00, 7200000.00),
                array(223424.0100, 248248.8889, 2.352624042, 9.95, 120, 13.19, 7200001.00, 9000000.00),
                array(248249.89, 600000.00, 2.352624042, 9.50, 120, 12.94)
            );
        }

        foreach ($plazo as $i => $item) {
            $desde = $item[0];
            $hasta = $item[1];
            $factorCitibanamex = $item[2];
            $tasaCitibanamex = $item[3];
            $plazoCitibanamex = $item[4];
            // $pagoXMilCitibanamex = $item[5];
            $pagoXMilCitibanamex = round(ceil(Payments::annuity(($tasaCitibanamex / 100) / 12, $plazoCitibanamex, -1000) * 100) / 100, 2);
            
            //Verificar si el sueldo está dentro del rango del subarreglo actual
            if ($sueldoReqCitibanamex >= $desde && $sueldoReqCitibanamex <= $hasta) {
                
                $base1 = $sueldoReqCitibanamex/$factorCitibanamex;
                $base1X1000 = $base1*1000;
                $montoCreditoCitibanamex = $base1X1000/$pagoXMilCitibanamex;
                $valorViviendaCitibanamex = $montoCreditoCitibanamex / $aforoCitibanamex;

                $creditoTopado = (($valorViviendaCitibanamex-8000000) * 0.4) + 7200000;
                $base2 = ($creditoTopado / 1000) * $pagoXMilCitibanamex;
                $base3 = $base1-$base2;
                $creditoAdicional = ($base3 < 0) ? 0 : ($base3 * 1000 / $pagoXMilCitibanamex * 2.1312);
                $topeCredito = $montoCreditoCitibanamex;
                $montoCreditoCitibanamex = $creditoAdicional+$topeCredito;
                $valorViviendaCitibanamex = $montoCreditoCitibanamex/0.9;

                if ($valorViviendaCitibanamex >= 8000000) {
                    $valorTopevivienda = 8000000;
                    $diferencia = $valorViviendaCitibanamex-$valorTopevivienda;
                    $diferenciaX04 = $diferencia*0.4;
                    $montoTopadoCredito = $valorTopevivienda*0.9;
                    $montoCreditoCitibanamex = $montoTopadoCredito + $diferenciaX04;
                } else {
                    $montoCreditoCitibanamex = $valorViviendaCitibanamex * 0.9;
                }

                break; // Salir del bucle si se encuentra el rango correspondiente
            }
        }

        
        
        /**
         * ! HEYBANCO  ==============================================================================
         */
        $pagoXMilHeybanco = Payments::annuity(( ($tasaHeybanco / 100) / 360 ) * 30, $data['montos']['plazo'], -1000 );
        $sueldoReqHeyBanco = ($data['montos']['sueldoBruto']-$data['montos']['deudas']); 
        $sueldoConComisionHeyBanco = ($sueldoReqHeyBanco/2) - $comisionHeyBanco;
        $segurosHeyBanco = $sueldoConComisionHeyBanco * 0.0957950246421783; //Verificar Valor
        $sueldoMenosSeguroHeyBanco = $sueldoConComisionHeyBanco - $segurosHeyBanco;
        $sueldoMenosFactorHeyBanco = $sueldoMenosSeguroHeyBanco / 1.02382105006842; //Verificar valor
        $seguroVidaHeyBanco = $sueldoMenosFactorHeyBanco * 0.0580013063357283; //Verificar valor
        $seguroITPHeyBanco = $sueldoMenosFactorHeyBanco * 0.0145003265839321; //Verificar valor
        $seguroDesempleoHeyBanco = $sueldoMenosFactorHeyBanco * 0.0293925538863488; //Verificar valor
        $baseCreditoHeyBanco = $sueldoMenosFactorHeyBanco-$seguroVidaHeyBanco-$seguroITPHeyBanco-$seguroDesempleoHeyBanco;
        $montoCreditoHeyBanco = (($baseCreditoHeyBanco*1200)/100)/ ($tasaHeybanco/100);
        $valorViviendaHeyBanco = $montoCreditoHeyBanco / 0.90;

        /**
         * ! AFIRME  ==============================================================================
         */
        $plazosAfirme = $data['montos']['plazo'];
        $sueldoReqAfirme = ($data['montos']['sueldoBruto']-$data['montos']['deudas']); 
        //TASA AFIRME
        $datosAfirme = array(
            //    Tipo     Plazo Tasa   PPM    FACTOR1   SUELDO               FACTOR2      FACTOR3      FACTOR4  
            array(2400.117, 240, 11.70, 10.80, 1.00052, 36058.42, 72116.85, 0.011240221, 0.068391112, 0.007637282),
            array(1800.117, 180, 11.70, 11.81, 0.996407, 38967.64, 77935.27, 0.011240221, 0.063209809, 0.007058682),
            array(1200.117, 120, 11.70, 14.17, 0.99448, 46034.24, 92068.48, 0.011240221, 0.053385573, 0.005961603),
            array(600.117, 60, 11.70, 22.09, 0.63803, 70457.55, 140915.10, 0.011240221, 0.053385573, 0.005961603)
        );
        $datosAfirme2 = array(
            //    Tipo     Plazo Tasa   PPM    FACTOR1   SUELDO               FACTOR2      FACTOR3      FACTOR4  
            array(2400.12, 240, 12.00, 11.01, 1.001258, 36058.42, 72116.85, 0.011240221, 0.067147629, 0.007498421),
            array(1800.12, 180, 12.00, 12.00, 0.997104, 38967.64, 77935.27, 0.011240221, 0.062226505, 0.006948876),
            array(1200.12, 120, 12.00, 14.35, 0.994988, 46034.24, 92068.48, 0.011240221, 0.052754702, 0.005891153),
            array(600.12, 60, 12.00, 22.24, 0.641742, 70457.55, 140915.10, 0.011240221, 0.052754702, 0.005891153)
        ); 
        $indiceAfirme = array_search($plazosAfirme, array_column($datosAfirme, 1));

        if ($sueldoReqAfirme > $datosAfirme[$indiceAfirme][6]) {
            $indiceAfirme = array_search($plazosAfirme, array_column($datosAfirme2, 1));
            $tasaAfirme = $datosAfirme2[$indiceAfirme][2];
            $factor1Afirme = $datosAfirme2[$indiceAfirme][4];
            $factor2Afirme = $datosAfirme2[$indiceAfirme][7];
            $factor3Afirme = $datosAfirme2[$indiceAfirme][8];
            $factor4Afirme = $datosAfirme2[$indiceAfirme][9];
        } else {
            $tasaAfirme = $datosAfirme[$indiceAfirme][2];  
            $factor1Afirme = $datosAfirme[$indiceAfirme][4];
            $factor2Afirme = $datosAfirme[$indiceAfirme][7];
            $factor3Afirme = $datosAfirme[$indiceAfirme][8];
            $factor4Afirme = $datosAfirme[$indiceAfirme][9]; 
        }
        $sueldoReqEntreDosAfirme = $sueldoReqAfirme / 2;
        $porFactor2 = $sueldoReqEntreDosAfirme * $factor2Afirme;
        $subTotalAfirme = $sueldoReqEntreDosAfirme-$porFactor2;
        $seguroInternoAfirme = 98;
        $subTotalAfirme2 = $subTotalAfirme - $seguroInternoAfirme;
        $subTotalAfirme3 = $subTotalAfirme2 - $comisionAfirme;
        $seguroVidaAfirme = $subTotalAfirme3 * $factor3Afirme;
        $seguroDaniosAfirme = $subTotalAfirme3 * $factor4Afirme;
        $subTotalAfirme4 = $subTotalAfirme3 - $seguroVidaAfirme - $seguroDaniosAfirme;
        $porFactor1 = $subTotalAfirme4 * $factor1Afirme;
        $pagoXMilAfirme = Payments::annuity(($tasaAfirme / 100) / 12, $data['montos']['plazo'], -1000);
        $subTotalAfirme5 = $porFactor1 / $pagoXMilAfirme;

        $montoCreditoAfirme = $subTotalAfirme5 * 1000;
        $valorViviendaAfirme = $montoCreditoAfirme / 0.9;

        /**
         * * Una vez obtenidos los calculos los enviamos a los simuladores para obtener los datos restantes
         */

        $curlBanorte = curl_init();
        curl_setopt_array($curlBanorte, [
            CURLOPT_URL => $apiEndpoint."?fechaNacimiento=1970-01-01&producto=0&plazo=". $data['montos']['plazo']/12 ."&valorVivienda=".$valorViviendaBanorte."&valorProyecto=&valorViviendaAdicional=&porcentajeNotarial=6&tipoTaza=2&sueldo=".$sueldoReqBanorte."&subcuenta=&infonavit=&montoCredito=".$montoCreditoBanorte."&estado=TLAXCALA&pagos=0&terreno=&construccion=&adeudoActual=&importeCredito=0&presupuestoRemodelacion=",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
        ]);
        $responseBanorte = curl_exec($curlBanorte);
        $errBanorte = curl_error($curlBanorte);
        curl_close($curlBanorte);

        $curlScotiabank = curl_init();
        curl_setopt_array($curlScotiabank, [
            CURLOPT_URL => $apiEndpoint."?fechaNacimiento=1970-01-01&producto=0&plazo=". $data['montos']['plazo']/12 ."&valorVivienda=".$valorViviendaScotiabank."&valorProyecto=&valorViviendaAdicional=&porcentajeNotarial=6&tipoTaza=2&sueldo=".$sueldoReqScotiabank."&subcuenta=&infonavit=&montoCredito=".$montoCreditoScotiabank."&estado=TLAXCALA&pagos=0&terreno=&construccion=&adeudoActual=&importeCredito=0&presupuestoRemodelacion=",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
        ]);
        $responseScotiabank = curl_exec($curlScotiabank);
        $errScotiabank = curl_error($curlScotiabank);
        curl_close($curlScotiabank);

        $curlHsbc = curl_init();
        curl_setopt_array($curlHsbc, [
            CURLOPT_URL => $apiEndpoint."?fechaNacimiento=1970-01-01&producto=0&plazo=". $data['montos']['plazo']/12 ."&valorVivienda=".$valorViviendaHsbc."&valorProyecto=&valorViviendaAdicional=&porcentajeNotarial=6&tipoTaza=2&sueldo=".$sueldoReqHsbc."&subcuenta=&infonavit=&montoCredito=".$montoCreditoHsbc."&estado=TLAXCALA&pagos=0&terreno=&construccion=&adeudoActual=&importeCredito=0&presupuestoRemodelacion=",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
        ]);
        $responseHsbc = curl_exec($curlHsbc);
        $errHsbc = curl_error($curlHsbc);
        curl_close($curlHsbc);

        $curlSantander = curl_init();
        curl_setopt_array($curlSantander, [
            CURLOPT_URL => $apiEndpoint."?fechaNacimiento=1970-01-01&producto=0&plazo=". $data['montos']['plazo']/12 ."&valorVivienda=".$valorViviendaSantander."&valorProyecto=&valorViviendaAdicional=&porcentajeNotarial=6&tipoTaza=2&sueldo=".$sueldoReqSantander."&subcuenta=&infonavit=&montoCredito=".$montoCreditoSantander."&estado=TLAXCALA&pagos=0&terreno=&construccion=&adeudoActual=&importeCredito=0&presupuestoRemodelacion=",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
        ]);
        $responseSantander = curl_exec($curlSantander);
        $errSantander = curl_error($curlSantander);
        curl_close($curlSantander);

        /**
         * ! NO EXISTE CITI EN SIMULADORES
         */
        $curlCitibanamex = curl_init();
        curl_setopt_array($curlCitibanamex, [
            CURLOPT_URL => $apiEndpoint."?fechaNacimiento=1970-01-01&producto=0&plazo=". $data['montos']['plazo']/12 ."&valorVivienda=".$valorViviendaCitibanamex."&valorProyecto=&valorViviendaAdicional=&porcentajeNotarial=6&tipoTaza=2&sueldo=".$sueldoReqCitibanamex."&subcuenta=&infonavit=&montoCredito=".$montoCreditoCitibanamex."&estado=TLAXCALA&pagos=0&terreno=&construccion=&adeudoActual=&importeCredito=0&presupuestoRemodelacion=",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
        ]);
        $responseCitibanamex = curl_exec($curlCitibanamex);
        $errCitibanamex = curl_error($curlCitibanamex);
        curl_close($curlCitibanamex);

        $curlHeyBanco = curl_init();
        curl_setopt_array($curlHeyBanco, [
            CURLOPT_URL => $apiEndpoint."?fechaNacimiento=1970-01-01&producto=0&plazo=". $data['montos']['plazo']/12 ."&valorVivienda=".$valorViviendaHeyBanco."&valorProyecto=&valorViviendaAdicional=&porcentajeNotarial=6&tipoTaza=2&sueldo=".$sueldoReqHeyBanco."&subcuenta=&infonavit=&montoCredito=".$montoCreditoHeyBanco."&estado=TLAXCALA&pagos=0&terreno=&construccion=&adeudoActual=&importeCredito=0&presupuestoRemodelacion=",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
        ]);
        $responseHeyBanco = curl_exec($curlHeyBanco);
        $errHeyBanco = curl_error($curlHeyBanco);
        curl_close($curlHeyBanco);

        $curlAfirme = curl_init();
        curl_setopt_array($curlAfirme, [
            CURLOPT_URL => $apiEndpoint."?fechaNacimiento=1970-01-01&producto=0&plazo=". $data['montos']['plazo']/12 ."&valorVivienda=".$valorViviendaAfirme."&valorProyecto=&valorViviendaAdicional=&porcentajeNotarial=6&tipoTaza=2&sueldo=".$sueldoReqAfirme."&subcuenta=&infonavit=&montoCredito=".$montoCreditoAfirme."&estado=TLAXCALA&pagos=0&terreno=&construccion=&adeudoActual=&importeCredito=0&presupuestoRemodelacion=",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
        ]);
        $responseAfirme = curl_exec($curlAfirme);
        $errAfirme = curl_error($curlAfirme);
        curl_close($curlAfirme);
        
        if ($errBanorte) {
            echo "Error al conectar con los simuladores Error (errBanorte) #:" . $errBanorte;
        }elseif ($errScotiabank) {
            echo "Error al conectar con los simuladores Error (errScotiabank) #:" . $errScotiabank;
        }elseif ($errHsbc) {
            echo "Error al conectar con los simuladores Error (errHsbc) #:" . $errHsbc;
        }elseif ($errHeyBanco) {
            echo "Error al conectar con los simuladores Error (errHeyBanco) #:" . $errHeyBanco;
        }elseif ($errAfirme) {
            echo "Error al conectar con los simuladores Error (errAfirme) #:" . $errAfirme;
        } else {

            $jsBanorte = json_decode($responseBanorte);
            $mensualidadPromedioBanorte = $jsBanorte->banorte->montos->mensualidadPromedio ?? 0;
            $sueldoParaGastosBanorte = $sueldoReqBanorte-$mensualidadPromedioBanorte;
            $banorte = array(
                "tasa" => $tasaBanorte,
                "pagoXMil" => $pagoXMilBanorte,
                "sueldoReq" => $sueldoReqBanorte,
                "factorSV" => $factorSVBanorte,
                "factorSD" => $factorSDBanorte,
                "comision" => $comisionBanorte,
                "sueldoConComision" => $sueldoConComisionBanorte,
                "seguroVida" => $seguroVidaBanorte,
                "seguroDanios" => $seguroDaniosBanorte,
                "creditoBanorte" => $creditoBanorte,
                "montoCredito" => $montoCreditoBanorte,
                "valorVivienda" => $valorViviendaBanorte,
                "valorDestructible" => $valorDestructibleBanorte,
                "pagoTotalSinGastosIniciales" => $jsBanorte->banorte->montos->pagoTotalMensualidades ?? 0,
                "mensualidaPromedio" => $mensualidadPromedioBanorte,
                "aforo" => $jsBanorte->banorte->montos->aforo ?? 0,
                "sueldoParaGastos" => $sueldoParaGastosBanorte
            );

            $jsScotiabank = json_decode($responseScotiabank);
            $mensualidadPromedioScotiabank = $jsScotiabank->scotiabank->montos->mensualidadPromedio ?? 0;
            $sueldoParaGastosScotiabank = $sueldoReqScotiabank-$mensualidadPromedioScotiabank;
            $scotiabank = array(
                "tasa" => $tasaScotiabank,
                "pagoXMil" => $pagoXMilScotiabank,
                "factorSueldo" => $factorSueldoScotiabank,
                "seguroVida" => $seguroVidaScotiabank,
                "seguroDanios" => $seguroDaniosScotiabank,
                "sueldoReq" => $sueldoReqScotiabank,
                "montoCredito" => $montoCreditoScotiabank,
                "valorVivienda" => $valorViviendaScotiabank,
                "montoCredito" => $montoCreditoScotiabank,
                "valorVivienda" => $valorViviendaScotiabank,
                "pagoTotalSinGastosIniciales" => $jsScotiabank->scotiabank->montos->pagoTotalMensualidades ?? 0,
                "mensualidaPromedio" => $mensualidadPromedioScotiabank,
                "aforo" => $jsScotiabank->scotiabank->montos->aforo * 100 ?? 0,
                "sueldoParaGastos" => $sueldoParaGastosScotiabank
            );

            $jsHsbc = json_decode($responseHsbc);
            $mensualidadPromedioHsbc = $jsHsbc->hsbc->montos->mensualidadPromedio ?? 0;
            $sueldoParaGastosHsbc = $data['montos']['sueldoNeto']-$mensualidadPromedioHsbc;
            $hsbc = array(
                "tasa" => $tasaHsbc,
                "pagoXMil" => $pagoXMilHsbc,
                "factorSueldo" => $factorSueldoHsbc,
                "sueldoConComision" => $sueldoConComisionHsbc,
                "seguroVida" => $seguroVidaHsbc,
                "seguroDanios" => $seguroDaniosHsbc,
                "sueldoReq" => $sueldoReqHsbc,
                "montoCredito" => $montoCreditoHsbc,
                "valorVivienda" => $valorViviendaHsbc,
                "pagoTotalSinGastosIniciales" => $jsHsbc->hsbc->montos->pagoTotalMensualidades ?? 0,
                "mensualidaPromedio" => $mensualidadPromedioHsbc,
                "aforo" => $jsHsbc->hsbc->montos->aforo * 100 ?? 0,
                "sueldoParaGastos" => $sueldoParaGastosHsbc
            );

            $jsSantander = json_decode($responseSantander);
            $mensualidadPromedioSantander = $jsSantander->santander->montos->mensualidadPromedio ?? 0;
            $sueldoParaGastosSantander = $sueldoReqSantander-$mensualidadPromedioSantander;
            $santander = array(
                "tasa" => $tasaSantander,
                "pagoXMil" => $pagoXMilSantander,
                "seguroVida" => $seguroVidaSantander,
                "seguroDanios" => $seguroDaniosSantander,
                "sueldoReq" => $sueldoReqSantander,
                "montoCredito" => $montoCreditoSantander,
                "valorVivienda" => $valorViviendaSantander,
                "valorDestructible" => $valorDestructibleSantander,
                "pagoTotalSinGastosIniciales" => $jsSantander->santander->montos->pagoTotalMensualidades ?? 0,
                "mensualidaPromedio" => $mensualidadPromedioSantander,
                "aforo" => $jsSantander->santander->montos->aforo * 100 ?? 0,
                "sueldoParaGastos" => $sueldoParaGastosSantander
            );

            $jsCitibanamex = json_decode($responseCitibanamex);
            $mensualidadPromedioCitibanamex = $jsCitibanamex->citi->montos->mensualidadPromedio ?? 0;
            $sueldoParaGastosCitibanamex = $sueldoReqCitibanamex-$mensualidadPromedioCitibanamex;
            $citibanamex = array(
                "tasa" => $tasaCitibanamex,
                "pagoXMil" => $pagoXMilCitibanamex,
                "sueldoReq" => $sueldoReqCitibanamex,
                "montoCredito" => $montoCreditoCitibanamex,
                "valorVivienda" => $valorViviendaCitibanamex,
                "pagoTotalSinGastosIniciales" => $jsCitibanamex->citi->montos->pagoTotalMensualidades ?? 0,
                "mensualidaPromedio" => $mensualidadPromedioCitibanamex,
                "aforo" => $jsCitibanamex->citi->montos->aforo ?? 0,
                "sueldoParaGastos" => $sueldoParaGastosCitibanamex
            );

            $jsHeyBanco = json_decode($responseHeyBanco);
            $mensualidadPromedioHeyBanco = $jsHeyBanco->hey->montos->mensualidadPromedio ?? 0;
            $sueldoParaGastosHeyBanco = $sueldoReqHeyBanco-$mensualidadPromedioHeyBanco;
            $heyBanco = array(
                "tasa" => $tasaHeybanco,
                "pagoXMil" => $pagoXMilHeybanco,
                "sueldoReq" => $sueldoReqHeyBanco,
                "sueldoConComision" => $sueldoConComisionHeyBanco,
                "seguros" => $segurosHeyBanco,
                "sueldoMenosSeguro" => $sueldoMenosSeguroHeyBanco,
                "sueldoMenosFactor" => $sueldoMenosFactorHeyBanco,
                "seguroVida" => $seguroVidaHeyBanco,
                "seguroITP" => $seguroITPHeyBanco,
                "seguroDesempleo" => $seguroDesempleoHeyBanco,
                "baseCredito" => $baseCreditoHeyBanco,
                "montoCredito" => $montoCreditoHeyBanco,
                "valorVivienda" => $valorViviendaHeyBanco,
                "pagoTotalSinGastosIniciales" => $jsHeyBanco->hey->montos->pagoTotalMensualidades ?? 0,
                "mensualidaPromedio" => $mensualidadPromedioHeyBanco,
                "aforo" => $jsHeyBanco->hey->montos->aforo ?? 0,
                "sueldoParaGastos" => $sueldoParaGastosHeyBanco
            );

            $jsAfirme = json_decode($responseAfirme);
            $mensualidadPromedioAfirme = $jsAfirme->afirme->montos->mensualidadPromedio ?? 0;
            $sueldoParaGastosAfirme = $sueldoReqAfirme-$mensualidadPromedioAfirme;
            $afirme = array(
                "tasa" => $tasaAfirme,
                "pagoXMil" => $pagoXMilAfirme,
                "sueldoReq" => $sueldoReqAfirme,
                "seguroVida" => $seguroVidaAfirme,
                "seguroDanios" => $seguroDaniosAfirme,
                "seguroInterno" => $seguroInternoAfirme,
                "montoCredito" => $montoCreditoAfirme,
                "valorVivienda" => $valorViviendaAfirme,
                "pagoTotalSinGastosIniciales" => $jsAfirme->afirme->montos->pagoTotalMensualidades ?? 0,
                "mensualidaPromedio" => $mensualidadPromedioAfirme,
                "aforo" => $jsAfirme->afirme->montos->aforo ?? 0,
                "sueldoParaGastos" => $sueldoParaGastosAfirme
            );

            $export['montos'] = $montos;
            $export['banorte'] = $banorte;
            $export['scotiabank'] = $scotiabank;
            $export['hsbc'] = $hsbc;
            $export['santander'] = $santander;
            $export['citibanamex'] = $citibanamex;
            $export['heyBanco'] = $heyBanco;
            $export['afirme'] = $afirme;
    
            return $export;
        }

        
    }
}

?>
