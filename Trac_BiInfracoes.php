<?php
  session_start();
  if( isset($_POST["principal"]) ){
    try{     
      require("classPhp/conectaSqlServer.class.php");
      require("classPhp/validaJSon.class.php"); 
      require("classPhp/removeAcento.class.php"); 
      require("classPhp/selectRepetidoTrac.class.php"); 
      require("classPhp/dataCompetencia.class.php");

      $vldr     = new validaJSon();          
      $retorno  = "";
      $retCls   = $vldr->validarJs($_POST["principal"]);
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
        $codmes   = ( isset($lote[0]->compet) ? $lote[0]->compet : "" );
        $classe   = new conectaBd();
        $classe->conecta($lote[0]->login);
        ///////////////////////////////////////////////////////////
        //   Bi Grafico line total de km dividido por infracoes  //
        ///////////////////////////////////////////////////////////
        if( $rotina=="biKmDividePorInfracao" ){
          $clsCompet = new dataCompetencia();
          switch( $lote[0]->levpes ){
            case "LP" : $frota=" AND (VCL.VCL_FROTA IN('L','P'))" ;break;
            case "L"  : $frota=" AND (VCL.VCL_FROTA='L')"         ;break;
            case "P"  : $frota=" AND (VCL.VCL_FROTA='P')"         ;break;
          };  
          ///////////////////////////////////
          // Pegando o total de KM por mês //
          ///////////////////////////////////
          $sql="";
          $sql.="SELECT A.BIKMM_ANOMES AS ANOMES,SUM(A.BIKMM_TOTAL) AS KM";
          $sql.="  FROM BI_KILOMETROMES A";
          $sql.="  LEFT OUTER JOIN VEICULO VCL ON A.BIKMM_CODVCL=VCL.VCL_CODIGO";          
          $sql.="  LEFT OUTER JOIN UNIDADE UNI ON A.BIKMM_CODUNI=UNI.UNI_CODIGO";
          $sql.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON A.BIKMM_CODUNI=UU.UU_CODUNI AND UU.UU_CODUSR=".$_SESSION['usr_codigo'];
          $sql.=" WHERE (A.BIKMM_ANOMES BETWEEN ".$lote[0]->dtIni." AND ".$lote[0]->dtFim.")";
          $sql.="   AND (COALESCE(UU.UU_ATIVO,'')='S')";
          $sql.=$frota;
          if( $lote[0]->coduni >0 ){
            $sql.=" AND (A.BIKMM_CODUNI=".$lote[0]->coduni.")";  
          } elseif($lote[0]->codpol != "*" ){
            $sql.=" AND (UNI.UNI_CODPOL='".$lote[0]->codpol."')";  
          };
          $sql.=" GROUP BY A.BIKMM_ANOMES";
          ///////////////////////////////
          // Guardando os totais de km //
          ///////////////////////////////
          $arrJs=[];
          ////////////////////////////////////////
          // Isso apenas para preencher espaços //
          ////////////////////////////////////////
          if( $lote[0]->dtFim<201812 ){
            array_push($arrJs,["ANOMES"=>"JAN/18" ,"KM"=>0,"INFRACAO"=>0,"MEDIA"=>0]);
            array_push($arrJs,["ANOMES"=>"FEV/18" ,"KM"=>0,"INFRACAO"=>0,"MEDIA"=>0]);
            array_push($arrJs,["ANOMES"=>"MAR/18" ,"KM"=>0,"INFRACAO"=>0,"MEDIA"=>0]);
            array_push($arrJs,["ANOMES"=>"ABR/18" ,"KM"=>0,"INFRACAO"=>0,"MEDIA"=>0]);
          };
          //  
          //
          
          
          $params   = array();
          $options  = array("Scrollable" => SQLSRV_CURSOR_FORWARD);
          $consulta = sqlsrv_query($_SESSION['conn'], $sql, $params, $options);
          while ($linha = sqlsrv_fetch_array($consulta, SQLSRV_FETCH_ASSOC)) {
            $clsCompet->montaRetorno($linha["ANOMES"],"yyyymm");
            array_push($arrJs,[
              "ANOMES"    =>  $clsCompet->getData('mmm/yy')//$linha["ANOMES"]
              ,"KM"       =>  round($linha["KM"],0)
              ,"INFRACAO" =>  0
              ,"MEDIA"    =>  0
            ]);
          };    
          ///////////////////////////////////
          // Pegando o total de EV por mês //
          ///////////////////////////////////
          $sql="";
          $sql.="SELECT A.BIEVCM_ANOMES AS ANOMES,SUM(A.BIEVCM_TOTAL) AS INFRACAO";
          $sql.="  FROM BI_EXCESSOVELCHMES A";
          $sql.="  LEFT OUTER JOIN VEICULO VCL ON A.BIEVCM_CODVCL=VCL.VCL_CODIGO";          
          $sql.="  LEFT OUTER JOIN UNIDADE UNI ON A.BIEVCM_CODUNI=UNI.UNI_CODIGO";
          $sql.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON A.BIEVCM_CODUNI=UU.UU_CODUNI AND UU.UU_CODUSR=".$_SESSION['usr_codigo'];
          $sql.=" WHERE (A.BIEVCM_ANOMES BETWEEN ".$lote[0]->dtIni." AND ".$lote[0]->dtFim.")";
          $sql.="   AND (COALESCE(UU.UU_ATIVO,'')='S')";
          $sql.=$frota;
          if( $lote[0]->coduni >0 ){
            $sql.=" AND (A.BIEVCM_CODUNI=".$lote[0]->coduni.")";  
          } elseif($lote[0]->codpol != "*" ){
            $sql.=" AND (UNI.UNI_CODPOL='".$lote[0]->codpol."')";  
          };
          $sql.=" GROUP BY A.BIEVCM_ANOMES";
          // Unido as tabelas //
          $sql.=" UNION ALL ";
          // Unido as tabelas //
          $sql.="SELECT A.BIEVM_ANOMES AS ANOMES,SUM(A.BIEVM_TOTAL) AS INFRACAO";
          $sql.="  FROM BI_EXCESSOVELOCMES A";
          $sql.="  LEFT OUTER JOIN VEICULO VCL ON A.BIEVM_CODVCL=VCL.VCL_CODIGO";          
          $sql.="  LEFT OUTER JOIN UNIDADE UNI ON A.BIEVM_CODUNI=UNI.UNI_CODIGO";
          $sql.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON A.BIEVM_CODUNI=UU.UU_CODUNI AND UU.UU_CODUSR=".$_SESSION['usr_codigo'];
          $sql.=" WHERE (A.BIEVM_ANOMES BETWEEN ".$lote[0]->dtIni." AND ".$lote[0]->dtFim.")";
          $sql.="   AND (COALESCE(UU.UU_ATIVO,'')='S')";
          $sql.=$frota;
          if( $lote[0]->coduni >0 ){
            $sql.=" AND (A.BIEVM_CODUNI=".$lote[0]->coduni.")";  
          } elseif($lote[0]->codpol != "*" ){
            $sql.=" AND (UNI.UNI_CODPOL='".$lote[0]->codpol."')";  
          };
          $sql.=" GROUP BY A.BIEVM_ANOMES";
          ////////////////////////////////////////////////////////////////
          // Guardando as infrações de cada mes junto com o total de KM //
          ////////////////////////////////////////////////////////////////
          //$tam=count($arrJs)
          $params   = array();
          $options  = array("Scrollable" => SQLSRV_CURSOR_FORWARD);
          $consulta = sqlsrv_query($_SESSION['conn'], $sql, $params, $options);
          while ($linha = sqlsrv_fetch_array($consulta, SQLSRV_FETCH_ASSOC)) {
            $clsCompet->montaRetorno($linha["ANOMES"],"yyyymm");
            $compara=$clsCompet->getData('mmm/yy');
            foreach( $arrJs as &$lin ){
              if( $lin["ANOMES"]==$compara ){
                $lin["INFRACAO"]+=$linha["INFRACAO"];
                $lin["MEDIA"]=round(($lin["KM"]/$lin["INFRACAO"]),0);
              };
            };    
          };
          /////////////////////////////////////////////////
          // Retornando ao javascript um array nao assoc //
          /////////////////////////////////////////////////
          $retorno='[{"retorno":"OK","dados":'.json_encode($arrJs).',"erro":""}]'; 
        };  
        ///////////////////////////////////////////////////////////
        //   Bi por tipo de infracao TURNO                       //
        ///////////////////////////////////////////////////////////
        if( $rotina=="biInfracaoTurno" ){
          $cSql   = new SelectRepetido();
          $retSql = $cSql->qualSelect("qualInfracaoMes",$lote[0]->login."|".$lote[0]->infracao);
          $alias  = $retSql[0]["alias"];
          $table  = $retSql[0]["tabela"];
          
          $sql="";  
          $sql.="SELECT ".$alias."_TURNO AS NOME";
          $sql.="       ,SUM(".$alias."_TOTAL) AS QTOS";
          $sql.="  FROM ".$table." A";
          $sql.="  LEFT OUTER JOIN UNIDADE U ON ".$alias."_CODUNI=U.UNI_CODIGO"; 
          $sql.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON ".$alias."_CODUNI=UU.UU_CODUNI AND UU.UU_CODUSR=".$_SESSION['usr_codigo'];
          if( $lote[0]->coduni >0 ){
            $sql.="  WHERE ( (".$alias."_ANOMES=".$codmes.") AND (".$alias."_CODUNI=".$lote[0]->coduni.") AND (COALESCE(UU.UU_ATIVO,'')='S'))";  
          } elseif($lote[0]->codpol != "*" ){            
            $sql.="  WHERE ( (".$alias."_ANOMES=".$codmes.") AND (U.UNI_CODPOL='".$lote[0]->codpol."') AND (COALESCE(UU.UU_ATIVO,'')='S'))";            
          } else {
            $sql.="  WHERE ( (".$alias."_ANOMES=".$codmes.") AND (COALESCE(UU.UU_ATIVO,'')='S'))";  
          };
          $sql.="  GROUP BY ".$alias."_TURNO";
          $classe->msgSelect(false);
          $retCls=$classe->selectAssoc($sql);
          if( $retCls['retorno'] != "OK" ){
            $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';  
          } else { 
            $retorno='[{"retorno":"OK","dados":'.json_encode($retCls['dados']).',"erro":""}]'; 
          };
        };  
        ///////////////////////////////////////////////////////////
        //   Bi por tipo de infracao TOP                         //
        ///////////////////////////////////////////////////////////
        if( $rotina=="biInfracaoTop" ){
          $cSql   = new SelectRepetido();
          $retSql = $cSql->qualSelect("qualInfracaoMes",$lote[0]->login."|".$lote[0]->infracao);
          $alias  = $retSql[0]["alias"];
          $table  = $retSql[0]["tabela"];
          
          $sql="";  
          $sql.="SELECT M.MTR_NOME";
          $sql.="       ,U.UNI_APELIDO";
          $sql.="       ,SUM(".$alias."_TOTAL) AS TOTAL";
          $sql.="  FROM ".$table." A";
          $sql.="  LEFT OUTER JOIN MOTORISTA M ON ".$alias."_CODMTR=M.MTR_CODIGO";
          $sql.="  LEFT OUTER JOIN UNIDADE U ON ".$alias."_CODUNI=U.UNI_CODIGO"; 
          $sql.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON ".$alias."_CODUNI=UU.UU_CODUNI AND UU.UU_CODUSR=".$_SESSION['usr_codigo'];
          if( $lote[0]->coduni >0 ){
            $sql.="  WHERE ( (".$alias."_ANOMES=".$codmes.") AND (".$alias."_CODUNI=".$lote[0]->coduni.") AND (COALESCE(UU.UU_ATIVO,'')='S'))";  
          } elseif($lote[0]->codpol != "*" ){            
            $sql.="  WHERE ( (".$alias."_ANOMES=".$codmes.") AND (U.UNI_CODPOL='".$lote[0]->codpol."') AND (COALESCE(UU.UU_ATIVO,'')='S'))";            
          } else {
            $sql.="  WHERE ( (".$alias."_ANOMES=".$codmes.") AND (COALESCE(UU.UU_ATIVO,'')='S'))";  
          };
          $sql.="  GROUP BY M.MTR_NOME,U.UNI_APELIDO";
          $classe->msgSelect(false);
          $retCls=$classe->selectAssoc($sql);
          if( $retCls['retorno'] != "OK" ){
            $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';  
          } else { 
            $retorno='[{"retorno":"OK","dados":'.json_encode($retCls['dados']).',"erro":""}]'; 
          };  
        };  
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
          $retSql = $cSql->qualSelect("quaisUnidade",$lote[0]->login);
          $retorno='[{"retorno":"'.$retSql["retorno"].'","dados":'.$retSql["dados"].',"erro":"'.$retSql["erro"].'"}]';
        };  
        ////////////////
        // BI CONTAR  //
        ////////////////
        if( $rotina=="biContar" ){
          $sql="";
          if( $lote[0]->qualSelect=="contarPolo" ){  
            $cSql = new SelectRepetido();
            $sql  = $cSql->qualSelect("qtosPolo",$lote[0]->login."|".$lote[0]->codpol);
          };
          if( $lote[0]->qualSelect=="contarUnidade" ){ 
            $cSql = new SelectRepetido();
            $sql  = $cSql->qualSelect("qtasUnidade",$lote[0]->login."|".$lote[0]->coduni."|".$lote[0]->codpol);
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
          if( $lote[0]->qualSelect=="bisUniKm" ){
            $sql.="SELECT UNI.UNI_APELIDO AS NOME,SUM(A.BIKMM_TOTAL) AS QTOS";
            $sql.="  FROM BI_KILOMETROMES A";
            $sql.="  LEFT OUTER JOIN UNIDADE UNI ON A.BIKMM_CODUNI=UNI.UNI_CODIGO";
            $sql.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON A.BIKMM_CODUNI=UU.UU_CODUNI AND UU.UU_CODUSR=".$_SESSION['usr_codigo'];
            if( $lote[0]->coduni >0 ){
              $sql.="  WHERE ((A.BIKMM_CODUNI=".$lote[0]->coduni.") AND (COALESCE(UU.UU_ATIVO,'')='S'))";
            } elseif($lote[0]->codpol != "*" ){              
              $sql.="  WHERE ((UNI.UNI_CODPOL='".$lote[0]->codpol."') AND (COALESCE(UU.UU_ATIVO,'')='S'))";
            } else {
              $sql.="  WHERE (COALESCE(UU.UU_ATIVO,'')='S')";
            };
            $sql.="  GROUP BY UNI.UNI_APELIDO";              
          };
          if( $lote[0]->qualSelect=="bisPolKm" ){
            $sql.="SELECT UNI.UNI_CODPOL AS NOME,SUM(A.BIKMM_TOTAL) AS QTOS";
            $sql.="  FROM BI_KILOMETROMES A";
            $sql.="  LEFT OUTER JOIN UNIDADE UNI ON A.BIKMM_CODUNI=UNI.UNI_CODIGO";
            $sql.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON A.BIKMM_CODUNI=UU.UU_CODUNI AND UU.UU_CODUSR=".$_SESSION['usr_codigo'];
            if( $lote[0]->coduni >0 ){
              $sql.="  WHERE ((A.BIKMM_CODUNI=".$lote[0]->coduni.") AND (COALESCE(UU.UU_ATIVO,'')='S'))";
            } elseif($lote[0]->codpol != "*" ){              
              $sql.="  WHERE ((UNI.UNI_CODPOL='".$lote[0]->codpol."') AND (COALESCE(UU.UU_ATIVO,'')='S'))";
            } else {
              $sql.="  WHERE (COALESCE(UU.UU_ATIVO,'')='S')";
            };
            $sql.="  GROUP BY UNI.UNI_CODPOL";              
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
                  ,"PERCENTUAL" => number_format($pc,0)
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
          }  
          //
          $sql="";
          if( $lote[0]->qualSelect=="infracao" ){ 
            $sql.=" SELECT 'AB' AS ID,'ACELERACAO BRUSCA' AS NOME,COALESCE(SUM(A.BIABM_TOTAL),0) AS QTOS";
            $sql.="  FROM BI_ACELERBRUSCAMES A";
            $sql.="  LEFT OUTER JOIN UNIDADE UNI ON A.BIABM_CODUNI=UNI.UNI_CODIGO";
            $sql.="  LEFT OUTER JOIN VEICULO VCL ON A.BIABM_CODVCL=VCL.VCL_CODIGO";
            $sql.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON A.BIABM_CODUNI=UU.UU_CODUNI AND UU.UU_CODUSR=".$_SESSION['usr_codigo'];
            if( $lote[0]->coduni >0 ){
              $sql.="  WHERE ((BIABM_ANOMES=".$codmes.") AND (A.BIABM_CODUNI=".$lote[0]->coduni.") AND (COALESCE(UU.UU_ATIVO,'')='S') ".$frota.")";    
            } elseif($lote[0]->codpol != "*" ){              
              $sql.="  WHERE ((BIABM_ANOMES=".$codmes.") AND (UNI.UNI_CODPOL='".$lote[0]->codpol."') AND (COALESCE(UU.UU_ATIVO,'')='S') ".$frota.")";    
            } else {
              $sql.="  WHERE ((BIABM_ANOMES=".$codmes.") AND (COALESCE(UU.UU_ATIVO,'')='S') ".$frota.")";  
            };  
            $sql.="  UNION ALL";
            $sql.=" SELECT 'CB' AS ID,'CONDUCAO BANGUELA' AS NOME,COALESCE(SUM(A.BICBM_TOTAL),0) AS QTOS";
            $sql.="  FROM BI_CONDUCAOBANGMES A";
            $sql.="  LEFT OUTER JOIN UNIDADE UNI ON A.BICBM_CODUNI=UNI.UNI_CODIGO";
            $sql.="  LEFT OUTER JOIN VEICULO VCL ON A.BICBM_CODVCL=VCL.VCL_CODIGO";
            $sql.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON A.BICBM_CODUNI=UU.UU_CODUNI AND UU.UU_CODUSR=".$_SESSION['usr_codigo'];
            if( $lote[0]->coduni >0 ){
              $sql.="  WHERE ((BICBM_ANOMES=".$codmes.") AND (A.BICBM_CODUNI=".$lote[0]->coduni.") AND (COALESCE(UU.UU_ATIVO,'')='S') ".$frota.")";    
            } elseif($lote[0]->codpol != "*" ){              
              $sql.="  WHERE ((BICBM_ANOMES=".$codmes.") AND (UNI.UNI_CODPOL='".$lote[0]->codpol."') AND (COALESCE(UU.UU_ATIVO,'')='S') ".$frota.")";    
            } else {
              $sql.="  WHERE ((BICBM_ANOMES=".$codmes.") AND (COALESCE(UU.UU_ATIVO,'')='S') ".$frota.")";  
            };  
            $sql.=" UNION ALL";
            $sql.=" SELECT 'EV' AS ID,'EXCESSO VELOC' AS NOME,COALESCE(SUM(A.BIEVM_TOTAL),0) AS QTOS";
            $sql.="  FROM BI_EXCESSOVELOCMES A";
            $sql.="  LEFT OUTER JOIN UNIDADE UNI ON A.BIEVM_CODUNI=UNI.UNI_CODIGO";
            $sql.="  LEFT OUTER JOIN VEICULO VCL ON A.BIEVM_CODVCL=VCL.VCL_CODIGO";
            $sql.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON A.BIEVM_CODUNI=UU.UU_CODUNI AND UU.UU_CODUSR=".$_SESSION['usr_codigo'];
            if( $lote[0]->coduni >0 ){
              $sql.="  WHERE ((BIEVM_ANOMES=".$codmes.") AND (A.BIEVM_CODUNI=".$lote[0]->coduni.") AND (COALESCE(UU.UU_ATIVO,'')='S') ".$frota.")";    
            } elseif($lote[0]->codpol != "*" ){              
              $sql.="  WHERE ((BIEVM_ANOMES=".$codmes.") AND (UNI.UNI_CODPOL='".$lote[0]->codpol."') AND (COALESCE(UU.UU_ATIVO,'')='S') ".$frota.")";    
            } else {
              $sql.="  WHERE ((BIEVM_ANOMES=".$codmes.") AND (COALESCE(UU.UU_ATIVO,'')='S') ".$frota.")";  
            };  
            $sql.=" UNION ALL";
            $sql.=" SELECT 'EVC' AS ID,'EXCESSO VELOC CHUVA' AS NOME,COALESCE(SUM(A.BIEVCM_TOTAL),0) AS QTOS";
            $sql.="  FROM BI_EXCESSOVELCHMES A";
            $sql.="  LEFT OUTER JOIN UNIDADE UNI ON A.BIEVCM_CODUNI=UNI.UNI_CODIGO";
            $sql.="  LEFT OUTER JOIN VEICULO VCL ON A.BIEVCM_CODVCL=VCL.VCL_CODIGO";            
            $sql.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON A.BIEVCM_CODUNI=UU.UU_CODUNI AND UU.UU_CODUSR=".$_SESSION['usr_codigo'];
            if( $lote[0]->coduni >0 ){
              $sql.="  WHERE ((BIEVCM_ANOMES=".$codmes.") AND (A.BIEVCM_CODUNI=".$lote[0]->coduni.") AND (COALESCE(UU.UU_ATIVO,'')='S') ".$frota.")";    
            } elseif($lote[0]->codpol != "*" ){              
              $sql.="  WHERE ((BIEVCM_ANOMES=".$codmes.") AND (UNI.UNI_CODPOL='".$lote[0]->codpol."') AND (COALESCE(UU.UU_ATIVO,'')='S') ".$frota.")";    
            } else {
              $sql.="  WHERE ((BIEVCM_ANOMES=".$codmes.") AND (COALESCE(UU.UU_ATIVO,'')='S') ".$frota.")";  
            };  
            $sql.=" UNION ALL";
            $sql.=" SELECT 'FB' AS ID,'FREADA BRUSCA' AS NOME,COALESCE(SUM(A.BIFBM_TOTAL),0) AS QTOS";
            $sql.="  FROM BI_FREADABRUSCAMES A";
            $sql.="  LEFT OUTER JOIN UNIDADE UNI ON A.BIFBM_CODUNI=UNI.UNI_CODIGO";
            $sql.="  LEFT OUTER JOIN VEICULO VCL ON A.BIFBM_CODVCL=VCL.VCL_CODIGO";            
            $sql.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON A.BIFBM_CODUNI=UU.UU_CODUNI AND UU.UU_CODUSR=".$_SESSION['usr_codigo'];
            if( $lote[0]->coduni >0 ){
              $sql.="  WHERE ((BIFBM_ANOMES=".$codmes.") AND (A.BIFBM_CODUNI=".$lote[0]->coduni.") AND (COALESCE(UU.UU_ATIVO,'')='S') ".$frota.")";    
            } elseif($lote[0]->codpol != "*" ){              
              $sql.="  WHERE ((BIFBM_ANOMES=".$codmes.") AND (UNI.UNI_CODPOL='".$lote[0]->codpol."') AND (COALESCE(UU.UU_ATIVO,'')='S') ".$frota.")";    
            } else {
              $sql.="  WHERE ((BIFBM_ANOMES=".$codmes.") AND (COALESCE(UU.UU_ATIVO,'')='S') ".$frota.")";  
            };  
            $sql.=" UNION ALL";
            $sql.=" SELECT 'ERPM' AS ID,'EXCESSO RPM' AS NOME,COALESCE(SUM(A.BIRAM_TOTAL),0) AS QTOS";
            $sql.="  FROM BI_RPMALTOMES A";
            $sql.="  LEFT OUTER JOIN UNIDADE UNI ON A.BIRAM_CODUNI=UNI.UNI_CODIGO";
            $sql.="  LEFT OUTER JOIN VEICULO VCL ON A.BIRAM_CODVCL=VCL.VCL_CODIGO";            
            $sql.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON A.BIRAM_CODUNI=UU.UU_CODUNI AND UU.UU_CODUSR=".$_SESSION['usr_codigo'];
            if( $lote[0]->coduni >0 ){
              $sql.="  WHERE ((BIRAM_ANOMES=".$codmes.") AND (A.BIRAM_CODUNI=".$lote[0]->coduni.") AND (COALESCE(UU.UU_ATIVO,'')='S') ".$frota.")";    
            } elseif($lote[0]->codpol != "*" ){              
              $sql.="  WHERE ((BIRAM_ANOMES=".$codmes.") AND (UNI.UNI_CODPOL='".$lote[0]->codpol."') AND (COALESCE(UU.UU_ATIVO,'')='S') ".$frota.")";    
            } else {
              $sql.="  WHERE ((BIRAM_ANOMES=".$codmes.") AND (COALESCE(UU.UU_ATIVO,'')='S') ".$frota.")";  
            };  
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
                [  "ID"         => ($lin+1)
                  ,"SIGLA"      => $tblMtr[$lin]["ID"]
                  ,"NOME"       => $tblMtr[$lin]["NOME"]
                  ,"QTOS"       => $tblMtr[$lin]["QTOS"]
                  ,"PERCENTUAL" => number_format($pc,0)
                  ,"COLCOR"     => $colCor
                  ,"COLCLASS"   => $colClass
                  ,"GRAFICO"    => (($tblMtr[$lin]["ID"] == "EV")  ? "S" : 
                                   (($tblMtr[$lin]["ID"] == "EVC") ? "S" : 
                                   (($tblMtr[$lin]["ID"] == "FB")  ? "S" : "N")))
                ]); 
            };
            $retorno='[{"retorno":"OK","dados":'.json_encode($arrJava).',"erro":""}]'; 
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
				comboCompetencia("classif_mot",document.getElementById("cbCompetencia"));
        window.parent.document.getElementById("iframeCorpo").height="50em";
        buscarUni();
        buscarPol();
        iniciarBi(0,"Todas unidades","*","Todos polos");
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
      var pubLevPes = "LP";   //Buscar por veiculo leve/pesado
      var pubCompetencia="*";  
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
      
      var areaChartOptions = {
        showScale               : true,               //Boolean - If we should show the scale at all
        scaleShowGridLines      : true,               //Boolean - Whether grid lines are shown across the chart
        scaleGridLineColor      : 'rgba(0,0,0,.05)',  //String - Colour of the grid lines
        scaleGridLineWidth      : 2,                  //Number - Width of the grid lines
        scaleShowHorizontalLines: true,               //Boolean - Whether to show horizontal lines (except X axis)
        scaleShowVerticalLines  : true,               //Boolean - Whether to show vertical lines (except Y axis)
        bezierCurve             : true,               //Boolean - Whether the line is curved between points
        bezierCurveTension      : 0.3,                //Number - Tension of the bezier curve between points
        pointDot                : true,               //Boolean - Whether to show a dot for each point
        pointDotRadius          : 4,                  //Number - Radius of each point dot in pixels
        pointDotStrokeWidth     : 1,                  //Number - Pixel width of point dot stroke
        pointHitDetectionRadius : 20,                 //Number - amount extra to add to the radius to cater for hit detection outside the drawn point
        datasetStroke           : true,               //Boolean - Whether to show a stroke for datasets
        datasetStrokeWidth      : 2,                  //Number - Pixel width of dataset stroke
        datasetFill             : true,               //Boolean - Whether to fill the dataset with a color
        //String - A legend template
        legendTemplate          : '<ul class="<%=name.toLowerCase()%>-legend"><% for (var i=0; i<datasets.length; i++){%><li><span style="background-color:<%=datasets[i].lineColor%>"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>',
        maintainAspectRatio     : true,         //Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
        responsive              : true          //Boolean - whether to make the chart responsive to window resizing
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
      //
      ////////////////////////////////////////////////////////////////////////////
      // Esta function inicia o BI com todas unidades que o usuario tem direito //
      // Tb eh usada qdo selecionado filtro por uma unidade                     //
      ////////////////////////////////////////////////////////////////////////////
      function iniciarBi(ibCodUni,ibDesUni,ibCodPol,ibDesPol){
        pubCompetencia=(document.getElementById("cbCompetencia").value).split("|");
        document.getElementById("infracaoCompet").innerHTML="Infrações "+document.getElementById("cbCompetencia").options[document.getElementById("cbCompetencia").selectedIndex].text+" ";
        pubCodUni=ibCodUni;
        pubDesUni=ibDesUni;
        pubCodPol=ibCodPol;
        pubDesPol=ibDesPol;
        pubLevPes=document.getElementById("cbLevePesado").value;
        fncContar("contarPolo"      ,"qtosPol",pubCodUni,pubCodPol,"*");
        fncContar("contarUnidade"   ,"qtosUni",pubCodUni,pubCodPol,"*");
        fncFiltrarTableInfracao("infracao"        ,"tblinf"   ,"divInfracao"  ,"qtosInfracao" ,pubCodUni,pubCodPol,pubLevPes);
        document.getElementById("smllDesUni").innerHTML=ibDesUni;
				fncInfracaoTop('EV');
				fncInfracaoTurno('EV');
				fncInfracaoTurno('EV');
      };
      //    
      //  
      function fncContar(qualSelect,qualSpan,qualCodUni,qualCodPol,qualLevPes){
        clsJs   = jsString("lote");  
        clsJs.add("rotina"      , "biContar"          );
        clsJs.add("login"       , jsPub[0].usr_login  );
        clsJs.add("qualSelect"  , qualSelect          );
        clsJs.add("coduni"      , qualCodUni          );
        clsJs.add("codpol"      , qualCodPol          );
        clsJs.add("levpes"      , qualLevPes          );
        clsJs.add("compet"      , pubCompetencia[0]   );      
        fd = new FormData();
        fd.append("principal" , clsJs.fim());
        msg     = requestPedido("Trac_BiInfracoes.php",fd); 
        retPhp  = JSON.parse(msg);
        if( retPhp[0].retorno == "OK" ){
          document.getElementById(qualSpan).innerHTML=retPhp[0]["dados"][0];
        };  
      };
      //
      //  
      //////////////////////////////////////
      // Somente tabelas infracao         //  
      //////////////////////////////////////
      function fncFiltrarTableInfracao(qualSelect,qualTbl,qualDiv,qualTot,qualCodUni,qualCodPol,qualLevPes){
        clsJs   = jsString("lote");  
        clsJs.add("rotina"      , "biInfracao"        );
        clsJs.add("login"       , jsPub[0].usr_login  );
        clsJs.add("qualSelect"  , qualSelect          );
        clsJs.add("coduni"      , qualCodUni          );
        clsJs.add("codpol"      , qualCodPol          );
        clsJs.add("levpes"      , qualLevPes          );      
        clsJs.add("compet"      , pubCompetencia[0]   );      
        fd = new FormData();
        fd.append("principal" , clsJs.fim());
        msg     = requestPedido("Trac_BiInfracoes.php",fd); 
        retPhp  = JSON.parse(msg);
        if( retPhp[0].retorno == "OK" ){
          var arrTitulo = ["ID"   ,"DESCRITIVO","GRAFICO","%"  ,"QTOS"];
          var arrColW   = ["8%"   ,"35%"       ,"37%"    ,"10%","10%"];
          var qtdCol    = arrColW.length;            // Quantidade de colunas
          var qtdRow    = retPhp[0]["dados"].length; // Quantidade de linhas do retorno select
          var totQtos   = 0;                         // Total de qtos
          var totGra    = 0;                         // Pegando total para grafico
          
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
                // Coluna sigla //
                ///////////////////////
                case 0:
                  ceTd = document.createElement("td");
                    ceContext = document.createTextNode( retPhp[0]["dados"][linR]["SIGLA"] );
                  ceTd.appendChild(ceContext); 
                  ceTr.appendChild(ceTd);         
                  break;
                ///////////////////////
                // Coluna descritivo //
                ///////////////////////
                case 1:
                  ceTd = document.createElement("td");
                    ceContext = document.createTextNode( retPhp[0]["dados"][linR]["NOME"] );
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
                      ceDivF.className = retPhp[0]["dados"][linR]["COLCLASS"];     
                      ceDivF.style.width = retPhp[0]["dados"][linR]["PERCENTUAL"]+"%";
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
                //akii
                case 4:
                  ceTd = document.createElement("td");
                  //ceTd.setAttribute("onclick","porInfracao('"+qualCodUni+"','"+retPhp[0]["dados"][linR]["SIGLA"]+"')");
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
                  if( retPhp[0]["dados"][linR]["GRAFICO"]=="S" ){
                    totGra+=retPhp[0]["dados"][linR]["QTOS"];
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
          var tblGra=retPhp[0]["dados"];
          var arrColor=["#f56954","#00a65a","#f39c12"];
          var iCor=0;
          var arrPieData=[];
          for( var linR=0;  linR<qtdRow;  linR++ ){
            if( tblGra[linR]["GRAFICO"]=="S" ){
              valor=((tblGra[linR]["QTOS"]*100)/totGra);
              arrPieData.push({
                "value":parseInt(valor)
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
        };
      };
      ///////////////////////////////////////////////////////////
      // Buscando apenas as unidades que o usuario tem direito //
      ///////////////////////////////////////////////////////////
      function buscarUni(){
        clsJs   = jsString("lote");  
        clsJs.add("rotina"      , "quaisUnidade"      );
        clsJs.add("login"       , jsPub[0].usr_login  );
        clsJs.add("compet"      , pubCompetencia[0]   );      
        fd = new FormData();
        fd.append("principal" , clsJs.fim());
        msg     = requestPedido("Trac_BiInfracoes.php",fd); 
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
                                                        +",'*','Todos polos')");
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
            ceAnc.setAttribute("onclick","iniciarBi('0','Todas unidades','*','Todos polos')");
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
        clsJs   = jsString("lote");  
        clsJs.add("rotina"      , "quaisPolo"         );
        clsJs.add("login"       , jsPub[0].usr_login  );
        clsJs.add("compet"      , pubCompetencia[0]   );      
        fd = new FormData();
        fd.append("principal" , clsJs.fim());
        msg     = requestPedido("Trac_BiInfracoes.php",fd); 
        retPhp  = JSON.parse(msg);
        if( retPhp[0].retorno == "OK" ){
          msg=retPhp[0]["dados"].length;  
          
          for( var lin=0;lin<msg;lin++ ){
            ceLi= document.createElement("li"); 
            ceLi.style.height="25px";
              ceAnc= document.createElement("a");
              ceAnc.href="#";
              ceAnc.setAttribute("onclick","iniciarBi('0','Todas unidades','"+retPhp[0]["dados"][lin]["POL_CODIGO"]+"','"+retPhp[0]["dados"][lin]["POL_NOME"]+"')");
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
            ceAnc.setAttribute("onclick","iniciarBi('0','Todas unidades','*','Todos polos')");
              ceImg= document.createElement("i");
              ceImg.className="fa fa-object-group text-red";
              ceAnc.appendChild(ceImg);
              
              ceContext = document.createTextNode( " -TODOS" );  
            ceAnc.appendChild(ceContext);
          ceLi.appendChild(ceAnc);
          document.getElementById("filtroPol").appendChild(ceLi);
        };
      };
      function fncInfracaoTop(qualInfracao){  
        clsJs   = jsString("lote");  
        clsJs.add("rotina"        , "biInfracaoTop"     );
        clsJs.add("login"         , jsPub[0].usr_login  );
        clsJs.add("infracao"      , qualInfracao        );
        clsJs.add("coduni"        , pubCodUni           );      
        clsJs.add("codpol"        , pubCodPol           );
        clsJs.add("levpes"        , pubLevPes           );      
        clsJs.add("compet"        , pubCompetencia[0]   );      
        fd = new FormData();
        fd.append("principal" , clsJs.fim());
        msg = requestPedido("Trac_BiInfracoes.php",fd); 
        retPhp  = JSON.parse(msg);
        if( retPhp[0].retorno == "OK" ){
          var tblGra=retPhp[0]["dados"];
          tblGra.sort(function (obj1, obj2) {
            return (obj1.TOTAL > obj2.TOTAL ? -1 : obj1.TOTAL < obj2.TOTAL ? 1 : 0);
          });
          var arrTitulo = ["NOME" ,"UNIDADE","TOTAL"];
          var arrColW   = ["60%"  , "20%"   , "10%" ];
          var arrAling  = ["E"    , "E"     , "C"   ];
          var qtdCol  = arrColW.length;            // Quantidade de colunas
          ///////////////////////////////////////////////
          // Se ja existir a table removo devido click //
          ///////////////////////////////////////////////
          if(document.getElementById("tblInf") != undefined ){
            document.getElementById("tblInf").remove();
          };
          /////////////////////
          // Criando a table //  
          /////////////////////
          ceTable           = document.createElement("table");
          ceTable.align = "center";
          ceTable.style.width = "60%";
          ceTable.style.border = "1px solid #CDC9C9";
          ceTable.id        = "tblInf";
          ceTable.className = "table table-bordered";
          ceTable.style.marginLeft="15em";
          ////////////////////////////////
          // Criando as th (cabecalho)  //  
          ////////////////////////////////
          ceTr = document.createElement("tr");          
          for( var lin=0;lin<qtdCol;lin++ ){
            ceTh = document.createElement("th");
            ceTh.style.textAlign = (arrAling[lin]=="C" ? "center" : arrAling[lin]=="D" ? "right" : "left" );
            ceTh.style.width = arrColW[lin];
              ceContext = document.createTextNode( arrTitulo[lin] );
            ceTh.appendChild(ceContext); 
            ceTr.appendChild(ceTh); 
          };
          ceTable.appendChild(ceTr); 

          /////////////////////
          // Criando as tr   //  
          /////////////////////
          ceTr = document.createElement("tr");
          ceTr.style.height="15px";
          msg=0;
          for( var linR=0;  linR<10;  linR++ ){
            ceTr = document.createElement("tr");
            ceTr.style.backgroundColor = (linR % 2 ? "#CDC9C9" : "white");     
            ceTr.style.fontSize = "13px";          
            for( var linC=0;  linC<qtdCol;  linC++ ){
              switch (linC) {
                ///////////////////////
                // Colunas descritivo //
                ///////////////////////
                case 0: 
                  ceTd = document.createElement("td");
                    ceContext = document.createTextNode( retPhp[0]["dados"][linR]["MTR_NOME"] );
                  ceTd.appendChild(ceContext); 
                  ceTr.appendChild(ceTd);         
                  break;
                case 1: 
                  ceTd = document.createElement("td");
                    ceContext = document.createTextNode( retPhp[0]["dados"][linR]["UNI_APELIDO"] );
                  ceTd.appendChild(ceContext); 
                  ceTr.appendChild(ceTd);         
                  break;
                case 2: 
                  ceTd = document.createElement("td");
                  ceTd.style.textAlign = (arrAling[linC]=="C" ? "center" : arrAling[linC]=="D" ? "right" : "left" );
                    ceContext = document.createTextNode( retPhp[0]["dados"][linR]["TOTAL"] );
                  ceTd.appendChild(ceContext); 
                  ceTr.appendChild(ceTd);         
                  msg+=retPhp[0]["dados"][linR]["TOTAL"];
                  break;
              };  
            };
            ceTable.appendChild(ceTr);
          };     
          document.getElementById("divTblInfracaoTop").appendChild(ceTable);
          document.getElementById("infracaoTop").innerHTML=jsNmrs(parseInt(msg)).emZero(4).ret();        
        };  
      };
      ////////////////////////
      // Infracao por turno //
      ////////////////////////    
      function fncInfracaoTurno(qualInfracao){  
        clsJs   = jsString("lote");  
        clsJs.add("rotina"      , "biInfracaoTurno"   );
        clsJs.add("login"       , jsPub[0].usr_login  );
        clsJs.add("infracao"    , qualInfracao        );
        clsJs.add("coduni"      , pubCodUni           );      
        clsJs.add("codpol"      , pubCodPol           );
        clsJs.add("levpes"      , pubLevPes           );      
        clsJs.add("compet"      , pubCompetencia[0]   );      
        fd = new FormData();
        fd.append("principal" , clsJs.fim());
        msg     = requestPedido("Trac_BiInfracoes.php",fd); 
        retPhp  = JSON.parse(msg);
        if( retPhp[0].retorno == "OK" ){
          //////////////////////////////////////////////////
          // Para nao remontar grafico em cima de grafico //
          //////////////////////////////////////////////////
          if(document.getElementById("pieChartTurno") != undefined ){
            document.getElementById("pieChartTurno").remove();
          };  
          ceCanvas              = document.createElement("canvas");
          ceCanvas.id           = "pieChartTurno";
          ceCanvas.style.height ="150px";
          ceCanvas.style.width  ="50%";
          document.getElementById("divPieChartTurno").appendChild(ceCanvas);
          //  
          //
          var pieChartCanvas  = document.getElementById("pieChartTurno").getContext("2d");
          var pieChartTurno   = new Chart(pieChartCanvas);
          var valor           = 0;
          var tblGra          = retPhp[0]["dados"];
          tam                 = tblGra.length;
          msg                 = 0;
          var arrColor        = ["#008d4c","#367fa9","#e7e7e7","#dd4b39"];
          var iCor            = 0;
          var arrPieData      = [];
          ceUl= document.createElement("ul");  
          ceUl.id         = "ulTurno";
          ceUl.className  = "chart-legend clearfix";
          var lgdNome     = "";
          
          for( var linR=0;  linR<tam;  linR++ ){
            
            ////////////////////////
            // Montando a legenda //
            ////////////////////////
            switch(tblGra[linR]["NOME"]){
              case "*": lgdNome=" ERRO"; break;
              case "M": lgdNome=" TURNO 1"; break;
              case "T": lgdNome=" TURNO 2"; break;
              case "N": lgdNome=" TURNO 3"; break;
            };
            
            
            arrPieData.push({
              "value"       : parseInt(tblGra[linR]["QTOS"])
              ,"color"      : arrColor[linR]
              ,"highlight"  : arrColor[linR]
              ,"label"      : lgdNome
            });  
            msg+=parseInt(tblGra[linR]["QTOS"]);
            
            
            ceLi= document.createElement("li");
              ceContext = document.createTextNode( lgdNome );
            ceImg= document.createElement("i");
            ceImg.className="fa fa-circle-o";
            ceImg.style.color = arrColor[linR];
            ceLi.appendChild(ceImg);
            ceLi.appendChild(ceContext);
            ceUl.appendChild(ceLi);  
          };  
          if(document.getElementById("ulTurno") != undefined ){
            document.getElementById("ulTurno").remove();
          };
          document.getElementById("divTurno").appendChild(ceUl);
          document.getElementById("infracaoTurno").innerHTML="Total:"+jsNmrs(parseInt(msg)).emZero(5).ret();
          // Criar gráfico de torta ou rosquinha
          // Você pode alternar entre torta e rosca usando o método abaixo.
          pieChartTurno.Doughnut(arrPieData, pieOptions);
        };  
      };
    function graficoLine(){
      pubCompetencia=(document.getElementById("cbCompetencia").value).split("|");
      clsJs   = jsString("lote");  
      clsJs.add("rotina"      , "biKmDividePorInfracao" );
      clsJs.add("login"       , jsPub[0].usr_login      );
      clsJs.add("coduni"      , pubCodUni               );
      clsJs.add("codpol"      , pubCodPol               );
      clsJs.add("levpes"      , pubLevPes               );
      clsJs.add("dtIni"       , pubCompetencia[1]       ); 
      clsJs.add("dtFim"       , pubCompetencia[0]       );       
      fd = new FormData();
      fd.append("principal"   , clsJs.fim());
      msg     = requestPedido("Trac_BiInfracoes.php",fd); 
      retPhp  = JSON.parse(msg);
      if( retPhp[0].retorno == "OK" ){
        tam=retPhp[0]["dados"].length;
        var arrLabel  = [];
        var arrData   = [];
        // Estes para chart
        var arrKm       = [];
        var arrInfracao = [];
        for( var lin=0;lin<tam;lin++ ){
          arrLabel.push(retPhp[0]["dados"][lin]["ANOMES"]);
          arrData.push(retPhp[0]["dados"][lin]["MEDIA"]);
          arrKm.push(retPhp[0]["dados"][lin]["KM"]/1000);
          arrInfracao.push(retPhp[0]["dados"][lin]["INFRACAO"]);
        };
      };  
      //////////////////////////////////////////////////
      // Para não remontar um canvas em cima do outro //
      //////////////////////////////////////////////////
      if(document.getElementById("lineChart") != undefined ){
        document.getElementById("lineChart").remove();
        ceCanvas              = document.createElement("canvas");
        ceCanvas.id           = "lineChart";
        ceCanvas.style.height ="250px";
        document.getElementById("divLineChart").appendChild(ceCanvas);
      };  
      //
      //
      var areaChartCanvas = document.getElementById("lineChart").getContext("2d");
      var areaChart       = new Chart(areaChartCanvas)
      var areaChartData = {
        //labels  : ['January', 'February', 'March', 'April', 'May', 'June', 'July'],     Exemplo original
        labels  : arrLabel,
        datasets: [
          {
            label               : 'Electronics',
            fillColor           : 'rgba(210, 214, 222, 1)',
            strokeColor         : 'rgba(210, 214, 222, 1)',
            pointColor          : 'rgba(210, 214, 222, 1)',
            pointStrokeColor    : '#c1c7d1',
            pointHighlightFill  : '#fff',
            pointHighlightStroke: 'rgba(220,220,220,1)',
            //data                : [65, 59, 80, 81, 56, 55, 40]    Exemplo original
            data                : arrData
          }
          /*
          Para quando se quer um comparativo ( duas linhas no grafico )
          ,
          {
            label               : 'Digital Goods',
            fillColor           : 'rgba(60,141,188,0.9)',
            strokeColor         : 'rgba(60,141,188,0.8)',
            pointColor          : '#3b8bba',
            pointStrokeColor    : 'rgba(60,141,188,1)',
            pointHighlightFill  : '#fff',
            pointHighlightStroke: 'rgba(60,141,188,1)',
            data                : [28, 48, 40, 19, 86, 27, 90]
          }
          */
        ]
      };
      areaChart.Line(areaChartData, areaChartOptions)
      //-------------
      //- LINE CHART -
      //--------------
      var lineChartCanvas          = document.getElementById("lineChart").getContext("2d");
      var lineChart                = new Chart(lineChartCanvas)
      var lineChartOptions         = areaChartOptions
      lineChartOptions.datasetFill = false
      lineChart.Line(areaChartData, lineChartOptions)
      //----------------------------
      //- Iniciando aqui o BAR CHART
      //----------------------------
      //////////////////////////////////////////////////
      // Para não remontar um canvas em cima do outro //
      //////////////////////////////////////////////////
      if(document.getElementById("barChart") != undefined ){
        document.getElementById("barChart").remove();
        ceCanvas              = document.createElement("canvas");
        ceCanvas.id           = "barChart";
        ceCanvas.style.height ="230px";
        document.getElementById("divBarChart").appendChild(ceCanvas);
      };  
      
      var areaChartCanvas = document.getElementById("barChart").getContext("2d");
      var areaChart       = new Chart(areaChartCanvas);
      var areaChartData = {
        //labels  : ['Janeiro', 'February', 'March', 'April', 'May', 'June', 'July'],
        labels  : arrLabel,
        datasets: [
          {
            label               : 'Infração',
            fillColor           : 'rgba(60,141,188,0.9)',
            strokeColor         : 'rgba(60,141,188,0.8)',
            pointColor          : '#3b8bba',
            pointStrokeColor    : 'rgba(60,141,188,1)',
            pointHighlightFill  : '#fff',
            pointHighlightStroke: 'rgba(60,141,188,1)',
            //data                : [28, 48, 40, 19, 86, 27, 90]
            data                : arrInfracao
          }
        
          /*
          {
            label               : 'Km',
            fillColor           : 'rgba(210, 214, 222, 1)',
            strokeColor         : 'rgba(210, 214, 222, 1)',
            pointColor          : 'rgba(210, 214, 222, 1)',
            pointStrokeColor    : '#c1c7d1',
            pointHighlightFill  : '#fff',
            pointHighlightStroke: 'rgba(220,220,220,1)',
            //data                : [65, 59, 80, 81, 56, 55, 40]
            data                : arrKm
          }
          ,
          {
            label               : 'Infração',
            fillColor           : 'rgba(60,141,188,0.9)',
            strokeColor         : 'rgba(60,141,188,0.8)',
            pointColor          : '#3b8bba',
            pointStrokeColor    : 'rgba(60,141,188,1)',
            pointHighlightFill  : '#fff',
            pointHighlightStroke: 'rgba(60,141,188,1)',
            //data                : [28, 48, 40, 19, 86, 27, 90]
            data                : arrInfracao
          }
          */
        ]
      };
      var barChartCanvas                   = document.getElementById("barChart").getContext("2d");
      var barChart                         = new Chart(barChartCanvas)
      var barChartData                     = areaChartData
      barChartData.datasets[0].fillColor   = '#00a65a'
      barChartData.datasets[0].strokeColor = '#00a65a'
      barChartData.datasets[0].pointColor  = '#00a65a'
      var barChartOptions                  = {
        scaleBeginAtZero        : true,               //Boolean - Whether the scale should start at zero, or an order of magnitude down from the lowest value
        scaleShowGridLines      : true,               //Boolean - Whether grid lines are shown across the chart
        scaleGridLineColor      : 'rgba(0,0,0,.05)',  //String - Colour of the grid lines
        scaleGridLineWidth      : 1,                  //Number - Width of the grid lines
        scaleShowHorizontalLines: true,               //Boolean - Whether to show horizontal lines (except X axis)
        scaleShowVerticalLines  : true,               //Boolean - Whether to show vertical lines (except Y axis)
        barShowStroke           : true,               //Boolean - If there is a stroke on each bar
        barStrokeWidth          : 2,                  //Number - Pixel width of the bar stroke
        barValueSpacing         : 30,                 //Number - Spacing between each of the X value sets
        barDatasetSpacing       : 1,                  //Number - Spacing between data sets within X values
        //String - A legend template
        legendTemplate          : '<ul class="<%=name.toLowerCase()%>-legend"><% for (var i=0; i<datasets.length; i++){%><li><span style="background-color:<%=datasets[i].fillColor%>"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>',
        responsive              : true, //Boolean - whether to make the chart responsive
        maintainAspectRatio     : true
      }

      barChartOptions.datasetFill = false
      barChart.Bar(barChartData, barChartOptions)
    }
    
    
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

        <div class="form-group" style="width:15%;height:1.5em;float:left;margin-top:0.5em;">
          <select id="cbCompetencia" class="form-control select2" style="width:70%;height:28px;margin-left:3em;">
						<!--
            <option value="201805|201805">MAI/18</option>
            <option value="201806|201805">JUN/18</option>
            <option value="201807|201805">JUL/18</option>
            <option value="201808|201805">AGO/18</option>
            <option value="201809|201805">SET/18</option>
            <option value="201810|201805">OUT/18</option>
            <option value="201811|201805">NOV/18</option>
            <option value="201812|201805">DEZ/18</option>
						-->
          </select>
        </div>
        
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
        <div class="col-md-6">
          <div class="box">
            <div class="box-header with-border">
              <h3 id="infracaoCompet" class="box-title">...</h3>
              <span id="qtosInfracao" class="badge bg-light-blue" style="width:50px;">0</span>
            </div>
            <div id="divInfracao" class="box-body" style="height: 270px; overflow-y:auto;">
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
        <div class="box box-sucess ">
          <div class="box-header with-border" style="height:5em;">
            <table class="table table-bordered">
							<tr>
                <td>
                  <div class="btn-group">
                    <button type="button" class="btn btn-label"><u><b>TOP 10 Motoristas x Infração </b></u></button>
                    <button id="infracaoTop" type="button" class="btn btn-label">Total: 0000 </button>
                    <button onClick="fncInfracaoTop('EV');" type="button" class="btn btn-default">Excesso velocidade</button>
                    <button onClick="fncInfracaoTop('EVC');"type="button" class="btn btn-default">Excesso veloc chuva</button>
                    <button onClick="fncInfracaoTop('FB');"type="button" class="btn btn-default">Freada brusca</button>
                    <button onClick="fncInfracaoTop('ERPM');"type="button" class="btn btn-default">RPM alto</button>
                    <button onClick="fncInfracaoTop('CB');"type="button" class="btn btn-default">Condução banguela</button>
                    <button onClick="fncInfracaoTop('AB');"type="button" class="btn btn-default">Aceleração brusca</button>
                    <button type="button" class="btn btn-default"><a href="#" onClick="window.open('Trac_BiInfracao.php','iframeCorpo');"><i class="fa  fa-plus-square-o"></i> Detalhe</button>
                  </div>
                </td>
              </tr>
            </table>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
            </div>
          </div>
          <div class="box-body" style="padding-top:15px;">
            <div class="row">
              <div class="col-md-12">
                <div id="divTblInfracaoTop" class="chart-responsive">

                </div>
              </div>
              <div id="divKm" class="col-md-4">
              </div>
            </div>
          </div>
        </div>
      </div>
<!--
-->
      <div class="row">
        <div class="box box-sucess ">
          <div class="box-header with-border" style="height:5em;">
            <table class="table table-bordered">
              <tr>
                <td>
                  <div class="btn-group">
                    <button type="button" class="btn btn-label"><u><b>Infrações x Turno</u></b></button>
                    <button id="infracaoTurno" type="button" class="btn btn-label">Total Infrações:0000 </button>
                    <button onClick="fncInfracaoTurno('EV');" type="button" class="btn btn-default">Excesso velocidade</button>
                    <button onClick="fncInfracaoTurno('EVC');"type="button" class="btn btn-default">Excesso veloc chuva</button>
                    <button onClick="fncInfracaoTurno('FB');"type="button" class="btn btn-default">Freada brusca</button>
                    <button onClick="fncInfracaoTurno('ERPM');"type="button" class="btn btn-default">RPM alto</button>
                    <button onClick="fncInfracaoTurno('CB');"type="button" class="btn btn-default">Condução banguela</button>
                    <button onClick="fncInfracaoTurno('AB');"type="button" class="btn btn-default">Aceleração brusca</button>
                    <button type="button" class="btn btn-default"><a href="#" onClick="window.open('Trac_BiMensal.php','iframeCorpo');"><i class="fa  fa-plus-square-o"></i> Detalhe</button>
                  </div>
                </td>
              </tr>
            </table>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
            </div>
          </div>
          
          <div class="box-body" style="padding-top:15px;">
            <div class="row">
              <div class="col-md-8">
                <div id="divPieChartTurno" class="chart-responsive">
                </div>
              </div>
              <div id="divTurno" class="col-md-4">
              </div>
            </div>
          </div>
          
          <!--
          -->
          <!-- LINE CHART -->
          <!--
          <div class="box box-info">
          <div class="col-md-6">                    
          -->
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title" >Média de KM sem Infrações</h3>
            <!--  
            <div class="form-group" style="width:10%;height:1.5em;margin-top:0.5em;">
              <button id="smllDesUni" type="button" class="btn btn-label" style="margin-left:10px;" >Filtrar</button>
            </div>
            -->  
              <button id="smllDesUni" type="button" class="btn btn-label" 
                                                    style="margin-left:10px;" 
                                                    onClick="graficoLine();">Filtrar</button>
              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
              </div>
            </div>
            <div class="box-body">
              <div id="divLineChart" class="chart">
                <canvas id="lineChart" style="height:250px"></canvas>
              </div>
            </div>
          </div>
          <!--
          -->
          <div class="box box-success">
            <div class="box-header with-border">
              <h3 class="box-title">Comparativo Mensal Infrações Velocidade</h3>

              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
              </div>
            </div>
            <div class="box-body">
              <div id="divBarChart" class="chart">
                <canvas id="barChart" style="height:230px"></canvas>
              </div>
            </div>
            <!-- /.box-body -->
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
