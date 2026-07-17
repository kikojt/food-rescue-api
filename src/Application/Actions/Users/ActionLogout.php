<?php

    declare(strict_types=1);

    namespace App\Application\Actions\Users;

    use App\Application\Actions\Action;
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Log\LoggerInterface;
    use \PDO;

    class ActionLogout extends Action {

        private PDO $DBH;

        public function __construct(LoggerInterface $logger, PDO $DBH) {
            parent::__construct($logger);
            $this->DBH = $DBH;
        }
        
        // Método para realizar o logout do 'cliente' autenticado
        protected function action(): Response {
            $json = $this->request->getBody()->getContents();         
            $input = json_decode($json, true);

            $queryAuth = $this->DBH->prepare("SELECT * FROM cliente WHERE token = :token");
            $queryAuth->bindParam(':token', $_SERVER['HTTP_AUTHORIZATION']);
            $queryAuth->execute();
            $client = $queryAuth->fetch(PDO::FETCH_ASSOC);

            // Se não existir, exibe uma mensagem de erro
            if(!$client) {
                $res['error'] = 1;
                $res['error_txt'] = "Não existe nenhum cliente autenticado!";
            // Se existir
            } else {
                $token = null;
                $id = $client['id'];

                // Atribui um 'token' -> nulo ao 'cliente' que está autenticado
                $queryAssignsToken = $this->DBH->prepare("UPDATE cliente SET token = :token WHERE id = :id");
                $queryAssignsToken->bindParam(':token', $token);
                $queryAssignsToken->bindParam(':id', $id);
                $queryAssignsToken->execute();
                
                $res['success'] = 1;
                $res['success_txt'] = "Foi realizado o logout com sucesso!";
            }            
            
            $payload = json_encode($res);
            $this->response->getBody()->write($payload);
    
            return $this->response->withHeader('Content-Type', 'application/json');
        }
    }

?>