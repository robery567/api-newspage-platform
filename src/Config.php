<?php

namespace Newspage\Api;

use Newspage\Api\Builder\PathBuilder;
use Symfony\Component\Yaml\Yaml;

class Config
{
    protected $data;

    public function __construct($filename)
    {
        $this->parse($filename);
    }

    protected function parse($filename): array
    {
        $path = new PathBuilder(root_dir);

        $filename = $path->addPaths(['config', $filename])->realPath();
        $this->data = Yaml::parse(file_get_contents($filename));

        return $this->data ?: [];
    }

    public function set($key, $value): Config
    {
        $this->data[$key] = $value;

        return $this;
    }

    public function get($key)
    {
        return $this->data[$key];
    }

    protected function dump($filename): bool
    {
        $path = new PathBuilder(root_dir);

        $filename = $path->addPaths(['config', $filename])->getPath();
        $dump = file_put_contents($filename, Yaml::dump($this->data));

        if ($dump === false) {
            throw new \Exception('Unable to dump configuration!');
        }

        return (bool) $dump;
    }

}