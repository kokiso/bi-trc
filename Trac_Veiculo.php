<?php
  session_start();
  if( isset($_POST["veiculo"]) ){
    try{
      require("classPhp/conectaSqlServer.class.php");
      require("classPhp/validaJson.class.php");
      require("classPhp/removeAcento.class.php");

      $vldr     = new validaJSon();
      $retorno  = "";
      $retCls   = $vldr->validarJs($_POST["veiculo"]);
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
        $sql="";
        ///////////////////////////////////////////////////////////
        //   Buscando apenas as unidades que usuario tem direito //
        ///////////////////////////////////////////////////////////
        if( $rotina=="unidade" ){
          $sql="SELECT UNI_CODIGO,UNI_APELIDO
                  FROM UNIDADE A
                  LEFT OUTER JOIN USUARIOSISTEMA U ON A.UNI_CODUSR=U.US_CODIGO
                  LEFT OUTER JOIN USUARIOUNIDADE UU ON A.UNI_CODIGO=UU.UU_CODUNI AND UU.UU_CODUSR=".$_SESSION['usr_codigo']."
                 WHERE ((UNI_ATIVO='S') AND (COALESCE(UU.UU_ATIVO,'')='S'))
                 ORDER BY UNI_APELIDO";
          $classe->msgSelect(false);
          $retCls=$classe->selectAssoc($sql);
          if( $retCls['retorno'] != "OK" ){
            $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';
          } else {
            $retorno='[{"retorno":"OK","dados":'.json_encode($retCls['dados']).',"erro":""}]';
          };
        };
        // if( $rotina=="executa" ){
        //   $sql=$lote[0]->sql;
        //   $classe->msgSelect(false);
        //   $retCls=$classe->selectAssoc($sql);
        //   if( $retCls['retorno'] != "OK" ){
        //     $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';
        //   } else {
        //     $retorno='[{"retorno":"OK","dados":'.json_encode($retCls['dados']).',"erro":""}]';
        //   };
        // };
        if( $rotina=="motorista" ){
          $sql.="SELECT MTR.MTR_CODIGO AS CODIGO, MTR.MTR_NOME AS NOME, MTR.MTR_RFID AS RFID";
          $sql.=" FROM MOTORISTA MTR ";
          $sql.=" INNER JOIN UNIDADE U ON U.UNI_CODIGO=MTR.MTR_CODUNI";                              
          $sql.=" WHERE MTR.MTR_ATIVO = 'S' AND MTR.MTR_EXCLUIDO = 'N'";
          if ($lote[0]->codUni != 0) {
            $sql.=" AND U.UNI_CODIGO = ".$lote[0]->codUni;
          }                                               
          $sql.=" GROUP BY MTR.MTR_CODIGO, MTR.MTR_NOME, MTR.MTR_RFID";
          $classe->msgSelect(false);
          $retCls=$classe->selectAssoc($sql);
          if( $retCls['retorno'] != "OK" ){
            $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';
          } else {
            $retorno='[{"retorno":"OK","dados":'.json_encode($retCls['dados']).',"erro":""}]';
          };
        };
        ///////////////////////////////////////////////////////////
        //   Buscando apenas as unidades que usuario tem direito //
        ///////////////////////////////////////////////////////////
        if( $rotina=="grupoOperacional" ){
          $sql.="SELECT GPO_CODIGO AS CODIGO, GPO_NOME AS NOME";
          
          if ($lote[0]->codUni != 0) {
           $sql.=", U.UNI_CODIGO, U.UNI_APELIDO ";
          }
          
          $sql.=" FROM GRUPOOPERACIONAL
          INNER JOIN GRUPOOPERACIONALUNIDADE on GOU_CODGPO = GRUPOOPERACIONAL.GPO_CODIGO AND
          GOU_CODUNI";
          if ($lote[0]->codUni != 0) {
            $sql.=" = ".$lote[0]->codUni." INNER JOIN UNIDADE U ON U.UNI_CODIGO=GOU_CODUNI";
          } else {
            $sql.=" IN (SELECT UU_CODUNI FROM USUARIOUNIDADE WHERE UU_CODUSR =".$_SESSION['usr_codigo']." AND UU_ATIVO = 'S')";
          }                                               
          $sql.=" GROUP BY GPO_CODIGO, GPO_NOME, GPO_CODUSR";
          if ($lote[0]->codUni != 0) {
            $sql.=", U.UNI_CODIGO, U.UNI_APELIDO ";
          }
          $classe->msgSelect(false);
          $retCls=$classe->selectAssoc($sql);
          if( $retCls['retorno'] != "OK" ){
            $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';
          } else {
            $retorno='[{"retorno":"OK","dados":'.json_encode($retCls['dados']).',"erro":""}]';
          };
        };
        ////////////////////////////////////////////////
        //       Dados para JavaScript VEICULO        //
        ////////////////////////////////////////////////
        if( $rotina=="selectVcl" ){
          
          $gpo="";

          if( $lote[0]->grupoOperacional != 'TODOS' ) {
            $gpo = " AND (A.VCL_CODGPO=".$lote[0]->grupoOperacional.")";
          }

          $sql="";
          $sql.="SELECT VCL_CODIGO";
          $sql.="      ,VCL_NOME";
          $sql.="      ,CASE WHEN A.VCL_FROTA='L' THEN CAST('LEVE' AS VARCHAR(4)) ELSE CAST('PESADA' AS VARCHAR(6)) END AS VCL_FROTA";
          $sql.="      ,VCL_CODUNI";
          $sql.="      ,CASE WHEN A.VCL_ENTRABI='S' THEN 'SIM' ELSE 'NAO' END AS VCL_ENTRABI";
          $sql.="      ,CASE WHEN A.VCL_MTRFIXO='S' THEN 'SIM' ELSE 'NAO' END AS VCL_MTRFIXO";
          $sql.="      ,VCL_CODMTR";
          $sql.="      ,MTR.MTR_NOME";
          $sql.="      ,CONVERT(VARCHAR(10),VCL_DTCALIBRACAO,127) AS VCL_DTCALIBRACAO";
          $sql.="      ,VCL_NUMFROTA";
          $sql.="      ,UNI.UNI_APELIDO";
          $sql.="      ,COALESCE(VCL_CODGPO, 0)";
          $sql.="      ,GPO.GPO_NOME";
          $sql.="      ,CASE WHEN A.VCL_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS VCL_ATIVO";
          $sql.="      ,CASE WHEN A.VCL_REG='P' THEN 'PUB' WHEN A.VCL_REG='S' THEN 'SIS' ELSE 'ADM' END AS VCL_REG";
          $sql.="      ,US_APELIDO";
          $sql.="      ,VCL_CODUSR";
          $sql.="  FROM VEICULO A";
          $sql.="  LEFT OUTER JOIN USUARIOSISTEMA U ON A.VCL_CODUSR=U.US_CODIGO";
          $sql.="  LEFT OUTER JOIN GRUPOOPERACIONAL GPO ON A.VCL_CODGPO=GPO.GPO_CODIGO";
          $sql.="  LEFT OUTER JOIN UNIDADE UNI ON A.VCL_CODUNI=UNI.UNI_CODIGO";
          $sql.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON A.VCL_CODUNI=UU.UU_CODUNI AND UU.UU_CODUSR=".$_SESSION['usr_codigo'];
          $sql.="  LEFT OUTER JOIN MOTORISTA MTR ON A.VCL_CODMTR=MTR.MTR_CODIGO"; 
          $sql.=" WHERE (((VCL_ATIVO='".$lote[0]->ativo."') OR ('* '='".$lote[0]->ativo."')) AND (COALESCE(UU.UU_ATIVO,'')='S'))";
          if( $lote[0]->coduni > 0 ){
            $sql.=" AND (VCL_CODUNI=".$lote[0]->coduni.")";
          };
          $sql.=$gpo;
          $classe->msgSelect(false);
          $retCls=$classe->select($sql);
          if( $retCls['retorno'] != "OK" ){
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
                //array_push($data,["0000","CABECALHO","LINHA 1 DEVE SER ".$lote[0]->cabec]);
                ////////////////////////////////////////////////////////
                // Sequencia obrigatoria em Ajuda pada campos padrões //
                ////////////////////////////////////////////////////////
                array_push($data,[ "CODIGO"       /* 00 */
                                  ,"DESCRICAO"    /* 01 */
                                  ,"FROTA"        /* 02 */
                                  ,"CODUNI"       /* 03 */
                                  ,"ENTRABI"      /* 04 */
                                  ,"DTCALIBRACAO" /* 05 */

                                  ,"LINHA 1 DEVE SER ".$lote[0]->cabec]);
                $strExcel   = "N";
              };
            } else {
              $erro="OK";

              $linC = -1;
              foreach($cells as $cell){
                $linC++;
                switch( $linC ){
                  /////////////////
                  //    PLACA    //
                  /////////////////
                  case 0:
                    $clsRa->montaRetorno($cell->nodeValue);
                    $codigo=$clsRa->getNome();
                    if( strlen($codigo)<>7 ){
                      $erro     = "CAMPO PLACA DEVE TER TAMANHO 07..07";
                      $strExcel = "N";
                    };
                    $achei=false;
                    foreach( $arrDup as $lin ){
                      if( $lin["DESCRICAO"]==$codigo ){
                        $erro     = "PLACA DUPLICADA NA PLANILHA";
                        $strExcel = "N";
                        $achei    = true;
                        break;
                      };
                    };
                    if( $achei==false ){
                      array_push($arrDup,[
                        "DESCRICAO"=>$codigo
                      ]);
                    };
                    break;
                  /////////////////
                  //  DESCRICAO  //
                  /////////////////
                  case 1:
                    $clsRa->montaRetorno($cell->nodeValue);
                    $descricao=$clsRa->getNome();
                    if( (strlen($descricao)<3) or (strlen($descricao)>40) ){
                      $erro     = "CAMPO DESCRICAO DEVE TER TAMANHO 01..20";
                      $strExcel = "N";
                    };
                    break;
                  /////////////////
                  //  FROTA      //
                  /////////////////
                  case 2:
                    $clsRa->montaRetorno($cell->nodeValue);
                    $frota=$clsRa->getNome();
                    if( !preg_match("/^(L|P)$/",$frota) ){
                      $erro     = "CAMPO FROTA ACEITA APENAS L/P";
                      $strExcel = "N";
                    };
                    break;
                  /////////////////
                  //   UNIDADE   //
                  /////////////////
                  case 3:
                    $coduni=preg_replace('/[^0-9]/', '', trim($cell->nodeValue));
                    break;
                  /////////////////
                  //  ENTRABI    //
                  /////////////////
                  case 4:
                    $clsRa->montaRetorno($cell->nodeValue);
                    $entrabi=$clsRa->getNome();
                    if( !preg_match("/^(S|N)$/",$entrabi) ){
                      $erro     = "CAMPO ENTRABI ACEITA APENAS S/V";
                      $strExcel = "N";
                    };
                    break;
                  ////////////////////
                  //  DTCALIBRACAO  //
                  ////////////////////
                  case 5:
                    $dtcalibracao=$cell->nodeValue;
                    break;

                };
              };
              ////////////////////////////////////////////
              // Montando o vetor para retornar ao JSON //
              ////////////////////////////////////////////
              array_push($data,[$codigo,$descricao,$frota,$coduni,$entrabi,$dtcalibracao,$erro]);
              /////////////////////////////////////////////////////
              // Guardando os inserts se não existir nenhum erro //
              /////////////////////////////////////////////////////
              $sql="INSERT INTO VVEICULO("
                ."VCL_CODIGO"
                .",VCL_NOME"
                .",VCL_FROTA"
                .",VCL_CODUNI"
                .",VCL_ENTRABI"
                .",VCL_DTCALIBRACAO"
                .",VCL_REG"
                .",VCL_CODUSR"
                .",VCL_ATIVO) VALUES("
                ."'$codigo'"                  // VCL_CODIGO
                .",'".$descricao."'"          // VCL_NOME
                .",'".$frota."'"              // VCL_FROTA
                .",'".$coduni."'"             // VCL_CODUNI
                .",'".$entrabi."'"            // VCL_ENTRABI
                .",'".$dtcalibracao."'"       // VCL_DTCALIBRACAO
                .",'P'"                       // VCL_REG
                .",".$_SESSION["usr_codigo"]  // VCL_CODUSR
                .",'S'"                       // VCL_ATIVO
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
    <title>Veiculo</title>
    <link rel="stylesheet" href="css/css2017.css">
    <link rel="stylesheet" href="css/cssTable2017.css">
    <link rel="stylesheet" href="css/cssFaTable.css">
    <script src="js/js2017.js"></script>
    <script src="js/jsTable2017.js"></script>
    <script src="tabelaTrac/f10/tabelaUnidadeF10.js"></script>
    <script src="tabelaTrac/f10/tabelaGrupoOperacionalF10.js"></script>
    <script src="tabelaTrac/f10/tabelaMotoristaF10.js"></script>
    <script language="javascript" type="text/javascript"></script>
    <style>
      .comboSobreTable {
        position:relative;
        float:left;
        display:block;
        overflow-x:auto;
        width:92em;
        height:5em;
        border:1px solid silver;
        border-radius: 6px 6px 6px 6px;
        background-color:white;
      }
      .botaoSobreTable {
        width:6em;
        margin-left:0.2em;
        margin-top:0.3em;
        height:3.05em;
        border-radius: 4px 4px 4px 4px;
      }
    </style>
    <script>
      "use strict";
      ////////////////////////////////////////////////
      // Executar o codigo após a pagina carregada  //
      ////////////////////////////////////////////////
      document.addEventListener("DOMContentLoaded", function(){
        /////////////////////////////////////////////
        //       Objeto clsTable2017 VEICULO       //
        /////////////////////////////////////////////
        jsVcl={
          "titulo":[
             {"id":0  ,"labelCol":"OPC"
                      ,"padrao":1}
            ,{"id":1  ,"field"          :"VCL_CODIGO"
                      ,"labelCol"       : "PLACA"
                      ,"obj"            : "edtCodigo"
                      ,"tamGrd"         : "7em"
                      ,"tamImp"         : "15"
                      ,"digitosValidos" : "A|B|C|D|E|F|G|H|I|J|K|L|M|N|O|P|Q|R|S|T|U|V|W|X|Y|Z|0|1|2|3|4|5|6|7|8|9"
                      ,"pk"             : "S"
                      ,"newRecord"      : ["","this","this"]
                      ,"insUpDel"       : ["S","N","N"]
                      ,"digitosMinMax"  : [7,7]
                      ,"formato"        : ["uppercase","removeacentos","tiraaspas","alltrim"]
                      ,"validar"        : ["notnull"]
                      ,"ajudaCampo"     : [  "Codigo do veiculo conforme definição empresa. Este campo é único e deve tem o formato AAA"
                                            ,"Campo deve ser utilizado em cadastros de Usuario"]
                      ,"importaExcel"   : "S"
                      ,"padrao":0}
            ,{"id":2  ,"field"          : "VCL_NOME"
                      ,"labelCol"       : "DESCRICAO"
                      ,"obj"            : "edtDescricao"
                      ,"tamGrd"         : "20em"
                      ,"tamImp"         : "80"
                      ,"digitosMinMax"  : [3,40]
                      ,"digitosValidos" : "A|B|C|D|E|F|G|H|I|J|K|L|M|N|O|P|Q|R|S|T|U|V|W|X|Y|Z|0|1|2|3|4|5|6|7|8|9| "
                      ,"ajudaCampo"     : ["Nome do veiculo com até 20 caracteres."]
                      ,"importaExcel"   : "S"
                      ,"padrao":0}
            ,{"id":3  ,"field"          : "VCL_FROTA"
                      ,"labelCol"       : "FROTA"
                      ,"obj"            : "cbFrota"
                      ,"tamGrd"         : "6em"
                      ,"tamImp"         : "20"
                      ,"tipo"           : "cb"
                      ,"newRecord"      : ["P","this","this"]
                      ,"ajudaCampo"     : ["Frota leve ou pesada."]
                      ,"importaExcel"   : "S"
                      ,"padrao":0}
            ,{"id":4  ,"field"          : "VCL_CODUNI"
                      ,"labelCol"       : "CODUNI"
                      ,"labelColImp"    : "UNID"
                      ,"obj"            : "edtCodUni"
                      ,"fieldType"      : "int"
                      ,"newRecord"      : ["0000","this","this"]
                      ,"formato"        : ["i4"]
                      ,"validar"        : ["notnull","intMaiorZero"]
                      ,"tamGrd"         : "0em"
                      ,"tamImp"         : "10"
                      ,"ajudaDetalhe"   : ["Para ver esta unidade é necessario direito em USUARIO->UNIDADE"]
                      ,"ajudaCampo"     : ["Para ver esta unidade é necessario direito em USUARIO->UNIDADE"]
                      ,"importaExcel"   : "S"
                      ,"padrao":0}
            ,{"id":5  ,"field"          : "VCL_ENTRABI"
                      ,"labelCol"       : "ENTRABI"
                      ,"obj"            : "cbEntraBi"
                      ,"tamGrd"         : "6em"
                      ,"tamImp"         : "20"
                      ,"tipo"           : "cb"
                      ,"newRecord"      : ["S","this","this"]
                      ,"ajudaCampo"     : ["Se veiculo entra nas estatisticas de BI."]
                      ,"importaExcel"   : "S"
                      ,"padrao":0}
            ,{"id":6  ,"field"          : "VCL_MTRFIXO"
                      ,"labelCol"       : "MTRFIXO"
                      ,"obj"            : "cbMtrFixo"
                      ,"tamGrd"         : "6em"
                      ,"tamImp"         : "20"
                      ,"tipo"           : "cb"
                      ,"newRecord"      : ["N","this","this"]
                      ,"ajudaCampo"     : ["Se o veículo possui motorista fixo ou não."]
                      ,"importaExcel"   : "S"
                      ,"padrao":0}
            ,{"id":7  ,"field"          : "VCL_CODMTR"
                      ,"obj"            : "edtCodMtr"
                      ,"tamGrd"         : "0em"
                      ,"newRecord"      : ["0000","this","this"]
                      ,"tamImp"         : "20"
                      ,"ajudaCampo"     : ["Código do motorista vinculado ao veículo."]
                      ,"importaExcel"   : "S"
                      ,"padrao":0}
            ,{"id":8  ,"field"          : "MTR_NOME"
                      ,"labelCol"       : "MOTORISTA"
                      ,"obj"            : "edtDesMtr"
                      ,"validar"        : ["podeNull"]
                      ,"insUpDel"       : ["N","N","N"]
                      ,"tamGrd"         : "15em"
                      ,"tamImp"         : "20"
                      ,"ajudaCampo"     : ["Motorista vinculado ao veículo."]
                      ,"importaExcel"   : "S"
                      ,"padrao":0}
            ,{"id":9  ,"field"          : "VCL_DTCALIBRACAO"
                      ,"labelCol"       : "CALIBRACAO"
                      ,"obj"            : "edtDtCalibracao"
                      ,"fieldType"      : "dat"
                      ,"tamGrd"         : "6em"
                      ,"tamImp"         : "20"
                      ,"newRecord"      : ["01/01/1900","this","this"]
                      ,"ajudaCampo"     : ["Data de calibracao veiculo."]
                      ,"importaExcel"   : "S"
                      ,"padrao":0}
            ,{"id":10  ,"field"          : "VCL_NUMFROTA"
                      ,"labelCol"       : "NUMFROTA"
                      ,"obj"            : "edtNumFrota"
                      ,"tamGrd"         : "10em"
                      ,"tamImp"         : "30"
                      ,"digitosMinMax"  : [3,20]
                      ,"digitosValidos" : "A|B|C|D|E|F|G|H|I|J|K|L|M|N|O|P|Q|R|S|T|U|V|W|X|Y|Z|0|1|2|3|4|5|6|7|8|9| "
                      ,"ajudaCampo"     : ["Frota do veiculo com até 20 caracteres."]
                      ,"importaExcel"   : "N"
                      ,"padrao":0}
            ,{"id":11  ,"field"          : "UNI_APELIDO"
                      ,"insUpDel"       : ["N","N","N"]
                      ,"labelCol"       : "UNID"
                      ,"obj"            : "edtDesUni"
                      ,"tamGrd"         : "10em"
                      ,"tamImp"         : "30"
                      ,"digitosMinMax"  : [3,15]
                      ,"validar"        : ["notnull"]
                      ,"ajudaCampo"     : ["Nome da unidade."]
                      ,"padrao":0}
            ,{"id":12  ,"field"          : "VCL_CODGPO"
                      ,"labelCol"       : "GRUPO OPERACIONAL"
                      ,"obj"            : "edtCodGpo"
                      ,"fieldType"      : "int"
                      ,"newRecord"      : ["0000","this","this"]
                      ,"formato"        : ["i4"]
                      ,"tamGrd"         : "0em"
                      ,"tamImp"         : "10"
                      ,"importaExcel"   : "S"
                      ,"padrao":0}
            ,{"id":13 ,"field"          : "GPO_NOME"
                      ,"insUpDel"       : ["N","N","N"]
                      ,"labelCol"       : "GRUPO OPERACIONAL"
                      ,"validar"        : ["podeNull"]
                      ,"obj"            : "edtDesGpo"
                      ,"tamGrd"         : "15em"
                      ,"tamImp"         : "80"
                      ,"digitosMinMax"  : [0,40]
                      ,"digitosValidos" : "A|B|C|D|E|F|G|H|I|J|K|L|M|N|O|P|Q|R|S|T|U|V|W|X|Y|Z|0|1|2|3|4|5|6|7|8|9| "
                      ,"ajudaCampo"     : ["Nome do grupo operacional."]
                      ,"importaExcel"   : "S"
                      ,"padrao":0}
            ,{"id":14  ,"field"          : "VCL_ATIVO"
                      ,"labelCol"       : "ATIVO"
                      ,"obj"            : "cbAtivo"
                      ,"ajudaDetalhe"   : "Se o veiculo esta ativo para uso"
                      ,"padrao":2}
            ,{"id":15 ,"field"          : "VCL_REG"
                      ,"labelCol"       : "REG"
                      ,"obj"            : "cbReg"
                      ,"lblDetalhe"     : "REGISTRO"
                      ,"ajudaDetalhe"   : "Se o registro é PUBlico/ADMinistrador ou do SIStema"
                      ,"padrao":3}
            ,{"id":16 ,"field"          : "US_APELIDO"
                      ,"labelCol"       : "USUARIO"
                      ,"obj"            : "edtUsuario"
                      ,"padrao":4}
            ,{"id":17 ,"field"          : "VCL_CODUSR"
                      ,"labelCol"       : "CODUSU"
                      ,"obj"            : "edtCodUsu"
                      ,"padrao":5}
            ,{"id":18 ,"labelCol"       : "PP"
                      ,"obj"            : "imgPP"
                      ,"func":"var elTr=this.parentNode.parentNode;"
                        +"elTr.cells[0].childNodes[0].checked=true;"
                        +"objVcl.espiao();"
                        +"elTr.cells[0].childNodes[0].checked=false;"
                      ,"padrao":8}
          ]
          ,
          "detalheRegistro":
          [
            { "width"           :"100%"
              ,"height"         :"320px"
              ,"label"          :"VEICULO - Detalhe do registro"
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
          ,"registros"      : []                        // Recebe um Json vindo da classe clsBancoDados
          ,"opcRegSeek"     : true                      // Opção para numero registros/botão/procurar
          ,"checarTags"     : "S"                       // Somente em tempo de desenvolvimento(olha as pricipais tags)
          ,"idBtnConfirmarAtualizar" : "idBtnConfirmarAtualizar"            // Se existir executa o confirmar do form/fieldSet
          ,"idBtnCancelar"  : "btnCancelar"             // Se existir executa o cancelar do form/fieldSet
          ,"div"            : "frmVcl"                  // Onde vai ser gerado a table
          ,"divFieldSet"    : "tabelaVcl"               // Para fechar a div onde estão os fieldset ao cadastrar
          ,"form"           : "frmVcl"                  // Onde vai ser gerado o fieldSet
          ,"divModal"       : "divTopoInicio"           // Onde vai se appendado abaixo deste a table
          ,"tbl"            : "tblVcl"                  // Nome da table
          ,"prefixo"        : "Vcl"                     // Prefixo para elementos do HTML em jsTable2017.js
          ,"tabelaBD"       : "VVEICULO"                // Nome da tabela no banco de dados
          ,"tabelaBKP"      : "BKPVEICULO"              // Nome da tabela no banco de dados
          ,"fieldAtivo"     : "VCL_ATIVO"               // SE EXISITIR - Nome do campo ATIVO(S/N) na tabela BD
          ,"fieldReg"       : "VCL_REG"                 // SE EXISITIR - Nome do campo SYS(P/A) na tabela BD
          ,"fieldCodUsu"    : "VCL_CODUSR"              // SE EXISITIR - Nome do campo CODIGO USUARIO na tabela BD
          ,"iFrame"         : "iframeCorpo"             // Se a table vai ficar dentro de uma tag iFrame
          ,"width"          : "92em"                    // Tamanho da table
          ,"height"         : "58em"                    // Altura da table
          ,"tableLeft"      : "sim"                     // Se tiver menu esquerdo
          ,"relTitulo"      : "VEICULO"                 // Titulo do relatório
          ,"relOrientacao"  : "P"                       // Paisagem ou retrato
          ,"relFonte"       : "8"                       // Fonte do relatório
          ,"foco"           : ["edtCodigo"
                              ,"edtDescricao"
                              ,"idBtnConfirmarAtualizar"]          // Foco qdo Cad/Alt/Exc
          ,"formPassoPasso" : "Trac_Espiao.php"         // Enderço da pagina PASSO A PASSO
          ,"indiceTable"    : "PLACA"                   // Indice inicial da table
          ,"tamBotao"       : "15"                      // Tamanho botoes defalt 12 [12/25/50/75/100]
          ,"tamMenuTable"   : ["10em","20em"]
          ,"labelMenuTable" : "Opções"              // Caption para menu table
          ,"_menuTable"     :[
                                ["Regitros ativos"                        ,"fa-thumbs-o-up"   ,"btnFiltrarClick('S');"]
                               ,["Registros inativos"                     ,"fa-thumbs-o-down" ,"btnFiltrarClick('N');"]
                               ,["Todos"                                  ,"fa-folder-open"   ,"btnFiltrarClick('*');"]
                               ,["Importar planilha excel"                ,"fa-file-excel-o"  ,"fExcel()"]
                               ,["Imprimir registros em tela"             ,"fa-print"         ,"objVcl.imprimir()"]
                               ,["Ajuda para campos padrões"              ,"fa-info"          ,"objVcl.AjudaSisAtivo(jsVcl);"]
                               ,["Detalhe do registro"                    ,"fa-folder-o"      ,"objVcl.detalhe();"]
                               ,["Gerar excel"                            ,"fa-file-excel-o"  ,"objVcl.excel();"]
                               ,["Passo a passo do registro"              ,"fa-binoculars"    ,"objVcl.espiao();"]
                               ,["Alterar status Ativo/Inativo"           ,"fa-share"         ,"objVcl.altAtivo(intCodDir);"]
                               ,["Alterar registro PUBlico/ADMinistrador" ,"fa-reply"         ,"objVcl.altPubAdm(intCodDir,jsPub[0].usr_admpub);"]
                               //,["Alterar para registro do SISTEMA"       ,"fa-reply"         ,"objVcl.altRegSistema("+jsPub[0].usr_d05+");"]
                               ,["Número de registros em tela"            ,"fa-info"          ,"objVcl.numRegistros();"]
                               ,["Número de registros em tela"            ,"fa-info"          ,"objVcl.numRegistros();"]
                               ,["Atualizar grade consulta"               ,"fa-filter"        ,"btnFiltrarClick('S');"]
                             ]
          ,"codTblUsu"      : "VEICULO[09]"
          ,"codDir"         : intCodDir
        };
        if( objVcl === undefined ){
          objVcl=new clsTable2017("objVcl");
        };
        objVcl.montarHtmlCE2017(jsVcl);
        //////////////////////////////////////////////////
        //          Fim objeto clsTable2017 VEICULO     //
        //////////////////////////////////////////////////
        //
        //
        //////////////////////////////////////////////
        // Montando a table para importar xls       //
        //////////////////////////////////////////////
        jsExc={
          "titulo":[
             {"id":0  ,"field":"PLACA"      ,"labelCol":"PLACA"     ,"tamGrd":"7em"   ,"tamImp":"20"}
            ,{"id":1  ,"field":"DESCRICAO"  ,"labelCol":"DESCRICAO" ,"tamGrd":"32em"  ,"tamImp":"100"}
            ,{"id":2  ,"field":"FROTA"      ,"labelCol":"FROTA"     ,"tamGrd":"5em"   ,"tamImp":"15"}
            ,{"id":3  ,"field":"CODUNI"     ,"labelCol":"UNIDADE"   ,"tamGrd":"32em"  ,"tamImp":"20"}
            ,{"id":4  ,"field":"ENTRABI"    ,"labelCol":"ENTRABI"   ,"tamGrd":"5em"   ,"tamImp":"15"}
            ,{"id":5  ,"field":"ERRO"       ,"labelCol":"ERRO"      ,"tamGrd":"35em"  ,"tamImp":"100"}
          ]
          ,"botoesH":[
             {"texto":"Imprimir"  ,"name":"excImprimir"   ,"onClick":"3"  ,"enabled":true ,"imagem":"fa fa-print"            ,"ajuda":"Imprimir registros em tela" }
            ,{"texto":"Excel"     ,"name":"excExcel"      ,"onClick":"5"  ,"enabled":false,"imagem":"fa fa-file-excel-o"     ,"ajuda":"Exportar para excel" }
            ,{"texto":"Fechar"    ,"name":"excFechar"     ,"onClick":"7"  ,"enabled":true ,"imagem":"fa fa-close"            ,"ajuda":"Fechar formulario" }
          ]
          ,"registros"      : []                        // Recebe um Json vindo da classe clsBancoDados
          ,"corLinha"       : "if(ceTr.cells[4].innerHTML !='OK') {ceTr.style.color='yellow';ceTr.style.backgroundColor='red';}"
          ,"checarTags"     : "N"                       // Somente em tempo de desenvolvimento(olha as pricipais tags)
          ,"div"            : "frmExc"                  // Onde vai ser gerado a table
          ,"divFieldSet"    : "tabelaExc"               // Para fechar a div onde estão os fieldset ao cadastrar
          ,"form"           : "frmExc"                  // Onde vai ser gerado o fieldSet
          ,"divModal"       : "divTopoInicioE"          // Nome da div que vai fazer o show modal
          ,"tbl"            : "tblExc"                  // Nome da table
          ,"prefixo"        : "exc"                     // Prefixo para elementos do HTML em jsTable2017.js
          ,"tabelaBD"       : "*"                       // Nome da tabela no banco de dados
          ,"width"          : "90em"                    // Tamanho da table
          ,"height"         : "48em"                    // Altura da table
          ,"tableLeft"      : "sim"                     // Se tiver menu esquerdo
          ,"relTitulo"      : "Importação veiculo"      // Titulo do relatório
          ,"relOrientacao"  : "P"                       // Paisagem ou retrato
          ,"indiceTable"    : "TAG"                     // Indice inicial da table
          ,"relFonte"       : "8"                       // Fonte do relatório
          ,"formName"       : "frmExc"                  // Nome do formulario para opção de impressão
          ,"tamBotao"       : "20"                      // Tamanho botoes defalt 12 [12/25/50/75/100]
        };
        if( objExc === undefined ){
          objExc=new clsTable2017("objCon");
        };
        objExc.montarHtmlCE2017(jsExc);
        //
        //
        // buscarUni();
      });
      //
      var objVcl;                     // Obrigatório para instanciar o JS TFormaCob
      var jsVcl;                      // Obj principal da classe clsTable2017
      var objUniF10;                  // Obrigatório para instanciar o JS CidadeF10
      var objGpoF10;                  // Obrigatório para instanciar o JS GrupoOperacionalF10
      var objMtrF10;                  // Obrigatório para instanciar o JS MotoristaF10
      var objExc;                     // Obrigatório para instanciar o JS Importar excel
      var jsExc;                      // Obrigatório para instanciar o objeto objExc
      var clsJs;                      // Classe responsavel por montar um Json e eviar PHP
      var clsErro;                    // Classe para erros
      var fd;                         // Formulario para envio de dados para o PHP
      var msg;                        // Variavel para guardadar mensagens de retorno/erro
      var retPhp                      // Retorno do Php para a rotina chamadora
      var contMsg   = 0;              // contador para mensagens
      var cmp       = new clsCampo(); // Abrindo a classe campos
      var jsPub     = JSON.parse(localStorage.getItem("lsPublico"));
      var intCodDir = parseInt(jsPub[0].usr_d09);
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
        if( document.getElementById("cbUnidade").value != "*" ){
          clsJs   = jsString("lote");
          clsJs.add("rotina"      , "selectVcl"                                 );
          clsJs.add("login"       , jsPub[0].usr_login                          );
          clsJs.add("ativo"       , atv                                         );
          clsJs.add("coduni"      , document.getElementById("cbUnidade").value  );
          clsJs.add("grupoOperacional"   	, document.getElementById("cbGpo").value  	);
          fd = new FormData();
          fd.append("veiculo" , clsJs.fim());
          msg     = requestPedido("Trac_Veiculo.php",fd);
          retPhp  = JSON.parse(msg);
          if( retPhp[0].retorno == "OK" ){
            //////////////////////////////////////////////////////////////////////////////////
            // O novo array não tem o campo idUnico mas a montarHtmlCE2017 ja foi executada //
            // Campo obrigatório se existir rotina de manutenção na table devido Json       //
            // Esta rotina não tem manutenção via classe clsTable2017                       //
            // jsCrv.registros=objCrv.addIdUnico(retPhp[0]["dados"]);                       //
            //////////////////////////////////////////////////////////////////////////////////
            jsVcl.registros=objVcl.addIdUnico(retPhp[0]["dados"]);
            objVcl.ordenaJSon(jsVcl.indiceTable,false);
            objVcl.montarBody2017();
            document.getElementById("edtDesMtr").enabled = true;
          };
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
          clsJs.add("rotina"      , "impExcel"                              );
          clsJs.add("login"       , jsPub[0].usr_login                      );
          clsJs.add("cabec"       , "PLACA|DESCRICAO|FROTA|CODUNI|ENTRABI"  );

          fd = new FormData();
          fd.append("veiculo"      , clsJs.fim());
          fd.append("arquivo"   , edtArquivo.files[0] );
          msg     = requestPedido("Trac_Veiculo.php",fd);
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
          gerarMensagemErro("Vcl",retPhp[0].erro,"AVISO");
        };
      };

    //   // Custom persistencia 

    //   function verificaTipoInstrucao(status, sql) {
    //     updateDados(sql);
    // }

    // function updateDados(sql) {
    //   clsJs   = jsString("lote");  
    //   clsJs.add("rotina"      , "executa"         );
    //   clsJs.add("login"       , jsPub[0].usr_login  );
    //   clsJs.add("sql"         , sql  );
    //   fd = new FormData();
    //   fd.append("veiculo" , clsJs.fim());
    //   msg     = requestPedido("Trac_Veiculo.php",fd); 
    //   retPhp  = JSON.parse(msg);
    //   document.getElementById('unidadeSelect').innerHTML = '';


      ////////////////////////
      // AJUDA PARA UNIDADE //
      ////////////////////////
      function uniFocus(obj){
        document.getElementById(obj.id).setAttribute("data-oldvalue",document.getElementById(obj.id).value);
      };
      function uniF10Click(){ fUnidadeF10(0,"edtCodUni","cbAtivo","soAtivo"); };
      function gpoF10Click(){
        let codUni = document.getElementById('edtCodUni').value;
         fGrupoOperacionalF10("cbAtivo", codUni);
         };
      function RetF10tblUni(arr){
        document.getElementById("edtCodUni").value   = arr[0].CODIGO;
        document.getElementById("edtDesUni").value   = arr[0].APELIDO;
        document.getElementById("edtCodUni").setAttribute("data-oldvalue",arr[0].CODIGO);
      };
    function RetF10tblGpo(arr){
        document.getElementById("edtCodGpo").value   = arr[0].CODIGO;
        document.getElementById("edtDesGpo").value   = arr[0].NOME;
        document.getElementById("edtCodGpo").setAttribute("data-oldvalue",arr[0].CODIGO);
      };
      function codUniBlur(obj){
        var elOld = jsNmrs(document.getElementById(obj.id).getAttribute("data-oldvalue")).inteiro().ret();
        var elNew = jsNmrs(obj.id).inteiro().ret();
        if( elOld != elNew ){
          var ret = fUnidadeF10(1,obj.id,"cbAtivo","soAtivo");
          document.getElementById(obj.id).value          = ( ret.length == 0 ? "0000"    : jsNmrs(ret[0].CODIGO).emZero(4).ret()  );
          document.getElementById("edtDesUni").value     = ( ret.length == 0 ? ""        : ret[0].APELIDO                       );
          document.getElementById(obj.id).setAttribute("data-oldvalue",( ret.length == 0 ? "0000" : ret[0].CODIGO )               );
        };
      };

      function mtrF10Click(){
        let codUni = document.getElementById('edtCodUni').value;
        if (codUni == 0) {
          return gerarMensagemErro("", "Selecione uma Unidade antes de escolher o motorista", "Erro");
        }
         fMotoristaF10("edtDesMtr",codUni);
       };

       function RetF10tblMtr(arr){
        document.getElementById("edtCodMtr").value   = arr[0].CODIGO;
        document.getElementById("edtDesMtr").value   = arr[0].NOME;
        document.getElementById("edtCodUni").setAttribute("data-oldvalue",arr[0].CODIGO);
      };

      function montaGrupoOperacional() {
        var cbUnidadeValue = document.getElementById("cbUnidade").value;
        var divGrupoOperacional = document.getElementById("divCbGrupoOperacional");
        var uniCodigo;

        if(cbUnidadeValue != "TODOS") {
          uniCodigo = cbUnidadeValue.split('-')[0];

          clsJs   = jsString("lote");
          clsJs.add("uniCodigo"  	, uniCodigo                    );
        } else {
          clsJs   = jsString("lote");
          clsJs.add("uniCodigo"  	, ""                    );
        }

        fd = new FormData();
        fd.append("montaSelectGrupoOperacional" , clsJs.fim());
        var selectGrupoOperacional = requestPedido("classPhp/comum/selectGrupoOperacional.class.php",fd);
        document.getElementById('selectGrupoOperacionalPHP').innerHTML = selectGrupoOperacional;
        document.getElementById('cbGpo').value="TODOS";
      };
    </script>
  </head>
  <body>
    <div id="divEvento" class="comboSobreTable">

      <div style="margin-top:3px;margin-left:3px;">
        <?php include 'classPhp/comum/selectUnidade.class.php';?>
      </div>
      <div style="margin-top:3px;margin-left:3px;" id="selectGrupoOperacionalPHP">
        <?php include 'classPhp/comum/selectGrupoOperacional.class.php';?>
      </div>
      <div class="campo10" style="float:left;">
        <input id="btnFilttrar" onClick="btnFiltrarClick('S');" type="button" value="Filtrar" class="botaoSobreTable"/>
      </div>
      <div class="_campotexto campo100" style="margin-top:1.7em;height:3em;">
        <label class="campo_required" style="font-size:1.4em;"></label>
        <label class="campo_labelSombra">Solicitado filtro devido quantidade de registros</label>
      </div>
    </div>

    <div class="divTelaCheia" style="float:left;">
      <div id="divRotina" class="conteudo" style="display:block;overflow-x:auto;">
        <div id="divTopoInicio">
        </div>
        <form method="post"
              name="frmVcl"
              id="frmVcl"
              class="frmTable"
              action="classPhp/imprimirsql.php"
              target="_newpage"
              style="top: 6em; width:90em;position: absolute; z-index:30;display:none;">
          <p class="frmCampoTit">
          <input class="informe" type="text" name="titulo" value="Veiculo" disabled="" style="color: white; text-align: left;">
          </p>
          <div style="height: 200px; overflow-y: auto;">
            <input type="hidden" id="sql" name="sql"/>
            <div class="campotexto campo100">
              <div class="campotexto campo10">
                <input class="campo_input" id="edtCodigo" type="text" maxlength="7" />
                <label class="campo_label campo_required" for="edtCodigo">CODIGO</label>
              </div>
              <div class="campotexto campo75">
                <input class="campo_input" id="edtDescricao" type="text" maxlength="40" />
                <label class="campo_label campo_required" for="edtDescricao">DESCRICAO</label>
              </div>
              <div class="campotexto campo15">
                <select class="campo_input_combo" id="cbFrota">
                  <option value="P">PESADA</option>
                  <option value="L">LEVE</option>
                </select>
                <label class="campo_label campo_required" for="cbFrota">FROTA</label>
              </div>
              <div class="campotexto campo15 naoDesabilitar">
                <input class="campo_input inputF10 naoDesabilitar" id="edtCodUni"
                                                    OnKeyPress="return mascaraInteiro(event);"
                                                    onBlur="codUniBlur(this);"
                                                    onFocus="uniFocus(this);"
                                                    onClick="uniF10Click('edtCodUni');"
                                                    data-oldvalue=""
                                                    autocomplete="off"
                                                    type="text" />
                <label class="campo_label campo_required" for="edtCodUni">UNIDADE</label>
              </div>
              <div class="campotexto campo25">
                <input class="campo_input_titulo input" id="edtDesUni" type="text" disabled />
                <label class="campo_label campo_required" for="edtDesUni">RAZAO_UNIDADE</label>
              </div>
              <div class="campotexto campo15">
                <select class="campo_input_combo" id="cbMtrFixo">
                  <option value="S">SIM</option>
                  <option value="N">NAO</option>
                </select>
                <label class="campo_label campo_required" for="cbMtrFixo">MOTORISTA_FIXO</label>
              </div>
              <div class="inactive">
                <input id="edtCodMtr" type="text" />
              </div>
              <div class="campotexto campo20">
                    <input class="campo_input inputF10" id="edtDesMtr"
                           onFocus="uniFocus(this);"
                           onClick="mtrF10Click('edtCodMtr');" 
                           autocomplete="off"
                           data-oldvalue=""
                           type="text"
                     />
                           
                    <label class="campo_label" for="edtDesMtr">MOTORISTA</label>
                </div>
              <div class="campotexto campo12">
                <select class="campo_input_combo" id="cbEntraBi">
                  <option value="S">SIM</option>
                  <option value="N">NAO</option>
                </select>
                <label class="campo_label campo_required" for="cbEntraBi">ENTRA BI</label>
              </div>
              <div class="campotexto campo12">
                <input class="campo_input" id="edtDtCalibracao" type="text" OnKeyUp="mascaraData(this,event);" maxlength="10" />
                <label class="campo_label campo_required" for="edtDtCalibracao">CALIBRACAO</label>
              </div>
              <div class="campotexto campo15">
                <input class="campo_input" id="edtNumFrota" type="text" maxlength="20" />
                <label class="campo_label campo_required" for="edtNumFrota">NUM.FROTA</label>
              </div>
              <div class="campotexto campo15">
                <select class="campo_input_combo" id="cbAtivo">
                  <option value="S">SIM</option>
                  <option value="N">NAO</option>
                </select>
                <label class="campo_label campo_required" for="cbAtivo">ATIVO</label>
              </div>
              <div class="campotexto campo15">
                <select class="campo_input_combo" id="cbReg">
                  <option value="P">PUBLICO</option>
                </select>
                <label class="campo_label campo_required" for="cbReg">REG</label>
              </div>
              <div class="campotexto campo10">
                <input class="campo_input inputF10" id="edtCodGpo"
                                                    OnKeyPress="return mascaraInteiro(event);"
                                                    onFocus="uniFocus(this);"
                                                    onClick="gpoF10Click('edtCodGpo');"
                                                    data-oldvalue=""
                                                    autocomplete="off"
                                                    maxlength="4"
                                                    type="text" />
                <label class="campo_label" for="edtCodGpo">GRUPO OP.</label>
              </div>
              <div class="campotexto campo25">
                <input class="campo_input_titulo input" id="edtDesGpo" type="text" disabled />
                <label class="campo_label" for="edtDesGpo">NOME GRUPO OPERACIONAL</label>
              </div>
              <div class="campotexto campo20">
                <input class="campo_input_titulo" disabled id="edtUsuario" type="text" />
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
                <div class="campo20" style="float:right;">
                  <input id="idBtnConfirmarAtualizar" type="button" value="Confirmar" class="campo100 tableBotao botaoForaTable"/>
                  <i class="faBtn fa-check icon-large"></i>
                </div>
                <div class="campo20" style="float:right;">
                  <input id="btnCancelar" type="button" value="Cancelar" class="campo100 tableBotao botaoForaTable"/>
                  <i class="faBtn fa-close icon-large"></i>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
      <!-- Importar excel -->
      <div id="divExcel" class="divTopoExcel">
        <div id="divTopoInicioE" class="divTopoInicio">
          <div class="divTopoInicio_Informacao" style="padding-top: 0.2em;border:none;">
            <div class="campotexto campo50">
              <input class="campo_file input" name="edtArquivo" id="edtArquivo" type="file" />
              <label class="campo_label" for="edtArquivo">Arquivo</label>
            </div>
            <div class="campo12" style="float:left;">
              <input id="btnAbrirExcel" onClick="btnAbrirExcelClick();" type="button" value="Abrir" class="campo100 tableBotao botaoForaTable" style="height: 3.4em !important;"/>
            </div>
          </div>
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
      <!-- Fim Importar excel -->
    </div>
  </body>
</html>