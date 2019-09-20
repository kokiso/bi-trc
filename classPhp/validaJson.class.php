<?php
  class validaJson{
    var $retorno="";
    function validarJs($arquivo){
      $str  = "";
      $js   = json_decode($arquivo);
      switch (json_last_error()){
        case JSON_ERROR_NONE:
        break;
        case JSON_ERROR_DEPTH:
          $str='Máxima qtdade de pilha excedida';
          break;
        case JSON_ERROR_STATE_MISMATCH:
          $str='Subfluxo ou a incompatibilidade de modos';
          break;
        case JSON_ERROR_CTRL_CHAR:
          $str='Caracter indefinido encontrado';
          break;
        case JSON_ERROR_SYNTAX:
          $str='JSON com erro de sintaxe';
          break;
        case JSON_ERROR_UTF8:
          $str='Caracter UTF-8 não reconhecido';
          break;
        default:
          $str='Erro desconhecido';
          break;
      }; 
      if(json_last_error() != JSON_ERROR_NONE)
        $this->retorno=["retorno"=>"ERR","dados"=>"","erro"=>$str];
      else
        $this->retorno=["retorno"=>"OK","dados"=>$js,"erro"=>""];         
      return $this->retorno;
    }  
  }
?>