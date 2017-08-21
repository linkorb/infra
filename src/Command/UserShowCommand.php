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

class UserShowCommand extends Command
{
    public function configure()
    {
        $this->setName('user:show')
            ->setDescription('Show user details')
            ->addArgument(
                'user',
                InputArgument::REQUIRED,
                'Name of the user'
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {

        $userName = $input->getArgument('user');

        $output->writeLn("<info>User:</info> <comment>{$userName}</comment>");
        $loader = new AutoInfraLoader();
        $infra = $loader->load();

        $user = $infra->getUsers()->get($userName);
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
    }
}
