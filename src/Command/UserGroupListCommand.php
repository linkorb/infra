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

class UserGroupListCommand extends Command
{
    public function configure()
    {
        $this->setName('user-group:list')
            ->setDescription('List user groups')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {

        $output->writeLn("<info>User groups:</info>");
        $loader = new AutoInfraLoader();
        $infra = $loader->load();

        foreach ($infra->getUserGroups() as $userGroup) {
            $output->writeLn("  <comment>" . $userGroup->getName() . "</comment>");
        }
        $output->writeLn("");
    }
}
