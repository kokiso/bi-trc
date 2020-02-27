<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Connect Plus | Total Trac</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  
  <link rel="stylesheet" href="adminLTE/bootstrap.css">
  <link rel="stylesheet" href="adminLTE/font-awesome.css">
  <link rel="stylesheet" href="adminLTE/ionicons.css">
  <link rel="stylesheet" href="adminLTE/AdminLTE.css">
  <link rel="stylesheet" href="adminLTE/all-skins.css">
  <script src="js/js2017.js"></script>
  <link rel="stylesheet" href="css/iframeBi.css">
  <script language="javascript" type="text/javascript"></script>
  <style>
    .btn-label {
      background-color: #3c8dbc;
      border-color: #367fa9;
      color:white;
    }
    .btn-label:hover{
      color:white;
    }
  </style>
</head>
<body class="hold-transition skin-blue sidebar-mini sidebar-collapse">
<div class="wrapper">

  <header class="main-header">
    <a href="Trac_BiDashBoard.php" class="logo">
      <span class="logo-mini"><b>B.I.</b></span>
      <span class="logo-lg">B.I.</span>
    </a>
    <nav class="navbar navbar-static-top">
      <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </a>
    </nav>
  </header>
  <aside class="main-sidebar">
    <section class="sidebar">
      <div class="user-panel">
        <div class="pull-left image">
          <img src="../../dist/img/logoMenor.png" class="img-circle" alt="User Image">
        </div>
        <div class="pull-left info">
          <p>Total Trac</p>
        </div>
      </div>
      <ul class="sidebar-menu" data-widget="tree">
        <li class="header">Principal</li>
        <li class="treeview">
          <a href="#">
            <i class="fa fa-dashboard"></i> <span>Dashboard</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="#" onClick="window.open('Trac_BiVisaoGeral.php','iframeCorpo');"><i class="fa fa-circle-o text-gray"></i> Visão Geral</a></li>
            <li><a href="#" onClick="window.open('Trac_BiInfracoes.php','iframeCorpo');"><i class="fa fa-circle-o text-red"></i> Infrações</a></li>
						<li><a href="#" onClick="window.open('Trac_BiVeiculos.php','iframeCorpo');"><i class="fa fa-circle-o text-blue"></i> Veículos</a></li>
						<li><a href="#" onClick="window.open('Trac_BiMotorista.php','iframeCorpo');"><i class="fa fa-circle-o text-green"></i> Motoristas</a></li>
          </ul>
        </li>
        <li  class="treeview">
					<a href="#">
            <i class="fa fa-television"></i> <span>Dashboard - TV</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="#"><i class="fa fa-circle-o text-gray"></i> Visão Geral</a></li>
          </ul>
				</li>
        </li>
        <li class="header"><i class="fa fa-search"></i> Consultas Gerais </li>
        <li class="treeview">
          <a href="#">
            <i class="fa fa-filter"></i> <span>Consultas (GRID)</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="#" onClick="window.open('Trac_BiInfracao.php','iframeCorpo');"><i class="fa fa-circle-o text-red"></i> Infrações</a></li>
						<li><a href="#" onClick="window.open('Trac_grdProdutividadeVei.php','iframeCorpo');"><i class="fa fa-circle-o text-red"></i> Produtiv. placa</a></li>
            <li><a href="#" onClick="window.open('Trac_grdProdutividadeMot.php','iframeCorpo');"><i class="fa fa-circle-o text-red"></i> Produtiv. motorista</a></li>
						<li><a href="#" onClick="window.open('Trac_Veiculo.php','iframeCorpo');"><i class="fa fa-circle-o text-blue"></i> Veículos</a></li>
						<li><a href="#" onClick="window.open('Trac_Motorista.php','iframeCorpo');"><i class="fa fa-circle-o text-green"></i> Motoristas</a></li>
						<li><a href="#" onClick="window.open('Trac_grdIdentificacaoMot.php','iframeCorpo');"><i class="fa fa-circle-o text-red"></i> Ident. Condutor</a></li>
						<li><a href="#" onClick="window.open('Trac_grdInfracaoTempo.php','iframeCorpo');"><i class="fa fa-circle-o text-red"></i> Tempo/Infrações</a></li>
						<li><a href="#" onClick="window.open('Trac_grdPeriodoIndevido.php','iframeCorpo');"><i class="fa fa-circle-o text-red"></i> Periodo indevido</a></li>
            <li><a href="#" onClick="window.open('Trac_grdClassifMotorista.php','iframeCorpo');"><i class="fa fa-circle-o text-red"></i> Classific motorista</a></li>
          </ul>
        </li>
        
        <!--
        <li><a href="https://adminlte.io/docs"><i class="fa fa-book"></i> <span>Documentação</span></a></li>
        <li class="header">Rotulos</li>
        <li><a href="#"><i class="fa fa-circle-o text-red"></i> <span>Importante</span></a></li>
        <li><a href="#"><i class="fa fa-circle-o text-yellow"></i> <span>Aviso</span></a></li>
        <li><a href="#"><i class="fa fa-circle-o text-aqua"></i> <span>Informação</span></a></li>
        -->
      </ul>
    </section>
  </aside>
  <div class="content-wrapper">
    <!--<iframe src="Trac_BiMotoristaInicial.php" name="iframeCorpo" id="iframeCorpo">-->
		<iframe src="Trac_BiVisaoGeral.php" name="iframeCorpo" id="iframeCorpo" style="width: 100%; height: 120em;">
    </iframe>
  </div>

  <footer class="main-footer">
    <div class="pull-right hidden-xs">
      <b>Version 1.0</b> de 21 de maio de 2018
    </div>
    <strong>Empresa Total Trac</strong> versão exclusiva para clientes
  </footer>
  <div class="control-sidebar-bg"></div>
</div>
<script src="adminLTE/jquery.js"></script>
<script src="adminLTE/bootstrap.js"></script>
<script src="adminLTE/jquery.slimscroll.js"></script>
<script src="adminLTE/fastclick.js"></script>
<script src="adminLTE/adminlte.js"></script>
<script src="adminLTE/demo.js"></script>
<script src="adminLTE/Chart.js"></script>
</body>
</html>


