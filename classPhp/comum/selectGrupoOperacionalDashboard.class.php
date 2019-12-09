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
<div class="form-group" style="width:15%;height:1.5em;float:left;margin-top:0.5em;">
  <select id="cbGrupoOperacional" onChange="chngGrupoOperacional();" class="form-control select2" style="width:70%;height:28px;margin-left:3em;">
    <option value="TODOS" selected="selected">TODOS</option>
    <?php foreach ($retCls['dados'] as $grupoOperacional) { ?>
      <option value="<?php echo $grupoOperacional['GPO_CODIGO'] ?>">
      <?php echo $grupoOperacional['GPO_NOME'] ?></option>
    <?php } ?>
</select>
</div>
