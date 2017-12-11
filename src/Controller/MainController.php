<?php
namespace Newspage\Api\Controller;

use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MainController extends ApiController
{
    protected function getRoutes(): array
    {
        return [
            'main_default' => [
                'path' => '/',
                'action' => [MainController::class, 'defaultAction'],
            ],

            'test_sender' => [
                'path' => '/test/sender',
                'action' => [MainController::class, 'blablaAction']
            ],

            'main_index' => [
                'path' => '/index.json',
                'action' => [MainController::class, 'indexAction'],
            ],
        ];
    }

    public function connect(Application $app): ControllerCollection
    {
        return parent::connect($app);
    }

    public function defaultAction(Application $api): RedirectResponse
    {
        return $api->redirect($api['url_generator']->generate('main_index'));
    }

    /**
     * Index page
     * @param Application $api
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function indexAction(Application $api, Request $request): Response
    {
        $json = $api['builder.json'];
        $json->setResponse(200);
        $json->setContent('site-path', $request->getUri());

        return $json->respond();
    }

    public function blablaAction(): Response
    {
        $response = <<<EOV
<!DOCTYPE html>
<html>
    <head>
        <title>Sender</title>
    </head>
    <body>
        <h1>Sender</h1>
        <p>This page sends API content with postMessage</p>
    </body>
    
    <script type="text/javascript">
        parent.postMessage("cereale", "*");
    </script>
</html>
EOV;
        return new Response($response, Response::HTTP_OK);
    }
}