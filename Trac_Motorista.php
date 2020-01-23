<?php
  session_start();
  if( isset($_POST["motorista"]) ){
    try{     
      require("classPhp/conectaSqlServer.class.php");
      require("classPhp/validaJson.class.php"); 
      require("classPhp/removeAcento.class.php"); 

      $vldr     = new validaJSon();          
      $retorno  = "";
      $retCls   = $vldr->validarJs($_POST["motorista"]);
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
        ////////////////////////////////////////////////
        //         Dados para JavaScript MOTORISTA    //
        ////////////////////////////////////////////////
        if( $rotina=="selectMtr" ){
          $sql="";
          $sql.="SELECT MTR_CODIGO";
          $sql.="      ,MTR_NOME";
          $sql.="      ,MTR_BIAB";
          $sql.="      ,MTR_BICB";
          $sql.="      ,MTR_BIEV";
          $sql.="      ,MTR_BIEVC";
          $sql.="      ,MTR_BIERPM";          
          $sql.="      ,MTR_BIFB";          
          $sql.="      ,MTR_BITOTAL";
          $sql.="      ,MTR_RFID";
          $sql.="      ,MTR_CODUNI";
          $sql.="      ,UNI.UNI_APELIDO";
					$sql.="      ,MTR_POSICAO";								
          $sql.="      ,CASE WHEN A.MTR_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS MTR_ATIVO";
          $sql.="      ,CASE WHEN A.MTR_REG='P' THEN 'PUB' WHEN A.MTR_REG='S' THEN 'SIS' ELSE 'ADM' END AS MTR_REG";
          $sql.="      ,US_APELIDO";
          $sql.="      ,MTR_CODUSR";
          $sql.="  FROM MOTORISTA A";
          $sql.="  LEFT OUTER JOIN USUARIOSISTEMA U ON A.MTR_CODUSR=U.US_CODIGO";
          $sql.="  LEFT OUTER JOIN UNIDADE UNI ON A.MTR_CODUNI=UNI.UNI_CODIGO";
          $sql.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON A.MTR_CODUNI=UU.UU_CODUNI AND UU.UU_CODUSR=".$_SESSION['usr_codigo'];
          $sql.=" WHERE (((MTR_ATIVO='".$lote[0]->ativo."') OR ('*'='".$lote[0]->ativo."')) AND (COALESCE(UU.UU_ATIVO,'')='S'))"; 
          if( $lote[0]->coduni > 0 ){
            $sql.=" AND (MTR_CODUNI=".$lote[0]->coduni.")";
          };          
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
          $arrDup   = [];           //Verifica se existe duplicidade no arquivo recebido
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
                array_push($data,[ "CODIGO"       /* 00 */
                                  ,"DESCRICAO"    /* 01 */
                                  ,"RFID"         /* 02 */
                                  ,"CODUNI"       /* 03 */
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
                  //   CODIGO    //
                  /////////////////
                  case 0: 
                    $codigo=preg_replace('/[^0-9]/', '', trim($cell->nodeValue));                                      
                    break;
                  /////////////////
                  //  DESCRICAO  //
                  /////////////////
                  case 1: 
                    $clsRa->montaRetorno($cell->nodeValue);
                    $descricao=$clsRa->getNome();
                    if( (strlen($descricao)<3) or (strlen($descricao)>60) ){ 
                      $erro     = "CAMPO DESCRICAO DEVE TER TAMANHO 01..60";
                      $strExcel = "N";
                    };
                    break;
                  /////////////////
                  //  RFID       //
                  /////////////////
                  case 2: 
                    $clsRa->montaRetorno($cell->nodeValue);
                    $rfid=$clsRa->getNome();
                    break;                  
                  /////////////////
                  //   UNIDADE   //
                  /////////////////
                  case 3: 
                    $coduni=preg_replace('/[^0-9]/', '', trim($cell->nodeValue));                    
                    break;                  
                };
              };
              ////////////////////////////////////////////
              // Montando o vetor para retornar ao JSON //
              ////////////////////////////////////////////
              array_push($data,[$codigo,$descricao,$rfid,$coduni,$erro]);
              /////////////////////////////////////////////////////
              // Guardando os inserts se não existir nenhum erro //
              /////////////////////////////////////////////////////
              $sql="INSERT INTO VMOTORISTA("          
                ."MTR_CODIGO"
                .",MTR_NOME"
                .",MTR_RFID"
                .",MTR_CODUNI"
                .",MTR_REG"
                .",MTR_CODUSR"
                .",MTR_ATIVO) VALUES("
                ."'$codigo'"                  // MTR_CODIGO
                .",'".$descricao."'"          // MTR_NOME
                .",'".$rfid."'"               // MTR_RFID
                .",'".$coduni."'"             // MTR_CODUNI
                .",'P'"                       // MTR_REG
                .",".$_SESSION["usr_codigo"]  // MTR_CODUSR
                .",'S'"                       // MTR_ATIVO
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
    <title>Motorista</title>
    <link rel="stylesheet" href="css/css2017.css">
    <link rel="stylesheet" href="css/cssTable2017.css">
    <link rel="stylesheet" href="css/cssFaTable.css">
    <script src="js/js2017.js"></script>
    <script src="js/jsTable2017.js"></script>
    <script src="tabelaTrac/f10/tabelaUnidadeF10.js"></script>  
    <script language="javascript" type="text/javascript"></script>
    <style>
      .comboSobreTable {
        position:relative;
        float:left;
        display:block;
        overflow-x:auto;
        width:110em;
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
        //         Objeto clsTable2017 MOTORISTA   //
        /////////////////////////////////////////////
        jsMtr={
          "titulo":[
             {"id":0  ,"labelCol":"OPC"     
                      ,"padrao":1}            
            ,{"id":1  ,"field"          :"MTR_CODIGO" 
                      ,"labelCol"       : "CODIGO"
                      ,"obj"            : "edtCodigo"
                      ,"fieldType"      : "int"
                      ,"formato"        : ["i4"]
                      ,"align"          : "center"                                      
                      ,"tamGrd"         : "5em"
                      ,"tamImp"         : "15"
                      ,"pk"             : "S"
                      ,"autoIncremento" : "S"
                      ,"newRecord"      : ["0000","this","this"]
                      ,"insUpDel"       : ["S","N","N"]
                      ,"validar"        : ["notnull"]
                      ,"ajudaCampo"     : [  "Codigo do motorista conforme definição empresa. Este campo é único e deve tem o formato 9999"
                                            ,"Campo deve ser utilizado em cadastros de Usuario"]
                      ,"importaExcel"   : "S"                                                                
                      ,"padrao":0}
            ,{"id":2  ,"field"          : "MTR_NOME"   
                      ,"labelCol"       : "DESCRICAO"
                      ,"obj"            : "edtDescricao"
                      ,"tamGrd"         : "32em"
                      ,"tamImp"         : "85"
                      ,"digitosMinMax"  : [3,60]
                      ,"digitosValidos" : "A|B|C|D|E|F|G|H|I|J|K|L|M|N|O|P|Q|R|S|T|U|V|W|X|Y|Z|0|1|2|3|4|5|6|7|8|9| "
                      ,"ajudaCampo"     : ["Nome do motorista com até 20 caracteres."]
                      ,"importaExcel"   : "S"                                          
                      ,"padrao":0}
            ,{"id":3  ,"field"          : "MTR_BIAB"   
                      ,"labelCol"       : "AB"
                      ,"obj"            : "edtBiAb"
                      ,"insUpDel"       : ["N","N","N"]
                      ,"newRecord"      : ["0000","this","this"]
                      ,"validar"        : ["podeNull"]
                      ,"fieldType"      : "int"
                      ,"align"          : "center"
                      ,"tamGrd"         : "4em"
                      ,"tamImp"         : "10"
                      ,"ajudaCampo"     : ["Total de aceleracao brusca."]
                      ,"importaExcel"   : "S"                                          
                      ,"padrao":0}
            ,{"id":4  ,"field"          : "MTR_BICB"   
                      ,"labelCol"       : "CB"
                      ,"obj"            : "edtBiCb"
                      ,"insUpDel"       : ["N","N","N"]
                      ,"newRecord"      : ["0000","this","this"]
                      ,"validar"        : ["podeNull"]
                      ,"fieldType"      : "int"
                      ,"align"          : "center"
                      ,"tamGrd"         : "4em"
                      ,"tamImp"         : "10"
                      ,"ajudaCampo"     : ["Total de condução banguela."]
                      ,"importaExcel"   : "S"                                          
                      ,"padrao":0}
            ,{"id":5  ,"field"          : "MTR_BIEV"   
                      ,"labelCol"       : "EV"
                      ,"obj"            : "edtBiEv"
                      ,"insUpDel"       : ["N","N","N"]
                      ,"newRecord"      : ["0000","this","this"]
                      ,"validar"        : ["podeNull"]
                      ,"fieldType"      : "int"
                      ,"align"          : "center"
                      ,"tamGrd"         : "4em"
                      ,"tamImp"         : "10"
                      ,"ajudaCampo"     : ["Total de excesso de velocidade."]
                      ,"importaExcel"   : "S"                                          
                      ,"padrao":0}
            ,{"id":6  ,"field"          : "MTR_BIEVC"   
                      ,"labelCol"       : "EVC"
                      ,"obj"            : "edtBiEvc"
                      ,"insUpDel"       : ["N","N","N"]
                      ,"validar"        : ["podeNull"]
                      ,"fieldType"      : "int"
                      ,"align"          : "center"
                      ,"tamGrd"         : "4em"
                      ,"tamImp"         : "10"
                      ,"ajudaCampo"     : ["Total de excesso de velocidade chuva."]
                      ,"importaExcel"   : "S"                                          
                      ,"padrao":0}
            ,{"id":7  ,"field"          : "MTR_BIERPM"   
                      ,"labelCol"       : "ERPM"
                      ,"obj"            : "edtBiErpm"
                      ,"insUpDel"       : ["N","N","N"]
                      ,"newRecord"      : ["0000","this","this"]
                      ,"validar"        : ["podeNull"]
                      ,"fieldType"      : "int"
                      ,"align"          : "center"
                      ,"tamGrd"         : "4em"
                      ,"tamImp"         : "10"
                      ,"ajudaCampo"     : ["Total de excesso de RPM."]
                      ,"importaExcel"   : "S"                                          
                      ,"padrao":0}
            ,{"id":8  ,"field"          : "MTR_BIFB"   
                      ,"labelCol"       : "FB"
                      ,"obj"            : "edtBiFb"
                      ,"insUpDel"       : ["N","N","N"]
                      ,"newRecord"      : ["0000","this","this"]
                      ,"validar"        : ["podeNull"]
                      ,"fieldType"      : "int"
                      ,"align"          : "center"
                      ,"tamGrd"         : "4em"
                      ,"tamImp"         : "10"
                      ,"ajudaCampo"     : ["Total de freada brusca."]
                      ,"importaExcel"   : "S"                                          
                      ,"padrao":0}
            ,{"id":9  ,"field"          : "MTR_BITOTAL"   
                      ,"labelCol"       : "TOTAL"
                      ,"obj"            : "edtBiTotal"
                      ,"insUpDel"       : ["N","N","N"]
                      ,"newRecord"      : ["0000","this","this"]
                      ,"validar"        : ["podeNull"]
                      ,"fieldType"      : "int"
                      ,"align"          : "center"
                      ,"tamGrd"         : "4em"
                      ,"tamImp"         : "10"
                      ,"ajudaCampo"     : ["Total de ocorrencias."]
                      ,"importaExcel"   : "S"                                          
                      ,"padrao":0}
            ,{"id":10 ,"field"          : "MTR_RFID"   
                      ,"labelCol"       : "RFID"
                      ,"obj"            : "edtRfid"
                      ,"validar"        : ["podeNull"]
                      ,"tamGrd"         : "10em"
                      ,"tamImp"         : "25"
                      ,"digitosValidos" : "A|B|C|D|E|F|G|H|I|J|K|L|M|N|O|P|Q|R|S|T|U|V|W|X|Y|Z|0|1|2|3|4|5|6|7|8|9|"
                      ,"ajudaCampo"     : ["Nome do motorista com até 20 caracteres."]
                      ,"importaExcel"   : "S"                                          
                      ,"padrao":0}
            ,{"id":11 ,"field"          : "MTR_CODUNI"   
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
            ,{"id":12 ,"field"          : "UNI_APELIDO"   
                      ,"insUpDel"       : ["N","N","N"]
                      ,"labelCol"       : "UNIDADE"
                      ,"obj"            : "edtDesUni"
                      ,"tamGrd"         : "10em"
                      ,"tamImp"         : "30"
                      ,"digitosMinMax"  : [3,15]
                      ,"validar"        : ["notnull"]                      
                      ,"ajudaCampo"     : ["Nome da unidade."]
                      ,"padrao":0}
            ,{"id":13 //,"field"          : "MTR_POSICAO"   
                      ,"labelCol"       : "POSICAO"
                      //,"obj"            : "edtPosicao"
                      ,"insUpDel"       : ["N","N","N"]
                      //,"newRecord"      : ["0","this","this"]
                      //,"validar"        : ["podeNull"]
                      ,"fieldType"      : "int"
                      //,"align"          : "center"
                      ,"tamGrd"         : "8em"
                      ,"tamImp"         : "15"
                      ,"ajudaCampo"     : ["Id systemsat."]
                      ,"importaExcel"   : "N"                                          
                      ,"padrao":0}
            ,{"id":14 ,"field"          : "MTR_ATIVO"  
                      ,"labelCol"       : "ATIVO"   
                      ,"obj"            : "cbAtivo"    
                      ,"padrao":2}                                        
            ,{"id":15 ,"field"          : "MTR_REG"    
                      ,"labelCol"       : "REG"     
                      ,"obj"            : "cbReg"      
                      ,"lblDetalhe"     : "REGISTRO"     
                      ,"ajudaDetalhe"   : "Se o registro é PUBlico/ADMinistrador ou do SIStema"                                         
                      ,"padrao":3}  
            ,{"id":16 ,"field"          : "US_APELIDO" 
                      ,"labelCol"       : "USUARIO" 
                      ,"obj"            : "edtUsuario" 
                      ,"padrao":4}                
            ,{"id":17 ,"field"          : "MTR_CODUSR" 
                      ,"labelCol"       : "CODUSU"  
                      ,"obj"            : "edtCodUsu"  
                      ,"padrao":5}                                   
            ,{"id":18 ,"labelCol"       : "PP"      
                      ,"obj"            : "imgPP"        
                      ,"func":"var elTr=this.parentNode.parentNode;"
                        +"elTr.cells[0].childNodes[0].checked=true;"
                        +"objMtr.espiao();"
                        +"elTr.cells[0].childNodes[0].checked=false;"
                      ,"padrao":8}
          ]
          ,
          "detalheRegistro":
          [
            { "width"           :"100%"
              ,"height"         :"300px" 
              ,"label"          :"MOTORISTA - Detalhe do registro"
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
          ,"idBtnConfirmarAtualizar" : "btnConfirmarAtualizar"            // Se existir executa o confirmar do form/fieldSet, e atualiza a grid depois
          ,"idBtnCancelar"  : "btnCancelar"             // Se existir executa o cancelar do form/fieldSet
          ,"div"            : "frmMtr"                  // Onde vai ser gerado a table
          ,"divFieldSet"    : "tabelaMtr"               // Para fechar a div onde estão os fieldset ao cadastrar
          ,"form"           : "frmMtr"                  // Onde vai ser gerado o fieldSet       
          ,"divModal"       : "divTopoInicio"           // Onde vai se appendado abaixo deste a table 
          ,"tbl"            : "tblMtr"                  // Nome da table
          ,"prefixo"        : "Mtr"                     // Prefixo para elementos do HTML em jsTable2017.js
          ,"tabelaBD"       : "VMOTORISTA"              // Nome da tabela no banco de dados  
          ,"tabelaBKP"      : "BKPMOTORISTA"            // Nome da tabela no banco de dados  
          ,"fieldAtivo"     : "MTR_ATIVO"               // SE EXISITIR - Nome do campo ATIVO(S/N) na tabela BD
          ,"fieldReg"       : "MTR_REG"                 // SE EXISITIR - Nome do campo SYS(P/A) na tabela BD            
          ,"fieldCodUsu"    : "MTR_CODUSR"              // SE EXISITIR - Nome do campo CODIGO USUARIO na tabela BD                        
          ,"iFrame"         : "iframeCorpo"             // Se a table vai ficar dentro de uma tag iFrame
          //,"temOpcao"       : "63em"                    // Se acima da div table vai existir opção de filtro
          ,"width"          : "110em"                   // Tamanho da table
          ,"height"         : "58em"                    // Altura da table
          ,"tableLeft"      : "sim"                     // Se tiver menu esquerdo
          ,"relTitulo"      : "MOTORISTA"               // Titulo do relatório
          ,"relOrientacao"  : "P"                       // Paisagem ou retrato
          ,"relFonte"       : "8"                       // Fonte do relatório
          ,"foco"           : ["edtDescricao"
                              ,"edtDescricao"
                              ,"btnConfirmarAtualizar"]          // Foco qdo Cad/Alt/Exc
          ,"formPassoPasso" : "Trac_Espiao.php"         // Enderço da pagina PASSO A PASSO
          ,"indiceTable"    : "DESCRICAO"               // Indice inicial da table
          ,"tamBotao"       : "15"                      // Tamanho botoes defalt 12 [12/25/50/75/100]
          ,"tamMenuTable"   : ["10em","20em"]                                
          ,"labelMenuTable" : "Opções"                  // Caption para menu table 
          ,"_menuTable"     :[
                                ["Regitros ativos"                        ,"fa-thumbs-o-up"   ,"btnFiltrarClick('S');"]
                               ,["Registros inativos"                     ,"fa-thumbs-o-down" ,"btnFiltrarClick('N');"]
                               ,["Todos"                                  ,"fa-folder-open"   ,"btnFiltrarClick('*');"]
                               ,["Importar planilha excel"                ,"fa-file-excel-o"  ,"fExcel()"]
                               ,["Imprimir registros em tela"             ,"fa-print"         ,"objMtr.imprimir()"]
                               ,["Ajuda para campos padrões"              ,"fa-info"          ,"objMtr.AjudaSisAtivo(jsMtr);"]
                               ,["Detalhe do registro"                    ,"fa-folder-o"      ,"objMtr.detalhe();"]
                               ,["Gerar excel"                            ,"fa-file-excel-o"  ,"objMtr.excel();"]
                               ,["Passo a passo do registro"              ,"fa-binoculars"    ,"objMtr.espiao();"]
                               ,["Alterar status Ativo/Inativo"           ,"fa-share"         ,"objMtr.altAtivo(intCodDir);"]
                               ,["Alterar registro PUBlico/ADMinistrador" ,"fa-reply"         ,"objMtr.altPubAdm(intCodDir,jsPub[0].usr_admpub);"]
															 ,["Número de registros em tela"            ,"fa-info"          ,"objMtr.numRegistros();"]
                               ,["Atualizar grade consulta"               ,"fa-filter"        ,"btnFiltrarClick('S');"]                               
                             ]  
          ,"codTblUsu"      : "MOTORISTA[04]"                          
          ,"codDir"         : intCodDir
        }; 
        if( objMtr === undefined ){  
          objMtr=new clsTable2017("objMtr");
        };  
        objMtr.montarHtmlCE2017(jsMtr); 
        //////////////////////////////////////////////////
        //          Fim objeto clsTable2017 MOTORISTA   //
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
            ,{"id":2  ,"field":"RFID"       ,"labelCol":"RFID"      ,"tamGrd":"15em"  ,"tamImp":"30"}
            ,{"id":3  ,"field":"CODUNI"     ,"labelCol":"UNIDADE"   ,"tamGrd":"32em"  ,"tamImp":"20"}
            ,{"id":4  ,"field":"ERRO"       ,"labelCol":"ERRO"      ,"tamGrd":"35em"  ,"tamImp":"100"}
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
        //btnFiltrarClick("S");  
        // buscarUni()
      });
      //
      var objMtr;                     // Obrigatório para instanciar o JS TFormaCob
      var jsMtr;                      // Obj principal da classe clsTable2017
      var objUniF10;                  // Obrigatório para instanciar o JS CidadeF10      
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
      var intCodDir = parseInt(jsPub[0].usr_d04);
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
          clsJs.add("rotina"      , "selectMtr"                                 );
          clsJs.add("login"       , jsPub[0].usr_login                          );
          clsJs.add("ativo"       , atv                                         );
          clsJs.add("coduni"      , document.getElementById("cbUnidade").value  );
          fd = new FormData();
          fd.append("motorista" , clsJs.fim());
          msg     = requestPedido("Trac_Motorista.php",fd); 
          retPhp  = JSON.parse(msg);
          if( retPhp[0].retorno == "OK" ){
            //////////////////////////////////////////////////////////////////////////////////
            // O novo array não tem o campo idUnico mas a montarHtmlCE2017 ja foi executada //
            // Campo obrigatório se existir rotina de manutenção na table devido Json       //
            // Esta rotina não tem manutenção via classe clsTable2017                       //
            // jsCrv.registros=objCrv.addIdUnico(retPhp[0]["dados"]);                       //
            //////////////////////////////////////////////////////////////////////////////////
            jsMtr.registros=objMtr.addIdUnico(retPhp[0]["dados"]);
            objMtr.ordenaJSon(jsMtr.indiceTable,false);  
            objMtr.montarBody2017();
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
          clsJs.add("rotina"      , "impExcel"          );
          clsJs.add("login"       , jsPub[0].usr_login  );
          clsJs.add("cabec"       , "CODIGO|DESCRICAO|RFID|CODUNI"  );          

          fd = new FormData();
          fd.append("motorista"      , clsJs.fim());
          fd.append("arquivo"   , edtArquivo.files[0] );
          msg     = requestPedido("Trac_Motorista.php",fd); 
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
          gerarMensagemErro("Mtr",retPhp[0].erro,"AVISO");    
        };  
      };
      ////////////////////////
      // AJUDA PARA UNIDADE //
      ////////////////////////
      function uniFocus(obj){ 
        document.getElementById(obj.id).setAttribute("data-oldvalue",document.getElementById(obj.id).value); 
      };
      function uniF10Click(){
         fUnidadeF10(0,"edtCodUni","cbAtivo","soAtivo");
       };  
      function RetF10tblUni(arr){
        document.getElementById("edtCodUni").value   = arr[0].CODIGO;
        document.getElementById("edtDesUni").value   = arr[0].APELIDO;
        document.getElementById("edtCodUni").setAttribute("data-oldvalue",arr[0].CODIGO);
      };
      function codUniBlur(obj){
        var elOld = jsNmrs(document.getElementById(obj.id).getAttribute("data-oldvalue")).inteiro().ret();
        var elNew = jsNmrs(obj.id).inteiro().ret();
        if( elOld != elNew ){
          var ret = fUnidadeF10(1,obj.id,"cbAtivo","soAtivo");
          document.getElementById(obj.id).value          = ( ret.length == 0 ? "0000"    : jsNmrs(ret[0].CODIGO).emZero(4).ret()  );
          document.getElementById("edtDesUni").value     = ( ret.length == 0 ? ""        : ret[0].APELIDO                         );
          document.getElementById(obj.id).setAttribute("data-oldvalue",( ret.length == 0 ? "0000" : ret[0].CODIGO )               );
        };
      };
    </script>
  </head>
  <body>
    <div id="loader" class="loader-out">
    </div>
    <div id="loading" class="fade-out">
  
    <div id="divEvento" class="comboSobreTable">

      <div style="margin-top:3px;margin-left:3px;">
        <?php include 'classPhp/comum/selectUnidade.class.php';?>
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
              name="frmMtr" 
              id="frmMtr" 
              class="frmTable" 
              action="classPhp/imprimirsql.php" 
              target="_newpage"
              style="top: 6em; width:90em;position: absolute; z-index:30;display:none;">
          <p class="frmCampoTit">
          <input class="informe" type="text" name="titulo" value="Motorista" disabled="" style="color: white; text-align: left;">
          </p>
          <div style="height: 200px; overflow-y: auto;">
            <input type="hidden" id="sql" name="sql"/>
            <div class="campotexto campo100">
              <div class="campotexto campo25">
                <input class="campo_input" id="edtCodigo" type="text" maxlength="5" />
                <label class="campo_label campo_required" for="edtCodigo">CODIGO</label>
              </div>
              <div class="campotexto campo75">
                <input class="campo_input" id="edtDescricao" type="text" maxlength="60" />
                <label class="campo_label campo_required" for="edtDescricao">DESCRICAO</label>
              </div>
              <div class="campotexto campo15">
                <input class="campo_input" id="edtRfid" type="text" maxlength="15" />
                <label class="campo_label" for="edtRfid">RFID</label>
              </div>
              <div class="campotexto campo10">
                <input class="campo_input inputF10" id="edtCodUni"
                                                    OnKeyPress="return mascaraInteiro(event);"
                                                    onBlur="codUniBlur(this);" 
                                                    onFocus="uniFocus(this);" 
                                                    onClick="uniF10Click('edtCodUni');"
                                                    data-oldvalue=""
                                                    autocomplete="off" 
                                                    maxlength="4"
                                                    type="text" />
                <label class="campo_label campo_required" for="edtCodUni">UNIDADE</label>
              </div>
              <div class="campotexto campo25">
                <input class="campo_input_titulo input" id="edtDesUni" type="text" disabled />
                <label class="campo_label campo_required" for="edtDesUni">RAZAO_UNIDADE</label>
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
              <div class="campotexto campo25" style="display: none">
                <input class="campo_input_titulo" disabled id="edtUsuario" type="text" />
                <label class="campo_label campo_required" for="edtUsuario">USUARIO</label>
              </div>
              <div class="inactive">
                <input id="edtCodUsu" type="text" />
                <input id="edtBiAb" type="text" />
                <input id="edtBiCb" type="text" />
                <input id="edtBiEv" type="text" />
                <input id="edtBiEvc" type="text" />
                <input id="edtBiErpm" type="text" />
                <input id="edtBiFb" type="text" />
                <input id="edtBiTotal" type="text" />
              </div>
              <div class="campotexto campo100">
                <div class="campotexto campo50" style="margin-top:1em;">
                  <label class="campo_required" style="font-size:1.4em;"></label>
                  <label class="campo_labelSombra">Campo obrigatório</label>
                </div>              
                <div class="campo20" style="float:right;">            
                  <input id="btnConfirmarAtualizar" type="button" value="Confirmar" class="campo100 tableBotao botaoForaTable"/>
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
    </div>
  </body>
</html>