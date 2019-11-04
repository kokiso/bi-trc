  <?php

//require_once "../../classPhp/service/serviceTempoInfracao.php";

$app->get('/consolidaInfracao', function ($request) {
//  $servicoTempoInfracao = new serviceTempoInfracao();
    return 'hausdhud';
  $servicoTempoInfracao->consolidaTempoInfracao('INTEGRAR');
});

?>