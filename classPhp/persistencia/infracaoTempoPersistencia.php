<?php
  if(!isset($_SESSION)) 
  { 
      session_start(); 
  }

  require_once(__DIR__."/../conectaSqlServer.class.php");
  require_once(__DIR__."/../validaJson.class.php");

  class infracaoTempoPersistencia{
    var $retorno="";

    // Busca registros para realizar a consolidação
    function buscaInfracaoTempo($login) {
      $classe   = new conectaBd();
      $classe->conecta($login);

      $sql="";
      $sql.="SELECT";
      $sql.="   MVM_POSICAO";
      $sql.="  ,MTR_CODIGO";
      $sql.="  ,MTR_NOME";
      $sql.="  ,MVM_PLACA";
      $sql.="  ,VCL.VCL_FROTA";
      $sql.="  ,MVM_TURNO";
      $sql.="  ,MVM_CODMTR";
      $sql.="  ,MVM_CODEVE";
      $sql.="  ,EVE_CODEG";
      $sql.="  ,MVM_VELOCIDADE";
      $sql.="  ,CONVERT(VARCHAR(23),MVM_DATAGPS,127) AS MVM_DATAGPS";
      $sql.="  ,MVM_HORAGPS";
      $sql.="  ,MTR_RFID";
      $sql.="  ,MVM_ODOMETRO";
      $sql.="  ,UNI_CODIGO";
      $sql.="  ,MVM_ANOMES";
      $sql.="  FROM MOVIMENTO";
      $sql.="  LEFT OUTER JOIN EVENTO EVE ON MVM_CODEVE=EVE.EVE_CODIGO";
      $sql.="  LEFT OUTER JOIN VEICULO VCL ON MVM_PLACA=VCL.VCL_CODIGO";
      $sql.="  LEFT OUTER JOIN MOTORISTA MTR ON MVM_CODMTR=MTR.MTR_CODIGO";
      $sql.="  LEFT OUTER JOIN UNIDADE UNI ON MVM_CODUNI=UNI.UNI_CODIGO";
      $sql.="   WHERE 1 = 1";
      $sql.="   AND (MVM_POSICAO > (SELECT TOP 1 ULTIMA_POSICAO_MOVIMENTO FROM CONSOLIDACAO_INFRACAO ORDER BY COD_CONSOLIDACAO DESC))";
      $sql.="   AND (MVM_ENTRABI='S')";
      $sql.="   AND (EVE_MOVIMENTO='S')";
      $sql.="   AND (MVM_VELOCIDADE>0) AND (VCL.VCL_ENTRABI='S') ";
      $sql.="   AND ( MVM_CODEG IN ('EV', 'EVC', 'VN'))";
      $sql.=" ORDER BY MVM_CODVEI,CONVERT(VARCHAR(23),MVM_DATAGPS,127)";

      $params   = array();
      $options  = array("Scrollable" => SQLSRV_CURSOR_FORWARD);
      $consulta = sqlsrv_query($_SESSION['conn'], $sql, $params, $options);

      return $consulta;
    }

    // Busca os dados consolidados
    function buscaInfracaoTempoConsolidacao($login, $lote) {
      $classe   = new conectaBd();
      $classe->conecta($login);

      switch( $lote[0]->frota ){
        case "LP" : $frota=" AND (FROTA IN('L','P'))" ;break;
        case "L"  : $frota=" AND (FROTA='L')"         ;break;
        case "P"  : $frota=" AND (FROTA='P')"         ;break;
      };

      $sql="";
      $sql.="SELECT ";
      $sql.="    PLACA, ";
      $sql.="    FROTA, ";
      $sql.="    TURNO, ";
      $sql.="    POSICAO_INICIAL, ";
      $sql.="    CONVERT(VARCHAR(23),DATA_INICIAL,127) AS DATA_INICIAL,";
      $sql.="    POSICAO_FINAL, ";
      $sql.="    CONVERT(VARCHAR(23),DATA_FINAL,127) AS DATA_FINAL,";
      $sql.="    TEMPO, ";
      $sql.="    VELOCIDADE, ";
      $sql.="    VELOCIDADE_MAX, ";
      $sql.="    MTR_NOME, ";
      $sql.="    DESCALIBRADO, ";
      $sql.="    EVE_CODEG, ";
      $sql.="    RFID, ";
      $sql.="    DISTANCIA_PERCORRIDA, ";
      $sql.="    ERRO ";
      $sql.=" FROM INFRACAO ";
      $sql.="    LEFT OUTER JOIN MOTORISTA MTO ON CODIGO_MOTORISTA = MTR_CODIGO ";
      $sql.="    LEFT OUTER JOIN VEICULO VCL ON PLACA=VCL.VCL_CODIGO";
      $sql.="    LEFT OUTER JOIN EVENTO EVE ON CODIGO_EVENTO = EVE_CODIGO ";
      $sql.="    LEFT OUTER JOIN UNIDADE UNI ON CODIGO_UNIDADE = UNI_CODIGO ";
      $sql.=" WHERE 1=1 ";
      $sql.=$frota;
      $sql.="    AND CONVERT(TIME, TEMPO) > (select cast(dateadd(ms, ".$lote[0]->tempo."*1000, '00:00:00') AS TIME(3)))";
      if($lote[0]->poloCodigo != "") {
        $sql.="   AND ( UPPER(UNI.UNI_CODPOL) = UPPER('".$lote[0]->poloCodigo."')) AND (UNI.UNI_CODGRP = ".$lote[0]->poloGrupo.")";
      }
      if($lote[0]->unidadeCodigo != "") {
        $sql.="   AND CODIGO_UNIDADE = ".$lote[0]->unidadeCodigo;
      }
      if($lote[0]->dtini != "") {
        $sql.="   AND ANO_MES = ".$lote[0]->dtini;
      }
      if($lote[0]->infracao != "TODOS") {
        $sql.="   AND EVE_CODEG = '".$lote[0]->infracao."'";
      }

      $params   = array();
      $options  = array("Scrollable" => SQLSRV_CURSOR_FORWARD);
      $consulta = sqlsrv_query($_SESSION['conn'], $sql, $params, $options);

      return $consulta;
    }

    // Busca a velocidade máxima de um intervalo de movimento
    function buscaVelocidadeMaxima($login, $array) {
      $classe   = new conectaBd();
      $classe->conecta($login);

      $query = "";
      $query .= " select MAX(MVM_VELOCIDADE) as MVM_VELOCIDADE from MOVIMENTO ";
      $query .= " where MVM_DATAGPS between '".$array["DTINI"]."' ";
      $query .= " and '".$array["DTFIM"]."' and MVM_PLACA = '".$array["PLACA"]."' ";

      $params   = array();
      $options  = array("Scrollable" => SQLSRV_CURSOR_FORWARD);
      $consulta = sqlsrv_query($_SESSION['conn'], $query, $params, $options);

      return $consulta;
    }

    // Insere uma linha de histórico de consolidação
    function insereConsolidacaoInfracao($login, $qtdRegistros) {
      $classe   = new conectaBd();
      $classe->conecta($login);
      
      $query = "";
      $query .= " INSERT INTO CONSOLIDACAO_INFRACAO (DATA_CONSOLIDACAO, ULTIMA_POSICAO_MOVIMENTO, REGISTROS_IMPORTADOS ) ";
      $query .= " VALUES ";
      $query .= " (GETDATE(), (select top 1 MVM_POSICAO from MOVIMENTO order by MVM_POSICAO desc), ".$qtdRegistros.")";

      $params   = array();
      $options  = array();
      sqlsrv_query($_SESSION['conn'], $query, $params, $options);
    }

    // Adiciona o horário de consolidação na tabela de configuração
    function atualizaConfiguracaoConsolidacao($login) {
      $classe   = new conectaBd();
      $classe->conecta($login);
      
      $query = "";
      $query .= " UPDATE CONFIGURACAO_CONSOLIDACAO_INFRACAO SET DATA_CONSOLIDACAO = GETDATE() ";

      $params   = array();
      $options  = array();
      sqlsrv_query($_SESSION['conn'], $query, $params, $options);
    }

    // Insere um registro consolidado
    function insereInfracao($login, $array) {
      try {
        $classe   = new conectaBd();
        $classe->conecta($login);

        if ($array[5] == "") {
          $array[5] = "null";
          $array[14] = "null";
          $array[19] = 1;
        }

        $query = "";
        $query .= " INSERT INTO INFRACAO (PLACA";
        $query .= "                      ,FROTA ";
        $query .= "                      ,TURNO ";
        $query .= "                      ,POSICAO_INICIAL ";
        $query .= "                      ,DATA_INICIAL ";
        $query .= "                      ,POSICAO_FINAL ";
        $query .= "                      ,DATA_FINAL ";
        $query .= "                      ,TEMPO ";
        $query .= "                      ,VELOCIDADE ";
        $query .= "                      ,VELOCIDADE_MAX ";
        $query .= "                      ,CODIGO_MOTORISTA ";
        $query .= "                      ,DESCALIBRADO ";
        $query .= "                      ,CODIGO_EVENTO ";
        $query .= "                      ,RFID ";
        $query .= "                      ,DISTANCIA_PERCORRIDA ";
        $query .= "                      ,CODIGO_UNIDADE ";
        $query .= "                      ,ANO_MES ";
        $query .= "                      ,ERRO )";
        $query .= " VALUES ";
        $query .= " ('".$array[0]."'";  // PLACA
        $query .= " ,'".$array[1]."'";  // FROTA
        $query .= " ,'".$array[2]."'";  // TURNO
        $query .= " ,".$array[3];       // POSICAO_INICIAL
        $query .= " ,'".$array[4]."'";  // DATA_INICIAL
        $query .= " ,".$array[5];       // POSICAO_FINAL
        $query .= " ,'".$array[6]."'";  // DATA_FINAL
        $query .= " ,'".$array[7]."'";  // TEMPO
        $query .= " ,".$array[8];       // VELOCIDADE
        $query .= " ,".$array[9];       // VELOCIDADE_MAX
        $query .= " ,".$array[15];      // CODIGO_MOTORISTA
        $query .= " ,'".$array[11]."'"; // DESCALIBRADO
        $query .= " ,".$array[17];      // CODIGO_EVENTO
        $query .= " ,'".$array[13]."'"; // RFID
        $query .= " ,".$array[14];      // DISTANCIA_PERCORRIDA
        $query .= " ,".$array[16];      // CODIGO_UNIDADE
        $query .= " ,".$array[18]."";  // ANO_MES
        $query .= " ,".$array[19].")";  // ERRO

        $params   = array();
        $options  = array();
        sqlsrv_query($_SESSION['conn'], $query, $params, $options);
      } catch(Exception $e){
        echo('Erro - '.$e);
      }
      
    }
  }
?>