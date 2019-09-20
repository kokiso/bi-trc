<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="SKYPE_TOOLBAR" content="SKYPE_TOOLBAR_PARSER_COMPATIBLE" />
<script language="javascript" type="text/javascript"></script>
<!DOCTYPE html>
  <head>
    <meta charset="utf-8">
    <title>Passo a passo</title>
    <link rel="stylesheet" href="css/css2017.css">
    <link rel="stylesheet" href="css/cssTable2017.css">
    <script src="js/js2017.js"></script>
    <script src="js/jsTable2017.js"></script>
    <script src="tabelaTrac/bkp/tabelaBkp_Cargo.js"></script>
    <script src="tabelaTrac/bkp/tabelaBkp_Evento.js"></script>
    <script src="tabelaTrac/bkp/tabelaBkp_EventoGrupo.js"></script>
    <script src="tabelaTrac/bkp/tabelaBkp_Grupo.js"></script>
    <script src="tabelaTrac/bkp/tabelaBkp_Motorista.js"></script>
    <script src="tabelaTrac/bkp/tabelaBkp_Polo.js"></script>
    <script src="tabelaTrac/bkp/tabelaBkp_Unidade.js"></script>
    <script src="tabelaTrac/bkp/tabelaBkp_Usuario.js"></script>
    <script src="tabelaTrac/bkp/tabelaBkp_UsuarioPerfil.js"></script>
    <script src="tabelaTrac/bkp/tabelaBkp_Veiculo.js"></script>
    <script>
      "use strict";
      document.addEventListener("DOMContentLoaded", function(){ 
        document.getElementById("edtData").value  = jsDatas(0).retDDMMYYYY(); 
        seekDt    = jsDatas(document.getElementById("edtData").value).retMMDDYYYY();        
      });
      var objBkpCrg;              // Obrigatório para instanciar JS Cargo
      var objBkpEg;               // Obrigatório para instanciar JS EventoGrupo
      var objBkpEve;              // Obrigatório para instanciar JS Evento
      var objBkpGrp;              // Obrigatório para instanciar JS Grupo
      var objBkpMtr;              // Obrigatório para instanciar JS Motorista
      var objBkpPol;              // Obrigatório para instanciar JS Polo
      var objBkpUni;              // Obrigatório para instanciar JS Unidade
      var objBkpUsr;              // Obrigatório para instanciar JS Usuario
      var objBkpUp;               // Obrigatório para instanciar JS UsuarioPerfil 
      var objBkpUu;               // Obrigatório para instanciar JS UsuarioUnidade
      var objBkpVcl;              // Obrigatório para instanciar JS Veiculo
      //
      var seekDt;      
      var contMsg   = 0;
      var cmp       = new clsCampo(); 
      var arrParam  = JSON.parse(localStorage.getItem("envJson"));
      var jsPub     = JSON.parse(localStorage.getItem("lsPublico"));
      var where     = "";
      //
      function fFiltrar(){
        var exc=document.getElementById("cbExcluido").value;
        switch (arrParam.espiao[0].tabela) {
          case "BKPCARGO":
            where=(exc=="N" ? arrParam.espiao[0].campo :" WHERE CRG_DATA>='"+seekDt+"' AND CRG_ACAO='E'");
            fBkp_Cargo(where,exc);
            break;
          case "BKPEVENTO":
            where=(exc=="N" ? arrParam.espiao[0].campo :" WHERE EVE_DATA>='"+seekDt+"' AND EVE_ACAO='E'");
            fBkp_Evento(where,exc);
            break;
          case "BKPEVENTOGRUPO":
            where=(exc=="N" ? arrParam.espiao[0].campo :" WHERE EG_DATA>='"+seekDt+"' AND EG_ACAO='E'");
            fBkp_EventoGrupo(where,exc);
            break;
          case "BKPGRUPO":
            where=(exc=="N" ? arrParam.espiao[0].campo :" WHERE GRP_DATA>='"+seekDt+"' AND GRP_ACAO='E'");
            fBkp_Grupo(where,exc);
            break;
          case "BKPMOTORISTA":
            where=(exc=="N" ? arrParam.espiao[0].campo :" WHERE MTR_DATA>='"+seekDt+"' AND MTR_ACAO='E'");
            fBkp_Motorista(where,exc);
            break;
          case "BKPPOLO":
            where=(exc=="N" ? arrParam.espiao[0].campo :" WHERE POL_DATA>='"+seekDt+"' AND POL_ACAO='E'");
            fBkp_Polo(where,exc);
            break;
          case "BKPUNIDADE":
            where=(exc=="N" ? arrParam.espiao[0].campo :" WHERE UNI_DATA>='"+seekDt+"' AND UNI_ACAO='E'");
            fBkp_Unidade(where,exc);
            break;
          case "BKPUSUARIO":
            where=(exc=="N" ? arrParam.espiao[0].campo :" WHERE USR_DATA>='"+seekDt+"' AND USR_ACAO='E'");
            fBkp_Usuario(where,exc);
            break;
          case "BKPUSUARIOPERFIL":
            where=(exc=="N" ? arrParam.espiao[0].campo :" WHERE UP_DATA>='"+seekDt+"' AND UP_ACAO='E'");
            fBkp_UsuarioPerfil(where,exc);
            break;
          case "BKPUSUARIOUNIDADE":
            where=(exc=="N" ? arrParam.espiao[0].campo :" WHERE UU_DATA>='"+seekDt+"' AND UU_ACAO='E'");
            fBkp_UsuarioOperacao(where,exc);
            break;
          case "BKPVEICULO":
            where=(exc=="N" ? arrParam.espiao[0].campo :" WHERE VCL_DATA>='"+seekDt+"' AND VCL_ACAO='E'");
            fBkp_Veiculo(where,exc);
            break;
        };    
      };
    </script>
  </head>

  <body style="background-color: #ecf0f5;">
    <div class="divTelaCheia">
      <div id="divTopoInicio" class="divTopoInicio">
        <div class="divTopoInicio_logo"></div>
        <div class="divTopoInicio_Informacao" style="padding-top: 1em;">
          <div class="campotexto campo25">
          </div>
          
          <div class="campotextoNew campo12">
            <input class="campo_input input" id="edtData" type="text" OnKeyUp="mascaraData(this,event);" maxlength="10" />
            <label class="campo_labelNew" for="edtData">A partir de:</label>
          </div>
          <div class="campotextoNew campo12">
            <select class="campo_input_combo" name="cbExcluido" id="cbExcluido" class="selectBis">
              <option value="S">SIM</option>
              <option value="N" selected>NAO</option>
            </select>
            <label class="campo_labelNew" for="cbExcluido">Excluidos:</label>
          </div>
          <div class="campo12" style="float:left;">            
            <input id="btnFiltrar" onClick="fFiltrar();" type="button" value="Filtrar" class="campo100 tableBotao botaoForaTable"/>            
          </div>
        </div>
        <div class="logoHome">
          <input id="lhFechar" onClick="window.close();" type="button" value="Sair" class="campo50 tableFechar"/>            
        </div>
      </div>
      <div id="espiaoModal" class="divShowModal" style="display:none;"></div>
      <form method="post" name="espiao" class="center" id="frmEspiao" action="classPhp/imprimirSql.php" target="_newpage" >
        <input type="hidden" id="sql" name="sql"/>
        <div id="tabelaEsp" class="center inactive" style="position:fixed;top:10em;width:90em;z-index:30;" >      
        </div>  
      </form>
    </div>
  </body>
</html>