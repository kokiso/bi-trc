<?php
  session_start();
  if( isset($_POST["grdPeriodoIndevido"]) ){
    try{     
      require("classPhp/conectaSqlServer.class.php");
      require("classPhp/validaJson.class.php"); 
      require("classPhp/removeAcento.class.php");
      require("classPhp/selectRepetidoTrac.class.php");       
      
      function diferenca($parI,$parF){
        $dtI      = new DateTime($parI); 
        $dtF      = new DateTime($parF);
        $dteDiff  = $dtI->diff($dtF); 
        return $dteDiff->format("%H:%I:%S"); 
      };  

      $vldr     = new validaJSon();          
      $retorno  = "";
      $retCls   = $vldr->validarJs($_POST["grdPeriodoIndevido"]);
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
          /////////////////////////////////////////////////////
          // EVENTOS IGNORADOS POR NAO INTERFERIR NA FORMULA //
          // 027=VEICULO PARADO C/ LIG IGNICAO               //
          // 031=BATERIA DO VEICULO VIOLADA                  //
          // 033-BATERIA DO VEICULO DESVIOLADA               //
          // 037-PARADO COM A IGNICAO LIGADA                 //
          // 044-IGNICAO LIGADA                              //
          // 052=IGNICAO DESLIGADA                           //
          // 055=POSICAO TEMPORIZADA                         //
          // 064=CONECTADO NA FONTE PRINCIPAL                //
          // 066=CONECTADO NA BATERIA BACKUP                 //
          // 067=DESCONECTADO DA BATERIA BACKUP              //
          // 080=SENSOR DE FORCA G                           //
          // 084=SENSOR DE JAMMING ATIVADO                   //
          // 085=SENSOR DE JAMMING DESATIVADO                //
          /////////////////////////////////////////////////////
          switch( $lote[0]->frota ){
            case "LP" : $frota=" AND (VCL.VCL_FROTA IN('L','P'))" ;break;
            case "L"  : $frota=" AND (VCL.VCL_FROTA='L')"         ;break;
            case "P"  : $frota=" AND (VCL.VCL_FROTA='P')"         ;break;
          };

          $gpo="";

          if( $lote[0]->grupoOperacional != 'TODOS' ) {
            $gpo = " AND (VCL.VCL_CODGPO=".$lote[0]->grupoOperacional.")";
          }
          ///////////////////////////////////////////////////////////////
          // Buscando um facilitador para indice devido tamanho da tabela
          ///////////////////////////////////////////////////////////////  
          $cSql   = new SelectRepetido();
          $retSql = $cSql->qualSelect("intervalo",$lote[0]->dtini);
          $expld = explode("|",$retSql);
          if( $expld[0]>0 ){
						$bwI=$expld[0];
						$bwF=$expld[1];
          };
          $sql="";
          $sql.="SELECT";
          $sql.="  MVM_POSICAO";
          $sql.="  ,MTR_NOME";
          $sql.="  ,MVM_PLACA";
          $sql.="  ,VCL.VCL_FROTA";
          $sql.="  ,MVM_TURNO";
          $sql.="  ,MVM_CODMTR";
          $sql.="  ,MVM_CODEVE";
          $sql.="  ,MVM_CODEG";
          $sql.="  ,MVM_VELOCIDADE";
          $sql.="  ,CONVERT(VARCHAR(23),MVM_DATAGPS,127) AS MVM_DATAGPS";
          $sql.="  ,MVM_HORAGPS";
					$sql.="  ,MTR_RFID";
          $sql.="  ,UNI.UNI_APELIDO AS UNIDADE";
          $sql.="  ,UNI.UNI_CODPOL AS POLO";
          $sql.="  FROM MOVIMENTO";
          $sql.="  LEFT OUTER JOIN EVENTO EVE ON MVM_CODEVE=EVE.EVE_CODIGO";
          $sql.="  LEFT OUTER JOIN VEICULO VCL ON MVM_PLACA=VCL.VCL_CODIGO";
          $sql.="  LEFT OUTER JOIN MOTORISTA MTR ON MVM_CODMTR=MTR.MTR_CODIGO";
          $sql.="  LEFT OUTER JOIN UNIDADE UNI ON MVM_CODUNI=UNI.UNI_CODIGO";          
          $sql.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON MVM_CODUNI=UU.UU_CODUNI AND UU.UU_CODUSR=".$lote[0]->codusu;  
          $sql.=" WHERE ((MVM_POSICAO BETWEEN ".$bwI." AND ".$bwF.")"; 
          $sql.="   AND (MVM_ANOMES=".$lote[0]->dtini.")"; 
          $sql.="   AND (MVM_CODEG IN('FPI','ID'))";
          $sql.="   AND (MVM_ENTRABI='S')"; 
          $sql.="   AND (EVE_MOVIMENTO='S')";
          $sql.="   AND (COALESCE(UU.UU_ATIVO,'')='S')";   					
          $sql.="   AND (VCL.VCL_ENTRABI='S') ".$frota.")";
          $sql.=$gpo;
          $sql.=" ORDER BY MVM_PLACA,CONVERT(VARCHAR(23),MVM_DATAGPS,127)";
					//file_put_contents("aaa.xml",$sql);					
          if( $retCls['retorno'] != "OK" ){
            $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';  
          } else {
						$tamArrRet=0;
						// Pegando a ultima placa e guardando a posicao no vertor
						$indicePLACA		=	"";
						$indicePOSICAO	=	0;
						$tamArrRet			= 0;
            $params   = array();
            $options  = array("Scrollable" => SQLSRV_CURSOR_FORWARD);
            $consulta = sqlsrv_query($_SESSION['conn'], $sql, $params, $options);
            while ($linha = sqlsrv_fetch_array($consulta, SQLSRV_FETCH_ASSOC)) {

              if( $linha["MVM_CODEG"]=="FPI" ){
                $gravar = true;
								
								$ini=0;
								if( $linha["MVM_PLACA"]==$indicePLACA )
									$ini=$indicePOSICAO;
								for( $lin=$ini;$lin<$tamArrRet;$lin++ ){
                  if( ($arrRet[$lin]["PLACA"]==$linha["MVM_PLACA"]) and ($arrRet[$lin]["STATUS"]=="iniciado") ){
                    $gravar=false;
                    break;
                  };
								};	
								/*	
                foreach( $arrRet as $reg ){
                  if( ($reg["PLACA"]==$linha["MVM_PLACA"]) and ($reg["STATUS"]=="iniciado") ){
                    $gravar=false;
                    break;
                  };
                };
								*/
                if( $gravar ){
                  array_push($arrRet,[
                    "PLACA"         =>  $linha["MVM_PLACA"]
                    ,"LP"           =>  $linha["VCL_FROTA"]
                    ,"TURNO"        =>  $linha["MVM_TURNO"]
                    ,"IDINI"        =>  $linha["MVM_POSICAO"]
                    ,"DTINI"        =>  $linha["MVM_DATAGPS"]
                    ,"IDFIM"        =>  "**erro**"
                    ,"DTFIM"        =>  $linha["MVM_DATAGPS"]
                    ,"TEMPO"        =>  "**erro**"
										,"CODEG"        =>  $linha["MVM_CODEG"]
                    ,"MOTORISTA"    =>  $linha["MTR_NOME"]
										,"RFID"    			=>  $linha["MTR_RFID"]
										,"UNIDADE"    	=>  $linha["UNIDADE"]
										,"POLO"    			=>  $linha["POLO"]										
                    ,"STATUS"       =>  "iniciado"
                  ]);
									$tamArrRet++;
									$indicePLACA		=	$linha["MVM_PLACA"];
									$indicePOSICAO	=	(count($arrRet)-1);
									//$indicePOSICAO=0;
                };
              };
              if( ($linha["MVM_CODEG"]=="ID") and ($tamArrRet>0) ){
								/*
                foreach( $arrRet as &$atu ){
                  if( ($atu["PLACA"]==$linha["MVM_PLACA"]) and ($atu["STATUS"]=="iniciado") ){
                    $atu["IDFIM"]=$linha["MVM_POSICAO"];  
                    $atu["DTFIM"]=$linha["MVM_DATAGPS"];
                    $atu["TEMPO"]=diferenca($atu["DTINI"],$atu["DTFIM"]);
										$atu["STATUS"]="fim";
                    break;
                  };
                };
								//unset($atu);
								*/
                //$tam=count($arrRet);
								$ini=0;
								if( $linha["MVM_PLACA"]==$indicePLACA )
									$ini=$indicePOSICAO;
								
                for( $lin=$ini;$lin<$tamArrRet;$lin++ ){
                  if( ($arrRet[$lin]["PLACA"]==$linha["MVM_PLACA"]) and ($arrRet[$lin]["STATUS"]=="iniciado") ){
                    $arrRet[$lin]["IDFIM"]=$linha["MVM_POSICAO"];  
                    $arrRet[$lin]["DTFIM"]=$linha["MVM_DATAGPS"];
                    $arrRet[$lin]["TEMPO"]=diferenca($arrRet[$lin]["DTINI"],$arrRet[$lin]["DTFIM"]);
										$arrRet[$lin]["STATUS"]="fim";
                    break;
                  };    
                };
								
              };
            }; 
            //////////////////////////////////
            // Retornando para o JavaScript //
            //////////////////////////////////  
            $arrJs=[];
            $qtos    = count($arrRet);
            $lin=0;
            
            while($lin<$qtos){
              $gravar=true;  
              if( $lote[0]->tempo>0 ){
                $gravar=false;  
                $numSeg=str_replace(":","",$arrRet[$lin]["TEMPO"]);
                if( $numSeg>$lote[0]->tempo ){
                  $gravar=true;    
                };
              };
              //  
              if( ($gravar) and ($lote[0]->erro=="N") and ($arrRet[$lin]["IDFIM"]=="**erro**") )
                $gravar=false;    
              if( $gravar ){
								//
                array_push($arrJs,[
                  $arrRet[$lin]["PLACA"]
                  ,$arrRet[$lin]["LP"]
                  ,$arrRet[$lin]["TURNO"]
                  ,$arrRet[$lin]["IDINI"]
                  ,$arrRet[$lin]["DTINI"]
									,$arrRet[$lin]["IDFIM"]
                  ,$arrRet[$lin]["DTFIM"]
                  ,$arrRet[$lin]["TEMPO"]
                  ,$arrRet[$lin]["MOTORISTA"]
									,$arrRet[$lin]["CODEG"]
									,$arrRet[$lin]["RFID"]
									,$arrRet[$lin]["UNIDADE"]
									,$arrRet[$lin]["POLO"]
                ]);
              }  
              $lin++;
            };  
            $retorno='[{"retorno":"OK","dados":'.json_encode($arrJs).',"erro":""}]'; 
          };  
        };
        if( $rotina=="detalhe" ){
          switch( $lote[0]->frota ){
            case "LP" : $frota=" AND (VCL.VCL_FROTA IN('L','P'))" ;break;
            case "L"  : $frota=" AND (VCL.VCL_FROTA='L')"         ;break;
            case "P"  : $frota=" AND (VCL.VCL_FROTA='P')"         ;break;
          };  
          $sql="";
          $sql.="SELECT";
          $sql.="  MVM_POSICAO";
          $sql.="  ,MVM_PLACA";
          $sql.="  ,MVM_TURNO";
          $sql.="  ,EVE_NOME";
          $sql.="  ,MVM_VELOCIDADE";
          $sql.="  ,CONVERT(VARCHAR(23),MVM_DATAGPS,127) AS MVM_DATAGPS";
          $sql.="  ,UNI.UNI_APELIDO";
          $sql.="  ,UNI.UNI_CODPOL";
          $sql.="  ,MVM_LATITUDE";
          $sql.="  ,MVM_LONGITUDE";
          $sql.="  ,MVM_LOCALIZACAO";	
					$sql.="  ,MVM_IGNICAO";						
          $sql.="  FROM MOVIMENTO";
          $sql.="  LEFT OUTER JOIN EVENTO EVE ON MVM_CODEVE=EVE.EVE_CODIGO";
          $sql.="  LEFT OUTER JOIN VEICULO VCL ON MVM_PLACA=VCL.VCL_CODIGO";
          $sql.="  LEFT OUTER JOIN MOTORISTA MTR ON MVM_CODMTR=MTR.MTR_CODIGO";
          $sql.="  LEFT OUTER JOIN UNIDADE UNI ON MVM_CODUNI=UNI.UNI_CODIGO";          
          $sql.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON MVM_CODUNI=UU.UU_CODUNI AND UU.UU_CODUSR=".$lote[0]->codusu;          
          $sql.=" WHERE (";
          $sql.="   (MVM_POSICAO BETWEEN ".$lote[0]->idIni." AND ".$lote[0]->idFim.")";
          $sql.="   AND (MVM_ENTRABI='S')"; 					
          $sql.="   AND (VCL.VCL_ENTRABI='S') ".$frota;
          $sql.="   AND (MVM_PLACA='".$lote[0]->placa."')";
					$sql.="   AND (MVM_DATAGPS>='".$lote[0]->dtini."')";
					$sql.="   AND (COALESCE(UU.UU_ATIVO,'')='S')";   					
          $sql.=" )";
          $sql.=" ORDER BY MVM_DATAGPS";
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
        width:110em;
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
            ,{"id":1  ,"labelCol"       : "PLACA"
                      ,"fieldType"      : "str"
                      ,"tamGrd"         : "6em"
                      ,"tamImp"         : "15"
                      ,"excel"          : "S"
                      ,"ordenaColuna"   : "S"
                      ,"padrao":0}
            ,{"id":2  ,"labelCol"       : "LP"
                      ,"fieldType"      : "str"
                      ,"tamGrd"         : "2em"
                      ,"tamImp"         : "8"
                      ,"excel"          : "S"
                      ,"ordenaColuna"   : "S"
                      ,"padrao":0}
            ,{"id":3  ,"labelCol"       : "T"
                      ,"fieldType"      : "str"
                      ,"tamGrd"         : "1em"
                      ,"tamImp"         : "5"
                      ,"excel"          : "S"
                      ,"ordenaColuna"   : "S"
                      ,"padrao":0}
            ,{"id":4  ,"labelCol"       : "IDINI"
                      ,"fieldType"      : "str"
                      ,"tamGrd"         : "7em"
                      ,"tamImp"         : "18"
                      ,"excel"          : "S"
                      ,"ordenaColuna"   : "S"
                      ,"padrao":0}
            ,{"id":5  ,"labelCol"       : "DTINI"
                      ,"fieldType"      : "str"
                      ,"tamGrd"         : "12em"
                      ,"tamImp"         : "35"
                      ,"excel"          : "S"
                      ,"ordenaColuna"   : "S"
                      ,"padrao":0}
            ,{"id":6  ,"labelCol"       : "IDFIM"
                      ,"fieldType"      : "str"
                      ,"tamGrd"         : "7em"
                      ,"tamImp"         : "18"
                      ,"excel"          : "S"
                      ,"ordenaColuna"   : "S"
                      ,"padrao":0}
            ,{"id":7  ,"labelCol"       : "DTFIM"
                      ,"fieldType"      : "str"
                      ,"tamGrd"         : "12em"
                      ,"tamImp"         : "35"
                      ,"excel"          : "S"
                      ,"ordenaColuna"   : "S"
                      ,"padrao":0}
            ,{"id":8  ,"labelCol"       : "TEMPO"
                      ,"fieldType"      : "str"
                      ,"tamGrd"         : "6em"
                      ,"tamImp"         : "18"
                      ,"excel"          : "S"
                      ,"ordenaColuna"   : "S"
                      ,"padrao":0}
            ,{"id":9 ,"labelCol"       : "MOTORISTA"
                      ,"fieldType"      : "str"
                      ,"tamGrd"         : "20em"
                      ,"tamImp"         : "60"
                      ,"excel"          : "S"
                      ,"ordenaColuna"   : "S"
                      ,"padrao":0}
            ,{"id":10 ,"labelCol"       : "EVE"
                      ,"fieldType"      : "str"
                      ,"tamGrd"         : "3em"
                      ,"tamImp"         : "10"
                      ,"excel"          : "S"
                      ,"ordenaColuna"   : "S"
                      ,"padrao":0}
            ,{"id":11 ,"labelCol"       : "RFID"
                      ,"fieldType"      : "str"
                      ,"tamGrd"         : "10em"
                      ,"tamImp"         : "0"
                      ,"excel"          : "S"
                      ,"ordenaColuna"   : "S"
                      ,"padrao":0}
						,{"id":12 ,"labelCol"       : "UNIDADE"
											,"fieldType"      : "str"
											,"obj"            : "edtUnidade"
											,"tamGrd"         : "10em"
											,"tamImp"         : "25"
											,"ajudaCampo"     : ["Unidade"]
											,"padrao":0}
						,{"id":13 ,"labelCol"       : "POLO"
											,"fieldType"      : "str"
											,"tamGrd"         : "4em"
											,"tamImp"         : "15"
											,"ajudaCampo"     : ["Polo"]
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
             {"texto":"Detalhe"       	,"name":"biDetalhe"       ,"onClick":"7"   ,"enabled":true,"imagem":"fa fa-sort-desc"  ,"ajuda":"Detalhe do registro" }
            ,{"texto":"Motorista"     	,"name":"biMotorista"     ,"onClick":"7"   ,"enabled":true,"imagem":"fa fa-user"  ,"ajuda":"Acumular por motorista" }             
            ,{"texto":"Imprimir"      	,"name":"biImprimir"      ,"onClick":"3"   ,"enabled":true,"imagem":"fa fa-print"  ,"ajuda":"Exportar para excel" }             
            ,{"texto":"Excel"         	,"name":"biExcel"         ,"onClick":"5"   ,"enabled":true,"imagem":"fa fa-file-excel-o"  ,"ajuda":"Exportar para excel" }
						,{"texto":"Desmarcar todos" ,"name":"biDesmarcar"   	,"onClick":"7"   ,"enabled":true,"imagem":"fa fa-check"  ,"ajuda":"Exportar para excel" }
            ,{"texto":"Fechar"        	,"name":"biFechar"        ,"onClick":"8"   ,"enabled":true ,"imagem":"fa fa-close"        ,"ajuda":"Fechar formulario" }
          ] 
          ,"registros"      : []                        // Recebe um Json vindo da classe clsBancoDados
          ,"corLinha"       : "if(ceTr.cells[6].innerHTML =='**erro**') {ceTr.style.color='black';ceTr.style.backgroundColor='#E9967A';}"      
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
          ,"width"          : "110em"                   // Tamanho da table
          ,"height"         : "54em"                    // Altura da table
          ,"tableLeft"      : "sim"                     // Se tiver menu esquerdo
          ,"relTitulo"      : "BI"                      // Titulo do relatório
          ,"relOrientacao"  : "P"                       // Paisagem ou retrato
          ,"relFonte"       : "8"                       // Fonte do relatório
          //,"indiceTable"    : "QTOS"                    // Indice inicial da table
          ,"tamBotao"       : "12"                      // Tamanho botoes defalt 12 [12/25/50/75/100]
          ,"codTblUsu"      : "MOVIMENTORESUMO[00]"                          
          ,"codDir"         : intCodDir
        }; 
        if( objBi === undefined ){  
          objBi=new clsTable2017("objBi");
        };
        //////////////////////////////////////////////////////////////
        // Usuarios externos não tem a opção de selecionar sem erro //
        //////////////////////////////////////////////////////////////
        if(jsPub[0].usr_interno=="E"){
          document.getElementById("divCbErro").style.display="none";
          document.getElementById("cbErro").value="N";
          jsBi.titulo[4].tamGrd="0em";
          jsBi.titulo[6].tamGrd="0em";
          jsBi.titulo[9].tamGrd="0em";
					jsBi.titulo[12].tamGrd="0em";
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
				clsJs.add("rotina"  	, "select"                                  	);
				clsJs.add("login"   	, jsPub[0].usr_login                        	);
				clsJs.add("codusu"  	, jsPub[0].usr_codigo                       	);
				clsJs.add("dtini"   	, document.getElementById("cbIni").value    	);
				clsJs.add("frota"   	, document.getElementById("cbFrota").value  	);
				clsJs.add("tempo"   	, document.getElementById("cbTempo").value  	);
        clsJs.add("erro"    	, document.getElementById("cbErro").value   	);
        clsJs.add("grupoOperacional"   	, document.getElementById("cbGpo").value  	);
				//clsJs.add("infracao"	, document.getElementById("cbInfracao").value	);
				fd = new FormData();
				fd.append("grdPeriodoIndevido" , clsJs.fim());
  			msg     = requestPedido("Trac_grdPeriodoIndevido.php",fd); 
				retPhp  = JSON.parse(msg);
				if( retPhp[0].retorno == "OK" ){
					//////////////////////////////////////////////////////////////////////////////////
					// O novo array não tem o campo idUnico mas a montarHtmlCE2017 ja foi executada //
					// Campo obrigatório se existir rotina de manutenção na table devido Json       //
					// Esta rotina não tem manutenção via classe clsTable2017                       //
					// jsCrv.registros=objCrv.addIdUnico(retPhp[0]["dados"]);                       //
					//////////////////////////////////////////////////////////////////////////////////
					jsBi.registros=objBi.addIdUnico(retPhp[0]["dados"]);
					jsBi.relTitulo="BI Perido indevido em "+document.getElementById("cbIni").options[document.getElementById("cbIni").selectedIndex].text;
					//objBi.ordenaJSon(jsBi.indiceTable,false);  
					objBi.montarBody2017();
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
          
          var pI=chkds[0].IDINI;
          var pF=chkds[0].IDFIM;
          if( pF=="**erro**" ){
            pF=(pI+10000);
          };
          clsJs.add("rotina"  , "detalhe"             );
          clsJs.add("login"   , jsPub[0].usr_login    );
          clsJs.add("codusu"  , jsPub[0].usr_codigo   );
          clsJs.add("frota"   , chkds[0].LP           );          
          clsJs.add("placa"   , chkds[0].PLACA        ); 
          clsJs.add("dtini"   , chkds[0].DTINI        );					
          clsJs.add("idIni"   , pI        );
          clsJs.add("idFim"   , pF        );          
          fd          = new FormData();
          fd.append("grdPeriodoIndevido" , clsJs.fim());          
          var req = requestPedido("Trac_grdPeriodoIndevido.php",fd);
          var ret = JSON.parse(req);
          if( ret[0].dados.length==0 ){
            gerarMensagemErro("ALV","NENHUM REGISTRO LOCALIZADO","AVISO");  
          } else {
            if( ret[0].retorno == "OK" ){
              jsDet={
                "titulo":[
                  {"id":0   ,"labelCol"       : "OPC"     
                            ,"padrao"         : 1}            
                  ,{"id":1  ,"field"          : "MVM_POSICAO" 
                            ,"labelCol"       : "ID"
                            ,"fieldType"      : "str"
                            ,"obj"            : "edtPosicao"
                            ,"tamGrd"         : "10em"
                            ,"tamImp"         : "15"
                            ,"pk"             : "S"
                            ,"ajudaCampo"     : ["Id sistemsat."]
                            ,"padrao":0}
                  ,{"id":2  ,"field"          : "MVM_PLACA"   
                            ,"fieldType"      : "str"
                            ,"labelCol"       : "PLACA"
                            ,"obj"            : "edtPlaca"
                            ,"tamGrd"         : "7em"
                            ,"tamImp"         : "15"
                            ,"ajudaCampo"     : ["Placa do veiculo"]
                            ,"padrao":0}
                  ,{"id":3  ,"field"          : "MVM_TURNO"   
                            ,"fieldType"      : "str"
                            ,"labelCol"       : "T"
                            ,"obj"            : "edtFrota"
                            ,"tamGrd"         : "2em"
                            ,"tamImp"         : "5"
                            ,"ajudaCampo"     : ["Veiculo pesado/leve"]
                            ,"padrao":0}
                  ,{"id":4  ,"field"          : "EVE_NOME"   
                            ,"fieldType"      : "str"
                            ,"labelCol"       : "EVENTO"
                            ,"obj"            : "edtFrota"
                            ,"tamGrd"         : "20em"
                            ,"tamImp"         : "40"
                            ,"ajudaCampo"     : ["Veiculo pesado/leve"]
                            ,"padrao":0}
                  ,{"id":5  ,"field"          : "MVM_VELOCIDADE"   
                            ,"fieldType"      : "int"
                            ,"align"          : "center"
                            ,"labelCol"       : "VEL"
                            ,"obj"            : "edtVelocidade"
                            ,"tamGrd"         : "5em"
                            ,"tamImp"         : "10"
                            ,"ajudaCampo"     : ["Velocidade"]
                            ,"padrao":0}
                  ,{"id":6  ,"field"          : "MVM_DATAGPS"   
                            ,"fieldType"      : "str"
                            ,"labelCol"       : "DATA"
                            ,"obj"            : "edtDataGps"
                            ,"tamGrd"         : "15em"
                            ,"tamImp"         : "30"
                            ,"ajudaCampo"     : ["Data."]
                            ,"padrao":0}
                  ,{"id":7  ,"field"          : "UNI_APELIDO"   
                            ,"fieldType"      : "str"
                            ,"labelCol"       : "UNIDADE"
                            ,"obj"            : "edtUnidade"
                            ,"tamGrd"         : "10em"
                            ,"tamImp"         : "25"
                            ,"ajudaCampo"     : ["Unidade"]
                            ,"padrao":0}
                  ,{"id":8  ,"field"          : "UNI_CODPOL"   
                            ,"fieldType"      : "str"
                            ,"labelCol"       : "POLO"
                            ,"obj"            : "edtCodPol"
                            ,"tamGrd"         : "4em"
                            ,"tamImp"         : "15"
                            ,"ajudaCampo"     : ["Polo"]
                            ,"padrao":0}
                  ,{"id":9 ,"labelCol"       : "LATITUDE"
                            ,"fieldType"      : "flo8" 
                            ,"tamGrd"         : "12em"
                            ,"tamImp"         : "0"
                            ,"excel"          : "S"                      
                            ,"padrao":0}
                  ,{"id":10 ,"labelCol"       : "LONGITUDE"
                            ,"fieldType"      : "flo8" 
                            ,"tamGrd"         : "12em"
                            ,"tamImp"         : "0"
                            ,"excel"          : "S"                      
                            ,"padrao":0}
                  ,{"id":11 ,"fieldType"      : "str"
                            ,"labelCol"       : "LOCALIZACAO"
                            ,"obj"            : "edtCodPol"
                            ,"tamGrd"         : "30em"
                            ,"tamImp"         : "45"
                            ,"ajudaCampo"     : ["Polo"]
                            ,"padrao":0}
                  ,{"id":12 ,"field"          : "MVM_IGNICAO"   
                            ,"fieldType"      : "int"
                            ,"align"          : "center"
                            ,"labelCol"       : "IGN"
                            ,"obj"            : "edtVelocidade"
                            ,"tamGrd"         : "3em"
                            ,"tamImp"         : "10"
                            ,"ajudaCampo"     : ["Ignição"]
                            ,"padrao":0}
														
                ]  
                , 
                "botoesH":[
                   {"texto":"Excel"     ,"name":"detExcel"  ,"onClick":"5"  ,"enabled":true,"imagem":"fa fa-file-excel-o"     ,"ajuda":"Exportar para excel" }
									,{"texto":"Mapa"      ,"name":"detMapa"   ,"onClick":"7"  ,"enabled":true,"imagem":"fa fa-map-marker"     ,"ajuda":"Exportar para excel" }        
                  ,{"texto":"Retornar"  ,"name":"detVoltar" ,"onClick":"7"  ,"enabled":true ,"imagem":"fa fa-plus","ajuda":"Retorna a tela anterior" }
                ] 
                ,"registros"      : ret[0].dados              // Recebe um Json vindo da classe clsBancoDados
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
                ,"relTitulo"      : "DETALHE EVENTO"          // Titulo do relatório
                ,"relOrientacao"  : "P"                       // Paisagem ou retrato
                ,"relFonte"       : "8"                       // Fonte do relatório
                ,"indiceTable"    : "DATA"                    // Indice inicial da table
                ,"tamBotao"       : "12"                      // Tamanho botoes defalt 12 [12/25/50/75/100]
                ,"tamMenuTable"   : ["10em","20em"]                                
                ,"codTblUsu"      : "USUARIO[01]"                          
                ,"codDir"         : intCodDir
              }; 
              if( objDet === undefined ){  
                objDet=new clsTable2017("objDet");
              };
              objDet.montarHtmlCE2017(jsDet); 
              window.location.href="#ancoraMovimento";
              var el = document.getElementsByClassName("acordeon");
              for( var lin=0;lin<el.length;lin++ ){
                if( (el[lin].id=="btnDetalhe") && (el[lin].className != "acordeon acrdnAtivo") ){
                  document.getElementById("btnDetalhe").click();  
                  window.location.href="#ancoraDetalhe";
                };  
              }; 
            } else {
              throw ret[0].erro;
            };
          };
        }catch(e){
          gerarMensagemErro("catch",e.message,"Erro");
        };
      };
      //////////////////////////////////////////////
      // Filtrando motoristas com tempo acumulado //
      //////////////////////////////////////////////
      function biMotoristaClick(){
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
          var parte;  
          var tempo;

          for(var fc=0; fc<tam;fc++){
            strSeek=json[fc].MOTORISTA;
            intSeek=addTbl.indexOf(strSeek);
            if( json[fc].TEMPO=="**erro**" ){
              tempo=0;
            } else {
              parte = json[fc].TEMPO.split(":");
              tempo = (parseInt(parte[2]) + (parseInt(parte[1])*60) + (parseInt(parte[0])*3600) );
            };  
            
            if( intSeek==-1 ){
              addTbl.push(strSeek);
              tbl.push([
                strSeek
                //,parseInt(tempo)
								,segundosEm( parseInt(tempo),"hms" )
              ]);
            } else {
              tbl[intSeek][1]+=parseInt(tempo); 
            };
						//let tam=tbl.length;
						//for( let lin=0;lin<tam;lin++ )
						//	tbl[lin][1]=segundosEm( parseInt(tbl[lin][1]),"hms" );
						
          };  
          //
          //
          if( tbl.length==0 ){
            gerarMensagemErro("ALV","NENHUM REGISTRO LOCALIZADO","AVISO");  
          } else {
            //
            jsMtr={
              "titulo":[
                {"id":0   ,"labelCol"       : "MOTORISTA"
                          ,"fieldType"      : "str"
                          ,"tamGrd"         : "35em"
                          ,"tamImp"         : "70"
                          ,"excel"          : "S"
                          ,"ordenaColuna"   : "S"
                          ,"padrao":0}
                ,{"id":1  ,"labelCol"       : "TEMPO"
                          ,"fieldType"      : "str"
                          ,"align"          : "center"
                          ,"tamGrd"         : "8em"
                          ,"tamImp"         : "10"
                          ,"excel"          : "S"
                          ,"ordenaColuna"   : "S"
                          ,"padrao":0}
              ]  
              , 
              "botoesH":[
                 {"texto":"Excel"     ,"name":"eveExcel"  ,"onClick":"5"  ,"enabled":true,"imagem":"fa fa-file-excel-o"     ,"ajuda":"Exportar para excel" }
                ,{"texto":"Retornar"  ,"name":"eveVoltar" ,"onClick":"7"  ,"enabled":true ,"imagem":"fa fa-plus"            ,"ajuda":"Retorna a tela anterior" }
              ] 
              ,"registros"      : tbl                       // Recebe um Json vindo da classe clsBancoDados
              ,"refazClasse"    : "S"
              ,"opcRegSeek"     : true                      // Opção para numero registros/botão/procurar                     
              ,"checarTags"     : "S"                       // Somente em tempo de desenvolvimento(olha as pricipais tags)                  
              ,"div"            : "frmMtr"                  // Onde vai ser gerado a table
              ,"divFieldSet"    : "tabela"                  // Para fechar a div onde estão os fieldset ao cadastrar
              ,"form"           : "frm"                     // Onde vai ser gerado o fieldSet       
              ,"divModal"       : "divMtrReg"               // Onde vai se appendado abaixo deste a table 
              ,"tbl"            : "tblMtr"                  // Nome da table
              ,"prefixo"        : "es"                      // Prefixo para elementos do HTML em jsTable2017.js
              ,"tabelaBD"       : "BI_EXCESSOVELOC"         // Nome da tabela no banco de dados  
              ,"tableLeft"      : "sim"                     // Se tiver menu esquerdo              
              ,"iFrame"         : "iframeCorpo"             // Se a table vai ficar dentro de uma tag iFrame
              ,"width"          : "56em"                    // Tamanho da table
              ,"height"         : "40em"                    // Altura da table
              ,"relTitulo"      : "DETALHE MOTORISTA"       // Titulo do relatório
              ,"relOrientacao"  : "P"                       // Paisagem ou retrato
              ,"relFonte"       : "8"                       // Fonte do relatório
              ,"indiceTable"    : "MOTORISTA"               // Indice inicial da table
              ,"tamBotao"       : "30"                      // Tamanho botoes defalt 12 [12/25/50/75/100]
              ,"tamMenuTable"   : ["10em","20em"]                                
              ,"codTblUsu"      : "USUARIO[01]"                          
              ,"codDir"         : intCodDir
            }; 
            if( objMtr === undefined ){  
              objMtr=new clsTable2017("objMtr");
            };
            objMtr.montarHtmlCE2017(jsMtr); 
             var el = document.getElementsByClassName("acordeon");
            for( var lin=0;lin<el.length;lin++ ){
              if( (el[lin].id=="btnMotorista") && (el[lin].className != "acordeon acrdnAtivo") ){
                document.getElementById("btnMotorista").click();  
                window.location.href="#ancoraMotorista";
              };  
            }; 
          };
        }catch( e ){
          gerarMensagemErro("Composição do evento",e,"Erro");
        };
      };
			//
      function detMapaClick(){
        try{        
          clsChecados = objDet.gerarJson("1");
          chkds       = clsChecados.gerar();
          clsJs       = jsString("lote");  
        
          msg='['
              + '{"lat":"' +jsNmrs(chkds[0].LATITUDE).dec(8).dolar().ret()
              +'","lon":"' +jsNmrs(chkds[0].LONGITUDE).dec(8).dolar().ret()
              +'","loc":"' +chkds[0].LOCALIZACAO+'"}'
              +']';
          localStorage.setItem("addMapa",msg);
          window.open("mapa/Trac_Mapa.php");
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
      <!-- <div class="campotexto campo10">      
        <select class="campo_input_combo" id="cbIni">
        </select>
        <label class="campo_label campo_required" for="cbFrota">MÊS</label>
      </div> -->

      <?php include 'classPhp/comum/selectMes.class.php';?>
			
      <div class="campotexto campo15">
        <select class="campo_input_combo" id="cbFrota">
          <option value="LP" selected="selected">Leve/Pesado</option>
          <option value="P">Pesado</option>
          <option value="L">Leve</option>
        </select>
        <label class="campo_label campo_required" for="cbFrota">FROTA</label>
      </div>

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

      <?php include 'classPhp/comum/selectGrupoOperacional.class.php';?>

      <div class="campo10" style="float:left;">            
        <input id="btnFilttrar" onClick="btnFiltrarClick();" type="button" value="Filtrar" class="botaoSobreTable"/>
      </div>
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