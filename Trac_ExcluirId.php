<?php
  session_start();
  if( isset($_POST["excluirid"]) ){
    try{     
      require("classPhp/conectaSqlServer.class.php");
      require("classPhp/validaJson.class.php"); 
      require("classPhp/removeAcento.class.php"); 

      $vldr     = new validaJSon();          
      $retorno  = "";
      $retCls   = $vldr->validarJs($_POST["excluirid"]);
      ///////////////////////////////////////////////////////////////////////
      // Variavel mostra que não foi feito apenas selects mas atualizou BD //
      ///////////////////////////////////////////////////////////////////////
      $atuBd    = false;
      if($retCls["retorno"] != "OK"){
        $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';
        unset($retCls,$vldr);      
      } else {
        $arrUpdt  = []; 
        $jsonObj  = $retCls["dados"];
        $lote     = $jsonObj->lote;
        $rotina   = $lote[0]->rotina;
        $classe   = new conectaBd();
        $classe->conecta($lote[0]->login);
        /////////////////////////////
        //   Buscando os excluidos //
        /////////////////////////////
        if( $rotina=="movimentoexc" ){
          $sql="";
          $sql.="SELECT A.ME_POSICAO";
          $sql.="       ,CONVERT(VARCHAR(10),A.ME_DATA,127) AS ME_DATA";
          $sql.="       ,CONVERT(VARCHAR(23),MVM.MVM_DATAGPS,127) AS MVM_DATAGPS";
          $sql.="       ,MVM.MVM_CODEG";
          $sql.="       ,U.US_APELIDO";
          $sql.="  FROM MOVIMENTOEXC A";
          $sql.="  LEFT OUTER JOIN USUARIOSISTEMA U ON A.ME_CODUSR=U.US_CODIGO";          
          $sql.="  LEFT OUTER JOIN MOVIMENTO MVM ON A.ME_POSICAO=MVM.MVM_POSICAO";                    
          $sql.=" WHERE (A.ME_DATA>='".$lote[0]->data."')";
          $classe->msgSelect(false);
          $retCls=$classe->select($sql);
          if( $retCls['retorno'] != "OK" ){
            $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';  
          } else { 
            $retorno='[{"retorno":"OK","dados":'.json_encode($retCls['dados']).',"erro":""}]'; 
          };  
        };  
        /////////////////////////////////////////////////
        //                 Excluir Id                  //
        // Usando o campo placa para checar a infracao //
        // entrabi eh se for inverter de N->S          //  
        /////////////////////////////////////////////////
        if( $rotina=="delId" ){
          $atuBd=true;
          $sql="";
          $sql.="UPDATE MOVIMENTO ";
          $sql.="       SET MVM_PLACA='".$lote[0]->infracao."'";     //Faz o campo MVM_CODEG
          $sql.="           ,MVM_CODEVE='".$lote[0]->codusr."'";       //Faz o campo USR_CODIGO
          $sql.=" WHERE MVM_POSICAO=".$lote[0]->posicao;
          array_push($arrUpdt,$sql);
        };
        ////////////////////////////////////////////////
        //       Atualizando o banco de dados         //
        ////////////////////////////////////////////////
        if( $atuBd ){
          if( count($arrUpdt) >0 ){
            $retCls=$classe->cmd($arrUpdt);
            if( $retCls['retorno']=="OK" ){
              $retorno='[{"retorno":"OK","dados":"","erro":"'.count($arrUpdt).' REGISTRO(s) ATUALIZADO(s)!"}]'; 
            } else {
              $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';  
            };  
          } else {
            $retorno='[{"retorno":"OK","dados":"","erro":"NENHUM REGISTRO CADASTRADO!"}]';
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
    <title>Excluir ID</title>
    <link rel="stylesheet" href="css/css2017.css">
    <link rel="stylesheet" href="css/cssTable2017.css">
    <!--<link rel="stylesheet" href="css/cssFaTable.css">-->
    <link rel="stylesheet" href="css/Acordeon.css">            
    <script src="js/js2017.js"></script>
    <script src="js/jsTable2017.js"></script>
    <script language="javascript" type="text/javascript"></script>
    <script>
      "use strict";
      ////////////////////////////////////////////////
      // Executar o codigo após a pagina carregada  //
      ////////////////////////////////////////////////
      document.addEventListener("DOMContentLoaded", function(){ 
        document.getElementById("edtCodigo").focus();
        document.getElementById("edtData").value = jsDatas(-1).retDDMMYYYY();
        //////////////////////////////////////
        // Objeto clsTable2017 MOVIMENTOEXC //
        //////////////////////////////////////
        jsMe={
          "titulo":[
             /* 
             {"id":0  ,"labelCol":"OPC"     
                      ,"padrao":1}            
            */          
            {"id":0  ,"field"          : "ME_POSICAO" 
                      ,"labelCol"       : "POSICAO"
                      ,"obj"            : "edtPosicao"
                      ,"tamGrd"         : "8em"
                      ,"tamImp"         : "20"
                      ,"padrao":0}
            ,{"id":1  ,"field"          : "ME_DATA" 
                      ,"labelCol"       : "DATAEXC"
                      ,"obj"            : "edtData"
                      ,"fieldType"      : "dat"
                      ,"tamGrd"         : "8em"
                      ,"tamImp"         : "15"
                      ,"padrao":0}
            ,{"id":2  ,"field"          : "MVM_DATAGPS" 
                      ,"labelCol"       : "DATAGPS"
                      ,"obj"            : "edtDataGps"
                      ,"fieldType"      : "str"
                      ,"align"          : "center"                      
                      ,"tamGrd"         : "14em"
                      ,"tamImp"         : "15"
                      ,"padrao":0}
            ,{"id":3  ,"field"          : "MVM_CODEG" 
                      ,"labelCol"       : "INFR"
                      ,"obj"            : "edtInfr"
                      ,"tamGrd"         : "4em"
                      ,"tamImp"         : "20"
                      ,"padrao":0}
            ,{"id":4  ,"field"          : "US_APELIDO" 
                      ,"labelCol"       : "USUARIO" 
                      ,"obj"            : "edtUsuario" 
                      ,"padrao":4}                
          ]
          /*
          ,
          "detalheRegistro":
          [
            { "width"           :"100%"
              ,"height"         :"500px" 
              ,"label"          :"VEICULO - Detalhe do registro"
            }
          ]
          */
          /*
          , 
          "botoesH":[
             {"texto":"Cadastrar" ,"name":"horCadastrar"  ,"onClick":"0"  ,"enabled":true ,"imagem":"fa fa-plus"             ,"ajuda":"Novo registro" }
            ,{"texto":"Alterar"   ,"name":"horAlterar"    ,"onClick":"1"  ,"enabled":true ,"imagem":"fa fa-pencil-square-o"  ,"ajuda":"Alterar registro selecionado" }
            ,{"texto":"Excluir"   ,"name":"horExcluir"    ,"onClick":"2"  ,"enabled":true ,"imagem":"fa fa-minus"            ,"ajuda":"Excluir registro selecionado" }
            ,{"texto":"Excel"     ,"name":"horExcel"      ,"onClick":"5"  ,"enabled":false,"imagem":"fa fa-file-excel-o"     ,"ajuda":"Exportar para excel" }        
            ,{"texto":"Fechar"    ,"name":"horFechar"     ,"onClick":"8"  ,"enabled":true ,"imagem":"fa fa-close"            ,"ajuda":"Fechar formulario" }
          ] 
          */
          ,"registros"      : []                    // Recebe um Json vindo da classe clsBancoDados
          ,"opcRegSeek"     : true                  // Opção para numero registros/botão/procurar                     
          ,"checarTags"     : "S"                   // Somente em tempo de desenvolvimento(olha as pricipais tags)                  
          //,"idBtnCancelar"  : "btnCancelar"       // Se existir executa o cancelar do form/fieldSet
          ,"div"            : "frmMe"               // Onde vai ser gerado a table
          ,"divFieldSet"    : "tabelaMe"            // Para fechar a div onde estão os fieldset ao cadastrar
          ,"form"           : "frmMe"               // Onde vai ser gerado o fieldSet       
          ,"divModal"       : "divMovimentoExcReg"  // Onde vai se appendado abaixo deste a table 
          ,"tbl"            : "tblMe"               // Nome da table
          ,"prefixo"        : "me"                  // Prefixo para elementos do HTML em jsTable2017.js
          ,"tabelaBD"       : "MOVIMENTOEXC"        // Nome da tabela no banco de dados  
          ,"tabelaBKP"      : "*"                   // Nome da tabela no banco de dados  
          ,"fieldAtivo"     : "**"                  // SE EXISITIR - Nome do campo ATIVO(S/N) na tabela BD
          ,"fieldReg"       : "*"                   // SE EXISITIR - Nome do campo SYS(P/A) na tabela BD            
          ,"fieldCodUsu"    : "*"                   // SE EXISITIR - Nome do campo CODIGO USUARIO na tabela BD                        
          ,"iFrame"         : "iframeCorpo"         // Se a table vai ficar dentro de uma tag iFrame
          ,"width"          : "55em"                // Tamanho da table
          ,"height"         : "27em"                // Altura da table
          ,"tableLeft"      : "sim"                 // Se tiver menu esquerdo
          ,"relTitulo"      : "ID EXCLUIDOS"        // Titulo do relatório
          ,"relOrientacao"  : "P"                   // Paisagem ou retrato
          ,"relFonte"       : "8"                   // Fonte do relatório
          /*
          ,"foco"           : ["edtCodigo"
                              ,"edtCodOpe"
                              ,"btnConfirmar"]      // Foco qdo Cad/Alt/Exc
          */                        
          //,"formPassoPasso" : "Atlas_Espiao.php"    // Enderço da pagina PASSO A PASSO
          ,"indiceTable"    : "POSICAO"               // Indice inicial da table
          ,"tamBotao"       : "12"                  // Tamanho botoes defalt 12 [12/25/50/75/100]
          /*
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
                               ,["Alterar para registro do SISTEMA"       ,"fa-reply"         ,"objVcl.altRegSistema("+jsPub[0].usr_d31+");"]
                               ,["Número de registros em tela"            ,"fa-info"          ,"objVcl.numRegistros();"]                               
                               ,["Atualizar grade consulta"               ,"fa-filter"        ,"btnFiltrarClick('S');"]                               
                             ]  
           */                     
          //,"codTblUsu"      : "VEICULO[13]"                          
          //,"codDir"         : intCodDir
        }; 
        if( objMe === undefined ){  
          objMe=new clsTable2017("objMe");
        };  
        objMe.montarHtmlCE2017(jsMe); 
        ///////////////////////////////////////////
        //  Fim objeto clsTable2017 MOVIMENTOEXC //
        /////////////////////////////////////////// 
      });
      //
      var objMe;                      // Obrigatório para instanciar o JS TFormaCob
      var jsMe;                       // Obj principal da classe clsTable2017
      var clsJs;                      // Classe responsavel por montar um Json e eviar PHP
      var clsErro;                    // Classe para erros            
      var fd;                         // Formulario para envio de dados para o PHP
      var msg;                        // Variavel para guardadar mensagens de retorno/erro 
      var retPhp                      // Retorno do Php para a rotina chamadora
      var contMsg   = 0;              // contador para mensagens
      var cmp       = new clsCampo(); // Abrindo a classe campos
      var jsPub     = JSON.parse(localStorage.getItem("lsPublico"));
      function funcConfirmar(){
        clsErro     = new clsMensagem("Erro");
        clsErro.intMaiorZero("POSICAO"      ,document.getElementById("edtCodigo").value  );
        if( clsErro.ListaErr() != "" ){
          clsErro.Show();
        } else {
          clsJs   = jsString("lote");  
          clsJs.add("rotina"    , "delId"                                     );
          clsJs.add("login"     , jsPub[0].usr_login                          );
          clsJs.add("codusr"    , jsPub[0].usr_codigo                         );
          clsJs.add("posicao"   , document.getElementById("edtCodigo").value  );
          clsJs.add("infracao"  , document.getElementById("cbInfracao").value );
          fd = new FormData();
          fd.append("excluirid" , clsJs.fim());
          msg     = requestPedido("Trac_ExcluirId.php",fd); 
          retPhp  = JSON.parse(msg);
          if( retPhp[0].retorno == "OK" ){
            document.getElementById("edtCodigo").value="";
            document.getElementById("edtCodigo").focus();
          } else {
            gerarMensagemErro("Ui",retPhp[0].erro,"Erro");    
          }  
        };  
      };
      function funcCancelar(){
        window.parent.document.getElementById("iframeCorpo").src=""
      };
      
      function fncMovimentoExc(el){
        var ga;   //ga=getAttribute;
        var pnl;  //pnl=proximo filho de el;
        
        ga=el.getAttribute("data-abreFecha");
        ////////////////////////////////////////////////////////////
        // Se etiver fechado entaum tenho que buscar os registros //
        ////////////////////////////////////////////////////////////
        if( ga=="fechar" ){
          clsJs   = jsString("lote");  
          clsJs.add("rotina"      , "movimentoexc"                    );
          clsJs.add("login"       , jsPub[0].usr_login                );
          clsJs.add("data"        , jsDatas("edtData").retMMDDYYYY()  );                    
          fd = new FormData();
          fd.append("excluirid"      , clsJs.fim());

          msg     = requestPedido("Trac_ExcluirId.php",fd); 
          retPhp  = JSON.parse(msg);
          if( retPhp[0].retorno == "OK" ){
            //////////////////////////////////////////////////////////////////////////////////
            // O novo array não tem o campo idUnico mas a montarHtmlCE2017 ja foi executada //
            // Campo obrigatório se existir rotina de manutenção na table devido Json       //
            // Esta rotina não tem manutenção via classe clsTable2017                       //
            // jsCrv.registros=objCrv.addIdUnico(retPhp[0]["dados"]);                       //
            //////////////////////////////////////////////////////////////////////////////////
            jsMe.registros=retPhp[0]["dados"];
            objMe.montarBody2017();
          };  
        };
        //
        el.classList.toggle("acrdnAtivo");
        el.setAttribute("data-abreFecha",(ga=="fechar" ? "abrir" : "fechar" ) );
        ga  = el.getAttribute("data-abreFecha");
        pnl = el.nextElementSibling;
        ///////////////////////////////////////////
        // Se estiver fechado->abre, senao fecha //
        ///////////////////////////////////////////
        if( ga=="abrir" ){
          document.getElementById("divMaster").style.height="500px";
          pnl.style.maxHeight = pnl.scrollHeight + "px";  
        } else {
          document.getElementById("divMaster").style.height=document.getElementById("divMaster").getAttribute("data-height");          
          pnl.style.maxHeight = null;  
        }
      };
    </script>
  </head>
  <body>
    <div class="divTelaCheia">
      <div id="divRotina" class="conteudo" style="display:block;overflow-x:auto;">  
        <div id="divTopoInicio">
        </div>
        <form method="post" 
              name="frmUi" 
              id="frmUi" 
              class="frmTable" 
              action="classPhp/imprimirsql.php" 
              target="_newpage"
              style="top: -2em; width:77em;position: absolute; z-index:30;display:block;">
          <p class="frmCampoTit">
          <input class="informe" type="text" name="titulo" value="Excluir ID" disabled="" style="color: white; text-align: left;">
          </p>
          <div id="divMaster" style="height: 250px; overflow-y: auto;"
                              data-height="250px" >
            <input type="hidden" id="sql" name="sql"/>
            <div class="campotexto campo100">
              <div class="campotexto campo25">
                <input class="campo_input" id="edtCodigo" type="text" 
                                                          OnKeyPress="return mascaraInteiro(event);" 
                                                          maxlength="15" />
                <label class="campo_label campo_required" for="edtCodigo">POSIÇÃO</label>
              </div>
              <div class="campotexto campo75">
                <select class="campo_input_combo" id="cbInfracao" class="selectBis">
                  <option value="EV" selected="selected">EXCESSO VELOCIDADE</option>
                  <option value="EVC">EXCESSO VELOCIDADE CHUVA</option>
                  <!--
                  <option value="AB">ACELERACAO BRUSCA</option>
                  <option value="CB">CONDUCAO BANGUELA</option>
                  <option value="ERPM">EXCESSO RPM</option>
                  <option value="EV" selected="selected">EXCESSO VELOCIDADE</option>
                  <option value="EV">EXCESSO VELOCIDADE CHUVA</option>
                  <option value="FB">FREADA BRUSCA</option>
                  <option value="VN">VELOCIDADE NORMALIZADA</option>
                  -->
                </select>
                <label class="campo_label campo_required" for="cbInfracao">Infração</label>
              </div>
              <div class="campotexto campo100" style="border-bottom:1px solid silver;">
                <div class="campotexto campo50" style="margin-top:1em;">
                  <label class="campo_required" style="font-size:1.4em;"></label>
                  <label class="campo_labelSombra">Campo obrigatório</label>
                </div>              
                <div class="campo20" style="float:right;">            
                  <input id="btnConfirmar" onClick="funcConfirmar();" type="button" value="Confirmar" class="campo100 tableBotao botaoForaTable"/>            
                  <i class="faBtn fa-check icon-large"></i>
                </div>
                <div class="campo20" style="float:right;">            
                  <input id="btnCancelar" onClick="funcCancelar();" type="button" value="Cancelar" class="campo100 tableBotao botaoForaTable"/>
                  <i class="faBtn fa-close icon-large"></i>
                </div>
              </div>
              <!-- ACORDEON -->  
              <div class="campotexto campo100" style="border-bottom:1px solid #25729a;">
                <div class="campotexto campo50" style="margin-top:1em;">
                  <label class="campo_labelSombra">Ver registros excluidos a partir desta data:</label>
                </div> 
                <div class="campotexto campo15">
                  <input class="campo_input" id="edtData" type="text" 
                                                            OnKeyPress="return mascaraData(this,event);" 
                                                            maxlength="10" />
                  <label class="campo_label campo_required" for="edtCodigo">Informe</label>                                                            
                </div>                                          
              </div>
              <div class="campotexto campo100">
                <div id="btnMovimentoExc"
                     onClick="fncMovimentoExc(this);"
                     class="acordeon"
                     data-abreFecha="fechar" 
                     style="width:25%;margin-left:0.1em;">Excluidos</div>
                <div class="acrdnDiv" style="width:90%;margin-left:0.1em;height:28em;">
                  <div id="divMovimentoExc" class="conteudo" style="position:relative;float:left;height:41em;width:150em;">
                    <div id="divMovimentoExcReg">
                    </div>
                  </div>
                </div>
              </div>
              <!-- ACORDEON -->
            </div>
          </div>
        </form>
      </div>
    </div>       
  </body>
</html>