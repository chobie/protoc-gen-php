<?php
namespace protocolbuffers\console;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use protocolbuffers\console\command\GenerateCommand;

class Application extends BaseApplication
{
    protected function getCommandName(InputInterface $input)
    {
        return 'protoc-gen-php';
    }

    public function __construct($version)
    {
        parent::__construct("protoc-gen-php", $version);
    }

    protected function getDefaultCommands()
    {
        $defaultCommands = parent::getDefaultCommands();
        $defaultCommands[] = new GenerateCommand();
        return $defaultCommands;
    }

    public function getDefinition()
    {
        $inputDefinition = parent::getDefinition();
        $inputDefinition->setArguments();
        return $inputDefinition;
    }
}
