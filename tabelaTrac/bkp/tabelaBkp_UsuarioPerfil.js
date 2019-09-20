function fBkp_UsuarioPerfil(whr,exc){
  "use strict";
  var clsJs;  // Classe responsavel por montar um Json e eviar PHP
  var fd;     // Formulario para envio de dados para o PHP
  var msg;    // Variavel para guardadar mensagens de retorno/erro 
  var retPhp; // Retorno do Php para a rotina chamadora  
  clsJs   = jsString("lote");  
  clsJs.add("rotina"      , "selectBkpUp"       );
  clsJs.add("login"       , jsPub[0].usr_login  );
  clsJs.add("where"       , whr                 );
  fd = new FormData();
  fd.append("bkp" , clsJs.fim());
  msg     = requestPedido("Trac_TabelasBkp.php",fd);   
  retPhp  = JSON.parse(msg);
  if( retPhp[0].retorno == "OK" ){
    //////////////////////////////////////////////////////////////////////////////////
    // O novo array não tem o campo idUnico mas a montarHtmlCE2017 ja foi executada //
    // Campo obrigatório se existir rotina de manutenção na table devido Json       //
    // Esta rotina não tem manutenção via classe clsTable2017                       //
    // jsCrv.registros=objCrv.addIdUnico(retPhp[0]["dados"]);                       //
    //////////////////////////////////////////////////////////////////////////////////
    var jsBkpUp={
      "titulo":[
         {"id":0  ,"labelCol":"ID"
                  ,"tamGrd":"5em"
                  ,"tamImp":"12"
                  ,"fieldType":"int"
                  ,"formato":['i6']
                  ,"ordenaColuna":"S"
                  ,"padrao":9}
        ,{"id":1  ,"labelCol":"DATA"
                  ,"tamGrd":"10em"
                  ,"tamImp":"16"
                  ,"fieldType":"dat"
                  ,"padrao":9}
        ,{"id":2  ,"labelCol":"ACAO"
                  ,"tamGrd":"4em"
                  ,"tamImp":"10"
                  ,"padrao":9}
        ,{"id":3  ,"labelCol":"CODIGO"
                  ,"tamGrd":"0em"
                  ,"tamImp":"0"
                  ,"fieldType":"int"
                  ,"formato":['i4']
                  ,"padrao":9}
        ,{"id":4  ,"labelCol":"DESCRICAO"
                  ,"tamGrd":"10em"
                  ,"tamImp":"30"
                  ,"padrao":9}
        ,{"id":5  ,"labelCol":"01","tamGrd":"1em","tamImp":"5","labelColImp":"01","padrao":9}
        ,{"id":6  ,"labelCol":"02","tamGrd":"1em","tamImp":"5","labelColImp":"02","padrao":9}
        ,{"id":7  ,"labelCol":"03","tamGrd":"1em","tamImp":"5","labelColImp":"03","padrao":9}        
        ,{"id":8  ,"labelCol":"04","tamGrd":"1em","tamImp":"5","labelColImp":"04","padrao":9}                  
        ,{"id":9  ,"labelCol":"05","tamGrd":"1em","tamImp":"5","labelColImp":"05","padrao":9} 
        ,{"id":10 ,"labelCol":"06","tamGrd":"1em","tamImp":"5","labelColImp":"06","padrao":9}
        ,{"id":11 ,"labelCol":"07","tamGrd":"1em","tamImp":"5","labelColImp":"07","padrao":9}
        ,{"id":12 ,"labelCol":"08","tamGrd":"1em","tamImp":"5","labelColImp":"08","padrao":9}        
        ,{"id":13 ,"labelCol":"09","tamGrd":"1em","tamImp":"5","labelColImp":"09","padrao":9}                  
        ,{"id":14 ,"labelCol":"10","tamGrd":"1em","tamImp":"5","labelColImp":"10","padrao":9} 
        ,{"id":15 ,"labelCol":"11","tamGrd":"1em","tamImp":"5","labelColImp":"11","padrao":9}
        ,{"id":16 ,"labelCol":"12","tamGrd":"1em","tamImp":"5","labelColImp":"12","padrao":9}
        ,{"id":17 ,"labelCol":"13","tamGrd":"1em","tamImp":"5","labelColImp":"13","padrao":9}        
        ,{"id":18 ,"labelCol":"14","tamGrd":"1em","tamImp":"5","labelColImp":"14","padrao":9}                  
        ,{"id":19 ,"labelCol":"15","tamGrd":"1em","tamImp":"5","labelColImp":"15","padrao":9} 
        ,{"id":20 ,"labelCol":"16","tamGrd":"1em","tamImp":"5","labelColImp":"16","padrao":9}
        ,{"id":21 ,"labelCol":"17","tamGrd":"1em","tamImp":"5","labelColImp":"17","padrao":9}
        ,{"id":22 ,"labelCol":"18","tamGrd":"1em","tamImp":"5","labelColImp":"18","padrao":9}        
        ,{"id":23 ,"labelCol":"19","tamGrd":"1em","tamImp":"5","labelColImp":"19","padrao":9}                  
        ,{"id":24 ,"labelCol":"20","tamGrd":"1em","tamImp":"5","labelColImp":"20","padrao":9} 
        ,{"id":25 ,"labelCol":"REG"
                  ,"tamGrd":"4em"
                  ,"tamImp":"10"
                  ,"padrao":9}
        ,{"id":26 ,"labelCol":"ATIVO"
                  ,"tamGrd":"4em"
                  ,"tamImp":"0"
                  ,"padrao":9}
        ,{"id":27 ,"labelCol":"USUARIO"
                  ,"tamGrd":"10em"
                  ,"tamImp":"0"
                  ,"classe":"escondeA"
                  ,"padrao":9}
      ]
      ,
      "botoesH":[
         {"texto":"Imprimir"  ,"name":"btnImprimir"   ,"onClick":"3"  ,"enabled":true ,"imagem":"fa fa-print" }
        ,{"texto":"Excel"     ,"name":"btnExcel"      ,"onClick":"5"  ,"enabled":true ,"imagem":"fa fa-file-excel-o" }         
        ,{"texto":"Fechar"    ,"name":"btnFechar"     ,"onClick":"6"  ,"enabled":true ,"imagem":"fa fa-close" }
      ]
      ,"registros"      : []                // Recebe um Json vindo da classe clsBancoDados
      ,"opcRegSeek"     : true              // Opção para numero registros/botão/procurar                     
      ,"checarTags"     : "S"               // Somente em tempo de desenvolvimento(olha as pricipais tags)                        
      ,"form"           : "espiao"          // Onde vai ser gerado o fieldSet             
      ,"divModal"       : "divTopoInicio"   // Onde vai se appendado abaixo deste a table 
      ,"tbl"            : "tblBkp"          // Nome da table
      ,"prefixo"        : "bkp"             // Prefixo para elementos do HTML em jsTable2017.js      
      ,"width"          : "75em"            // Tamanho da table
      ,"height"         : "53em"            // Altura da table
      ,"relTitulo"      : "ESPIAO - PERFIL" // Titulo do relatório
      ,"relOrientacao"  : "P"               // Paisagem ou retrato
      ,"relFonte"       : "8"               // Fonte do relatório
      ,"indiceTable"    : "ID"              // Indice inicial da table
      ,"tamBotao"       : "15"              // Tamanho botoes defalt 12 [12/25/50/75/100]
    }; 
    if( objBkpUp === undefined ){          
      objBkpUp=new clsTable2017("objBkpUp");
    };  
    objBkpUp.montarHtmlCE2017(jsBkpUp);
    /////////////////////////////////////
    // Enchendo a grade com registros  //   
    /////////////////////////////////////
    jsBkpUp.registros=objBkpUp.addIdUnico(retPhp[0]["dados"]);
    objBkpUp.montarBody2017();
    /////////////////////////////////////
    //   Marcando os campos alterados  //   
    /////////////////////////////////////
    if( exc.toUpperCase()=='N' )
      document.getElementById(jsBkpUp.tbl).colAlterada();
  };  
};