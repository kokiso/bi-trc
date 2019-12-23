<?php
session_start();
$opcao = $_POST['opcao'];
if( $opcao=='executeSql'){
  //https://docs.microsoft.com/pt-br/sql/connect/php/sqlsrv-errors
  $deadlock = true;
  while($deadlock) {
  $deadlock = false;
  $retorno = 'OK';
  $str     = ''; 
  try{
    $conn = sqlsrv_connect( $_SESSION["path"],$_SESSION["connInfo"] );
    if( $conn ) {
      if ( sqlsrv_begin_transaction( $conn ) === false ) {
        $retorno = 'ERR';
        $str     = '"'.str_replace('"','|',sqlsrv_errors()).'"';
      } else {
        $commitar   = true;
        $jsonObj    = json_decode($_POST['sql']);
        $lote       = $jsonObj->lote; 
        foreach ( $lote as $objlote ){
          $stmt = sqlsrv_query($conn, $objlote->comando);
          
          if( $stmt === false ){ 
            if( ($errors = sqlsrv_errors() ) != null) {
              //file_put_contents("aaa.xml",print_r(sqlsrv_errors(),true));
              if( $errors[0]['code']==0 ){
                $retorno  = "OK";
                $str      = "";
              } else {  
                $message  = $errors[0]['2'];
                $message  = str_replace("'","",$message);
                $message  = str_replace('Cannot insert the value NULL into','Nao aceito null para',$message);
                
                $message  = str_replace('The INSERT','O cadastro',$message);
                $message  = str_replace('The UPDATE','A alteracao',$message);
                $message  = str_replace('statement conflicted with the CHECK constraint','causou conflito com a checagem do campo',$message);
                $message  = str_replace('The conflict occurred in','O conflito ocorreu no',$message); 
                $message  = str_replace('column','coluna',$message); 
                
                $int      = strpos($message,"[SQL Server]");
                $str      = '"erronull'.str_replace('"','|',trim(substr($message,($int+12),strlen($message)))).'"';
                $commitar = false;      
                break;
              };
            };  
          };
        };
        if($commitar) {
          sqlsrv_commit($conn);
          $str='[{"EXECUTE":"OK"}]';
        } else {
          $retorno  = 'ERR';
          sqlsrv_rollback($conn);
          sqlsrv_close($conn);
        }; 
      }
    } else {
      $retorno  = 'ERR';
      $arr      = sqlsrv_errors();
      $int      = strpos($arr[0]["message"],"[SQL Server]");
      $str      = '"'.trim(substr($arr[0]["message"],($int+12),strlen($arr[0]["message"]))).'"';
    }
  } catch (Exception $e){
    $retorno = 'ERR';
    $str     = '"'.str_replace('"','|',$e->getMessage()).'"';    
  }
  if (strpos($str, 'deadlock')) {
    $str = 'Falha ao executar a operação. Tente novamente.';
    $deadlock = true;
  } else {
    echo '[{"retorno":"'.$retorno.'","dados":'.$str.'}]'; 
    exit;
  }
}
  exit;
};
///////////////////////////////////////////////
// Se for retornar um array naum ASSOCIATIVO //
///////////////////////////////////////////////
if( $opcao=='selectRow' ){
  $retorno = 'OK';
  $str     = '';
  try{
    $sql   = $_POST['sql'];
    $conn = sqlsrv_connect( $_SESSION["path"],$_SESSION["connInfo"] );
    if( $conn ) {
      try{
        $qtosReg  = 0;
        $reg      = array();
        $params   = array();
        $options  = array("Scrollable" => SQLSRV_CURSOR_KEYSET);
        $consulta = sqlsrv_query($conn, $sql, $params, $options);
        while ($linha = sqlsrv_fetch_array($consulta, SQLSRV_FETCH_NUMERIC)) {
          $registro=array();
          foreach($linha as $campo)
            $registro[]=utf8_encode($campo);
          $reg[]=$registro;
          $qtosReg++;
        };
        $str=json_encode($reg);
      } catch (Exception $e){
        $this->retorno=["retorno"=>"ERR","dados"=>"","erro"=>$e];  
      }  
    }  
  } catch (Exception $e) {
    $retorno = 'ERR';
    $str     = '"'.str_replace('"','|',$e->getMessage()).'"';    
  }
  echo '[{"retorno":"'.$retorno.'","dados":'.$str.'}]';  
}
//////////////////////////////////////////
// Se for retornar um array ASSOCIATIVO //
//////////////////////////////////////////
if( $opcao=='selectAssoc' ){
  $retorno = 'OK';
  $str     = '';
  try{
    $sql   = $_POST['sql'];
    $conn = sqlsrv_connect( $_SESSION["path"],$_SESSION["connInfo"] );
    if( $conn ) {
      try{
        $qtosReg  = 0;
        $reg      = array();
        $params   = array();
        $options  = array("Scrollable" => SQLSRV_CURSOR_KEYSET);
        $consulta = sqlsrv_query($conn, $sql, $params, $options);
        $registro=array();
        while ($linha = sqlsrv_fetch_array($consulta, SQLSRV_FETCH_ASSOC)) {
          $registro[]=array_map('utf8_encode', $linha);
          $qtosReg++;
        };
        $str=json_encode($registro);
      } catch (Exception $e){
        $this->retorno=["retorno"=>"ERR","dados"=>"","erro"=>$e];  
      }  
    }  
  } catch (Exception $e) {
    $retorno = 'ERR';
    $str     = '"'.str_replace('"','|',$e->getMessage()).'"';    
  }
  echo '[{"retorno":"'.$retorno.'","dados":'.$str.'}]';  
}
?>