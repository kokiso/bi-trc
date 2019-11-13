<?php
  session_start();
  require("classPhp/conectaSqlServer.class.php");
  require("classPhp/validaJson.class.php");
  if( isset($_POST["login"]) ){
    $vldr = new validaJSon();          
    $retorno  = "";
    $retCls   = $vldr->validarJs($_POST["login"]);
    if($retCls["retorno"] != "OK"){
      $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';  
    } else { 
      ////////////////////////////////////////////////////////////////////////
      // Excluindo a SESSION devido multi-banco se logar em varias empresas //
      ////////////////////////////////////////////////////////////////////////
      unset($_SESSION['pathBD']);
      //
      $classe   = new conectaBd();
      $jsonObj  = $retCls["dados"];
      $lote     = $jsonObj->lote;      
      $classe->conecta($lote[0]->login);
      $classe->msg("NAO LOCALIZADO SENHA/USUARIO ".$lote[0]->usuario." PARA EMPRESA ".$lote[0]->login);
      /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
      // Pegando qtas filiais existem cadastradas pois na maioria será uma, esta desabilita/habilita o campo codfil no front //
      /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
      //$sql = "SELECT COUNT(FIL_CODIGO) AS CODIGO FROM TFILIAL WHERE FIL_ATIVO='S'"; 
      //$retCls     = $classe->selectAssoc($sql);
      //$qtosCodFil = $retCls["dados"][0]["CODIGO"]; 
      //
      ////////////////////////////////////////////////
      // Pegando todos usuarios para montar SESSION //
      ////////////////////////////////////////////////
      $sql = "SELECT A.USR_CODIGO
                    ,A.USR_CPF
                    ,A.USR_APELIDO
                    ,A.USR_ADMPUB
                    ,A.USR_EMAIL
                    ,A.USR_INTERNO
                    ,P.UP_D01
                    ,P.UP_D02
                    ,P.UP_D03
                    ,P.UP_D04
                    ,P.UP_D05
                    ,P.UP_D06
                    ,P.UP_D07
                    ,P.UP_D08
                    ,P.UP_D09
                    ,P.UP_D10
                    ,P.UP_D11
                    ,P.UP_D12
                    ,P.UP_D13
                    ,P.UP_D14
                    ,P.UP_D15
                    ,P.UP_D16
                    ,P.UP_D17
                    ,P.UP_D18
                    ,P.UP_D19
                    ,P.UP_D20
                    ,A.USR_CODCRG
                    ,P.CONSULTAR_RELATORIO
             FROM USUARIO A
             LEFT OUTER JOIN USUARIOPERFIL P ON A.USR_CODUP=P.UP_CODIGO 
            WHERE A.USR_CPF='".$lote[0]->usuario."'
              AND A.USR_SENHA='".$lote[0]->senha."'
              AND A.USR_ATIVO='S'"; 
      $classe->msgSelect(true);              
      $retCls=$classe->selectAssoc($sql);
      //file_put_contents("aaa.xml",print_r($retCls,true));      
      if( $retCls["retorno"] != "OK" ){
        $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';  
      } else { 
        $retPhp=$retCls["dados"];
        //////////////////////////////////////////////////////////////
        // se chegou aqui é por que a senha do usuário está correta //
        //////////////////////////////////////////////////////////////
        $_SESSION["usr_codigo"]           = $retPhp[0]["USR_CODIGO"];
        $_SESSION["usr_apelido"]          = $retPhp[0]["USR_APELIDO"];
        $_SESSION["usr_cpf"]              = $retPhp[0]["USR_CPF"];
        $_SESSION["usr_admpub"]           = $retPhp[0]["USR_ADMPUB"];        
        $_SESSION["usr_email"]            = $retPhp[0]["USR_EMAIL"];
        $_SESSION["usr_cargo"]            = $retPhp[0]["USR_CODCRG"];
        $_SESSION["consultar_relatorio"]  = $retPhp[0]["CONSULTAR_RELATORIO"];
        $_SESSION["usr_grupos"]           = "(0)";                    // Qual(is) grupos(s) o usuario que esta se logando pode ver na tela de grupos e f10
        ////////////////////////////////////////////////////////////////////////
        // PARAMETRO PARA SABER QUAL(IS) GRUPO(S) O USUARIO LOGADO PODE VER   //
        ////////////////////////////////////////////////////////////////////////
        $sql = "SELECT DISTINCT uni.UNI_CODGRP AS CODGRP
                  FROM USUARIOUNIDADE uu
                  LEFT OUTER JOIN UNIDADE uni ON uu.UU_CODUNI=uni.UNI_CODIGO
                 WHERE uu.UU_CODUSR=".$_SESSION["usr_codigo"]." AND uu.UU_ATIVO='S'";
        $retCls=$classe->selectAssoc($sql);
        if( $retCls["qtos"]>0 ){
          $tbl    = $retCls["dados"];
          $in     = "";
          $sep    = "";
          foreach( $tbl as $lin ){
            $in.=$sep.$lin["CODGRP"];
            $sep=",";
          };  
          $_SESSION["usr_grupos"]="(".$in.")";
          unset($tbl,$in,$sep);
        };    
        
        ////////////////////////////////////////////////////////
        // VENDO QUAL NAVEGADOR PARA SER USADO NO JAVASCRIPT  //
        ////////////////////////////////////////////////////////
        $u_agent = $_SERVER['HTTP_USER_AGENT'];
        $navegador="OUTRO";
        if( (preg_match('/MSIE/i',$u_agent)) && (!preg_match('/Opera/i',$u_agent))){
          $navegador = "IE";
        } else if (preg_match('~MSIE|Internet Explorer~i', $_SERVER['HTTP_USER_AGENT']) || (strpos($_SERVER['HTTP_USER_AGENT'], 'Trident/7.0; rv:11.0') !== false)) {
          $navegador = "IE";            
        }elseif(preg_match('/Firefox/i',$u_agent)){
          $navegador = "FIREFOX";
        } elseif(preg_match('/Chrome/i',$u_agent)){
          $navegador = "CHROME";
        } elseif(preg_match('/AppleWebKit/i',$u_agent)){
          $navegador = "OPERA";
        } elseif(preg_match('/Safari/i',$u_agent)){
          $navegador = "SAFARI";
        } elseif(preg_match('/Netscape/i',$u_agent)){
          $navegador = "NETSCAPE";
        }
        ////////////////////////////////////////////////////
        // CRIANDO UM OBJETO PARA SER USADO NO JAVASCRIPT //
        // Os que se repetem é devido banco de dados      //
        ////////////////////////////////////////////////////
        $str =
         '[{
             "usr_apelido"         :"'.$retPhp[0]["USR_APELIDO"].'"        
            ,"usr_codigo"          :"'.str_pad($retPhp[0]["USR_CODIGO"], 4, "0", STR_PAD_LEFT).'"             
            ,"usr_admpub"          :"'.$retPhp[0]["USR_ADMPUB"].'"  
            ,"usr_email"           :"'.$retPhp[0]["USR_EMAIL"].'" 
            ,"usr_interno"         :"'.$retPhp[0]["USR_INTERNO"].'" 
            ,"usr_login"           :"'.$_SESSION["login"].'"   
            ,"usr_d01"             :"'.$retPhp[0]["UP_D01"].'"
            ,"usr_d02"             :"'.$retPhp[0]["UP_D02"].'"
            ,"usr_d03"             :"'.$retPhp[0]["UP_D03"].'"
            ,"usr_d04"             :"'.$retPhp[0]["UP_D04"].'"
            ,"usr_d05"             :"'.$retPhp[0]["UP_D05"].'"
            ,"usr_d06"             :"'.$retPhp[0]["UP_D06"].'"
            ,"usr_d07"             :"'.$retPhp[0]["UP_D07"].'"
            ,"usr_d08"             :"'.$retPhp[0]["UP_D08"].'"
            ,"usr_d09"             :"'.$retPhp[0]["UP_D09"].'"
            ,"usr_d10"             :"'.$retPhp[0]["UP_D10"].'"
            ,"usr_d11"             :"'.$retPhp[0]["UP_D11"].'"
            ,"usr_d12"             :"'.$retPhp[0]["UP_D12"].'"
            ,"usr_d13"             :"'.$retPhp[0]["UP_D13"].'"
            ,"usr_d14"             :"'.$retPhp[0]["UP_D14"].'"
            ,"usr_d15"             :"'.$retPhp[0]["UP_D15"].'"
            ,"usr_d16"             :"'.$retPhp[0]["UP_D16"].'"
            ,"usr_d17"             :"'.$retPhp[0]["UP_D17"].'"
            ,"usr_d18"             :"'.$retPhp[0]["UP_D18"].'"
            ,"usr_d19"             :"'.$retPhp[0]["UP_D19"].'"
            ,"usr_d20"             :"'.$retPhp[0]["UP_D20"].'"
            ,"usr_grupos"          :"'.$_SESSION["usr_grupos"].'"   
            ,"navegador"           :"'.$navegador.'"    
            ,"DESUSU"              :"'.$retPhp[0]["USR_APELIDO"].'"
            ,"usr_cargo"           :"'.$_SESSION["usr_cargo"].'" 
            ,"consultar_relatorio":"'.$_SESSION["consultar_relatorio"].'"   
          }]';              
        $retorno='[{"retorno":"OK","dados":'.str_replace(array("\r","\n"),'',$str).',"erro":""}]';
      };
    };  
    unset($classe,$jsonObj,$lote,$navegador,$retCls,$retPhp,$sql,$str,$u_agent);      
    echo $retorno;
    exit;
  }
