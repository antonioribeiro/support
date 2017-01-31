<?php

namespace PragmaRX\Support;

use Illuminate\Config\Repository as IlluminateConfig;

class Config {

    protected $config;

    private $namespace;

    public function __construct(IlluminateConfig $config, $namespace)
    {
        $this->config = $config;

        $this->namespace = $namespace;
    }

    public function get($key, $default = null)
    {
        return $this->config->get($this->namespace.$key, $default);
    }

    public function set($key, $value = null)
    {
        $this->config->set($this->namespace.$key, $value);
    }

}
