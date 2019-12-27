<?php
  session_start();
  if( isset($_POST["usuarioperfil"]) ){
    try{     
      require("classPhp/conectaSqlServer.class.php");
      require("classPhp/validaJson.class.php"); 
      require("classPhp/removeAcento.class.php"); 

      $vldr     = new validaJSon();          
      $retorno  = "";
      $retCls   = $vldr->validarJs($_POST["usuarioperfil"]);
      ///////////////////////////////////////////////////////////////////////
      // Variavel mostra que não foi feito apenas selects mas atualizou BD //
      ///////////////////////////////////////////////////////////////////////
      $atuBd    = false;
      if($retCls["retorno"] != "OK"){
        $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';
        unset($retCls,$vldr);      
      } else {
        $strExcel = "*"; 
        $arrUpdt  = []; 
        $jsonObj  = $retCls["dados"];
        $lote     = $jsonObj->lote;
        $rotina   = $lote[0]->rotina;
        $classe   = new conectaBd();
        $classe->conecta($lote[0]->login);
        //////////////////////////////////////
        //    Dados para JavaScript PERFIL  //
        //////////////////////////////////////
        if( $rotina=="selectUp" ){
        $sql = "SELECT A.UP_CODIGO
                       ,A.UP_NOME
                       ,A.UP_GRUPO
                       ,COALESCE(G.GRP_NOME, 'SEM GRUPO') AS GRP_NOME
                       ,A.UP_D01
                       ,A.UP_D02
                       ,A.UP_D03
                       ,A.UP_D04
                       ,A.UP_D05
                       ,A.UP_D06
                       ,A.UP_D07
                       ,A.UP_D08
                       ,A.UP_D09
                       ,A.UP_D10
                       ,A.UP_D11
                       ,A.UP_D12
                       ,A.UP_D13
                       ,A.UP_D14
                       ,A.UP_D15
                       ,A.UP_D16
                       ,A.UP_D17
                       ,A.UP_D18
                       ,A.UP_D19
                       ,A.UP_D20
                       ,CASE WHEN A.UP_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS UP_ATIVO
                       ,CASE WHEN A.UP_REG='P' THEN 'PUB' WHEN A.UP_REG='S' THEN 'SIS' ELSE 'ADM' END AS UP_REG
                       ,U.US_APELIDO
                       ,A.UP_CODUSR";
        $sql .= ",CASE WHEN A.CONSULTAR_RELATORIO='S' THEN 'PERMITIDO' ELSE 'NAO PERMITIDO' END AS CONSULTAR_USUARIO
                 ,CASE WHEN A.GRUPO_OPERACIONAL='S' THEN 'PERMITIDO' ELSE 'NAO PERMITIDO' END AS CONSULTAR_USUARIO
                  FROM USUARIOPERFIL A
                  INNER JOIN USUARIOSISTEMA U ON A.UP_CODUSR=U.US_CODIGO";
        $sql .= " LEFT OUTER JOIN GRUPO G ON A.UP_GRUPO=G.GRP_CODIGO";
        $sql .= " WHERE ((A.UP_ATIVO='" . $lote[0]->ativo . "') OR ('*'='" . $lote[0]->ativo . "')) ";
        if ($_SESSION['usr_grupoPerfil'] != "0") {
          $sql .= " AND A.UP_GRUPO =" . $_SESSION['usr_grupoPerfil'];
        }
        $classe->msgSelect(false);
        $retCls = $classe->select($sql);
        if ($retCls['retorno'] != "OK") {
          $retorno = '[{"retorno":"ERR","dados":"","erro":"' . $retCls['erro'] . '"}]';
        } else {
          $retorno = '[{"retorno":"OK","dados":' . json_encode($retCls['dados']) . ',"erro":""}]';
        };
      };
        ////////////////////////////////////
        // Dados para JavaScript          //
        ////////////////////////////////////
        if( $rotina=="impExcel" ){
          $strExcel   = "S";                                                //Se S mostra na grade e importa, se N só mostra na grade    
          $dom        = DOMDocument::load($_FILES["arquivo"]["tmp_name"]);  //Abre o arquivo completo
          $rows       = $dom->getElementsByTagName("Row");                  //Retorna um array de todas as linhas

          $tamR     = $rows->length; // tamanho do array rows
          $data     = [];        
          $arrUpdt  = []; 
          $arrDup   = []; //Verifica se existe duplicidade no arquivo recebido
          $clsRa    = new removeAcento();
          
          for ($linR = 0; $linR < $tamR; $linR ++){
            $cells = $rows->item($linR)->getElementsByTagName("Cell");
            
            if( $linR==0 ){
              $tamC=$cells->length;
              $cabec="";
              
              foreach($cells as $cell){
                $cabec.=($cabec=="" ? "" : "|").strtoupper( $cell->nodeValue );
              };
              
              if( $cabec != $lote[0]->cabec ){
                array_push($data,["CODIGO","DESCRICAO","LINHA 1 DEVE SER ".$lote[0]->cabec]);
                $strExcel   = "N";
              };
            } else {
              $erro="OK";
              
              $linC = -1;
              foreach($cells as $cell){
                $linC++;
                switch( $linC ){
                  case 0:                  
                    $codigo=preg_replace('/[^0-9]/', '', trim($cell->nodeValue));
                    if( strlen(trim($codigo)) == 0 ){ 
                      $erro     = "CAMPO CODIGO DEVE TER UM INTEIRO VALIDO";
                      $strExcel = "N";
                    } else {
                      $codigo=str_pad($codigo, 4, "0", STR_PAD_LEFT);
                    };  
                    break;
                  case 1: 
                    $clsRa->montaRetorno($cell->nodeValue);
                    $descricao=$clsRa->getNome();
                    if( (strlen($descricao)<1) or (strlen($descricao)>15) ){ 
                      $erro     = "CAMPO DESCRICAO DEVE TER TAMANHO 01..15";
                      $strExcel = "N";
                    };
                    $achei=false;
                    foreach( $arrDup as $lin ){
                      if( $lin["DESCRICAO"]==$descricao ){
                        $erro     = "DESCRITIVO DUPLICADO NA PLANILHA";
                        $strExcel = "N";
                        $achei    = true;
                        break;
                      };
                    };
                    if( $achei==false ){
                      array_push($arrDup,[
                        "DESCRICAO"=>$descricao
                      ]);
                    };    
                    break;
                  case 2:                  
                    $d01=preg_replace('/[^0-9]/', '', trim($cell->nodeValue));
                    if( ($d01 < 0) or ($d01 > 4)  ){ 
                      $erro     = "CAMPO D01 INTEIRO VALIDO ENTRE 0..4";
                      $strExcel = "N";
                    };  
                    break;
                  case 3:                  
                    $d02=preg_replace('/[^0-9]/', '', trim($cell->nodeValue));
                    if( ($d02 < 0) or ($d02 > 4)  ){ 
                      $erro     = "CAMPO D02 INTEIRO VALIDO ENTRE 0..4";
                      $strExcel = "N";
                    };  
                    break;
                    
                  case 4:                  
                    $d03=preg_replace('/[^0-9]/', '', trim($cell->nodeValue));
                    if( ($d03 < 0) or ($d03 > 4)  ){ 
                      $erro     = "CAMPO D03 INTEIRO VALIDO ENTRE 0..4";
                      $strExcel = "N";
                    };  
                    break;
                  case 5:                  
                    $d04=preg_replace('/[^0-9]/', '', trim($cell->nodeValue));
                    if( ($d04 < 0) or ($d04 > 4)  ){ 
                      $erro     = "CAMPO D04 INTEIRO VALIDO ENTRE 0..4";
                      $strExcel = "N";
                    };  
                    break;
                  case 6:                  
                    $d05=preg_replace('/[^0-9]/', '', trim($cell->nodeValue));
                    if( ($d05 < 0) or ($d05 > 4)  ){ 
                      $erro     = "CAMPO D05 INTEIRO VALIDO ENTRE 0..4";
                      $strExcel = "N";
                    };  
                    break;
                  case 7:                  
                    $d06=preg_replace('/[^0-9]/', '', trim($cell->nodeValue));
                    if( ($d06 < 0) or ($d06 > 4)  ){ 
                      $erro     = "CAMPO D06 INTEIRO VALIDO ENTRE 0..4";
                      $strExcel = "N";
                    };  
                    break;
                    
                  case 8:                  
                    $d07=preg_replace('/[^0-9]/', '', trim($cell->nodeValue));
                    if( ($d07 < 0) or ($d07 > 4)  ){ 
                      $erro     = "CAMPO D07 INTEIRO VALIDO ENTRE 0..4";
                      $strExcel = "N";
                    };  
                    break;
                    
                  case 9:                  
                    $d08=preg_replace('/[^0-9]/', '', trim($cell->nodeValue));
                    if( ($d08 < 0) or ($d08 > 4)  ){ 
                      $erro     = "CAMPO D08 INTEIRO VALIDO ENTRE 0..4";
                      $strExcel = "N";
                    };  
                    break;
                    
                  case 10:                  
                    $d09=preg_replace('/[^0-9]/', '', trim($cell->nodeValue));
                    if( ($d09 < 0) or ($d09 > 4)  ){ 
                      $erro     = "CAMPO D09 INTEIRO VALIDO ENTRE 0..4";
                      $strExcel = "N";
                    };  
                    break;
                    
                  case 11:                  
                    $d10=preg_replace('/[^0-9]/', '', trim($cell->nodeValue));
                    if( ($d10 < 0) or ($d10 > 4)  ){ 
                      $erro     = "CAMPO D10 INTEIRO VALIDO ENTRE 0..4";
                      $strExcel = "N";
                    };  
                    break;
                  case 12:                  
                    $d11=preg_replace('/[^0-9]/', '', trim($cell->nodeValue));
                    if( ($d11 < 0) or ($d11 > 4)  ){ 
                      $erro     = "CAMPO D11 INTEIRO VALIDO ENTRE 0..4";
                      $strExcel = "N";
                    };  
                    break;
                    
                  case 13:                  
                    $d12=preg_replace('/[^0-9]/', '', trim($cell->nodeValue));
                    if( ($d12 < 0) or ($d12 > 4)  ){ 
                      $erro     = "CAMPO D12 INTEIRO VALIDO ENTRE 0..4";
                      $strExcel = "N";
                    };  
                    break;
                    
                  case 14:                  
                    $d13=preg_replace('/[^0-9]/', '', trim($cell->nodeValue));
                    if( ($d13 < 0) or ($d13 > 4)  ){ 
                      $erro     = "CAMPO D13 INTEIRO VALIDO ENTRE 0..4";
                      $strExcel = "N";
                    };  
                    break;
                    
                  case 15:                  
                    $d14=preg_replace('/[^0-9]/', '', trim($cell->nodeValue));
                    if( ($d14 < 0) or ($d14 > 4)  ){ 
                      $erro     = "CAMPO D14 INTEIRO VALIDO ENTRE 0..4";
                      $strExcel = "N";
                    };  
                    break;
                    
                  case 16:                  
                    $d15=preg_replace('/[^0-9]/', '', trim($cell->nodeValue));
                    if( ($d15 < 0) or ($d15 > 4)  ){ 
                      $erro     = "CAMPO D15 INTEIRO VALIDO ENTRE 0..4";
                      $strExcel = "N";
                    };  
                    break;
                    
                  case 17:                  
                    $d16=preg_replace('/[^0-9]/', '', trim($cell->nodeValue));
                    if( ($d16 < 0) or ($d16 > 4)  ){ 
                      $erro     = "CAMPO D16 INTEIRO VALIDO ENTRE 0..4";
                      $strExcel = "N";
                    };  
                    break;
                    
                  case 18:                  
                    $d17=preg_replace('/[^0-9]/', '', trim($cell->nodeValue));
                    if( ($d17 < 0) or ($d17 > 4)  ){ 
                      $erro     = "CAMPO D17 INTEIRO VALIDO ENTRE 0..4";
                      $strExcel = "N";
                    };  
                    break;
                    
                  case 19:                  
                    $d18=preg_replace('/[^0-9]/', '', trim($cell->nodeValue));
                    if( ($d18 < 0) or ($d18 > 4)  ){ 
                      $erro     = "CAMPO D18 INTEIRO VALIDO ENTRE 0..4";
                      $strExcel = "N";
                    };  
                    break;
                    
                  case 20:                  
                    $d19=preg_replace('/[^0-9]/', '', trim($cell->nodeValue));
                    if( ($d19 < 0) or ($d19 > 4)  ){ 
                      $erro     = "CAMPO D19 INTEIRO VALIDO ENTRE 0..4";
                      $strExcel = "N";
                    };  
                    break;
                    
                  case 21:                  
                    $d20=preg_replace('/[^0-9]/', '', trim($cell->nodeValue));
                    if( ($d20 < 0) or ($d20 > 4)  ){ 
                      $erro     = "CAMPO D20 INTEIRO VALIDO ENTRE 0..4";
                      $strExcel = "N";
                    };  
                    break;
                };
              };
              ////////////////////////////////////////////
              // Montando o vetor para retornar ao JSON //
              ////////////////////////////////////////////
              array_push($data,[$codigo,$descricao,$d01,$d02,$d03,$d04,$d05,$d06,$d07,$d08,$d09,$d10,$erro]);
              /////////////////////////////////////////////////////
              // Guardando os inserts se não existir nenhum erro //
              /////////////////////////////////////////////////////
              $sql="INSERT INTO USUARIOPERFIL("          
                ."UP_NOME"
                .",UP_D01"
                .",UP_D02"
                .",UP_D03"
                .",UP_D04"
                .",UP_D05"
                .",UP_D06"
                .",UP_D07"
                .",UP_D08"
                .",UP_D09"
                .",UP_D10"
                .",UP_D11"
                .",UP_D12"
                .",UP_D13"
                .",UP_D14"
                .",UP_D15"
                .",UP_D16"
                .",UP_D17"
                .",UP_D18"
                .",UP_D19"
                .",UP_D20"
                .",UP_REG"
                .",UP_CODUSR"
                .",UP_ATIVO"
                .",GRUPO_OPERACIONAL"
                .",CONSULTAR_RELATORIO) VALUES("
                ."'".$descricao."'"             // UP_NOME
                .",".$d01                       // UP_D01
                .",".$d02                       // UP_D02
                .",".$d03                       // UP_D03
                .",".$d04                       // UP_D04
                .",".$d05                       // UP_D05
                .",".$d06                       // UP_D06
                .",".$d07                       // UP_D07
                .",".$d08                       // UP_D08
                .",".$d09                       // UP_D09
                .",".$d10                       // UP_D10
                .",".$d11                       // UP_D11
                .",".$d12                       // UP_D12
                .",".$d13                       // UP_D13
                .",".$d14                       // UP_D14
                .",".$d15                       // UP_D15
                .",".$d16                       // UP_D16
                .",".$d17                       // UP_D17
                .",".$d18                       // UP_D18
                .",".$d19                       // UP_D19
                .",".$d20                       // UP_D20
                .",'P'"                         // UP_REG
                .",".$_SESSION["usr_codigo"]    // UP_CODUSR
                .",'S'"                         // UP_ATIVO
                .",'".$GRUPO_OPERACIONAL."'"    // GRUPO_OPERACIONAL
                .",'".$CONSULTAR_RELATORIO."'"  // CONSULTAR_RELATORIO
              .")";
              array_push($arrUpdt,$sql);
            };
          };
        };
        ///////////////////////////////////////////////////
        // Passou pela rotina de importação mas deu erro //
        ///////////////////////////////////////////////////
        if( $strExcel== "N"){
          $retorno='[{"retorno":"OK","dados":'.json_encode($data).',"erro":"ERRO(s) ENCONTRADOS"}]';   
        } else {
          ////////////////////////////////////////////////////////////////////
          // Se strExcel="S" eh pq tem rotina de importacao e nao teve erro //
          ////////////////////////////////////////////////////////////////////
          if( $strExcel== "S"){
            $atuBd=true;
          };  
          ///////////////////////////////////////////////////////////////////
          // Atualizando o banco de dados se opcao de insert/updade/delete //
          ///////////////////////////////////////////////////////////////////
          if( $atuBd ){
            if( count($arrUpdt) >0 ){
              $retCls=$classe->cmd($arrUpdt);
              if( $retCls['retorno']=="OK" ){
                $retorno='[{"retorno":"OK","dados":'.json_encode($data).',"erro":"'.count($arrUpdt).' REGISTRO(s) ATUALIZADO(s)!"}]'; 
              } else {
                $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';  
              };  
            } else {
              $retorno='[{"retorno":"OK","dados":"","erro":"NENHUM REGISTRO CADASTRADO!"}]';
            };  
          };
        };
      };
    } catch(Exception $e ){
      $retorno='[{"retorno":"ERR","dados":"","erro":"'.$e.'"}]'; 
    };    
    echo $retorno;
    exit;
  };  
