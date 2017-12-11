<?php
namespace Newspage\Api\Builder;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;

class PathBuilder
{
    private $path = [];

    private $compiled;

    public function __construct($basepath)
    {
        $this->path[] = $basepath;

        return $this;
    }

    public function reset($basepath): self
    {
        unset($this->path);
        $this->path[] = $basepath;

        return $this;
    }

    public function addPaths(array $paths): self
    {
        if (!is_array($paths)) {
            throw new \InvalidArgumentException("You must provide an array for path container.");
        }

        foreach ($paths as $path) {
            if (is_array($path)) {
                $this->addPaths($path);
            }

            $this->addPath($path);
        }

        return $this;
    }

    public function addPath(string $path): self
    {
        if (!is_string($path)) {
            throw new \InvalidArgumentException("You must provide a string for path container.");
        }

        $this->path[] = $path;

        return $this;
    }

    public function addUpper(): self
    {
        $this->addPath('..');

        return $this;
    }

    public function flush(): self
    {
        unset($this->path);

        return $this;
    }

    public function validate(): bool
    {
        $compiled = $this->compilePath();

        if (class_exists('Symfony\\Component\\Filesystem\\Filesystem')) {
            $fs = new Filesystem();
            if (!$fs->exists($compiled)) {
                throw new FileNotFoundException("The path you have built is invalid.");
            }
        } else {
            if (!file_exists($compiled) || !is_dir($compiled)) {
                throw new \Exception('The path you have built is invalid.');
            }
        }

        return true;
    }

    private function compilePath(): string
    {
        $this->compiled = implode('/', $this->path);

        return $this->compiled;
    }

    public function realPath(): string
    {
        $compiled = realpath($this->compilePath());
        if (!$compiled) {
            throw new \Exception('The path you have built is invalid.');
        }

        return $compiled;
    }

    public function getPath(): string
    {
        return $this->compilePath();
    }
}