<?php
  session_start();
  if( isset($_POST["movimentoresumo"]) ){
    try{     
      require("classPhp/conectaSqlServer.class.php");
      require("classPhp/validaJson.class.php"); 
      require("classPhp/removeAcento.class.php"); 

      $vldr     = new validaJSon();          
      $retorno  = "";
      $retCls   = $vldr->validarJs($_POST["movimentoresumo"]);
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
        //      Dados para JavaScript DETALHE         //
        //      38=Freada brusca                      //        
        //      44=Excesso velocidade                 //
        //     167=Aceleracao brusca                  //
        ////////////////////////////////////////////////
        if( $rotina=="detalhe" ){
          
          $sql="SELECT MVM_POSICAO
                       ,MVM_CODVEI
                       ,MVM_PLACA
                       ,MVM_CODUNI
                       ,MVM_CODPOL
                       ,MVM_RFID
                       ,MVM_CODMTR
                       ,MVM_CODEVE
                       ,MVM_NUMEROSERIE
                       ,MVM_LATITUDE
                       ,MVM_LONGITUDE
                       ,MVM_VELOCIDADE
                       ,MVM_ODOMETRO
                       ,MVM_IGNICAO
                       ,MVM_TEMPERATURA
                       ,CONVERT(VARCHAR(20),MVM_DATAGPS,127) AS MVM_DATAGPS
                       ,MVM_HORIMETRO
                       ,MVM_RPM INTEGER
                  FROM MOVIMENTO
                 WHERE (MVM_POSICAO BETWEEN ".$lote[0]->posini." AND ".$lote[0]->posfin.")";
          $classe->msgSelect(false);
          $retCls=$classe->select($sql);
          if( $retCls['retorno'] != "OK" ){
            $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';  
          } else { 
            $retorno='[{"retorno":"OK","dados":'.json_encode($retCls['dados']).',"erro":""}]'; 
          };  
        };  
        if( $rotina=="selectMvm" ){
          $sql="SELECT MR_POSICAOINI
                       ,MR_POSICAOFIN
                       ,CONVERT(VARCHAR(20),MR_INICIO,127) AS MR_INICIO
                       ,CONVERT(VARCHAR(20),MR_FIM,127) AS MR_FIM
                       ,CONVERT(VARCHAR(10),MR_DATAGPS,127) AS MR_DATAGPS
                       ,CONVERT(VARCHAR(10),MR_DATAIMPORTACAO,127) AS MR_DATAIMPORTACAO
                  FROM MOVIMENTORESUMO
                 WHERE MR_DATAIMPORTACAO BETWEEN '".$lote[0]->dtini."' AND '".$lote[0]->dtfin."'";
          $classe->msgSelect(false);
          $retCls=$classe->select($sql);
          if( $retCls['retorno'] != "OK" ){
            $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';  
          } else { 
            $retorno='[{"retorno":"OK","dados":'.json_encode($retCls['dados']).',"erro":""}]'; 
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
    <title>Movimento resumo</title>
    <link rel="stylesheet" href="css/css2017.css">
    <link rel="stylesheet" href="css/cssTable2017.css">
    <link rel="stylesheet" href="css/cssFaTable.css">
    <script src="js/js2017.js"></script>
    <script src="js/jsTable2017.js"></script>
    <script language="javascript" type="text/javascript"></script>
    <script>
      "use strict";
      ////////////////////////////////////////////////
      // Executar o codigo após a pagina carregada  //
      ////////////////////////////////////////////////
      document.addEventListener("DOMContentLoaded", function(){ 
        /////////////////////////////////////////////
        //   Objeto clsTable2017 MOVIMENTORESUMO   //
        /////////////////////////////////////////////
        jsMvm={
          "titulo":[
             {"id":0  ,"labelCol":"OPC"     
                      ,"padrao":1}            
            ,{"id":1  ,"field"          :"MR_POSICAOINI" 
                      ,"labelCol"       : "ID_INICIO"
                      ,"obj"            : "edtPosicaoIni"
                      ,"fieldType"      : "int"
                      ,"align"          : "center"                                      
                      ,"tamGrd"         : "10em"
                      ,"tamImp"         : "20"
                      ,"pk"             : "S"
                      ,"ajudaCampo"     : ["Posicao inicial da importacao"]
                      ,"padrao":0}
            ,{"id":2  ,"field"          :"MR_POSICAOFIN" 
                      ,"labelCol"       : "ID_FIM"
                      ,"obj"            : "edtPosicaoFin"
                      ,"fieldType"      : "int"
                      ,"align"          : "center"                                      
                      ,"tamGrd"         : "10em"
                      ,"tamImp"         : "20"
                      ,"ajudaCampo"     : ["Posicao final da importacao"]
                      ,"padrao":0}
            ,{"id":3  ,"field"          :"MR_INICIO" 
                      ,"labelCol"       : "HORA_INICIO"
                      ,"obj"            : "edtInicio"
                      ,"tamGrd"         : "15em"
                      ,"tamImp"         : "30"
                      ,"ajudaCampo"     : ["Hora de inicio da importacao"]
                      ,"padrao":0}
            ,{"id":4  ,"field"          :"MR_FIM" 
                      ,"labelCol"       : "HORA_FIM"
                      ,"obj"            : "edtFim"
                      ,"tamGrd"         : "15em"
                      ,"tamImp"         : "30"
                      ,"ajudaCampo"     : ["Hora final da importacao"]
                      ,"padrao":0}
            ,{"id":5  ,"field"          :"MR_DATAGPS" 
                      ,"labelCol"       : "DATAGPS"
                      ,"obj"            : "edtDataGps"
                      ,"fieldType"      : "dat"
                      ,"tamGrd"         : "8em"
                      ,"tamImp"         : "15"
                      ,"ajudaCampo"     : ["Data do gps"]
                      ,"padrao":0}
            ,{"id":6  ,"field"          :"MR_DATAIMPORTACAO" 
                      ,"labelCol"       : "IMPORTACAO"
                      ,"obj"            : "edtDataImportacao"
                      ,"fieldType"      : "dat"
                      ,"tamGrd"         : "8em"
                      ,"tamImp"         : "15"
                      ,"ajudaCampo"     : ["Data da importacao"]
                      ,"padrao":0}
          ]
          ,
          "detalheRegistro":
          [
            { "width"           :"100%"
              ,"height"         :"300px" 
              ,"label"          :"MOVIMENTORESUMO - Detalhe do registro"
            }
          ]
          , 
          "botoesH":[
             {"texto":"Filtrar"  ,"name":"mvmFiltrar"    ,"onClick":"7"  ,"enabled":true ,"imagem":"fa fa-plus"         ,"ajuda":"Selecione datas para filtro" }          
            ,{"texto":"Detalhe"  ,"name":"mvmDetalhe"    ,"onClick":"7"  ,"enabled":true,"imagem":"fa fa-file-excel-o"  ,"ajuda":"Exportar para excel" }             
            ,{"texto":"Excel"    ,"name":"mvmExcel"      ,"onClick":"5"  ,"enabled":true,"imagem":"fa fa-file-excel-o"  ,"ajuda":"Exportar para excel" }
            ,{"texto":"Fechar"   ,"name":"mvmFechar"     ,"onClick":"8"  ,"enabled":true ,"imagem":"fa fa-close"        ,"ajuda":"Fechar formulario" }
          ] 
          ,"registros"      : []                        // Recebe um Json vindo da classe clsBancoDados
          ,"opcRegSeek"     : true                      // Opção para numero registros/botão/procurar                     
          ,"checarTags"     : "S"                       // Somente em tempo de desenvolvimento(olha as pricipais tags)                  
          ,"div"            : "frmMvm"                  // Onde vai ser gerado a table
          ,"divFieldSet"    : "tabelaMvm"               // Para fechar a div onde estão os fieldset ao cadastrar
          ,"form"           : "frmMvm"                  // Onde vai ser gerado o fieldSet       
          ,"divModal"       : "divTopoInicio"           // Onde vai se appendado abaixo deste a table 
          ,"tbl"            : "tblMvm"                  // Nome da table
          ,"prefixo"        : "mvm"                     // Prefixo para elementos do HTML em jsTable2017.js
          ,"tabelaBD"       : "MOVIMENTORESUMO"         // Nome da tabela no banco de dados  
          ,"tabelaBKP"      : "*"                       // Nome da tabela no banco de dados  
          ,"fieldAtivo"     : "*"                       // SE EXISITIR - Nome do campo ATIVO(S/N) na tabela BD
          ,"fieldReg"       : "*"                       // SE EXISITIR - Nome do campo SYS(P/A) na tabela BD            
          ,"fieldCodUsu"    : "*"                       // SE EXISITIR - Nome do campo CODIGO USUARIO na tabela BD                        
          ,"iFrame"         : "iframeCorpo"             // Se a table vai ficar dentro de uma tag iFrame
          ,"width"          : "95em"                    // Tamanho da table
          ,"height"         : "48em"                    // Altura da table
          ,"tableLeft"      : "sim"                     // Se tiver menu esquerdo
          ,"relTitulo"      : "MOVIMENTORESUMO"         // Titulo do relatório
          ,"relOrientacao"  : "R"                       // Paisagem ou retrato
          ,"relFonte"       : "8"                       // Fonte do relatório
          ,"foco"           : ["edtDescricao"
                              ,"edtDescricao"
                              ,"btnConfirmar"]          // Foco qdo Cad/Alt/Exc
          ,"formPassoPasso" : "Trac_Espiao.php"         // Enderço da pagina PASSO A PASSO
          ,"indiceTable"    : "DESCRICAO"               // Indice inicial da table
          ,"tamBotao"       : "15"                      // Tamanho botoes defalt 12 [12/25/50/75/100]
          ,"tamMenuTable"   : ["10em","20em"]                                
          ,"labelMenuTable" : "Opções"                  // Caption para menu table 
          ,"_menuTable"     :[
                                ["Imprimir registros em tela"             ,"fa-print"         ,"objMvm.imprimir()"]
                               ,["Gerar excel"                            ,"fa-file-excel-o"  ,"objMvm.excel();"]
                             ]  
          ,"codTblUsu"      : "MOVIMENTORESUMO[00]"                          
          ,"codDir"         : intCodDir
        }; 
        if( objMvm === undefined ){  
          objMvm=new clsTable2017("objMvm");
        };  
        objMvm.montarHtmlCE2017(jsMvm); 
        //////////////////////////////////////////////////
        //  Fim objeto clsTable2017 MOVIMENTORESUMO      //
        ////////////////////////////////////////////////// 
        //btnFiltrarClick("S");  
      });
      //
      var objMvm;                     // Obrigatório para instanciar o JS TFormaCob
      var objComp;                    //
      var jsMvm;                      // Obj principal da classe clsTable2017
      var jsComp;                     // Obj da composição do evento
      var clsJs;                      // Classe responsavel por montar um Json e eviar PHP
      var clsErro;                    // Classe para erros            
      var fd;                         // Formulario para envio de dados para o PHP
      var msg;                        // Variavel para guardadar mensagens de retorno/erro 
      var retPhp                      // Retorno do Php para a rotina chamadora
      var clsChecados;                // Classe para montar Json
      var chkds;                      // Guarda todos registros checados na table 
      var tamC;                       // Guarda a quantidade de registros dentro do vetor chkds
      var contMsg   = 0;              // contador para mensagens
      var cmp       = new clsCampo(); // Abrindo a classe campos
      var jsPub     = JSON.parse(localStorage.getItem("lsPublico"));
      var intCodDir = parseInt(jsPub[0].usr_d04);
      var grupo;
      ////////////////////////////////
      // ALTERAR EMISSAO/VENCIMENTO //
      ////////////////////////////////
      function mvmFiltrarClick(){
        try{        
          var jsCx={
            "botaoEsquerdo":"s"
            ,"botaoDireito":"s"
            ,"titulo":"DataGPS dd/mm/yyyy"
            ,"width":"25%"
            ,"top":"10em"
            ,"left":"20em"
            ,"hint":"s"
            ,"onClick":"btnFiltrarClick()"
            ,"campos":[
               {"name":"newEmissao" ,"type":"date"  ,"placeholder":"Data inicial" ,"maxlength":"10" ,"imagem":"fa-calendar" }
              ,{"name":"newVencto"  ,"type":"date"  ,"placeholder":"Data final"   ,"maxlength":"10" ,"imagem":"fa-calendar" }
            ]          
          };
          cxDialogo(jsCx);
          document.getElementById("newEmissao").value=jsDatas(-30).retDDMMYYYY();
          document.getElementById("newVencto").value=jsDatas(-15).retDDMMYYYY();
          document.getElementById("newEmissao").foco();

        }catch(e){
          gerarMensagemErro("catch",e.message,"Erro");
        };
      };      
      
      ////////////////////////////
      // Filtrando os registros //
      ////////////////////////////
      function btnFiltrarClick() { 
        clsJs   = jsString("lote");  
        clsJs.add("rotina"  , "selectMvm"         );
        clsJs.add("login"   , jsPub[0].usr_login  );
        clsJs.add("dtini"   , jsDatas("newEmissao").retMMDDYYYY() );
        clsJs.add("dtfin"   , jsDatas("newVencto").retMMDDYYYY()  );
        fd = new FormData();
        fd.append("movimentoresumo" , clsJs.fim());
        msg     = requestPedido("Trac_MovimentoResumo.php",fd); 
        retPhp  = JSON.parse(msg);
        if( retPhp[0].retorno == "OK" ){
          //////////////////////////////////////////////////////////////////////////////////
          // O novo array não tem o campo idUnico mas a montarHtmlCE2017 ja foi executada //
          // Campo obrigatório se existir rotina de manutenção na table devido Json       //
          // Esta rotina não tem manutenção via classe clsTable2017                       //
          // jsCrv.registros=objCrv.addIdUnico(retPhp[0]["dados"]);                       //
          //////////////////////////////////////////////////////////////////////////////////
          jsMvm.registros=objMvm.addIdUnico(retPhp[0]["dados"]);
          objMvm.ordenaJSon(jsMvm.indiceTable,false);  
          objMvm.montarBody2017();
        };  
        document.getElementById("dlgCancelar").click();
      };  

      function mvmDetalheClick(){
        try{
          clsChecados = objMvm.gerarJson("1");
          chkds       = clsChecados.gerar();
          
          clsJs       = jsString("lote");  
          clsJs.add("rotina"  , "detalhe"           );
          clsJs.add("login"   , jsPub[0].usr_login  );
          clsJs.add("posini"   , chkds[0].ID_INICIO );
          clsJs.add("posfin"   , chkds[0].ID_FIM    );
          fd          = new FormData();
          fd.append("movimentoresumo" , clsJs.fim());          
          var req = requestPedido("Trac_MovimentoResumo.php",fd);
          var ret = JSON.parse(req);
          if( ret[0].dados.length==0 ){
            gerarMensagemErro("ALV","NENHUM REGISTRO LOCALIZADO","AVISO");  
          } else {
            if( ret[0].retorno == "OK" ){
              jsComp={
                "titulo":[
                  {"id":0   ,"labelCol"       : "OPC"     
                            ,"padrao"         : 1}            
                  ,{"id":1  ,"field"          : "MVM_POSICAO" 
                            ,"labelCol"       : "ID"
                            ,"fieldType"      : "str"
                            ,"tamGrd"         : "10em"
                            ,"tamImp"         : "15"
                            ,"pk"             : "S"
                            ,"padrao":0}
                  ,{"id":2  ,"field"          : "MVM_CODVEI"   
                            ,"fieldType"      : "int"
                            ,"align"          : "center"
                            ,"labelCol"       : "CODVEI"
                            ,"tamGrd"         : "5em"
                            ,"tamImp"         : "10"
                            ,"padrao":0}
                  ,{"id":3  ,"field"          : "MVM_PLACA"   
                            ,"fieldType"      : "str"
                            ,"labelCol"       : "PLACA"
                            ,"tamGrd"         : "8em"
                            ,"tamImp"         : "15"
                            ,"padrao":0}
                  ,{"id":4  ,"field"          : "MVM_CODUNI"   
                            ,"fieldType"      : "int"
                            ,"align"          : "center"
                            ,"labelCol"       : "UNIDADE"
                            ,"tamGrd"         : "5em"
                            ,"tamImp"         : "10"
                            ,"padrao":0}
                  ,{"id":5  ,"field"          : "MVM_CODPOL"   
                            ,"fieldType"      : "str"
                            ,"labelCol"       : "POLO"
                            ,"tamGrd"         : "4em"
                            ,"tamImp"         : "10"
                            ,"padrao":0}
                  ,{"id":6  ,"field"          : "MVM_RFID"   
                            ,"fieldType"      : "str"
                            ,"labelCol"       : "RFID"
                            ,"tamGrd"         : "7em"
                            ,"tamImp"         : "15"
                            ,"padrao":0}
                  ,{"id":7  ,"field"          : "MVM_CODMOT"   
                            ,"fieldType"      : "int"
                            ,"align"          : "center"
                            ,"labelCol"       : "MOTORISTA"
                            ,"tamGrd"         : "5em"
                            ,"tamImp"         : "10"
                            ,"padrao":0}
                  ,{"id":8  ,"field"          : "MVM_EVENTO"   
                            ,"fieldType"      : "str"
                            ,"labelCol"       : "EVENTO"
                            ,"align"          : "center"                            
                            ,"tamGrd"         : "6em"
                            ,"tamImp"         : "10"
                            ,"padrao":0}
                  ,{"id":9  ,"field"          : "MVM_NUMEROSERIE"   
                            ,"fieldType"      : "str"
                            ,"labelCol"       : "SERIE"
                            ,"tamGrd"         : "15em"
                            ,"tamImp"         : "35"
                            ,"padrao":0}
                  ,{"id":10 ,"field"          :"MVM_LATITUDE" 
                            ,"labelCol"       : "LATITUDE"
                            ,"fieldType"      : "flo8" 
                            ,"tamGrd"         : "10em"
                            ,"tamImp"         : "20"
                            ,"padrao":0}
                  ,{"id":11 ,"field"          :"MVM_LONGITUDE" 
                            ,"labelCol"       : "LONGITIDE"
                            ,"fieldType"      : "flo8" 
                            ,"tamGrd"         : "10em"
                            ,"tamImp"         : "20"
                            ,"padrao":0}
                  ,{"id":12 ,"field"          : "MVM_VELOCIDADE"   
                            ,"fieldType"      : "int"
                            ,"align"          : "center"
                            ,"labelCol"       : "VELOCIDADE"
                            ,"tamGrd"         : "5em"
                            ,"tamImp"         : "10"
                            ,"padrao":0}
                  ,{"id":13 ,"field"          : "MVM_ODOMETRO"   
                            ,"fieldType"      : "int"
                            ,"align"          : "center"
                            ,"labelCol"       : "ODOMETRO"
                            ,"tamGrd"         : "10em"
                            ,"tamImp"         : "10"
                            ,"padrao":0}
                  ,{"id":14 ,"field"          : "MVM_IGNICAO"   
                            ,"fieldType"      : "int"
                            ,"align"          : "center"
                            ,"labelCol"       : "IGINICAO"
                            ,"tamGrd"         : "5em"
                            ,"tamImp"         : "10"
                            ,"padrao":0}
                  ,{"id":15 ,"field"          : "MVM_TEMPERATURA"   
                            ,"fieldType"      : "int"
                            ,"align"          : "center"
                            ,"labelCol"       : "TEMPERATURA"
                            ,"tamGrd"         : "5em"
                            ,"tamImp"         : "10"
                            ,"padrao":0}
                  ,{"id":16 ,"field"          : "MVM_DATAGPS" 
                            ,"labelCol"       : "DATAGPS"
                            ,"tamGrd"         : "20em"
                            ,"tamImp"         : "30"
                            ,"padrao":0}
                  ,{"id":17 ,"field"          : "MVM_HORIMETRO"   
                            ,"fieldType"      : "int"
                            ,"align"          : "center"
                            ,"labelCol"       : "HORIMETRO"
                            ,"tamGrd"         : "5em"
                            ,"tamImp"         : "10"
                            ,"padrao":0}
                  ,{"id":18 ,"field"          : "MVM_RPM"   
                            ,"fieldType"      : "int"
                            ,"align"          : "center"
                            ,"labelCol"       : "RPM"
                            ,"tamGrd"         : "5em"
                            ,"tamImp"         : "10"
                            ,"padrao":0}
                ]  
                , 
                "botoesH":[
                   {"texto":"Excel"     ,"name":"comExcel"  ,"onClick":"5"  ,"enabled":true,"imagem":"fa fa-file-excel-o"     ,"ajuda":"Exportar para excel" }
                  ,{"texto":"Retornar"  ,"name":"comVoltar" ,"onClick":"7"  ,"enabled":true ,"imagem":"fa fa-plus","ajuda":"Retorna a tela anterior" }
                ] 
                ,"registros"      : ret[0].dados              // Recebe um Json vindo da classe clsBancoDados
                ,"refazClasse"    : "S"
                //,"corLinha"       : "switch (ceTr.cells[1].innerHTML){case '1' : ceTr.style.backgroundColor='red';break; case '2' : ceTr.style.backgroundColor='#6CA6CD';break;}"          
                ,"opcRegSeek"     : true                      // Opção para numero registros/botão/procurar                     
                ,"checarTags"     : "S"                       // Somente em tempo de desenvolvimento(olha as pricipais tags)                  
                ,"div"            : "frmComp"                 // Onde vai ser gerado a table
                ,"divFieldSet"    : "tabela"                  // Para fechar a div onde estão os fieldset ao cadastrar
                ,"form"           : "frm"                     // Onde vai ser gerado o fieldSet       
                ,"divModal"       : "divComposicaoTitulo"     // Onde vai se appendado abaixo deste a table 
                ,"tbl"            : "tblComp"                 // Nome da table
                ,"prefixo"        : "es"                      // Prefixo para elementos do HTML em jsTable2017.js
                ,"tabelaBD"       : "MOVIMENTO"               // Nome da tabela no banco de dados  
                ,"iFrame"         : "iframeCorpo"             // Se a table vai ficar dentro de uma tag iFrame
                ,"width"          : "110em"                   // Tamanho da table
                ,"height"         : "58em"                    // Altura da table
                ,"tableLeft"      : "sim"                     // Se tiver menu esquerdo
                ,"relTitulo"      : "DETALHE RESUMO"          // Titulo do relatório
                ,"relOrientacao"  : "P"                       // Paisagem ou retrato
                ,"relFonte"       : "8"                       // Fonte do relatório
                ,"indiceTable"    : "ID"                      // Indice inicial da table
                ,"tamBotao"       : "12"                      // Tamanho botoes defalt 12 [12/25/50/75/100]
                ,"tamMenuTable"   : ["10em","20em"]                                
                ,"codTblUsu"      : "MOVIMENTO[01]"                          
                ,"codDir"         : intCodDir
              }; 
              if( objComp === undefined ){  
                objComp=new clsTable2017("objComp");
              };
              objComp.montarHtmlCE2017(jsComp); 
              document.getElementById("divComposicao").style.display  = "block";
              window.location.href="#ancoraCom";
              //
            } else {
              throw ret[0].erro;
            }
          };
        }catch( e ){
          console.log(req);
          gerarMensagemErro("Composição do movimentoresumo",e,"Erro");
        }
      };
      //
      //
      function comVoltarClick(){
        document.getElementById("divComposicao").style.display  = "none";
        window.location.href="#ancoraMvm";
      }
    </script>
  </head>
  <body>
    <div class="divTelaCheia">
      <a name="ancoraMvm"></a> 
      <div id="divRotina" class="conteudo" style="display:block;overflow-x:auto;position:relative;float:left;width:95em;height:50em;">  
        <div id="divTopoInicio">
        </div>
        <form method="post" 
              name="frm" 
              id="frm" 
              class="frmTable" 
              action="classPhp/imprimirsql.php" 
              target="_newpage"
              style="top: 6em; width:77em;position: absolute; z-index:30;display:none;">
          <p class="frmCampoTit">
          <input type="hidden" id="sql" name="sql"/>
        </form>
      </div>
      <a name="ancoraCom"></a> 
      <div id="divComposicao" class="conteudo" style="position:relative;float:left;display:none;overflow-x:auto;width:115em;height:60em;">
        <div id="divComposicaoTitulo">
        </div>
      </div>
    </div>       
  </body>
  <!--
  <body>
    <div class="divTelaCheia">
      <div id="divRotina" class="conteudo" style="display:block;overflow-x:auto;">  
        <div id="divTopoInicio">
        </div>
        <form method="post" 
              name="frm" 
              id="frm" 
              class="frmTable" 
              action="classPhp/imprimirsql.php" 
              target="_newpage"
              style="top: 6em; width:77em;position: absolute; z-index:30;display:none;">
          <p class="frmCampoTit">
          <input type="hidden" id="sql" name="sql"/>
        </form>
      </div>
      <div id="divComposicao" class="conteudo" style="display:block;overflow-x:auto;">  
        <div id="divComposicaoTitulo">
        </div>
      </div>
    </div>       
  </body>
  -->
</html>