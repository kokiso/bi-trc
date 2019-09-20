<?php
  class removeAcento{
    var $nome;
    function __construct(){
      $this->caixaAlta = true;  
    }  
    function montaRetorno($nome){
      if( $this->caixaAlta ){
        $nome = strtoupper($nome);
      }  
      //////////////////////////
      // Removendo os acentos //
      //////////////////////////
      $remove = iconv("UTF-8","ASCII//TRANSLIT",$nome);
      $nome   = strtoupper(preg_replace("/[~^\'`^]/",null,$remove));
      /////////////////////////////////////////////////////////////////////////
      // Dobrado strtoupper pois acentos em caixa baixa ficam em caixa baixa //
      /////////////////////////////////////////////////////////////////////////
      if( $this->caixaAlta ){
        $nome = trim(strtoupper($nome));
      }  
      $this->setNome($nome);      
    }
    function opcCaixaAlta($bool){
      $this->caixaAlta=$bool;
    }      
    //////////////////////////////////////////////////////
    // Responsavel por receber e checar os valores      //
    // Cada set pode ser tradado para a chamada do get  //
    //////////////////////////////////////////////////////
    function setNome($nome){ 
      $this->nome=$nome; 
    }
    /////////////////////////////////////////
    // Responsavel por retornar os valores //
    /////////////////////////////////////////
    function getNome(){
      return $this->nome;
    }    
  };
?>