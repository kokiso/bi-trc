<?php
  class consultaCep{
    var $uf;
    var $cidade;
    var $bairro;
    var $logradouro;
    
    function buscaCep($cep){
      $cep = preg_replace('/[^0-9]/', '', (string) $cep);
      $url = "http://viacep.com.br/ws/".$cep."/json/";
file_put_contents("bbb.xml",$url);      			
      // CURL
      $ch = curl_init();
      // Desabilita SSL verificação
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      // Retornará a resposta, se falso imprimir a resposta
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      // Seta a url
      curl_setopt($ch, CURLOPT_URL, $url);
      // Executa
      $result = curl_exec($ch);
      // Fechando
      curl_close($ch);

      $retorno  = "";
      $json=json_decode($result);
      if(!isset($json->erro)){
        $uf     = trim(strtoupper($json->uf));
        //
        $cidade = strtolower($json->localidade);
        $cidade = iconv("UTF-8","ASCII//TRANSLIT",$cidade);
        $cidade = preg_replace("/[~^\'`^]/",null,$cidade);
        $cidade = strtoupper($cidade);
        //
        $bairro = strtolower($json->bairro);
        $bairro = iconv("UTF-8","ASCII//TRANSLIT",$bairro);
        $bairro = preg_replace("/[~^\'`^]/",null,$bairro);
        $bairro = strtoupper($bairro);
        //
        $ibge   = $json->ibge;
        //
        $endereco = strtoupper($json->logradouro);
        $endereco = iconv("UTF-8","ASCII//TRANSLIT",$endereco);
        $endereco = preg_replace("/[~^\'`^]/",null,$endereco);
				$endereco = strtoupper($endereco);
				
        $expld    = explode(" ",$endereco);
        
        $codtl    = trim(strtoupper($expld[0]));
        $endereco ="";
        for($lin=0;$lin<count($expld);$lin++){
          if($lin>0){
            $endereco.=($endereco=="" ? "" : " ").$expld[$lin];
          };
        };
				
        /////////////////////////////////////////////////////////////////////
        // Ajustando o logradouro conforme alguns cadastros basicos do ERP //
        /////////////////////////////////////////////////////////////////////
        switch( $codtl ){
          case "ALA"      : $codtl="AL";  break;
          case "ALAM"     : $codtl="AL";  break;
          case "AVE"      : $codtl="AV";  break;
          case "AVENIDA"  : $codtl="AV";  break;
          case "COM"      : $codtl="RUA"; break;
          case "PCA"      : $codtl="PCA"; break;
          case "PÇA"      : $codtl="PCA"; break;
          case "PC"       : $codtl="PCA"; break;
          case "PRA"      : $codtl="PCA"; break;
          case "Q"        : $codtl="QD";  break;
          case "QUA"      : $codtl="QD";  break;
          case "R"        : $codtl="RUA"; break;
          case "TR"       : $codtl="TV";  break;
          case "TRA"      : $codtl="TV";  break;
          case "TV"       : $codtl="TV";  break;
          case "VIL"      : $codtl="VL";  break;
          case "VILA"     : $codtl="VL";  break;
        };
        ///////////////////////////////
        // retornando ao JS um array //
        ///////////////////////////////
        $arrRet=[];
        array_push($arrRet,[
          "uf"        =>  $uf
          ,"cidade"   =>  $cidade
          ,"bairro"   =>  $bairro
          ,"codtl"    =>  $codtl
          ,"endereco" =>  $endereco
          ,"codcdd"   =>  $ibge
        ]);
        
        $retorno='[{"retorno":"OK"
                   ,"dados": '.json_encode($arrRet).'
                   ,"erro":""}]'; 
      } else {
        $retorno='[{"retorno":"ERR","dados":"","erro":"'.$json->erro.'"}]';
      }
      return $retorno;
    }
  }
?>