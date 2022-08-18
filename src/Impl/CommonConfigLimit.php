<?php


namespace Moonpie\Tp6Plugin\LoginGuard\Impl;

use Moonpie\Tp6Plugin\LoginGuard\Contracts\LimitConfigInterface;
use think\Config;

class CommonConfigLimit implements LimitConfigInterface
{
    /**
     * @var Config
     */
    private $config;
    private $prefix;

    public function __construct(Config $config, $prefix)
    {
        $this->config = $config;
        $this->prefix = $prefix;
    }

    public function loadConfig($name, $key, $default = null)
    {
        if (is_null($key)) {
            $config_name = $this->prefix . ".{$name}";
        }else {
            $config_name = $this->prefix . ".{$name}.{$key}";
        }
        return $this->config->get($config_name, $default);
    }
}