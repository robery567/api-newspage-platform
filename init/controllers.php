<?php
/**
 * @var \Newspage\Api\Api $api
 */

$api->mount('/', new \Newspage\Api\Controller\MainController());
$api->mount('/ad', new \Newspage\Api\Controller\AdController());
$api->mount('/video', new \Newspage\Api\Controller\VideoController());
$api->mount('/gallery', new \Newspage\Api\Controller\GalleryController());

$api->error(function (\Exception $e, \Symfony\Component\HttpFoundation\Request $request, $code) use ($api) {
    $json = $api->json;
    /** @var \Newspage\Api\Env $env */
    $env = $api->getEnv();

    if ($code == 404) {
        $json->setError('Requested resource can not be found.', $code);
    } else {
        if ($code == 500) {
            $json->setError('Whoops! Server is currently not responding.', $code);
        } else {
            $json->setError('Something went wrong, and we can not determine what it is...', $code);
        }
    }

    if ($env->isDev()) {
        $json->setContent('error-details', [
            'code'    => $e->getCode(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'message' => $e->getMessage(),
            'trace'   => $e->getTrace(),
        ]);
    }

    return $api->json();
});