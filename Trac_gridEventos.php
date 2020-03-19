<?php
  session_start();

    try{     
        require("classPhp/conectaSqlServer.class.php");
        require("classPhp/validaJson.class.php"); 
        require("classPhp/removeAcento.class.php"); 
        require("classPhp/selectRepetidoTrac.class.php"); 
        print_r($_POST);
        $classe   = new conectaBd();
        $classe->conecta('INTEGRAR'); 
        $vldr       = new validaJson();          
        $retorno    = "";
        $polo = "";
        $unidade = "";
        $codEvento = "";
        $arrayGrid = [];
        $arrayFiltroUnidades = [];
        $arrayFiltroPolo = [];
        $auxForQuery = 0;
        $arrayEventos = [31, 78, 67, 65, 49];


        // echo $_SESSION['usr_grupos'];
        
        // BUSCA INICIAL DOS VALORES DO FILTRO DAS UNIDADES
        if($_SESSION['usr_cargo'] != 'ADM'){
          $grupos = $_SESSION['usr_grupos'];
          $grupos1 = str_replace('(', "", $grupos);
          $grupos2 = str_replace(")", "", $grupos1);
          $arrayGrupos = explode(",", $grupos2);
          for($i = 0; $i <= (sizeof($arrayGrupos) - 1); $i ++ ){
            $query_unidades = "SELECT DISTINCT UNI_CODIGO, UNI_NOME FROM UNIDADE
            LEFT JOIN GRUPO GPO ON UNI_CODGRP = GPO.GRP_CODIGO";
            if($_SESSION['usr_cargo'] != 'ADM'){
              $query_unidades.= " WHERE GPO.GRP_CODIGO = ".$arrayGrupos[$i]."";
            }


            $classe->msgSelect(false);
            $resultFiltroUnidades = $classe->select($query_unidades);
            // print_r($resultFiltroUnidades);
            if( $resultFiltroUnidades['retorno'] != "OK" ){
              trigger_error("Deu ruim!",  $resultFiltroUnidades['error']);  
            } else {
              array_push($arrayFiltroUnidades, $resultFiltroUnidades['dados']);
              // print_r($arrayFiltroUnidades);
              $jsonUnidades = json_encode($arrayFiltroUnidades,true);
              //echo $json;
            }; 
          }
      }else {
        $query_unidades = "SELECT DISTINCT UNI_CODIGO, UNI_NOME FROM UNIDADE
        LEFT JOIN GRUPO GPO ON UNI_CODGRP = GPO.GRP_CODIGO";

        $classe->msgSelect(false);
        $resultFiltroUnidades = $classe->select($query_unidades);
        
        if ($resultFiltroUnidades['retorno'] != "OK" ) {
          trigger_error("Deu ruim!",$resultFiltroUnidades ['error']);  
        } else {
          array_push($arrayFiltroUnidades, $resultFiltroUnidades['dados']);
          // print_r ($ arrayFiltroUnidades);
        }; 
      }


        //BUSCA INICIAL DOS VALORES DO FILTRO DOS POLOS
        if($_SESSION['usr_cargo'] != 'ADM'){
          $grupos = $_SESSION['usr_grupos'];
          $grupos1 = str_replace('(', "", $grupos);
          $grupos2 = str_replace(")", "", $grupos1);
          $arrayGrupos = explode(",", $grupos2);
          for($i = 0; $i <= (sizeof($arrayGrupos) - 1); $i ++ ){
            $query_filtro_polo = "SELECT DISTINCT POL_CODIGO, POL_NOME FROM POLO
            LEFT JOIN GRUPO GPO ON POL_CODGRP = GPO.GRP_CODIGO";
            if($_SESSION['usr_cargo'] != 'ADM'){
              $query_filtro_polo.= " WHERE GPO.GRP_CODIGO = ".$arrayGrupos[$i]."";
            }

            
            $classe->msgSelect(false);
            $resultFiltroPolo= $classe->select($query_filtro_polo);
            // print_r($resultFiltroPolo);
            if( $resultFiltroPolo['retorno'] != "OK" ){
              trigger_error("Deu ruim!",  $resultFiltroPolo['error']);  
            } else {
              array_push($arrayFiltroPolo,$resultFiltroPolo['dados']);
              // print_r($arrayFiltroPolo);
              $jsonPolo = json_encode($arrayFiltroPolo,true);
            //   echo $json;
            }; 
          }
      }else {
        $query_filtro_polo = "SELECT DISTINCT POL_CODIGO, POL_NOME FROM POLO
        LEFT OUTER JOIN GRUPO GPO ON POL_CODGRP = GPO.GRP_CODIGO";
        if ($_SESSION[ 'usr_cargo'] != 'ADM' ) {
          $query_filtro_polo.= "WHERE GPO.GRP_CODIGO=".$_SESSION['usr_grupos']."" ;
        }

        
        $classe->msgSelect(false);
        $resultFiltroPolo = $classe->select($query_filtro_polo);
        // print_r ($ resultFiltroPolo);
        if ($resultFiltroPolo['retorno'] != "OK") {
          trigger_error("Deu ruim!",$resultFiltroPolo['error']);  
        }else{
          array_push($arrayFiltroPolo,$resultFiltroPolo['dados']);
          // print_r ($ arrayFiltroPolo);
          $jsonPolo = json_encode ($arrayFiltroPolo, true);
        // echo $ json;
        }; 
      }

        // PEGA OS DADOS DO POST
        if(sizeof($_POST) > 0){
          // print_r($_POST);
          $polo = $_POST['poloFiltro'];
          $unidade = $_POST['unidadeFiltro'];
          $codEvento = $_POST['codEvento'];
          $dataIni = $_POST['data_ini'];
          $dataFim = $_POST['data_fim'];
        }





        // FILTRO POR POLO, UNIDADE, E HORAS
        if($polo != "" && $unidade != "" && $codEvento != "" && $dataIni != "" && $dataFim != ""){
          $grupos = $_SESSION['usr_grupos'];
          $grupos1 = str_replace('(', "", $grupos);
          $grupos2 = str_replace(")", "", $grupos1);
          $arrayGrupos = explode(",", $grupos2);
          for($i = 0; $i <= (sizeof($arrayGrupos) - 1); $i ++ ){
              $query_all_results_grid = " SELECT DISTINCT E.EVE_NOME, CONVERT(varchar, MVM_DATAGPS) AS DATAGPS,
              MVM_PLACA AS PLACA, MVM_CODPOL AS POL, U.UNI_NOME AS UNI,
              MVM_LOCALIZACAO AS LOCALIZACAO
              FROM MOVIMENTO
              LEFT JOIN EVENTO E ON E.EVE_CODIGO = MVM_CODEVE
              LEFT JOIN USUARIOUNIDADE UU ON UU.UU_CODUNI = MVM_CODUNI
              LEFT JOIN UNIDADE U ON U.UNI_CODIGO = UU.UU_CODUNI
              LEFT JOIN GRUPO GPO ON U.UNI_CODGRP = GPO.GRP_CODIGO
              where MVM_CODPOL = '".$polo."'
              AND MVM_CODUNI = '".$unidade."' AND GPO.GRP_CODIGO = '".$arrayGrupos[$i]."'
              AND MVM_CODEVE = '".$codEvento."'
              AND CONVERT(varchar, MVM_DATAGPS, 23) BETWEEN '".$dataIni."' AND '".$dataFim."'";
              

              echo $query_all_results_grid;
              $classe->msgSelect(false);
              $result=$classe->select($query_all_results_grid);
              if( $result['retorno'] != "OK" ){
                trigger_error("Deu ruim!",  $result['error']);  
              } else {
                  array_push($arrayGrid, $result['dados']);
                  $json = json_encode($arrayGrid,true);
                }             
            }
        // FILTRA POR POLO E TAMBEM UNIDADE
        }elseif($polo != "" && $unidade != "" && $dataIni != "" && $dataFim != ""){
          $grupos = $_SESSION['usr_grupos'];
          $grupos1 = str_replace('(', "", $grupos);
          $grupos2 = str_replace(")", "", $grupos1);
          $arrayGrupos = explode(",", $grupos2);
          for($i = 0; $i <= (sizeof($arrayGrupos) - 1); $i ++ ){
            for($j = 0; $j <= (sizeof($arrayEventos) -1); $j ++){
              $query_all_results_grid = " SELECT DISTINCT E.EVE_NOME, CONVERT(varchar, MVM_DATAGPS) AS DATAGPS,
              MVM_PLACA AS PLACA, MVM_CODPOL AS POL, U.UNI_NOME AS UNI,
              MVM_LOCALIZACAO AS LOCALIZACAO
              FROM MOVIMENTO
              LEFT JOIN EVENTO E ON E.EVE_CODIGO = MVM_CODEVE
              LEFT JOIN USUARIOUNIDADE UU ON UU.UU_CODUNI = MVM_CODUNI
              LEFT JOIN UNIDADE U ON U.UNI_CODIGO = UU.UU_CODUNI
              LEFT JOIN GRUPO GPO ON U.UNI_CODGRP = GPO.GRP_CODIGO
              where MVM_CODPOL = '".$polo."'
              AND MVM_CODUNI = '".$unidade."' AND GPO.GRP_CODIGO = '".$arrayGrupos[$i]."'
              AND MVM_CODEVE = $arrayEventos[$j]
              AND CONVERT(varchar, MVM_DATAGPS, 23) BETWEEN '".$dataIni."' AND '".$dataFim."'";

              // echo $query_all_results_grid;
              $classe->msgSelect(false);
              $result=$classe->select($query_all_results_grid);
              if( $result['retorno'] != "OK" ){
                trigger_error("Deu ruim!",  $result['error']);  
              } else {
                  array_push($arrayGrid, $result['dados']);
                  $json = json_encode($arrayGrid,true);
                }             
            }
          }
        // FILTRO POR POLO E HORA
        }elseif($polo != "" && $codEvento != "" && $dataIni != "" && $dataFim != ""){
          $grupos = $_SESSION['usr_grupos'];
          $grupos1 = str_replace('(', "", $grupos);
          $grupos2 = str_replace(")", "", $grupos1);
          $arrayGrupos = explode(",", $grupos2);
          for($i = 0; $i <= (sizeof($arrayGrupos) - 1); $i ++ ){
              $query_all_results_grid = " SELECT DISTINCT E.EVE_NOME, CONVERT(varchar, MVM_DATAGPS) AS DATAGPS,
              MVM_PLACA AS PLACA, MVM_CODPOL AS POL, U.UNI_NOME AS UNI,
              MVM_LOCALIZACAO AS LOCALIZACAO
              FROM MOVIMENTO
              LEFT JOIN EVENTO E ON E.EVE_CODIGO = MVM_CODEVE
              LEFT JOIN USUARIOUNIDADE UU ON UU.UU_CODUNI = MVM_CODUNI
              LEFT JOIN UNIDADE U ON U.UNI_CODIGO = UU.UU_CODUNI
              LEFT JOIN GRUPO GPO ON U.UNI_CODGRP = GPO.GRP_CODIGO
              where MVM_CODPOL = '".$polo."'
              AND MVM_CODEVE = ".$codEvento." AND GPO.GRP_CODIGO = '".$arrayGrupos[$i]."'
              AND CONVERT(varchar, MVM_DATAGPS, 23) BETWEEN '".$dataIni."' AND '".$dataFim."'";

              $classe->msgSelect(false);
              $result=$classe->select($query_all_results_grid);
              if( $result['retorno'] != "OK" ){
                trigger_error("Deu ruim!",  $result['error']);  
              } else {
                  array_push($arrayGrid, $result['dados']);
                  $json = json_encode($arrayGrid,true);
                }             
            }
        // FILTRA POR POLO
        }elseif($polo != "" && $dataIni != "" && $dataFim != "") {
          $grupos = $_SESSION['usr_grupos'];
          $grupos1 = str_replace('(', "", $grupos);
          $grupos2 = str_replace(")", "", $grupos1);
          $arrayGrupos = explode(",", $grupos2);
          for($i = 0; $i <= (sizeof($arrayGrupos) - 1); $i ++ ){
            for($j = 0; $j <= (sizeof($arrayEventos) -1); $j ++){
              $query_all_results_grid = " SELECT DISTINCT E.EVE_NOME, CONVERT(varchar, MVM_DATAGPS) AS DATAGPS,
              MVM_PLACA AS PLACA, MVM_CODPOL AS POL, U.UNI_NOME AS UNI,
              MVM_LOCALIZACAO AS LOCALIZACAO
              FROM MOVIMENTO
              LEFT JOIN EVENTO E ON E.EVE_CODIGO = MVM_CODEVE
              LEFT JOIN USUARIOUNIDADE UU ON UU.UU_CODUNI = MVM_CODUNI
              LEFT JOIN UNIDADE U ON U.UNI_CODIGO = UU.UU_CODUNI
              LEFT JOIN GRUPO GPO ON U.UNI_CODGRP = GPO.GRP_CODIGO
              where MVM_CODPOL = '".$polo."'
              AND MVM_CODEVE = ".$arrayEventos[$j]." AND GPO.GRP_CODIGO = '".$arrayGrupos[$i]."'
              AND CONVERT(varchar, MVM_DATAGPS, 23) BETWEEN '".$dataIni."' AND '".$dataFim."'";

              // echo $query_all_results_grid;
              $classe->msgSelect(false);
              $result=$classe->select($query_all_results_grid);
              if( $result['retorno'] != "OK" ){
                trigger_error("Deu ruim!",  $result['error']);  
              } else {
                  array_push($arrayGrid, $result['dados']);
                  $json = json_encode($arrayGrid,true);
                  
                }
                }
              }              
          
        // FILTRO POR UNIDADE E TAMBEM POR HORAS
        }elseif($unidade != "" && $codEvento != "" && $dataIni != "" && $dataFim != ""){
          $grupos = $_SESSION['usr_grupos'];
          $grupos1 = str_replace('(', "", $grupos);
          $grupos2 = str_replace(")", "", $grupos1);
          $arrayGrupos = explode(",", $grupos2);
          for($i = 0; $i <= (sizeof($arrayGrupos) - 1); $i ++ ){
              $query_all_results_grid = " SELECT DISTINCT E.EVE_NOME, CONVERT(varchar, MVM_DATAGPS) AS DATAGPS,
              MVM_PLACA AS PLACA, MVM_CODPOL AS POL, U.UNI_NOME AS UNI,
              MVM_LOCALIZACAO AS LOCALIZACAO
              FROM MOVIMENTO
              LEFT JOIN EVENTO E ON E.EVE_CODIGO = MVM_CODEVE
              LEFT JOIN USUARIOUNIDADE UU ON UU.UU_CODUNI = MVM_CODUNI
              LEFT JOIN UNIDADE U ON U.UNI_CODIGO = UU.UU_CODUNI
              LEFT JOIN GRUPO GPO ON U.UNI_CODGRP = GPO.GRP_CODIGO
              where UNI_CODIGO = '".$unidade."'
              AND MVM_CODEVE = ".$codEvento." AND GPO.GRP_CODIGO = '".$arrayGrupos[$i]."'
              AND CONVERT(varchar, MVM_DATAGPS, 23) BETWEEN '".$dataIni."' AND '".$dataFim."'";

              echo  $query_all_results_grid;
              $classe->msgSelect(false);
              $result=$classe->select($query_all_results_grid);
              if( $result['retorno'] != "OK" ){
                trigger_error("Deu ruim!",  $result['error']);  
              } else {
                  array_push($arrayGrid, $result['dados']);
                  $json = json_encode($arrayGrid,true);
                }             
            }
        // FILTRA POR UNIDADE
        }elseif($unidade != "" && $dataIni != "" && $dataFim != "") {
          $grupos = $_SESSION['usr_grupos'];
          $grupos1 = str_replace('(', "", $grupos);
          $grupos2 = str_replace(")", "", $grupos1);
          $arrayGrupos = explode(",", $grupos2);
          for($i = 0; $i <= (sizeof($arrayGrupos) - 1); $i ++ ){
            for($j = 0; $j <= (sizeof($arrayEventos) -1); $j ++){
              $query_all_results_grid = " SELECT DISTINCT E.EVE_NOME, CONVERT(varchar, MVM_DATAGPS) AS DATAGPS,
              MVM_PLACA AS PLACA, MVM_CODPOL AS POL, U.UNI_NOME AS UNI,
              MVM_LOCALIZACAO AS LOCALIZACAO
              FROM MOVIMENTO
              LEFT JOIN EVENTO E ON E.EVE_CODIGO = MVM_CODEVE
              LEFT JOIN USUARIOUNIDADE UU ON UU.UU_CODUNI = MVM_CODUNI
              LEFT JOIN UNIDADE U ON U.UNI_CODIGO = UU.UU_CODUNI
              LEFT JOIN GRUPO GPO ON U.UNI_CODGRP = GPO.GRP_CODIGO
              where UNI_CODIGO = '".$unidade."'
              AND MVM_CODEVE = ".$arrayEventos[$j]." AND GPO.GRP_CODIGO = '".$arrayGrupos[$i]."'
              AND CONVERT(varchar, MVM_DATAGPS, 23) BETWEEN '".$dataIni."' AND '".$dataFim."'";

              // echo $query_all_results_grid;
              $classe->msgSelect(false);
              $result=$classe->select($query_all_results_grid);
              if( $result['retorno'] != "OK" ){
                trigger_error("Deu ruim!",  $result['error']);  
              } else {
                  array_push($arrayGrid, $result['dados']);
                  $json = json_encode($arrayGrid,true);
                  
                }
                }
              }              
            }elseif($codEvento != "" && $dataIni != "" && $dataFim != ""){
          if($_SESSION['usr_cargo'] != 'ADM'){
            $grupos = $_SESSION['usr_grupos'];
            $grupos1 = str_replace('(', "", $grupos);
            $grupos2 = str_replace(")", "", $grupos1);
            $arrayGrupos = explode(",", $grupos2);
            for($i = 0; $i <= (sizeof($arrayGrupos) - 1); $i ++ ){
            $query_all_results_grid = "SELECT DISTINCT EVE_NOME AS NOME, CONVERT(varchar, MVM_DATAGPS) AS DATAGPS,
            MVM_PLACA AS PLACA, MVM_CODPOL AS POL, U.UNI_NOME AS UNI, MVM_LOCALIZACAO AS LOCALIZACAO
            FROM MOVIMENTO
            LEFT JOIN EVENTO E ON E.EVE_CODIGO = MVM_CODEVE
            LEFT JOIN USUARIOUNIDADE UU ON UU.UU_CODUNI = MVM_CODUNI
            LEFT JOIN UNIDADE U ON U.UNI_CODIGO = UU.UU_CODUNI
            LEFT JOIN GRUPO GPO ON U.UNI_CODGRP = GPO.GRP_CODIGO 
            WHERE GPO.GRP_CODIGO = '".$arrayGrupos[$i]."'
            AND MVM_CODEVE = '".$codEvento."'
            AND CONVERT(varchar, MVM_DATAGPS, 23) BETWEEN '".$dataIni."' AND '".$dataFim."'";

            // echo $query_all_results_grid;
            $classe->msgSelect(false);
            $result=$classe->select($query_all_results_grid);
            if( $result['retorno'] != "OK" ){
              trigger_error("Deu ruim!",  $result['error']);  
            } else {
              if($auxForQuery <= 0){
                $auxForQuery ++;
                array_push($arrayGrid, $result['dados']);
                $json = json_encode($arrayGrid,true);
                // print_r($arrayGrid);
              }else {
                if(sizeof($result['dados']) > 0){
                array_push($arrayGrid, $result['dados'][0]);
                // echo $query_all_results_grid;
                $json = json_encode($arrayGrid,true);
                //echo $json;
              }
            }
              
            };
          }
        }else {
          $query_all_results_grid = "SELECT DISTINCT E.EVE_NOME, CONVERT(varchar, MVM_DATAGPS) AS DATAGPS,
          MVM_PLACA AS PLACA, MVM_CODPOL AS POL, U.UNI_NOME AS UNI,
          MVM_LOCALIZACAO AS LOCALIZACAO
          FROM MOVIMENTO
          LEFT JOIN EVENTO E ON E.EVE_CODIGO = MVM_CODEVE
          LEFT JOIN USUARIOUNIDADE UU ON UU.UU_CODUNI = MVM_CODUNI
          LEFT JOIN UNIDADE U ON U.UNI_CODIGO = UU.UU_CODUNI
          LEFT JOIN GRUPO GPO ON U.UNI_CODGRP = GPO.GRP_CODIGO
          where MVM_CODEVE = '".$codEvento."'
          AND CONVERT(varchar, MVM_DATAGPS, 23) BETWEEN '".$dataIni."' AND '".$dataFim."'";

            $classe->msgSelect(false);
            $result=$classe->select($query_all_results_grid);
              //print_r($result);
            if( $result['retorno'] != "OK" ){
              trigger_error("Deu ruim!",  $result['error']);  
            } else {
              array_push($arrayGrid,$result['dados']);
              $json = json_encode($arrayGrid,true);
              //echo $json;
            }

          }
          
        }else{
          // FILTRA TUDO DA TABELA MOVIMENTOFILTO, ESSA TABELA JA ESTA FILTRADA COM VEICULOS QUE N POSICIONAM A 12H OU MAIS
          if($_SESSION['usr_cargo'] != 'ADM'){
            $grupos = $_SESSION['usr_grupos'];
            $grupos1 = str_replace('(', "", $grupos);
            $grupos2 = str_replace(")", "", $grupos1);
            $arrayGrupos = explode(",", $grupos2);
            for($i = 0; $i <= (sizeof($arrayGrupos) - 1); $i ++ ){
              for($j = 0; $j <= (sizeof($arrayEventos) -1); $j ++){
                $query_all_results_grid="SELECT DISTINCT E.EVE_NOME, CONVERT(varchar, MVM_DATAGPS) AS DATAGPS,
                MVM_PLACA AS PLACA, MVM_CODPOL AS POL, U.UNI_NOME AS UNI,
                MVM_LOCALIZACAO AS LOCALIZACAO
                FROM MOVIMENTO
                LEFT JOIN EVENTO E ON E.EVE_CODIGO = MVM_CODEVE
                LEFT JOIN USUARIOUNIDADE UU ON UU.UU_CODUNI = MVM_CODUNI
                LEFT JOIN UNIDADE U ON U.UNI_CODIGO = UU.UU_CODUNI
                LEFT JOIN GRUPO GPO ON U.UNI_CODGRP = GPO.GRP_CODIGO
                WHERE GPO.GRP_CODIGO = ".$arrayGrupos[$i]." AND MVM_CODEVE = '".$arrayEventos[$j]."'
                AND CONVERT(varchar, MVM_DATAGPS, 23) BETWEEN '".$dataIni."' AND '".$dataFim."'";

                // echo $query_all_results_grid;
                $classe->msgSelect(false);
                $result=$classe->select($query_all_results_grid);
                  //print_r($result);
                if( $result['retorno'] != "OK" ){
                  trigger_error("Deu ruim!",  $result['error']);  
                } else {
                  if($auxForQuery <= 0){
                    $auxForQuery ++;
                    array_push($arrayGrid, $result['dados']);
                    // print_r($arrayGrid);
                    // print_r($result['dados'][0]);
                    $json = json_encode($arrayGrid,true);
                    //echo $json;
                  }else {
                    if(sizeof($result['dados']) > 0){
                    array_push($arrayGrid, $result['dados']);
                    // echo $query_all_results_grid;
                    $json = json_encode($arrayGrid,true);
                    //echo $json;
                  }
                }
              }
            };
          }
        }else {
          // $query_all_results_grid = "	SELECT DISTINCT E.EVE_NOME, CONVERT(varchar, MVM_DATAGPS) AS DATAGPS,
          // MVM_PLACA AS PLACA, MVM_CODPOL AS POL, U.UNI_NOME AS UNI,
          // MVM_LOCALIZACAO AS LOCALIZACAO
          // FROM MOVIMENTO
          // LEFT JOIN EVENTO E ON E.EVE_CODIGO = MVM_CODEVE
          // LEFT JOIN USUARIOUNIDADE UU ON UU.UU_CODUNI = MVM_CODUNI
          // LEFT JOIN UNIDADE U ON U.UNI_CODIGO = UU.UU_CODUNI
          // LEFT JOIN GRUPO GPO ON U.UNI_CODGRP = GPO.GRP_CODIGO
          // where MVM_CODEVE = 65 or MVM_CODEVE = 31 or MVM_CODEVE = 49 or MVM_CODEVE = 78 OR MVM_CODEVE = 67";


          //   $classe->msgSelect(false);
          //   $result=$classe->select($query_all_results_grid);
          //   // print_r($result);
          //   if( $result['retorno'] != "OK" ){
          //     trigger_error("Deu ruim!",  $result['error']);  
          //   } else {
          //     array_push($arrayGrid,$result['dados']);
          //     $json = json_encode($arrayGrid,true);
          //     //echo $json;
          //   }

          }
        }
        
       } catch(Exception $e){
      } 
      
    ?>
    
        <!--BUSCA ALLLLL DA GRID  -->

      <!--           $query_all_results_grid = "SELECT CONVERT(varchar, X.MVM_DATAGPS) AS DATAGPS, X.MVM_PLACA AS PLACA, X.MVM_CODPOL AS POL FROM MOVIMENTO X 
          WHERE MVM_DATAGPS = (SELECT MAX(I.MVM_DATAGPS) FROM MOVIMENTO I
          WHERE X.MVM_CODVEI = I.MVM_CODVEI AND X.MVM_PLACA = I.MVM_PLACA)"; -->


    
    <!-- JS imports -->
    
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    
    <script src="https://code.jquery.com/jquery-3.3.1.js"></script> 
    <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script> 
    <script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js"></script> 
    <script src="https://cdn.datatables.net/select/1.3.1/js/dataTables.select.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
  
    <script src="https://cdn.datatables.net/buttons/1.6.1/js/dataTables.buttons.min.js"></script> 
    <script src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.bootstrap4.min.js"></script> 
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>  
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>  
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.html5.min.js"></script>
    <!-- <script src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.print.min.js"></script> -->
    <!-- <script src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.colVis.min.js"></script> -->
    
    <!-- CSS IMPORTS -->
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/1.3.1/css/select.bootstrap4.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <header>
      <title>Faturamento</title>
    </header>
    <nav class="navbar navbar-light bg-light" style="background-color: #ecf0f5;"></nav>
    <body style="background-color: #ecf0f5;">
      
     
      <nav class="navbar navbar-light bg-light">
        <a class="navbar-brand"></a>
        <img src="imagens/logoMaiorNew.png" >
        <form class="form-inline">
        </form>
      </nav>  
      <nav class="navbar navbar-light bg-light">
            <nav>
                <div class="form-row ">  
                  <form class="col-auto row" method="post" action="Trac_gridEventos.php">
                    <div>
                      <label style="margin-left: 20px; font-size: 12px;">Polo: </label>
                      <select style="width: 165px;" class=" btn mdb-select md-form colorful-select" name="poloFiltro" id="polofiltro" for="polofiltro">
                        <option selected value=""></option>
                        <?php  foreach($arrayFiltroPolo as $options) {?>
                        <?php foreach($options as $value) { ?>
                          <option style="font-size: 12px;" value="<?= $value[0] ?>" name="<?= $value[1] ?>"  id="<?= $value[0] ?>"><?= $value[1] ?></option>
                        <?php  } ?>
                        <?php }?>
                      </select>
                    </div>
                    <div>
                      <label  style="margin-left: 20px; font-size: 12px;">Unidade: </label>
                      <select style="width: 165px;" class=" btn mdb-select md-form colorful-select" name="unidadeFiltro" id="unidadeFiltro" for="unidadeFiltro">
                        <option selected value=""></option>
                        <?php  foreach($arrayFiltroUnidades as $options) {?>
                          <?php foreach($options as $value){ ?>
                          <option style="font-size: 12px;" value="<?= $value[0] ?>" name="<?= $value[1] ?>"  id="<?= $value[0] ?>"><?= $value[1] ?></option>
                          <?php  } ?>
                        <?php } ?>
                      </select>
                    </div>
                    <div>
                    <label style="margin-left: 20px; font-size: 12px;"> EVENTO: </label>
                      <select style="width: 165px;" class=" btn mdb-select md-form colorful-select" name="codEvento" id="codEvento" for="codEvento">
                          <option selected value=""></option>
                          <option style="font-size: 12px;" value="31" name="bateriaViolada"  id="bateriaViolada">Bateria Violada</option>
                          <option style="font-size: 12px;" value="49" name="moduloDesligado"  id="moduloDesligado">Módulo Desligado</option>
                          <option style="font-size: 12px;" value="65" name="descFontPrincipal"  id="descFontPrincipal">Desconectado da fonte principal</option>
                          <option style="font-size: 12px;" value="67" name="descBateBackup"  id="descBateBackup">Desconectado da bateria backup</option>
                          <option style="font-size: 12px;" value="78" name="falhaBateriaBackup"  id="falhaBateriaBackup">Falha na bateria backup</option>
                      </select> 
                    </div>
                    <div style="display: flex;">
                      <label style="padding: 5px;" class="">Inicio:</label> 
                      <input type="date" class="form-control" id="data_ini" name="data_ini">
                    </div>
                    <div style="display: flex;">
                      <label style="padding: 5px;">Fim:</label> 
                      <input type="date" class="form-control" id="data_fim" name="data_fim">
                    </div>
                    <button class="btn btn-dark" input="submit" id="buttomSubimit"><i class="fa fa-check"></i></button>
                  </form>
                <div>
            </nav>    
          </div>
        </div>
      </nav>
    <table id="example" class="table table-striped table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th>EVENTO</th>
                    <th>ULTIMO SINAL RECEBIDO</th>
                    <th>PLACA</th>
                    <th>POLO</th>
                    <th>UNIDADE</th>
                    <th>ULTIMA LOCALIZACAO</th>
                </tr>
            </thead>
            <tbody>
                  <?php foreach ( $arrayGrid as $índices ) { ?>
                    <?php foreach ( $índices as $valor => $key ) { ?>
                      <?php if(is_array($key)){?>
                      <tr>
                      <?php foreach($key as $value1) { ?>
                        <td> <?=  $value1; ?> </td>
                      <?php } ?>
                      </tr>
                    <?php }else { ?>
                      <tr>
                        <?php foreach ( $índices as $key ) { ?>
                          <td> <?=  $key; ?> </td>
                        <?php } ?>
                      </tr>
                  <?php } ?>
                <?php } ?>
              <?php } ?>
            </tbody>
        </table>
    
    <div>
        <!-- <a Data-toggle="modal" data-target="#exampleModal" data-whatever="@mdo" name="faturar" id="faturar" value="editar" class="btn btn-info active">Detalhes<i class="fa fa-edit"></i></a> -->
    </div>
    
    <!-- MODAL CONFIRMAR DIA DE VENCIMENTO -->
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Detalhes do Veiculo</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form>
              <div class="form-group">
                <label for="recipient-name" class="col-form-label">Dia:</label>
                <p><?php  print_r($_SESSION); ?></p>
              </div>
            </form>
            <div class="modal-footer">
              <button id="enviaDia" class="btn btn-primary">Salvar</button>
          </div>
          </div>
        </div>
      </div>
    </div>
    
    
    
    
    
    
    <script>
        $(document).ready(function() {
        var table = $('#example').DataTable({
            select: true,
            buttons: ['excel', 'pdf' ]
        });
     
        table.buttons().container()
                .appendTo( '#example_wrapper .col-md-6:eq(0)' );

        $('#example tbody').on( 'click', 'tr', function () {
            $(this).toggleClass('selected');
        } );
     
        // $('#button').click( function () {
        //     // alert( table.rows('.selected') +' row(s) selected' );
        //     for(let i = 0; i <= table.rows('.selected').data().length; i ++){
        //         // console.log(table.rows('.selected').data()[i]);
        //     }
        // } );
    
        $('#faturar').click(function() {
          // if(table.rows('.selected').data()[0].length > 0){
          //   console.log(table.rows('.selected').data()[0][1]);
          //   alert("Selecione apena uma linha da tabela");
          //   }else {
            
            placa = table.rows('.selected').data()[0][1];
            let url1 = 'Trac_DetalhesVeiculoSelecionado.php';
            $.ajax({
                data: {placa: placa},
                method: "get",
                url: url1
            })
            .done(function(data){
              console.log(data);
              // alert('Faturado com sucesso!');
            });
          // }
        })
    } );
    
    // RELACIONADO AO MODAL DO DIA DE VENCIMENTO
    $('#exampleModal').on('show.bs.modal', function (event) {
      var button = $(event.relatedTarget)
      var recipient = button.data('whatever') 
      var modal = $(this)
    })
    

    </script>
    
    
    <style>
    
    .nav-filtro {
      height: 120px
    }
    
    .button-filtro {
      position: absolute;
      top: 40;
      right: 10;
    }
    .button-back {
      position: absolute;
      top: 40;
      right: 65;
    }
    
    .div-collpse{
      right: 150px;
    }
    
    .voltar {
      color: #fff;
    }
    
    </style>