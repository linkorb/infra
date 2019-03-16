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

class FirewallImportCommand extends AbstractCommand
{
    public function configure()
    {
        $this->setName('firewall:import')
            ->setDescription('Imports firewall resources')
            ->addArgument(
                'filename',
                InputArgument::REQUIRED,
                'Name of the yaml file to import'
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
        $yaml = file_get_contents($filename);
        $data = Yaml::parse($yaml);
        $resource = [
            'FirewallRule' => [],
        ];
        foreach ($data['host_groups'] as $hostGroupName => $hostGroupData) {

            foreach ($hostGroupData['firewall_rules'] ?? [] as $firewallRuleName => $firewallRuleData) {
                $firewallRuleName = $hostGroupName . '__' . $firewallRuleName;
                echo $firewallRuleName . PHP_EOL;
                if (!isset($resource['FirewallRule'][$firewallRuleName])) {
                    $remote = $firewallRuleData['remote'] ?? null;
                    if ($remote=='*') {
                        $remote = null;
                    }
                    $remote = str_replace('group:', '', $remote);
                    $remote = str_replace('host:', '', $remote);
                    $resource['FirewallRule'][$firewallRuleName] = [
                        'kind' => 'FirewallRule',
                        'metadata' => [
                            'name' => $firewallRuleName,
                        ],
                        'spec' => [
                            'hosts' => $hostGroupName,
                            'remoteHosts' => $remote,
                            'template' => $firewallRuleData['template'],
                        ]
                    ];
                }
                $firewallRule = &$resource['FirewallRule'][$firewallRuleName];
                $firewallRule['metadata']['name'] = $firewallRuleName;
            }
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
