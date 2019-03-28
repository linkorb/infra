<?php

namespace Infra\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use GraphQL\GraphQL;
use GraphQL\Error\Debug;

class QueryCommand extends AbstractCommand
{
    public function configure()
    {
        $this->setName('query')
            ->setDescription('Execute GraphQL query');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $query = file_get_contents('php://stdin');

        $rootValue = [];
        $variableValues = [];
        $result = GraphQL::executeQuery($this->infra->getSchema(), $query, $rootValue, null, $variableValues);
        $debug = Debug::INCLUDE_DEBUG_MESSAGE | Debug::INCLUDE_TRACE;
        $result = $result->toArray($debug);

        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
