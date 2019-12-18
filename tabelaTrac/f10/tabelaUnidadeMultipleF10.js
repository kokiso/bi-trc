////////////////////////////////////////////////////////////////////////////
// foco  - Onde vai o foco quando confirmar                               //
////////////////////////////////////////////////////////////////////////////
function fUnidadeF10(caminho, tipo){
  clsJs = jsString("lote");
  clsJs.add("rotina", "selectUnidade");
  clsJs.add("login", jsPub[0].usr_login);
  clsJs.add("codUsr", jsPub[0].usr_codigo);
  fd = new FormData();
  fd.append(tipo, clsJs.fim());
  msg = requestPedido(caminho, fd);
  retPhp = JSON.parse(msg);
  if (retPhp[0].retorno == "OK") {
    retPhp[0].dados = converterObjeto(retPhp[0].dados);
    var jsUniF10={
      "titulo":[
         {"id":0 ,"labelCol":"OPC"       ,"tipo":"chk"  ,"tamGrd":"5em"   ,"fieldType":"chk"}                                
        ,{"id":1 ,"labelCol":"CODIGO"    ,"tipo":"edt"  ,"tamGrd":"6em"   ,"fieldType":"int","formato":['i4'],"ordenaColuna":"S","align":"center"}
        ,{"id":2 ,"labelCol":"DESCRICAO" ,"tipo":"edt"  ,"tamGrd":"28em"  ,"fieldType":"str","ordenaColuna":"S"}
        ,{"id":3 ,"labelCol":"APELIDO"   ,"tipo":"edt"  ,"tamGrd":"0em"   ,"fieldType":"str","ordenaColuna":"S"}
      ]
      ,"registros"      : retPhp[0].dados             // Recebe um Json vindo da classe clsBancoDados
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
      objUniF10         = new clsTable2017("objUniF10", true);
      objUniF10.tblF10  = true;

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