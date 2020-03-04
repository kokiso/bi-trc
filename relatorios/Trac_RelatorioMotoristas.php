

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <title>Relatório Geral B.I</title>
  <!-- MDB icon -->
  <link rel="icon" href="img/mdb-favicon.ico" type="image/x-icon">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.11.2/css/all.css">
  <!-- Google Fonts Roboto -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap">
  <!-- Bootstrap core CSS -->
  <link rel="stylesheet" href="../charts/cssCharts/bootstrap.min.css">
  <!-- Material Design Bootstrap -->
  <link rel="stylesheet" href="../charts/cssCharts/mdb.min.css">
  <!-- Your custom styles (optional) -->
  <link rel="stylesheet" href="../charts/cssCharts/style.css">
</head>
<body>
  <section class="container">
    <nav class="nav-bi">
      <div><img src="../imagens/logoMenor - original.png" alt="logo" style="max-width: 300px;"/></div>
        <div class="div-info1">
            <div>
                <p>Rua:<span> R. Itanhaém, 2.389 - Vila Elisa, Ribeirão Preto - SP</span></p>
            </div>
            <div>
                <p>E-mail:<span> suporte@totaltrac.com.br</span></p>
            </div>
        </div>
        <div class="div-info2">
            <p>Site:<span> http://totaltrac.com.br</span></p>
            <p>Telefone:<span> (16) 3615 8571</span></p>
        </div>
      </nav>
      <h1 class="titulo" style="text-align: center;">Relatório Geral B.I.</h1>
      <header class="header">
        <h3 class="titulo-informacoes-gerais">Informções gerais</h3>
        <div>
          <ul class="lista-header">
            <li>Motoristas: <span id="motoristas"></span></li>
          </ul>
        </div>
      </header>
      <section>
        <h2 class="titulo-graficos">Graficos Demonstrativos</h2>
        <div class="div-principal-grafico">
            <div class="divs-grafico">
                <h6 class=h6>Motoristas</h6>
                <canvas id="labelMotorista"></canvas>
            </div>

            <div class="divs-grafico">
                <h6 class=h6>Veiculos</h6>
                <canvas id="labelVeiculo"></canvas>
            </div>
        </div>
      <!-- End your project here-->
      </section>
  </section>


  <footer class="footer">
      <div class="div-footer">
        <p>Desenvolvido pela empresa Total Trac Soluções de Rastreamento e Telemetria</p>
      </div>
  </footer>






  <!-- jQuery -->
  <script type="text/javascript" src="../charts/jsCharts/jquery.min.js"></script>
  <!-- Bootstrap tooltips -->
  <script type="text/javascript" src="../charts/jsCharts/popper.min.js"></script>
  <!-- Bootstrap core JavaScript -->
  <script type="text/javascript" src="../charts/jsCharts/bootstrap.min.js"></script>
  <!-- MDB core JavaScript -->
  <script type="text/javascript" src="../charts/jsCharts/mdb.min.js"></script>
  <!-- Your custom scripts (optional) -->
  <script type="text/javascript"></script>

</body>

<script>
let arrayEnvio = JSON.parse(sessionStorage.getItem('chave'));
console.log(arrayEnvio);





// GRAFICO RANKING UNIDADE
let graficoMotorista = [];
let graficoMotoQTOS = [];
let graficoMotoNome = []
for(let i = 0; i < arrayEnvio[0].length; i++){
    graficoMotorista.push(arrayEnvio[0][i]);
    graficoMotoQTOS.push(graficoMotorista[i].QTOS);
    graficoMotoNome.push(graficoMotorista[i].NOME + '- QTD:' + graficoMotorista[i].QTOS +  ' - ' + graficoMotorista[i].PERCENTUAL +'%');
}



var ctxP  = document.getElementById("labelMotorista").getContext('2d');
var myPieChart  = new Chart(ctxP, {
type: 'pie',
data: {
labels: graficoMotoNome,
datasets: [{
label: 'Ranking Unidades',
data: graficoMotoQTOS,
backgroundColor: ["#F7464A", "#46BFBD", "#FDB45C", "#949FB1", "#4D5360","#000","#99CC32","#32CD99","#00FFFF","#FF00FF","#FFFF00","#00FF00","#00FF7F","#A68064","#4F2F4F","#FFB6C1","#E0FFFF","#0000FF","#4B0082","#B0E0E6"],
    }]
  },
  options: {
    responsive: true,
    legend: {
      position: 'right',
      labels: {
        padding: 20,
        boxWidth: 15,
        fontSize: 9,
      }
    },
    plugins: {
      datalabels: {
        formatter: (value, ctx) => {
          let sum = 0;
          let dataArr = ctx.chart.data.datasets[0].data;
          dataArr.map(data => {
            sum += data;
          });
          let percentage = (value * 100 / sum).toFixed(2) + "%";
          return percentage;
        },
        color: 'white',
        labels: {
          title: {
            font: {
              size: '16'
            }
          }
        }
      }
    }
  }
});






