<?php
namespace Newspage\Api\Controller;

use Newspage\Api\Builder\JsonBuilder;
use Newspage\Api\Builder\PathBuilder;
use Newspage\Api\Config;
use Newspage\Api\Model\AbstractModel;
use claviska\SimpleImage;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

abstract class ApiController implements ControllerProviderInterface
{
    protected $api;
    protected $uuidTest = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

    abstract protected function getRoutes(): array;

    public function connect(Application $app): ControllerCollection
    {
        $this->api = $app;
        $routes = $this->getRoutes();

        /** @var ControllerCollection $controllers */
        $controllers = $app['controllers_factory'];
        foreach ($routes as $route => $values) {
            $methods = ['GET'];
            $options = [];

            if (isset($values['options']) && is_array($values['options'])) {
                $options = array_merge($options, $values['options']);
            }

            if (isset($values['methods'])) {
                if (isset($options['strict']) && $options['strict'] === true) {
                    $methods = $values['methods'];
                } else {
                    $methods = array_merge($methods, $values['methods']);
                }
            }

            array_map('strtoupper', $methods);
            $methods = implode('|', $methods);

            $controllers
                ->match($values['path'], $values['action'])
                ->bind($route)
                ->method($methods);
        }

        return $controllers;
    }

    protected function getModel(Application $app, $name = ''): AbstractModel
    {
        if (empty($name)) {
            $classFQDN = explode('\\', get_class($this));
            $classSegments = count($classFQDN);

            $name = substr($classFQDN[$classSegments - 1], 0, -10);
        }

        $modelName = "\\Newspage\\Api\\Model\\{$name}Model";
        if (!class_exists($modelName)) {
            throw new \Exception(sprintf("Model is missing for controller: %s", $modelName));
        }

        return new $modelName($app);
    }

    protected function getJsonBuilder(): JsonBuilder
    {
        return $this->api['builder.json'];
    }

    protected function getPathBuilder(): PathBuilder
    {
        return $this->api['builder.path'];
    }

    protected function validate($what, callable $rules): bool
    {
        return array_key_exists($what, $this->api->json) && call_user_func($rules);
    }

    protected function validateUuid4($uuid): bool
    {
        return preg_match($this->uuidTest, $uuid);
    }

    protected function processMedia(File $media, $savePath): bool
    {
        $mimeType = $media->getMimeType();
        /** @var Config $config */
        $config = $this->api['config'];

        if (in_array($mimeType, $config->get('media.mimes.image'))) {
            return $this->processImage($media, $savePath);
        } else if (in_array($mimeType, $config->get('media.mimes.video'))) {
            return $this->processVideo($media, $savePath);
        } else {
            return false;
        }
    }

    protected function processImage(File $media, $savePath): bool
    {
        $processor = new SimpleImage();
        $processor->fromFile($media->getRealPath())
            ->overlay($this->getWatermark(), 'bottom right')
            ->toFile($savePath);

        return (bool)realpath($savePath);
    }

    protected function processVideo(File $media, $savePath): bool
    {
        $ffmpeg = FFMpeg::create();
        $processor = $ffmpeg->open($media->getRealPath());
        $processor->filters()->watermark($this->getWatermark(), [
            'position' => 'relative',
            'bottom' => 10,
            'right' => 10,
        ]);
        $processor->save(new X264('libmp3lame'), $savePath);
        return (bool)realpath($savePath);
    }

    protected function getCdnDirectory(string $directory = null): string
    {
        /** @var Config $config */
        $config = $this->api['config'];

        $__path = [
            $config->get('cdn.base'),
            $config->get('cdn.user'),
            'web',
            $directory,
        ];
        $basePath = implode('/', $__path);
        return $basePath;
    }

    protected function getBaseDirectory($directory, $articleId): string
    {
        $fs = new Filesystem();
        $basePath = $this->getCdnDirectory($directory);
        if (!$fs->exists($articleMediaPath = "{$basePath}/{$articleId}")) {
            $fs->mkdir($articleMediaPath);
        }

        return $articleMediaPath;
    }

    protected function getWatermark(): string
    {
        return $this->api['api.directory.root'] . '/web/watermark.png';
    }
}