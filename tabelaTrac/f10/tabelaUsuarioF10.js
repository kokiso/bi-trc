////////////////////////////////////////////////////////////////////////////
// opc=0 - Abre a janela F10                                              //
// opc=1 - Retorna somente o select para Janela F10 ou para blur do campo //
// foco  - Onde vai o foco quando confirmar                               //
////////////////////////////////////////////////////////////////////////////
function fUsuarioF10(opc,codUsr,foco){
  var sql="SELECT A.USR_CODIGO AS CODIGO,A.USR_APELIDO AS DESCRICAO FROM USUARIO A ";
  //            
  if( opc == 0 ){            
    sql+="WHERE (A.USR_ATIVO='S')";  
    //////////////////////////////////////////////////////////////////////////////
    // localStorage eh o arquivo .php onde estao os select/insert/update/delete //
    //////////////////////////////////////////////////////////////////////////////
    var bdUsr=new clsBancoDados(localStorage.getItem('lsPathPhp'));
    bdUsr.Assoc=false;
    bdUsr.select( sql );
    if( bdUsr.retorno=='OK'){
      var jsUsrF10={
        "titulo":[
           {"id":0 ,"labelCol":"OPC"       ,"tipo":"chk"  ,"tamGrd":"5em"   ,"fieldType":"chk"}                                
          ,{"id":1 ,"labelCol":"CODIGO"    ,"tipo":"edt"  ,"tamGrd":"6em"   ,"fieldType":"int","formato":['i4'],"ordenaColuna":"S","align":"center"}
          ,{"id":2 ,"labelCol":"DESCRICAO" ,"tipo":"edt"  ,"tamGrd":"30em"  ,"fieldType":"str","ordenaColuna":"S"}
        ]
        ,"registros"      : bdUsr.dados             // Recebe um Json vindo da classe clsBancoDados
        ,"opcRegSeek"     : true                    // Opção para numero registros/botão/procurar                       
        ,"checarTags"     : "N"                     // Somente em tempo de desenvolvimento(olha as pricipais tags)
        ,"tbl"            : "tblUsr"                // Nome da table
        ,"prefixo"        : "usr"                   // Prefixo para elementos do HTML em jsTable2017.js
        ,"tabelaBD"       : "USUARIO"               // Nome da tabela no banco de dados  
        ,"width"          : "52em"                  // Tamanho da table
        ,"height"         : "37em"                  // Altura da table
        ,"indiceTable"    : "DESCRICAO"             // Indice inicial da table
      };
      if( objUsrF10 === undefined ){          
        objUsrF10         = new clsTable2017("objUsrF10");
        objUsrF10.tblF10  = true;
        if( foco != undefined ){
          objUsrF10.focoF10=foco;  
        };
      };  
      var html          = objUsrF10.montarHtmlCE2017(jsUsrF10);
      var ajudaF10      = new clsMensagem('Ajuda');
      ajudaF10.divHeight= '400px';  /* Altura container geral*/
      ajudaF10.divWidth = '42%';
      ajudaF10.tagH2    = false;
      ajudaF10.mensagem = html;
      ajudaF10.Show('ajudaUsr');
      document.getElementById('tblUsr').rows[0].cells[2].click();
      delete(ajudaF10);
      delete(objUsrF10);
    };
  }; 
  if( opc == 1 ){
    sql+=" WHERE (A.USR_CODIGO='"+document.getElementById(codUsr).value.toUpperCase()+"')"
        +"   AND (A.USR_ATIVO='S')";
    var bdUsr=new clsBancoDados(localStorage.getItem("lsPathPhp"));
    bdUsr.Assoc=true;
    bdUsr.select( sql );
    return bdUsr.dados;
  };     
};