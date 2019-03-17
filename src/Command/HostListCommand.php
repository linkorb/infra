<?php

namespace Infra\Command;

use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Infra\Firewall\IptablesFirewall;

class HostListCommand extends AbstractCommand
{
    public function configure()
    {
        $this->setName('host:list')
            ->setDescription('List hosts using the host expansion algorithm')
            ->addArgument(
                'hosts',
                InputArgument::REQUIRED,
                'Name of the hosts'
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $hosts = $this->infra->getHosts($input->getArgument('hosts'));

        foreach ($hosts as $host) {
            $output->writeLn("<info>" . $host->getName() . "</info>");
        }
    }
}
