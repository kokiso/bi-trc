<?php
    session_start();
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<title>Connect Plus | Trac</title>
		<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
		
    <script src="js/jsTable2017.js"></script>
    <script src="js/js2017.js"></script>

    <link rel="stylesheet" href="css/css2017.css">
    <link rel="stylesheet" href="css/cssTable2017.css">
		<link rel="stylesheet" href="css/bootstrap.css">
		<link rel="stylesheet" href="css/font-awesome.css">
		<link rel="stylesheet" href="css/AdminLTE.css">
		<link rel="stylesheet" href="css/skin-black-light.css">
		<link rel="icon" type="image/png" href="imagens/logoMenor.png">
		<link rel="stylesheet" href="css/iframe.css">
    <!--
    <script src="tabelaTrac/f10/tabelaGrupoF10.js"></script>
    <script src="tabelaTrac/f10/tabelaPoloF10.js"></script>
    <script src="tabelaTrac/f10/tabelaUnidadeF10.js"></script>
    -->
    <script>
      "use strict";
      function ifAbrir(arquivo,direito,titulo){
        eval(" locDireito = "+direito);
        if( locDireito==0 ){
          gerarMensagemErro("login","Usuário não tem direito de consulta nesta rotina!","Aviso");
        } else {
          window.open(arquivo, "iframeCorpo"); 
          if( titulo != undefined ){
            document.getElementById("tituloMenu").innerHTML=titulo;
          }
          return false;
        }
      };

      function temPermissao(permissao) {
        console.log(eval(permissao));
      }
      
      var jsPub      = JSON.parse(localStorage.getItem("lsPublico"));
      var contMsg    = 0;            // contador para mensagens
      var locDireito = 0;
      
      var clsErro;                    // Classe para erros            
      var fd;                         // Formulario para envio de dados para o PHP
      var msg;                        // Variavel para guardadar mensagens de retorno/erro 
      var retPhp;                     // Retorno do Php para a rotina chamadora
      var contMsg   = 0;              // contador para mensagens
    </script>  
	</head>
	<body class="hold-transition skin-black-light sidebar-mini">
		<div class="wrapper">
			<header class="main-header">
				<a href="index.php" class="logo">
					<img src="imagens/logoMenor.png" class="user-image" style="background-size: 100% 100%;" alt="Logomarca Connect Plus">
				</a>
				<nav class="navbar navbar-static-top">
					<a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</a>
          <a href="#" class="sidebar-orlando"><div id="tituloMenu">Integração Total trac->SistemSat</div></a>
				</nav>
			</header>
			<aside class="main-sidebar">
				<section class="sidebar">
					<ul class="sidebar-menu" data-widget="tree">
						<li class="treeview">
							<a href="#">
								<i class="fa fa-files-o"></i>
								<span>INTEGRAÇÂO</span>
								<span class="pull-right-container">
									<span class="label label-primary pull-right">2</span>
								</span>
							</a>
              
							<ul class="treeview-menu">
              
                <li><a href="#" onclick="ifAbrir('Trac_AlteraSenha.php','-1');"><i class="fa fa-circle-o"></i> Alterar minha senha</a></li>
                <li class="treeview">
                  <a href="#">
                    <i class="fa fa-users"></i> <span>Usuários do sistema</span>
                    <span class="pull-right-container">
                      <i class="fa fa-angle-left pull-right"></i>
                    </span>
                  </a>
                  <ul class="treeview-menu">
                    <li><a href="#" onclick="ifAbrir('Trac_Usuario.php'          ,'jsPub[0].usr_d01','Usuarios');"><i class="fa fa-circle-o"></i> Usuários ativos</a></li>
                    <li><a href="#" onclick="ifAbrir('Trac_UsuarioUnidade.php'   ,'jsPub[0].usr_d01','Usuario/Unidade');"><i class="fa fa-circle-o"></i> Usuário->Unidade</a></li>
                    <li><a href="#" onclick="ifAbrir('Trac_UsuarioPerfil.php'    ,'jsPub[0].usr_d01','Perfil');"><i class="fa fa-circle-o"></i> Perfil</a></li>
                    <li><a href="#" onclick="ifAbrir('Trac_Cargo.php'            ,'jsPub[0].usr_d01','Cargo');"><i class="fa fa-circle-o"></i> Cargo</a></li>
                  </ul>
                </li>               
                <li class="treeview">
                  <a href="#">
                    <i class="fa fa-users"></i> <span>Cadastros obrigatórios</span>
                    <span class="pull-right-container">
                      <i class="fa fa-angle-left pull-right"></i>
                    </span>
                  </a>
                  <ul class="treeview-menu">
                    <li><a href="#" onclick="ifAbrir('Trac_Grupo.php'       ,'jsPub[0].usr_d06','Grupos');"><i class="fa fa-circle-o"></i> Grupos</a></li>
                    <li><a href="#" onclick="ifAbrir('Trac_Unidade.php'     ,'jsPub[0].usr_d07','Unidades');"><i class="fa fa-circle-o"></i> Unidades</a></li>
                    <li><a href="#" onclick="ifAbrir('Trac_Polo.php'        ,'jsPub[0].usr_d08','Polos');"><i class="fa fa-circle-o"></i> Polos</a></li>
                    <li><a href="#" onclick="ifAbrir('Trac_Veiculo.php'     ,'jsPub[0].usr_d09','Veiculos');"><i class="fa fa-circle-o"></i> Veiculos</a></li>
                    <li><a href="#" onclick="ifAbrir('Trac_Motorista.php'   ,'jsPub[0].usr_d04','Motoristas');"><i class="fa fa-circle-o"></i> Motoristas</a></li>
										<li><a href="#" onclick="ifAbrir('Trac_Posicao.php'     ,'jsPub[0].usr_d12','Posicao gslog');"><i class="fa fa-circle-o"></i> Posicao gslog</a></li>
                    <li><a href="#" onclick="ifAbrir('Trac_Evento.php'      ,'jsPub[0].usr_d10','Evento');"><i class="fa fa-circle-o"></i> Evento</a></li>
                    <li><a href="#" onclick="ifAbrir('Trac_EventoGrupo.php' ,'jsPub[0].usr_d10','Grupo Evento');"><i class="fa fa-circle-o"></i> Grupo Evento</a></li>
                  </ul>
                </li> 
                
                <li class="treeview">
                  <a href="#">
                    <i class="fa fa-users"></i> <span>Integrar SistemSat</span>
                    <span class="pull-right-container">
                      <i class="fa fa-angle-left pull-right"></i>
                    </span>
                  </a>
                  <ul class="treeview-menu">
                    <li><a href="#" onclick="ifAbrir('Trac_UltimoId.php'        ,'jsPub[0].usr_d02','Parmetros integracao');"><i class="fa fa-circle-o"></i> Parametros integração</a></li>
                    <!--<li><a href="#" onclick="ifAbrir('Trac_LerArquivo.php'      ,'jsPub[0].usr_d03','Importação registros');"><i class="fa fa-circle-o"></i> Iniciar integração</a></li>-->
                    <li><a href="#" onclick="ifAbrir('Trac_ExcluirId.php'       ,'jsPub[0].usr_d11','Excluir ID');"><i class="fa fa-circle-o"></i> Excluir ID</a></li>										
                    <li><a href="#" onclick="ifAbrir('Trac_MovimentoResumo.php' ,'jsPub[0].usr_d02','Resumo integração');"><i class="fa fa-circle-o"></i> Resumo integração</a></li>
										<!--<li><a href="#" onclick="ifAbrir('paineltrac.php' ,'jsPub[0].usr_d02','Painel');"><i class="fa fa-circle-o"></i> Painel</a></li>-->
                  </ul>
                </li> 
                
                <li class="treeview">
                  <a href="#">
                    <i class="fa fa-users"></i> <span>BI</span>
                    <span class="pull-right-container">
                      <i class="fa fa-angle-left pull-right"></i>
                    </span>
                  </a>
                  <ul class="treeview-menu">
                    <li><a href="#" onclick="window.open('Trac_BiDashBoard.php');"><i class="subMenuImagem fa fa-folder-o"></i>Dashboard</a></li>
                    <li><a href="#" onClick="window.open('Trac_grdInfracaoTempo.php','iframeCorpo');"><i class="fa fa-circle-o text-red"></i> Tempo/Infrações</a></li>
                  </ul>
                </li> 
                <?php  if($_SESSION['consultar_relatorio'] == 'S') { ?>
                  <li class="treeview">
                    <a href="#">
                      <i class="fa fa-users"></i> <span>Relatórios</span>
                      <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                      </span>
                    </a>
                    <ul class="treeview-menu">
                    <li><a href="#" onClick="window.open('classPhp/relatorios/relatorioTempoInfracao.php','iframeCorpo');"><i class="fa fa-circle-o"></i> Tempo/Infrações</a></li>
                    </ul>
                  </li>
                <?php } ?>
							</ul>
						</li>
					</ul>
				</section>
			</aside>			
			<div class="content-wrapper">
				<iframe src="" name="iframeCorpo" id="iframeCorpo">
				</iframe>
			</div>
			<!--
			<footer class="main-footer">
				<div class="pull-right hidden-xs">
					<b>Versão</b> 1.1.1
				</div>
				<strong>Ambiente Desenvolvimento</strong>
			</footer>	
			-->		
		</div>
		<script src="js/jquery.js"></script>
		<script src="js/adminlte.js"></script>
		<script>
			$(document).ready(function () {
				$('.sidebar-menu').tree()
			});
      
      function funcIntegrar(){
        clsJs   = jsString("lote");  
        clsJs.add("rotina"      , "lerSistemSat"      );
        clsJs.add("login"       , jsPub[0].usr_login  );
        fd = new FormData();
        fd.append("integrar" , clsJs.fim());
        msg     = requestPedido("Trac_Integrar.php",fd); 
        retPhp  = JSON.parse(msg);
        if( retPhp[0].retorno == "OK" ){
          gerarMensagemErro("Importar",retPhp[0].erro,"Aviso");  
        } else {
          gerarMensagemErro("Importar",retPhp[0].erro,"Erro");  
        };  
      };
		</script>
	</body>
</html>