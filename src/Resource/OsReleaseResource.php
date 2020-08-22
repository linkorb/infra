<?php

namespace Infra\Resource;

use GraphQL\Type\Definition\Type;
use Graph\Graph;

class OsReleaseResource extends AbstractResource
{
    public function getDisplayName()
    {
        return $this->spec['displayName'] ?? null;
    }

    public function getHosts(): array
    {
        /** @var HostResource[] $hosts */
        $hosts = $this->graph->getResourcesByType('Host');
        $res = [];
        foreach ($hosts as $host) {
            if ($host->hasOsReleaseName($this->getName())) {
                $res[] = $host;
            }
        }

        return $res;
    }

    public static function getConfig(Graph $graph): array
    {
        return [
            'name'   => 'OsRelease',
            'fields' => [
                'name'        => Type::id(),
                'description' => [
                    'type'        => Type::string(),
                    'description' => 'Description',
                ],
                'labels' => [
                    'type'        => Type::listOf($graph->getType('Label')),
                    'description' => 'Returns all labels',
                ],
                'displayName' => [
                    'type'        => Type::string(),
                    'description' => 'OS Display Name',
                ],
                'hosts'       => [
                    'type'        => Type::listOf($graph->getType('Host')),
                    'description' => 'Returns all hosts in this group',
                ],
            ],
        ];
    }
}
