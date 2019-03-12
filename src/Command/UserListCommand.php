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

class UserListCommand extends Command
{
    public function configure()
    {
        $this->setName('user:list')
            ->setDescription('List users')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {

        $output->writeLn("<info>Users:</info>");
        $loader = new AutoInfraLoader();
        $infra = $loader->load();

        foreach ($infra->getUsers() as $user) {
            $output->writeLn("  <comment>" . $user->getName() . "</comment>");
            $output->writeLn("    ssh username: " . $user->getSshUsername() . " key: " . $user->getSshPublicKey());
            $output->writeLn("    github: " . $user->getGithubUsername());
            $output->writeLn("    image: " . $user->getImageUrl());
            
        }
        $output->writeLn("");
    }
}
