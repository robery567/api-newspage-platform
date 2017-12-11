<?php
namespace Newspage\Api;

use Newspage\Api\Builder\JsonBuilder;
use Newspage\Api\Provider\BuilderServiceProvider;
use Newspage\Api\Provider\ConfigServiceProvider;
use Newspage\Api\Provider\ConverterServiceProvider;
use Newspage\Api\Provider\DateServiceProvider;
use Newspage\Api\Provider\EnvServiceProvider;
use Newspage\Api\Provider\LoggerServiceProvider;
use JDesrosiers\Silex\Provider\CorsServiceProvider;
use Silex\Application;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\SwiftmailerServiceProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class Api extends Application
{
    const VERSION = '1.0.0';
    public $json;
    private $env;
    private $projectDirectory;

    public function __construct(array $values = [])
    {
        $this->env = new Env();

        $values['api.version'] = self::VERSION;
        $values['api.php.supported'] = version_compare(PHP_VERSION, '7.0.0', '>=');
        $values['api.env'] = $this->env->get();
        $values['api.directory.root'] = dirname(__DIR__);

        $this->setProjectDirectory($values['api.directory.root']);

        parent::__construct($values);

        $this->json = new JsonBuilder($values['api.version']);
        $this->initialize();
    }

    public function setProjectDirectory(string $directory): Api
    {
        $this->projectDirectory = $directory;

        return $this;
    }

    public function getProjectDirectory(): string
    {
        return $this->projectDirectory;
    }

    public function initialize(): Api
    {
        $this
            ->register(new ConfigServiceProvider())
            ->register(new EnvServiceProvider())
            ->register(new DateServiceProvider())
            ->register(new BuilderServiceProvider())
            ->register(new SwiftmailerServiceProvider())
            ->register(new LoggerServiceProvider())
            ->register(new ServiceControllerServiceProvider())
            ->register(new DoctrineServiceProvider())
            ->register(new ConverterServiceProvider())
        ;

        if ($this->env->isProd()) {
            $this->register(new HttpFragmentServiceProvider());
        }

        return $this;
    }

    public function getEnv(): Env
    {
        return $this->env;
    }

    public function run(Request $request = null)
    {
        if ($this->env->isProd()) {
            $this['http_cache']->run($request);
        } else {
            parent::run($request);
        }
    }

    public function json($data = array(), $status = 200, array $headers = array()): JsonResponse
    {
        if (empty($data) || is_null($data)) {
            $data = $this->json->getJson();
        } else {
            $data = $this->json->setContents($data)->getJson();
        }

        return parent::json($data, $this->json->getResponse(), $headers);
    }
}