// GRAFICO RANKING UNIDADE
let graficoVeiculos = [];
let graficoVeiculosQTOS = [];
let graficoVeiculosNome = []
for(let i = 0; i < arrayEnvio[1].length; i++){
    graficoVeiculos.push(arrayEnvio[1][i]);
    graficoVeiculosQTOS.push(graficoVeiculos[i].QTOS);
    graficoVeiculosNome.push(graficoVeiculos[i].NOME + '- QTD:' + graficoVeiculos[i].QTOS +  ' - ' + graficoVeiculos[i].PERCENTUAL +'%');
}



var ctxP  = document.getElementById("labelVeiculo").getContext('2d');
var myPieChart  = new Chart(ctxP, {
type: 'pie',
data: {
labels: graficoVeiculosNome,
datasets: [{
label: 'Ranking Unidades',
data: graficoVeiculosQTOS,
backgroundColor: ["#F7464A", "#46BFBD", "#FDB45C", "#949FB1", "#4D5360","#000","#99CC32","#32CD99","#00FFFF","#FF00FF","#FFFF00","#00FF00","#00FF7F","#A68064","#4F2F4F","#FFB6C1","#E0FFFF","#0000FF","#4B0082","#B0E0E6"],
    }]
  },
  options: {
    responsive: true,
    legend: {
      position: 'right',
      labels: {
        padding: 20,
        boxWidth: 15,
        fontSize: 9
      }
    },
    plugins: {
      datalabels: {
        formatter: (value, ctx) => {
          let sum = 0;
          let dataArr = ctx.chart.data.datasets[0].data;
          dataArr.map(data => {
            sum += data;
          });
          let percentage = (value * 100 / sum).toFixed(2) + "%";
          return percentage;
        },
        color: 'white',
        labels: {
          title: {
            font: {
              size: '16'
            }
          }
        }
      }
    }
  }
});


  /*ARRAY arrayEnvio[2] QUE SERA USADO PRO CABEÇALHO DE INFORMAÇOES*/
  document.getElementById("motoristas").innerText = arrayEnvio[2];


    setTimeout(() => {
      window.print();
    }, 1500);
  


</script>
</html>


<style>
/* RESET DE CSS PARA NAO CAUSAR PROBLEMAS */
html, body, div, span, applet, object, iframe,
h1, h2, h3, h4, h5, h6, p, blockquote, pre,
a, abbr, acronym, address, big, cite, code,
del, dfn, em, img, ins, kbd, q, s, samp,
small, strike, strong, sub, sup, tt, var,
b, u, i, center,
dl, dt, dd, ol, ul, li,
fieldset, form, label, legend,
table, caption, tbody, tfoot, thead, tr, th, td,
article, aside, canvas, details, embed, 
figure, figcaption, footer, header, hgroup, 
menu, nav, output, ruby, section, summary,
time, mark, audio, video {
	margin: 0;
	padding: 0;
	border: 0;
	font-size: 100%;
	font: inherit;
	vertical-align: baseline;
}
/* HTML5 display-role reset for older browsers */
article, aside, details, figcaption, figure, 
footer, header, hgroup, menu, nav, section {
	display: block;
}
body {
	line-height: 1;
}
ol, ul {
	list-style: none;
}
blockquote, q {
	quotes: none;
}
blockquote:before, blockquote:after,
q:before, q:after {
	content: '';
	content: none;
}
table {
	border-collapse: collapse;
	border-spacing: 0;
}


/* AQUI COMEÇA O CSS DA PAGINA CRIADO POR MIM */

/* PARTE NAV TOTALTRAC BI */
.nav-bi {
  margin-bottom: 20px;
  display:flex;
  font-weight: bold;
}


.div-info1 {
  margin: 25px 0px 10px 130px;
  font-size: 14px;
}

.div-info2 {
  margin: 25px 0px 10px 20px;
  font-size: 14px;
}

.divs-grafico-2 {
    width: 580px;
}






.titulo {
  font-size: 40px;
}

.h6 {
  font-size: 16px;
  font-weight: bold;
}

.divs-grafico {
  max-width: 580px !important;
  min-width: 580px !important;
}

.div-principal-grafico {
  display: flex;
  margin-bottom: 150px;
}

.header {
  margin: 40px auto 40px auto;
}

.lista-header {
  display: flex;
  flex-wrap: wrap;
}
.lista-header li {
  padding: 5px 15px 5px 15px;
  font-size : 14px;
  border: #000 1px solid;
  font-weight: bold;
}



/* parte dos graficos */
.titulo-graficos {
  font-size: 33px;
  margin-bottom: 80px;
}

.titulo-informacoes-gerais {
  font-size: 23px;
  margin-bottom: 10px;
}


/* FOOTER */

.footer {
  width: 100%;
  height: 60px;
  background-color: #378ECC;
  position: relative;
  bottom: 0;
}

.div-footer p {
  text-align: center;
  /* justify-content: center; */
  color: #fff;
  padding: 20px 0px 20px 0;
}

.container {
  width: 1200px !important;
}













</style>
