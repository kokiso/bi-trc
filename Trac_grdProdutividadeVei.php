<?php
  session_start();
  if( isset($_POST["grdProdutividadevei"]) ){
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
      $retCls   = $vldr->validarJs($_POST["grdProdutividadevei"]);
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
                        break;
            case "L"  : $frota=" AND (VCL.VCL_FROTA='L')";
                        break;
            case "P"  : $frota=" AND (VCL.VCL_FROTA='P')";
                        break;
          };

          if( $lote[0]->grupoOperacional != 'TODOS' ) {
            $gpo = " AND (VCL.VCL_CODGPO=".$lote[0]->grupoOperacional.")";
          }

          $sql="";          
          $sql.="SELECT A.BIPRDM_ANOMES AS ANOMES";
          $sql.="       ,UNI.UNI_CODIGO AS CODUNI";
          $sql.="       ,A.BIPRDM_CODVCL AS PLACA";
          $sql.="       ,VCL.VCL_FROTA AS FROTA";
          $sql.="       ,A.BIPRDM_TEMPORODANDO AS RODANDO";
          $sql.="       ,A.BIPRDM_TEMPOPARADO AS PARADO";
          $sql.="       ,A.BIPRDM_ODOMETROINI AS KMINI";
          $sql.="       ,A.BIPRDM_ODOMETROFIM AS KMFIM";
          $sql.="       ,UNI.UNI_APELIDO AS UNIDADE";
          $sql.="       ,UNI.UNI_CODPOL AS POLO";
          $sql.="       ,A.BIPRDM_CODPRDINI AS PRDINI";          
          $sql.="       ,A.BIPRDM_CODPRDFIM AS PRDFIM";
          $sql.="  FROM BI_PRODUTIVIDADEVEIMES A";          
          $sql.="  LEFT OUTER JOIN VEICULO VCL ON A.BIPRDM_CODVCL=VCL.VCL_CODIGO";
          $sql.="  LEFT OUTER JOIN UNIDADE UNI ON VCL.VCL_CODUNI=UNI.UNI_CODIGO";
          $sql.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON UNI.UNI_CODIGO=UU.UU_CODUNI AND UU.UU_CODUSR=".$lote[0]->codusu;            
          $sql.=" WHERE (A.BIPRDM_ANOMES BETWEEN ".$lote[0]->dtini." AND ".$lote[0]->dtfim.")";
          $sql.=$frota;
          $sql.=$gpo;
          $sql.="   AND (COALESCE(UU.UU_ATIVO,'')='S')";   
