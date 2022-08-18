<?php


namespace Moonpie\Tp6Plugin\LoginGuard;

use think\Service as Base;
class Service extends Base
{
    public function register()
    {
    }

    public function boot()
    {
        $this->commands([
            Commands\InitCommand::class
        ]);
    }
}