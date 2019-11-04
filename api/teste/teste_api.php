<?php


$app->get('/teste', function ($request) {
    return json_encode($request);
  // return $request;
});



