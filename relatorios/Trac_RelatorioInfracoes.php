

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
      </header>
      <section>
        <h2 class="titulo-graficos">Graficos Demonstrativos</h2>
        <div class="div-principal-grafico">
          <div class="divs-grafico">
          <h6 class="h6" id="tituloInfraMes">Infrações DEZ/19 74102</h6>
              <canvas id="labelInfraMes"></canvas>
          </div>
          <div class="divs-grafico">
            <h6 class="h6">Comparativo EV/EVC/FB em %</h6>
            <canvas id="labelComparativo"></canvas>
          </div>
        </div>
        <div class="div-principal-grafico">
            <div class="divs-grafico">
                <h6 class=h6>Média de KM sem Infrações</h6>
                <canvas id="lineChart"></canvas>
            </div>
            <div class="divs-grafico">
              <h6 class=h6>Comparativo Mensal Infrações Velocidade</h6>
              <canvas id="barChart"></canvas>
            </div>
        </div>
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
// FOR PARA O PRIMEIRO GRAFICO -- infraçoes/MES
let mesInfracao = sessionStorage.getItem('tituloMes');
let graficoInfraDez = [];
let graficoInfraQTOS = [];
let graficoInfraNome = [];



// SETANDO TITULO DINAMICO PRA MES
document.getElementById("tituloInfraMes").innerHTML = mesInfracao;


// MONTA ARRAY COM OS DADOS DE INFRA/MES
for(let i = 0; i < arrayEnvio[0].length; i++){
    graficoInfraDez.push(arrayEnvio[0][i]);
    graficoInfraQTOS.push(graficoInfraDez[i].QTOS);
    graficoInfraNome.push(graficoInfraDez[i].NOME + '- QTD:' + graficoInfraDez[i].QTOS +  ' - ' + graficoInfraDez[i].PERCENTUAL +'%');
}


var ctxP  = document.getElementById("labelInfraMes").getContext('2d');
var myPieChart  = new Chart(ctxP, {
type: 'doughnut',
data: {
labels: graficoInfraNome,
datasets: [{
label: 'Infraçoes',
data: graficoInfraQTOS,
backgroundColor: ["#F7464A", "#46BFBD", "#FDB45C", "#949FB1", "#4D5360","#000","#99CC32","#32CD99","#00FFFF","#FF00FF","#FFFF00","#00FF00","#00FF7F","#A68064","#4F2F4F","#FFB6C1","#E0FFFF"],
    }]
  },
  options: {
    responsive: true,
    legend: {
      position: 'right',
      labels: {
        padding: 20,
        boxWidth: 10,
        fontSize: 14
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


// GRAFICO DO COMPARATIVO EV/EVC/FB EM %
let graficoComparativo = [];
let graficoComparativoQTOS = [];
let graficoComparativoNome = [];
// USADA DENTRO DO IF PQ O I FICA PRO ESCOPO DE FORA E O J PRA DENTRO COMEÇANDO COM 0
let j = 0;
// MONTA ARRAY COM OS DADOS COMPARATIVO
for(let i = 0; i < arrayEnvio[0].length; i++){
  // console.log(arrayEnvio[2][i]);
  if(arrayEnvio[0][i].GRAFICO == 'S'){
    graficoComparativo.push(arrayEnvio[0][i]);
    graficoComparativoQTOS.push(graficoComparativo[j].QTOS);
    graficoComparativoNome.push(graficoComparativo[j].NOME + '- QTD:' + graficoComparativo[j].QTOS +  ' - ' + graficoComparativo[j].PERCENTUAL +'%');
    j++;
  }
}


var ctxP  = document.getElementById("labelComparativo").getContext('2d');
var myPieChart  = new Chart(ctxP, {
type: 'doughnut',
data: {
labels: graficoComparativoNome,
datasets: [{
label: 'Comparativo',
data: graficoComparativoQTOS,
backgroundColor: ["#F7464A", "#46BFBD", "#FDB45C", "#949FB1", "#4D5360","#000","#99CC32","#32CD99","#00FFFF","#FF00FF","#FFFF00","#00FF00","#00FF7F","#A68064","#4F2F4F","#FFB6C1","#E0FFFF"],
    }]
  },
  options: {
    responsive: true,
    legend: {
      position: 'right',
      labels: {
        padding: 20,
        boxWidth: 10,
        fontSize: 14
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




// GRAFICO "Média de KM sem Infrações"
let dadosGrafLine = arrayEnvio[1];
let labelsGrafLine = [];
let dataGrafLine = [];
dadosGrafLine.forEach((item) =>{
  labelsGrafLine.push(item.ANOMES);
  dataGrafLine.push(item.MEDIA);
})

var ctxL = document.getElementById("lineChart").getContext('2d');
var myLineChart = new Chart(ctxL, {
type: 'line',
data: {
labels: labelsGrafLine,
datasets: [{
label: "Média de KM sem Infrações",
data: dataGrafLine,
backgroundColor: [
'rgba(34, 156, 45, 0.2)',
],
borderColor: [
'rgba(4, 156, 45, 1)',
],
borderWidth: 2
}
]
},
options: {
responsive: true
}
});







//GRAFICO Comparativo Mensal Infrações Velocidade
let dataGrafBarra = [];
dadosGrafLine.forEach((item) =>{
  dataGrafBarra.push(item.INFRACAO);
})
var ctxB = document.getElementById("barChart").getContext('2d');
var myBarChart = new Chart(ctxB, {
type: 'bar',
data: {
labels: labelsGrafLine,
datasets: [{
label: '# of Votes',
data: dataGrafBarra,
backgroundColor: [
'rgba(54, 162, 235, 0.2)',
'rgba(255, 206, 86, 0.2)',
'rgba(153, 102, 255, 0.2)',
],
borderColor: [
'rgba(54, 162, 235, 1)',
'rgba(255, 206, 86, 1)',
'rgba(153, 102, 255, 1)'

],
borderWidth: 1
}]
},
options: {
scales: {
yAxes: [{
ticks: {
beginAtZero: true
}
}]
}
}
});
  /*ARRAY arrayEnvio[5][?] QUE SERA USADO PRO CABEÇALHO DE INFORMAÇOES
    [0] - NUMERO MOTORISTAS
    [1]- NUMERO DE VEICULOS
    [5] - KM PERCORRIDO
    [6] - HORAS MOVIMENTO
    [7] - HORAS PARADO
    [8] - VELOCIADDE MEDIA
    [9] - INFRAÇOES (UNICO QUE VEM COMO INTEGER, O RESTO É STRING);
  */

//   document.getElementById("motoristas").innerText = arrayEnvio[5][0];
//   document.getElementById("veiculos").innerText = arrayEnvio[5][1];
//   document.getElementById("kmPercorrido").innerText = arrayEnvio[5][5];
//   document.getElementById("horasMovimento").innerText = arrayEnvio[5][6];
//   document.getElementById("horasParado").innerText = arrayEnvio[5][8];
//   document.getElementById("velocidadeMedia").innerText = arrayEnvio[5][7];
//   document.getElementById("infracoes").innerText = arrayEnvio[5][9];


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









.titulo {
  font-size: 40px;
}

.h6 {
  font-size: 16px;
  font-weight: bold;
}

.divs-grafico {
  max-width: 500px !important;
  margin-right: 10px;
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
