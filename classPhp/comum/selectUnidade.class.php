<?php
    session_start();

    require_once(__DIR__."/../conectaSqlServer.class.php");
    require_once(__DIR__."/../validaJSon.class.php");    

    $classe   = new conectaBd();      
    $classe->conecta($_SESSION['login']);


    $sql="";
    $sql.=" SELECT ";
    $sql.="  UNI_CODIGO, ";
    $sql.="  UNI_CODGRP, ";
    $sql.="  UNI_NOME  ";
    $sql.="  FROM UNIDADE ";
    $sql.="  JOIN USUARIOUNIDADE ON UNI_CODIGO = UU_CODUNI ";
    $sql.="  WHERE UU_CODUSR = ".$_SESSION["usr_codigo"];

    if( isset($_POST["montaSelectUnidade"]) ){
        $vldr     = new validaJSon();          
        $retCls   = $vldr->validarJs($_POST["montaSelectUnidade"]);
    
        $jsonObj  = $retCls["dados"];
        $lote     = $jsonObj->lote;

        if($lote[0]->poloCodigo != "") {
            $sql.="  AND UNI_CODPOL = '".$lote[0]->poloCodigo."'";
            $sql.="  AND UNI_CODGRP = ".$lote[0]->poloGrupo;
        }
    }
    $sql.=" ORDER BY UNI_NOME, UNI_CODGRP ";
    $classe->msgSelect(false);
    $retClsUnidade=$classe->selectAssoc($sql);
?>
<div id="divCbUnidade" class="campotexto campo40">
<select class="campo_input_combo" id="cbUnidade">
    <option value="TODOS" selected="selected">TODOS</option>
    <?php foreach ($retClsUnidade['dados'] as $unidade) { ?>
      <option value="<?php echo $unidade['UNI_CODIGO'].'-'.$unidade['UNI_CODGRP'] ?>">
      <?php echo $unidade['UNI_NOME'].' - '.$unidade['UNI_CODGRP'] ?></option>
    <?php } ?>
</select>
<label class="campo_label campo_required" for="cbUnidade">UNIDADE</label>
</div>