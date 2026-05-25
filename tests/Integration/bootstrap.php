<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

$envFile = __DIR__ . '/../../.env';
if (file_exists($envFile)) {
    (new Dotenv())->load($envFile);
}
