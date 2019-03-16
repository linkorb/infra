<?php

namespace Infra\Command;

use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Infra\Resource\ResourceInterface;
use Infra\Infra;

class GetCommand extends AbstractCommand
{
    public function configure()
    {
        $this->setName('get')
            ->setDescription('Get type index or instance')
            ->addArgument(
                'typeName',
                InputArgument::OPTIONAL,
                'Type name'
            )
            ->addArgument(
                'resourceName',
                InputArgument::OPTIONAL,
                'Resource Name'
            )
            ->addArgument(
                'propertyName',
                InputArgument::OPTIONAL,
                'Property Name'
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $typeName = $input->getArgument('typeName');
        $resourceName = $input->getArgument('resourceName');
        $propertyName = $input->getArgument('propertyName');
        $infra = $this->infra;
        if (!$typeName) {
            foreach($infra->getTypeNames() as $typeName) {
                $aliases = $infra->getTypeAliases($typeName);
                $output->writeLn(' * <info>' . $typeName . '</info> (' . implode(', ', $aliases) . ')');
                // print_r($aliases);
            }
            return;
        }
        
        $typeName = $infra->getCanonicalTypeName($typeName);
        if (!$typeName) {
            exit("Unknown type: " . $typeName . PHP_EOL);
        }
        $resources = $infra->getResourcesByType($typeName);

        if (!$resourceName) {
            foreach ($resources as $resource) {
                $output->writeLn(" * " . $resource->getName());
            }
            return;
        }
        $resource = $infra->getResource($typeName, $resourceName);
        if (!$propertyName) {
            $yaml = Yaml::dump($resource->serialize(), 10, 2);
            $output->write($yaml);
            return;
        }

        $value = $resource[$propertyName];
        if (is_string($value) || is_numeric($value)) {
            $output->writeLn($value);
            return;
        }
        if (is_array($value)) {
            foreach ($value as $k=>$v) {
                if (is_string($v)) {
                    $output->writeLn($v);
                } else {
                    if (is_a($v, ResourceInterface::class)) {
                        $output->writeLn($v->getName());
                    }
                }
            }
            return;
        }
        exit("Unsupported property type...\n");
    }
}
