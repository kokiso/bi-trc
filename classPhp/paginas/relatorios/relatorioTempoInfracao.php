<?php

  require_once("../../service/serviceTempoInfracao.php");
  $servicoTempoInfracao     = new serviceTempoInfracao();

  if( isset($_POST["grdConsolidacaoInfracaoTempo"]) ){
    $servicoTempoInfracao->buscaDadosConsolidados('INTEGRAR', $_POST["grdConsolidacaoInfracaoTempo"]);
  }
?>
<!DOCTYPE html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="SKYPE_TOOLBAR" content="SKYPE_TOOLBAR_PARSER_COMPATIBLE" />
    <link rel="icon" type="image/png" href="imagens/logo_aba.png" />
    <title>Infração/tempo</title>
    <link rel="stylesheet" href="../../../css/css2017.css">
    <link rel="stylesheet" href="../../../css/cssTable2017.css">
    <link rel="stylesheet" href="../../../css/Acordeon.css">
    <script src="../../../config/configuracoes.js"></script>
    <script src="../../../js/js2017.js"></script>
    <script src="../../../js/jsTable2017.js"></script>
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
        height:11em;
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
				//comboCompetencia("YYYYMM_MMM/YY",document.getElementById("cbIni"));
        //document.getElementById("cbIni").focus();
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
            ,{"id":9  ,"labelCol"       : "VEL"
                      ,"fieldType"      : "int"
                      ,"align"          : "center"                                      
                      ,"tamGrd"         : "3em"
                      ,"tamImp"         : "0"
                      ,"excel"          : "S"
                      ,"ordenaColuna"   : "S"
                      ,"padrao":0}
            ,{"id":10 ,"labelCol"       : "MAX"
                      ,"fieldType"      : "int"
                      ,"align"          : "center"                                      
                      ,"tamGrd"         : "3em"
                      ,"tamImp"         : "0"
                      ,"excel"          : "S"
                      ,"ordenaColuna"   : "S"
                      ,"padrao":0}
            ,{"id":11 ,"labelCol"       : "MOTORISTA"
                      ,"fieldType"      : "str"
                      ,"tamGrd"         : "20em"
                      ,"tamImp"         : "60"
                      ,"excel"          : "S"
                      ,"ordenaColuna"   : "S"
                      ,"padrao":0}
            ,{"id":12 ,"labelCol"       : "DES"
                      ,"fieldType"      : "str"
                      ,"tamGrd"         : "3em"
                      ,"tamImp"         : "10"
                      ,"excel"          : "S"
                      ,"ordenaColuna"   : "S"
                      ,"funcCor"        : "(objCell.innerHTML=='SIM'  ? objCell.classList.add('corVermelho') : objCell.classList.remove('corVermelho'))"
                      ,"padrao":0}
            ,{"id":13 ,"labelCol"       : "EVE"
                      ,"fieldType"      : "str"
                      ,"tamGrd"         : "3em"
                      ,"tamImp"         : "10"
                      ,"excel"          : "S"
                      ,"ordenaColuna"   : "S"
                      ,"padrao":0}
            ,{"id":14 ,"labelCol"       : "RFID"
                      ,"fieldType"      : "str"
                      ,"tamGrd"         : "10em"
                      ,"tamImp"         : "0"
                      ,"excel"          : "S"
                      ,"ordenaColuna"   : "S"
                      ,"padrao":0}
            ,{"id":15 ,"labelCol"       : "DISTPERC"
                      ,"fieldType"      : "str"
                      ,"tamGrd"         : "10em"
                      ,"tamImp"         : "0"
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
          ,"indiceTable"    : "QTOS"                    // Indice inicial da table
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
        // if(jsPub[0].usr_interno=="E"){
        //   document.getElementById("divCbErro").style.display="none";
        //   document.getElementById("cbErro").value="N";
        //   jsBi.titulo[4].tamGrd="0em";
        //   jsBi.titulo[6].tamGrd="0em";
        //   jsBi.titulo[9].tamGrd="0em";
				// 	jsBi.titulo[12].tamGrd="0em";
        // };
        // objBi.montarHtmlCE2017(jsBi);
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
      var retPhp;                      // Retorno do Php para a rotina chamadora
      var clsChecados;                // Classe para montar Json
      var chkds;                      // Guarda todos registros checados na table 
      var tamC;                       // Guarda a quantidade de registros dentro do vetor chkds
      var contMsg   = 0;              // contador para mensagens
      var cmp       = new clsCampo(); // Abrindo a classe campos
      var jsPub     = JSON.parse(localStorage.getItem("lsPublico"));
      var intCodDir = parseInt(jsPub[0].usr_d04);

      function btnFiltrarConsolidacaoClick() {
        clsJs   = jsString("lote");
				clsJs.add("rotina"  	, "select"                                  	);
				clsJs.add("login"   	, jsPub[0].usr_login                        	);
				clsJs.add("codusu"  	, jsPub[0].usr_codigo                       	);
				clsJs.add("dtini"   	, document.getElementById("cbIni").value    	);
				clsJs.add("frota"   	, document.getElementById("cbFrota").value  	);
				clsJs.add("tempo"   	, document.getElementById("cbTempo").value  	);
				clsJs.add("erro"    	, document.getElementById("cbErro").value   	);
        clsJs.add("infracao"	, document.getElementById("cbInfracao").value	);

        var cbPoloValue = document.getElementById("cbPolo").value;
        var poloCodigo;
        var poloGrupo;

        if(cbPoloValue != "TODOS") {
          poloCodigo = cbPoloValue.split('-')[0];
          poloGrupo = cbPoloValue.split('-')[1];
          clsJs.add("poloCodigo"	, poloCodigo);
          clsJs.add("poloGrupo"	, poloGrupo);
        } else {
          clsJs.add("poloCodigo"	, '');
          clsJs.add("poloGrupo"	, '');
        }

        var cbUnidadeValue = document.getElementById("cbUnidade").value;
        var unidadeCodigo;

        if(cbUnidadeValue != "TODOS") {
          unidadeCodigo = cbUnidadeValue.split('-')[0];
          clsJs.add("unidadeCodigo"	, unidadeCodigo);
        } else {
          clsJs.add("unidadeCodigo"	, '');
        }

        var obrigaUnidade = true;

        if(jsPub[0].usr_cargo == 'ADM' || cbUnidadeValue != "TODOS") {
          obrigaUnidade = false;
        }

        if(!obrigaUnidade) {
          fd = new FormData();
          fd.append("grdConsolidacaoInfracaoTempo" , clsJs.fim());
          msg     = requestPedido("relatorioTempoInfracao.php",fd);
          retPhp  = JSON.parse(msg);
          if( retPhp[0].retorno == "OK" ){
              var dlink = document.createElement('a');
              dlink.download = new Date().toLocaleString('default', { day: 'numeric', month: 'long', year: 'numeric'});
              dlink.href = ENDERECO_DOWNLOAD + retPhp[0].data + '.xlsx';
              dlink.click();
              dlink.remove();   
          }
        } else {
          gerarMensagemErro("catch",'É obrigatório escolher uma unidade.',"Atenção");
        }
      }

      function montaUnidade() {
        var cbPoloValue = document.getElementById("cbPolo").value;
        var divUnidade = document.getElementById("divCbUnidade");
        var poloCodigo;
        var poloGrupo;

        if(cbPoloValue != "TODOS") {
          poloCodigo = cbPoloValue.split('-')[0];
          poloGrupo = cbPoloValue.split('-')[1];

          clsJs   = jsString("lote");
          clsJs.add("poloCodigo"  	, poloCodigo                    );
          clsJs.add("poloGrupo"  	, poloGrupo                       );
        } else {
          clsJs   = jsString("lote");
          clsJs.add("poloCodigo"  	, ""                    );
          clsJs.add("poloGrupo"  	, ""                       );
        }

        fd = new FormData();
        fd.append("montaSelectUnidade" , clsJs.fim());
        var selectUnidade = requestPedido("../../comum/selectUnidade.class.php",fd);
        document.getElementById('selectUnidadePHP').innerHTML = selectUnidade;
        document.getElementById('cbUnidade').value="TODOS";
      };
    </script>
  </head>
  <body>
    <div id="divCabec" class="comboSobreTable" style="margin-top:5px;float:left;">
      <a name="ancoraCabec"></a> 
      
      <?php include '../../comum/selectMes.class.php';?>
      
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
      <div id="divCbErro" class="campotexto campo12">
        <select class="campo_input_combo" id="cbInfracao">
          <option value="TODOS" selected="selected">EV/EVC</option>
          <option value="EV">EV</option>
          <option value="EVC">EVC</option>
        </select>
        <label class="campo_label campo_required" for="cbInfracao">INFRAÇÃO</label>
      </div>

      <?php include '../../comum/selectPolo.class.php';?>

      <div id="selectUnidadePHP">        
        <?php include '../../comum/selectUnidade.class.php';?>
      </div>
      
      <div class="campo10" style="float:left;">            
        <input id="btnFiltrar" onClick="btnFiltrarConsolidacaoClick();" type="button" value="Relatório" class="botaoSobreTable"/>
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