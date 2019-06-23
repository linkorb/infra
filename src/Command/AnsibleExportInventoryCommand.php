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

class AnsibleExportInventoryCommand extends AbstractCommand
{
    public function configure()
    {
        $this->setName('ansible:export-inventory')
            ->setDescription('Export ansible inventory')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $data = [];
        foreach ($this->graph->getResourcesByType('HostGroup') as $hostGroup) {
            $data[$hostGroup->getName()] = [
                'hosts' => [],
            ];
            if (count($hostGroup->getHosts())>0) {
                foreach ($hostGroup->getHosts() as $host) {
                    $data[$hostGroup->getName()]['hosts'][] = $host->getName();
                }
            }
            $vars = $hostGroup->getSpec()['vars'] ?? [];
            if (count($vars)>0) {
                $data[$hostGroup->getName()]['vars']=$vars;
            }

            if (count($hostGroup->getHosts())>0) {
                foreach ($hostGroup->getChildHostGroups() as $childHostGroup) {
                    $data[$hostGroup->getName()]['children'][] = $childHostGroup->getName();
                }
            }
        }
        $hostvars = [];
        foreach ($this->graph->getResourcesByType('Host') as $host) {
            $vars = $host->getSpec()['vars'] ?? [];
            $vars = array_merge_recursive($vars, [
                'public_ip' => $host->getPublicIp(),
                'private_ip' => $host->getPrivateIp(),
                'ansible_host' => $host->getPublicIp(),
                'ansible_user' => $host->getSshUsername(),
            ]);
            $hostvars[$host->getName()] = $vars;
        }
        $data['_meta']['hostvars'] = $hostvars;
        $output->writeLn(json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
    }
}
