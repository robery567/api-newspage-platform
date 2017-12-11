<?php
namespace Newspage\Api\Provider;

use Newspage\Api\Builder\JsonBuilder;
use Newspage\Api\Builder\PathBuilder;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class BuilderServiceProvider implements ServiceProviderInterface {
    public function register(Container $pimple) {
        $pimple['builder.json'] = $pimple->factory(function () use ($pimple) {
            return new JsonBuilder($pimple['api.version']);
        });

        $pimple['builder.path'] = $pimple->factory(function () use ($pimple) {
            return new PathBuilder($pimple['api.directory.root']);
        });
    }
}