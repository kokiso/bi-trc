////////////////////////////////////////////////////////////////////////////
// opc=0 - Abre a janela F10                                              //
// opc=1 - Retorna somente o select para Janela F10 ou para blur do campo //
// foco  - Onde vai o foco quando confirmar                               //
////////////////////////////////////////////////////////////////////////////
function fUnidadeF10(opc,codUni,foco,todos){
  var sql="SELECT A.UNI_CODIGO AS CODIGO,A.UNI_NOME AS DESCRICAO,A.UNI_APELIDO AS APELIDO"
         +"       ,A.UNI_CODPOL AS POLO,G.GRP_APELIDO AS GRUPO" 
         +"  FROM UNIDADE A "
         +"  LEFT OUTER JOIN USUARIOUNIDADE UU ON A.UNI_CODIGO=UU.UU_CODUNI AND UU.UU_CODUSR="+jsPub[0].usr_codigo
         +"  LEFT OUTER JOIN POLO P ON A.UNI_CODPOL=P.POL_CODIGO"
         +"  LEFT OUTER JOIN GRUPO G ON P.POL_CODGRP=G.GRP_CODIGO"
  if( opc == 0 ){            
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // O parametro soAtivo mostra somente ativos="S" pois em Trac_UsuarioUnidade.php tenho que mostrar todos   //
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////
    if( todos=="soAtivo" ){
      sql+=" WHERE ((A.UNI_ATIVO='S') AND (COALESCE(UU.UU_ATIVO,'')='S'))"; 
    } else {
      sql+=" WHERE (A.UNI_ATIVO='S')";   
    }; 
    //////////////////////////////////////////////////////////////////////////////
    // localStorage eh o arquivo .php onde estao os select/insert/update/delete //
    //////////////////////////////////////////////////////////////////////////////
    var bdUni=new clsBancoDados(localStorage.getItem('lsPathPhp'));
    bdUni.Assoc=false;
    bdUni.select( sql );
    if( bdUni.retorno=='OK'){
      var jsUniF10={
        "titulo":[
           {"id":0 ,"labelCol":"OPC"       ,"tipo":"chk"  ,"tamGrd":"5em"   ,"fieldType":"chk"}                                
          ,{"id":1 ,"labelCol":"CODIGO"    ,"tipo":"edt"  ,"tamGrd":"6em"   ,"fieldType":"int","formato":['i4'],"ordenaColuna":"S","align":"center"}
          ,{"id":2 ,"labelCol":"DESCRICAO" ,"tipo":"edt"  ,"tamGrd":"28em"  ,"fieldType":"str","ordenaColuna":"S"}
          ,{"id":3 ,"labelCol":"APELIDO"   ,"tipo":"edt"  ,"tamGrd":"0em"   ,"fieldType":"str","ordenaColuna":"S"}
          ,{"id":4 ,"labelCol":"POLO"      ,"tipo":"edt"  ,"tamGrd":"0em"   ,"fieldType":"str","ordenaColuna":"N"}
          ,{"id":5 ,"labelCol":"GRUPO"     ,"tipo":"edt"  ,"tamGrd":"0em"   ,"fieldType":"str","ordenaColuna":"N"}
        ]
        ,"registros"      : bdUni.dados             // Recebe um Json vindo da classe clsBancoDados
        ,"opcRegSeek"     : true                    // Opção para numero registros/botão/procurar                       
        ,"checarTags"     : "N"                     // Somente em tempo de desenvolvimento(olha as pricipais tags)
        ,"tbl"            : "tblUni"                // Nome da table
        ,"prefixo"        : "Uni"                   // Prefixo para elementos do HTML em jsTable2017.js
        ,"tabelaBD"       : "UNIDADE"               // Nome da tabela no banco de dados  
        ,"width"          : "52em"                  // Tamanho da table
        ,"height"         : "37em"                  // Altura da table
        ,"indiceTable"    : "DESCRICAO"             // Indice inicial da table
      };
      if( objUniF10 === undefined ){          
        objUniF10         = new clsTable2017("objUniF10");
        objUniF10.tblF10  = true;
        if( foco != undefined ){
          objUniF10.focoF10=foco;  
        };
      };  
      var html          = objUniF10.montarHtmlCE2017(jsUniF10);
      var ajudaF10      = new clsMensagem('Ajuda');
      ajudaF10.divHeight= '400px';  /* Altura container geral*/
      ajudaF10.divWidth = '42%';
      ajudaF10.tagH2    = false;
      ajudaF10.mensagem = html;
      ajudaF10.Show('ajudaUni');
      document.getElementById('tblUni').rows[0].cells[2].click();
      delete(ajudaF10);
      delete(objUniF10);
    };
  }; 
  if( opc == 1 ){
    /*
    sql+=" WHERE (A.UNI_CODIGO='"+document.getElementById(codUni).value.toUpperCase()+"')"
        +"   AND (A.UNI_ATIVO='S')";
    */    
    if( todos=="soAtivo" ){    
      sql+=" WHERE ((A.UNI_CODIGO='"+document.getElementById(codUni).value.toUpperCase()+"')"
          +"   AND (A.UNI_ATIVO='S') AND (COALESCE(UU.UU_ATIVO,'')='S'))";
    } else {
      sql+=" WHERE ((A.UNI_CODIGO='"+document.getElementById(codUni).value.toUpperCase()+"') AND (A.UNI_ATIVO='S'))";
    }      
    var bdUni=new clsBancoDados(localStorage.getItem("lsPathPhp"));
    bdUni.Assoc=true;
    bdUni.select( sql );
    return bdUni.dados;
  };     
};