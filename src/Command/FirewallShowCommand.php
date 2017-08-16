<?php

namespace Infra\Command;

use RuntimeException;

use Infra\Model\Infra;
use Infra\Loader\AutoInfraLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Infra\Firewall\IptablesFirewall;

class FirewallShowCommand extends Command
{
    public function configure()
    {
        $this->setName('firewall:show')
            ->setDescription('Show firewall for given host')
            ->addArgument(
                'host',
                InputArgument::REQUIRED,
                'Name of the host'
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $hostName = $input->getArgument('host');
        $loader = new AutoInfraLoader();
        $infra = $loader->load();
        $host = $infra->getHosts()->get($hostName);

        $firewall = new IptablesFirewall();
        $script = $firewall->generateRules($infra, $host);
        echo $script;
    }
}
