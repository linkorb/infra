<?php

namespace Infra\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Infra\Infra;
use GraphQL\GraphQL;
use GraphQL\Error\Debug;

class QueryCommand extends AbstractCommand
{
    public function configure()
    {
        $this->setName('query')
            ->setDescription('Execute GraphQL query')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $infra = new Infra();
        $infra->loadFile(__DIR__ . '/../../example/infra.yaml');

        $query = file_get_contents("php://stdin");

        $rootValue = [];
        $variableValues = [];
        $result = GraphQL::executeQuery($infra->getSchema(), $query, $rootValue, null, $variableValues);
        $debug = Debug::INCLUDE_DEBUG_MESSAGE | Debug::INCLUDE_TRACE;
        $output = $result->toArray($debug);

        echo json_encode($output, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);


    }
}
