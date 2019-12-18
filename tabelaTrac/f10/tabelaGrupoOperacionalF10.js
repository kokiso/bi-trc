////////////////////////////////////////////////////////////////////////////
// foco  - Onde vai o foco quando confirmar                               //
// jsPub[0].usr_grupos sao os grupos que o usuario pode ver               //
////////////////////////////////////////////////////////////////////////////
function fGrupoOperacionalF10(foco, codUni) {
  clsJs = jsString("lote");
  clsJs.add("rotina", "grupoOperacional");
  clsJs.add("login", jsPub[0].usr_login);
  clsJs.add("codUni", codUni);
  fd = new FormData();
  fd.append("veiculo", clsJs.fim());
  msg = requestPedido("Trac_Veiculo.php", fd);
  retPhp = JSON.parse(msg);
  if (retPhp[0].retorno == "OK") {
    retPhp[0].dados = converterObjeto(retPhp[0].dados);
    var jsGpoF10 = {
      titulo: [
        {
          id: 0,
          labelCol: "OPC",
          tipo: "chk",
          tamGrd: "5em",
          fieldType: "chk"
        },
        {
          id: 1,
          labelCol: "CODIGO",
          tipo: "edt",
          tamGrd: "6em",
          fieldType: "int",
          formato: ["i4"],
          ordenaColuna: "S",
          align: "center"
        },
        {
          id: 2,
          labelCol: "NOME",
          tipo: "edt",
          tamGrd: "30em",
          fieldType: "str",
          ordenaColuna: "S"
        }
      ],
      registros: retPhp[0].dados, // Recebe um Json vindo da classe clsBancoDados
      opcRegSeek: true, // Opção para numero registros/botão/procurar
      checarTags: "N", // Somente em tempo de desenvolvimento(olha as pricipais tags)
      tbl: "tblGpo", // Nome da table
      prefixo: "Gpo", // Prefixo para elementos do HTML em jsTable2017.js
      tabelaBD: "GRUPOOPERACIONAL", // Nome da tabela no banco de dados
      width: "52em", // Tamanho da table
      height: "37em", // Altura da table
      indiceTable: "NOME" // Indice inicial da table
    };
    if (objGpoF10 === undefined) {
      objGpoF10 = new clsTable2017("objGpoF10");
      objGpoF10.tblF10 = true;
      if (foco != undefined) {
        objGpoF10.focoF10 = foco;
      }
    }
    var html = objGpoF10.montarHtmlCE2017(jsGpoF10);
    var ajudaF10 = new clsMensagem("Ajuda");
    ajudaF10.divHeight = "400px"; /* Altura container geral*/
    ajudaF10.divWidth = "42%";
    ajudaF10.tagH2 = false;
    ajudaF10.mensagem = html;
    ajudaF10.Show("ajudaGpo");
    document.getElementById("tblGpo").rows[0].cells[2].click();
    delete ajudaF10;
    delete objGpoF10;
  }
}
