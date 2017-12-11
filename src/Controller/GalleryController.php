<?php
namespace Newspage\Api\Controller;


use Newspage\Api\Api;
use claviska\SimpleImage;
use Ramsey\Uuid\Uuid;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GalleryController extends ApiController
{
    private $filename;

    public function getRoutes(): array
    {
        return [
            'media_api_uploader' => [
                'path' => '/upload/{articleId}',
                'action' => [$this, 'uploadAction'],
                'methods' => ['POST'],
                'options' => [
                    'strict' => true,
                ],
            ],
            'media_api_fetcher' => [
                'path' => '/{articleId}',
                'action' => [$this, 'fetchAction'],
            ],
            'media_api_remover' => [
                'path' => '/delete/{articleId}/{mediaName}',
                'action' => [$this, 'deleteAction'],
                'options' => [
                    'strict' => true
                ],
            ],
            'media_api_thumbnail' => [
                'path' => '/thumbnail/{articleId}/{mediaFilename}',
                'action' => [$this, 'thumbnailAction'],
            ]
        ];
    }

    public function connect(Application $app): ControllerCollection
    {
        return parent::connect($app);
    }

    public function thumbnailAction(Api $api, $articleId, $mediaFilename): Response
    {
        $options = [
            'fallbackUrl' => 'https://placehold.it/512x256',
            'fallbackMimeType' => 'image/png',
        ];

        if (!$this->validateUuid4($articleId)) {
            throw new \Exception('Invalid Article ID');
        }

        $fs = new Filesystem();
        $fileName = "{$articleId}.png";

        $source = implode('/', [
            $this->getBaseDirectory('gallery', $articleId),
            $mediaFilename
        ]);
        $destination = implode('/', [
            $this->getCdnDirectory('thumbnail'),
            $fileName
        ]);

        try {
            if (!$fs->exists($source) || !$this->mediaIsValid($mediaFilename)) {
                file_put_contents($destination, file_get_contents($options['fallbackUrl']));
            } else {
                $fs->copy($source, $destination);
            }

            $protocol = $api['config']->get('cdn.schema');
            $hostname = $api['config']->get('cdn.host');

            return new JsonResponse([
                'location' => "{$protocol}://{$hostname}/thumbnail/{$fileName}",
            ]);
        } catch (FileNotFoundException $e) {
            return new JsonResponse([
                'error' => 'Unable to copy source ' . $source . ' as it does not exist.',
                'details' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        } catch (IOException $e) {
            return new JsonResponse([
                'error' => 'Unable to copy ' . $source . ' to ' . $destination,
                'details' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function uploadAction(Api $api, Request $request, $articleId): Response
    {
        $files = $request->files->all();
        reset($files);
        $media = current($files);

        if (!$this->validateUuid4($articleId)) {
            return $api->json->setError('Invalid Article ID', 400)->respond();
        }

        if (empty($media)) {
            return $api->json->setError('No files sent to be processed.', 400)->respond();
        }

        $savePath = $this->getSavePath($media, $articleId);
        $fileName = $this->getFilename();
        $processed = $this->processMedia($media, $savePath);

        if (!$processed) {
            return $api->json->setError('Unable to process media.', 400)->respond();
        }

        $location = "/gallery/{$articleId}/{$fileName}";

        return new JsonResponse([
            'location' => $location
        ]);
    }

    public function deleteAction(Api $api, $articleId, $mediaName): Response
    {
        $fs = new Filesystem();
        $path = $this->getBaseDirectory('gallery', $articleId) . '/' . $mediaName;

        try {
            $fs->remove($path);
            return $api->json->setContent('message', 'File deleted successfully!')->setResponse(200)->respond();
        } catch (FileNotFoundException $e) {
            return $api->json->setError('Image not found on server: ' . $path)->setResponse(404)->respond();
        } catch (\Exception $e) {
            return $api->json->setError('Unknown server error.')->setResponse(500)->respond();
        }
    }

    public function fetchAction(Api $api, $articleId): Response
    {
        $directory = $this->getBaseDirectory('gallery', $articleId);

        if (!is_dir($directory)) {
            return $api->json->setError('Requested media could not be found.', 404)->respond();
        }

        $finder = new Finder();
        $finder->files()->in($directory)->sortByChangedTime();
        $results = [];
        foreach ($finder as $fileInfo) {
            $protocol = $api['config']->get('cdn.schema');
            $hostname = $api['config']->get('cdn.host');
            $filename = $fileInfo->getFilename();

            $imgInfo = new SimpleImage();
            $imgInfo->fromFile($fileInfo->getRealPath());

            $results[] = [
                'src' => "{$protocol}://{$hostname}/gallery/{$articleId}/{$filename}",
                'w' => $imgInfo->getWidth(),
                'h' => $imgInfo->getHeight(),
            ];
        }
        $response = count($results) > 0 ? 200 : 404;
        $api->json->setResponse($response);
        $api->json->setContent('results', $results);

        return $api->json();
    }

    private function getMediaFilename(File $media, $articleId): string
    {
        $fileName = Uuid::uuid5($articleId, bin2hex(random_bytes(random_int(0, 65536))))->toString();
        $fileExtension = explode('/', $media->getMimeType())[1];
        $file = "{$fileName}.{$fileExtension}";

        return $file;
    }

    private function getSavePath(File $media, string $articleId): string
    {
        $baseDirectory = $this->getBaseDirectory('gallery', $articleId);
        $this->filename = $fileName = $this->getMediaFilename($media, $articleId);
        $savePath = "{$baseDirectory}/{$fileName}";

        return $savePath;
    }

    private function getFilename(): string
    {
        return $this->filename;
    }

    private function mediaIsValid(string $uuid): bool
    {
        if (strpos('.', $uuid) !== false) {
            $uuid = substr($uuid, 0, 36);
        }

        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $uuid);
    }
}