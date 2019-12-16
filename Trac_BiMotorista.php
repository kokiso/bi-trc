<?php
  session_start();
  if( isset($_POST["principal"]) ){
    try{     
      require("classPhp/conectaSqlServer.class.php");
      require("classPhp/validaJson.class.php"); 
      require("classPhp/removeAcento.class.php"); 
      require("classPhp/selectRepetidoTrac.class.php"); 
      require("classPhp/dataCompetencia.class.php");

      $vldr     = new validaJSon();          
      $retorno  = "";
      $retCls   = $vldr->validarJs($_POST["principal"]);
      ///////////////////////////////////////////////////////////////////////
      // Variavel mostra que não foi feito apenas selects mas atualizou BD //
      ///////////////////////////////////////////////////////////////////////
      //$atuBd    = false;
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
        //   Buscando apenas as unidades que usuario tem direito //
        ///////////////////////////////////////////////////////////
        if( $rotina=="quaisUnidade" ){
          $cSql   = new SelectRepetido();
          $retSql = $cSql->qualSelect("quaisUnidade",$lote[0]->login, $lote[0]->poloCodigo);
          $retorno='[{"retorno":"'.$retSql["retorno"].'","dados":'.$retSql["dados"].',"erro":"'.$retSql["erro"].'"}]';
        };  
        ///////////////////////////////////////////////////////////
        //     Buscando apenas os polos que usuario tem direito  //
        ///////////////////////////////////////////////////////////
        if( $rotina=="quaisPolo" ){
          $cSql   = new SelectRepetido();
          $retSql = $cSql->qualSelect("quaisPolo",$lote[0]->login);
          $retorno='[{"retorno":"'.$retSql["retorno"].'","dados":'.$retSql["dados"].',"erro":"'.$retSql["erro"].'"}]';
        };  
        ////////////////
        // BI SIMPLES //
        ////////////////
        if( $rotina=="biSimples" ){
          $sql="";
          $sql.="SELECT U.UNI_APELIDO AS NOME";
          $sql.="       ,COUNT(U.UNI_APELIDO) AS QTOS ";
          $sql.="       ,CAST('U' AS VARCHAR(1)) AS UP";
          $sql.="  FROM BI_PRODUTIVIDADEMOTMES A";
          $sql.="  LEFT OUTER JOIN MOTORISTA M ON A.BIPRDM_CODMTR=M.MTR_CODIGO";
          $sql.="  LEFT OUTER JOIN UNIDADE U ON M.MTR_CODUNI=U.UNI_CODIGO";
          $sql.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON M.MTR_CODUNI=UU.UU_CODUNI AND UU.UU_CODUSR=".$_SESSION['usr_codigo'];
          $sql.="  WHERE (A.BIPRDM_ANOMES=".$lote[0]->compet.")";  
          $sql.="    AND (M.MTR_ATIVO='S')";
          $sql.="    AND (COALESCE(UU.UU_ATIVO,'')='S')";
          if( $lote[0]->coduni >0 ){
            $sql.="  AND (M.MTR_CODUNI=".$lote[0]->coduni.")";
          };  
          if( $lote[0]->codpol != "*" ){
            $sql.="  AND (U.UNI_CODPOL='".$lote[0]->codpol."')";
          };  
          $sql.="  GROUP BY U.UNI_APELIDO";              
          $sql.="  UNION ALL ";             
          $sql.="SELECT P.POL_NOME AS NOME";
          $sql.="       ,COUNT(P.POL_NOME) AS QTOS ";
          $sql.="       ,CAST('P' AS VARCHAR(1)) AS UP";
          $sql.="  FROM BI_PRODUTIVIDADEMOTMES A";
          $sql.="  LEFT OUTER JOIN MOTORISTA M ON A.BIPRDM_CODMTR=M.MTR_CODIGO";
          $sql.="  LEFT OUTER JOIN UNIDADE U ON M.MTR_CODUNI=U.UNI_CODIGO";
          $sql.="  LEFT OUTER JOIN POLO P ON U.UNI_CODPOL=P.POL_CODIGO";
          $sql.="  LEFT OUTER JOIN USUARIOUNIDADE UU ON M.MTR_CODUNI=UU.UU_CODUNI AND UU.UU_CODUSR=".$_SESSION['usr_codigo'];
          $sql.="  WHERE (A.BIPRDM_ANOMES=".$lote[0]->compet.")";  
          $sql.="    AND (M.MTR_ATIVO='S')";
          $sql.="    AND (COALESCE(UU.UU_ATIVO,'')='S')";
          if( $lote[0]->coduni >0 ){
            $sql.="  AND (M.MTR_CODUNI=".$lote[0]->coduni.")";
          };  
          if( $lote[0]->codpol != "*" ){
            $sql.="  AND (U.UNI_CODPOL='".$lote[0]->codpol."')";
          };  
          $sql.="  GROUP BY P.POL_NOME"; 

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
                  ,"UP"         => $tblMtr[$lin]["UP"]
                ]);  
            };
            $retorno='[{"retorno":"OK","dados":'.json_encode($arrJava).',"erro":""}]'; 
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
        buscarUni();  //Preenche o combobox
        buscarPol();  //Preenche o combobox
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
        pubCodUni=ibCodUni;
        pubDesUni=ibDesUni;
        pubCodPol=ibCodPol;
        pubDesPol=ibDesPol;
        //pubLevPes=document.getElementById("cbLevePesado").value;
        fncFiltrarTableSimples("bisMotoristaUni"  ,"tblUniMtr","divUniMtr"    ,"qtosUniMtr"   ,pubCodUni,pubCodPol,"*");
        document.getElementById("smllDesUni").innerHTML=ibDesUni;
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
        clsJs.add("coduni"      , qualCodUni                                      );      
        clsJs.add("codpol"      , qualCodPol                                      );
        clsJs.add("levpes"      , qualLevPes                                      );      
        clsJs.add("compet"      , document.getElementById("cbCompetencia").value  );      
        fd = new FormData();
        fd.append("principal" , clsJs.fim());
        msg     = requestPedido("Trac_BiMotorista.php",fd); 
        
        var arrUp=["U","P"];
        var arrTbl=["tblUniMtr","tblPolMtr"];
        var arrDiv=["divUniMtr","divPolMtr"];

        retPhp  = JSON.parse(msg);
        
        if( retPhp[0].retorno == "OK" ){
          if(retPhp[0]["dados"].length==0){
            alert("Nenhum registro localizado!");
          } else {
          
            retPhp=retPhp[0]["dados"];  
            
            for( var q=0;q<=1;q++ ){     
              var arrTitulo = ["DESCRITIVO","GRAFICO","%","QTOS"];
              var arrColW  = ["40%","40%","10%","10%"];

              
              var filtrarUP=function(vouFiltrar){
                return vouFiltrar.UP===arrUp[q];
              };
              var tbl=retPhp.filter(filtrarUP);
             
              
              var qtdCol  = arrColW.length;   // Quantidade de colunas
              var qtdRow  = tbl.length;       // Quantidade de linhas do retorno select
              var totQtos = 0;                // Total de qtos
              
              ///////////////////////////////////////////////
              // Se ja existir a table removo devido click //
              ///////////////////////////////////////////////
              if(document.getElementById(arrTbl[q]) != undefined ){
                document.getElementById(arrTbl[q]).remove();
              };
              /////////////////////
              // Criando a table //  
              /////////////////////
              ceTable           = document.createElement("table");
              ceTable.id        = arrTbl[q];
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
                        ceContext = document.createTextNode( tbl[linR]["NOME"] );
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
              document.getElementById( arrDiv[q] ).appendChild(ceTable);
              //////////////////////////////////////////////////////////////////
              // Nao obrigatorio - Totaliza a coluna qtos no descritivo do BI //
              //////////////////////////////////////////////////////////////////
              if( q==0 ){
                if(document.getElementById("qtosMtr") != undefined ){
                  document.getElementById("qtosMtr").innerHTML=totQtos;
                };
              };
            };
          };  
        };
      };
      //
      //  
      ///////////////////////////////////////////////////////////
      // Buscando apenas as unidades que o usuario tem direito //
      ///////////////////////////////////////////////////////////
      function buscarUni(){
        clsJs   = jsString("lote");  
        clsJs.add("rotina"      , "quaisUnidade"                                  );
        clsJs.add("login"       , jsPub[0].usr_login                              );
        clsJs.add("poloCodigo"  , pubCodPol                                       );
        clsJs.add("compet"      , document.getElementById("cbCompetencia").value  );      
        fd = new FormData();
        fd.append("principal" , clsJs.fim());
        msg     = requestPedido("Trac_BiMotorista.php",fd); 
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
          document.getElementById("qtosUni").innerHTML=(document.getElementById("filtroUni").getElementsByTagName("li").length-1);
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
        msg     = requestPedido("Trac_BiMotorista.php",fd); 
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
          document.getElementById("qtosPol").innerHTML=(document.getElementById("filtroPol").getElementsByTagName("li").length-1);
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
        msg = requestPedido("Trac_BiMotorista.php",fd); 
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
          for( var linR=0;  retPhp[0]["dados"].length < 10 ? linR<retPhp[0]["dados"].length : linR<10;  linR++ ){
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
      function chngCompetencia(){
        iniciarBi(0,"Todas unidades","*","Todos polos");
      };
     </script> 
  </head>
  <body>
    <nav class="navbar navbar-static-top">
      <div class="navbar-custom-menu" style="float:left;width:100%;border-bottom:1px solid silver;">
        <div class="form-group" style="width:10%;height:1.5em;float:left;margin-top:0.5em;">
          <button id="smllDesUni" type="button" class="btn btn-label" style="margin-left:10px;" >BI-...</button>
        </div>
        <!--  
        <div class="form-group" style="width:15%;height:1.5em;float:left;margin-top:0.5em;">
          <select id="cbLevePesado" class="form-control select2" style="width:70%;height:28px;margin-left:3em;">
            <option value="LP" selected="selected">Leve/Pesado</option>
            <option value="P">Pesado</option>
            <option value="L">Leve</option>
          </select>
        </div>
        -->
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

    <section class="content" style="min-height: 120px;">
      <div class="row">
				<div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box bg-blue">
            <span class="info-box-icon"><i class="fa fa-users"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Motoristas</span>
              <span id="qtosMtr" class="info-box-number"></span>
            </div>
           </div>
         </div>
      </div>
    </section>
    
    
    <section class="content">
        
      <div class="row">
        <div class="col-md-6">
          <div class="box">
            <div class="box-header with-border">
              <h3 id="h3UniMot" class="box-title">Motoristas/Unidade</h3>
            </div>
            <div id="divUniMtr" class="box-body" style="height: 250px; overflow-y:auto;">            
            </div>
          </div>
        </div>
        
        <div class="col-md-6">
          <div class="box">
            <div class="box-header with-border">
              <h3 id="h3PolMot" class="box-title">Motoristas/Polo</h3>
            </div>
            <div id="divPolMtr" class="box-body" style="height: 250px; overflow-y:auto;">            
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
