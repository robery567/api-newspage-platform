<?php
$api['config.filename'] = 'api.yml';
$api['date.format'] = 'Y-m-d H:i:s.u';

$api['db.options'] = [
    'driver' => 'pdo_mysql',
    'dbname' => getenv('API_DB_NAME'),
    'host' => getenv('API_DB_HOST'),
    'user' => getenv('API_DB_USER'),
    'password' => getenv('API_DB_PASS'),
    'port' => getenv('API_DB_PORT'),
    'charset' => 'utf8mb4',
];