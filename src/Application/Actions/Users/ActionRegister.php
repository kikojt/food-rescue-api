<?php

    declare(strict_types=1);

    namespace App\Application\Actions\Users;

    use App\Application\Actions\Action;
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Log\LoggerInterface;
    use \PDO;

    class ActionRegister extends Action {

        private PDO $DBH;

        public function __construct(LoggerInterface $logger, PDO $DBH) {
            parent::__construct($logger);
            $this->DBH = $DBH;
        }
        
        // Método para registar um novo 'cliente'
        protected function action(): Response {
            $json = $this->request->getBody()->getContents();         
            $input = json_decode($json, true);

            $email = $input['email'];
            // Verificar se o 'email' já existe
            $queryCheckClient = $this->DBH->prepare("SELECT * FROM cliente WHERE email = :email");
            $queryCheckClient->bindParam(':email', $email);
            $queryCheckClient->execute();
            $client = $queryCheckClient->fetch(PDO::FETCH_ASSOC);

            // Se existir, exibe uma mensagem de erro
            if($client) {
                $res['error'] = 1;
                $res['error_txt'] = "Já existe um cliente com esse email!";
            // Se não existir, insere na base de dados
            } else {
                $nome = $input['nome'];
                $telefone = $input['telefone'];
                $password = $input['password'];
                $passwordEncrypted = password_hash($password, PASSWORD_DEFAULT);
                $ativo = 1;

                $queryRegisterClient = $this->DBH->prepare("INSERT INTO cliente (nome, email, telefone, password, ativo) VALUES (:nome, :email, :telefone, :password, :ativo)");
                $queryRegisterClient->bindParam(':nome', $nome);
                $queryRegisterClient->bindParam(':email', $email);
                $queryRegisterClient->bindParam(':telefone', $telefone);
                $queryRegisterClient->bindParam(':password', $passwordEncrypted);
                $queryRegisterClient->bindParam(':ativo', $ativo);
                $queryRegisterClient->execute();

                $res['success'] = 1;
                $res['success_txt'] = "Cliente registado com sucesso!";
            }
            
            $payload = json_encode($res);
            $this->response->getBody()->write($payload);
    
            return $this->response->withHeader('Content-Type', 'application/json');
        }
    }

?>