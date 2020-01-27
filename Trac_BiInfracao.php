<?php
  session_start();
  if( isset($_POST["biInfracao"]) ){
    try{     
      require("classPhp/conectaSqlServer.class.php");
      require("classPhp/validaJson.class.php"); 
      require("classPhp/removeAcento.class.php"); 
      require("classPhp/selectRepetidoTrac.class.php"); 

      $vldr     = new validaJSon();          
      $retorno  = "";
      $retCls   = $vldr->validarJs($_POST["biInfracao"]);
      ///////////////////////////////////////////////////////////////////////
      // Variavel mostra que não foi feito apenas selects mas atualizou BD //
      ///////////////////////////////////////////////////////////////////////
      $atuBd    = false;
      if($retCls["retorno"] != "OK"){
        $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';
        unset($retCls,$vldr);      
      } else {
        $strExcel = "*"; 
        $arrUpdt  = []; 
        $jsonObj  = $retCls["dados"];
        $lote     = $jsonObj->lote;
        $rotina   = $lote[0]->rotina;
        $classe   = new conectaBd();
        $classe->conecta($lote[0]->login);
        ////////////////////////////////////
        // Detalhe de cada linha da grade //
        ////////////////////////////////////
        if( $rotina=="detalhe" ){
          ////////////////////////////////////////////////
          // Aqui tem individual e uma opcao todos="**" //
          ////////////////////////////////////////////////
          if( $lote[0]->infracao <> "**" ){
            switch( $lote[0]->infracao ){
              case "AB":    $alias="A.BIAB";  $table="BI_ACELERBRUSCA";  break;
              case "CB":    $alias="A.BICB";  $table="BI_CONDUCAOBANG";  break;
              case "ERPM":  $alias="A.BIRA";  $table="BI_RPMALTO";       break;            
              case "EV":    $alias="A.BIEV";  $table="BI_EXCESSOVELOC";  break;
              case "EVC":   $alias="A.BIEVC"; $table="BI_EXCESSOVELCH";  break;
              case "FB":    $alias="A.BIFB";  $table="BI_FREADABRUSCA";  break;
            };
            $sql="";
            $sql.="SELECT ".$alias."_POSICAO";
            $sql.="       ,CONVERT(VARCHAR(23),MVM.MVM_DATAGPS,127) AS MVM_DATAGPS";
            $sql.="       ,UNI.UNI_APELIDO";
            $sql.="       ,MVM.MVM_CODPOL";
            $sql.="       ,MVM.MVM_PLACA";
            $sql.="       ,COALESCE(VCL.VCL_FROTA,'*') AS VCL_FROTA";
            $sql.="       ,MVM.MVM_RFID";
            $sql.="       ,MVM.MVM_VELOCIDADE";
            $sql.="       ,MVM.MVM_RPM";
            $sql.="       ,MVM.MVM_CODEG";            
            $sql.="       ,MVM.MVM_TURNO";
            $sql.="       ,MTR.MTR_NOME";
            $sql.="       ,MVM.MVM_LOCALIZACAO";
            $sql.="  FROM ".$table." A";
            $sql.="  LEFT OUTER JOIN UNIDADE UNI ON ".$alias."_CODUNI=UNI.UNI_CODIGO";
            $sql.="  LEFT OUTER JOIN MOVIMENTO MVM ON ".$alias."_POSICAO=MVM.MVM_POSICAO";
            $sql.="  LEFT OUTER JOIN VEICULO VCL ON ".$alias."_CODVCL=VCL.VCL_CODIGO AND VCL.VCL_ATIVO='S'";
            $sql.="  LEFT OUTER JOIN MOTORISTA MTR ON ".$alias."_CODMTR=MTR.MTR_CODIGO";
            $sql.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON ".$alias."_CODUNI=UU.UU_CODUNI AND UU.UU_CODUSR=".$_SESSION['usr_codigo'];
            $sql.=" WHERE (".$alias."_ANOMES='".$lote[0]->dtini."')";
            $sql.="   AND (".$alias."_CODUNI=".$lote[0]->coduni.")";
            $sql.="   AND (".$alias."_CODMTR=".$lote[0]->codmtr.")";
            $sql.="   AND (".$alias."_CODVCL='".$lote[0]->codvcl."')"; 
            $sql.="   AND (".$alias."_ENTRABI='S')"; 
            $sql.="   AND (COALESCE(UU.UU_ATIVO,'')='S')"; 
          } else {
            $arrAll=[];
            array_push($arrAll,["SIGLA"=>"AB"   ,"ALIAS"=>"A.BIAB"   ,"TABLE"=>"BI_ACELERBRUSCA"  ,"UNION"=>"S"]);
            array_push($arrAll,["SIGLA"=>"CB"   ,"ALIAS"=>"A.BICB"   ,"TABLE"=>"BI_CONDUCAOBANG"  ,"UNION"=>"S"]);
            array_push($arrAll,["SIGLA"=>"ERPM" ,"ALIAS"=>"A.BIRA"   ,"TABLE"=>"BI_RPMALTO"       ,"UNION"=>"S"]);
            array_push($arrAll,["SIGLA"=>"EV"   ,"ALIAS"=>"A.BIEV"   ,"TABLE"=>"BI_EXCESSOVELOC"  ,"UNION"=>"S"]);
            array_push($arrAll,["SIGLA"=>"EVC"  ,"ALIAS"=>"A.BIEVC"  ,"TABLE"=>"BI_EXCESSOVELCH"  ,"UNION"=>"S"]);
            array_push($arrAll,["SIGLA"=>"FB"   ,"ALIAS"=>"A.BIFB"   ,"TABLE"=>"BI_FREADABRUSCA"  ,"UNION"=>"N"]);
            $qtos=count($arrAll);
            $sql="";            
            for( $lin=0;$lin<$qtos;$lin++ ){
              $alias=$arrAll[$lin]["ALIAS"];
              $table=$arrAll[$lin]["TABLE"];
              //
              //
              $sql.="SELECT ".$alias."_POSICAO";
              $sql.="       ,CONVERT(VARCHAR(23),MVM.MVM_DATAGPS,127) AS MVM_DATAGPS";
              $sql.="       ,UNI.UNI_APELIDO";
              $sql.="       ,MVM.MVM_CODPOL";
              $sql.="       ,MVM.MVM_PLACA";
              $sql.="       ,COALESCE(VCL.VCL_FROTA,'*') AS VCL_FROTA";
              $sql.="       ,MVM.MVM_RFID";
              $sql.="       ,MVM.MVM_VELOCIDADE";
              $sql.="       ,MVM.MVM_RPM";
              $sql.="       ,MVM.MVM_CODEG";              
              $sql.="       ,MVM.MVM_TURNO";
              $sql.="       ,MTR.MTR_NOME";
              $sql.="       ,MVM.MVM_LOCALIZACAO";
              $sql.="  FROM ".$table." A";
              $sql.="  LEFT OUTER JOIN UNIDADE UNI ON ".$alias."_CODUNI=UNI.UNI_CODIGO";
              $sql.="  LEFT OUTER JOIN MOVIMENTO MVM ON ".$alias."_POSICAO=MVM.MVM_POSICAO";
              $sql.="  LEFT OUTER JOIN VEICULO VCL ON ".$alias."_CODVCL=VCL.VCL_CODIGO AND VCL.VCL_ATIVO='S'";
              $sql.="  LEFT OUTER JOIN MOTORISTA MTR ON ".$alias."_CODMTR=MTR.MTR_CODIGO";
              $sql.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON ".$alias."_CODUNI=UU.UU_CODUNI AND UU.UU_CODUSR=".$_SESSION['usr_codigo'];
              $sql.=" WHERE (".$alias."_ANOMES='".$lote[0]->dtini."')";
              $sql.="   AND (".$alias."_CODMTR=".$lote[0]->codmtr.")";
              $sql.="   AND (".$alias."_CODVCL='".$lote[0]->codvcl."')"; 
              $sql.="   AND (".$alias."_ENTRABI='S')"; 
              $sql.="   AND (COALESCE(UU.UU_ATIVO,'')='S')";   
              if($arrAll[$lin]["UNION"]=="S" )
                $sql.=" UNION ALL ";
            };    
          };
          $classe->msgSelect(false);
          $retCls=$classe->select($sql);
          if( $retCls['retorno'] != "OK" ){
            $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';  
          } else { 
            $retorno='[{"retorno":"OK","dados":'.json_encode($retCls['dados']).',"erro":""}]'; 
          };  
        };  
        ////////////////////////////////////
        //          Select completo       //
        ////////////////////////////////////
        if( $rotina=="selectBi" ){

          $gpo="";

          if( $lote[0]->grupoOperacional != 'TODOS' ) {
            $gpo = " AND (VCL.VCL_CODGPO=".$lote[0]->grupoOperacional.")";
          }
          ////////////////////////////////////////////////
          // Aqui tem individual e uma opcao todos="**" //
          ////////////////////////////////////////////////
          if( $lote[0]->infracao <> "**" ){
            $cSql   = new SelectRepetido();
            $retSql = $cSql->qualSelect("qualInfracaoMes",$lote[0]->login."|".$lote[0]->infracao);
            $alias  = $retSql[0]["alias"];
            $table  = $retSql[0]["tabela"];
            
            $sql="";
            $sql.="SELECT ".$alias."_CODUNI AS CODUNI";          
            $sql.="       ,UNI.UNI_APELIDO AS UNIDADE";
            $sql.="       ,UNI.UNI_CODPOL AS POLO";
            $sql.="       ,".$alias."_CODVCL AS PLACA";
            $sql.="       ,CASE WHEN VCL.VCL_FROTA='L' THEN 'LEVE'";
            $sql.="             WHEN VCL.VCL_FROTA='P' THEN 'PESADA'"; 
            $sql.="        ELSE '*' END AS FROTA";
            $sql.="       ,".$alias."_TURNO AS TURNO";          
            $sql.="       ,".$alias."_CODMTR AS CODMTR";          
            $sql.="       ,COALESCE(MTR.MTR_NOME,'...') AS MOTORISTA";
            $sql.="       ,SUM(".$alias."_TOTAL) AS QTOS";
            $sql.="  FROM ".$table." A";
            $sql.="  LEFT OUTER JOIN UNIDADE UNI ON ".$alias."_CODUNI=UNI.UNI_CODIGO";
            $sql.="  LEFT OUTER JOIN VEICULO VCL ON ".$alias."_CODVCL=VCL.VCL_CODIGO";
            $sql.="  LEFT OUTER JOIN MOTORISTA MTR ON ".$alias."_CODMTR=MTR.MTR_CODIGO";
            $sql.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON ".$alias."_CODUNI=UU.UU_CODUNI AND UU.UU_CODUSR=".$lote[0]->codusu;
            $sql.=" WHERE (".$alias."_ANOMES='".$lote[0]->dtini."')";            
            if($lote[0]->frota != "*" ){
              $sql.="  AND (VCL.VCL_FROTA='".$lote[0]->frota."')";
            };
            $sql.=$gpo;
            $sql.="  AND (COALESCE(UU.UU_ATIVO,'')='S')";
            $sql.=" GROUP BY ".$alias."_CODUNI,UNI.UNI_APELIDO,UNI.UNI_CODPOL,".$alias."_CODVCL,VCL.VCL_FROTA,".$alias."_TURNO,".$alias."_CODMTR,MTR.MTR_NOME";
            $sql.="  HAVING (SUM(".$alias."_TOTAL)>".$lote[0]->qtos.")"; 
//file_put_contents("aaa.xml",$sql);																			
          } else {
            $arrAll=[];
            array_push($arrAll,["SIGLA"=>"AB"   ,"ALIAS"=>"A.BIABM"   ,"TABLE"=>"BI_ACELERBRUSCAMES"  ,"UNION"=>"S"]);
            array_push($arrAll,["SIGLA"=>"CB"   ,"ALIAS"=>"A.BICBM"   ,"TABLE"=>"BI_CONDUCAOBANGMES"  ,"UNION"=>"S"]);
            array_push($arrAll,["SIGLA"=>"ERPM" ,"ALIAS"=>"A.BIRAM"   ,"TABLE"=>"BI_RPMALTOMES"       ,"UNION"=>"S"]);
            array_push($arrAll,["SIGLA"=>"EV"   ,"ALIAS"=>"A.BIEVM"   ,"TABLE"=>"BI_EXCESSOVELOCMES"  ,"UNION"=>"S"]);
            array_push($arrAll,["SIGLA"=>"EVC"  ,"ALIAS"=>"A.BIEVCM"  ,"TABLE"=>"BI_EXCESSOVELCHMES"  ,"UNION"=>"S"]);
            array_push($arrAll,["SIGLA"=>"FB"   ,"ALIAS"=>"A.BIFBM"   ,"TABLE"=>"BI_FREADABRUSCAMES"  ,"UNION"=>"N"]);
            $qtos=count($arrAll);
            $sql="";            
            for( $lin=0;$lin<$qtos;$lin++ ){
              $alias=$arrAll[$lin]["ALIAS"];
              $table=$arrAll[$lin]["TABLE"];
              //
              //
              $sql.="SELECT ".$alias."_CODUNI AS CODUNI";          
              $sql.="       ,UNI.UNI_APELIDO AS UNIDADE";
              $sql.="       ,UNI.UNI_CODPOL AS POLO";
              $sql.="       ,".$alias."_CODVCL AS PLACA";
              $sql.="       ,CASE WHEN VCL.VCL_FROTA='L' THEN 'LEVE'";
              $sql.="             WHEN VCL.VCL_FROTA='P' THEN 'PESADA'"; 
              $sql.="        ELSE '*' END AS FROTA";
              $sql.="       ,".$alias."_TURNO AS TURNO";          
              $sql.="       ,".$alias."_CODMTR AS CODMTR";          
              $sql.="       ,COALESCE(MTR.MTR_NOME,'...') AS MOTORISTA";
              $sql.="       ,SUM(".$alias."_TOTAL) AS QTOS";
              $sql.="  FROM ".$table." A";
              $sql.="  LEFT OUTER JOIN UNIDADE UNI ON ".$alias."_CODUNI=UNI.UNI_CODIGO";
              $sql.="  LEFT OUTER JOIN VEICULO VCL ON ".$alias."_CODVCL=VCL.VCL_CODIGO";
              $sql.="  LEFT OUTER JOIN MOTORISTA MTR ON ".$alias."_CODMTR=MTR.MTR_CODIGO";
              $sql.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON ".$alias."_CODUNI=UU.UU_CODUNI AND UU.UU_CODUSR=".$lote[0]->codusu;
              $sql.=" WHERE (".$alias."_ANOMES='".$lote[0]->dtini."')";
              if($lote[0]->frota != "*" ){
                $sql.="  AND (VCL.VCL_FROTA='".$lote[0]->frota."')";
              };
              $sql.=$gpo;
              $sql.="  AND (COALESCE(UU.UU_ATIVO,'')='S')";
              $sql.=" GROUP BY ".$alias."_CODUNI,UNI.UNI_APELIDO,UNI.UNI_CODPOL,".$alias."_CODVCL,VCL.VCL_FROTA,".$alias."_TURNO,".$alias."_CODMTR,MTR.MTR_NOME";
              $sql.="  HAVING (SUM(".$alias."_TOTAL)>".$lote[0]->qtos.")"; 
              if($arrAll[$lin]["UNION"]=="S" )
                $sql.=" UNION ALL ";

            };
          };
          $classe->msgSelect(false);
          $retCls=$classe->selectAssoc($sql);
          $tbl    = $retCls["dados"];
          $arrRet = []; 
          foreach( $tbl as $linTbl ){
            ////////////////////////////////////
            // Preciso quebrar por turno aqui //
            ////////////////////////////////////
            $manha=0;
            $tarde=0;
            $noite=0;
            
            switch( $linTbl["TURNO"] ){
              case "M"  : $manha=$linTbl["QTOS"] ;break;
              case "T"  : $tarde=$linTbl["QTOS"] ;break;
              case "N"  : $noite=$linTbl["QTOS"] ;break;
            };
            //
            //
            $achei=false;
            foreach( $arrRet as &$lin ){
              if( ($lin["CODUNI"]==$linTbl["CODUNI"]) and  ($lin["POLO"]==$linTbl["POLO"]) and ($lin["PLACA"]==$linTbl["PLACA"])
              and ($lin["CODMTR"]==$linTbl["CODMTR"]) ){
                $lin["MANHA"] +=  $manha;
                $lin["TARDE"] +=  $tarde;
                $lin["NOITE"] +=  $noite;
                $lin["QTOS"]  +=  $linTbl["QTOS"];
                $achei=true;
                break;
              };                  
            };
            //  
            if( $achei==false ){
              array_push($arrRet,[
                "CODUNI"     =>  $linTbl["CODUNI"]
                ,"UNIDADE"   =>  $linTbl["UNIDADE"]
                ,"POLO"      =>  $linTbl["POLO"]
                ,"PLACA"     =>  $linTbl["PLACA"]
                ,"FROTA"     =>  $linTbl["FROTA"]
                ,"MANHA"     =>  $manha
                ,"TARDE"     =>  $tarde
                ,"NOITE"     =>  $noite
                ,"CODMTR"    =>  $linTbl["CODMTR"]
                ,"MOTORISTA" =>  $linTbl["MOTORISTA"]
                ,"QTOS"      =>  $linTbl["QTOS"]
              ]);
            };
          };  
          unset($lin,$manha,$tarde,$noite,$tbl);
          $retJs=[];
          foreach( $arrRet as $lin ){
            array_push($retJs,[
              $lin["CODUNI"]    
              ,$lin["UNIDADE"]
              ,$lin["POLO"]
              ,$lin["PLACA"]
              ,$lin["FROTA"]
              ,$lin["MANHA"]
              ,$lin["TARDE"]
              ,$lin["NOITE"]
              ,$lin["CODMTR"]
              ,$lin["MOTORISTA"]
              ,$lin["QTOS"]
            ]);
          };  
          /////////////////////////////////////////////////
          // Retornando ao javascript um array nao assoc //
          /////////////////////////////////////////////////
          if( $retCls['retorno'] != "OK" ){
            $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';  
          } else { 
            $retorno='[{"retorno":"OK","dados":'.json_encode($retJs).',"erro":""}]'; 
          };  
        };
        ///////////////////////////////////////////////////
        // Passou pela rotina de importação mas deu erro //
        ///////////////////////////////////////////////////
        if( $strExcel== "N"){
          $retorno='[{"retorno":"OK","dados":'.json_encode($data).',"erro":"ERRO(s) ENCONTRADOS"}]';   
        } else {
          ////////////////////////////////////////////////////////////////////
          // Se strExcel="S" eh pq tem rotina de importacao e nao teve erro //
          ////////////////////////////////////////////////////////////////////
          if( $strExcel== "S"){
            $atuBd=true;
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
      };
    } catch(Exception $e ){
      $retorno='[{"retorno":"ERR","dados":"","erro":"'.$e.'"}]'; 
    };    
    echo $retorno;
    exit;
  };  
?>
<!DOCTYPE html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="SKYPE_TOOLBAR" content="SKYPE_TOOLBAR_PARSER_COMPATIBLE" />
    <link rel="icon" type="image/png" href="imagens/logo_aba.png" />
    <title>BI-Infrações</title>
    <link rel="stylesheet" href="css/css2017.css">
    <link rel="stylesheet" href="css/cssTable2017.css">
    <link rel="stylesheet" href="css/Acordeon.css">    
    <script src="js/js2017.js"></script>
    <script src="js/jsTable2017.js"></script>
    <script src="js/converterData.js"></script>
    <script language="javascript" type="text/javascript"></script>
    <style>
      .comboSobreTable {
        position:relative;
        float:left;
        display:block;
        overflow-x:auto;
        /*background-color:white;*/
        padding-top:5px;
        padding-left:3px;
        width:100em;
        height:5.5em;
        border:1px solid silver;
        border-radius: 6px 6px 6px 6px;
      }
      .botaoSobreTable {
        width:6em;
        margin-left:0.2em;
        margin-top:0.1em;
        height:3.05em;
        border-radius: 4px 4px 4px 4px;
      }
    </style>  
    <script>
      "use strict";
      var clsData;
      document.addEventListener("DOMContentLoaded", function(){ 
				// comboCompetencia("YYYYMM_MMM/YY",document.getElementById("cbIni"));
        document.getElementById("cbIni").focus();
        jsBi={
          "titulo":[
             {"id":0  ,"labelCol":"OPC"     
                      ,"padrao":1}            
            ,{"id":1  ,"labelCol"       : "CODUNI"
                      ,"fieldType"      : "int"
                      ,"align"          : "center"                                      
                      ,"tamGrd"         : "0em"
                      ,"tamImp"         : "0"
                      ,"excel"          : "S"
                      ,"ordenaColuna"   : "S"
                      ,"padrao":0}
            ,{"id":2  ,"labelCol"       : "UNIDADE"
                      ,"fieldType"      : "str"
                      ,"tamGrd"         : "12em"
                      ,"tamImp"         : "25"
                      ,"excel"          : "S"
                      ,"ordenaColuna"   : "S"
                      ,"padrao":0}
            ,{"id":3  ,"labelCol"       : "POLO"
                      ,"fieldType"      : "str"
                      ,"align"          : "center"
                      ,"tamGrd"         : "8em"
                      ,"tamImp"         : "12"
                      ,"excel"          : "S"
                      ,"ordenaColuna"   : "S"
                      ,"padrao":0}
            ,{"id":4  ,"labelCol"       : "PLACA"
                      ,"fieldType"      : "str"
                      ,"tamGrd"         : "10em"
                      ,"tamImp"         : "25"
                      ,"excel"          : "S"
                      ,"ordenaColuna"   : "S"
                      ,"padrao":0}
            ,{"id":5  ,"labelCol"       : "FROTA"
                      ,"fieldType"      : "str"
                      ,"tamGrd"         : "8em"
                      ,"tamImp"         : "20"
                      ,"excel"          : "S"
                      ,"ordenaColuna"   : "S"
                      ,"padrao":0}
            ,{"id":6  ,"labelCol"       : "MAN"
                      ,"fieldType"      : "str"
                      ,"tamGrd"         : "3em"
                      ,"tamImp"         : "10"
                      ,"align"          : "center"
                      ,"excel"          : "S"
                      ,"ordenaColuna"   : "S"
                      ,"padrao":0}
            ,{"id":7  ,"labelCol"       : "TAR"
                      ,"fieldType"      : "str"
                      ,"tamGrd"         : "3em"
                      ,"tamImp"         : "10"
                      ,"align"          : "center"                      
                      ,"excel"          : "S"
                      ,"ordenaColuna"   : "S"
                      ,"padrao":0}
            ,{"id":8  ,"labelCol"       : "NOI"
                      ,"fieldType"      : "str"
                      ,"tamGrd"         : "3em"
                      ,"tamImp"         : "10"
                      ,"align"          : "center"                      
                      ,"excel"          : "S"
                      ,"ordenaColuna"   : "S"
                      ,"padrao":0}
            ,{"id":9  ,"labelCol"       : "CODMTR"
                      ,"fieldType"      : "int"
                      ,"align"          : "center"                                      
                      ,"tamGrd"         : "0em"
                      ,"tamImp"         : "0"
                      ,"excel"          : "S"
                      ,"ordenaColuna"   : "S"
                      ,"padrao":0}
            ,{"id":10 ,"labelCol"       : "MOTORISTA"
                      ,"fieldType"      : "str"
                      ,"tamGrd"         : "25em"
                      ,"tamImp"         : "60"
                      ,"excel"          : "S"
                      ,"ordenaColuna"   : "S"
                      ,"padrao":0}
            ,{"id":11 ,"labelCol"       : "QTOS"
                      ,"fieldType"      : "int"
                      ,"align"          : "center"                                      
                      ,"tamGrd"         : "5em"
                      ,"tamImp"         : "12"
                      ,"excel"          : "S"
                      ,"ordenaColuna"   : "S"
                      ,"padrao":0}
          ]
          ,
          "detalheRegistro":
          [
            { "width"           :"100%"
              ,"height"         :"300px" 
              ,"label"          :"BI - Detalhe do registro"
            }
          ]
          , 
          "botoesH":[
             {"texto":"Detalhe"       	,"name":"biDetalhe"     ,"onClick":"7"   ,"enabled":true,"imagem":"fa fa-sort-desc"  ,"ajuda":"Exportar para excel" }
            ,{"texto":"Imprimir"      	,"name":"biImprimir"    ,"onClick":"7"   ,"enabled":true,"imagem":"fa fa-print"  ,"ajuda":"Exportar para excel" }             
            ,{"texto":"Excel"         	,"name":"biExcel"       ,"onClick":"5"   ,"enabled":true,"imagem":"fa fa-file-excel-o"  ,"ajuda":"Exportar para excel" }
            ,{"texto":"Ver unidade"   	,"name":"biVerUnidade"  ,"onClick":"7"   ,"enabled":true,"imagem":"fa fa-eye-slash"  ,"ajuda":"Exportar para excel" }
            ,{"texto":"Ver polo"      	,"name":"biVerPolo"     ,"onClick":"7"   ,"enabled":true,"imagem":"fa fa-eye-slash"  ,"ajuda":"Exportar para excel" }
            ,{"texto":"Ver motorista" 	,"name":"biVerMotorista","onClick":"7"  ,"tamBotao":"15","enabled":true,"imagem":"fa fa-eye-slash"  ,"ajuda":"Exportar para excel" }
						,{"texto":"Desmarcar todos" ,"name":"biDesmarcar"   ,"onClick":"7"   ,"enabled":true,"imagem":"fa fa-check"  ,"ajuda":"Exportar para excel" }
            ,{"texto":"Fechar"        	,"name":"biFechar"      ,"onClick":"8"   ,"enabled":true ,"imagem":"fa fa-close"        ,"ajuda":"Fechar formulario" }
          ] 
          ,"registros"      : []                        // Recebe um Json vindo da classe clsBancoDados
          ,"opcRegSeek"     : true                      // Opção para numero registros/botão/procurar                     
          ,"checarTags"     : "S"                       // Somente em tempo de desenvolvimento(olha as pricipais tags)                  
          ,"div"            : "frmBi"                   // Onde vai ser gerado a table
          ,"divFieldSet"    : "tabelaBi"                // Para fechar a div onde estão os fieldset ao cadastrar
          ,"form"           : "frmBi"                   // Onde vai ser gerado o fieldSet       
          ,"divModal"       : "divTopoInicio"           // Onde vai se appendado abaixo deste a table 
          ,"tbl"            : "tblBi"                   // Nome da table
          ,"prefixo"        : "bi"                      // Prefixo para elementos do HTML em jsTable2017.js
          ,"tabelaBD"       : "*"                       // Nome da tabela no banco de dados  
          ,"tabelaBKP"      : "*"                       // Nome da tabela no banco de dados  
          ,"fieldAtivo"     : "*"                       // SE EXISITIR - Nome do campo ATIVO(S/N) na tabela BD
          ,"fieldReg"       : "*"                       // SE EXISITIR - Nome do campo SYS(P/A) na tabela BD            
          ,"fieldCodUsu"    : "*"                       // SE EXISITIR - Nome do campo CODIGO USUARIO na tabela BD                        
          ,"iFrame"         : "iframeCorpo"             // Se a table vai ficar dentro de uma tag iFrame
          ,"width"          : "100em"                   // Tamanho da table
          ,"height"         : "54em"                    // Altura da table
          ,"tableLeft"      : "sim"                     // Se tiver menu esquerdo
          ,"relTitulo"      : "BI"                      // Titulo do relatório
          ,"relOrientacao"  : "R"                       // Paisagem ou retrato
          ,"relFonte"       : "8"                       // Fonte do relatório
          ,"indiceTable"    : "QTOS"                    // Indice inicial da table
          ,"tamBotao"       : "12"                      // Tamanho botoes defalt 12 [12/25/50/75/100]
          ,"codTblUsu"      : "MOVIMENTORESUMO[00]"                          
          ,"codDir"         : intCodDir
        }; 
        if( objBi === undefined ){  
          objBi=new clsTable2017("objBi");
        };  
        objBi.montarHtmlCE2017(jsBi); 
        //////////////////////////////////////////////////
        //  Fim objeto clsTable2017 MOVIMENTORESUMO      //
        ////////////////////////////////////////////////// 
      });
      var objBi;                      // Obrigatório para instanciar o JS TFormaCob
      var jsBi;                       // Obj principal da classe clsTable2017      
      var objDet;                     //
      var jsDet;                      // Obj da composição do evento
      var objUp;                      //
      var jsUp;                       // Obj da composição do evento
      var clsJs;                      // Classe responsavel por montar um Json e eviar PHP
      var clsErro;                    // Classe para erros            
      var fd;                         // Formulario para envio de dados para o PHP
      var msg;                        // Variavel para guardadar mensagens de retorno/erro 
      var retPhp                      // Retorno do Php para a rotina chamadora
      var clsChecados;                // Classe para montar Json
      var chkds;                      // Guarda todos registros checados na table 
      var tamC;                       // Guarda a quantidade de registros dentro do vetor chkds
      var contMsg   = 0;              // contador para mensagens
      var cmp       = new clsCampo(); // Abrindo a classe campos
      var jsPub     = JSON.parse(localStorage.getItem("lsPublico"));
      var intCodDir = parseInt(jsPub[0].usr_d04);
      /////////////////////////////////
      // Desmarcando todos registros //
      /////////////////////////////////
			function biDesmarcarClick(){
				tblBi.retiraChecked();
			};
      ////////////////////////////
      // Filtrando os registros //
      ////////////////////////////
      function btnFiltrarClick() { 
        clsJs   = jsString("lote");  
        clsJs.add("rotina"  , "selectBi"                                  );
        clsJs.add("login"   , jsPub[0].usr_login                          );
        clsJs.add("codusu"   , jsPub[0].usr_codigo                        );
        clsJs.add("dtini"   , document.getElementById("cbIni").value   );
        clsJs.add("infracao", document.getElementById("cbInfracao").value );
        clsJs.add("frota"   , document.getElementById("cbFrota").value    );
        clsJs.add("qtos"    , document.getElementById("cbqtos").value     );
        clsJs.add("grupoOperacional"   	, document.getElementById("cbGpo").value  	);
// debugger;				
        
        fd = new FormData();
        fd.append("biInfracao" , clsJs.fim());
        msg     = requestPedido("Trac_BiInfracao.php",fd); 
        retPhp  = JSON.parse(msg);
        if( retPhp[0].retorno == "OK" ){
          //////////////////////////////////////////////////////////////////////////////////
          // O novo array não tem o campo idUnico mas a montarHtmlCE2017 ja foi executada //
          // Campo obrigatório se existir rotina de manutenção na table devido Json       //
          // Esta rotina não tem manutenção via classe clsTable2017                       //
          // jsCrv.registros=objCrv.addIdUnico(retPhp[0]["dados"]);                       //
          //////////////////////////////////////////////////////////////////////////////////
          jsBi.registros=objBi.addIdUnico(retPhp[0]["dados"]);
          jsBi.relTitulo="BI "+document.getElementById("cbInfracao").options[document.getElementById("cbInfracao").selectedIndex].text
                         +" NO PERIODO DE "+document.getElementById("cbIni").value;//+" A "+document.getElementById("edtFim").value;
          objBi.ordenaJSon(jsBi.indiceTable,false);  
          objBi.montarBody2017();
        };  
      }; 
      ///////////////////////////
      // Fechando o formulario //
      ///////////////////////////
      function biFecharClick(){
        window.close();
      };
      ////////////////
      // Imprimindo //
      ////////////////
      function biImprimirClick(){
        //////////////////////////////
        // CRIANDO UM JSON DA TABLE //
        //////////////////////////////
        clsChecados = objBi.gerarJson();
        clsChecados.retornarQtos("n");        
        clsChecados.temColChk(false);
        var json  = clsChecados.gerar();
        var tam   = json.length;
        
        var imprimir = '{'
          + '"orientacao":"R"'
          + ',"imprimir":'
          + '[{"SetFont":["Arial","B",8]}'          
          + ',{"SetFillColor":["cinzaclaro","8","175"]}'
          + ',{"Cell":[175,8,"RELATORIO:'+jsBi.relTitulo+'",0,0,"C"]}'
          + ',{"Ln":[8]}'
          + ',{"SetFillColor":["branco","5","200"]}'                    
          + ',{"Cell":[30,6,"UNIDADE"    ,1,0,"L"]}'
          + ',{"Cell":[15,6,"POLO"       ,1,0,"L"]}'
          + ',{"Cell":[20,6,"PLACA"      ,1,0,"L"]}'
          + ',{"Cell":[20,6,"FROTA"      ,1,0,"L"]}'            
          + ',{"Cell":[80,6,"MOTORISTA"  ,1,0,"L"]}'            
          + ',{"Cell":[10,6,"QTOS"       ,1,0,"L"]}'          
          + ',{"Ln":[6]}'
          + ',{"SetFont":["Arial","",8]}';
        var totInfracao=0;
        var frotaLev  = 0;
        var frotaPes  = 0;
        var addUni    = new Array();  //Array somente para buscar unidades
        var tblUni    = new Array();
        var addPol    = new Array();  //Array somente para buscar polos
        var tblPol    = new Array();
        var addMot    = new Array();  //Array somente para buscar motoristas
        var tblMot    = new Array();
        
        var intSeek   = 0;        
        for(var fc=0; fc<tam;fc++){
          imprimir += 
           ',{"Cell":[30,6,"'+json[fc].UNIDADE    +'",0,0,"L"]}'
          +',{"Cell":[15,6,"'+json[fc].POLO       +'",0,0,"L"]}'
          +',{"Cell":[20,6,"'+json[fc].PLACA      +'",0,0,"L"]}'
          +',{"Cell":[20,6,"'+json[fc].FROTA      +'",0,0,"L"]}'
          +',{"Cell":[80,6,"'+json[fc].MOTORISTA  +'",0,0,"L"]}'
          +',{"Cell":[10,6,"'+json[fc].QTOS       +'",0,0,"C"]}'          
          +',{"Ln":[4]}';
          ////////////////////////
          // Total de infrações //
          ////////////////////////
          totInfracao+=parseInt(json[fc].QTOS);
          /////////////////////////////////
          // Total por frota leve/pesada //
          /////////////////////////////////
          if(json[fc].FROTA=="LEVE"){
            frotaLev+=parseInt(json[fc].QTOS);  
          }
          if(json[fc].FROTA=="PESADA"){
            frotaPes+=parseInt(json[fc].QTOS);  
          };
          /////////////////////////////////
          // Acumulando por unidade      //
          /////////////////////////////////
          intSeek=addUni.indexOf(json[fc].UNIDADE);
          if( intSeek==-1 ){
            addUni.push(json[fc].UNIDADE);
            tblUni.push({"UNIDADE":json[fc].UNIDADE,"QTOS":parseInt(json[fc].QTOS)});
          } else {
            tblUni[intSeek].QTOS +=parseInt(json[fc].QTOS); 
          };
          /////////////////////////////////
          // Acumulando por polo      //
          /////////////////////////////////
          intSeek=addPol.indexOf(json[fc].POLO);
          if( intSeek==-1 ){
            addPol.push(json[fc].POLO);
            tblPol.push({"POLO":json[fc].POLO,"QTOS":parseInt(json[fc].QTOS)});
          } else {
            tblPol[intSeek].QTOS +=parseInt(json[fc].QTOS); 
          };
          /////////////////////////////////
          // Acumulando por motorista    //
          /////////////////////////////////
          intSeek=addMot.indexOf(json[fc].MOTORISTA);
          if( intSeek==-1 ){
            addMot.push(json[fc].MOTORISTA);
            tblMot.push({"MOTORISTA":json[fc].MOTORISTA,"QTOS":parseInt(json[fc].QTOS)});
          } else {
            tblMot[intSeek].QTOS +=parseInt(json[fc].QTOS); 
          };
        };
        imprimir +=
         ',{"Ln":[2]}'
        +',{"SetFont":["Arial","B",8]}'
        +',{"SetFillColor":["cinza","5","175"]}'
        +',{"Cell":[85,6,"",0,0,"L"]}'
        +',{"Cell":[80,6,"TOTAL",0,0,"L"]}' 
        +',{"Cell":[10,6,"'+totInfracao+'",0,0,"C"]}'
        +',{"SetFillColor":["branco","5","260"]}'            
        + ',{"Ln":[6]}' 
        +',{"Ln":[2]}'
        +',{"SetFont":["Arial","B",8]}' 
        + ',{"SetFillColor":["cinza","6","15"]}'        
        +',{"Cell":[15,6,"FROTA",1,0,"L"]}'        
        + ',{"SetFont":["Arial","",8]}'
        +',{"Cell":[25,6,"LEVE: '  +frotaLev+'",1,0,"C"]}'
        +',{"Cell":[25,6,"PESADA: '+frotaPes+'",1,0,"C"]}'
        +',{"Ln":[8]}';
        ////////////////////////
        // Ordenando o array  //
        ////////////////////////
        tblUni.sort(function (obj1, obj2) {
          return (obj1.QTOS > obj2.QTOS ? -1 : obj1.QTOS < obj2.QTOS ? 1 : 0);
        });
        ///////////////////////////
        // Mostrando as unidades //
        ///////////////////////////
        imprimir +=        
          ',{"SetFont":["Arial","B",8]}' 
        + ',{"SetFillColor":["cinza","6","15"]}'        
        +',{"Cell":[15,6,"UNIDADE",1,0,"L"]}'
        +',{"SetFont":["Arial","",8]}'         
        tam = tblUni.length;
        var coluna = -1;  //para nova linha
        for(var fc=0; fc<tam;fc++){
          coluna++;
          msg=tblUni[fc].UNIDADE+": "+tblUni[fc].QTOS;
          if( coluna != 3 ){
            imprimir += ',{"Cell":[40,6,"'+msg+'",1,0,"C"]}';
          } else {  
            imprimir += ',{"Cell":[40,6,"'+msg+'",1,0,"C"]}'
            +',{"Ln":[6]}'
            +',{"Cell":[15,6,"",0,0,"L"]}';
            coluna=-1;
          };
        };      
        ////////////////////////
        // Ordenando o array  //
        ////////////////////////
        tblPol.sort(function (obj1, obj2) {
          return (obj1.QTOS > obj2.QTOS ? -1 : obj1.QTOS < obj2.QTOS ? 1 : 0);
        });
        ///////////////////////////
        // Mostrando os polos    //
        ///////////////////////////
        imprimir +=
         ',{"Ln":[8]}'        
        +',{"SetFont":["Arial","B",8]}' 
        + ',{"SetFillColor":["cinza","6","15"]}'        
        +',{"Cell":[15,6,"POLO",1,0,"L"]}'
        +',{"SetFont":["Arial","",8]}'         
        tam = tblPol.length;
        var coluna = -1;  //para nova linha
        for(var fc=0; fc<tam;fc++){
          coluna++;
          msg=tblPol[fc].POLO+": "+tblPol[fc].QTOS;
          if( coluna != 5 ){
            imprimir += ',{"Cell":[25,6,"'+msg+'",1,0,"C"]}';  
          } else {
            imprimir += ',{"Cell":[25,6,"'+msg+'",1,0,"C"]}'
            +',{"Ln":[6]}'
            +',{"Cell":[15,6,"",0,0,"L"]}';
            coluna=-1;
          };
        };      
        ////////////////////////
        // Ordenando o array  //
        ////////////////////////
        tblMot.sort(function (obj1, obj2) {
          return (obj1.QTOS > obj2.QTOS ? -1 : obj1.QTOS < obj2.QTOS ? 1 : 0);
        });
        /////////////////////////////
        // Mostrando os motoristas //
        /////////////////////////////
        imprimir +=        
         ',{"Ln":[8]}'        
        +',{"SetFont":["Arial","B",8]}' 
        + ',{"SetFillColor":["cinza","5","20"]}'        
        +',{"Cell":[20,5,"MOTORISTA",1,0,"L"]}'
        +',{"SetFont":["Arial","",8]}'         
        tam = tblMot.length;
        var coluna = -1;  //para nova linha
        for(var fc=0; fc<tam;fc++){
          coluna++;
          msg=tblMot[fc].MOTORISTA.substring(0,20)+": "+tblMot[fc].QTOS;
          if( coluna != 2 ){
            imprimir += ',{"Cell":[52,5,"'+msg+'",1,0,"C"]}';
          } else {  
            imprimir += ',{"Cell":[52,5,"'+msg+'",1,0,"C"]}'
            +',{"Ln":[5]}'
            +',{"Cell":[20,5,"",0,0,"L"]}';
            coluna=-1;
          };
        };      
        imprimir += ']}';
        mostrarImpressao(imprimir);
      };
      
      
      function biVerUnidadeClick(){
        filtrarEvento("unidade");    
      };
      function biVerPoloClick(){
        filtrarEvento("polo");    
      };
      function biVerMotoristaClick(){
        filtrarEvento("motorista");    
      };
      //
      //
      function filtrarEvento(qual){
        try{
          //////////////////////////////
          // CRIANDO UM JSON DA TABLE //
          //////////////////////////////
          clsChecados = objBi.gerarJson();
          clsChecados.retornarQtos("n");        
          clsChecados.temColChk(false);
          var json    = clsChecados.gerar();
          var tam     = json.length;
          var addTbl  = new Array();  //Array somente para buscar unidades
          var tbl     = new Array();
          var intSeek = 0;        
          var strSeek = "";  
          for(var fc=0; fc<tam;fc++){
            switch (qual) {
              case "unidade"  : 
                strSeek=json[fc].UNIDADE;
                intSeek=addTbl.indexOf(strSeek);
                break;
              case "polo"  : 
                strSeek=json[fc].POLO;
                intSeek=addTbl.indexOf(strSeek);
                break;
              case "motorista"  : 
                strSeek=json[fc].MOTORISTA;
                intSeek=addTbl.indexOf(strSeek);
                break;
            };            
            if( intSeek==-1 ){
              addTbl.push(strSeek);
              tbl.push([
                strSeek
                ,parseInt(json[fc].QTOS)
              ]);
            } else {
              tbl[intSeek][1]+=parseInt(json[fc].QTOS); 
            };
          };  
          //
          //
          if( tbl.length==0 ){
            gerarMensagemErro("ALV","NENHUM REGISTRO LOCALIZADO","AVISO");  
          } else {
            //
            jsUp={
              "titulo":[
                {"id":0   ,"labelCol"       : qual.toUpperCase()
                          ,"fieldType"      : "str"
                          ,"tamGrd"         : "34em"
                          ,"tamImp"         : "15"
                          ,"excel"          : "S"
                          ,"ordenaColuna"   : "S"
                          ,"padrao":0}
                ,{"id":1  ,"labelCol"       : "QTOS"
                          ,"fieldType"      : "int"
                          ,"align"          : "center"
                          ,"tamGrd"         : "10em"
                          ,"tamImp"         : "10"
                          ,"excel"          : "S"
                          ,"ordenaColuna"   : "S"
                          ,"padrao":0}
              ]  
              , 
              "botoesH":[
                 {"texto":"Excel"     ,"name":"puExcel"  ,"onClick":"5"  ,"enabled":true,"imagem":"fa fa-file-excel-o"     ,"ajuda":"Exportar para excel" }
                ,{"texto":"Retornar"  ,"name":"puVoltar" ,"onClick":"7"  ,"enabled":true ,"imagem":"fa fa-plus"            ,"ajuda":"Retorna a tela anterior" }
              ] 
              ,"registros"      : tbl                       // Recebe um Json vindo da classe clsBancoDados
              ,"refazClasse"    : "S"
              ,"opcRegSeek"     : true                      // Opção para numero registros/botão/procurar                     
              ,"checarTags"     : "S"                       // Somente em tempo de desenvolvimento(olha as pricipais tags)                  
              ,"div"            : "frmUp"                   // Onde vai ser gerado a table
              ,"divFieldSet"    : "tabela"                  // Para fechar a div onde estão os fieldset ao cadastrar
              ,"form"           : "frm"                     // Onde vai ser gerado o fieldSet       
              ,"divModal"       : "divUniPol"               // Onde vai se appendado abaixo deste a table 
              ,"tbl"            : "tblUp"                   // Nome da table
              ,"prefixo"        : "es"                      // Prefixo para elementos do HTML em jsTable2017.js
              ,"tabelaBD"       : "BI_EXCESSOVELOC"         // Nome da tabela no banco de dados  
              ,"tableLeft"      : "sim"                     // Se tiver menu esquerdo              
              ,"iFrame"         : "iframeCorpo"             // Se a table vai ficar dentro de uma tag iFrame
              ,"width"          : "57em"                    // Tamanho da table
              ,"height"         : "40em"                    // Altura da table
              ,"relTitulo"      : "DETALHE EVENTO"          // Titulo do relatório
              ,"relOrientacao"  : "P"                       // Paisagem ou retrato
              ,"relFonte"       : "8"                       // Fonte do relatório
              ,"indiceTable"    : "QTOS"                    // Indice inicial da table
              ,"tamBotao"       : "30"                      // Tamanho botoes defalt 12 [12/25/50/75/100]
              ,"tamMenuTable"   : ["10em","20em"]                                
              ,"codTblUsu"      : "USUARIO[01]"                          
              ,"codDir"         : intCodDir
            }; 
            if( objUp === undefined ){  
              objUp=new clsTable2017("objUp");
            };
            objUp.montarHtmlCE2017(jsUp); 
            var el = document.getElementsByClassName("acordeon");
            for( var lin=0;lin<el.length;lin++ ){
              if( (el[lin].id=="btnUniPol") && (el[lin].className != "acordeon acrdnAtivo") ){
                document.getElementById("btnUniPol").click();  
                window.location.href="#ancoraUniPol";
              };  
            }; 
          };
        }catch( e ){
          gerarMensagemErro("Composição do evento",e,"Erro");
        };
      };
      ////////////////////////////////
      //          DETALHE           //
      ////////////////////////////////
      function biDetalheClick(){
        try{        
          clsChecados = objBi.gerarJson("1");
          chkds       = clsChecados.gerar();
          clsJs       = jsString("lote");  
          clsJs.add("rotina"  , "detalhe"                                   );
          clsJs.add("login"   , jsPub[0].usr_login                          );
          clsJs.add("codusu"  , jsPub[0].usr_codigo                         );
          clsJs.add("coduni"  , chkds[0].CODUNI                             );
          clsJs.add("codmtr"  , chkds[0].CODMTR                             );
          clsJs.add("codvcl"  , chkds[0].PLACA                              );
          clsJs.add("dtini"   , document.getElementById("cbIni").value   );
          clsJs.add("infracao", document.getElementById("cbInfracao").value );
          clsJs.add("frota"   , document.getElementById("cbFrota").value    );
          fd          = new FormData();
          fd.append("biInfracao" , clsJs.fim());          
          var req = requestPedido("Trac_BiInfracao.php",fd);
          var ret = JSON.parse(req);
          if( ret[0].dados.length==0 ){
            gerarMensagemErro("ALV","NENHUM REGISTRO LOCALIZADO","AVISO");  
          } else {
            if( ret[0].retorno == "OK" ){
              ret[0].dados.forEach(arr => {
              const dataHora = converterData(arr[1]);
              arr[1] = dataHora.dataConvertida;
              arr.splice(2, 0, dataHora.horaConvertida);
            });
              jsDet={
                "titulo":[
                  {"id":0   ,"labelCol"       : "OPC"     
                            ,"padrao"         : 1}            
                  ,{"id":1  ,"field"          : "BIEV_POSICAO" 
                            ,"labelCol"       : "ID"
                            ,"fieldType"      : "str"
                            ,"obj"            : "edtPosicao"
                            ,"tamGrd"         : "10em"
                            ,"tamImp"         : "15"
                            ,"pk"             : "S"
                            ,"ajudaCampo"     : ["Id sistemsat."]
                            ,"padrao":0}
                  ,{"id":2  ,"field"          : "BIEV_DATAGPS"   
                            ,"fieldType"      : "str"
                            ,"labelCol"       : "DATA"
                            ,"obj"            : "edtDataGps"
                            ,"tamGrd"         : "7em"
                            ,"tamImp"         : "30"
                            ,"ajudaCampo"     : ["Data."]
                            ,"padrao":0}
                  ,{"id":3  ,"field"          : "BIEV_DATAGPS"   
                            ,"fieldType"      : "str"
                            ,"labelCol"       : "HORA"
                            ,"obj"            : "edtHoraGps"
                            ,"tamGrd"         : "7em"
                            ,"tamImp"         : "30"
                            ,"ajudaCampo"     : ["Hora."]
                            ,"padrao":0}
                  ,{"id":4  ,"field"          : "UNI_APELIDO"   
                            ,"fieldType"      : "str"
                            ,"labelCol"       : "UNIDADE"
                            ,"obj"            : "edtUnidade"
                            ,"tamGrd"         : "10em"
                            ,"tamImp"         : "20"
                            ,"ajudaCampo"     : ["Unidde"]
                            ,"padrao":0}
                  ,{"id":5  ,"field"          : "MVM_CODPOL"   
                            ,"fieldType"      : "str"
                            ,"labelCol"       : "POLO"
                            ,"obj"            : "edtPolo"
                            ,"tamGrd"         : "3em"
                            ,"tamImp"         : "10"
                            ,"ajudaCampo"     : ["Polo"]
                            ,"padrao":0}
                  ,{"id":6  ,"field"          : "MVM_PLACA"   
                            ,"fieldType"      : "str"
                            ,"labelCol"       : "PLACA"
                            ,"obj"            : "edtPlaca"
                            ,"tamGrd"         : "6em"
                            ,"tamImp"         : "15"
                            ,"ajudaCampo"     : ["Placa do veiculo"]
                            ,"padrao":0}
                  ,{"id":7  ,"field"          : "VCL_FROTA"   
                            ,"fieldType"      : "str"
                            ,"labelCol"       : "PL"
                            ,"obj"            : "edtFrota"
                            ,"tamGrd"         : "2em"
                            ,"tamImp"         : "5"
                            ,"ajudaCampo"     : ["Veiculo pesado/leve"]
                            ,"padrao":0}
                  ,{"id":8  ,"field"          : "MVM_RFID"   
                            ,"fieldType"      : "str"
                            ,"labelCol"       : "RFID"
                            ,"obj"            : "edtRfid"
                            ,"tamGrd"         : "7em"
                            ,"tamImp"         : "15"
                            ,"ajudaCampo"     : ["RFID do motorista"]
                            ,"padrao":0}
                  ,{"id":9  ,"field"          : "MVM_VELOCIDADE"   
                            ,"fieldType"      : "int"
                            ,"align"          : "center"
                            ,"labelCol"       : "VEL"
                            ,"obj"            : "edtVelocidade"
                            ,"tamGrd"         : "4em"
                            ,"tamImp"         : "10"
                            ,"ajudaCampo"     : ["Velocidade"]
                            ,"padrao":0}
                  ,{"id":10  ,"field"          : "MVM_RPM"   
                            ,"fieldType"      : "int"
                            ,"align"          : "center"
                            ,"labelCol"       : "RPM"
                            ,"obj"            : "edtRpm"
                            ,"tamGrd"         : "4em"
                            ,"tamImp"         : "10"
                            ,"ajudaCampo"     : ["Rpm"]
                            ,"padrao":0}
                  ,{"id":11 ,"field"          : "MTR_CODEG"   
                            ,"fieldType"      : "str"
                            ,"labelCol"       : "INFRACAO"
                            ,"obj"            : "edtInfracao"
                            ,"tamGrd"         : "6em"
                            ,"tamImp"         : "15"
                            ,"ajudaCampo"     : ["Tipo infracao"]
                            ,"padrao":0}
                  ,{"id":12 ,"field"          : "MVM_TURNO"   
                            ,"labelCol"       : "T"
                            ,"obj"            : "edtTurno"
                            ,"tamGrd"         : "1em"
                            ,"tamImp"         : "5"
                            ,"ajudaCampo"     : ["Turno"]
                            ,"padrao":0}
                  ,{"id":13 ,"field"          : "MTR_NOME"   
                            ,"fieldType"      : "str"
                            ,"labelCol"       : "MOTORISTA"
                            ,"obj"            : "edtMotorista"
                            ,"tamGrd"         : "25em"
                            ,"tamImp"         : "80"
                            ,"ajudaCampo"     : ["Motorista"]
                            ,"padrao":0}
                  ,{"id":14 ,"field"          : "BIEV_LOCALIZACAO"   
                            ,"fieldType"      : "str"
                            ,"labelCol"       : "LOCALIZACAO"
                            ,"obj"            : "edtLocalizacao"
                            ,"tamGrd"         : "30em"
                            ,"tamImp"         : "80"
                            ,"ajudaCampo"     : ["Localizacao"]
                            ,"padrao":0}
                ]  
                , 
                "botoesH":[
                   {"texto":"Excel"     ,"name":"detExcel"  ,"onClick":"5"  ,"enabled":true,"imagem":"fa fa-file-excel-o"     ,"ajuda":"Exportar para excel" }
                  ,{"texto":"Retornar"  ,"name":"detVoltar" ,"onClick":"7"  ,"enabled":true ,"imagem":"fa fa-plus","ajuda":"Retorna a tela anterior" }
                ] 
                ,"registros"      : ret[0].dados              // Recebe um Json vindo da classe clsBancoDados
								,"corLinha"       : "if(ceTr.cells[10].innerHTML =='"+chkds[0].TURNO+"') {ceTr.style.color='blue';}"
                ,"refazClasse"    : "S"
                ,"opcRegSeek"     : true                      // Opção para numero registros/botão/procurar                     
                ,"checarTags"     : "S"                       // Somente em tempo de desenvolvimento(olha as pricipais tags)                  
                ,"div"            : "frmDet"                  // Onde vai ser gerado a table
                ,"divFieldSet"    : "tabela"                  // Para fechar a div onde estão os fieldset ao cadastrar
                ,"form"           : "frm"                     // Onde vai ser gerado o fieldSet       
                ,"divModal"       : "divDetalheReg"           // Onde vai se appendado abaixo deste a table 
                ,"tbl"            : "tblDet"                  // Nome da table
                ,"prefixo"        : "es"                      // Prefixo para elementos do HTML em jsTable2017.js
                ,"width"          : "110em"                   // Tamanho da table
                ,"height"         : "40em"                    // Altura da table
                ,"iFrame"         : "iframeCorpo"             // Se a table vai ficar dentro de uma tag iFrame
                ,"tableLeft"      : "sim"                     // Se tiver menu esquerdo
                ,"relTitulo"      : "DETALHE REGISTRO"        // Titulo do relatório
                ,"relOrientacao"  : "P"                       // Paisagem ou retrato
                ,"relFonte"       : "8"                       // Fonte do relatório
                ,"indiceTable"    : "ID"                      // Indice inicial da table
                ,"tamBotao"       : "12"                      // Tamanho botoes defalt 12 [12/25/50/75/100]
                ,"tamMenuTable"   : ["10em","20em"]                                
                ,"codTblUsu"      : "USUARIO[01]"                          
                ,"codDir"         : intCodDir
              }; 
              if( objDet === undefined ){  
                objDet=new clsTable2017("objDet");
              };
              objDet.montarHtmlCE2017(jsDet);
              var el = document.getElementsByClassName("acordeon");
              for( var lin=0;lin<el.length;lin++ ){
                if( (el[lin].id=="btnDetalhe") && (el[lin].className != "acordeon acrdnAtivo") ){
                  document.getElementById("btnDetalhe").click();  
                  window.location.href="#ancoraDetalhe";
                };  
              }; 
            } else {
              throw ret[0].erro;
            }
          };
        }catch(e){
          gerarMensagemErro("catch",e.message,"Erro");
        };
      };
      
      
      function puVoltarClick(){
        document.getElementById("btnUniPol").click();
        window.location.href="#ancoraCabec";
      }
      function detVoltarClick(){
        document.getElementById("btnDetalhe").click();
        window.location.href="#ancoraCabec";
      }
    </script>
  </head>
  <body>
    <div id="divCabec" class="comboSobreTable" style="margin-top:5px;float:left;">
      <a name="ancoraCabec"></a> 
      <!-- <div class="campotexto campo15">
        <select class="campo_input_combo" id="cbIni">
					
          <option value="201805">MAI/18</option>
          <option value="201806">JUN/18</option>
          <option value="201807">JUL/18</option>
          <option value="201808">AGO/18</option>
          <option value="201809">SET/18</option>
          <option value="201810">OUT/18</option>
          <option value="201811">NOV/18</option>
          <option value="201812">DEZ/18</option>
					
        </select>
        <label class="campo_label campo_required" for="cbIni">INFORME</label>
      </div> -->

      <?php include 'classPhp/comum/selectMes.class.php';?>
      
      <div class="campotexto campo25">
        <select class="campo_input_combo" id="cbInfracao" class="selectBis">
          <option value="AB">ACELERACAO BRUSCA</option>
          <option value="CB">CONDUCAO BANGUELA</option>
          <option value="ERPM">EXCESSO RPM</option>
          <option value="EV" selected="selected">EXCESSO VELOCIDADE</option>
          <option value="EVC">EXCESSO VELOCIDADE CHUVA</option>
          <option value="FB">FREADA BRUSCA</option>
          <!--<option value="VN">VELOCIDADE NORMALIZADA</option>-->
          <option value="**">TODOS</option>
        </select>
        <label class="campo_label campo_required" for="cbInfracao">Infração</label>
      </div>
      <div class="campotexto campo15">
        <select class="campo_input_combo" id="cbFrota" class="selectBis">
          <option value="*">TODOS</option>
          <option value="L">LEVE</option>
          <option value="P">PESADA</option>
        </select>
        <label class="campo_label campo_required" for="cbFrota">Frota</label>
      </div>

      <div class="campotexto campo10">
        <select class="campo_input_combo" id="cbqtos" class="selectBis">
          <option value="0">maior 0</option>
          <option value="1">maior 1</option>
          <option value="2">maior 2</option>
          <option value="5">maior 5</option>
        </select>
        <label class="campo_label campo_required" for="cbFrota">Qtos1</label>
      </div>

      <?php include 'classPhp/comum/selectGrupoOperacional.class.php';?>

      <div class="campo10" style="float:left;">            
        <input id="btnFilttrar" onClick="btnFiltrarClick();" type="button" value="Filtrar" class="botaoSobreTable"/>
      </div>
    </div>
  
    <div class="divTelaCheia" style="float:left;">
      <div id="divContabil" class="conteudo" style="display:block;overflow-x:auto;position:relative;float:left;width:100em;height:55em;">
        <div id="divTopoInicio">
        </div>
      </div>

      <a name="ancoraDetalhe">
      <button id="btnDetalhe"
              class="acordeon"
              style="width:25%;margin-left:0.1em;">Detalhe</button>
      <div class="acrdnDiv" style="width:90%;margin-left:0.1em;height:42em;">
        <div id="divDetalhe" class="conteudo" style="position:relative;float:left;height:41em;width:150em;">
          <div id="divDetalheReg">
          </div>
        </div>
      </div>
      
      <a name="ancoraUniPol">
      <button id="btnUniPol"
              class="acordeon"
              style="width:25%;margin-left:0.1em;">Unidade/Polo</button>
      <div class="acrdnDiv" style="width:78%;margin-left:0.1em;height:42em;">
        <div id="divUp" class="conteudo" style="position:relative;float:left;height:41em;width:78%;">
          <div id="divUniPol">
          </div>
        </div>
      </div>
    </div>    
    
    <script>
      var acc = document.getElementsByClassName("acordeon");
      var i;

      for (i = 0; i < acc.length; i++) {
        acc[i].addEventListener("click", function() {
          this.classList.toggle("acrdnAtivo");
          var panel = this.nextElementSibling;
          if (panel.style.maxHeight){
            panel.style.maxHeight = null;
          } else {
            panel.style.maxHeight = panel.scrollHeight + "px";
          } 
        });
      }
    </script>
  </body>
</html>