?>
<!DOCTYPE html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="SKYPE_TOOLBAR" content="SKYPE_TOOLBAR_PARSER_COMPATIBLE" />
    <title>Perfil</title>
    <link rel="stylesheet" href="css/css2017.css">
    <link rel="stylesheet" href="css/cssTable2017.css">
    <link rel="stylesheet" href="css/cssFaTable.css">
    <script src="js/js2017.js"></script>
    <script src="js/jsTable2017.js"></script>
    <script src="tabelaTrac/f10/tabelaGrupoF10.js"></script>
    <script language="javascript" type="text/javascript"></script>
    <script>
      "use strict";
      ////////////////////////////////////////////////
      // Executar o codigo após a pagina carregada  //
      ////////////////////////////////////////////////
      document.addEventListener("DOMContentLoaded", function(){ 
        ////////////////////////////////////
        //   Objeto clsTable2017 PERFIL   //
        ////////////////////////////////////
        jsUp={
          "titulo":[
             {"id":0  ,"labelCol":"OPC"     
                      ,"padrao":1}            
            ,{"id":1  ,"field"          :"UP_CODIGO" 
                      ,"labelCol"       : "CODIGO"
                      ,"obj"            : "edtCodigo"
                      ,"tamGrd"         : "5em"
                      ,"tamImp"         : "0"
                      ,"fieldType"      : "int"
                      ,"align"          : "center"                                      
                      ,"pk"             : "S"
                      ,"newRecord"      : ["0000","this","this"]
                      ,"insUpDel"       : ["N","N","N"]
                      ,"formato"        : ["i4"]
                      ,"validar"        : ["notnull","intMaiorZero"]
                      ,"autoIncremento" : "S"
                      ,"ajudaCampo"     : [  "Codigo do perfil. Gerado pelo sistema é único e tem o formato 9999"
                                            ,"Campo deve ser utilizado no cadastro de Usuário"]
                      ,"importaExcel"   : "S"                                                                
                      ,"padrao":0}
            ,{"id":2  ,"field"          : "UP_NOME"   
                      ,"labelCol"       : "DESCRICAO"
                      ,"obj"            : "edtDescricao"
                      ,"tamGrd"         : "15em"
                      ,"tamImp"         : "30"
                      ,"digitosMinMax"  : [3,40]
                      ,"ajudaCampo"     : ["Nome do perfil."]
                      ,"importaExcel"   : "S"                                          
                      ,"padrao":0}
            ,{"id":3  ,"field"          : "UP_GRUPO"   
                      ,"labelCol"       : "CODGRP"
                      ,"labelColImp"    : "GRP"
                      ,"obj"            : "edtPerfilGrupo"
                      ,"fieldType"      : "int"              
                      ,"formato"        : ["i4"]
                      ,"tamGrd"         : "0em"
                      ,"tamImp"         : "10"
                      ,"ajudaCampo"     : ["Codigo do grupo."]
                      ,"importaExcel"   : "S"                                          
                      ,"padrao":0}
            ,{"id":4  ,"field"          : "GRP_NOME"   
                      ,"insUpDel"       : ["N","N","N"]
                      ,"labelCol"       : "GRUPO"
                      ,"obj"            : "edtDesPerfilGrupo"
                      ,"tamGrd"         : "15em"
                      ,"tamImp"         : "30"
                      ,"digitosMinMax"  : [3,40]
                      ,"ajudaCampo"     : ["Nome do grupo."]
                      ,"importaExcel"   : "S"                                          
                      ,"padrao":0}
            ,{"id":5  ,"field"          : "UP_D01"      
                      ,"labelCol"       : "D01"
                      ,"labelColImp"    : "01"
                      ,"obj"            : "cbD01"
                      ,"tipo"           : "cb"
                      ,"tamGrd"         : "0em"
                      ,"tamImp"         : "5"
                      ,"fieldType"      : "int"
                      ,"copyGRD"        : [0,1]
                      ,"newRecord"      : ["0","this","this"]
                      ,"validar"        : ["notnull","intMaiorIgualZero"]
                      ,"digitosMinMax"  : [1,1]
                      ,"ajudaCampo"     : [  "Direito para opções"
                                            ,"Usuarios ativos","Usuario->Unidade","Perfil","Cargo"]
                      ,"importaExcel"   : "S"                                                                
                      ,"padrao":0}                                                  
            ,{"id":6  ,"field"          : "UP_D02"      
                      ,"labelCol"       : "D02"
                      ,"labelColImp"    : "02"
                      ,"obj"            : "cbD02"
                      ,"tipo"           : "cb"                      
                      ,"tamGrd"         : "0em"
                      ,"tamImp"         : "5"                      
                      ,"fieldType"      : "int"
                      ,"copyGRD"        : [0,1]
                      ,"newRecord"      : ["0","this","this"]
                      ,"validar"        : ["notnull","intMaiorIgualZero"]
                      ,"digitosMinMax"  : [1,1]
                      ,"ajudaCampo"     : [  "Direito para opçâo parametro integracao"]
                      ,"importaExcel"   : "S"                                                                
                      ,"padrao":0}                                                  
            ,{"id":7  ,"field"          : "UP_D03"      
                      ,"labelCol"       : "D03"
                      ,"labelColImp"    : "03"
                      ,"obj"            : "cbD03"
                      ,"tipo"           : "cb"                      
                      ,"tamGrd"         : "0em"
                      ,"tamImp"         : "5"                      
                      ,"fieldType"      : "int"
                      ,"copyGRD"        : [0,1]
                      ,"newRecord"      : ["0","this","this"]
                      ,"validar"        : ["notnull","intMaiorIgualZero"]
                      ,"digitosMinMax"  : [1,1]
                      ,"ajudaCampo"     : [  "Direito para integrar"]
                      ,"importaExcel"   : "S"                                                                
                      ,"padrao":0}                                                  
            ,{"id":8  ,"field"          : "UP_D04"      
                      ,"labelCol"       : "D04"
                      ,"labelColImp"    : "04"
                      ,"obj"            : "cbD04"
                      ,"tipo"           : "cb"                      
                      ,"tamGrd"         : "0em"
                      ,"tamImp"         : "5"                      
                      ,"fieldType"      : "int"
                      ,"copyGRD"        : [0,1]
                      ,"newRecord"      : ["0","this","this"]
                      ,"validar"        : ["notnull","intMaiorIgualZero"]
                      ,"digitosMinMax"  : [1,1]
                      ,"ajudaCampo"     : [  "..."]
                      ,"importaExcel"   : "S"                                                                
                      ,"padrao":0}                                                  
            ,{"id":9  ,"field"          : "UP_D05"  ,"labelCol":"D05" ,"labelColImp":"05" ,"obj":"cbD05"
                      ,"tipo"           : "cb"                      
                      ,"tamGrd"         : "0em"
                      ,"tamImp"         : "5"                      
                      ,"fieldType"      : "int"
                      ,"copyGRD"        : [0,1]
                      ,"newRecord"      : ["0","this","this"]
                      ,"validar"        : ["notnull","intMaiorIgualZero"]
                      ,"digitosMinMax"  : [1,1]
                      ,"ajudaCampo"     : [ "Direito para opções..."]
                      ,"importaExcel"   : "S"                                                                
                      ,"padrao":0}  
            ,{"id":10  ,"field"          : "UP_D06"  ,"labelCol":"D06" ,"labelColImp":"06" ,"obj":"cbD06"
                      ,"tipo"           : "cb"                      
                      ,"tamGrd"         : "0em"
                      ,"tamImp"         : "5"                      
                      ,"fieldType"      : "int"
                      ,"copyGRD"        : [0,1]
                      ,"newRecord"      : ["0","this","this"]
                      ,"validar"        : ["notnull","intMaiorIgualZero"]
                      ,"digitosMinMax"  : [1,1]
                      ,"ajudaCampo"     : [ "Direito para opções..."]
                      ,"importaExcel"   : "S"                                                                
                      ,"padrao":0}                                                  
            ,{"id":11  ,"field"          : "UP_D07"  ,"labelCol":"D07" ,"labelColImp":"07" ,"obj":"cbD07"
                      ,"tipo"           : "cb"                      
                      ,"tamGrd"         : "0em"
                      ,"tamImp"         : "5"                      
                      ,"fieldType"      : "int"
                      ,"copyGRD"        : [0,1]
                      ,"newRecord"      : ["0","this","this"]
                      ,"validar"        : ["notnull","intMaiorIgualZero"]
                      ,"digitosMinMax"  : [1,1]
                      ,"ajudaCampo"     : [ "Direito para opções..."]
                      ,"importaExcel"   : "S"                                                                
                      ,"padrao":0}                                                  
            ,{"id":12 ,"field"          : "UP_D08"  ,"labelCol":"D08" ,"labelColImp":"08" ,"obj":"cbD08"
                      ,"tipo"           : "cb"                      
                      ,"tamGrd"         : "0em"
                      ,"tamImp"         : "5"                      
                      ,"fieldType"      : "int"
                      ,"copyGRD"        : [0,1]
                      ,"newRecord"      : ["0","this","this"]
                      ,"validar"        : ["notnull","intMaiorIgualZero"]
                      ,"digitosMinMax"  : [1,1]
                      ,"ajudaCampo"     : [ "Direito para opções..."]
                      ,"importaExcel"   : "S"                                                                
                      ,"padrao":0}                                                  
            ,{"id":13 ,"field"          : "UP_D09"  ,"labelCol":"D09" ,"labelColImp":"09" ,"obj":"cbD09"
                      ,"tipo"           : "cb"                      
                      ,"tamGrd"         : "0em"
                      ,"tamImp"         : "5"                      
                      ,"fieldType"      : "int"
                      ,"copyGRD"        : [0,1]
                      ,"newRecord"      : ["0","this","this"]
                      ,"validar"        : ["notnull","intMaiorIgualZero"]
                      ,"digitosMinMax"  : [1,1]
                      ,"ajudaCampo"     : [ "Direito para opções..."]
                      ,"importaExcel"   : "S"                                                                
                      ,"padrao":0}                                                  
            ,{"id":14 ,"field"          : "UP_D10"  ,"labelCol":"D10" ,"labelColImp":"10" ,"obj":"cbD10"
                      ,"tipo"           : "cb"                      
                      ,"tamGrd"         : "0em"
                      ,"tamImp"         : "5"                      
                      ,"fieldType"      : "int"
                      ,"copyGRD"        : [0,1]
                      ,"newRecord"      : ["0","this","this"]
                      ,"validar"        : ["notnull","intMaiorIgualZero"]
                      ,"digitosMinMax"  : [1,1]
                      ,"ajudaCampo"     : [ "Direito para opções..."]
                      ,"importaExcel"   : "S"                                                                
                      ,"padrao":0}
            ,{"id":15 ,"field"          : "UP_D11"  ,"labelCol":"D11" ,"labelColImp":"11" ,"obj":"cbD11"
                      ,"tipo"           : "cb"                      
                      ,"tamGrd"         : "0em"
                      ,"tamImp"         : "5"                      
                      ,"fieldType"      : "int"
                      ,"copyGRD"        : [0,1]
                      ,"newRecord"      : ["0","this","this"]
                      ,"validar"        : ["notnull","intMaiorIgualZero"]
                      ,"digitosMinMax"  : [1,1]
                      ,"ajudaCampo"     : [ "Direito para opções..."]
                      ,"importaExcel"   : "S"                                                                
                      ,"padrao":0}
            ,{"id":16 ,"field"          : "UP_D12"  ,"labelCol":"D12" ,"labelColImp":"12" ,"obj":"cbD12"
                      ,"tipo"           : "cb"                      
                      ,"tamGrd"         : "0em"
                      ,"tamImp"         : "5"                      
                      ,"fieldType"      : "int"
                      ,"copyGRD"        : [0,1]
                      ,"newRecord"      : ["0","this","this"]
                      ,"validar"        : ["notnull","intMaiorIgualZero"]
                      ,"digitosMinMax"  : [1,1]
                      ,"ajudaCampo"     : [ "Direito para opções..."]
                      ,"importaExcel"   : "S"                                                                
                      ,"padrao":0}
            ,{"id":17 ,"field"          : "UP_D13"  ,"labelCol":"D13" ,"labelColImp":"13" ,"obj":"cbD13"
                      ,"tipo"           : "cb"                      
                      ,"tamGrd"         : "0em"
                      ,"tamImp"         : "5"                      
                      ,"fieldType"      : "int"
                      ,"copyGRD"        : [0,1]
                      ,"newRecord"      : ["0","this","this"]
                      ,"validar"        : ["notnull","intMaiorIgualZero"]
                      ,"digitosMinMax"  : [1,1]
                      ,"ajudaCampo"     : [ "Direito para opções..."]
                      ,"importaExcel"   : "S"                                                                
                      ,"padrao":0}
            ,{"id":18 ,"field"          : "UP_D14"  ,"labelCol":"D14" ,"labelColImp":"14" ,"obj":"cbD14"
                      ,"tipo"           : "cb"                      
                      ,"tamGrd"         : "0em"
                      ,"tamImp"         : "5"                      
                      ,"fieldType"      : "int"
                      ,"copyGRD"        : [0,1]
                      ,"newRecord"      : ["0","this","this"]
                      ,"validar"        : ["notnull","intMaiorIgualZero"]
                      ,"digitosMinMax"  : [1,1]
                      ,"ajudaCampo"     : [ "Direito para opções..."]
                      ,"importaExcel"   : "S"                                                                
                      ,"padrao":0}
            ,{"id":19 ,"field"          : "UP_D15"  ,"labelCol":"D15" ,"labelColImp":"15" ,"obj":"cbD15"
                      ,"tipo"           : "cb"                      
                      ,"tamGrd"         : "0em"
                      ,"tamImp"         : "5"                      
                      ,"fieldType"      : "int"
                      ,"copyGRD"        : [0,1]
                      ,"newRecord"      : ["0","this","this"]
                      ,"validar"        : ["notnull","intMaiorIgualZero"]
                      ,"digitosMinMax"  : [1,1]
                      ,"ajudaCampo"     : [ "Direito para opções..."]
                      ,"importaExcel"   : "S"                                                                
                      ,"padrao":0}
            ,{"id":20 ,"field"          : "UP_D16"  ,"labelCol":"D16" ,"labelColImp":"16" ,"obj":"cbD16"
                      ,"tipo"           : "cb"                      
                      ,"tamGrd"         : "0em"
                      ,"tamImp"         : "5"                      
                      ,"fieldType"      : "int"
                      ,"copyGRD"        : [0,1]
                      ,"newRecord"      : ["0","this","this"]
                      ,"validar"        : ["notnull","intMaiorIgualZero"]
                      ,"digitosMinMax"  : [1,1]
                      ,"ajudaCampo"     : [ "Direito para opções..."]
                      ,"importaExcel"   : "S"                                                                
                      ,"padrao":0}
            ,{"id":21 ,"field"          : "UP_D17"  ,"labelCol":"D17" ,"labelColImp":"17" ,"obj":"cbD17"
                      ,"tipo"           : "cb"                      
                      ,"tamGrd"         : "0em"
                      ,"tamImp"         : "5"                      
                      ,"fieldType"      : "int"
                      ,"copyGRD"        : [0,1]
                      ,"newRecord"      : ["0","this","this"]
                      ,"validar"        : ["notnull","intMaiorIgualZero"]
                      ,"digitosMinMax"  : [1,1]
                      ,"ajudaCampo"     : [ "Direito para opções..."]
                      ,"importaExcel"   : "S"                                                                
                      ,"padrao":0}
            ,{"id":22 ,"field"          : "UP_D18"  ,"labelCol":"D18" ,"labelColImp":"18" ,"obj":"cbD18"
                      ,"tipo"           : "cb"                      
                      ,"tamGrd"         : "0em"
                      ,"tamImp"         : "5"                      
                      ,"fieldType"      : "int"
                      ,"copyGRD"        : [0,1]
                      ,"newRecord"      : ["0","this","this"]
                      ,"validar"        : ["notnull","intMaiorIgualZero"]
                      ,"digitosMinMax"  : [1,1]
                      ,"ajudaCampo"     : [ "Direito para opções..."]
                      ,"importaExcel"   : "S"                                                                
                      ,"padrao":0}
            ,{"id":23 ,"field"          : "UP_D19"  ,"labelCol":"D19" ,"labelColImp":"19" ,"obj":"cbD19"
                      ,"tipo"           : "cb"                      
                      ,"tamGrd"         : "0em"
                      ,"tamImp"         : "5"                      
                      ,"fieldType"      : "int"
                      ,"copyGRD"        : [0,1]
                      ,"newRecord"      : ["0","this","this"]
                      ,"validar"        : ["notnull","intMaiorIgualZero"]
                      ,"digitosMinMax"  : [1,1]
                      ,"ajudaCampo"     : [ "Direito para opções..."]
                      ,"importaExcel"   : "S"                                                                
                      ,"padrao":0}
            ,{"id":24 ,"field"          : "UP_D20"  ,"labelCol":"D20" ,"labelColImp":"20" ,"obj":"cbD20"
                      ,"tipo"           : "cb"                      
                      ,"tamGrd"         : "0em"
                      ,"tamImp"         : "5"                      
                      ,"fieldType"      : "int"
                      ,"copyGRD"        : [0,1]
                      ,"newRecord"      : ["0","this","this"]
                      ,"validar"        : ["notnull","intMaiorIgualZero"]
                      ,"digitosMinMax"  : [1,1]
                      ,"ajudaCampo"     : [ "Direito para opções..."]
                      ,"importaExcel"   : "S"                                                                
                      ,"padrao":0}
            ,{"id":25 ,"field"          : "UP_ATIVO"  
                      ,"labelCol"       : "ATIVO"   
                      ,"obj"            : "cbAtivo"
                      ,"tamImp"         : "10"
                      ,"padrao":2}                                        
            ,{"id":26 ,"field"          : "UP_REG"    
                      ,"labelCol"       : "REG"     
                      ,"obj"            : "cbReg"      
                      ,"tamImp"         : "10"
                      ,"lblDetalhe"     : "REGISTRO"     
                      ,"ajudaDetalhe"   : "Se o registro é PUBlico/ADMinistrador ou do SIStema"                                         
                      ,"padrao":3}  
            ,{"id":27 ,"field"          : "US_APELIDO" 
                      ,"labelCol"       : "USUARIO" 
                      ,"obj"            : "edtUsuario" 
                      ,"tamGrd"         : "10em"
                      ,"padrao":4}                
            ,{"id":28 ,"field"          : "UP_CODUSR" 
                      ,"labelCol"       : "CODUSU"  
                      ,"obj"            : "edtCodUsu"  
                      ,"padrao":5}                                      
            ,{"id":29 ,"labelCol"       : "PP"      
                      ,"obj"            : "imgPP"        
                      ,"func":"var elTr=this.parentNode.parentNode;"
                        +"elTr.cells[0].childNodes[0].checked=true;"
                        +"objUp.espiao();"
                        +"elTr.cells[0].childNodes[0].checked=false;"
                      ,"padrao":8}
            ,{"id":30 ,"field"          : "CONSULTAR_RELATORIO"  
                      ,"labelCol"       : "CONSULT. RELATÓRIO"   
                      ,"obj"            : "cbConsultaRelatorio"
                      ,"tamGrd"         : "10em"
                      ,"tamImp"         : "10"
                      ,"padrao":10}   
            ,{"id":31 ,"field"          : "GRUPO_OPERACIONAL"  
                      ,"labelCol"       : "GRUPO OPERACIONAL"   
                      ,"obj"            : "cbGrupoOperacional"
                      ,"tamGrd"         : "10em"
                      ,"tamImp"         : "10"
                      ,"padrao":10}   
          ]
          ,
          "detalheRegistro":
          [
            { "width"           :"100%"
              ,"height"         :"300px" 
              ,"label"          :"PERFIL - Detalhe do registro"
            }
          ]
          , 
          "botoesH":[
             {"texto":"Cadastrar" ,"name":"horCadastrar"  ,"onClick":"0"  ,"enabled":true ,"imagem":"fa fa-plus"             ,"ajuda":"Novo registro" }
            ,{"texto":"Alterar"   ,"name":"horAlterar"    ,"onClick":"1"  ,"enabled":true ,"imagem":"fa fa-pencil-square-o"  ,"ajuda":"Alterar registro selecionado" }
            ,{"texto":"Excluir"   ,"name":"horExcluir"    ,"onClick":"2"  ,"enabled":true ,"imagem":"fa fa-minus"            ,"ajuda":"Excluir registro selecionado" }
            ,{"texto":"Excel"     ,"name":"horExcel"      ,"onClick":"5"  ,"enabled":false,"imagem":"fa fa-file-excel-o"     ,"ajuda":"Exportar para excel" }        
            ,{"texto":"Fechar"    ,"name":"horFechar"     ,"onClick":"8"  ,"enabled":true ,"imagem":"fa fa-close"            ,"ajuda":"Fechar formulario" }
          ] 
          ,"registros"      : []                    // Recebe um Json vindo da classe clsBancoDados
          ,"opcRegSeek"     : true                  // Opção para numero registros/botão/procurar                     
          ,"checarTags"     : "S"                   // Somente em tempo de desenvolvimento(olha as pricipais tags)                  
          ,"idBtnConfirmar" : "btnConfirmar"        // Se existir executa o confirmar do form/fieldSet
          ,"idBtnCancelar"  : "btnCancelar"         // Se existir executa o cancelar do form/fieldSet
          ,"div"            : "frmUp"               // Onde vai ser gerado a table
          ,"divFieldSet"    : "tabelaUp"            // Para fechar a div onde estão os fieldset ao cadastrar
          ,"form"           : "frmUp"               // Onde vai ser gerado o fieldSet       
          ,"divModal"       : "divTopoInicio"       // Onde vai se appendado abaixo deste a table 
          ,"tbl"            : "tblUp"               // Nome da table
          ,"prefixo"        : "up"                  // Prefixo para elementos do HTML em jsTable2017.js
          ,"tabelaBD"       : "VUSUARIOPERFIL"      // Nome da tabela no banco de dados  
          ,"tabelaBKP"      : "BKPUSUARIOPERFIL"    // Nome da tabela no banco de dados  
          ,"fieldAtivo"     : "UP_ATIVO"            // SE EXISITIR - Nome do campo ATIVO(S/N) na tabela BD
          ,"fieldConsultarRelatorio"     : "CONSULTAR_RELATORIO"            // SE EXISITIR - Nome do campo ATIVO(S/N) na tabela BD
          ,"fieldGrupoOperacional"     : "GRUPO_OPERACIONAL"            // SE EXISITIR - Nome do campo ATIVO(S/N) na tabela BD
          ,"fieldReg"       : "UP_REG"              // SE EXISITIR - Nome do campo SYS(P/A) na tabela BD            
          ,"fieldCodUsu"    : "UP_CODUSR"           // SE EXISITIR - Nome do campo CODIGO USUARIO na tabela BD                        
          ,"iFrame"         : "iframeCorpo"         // Se a table vai ficar dentro de uma tag iFrame
          //,"fieldCodEmp"  : "*"                   // SE EXISITIR - Nome do campo CODIGO EMPRESA na tabela BD            
          //,"fieldCodDir"  : "*"                   // SE EXISITIR - Nome do campo CODIGO DIREITO na tabela BD                        
          ,"width"          : "80em"                // Tamanho da table
          ,"height"         : "58em"                // Altura da table
          ,"tableLeft"      : "sim"                 // Se tiver menu esquerdo
          ,"relTitulo"      : "PERFIL"              // Titulo do relatório
          ,"relOrientacao"  : "P"                   // Paisagem ou retrato
          ,"relFonte"       : "8"                   // Fonte do relatório
          ,"foco"           : ["edtDescricao"
                              ,"edtDescricao"
                              ,"btnConfirmar"]      // Foco qdo Cad/Alt/Exc
          ,"formPassoPasso" : "Trac_Espiao.php"    // Enderço da pagina PASSO A PASSO
          ,"indiceTable"    : "DESCRICAO"           // Indice inicial da table
          ,"tamBotao"       : "15"                  // Tamanho botoes defalt 12 [12/25/50/75/100]
          ,"tamMenuTable"   : ["10em","20em"]                                
          ,"labelMenuTable" : "Opções"              // Caption para menu table 
          ,"_menuTable"     :[
                                ["Regitros ativos"                        ,"fa-thumbs-o-up"   ,"btnFiltrarClick('S');"]
                               ,["Registros inativos"                     ,"fa-thumbs-o-down" ,"btnFiltrarClick('N');"]
                               ,["Todos"                                  ,"fa-folder-open"   ,"btnFiltrarClick('*');"]
                               //,["Importar planilha excel"                ,"fa-file-excel-o"  ,"fExcel()"]
                               ,["Imprimir registros em tela"             ,"fa-print"         ,"objUp.imprimir()"]
                               ,["Ajuda para campos padrões"              ,"fa-info"          ,"objUp.AjudaSisAtivo(jsUp);"]
                               ,["Detalhe do registro"                    ,"fa-folder-o"      ,"objUp.detalhe();"]
                               ,["Gerar excel"                            ,"fa-file-excel-o"  ,"objUp.excel();"]
                               ,["Passo a passo do registro"              ,"fa-binoculars"    ,"objUp.espiao();"]
                               ,["Alterar status Ativo/Inativo"           ,"fa-share"         ,"objUp.altAtivo(intCodDir);"]
                               ,["Alterar registro PUBlico/ADMinistrador" ,"fa-reply"         ,"objUp.altPubAdm(intCodDir,jsPub[0].usr_admpub);"]
                               //,["Alterar para registro do SISTEMA"       ,"fa-reply"         ,"objUp.altRegSistema("+jsPub[0].usr_d05+");"] 
                               ,["Número de registros em tela"            ,"fa-info"          ,"objUp.numRegistros();"]                               
                               ,["Atualizar grade consulta"               ,"fa-filter"        ,"btnFiltrarClick('S');"]
                             ]  
          ,"codTblUsu"      : "PERFIL[03]"                          
          ,"codDir"         : intCodDir
        }; 
        if( objUp === undefined ){  
          objUp=new clsTable2017("objUp");
        };  
        objUp.montarHtmlCE2017(jsUp); 
        ////////////////////////////////////////
        //    Fim objeto clsTable2017 PERFIL  //
        //////////////////////////////////////// 
        //
        //
        //////////////////////////////////////////////
        // Montando a table para importar xls       //
        //////////////////////////////////////////////
        jsExc={
          "titulo":[
             {"id":0  ,"field":"CODIGO"     ,"labelCol":"CODIGO"    ,"tamGrd":"6em"  ,"tamImp":"20","align":"center"}
            ,{"id":1  ,"field":"DESCRICAO"  ,"labelCol":"DESCRICAO" ,"tamGrd":"10em" ,"tamImp":"30"}
            ,{"id":2  ,"field":"D01"        ,"labelCol":"D01"       ,"tamGrd":"2em"  ,"tamImp":"10"}
            ,{"id":3  ,"field":"D02"        ,"labelCol":"D02"       ,"tamGrd":"2em"  ,"tamImp":"10"}
            ,{"id":4  ,"field":"D03"        ,"labelCol":"D03"       ,"tamGrd":"2em"  ,"tamImp":"10"}
            ,{"id":5  ,"field":"D04"        ,"labelCol":"D04"       ,"tamGrd":"2em"  ,"tamImp":"10"}
            ,{"id":6  ,"field":"D05"        ,"labelCol":"D05"       ,"tamGrd":"2em"  ,"tamImp":"10"}
            ,{"id":7  ,"field":"D06"        ,"labelCol":"D06"       ,"tamGrd":"2em"  ,"tamImp":"10"}
            ,{"id":8  ,"field":"D07"        ,"labelCol":"D07"       ,"tamGrd":"2em"  ,"tamImp":"10"}
            ,{"id":9  ,"field":"D08"        ,"labelCol":"D08"       ,"tamGrd":"2em"  ,"tamImp":"10"}
            ,{"id":10 ,"field":"D09"        ,"labelCol":"D09"       ,"tamGrd":"2em"  ,"tamImp":"10"}
            ,{"id":11 ,"field":"D10"        ,"labelCol":"D10"       ,"tamGrd":"2em"  ,"tamImp":"10"}
            ,{"id":12 ,"field":"D11"        ,"labelCol":"D11"       ,"tamGrd":"2em"  ,"tamImp":"10"}
            ,{"id":13 ,"field":"D12"        ,"labelCol":"D12"       ,"tamGrd":"2em"  ,"tamImp":"10"}
            ,{"id":14 ,"field":"D13"        ,"labelCol":"D13"       ,"tamGrd":"2em"  ,"tamImp":"10"}
            ,{"id":15 ,"field":"D14"        ,"labelCol":"D14"       ,"tamGrd":"2em"  ,"tamImp":"10"}
            ,{"id":16 ,"field":"D15"        ,"labelCol":"D15"       ,"tamGrd":"2em"  ,"tamImp":"10"}
            ,{"id":17 ,"field":"D16"        ,"labelCol":"D16"       ,"tamGrd":"2em"  ,"tamImp":"10"}
            ,{"id":18 ,"field":"D17"        ,"labelCol":"D17"       ,"tamGrd":"2em"  ,"tamImp":"10"}
            ,{"id":19 ,"field":"D18"        ,"labelCol":"D18"       ,"tamGrd":"2em"  ,"tamImp":"10"}
            ,{"id":20 ,"field":"D19"        ,"labelCol":"D19"       ,"tamGrd":"2em"  ,"tamImp":"10"}
            ,{"id":21 ,"field":"D20"        ,"labelCol":"D20"       ,"tamGrd":"2em"  ,"tamImp":"10"}
            ,{"id":22 ,"field":"ERRO"       ,"labelCol":"ERRO"      ,"tamGrd":"35em" ,"tamImp":"100"}
          ]
          ,"botoesH":[
             {"texto":"Imprimir"  ,"name":"excImprimir"   ,"onClick":"3"  ,"enabled":true ,"imagem":"fa fa-print"            ,"ajuda":"Imprimir registros em tela" }        
            ,{"texto":"Excel"     ,"name":"excExcel"      ,"onClick":"5"  ,"enabled":false,"imagem":"fa fa-file-excel-o"     ,"ajuda":"Exportar para excel" }        
            ,{"texto":"Fechar"    ,"name":"excFechar"     ,"onClick":"7"  ,"enabled":true ,"imagem":"fa fa-close"            ,"ajuda":"Fechar formulario" }
          ] 
          ,"registros"      : []                    // Recebe um Json vindo da classe clsBancoDados
          ,"corLinha"       : "if(ceTr.cells[22].innerHTML !='OK') {ceTr.style.color='yellow';ceTr.style.backgroundColor='red';}"      
          ,"checarTags"     : "N"                   // Somente em tempo de desenvolvimento(olha as pricipais tags)                                
          ,"div"            : "frmExc"              // Onde vai ser gerado a table
          ,"divFieldSet"    : "tabelaExc"           // Para fechar a div onde estão os fieldset ao cadastrar
          ,"form"           : "frmExc"              // Onde vai ser gerado o fieldSet                     
          ,"divModal"       : "divTopoInicioE"      // Nome da div que vai fazer o show modal
          ,"tbl"            : "tblExc"              // Nome da table
          ,"prefixo"        : "exc"                 // Prefixo para elementos do HTML em jsTable2017.js
          ,"tabelaBD"       : "*"                   // Nome da tabela no banco de dados  
          ,"width"          : "90em"                // Tamanho da table
          ,"height"         : "48em"                // Altura da table
          ,"tableLeft"      : "sim"                 // Se tiver menu esquerdo
          ,"relTitulo"      : "Importação perfil"   // Titulo do relatório
          ,"relOrientacao"  : "P"                   // Paisagem ou retrato
          ,"indiceTable"    : "TAG"                 // Indice inicial da table
          ,"relFonte"       : "8"                   // Fonte do relatório
          ,"formName"       : "frmExc"              // Nome do formulario para opção de impressão 
          ,"tamBotao"       : "20"                  // Tamanho botoes defalt 12 [12/25/50/75/100]
        }; 
        if( objExc === undefined ){          
          objExc=new clsTable2017("objCon");
        };  
        objExc.montarHtmlCE2017(jsExc);
        //
        //  
        btnFiltrarClick("S");  
      });
      //
      var objUp;                      // Obrigatório para instanciar o JS TFormaCob
      var jsUp;                       // Obj principal da classe clsTable2017
      var objExc;                     // Obrigatório para instanciar o JS Importar excel
      var jsExc;                      // Obrigatório para instanciar o objeto objExc
      var objGrpF10;                  // Obrigatório para instanciar o JS GrupoF10
      var clsJs;                      // Classe responsavel por montar um Json e eviar PHP
      var clsErro;                    // Classe para erros            
      var fd;                         // Formulario para envio de dados para o PHP
      var msg;                        // Variavel para guardadar mensagens de retorno/erro 
      var retPhp                      // Retorno do Php para a rotina chamadora
      var contMsg   = 0;              // contador para mensagens
      var cmp       = new clsCampo(); // Abrindo a classe campos
      var jsPub     = JSON.parse(localStorage.getItem("lsPublico"));
      var intCodDir = parseInt(jsPub[0].usr_d03);
      function funcRetornar(intOpc){
        document.getElementById("divRotina").style.display  = (intOpc==0 ? "block" : "none" );        
        document.getElementById("divExcel").style.display   = (intOpc==1 ? "block" : "none" );
      };
      function fExcel(){
        if( intCodDir<2 ){
          clsErro     = new clsMensagem("Erro");
          clsErro.add("USUARIO SEM DIREITO DE CADASTRAR NESTA TABELA DO BANCO DE DADOS");            
          if( clsErro.ListaErr() != "" ){
            clsErro.Show();
          }
        } else {  
          funcRetornar(1);  
        }  
      };
      function excFecharClick(){
        funcRetornar(0);  
      };
      ////////////////////////////
      // Filtrando os registros //
      ////////////////////////////
      function btnFiltrarClick(atv) { 
        clsJs   = jsString("lote");  
        clsJs.add("rotina"      , "selectUp"          );
        clsJs.add("login"       , jsPub[0].usr_login  );
        clsJs.add("ativo"       , atv                 );
        fd = new FormData();
        fd.append("usuarioperfil" , clsJs.fim());
        msg     = requestPedido("Trac_UsuarioPerfil.php",fd); 
        retPhp  = JSON.parse(msg);
        if( retPhp[0].retorno == "OK" ){
          //////////////////////////////////////////////////////////////////////////////////
          // O novo array não tem o campo idUnico mas a montarHtmlCE2017 ja foi executada //
          // Campo obrigatório se existir rotina de manutenção na table devido Json       //
          // Esta rotina não tem manutenção via classe clsTable2017                       //
          // jsCrv.registros=objCrv.addIdUnico(retPhp[0]["dados"]);                       //
          //////////////////////////////////////////////////////////////////////////////////
          jsUp.registros=objUp.addIdUnico(retPhp[0]["dados"]);
          objUp.ordenaJSon(jsUp.indiceTable,false);  
          objUp.montarBody2017();
        };  
      };
      ////////////////////
      // Importar excel //
      ////////////////////
      function btnAbrirExcelClick(){
        clsErro = new clsMensagem("Erro");
        clsErro.notNull("ARQUIVO"       ,edtArquivo.value);
        if( clsErro.ListaErr() != "" ){
          clsErro.Show();
        } else {
          clsJs   = jsString("lote");  
          clsJs.add("rotina"      , "impExcel"          );
          clsJs.add("login"       , jsPub[0].usr_login  );
          clsJs.add("cabec"       , "CODIGO|DESCRICAO|D01|D02|D03|D04|D05|D06|D07|D08|D09|D10"  );          
          fd = new FormData();
          fd.append("usuarioperfil" , clsJs.fim());
          fd.append("arquivo"       , edtArquivo.files[0] );
          msg     = requestPedido("Trac_UsuarioPerfil.php",fd); 
          retPhp  = JSON.parse(msg);
          if( retPhp[0].retorno == "OK" ){
            //////////////////////////////////////////////////////////////////////////////////
            // O novo array não tem o campo idUnico mas a montarHtmlCE2017 ja foi executada //
            // Campo obrigatório se existir rotina de manutenção na table devido Json       //
            // Esta rotina não tem manutenção via classe clsTable2017                       //
            // jsCrv.registros=objCrv.addIdUnico(retPhp[0]["dados"]);                       //
            //////////////////////////////////////////////////////////////////////////////////
            jsExc.registros=retPhp[0]["dados"];
            objExc.montarBody2017();
          };  
          /////////////////////////////////////////////////////////////////////////////////////////
          // Mesmo se der erro mostro o erro, se der ok mostro a qtdade de registros atualizados //
          // dlgCancelar fecha a caixa de informacao de data                                     //
          /////////////////////////////////////////////////////////////////////////////////////////
          gerarMensagemErro("UP",retPhp[0].erro,"AVISO");    
        };  
      };
      function grpFocus(obj){ 
        document.getElementById(obj.id).setAttribute("data-oldvalue",document.getElementById(obj.id).value); 
      };
      function grpF10Click(){ fGrupoF10(0,"edtCodGrp","cbD01"); };  
      function RetF10tblGrp(arr){
        document.getElementById("edtPerfilGrupo").value   = arr[0].CODIGO;
        document.getElementById("edtDesPerfilGrupo").value   = arr[0].APELIDO;
        document.getElementById("edtPerfilGrupo").setAttribute("data-oldvalue",arr[0].CODIGO);
      };
      function codGrpBlur(obj){
        var elOld = jsNmrs(document.getElementById(obj.id).getAttribute("data-oldvalue")).inteiro().ret();
        var elNew = jsNmrs(obj.id).inteiro().ret();
        if( elOld != elNew ){
          var ret = fGrupoF10(1,obj.id,"cbD01");
          document.getElementById(obj.id).value          = ( ret.length == 0 ? "0000"    : jsNmrs(ret[0].CODIGO).emZero(4).ret()  );
          document.getElementById("edtDesPerfilGrupo").value     = ( ret.length == 0 ? ""        : ret[0].APELIDO                       );
          document.getElementById(obj.id).setAttribute("data-oldvalue",( ret.length == 0 ? "0000" : ret[0].CODIGO )               );
        };
      };
    </script>
  </head>
  <body>
    <div class="divTelaCheia">
      <div id="divRotina" class="conteudo" style="display:block;overflow-x:auto;">  
        <div id="divTopoInicio">
        </div>
        <form method="post" 
              name="frmUp" 
              id="frmUp" 
              class="frmTable" 
              action="classPhp/imprimirsql.php" 
              target="_newpage"
              style="top: 0em; width:90em; height:50em; position: absolute; z-index:30;display:none;">
          <p class="frmCampoTit">
          <input class="informe" type="text" name="titulo" value="Perfil" disabled="" style="color: white; text-align: left;">
          </p>
          <div style="height: 40em; overflow-y: auto;">
            <input type="hidden" id="sql" name="sql"/>
            <div class="campotexto campo100">
              <div class="campotexto campo25">
                <input class="campo_input"  name="edtCodigo" id="edtCodigo" type="text" />
                <label class="campo_label campo_required" for="edtCodigo">CODIGO</label>
              </div>
              <div class="campotexto campo75">
                <input class="campo_input"  name="edtDescricao" id="edtDescricao" type="text" maxlength="15" />
                <label class="campo_label campo_required" for="edtDescricao">DESCRICAO</label>
              </div>
              <div class="campotexto campo25">
                <input class="campo_input inputF10" id="edtPerfilGrupo"
                                                    OnKeyPress="return mascaraInteiro(event);"
                                                    onBlur="codGrpBlur(this);" 
                                                    onFocus="grpFocus(this);" 
                                                    onClick="grpF10Click();"
                                                    data-oldvalue=""
                                                    autocomplete="off" 
                                                    maxlength="4"
                                                    type="text" />
                <label class="campo_label campo_required" for="edtPerfilGrupo">GRUPO</label>
              </div>
              <div class="campotexto campo75">
                <input class="campo_input_titulo input" id="edtDesPerfilGrupo" type="text" disabled />
                <label class="campo_label campo_required" for="edtDesPerfilGrupo">RAZAO_GRUPO</label>
              </div>
              <div class="campotexto campo20">
                <select class="campo_input_combo" name="cbD01" id="cbD01">
                  <option value="0">0-SEM DIREITO</option>
                  <option value="1">1-CON</option>
                  <option value="2">2-CON/INC</option>
                  <option value="3">3-CON/INC/ALT</option>
                  <option value="4">4-CON/INC/ALT/EXC</option>
                </select>
                <label class="campo_label campo_required" for="cbD01">USUARIOS[01]</label>
              </div>
              <div class="campotexto campo20">
                <select class="campo_input_combo" name="cbD02" id="cbD02">
                  <option value="0">0-SEM DIREITO</option>
                  <option value="1">1-CON</option>
                  <option value="2">2-CON/INC</option>
                  <option value="3">3-CON/INC/ALT</option>
                  <option value="4">4-CON/INC/ALT/EXC</option>
                </select>
                <label class="campo_label campo_required" for="cbD02">PARAMETRO[02]</label>
              </div>
              
              <div class="campotexto campo20">
                <select class="campo_input_combo" name="cbD03" id="cbD03">
                  <option value="0">0-SEM DIREITO</option>
                  <option value="1">1-CON</option>
                  <option value="2">2-CON/INC</option>
                  <option value="3">3-CON/INC/ALT</option>
                  <option value="4">4-CON/INC/ALT/EXC</option>
                </select>
                <label class="campo_label campo_required" for="cbD03">INTEGRAR[03]</label>
              </div>
              <div class="campotexto campo20">
                <select class="campo_input_combo" name="cbD04" id="cbD04">
                  <option value="0">0-SEM DIREITO</option>
                  <option value="1">1-CON</option>
                  <option value="2">2-CON/INC</option>
                  <option value="3">3-CON/INC/ALT</option>
                  <option value="4">4-CON/INC/ALT/EXC</option>
                </select>
                <label class="campo_label campo_required" for="cbD04">MOTORISTA[04]</label>
              </div>
              <div class="campotexto campo20">
                <select class="campo_input_combo" name="cbD05" id="cbD05">
                  <option value="0">0-SEM DIREITO</option>
                  <option value="1">1-CON</option>
                  <option value="2">2-CON/INC</option>
                  <option value="3">3-CON/INC/ALT</option>
                  <option value="4">4-CON/INC/ALT/EXC</option>
                </select>
                <label class="campo_label campo_required" for="cbD05">REG SISTEMA[05]</label>
              </div>
              <!---->
              <div class="campotexto campo20">
                <select class="campo_input_combo" name="cbD06" id="cbD06">
                  <option value="0">0-SEM DIREITO</option>
                  <option value="1">1-CON</option>
                  <option value="2">2-CON/INC</option>
                  <option value="3">3-CON/INC/ALT</option>
                  <option value="4">4-CON/INC/ALT/EXC</option>
                </select>
                <label class="campo_label campo_required" for="cbD06">GRUPO[06]</label>
              </div>
              <!---->
              <div class="campotexto campo20">
                <select class="campo_input_combo" name="cbD07" id="cbD07">
                  <option value="0">0-SEM DIREITO</option>
                  <option value="1">1-CON</option>
                  <option value="2">2-CON/INC</option>
                  <option value="3">3-CON/INC/ALT</option>
                  <option value="4">4-CON/INC/ALT/EXC</option>
                </select>
                <label class="campo_label campo_required" for="cbD07">UNIDADE[07]</label>
              </div>
              <!---->
              <div class="campotexto campo20">
                <select class="campo_input_combo" name="cbD08" id="cbD08">
                  <option value="0">0-SEM DIREITO</option>
                  <option value="1">1-CON</option>
                  <option value="2">2-CON/INC</option>
                  <option value="3">3-CON/INC/ALT</option>
                  <option value="4">4-CON/INC/ALT/EXC</option>
                </select>
                <label class="campo_label campo_required" for="cbD08">POLO[08]</label>
              </div>
              <!---->
              <div class="campotexto campo20">
                <select class="campo_input_combo" name="cbD09" id="cbD09">
                  <option value="0">0-SEM DIREITO</option>
                  <option value="1">1-CON</option>
                  <option value="2">2-CON/INC</option>
                  <option value="3">3-CON/INC/ALT</option>
                  <option value="4">4-CON/INC/ALT/EXC</option>
                </select>
                <label class="campo_label campo_required" for="cbD09">VEICULO[09]</label>
              </div>
              <!---->
              <div class="campotexto campo20">
                <select class="campo_input_combo" name="cbD10" id="cbD10">
                  <option value="0">0-SEM DIREITO</option>
                  <option value="1">1-CON</option>
                  <option value="2">2-CON/INC</option>
                  <option value="3">3-CON/INC/ALT</option>
                  <option value="4">4-CON/INC/ALT/EXC</option>
                </select>
                <label class="campo_label campo_required" for="cbD10">EVENTO[10]</label>
              </div>
              <!---->
              <div class="campotexto campo20">
                <select class="campo_input_combo" name="cbD11" id="cbD11">
                  <option value="0">0-NAO</option>
                  <option value="4">4-SIM</option>
                </select>
                <label class="campo_label campo_required" for="cbD11">EXCLUIR ID[11]</label>
              </div>
              <!---->
              <div class="campotexto campo20">
                <select class="campo_input_combo" name="cbD12" id="cbD12">
                  <option value="0">0-SEM DIREITO</option>
                  <option value="1">1-CON</option>
                  <option value="2">2-CON/INC</option>
                  <option value="3">3-CON/INC/ALT</option>
                  <option value="4">4-CON/INC/ALT/EXC</option>
                </select>
                <label class="campo_label campo_required" for="cbD12">POSICAO GSLOG[12]</label>
              </div>
              <!---->
              <div class="campotexto campo20">
                <select class="campo_input_combo" name="cbD13" id="cbD13">
                  <option value="0">0-SEM DIREITO</option>
                  <option value="1">1-CON</option>
                  <option value="2">2-CON/INC</option>
                  <option value="3">3-CON/INC/ALT</option>
                  <option value="4">4-CON/INC/ALT/EXC</option>
                </select>
                <label class="campo_label campo_required" for="cbD13">...[13]</label>
              </div>
              <!---->
              <div class="campotexto campo20">
                <select class="campo_input_combo" name="cbD14" id="cbD14">
                  <option value="0">0-SEM DIREITO</option>
                  <option value="1">1-CON</option>
                  <option value="2">2-CON/INC</option>
                  <option value="3">3-CON/INC/ALT</option>
                  <option value="4">4-CON/INC/ALT/EXC</option>
                </select>
                <label class="campo_label campo_required" for="cbD14">...[14]</label>
              </div>
              <!---->
              <div class="campotexto campo20">
                <select class="campo_input_combo" name="cbD15" id="cbD15">
                  <option value="0">0-SEM DIREITO</option>
                  <option value="1">1-CON</option>
                  <option value="2">2-CON/INC</option>
                  <option value="3">3-CON/INC/ALT</option>
                  <option value="4">4-CON/INC/ALT/EXC</option>
                </select>
                <label class="campo_label campo_required" for="cbD15">...[15]</label>
              </div>
              <!---->
              <div class="campotexto campo20">
                <select class="campo_input_combo" name="cbD16" id="cbD16">
                  <option value="0">0-SEM DIREITO</option>
                  <option value="1">1-CON</option>
                  <option value="2">2-CON/INC</option>
                  <option value="3">3-CON/INC/ALT</option>
                  <option value="4">4-CON/INC/ALT/EXC</option>
                </select>
                <label class="campo_label campo_required" for="cbD16">...[16]</label>
              </div>
              <!---->
              <div class="campotexto campo20">
                <select class="campo_input_combo" name="cbD17" id="cbD17">
                  <option value="0">0-SEM DIREITO</option>
                  <option value="1">1-CON</option>
                  <option value="2">2-CON/INC</option>
                  <option value="3">3-CON/INC/ALT</option>
                  <option value="4">4-CON/INC/ALT/EXC</option>
                </select>
                <label class="campo_label campo_required" for="cbD17">...[17]</label>
              </div>
              <!---->
              <div class="campotexto campo20">
                <select class="campo_input_combo" name="cbD18" id="cbD18">
                  <option value="0">0-SEM DIREITO</option>
                  <option value="1">1-CON</option>
                  <option value="2">2-CON/INC</option>
                  <option value="3">3-CON/INC/ALT</option>
                  <option value="4">4-CON/INC/ALT/EXC</option>
                </select>
                <label class="campo_label campo_required" for="cbD18">...[18]</label>
              </div>
              <!---->
              <div class="campotexto campo20">
                <select class="campo_input_combo" name="cbD19" id="cbD19">
                  <option value="0">0-SEM DIREITO</option>
                  <option value="1">1-CON</option>
                  <option value="2">2-CON/INC</option>
                  <option value="3">3-CON/INC/ALT</option>
                  <option value="4">4-CON/INC/ALT/EXC</option>
                </select>
                <label class="campo_label campo_required" for="cbD19">...[19]</label>
              </div>
              <!---->
              <div class="campotexto campo20">
                <select class="campo_input_combo" name="cbD20" id="cbD20">
                  <option value="0">0-SEM DIREITO</option>
                  <option value="1">1-CON</option>
                  <option value="2">2-CON/INC</option>
                  <option value="3">3-CON/INC/ALT</option>
                  <option value="4">4-CON/INC/ALT/EXC</option>
                </select>
                <label class="campo_label campo_required" for="cbD20">...[20]</label>
              </div>
              <div class="campotexto campo20">
                <select class="campo_input_combo" name="cbAtivo" id="cbAtivo">
                  <option value="S">SIM</option>
                  <option value="N">NAO</option>
                </select>
                <label class="campo_label campo_required" for="cbAtivo">ATIVO</label>
              </div>
              <div class="campotexto campo20">
                <select class="campo_input_combo" name="cbReg" id="cbReg">
                  <option value="P">PUBLICO</option>               
                </select>
                <label class="campo_label campo_required" for="cbReg">REG</label>
              </div>
              <div class="campotexto campo20">
                <select class="campo_input_combo" name="cbConsultaRelatorio" id="cbConsultaRelatorio">
                  <option value="S">PERMITIDO</option>
                  <option value="N" selected>NAO PERMITIDO</option>
                </select>
                <label class="campo_label campo_required" for="cbConsultaRelatorio">CONSULT. RELATÓRIO</label>
              </div>
              <div class="campotexto campo20">
                <select class="campo_input_combo" name="cbGrupoOperacional" id="cbGrupoOperacional">
                  <option value="S">PERMITIDO</option>
                  <option value="N" selected>NAO PERMITIDO</option>
                </select>
                <label class="campo_label campo_required" for="cbGrupoOperacional">GRUPO OPERACIONAL</label>
              </div>
              <div class="campotexto campo20">
                <input class="campo_input_titulo" disabled name="edtUsuario" id="edtUsuario" type="text" />
                <label class="campo_label campo_required" for="edtUsuario">USUARIO</label>
              </div>
              <div class="inactive">
                <input id="edtCodUsu" type="text" />
              </div>
              <div class="campotexto campo100">
                <div class="campotexto campo50" style="margin-top:1em;">
                  <label class="campo_required" style="font-size:1.4em;"></label>
                  <label class="campo_labelSombra">Campo obrigatório</label>
                </div>              
                <div class="campo15" style="float:right;">            
                  <input id="btnConfirmar" type="button" value="Confirmar" class="campo100 tableBotao botaoForaTable"/>            
                  <i class="faBtn fa-check icon-large"></i>
                </div>
                <div class="campo15" style="float:right;">            
                  <input id="btnCancelar" type="button" value="Cancelar" class="campo100 tableBotao botaoForaTable"/>            
                  <i class="faBtn fa-close icon-large"></i>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
      <!--/Importar excel -->
      <!--<div id="divExcel" class="divTopoExcel" style="display:none;overflow-x:auto;height:5.4em;border:1px solid silver;">-->
      <div id="divExcel" class="divTopoExcel">
        <div id="divTopoInicioE" class="divTopoInicio">
          <div class="divTopoInicio_Informacao" style="padding-top: 0.2em;border:none;">
            <div class="campotexto campo50">
              <input class="campo_file input" name="edtArquivo" id="edtArquivo" type="file" />
              <label class="campo_label" for="edtArquivo">Arquivo</label>
            </div>
            <div class="campo12" style="float:left;">            
              <input id="btnAbrirExcel" onClick="btnAbrirExcelClick();" type="button" value="Abrir" class="campo100 tableBotao botaoForaTable" style="height: 3.4em !important;"/>            
              <!--<i class="faBtn fa-search icon-large"></i>-->
            </div>
          </div>        
          <!--<div class="logoHome" data-name="Home" onclick="ncmRetornar(0);"></div>-->
        </div>
        <div id="xmlModal" class="divShowModal" style="display:none;"></div>
        <div id="divErr" class="conteudo" style="display:block;overflow-x:auto;">
          <form method="post" name="frmExc" class="center" id="frmExc" action="imprimirsql.php" target="_newpage" >
            <input type="hidden" id="sql" name="sql"/>
            <div id="tabelaExc" class="center active" style="position:fixed;top:10em;width:90em;z-index:30;display:none;" >
            </div>
          </form>
        </div>
      </div>
      <!--/Fim Importar excel -->
    </div>       
  </body>
</html>