<?php
  session_start();
  if( isset($_POST["grupoOperacional"]) ){
    try{     
      require("../../conectaSqlServer.class.php");
      require("../../validaJson.class.php"); 
      require("../../removeAcento.class.php"); 

      $vldr     = new validaJSon();          
      $retorno  = "";
      $retCls   = $vldr->validarJs($_POST["grupoOperacional"]);
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
        /////////////////////////////////////////////////
        //   Dados para JavaScript GRUPO OPERACIONAL   //
        /////////////////////////////////////////////////
        if( $rotina=="selectGpo" ){
          $sql="";
          $sql.="SELECT";
          $sql.="   GPO_CODIGO";
          $sql.="  ,GPO_NOME";
          $sql.="  ,US_APELIDO";
          $sql.="  ,GPO_CODUSR";
          $sql.="  FROM GRUPOOPERACIONAL GPO";
          $sql.="   LEFT OUTER JOIN USUARIOSISTEMA U ON GPO.GPO_CODUSR=U.US_CODIGO";
          $classe->msgSelect(false);
          $retCls=$classe->select($sql);
          if( $retCls['retorno'] != "OK" ){
            $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';  
          } else { 
            $retorno='[{"retorno":"OK","dados":'.json_encode($retCls['dados']).',"erro":""}]'; 
          };  
        };
        if( $rotina=="verificaInstrucao" ){
          $sql="";
          $sql.="SELECT GPO_CODIGO FROM GRUPOOPERACIONAL WHERE GPO_CODIGO =".$lote[0]->gpoCodigo;
          $classe->msgSelect(false);
          $retCls=$classe->select($sql);
          if( $retCls['retorno'] != "OK" ){
            $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';  
          } else { 
            $retorno='[{"retorno":"OK","dados":'.json_encode($retCls['dados']).',"erro":""}]'; 
          };  
        };
        if( $rotina=="buscarUnidadesGpo" ){
          $sql="";
          $sql.="SELECT U.UNI_CODIGO AS CODIGO, U.UNI_NOME AS DESCRICAO FROM GRUPOOPERACIONALUNIDADE GOU
          INNER JOIN UNIDADE U ON U.UNI_CODIGO=GOU_CODUNI WHERE GOU_CODGPO =".$lote[0]->grupoOperacional;
          $classe->msgSelect(false);
          $retCls=$classe->selectAssoc($sql);
          if( $retCls['retorno'] != "OK" ){
            $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';  
          } else { 
            $retorno='[{"retorno":"OK","dados":'.json_encode($retCls['dados']).',"erro":""}]'; 
          };  
        };
        if( $rotina=="insertGpo" ){
          $arrUpdt = [];
          $sql="";
          $somasql="";
          $sql.="INSERT INTO GRUPOOPERACIONAL (GPO_NOME, GPO_CODUSR) VALUES(UPPER('".$lote[0]->gpoNome."'),".$lote[0]->usrCodigo.")";
          array_push($arrUpdt, $sql);
          $arrUnidades = explode("|",$lote[0]->unidades );
          foreach ($arrUnidades as $codigo) {
              $sql="INSERT INTO GRUPOOPERACIONALUNIDADE (GOU_CODGPO, GOU_CODUNI) VALUES (".$lote[0]->gpoCodigo.",".$codigo.")";
              array_push($arrUpdt, $sql);
            }
          $retCls=$classe->cmd($arrUpdt);
          if( $retCls['retorno'] != "OK" ){
            $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';  
          } else { 
            $retorno='[{"retorno":"OK","dados":'.json_encode($retCls['dados']).',"erro":""}]'; 
          };  
        };
        if( $rotina=="updateGpo" ){
          $arrUpdt = [];
          $sql="";
          $somasql="";
          $sql.="UPDATE GRUPOOPERACIONAL SET GPO_NOME =UPPER('".$lote[0]->gpoNome."'),"
                 ."GPO_CODUSR = ".$lote[0]->usrCodigo." WHERE GPO_CODIGO =".$lote[0]->gpoCodigo;
          array_push($arrUpdt, $sql);
          $sql="DELETE FROM GRUPOOPERACIONALUNIDADE WHERE GOU_CODGPO =".$lote[0]->gpoCodigo;
          array_push($arrUpdt, $sql);  
          $arrUnidades = explode("|",$lote[0]->unidades );
          foreach ($arrUnidades as $codigo) {
              $sql="INSERT INTO GRUPOOPERACIONALUNIDADE (GOU_CODGPO, GOU_CODUNI) VALUES (".$lote[0]->gpoCodigo.",".$codigo.")";
              array_push($arrUpdt, $sql);
            }
          $retCls=$classe->cmd($arrUpdt);
          if( $retCls['retorno'] != "OK" ){
            $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';  
          } else { 
            $retorno='[{"retorno":"OK","dados":'.json_encode($retCls['dados']).',"erro":""}]'; 
          };  
        };
        if( $rotina=="deleteGpo" ){
          $arrUpdt = [];
          $sql="";
          $somasql="";
          $sql.="DELETE FROM GRUPOOPERACIONAL WHERE GPO_CODIGO =".$lote[0]->gpoCodigo;
          array_push($arrUpdt, $sql);
          $sql="DELETE FROM GRUPOOPERACIONALUNIDADE WHERE GOU_CODGPO =".$lote[0]->gpoCodigo;
          array_push($arrUpdt, $sql); 
          $retCls=$classe->cmd($arrUpdt);
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
    <title>Grupo Operacional</title>
    <link rel="stylesheet" href="../../../css/css2017.css">
    <link rel="stylesheet" href="../../../css/cssTable2017.css">
    <link rel="stylesheet" href="../../../css/cssFaTable.css">
    <script src="../../../js/js2017.js"></script>
    <script src="../../../js/jsTable2017.js"></script>
    <script src="../../../tabelaTrac/f10/tabelaUnidadeMultipleF10.js"></script>
    <script language="javascript" type="text/javascript"></script>
    <script>
      "use strict";
      ////////////////////////////////////////////////
      // Executar o codigo após a pagina carregada  //
      ////////////////////////////////////////////////
      document.addEventListener("DOMContentLoaded", function(){ 
        /////////////////////////////////////////////
        //         Objeto clsTable2017 GRUPO       //
        /////////////////////////////////////////////
        jsGpo={
          "titulo":[
             {"id":0  ,"labelCol":"OPC"     
                      ,"padrao":1}            
            ,{"id":1  ,"field"          :"GPO_CODIGO" 
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
                      ,"insUpDel"       : ["N","N","N"]
                      ,"validar"        : ["notnull"]
                      ,"ajudaCampo"     : [  "Codigo do grupo conforme definição empresa. Este campo é único e deve tem o formato 9999"
                                            ,"Para ver este grupo o usuario precisa ter direito a no minimo uma unidade pertencente a este"]
                      ,"importaExcel"   : "S"                                                                
                      ,"padrao":0}
            ,{"id":2  ,"field"          : "GPO_NOME"   
                      ,"labelCol"       : "DESCRICAO"
                      ,"obj"            : "edtDescricao"
                      ,"tamGrd"         : "50em"
                      ,"tamImp"         : "85"
                      ,"digitosMinMax"  : [3,60]
                      ,"digitosValidos" : "A|B|C|D|E|F|G|H|I|J|K|L|M|N|O|P|Q|R|S|T|U|V|W|X|Y|Z|0|1|2|3|4|5|6|7|8|9| "
                      ,"ajudaCampo"     : ["Nome do grupo operacional com até 20 caracteres."]
                      ,"importaExcel"   : "S"                                          
                      ,"padrao":0}
            ,{"id":3  ,"field"          : "US_APELIDO" 
                      ,"labelCol"       : "USUARIO" 
                      ,"obj"            : "edtUsuario" 
                      ,"padrao":4}        
            ,{"id":4 ,"field"          : "GPO_CODUSR" 
                      ,"labelCol"       : "CODUSU"  
                      ,"obj"            : "edtCodUsu"  
                      ,"padrao":5}                                           
            ,{"id":5  ,"labelCol"       : "PP"      
                      ,"obj"            : "imgPP"        
                      ,"func":"var elTr=this.parentNode.parentNode;"
                        +"elTr.cells[0].childNodes[0].checked=true;"
                        +"objGpo.espiao();"
                        +"elTr.cells[0].childNodes[0].checked=false;"
                      ,"padrao":8}
          ]
          ,
          "detalheRegistro":
          [
            { "width"           :"100%"
              ,"height"         :"300px" 
              ,"label"          :"GRUPO - Detalhe do registro"
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
          ,"idBtnConfirmarCustom" : "idBtnConfirmarCustom"            // Se existir executa o confirmar do form/fieldSet
          ,"idBtnCancelar"  : "btnCancelar"             // Se existir executa o cancelar do form/fieldSet
          ,"div"            : "frmGpo"                  // Onde vai ser gerado a table
          ,"divFieldSet"    : "tabelaGpo"               // Para fechar a div onde estão os fieldset ao cadastrar
          ,"form"           : "frmGpo"                  // Onde vai ser gerado o fieldSet       
          ,"divModal"       : "divTopoInicio"           // Onde vai se appendado abaixo deste a table 
          ,"tbl"            : "tblGpo"                  // Nome da table
          ,"prefixo"        : "Gpo"                     // Prefixo para elementos do HTML em jsTable2017.js
          ,"tabelaBD"       : "GRUPOOPERACIONAL"        // Nome da tabela no banco de dados  
          ,"tabelaBKP"      : "BKPGRUPO"                // Nome da tabela no banco de dados  
          ,"fieldCodUsu"    : "GPO_CODUSR"              // SE EXISITIR - Nome do campo CODIGO USUARIO na tabela BD                        
          ,"iFrame"         : "iframeCorpo"             // Se a table vai ficar dentro de uma tag iFrame
          ,"width"          : "90em"                    // Tamanho da table
          ,"height"         : "58em"                    // Altura da table
          ,"tableLeft"      : "sim"                     // Se tiver menu esquerdo
          ,"relTitulo"      : "GRUPOOPERACIONAL"        // Titulo do relatório
          ,"relOrientacao"  : "R"                       // Paisagem ou retrato
          ,"relFonte"       : "8"                       // Fonte do relatório
          ,"foco"           : ["edtDescricao"
                              ,"edtDescricao"
                              ,"idBtnConfirmarCustom"]          // Foco qdo Cad/Alt/Exc
          ,"formPassoPasso" : "/Trac_Espiao.php"         // Enderço da pagina PASSO A PASSO
          ,"indiceTable"    : "DESCRICAO"               // Indice inicial da table
          ,"tamBotao"       : "15"                      // Tamanho botoes defalt 12 [12/25/50/75/100]
          ,"tamMenuTable"   : ["10em","20em"]                                
          ,"labelMenuTable" : "Opções"                  // Caption para menu table 
          ,"_menuTable"     :[
                                ["Regitros ativos"                        ,"fa-thumbs-o-up"   ,"btnFiltrarClick('S');"]
                               ,["Registros inativos"                     ,"fa-thumbs-o-down" ,"btnFiltrarClick('N');"]
                               ,["Todos"                                  ,"fa-folder-open"   ,"btnFiltrarClick('*');"]
                               //,["Importar planilha excel"                ,"fa-file-excel-o"  ,"fExcel()"]
                               ,["Imprimir registros em tela"             ,"fa-print"         ,"objGpo.imprimir()"]
                               ,["Ajuda para campos padrões"              ,"fa-info"          ,"objGpo.AjudaSisAtivo(jsGpo);"]
                               ,["Detalhe do registro"                    ,"fa-folder-o"      ,"objGpo.detalhe();"]
                               ,["Gerar excel"                            ,"fa-file-excel-o"  ,"objGpo.excel();"]
                               ,["Passo a passo do registro"              ,"fa-binoculars"    ,"objGpo.espiao();"]
                               ,["Alterar status Ativo/Inativo"           ,"fa-share"         ,"objGpo.altAtivo(intCodDir);"]
                               ,["Alterar registro PUBlico/ADMinistrador" ,"fa-reply"         ,"objGpo.altPubAdm(intCodDir,jsPub[0].usr_admpub);"]
                               //,["Alterar para registro do SISTEMA"       ,"fa-reply"         ,"objGpo.altRegSistema("+jsPub[0].usr_d05+");"]                               
                               ,["Atualizar grade consulta"               ,"fa-filter"        ,"btnFiltrarClick('S');"]                               
                             ]  
          ,"codTblUsu"      : "USUARIO[01]"                          
          ,"codDir"         : intCodDir
        }; 
        if( objGpo === undefined ){  
          objGpo=new clsTable2017("objGpo", true);
        };  
        objGpo.montarHtmlCE2017(jsGpo); 
        //////////////////////////////////////////////////
        //          Fim objeto clsTable2017 GRUPO       //
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
          ,"relTitulo"      : "Importação grupo"        // Titulo do relatório
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
      var objGpo;                     // Obrigatório para instanciar o JS TFormaCob
      var jsGpo;                      // Obj principal da classe clsTable2017
      var objUniF10;                  // Obrigatório para instanciar o JS CidadeF10
      var dadosUnidade = [];          // Unidades escolhidas no multiple select
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
        clsJs   = jsString("lote");  
        clsJs.add("rotina"      , "selectGpo"         );
        clsJs.add("login"       , jsPub[0].usr_login  );
        fd = new FormData();
        fd.append("grupoOperacional" , clsJs.fim());
        msg     = requestPedido("grupoOperacional.php",fd); 
        retPhp  = JSON.parse(msg);
        if( retPhp[0].retorno == "OK" ){
          //////////////////////////////////////////////////////////////////////////////////
          // O novo array não tem o campo idUnico mas a montarHtmlCE2017 ja foi executada //
          // Campo obrigatório se existir rotina de manutenção na table devido Json       //
          // Esta rotina não tem manutenção via classe clsTable2017                       //
          // jsCrv.registros=objCrv.addIdUnico(retPhp[0]["dados"]);                       //
          //////////////////////////////////////////////////////////////////////////////////
          jsGpo.registros=objGpo.addIdUnico(retPhp[0]["dados"]);
          objGpo.ordenaJSon(jsGpo.indiceTable,false);  
          objGpo.montarBody2017();
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
          clsJs.add("cabec"       , "CODIGO|DESCRICAO"  );          

          fd = new FormData();
          fd.append("grupoOperacional"      , clsJs.fim());
          fd.append("arquivo"   , edtArquivo.files[0] );
          msg     = requestPedido("grupoOperacional.php",fd); 
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
          gerarMensagemErro("Gpo",retPhp[0].erro,"AVISO");    
        };  
      };
      function uniFocus(obj){
        document.getElementById(obj.id).setAttribute("data-oldvalue",document.getElementById(obj.id).value);
      };
      function limparCampos(){
        document.getElementById('unidadeSelect').innerHTML = '';
      }

      function preencherSelect(chkds){
        clsJs   = jsString("lote");  
        clsJs.add("rotina"      , "buscarUnidadesGpo"         );
        clsJs.add("login"       , jsPub[0].usr_login  );
        clsJs.add("grupoOperacional", chkds[0]['CODIGO']  );          
        fd = new FormData();
        fd.append("grupoOperacional" , clsJs.fim());
        msg     = requestPedido("grupoOperacional.php",fd); 
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

    function updateDados() {
      clsJs   = jsString("lote");  
      clsJs.add("rotina"      , "updateGpo"         );
      clsJs.add("login"       , jsPub[0].usr_login  );
      clsJs.add("usrCodigo"       , jsPub[0].usr_codigo  );
      clsJs.add("gpoCodigo", document.getElementById('edtCodigo').value);   
      clsJs.add("gpoNome", document.getElementById('edtDescricao').value);   
      let unidadesString = "";       
      dadosUnidade.forEach(element => {
        unidadesString+=(element['CODIGO']+"|"); 
      });
      unidadesString = unidadesString.substring(0, unidadesString.length - 1)
  
      clsJs.add("unidades", unidadesString);          
      fd = new FormData();
      fd.append("grupoOperacional" , clsJs.fim());
      msg     = requestPedido("grupoOperacional.php",fd); 
      retPhp  = JSON.parse(msg);
      document.getElementById('unidadeSelect').innerHTML = '';

    }
    function deleteDados() {
      clsJs   = jsString("lote");  
      clsJs.add("rotina"      , "deleteGpo"         );
      clsJs.add("login"       , jsPub[0].usr_login  );
      clsJs.add("usrCodigo"       , jsPub[0].usr_codigo  );
      clsJs.add("gpoCodigo", document.getElementById('edtCodigo').value);        
      fd = new FormData();
      fd.append("grupoOperacional" , clsJs.fim());
      msg     = requestPedido("grupoOperacional.php",fd); 
      retPhp  = JSON.parse(msg);
      document.getElementById('unidadeSelect').innerHTML = '';
    }

    
    function salvarDados() {
      clsJs   = jsString("lote");  
      clsJs.add("rotina"      , "insertGpo"         );
      clsJs.add("login"       , jsPub[0].usr_login  );
      clsJs.add("usrCodigo"       , jsPub[0].usr_codigo  );
      clsJs.add("gpoCodigo", document.getElementById('edtCodigo').value);   
      clsJs.add("gpoNome", document.getElementById('edtDescricao').value);   
      let unidadesString = "";       
      dadosUnidade.forEach(element => {
        unidadesString+=(element['CODIGO']+"|"); 
      });
      unidadesString = unidadesString.substring(0, unidadesString.length - 1)

      clsJs.add("unidades", unidadesString);          
      fd = new FormData();
      fd.append("grupoOperacional" , clsJs.fim());
      msg     = requestPedido("grupoOperacional.php",fd); 
      retPhp  = JSON.parse(msg);
      document.getElementById('unidadeSelect').innerHTML = '';
    }
      function uniF10Click(){
        fUnidadeF10(0,"edtCodUni","soAtivo");
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
        // document.getElementById("edtCodUni").value   = arr[0].CODIGO;
        // document.getElementById("edtDesUni").value   = arr[0].APELIDO;
        // document.getElementById("edtCodUni").setAttribute("data-oldvalue",arr[0].CODIGO);
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
    </script>
  </head>
  <body>
    <div class="divTelaCheia">
      <div id="divRotina" class="conteudo" style="display:block;overflow-x:auto;">  
        <div id="divTopoInicio">
        </div>
        <form method="post" 
              name="frmGpo" 
              id="frmGpo" 
              class="frmTable" 
              action="/classPhp/imprimirsql.php" 
              target="_newpage"
              style="top: 6em; width:90em;position: absolute; z-index:30;display:none;">
          <p class="frmCampoTit">
          <input class="informe" type="text" name="titulo" value="Grupo Operacional" disabled="" style="color: white; text-align: left;">
          </p>
          <div style="height: 360px; overflow-y: auto;">
            <input type="hidden" id="sql" name="sql"/>
            <div class="campotexto campo100">
              <div class="campotexto campo50">
                <input class="campo_input" id="edtCodigo" type="text" maxlength="5" />
                <label class="campo_label campo_required" for="edtCodigo">CODIGO</label>
              </div>
              <div class="campotexto campo50">
                <input class="campo_input_titulo" disabled id="edtUsuario" type="text" />
                <label class="campo_label campo_required" for="edtUsuario">USUARIO</label>
              </div>
              <div class="campotexto campo100">
                <input class="campo_input" id="edtDescricao" upper type="text" maxlength="60" />
                <label class="campo_label campo_required" for="edtDescricao">DESCRICAO</label>
              </div>
              <div class="campotexto campo100">
                <button class="campo100 tableBotao botaoHorizontal" type="button" id="edtCodUni" onClick="uniF10Click('edtCodUni');">ESCOLHER UNIDADES </button>
                <label for="edtCodUni">UNIDADES SELECIONADAS</label>
              </div>
              <div class="campotexto campo100">
                <select id="unidadeSelect" multiple size="8" class="campo100">
                </select>              
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
                  <input id="idBtnConfirmarCustom" type="button" value="Confirmar" class="campo100 tableBotao botaoForaTable"/>            
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
          <form method="post" name="frmExc" class="center" id="frmExc" action="classPhp/imprimirsql.php" target="_newpage" >
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