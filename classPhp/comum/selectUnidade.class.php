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
    $sql.="SELECT UNI_CODIGO, UNI_CODGRP, UNI_NOME FROM UNIDADE A LEFT OUTER JOIN USUARIOSISTEMA U ON A.UNI_CODUSR=U.US_CODIGO
    LEFT OUTER JOIN USUARIOUNIDADE UU ON A.UNI_CODIGO=UU.UU_CODUNI AND UU.UU_CODUSR=".$_SESSION['usr_codigo']."
    WHERE ((UNI_ATIVO='S') AND (COALESCE(UU.UU_ATIVO,'')='S'))";

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