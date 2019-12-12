<?php
  session_start();
  if( isset($_POST["visaogeral"]) ){
    try{     
      require("classPhp/conectaSqlServer.class.php");
      require("classPhp/validaJson.class.php"); 
      require("classPhp/removeAcento.class.php"); 
      require("classPhp/selectRepetidoTrac.class.php"); 

      function cmp($a, $b) {
       return $a["PERCENTUAL"] < $b["PERCENTUAL"];
      };
      
      $vldr     = new validaJSon();          
      $retorno  = "";
      $retCls   = $vldr->validarJs($_POST["visaogeral"]);
      ///////////////////////////////////////////////////////////////////////
      // Variavel mostra que não foi feito apenas selects mas atualizou BD //
      ///////////////////////////////////////////////////////////////////////
      $atuBd    = false;
      if($retCls["retorno"] != "OK"){
        $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';
        unset($retCls,$vldr);      
      } else {
        $arrUpdt  = []; 
        $jsonObj  = $retCls["dados"];
        $lote     = $jsonObj->lote;
        $rotina   = $lote[0]->rotina;
        $codmes   = $lote[0]->compet;
        $gpo      = "";
        $classe   = new conectaBd();
        $classe->conecta($lote[0]->login);
        ///////////////////////////////////////////////////////////
        //     Buscando apenas os polos que usuario tem direito  //
        ///////////////////////////////////////////////////////////
        if( $rotina=="quaisPolo" ){
          $cSql   = new SelectRepetido();
          $retSql = $cSql->qualSelect("quaisPolo",$lote[0]->login);
          $retorno='[{"retorno":"'.$retSql["retorno"].'","dados":'.$retSql["dados"].',"erro":"'.$retSql["erro"].'"}]';
        };  
        ///////////////////////////////////////////////////////////
        //   Buscando apenas as unidades que usuario tem direito //
        ///////////////////////////////////////////////////////////
        if( $rotina=="quaisUnidade" ){
          $cSql   = new SelectRepetido();
          $retSql = $cSql->qualSelect("quaisUnidade",$lote[0]->login, $lote[0]->poloCodigo);
          $retorno='[{"retorno":"'.$retSql["retorno"].'","dados":'.$retSql["dados"].',"erro":"'.$retSql["erro"].'"}]';
        };  
        //////////////////////////////////////////////////////////////////////
        //   Buscando apenas os grupos operacionais que usuario tem direito //
        //////////////////////////////////////////////////////////////////////
        if( $rotina=="quaisGpo" ){
          $cSql   = new SelectRepetido();
          $retSql = $cSql->qualSelect("quaisGpo",$lote[0]->login, $lote[0]->uniCodigo);
          $retorno='[{"retorno":"'.$retSql["retorno"].'","dados":'.$retSql["dados"].',"erro":"'.$retSql["erro"].'"}]';
        };  
        ////////////////
        // BI CONTAR  //
        ////////////////
        if( $rotina=="biContar" ){
          $sql="";
          if( $lote[0]->qualSelect=="contarKm" ){  
            $cSql = new SelectRepetido();
            $sql  = $cSql->qualSelect("contarKm",$lote[0]->login."|".$lote[0]->coduni."|".$lote[0]->codpol."|".$codmes);
          };
					/*
          if( $lote[0]->qualSelect=="contarMotorista" ){  
            $sql.="SELECT COUNT(A.MTR_CODIGO) AS QTOS";
            $sql.="  FROM MOTORISTA A";
            $sql.="  LEFT OUTER JOIN USUARIOSISTEMA U ON A.MTR_CODUSR=U.US_CODIGO";
            $sql.="  LEFT OUTER JOIN UNIDADE UNI ON A.MTR_CODUNI=UNI.UNI_CODIGO";
            $sql.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON A.MTR_CODUNI=UU.UU_CODUNI AND UU.UU_CODUSR=".$_SESSION['usr_codigo'];
            if( $lote[0]->coduni >0 ){
              $sql.="  WHERE ((MTR_ATIVO='S') AND (A.MTR_CODUNI=".$lote[0]->coduni.") AND (COALESCE(UU.UU_ATIVO,'')='S'))";  
            } elseif($lote[0]->codpol != "*" ){
              $sql.="  WHERE ((MTR_ATIVO='S') AND (UNI.UNI_CODPOL='".$lote[0]->codpol."') AND (COALESCE(UU.UU_ATIVO,'')='S'))";  
            } else {  
              $sql.="  WHERE ((MTR_ATIVO='S') AND (COALESCE(UU.UU_ATIVO,'')='S'))";  
            };
          };
					*/
          if( $lote[0]->qualSelect=="contarMotorista" ){  
            $cSql = new SelectRepetido();
            $sql  = $cSql->qualSelect("contarMotorista",$lote[0]->login."|".$lote[0]->coduni."|".$lote[0]->codpol."|".$codmes);
          };
					/*
          if( $lote[0]->qualSelect=="contarVeiculo" ){  
            $sql.="SELECT COUNT(A.VCL_CODIGO) AS QTOS";
            $sql.="  FROM VEICULO A";
            $sql.="  LEFT OUTER JOIN USUARIOSISTEMA U ON A.VCL_CODUSR=U.US_CODIGO";
            $sql.="  LEFT OUTER JOIN UNIDADE UNI ON A.VCL_CODUNI=UNI.UNI_CODIGO";
            $sql.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON A.VCL_CODUNI=UU.UU_CODUNI AND UU.UU_CODUSR=".$_SESSION['usr_codigo'];
            switch( $lote[0]->levpes ){
              case "LP" : $frota=" AND (A.VCL_FROTA IN('L','P'))" ;break;
              case "L"  : $frota=" AND (A.VCL_FROTA='L')"         ;break;
              case "P"  : $frota=" AND (A.VCL_FROTA='P')"         ;break;
            }  
            if( $lote[0]->coduni >0 ){
              $sql.="  WHERE ((VCL_ATIVO='S') AND (A.VCL_CODUNI=".$lote[0]->coduni.") AND (COALESCE(UU.UU_ATIVO,'')='S') ".$frota.")";  
            } elseif($lote[0]->codpol != "*" ){
              $sql.="  WHERE ((VCL_ATIVO='S') AND (UNI.UNI_CODPOL='".$lote[0]->codpol."') AND (COALESCE(UU.UU_ATIVO,'')='S') ".$frota.")";                
            } else {
              $sql.="  WHERE ((VCL_ATIVO='S') AND (COALESCE(UU.UU_ATIVO,'')='S') ".$frota.")";  
            };
          };
					*/
          if( $lote[0]->qualSelect=="contarVeiculo" ){  
            $cSql = new SelectRepetido();
            $sql  = $cSql->qualSelect("contarVeiculo",$lote[0]->login."|".$lote[0]->coduni."|".$lote[0]->codpol."|".$codmes);
          };
          if( $lote[0]->qualSelect=="contarHoraRodando" ){  
            $cSql = new SelectRepetido();
            $sql  = $cSql->qualSelect("contarHoraRodando",$lote[0]->login."|".$lote[0]->coduni."|".$lote[0]->codpol."|".$codmes);
          };
          if( $lote[0]->qualSelect=="contarHoraParado" ){  
            $cSql = new SelectRepetido();
            $sql  = $cSql->qualSelect("contarHoraParado",$lote[0]->login."|".$lote[0]->coduni."|".$lote[0]->codpol."|".$codmes);
          };
          if( $lote[0]->qualSelect=="contarPolo" ){  
            $cSql = new SelectRepetido();
            $sql  = $cSql->qualSelect("qtosPolo",$lote[0]->login."|".$lote[0]->codpol);
          };
          
          if( $lote[0]->qualSelect=="contarUnidade" ){  
            $cSql = new SelectRepetido();
            $sql  = $cSql->qualSelect("qtasUnidade",$lote[0]->login."|".$lote[0]->coduni."|".$lote[0]->codpol);
          };
          if( $lote[0]->qualSelect=="contarGpo" ){  
            $cSql = new SelectRepetido();
            $sql  = $cSql->qualSelect("qtosGpo",$lote[0]->login."|".$lote[0]->coduni."|".$lote[0]->codgpo);
          };
          $classe->msgSelect(false);
          $retCls=$classe->select($sql);
          if( $retCls['retorno'] != "OK" ){
            $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';  
          } else { 
            $retorno='[{"retorno":"OK","dados":'.json_encode($retCls["dados"]).',"erro":""}]';
          }  
        };    
        ////////////////
        // BI SIMPLES //
        ////////////////
        if( $rotina=="biSimples" ){
          $sql="";

          if( $lote[0]->codgpo != '*' ) {
            $gpo = " AND (A.VCL_CODGPO=".$lote[0]->codgpo.")";
          }

          if( $lote[0]->qualSelect=="bisUniKm" ){
            $sql.="SELECT UNI.UNI_APELIDO AS NOME,SUM(A.BIKMM_TOTAL) AS QTOS";
            $sql.="  FROM BI_KILOMETROMES A";
            $sql.="  LEFT OUTER JOIN UNIDADE UNI ON A.BIKMM_CODUNI=UNI.UNI_CODIGO";
            $sql.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON A.BIKMM_CODUNI=UU.UU_CODUNI AND UU.UU_CODUSR=".$_SESSION['usr_codigo'];
            if( $lote[0]->coduni >0 ){
              $sql.="  WHERE ((A.BIKMM_ANOMES=".$codmes.") AND (A.BIKMM_CODUNI=".$lote[0]->coduni.") AND (COALESCE(UU.UU_ATIVO,'')='S'))";
            } elseif($lote[0]->codpol != "*" ){              
              $sql.="  WHERE ((A.BIKMM_ANOMES=".$codmes.") AND (UNI.UNI_CODPOL='".$lote[0]->codpol."') AND (COALESCE(UU.UU_ATIVO,'')='S'))";
            } else {
              $sql.="  WHERE ((A.BIKMM_ANOMES=".$codmes.") AND (COALESCE(UU.UU_ATIVO,'')='S'))";
            };
            $sql.="  GROUP BY UNI.UNI_APELIDO"; 
          };
          if( $lote[0]->qualSelect=="bisPolKm" ){
            $sql.="SELECT UNI.UNI_CODPOL AS NOME,SUM(A.BIKMM_TOTAL) AS QTOS";
            $sql.="  FROM BI_KILOMETROMES A";
            $sql.="  LEFT OUTER JOIN UNIDADE UNI ON A.BIKMM_CODUNI=UNI.UNI_CODIGO";
            $sql.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON A.BIKMM_CODUNI=UU.UU_CODUNI AND UU.UU_CODUSR=".$_SESSION['usr_codigo'];
            if( $lote[0]->coduni >0 ){
              $sql.="  WHERE ((A.BIKMM_ANOMES=".$codmes.") AND (A.BIKMM_CODUNI=".$lote[0]->coduni.") AND (COALESCE(UU.UU_ATIVO,'')='S'))";
            } elseif($lote[0]->codpol != "*" ){              
              $sql.="  WHERE ((A.BIKMM_ANOMES=".$codmes.") AND (UNI.UNI_CODPOL='".$lote[0]->codpol."') AND (COALESCE(UU.UU_ATIVO,'')='S'))";
            } else {
              $sql.="  WHERE ((A.BIKMM_ANOMES=".$codmes.") AND (COALESCE(UU.UU_ATIVO,'')='S'))";
            };
            $sql.="  GROUP BY UNI.UNI_CODPOL";              
          };
          if( $lote[0]->qualSelect=="bisMotoristaUni" ){
            $sql.="SELECT UNI.UNI_APELIDO AS NOME,COUNT(A.MTR_CODIGO) AS QTOS";
            $sql.="  FROM MOTORISTA A";
            $sql.="  LEFT OUTER JOIN USUARIOSISTEMA U ON A.MTR_CODUSR=U.US_CODIGO";
            $sql.="  LEFT OUTER JOIN UNIDADE UNI ON A.MTR_CODUNI=UNI.UNI_CODIGO";
            $sql.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON A.MTR_CODUNI=UU.UU_CODUNI AND UU.UU_CODUSR=".$_SESSION['usr_codigo'];
            if( $lote[0]->coduni >0 ){
              $sql.="  WHERE ((A.MTR_ATIVO='S') AND (A.MTR_CODUNI=".$lote[0]->coduni.") AND (COALESCE(UU.UU_ATIVO,'')='S'))";
            } elseif($lote[0]->codpol != "*" ){              
              $sql.="  WHERE ((A.MTR_ATIVO='S') AND (UNI.UNI_CODPOL='".$lote[0]->codpol."') AND (COALESCE(UU.UU_ATIVO,'')='S'))";
            } else {
              $sql.="  WHERE ((MTR_ATIVO='S') AND (COALESCE(UU.UU_ATIVO,'')='S'))";
            };
            $sql.="  GROUP BY UNI.UNI_APELIDO";              
          };
          if( $lote[0]->qualSelect=="bisMotoristaPol" ){
            $sql.="SELECT POL.POL_NOME AS NOME,COUNT(A.MTR_CODIGO) AS QTOS";
            $sql.="  FROM MOTORISTA A";
            $sql.="  LEFT OUTER JOIN USUARIOSISTEMA U ON A.MTR_CODUSR=U.US_CODIGO";
            $sql.="  LEFT OUTER JOIN UNIDADE UNI ON A.MTR_CODUNI=UNI.UNI_CODIGO";
            $sql.="  LEFT OUTER JOIN POLO POL ON UNI.UNI_CODPOL=POL.POL_CODIGO";
            $sql.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON A.MTR_CODUNI=UU.UU_CODUNI AND UU.UU_CODUSR=".$_SESSION['usr_codigo'];
            if( $lote[0]->coduni >0 ){
              $sql.="  WHERE ((A.MTR_ATIVO='S') AND (A.MTR_CODUNI=".$lote[0]->coduni.") AND (COALESCE(UU.UU_ATIVO,'')='S'))";
            } elseif($lote[0]->codpol != "*" ){              
              $sql.="  WHERE ((A.MTR_ATIVO='S') AND (UNI.UNI_CODPOL='".$lote[0]->codpol."') AND (COALESCE(UU.UU_ATIVO,'')='S'))";
            } else {
              $sql.="  WHERE ((MTR_ATIVO='S') AND (COALESCE(UU.UU_ATIVO,'')='S'))";
            };
            $sql.="  GROUP BY POL.POL_NOME";              
          };
          if( $lote[0]->qualSelect=="bisVeiculoUni" ){
            $sql.="SELECT UNI.UNI_APELIDO AS NOME,COUNT(A.VCL_CODIGO) AS QTOS";
            $sql.="  FROM VEICULO A";
            $sql.="  LEFT OUTER JOIN USUARIOSISTEMA U ON A.VCL_CODUSR=U.US_CODIGO";
            $sql.="  LEFT OUTER JOIN UNIDADE UNI ON A.VCL_CODUNI=UNI.UNI_CODIGO";
            $sql.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON A.VCL_CODUNI=UU.UU_CODUNI AND UU.UU_CODUSR=".$_SESSION['usr_codigo'];
            switch( $lote[0]->levpes ){
              case "LP" : $frota=" AND (A.VCL_FROTA IN('L','P'))" ;break;
              case "L"  : $frota=" AND (A.VCL_FROTA='L')"         ;break;
              case "P"  : $frota=" AND (A.VCL_FROTA='P')"         ;break;
            }  
            $sql.=$gpo;
            if( $lote[0]->coduni >0 ){
              $sql.="  WHERE ((A.VCL_ATIVO='S') AND (A.VCL_CODUNI=".$lote[0]->coduni.") AND (COALESCE(UU.UU_ATIVO,'')='S') ".$frota.")";
            } elseif($lote[0]->codpol != "*" ){              
              $sql.="  WHERE ((A.VCL_ATIVO='S') AND (UNI.UNI_CODPOL='".$lote[0]->codpol."') AND (COALESCE(UU.UU_ATIVO,'')='S') ".$frota.")";
            } else {
              $sql.="  WHERE ((VCL_ATIVO='S') AND (COALESCE(UU.UU_ATIVO,'')='S') ".$frota.")";
            };
            $sql.="  GROUP BY UNI.UNI_APELIDO";              
          };       
          if( $lote[0]->qualSelect=="bisVeiculoPol" ){

            $sql.="SELECT POL.POL_NOME AS NOME,COUNT(A.VCL_CODIGO) AS QTOS";
            $sql.="  FROM VEICULO A";
            $sql.="  LEFT OUTER JOIN USUARIOSISTEMA U ON A.VCL_CODUSR=U.US_CODIGO";
            $sql.="  LEFT OUTER JOIN UNIDADE UNI ON A.VCL_CODUNI=UNI.UNI_CODIGO";
            $sql.="  LEFT OUTER JOIN POLO POL ON UNI.UNI_CODPOL=POL.POL_CODIGO";            
            $sql.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON A.VCL_CODUNI=UU.UU_CODUNI AND UU.UU_CODUSR=".$_SESSION['usr_codigo'];
            switch( $lote[0]->levpes ){
              case "LP" : $frota=" AND (A.VCL_FROTA IN('L','P'))" ;break;
              case "L"  : $frota=" AND (A.VCL_FROTA='L')"         ;break;
              case "P"  : $frota=" AND (A.VCL_FROTA='P')"         ;break;
            }
            $sql.=$gpo;
            if( $lote[0]->coduni >0 ){
              $sql.="  WHERE ((A.VCL_ATIVO='S') AND (A.VCL_CODUNI=".$lote[0]->coduni.") AND (COALESCE(UU.UU_ATIVO,'')='S') ".$frota.")";
            } elseif($lote[0]->codpol != "*" ){              
              $sql.="  WHERE ((A.VCL_ATIVO='S') AND (UNI.UNI_CODPOL='".$lote[0]->codpol."') AND (COALESCE(UU.UU_ATIVO,'')='S') ".$frota.")";
            } else {
              $sql.="  WHERE ((VCL_ATIVO='S') AND (COALESCE(UU.UU_ATIVO,'')='S') ".$frota.")";
            };
            $sql.="  GROUP BY POL.POL_NOME";              
          };                 
          $classe->msgSelect(false);
          $retCls=$classe->selectAssoc($sql);
          if( $retCls['retorno'] != "OK" ){
            $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';  
          } else { 
            $tblMtr=$retCls["dados"];
            $tam=count($tblMtr);
            ////////////////////////////////////////////////////
            // Pegando o total de registros para calcular o % //
            ////////////////////////////////////////////////////
            $qtos=0;
            for( $lin=0; $lin<$tam; $lin++ ){
              $qtos+=$tblMtr[$lin]["QTOS"];
            }
            $cor=0;
            $arrJava=[];
            for( $lin=0; $lin<$tam; $lin++ ){
              $pc=( ( $tblMtr[$lin]["QTOS"]*100 ) / $qtos );
              switch( $cor ){
                ////////////////
                // light-blue //
                ////////////////
                case 0 : $colCor="#3c8dbc%"; 
                         $colClass="progress-bar progress-bar-light-blue"; 
                         break;
                ////////////////
                // green      //
                ////////////////
                case 1 : $colCor="#00a65a"; 
                         $colClass="progress-bar progress-bar-green"; 
                         break;
                ////////////////
                // aqua       //
                ////////////////
                case 2 : $colCor="#00c0ef"; 
                         $colClass="progress-bar progress-bar-aqua"; 
                         break;
                ////////////////
                // yellow     //
                ////////////////
                case 3 : $colCor="#f39c12"; 
                         $colClass="progress-bar progress-bar-yellow"; 
                         break;
                ////////////////
                // red        //
                ////////////////
                case 4 : $colCor="#dd4b39"; 
                         $colClass="progress-bar progress-bar-red"; 
                         break;
              }  
              $cor++;
              if( $cor==5 ){
                $cor=0;
              };
              array_push($arrJava,
                [  "ID"         => ($lin+1)
                  ,"NOME"       => $tblMtr[$lin]["NOME"]
                  ,"QTOS"       => $tblMtr[$lin]["QTOS"]
                  ,"PERCENTUAL" => number_format($pc,2)
                  ,"COLCOR"     => $colCor
                  ,"COLCLASS"   => $colClass
                ]);  
            };
            $retorno='[{"retorno":"OK","dados":'.json_encode($arrJava).',"erro":""}]'; 
          };  
        };
        /////////////////
        // BI INFRACAO //
        /////////////////
        if( $rotina=="biInfracao" ){
          switch( $lote[0]->levpes ){
            case "LP" : $frota=" AND (VCL.VCL_FROTA IN('L','P'))" ;break;
            case "L"  : $frota=" AND (VCL.VCL_FROTA='L')"         ;break;
            case "P"  : $frota=" AND (VCL.VCL_FROTA='P')"         ;break;
          };  
          if( $lote[0]->codgpo != '*' ) {
            $gpo = " AND (VCL.VCL_CODGPO=".$lote[0]->codgpo.")";
          }
          //
          $sql="";
          if( $lote[0]->qualSelect=="infracao" ){ 

            $arrAll=[];
            array_push($arrAll,["SIGLA"=>"AB"   ,"NOME"=>"ACELERACAO BRUSCA"    ,"ALIAS"=>"A.BIABM"   ,"TABLE"=>"BI_ACELERBRUSCAMES"  ,"UNION"=>"S"]);
            array_push($arrAll,["SIGLA"=>"CB"   ,"NOME"=>"CONDUCAO BANGUELA"    ,"ALIAS"=>"A.BICBM"   ,"TABLE"=>"BI_CONDUCAOBANGMES"  ,"UNION"=>"S"]);
            array_push($arrAll,["SIGLA"=>"EV"   ,"NOME"=>"EXCESSO VELOC"        ,"ALIAS"=>"A.BIEVM"   ,"TABLE"=>"BI_EXCESSOVELOCMES"  ,"UNION"=>"S"]);
            array_push($arrAll,["SIGLA"=>"EVC"  ,"NOME"=>"EXCESSO VELOC CHUVA"  ,"ALIAS"=>"A.BIEVCM"  ,"TABLE"=>"BI_EXCESSOVELCHMES"  ,"UNION"=>"S"]);
            array_push($arrAll,["SIGLA"=>"FB"   ,"NOME"=>"FREADA BRUSCA"        ,"ALIAS"=>"A.BIFBM"   ,"TABLE"=>"BI_FREADABRUSCAMES"  ,"UNION"=>"S"]);
            array_push($arrAll,["SIGLA"=>"ERPM" ,"NOME"=>"EXCESSO RPM"          ,"ALIAS"=>"A.BIRAM"   ,"TABLE"=>"BI_RPMALTOMES"       ,"UNION"=>"N"]);          
            $qtos=count($arrAll);
            $sql="";            
            for( $lin=0;$lin<$qtos;$lin++ ){
              $alias  = $arrAll[$lin]["ALIAS"];
              $table  = $arrAll[$lin]["TABLE"];
              $sigla  = $arrAll[$lin]["SIGLA"];
              $nome   = $arrAll[$lin]["NOME"];
              
              $sql.=" SELECT '".$sigla."' AS ID";
              $sql.="        ,'".$nome."' AS NOME";
              $sql.="        ,UNI.UNI_APELIDO AS UNIDADE";
              $sql.="        ,UNI.UNI_CODPOL AS POLO";
              $sql.="        ,COALESCE(SUM(".$alias."_TOTAL),0) AS QTOS";
              $sql.="  FROM ".$table." A";
              $sql.="  LEFT OUTER JOIN UNIDADE UNI ON ".$alias."_CODUNI=UNI.UNI_CODIGO";
              $sql.="  LEFT OUTER JOIN VEICULO VCL ON ".$alias."_CODVCL=VCL.VCL_CODIGO";
              $sql.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON ".$alias."_CODUNI=UU.UU_CODUNI AND UU.UU_CODUSR=".$_SESSION['usr_codigo'];
              $sql.="  WHERE (".$alias."_ANOMES=".$codmes.")";
              $sql.="    AND (COALESCE(UU.UU_ATIVO,'')='S')";
              $sql.=$frota;
              $sql.=$gpo; 
              if( $lote[0]->coduni >0 ){
                $sql.="    AND (".$alias."_CODUNI=".$lote[0]->coduni.")";  
              };
              if( $lote[0]->codpol != "*" ){
                $sql.="    AND (UNI.UNI_CODPOL='".$lote[0]->codpol."')";
              }; 
              $sql.=" GROUP BY UNI.UNI_APELIDO,UNI.UNI_CODPOL ";
              if($arrAll[$lin]["UNION"]=="S" )
                $sql.=" UNION ALL ";
            };
          };
          
          $classe->msgSelect(false);
          $retCls=$classe->selectAssoc($sql);
          if( $retCls['retorno'] != "OK" ){
            $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';  
          } else { 
            //////////////////////////////////////////////////////////////////
            // Quebrando as infracoes em 3 retornos devido tempo/velocidade //
            // tblInfN - Pelo nome da infracao                              //
            // tblInfU - Pela unidade                                       //
            // tblInfP - Pelo polo                                          //
            //////////////////////////////////////////////////////////////////
            $tblInfN=[];
            $tblInfU=[];
            $tblInfP=[];
            //////////////////////////////////////////////////////////
            // tblAll vem todo select neste formato                 //
            // ID   NOME                  UNIDADE     POLO   QTOS   //
            // ERPM	EXCESSO RPM	          LEME        LEM   45361   //
            // ERPM	EXCESSO RPM	          LAGOA       LPT   87850   //
            // ERPM	EXCESSO RPM	          BRILHANTE   MS    27059   //
            // AB	  ACELERACAO BRUSCA	    MARACAJU    MS      750   //
            // AB	  ACELERACAO BRUSCA	    PASSATEMPO  MS     3655   //
            // EVC	EXCESSO VELOC CHUVA	  BRILHANTE   MS      451   //
            // EVC	EXCESSO VELOC CHUVA	  MARACAJU    MS        5   //
            //////////////////////////////////////////////////////////
            $tblAll = [];
            $tblAll = $retCls["dados"];
            $tam    = count($tblAll);
            ////////////////////////////////////////////////////////////////////////////
            // Somando a quantidade de infracoes por array para uma conferencia exata //
            ////////////////////////////////////////////////////////////////////////////
            $qtosN=0;
            $qtosU=0;
            $qtosP=0;
            /////////////////////////////
            // Enchendo os tres arrays //
            /////////////////////////////  
            for( $lin=0; $lin<$tam; $lin++ ){
              //////////////////////
              //     tblInfN      //
              //////////////////////  
              $achei=false;
              foreach( $tblInfN as &$linN ){
                if( $tblAll[$lin]["ID"]==$linN["SIGLA"] ){
                  $linN["QTOS"] +=  $tblAll[$lin]["QTOS"];
                  $qtosN        +=  $tblAll[$lin]["QTOS"];
                  $achei=true;
                  break;
                };
              };  
              if( $achei==false ){
                $qtosN+=$tblAll[$lin]["QTOS"];
                array_push($tblInfN,[
                  "SIGLA"       =>  $tblAll[$lin]["ID"]
                  ,"NOME"       =>  $tblAll[$lin]["NOME"]
                  ,"QTOS"       =>  $tblAll[$lin]["QTOS"]
                  ,"PERCENTUAL" => 0                  
                  ,"COLCOR"     => "*"
                  ,"COLCLASS"   => "*"
                  ,"GRAFICO"    => (($tblAll[$lin]["ID"]   == "EV")  ? "S" : 
                                    (($tblAll[$lin]["ID"] == "EVC") ? "S" : 
                                    (($tblAll[$lin]["ID"] == "FB")  ? "S" : "N")))
                ]);
              };
              //////////////////////
              //     tblInfU      //
              ////////////////////// 
              $achei=false;
              foreach( $tblInfU as &$linU ){
                if( $tblAll[$lin]["UNIDADE"]==$linU["SIGLA"] ){
                  $linU["QTOS"] +=  $tblAll[$lin]["QTOS"];
                  $qtosU        +=  $tblAll[$lin]["QTOS"];
                  $achei=true;
                  break;
                };
              };  
              if( $achei==false ){
                $qtosU+=$tblAll[$lin]["QTOS"];
                array_push($tblInfU,[
                  "SIGLA"       =>  $tblAll[$lin]["UNIDADE"]
                  ,"NOME"       =>  $tblAll[$lin]["UNIDADE"]
                  ,"QTOS"       =>  $tblAll[$lin]["QTOS"]
                  ,"PERCENTUAL" => 0
                  ,"COLCOR"     => "*"
                  ,"COLCLASS"   => "*"
                  ,"GRAFICO"    => "N"
                ]);
              };
              //////////////////////
              //     tblInfP      //
              //////////////////////  
              $achei=false;
              foreach( $tblInfP as &$linP ){
                if( $tblAll[$lin]["POLO"]==$linP["SIGLA"] ){
                  $linP["QTOS"] +=  $tblAll[$lin]["QTOS"];
                  $qtosP        +=  $tblAll[$lin]["QTOS"];
                  $achei=true;
                  break;
                };
              };  
              if( $achei==false ){
                $qtosP+=$tblAll[$lin]["QTOS"];
                array_push($tblInfP,[
                  "SIGLA"       =>  $tblAll[$lin]["POLO"]
                  ,"NOME"       =>  $tblAll[$lin]["POLO"]
                  ,"QTOS"       =>  $tblAll[$lin]["QTOS"]
                  ,"PERCENTUAL" => 0
                  ,"COLCOR"     => "*"
                  ,"COLCLASS"   => "*"
                  ,"GRAFICO"    => "N"
                ]);
              };
            };
            unset($linN,$linU,$linP,$tblAll);
            //////////////////////////////////////////////////////////////////////
            // Calculando o percentual e atribuido cor e classe para cada linha //
            //////////////////////////////////////////////////////////////////////
            $arrCorClass=[];
            array_push($arrCorClass,["COR"=>"#3c8dbc%"  ,"CLASSE"=>"progress-bar progress-bar-light-blue"]);
            array_push($arrCorClass,["COR"=>"#00a65a"   ,"CLASSE"=>"progress-bar progress-bar-green"]);
            array_push($arrCorClass,["COR"=>"#00c0ef"   ,"CLASSE"=>"progress-bar progress-bar-aqua"]);
            array_push($arrCorClass,["COR"=>"#f39c12"   ,"CLASSE"=>"progress-bar progress-bar-yellow"]);
            array_push($arrCorClass,["COR"=>"#dd4b39"   ,"CLASSE"=>"progress-bar progress-bar-red"]);
            //////////
            // Nome //
            //////////
            $cor  = 0;
            $pc   = 0;
            foreach( $tblInfN as &$linN ){
              $pc=( ( $linN["QTOS"]*100 ) / $qtosN );
              $linN["PERCENTUAL"] = number_format($pc,0);
              $linN["COLCOR"]     = $arrCorClass[$cor]["COR"];
              $linN["COLCLASS"]   = $arrCorClass[$cor]["CLASSE"];
              $cor=($cor+1);
              if( $cor==5 ){
                $cor=0;
              };
            };
            /////////////
            // UNIDADE //
            /////////////
            $cor  = 0;
            $pc   = 0;
            foreach( $tblInfU as &$linU ){
              $pc=( ( $linU["QTOS"]*100 ) / $qtosU );
              $linU["PERCENTUAL"] = number_format($pc,0);
              $linU["COLCOR"]     = $arrCorClass[$cor]["COR"];
              $linU["COLCLASS"]   = $arrCorClass[$cor]["CLASSE"];
              $cor=($cor+1);
              if( $cor==5 ){
                $cor=0;
              };
            };
            /////////////
            // POLO    //
            /////////////
            $cor  = 0;
            $pc   = 0;
            foreach( $tblInfP as &$linP ){
              $pc=( ( $linP["QTOS"]*100 ) / $qtosP );
              $linP["PERCENTUAL"] = number_format($pc,0);
              $linP["COLCOR"]     = $arrCorClass[$cor]["COR"];
              $linP["COLCLASS"]   = $arrCorClass[$cor]["CLASSE"];
              $cor=($cor+1);
              if( $cor==5 ){
                $cor=0;
              };
            };
            
            usort($tblInfN,"cmp");
            usort($tblInfU,"cmp");
            usort($tblInfP,"cmp");
            
            $retorno='[{"retorno":"OK"
              ,"tblN":'.json_encode($tblInfN).'
              ,"tblU":'.json_encode($tblInfU).'
              ,"tblP":'.json_encode($tblInfP).'
              ,"erro":""}]';             
            unset($tblN,$linN,$tblU,$linU,$tblP,$linP);
            
            
            /*
            //file_put_contents("aaa.xml",$qtosN." ".$qtosU." ".$qtosP);            
            $tblMtr=$tblInfN;
            $tam=count($tblMtr);
            ////////////////////////////////////////////////////
            // Pegando o total de registros para calcular o % //
            ////////////////////////////////////////////////////
            $qtos=0;
            for( $lin=0; $lin<$tam; $lin++ ){
              $qtos+=$tblMtr[$lin]["QTOS"];
            }
            $cor=0;
            $arrJava=[];
            for( $lin=0; $lin<$tam; $lin++ ){
              if( $qtos>0 ){
                $pc=( ( $tblMtr[$lin]["QTOS"]*100 ) / $qtos );
              } else {
                $pc=0;
              }  
              switch( $cor ){
                ////////////////
                // light-blue //
                ////////////////
                case 0 : $colCor="#3c8dbc%"; 
                         $colClass="progress-bar progress-bar-light-blue"; 
                         break;
                ////////////////
                // green      //
                ////////////////
                case 1 : $colCor="#00a65a"; 
                         $colClass="progress-bar progress-bar-green"; 
                         break;
                ////////////////
                // aqua       //
                ////////////////
                case 2 : $colCor="#00c0ef"; 
                         $colClass="progress-bar progress-bar-aqua"; 
                         break;
                ////////////////
                // yellow     //
                ////////////////
                case 3 : $colCor="#f39c12"; 
                         $colClass="progress-bar progress-bar-yellow"; 
                         break;
                ////////////////
                // red        //
                ////////////////
                case 4 : $colCor="#dd4b39"; 
                         $colClass="progress-bar progress-bar-red"; 
                         break;
              }  
              $cor++;
              if( $cor==5 ){
                $cor=0;
              };
              array_push($arrJava,
                [  "SIGLA"      => $tblMtr[$lin]["SIGLA"]
                  ,"NOME"       => $tblMtr[$lin]["NOME"]
                  ,"QTOS"       => $tblMtr[$lin]["QTOS"]
                  ,"PERCENTUAL" => number_format($pc,0)
                  ,"COLCOR"     => $colCor
                  ,"COLCLASS"   => $colClass
                  ,"GRAFICO"    => $tblMtr[$lin]["GRAFICO"]
                ]); 
            };
            $retorno='[{"retorno":"OK","dados":'.json_encode($arrJava).',"erro":""}]'; 
            */
          };  
        };
        ///////////////////////////////////////////////////////////////////
        // Atualizando o banco de dados se opcao de insert/updade/delete //
        ///////////////////////////////////////////////////////////////////
        if( $atuBd ){
          if( count($arrUpdt) >0 ){
            $retCls=$classe->cmd($arrUpdt);
            if( $retCls['retorno']=="OK" ){
              $retorno='[{"retorno":"OK","dados":'.json_encode($data).',"erro":"'.count($arrUpdt).' REGISTRO(s) ATUALIZADO(s)!"}]'; 
            } else {
              $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';  
            };  
          } else {
            $retorno='[{"retorno":"OK","dados":"","erro":"NENHUM REGISTRO CADASTRADO!"}]';
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
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Connect Plus | Total Trac</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    
    <link rel="stylesheet" href="adminLTE/bootstrap.css">
    <link rel="stylesheet" href="adminLTE/font-awesome.css">
    <link rel="stylesheet" href="adminLTE/ionicons.css">
    <link rel="stylesheet" href="adminLTE/AdminLTE.css">
    <link rel="stylesheet" href="adminLTE/all-skins.css">
    <script src="js/js2017.js"></script>
    <link rel="stylesheet" href="css/iframeBi.css">
    <script language="javascript" type="text/javascript"></script>
    <style>
      .btn-label {
        background-color: #3c8dbc;
        border-color: #367fa9;
        color:white;
      }
      .btn-label:hover{
        color:white;
      }
    </style>
    <script>
      "use strict";
      document.addEventListener("DOMContentLoaded", function(){
				// comboCompetencia("YYYYMM_MMM/YY",document.getElementById("cbCompetencia"));
        //buscarCompetencia();
        buscarUni();
        buscarPol();
        buscarGpo();
        iniciarBi(0,"Todas unidades","*","Todos polos", '*', 'Todos Grupos Operacionais');
      });  
      var clsJs;          // Classe responsavel por montar um Json e eviar PHP
      var clsErro;        // Classe para erros            
      var fd;             // Formulario para envio de dados para o PHP
      var msg;            // Variavel para guardadar mensagens de retorno/erro 
      var tam             // Para tamanho de arrays
      var retPhp          // Retorno do Php para a rotina chamadora
      var contMsg   = 0;  // contador para mensagens
      var jsPub     = JSON.parse(localStorage.getItem("lsPublico"));
      var pubCodUni = 0; 
      var pubDesUni = ""; 
      var pubCodPol = "*"; 
      var pubDesPol = "";
      let pubCodGpo = "*";
      let pubDesGpo = "";
      var pubLevPes = "LP";   //Buscar por veiculo leve/pesado 
      //////////////////////////////////////
      // Opcoes para grafico              //
      //////////////////////////////////////
      var pieOptions     = {
        segmentShowStroke    : true,              //Boolean - Se devemos mostrar um traço em cada segmento
        segmentStrokeColor   : '#fff',            //String - A cor de cada traço de segmento
        segmentStrokeWidth   : 2,                 //Number - A largura de cada traço de segmento
        percentageInnerCutout: 50,                // Este é 0 para gráficos de pizza  Number - A porcentagem do gráfico que cortamos do meio
        animationSteps       : 100,               //Number - Quantidade de etapas de animação
        animationEasing      : 'easeOutBounce',   //String - Efeito de facilitação de animação
        animateRotate        : true,              //Boolean - Se nós animamos a rotação do Donut
        animateScale         : false,             //Boolean - Se nós animamos escalando o Donut do centro
        responsive           : true,              //Boolean - seja para tornar o gráfico responsivo ao redimensionamento da janela
        maintainAspectRatio  : true,              // Boolean - se deseja manter a relação de aspecto inicial ou não quando responsivo, se definido como falso, ocupará todo o contêiner
        //String - Um modelo de legenda
        legendTemplate       : '<ul class="<%=name.toLowerCase()%>-legend"><% for (var i=0; i<segments.length; i++){%><li><span style="background-color:<%=segments[i].fillColor%>"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>'
      };
      //////////////////////////////////////
      // Criando as variaveis para tables //
      //////////////////////////////////////
      var ceAnc;
      var ceCanvas;
      var ceContext;
      var ceDivF;    
      var ceDivP;    
      var ceImg;    
      var ceLi;
      var ceOpt;
      var ceSpan;
      var ceTable;
      var ceTr;
      var ceTh;
      var ceTd;
      var ceUl;
			var velocMedia=[0,0];	//Usado para calcular velocidade media
      //
      ////////////////////////////////////////////////////////////////////////////
      // Esta function inicia o BI com todas unidades que o usuario tem direito //
      // Tb eh usada qdo selecionado filtro por uma unidade                     //
      ////////////////////////////////////////////////////////////////////////////
      function iniciarBi(ibCodUni,ibDesUni,ibCodPol,ibDesPol, ibCodGpo, ibDesGpo){
        document.getElementById("infracaoCompet").innerHTML="Infrações "+document.getElementById("cbCompetencia").options[document.getElementById("cbCompetencia").selectedIndex].text+" ";
        pubCodUni=ibCodUni;
        pubDesUni=ibDesUni;
        pubCodPol=ibCodPol;
        pubDesPol=ibDesPol;
        pubCodGpo=ibCodGpo;
        pubDesGpo=ibDesGpo;
        pubLevPes=document.getElementById("cbLevePesado").value;
        fncContar("contarMotorista" 	,"qtosMtr"	,pubCodUni,pubCodPol,"*", "*");
        fncContar("contarVeiculo"   	,"qtosVcl"	,pubCodUni,pubCodPol,pubLevPes, "*");
        fncContar("contarPolo"      	,"qtosPol"	,pubCodUni,pubCodPol,"*","*");
        fncContar("contarUnidade"   	,"qtosUni"	,pubCodUni,pubCodPol,"*","*");
        fncContar("contarGpo"   	    ,"qtosGpo"	,pubCodUni,pubCodPol,"*",pubCodGpo);
				fncContar("contarKm"        	,"qtosKm" 	,pubCodUni,pubCodPol,"*","*");
				fncContar("contarHoraRodando"	,"qtosHRod"	,pubCodUni,pubCodPol,"*","*");				
        fncContar("contarHoraParado"	,"qtosHPar"	,pubCodUni,pubCodPol,"*","*");
        if (pubCodPol != '*') {
          buscarPol();
        }
        if (pubCodUni != '*') {
          buscarUni();
          buscarGpo();
        }
        
        fncFiltrarTableSimples("bisMotoristaUni"  ,"tblUniMtr","divUniMtr"    ,"qtosUniMtr"   ,pubCodUni,pubCodPol,"*", pubCodGpo);
        fncFiltrarTableSimples("bisVeiculoUni"    ,"tblPolMtr","divPolMtr"    ,"qtosPolMtr"   ,pubCodUni,pubCodPol,pubLevPes, pubCodGpo);
        //////////////////////////////////////////////////////////////
        // Aqui sao 3 tbls                                          //
        // tblInfN = Infracao pelo nome da infracao                 //
        // tblInfU = As mesmas infracoes mas quebradas por unidade  //
        // tblInfP = As mesmas infracoes mas quebradas por polo     //
        //////////////////////////////////////////////////////////////
        fncFiltrarTableInfracao("infracao"        ,"tblinfN"  ,"divInfracaoN"  ,"qtosInfracaoN" ,pubCodUni,pubCodPol,pubLevPes, pubCodGpo);
        document.getElementById("smllDesUni").innerHTML=ibDesUni;
      };
      //    
      //  
      function fncContar(qualSelect,qualSpan,qualCodUni,qualCodPol,qualLevPes,qualCodGpo){
        clsJs   = jsString("lote");  
        clsJs.add("rotina"      , "biContar"                                      );
        clsJs.add("login"       , jsPub[0].usr_login                              );
        clsJs.add("qualSelect"  , qualSelect                                      );
        clsJs.add("coduni"      , qualCodUni                                      );
        clsJs.add("codpol"      , qualCodPol                                      );
        clsJs.add("levpes"      , qualLevPes                                      );
        clsJs.add("codgpo"      , qualCodGpo                                      );
        clsJs.add("compet"      , document.getElementById("cbCompetencia").value  );      
        fd = new FormData();
        fd.append("visaogeral" , clsJs.fim());
        msg     = requestPedido("Trac_BiVisaoGeral.php",fd); 
//debugger;
        retPhp  = JSON.parse(msg);
        if( retPhp[0].retorno == "OK" ){
          //- 09jul document.getElementById(qualSpan).innerHTML=parseInt(retPhp[0]["dados"][0]);
          //- 09jul if(qualSpan=="qtosKm")
            //- 09jul document.getElementById(qualSpan).innerHTML=parseInt(parseFloat(retPhp[0]["dados"][0]));
					document.getElementById(qualSpan).innerHTML=jsNmrs(parseInt(parseFloat(retPhp[0]["dados"][0]))).dolar().sepMilhar(0).ret();
					//////////////////////////////////////////////
					// Somente para calcular a velocidade media //
					//////////////////////////////////////////////			
					switch (qualSpan) {
						case "qtosKm"		: velocMedia[0]=parseInt(parseFloat(retPhp[0]["dados"][0])); break;
						case "qtosHRod"	: velocMedia[1]=parseInt(parseFloat(retPhp[0]["dados"][0])); break;
					};	
					if( (velocMedia[0]>0) && (velocMedia[1]>0) ){
						document.getElementById("qtosVclMed").innerHTML=jsNmrs((velocMedia[0]/velocMedia[1])).dolar().sepMilhar(4).ret();	
					};	
        };  
      };
      //
      //  
      //////////////////////////////////////
      // Somente tabelas com duas colunas //  
      //////////////////////////////////////
      function fncFiltrarTableSimples(qualSelect,qualTbl,qualDiv,qualTot,qualCodUni,qualCodPol,qualLevPes,qualCodGpo){
        clsJs   = jsString("lote");  
        clsJs.add("rotina"      , "biSimples"                                     );
        clsJs.add("login"       , jsPub[0].usr_login                              );
        clsJs.add("qualSelect"  , qualSelect                                      );
        clsJs.add("coduni"      , qualCodUni                                      );      
        clsJs.add("codpol"      , qualCodPol                                      );
        clsJs.add("codgpo"      , qualCodGpo                                      );
        clsJs.add("levpes"      , qualLevPes                                      );      
        clsJs.add("compet"      , document.getElementById("cbCompetencia").value  );      
        // clsJs.add("grupoOperacional"      , document.getElementById("cbGrupoOperacional").value  );      
        fd = new FormData();
        fd.append("visaogeral" , clsJs.fim());
        msg     = requestPedido("Trac_BiVisaoGeral.php",fd); 

        retPhp  = JSON.parse(msg);
        if( retPhp[0].retorno == "OK" ){
          var arrTitulo = ["DESCRITIVO","GRAFICO","%","QTOS"];
          var arrColW  = ["40%","40%","10%","10%"];
          var qtdCol  = arrColW.length;            // Quantidade de colunas
          var qtdRow  = retPhp[0]["dados"].length; // Quantidade de linhas do retorno select
          var totQtos = 0;                         // Total de qtos
          
          ///////////////////////////////////////////////
          // Se ja existir a table removo devido click //
          ///////////////////////////////////////////////
          if(document.getElementById(qualTbl) != undefined ){
            document.getElementById(qualTbl).remove();
          };
          /////////////////////
          // Criando a table //  
          /////////////////////
          ceTable           = document.createElement("table");
          ceTable.id        = qualTbl;
          ceTable.className = "table table-bordered";
          /////////////////////
          // Criando as th   //  
          /////////////////////
          ceTr = document.createElement("tr");          
          for( var lin=0;lin<qtdCol;lin++ ){
            ceTh = document.createElement("th");
            ceTh.style.width = arrColW[lin];
              ceContext = document.createTextNode( arrTitulo[lin] );
            ceTh.appendChild(ceContext); 
            ceTr.appendChild(ceTh); 
          };
          ceTable.appendChild(ceTr);          
          //
          //
          /////////////////////
          // Criando as tr   //  
          /////////////////////
          ceTr = document.createElement("tr");
          ceTr.style.height="15px";
          for( var linR=0;  linR<qtdRow;  linR++ ){
            ceTr = document.createElement("tr"); 
            for( var linC=0;  linC<qtdCol;  linC++ ){
              switch (linC) {
                ///////////////////////
                // Coluna descritivo //
                ///////////////////////
                case 0: 
                  ceTd = document.createElement("td");
                    ceContext = document.createTextNode( retPhp[0]["dados"][linR]["NOME"] );
                  ceTd.appendChild(ceContext); 
                  ceTr.appendChild(ceTd);         
                  break;
                /////////////////////////////////////
                // Coluna com a barra de progresso //
                /////////////////////////////////////
                case 1: 
                  ceTd = document.createElement("td");
                    ceDivP = document.createElement("div");
                    ceDivP.className = "progress progress-xs";
                    ceDivP.style.height= "15px";
                      ceDivF = document.createElement("div");
                      ceDivF.className = retPhp[0]["dados"][linR]["COLCLASS"];     
                      ceDivF.style.width = retPhp[0]["dados"][linR]["PERCENTUAL"]+"%";
                    ceDivP.appendChild(ceDivF);  
                    ceTd.appendChild(ceDivP);   
                  ceTr.appendChild(ceDivP); 
                  break;
                /////////////////////////////////////
                // Coluna com o percentual         //
                /////////////////////////////////////
                case 2:
                  ceTd = document.createElement("td");
                    ceSpan=document.createElement("span");
                    ceSpan.className ="badge";
                    ceSpan.style.backgroundColor = retPhp[0]["dados"][linR]["COLCOR"];
                    ceSpan.style.width = "90%";
                    ceSpan.style.marginBottom = "20px";
                    ceContext = document.createTextNode( retPhp[0]["dados"][linR]["PERCENTUAL"]+"%" );          
                    ceSpan.appendChild(ceContext);
                    ceTd.appendChild(ceSpan);   
                  ceTr.appendChild(ceTd);   
                  break;
                /////////////////////////////////////
                // Coluna quantidade               //
                /////////////////////////////////////
                case 3:
                  ceTd = document.createElement("td");
                    ceSpan=document.createElement("span");
                    ceSpan.className ="badge";
                    ceSpan.style.backgroundColor = retPhp[0]["dados"][linR]["COLCOR"];
                    ceSpan.style.width = "90%";
                    ceSpan.style.marginBottom = "20px";
                    ceContext = document.createTextNode( retPhp[0]["dados"][linR]["QTOS"] );          
                    ceSpan.appendChild(ceContext);
                    ceTd.appendChild(ceSpan);   
                  ceTr.appendChild(ceTd);   
                  ///////////////////////////////
                  // Totalizando o coluna qtos //
                  ///////////////////////////////
                  totQtos+=retPhp[0]["dados"][linR]["QTOS"];
                  break;
              };    
            };  
            ceTable.appendChild(ceTr);
          };  
          document.getElementById(qualDiv).appendChild(ceTable);
          //////////////////////////////////////////////////////////////////
          // Nao obrigatorio - Totaliza a coluna qtos no descritivo do BI //
          //////////////////////////////////////////////////////////////////
          if(document.getElementById(qualTot) != undefined ){
            document.getElementById(qualTot).innerHTML=totQtos;
          };
        };
      };
      
function criarElemento(elem,attr,app){
    var i;
    elem = (typeof elem === 'object' ? elem : document.createElement(elem));
    /////////////////////////////////////////////////////////
    // Se naum existir atributos retorno apenas o elemento //
    /////////////////////////////////////////////////////////
    if (typeof attr !== 'object'){
        return elem;
    };
    /////////////////////////////////////////////////////////////
    // Se existir atributos retorno apenas o elemento verifico //
    // se eles pertencem ao objeto                             //
    /////////////////////////////////////////////////////////////
    for (i in attr){
      if (attr.hasOwnProperty(i)){
        elem[i] = (typeof attr[i] === 'object' ? criarElemento(elem[i],attr[i]) : attr[i]);
      };
    };

    /////////////////////////////////////////////////////////
    // Opcional se vou querer apendar em um elemento filho //
    /////////////////////////////////////////////////////////
    if( typeof app === "object") {  
      if( app.appendChild != null ){
        app.appendChild(elem);
        return true;
      }  
    };
    return elem;
}
      
      
      
      //
      //  
      //////////////////////////////////////
      // Somente tabelas infracao         //  
      //////////////////////////////////////
      function fncFiltrarTableInfracao(qualSelect,qualTbl,qualDiv,qualTot,qualCodUni,qualCodPol,qualLevPes, qualCodGpo){
        clsJs   = jsString("lote");  
        clsJs.add("rotina"      , "biInfracao"                                    );
        clsJs.add("login"       , jsPub[0].usr_login                              );
        clsJs.add("qualSelect"  , qualSelect                                      );
        clsJs.add("coduni"      , qualCodUni                                      );
        clsJs.add("codpol"      , qualCodPol                                      );
        clsJs.add("codgpo"      , qualCodGpo                                      );
        clsJs.add("levpes"      , qualLevPes                                      );      
        clsJs.add("compet"      , document.getElementById("cbCompetencia").value  );      
        fd = new FormData();
        fd.append("visaogeral" , clsJs.fim());
        msg     = requestPedido("Trac_BiVisaoGeral.php",fd); 
        retPhp  = JSON.parse(msg);
        if( retPhp[0].retorno == "OK" ){
          var arrTitulo = ["ID"   ,"DESCRITIVO","GRAFICO","%"  ,"QTOS"];
          var arrColW   = ["8%"   ,"35%"       ,"37%"    ,"10%","10%"];
          var qtdCol    = arrColW.length;             // Quantidade de colunas
          var qtdRow    = retPhp[0]["tblN"].length;   // Quantidade de linhas do retorno select
          var totQtos   = 0;                          // Total de qtos
          var totGra    = 0;                          // Pegando total para grafico
          ///////////////////////////////////////////////
          // Se ja existir a table removo devido click //
          // Tem mais duas dependentes da tblInfN      //
          ///////////////////////////////////////////////
          if(document.getElementById(qualTbl) != undefined ){
            document.getElementById(qualTbl).remove();
          };
          if(document.getElementById("tblInfU") != undefined ){
            document.getElementById("tblInfU").remove();
          };
          if(document.getElementById("tblInfP") != undefined ){
            document.getElementById("tblInfP").remove();
          };
          /////////////////////
          // Criando a table //  
          /////////////////////
          /*
          ceTable           = document.createElement("table");
          ceTable.id        = qualTbl;
          ceTable.className = "table table-bordered";
          */
          ceTable = criarElemento("table",{id:qualTbl,className:"table table-bordered"},{appendChild:null});          
          /////////////////////
          // Criando as th   //  
          /////////////////////
          //ceTr = document.createElement("tr");          
          ceTr = criarElemento("tr");
          for( var lin=0;lin<qtdCol;lin++ ){
            //ceTh = criarElemento("th",{id:"aa",style:{width:"8%"}});
            ceTh = document.createElement("th");
            ceTh.style.width = arrColW[lin];
              ceContext = document.createTextNode( arrTitulo[lin] );
            ceTh.appendChild(ceContext); 
            ceTr.appendChild(ceTh); 
          };
          ceTable.appendChild(ceTr);          
          //
          //
          /////////////////////
          // Criando as tr   //  
          /////////////////////
          ceTr = document.createElement("tr");
          ceTr.style.height="15px";
          for( var linR=0;  linR<qtdRow;  linR++ ){
            ceTr = document.createElement("tr"); 
            for( var linC=0;  linC<qtdCol;  linC++ ){
              switch (linC) {
                ///////////////////////
                // Coluna sigla //
                ///////////////////////
                case 0:
                  ceTd = document.createElement("td");
                    ceContext = document.createTextNode( retPhp[0]["tblN"][linR]["SIGLA"] );
                  ceTd.appendChild(ceContext); 
                  ceTr.appendChild(ceTd);         
                  break;
                ///////////////////////
                // Coluna descritivo //
                ///////////////////////
                case 1:
                  ceTd = document.createElement("td");
                    ceContext = document.createTextNode( retPhp[0]["tblN"][linR]["NOME"] );
                  ceTd.appendChild(ceContext); 
                  ceTr.appendChild(ceTd);         
                  break;
                /////////////////////////////////////
                // Coluna com a barra de progresso //
                /////////////////////////////////////
                case 2: 
                  ceTd = document.createElement("td");
                    ceDivP = document.createElement("div");
                    ceDivP.className = "progress progress-xs";
                    ceDivP.style.height= "15px";
                      ceDivF = document.createElement("div");
                      ceDivF.className = retPhp[0]["tblN"][linR]["COLCLASS"];     
                      ceDivF.style.width = retPhp[0]["tblN"][linR]["PERCENTUAL"]+"%";
                    ceDivP.appendChild(ceDivF);  
                    ceTd.appendChild(ceDivP);   
                  ceTr.appendChild(ceDivP); 
                  break;
                /////////////////////////////////////
                // Coluna com o percentual         //
                /////////////////////////////////////
                case 3:
                  ceTd = document.createElement("td");
                    ceSpan=document.createElement("span");
                    ceSpan.className ="badge";
                    ceSpan.style.backgroundColor = retPhp[0]["tblN"][linR]["COLCOR"];
                    ceSpan.style.width = "90%";
                    ceSpan.style.marginBottom = "20px";
                    ceContext = document.createTextNode( retPhp[0]["tblN"][linR]["PERCENTUAL"]+"%" );          
                    ceSpan.appendChild(ceContext);
                    ceTd.appendChild(ceSpan);   
                  ceTr.appendChild(ceTd);   
                  break;
                /////////////////////////////////////
                // Coluna quantidade               //
                /////////////////////////////////////
                //akii
                case 4:
                  ceTd = document.createElement("td");
                    ceSpan=document.createElement("span");
                    ceSpan.className ="badge";
                    ceSpan.style.backgroundColor = retPhp[0]["tblN"][linR]["COLCOR"];
                    ceSpan.style.width = "90%";
                    ceSpan.style.marginBottom = "20px";
                    ceContext = document.createTextNode( retPhp[0]["tblN"][linR]["QTOS"] );          
                    ceSpan.appendChild(ceContext);
                    ceTd.appendChild(ceSpan);   
                  ceTr.appendChild(ceTd);   
                  ///////////////////////////////
                  // Totalizando o coluna qtos //
                  ///////////////////////////////
                  totQtos+=retPhp[0]["tblN"][linR]["QTOS"];
                  if( retPhp[0]["tblN"][linR]["GRAFICO"]=="S" ){
                    totGra+=retPhp[0]["tblN"][linR]["QTOS"];
                  }  
                  break;
              };    
            };  
            ceTable.appendChild(ceTr);
          };  
          document.getElementById(qualDiv).appendChild(ceTable);
          //////////////////////////////////////////////////////////////////
          // Nao obrigatorio - Totaliza a coluna qtos no descritivo do BI //
          //////////////////////////////////////////////////////////////////
          if(document.getElementById(qualTot) != undefined ){
            document.getElementById(qualTot).innerHTML=totQtos;
						document.getElementById("qtosInfra").innerHTML=totQtos;						
          };
          
          
          
          
          //////////////////////////////////////////////////
          // Para nao remontar grafico em cima de grafico //
          //////////////////////////////////////////////////
          if(document.getElementById("pieChart") != undefined ){
            document.getElementById("pieChart").remove();
          };  
          ceCanvas              = document.createElement("canvas");
          ceCanvas.id           = "pieChart";
          ceCanvas.style.height ="150px";
          document.getElementById("divPieChart").appendChild(ceCanvas);
          //
          var pieChartCanvas = document.getElementById("pieChart").getContext("2d");
          var pieChart       = new Chart(pieChartCanvas);
          var valor          = 0;
          var tblGra=retPhp[0]["tblN"];
          var arrColor=["#f56954","#00a65a","#f39c12"];
          var iCor=0;
          var arrPieData=[];
          for( var linR=0;  linR<qtdRow;  linR++ ){
            if( tblGra[linR]["GRAFICO"]=="S" ){
              valor=((tblGra[linR]["QTOS"]*100)/totGra);
							valor=jsNmrs(valor).dec(2).dolar().ret();
              arrPieData.push({
                "value":valor//parseInt(valor)
                ,"color":arrColor[iCor]
                ,"highlight":arrColor[iCor]
                ,"label":tblGra[linR]["SIGLA"]
              });  
              iCor++;
            }  
          }  
          // Criar gráfico de torta ou rosquinha
          // Você pode alternar entre torta e rosca usando o método abaixo.
          pieChart.Doughnut(arrPieData, pieOptions)
          //
          //
          //////////////////////////////////////////////////////////
          // A tabela de infracao(tblInfN) gera mais duas tabelas //
          // tblInfU - por unidade                                //
          // tblInfP - por polo                                   //
          //////////////////////////////////////////////////////////
          /////////////////////
          // Criando a table //  
          /////////////////////
          for( var loo=0;loo<2;loo++){ 
            totQtos=0;
            if( loo==0 ){
              var tbl=retPhp[0]["tblU"];
              var qDiv="divInfracaoU"
              var qTot="qtosInfracaoU"
            } else {
              var tbl=retPhp[0]["tblP"];
              var qDiv="divInfracaoP"
              var qTot="qtosInfracaoP"
            };
          
            ceTable           = document.createElement("table");
            ceTable.id        = ( loo==0 ? "tblInfU" : "tblInfP" );
            ceTable.className = "table table-bordered";
            /////////////////////
            // Criando as th   //  
            /////////////////////
            if( loo==0 ){            
              var arrTitulo = ["UNIDADE","GRAFICO","%"    ,"QTOS"];
              var arrColW   = ["30%"    ,"50%"    ,"10%"  ,"10%"];
            } else {
              var arrTitulo = ["POLO"   ,"GRAFICO","%"    ,"QTOS"];
              var arrColW   = ["30%"    ,"50%"    ,"10%"  ,"10%"];
            }  
            var qtdCol    = arrColW.length;             // Quantidade de colunas
            var qtdRow    = tbl.length;   // Quantidade de linhas do retorno select
            
            ceTr = document.createElement("tr");          
            for( var lin=0;lin<qtdCol;lin++ ){
              ceTh = document.createElement("th");
              ceTh.style.width = arrColW[lin];
                ceContext = document.createTextNode( arrTitulo[lin] );
              ceTh.appendChild(ceContext); 
              ceTr.appendChild(ceTh); 
            };
            ceTable.appendChild(ceTr);          
            //
            //
            /////////////////////
            // Criando as tr   //  
            /////////////////////
            ceTr = document.createElement("tr");
            ceTr.style.height="15px";
            for( var linR=0;  linR<qtdRow;  linR++ ){
              ceTr = document.createElement("tr"); 
              for( var linC=0;  linC<qtdCol;  linC++ ){
                switch (linC) {
                  ///////////////////////
                  // Coluna sigla //
                  ///////////////////////
                  case 0:
                    ceTd = document.createElement("td");
                      ceContext = document.createTextNode( tbl[linR]["SIGLA"] );
                    ceTd.appendChild(ceContext); 
                    ceTr.appendChild(ceTd);         
                    break;
                  /////////////////////////////////////
                  // Coluna com a barra de progresso //
                  /////////////////////////////////////
                  case 1: 
                    ceTd = document.createElement("td");
                      ceDivP = document.createElement("div");
                      ceDivP.className = "progress progress-xs";
                      ceDivP.style.height= "15px";
                        ceDivF = document.createElement("div");
                        ceDivF.className = tbl[linR]["COLCLASS"];     
                        ceDivF.style.width = tbl[linR]["PERCENTUAL"]+"%";
                      ceDivP.appendChild(ceDivF);  
                      ceTd.appendChild(ceDivP);   
                    ceTr.appendChild(ceDivP); 
                    break;
                  /////////////////////////////////////
                  // Coluna com o percentual         //
                  /////////////////////////////////////
                  case 2:
                    ceTd = document.createElement("td");
                      ceSpan=document.createElement("span");
                      ceSpan.className ="badge";
                      ceSpan.style.backgroundColor = tbl[linR]["COLCOR"];
                      ceSpan.style.width = "90%";
                      ceSpan.style.marginBottom = "20px";
                      ceContext = document.createTextNode( tbl[linR]["PERCENTUAL"]+"%" );          
                      ceSpan.appendChild(ceContext);
                      ceTd.appendChild(ceSpan);   
                    ceTr.appendChild(ceTd);   
                    break;
                  /////////////////////////////////////
                  // Coluna quantidade               //
                  /////////////////////////////////////
                  //akii
                  case 3:
                    ceTd = document.createElement("td");
                      ceSpan=document.createElement("span");
                      ceSpan.className ="badge";
                      ceSpan.style.backgroundColor = tbl[linR]["COLCOR"];
                      ceSpan.style.width = "90%";
                      ceSpan.style.marginBottom = "20px";
                      ceContext = document.createTextNode( tbl[linR]["QTOS"] );          
                      ceSpan.appendChild(ceContext);
                      ceTd.appendChild(ceSpan);   
                    ceTr.appendChild(ceTd);   
                    ///////////////////////////////
                    // Totalizando o coluna qtos //
                    ///////////////////////////////
                    totQtos+=tbl[linR]["QTOS"];
                    break;
                };    
              };  
              ceTable.appendChild(ceTr);
            };  
            document.getElementById(qDiv).appendChild(ceTable);
            //////////////////////////////////////////////////////////////////
            // Nao obrigatorio - Totaliza a coluna qtos no descritivo do BI //
            //////////////////////////////////////////////////////////////////
            if(document.getElementById(qTot) != undefined ){
              document.getElementById(qTot).innerHTML=totQtos;
            };
          };
        };
      };
      ///////////////////////////
      // Buscando competencias //
      ///////////////////////////
			/*
      function buscarCompetencia(){
        var dias=0;
        for( var lin=0;lin<4;lin++ ){
          ceOpt = document.createElement ("option");
          ceContext = document.createTextNode (jsDatas(dias).retMMMbYY());
          ceOpt.appendChild (ceContext);
          ceOpt.setAttribute ("value", jsDatas(dias).retYYYYMM()    );
          ceOpt.setAttribute ("text", jsDatas(dias).retMMMbYY()     );
          if( lin==0 )
            ceOpt.setAttribute ("selected", true   );  
          document.getElementById("cbCompetencia").appendChild(ceOpt);
          dias=(dias-30);
        }
      };  
			*/
      ///////////////////////////////////////////////////////////////////////////////////////////
      // Buscando apenas as unidades que o usuario tem direito, e se tiver, só as daquele polo //
      ///////////////////////////////////////////////////////////////////////////////////////////
      function buscarGpo(){
        document.getElementById('filtroGpo').innerHTML = '';
        clsJs   = jsString("lote");  
        clsJs.add("rotina"      , "quaisGpo"                                  );
        clsJs.add("login"       , jsPub[0].usr_login                              );
        clsJs.add("uniCodigo"   , pubCodUni                                       );
        clsJs.add("compet"      , document.getElementById("cbCompetencia").value  );      
        fd = new FormData();
        fd.append("visaogeral" , clsJs.fim());
        msg     = requestPedido("Trac_BiVisaoGeral.php",fd); 
        retPhp  = JSON.parse(msg);
        if( retPhp[0].retorno == "OK" ){
          msg=retPhp[0]["dados"].length;  
          
          for( var lin=0;lin<msg;lin++ ){
            ceLi= document.createElement("li"); 
            ceLi.style.height="25px";
              ceAnc= document.createElement("a");
              ceAnc.href="#";
              ceAnc.setAttribute("onclick",
              `iniciarBi('${retPhp[0]["dados"][lin]["UNI_CODIGO"] ? retPhp[0]["dados"][lin]["UNI_CODIGO"] : '*'}',
               '${retPhp[0]["dados"][lin]["UNI_APELIDO"] ? retPhp[0]["dados"][lin]["UNI_APELIDO"] : '*'}',
                          '${pubCodPol}', 'Todos polos', '${retPhp[0]["dados"][lin]["GPO_CODIGO"]}', 'Todos Grupos Operacionais')`);
                ceImg= document.createElement("i");
                ceImg.className="fa fa-object-ungroup text-red";
                ceAnc.appendChild(ceImg);
                
                ceContext = document.createTextNode( " -"+retPhp[0]["dados"][lin]["GPO_NOME"] );  
              ceAnc.appendChild(ceContext);
            ceLi.appendChild(ceAnc);
            document.getElementById("filtroGpo").appendChild(ceLi);
          };    
          ceLi= document.createElement("li"); 
            ceAnc= document.createElement("a");
            ceAnc.href="#";
            ceAnc.setAttribute("onclick",
            `iniciarBi('${pubCodUni}', 'Todas Unidades',
                          '${pubCodPol}', 'Todos polos', '*', 'Todos Grupos Operacionais')`);
              ceImg= document.createElement("i");
              ceImg.className="fa fa-object-ungroup text-red";
              ceAnc.appendChild(ceImg);
              
              ceContext = document.createTextNode( " -TODOS" );  
            ceAnc.appendChild(ceContext);
          ceLi.appendChild(ceAnc);
          document.getElementById("filtroGpo").appendChild(ceLi);
        };
      };
      function buscarUni(){
        document.getElementById('filtroUni').innerHTML = '';
        clsJs   = jsString("lote");  
        clsJs.add("rotina"      , "quaisUnidade"                                  );
        clsJs.add("login"       , jsPub[0].usr_login                              );
        clsJs.add("poloCodigo"  , pubCodPol                                       );
        clsJs.add("compet"      , document.getElementById("cbCompetencia").value  );      
        fd = new FormData();
        fd.append("visaogeral" , clsJs.fim());
        msg     = requestPedido("Trac_BiVisaoGeral.php",fd); 
        retPhp  = JSON.parse(msg);
        if( retPhp[0].retorno == "OK" ){
          msg=retPhp[0]["dados"].length;  
          
          for( var lin=0;lin<msg;lin++ ){
            ceLi= document.createElement("li"); 
            ceLi.style.height="25px";
              ceAnc= document.createElement("a");
              ceAnc.href="#";
              ceAnc.setAttribute("onclick","iniciarBi('"+retPhp[0]["dados"][lin]["UNI_CODIGO"]
                                                        +"','"+retPhp[0]["dados"][lin]["UNI_APELIDO"]+"'"
                                                        +"," + `'${pubCodPol}'` + ",'Todos polos', '*' , 'Todos Grupos Operacionais')");
                ceImg= document.createElement("i");
                ceImg.className="fa fa-object-ungroup text-red";
                ceAnc.appendChild(ceImg);
                
                ceContext = document.createTextNode( " -"+retPhp[0]["dados"][lin]["UNI_APELIDO"] );  
              ceAnc.appendChild(ceContext);
            ceLi.appendChild(ceAnc);
            document.getElementById("filtroUni").appendChild(ceLi);
          };    
          ceLi= document.createElement("li"); 
            ceAnc= document.createElement("a");
            ceAnc.href="#";
            ceAnc.setAttribute("onclick","iniciarBi('0','Todas unidades','*','Todos polos', '*', 'Todos Grupos Operacionais')");
              ceImg= document.createElement("i");
              ceImg.className="fa fa-object-ungroup text-red";
              ceAnc.appendChild(ceImg);
              
              ceContext = document.createTextNode( " -TODAS" );  
            ceAnc.appendChild(ceContext);
          ceLi.appendChild(ceAnc);
          document.getElementById("filtroUni").appendChild(ceLi);
        };
      };
      ///////////////////////////////////////////////////////////
      //   Buscando apenas os polos que o usuario tem direito  //
      ///////////////////////////////////////////////////////////
      function buscarPol(){
        document.getElementById('filtroPol').innerHTML = '';
        clsJs   = jsString("lote");  
        clsJs.add("rotina"      , "quaisPolo"                                     );
        clsJs.add("login"       , jsPub[0].usr_login                              );
        clsJs.add("compet"      , document.getElementById("cbCompetencia").value  );      
        fd = new FormData();
        fd.append("visaogeral" , clsJs.fim());
        msg     = requestPedido("Trac_BiVisaoGeral.php",fd); 
        retPhp  = JSON.parse(msg);
        if( retPhp[0].retorno == "OK" ){
          msg=retPhp[0]["dados"].length;  
          
          for( var lin=0;lin<msg;lin++ ){
            ceLi= document.createElement("li"); 
            ceLi.style.height="25px";
              ceAnc= document.createElement("a");
              ceAnc.href="#";
              ceAnc.setAttribute("onclick","iniciarBi('0','Todas unidades','"+retPhp[0]["dados"][lin]["POL_CODIGO"]+"','"
                +retPhp[0]["dados"][lin]["POL_NOME"]+"', '*', 'Todos Grupos Operacionais')");
                ceImg= document.createElement("i");
                ceImg.className="fa fa-object-group text-red";
                ceAnc.appendChild(ceImg);
                
                ceContext = document.createTextNode( " -"+retPhp[0]["dados"][lin]["POL_NOME"] );  
              ceAnc.appendChild(ceContext);
            ceLi.appendChild(ceAnc);
            document.getElementById("filtroPol").appendChild(ceLi);
          };    
          ceLi= document.createElement("li"); 
            ceAnc= document.createElement("a");
            ceAnc.href="#";
            ceAnc.setAttribute("onclick","iniciarBi('0','Todas unidades','*','Todos polos', '*', 'Todos Grupos Operacionais')");
              ceImg= document.createElement("i");
              ceImg.className="fa fa-object-group text-red";
              ceAnc.appendChild(ceImg);
              
              ceContext = document.createTextNode( " -TODOS" );  
            ceAnc.appendChild(ceContext);
          ceLi.appendChild(ceAnc);
          document.getElementById("filtroPol").appendChild(ceLi);
        };
      };
      ////////////////////////
      // UP=Unidade ou polo //
      ////////////////////////
      function fncKilometragem(qualUP){  
        clsJs   = jsString("lote");  
        clsJs.add("rotina"      , "biSimples"                                     );
        clsJs.add("login"       , jsPub[0].usr_login                              );
        clsJs.add("qualSelect"  , (qualUP=="U" ? "bisUniKm" : "bisPolKm")         );
        clsJs.add("coduni"      , pubCodUni                                       );      
        clsJs.add("codpol"      , pubCodPol                                       );
        clsJs.add("levpes"      , pubLevPes                                       );      
        clsJs.add("compet"      , document.getElementById("cbCompetencia").value  );      
        fd = new FormData();
        fd.append("visaogeral" , clsJs.fim());
        msg     = requestPedido("Trac_BiVisaoGeral.php",fd); 
        retPhp  = JSON.parse(msg);
        if( retPhp[0].retorno == "OK" ){
          //////////////////////////////////////////////////
          // Para nao remontar grafico em cima de grafico //
          //////////////////////////////////////////////////
          if(document.getElementById("pieChartKm") != undefined ){
            document.getElementById("pieChartKm").remove();
          };  
          ceCanvas              = document.createElement("canvas");
          ceCanvas.id           = "pieChartKm";
          ceCanvas.style.height ="150px";
          ceCanvas.style.width  ="50%";
          document.getElementById("divPieChartKm").appendChild(ceCanvas);
          //  
          //
          var pieChartCanvas  = document.getElementById("pieChartKm").getContext("2d");
          var pieChartKm      = new Chart(pieChartCanvas);
          var valor           = 0;
          var tblGra          = retPhp[0]["dados"];
          tam                 = tblGra.length;
          msg                 = 0;
          var arrColor        = ["#008d4c","#367fa9","#3c8dbc%","#dd4b39","#00a65a","#e7e7e7","#f39c12","#f56954","#00c0ef"];
          var iCor            = 0;
          var arrPieData      = [];
          ceUl= document.createElement("ul");  
          ceUl.id        = "ulKm";
          ceUl.className = "chart-legend clearfix";
          for( var linR=0;  linR<tam;  linR++ ){
            arrPieData.push({
              "value"       : tblGra[linR]["PERCENTUAL"] //parseInt(tblGra[linR]["PERCENTUAL"]+ "%")
              ,"color"      : arrColor[iCor]
              ,"highlight"  : arrColor[iCor]
              ,"label"      : tblGra[linR]["NOME"]
            });  
            msg+=parseFloat(tblGra[linR]["QTOS"]);
            ////////////////////////
            // Montando a legenda //
            ////////////////////////
            ceLi= document.createElement("li");
              ceContext = document.createTextNode(" " +tblGra[linR]["NOME"]+ ": "+jsNmrs(parseInt(tblGra[linR]["QTOS"])).emZero(5).ret()+" KM`s");
            ceImg= document.createElement("i");
            ceImg.className="fa fa-circle-o";
            ceImg.style.color = arrColor[iCor];
            ceLi.appendChild(ceImg);
            ceLi.appendChild(ceContext);
            ceUl.appendChild(ceLi);  
              
            iCor++;
            if( iCor==9 )
              iCor=0;
          };  
          if(document.getElementById("ulKm") != undefined ){
            document.getElementById("ulKm").remove();
          };
          document.getElementById("divKm").appendChild(ceUl);
          document.getElementById("totalKm").innerHTML="Total KM`s: "+jsNmrs(parseInt(msg)).emZero(5).ret();
          // Criar gráfico de torta ou rosquinha
          // Você pode alternar entre torta e rosca usando o método abaixo.
          pieChartKm.Doughnut(arrPieData, pieOptions)
        }  
      }
      /////////////////////////////////////////////
			// Funcao para trocar de Unidade para Polo //
      /////////////////////////////////////////////
      function chngMotUP(){
        if( document.getElementById("cbMotUP").value=="UNI" ){
          fncFiltrarTableSimples("bisMotoristaUni" ,"tblUniMtr","divUniMtr"    ,"qtosUniMtr"   ,pubCodUni,pubCodPol,"*", pubCodGpo);
        } else {
          fncFiltrarTableSimples("bisMotoristaPol" ,"tblUniMtr","divUniMtr"    ,"qtosUniMtr"   ,pubCodUni,pubCodPol,"*", pubCodGpo);
        }    
      }; 
      function chngVeiUP(){
        if( document.getElementById("cbVeiUP").value=="UNI" ){
          fncFiltrarTableSimples("bisVeiculoUni" ,"tblPolMtr","divPolMtr"    ,"qtosPolMtr"   ,pubCodUni,pubCodPol,pubLevPes, pubCodGpo);
        } else {
          fncFiltrarTableSimples("bisVeiculoPol" ,"tblPolMtr","divPolMtr"    ,"qtosPolMtr"   ,pubCodUni,pubCodPol,pubLevPes, pubCodGpo);
        }    
      }; 
      function chngCompetencia(){
        iniciarBi(0,"Todas unidades","*","Todos polos", '*', 'Todos Grupos Operacionais');
      };
     </script> 
  </head>
  <body>
    <nav class="navbar navbar-static-top">
      <div class="navbar-custom-menu" style="float:left;width:100%;border-bottom:1px solid silver;">
        <div class="form-group" style="width:10%;height:1.5em;float:left;margin-top:0.5em;">
          <button id="smllDesUni" type="button" class="btn btn-label" style="margin-left:10px;" >BI-...</button>
        </div>
        
        <div class="form-group" style="width:15%;height:1.5em;float:left;margin-top:0.5em;">
          <select id="cbLevePesado" class="form-control select2" style="width:70%;height:28px;margin-left:3em;">
            <option value="LP" selected="selected">Leve/Pesado</option>
            <option value="P">Pesado</option>
            <option value="L">Leve</option>
          </select>
        </div>

        <!-- <div class="form-group" style="width:15%;height:1.5em;float:left;margin-top:0.5em;">
          <select id="cbCompetencia" onChange="chngCompetencia();" class="form-control select2" style="width:70%;height:28px;margin-left:3em;">
          </select>
        </div> -->
        
        <?php include 'classPhp/comum/selectMesDashboard.class.php';?>

        <ul class="nav navbar-nav">
          <li class="dropdown notifications-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <i class="fa fa-filter">&nbsp;Polo</i>
              <span class="label label-success" style="top:5px;font-size:0.9em;" id="qtosPol"></span>
            </a>
            <ul class="dropdown-menu">
              <li class="header">Opções para Polo</li>
              <li>
                <ul id="filtroPol" class="menu" style="max-height: 500px;">
                </ul>
              </li>
              <li class="footer"><a href="#">Fechar</a></li>
            </ul>
          </li>
          
          <li class="dropdown notifications-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <i class="fa fa-filter">&nbsp;Unid</i>
              <span class="label label-warning" style="top:5px;" id="qtosUni"></span>
            </a>
            <ul class="dropdown-menu">
              <li class="header">Opções para Unidade</li>
              <li>
                <ul id="filtroUni" class="menu" style="max-height: 500px;">
                </ul>
              </li>
              <li class="footer"><a href="#">Fechar</a></li>
            </ul>
          </li>

          <li class="dropdown notifications-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <i class="fa fa-filter">&nbsp;Grupo Operacional</i>
              <span class="label label-warning" style="top:5px;" id="qtosGpo"></span>
            </a>
            <ul class="dropdown-menu">
              <li class="header">Grupos Operacionais</li>
              <li>
                <ul id="filtroGpo" class="menu" style="max-height: 500px;">
                </ul>
              </li>
              <li class="footer"><a href="#">Fechar</a></li>
            </ul>
          </li>

          <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <span class="hidden-xs">Trac</span>
            </a>
            <ul class="dropdown-menu">
              <li class="user-header">
                <img src="imagens/logoMenor.png" class="img-circle" alt="User Image">
                <p>
                  Total Trac
                  <small>
                    Rua Itanhaem, 2389
                    Vila Elisa - Ribeirão Preto SP
                  </small>
                </p>
              </li>
              <li class="user-footer">
                <div class="pull-left">
                  <a href="#" class="btn btn-default btn-flat">Fechar</a>
                </div>
              </li>
            </ul>
          </li>
          
          <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <span onClick="window.parent.document.getElementById('iframeCorpo').src='';"class="hidden-xs">Fechar</span>
            </a>
          </li>  
          
        </ul>
      </div>
    </nav>

    <section class="content">
      <div class="row">
       <!--BOX QTDE MOTORISTAS-->
				<div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box bg-blue">
            <span class="info-box-icon"><i class="fa fa-users"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Motoristas</span>
              <span id="qtosMtr" class="info-box-number"></span>
            </div>
           </div>
         </div>
 				<!-- -->
			  <!--BOX QTDE VEICULOS-->
				<div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box bg-green">
            <span class="info-box-icon"><i class="fa fa-car"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Veículos</span>
              <span id="qtosVcl" class="info-box-number"></span>
							<!--
              <div class="progress">
                <div class="progress-bar" style="width: 30%"></div>
              </div>
                  <span class="progress-description">
                    Aumento de 30 % 
                  </span>
							-->		
            </div>
           </div>
         </div>
 				<!-- -->
        <!--BOX QTDE KM MES-->
				<div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box bg-yellow">
            <span class="info-box-icon"><i class="fa fa-map"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">KM Percorrido</span>
              <span id="qtosKm" class="info-box-number"></span>
							<!--
              <div class="progress">
                <div class="progress-bar" style="width: 10%"></div>
              </div>
                  <span class="progress-description">
                    Aumento de 10 % 
                  </span>
							-->		
            </div>
           </div>
         </div>
 				<!-- -->
        <!--BOX QTDE HORAS EM MOVIMENTO-->
				<div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box bg-aqua">
            <span class="info-box-icon"><i class="fa fa-clock-o"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Horas Movimento</span>
              <span id="qtosHRod" class="info-box-number"></span>
            </div>
           </div>
         </div>
 				<!-- -->

        <!--BOX QTDE HORAS PARADO-->
				<div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box bg-aqua">
            <span class="info-box-icon"><i class="fa fa-clock-o"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Horas Parado</span>
              <span id="qtosHPar" class="info-box-number"></span>
            </div>
           </div>
         </div>
 				<!-- -->
				


				
				<!--BOX VELOCIDADE MÉDIA-->
				<div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box bg-navy">
            <span class="info-box-icon"><i class="fa fa-flag-checkered"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Velocidade Média</span>
              <span id="qtosVclMed" class="info-box-number"></span>
							<!--
              <div class="progress">
                <div class="progress-bar" style="width: 20%"></div>
              </div>
                  <span class="progress-description">
                    Aumento de 20 % 
                  </span>
							-->			
            </div>
           </div>
         </div>
 				<!-- -->
				<!--BOX QTDE INFRACOES-->
				<div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box bg-red">
            <span class="info-box-icon"><i class="fa  fa-exclamation-triangle"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">INFRAÇÕES</span>
              <span id="qtosInfra" class="info-box-number"></span>
              <div class="progress">
                <div class="progress-bar" style="width: 20%"></div>
              </div>
                  <span class="progress-description">
                    Aumento de 20 % 
                  </span>
            </div>
           </div>
         </div>
      </div>
    
    
      <div class="row">
        <div class="col-md-6">
          <div class="box">
            <div class="box-header with-border">
              <h3 id="h3UniMot" class="box-title">Motoristas</h3>
              <span id="qtosUniMtr" class="badge bg-light-blue" style="width:50px;">0</span>
              <div class="form-group" style="width:20%;height:1.5em;float:right;">
                <select id="cbMotUP" onChange="chngMotUP();" class="form-control select2" style="width: 100%;height:28px;">
                  <option value="UNI" selected="selected">Unidade</option>
                  <option value="POL">Polo</option>
                </select>
              </div> 
            </div>
            <div id="divUniMtr" class="box-body" style="height: 250px; overflow-y:auto;">            
            </div>
          </div>
        </div>
         
        <div class="col-md-6">
          <div class="box">
            <div class="box-header with-border">
              <h3 class="box-title">Veículos</h3>
              <span id="qtosPolMtr" class="badge bg-light-blue" style="width:50px;">0</span>
              <div class="form-group" style="width:20%;height:1.5em;float:right;">
                <select id="cbVeiUP" onChange="chngVeiUP();" class="form-control select2" style="width: 100%;height:28px;">
                  <option value="UNI" selected="selected">Unidade</option>
                  <option value="POL">Polo</option>
                </select>
              </div> 
              
            </div>
            <div id="divPolMtr" class="box-body" style="height: 250px; overflow-y:auto;">
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="box">
            <div class="box-header with-border">
              <h3 id="infracaoCompet" class="box-title">...</h3>
              <span id="qtosInfracaoN" class="badge bg-light-blue" style="width:50px;">0</span>
            </div>
            <div id="divInfracaoN" class="box-body" style="height: 270px; overflow-y:auto;">
            </div>
          </div>
        </div>
        
        <div class="col-md-6">
          <div class="box box-default" style="height: 315px;">
            <div class="box-header with-border">
              <h3 class="box-title">Comparativo EV/EVC/FB em %</h3>
              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
              </div>
            </div>
            <div class="box-body" style="padding-top:15px;">
              <div class="row">
                <div class="col-md-8">
                  <div id="divPieChart" class="chart-responsive">
                    <canvas id="pieChart" height="150"></canvas>
                  </div>
                </div>
                <div class="col-md-4">
                  <ul class="chart-legend clearfix">
                    <li><i class="fa fa-circle-o text-red"></i> Excesso veloc</li>
                    <li><i class="fa fa-circle-o text-green"></i> Excesso veloc chuva</li>
                    <li><i class="fa fa-circle-o text-yellow"></i> Freada brusca</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="row">
        <div class="col-md-6">
          <div class="box">
            <div class="box-header with-border">
              <h3 id="infracaoUni" class="box-title">Ranking Infrações por UNIDADE</h3>
              <span id="qtosInfracaoU" class="badge bg-light-blue" style="width:50px;">0</span>
            </div>
            <div id="divInfracaoU" class="box-body" style="height: 270px; overflow-y:auto;">
            </div>
          </div>
        </div>
        
        <div class="col-md-6">
          <div class="box">
            <div class="box-header with-border">
              <h3 id="infracaoPol" class="box-title">Ranking Infrações por POLO</h3>
              <span id="qtosInfracaoP" class="badge bg-light-blue" style="width:50px;">0</span>
            </div>
            <div id="divInfracaoP" class="box-body" style="height: 270px; overflow-y:auto;">
            </div>
          </div>
        </div>
      </div>
      
      
      
      
      
      
    </section>
    <div class="control-sidebar-bg"></div>
    <script src="adminLTE/jquery.js"></script>
    <script src="adminLTE/bootstrap.js"></script>
    <script src="adminLTE/jquery.slimscroll.js"></script>
    <script src="adminLTE/fastclick.js"></script>
    <script src="adminLTE/adminlte.js"></script>
    <script src="adminLTE/demo.js"></script>
    <script src="adminLTE/Chart.js"></script>
  </body>
</html>
