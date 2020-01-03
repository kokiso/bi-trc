<?php
  session_start();
  if( isset($_POST["usuariounidade"]) ){
    try{     
      require("classPhp/conectaSqlServer.class.php");
      require("classPhp/validaJson.class.php"); 
      require("classPhp/removeAcento.class.php"); 

      $vldr     = new validaJSon();          
      $retorno  = "";
      $retCls   = $vldr->validarJs($_POST["usuariounidade"]);
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
        ////////////////////////////////////////////
        //  Dados para JavaScript USUARIOUNIDADE //
        ////////////////////////////////////////////
        if( $rotina=="selectUo" ){
          $sql="SELECT A.UU_CODUSR
                       ,USR.USR_APELIDO AS USR_FUNCIONARIO
                       ,GRP.GRP_APELIDO
                       ,A.UU_CODUNI
                       ,UNI.UNI_APELIDO
                       ,UNI.UNI_CODPOL
                       ,CASE WHEN A.UU_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS UU_ATIVO
                       ,CASE WHEN A.UU_REG='P' THEN 'PUB' WHEN A.UU_REG='S' THEN 'SIS' ELSE 'ADM' END AS UU_REG
                       ,US.US_APELIDO AS US_USUARIO
                       ,A.SIS_CODUSR
                  FROM USUARIOUNIDADE A
                  LEFT OUTER JOIN USUARIO USR ON A.UU_CODUSR=USR.USR_CODIGO                  
                  LEFT OUTER JOIN USUARIOSISTEMA US ON A.SIS_CODUSR=US.US_CODIGO
                  LEFT OUTER JOIN UNIDADE UNI ON A.UU_CODUNI=UNI.UNI_CODIGO
                  LEFT OUTER JOIN GRUPO GRP ON UNI.UNI_CODGRP=GRP.GRP_CODIGO
                  INNER JOIN USUARIOPERFIL P ON USR.USR_CODUP=P.UP_CODIGO ";
                  if ($_SESSION['usr_grupoPerfil'] != "0") {
                     $sql.=" AND P.UP_GRUPO =".$_SESSION['usr_grupoPerfil'];
                  }
          $sql.=" WHERE (UU_ATIVO='".$lote[0]->ativo."') OR ('*'='".$lote[0]->ativo."')";                
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
                array_push($data,["0000","0000","LINHA 1 DEVE SER ".$lote[0]->cabec]);
                $strExcel   = "N";
              };
            } else {
              $erro="OK";
              
              $linC = -1;
              foreach($cells as $cell){
                $linC++;
                switch( $linC ){
                  case 0:                  
                    $codusr=preg_replace('/[^0-9]/', '', trim($cell->nodeValue));
                    if( strlen(trim($codusr)) == 0 ){ 
                      $erro     = "CAMPO CODIGO DEVE TER UM INTEIRO VALIDO";
                      $strExcel = "N";
                    } else {
                      $codigo=str_pad($codusr, 4, "0", STR_PAD_LEFT);
                    };  
                    break;
                  case 1:                  
                    $coduni=preg_replace('/[^0-9]/', '', trim($cell->nodeValue));
                    if( strlen(trim($coduni)) == 0 ){ 
                      $erro     = "CAMPO CODIGO DEVE TER UM INTEIRO VALIDO";
                      $strExcel = "N";
                    } else {
                      $coduni=str_pad($coduni, 4, "0", STR_PAD_LEFT);
                    };  
                    break;
                };
              };
              ////////////////////////////////////////////
              // Montando o vetor para retornar ao JSON //
              ////////////////////////////////////////////
              array_push($data,[$codusr,$coduni,$erro]);
              /////////////////////////////////////////////////////
              // Guardando os inserts se não existir nenhum erro //
              /////////////////////////////////////////////////////
              $sql="INSERT INTO USUARIOUNIDADE("          
                ."UU_CODUSR"
                .",UU_CODUNI"
                .",UU_REG"
                .",SIS_CODUSR"
                .",UU_ATIVO) VALUES("
                ."'$codusr'"                  // UU_CODUSR
                .",".$coduni                  // UU_CODUNI
                .",'P'"                       // UU_REG
                .",".$_SESSION["usr_codigo"]  // SIS_CODUSR
                .",'S'"                       // UU_ATIVO
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
    <title>Direitos-Usuario/Unidade</title>
    <link rel="stylesheet" href="css/css2017.css">
    <link rel="stylesheet" href="css/cssTable2017.css">
    <link rel="stylesheet" href="css/cssFaTable.css">
    <script src="js/js2017.js"></script>
    <script src="js/jsTable2017.js"></script>
    <script src="tabelaTrac/f10/tabelaUnidadeF10.js"></script>    
    <script src="tabelaTrac/f10/tabelaUsuarioF10.js"></script>    
    <script language="javascript" type="text/javascript"></script>
    <script>
      "use strict";
      ////////////////////////////////////////////////
      // Executar o codigo após a pagina carregada  //
      ////////////////////////////////////////////////
      document.addEventListener("DOMContentLoaded", function(){ 
        //////////////////////////////////////////////
        //   Objeto clsTable2017 USUARIOUNIDADE     //
        //////////////////////////////////////////////
        jsUo={
          "titulo":[
             {"id":0  ,"labelCol":"OPC"     
                      ,"padrao":1}            
            ,{"id":1  ,"field"          :"UU_CODUSR" 
                      ,"labelCol"       : "CODUSR"
                      ,"obj"            : "edtCodUsr"
                      //,"tamGrd"         : "0em"
                      //,"tamImp"         : "15"
                      ,"fieldType"      : "int"
                      ,"align"          : "center"                                      
                      ,"pk"             : "S"
                      ,"newRecord"      : ["0000","this","this"]
                      ,"insUpDel"       : ["S","N","N"]
                      ,"formato"        : ["i4"]
                      ,"validar"        : ["notnull","intMaiorZero"]
                      ,"ajudaCampo"     : ["Codigo do usuario. Este deve existir e estar ativo na tabela de usuarios"]
                      ,"importaExcel"   : "S"                                                                
                      ,"padrao":0}
            ,{"id":2  ,"field"          : "USR_FUNCIONARIO"   
                      ,"insUpDel"       : ["N","N","N"]
                      ,"labelCol"       : "FUNCIONARIO"
                      ,"obj"            : "edtDesUsr"
                      ,"tamGrd"         : "15em"
                      ,"tamImp"         : "30"
                      ,"newRecord"      : ["","this","this"]
                      ,"digitosMinMax"  : [1,20]
                      ,"validar"        : ["notnull"]                      
                      ,"ajudaCampo"     : ["Nome resumido do usuario."]
                      ,"padrao":0}
            ,{"id":3  ,"field"          : "GRP_APELIDO"   
                      ,"insUpDel"       : ["N","N","N"]
                      ,"labelCol"       : "GRUPO"
                      ,"obj"            : "edtDesGrp"
                      ,"tamGrd"         : "15em"
                      ,"tamImp"         : "30"
                      ,"newRecord"      : ["","this","this"]
                      ,"validar"        : ["notnull"]                      
                      ,"ajudaCampo"     : ["Nome resumido do cliente."]
                      ,"padrao":0}
            ,{"id":4  ,"field"          :"UU_CODUNI" 
                      ,"labelCol"       : "CODUNI"
                      ,"obj"            : "edtCodUni"
                      //,"tamGrd"         : "0em"
                      //,"tamImp"         : "15"
                      ,"fieldType"      : "int"
                      ,"align"          : "center"                                      
                      ,"pk"             : "S"
                      ,"newRecord"      : ["0000","this","this"]
                      ,"insUpDel"       : ["S","N","N"]
                      ,"formato"        : ["i4"]
                      ,"validar"        : ["notnull","intMaiorZero"]
                      ,"ajudaCampo"     : ["Codigo da Unidade. Este deve existir e estar ativo na tabela de unidade"]
                      ,"importaExcel"   : "S"                                                                
                      ,"padrao":0}
            ,{"id":5  ,"field"          : "UNI_APELIDO"   
                      ,"insUpDel"       : ["N","N","N"]
                      ,"labelCol"       : "UNIDADE"
                      ,"obj"            : "edtDesUni"
                      ,"tamGrd"         : "15em"
                      ,"tamImp"         : "30"
                      ,"newRecord"      : ["","this","this"]
                      ,"digitosMinMax"  : [1,20]
                      ,"validar"        : ["notnull"]                      
                      ,"ajudaCampo"     : ["Nome resumido da empresa."]
                      ,"padrao":0}
            ,{"id":6  ,"field"          : "UNI_CODPOL"   
                      ,"insUpDel"       : ["N","N","N"]
                      ,"labelCol"       : "POLO"
                      ,"obj"            : "edtCodPol"
                      ,"tamGrd"         : "4em"
                      ,"tamImp"         : "10"
                      ,"newRecord"      : ["","this","this"]
                      ,"digitosMinMax"  : [1,3]
                      ,"validar"        : ["notnull"]                      
                      ,"ajudaCampo"     : ["Nome resumido do polo."]
                      ,"padrao":0}
            ,{"id":7  ,"field"          : "UU_ATIVO"  
                      ,"labelCol"       : "DIREITO"   
                      ,"obj"            : "cbAtivo"    
                      ,"padrao":2}                                        
            ,{"id":8  ,"field"          : "UU_REG"    
                      ,"labelCol"       : "REG"     
                      ,"obj"            : "cbReg"      
                      ,"lblDetalhe"     : "REGISTRO"     
                      ,"ajudaDetalhe"   : "Se o registro é PUBlico/ADMinistrador ou do SIStema"                                         
                      ,"padrao":3}  
            ,{"id":9  ,"field"          : "US_APELIDO" 
                      ,"labelCol"       : "USUARIO" 
                      ,"obj"            : "edtUsuario" 
                      ,"padrao":4}                
            ,{"id":10 ,"field"          : "SIS_CODUSR" 
                      ,"labelCol"       : "CODUSU"  
                      ,"obj"            : "edtCodUsu"  
                      ,"padrao":5}                                      
            ,{"id":11 ,"labelCol"       : "PP"      
                      ,"obj"            : "imgPP"        
                      ,"func":"var elTr=this.parentNode.parentNode;"
                        +"elTr.cells[0].childNodes[0].checked=true;"
                        +"objUo.espiao();"
                        +"elTr.cells[0].childNodes[0].checked=false;"
                      ,"padrao":8}
          ]
          ,
          "detalheRegistro":
          [
            { "width"           :"100%"
              ,"height"         :"300px" 
              ,"label"          :"USUARIOUNIDADE - Detalhe do registro"
            }
          ]
          , 
          "botoesH":[
             {"texto":"Cadastrar" ,"name":"horCadastrar"  ,"onClick":"0"  ,"enabled":true ,"imagem":"fa fa-plus"             ,"ajuda":"Novo registro" }
            ,{"texto":"Alterar"   ,"name":"horAlterar"    ,"onClick":"1"  ,"enabled":true ,"imagem":"fa fa-pencil-square-o"  ,"ajuda":"Alterar registro selecionado" }
            ,{"texto":"Excel"     ,"name":"horExcel"      ,"onClick":"5"  ,"enabled":false,"imagem":"fa fa-file-excel-o"     ,"ajuda":"Exportar para excel" }        
            ,{"texto":"Fechar"    ,"name":"horFechar"     ,"onClick":"8"  ,"enabled":true ,"imagem":"fa fa-close"            ,"ajuda":"Fechar formulario" }
          ] 
          ,"registros"      : []                    // Recebe um Json vindo da classe clsBancoDados
          ,"opcRegSeek"     : true                  // Opção para numero registros/botão/procurar                     
          ,"checarTags"     : "S"                   // Somente em tempo de desenvolvimento(olha as pricipais tags)                  
          ,"idBtnConfirmar" : "btnConfirmar"        // Se existir executa o confirmar do form/fieldSet
          ,"idBtnCancelar"  : "btnCancelar"         // Se existir executa o cancelar do form/fieldSet
          ,"div"            : "frmUo"               // Onde vai ser gerado a table
          ,"divFieldSet"    : "tabelaUo"            // Para fechar a div onde estão os fieldset ao cadastrar
          ,"form"           : "frmUo"               // Onde vai ser gerado o fieldSet       
          ,"divModal"       : "divTopoInicio"       // Onde vai se appendado abaixo deste a table 
          ,"tbl"            : "tblUo"               // Nome da table
          ,"prefixo"        : "Uo"                  // Prefixo para elementos do HTML em jsTable2017.js
          ,"tabelaBD"       : "USUARIOUNIDADE"      // Nome da tabela no banco de dados  
          ,"tabelaBKP"      : "BKPUSUARIOUNIDADE"   // Nome da tabela no banco de dados  
          ,"fieldAtivo"     : "UU_ATIVO"            // SE EXISITIR - Nome do campo ATIVO(S/N) na tabela BD
          ,"fieldReg"       : "UU_REG"              // SE EXISITIR - Nome do campo SYS(P/A) na tabela BD            
          ,"fieldCodUsu"    : "SIS_CODUSR"          // SE EXISITIR - Nome do campo CODIGO USUARIO na tabela BD                        
          ,"iFrame"         : "iframeCorpo"         // Se a table vai ficar dentro de uma tag iFrame
          //,"fieldCodEmp"  : "*"                   // SE EXISITIR - Nome do campo CODIGO EMPRESA na tabela BD            
          //,"fieldCodDir"  : "*"                   // SE EXISITIR - Nome do campo CODIGO DIREITO na tabela BD                        
          ,"width"          : "94em"                // Tamanho da table
          ,"height"         : "58em"                // Altura da table
          ,"tableLeft"      : "sim"                 // Se tiver menu esquerdo
          ,"relTitulo"      : "DIREITO DE USUARIO"  // Titulo do relatório
          ,"relOrientacao"  : "R"                   // Paisagem ou retrato
          ,"relFonte"       : "8"                   // Fonte do relatório
          ,"foco"           : ["edtCodUsr"
                              ,"cbAtivo"
                              ,"btnConfirmar"]      // Foco qdo Cad/Alt/Exc
          //,"formName"     : "frmPais"             // Nome do formulario para opção de impressão 
          ,"formPassoPasso" : "Atlas_Espiao.php"    // Enderço da pagina PASSO A PASSO
          ,"indiceTable"    : "DESCRICAO"           // Indice inicial da table
          ,"tamBotao"       : "20"                  // Tamanho botoes defalt 12 [12/25/50/75/100]
          ,"tamMenuTable"   : ["10em","20em"]                                
          ,"labelMenuTable" : "Opções"              // Caption para menu table 
          ,"_menuTable"     :[
                                ["Direito SIM"                            ,"fa-thumbs-o-up"   ,"btnFiltrarClick('S');"]
                               ,["Direito NÃO"                            ,"fa-thumbs-o-down" ,"btnFiltrarClick('N');"]
                               ,["Todos"                                  ,"fa-folder-open"   ,"btnFiltrarClick('*');"]
                               ,["Importar planilha excel"                ,"fa-file-excel-o"  ,"fExcel()"]
                               ,["Imprimir registros em tela"             ,"fa-print"         ,"objUo.imprimir()"]
                               ,["Ajuda para campos padrões"              ,"fa-info"          ,"objUo.AjudaSisAtivo(jsUo);"]
                               ,["Detalhe do registro"                    ,"fa-folder-o"      ,"objUo.detalhe();"]
                               ,["Gerar excel"                            ,"fa-file-excel-o"  ,"objUo.excel();"]
                               ,["Passo a passo do registro"              ,"fa-binoculars"    ,"objUo.espiao();"]
                               ,["Alterar status de direito"              ,"fa-share"         ,"objUo.altAtivo(intCodDir);"]
                               ,["Alterar registro PUBlico/ADMinistrador" ,"fa-reply"         ,"objUo.altPubAdm(intCodDir,jsPub[0].usr_admpub);"]
                               ,["Marcar/Desmarcar REGISTROS"             ,"fa-reply"         ,"objUo.marcarDesmarcar();"]                               
                               ,["Atualizar grade consulta"               ,"fa-filter"        ,"btnFiltrarClick('S');"]                               
                             ]  
          ,"codTblUsu"      : "USUARIO->UNIDADE[02]"                          
          ,"codDir"         : intCodDir
        }; 
        if( objUo === undefined ){  
          objUo=new clsTable2017("objUo");
        };  
        objUo.montarHtmlCE2017(jsUo); 
        //////////////////////////////////////////////////
        //    Fim objeto clsTable2017 USUARIOUNIDADE    //
        ////////////////////////////////////////////////// 
        //
        //
        //////////////////////////////////////////////
        // Montando a table para importar xls       //
        //////////////////////////////////////////////
        jsExc={
          "titulo":[
             {"id":0  ,"field":"CODUSR"  ,"labelCol":"USUARIO" ,"tamGrd":"6em"   ,"tamImp":"30"}
            ,{"id":1  ,"field":"CODUNI"  ,"labelCol":"UNIDADE" ,"tamGrd":"6em"  ,"tamImp":"30"}
            ,{"id":2  ,"field":"ERRO"    ,"labelCol":"ERRO"    ,"tamGrd":"35em"  ,"tamImp":"100"}
          ]
          ,"botoesH":[
             {"texto":"Imprimir"  ,"name":"excImprimir"   ,"onClick":"3"  ,"enabled":true ,"imagem":"fa fa-print"            ,"ajuda":"Imprimir registros em tela" }        
            ,{"texto":"Excel"     ,"name":"excExcel"      ,"onClick":"5"  ,"enabled":false,"imagem":"fa fa-file-excel-o"     ,"ajuda":"Exportar para excel" }        
            ,{"texto":"Fechar"    ,"name":"excFechar"     ,"onClick":"7"  ,"enabled":true ,"imagem":"fa fa-close"            ,"ajuda":"Fechar formulario" }
          ] 
          ,"registros"      : []                    // Recebe um Json vindo da classe clsBancoDados
          ,"corLinha"       : "if(ceTr.cells[2].innerHTML !='OK') {ceTr.style.color='yellow';ceTr.style.backgroundColor='red';}"      
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
          ,"relTitulo"      : "Importação Uo"       // Titulo do relatório
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
      var objUo;                      // Obrigatório para instanciar o JS TFormaCob
      var jsUo;                       // Obj principal da classe clsTable2017
      var objExc;                     // Obrigatório para instanciar o JS Importar excel
      var jsExc;                      // Obrigatório para instanciar o objeto objExc
      var objUniF10;                  // Obrigatório para instanciar o JS FilialF10          
      var objUsrF10;                  // Obrigatório para instanciar o JS UsuarioF10          
      var clsJs;                      // Classe responsavel por montar um Json e eviar PHP
      var clsErro;                    // Classe para erros            
      var fd;                         // Formulario para envio de dados para o PHP
      var msg;                        // Variavel para guardadar mensagens de retorno/erro 
      var retPhp                      // Retorno do Php para a rotina chamadora
      var contMsg   = 0;              // contador para mensagens
      var cmp       = new clsCampo(); // Abrindo a classe campos
      var jsPub     = JSON.parse(localStorage.getItem("lsPublico"));
      var intCodDir = parseInt(jsPub[0].usr_d02);
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
        clsJs.add("rotina"      , "selectUo"          );
        clsJs.add("login"       , jsPub[0].usr_login  );
        clsJs.add("ativo"       , atv                 );
        fd = new FormData();
        fd.append("usuariounidade" , clsJs.fim());
        msg     = requestPedido("Trac_UsuarioUnidade.php",fd); 
        retPhp  = JSON.parse(msg);
        if( retPhp[0].retorno == "OK" ){
          //////////////////////////////////////////////////////////////////////////////////
          // O novo array não tem o campo idUnico mas a montarHtmlCE2017 ja foi executada //
          // Campo obrigatório se existir rotina de manutenção na table devido Json       //
          // Esta rotina não tem manutenção via classe clsTable2017                       //
          // jsCrv.registros=objCrv.addIdUnico(retPhp[0]["dados"]);                       //
          //////////////////////////////////////////////////////////////////////////////////
          jsUo.registros=objUo.addIdUnico(retPhp[0]["dados"]);
          objUo.ordenaJSon(jsUo.indiceTable,false);  
          objUo.montarBody2017();
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
          clsJs.add("cabec"       , "CODUSR|CODUNI"  );          

          fd = new FormData();
          fd.append("usuariounidade"      , clsJs.fim());
          fd.append("arquivo"   , edtArquivo.files[0] );
          msg     = requestPedido("Trac_UsuarioUnidade.php",fd);
                                             
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
          gerarMensagemErro("Uo",retPhp[0].erro,"AVISO");    
        };  
      };
      ////////////////////////
      // AJUDA PARA USUARIO //
      ////////////////////////
      function usrFocus(obj){ 
        document.getElementById(obj.id).setAttribute("data-oldvalue",document.getElementById(obj.id).value); 
      };
      function usrF10Click(){ fUsuarioF10(0,"edtCodUsr","edtCodUni"); };  
      function RetF10tblUsr(arr){
        document.getElementById("edtCodUsr").value     = arr[0].CODIGO;
        document.getElementById("edtDesUsr").value    = arr[0].DESCRICAO;
        document.getElementById("edtCodUsr").setAttribute("data-oldvalue",arr[0].CODIGO);
      };
      function codUsrBlur(obj){
        var elOld = jsNmrs(document.getElementById(obj.id).getAttribute("data-oldvalue")).inteiro().ret();
        var elNew = jsNmrs(obj.id).inteiro().ret();
        if( elOld != elNew ){
          var ret = fUsuarioF10(1,obj.id,"edtCodUni");
          document.getElementById(obj.id).value         = ( ret.length == 0 ? "0000"    : jsNmrs(ret[0].CODIGO).emZero(4).ret() );
          document.getElementById("edtDesUsr").value     = ( ret.length == 0 ? ""        : ret[0].DESCRICAO                      );
          document.getElementById(obj.id).setAttribute("data-oldvalue",( ret.length == 0 ? "0000" : ret[0].CODIGO )             );
        };
      };
      //////////////////////////
      // AJUDA PARA UNIDADE   //
      //////////////////////////
      function uniFocus(obj){ 
        document.getElementById(obj.id).setAttribute("data-oldvalue",document.getElementById(obj.id).value); 
      };
      // Em todas chamadas tragos somente soAtivo, aqui tenho que trazer todos
      function uniF10Click(){ fUnidadeF10(0,"edtCodUni","cbAtivo","soAtivo"); };  
      function RetF10tblUni(arr){
        // debugger;
        document.getElementById("edtCodUni").value    = arr[0].CODIGO;
        document.getElementById("edtDesUni").value    = arr[0].APELIDO;
        document.getElementById("edtDesGrp").value    = arr[0].GRUPO;
        document.getElementById("edtCodPol").value    = arr[0].POLO;
        document.getElementById("edtCodUni").setAttribute("data-oldvalue",arr[0].CODIGO);
      };
      function codUniBlur(obj){
        // debugger;        
        var elOld = jsNmrs(document.getElementById(obj.id).getAttribute("data-oldvalue")).inteiro().ret();
        var elNew = jsNmrs(obj.id).inteiro().ret();
        if( elOld != elNew ){
          var ret = fUnidadeF10(1,obj.id,"cbAtivo","soAtivo");
          document.getElementById(obj.id).value           = ( ret.length == 0 ? "0000"    : jsNmrs(ret[0].CODIGO).emZero(4).ret() );
          document.getElementById("edtDesUni").value      = ( ret.length == 0 ? ""        : ret[0].APELIDO                        );
          document.getElementById("edtDesGrp").value      = ( ret.length == 0 ? ""        : ret[0].GRUPO                          );
          document.getElementById("edtCodPol").value      = ( ret.length == 0 ? ""        : ret[0].POLO                           );
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
              name="frmUo" 
              id="frmUo" 
              class="frmTable" 
              action="classPhp/imprimirsql.php" 
              target="_newpage"
              style="top: 5em; width:77em; position: absolute; z-index:30;display:none;">
          <p class="frmCampoTit">
          <input class="informe" type="text" name="titulo" value="Usuario/Unidade" disabled="" style="color: white; text-align: left;">
          </p>
          <div style="height: 200px; overflow-y: auto;">
          <input type="hidden" id="sql" name="sql"/>
            <div class="campotexto campo100">
              <div class="campotexto campo15">
                <input class="campo_input inputF10" id="edtCodUsr"
                                                    onBlur="codUsrBlur(this);" 
                                                    onFocus="usrFocus(this);" 
                                                    onClick="usrF10Click('edtCodUsr');"
                                                    data-oldvalue="0000" 
                                                    autocomplete="off" 
                                                    type="text" />
                <label class="campo_label campo_required" for="edtCodUsr">USUARIO:</label>
              </div>
              <div class="campotexto campo35">
                <input class="campo_input_titulo input" id="edtDesUsr" type="text" disabled />
                <label class="campo_label campo_required" for="edtDesUsr">NOME_USUARIO:</label>
              </div>
            
              <div class="campotexto campo15">
                <input class="campo_input inputF10" id="edtCodUni"
                                                    onBlur="codUniBlur(this);" 
                                                    onFocus="uniFocus(this);" 
                                                    onClick="uniF10Click('edtCodUni');"
                                                    data-oldvalue="0000" 
                                                    autocomplete="off" 
                                                    type="text" />
                <label class="campo_label campo_required" for="edtCodUni">UNIDADE:</label>
              </div>
              <div class="campotexto campo35">
                <input class="campo_input_titulo input" id="edtDesUni" type="text" disabled />
                <label class="campo_label campo_required" for="edtDesUni">RAZÃO SOCIAL:</label>
              </div>
              <div class="campotexto campo10">
                <input class="campo_input_titulo input" id="edtCodPol" type="text" disabled />
                <label class="campo_label campo_required" for="edtCodPol">POLO:</label>
              </div>
              <div class="campotexto campo15">
                <input class="campo_input_titulo input" id="edtDesGrp" type="text" disabled />
                <label class="campo_label campo_required" for="edtDesGrp">GRUPO:</label>
              </div>
              
              <div class="campotexto campo25">
                <select class="campo_input_combo" id="cbAtivo">
                  <option value="S">SIM</option>
                  <option value="N">NAO</option>
                </select>
                <label class="campo_label campo_required" for="cbAtivo">DIREITO</label>
              </div>
              <div class="campotexto campo25">
                <select class="campo_input_combo" id="cbReg">
                  <option value="P">PUBLICO</option>               
                </select>
                <label class="campo_label campo_required" for="cbReg">REG</label>
              </div>
              <div class="campotexto campo25">
                <input class="campo_input_titulo" disabled id="edtUsuario" type="text" />
                <label class="campo_label campo_required" for="edtUsuario">USUARIO</label>
              </div>
              <div class="inactive">
                <input id="edtCodUsu" type="text" />
                <input id="edtDesGrp" type="text" />
              </div>
              <div class="campotexto campo100"></div>
              <div class="campotexto campo100">
                <div class="campotexto campo50" style="margin-top:1em;">
                  <label class="campo_required" style="font-size:1.4em;"></label>
                  <label class="campo_labelSombra">Campo obrigatório</label>
                </div>              
                <div class="campo20" style="float:right;">            
                  <input id="btnConfirmar" type="button" value="Confirmar" class="campo100 tableBotao botaoForaTable"/>            
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
      <!--/Importar excel -->
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