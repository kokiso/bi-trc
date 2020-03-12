<?php
  session_start();

    require("classPhp/conectaSqlServer.class.php");
    require("classPhp/validaJson.class.php"); 
    require("classPhp/removeAcento.class.php"); 
    require("classPhp/selectRepetidoTrac.class.php"); 


    $classe   = new conectaBd();
    $classe->conecta('INTEGRAR'); 
    $vldr       = new validaJson();       

    // SELECT DETALHES
    if(isset($_GET['placa'])){
        $placaParaDetalhes = $_GET['placa'];
        $query_detalhes_result = "SELECT CONVERT(varchar, X.MVM_DATAGPS) AS DATAGPS,
        X.MVM_PLACA AS PLACA, X.MVM_CODPOL AS POL, X.MVM_CODUNI AS UNI, X.MVM_CODVEI AS VEI, X.MVM_LOCALIZACAO AS LOCALIZACAO
        FROM MOVIMENTO X
        WHERE MVM_PLACA = '".$placaParaDetalhes."' AND
        X.MVM_DATAGPS = (SELECT MAX(I.MVM_DATAGPS) FROM MOVIMENTO I
        WHERE X.MVM_CODVEI = I.MVM_CODVEI AND X.MVM_PLACA = I.MVM_PLACA)";

        $classe->msgSelect(false);
        $result=$classe->select($query_detalhes_result);
        if( $result['retorno'] != "OK" ){
            trigger_error("Deu ruim!",  $result['error']);  
        } else {
            $arrayDetalhes = $result['dados'];
            $_SESSION['arrayDetalhes'] = $arrayDetalhes;
            $arryRetorno = strval($arrayDetalhes[0][0]) + ',' + strval($arrayDetalhes[0][1]) + ',' + strval($arrayDetalhes[0][2]) + ','
            + strval($arrayDetalhes[0][3]) + ',' + strval($arrayDetalhes[0][4]) + ',' + strval($arrayDetalhes[0][5]);
            echo $arryRetorno;
        }; 
    }
  


?>