////////////////////////////////////////////////////////////////////////////
// opc=0 - Abre a janela F10                                              //
// opc=1 - Retorna somente o select para Janela F10 ou para blur do campo //
// foco  - Onde vai o foco quando confirmar                               //
////////////////////////////////////////////////////////////////////////////
function fPoloF10(opc,codPol,foco){
  var sql="SELECT A.POL_CODIGO AS CODIGO,A.POL_NOME AS DESCRICAO"
         +"  FROM POLO A WHERE (A.POL_CODGRP IN"+jsPub[0].usr_grupos+")";
  if( opc == 0 ){            
    sql+=" AND (A.POL_ATIVO='S')";  
console.log(sql);    
    //////////////////////////////////////////////////////////////////////////////
    // localStorage eh o arquivo .php onde estao os select/insert/update/delete //
    //////////////////////////////////////////////////////////////////////////////
    var bdPol=new clsBancoDados(localStorage.getItem("lsPathPhp"));
    //
    //
    bdPol.Assoc=false;
    bdPol.select( sql );
    if( bdPol.retorno=='OK'){
      var jsPolF10={
        "titulo":[
           {"id":0 ,"labelCol":"OPC"       ,"tipo":"chk"  ,"tamGrd":"5em"   ,"fieldType":"chk"}                                
          ,{"id":1 ,"labelCol":"CODIGO"    ,"tipo":"edt"  ,"tamGrd":"6em"   ,"fieldType":"str","ordenaColuna":"S"}
          ,{"id":2 ,"labelCol":"DESCRICAO" ,"tipo":"edt"  ,"tamGrd":"30em"  ,"fieldType":"str","ordenaColuna":"S"}
        ]
        ,"registros"      : bdPol.dados             // Recebe um Json vindo da classe clsBancoDados
        ,"opcRegSeek"     : true                    // Opção para numero registros/botão/procurar                       
        ,"checarTags"     : "N"                     // Somente em tempo de desenvolvimento(olha as pricipais tags)
        ,"tbl"            : "tblPol"                // Nome da table
        ,"prefixo"        : "Pol"                   // Prefixo para elementos do HTML em jsTable2017.js
        ,"tabelaBD"       : "POLO"                 // Nome da tabela no banco de dados  
        ,"width"          : "52em"                  // Tamanho da table
        ,"height"         : "37em"                  // Altura da table
        ,"indiceTable"    : "DESCRICAO"             // Indice inicial da table
      };
      if( objPolF10 === undefined ){          
        objPolF10         = new clsTable2017("objPolF10");
        objPolF10.tblF10  = true;
        if( foco != undefined ){
          objPolF10.focoF10=foco;  
        };
      };  
      var html          = objPolF10.montarHtmlCE2017(jsPolF10);
      var ajudaF10      = new clsMensagem('Ajuda');
      ajudaF10.divHeight= '400px';  /* Altura container geral*/
      ajudaF10.divWidth = '42%';
      ajudaF10.tagH2    = false;
      ajudaF10.mensagem = html;
      ajudaF10.Show('ajudaPol');
      document.getElementById('tblPol').rows[0].cells[2].click();
      delete(ajudaF10);
      delete(objPolF10);
    };
  }; 
  if( opc == 1 ){
    sql+=" WHERE (A.POL_CODIGO='"+document.getElementById(codPol).value.toUpperCase()+"')"
        +"   AND (A.POL_ATIVO='S')";
    var bdPol=new clsBancoDados(localStorage.getItem("lsPathPhp"));
    bdPol.Assoc=true;
    bdPol.select( sql );
    return bdPol.dados;
  };     
};