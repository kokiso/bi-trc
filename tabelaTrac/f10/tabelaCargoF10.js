////////////////////////////////////////////////////////////////////////////
// opc=0 - Abre a janela F10                                              //
// opc=1 - Retorna somente o select para Janela F10 ou para blur do campo //
// foco  - Onde vai o foco quando confirmar                               //
////////////////////////////////////////////////////////////////////////////
function fCargoF10(opc,codCrg,foco){
  var sql="SELECT A.CRG_CODIGO AS CODIGO,A.CRG_NOME AS DESCRICAO FROM CARGO A ";
  if( opc == 0 ){            
    sql+="WHERE (A.CRG_ATIVO='S')";  
    //////////////////////////////////////////////////////////////////////////////
    // localStorage eh o arquivo .php onde estao os select/insert/update/delete //
    //////////////////////////////////////////////////////////////////////////////
    var bdCrg=new clsBancoDados(localStorage.getItem("lsPathPhp"));
    //
    //
    bdCrg.Assoc=false;
    bdCrg.select( sql );
    if( bdCrg.retorno=='OK'){
      var jsCrgF10={
        "titulo":[
           {"id":0 ,"labelCol":"OPC"       ,"tipo":"chk"  ,"tamGrd":"5em"   ,"fieldType":"chk"}                                
          ,{"id":1 ,"labelCol":"CODIGO"    ,"tipo":"edt"  ,"tamGrd":"6em"   ,"fieldType":"str","ordenaColuna":"S"}
          ,{"id":2 ,"labelCol":"DESCRICAO" ,"tipo":"edt"  ,"tamGrd":"30em"  ,"fieldType":"str","ordenaColuna":"S"}
        ]
        ,"registros"      : bdCrg.dados             // Recebe um Json vindo da classe clsBancoDados
        ,"opcRegSeek"     : true                    // Opção para numero registros/botão/procurar                       
        ,"checarTags"     : "N"                     // Somente em tempo de desenvolvimento(olha as pricipais tags)
        ,"tbl"            : "tblCrg"                // Nome da table
        ,"prefixo"        : "crg"                   // Prefixo para elementos do HTML em jsTable2017.js
        ,"tabelaBD"       : "CARGO"                 // Nome da tabela no banco de dados  
        ,"width"          : "52em"                  // Tamanho da table
        ,"height"         : "37em"                  // Altura da table
        ,"indiceTable"    : "DESCRICAO"             // Indice inicial da table
      };
      if( objCrgF10 === undefined ){          
        objCrgF10         = new clsTable2017("objCrgF10");
        objCrgF10.tblF10  = true;
        if( foco != undefined ){
          objCrgF10.focoF10=foco;  
        };
      };  
      var html          = objCrgF10.montarHtmlCE2017(jsCrgF10);
      var ajudaF10      = new clsMensagem('Ajuda');
      ajudaF10.divHeight= '400px';  /* Altura container geral*/
      ajudaF10.divWidth = '42%';
      ajudaF10.tagH2    = false;
      ajudaF10.mensagem = html;
      ajudaF10.Show('ajudaCrg');
      document.getElementById('tblCrg').rows[0].cells[2].click();
      delete(ajudaF10);
      delete(objCrgF10);
    };
  }; 
  if( opc == 1 ){
    sql+=" WHERE (A.CRG_CODIGO='"+document.getElementById(codCrg).value.toUpperCase()+"')"
        +"   AND (A.CRG_ATIVO='S')";
    var bdCrg=new clsBancoDados(localStorage.getItem("lsPathPhp"));
    bdCrg.Assoc=true;
    bdCrg.select( sql );
    return bdCrg.dados;
  };     
};