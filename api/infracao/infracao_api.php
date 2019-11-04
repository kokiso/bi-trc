<?php

include_once __DIR__ . "/../../classPhp/service/serviceTempoInfracao.php";

$app->get('/consolidaInfracao', function ($request) {
  $servicoTempoInfracao = new serviceTempoInfracao();
  $servicoTempoInfracao->consolidaTempoInfracao('INTEGRAR');
  return json_encode($request);
});

?>