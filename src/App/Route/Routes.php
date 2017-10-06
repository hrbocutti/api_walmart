<?php

namespace App\Route;
use \Psr\Http\Message\RequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * Class Routes
 * @package App\Route
 * @author Higor Bocutti
 */
class Routes
{
    /**
     * Definição das Rotas para a aplicação
     */
    public function routes()
    {
        $app = new \Slim\App();

        /**
         * Endpoint inválido ou podemos direcionar para uma página de apresentação do projeto
         */
        $app->get('/', function (Request $req,  Response $res, $args = []){
            return $res->withStatus(400)->write("Bad Request");
        });

        /**
         * Endpoint para testar conectividade com o serviço do Seller
         */
        $app->get('/ping', function (Request $request,  Response $response, $args = []){
            return $response->write("pong");
        });

        /**
         * Este serviço tem como intuito resgatar informações como preços, slas de entrega e balanço de estoque dos itens que estão diponíveis.
         */
        $app->post('/fulfillment-preview', function (Request $request,  Response $response){
            $class = "App\\MVC\\Controller\\FulfillmentPreviewController";
            $method = "index";
            $controller = new $class;
            $controller->$method($request->getParsedBody());
        });


        $app->post('/fulfillment/{controller}/{method}',function (Request $request, Response $response){
            $class = "App\\MVC\\Controller\\". ucwords($request->getAttribute('controller')."Controller");
            $method = $request->getAttribute('method');
            $controller = new $class;
            $controller->$method($request->getBody()->getContents());
        });

        $app->post('/fulfillment/{controller}/{method}/{marketplaceOrderId}',function (Request $request, Response $response){
            $class = "App\\MVC\\Controller\\". ucwords($request->getAttribute('controller')."Controller");
            $method = $request->getAttribute('method');
            $marketplaceOrderId = $request->getAttribute('marketplaceOrderId');
            $controller = new $class;
            $controller->$method($marketplaceOrderId, $request->getBody()->getContents());
        });

        /**
         * Responsavel por Startar a aplicação
         */
        $app->run();

    }
}