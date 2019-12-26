//******************************************************************************
//** SITE
//** Caracteres especiais http://www.w3schools.com/charsets/ref_utf_symbols.asp
//** http://guilhermemuller.com.br/tutoriais/formularios/2.html
//** https://css-tricks.com/float-labels-css/ - BOMMMMMMMMMMMMM
//** http://pt.stackoverflow.com/questions/13895/passar-lista-de-objetos-entre-arquivos - passar lista obj
//** https://www.youtube.com/watch?v=h8KgFB4nhL4&list=PLHO9UhS3tkPTvd2ZD86Kf69lXOQlLlfnX&index=32  curso js
//** http://desenvolvimentoparaweb.com/javascript/conhecimentos-essenciais-javascript-para-quem-ja-usa-jquery/ equivalencia Jquery/JavaScript
//** http://www.w3schools.com/js/tryit.asp?filename=tryjs_json_parse testar codigo
//** https://search.google.com/structured-data/testing-tool?hl=pt-BR Validar JSON
//***************************
//**APENAS PARA MENU OPÇÕES
//***************************
document.addEventListener(
  "DOMContentLoaded",
  function() {
    /*
     * Onde usado grafico esta é a definição de cores
     * http://erikasarti.net/html/tabela-cores/
     */
    if (document.getElementsByTagName("verGrafico") != undefined) {
      arrFill = [
        "rgba(143,188,143,0.5)",
        "rgba(151,187,205,0.5)",
        "rgba(0,206,209,0.5)",
        "rgba(160,82,45,0.5)",
        "rgba(205,133,63,0.5)",
        "rgba(46,139,87,0.5)",
        "rgba(100,149,237,0.5)",
        "rgba(95,158,160,0.5)",
        "rgba(205,92,92,0.5)",
        "rgba(143,188,143,0.5)",
        "rgba(151,187,205,0.5)"
      ];
      arrBorder = [
        "rgba(143,188,143,1)",
        "rgba(151,187,205,1)",
        "rgba(0,206,209,1)",
        "rgba(160,82,45,1)",
        "rgba(205,133,63,1)",
        "rgba(46,139,87,1)",
        "rgba(100,149,237,1)",
        "rgba(95,158,160,1)",
        "rgba(205,92,92,1)",
        "rgba(143,188,143,1)",
        "rgba(151,187,205,1)"
      ];
    }
    /*
     * Pelo usuario é disponibilizado a opção ADM para registros
     */
    if (document.getElementById("cbReg") != undefined) {
      if (jsPub[0].USU_ADMPUB == "ADM") {
        var ceOpt = document.createElement("option");
        ceOpt.value = "A";
        ceOpt.text = "ADMINISTRADOR";
        document.getElementById("cbReg").appendChild(ceOpt);
      }
    }
    /*
     */
    if (document.getElementsByClassName("moTituloMenu")[0] != undefined) {
      /*
       * Intervalo por navegador
       */
      var incCounter = jsPub[0].NAVEGADOR == "CHROME" ? 2 : 6;
      document
        .getElementsByClassName("moTituloMenu")[0]
        .addEventListener("click", function() {
          var els = this.querySelectorAll("li.moItemMenu");
          var elQI = this.querySelectorAll("span.moQtosItens")[0];
          elQI.innerHTML = els.length;

          //+6 Para poder fazer a ultima borda;
          var tamMax = els.length * (this.offsetHeight + 6);
          var elUL = this.getElementsByTagName("ul")[0]; //this = <li class="moTituloMenu">
          elUL.style.overflow = "hidden";
          if (elUL.style.display == "none" || elUL.style.display == "") {
            elUL.style.height = "0px";
            elUL.style.display = "block";
            //setInterval
            var counter = 0;
            var time = window.setInterval(function() {
              counter += incCounter;
              elUL.style.height = counter + "px";
              if (counter >= tamMax) {
                window.clearInterval(time);
              }
            }, 1);
            //
          } else {
            elUL.style.display = "none";
          }
        });
    }

    if (document.getElementsByClassName("sub-menuSmall")[0] != undefined) {
      var incCounter = jsPub[0].NAVEGADOR == "CHROME" ? 2 : 6;
      document
        .getElementsByClassName("moTituloMenu")[0]
        .addEventListener("click", function() {
          var els = this.querySelectorAll("li.moItemMenu");
          var elQI = this.querySelectorAll("span.moQtosItens")[0];
          elQI.innerHTML = els.length;

          //+6 Para poder fazer a ultima borda;
          var tamMax = els.length * (this.offsetHeight + 6);
          var elUL = this.getElementsByTagName("ul")[0]; //this = <li class="moTituloMenu">
          elUL.style.overflow = "hidden";
          if (elUL.style.display == "none" || elUL.style.display == "") {
            elUL.style.height = "0px";
            elUL.style.display = "block";
            //setInterval
            var counter = 0;
            var time = window.setInterval(function() {
              counter += incCounter;
              elUL.style.height = counter + "px";
              if (counter >= tamMax) {
                window.clearInterval(time);
              }
            }, 1);
            //
          } else {
            elUL.style.display = "none";
          }
        });
    }
  },
  false
);

function criarAncora(pAppenChild, pFoco) {
  var ceAnc = document.createElement("a");
  ceAnc.href = "#";
  ceAnc.id = "ancora";
  document.getElementById(pAppenChild).appendChild(ceAnc);
  window.location.href = "#ancora";
  document.getElementById("ancora").remove();
  //--
  document.getElementById(pFoco).select();
}
/////////////////////////////////////////////////
//                  Buscar CEP                 //
/////////////////////////////////////////////////
/*
function buscarCep(strCep){
  var ajax = new XMLHttpRequest();
  ajax.open('GET', "http://viacep.com.br/ws/"+strCep+"/json/", true);
  ajax.send();
  ajax.onreadystatechange = function() {
    if(ajax.readyState == 4 && ajax.status == 200) {
      var json = JSON.parse(ajax.responseText);							
      json.logradouro = removeAcentos( json.logradouro.toUpperCase() );
      json.bairro     = removeAcentos( json.bairro.toUpperCase() );
      json.localidade = removeAcentos( json.localidade.toUpperCase() );   //cidade     
      json.uf         = json.uf.toUpperCase();
      /////////////////////////////////////////////
      // trata o logradouro que veio no endereco //
      /////////////////////////////////////////////
      var tl = json.logradouro;
      if (tl.indexOf(" ")>0){
        tl = tl.substring(0,tl.indexOf(' ')).trim();
        json.logradouro = json.logradouro.replace(tl,"").trim();
        switch( tl ){
          case "ALA"      : tl="AL";  break;
          case "ALAM"     : tl="AL";  break;
          case "AVE"      : tl="AV";  break;
          case "AVENIDA"  : tl="AV";  break;
          case "COM"      : tl="RUA"; break;
          case "PCA"      : tl="PCA"; break;
          case "PÇA"      : tl="PCA"; break;
          case "PC"       : tl="PCA"; break;
          case "PRA"      : tl="PCA"; break;
          case "Q"        : tl="QD";  break;
          case "QUA"      : tl="QD";  break;
          case "R"        : tl="RUA"; break;
          case "TR"       : tl="TV";  break;
          case "TRA"      : tl="TV";  break;
          case "TV"       : tl="TV";  break;
          case "VIL"      : tl="VL";  break;
          case "VILA"     : tl="VL";  break;
        };
        json.endtl=tl;
        return json;
      };
    };
  };
};
*/
/////////////////////////////////////////////////
// Converte um json assoc em um json não assoc //
/////////////////////////////////////////////////
function jsonInArray(obj) {
  var b = new Array();
  for (var i in obj) {
    b.push([]);
    for (var x in obj[i]) {
      b[b.length - 1].push(obj[i][x]);
    }
  }
  return b;
}
/*
 * XMLHttpRequest
 */
function requestPedido(arquivo, formulario) {
  var retorno;
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      retorno = this.responseText;
    }
    if (this.status == 404) {
      var help = new clsMensagem("Retorno");
      help.mensagem = "URL NÃO LOCALIZADA!";
      help.Show();
      retorno = "erro";
    }
  };
  xhttp.open("POST", arquivo, false);
  xhttp.send(formulario);
  return retorno;
}
/*
 * Exemplo ERP_SatConsultaFin2017.php
 * data sempre no formato dd/mm/yyyy
 * Se o length for 6 é que estou passando uma competencia YYYYMM
 * data[4] quando recebe valor de um selcet yyyy-mm-dd
 * -retSomadias ( SB_TClienteGrupo.php )
 *   (1) var clsData=jsDatas('19/09/2017').retSomarDias(15);
 *       var ret=clsData.retYYYYMM();
 *   (2) var ret=jsDatas('21/09/2017').retSomarDias(30).retYYYYMM()
 *
 */
function jsDatas(data) {
  if (typeof data == "number") {
    if (data.toString().length != 6) {
      var hoje = new Date();
      hoje.setDate(hoje.getDate() + data);
      data =
        (hoje.getDate() < 10 ? "0" + hoje.getDate() : hoje.getDate()) +
        "/" +
        (hoje.getMonth() + 1 < 10
          ? "0" + (hoje.getMonth() + 1)
          : hoje.getMonth() + 1) +
        "/" +
        hoje.getFullYear();
    } else {
      data =
        "01/" +
        data.toString().substring(4, 6) +
        "/" +
        data.toString().substring(0, 4);
    }
  } else {
    /////////////////////////////////////////////////////////////////////////////////////////
    // Aqui é se vier o parametro "edtData" e não document.getElementById("edtData").value //
    /////////////////////////////////////////////////////////////////////////////////////////
    if (
      typeof data == "string" &&
      ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9"].indexOf(data[0]) == -1
    ) {
      var total = document.getElementsByTagName("input").length;
      for (var i = 0; i < total; i++) {
        if (document.getElementsByTagName("input")[i].id == data) {
          data = document.getElementById(data).value;
          break;
        }
      }
    }
    //////////////////////////////////////////
    // Se vier do banco de dados 2017-03-28 //
    //////////////////////////////////////////
    if (data[4] == "-") {
      var splt = data.split("-");
      data = splt[2] + "/" + splt[1] + "/" + splt[0];
    }
  }
  return {
    data: data,
    anoYY: function() {
      return this.data.substring(8, 10);
    },
    anoYYYY: function() {
      return this.data.substring(6, 10);
    },
    dia: function() {
      return this.data.substring(0, 2);
    },
    mes: function() {
      return this.data.substring(3, 5);
    },
    mesExt: function() {
      var locMes = this.data.substring(3, 5);
      return locMes == "01"
        ? "Janeiro"
        : locMes == "02"
        ? "Fevereiro"
        : locMes == "03"
        ? "Marco"
        : locMes == "04"
        ? "Abril"
        : locMes == "05"
        ? "Maio"
        : locMes == "06"
        ? "Junho"
        : locMes == "07"
        ? "Julho"
        : locMes == "08"
        ? "Agosto"
        : locMes == "09"
        ? "Setembro"
        : locMes == "10"
        ? "Outubro"
        : locMes == "11"
        ? "Novembro"
        : "Dezembro";
    },
    retDDMMYYYY: function() {
      return this.dia() + "/" + this.mes() + "/" + this.anoYYYY();
    },
    retDDMM: function() {
      return this.dia() + "/" + this.mes();
    },
    retExt: function(cidade) {
      return (
        cidade +
        ", " +
        this.dia() +
        " de " +
        this.mesExt() +
        " de " +
        this.anoYYYY()
      );
    },
    retMMDDYYYY: function() {
      return this.mes() + "/" + this.dia() + "/" + this.anoYYYY();
    },
    retYYYYtMMtDD: function() {
      return this.anoYYYY() + "-" + this.mes() + "-" + this.dia();
    }, // "t" = traço
    retMMMYY: function() {
      return (
        this.mesExt()
          .substring(0, 3)
          .toUpperCase() + this.anoYY()
      );
    },
    retMMMbYY: function() {
      return (
        this.mesExt()
          .substring(0, 3)
          .toUpperCase() +
        "/" +
        this.anoYY()
      );
    }, // "b" = Barra
    retPriDiaMes: function() {
      return "01/" + this.mes() + "/" + this.anoYYYY();
    },
    retSomarDias: function(n) {
      var sdData = new Date(this.anoYYYY(), this.mes() - 1, this.dia());
      sdData.setFullYear(
        sdData.getFullYear(),
        sdData.getMonth(),
        sdData.getDate() + n
      );
      this.data =
        sdData.getDate().EmZero(2) +
        "/" +
        (sdData.getMonth() + 1).EmZero(2) +
        "/" +
        sdData.getFullYear().EmZero(4);
      return this;
    },
    retUltDiaMes: function() {
      var udm = new Date(
        parseInt(this.anoYYYY()),
        parseInt(this.mes()),
        0
      ).getDate();
      return udm + "/" + this.mes() + "/" + this.anoYYYY();
    },
    retYYYYMM: function() {
      return this.anoYYYY() + this.mes();
    }
  };
}
/*
 */
