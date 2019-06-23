<?php

namespace Infra\Resource;

use GraphQL\Type\Definition\Type;
use Graph\Graph;

class DnsRecordResource extends AbstractResource
{
    public function getDnsDomain()
    {
        return $this->graph->getResource('DnsDomain', $this->spec['dnsDomain'] ?? null);
    }

    public function getType()
    {
        return $this->spec['type'] ?? null;
    }

    public function getTtl()
    {
        return $this->spec['ttl'] ?? null;
    }

    public static function getConfig(Graph $graph): array
    {
        return [
            'name'   => 'DnsRecord',
            'fields' => [
                'name'      => Type::id(),
                'dnsDomain' => [
                    'type'        => $graph->getType('DnsDomain'),
                    'description' => 'DNS domain',
                ],
                'type'      => Type::string(),
                'ttl'       => Type::int(),
                'value'     => Type::string(),
            ],
        ];
    }
}
