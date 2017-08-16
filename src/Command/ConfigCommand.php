<?php

namespace Infra\Command;

use RuntimeException;

use Infra\Model\Infrastructure;
use Infra\Loader\AutoInfraLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigCommand extends Command
{
    protected $inventory;

    public function configure()
    {
        $this->setName('config')
            ->setDescription('Show configuration')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $loader = new AutoInfraLoader();
        $infra = $loader->load();

        $output->writeLn("<comment>Hosts:</comment>");
        foreach ($infra->getHosts() as $host) {
            $output->write(
                "<info>" . $host->getName() . "</info>"
            );

            //print_r($host);
            foreach ($host->getHostGroups() as $hostGroup) {
              $output->write(" <comment>@" . $hostGroup->getName() . "</comment>");
            }
            $output->writeLn("");

            foreach ($host->getProperties() as $p) {
                $output->writeLn(
                    "  - " . $p->getName()  . '=' . $p->getValue() . "</info> "
                );
            }
        }
        $output->writeLn("");

        $output->writeLn("<comment>Host groups:</comment>");
        foreach ($infra->getHostGroups() as $hostGroup) {
            $output->write(
                "<info>" . $hostGroup->getName() . "</info>"
            );
            $hosts = $hostGroup->getHosts();
            foreach ($hosts as $host) {
              $output->write(" <comment>@" . $host->getName() . "</comment>");
            }
            $output->writeLn("");

            foreach ($hostGroup->getRules() as $r) {
                $output->writeLn(
                    "  - " . $r->getName() . " (" . $r->getRemote() . ") " . $r->getTemplate()
                );
            }
        }
    }
}
