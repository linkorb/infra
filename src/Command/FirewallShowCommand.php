<?php

namespace Infra\Command;

use RuntimeException;

use Infra\Infra;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Infra\Firewall\IptablesFirewall;

class FirewallShowCommand extends AbstractCommand
{
    public function configure()
    {
        $this->setName('firewall:show')
            ->setDescription('Show firewall for given host')
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
            $firewall = new IptablesFirewall();
            $script = $firewall->generateRules($this->infra, $host);
            $output->writeLn($script);
        }
    }
}
