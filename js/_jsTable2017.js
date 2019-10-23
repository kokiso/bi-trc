////////////////////////////////////////
// PROTOTYPES USADOS APENAS NA CLASSE //
////////////////////////////////////////
//"use strict";
String.prototype.campoSql = function(tipo) {
    var cmpfBD = new clsCampo();
    try {
        if (['dat', 'flo', 'flo2', 'flo4', 'flo8', 'int', 'str'].indexOf(tipo) == -1)
            throw "Tipo informado " + tipo + " incorreto!";
        switch (tipo) {
            case 'str':
                return (this == null ? null : '\'' + this + '\'');
                break;
            case 'dat':
                return (((this == null) || (this == "")) ? null : '\'' + jsDatas(this).retMMDDYYYY() + '\'');
                break;
            case 'flo':
                return '\'' + cmpfBD.floatNA(this) + '\'';
                break;
            case 'flo2':
                return '\'' + cmpfBD.floatNA(this) + '\'';
                break;
            case 'flo4':
                return '\'' + cmpfBD.floatNA4(this) + '\'';
                break;
            case 'flo8':
                return '\'' + cmpfBD.floatNA8(this) + '\'';
                break;
            case 'int':
                return this;
                break;
        };
    } catch (e) {
        gerarMensagemErro("SQL", e, "Erro");
    };
};
//
function clsTable2017(obj) {

    var self = this;
    self.Js = "";
    self.procurar = true; // Opção para monta a div de qtdade de registros e input procurar
    self.status = -1; // I/A/E para ver se estou cadastrando, alterando ou excluindo um registro na grade
    self.tblF10 = false; // Se é uma table complementar de ajuda
    self.focoF10; // Onde vai o foco quando confirmar
    self.obj = obj; // Nome do objeto criador da instância
    self.iFrame = ""; // Se a table vai ficar dentro de um iFrame
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Adiciona um id unico no json e table para alterações e exclusões                                                         //
    // Primeiro parametro guarda o último ID inserido na tabela para quando novo registro( usar Id para proximo e incrementar ) //
    // Segundo parametro é a coluna do JSON que esta o campo _ID ( usar este para alterar e excluir )                           //
    // Terceiro parametro é a coluna na table que esta o campo _ID ( usar este para pegar o rowIndex da table )                 //
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    self.addId = new Array(0, 0, 0, 0);
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // self.altVarios                                                                                                           //
    // Para pode alterar/excluir(marcar checkbos) varios registros em uma table(Não pode usar o confirmar padrão)               //
    // O botão confirmar deve ser do tipo '7'(manual), exemplo desta variavel em ERP_Competencia2017.php onde                   //
    // varios registros são alterados ao mesmo temporario                                                                       //
    // Esta variavel deve ser declarada na criação do obj no arquivo tabelaXXXXXXX.js exemplo em tabelaERP/tabelaCompetencia.js //
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    self.altVarios = false;
    //
    this.atualizarJS = function(parJS) {
            self.Js = parJS;
        }
        //
    this.addIdUnico = function(arr) {
        var tam = arr.length;
        self.addId[0] = (tam + 1);
        for (var linha = 0; linha < tam; linha++) {
            arr[linha].push(linha + 1);
        };
        return arr;
    };
    //  
    this.montarHtmlCE2017 = function(parJS) {
        //////////////////////////////////////////////////////////////////////////////////
        // Colocando um ID único no JSON e TABLE para ser o indice ao alterar e excluir //
        //////////////////////////////////////////////////////////////////////////////////
        parJS.titulo.push({
            "id": parJS.titulo.length,
            "tipo": "id",
            "fieldType": "idUnico",
            "labelCol": "_ID",
            "tamGrd": "0em",
            "ordenaColuna": "N",
            "insUpDel": ['N', 'N', 'N'],
            "padrao": 0
        });
        self.Js = parJS;
        if (tagValida(self.Js.iFrame)) {
            self.iFrame = self.Js.iFrame;
        }

        this.camposPadrao2017();

        var tamanho = parJS.titulo.length;
        var naoJson = 0;
        for (var linha = 0; linha < tamanho; linha++) {
            if ((['chk', 'img', 'idUnico', 'pagUnico']).indexOf(parJS.titulo[linha].fieldType) != -1) {
                naoJson++;
            };
        };
        tamanho = parJS.registros.length;
        /////////////////////////////////////////////////////////////////////////////////////
        // Obrigatorio no minimo um registro. Existe as tables que vem sem nenhum registro //
        /////////////////////////////////////////////////////////////////////////////////////
        if (tamanho > 0) {
            for (var linha = 0; linha < tamanho; linha++) {
                parJS.registros[linha].push(linha + 1);
            }
            self.addId[0] = (linha + 2); //Se incluir novo registro usar este ID e incrementar
        } else {
            self.addId[0] = 1;
        };
        self.addId[1] = (parJS.titulo.length - naoJson); //Coluna no JSON que esta o campo _ID    
        self.addId[2] = (parJS.titulo.length - 1); //Coluna na TABLE que esta o campo _ID
        self.addId[3] = (self.addId[2] + 1); //Coluna no JSON _PAG de paginação


        this.verTags2017();
        var bBotaoD = (tagValida(self.Js.botoesD) ? true : false); // Checando se existe botão a direita 
        var bBotaoH = (tagValida(self.Js.botoesH) ? true : (self.tblF10 ? true : false)); // Checando se existe botão horizontal
        var dTable = '';
        var _divTd = '';
        var ceAnc = '';
        var ceBold = '';
        var ceButton = '';
        var ceContext = '';
        var ceDiv = '';
        var ceChanfro = '';
        var ceHint = '';
        var ceImg = '';
        var ceInput = '';
        var ceLabel = '';
        var ceLiPai = '';
        var ceLi = '';
        var ceNav = '';
        var ceTable = '';
        var ceTd = '';
        var ceTh = '';
        var ceThead = '';
        var ceTr = '';
        var ceUlPai = '';
        var ceUl = '';
        var cmp = new clsCampo();
        var dPaiD = '';
        var lblMenuTable = (tagValida(self.Js.labelMenuTable) ? self.Js.labelMenuTable : '+ Opções');
        var margem = "5px";
        var newFunc = '';
        var nItens = 0;
        var registros = '';
        var taman = "calc(100% - 10px)";
        var tamBotao = '15';
        var tamIte = '15em';
        var tamTit = '10em';
        var refazClasse = 'N';
        ttl = self.Js.titulo;
        //////////////////////////////////////////////////////////////////////////////////////////////////////////
        // A variavel refazClasse vem do JS e é preenchida com "S" quando da necessidade de se refazer a table  //
        // aumentando ou diminuindo seu tamanho                                                                 //
        // EX: Gerador de relatórios onde se adiciona ou remove campos                                          //
        // Todos os js que tiverem campos alteraveis nos campos(variaveis/array ou edts)                        //
        //////////////////////////////////////////////////////////////////////////////////////////////////////////
        if (tagValida(self.Js.refazClasse)) {
            if (self.Js.refazClasse == 'S') {
                if (document.getElementById("dPai" + self.Js.div) !== null) {
                    document.getElementById("dPai" + self.Js.div).remove();
                };
            };
        };
        ////////////////////////////////////////////////////////////////////////////////////////////////////
        // Se ja o dPai não preciso criar todos elementos novamente, somente preencher novamente a table  //
        ////////////////////////////////////////////////////////////////////////////////////////////////////
        if (document.getElementById("tbody_" + self.Js.tbl) !== null) {
            var tableId = self.Js.tbl;
            this.ordenaJSon(self.Js.indiceTable, false);
            this.montarBody2017();
            return false;
        };
        ////////////////////////////////////////////////////////////////////
        // A opção de botões a direita botoesD muda o tamanho da div dPai //
        // Checando se existe esta tag                                    //
        ////////////////////////////////////////////////////////////////////
        if (bBotaoD) {
            bBotaoD = true;
            dPaiD = document.createElement("div");
            dPaiD.id = "dPaiD" + self.Js.div;
            dPaiD.style.cssFloat = "right";
            dPaiD.style.height = (tagValida(self.Js.height) ? self.Js.height : "40em");
            dPaiD.style.width = "10em";
            var margTop = 1;
            self.Js.botoesD.forEach(function(reg, lin) {
                if (reg.enabled) {
                    ceDiv = document.createElement("div");
                    ceDiv.className = "tableFrm tooltip";
                    ceDiv.style.width = "5em";
                    ceDiv.style.top = margTop + "%";

                    var cePar = document.createElement("p");
                    cePar.className = "frmSupDir";
                    ceButton = document.createElement("button");
                    ceButton.className = "tableBotao frmDir";
                    ceButton.type = "button";
                    ceButton.id = reg.name;
                    ceImg = document.createElement("i");
                    ceImg.className = reg.imagem;

                    if (reg.ajuda != undefined) {
                        ceSpan = document.createElement('span');
                        ceSpan.className = "tooltiptext";
                        ceSpan.style.marginLeft = "3.9em";
                        ceContext = document.createTextNode(reg.ajuda);
                        ceSpan.appendChild(ceContext);
                    };
                    ///////////////////////////////////////////////////////////////////////////////////
                    // Os eventos são criados após appendChild devido cadastrar receber 3 parametros //
                    ///////////////////////////////////////////////////////////////////////////////////
                    ceButton.appendChild(ceImg);
                    cePar.appendChild(ceButton);
                    ceDiv.appendChild(cePar);
                    if (reg.ajuda != undefined)
                        ceDiv.appendChild(ceSpan);
                    dPaiD.appendChild(ceDiv);
                    margTop = (margTop + 14);
                };
            });
        };
        //////////////////////////////////////////////////////
        // CREATE ELEMENT                                   //
        // dPai                                             //
        //   -dPaiE  ( div da esquerda )                    //
        //     -dPaiETop  (Depende do parametro opcRegSeek) //
        //     -dPaiETab                                    //      
        //     -dPaiEBot                                    //
        //   -dPaiD  ( div da direita )                     //  
        //////////////////////////////////////////////////////
        var opcaoTopo = true;
        if (tagValida(self.Js.opcRegSeek))
            opcaoTopo = self.Js.opcRegSeek;
        //
        var dPai = document.createElement("div");
        dPai.id = "dPai" + self.Js.div;

        if ((tagValida(self.Js.tableLeft)) && (self.Js.tableLeft == "sim")) {
            dPai.className = "tableLeft";
        } else {
            dPai.className = "tableCenter";
        }
        dPai.style.borderRadius = "6px 6px 6px 6px";
        dPai.style.height = (tagValida(self.Js.height) ? self.Js.height : "40em");
        dPai.style.width = (parseInt((tagValida(self.Js.width) ? self.Js.width : "60em").soNumeros()) + (bBotaoD ? 10 : 0)) + "em";
        if (tagValida(self.Js.position)) {
            dPai.style.position = self.Js.position
            dPai.style.float = "left"
        } else {
            dPai.style.position = "absolute";
        }
        var dPaiE = document.createElement("div"); //Div principal contendo as outras 3 (dPaiETop/dPaiETab/dPaiEBot)
        dPaiE.id = "dPaiE" + self.Js.div;
        dPaiE.name = "dPaiE" + self.Js.div;
        dPaiE.style.cssFloat = "left";
        dPaiE.style.borderRadius = "6px 6px 6px 6px";
        dPaiE.style.height = (tagValida(self.Js.height) ? self.Js.height : "40em");
        dPaiE.style.width = (tagValida(self.Js.width) ? self.Js.width : "60em");
        dPaiE.style.position = "absolute";
        dPaiE.style.border = "1px solid silver";
        dPaiE.style.backgroundColor = "white";

        var dPaiETop = document.createElement("div");
        dPaiETop.id = "dPaiETop" + self.Js.div;
        dPaiETop.className = "campotexto campo100 divProcurar";
        dPaiETop.style.height = "3em";
        dPaiETop.style.top = "0em";
        dPaiETop.style.margin = margem;
        dPaiETop.style.width = taman;
        dPaiETop.style.border = "1px solid silver";
        //dPaiETop.style.display     = "none";

        var dPaiETopOpc = document.createElement("div"); //Div botao opção
        var dPaiETopLab = document.createElement("div"); //Div label para dPaiTopInp
        var dPaiETopInp = document.createElement("div"); //Div input

        var dPaiETab = document.createElement("div"); //Div table
        dPaiETab.name = "dPaiETab" + self.Js.div;
        dPaiETab.id = "dPaiETab" + self.Js.div;
        dPaiETab.className = "divContainerTable";
        //akiiii    
        dPaiETab.style.height = (bBotaoH ? "calc(100% - 12.4em)" : "calc(100% - 6em)"); // 7em=3em Top+4em Bot
        ////////////////////////////////////////////////////////////
        // Se a opção for para não montar a div de registros/seek //
        ////////////////////////////////////////////////////////////
        if (opcaoTopo == false) {
            dPaiETab.style.height = (dPaiETab.style.height).replaceAll("6em", "3em");
            dPaiETab.style.height = (dPaiETab.style.height).replaceAll("10.4em", "6em");
        };
        dPaiETab.style.margin = margem;
        dPaiETab.style.width = taman

        var dPaiEBot = document.createElement("div"); //Div botoes
        dPaiEBot.id = "dPaiEBot" + self.Js.div;
        dPaiEBot.name = "dPaiEBot" + self.Js.div;
        dPaiEBot.style.height = "6em";
        dPaiEBot.style.bottom = "2px";
        dPaiEBot.style.paddingTop = "0.4em";
        dPaiEBot.className = "campo100";
        dPaiEBot.style.position = "absolute";
        dPaiEBot.style.margin = margem;
        dPaiEBot.style.width = taman;

        if ((self.tblF10 == false) && (tagValida(self.Js.menuTable) == false) && (tagValida(self.Js._menuTable) == false)) {
            registros = (tagValida(self.Js.lblRegistros) ? self.Js.lblRegistros : 'REGISTROS:');
            dPaiETopOpc.className = "campo25 divProcurarReg";
            dPaiETopOpc.style.cssFloat = "left";
            dPaiETopOpc.style.marginTop = "0.4em";
            ceLabel = document.createElement("label");
            ceLabel.id = "lblReg" + self.Js.prefixo;
            ceContext = document.createTextNode(registros + (self.Js.registros.length).EmZero(4));
            ceLabel.appendChild(ceContext);
            dPaiETopOpc.appendChild(ceLabel);
        };
        //////////////////////////////
        // Calculando tamanho table //
        //////////////////////////////
        var tamanho = 0;
        self.Js.titulo.forEach(function(ttl) {
            if (tagValida(ttl.tamGrd))
                tamanho += parseInt(ttl.tamGrd);
        });
        dTable = document.createElement("table");
        dTable.name = self.Js.tbl;
        dTable.id = self.Js.tbl;
        dTable.className = "bisTable";
        dTable.style.width = tamanho + "em";
        dTable.tableLayout = "fixed";
        ///////////////////////////////////////////////////////////
        // Nova opção para menu em table com somente uma chamada //
        ///////////////////////////////////////////////////////////
        if (tagValida(self.Js._menuTable)) {
            ////////////////////////////////////////////////////////////
            // Procurando o tamanho do botão e o tamanho da div itens //
            ////////////////////////////////////////////////////////////
            if (tagValida(self.Js.tamMenuTable)) {
                tamTit = self.Js.tamMenuTable[0];
                tamIte = self.Js.tamMenuTable[1];
            };
            dPaiETopOpc.className = "campo25 tableDivProcurarReg";
            //dPaiETopOpc.id="menuNav";
            ceNav = document.createElement("nav");
            //ceNav.id="menuNav";

            ceUlPai = document.createElement("ul");
            ceUlPai.className = "menuSmall";
            ceUlPai.style.borderRadius = "6px 0px 0px 0px";
            ceUlPai.style.marginLeft = "0.2em";
            ceLiPai = document.createElement("li");
            ceLiPai.style.width = tamTit;

            ceAnc = document.createElement("a");
            ceAnc.className = "homer";
            ceAnc.href = "#";

            ceImg = document.createElement("i");
            ceImg.className = "menuImagem fa fa-folder-open";
            ceAnc.appendChild(ceImg);

            ceContext = document.createTextNode(tagValida(self.Js.labelMenuTable) ? self.Js.labelMenuTable : "Opções");
            ceAnc.appendChild(ceContext);
            ceLiPai.appendChild(ceAnc);

            ceUl = document.createElement("ul");
            ceUl.style.width = tamIte;
            ceUl.style.left = "-0.5em";
            ceUl.className = "sub-menuSmall";
            ceUl.id = "subUl";

            nItens = 0;
            var tamMt = self.Js._menuTable.length;
            for (var linMt = 0; linMt < tamMt; linMt++) {
                nItens++;
                ceLi = document.createElement("li");
                ceAnc = document.createElement("a");
                ceAnc.href = "#";
                ////////////////////////////////////////////////////////////////////////////////////////
                // 24nov2017                                                                          //  
                // Alterado chamada para se poder chamar uma função com parametro ex: ERP_CfoRelVenda //
                //  ,"funcMenuTable"  :["btnFiltrarClick('S');"                                       //
                //                     ,"btnFiltrarClick('N');"                                       //
                //                     ,"btnFiltrarClick('*');"]                                      //
                ////////////////////////////////////////////////////////////////////////////////////////
                if (self.Js._menuTable[linMt][2].indexOf("(") == -1) {
                    ceAnc.setAttribute("onclick", self.Js._menuTable[linMt][2] + '();');
                } else {
                    ceAnc.setAttribute("onclick", self.Js._menuTable[linMt][2]);
                };
                ceImg = document.createElement("i");
                ceImg.className = "subMenuImagem fa " + self.Js._menuTable[linMt][1]
                ceAnc.appendChild(ceImg);

                ceContext = document.createTextNode(' ' + self.Js._menuTable[linMt][0]);
                ceAnc.appendChild(ceContext);

                ceLi.appendChild(ceAnc);
                ceUl.appendChild(ceLi);
            };
            ceLiPai.appendChild(ceUl);
            ceUlPai.appendChild(ceLiPai);
            ceNav.appendChild(ceUlPai);
            dPaiETopOpc.appendChild(ceNav);
        };

        /*
    if( tagValida(self.Js._menuTable) ){
      alert('oi');
    var ht=
				'<section class="sidebar">'+
					'<ul class="sidebar-menu" data-widget="tree">'+
						'<li class="treeview">'+
							'<a href="#">'+
								'<i class="fa fa-dashboard"></i> <span>Dashboard</span>'+
								'<span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>'+
							'</a>'+
							'<ul class="treeview-menu">'+
								'<li><a href="#"><i class="fa fa-circle-o"></i> Dashboard v1</a></li>'+
								'<li><a href="#"><i class="fa fa-circle-o"></i> Dashboard v2</a></li>'+
							'</ul>'+
						'</li>'+
          '</ul>'+
        '</section>';
    dPaiETopOpc.innerHTML=ht;
    };
    */
        ////////////////////////////
        // Label para campo imput //
        ////////////////////////////
        dPaiETopLab.className = "campo25 tableDivProcurarLabel";
        ceLabel = document.createElement("label");
        ceLabel.cssFloat = "right";
        ceLabel.id = "lblProcurar_" + self.Js.tbl;
        ceContext = document.createTextNode(self.Js.indiceTable);
        ceLabel.appendChild(ceContext);
        dPaiETopLab.appendChild(ceLabel);

        dPaiETopInp.className = (self.tblF10 ? "campo75" : "campo50") + " tableDivProcurarInput";
        var cePar = document.createElement("div");
        ceInput = document.createElement('input');
        ceInput.type = "text";
        ceInput.name = "txtProcurar_" + self.Js.div;
        ceInput.id = "txtProcurar_" + self.Js.div;
        ceInput.placeholder = "Informe texto para filtro";

        var param = ['', '', ''];
        param[0] = ttl;
        param[1] = "\'" + self.Js.tbl + "'";
        param[2] = 'txtProcurar_' + self.Js.div + '.value';
        param[3] = 'lblProcurar_' + self.Js.tbl + '.innerHTML';
        //ceInput.setAttribute("onkeyup","filtrarReg(ttl,"+param[1]+","+param[2]+","+param[3]+")");
        var func = self.obj + '.filtraTbl(' + param[1] + ',' + param[2] + ',' + param[3] + ');';
        ceInput.setAttribute("onkeyup", func);

        ceImg = document.createElement("i");
        ceImg.setAttribute("class", "faInp fa-pencil-square-o icon-large");

        cePar.appendChild(ceInput);
        cePar.appendChild(ceImg);

        dPaiETopInp.appendChild(cePar);
        if (self.tblF10 == false)
            dPaiETop.appendChild(dPaiETopOpc);
        dPaiETop.appendChild(dPaiETopLab);
        dPaiETop.appendChild(dPaiETopInp);
        ////////////////////////////
        // Somente se for um F10  //
        ////////////////////////////
        if (self.tblF10) {
            /////////////////////////////////////////////////////////////////////////////
            // o "+1" é pq foi presumido qque o proximo comando será um '.show()' onde //
            // os objetos serao criados com (contMsg++)                                //   
            /////////////////////////////////////////////////////////////////////////////
            var lblCls = 'lcls' + (contMsg + 1); //para remover a mensagem quando do confirmar
            var btnF10 = 'btn' + (contMsg + 1);
            var btnF10f = 'btn' + (contMsg + 1) + 'click()';
            //
            var cePar = document.createElement("div");
            cePar.className = "campo25";
            cePar.style.cssFloat = "right";

            ceButton = document.createElement('button');
            ceButton.id = btnF10;
            ceButton.innerHTML = "Confirmar";
            ceButton.className = "campo100 tableBotao botaoHorizontal";
            ceButton.addEventListener('click', function() {
                try {
                    var clsChecados = self.gerarJson("1"); //Classe para buscar registros checados
                    var chkds = clsChecados.gerar(); //Retorna um array associativo de todos registros checados
                    var tamC = chkds.length; //Tamanho do array chkds
                    for (var checados = 0; checados < tamC; checados++) {
                        eval('RetF10' + self.Js.tbl + '(chkds)');
                        document.getElementById(lblCls).click();
                        ///////////////////////////////////////////////////
                        // Onde vai o foco quando confirmar o formulario //
                        ///////////////////////////////////////////////////
                        if (self.focoF10 != undefined) {
                            document.getElementById(self.focoF10).focus();
                        };
                    };
                    clsChecados = null;
                } catch (e) {
                    gerarMensagemErro("WHR", e, "Erro");
                };
            });
            //////////////////////
            // Botao com imagem //
            //////////////////////  
            /*
            ceImg= document.createElement("i");           
            ceImg.className="faBtn fa-save icon-large";
            cePar.appendChild(ceButton);
            cePar.appendChild(ceImg);
            dPaiEBot.appendChild(cePar);
            */
            //////////////////////
            // Botao sem imagem //
            //////////////////////  
            cePar.appendChild(ceButton);
            dPaiEBot.appendChild(cePar);
        };
        //  
        ceThead = document.createElement("thead");
        ceTr = document.createElement("tr");

        self.Js.titulo.forEach(function(ttl) {
            cls = [];
            ceTh = document.createElement("th");

            if (tagValida(ttl.tamGrd)) {
                ceTh.setAttribute("style", "width:" + ttl.tamGrd);
                if (ttl.tamGrd == '0em')
                    ceTh.setAttribute("class", "colunaOculta");
            };
            if (tagValida(ttl.tamGrd) == false)
                ceTh.setAttribute("class", "colunaOculta");

            if (tagValida(ttl.classe))
                ceTh.setAttribute("class", ttl.classe);
            ////////////////////////////////////////////
            // Para ver se a coluna pode ser ordenada //
            ////////////////////////////////////////////
            var ordn = (((tagValida(ttl.ordenaColuna)) && ((ttl.ordenaColuna).toUpperCase() == 'S')) ? 'S' : 'N');
            if (ordn == "S")
                func = ' ' + self.obj + '.ordenaJSon("' + ttl.labelCol + '",true);';
            else
                func = 'gerarMensagemErro("catch","COLUNA NÃO PODE SER ORDENADA!","AVISO");';
            ceTh.setAttribute("onclick", func);
            ceContext = document.createTextNode(ttl.labelCol);
            ceTh.appendChild(ceContext);
            ceTr.appendChild(ceTh);
        });
        ceThead.appendChild(ceTr);
        dTable.appendChild(ceThead);
        ////////////////////////////////////
        // MONTANDO OS BOTÕES SE EXISTIR  //
        ////////////////////////////////////
        if (tagValida(self.Js.tamBotao))
            tamBotao = self.Js.tamBotao;

        if (tagValida(self.Js.botoesH)) {
            var cePar = document.createElement("div");
            cePar.className = "divBotaoImagemSup";

            self.Js.botoesH.forEach(function(reg, lin) {
                if ((tagValida(reg.enabled)) && (reg.enabled == true)) {
                    var qualTamBotao = (tagValida(reg.tamBotao) ? reg.tamBotao : tamBotao);
                    ////////////////////////////////////////////////
                    // Opção 7 a func deve ser criada manualmente //
                    ////////////////////////////////////////////////
                    /*
                    var cePar=document.createElement("div");
                    cePar.className      = "campo"+qualTamBotao;
                    cePar.style.cssFloat = "left";
          
                    ceButton=document.createElement('div');
                    ceButton.id             = reg.name;
                    ceButton.innerHTML      = reg.texto;
                    ceButton.className      = "campo100 tableBotao botaoHorizontal";
                    ceButton.style.cssFloat = "left";
                    ceButton.style.fontSize = '14px';
          
                    //ceImg= document.createElement("i");
                    //ceImg.className=reg.imagem;
          
                    cePar.appendChild(ceButton);
                    //cePar.appendChild(ceImg);
                    dPaiEBot.appendChild(cePar);
                    */
                    //cePar.style.cssFloat = "left";
                    ceButton = document.createElement('div');
                    ceButton.id = reg.name;
                    //ceButton.innerHTML      = reg.texto;
                    ceButton.className = "botaoImagemSup-icon-big";



                    ceImg = document.createElement("i");
                    ceImg.className = reg.imagem;
                    ceButton.appendChild(ceImg);

                    ceContext = document.createTextNode(reg.texto);
                    ceButton.appendChild(ceContext);

                    cePar.appendChild(ceButton);
                    dPaiEBot.appendChild(cePar);
                };
            });
        };
        dPaiETab.appendChild(dTable);
        if (opcaoTopo)
            dPaiE.appendChild(dPaiETop);
        dPaiE.appendChild(dPaiETab);
        if (bBotaoH)
            dPaiE.appendChild(dPaiEBot);

        dPai.appendChild(dPaiE);
        if (bBotaoD)
            dPai.appendChild(dPaiD);
        ////////////////////////////////////////////////////////////////////////////////
        // obj=objeto ja existente no html                                            //
        // nextSibling = proximo objeto depois do ja existente antes do proximo irmão //
        ////////////////////////////////////////////////////////////////////////////////
        if (self.tblF10) {
            return dPai;
        }

        var obj = document.getElementById(self.Js.divModal);
        obj.parentNode.insertBefore(dPai, obj.nextSibling);
        ceDiv = document.createElement('div');
        ceDiv.id = self.Js.div + 'Modal';
        ceDiv.className = "divShowModal";
        ceDiv.style.display = "none";
        document.getElementsByTagName('body')[0].appendChild(ceDiv);
        //////////////////////////////////////////////////////////////////////////////
        // Cria os eventos dos botões se existir a chamada botoes no json           //
        // Deve ser criado depois devido self.cadastrar poder receber 3 parametros  //
        //////////////////////////////////////////////////////////////////////////////
        var tagV = new Array(self.Js.botoesH, self.Js.botoesD);
        for (var lin = 0; lin < tagV.length; lin++) {
            if (tagValida(tagV[lin])) {
                tagV[lin].forEach(function(reg, lin) {
                    if ((tagValida(reg.enabled)) && (reg.enabled == true)) {
                        if (tagValida(reg.onClick)) {
                            document.getElementById(reg.name).addEventListener('click', function() {
                                self.status = parseInt(reg.onClick);
                                switch (reg.onClick) {
                                    case '0':
                                    case '1':
                                    case '2':
                                        //26mar2018-Table invisivel qdo cad/alt/exc
                                        //document.getElementById('dPai'+self.Js.form).style.display = 'none';
                                        //
                                        self.cadastrar(self.status);
                                        break;
                                    case '3':
                                        self.imprimir();
                                        break;
                                    case '4':
                                        self.detalhe();
                                        break;
                                    case '5':
                                        self.excel();
                                        break;
                                    case '6':
                                        window.close();
                                        break; //Este quando não esta em um iFrame
                                    case '8':
                                        window.parent.document.getElementById(self.iFrame).src = "";
                                        break; //Este quando esta em um iFrame
                                };
                            });
                        };
                        if (reg.onClick == '7')
                            document.getElementById(reg.name).setAttribute("onclick", reg.name + 'Click();');
                    };
                });
            };
        };
        //////////////////////////////////////////////////
        // Executando o botão cancelar do form/fieldSet //
        //////////////////////////////////////////////////
        if (tagValida(self.Js.idBtnCancelar)) {
            if (document.getElementById(self.Js.idBtnCancelar) != null) {
                document.getElementById(self.Js.idBtnCancelar).addEventListener('click', function() {
                    self.retirarModal2017();
                    //26mar2018-Table invisivel qdo cad/alt/exc
                    //document.getElementById('dPai'+self.Js.form).style.display = 'block';
                    //
                });
            };
        };
        if (tagValida(self.Js.idBtnConfirmar)) {
            if (document.getElementById(self.Js.idBtnConfirmar) != null) {
                document.getElementById(self.Js.idBtnConfirmar).addEventListener('click', function() {
                    self.gravar();
                });
            };
        };
        this.ordenaJSon(self.Js.indiceTable, false);
        this.montarBody2017();
    };
    //
    this.montarBody2017 = function() {
        //////////////////////////////////////////////////////////////////////
        // Toda atualização da grade é feita atraves de um vetor não assoc  //
        //////////////////////////////////////////////////////////////////////
        var mbReg;
        var objBody = ((document.getElementById("tbody_" + self.Js.tbl) == null) ? false : true);

        if (objBody == true)
            document.getElementById("tbody_" + self.Js.tbl).remove();

        var mbBody = document.createElement("tbody");
        mbBody.setAttribute("id", "tbody_" + self.Js.tbl);

        mbReg = self.Js.registros;

        var cnvData; //variavel converteData;
        var newIdUnico;
        mbReg.forEach(function(reg, lin) {
            var iC = 0;
            cnvData = '';
            ceTr = document.createElement("tr");
            ceTr.setAttribute("class", (lin % 2 ? "corGradeImpar" : "corGradePar"));

            self.Js.titulo.forEach(function(tit) {
                ceTd = document.createElement("td");

                if ((tagValida(tit.tamGrd) == false) || (tit.tamGrd == '0em'))
                    ceTd.classList.add("colunaOculta");

                if (tagValida(tit.classe))
                    ceTd.classList.add(tit.classe);

                switch (tit.fieldType) {
                    /*
                    case 'chk':
                      ceInput=document.createElement('input');      
                      ceInput.setAttribute("type","checkbox");
                      ceInput.setAttribute("class","tdInput");
                      ceInput.setAttribute("name",tit.obj);
                      ceInput.setAttribute("onclick","linhaChecada(this,"+tit.id+")");
                      ceTd.appendChild(ceInput);
                      break;
                    */
                    case 'chk':
                        ceInput = document.createElement('input');
                        ceInput.setAttribute("type", "checkbox");
                        ceInput.setAttribute("class", "tdInput");
                        ceInput.setAttribute("name", tit.obj);
                        /*  
                        if (self.tblF10==true){
                          ceTr.addEventListener('click',function(){
                            this.cells[0].children[0].checked = (this.cells[0].children[0].checked == false);
                            if (this.cells[0].children[0].checked==true)
                              this.cells[0].parentNode.classList.add('corGradeParCheck');
                            else 
                              this.cells[0].parentNode.classList.remove('corGradeParCheck');
                          });
                          ceTr.addEventListener('dblclick',function(){
                            // desmarca todos
                            var check = this.parentNode.getElementsByTagName('input');
                            for(var i=0;i<check.length;i++){
                              if((check[i].checked!=undefined)&&(check[i].checked==true)){
                                check[i].checked = false;
                                check[i].parentNode.parentNode.classList.remove('corGradeParCheck');
                              }
                            }
                            // marca este
                            this.cells[0].children[0].checked = true;
                            this.cells[0].parentNode.classList.add('corGradeParCheck');
                            // seleciona
                            // tr,table,div,div -> button
                            this.parentNode.parentNode.parentNode.parentNode.getElementsByTagName('button')[0].click();
                          });
                          ceInput.setAttribute("onclick","this.checked=(this.checked==false)");
                        } else 
                          ceInput.setAttribute("onclick","linhaChecada(this,"+tit.id+")");
                        */
                        //25mai2018
                        if (self.tblF10 == true) {
                            ceTr.addEventListener('click', function() {
                                this.cells[0].children[0].checked = (this.cells[0].children[0].checked == false);
                                if (this.cells[0].children[0].checked == true)
                                    this.cells[0].parentNode.classList.add('corGradeParCheck');
                                else
                                    this.cells[0].parentNode.classList.remove('corGradeParCheck');
                            });
                            ceTr.addEventListener('dblclick', function() {
                                // desmarca todos
                                var check = this.parentNode.getElementsByTagName('input');
                                for (var i = 0; i < check.length; i++) {
                                    if ((check[i].checked != undefined) && (check[i].checked == true)) {
                                        check[i].checked = false;
                                        check[i].parentNode.parentNode.classList.remove('corGradeParCheck');
                                    }
                                }
                                // marca este
                                this.cells[0].children[0].checked = true;
                                this.cells[0].parentNode.classList.add('corGradeParCheck');
                                // seleciona
                                // tr,table,div,div -> button
                                this.parentNode.parentNode.parentNode.parentNode.getElementsByTagName('button')[0].click();
                            });
                            ceInput.setAttribute("onclick", "this.checked=(this.checked==false)");
                        } else {
                            ceTr.addEventListener('click', function() {
                                this.cells[0].children[0].checked = (this.cells[0].children[0].checked == false);
                                if (this.cells[0].children[0].checked == true)
                                    this.cells[0].parentNode.classList.add('corGradeParCheck');
                                else
                                    this.cells[0].parentNode.classList.remove('corGradeParCheck');
                            });
                        };
                        ceTd.appendChild(ceInput);
                        break;

                    case 'img':
                        var tagI = '';
                        if (tagValida(tit.tagI))
                            tagI = tit.tagI;
                        newFunc = '';
                        if (tagValida(tit.func))
                            newFunc = ' ' + tit.func;
                        //
                        ceTd.setAttribute("class", "tdInput");
                        _divTd = document.createElement("div");
                        _divTd.setAttribute("width", "100%");
                        _divTd.setAttribute("height", "100%");
                        if (newFunc != '')
                            _divTd.setAttribute("onclick", newFunc);

                        ceImg = document.createElement("i");
                        ceImg.setAttribute("name", tit.obj);
                        ceImg.setAttribute("class", tagI);
                        ceImg.setAttribute("style", "margin-left:10px");
                        _divTd.appendChild(ceImg);
                        ceTd.appendChild(_divTd);
                        break;
                    case 'idUnico':
                        //////////////////////////////////////////////////////////////////////////////////////
                        // 24nov2017                                                                        //
                        // Se estiver alterando [!= undefined] o idUnico ja existe e não pode ser alterado  //
                        // Se estiver incluido  [== undefined] o idUnico tem que ser criado                 //
                        //////////////////////////////////////////////////////////////////////////////////////
                        if (reg[iC] != undefined) {
                            ceContext = document.createTextNode(reg[iC++]);
                        } else {
                            ceContext = document.createTextNode(self.addId[0]);
                            self.addId[0]++;
                        }
                        ceTd.appendChild(ceContext);
                        break;

                    case 'str':
                        ceContext = document.createTextNode(reg[iC++]);
                        ceTd.appendChild(ceContext);

                        if ((tagValida(tit.align)) && (tit.align == "center")) {
                            ceTd.classList.add("textoCentro");
                        };

                        if (tagValida(tit.funcCor))
                            eval(tit.funcCor.replaceAll('objCell', 'ceTd'));
                        break;
                    case 'flo':
                    case 'flo2':
                        ceContext = document.createTextNode(cmp.floatNB(reg[iC++]));
                        ceTd.appendChild(ceContext);
                        ceTd.classList.add("textoDireita");
                        if (tagValida(tit.funcCor))
                            eval(tit.funcCor.replaceAll('objCell', 'ceTd'));
                        break;
                    case 'flo4':
                        ceContext = document.createTextNode(cmp.floatNB4(reg[iC++]));
                        ceTd.appendChild(ceContext);
                        ceTd.classList.add("textoDireita");
                        break;
                    case 'flo8':
                        ceContext = document.createTextNode(cmp.floatNB8(reg[iC++]));
                        ceTd.appendChild(ceContext);
                        ceTd.classList.add("textoDireita");
                        break;
                    case 'int':
                        conteudo = reg[iC++];
                        if (tagValida(tit.formato)) {
                            frmt = tit.formato;
                            for (li in frmt) {
                                switch (frmt[li].toUpperCase()) {
                                    case 'I2':
                                        conteudo = (parseInt(conteudo)).EmZero(2);
                                        break;
                                    case 'I3':
                                        conteudo = (parseInt(conteudo)).EmZero(3);
                                        break;
                                    case 'I4':
                                        conteudo = (parseInt(conteudo)).EmZero(4);
                                        break;
                                    case 'I5':
                                        conteudo = (parseInt(conteudo)).EmZero(5);
                                        break;
                                    case 'I6':
                                        conteudo = (parseInt(conteudo)).EmZero(6);
                                        break;
                                    case 'I7':
                                        conteudo = (parseInt(conteudo)).EmZero(7);
                                        break;
                                    case 'I8':
                                        conteudo = (parseInt(conteudo)).EmZero(8);
                                        break;
                                    case 'I9':
                                        conteudo = (parseInt(conteudo)).EmZero(9);
                                        break;
                                };
                            };
                        };
                        ceContext = document.createTextNode(conteudo);
                        ceTd.appendChild(ceContext);
                        if (tagValida(tit.align)) {
                            switch (tit.align) {
                                case "center":
                                    ceTd.classList.add("textoCentro");
                                    break;
                                case "left":
                                    ceTd.classList.add("textoEsquerdo");
                                    break;
                                default:
                                    ceTd.classList.add("textoDireita");
                                    break;
                            };
                        } else {
                            ceTd.classList.add("textoDireita");
                        }
                        //ceTd.classList.add("textoDireita");            
                        //ceTd.classList.add("textoCentro");            
                        if (tagValida(tit.funcCor))
                            eval(tit.funcCor.replaceAll('objCell', 'ceTd'));
                        break;

                    case "dat":
                        cnvData = reg[iC++];
                        ceContext = document.createTextNode((cnvData == "" ? "" : (cnvData[4] == "-" ? jsDatas(cnvData).retDDMMYYYY() : cnvData)));
                        ceTd.appendChild(ceContext);
                        if (tagValida(tit.funcCor))
                            eval(tit.funcCor.replaceAll('objCell', 'ceTd'));
                        ceTd.classList.add("textoDireita");
                        break;
                };
                ceTr.appendChild(ceTd);
            });
            //////////////////////////////////
            // Formatando a linha da table  //
            //////////////////////////////////
            if (tagValida(self.Js.corLinha)) {
                eval(self.Js.corLinha);
            }
            //
            mbBody.appendChild(ceTr);
        });
        document.getElementById(self.Js.tbl).appendChild(mbBody);
        //
        //
        ///////////////////////////////////////
        // esconde e mostra o menu de opções //
        ///////////////////////////////////////
        if (tagValida(self.Js._menuTable)) {
            var submenu = document.getElementsByClassName('sub-menuSmall');
            for (var x = 0; x < submenu.length; x++) {
                submenu[x].addEventListener('click', function() {
                    this.style.visibility = 'hidden';
                });
            }

            var menu = document.getElementsByClassName('menuSmall')[0];
            menu.addEventListener('mouseover', function() {
                for (var x = 0; x < submenu.length; x++) {
                    if (submenu[x].style.visibility != 'visible')
                        submenu[x].style.visibility = 'visible';
                };
            });
        };
        //
        //
        /////////////////////////////////////////
        // Atualizando o contador de registros //
        /////////////////////////////////////////
        if ((tagValida(self.Js.menuTable) == false) && (tagValida(self.Js._menuTable) == false) && (self.tblF10 == false)) {
            //////////////////////////////////////////////////////////////////////////////////
            // Existe no cadastro de tarefas três opções que não tem a opcao Registros/seek //
            // ERP_Agenda2017.php                                                           //
            //////////////////////////////////////////////////////////////////////////////////
            if (self.Js.opcRegSeek != false) {
                document.getElementById("lblReg" + self.Js.prefixo).innerHTML = "REGISTROS:" + (self.Js.registros.length).EmZero(4);
            };
        };
    };
    //////////////////////////////////////////////////////////////////////////////
    // A pk pode vir de duas maneiras                                           //  
    // "edt" Vem quando se esta cadastrando ou alterando/excluindo um registro  //
    // "tbl" Vem de uma table Ex:buscar um registro para espiao                 //
    // "xxx" Valor que vem da função chamadora Ex alterar campo ativo/sys       //
    //////////////////////////////////////////////////////////////////////////////
    this.cmdWhere = function(qual) {
        var retorno = '';
        var cntd;
        var indice = 0; //se for uma PK composta recebo os valores separados por virgula em um objeto
        var tblRowIndex; //A linha da table onde vai ser alterado o registro 

        self.Js.titulo.forEach(function(tit) {
            if (tit.pk == 'S') {
                if (qual == 'edt')
                    cntd = (document.getElementById(tit.obj).value).campoSql(tit.fieldType);
                else if (qual == 'tbl') {
                    var obj = document.getElementById(self.Js.tbl);
                    var clsChecados = self.gerarJson("1"); //Classe para buscar registros checados
                    var chkds = clsChecados.gerar(); //Retorna um array associativo de todos registros checados
                    var tamC = chkds.length; //Tamanho do array chkds
                    for (var checados = 0; checados < tamC; checados++) {
                        var tblRowIndex = self.buscaRowIndexTable(chkds[checados]._ID);
                        cntd = (obj.rows[tblRowIndex].cells[tit.id].innerHTML).campoSql(tit.fieldType);
                    };
                } else {
                    var ceInput = document.createElement('input');
                    if (typeof(qual) == "object") {
                        ceInput.value = qual[indice];
                        indice++;
                    } else
                        ceInput.value = qual;
                    cntd = (ceInput.value).campoSql(tit.fieldType);
                    ceInput.remove();
                };
                retorno += (retorno == '' ?
                    ' WHERE (' + tit.field + '=' + cntd + ')' :
                    ' AND (' + tit.field + '=' + cntd + ')');
            };
        });
        try {
            if (retorno == '')
                throw "Não localizado comando 'where'!";
            else
                return retorno;
        } catch (e) {
            gerarMensagemErro("WHR", e, "Erro");
        };
    };
    //
    this.cmdInsert = function() {
        var retorno = '';
        var cntd = '';
        var ladoField = '';
        var ladoValor = '';
        self.Js.titulo.forEach(function(tit) {
            if ((tagValida(tit.insUpDel)) && (tit.insUpDel[0] == 'S')) {
                ladoField += (ladoField == '' ? tit.field : ',' + tit.field);
                cntd = (document.getElementById(tit.obj).value).campoSql(tit.fieldType);
                ladoValor += (ladoValor == '' ? cntd : ',' + cntd);
            };
        });
        retorno = 'INSERT INTO ' + self.Js.tabelaBD + '(' + ladoField + ') VALUES(' + ladoValor + ')';
        return retorno;
    };
    this.cmdUpdate = function(qual) {
        var retorno = '';
        var cntd = '';
        var sql = '';
        self.Js.titulo.forEach(function(tit) {
            if ((tagValida(tit.insUpDel)) && (tit.insUpDel[1] == 'S')) {
                cntd = (document.getElementById(tit.obj).value).campoSql(tit.fieldType);
                sql += (sql == '' ?
                    tit.field + '=' + cntd :
                    ',' + tit.field + '=' + cntd);
            };
        });
        retorno = 'UPDATE ' + self.Js.tabelaBD + ' SET ' + sql + ' ' + this.cmdWhere(qual);
        return retorno;
    };
    this.cmdDelete = function(qual) {
        var retorno = '';
        retorno = 'DELETE FROM ' + self.Js.tabelaBD + ' ' + this.cmdWhere(qual);
        return retorno;
    };
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Exemplo <option value=2>LUCRO PRESUMIDO</option>                                                                         //
    // valueOpt=true  retorna o value(2), o cntd=conteudo deve ser o label do option(LUCRO PRESUMIDO) Para atualizar o combo/Bd //
    // valueOpt=false retorna o label do option(LUCRO PRESUMIDO), o cntd=conteudo é selectedIndex     Para atualizar a grade    //
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    this.comboBox = function(cb, valueOpt, cntd, copyGrd) {
        objCb = document.getElementById(cb);
        var tam = objCb.length;
        var retorno;
        var seek;
        if (valueOpt) {
            for (var lin = 0; lin < tam; lin++) {
                seek = objCb[lin].text;
                if (tagValida(copyGrd))
                    seek = seek.substring(copyGrd[0], copyGrd[1]);

                if (seek == cntd) {
                    retorno = objCb[lin].value;
                    break;
                };
            };
        } else {
            retorno = objCb[objCb.selectedIndex].text;
            if (tagValida(copyGrd))
                retorno = retorno.substring(copyGrd[0], copyGrd[1]);
        };
        return retorno;
    };
    //////////////////////////
    // Retorna o JS origem  //
    //////////////////////////
    this.retornarJS = function() {
        return self.Js;
    };
    ////////////////////////////////////////
    // Chamando o formulario PassoAPasso  //
    ////////////////////////////////////////
    /*
    this.espiao=function(){
      try{
        var objTbl = document.getElementById(self.Js.tbl); 
        var clsChecados = self.gerarJson("1");  //Classe para buscar registros checados
        var chkds       = clsChecados.gerar();  //Retorna um array associativo de todos registros checados
        var tamC        = chkds.length;         //Tamanho do array chkds
        ////////////////////////////////////////////////////////////////////////
        // A função verTags2017() garante as tags formPassoPasso e tabelaBKP  //
        ////////////////////////////////////////////////////////////////////////
        for( var checados=0; checados<tamC; checados++ ){        
          if( tagValida(self.Js.formPassoPasso) ){        
            var campo = self.cmdWhere('tbl');
            var lsJS  = '{"espiao":[{"tabela":"'+self.Js.tabelaBKP+'","campo":"'+campo+'"}]}';
            localStorage.setItem("envJson",lsJS);
            window.open(self.Js.formPassoPasso);
          } else {
            console.log('Erro na tag formPassoPasso');
          }; 
        };
        clsChecados = null;
      } catch(e){
        gerarMensagemErro('WHR',e);    
      };
    };
    */
    this.espiao = function() {
        try {
            var objTbl = document.getElementById(self.Js.tbl);
            var clsChecados = self.gerarJson("1"); //Classe para buscar registros checados
            var chkds = clsChecados.gerar(); //Retorna um array associativo de todos registros checados
            var arrW = [];
            var campo = ""; //Deve ser "";
            var tblRowIndex;
            var cntd;
            ////////////////////////////////////////////////////////////////////////
            // A função verTags2017() garante as tags formPassoPasso e tabelaBKP  //
            ////////////////////////////////////////////////////////////////////////
            if (tagValida(self.Js.formPassoPasso)) {
                var tamanho = self.Js.titulo.length;
                tblRowIndex = self.buscaRowIndexTable(chkds[0]._ID);
                ////////////////////////////////////////////////////////////////////////////////////
                // Buscando a chave primaria para select pois existem tabelas com até 5 campos PK //
                ////////////////////////////////////////////////////////////////////////////////////
                for (var linha = 0; linha < tamanho; linha++) {
                    if (self.Js.titulo[linha].pk == "S") {
                        if (self.Js.titulo[linha].tipo == 'cb') {
                            cntd = self.comboBox(self.Js.titulo[linha].obj, true, objTbl.rows[tblRowIndex].cells[self.Js.titulo[linha].id].innerHTML, self.Js.titulo[linha].copyGRD);
                            cntd = cntd.campoSql(self.Js.titulo[linha].fieldType)
                        } else {
                            cntd = (objTbl.rows[tblRowIndex].cells[self.Js.titulo[linha].id].innerHTML).campoSql(self.Js.titulo[linha].fieldType);
                        };
                        arrW.push({
                            "campo": self.Js.titulo[linha].field,
                            "valor": cntd
                        });
                    };
                };
                tamanho = arrW.length;
                for (var linha = 0; linha < tamanho; linha++) {
                    campo += (campo == '' ?
                        ' WHERE (' + arrW[linha].campo + '=' + arrW[linha].valor + ')' :
                        ' AND (' + arrW[linha].campo + '=' + arrW[linha].valor + ')');
                };
                var lsJS = '{"espiao":[{"tabela":"' + self.Js.tabelaBKP + '","campo":"' + campo + '"}]}';
                localStorage.setItem("envJson", lsJS);
                window.open(self.Js.formPassoPasso);
                //window.parent.document.getElementById('iframeCorpo').src="Atlas_Espiao.php";
            } else {
                console.log('Erro na tag formPassoPasso');
            };
            clsChecados = null;
        } catch (e) {
            gerarMensagemErro("WHR", e, "Erro");
        };
    };
    //
    this.verTags2017 = function() {
        //////////////////////////////////////////////
        // Checando o JSON antes de iniciar a table //
        //////////////////////////////////////////////
        var chkTipo = new Array('chk', 'edt', 'cb', 'img', 'id');
        var chkFieldType = new Array('chk', 'img', 'dat', 'str', 'int', 'flo', 'flo2', 'flo4', 'idUnico', 'flo8');
        var chkSN = new Array('N', 'S');
        var chkId = 0;
        var padrao = 0;
        if (tagValida(self.Js.checarTags) && (self.Js.checarTags == 'S')) {
            self.Js.titulo.forEach(function(t, lin) {
                ////////////
                // Tag id //
                ////////////
                if (tagValida(t.id) === false) {
                    console.log('Obrigatorio tag "id"');
                } else {
                    if (chkId != t.id)
                        console.log('Sequencial ' + t.id + ' invalido para tag id!');
                };
                //////////////
                // Tag tipo //
                //////////////        
                if (tagValida(t.tipo) == false) {
                    console.log('Obrigatorio tag "tipo" para linha:' + lin);
                } else {
                    if (chkTipo.indexOf(t.tipo) == -1) {
                        console.log('valor "' + t.tipo + '" invalido para tag tipo!');
                    };
                };
                //////////////////////////////////////////////////
                // Tag obj                                      //
                // Quando JS de bkp não é obrigatório esta tag  //
                //////////////////////////////////////////////////
                if (tagValida(t.obj) == false) {
                    if (tagValida(t.padrao))
                        padrao = t.padrao;
                    if (padrao != 9)
                        console.log('Obrigatorio tag "obj"');
                }
                ////////////////////
                // Tag fieldType  //
                ////////////////////
                if (tagValida(t.fieldType) == false)
                    console.log('Obrigatorio tag "fieldType"');
                ////////////
                // Tag pk //
                ////////////
                if (tagValida(t.pk)) {
                    if (chkSN.indexOf(t.pk) == -1)
                        console.log('valor "' + t.pk + '" invalido para tag pk! Linha: ' + lin);
                };
                //////////////////
                // Tag insUpDel //
                //////////////////
                if (tagValida(t.insUpDel)) {
                    if ((t.insUpDel[0] == 'S') && (tagValida(t.field) == false))
                        console.log('Para tag insUpDel[0]="S" obrigatório tag field na linha ' + lin);
                    if ((t.insUpDel[1] == 'S') && (tagValida(t.field) == false))
                        console.log('Para tag insUpDel[1]="S" obrigatório tag field na linha ' + lin);
                    if ((t.insUpDel[2] == 'S') && (tagValida(t.field) == false))
                        console.log('Para tag insUpDel[2]="S" obrigatório tag field na linha ' + lin);
                };
                if ((tagValida(t.pk)) && (tagValida(t.tipo)) && (t.tipo == 'chk'))
                    console.log('Para tipo chk não pode existir a tag PK linha: ' + lin);
                if ((tagValida(t.pk)) && (tagValida(t.tipo)) && (t.tipo == 'img'))
                    console.log('Para tipo img não pode existir a tag PK');
                /////////////////////////
                //  Tag autoIncremento //
                /////////////////////////
                if (tagValida(t.autoIncremento)) {
                    if (chkSN.indexOf(t.autoIncremento) == -1)
                        console.log('valor "' + t.autoIncremento + '" invalido para tag autoIncremento!');
                };
                ////////////////
                // Tag Excel  //
                ////////////////
                if (tagValida(t.excel)) {
                    if (chkSN.indexOf(t.excel) == -1)
                        console.log('valor "' + t.excel + '" invalido para tag excel-label ' + t.labelCol + '!');
                };
                //////////////
                // Tag hint //
                //////////////
                if (tagValida(t.hint)) {
                    if (chkSN.indexOf(t.hint) == -1)
                        console.log('valor "' + t.hint + '" invalido para tag hint!');
                };
                //////////////////
                // Tag insUpDel //
                //////////////////
                if (tagValida(t.insUpDel)) {
                    for (var li in t.insUpDel) {
                        if (chkSN.indexOf(t.insUpDel[li]) == -1)
                            console.log('valor "' + t.insUpDel[li] + '" invalido para tag insUpDel!');
                    };
                };
                ////////////
                // Tag pk //
                ////////////
                if (tagValida(t.ordenaColuna)) {
                    if (chkSN.indexOf(t.ordenaColuna) == -1)
                        console.log('valor "' + t.ordenaColuna + '" invalido para tag ordenaColuna!');
                };
                //////////////////
                // Tag somarImp //
                //////////////////
                if (tagValida(t.somarImp)) {
                    if (chkSN.indexOf(t.somarImp) == -1)
                        console.log('valor "' + t.somarImp + '" invalido para tag somarImp!');
                };
                ///////////////////
                // Tag fieldType //
                ///////////////////
                if (tagValida(t.fieldType) == false) {
                    console.log('Obrigatorio tag "fieldType"');
                } else {
                    if (chkFieldType.indexOf(t.fieldType) == -1) {
                        console.log('valor "' + t.fieldType + '" invalido para tag fieldType!');
                    };
                };
                chkId++;
            });
            if (tagValida(self.Js.prefixo) == false) {
                console.log('Obrigatorio tag "prefixo"')
            };
            ////////////////////////////////////////////////////////////////////////////////////////
            // Tag formPassoPasso                                                                 //  
            // Se existir a tag formPassoPasso deve existir a tag tabelaBKP e também o contratio  //
            ////////////////////////////////////////////////////////////////////////////////////////
            if (tagValida(self.Js.formPassoPasso)) {
                if (tagValida(self.Js.tabelaBKP) == false)
                    console.log('Se informar tag "formPassoPasso" obrigatório tag "tabelaBKP"');
            };
            if (tagValida(self.Js.tabelaBKP)) {
                if (tagValida(self.Js.formPassoPasso) == false)
                    console.log('Se informar tag "tabelaBKP" obrigatório tag "formPassoPasso"');
            };
            if ((tagValida(self.Js.opcRegSeek)) && (self.Js.opcRegSeek == false) && (tagValida(self.Js.indiceTable)))
                console.log('tabela sem opção procura não pode ter a tag "indiceTable"');
        };
    };
    //
    this.retirarModal2017 = function() {
        if (tagValida(self.Js.divFieldSet))
            document.getElementById(self.Js.form).style.display = 'none';
        document.getElementById(self.Js.div + 'Modal').style.display = 'none';
    };
    ////////////////////////
    // Limpando uma table //
    ////////////////////////
    this.limparTable = function() {
        var obj = document.getElementById(self.Js.tbl);
        var table = obj.getElementsByTagName('tbody')[0];
        var nl = table.rows.length; //Numero de linhas
        while (nl > 0) {
            table.deleteRow(nl - 1);
            nl--;
        };
    };

    function objInArray(obj) {
        var array = $.map(obj, function(value, index) {
            return [value];
        });
    };

    this.gradeFields = function(arrCmp, arrVlr, arrSel) {
        var tam = arrSel.length;
        var lin = 0;
        var td;
        var reg = document.getElementById(self.Js.tbl);
        var tblRowIndex; //A linha da table onde vai ser alterado o registro     
        ////////////////////////////////////////////////////////////////////
        // O primeiro for pega todas as linhas dos registros selecionados //
        ////////////////////////////////////////////////////////////////////
        for (var regSel = 0; regSel < tam; regSel++) {
            for (li in arrCmp) {
                self.Js.titulo.forEach(function(tit) {
                    if (arrCmp[li] == tit.labelCol) {
                        tblRowIndex = self.buscaRowIndexTable(arrSel[regSel]._ID);
                        reg.rows[tblRowIndex].cells[tit.id].innerHTML = arrVlr[li];
                    }
                });
                self.Js.titulo.forEach(function(tit) {
                    if (tagValida(tit.funcCor)) {
                        td = reg.rows[tblRowIndex].cells[tit.id];
                        eval(tit.funcCor.replaceAll('objCell', 'td'));
                    };
                    ////////////////////////////////////////////////////////////////////////////////////////
                    // Modelo usado para bloquear/desbloquear titulos atualizando a cor da linha da grade //
                    ////////////////////////////////////////////////////////////////////////////////////////
                    if (tagValida(self.Js.corLinha)) {
                        td = reg.rows[tblRowIndex];
                        var corL = self.Js.corLinha;
                        while (corL.indexOf("ceTr") != -1) {
                            corL = corL.replace("ceTr", "td");
                        }
                        eval(corL);
                    };
                });
            };
        };
    };
    //
    this.gravar = function() {
        var cmp = new clsCampo(); // Classe para retornar campos formatados
        var erro = new clsMensagem('Erro'); // Classe para validação dos campos
        if ((self.status == 0) || (self.status == 1)) {
            self.Js.titulo.forEach(function(reg, lin) {
                arr = JSON.parse(JSON.stringify(self.Js.titulo[lin])); // convertendo Json para arr
                if ((tagValida(arr.field)) && (tagValida(arr.insUpDel))) {
                    conteudo = document.getElementById(self.Js.titulo[lin].obj).value;
                    ////////////////////////////////////////////////////////////////////////////////
                    // Se for uma string removo os espações em branco no inicio e fim - 19jun2017 //
                    ////////////////////////////////////////////////////////////////////////////////
                    if ((tagValida(arr.fieldType)) && (arr.fieldType == 'str')) {
                        conteudo = conteudo.rtrim();
                        conteudo = conteudo.ltrim();
                    };
                    ////////////////////////////////////
                    // FORMATANDO PELO FLAG "formato" //
                    ////////////////////////////////////
                    if (tagValida(arr.formato)) {
                        frmt = arr.formato;
                        for (li in frmt) {
                            switch (frmt[li].toUpperCase()) {
                                case "ALLTRIM":
                                    conteudo = conteudo.alltrim();
                                    break;
                                case "LOWERCASE":
                                    conteudo = conteudo.toLowerCase();
                                    break;
                                case "TIRAASPAS":
                                    conteudo = conteudo.tiraAspas();
                                    break;
                                case "UPPERCASE":
                                    conteudo = conteudo.toUpperCase();
                                    break;
                                case "REMOVEACENTOS":
                                    conteudo = removeAcentos(conteudo);
                                    break;
                                case "I2":
                                    conteudo = (parseInt(conteudo)).EmZero(2);
                                    break;
                                case "I3":
                                    conteudo = (parseInt(conteudo)).EmZero(3);
                                    break;
                                case "I4":
                                    conteudo = (parseInt(conteudo)).EmZero(4);
                                    break;
                                case "I5":
                                    conteudo = (parseInt(conteudo)).EmZero(5);
                                    break;
                                case "I6":
                                    conteudo = (parseInt(conteudo)).EmZero(6);
                                    break;
                                case "I7":
                                    conteudo = (parseInt(conteudo)).EmZero(7);
                                    break;
                                case "I8":
                                    conteudo = (parseInt(conteudo)).EmZero(8);
                                    break;
                                case "I9":
                                    conteudo = (parseInt(conteudo)).EmZero(9);
                                    break;
                            };
                        };
                        document.getElementById(self.Js.titulo[lin].obj).value = conteudo;
                    };
                    ////////////////////////////////////
                    // VALIDANDO PELO FLAG "validar"  //
                    ////////////////////////////////////
                    if (tagValida(arr.validar)) {
                        vldr = arr.validar;
                        for (li in vldr) {
                            switch (vldr[li].toUpperCase()) {
                                case "DATAVALIDA":
                                    erro.dataValida(arr.labelCol, conteudo);
                                    break;
                                case "NOTNULL":
                                    erro.notNull(arr.labelCol, conteudo);
                                    break;
                                case "I>0":
                                case "INTMAIORZERO":
                                    erro.intMaiorZero(arr.labelCol, conteudo);
                                    break;
                                case "I>=0":
                                case "INTMAIORIGUALZERO":
                                    erro.intMaiorIgualZero(arr.labelCol, conteudo);
                                    break;
                                case "F>0":
                                case "FLOMAIORZERO":
                                    erro.floMaiorZero(arr.labelCol, conteudo);
                                    break;
                                case "F>=0":
                                case "FLOMAIORIGUALZERO":
                                    erro.floMaiorIgualZero(arr.labelCol, conteudo);
                                    break;
                            };
                        };
                    };
                    /////////////////////////////////////////
                    // VALIDANDO PELO FLAG "digitosMinMax" //
                    /////////////////////////////////////////
                    if (tagValida(arr.digitosMinMax)) {
                        dgts = arr.digitosMinMax;
                        if (dgts[0] == dgts[1]) {
                            erro.tamFixo(arr.labelCol, conteudo, dgts[0]);
                        } else {
                            erro.tamMin(arr.labelCol, conteudo, dgts[0]);
                            erro.tamMax(arr.labelCol, conteudo, dgts[1]);
                        };
                    };
                    ///////////////////////////////////
                    // VALIDANDO PELO FLAG "contido" //
                    ///////////////////////////////////
                    if (tagValida(arr.contido)) {
                        cntd = arr.contido;
                        erro.contido(arr.labelCol, conteudo, cntd);
                    };
                    //////////////////////////////////////////
                    // VALIDANDO PELO FLAG "digitosValidos" //
                    //////////////////////////////////////////
                    if (tagValida(arr.digitosValidos))
                        erro.digitosValidos(arr.labelCol, conteudo, arr.digitosValidos);
                };
            });
        };
        if (erro.ListaErr() != '') {
            erro.divTopo = '300px';
            erro.Show();
        } else {
            if (self.status == 0)
                sql = '{"comando":"' + this.cmdInsert() + '"}';
            if (self.status == 1)
                sql = '{"comando":"' + this.cmdUpdate('edt') + '"}';
            if (self.status == 2) {
                var sqlUp = "";
                self.Js.titulo.forEach(function(reg, lin) {
                    if ((self.Js.fieldCodUsu != '') && (self.Js.fieldCodUsu == reg.field)) {
                        sqlUp += 'UPDATE ' + self.Js.tabelaBD + ' SET ' + reg.field + '=' + jsPub[0].usr_codigo + ' ' + self.cmdWhere('edt');
                    };
                });
                try {
                    if (sqlUp == '') {
                        throw "Não localizado usuario para atualização antes de excluir!";
                    }
                } catch (e) {
                    gerarMensagemErro("WHR", e, "Erro");
                };
                sql = '{"comando":"' + sqlUp + '"}';
                sql += ',{"comando":"' + this.cmdDelete('edt') + '"}';
            }
            sql = '{"lote":[' + sql + ']}';
            console.log(sql);
            //return false;  
            //debugger;    
            var bd = new clsBancoDados(localStorage.getItem('lsPathPhp'));
            bd.execute(sql);
            ////////////////////////////////////////////////////////////////////////
            // SE DER CERTO O COMMIT ATUALIZO A GRADE(TABLE) CONFORMA self.status //
            // 0=Novo  1=Atualizar  2=Excluir                                     //
            ////////////////////////////////////////////////////////////////////////
            if ((bd.retorno == 'OK') && (self.status == 0)) {
                //////////////////////////////////////////////////////////////////////////
                // Todos os registros são inseridos atraves de um vetor não associativo //
                //////////////////////////////////////////////////////////////////////////
                var arr = new Array();
                var tmnh = (self.Js.titulo).length;
                var cntd = "";
                for (var lin = 0; lin < tmnh; lin++) {
                    reg = self.Js.titulo[lin];
                    if (["chk", "img"].indexOf(reg.tipo) == -1) {
                        switch (reg.tipo) {
                            case "cb":
                                cntd = self.comboBox(reg.obj, false, 0, reg.copyGRD);
                                break;
                            case "id":
                                cntd = self.addId[0];
                                self.addId[0] = (self.addId[0] + 1);
                                break;
                            default:
                                cntd = document.getElementById(reg.obj).value;
                                break;
                        };
                        arr.push(cntd);
                    };
                };
                self.Js.registros.push(arr);
                this.montarBody2017();
                self.retirarModal2017();
                //26mar2018-Table invisivel qdo cad/alt/exc
                //document.getElementById('dPai'+self.Js.form).style.display = 'block';
                //
            };
            //
            if ((bd.retorno == 'OK') && (self.status == 1)) {
                var clsChecados; //Classe para buscar registros checados
                var chkds; //Retorna um array associativo de todos registros checados
                var tamC; //Tamanho do array chkds
                var tamT; //Tamanho do array json
                var tit; //Array da linha da table 
                var tblRowIndex; //A linha da table onde vai ser alterado o registro 
                var arrRowIndex; //Indice do ARRAY/JSON - Deve ser alterado devido ordenação de colunas
                var colArr; //A coluna do ARRAY/JSON pois nesta não entra chk/img
                var tbl = document.getElementById(self.Js.tbl);
                try {
                    clsChecados = this.gerarJson("n");
                    chkds = clsChecados.gerar();
                    tamC = chkds.length;
                    for (var checados = 0; checados < tamC; checados++) {
                        tblRowIndex = self.buscaRowIndexTable(chkds[checados]._ID);
                        arrRowIndex = self.buscaRowIndexArray(chkds[checados]._ID);
                        colArr = -1;

                        tamT = (self.Js.titulo).length;
                        for (var tabela = 0; tabela < tamT; tabela++) {
                            tit = self.Js.titulo[tabela];

                            if (tagValida(tit.pk)) {
                                colArr++;
                                conteudo = document.getElementById(tit.obj).value;
                                if (tit.tipo == 'cb') {
                                    conteudo = self.comboBox(tit.obj, false, 0, tit.copyGRD);
                                };
                                //////////////////////////////////////
                                // Atualizando a table e ARRAY/JSON //
                                //////////////////////////////////////
                                tbl.rows[tblRowIndex].cells[tit.id].innerHTML = conteudo;
                                self.Js.registros[arrRowIndex][colArr] = conteudo;
                                //
                                if (tagValida(tit.funcCor)) {
                                    var obj = 'tbl.rows[tblRowIndex].cells[tit.id]';
                                    eval(tit.funcCor.replaceAll('objCell', obj));
                                };
                            };
                        };
                    };
                    self.retirarModal2017();
                    tbl.retiraChecked();
                    //26mar2018-Table invisivel qdo cad/alt/exc
                    //document.getElementById('dPai'+self.Js.form).style.display = 'block';
                    //    
                } catch (e) {
                    gerarMensagemErro("catch", e.message, "Erro");
                };
            };
            //
            if ((bd.retorno == 'OK') && (self.status == 2)) {
                try {
                    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    // Obrigatório apagar registro do ARRAY/JSON ( Se nova ordenação por colunas o registro não estara no array ) //
                    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    var indice; //Localiza no ARRAY/JSON o _ID na table          
                    var clsChecados = self.gerarJson("n"); //Classe para buscar registros checados
                    var chkds = clsChecados.gerar(); //Retorna um array associativo de todos registros checados
                    var tamC = chkds.length; //Tamanho do array chkds
                    for (var checados = 0; checados < tamC; checados++) {
                        indice = self.buscaRowIndexArray(parseInt(chkds[checados]._ID));
                        self.Js.registros.splice(indice, 1);
                        break;
                    };
                    clsChecados = null;
                    var tbl = document.getElementById(self.Js.tbl);
                    tbl.apagaChecados();
                    self.retirarModal2017();
                } catch (e) {
                    gerarMensagemErro("catch", e.message, "Erro");
                };
            };
        };
    };
    //
    this.cadastrar = function(vet) {
        self.status = vet;
        var div = self.Js.divFieldSet;
        var contador = 0;
        var arr = [];
        var autoInc = ''; //Se o campo for autoIncremento desabilito no cadastro
        var codDir = 4; //Se não estiver no Json é direito total
        var tblRowIndex; //A linha da table onde vai ser alterado o registro 
        //////////////////////////////////////////////////////////////////////////////
        // Vendo o direito de usuario vet[0]=Incluir vet[1]=Alterar vet[2]=Excluir  //
        //////////////////////////////////////////////////////////////////////////////
        if (tagValida(self.Js.codDir))
            codDir = self.Js.codDir;
        if (((vet == 0) && (codDir < 2)) || ((vet == 1) && (codDir < 3)) || ((vet == 2) && (codDir < 4))) {
            gerarMensagemErro('ErrDir', 'Usuario sem direito de ' + (vet == 0 ? 'INCLUIR' : vet == 1 ? 'ALTERAR' : 'EXCLUIR') + ' nesta tabela', "Erro");
            contador = 0;
            return false;
        };
        ////////////////
        // CADASTRAR  //
        ////////////////
        if (vet == 0) {
            self.Js.titulo.forEach(function(tit) {
                ///////////////////////////////////////////////////////////////////////////////////////////////
                // Vendo se o usuário tem direito de incluir(Pode ter direito de consultar e não de incluir) //
                ///////////////////////////////////////////////////////////////////////////////////////////////
                autoInc = 'N';
                if ((tagValida(tit.autoIncremento)) && (tit.autoIncremento == 'S'))
                    autoInc = 'S';

                if (tagValida(tit.newRecord)) {
                    arr = tit.newRecord;
                    if (autoInc == 'N') {
                        try {
                            if (typeof arr[vet] == "string")
                                document.getElementById(tit.obj).value = arr[vet];
                            if (typeof arr[vet] == "object")
                                document.getElementById(tit.obj).value = arr[vet].value;

                        } catch (e) { throw new Error(tit.field + ' - ' + e) }
                    } else {
                        sql = 'SELECT COALESCE(MAX(' + tit.field + '),0) AS INCREMENTO FROM ' + self.Js.tabelaBD;
                        var bdAI = new clsBancoDados(localStorage.getItem('lsPathPhp'));
                        bdAI.Assoc = false;
                        bdAI.select(sql);
                        if (bdAI.retorno == 'OK')
                            document.getElementById(tit.obj).value = (parseInt(bdAI.dados[0][0]) + 1).EmZero(4);
                    };
                };
            });
            self.desabilitaCampos(vet);
            contador = 1;
        };
        //////////////////////
        // ALTERAR/EXCLUIR  //
        //////////////////////
        if (vet > 0) {
            try {
                var objTbl = document.getElementById(self.Js.tbl);
                var clsChecados = self.gerarJson("1"); //Classe para buscar registros checados
                if (self.altVarios) {
                    clsChecados.retornarQtos("n");
                };

                var chkds = clsChecados.gerar(); //Retorna um array associativo de todos registros checados
                var tamC = chkds.length; //Tamanho do array chkds
                var ultimoCampo;
                for (var checados = 0; checados < tamC; checados++) {
                    tblRowIndex = self.buscaRowIndexTable(chkds[checados]._ID);
                    contador = 1;
                    self.Js.titulo.forEach(function(ttl) {
                        ////////////////////////////////////////
                        // Olhando se o registro é do sistema //
                        ////////////////////////////////////////
                        if ((ttl.field == self.Js.fieldReg) && (objTbl.rows[tblRowIndex].cells[ttl.id].innerHTML == 'SIS') && (ttl.altRegistro == 'N')) {
                            contador = 0;
                            throw "REGISTRO DO SISTEMA NÃO PODE SER ALTERADO/EXCLUIDO!";
                        }
                        if ((ttl.field == self.Js.fieldReg) && (objTbl.rows[tblRowIndex].cells[ttl.id].innerHTML == 'ADM') && (jsPub[0].usr_admpub != 'ADM')) {
                            contador = 0;
                            throw "REGISTRO DO ADMINISTRADOR NÃO PODE SER ALTERADO/EXCLUIDO POR USUARIO PUBLICO!";
                        }

                        if (tagValida(ttl.newRecord)) {
                            arr = (ttl.newRecord == undefined ? '' : ttl.newRecord); //Se for codusu ou codemp não é 'this'
                            ultimoCampo = ttl.labelCol;
                            if (arr[vet] == 'this') {
                                conteudo = objTbl.rows[tblRowIndex].cells[ttl.id].innerHTML;
                                ////////////////////////////////////////////////////////////////
                                // PARA CAMPOS COM VALOR DIFERENTE DO QUE É MOSTRADO NA GRADE //
                                // Obrigatorio usar cast() no select tabelaTbanco.js          //
                                ////////////////////////////////////////////////////////////////
                                if (ttl.tipo == 'cb') {
                                    conteudo = self.comboBox(ttl.obj, true, conteudo, ttl.copyGRD);
                                }
                                document.getElementById(ttl.obj).value = conteudo;
                            } else {
                                document.getElementById(ttl.obj).value = arr[vet];
                            };
                        };
                    });
                    self.desabilitaCampos(vet);
                };
            } catch (e) {
                gerarMensagemErro("ALT", e + " " + ultimoCampo, "Erro");
            };
        };
        //
        if (contador == 1) {
            var divM = self.Js.div + 'Modal';
            var frmM = self.Js.form;
            if ((document.getElementById(divM).style.display == 'none') || (document.getElementById(divM).style.display == '')) {
                document.getElementById(frmM).style.display = 'block';
                document.getElementById(divM).style.display = 'block';
            };
            if (tagValida(self.Js.foco)) {
                document.getElementById(self.Js.foco[vet]).focus();
                ////////////////////////////////////////////////
                // in é se existe a propriedade para o objeto //
                ////////////////////////////////////////////////
                if ('select' in document.getElementById(self.Js.foco[vet]))
                    document.getElementById(self.Js.foco[vet]).select();
            };
        };
    };
    //////////////////////////////////////////////////////////////////
    // Desabilitando/habilitando campos no Incluir/Alterar/Excluir  //
    //////////////////////////////////////////////////////////////////
    this.desabilitaCampos = function(parVet) {
        self.Js.titulo.forEach(function(dc) {

            if (tagValida(dc.field)) {
                var obj = document.getElementById(dc.obj);
                /////////////////////////////////////////////////////////////
                // Aqui opcional desabilita imput para qquer estado da tbl //
                /////////////////////////////////////////////////////////////
                if ((tagValida(dc.inputDisabled)) && (dc.inputDisabled)) {
                    obj.classList.remove((obj.classList.contains('campo_input_combo') ? 'campo_input_combo' : 'campo_input'));
                    obj.classList.remove((obj.classList.contains('inputF10') ? 'inputF10' : 'campo_input'));
                    obj.classList.add('campo_input_titulo');
                    obj.disabled = true;
                } else {
                    //////////////////////////////////////////////////////////
                    // Aqui opcional por Inc/Alt/Esc - Dependendo da tabela //
                    //////////////////////////////////////////////////////////
                    var attrSN = (dc.autoIncremento == 'S' ? 'N' : dc.insUpDel[parVet]);
                    if (attrSN == 'S') {
                        obj.classList.remove('campo_input_titulo');
                        obj.classList.add((dc.tipo == 'cb' ? 'campo_input_combo' : 'campo_input'));
                        obj.disabled = false;
                    } else {
                        obj.classList.remove((obj.classList.contains('campo_input_combo') ? 'campo_input_combo' : 'campo_input'));
                        obj.classList.remove((obj.classList.contains('inputF10') ? 'inputF10' : 'campo_input')); //21jun2018
                        obj.classList.add('campo_input_titulo');
                        obj.disabled = true;
                    };
                };
            };
        });
    };
    ////////////////////////////////////////////
    // acumula valor para montagem de grafico //
    ////////////////////////////////////////////
    this.graficoAcumula = function(cmp, vlr, maximo) {
        var colCmp = -1; //Acumulando por este campo
        var colVlr = -1; //Total do campo
        var colTipo = '';
        self.Js.titulo.forEach(function(tit) {
            if (cmp == tit.labelCol) colCmp = tit.id;
            if (vlr == tit.labelCol) {
                colVlr = tit.id;
                colTipo = tit.fieldType;
            }
        });
        if ((colCmp > -1) && (colVlr > -1)) {
            var arrAdd = new Array(); //Vetor apenas para busca
            var tblGra = new Array(); //Nova tabela para indice unico
            var tblRet = new Array(); //Nova tabela ordenando a tblGra e sumarizando o maximo de registros
            var cmp = new clsCampo();
            var indice = -1;
            var seek = '';
            var valor = 0;
            var table = document.getElementById(self.Js.tbl).tBodies[0];
            var nl = table.rows.length; //numero de linhas
            var nc = table.rows[nl - 1].cells.length; //numero de colunas
            for (var li = 0; li < nl; li++) {
                for (var ci = 0; ci < nc; ci++) {
                    if (ci == colCmp) {
                        seek = table.rows[li].cells[ci].innerHTML;
                        indice = arrAdd.indexOf(seek);
                        //Tipo str para quando for contar campos
                        if (colTipo == 'str')
                            valor = 1
                        else
                            valor = cmp.floatNA(table.rows[li].cells[colVlr].innerHTML);
                        //
                        if (indice == -1) {
                            arrAdd.push(seek);
                            tblGra.push({ "CAMPO": seek, "VALOR": valor });
                        } else {
                            tblGra[indice].VALOR = cmp.floatNA(parseFloat(tblGra[indice].VALOR) + valor);
                        };
                    };
                };
            };
            ////////////////////////
            // Ordenando o array  //
            ////////////////////////
            tblGra.sort(function(obj1, obj2) {
                return (obj1.VALOR > obj2.VALOR ? -1 : obj1.VALOR < obj2.VALOR ? 1 : 0);
            });
            //////////////////////////////////////////////////////////////////////////////
            // tblGra traz todos os registros sumarizados sem olhar o parametro maximo  //
            //////////////////////////////////////////////////////////////////////////////
            if (tblGra.length <= maximo) {
                tblRet = tblGra;
            } else {
                arrAdd = new Array();
                tblGra.forEach(function(gra) {
                    seek = gra.CAMPO;
                    valor = gra.VALOR;
                    //////////////////////////////////////////////
                    // Vendo o maximo de itens no vetor retorno //
                    //////////////////////////////////////////////
                    if (tblRet.length >= maximo)
                        seek = 'DIVERSOS';
                    //
                    indice = arrAdd.indexOf(seek);
                    if (indice == -1) {
                        arrAdd.push(seek);
                        tblRet.push({ "CAMPO": seek, "VALOR": gra.VALOR });
                    } else {
                        tblRet[indice].VALOR = cmp.floatNA(parseFloat(tblRet[indice].VALOR) + valor);
                    };
                });
            }
            return tblRet;
        };
    };
    //////////////////////////////
    // Alterando o campo ativo  //
    //////////////////////////////
    this.altAtivo = function(dir) {
        try {
            var tamanho;
            var tit;
            var tblRowIndex; // A linha da table onde vai ser alterado o registro 
            var arrRowIndex; // Indice do ARRAY/JSON - Deve ser alterado devido ordenação de colunas
            var colTbl = -1; // Coluna da grade para atualizar( Se encontrar antes do campo SYS não procuro novamente )
            var colArr = -1; // A coluna do ARRAY/JSON pois nesta não entra chk/img
            var bolArr = true; // Quando achar a coluna ativo para de incrementar colArr
            var cntd; // Conteudo da celula
            var clsChecados = self.gerarJson("n"); // Classe para buscar registros checados
            var chkds = clsChecados.gerar(); // Retorna um array associativo de todos registros checados
            var tamC = chkds.length; // Tamanho do array chkds
            var objTbl = document.getElementById(self.Js.tbl);
            for (var checados = 0; checados < tamC; checados++) {
                tamanho = self.Js.titulo.length;
                for (var reg = 0; reg < tamanho; reg++) {
                    tit = self.Js.titulo[reg];
                    //////////////////////////////////
                    // Para atualizar o ARRAY/JSON  //
                    //////////////////////////////////
                    if ((tagValida(tit.pk)) && (bolArr)) {
                        colArr++;
                    };
                    if (tit.field == self.Js.fieldAtivo) {
                        colTbl = tit.id;
                        bolArr = false;
                    };
                    //
                    if (tit.field == self.Js.fieldReg) {
                        tblRowIndex = self.buscaRowIndexTable(chkds[checados]._ID);
                        if (objTbl.rows[tblRowIndex].cells[tit.id].innerHTML[0] == "S") {
                            throw "REGISTRO DO SISTEMA NÃO PODE SER ALTERADO/EXCLUIDO!";
                        };
                        break;
                    };
                };
                ////////////////////////////////////////////////////////////////////////////
                // Se entrar neste if eh pq n encontrei a coluna do campo ativo na grade  //
                ////////////////////////////////////////////////////////////////////////////
                if (colTbl == -1) {
                    colArr = -1;
                    for (var reg = 0; reg < tamanho; reg++) {
                        tit = self.Js.titulo[reg];
                        if (tagValida(tit.pk)) {
                            colArr++;
                        };
                        if (tit.field == self.Js.fieldAtivo) {
                            colTbl = tit.id;
                            break;
                        };
                    };
                };
                //
                var jsPub = JSON.parse(localStorage.getItem("lsPublico"));
                var sqlUp = '';
                var sep = '';
                var sql = '';
                //////////////////////////////////////////////////////////
                // Ja tenho os registros checados, agora monto o Update //
                //////////////////////////////////////////////////////////
                for (var li = 0; vetor = chkds[li]; li++) {
                    tblRowIndex = self.buscaRowIndexTable(vetor._ID);
                    var sqlUp = 'UPDATE ' + self.Js.tabelaBD + ' SET';
                    var objCell = '';
                    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    // CORRENDO O JSON PARA PEGAR NOME CAMPO E POSIÇÃO COLUNA - where = Guardando os valores para montar a clausula where //
                    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    var where = [];
                    var attrFuncCor = '';
                    self.Js.titulo.forEach(function(reg, lin) {
                        var col = reg.id;
                        if ((tagValida(reg.pk)) && (reg.pk == 'S')) {
                            cntd = objTbl.rows[tblRowIndex].cells[reg.id].innerHTML;
                            where.push(cntd);
                        };
                        //           
                        if ((tagValida(reg.pk)) && (reg.pk == 'N')) {
                            if ((self.Js.fieldAtivo != '') && (self.Js.fieldAtivo == reg.field)) {
                                cntd = objTbl.rows[tblRowIndex].cells[reg.id].innerHTML;
                                sqlUp += ',' + reg.field +
                                    '=' +
                                    (cntd == 'SIM' ? '\'N\'' : '\'S\'');
                                if (tagValida(reg.funcCor))
                                    attrFuncCor = reg.funcCor;
                            };
                            if ((self.Js.fieldCodUsu != '') && (self.Js.fieldCodUsu == reg.field))
                                sqlUp += ',' + reg.field + '=' + jsPub[0].usr_codigo;

                            //if( (self.Js.fieldCodEmp != '') && (self.Js.fieldCodEmp==reg.field) )
                            //  sqlUp+=','+reg.field+'='+jsPub[0].EMP_CODIGO;

                            //if( (self.Js.fieldCodDir != '') && (self.Js.fieldCodDir==reg.field) )
                            //  sqlUp+=','+reg.field+'='+dir;
                        };
                    });
                    sqlUp = sqlUp.replace('SET,', 'SET ') + self.cmdWhere(where);
                    sql += sep + '{"comando":"' + sqlUp + '"}';
                    sep = ',';
                };
            };
            //
            sql = '{"lote":[' + sql + ']}';
            var bd = new clsBancoDados(localStorage.getItem('lsPathPhp'));
            bd.execute(sql);
            ///////////////////////////////////////////////////
            // SE DER CERTO O COMMIT ATUALIZO A GRADE(TABLE) //
            ///////////////////////////////////////////////////
            if (bd.retorno == 'OK') {
                for (var li = 0; vetor = chkds[li]; li++) {
                    tblRowIndex = self.buscaRowIndexTable(vetor._ID);
                    arrRowIndex = self.buscaRowIndexArray(vetor._ID);
                    cntd = (objTbl.rows[tblRowIndex].cells[colTbl].innerHTML == 'SIM' ? 'NAO' : 'SIM');
                    objTbl.rows[tblRowIndex].cells[colTbl].innerHTML = cntd;
                    self.Js.registros[arrRowIndex][colArr] = cntd;
                    if (attrFuncCor != '') {
                        objCell = objTbl.rows[tblRowIndex].cells[colTbl];
                        eval(attrFuncCor);
                    };
                };
                objTbl.retiraChecked();
            };
        } catch (e) {
            gerarMensagemErro("ATIVO", e, "Erro");
        };
    };
    //////////////////////////////////////////////////////////////////////////////////////////////////
    // Alterando o campo sys - O parametro admPub é o que esta parametrizado no cadastro de usuário //
    // Altera de PUB->ADM ou ADM->PUB
    //////////////////////////////////////////////////////////////////////////////////////////////////
    this.altPubAdm = function(dir, admPub) {
        try {
            var tamanho;
            var tit;
            var tblRowIndex; //A linha da table onde vai ser alterado o registro 
            var arrRowIndex; //Indice do ARRAY/JSON - Deve ser alterado devido ordenação de colunas
            var colTbl = -1; //Coluna da grade para atualizar( Se encontrar antes do campo SYS não procuro novamente )
            var colArr = -1; //A coluna do ARRAY/JSON pois nesta não entra chk/img
            var cntd; //Conteudo da celula
            var objTbl = document.getElementById(self.Js.tbl);
            var clsChecados = self.gerarJson("n"); //Classe para buscar registros checados
            var chkds = clsChecados.gerar(); //Retorna um array associativo de todos registros checados
            var tamC = chkds.length; //Tamanho do array chkds

            for (var checados = 0; checados < tamC; checados++) {
                tamanho = self.Js.titulo.length;
                for (var reg = 0; reg < tamanho; reg++) {
                    tit = self.Js.titulo[reg];
                    /////////////////////////////////
                    // Para atualizar o ARRAY/JSON //
                    /////////////////////////////////
                    if (tagValida(tit.pk)) {
                        colArr++;
                    };
                    //
                    if (tit.field == self.Js.fieldReg) {
                        colTbl = tit.id;
                        tblRowIndex = self.buscaRowIndexTable(chkds[checados]._ID);
                        if (objTbl.rows[tblRowIndex].cells[tit.id].innerHTML[0] == "S") {
                            throw "REGISTRO DO SISTEMA NÃO PODE SER ALTERADO/EXCLUIDO!";
                        };
                        ////////////////////////////////////////////////////////////////////////
                        // Verifica se o usuario atual é PUB e esta tentando alterar para ADM //
                        ////////////////////////////////////////////////////////////////////////
                        if (admPub != undefined) {
                            if ((admPub == 'P') && (objTbl.rows[tblRowIndex].cells[tit.id].innerHTML == 'PUB')) {
                                throw "USUÁRIO COM PARAMETRO <b>" + admPub + "</b> NO CADASTRO DE USUARIO NÃO PODE ALTERAR REGISTRO PARA ADM!"
                            };
                        };
                        break;
                    };
                };
                //
                var jsPub = JSON.parse(localStorage.getItem("lsPublico"));
                var sqlUp = '';
                var sep = '';
                var sql = '';
                //////////////////////////////////////////////////////////
                // Ja tenho os registros checados, agora monto o Update //
                //////////////////////////////////////////////////////////
                for (var li = 0; vetor = chkds[li]; li++) {
                    tblRowIndex = self.buscaRowIndexTable(vetor._ID);
                    var sqlUp = 'UPDATE ' + self.Js.tabelaBD + ' SET';
                    var objCell = '';
                    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    // CORRENDO O JSON PARA PEGAR NOME CAMPO E POSIÇÃO COLUNA - where = Guardando os valores para montar a clausula where //
                    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    var where = [];
                    var attrFuncCor = '';
                    self.Js.titulo.forEach(function(reg, lin) {
                        var col = reg.id;
                        if ((tagValida(reg.pk)) && (reg.pk == 'S')) {
                            cntd = objTbl.rows[tblRowIndex].cells[reg.id].innerHTML;
                            where.push(cntd);
                        };
                        //           
                        if ((tagValida(reg.pk)) && (reg.pk == 'N')) {
                            if ((self.Js.fieldReg != '') && (self.Js.fieldReg == reg.field)) {
                                cntd = objTbl.rows[tblRowIndex].cells[reg.id].innerHTML;
                                sqlUp += ',' + reg.field +
                                    '=' +
                                    (cntd == 'PUB' ? '\'A\'' : '\'P\'');
                                if (tagValida(reg.funcCor))
                                    attrFuncCor = reg.funcCor;
                            };

                            if ((self.Js.fieldCodUsu != '') && (self.Js.fieldCodUsu == reg.field))
                                sqlUp += ',' + reg.field + '=' + jsPub[0].usr_codigo;

                            //if( (self.Js.fieldCodEmp != '') && (self.Js.fieldCodEmp==reg.field) )
                            //  sqlUp+=','+reg.field+'='+jsPub[0].EMP_CODIGO;

                            //if( (self.Js.fieldCodDir != '') && (self.Js.fieldCodDir==reg.field) )
                            //  sqlUp+=','+reg.field+'='+dir;
                        };
                    });
                    sqlUp = sqlUp.replace('SET,', 'SET ') + self.cmdWhere(where);
                    sql += sep + '{"comando":"' + sqlUp + '"}';
                    sep = ',';
                };
            };
            //
            sql = '{"lote":[' + sql + ']}';
            var bd = new clsBancoDados(localStorage.getItem('lsPathPhp'));
            bd.execute(sql);
            ///////////////////////////////////////////////////
            // SE DER CERTO O COMMIT ATUALIZO A GRADE(TABLE) //
            ///////////////////////////////////////////////////
            if (bd.retorno == 'OK') {
                for (var li = 0; vetor = chkds[li]; li++) {
                    tblRowIndex = self.buscaRowIndexTable(vetor._ID);
                    arrRowIndex = self.buscaRowIndexArray(vetor._ID);
                    cntd = (objTbl.rows[tblRowIndex].cells[colTbl].innerHTML == 'PUB' ? 'ADM' : 'PUB');
                    objTbl.rows[tblRowIndex].cells[colTbl].innerHTML = cntd;
                    self.Js.registros[arrRowIndex][colArr] = cntd;
                    if (attrFuncCor != '') {
                        objCell = objTbl.rows[tblRowIndex].cells[colTbl];
                        eval(attrFuncCor);
                    };
                };
                objTbl.retiraChecked();
            };
        } catch (e) {
            gerarMensagemErro("ATIVO", e, "Erro");
        };
    };
    ///////////////////////////////////
    // Alterando o campo sys para S  //
    ///////////////////////////////////
    this.altRegSistema = function(dir) {
        if (dir != 4) {
            gerarMensagemErro("SIS", "USUARIO NAO TEM DIREITO A ESTA ROTINA!", "Erro");
        } else {
            try {
                var tamanho;
                var tit;
                var tblRowIndex; //A linha da table onde vai ser alterado o registro 
                var arrRowIndex; //Indice do ARRAY/JSON - Deve ser alterado devido ordenação de colunas
                var colTbl = -1; //Coluna da grade para atualizar( Se encontrar antes do campo SYS não procuro novamente )
                var colArr = -1; //A coluna do ARRAY/JSON pois nesta não entra chk/img
                var cntd; //Conteudo da celula
                var objTbl = document.getElementById(self.Js.tbl);
                var clsChecados = self.gerarJson("n"); //Classe para buscar registros checados
                var chkds = clsChecados.gerar(); //Retorna um array associativo de todos registros checados
                var tamC = chkds.length; //Tamanho do array chkds

                for (var checados = 0; checados < tamC; checados++) {
                    tamanho = self.Js.titulo.length;
                    for (var reg = 0; reg < tamanho; reg++) {
                        tit = self.Js.titulo[reg];
                        /////////////////////////////////
                        // Para atualizar o ARRAY/JSON //
                        /////////////////////////////////
                        if (tagValida(tit.pk)) {
                            colArr++;
                        };
                        //
                        if (tit.field == self.Js.fieldReg) {
                            colTbl = tit.id;
                            tblRowIndex = self.buscaRowIndexTable(chkds[checados]._ID);
                            if (objTbl.rows[tblRowIndex].cells[tit.id].innerHTML[0] == "S") {
                                throw "REGISTRO DO SISTEMA NÃO PODE SER ALTERADO/EXCLUIDO!";
                            };
                            break;
                        };
                    };
                    //
                    var jsPub = JSON.parse(localStorage.getItem("lsPublico"));
                    var sqlUp = '';
                    var sep = '';
                    var sql = '';
                    //////////////////////////////////////////////////////////
                    // Ja tenho os registros checados, agora monto o Update //
                    //////////////////////////////////////////////////////////
                    for (var li = 0; vetor = chkds[li]; li++) {
                        tblRowIndex = self.buscaRowIndexTable(vetor._ID);
                        var sqlUp = 'UPDATE ' + self.Js.tabelaBD + ' SET';
                        var objCell = '';
                        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        // CORRENDO O JSON PARA PEGAR NOME CAMPO E POSIÇÃO COLUNA - where = Guardando os valores para montar a clausula where //
                        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        var where = [];
                        var attrFuncCor = '';
                        self.Js.titulo.forEach(function(reg, lin) {
                            var col = reg.id;
                            if ((tagValida(reg.pk)) && (reg.pk == 'S')) {
                                cntd = objTbl.rows[tblRowIndex].cells[reg.id].innerHTML;
                                where.push(cntd);
                            };
                            //           
                            if ((tagValida(reg.pk)) && (reg.pk == 'N')) {
                                if ((self.Js.fieldReg != '') && (self.Js.fieldReg == reg.field)) {
                                    cntd = objTbl.rows[tblRowIndex].cells[reg.id].innerHTML;
                                    sqlUp += ',' + reg.field + '=\'S\'';
                                    if (tagValida(reg.funcCor))
                                        attrFuncCor = reg.funcCor;
                                };
                                if ((self.Js.fieldCodUsu != '') && (self.Js.fieldCodUsu == reg.field))
                                    sqlUp += ',' + reg.field + '=' + jsPub[0].usr_codigo;

                                //if( (self.Js.fieldCodEmp != '') && (self.Js.fieldCodEmp==reg.field) )
                                //  sqlUp+=','+reg.field+'='+jsPub[0].EMP_CODIGO;

                                //if( (self.Js.fieldCodDir != '') && (self.Js.fieldCodDir==reg.field) )
                                //  sqlUp+=','+reg.field+'='+dir;
                            };
                        });
                        sqlUp = sqlUp.replace('SET,', 'SET ') + self.cmdWhere(where);
                        sql += sep + '{"comando":"' + sqlUp + '"}';
                        sep = ',';
                    };
                };
                //////////////////////////////////////////
                // Atualizando o banco de dados e grade //
                //////////////////////////////////////////  
                sql = '{"lote":[' + sql + ']}';
                var bd = new clsBancoDados(localStorage.getItem('lsPathPhp'));
                bd.execute(sql);
                ///////////////////////////////////////////////////
                // SE DER CERTO O COMMIT ATUALIZO A GRADE(TABLE) //
                ///////////////////////////////////////////////////
                if (bd.retorno == 'OK') {
                    for (var li = 0; vetor = chkds[li]; li++) {
                        tblRowIndex = self.buscaRowIndexTable(vetor._ID);
                        arrRowIndex = self.buscaRowIndexArray(vetor._ID);
                        objTbl.rows[tblRowIndex].cells[colTbl].innerHTML = 'SIS';
                        self.Js.registros[arrRowIndex][colArr] = 'SIS';
                        if (attrFuncCor != '') {
                            objCell = objTbl.rows[tblRowIndex].cells[colTbl];
                            eval(attrFuncCor);
                        };
                    };
                    objTbl.retiraChecked();
                };
            } catch (e) {
                gerarMensagemErro("ATIVO", e, "Erro");
            };
        };
    };
    //
    this.numRegistros = function(qtos) {
        var table = document.getElementById(self.Js.tbl).tBodies[0];
        var nl = table.rows.length; // numero de linhas
        var ret = 0;
        for (li = 0; li < nl; li++) {
            //////////////////////////////////
            // Inibindo as linhas filtradas //
            //////////////////////////////////
            if (table.rows[li].style.display == 'none')
                continue;
            ret++;
        };
        if (qtos == undefined) {
            gerarMensagemErro("ATIVO", "Numero registros: " + ret, "Aviso");
        } else {
            return ret;
        }
    };
    //
    this.imprimir = function() {
        var table = document.getElementById(self.Js.tbl).tBodies[0];
        var nl = table.rows.length; // numero de linhas
        if (nl == 0)
            gerarMensagemErro("IMP", "NENHUM REGISTRO PARA SER IMPRESSO", "Erro");
        else {
            var nc = table.rows[nl - 1].cells.length; // numero de colunas
            var arrSomar = []; // Guardar soma se parametrizado sim "somarImp"
            var cmp = new clsCampo(); // Classe para retornar campos
            var cntd = '';
            ///////////////////////////////////////////////////////////////////////
            // MONTANDO UM ARRAY SOMENTE COM OS CAMPOS QUE PRECISO PARA IMPRIMIR //
            ///////////////////////////////////////////////////////////////////////
            var obj = self.Js.titulo;
            var nlJ = self.Js.titulo.length;
            var arr = [];
            for (li = 0; li < nlJ; li++) {
                if ((tagValida(obj[li].tamImp)) && (parseInt(obj[li].tamImp) > 0)) {
                    arr.push({
                        "id": obj[li].id,
                        "labelCol": (tagValida(obj[li].labelColImp) ? obj[li].labelColImp : obj[li].labelCol),
                        "fieldType": obj[li].fieldType,
                        "tamImp": obj[li].tamImp,
                        "copyTamImp": (tagValida(obj[li].copyTamImp) ? obj[li].copyTamImp : 0),
                        "somarImp": (((tagValida(obj[li].tamImp)) && (obj[li].somarImp == 'S')) ? 'S' : 'N'),
                        "sepMinhar": obj[li].sepMilhar
                    });
                    arrSomar.push(0);
                };
            };
            //
            var imp = '';
            imp += '{';
            imp += '"orientacao":"' + self.Js.relOrientacao + '"';
            imp += ',"Cabecalho":[';
            imp += '{"SetFont":["Arial","B",' + self.Js.relFonte + ']}';
            imp += ',{"Cell":[100,7,"' + (typeof(self.Js.relTitulo) == 'string' ? self.Js.relTitulo : self.Js.relTitulo.value) + '",0,0,"L"]}';
            imp += ',{"Ln":[6]}';
            //////////////////////////
            // MONTANDO O CABEÇALHO //
            //////////////////////////
            var frmt = '';
            var arrFrmt = new Array();
            for (ii = 0; col = arr[ii]; ii++) {
                frmt = (("flo flo2 flo4 flo8 int".indexOf(col.fieldType) == -1) ? "L" : "R");
                arrFrmt.push(frmt); //Para não ter que montar novamente nos itens e se somar colunas
                imp += ',{"Cell":[' + col.tamImp + ',6,"' + col.labelCol + '" ,1,0,"' + frmt + '"]}';
            };
            imp += ',{"Ln":[9]}]'
            imp += ',"imprimir":'
            imp += '[{"SetFont":["Arial","",' + self.Js.relFonte + ']}';
            ///////////
            // ITENS //
            ///////////
            for (li = 0; li < nl; li++) {
                //////////////////////////////////
                // Inibindo as linhas filtradas //
                //////////////////////////////////
                if (table.rows[li].style.display == 'none')
                    continue;
                //
                for (ii = 0; col = arr[ii]; ii++) {
                    cntd = (((col.sepMilhar == false) || (col.sepMilhar == undefined)) ? table.rows[li].cells[col.id].innerHTML : table.rows[li].cells[col.id].innerHTML.replaceAll(".", ""));
                    if (col.copyTamImp > 0) {
                        cntd = cntd.substring(0, col.copyTamImp);
                    }
                    /*  
                    switch(col.fieldType){
                      case 'flo'   :
                      case 'flo2'  : imp+=',{"Cell":['+col.tamImp+',0,"'+(cmp.floatNA(cntd)).sepNB()+'",0,0,"'+arrFrmt[ii]+'"]}';  break;
                      case 'flo4'  : imp+=',{"Cell":['+col.tamImp+',0,"'+(cmp.floatNA(cntd)).sepNB(4)+'",0,0,"'+arrFrmt[ii]+'"]}'; break;
                      default      : imp+=',{"Cell":['+col.tamImp+',0,"'+cntd+'",0,0,"'+arrFrmt[ii]+'"]}';                         break;
                    };
                    */
                    switch (col.fieldType) {
                        case 'flo':
                        case 'flo2':
                            imp += ',{"Cell":[' + col.tamImp + ',0,"' + jsNmrs(cntd).dolar().sepMilhar().ret() + '",0,0,"' + arrFrmt[ii] + '"]}';
                            break;
                        case 'flo4':
                            imp += ',{"Cell":[' + col.tamImp + ',0,"' + jsNmrs(cntd).dec(4).dolar().sepMilhar(4).ret() + '",0,0,"' + arrFrmt[ii] + '"]}';
                            break;
                        case 'flo8':
                            imp += ',{"Cell":[' + col.tamImp + ',0,"' + jsNmrs(cntd).dec(8).dolar().sepMilhar(8).ret() + '",0,0,"' + arrFrmt[ii] + '"]}';
                            break;
                        default:
                            imp += ',{"Cell":[' + col.tamImp + ',0,"' + cntd + '",0,0,"' + arrFrmt[ii] + '"]}';
                            break;
                    };

                    if (col.somarImp == 'S')
                        arrSomar[ii] += cmp.floatNA(table.rows[li].cells[col.id].innerHTML.replaceAll(".", ""));
                };
                imp += ',{"Ln":["4"]}';
            };
            ///////////////////////////////////////
            // Se for parametrizado somar coluna //
            ///////////////////////////////////////
            for (li = 0; li < 1; li++) {
                for (ii = 0; col = arr[ii]; ii++) {
                    if (col.somarImp == 'S') {
                        if (col.fieldType == "int")
                            imp += ',{"Cell":[' + col.tamImp + ',0,"' + arrSomar[ii] + '",0,0,"' + arrFrmt[ii] + '"]}';
                        else
                        //imp+=',{"Cell":['+col.tamImp+',0,"'+(cmp.floatNA(arrSomar[ii])).sepNB()+'",0,0,"'+arrFrmt[ii]+'"]}';
                            imp += ',{"Cell":[' + col.tamImp + ',0,"' + jsNmrs(arrSomar[ii]).dolar().sepMilhar().ret() + '",0,0,"' + arrFrmt[ii] + '"]}';

                    } else {
                        imp += ',{"Cell":[' + col.tamImp + ',0,"",0,0,"' + arrFrmt[ii] + '"]}';
                    };
                };
                imp += ',{"Ln":["4"]}';
            };
            //
            imp += ']}';
            document.getElementById('sql').value = imp;
            document.getElementsByTagName('form')[0].submit();
        };
    };
    //
    this.detalhe = function() {
        if (tagValida(self.Js.detalheRegistro)) {
            try {
                var objTbl = document.getElementById(self.Js.tbl);
                var detReg = self.Js.detalheRegistro[0];
                var parImpar = 0;
                var ceTable = '';
                var ceThead = '';
                var ceTr = '';
                var ceDiv = '';
                var ceTd = '';
                var ceTh = '';
                var ceContext = '';
                var ceBody = '';
                var tblRowIndex; //A linha da table para buscar dados
                var clsChecados = this.gerarJson("1");
                var chkds = clsChecados.gerar();
                var tamC = chkds.length;
                //////////////////////
                // Montando a table //
                //////////////////////
                for (var checados = 0; checados < tamC; checados++) {
                    tblRowIndex = self.buscaRowIndexTable(chkds[checados]._ID);
                    ceTable = document.createElement("table");
                    ceTable.id = "tblDet";
                    ceTable.className = "bisTable";
                    ceTable.style.width = detReg.width;
                    ceTable.style.fontSize = detReg.fontSize;

                    ceThead = document.createElement("thead");
                    ceTr = document.createElement("tr");

                    ceTh = document.createElement("th");
                    ceTh.style.width = detReg.colDescricao;
                    ceContext = document.createTextNode("DESCRICAO");
                    ceTh.appendChild(ceContext);
                    ceTr.appendChild(ceTh);

                    ceTh = document.createElement("th");
                    ceTh.style.width = detReg.colConteudo;
                    ceContext = document.createTextNode("CONTEUDO");
                    ceTh.appendChild(ceContext);
                    ceTr.appendChild(ceTh);
                    ceThead.appendChild(ceTr);

                    ceBody = document.createElement("tbody");
                    ceBody.id = "tbody_tblDet";
                    self.Js.titulo.forEach(function(ttl) {
                        if ((tagValida(ttl.hint)) && (ttl.hint == 'S')) {
                            ceTr = document.createElement("tr");
                            ceTr.className = (parImpar % 2 ? "corGradePar" : "corGradeImpar");

                            ceTd = document.createElement("td");
                            ceContext = document.createTextNode((tagValida(ttl.lblDetalhe) ? ttl.lblDetalhe : ttl.labelCol));
                            ceTd.appendChild(ceContext);
                            ceTr.appendChild(ceTd);

                            ceTd = document.createElement("td");
                            ceContext = document.createTextNode(objTbl.rows[tblRowIndex].cells[ttl.id].innerHTML);
                            ceTd.appendChild(ceContext);
                            ceTr.appendChild(ceTd);
                            ceBody.appendChild(ceTr);

                            if (tagValida(ttl.ajudaDetalhe)) {
                                ceTr = document.createElement("tr");
                                ceTr.setAttribute("style", "background-color:#E6F6F5;color:blue");
                                ceTd = document.createElement("td");
                                ceTd.setAttribute("colspan", "2");

                                var ceImg = document.createElement("i");
                                ceImg.className = "fa fa-check";
                                ceImg.setAttribute("style", "background-color:#E6F6F5;color:blue;font-weight: bold");
                                ceTd.appendChild(ceImg);

                                ceContext = document.createTextNode(ttl.ajudaDetalhe);
                                ceTd.appendChild(ceContext);
                                ceTr.appendChild(ceTd);
                                ceBody.appendChild(ceTr);
                            };
                            parImpar++;
                        };
                    });
                    ceTable.appendChild(ceThead);
                    ceTable.appendChild(ceBody);

                    ceDiv = document.createElement("div");
                    ceDiv.style.height = (parseInt((detReg.height).soNumeros()) - 100) + "px";
                    ceDiv.style.overflowY = "auto";
                    ceDiv.appendChild(ceTable);
                    //////////////////////////////////////
                    // Montando o formulario para table //
                    // Variaveis para modal             //
                    //////////////////////////////////////
                    contMsg++;
                    var divModal = 'dm' + contMsg; // div modal
                    var divMsg = 'dms' + contMsg; // div mensagem
                    var maior = retornarZIndex(); // maior indice para Z-index  
                    //////////////////////
                    // Create Elements  //
                    //////////////////////
                    var ceModal = ''; //Div modal para marcar todo fundo como desabilitado
                    var ceSec = '';
                    var ceFrm = '';
                    var cePar = '';
                    var ceImg = '';
                    var ceInp = '';
                    var ceBut = '';
                    var objImg = '';
                    ceModal = document.createElement("div");
                    ceModal.id = divModal;
                    ceModal.className = "divShowModal";
                    ceModal.style.zIndex = (maior);
                    ////////////////
                    // Formulario //
                    ////////////////
                    ceFrm = document.createElement("form");
                    ceFrm.className = "formulario";
                    ceFrm.id = divMsg;
                    ceFrm.style.top = detReg.top;
                    if ((tagValida(self.Js.tableLeft)) && (self.Js.tableLeft == "sim")) {
                        ceFrm.style.left = "1em";
                    } else {
                        ceFrm.style.left = detReg.left;
                    }
                    ceFrm.style.width = "40%";
                    ceFrm.style.height = detReg.height;

                    ceFrm.style.position = "absolute";
                    ceFrm.style.zIndex = (maior + 2);
                    ////////////
                    // titulo //
                    ////////////
                    cePar = document.createElement("p");
                    cePar.className = "frmCampoTit";
                    ceInp = document.createElement('input');
                    ceInp.className = "informe";
                    ceInp.style.color = "white";
                    ceInp.type = "text";
                    ceInp.name = "titulo";
                    ceInp.value = detReg.label;
                    ceInp.style.textAlign = "left";
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
                    ceBut.addEventListener('click', function() {
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
                    document.getElementsByTagName('body')[0].appendChild(ceModal);
                    document.getElementsByTagName('body')[0].appendChild(ceFrm);
                };
            } catch (e) {
                gerarMensagemErro("ATIVO", e, "Erro");
            };
        };
    };
    //
    this.alertas = function(parArr) {
        var detReg = self.Js.detalheErro[0];
        var parImpar = 0;
        var ceTable = '';
        var ceThead = '';
        var ceTr = '';
        var ceDiv = '';
        var ceTd = '';
        var ceTh = '';
        var ceContext = '';
        var ceBody = '';
        var tam = parArr.length;
        //////////////////////
        // Montando a table //
        //////////////////////
        for (var lin = 0; lin < tam; lin++) {
            if (lin == 0) {
                ceTable = document.createElement("table");
                ceTable.id = "tblDet";
                ceTable.className = "bisTable";
                ceTable.style.width = detReg.width;
                ceTable.style.fontSize = detReg.fontSize;

                ceThead = document.createElement("thead");
                ceTr = document.createElement("tr");

                ceTh = document.createElement("th");
                ceTh.style.width = detReg.colDescricao;
                ceContext = document.createTextNode("COD");
                ceTh.appendChild(ceContext);
                ceTr.appendChild(ceTh);

                ceTh = document.createElement("th");
                ceTh.style.width = detReg.colConteudo;
                ceContext = document.createTextNode("CONTEUDO");
                ceTh.appendChild(ceContext);
                ceTr.appendChild(ceTh);
                ceThead.appendChild(ceTr);

                ceBody = document.createElement("tbody");
                ceBody.id = "tbody_tblDet";
            };
            ceTr = document.createElement("tr");
            ceTr.className = (parImpar % 2 ? "corGradePar" : "corGradeImpar");

            ceTd = document.createElement("td");
            ceContext = document.createTextNode(parArr[lin][1]);
            ceTd.appendChild(ceContext);
            ceTr.appendChild(ceTd);

            ceTd = document.createElement("td");
            ceContext = document.createTextNode(parArr[lin][2]);
            ceTd.appendChild(ceContext);
            ceTr.appendChild(ceTd);
            ceBody.appendChild(ceTr);

            parImpar++;
        };
        ceTable.appendChild(ceThead);
        ceTable.appendChild(ceBody);

        ceDiv = document.createElement("div");
        ceDiv.style.height = (parseInt((detReg.height).soNumeros()) - 100) + "px";
        ceDiv.style.overflowY = "auto";
        ceDiv.appendChild(ceTable);
        //////////////////////////////////////
        // Montando o formulario para table //
        // Variaveis para modal             //
        //////////////////////////////////////
        contMsg++;
        var divModal = 'dm' + contMsg; // div modal
        var divMsg = 'dms' + contMsg; // div mensagem
        var maior = retornarZIndex(); // maior indice para Z-index  
        //////////////////////
        // Create Elements  //
        //////////////////////
        var ceModal = ''; //Div modal para marcar todo fundo como desabilitado
        var ceSec = '';
        var ceFrm = '';
        var cePar = '';
        var ceImg = '';
        var ceInp = '';
        var ceBut = '';
        var objImg = '';
        ceModal = document.createElement("div");
        ceModal.id = divModal;
        ceModal.className = "divShowModal";
        ceModal.style.zIndex = (maior);
        // Formulario
        ceFrm = document.createElement("form");
        ceFrm.className = "formulario";
        ceFrm.id = divMsg;
        ceFrm.style.top = detReg.top;
        ceFrm.style.left = detReg.left;
        ceFrm.style.width = "50%";
        ceFrm.style.height = detReg.height;

        ceFrm.style.position = "absolute";
        ceFrm.style.zIndex = (maior + 2);
        // titulo
        cePar = document.createElement("p");
        cePar.className = "frmCampo";
        ceInp = document.createElement('input');
        ceInp.className = "informe";
        ceInp.type = "text";
        ceInp.name = "titulo";
        ceInp.value = detReg.label;
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
        ceBut.addEventListener('click', function() {
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
        document.getElementsByTagName('body')[0].appendChild(ceModal);
        document.getElementsByTagName('body')[0].appendChild(ceFrm);
    };
    ///////////////////////////////////////////////////////////////////////
    // Este grafico eh montado atraves da propria table na tela          //
    // Existe o graficoPhp que os dados são gerados por select ou manual //
    ///////////////////////////////////////////////////////////////////////
    this.grafico = function(js) {
        var objJs = js.parametros;
        var ceDiv = '';
        ////////////////////////
        // Montando o grafico //
        ////////////////////////
        var dPaiGra = document.createElement("div");
        var dPaiGraE = document.createElement("canvas");
        dPaiGraE.id = "pieChart";
        dPaiGraE.style.cssFloat = "left";
        //dPaiGraE.width            = "300";
        dPaiGraE.width = (objJs[0].widthGra == undefined ? 300 : objJs[0].widthGra);
        dPaiGraE.height = (objJs[0].height - 100);
        dPaiGraE.style.marginLeft = "20px";

        var dPaiGraD = document.createElement("div");
        dPaiGraD.id = "pieLegend"
        dPaiGraD.style.cssFloat = "left";
        dPaiGraD.style.marginLeft = "30px";

        var ceAnc = document.createElement('a');
        ceAnc.name = "verGrafico";

        dPaiGra.appendChild(dPaiGraE);
        dPaiGra.appendChild(dPaiGraD);
        dPaiGra.appendChild(ceAnc);
        ////////////////////////////////////////////
        // Acumulando valores para gerar grafico  //
        ////////////////////////////////////////////
        var arrVlr = this.graficoAcumula(objJs[0].campoTable, objJs[0].campoVlr, objJs[0].numQuebras);
        var data = new Array();
        var arrGra = new Array();
        var valor = 0;
        var ctx = dPaiGraE.getContext("2d");

        switch (objJs[0].tipoGrafico) {
            case 'pie':
                for (var lin = 0 in arrVlr) {
                    data.push({
                        "value": (objJs[0].vlrAbs ? Math.abs(cmp.int(arrVlr[lin].VALOR, 'js')) : cmp.int(arrVlr[lin].VALOR, 'js')),
                        "color": arrFill[lin],
                        "label": arrVlr[lin].CAMPO
                    });
                };
                var myGra = new Chart(ctx).Pie(data);
                legend(dPaiGraD, data, myGra, true);
                break;
            case 'bar':
                for (var lin = 0 in arrVlr) {
                    arrGra.push({
                        "fillColor": arrFill[lin],
                        "strokeColor": arrBorder[lin],
                        "pointColor": "red",
                        "pointStrokeColor": "#fff",
                        "data": [(objJs[0].vlrAbs ? Math.abs(cmp.int(arrVlr[lin].VALOR, 'js')) : cmp.int(arrVlr[lin].VALOR, 'js'))],
                        "label": [arrVlr[lin].CAMPO]
                    });
                };
                var data = {
                    labels: [''],
                    datasets: arrGra,
                };
                var myGra = new Chart(ctx).Bar(data);
                legend(dPaiGraD, data, myGra, true);
                break;

            case 'lin':
                var data = objJs[0].dados;
                var myGra = new Chart(ctx).Line(data);
                legend(dPaiGraD, data, myGra, false);
                break;
        };

        ceDiv = document.createElement("div");
        ceDiv.style.height = objJs[0].height; //"400";
        ceDiv.style.overflowY = "auto";
        ceDiv.appendChild(dPaiGra);
        //////////////////////////////////////
        // Montando o formulario para table //
        // Variaveis para modal             //
        //////////////////////////////////////
        contMsg++;
        var divModal = 'dm' + contMsg; // div modal
        var divMsg = 'dms' + contMsg; // div mensagem
        var maior = retornarZIndex(); // maior indice para Z-index  
        //////////////////////
        // Create Elements  //
        //////////////////////
        var ceModal = ''; //Div modal para marcar todo fundo como desabilitado
        var ceSec = '';
        var ceFrm = '';
        var cePar = '';
        var ceImg = '';
        var ceInp = '';
        var ceBut = '';
        var objImg = '';
        ceModal = document.createElement("div");
        ceModal.id = divModal;
        ceModal.className = "divShowModal";
        ceModal.style.zIndex = (maior);
        // Formulario
        ceFrm = document.createElement("form");
        ceFrm.className = "formulario center";
        ceFrm.id = divMsg;
        ceFrm.style.top = "20px"; //detReg.top; 
        //ceFrm.style.left      = "150px";//detReg.left; 
        ceFrm.style.width = objJs[0].width;
        ceFrm.style.height = "400px";

        ceFrm.style.position = "absolute";
        ceFrm.style.zIndex = (maior + 2);
        // titulo
        cePar = document.createElement("p");
        cePar.className = "frmCampo";
        ceInp = document.createElement('input');
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
        ceBut.addEventListener('click', function() {
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
        document.getElementsByTagName('body')[0].appendChild(ceModal);
        document.getElementsByTagName('body')[0].appendChild(ceFrm);
    };
    //
    this.excel = function() {
        var tbl = self.Js.tbl;
        var table = document.getElementById(tbl).tBodies[0];
        var nl = table.rows.length; //numero de linhas
        if (nl == 0)
            gerarMensagemErro("IMP", "NENHUM REGISTRO PARA GERAR EXCEL", "Erro");
        else {
            var nc = table.rows[nl - 1].cells.length; // numero de colunas
            var obj = self.Js.titulo;
            var nlJ = self.Js.titulo.length; // Numero linha JSON
            var xlsTable;
            ///////////////////////////////////////////
            // Conto quantas colunas serão impressas //
            ///////////////////////////////////////////
            var arr = [];
            for (li = 0; li < nlJ; li++) {
                if ((tagValida(obj[li].excel)) && (obj[li].excel === 'S')) {
                    arr.push({
                        "id": obj[li].id,
                        "labelCol": obj[li].labelCol,
                        "fieldType": obj[li].fieldType
                            //,"tamGrd"     : obj[li].tamGrd
                            ,
                        "tamGrd": (((tagValida(obj[li].tamGrd)) && (obj[li].tamGrd != '0em')) ? obj[li].tamGrd : '5em')
                    });
                };
            };
            /////////////////////////////////////////
            // Montando cabecalho do arquivo/excel //
            /////////////////////////////////////////
            xlsTable = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
            xlsTable += '<style>';
            xlsTable += '  table {border: 2px solid black;font-family:Calibri;font-size:9pt;color:black;}';
            xlsTable += '  th    {font-weight:900}';
            xlsTable += '  td    {font-weight:300;}';
            xlsTable += '  .text {ms-number-format:"\@";}';
            xlsTable += '</style>';
            xlsTable += '<body>';
            xlsTable += '<table>';
            xlsTable += '<thead>';
            xlsTable += '<tr><th colspan="' + arr.length + '" style="background-color:silver">' + self.Js.relTitulo + '</th></tr>';
            xlsTable += '<tr>';
            for (ii = 0; col = arr[ii]; ii++) {
                xlsTable += '<th style="width:' + col.tamGrd + ';background-color:silver;border:1px solid black;">' + col.labelCol + '</th>';
            };
            xlsTable += '</tr>';
            xlsTable += '</thead>';
            xlsTable += '<tbody>';
            //Tabela de formatação para colunas
            //http://cosicimiento.blogspot.com.br/2008/11/styling-excel-cells-with-mso-number.html
            for (li = 0; li < nl; li++) {
                //Inibindo as linhas filtradas 
                if (table.rows[li].style.display == 'none')
                    continue;
                xlsTable += '<tr>';
                for (ii = 0; col = arr[ii]; ii++) {
                    if (col.fieldType.toLowerCase() == 'str') {
                        if (parseFloat(table.rows[li].cells[col.id].innerHTML) >= 0)
                            xlsTable += "<td class='text'>'" + table.rows[li].cells[col.id].innerHTML + '</td>';
                        else
                            xlsTable += "<td>" + table.rows[li].cells[col.id].innerHTML + '</td>';
                    } else if (col.fieldType.toLowerCase() == 'flo2') {
                        xlsTable += '<td style="mso-number-format:\'#,##0.00\'">' + table.rows[li].cells[col.id].innerHTML + '</td>';
                    } else if (col.fieldType.toLowerCase() == 'flo4') {
                        xlsTable += '<td style="mso-number-format:\'#,##0.0000\'">' + table.rows[li].cells[col.id].innerHTML + '</td>';
                    } else if (col.fieldType.toLowerCase() == 'flo8') {
                        xlsTable += '<td style="mso-number-format:\'#,##0.00000000\'">' + table.rows[li].cells[col.id].innerHTML + '</td>';
                    } else if (col.fieldType.substring(0, 1).toLowerCase() == 'int') {
                        xlsTable += "<td>'" + table.rows[li].cells[col.id].innerHTML + '</td>';
                    } else if (col.fieldType.substring(0, 1).toLowerCase() != 'dat') {
                        xlsTable += "<td style='Short Date'>" + table.rows[li].cells[col.id].innerHTML + '</td>';
                    }
                };
                xlsTable += '</tr>';
            };
            xlsTable += '</tbody>';
            xlsTable += '<tr><th colspan="' + arr.length + '" style="background-color:silver;border-top:1px solid black;">FIM DE ARQUIVO</th></tr>';
            xlsTable += '</table>';
            xlsTable += '</body>';
            xlsTable += '</html>';
            ////////////////////////////////
            // Download do arquivo gerado //
            ////////////////////////////////
            var dt = new Date();
            var day = dt.getDate();
            var month = dt.getMonth() + 1;
            var year = dt.getFullYear();
            var hour = dt.getHours();
            var mins = dt.getMinutes();
            var arquivo = self.Js.relTitulo.replace(/ /g, '') + '.xls';
            //////////////////////////////////  
            //Se o browser aceita BLOB      //
            //IE, Chrome e FireFox aceitam  //
            //////////////////////////////////
            if (window.Blob) {
                var textFileAsBlob = new Blob([xlsTable], {
                    type: 'text/plain'
                });

                var fileNameToSaveAs = "output.xls";
                var downloadLink = document.createElement("a");
                downloadLink.download = arquivo;
                downloadLink.innerHTML = "Download File";
                if (window.webkitURL != null) {
                    // o chrome permite que o link seja clicado sem inserir ele no DOM (fisicamente na pagina)
                    downloadLink.href = window.webkitURL.createObjectURL(textFileAsBlob);
                } else {
                    // O Firefox não permite clicar no link se nao existir na pagina, por isso precisa da funca para
                    // pagar o mesmo após ser clicado
                    downloadLink.href = window.URL.createObjectURL(textFileAsBlob);
                    downloadLink.onclick = destroyClickedElement;
                    downloadLink.style.display = "none";
                    document.body.appendChild(downloadLink);
                }
                ////////////////////////////////////
                // IE salva o arquivo deste jeito //
                ////////////////////////////////////
                if (navigator.msSaveBlob) {
                    navigator.msSaveBlob(textFileAsBlob, arquivo);
                    ////////////////////////////////////////////////////////////////////
                    // Firefox e Chrome permitem clicar no link para salvar o arquivo //
                    ////////////////////////////////////////////////////////////////////
                } else {
                    downloadLink.click();
                }
            } else {
                SaveContents();
            }
        };
    };
    ////////////////////////////////////////////////////////////////////////////
    // esta função apaga o link gerado para baixar o arquivo (xls) no firefox //
    ////////////////////////////////////////////////////////////////////////////
    function destroyClickedElement(event) {
        document.body.removeChild(event.target);
    }
    //
    this.camposPadrao2017 = function() {
        //////////////////////////////////////////////////////////////
        // Campo chave que deve existir em todas as tags "padrao":n //
        // n[0]=Valido para todas as tags                           //
        // n[1]=Campo checkbox OPC                                  //
        // n[2]=Campo combobox para sim/nao                         //
        // n[3]=Campo combobox para sis                             //
        // n[4]=Campo nome do usuario                               //
        // n[5]=Campo codigo do usuario                             //
        // n[6]=Campo codigo do direito de usuario                  //
        // n[7]=Campo codigo da empresa                             //
        // n[8]=Imagem do PP                                        //
        //////////////////////////////////////////////////////////////
        self.Js.titulo.forEach(function(reg, lin) {
            switch (self.Js.titulo[lin].padrao) {
                case 1: // opc
                    self.Js.titulo[lin].fieldType = (self.Js.titulo[lin].fieldType == undefined ? "chk" : self.Js.titulo[lin].fieldType);
                    self.Js.titulo[lin].obj = (self.Js.titulo[lin].obj == undefined ? "cbOpc" : self.Js.titulo[lin].obj);
                    self.Js.titulo[lin].tamGrd = (self.Js.titulo[lin].tamGrd == undefined ? "3em" : self.Js.titulo[lin].tamGrd);
                    self.Js.titulo[lin].tipo = (self.Js.titulo[lin].tipo == undefined ? "chk" : self.Js.titulo[lin].tipo);
                    break;
                case 2: // combobox sim/nao         
                    self.Js.titulo[lin].contido = (self.Js.titulo[lin].contido == undefined ? ['S', 'N'] : self.Js.titulo[lin].contido);
                    self.Js.titulo[lin].copyGRD = (self.Js.titulo[lin].copyGRD == undefined ? [0, 3] : self.Js.titulo[lin].copyGRD);
                    self.Js.titulo[lin].digitosMinMax = (self.Js.titulo[lin].digitosMinMax == undefined ? [1, 1] : self.Js.titulo[lin].digitosMinMax);
                    self.Js.titulo[lin].fieldType = (self.Js.titulo[lin].fieldType == undefined ? "str" : self.Js.titulo[lin].fieldType);
                    self.Js.titulo[lin].html = (self.Js.titulo[lin].html == undefined ? ['S', 25, 'S|N', 'SIM|NAO'] : self.Js.titulo[lin].html);
                    self.Js.titulo[lin].newRecord = (self.Js.titulo[lin].newRecord == undefined ? ['S', 'this', 'this'] : self.Js.titulo[lin].newRecord);
                    self.Js.titulo[lin].tamGrd = (self.Js.titulo[lin].tamGrd == undefined ? "4em" : self.Js.titulo[lin].tamGrd);
                    self.Js.titulo[lin].tamImp = (self.Js.titulo[lin].tamImp == undefined ? "15" : self.Js.titulo[lin].tamImp);
                    self.Js.titulo[lin].tipo = (self.Js.titulo[lin].tipo == undefined ? "cb" : self.Js.titulo[lin].tipo);
                    self.Js.titulo[lin].validar = (self.Js.titulo[lin].validar == undefined ? ['notnull'] : self.Js.titulo[lin].validar);
                    self.Js.titulo[lin].funcCor = (self.Js.titulo[lin].funcCor == undefined ? "(objCell.innerHTML=='NAO' ? objCell.classList.add('corAlterado') : " +
                        "objCell.classList.remove('corAlterado'))" : self.Js.titulo[lin].funcCor);
                    ///////////////////////////////////////////                                                                                    
                    // Complemento para detalhe do registrso //
                    ///////////////////////////////////////////
                    if (self.Js.titulo[lin].labelCol == "ATIVO") {
                        self.Js.titulo[lin].lblDetalhe = "ATIVO";
                        self.Js.titulo[lin].ajudaDetalhe = "Se o registro pode ser usado por cadastros complementares";
                    };
                    break;
                case 3: // combobox sis
                    self.Js.titulo[lin].contido = (self.Js.titulo[lin].contido == undefined ? ['P', 'A'] : self.Js.titulo[lin].contido);
                    self.Js.titulo[lin].copyGRD = (self.Js.titulo[lin].copyGRD == undefined ? [0, 3] : self.Js.titulo[lin].copyGRD);
                    self.Js.titulo[lin].digitosMinMax = (self.Js.titulo[lin].digitosMinMax == undefined ? [1, 1] : self.Js.titulo[lin].digitosMinMax);
                    self.Js.titulo[lin].html = (self.Js.titulo[lin].html == undefined ? ['S', 25, 'P|A', 'PUBLICO|ADMINISTRADOR'] : self.Js.titulo[lin].html);
                    self.Js.titulo[lin].newRecord = (self.Js.titulo[lin].newRecord == undefined ? ['P', 'this', 'this'] : self.Js.titulo[lin].newRecord);
                    self.Js.titulo[lin].tamGrd = (self.Js.titulo[lin].tamGrd == undefined ? "4em" : self.Js.titulo[lin].tamGrd);
                    self.Js.titulo[lin].tamImp = (self.Js.titulo[lin].tamImp == undefined ? "15" : self.Js.titulo[lin].tamImp);
                    self.Js.titulo[lin].tipo = (self.Js.titulo[lin].tipo == undefined ? "cb" : self.Js.titulo[lin].tipo);
                    self.Js.titulo[lin].validar = (self.Js.titulo[lin].validar == undefined ? ['notnull'] : self.Js.titulo[lin].validar);
                    self.Js.titulo[lin].funcCor = (self.Js.titulo[lin].funcCor == undefined ? "(objCell.innerHTML=='SIS' ? objCell.classList.add('corAzul') : " +
                        "objCell.classList.remove('corAzul'))" : parJS.titulo[lin].funcCor);
                    /////////////////////////////////////                                                                                    
                    // se o registro pode ser alterado //
                    /////////////////////////////////////  
                    self.Js.titulo[lin].altRegistro = (self.Js.titulo[lin].altRegistro == undefined ? "N" : self.Js.titulo[lin].altRegistro);
                    ///////////////////////////////////////////                                                                                    
                    // Complemento para detalhe do registrso //
                    ///////////////////////////////////////////
                    self.Js.titulo[lin].lblDetalhe = "REGISTRO";
                    self.Js.titulo[lin].ajudaDetalhe = "Se o registro é PUBlico/ADMinistrador ou do SIStema";
                    break;

                case 4: // nome do usuario
                    self.Js.titulo[lin].html = (self.Js.titulo[lin].html == undefined ? ['S', 25] : self.Js.titulo[lin].html);
                    self.Js.titulo[lin].insUpDel = (self.Js.titulo[lin].insUpDel == undefined ? ['N', 'N', 'N'] : self.Js.titulo[lin].insUpDel);
                    self.Js.titulo[lin].newRecord = (self.Js.titulo[lin].newRecord == undefined ? [jsPub[0].DESUSU, jsPub[0].DESUSU, jsPub[0].DESUSU] : self.Js.titulo[lin].newRecord);
                    self.Js.titulo[lin].tamGrd = (self.Js.titulo[lin].tamGrd == undefined ? "10em" : self.Js.titulo[lin].tamGrd);
                    self.Js.titulo[lin].tamImp = (self.Js.titulo[lin].tamImp == undefined ? "30" : self.Js.titulo[lin].tamImp);
                    self.Js.titulo[lin].tipo = (self.Js.titulo[lin].tipo == undefined ? "edt" : self.Js.titulo[lin].tipo);
                    ///////////////////////////////////////////                                                                                    
                    // Complemento para detalhe do registrso //
                    ///////////////////////////////////////////
                    self.Js.titulo[lin].lblDetalhe = "USUARIO";
                    self.Js.titulo[lin].ajudaDetalhe = "Último usuário dar manutenção no registro";
                    break;

                case 5: // codigo do usuario
                    self.Js.titulo[lin].excel = (self.Js.titulo[lin].excel == undefined ? "N" : self.Js.titulo[lin].excel);
                    self.Js.titulo[lin].fieldType = (self.Js.titulo[lin].fieldType == undefined ? "int" : self.Js.titulo[lin].fieldType);
                    self.Js.titulo[lin].hint = (self.Js.titulo[lin].hint == undefined ? "N" : self.Js.titulo[lin].hint);
                    self.Js.titulo[lin].html = (self.Js.titulo[lin].html == undefined ? ['S', 0] : self.Js.titulo[lin].html);
                    self.Js.titulo[lin].newRecord = (self.Js.titulo[lin].newRecord == undefined ? [jsPub[0].usr_codigo, jsPub[0].usr_codigo, jsPub[0].usr_codigo] : self.Js.titulo[lin].newRecord);
                    self.Js.titulo[lin].ordenaColuna = (self.Js.titulo[lin].ordenaColuna == undefined ? "N" : self.Js.titulo[lin].ordenaColuna);
                    self.Js.titulo[lin].tamGrd = (self.Js.titulo[lin].tamGrd == undefined ? "0em" : self.Js.titulo[lin].tamGrd);
                    self.Js.titulo[lin].tipo = (self.Js.titulo[lin].tipo == undefined ? "edt" : self.Js.titulo[lin].tipo);
                    break;
                case 6: // codigo do direito do usuario
                    self.Js.titulo[lin].excel = (self.Js.titulo[lin].excel == undefined ? "N" : self.Js.titulo[lin].excel);
                    self.Js.titulo[lin].fieldType = (self.Js.titulo[lin].fieldType == undefined ? "int" : self.Js.titulo[lin].fieldType);
                    self.Js.titulo[lin].hint = (self.Js.titulo[lin].hint == undefined ? "N" : self.Js.titulo[lin].hint);
                    self.Js.titulo[lin].html = (self.Js.titulo[lin].html == undefined ? ['S', 0] : self.Js.titulo[lin].html);
                    self.Js.titulo[lin].newRecord = (self.Js.titulo[lin].newRecord == undefined ? [intCodDir, intCodDir, intCodDir] : self.Js.titulo[lin].newRecord);
                    self.Js.titulo[lin].ordenaColuna = (self.Js.titulo[lin].ordenaColuna == undefined ? "N" : self.Js.titulo[lin].ordenaColuna);
                    self.Js.titulo[lin].tamGrd = (self.Js.titulo[lin].tamGrd == undefined ? "0em" : self.Js.titulo[lin].tamGrd);
                    self.Js.titulo[lin].tipo = (self.Js.titulo[lin].tipo == undefined ? "edt" : self.Js.titulo[lin].tipo);
                    self.Js.titulo[lin].validar = (self.Js.titulo[lin].validar == undefined ? ['notnull', 'intMaiorIgualZero'] : self.Js.titulo[lin].validar);
                    break;

                case 7: // codigo da empresa
                    self.Js.titulo[lin].excel = (self.Js.titulo[lin].excel == undefined ? "N" : self.Js.titulo[lin].excel);
                    self.Js.titulo[lin].fieldType = (self.Js.titulo[lin].fieldType == undefined ? "int" : self.Js.titulo[lin].fieldType);
                    self.Js.titulo[lin].hint = (self.Js.titulo[lin].hint == undefined ? "N" : self.Js.titulo[lin].hint);
                    self.Js.titulo[lin].html = (self.Js.titulo[lin].html == undefined ? ['S', 0] : self.Js.titulo[lin].html);
                    self.Js.titulo[lin].insUpDel = (self.Js.titulo[lin].insUpDel == undefined ? ['S', 'N', 'N'] : self.Js.titulo[lin].insUpDel);
                    self.Js.titulo[lin].newRecord = (self.Js.titulo[lin].newRecord == undefined ? [jsPub[0].EMP_CODIGO, jsPub[0].EMP_CODIGO, 'this'] : self.Js.titulo[lin].newRecord);
                    self.Js.titulo[lin].ordenaColuna = (self.Js.titulo[lin].ordenaColuna == undefined ? "N" : self.Js.titulo[lin].ordenaColuna);
                    self.Js.titulo[lin].tamGrd = (self.Js.titulo[lin].tamGrd == undefined ? "0em" : self.Js.titulo[lin].tamGrd);
                    self.Js.titulo[lin].tipo = (self.Js.titulo[lin].tipo == undefined ? "edt" : self.Js.titulo[lin].tipo);
                    self.Js.titulo[lin].validar = (self.Js.titulo[lin].validar == undefined ? ['notnull', 'intMaiorIgualZero'] : self.Js.titulo[lin].validar);
                    break;
                case 8: // imagem do passo-a-passo
                    self.Js.titulo[lin].fieldType = (self.Js.titulo[lin].fieldType == undefined ? "img" : self.Js.titulo[lin].fieldType);
                    self.Js.titulo[lin].tamGrd = (self.Js.titulo[lin].tamGrd == undefined ? "5em" : self.Js.titulo[lin].tamGrd);
                    self.Js.titulo[lin].tagI = (self.Js.titulo[lin].tagI == undefined ? "fa fa-binoculars passoApasso" : self.Js.titulo[lin].tagI);
                    self.Js.titulo[lin].tipo = (self.Js.titulo[lin].tipo == undefined ? "img" : self.Js.titulo[lin].tipo);
                    break;
                case 9: // passo a passo
                    self.Js.titulo[lin].excel = (self.Js.titulo[lin].excel == undefined ? "S" : self.Js.titulo[lin].excel);
                    self.Js.titulo[lin].ordenaColuna = (self.Js.titulo[lin].ordenaColuna == undefined ? "N" : self.Js.titulo[lin].ordenaColuna);
                    self.Js.titulo[lin].fieldType = (self.Js.titulo[lin].fieldType == undefined ? "str" : self.Js.titulo[lin].fieldType);
                    break;
            };
            if (tagValida(self.Js.titulo[lin].field)) {
                self.Js.titulo[lin].excel = (self.Js.titulo[lin].excel == undefined ? "S" : self.Js.titulo[lin].excel);
                self.Js.titulo[lin].fieldType = (self.Js.titulo[lin].fieldType == undefined ? "str" : self.Js.titulo[lin].fieldType);
                self.Js.titulo[lin].inputDisabled = (self.Js.titulo[lin].inputDisabled == undefined ? false : self.Js.titulo[lin].inputDisabled);
                switch (self.Js.titulo[lin].fieldType) {
                    case 'str':
                        self.Js.titulo[lin].formato = (self.Js.titulo[lin].formato == undefined ? ['uppercase', 'removeacentos', 'tiraaspas'] : self.Js.titulo[lin].formato);
                        self.Js.titulo[lin].validar = (self.Js.titulo[lin].validar == undefined ? ['notnull'] : self.Js.titulo[lin].validar); // aceita null validar['podeNull']
                        break;
                    case 'int':
                        self.Js.titulo[lin].validar = (self.Js.titulo[lin].validar == undefined ? ['notnull', 'intMaiorZero'] : self.Js.titulo[lin].validar);
                        break;
                    case 'flo':
                    case 'flo2':
                    case 'flo4':
                    case 'flo8':
                        self.Js.titulo[lin].formato = (self.Js.titulo[lin].formato == undefined ? ['flo2'] : self.Js.titulo[lin].formato);
                        self.Js.titulo[lin].sepMilhar = (self.Js.titulo[lin].sepMilhar == undefined ? false : self.Js.titulo[lin].sepMilhar);
                        break;
                };
                self.Js.titulo[lin].hint = (self.Js.titulo[lin].hint == undefined ? "S" : self.Js.titulo[lin].hint);
                self.Js.titulo[lin].ordenaColuna = (self.Js.titulo[lin].ordenaColuna == undefined ? "S" : self.Js.titulo[lin].ordenaColuna);
                self.Js.titulo[lin].pk = (self.Js.titulo[lin].pk == undefined ? "N" : self.Js.titulo[lin].pk);
                switch (self.Js.titulo[lin].pk) {
                    case 'S':
                        self.Js.titulo[lin].insUpDel = (self.Js.titulo[lin].insUpDel == undefined ? ['S', 'N', 'N'] : self.Js.titulo[lin].insUpDel);
                        break;
                    case 'N':
                        self.Js.titulo[lin].insUpDel = (self.Js.titulo[lin].insUpDel == undefined ? ['S', 'S', 'N'] : self.Js.titulo[lin].insUpDel);
                        self.Js.titulo[lin].newRecord = (self.Js.titulo[lin].newRecord == undefined ? ['', 'this', 'this'] : self.Js.titulo[lin].newRecord);
                        break;
                };
                self.Js.titulo[lin].tipo = (self.Js.titulo[lin].tipo == undefined ? "edt" : self.Js.titulo[lin].tipo);
            };
        });
        //////////////////////////
        // Detalhe do registro  //
        //////////////////////////
        if (tagValida(self.Js.detalheRegistro)) {
            self.Js.detalheRegistro[0].colConteudo = (self.Js.detalheRegistro[0].colConteudo == undefined ? "70%" : self.Js.detalheRegistro[0].colConteudo);
            self.Js.detalheRegistro[0].colDescricao = (self.Js.detalheRegistro[0].colDescricao == undefined ? "30%" : self.Js.detalheRegistro[0].colDescricao);
            self.Js.detalheRegistro[0].fontSize = (self.Js.detalheRegistro[0].fontSize == undefined ? "1.2em%" : self.Js.detalheRegistro[0].fontSize);
            self.Js.detalheRegistro[0].height = (self.Js.detalheRegistro[0].height == undefined ? "400px" : self.Js.detalheRegistro[0].height);
            self.Js.detalheRegistro[0].left = (self.Js.detalheRegistro[0].left == undefined ? "40em" : self.Js.detalheRegistro[0].left);
            self.Js.detalheRegistro[0].top = (self.Js.detalheRegistro[0].top == undefined ? "1em" : self.Js.detalheRegistro[0].top);
            self.Js.detalheRegistro[0].width = (self.Js.detalheRegistro[0].width == undefined ? "100%" : self.Js.detalheRegistro[0].width);
        };
        //////////////////////
        // Detalhe do erro  //
        //////////////////////
        if (tagValida(self.Js.detalheErro)) {
            self.Js.detalheErro[0].colConteudo = (self.Js.detalheErro[0].colConteudo == undefined ? "70%" : self.Js.detalheErro[0].colConteudo);
            self.Js.detalheErro[0].colDescricao = (self.Js.detalheErro[0].colDescricao == undefined ? "30%" : self.Js.detalheErro[0].colDescricao);
            self.Js.detalheErro[0].fontSize = (self.Js.detalheErro[0].fontSize == undefined ? "1.2em%" : self.Js.detalheErro[0].fontSize);
            self.Js.detalheErro[0].height = (self.Js.detalheErro[0].height == undefined ? "400px" : self.Js.detalheErro[0].height);
            self.Js.detalheErro[0].left = (self.Js.detalheErro[0].left == undefined ? "40em" : self.Js.detalheErro[0].left);
            self.Js.detalheErro[0].top = (self.Js.detalheErro[0].top == undefined ? "1em" : self.Js.detalheErro[0].top);
            self.Js.detalheErro[0].width = (self.Js.detalheErro[0].width == undefined ? "100%" : self.Js.detalheErro[0].width);
        };
        ////////////////////////
        // Configuração geral //
        ////////////////////////
        self.Js.paginacao = (self.Js.paginacao == undefined ? "0" : self.Js.paginacao);
        self.Js.opcRegSeek = (self.Js.opcRegSeek == undefined ? true : self.Js.opcRegSeek);
    };
    //
    this.gerarJson = function(qtos) {
        return {
            somenteChecados: true,
            colChk: true //Se a classe tem uma tabela com a coluna checkbox  
                ,
            podeZero: false //Se a classe pode ter a grade sem nenhum registro checado
                ,
            retQtos: (qtos == undefined ? "1" : qtos),
            retornarQtos: function(str) {
                this.retQtos = str;
            },
            temColChk: function(bol) {
                this.colChk = bol;
            },
            nenhumChecado: function(bol) {
                this.podeZero = bol;
            },
            gerar: function() {
                var obj = document.getElementById(self.Js.tbl);
                var table = obj.getElementsByTagName("tbody")[0];
                var nl = table.rows.length;
                var nc = 0;
                var colChk = -1;
                var el = "";
                var arrTit = [];
                var cntd = '';
                var retorno = '[';
                var linI = 0;
                var qtos = 0;
                var ret = "";
                /////////////////////////////////////////////////////////////////////////
                // Booleano opcional para quando se quer somente os registros checados //
                /////////////////////////////////////////////////////////////////////////
                if (this.somenteChecados) {
                    if (this.colChk) {
                        self.Js.titulo.forEach(function(tit) {
                            if (tit.tipo == "chk")
                                colChk = tit.id;
                        });
                        if (colChk == -1)
                            throw "NENHUMA COLUNA CHK ENCONTRADA!";
                    };
                };
                ////////////////////////
                // Pegando os titulos //
                ////////////////////////
                el = obj.getElementsByTagName('thead')[0].getElementsByTagName('th');
                nc = el.length;
                for (var col = 0; col < nc; col++)
                    arrTit.push((el[col].innerHTML).toUpperCase());
                ////////////////////////
                // Pegando os valores //
                ////////////////////////
                el = obj.getElementsByTagName('tbody')[0];
                nl = el.rows.length;
                if (nl > 0) {
                    nc = el.rows[nl - 1].cells.length;
                    linI = -1;
                    for (var lin = 0; lin < nl; lin++) {
                        if ((this.somenteChecados) && (this.colChk) && (el.rows[lin].cells[colChk].children[0].checked == false))
                            continue;
                        linI++;
                        qtos++;
                        retorno += (linI == 0 ? '{' : ',{');
                        for (var col = 0; col < nc; col++) {
                            cntd = el.rows[lin].cells[col].innerHTML;
                            if (cntd.substring(0, 1) == '<')
                                cntd = '';
                            retorno += (col == 0 ? '' : ',') + '"' + arrTit[col] + '":"' + cntd + '"';
                        };
                        retorno += '}';
                    };
                    retorno += ']';
                    ret = JSON.parse(retorno);
                    //////////////////////////////////////////////
                    // Se for para retornar apenas um registro  //
                    // A chamada de deve estar dentro de um try //
                    //////////////////////////////////////////////
                    if ((this.retQtos == "1") && (qtos != 1)) {
                        throw new Error("FAVOR SELECIONAR APENAS UM REGISTRO!");
                    };
                } else {
                    // retornando um json com tam 0 exemplo ERP_SatConsultaFin.php
                    ret = JSON.parse('[]');
                };
                //03dez2017 if( (qtos==0) && (this.somenteChecados) )
                if ((qtos == 0) && (this.somenteChecados) && (this.podeZero == false))
                    throw new Error("NENHUM REGISTRO SELECIONADO!");
                return ret;
            }
        };
    };
    ////////////////////////////////////////////////////////////
    // Marcar/desmarcar todos registros                       //
    ////////////////////////////////////////////////////////////
    this.marcarDesmarcar = function() {
        var colChk = -1;
        var tam = self.Js.titulo.length;
        for (var lin = 0; lin < tam; lin++) {
            if ((['chk']).indexOf(self.Js.titulo[lin].fieldType) != -1) {
                colChk = lin;
                break;
            };
        };
        if (colChk != -1) {
            var obj = document.getElementById(self.Js.tbl);
            var el = obj.getElementsByTagName('tbody')[0];
            var nl = el.rows.length;
            var nc;
            if (nl > 0) {
                nc = el.rows[nl - 1].cells.length;
                for (var lin = 0; lin < nl; lin++) {
                    if (el.rows[lin].cells[colChk].children[0].checked == false) {
                        el.rows[lin].cells[colChk].children[0].checked = true;
                        el.rows[lin].cells[colChk].parentNode.classList.add('corGradeParCheck');
                    } else {
                        el.rows[lin].cells[colChk].children[0].checked = false;
                        el.rows[lin].cells[colChk].parentNode.classList.remove('corGradeParCheck');
                    };
                };
            };
        };
    };
    this.filtraTbl = function(tbl, conteudo, lbl) {
        var col = 0;
        self.Js.titulo.forEach(function(e) {
            if (e.labelCol == lbl) {
                col = e.id;
                return false;
            };
        });
        var el = document.getElementById(tbl).getElementsByTagName('tbody')[0];
        var tam = el.rows.length;
        for (var lin = 0; lin < tam; lin++) {
            if (el.rows[lin].cells[col].innerHTML.indexOf(conteudo.toUpperCase()) < 0)
                el.rows[lin].style.display = 'none';
            else
                el.rows[lin].style.display = 'table-row';
        };
    };
    ////////////////////////////////////////////////////////////
    // Ordenando o JSON para não ter que fazer isso na grade  //
    ////////////////////////////////////////////////////////////
    this.ordenaJSon = function(strCol, refazBody) {
        var seq = -1;
        var col = 0;
        var tipo = "";
        var coluna = (strCol == undefined ? self.Js.indiceTable : strCol);
        var tam = (self.Js.titulo).length;
        var reg;
        var datA;
        var datB;
        //////////////////////////////
        // Usando for devido break  //
        //////////////////////////////
        for (var lin = 0; lin < tam; lin++) {
            reg = self.Js.titulo[lin];
            if (['chk', 'img'].indexOf(reg.tipo) == -1) {
                seq++;
                if (reg.labelCol == coluna) {
                    document.getElementById("lblProcurar_" + self.Js.tbl).innerHTML = coluna;
                    col = seq;
                    tipo = reg.fieldType;
                    break;
                };
            };
        };
        ////////////////////////////////////////
        // Ordenando o Json para encher table //
        ////////////////////////////////////////
        self.Js.registros.sort(function(a, b) {
            switch (tipo) {
                case 'dat':
                    datA = a[col].replace(/\D/g, "").replace(/(\d{2})(\d{2})(\d{4})/, "$3$2$1");
                    datB = b[col].replace(/\D/g, "").replace(/(\d{2})(\d{2})(\d{4})/, "$3$2$1");
                    return (parseInt(datA) > parseInt(datB) ? 1 : parseFloat(datA) < parseFloat(datB) ? -1 : 0);
                    break;

                case "flo":
                case "flo2":
                case "flo4":
                case "flo8":
                    return (parseFloat(a[col]) > parseFloat(b[col]) ? 1 : parseFloat(a[col]) < parseFloat(b[col]) ? -1 : 0);
                    break;
                case "int":
                    return (parseInt(a[col]) > parseInt(b[col]) ? 1 : parseInt(a[col]) < parseInt(b[col]) ? -1 : 0);
                    break;
                default:
                    return (a[col] > b[col] ? 1 : a[col] < b[col] ? -1 : 0);
                    break;
            };
        });
        seq = 0;
        if ((tagValida(self.Js.paginacao)) && (self.Js.paginacao > 0)) {
            var quebraPag = 1;
            var numCampos = (self.Js.registros[0].length - 1);
            self.Js.registros.forEach(function(reg, lin) {
                reg[numCampos] = quebraPag;
                seq++;
                if (seq == self.Js.paginacao) {
                    quebraPag++;
                    seq = 0;
                }
            });
            self.totalPag = quebraPag;
        };
        if (refazBody)
            this.montarBody2017();
    };
    //////////////////////////////////////////
    // Corre a table procurando o rowIndex  //
    //////////////////////////////////////////
    this.buscaRowIndexTable = function(_id) {
        var obj = document.getElementById(self.Js.tbl);
        var table = obj.getElementsByTagName("tbody")[0];
        var nl = table.rows.length;
        for (var li = 0; li < nl; li++) {
            if (parseInt(table.rows[li].cells[self.addId[2]].innerHTML) == _id) {
                return table.rows[li].rowIndex;
            };
        };
    };
    ////////////////////////////////////////////////////////////////
    // Corre o ARRAY/JSON procurando o rowIndex(indice do vetor)  //
    ////////////////////////////////////////////////////////////////
    this.buscaRowIndexArray = function(_id) {
        var tam = (self.Js.registros).length;
        for (var reg = 0; reg < tam; reg++) {
            if (self.Js.registros[reg][self.addId[1]] == parseInt(_id)) {
                return reg;
            };
        };
    };
    ////////////////////////////////////////////////////////////////
    // Ajuda para campo Sis/Ativo                                 //
    ////////////////////////////////////////////////////////////////
    this.AjudaSisAtivo = function(parJS) {
        try {
            var ceDiv = '';
            var ceDivH = '';
            var ceContext = '';
            var ceH2 = "";
            var ceP = "";
            var ceHr = "";
            var ceStrong = "";
            /////////////////////////////////////////////////////
            // Buscando as tags parametrizadas para esta opção //
            /////////////////////////////////////////////////////  
            var tamanho = parJS.titulo.length;
            var html = "";
            var excel = "";
            var allExcel = "";
            var arrTam = 0; //Para quebrar por LI
            var colExcel = "ABCDEFGHIJKLMNOPQRSTUV";
            var posExcel = 0;
            var cmpObrigatorio;
            for (var linha = 0; linha < tamanho; linha++) {
                if (tagValida(parJS.titulo[linha].ajudaCampo)) {
                    if (tagValida(parJS.titulo[linha].validar)) {
                        cmpObrigatorio = (parJS.titulo[linha].validar[0] != "podeNull" ? "" : " não obrigatório");
                    }
                    arrTam = (parJS.titulo[linha].ajudaCampo).length;
                    html += '<tr>';
                    html += '<td class="tableHelpTitulo">Campo ' + parJS.titulo[linha].labelCol + ' ' + cmpObrigatorio + '</td>';
                    html += '</tr>';
                    html += '<tr>';
                    html += '<td class="tableHelpTexto">';
                    html += '<ul class="tableHelpUl">';
                    for (var arrLin = 0; arrLin < arrTam; arrLin++) {
                        html += '<li class="tableHelpLiUltimo">' + parJS.titulo[linha].ajudaCampo[arrLin] + '</li>';
                    }
                    html += '</ul>';
                    html += '</td>';
                    html += '</tr>';
                    html += '<tr>'
                    html += '<td><hr></td>'
                    html += '</tr>'
                };
                if ((tagValida(parJS.titulo[linha].importaExcel)) && (parJS.titulo[linha].importaExcel == "S")) {
                    excel += '<li class="tableHelpLiUltimo">COLUNA <b>' + colExcel.substring(posExcel, (posExcel + 1)) + '</b> TEXTO:' + parJS.titulo[linha].labelCol + '</li>';
                    posExcel++;
                };
            };
            if (excel != "") {
                allExcel += '<tr>';
                allExcel += '<td class="tableHelpTitulo">Importação via planilha excel</td>';
                allExcel += '</tr>';
                allExcel += '<tr>';
                allExcel += '<td class="tableHelpTexto">';
                allExcel += '<ul class="tableHelpUl">';
                allExcel += '<li class="tableHelpLiUltimo">Crie uma nova planilha e na linha 1 insira estes campos na sequencia de colunas.</li>';
                allExcel += excel;
                allExcel += '<li class="tableHelpLiUltimoB">Salve a planilha no formato xml 2003 e selecione importar planilha excel.</li>';
                allExcel += '<li class="tableHelpLiUltimoB">Devido ao formato .xml todos os campos devem ser preenchidos.</li>';
                allExcel += '</ul>';
                allExcel += '</td>';
                allExcel += '</tr>';
                allExcel += '<tr>';
                allExcel += '<td><hr></td>';
                allExcel += '</tr>';
            };
            /////////////////////////////////////////////////////
            // Checando se os campos existem para exibir ajuda //
            /////////////////////////////////////////////////////
            var cmpEspiao = ((tagValida(parJS.tabelaBKP) && (parJS.tabelaBKP.length > 1)) ? true : false);
            var cmpAtivo = ((tagValida(parJS.fieldAtivo) && (parJS.fieldAtivo.length > 1)) ? true : false);
            var cmpReg = ((tagValida(parJS.fieldReg) && (parJS.fieldReg.length > 1)) ? true : false);
            var cmpUsu = ((tagValida(parJS.fieldCodUsu) && (parJS.fieldCodUsu.length > 1)) ? true : false);
            var cmpCodDir = ((tagValida(parJS.codDir) && tagValida(parJS.codTblUsu)) ? true : false);
            //
            //
            ceDivH = document.createElement("div");
            var texto = "";
            texto += '<table id="idAjuda" class="tableHelp">';
            texto += '<tbody class="tableHelpBody">';
            if (cmpCodDir) {
                texto += '<tr>';
                texto += '<td class="tableHelpTitulo">Direito de usuario->Opção: ' + parJS.codTblUsu + '. Usuário:' + jsPub[0].DESUSU + ' com flag: ' + parJS.codDir + '</td>';
                texto += '</tr>';
                texto += '<tr>';
                texto += '<td class="tableHelpTexto">';
                texto += '<ul class="tableHelpUl">';
                texto += '<li class="tableHelpLiUltimo">Somente colaboradores com direitos na rotina de usuários do sistema podem alterar este parametro.</li>';
                texto += '<li class="' + (parJS.codDir == 0 ? "tableHelpLiUltimoB" : "tableHelpLiUltimo") + '">Flag 0: Sem direito.</li>';
                texto += '<li class="' + (parJS.codDir == 1 ? "tableHelpLiUltimoB" : "tableHelpLiUltimo") + '">Flag 1: Somente consultar.</li>';
                texto += '<li class="' + (parJS.codDir == 2 ? "tableHelpLiUltimoB" : "tableHelpLiUltimo") + '">Flag 2: Consultar/Cadastrar.</li>';
                texto += '<li class="' + (parJS.codDir == 3 ? "tableHelpLiUltimoB" : "tableHelpLiUltimo") + '">Flag 3: Consultar/Cadastrar/Alterar.</li>';
                texto += '<li class="' + (parJS.codDir == 4 ? "tableHelpLiUltimoB" : "tableHelpLiUltimo") + '">Flag 4: Consultar/Cadastrar/Alterar/Excluir.</li>';
                texto += '</ul>';
                texto += '</td>';
                texto += '</tr>';
                texto += '<tr>';
                texto += '<td><hr></td>';
                texto += '</tr>';
            }
            texto += html;
            if (cmpAtivo) {
                texto += '<tr>';
                texto += '<td class="tableHelpTitulo">Campo ATIVO</td>';
                texto += '</tr>';
                texto += '<tr>';
                texto += '<td class="tableHelpTexto">';
                texto += '<ul class="tableHelpUl">';
                texto += '<li class="tableHelpLiUltimo">Informa ao <b>ERP</b> se o registro pode ser utilizado em cadastro complementar.</li>';
                texto += '</ul>';
                texto += '</td>';
                texto += '</tr>';
                texto += '<tr>';
                texto += '<td><hr></td>';
                texto += '</tr>';
            }
            if (cmpReg) {
                texto += '<tr>';
                texto += '<td class="tableHelpTitulo">Campo REG</td>';
                texto += '</tr>';
                texto += '<tr>';
                texto += '<td class="tableHelpTexto">';
                texto += '<ul class="tableHelpUl">';
                texto += '<li class="tableHelpLi">Informa ao <b>ERP</b> quais usuários podem dar manutenção no registro</li>';
                texto += '<li class="tableHelpLi">O registro pode ser <b>PUB</b>lico/<b>ADM</b>inistrador/<b>SIS</b>tema, no cadastro de usuário existe esta parametrização definindo o perfil de cada usuário</li>';
                texto += '<li class="tableHelpLi">Se o usuário logado é <b>PUBLICO</b> no cadastro de usuarios, este poderá apenas dar manutenção em registros PUB.</li>';
                texto += '<li class="tableHelpLi">Se o usuário logado é <b>ADMINISTRADOR</b> no cadastro de usuário este poderá dar manutenção em registros PUB/ADM e alterar este status</li>';
                texto += '<li class="tableHelpLiUltimo">Se o registro for <b>SIS</b> indica que o ERP precisa deste e não permitirá manutenção de nenhum usuário!</li>';
                texto += '</ul>';
                texto += '</td>';
                texto += '</tr>';
                texto += '<tr>';
                texto += '<td><hr></td>';
                texto += '</tr>';
            }
            if (cmpUsu) {
                texto += '<tr>';
                texto += '<td class="tableHelpTitulo">Campo USUARIO</td>';
                texto += '</tr>';
                texto += '<tr>';
                texto += '<td class="tableHelpTexto">';
                texto += '<ul class="tableHelpUl">';
                texto += '<li class="tableHelpLiUltimo">Informa o último usuario a dar manutenção no registro.</li>';
                texto += '</ul>';
                texto += '</td>';
                texto += '</tr>';
                texto += '<tr>';
                texto += '<td><hr></td>';
                texto += '</tr>';
            }
            if (cmpEspiao) {
                texto += '<tr>';
                texto += '<td class="tableHelpTitulo">Campo PP(Passo a passo)</td>';
                texto += '</tr>';
                texto += '<tr>';
                texto += '<td class="tableHelpTexto">';
                texto += '<ul class="tableHelpUl">';
                texto += '<li class="tableHelpLiUltimo">Informa toda atualização feita no registro e o usuário logado que efetuou a manutenção.</li>';
                texto += '</ul>';
                texto += '</td>';
                texto += '</tr>';
            };
            if (allExcel != "") {
                texto += allExcel;
            }


            texto += '</tbody>';
            texto += '</able>';
            ceDivH.innerHTML = texto;
            ///////////////
            // FIM TEXTO //  
            ///////////////
            ceDiv = document.createElement("div");
            ceDiv.style.height = "400px";
            ceDiv.style.overflowY = "auto";
            ceDiv.appendChild(ceDivH);
            //////////////////////////////////////
            // Montando o formulario para table //
            // Variaveis para modal             //
            //////////////////////////////////////
            contMsg++;
            var divModal = 'dm' + contMsg; // div modal
            var divMsg = 'dms' + contMsg; // div mensagem
            var maior = retornarZIndex(); // maior indice para Z-index  
            //////////////////////
            // Create Elements  //
            //////////////////////
            var ceModal = ''; //Div modal para marcar todo fundo como desabilitado
            var ceSec = '';
            var ceFrm = '';
            var cePar = '';
            var ceImg = '';
            var ceInp = '';
            var ceBut = '';
            var objImg = '';
            ceModal = document.createElement("div");
            ceModal.id = divModal;
            ceModal.className = "divShowModal";
            ceModal.style.zIndex = (maior);
            ////////////////
            // Formulario //
            ////////////////
            ceFrm = document.createElement("form");
            ceFrm.className = "formulario";
            ceFrm.id = divMsg;
            ceFrm.style.top = "1em";
            if ((tagValida(self.Js.tableLeft)) && (self.Js.tableLeft == "sim")) {
                ceFrm.style.left = "1em";
            } else {
                ceFrm.style.left = "15%";
            }
            ceFrm.style.width = "70%";
            ceFrm.style.height = "500px";

            ceFrm.style.position = "absolute";
            ceFrm.style.zIndex = (maior + 2);
            ////////////
            // titulo //
            ////////////
            cePar = document.createElement("p");
            cePar.className = "frmCampoTit";

            ceInp = document.createElement('input');
            ceInp.className = "informe";
            ceInp.style.color = "white";
            ceInp.type = "text";
            ceInp.name = "titulo";
            ceInp.style.textAlign = "left";
            ceInp.value = "Ajuda para campos padrões";

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
            ceBut.addEventListener('click', function() {
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
            document.getElementsByTagName('body')[0].appendChild(ceModal);
            document.getElementsByTagName('body')[0].appendChild(ceFrm);

        } catch (e) {
            gerarMensagemErro("ATIVO", e, "Erro");
        };
    };
    //
    this.apagaChecadosSoTable = function() {
        try {
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            // Obrigatório apagar registro do ARRAY/JSON ( Se nova ordenação por colunas o registro não estara no array ) //
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            var indice; //Localiza no ARRAY/JSON o _ID na table          
            var clsChecados = self.gerarJson("n"); //Classe para buscar registros checados
            var chkds = clsChecados.gerar(); //Retorna um array associativo de todos registros checados
            var tamC = chkds.length; //Tamanho do array chkds
            for (var checados = 0; checados < tamC; checados++) {
                indice = self.buscaRowIndexArray(parseInt(chkds[checados]._ID));
                self.Js.registros.splice(indice, 1);
                break;
            };
            clsChecados = null;
            var tbl = document.getElementById(self.Js.tbl);
            tbl.apagaChecados();
        } catch (e) {
            gerarMensagemErro("catch", e.message, "Erro");
        };
    };
};