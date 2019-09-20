<?php
  /*
  * dados da conexão 
  * EmpCliente        - 138481
  * Login             - wsrelieve
  * Senha             - wsrelieve
  * Id_Posicao        - 585438744
  * ObterLocalizacao  - false
  */
  
  $EmpCliente         = 138481;
  $Login              = 'wsrelieve';
  $Senha              = 'wsrelieve';
  $Id_Posicao         = 873030342;
  $ObterLocalizacao   = false;
  
  $metodo             = 'Lista_HistoricoPosicoesPorIdPosicao';
  $opcoes             = array('location' => 'http://wsgslog.globalsearch.com.br/V20160/posicoes.asmx');
  $parametros         = array ([
                                'EmpCliente'        => $EmpCliente
                                ,'Login'            => $Login 
                                ,'Senha'            => $Senha 
                                ,'Id_Posicao'       => $Id_Posicao
                                ,'ObterLocalizacao' => $ObterLocalizacao
                              ]);  
  $cnx                = new SoapClient('http://wsgslog.globalsearch.com.br/V20160/posicoes.asmx?wsdl');
      
  try {
    $resultado        = $cnx->__soapCall($metodo,$parametros,$opcoes);

    if (isset($resultado->Lista_HistoricoPosicoesPorIdPosicaoResult->Posicao)) {
      $lista            = $resultado->Lista_HistoricoPosicoesPorIdPosicaoResult->Posicao;
      echo "<table border=1>";
      echo "<tr>";
      echo "<th>Seq</th>";
      echo "<th>Id_Posicao</th>";
      echo "<th>Id_PosicaoIntegracao</th>";
      echo "<th>Id_Veiculo</th>";
      echo "<th>Id_Cliente</th>";
      echo "<th>IdentificacaoMotorista</th>";
      echo "<th>NomeMotorista</th>";
      echo "<th>Anotacao</th>";
      echo "<th>isAlerta</th>";
      echo "<th>idFornecedorLocalizacao</th>";
      echo "<th>DescricaoVeiculo</th>";
      echo "<th>IdentificacaoVeiculo</th>";
      echo "<th>IdentificacaoIntegracao</th>";
      echo "<th>Placa</th>";
      echo "<th>NomeCliente</th>";
      echo "<th>Id_Evento</th>";
      echo "<th>DescricaoEvento</th>";
      echo "<th>NumeroSerie</th>";
      echo "<th>Latitude</th>";
      echo "<th>Longitude</th>";
      echo "<th>Direcao</th>";
      echo "<th>Velocidade</th>";
      echo "<th>RPM</th>";
      echo "<th>Odometro</th>";
      echo "<th>Ignicao</th>";
      echo "<th>Temperatura</th>";
      echo "<th>TemperaturaSensor1</th>";
      echo "<th>TemperaturaSensor2</th>";
      echo "<th>TemperaturaSensor3</th>";
      echo "<th>UnidadeTemperatura</th>";
      echo "<th>Altitude</th>";
      echo "<th>NivelBateria</th>";
      echo "<th>NivelBateriaPrincipal</th>";
      echo "<th>NivelSinal</th>";
      echo "<th>NumeroSatelite</th>";
      echo "<th>StatusEntradas</th>";
      echo "<th>StatusSaidas</th>";
      echo "<th>DataGPS</th>";
      echo "<th>DataServidor</th>";
      echo "<th>Localizacao</th>";
      echo "<th>Horimetro</th>";
      echo "<th></th>";
      echo "</tr>";
      $seq = 1;
      foreach($lista as $posicao){
        echo "<tr>";
        echo "<td>".$seq++."</td>";
        echo "<td>".$posicao->Id_Posicao."</td>";
        echo "<td>".$posicao->Id_PosicaoIntegracao."</td>";
        echo "<td>".$posicao->Id_Veiculo."</td>";
        echo "<td>".$posicao->Id_Cliente."</td>";
        echo "<td>".$posicao->IdentificacaoMotorista."</td>";
        echo "<td>".$posicao->NomeMotorista."</td>";
        echo "<td>".$posicao->Anotacao."</td>";
        echo "<td>".$posicao->isAlerta."</td>";
        echo "<td>".$posicao->IdFornecedorLocalizacao."</td>";
        echo "<td>".$posicao->DescricaoVeiculo."</td>";
        echo "<td>".$posicao->IdentificacaoVeiculo."</td>";
        echo "<td>".$posicao->IdentificacaoIntegracao."</td>";
        echo "<td>".(isset($posicao->Placa) ? $posicao->Placa : 'XXXX' )."</td>";
        echo "<td>".$posicao->NomeCliente."</td>";
        echo "<td>".$posicao->Id_Evento."</td>";
        echo "<td>".$posicao->DescricaoEvento."</td>";
        echo "<td>".$posicao->NumeroSerie."</td>";
        echo "<td>".$posicao->Latitude."</td>";
        echo "<td>".$posicao->Longitude."</td>";
        echo "<td>".$posicao->Direcao."</td>";
        echo "<td>".$posicao->Velocidade."</td>";
        echo "<td>".$posicao->RPM."</td>";
        echo "<td>".$posicao->Odometro."</td>";
        echo "<td>".$posicao->Ignicao."</td>";
        echo "<td>".$posicao->Temperatura."</td>";
        echo "<td>".$posicao->TemperaturaSensor1."</td>";
        echo "<td>".$posicao->TemperaturaSensor2."</td>";
        echo "<td>".$posicao->TemperaturaSensor3."</td>";
        echo "<td>".$posicao->UnidadeTemperatura."</td>";
        echo "<td>".$posicao->Altitude."</td>";
        echo "<td>".$posicao->NivelBateria."</td>";
        echo "<td>".$posicao->NivelBateriaPrincipal."</td>";
        echo "<td>".$posicao->NivelSinal."</td>";
        echo "<td>".$posicao->NumeroSatelite."</td>";
        echo "<td>".$posicao->StatusEntradas."</td>";
        echo "<td>".$posicao->StatusSaidas."</td>";
        echo "<td>".$posicao->DataGPS."</td>";
        echo "<td>".$posicao->DataServidor."</td>";
        echo "<td>".$posicao->Localizacao."</td>";
        echo "<td>".$posicao->Horimetro."</td>";
        echo "</tr>";
      //}
      }
      echo "</table>";
    } else 
      throw new Exception('Nao foram retornados posicoes com estes parametros.');
  } catch (Exception $e) {
    echo $e->getMessage();
  }
?>  
  
  
  
  