/*
 * CHAMADA
 *
 * elemento html | document.getElementById('edtValor').value   = "12300,87"
 * elemento html | document.getElementById('edtInteiro').value = "123"
 * variavel      | fValor    = 12300.87
 * variavel      | sValor    = ["12300.87" / "12300,87]"
 * variavel      | iInteiro  = 123
 * variavel      | sInteiro  = "123"
 *
 * jsNmrs(document.getElementById('edtValor').value).dolar()         | retorno = [number] 12300.87
 * jsNmrs('edtValor').dolar()                                        | retorno = [number] 12300.87
 * jsNmrs(fValor).dolar()                                            | retorno = [number] 12300.87
 * jsNmrs(sValor).dolar()                                            | retorno = [number] 12300.87
 * jsNmrs().dolar(document.getElementById('edtValor').value)         | retorno = [number] 12300.87
 * jsNmrs().dolar('edtValor')                                        | retorno = [number] 12300.87
 * jsNmrs().dolar(fValor)                                            | retorno = [number] 12300.87
 * jsNmrs().dolar(sValor)                                            | retorno = [number] 12300.87
 *
 * jsNmrs(document.getElementById('edtValor').value).real()          | retorno = [string] "12300,87"
 * jsNmrs('edtValor').real()                                         | retorno = [string] "12300.87"
 * jsNmrs(fValor).real()                                             | retorno = [string] "12300.87"
 * jsNmrs(sValor).real()                                             | retorno = [string] "12300.87"
 * jsNmrs().real(document.getElementById('edtValor').value)          | retorno = [string] "12300.87"
 * jsNmrs().real('edtValor')                                         | retorno = [string] "12300.87"
 * jsNmrs().real(fValor)                                             | retorno = [string] "12300.87"
 * jsNmrs().real(sValor)                                             | retorno = [string] "12300.87"
 *
 * jsNmrs(document.getElementById('edtInteiro').value).inteiro()     | retorno = [number] 123
 * jsNmrs('edtInteiro').inteiro()                                    | retorno = [number] 123
 * jsNmrs(iInteiro).inteiro()                                        | retorno = [number] 123
 * jsNmrs(sInteiro).inteiro()                                        | retorno = [number] 123
 * jsNmrs().inteiro(document.getElementById('edtInteiro').value)     | retorno = [number] 123
 * jsNmrs().inteiro('edtInteiro')                                    | retorno = [number] 123
 * jsNmrs().inteiro(iInteiro)                                        | retorno = [number] 123
 * jsNmrs().inteiro(sInteiro)                                        | retorno = [number] 123
 *
 * jsNmrs(document.getElementById('edtInteiro').value).emZero(6)     | retorno = [string] "000123"
 * jsNmrs('edtInteiro').emZero(6)                                    | retorno = [string] "000123"
 *
 * jsNmrs(document.getElementById('edtValor').value).dec(1).dolar()  | retorno = [number] 12300.9
 * jsNmrs().dec(1).dolar(document.getElementById('edtValor').value)  | retorno = [number] 12300.9
 * jsNmrs().dec(1).dolar('edtValor')                                 | retorno = [number] 12300.9
 *
 * jsNmrs(document.getElementById('edtValor').value).dec(4).real()   | retorno = [string] "12300.8700"
 * jsNmrs().dec(4).real(document.getElementById('edtValor').value)   | retorno = [string] "12300.8700"
 * jsNmrs().dec(4).real('edtValor')                                  | retorno = [string] "12300.8700"
 *
 * jsNmrs('edtValor').dec(2).percentual(10).dolar()                  | retorno = [number] 1230.09
 * jsNmrs('edtValor').dec(2).percentual(10).real()                   | retorno = [string] "12300,87"
 * jsNmrs('edtValor').dec(4).percentual(10).real()                   | retorno = [string] "12300,8700"
 *
 * Outros exemplos usados ERP
 * jsNmrs(valor).divide(div).dec(4).dolar().ret();
 * jsNmrs(valor).soma(div).emZero(4).ret()
 * jsNmrs("edtVlrEvento").subtrai(document.getElementById("edtValorRetido").value).dolar().ret()
 * jsNmrs(vlrInf-vlrOrigem).abs().real().ret();
 */
function jsNmrs(data) {
  /*
   * retorno = É atualizado a cada chamada de um metodo, seu valor se alterar ex:percentual
   */
  var retorno = "";
  /*
   * retClasse = É o valor que vai ser retornado a classe, mas antes o retorno volta a ser igualado do data devido classe complementar
   * var foo = jsNmrs(valor)
   * var vlr = foo.percentual(10).dolar()  => nesta chamada o valor de retorno é alterado mas deve ser igualado com data para segunda chamada;
   */
  var retClasse = "";
  /*
   * Converte quando vem da classe principal jsNmrs(str) ou jsNmrs().dolar(str);
   * pubRetorno é para atualizar a variavel retorno,em caso de subtração/divisão...esta não pode ser alterada
   */
  function converte(n, pubRetorno) {
    if (n == undefined) {
      if (pubRetorno) {
        retorno = null;
      }
      return null;
    } else {
      switch (typeof n) {
        case "number":
          if (pubRetorno) {
            retorno = n;
          }
          return n;
        case "string":
          var total = document.getElementsByTagName("input").length;
          for (var i = 0; i < total; i++) {
            if (document.getElementsByTagName("input")[i].id == n) {
              if (pubRetorno) {
                retorno = document
                  .getElementById(n)
                  .value.replace(/[^0-9-.,]/g, "")
                  .replace(",", "."); //18nov2017 document.getElementById(n).value;
                return document.getElementById(n).value;
              } else {
                return document
                  .getElementById(n)
                  .value.replace(/[^0-9-.,]/g, "")
                  .replace(",", ".");
              }
            }
          }
          if (pubRetorno) {
            retorno = n;
            return n;
          } else {
            return n.replace(/[^0-9-.,]/g, "").replace(",", ".");
          }
      }
    }
  }
  /*
   * Iniciando a classe
   */
  return {
    data: converte(data, true),
    decimais: 2,
    dolar: function(obj) {
      if (obj != undefined) retorno = converte(obj, true);
      retorno = retorno.toString();
      retorno = retorno.replace(/[^0-9-.,]/g, "").replace(",", ".");
      var novaStr = "";
      for (var pos = 0; pos < retorno.length; pos++) {
        if (
          ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "-", "."].indexOf(
            retorno[pos]
          ) == -1
        ) {
          throw "Aceito apenas -0123456789.!";
        } else {
          novaStr += retorno[pos];
        }
      }
      retorno = novaStr;
      retorno = parseFloat(parseFloat(retorno).toFixed(this.decimais));
      return this;
    },
    abs: function() {
      retorno = retorno.toString();
      retorno = retorno.replace("-", "");
      return this;
    },
    divide: function(d) {
      d = converte(d, false);
      retorno = parseFloat(retorno) / d;
      return this;
    },
    soma: function(s) {
      s = converte(s, false);
      retorno = parseFloat(retorno) + parseFloat(s);
      return this;
    },
    subtrai: function(s) {
      s = converte(s, false);
      retorno = parseFloat(retorno) - s;
      return this;
    },
    multiplica: function(m) {
      m = converte(m, false);
      retorno = parseFloat(retorno) * m;
      return this;
    },
    real: function(obj) {
      if (obj != undefined) retorno = converte(obj, true);
      retorno = retorno.toString();
      retorno = retorno.replace(/[^0-9-.,]/g, "").replace(",", ".");
      var novaStr = "";
      for (var pos = 0; pos < retorno.length; pos++) {
        if (
          ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "-", "."].indexOf(
            retorno[pos]
          ) == -1
        ) {
          throw "Aceito apenas -0123456789,!";
        } else {
          novaStr += retorno[pos];
        }
      }
      retorno = novaStr;
      retorno = parseFloat(retorno)
        .toFixed(this.decimais)
        .replace(".", ",");
      return this;
    },
    inteiro: function(obj) {
      if (obj != undefined) retorno = converte(obj, true);

      retorno = retorno.toString();
      var novaStr = "";
      for (var pos = 0; pos < retorno.length; pos++) {
        if (
          ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "-"].indexOf(
            retorno[pos]
          ) == -1
        ) {
          throw "Aceito apenas -0123456789!";
        } else {
          novaStr += retorno[pos];
        }
      }
      retorno = novaStr;
      retorno = parseInt(retorno);
      return this;
    },
    /*
     * define o numero de casas decimais
     */
    dec: function(d) {
      this.decimais = d;
      return this;
    },
    /*
     * define o percentual aplicado em dolar ou real
     */
    percentual: function(p) {
      retorno = (parseFloat(retorno) * p) / 100; //Obrigatorio pois se vier aliquota 0 tem que retornar 0.00
      return this;
    },
    emZero: function(n) {
      if (typeof retorno == "number") retorno = retorno.toString();

      var novaStr = "";
      for (var pos = 0; pos < retorno.length; pos++) {
        if (
          ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "-"].indexOf(
            retorno[pos]
          ) != -1
        )
          novaStr += retorno[pos];
        else break;
      }
      retorno = novaStr;
      retorno =
        retorno == undefined || retorno.length == 0
          ? "0"
          : retorno.replace(/\D/g, "");
      var f1 = (retorno + "").length < n ? (retorno + "").length : n;
      retorno = new Array(++n - f1).join(0) + retorno;
      return this;
    },
    sepMilhar(c) {
      var n = retorno;
      var c = isNaN((c = Math.abs(c))) ? 2 : c;
      var s = n < 0 ? "-" : "";
      var i = parseInt((n = Math.abs(+n || 0).toFixed(c))) + "";
      var j = (j = i.length) > 3 ? j % 3 : 0;
      retorno =
        s +
        (j ? i.substr(0, j) + "." : "") +
        i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + ".") +
        (c
          ? "," +
            Math.abs(n - i)
              .toFixed(c)
              .slice(2)
          : "");
      return this;
    },
    ret: function() {
      retClasse = retorno;
      retorno = data; //retornando o valor origem para nova conta ou formatação
      return retClasse;
    }
  };
}

