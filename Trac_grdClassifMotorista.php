<?php
  session_start();
  if( isset($_POST["grdClassifMotorista"]) ){
    try{     
      require("classPhp/conectaSqlServer.class.php");
      require("classPhp/validaJson.class.php"); 
      require("classPhp/removeAcento.class.php"); 
      
      function diferenca($parI,$parF){
        $dtI      = new DateTime($parI); 
        $dtF      = new DateTime($parF);
        $dteDiff  = $dtI->diff($dtF); 
        return $dteDiff->format("%H:%I:%S"); 
      };  

      $vldr     = new validaJSon();          
      $retorno  = "";
      $retCls   = $vldr->validarJs($_POST["grdClassifMotorista"]);
      ///////////////////////////////////////////////////////////////////////
      // Variavel mostra que não foi feito apenas selects mas atualizou BD //
      ///////////////////////////////////////////////////////////////////////
      $atuBd    = false;
      if($retCls["retorno"] != "OK"){
        $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';
        unset($retCls,$vldr);      
      } else {
        $arrRet  = []; 
        $jsonObj  = $retCls["dados"];
        $lote     = $jsonObj->lote;
        $rotina   = $lote[0]->rotina;
        $classe   = new conectaBd();
        $classe->conecta($lote[0]->login);
        if( $rotina=="select" ){
          switch( $lote[0]->frota ){
            case "LP" : $frota=" AND (VCL.VCL_FROTA IN('L','P'))";
                        $frotaCor="LP";
                        break;
            case "L"  : $frota=" AND (VCL.VCL_FROTA='L')";
                        $frotaCor="L";
                        break;
            case "P"  : $frota=" AND (VCL.VCL_FROTA='P')";
                        $frotaCor="P";
                        break;
          };
          
          $sqlEv="";
          $sqlEv.="SELECT A.BIEV_POSICAO AS POSICAO";
          $sqlEv.="       ,A.BIEV_CODUNI AS CODUNI";
          $sqlEv.="       ,A.BIEV_CODMTR AS CODMTR";          
          $sqlEv.="       ,COALESCE(MTR.MTR_RFID, '-') AS RFID";                    
          $sqlEv.="       ,COALESCE(MTR.MTR_NOME,'...') AS MOTORISTA";          
          $sqlEv.="       ,COALESCE(MVM.MVM_VELOCIDADE, 0) AS VELOCIDADE";
          $sqlEv.="       ,A.BIEV_ANOMES AS ANOMES";
          $sqlEv.="       ,'EV' AS INFRACAO";
          $sqlEv.="       ,UNI.UNI_APELIDO AS UNIDADE";
          $sqlEv.="       ,UNI.UNI_CODPOL AS POLO";
          $sqlEv.="  FROM BI_EXCESSOVELOC A";
          $sqlEv.="  LEFT OUTER JOIN MOVIMENTO MVM ON A.BIEV_POSICAO=MVM.MVM_POSICAO";
          $sqlEv.="  LEFT OUTER JOIN VEICULO VCL ON A.BIEV_CODVCL=VCL.VCL_CODIGO";
          $sqlEv.="  LEFT OUTER JOIN MOTORISTA MTR ON A.BIEV_CODMTR=MTR.MTR_CODIGO";
          $sqlEv.="  LEFT OUTER JOIN UNIDADE UNI ON A.BIEV_CODUNI=UNI.UNI_CODIGO";
          $sqlEv.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON A.BIEV_CODUNI=UU.UU_CODUNI AND UU.UU_CODUSR=".$lote[0]->codusu;            
          $sqlEv.=" WHERE (A.BIEV_ANOMES BETWEEN ".$lote[0]->dtini." AND ".$lote[0]->dtfim.")";
          $sqlEv.=$frota;
          $sqlEv.="   AND (A.BIEV_ENTRABI='S')";
          $sqlEv.="   AND (COALESCE(UU.UU_ATIVO,'')='S')";   
          //
          $sqlEvc="";
          $sqlEvc.="SELECT A.BIEVC_POSICAO AS POSICAO";
          $sqlEvc.="       ,A.BIEVC_CODUNI AS CODUNI";
          $sqlEvc.="       ,A.BIEVC_CODMTR AS CODMTR";          
          $sqlEvc.="       ,COALESCE(MTR.MTR_RFID, '-') AS RFID";
          $sqlEvc.="       ,COALESCE(MTR.MTR_NOME,'...') AS MOTORISTA";          
          $sqlEvc.="       ,COALESCE(MVM.MVM_VELOCIDADE, 0) AS VELOCIDADE";
          $sqlEvc.="       ,A.BIEVC_ANOMES AS ANOMES";
          $sqlEvc.="       ,'EVC' AS INFRACAO";
          $sqlEvc.="       ,UNI.UNI_APELIDO AS UNIDADE";
          $sqlEvc.="       ,UNI.UNI_CODPOL AS POLO";
          $sqlEvc.="  FROM BI_EXCESSOVELCH A";
          $sqlEvc.="  LEFT OUTER JOIN MOVIMENTO MVM ON A.BIEVC_POSICAO=MVM.MVM_POSICAO";
          $sqlEvc.="  LEFT OUTER JOIN VEICULO VCL ON A.BIEVC_CODVCL=VCL.VCL_CODIGO";          
          $sqlEvc.="  LEFT OUTER JOIN MOTORISTA MTR ON A.BIEVC_CODMTR=MTR.MTR_CODIGO";          
          $sqlEvc.="  LEFT OUTER JOIN UNIDADE UNI ON A.BIEVC_CODUNI=UNI.UNI_CODIGO";          
          $sqlEvc.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON A.BIEVC_CODUNI=UU.UU_CODUNI AND UU.UU_CODUSR=".$lote[0]->codusu;          
          $sqlEvc.=" WHERE (A.BIEVC_ANOMES BETWEEN ".$lote[0]->dtini." AND ".$lote[0]->dtfim.")";
          $sqlEvc.=$frota;          
          $sqlEvc.="   AND (A.BIEVC_ENTRABI='S')";
          $sqlEvc.="   AND (COALESCE(UU.UU_ATIVO,'')='S')";             
          
          $sql="";
          switch( $lote[0]->infracao ){
            case "EV"     : $sql=$sqlEv;                        
                            $sqlCor="EV";
                            break;
            case "EVC"    : $sql=$sqlEvc;                       
                            $sqlCor="EVC";
                            break;
            case "TODOS"  : $sql=$sqlEv." UNION ALL ".$sqlEvc;  
                            $sqlCor="TODOS";
                            break;
          };
          if( $retCls['retorno'] != "OK" ){
            $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';  
          } else {
            $params   = array();
            $options  = array("Scrollable" => SQLSRV_CURSOR_FORWARD);
            $consulta = sqlsrv_query($_SESSION['conn'], $sql, $params, $options);
            while ($linha = sqlsrv_fetch_array($consulta, SQLSRV_FETCH_ASSOC)) {
              $rfid=$linha["RFID"];
              $unid=$linha["UNIDADE"];
              
              $achei=false;
              $velocidade=$linha["VELOCIDADE"];
              
              foreach( $arrRet as &$lin ){
                if( ($lin["RFID"]==$rfid) and ($lin["UNIDADE"]==$unid)){  
                  $lin["INFRACAOMES"]     +=  ( $linha["ANOMES"]==$lote[0]->dtfim ? 1 : 0 );
                  $lin["INFRACAOANT"]     +=  ( $linha["ANOMES"]!=$lote[0]->dtfim ? 1 : 0 );
                  $lin["MAIORVELOCIDADE"] =   ($lin["MAIORVELOCIDADE"]<$velocidade ? $velocidade : $lin["MAIORVELOCIDADE"]);
                  $achei=true;
                  break;
                };
              };                
              
              if( $achei==false ){
                array_push($arrRet,[
                  "CODUNI"            =>  $linha["CODUNI"]
                  ,"CODMTR"           =>  $linha["CODMTR"]                
                  ,"RFID"             =>  $linha["RFID"]
                  ,"MOTORISTA"        =>  $linha["MOTORISTA"]
                  ,"INFRACAOMES"      =>  ( $linha["ANOMES"]==$lote[0]->dtfim ? 1 : 0 )
                  ,"INFRACAOANT"      =>  ( $linha["ANOMES"]!=$lote[0]->dtfim ? 1 : 0 )
                  ,"MAIORVELOCIDADE"  =>  $velocidade
                  ,"UNIDADE"          =>  $linha["UNIDADE"]
                  ,"POLO"             =>  $linha["POLO"]                  
                ]);
              };
            }; 
            unset($lin);
            //////////////////////////////////
            // Retornando para o JavaScript //
            //////////////////////////////////  
            $retJs=[];
            foreach( $arrRet as $linha ){
              ////////////////////////////////////////
              // Aqui colocando a cor em cada linha //
              ////////////////////////////////////////
              $corLinha="null";
              $numInfracao=($linha["INFRACAOANT"]+$linha["INFRACAOMES"]);
             
							if( $numInfracao==1 )
								$corLinha="yellow";
							if( $numInfracao>1 )
								$corLinha="#FF0000";
              
              array_push($retJs,[
                $linha["CODUNI"]              
                ,$linha["CODMTR"]
                ,$linha["RFID"]
                ,$linha["MOTORISTA"]
                ,$linha["INFRACAOMES"]
                ,($linha["INFRACAOANT"]+$linha["INFRACAOMES"])
                ,$linha["MAIORVELOCIDADE"]
                ,$linha["UNIDADE"]
                ,$linha["POLO"]                  
                ,$corLinha
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
        };
        ////////////////////////////////////
        // Detalhe de cada linha da grade //
        ////////////////////////////////////
        if( $rotina=="detalhe" ){
          switch( $lote[0]->frota ){
            case "LP" : $frota=" AND (VCL.VCL_FROTA IN('L','P'))" ;break;
            case "L"  : $frota=" AND (VCL.VCL_FROTA='L')"         ;break;
            case "P"  : $frota=" AND (VCL.VCL_FROTA='P')"         ;break;
          };
          
          $sqlEv="";          
          $sqlEv.="SELECT A.BIEV_POSICAO";
					$sqlEv.="		    ,CONVERT(VARCHAR(23),MVM.MVM_DATAGPS,127) AS MVM_DATAGPS";
          $sqlEv.="       ,UNI.UNI_APELIDO";
          $sqlEv.="       ,MVM.MVM_CODPOL";
          $sqlEv.="       ,MVM.MVM_PLACA";
          $sqlEv.="       ,COALESCE(VCL.VCL_FROTA,'*') AS VCL_FROTA";
          $sqlEv.="       ,MVM.MVM_RFID";
          $sqlEv.="       ,MVM.MVM_VELOCIDADE";
          $sqlEv.="       ,MVM.MVM_RPM";
					$sqlEv.="	      ,MVM.MVM_TURNO";
          $sqlEv.="       ,MTR.MTR_NOME";
          $sqlEv.="       ,MVM.MVM_LOCALIZACAO";
					$sqlEv.="	      ,MVM.MVM_CODEG";
          $sqlEv.="  FROM BI_EXCESSOVELOC A";
          $sqlEv.="  LEFT OUTER JOIN UNIDADE UNI ON A.BIEV_CODUNI=UNI.UNI_CODIGO";
          $sqlEv.="  LEFT OUTER JOIN MOVIMENTO MVM ON A.BIEV_POSICAO=MVM.MVM_POSICAO";
          $sqlEv.="  LEFT OUTER JOIN VEICULO VCL ON A.BIEV_CODVCL=VCL.VCL_CODIGO AND VCL.VCL_ATIVO='S'";
          $sqlEv.="  LEFT OUTER JOIN MOTORISTA MTR ON A.BIEV_CODMTR=MTR.MTR_CODIGO";
          $sqlEv.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON A.BIEV_CODUNI=UU.UU_CODUNI AND UU.UU_CODUSR=".$_SESSION['usr_codigo'];
          $sqlEv.=" WHERE (A.BIEV_ANOMES=".$lote[0]->dtini.")";
          $sqlEv.="   AND (A.BIEV_CODUNI=".$lote[0]->coduni.")";
          $sqlEv.="   AND (A.BIEV_CODMTR=".$lote[0]->codmtr.")";
          $sqlEv.=$frota;          
          $sqlEv.="   AND (A.BIEV_ENTRABI='S')"; 
          $sqlEv.="   AND (COALESCE(UU.UU_ATIVO,'')='S')";   

          $sqlEvc="";          
          $sqlEvc.="SELECT A.BIEVC_POSICAO";
					$sqlEvc.="		    ,CONVERT(VARCHAR(23),MVM.MVM_DATAGPS,127) AS MVM_DATAGPS";
          $sqlEvc.="       ,UNI.UNI_APELIDO";
          $sqlEvc.="       ,MVM.MVM_CODPOL";
          $sqlEvc.="       ,MVM.MVM_PLACA";
          $sqlEvc.="       ,COALESCE(VCL.VCL_FROTA,'*') AS VCL_FROTA";
          $sqlEvc.="       ,MVM.MVM_RFID";
          $sqlEvc.="       ,MVM.MVM_VELOCIDADE";
          $sqlEvc.="       ,MVM.MVM_RPM";
					$sqlEvc.="	      ,MVM.MVM_TURNO";
          $sqlEvc.="       ,MTR.MTR_NOME";
          $sqlEvc.="       ,MVM.MVM_LOCALIZACAO";
					$sqlEvc.="	      ,MVM.MVM_CODEG";
          $sqlEvc.="  FROM BI_EXCESSOVELCH A";
          $sqlEvc.="  LEFT OUTER JOIN UNIDADE UNI ON A.BIEVC_CODUNI=UNI.UNI_CODIGO";
          $sqlEvc.="  LEFT OUTER JOIN MOVIMENTO MVM ON A.BIEVC_POSICAO=MVM.MVM_POSICAO";
          $sqlEvc.="  LEFT OUTER JOIN VEICULO VCL ON A.BIEVC_CODVCL=VCL.VCL_CODIGO AND VCL.VCL_ATIVO='S'";
          $sqlEvc.="  LEFT OUTER JOIN MOTORISTA MTR ON A.BIEVC_CODMTR=MTR.MTR_CODIGO";
          $sqlEvc.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON A.BIEVC_CODUNI=UU.UU_CODUNI AND UU.UU_CODUSR=".$_SESSION['usr_codigo'];
          $sqlEvc.=" WHERE (A.BIEVC_ANOMES=".$lote[0]->dtini.")";
          $sqlEvc.="   AND (A.BIEVC_CODUNI=".$lote[0]->coduni.")";
          $sqlEvc.="   AND (A.BIEVC_CODMTR=".$lote[0]->codmtr.")";
          $sqlEvc.=$frota;          
          $sqlEvc.="   AND (A.BIEVC_ENTRABI='S')"; 
          $sqlEvc.="   AND (COALESCE(UU.UU_ATIVO,'')='S')";   

          $sql="";
          switch( $lote[0]->infracao ){
            case "EV"     : $sql=$sqlEv;                        break;
            case "EVC"    : $sql=$sqlEvc;                       break;
            case "TODOS"  : $sql=$sqlEv." UNION ALL ".$sqlEvc;  break;
          };
          $classe->msgSelect(false);
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
<!DOCTYPE html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="SKYPE_TOOLBAR" content="SKYPE_TOOLBAR_PARSER_COMPATIBLE" />
    <link rel="icon" type="image/png" href="imagens/logo_aba.png" />
    <title>Infração/tempo</title>
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
        background-color:white;
        padding-top:5px;
        padding-left:3px;
        width:105em;
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
        // comboCompetencia("classif_mot",document.getElementById("cbIni"));			
        jsBi={
          "titulo":[
             {"id":0  ,"labelCol":"OPC"     
                      ,"padrao":1}            
            ,{"id":1  ,"labelCol"       : "CODUNI"
                      ,"fieldType"      : "int"
                      ,"align"          : "center"                                      
                      ,"tamGrd"         : "0em"
                      ,"tamImp"         : "0"
                      ,"padrao":0}
            ,{"id":2  ,"labelCol"       : "CODMTR"
                      ,"fieldType"      : "int"
                      ,"align"          : "center"                                      
                      ,"tamGrd"         : "0em"
                      ,"tamImp"         : "0"
                      ,"padrao":0}
            ,{"id":3  ,"labelCol"       : "RFID"
                      ,"fieldType"      : "str"
                      ,"tamGrd"         : "10em"
                      ,"tamImp"         : "25"
                      ,"excel"          : "S"
                      ,"ordenaColuna"   : "S"
                      ,"padrao":0}
            ,{"id":4  ,"labelCol"       : "MOTORISTA"
                      ,"fieldType"      : "str"
                      ,"tamGrd"         : "35em"
                      ,"tamImp"         : "70"
                      ,"excel"          : "S"
                      ,"ordenaColuna"   : "S"
                      ,"padrao":0}
            ,{"id":5  ,"labelCol"       : "MES"
                      ,"fieldType"      : "int"
                      ,"align"          : "center"                                      
                      ,"tamGrd"         : "3em"
                      ,"tamImp"         : "15"
                      ,"excel"          : "S"
                      ,"ordenaColuna"   : "S"
											,"somarImp"				: "S"	
                      ,"padrao":0}
            ,{"id":6  ,"labelCol"       : "ANO"
                      ,"fieldType"      : "int"
                      ,"align"          : "center"                                      
                      ,"tamGrd"         : "3em"
                      ,"tamImp"         : "15"
                      ,"excel"          : "S"
                      ,"ordenaColuna"   : "S"
											,"somarImp"				: "S"
                      ,"padrao":0}
            ,{"id":7  ,"labelCol"       : "MAIOR_VELOC"
                      ,"labelColImp"    : "VEL"
                      ,"fieldType"      : "int"
                      ,"align"          : "center"                                      
                      ,"tamGrd"         : "8em"
                      ,"tamImp"         : "15"
                      ,"excel"          : "S"
                      ,"ordenaColuna"   : "S"
                      ,"padrao":0}
            ,{"id":8  ,"labelCol"       : "UNIDADE"
                      ,"fieldType"      : "str"
                      ,"tamGrd"         : "12em"
                      ,"tamImp"         : "25"
                      ,"excel"          : "S"
                      ,"ordenaColuna"   : "S"
                      ,"padrao":0}
            ,{"id":9  ,"labelCol"       : "POLO"
                      ,"fieldType"      : "str"
                      ,"align"          : "center"
                      ,"tamGrd"         : "8em"
                      ,"tamImp"         : "12"
                      ,"excel"          : "S"
                      ,"ordenaColuna"   : "S"
                      ,"padrao":0}
            ,{"id":10 ,"labelCol"       : "COR"
                      ,"fieldType"      : "str"
                      ,"tamGrd"         : "0em"
                      ,"tamImp"         : "0"
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
             {"texto":"Detalhe"       ,"name":"biDetalhe"       ,"onClick":"7"   ,"enabled":true,"imagem":"fa fa-sort-desc"     ,"ajuda":"Detalhe do registro" }
            ,{"texto":"Imprimir"      ,"name":"biImprimir"      ,"onClick":"3"   ,"enabled":true,"imagem":"fa fa-print"         ,"ajuda":"Exportar para excel" }             
            ,{"texto":"Excel"         ,"name":"biExcel"         ,"onClick":"5"   ,"enabled":true,"imagem":"fa fa-file-excel-o"  ,"ajuda":"Exportar para excel" }
            ,{"texto":"Fechar"        ,"name":"biFechar"        ,"onClick":"8"   ,"enabled":true ,"imagem":"fa fa-close"        ,"ajuda":"Fechar formulario" }
          ] 
          ,"registros"      : []                        // Recebe um Json vindo da classe clsBancoDados
          ,"corLinha"       : "if(ceTr.cells[10].innerHTML !='null') {ceTr.style.backgroundColor=ceTr.cells[10].innerHTML;}"      
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
          ,"width"          : "105em"                   // Tamanho da table
          ,"height"         : "54em"                    // Altura da table
          ,"tableLeft"      : "sim"                     // Se tiver menu esquerdo
          ,"relTitulo"      : "BI"                      // Titulo do relatório
          ,"relOrientacao"  : "P"                       // Paisagem ou retrato
          ,"relFonte"       : "8"                       // Fonte do relatório
          ,"indiceTable"    : "ANO"               // Indice inicial da table
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
      var objMtr;                     //
      var jsMtr;                      // Obj da composição do evento
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
      ////////////////////////////
      // Filtrando os registros //
      ////////////////////////////
      function btnFiltrarClick() { 
          //////////////////////////////////////////////
          //Buscando a dada de inicio/fim para select //
          //////////////////////////////////////////////
          //alert(bl("cbIni").value());    
          var splt=document.getElementById("cbIni").value.split('|');
          //
					clsJs   = jsString("lote");  
					clsJs.add("rotina"  	, "select"                                  	);
					clsJs.add("login"   	, jsPub[0].usr_login                        	);
					clsJs.add("codusu"  	, jsPub[0].usr_codigo                       	);
					clsJs.add("dtini"   	, splt[1]   																	);
					clsJs.add("dtfim"   	, splt[0]   																	);          
					clsJs.add("frota"   	, document.getElementById("cbFrota").value  	);
					clsJs.add("infracao"	, document.getElementById("cbInfracao").value	);
					fd = new FormData();
					fd.append("grdClassifMotorista" , clsJs.fim());
					msg     = requestPedido("Trac_grdClassifMotorista.php",fd); 
					retPhp  = JSON.parse(msg);
					if( retPhp[0].retorno == "OK" ){
						//////////////////////////////////////////////////////////////////////////////////
						// O novo array não tem o campo idUnico mas a montarHtmlCE2017 ja foi executada //
						// Campo obrigatório se existir rotina de manutenção na table devido Json       //
						// Esta rotina não tem manutenção via classe clsTable2017                       //
						// jsCrv.registros=objCrv.addIdUnico(retPhp[0]["dados"]);                       //
						//////////////////////////////////////////////////////////////////////////////////
						jsBi.registros=objBi.addIdUnico(retPhp[0]["dados"]);
						//jsBi.relTitulo="BI Infração/Tempo em "+document.getElementById("cbIni").value;
						jsBi.relTitulo="BI Infração/Tempo em "+document.getElementById("cbIni").options[document.getElementById("cbIni").selectedIndex].text;
						objBi.ordenaJSon(jsBi.indiceTable,false);  
						objBi.montarBody2017();
					};  
				//};	
      }; 
      ////////////////////////////////
      //          DETALHE           //
      ////////////////////////////////
      function biDetalheClick(){
        //////////////////////////////////////////////
        //Buscando a dada de inicio/fim para select //
        //////////////////////////////////////////////    
        var splt=document.getElementById("cbIni").value.split('|');
        
        try{        
          clsChecados = objBi.gerarJson("1");
          chkds       = clsChecados.gerar();
          if( chkds[0].MES==0 ){
            gerarMensagemErro("vel","Nenhuma infração do mês para detalhe!","Aviso");            
          } else {
            clsJs       = jsString("lote");  
            clsJs.add("rotina"  , "detalhe"                                   );
            clsJs.add("login"   , jsPub[0].usr_login                          );
            clsJs.add("codusu"  , jsPub[0].usr_codigo                         );
            clsJs.add("coduni"  , chkds[0].CODUNI                             );
            clsJs.add("codmtr"  , chkds[0].CODMTR                             );
            clsJs.add("dtini"   , splt[0]                                     );
            clsJs.add("infracao", document.getElementById("cbInfracao").value );
            clsJs.add("frota"   , document.getElementById("cbFrota").value    );
            fd          = new FormData();
            fd.append("grdClassifMotorista" , clsJs.fim());          
            var req = requestPedido("Trac_grdClassifMotorista.php",fd);
            var ret = JSON.parse(req);
            if( ret[0].dados.length==0 ){
              gerarMensagemErro("ALV","NENHUM REGISTRO LOCALIZADO","AVISO");  
            } else {
              if( ret[0].retorno == "OK" ){
                // Arrumando a exibição de data para data e hora no padrão brasileiro
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
                              ,"tamGrd"         : "11em"
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
                              ,"tamGrd"         : "7em"
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
                              ,"tamGrd"         : "5em"
                              ,"tamImp"         : "10"
                              ,"ajudaCampo"     : ["Velocidade"]
                              ,"padrao":0}
                    ,{"id":10  ,"field"          : "MVM_RPM"   
                              ,"fieldType"      : "int"
                              ,"align"          : "center"
                              ,"labelCol"       : "RPM"
                              ,"obj"            : "edtRpm"
                              ,"tamGrd"         : "5em"
                              ,"tamImp"         : "10"
                              ,"ajudaCampo"     : ["Rpm"]
                              ,"padrao":0}
                    ,{"id":11 ,"field"          : "MVM_TURNO"   
                              ,"labelCol"       : "T"
                              ,"obj"            : "edtTurno"
                              ,"tamGrd"         : "1em"
                              ,"tamImp"         : "5"
                              ,"ajudaCampo"     : ["Turno"]
                              ,"padrao":0}
                    ,{"id":12 ,"field"          : "MTR_NOME"   
                              ,"fieldType"      : "str"
                              ,"labelCol"       : "MOTORISTA"
                              ,"obj"            : "edtMotorista"
                              ,"tamGrd"         : "25em"
                              ,"tamImp"         : "80"
                              ,"ajudaCampo"     : ["Motorista"]
                              ,"padrao":0}
                    ,{"id":13 ,"field"          : "BIEV_LOCALIZACAO"   
                              ,"fieldType"      : "str"
                              ,"labelCol"       : "LOCALIZACAO"
                              ,"obj"            : "edtLocalizacao"
                              ,"tamGrd"         : "30em"
                              ,"tamImp"         : "80"
                              ,"ajudaCampo"     : ["Localizacao"]
                              ,"padrao":0}
                    ,{"id":14 ,"field"          : "MTR_CODEG"   
                              ,"fieldType"      : "str"
                              ,"labelCol"       : "INFRACAO"
                              ,"obj"            : "edtInfracao"
                              ,"tamGrd"         : "6em"
                              ,"tamImp"         : "15"
                              ,"ajudaCampo"     : ["Tipo infracao"]
                              ,"padrao":0}
                  ]  
                  , 
                  "botoesH":[
                     {"texto":"Excel"     ,"name":"detExcel"  ,"onClick":"5"  ,"enabled":true,"imagem":"fa fa-file-excel-o"     ,"ajuda":"Exportar para excel" }
                    ,{"texto":"Retornar"  ,"name":"detVoltar" ,"onClick":"7"  ,"enabled":true ,"imagem":"fa fa-plus","ajuda":"Retorna a tela anterior" }
                  ] 
                  ,"registros"      : ret[0].dados              // Recebe um Json vindo da classe clsBancoDados
                  //,"corLinha"       : "if(ceTr.cells[10].innerHTML =='"+chkds[0].TURNO+"') {ceTr.style.color='black';ceTr.style.backgroundColor='#E9967A';}"
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
          };
        }catch(e){
          gerarMensagemErro("catch",e.message,"Erro");
        };
      };
      //
      //
      function detVoltarClick(){
        document.getElementById("btnDetalhe").click();
        window.location.href="#ancoraCabec";
      };
      ///////////////////////////
      // Fechando o formulario //
      ///////////////////////////
      function biFecharClick(){
        window.close();
      };
    </script>
  </head>
  <body>
    <div id="divCabec" class="comboSobreTable" style="margin-top:5px;float:left;">
      <a name="ancoraCabec"></a> 
      <!-- <div class="campotexto campo15">
        <select class="campo_input_combo" id="cbIni">
					
          <option value="201805|201805">MAI/18</option>
          <option value="201806|201805">JUN/18</option>
          <option value="201807|201805">JUL/18</option>
          <option value="201808|201805">AGO/18</option>
          <option value="201809|201805">SET/18</option>
          <option value="201810|201805">OUT/18</option>
          <option value="201811|201805">NOV/18</option>
          <option value="201812|201805">DEZ/18</option>
					
        </select>
        <label class="campo_label campo_required" for="cbIni">INICIO</label>
      </div> -->

      <?php $mesAntigoPipe=true; include 'classPhp/comum/selectMes.class.php';?>
    
      <div class="campotexto campo15">
        <select class="campo_input_combo" id="cbFrota">
          <option value="LP" selected="selected">Leve/Pesado</option>
          <option value="P">Pesado</option>
          <option value="L">Leve</option>
        </select>
        <label class="campo_label campo_required" for="cbFrota">FROTA</label>
      </div>
      <!--
      <div class="campotexto campo15">
        <select class="campo_input_combo" id="cbTempo">
          <option value="0" selected="selected">Todos</option>
          <option value="1">00:00:01</option>
          <option value="2">00:00:02</option>
          <option value="3">00:00:03</option>
          <option value="4">00:00:04</option>
          <option value="5">00:00:05</option>
          <option value="10">00:00:10</option>
          <option value="15">00:00:15</option>
          <option value="20">00:00:20</option>
        </select>
        <label class="campo_label campo_required" for="cbTempo">TEMPO MAIOR:</label>
      </div>
      <div id="divCbErro" class="campotexto campo10">
        <select class="campo_input_combo" id="cbErro">
          <option value="S" selected="selected">Mostrar</option>
          <option value="N">Inibir</option>
        </select>
        <label class="campo_label campo_required" for="cbErro">ERRO</label>
      </div>
      -->
      <div id="divCbErro" class="campotexto campo10">
        <select class="campo_input_combo" id="cbInfracao">
          <option value="TODOS" selected="selected">EV/EVC</option>
          <option value="EV">EV</option>
					<option value="EVC">EVC</option>
        </select>
        <label class="campo_label campo_required" for="cbErro">Infração</label>
      </div>

      <div class="campo10" style="float:left;">            
        <input id="btnFilttrar" onClick="btnFiltrarClick();" type="button" value="Filtrar" class="botaoSobreTable"/>
      </div>
      <!--
      <div style="float:left;">
        <div id="btnFiltrar" class="botaoImagemSup-icon-big" onclick="btnFiltrarClick();" style="padding-top:7px;"><i class="fa fa-filter" style="font-size:1.4em;"></i>Filtrar</div>      
      </div>
      -->
      
    </div>
    <div class="divTelaCheia" style="float:left;">
      <div id="divContabil" class="conteudo" style="display:block;overflow-x:auto;position:relative;float:left;width:110em;height:55em;">
        <div id="divTopoInicio">
        </div>
      </div>
      <a name="ancoraDetalhe">
      <button id="btnDetalhe"
              class="acordeon"
              style="width:25%;margin-left:0.1em;">Detalhe do registro</button>
      <div class="acrdnDiv" style="width:90%;margin-left:0.1em;height:42em;">
        <div id="divDetalhe" class="conteudo" style="position:relative;float:left;height:41em;width:150em;">
          <div id="divDetalheReg">
          </div>
        </div>
      </div>
      <!--
      <a name="ancoraMotorista">
      <button id="btnMotorista"
              class="acordeon"
              style="width:25%;margin-left:0.1em;">Motorista</button>
      <div class="acrdnDiv" style="width:78%;margin-left:0.1em;height:42em;">
        <div id="divMtr" class="conteudo" style="position:relative;float:left;height:41em;width:78%;">
          <div id="divMtrReg">
          </div>
        </div>
      </div>
      -->

      <form method="post" name="frmScf" class="center" id="frmScf" action="classPhp/imprimirsql.php" target="_newpage" style="position:fixed;top:10em;width:90em;z-index:30;display:none;">
        <input type="hidden" id="sql" name="sql"/>
      </form>
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