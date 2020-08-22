<?php

namespace Infra\Resource;

use GraphQL\Type\Definition\Type;
use Graph\Graph;
use RuntimeException;

class ServiceResource extends AbstractResource
{
    protected $hostGroups = null; // cache

    public function getPublicIp()
    {
        return $this->spec['publicIp'] ?? null;
    }

    public function getFqsn()
    {
        return $this->getName() . '.service.example.com';
    }

    public function getHosts()
    {
        return $this->infra->getHosts($this->spec['hosts'] ?? []);
    }

    public static function getConfig(Graph $graph)
    {
        return [
            'name'   => 'Service',
            'fields' => function () use (&$graph) {
                return [
                    'name'            => Type::id(),
                    'description'     => [
                        'type'        => Type::string(),
                        'description' => 'Description',
                    ],
                    'fqsn'            => [
                        'type'        => Type::string(),
                        'description' => 'Fully Qualified Service Name',
                    ],
                    'hosts'      => [
                        'type'        => Type::listOf($graph->getType('Host')),
                        'description' => 'Returns all hosts',
                    ],
                    'labels' => [
                        'type'        => Type::listOf($graph->getType('Label')),
                        'description' => 'Returns all labels',
                    ],
                ];
            },
        ];
    }

}
