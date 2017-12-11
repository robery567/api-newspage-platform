<?php
namespace Newspage\Api\Provider;

use Newspage\Api\Config;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ConfigServiceProvider implements ServiceProviderInterface {
    public function register(Container $pimple) {
        $pimple['config'] = function () use ($pimple) {
            return new Config($pimple['config.filename']);
        };
    }
}