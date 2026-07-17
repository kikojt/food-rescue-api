<?php

    declare(strict_types=1);

    namespace App\Application\Actions\Users;

    use App\Application\Actions\Action;
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Log\LoggerInterface;
    use \PDO;

    class ActionLogin extends Action {

        private PDO $DBH;

        public function __construct(LoggerInterface $logger, PDO $DBH) {
            parent::__construct($logger);
            $this->DBH = $DBH;
        }
        
        // Método para relizar o login de um 'cliente' registado
        protected function action(): Response {
            $json = $this->request->getBody()->getContents();         
            $input = json_decode($json, true);

            $email = $input['email'];
            $password = $input['password'];

            if (empty($email) || empty($password)) {
                if (empty($email)) {
                    $res['error'] = 1;
                    $res['error_txt'] = "Preencha o campo 'email'!";
                }
                if (empty($password)) {
                    $res['error'] = 1;
                    $res['error_txt'] = "Preencha o campo 'password'!";
                }
            } else {
                $res['error'] = 0;
            }

            // Verifica se existe algum 'cliente' com o 'email' inserido
            $queryCheckClient = $this->DBH->prepare("SELECT * FROM cliente WHERE email = :email");
            $queryCheckClient->bindParam(':email', $email);
            $queryCheckClient->execute();
            $client = $queryCheckClient->fetch(PDO::FETCH_ASSOC);

            // Se não existir, exibe uma mensagem de erro
            if(!$client) {
                $res['error'] = 1;
                $res['error_txt'] = "Não existe nenhum cliente com esse email!";
            // Se existir
            } else {
                // Verifica se a o 'cliente' está 'ativo'
                if($client['ativo'] == 0) {
                    $res['error'] = 1;
                    $res['error_txt'] = "De momento a conta do cliente está inativa!";
                // Se estiver 'ativo'
                } else {
                    // Verifica se a 'password' inserida é igual à 'password' do 'cliente'
                    if(password_verify($password, $client['password'])) {
                        $nome = $client['nome'];
                        $id = $client['id'];
                        $token = md5(time().$nome);

                        // Atribui um 'token' ao 'cliente'
                        $queryAssignsToken = $this->DBH->prepare("UPDATE cliente SET token = :token WHERE id = :id");
                        $queryAssignsToken->bindParam(':token', $token);
                        $queryAssignsToken->bindParam(':id', $id);
                        $queryAssignsToken->execute();
                       
                        $res['id'] = $id;
                        $res['nome'] = $nome;
                        $res['token'] = $token;
                        
                        $res['success'] = 1;
                        $res['success_txt'] = "Login efetuado com sucesso!";
                    // Se não for igual
                    } else {
                        $res['error'] = 1;
                        $res['error_txt'] = "Password incorreta!";
                    }
                }
            }
            
            $payload = json_encode($res);
            $this->response->getBody()->write($payload);
    
            return $this->response->withHeader('Content-Type', 'application/json');
        }
    }

?>