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

class AnsibleImportInventoryCommand extends AbstractCommand
{
    public function configure()
    {
        $this->setName('ansible:import-inventory')
            ->setDescription('Imports an ansible inventory json file as resources')
            ->addArgument(
                'filename',
                InputArgument::REQUIRED,
                'Name of the ansible inventory json file'
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
            'HostGroup' => [],
            'Host' => [],
        ];
        foreach ($data as $hostGroupName => $hostGroupData) {
            if ($hostGroupName[0]!='_') {
                // $output->writeLn('<info>' . $hostGroupName . '</info>');
                if (!isset($resource['HostGroup'][$hostGroupName])) {
                    $resource['HostGroup'][$hostGroupName] = [
                        'kind' => 'HostGroup',
                        'metadata' => [
                            'name' => $hostGroupName,
                        ],
                        'spec' => [

                        ]
                    ];
                }
                $hostGroup = &$resource['HostGroup'][$hostGroupName];
                $hostGroup['metadata']['name'] = $hostGroupName;

                foreach ($hostGroupData['hosts'] ?? [] as $hostName) {
                    if (!isset($resource['Host'][$hostName])) {
                        $resource['Host'][$hostName] = [
                            'kind' => 'Host',
                            'metadata' => [
                                'name' => $hostName
                            ],
                            'spec' => [
                                'publicIp' => null,
                                'privateIp' => null,
                                'sshUsername' => null,
                                'hostGroups' => null
                            ]
                        ];
                    }
                    $host = &$resource['Host'][$hostName];
                    if (!in_array($hostGroupName, $host['spec']['hostGroups'] ?? [])) {
                        $host['spec']['hostGroups'][] = $hostGroupName;
                    }
                }
            }
        }
        foreach ($data['_meta']['hostvars'] as $hostName => $hostVars) {
            $host = &$resource['Host'][$hostName];
            // print_r($hostVars);
            $host['spec']['publicIp'] = $hostVars['public_ip'] ?? null;
            $host['spec']['privateIp'] = $hostVars['private_ip'] ?? null;
            $host['metadata']['labels']['linkorb.com/configuration-management'] = $hostVars['cm'] ?? null;
            $host['metadata']['labels']['linkorb.com/operating-system'] = $hostVars['os'] ?? null;
            $host['metadata']['labels']['linkorb.com/provider'] = $hostVars['provider'] ?? null;
            $host['spec']['sshUsername'] = $hostVars['ansible_user'] ?? null;
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
