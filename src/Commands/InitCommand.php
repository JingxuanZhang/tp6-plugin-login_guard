<?php


namespace Moonpie\Tp6Plugin\LoginGuard\Commands;

use Phinx\Util\Util;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;

class InitCommand extends Command
{
    protected function configure()
    {
        $init_usage = <<<EOT
mp:login-guard-tool init
EOT;

        $this->setDescription('处理登录守卫相关命令')
             ->addArgument('cmd', Argument::REQUIRED, '子命令')
             ->setName('mp:login-guard-tool')
             ->addUsage($init_usage);

    }

    protected function execute(Input $input, Output $output)
    {
        $cmd = $input->getArgument('cmd');
        switch ($cmd) {
            case 'init':
                return $this->handleInitCommand();
            default:
                $this->output->warning(sprintf('未知命令"%s"', $cmd));
                return 0;
        }
    }

    protected function handleInitCommand()
    {
        $dir = $this->getApp()->getRootPath(
            ).'database'.DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) {
            $this->output->error(
                sprintf(
                    '系统依赖数据迁移,请先确保迁移命令已在您的系统'
                )
            );

            return;
        }
        foreach (glob(__DIR__.'/../../migrations/*.php') as $source) {
            $dest = $dir.Util::getCurrentTimestamp().'_'.basename($source);
            //printf('将会把文件"%s"复制到"%s"'.PHP_EOL, $source, $dest);
            $pattern = $dir . '*_' . basename($source);
            $copy = true;
            foreach(glob($pattern) as $exist_dest) {
                $this->output->warning(sprintf(
                    '目标目录存在相似文件"%s",系统将会忽略', basename($exist_dest)
                ));
                $copy = false;
                break;
            }
            if ($copy) {
                copy($source, $dest);
            }
        }
    }
}