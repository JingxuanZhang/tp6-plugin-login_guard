<?php


namespace Moonpie\Tp6Plugin\LoginGuard\Contracts;


interface LimitConfigInterface
{
    const KEY_ACCOUNT_TEMP_LOCK = 'account_temp_lock';
    const KEY_ACCOUNT_FOREVER_LOCK = 'account_forever_lock';
    const KEY_IP_TEMP_LOCK = 'ip_temp_lock';
    public function loadConfig($name, $key, $default = null);
}