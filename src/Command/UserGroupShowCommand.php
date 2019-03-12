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

class UserGroupShowCommand extends Command
{
    public function configure()
    {
        $this->setName('user-group:show')
            ->setDescription('Show user group details')
            ->addArgument(
                'user-group',
                InputArgument::REQUIRED,
                'Name of the user group'
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {

        $userGroupName = $input->getArgument('user-group');

        $output->writeLn("<info>UserGroup:</info> <comment>{$userGroupName}</comment>");
        $loader = new AutoInfraLoader();
        $infra = $loader->load();

        $user = $infra->getUserGroups()->get($userGroupName);
        //print_r($user);

        $output->writeLn("");
        $output->writeLn("<info>Properties:</info>");
        foreach ($user->getProperties() as $p) {
            $output->writeLn("  - <comment>{$p->getName()}</comment> = {$p->getValue()}");
        }

        $output->writeLn("");
        $output->write("<info>Hosts:</info>");
        foreach ($user->getHosts() as $h) {
            $output->write(" {$h->getName()}");
        }
        $output->writeLn("");

        $output->writeLn("");
        $output->write("<info>Users:</info>");
        foreach ($user->getUsers() as $u) {
            $output->write(" {$u->getName()}");
        }
        $output->writeLn("");
    }
}
