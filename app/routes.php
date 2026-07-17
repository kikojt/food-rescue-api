<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

use App\Application\Actions\Users\ActionRegister;
use App\Application\Actions\Users\ActionLogin;
use App\Application\Actions\Users\ActionLogout;

use App\Application\Actions\Entities\ActionListAllActiveEntities;
use App\Application\Actions\Entities\ActionListActiveOffers;
use App\Application\Actions\Entities\ActionDetailsEntity;

use App\Application\Actions\Entities\ActionListOffersAvailable;
use App\Application\Actions\Entities\ActionListAllOffersAvailable;

use App\Application\Actions\Purchase\ActionBuyOffer;
use App\Application\Actions\Purchase\ActionListPurchase;
use App\Application\Actions\Purchase\ActionCancelPurchase;

return function (App $app) {

    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        return $response;
    });
    
    // Rotas de clientes (Registar, Login e Logout)
    $app->POST('/api/users/register/', ActionRegister::class)->setArgument('auth', 'false');
    $app->PATCH('/api/users/login/', ActionLogin::class)->setArgument('auth', 'false');
    $app->PATCH('/api/users/logout.php', ActionLogout::class)->setArgument('auth', 'true');

    // Rotas de entidades
    // Rota para listar todas as entidades ativas
    $app->GET('/api/entities/', ActionListAllActiveEntities::class)->setArgument('auth', 'true');
    // Rota para listar todas as entidades ativas e as suas ofertas
    $app->GET('/api/entities/withoffers/', ActionListActiveOffers::class)->setArgument('auth', 'true');
    // Rota para listar todas as ofertas disponíveis
    $app->GET('/api/entities/offers/', ActionListAllOffersAvailable::class)->setArgument('auth', 'true');
    // Rota para visualizar os detalhes de uma entidade
    $app->GET('/api/entities/{id}/', ActionDetailsEntity::class)->setArgument('auth', 'true');
    // Rota para listar as ofertas disponíveis de uma entidade
    $app->GET('/api/entities/offers/{id}/', ActionListOffersAvailable::class)->setArgument('auth', 'true');
    // Rota para registar uma compra disponivel de um cliente
    $app->POST('/api/entities/offers/buy/', ActionBuyOffer::class)->setArgument('auth', 'true');
    // Rota para listar as compras de um cliente
    $app->GET('/api/users/purchase/', ActionListPurchase::class)->setArgument('auth', 'true');
    // Rota para cancelar uma compra de um cliente
    $app->PATCH('/api/users/purchase/', ActionCancelPurchase::class)->setArgument('auth', 'true');
    
};
