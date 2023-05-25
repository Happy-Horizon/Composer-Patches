<?php
use DI\ContainerBuilder;
use HH\Patches\Router;
use Psr\Log\LogLevel;
use Slim\Factory\AppFactory;

require __DIR__ . '/vendor/autoload.php';

/**
 * Definitions for Dependency Injection
 */
$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions([
    'settings' => [
        'displayErrorDetails' => false, // Should be set to false in production
        'logger' => [
            'name' => 'patches-log',
            'path' => __DIR__ . '/var/log/debug.log',
            'level' => LogLevel::DEBUG,
        ],
    ],
]);
AppFactory::setContainer($containerBuilder->build());
$app = AppFactory::create();

Router::populateRoutes($app);

/**
 * Application Constants
 */
const BP = __DIR__;

$app->run();