<?php

    declare(strict_types=1);

    namespace App\Application\Actions\Entities;

    use App\Application\Actions\Action;
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Log\LoggerInterface;
    use \PDO;

    class ActionListAllOffersAvailable extends Action {

        private PDO $DBH;

        public function __construct(LoggerInterface $logger, PDO $DBH) {
            parent::__construct($logger);
            $this->DBH = $DBH;
        }
        
        // Método para obter a lista das 'entidades' 'ativas' com todas as suas 'ofertas'
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
                if ($entities) {
                    // Lista todas as 'entidades' com as suas 'ofertas' (disponíveis ou não)
                    foreach ($entities as $i => $entity) {
                        $res['entities'][$i] = $entity;
                        $res['entities'][$i]['offers'] = [];

                        $id = $entity['id'];
                        // Lista todas as 'ofertas' para a 'entidade' pelo 'entidade_id'
                        $queryCheckOffers = $this->DBH->prepare("SELECT * FROM oferta WHERE entidade_id = :entidade_id AND disponivel = 1");
                        $queryCheckOffers->bindParam(':entidade_id', $id);
                        $queryCheckOffers->execute();
                        $offers = $queryCheckOffers->fetchAll(PDO::FETCH_ASSOC);

                        // Se existirem 'ofertas' para a 'entidade'
                        if ($offers) {
                            // Verifica se a 'oferta' é do dia atual
                            foreach ($offers as $j => $offer) {
                                $dataOferta = $offer['data'];
                                $dataOferta = date("Y-m-d", strtotime($dataOferta));
                                $dataHoje = date("Y-m-d");

                                if ($dataOferta == $dataHoje) {
                                    $res['entities'][$i]['offers'][$j] = $offer;
                                }
                            }
                            // Se não existirem 'ofertas' no dia atual
                            if (empty($res['entities'][$i]['offers'])) {
                                $res['entities'][$i]['offers']['error'] = 1;
                                $res['entities'][$i]['offers']['error_txt'] = "Não existem ofertas disponíveis para hoje!";
                            }
                        } else {
                            $res['entities'][$i]['offers']['error'] = 1;
                            $res['entities'][$i]['offers']['error_txt'] = "Não existem ofertas desta entidade!";
                        }
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