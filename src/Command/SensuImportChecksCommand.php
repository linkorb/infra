<?php

namespace Infra\Command;

use RuntimeException;

use Infra\Infra;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Infra\Firewall\IptablesFirewall;

class SensuImportChecksCommand extends AbstractCommand
{
    public function configure()
    {
        $this->setName('sensu:import-checks')
            ->setDescription('Imports sensu checks from a bundle json file')
            ->addArgument(
                'filename',
                InputArgument::REQUIRED,
                'Name of the sensu checks json file'
            )
            ->addOption(
                'write-singles',
                null,
                InputOption::VALUE_NONE,
                'Write each imported resources into seperate files in config/typeName/resourceName.yaml'
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = $input->getArgument('filename');
        $json = file_get_contents($filename);
        $data = json_decode($json, true);
        $resource = [
            'MonitoringCheck' => [],
        ];
        foreach ($data['checks'] ?? [] as $checkName => $checkData) {
            $hosts = $checkData['subscribers'] ?? null;
            if (is_array($hosts)) {
                if (count($hosts)==1) {
                    $hosts = $hosts[0];
                }
            }
            if (!isset($resource['MonitoringCheck'][$checkName])) {
                $resource['MonitoringCheck'][$checkName] = [
                    'kind' => 'MonitoringCheck',
                    'metadata' => [
                        'name' => $checkName,
                    ],
                    'spec' => [
                        'command' => $checkData['command'] ?? null,
                        'hosts' => $hosts,
                        'interval' => (int)($checkData['interval'] ?? null),
                        'occurrences' => (int)($checkData['occurrences'] ?? null),
                        'handlers' => $checkData['handlers'] ?? null,
                    ]
                ];
            }
            $check = &$resource['MonitoringCheck'][$checkName];
            $check['metadata']['name'] = $checkName;
        }

        foreach ($resource as $typeName=>$resources) {
            foreach ($resources as $name=>$res) {
                $yaml = '---' . PHP_EOL . Yaml::dump($res, 10, 2);
                if ($input->getOption('write-singles')) {
                    $path = 'config/' . $typeName;
                    if (!file_exists($path)) {
                        mkdir($path, 0777, true);
                    }
                    file_put_contents($path . '/' . $name . '.yaml', $yaml);
                } else {
                    $output->writeLn($yaml);
                }
            }
        }

    }
}
