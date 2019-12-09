<?php
  session_start();
  if( isset($_POST["evento"]) ){
    try{     
      require("classPhp/conectaSqlServer.class.php");
      require("classPhp/validaJson.class.php"); 
      require("classPhp/removeAcento.class.php"); 

      $vldr     = new validaJSon();          
      $retorno  = "";
      $retCls   = $vldr->validarJs($_POST["evento"]);
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
          switch( $lote[0]->codeve ){
            case "AB":    $alias="A.BIAB";  $table="BI_ACELERBRUSCA";  break;
            case "CB":    $alias="A.BICB";  $table="BI_CONDUCAOBANG";  break;
            case "ERPM":  $alias="A.BIRA";  $table="BI_RPMALTO";       break;            
            case "EV":    $alias="A.BIEV";  $table="BI_EXCESSOVELOC";  break;
            case "EVC":   $alias="A.BIEVC"; $table="BI_EXCESSOVELCH";  break;
            case "FB":    $alias="A.BIFB";  $table="BI_FREADABRUSCA";  break;
            case "VN":    $alias="A.BIVN";  $table="BI_VELOCNORMALI";  break;
          };
          $sql="SELECT ".$alias."_POSICAO
                       ,CAST(".$alias."_DATAGPS AS VARCHAR(10)) 
                       ,UNI.UNI_APELIDO
                       ,MVM.MVM_CODPOL
                       ,MVM.MVM_PLACA
                       ,COALESCE(VCL.VCL_FROTA,'*') AS VCL_FROTA
                       ,MVM.MVM_RFID
                       ,MVM.MVM_VELOCIDADE
                       ,MVM.MVM_RPM
                       ,MTR.MTR_NOME
                       ,MVM.MVM_LOCALIZACAO
                  FROM ".$table." A
                  LEFT OUTER JOIN UNIDADE UNI ON ".$alias."_CODUNI=UNI.UNI_CODIGO
                  LEFT OUTER JOIN MOVIMENTO MVM ON ".$alias."_POSICAO=MVM.MVM_POSICAO
                  LEFT OUTER JOIN VEICULO VCL ON ".$alias."_CODVCL=VCL.VCL_CODIGO AND VCL.VCL_ATIVO='S'
                  LEFT OUTER JOIN MOTORISTA MTR ON ".$alias."_CODMTR=MTR.MTR_CODIGO
                  LEFT OUTER JOIN USUARIOUNIDADE UU ON ".$alias."_CODUNI=UU.UU_CODUNI AND UU.UU_CODUSR=".$_SESSION['usr_codigo']."
                 WHERE ".$alias."_DATAGPS BETWEEN '".$lote[0]->dtini."' AND '".$lote[0]->dtfin."'
                   AND (COALESCE(UU.UU_ATIVO,'')='S')";                 
          $classe->msgSelect(false);
          $retCls=$classe->select($sql);
          if( $retCls['retorno'] != "OK" ){
            $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';  
          } else { 
            $retorno='[{"retorno":"OK","dados":'.json_encode($retCls['dados']).',"erro":""}]'; 
          };  
        };  
        if( $rotina=="selectEve" ){
          $sql="SELECT A.EVE_CODIGO
                       ,A.EVE_NOME
                       ,A.EVE_CODEG
                       ,EG.EG_NOME
                       ,CASE WHEN A.EVE_MOVIMENTO='S' THEN 'SIM' ELSE 'NAO' END AS EVE_MOVIMENTO
                       ,CASE WHEN A.EVE_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS EVE_ATIVO
                       ,CASE WHEN A.EVE_REG='P' THEN 'PUB' WHEN A.EVE_REG='S' THEN 'SIS' ELSE 'ADM' END AS EVE_REG
                       ,US_APELIDO
                       ,EVE_CODUSR
                  FROM EVENTO A
                  LEFT OUTER JOIN USUARIOSISTEMA U ON A.EVE_CODUSR=U.US_CODIGO
                  LEFT OUTER JOIN EVENTOGRUPO EG ON A.EVE_CODEG=EG.EG_CODIGO
                 WHERE (EVE_ATIVO='".$lote[0]->ativo."') OR ('*'='".$lote[0]->ativo."')";                 
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
    <title>Eventos</title>
    <link rel="stylesheet" href="css/css2017.css">
    <link rel="stylesheet" href="css/cssTable2017.css">
    <link rel="stylesheet" href="css/cssFaTable.css">
    <link rel="stylesheet" href="css/Acordeon.css">        
    <script src="js/js2017.js"></script>
    <script src="js/jsTable2017.js"></script>
    <!--<script src="js/eventoAcordeon.js"></script>-->
    <script src="tabelaTrac/f10/tabelaEventoGrupoF10.js"></script>        
    <script language="javascript" type="text/javascript"></script>
    <script>
      "use strict";
      ////////////////////////////////////////////////
      // Executar o codigo após a pagina carregada  //
      ////////////////////////////////////////////////
      document.addEventListener("DOMContentLoaded", function(){ 
        /////////////////////////////////////////////
        //       Objeto clsTable2017 EVENTO        //
        /////////////////////////////////////////////
        jsEve={
          "titulo":[
             {"id":0  ,"labelCol":"OPC"     
                      ,"padrao":1}            
            ,{"id":1  ,"field"          :"EVE_CODIGO" 
                      ,"labelCol"       : "CODIGO"
                      ,"obj"            : "edtCodigo"
                      ,"fieldType"      : "int"
                      ,"formato"        : ["i6"]
                      ,"align"          : "center"                                      
                      ,"tamGrd"         : "8em"
                      ,"tamImp"         : "20"
                      ,"pk"             : "S"
                      ,"autoIncremento" : "S"
                      ,"newRecord"      : ["0000","this","this"]
                      ,"insUpDel"       : ["S","N","N"]
                      ,"validar"        : ["notnull"]
                      ,"ajudaCampo"     : ["Codigo do evento cadastrado automaticamente na importacao sistemsat"]
                      ,"padrao":0}
            ,{"id":2  ,"field"          : "EVE_NOME"   
                      ,"labelCol"       : "DESCRICAO"
                      ,"obj"            : "edtDescricao"
                      ,"tamGrd"         : "50em"
                      ,"tamImp"         : "120"
                      ,"ajudaCampo"     : ["Nome do evento."]
                      ,"padrao":0}
            ,{"id":3  ,"field"          : "EVE_CODEG"   
                      ,"labelCol"       : "GRUPO"
                      ,"obj"            : "edtCodEg"
                      ,"tamGrd"         : "5em"
                      ,"tamImp"         : "20"
                      ,"ajudaCampo"     : ["Nome do grupo."]
                      ,"padrao":0}
            ,{"id":4  ,"field"          : "EG_NOME"   
                      ,"insUpDel"       : ["N","N","N"]
                      ,"labelCol"       : "NOME"
                      ,"obj"            : "edtDesEg"
                      ,"tamGrd"         : "10em"
                      ,"tamImp"         : "50"
                      ,"validar"        : ["notnull"]                      
                      ,"ajudaCampo"     : ["Descrição do grupo."]
                      ,"padrao":0}
            ,{"id":5  ,"field"          : "EVE_MOVIMENTO"   
                      ,"labelCol"       : "EV_EVC"
                      ,"obj"            : "cbMovimento"
                      ,"tamGrd"         : "6em"
                      ,"tamImp"         : "20"
                      ,"tipo"           : "cb"
                      ,"newRecord"      : ["S","this","this"]
											,"funcCor"        : "(objCell.innerHTML=='NAO'  ? objCell.classList.add('corVermelho') : objCell.classList.remove('corVermelho'))"
                      ,"ajudaCampo"     : ["Se o evento entra no movimento excesso de velocidade."]
                      ,"importaExcel"   : "S"                                          
                      ,"padrao":0}
            ,{"id":6  ,"field"          : "EVE_ATIVO"  
                      ,"labelCol"       : "ATIVO"   
                      ,"obj"            : "cbAtivo"    
                      ,"padrao":2}                                        
            ,{"id":7  ,"field"          : "EVE_REG"    
                      ,"labelCol"       : "REG"     
                      ,"obj"            : "cbReg"      
                      ,"lblDetalhe"     : "REGISTRO"     
                      ,"ajudaDetalhe"   : "Se o registro é PUBlico/ADMinistrador ou do SIStema"                                         
                      ,"padrao":3}  
            ,{"id":8  ,"field"          : "US_APELIDO" 
                      ,"labelCol"       : "USUARIO" 
                      ,"obj"            : "edtUsuario" 
                      ,"padrao":4}                
            ,{"id":9  ,"field"          : "EVE_CODUSR" 
                      ,"labelCol"       : "CODUSU"  
                      ,"obj"            : "edtCodUsu"  
                      ,"padrao":5}                                      
            ,{"id":10 ,"labelCol"       : "PP"      
                      ,"obj"            : "imgPP"        
                      ,"func":"var elTr=this.parentNode.parentNode;"
                        +"elTr.cells[0].childNodes[0].checked=true;"
                        +"objEg.espiao();"
                        +"elTr.cells[0].childNodes[0].checked=false;"
                      ,"padrao":8}
          ]
          ,
          "detalheRegistro":
          [
            { "width"           :"100%"
              ,"height"         :"300px" 
              ,"label"          :"EVENTO - Detalhe do registro"
            }
          ]
          , 
          "botoesH":[
             {"texto":"Cadastrar" ,"name":"horCadastrar"  ,"onClick":"0"  ,"enabled":true ,"imagem":"fa fa-plus"             ,"ajuda":"Novo registro" }
            ,{"texto":"Alterar"   ,"name":"horAlterar"    ,"onClick":"1"  ,"enabled":true ,"imagem":"fa fa-pencil-square-o"  ,"ajuda":"Alterar registro selecionado" }
            ,{"texto":"Excluir"   ,"name":"horExcluir"    ,"onClick":"2"  ,"enabled":true ,"imagem":"fa fa-minus"            ,"ajuda":"Excluir registro selecionado" }
            ,{"texto":"Excel"      ,"name":"eveExcel"      ,"onClick":"5"  ,"enabled":true,"imagem":"fa fa-file-excel-o"     ,"ajuda":"Exportar para excel" }
            ,{"texto":"Detalhe"   ,"name":"horDetalhe"    ,"onClick":"7"  ,"enabled":true ,"imagem":"fa fa-plus"             ,"ajuda":"Detalhe do evento" }
            ,{"texto":"Fechar"    ,"name":"horFechar"     ,"onClick":"8"  ,"enabled":true ,"imagem":"fa fa-close"            ,"ajuda":"Fechar formulario" }
          ] 
          ,"registros"      : []                        // Recebe um Json vindo da classe clsBancoDados
          ,"corLinha"       : "if(ceTr.cells[3].innerHTML !='*') {ceTr.style.color='black';ceTr.style.backgroundColor='#9ACD32';}"      
          ,"opcRegSeek"     : true                      // Opção para numero registros/botão/procurar                     
          ,"checarTags"     : "S"                       // Somente em tempo de desenvolvimento(olha as pricipais tags)                  
          ,"idBtnConfirmar" : "btnConfirmar"            // Se existir executa o confirmar do form/fieldSet
          ,"idBtnCancelar"  : "btnCancelar"             // Se existir executa o cancelar do form/fieldSet
          ,"div"            : "frmEve"                  // Onde vai ser gerado a table
          ,"divFieldSet"    : "tabelaEve"               // Para fechar a div onde estão os fieldset ao cadastrar
          ,"form"           : "frmEve"                  // Onde vai ser gerado o fieldSet       
          ,"divModal"       : "divTopoInicio"           // Onde vai se appendado abaixo deste a table 
          ,"tbl"            : "tblEve"                  // Nome da table
          ,"prefixo"        : "eve"                     // Prefixo para elementos do HTML em jsTable2017.js
          ,"tabelaBD"       : "VEVENTO"                 // Nome da tabela no banco de dados  
          ,"tabelaBKP"      : "BKPEVENTO"               // Nome da tabela no banco de dados  
          ,"fieldAtivo"     : "EVE_ATIVO"               // SE EXISITIR - Nome do campo ATIVO(S/N) na tabela BD
          ,"fieldReg"       : "EVE_REG"                 // SE EXISITIR - Nome do campo SYS(P/A) na tabela BD            
          ,"fieldCodUsu"    : "EVE_CODUSR"              // SE EXISITIR - Nome do campo CODIGO USUARIO na tabela BD                        
          ,"iFrame"         : "iframeCorpo"             // Se a table vai ficar dentro de uma tag iFrame
          ,"width"          : "100em"                   // Tamanho da table
          ,"height"         : "48em"                    // Altura da table
          ,"tableLeft"      : "sim"                     // Se tiver menu esquerdo
          ,"relTitulo"      : "EVENTOS"                 // Titulo do relatório
          ,"relOrientacao"  : "P"                       // Paisagem ou retrato
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
                                //["Regitros ativos"                        ,"fa-thumbs-o-up"   ,"btnFiltrarClick('S');"]
                               //,["Registros inativos"                     ,"fa-thumbs-o-down" ,"btnFiltrarClick('N');"]
                               //,["Todos"                                  ,"fa-folder-open"   ,"btnFiltrarClick('*');"]
                               //,["Importar planilha excel"                ,"fa-file-excel-o"  ,"fExcel()"]
                               ["Imprimir registros em tela"             ,"fa-print"         ,"objEve.imprimir()"]
                               ,["Ajuda para campos padrões"              ,"fa-info"          ,"objEve.AjudaSisAtivo(jsEve);"]
                               //,["Detalhe do registro"                    ,"fa-folder-o"      ,"objEve.detalhe();"]
                               ,["Gerar excel"                            ,"fa-file-excel-o"  ,"objEve.excel();"]
                               ,["Número de registros em tela"            ,"fa-info"          ,"objEve.numRegistros();"]
                               ,["Passo a passo do registro"              ,"fa-binoculars"    ,"objEve.espiao();"]
                               //,["Alterar status Ativo/Inativo"           ,"fa-share"         ,"objEve.altAtivo(intCodDir);"]
                               //,["Alterar registro PUBlico/ADMinistrador" ,"fa-reply"         ,"objEve.altPubAdm(intCodDir,jsPub[0].usr_admpub);"]
                               //,["Alterar para registro do SISTEMA"       ,"fa-reply"         ,"objEve.altRegSistema("+jsPub[0].usr_d05+");"]                               
                               ,["Atualizar grade consulta"               ,"fa-filter"        ,"btnFiltrarClick('S');"]                               
                             ]  
          ,"codTblUsu"      : "EVENTO[00]"                          
          ,"codDir"         : intCodDir
        }; 
        if( objEve === undefined ){  
          objEve=new clsTable2017("objEve");
        };  
        objEve.montarHtmlCE2017(jsEve); 
        //////////////////////////////////////////////////
        //          Fim objeto clsTable2017 EVENTO      //
        ////////////////////////////////////////////////// 
        btnFiltrarClick("S");  
      });
      //
      var objEve;                     // Obrigatório para instanciar o JS TFormaCob
      var objDet;                     //
      var objEgF10;                   // Obrigatório para instanciar o JS EventoGrupo
      var jsEve;                      // Obj principal da classe clsTable2017
      var jsDet;                      // Obj da composição do evento
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
      ////////////////////////////
      // Filtrando os registros //
      ////////////////////////////
      function btnFiltrarClick(atv) { 
        clsJs   = jsString("lote");  
        clsJs.add("rotina"      , "selectEve"         );
        clsJs.add("login"       , jsPub[0].usr_login  );
        clsJs.add("ativo"       , atv                 );
        fd = new FormData();
        fd.append("evento" , clsJs.fim());
        msg     = requestPedido("Trac_Evento.php",fd); 
        retPhp  = JSON.parse(msg);
        if( retPhp[0].retorno == "OK" ){
          //////////////////////////////////////////////////////////////////////////////////
          // O novo array não tem o campo idUnico mas a montarHtmlCE2017 ja foi executada //
          // Campo obrigatório se existir rotina de manutenção na table devido Json       //
          // Esta rotina não tem manutenção via classe clsTable2017                       //
          // jsCrv.registros=objCrv.addIdUnico(retPhp[0]["dados"]);                       //
          //////////////////////////////////////////////////////////////////////////////////
          jsEve.registros=objEve.addIdUnico(retPhp[0]["dados"]);
          objEve.ordenaJSon(jsEve.indiceTable,false);  
          objEve.montarBody2017();
        };  
      };  
      ////////////////////////////////
      //          DETALHE           //
      ////////////////////////////////
      function horDetalheClick(nomeFun){
        try{        
          clsChecados = objEve.gerarJson("1");
          chkds       = clsChecados.gerar();
          grupo       = chkds[0].GRUPO;
          if( grupo=="00000-0" ){
            gerarMensagemErro("ALV","EVENTO NAO PARAMETRIZADO PARA BI","AVISO");    
          } else {
            var jsCx={
              "botaoEsquerdo":"s"
              ,"botaoDireito":"s"
              ,"titulo":"Periodo dd/mm/yyyy"
              ,"width":"25%"
              ,"top":"10em"
              ,"left":"20em"
              ,"hint":"s"
              //,"divModalFull":"n"
              ,"onClick":"filtrarDetalhe()"
              ,"campos":[
                 {"name":"newEmissao" ,"type":"date"  ,"placeholder":"Data inicial" ,"maxlength":"10" ,"imagem":"fa-calendar" }
                ,{"name":"newVencto"  ,"type":"date"  ,"placeholder":"Data final"   ,"maxlength":"10" ,"imagem":"fa-calendar" }
              ]          
            };
            cxDialogo(jsCx);
            document.getElementById("newEmissao").value=jsDatas(-30).retDDMMYYYY();
            document.getElementById("newVencto").value=jsDatas(-15).retDDMMYYYY();
            document.getElementById("newEmissao").foco();
          };
        }catch(e){
          gerarMensagemErro("catch",e.message,"Erro");
        };
      };      
      function filtrarDetalhe(){
        try{
          clsJs       = jsString("lote");  
          clsJs.add("rotina"  , "detalhe"                           );
          clsJs.add("login"   , jsPub[0].usr_login                  );
          clsJs.add("codeve"  , chkds[0].GRUPO                      );
          clsJs.add("dtini"   , jsDatas("newEmissao").retMMDDYYYY() );
          clsJs.add("dtfin"   , jsDatas("newVencto").retMMDDYYYY()  );
          fd          = new FormData();
          fd.append("evento" , clsJs.fim());          
          document.getElementById("dlgCancelar").click();
          var req = requestPedido("Trac_Evento.php",fd);
          var ret = JSON.parse(req);
          if( ret[0].dados.length==0 ){
            gerarMensagemErro("ALV","NENHUM REGISTRO LOCALIZADO","AVISO");  
          } else {
            if( ret[0].retorno == "OK" ){
              jsDet={
                "titulo":[
                  {"id":0   ,"labelCol"       : "OPC"     
                            ,"padrao"         : 1}            
                  ,{"id":1  ,"field"          : "BIEV_POSICAO" 
                            ,"labelCol"       : "ID"
                            ,"fieldType"      : "str"
                            ,"obj"            : "edtPosicao"
                            ,"tamGrd"         : "10em"
                            ,"tamImp"         : "15"
                            ,"pk"             : "S"
                            ,"ajudaCampo"     : ["Id sistemsat."]
                            ,"padrao":0}
                  ,{"id":2  ,"field"          : "BIEV_DATAGPS"   
                            ,"fieldType"      : "str"
                            ,"labelCol"       : "DATA"
                            ,"obj"            : "edtDataGps"
                            ,"tamGrd"         : "8em"
                            ,"tamImp"         : "20"
                            ,"ajudaCampo"     : ["Data."]
                            ,"padrao":0}
                  ,{"id":3  ,"field"          : "UNI_APELIDO"   
                            ,"fieldType"      : "str"
                            ,"labelCol"       : "UNIDADE"
                            ,"obj"            : "edtUnidade"
                            ,"tamGrd"         : "10em"
                            ,"tamImp"         : "20"
                            ,"ajudaCampo"     : ["Unidde"]
                            ,"padrao":0}
                  ,{"id":4  ,"field"          : "MVM_CODPOL"   
                            ,"fieldType"      : "str"
                            ,"labelCol"       : "POLO"
                            ,"obj"            : "edtPolo"
                            ,"tamGrd"         : "3em"
                            ,"tamImp"         : "10"
                            ,"ajudaCampo"     : ["Polo"]
                            ,"padrao":0}
                  ,{"id":5  ,"field"          : "MVM_PLACA"   
                            ,"fieldType"      : "str"
                            ,"labelCol"       : "PLACA"
                            ,"obj"            : "edtPlaca"
                            ,"tamGrd"         : "7em"
                            ,"tamImp"         : "15"
                            ,"ajudaCampo"     : ["Placa do veiculo"]
                            ,"padrao":0}
                  ,{"id":6  ,"field"          : "VCL_FROTA"   
                            ,"fieldType"      : "str"
                            ,"labelCol"       : "PL"
                            ,"obj"            : "edtFrota"
                            ,"tamGrd"         : "2em"
                            ,"tamImp"         : "5"
                            ,"ajudaCampo"     : ["Veiculo pesado/leve"]
                            ,"padrao":0}
                  ,{"id":7  ,"field"          : "MVM_RFID"   
                            ,"fieldType"      : "str"
                            ,"labelCol"       : "RFID"
                            ,"obj"            : "edtRfid"
                            ,"tamGrd"         : "7em"
                            ,"tamImp"         : "15"
                            ,"ajudaCampo"     : ["RFID do motorista"]
                            ,"padrao":0}
                  ,{"id":8  ,"field"          : "MVM_VELOCIDADE"   
                            ,"fieldType"      : "int"
                            ,"align"          : "center"
                            ,"labelCol"       : "VEL"
                            ,"obj"            : "edtVelocidade"
                            ,"tamGrd"         : "5em"
                            ,"tamImp"         : "10"
                            ,"ajudaCampo"     : ["Velocidade"]
                            ,"padrao":0}
                  ,{"id":9  ,"field"          : "MVM_RPM"   
                            ,"fieldType"      : "int"
                            ,"align"          : "center"
                            ,"labelCol"       : "RPM"
                            ,"obj"            : "edtRpm"
                            ,"tamGrd"         : "5em"
                            ,"tamImp"         : "10"
                            ,"ajudaCampo"     : ["Rpm"]
                            ,"padrao":0}
                  ,{"id":10 ,"field"          : "MTR_NOME"   
                            ,"fieldType"      : "str"
                            ,"labelCol"       : "MOTORISTA"
                            ,"obj"            : "edtMotorista"
                            ,"tamGrd"         : "25em"
                            ,"tamImp"         : "80"
                            ,"ajudaCampo"     : ["Motorista"]
                            ,"padrao":0}
                  ,{"id":11 ,"field"          : "BIEV_LOCALIZACAO"   
                            ,"fieldType"      : "str"
                            ,"labelCol"       : "LOCALIZACAO"
                            ,"obj"            : "edtLocalizacao"
                            ,"tamGrd"         : "30em"
                            ,"tamImp"         : "80"
                            ,"ajudaCampo"     : ["Localizacao"]
                            ,"padrao":0}
                ]  
                , 
                "botoesH":[
                   {"texto":"Excel"     ,"name":"detExcel"  ,"onClick":"5"  ,"enabled":true,"imagem":"fa fa-file-excel-o"     ,"ajuda":"Exportar para excel" }
                  ,{"texto":"Retornar"  ,"name":"detVoltar" ,"onClick":"7"  ,"enabled":true ,"imagem":"fa fa-plus","ajuda":"Retorna a tela anterior" }
                ] 
                ,"registros"      : ret[0].dados              // Recebe um Json vindo da classe clsBancoDados
                ,"refazClasse"    : "S"
                //,"corLinha"       : "switch (ceTr.cells[1].innerHTML){case '1' : ceTr.style.backgroundColor='red';break; case '2' : ceTr.style.backgroundColor='#6CA6CD';break;}"          
                ,"opcRegSeek"     : true                      // Opção para numero registros/botão/procurar                     
                ,"checarTags"     : "S"                       // Somente em tempo de desenvolvimento(olha as pricipais tags)                  
                ,"div"            : "frmComp"                 // Onde vai ser gerado a table
                ,"divFieldSet"    : "tabela"                  // Para fechar a div onde estão os fieldset ao cadastrar
                ,"form"           : "frm"                     // Onde vai ser gerado o fieldSet       
                ,"divModal"       : "divDetalheReg"           // Onde vai se appendado abaixo deste a table 
                ,"tbl"            : "tblComp"                 // Nome da table
                ,"prefixo"        : "es"                      // Prefixo para elementos do HTML em jsTable2017.js
                ,"tabelaBD"       : "BI_EXCESSOVELOC"         // Nome da tabela no banco de dados  
                ,"iFrame"         : "iframeCorpo"             // Se a table vai ficar dentro de uma tag iFrame
                ,"width"          : "110em"                   // Tamanho da table
                ,"height"         : "58em"                    // Altura da table
                ,"tableLeft"      : "sim"                     // Se tiver menu esquerdo
                ,"relTitulo"      : "DETALHE EVENTO"          // Titulo do relatório
                ,"relOrientacao"  : "P"                       // Paisagem ou retrato
                ,"relFonte"       : "8"                       // Fonte do relatório
                ,"indiceTable"    : "ID"                      // Indice inicial da table
                ,"tamBotao"       : "12"                      // Tamanho botoes defalt 12 [12/25/50/75/100]
                ,"tamMenuTable"   : ["10em","20em"]                                
                ,"codTblUsu"      : "USUARIO[01]"                          
                ,"codDir"         : intCodDir
              }; 
              if( objDet === undefined ){  
                objDet=new clsTable2017("objDet");
              };
              objDet.montarHtmlCE2017(jsDet);
              window.location.href="#ancoraDetalhe";
              var el = document.getElementsByClassName("acordeon");
              for( var lin=0;lin<el.length;lin++ ){
                if( (el[lin].id=="btnDetalhe") && (el[lin].className != "acordeon acrdnAtivo") ){
                  document.getElementById("btnDetalhe").click();  
                  window.location.href="#ancoraDetalhe";
                };  
              }; 
              //
            } else {
              throw ret[0].erro;
            }
          };
        }catch( e ){
          console.log(req);
          gerarMensagemErro("Composição do evento",e,"Erro");
        }
      };
      //
      //
      ///////////////////////////////////////
      //     AJUDA PARA EVENTOGRUPO        //
      ///////////////////////////////////////
      function egFocus(obj){ 
        document.getElementById(obj.id).setAttribute("data-oldvalue",document.getElementById(obj.id).value); 
      };
      function egF10Click(){ fEventoGrupoF10(0,"edtCodEg","cbAtivo"); };  
      function RetF10tblEg(arr){
        document.getElementById("edtCodEg").value    = arr[0].CODIGO;
        document.getElementById("edtDesEg").value    = arr[0].DESCRICAO;
      };
      function codEgBlur(obj){
        var elOld = document.getElementById(obj.id).getAttribute("data-oldvalue");
        var elNew = obj.value;
        if( elOld != elNew ){
          var ret = fEventoGrupoF10(1,obj.id,"cbAtivo");
          document.getElementById(obj.id).value       = ( ret.length == 0 ? ""    : ret[0].CODIGO               );
          document.getElementById("edtDesEg").value  = ( ret.length == 0 ? ""    : ret[0].DESCRICAO             );
          document.getElementById(obj.id).setAttribute("data-oldvalue",( ret.length == 0 ? "" : ret[0].CODIGO ) );
        };
      };
      function detVoltarClick(){
        document.getElementById("btnDetalhe").click();
        window.location.href="#ancoraCabec";
      };
    </script>
  </head>
  <body>
    <div class="divTelaCheia">
      <a name="ancoraCabec"></a> 
      <div id="divRotina" class="conteudo" style="display:block;overflow-x:auto;position:relative;float:left;width:100em;height:50em;">  
        <div id="divTopoInicio">
        </div>
        <form method="post" 
              name="frmEve" 
              id="frmEve" 
              class="frmTable" 
              action="classPhp/imprimirsql.php" 
              target="_newpage"
              style="top: 6em; width:98em;position: absolute; z-index:30;display:none;">
          <p class="frmCampoTit">
          <input class="informe" type="text" name="titulo" value="Eventos" disabled="" style="color: white; text-align: left;">
          <div style="height: 200px; overflow-y: auto;">
            <input type="hidden" id="sql" name="sql"/>
            <div class="campotexto campo100">
              <div class="campotexto campo25">
                <input class="campo_input" id="edtCodigo" type="text" maxlength="5" />
                <label class="campo_label campo_required" for="edtCodigo">CODIGO</label>
              </div>
              <div class="campotexto campo75">
                <input class="campo_input" id="edtDescricao" type="text" maxlength="20" />
                <label class="campo_label campo_required" for="edtDescricao">DESCRICAO</label>
              </div>
              
              <div class="campotexto campo15">
                <input class="campo_input inputF10" id="edtCodEg"
                                                    onBlur="codEgBlur(this);" 
                                                    onFocus="egFocus(this);" 
                                                    onClick="egF10Click('edtCodVmr');"
                                                    data-oldvalue=""
                                                    autocomplete="off"
																								    maxlength="3"
                                                    type="text" />
                <label class="campo_label campo_required" for="edtCodEg">GRUPO</label>
              </div>
              <div class="campotexto campo35">
                <input class="campo_input_titulo input" id="edtDesEg" type="text" disabled />
                <label class="campo_label campo_required" for="edtDesEg">NOME_GRUPO</label>
              </div>
              <div class="campotexto campo25">
                <select class="campo_input_combo" id="cbMovimento">
                  <option value="S">SIM</option>
                  <option value="N">NAO</option>
                </select>
                <label class="campo_label campo_required" for="cbMovimento">ENTRA MOVIMENTO</label>
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
      <!--
      <a name="ancoraCom"></a> 
      <div id="divComposicao" class="conteudo" style="position:relative;float:left;display:none;overflow-x:auto;width:115em;height:60em;">
        <div id="divComposicaoTitulo">
        </div>
      </div>
      -->
      
      <a name="ancoraDetalhe">
      <button id="btnDetalhe"
              class="acordeon"
              style="width:25%;margin-left:0.1em;">Detalhe do registro</button>
      <div class="acrdnDiv" style="width:90%;margin-left:0.1em;height:60em;">
        <div id="divDetalhe" class="conteudo" style="position:relative;float:left;height:41em;width:150em;">
          <div id="divDetalheReg">
          </div>
        </div>
      </div>
    </div>  
    <script>
      var acc = document.getElementsByClassName("acordeon");
      var i;

      for (i = 0; i < acc.length; i++) {
        acc[i].addEventListener("click", function() {
          this.classList.toggle("acrdnAtivo");
          var panel = this.nextElementSibling;
          if (panel.style.maxHeight){
            panel.style.maxHeight = null;
          } else {
            panel.style.maxHeight = panel.scrollHeight + "px";
          } 
        });
      }
    </script>
  </body>
</html>