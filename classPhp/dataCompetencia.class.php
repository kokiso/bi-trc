<?php
  class dataCompetencia{
    /*
    * Chamando a classe
    *   $classe = new dataCompetencia();
    *   $classe->montaRetorno("28/03/1966","dd/mm/yyyy","mm/dd/yyyy");
    *   $apelido    = $classe->getData();
    */
    /*
    * Criando os atributos da classe
    * Estas podem se acessadas pelo criador da classe obj->classe="JOSE";
    */
    //var $data;
    var $dia;
    var $mes;
    var $ano;
    
    function montaRetorno($data,$formato){
      /*
      * Formato da data recebida
      */
      switch ($formato){
        case "dd/mm/yyyy":
          $local=explode('/',$data);
          $dia=$local[0];
          $mes=$local[1];
          $ano=$local[2];
          break;                    

        case "mm/dd/yyyy":          
          $local=explode('/',$data);
          $mes=$local[0];          
          $dia=$local[1];
          $ano=$local[2];
          break;                    
          
        case "yyyy-mm-dd":
          $local=explode('-',$data);
          $ano=$local[0];          
          $mes=$local[1];          
          $dia=$local[2];
          break;                    
          
        case "ddmmyyyy":
          $dia=substr($data,0,2);
          $mes=substr($data,2,2);
          $ano=substr($data,4,4);
          break;                    
          
        case "yyyymm":
          $dia='01';
          $mes=substr($data,4,2);
          $ano=substr($data,0,4);
          break;                    
          
      };
      /*
      * Gravando no campo
      */
      $this->setDia($dia);
      $this->setMes($mes);
      $this->setAno($ano);
    }
    /* 
    * Responsavel por receber e checar os valores 
    * Cada set pode ser tradado para a chamada do get 
    */
    function setDia($dia){ $this->dia=$dia; }    
    function setMes($mes){ $this->mes=$mes; }    
    function setAno($ano){ $this->ano=$ano; }    
    /* 
    * Responsavel por retornar os valores 
    */
    function getData($formato){ 
      switch ($formato){
        case "ddmmyyyy":
          $local=$this->dia.$this->mes.$this->ano;
          break;                    
        case "dd/mm/yyyy":
          $local=$this->dia.'/'.$this->mes.'/'.$this->ano;
          break;                    
        case "mm/dd/yyyy":
          $local=$this->mes.'/'.$this->dia.'/'.$this->ano;
          break;                    
        case "yyyy-mm-dd":
          $local=$this->ano.'-'.$this->mes.'-'.$this->dia;
          break;                    
        case "yyyymm":
          $local=$this->ano.$this->mes;
          break;                    
        case "mmm/yy":
          $local=
            (($this->mes == '01') ? 'JAN/' : 
            (($this->mes == '02') ? 'FEV/' : 
            (($this->mes == '03') ? 'MAR/' :       
            (($this->mes == '04') ? 'ABR/' :       
            (($this->mes == '05') ? 'MAI/' :       
            (($this->mes == '06') ? 'JUN/' : 
            (($this->mes == '07') ? 'JUL/' :         
            (($this->mes == '08') ? 'AGO/' :       
            (($this->mes == '09') ? 'SET/' :       
            (($this->mes == '10') ? 'OUT/' :       
            (($this->mes == '11') ? 'NOV/' : 
            (($this->mes == '12') ? 'DEZ/' :  '*' ))))))))))) ).substr($this->ano,2,4);
          break;
          
        case "dd":
          $local=$this->dia;
          break;                    
      };
      return $local; 
    }
    function getDia(){
      return $this->dia;
    }

    
    function verClasse(){
      echo '<pre>';
      print_r($this);
      echo '</pre>';      
    }    
  };
?>