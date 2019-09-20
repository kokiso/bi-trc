////////////////////////////////////////////////////////////////////////////
// opc=0 - Abre a janela F10                                              //
// opc=1 - Retorna somente o select para Janela F10 ou para blur do campo //
// foco  - Onde vai o foco quando confirmar                               //
////////////////////////////////////////////////////////////////////////////
function fUsuarioPerfilF10(opc,codUp,foco){
  var sql="SELECT A.UP_CODIGO AS CODIGO,A.UP_NOME AS DESCRICAO FROM USUARIOPERFIL A ";
  //            
  if( opc == 0 ){            
    sql+="WHERE (A.UP_ATIVO='S')";  
    //////////////////////////////////////////////////////////////////////////////
    // localStorage eh o arquivo .php onde estao os select/insert/update/delete //
    //////////////////////////////////////////////////////////////////////////////
    var bdUp=new clsBancoDados(localStorage.getItem('lsPathPhp'));
    bdUp.Assoc=false;
    bdUp.select( sql );
    if( bdUp.retorno=='OK'){
      var jsUpF10={
        "titulo":[
           {"id":0 ,"labelCol":"OPC"       ,"tipo":"chk"  ,"tamGrd":"5em"   ,"fieldType":"chk"}                                
          ,{"id":1 ,"labelCol":"CODIGO"    ,"tipo":"edt"  ,"tamGrd":"6em"   ,"fieldType":"int","formato":['i4'],"ordenaColuna":"S","align":"center"}
          ,{"id":2 ,"labelCol":"DESCRICAO" ,"tipo":"edt"  ,"tamGrd":"30em"  ,"fieldType":"str","ordenaColuna":"S"}
        ]
        ,"registros"      : bdUp.dados              // Recebe um Json vindo da classe clsBancoDados
        ,"opcRegSeek"     : true                    // Opção para numero registros/botão/procurar                       
        ,"checarTags"     : "N"                     // Somente em tempo de desenvolvimento(olha as pricipais tags)
        ,"tbl"            : "tblUp"                 // Nome da table
        ,"prefixo"        : "up"                    // Prefixo para elementos do HTML em jsTable2017.js
        ,"tabelaBD"       : "USUARIOPERFIL"         // Nome da tabela no banco de dados  
        ,"width"          : "52em"                  // Tamanho da table
        ,"height"         : "37em"                  // Altura da table
        ,"indiceTable"    : "DESCRICAO"             // Indice inicial da table
      };
      if( objUpF10 === undefined ){          
        objUpF10         = new clsTable2017("objUpF10");
        objUpF10.tblF10  = true;
        if( foco != undefined ){
          objUpF10.focoF10=foco;  
        };
      };  
      var html          = objUpF10.montarHtmlCE2017(jsUpF10);
      var ajudaF10      = new clsMensagem('Ajuda');
      ajudaF10.divHeight= '400px';  /* Altura container geral*/
      ajudaF10.divWidth = '42%';
      ajudaF10.tagH2    = false;
      ajudaF10.mensagem = html;
      ajudaF10.Show('ajudaUp');
      document.getElementById('tblUp').rows[0].cells[2].click();
      delete(ajudaF10);
      delete(objUpF10);
    };
  }; 
  if( opc == 1 ){
    sql+=" WHERE (A.UP_CODIGO='"+document.getElementById(codUp).value.toUpperCase()+"')"
        +"   AND (A.UP_ATIVO='S')";
    var bdUp=new clsBancoDados(localStorage.getItem("lsPathPhp"));
    bdUp.Assoc=true;
    bdUp.select( sql );
    return bdUp.dados;
  };     
};