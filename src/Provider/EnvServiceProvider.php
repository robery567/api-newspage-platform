<?php
namespace Newspage\Api\Provider;

use Newspage\Api\Env;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class EnvServiceProvider implements ServiceProviderInterface {
    public function register(Container $pimple) {
        $pimple['env'] = function () use ($pimple) {
            return new Env();
        };
    }
}