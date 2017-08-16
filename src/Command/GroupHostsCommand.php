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

class GroupHostsCommand extends Command
{
    public function configure()
    {
        $this->setName('group:hosts')
            ->setDescription('List hosts in group')
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

        $loader = new AutoInfraLoader();
        $infra = $loader->load();
        $group = $infra->getHostGroups()->get($groupName);
        foreach ($group->getHosts() as $host) {
            echo $host->getName() . "\n";
        }
    }
}
