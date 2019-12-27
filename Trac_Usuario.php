<?php
  session_start();
  if( isset($_POST["usuario"]) ){
    try{     
      require("classPhp/conectaSqlServer.class.php");
      require("classPhp/validaJson.class.php"); 
      require("classPhp/removeAcento.class.php"); 

      $vldr     = new validaJSon();          
      $retorno  = "";
      $retCls   = $vldr->validarJs($_POST["usuario"]);
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
        //    Dados para JavaScript USUARIO //
        //////////////////////////////////////
        if( $rotina=="selectUsr" ){
          $sql="SELECT DISTINCT(A.USR_CODIGO)
                       ,A.USR_CPF
                       ,A.USR_APELIDO
                       ,A.USR_CODUP
                       ,P.UP_NOME
                       ,A.USR_CODCRG
                       ,C.CRG_NOME
                       ,A.USR_EMAIL
                       ,''
                       ,CASE WHEN A.USR_INTERNO='I' THEN CAST('INTERNO' AS VARCHAR(7)) 
                             WHEN A.USR_INTERNO='E' THEN CAST('EXTERNO' AS VARCHAR(7)) 
                             WHEN A.USR_INTERNO='D' THEN CAST('DEDICADO' AS VARCHAR(8)) END AS USR_INTERNO
                       ,CASE WHEN A.USR_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS USR_ATIVO
                       ,CASE WHEN A.USR_REG='P' THEN 'PUB' WHEN A.USR_REG='S' THEN 'SIS' ELSE 'ADM' END AS USR_REG
                       ,U.US_APELIDO
                       ,CASE WHEN A.USR_ADMPUB='P' THEN 'PUB' WHEN A.USR_ADMPUB='A' THEN 'ADM' WHEN A.USR_ADMPUB='S' THEN 'SIS' END AS USR_ADMPUB
                       ,A.USR_CODUSR
                  FROM USUARIO A
                  LEFT OUTER JOIN USUARIOSISTEMA U ON A.USR_CODUSR=U.US_CODIGO
                  INNER JOIN USUARIOPERFIL P ON A.USR_CODUP=P.UP_CODIGO";
                  if ($_SESSION['usr_grupoPerfil'] != "0") {
                     $sql.=" AND P.UP_GRUPO =".$_SESSION['usr_grupoPerfil'];
                  }
          $sql.=" LEFT OUTER JOIN CARGO C ON A.USR_CODCRG=C.CRG_CODIGO
                  INNER JOIN USUARIOUNIDADE UU ON A.USR_CODUSR = UU.UU_CODUSR
                 WHERE ((A.USR_ATIVO='".$lote[0]->ativo."') OR ('*'='".$lote[0]->ativo."'))";
          $classe->msgSelect(false);
          $retCls=$classe->select($sql);
          if( $retCls['retorno'] != "OK" ){
            $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';  
          } else { 
            $retorno='[{"retorno":"OK","dados":'.json_encode($retCls['dados']).',"erro":""}]'; 
          };  
        };
        if( $rotina=="buscarUnidade" ){
          $sql="";
          $sql.="SELECT U.UNI_CODIGO AS CODIGO, U.UNI_NOME AS DESCRICAO FROM UNIDADE U";
          $sql.=" LEFT OUTER JOIN USUARIOUNIDADE UU ON U.UNI_CODIGO=UU.UU_CODUNI AND UU_CODUSR =".$_SESSION['usr_codigo']." AND UU.ATIVO = 'S' GROUP BY U.UNI_CODIGO, U.UNI_NOME";
          $classe->msgSelect(false);
          $retCls=$classe->selectAssoc($sql);
          if( $retCls['retorno'] != "OK" ){
            $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';  
          } else { 
            $retorno='[{"retorno":"OK","dados":'.json_encode($retCls['dados']).',"erro":""}]'; 
          };  
        };
        if( $rotina=="selectUnidade" ){
          $sql="";
          $sql.= "SELECT A.UNI_CODIGO AS CODIGO,A.UNI_NOME AS DESCRICAO,A.UNI_APELIDO AS APELIDO";
          $sql.= "  FROM UNIDADE A ";
          $sql.= "  LEFT OUTER JOIN USUARIOUNIDADE UU ON A.UNI_CODIGO=UU.UU_CODUNI AND UU.UU_CODUSR=".$lote[0]->codUsr." AND UU.UU_ATIVO = 'S'";
          $sql.= "  LEFT OUTER JOIN USUARIOPERFIL UP ON A.UNI_CODGRP=UP.UP_GRUPO";
          if ($_SESSION['usr_grupoPerfil'] != "0") {
            $sql.=" WHERE UP.UP_GRUPO=".$_SESSION['usr_grupoPerfil'];
         }
          $sql.= "  GROUP BY A.UNI_CODIGO, A.UNI_NOME, A.UNI_APELIDO";
          $classe->msgSelect(false);
          $retCls=$classe->select($sql);
          if( $retCls['retorno'] != "OK" ){
            $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';  
          } else { 
            $retorno='[{"retorno":"OK","dados":'.json_encode($retCls['dados']).',"erro":""}]'; 
          };  
         };
         if( $rotina=="buscarUnidadesUsuario" ){
          $sql="";
          $sql.="SELECT U.UNI_CODIGO AS CODIGO, U.UNI_NOME AS DESCRICAO FROM USUARIOUNIDADE UU";
          $sql.=" INNER JOIN UNIDADE U ON U.UNI_CODIGO=UU_CODUNI AND UU_CODUSR =".$lote[0]->usuarioSelecionado." WHERE UU.UU_ATIVO = 'S'";
          $classe->msgSelect(false);
          $retCls=$classe->selectAssoc($sql);
          if( $retCls['retorno'] != "OK" ){
            $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';  
          } else { 
            $retorno='[{"retorno":"OK","dados":'.json_encode($retCls['dados']).',"erro":""}]'; 
          };  
        };
        if( $rotina=="insertUsuario" ){
          $arrUpdt = [];
          $sql="";
          $sql.="INSERT INTO USUARIO (
            USR_CPF,
            USR_APELIDO,
            USR_CODUP,
            USR_CODCRG,
            USR_EMAIL,
            USR_SENHA,
            USR_INTERNO,
            USR_ATIVO,
            USR_REG,
            USR_ADMPUB,
            USR_CODUSR) VALUES('"
            .$lote[0]->cpf."',UPPER('"
            .$lote[0]->apelido."'),"
            .$lote[0]->codup.",'"
            .$lote[0]->codcrg."','"
            .$lote[0]->email."','"
            .$lote[0]->senha."','"
            .$lote[0]->interno."','"
            .$lote[0]->ativo."','"
            .$lote[0]->reg."','"
            .$lote[0]->admpub."',"
            .$_SESSION['usr_codigo'].")";
          array_push($arrUpdt, $sql);
          $arrUnidades = explode("|",$lote[0]->unidades );
          foreach ($arrUnidades as $codigo) {
              $sql="INSERT INTO USUARIOUNIDADE (
                UU_CODUSR,
                UU_CODUNI,
                UU_ATIVO,
                UU_REG,
                SIS_CODUSR) VALUES ("
                .$lote[0]->usuarioCodigo.","
                .$codigo.","
                ."'S',"
                ."'P',"
                .$_SESSION['usr_codigo'].")";
              array_push($arrUpdt, $sql);
            }
          $retCls=$classe->cmd($arrUpdt);
          if( $retCls['retorno'] != "OK" ){
            $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';  
          } else { 
            $retorno='[{"retorno":"OK","dados":'.json_encode($retCls['dados']).',"erro":""}]'; 
          };  
        };
        if( $rotina=="updateUsuario" ){
          $arrUpdt = [];
          $sql="";
          $sql.="UPDATE USUARIO SET";
          $sql.=" USR_CPF ='".$lote[0]->cpf."',";
          $sql.=" USR_APELIDO = UPPER('".$lote[0]->apelido."'),";
          $sql.=" USR_CODUP = '".$lote[0]->codup."',";
          $sql.=" USR_CODCRG = '".$lote[0]->codcrg."',";
          $sql.=" USR_EMAIL = '".$lote[0]->email."',";
          $sql.=" USR_SENHA = '".$lote[0]->senha."',";
          $sql.=" USR_INTERNO = '".$lote[0]->interno."',";
          $sql.=" USR_ATIVO = '".$lote[0]->ativo."',";
          $sql.=" USR_REG = '".$lote[0]->reg."',";
          $sql.=" USR_ADMPUB = '".$lote[0]->admpub."',";
          $sql.=" USR_CODUSR = ".$_SESSION['usr_codigo'];
          $sql.=" WHERE USR_CODIGO = ".$lote[0]->usuarioCodigo;
          array_push($arrUpdt, $sql);
          $sql="DELETE FROM USUARIOUNIDADE WHERE UU_CODUSR=".$lote[0]->usuarioCodigo;
          array_push($arrUpdt, $sql);
          $arrUnidades = explode("|",$lote[0]->unidades );
          foreach ($arrUnidades as $codigo) {
              $sql="INSERT INTO USUARIOUNIDADE (
                UU_CODUSR,
                UU_CODUNI,
                UU_ATIVO,
                UU_REG,
                SIS_CODUSR) VALUES ("
                .$lote[0]->usuarioCodigo.","
                .$codigo.","
                ."'S',"
                ."'P',"
                .$_SESSION['usr_codigo'].")";
              array_push($arrUpdt, $sql);
            }
          $retCls=$classe->cmd($arrUpdt);
          if( $retCls['retorno'] != "OK" ){
            $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';  
          } else { 
            $retorno='[{"retorno":"OK","dados":'.json_encode($retCls['dados']).',"erro":""}]'; 
          };  
        };
        if( $rotina=="deleteUsuario" ){
          $arrUpdt = [];
          $sql="";
          $somasql="";
          $sql.="DELETE FROM USUARIO WHERE USR_CODIGO =".$lote[0]->usuarioCodigo;
          array_push($arrUpdt, $sql);
          $sql="DELETE FROM USUARIOUNIDADE WHERE UU_CODUSR =".$lote[0]->usuarioCodigo;
          array_push($arrUpdt, $sql); 
          $retCls=$classe->cmd($arrUpdt);
          if( $retCls['retorno'] != "OK"){
            $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';  
          } else { 
            $retorno='[{"retorno":"OK","dados":'.json_encode($retCls['dados']).',"erro":""}]'; 
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
                ////////////////////////////////////////////////////////
                // Sequencia obrigatoria em Ajuda pada campos padrões //
                ////////////////////////////////////////////////////////
                array_push($data,[ "0000"           /* 00 CODIGO */
                                  ,"00000000000"    /* 01 CPF */
                                  ,"APELIDO"        /* 02 */
                                  ,"PERFIL"         /* 03 */
                                  ,"CARGO"          /* 04 */                                  
                                  ,"EMAIL"          /* 05 */
                                  ,"SENHA"          /* 06 */
                                  ,"*"              /* 07 INTERNO */
                                  ,"XXX"            /* 08 ADMPUB */                                  
                                  ,"LINHA 1 DEVE SER ".$lote[0]->cabec]);
                $strExcel   = "N";
              };
            } else {
              $erro="OK";
              
              $linC = -1;
              foreach($cells as $cell){
                $linC++;
                switch( $linC ){
                  ////////////////
                  // USR_CODIGO //
                  ////////////////
                  case 0:                  
                    $codigo=preg_replace('/[^0-9]/', '', trim($cell->nodeValue));
                    if( strlen(trim($codigo)) == 0 ){ 
                      $erro     = "CAMPO CODIGO DEVE TER UM INTEIRO VALIDO";
                      $strExcel = "N";
                    } else {
                      $codigo=str_pad($codigo, 4, "0", STR_PAD_LEFT);
                    };  
                    break;
                  ////////////////
                  //   USR_CPF  //
                  ////////////////
                  case 1: 
                    $cpf=preg_replace('/[^0-9]/', '', trim($cell->nodeValue));
                    if( (strlen($cpf)<1) or (strlen($cpf)>11) ){ 
                      $erro     = "CAMPO CPF DEVE TER TAMANHO 01..11";
                      $strExcel = "N";
                    };
                    $achei=false;
                    foreach( $arrDup as $lin ){
                      if( $lin["DESCRICAO"]==$cpf ){
                        $erro     = "CPF DUPLICADO NA PLANILHA";
                        $strExcel = "N";
                        $achei    = true;
                        break;
                      };
                    };
                    if( $achei==false ){
                      array_push($arrDup,[
                        "DESCRICAO"=>$cpf
                      ]);
                    };    
                    break;
                  /////////////////
                  // USR_APELIDO //
                  /////////////////
                  case 2: 
                    $clsRa->montaRetorno($cell->nodeValue);
                    $apelido=$clsRa->getNome();
                    if( (strlen($apelido)<1) or (strlen($apelido)>15) ){ 
                      $erro     = "CAMPO APELIDO DEVE TER TAMANHO 01..15";
                      $strExcel = "N";
                    };
                    $achei=false;
                    foreach( $arrDup as $lin ){
                      if( $lin["DESCRICAO"]==$apelido ){
                        $erro     = "APELIDO DUPLICADO NA PLANILHA";
                        $strExcel = "N";
                        $achei    = true;
                        break;
                      };
                    };
                    if( $achei==false ){
                      array_push($arrDup,[
                        "DESCRICAO"=>$apelido
                      ]);
                    };    
                    break;
                  ////////////////
                  //  USR_CODUP //
                  ////////////////
                  case 3:                  
                    $codup=preg_replace('/[^0-9]/', '', trim($cell->nodeValue));
                    if( strlen(trim($codup)) <= 0 ){ 
                      $erro     = "CAMPO CODIGO PERFIL DEVE TER UM INTEIRO VALIDO";
                      $strExcel = "N";
                    } else {
                      $codup=str_pad($codup, 4, "0", STR_PAD_LEFT);
                    };  
                    break;
                  ////////////////
                  // USR_CODCRG //
                  ////////////////
                  case 4: 
                    $clsRa->montaRetorno($cell->nodeValue);
                    $codcrg=$clsRa->getNome();
                    if( (strlen($codcrg)<1) or (strlen($codcrg)>5) ){ 
                      $erro     = "CAMPO cargo DEVE TER TAMANHO 01..05";
                      $strExcel = "N";
                    };
                    break;
                  ////////////////
                  // USR_EMAIL  //
                  ////////////////
                  case 5: 
                    $clsRa->montaRetorno($cell->nodeValue);
                    $email=strtolower($clsRa->getNome());
                    if( (strlen($email)<1) or (strlen($email)>60) ){ 
                      $erro     = "CAMPO EMAIL DEVE TER TAMANHO 01..60";
                      $strExcel = "N";
                    };
                    break;
                  ////////////////
                  // USR_SENHA  //
                  ////////////////
                  case 6: 
                    $clsRa->montaRetorno($cell->nodeValue);
                    $senha=$clsRa->getNome();
                    if( (strlen($senha)<1) or (strlen($senha)>15) ){ 
                      $erro     = "CAMPO SENHA DEVE TER TAMANHO 01..15";
                      $strExcel = "N";
                    };
                    break;
                  //////////////////
                  // USR_INTERNO  //
                  //////////////////
                  case 7: 
                    $clsRa->montaRetorno($cell->nodeValue);
                    $interno=$clsRa->getNome();
                    if( !preg_match("/^(D|E|I)$/",$interno) ){
                      $erro     = "CAMPO INTERNO ACEITA APENAS D/E/I";
                      $strExcel = "N";
                    }  
                    break;
                  ////////////////
                  // USR_ADMPUB //
                  ////////////////
                  case 8: 
                    $clsRa->montaRetorno($cell->nodeValue);
                    $admpub=$clsRa->getNome();
                    if( (strlen($admpub)<1) or (strlen($admpub)>3) ){ 
                      $erro     = "CAMPO ADMPUB DEVE TER TAMANHO 01..3";
                      $strExcel = "N";
                    };
                    if( !preg_match("/^(PUB|ADM)$/",$admpub) ){
                      $erro     = "CAMPO ADMPUB ACEITA APENAS PUB/ADM";
                      $strExcel = "N";
                    }  
                    break;
                };
              };
              ////////////////////////////////////////////////////////
              // Montando o vetor para retornar ao JSON             //
              // Sequencia obrigatoria em Ajuda pada campos padrões //
              ////////////////////////////////////////////////////////
              array_push($data,[$codigo,$cpf,$apelido,$codup,$codcrg,$email,$senha,$interno,$admpub,$erro]);
              /////////////////////////////////////////////////////
              // Guardando os inserts se não existir nenhum erro //
              /////////////////////////////////////////////////////
              $sql="INSERT INTO USUARIO("          
                ."USR_CPF"
                .",USR_APELIDO"
                .",USR_CODUP"
                .",USR_CODCRG"
                .",USR_EMAIL"
                .",USR_SENHA"
                .",USR_INTERNO"
                .",USR_ADMPUB"
                .",USR_REG"
                .",USR_CODUSR"
                .",USR_ATIVO) VALUES("
                ."'".$cpf."'"                 // USR_CPF
                .",'".$apelido."'"            // USR_APELIDO
                .",".$codup                   // USR_CODUP
                .",'".$codcrg."'"             // USR_CODCRG
                .",'".$email."'"              // USR_EMAIL
                .",'".$senha."'"              // USR_SENHA
                .",'".$interno."'"            // USR_INTERNO
                .",'".$admpub."'"             // USR_ADMPUB
                .",'P'"                       // USR_REG
                .",".$_SESSION["usr_codigo"]  // USR_CODUSR
                .",'S'"                       // USR_ATIVO
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
    <title>Usuário</title>
    <link rel="stylesheet" href="css/css2017.css">
    <link rel="stylesheet" href="css/cssTable2017.css">
    <link rel="stylesheet" href="css/cssFaTable.css">
    <script src="js/js2017.js"></script>
    <script src="js/jsTable2017.js"></script>
    <script src="tabelaTrac/f10/tabelaUsuarioPerfilF10.js"></script>
    <script src="tabelaTrac/f10/tabelaCargoF10.js"></script>
    <script src="tabelaTrac/f10/tabelaUnidadeMultipleF10.js"></script>
    <script language="javascript" type="text/javascript"></script>
    <script>
      "use strict";
      ////////////////////////////////////////////////
      // Executar o codigo após a pagina carregada  //
      ////////////////////////////////////////////////
      document.addEventListener("DOMContentLoaded", function(){ 
        ////////////////////////////////////
        //   Objeto clsTable2017 USUARIO  //
        ////////////////////////////////////
        jsUsr={
          "titulo":[
             {"id":0  ,"labelCol":"OPC"     
                      ,"padrao":1}            
            ,{"id":1  ,"field"          :"USR_CODIGO" 
                      ,"labelCol"       : "CODIGO"
                      ,"obj"            : "edtCodigo"
                      ,"tamGrd"         : "0em"
                      ,"tamImp"         : "15"
                      ,"fieldType"      : "int"
                      ,"align"          : "center"                                      
                      ,"pk"             : "S"
                      ,"newRecord"      : ["0000","this","this"]
                      ,"insUpDel"       : ["N","N","N"]
                      ,"formato"        : ["i4"]
                      ,"validar"        : ["notnull","intMaiorZero"]
                      ,"autoIncremento" : "S"
                      ,"ajudaCampo"     : [  "Codigo do Usuario. Gerado pelo sistema é único e tem o formato 9999"
                                            ,"Campo deve ser utilizado no cadastro de Cliente/Operacao"]
                      ,"importaExcel"   : "S"                                                                
                      ,"padrao":0}
            ,{"id":2  ,"field"          : "USR_CPF"   
                      ,"labelCol"       : "CPF"
                      ,"obj"            : "edtCpf"
                      ,"tamGrd"         : "10em"
                      ,"tamImp"         : "30"
                      ,"digitosValidos" : "0|1|2|3|4|5|6|7|8|9"
                      ,"digitosMinMax"  : [11,11]
                      ,"ajudaCampo"     : ["CPF do usuário."]
                      ,"importaExcel"   : "S"                                          
                      ,"padrao":0}
            ,{"id":3  ,"field"          : "USR_APELIDO"   
                      ,"labelCol"       : "APELIDO"
                      ,"obj"            : "edtApelido"
                      ,"tamGrd"         : "10em"
                      ,"tamImp"         : "30"
                      ,"digitosValidos" : "A|B|C|D|E|F|G|H|I|J|K|L|M|N|O|P|Q|R|S|T|U|V|W|X|Y|Z| "
                      ,"digitosMinMax"  : [5,15]
                      ,"ajudaCampo"     : ["Nome do usuário."]
                      ,"importaExcel"   : "S"                                          
                      ,"padrao":0}
            ,{"id":4  ,"field"          :"USR_CODUP" 
                      ,"labelCol"       : "CODPER"
                      ,"obj"            : "edtCodUp"
                      ,"tamGrd"         : "0em"
                      ,"tamImp"         : "0"
                      ,"fieldType"      : "int"
                      ,"align"          : "center"                                      
                      ,"newRecord"      : ["0000","this","this"]
                      ,"formato"        : ["i4"]
                      ,"validar"        : ["notnull","intMaiorZero"]
                      ,"ajudaCampo"     : [  "Codigo do Perfil. Registro deve existir na tabela de perfil e tem o formato 9999"
                                            ,"A checagem de cada rotina é relacionada com esta informação"]
                      ,"importaExcel"   : "S"                                                                
                      ,"padrao":0}
            ,{"id":5  ,"field"          : "UP_NOME"   
                      ,"insUpDel"       : ["N","N","N"]
                      ,"labelCol"       : "PERFIL"
                      ,"obj"            : "edtDesUp"
                      ,"tamGrd"         : "10em"
                      ,"tamImp"         : "30"
                      ,"digitosMinMax"  : [1,15]
                      ,"validar"        : ["notnull"]                      
                      ,"ajudaCampo"     : ["Descrição do perfil para este usuário."]
                      ,"padrao":0}
                      
            ,{"id":6  ,"field"          :"USR_CODCRG" 
                      ,"labelCol"       : "CODCRG"
                      ,"obj"            : "edtCodCrg"
                      ,"tamGrd"         : "0em"
                      ,"tamImp"         : "0"
                      ,"digitosValidos" : "A|B|C|D|E|F|G|H|I|J|K|L|M|N|O|P|Q|R|S|T|U|V|W|X|Y|Z"
                      ,"newRecord"      : ["","this","this"]
                      ,"validar"        : ["notnull"]
                      ,"ajudaCampo"     : [  "Codigo do Cargo. Registro deve existir na tabela de cargo e tem o formato AAAAA"
                                            ,"A checagem de cada rotina é relacionada com esta informação"]
                      ,"importaExcel"   : "S"                                                                
                      ,"padrao":0}
            ,{"id":7  ,"field"          : "CRG_NOME"   
                      ,"insUpDel"       : ["N","N","N"]
                      ,"labelCol"       : "CARGO"
                      ,"obj"            : "edtDesCrg"
                      ,"tamGrd"         : "10em"
                      ,"tamImp"         : "30"
                      ,"digitosMinMax"  : [1,20]
                      ,"validar"        : ["notnull"]                      
                      ,"ajudaCampo"     : ["Descrição do cargo para este usuário."]
                      ,"padrao":0}
            ,{"id":8  ,"field"          : "USR_EMAIL"   
                      ,"labelCol"       : "EMAIL"
                      ,"obj"            : "edtEmail"
                      ,"tamGrd"         : "26em"
                      ,"tamImp"         : "60"
                      ,"digitosValidos" : "a|b|c|d|e|f|g|h|i|j|k|l|m|n|o|p|q|r|s|t|u|v|w|x|y|z|0|1|2|3|4|5|6|7|8|9|@|_|.|-"
                      ,"digitosMinMax"  : [5,60]
                      ,"formato"        : ["lowercase","removeacentos","tiraaspas","alltrim"]
                      ,"ajudaCampo"     : ["Email do usuário para envio automatico de mensagens."]
                      ,"importaExcel"   : "S"                                          
                      ,"padrao":0}
            ,{"id":9  ,"field"          : "USR_SENHA"   
                      ,"labelCol"       : "SENHA"
                      ,"obj"            : "edtSenha"
                      ,"digitosValidos" : "A|B|C|D|E|F|G|H|I|J|K|L|M|N|O|P|Q|R|S|T|U|V|W|X|Y|Z|0|1|2|3|4|5|6|7|8|9|"
                      ,"digitosMinMax"  : [5,15]
                      ,"ajudaCampo"     : [ "Senha do usuario com no maximo 15 caracteres."
                                           ,"Após cadastro deve ser informado ao usuário para alterar esta informação"]
                      ,"importaExcel"   : "S"                                          
                      ,"padrao":0}
            ,{"id":10 ,"field"          : "USR_INTERNO"  
                      ,"labelCol"       : "TIPO"   
                      ,"labelColImp"    : "INT" 
                      ,"insUpDel"      : ["S","S","N"]         
                      ,"newRecord"      : ["I","this","this"]                      
                      ,"obj"            : "cbInterno"
                      ,"tipo"           : "cb"
                      ,"tamGrd"         : "7em"
                      ,"tamImp"         : "10"
                      ,"ajudaCampo"     : [ "Interno é quando funcionario trac."
                                           ,"Se interno=S tem direito a todas empresas"]
                      ,"importaExcel"   : "S"                                           
                      ,"padrao":0}                                        
            ,{"id":11 ,"field"          : "USR_ATIVO"  
                      ,"labelCol"       : "ATIVO"   
                      ,"obj"            : "cbAtivo"
                      ,"tamImp"         : "10"
                      ,"padrao":2}                                        
            ,{"id":12 ,"field"          : "USR_REG"    
                      ,"labelCol"       : "REG"     
                      ,"obj"            : "cbReg"      
                      ,"tamImp"         : "10"
                      ,"lblDetalhe"     : "REGISTRO"     
                      ,"ajudaDetalhe"   : "Se o registro é PUBlico/ADMinistrador ou do SIStema"                                         
                      ,"padrao":3}  
            ,{"id":13 ,"field"          : "US_APELIDO" 
                      ,"labelCol"       : "USUARIO" 
                      ,"obj"            : "edtUsuario" 
                      ,"padrao":4} 
            ,{"id":14 ,"field"          : "USR_ADMPUB"    
                      ,"labelCol"       : "PA"     
                      ,"obj"            : "cbAdmPub"  
                      ,"newRecord"      : ["P","this","this"]
                      ,"tamGrd"         : "4em"                      
                      ,"tamImp"         : "10"
                      ,"lblDetalhe"     : "PUBLICO/ADMIN" 
                      ,"newRecord"      : ["P","this","this"]                      
                      ,"ajudaCampo"     : [  "Flag para descrever se usuário tem direito Público ou de Administrador"
                                            ,"Este flag esta relacionado com todos os registros <b>individuais</b> do sistema"]
                      ,"ajudaDetalhe"   : "Se o usuario é PUBlico/ADMinistrador"       
                      ,"importaExcel"   : "S"                                                                
                      ,"padrao":3}  
            ,{"id":15 ,"field"          : "USR_CODUSR" 
                      ,"labelCol"       : "CODUSU"  
                      ,"obj"            : "edtCodUsu"  
                      ,"padrao":5}                                      
            ,{"id":16 ,"labelCol"       : "PP"      
                      ,"obj"            : "imgPP"        
                      ,"func":"var elTr=this.parentNode.parentNode;"
                        +"elTr.cells[0].childNodes[0].checked=true;"
                        +"objUsr.espiao();"
                        +"elTr.cells[0].childNodes[0].checked=false;"
                      ,"padrao":8}
          ]
          ,
          "detalheRegistro":
          [
            { "width"           :"100%"
              ,"height"         :"400px" 
              ,"label"          :"USUARIO - Detalhe do registro"
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
          ,"idBtnConfirmarCustom" : "idBtnConfirmarCustom"            // Se existir executa o confirmar do form/fieldSet
          ,"idBtnCancelar"  : "btnCancelar"         // Se existir executa o cancelar do form/fieldSet
          ,"div"            : "frmUsr"              // Onde vai ser gerado a table
          ,"divFieldSet"    : "tabelaUsr"           // Para fechar a div onde estão os fieldset ao cadastrar
          ,"form"           : "frmUsr"              // Onde vai ser gerado o fieldSet       
          ,"divModal"       : "divTopoInicio"       // Onde vai se appendado abaixo deste a table 
          ,"tbl"            : "tblUsr"              // Nome da table
          ,"prefixo"        : "usr"                 // Prefixo para elementos do HTML em jsTable2017.js
          ,"tabelaBD"       : "VUSUARIO"            // Nome da tabela no banco de dados  
          ,"tabelaBKP"      : "BKPUSUARIO"          // Nome da tabela no banco de dados  
          ,"fieldAtivo"     : "USR_ATIVO"           // SE EXISITIR - Nome do campo ATIVO(S/N) na tabela BD
          ,"fieldReg"       : "USR_REG"             // SE EXISITIR - Nome do campo SYS(P/A) na tabela BD            
          ,"fieldCodUsu"    : "USR_CODUSR"          // SE EXISITIR - Nome do campo CODIGO USUARIO na tabela BD                        
          ,"iFrame"         : "iframeCorpo"         // Se a table vai ficar dentro de uma tag iFrame
          //,"fieldCodEmp"  : "*"                   // SE EXISITIR - Nome do campo CODIGO EMPRESA na tabela BD            
          //,"fieldCodDir"  : "*"                   // SE EXISITIR - Nome do campo CODIGO DIREITO na tabela BD                        
          ,"width"          : "106em"               // Tamanho da table
          ,"height"         : "58em"                // Altura da table
          ,"tableLeft"      : "sim"                 // Se tiver menu esquerdo
          ,"relTitulo"      : "USUARIO"             // Titulo do relatório
          ,"relOrientacao"  : "P"                   // Paisagem ou retrato
          ,"relFonte"       : "8"                   // Fonte do relatório
          ,"foco"           : ["edtCpf"
                              ,"edtCpf"
                              ,"idBtnConfirmarCustom"]      // Foco qdo Cad/Alt/Exc
          ,"formPassoPasso" : "Trac_Espiao.php"     // Enderço da pagina PASSO A PASSO
          ,"indiceTable"    : "CPF"           // Indice inicial da table
          ,"tamBotao"       : "15"                  // Tamanho botoes defalt 12 [12/25/50/75/100]
          ,"tamMenuTable"   : ["10em","20em"]                                
          ,"labelMenuTable" : "Opções"              // Caption para menu table 
          ,"_menuTable"     :[
                                ["Regitros ativos"                        ,"fa-thumbs-o-up"   ,"btnFiltrarClick('S');"]
                               ,["Registros inativos"                     ,"fa-thumbs-o-down" ,"btnFiltrarClick('N');"]
                               ,["Todos"                                  ,"fa-folder-open"   ,"btnFiltrarClick('*');"]
                               ,["Importar planilha excel"                ,"fa-file-excel-o"  ,"fExcel()"]
                               ,["Imprimir registros em tela"             ,"fa-print"         ,"objUsr.imprimir()"]
                               ,["Ajuda para campos padrões"              ,"fa-info"          ,"objUsr.AjudaSisAtivo(jsUsr);"]
                               ,["Detalhe do registro"                    ,"fa-folder-o"      ,"objUsr.detalhe();"]
                               ,["Gerar excel"                            ,"fa-file-excel-o"  ,"objUsr.excel();"]
                               ,["Passo a passo do registro"              ,"fa-binoculars"    ,"objUsr.espiao();"]
                               ,["Alterar status Ativo/Inativo"           ,"fa-share"         ,"objUsr.altAtivo(intCodDir);"]
                               ,["Alterar registro PUBlico/ADMinistrador" ,"fa-reply"         ,"objUsr.altPubAdm(intCodDir,jsPub[0].usr_admpub);"]
                               ,["Alterar para registro do SISTEMA"       ,"fa-reply"         ,"objUsr.altRegSistema("+jsPub[0].usr_d05+");"]
                               ,["Atualizar grade consulta"               ,"fa-filter"        ,"btnFiltrarClick('S');"]                               
                             ]  
          ,"codTblUsu"      : "USUARIOS[01]"                          
          ,"codDir"         : intCodDir
        }; 
        if( objUsr === undefined ){  
          objUsr=new clsTable2017("objUsr", true);
        };  
        objUsr.montarHtmlCE2017(jsUsr); 
        ////////////////////////////////////////
        //    Fim objeto clsTable2017 USUARIO //
        //////////////////////////////////////// 
        //
        //
        ////////////////////////////////////////////////////////
        // Montando a table para importar xls                 //
        // Sequencia obrigatoria em Ajuda pada campos padrões //
        ////////////////////////////////////////////////////////
        jsExc={
          "titulo":[
             {"id":0  ,"field":"CODIGO"     ,"labelCol":"CODIGO"    ,"tamGrd":"6em"  ,"tamImp":"20","align":"center"}
            ,{"id":1  ,"field":"CPF"        ,"labelCol":"CPF"       ,"tamGrd":"10em" ,"tamImp":"30"}
            ,{"id":2  ,"field":"APELIDO"    ,"labelCol":"APELIDO"   ,"tamGrd":"10em" ,"tamImp":"30"}
            ,{"id":3  ,"field":"CODPER"     ,"labelCol":"CODPERFIL" ,"tamGrd":"2em"  ,"tamImp":"10"}
            ,{"id":4  ,"field":"CARGO"      ,"labelCol":"CARGO"     ,"tamGrd":"10em" ,"tamImp":"30"}            
            ,{"id":5  ,"field":"EMAIL"      ,"labelCol":"EMAIL"     ,"tamGrd":"40em" ,"tamImp":"80"}
            ,{"id":6  ,"field":"SENHA"      ,"labelCol":"SENHA"     ,"tamGrd":"10em" ,"tamImp":"30"}
            ,{"id":7  ,"field":"INTERNO"    ,"labelCol":"INTERNO"   ,"tamGrd":"7em"  ,"tamImp":"25"}
            ,{"id":8  ,"field":"ADMPUB"     ,"labelCol":"ADMPUB"    ,"tamGrd":"10em" ,"tamImp":"30"}
            ,{"id":9  ,"field":"ERRO"       ,"labelCol":"ERRO"      ,"tamGrd":"60em" ,"tamImp":"100"}
          ]
          ,"botoesH":[
             {"texto":"Imprimir"  ,"name":"excImprimir"   ,"onClick":"3"  ,"enabled":true ,"imagem":"fa fa-print"            ,"ajuda":"Imprimir registros em tela" }        
            ,{"texto":"Excel"     ,"name":"excExcel"      ,"onClick":"5"  ,"enabled":false,"imagem":"fa fa-file-excel-o"     ,"ajuda":"Exportar para excel" }        
            ,{"texto":"Fechar"    ,"name":"excFechar"     ,"onClick":"7"  ,"enabled":true ,"imagem":"fa fa-close"            ,"ajuda":"Fechar formulario" }
          ] 
          ,"registros"      : []                    // Recebe um Json vindo da classe clsBancoDados
          ,"corLinha"       : "if(ceTr.cells[9].innerHTML !='OK') {ceTr.style.color='yellow';ceTr.style.backgroundColor='red';}"      
          ,"checarTags"     : "N"                   // Somente em tempo de desenvolvimento(olha as pricipais tags)                                
          ,"div"            : "frmExc"              // Onde vai ser gerado a table
          ,"divFieldSet"    : "tabelaExc"           // Para fechar a div onde estão os fieldset ao cadastrar
          ,"form"           : "frmExc"              // Onde vai ser gerado o fieldSet                     
          ,"divModal"       : "divTopoInicioE"      // Nome da div que vai fazer o show modal
          ,"tbl"            : "tblExc"              // Nome da table
          ,"prefixo"        : "exc"                 // Prefixo para elementos do HTML em jsTable2017.js
          ,"tabelaBD"       : "*"                   // Nome da tabela no banco de dados  
          ,"width"          : "100em"               // Tamanho da table
          ,"height"         : "48em"                // Altura da table
          ,"tableLeft"      : "sim"                 // Se tiver menu esquerdo
          ,"relTitulo"      : "Importação Usuario"  // Titulo do relatório
          ,"relOrientacao"  : "P"                   // Paisagem ou retrato
          ,"indiceTable"    : "TAG"                 // Indice inicial da table
          ,"relFonte"       : "8"                   // Fonte do relatório
          ,"formName"       : "frmExc"              // Nome do formulario para opção de impressão 
          ,"tamBotao"       : "15"                  // Tamanho botoes defalt 12 [12/25/50/75/100]
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
      var objUsr;                     // Obrigatório para instanciar o JS Usuario
      var jsUsr;                      // Obj principal da classe clsTable2017
      var objExc;                     // Obrigatório para instanciar o JS Importar excel
      var jsExc;                      // Obrigatório para instanciar o objeto objExc
      var objUpF10;                   // Obrigatório para instanciar o JS UsuarioPerfilF10          
      var objCrgF10;                  // Obrigatório para instanciar o JS CargoF10          
      var objUniF10;                  // Obrigatório para instanciar o JS UnidadeF10          
      var dadosUnidade = [];          // Unidades escolhidas no multiple select
      var clsJs;                      // Classe responsavel por montar um Json e eviar PHP
      var clsErro;                    // Classe para erros            
      var fd;                         // Formulario para envio de dados para o PHP
      var msg;                        // Variavel para guardadar mensagens de retorno/erro 
      var retPhp                      // Retorno do Php para a rotina chamadora
      var contMsg   = 0;              // contador para mensagens
      var cmp       = new clsCampo(); // Abrindo a classe campos
      var jsPub     = JSON.parse(localStorage.getItem("lsPublico"));
      var intCodDir = parseInt(jsPub[0].usr_d01);
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
        clsJs.add("rotina"      , "selectUsr"         );
        clsJs.add("login"       , jsPub[0].usr_login  );
        clsJs.add("ativo"       , atv                 );
        fd = new FormData();
        fd.append("usuario" , clsJs.fim());
        msg     = requestPedido("Trac_Usuario.php",fd); 
        retPhp  = JSON.parse(msg);
        if( retPhp[0].retorno == "OK" ){
          //////////////////////////////////////////////////////////////////////////////////
          // O novo array não tem o campo idUnico mas a montarHtmlCE2017 ja foi executada //
          // Campo obrigatório se existir rotina de manutenção na table devido Json       //
          // Esta rotina não tem manutenção via classe clsTable2017                       //
          // jsCrv.registros=objCrv.addIdUnico(retPhp[0]["dados"]);                       //
          //////////////////////////////////////////////////////////////////////////////////
          jsUsr.registros=objUsr.addIdUnico(retPhp[0]["dados"]);
          objUsr.ordenaJSon(jsUsr.indiceTable,false);  
          objUsr.montarBody2017();
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
          clsJs.add("rotina"      , "impExcel"                                                          );
          clsJs.add("login"       , jsPub[0].usr_login                                                  );
          clsJs.add("cabec"       , "CODIGO|CPF|APELIDO|CODPER|CODCRG|EMAIL|SENHA|INTERNO|PA"  );          
          fd = new FormData();
          fd.append("usuario"    , clsJs.fim());
          fd.append("arquivo"   , edtArquivo.files[0] );
          msg     = requestPedido("Trac_Usuario.php",fd); 
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
      function verificaTipoInstrucao(status) {
      if (status == 0) {
        // inserindo
        salvarDados();
      } else if (status == 1) {
        //update
        updateDados();
      } else if (status == 2){
        //delete
        deleteDados();
      }
    }
    function limparCampos(){
        document.getElementById('unidadeSelect').innerHTML = '';
      }
    function preencherSelect(){
        clsJs   = jsString("lote");  
        clsJs.add("rotina", "buscarUnidadesUsuario");
        clsJs.add("login", jsPub[0].usr_login  );
        clsJs.add("usuarioSelecionado", document.getElementById('edtCodigo').value);
        fd = new FormData();
        fd.append("usuario" , clsJs.fim());
        msg     = requestPedido("Trac_Usuario.php",fd); 
        retPhp  = JSON.parse(msg);
        dadosUnidade = retPhp[0]["dados"];
        document.getElementById('unidadeSelect').innerHTML = '';
        dadosUnidade.forEach(element => {
          var campo = document.createElement('option')
          campo.value = element['CODIGO'];
          campo.label = element['DESCRICAO'];
          document.getElementById('unidadeSelect').appendChild(campo);
      });
    }

    function updateDados() {
      clsJs   = jsString("lote");
      clsJs.add("rotina", "updateUsuario");
      clsJs.add("login", jsPub[0].usr_login  );
      clsJs.add("usrCodigo", jsPub[0].usr_codigo  );
      clsJs.add("usuarioCodigo", document.getElementById('edtCodigo').value);   
      clsJs.add("cpf", document.getElementById('edtCpf').value);   
      clsJs.add("apelido", document.getElementById('edtApelido').value);   
      clsJs.add("codup", document.getElementById('edtCodUp').value);   
      clsJs.add("codcrg", document.getElementById('edtCodCrg').value);   
      clsJs.add("email", document.getElementById('edtEmail').value);   
      clsJs.add("senha", document.getElementById('edtSenha').value);   
      clsJs.add("interno", document.getElementById('cbInterno').value);   
      clsJs.add("ativo", document.getElementById('cbAtivo').value);   
      clsJs.add("reg", document.getElementById('cbReg').value);   
      clsJs.add("admpub", document.getElementById('cbAdmPub').value); 
      let unidadesString = "";
      dadosUnidade.forEach(element => {
        unidadesString+=(element['CODIGO']+"|"); 
      });
      unidadesString = unidadesString.substring(0, unidadesString.length - 1)
  
      clsJs.add("unidades", unidadesString);
      fd = new FormData();
      fd.append("usuario" , clsJs.fim());
      msg     = requestPedido("Trac_Usuario.php",fd); 
      retPhp  = JSON.parse(msg);
      document.getElementById('unidadeSelect').innerHTML = '';
    }
    function deleteDados() {
      clsJs   = jsString("lote");  
      clsJs.add("rotina"      , "deleteUsuario"         );
      clsJs.add("login"       , jsPub[0].usr_login  );
      clsJs.add("usrCodigo"       , jsPub[0].usr_codigo  );
      clsJs.add("usuarioCodigo", document.getElementById('edtCodigo').value);   
      fd = new FormData();
      fd.append("usuario" , clsJs.fim());
      msg     = requestPedido("Trac_Usuario.php",fd); 
      retPhp  = JSON.parse(msg);
      document.getElementById('unidadeSelect').innerHTML = '';
    }
    
    function salvarDados() {
      clsJs   = jsString("lote");
      clsJs.add("rotina"      , "insertUsuario"         );
      clsJs.add("login"       , jsPub[0].usr_login  );
      clsJs.add("usrCodigo"       , jsPub[0].usr_codigo  );
      clsJs.add("usuarioCodigo", document.getElementById('edtCodigo').value);   
      clsJs.add("cpf", document.getElementById('edtCpf').value);   
      clsJs.add("apelido", document.getElementById('edtApelido').value);   
      clsJs.add("codup", document.getElementById('edtCodUp').value);   
      clsJs.add("codcrg", document.getElementById('edtCodCrg').value);   
      clsJs.add("email", document.getElementById('edtEmail').value);   
      clsJs.add("senha", document.getElementById('edtSenha').value);   
      clsJs.add("interno", document.getElementById('cbInterno').value);   
      clsJs.add("ativo", document.getElementById('cbAtivo').value);   
      clsJs.add("reg", document.getElementById('cbReg').value);   
      clsJs.add("admpub", document.getElementById('cbAdmPub').value); 
      let unidadesString = "";       
      dadosUnidade.forEach(element => {
        unidadesString+=(element['CODIGO']+"|"); 
      });
      unidadesString = unidadesString.substring(0, unidadesString.length - 1)

      clsJs.add("unidades", unidadesString);          
      fd = new FormData();
      fd.append("usuario" , clsJs.fim());
      msg     = requestPedido("Trac_Usuario.php",fd); 
      retPhp  = JSON.parse(msg);
      document.getElementById('unidadeSelect').innerHTML = '';
    }
    function uniF10Click(){
        fUnidadeF10("Trac_Usuario.php", "usuario");
    };
      function gpoF10Click(){ fGrupoOperacionalF10("cbAtivo"); };
      function RetF10tblUni(arr){
        document.getElementById('unidadeSelect').innerHTML = '';
        dadosUnidade = arr;
        arr.forEach(element => {
          var campo = document.createElement('option')
          campo.value = element.CODIGO;
          campo.label = element.DESCRICAO;
          document.getElementById('unidadeSelect').appendChild(campo);
        });
      };
      ///////////////////////
      // AJUDA PARA PERFIL //
      ///////////////////////
      function upFocus(obj){ 
        document.getElementById(obj.id).setAttribute("data-oldvalue",document.getElementById(obj.id).value); 
      };
      function upF10Click(){ fUsuarioPerfilF10(0,"edtCodUp","edtCodCrg"); };  
      function RetF10tblUp(arr){
        document.getElementById("edtCodUp").value     = arr[0].CODIGO;
        document.getElementById("edtDesUp").value    = arr[0].DESCRICAO;
        document.getElementById("edtCodUp").setAttribute("data-oldvalue",arr[0].CODIGO);
      };
      function codUpBlur(obj){
        var elOld = jsNmrs(document.getElementById(obj.id).getAttribute("data-oldvalue")).inteiro().ret();
        var elNew = jsNmrs(obj.id).inteiro().ret();
        if( elOld != elNew ){
          var ret = fUsuarioPerfilF10(1,obj.id,"edtCodCrg");
          document.getElementById(obj.id).value         = ( ret.length == 0 ? "0000"    : jsNmrs(ret[0].CODIGO).emZero(4).ret() );
          document.getElementById("edtDesUp").value     = ( ret.length == 0 ? ""        : ret[0].DESCRICAO                      );
          document.getElementById(obj.id).setAttribute("data-oldvalue",( ret.length == 0 ? "0000" : ret[0].CODIGO )             );
        };
      };
      ////////////////////////
      //  AJUDA PARA CARGO  //
      ////////////////////////
      function crgFocus(obj){ 
        document.getElementById(obj.id).setAttribute("data-oldvalue",document.getElementById(obj.id).value); 
      };
      function crgF10Click(){ fCargoF10(0,"edtCodCrg","cbAdmPub"); };  
      function RetF10tblCrg(arr){
        document.getElementById("edtCodCrg").value    = arr[0].CODIGO;
        document.getElementById("edtDesCrg").value    = arr[0].DESCRICAO;
        document.getElementById("edtCodCrg").setAttribute("data-oldvalue",arr[0].CODIGO);
      };
      function codCrgBlur(obj){
        var elOld = document.getElementById(obj.id).getAttribute("data-oldvalue");
        var elNew = obj.value;
        if( elOld != elNew ){
          var ret = fCargoF10(1,obj.id,"cbAdmPub");
          document.getElementById(obj.id).value          = ( ret.length == 0 ? ""    : ret[0].CODIGO            );
          document.getElementById("edtDesCrg").value     = ( ret.length == 0 ? ""    : ret[0].DESCRICAO         );
          document.getElementById(obj.id).setAttribute("data-oldvalue",( ret.length == 0 ? "" : ret[0].CODIGO ) );
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
              name="frmUsr" 
              id="frmUsr" 
              class="frmTable" 
              action="classPhp/imprimirsql.php" 
              target="_newpage"
              style="top: 5em; width:90em; height:40m; position: absolute; z-index:30;display:none;">
          <p class="frmCampoTit">
          <input class="informe" type="text" name="titulo" value="Usuario" disabled="" style="color: white; text-align: left;">
          </p>
          <div style="height: 48em; overflow-y: auto;">
            <input type="hidden" id="sql" name="sql"/>
            <div class="campotexto campo100">
              <div class="campotexto campo25">
                <input class="campo_input" id="edtCodigo" type="text" />
                <label class="campo_label campo_required" for="edtCodigo">CODIGO</label>
              </div>
              <div class="campotexto campo25">
                <input class="campo_input" id="edtCpf" type="text" maxlength="11" />
                <label class="campo_label campo_required" for="edtCpf">CPF</label>
              </div>
              <div class="campotexto campo25">
                <input class="campo_input" id="edtApelido" type="text" maxlength="15" />
                <label class="campo_label campo_required" for="edtApelido">Apelido</label>
              </div>
              <div class="campotexto campo25">
                <input class="campo_input" id="edtSenha" type="password" maxlength="15" />
                <label class="campo_label campo_required" for="edtSenha">Senha</label>
              </div>
              <div class="campotexto campo15">
                <input class="campo_input inputF10" id="edtCodUp"
                                                    onBlur="codUpBlur(this);" 
                                                    onFocus="upFocus(this);" 
                                                    onClick="upF10Click('edtCodUp');"
                                                    data-oldvalue="0000" 
                                                    autocomplete="off" 
                                                    type="text" />
                <label class="campo_label campo_required" for="edtCodUp">PERFIL:</label>
              </div>
              <div class="campotexto campo35">
                <input class="campo_input_titulo input" id="edtDesUp" type="text" disabled />
                <label class="campo_label campo_required" for="edtDesUp">NOME_PERFIL</label>
              </div>
              <div class="campotexto campo15">
                <input class="campo_input inputF10" id="edtCodCrg"
                                                    onBlur="codCrgBlur(this);" 
                                                    onFocus="crgFocus(this);" 
                                                    onClick="crgF10Click('edtCodCrg');"
                                                    data-oldvalue="" 
                                                    autocomplete="off" 
                                                    type="text" />
                <label class="campo_label campo_required" for="edtCodCrg">CARGO:</label>
              </div>
              <div class="campotexto campo35">
                <input class="campo_input_titulo input" id="edtDesCrg" type="text" disabled />
                <label class="campo_label campo_required" for="edtDesCrg">NOME_CARGO</label>
              </div>
              <div class="campotexto campo25">
                <select class="campo_input_combo" id="cbAdmPub">
                  <option value="P">PUBLICO</option>
                  <option value="A">ADMINISTRADOR</option>
                </select>
                <label class="campo_label campo_required" for="cbAdmPub">PUB/ADM</label>
              </div>
              <div class="campotexto campo50">
                <input class="campo_input" id="edtEmail" type="text" maxlength="60" />
                <label class="campo_label campo_required" for="edtEmail">Email</label>
              </div>
              <div class="campotexto campo25">
                <select class="campo_input_combo" id="cbInterno">
                  <option value="I">INTERNO</option>
                  <option value="E">EXTERNO</option>
                  <option value="D">DEDICADO</option>
                </select>
                <label class="campo_label campo_required" for="cbInterno">INTERNO</label>
              </div>
              <div class="campotexto campo25">
                <select class="campo_input_combo" id="cbAtivo">
                  <option value="S">SIM</option>
                  <option value="N">NAO</option>
                </select>
                <label class="campo_label campo_required" for="cbAtivo">ATIVO</label>
              </div>
              <div class="campotexto campo25">
                <select class="campo_input_combo" id="cbReg">
                  <option value="P">PUBLICO</option>               
                </select>
                <label class="campo_label campo_required" for="cbReg">REG</label>
              </div>
              <div class="campotexto campo25">
                <input class="campo_input_titulo" disabled name="edtUsuario" id="edtUsuario" type="text" />
                <label class="campo_label campo_required" for="edtUsuario">USUARIO</label>
              </div>
              <div class="inactive">
                <input id="edtCodUsu" type="text" />
              </div>
              <div id="edtSelecionarUnidadeDiv" class="campotexto campo100">
                <button class="campo100 tableBotao botaoHorizontal" type="button" id="edtSelecionarUnidade" onClick="uniF10Click('edtSelecionarUnidade');">ESCOLHER UNIDADES </button>
                <label for="edtSelecionarUnidade">UNIDADES SELECIONADAS</label>
              </div>
              <div class="campotexto campo100">
                <select id="unidadeSelect" multiple size="8" class="campo100">
                </select>              
              </div>
              <div class="campotexto campo100"></div>
              <div class="campotexto campo100">
                <div class="campotexto campo50" style="margin-top:1em;">
                  <label class="campo_required" style="font-size:1.4em;"></label>
                  <label class="campo_labelSombra">Campo obrigatório</label>
                </div>              
                <div class="campo15" style="float:right;">            
                  <input id="idBtnConfirmarCustom" type="button" value="Confirmar" class="campo100 tableBotao botaoForaTable"/>            
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
            <!--<input type="hidden" id="sql" name="sql"/>-->
            <div id="tabelaExc" class="center active" style="position:fixed;top:10em;width:90em;z-index:30;display:none;" >
            </div>
          </form>
        </div>
      </div>
      <!--/Fim Importar excel -->
    </div>       
  </body>
</html>