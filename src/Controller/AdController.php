<?php
namespace Newspage\Api\Controller;

use Newspage\Api\Api;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdController extends ApiController
{
    protected function getRoutes(): array
    {
        return [
            'ad_upload' => [
                'path' => '/upload/{adId}',
                'action' => [$this, 'uploadAction'],
            ],
            'ad_upload_process' => [
                'path' => '/upload/process/{adId}',
                'action' => [$this, 'uploadProcessAction'],
                'methods' => ['POST'],
            ],
            'ad_upload_done' => [
                'path' => '/upload/done/{status}',
                'action' => [$this, 'uploadDoneAction'],
            ],
            'ad_render' => [
                'path' => '/{adId}',
                'action' => [$this, 'fetchAction'],
            ]
        ];
    }

    public function connect(Application $app): ControllerCollection
    {
        return parent::connect($app);
    }

    public function uploadProcessAction(Api $api, Request $request, $adId): Response
    {
        $fs = new Filesystem();
        /** @var File $file */
        $file = $request->files->get('media');

        if (is_null($file) || empty($file)) {
            return new Response('Trebuie să trimiți o imagine!');
        }

        if (!preg_match('/(jpeg|png|gif)/i', $file->getMimeType())) {
            return new Response('Trebuie să trimiți o imagine!');
        }

        $basePath = $this->getCdnDirectory('ad');
        $fileExt = $file->guessExtension();
        $fileName = "{$adId}.{$fileExt}";

        if (!$fs->exists($basePath)) {
            $fs->mkdir($basePath);
        }

        if ($file->move($basePath, $fileName)) {
            return $api->redirect($api['url_generator']->generate('ad_upload_done', ['status' => $fileName]));
        } else {
            return $api->redirect($api['url_generator']->generate('ad_upload_done', ['status' => 'uploadFailed']));
        }
    }

    public function fetchAction(Api $api, $adId): StreamedResponse
    {
        $basePath = $this->getCdnDirectory('ad');
        $bits = explode('.', $adId);
        $adId = $bits[0];
        $fileExt = $bits[1];

        if (!$this->validateUuid4($adId)) {
            throw new \Exception('Invalid Ad ID');
        }

        if (!file_exists($url = "{$basePath}/{$adId}.{$fileExt}")) {
            $url = 'https://placehold.it/512x256';
        }

        $stream = function () use ($url) {
            readfile($url);
        };

        return $api->stream($stream, 200, ['Content-Type' => 'image/png', 'x-debug-url' => $url]);
    }

    public function uploadAction($adId): Response
    {
        $response = <<<EOV
<!DOCTYPE html>
<html>
    <body>
        <form action="/ad/upload/process/{$adId}" enctype="multipart/form-data" method="post">
            <input type="file" name="media">
            <input type="submit" value="Uploadează!" onclick="this.disabled=true; this.value='Se uploadează...';">
        </form>
    </body>
</html>
EOV;

        return new Response($response);
    }

    public function uploadDoneAction($status): Response
    {
        $response = <<<EOV
<!DOCTYPE html>
<html>
    <head><meta charset="UTF-8"></head>
    <body>
        <p>Fișierele alese de tine au fost încărcate și procesate!</p>
        <p>Link-ul spre imaginea reclamei ar trebui să apară în formular.</p>
        <p><a href="javascript:history.back();">Click aici pentru a reveni la opțiunea de upload.</a></p>
        <script type="text/javascript">
            parent.postMessage("{$status}", "*");
        </script>
    </body>
</html>
EOV;

        return new Response($response);
    }
}