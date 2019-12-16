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
    $sql.="SELECT GPO_CODIGO, GPO_NOME from GRUPOOPERACIONAL
    INNER JOIN GRUPOOPERACIONALUNIDADE on GOU_CODGPO=GRUPOOPERACIONAL.GPO_CODIGO AND
    GOU_CODUNI";
    if( isset($_POST["montaSelectGrupoOperacional"]) ){
        $vldr     = new validaJSon();          
        $retCls   = $vldr->validarJs($_POST["montaSelectGrupoOperacional"]);
    
        $jsonObj  = $retCls["dados"];
        $lote     = $jsonObj->lote;

        if($lote[0]->uniCodigo != "") {
            $sql.="=".$lote[0]->uniCodigo;
        } else {
           $sql.=" IN (SELECT UU_CODUNI FROM USUARIOUNIDADE WHERE UU_CODUSR=".$_SESSION['usr_codigo']." AND UU_ATIVO='S')";
        }
    } else {
        $sql.=" IN (SELECT UU_CODUNI FROM USUARIOUNIDADE WHERE UU_CODUSR=".$_SESSION['usr_codigo']." AND UU_ATIVO='S')";
    }
    $sql.=" GROUP BY GPO_CODIGO, GPO_NOME";
     
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