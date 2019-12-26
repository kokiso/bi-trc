<?php
  class selectRepetido{
    var $retorno="";
    ////////////////////////////////////////////////////////////////////////////////////////
    //$param Recebe parametros da funcao chamadora, o primeiro parametro deve ser o login //
    ////////////////////////////////////////////////////////////////////////////////////////
    function qualSelect($qual,$param, $busca = null){
      $sql      = "";
      /////////////////////////////////////////////////////////////
      // $execSql Opcional para quando se quer retornar o select //
      /////////////////////////////////////////////////////////////
      $execSql  = true;
      //
      //
      $expld = explode("|",$param);
      switch ($qual){
        ///////////////////////////////////////////////
        // Facilitador select devido qtdade de regisros
        ///////////////////////////////////////////////
        case "intervalo":        
          $bwI=0;
          $bwF=0;
					if( $expld[0]==201804 ){
						$bwI=595980002;
						$bwF=648591497;
						//$between=true;
					};		
					if( $expld[0]==201805 ){
						$bwI=624197131;
						$bwF=655786470;
						//$between=true;
					};		
					if( $expld[0]==201806 ){
						$bwI=652759482;
						$bwF=686622947;
					};		
					if( $expld[0]==201807 ){
						$bwI=683670892;
						$bwF=720820768;
					};		
					if( $expld[0]==201808 ){
						$bwI=716372693;
						$bwF=753106302;
					};		
					if( $expld[0]==201809 ){
						$bwI=749858752;
						$bwF=782625217;													
					};		
					if( $expld[0]==201810 ){
						$bwI=782095793;													
						$bwF=820993179;
					};		
					if( $expld[0]==201811 ){
						$bwI=816418011;													
						$bwF=847698077;
					};		
					if( $expld[0]==201812 ){
						$bwI=846760689;													
						$bwF=875424963;
					};		
					if( $expld[0]==201901 ){
						$bwI=873029326;													
						$bwF=901594959;
					};		
					if( $expld[0]==201902 ){
						$bwI=900347928;
						$bwF=927435774;												
					};		
					if( $expld[0]==201903 ){
						$bwI=925625766;
						$bwF=957245478;
					};		

					if( $expld[0]==201904 ){
						$bwI=954950092;
						$bwF=990403638;
					};		

					if( $expld[0]==201905 ){
						$bwI=988003763;												//select min(mvm_posicao) from movimento where mvm_anomes=201905;			
						$bwF=1025833815;												//select max(mvm_posicao) from movimento where mvm_anomes=201905; 
					};		

					if( $expld[0]==201906 ){
						$bwI=1023467810;												//select min(mvm_posicao) from movimento where mvm_anomes=201905;			
						$bwF=1060800865;												//select max(mvm_posicao) from movimento where mvm_anomes=201905; 
					};		

					if( $expld[0]==201907 ){
						$bwI=1057058906;												//select min(mvm_posicao) from movimento where mvm_anomes=201905;			
						$bwF=1097205579;												//select max(mvm_posicao) from movimento where mvm_anomes=201907;
					};		
					
					if( $expld[0]==201908 ){
						$bwI=1093268460;												//select min(mvm_posicao) from movimento where mvm_anomes=201908;			
						$bwF=1999999999;												//select max(mvm_posicao) from movimento where mvm_anomes=201905;
					};		

          return $bwI."|".$bwF;
          break;
        //////////////////////////////
        // Trac_BiVisaoGeral.php    //
        // Trac_BiMotorista.php     //
        //////////////////////////////
        case "contarKm":
          $execSql=false;
          $sql.="SELECT COALESCE(SUM(A.BIPRDM_ODOMETROFIM-BIPRDM_ODOMETROINI),0) AS QTOS"; 
          $sql.="  FROM BI_PRODUTIVIDADEVEIMES A";
          $sql.="  LEFT OUTER JOIN VEICULO VCL ON A.BIPRDM_CODVCL=VCL.VCL_CODIGO";
          $sql.="  LEFT OUTER JOIN UNIDADE UNI ON VCL.VCL_CODUNI=UNI.UNI_CODIGO";
          $sql.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON UNI.UNI_CODIGO=UU.UU_CODUNI AND UU.UU_CODUSR=".$_SESSION['usr_codigo'];					
          $sql.=" WHERE (A.BIPRDM_ANOMES=".$expld[3].")";
	  $sql.=" AND A.BIPRDM_ODOMETROFIM-BIPRDM_ODOMETROINI < 3000";	      
          $sql.="   AND (COALESCE(UU.UU_ATIVO,'')='S')";
          if( $expld[1] >0 ){
            $sql.="  AND (UNI.UNI_CODIGO=".$expld[1].")";  
          }; 
          if($expld[2] != "*" ){
            $sql.="  AND (UNI.UNI_CODPOL='".$expld[2]."')";  
          };
          if ($expld[4] != "*") {
            $sql.=" AND VCL.VCL_CODGPO =".$expld[4];
           }
          return $sql;
          break; 
        case "contarMotorista":        
          $execSql=false;
          $sql.="SELECT COUNT(A.BIPRDM_CODMTR) AS QTOS"; 
          $sql.="  FROM BI_PRODUTIVIDADEMOTMES A";
          $sql.="  LEFT OUTER JOIN MOTORISTA MTR ON A.BIPRDM_CODMTR=MTR.MTR_CODIGO";
          $sql.="  LEFT OUTER JOIN UNIDADE UNI ON MTR.MTR_CODUNI=UNI.UNI_CODIGO";
          $sql.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON UNI.UNI_CODIGO=UU.UU_CODUNI AND UU.UU_CODUSR=".$_SESSION['usr_codigo'];          
          $sql.=" WHERE (A.BIPRDM_ANOMES=".$expld[3].")";
          $sql.="   AND (COALESCE(UU.UU_ATIVO,'')='S')";
          if( $expld[1] >0 ){
            $sql.="  AND (UNI.UNI_CODIGO=".$expld[1].")";  
          }; 
          if($expld[2] != "*" ){
            $sql.="  AND (UNI.UNI_CODPOL='".$expld[2]."')";  
          };
          return $sql;
          break;    
        case "contarVeiculo":        
          $execSql=false;
          $sql.="SELECT COUNT(A.BIPRDM_CODVCL) AS QTOS"; 
          $sql.="  FROM BI_PRODUTIVIDADEVEIMES A";
          $sql.="  LEFT OUTER JOIN VEICULO VCL ON A.BIPRDM_CODVCL=VCL.VCL_CODIGO";
          if ($expld[4] != "*") {
            $sql.=" AND VCL.VCL_CODGPO =".$expld[4];
           }
          $sql.="  LEFT OUTER JOIN UNIDADE UNI ON VCL.VCL_CODUNI=UNI.UNI_CODIGO";
          $sql.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON UNI.UNI_CODIGO=UU.UU_CODUNI AND UU.UU_CODUSR=".$_SESSION['usr_codigo'];          
          $sql.=" WHERE (A.BIPRDM_ANOMES=".$expld[3].")";
          $sql.="   AND (COALESCE(UU.UU_ATIVO,'')='S')";
          if( $expld[1] >0 ){
            $sql.="  AND (UNI.UNI_CODIGO=".$expld[1].")";  
          }; 
          if($expld[2] != "*" ){
            $sql.="  AND (UNI.UNI_CODPOL='".$expld[2]."')";  
          };
          return $sql;
          break;
        case "contarHoraRodando":
          $execSql=false;
          $sql.="SELECT COALESCE( (SUM(CAST(A.BIPRDM_TEMPORODANDO AS BIGINT)) /3600) ,0) AS QTOS"; 
          $sql.="  FROM BI_PRODUTIVIDADEVEIMES A";
          $sql.="  LEFT OUTER JOIN VEICULO VCL ON A.BIPRDM_CODVCL=VCL.VCL_CODIGO";
          $sql.="  LEFT OUTER JOIN UNIDADE UNI ON VCL.VCL_CODUNI=UNI.UNI_CODIGO";
          $sql.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON UNI.UNI_CODIGO=UU.UU_CODUNI AND UU.UU_CODUSR=".$_SESSION['usr_codigo'];					
          $sql.=" WHERE (A.BIPRDM_ANOMES=".$expld[3].")";
	  $sql.=" AND A.BIPRDM_ODOMETROFIM-BIPRDM_ODOMETROINI < 3000";
          $sql.="   AND (COALESCE(UU.UU_ATIVO,'')='S')";
          if( $expld[1] >0 ){
            $sql.="  AND (UNI.UNI_CODIGO=".$expld[1].")";  
          }; 
          if($expld[2] != "*" ){
            $sql.="  AND (UNI.UNI_CODPOL='".$expld[2]."')";  
          };
          if ($expld[4] != "*") {
            $sql.=" AND VCL.VCL_CODGPO =".$expld[4];
           }
          return $sql;
          break; 
        case "contarHoraParado":
          $execSql=false;
          $sql.="SELECT COALESCE( (SUM(CAST(A.BIPRDM_TEMPOPARADO AS BIGINT)) /3600) ,0) AS QTOS"; 
          $sql.="  FROM BI_PRODUTIVIDADEVEIMES A";
          $sql.="  LEFT OUTER JOIN VEICULO VCL ON A.BIPRDM_CODVCL=VCL.VCL_CODIGO";
          $sql.="  LEFT OUTER JOIN UNIDADE UNI ON VCL.VCL_CODUNI=UNI.UNI_CODIGO";
          $sql.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON UNI.UNI_CODIGO=UU.UU_CODUNI AND UU.UU_CODUSR=".$_SESSION['usr_codigo'];					
          $sql.=" WHERE (A.BIPRDM_ANOMES=".$expld[3].")";
          $sql.="   AND (COALESCE(UU.UU_ATIVO,'')='S')";
          if( $expld[1] >0 ){
            $sql.="  AND (UNI.UNI_CODIGO=".$expld[1].")";  
          }; 
          if($expld[2] != "*" ){
            $sql.="  AND (UNI.UNI_CODPOL='".$expld[2]."')";  
          };
          if ($expld[4] != "*") {
            $sql.=" AND VCL.VCL_CODGPO =".$expld[4];
           }
          return $sql;
          break; 
        //////////////////////////////
        // Trac_BiVisaoGeral.php    //
        // Trac_BiInfracoes.php     //
        // Trac_BiMotorista.php     //
        //////////////////////////////
        case "quaisPolo":
          $sql.="SELECT P.POL_CODIGO,P.POL_NOME";
          $sql.="  FROM UNIDADE A";
          $sql.="  LEFT OUTER JOIN POLO P ON A.UNI_CODPOL=P.POL_CODIGO";
          $sql.=" LEFT OUTER JOIN USUARIOSISTEMA U ON A.UNI_CODUSR=U.US_CODIGO";
          $sql.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON A.UNI_CODIGO=UU.UU_CODUNI AND UU.UU_CODUSR=".$_SESSION['usr_codigo'];
          $sql.=" WHERE ((UNI_ATIVO='S') AND (COALESCE(UU.UU_ATIVO,'')='S'))";
          $sql.=" GROUP BY P.POL_CODIGO,P.POL_NOME";
          $sql.=" ORDER BY POL_NOME";
//file_put_contents("aaa.xml",$sql);					
          break;
        //////////////////////////////
        // Trac_BiVisaoGeral.php    //
        // Trac_BiInfracoes.php     //
        // Trac_BiMotorista.php     //
        //////////////////////////////
        case "qtosPolo":
          $execSql=false;
          $sql.="SELECT COUNT(DISTINCT(P.POL_CODIGO)) AS QTOS";
          $sql.="  FROM UNIDADE A";
          $sql.="  LEFT OUTER JOIN POLO P ON A.UNI_CODPOL=P.POL_CODIGO";
          $sql.="  LEFT OUTER JOIN USUARIOSISTEMA U ON A.UNI_CODUSR=U.US_CODIGO";
          $sql.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON A.UNI_CODIGO=UU.UU_CODUNI AND UU.UU_CODUSR=".$_SESSION['usr_codigo'];
          if( $expld[1] =="*" ){            
            $sql.=" WHERE ((UNI_ATIVO='S') AND (COALESCE(UU.UU_ATIVO,'')='S') AND (P.POL_CODGRP IN".$_SESSION['usr_grupos']."))";
          }else {
            $sql.=" WHERE ((UNI_ATIVO='S') AND (P.POL_CODIGO='".$expld[1]."') AND (COALESCE(UU.UU_ATIVO,'')='S'))";  
          };
          return $sql;
          break;
//////////////////////////////////////////////////////////////////////////////
        case "qtosGpo":
          $execSql=false;
          $sql.="SELECT COUNT(DISTINCT GPO_CODIGO) from GRUPOOPERACIONAL INNER JOIN GRUPOOPERACIONALUNIDADE on GOU_CODGPO = GRUPOOPERACIONAL.GPO_CODIGO";
          if ($expld[1] != 0) {
            $sql.=" AND GOU_CODUNI = ".$expld[1];
          } else {
            $sql.=" AND GOU_CODUNI IN (SELECT UU_CODUNI FROM USUARIOUNIDADE WHERE UU_CODUSR =".$_SESSION['usr_codigo']." AND UU_ATIVO = 'S')";
          }
          if ($expld[2] != "*") {
            $sql.=" AND GPO_CODIGO = ".$expld[2];
          }
          return $sql;
          break;
//////////////////////////////////////////////////////////////////////////////          
        case "quaisGpo":
          $sql.="SELECT GPO_CODIGO, GPO_NOME, GPO_CODUSR";
          
          if ($busca != 0) {
           $sql.=", U.UNI_CODIGO, U.UNI_APELIDO ";
          }
          
          $sql.=" FROM GRUPOOPERACIONAL INNER JOIN GRUPOOPERACIONALUNIDADE on GOU_CODGPO = GRUPOOPERACIONAL.GPO_CODIGO AND GOU_CODUNI";
          if ($busca != 0) {
            $sql.=" = ".$busca." INNER JOIN UNIDADE U ON U.UNI_CODIGO=GOU_CODUNI";
          } else {
            $sql.=" IN (SELECT UU_CODUNI FROM USUARIOUNIDADE WHERE UU_CODUSR =".$_SESSION['usr_codigo']." AND UU_ATIVO = 'S')";
          }                                               
          $sql.=" GROUP BY GPO_CODIGO, GPO_NOME, GPO_CODUSR";
          if ($busca != 0) {
            $sql.=", U.UNI_CODIGO, U.UNI_APELIDO ";
          }
          break;
        //////////////////////////////
        // Trac_BiVisaoGeral.php    //
        // Trac_BiInfracoes.php     //        
        //////////////////////////////
        case "quaisUnidade":
          $sql.="SELECT UNI_CODIGO,UNI_APELIDO";
          $sql.="  FROM UNIDADE A";
          $sql.="  LEFT OUTER JOIN USUARIOSISTEMA U ON A.UNI_CODUSR=U.US_CODIGO";
          $sql.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON A.UNI_CODIGO=UU.UU_CODUNI AND UU.UU_CODUSR=".$_SESSION['usr_codigo'];
          $sql.=" WHERE ((UNI_ATIVO='S') AND (COALESCE(UU.UU_ATIVO,'')='S'))";
          if ($busca != "*") {
            $sql.="  AND UNI_CODPOL = '".$busca."'";
          }
          $sql.=" ORDER BY UNI_APELIDO";
          break;
        //////////////////////////////
        // Trac_BiVisaoGeral.php    //
        // Trac_BiInfracoes.php     //
        // Trac_BiMotorista.php     //
        //////////////////////////////
        case "qtasUnidade":
          $execSql=false;
          $sql.="SELECT COUNT(A.UNI_CODIGO) AS QTOS";
          $sql.="  FROM UNIDADE A";
          $sql.="  LEFT OUTER JOIN USUARIOSISTEMA U ON A.UNI_CODUSR=U.US_CODIGO";
          $sql.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON A.UNI_CODIGO=UU.UU_CODUNI AND UU.UU_CODUSR=".$_SESSION['usr_codigo'];
          if( $expld[1] >0 ){
            $sql.="  WHERE ((A.UNI_ATIVO='S') AND (A.UNI_CODIGO=".$expld[1].") AND (COALESCE(UU.UU_ATIVO,'')='S'))";
          } elseif($expld[2] != "*" ){              
            $sql.="  WHERE ((A.UNI_ATIVO='S') AND (A.UNI_CODPOL='".$expld[2]."') AND (COALESCE(UU.UU_ATIVO,'')='S'))";
          } else {
            $sql.="  WHERE ((UNI_ATIVO='S') AND (COALESCE(UU.UU_ATIVO,'')='S'))";  
          };
          return $sql;
          break;
        //////////////////////////////
        // Trac_BiInfracoes.php     //
        // Trac_BiMotorista.php     //
        //////////////////////////////
        case "qualInfracaoMes":
          $execSql  = false;
          $alias    = "*";
          $table    = "*";
          $arr=[];
          switch( $expld[1] ){
            case "AB":    $alias="A.BIABM";  $table="BI_ACELERBRUSCAMES";  break;
            case "CB":    $alias="A.BICBM";  $table="BI_CONDUCAOBANGMES";  break;
            case "ERPM":  $alias="A.BIRAM";  $table="BI_RPMALTOMES";       break;            
            case "EV":    $alias="A.BIEVM";  $table="BI_EXCESSOVELOCMES";  break;
            case "EVC":   $alias="A.BIEVCM"; $table="BI_EXCESSOVELCHMES";  break;
            case "FB":    $alias="A.BIFBM";  $table="BI_FREADABRUSCAMES";  break;
          };
          array_push($arr,["alias" => $alias,"tabela" => $table]);
          return $arr;
          break;
      }; 
      if( $execSql ){
        $expld  = explode("|",$param);
        $classe   = new conectaBd();
        $classe->conecta( $expld[0] );
        $classe->msgSelect(false);
        $retCls=$classe->selectAssoc($sql);
        if( $retCls['retorno'] != "OK" ){
          $this->retorno=[ "retorno"  =>  "ERR"
                          ,"dados"    =>  ""
                          ,"erro"     =>  $retCls['erro']];
        } else { 
          $this->retorno=[ "retorno"  =>  "OK"
                          ,"dados"    =>  json_encode($retCls['dados'])
                          ,"erro"     =>  ""];
        };
        return $this->retorno;
      };
    }  
  }
?>
