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

class HostShowCommand extends Command
{
    public function configure()
    {
        $this->setName('host:show')
            ->setDescription('Show host details')
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

        $output->writeLn("<info>Host:</info> " . $hostName);
        $loader = new AutoInfraLoader();
        $infra = $loader->load();
        $host = $infra->getHosts()->get($hostName);

        $output->writeLn("  * Connection: <info>" . $host->getConnectionUsername() . '@' . $host->getConnectionAddress() . ':' . $host->getConnectionPort() . "</info>");
        $output->write("<info>Groups:</info>");
        foreach ($host->getHostGroups() as $hostGroup) {
            $output->write(" <comment>@" . $hostGroup->getName() . "</comment>");
        }
        $output->writeLn("");
        $output->writeLn("<info>Properties:</info>");
        foreach ($host->getProperties() as $p) {
            $output->writeLn("  - {$p->getName()}={$p->getValue()}");
        }
        $output->writeLn("<info>Rules:</info>");
        foreach ($hostGroup->getRules() as $r) {
            $output->writeLn(
                "  - " . $r->getName() . " (" . $r->getRemote() . ") " . $r->getTemplate()
            );
        }
        $output->writeLn("");
    }
}
