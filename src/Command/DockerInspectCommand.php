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
use Docker\Docker;
use Docker\DockerClient;

class DockerInspectCommand extends Command
{
    public function configure()
    {
        $this->setName('docker:inspect')
            ->setDescription('Docker inspect')
            ->addArgument(
                'host',
                InputArgument::REQUIRED,
                'Name of the host'
            )
            ->addArgument(
                'container',
                InputArgument::REQUIRED,
                'Name/ID of the container'
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $hostName = $input->getArgument('host');
        $containerId = $input->getArgument('container');

        $output->writeLn("<info>Host:</info> " . $hostName);
        $loader = new AutoInfraLoader();
        $infra = $loader->load();
        $host = $infra->getHosts()->get($hostName);


        $client = new DockerClient([
            'remote_socket' => 'tcp://' . $host->getConnectionAddress() . ':2375',
            'ssl' => false,
        ]);
        $docker = new Docker($client);
        $container = $docker->getContainerManager()->find($containerId);
        print_r($container);
    }
}