//file_put_contents("aaa.xml",$sql);					
          $classe->msgSelect(false);
          $retCls=$classe->selectAssoc($sql);
          if( $retCls['retorno'] != "OK" ){
            $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';  
          } else { 
            $retorno='[{"retorno":"OK","dados":'.json_encode($retCls['dados']).',"erro":""}]'; 
          };  
        };
        ////////////////////////////////////
        // Detalhe de cada linha da grade //
        ////////////////////////////////////
        if( $rotina=="detalhe" ){
          $sql="";                    
          $sql.="SELECT A.BIPRD_CODIGO AS INDICE";
          $sql.="       ,COALESCE(MTR.MTR_NOME,'...') AS MOTORISTA";                    
          $sql.="       ,BIPRD_CODVCL AS PLACA";
          $sql.="       ,BIPRD_TURNO AS TURNO";
          $sql.="       ,CAST(CAST(BIPRD_IGNICAOINI AS VARCHAR(1))+'-'+CAST(BIPRD_IGNICAOFIM AS VARCHAR(1)) AS VARCHAR(3)) AS IGNICAO";
          $sql.="       ,BIPRD_TEMPORODANDO AS RODANDO";
          $sql.="       ,BIPRD_TEMPOPARADO AS PARADO";
          $sql.="       ,BIPRD_ODOMETROINI AS KMINI";
          $sql.="       ,BIPRD_ODOMETROFIM AS KMFIM";
          $sql.="       ,BIPRD_ODOMETRO AS KM";
          $sql.="       ,BIPRD_IDINI AS IDINI";
					$sql.="       ,CONVERT(VARCHAR(23),BIPRD_DATAGPSINI,127) AS DATAINI";
					$sql.="       ,MVMI.MVM_LOCALIZACAO AS LOCINI";
          $sql.="       ,BIPRD_IDFIM AS IDFIM";
					$sql.="       ,CONVERT(VARCHAR(23),BIPRD_DATAGPSFIM,127) AS DATAFIM";
					$sql.="       ,MVMF.MVM_LOCALIZACAO AS LOCFIM"; 
          $sql.="       ,UNI.UNI_APELIDO AS UNIDADE";
          $sql.="       ,UNI.UNI_CODPOL AS POLO";
          $sql.="       ,BIPRD_ERROGPS AS ERRO";          
          $sql.="  FROM BI_PRODUTIVIDADE A";
          $sql.="  LEFT OUTER JOIN VEICULO VCL ON A.BIPRD_CODVCL=VCL.VCL_CODIGO";
          $sql.="  LEFT OUTER JOIN MOTORISTA MTR ON A.BIPRD_CODMTR=MTR.MTR_CODIGO";
          $sql.="  LEFT OUTER JOIN UNIDADE UNI ON A.BIPRD_CODUNI=UNI.UNI_CODIGO";
					$sql.="  LEFT OUTER JOIN MOVIMENTO MVMI ON BIPRD_IDINI=MVMI.MVM_POSICAO";
					$sql.="  LEFT OUTER JOIN MOVIMENTO MVMF ON BIPRD_IDFIM=MVMF.MVM_POSICAO";
          $sql.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON A.BIPRD_CODUNI=UU.UU_CODUNI AND UU.UU_CODUSR=".$lote[0]->codusu;            
          $sql.=" WHERE (A.BIPRD_CODIGO BETWEEN ".$lote[0]->prdini." AND ".$lote[0]->prdfim.")";
          $sql.="   AND (A.BIPRD_ANOMES=".$lote[0]->dtini.")";
          $sql.="   AND (A.BIPRD_CODVCL='".$lote[0]->codvcl."')";  
          $sql.="   AND (COALESCE(UU.UU_ATIVO,'')='S')";   
          //
          file_put_contents("aaa.xml",$sql);
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
    <title>Produtividade/veiculo</title>
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
				// comboCompetencia("YYYYMM_MMM/YY",document.getElementById("cbIni"));
        jsBi={
          "titulo":[
             {"id":0  ,"labelCol":"OPC"     
                      ,"padrao":1}            
            ,{"id":1  ,"labelCol"       : "DATA"
                      ,"fieldType"      : "int"
                      ,"align"          : "center"                                      
                      ,"tamGrd"         : "5em"
                      ,"tamImp"         : "15"
                      ,"excel"          : "S"
                      ,"ordenaColuna"   : "S"
											,"somarImp"				: "S"	
                      ,"padrao":0}
            ,{"id":2  ,"labelCol"       : "CODUNI"
                      ,"fieldType"      : "int"
                      ,"align"          : "center"                                      
                      ,"tamGrd"         : "0em"
                      ,"tamImp"         : "0"
                      ,"padrao":0}
            ,{"id":3  ,"labelCol"       : "PLACA"
                      ,"fieldType"      : "str"
                      ,"tamGrd"         : "6em"
                      ,"tamImp"         : "25"
                      ,"excel"          : "S"
                      ,"ordenaColuna"   : "S"
                      ,"padrao":0}
            ,{"id":4  ,"labelCol"       : "F"
                      ,"fieldType"      : "str"
                      ,"tamGrd"         : "0em"
                      ,"tamImp"         : "5"
                      ,"excel"          : "S"
                      ,"ordenaColuna"   : "S"
                      ,"padrao":0}
            ,{"id":5  ,"labelCol"       : "RODANDO"
                      ,"fieldType"      : "int"
                      ,"align"          : "center"                                      
                      ,"tamGrd"         : "10em"
                      ,"tamImp"         : "15"
                      ,"excel"          : "S"                      
                      ,"padrao":0}
            ,{"id":6 ,"labelCol"       : "PARADO"
                      ,"fieldType"      : "int"
                      ,"align"          : "center"                                      
                      ,"tamGrd"         : "10em"
                      ,"tamImp"         : "15"
                      ,"excel"          : "S"                      
                      ,"padrao":0}
            ,{"id":7  ,"labelCol"       : "TOTAL"
                      ,"fieldType"      : "int"
                      ,"align"          : "center"                                      
                      ,"tamGrd"         : "10em"
                      ,"tamImp"         : "15"
                      ,"excel"          : "S"                      
                      ,"padrao":0}
            ,{"id":8  ,"labelCol"       : "KMINI"
                      ,"fieldType"      : "int"
                      ,"align"          : "center"                      
                      ,"tamGrd"         : "7em"
                      ,"tamImp"         : "15"
                      ,"excel"          : "S"                      
                      ,"padrao":0}
            ,{"id":9  ,"labelCol"       : "KMFIM"
                      ,"fieldType"      : "int"
                      ,"align"          : "center"                      
                      ,"tamGrd"         : "7em"
                      ,"tamImp"         : "15"
                      ,"excel"          : "S"                      
                      ,"padrao":0}
            ,{"id":10 ,"labelCol"       : "KM"
                      ,"fieldType"      : "flo4"
                      ,"align"          : "center"                      
                      ,"tamGrd"         : "8em"
                      ,"tamImp"         : "15"
                      ,"excel"          : "S"                      
                      ,"padrao":0}
            ,{"id":11 ,"labelCol"       : "VEL MED"
                      ,"fieldType"      : "flo2"
                      ,"align"          : "center"                      
                      ,"tamGrd"         : "6em"
                      ,"tamImp"         : "15"
                      ,"excel"          : "S"                      
                      ,"padrao":0}
            ,{"id":12 ,"labelCol"       : "UNIDADE"
                      ,"fieldType"      : "str"
                      ,"tamGrd"         : "10em"
                      ,"tamImp"         : "25"
                      ,"excel"          : "S"
                      ,"ordenaColuna"   : "S"
                      ,"padrao":0}
            ,{"id":13 ,"labelCol"       : "POLO"
                      ,"fieldType"      : "str"
                      ,"align"          : "center"
                      ,"tamGrd"         : "4em"
                      ,"tamImp"         : "12"
                      ,"excel"          : "S"
                      ,"ordenaColuna"   : "S"
                      ,"padrao":0}
            ,{"id":14 ,"labelCol"       : "PRDINI"
                      ,"fieldType"      : "int"
                      ,"align"          : "center"                                      
                      ,"tamGrd"         : "0em"
                      ,"tamImp"         : "0"
                      ,"padrao":0}
            ,{"id":15 ,"labelCol"       : "PRDFIM"
                      ,"fieldType"      : "int"
                      ,"align"          : "center"                                      
                      ,"tamGrd"         : "0em"
                      ,"tamImp"         : "0"
                      ,"padrao":0}
            ,{"id":16 ,"labelCol"       : "INT_RODANDO"
                      ,"fieldType"      : "int"
                      ,"align"          : "center"                                      
                      ,"tamGrd"         : "0em"
                      ,"tamImp"         : "0"
                      ,"padrao":0}
            ,{"id":17 ,"labelCol"       : "INT_PARADO"
                      ,"fieldType"      : "int"
                      ,"align"          : "center"                                      
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
             {"texto":"Detalhe"       	,"name":"biDetalhe"       ,"onClick":"7"   ,"enabled":true,"imagem":"fa fa-sort-desc"     ,"ajuda":"Detalhe do registro" }
            ,{"texto":"Imprimir"      	,"name":"biImprimir"      ,"onClick":"7"   ,"enabled":true,"imagem":"fa fa-print"         ,"ajuda":"Exportar para excel" }             
            ,{"texto":"Excel"         	,"name":"biExcel"         ,"onClick":"5"   ,"enabled":true,"imagem":"fa fa-file-excel-o"  ,"ajuda":"Exportar para excel" }
						,{"texto":"Desmarcar todos" ,"name":"biDesmarcar"   	,"onClick":"7"   ,"enabled":true,"imagem":"fa fa-check"  ,"ajuda":"Exportar para excel" }
            ,{"texto":"Fechar"        	,"name":"biFechar"        ,"onClick":"8"   ,"enabled":true ,"imagem":"fa fa-close"        ,"ajuda":"Fechar formulario" }
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
          ,"width"          : "105em"                   // Tamanho da table
          ,"height"         : "54em"                    // Altura da table
          ,"tableLeft"      : "sim"                     // Se tiver menu esquerdo
          ,"relTitulo"      : "BI"                      // Titulo do relatório
          ,"relOrientacao"  : "P"                       // Paisagem ou retrato
          ,"relFonte"       : "8"                       // Fonte do relatório
          ,"indiceTable"    : "ANO"               // Indice inicial da table
          ,"tamBotao"       : "12"                      // Tamanho botoes defalt 12 [12/25/50/75/100]
          ,"codTblUsu"      : "PRODUTIVIDADE[00]"                          
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
      ////////////////////////////////////////////////////////////
      // Acumulando totais da grade para quando gerar relatorio //
      ////////////////////////////////////////////////////////////
      var pubRodando;
      var pubParado;
      var pubTotal;
      var pubKm;
      /////////////////////////////////
      // Desmarcando todos registros //
      /////////////////////////////////
			function biDesmarcarClick(){
				tblBi.retiraChecked();
			};
      //
      //
      ////////////////////////////
      // Filtrando os registros //
      ////////////////////////////
      function btnFiltrarClick() { 
        //////////////////////////////////////////////
        //Buscando a dada de inicio/fim para select //
        //////////////////////////////////////////////
        //var splt=document.getElementById("cbIni").value.split('|');
        //
        clsJs   = jsString("lote");  
        clsJs.add("rotina"  	, "select"                                  	);
        clsJs.add("login"   	, jsPub[0].usr_login                        	);
        clsJs.add("codusu"  	, jsPub[0].usr_codigo                       	);
        //clsJs.add("dtini"   	, splt[1]                                   );
        //clsJs.add("dtfim"   	, splt[0]                                   );          
        clsJs.add("dtini"   	, document.getElementById("cbIni").value   );
        clsJs.add("dtfim"   	, document.getElementById("cbIni").value   );
        clsJs.add("frota"   	, document.getElementById("cbFrota").value  	);
        clsJs.add("grupoOperacional"   	, document.getElementById("cbGpo").value  	);
        fd = new FormData();
        fd.append("grdProdutividadevei" , clsJs.fim());
        msg     = requestPedido("Trac_grdProdutividadeVei.php",fd); 
        retPhp  = JSON.parse(msg);
        if( retPhp[0].retorno == "OK" ){
          //////////////////////////////////////////////////////////////////////////////////
          // O novo array não tem o campo idUnico mas a montarHtmlCE2017 ja foi executada //
          // Campo obrigatório se existir rotina de manutenção na table devido Json       //
          // Esta rotina não tem manutenção via classe clsTable2017                       //
          // jsCrv.registros=objCrv.addIdUnico(retPhp[0]["dados"]);                       //
          //////////////////////////////////////////////////////////////////////////////////
          pubRodando  = 0;
          pubParado   = 0;
          pubTotal    = 0;
          pubKm       = 0;
          /////////////////////////////////////////////////////////////////////////////////////
          // Como vou precisar dos dados originais do php gero um array naum assoc para a table
          /////////////////////////////////////////////////////////////////////////////////////
          retPhp  = retPhp[0]["dados"];
          var retJs   = [];
          var pIni,kmIni,pFim,kmFim,velocMedia;
          retPhp.forEach(function(lin){
            pIni=(lin.KMINI).indexOf(".");
            pFim=(lin.KMFIM).indexOf(".");
            velocMedia=((parseFloat(lin.KMFIM)-parseFloat(lin.KMINI))/(lin.RODANDO/3600));
            //
            retJs.push([
              lin.ANOMES                                                  // DATA
              ,lin.CODUNI                                                 //
              ,lin.PLACA                                                  // PLACA
              ,lin.FROTA                                                  //
              ,segundosEm(lin.RODANDO,"ddhms" )                           // RODANDO
              ,segundosEm(lin.PARADO,"ddhms" )                            // PARADO
              ,segundosEm( (lin.RODANDO+lin.PARADO),"ddhms" )             // TOTAL
              ,lin.KMINI.substr(0,pIni)                                   // KMINI
              ,lin.KMFIM.substr(0,pFim)                                   // KMFIM
              ,(parseFloat(lin.KMFIM)-parseFloat(lin.KMINI)).toFixed(4)   // KM
              ,jsNmrs( velocMedia ).dec(2).real().ret()                   // VELOCMEDIA               
              ,lin.UNIDADE                                                // UNIDADE
              ,lin.POLO                                                   // POLO 
              ,lin.PRDINI                                                 // Acelerador na procura pelo detalhe
              ,lin.PRDFIM                                                 // Acelerador na procura pelo detalhe
              ,lin.RODANDO
              ,lin.PARADO
            ]);
            ////////////////////////
            // Totais para relatorio
            ////////////////////////
            pubRodando  += lin.RODANDO;
            pubParado   += lin.PARADO;
            pubTotal    += (lin.RODANDO+lin.PARADO);
            pubKm       += (parseFloat(lin.KMFIM)-parseFloat(lin.KMINI));
          });
          jsBi.registros=objBi.addIdUnico(retJs);
          jsBi.relTitulo="BI Produtividade por veiculo "+document.getElementById("cbIni").value;
          jsBi.indiceTable="PLACA";
          objBi.ordenaJSon(jsBi.indiceTable,false);  
          objBi.montarBody2017();
        };  
      }; 
      ////////////////////////////////
      //          DETALHE           //
      ////////////////////////////////
      function biDetalheClick(){
        //////////////////////////////////////////////
        //Buscando a dada de inicio/fim para select //
        //////////////////////////////////////////////    
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
            clsJs.add("dtini"   , chkds[0].DATA                               );
            clsJs.add("codvcl"  , chkds[0].PLACA                              );
            clsJs.add("prdini"  , chkds[0].PRDINI                             );
            clsJs.add("prdfim"  , chkds[0].PRDFIM                             );
            fd          = new FormData();
            fd.append("grdProdutividadevei" , clsJs.fim()); 
            var req = requestPedido("Trac_grdProdutividadeVei.php",fd);
            retPhp = JSON.parse(req);
            if( retPhp[0].dados.length==0 ){
              gerarMensagemErro("ALV","NENHUM REGISTRO LOCALIZADO","AVISO");  
            } else {
              if( retPhp[0].retorno == "OK" ){
                /////////////////////////////
                //Convertendo segundos em hms
                /////////////////////////////
                retPhp=retPhp[0]["dados"];
                retPhp.forEach(function(lin){
                  lin[5]  = segundosEm( lin[5],"hms" );
                  lin[6]  = segundosEm( lin[6],"hms" );
                });
                
                jsDet={
                  "titulo":[
                    {"id":0   ,"labelCol"       : "OPC"     
                              ,"padrao"         : 1}            
                    ,{"id":1  ,"labelCol"       : "ID"
                              ,"fieldType"      : "str"
                              ,"tamGrd"         : "8em"
                              ,"tamImp"         : "15"
                              ,"padrao":0}
                    ,{"id":2  ,"labelCol"       : "MOTORISTA"
                              ,"fieldType"      : "str"
                              ,"tamGrd"         : "25em"
                              ,"tamImp"         : "70"
                              ,"excel"          : "S"
                              ,"ordenaColuna"   : "S"
                              ,"padrao":0}
                    ,{"id":3  ,"labelCol"       : "PLACA"
                              ,"fieldType"      : "str"
                              ,"tamGrd"         : "6em"
                              ,"tamImp"         : "25"
                              ,"excel"          : "S"
                              ,"ordenaColuna"   : "S"
                              ,"padrao":0}
                    ,{"id":4  ,"labelCol"       : "T"
                              ,"fieldType"      : "str"
                              ,"tamGrd"         : "0em"
                              ,"tamImp"         : "5"
                              ,"excel"          : "S"
                              ,"ordenaColuna"   : "S"
                              ,"padrao":0}
                    ,{"id":5  ,"labelCol"       : "IGN"
                              ,"fieldType"      : "str"
                              ,"tamGrd"         : "3em"
                              ,"tamImp"         : "0"
                              ,"excel"          : "S"                              
                              ,"padrao":0}
                    ,{"id":6  ,"labelCol"       : "RODANDO"
                              ,"fieldType"      : "int"
                              ,"align"          : "center"                                      
                              ,"tamGrd"         : "6em"
                              ,"tamImp"         : "15"
                              ,"excel"          : "S"                              
                              ,"padrao":0}
                    ,{"id":7  ,"labelCol"       : "PARADO"
                              ,"fieldType"      : "int"
                              ,"align"          : "center"                                      
                              ,"tamGrd"         : "6em"
                              ,"tamImp"         : "15"
                              ,"excel"          : "S"                              
                              ,"padrao":0}
                    ,{"id":8  ,"labelCol"       : "KMINI"
                              ,"fieldType"      : "flo4"
                              ,"align"          : "center"                      
                              ,"tamGrd"         : "10em"
                              ,"tamImp"         : "15"
                              ,"excel"          : "S"                              
                              ,"padrao":0}
                    ,{"id":9  ,"labelCol"       : "KMFIM"
                              ,"fieldType"      : "flo4"
                              ,"align"          : "center"                      
                              ,"tamGrd"         : "10em"
                              ,"tamImp"         : "15"
                              ,"excel"          : "S"                              
                              ,"padrao":0}
                    ,{"id":10 ,"labelCol"       : "KM"
                              ,"fieldType"      : "flo4"
                              ,"align"          : "center"                      
                              ,"tamGrd"         : "8em"
                              ,"tamImp"         : "15"
                              ,"excel"          : "S"                              
                              ,"padrao":0}
                    ,{"id":11 ,"labelCol"       : "IDINI"
                              ,"fieldType"      : "str"
                              ,"tamGrd"         : "9em"
                              ,"tamImp"         : "15"
                              ,"excel"          : "S"                              
                              ,"padrao":0}
					,{"id":12 ,"labelCol"       : "DATAGPSINI"
                              ,"fieldType"      : "str"
                              ,"tamGrd"         : "14em"
                              ,"tamImp"         : "15"
                              ,"excel"          : "S"                              
                              ,"padrao":0}		  
                    ,{"id":13 ,"labelCol"       : "LOCINI"
                              ,"fieldType"      : "str"
                              ,"tamGrd"         : "20em"
                              ,"tamImp"         : "15"
                              ,"excel"          : "S"                              
                              ,"padrao":0}
					,{"id":14 ,"labelCol"       : "IDFIM"
                              ,"fieldType"      : "str"
                              ,"tamGrd"         : "9em"
                              ,"tamImp"         : "15"
                              ,"excel"          : "S"                              
                              ,"padrao":0}		  
                    ,{"id":15 ,"labelCol"       : "DATAGPSFIM"
                              ,"fieldType"      : "str"
                              ,"tamGrd"         : "14em"
                              ,"tamImp"         : "30"
                              ,"excel"          : "S"                              
                              ,"padrao":0}
                    ,{"id":16 ,"labelCol"       : "LOCFIM"
                              ,"fieldType"      : "str"
                              ,"tamGrd"         : "20em"
                              ,"tamImp"         : "30"
                              ,"excel"          : "S"                              
                              ,"padrao":0}
                    ,{"id":17 ,"labelCol"       : "UNIDADE"
                              ,"fieldType"      : "str"
                              ,"tamGrd"         : "10em"
                              ,"tamImp"         : "25"
                              ,"excel"          : "S"
                              ,"ordenaColuna"   : "S"
                              ,"padrao":0}
                    ,{"id":18 ,"labelCol"       : "POLO"
                              ,"fieldType"      : "str"
                              ,"align"          : "center"
                              ,"tamGrd"         : "4em"
                              ,"tamImp"         : "12"
                              ,"excel"          : "S"
                              ,"ordenaColuna"   : "S"
                              ,"padrao":0}
                    ,{"id":19 ,"labelCol"       : "ERR"
                              ,"fieldType"      : "str"
                              ,"tamGrd"         : "1em"
                              ,"tamImp"         : "0"
                              ,"excel"          : "S"
                              ,"padrao":0}
                              
                  ]  
                  , 
                  "botoesH":[
                     {"texto":"Excel"     ,"name":"detExcel"  ,"onClick":"5"  ,"enabled":true,"imagem":"fa fa-file-excel-o"     ,"ajuda":"Exportar para excel" }
                    ,{"texto":"Retornar"  ,"name":"detVoltar" ,"onClick":"7"  ,"enabled":true ,"imagem":"fa fa-plus","ajuda":"Retorna a tela anterior" }
                  ] 
                  ,"registros"      : retPhp                    // Recebe um Json vindo da classe clsBancoDados
                  ,"corLinha"       : "if(ceTr.cells[17].innerHTML =='S') {ceTr.style.color='black';ceTr.style.backgroundColor='#E9967A';}"
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
                  ,"height"         : "60em"                    // Altura da table
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
                  document.getElementById("btnDetalhe").click();  
                  window.location.href="#ancoraDetalhe";
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
      function biImprimirClick(){
        //////////////////////////////////////////////////////////////////////////////////////////
        // O mesmo relatório tanto para veiculos como motorista, muda apelas o select principal //
        //////////////////////////////////////////////////////////////////////////////////////////
        var porVm="V";
        //////////////////////////////
        // CRIANDO UM JSON DA TABLE //
        //////////////////////////////
        clsChecados = objBi.gerarJson();
        clsChecados.retornarQtos("n");        
        clsChecados.temColChk(false);
        retPhp  = clsChecados.gerar();
        tamC    = retPhp.length;

        var imprimir = '{'
          + '"orientacao":"P"'
          + ',"imprimir":'
          + '[{"SetFont":["Arial","B",8]}'          
          + ',{"SetFillColor":["cinzaclaro","8","205"]}'
          + ',{"Cell":[205,8,"RELATORIO:'+jsBi.relTitulo+'",0,0,"C"]}'
          + ',{"Ln":[8]}'
          + ',{"SetFillColor":["branco","5","200"]}'                    
          + ',{"Cell":[20,6,"PLACA"      ,1,0,"L"]}'
          + ',{"Cell":[25,6,"RODANDO"    ,1,0,"L"]}'
          + ',{"Cell":[25,6,"PARADO"     ,1,0,"L"]}'
          + ',{"Cell":[25,6,"TOTAL"      ,1,0,"L"]}'
          + ',{"Cell":[20,6,"KMINI"      ,1,0,"C"]}'
          + ',{"Cell":[20,6,"KMFIM"      ,1,0,"C"]}'
          + ',{"Cell":[20,6,"KM"         ,1,0,"C"]}'
          + ',{"Cell":[30,6,"UNIDADE"    ,1,0,"L"]}'
          + ',{"Cell":[20,6,"POLO"       ,1,0,"L"]}'
          + ',{"Ln":[6]}'
          + ',{"SetFont":["Arial","",8]}';
        var intSeek     = 0;        
        var addUni      = new Array();  //Array somente para buscar unidades
        var tblUni      = new Array();
        var addPol      = new Array();  //Array somente para buscar polos
        var tblPol      = new Array();
        var locRodando  = 0;
        var locParado   = 0;
        var locTotal    = 0;
        var locKm       = 0;
        //
        for(var fc=0; fc<tamC;fc++){
          imprimir += 
           ',{"Cell":[20,6,"'+retPhp[fc].PLACA    +'",0,0,"L"]}'
          +',{"Cell":[25,6,"'+retPhp[fc].RODANDO  +'",0,0,"L"]}'
          +',{"Cell":[25,6,"'+retPhp[fc].PARADO   +'",0,0,"L"]}'
          +',{"Cell":[25,6,"'+retPhp[fc].TOTAL    +'",0,0,"L"]}'
          +',{"Cell":[20,6,"'+retPhp[fc].KMINI    +'",0,0,"C"]}'
          +',{"Cell":[20,6,"'+retPhp[fc].KMFIM    +'",0,0,"C"]}'          
          +',{"Cell":[20,6,"'+retPhp[fc].KM       +'",0,0,"C"]}'          
          +',{"Cell":[30,6,"'+retPhp[fc].UNIDADE  +'",0,0,"L"]}'          
          +',{"Cell":[20,6,"'+retPhp[fc].POLO     +'",0,0,"L"]}'          
          +',{"Ln":[4]}';
          ////////////////////////////////////
          // Revertendo para poder acumular //
          ////////////////////////////////////
          locRodando  =  parseInt(retPhp[fc].INT_RODANDO);
          locParado   =  parseInt(retPhp[fc].INT_PARADO);
          locTotal    =  parseInt(locRodando+locParado);
          locKm       =  jsNmrs(retPhp[fc].KM).dec(4).dolar().ret();
          /////////////////////////////////
          // Acumulando por unidade      //
          /////////////////////////////////
          intSeek=addUni.indexOf(retPhp[fc].UNIDADE);
          if( intSeek==-1 ){
            addUni.push(retPhp[fc].UNIDADE);
            tblUni.push({"UNIDADE"  : retPhp[fc].UNIDADE
                        ,"RODANDO"  : locRodando
                        ,"PARADO"   : locParado
                        ,"TOTAL"    : locTotal
                        ,"KM"       : locKm
            });
          } else {
            tblUni[intSeek].RODANDO +=  locRodando; 
            tblUni[intSeek].PARADO  +=  locParado;
            tblUni[intSeek].TOTAL   +=  (locRodando+locParado);
            tblUni[intSeek].KM      +=  locKm;
          };
          /////////////////////////////////
          // Acumulando por polo         //
          /////////////////////////////////
          intSeek=addPol.indexOf(retPhp[fc].POLO);
          if( intSeek==-1 ){
            addPol.push(retPhp[fc].POLO);
            tblPol.push({"POLO"     : retPhp[fc].POLO
                        ,"RODANDO"  : locRodando
                        ,"PARADO"   : locParado
                        ,"TOTAL"    : locTotal
                        ,"KM"       : locKm
            });
          } else {
            tblPol[intSeek].RODANDO +=  locRodando; 
            tblPol[intSeek].PARADO  +=  locParado;
            tblPol[intSeek].TOTAL   +=  (locRodando+locParado);
            tblPol[intSeek].KM      +=  locKm;
          };
        };
        imprimir +=
         ',{"Ln":[2]}'
        +',{"SetFont":["Arial","B",8]}'
        + ',{"SetFillColor":["cinzaclaro","6","205"]}'
        +',{"Cell":[18,6,"TOTAL",0,0,"L"]}' 
        +',{"Cell":[25,6,"'+segundosEm( pubRodando,"ddddhms" )+'" ,0,0,"L"]}'    
        +',{"Cell":[25,6,"'+segundosEm( pubParado,"ddddhms" )+'"  ,0,0,"L"]}'
        +',{"Cell":[25,6,"'+segundosEm( pubTotal,"ddddhms" )+'"   ,0,0,"L"]}'
        +',{"Cell":[20,6,""                                       ,0,0,"C"]}'
        +',{"Cell":[20,6,""                                       ,0,0,"C"]}'
        +',{"Cell":[25,6,"'+(parseFloat(pubKm)).toFixed(4)+'"     ,0,0,"C"]}'
        +',{"Cell":[30,6,""                                       ,0,0,"L"]}'        
        +',{"Cell":[20,6,""                                       ,0,0,"L"]}'
        +',{"SetFillColor":["branco","5","205"]}'            
        + ',{"Ln":[8]}';
        ///////////////////////////
        // Mostrando as unidades //
        ///////////////////////////
        imprimir +=      
          ',{"SetFont":["Arial","B",8]}' 
        + ',{"SetFillColor":["cinza","6","165"]}'        
        + ',{"Cell":[40,6,"UNIDADE"  ,1,0,"L"]}'
        + ',{"Cell":[25,6,"RODANDO" ,1,0,"L"]}'
        + ',{"Cell":[25,6,"PARADO"  ,1,0,"L"]}'
        + ',{"Cell":[25,6,"TOTAL"   ,1,0,"L"]}'
        + ',{"Cell":[25,6,"KM"      ,1,0,"R"]}'
        + ',{"SetFillColor":["branco","6","165"]}'            
        + ',{"SetFont":["Arial","",8]}'
        + ',{"Ln":[6]}';                  
        tamC = tblUni.length;
        locRodando  =  0;
        locParado   =  0;
        locTotal    =  0;
        locKm       =  0;
        
        for(var fc=0; fc<tamC;fc++){
          imprimir+=
             ',{"SetFont":["Arial","",8]}'
            +',{"Cell":[40,6,"'+tblUni[fc].UNIDADE+'",0,0,"L"]}' 
            +',{"Cell":[25,6,"'+segundosEm( tblUni[fc].RODANDO,"ddddhms" )+'" ,0,0,"L"]}'    
            +',{"Cell":[25,6,"'+segundosEm( tblUni[fc].PARADO,"ddddhms" )+'"  ,0,0,"L"]}'
            +',{"Cell":[25,6,"'+segundosEm( tblUni[fc].TOTAL,"ddddhms" )+'"   ,0,0,"L"]}'
            +',{"Cell":[25,6,"'+jsNmrs(tblUni[fc].KM).dec(4).real().ret()+'"  ,0,0,"R"]}'   
            + ',{"Ln":[4]}';          
          locRodando  +=  tblUni[fc].RODANDO;
          locParado   +=  tblUni[fc].PARADO;
          locTotal    +=  tblUni[fc].TOTAL;
          locKm       +=  tblUni[fc].KM;
        };
        imprimir+=        
         ',{"Ln":[2]}'
        +',{"SetFont":["Arial","B",8]}'
        + ',{"SetFillColor":["cinzaclaro","6","165"]}'
        +',{"Cell":[40,6,"TOTAL",0,0,"L"]}' 
        +',{"Cell":[25,6,"'+segundosEm( locRodando,"ddddhms" )+'"  ,0,0,"L"]}'    
        +',{"Cell":[25,6,"'+segundosEm( locParado,"ddddhms" )+'"   ,0,0,"L"]}'
        +',{"Cell":[25,6,"'+segundosEm( locTotal,"ddddhms" )+'"    ,0,0,"L"]}'
        +',{"Cell":[25,6,"'+jsNmrs(locKm).dec(4).real().ret()+'"   ,0,0,"R"]}'     
        +',{"SetFillColor":["branco","6","165"]}'            
        + ',{"Ln":[8]}';
        ///////////////////////////
        // Mostrando os polos    //
        ///////////////////////////
        imprimir +=      
          ',{"SetFont":["Arial","B",8]}' 
        + ',{"SetFillColor":["cinza","6","165"]}'        
        + ',{"Cell":[40,6,"POLO"    ,1,0,"L"]}'
        + ',{"Cell":[25,6,"RODANDO" ,1,0,"L"]}'
        + ',{"Cell":[25,6,"PARADO"  ,1,0,"L"]}'
        + ',{"Cell":[25,6,"TOTAL"   ,1,0,"L"]}'
        + ',{"Cell":[25,6,"KM"      ,1,0,"R"]}'
        + ',{"SetFillColor":["branco","6","165"]}'            
        + ',{"SetFont":["Arial","",8]}'
        + ',{"Ln":[6]}';                  
        tamC = tblPol.length;
        locRodando  =  0;
        locParado   =  0;
        locTotal    =  0;
        locKm       =  0;
        
        for(var fc=0; fc<tamC;fc++){
          imprimir+=
             ',{"SetFont":["Arial","",8]}'
            +',{"Cell":[40,6,"'+tblPol[fc].POLO+'",0,0,"L"]}' 
            +',{"Cell":[25,6,"'+segundosEm( tblPol[fc].RODANDO,"ddddhms" )+'"  ,0,0,"L"]}'    
            +',{"Cell":[25,6,"'+segundosEm( tblPol[fc].PARADO,"ddddhms" )+'"   ,0,0,"L"]}'
            +',{"Cell":[25,6,"'+segundosEm( tblPol[fc].TOTAL,"ddddhms" )+'"    ,0,0,"L"]}'
            +',{"Cell":[25,6,"'+jsNmrs(tblPol[fc].KM).dec(4).real().ret()+'"   ,0,0,"R"]}'
            + ',{"Ln":[4]}';          
          locRodando  +=  tblPol[fc].RODANDO;
          locParado   +=  tblPol[fc].PARADO;
          locTotal    +=  tblPol[fc].TOTAL;
          locKm       +=  tblPol[fc].KM;
        };
        imprimir+=        
         ',{"Ln":[2]}'
        +',{"SetFont":["Arial","B",8]}'
        + ',{"SetFillColor":["cinzaclaro","6","165"]}'
        +',{"Cell":[40,6,"TOTAL",0,0,"L"]}' 
        +',{"Cell":[25,6,"'+segundosEm( locRodando,"ddddhms" )+'"  ,0,0,"L"]}'    
        +',{"Cell":[25,6,"'+segundosEm( locParado,"ddddhms" )+'"   ,0,0,"L"]}'
        +',{"Cell":[25,6,"'+segundosEm( locTotal,"ddddhms" )+'"    ,0,0,"L"]}'
        +',{"Cell":[25,6,"'+jsNmrs(locKm).dec(4).real().ret()+'"   ,0,0,"R"]}'
        +',{"SetFillColor":["branco","6","165"]}'            
        + ',{"Ln":[6]}';
        imprimir += ']}';
        mostrarImpressao(imprimir);
      };
    </script>
  </head>
  <body>
    <div id="divCabec" class="comboSobreTable" style="margin-top:5px;float:left;">
      <a name="ancoraCabec"></a> 
      <!-- <div class="campotexto campo15">
        <select class="campo_input_combo" id="cbIni">
					
          <option value="201805|201805">MAI/18</option>
          <option value="201806|201806">JUN/18</option>
          <option value="201807|201807">JUL/18</option>
          <option value="201808|201808">AGO/18</option>
          <option value="201809|201809">SET/18</option>
          <option value="201810|201810">OUT/18</option>
          <option value="201811|201811">NOV/18</option>
          <option value="201812|201812">DEZ/18</option>
					
        </select>
        <label class="campo_label campo_required" for="cbIni">INICIO</label>
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
      <div class="acrdnDiv" style="width:90%;margin-left:0.1em;height:70em;">
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