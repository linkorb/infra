<?php

namespace Infra\Resource;

use GraphQL\Type\Definition\Type;
use Graph\Graph;

class DnsDomainResource extends AbstractResource
{
    public function getDnsAccount()
    {
        return $this->spec['dnsAccount'] ?? null;
    }

    public function getDnsRecords(): array
    {
        /** @var DnsRecordResource[] $records */
        $records = $this->graph->getResourcesByType('DnsRecord');
        $res = [];
        foreach ($records as $record) {
            if (
                $record->getDnsDomain() &&
                $record->getDnsDomain()->getName() === $this->getName()
            ) {
                $res[] = $record;
            }
        }

        return $res;
    }

    public static function getConfig(Graph $graph): array
    {
        return [
            'name'   => 'DnsDomain',
            'fields' => function () use (&$graph) {
                return [
                    'name'       => Type::id(),
                    'labels' => [
                        'type'        => Type::listOf($graph->getType('Label')),
                        'description' => 'Returns all labels',
                    ],
                    'dnsAccount' => [
                        'type'        => Type::string(),
                        'description' => 'DNS account',
                    ],
                    'dnsRecords' => [
                        'type'        => Type::listOf($graph->getType('DnsRecord')),
                        'description' => 'Returns all dns records for this domain',
                    ],
                ];
            },
        ];
    }
}
