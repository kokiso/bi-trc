<?php
  class conectaBd{
    //////////////////////////////////////////////////////////////////////////
    // Criando os atributos da classe                                       //
    // Estas podem se acessadas pelo criador da classe obj->classe="JOSE";  //
    //////////////////////////////////////////////////////////////////////////
    var $login;
    var $select;
    var $gdb;
    var $tr;
    var $retorno;
    //////////////////////////////////////////////////////////////////////////////////////
    // Ao criar o objeto posso passar como padrão  o usuario e senha do banco de dados  //
    //////////////////////////////////////////////////////////////////////////////////////
    function __construct(){
      $this->vetor          = array();
      //////////////////////////////////////////////////////////////////////////////////////
      // msgSelectVazio                                                                   //
      // Propriedade para retornar mensagem quando um select não retorna nenhum registro  //
      //////////////////////////////////////////////////////////////////////////////////////
      $this->msgSelectVazio = true;
      ////////////////////////////////////////////////////////////////////
      // msgErro                                                        //  
      // Opção para alterar com o nome da tabela que se refere o select //
      ////////////////////////////////////////////////////////////////////
      $this->msgErro="NENHUM REGISTRO LOCALIZADO PARA ESTA OPÇÃO!";
      array_push($this->vetor,
        array("CONECTA"  =>  array(
          ["login"=>"ATLAS"     , "path"=>"127.0.0.1" , "cnpj"=>"07116306000157","user"=>"sa","pass"=>"t3cn0l0g!"]    //Bd oficial
         ,["login"=>"ATLAS1"    , "path"=>"127.0.0.1" , "cnpj"=>"18269882000150","user"=>"atlas","pass"=>"atlas=123"] //Bd desenvolvimento
         ,["login"=>"ATLAS2"    , "path"=>"127.0.0.1" , "cnpj"=>"18269882000150","user"=>"sa","pass"=>"BIS@18"]       //Bd desenvolvimento
         ,["login"=>"ATLAS3"    , "path"=>"127.0.0.1" , "cnpj"=>"18269882000150","user"=>"sa","pass"=>"BIS@18"]       //Bd desenvolvimento
         ,["login"=>"ATLAS4"    , "path"=>"127.0.0.1" , "cnpj"=>"18269882000150","user"=>"sa","pass"=>"BIS@18"]       //Bd desenvolvimento
         ,["login"=>"INTEGRAR"  , "path"=>"192.168.1.51,1433" , "cnpj"=>"18269882000150","user"=>"sa","pass"=>"@A1111111"]       //Bd para ler base de dados SistemSat
         ,["login"=>"INTEGRAR1" , "path"=>"127.0.0.1" , "cnpj"=>"18269882000150","user"=>"sa","pass"=>"BIS@2018"]       //Bd para ler base de dados SistemSat
         )
        )
      );    
    }
    //--
    function conecta($login){
      $_SESSION["path"]= "";
      $_SESSION["user"]= "";
      $_SESSION["pass"]= "";
      
      $retorno        = "";
      $this->retorno  = ["retorno"=>"OK","dados"=>"","erro"=>""];  
      $this->login    = $login;
      $this->msgErro="NENHUM REGISTRO LOCALIZADO PARA ESTA OPÇÃO! ".$login;
      foreach($this->vetor[0]["CONECTA"] as $cnct):
        if( $cnct["login"]==$login):
          $_SESSION["login"] = $cnct["login"];
          $_SESSION["path"]  = $cnct["path"];
          $_SESSION["user"]  = $cnct["user"];
          $_SESSION["pass"]  = $cnct["pass"];
          $_SESSION["connInfo"]=array("Database" => $_SESSION["login"], "UID" => $_SESSION["user"], "PWD" => $_SESSION["pass"]);
        endif;
      endforeach;
      
      if( $_SESSION["path"]=="" ):
        $this->retorno=["retorno"=>"ERR","dados"=>"","erro"=>"LOGIN ".$login." NAO LOCALIZADO!"];  
      else:
        $conn = sqlsrv_connect( $_SESSION["path"],$_SESSION["connInfo"] );
        $_SESSION['conn']=$conn;
        if( !$conn ) {
          $this->retorno=["retorno"=>"ERR","dados"=>"","erro"=>"LOGIN ".print_r( sqlsrv_errors(), true)." NAO LOCALIZADO!"];
          die( print_r( sqlsrv_errors(), true));
        };  
      endif;
    }
    ////////////////////////////////////////////////////////////////////////
    // PARAMETRO PARA MENSAGEM QUANDO SELECT RETORNAR SEM NENHUM REGISTRO //
    ////////////////////////////////////////////////////////////////////////
    function msgSelect($bool){
      $this->msgSelectVazio=$bool;
    }  
    ////////////////////////////////////////
    // PARAMETRO PARA MENSAGEM DE RETORNO //
    ////////////////////////////////////////
    function msg($str){
      $this->msgErro=$str;
    }  
    ////////////////////////
    // SELECT ASSOCIATIVO //
    ////////////////////////
    function selectAssoc($select){
      if( $this->retorno['retorno'] != "ERR" ){
        try{
          $qtosReg  = 0;
          $reg      = array();
          $params   = array();
					$options  = array("Scrollable" => SQLSRV_CURSOR_FORWARD);
          //$options  = array("Scrollable" => SQLSRV_CURSOR_KEYSET);
          $consulta = sqlsrv_query($_SESSION['conn'], $select, $params, $options);          

          while ($linha = sqlsrv_fetch_array($consulta, SQLSRV_FETCH_ASSOC)) {
            $reg[]=$linha;
            $qtosReg++;
          };
          if( $qtosReg>0 ){          
            $this->retorno=[
               "retorno"=>"OK"
              ,"dados"=>$reg
              ,"erro"=>""
              ,"qtos"=>$qtosReg
            ]; 
          } else {
            //////////////////////////////////////////////////////////////////////////////////////////
            // Alguns selects podem retornar vazio e não precisam gerar a mensagem de erro, ex:SPED //
            //////////////////////////////////////////////////////////////////////////////////////////
            if( $this->msgSelectVazio )
              $this->retorno=["retorno"=>"ERR","dados"=>[],"erro"=>$this->msgErro,"qtos"=>0];
            else            
              $this->retorno=["retorno"=>"OK","dados"=>[],"erro"=>"","qtos"=>$qtosReg];    
          };  
        } catch (Exception $e){
          $this->retorno=["retorno"=>"ERR","dados"=>"","erro"=> $this->login." ".substr(str_replace(["\r","\n","{","}","\\",'"'],"",utf8_decode($e)),0,120)];  
        };  
      };  
      return $this->retorno;
    }  
    ////////////////////////////
    // SELECT NAO ASSOCIATIVO //
    ////////////////////////////
    function select($select){
      if( $this->retorno['retorno'] != "ERR" ){
        try{
          $qtosReg  = 0;
          $reg      = array();
          $params   = array();
					$options  = array("Scrollable" => SQLSRV_CURSOR_FORWARD);
          //$options  = array("Scrollable" => SQLSRV_CURSOR_KEYSET);
          $consulta = sqlsrv_query($_SESSION['conn'], $select, $params, $options);
          while ($linha = sqlsrv_fetch_array($consulta, SQLSRV_FETCH_NUMERIC)) {
            $registro=array();
            foreach($linha as $campo)
              $registro[]=utf8_encode($campo);
            $reg[]=$registro;
            $qtosReg++;
          };
          if( $qtosReg>0 ){          
            $this->retorno=["retorno"=>"OK","dados"=>$reg,"erro"=>""];  
          } else {
            $this->retorno=["retorno"=>"OK","dados"=>[],"erro"=>""];      
          };  
        } catch (Exception $e){
          $this->retorno=["retorno"=>"ERR","dados"=>"","erro"=>$e];  
        }  
      }  
      return $this->retorno;
    }  
    //////////////////
    // ATUALIZANDO  //
    //////////////////
    function cmd($atualiza){
//file_put_contents("aaa.xml",print_r($this->retorno,true));      
      if( $this->retorno['retorno'] != "ERR" ){      
        try{
          if( count($atualiza)==0 )
            throw new Exception('NENHUMA INSTRUCAO SQL PARA SER EXECUTADA');          
          
          if ( sqlsrv_begin_transaction( $_SESSION['conn'] ) === false ) {
            $arr      = sqlsrv_errors();
            $int      = strpos($arr[0]["message"],"[SQL Server]");
            $str      = trim(substr($arr[0]["message"],($int+12),strlen($arr[0]["message"])));
            $this->retorno=["retorno"=>"ERR","dados"=>"","erro"=> substr($str,0,300)];
          } else {
            $params   = array();
            $commitar = true;
            foreach( $atualiza as $atu ){
              if( !sqlsrv_query($_SESSION['conn'], $atu, $params)) {
                $commitar = false;  
                break;
              };
            };
            if($commitar) {
              sqlsrv_commit($_SESSION['conn']);
              $this->retorno  = ["retorno"=>"OK","dados"=>"","erro"=>""];
            } else {
              $retorno  = 'ERR';
              $arr      = sqlsrv_errors();
              $int      = strpos($arr[0]["message"],"[SQL Server]");
              $str      = trim(substr($arr[0]["message"],($int+12),strlen($arr[0]["message"])));
              $this->retorno=["retorno"=>"ERR","dados"=>"","erro"=> substr($str,0,300)];

              sqlsrv_rollback( $_SESSION['conn'] );
              sqlsrv_close( $_SESSION['conn'] );
            }; 
          }
        } catch(Exception $e ){
            $e = substr($e,0,strpos($e,' in C:'));
            $erro=str_replace("\\","/",$e);
            $erro=str_replace(["Exception:","exception 1","exception","Exception","with message","\r","\n","{","}","\\",'"',"'"],"",utf8_decode($erro));
            $this->retorno=["retorno"=>"ERR","dados"=>"","erro"=> substr($erro,0,300)];
            @ibase_rollback($this->gdb);            
        }
        return $this->retorno;        
      };        
    }  
    function setLogin($login){ 
      $this->login=$login; 
    }
    function verClasse(){
      echo '<pre>';
      print_r($this);
      echo '</pre>';      
    }    
  }
?>