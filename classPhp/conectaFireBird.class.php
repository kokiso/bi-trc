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
           ["login"=>"PIER"           , "path"=>"52.21.127.240:d:/pastabis/VTEX2.FDB"               , "cnpj"=>"08181938000167","user"=>"SYSDBA","pass"=>"21106400"]
          ,["login"=>"PIER2"          , "path"=>"localhost:c:/meus_doctos/bd/piervtex2.gdb"         , "cnpj"=>"08181938000167","user"=>"SYSDBA","pass"=>"21106400"]          
          ,["login"=>"PIER3"          , "path"=>"localhost:c:/meus_doctos/bd/piervtex3.gdb"         , "cnpj"=>"08181938000167","user"=>"SYSDBA","pass"=>"21106400"]          
          ,["login"=>"PIER4"          , "path"=>"localhost:c:/meus_doctos/bd/piervtex4.gdb"         , "cnpj"=>"08181938000167","user"=>"SYSDBA","pass"=>"21106400"]          
        ))    
      );    
    }
    //--
    function conecta($login){
      $path           = "";
      $user           = "";
      $pass           = "";
      $retorno        = "";
      $this->retorno  = ["retorno"=>"OK","dados"=>"","erro"=>""];  
      $this->login    = $login;
      $this->msgErro="NENHUM REGISTRO LOCALIZADO PARA ESTA OPÇÃO! ".$login;
      foreach($this->vetor[0]["CONECTA"] as $cnct){
        if( $cnct["login"]==$login):
          $path=$cnct["path"];
          $user=$cnct["user"];
          $pass=$cnct["pass"];
          ////////////////////////////////////////////////////////////////////////
          // Devido multi-banco, a cada novo login unset em $_SESSION['pathBD'] //
          ////////////////////////////////////////////////////////////////////////
          if( !isset($_SESSION['pathBD']) ){ 
            $_SESSION['pathBD'] = $path;
            $_SESSION['userBD'] = $user;
            $_SESSION['passBD'] = $pass;		
          }  
          break;
        endif;
      };
      if( $path=="" ):
        $this->retorno=["retorno"=>"ERR","dados"=>"","erro"=>"LOGIN ".$login." NAO LOCALIZADO!"];  
      else:
        $this->gdb = @ibase_connect($path, $user, $pass);
        if( $this->gdb === false )
          $this->retorno=["retorno"=>"ERR","dados"=>"","erro"=>"ERRO AO CONECTAR ".ibase_errmsg()];  
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
          $reg    = array();
          $dados  = @ibase_query($this->gdb,$select);
          if( $dados===false )
            throw new Exception("ERRO SELECT ".ibase_errmsg());
          $qtosReg=0;
          while ($linha = ibase_fetch_assoc($dados)) {
            $reg[]=$linha;
            $qtosReg++;
          };
          ibase_free_result($dados);
          if( $qtosReg>0 )
            $this->retorno=["retorno"=>"OK","dados"=>$reg,"erro"=>""];  
          else{
            //////////////////////////////////////////////////////////////////////////////////////////
            // Alguns selects podem retornar vazio e não precisam gerar a mensagem de erro, ex:SPED //
            //////////////////////////////////////////////////////////////////////////////////////////
            if( $this->msgSelectVazio )
              $this->retorno=["retorno"=>"ERR","dados"=>"","erro"=>$this->msgErro];
            else            
              $this->retorno=["retorno"=>"OK","dados"=>[],"erro"=>""];    
          }  
        } catch (Exception $e){
          $this->retorno=["retorno"=>"ERR","dados"=>"","erro"=> $this->login." ".substr(str_replace(["\r","\n","{","}","\\",'"'],"",utf8_decode($e)),0,120)];  
        }  
      }  
      return $this->retorno;
    }  
    ////////////////////////////
    // SELECT NAO ASSOCIATIVO //
    ////////////////////////////
    function select($select){
      if( $this->retorno['retorno'] != "ERR" ){
        try{
          $reg    = array();
          $dados  = @ibase_query($this->gdb,$select);
          if( $dados===false )
            throw new Exception("ERRO SELECT ".ibase_errmsg());
          $qtosReg=0;
          while ($linha = ibase_fetch_row($dados)) {
            $registro=array();
            foreach($linha as $campo)
              $registro[]=utf8_encode($campo);
            $reg[]=$registro;
            $qtosReg++;
          }    
          ibase_free_result($dados);
          if( $qtosReg>0 )          
            $this->retorno=["retorno"=>"OK","dados"=>$reg,"erro"=>""];  
          else{
            //////////////////////////////////////////////////////////////////////////////////////////
            // Alguns selects podem retornar vazio e não precisam gerar a mensagem de erro, ex:SPED //
            //////////////////////////////////////////////////////////////////////////////////////////
            if( $this->msgSelectVazio )
              $this->retorno=["retorno"=>"ERR","dados"=>"","erro"=>$this->msgErro];            
            else            
              $this->retorno=["retorno"=>"OK","dados"=>[],"erro"=>""];    
          }  
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
      if( $this->retorno['retorno'] != "ERR" ){      
        try{
          if( count($atualiza)==0 )
            throw new Exception('NENHUMA INSTRUCAO SQL PARA SER EXECUTADA');          
          
          $this->tr=ibase_trans($this->gdb);
          foreach( $atualiza as $atu ){
            $fezSql=@ibase_query($this->tr,$atu);
            if( $fezSql===false )
              throw new Exception(ibase_errmsg());
          }
          @ibase_commit($this->tr); 
          $this->retorno  = ["retorno"=>"OK","dados"=>"","erro"=>""];            
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