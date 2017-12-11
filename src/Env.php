<?php

namespace Newspage\Api;

class Env
{
    private $env;

    public function __construct()
    {
        $this->env = getenv('API_ENV');

        if (empty($this->env)) {
            throw new \InvalidArgumentException('API_ENV variable is empty.');
        }

        return $this;
    }

    public function get(): string
    {
        return $this->env;
    }

    public function is($env): bool
    {
        return ($this->env == $env);
    }

    public function isDev(): bool
    {
        return $this->is('dev');
    }

    public function isTest(): bool
    {
        return $this->is('test');
    }

    public function isProd(): bool
    {
        return $this->is('prod');
    }

    /**
     * @deprecated use $this->isProd() instead.
     * @return bool
     */
    public function isLive(): bool
    {
        return $this->isProd();
    }
}