////////////////////////////////////
// Habilitar/Desabilitar um campo //
////////////////////////////////////
/*
function jsCmpAtivo(data){
  return {
    remove(str){
      document.getElementById(data).classList.remove(str); 
      return this;
    }
    ,add(str){
      document.getElementById(data).classList.add(str);      
      return this;
    }
    ,disabled(str){
      document.getElementById(data).disabled=str;
      return this;
    }
    ,foco(str){
      document.getElementById(str).foco();
      return this;
    }
  }  
};  
*/
function jsCmpAtivo(data) {
  return {
    remove(str) {
      let arr = str.split(" ");
      arr.forEach(function(reg) {
        document.getElementById(data).classList.remove(reg);
      });
      return this;
    },
    add(str) {
      let arr = str.split(" ");
      arr.forEach(function(reg) {
        document.getElementById(data).classList.add(reg);
      });
      return this;
    },
    disabled(str) {
      document.getElementById(data).disabled = str;
      return this;
    },
    foco(str) {
      document.getElementById(str).foco();
      return this;
    },
    cor(str) {
      document.getElementById(data).style.color = str;
      return this;
    }
  };
}
////////////////////////
// Formata uma string //
////////////////////////
function jsStr(data) {
  ////////////////////////////////////////////////////////////////////////////////////////////
  // retorno = É atualizado a cada chamada de um metodo, seu valor se alterar ex:percentual //
  ////////////////////////////////////////////////////////////////////////////////////////////
  var retorno = "";
  //////////////////////////////////////////////////////////////////////////////////
  // Converte quando vem da classe principal jsNmrs(str) ou jsNmrs().dolar(str);  //
  //////////////////////////////////////////////////////////////////////////////////
  function converte(n) {
    if (n == undefined) {
      n = "";
    }
    retorno = n;
    ///////////////////////////////////////////////////
    // Procurando se veio um element ou uma variavel //
    ///////////////////////////////////////////////////
    var total = document.getElementsByTagName("input").length;
    for (var i = 0; i < total; i++) {
      if (document.getElementsByTagName("input")[i].id == n) {
        retorno = document.getElementById(n).value;
      }
    }
    ////////////////////////////////////////////////////
    // Padrão da função tirar aspas e remover acentos //
    ////////////////////////////////////////////////////
    retorno = removeAcentos(retorno.replace(/'/g, "")); //remoceAcentos+tira aspas
    retorno = retorno.replace(/^\s+/, ""); //ltrim
    retorno = retorno.replace(/\s+$/, ""); //rtrim
    return retorno;
  }
  ////////////////////////
  // Iniciando a classe //
  ////////////////////////
  return {
    data: converte(data),
    alltrim() {
      retorno = retorno.split(" ").join("");
      return this;
    },
    upper() {
      retorno = retorno.toUpperCase();
      return this;
    },
    lower() {
      retorno = retorno.toLowerCase();
      return this;
    },
    soNumeros() {
      retorno = retorno.replace(/\D/g, "");
      return this;
    },
    tamMax(i) {
      if (retorno.length > i) {
        retorno = retorno.substring(0, i);
      }
      return this;
    },
    ret: function() {
      return retorno;
    }
  };
}
//
//Formatar campo data
function mascaraData(campoData, campoEvento) {
  if (campoEvento.keyCode >= 48 && campoEvento.keyCode <= 57) {
    var data = campoData.value;
    if (data.length == 2 || data.length == 5) {
      data = data + "/";
      campoData.value = data;
      return true;
    }
  }
  /*
   * Firefox não aceita keyCode
   */
}
//Formatar campo PRO_NCM
function mascaraNcm(campoData, campoEvento) {
  if (campoEvento.keyCode >= 48 && campoEvento.keyCode <= 57) {
    var data = campoData.value;
    if (data.length == 4 || data.length == 7) {
      data = data + ".";
      campoData.value = data;
      return true;
    }
  }
}
//Formatar campo CFOP
function mascaraCfop(campoData, campoEvento) {
  if (campoEvento.keyCode >= 48 && campoEvento.keyCode <= 57) {
    var data = campoData.value;
    if (data.length == 1) {
      data = data + ".";
      campoData.value = data;
      return true;
    }
  }
}
//Formatar campo HORA
function mascaraHora(campoData, campoEvento) {
  if (campoEvento.charCode >= 48 && campoEvento.charCode <= 57) {
    var data = campoData.value;
    if (data.length == 2) {
      data = data + ":";
      campoData.value = data;
      return true;
    }
  }
}

//Formatar campo CODCCU
function mascaraGerencial(campoData, campoEvento) {
  if (campoEvento.keyCode >= 48 && campoEvento.keyCode <= 57) {
    var data = campoData.value;
    if (
      data.length == 1 ||
      data.length == 4 ||
      data.length == 7 ||
      data.length == 10
    ) {
      data = data + ".";
      campoData.value = data;
      return true;
    }
  }
}
////////////////////////////////////
// Formatar campo somente inteiro //
////////////////////////////////////
function mascaraInteiro(inteiro) {
  //////////////////////////////////////
  // Firefox não aceita keyCode       //
  // Chrome aceita keyCode e charCode //
  //////////////////////////////////////
  switch (inteiro.charCode) {
    case 48:
    case 49:
    case 50:
    case 51:
    case 52:
    case 53:
    case 54:
    case 55:
    case 56:
    case 57:
      return true;
      break;
    case 0:
      return true;
      break;
    default:
      return false;
      break;
  }
}
//Retorna o maior ZIndex da pagina
function retornarZIndex() {
  var divs = document.getElementsByTagName("div");
  var di = divs.length;
  var maior = 0;
  for (var i = 0; i < di; i++) {
    if (maior <= divs[i].style.zIndex) maior = divs[i].style.zIndex + 1;
  }
  divs = document.getElementsByTagName("form");
  di = divs.length;
  for (var i = 0; i < di; i++) {
    if (maior <= divs[i].style.zIndex) maior = divs[i].style.zIndex + 1;
  }
  return maior;
}
//Adicionar linha em uma table
function criarTD(coluna, arrTD) {
  var divTd = "";
  var ceImg = "";
  var ceTd = document.createElement("td");
  ceTd.innerHTML = arrTD[0];
  if (arrTD[1] != "NAOCLASS") ceTd.setAttribute("class", arrTD[1]);
  //Opção livre para poder colocar qquer condição em uma celula
  //Opção 1 = Marca de vermelho valor negativo
  //Opção 2 = Coluna image para copia de documento
  //Opção 3 = Marca font do texto vermelho
  switch (arrTD[2]) {
    case "0":
      break;
    case "1":
      if (cmp.floatNA(arrTD[0]) < 0) ceTd.classList.add("corVermelho");
      break;

    case "2":
      ceTd.innerHTML = "";
      ceTd.setAttribute("class", "tdInput");
      divTd = document.createElement("div");
      divTd.setAttribute("width", "100%");
      divTd.setAttribute("height", "100%");

      ceImg = document.createElement("i");
      ceImg.setAttribute("class", "fa fa-print copiaDoc");
      ceImg.setAttribute("style", "margin-left:10px");
      divTd.appendChild(ceImg);
      divTd.setAttribute(
        "onclick",
        "copiaDocumento(this.parentNode.parentNode.cells[" +
          arrTD[0] +
          "].innerHTML);"
      );
      ceTd.appendChild(divTd);
      break;
    case "3":
      if (cmp.floatNA(arrTD[0]) < 0) ceTd.classList.add("fontVermelho");
      break;
    case "4":
      ceTd.classList.add("fontAzul");
      break;
  }
  arrTD[3].appendChild(ceTd);
}
//Retorno um JSON de uma table
function tableJson(tbl) {
  var el = "";
  var nl = 0;
  var nc = 0;
  var arrTit = [];
  var cntd = "";
  var retorno = "[";

  el = document
    .getElementById(tbl)
    .getElementsByTagName("thead")[0]
    .getElementsByTagName("th");
  nc = el.length;
  for (var col = 0; col < nc; col++)
    arrTit.push(el[col].innerHTML.toUpperCase());
  //
  el = document.getElementById(tbl).getElementsByTagName("tbody")[0];
  nl = el.rows.length;
  if (nl > 0) {
    nc = el.rows[nl - 1].cells.length;
    for (var lin = 0; lin < nl; lin++) {
      retorno += lin == 0 ? "{" : ",{";
      for (var col = 0; col < nc; col++) {
        cntd = el.rows[lin].cells[col].innerHTML;
        if (cntd.substring(0, 1) == "<") cntd = "";
        retorno +=
          (col == 0 ? "" : ",") + '"' + arrTit[col] + '":"' + cntd + '"';
      }
      retorno += "}";
    }
    retorno += "]";
    return retorno;
  } else {
    /* retornando um json com tam 0 exemplo ERP_SatConsultaFin.php */
    return "[]";
  }
}
//Coloca a impressão em tela
function mostrarImpressao(imp) {
  var formReport = "";
  var campoSql = "";
  formReport = document.createElement("form");
  formReport.setAttribute("name", "relatorio");
  formReport.setAttribute("id", "relatorio");
  formReport.setAttribute("method", "post");
  formReport.setAttribute("target", "_blank");
  formReport.setAttribute("action", "classPhp/imprimirsql.php");
  campoSql = document.createElement("input");
  campoSql.setAttribute("type", "hidden");
  campoSql.setAttribute("name", "sql");
  campoSql.setAttribute("id", "sql");
  campoSql.setAttribute("value", imp);
  formReport.appendChild(campoSql);
  document.body.appendChild(formReport);
  formReport.submit();
  formReport.remove();
}
//Function para marcar/desmarcar linhas checadas
function linhaChecada(chk, col) {
  var el = chk.parentNode.parentNode; //Leva do chk->td->tr
  el.classList.contains("corGradeParCheck")
    ? el.classList.remove("corGradeParCheck")
    : el.classList.add("corGradeParCheck");
}
//Função para escondes menu horizontal
function escondeMenu(obj) {
  var mostra;
  var el = obj;
  var ul = el.getElementsByTagName("li")[0];
  var itens = el.getElementsByClassName("escItemMenu");
  for (li = 0; li < itens.length; li++) {
    if (itens[li].classList.contains("mostra")) {
      itens[li].classList.remove("mostra");
      itens[li].classList.add("esconde");
      mostra = false;
    } else {
      itens[li].classList.remove("esconde");
      itens[li].classList.add("mostra");
      mostra = true;
    }
  }
  if (mostra == true) ul.style.display = "block";
  else ul.style.display = "none";
}
////////////////
// PROTOTYPES //
////////////////
HTMLElement.prototype.foco = function() {
  this.focus();
  this.select();
};
String.prototype.alltrim = function() {
  return this.split(" ").join("");
};
Number.prototype.EmZero = function(n) {
  f1 = (this + "").length < n ? (this + "").length : n;
  return (n = new Array(++n - f1).join(0) + this);
};
Number.prototype.sepNB = function(c) {
  /*  
    var n = this, c = isNaN(c = Math.abs(c)) ? 2 : c, d = d == undefined ? "," : d, t = t == undefined ? "." : t, s = n < 0 ? "-" : "", i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "", j = (j = i.length) > 3 ? j % 3 : 0;
       return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
    */
  var n = this;
  var c = isNaN((c = Math.abs(c))) ? 2 : c;
  var s = n < 0 ? "-" : "";
  var i = parseInt((n = Math.abs(+n || 0).toFixed(c))) + "";
  var j = (j = i.length) > 3 ? j % 3 : 0;
  return (
    s +
    (j ? i.substr(0, j) + "." : "") +
    i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + ".") +
    (c
      ? "," +
        Math.abs(n - i)
          .toFixed(c)
          .slice(2)
      : "")
  );
};
String.prototype.ltrim = function() {
  return this.replace(/^\s+/, "");
};
String.prototype.replaceAll = function(find, replacement) {
  return this.split(find).join(replacement);
};
String.prototype.rtrim = function() {
  return this.replace(/\s+$/, "");
};
String.prototype.soNumeros = function() {
  return this.replace(/\D/g, "");
};
//
String.prototype.tiraAspas = function() {
  return this.replace(/'/g, "");
};

//O IE não tem suporte ao remove, solução foi criar um prototype
if (!("remove" in Element.prototype)) {
  Element.prototype.remove = function() {
    this.parentNode.removeChild(this);
  };
}
///////////////////////////////////////
// Preenche com valores campos forms //
// str é o element container         //
///////////////////////////////////////
HTMLElement.prototype.newRecord = function(nrStr, nrFoco) {
  var total = this.getElementsByTagName("input").length;
  for (var i = 0; i < total; i++) {
    if (this.getElementsByTagName("input")[i].getAttribute(nrStr) != null) {
      switch (this.getElementsByTagName("input")[i].getAttribute(nrStr)) {
        case "nrHoje":
          this.getElementsByTagName("input")[i].value = jsDatas(
            0
          ).retDDMMYYYY();
          break;
        default:
          this.getElementsByTagName("input")[
            i
          ].value = this.getElementsByTagName("input")[i].getAttribute(nrStr);
          break;
      }
    }
  }
  if (nrFoco != undefined) document.getElementById(nrFoco).foco();
};
HTMLElement.prototype.formataEdtDireita = function() {
  var arr = this.getElementsByClassName("edtDireita");
  for (var ii = 0; ii < arr.length; ii++) {
    arr[ii].addEventListener("blur", function() {
      this.value = cmp.floatNB(this.value);
    });
  }
};
HTMLElement.prototype.insereRodape = function(rotina, cadastro) {
  if (rotina == "") {
    this.innerHTML =
      "Usuário:" + jsPub[0].DESUSU + "  |  Empresa:" + jsPub[0].EMP_APELIDO;
  } else {
    var ceBut, ceImg;
    var ceTable = document.createElement("table");
    ceTable.style.background =
      "-webkit-gradient(linear, left top, left bottom, color-stop(0%, #3093c7), color-stop(100%, #1c5a85))";
    ceTable.color = "white";
    ceTable.className = "divRodapeInicio";

    ceTable.style.width = "100%";
    ceTable.style.border = "1px solid silver";

    var ceThead = document.createElement("thead");
    var ceTr = document.createElement("tr");
    ceTr.style.height = "2em";
    ceTr.style.color = "white";

    if (cadastro != undefined) {
      var tam;
      tam = cadastro.length;
      for (var lin = 0; lin < tam; lin++) {
        var ceTd = document.createElement("td");
        ceTd.style.width = "10%";
        switch (cadastro[lin]) {
          case "SB_TCliente2017.php":
            ceBut = document.createElement("input");
            ceBut.className = "campo100 tableBotaoCad botaoForaTable";
            ceBut.type = "button";
            ceBut.id = "cadCli";
            ceBut.title =
              "Ajuda para consulta/manutenção em clientes/favorecidos";
            ceBut.value = "Favorecido";
            ceBut.addEventListener("click", function() {
              window.open("SB_TCliente2017.php");
            });
            break;
          case "SB_TBanco2017.php":
            ceBut = document.createElement("input");
            ceBut.className = "campo100 tableBotaoCad botaoForaTable";
            ceBut.type = "button";
            ceBut.id = "cadCli";
            ceBut.title = "Ajuda para consulta/manutenção em bancos";
            ceBut.value = "Banco";
            ceBut.addEventListener("click", function() {
              window.open("SB_TBanco2017.php");
            });
            break;
          case "SB_TServico2017.php":
            ceBut = document.createElement("input");
            ceBut.className = "campo100 tableBotaoCad botaoForaTable";
            ceBut.type = "button";
            ceBut.id = "cadSer";
            ceBut.title = "Ajuda para consulta/manutenção em serviços";
            ceBut.value = "Serviço";
            ceBut.addEventListener("click", function() {
              window.open("SB_TServico2017.php");
            });
            break;
          case "SB_TProduto2017.php":
            ceBut = document.createElement("input");
            ceBut.className = "campo100 tableBotaoCad botaoForaTable";
            ceBut.type = "button";
            ceBut.id = "cadPro";
            ceBut.title = "Ajuda para consulta/manutenção em produtos";
            ceBut.value = "Produto";
            ceBut.addEventListener("click", function() {
              window.open("SB_TProduto2017.php");
            });
            break;
          case "SB_TEmbalagem2017.php":
            ceBut = document.createElement("input");
            ceBut.className = "campo100 tableBotaoCad botaoForaTable";
            ceBut.type = "button";
            ceBut.id = "cadEmb";
            ceBut.title = "Ajuda para consulta/manutenção em embalagens";
            ceBut.value = "Embalagem";
            ceBut.addEventListener("click", function() {
              window.open("SB_TEmbalagem2017.php");
            });
            break;
          case "SB_TMesCompeten2017.php":
            ceBut = document.createElement("input");
            ceBut.className = "campo100 tableBotaoCad botaoForaTable";
            ceBut.type = "button";
            ceBut.id = "cadMc";
            ceBut.title = "Ajuda para consulta/manutenção em competências";
            ceBut.value = "Competência";
            ceBut.addEventListener("click", function() {
              window.open("SB_TMesCompeten2017.php");
            });
            break;
        }
        ceTd.appendChild(ceBut);
        ceTr.appendChild(ceTd);
      }
    }
    //////////////
    // Empresa  //
    //////////////
    var ceTd = document.createElement("td");
    ceTd.style.width = "20%";
    ceTd.style.textAlign = "center";
    ceTd.style.paddingRight = "0.5em";
    ceTd.style.borderRight = "1px solid silver";
    var ceContext = document.createTextNode("Empresa: " + jsPub[0].EMP_APELIDO);
    ceTd.appendChild(ceContext);
    ceTr.appendChild(ceTd);
    //////////////
    // Usuario  //
    //////////////
    ceTd = document.createElement("td");
    ceTd.style.width = "20%";
    ceTd.style.textAlign = "center";
    ceTd.style.paddingRight = "0.5em";
    ceTd.style.borderRight = "1px solid silver";
    ceContext = document.createTextNode("Usuario: " + jsPub[0].DESUSU);
    ceTd.appendChild(ceContext);
    ceTr.appendChild(ceTd);
    ////////////
    // Rotina //
    ////////////
    ceTd = document.createElement("td");
    ceTd.style.width = "20%";
    ceTd.style.textAlign = "center";
    ceContext = document.createTextNode("Rotina: " + rotina);
    ceTd.appendChild(ceContext);
    ceTr.appendChild(ceTd);
    ceThead.appendChild(ceTr);
    ceTable.appendChild(ceThead);
    this.appendChild(ceTable);
  }
};

/*
 * PROTOTYPES MENU - Insere itens no menu
 */
HTMLElement.prototype.insereItensMo = function(arr) {
  var ceLi = "";
  var ceAnc = "";
  var ceImg = "";
  var ceCtx = "";
  var strCtx = "";
  var strImg = "";
  var cont = arr.length;
  for (var lin = 0; lin < cont; lin++) {
    ceLi = document.createElement("li");
    ceLi.setAttribute("class", "moItemMenu");
    ceLi.setAttribute("id", "mu" + arr[lin]);
    switch (arr[lin]) {
      case "Ajuda":
        ceLi.addEventListener("click", function() {
          fAjuda();
        });
        strCtx = " Ajuda";
        strImg = "fa fa-info";
        break;
      /*  
                case 'Ativo':
                  ceLi.addEventListener('click',function(){ faltAtivo(); });    
                  strCtx=' Alterar->Status ativo/inativo';
                  strImg="fa fa-circle";
                  break;
                  
                case 'Detalhe':
                  ceLi.addEventListener('click',function(){ fDetalhe(); });
                  strCtx=' Detalhe registro';
                  strImg="fa fa-circle";
                  break;
                  
                case 'Espiao':
                  ceLi.addEventListener('click',function(){ fEspiao(); });    
                  strCtx=' Passo a passo do registro';
                  strImg="fa fa-circle";
                  break;
                case 'Excel':
                  ceLi.addEventListener('click',function(){ fExcel(); });    
                  strCtx=' Gerar excel';
                  strImg="fa fa-circle";
                  break;
                case 'Sis':
                  ceLi.addEventListener('click',function(){ faltSis(); });    
                  strCtx=' Alterar->Registro público/administrador';
                  strImg="fa fa-circle";
                  break;
                */
    }
    ceAnc = document.createElement("a");
    ceAnc.setAttribute("href", "#");

    ceImg = document.createElement("i");
    ceImg.setAttribute("class", strImg);
    ceAnc.appendChild(ceImg);

    ceCtx = document.createTextNode(strCtx);
    ceAnc.appendChild(ceCtx);
    ceLi.appendChild(ceAnc);
    this.appendChild(ceLi);
  }
  /*
   * Esconde menu ao sair
   */
  this.parentNode.addEventListener("mouseleave", function() {
    var popup = this.parentNode;
    popup.getElementsByTagName("ul")[0].style.display = "none";
  });
};
//*********************************************
//** PROTOTYPES TABLE
//*********************************************
//Para rotina espiao
//Pega todas as linhas e compara se a coluna atual é diferente da coluna anterior
HTMLElement.prototype.colAlterada = function(retornar) {
  var nl = this.rows.length; //** numero de linhas
  var nc = this.rows[nl - 1].cells.length; //** numero de colunas
  for (var li = 2; li < nl; li++) {
    for (var ci = 2; ci < nc; ci++) {
      if (
        this.rows[li].cells[ci].innerHTML !=
        this.rows[li - 1].cells[ci].innerHTML
      )
        this.rows[li].cells[ci].classList.add("corVermelho");
    }
  }
};
//Pega a coluna que contem o checkbox
HTMLElement.prototype.qualColuna = function(nc) {
  for (var ci = 0; ci < nc; ci++) {
    el = this.rows[1].cells[ci].children;
    if (el[0] != undefined && el[0].checked != undefined) {
      return ci;
      break;
    }
  }
};
//Pega uma coluna pelo titulo
HTMLElement.prototype.colunaTitulo = function(titulo) {
  //var nl     = this.rows.length;              /* numero de linhas */
  var nc = this.rows[0].cells.length; /* numero de colunas */
  for (var ci = 0; ci < nc; ci++) {
    if (this.rows[0].cells[ci].innerHTML == titulo) {
      return ci;
      break;
    }
  }
};
//////////////////////////////
// Inverte marca de checked //
//////////////////////////////
/*
HTMLElement.prototype.marcarDesmarcar = function(){
  var nl     = this.rows.length;              // numero de linhas
  var nc     = this.rows[nl-1].cells.length;  // numero de colunas
  var coluna = this.qualColuna(nc);           // achando a coluna que esta o checkbox
  ///////////////////////
  // Retirando a marca //
  ///////////////////////
  for(var li = 1; li < nl; li++){
    //////////////////////////////////
    // Inibindo as linhas filtradas //
    //////////////////////////////////
    if( this.rows[li].style.display=='none' )
      continue;
    //
    el=this.rows[li].cells[coluna].children;
    if (el[coluna].checked){
      el[coluna].checked=false;
      el[coluna].parentNode.parentNode.classList.remove('corGradeParCheck');
    } else {
      el[coluna].checked=true;
      el[coluna].parentNode.parentNode.classList.add('corGradeParCheck');
    }
  }
};
*/
// Remove as linhas de registros checked
HTMLElement.prototype.apagaChecados = function() {
  var nl = this.rows.length; // numero de linhas
  var nc = this.rows[nl - 1].cells.length; // numero de colunas
  var qtd = 0; // quantas linhas checadas
  var coluna = this.qualColuna(nc); // achando a coluna que esta o checkbox
  //Excluindo
  for (var li = nl - 1; li >= 1; li--) {
    el = this.rows[li].cells[coluna].children;
    /*
     * Este if é que tenho lugares onde quando adiciono uma linha esta vem sem o checked
     */
    if (el.length == 0) continue;
    /**/
    if (el[coluna].checked) {
      this.deleteRow(li);
      qtd++;
    }
  }
};
//Retira a marca de checked
HTMLElement.prototype.retiraChecked = function() {
  var nl = this.rows.length; // numero de linhas
  var nc = this.rows[nl - 1].cells.length; // numero de colunas
  var coluna = this.qualColuna(nc); // achando a coluna que esta o checkbox
  var el = "";
  for (var li = 1; li < nl; li++) {
    el = this.rows[li].cells[coluna].children;
    /*
     * Este if é que tenho lugares onde quando adiciono uma linha esta vem sem o checked
     */
    if (el.length == 0) continue;
    /**/
    if (el[coluna].checked) {
      el[coluna].checked = false;
      el[coluna].parentNode.parentNode.classList.remove("corGradeParCheck");
    }
  }
};
//Classe para ler(eq) e gravar(html) em uma table
var grade = function(str) {
  this.argStr = str;
  this.eq = function(n) {
    return this.argStr
      .parent()
      .parent()
      .find("td:eq(" + n + ")")
      .html();
  };
  this.html = function(n, s) {
    this.argStr
      .parent()
      .parent()
      .find("td:eq(" + n + ")")
      .html(s);
  };
};
//FUNÇÃO PARA FORMATAR CAMPOS PARA ENTRAR BD
function formatoBD(str, tipo) {
  var cmpfBD = new clsCampo(); // Classe para retornar campos  "cmpfBD" para não ter igual na chamadora
  switch (tipo) {
    case "str":
      return str == null ? null : "'" + str + "'";
      break;
    //case 'dat'  : return ( str == null ? null : '\''+cmpfBD.datMMDDYYYY(str,'var')+'\'' ) ;break;
    case "dat":
      return str == null ? null : "'" + jsDatas(str).retMMDDYYYY() + "'";
      break;
    case "flo":
      return "'" + cmpfBD.floatNA(str) + "'";
      break;
    case "flo2":
      return "'" + cmpfBD.floatNA(str) + "'";
      break;
    case "flo4":
      return "'" + cmpfBD.floatNA4(str) + "'";
      break;
    case "flo8":
      return "'" + cmpfBD.floatNA8(str) + "'";
      break;
    case "int":
      return str;
      break;
    default:
      console.log("Erro na função formatoBD");
      return "ERRO";
      break;
  }
  delete cmpfBD;
}
//
function gerarMensagemErro(parRotina, parMensagem, parCabec, parFoco) {
  if (tagValida(parRotina) == false) parRotina = "ERR";
  var cabec = tagValida(parCabec) ? parCabec : "Erro";
  var erro = new clsMensagem(cabec);
  erro.ListaErr(parMensagem);
  erro.Show(parRotina, parFoco);
  delete erro;
}
// ATUALIZA SOMENTE CAMPOS INFORMADOS( function clausulaUpdate atualiza todos os campos da grade )
// js     - JSon completo
// arrCmp - Vetor dos campos a serem atualizados pela coluna labelCol
// arrVlr - Vetor dos valores a serem atualizados
// arrSel - Vetor de todos os registros selecionados na table
function updateFields(ufJS, arrCmp, arrVlr, arrSel) {
  var ret = "";
  var sep = "";
  var sql = "";
  for (var regSel = 0; regSel < arrSel.length; regSel++) {
    var arr = [];
    /*
     * MONTANDO UM ARRAY SOMENTE COM OS CAMPOS QUE PRECISO PARA UPDATE
     * QUE VEM DE arrCmp + FK(s) no JSON
     * Primeiro pego o JSON depois os campos pois devem estar na mesma ordem dos vetores arrCmp e arrVlr
     */
    ufJS.titulo.forEach(function(tit) {
      if (tagValida(tit.pk) && tit.pk == "S")
        arr.push({
          obj: tit.obj,
          field: tit.field,
          fieldType: tit.fieldType,
          pk: tit.pk,
          labelCol: tit.labelCol
        });
    });
    for (li in arrCmp) {
      ufJS.titulo.forEach(function(tit) {
        if (arrCmp[li] == tit.labelCol)
          arr.push({
            obj: tit.obj,
            field: tit.field,
            fieldType: tit.fieldType,
            pk: tit.pk,
            labelCol: tit.labelCol
          });
      });
    }
    var where = []; // Guardando os valores para montar a clausula where
    var arrField = []; // Guardar campos do banco de dados
    var arrCntd = []; // Guardar conteudo para cada campo acima
    /*
     * a variavel arr tem os campos recebidos do parametro arrCmp + FK(s) no Json por isso preciso da
     * variavel colVlr pois os dois vetores sempre terão tamanhos diferentes devido FK(s)
     *
     * arr =
     * [
     *   {"field":"GUIA"       ,"fieldType":"int","pk":"S","labelC0l":"LANCTO"}
     *  ,{"field":"DATAPAGA"   ,"fieldType":"dat","labelC0l":"BAIXA"}
     *  ,{"field":"CODUSU"     ,"fieldType":"int","labelC0l":"CODUSU"}
     *  ,{"field":"PAG_CODEMP" ,"fieldType":"int","labelC0l":"EMP"}
     *  ]
     *
     * arrSel  [{"LANCTO":"001719","TP":"CP","CODCLI":"56","FAVORECIDO":"FLAVIO"...
     */
    var colVlr = 0;
    var conteudo = "";
    for (li = 0; (col = arr[li]); li++) {
      if (tagValida(col.pk) && col.pk == "S") {
        eval("conteudo=arrSel[" + regSel + "]." + col.labelCol);
        where.push(conteudo);
      } else {
        conteudo = arrVlr[colVlr];
        colVlr++;
        conteudo = formatoBD(conteudo, col.fieldType);
        arrField.push(col.field);
        arrCntd.push(conteudo);
      }
    }
    //
    sep = "";
    ret = "UPDATE " + ufJS.tabelaBD + " SET ";
    for (li in arrField) {
      ret += sep + arrField[li] + "=" + arrCntd[li];
      sep = ",";
    }
    ret += " " + clausulaWhere(ufJS, where);
    if (regSel == 0) {
      ret = '{"comando":"' + ret + '"}';
    } else {
      ret = ',{"comando":"' + ret + '"}';
    }
    sql += ret;
  }
  sql = '{"lote":[' + sql + "]}";
  return sql;
}
// Monta o update de TODOS  os campos da tabela, existe a updateFields(acima)
// que faz apenas de campos informados

// 22set2017 - voltei, porque a funcao é usada em vários lugares
function clausulaWhere(js, whr) {
  var arrW = [];
  var ret = "";
  var parte = 0;
  // MONTANDO UM ARRAY SOMENTE COM OS CAMPOS QUE PRECISO PARA WHERE
  js.titulo.forEach(function(tit) {
    if (tagValida(tit.pk) && tit.pk == "S") {
      // a variavel parte é para quando campo for combobox e a grade tiver descritivo diferente do conteudo
      parte = 0;
      if (tagValida(tit.tipo) && tit.tipo == "cb") {
        if (tagValida(tit.copy)) {
          parte = cmp.int(tit.copy[1], "arr");
        }
      }
      //
      arrW.push({ fieldType: tit.fieldType, field: tit.field, parte: parte });
    }
  });
  for (li = 0; (col = arrW[li]); li++) {
    if (col.parte > 0) whr[li] = whr[li].substring(0, col.parte);

    var conteudo = formatoBD(whr[li], col.fieldType);
    ret += (ret == "" ? " WHERE " : " AND ") + col.field + "=" + conteudo;
  }
  return ret;
}
/*
 * Usar funcao escape para ver codigo
 */
function removeAcentos(newStringComAcento) {
  var string = newStringComAcento;
  var mapaAcentosHex = {
    a: /[\xE0-\xE6]/g,
    A: /[\xC0-\xC6]/g,
    e: /[\xE8-\xEB]/g,
    E: /[\xC8-\xCB]/g,
    i: /[\xEC-\xEF]/g,
    I: /[\xCC-\xCF]/g,
    o: /[\xF2-\xF6]/g,
    O: /[\xD2-\xD6]/g,
    u: /[\xF9-\xFC]/g,
    U: /[\xD9-\xDC]/g,
    c: /\xE7/g,
    C: /\xC7/g,
    n: /\xF1/g,
    N: /\xD1/g
  };
  for (var letra in mapaAcentosHex) {
    var expressaoRegular = mapaAcentosHex[letra];
    string = string.replaceAll(expressaoRegular, letra);
  }
  string = string.replace('"', "");
  return string;
}
//
function filtrarReg(js, tbl, conteudo, lbl) {
  var col = 0;
  js.forEach(function(e) {
    if (e.labelCol == lbl) {
      col = e.id + 0;
      return false;
    }
  });
  var el = document.getElementById(tbl).getElementsByTagName("tbody")[0];
  var tam = el.rows.length;
  for (var lin = 0; lin < tam; lin++) {
    if (el.rows[lin].cells[col].innerHTML.indexOf(conteudo.toUpperCase()) < 0)
      el.rows[lin].style.display = "none";
    else el.rows[lin].style.display = "table-row";
  }
}
//
function tagValida(tag) {
  return tag !== undefined && tag !== "" ? true : false;
}

// CLASSE SELECT/ATUALIZAÇÃO BD
function clsBancoDados(url) {
  var self = this;
  self.metodo = "POST";
  self.url = url;
  self.xhttp = new XMLHttpRequest();
  self.sync = false;
  self.retorno = "";
  self.retPHP = "";
  self.dados = "";
  self.Assoc = true;
  self.retDefault = true; // Tratamento de erro é feito na chamada

  self.xhttp.onreadystatechange = function() {
    // Mostra que o a pagina foi localizada
    if (this.readyState == 4 && this.status == 200) {
      self.retPHP = this.responseText;
      //console.log(self.retPHP); ////////////////////////////////////////////////////////
      if (self.retDefault == true) {
        eval("tblRet=" + self.retPHP);
        self.retorno = tblRet[0].retorno;
        if (tblRet.length > 0 && tblRet[0].retorno == "OK") {
          self.dados = tblRet[0].dados;
        } else {
          var help = new clsMensagem("Retorno");
          help.mensagem = tblRet[0].dados;
          help.Show();
        }
      }
    }
    if (this.status == 404) {
      var help = new clsMensagem("Retorno");
      help.mensagem = "URL NÃO LOCALIZADA!";
      help.Show();
    }
  };
  //
  this.conecta = function(strEmp, strUsu, strSen) {
    self.xhttp.open(self.metodo, self.url, self.sync);
    self.xhttp.setRequestHeader(
      "Content-type",
      "application/x-www-form-urlencoded"
    );
    self.xhttp.send(
      "opcao=conectaBD&parEmpresa=" +
        strEmp +
        "&parUsuario=" +
        strUsu +
        "&parSenha=" +
        strSen
    );
  };
  this.select = function(sql) {
    self.xhttp.open(self.metodo, self.url, self.sync);
    // Esse fp troca o sinal "+" por ""
    form = new FormData();
    form.append("opcao", self.Assoc ? "selectAssoc" : "selectRow");
    form.append("sql", sql);
    self.xhttp.send(form);
  };
  this.execute = async function(js) {
    var xhttp = new XMLHttpRequest();
    xhttp.open(self.metodo, self.url, true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    exibeLoading();
    xhttp.send("opcao=executeSql&sql=" + js);

    var promise = new Promise(function(resolve, reject) {
      xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
          self.retPHP = this.responseText;
          if (self.retDefault == true) {
            eval("tblRet=" + self.retPHP);
            self.retorno = tblRet[0].retorno;
            if (tblRet.length > 0 && tblRet[0].retorno == "OK") {
              self.dados = tblRet[0].dados;
            } else {
              var help = new clsMensagem("Retorno");
              help.mensagem = tblRet[0].dados;
              help.Show();
            }
          }
          ocultaLoading();
          resolve();
        }
        if (this.status == 404) {
          var help = new clsMensagem("Retorno");
          help.mensagem = "URL NÃO LOCALIZADA!";
          help.Show();
          reject();
        }
      };
    });
    return promise;
  };

  this.cadtit = function(js) {
    self.xhttp.open(self.metodo, self.url, self.sync);
    self.xhttp.setRequestHeader(
      "Content-type",
      "application/x-www-form-urlencoded"
    );
    self.xhttp.send("opcao=cadtit&xml=" + js);
  };
}

function exibeLoading() {
  var x = document.getElementById("loading");
  var y = document.getElementById("loader");
  if (x && y) {
    x.classList.toggle("fade-out", false);
    x.classList.toggle("fade-in", true);
    y.classList.toggle("loader-out", false);
    y.classList.toggle("loader-in", true);
  }
}
function ocultaLoading() {
  var x = document.getElementById("loading");
  var y = document.getElementById("loader");
  if (x && y) {
    x.classList.toggle("fade-out", true);
    x.classList.toggle("fade-in", false);
    y.classList.toggle("loader-out", true);
    y.classList.toggle("loader-in", false);
  }
}
// CLASSE MENSAGEM
// CLASSE PARA EXIBER MENSAGEM DE ERRO E HELP
// O SAFARI não aceita dar um default no parametro cabec (cabec='Ajuda')
function clsMensagem(cabec) {
  cabec = tagValida(cabec) == true ? cabec : "Ajuda";
  var self = this;
  self.strLista = ""; // Pegar erros quando de validação de campos
  self.cabec = cabec;
  self.divTopo = "100px";
  self.divWidth = "40%";
  self.divHeight = "200px";
  self.tagH2 = true; // Opção para mostrar ou não a tag <h2>
  self.mensagem = "";
  //
  this.contido = function(campo, valor, arr) {
    ok = false;
    str = "";
    for (li in arr) {
      str += "/" + arr[li];
      if (valor == arr[li]) ok = true;
    }
    if (!ok) {
      str = str.replace("/", "");
      this.strLista +=
        "<tr><td>CAMPO <b>" + campo + "</b> ACEITA " + str + "!</td></tr>";
    }
  };
  this.dataValida = function(campo, valor) {
    var bits = valor.split("/");
    var d = new Date(bits[2], bits[1] - 1, bits[0]);
    if ((d && d.getMonth() + 1 == bits[1]) == false)
      this.strLista +=
        "<tr><td>CAMPO <b>" + campo + "</b> NÃO É UMA DATA VALIDA!</td></tr>";
  };
  //
  this.dataCompara = function(campo, dat1, dat2, condicao) {
    var d1 = parseInt(
      dat1.replace(/\D/g, "").replace(/(\d{2})(\d{2})(\d{4})/, "$3$2$1")
    );
    var d2 = parseInt(
      dat2.replace(/\D/g, "").replace(/(\d{2})(\d{2})(\d{4})/, "$3$2$1")
    );
    switch (condicao) {
      case "dataMaior":
        if (d1 <= d2)
          this.strLista +=
            "<tr><td>CAMPO <b>" +
            campo +
            "</b> " +
            dat1 +
            " DEVE SER MAIOR QUE " +
            dat2 +
            "</td></tr>";
        break;
      case "dataMaiorIgual":
        if (d1 < d2)
          this.strLista +=
            "<tr><td>CAMPO <b>" +
            campo +
            "</b> " +
            dat1 +
            " DEVE SER MAIOR OU IGUAL " +
            dat2 +
            "</td></tr>";
        break;
      case "dataDiferente":
        if (d1 == d2)
          this.strLista +=
            "<tr><td>CAMPO <b>" +
            campo +
            "</b> " +
            dat1 +
            " DEVE SER DIFERENTE DE " +
            dat2 +
            "</td></tr>";
        break;
      case "dataMenorIgual":
        if (d1 > d2)
          this.strLista +=
            "<tr><td>CAMPO <b>" +
            campo +
            "</b> " +
            dat1 +
            " DEVE SER MENOR OU IGUAL " +
            dat2 +
            "</td></tr>";
        break;
    }
  };
  //
  this.intCompara = function(campo, int1, int2, condicao) {
    var i1 = parseInt(int1.replace(/\D/g, ""));
    var i2 = parseInt(int2.replace(/\D/g, ""));
    switch (condicao) {
      case "intMenor":
        if (i1 >= i2)
          this.strLista +=
            "<tr><td>CAMPO <b>" +
            campo +
            "</b> " +
            int1 +
            " DEVE SER MENOR QUE " +
            int2 +
            "</td></tr>";
        break;
      case "intMenorIgual":
        if (i1 > i2)
          this.strLista +=
            "<tr><td>CAMPO <b>" +
            campo +
            "</b> " +
            int1 +
            " DEVE SER MENOR OU IGUAL " +
            int2 +
            "</td></tr>";
        break;
      case "intMaior":
        if (i1 <= i2)
          this.strLista +=
            "<tr><td>CAMPO <b>" +
            campo +
            "</b> " +
            int1 +
            " DEVE SER MAIOR QUE " +
            int2 +
            "</td></tr>";
        break;
      case "intMaiorIgual":
        if (i1 < i2)
          this.strLista +=
            "<tr><td>CAMPO <b>" +
            campo +
            "</b> " +
            int1 +
            " DEVE SER MAIOR OU IGUAL " +
            int2 +
            "</td></tr>";
        break;
      case "intDiferente":
        if (i1 == i2)
          this.strLista +=
            "<tr><td>CAMPO <b>" +
            campo +
            "</b> " +
            int1 +
            " DEVE SER DIFERENTE DE " +
            int2 +
            "</td></tr>";
        break;
    }
  };
  //
  this.digitosValidos = function(campo, valor, digitos) {
    var dig = digitos.split("|");
    var ret = "";
    for (li = 0; (letra = valor[li]); li++) {
      for (ld in dig) {
        if (letra == dig[ld]) ret += letra;
      }
    }
    if (ret != valor)
      this.strLista +=
        "<tr><td>CAMPO <b>" +
        campo +
        "</b> ACEITA APENAS <b>" +
        digitos +
        "</b>!</td></tr>";
  };
  //Os dois valores tem que ser diferentes(ex: transf codigo bancos)
  this.diferente = function(campo, valorI, valorF) {
    if (valorI == valorF)
      this.strLista +=
        "<tr><td>CAMPO <b>" +
        campo +
        "</b> VALORES[" +
        valorI +
        "/" +
        valorF +
        "] DEVE SER DIFERENTES!</td></tr>";
  };
  // Olha o direito de usuario antes de gravar
  this.direitoIgual = function(campo, valor, valido) {
    if (valor != valido)
      this.strLista +=
        "<tr><td>USUARIO SEM DIREITO PARA ESTA ROTINA!</td></tr>";
  };
  //
  this.floMaiorZero = function(campo, valor) {
    str = valor.replace(/[^0-9-.,]/g, "").replace(",", ".");
    if (str.length != valor.length || isNaN(parseFloat(str))) {
      this.strLista +=
        "<tr><td>CAMPO <b>" + campo + "</b> NÃO É UM FLOAT VALIDO!</td></tr>";
    } else {
      if (parseFloat(str) <= 0)
        this.strLista +=
          "<tr><td>CAMPO <b>" +
          campo +
          "</b> DEVE SER MAIOR QUE 0(ZERO)!</td></tr>";
    }
  };
  this.add = function(str) {
    this.strLista += "<tr><td>" + str + "</td></tr>";
  };
  //
  this.floMaiorIgualZero = function(campo, valor) {
    str = valor.replace(/[^0-9-.,]/g, "").replace(",", ".");
    if (str.length != valor.length || isNaN(parseFloat(str))) {
      this.strLista +=
        "<tr><td>CAMPO <b>" + campo + "</b> NÃO É UM FLOAT VALIDO!</td></tr>";
    } else {
      if (parseFloat(str) < 0)
        this.strLista +=
          "<tr><td>CAMPO <b>" +
          campo +
          "</b> DEVE SER MAIOR OU IGUAL 0(ZERO)!</td></tr>";
    }
  };
  //
  this.igual = function(campo, valor, valido) {
    if (valor != valido)
      this.strLista +=
        "<tr><td>CAMPO <b>" + campo + "</b> DEVE SER " + valido + "!</td></tr>";
  };
  //
  this.intMaiorZero = function(campo, valor) {
    //soNumeros é im prototype STRING
    if (typeof valor == "number") valor = valor.toFixed(0);
    //
    str = valor.soNumeros();
    if (campo === "GRUPO OPERACIONAL") {
      return 0;
    }
    if (str.length != valor.length) {
      this.strLista +=
        "<tr><td>CAMPO <b>" + campo + "</b> NÃO É UM INTEIRO VALIDO!</td></tr>";
    } else {
      if (parseInt(str) <= 0)
        this.strLista +=
          "<tr><td>CAMPO <b>" +
          campo +
          "</b> DEVE SER MAIOR QUE 0(ZERO)!</td></tr>";
    }
  };
  //
  this.intMaiorIgualZero = function(campo, valor) {
    str = valor.soNumeros();
    if (str.length != valor.length) {
      this.strLista +=
        "<tr><td>CAMPO <b>" + campo + "</b> NÃO É UM INTEIRO VALIDO!</td></tr>";
    } else {
      if (parseInt(str) < 0)
        this.strLista +=
          "<tr><td>CAMPO <b>" +
          campo +
          "</b> DEVE SER MAIOR OU IGUAL QUE 0(ZERO)!</td></tr>";
    }
  };
  //
  this.notNull = function(campo, valor) {
    try {
      valor = valor.replaceAll(" ", "");
    } catch (e) {
      throw new Error(campo + " " + e);
    }
    if (valor == "")
      this.strLista +=
        "<tr><td>CAMPO <b>" + campo + "</b> NÃO ACEITA VAZIO!</td></tr>";
  };
  //
  this.null = function(campo, valor) {
    if (valor != "")
      this.strLista +=
        "<tr><td>CAMPO <b>" + campo + "</b> DEVE SER VAZIO!</td></tr>";
  };
  //
  this.tamMin = function(campo, valor, tam) {
    if (valor.length < tam)
      this.strLista +=
        "<tr><td>CAMPO <b>" +
        campo +
        "</b> MINIMO DE " +
        tam +
        " CARACTER(es)!</td></tr>";
  };
  //
  this.tamMax = function(campo, valor, tam) {
    if (valor.length > tam)
      this.strLista +=
        "<tr><td>CAMPO <b>" +
        campo +
        "</b> MAXIMO DE " +
        tam +
        " CARACTER(es)!</td></tr>";
  };
  //
  this.tamFixo = function(campo, valor, tam) {
    if (valor.length != tam)
      this.strLista +=
        "<tr><td>CAMPO <b>" +
        campo +
        "</b> DEVE TER " +
        tam +
        " CARACTER(es)!</td></tr>";
  };
  //////////////////////////////////////////////////////////
  // O parametro "str" é para forçar uma mensagem de erro //
  // O SAFARI não aceita declarar o parametro (str='')    //
  //////////////////////////////////////////////////////////
  this.ListaErr = function(str) {
    str = tagValida(str) == true ? str : "";
    if (str != "") this.strLista = "<tr><td>" + str + "</td></tr>";
    if (this.strLista != "") {
      self.mensagem =
        '<table id="tabME" style="font-size:14px;">' +
        '<tr><td style="color:red;">Mensagem</td></tr>' +
        this.strLista +
        "</table>";
    }
    return this.strLista;
  };
  ////////////////////////////////////////////////////////////////////////////////////////////
  // O parametro cxMen(Caixa mensagem) é devido poder abrir duas caixas de dialogo na tela  //
  // Uma rotina outra gerada com um erro, o botão fechar deve ter este id                   //
  ////////////////////////////////////////////////////////////////////////////////////////////
  this.Show = function(cxMen, foco) {
    contMsg++;
    var divModal = "dm" + contMsg; // div modal
    var divMsg = "dms" + contMsg; // div mensagem
    var lblCls = "lcls" + contMsg; // label close
    var pObj = "obj" + contMsg; // Guardar a table quando for gerado pela classe
    var maior = retornarZIndex();
    var zModal = "z-index:" + (maior + 0); // div modal
    var zMsg = "z-index:" + (maior + 1); // div mensagem
    var zCls = "z-index:" + (maior + 2); // label close
    //
    str = "";
    str +=
      '<div id="' +
      divModal +
      '" class="divShowModal" style="' +
      zModal +
      '"></div>';
    str += '<div id="' + divMsg + '" ';
    str += 'style="left:0;right:0;margin-left:auto;margin-right:auto;';
    str +=
      "width:" +
      self.divWidth +
      ";height:" +
      self.divHeight +
      ";position:absolute;top:" +
      self.divTopo +
      ";" +
      zMsg +
      '">';
    str += '<div class="alertContainer" style="' + zCls + '">';
    str +=
      '<label id="' + lblCls + '" class="alertClose" for="alternar">X</label>';
    str += '<div class="alertMensagem">';
    if (self.tagH2) str += '<h2 class="alertH2">' + self.cabec + "</h2>";
    if (typeof self.mensagem == "string")
      str += '<p class="alertP">' + self.mensagem + "<p>";
    else str += '<div id="' + pObj + '"></div>';
    str += "</div>";
    str += "</div>";
    str += "</div>";
    str += "<script>";
    str += "</script>";
    document
      .getElementsByTagName("body")[0]
      .insertAdjacentHTML("afterbegin", str);
    document.getElementById(lblCls).addEventListener("click", function() {
      document.getElementById(divModal).remove();
      document.getElementById(divMsg).remove();
      if (foco != undefined) document.getElementById(foco).foco();
    });
    if (typeof self.mensagem == "object")
      document.getElementById(pObj).appendChild(self.mensagem);
  };
}
//***************************************************************************************
//** CLASSE RETORNA UM CAMPO OBJETO/JSON/ARRAY
//** Existe a opção de converter para maiuscula qdo estiver lendo um campo getElementById
//** Parametro tipo obj=Objeto/js=JSon/arr=Array/var=Variavel
//***************************************************************************************
function clsCampo() {
  var self = this;
  self.Maiuscula = "N";
  //Olha o direito de usuario antes de gravar
  this.direitoIgual = function(campo, valor, valido) {
    if (valor != valido)
      this.strLista +=
        "<tr><td>USUARIO SEM DIREITO PARA ESTA ROTINA!</td></tr>";
  };
  /*
   * FloatNA retorna um numeric americano com 2 casas decimais e "." ponto como separador de centavos
   */
  this.floatNA = function(nome) {
    if (typeof nome == "number") nome = nome.toFixed(2);
    nome = nome.replace(/[^0-9-.,]/g, "").replace(",", ".");
    nome = parseFloat(nome).toFixed(2);
    return parseFloat(nome) == "NaN" ? 0.0 : parseFloat(nome);
  };
  this.floatNA4 = function(nome) {
    if (typeof nome == "number") nome = nome.toFixed(4);
    nome = nome.replace(/[^0-9-.,]/g, "").replace(",", ".");
    nome = parseFloat(nome).toFixed(4);
    return parseFloat(nome) == "NaN" ? 0.0 : parseFloat(nome);
  };
  this.floatNA8 = function(nome) {
    if (typeof nome == "number") nome = nome.toFixed(8);
    nome = nome.replace(/[^0-9-.,]/g, "").replace(",", ".");
    nome = parseFloat(nome).toFixed(8);
    return parseFloat(nome) == "NaN" ? 0.0 : parseFloat(nome);
  };
  /*
   * FloatNB retorna uma string brasil com 2 casas decimais e "," virgunha como separador de centavos
   */
  this.floatNB = function(nome) {
    if (typeof nome == "string") {
      nome = nome.replace(/[^0-9-.,]/g, "").replace(",", ".");
      nome = parseFloat(nome).toFixed(2);
      return nome.replace(/[^0-9-.,]/g, "").replace(".", ",");
    }
    if (typeof nome == "number") {
      nome = nome.toFixed(2);
      nome = nome.replace(".", ",");
      return nome;
    }
  };
  this.floatNB4 = function(nome) {
    if (typeof nome == "string") {
      nome = nome.replace(/[^0-9-.,]/g, "").replace(",", ".");
      nome = parseFloat(nome).toFixed(4);
      return nome.replace(/[^0-9-.,]/g, "").replace(".", ",");
    }
    if (typeof nome == "number") {
      nome = nome.toFixed(4);
      nome = nome.replace(".", ",");
      return nome;
    }
  };
  this.floatNB8 = function(nome) {
    if (typeof nome == "string") {
      nome = nome.replace(/[^0-9-.,]/g, "").replace(",", ".");
      nome = parseFloat(nome).toFixed(8);
      return nome.replace(/[^0-9-.,]/g, "").replace(".", ",");
    }
    if (typeof nome == "number") {
      nome = nome.toFixed(8);
      nome = nome.replace(".", ",");
      return nome;
    }
  };
  //O SAFARI não aceita (tipo='obj')
  this.float = function(nome, decimais, tipo) {
    tipo = tagValida(tipo) == true ? tipo : "obj";
    if (tipo == "obj") {
      tmp = document.getElementById(nome).value;
    } else {
      tmp = nome;
    }
    if (typeof tmp == "number") tmp = tmp.toFixed(decimais);

    tmp = tmp.replace(/[^0-9-.,]/g, "").replace(",", ".");
    tmp = parseFloat(tmp).toFixed(decimais);
    return parseFloat(tmp) == "NaN" ? 0.0 : parseFloat(tmp);
  };
  //O SAFARI não aceita (tipo='obj')
  this.int = function(nome, tipo) {
    if (typeof nome == "number") nome = nome.toFixed(0);

    tipo = tagValida(tipo) == true ? tipo : "obj";
    if (tipo == "obj") {
      tmp = document.getElementById(nome).value.replace(/[^0-9-]/g, "");
    } else {
      tmp = nome.replace(/[^0-9-]/g, "");
    }
    return parseInt(tmp) == "NaN" ? 0 : parseInt(tmp);
  };
  //O SAFARI não aceita (tipo='obj')
  this.str = function(nome, tipo) {
    tipo = tagValida(tipo) == true ? tipo : "obj";
    if (tipo == "obj") {
      //Opção para converter obj em string
      if (self.Maiuscula == "S") {
        document.getElementById(nome).value = document
          .getElementById(nome)
          .value.toUpperCase();
      }
      tmp = document.getElementById(nome).value;
    } else {
      tmp = nome;
    }
    return tmp;
  };
}
//
function glossario() {
  return (
    "<p>Glossário:</p>" +
    "<table>" +
    "<tr>" +
    "<th>Campo</th><th>Valor</th><th>Descritivo</th>" +
    "</tr>" +
    "<tr>" +
    "<td>ATIVO</td><td>SIM</td><td>Registro pode ser referenciado nos cadastros do sistema.</td>" +
    "</tr>" +
    "<tr>" +
    '<td>ATIVO</td><td><font color="red">NAO</font></td><td><font color="red">Registro desabilitado para utilização.</font></td>' +
    "</tr>" +
    "<tr>" +
    "<td>REG</td><td>PUB</td><td>Registro público - manuseio do mesmo é permitido.</td>" +
    "</tr>" +
    "<tr>" +
    "<td>REG</td><td>ADM</td><td>Registro do administrador - somente o administrador poderá manusea-lo.</td>" +
    "</tr>" +
    "<tr>" +
    '<td>REG</td><td><font color="red">SIS</font></td><td><font color="red">Registro do sistema - não pode ser alterado e/ou excluído.</font></td>' +
    "</tr>" +
    "</table>" +
    "<span>A definição de <b>PUB/ADM</b> é estipulada pelo cadastro de usuário.</span>"
  );
}
/*
 */
function cxDialogo(js) {
  /*
   * Variaveis para modal
   */
  contMsg++;
  var divModal = "dm" + contMsg; // div modal
  var divMsg = "dms" + contMsg; // div mensagem
  var maior = retornarZIndex();
  /*
   * Create Elements
   */
  var ceModal = ""; //Div modal para marcar todo fundo como desabilitado
  var ceSec = "";
  var ceFrm = "";
  var cePar = "";
  var ceImg = "";
  var ceInp = "";
  var ceBut = "";
  var objImg = "";
  var objHnt = tagValida(js.hint) ? true : false;
  var objinpt = "";
  ////////////////////////////////////////////////////////////////
  // Div modal para marcar todo fundo de tela como desabilitado //
  ////////////////////////////////////////////////////////////////
  ceModal = document.createElement("div");
  ceModal.id = divModal;
  ceModal.className = "divShowModal";
  ceModal.style.zIndex = maior;
  ////////////////
  // Formulario //
  ////////////////
  ceFrm = document.createElement("form");
  ceFrm.className = "formulario";
  ceFrm.id = divMsg;
  ceFrm.style.top = js.top;
  ceFrm.style.left = js.left;
  ceFrm.style.width = js.width;
  ceFrm.style.position = "absolute";
  ceFrm.style.zIndex = maior + 2;
  /* titulo */
  cePar = document.createElement("p");
  cePar.className = "frmCampo";
  ceInp = document.createElement("input");
  ceInp.className = "informe";
  ceInp.type = "text";
  ceInp.name = "titulo";
  ceInp.value = js.titulo;
  ceInp.disabled = true;
  cePar.appendChild(ceInp);
  ceFrm.appendChild(cePar);
  /* Trazendo todos inputs */
  js.campos.forEach(function(c) {
    objinpt = c.type == "textarea" ? "textarea" : "input";
    /*
     * Quando textarea não usa imagem
     */
    if (objinpt == "input") objImg = tagValida(c.imagem) ? c.imagem : "fa-info";

    cePar = document.createElement("p");
    cePar.className = "frmCampo";
    ceInp = document.createElement(objinpt);
    switch (c.type) {
      case "date":
        ceInp.type = "text";
        break;
      case "text":
        ceInp.type = "text";
        break;
      case "textarea":
        ceInp.type = "textarea";
        ceInp.setAttribute("style", "width:100%");
        ceInp.rows = "12";
        ceInp.cols = "35";
        break;
      case "integer":
        ceInp.type = "text";
        break;
      case "password":
        ceInp.type = "password";
        break;
    }
    ceInp.name = c.name;
    ceInp.id = c.name;
    ceInp.placeholder = c.placeholder;
    ceInp.maxLength = c.maxlength;
    if (c.type == "integer")
      ceInp.onkeypress = function() {
        return mascaraInteiro(event);
      };
    if (c.type == "date")
      ceInp.onkeyup = function() {
        return mascaraData(this, event);
      };

    if (objHnt)
      ceInp.onfocus = function() {
        document.getElementById("hint").value = this.placeholder;
      };
    /*
     * Quando textarea não usa imagem
     */
    if (objinpt == "input") {
      ceImg = document.createElement("i");
      ceImg.className = "faIL " + objImg + " icon-large";
    }
    cePar.appendChild(ceInp);
    if (objinpt == "input") cePar.appendChild(ceImg);
    ceFrm.appendChild(cePar);
  });

  /* Botão direito */
  if (tagValida(js.botaoDireito) && js.botaoDireito == "s") {
    cePar = document.createElement("p");
    cePar.className = "botSupDir";
    ceBut = document.createElement("button");
    ceBut.className = "dir";
    ceBut.type = "button";
    ceBut.name = "dlgConfirmar";
    ceBut.id = "dlgConfirmar";
    ceBut.setAttribute("onclick", js.onClick);
    ceImg = document.createElement("i");
    ceImg.className = "faIL fa-arrow-right icon-large";
    ceBut.appendChild(ceImg);
    cePar.appendChild(ceBut);
    ceFrm.appendChild(cePar);
  }

  /* Botão esquerdo */
  if (tagValida(js.botaoEsquerdo)) {
    cePar = document.createElement("p");
    cePar.className = "botSupEsq";
    ceBut = document.createElement("button");
    ceBut.className = "esq";
    ceBut.type = "button";
    ceBut.name = "dlgCancelar";
    ceBut.id = "dlgCancelar";

    ceBut.addEventListener("click", function() {
      document.getElementById(divModal).remove();
      document.getElementById(divMsg).remove();
    });
    ceImg = document.createElement("i");
    ceImg.className = "faIL fa-close icon-large";
    ceBut.appendChild(ceImg);
    cePar.appendChild(ceBut);
    ceFrm.appendChild(cePar);
  }
  /* Hint */
  if (objHnt) {
    cePar = document.createElement("p");
    cePar.className = "frmCampo";
    ceInp = document.createElement("input");
    ceInp.className = "informe";
    ceInp.type = "text";
    ceInp.name = "hint";
    ceInp.id = "hint";
    ceInp.style.height = "1.7em";
    ceInp.value = "...";
    ceInp.disabled = true;
    ceImg = document.createElement("i");
    ceImg.className = "faIL fa-lightbulb-o icon-large";

    cePar.appendChild(ceInp);
    cePar.appendChild(ceImg);
    ceFrm.appendChild(cePar);
  }
  /* Levando para a pagina */
  /*
   * js.divModalFull para desabilitar todo fundo de tela, se não declarada no json ela vai ser acionada
   */
  if (!tagValida(js.divModalFull))
    document.getElementsByTagName("body")[0].appendChild(ceModal);
  document.getElementsByTagName("body")[0].appendChild(ceFrm);
}
///////////////////////////
// MONTA UM ARQUIVO JSON //
///////////////////////////
function jsString(tag) {
  return {
    tag: tag,
    primeiroCmp: "",
    master: true,
    retorno: "",
    add: function(cmp, vlr) {
      if (this.primeiroCmp == "") {
        this.retorno += "{";
        this.primeiroCmp = cmp;
      } else if (this.primeiroCmp == cmp) {
        this.retorno = this.retorno.substring(0, this.retorno.length - 1);
        this.retorno += "},{";
      }
      /////////////////////////////////////////////////////////////////////////////////
      // Se vier [{"campo":"valor",......}] é que é um json dentro do json principal //
      /////////////////////////////////////////////////////////////////////////////////
      if (vlr.toString().substring(0, 2) != "[{") {
        this.retorno += '"' + cmp + '":"' + vlr + '",';
      } else {
        this.retorno += '"' + cmp + '":' + vlr + ",";
      }
      return this;
    },
    principal: function(bol) {
      this.master = bol;
    },
    fim: function() {
      this.retorno = this.retorno.substring(0, this.retorno.length - 1) + "}";
      //////////////////////////////////////////////////////////////////
      // master true monta um json completo                        //
      // master false monta um json para estar dentro do principal //
      //////////////////////////////////////////////////////////////////
      if (this.master) {
        this.retorno = '{"' + tag + '":[' + this.retorno + "]}";
      } else {
        this.retorno = "[" + this.retorno + "]";
      }
      return this.retorno;
    }
  };
}
graficoPhp = function(js, dados) {
  var objJs = js.parametros;
  var ceDiv = "";
  ////////////////////////
  // Montando o grafico //
  ////////////////////////
  var dPaiGra = document.createElement("div");
  var dPaiGraE = document.createElement("canvas");
  dPaiGraE.id = "pieChart";
  dPaiGraE.style.cssFloat = "left";
  //dPaiGraE.width            = "300";
  dPaiGraE.width = objJs[0].widthGra == undefined ? 300 : objJs[0].widthGra;
  dPaiGraE.height = objJs[0].height - 100;
  dPaiGraE.style.marginLeft = "20px";

  var dPaiGraD = document.createElement("div");
  dPaiGraD.id = "pieLegend";
  dPaiGraD.style.cssFloat = "left";
  dPaiGraD.style.marginLeft = "30px";

  var ceAnc = document.createElement("a");
  ceAnc.name = "verGrafico";

  dPaiGra.appendChild(dPaiGraE);
  dPaiGra.appendChild(dPaiGraD);
  dPaiGra.appendChild(ceAnc);
  ////////////////////////////////////////////
  // Acumulando valores para gerar grafico  //
  ////////////////////////////////////////////
  /*
    var arrVlr  = this.graficoAcumula( objJs[0].campoTable
                                      ,objJs[0].campoVlr
                                      ,objJs[0].numQuebras); 
    */
  var arrVlr = dados[0]["dados"];
  var data = new Array();
  var arrGra = new Array();
  var valor = 0;
  var ctx = dPaiGraE.getContext("2d");
  switch (objJs[0].tipoGrafico) {
    case "pie":
      for (var lin = 0 in arrVlr) {
        data.push({
          value: arrVlr[lin]["VALOR"],
          color: arrFill[lin],
          label: arrVlr[lin]["CAMPO"]
        });
      }
      var myGra = new Chart(ctx).Pie(data);
      legend(dPaiGraD, data, myGra, true);
      break;
    case "bar":
      for (var lin = 0 in arrVlr) {
        arrGra.push({
          fillColor: arrFill[lin],
          strokeColor: arrBorder[lin],
          pointColor: "red",
          pointStrokeColor: "#fff",
          //"data"              : [( objJs[0].vlrAbs ? Math.abs(cmp.int(arrVlr[lin].VALOR,'js')) : cmp.int(arrVlr[lin].VALOR,'js') )],
          data: [arrVlr[lin]["VALOR"]],
          label: [arrVlr[lin]["CAMPO"]]
        });
      }
      var data = {
        labels: [""],
        datasets: arrGra
      };
      var myGra = new Chart(ctx).Bar(data);
      legend(dPaiGraD, data, myGra, true);
      break;

    case "lin":
      var data = objJs[0].dados;
      var myGra = new Chart(ctx).Line(data);
      legend(dPaiGraD, data, myGra, false);
      break;
  }

  ceDiv = document.createElement("div");
  ceDiv.style.height = objJs[0].height; //"400";
  ceDiv.style.overflowY = "auto";
  ceDiv.appendChild(dPaiGra);
  //////////////////////////////////////
  // Montando o formulario para table //
  // Variaveis para modal             //
  //////////////////////////////////////
  contMsg++;
  var divModal = "dm" + contMsg; // div modal
  var divMsg = "dms" + contMsg; // div mensagem
  var maior = retornarZIndex(); // maior indice para Z-index
  //////////////////////
  // Create Elements  //
  //////////////////////
  var ceModal = ""; //Div modal para marcar todo fundo como desabilitado
  var ceSec = "";
  var ceFrm = "";
  var cePar = "";
  var ceImg = "";
  var ceInp = "";
  var ceBut = "";
  var objImg = "";
  ceModal = document.createElement("div");
  ceModal.id = divModal;
  ceModal.className = "divShowModal";
  ceModal.style.zIndex = maior;
  // Formulario
  ceFrm = document.createElement("form");
  if (objJs[0].left == undefined) {
    ceFrm.className = "formulario center";
  } else {
    ceFrm.className = "formulario";
  }
  ceFrm.id = divMsg;
  ceFrm.style.top = "20px"; //detReg.top;
  if (objJs[0].left != undefined) {
    ceFrm.style.left = objJs[0].left;
  }
  ceFrm.style.width = objJs[0].width;
  ceFrm.style.height = "400px";

  ceFrm.style.position = "absolute";
  ceFrm.style.zIndex = maior + 2;
  // titulo
  cePar = document.createElement("p");
  cePar.className = "frmCampo";
  ceInp = document.createElement("input");
  ceInp.className = "informe";
  ceInp.type = "text";
  ceInp.name = "titulo";
  ceInp.value = objJs[0].titulo;
  ceInp.disabled = true;
  cePar.appendChild(ceInp);
  ceFrm.appendChild(cePar);
  ceFrm.appendChild(ceDiv);

  cePar = document.createElement("p");
  cePar.className = "botSupDir";
  ceBut = document.createElement("button");
  ceBut.className = "dir";
  ceBut.type = "button";
  ceBut.name = "dlgConfirmar";
  ceBut.id = "dlgConfirmar";
  ceBut.addEventListener("click", function() {
    document.getElementById(divModal).remove();
    document.getElementById(divMsg).remove();
  });

  ceImg = document.createElement("i");
  ceImg.className = "faIL fa-close icon-large";
  ceBut.appendChild(ceImg);
  cePar.appendChild(ceBut);
  ceFrm.appendChild(cePar);
  ////////////////////////////////////////////////////////////////////////////////////////////////////////
  // js.divModalFull para desabilitar todo fundo de tela, se não declarada no json ela vai ser acionada //
  ////////////////////////////////////////////////////////////////////////////////////////////////////////
  document.getElementsByTagName("body")[0].appendChild(ceModal);
  document.getElementsByTagName("body")[0].appendChild(ceFrm);
};
//////////////////////////////////////////////////////
// Retorna a posicao do elemento em relacao ao topo //
//////////////////////////////////////////////////////
function getPosicaoElemento(elemID) {
  var offsetTrail = document.getElementById(elemID);
  var offsetLeft = 0;
  var offsetTop = 0;
  while (offsetTrail) {
    offsetLeft += offsetTrail.offsetLeft;
    offsetTop += offsetTrail.offsetTop;
    offsetTrail = offsetTrail.offsetParent; //buscando o parent ateh chegar no body
  }
  if (
    navigator.userAgent.indexOf("Mac") != -1 &&
    typeof document.body.leftMargin != "undefined"
  ) {
    offsetLeft += document.body.leftMargin;
    offsetTop += document.body.topMargin;
  }
  return { left: offsetLeft, top: offsetTop };
}

function fncCasaDecimal(obj, dec) {
  document.getElementById(obj.id).value = jsNmrs(obj.id)
    .dec(dec)
    .real()
    .ret();
}

function criarEl(elem, attr, app) {
  if (typeof elem === undefined) {
    return false;
  }
  var el = document.createElement(elem);
  if (typeof attr === "object") {
    for (var key in attr) {
      el.setAttribute(key, attr[key]);
    }
  }
  if (typeof app === "object") {
    if (app.textNode != undefined) {
      var cn = document.createTextNode(app.textNode);
      el.appendChild(cn);
    }

    if (app.appChild != null) {
      document.getElementById(app.appChild).appendChild(el);
      return true;
    }
  }
  return el;
}

function fncExcel(divCe, novoPadrao) {
  //////////////////////////////////////////////////////////////////
  // Aqui colocados valores padroes para tela de importacao excel //
  //////////////////////////////////////////////////////////////////
  var padrao = { widthDivE: "89.8em" };
  //
  ///////////////////////////////////////////
  // Aqui pode ser alterado qualquer valor //
  ///////////////////////////////////////////
  if (novoPadrao != undefined && typeof novoPadrao == "object") {
    if (novoPadrao.widthDivE != undefined)
      padrao.widthDivE = novoPadrao.widthDivE;
  }
  //
  //
  criarEl(
    "div",
    {
      id: "divTopoInicioE",
      class: "divTopoInicio",
      style: "width:" + padrao.widthDivE + ";"
    },
    { appChild: divCe }
  );

  criarEl(
    "div",
    {
      id: "divCampo",
      class: "campotexto campo100"
    },
    { appChild: "divTopoInicioE" }
  );

  criarEl(
    "div",
    {
      id: "divInput",
      class: "campo85",
      style: "float:left;"
    },
    { appChild: "divCampo" }
  );

  criarEl(
    "input",
    {
      id: "edtArquivo",
      class: "campo_file input",
      name: "edtArquivo",
      type: "file",
      style: "float:left;"
    },
    { appChild: "divInput" }
  );

  criarEl(
    "label",
    {
      id: "lblArquivo",
      class: "campo_label",
      for: "edtArquivo"
    },
    { appChild: "divInput", textNode: "Arquivo" }
  );

  criarEl(
    "div",
    {
      id: "divBtn",
      class: "campo15",
      style: "float:left;"
    },
    { appChild: "divCampo" }
  );

  criarEl(
    "input",
    {
      id: "btnAbrirExcel",
      onClick: "btnAbrirExcelClick();",
      type: "button",
      value: "Abrir",
      class: "campo100 tableBotao botaoForaTable",
      style: "height:3.6em !important;"
    },
    { appChild: "divBtn" }
  );

  criarEl(
    "div",
    {
      id: "xmlModal",
      class: "divShowModal",
      style: "display:none;"
    },
    { appChild: divCe }
  );

  criarEl(
    "div",
    {
      id: "divErr",
      class: "conteudo",
      style: "display:block;overflow-x:auto;"
    },
    { appChild: divCe }
  );

  criarEl(
    "form",
    {
      id: "frmExc",
      class: "center",
      method: "post",
      name: "frmExc",
      action: "imprimirsql.php",
      target: "_newpage"
    },
    { appChild: "divErr" }
  );

  criarEl(
    "input",
    {
      id: "sql",
      name: "sql",
      type: "hidden"
    },
    { appChild: "frmExc" }
  );

  criarEl(
    "div",
    {
      id: "tabelaExc",
      class: "center active",
      style: "position:fixed;top:10em;width:90em;z-index:30;display:none;"
    },
    { appChild: "frmExc" }
  );
}
//Reverte dmhs em segundos
function segundosRev(parStr) {
  var splt = parStr.split(":");
  var ret = 0;
  if (splt.length == 4)
    return (
      parseInt(splt[0]) * 86400 +
      parseInt(splt[1]) * 3600 +
      parseInt(splt[2]) * 60 +
      parseInt(splt[0])
    );
  if (splt.length == 4)
    return (
      parseInt(splt[1]) * 3600 + parseInt(splt[2]) * 60 + parseInt(splt[0])
    );
}

function segundosEm(parSeg, parRet) {
  ////////////////////////////////
  // Um dia tem 86400 segundos  //
  // Uma hora tem 3600 segundos //
  // Um minuto tem 60 segundos  //
  ////////////////////////////////
  var iDia = 0;
  var iHor = 0;
  var iMin = 0;
  var iSeg = 0;

  var sDia = "00";
  switch (parRet) {
    case "dddhms":
      var sDia = "000";
      break;
    case "ddddhms":
      var sDia = "0000";
      break;
  }

  var sHor = "00";
  var sMin = "00";
  var sSeg = "00";

  var iSeg = parSeg;
  if (iSeg >= 86400) {
    iDia = parseInt(parseInt(iSeg) / 86400);
    iSeg = iSeg - iDia * 86400;
    sDia = (100 + iDia).toString().substr(1, 3); // convertendo para "99"
    if (parRet == "dddhms") {
      sDia = (1000 + iDia).toString().substr(1, 4); // convertendo para "99"
    }
    if (parRet == "ddddhms") {
      sDia = (10000 + iDia).toString().substr(1, 5); // convertendo para "99"
    }
  }

  if (iSeg >= 3600) {
    iHor = parseInt(parseInt(iSeg) / 3600);
    iSeg = iSeg - iHor * 3600;
    sHor = (100 + iHor).toString().substr(1, 3); // convertendo para "99"
  }

  if (iSeg >= 60) {
    iMin = parseInt(parseInt(iSeg) / 60);
    sMin = (100 + iMin).toString().substr(1, 3); // convertendo para "99"
    iSeg = iSeg - iMin * 60;
    sSeg = (100 + iSeg).toString().substr(1, 3); // convertendo para "99"
  }
  switch (parRet) {
    case "%ddhms":
      return sDia + "d:" + sHor + "h:" + sMin + "m:" + sSeg + "s";
      break;
    case "%hm,s":
      return sHor + "h:" + sMin + "m:" + sSeg + "s";
      break;
    case "%ms":
      return sMin + "m:" + sSeg + "s";
      break;
    case "ddhms":
      return sDia + ":" + sHor + ":" + sMin + ":" + sSeg;
      break;
    case "dddhms":
      return sDia + ":" + sHor + ":" + sMin + ":" + sSeg;
      break;
    case "ddddhms":
      return sDia + ":" + sHor + ":" + sMin + ":" + sSeg;
      break;
    case "hms":
      return sHor + ":" + sMin + ":" + sSeg;
      break;
    case "ms":
      return sMin + ":" + sSeg;
      break;
  }
}

function comboCompetencia(qual, el) {
  var meses = [];
  var tam = 0;
  var ceOpt, ceContext;
  if (qual == "YYYYMM_MMM/YY") {
    meses.push({ valor: "201805", texto: "MAI/18" });
    meses.push({ valor: "201806", texto: "JUN/18" });
    meses.push({ valor: "201807", texto: "JUL/18" });
    meses.push({ valor: "201808", texto: "AGO/18" });
    meses.push({ valor: "201809", texto: "SET/18" });
    meses.push({ valor: "201810", texto: "OUT/18" });
    meses.push({ valor: "201811", texto: "NOV/18" });
    meses.push({ valor: "201812", texto: "DEZ/18" });
    meses.push({ valor: "201901", texto: "JAN/19" });
    meses.push({ valor: "201902", texto: "FEV/19" });
    meses.push({ valor: "201903", texto: "MAR/19" });
    meses.push({ valor: "201904", texto: "ABR/19" });
    meses.push({ valor: "201905", texto: "MAI/19" });
    meses.push({ valor: "201906", texto: "JUN/19" });
    meses.push({ valor: "201907", texto: "JUL/19" });
    meses.push({ valor: "201908", texto: "AGO/19" });
    meses.push({ valor: "201909", texto: "SET/19" });
    meses.push({ valor: "201910", texto: "OUT/19" });
  }
  if (qual == "classif_mot") {
    meses.push({ valor: "201805|201805", texto: "MAI/18" });
    meses.push({ valor: "201806|201805", texto: "JUN/18" });
    meses.push({ valor: "201807|201805", texto: "JUL/18" });
    meses.push({ valor: "201808|201805", texto: "AGO/18" });
    meses.push({ valor: "201809|201805", texto: "SET/18" });
    meses.push({ valor: "201810|201805", texto: "OUT/18" });
    meses.push({ valor: "201811|201805", texto: "NOV/18" });
    meses.push({ valor: "201812|201805", texto: "DEZ/18" });
    meses.push({ valor: "201901|201805", texto: "JAN/19" });
    meses.push({ valor: "201902|201805", texto: "FEV/19" });
    meses.push({ valor: "201903|201805", texto: "MAR/19" });
    meses.push({ valor: "201904|201805", texto: "ABR/19" });
    meses.push({ valor: "201905|201805", texto: "MAI/19" });
    meses.push({ valor: "201906|201805", texto: "JUN/19" });
    meses.push({ valor: "201907|201805", texto: "JUL/19" });
    meses.push({ valor: "201908|201805", texto: "AGO/19" });
    meses.push({ valor: "201909|201805", texto: "SET/19" });
    meses.push({ valor: "201910|201805", texto: "OUT/19" });
  }
  tam = meses.length;
  if (tam > 0) {
    for (var lin = 0; lin < tam; lin++) {
      ceOpt = document.createElement("option");
      ceContext = document.createTextNode(meses[lin].texto);
      ceOpt.appendChild(ceContext);
      ceOpt.setAttribute("value", meses[lin].valor);
      ceOpt.setAttribute("text", meses[lin].texto);
      if (meses[lin].texto == "OUT/19") ceOpt.setAttribute("selected", true);
      el.appendChild(ceOpt);
    }
  }
}

function converterObjeto(objeto) {
  var novoArray = [];
  objeto.forEach(element => {
    var arrayElemento = [];
    for (let key in element) {
      arrayElemento.push(element[key]);
    }
    novoArray.push(arrayElemento);
  });
  return novoArray;
}
