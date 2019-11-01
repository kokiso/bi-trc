<?php
  if(!isset($_SESSION)) 
  { 
      session_start(); 
  }

  require_once(__DIR__."/../conectaSqlServer.class.php");
  require_once(__DIR__."/../validaJSon.class.php");

  class infracaoTempoPersistencia{
    var $retorno="";

    function buscaInfracaoTempo($login) {
      $classe   = new conectaBd();
      $classe->conecta($login);

      $sql="";
      $sql.="SELECT top 10000";
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

    function buscaInfracaoTempoConsolidacao($login, $lote) {
      $classe   = new conectaBd();
      $classe->conecta($login);

      switch( $lote[0]->frota ){
        case "LP" : $frota=" AND (VCL.VCL_FROTA IN('L','P'))" ;break;
        case "L"  : $frota=" AND (VCL.VCL_FROTA='L')"         ;break;
        case "P"  : $frota=" AND (VCL.VCL_FROTA='P')"         ;break;
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
      $sql.="    DISTANCIA_PERCORRIDA ";
      $sql.=" FROM INFRACAO ";
      $sql.="    INNER JOIN MOTORISTA MTO ON CODIGO_MOTORISTA = MTR_CODIGO ";
      $sql.="    INNER JOIN EVENTO EVE ON CODIGO_EVENTO = EVE_CODIGO ";
      $sql.=" WHERE 1=1 ";

      $params   = array();
      $options  = array("Scrollable" => SQLSRV_CURSOR_FORWARD);
      $consulta = sqlsrv_query($_SESSION['conn'], $sql, $params, $options);

      return $consulta;
    }

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

    function insereConsolidacaoInfracao($login, $array) {
      $classe   = new conectaBd();
      $classe->conecta($login);
      
      $query = "";
      $query .= " INSERT INTO CONSOLIDACAO_INFRACAO (DATA_CONSOLIDACAO, ULTIMA_POSICAO_MOVIMENTO ) ";
      $query .= " VALUES ";
      $query .= " (GETDATE(), ".$array[3].") ";

      $params   = array();
      $options  = array();
      sqlsrv_query($_SESSION['conn'], $query, $params, $options);
    }

    function insereInfracao($login, $array) {
      $classe   = new conectaBd();
      $classe->conecta($login);

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
      $query .= "                      ,CODIGO_UNIDADE )";
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
      $query .= " ,".$array[16].")";  // CODIGO_UNIDADE

      //echo($query."\n");

      $params   = array();
      $options  = array();
      sqlsrv_query($_SESSION['conn'], $query, $params, $options);
    }
  }
?>