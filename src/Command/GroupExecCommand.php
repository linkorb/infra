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

class GroupExecCommand extends Command
{
    public function configure()
    {
        $this->setName('group:exec')
            ->setDescription('Execute a command on a group of hosts')
            ->addArgument(
                'group',
                InputArgument::REQUIRED,
                'Name of the group'
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
        $groupName = $input->getArgument('group');
        $command = $input->getArgument('cmd');

        $loader = new AutoInfraLoader();
        $infra = $loader->load();
        $group = $infra->getHostGroups()->get($groupName);
        foreach ($group->getHosts() as $host) {
            $output->writeLn("<info>" . $host->getName() . "</info>");
            // Create ssh and scp clients
            $sshBuilder = $infra->getSshBuilder($host->getName());
            $ssh = $sshBuilder->buildClient();
            $ssh->exec([$command]);
            echo $ssh->getOutput();
            if ($ssh->getExitCode()!=0) {
                throw new RuntimeException($ssh->getErrorOutput());
            }

        }
    }
}
