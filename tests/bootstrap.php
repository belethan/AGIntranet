<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Bootstrap PHPUnit – environnement TEST uniquement
|--------------------------------------------------------------------------
|
| - Force APP_ENV=test
| - Charge .env.test en priorité
| - N'utilise JAMAIS .env (sécurité Oracle / prod)
|
*/

$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = 'test';
$_SERVER['APP_DEBUG'] = $_ENV['APP_DEBUG'] = '1';

$dotenv = new Dotenv();

// Symfony >= 5.3
if (method_exists($dotenv, 'bootEnv')) {
    $dotenv->bootEnv(dirname(__DIR__).'/.env.test');
} else {
    // Fallback ancien Symfony (au cas où)
    $dotenv->load(dirname(__DIR__).'/.env.test');
}

// Sécurité permissions fichiers en mode debug
if ((int) ($_SERVER['APP_DEBUG']) === 1) {
    umask(0000);
}
