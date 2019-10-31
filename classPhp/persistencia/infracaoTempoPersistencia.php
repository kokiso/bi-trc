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
      $sql.="  MVM_POSICAO";
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
      $sql.="  FROM MOVIMENTO";
      $sql.="  LEFT OUTER JOIN EVENTO EVE ON MVM_CODEVE=EVE.EVE_CODIGO";
      $sql.="  LEFT OUTER JOIN VEICULO VCL ON MVM_PLACA=VCL.VCL_CODIGO";
      $sql.="  LEFT OUTER JOIN MOTORISTA MTR ON MVM_CODMTR=MTR.MTR_CODIGO";
      $sql.="  LEFT OUTER JOIN UNIDADE UNI ON MVM_CODUNI=UNI.UNI_CODIGO";          
      $sql.="   WHERE 1 = 1"; 	
      $sql.="   AND (MVM_POSICAO > (SELECT TOP 1 ULTIMA_POSICAO_MOVIMENTO FROM CONSOLIDACAO_TEMPO_INFRACOES ORDER BY COD_CONSOLIDACAO DESC))"; 
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

    function insereHistoricoConsolidacao($login, $array) {
      $classe   = new conectaBd();
      $classe->conecta($login);

      $query = "";
      $query .= " INSERT INTO CONSOLIDACAO_TEMPO_INFRACOES (DATA_CONSOLIDACAO, ULTIMA_POSICAO_MOVIMENTO ) ";
      $query .= " VALUES ";
      $query .= " (GETDATE(), ".$array[3].") ";

      $params   = array();
      $options  = array();
      sqlsrv_query($_SESSION['conn'], $query, $params, $options);
    }
  }
?>