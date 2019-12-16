<?php
    if(!isset($_SESSION)) { 
        session_start(); 
    }
    
    require_once(__DIR__ . "/../conectaSqlServer.class.php");
    require_once(__DIR__ . "/../validaJson.class.php");
    require_once(__DIR__ . "/../removeAcento.class.php");
    require_once(__DIR__ . "/../selectRepetidoTrac.class.php");
    require_once(__DIR__ . "/../persistencia/grupoOperacionalPersistencia.php");
    require_once(__DIR__ . "/../exportarExcel.class.php");

    class serviceGrupoOperacional{

        // Busca uma lista de movimentos para serem consolidados, trata os registros e insere no banco
        function consolidaTempoInfracao($login) {
            try{     
                function diferenca($parI,$parF){
                    $dtI      = new DateTime($parI); 
                    $dtF      = new DateTime($parF);
                    $dteDiff  = $dtI->diff($dtF); 
                    return $dteDiff->format("%H:%I:%S"); 
                };
                
            
                $persistencia     = new infracaoTempoPersistencia();
                $persistencia->atualizaConfiguracaoConsolidacao($login);
        
                $vldr     = new validaJSon();
        
                $retorno  = "";
                $atuBd    = false;
                $arrRet  = [];
                $linR    = -1;  //Linha do array de retorno
                $placaOld        = "***9999";
                $placaAtu        = "***9999";
                $params   = array();
                $options  = array("Scrollable" => SQLSRV_CURSOR_FORWARD);

                $persistencia     = new infracaoTempoPersistencia();
                $consulta = $persistencia->buscaInfracaoTempo($login);
                $normalizou  = false;
                while ($linha = sqlsrv_fetch_array($consulta, SQLSRV_FETCH_ASSOC)) {
                    $placaAtu=$linha["MVM_PLACA"];
    
                    if( ($linha["EVE_CODEG"]=="EV") or ($linha["EVE_CODEG"]=="EVC") ){
                        $descalibrado="NAO";
                        if( ($linha["VCL_FROTA"]=="L") and ($linha["EVE_CODEG"]=="EV") and ($linha["MVM_VELOCIDADE"]<=110) )
                            $descalibrado="SIM";  
                        if( ($linha["VCL_FROTA"]=="L") and ($linha["EVE_CODEG"]=="EVC") and ($linha["MVM_VELOCIDADE"]<=90) )
                            $descalibrado="SIM";  
                        if( ($linha["VCL_FROTA"]=="P") and ($linha["EVE_CODEG"]=="EV") and ($linha["MVM_VELOCIDADE"]<=80) )
                            $descalibrado="SIM";  
                        if( ($linha["VCL_FROTA"]=="P") and ($linha["EVE_CODEG"]=="EVC") and ($linha["MVM_VELOCIDADE"]<=60) )
                            $descalibrado="SIM";  
                            
                        if( $placaAtu<>$placaOld  ){
                            $linR++;
                            array_push($arrRet,[
                                "PLACA"               =>  $linha["MVM_PLACA"]
                                ,"LP"                 =>  $linha["VCL_FROTA"]
                                ,"TURNO"              =>  $linha["MVM_TURNO"]
                                ,"IDINI"              =>  $linha["MVM_POSICAO"]
                                ,"DTINI"              =>  $linha["MVM_DATAGPS"]
                                ,"IDFIM"              =>  ""
                                ,"DTFIM"              =>  $linha["MVM_DATAGPS"]
                                ,"TEMPO"              =>  ""
                                ,"VELOC"              =>  $linha["MVM_VELOCIDADE"]
                                ,"CODEG"              =>  $linha["EVE_CODEG"]
                                ,"CODIGO_EVENTO"      =>  $linha["MVM_CODEVE"]
                                ,"MAXIMAVELOC"        =>  $linha["MVM_VELOCIDADE"]
                                ,"CODIGO_MOTORISTA"   =>  $linha["MVM_CODMTR"]
                                ,"MOTORISTA"          =>  $linha["MTR_NOME"]
                                ,"CODIGO_UNIDADE"     =>  $linha["UNI_CODIGO"]
                                ,"DESCALIBRADO"       =>  $descalibrado
                                ,"RFID"    			  =>  $linha["MTR_RFID"]
                                ,"ODOMINI"            =>  $linha["MVM_ODOMETRO"]
                                ,"ODOMFIM"            =>  $linha["MVM_ODOMETRO"]
                                ,"DISTPERC"           =>  ""
                                ,"ANO_MES"            =>  $linha["MVM_ANOMES"]
                                ,"ERRO"               =>  0
                            ]);
                            $placaOld=$placaAtu;
                             $normalizou  = true;									
                        } else {
                            $arrRet[$linR]["DTFIM"]=$linha["MVM_DATAGPS"];  
                            $arrRet[$linR]["ODOMFIM"]=$linha["MVM_ODOMETRO"];  
                            $arrRet[$linR]["TEMPO"]=diferenca($arrRet[$linR]["DTINI"],$arrRet[$linR]["DTFIM"]);
                            $arrRet[$linR]["DISTPERC"]=number_format(($arrRet[$linR]["ODOMFIM"]-$arrRet[$linR]["ODOMINI"])*1000, 2, '.', '');
                            if( $linha["MVM_VELOCIDADE"]>$arrRet[$linR]["MAXIMAVELOC"] )
                            $arrRet[$linR]["MAXIMAVELOC"]=$linha["MVM_VELOCIDADE"];
                        };
                    };
            
                    if( $linha["EVE_CODEG"]=="VN" ){
                        if($normalizou){
                            $arrRet[$linR]["IDFIM"]=$linha["MVM_POSICAO"];  
                            $arrRet[$linR]["DTFIM"]=$linha["MVM_DATAGPS"];
                            $arrRet[$linR]["ODOMFIM"]=$linha["MVM_ODOMETRO"];  
                            $arrRet[$linR]["TEMPO"]=diferenca($arrRet[$linR]["DTINI"],$arrRet[$linR]["DTFIM"]);
                            $arrRet[$linR]["DISTPERC"]=number_format(($arrRet[$linR]["ODOMFIM"]-$arrRet[$linR]["ODOMINI"])*1000, 2, '.', '');
                            if($arrRet[$linR]["DISTPERC"] < 0) {
                                $arrRet[$linR]["ERRO"]=1;
                            }
                            $placaOld="***9999"; 
                            $normalizou=false;									
                        };									
                    } else {
                        if( $placaAtu==$placaOld  ){
                            if( $linha["MVM_VELOCIDADE"]>$arrRet[$linR]["MAXIMAVELOC"] )
                            $arrRet[$linR]["MAXIMAVELOC"]=$linha["MVM_VELOCIDADE"];
                        };  
                    };
                }; 
                //////////////////////////////////
                // Retornando para o JavaScript //
                //////////////////////////////////  
                $arrJs=[];
                $qtos    = count($arrRet);
                $lin=0;
                
                while($lin<$qtos){
                    $gravar=true;
                                
                    if( $gravar ){
                        //////////////////////////////////////////////////////////////////
                        // Se o tempo for maior que 30min considerar erro-01ago2018(Pedro)
                        //////////////////////////////////////////////////////////////////
                        $idfim=$arrRet[$lin]["IDFIM"];
                        $splTempo=explode(":",$arrRet[$lin]["TEMPO"]);
                        if(sizeof($splTempo) > 2) {
                            if( ((int)$splTempo[0]>0) or ((int)$splTempo[1]>30) ){
                                $arrRet[$lin]["ERRO"]=1;
                            };
                        };
                            
                        $query = "";
                        $query .= " select MAX(MVM_VELOCIDADE) as MVM_VELOCIDADE from MOVIMENTO ";
                        $query .= " where MVM_DATAGPS between '".$arrRet[$lin]["DTINI"]."' ";
                        $query .= " and '".$arrRet[$lin]["DTFIM"]."' and MVM_PLACA = '".$arrRet[$lin]["PLACA"]."' ";
        
                        $consulta = $persistencia->buscaVelocidadeMaxima($login, $arrRet[$lin]);
                        while ($veloc_maxima = sqlsrv_fetch_array($consulta, SQLSRV_FETCH_ASSOC)) {
                            if($veloc_maxima["MVM_VELOCIDADE"] != null) {
                                $arrRet[$lin]["MAXIMAVELOC"] = $veloc_maxima["MVM_VELOCIDADE"];
                            }
                        }
                            
                        array_push($arrJs,[
                            $arrRet[$lin]["PLACA"]             //0
                            ,$arrRet[$lin]["LP"]               //1
                            ,$arrRet[$lin]["TURNO"]            //2
                            ,$arrRet[$lin]["IDINI"]            //3
                            ,$arrRet[$lin]["DTINI"]            //4
                            ,$idfim                            //5
                            ,$arrRet[$lin]["DTFIM"]            //6
                            ,$arrRet[$lin]["TEMPO"]            //7
                            ,$arrRet[$lin]["VELOC"]            //8
                            ,$arrRet[$lin]["MAXIMAVELOC"]      //9
                            ,$arrRet[$lin]["MOTORISTA"]        //10
                            ,$arrRet[$lin]["DESCALIBRADO"]     //11
                            ,$arrRet[$lin]["CODEG"]            //12
                            ,$arrRet[$lin]["RFID"]             //13
                            ,$arrRet[$lin]["DISTPERC"]         //14
                            ,$arrRet[$lin]["CODIGO_MOTORISTA"] //15
                            ,$arrRet[$lin]["CODIGO_UNIDADE"]   //16
                            ,$arrRet[$lin]["CODIGO_EVENTO"]    //17
                            ,$arrRet[$lin]["ANO_MES"]          //18
                            ,$arrRet[$lin]["ERRO"]             //19
                        ]);
                    }  
                    $lin++;
                };
                foreach ($arrJs as $value) {
                    $persistencia->insereInfracao($login, $value);
                }
                $persistencia->insereConsolidacaoInfracao($login, count($arrJs));
            } catch(Exception $e ){
                $retorno='[{"retorno":"ERR","dados":"","erro":"'.$e.'"}]';
            };
            exit;
        }

        // Busca uma lista de dados consolidados
        function buscaDadosConsolidados($login, $json) {
            try{
                function diferenca($parI,$parF){
                    $dtI      = new DateTime($parI); 
                    $dtF      = new DateTime($parF);
                    $dteDiff  = $dtI->diff($dtF); 
                    return $dteDiff->format("%H:%I:%S"); 
                };  
        
                $vldr     = new validaJSon();
        
                $retorno  = "";
                $retCls   = $vldr->validarJs($json);
                $atuBd    = false;
                $arrJs=[];
                if($retCls["retorno"] != "OK"){
                    $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';
                    unset($retCls,$vldr);      
                } else {
                    $arrRet  = []; 
                    $jsonObj  = $retCls["dados"];
                    $lote     = $jsonObj->lote;
        
                    if( $retCls['retorno'] != "OK" ){
                        $retorno='[{"retorno":"ERR","dados":"","erro":"'.$retCls['erro'].'"}]';  
                    } else {
                        $params   = array();
                        $options  = array("Scrollable" => SQLSRV_CURSOR_FORWARD);
            
                        $persistencia     = new infracaoTempoPersistencia();
                        $consulta = $persistencia->buscaInfracaoTempoConsolidacao($login, $lote);
                        $normalizou  = false;
                        while ($linha = sqlsrv_fetch_array($consulta, SQLSRV_FETCH_ASSOC)) {
                            if($linha["ERRO"] == 1) {
                                $linha["POSICAO_FINAL"] = "**erro**";
                            }
                            array_push($arrJs,[
                                 $linha["PLACA"]
                                ,$linha["FROTA"]
                                ,$linha["TURNO"]
                                ,$linha["POSICAO_INICIAL"]
                                ,$linha["DATA_INICIAL"]
                                ,$linha["POSICAO_FINAL"]
                                ,$linha["DATA_FINAL"]
                                ,$linha["TEMPO"]
                                ,$linha["VELOCIDADE"]
                                ,$linha["VELOCIDADE_MAX"]
                                ,$linha["MTR_NOME"]
                                ,$linha["DESCALIBRADO"]
                                ,$linha["EVE_CODEG"]
                                ,$linha["RFID"]
                                ,number_format($linha["DISTANCIA_PERCORRIDA"], 2, '.', '')
                              ]);
                        }; 
                    };
                    $titulos = [
                        "PLACA",
                        "LP",
                        "T",
                        "IDINI",
                        "DTINI",
                        "IDFIM",
                        "DTFIM",
                        "TEMPO",
                        "VEL",
                        "MAX",
                        "MOTORISTA",
                        "DES",
                        "EVE",
                        "RFID",
                        "DISTPERC"
                    ];
                    $exportar = new exportarExcel();
                    $nomeArquivo = 'BI Infração/Tempo em '. date("M/y");
                    $data = $exportar->exportar($nomeArquivo, $arrJs, $titulos);
                    $retorno='[{"retorno":"OK","dados":'.json_encode($arrJs).',"erro":"", "data":"'.$data.'"}]';
                };
            } catch(Exception $e ){
                $retorno='[{"retorno":"ERR","dados":"","erro":"'.$e.'"}]';
            };
            echo $retorno;
            exit;
        }
    }
?>