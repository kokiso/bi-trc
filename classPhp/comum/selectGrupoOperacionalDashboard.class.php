<?php
    if(!isset($_SESSION)) 
    { 
        session_start(); 
    }

    require_once(__DIR__."/../conectaSqlServer.class.php");
    require_once(__DIR__."/../validaJson.class.php");
    
    $classe   = new conectaBd();      
    $classe->conecta($_SESSION['login']);

    $sql="";
    $sql.=" SELECT ";
    $sql.="  GPO_CODIGO, ";
    $sql.="  GPO_NOME ";
    $sql.="  FROM GRUPOOPERACIONAL ";
    $classe->msgSelect(false);
    $retCls=$classe->selectAssoc($sql);
?>

<li class="dropdown notifications-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <i class="fa fa-filter">&nbsp;Grupos Operacionais</i>
              <span class="label label-warning" style="top:5px;" id="qtosGpo"></span>
            </a>
            <ul class="dropdown-menu">
              <li class="header">Grupos Operacionais</li>
              <li>
                <ul id="filtroGpo" class="menu" style="max-height: 500px;">
                </ul>
              </li>
              <li class="footer"><a href="#">Fechar</a></li>
            </ul>
          </li>

<div class="form-group" style="width:15%;height:1.5em;float:left;margin-top:0.5em;">
  <select id="cbGrupoOperacional" onChange="chngGrupoOperacional();" class="form-control select2" style="width:70%;height:28px;margin-left:3em;">
    <option value="TODOS GRUPOS OPERACIONAIS" selected="selected">TODOS GRUPOS OPERACIONAIS</option>
    <?php foreach ($retCls['dados'] as $grupoOperacional) { ?>
      <option value="<?php echo $grupoOperacional['GPO_CODIGO'] ?>">
      <?php echo $grupoOperacional['GPO_NOME'] ?></option>
    <?php } ?>
</select>
</div>
