<?php
declare(strict_types=1);

use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../APP/CONFIG/database.php';

$cors = require __DIR__ . '/../APP/MIDDLEWARES/CorsMiddleware.php';
$endpoints = require __DIR__ . '/../APP/ALQUILERVEHICULOS/Presentation/Routers/endpoints.php';

$app = AppFactory::create();

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

$cors($app);
$endpoints($app);

$app->run();