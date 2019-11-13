<?php
  session_start();
  
  ////////////////////////////////////////////////////
  // Tempo de espera para cada chamada da rotina 30seg
  ////////////////////////////////////////////////////  
  $ciclo = "20";
  ////////////////////////////////////////////////////
  // Classes auxiliares
  ////////////////////////////////////////////////////  
  require("classPhp/conectaSqlServer.class.php");
  require("classPhp/validaJson.class.php"); 
  require("classPhp/removeAcento.class.php"); 
  ///////////////////////////////
  // Funcao para ordenar array //
  ///////////////////////////////
  date_default_timezone_set('America/Sao_Paulo');    
  function tirarAcentos($str){
      return preg_replace('{\W}', '', preg_replace('{ +}', '_', strtr(
          utf8_decode(html_entity_decode($str)),
          utf8_decode('ÀÁÃÂÉÊÍÓÕÔÚÜÇÑàáãâéêíóõôúüçñ'),
          'AAAAEEIOOOUUCNaaaaeeiooouucn')));
  };
  
  $clsRa    = new removeAcento();    
  $classe   = new conectaBd();  
  $metodo   = 'Lista_HistoricoPosicoesPorIdPosicao';
  $opcoes   = array('location' => 'http://wsgslog.globalsearch.com.br/V20160/posicoes.asmx');
  $bolGrp   = true;
  $pasta    = "d:/xampp/htdocs/portalatlas/totalTrac/arquivos/";  

  do{
    try{         
      $continua = true;
      $classe->conecta("INTEGRAR");
      ///////////////////////////////////////////////////////////////////////////////////////
      // Preciso do grupo para pegar o codigo e multipicar por 1000(achar unidade)         //
      // Este soh preciso fazer uma vez                                                    //
      ///////////////////////////////////////////////////////////////////////////////////////
      if( $bolGrp ){
        $sql="SELECT GRP_CODIGO,GRP_APELIDO FROM GRUPO A WHERE(A.GRP_ATIVO='S')";
        $retCls=$classe->selectAssoc($sql);
        if( $retCls['retorno'] != "OK" ){  
          echo "ERRO - FALHA DE CONEXÃO COM BANCO INTEGRAR!"."\r\n";  
          $continua=false;
        } else {
          $tblGrp=$retCls["dados"];          		
          $bolGrp=false;
        };			
        
        $retCls=$classe->selectAssoc("SELECT UI_INTEGRAR,UI_QTOS,UI_CLIENTE,UI_LOGIN,UI_SENHA FROM ULTIMOID");
        if( $retCls['retorno'] != "OK" ){  
          echo "ERRO - PARAMETRO PARA INTEGRAÇÃO NAO LIDO!"."\r\n";  
          $continua=false;
        } else {
          $empCliente	= $retCls["dados"][0]["UI_CLIENTE"];
          $login      = $retCls["dados"][0]["UI_LOGIN"];
          $senha      = $retCls["dados"][0]["UI_SENHA"];
          $integrar   = $retCls["dados"][0]["UI_INTEGRAR"];
          if( strtoupper($integrar)=="N" ){
            echo "ERRO - PARAMETRO PARA INTEGRAÇÃO DESLIGADO!"."\r\n";  
            $continua=false;
          };
        };  
        unset($retCls);
        if( $continua )
          echo "OK   - TABELA DE GRUPOS ABERTA!"."\r\n";  
      };
      if( $continua ){
        //$tblGrp=$retCls["dados"];          
        ///////////////////////////////////////////////////////////////////////////////////////
        // Pegando o ultimo id integrado e vendo se o parametro eh para continuar importando //
        ///////////////////////////////////////////////////////////////////////////////////////
        $retCls=$classe->selectAssoc("SELECT UI_CODIGO FROM ULTIMOID");
        if( $retCls['retorno'] != "OK" ){  
          echo "ERRO - PARAMETRO PARA INTEGRAÇÃO NAO LIDO!"."\r\n";  
          $continua=false;
        };  
        
        if( $continua ){
          ////////////////////////////////////////////////////////////////////////////
          // Buscando o ultimo id integrado e vendo se a integracao esta habilitada //
          ////////////////////////////////////////////////////////////////////////////
          $dhIni            = date('Y/m/d H:i:s');
          $ultimoId         = $retCls["dados"][0]["UI_CODIGO"];
        };  
        
        if( $continua ){
          $idIni        = 0;
          $parametros = array ([
            'EmpCliente'        => $empCliente
            ,'Login'            => $login 
            ,'Senha'            => $senha
            ,'Id_Posicao'       => $ultimoId
            ,'ObterLocalizacao' => false
          ]);  
          try{
            $cnx = false;
            $cnx = new SoapClient('http://wsgslog.globalsearch.com.br/V20160/posicoes.asmx?wsdl');
          } catch (Exception $e){
            $continua=false;
          }
          if( $cnx === false ){
            echo "ERRO - ERRO CONEXAO SYSTEMSAT AGUARDANDO 20seg!"."\r\n";
            sleep($ciclo);            
            continue;
          };  
          $resultado  = $cnx->__soapCall($metodo,$parametros,$opcoes);
          
          unset($cnx);
          
          if( !isset($resultado->Lista_HistoricoPosicoesPorIdPosicaoResult) ) {
            echo "ERRO - SEM COMUNICACAO WEBSERVICE!"."\r\n";  
            $continua=false;
          };    
        };

        if( $continua ){        
          $arrUpdt  = [];
          /////////////////////////////////////////////////
          // Usando esta importacao para integrar com Atlas
          /////////////////////////////////////////////////
          $arrAtlas = []; 
					//
          //
          $lista = $resultado->Lista_HistoricoPosicoesPorIdPosicaoResult->Posicao;
          unset($resultado);
					
          foreach($lista as $arq){
            ////////////////
            // Id_Posicao //
            ////////////////
            $mvmPosicao      = ( isset($arq->Id_Posicao)             ? $arq->Id_Posicao              : 0 );
            if( $idIni==0 ) {
              $idIni=$mvmPosicao;
            } 
            ////////////////
            // Id_Veiculo //
            ////////////////
            $mvmCodVei       = ( isset($arq->Id_Veiculo)             ? preg_replace('/[^0-9]/', '', $arq->Id_Veiculo)              : 0 );
            if( $mvmCodVei=="" ){
              $mvmCodVei=0;
            };
            ////////////////
            //    Placa   //
            ////////////////
            $mvmPlaca = ( isset($arq->Placa)             ? $arq->Placa              : "AAA9999" );
            $mvmPlaca = str_replace("-","",$mvmPlaca);
            ///////////////////////////
            // nomeCliente -> Unidade //  
            ///////////////////////////
            $mvmNomeCliente = ( isset($arq->NomeCliente)             ? $arq->NomeCliente              : "NAOINFORMADO" );
            $mvmNomeCliente=tirarAcentos($mvmNomeCliente);
            $clsRa->montaRetorno($mvmNomeCliente);
            $mvmNomeCliente=substr($clsRa->getNome(),0,60);
            //////////////////////////////////////////
            // Grande php ( tem que ser === false ) //
            //////////////////////////////////////////  
            foreach( $tblGrp as $grp ){
              if( strpos($mvmNomeCliente,$grp["GRP_APELIDO"]) === false){
                $codgrp=1;
              } else { 
                $codgrp=$grp["GRP_CODIGO"];
                break;
              };  
            };          
            ///////////////////////////
            // Id_Cliente -> Unidade //  
            ///////////////////////////
            $mvmIdCliente    = ( isset($arq->Id_Cliente)             ? preg_replace('/[^0-9]/', '', $arq->Id_Cliente)              : 0 );
            $mvmCodUni       = ( isset($arq->Id_Cliente)             ? preg_replace('/[^0-9]/', '', $arq->Id_Cliente)              : 0 );
						
            if( $mvmCodUni=="" ){
              $mvmCodUni=0;
            } else {                        
              $mvmCodUni=( ($codgrp*1000) + $mvmCodUni ); // No ERP tb eh convertido a unidade para nunca se repetir
            };    
            ////////////////////////////    
            //      NomeMotorista     //
            ////////////////////////////
            $mvmDesMtr = ( isset($arq->NomeMotorista)          ? $arq->NomeMotorista           : "" );
            $mvmDesMtr = str_replace("'","",$mvmDesMtr);                      
            if( $mvmDesMtr=="-" ){
              $mvmDesMtr="NAO INFORMADO";
            } else {
              $mvmDesMtr=tirarAcentos($mvmDesMtr);      
              $clsRa->montaRetorno($mvmDesMtr);
              $mvmDesMtr=substr($clsRa->getNome(),0,60);
            };
            //////////////////////////////////////////////////    
            //                   equipamento                //
            //////////////////////////////////////////////////
            $mvmIdVeiculo = ( isset($arq->IdentificacaoVeiculo)       ? preg_replace('/[^0-9]/', '', $arq->IdentificacaoVeiculo) : 0 );
            //////////////////////////////////////////////////    
            // IdentificacaoMotorista e Codigo do motorista //
            //////////////////////////////////////////////////
            $mvmRfid = ( isset($arq->IdentificacaoMotorista)          ? $arq->IdentificacaoMotorista           : "-" );
            ////////////////////////////    
            //      DescricaoEvento   //
            ////////////////////////////
            $mvmDesEve       = ( isset($arq->DescricaoEvento)          ? $arq->DescricaoEvento           : "" );
            if( ($mvmDesEve=="-") or ($mvmDesEve=="")  ){
              $mvmDesEve="NAO INFORMADO";
            } else {
              $clsRa->montaRetorno($mvmDesEve);
              $mvmDesEve=substr($clsRa->getNome(),0,80);
              if(substr($mvmDesEve,0,17)=="ACELERACAO BRUSCA")                        $mvmDesEve="ACELERACAO BRUSCA"; 
              if(substr($mvmDesEve,0,19)=="ACELERACAO EXCEDIDA")                      $mvmDesEve="ACELERACAO BRUSCA"; 
              if(substr($mvmDesEve,0,23)=="ANTENA GPS DESCONECTADA")                  $mvmDesEve="ANTENA GPS DESCONECTADA"; 
              if(substr($mvmDesEve,0,8) =="BANGUELA")                                 $mvmDesEve="BANGUELA";												
              if(substr($mvmDesEve,0,29)=="BATERIA DO VEICULO DESVIOLADA")            $mvmDesEve="BATERIA DO VEICULO DESVIOLADA";
              if(substr($mvmDesEve,0,26)=="BATERIA DO VEICULO VIOLADA")               $mvmDesEve="BATERIA DO VEICULO VIOLADA";                        
              if(substr($mvmDesEve,0,36)=="BATERIA PRINCIPAL COM BAIXA VOLTAGEM")			$mvmDesEve="BATERIA PRINCIPAL COM BAIXA VOLTAGEM";
              if(substr($mvmDesEve,0,39)=="CALIBRACAO ODOMETRO AUTOMATICA COMPLETA")  $mvmDesEve="CALIBRACAO ODOMETRO AUTOMATICA COMPLETA";
              if(substr($mvmDesEve,0,16)=="CHEGADA DE DADOS")                         $mvmDesEve="CHEGADA DE DADOS";
              if(substr($mvmDesEve,0,27)=="CONECTADO NA BATERIA BACKUP")              $mvmDesEve="CONECTADO NA BATERIA BACKUP";         
              if(substr($mvmDesEve,0,28)=="CONECTADO NA FONTE PRINCIPAL")             $mvmDesEve="CONECTADO NA FONTE PRINCIPAL";
              if(substr($mvmDesEve,0,15)=="CURVA ACENTUADA")                          $mvmDesEve="CURVA ACENTUADA";
              if(substr($mvmDesEve,0,22)=="DESACELERACAO EXCEDIDA")                   $mvmDesEve="DESACELERACAO EXCEDIDA";
              if(substr($mvmDesEve,0,31)=="DESCONECTADO DA FONTE PRINCIPAL")          $mvmDesEve="DESCONECTADO DA FONTE PRINCIPAL";                        
              if(substr($mvmDesEve,0,19)=="DETECCAO DE JAMMING")                      $mvmDesEve="DETECCAO DE JAMMING";
              if(substr($mvmDesEve,0,12)=="ENTRADA 1 ON")                             $mvmDesEve="ENTRADA 1 ON";
              if(substr($mvmDesEve,0,13)=="ENTRADA 1 OFF")                            $mvmDesEve="ENTRADA 1 OFF";
              if(substr($mvmDesEve,0,23)=="ENTROU EM AREA RESTRITA")                  $mvmDesEve="ENTROU EM AREA RESTRITA";
              if(substr($mvmDesEve,0,29)=="ENTROU NO MODO SLEEP PROFUNDO")            $mvmDesEve="ENTROU NO MODO SLEEP PROFUNDO";                        
              if(substr($mvmDesEve,0,27)=="FIM DA VIDA UTIL DA BATERIA")              $mvmDesEve="FIM DA VIDA UTIL DA BATERIA";                        
              if(substr($mvmDesEve,0,13)=="FREADA BRUSCA")                            $mvmDesEve="FREADA BRUSCA";                        
              if(substr($mvmDesEve,0,33)=="FUNCIONAMENTO EM PERIODO INDEVIDO")        $mvmDesEve="FUNCIONAMENTO EM PERIODO INDEVIDO";                        												
              if(substr($mvmDesEve,0,40)=="GPS VALIDO APOS INTERVALO DE TRANSMISSAO") $mvmDesEve="GPS VALIDO APOS INTERVALO DE TRANSMISSAO";
              if(substr($mvmDesEve,0,14)=="GPRS CONECTADO")                           $mvmDesEve="GPRS CONECTADO";
              if(substr($mvmDesEve,0,17)=="IGNICAO DESLIGADA")                        $mvmDesEve="IGNICAO DESLIGADA";
              if(substr($mvmDesEve,0,14)=="IGNICAO LIGADA")                           $mvmDesEve="IGNICAO LIGADA";                        
              if(substr($mvmDesEve,0,16)=="MODULO DESLIGADO")                         $mvmDesEve="MODULO DESLIGADO";
              if(substr($mvmDesEve,0,27)=="MODULO ENTROU EM MODO SLEEP")              $mvmDesEve="MODULO ENTROU EM MODO SLEEP";
              if(substr($mvmDesEve,0,13)=="MODULO LIGADO")                         		$mvmDesEve="MODULO LIGADO";												
              if(substr($mvmDesEve,0,25)=="MODULO SAIU DO MODO SLEEP")                $mvmDesEve="MODULO SAIU DO MODO SLEEP";
              if(substr($mvmDesEve,0,36)=="NORMAL VEICULO PARADO C/ LIG IGNICAO")     $mvmDesEve="NORMAL VEICULO PARADO C/ LIG IGNICAO";
              if(substr($mvmDesEve,0,27)=="PARADO COM A IGNICAO LIGADA")              $mvmDesEve="PARADO COM A IGNICAO LIGADA";
              if(substr($mvmDesEve,0,11)=="PONTO MORTO")                              $mvmDesEve="PONTO MORTO";
              if(substr($mvmDesEve,0,19)=="POSICAO TEMPORIZADA")                      $mvmDesEve="POSICAO TEMPORIZADA";
              if(substr($mvmDesEve,0,15)=="RECEPCAO DE SMS")                          $mvmDesEve="RECEPCAO DE SMS";
              if(substr($mvmDesEve,0,20)=="RECONEXAO ANTENA GPS")                     $mvmDesEve="RECONEXAO ANTENA GPS";												
              if(substr($mvmDesEve,0,31)=="RETORNO AO LIMITE DE VELOCIDADE")          $mvmDesEve="RETORNO AO LIMITE DE VELOCIDADE";                        
              if(substr($mvmDesEve,0,8)=="RPM ALTO")                                  $mvmDesEve="RPM ALTO";                        
              if(substr($mvmDesEve,0,21)=="SAIU DA AREA RESTRITA")                    $mvmDesEve="SAIU DA AREA RESTRITA";
              if(substr($mvmDesEve,0,27)=="SAIU DO MODO SLEEP PROFUNDO")              $mvmDesEve="SAIU DO MODO SLEEP PROFUNDO";
              if(substr($mvmDesEve,0,17)=="SENSOR DE FORCA G")                        $mvmDesEve="SENSOR DE FORCA G";
              if(substr($mvmDesEve,0,25)=="SENSOR DE JAMMING ATIVADO")                $mvmDesEve="SENSOR DE JAMMING ATIVADO";
              if(substr($mvmDesEve,0,28)=="SENSOR DE JAMMING DESATIVADO")             $mvmDesEve="SENSOR DE JAMMING DESATIVADO";
              if(substr($mvmDesEve,0,31)=="TRAVA DE ESTACIONAMENTO VIOLADA")          $mvmDesEve="TRAVA DE ESTACIONAMENTO VIOLADA";												
              if(substr($mvmDesEve,0,26)=="VELOCIDADE NORMAL ATINGIDA")               $mvmDesEve="VELOCIDADE NORMAL ATINGIDA"; 
              if(substr($mvmDesEve,0,29)=="VEICULO PARADO C/ LIG IGNICAO")            $mvmDesEve="VEICULO PARADO C/ LIG IGNICAO";                        
              if(substr($mvmDesEve,0,61)=="VEICULO LIGADO MAS NAO PODE DIRIGIR DURANTE TEMPO PREDEFINIDO") 
                $mvmDesEve="VEICULO LIGADO MAS NAO PODE DIRIGIR DURANTE TEMPO PREDEFINIDO";
            };
            ////////////////////////////
            // Id_Evento da sistemSat //
            ////////////////////////////
            $mvmIdEvento       = ( isset($arq->Id_Evento) ? $arq->Id_Evento : 0   );
            $mvmCodEveSS       = ( isset($arq->Id_Evento) ? $arq->Id_Evento : '1' );
            if( $mvmCodEveSS=="" ){
              $mvmCodEveSS='1';
            };
            //
            //
            $mvmNumeroSerie  = ( isset($arq->NumeroSerie)            ? trim($arq->NumeroSerie)       : "NAO INFORMADO" );
            if( $mvmNumeroSerie=="" ){
              $mvmNumeroSerie="NAO INFORMADO";
            };    
            //
            $mvmLatitude     = ( isset($arq->Latitude)               ? preg_replace('/[^0-9]-./', '', $arq->Latitude)                : 0 );    
            if( $mvmLatitude=="" ){
              $mvmLatitude=0;
            };
            $mvmLatitude=number_format($mvmLatitude, 8, '.', '');                 
            //
            $mvmLongitude    = ( isset($arq->Longitude)              ? preg_replace('/[^0-9]-./', '', $arq->Longitude)               : 0 );
            if( $mvmLongitude=="" ){
              $mvmLongitude=0;
            };
            $mvmLongitude=number_format($mvmLongitude, 8, '.', '');                 
            ////////////////
            // Velocidade //
            ////////////////
            $mvmVelocidade   = ( isset($arq->Velocidade)             ? preg_replace('/[^0-9,-.]/', '', $arq->Velocidade)              : 0 );
            if( $mvmVelocidade=="" ){
              $mvmVelocidade=0;
            } else {
              $mvmVelocidade   = intval($mvmVelocidade);
            };
            ////////////////
            // RPM        //
            ////////////////
            $mvmRpm   = ( isset($arq->RPM)             ? preg_replace('/[^0-9,-.]/', '', $arq->RPM)              : 0 );
            if( $mvmRpm=="" ){
              $mvmRpm=0;
            } else {
              $mvmRpm   = intval($mvmRpm);
            };
            //
            $mvmOdometro     = ( isset($arq->Odometro)               ? preg_replace('/[^0-9,.]/', '', $arq->Odometro)                : 0 );
            if( $mvmOdometro=="" ){
              $mvmOdometro=0;
            } else {
              $mvmOdometro=number_format($mvmOdometro, 4, '.', '');
            };  
            //
            //
            $mvmIgnicao=( $mvmVelocidade>3 ? 1 : 0 );
            //
            $mvmTemperatura  = ( isset($arq->Temperatura)            ? preg_replace('/[^0-9]/', '', $arq->Temperatura)             : 0 );
            if( $mvmTemperatura=="" ){
              $mvmTemperatura=0;
            };  
            $mvmDataGps      = $arq->DataGPS;
            $mvmDataServidor = $arq->DataServidor;
						
            $hojeGps         = substr($mvmDataGps,0,10);
            $mvmAnoMes       = preg_replace('/[^0-9]/', '',substr($hojeGps,0,7) );
            $mvmHoraGps      = substr($mvmDataGps,11,5);
            $splt            = explode(":",$mvmHoraGps);
            $mvmHoraGps      = (($splt[0]*60)+$splt[1]);
            //
            $mvmHorimetro    = ( isset($arq->Horimetro)              ? preg_replace('/[^0-9]/', '', $arq->Horimetro)               : 0 );
            if( $mvmHorimetro=="" ){
              $mvmHorimetro=0;
            };
            //////////////////
            // Localizacao  //
            //////////////////
            $mvmLocalizacao  = ( isset($arq->Localizacao)            ? trim($arq->Localizacao)       : "NAO INFORMADO" );
            if( $mvmLocalizacao=="" ){
              $mvmLocalizacao="NAO INFORMADO";
            } else {
              $clsRa->montaRetorno($mvmLocalizacao);
              $mvmLocalizacao=substr($clsRa->getNome(),0,100);
              $mvmLocalizacao = str_replace("DE:","DE",$mvmLocalizacao);
              $mvmLocalizacao = str_replace(" - "," ",$mvmLocalizacao);
            }; 
            if( strlen($mvmPlaca) == 7 ){
              $sql="INSERT INTO VTMPMOVIMENTO("
                ."TMVM_POSICAO"
                .",TMVM_CODVEI"
                .",TMVM_PLACA"
                .",TMVM_CODUNI"
                .",TMVM_RFID"
                .",TMVM_DESMTR"
                .",TMVM_CODEVESS"
                .",TMVM_DESEVE"
                .",TMVM_NUMEROSERIE"
                .",TMVM_LATITUDE"
                .",TMVM_LONGITUDE"
                .",TMVM_VELOCIDADE"
                .",TMVM_ODOMETRO"
                .",TMVM_IGNICAO"
                .",TMVM_TEMPERATURA"
                .",TMVM_LOCALIZACAO"
                .",TMVM_DATAGPS"
                .",TMVM_HORAGPS"
                .",TMVM_RPM"
                .",TMVM_HORIMETRO"
                .",TMVM_ANOMES) VALUES("
                ."'$mvmPosicao'"            //TMVM_POSICAO
                .",".$mvmCodVei             //TMVM_CODVEI
                .",'".$mvmPlaca."'"         //TMVM_PLACA
                .",".$mvmCodUni             //TMVM_CODUNI
                .",'".$mvmRfid."'"          //TMVM_RFID
                .",'".$mvmDesMtr."'"        //TMVM_DESMTR
                .",'".$mvmCodEveSS."'"      //TMVM_CODEVESS
                .",'".$mvmDesEve."'"        //TMVM_DESEVE
                .",'".$mvmNumeroSerie."'"   //TMVM_NUMEROSERIE
                .",".$mvmLatitude           //TMVM_LATITUDE
                .",".$mvmLongitude          //TMVM_LONGITUDE
                .",".$mvmVelocidade         //TMVM_VELOCIDADE
                .",".$mvmOdometro           //TMVM_ODOMETRO
                .",".$mvmIgnicao            //TMVM_IGNICAO
                .",".$mvmTemperatura        //TMVM_TEMPERATURA
                .",'".$mvmLocalizacao."'"   //TMVM_LOCALIZACAO
                .",'".$mvmDataGps."'"       //TMVM_DATAGPS
                .",".$mvmHoraGps            //TMVM_HORAGPS
                .",".$mvmRpm                //TMVM_RPM
                .",".$mvmHorimetro          //TMVM_HORIMETRO
                .",".$mvmAnoMes             //TMVM_ANOMES
              .")";   
              array_push($arrUpdt,$sql);
              ///////////////////////////////////////////////////////
              // Array para integrar com Atlas somente posicionamento
              ///////////////////////////////////////////////////////
              if( ($mvmIdCliente==867) or ($mvmIdCliente==428) or ($mvmIdCliente==445) or ($mvmIdCliente==203) ){
                array_push( $arrAtlas,[
                  "idpacote"      => $mvmPosicao
                  ,"tecnologia"   => "SS"
									,"placa"        => $mvmPlaca
                  ,"idveiculo"    => $mvmIdVeiculo
                  ,"datagps"      => $mvmDataGps
                  ,"dataservidor" => $mvmDataServidor
                  ,"latitude"     => $mvmLatitude
                  ,"longitude"    => $mvmLongitude
                  ,"velocidade"   => $mvmVelocidade
                  ,"hodometro"    => $mvmOdometro
                  ,"horimetro"    => $mvmHorimetro
                  ,"ignicao"      => $mvmIgnicao
                  ,"localizacao"  => $mvmLocalizacao
                  ,"evento"       => $mvmIdEvento
                ]);
              };      
            };
          };
          if( count($arrUpdt) == 0 ){
            echo "ERRO - NENHUM REGISTRO NO ARRAY PARA ENVIO AO BANCO DE DADOS!"."\r\n";
          } else { 
            if( count($arrAtlas)>0 ){
              file_put_contents($pasta."arq".str_pad($mvmPosicao, 12, "0", STR_PAD_LEFT).".json",json_encode($arrAtlas));  
            };  
            ///////////////////////////////////////////////////////////////////////////////
            // Guardando o id inicial/final e o tempo que demorou a leitura e importacao //
            ///////////////////////////////////////////////////////////////////////////////
            $dhFim = date('Y/m/d H:i:s');
            ///////////////////////////////////////////////////////////////////////////////
            //                    Atualizando o ultimo id importado                      //
            ///////////////////////////////////////////////////////////////////////////////
            if( $ultimoId>0 ){
              $sql="UPDATE ULTIMOID SET UI_CODIGO=".$mvmPosicao;
              array_push($arrUpdt,$sql);
            }; 
            //echo "OK   - ENVIANDO ".count($arrUpdt)." REGISTROS PARA BANCO DE DADOS!"."\r\n";                          
						echo "OK   - ENVIANDO ".count($arrUpdt)." REGISTROS {$idIni} a {$mvmPosicao}!"."\r\n";                          
            $retCls=$classe->cmd($arrUpdt);
            if( $retCls['retorno']=="OK" ){
              echo "OK   - GRAVOU ".count($arrUpdt)." REGISTROS! ".date("d/m/Y H:i:s",strtotime($mvmDataGps))."\r\n";              
            } else {
              echo "ERRO - ".$retCls["erro"]."\r\n";                
            } 
          };    
        };
      };  
      echo "AGUARDANDO PROXIMO CICLO {$ciclo}segs"."\r\n";
      sleep($ciclo);
    } catch(Exception $e ){
      echo "ERRO - ".$e->getMessage()." Aguardando proximo ciclo {$ciclo}segs"."\r\n";
      sleep($ciclo);
      continue;
    };    
  } while (true);
?>