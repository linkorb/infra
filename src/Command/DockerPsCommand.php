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

class DockerPsCommand extends Command
{
    public function configure()
    {
        $this->setName('docker:ps')
            ->setDescription('Docker upstream')
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


        $client = new DockerClient([
            'remote_socket' => 'tcp://' . $host->getConnectionAddress() . ':2375',
            'ssl' => false,
        ]);
        $docker = new Docker($client);
        $containers = $docker->getContainerManager()->findAll();
        //print_r($containers);
        $output->writeLn('CONTAINER ID        IMAGE               COMMAND                  CREATED             STATUS              PORTS                 NAMES');

        foreach ($containers as $container) {
            $line = '';
            $line .= $this->resize($container->getId(), 12) . '        ';
            $line .= $this->resize($container->getImage(), 19) . ' ';
            $line .= '"' . $this->resize($container->getCommand(), 22) . '" ';
            $line .= $this->resize(date('d/M/Y H:i', $container->getCreated()), 19) . ' ';
            $line .= $this->resize($container->getStatus(), 19) . ' ';
            $p = '';
            foreach ($container->getPorts() as $port) {
                $p .= $port->getPrivatePort() .'/' . $port->getType() . ', ';
            }
            $line .= $this->resize(trim($p, ' ,'), 21) . ' ';
            $line .= $this->resize(trim($container->getNames()[0], '/'), 24);
            $output->writeLn($line);
        }

    }

    private function resize($string, $size)
    {
        $string = substr($string, 0, $size);
        return str_pad($string, $size, ' ');
    }
}
