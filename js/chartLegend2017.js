/*
function legend(parent, data) {
    legend(parent, data, null);
}

function legend(parent, data, chart) {
    parent.className = 'legend';
    var datas = data.hasOwnProperty('datasets') ? data.datasets : data;

    // remove possible children of the parent
    while(parent.hasChildNodes()) {
        parent.removeChild(parent.lastChild);
    }

    var show = chart ? showTooltip : noop;
    datas.forEach(function(d, i) {
        //span to div: legend appears to all element (color-sample and text-node)
        var title = document.createElement('div');
        title.className = 'title';
        parent.appendChild(title);

        var colorSample = document.createElement('div');
        colorSample.className = 'color-sample';
        colorSample.style.backgroundColor = d.hasOwnProperty('strokeColor') ? d.strokeColor : d.color;
        colorSample.style.borderColor = d.hasOwnProperty('fillColor') ? d.fillColor : d.color;
        title.appendChild(colorSample);

        var text = document.createTextNode(d.label);
        text.className = 'text-node';
        title.appendChild(text);
        show(chart, title, i);
    });
}

//add events to legend that show tool tips on chart
function showTooltip(chart, elem, indexChartSegment){
    var helpers = Chart.helpers;

    var segments = chart.segments;
    //Only chart with segments
    if(typeof segments != 'undefined'){
        helpers.addEvent(elem, 'mouseover', function(){
            var segment = segments[indexChartSegment];
            segment.save();
            segment.fillColor = segment.highlightColor;
            chart.showTooltip([segment]);
            segment.restore();
        });

        helpers.addEvent(elem, 'mouseout', function(){
            chart.draw();
        });
    }
}

function noop() {}
*/


function legend(parent, data) {
    legend(parent, data, null);
}

function legend(parent, data, chart, vlr) {
    parent.className = 'legend';
    var datas = data.hasOwnProperty('datasets') ? data.datasets : data;

    // Remover filhos possíveis do pai
    while(parent.hasChildNodes()) {
        parent.removeChild(parent.lastChild);
    };
    var show = chart ? showTooltip : noop;
    
    var valor;
    datas.forEach(function(d, i) {
      if( i== 0 ){
        tbl=document.createElement('table');
        tbl.style.fontSize="14px";
      };
      var lin=document.createElement('tr');      
      parent.appendChild(tbl);
      
        var col = document.createElement('td');
        col.className = 'color-sample';
        
        col.setAttribute("style","margin-top:4px");
        col.style.padding='4px';
        col.style.backgroundColor = d.hasOwnProperty('strokeColor') ? d.strokeColor : d.color;
        col.style.borderColor     = d.hasOwnProperty('fillColor')   ? d.fillColor   : d.color;
        valor                     = d.hasOwnProperty('strokeColor') ? d.data        : d.value;
        lin.appendChild(col);
        //    
        col = document.createElement('td');
        col.innerHTML = d.label;
        lin.appendChild(col);
        //
        if( vlr ){        
          col = document.createElement('td');
          col.innerHTML = '&nbsp;&nbsp;';
          lin.appendChild(col);
          //
          col = document.createElement('td');
          col.className = 'edtDireita';
          col.innerHTML = valor;
          lin.appendChild(col);
        };
        //
        tbl.appendChild(lin);
        show(chart, lin,i);
    });
};

//Adicionar eventos à legenda que mostram dicas de ferramentas no gráfico
function showTooltip(chart, elem, indexChartSegment){
    var helpers = Chart.helpers;
    var segments = chart.segments;
    //Only chart with segments
    if(typeof segments != 'undefined'){
        helpers.addEvent(elem, 'mousemove', function(){
            var segment = segments[indexChartSegment];
            segment.save();
            segment.fillColor = segment.highlightColor;
            chart.showTooltip([segment]);
            segment.restore();
        });

        helpers.addEvent(elem, 'mouseout', function(){
            chart.draw();
        });
    }
}
function noop() {}
