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
    $sql.="SELECT P.POL_CODIGO, P.POL_CODGRP, P.POL_NOME
            FROM UNIDADE A
             LEFT OUTER JOIN POLO P ON A.UNI_CODPOL = P.POL_CODIGO
             LEFT OUTER JOIN USUARIOSISTEMA U ON A.UNI_CODUSR = U.US_CODIGO
             LEFT OUTER JOIN USUARIOUNIDADE UU ON A.UNI_CODIGO = UU.UU_CODUNI AND UU.UU_CODUSR = '".$_SESSION["usr_codigo"]."' 

    WHERE ((UNI_ATIVO = 'S') AND (COALESCE(UU.UU_ATIVO, '') = 'S'))
    GROUP BY P.POL_CODIGO, P.POL_NOME, P.POL_CODGRP;";
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