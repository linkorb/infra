<?php

namespace Infra\Command;

use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class CustomCommand extends AbstractCommand
{
    protected $script;

    public static function fromScript($infra, $script)
    {
        $command = new self($infra);
        $command->setName($script->getName());
        $command->script = $script;
        return $command;
    }

    public function configure()
    {
        $this->setName('custom')
            ->setDescription('')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $process = new Process([$this->script->getFilename()]);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        echo $process->getOutput();

    }
}
