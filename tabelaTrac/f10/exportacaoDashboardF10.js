function fexportarRelatorioF10(cards){
    var jsRelatorioF10={
      "titulo":[
          {"id":0 ,"labelCol":"OPC"       ,"tipo":"chk"  ,"tamGrd":"2em"   ,"fieldType":"chk", "align":"center"}                                
        ,{"id":1 ,"labelCol":"RELATORIO" ,"tipo":"edt"  ,"tamGrd":"31em"  ,"fieldType":"str","ordenaColuna":"S"}
      ]
      ,"registros"      : cards             // Recebe um Json vindo da classe clsBancoDados
      ,"opcRegSeek"     : true                    // Opção para numero registros/botão/procurar                       
      ,"checarTags"     : "N"                     // Somente em tempo de desenvolvimento(olha as pricipais tags)
      ,"tbl"            : "tblRelatorio"                // Nome da table
      ,"prefixo"        : "Rel"                   // Prefixo para elementos do HTML em jsTable2017.js
      ,"tabelaBD"       : "RELATORIO"               // Nome da tabela no banco de dados  
      ,"width"          : "41em"                  // Tamanho da table
      ,"height"         : "30em"                  // Altura da table
      ,"indiceTable"    : "RELATORIO"             // Indice inicial da table
    };
    if ( relatorioF10 === undefined ){          
      relatorioF10         = new clsTable2017("relatorioF10", true, true);
      relatorioF10.tblF10  = true;
    }
    var html          = relatorioF10.montarHtmlCE2017(jsRelatorioF10, true);
    var ajudaF10      = new clsMensagem('Ajuda');
    ajudaF10.divHeight= '450px';  /* Altura container geral*/
    ajudaF10.divWidth = '46%';
    ajudaF10.tagH2    = false;
    ajudaF10.mensagem = html;
    ajudaF10.Show('ajudaUni');
    document.getElementById('tblRelatorio').rows[0].cells[1].click();
    delete(ajudaF10);
    delete(relatorioF10);
};

function exportarRelatorioF10(relatorios, formato, filtros, caminho, tipo) {

  if (formato){

    
    var request = new XMLHttpRequest();
    request.open('POST', URL_EXPORTAR + caminho, true);
    request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    request.responseType = 'blob';
    
  request.onload = function() {
    if(request.status === 200) {
      var filename = tipo + formato;
      var blob = new Blob([request.response], { type: 'application/' + formato });
      var link = document.createElement('a');
      link.href = window.URL.createObjectURL(blob);
      link.download = filename;
      
      document.body.appendChild(link);
      
      link.click();
      
      document.body.removeChild(link);
    } else {
      alert('Ocorreu um erro');
    }
  };
  request.send(JSON.stringify({
    formato: formato,
    usr_apelido: jsPub[0].usr_apelido,
    usr_codigo: jsPub[0].usr_codigo,
    usr_email: jsPub[0].usr_email,
    filtros: filtros,
    reports: relatorios
  }));
}
}