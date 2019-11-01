<?php


$app->get('/teste', function ($request) {
  return 'teste';
  // return $request;
});

?>

<!-- Exemplo do outro projeto -->
<!-- $app->get('/consultarProgramacao', function($request){
    $filtro = json_decode(file_get_contents('php://input'));
    $service = new ProgramacaoService();
    $retorno = $service->buscarPorFiltros($filtro);

    return json_encode($retorno);
});

$app->get('/consultarErroPorIdDetalheProgramacao', function ($request){
    $idDet = json_decode(file_get_contents('php://input'));
    $service = new ErroProgramacaoService();
    $retorno = $service->buscarPorIdDetalheProgramacao($idDet);

    return json_encode($retorno);
});

$app->get('/buscarTotalProgramacoes', function ($request){
    $service = new ProgramacaoService();
    $retorno = $service->buscarTotalProgramacoes();

    return json_encode($retorno);
}); -->