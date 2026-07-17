<?php

    declare(strict_types=1);

    namespace App\Application\Actions\Purchase;

    use App\Application\Actions\Action;
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Log\LoggerInterface;
    use \PDO;

    class ActionCancelPurchase extends Action {

        private PDO $DBH;

        public function __construct(LoggerInterface $logger, PDO $DBH) {
            parent::__construct($logger);
            $this->DBH = $DBH;
        }
        
        // Método para cancelar uma compra de um cliente
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
                    $idCliente = $client['id'];

                    // Lista as 'compras' do 'cliente' com o 'id'
                    $queryCheckPurchases = $this->DBH->prepare("SELECT * FROM compra WHERE cliente_id = :cliente_id");
                    $queryCheckPurchases->bindParam(':cliente_id', $idCliente);
                    $queryCheckPurchases->execute();
                    $purchases = $queryCheckPurchases->fetchAll(PDO::FETCH_ASSOC);

                    // Se o 'cliente' possuir 'compras'
                    if ($purchases) {
                        // Lista os detalhes de cada 'compra' incluindo a entidade e a oferta
                        foreach ($purchases as $i => $purchase) {
                            // Para saber qual é a 'oferta' através do 'id' da 'oferta'
                            $idCancelarCompra = $input['idCancelarCompra'];

                            $queryCheckOffer = $this->DBH->prepare("SELECT * FROM oferta WHERE id = :id");
                            $queryCheckOffer->bindParam(':id', $idCancelarCompra); 
                            $queryCheckOffer->execute();
                            $offer = $queryCheckOffer->fetch(PDO::FETCH_ASSOC);

                            // Verifica se a oferta é do mesmo dia
                            $dataOferta = date("Y-m-d", strtotime($offer['data']));
                            $dataAtual = date("Y-m-d");

                            if ($dataOferta == $dataAtual) {
                                // Ao cancelar a oferta fica disponível novamente
                                $queryCancelPurchase = $this->DBH->prepare("UPDATE oferta SET disponivel = 1 WHERE id = :id");
                                $queryCancelPurchase->bindParam(':id', $idCancelarCompra);
                                $queryCancelPurchase->execute();

                                // Vai remover a compra
                                $queryRemovePurchase = $this->DBH->prepare("DELETE FROM compra WHERE oferta_id = :oferta_id");
                                $queryRemovePurchase->bindParam(':oferta_id', $idCancelarCompra);
                                $queryRemovePurchase->execute();

                                $res['success'] = 1;
                                $res['success_txt'] = "Compra cancelada com sucesso!";
                            } else {
                                $res['success'] = 1;
                                $res['success_txt'] = "Não é possível cancelar a compra! Só pode cancelar no mesmo dia da compra!";
                            }
                        }
                    // Se não possuir 'compras'
                    } else {
                        $res['success'] = 1;
                        $res['success_txt'] = "Não possui nenhuma compras!";
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