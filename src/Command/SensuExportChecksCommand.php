<?php

namespace Infra\Command;

use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Infra\Infra;

class SensuExportChecksCommand extends AbstractCommand
{
    public function configure()
    {
        $this->setName('sensu:export-checks')
            ->setDescription('Export sensu checks json')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $infra = $this->infra;
        $data = ['checks' => []];
        foreach ($infra->getResourcesByType('MonitoringCheck') as $monitoringCheck) {
            $data['checks'][$monitoringCheck->getName()] = [
                'command' => $monitoringCheck->getCommand(),
                'interval' => $monitoringCheck->getInterval(),
                'occurrences' => $monitoringCheck->getOccurrences(),
                'subscribers' => $monitoringCheck->getSubscribers(),
                'handlers' => $monitoringCheck->getHandlers(),
            ];
        }
        $output->writeLn(json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
    }
}
