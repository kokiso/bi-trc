////////////////////////////////////////////////////////////////////////////
// opc=0 - Abre a janela F10                                              //
// opc=1 - Retorna somente o select para Janela F10 ou para blur do campo //
// foco  - Onde vai o foco quando confirmar                               //
////////////////////////////////////////////////////////////////////////////
function fVeiculoF10(codUni) {
  var sql =
    "SELECT A.VCL_CODIGO AS CODIGO,A.VCL_NOME AS DESCRICAO,A.VCL_ATIVO AS ATIVO" +
    "  FROM VEICULO A " +
    "  WHERE A.VCL_CODUNI=" +
    codUni +
    " AND A.VCL_ATIVO = 'S'";
  //////////////////////////////////////////////////////////////////////////////
  // localStorage eh o arquivo .php onde estao os select/insert/update/delete //
  //////////////////////////////////////////////////////////////////////////////
  var bdVcl = new clsBancoDados(localStorage.getItem("lsPathPhp"));
  bdVcl.Assoc = false;
  bdVcl.select(sql);
  if (bdVcl.retorno == "OK") {
    var jsVclF10 = {
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
          fieldType: "str",
          formato: ["i4"],
          ordenaColuna: "S",
          align: "center"
        },
        {
          id: 2,
          labelCol: "DESCRICAO",
          tipo: "edt",
          tamGrd: "28em",
          fieldType: "str",
          ordenaColuna: "S"
        }
      ],
      registros: bdVcl.dados, // Recebe um Json vindo da classe clsBancoDados
      opcRegSeek: true, // Opção para numero registros/botão/procurar
      checarTags: "N", // Somente em tempo de desenvolvimento(olha as pricipais tags)
      tbl: "tblVcl", // Nome da table
      prefixo: "Vcl", // Prefixo para elementos do HTML em jsTable2017.js
      tabelaBD: "VEICULO", // Nome da tabela no banco de dados
      width: "52em", // Tamanho da table
      height: "37em", // Altura da table
      indiceTable: "DESCRICAO" // Indice inicial da table
    };
    if (objVclF10 === undefined) {
      objVclF10 = new clsTable2017("objVclF10");
      objVclF10.tblF10 = true;
    }
    var html = objVclF10.montarHtmlCE2017(jsVclF10);
    var ajudaF10 = new clsMensagem("Ajuda");
    ajudaF10.divHeight = "400px"; /* Altura container geral*/
    ajudaF10.divWidth = "42%";
    ajudaF10.tagH2 = false;
    ajudaF10.mensagem = html;
    ajudaF10.Show("ajudaVcl");
    document.getElementById("tblVcl").rows[0].cells[2].click();
    delete ajudaF10;
    delete objVclF10;
  }
}
