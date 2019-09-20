<?php
  session_start();
  require("php/class.phpmailer.php");
  require("php/class.smtp.php");
  require("classPhp/conectaSqlServer.class.php");

  if( isset($_POST['alterar']) ){
    $retorno="";
    $classe   = new conectaBd();
    $jsonObj  = json_decode($_POST['alterar']);
    ///////////////////////////////
    // Validando o json recebido //
    ///////////////////////////////
    if(json_last_error() != JSON_ERROR_NONE)
      $retorno='[{"retorno":"ERR","dados":"","erro":"FORMATO JSON INVALIDO!"}]';
    //
    if( $retorno == "" ){
      $lote     = $jsonObj->lote;
      $classe->conecta($lote[0]->login);
      $classe->msgSelect=false;      
      
      foreach ( $lote as $lt ){   
        $sql="SELECT USR_SENHA 
                FROM USUARIO 
               WHERE USR_CODIGO=".$lt->codusu." AND USR_SENHA='".$lt->oldsenha."'";
        $retCls=$classe->selectAssoc($sql);
        if( $retCls['retorno']=="OK" ){
          $arrUpdt=[];
          $sql = "UPDATE USUARIO
                    SET USR_SENHA='"  .$lt->newsenha."'
                  WHERE USR_CODIGO="  .$lt->codusu;
          array_push($arrUpdt,$sql);
          $retCls=$classe->cmd($arrUpdt);
          if( $retCls['retorno']=="OK" ){
            $retorno='[{"retorno":"OK","dados":"","erro":"SENHA ALTERADA COM SUCESSO!"}]';                        
          }else{
            $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';
            break;  
          };
        } else {
          $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';
        }; 
      };    
    };      
    echo $retorno;
    exit;
  };  
  /*  
  if( isset($_POST['alterar']) ){
    $retorno="";
    $classe   = new conectaBd();
    $jsonObj  = json_decode($_POST['alterar']);
    ///////////////////////////////
    // Validando o json recebido //
    ///////////////////////////////
    if(json_last_error() != JSON_ERROR_NONE)
      $retorno='[{"retorno":"ERR","dados":"","erro":"FORMATO JSON INVALIDO!"}]';
    //
    if( $retorno == "" ){
      $lote     = $jsonObj->lote;
      $classe->conecta($lote[0]->login);
      $classe->msgSelect=false;      
      $arrUpdt=[];
      $sql = "INSERT INTO TAB01(T01_APELIDO,T01_FISJUR) VALUES('ORLANDO','F')";
      array_push($arrUpdt,$sql);
      $retCls=$classe->cmd($arrUpdt);
      if( $retCls['retorno']=="OK" ){
        $retorno='[{"retorno":"OK","dados":"","erro":"SENHA ALTERADA COM SUCESSO!"}]';                        
      }else{
        $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';
        //break;  
      };
    };      
    echo $retorno;
    exit;
  };  
  */
