<?php

    declare(strict_types=1);

    namespace App\Application\Middleware;

    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    use Psr\Http\Server\MiddlewareInterface as Middleware;
    use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
    use Psr\Log\LoggerInterface;

    use Slim\Routing\RouteContext;

    use Slim\Psr7\Factory\ResponseFactory;

    use \PDO;

    class AuthMiddleware implements Middleware {

        private LoggerInterface $logger;
        private PDO $pdo;

        public function __construct(PDO $pdo, LoggerInterface $logger) {
        $this->pdo = $pdo;
        $this->logger = $logger;
        }

        /**
         * {@inheritdoc}
         */
        public function process(Request $request, RequestHandler $handler): Response {
            // Para lidar com o CORS Preflight
            if($request->getMethod() == 'OPTIONS') {
                $response = $handler->handle($request);
                $response = $response->withHeader('Access-Control-Allow-Origin', '*');
                $response = $response->withHeader('Access-Control-Allow-Methods', $request->getHeaderLine('Access-Control-Request-Method'));
                $response = $response->withHeader('Access-Control-Allow-Headers', $request->getHeaderLine('Access-Control-Request-Headers'));
                return $response;
            }

            //Para saber se a rota atual necessita ou não de autenticação
            $routeContext = RouteContext::fromRequest($request);
            $route = $routeContext->getRoute();
            $auth = $route->getArgument('auth');

            if ($auth === 'false') {
                // Trata-se de um endpoint público... pode continuar!
                $response = $handler->handle($request);
                return $response;
            } else {
                // Não é um endpoint público, vamos ver se tem o token
                if ($request->hasHeader('Authorization')) {
                    // Tem um token. Vamos ver se ainda é válido
                    $headers = $request->getHeader('Authorization');
                    $tokenAuth = $headers[0];    

                    // Utilizando os tokens individuais para cada utilizador
                    $sth = $this->pdo->prepare("SELECT id FROM cliente WHERE token = ?");
                    $sth->bindParam(1, $tokenAuth);
                    $sth->execute();
                    $client = $sth->fetchAll();
                    
                    if(count($client)<=0){
                        // O token já não é válido - 401
                        $responseFactory = new ResponseFactory();
                        $response = $responseFactory->createResponse(401);
                        $response->getBody()->write('Erro: Token inválido!');
                        return $response; 
                    } else {
                        // O token é válido. Vamos obter o ID do utilizador e passá-lo adiante
                        $request = $request->withAttribute('uid', $client[0]["id"]);
                        $response = $handler->handle($request);
                        return $response; 
                    }
                } else {
                    // Não tem nenhum token - 401
                    $responseFactory = new ResponseFactory();
                    $response = $responseFactory->createResponse(401);
                    $response->getBody()->write('Erro: Acesso não autorizado! Token inexistente!');
                    return $response;    
                }
            }
        }
    }

?>