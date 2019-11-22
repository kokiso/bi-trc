<?php
  session_start();
  if( isset($_POST["principal"]) ){
    try{     
      require("classPhp/conectaSqlServer.class.php");
      require("classPhp/validaJson.class.php"); 
      require("classPhp/removeAcento.class.php"); 

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
        $codmes   = $lote[0]->compet;
        $classe   = new conectaBd();
        $classe->conecta($lote[0]->login);
        ///////////////////////////////////////////////////////////
        //   Bi por tipo de infracao TURNO                       //
        ///////////////////////////////////////////////////////////
        if( $rotina=="biInfracaoTurno" ){
          switch( $lote[0]->infracao ){
            case "AB":    $alias="A.BIABM";  $table="BI_ACELERBRUSCAMES";  break;
            case "CB":    $alias="A.BICBM";  $table="BI_CONDUCAOBANGMES";  break;
            case "ERPM":  $alias="A.BIRAM";  $table="BI_RPMALTOMES";       break;            
            case "EV":    $alias="A.BIEVM";  $table="BI_EXCESSOVELOCMES";  break;
            case "EVC":   $alias="A.BIEVCM"; $table="BI_EXCESSOVELCHMES";  break;
            case "FB":    $alias="A.BIFBM";  $table="BI_FREADABRUSCAMES";  break;
          };
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
          switch( $lote[0]->infracao ){
            case "AB":    $alias="A.BIABM";  $table="BI_ACELERBRUSCAMES";  break;
            case "CB":    $alias="A.BICBM";  $table="BI_CONDUCAOBANGMES";  break;
            case "ERPM":  $alias="A.BIRAM";  $table="BI_RPMALTOMES";       break;            
            case "EV":    $alias="A.BIEVM";  $table="BI_EXCESSOVELOCMES";  break;
            case "EVC":   $alias="A.BIEVCM"; $table="BI_EXCESSOVELCHMES";  break;
            case "FB":    $alias="A.BIFBM";  $table="BI_FREADABRUSCAMES";  break;
          };
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
          $sql="SELECT P.POL_CODIGO,P.POL_NOME
                  FROM UNIDADE A
                  LEFT OUTER JOIN POLO P ON A.UNI_CODPOL=P.POL_CODIGO
                 LEFT OUTER JOIN USUARIOSISTEMA U ON A.UNI_CODUSR=U.US_CODIGO
                  LEFT OUTER JOIN USUARIOUNIDADE UU ON A.UNI_CODIGO=UU.UU_CODUNI AND UU.UU_CODUSR=2
                 WHERE ((UNI_ATIVO='S') AND (COALESCE(UU.UU_ATIVO,'')='S'))
                GROUP BY P.POL_CODIGO,P.POL_NOME
                  ORDER BY POL_NOME";
          $classe->msgSelect(false);
          $retCls=$classe->selectAssoc($sql);
          if( $retCls['retorno'] != "OK" ){
            $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';  
          } else { 
            $retorno='[{"retorno":"OK","dados":'.json_encode($retCls['dados']).',"erro":""}]'; 
          };  
        };  
        ///////////////////////////////////////////////////////////
        //   Buscando apenas as unidades que usuario tem direito //
        ///////////////////////////////////////////////////////////
        if( $rotina=="quaisUnidade" ){
          $sql="SELECT UNI_CODIGO,UNI_APELIDO
                  FROM UNIDADE A
                  LEFT OUTER JOIN USUARIOSISTEMA U ON A.UNI_CODUSR=U.US_CODIGO
                  LEFT OUTER JOIN USUARIOUNIDADE UU ON A.UNI_CODIGO=UU.UU_CODUNI AND UU.UU_CODUSR=".$_SESSION['usr_codigo']."
                 WHERE ((UNI_ATIVO='S') AND (COALESCE(UU.UU_ATIVO,'')='S'))
                 ORDER BY UNI_APELIDO";
          $classe->msgSelect(false);
          $retCls=$classe->selectAssoc($sql);
          if( $retCls['retorno'] != "OK" ){
            $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';  
          } else { 
            $retorno='[{"retorno":"OK","dados":'.json_encode($retCls['dados']).',"erro":""}]'; 
          };  
        };  
        ////////////////
        // BI CONTAR  //
        ////////////////
        if( $rotina=="biContar" ){
          $sql="";
          if( $lote[0]->qualSelect=="contarKm" ){  
            $sql.="SELECT COALESCE(SUM(A.BIKMM_TOTAL),0) AS QTOS";
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
          };
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
          if( $lote[0]->qualSelect=="contarPolo" ){  
            $sql.="SELECT COUNT(DISTINCT(P.POL_CODIGO)) AS QTOS";
            $sql.="  FROM UNIDADE A";
            $sql.="  LEFT OUTER JOIN POLO P ON A.UNI_CODPOL=P.POL_CODIGO";
            $sql.="  LEFT OUTER JOIN USUARIOSISTEMA U ON A.UNI_CODUSR=U.US_CODIGO";
            $sql.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON A.UNI_CODIGO=UU.UU_CODUNI AND UU.UU_CODUSR=".$_SESSION['usr_codigo'];
            if( $lote[0]->codpol =="*" ){            
              $sql.=" WHERE ((UNI_ATIVO='S') AND (COALESCE(UU.UU_ATIVO,'')='S') AND (P.POL_CODGRP IN".$_SESSION['usr_grupos']."))";
            }else {
              $sql.=" WHERE ((UNI_ATIVO='S') AND (P.POL_CODIGO='".$lote[0]->codpol."') AND (COALESCE(UU.UU_ATIVO,'')='S'))";  
            };
          };
          
          if( $lote[0]->qualSelect=="contarUnidade" ){  
            $sql.="SELECT COUNT(A.UNI_CODIGO) AS QTOS";
            $sql.="  FROM UNIDADE A";
            $sql.="  LEFT OUTER JOIN USUARIOSISTEMA U ON A.UNI_CODUSR=U.US_CODIGO";
            $sql.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON A.UNI_CODIGO=UU.UU_CODUNI AND UU.UU_CODUSR=".$_SESSION['usr_codigo'];
            if( $lote[0]->coduni >0 ){
              $sql.="  WHERE ((A.UNI_ATIVO='S') AND (A.UNI_CODIGO=".$lote[0]->coduni.") AND (COALESCE(UU.UU_ATIVO,'')='S'))";
            } elseif($lote[0]->codpol != "*" ){              
              $sql.="  WHERE ((A.UNI_ATIVO='S') AND (A.UNI_CODPOL='".$lote[0]->codpol."') AND (COALESCE(UU.UU_ATIVO,'')='S'))";
            } else {
              $sql.="  WHERE ((UNI_ATIVO='S') AND (COALESCE(UU.UU_ATIVO,'')='S'))";  
            };
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
        buscarCompetencia();
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
      //
      ////////////////////////////////////////////////////////////////////////////
      // Esta function inicia o BI com todas unidades que o usuario tem direito //
      // Tb eh usada qdo selecionado filtro por uma unidade                     //
      ////////////////////////////////////////////////////////////////////////////
      function iniciarBi(ibCodUni,ibDesUni,ibCodPol,ibDesPol){
        // document.getElementById("infracaoCompet").innerHTML="Infrações "+document.getElementById("cbCompetencia").options[document.getElementById("cbCompetencia").selectedIndex].text+" ";
        pubCodUni=ibCodUni;
        pubDesUni=ibDesUni;
        pubCodPol=ibCodPol;
        pubDesPol=ibDesPol;
        pubLevPes=document.getElementById("cbLevePesado").value;
        fncContar("contarMotorista" ,"qtosMtr",pubCodUni,pubCodPol,"*");
        fncContar("contarVeiculo"   ,"qtosVcl",pubCodUni,pubCodPol,pubLevPes);
        fncContar("contarPolo"      ,"qtosPol",pubCodUni,pubCodPol,"*");
        fncContar("contarUnidade"   ,"qtosUni",pubCodUni,pubCodPol,"*");
				fncContar("contarKm"        ,"qtosKm" ,pubCodUni,pubCodPol,"*");
        
        fncFiltrarTableSimples("bisMotoristaUni"  ,"tblUniMtr","divUniMtr"    ,"qtosUniMtr"   ,pubCodUni,pubCodPol,"*");
        fncFiltrarTableSimples("bisVeiculoUni"    ,"tblPolMtr","divPolMtr"    ,"qtosPolMtr"   ,pubCodUni,pubCodPol,pubLevPes);
        fncFiltrarTableInfracao("infracao"        ,"tblinf"   ,"divInfracao"  ,"qtosInfracao" ,pubCodUni,pubCodPol,pubLevPes);
        document.getElementById("smllDesUni").innerHTML=ibDesUni;
      };
      //    
      //  
      function fncContar(qualSelect,qualSpan,qualCodUni,qualCodPol,qualLevPes){
        clsJs   = jsString("lote");  
        clsJs.add("rotina"      , "biContar"                                      );
        clsJs.add("login"       , jsPub[0].usr_login                              );
        clsJs.add("qualSelect"  , qualSelect                                      );
        clsJs.add("coduni"      , qualCodUni                                      );
        clsJs.add("codpol"      , qualCodPol                                      );
        clsJs.add("levpes"      , qualLevPes                                      );
        clsJs.add("compet"      , document.getElementById("cbCompetencia").value  );      
        fd = new FormData();
        fd.append("principal" , clsJs.fim());
        //msg     = requestPedido("Trac_BiVisaoGeral.php",fd); 
				msg     = requestPedido("Trac_BiVeiculos.php",fd); 

        retPhp  = JSON.parse(msg);
        if( retPhp[0].retorno == "OK" ){
          document.getElementById(qualSpan).innerHTML=parseInt(retPhp[0]["dados"][0]);
          if(qualSpan=="qtosKm")
            document.getElementById(qualSpan).innerHTML=parseInt(parseFloat(retPhp[0]["dados"][0]));
        };  
      };
      //
      //  
      //////////////////////////////////////
      // Somente tabelas com duas colunas //  
      //////////////////////////////////////
      function fncFiltrarTableSimples(qualSelect,qualTbl,qualDiv,qualTot,qualCodUni,qualCodPol,qualLevPes){
        clsJs   = jsString("lote");  
        clsJs.add("rotina"      , "biSimples"                                     );
        clsJs.add("login"       , jsPub[0].usr_login                              );
        clsJs.add("qualSelect"  , qualSelect                                      );
        clsJs.add("coduni"      , qualCodUni                                      );      
        clsJs.add("codpol"      , qualCodPol                                      );
        clsJs.add("levpes"      , qualLevPes                                      );      
        clsJs.add("compet"      , document.getElementById("cbCompetencia").value  );      
        fd = new FormData();
        fd.append("principal" , clsJs.fim());
        //msg     = requestPedido("Trac_BiVisaoGeral.php",fd); 
				msg     = requestPedido("Trac_BiVeiculos.php",fd); 

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
      //
      //  
      //////////////////////////////////////
      // Somente tabelas infracao         //  
      //////////////////////////////////////
      function fncFiltrarTableInfracao(qualSelect,qualTbl,qualDiv,qualTot,qualCodUni,qualCodPol,qualLevPes){
        clsJs   = jsString("lote");  
        clsJs.add("rotina"      , "biInfracao"                                    );
        clsJs.add("login"       , jsPub[0].usr_login                              );
        clsJs.add("qualSelect"  , qualSelect                                      );
        clsJs.add("coduni"      , qualCodUni                                      );
        clsJs.add("codpol"      , qualCodPol                                      );
        clsJs.add("levpes"      , qualLevPes                                      );      
        clsJs.add("compet"      , document.getElementById("cbCompetencia").value  );      
        fd = new FormData();
        fd.append("principal" , clsJs.fim());
        //msg     = requestPedido("Trac_BiVisaoGeral.php",fd); 
				msg     = requestPedido("Trac_BiVeiculos.php",fd); 
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
						document.getElementById("qtosInfra").innerHTML=totQtos;						
          };
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
        };
      };
      ///////////////////////////
      // Buscando competencias //
      ///////////////////////////
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
      ///////////////////////////////////////////////////////////
      // Buscando apenas as unidades que o usuario tem direito //
      ///////////////////////////////////////////////////////////
      function buscarUni(){
        clsJs   = jsString("lote");  
        clsJs.add("rotina"      , "quaisUnidade"                                  );
        clsJs.add("login"       , jsPub[0].usr_login                              );
        clsJs.add("compet"      , document.getElementById("cbCompetencia").value  );      
        fd = new FormData();
        fd.append("principal" , clsJs.fim());
        //msg     = requestPedido("Trac_BiVisaoGeral.php",fd); 
				msg     = requestPedido("Trac_BiVeiculos.php",fd); 
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
        clsJs.add("rotina"      , "quaisPolo"                                     );
        clsJs.add("login"       , jsPub[0].usr_login                              );
        clsJs.add("compet"      , document.getElementById("cbCompetencia").value  );      
        fd = new FormData();
        fd.append("principal" , clsJs.fim());
        //msg     = requestPedido("Trac_BiVisaoGeral.php",fd); 
				msg     = requestPedido("Trac_BiVeiculos.php",fd); 
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
        clsJs.add("rotina"        , "biInfracaoTop"                                 );
        clsJs.add("login"         , jsPub[0].usr_login                              );
        clsJs.add("infracao"      , qualInfracao                                    );
        clsJs.add("coduni"        , pubCodUni                                       );      
        clsJs.add("codpol"        , pubCodPol                                       );
        clsJs.add("levpes"        , pubLevPes                                       );      
        clsJs.add("compet"        , document.getElementById("cbCompetencia").value  );      
        fd = new FormData();
        fd.append("principal" , clsJs.fim());
        //msg = requestPedido("Trac_BiVisaoGeral.php",fd); 
				msg = requestPedido("Trac_BiVeiculos.php",fd); 
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
        fd.append("principal" , clsJs.fim());
        //msg     = requestPedido("Trac_BiVisaoGeral.php",fd); 
				msg     = requestPedido("Trac_BiVeiculos.php",fd); 
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
      ////////////////////////
      // Infracao por turno //
      ////////////////////////    
      function fncInfracaoTurno(qualInfracao){  
        clsJs   = jsString("lote");  
        clsJs.add("rotina"      , "biInfracaoTurno"                               );
        clsJs.add("login"       , jsPub[0].usr_login                              );
        clsJs.add("infracao"    , qualInfracao                                    );
        clsJs.add("coduni"      , pubCodUni                                       );      
        clsJs.add("codpol"      , pubCodPol                                       );
        clsJs.add("levpes"      , pubLevPes                                       );      
        clsJs.add("compet"      , document.getElementById("cbCompetencia").value  );      
        fd = new FormData();
        fd.append("principal" , clsJs.fim());
        //msg     = requestPedido("Trac_BiVisaoGeral.php",fd); 
				msg     = requestPedido("Trac_BiVeiculos.php",fd); 
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
              case "M": lgdNome=" MANHA"; break;
              case "T": lgdNome=" TARDE"; break;
              case "N": lgdNome=" NOITE"; break;
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
      /////////////////////////////////////////////
			// Funcao para trocar de Unidade para Polo //
      /////////////////////////////////////////////
      function chngMotUP(){
        if( document.getElementById("cbMotUP").value=="UNI" ){
          fncFiltrarTableSimples("bisMotoristaUni" ,"tblUniMtr","divUniMtr"    ,"qtosUniMtr"   ,pubCodUni,pubCodPol,"*");
        } else {
          fncFiltrarTableSimples("bisMotoristaPol" ,"tblUniMtr","divUniMtr"    ,"qtosUniMtr"   ,pubCodUni,pubCodPol,"*");
        }    
      }; 
      function chngVeiUP(){
        if( document.getElementById("cbVeiUP").value=="UNI" ){
          fncFiltrarTableSimples("bisVeiculoUni" ,"tblPolMtr","divPolMtr"    ,"qtosPolMtr"   ,pubCodUni,pubCodPol,pubLevPes);
        } else {
          fncFiltrarTableSimples("bisVeiculoPol" ,"tblPolMtr","divPolMtr"    ,"qtosPolMtr"   ,pubCodUni,pubCodPol,pubLevPes);
        }    
      }; 
      function chngCompetencia(){
        fncContar("contarKm"        ,"qtosKm" ,pubCodUni,pubCodPol,"*");  
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
              <!--
							<div class="progress">
                <div class="progress-bar" style="width: 60%"></div>
              </div>
                  <span class="progress-description">
                    Aumento de 60 % 
                  </span> -->
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
              <div class="progress">
                <div class="progress-bar" style="width: 30%"></div>
              </div>
                  <span class="progress-description">
                    Aumento de 30 % 
                  </span>
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
              <div class="progress">
                <div class="progress-bar" style="width: 10%"></div>
              </div>
                  <span class="progress-description">
                    Aumento de 10 % 
                  </span>
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
              <span id="qtosHMov" class="info-box-number"></span>
              <div class="progress">
                <div class="progress-bar" style="width: 20%"></div>
              </div>
                  <span class="progress-description">
                    Aumento de 20 % 
                  </span>
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
              <div class="progress">
                <div class="progress-bar" style="width: 20%"></div>
              </div>
                  <span class="progress-description">
                    Aumento de 20 % 
                  </span>
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
                  <div class="chart-responsive">
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
      <!--
      <div class="row">
        <div class="box box-sucess collapsed-box">
          <div class="box-header with-border" style="height:5em;">
            <table class="table table-bordered">
              <tr>
                <td>
                  <div class="btn-group">
                    <button type="button" class="btn btn-label"><u><b>KM Percorrido</u></b></button>
                    <button onClick="fncKilometragem('P');" type="button" class="btn btn-default">Polo</button>
                    <button onClick="fncKilometragem('U');"type="button" class="btn btn-default">Unidade</button>
                    <button id="totalKm" type="button" class="btn btn-label">Total KM`s: 00000</button>
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
                <div id="divPieChartKm" class="chart-responsive">
                </div>
              </div>
              <div id="divKm" class="col-md-4">
              </div>
            </div>
          </div>
          
        </div>
      </div>
      
      <div class="row">
        <div class="box box-sucess collapsed-box">
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
                    <button type="button" class="btn btn-label">Detalhe</button>
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

      <div class="row"">
        <div class="box box-sucess collapsed-box">
          <div class="box-header with-border" style="height:5em;">
            <table class="table table-bordered">
              <tr>
                <td>
                  <div class="btn-group">
                    <button type="button" class="btn btn-label"><u><b>Infrações x Turno</u></b></button>
                    <button id="infracaoTurno" type="button" class="btn btn-label">Total Infrações: 0000 </button>
                    <button onClick="fncInfracaoTurno('EV');" type="button" class="btn btn-default">Excesso velocidade</button>
                    <button onClick="fncInfracaoTurno('EVC');"type="button" class="btn btn-default">Excesso veloc chuva</button>
                    <button onClick="fncInfracaoTurno('FB');"type="button" class="btn btn-default">Freada brusca</button>
                    <button onClick="fncInfracaoTurno('ERPM');"type="button" class="btn btn-default">RPM alto</button>
                    <button onClick="fncInfracaoTurno('CB');"type="button" class="btn btn-default">Condução banguela</button>
                    <button onClick="fncInfracaoTurno('AB');"type="button" class="btn btn-default">Aceleração brusca</button>
                    <button type="button" class="btn btn-label">Detalhe</button>
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

        </div>
      </div>
			-->
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
