<?php

namespace HappyHorizon\Patches;

use HappyHorizon\Patches\Assets;
use HappyHorizon\Patches\Controller;
use Slim\App;

class Router
{
    /**
     * @param App $app
     * @return void
     */
    public static function populateRoutes(App $app): void
    {
        $app->get('/', [Controller::class, 'root']);
        $app->get('/patches[/{params:.*}]', [Controller::class, 'patches']);
        $app->get('/generate/patches[/{params:.*}]', [Controller::class, 'generate']);
        $app->get('/static[/{filepath:.*}]', [Assets::class, 'static']);
    }
}