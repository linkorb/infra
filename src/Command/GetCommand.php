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
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $typeName = $input->getArgument('typeName');
        $resourceName = $input->getArgument('resourceName');

        $infra = new Infra();
        $infra->loadFile(__DIR__ . '/../../example/infra.yaml');

        if (!$typeName) {
            foreach($infra->getTypeNames() as $typeName) {
                $aliases = $infra->getTypeAliases($typeName);
                $output->writeLn(' * <info>' . $typeName . '</info> (' . implode(', ', $aliases) . ')');
                // print_r($aliases);
            }
            exit(0);
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
            exit(0);
        }

        $resource = $infra->getResource($typeName, $resourceName);
        $yaml = Yaml::dump($resource->serialize(), 10, 2);
        $output->write($yaml);
    }
}
