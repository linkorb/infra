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

class GroupShowCommand extends Command
{
    public function configure()
    {
        $this->setName('group:show')
            ->setDescription('Show group details')
            ->addArgument(
                'group',
                InputArgument::REQUIRED,
                'Name of the group'
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $groupName = $input->getArgument('group');

        $output->writeLn("<info>Group:</info> " . $groupName);

        $loader = new AutoInfraLoader();
        $infra = $loader->load();

        $output->writeLn("<info>Hosts:</info>");


        $group = $infra->getHostGroups()->get($groupName);
        foreach ($group->getHosts() as $host) {
            $output->writeLn("  " . $host->getName());
        }

        $output->writeLn("<info>Rules:</info>");
        foreach ($group->getRules() as $r) {
            $output->writeLn(
                "  - " . $r->getName() . " (" . $r->getRemote() . ") " . $r->getTemplate()
            );
        }
    }
}