?>
<!DOCTYPE html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="SKYPE_TOOLBAR" content="SKYPE_TOOLBAR_PARSER_COMPATIBLE" />
    <!--<link rel="icon" type="image/png" href="imagens/logo_aba.png" />-->
    <title>Login</title>
    <link rel="stylesheet" href="css/css2017.css">
    <link rel="stylesheet" href="css/cssTable2017.css">
    <script src="js/js2017.js"></script>
    <script src="js/jsTable2017.js"></script>
    <script language="javascript" type="text/javascript"></script>
    <style>
      .layout-boxed {
          background: url(imagens/boxed-bg.jpg) repeat fixed;
      }    
    </style>
    <script>
      "use strict";
      document.addEventListener("DOMContentLoaded", function(){ 
        document.getElementById('edtUsuario').foco();      
      });
      var contMsg = 0;
      var clsArq  = ""; // Classe para gerar arquivo JSon para envio Php
      var mo  = {
        corpo     : ""
        ,fd       : ""
        ,from     : ""
        ,fromname : ""        
        ,jsPhp    : ""        
        ,msg      : ""
        ,subject  : ""    
      };
      function loginClick(){
        var erro = new clsMensagem('Erro');
        erro.notNull( "EMPRESA"  , document.getElementById("edtEmpresa").value.toUpperCase() );
        erro.notNull( "USUARIO"  , document.getElementById("edtUsuario").value.toUpperCase() );
        erro.notNull( "SENHA"    , document.getElementById("edtSenha").value.toUpperCase()   );
        
        if( erro.ListaErr() != '' ){
          erro.Show();
        } else {
          try{
            document.getElementById("edtUsuario").value   = jsStr("edtUsuario").upper().alltrim().ret();
            document.getElementById("edtEmpresa").value   = jsStr("edtEmpresa").upper().alltrim().ret();            
            document.getElementById("edtSenha").value     = jsStr("edtSenha").upper().alltrim().ret();            
            /////////////////////////////////
            // Passando um JSON para o PHP //
            /////////////////////////////////
            clsArq=jsString("lote");
            clsArq.add("login"    , document.getElementById("edtEmpresa").value );
            clsArq.add("usuario"  , document.getElementById("edtUsuario").value );
            clsArq.add("senha"    , document.getElementById("edtSenha").value   );
            mo.jsPhp=clsArq.fim();
            //
            mo.fd = new FormData();
            mo.fd.append("login",mo.jsPhp );
            mo.msg=requestPedido("Trac_Login.php",mo.fd); 
            var retPhp=JSON.parse(mo.msg);

            if( retPhp[0].retorno=="OK" ){
              localStorage.setItem("lsPublico",JSON.stringify(retPhp[0].dados));
              localStorage.setItem("lsPathPhp","phpSqlServer.php");
              if( retPhp[0].dados[0].usr_interno=="E" ){
                window.location="Trac_BiDashBoard.php";   
              } else {        
                window.location="Trac_Principal.php"; 
              }  
            }else{
              gerarMensagemErro("COB",retPhp[0].erro,"Erro");                
            };
          }catch(e){
            gerarMensagemErro("catch",e.message,"Erro");
          };
        };
      };
    </script>
  </head>
  <body class="layout-boxed">
  <div id="fundo">
        <!--<img src="http://flickholdr.com/1600/1200/sunset" alt="" />-->
    </div>
    <!--
    <div class="divTelaCheia" id="telaCheia"><img src="imagens/fundoLogin.png" alt="" />
    -->
    <div class="divTelaCheia" id="telaCheia">
      <form method="post" 
            name="frmPai" 
            id="frmPai" 
            class="formulario center" 
            style="top: 15em; width:40em; position: absolute; z-index:30;display:block;">
        <p align="center">
        <img src="imagens/logoMenor.png" class="user-image" alt="Trac | Connect Plus">
        </p>
        <div style="height: 200px; overflow-y: auto;">
      
          <!--<div class="campotexto campo100">-->
					<div class="inactive">
            <input class="campo_input input" id="edtEmpresa" type="text" value="INTEGRAR" maxlength="15" disabled />
            <label class="campo_label campo_required" for="edtEmpresa">Empresa:</label>
			
          </div>
          <div class="campotexto campo100">
            <input class="campo_input input" id="edtUsuario" value="" type="text" maxlength="11"/>
            <label class="campo_label campo_required" for="edtUsuario">CPF:</label>
			
          </div>
          <div class="campotexto campo100">
            <input type="password" class="campo_input input" id="edtSenha" value="" type="text" maxlength="10" />
            <label class="campo_label campo_required" for="edtSenha">Senha:</label>
			
          </div>
          <div class="campotexto campo100">
            <!--<div class="campo50" style="float:left;"><img src="imagens\security2.jpg">-->
            <div class="campo30" style="float:right;">
              <input id="btnLogin" onClick="loginClick();" type="button" value="Entrar" class="campo100 tableBotao botaoForaTable"/>
           	</div>
            <!--
            <div class="campo50" style="float:right;">            
              <input id="btnLogin" onClick="loginClick();" type="button" value="Recuperar Senha" class="campo100 tableBotao botaoForaTable"/>            
            </div>
            -->
          </div>
        </div>
      </form>    
      <!--<div class="divRodapeInicio">
        <div id="labelRodape" class="label_Rodape">www.atlas.com.br</div>
      </div>  -->    
    </div>    
  </body>
</html>