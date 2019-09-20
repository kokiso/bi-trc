<!DOCTYPE html>
<html>
<head>
    <meta charset=utf-8 />
    <title>Procurar por endereço</title>
    <meta name='viewport' content='initial-scale=1,maximum-scale=1,user-scalable=no' />

    <!-- Load Leaflet from CDN-->
    <!--
      <link rel="stylesheet" href="https://unpkg.com/leaflet@1.3.1/dist/leaflet.css" />
    -->
    <link rel="stylesheet" href="css/leafletAlvo.css" />
    
    <!--
    <script src="https://unpkg.com/leaflet@1.3.1/dist/leaflet-src.js"></script>
    -->
    <script src="js/leaflet-src.js"></script>

    <!-- Load Esri Leaflet from CDN -->
    <!--<script src="https://unpkg.com/esri-leaflet@2.1.3"></script>-->
    <script src="js/esri-leaflet-debug.js"></script>
    
    

    <!-- Esri Leaflet Geocoder -->
    <!--<link rel="stylesheet" href="https://unpkg.com/esri-leaflet-geocoder@2.2.9/dist/esri-leaflet-geocoder.css">-->
    <link rel="stylesheet" href="css/esri-leaflet-geocoder.css" />
    
    <!--<script src="https://unpkg.com/esri-leaflet-geocoder@2.2.8"></script>
    <script src="js/esri-leaflet-debug.js"></script>
    
    -->
    <script src="https://unpkg.com/esri-leaflet-geocoder@2.2.8"></script>
    
    
    <link rel="stylesheet" href="../css/css2017.css">
    <link rel="stylesheet" href="../css/cssTable2017.css">
    <link rel="stylesheet" href="../css/cssFaTable.css">
    <script src="../js/js2017.js"></script>
    <script src="../js/jsTable2017.js"></script>
    
    

    <!-- Make the map fill the entire page -->
    <style>

    #map {
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
    }

    </style>
</head>