?>
<!DOCTYPE html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="SKYPE_TOOLBAR" content="SKYPE_TOOLBAR_PARSER_COMPATIBLE" />
    <title>Alterar senha</title>
    <link rel="stylesheet" href="css/css2017.css">
    <link rel="stylesheet" href="css/cssTable2017.css">
    <script src="js/js2017.js"></script>
    <script src="js/jsTable2017.js"></script>
    <script language="javascript" type="text/javascript"></script>
    <script>
      "use strict";
      ////////////////////////////////////////////////
      // Executar o codigo após a pagina carregada  //
      ////////////////////////////////////////////////
      document.addEventListener("DOMContentLoaded", function(){ 
        horAltSenClick();
      });
      //
      var clsJs;                      // Classe responsavel por montar um Json e eviar PHP
      var clsErro;                    // Classe para erros            
      var fd;                         // Formulario para envio de dados para o PHP
      var msg;                        // Variavel para guardadar mensagens de retorno/erro 
      var retPhp;                     // Retorno do Php para a rotina chamadora
      var clsArq;                     // Classe para gerar arquivo JSon para envio Php
      var contMsg   = 0;              // contador para mensagens
      var cmp       = new clsCampo(); // Abrindo a classe campos
      var jsPub     = JSON.parse(localStorage.getItem("lsPublico"));
      var intCodDir = parseInt(jsPub[0].usr_d05);
      function horAltSenClick(){
        try{
          var jsCx={
             "botaoEsquerdo":"s"
            ,"botaoDireito":"s"
            ,"titulo":"Alterar senha"
            ,"width":"30%"
            ,"top":"10em"
            ,"left":"30em"
            ,"hint":"s"
            ,"onClick":"altSen()"
            ,"campos":[
              {"name":"oldSenha" ,"type":"password"  ,"placeholder":"Informe senha atual" ,"maxlength":"10"   ,"imagem":"fa-key"}
             ,{"name":"newSenha" ,"type":"password"  ,"placeholder":"Informe nova senha"  ,"maxlength":"10"   ,"imagem":"fa-key"}                    
             ,{"name":"conSenha" ,"type":"password"  ,"placeholder":"confirme nova senha" ,"maxlength":"10"   ,"imagem":"fa-key"}
            ]          
          };
          cxDialogo(jsCx);
          document.getElementById("oldSenha").foco();
        }catch(e){
          gerarMensagemErro('catch',e.message,"Erro");
        };
      }; 
      //--akiiiii
      function altSen(){
        try{
          clsErro = new clsMensagem('Erro');
          var oldSenha = jsStr("oldSenha").upper().alltrim().ret();
          var newSenha = jsStr("newSenha").upper().alltrim().ret();
          var conSenha = jsStr("conSenha").upper().alltrim().ret(); 
          var codUsu   = jsPub[0].usr_codigo
          //  
          clsErro.tamMin("Nova senha",conSenha,6 );
          clsErro.tamMax("Nova senha",conSenha,10 ); 
          oldSenha=oldSenha.toUpperCase();            
          if( newSenha != conSenha )
            clsErro.add("CAMPO<b>SENHA</b>NOVA SENHA INVALIDA!");            
          if( oldSenha == newSenha.toUpperCase() )
            clsErro.add("CAMPO<b>SENHA</b>NOVA SENHA DEVE SER DIFERENTE DA ATUAL!");
          /////////////////////////////////////////////////////////////////////////////////////////////////////////////
          // Senha deve ter no minimo uma letra ou um número(StoredProcedure(VALIDAR_SENHA) olhando para essa regra) // 
          /////////////////////////////////////////////////////////////////////////////////////////////////////////////
          var numInt=0;
          var numStr=0;
          for( var cntd=0;cntd<newSenha.length;cntd++){
            if( ['0','1','2','3','4','5','6','7','8','9'].indexOf(newSenha[cntd]) == -1 )
              numStr++;
            else
              numInt++;              
          };
          if( (numInt==0) || (numStr==0) ){
            clsErro.add("CAMPO<b>SENHA</b>OBRIGATORIO UM NUMERO OU UMA STRING NA NOVA SENHA!");
          };  
          //--
          if( clsErro.ListaErr() != '' )      //MUDAR DE == PARA != (TESTE DE TRIGGER)
            clsErro.Show();
          else {
            clsArq=jsString("lote");            
            clsArq.add("oldsenha" , oldSenha );
            clsArq.add("codusu"   , codUsu   );
            clsArq.add("newsenha" , newSenha );
            clsArq.add("consenha" , conSenha );
            clsArq.add("login"    , jsPub[0].usr_login  );            
            retPhp=clsArq.fim();
            var fd = new FormData();
            var retorno;
            fd.append("alterar",retPhp );
            retorno=requestPedido("Trac_AlteraSenha.php",fd); 
            retPhp=JSON.parse(retorno);
            if( retPhp[0].retorno=="OK" )
              dlgCancelar.click(); 
            gerarMensagemErro("USU",retPhp[0].erro,"Erro");  
          };  
        }catch(e){
          gerarMensagemErro("catch",e.message,"Erro");
        };
      };
    </script>
  </head>
  <body>
    <div class="divTelaCheia">
    </div>       
  </body>
</html>