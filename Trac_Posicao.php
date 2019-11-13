<?php
  session_start();
  if( isset($_POST["posicao"]) ){
    try{     
      require("classPhp/conectaSqlServer.class.php");
      require("classPhp/validaJson.class.php"); 
      require("classPhp/removeAcento.class.php"); 

      $vldr     = new validaJSon();          
      $retorno  = "";
      $retCls   = $vldr->validarJs($_POST["posicao"]);
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
        ////////////////////////////////////////////////
        //       Dados para JavaScript POSICAO        //
        ////////////////////////////////////////////////
        if( $rotina=="selectPos" ){
					$metodo             = 'Lista_HistoricoPosicoesPorIdPosicao';
					$opcoes             = array('location' => 'http://wsgslog.globalsearch.com.br/V20160/posicoes.asmx');
					$parametros         = array ([
																				'EmpCliente'        => 138481
																				,'Login'            => "wsrelieve"
																				,'Senha'            => "wsrelieve"
																				,'Id_Posicao'       => ($lote[0]->id-1)
																				,'ObterLocalizacao' => false
																			]);  
					$cnx                = new SoapClient('http://wsgslog.globalsearch.com.br/V20160/posicoes.asmx?wsdl');
							
					try {
						$resultado        = $cnx->__soapCall($metodo,$parametros,$opcoes);

						if (isset($resultado->Lista_HistoricoPosicoesPorIdPosicaoResult->Posicao)) {
							$lista            = $resultado->Lista_HistoricoPosicoesPorIdPosicaoResult->Posicao;
							$ret=[];
							foreach($lista as $posicao){
								array_push($ret,[
									$posicao->Id_Posicao
									,$posicao->Id_PosicaoIntegracao
									,$posicao->Id_Veiculo
									,$posicao->Id_Cliente
									,$posicao->IdentificacaoMotorista
									,$posicao->NomeMotorista
									,$posicao->IdFornecedorLocalizacao
									,$posicao->DescricaoVeiculo
									,$posicao->IdentificacaoVeiculo
									,(isset($posicao->Placa) ? $posicao->Placa : 'XXXX' )
									,$posicao->NomeCliente
									,$posicao->Id_Evento
									,$posicao->DescricaoEvento
									,$posicao->NumeroSerie
									,$posicao->Latitude
									,$posicao->Longitude
									,$posicao->Velocidade
									,$posicao->RPM
									,$posicao->Odometro
									,$posicao->Ignicao
									,$posicao->Temperatura
									,$posicao->DataGPS
									,$posicao->DataServidor
									,$posicao->Localizacao
									,$posicao->Horimetro
							  ]);
							  break;		
							};
							$retorno='[{"retorno":"OK","dados":'.json_encode($ret).',"erro":""}]'; 
							
						} else 
							throw new Exception('Nao foram retornados posicoes com estes parametros.');
					} catch (Exception $e) {
						echo $e->getMessage();
					}
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
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="SKYPE_TOOLBAR" content="SKYPE_TOOLBAR_PARSER_COMPATIBLE" />
    <title>Posicao</title>
    <link rel="stylesheet" href="css/css2017.css">
    <link rel="stylesheet" href="css/cssTable2017.css">
    <link rel="stylesheet" href="css/cssFaTable.css">
    <script src="js/js2017.js"></script>
    <script src="js/jsTable2017.js"></script>
    <script src="tabelaTrac/f10/tabelaUnidadeF10.js"></script>    
    <script language="javascript" type="text/javascript"></script>
    <style>
      .comboSobreTable {
        position:relative;
        float:left;
        display:block;
        overflow-x:auto;
        width:92em;
        height:5em;
        border:1px solid silver;
        border-radius: 6px 6px 6px 6px;
        background-color:white;        
      }
      .botaoSobreTable {
        width:6em;
        margin-left:0.2em;
        margin-top:0.3em;
        height:3.05em;
        border-radius: 4px 4px 4px 4px;
      }
    </style>  
    <script>
      "use strict";
      ////////////////////////////////////////////////
      // Executar o codigo após a pagina carregada  //
      ////////////////////////////////////////////////
      document.addEventListener("DOMContentLoaded", function(){ 
        /////////////////////////////////////////////
        //       Objeto clsTable2017 POSICAO       //
        /////////////////////////////////////////////
        jsPos={
          "titulo":[
              {"id":0 ,"labelCol":"OPC","padrao":1}            
             ,{"id":1 ,"field":"Id_Posicao"				,"labelCol":"Id_Posicao"				,"tamGrd":"7em","excel":"S","padrao":0}
             ,{"id":2 ,"field":"Id_PosIntegracao"	,"labelCol":"Id_PosIntegracao"	,"tamGrd":"7em","excel":"S","padrao":0}
             ,{"id":3	,"field":"Id_Veiculo"				,"labelCol":"Id_Veiculo"				,"tamGrd":"7em","excel":"S","padrao":0}
             ,{"id":4	,"field":"Id_Cliente"				,"labelCol":"Id_Cliente"				,"tamGrd":"7em","excel":"S","padrao":0}
             ,{"id":5	,"field":"Identif_Mot"			,"labelCol":"Identif_Mot"				,"tamGrd":"7em","excel":"S","padrao":0}
             ,{"id":6	,"field":"NomeMotorista"		,"labelCol":"NomeMotorista"			,"tamGrd":"30em","excel":"S","padrao":0}
             ,{"id":7	,"field":"IdFornecLocaliz"	,"labelCol":"IdFornecLocaliz"		,"tamGrd":"7em","excel":"S","padrao":0}
             ,{"id":8	,"field":"DescricaoVeiculo"	,"labelCol":"DescricaoVeiculo"	,"tamGrd":"10em","excel":"S","padrao":0}
             ,{"id":9	,"field":"Identif_Veiculo"	,"labelCol":"Identif_Veiculo"		,"tamGrd":"7em","excel":"S","padrao":0}
             ,{"id":10,"field":"Placa"						,"labelCol":"Placa"							,"tamGrd":"7em","excel":"S","padrao":0}
             ,{"id":11,"field":"NomeCliente"			,"labelCol":"NomeCliente"				,"tamGrd":"7em","excel":"S","padrao":0}
             ,{"id":12,"field":"Id_Evento"				,"labelCol":"Id_Evento"					,"tamGrd":"7em","excel":"S","padrao":0}
             ,{"id":13,"field":"DescricaoEvento"	,"labelCol":"DescricaoEvento"		,"tamGrd":"20em","excel":"S","padrao":0}
             ,{"id":14,"field":"NumeroSerie"			,"labelCol":"NumeroSerie"				,"tamGrd":"10em","excel":"S","padrao":0}
             ,{"id":15,"field":"Latitude"					,"labelCol":"Latitude"					,"tamGrd":"7em","excel":"S","padrao":0}
             ,{"id":16,"field":"Longitude"				,"labelCol":"Longitude"					,"tamGrd":"7em","excel":"S","padrao":0}
             ,{"id":17,"field":"Velocidade"				,"labelCol":"Velocidade"				,"tamGrd":"7em","excel":"S","padrao":0}
             ,{"id":18,"field":"RPM"							,"labelCol":"RPM"								,"tamGrd":"7em","excel":"S","padrao":0}
             ,{"id":19,"field":"Odometro"					,"labelCol":"Odometro"					,"tamGrd":"7em","excel":"S","padrao":0}
             ,{"id":20,"field":"Ignicao"					,"labelCol":"Ignicao"						,"tamGrd":"7em","excel":"S","padrao":0}
             ,{"id":21,"field":"Temperatura"			,"labelCol":"Temperatura"				,"tamGrd":"7em","excel":"S","padrao":0}
             ,{"id":22,"field":"DataGPS"					,"labelCol":"DataGPS"						,"tamGrd":"15em","excel":"S","padrao":0}
             ,{"id":23,"field":"DataServidor"			,"labelCol":"DataServidor"			,"tamGrd":"15em","excel":"S","padrao":0}
             ,{"id":24,"field":"Localizacao"			,"labelCol":"Localizacao"				,"tamGrd":"7em","excel":"S","padrao":0}
             ,{"id":25,"field":"Horimetro"				,"labelCol":"Horimetro"					,"tamGrd":"7em","excel":"S","padrao":0}
          ]
          , 
          "botoesH":[
						{"texto":"Excel"     ,"name":"horExcel"      ,"onClick":"5"  ,"enabled":true,"imagem":"fa fa-file-excel-o"     ,"ajuda":"Exportar para excel" }        
            ,{"texto":"Fechar"    ,"name":"horFechar"     ,"onClick":"8"  ,"enabled":true ,"imagem":"fa fa-close"            ,"ajuda":"Fechar formulario" }
          ] 
          ,"registros"      : []                        // Recebe um Json vindo da classe clsBancoDados
          ,"checarTags"     : "S"                       // Somente em tempo de desenvolvimento(olha as pricipais tags)                  
          ,"div"            : "frmPos"                  // Onde vai ser gerado a table
          ,"divFieldSet"    : "tabelaPos"               // Para fechar a div onde estão os fieldset ao cadastrar
          ,"form"           : "frmPos"                  // Onde vai ser gerado o fieldSet       
          ,"divModal"       : "divTopoInicio"           // Onde vai se appendado abaixo deste a table 
          ,"tbl"            : "tblPos"                  // Nome da table
          ,"prefixo"        : "pos"                     // Prefixo para elementos do HTML em jsTable2017.js
          ,"tabelaBD"       : "*"                				// Nome da tabela no banco de dados  
          ,"tabelaBKP"      : "*"              					// Nome da tabela no banco de dados  
          ,"fieldAtivo"     : "*"               				// SE EXISITIR - Nome do campo ATIVO(S/N) na tabela BD
          ,"fieldReg"       : "*"                 			// SE EXISITIR - Nome do campo SYS(P/A) na tabela BD            
          ,"fieldCodUsu"    : "*"              					// SE EXISITIR - Nome do campo CODIGO USUARIO na tabela BD                        
          ,"iFrame"         : "iframeCorpo"             // Se a table vai ficar dentro de uma tag iFrame
          ,"width"          : "110em"                    // Tamanho da table
          ,"height"         : "58em"                    // Altura da table
          ,"tableLeft"      : "sim"                     // Se tiver menu esquerdo
          ,"relTitulo"      : "POSICAO"                 // Titulo do relatório
          ,"relOrientacao"  : "P"                       // Paisagem ou retrato
          ,"relFonte"       : "8"                       // Fonte do relatório
          ,"formPassoPasso" : "*"         							// Enderço da pagina PASSO A PASSO
          ,"indiceTable"    : "ID"                   		// Indice inicial da table
          ,"tamBotao"       : "15"                      // Tamanho botoes defalt 12 [12/25/50/75/100]
        }; 
        if( objPos === undefined ){  
          objPos=new clsTable2017("objPos");
        };  
        objPos.montarHtmlCE2017(jsPos); 
        //////////////////////////////////////////////////
        //          Fim objeto clsTable2017 VEICULO     //
        ////////////////////////////////////////////////// 
      });
      var objPos;                     // Obrigatório para instanciar o JS TFormaCob
      var jsPos;                      // Obj principal da classe clsTable2017
      var clsJs;                      // Classe responsavel por montar um Json e eviar PHP
      var clsErro;                    // Classe para erros            
      var fd;                         // Formulario para envio de dados para o PHP
      var msg;                        // Variavel para guardadar mensagens de retorno/erro 
      var retPhp                      // Retorno do Php para a rotina chamadora
      var contMsg   = 0;              // contador para mensagens
      var cmp       = new clsCampo(); // Abrindo a classe campos
      var jsPub     = JSON.parse(localStorage.getItem("lsPublico"));
      var intCodDir = parseInt(jsPub[0].usr_d12);
      ////////////////////////////
      // Filtrando os registros //
      ////////////////////////////
      function btnFiltrarClick(atv) {
				clsJs   = jsString("lote");  
				clsJs.add("rotina"      , "selectPos"                             );
				clsJs.add("login"       , jsPub[0].usr_login                      );
				clsJs.add("id"          , document.getElementById("edtId").value	);          
				fd = new FormData();
				fd.append("posicao" , clsJs.fim());
				msg     = requestPedido("Trac_Posicao.php",fd); 
				retPhp  = JSON.parse(msg);
				if( retPhp[0].retorno == "OK" ){
					//////////////////////////////////////////////////////////////////////////////////
					// O novo array não tem o campo idUnico mas a montarHtmlCE2017 ja foi executada //
					// Campo obrigatório se existir rotina de manutenção na table devido Json       //
					// Esta rotina não tem manutenção via classe clsTable2017                       //
					// jsCrv.registros=objCrv.addIdUnico(retPhp[0]["dados"]);                       //
					//////////////////////////////////////////////////////////////////////////////////
					jsPos.registros=objPos.addIdUnico(retPhp[0]["dados"]);
					objPos.ordenaJSon(jsPos.indiceTable,false);  
					objPos.montarBody2017();
				};  
      };
    </script>
  </head>
  <body>
    <div id="divEvento" class="comboSobreTable">
			<div class="campotexto campo25">
				<input class="campo_input" id="edtId" type="text" value="907240268" maxlength="10" />
				<label class="campo_label campo_required" for="edtId">ID INICIAL</label>
			</div>
      <div class="campo10" style="float:left;">            
        <input id="btnFilttrar" onClick="btnFiltrarClick('S');" type="button" value="Filtrar" class="botaoSobreTable"/>
      </div>
      <div class="_campotexto campo100" style="margin-top:1.7em;height:3em;">
        <label class="campo_required" style="font-size:1.4em;"></label>
        <label class="campo_labelSombra">Solicitado filtro devido quantidade de registros</label>
      </div>              
    </div>
  
    <div class="divTelaCheia" style="float:left;">
      <div id="divRotina" class="conteudo" style="display:block;overflow-x:auto;">  
        <div id="divTopoInicio">
        </div>
        <form method="post" 
              name="frmPos" 
              id="frmPos" 
              class="frmTable" 
              action="classPhp/imprimirsql.php" 
              target="_newpage"
              style="top: 6em; width:90em;position: absolute; z-index:30;display:none;">
          <p class="frmCampoTit">
					<input type="hidden" id="sql" name="sql"/>
        </form>
      </div>
    </div>       
  </body>
</html>