<?php

namespace Infra\Command;

use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Infra\Firewall\IptablesFirewall;
use Docopt;

class ScriptCommand extends AbstractCommand
{
    public function configure()
    {
        $this->setName('script')
            ->setDescription('List scripts')
            ->addArgument(
                'scriptName',
                InputArgument::OPTIONAL,
                'Name of the script'
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('scriptName');
        if (!$name) {
            foreach ($this->infra->getScripts() as $script) {
                $output->writeLn("<info>" . $script->getName() . "</info> " . $script->getFilename());
            }
            return;
        }
        $scripts = $this->infra->getScripts();

        $script = $scripts[$name];
        $output->writeLn('<info>' . $script->getDoc() . '</info>');
    }
}
