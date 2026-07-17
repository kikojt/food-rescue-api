<?php

    declare(strict_types=1);

    namespace App\Application\Actions\Entities;

    use App\Application\Actions\Action;
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Log\LoggerInterface;
    use \PDO;

    class ActionListAllActiveEntities extends Action {

        private PDO $DBH;

        public function __construct(LoggerInterface $logger, PDO $DBH) {
            parent::__construct($logger);
            $this->DBH = $DBH;
        }
        
        // Método para obter a lista das 'entidades' 'ativas'
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
                    // Lista todas as 'entidades' 'ativas'
                    $queryCheckEntitiesActive = $this->DBH->prepare("SELECT * FROM entidade WHERE ativo = 1");
                    $queryCheckEntitiesActive->execute();
                    $entitiesActive = $queryCheckEntitiesActive->fetchAll(PDO::FETCH_ASSOC);

                    // Se existirem 'entidades' 'ativas'
                    if($entitiesActive) {
                        $res['error'] = 0;
                        foreach($entitiesActive as $i => $entityActive) {
                            $res['entities'][$i] = $entityActive;
                        }
                    // Se não existirem 'entidades' 'ativas'
                    } else {
                        $res['error'] = 1;
                        $res['error_txt'] = "Não existe nenhuma entidade ativa!";
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