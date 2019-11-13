<?php
  session_start();
  if( isset($_POST["ultimoid"]) ){
    try{     
      require("classPhp/conectaSqlServer.class.php");
      require("classPhp/validaJson.class.php"); 
      require("classPhp/removeAcento.class.php"); 

      $vldr     = new validaJSon();          
      $retorno  = "";
      $retCls   = $vldr->validarJs($_POST["ultimoid"]);
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
//file_put_contents("aaa.xml",print_r($classe->conecta($lote[0]->login),true));        
        ////////////////////////////////////////////////
        //         Dados para JavaScript CARGO        //
        ////////////////////////////////////////////////
        if( $rotina=="selectUi" ){
          $sql="SELECT UI_CODIGO"          
               ."     ,UI_INTEGRAR"
               ."     ,UI_QTOS"
               ."     ,UI_CLIENTE"
               ."     ,UI_LOGIN"
               ."     ,UI_SENHA"
               ." FROM ULTIMOID";
          $classe->msgSelect(false);
          $retCls=$classe->selectAssoc($sql);
          if( $retCls['retorno'] != "OK" ){
            $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';  
          } else { 
            $retorno='[{"retorno":"OK","dados":'.json_encode($retCls['dados']).',"erro":""}]'; 
          };  
        };
        ////////////////////////////////////////////////
        //                 Alterar                    //
        ////////////////////////////////////////////////
        if( $rotina=="alterarUi" ){
          $atuBd=true;
          $sql="UPDATE ULTIMOID"
               ."  SET UI_CODIGO=".   $lote[0]->codigo
               ."     ,UI_INTEGRAR='".$lote[0]->integrar."'"
               ."     ,UI_QTOS=".     $lote[0]->qtos
               ."     ,UI_CLIENTE=".  $lote[0]->cliente
               ."     ,UI_LOGIN='".   $lote[0]->loginWs."'"
               ."     ,UI_SENHA='".   $lote[0]->senhaWs."'";
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
    <title>Cargo</title>
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
        btnFiltrarClick();  
      });
      //
      //var objUi;                     // Obrigatório para instanciar o JS TFormaCob
      //var jsUi;                      // Obj principal da classe clsTable2017
      var clsJs;                      // Classe responsavel por montar um Json e eviar PHP
      var clsErro;                    // Classe para erros            
      var fd;                         // Formulario para envio de dados para o PHP
      var msg;                        // Variavel para guardadar mensagens de retorno/erro 
      var retPhp                      // Retorno do Php para a rotina chamadora
      var contMsg   = 0;              // contador para mensagens
      var cmp       = new clsCampo(); // Abrindo a classe campos
      var jsPub     = JSON.parse(localStorage.getItem("lsPublico"));
      ////////////////////////////
      // Filtrando os registros //
      ////////////////////////////
      function btnFiltrarClick() { 
        clsJs   = jsString("lote");  
        clsJs.add("rotina"      , "selectUi"          );
        clsJs.add("login"       , jsPub[0].usr_login  );
        fd = new FormData();
        fd.append("ultimoid" , clsJs.fim());
        msg     = requestPedido("Trac_UltimoId.php",fd); 
        retPhp  = JSON.parse(msg);
        if( retPhp[0].retorno == "OK" ){
          document.getElementById("edtCodigo").value  = retPhp[0]["dados"][0]["UI_CODIGO"];
          document.getElementById("cbIntegrar").value = retPhp[0]["dados"][0]["UI_INTEGRAR"];
          document.getElementById("edtQtos").value    = retPhp[0]["dados"][0]["UI_QTOS"];
          document.getElementById("edtCliente").value = retPhp[0]["dados"][0]["UI_CLIENTE"];
          document.getElementById("edtLogin").value   = retPhp[0]["dados"][0]["UI_LOGIN"];
          document.getElementById("edtSenha").value   = retPhp[0]["dados"][0]["UI_SENHA"];
        };  
      };
      function funcConfirmar(){
        clsErro     = new clsMensagem("Erro");
        clsErro.intMaiorZero("POSICAO"      ,document.getElementById("edtCodigo").value  );
        clsErro.intMaiorZero("VEZES_1000"   ,document.getElementById("edtQtos").value    );
        clsErro.intMaiorZero("CLIENTE"      ,document.getElementById("edtCliente").value );
        clsErro.notNull(     "SENHA"        ,document.getElementById("edtSenha").value   );        
        clsErro.notNull(     "LOGIN"        ,document.getElementById("edtLogin").value   );                
        if( clsErro.ListaErr() != "" ){
          clsErro.Show();
        } else {
          clsJs   = jsString("lote");  
          clsJs.add("rotina"    , "alterarUi"                                 );
          clsJs.add("login"     , jsPub[0].usr_login                          );
          clsJs.add("codigo"    , document.getElementById("edtCodigo").value  );
          clsJs.add("qtos"      , document.getElementById("edtQtos").value    );
          clsJs.add("cliente"   , document.getElementById("edtCliente").value );
          clsJs.add("senhaWs"   , document.getElementById("edtSenha").value   );        
          clsJs.add("loginWs"   , document.getElementById("edtLogin").value   );
          clsJs.add("integrar"  , document.getElementById("cbIntegrar").value );          
          fd = new FormData();
          fd.append("ultimoid" , clsJs.fim());
          msg     = requestPedido("Trac_UltimoId.php",fd); 
          retPhp  = JSON.parse(msg);
          if( retPhp[0].retorno == "OK" ){
            //gerarMensagemErro("Ui",retPhp[0].erro,"Aviso");  
            window.parent.document.getElementById("iframeCorpo").src=""
          } else {
            gerarMensagemErro("Ui",retPhp[0].erro,"Erro");    
          }  
        };  
      };
      function funcCancelar(){
        window.parent.document.getElementById("iframeCorpo").src=""
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
              style="top: 6em; width:77em;position: absolute; z-index:30;display:block;">
          <p class="frmCampoTit">
          <input class="informe" type="text" name="titulo" value="Parametros para integração" disabled="" style="color: white; text-align: left;">
          </p>
          <div style="height: 200px; overflow-y: auto;">
            <input type="hidden" id="sql" name="sql"/>
            <div class="campotexto campo100">
              <div class="campotexto campo25">
                <input class="campo_input_titulo" id="edtCodigo" disabled type="text" OnKeyPress="return mascaraInteiro(event);" maxlength="15" />
                <label class="campo_label campo_required" for="edtCodigo">POSIÇÃO</label>
              </div>
              <div class="campotexto campo25">
                <select class="campo_input_combo" id="cbIntegrar">
                  <option value="S">SIM</option>
                  <option value="N">NAO</option>
                </select>
                <label class="campo_label campo_required" for="cbIntegrar">ATIVO</label>
              </div>
              
              <div class="campotexto campo25">
                <input class="campo_input" id="edtQtos" type="text" OnKeyPress="return mascaraInteiro(event);" maxlength="2" />
                <label class="campo_label campo_required" for="edtQtos">X 1000</label>
              </div>
              <div class="campotexto campo25">
                <input class="campo_input_titulo" disabled id="edtCliente" type="text" OnKeyPress="return mascaraInteiro(event);" maxlength="10" />
                <label class="campo_label campo_required" for="edtCliente">COD CLIENTE</label>
              </div>
              <div class="campotexto campo25">
                <input class="campo_input_titulo" disabled id="edtLogin" type="text" />
                <label class="campo_label campo_required" for="edtLogin">LOGIN</label>
              </div>
              <div class="campotexto campo25">
                <input class="campo_input_titulo" disabled id="edtSenha" type="text" />
                <label class="campo_label campo_required" for="edtSenha">LOGIN</label>
              </div>
              <div class="campotexto campo100">
                <div class="campotexto campo50" style="margin-top:1em;">
                  <label class="campo_required" style="font-size:1.4em;"></label>
                  <label class="campo_labelSombra">Campo obrigatório</label>
                </div>              
                <div class="campo20" style="float:right;">            
                  <input id="btnConfirmar" onClick="funcConfirmar();" type="button" value="Confirmar" class="campo100 tableBotao botaoForaTable"/>            
                  <i class="faBtn fa-check icon-large"></i>
                </div>
                <div class="campo20" style="float:right;">            
                  <!--<input id="btnCancelar" onClick="window.close();" type="button" value="Cancelar" class="campo100 tableBotao botaoForaTable"/>-->
                  <input id="btnCancelar" onClick="funcCancelar();" type="button" value="Cancelar" class="campo100 tableBotao botaoForaTable"/>
                  <i class="faBtn fa-close icon-large"></i>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>       
  </body>
</html>