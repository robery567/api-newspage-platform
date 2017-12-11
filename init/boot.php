<?php
if (version_compare(PHP_VERSION, '7.1.3', '<=')) {
    echo "You must have PHP 7.1.3 or newer!";
    exit;
}

require_once dirname(__DIR__) . '/vendor/autoload.php';
define('root_dir', dirname(__DIR__));

$env = new \Dotenv\Dotenv(dirname(__DIR__));
$env->load();

$env->required('API_ENV')->allowedValues(['dev', 'test', 'prod']);
$env->required(['API_DB_HOST', 'API_DB_NAME', 'API_DB_USER', 'API_DB_PASS'])->notEmpty();
$env->required('API_DB_PORT')->isInteger();

$medium = !empty(getenv('API_ENV')) ? getenv('API_ENV') : 'test';
if ($medium == "dev") {
    \Symfony\Component\Debug\Debug::enable();

    ini_set('xdebug.var_display_max_depth', -1);
    ini_set('xdebug.var_display_max_children', -1);
    ini_set('xdebug.var_display_max_data', -1);
} else {
    ini_set('display_errors', false);
}

$api = new \Newspage\Api\Api();

return $api;