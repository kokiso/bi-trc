////////////////////////////////////////////////////////////////////////////
// opc=0 - Abre a janela F10                                              //
// opc=1 - Retorna somente o select para Janela F10 ou para blur do campo //
// foco  - Onde vai o foco quando confirmar                               //
// jsPub[0].usr_grupos sao os grupos que o usuario pode ver               //
////////////////////////////////////////////////////////////////////////////
function fGrupoF10(opc,codGrp,foco){
  var sql="SELECT A.GRP_CODIGO AS CODIGO,A.GRP_NOME AS DESCRICAO,A.GRP_APELIDO AS APELIDO" 
          +"  FROM GRUPO A WHERE (A.GRP_CODIGO IN"+jsPub[0].usr_grupos+")";
  //            
  if( opc == 0 ){            
    sql+="AND (A.GRP_ATIVO='S')";  
    //////////////////////////////////////////////////////////////////////////////
    // localStorage eh o arquivo .php onde estao os select/insert/update/delete //
    //////////////////////////////////////////////////////////////////////////////
    var bdGrp=new clsBancoDados(localStorage.getItem('lsPathPhp'));
    bdGrp.Assoc=false;
    bdGrp.select( sql );
    if( bdGrp.retorno=='OK'){
      var jsGrpF10={
        "titulo":[
           {"id":0 ,"labelCol":"OPC"       ,"tipo":"chk"  ,"tamGrd":"5em"   ,"fieldType":"chk"}                                
          ,{"id":1 ,"labelCol":"CODIGO"    ,"tipo":"edt"  ,"tamGrd":"6em"   ,"fieldType":"int","formato":['i4'],"ordenaColuna":"S","align":"center"}
          ,{"id":2 ,"labelCol":"DESCRICAO" ,"tipo":"edt"  ,"tamGrd":"30em"  ,"fieldType":"str","ordenaColuna":"S"}
          ,{"id":3 ,"labelCol":"APELIDO"   ,"tipo":"edt"  ,"tamGrd":"0em"   ,"fieldType":"str","ordenaColuna":"S"}
        ]
        ,"registros"      : bdGrp.dados              // Recebe um Json vindo da classe clsBancoDados
        ,"opcRegSeek"     : true                    // Opção para numero registros/botão/procurar                       
        ,"checarTags"     : "N"                     // Somente em tempo de desenvolvimento(olha as pricipais tags)
        ,"tbl"            : "tblGrp"                 // Nome da table
        ,"prefixo"        : "Grp"                    // Prefixo para elementos do HTML em jsTable2017.js
        ,"tabelaBD"       : "GRUPO"                 // Nome da tabela no banco de dados  
        ,"width"          : "52em"                  // Tamanho da table
        ,"height"         : "37em"                  // Altura da table
        ,"indiceTable"    : "DESCRICAO"             // Indice inicial da table
      };
      if( objGrpF10 === undefined ){          
        objGrpF10         = new clsTable2017("objGrpF10");
        objGrpF10.tblF10  = true;
        if( foco != undefined ){
          objGrpF10.focoF10=foco;  
        };
      };  
      var html          = objGrpF10.montarHtmlCE2017(jsGrpF10);
      var ajudaF10      = new clsMensagem('Ajuda');
      ajudaF10.divHeight= '400px';  /* Altura container geral*/
      ajudaF10.divWidth = '42%';
      ajudaF10.tagH2    = false;
      ajudaF10.mensagem = html;
      ajudaF10.Show('ajudaGrp');
      document.getElementById('tblGrp').rows[0].cells[2].click();
      delete(ajudaF10);
      delete(objGrpF10);
    };
  }; 
  if( opc == 1 ){
    sql+=" AND (A.GRP_CODIGO='"+document.getElementById(codGrp).value.toUpperCase()+"')"
       +"  AND (A.GRP_ATIVO='S')";
    var bdGrp=new clsBancoDados(localStorage.getItem("lsPathPhp"));
    bdGrp.Assoc=true;
    bdGrp.select( sql );
    return bdGrp.dados;
  };     
};