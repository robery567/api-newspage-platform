<?php

namespace Newspage\Api\Controller;

use Newspage\Api\Api;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class VideoController extends ApiController
{
    protected function getRoutes(): array
    {
        return [
            'video_upload' => [
                'path' => '/upload/{videoId}',
                'action' => [$this, 'uploadAction'],
            ],
            'video_upload_process' => [
                'path' => '/upload/process/{videoId}',
                'action' => [$this, 'uploadProcessAction'],
                'methods' => ['POST'],
            ],
            'video_upload_done' => [
                'path' => '/upload/done/{status}',
                'action' => [$this, 'uploadDoneAction'],
            ],
        ];
    }

    public function connect(Application $app): ControllerCollection
    {
        return parent::connect($app);
    }

    public function uploadProcessAction(Api $api, Request $request, $videoId): Response
    {
        /** @var File $file */
        $file = $request->files->get('media');

        if (is_null($file) || empty($file)) {
            return new Response('Trebuie să trimiți un video!');
        }

        if (!preg_match('/(mp4)/i', $file->getMimeType())) {
            return new Response('Trebuie să trimiți un video!');
        }

        $saved = $this->processVideo($file, $this->getSavePath($videoId));

        if ($saved) {
            return $api->redirect($api['url_generator']->generate('video_upload_done', ['status' => 'uploaded']));
        } else {
            return $api->redirect($api['url_generator']->generate('video_upload_done', ['status' => 'uploadFailed']));
        }
    }

    public function uploadAction($videoId): Response
    {
        $response = <<<EOV
<!DOCTYPE html>
<html>
    <body>
        <form action="/video/upload/process/{$videoId}" enctype="multipart/form-data" method="post">
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

    private function getSavePath(string $videoId): string
    {
        $basePath = $this->getCdnDirectory('video');
        $savePath = "{$basePath}/{$videoId}.mp4";

        return $savePath;
    }
}