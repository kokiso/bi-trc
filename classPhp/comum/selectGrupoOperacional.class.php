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
<div id="divCbGpo" class="campotexto campo25">
<select class="campo_input_combo" id="cbGpo">
    <option value="TODOS" selected="selected">TODOS</option>
    <?php foreach ($retCls['dados'] as $grupoOperacional) { ?>
      <option value="<?php echo $grupoOperacional['GPO_CODIGO'] ?>">
      <?php echo $grupoOperacional['GPO_NOME'] ?></option>
    <?php } ?>
</select>
<label class="campo_label campo_required" for="cbGpo">GRUPO OPERACIONAL</label>
</div>