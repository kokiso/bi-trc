<?php
  session_start();
  if( isset($_POST["polo"]) ){
    try{     
      require("classPhp/conectaSqlServer.class.php");
      require("classPhp/validaJson.class.php"); 
      require("classPhp/removeAcento.class.php"); 

      $vldr     = new validaJSon();          
      $retorno  = "";
      $retCls   = $vldr->validarJs($_POST["polo"]);
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
        ////////////////////////////////////////////////
        //         Dados para JavaScript POLO         //
        ////////////////////////////////////////////////
        if( $rotina=="selectPol" ){
          $sql="SELECT POL_CODIGO
                       ,POL_NOME
                       ,POL_CODGRP
                       ,GRP.GRP_APELIDO
                       ,CASE WHEN A.POL_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS POL_ATIVO
                       ,CASE WHEN A.POL_REG='P' THEN 'PUB' WHEN A.POL_REG='S' THEN 'SIS' ELSE 'ADM' END AS POL_REG
                       ,US_APELIDO
                       ,POL_CODUSR
                  FROM POLO A
                  LEFT OUTER JOIN USUARIOSISTEMA U ON A.POL_CODUSR=U.US_CODIGO
                  LEFT OUTER JOIN GRUPO GRP ON A.POL_CODGRP=GRP.GRP_CODIGO                  
                 WHERE (((POL_ATIVO='".$lote[0]->ativo."') OR ('*'='".$lote[0]->ativo."')) AND (A.POL_CODGRP IN".$_SESSION["usr_grupos"]."))";
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
        /*
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
                array_push($data,["0000","CABECALHO","LINHA 1 DEVE SER ".$lote[0]->cabec]);
                $strExcel   = "N";
              };
            } else {
              $erro="OK";
              
              $linC = -1;
              foreach($cells as $cell){
                $linC++;
                switch( $linC ){
                  case 0: 
                    $clsRa->montaRetorno($cell->nodeValue);
                    $codigo=$clsRa->getNome();
                    if( (strlen($codigo)<1) or (strlen($codigo)>5) ){ 
                      $erro     = "CAMPO CODIGO DEVE TER TAMANHO 01..05";
                      $strExcel = "N";
                    };
                    $achei=false;
                    foreach( $arrDup as $lin ){
                      if( $lin["DESCRICAO"]==$codigo ){
                        $erro     = "CODIGO DUPLICADO NA PLANILHA";
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
                  case 1: 
                    $clsRa->montaRetorno($cell->nodeValue);
                    $descricao=$clsRa->getNome();
                    if( (strlen($descricao)<1) or (strlen($descricao)>20) ){ 
                      $erro     = "CAMPO DESCRICAO DEVE TER TAMANHO 01..20";
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
                };
              };
              ////////////////////////////////////////////
              // Montando o vetor para retornar ao JSON //
              ////////////////////////////////////////////
              array_push($data,[$codigo,$descricao,$erro]);
              /////////////////////////////////////////////////////
              // Guardando os inserts se não existir nenhum erro //
              /////////////////////////////////////////////////////
              $sql="INSERT INTO POLO("          
                ."POL_CODIGO"
                .",POL_NOME"
                .",POL_REG"
                .",POL_CODUSR"
                .",POL_ATIVO) VALUES("
                ."'$codigo'"                  // POL_CODIGO
                .",'".$descricao."'"          // POL_NOME
                .",'P'"                       // POL_REG
                .",".$_SESSION["usr_codigo"]  // POL_CODUSR
                .",'S'"                       // POL_ATIVO
              .")";
              array_push($arrUpdt,$sql);
            };
          };
        };
        */
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
    <title>Polo</title>
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
        /////////////////////////////////////////////
        //         Objeto clsTable2017 POLO        //
        /////////////////////////////////////////////
        jsPol={
          "titulo":[
             {"id":0  ,"labelCol":"OPC"     
                      ,"padrao":1}            
            ,{"id":1  ,"field"          :"POL_CODIGO" 
                      ,"labelCol"       : "CODIGO"
                      ,"obj"            : "edtCodigo"
                      ,"tamGrd"         : "5em"
                      ,"tamImp"         : "15"
                      ,"digitosValidos" : "A|B|C|D|E|F|G|H|I|J|K|L|M|N|O|P|Q|R|S|T|U|V|W|X|Y|Z"
                      ,"pk"             : "S"
                      ,"newRecord"      : ["","this","this"]
                      ,"insUpDel"       : ["S","N","N"]
                      ,"digitosMinMax"  : [2,3] 
                      ,"formato"        : ["uppercase","removeacentos","tiraaspas","alltrim"]                      
                      ,"validar"        : ["notnull"]
                      ,"ajudaCampo"     : [  "Codigo do polo conforme definição empresa. Este campo é único e deve tem o formato AAA"
                                            ,"Campo deve ser utilizado em cadastros de Usuario"]
                      ,"importaExcel"   : "S"                                                                
                      ,"padrao":0}
            ,{"id":2  ,"field"          : "POL_NOME"   
                      ,"labelCol"       : "DESCRICAO"
                      ,"obj"            : "edtDescricao"
                      ,"tamGrd"         : "20em"
                      ,"tamImp"         : "50"
                      ,"digitosMinMax"  : [3,20]
                      ,"digitosValidos" : "A|B|C|D|E|F|G|H|I|J|K|L|M|N|O|P|Q|R|S|T|U|V|W|X|Y|Z|0|1|2|3|4|5|6|7|8|9| "
                      ,"ajudaCampo"     : ["Nome do polo com até 20 caracteres."]
                      ,"importaExcel"   : "S"                                          
                      ,"padrao":0}
            ,{"id":3  ,"field"          : "POL_CODGRP"   
                      ,"pk"             : "S"            
                      ,"labelCol"       : "CODGRP"
                      ,"labelColImp"    : "GRP"
                      ,"obj"            : "edtCodGrp"
                      ,"fieldType"      : "int"              
                      ,"newRecord"      : ["0000","this","this"]                      
                      ,"formato"        : ["i4"]
                      ,"validar"        : ["notnull","intMaiorZero"]
                      ,"tamGrd"         : "0em"
                      ,"tamImp"         : "10"
                      ,"ajudaCampo"     : ["Codigo do grupo."]
                      ,"importaExcel"   : "S"                                          
                      ,"padrao":0}
            ,{"id":4  ,"field"          : "GRP_APELIDO"   
                      ,"insUpDel"       : ["N","N","N"]
                      ,"labelCol"       : "GRUPO"
                      ,"obj"            : "edtDesGrp"
                      ,"tamGrd"         : "10em"
                      ,"digitosMinMax"  : [3,15]
                      ,"validar"        : ["notnull"]                      
                      ,"ajudaCampo"     : ["Razão social do grupo."]
                      ,"padrao":0}
            ,{"id":5  ,"field"          : "POL_ATIVO"  
                      ,"labelCol"       : "ATIVO"   
                      ,"obj"            : "cbAtivo"    
                      ,"padrao":2}                                        
            ,{"id":6  ,"field"          : "POL_REG"    
                      ,"labelCol"       : "REG"     
                      ,"obj"            : "cbReg"      
                      ,"lblDetalhe"     : "REGISTRO"     
                      ,"ajudaDetalhe"   : "Se o registro é PUBlico/ADMinistrador ou do SIStema"                                         
                      ,"padrao":3}  
            ,{"id":7  ,"field"          : "US_APELIDO" 
                      ,"labelCol"       : "USUARIO" 
                      ,"obj"            : "edtUsuario" 
                      ,"padrao":4}                
            ,{"id":8  ,"field"          : "POL_CODUSR" 
                      ,"labelCol"       : "CODUSU"  
                      ,"obj"            : "edtCodUsu"  
                      ,"padrao":5}                                      
            ,{"id":9  ,"labelCol"       : "PP"      
                      ,"obj"            : "imgPP"        
                      ,"func":"var elTr=this.parentNode.parentNode;"
                        +"elTr.cells[0].childNodes[0].checked=true;"
                        +"objPol.espiao();"
                        +"elTr.cells[0].childNodes[0].checked=false;"
                      ,"padrao":8}
          ]
          ,
          "detalheRegistro":
          [
            { "width"           :"100%"
              ,"height"         :"300px" 
              ,"label"          :"POLO - Detalhe do registro"
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
          ,"idBtnConfirmar" : "btnConfirmar"            // Se existir executa o confirmar do form/fieldSet
          ,"idBtnCancelar"  : "btnCancelar"             // Se existir executa o cancelar do form/fieldSet
          ,"div"            : "frmPol"                  // Onde vai ser gerado a table
          ,"divFieldSet"    : "tabelaPol"               // Para fechar a div onde estão os fieldset ao cadastrar
          ,"form"           : "frmPol"                  // Onde vai ser gerado o fieldSet       
          ,"divModal"       : "divTopoInicio"           // Onde vai se appendado abaixo deste a table 
          ,"tbl"            : "tblPol"                  // Nome da table
          ,"prefixo"        : "Pol"                     // Prefixo para elementos do HTML em jsTable2017.js
          ,"tabelaBD"       : "VPOLO"                   // Nome da tabela no banco de dados  
          ,"tabelaBKP"      : "BKPPOLO"                 // Nome da tabela no banco de dados  
          ,"fieldAtivo"     : "POL_ATIVO"               // SE EXISITIR - Nome do campo ATIVO(S/N) na tabela BD
          ,"fieldReg"       : "POL_REG"                 // SE EXISITIR - Nome do campo SYS(P/A) na tabela BD            
          ,"fieldCodUsu"    : "POL_CODUSR"              // SE EXISITIR - Nome do campo CODIGO USUARIO na tabela BD                        
          ,"iFrame"         : "iframeCorpo"             // Se a table vai ficar dentro de uma tag iFrame
          ,"width"          : "77em"                    // Tamanho da table
          ,"height"         : "58em"                    // Altura da table
          ,"tableLeft"      : "sim"                     // Se tiver menu esquerdo
          ,"relTitulo"      : "POLO"                    // Titulo do relatório
          ,"relOrientacao"  : "R"                       // Paisagem ou retrato
          ,"relFonte"       : "8"                       // Fonte do relatório
          ,"foco"           : ["edtCodigo"
                              ,"edtDescricao"
                              ,"btnConfirmar"]          // Foco qdo Cad/Alt/Exc
          ,"formPassoPasso" : "Trac_Espiao.php"        // Enderço da pagina PASSO A PASSO
          ,"indiceTable"    : "DESCRICAO"               // Indice inicial da table
          ,"tamBotao"       : "20"                      // Tamanho botoes defalt 12 [12/25/50/75/100]
          ,"tamMenuTable"   : ["10em","20em"]                                
          ,"labelMenuTable" : "Opções"              // Caption para menu table 
          ,"_menuTable"     :[
                                ["Regitros ativos"                        ,"fa-thumbs-o-up"   ,"btnFiltrarClick('S');"]
                               ,["Registros inativos"                     ,"fa-thumbs-o-down" ,"btnFiltrarClick('N');"]
                               ,["Todos"                                  ,"fa-folder-open"   ,"btnFiltrarClick('*');"]
                               //,["Importar planilha excel"                ,"fa-file-excel-o"  ,"fExcel()"]
                               ,["Imprimir registros em tela"             ,"fa-print"         ,"objPol.imprimir()"]
                               ,["Ajuda para campos padrões"              ,"fa-info"          ,"objPol.AjudaSisAtivo(jsPol);"]
                               ,["Detalhe do registro"                    ,"fa-folder-o"      ,"objPol.detalhe();"]
                               ,["Gerar excel"                            ,"fa-file-excel-o"  ,"objPol.excel();"]
                               ,["Passo a passo do registro"              ,"fa-binoculars"    ,"objPol.espiao();"]
                               ,["Alterar status Ativo/Inativo"           ,"fa-share"         ,"objPol.altAtivo(intCodDir);"]
                               ,["Alterar registro PUBlico/ADMinistrador" ,"fa-reply"         ,"objPol.altPubAdm(intCodDir,jsPub[0].usr_admpub);"]
                               //,["Alterar para registro do SISTEMA"       ,"fa-reply"         ,"objPol.altRegSistema("+jsPub[0].usr_d05+");"]                               
                               ,["Atualizar grade consulta"               ,"fa-filter"        ,"btnFiltrarClick('S');"]                               
                             ]  
          ,"codTblUsu"      : "POLO[08]"                          
          ,"codDir"         : intCodDir
        }; 
        if( objPol === undefined ){  
          objPol=new clsTable2017("objPol");
        };  
        objPol.montarHtmlCE2017(jsPol); 
        //////////////////////////////////////////////////
        //          Fim objeto clsTable2017 POLO        //
        ////////////////////////////////////////////////// 
        //
        //
        //////////////////////////////////////////////
        // Montando a table para importar xls       //
        //////////////////////////////////////////////
        /*
        jsExc={
          "titulo":[
             {"id":0  ,"field":"CODIGO"     ,"labelCol":"CODIGO"    ,"tamGrd":"6em"  ,"tamImp":"20"}
            ,{"id":1  ,"field":"DESCRICAO"  ,"labelCol":"DESCRICAO" ,"tamGrd":"32em" ,"tamImp":"100"}
            ,{"id":2  ,"field":"ERRO"       ,"labelCol":"ERRO"      ,"tamGrd":"35em" ,"tamImp":"100"}
          ]
          ,"botoesH":[
             {"texto":"Imprimir"  ,"name":"excImprimir"   ,"onClick":"3"  ,"enabled":true ,"imagem":"fa fa-print"            ,"ajuda":"Imprimir registros em tela" }        
            ,{"texto":"Excel"     ,"name":"excExcel"      ,"onClick":"5"  ,"enabled":false,"imagem":"fa fa-file-excel-o"     ,"ajuda":"Exportar para excel" }        
            ,{"texto":"Fechar"    ,"name":"excFechar"     ,"onClick":"7"  ,"enabled":true ,"imagem":"fa fa-close"            ,"ajuda":"Fechar formulario" }
          ] 
          ,"registros"      : []                        // Recebe um Json vindo da classe clsBancoDados
          ,"corLinha"       : "if(ceTr.cells[2].innerHTML !='OK') {ceTr.style.color='yellow';ceTr.style.backgroundColor='red';}"      
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
          ,"relTitulo"      : "Importação polo"         // Titulo do relatório
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
        */
        //
        //  
        btnFiltrarClick("S");  
      });
      //
      var objPol;                     // Obrigatório para instanciar o JS TFormaCob
      var jsPol;                      // Obj principal da classe clsTable2017
      var objGrpF10;                  // Obrigatório para instanciar o JS CidadeF10                      
      //var objExc;                     // Obrigatório para instanciar o JS Importar excel
      //var jsExc;                      // Obrigatório para instanciar o objeto objExc
      var clsJs;                      // Classe responsavel por montar um Json e eviar PHP
      var clsErro;                    // Classe para erros            
      var fd;                         // Formulario para envio de dados para o PHP
      var msg;                        // Variavel para guardadar mensagens de retorno/erro 
      var retPhp                      // Retorno do Php para a rotina chamadora
      var contMsg   = 0;              // contador para mensagens
      var cmp       = new clsCampo(); // Abrindo a classe campos
      var jsPub     = JSON.parse(localStorage.getItem("lsPublico"));
      var intCodDir = parseInt(jsPub[0].usr_d08);
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
        clsJs.add("rotina"      , "selectPol"         );
        clsJs.add("login"       , jsPub[0].usr_login  );
        clsJs.add("ativo"       , atv                 );
        fd = new FormData();
        fd.append("polo" , clsJs.fim());
        msg     = requestPedido("Trac_Polo.php",fd); 
        retPhp  = JSON.parse(msg);
        if( retPhp[0].retorno == "OK" ){
          //////////////////////////////////////////////////////////////////////////////////
          // O novo array não tem o campo idUnico mas a montarHtmlCE2017 ja foi executada //
          // Campo obrigatório se existir rotina de manutenção na table devido Json       //
          // Esta rotina não tem manutenção via classe clsTable2017                       //
          // jsCrv.registros=objCrv.addIdUnico(retPhp[0]["dados"]);                       //
          //////////////////////////////////////////////////////////////////////////////////
          jsPol.registros=objPol.addIdUnico(retPhp[0]["dados"]);
          objPol.ordenaJSon(jsPol.indiceTable,false);  
          objPol.montarBody2017();
        };  
      };
      ////////////////////
      // Importar excel //
      ////////////////////
      /*
      function btnAbrirExcelClick(){
        clsErro = new clsMensagem("Erro");
        clsErro.notNull("ARQUIVO"       ,edtArquivo.value);
        if( clsErro.ListaErr() != "" ){
          clsErro.Show();
        } else {
          clsJs   = jsString("lote");  
          clsJs.add("rotina"      , "impExcel"          );
          clsJs.add("login"       , jsPub[0].usr_login  );
          clsJs.add("cabec"       , "CODIGO|DESCRICAO"  );          

          fd = new FormData();
          fd.append("polo"      , clsJs.fim());
          fd.append("arquivo"   , edtArquivo.files[0] );
          msg     = requestPedido("Trac_Polo.php",fd); 
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
          gerarMensagemErro("Pol",retPhp[0].erro,"AVISO");    
        };  
      };
      */
      ////////////////////////
      //  AJUDA PARA GRUPO  //
      ////////////////////////
      function grpFocus(obj){ 
        document.getElementById(obj.id).setAttribute("data-oldvalue",document.getElementById(obj.id).value); 
      };
      function grpF10Click(){ fGrupoF10(0,"edtCodGrp","cbAtivo"); };  
      function RetF10tblGrp(arr){
        document.getElementById("edtCodGrp").value   = arr[0].CODIGO;
        document.getElementById("edtDesGrp").value   = arr[0].APELIDO;
        document.getElementById("edtCodGrp").setAttribute("data-oldvalue",arr[0].CODIGO);
      };
      function codGrpBlur(obj){
        var elOld = jsNmrs(document.getElementById(obj.id).getAttribute("data-oldvalue")).inteiro().ret();
        var elNew = jsNmrs(obj.id).inteiro().ret();
        if( elOld != elNew ){
          var ret = fGrupoF10(1,obj.id,"cbAtivo");
          document.getElementById(obj.id).value          = ( ret.length == 0 ? "0000"    : jsNmrs(ret[0].CODIGO).emZero(4).ret()  );
          document.getElementById("edtDesGrp").value     = ( ret.length == 0 ? ""        : ret[0].APELIDO                       );
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
              name="frmPol" 
              id="frmPol" 
              class="frmTable" 
              action="classPhp/imprimirsql.php" 
              target="_newpage"
              style="top: 6em; width:77em;position: absolute; z-index:30;display:none;">
          <p class="frmCampoTit">
          <input class="informe" type="text" name="titulo" value="Polo" disabled="" style="color: white; text-align: left;">
          </p>
          <div style="height: 200px; overflow-y: auto;">
            <input type="hidden" id="sql" name="sql"/>
            <div class="campotexto campo100">
              <div class="campotexto campo25">
                <input class="campo_input" id="edtCodigo" type="text" maxlength="3" />
                <label class="campo_label campo_required" for="edtCodigo">CODIGO</label>
              </div>
              <div class="campotexto campo75">
                <input class="campo_input" id="edtDescricao" type="text" maxlength="20" />
                <label class="campo_label campo_required" for="edtDescricao">DESCRICAO</label>
              </div>
              <div class="campotexto campo10">
                <input class="campo_input inputF10" id="edtCodGrp"
                                                    OnKeyPress="return mascaraInteiro(event);"
                                                    onBlur="codGrpBlur(this);" 
                                                    onFocus="grpFocus(this);" 
                                                    onClick="grpF10Click('edtCodGrp');"
                                                    data-oldvalue=""
                                                    autocomplete="off" 
                                                    maxlength="4"
                                                    type="text" />
                <label class="campo_label campo_required" for="edtCodGrp">GRUPO</label>
              </div>
              <div class="campotexto campo25">
                <input class="campo_input_titulo input" id="edtDesGrp" type="text" disabled />
                <label class="campo_label campo_required" for="edtDesGrp">RAZAO_GRUPO</label>
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