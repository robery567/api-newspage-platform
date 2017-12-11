<?php
namespace Newspage\Api\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class DateServiceProvider implements ServiceProviderInterface {
    public function register(Container $pimple) {
        $pimple['date'] = $pimple->factory(function () use ($pimple) {
            return new \DateTime();
        });

        $pimple['date.now'] = $pimple->factory(function () use ($pimple) {
            return $pimple['date']->format($pimple['date.format']);
        });
    }
}