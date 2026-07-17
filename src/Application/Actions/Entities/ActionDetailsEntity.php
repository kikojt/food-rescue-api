<?php

    declare(strict_types=1);

    namespace App\Application\Actions\Entities;

    use App\Application\Actions\Action;
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Log\LoggerInterface;
    use \PDO;

    class ActionDetailsEntity extends Action {

        private PDO $DBH;

        public function __construct(LoggerInterface $logger, PDO $DBH) {
            parent::__construct($logger);
            $this->DBH = $DBH;
        }
        
        // Método para obter os detalhes de uma 'entidade'
        protected function action(): Response {
            $json = $this->request->getBody()->getContents();         
            $input = json_decode($json, true);

            // Verifica se tem autorização
            if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
                // Lista todas as entidades
                $queryCheckEntities = $this->DBH->prepare("SELECT * FROM entidade");
                $queryCheckEntities->execute();
                $entities = $queryCheckEntities->fetchAll(PDO::FETCH_ASSOC);

                 // Se existirem 'entidades'
                 if($entities) {
                    $id = $this->resolveArg('id');
                    // Lista os detalhes da 'entidade' com o 'id' recebido por parâmetro
                    $queryCheckEntity = $this->DBH->prepare("SELECT * FROM entidade WHERE id = :id");
                    $queryCheckEntity->bindParam(':id', $id);
                    $queryCheckEntity->execute();
                    $entity = $queryCheckEntity->fetch(PDO::FETCH_ASSOC);
                    
                    // Se existir a 'entidade'
                    if($entity) {
                        $res['error'] = 0;
                        $res['entity'] = $entity;
                    // Se não existir a 'entidade'
                    } else {
                        $res['error'] = 1;
                        $res['error_txt'] = "Não existe nenhuma entidade com esse id!";
                    }                   
                // Se não existirem 'entidades'
                } else {
                    $res['error'] = 1;
                    $res['error_txt'] = "Não existe nenhuma entidade!";
                }
            // Se não tiver autorização
            } else {
                $res["error"] = 1;
                $res["error_txt"] = "Não tem acesso a esta página!";
            }                  
                    
            $payload = json_encode($res);
            $this->response->getBody()->write($payload);
    
            return $this->response->withHeader('Content-Type', 'application/json');
        }
    }

?>