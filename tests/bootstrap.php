<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

new Dotenv()->bootEnv(dirname(__DIR__).'/.env');

$console = escapeshellarg(dirname(__DIR__).'/bin/console');
passthru("php $console doctrine:migrations:migrate --env=test --no-interaction --quiet", $exitCode);

if ($exitCode !== 0) {
    exit($exitCode);
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}
