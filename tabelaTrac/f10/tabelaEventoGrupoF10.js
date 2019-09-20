////////////////////////////////////////////////////////////////////////////
// opc=0 - Abre a janela F10                                              //
// opc=1 - Retorna somente o select para Janela F10 ou para blur do campo //
// foco  - Onde vai o foco quando confirmar                               //
////////////////////////////////////////////////////////////////////////////
function fEventoGrupoF10(opc,codEg,foco){
  var sql="SELECT A.EG_CODIGO AS CODIGO,A.EG_NOME AS DESCRICAO"
         +"  FROM EVENTOGRUPO A";
  if( opc == 0 ){            
    sql+=" WHERE (A.EG_ATIVO='S')";  
    //////////////////////////////////////////////////////////////////////////////
    // localStorage eh o arquivo .php onde estao os select/insert/update/delete //
    //////////////////////////////////////////////////////////////////////////////
    var bdEg=new clsBancoDados(localStorage.getItem("lsPathPhp"));
    //
    //
    bdEg.Assoc=false;
    bdEg.select( sql );
    if( bdEg.retorno=='OK'){
      var jsEgF10={
        "titulo":[
           {"id":0 ,"labelCol":"OPC"       ,"tipo":"chk"  ,"tamGrd":"5em"   ,"fieldType":"chk"}                                
          ,{"id":1 ,"labelCol":"CODIGO"    ,"tipo":"edt"  ,"tamGrd":"6em"   ,"fieldType":"str","ordenaColuna":"S"}
          ,{"id":2 ,"labelCol":"DESCRICAO" ,"tipo":"edt"  ,"tamGrd":"30em"  ,"fieldType":"str","ordenaColuna":"S"}
        ]
        ,"registros"      : bdEg.dados             // Recebe um Json vindo da classe clsBancoDados
        ,"opcRegSeek"     : true                    // Opção para numero registros/botão/procurar                       
        ,"checarTags"     : "N"                     // Somente em tempo de desenvolvimento(olha as pricipais tags)
        ,"tbl"            : "tblEg"                // Nome da table
        ,"prefixo"        : "Eg"                   // Prefixo para elementos do HTML em jsTable2017.js
        ,"tabelaBD"       : "EVENTOGRUPO"                 // Nome da tabela no banco de dados  
        ,"width"          : "52em"                  // Tamanho da table
        ,"height"         : "37em"                  // Altura da table
        ,"indiceTable"    : "DESCRICAO"             // Indice inicial da table
      };
      if( objEgF10 === undefined ){          
        objEgF10         = new clsTable2017("objEgF10");
        objEgF10.tblF10  = true;
        if( foco != undefined ){
          objEgF10.focoF10=foco;  
        };
      };  
      var html          = objEgF10.montarHtmlCE2017(jsEgF10);
      var ajudaF10      = new clsMensagem('Ajuda');
      ajudaF10.divHeight= '400px';  /* Altura container geral*/
      ajudaF10.divWidth = '42%';
      ajudaF10.tagH2    = false;
      ajudaF10.mensagem = html;
      ajudaF10.Show('ajudaEg');
      document.getElementById('tblEg').rows[0].cells[2].click();
      delete(ajudaF10);
      delete(objEgF10);
    };
  }; 
  if( opc == 1 ){
    sql+=" WHERE (A.EG_CODIGO='"+document.getElementById(codEg).value.toUpperCase()+"')"
        +"   AND (A.EG_ATIVO='S')";
    var bdEg=new clsBancoDados(localStorage.getItem("lsPathPhp"));
    bdEg.Assoc=true;
    bdEg.select( sql );
    return bdEg.dados;
  };     
};