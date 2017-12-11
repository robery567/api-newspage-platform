<?php
require __DIR__ . '/config_test.php';

$api['logger.receivers'] = 'dev@robery.eu';
$api['http_cache.cache_dir'] = dirname(__DIR__) . '/var/cache/http';
$api['http_cache.options'] = [

];

$trustedProxies = [
    '127.0.0.1',
    '127.0.1.1',
    'fe80::1',
    '::1',
];

\Symfony\Component\HttpFoundation\Request::setTrustedProxies($trustedProxies);