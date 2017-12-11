<?php
namespace Newspage\Api\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ConverterServiceProvider implements ServiceProviderInterface {
    public function register(Container $pimple) {
        $pimple['converter.size'] = $pimple->factory(function ($size) use ($pimple) {
            $units = ['B', 'kB', 'MB', 'GB'];

            $bytes = max($size, 0);
            $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
            $pow = min($pow, count($units) - 1);
            $bytes /= pow(1024, $pow);

            return round($bytes, 2) . ' ' . $units[$pow];
        });

        $pimple['converter.ratio'] = $pimple->factory(function ($x, $y) use ($pimple) {
            $gcd = gmp_strval(gmp_gcd($x, $y));

            return ($x / $gcd) . ':' . ($y / $gcd);
        });
    }
}