<?php
    session_start();

    require("../classPhp/conectaSqlServer.class.php");
    require("../classPhp/validaJson.class.php"); 

    $classe   = new conectaBd();
    $classe->conecta('INTEGRAR'); 
    $vldr       = new validaJson();          
    $retorno    = "";
    $polo = "";
    $unidade = "";

    $query_drop_table_movimentoFiltro= "TRUNCATE TABLE MOVIMENTOFILTRO";

    $classe->msgSelect(false);
    $result=$classe->select($query_drop_table_movimentoFiltro);
    //print_r($result);
    if( $result['retorno'] != "OK" ){
    trigger_error("Deu ruim!",  $result['error']);  
    }

    // INSERINDO DADOS EM OUTRA TABELA
        $query_insert_movimentofiltro = "INSERT INTO MOVIMENTOFILTRO(MVMF_DATAGPS, MVMF_PLACA, MVMF_CODPOL, MVMF_CODUNI, MVMF_CODVEI, MVMF_TEMPOSEMCOM, MVMF_LOCALIZACAO, MVMF_NOMEUNI) SELECT CONVERT(varchar, X.MVM_DATAGPS) AS DATAGPS,
        X.MVM_PLACA AS PLACA, X.MVM_CODPOL AS POL, X.MVM_CODUNI AS UNI, x.MVM_CODVEI AS VEI, 
        CAST(CAST(Convert(VarChar(10), DATEDIFF(MI, MVM_DATAGPS, GETDATE()) / 60) + '.' + Right(Replicate('0', 2) + Convert(VarChar(10), DATEDIFF(MI, MVM_DATAGPS, GETDATE()) % 60), 2) AS NUMERIC) AS INT) AS TEMPOSEMCOM, X.MVM_LOCALIZACAO  AS LOCALIZACAO, Y.UNI_NOME FROM MOVIMENTO X
        LEFT JOIN UNIDADE AS Y ON X.MVM_CODUNI = Y.UNI_CODIGO
        WHERE X.MVM_DATAGPS = (SELECT MAX(I.MVM_DATAGPS) FROM MOVIMENTO I
        WHERE X.MVM_CODVEI = I.MVM_CODVEI AND X.MVM_PLACA = I.MVM_PLACA AND CAST(CAST(Convert(VarChar(10), DATEDIFF(MI, MVM_DATAGPS, GETDATE()) / 60) + '.' + Right(Replicate('0', 2) + Convert(VarChar(10), DATEDIFF(MI, MVM_DATAGPS, GETDATE()) % 60), 2) AS NUMERIC) AS INT)  > 12)";


        $classe->msgSelect(false);
        $result=$classe->select($query_insert_movimentofiltro);
        //print_r($result);
        if( $result['retorno'] != "OK" ){
        trigger_error("Deu ruim!",  $result['error']);  
        }
?>

<!-- USE integrar

DROP TABLE MOVIMENTOFILTRO;

CREATE TABLE MOVIMENTOFILTRO(
	MVMF_CODVEI int NOT NULL,
	MVMF_PLACA varchar(10) NOT NULL,
	MVMF_CODUNI int NOT NULL,
	MVMF_CODPOL varchar(3) NOT NULL,
	MVMF_DATAGPS datetime NOT NULL,
	MVMF_TEMPOSEMCOM int NOT NULL,
	MVMF_LOCALIZACAO varchar(100) NOT NULL
) 

 -->
