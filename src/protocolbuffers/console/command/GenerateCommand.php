<?php
namespace protocolbuffers\console\command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCommand extends Command
{
    protected function configure()
    {
        $this->setName('protoc-gen-php');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        fwrite(STDERR, "# protoc-gen-php\n");

        $stdin = stream_get_contents(STDIN);
        $time = time();

        if (getEnv("CAPTURE")) {
            file_put_contents(sprintf("%s.input.bin", $time), $stdin);
        }

        $compiler = new \protocolbuffers\Compiler();
        $response = $compiler->compile($stdin);
        $result = $response->serializeToString();

        if (getEnv("CAPTURE")) {
            file_put_contents(sprintf("%s.output.bin", $time), $result);
        }

        fwrite(STDOUT, $result);
    }
}