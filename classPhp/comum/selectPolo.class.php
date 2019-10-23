<?php
    session_start();

    require_once(__DIR__."/../conectaSqlServer.class.php");
    require_once(__DIR__."/../validaJSon.class.php");    
    
    $classe   = new conectaBd();      
    $classe->conecta($_SESSION['login']);

    $sql="";
    $sql.=" SELECT ";
    $sql.="  POL_CODIGO, ";
    $sql.="  POL_CODGRP, ";
    $sql.="  POL_NOME  ";
    $sql.="  FROM POLO ";
    $sql.="  WHERE POL_CODIGO IN (SELECT UNI_CODPOL ";
    $sql.="   FROM UNIDADE ";
    $sql.="   JOIN USUARIOUNIDADE ON UNI_CODIGO = UU_CODUNI ";
    $sql.="   WHERE UU_CODUSR = ".$_SESSION["usr_codigo"].");";
    $classe->msgSelect(false);
    $retCls=$classe->selectAssoc($sql);
?>
<div id="divCbPolo" class="campotexto campo25">
<select class="campo_input_combo" id="cbPolo" onchange="montaUnidade()">
    <option value="TODOS" selected="selected">TODOS</option>
    <?php foreach ($retCls['dados'] as $polo) { ?>
      <option value="<?php echo $polo['POL_CODIGO'].'-'.$polo['POL_CODGRP'] ?>">
      <?php echo $polo['POL_NOME'].' - '.$polo['POL_CODGRP'] ?></option>
    <?php } ?>
</select>
<label class="campo_label campo_required" for="cbPolo">POLO</label>
</div>