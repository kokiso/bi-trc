<?php 

require __DIR__ . "/../vendor/autoload.php";

class App
{
    private $routes = array();

    public function get($route, $func) {
        $this->addRota($route, $func, "GET");
    }

    public function post($route, $func) {
        $this->addRota($route, $func, "POST");
    }

    public function put($route, $func) {
        //TODO pensar numa forma de fazer o match com a URL
        $this->addRota($route, $func, "PUT");
    }

    public function delete($route, $func) {
        $this->addRota($route, $func, "DELETE");
    }

    private function addRota($rota, $func, $metodo) {
        $this->routes[$metodo."_".$rota] = $func;
    }

    public function execute($request) {
        $url = $_SERVER['REQUEST_METHOD']."_/".$request['url'];
        
        if (!array_key_exists($url, $this->routes)) {
            http_response_code(404);
            throw new Exception("Rota não encontrada! Rota: $url");
        }

        header('Content-Type: application/json');
        $this->cors();
        $func = $this->routes[$url];
        $retorno = $func($request);
        echo $retorno;
    }

    private function cors() {
        if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] != '') {
            header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
            header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
            header('Access-Control-Max-Age: 1000');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');                
        }
    }
}

if (isset($_REQUEST)) {
    
    $app = new App();

    require_once __DIR__ . '/infracao/infracao_api.php';

    $app->execute($_REQUEST);

}