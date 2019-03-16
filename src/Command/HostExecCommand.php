<?php

namespace Infra\Command;

use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Infra\Firewall\IptablesFirewall;

class HostExecCommand extends AbstractCommand
{
    public function configure()
    {
        $this->setName('host:exec')
            ->setDescription('Execute a command on a host or a group of hosts')
            ->addArgument(
                'hosts',
                InputArgument::REQUIRED,
                'Name of the hosts'
            )
            ->addArgument(
                'cmd',
                InputArgument::REQUIRED,
                'Command to execute'
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $hosts = $this->infra->getHosts($input->getArgument('hosts'));
        $command = $input->getArgument('cmd');

        foreach ($hosts as $host) {

            $output->writeLn("<info>" . $host->getName() . "</info>");
            // Create ssh and scp clients
            $sshBuilder = $this->infra->getSshBuilder($host);
            $ssh = $sshBuilder->buildClient();
            $ssh->exec([$command]);
            echo $ssh->getOutput();
            if ($ssh->getExitCode()!=0) {
                throw new RuntimeException($ssh->getErrorOutput());
            }
        }
    }
}
