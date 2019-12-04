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
    $sql.="select TOP 1 MONTH(MVM_DATAGPS) as MES, YEAR(MVM_DATAGPS) as ANO from MOVIMENTO order by MVM_POSICAO desc;";
    $classe->msgSelect(false);
    $retCls=$classe->selectAssoc($sql);

    $listaMeses = array();

    foreach($retCls['dados'] as $ret) {
        for($i = 0; $i <= 12; $i++) {
            $data = date('m/y', strtotime('-'.$i.' month', strtotime('1-'.$ret["MES"].'-'.$ret["ANO"].'')));
            $lista = (explode("/",$data));
            switch ($lista[0]) {
                case 1:
                    $lista[2] = 'JAN';
                    break;
                case 2:
                    $lista[2] = 'FEV';
                    break;
                case 3:
                    $lista[2] = 'MAR';
                    break;
                case 4:
                    $lista[2] = 'ABR';
                    break;
                case 5:
                    $lista[2] = 'MAI';
                    break;
                case 6:
                    $lista[2] = 'JUN';
                    break;
                case 7:
                    $lista[2] = 'JUL';
                    break;
                case 8:
                    $lista[2] = 'AGO';
                    break;
                case 9:
                    $lista[2] = 'SET';
                    break;
                case 10:
                    $lista[2] = 'OUT';
                    break;
                case 11:
                    $lista[2] = 'NOV';
                    break;
                case 12:
                    $lista[2] = 'DEZ';
                    break;
            }
            array_push($listaMeses, $lista);
        }
        $listaMeses = array_reverse($listaMeses);
    }
?>
<div id="divCbMes" class="campotexto campo15">
<select class="campo_input_combo" id="cbIni">
<?php foreach ($listaMeses as $li) { ?>
        <option value="<?php 
        $mes = $li[1].$li[0];
        if ($mesAntigoPipe) {
            $mes = $mes.'|201805';
        } elseif ($mesAtualPipe) {
            $mes = $mes.'|'.'20'.$mes;
        }
        echo '20'.$mes ?>" selected="selected">
        <?php echo $li[2].'/'.$li[1] ?>
        </option>
      <?php } ?>
</select>
<label class="campo_label campo_required" for="cbIni">MÊS</label>
</div>