<body>
    <div id="map" style="width:100%;height:93%;">
    </div>
    
    <div class="campotexto campo100" style="border:1px solid silver;background-color: #ecf0f5; bottom:0px;position:absolute;">
      <div class="campotexto campo10">
        <input class="campo_input_titulo edtDireita" id="edtLatitude" type="text" disabled />
        <label class="campo_label campo_required" for="edtLatitude">LATITUDE:</label>
      </div>
      <div class="campotexto campo10">
        <input class="campo_input_titulo edtDireita" id="edtLongitude" type="text" disabled />
        <label class="campo_label campo_required" for="edtLongitude">LONGITUDE:</label>
      </div>
      <!--
      <div class="campo10" style="float:left;">
        <input onClick="confirmar();" id="btnConfirmar" type="button" value="Confirmar" class="campo100 tableBotao botaoForaTable" />
      </div>
      -->
      <div class="campo10" style="float:left;">
        <input onClick="cancelar();" id="btnCancelar" type="button" value="Cancelar" class="campo100 tableBotao botaoForaTable" />
      </div>
      <!--
      <div class="campotexto campo50">
        <input class="campo_input_titulo" id="edtEndereco" type="text" disabled />
        <label class="campo_label campo" for="edtEndereco">Endereço origem:</label>
      </div>
      -->
    </div>
    
    <script>
      /*
      * Exemplos
      * https://codepen.io/rubenspgcavalcante/pen/MvdJbb?limit=all&page=5&q=openstreetmap     ROTAAAAAAAAAAAAAAAAAAAAAAA
      * https://codepen.io/travishorn/pen/rYeeMw
      * https://codepen.io/belgattitude/pen/grobPO
      * https://codepen.io/christippett/pen/dojgPb
      */
      var map;
      var markers = [];
      var myMarker;
      var popupContent;
      var msg=JSON.parse(localStorage.getItem("addMapa"));
      var iniLat=msg[0].lat;
      var iniLon=msg[0].lon;
      var iniLoc=msg[0].loc;
      
      //document.getElementById("edtEndereco").value=msg[0].codlgr+" "+msg[0].endereco+" "+msg[0].numero+" "+msg[0].cidade+" "+msg[0].codest;
      
      document.getElementById("edtLatitude").value  = iniLat;
      document.getElementById("edtLongitude").value = iniLon;
      map = L.map('map').setView([iniLat,iniLon], 10);
      map.panTo(new L.LatLng(iniLat,iniLon));
      
      createMarker([iniLat, iniLon]);
      map.setView([iniLat, iniLon]);
      
      var tiles = L.esri.basemapLayer("Streets").addTo(map);
      ////////////////////////////////////////////////////////////
      // crie o controle de geocodificação e adicione-o ao mapa //
      ////////////////////////////////////////////////////////////
      // api = https://esri.github.io/esri-leaflet/api-reference/controls/geosearch.html
      //var searchControl = L.esri.Geocoding.geosearch().addTo(map);
      var searchControl = L.esri.Geocoding.geosearch({
        placeholder:"Procurar por lugar, endereço ou CEP"
        ,title:"procurar"
        ,expanded:false  
        //,text:"orlando"
        //,useMapBounds:true
        //providers: [ L.esri.Geocoding.arcgisOnlineProvider() ]
      }).addTo(map);
      
      //searchControl.options.placeholder("procurar por praça ou endereco");
      ////////////////////////////////////////////////////////////////////////////////////////
      // crie um grupo de camadas vazio para armazenar os resultados e adicioná-los ao mapa //
      ////////////////////////////////////////////////////////////////////////////////////////
      var results = L.layerGroup().addTo(map);
      ////////////////////////////////////////////////////////////////////////
      // ouça o evento de resultados e adicione todos os resultados ao mapa //
      ////////////////////////////////////////////////////////////////////////
      searchControl.on("results", function(data) {
        results.clearLayers();
        for (var i = data.results.length - 1; i >= 0; i--) {
          popupContent=data.text;
          //document.getElementById("edtEndereco").value=data.text;
          /////////////////////////////////////////////////////////
          // Esta funcao adiciona automaticamente o maker        //
          //results.addLayer(L.marker(data.results[i].latlng));  //
          /////////////////////////////////////////////////////////
          clearMarker(0);
          createMarker([data.results[i].latlng.lat, data.results[i].latlng.lng]);
          document.getElementById("edtLatitude").value  = jsNmrs(data.results[i].latlng.lat).dec(8).real().ret();
          document.getElementById("edtLongitude").value = jsNmrs(data.results[i].latlng.lng).dec(8).real().ret();
        };
      });
      
      map.on('click', function(ev) {
        document.getElementById("edtLatitude").value  = jsNmrs(ev.latlng.lat).dec(8).real().ret();
        document.getElementById("edtLongitude").value = jsNmrs(ev.latlng.lng).dec(8).real().ret();
        clearMarker(0);
        createMarker([ev.latlng.lat, ev.latlng.lng]);
        map.setView([ev.latlng.lat, ev.latlng.lng]);
      });
      
      function createMarker(coords) {
        var id;
        if (markers.length < 1) 
          id = 0
        else 
          id = markers[markers.length - 1]._id + 1
          popupContent = iniLoc;
        /*  
        var popupContent =
          '<p>Remover marca</p></br>' +
          //'<p>test</p></br>' +
          '<button onclick="clearMarker(' + id + ')">Remover</button>';
        */  

        myMarker = L.marker(coords, {
          draggable: false
        });

        //
        //  aqui se quiser arrastar
        /*
        myMarker.on('dragend', function(event){
          var marker = event.target;
          var position = marker.getLatLng();
          document.getElementById("edtLatitude").value  = jsNmrs(position.lat).dec(8).real().ret();
          document.getElementById("edtLongitude").value = jsNmrs(position.lng).dec(8).real().ret();
          
          popupContent=
            '<p>Latirude:'+document.getElementById("edtLatitude").value+'</p>' +
            '<p>Longitude:'+document.getElementById("edtLongitude").value+'</p>';
          var myPopup = myMarker.bindPopup(popupContent, {
            closeButton: false
          });
  
            
            
          map.panTo(new L.LatLng(position.lat, position.lng));
        });
        */
        //
        //
        myMarker._id = id
        var myPopup = myMarker.bindPopup(popupContent, {
          closeButton: false
        });
        map.addLayer(myMarker)
        markers.push(myMarker)
      }
      //
      //
      function clearMarker(id) {
        var new_markers = []
        markers.forEach(function(marker) {
          if (marker._id == id) map.removeLayer(marker)
          else new_markers.push(marker)
        })
        markers = new_markers
      }

      
      //var tem=document.getElementsByClassName("geocoder-control-input");
      //tem.innerHTLM="orlando";
      //debugger;
      
      function hasClass (obj, className) {
        if (typeof obj == 'undefined' || obj==null || !RegExp) { 
          return false; 
        };
        var re = new RegExp("(^|\\s)" + className + "(\\s|$)");
        if (typeof(obj)=="string") {
          return re.test(obj);
        }
        else if (typeof(obj)=="object" && obj.className) {
          return re.test(obj.className);
        }
        return false;
      };      
      
      
      //////////////////////////////////////////////////
      // Confirmar e retornar ao formulario de origem //  
      //////////////////////////////////////////////////
      /*
      function confirmar(){
        opener.document.getElementById("edtLatitude").value=document.getElementById("edtLatitude").value;
        opener.document.getElementById("edtLongitude").value=document.getElementById("edtLongitude").value;
        window.close();
      }
      */
      //////////////////////////////////////////////////
      // Confirmar e retornar ao formulario de origem //  
      //////////////////////////////////////////////////
      function cancelar(){
        window.close();
      }
      /*
      document.getElementsByClassName('geocoder-control-input')[0].addEventListener('focus',function(){
        this.value = document.getElementById('edtEndereco').value;
      });      
      */
    </script>
</body>
</html>