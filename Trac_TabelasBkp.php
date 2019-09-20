<?php
  session_start();
  if( isset($_POST["bkp"]) ){
    try{     
      require("classPhp/conectaSqlServer.class.php");
      require("classPhp/validaJSon.class.php"); 
      require("classPhp/removeAcento.class.php"); 

      $vldr     = new validaJSon();          
      $retorno  = "";
      $retCls   = $vldr->validarJs($_POST["bkp"]);
      ///////////////////////////////////////////////////////////////////////
      // Variavel mostra que não foi feito apenas selects mas atualizou BD //
      ///////////////////////////////////////////////////////////////////////
      if($retCls["retorno"] != "OK"){
        $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';
        unset($retCls,$vldr);      
      } else {
        $ql       = "";
        $jsonObj  = $retCls["dados"];
        $lote     = $jsonObj->lote;
        $rotina   = $lote[0]->rotina;
        $classe   = new conectaBd();
        $classe->conecta($lote[0]->login);
        ////////////////////////////////////////////////
        //          Dados para JavaScript CARGO       //
        ////////////////////////////////////////////////
        if( $rotina=="selectBkpCrg" ){
          $sql="SELECT CRG_ID"
          ."           ,CAST(CRG_DATA AS VARCHAR(10)) AS CRG_DATA"
          ."           ,CASE WHEN A.CRG_ACAO='I' THEN 'INC'" 
          ."                 WHEN A.CRG_ACAO='A' THEN 'ALT'" 
          ."                 WHEN A.CRG_ACAO='E' THEN 'EXC' END AS CRG_ACAO"
          ."          ,CRG_CODIGO"
          ."          ,CRG_NOME"
          ."          ,CASE WHEN A.CRG_REG='P' THEN 'PUB' WHEN A.CRG_REG='S' THEN 'SIS' ELSE 'ADM' END AS CRG_REG"
          ."          ,CASE WHEN A.CRG_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS CRG_ATIVO"
          ."          ,US_APELIDO"
          ."     FROM BKPCARGO A" 
          ."     LEFT OUTER JOIN USUARIOSISTEMA U ON A.CRG_CODUSR=U.US_CODIGO"
          .$lote[0]->where;
        };
        ////////////////////////////////////////////////
        //    Dados para JavaScript EVENTO            //
        ////////////////////////////////////////////////        
        if( $rotina=="selectBkpEve" ){
          $sql="SELECT EVE_ID"
          ."           ,CAST(EVE_DATA AS VARCHAR(10)) AS EG_DATA"
          ."           ,CASE WHEN A.EVE_ACAO='I' THEN 'INC'" 
          ."                 WHEN A.EVE_ACAO='A' THEN 'ALT'" 
          ."                 WHEN A.EVE_ACAO='E' THEN 'EXC' END AS EVE_ACAO"
          ."          ,EVE_CODIGO"
          ."          ,EVE_NOME"
          ."          ,EVE_CODEG" 
          ."          ,EVE_MOVIMENTO"					
          ."          ,CASE WHEN A.EVE_REG='P' THEN 'PUB' WHEN A.EVE_REG='S' THEN 'SIS' ELSE 'ADM' END AS EVE_REG"
          ."          ,CASE WHEN A.EVE_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS EVE_ATIVO"
          ."          ,US_APELIDO"
          ."     FROM BKPEVENTO A" 
          ."     LEFT OUTER JOIN USUARIOSISTEMA U ON A.EVE_CODUSR=U.US_CODIGO"
          .$lote[0]->where;
        };
        ////////////////////////////////////////////////
        //    Dados para JavaScript EVENTOGRUPO       //
        ////////////////////////////////////////////////        
        if( $rotina=="selectBkpEg" ){
          $sql="SELECT EG_ID"
          ."           ,CAST(EG_DATA AS VARCHAR(10)) AS EG_DATA"
          ."           ,CASE WHEN A.EG_ACAO='I' THEN 'INC'" 
          ."                 WHEN A.EG_ACAO='A' THEN 'ALT'" 
          ."                 WHEN A.EG_ACAO='E' THEN 'EXC' END AS EG_ACAO"
          ."          ,EG_CODIGO"
          ."          ,EG_NOME"
          ."          ,CASE WHEN A.EG_REG='P' THEN 'PUB' WHEN A.EG_REG='S' THEN 'SIS' ELSE 'ADM' END AS EG_REG"
          ."          ,CASE WHEN A.EG_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS EG_ATIVO"
          ."          ,US_APELIDO"
          ."     FROM BKPEVENTOGRUPO A" 
          ."     LEFT OUTER JOIN USUARIOSISTEMA U ON A.EG_CODUSR=U.US_CODIGO"
          .$lote[0]->where;
        };
        ////////////////////////////////////////////////
        //          Dados para JavaScript GRUPO       //
        ////////////////////////////////////////////////
        if( $rotina=="selectBkpGrp" ){
          $sql="SELECT GRP_ID"
          ."           ,CAST(GRP_DATA AS VARCHAR(10)) AS GRP_DATA"
          ."           ,CASE WHEN A.GRP_ACAO='I' THEN 'INC'" 
          ."                 WHEN A.GRP_ACAO='A' THEN 'ALT'" 
          ."                 WHEN A.GRP_ACAO='E' THEN 'EXC' END AS GRP_ACAO"
          ."          ,GRP_CODIGO"
          ."          ,GRP_NOME"
          ."          ,GRP_APELIDO"
          ."          ,CASE WHEN A.GRP_REG='P' THEN 'PUB' WHEN A.GRP_REG='S' THEN 'SIS' ELSE 'ADM' END AS GRP_REG"
          ."          ,CASE WHEN A.GRP_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS GRP_ATIVO"
          ."          ,US_APELIDO"
          ."     FROM BKPGRUPO A" 
          ."     LEFT OUTER JOIN USUARIOSISTEMA U ON A.GRP_CODUSR=U.US_CODIGO"
          .$lote[0]->where;
        };
        ////////////////////////////////////////////////
        //      Dados para JavaScript MOTORISTA       //
        ////////////////////////////////////////////////
        if( $rotina=="selectBkpMtr" ){
          $sql="SELECT MTR_ID"
          ."           ,CAST(MTR_DATA AS VARCHAR(10)) AS MTR_DATA"
          ."           ,CASE WHEN A.MTR_ACAO='I' THEN 'INC'" 
          ."                 WHEN A.MTR_ACAO='A' THEN 'ALT'" 
          ."                 WHEN A.MTR_ACAO='E' THEN 'EXC' END AS MTR_ACAO"
          ."          ,MTR_CODIGO"
          ."          ,MTR_NOME"
          ."          ,MTR_RFID"
          ."          ,MTR_CODUNI"          
          ."          ,CASE WHEN A.MTR_REG='P' THEN 'PUB' WHEN A.MTR_REG='S' THEN 'SIS' ELSE 'ADM' END AS MTR_REG"
          ."          ,CASE WHEN A.MTR_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS MTR_ATIVO"
          ."          ,US_APELIDO"
          ."     FROM BKPMOTORISTA A" 
          ."     LEFT OUTER JOIN USUARIOSISTEMA U ON A.MTR_CODUSR=U.US_CODIGO"
          .$lote[0]->where;
        };
        
        ////////////////////////////////////////////////
        //          Dados para JavaScript POLO        //
        ////////////////////////////////////////////////
        if( $rotina=="selectBkpPol" ){
          $sql="SELECT POL_ID"
          ."           ,CAST(POL_DATA AS VARCHAR(10)) AS POL_DATA"
          ."           ,CASE WHEN A.POL_ACAO='I' THEN 'INC'" 
          ."                 WHEN A.POL_ACAO='A' THEN 'ALT'" 
          ."                 WHEN A.POL_ACAO='E' THEN 'EXC' END AS POL_ACAO"
          ."          ,POL_CODIGO"
          ."          ,POL_NOME"
          ."          ,POL_CODGRP"
          ."          ,CASE WHEN A.POL_REG='P' THEN 'PUB' WHEN A.POL_REG='S' THEN 'SIS' ELSE 'ADM' END AS POL_REG"
          ."          ,CASE WHEN A.POL_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS POL_ATIVO"
          ."          ,US_APELIDO"
          ."     FROM BKPPOLO A" 
          ."     LEFT OUTER JOIN USUARIOSISTEMA U ON A.POL_CODUSR=U.US_CODIGO"
          .$lote[0]->where;
        };
        ////////////////////////////////////////////////
        //        Dados para JavaScript UNIDADE       //
        ////////////////////////////////////////////////
        if( $rotina=="selectBkpUni" ){
          $sql="SELECT UNI_ID"
          ."           ,CAST(UNI_DATA AS VARCHAR(10)) AS UNI_DATA"
          ."           ,CASE WHEN A.UNI_ACAO='I' THEN 'INC'" 
          ."                 WHEN A.UNI_ACAO='A' THEN 'ALT'" 
          ."                 WHEN A.UNI_ACAO='E' THEN 'EXC' END AS UNI_ACAO"
          ."          ,UNI_CODIGO"
          ."          ,UNI_NOME"
          ."          ,UNI_APELIDO"
          ."          ,UNI_CNPJCPF"
          ."          ,UNI_CODGRP"
          ."          ,CASE WHEN A.UNI_REG='P' THEN 'PUB' WHEN A.UNI_REG='S' THEN 'SIS' ELSE 'ADM' END AS UNI_REG"
          ."          ,CASE WHEN A.UNI_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS UNI_ATIVO"
          ."          ,US_APELIDO"
          ."     FROM BKPUNIDADE A" 
          ."     LEFT OUTER JOIN USUARIOSISTEMA U ON A.UNI_CODUSR=U.US_CODIGO"
          .$lote[0]->where;
        };
        
        /////////////////////////////////////////
        // Dados para JavaScript USUARIOPERFIL //
        /////////////////////////////////////////
        if( $rotina=="selectBkpUp" ){
          $sql="SELECT A.UP_ID"
          ."           ,CAST(A.UP_DATA AS VARCHAR(10)) AS UP_DATA"
          ."           ,CASE WHEN A.UP_ACAO='I' THEN 'INC'" 
          ."                 WHEN A.UP_ACAO='A' THEN 'ALT'" 
          ."                 WHEN A.UP_ACAO='E' THEN 'EXC' END AS UP_ACAO"
          ."          ,A.UP_CODIGO"
          ."          ,A.UP_NOME"
          ."          ,A.UP_D01"
          ."          ,A.UP_D02"
          ."          ,A.UP_D03"
          ."          ,A.UP_D04"
          ."          ,A.UP_D05"
          ."          ,A.UP_D06"
          ."          ,A.UP_D07"
          ."          ,A.UP_D08"
          ."          ,A.UP_D09"
          ."          ,A.UP_D10"
          ."          ,A.UP_D11"
          ."          ,A.UP_D12"
          ."          ,A.UP_D13"
          ."          ,A.UP_D14"
          ."          ,A.UP_D15"
          ."          ,A.UP_D16"
          ."          ,A.UP_D17"
          ."          ,A.UP_D18"
          ."          ,A.UP_D19"
          ."          ,A.UP_D20"
          ."          ,CASE WHEN A.UP_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS UP_ATIVO"
          ."          ,CASE WHEN A.UP_REG='P' THEN 'PUB' WHEN A.UP_REG='S' THEN 'SIS' ELSE 'ADM' END AS UP_REG"
          ."          ,U.US_APELIDO"
          ."          ,A.UP_CODUSR"
          ."     FROM BKPUSUARIOPERFIL A" 
          ."     LEFT OUTER JOIN USUARIOSISTEMA U ON A.UP_CODUSR=U.US_CODIGO"
          .$lote[0]->where;
        };
        ////////////////////////////////////////////
        // Dados para JavaScript USUARIOUNIDADE  //
        ////////////////////////////////////////////
        if( $rotina=="selectBkpUu" ){
          $sql="SELECT A.UU_ID"
          ."           ,CAST(A.UU_DATA AS VARCHAR(10)) AS UU_DATA"
          ."           ,CASE WHEN A.UU_ACAO='I' THEN 'INC'" 
          ."                 WHEN A.UU_ACAO='A' THEN 'ALT'" 
          ."                 WHEN A.UU_ACAO='E' THEN 'EXC' END AS UU_ACAO"
          ."           ,A.UU_CODUSR"
          ."           ,A.UU_CODGRP"          
          ."           ,CASE WHEN A.UU_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS UU_ATIVO"
          ."           ,CASE WHEN A.UU_REG='P' THEN 'PUB' WHEN A.UU_REG='S' THEN 'SIS' ELSE 'ADM' END AS UU_REG"
          ."           ,U.US_APELIDO"
          ."     FROM BKPUSUARIOUNIDADE A" 
          ."     LEFT OUTER JOIN USUARIOSISTEMA U ON A.SIS_CODUSR=U.US_CODIGO"
          .$lote[0]->where;
        };        
        ////////////////////////////////////////////////
        //       Dados para JavaScript VEICULO        //
        ////////////////////////////////////////////////
        if( $rotina=="selectBkpVcl" ){
          $sql="SELECT VCL_ID"
          ."           ,CAST(VCL_DATA AS VARCHAR(10)) AS VCL_DATA"
          ."           ,CASE WHEN A.VCL_ACAO='I' THEN 'INC'" 
          ."                 WHEN A.VCL_ACAO='A' THEN 'ALT'" 
          ."                 WHEN A.VCL_ACAO='E' THEN 'EXC' END AS VCL_ACAO"
          ."          ,VCL_CODIGO"
          ."          ,VCL_NOME"
          ."          ,VCL_FROTA"
          ."          ,VCL_CODUNI"
          ."          ,VCL_ENTRABI"
          ."          ,CONVERT(VARCHAR(10),VCL_DTCALIBRACAO,127) AS VCL_DTCALIBRACAO"          
          ."          ,VCL_NUMFROTA"          
          ."          ,CASE WHEN A.VCL_REG='P' THEN 'PUB' WHEN A.VCL_REG='S' THEN 'SIS' ELSE 'ADM' END AS VCL_REG"
          ."          ,CASE WHEN A.VCL_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS VCL_ATIVO"
          ."          ,US_APELIDO"
          ."     FROM BKPVEICULO A" 
          ."     LEFT OUTER JOIN USUARIOSISTEMA U ON A.VCL_CODUSR=U.US_CODIGO"
          .$lote[0]->where;
        };
        /*
        ////////////////////////////////////////////////
        //       Dados para JavaScript ALVO           //
        ////////////////////////////////////////////////
        if( $rotina=="selectBkpAlv" ){
          $sql="SELECT ALV.ALV_ID"
          ."           ,CAST(ALV.ALV_DATA AS VARCHAR(10)) AS ALV_DATA"
          ."           ,CASE WHEN ALV.ALV_ACAO='I' THEN 'INC'" 
          ."                 WHEN ALV.ALV_ACAO='A' THEN 'ALT'" 
          ."                 WHEN ALV.ALV_ACAO='E' THEN 'EXC' END AS ALV_ACAO"
          ."           ,ALV.ALV_CODIGO"
          ."           ,ALV.ALV_NOME"
          ."           ,ALV.ALV_CODACL"
          ."           ,ALV.ALV_CODCLN"
          ."           ,ALV.ALV_CEP"
          ."           ,ALV.ALV_CODCDD"
          ."           ,ALV.ALV_CODLGR"
          ."           ,ALV.ALV_ENDERECO"
          ."           ,ALV.ALV_NUMERO"
          ."           ,ALV.ALV_CNPJALVO"
          ."           ,ALV.ALV_CHAVE"
          ."           ,ALV.ALV_LATITUDE"
          ."           ,ALV.ALV_LONGITUDE"
          ."           ,ALV.ALV_RAIO"
          ."           ,ALV.ALV_CODRSC"
          ."           ,ALV.ALV_PARADA"
          ."           ,CASE WHEN ALV.ALV_ATIVO='S' THEN 'SIM' ELSE 'NAO' END"
          ."           ,CASE WHEN ALV.ALV_REG='P' THEN 'PUB' WHEN ALV.ALV_REG='S' THEN 'SIS' ELSE 'ADM' END"
          ."           ,US.US_APELIDO"
          ."           ,ALV.ALV_CODUSR"
          ."      FROM BKPALVO ALV"
          ."      LEFT OUTER JOIN USUARIOSISTEMA US ON ALV.ALV_CODUSR=US.US_CODIGO"
          .$lote[0]->where;
        };
        ////////////////////////////////////////////////
        //  Dados para JavaScript ALVOCLASSIF         //
        ////////////////////////////////////////////////
        if( $rotina=="selectBkpAcl" ){
          $sql="SELECT ACL_ID"
          ."           ,CAST(ACL_DATA AS VARCHAR(10)) AS ACL_DATA"
          ."           ,CASE WHEN A.ACL_ACAO='I' THEN 'INC'" 
          ."                 WHEN A.ACL_ACAO='A' THEN 'ALT'" 
          ."                 WHEN A.ACL_ACAO='E' THEN 'EXC' END AS ACL_ACAO"
          ."          ,ACL_CODIGO"
          ."          ,ACL_NOME"
          ."          ,CASE WHEN A.ACL_REG='P' THEN 'PUB' WHEN A.ACL_REG='S' THEN 'SIS' ELSE 'ADM' END AS ACL_REG"
          ."          ,CASE WHEN A.ACL_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS ACL_ATIVO"
          ."          ,US_APELIDO"
          ."     FROM BKPALVOCLASSIF A" 
          ."     LEFT OUTER JOIN USUARIOSISTEMA U ON A.ACL_CODUSR=U.US_CODIGO"
          .$lote[0]->where;
        };
        ////////////////////////////////////////////////
        //        Dados para JavaScript CLIENTE       //
        ////////////////////////////////////////////////
        if( $rotina=="selectBkpCln" ){
          $sql="SELECT CLN_ID"
          ."           ,CAST(CLN_DATA AS VARCHAR(10)) AS CLN_DATA"
          ."           ,CASE WHEN A.CLN_ACAO='I' THEN 'INC'" 
          ."                 WHEN A.CLN_ACAO='A' THEN 'ALT'" 
          ."                 WHEN A.CLN_ACAO='E' THEN 'EXC' END AS CLN_ACAO"
          ."           ,A.CLN_CODIGO"
          ."           ,A.CLN_NOME"
          ."           ,A.CLN_APELIDO"
          ."           ,CASE WHEN A.CLN_FISJUR='F' THEN CAST('FIS' AS VARCHAR(3)) ELSE CAST('JUR' AS VARCHAR(3)) END AS CLN_FISJUR"
          ."           ,A.CLN_CNPJCPF"
          ."           ,A.CLN_CODTCO"
          ."           ,A.CLN_CODCDD"
          ."           ,A.CLN_CEP"
          ."           ,A.CLN_CODLGR"
          ."           ,A.CLN_ENDERECO"
          ."           ,A.CLN_NUMERO"
          ."           ,A.CLN_COMPLEMENTO"
          ."           ,A.CLN_BAIRRO"
          ."           ,A.CLN_FONE"
          ."           ,A.CLN_EMAIL"
          ."           ,A.CLN_SITE"
          ."           ,A.CLN_CODSGR"
          ."           ,A.CLN_CODCRR"          
          ."           ,A.CLN_FATURAR"          
          ."          ,CASE WHEN A.CLN_REG='P' THEN 'PUB' WHEN A.CLN_REG='S' THEN 'SIS' ELSE 'ADM' END AS CLN_REG"
          ."          ,CASE WHEN A.CLN_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS CLN_ATIVO"
          ."          ,US_APELIDO"
          ."     FROM BKPCLIENTE A" 
          ."     LEFT OUTER JOIN USUARIOSISTEMA U ON A.CLN_CODUSR=U.US_CODIGO"
          .$lote[0]->where;
        };
        ////////////////////////////////////////////////
        //        Dados para JavaScript CONTATO       //
        ////////////////////////////////////////////////
        if( $rotina=="selectBkpCnt" ){
          $sql="SELECT CNT_ID"
          ."           ,CAST(CNT_DATA AS VARCHAR(10)) AS CNT_DATA"
          ."           ,CASE WHEN A.CNT_ACAO='I' THEN 'INC'" 
          ."                 WHEN A.CNT_ACAO='A' THEN 'ALT'" 
          ."                 WHEN A.CNT_ACAO='E' THEN 'EXC' END AS CNT_ACAO"
          ."           ,CNT_CODIGO"
          ."           ,CNT_CODOPE"
          ."           ,CNT_NOME"
          ."           ,CNT_CODCRG"
          ."           ,CNT_CODCDD"
          ."           ,CNT_HORAI"
          ."           ,CNT_HORAF"
          ."           ,CNT_EMAIL"
          ."           ,CNT_FONE"
          ."           ,CNT_RAMAL"
          ."           ,CNT_CELULARDDD"
          ."           ,CNT_CELULAR"
          ."          ,CASE WHEN A.CNT_REG='P' THEN 'PUB' WHEN A.CNT_REG='S' THEN 'SIS' ELSE 'ADM' END AS CNT_REG"
          ."          ,CASE WHEN A.CNT_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS CNT_ATIVO"
          ."          ,US_APELIDO"
          ."     FROM BKPCONTATO A" 
          ."     LEFT OUTER JOIN USUARIOSISTEMA U ON A.CNT_CODUSR=U.US_CODIGO"
          .$lote[0]->where;
        };
        ////////////////////////////////////////////////
        //          Dados para JavaScript CARGO       //
        ////////////////////////////////////////////////
        if( $rotina=="selectBkpCrg" ){
          $sql="SELECT CRG_ID"
          ."           ,CAST(CRG_DATA AS VARCHAR(10)) AS CRG_DATA"
          ."           ,CASE WHEN A.CRG_ACAO='I' THEN 'INC'" 
          ."                 WHEN A.CRG_ACAO='A' THEN 'ALT'" 
          ."                 WHEN A.CRG_ACAO='E' THEN 'EXC' END AS CRG_ACAO"
          ."          ,CRG_CODIGO"
          ."          ,CRG_NOME"
          ."          ,CASE WHEN A.CRG_REG='P' THEN 'PUB' WHEN A.CRG_REG='S' THEN 'SIS' ELSE 'ADM' END AS CRG_REG"
          ."          ,CASE WHEN A.CRG_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS CRG_ATIVO"
          ."          ,US_APELIDO"
          ."     FROM BKPCARGO A" 
          ."     LEFT OUTER JOIN USUARIOSISTEMA U ON A.CRG_CODUSR=U.US_CODIGO"
          .$lote[0]->where;
        };
        /////////////////////////////////////////
        //     Dados para JavaScript CIDADE    //
        /////////////////////////////////////////
        if( $rotina=="selectBkpCdd" ){
          $sql="SELECT CDD_ID"
          ."           ,CAST(CDD_DATA AS VARCHAR(10)) AS CDD_DATA"
          ."           ,CASE WHEN A.CDD_ACAO='I' THEN 'INC'" 
          ."                 WHEN A.CDD_ACAO='A' THEN 'ALT'" 
          ."                 WHEN A.CDD_ACAO='E' THEN 'EXC' END AS CDD_ACAO"
          ."          ,CDD_CODIGO"
          ."          ,CDD_NOME"
          ."          ,CDD_CODEST"
          ."          ,CDD_LATITUDE"
          ."          ,CDD_LONGITUDE" 
          ."          ,CDD_RAIO" 
          ."          ,CDD_DDD"               
          ."          ,CASE WHEN A.CDD_REG='P' THEN 'PUB' WHEN A.CDD_REG='S' THEN 'SIS' ELSE 'ADM' END AS CDD_REG"
          ."          ,CASE WHEN A.CDD_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS CDD_ATIVO"
          ."          ,US_APELIDO"
          ."     FROM BKPCIDADE A" 
          ."     LEFT OUTER JOIN USUARIOSISTEMA U ON A.CDD_CODUSR=U.US_CODIGO"
          .$lote[0]->where;
        };
        ////////////////////////////////////////////////
        //        Dados para JavaScript CORRETORA     //
        ////////////////////////////////////////////////
        if( $rotina=="selectBkpCrr" ){
          $sql="SELECT CRR_ID"
          ."           ,CAST(CRR_DATA AS VARCHAR(10)) AS CRR_DATA"
          ."           ,CASE WHEN A.CRR_ACAO='I' THEN 'INC'" 
          ."                 WHEN A.CRR_ACAO='A' THEN 'ALT'" 
          ."                 WHEN A.CRR_ACAO='E' THEN 'EXC' END AS CRR_ACAO"
          ."           ,A.CRR_CODIGO"
          ."           ,A.CRR_NOME"
          ."           ,A.CRR_APELIDO"
          ."           ,A.CRR_CNPJCPF"
          ."           ,A.CRR_FONE"
          ."           ,A.CRR_EMAIL"
          ."          ,CASE WHEN A.CRR_REG='P' THEN 'PUB' WHEN A.CRR_REG='S' THEN 'SIS' ELSE 'ADM' END AS CRR_REG"
          ."          ,CASE WHEN A.CRR_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS CRR_ATIVO"
          ."          ,US_APELIDO"
          ."     FROM BKPCORRETORA A" 
          ."     LEFT OUTER JOIN USUARIOSISTEMA U ON A.CRR_CODUSR=U.US_CODIGO"
          .$lote[0]->where;
        };
        ////////////////////////////////////////////
        // Dados para JavaScript EMBARCADORTRANSP //
        ////////////////////////////////////////////
        if( $rotina=="selectBkpEt" ){
          $sql="SELECT A.ET_ID"
          ."           ,CAST(A.ET_DATA AS VARCHAR(10)) AS ET_DATA"
          ."           ,CASE WHEN A.ET_ACAO='I' THEN 'INC'" 
          ."                 WHEN A.ET_ACAO='A' THEN 'ALT'" 
          ."                 WHEN A.ET_ACAO='E' THEN 'EXC' END AS ET_ACAO"
          ."           ,A.ET_CODEMB"
          ."           ,A.ET_CODTRA"          
          ."           ,CASE WHEN A.ET_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS ET_ATIVO"
          ."           ,CASE WHEN A.ET_REG='P' THEN 'PUB' WHEN A.ET_REG='S' THEN 'SIS' ELSE 'ADM' END AS ET_REG"
          ."           ,U.US_APELIDO"
          ."     FROM BKPEMBARCADORTRANSP A" 
          ."     LEFT OUTER JOIN USUARIOSISTEMA U ON A.ET_CODUSR=U.US_CODIGO"
          .$lote[0]->where;
        };        
        /////////////////////////////////////////
        //  Dados para JavaScript EQUIPAMENTO  //
        /////////////////////////////////////////
        if( $rotina=="selectBkpEqu" ){
          $sql="SELECT EQU_ID"
          ."           ,CAST(EQU_DATA AS VARCHAR(10)) AS EQU_DATA"
          ."           ,CASE WHEN A.EQU_ACAO='I' THEN 'INC'" 
          ."                 WHEN A.EQU_ACAO='A' THEN 'ALT'" 
          ."                 WHEN A.EQU_ACAO='E' THEN 'EXC' END AS EQU_ACAO"
          ."           ,EQU_CODIGO"          
          ."           ,EQU_CODETP"
          ."           ,EQU_CODETC"
          ."           ,EQU_CODEMD"
          ."           ,EQU_CODECM"
          ."           ,EQU_CODVCL"          
          ."          ,CASE WHEN A.EQU_REG='P' THEN 'PUB' WHEN A.EQU_REG='S' THEN 'SIS' ELSE 'ADM' END AS EQU_REG"
          ."          ,CASE WHEN A.EQU_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS EQU_ATIVO"
          ."          ,US_APELIDO"
          ."     FROM BKPEQUIPAMENTO A" 
          ."     LEFT OUTER JOIN USUARIOSISTEMA U ON A.EQU_CODUSR=U.US_CODIGO"
          .$lote[0]->where;
        };
        ///////////////////////////////////////////////////
        //  Dados para JavaScript EQUIPAMENTOCOMUNICACAO //
        ///////////////////////////////////////////////////
        if( $rotina=="selectBkpEcm" ){
          $sql="SELECT ECM_ID"
          ."           ,CAST(ECM_DATA AS VARCHAR(10)) AS ECM_DATA"
          ."           ,CASE WHEN A.ECM_ACAO='I' THEN 'INC'" 
          ."                 WHEN A.ECM_ACAO='A' THEN 'ALT'" 
          ."                 WHEN A.ECM_ACAO='E' THEN 'EXC' END AS ECM_ACAO"
          ."          ,ECM_CODIGO"
          ."          ,ECM_NOME"
          ."          ,CASE WHEN A.ECM_REG='P' THEN 'PUB' WHEN A.ECM_REG='S' THEN 'SIS' ELSE 'ADM' END AS ECM_REG"
          ."          ,CASE WHEN A.ECM_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS ECM_ATIVO"
          ."          ,US_APELIDO"
          ."     FROM BKPEQUIPAMENTOCOMUNICACAO A" 
          ."     LEFT OUTER JOIN USUARIOSISTEMA U ON A.ECM_CODUSR=U.US_CODIGO"
          .$lote[0]->where;
        };
        ////////////////////////////////////////////////
        //  Dados para JavaScript EQUIPAMENTOMODELO   //
        ////////////////////////////////////////////////
        if( $rotina=="selectBkpEmd" ){
          $sql="SELECT EMD_ID"
          ."           ,CAST(EMD_DATA AS VARCHAR(10)) AS EMD_DATA"
          ."           ,CASE WHEN A.EMD_ACAO='I' THEN 'INC'" 
          ."                 WHEN A.EMD_ACAO='A' THEN 'ALT'" 
          ."                 WHEN A.EMD_ACAO='E' THEN 'EXC' END AS EMD_ACAO"
          ."          ,EMD_CODIGO"
          ."          ,EMD_NOME"
          ."          ,CASE WHEN A.EMD_REG='P' THEN 'PUB' WHEN A.EMD_REG='S' THEN 'SIS' ELSE 'ADM' END AS EMD_REG"
          ."          ,CASE WHEN A.EMD_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS EMD_ATIVO"
          ."          ,US_APELIDO"
          ."     FROM BKPEQUIPAMENTOMODELO A" 
          ."     LEFT OUTER JOIN USUARIOSISTEMA U ON A.EMD_CODUSR=U.US_CODIGO"
          .$lote[0]->where;
        };
        //////////////////////////////////////////////////
        // Dados para JavaScript EQUIPAMENTOTECNOLOGIA  //
        //////////////////////////////////////////////////
        if( $rotina=="selectBkpEtc" ){
          $sql="SELECT ETC_ID"
          ."           ,CAST(ETC_DATA AS VARCHAR(10)) AS ETC_DATA"
          ."           ,CASE WHEN A.ETC_ACAO='I' THEN 'INC'" 
          ."                 WHEN A.ETC_ACAO='A' THEN 'ALT'" 
          ."                 WHEN A.ETC_ACAO='E' THEN 'EXC' END AS ETC_ACAO"
          ."          ,ETC_CODIGO"
          ."          ,ETC_NOME"
          ."          ,CASE WHEN A.ETC_REG='P' THEN 'PUB' WHEN A.ETC_REG='S' THEN 'SIS' ELSE 'ADM' END AS ETC_REG"
          ."          ,CASE WHEN A.ETC_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS ETC_ATIVO"
          ."          ,US_APELIDO"
          ."     FROM BKPEQUIPAMENTOTECNOLOGIA A" 
          ."     LEFT OUTER JOIN USUARIOSISTEMA U ON A.ETC_CODUSR=U.US_CODIGO"
          .$lote[0]->where;
        };
        ////////////////////////////////////////////////
        //  Dados para JavaScript EQUIPAMENTOTIPO     //
        ////////////////////////////////////////////////
        if( $rotina=="selectBkpEtp" ){
          $sql="SELECT ETP_ID"
          ."           ,CAST(ETP_DATA AS VARCHAR(10)) AS ETP_DATA"
          ."           ,CASE WHEN A.ETP_ACAO='I' THEN 'INC'" 
          ."                 WHEN A.ETP_ACAO='A' THEN 'ALT'" 
          ."                 WHEN A.ETP_ACAO='E' THEN 'EXC' END AS ETP_ACAO"
          ."          ,ETP_CODIGO"
          ."          ,ETP_NOME"
          ."          ,CASE WHEN A.ETP_REG='P' THEN 'PUB' WHEN A.ETP_REG='S' THEN 'SIS' ELSE 'ADM' END AS ETP_REG"
          ."          ,CASE WHEN A.ETP_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS ETP_ATIVO"
          ."          ,US_APELIDO"
          ."     FROM BKPEQUIPAMENTOTIPO A" 
          ."     LEFT OUTER JOIN USUARIOSISTEMA U ON A.ETP_CODUSR=U.US_CODIGO"
          .$lote[0]->where;
        };
        ////////////////////////////////////////////////
        //        Dados para JavaScript ESCOLTA       //
        ////////////////////////////////////////////////
        if( $rotina=="selectBkpEsc" ){
          $sql="SELECT ESC_ID"
          ."           ,CAST(ESC_DATA AS VARCHAR(10)) AS ESC_DATA"
          ."           ,CASE WHEN A.ESC_ACAO='I' THEN 'INC'" 
          ."                 WHEN A.ESC_ACAO='A' THEN 'ALT'" 
          ."                 WHEN A.ESC_ACAO='E' THEN 'EXC' END AS ESC_ACAO"
          ."           ,A.ESC_CODIGO"
          ."           ,A.ESC_NOME"
          ."           ,A.ESC_APELIDO"
          ."           ,A.ESC_CNPJCPF"
          ."           ,A.ESC_FONE"
          ."           ,A.ESC_EMAIL"
          ."          ,CASE WHEN A.ESC_REG='P' THEN 'PUB' WHEN A.ESC_REG='S' THEN 'SIS' ELSE 'ADM' END AS ESC_REG"
          ."          ,CASE WHEN A.ESC_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS ESC_ATIVO"
          ."          ,US_APELIDO"
          ."     FROM BKPESCOLTA A" 
          ."     LEFT OUTER JOIN USUARIOSISTEMA U ON A.ESC_CODUSR=U.US_CODIGO"
          .$lote[0]->where;
        };
        /////////////////////////////////////////
        //     Dados para JavaScript ESTADO    //
        /////////////////////////////////////////
        if( $rotina=="selectBkpEst" ){
          $sql="SELECT EST_ID"
          ."           ,CAST(EST_DATA AS VARCHAR(10)) AS EST_DATA"
          ."           ,CASE WHEN A.EST_ACAO='I' THEN 'INC'" 
          ."                 WHEN A.EST_ACAO='A' THEN 'ALT'" 
          ."                 WHEN A.EST_ACAO='E' THEN 'EXC' END AS EST_ACAO"
          ."          ,EST_CODIGO"
          ."          ,EST_NOME"
          ."          ,EST_CODREG"          
          ."          ,CASE WHEN A.EST_REG='P' THEN 'PUB' WHEN A.EST_REG='S' THEN 'SIS' ELSE 'ADM' END AS EST_REG"
          ."          ,CASE WHEN A.EST_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS EST_ATIVO"
          ."          ,US_APELIDO"
          ."     FROM BKPESTADO A" 
          ."     LEFT OUTER JOIN USUARIOSISTEMA U ON A.EST_CODUSR=U.US_CODIGO"
          .$lote[0]->where;
        };
        ////////////////////////////////////////////////
        //        Dados para JavaScript OPERACAO      //
        ////////////////////////////////////////////////
        if( $rotina=="selectBkpOpe" ){
          $sql="SELECT OPE_ID"
          ."           ,CAST(OPE_DATA AS VARCHAR(10)) AS OPE_DATA"
          ."           ,CASE WHEN A.OPE_ACAO='I' THEN 'INC'" 
          ."                 WHEN A.OPE_ACAO='A' THEN 'ALT'" 
          ."                 WHEN A.OPE_ACAO='E' THEN 'EXC' END AS OPE_ACAO"
          ."           ,A.OPE_CODIGO"
          ."           ,A.OPE_NOME"
          ."           ,A.OPE_APELIDO"
          ."           ,A.OPE_CODCLN"          
          ."           ,CASE WHEN A.OPE_FISJUR='F' THEN CAST('FIS' AS VARCHAR(3)) ELSE CAST('JUR' AS VARCHAR(3)) END AS OPE_FISJUR"
          ."           ,A.OPE_CNPJCPF"
          ."           ,A.OPE_CODCDD"
          ."           ,A.OPE_CEP"
          ."           ,A.OPE_CODLGR"
          ."           ,A.OPE_ENDERECO"
          ."           ,A.OPE_NUMERO"
          ."           ,A.OPE_COMPLEMENTO"
          ."           ,A.OPE_BAIRRO"
          ."           ,A.OPE_FONE"
          ."           ,A.OPE_EMAIL"
          ."           ,A.OPE_SITE"
          ."          ,CASE WHEN A.OPE_REG='P' THEN 'PUB' WHEN A.OPE_REG='S' THEN 'SIS' ELSE 'ADM' END AS OPE_REG"
          ."          ,CASE WHEN A.OPE_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS OPE_ATIVO"
          ."          ,US_APELIDO"
          ."     FROM BKPOPERACAO A" 
          ."     LEFT OUTER JOIN USUARIOSISTEMA U ON A.OPE_CODUSR=U.US_CODIGO"
          .$lote[0]->where;
        };
        /////////////////////////////////////////
        //    Dados para JavaScript LOGRADOURO //
        /////////////////////////////////////////
        if( $rotina=="selectBkpLgr" ){
          $sql="SELECT LGR_ID"
          ."           ,CAST(LGR_DATA AS VARCHAR(10)) AS LGR_DATA"
          ."           ,CASE WHEN A.LGR_ACAO='I' THEN 'INC'" 
          ."                 WHEN A.LGR_ACAO='A' THEN 'ALT'" 
          ."                 WHEN A.LGR_ACAO='E' THEN 'EXC' END AS LGR_ACAO"
          ."          ,LGR_CODIGO"
          ."          ,LGR_NOME"
          ."          ,CASE WHEN A.LGR_REG='P' THEN 'PUB' WHEN A.LGR_REG='S' THEN 'SIS' ELSE 'ADM' END AS LGR_REG"
          ."          ,CASE WHEN A.LGR_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS LGR_ATIVO"
          ."          ,US_APELIDO"
          ."     FROM BKPLOGRADOURO A" 
          ."     LEFT OUTER JOIN USUARIOSISTEMA U ON A.LGR_CODUSR=U.US_CODIGO"
          .$lote[0]->where;
        };
        ////////////////////////////////////////////////
        //        Dados para JavaScript MERCADORIA    //
        ////////////////////////////////////////////////
        if( $rotina=="selectBkpMrc" ){
          $sql="SELECT MRC_ID"
          ."           ,CAST(MRC_DATA AS VARCHAR(10)) AS MRC_DATA"
          ."           ,CASE WHEN A.MRC_ACAO='I' THEN 'INC'" 
          ."                 WHEN A.MRC_ACAO='A' THEN 'ALT'" 
          ."                 WHEN A.MRC_ACAO='E' THEN 'EXC' END AS MRC_ACAO"
          ."           ,A.MRC_CODIGO"
          ."           ,A.MRC_NOME"
          ."           ,A.MRC_CODRSC"
          ."          ,CASE WHEN A.MRC_REG='P' THEN 'PUB' WHEN A.MRC_REG='S' THEN 'SIS' ELSE 'ADM' END AS MRC_REG"
          ."          ,CASE WHEN A.MRC_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS MRC_ATIVO"
          ."          ,US_APELIDO"
          ."     FROM BKPMERCADORIA A" 
          ."     LEFT OUTER JOIN USUARIOSISTEMA U ON A.MRC_CODUSR=U.US_CODIGO"
          .$lote[0]->where;
        };
        /////////////////////////////////////////
        //    Dados para JavaScript MOTORISTA  //
        /////////////////////////////////////////
        if( $rotina=="selectBkpMtr" ){
          $sql="SELECT MTR_ID"
          ."           ,CAST(MTR_DATA AS VARCHAR(10)) AS MTR_DATA"
          ."           ,CASE WHEN A.MTR_ACAO='I' THEN 'INC'" 
          ."                 WHEN A.MTR_ACAO='A' THEN 'ALT'" 
          ."                 WHEN A.MTR_ACAO='E' THEN 'EXC' END AS MTR_ACAO"
          ."          ,MTR_CODIGO"
          ."          ,MTR_NOME"
          ."          ,MTR_CODOPE"
          ."          ,MTR_CODVC"
          ."          ,MTR_CPF"
          ."          ,MTR_RG"
          ."          ,MTR_CNHNUMERO"
          ."          ,MTR_CODCDD"
          ."          ,MTR_CEP"
          ."          ,MTR_CODLGR"
          ."          ,MTR_ENDERECO"
          ."          ,MTR_NUMERO"
          ."          ,MTR_COMPLEMENTO"
          ."          ,MTR_BAIRRO"
          ."          ,MTR_FONE"
          ."          ,CASE WHEN A.MTR_REG='P' THEN 'PUB' WHEN A.MTR_REG='S' THEN 'SIS' ELSE 'ADM' END AS MTR_REG"
          ."          ,CASE WHEN A.MTR_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS MTR_ATIVO"
          ."          ,US_APELIDO"
          ."     FROM BKPMOTORISTA A" 
          ."     LEFT OUTER JOIN USUARIOSISTEMA U ON A.MTR_CODUSR=U.US_CODIGO"
          .$lote[0]->where;
        };
        //////////////////////////////////////
        //    Dados para JavaScript PAIS    //
        //////////////////////////////////////
        if( $rotina=="selectBkpPai" ){
          $sql="SELECT PAI_ID"
          ."           ,CAST(PAI_DATA AS VARCHAR(10)) AS PAI_DATA"
          ."           ,CASE WHEN A.PAI_ACAO='I' THEN 'INC'" 
          ."                 WHEN A.PAI_ACAO='A' THEN 'ALT'" 
          ."                 WHEN A.PAI_ACAO='E' THEN 'EXC' END AS PAI_ACAO"
          ."          ,PAI_CODIGO"
          ."          ,PAI_NOME"
          ."          ,PAI_DDI"          
          ."          ,CASE WHEN A.PAI_REG='P' THEN 'PUB' WHEN A.PAI_REG='S' THEN 'SIS' ELSE 'ADM' END AS PAI_REG"
          ."          ,CASE WHEN A.PAI_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS PAI_ATIVO"
          ."          ,US_APELIDO"
          ."     FROM BKPPAIS A" 
          ."     LEFT OUTER JOIN USUARIOSISTEMA U ON A.PAI_CODUSR=U.US_CODIGO"
          .$lote[0]->where;
        };
        /////////////////////////////////////////
        //     Dados para JavaScript REGIAO    //
        /////////////////////////////////////////
        if( $rotina=="selectBkpReg" ){
          $sql="SELECT REG_ID"
          ."           ,CAST(REG_DATA AS VARCHAR(10)) AS REG_DATA"
          ."           ,CASE WHEN A.REG_ACAO='I' THEN 'INC'" 
          ."                 WHEN A.REG_ACAO='A' THEN 'ALT'" 
          ."                 WHEN A.REG_ACAO='E' THEN 'EXC' END AS REG_ACAO"
          ."          ,REG_CODIGO"
          ."          ,REG_NOME"
          ."          ,REG_CODPAI"          
          ."          ,CASE WHEN A.REG_REG='P' THEN 'PUB' WHEN A.REG_REG='S' THEN 'SIS' ELSE 'ADM' END AS REG_REG"
          ."          ,CASE WHEN A.REG_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS REG_ATIVO"
          ."          ,US_APELIDO"
          ."     FROM BKPREGIAO A" 
          ."     LEFT OUTER JOIN USUARIOSISTEMA U ON A.REG_CODUSR=U.US_CODIGO"
          .$lote[0]->where;
        };
        ////////////////////////////////////////////////
        //          Dados para JavaScript RISCO       //
        ////////////////////////////////////////////////
        if( $rotina=="selectBkpRsc" ){
          $sql="SELECT RSC_ID"
          ."           ,CAST(RSC_DATA AS VARCHAR(10)) AS RSC_DATA"
          ."           ,CASE WHEN A.RSC_ACAO='I' THEN 'INC'" 
          ."                 WHEN A.RSC_ACAO='A' THEN 'ALT'" 
          ."                 WHEN A.RSC_ACAO='E' THEN 'EXC' END AS RSC_ACAO"
          ."          ,RSC_CODIGO"
          ."          ,RSC_NOME"
          ."          ,CASE WHEN A.RSC_REG='P' THEN 'PUB' WHEN A.RSC_REG='S' THEN 'SIS' ELSE 'ADM' END AS RSC_REG"
          ."          ,CASE WHEN A.RSC_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS RSC_ATIVO"
          ."          ,US_APELIDO"
          ."     FROM BKPRISCO A" 
          ."     LEFT OUTER JOIN USUARIOSISTEMA U ON A.RSC_CODUSR=U.US_CODIGO"
          .$lote[0]->where;
        };        
        //////////////////////////////////////
        //    Dados para JavaScript ROTA    //
        //////////////////////////////////////
        if( $rotina=="selectBkpRot" ){
          $sql="SELECT ROT_ID"
          ."           ,CAST(ROT_DATA AS VARCHAR(10)) AS ROT_DATA"
          ."           ,CASE WHEN A.ROT_ACAO='I' THEN 'INC'" 
          ."                 WHEN A.ROT_ACAO='A' THEN 'ALT'" 
          ."                 WHEN A.ROT_ACAO='E' THEN 'EXC' END AS ROT_ACAO"
          ."          ,ROT_CODIGO"
          ."          ,ROT_NOME"
          ."          ,ROT_CODCLN"          
          ."          ,CASE WHEN A.ROT_REG='P' THEN 'PUB' WHEN A.ROT_REG='S' THEN 'SIS' ELSE 'ADM' END AS ROT_REG"
          ."          ,CASE WHEN A.ROT_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS ROT_ATIVO"
          ."          ,US_APELIDO"
          ."     FROM BKPROTA A" 
          ."     LEFT OUTER JOIN USUARIOSISTEMA U ON A.ROT_CODUSR=U.US_CODIGO"
          .$lote[0]->where;
        };
        ////////////////////////////////////////////////
        //  Dados para JavaScript ROTATIPOPARADA      //
        ////////////////////////////////////////////////
        if( $rotina=="selectBkpRtp" ){
          $sql="SELECT RTP_ID"
          ."           ,CAST(RTP_DATA AS VARCHAR(10)) AS RTP_DATA"
          ."           ,CASE WHEN A.RTP_ACAO='I' THEN 'INC'" 
          ."                 WHEN A.RTP_ACAO='A' THEN 'ALT'" 
          ."                 WHEN A.RTP_ACAO='E' THEN 'EXC' END AS RTP_ACAO"
          ."          ,RTP_CODIGO"
          ."          ,RTP_NOME"
          ."          ,CASE WHEN A.RTP_REG='P' THEN 'PUB' WHEN A.RTP_REG='S' THEN 'SIS' ELSE 'ADM' END AS RTP_REG"
          ."          ,CASE WHEN A.RTP_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS RTP_ATIVO"
          ."          ,US_APELIDO"
          ."     FROM BKPROTATIPOPARADA A" 
          ."     LEFT OUTER JOIN USUARIOSISTEMA U ON A.RTP_CODUSR=U.US_CODIGO"
          .$lote[0]->where;
        };
        ////////////////////////////////////////////////
        //        Dados para JavaScript SEGURADORA    //
        ////////////////////////////////////////////////
        if( $rotina=="selectBkpSgr" ){
          $sql="SELECT SGR_ID"
          ."           ,CAST(SGR_DATA AS VARCHAR(10)) AS SGR_DATA"
          ."           ,CASE WHEN A.SGR_ACAO='I' THEN 'INC'" 
          ."                 WHEN A.SGR_ACAO='A' THEN 'ALT'" 
          ."                 WHEN A.SGR_ACAO='E' THEN 'EXC' END AS SGR_ACAO"
          ."           ,A.SGR_CODIGO"
          ."           ,A.SGR_NOME"
          ."           ,A.SGR_APELIDO"
          ."           ,A.SGR_CNPJCPF"
          ."           ,A.SGR_FONE"
          ."           ,A.SGR_EMAIL"
          ."          ,CASE WHEN A.SGR_REG='P' THEN 'PUB' WHEN A.SGR_REG='S' THEN 'SIS' ELSE 'ADM' END AS SGR_REG"
          ."          ,CASE WHEN A.SGR_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS SGR_ATIVO"
          ."          ,US_APELIDO"
          ."     FROM BKPSEGURADORA A" 
          ."     LEFT OUTER JOIN USUARIOSISTEMA U ON A.SGR_CODUSR=U.US_CODIGO"
          .$lote[0]->where;
        };
        ////////////////////////////////////////////////
        //  Dados para JavaScript TIPOCLIENTEOPERACAO //
        ////////////////////////////////////////////////
        if( $rotina=="selectBkpTco" ){
          $sql="SELECT TCO_ID"
          ."           ,CAST(TCO_DATA AS VARCHAR(10)) AS TCO_DATA"
          ."           ,CASE WHEN A.TCO_ACAO='I' THEN 'INC'" 
          ."                 WHEN A.TCO_ACAO='A' THEN 'ALT'" 
          ."                 WHEN A.TCO_ACAO='E' THEN 'EXC' END AS TCO_ACAO"
          ."          ,TCO_CODIGO"
          ."          ,TCO_NOME"
          ."          ,CASE WHEN A.TCO_REG='P' THEN 'PUB' WHEN A.TCO_REG='S' THEN 'SIS' ELSE 'ADM' END AS TCO_REG"
          ."          ,CASE WHEN A.TCO_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS TCO_ATIVO"
          ."          ,US_APELIDO"
          ."     FROM BKPTIPOCLIENTEOPERACAO A" 
          ."     LEFT OUTER JOIN USUARIOSISTEMA U ON A.TCO_CODUSR=U.US_CODIGO"
          .$lote[0]->where;
        };
        /////////////////////////////////////////
        //    Dados para JavaScript USUARIO    //
        /////////////////////////////////////////
        if( $rotina=="selectBkpUsr" ){
          $sql="SELECT A.USR_ID"
          ."           ,CAST(A.USR_DATA AS VARCHAR(10)) AS USR_DATA"
          ."           ,CASE WHEN A.USR_ACAO='I' THEN 'INC'" 
          ."                 WHEN A.USR_ACAO='A' THEN 'ALT'" 
          ."                 WHEN A.USR_ACAO='E' THEN 'EXC' END AS UP_ACAO"
          ."           ,A.USR_CODIGO"
          ."           ,A.USR_CPF"
          ."           ,A.USR_APELIDO"
          ."           ,A.USR_CODUP"
          ."           ,A.USR_CODCRG"          
          ."           ,A.USR_EMAIL"
          ."           ,CASE WHEN A.USR_INTERNO='I' THEN CAST('INTERNO' AS VARCHAR(7))" 
          ."                 WHEN A.USR_INTERNO='E' THEN CAST('EXTERNO' AS VARCHAR(7))" 
          ."                 WHEN A.USR_INTERNO='D' THEN CAST('DEDICADO' AS VARCHAR(8)) END AS USR_INTERNO"
          ."           ,CASE WHEN A.USR_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS USR_ATIVO"
          ."           ,CASE WHEN A.USR_REG='P' THEN 'PUB' WHEN A.USR_REG='S' THEN 'SIS' ELSE 'ADM' END AS USR_REG"
          ."           ,U.US_APELIDO"
          ."           ,A.USR_ADMPUB"
          ."     FROM BKPUSUARIO A" 
          ."     LEFT OUTER JOIN USUARIOSISTEMA U ON A.USR_CODUSR=U.US_CODIGO"
          .$lote[0]->where;
        };
        ////////////////////////////////////////////
        // Dados para JavaScript USUARIOOPERACAO  //
        ////////////////////////////////////////////
        if( $rotina=="selectBkpUo" ){
          $sql="SELECT A.UO_ID"
          ."           ,CAST(A.UO_DATA AS VARCHAR(10)) AS UO_DATA"
          ."           ,CASE WHEN A.UO_ACAO='I' THEN 'INC'" 
          ."                 WHEN A.UO_ACAO='A' THEN 'ALT'" 
          ."                 WHEN A.UO_ACAO='E' THEN 'EXC' END AS UO_ACAO"
          ."           ,A.UO_CODUSR"
          ."           ,A.UO_CODOPE"          
          ."           ,CASE WHEN A.UO_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS UO_ATIVO"
          ."           ,CASE WHEN A.UO_REG='P' THEN 'PUB' WHEN A.UO_REG='S' THEN 'SIS' ELSE 'ADM' END AS UO_REG"
          ."           ,U.US_APELIDO"
          ."     FROM BKPUSUARIOOPERACAO A" 
          ."     LEFT OUTER JOIN USUARIOSISTEMA U ON A.SIS_CODUSR=U.US_CODIGO"
          .$lote[0]->where;
        };        
        /////////////////////////////////////////
        // Dados para JavaScript USUARIOPERFIL //
        /////////////////////////////////////////
        if( $rotina=="selectBkpUp" ){
          $sql="SELECT A.UP_ID"
          ."           ,CAST(A.UP_DATA AS VARCHAR(10)) AS UP_DATA"
          ."           ,CASE WHEN A.UP_ACAO='I' THEN 'INC'" 
          ."                 WHEN A.UP_ACAO='A' THEN 'ALT'" 
          ."                 WHEN A.UP_ACAO='E' THEN 'EXC' END AS UP_ACAO"
          ."          ,A.UP_CODIGO"
          ."          ,A.UP_NOME"
          ."          ,A.UP_D01"
          ."          ,A.UP_D02"
          ."          ,A.UP_D03"
          ."          ,A.UP_D04"
          ."          ,A.UP_D05"
          ."          ,A.UP_D06"
          ."          ,A.UP_D07"
          ."          ,A.UP_D08"
          ."          ,A.UP_D09"
          ."          ,A.UP_D10"
          ."          ,A.UP_D11"
          ."          ,A.UP_D12"
          ."          ,A.UP_D13"
          ."          ,A.UP_D14"
          ."          ,A.UP_D15"
          ."          ,A.UP_D16"
          ."          ,A.UP_D17"
          ."          ,A.UP_D18"
          ."          ,A.UP_D19"
          ."          ,A.UP_D20"
          ."          ,A.UP_D21"
          ."          ,A.UP_D22"
          ."          ,A.UP_D23"
          ."          ,A.UP_D24"
          ."          ,A.UP_D25"
          ."          ,A.UP_D26"
          ."          ,A.UP_D27"
          ."          ,A.UP_D28"
          ."          ,A.UP_D29"
          ."          ,A.UP_D30"
          ."          ,A.UP_D31"
          ."          ,A.UP_D32"
          ."          ,A.UP_D33"
          ."          ,A.UP_D34"
          ."          ,A.UP_D35"
          ."          ,A.UP_D36"
          ."          ,A.UP_D37"
          ."          ,A.UP_D38"
          ."          ,A.UP_D39"
          ."          ,A.UP_D40"
          ."          ,CASE WHEN A.UP_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS UP_ATIVO"
          ."          ,CASE WHEN A.UP_REG='P' THEN 'PUB' WHEN A.UP_REG='S' THEN 'SIS' ELSE 'ADM' END AS UP_REG"
          ."          ,U.US_APELIDO"
          ."          ,A.UP_CODUSR"
          ."     FROM BKPUSUARIOPERFIL A" 
          ."     LEFT OUTER JOIN USUARIOSISTEMA U ON A.UP_CODUSR=U.US_CODIGO"
          .$lote[0]->where;
        };
        ////////////////////////////////////////////////
        //       Dados para JavaScript VEICULO        //
        ////////////////////////////////////////////////
        if( $rotina=="selectBkpVcl" ){
          $sql="SELECT VCL_ID"
          ."           ,CAST(VCL_DATA AS VARCHAR(10)) AS VCL_DATA"
          ."           ,CASE WHEN A.VCL_ACAO='I' THEN 'INC'" 
          ."                 WHEN A.VCL_ACAO='A' THEN 'ALT'" 
          ."                 WHEN A.VCL_ACAO='E' THEN 'EXC' END AS VCL_ACAO"
          ."          ,VCL_CODIGO"
          ."          ,VCL_CODOPE"
          ."          ,VCL_CODVC"
          ."          ,VCL_CODVMR"
          ."          ,VCL_CODVMD"
          ."          ,VCL_CODVTP"
          ."          ,VCL_CODVCR"
          ."          ,VCL_EQUPRI"
          ."          ,VCL_EQUSEC"
          ."          ,VCL_EQUTER"
          ."          ,CASE WHEN A.VCL_REG='P' THEN 'PUB' WHEN A.VCL_REG='S' THEN 'SIS' ELSE 'ADM' END AS VCL_REG"
          ."          ,CASE WHEN A.VCL_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS VCL_ATIVO"
          ."          ,US_APELIDO"
          ."     FROM BKPVEICULO A" 
          ."     LEFT OUTER JOIN USUARIOSISTEMA U ON A.VCL_CODUSR=U.US_CODIGO"
          .$lote[0]->where;
        };
        ////////////////////////////////////////////////
        //  Dados para JavaScript VEICULOCARROCERIA   //
        ////////////////////////////////////////////////
        if( $rotina=="selectBkpVcr" ){
          $sql="SELECT VCR_ID"
          ."           ,CAST(VCR_DATA AS VARCHAR(10)) AS VCR_DATA"
          ."           ,CASE WHEN A.VCR_ACAO='I' THEN 'INC'" 
          ."                 WHEN A.VCR_ACAO='A' THEN 'ALT'" 
          ."                 WHEN A.VCR_ACAO='E' THEN 'EXC' END AS VCR_ACAO"
          ."          ,VCR_CODIGO"
          ."          ,VCR_NOME"
          ."          ,CASE WHEN A.VCR_REG='P' THEN 'PUB' WHEN A.VCR_REG='S' THEN 'SIS' ELSE 'ADM' END AS VCR_REG"
          ."          ,CASE WHEN A.VCR_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS VCR_ATIVO"
          ."          ,US_APELIDO"
          ."     FROM BKPVEICULOCARROCERIA A" 
          ."     LEFT OUTER JOIN USUARIOSISTEMA U ON A.VCR_CODUSR=U.US_CODIGO"
          .$lote[0]->where;
        };
        ////////////////////////////////////////////////
        //  Dados para JavaScript VEICULOMARCA        //
        ////////////////////////////////////////////////
        if( $rotina=="selectBkpVmr" ){
          $sql="SELECT VMR_ID"
          ."           ,CAST(VMR_DATA AS VARCHAR(10)) AS VMR_DATA"
          ."           ,CASE WHEN A.VMR_ACAO='I' THEN 'INC'" 
          ."                 WHEN A.VMR_ACAO='A' THEN 'ALT'" 
          ."                 WHEN A.VMR_ACAO='E' THEN 'EXC' END AS VMR_ACAO"
          ."          ,VMR_CODIGO"
          ."          ,VMR_NOME"
          ."          ,CASE WHEN A.VMR_REG='P' THEN 'PUB' WHEN A.VMR_REG='S' THEN 'SIS' ELSE 'ADM' END AS VMR_REG"
          ."          ,CASE WHEN A.VMR_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS VMR_ATIVO"
          ."          ,US_APELIDO"
          ."     FROM BKPVEICULOMARCA A" 
          ."     LEFT OUTER JOIN USUARIOSISTEMA U ON A.VMR_CODUSR=U.US_CODIGO"
          .$lote[0]->where;
        };
        ////////////////////////////////////////////////
        //  Dados para JavaScript VEICULOMODELO       //
        ////////////////////////////////////////////////
        if( $rotina=="selectBkpVmd" ){
          $sql="SELECT VMD_ID"
          ."           ,CAST(VMD_DATA AS VARCHAR(10)) AS VMD_DATA"
          ."           ,CASE WHEN A.VMD_ACAO='I' THEN 'INC'" 
          ."                 WHEN A.VMD_ACAO='A' THEN 'ALT'" 
          ."                 WHEN A.VMD_ACAO='E' THEN 'EXC' END AS VMD_ACAO"
          ."          ,VMD_CODIGO"
          ."          ,VMD_NOME"
          ."          ,VMD_CODVMR"
          ."          ,CASE WHEN A.VMD_REG='P' THEN 'PUB' WHEN A.VMD_REG='S' THEN 'SIS' ELSE 'ADM' END AS VMD_REG"
          ."          ,CASE WHEN A.VMD_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS VMD_ATIVO"
          ."          ,US_APELIDO"
          ."     FROM BKPVEICULOMODELO A" 
          ."     LEFT OUTER JOIN USUARIOSISTEMA U ON A.VMD_CODUSR=U.US_CODIGO"
          .$lote[0]->where;
        };
        ////////////////////////////////////////////////
        //  Dados para JavaScript VEICULOTIPO         //
        ////////////////////////////////////////////////
        if( $rotina=="selectBkpVtp" ){
          $sql="SELECT VTP_ID"
          ."           ,CAST(VTP_DATA AS VARCHAR(10)) AS VTP_DATA"
          ."           ,CASE WHEN A.VTP_ACAO='I' THEN 'INC'" 
          ."                 WHEN A.VTP_ACAO='A' THEN 'ALT'" 
          ."                 WHEN A.VTP_ACAO='E' THEN 'EXC' END AS VTP_ACAO"
          ."          ,VTP_CODIGO"
          ."          ,VTP_NOME"
          ."          ,CASE WHEN A.VTP_REG='P' THEN 'PUB' WHEN A.VTP_REG='S' THEN 'SIS' ELSE 'ADM' END AS VTP_REG"
          ."          ,CASE WHEN A.VTP_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS VTP_ATIVO"
          ."          ,US_APELIDO"
          ."     FROM BKPVEICULOTIPO A" 
          ."     LEFT OUTER JOIN USUARIOSISTEMA U ON A.VTP_CODUSR=U.US_CODIGO"
          .$lote[0]->where;
        };
        ////////////////////////////////////////////////
        //  Dados para JavaScript VINCULOCONTRATUAL   //
        ////////////////////////////////////////////////
        if( $rotina=="selectBkpVc" ){
          $sql="SELECT VC_ID"
          ."           ,CAST(VC_DATA AS VARCHAR(10)) AS VC_DATA"
          ."           ,CASE WHEN A.VC_ACAO='I' THEN 'INC'" 
          ."                 WHEN A.VC_ACAO='A' THEN 'ALT'" 
          ."                 WHEN A.VC_ACAO='E' THEN 'EXC' END AS VC_ACAO"
          ."          ,VC_CODIGO"
          ."          ,VC_NOME"
          ."          ,CASE WHEN A.VC_REG='P' THEN 'PUB' WHEN A.VC_REG='S' THEN 'SIS' ELSE 'ADM' END AS VC_REG"
          ."          ,CASE WHEN A.VC_ATIVO='S' THEN 'SIM' ELSE 'NAO' END AS VC_ATIVO"
          ."          ,US_APELIDO"
          ."     FROM BKPVINCULOCONTRAT A" 
          ."     LEFT OUTER JOIN USUARIOSISTEMA U ON A.VC_CODUSR=U.US_CODIGO"
          .$lote[0]->where;
        };
        */
        if( $sql != "" ){  
          $retCls=$classe->select($sql);
          if( $retCls['retorno'] != "OK" ){
            $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';  
          } else { 
            $retorno='[{"retorno":"OK","dados":'.json_encode($retCls['dados']).',"erro":""}]'; 
          };  
        };
      };
    } catch(Exception $e ){
      $retorno='[{"retorno":"ERR","dados":"","erro":"'.$e.'"}]'; 
    };    
    echo $retorno;
    exit;
  };  
?>
