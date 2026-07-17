<?php

    declare(strict_types=1);

    namespace App\Application\Actions\Purchase;

    use App\Application\Actions\Action;
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Log\LoggerInterface;
    use \PDO;

    class ActionBuyOffer extends Action {

        private PDO $DBH;

        public function __construct(LoggerInterface $logger, PDO $DBH) {
            parent::__construct($logger);
            $this->DBH = $DBH;
        }
        
        // Método para registar uma 'compra' de uma 'oferta'
        protected function action(): Response {
            $json = $this->request->getBody()->getContents();         
            $input = json_decode($json, true);

            // Verifica se tem autorização
            if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
                // Verifica se o 'token' é válido
                $token = $_SERVER['HTTP_AUTHORIZATION'];
                
                $queryCheckToken = $this->DBH->prepare("SELECT * FROM cliente WHERE token = :token AND ativo = 1");
                $queryCheckToken->bindParam(':token', $token);
                $queryCheckToken->execute();
                $client = $queryCheckToken->fetch(PDO::FETCH_ASSOC);

                // Se o 'token' for válido e o 'cliente' estiver 'ativo'
                if ($client) {
                    $ofertaId = $input['ofertaId'];
                    // Lista a 'oferta' com o 'id' recebido que esteja 'disponível'
                    $queryCheckOffer = $this->DBH->prepare("SELECT * FROM oferta WHERE id = :id AND disponivel = 1");
                    $queryCheckOffer->bindParam(':id', $ofertaId);
                    $queryCheckOffer->execute();
                    $offerAvailable = $queryCheckOffer->fetch(PDO::FETCH_ASSOC);

                    // Verifica se a 'oferta' está 'disponível'
                    if ($offerAvailable) {
                        $clienteId = $client['id'];
                            
                        // Regista a 'compra' efetuada pelo 'cliente'
                        $queryBuyOffer = $this->DBH->prepare("INSERT INTO compra (cliente_id, oferta_id) VALUES (:cliente_id, :oferta_id)");
                        $queryBuyOffer->bindParam(':cliente_id', $clienteId);
                        $queryBuyOffer->bindParam(':oferta_id', $ofertaId);
                        $queryBuyOffer->execute();

                        // Atualiza a 'oferta' para 'indisponível'
                        $queryUpdateOffer = $this->DBH->prepare("UPDATE oferta SET disponivel = 0 WHERE id = :id");
                        $queryUpdateOffer->bindParam(':id', $ofertaId);
                        $queryUpdateOffer->execute();

                        $res["success"] = 1;
                        $res["success_txt"] = "Compra registada com sucesso!";
                        
                    // Se a 'oferta' não estiver 'disponível'
                    } else {
                        $res["error"] = 1;
                        $res["error_txt"] = "A oferta não está disponível!";
                    }
                // Se o 'token' for inválido ou o 'cliente' estiver 'inativo'
                } else {
                    $res["error"] = 1;
                    $res["error_txt"] = "Token inválido ou utilizador inativo!";
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